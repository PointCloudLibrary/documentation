<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation - Point Cloud Library (PCL)</title>
</head>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">


<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    
    <title>Spatial change detection on unorganized point cloud data</title>
    
    <link rel="stylesheet" href="_static/sphinxdoc.css" type="text/css" />
    <link rel="stylesheet" href="_static/pygments.css" type="text/css" />
    
    <script type="text/javascript">
      var DOCUMENTATION_OPTIONS = {
        URL_ROOT:    './',
        VERSION:     '0.0',
        COLLAPSE_INDEX: false,
        FILE_SUFFIX: '.php',
        HAS_SOURCE:  true
      };
    </script>
    <script type="text/javascript" src="_static/jquery.js"></script>
    <script type="text/javascript" src="_static/underscore.js"></script>
    <script type="text/javascript" src="_static/doctools.js"></script>
    <link rel="top" title="None" href="index.php" />
<?php
define('MODX_CORE_PATH', '/var/www/pointclouds.org/core/');
define('MODX_CONFIG_KEY', 'config');

require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
require_once MODX_CORE_PATH.'model/modx/modx.class.php';
$modx = new modX();
$modx->initialize('web');

$snip = $modx->runSnippet("getSiteNavigation", array('id'=>5, 'phLevels'=>'sitenav.level0,sitenav.level1', 'showPageNav'=>'n'));
$chunkOutput = $modx->getChunk("site-header", array('sitenav'=>$snip));
$bodytag = str_replace("[[+showSubmenus:notempty=`", "", $chunkOutput);
$bodytag = str_replace("`]]", "", $bodytag);
echo $bodytag;
echo "\n";
?>
<div id="pagetitle">
<h1>Documentation</h1>
<a id="donate" href="http://www.openperception.org/support/"><img src="/assets/images/donate-button.png" alt="Donate to the Open Perception foundation"/></a>
</div>
<div id="page-content">

  </head>
  <body>

    <div class="document">
      <div class="documentwrapper">
          <div class="body">
            
  <div class="section" id="spatial-change-detection-on-unorganized-point-cloud-data">
<span id="octree-change-detection"></span><h1>Spatial change detection on unorganized point cloud data</h1>
<p>An octree is a tree-based data structure for organizing sparse 3-D data. In this tutorial we will learn how to use the octree implementation for detecting
spatial changes between multiple unorganized point clouds which could vary in size, resolution, density and point ordering. By recursively comparing
the tree structures of octrees, spatial changes represented by differences in voxel configuration can be identified.
Additionally, we explain how to use the pcl octree &#8220;double buffering&#8221; technique allows us to efficiently process multiple point clouds over time.</p>
</div>
<div class="section" id="the-code">
<h1>The code:</h1>
<p>First, create a file, let&#8217;s say, <tt class="docutils literal"><span class="pre">octree_change_detection.cpp</span></tt> and place the following inside it:</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
 2
 3
 4
 5
 6
 7
 8
 9
10
11
12
13
14
15
16
17
18
19
20
21
22
23
24
25
26
27
28
29
30
31
32
33
34
35
36
37
38
39
40
41
42
43
44
45
46
47
48
49
50
51
52
53
54
55
56
57
58
59
60
61
62
63
64
65
66
67
68
69
70
71</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;pcl/point_cloud.h&gt;</span>
<span class="cp">#include &lt;pcl/octree/octree.h&gt;</span>

<span class="cp">#include &lt;iostream&gt;</span>
<span class="cp">#include &lt;vector&gt;</span>
<span class="cp">#include &lt;ctime&gt;</span>

<span class="kt">int</span>
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">srand</span> <span class="p">((</span><span class="kt">unsigned</span> <span class="kt">int</span><span class="p">)</span> <span class="n">time</span> <span class="p">(</span><span class="nb">NULL</span><span class="p">));</span>

  <span class="c1">// Octree resolution - side length of octree voxels</span>
  <span class="kt">float</span> <span class="n">resolution</span> <span class="o">=</span> <span class="mf">32.0f</span><span class="p">;</span>

  <span class="c1">// Instantiate octree-based point cloud change detection class</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">octree</span><span class="o">::</span><span class="n">OctreePointCloudChangeDetector</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">octree</span> <span class="p">(</span><span class="n">resolution</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloudA</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">);</span>

  <span class="c1">// Generate pointcloud data for cloudA</span>
  <span class="n">cloudA</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">=</span> <span class="mi">128</span><span class="p">;</span>
  <span class="n">cloudA</span><span class="o">-&gt;</span><span class="n">height</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
  <span class="n">cloudA</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">resize</span> <span class="p">(</span><span class="n">cloudA</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">*</span> <span class="n">cloudA</span><span class="o">-&gt;</span><span class="n">height</span><span class="p">);</span>

  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">cloudA</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">cloudA</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">=</span> <span class="mf">64.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">cloudA</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">=</span> <span class="mf">64.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">cloudA</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">=</span> <span class="mf">64.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="c1">// Add points from cloudA to octree</span>
  <span class="n">octree</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloudA</span><span class="p">);</span>
  <span class="n">octree</span><span class="p">.</span><span class="n">addPointsFromInputCloud</span> <span class="p">();</span>

  <span class="c1">// Switch octree buffers: This resets octree but keeps previous tree structure in memory.</span>
  <span class="n">octree</span><span class="p">.</span><span class="n">switchBuffers</span> <span class="p">();</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloudB</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">);</span>
   
  <span class="c1">// Generate pointcloud data for cloudB </span>
  <span class="n">cloudB</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">=</span> <span class="mi">128</span><span class="p">;</span>
  <span class="n">cloudB</span><span class="o">-&gt;</span><span class="n">height</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
  <span class="n">cloudB</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">resize</span> <span class="p">(</span><span class="n">cloudB</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">*</span> <span class="n">cloudB</span><span class="o">-&gt;</span><span class="n">height</span><span class="p">);</span>

  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">cloudB</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">cloudB</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">=</span> <span class="mf">64.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">cloudB</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">=</span> <span class="mf">64.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">cloudB</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">=</span> <span class="mf">64.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="c1">// Add points from cloudB to octree</span>
  <span class="n">octree</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloudB</span><span class="p">);</span>
  <span class="n">octree</span><span class="p">.</span><span class="n">addPointsFromInputCloud</span> <span class="p">();</span>

  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">newPointIdxVector</span><span class="p">;</span>

  <span class="c1">// Get vector of point indices from octree voxels which did not exist in previous buffer</span>
  <span class="n">octree</span><span class="p">.</span><span class="n">getPointIndicesFromNewVoxels</span> <span class="p">(</span><span class="n">newPointIdxVector</span><span class="p">);</span>

  <span class="c1">// Output points</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Output from getPointIndicesFromNewVoxels:&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">newPointIdxVector</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="n">i</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;# Index:&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">newPointIdxVector</span><span class="p">[</span><span class="n">i</span><span class="p">]</span>
              <span class="o">&lt;&lt;</span> <span class="s">&quot;  Point:&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloudB</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">newPointIdxVector</span><span class="p">[</span><span class="n">i</span><span class="p">]].</span><span class="n">x</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span>
              <span class="o">&lt;&lt;</span> <span class="n">cloudB</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">newPointIdxVector</span><span class="p">[</span><span class="n">i</span><span class="p">]].</span><span class="n">y</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span>
              <span class="o">&lt;&lt;</span> <span class="n">cloudB</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">newPointIdxVector</span><span class="p">[</span><span class="n">i</span><span class="p">]].</span><span class="n">z</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>Now, let&#8217;s discuss the code in detail.</p>
<p>We fist instantiate the OctreePointCloudChangeDetector class and define its voxel resolution.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">srand</span> <span class="p">((</span><span class="kt">unsigned</span> <span class="kt">int</span><span class="p">)</span> <span class="n">time</span> <span class="p">(</span><span class="nb">NULL</span><span class="p">));</span>

  <span class="c1">// Octree resolution - side length of octree voxels</span>
  <span class="kt">float</span> <span class="n">resolution</span> <span class="o">=</span> <span class="mf">32.0f</span><span class="p">;</span>

  <span class="c1">// Instantiate octree-based point cloud change detection class</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">octree</span><span class="o">::</span><span class="n">OctreePointCloudChangeDetector</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">octree</span> <span class="p">(</span><span class="n">resolution</span><span class="p">);</span>
</pre></div>
</div>
<p>Then we create a point cloud instance cloudA which is initialized with random point data. The generated point data is used to build an octree structure.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloudA</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">);</span>

  <span class="c1">// Generate pointcloud data for cloudA</span>
  <span class="n">cloudA</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">=</span> <span class="mi">128</span><span class="p">;</span>
  <span class="n">cloudA</span><span class="o">-&gt;</span><span class="n">height</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
  <span class="n">cloudA</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">resize</span> <span class="p">(</span><span class="n">cloudA</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">*</span> <span class="n">cloudA</span><span class="o">-&gt;</span><span class="n">height</span><span class="p">);</span>

  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">cloudA</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">cloudA</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">=</span> <span class="mf">64.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">cloudA</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">=</span> <span class="mf">64.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">cloudA</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">=</span> <span class="mf">64.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="c1">// Add points from cloudA to octree</span>
  <span class="n">octree</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloudA</span><span class="p">);</span>
  <span class="n">octree</span><span class="p">.</span><span class="n">addPointsFromInputCloud</span> <span class="p">();</span>
</pre></div>
</div>
<p>Point cloud cloudA is our reference point cloud and the octree structure describe its spatial distribution. The class OctreePointCloudChangeDetector inherits from
class Octree2BufBase which enables to keep and manage two octrees in the memory at the same time. In addition, it implements a memory pool that reuses
already allocated node objects and therefore reduces expensive memory allocation and deallocation operations when generating octrees of multiple point clouds. By calling &#8220;octree.switchBuffers()&#8221;, we reset the
octree class while keeping the previous octree structure in memory.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Switch octree buffers: This resets octree but keeps previous tree structure in memory.</span>
  <span class="n">octree</span><span class="p">.</span><span class="n">switchBuffers</span> <span class="p">();</span>
</pre></div>
</div>
<p>Now we instantiate a second point cloud &#8220;cloudB&#8221; and fill it with random point data. This point cloud is used to build a new octree structure.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloudB</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">);</span>
   
  <span class="c1">// Generate pointcloud data for cloudB </span>
  <span class="n">cloudB</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">=</span> <span class="mi">128</span><span class="p">;</span>
  <span class="n">cloudB</span><span class="o">-&gt;</span><span class="n">height</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
  <span class="n">cloudB</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">resize</span> <span class="p">(</span><span class="n">cloudB</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">*</span> <span class="n">cloudB</span><span class="o">-&gt;</span><span class="n">height</span><span class="p">);</span>

  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">cloudB</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">cloudB</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">=</span> <span class="mf">64.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">cloudB</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">=</span> <span class="mf">64.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">cloudB</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">=</span> <span class="mf">64.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="c1">// Add points from cloudB to octree</span>
  <span class="n">octree</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloudB</span><span class="p">);</span>
  <span class="n">octree</span><span class="p">.</span><span class="n">addPointsFromInputCloud</span> <span class="p">();</span>
</pre></div>
</div>
<p>In order to retrieve points that are stored at voxels of the current octree structure (based on cloudB) which did not exist in the previous octree structure
(based on cloudA), we can call the method &#8220;getPointIndicesFromNewVoxels&#8221; which return a vector of the result point indices.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">newPointIdxVector</span><span class="p">;</span>

  <span class="c1">// Get vector of point indices from octree voxels which did not exist in previous buffer</span>
  <span class="n">octree</span><span class="p">.</span><span class="n">getPointIndicesFromNewVoxels</span> <span class="p">(</span><span class="n">newPointIdxVector</span><span class="p">);</span>
</pre></div>
</div>
<p>Finally, we output the results to the std::cout stream.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Output points</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Output from getPointIndicesFromNewVoxels:&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">newPointIdxVector</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="n">i</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;# Index:&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">newPointIdxVector</span><span class="p">[</span><span class="n">i</span><span class="p">]</span>
              <span class="o">&lt;&lt;</span> <span class="s">&quot;  Point:&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloudB</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">newPointIdxVector</span><span class="p">[</span><span class="n">i</span><span class="p">]].</span><span class="n">x</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span>
              <span class="o">&lt;&lt;</span> <span class="n">cloudB</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">newPointIdxVector</span><span class="p">[</span><span class="n">i</span><span class="p">]].</span><span class="n">y</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span>
              <span class="o">&lt;&lt;</span> <span class="n">cloudB</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">newPointIdxVector</span><span class="p">[</span><span class="n">i</span><span class="p">]].</span><span class="n">z</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
</pre></div>
</div>
</div>
<div class="section" id="compiling-and-running-the-program">
<h1>Compiling and running the program</h1>
<p>Add the following lines to your CMakeLists.txt file:</p>
<div class="highlight-cmake"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
 2
 3
 4
 5
 6
 7
 8
 9
10
11
12</pre></div></td><td class="code"><div class="highlight"><pre><span class="nb">cmake_minimum_required</span><span class="p">(</span><span class="s">VERSION</span> <span class="s">2.8</span> <span class="s">FATAL_ERROR</span><span class="p">)</span>

<span class="nb">project</span><span class="p">(</span><span class="s">octree_change_detection</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.2</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">octree_change_detection</span> <span class="s">octree_change_detection.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">octree_change_detection</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it. Simply do:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./octree_change_detection
</pre></div>
</div>
<p>You will see something similar to:</p>
<div class="highlight-python"><div class="highlight"><pre>Output from getPointIndicesFromNewVoxels:
0# Index:11  Point:5.56047 56.5082 10.2807
1# Index:34  Point:1.27106 63.8973 14.5316
2# Index:102  Point:6.42197 60.7727 14.7087
3# Index:105  Point:5.64673 57.736 25.7479
4# Index:66  Point:22.8585 56.4647 63.9779
5# Index:53  Point:52.0745 14.9643 63.5844
</pre></div>
</div>
</div>
<div class="section" id="another-example-application-openni-change-viewer">
<h1>Another example application: OpenNI change viewer</h1>
<p>The pcl visualization component contains an openNI change detector example. It displays grabbed point clouds from the OpenNI interface and displays
detected spatial changes in red.</p>
<p>Simply execute:</p>
<div class="highlight-python"><div class="highlight"><pre>$ cd visualization/tools
$ ./openni_change_viewer
</pre></div>
</div>
<p>And you should see something like this:</p>
<p><img alt="octreeChangeViewer" src="_images/changedetectionViewer.jpg" /></p>
<blockquote>
<div></div></blockquote>
</div>
<div class="section" id="conclusion">
<h1>Conclusion</h1>
<p>This octree-based change detection enables to analyse &#8220;unorganized&#8221; point clouds for spatial changes.</p>
</div>
<div class="section" id="additional-details">
<h1>Additional Details</h1>
<p>&#8220;Unorganized&#8221; point clouds are characterized by non-existing point references between points from different point clouds due to varying size, resolution, density and/or point ordering.
In case of &#8220;organized&#8221; point clouds often based on a single 2D depth/disparity images with fixed width and height, a differential analysis of the corresponding 2D depth data might be faster.</p>
</div>


          </div>
      </div>
      <div class="clearer"></div>
    </div>
</div> <!-- #page-content -->

<?php
$chunkOutput = $modx->getChunk("site-footer");
echo $chunkOutput;
?>

  </body>
</html>