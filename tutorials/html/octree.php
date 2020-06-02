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
    
    <title>Spatial Partitioning and Search Operations with Octrees</title>
    
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
            
  <div class="section" id="spatial-partitioning-and-search-operations-with-octrees">
<span id="octree-search"></span><h1>Spatial Partitioning and Search Operations with Octrees</h1>
<p>An octree is a tree-based data structure for managing sparse 3-D data. Each internal node has exactly eight children.
In this tutorial we will learn how to use the octree for spatial partitioning and neighbor search within pointcloud data. Particularly, we explain how to perform a &#8220;Neighbors within Voxel Search&#8221;, the
&#8220;K Nearest Neighbor Search&#8221; and &#8220;Neighbors within Radius Search&#8221;.</p>
</div>
<div class="section" id="the-code">
<h1>The code:</h1>
<p>First, create a file, let&#8217;s say, <tt class="docutils literal"><span class="pre">octree_search.cpp</span></tt> and place the following inside it:</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre>  1
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
 71
 72
 73
 74
 75
 76
 77
 78
 79
 80
 81
 82
 83
 84
 85
 86
 87
 88
 89
 90
 91
 92
 93
 94
 95
 96
 97
 98
 99
100</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;pcl/point_cloud.h&gt;</span>
<span class="cp">#include &lt;pcl/octree/octree.h&gt;</span>

<span class="cp">#include &lt;iostream&gt;</span>
<span class="cp">#include &lt;vector&gt;</span>
<span class="cp">#include &lt;ctime&gt;</span>

<span class="kt">int</span>
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">srand</span> <span class="p">((</span><span class="kt">unsigned</span> <span class="kt">int</span><span class="p">)</span> <span class="n">time</span> <span class="p">(</span><span class="nb">NULL</span><span class="p">));</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="c1">// Generate pointcloud data</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">=</span> <span class="mi">1000</span><span class="p">;</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">height</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">resize</span> <span class="p">(</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">*</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">height</span><span class="p">);</span>

  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">=</span> <span class="mf">1024.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">=</span> <span class="mf">1024.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">=</span> <span class="mf">1024.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="kt">float</span> <span class="n">resolution</span> <span class="o">=</span> <span class="mf">128.0f</span><span class="p">;</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">octree</span><span class="o">::</span><span class="n">OctreePointCloudSearch</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">octree</span> <span class="p">(</span><span class="n">resolution</span><span class="p">);</span>

  <span class="n">octree</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">octree</span><span class="p">.</span><span class="n">addPointsFromInputCloud</span> <span class="p">();</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">searchPoint</span><span class="p">;</span>

  <span class="n">searchPoint</span><span class="p">.</span><span class="n">x</span> <span class="o">=</span> <span class="mf">1024.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
  <span class="n">searchPoint</span><span class="p">.</span><span class="n">y</span> <span class="o">=</span> <span class="mf">1024.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
  <span class="n">searchPoint</span><span class="p">.</span><span class="n">z</span> <span class="o">=</span> <span class="mf">1024.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>

  <span class="c1">// Neighbors within voxel search</span>

  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">pointIdxVec</span><span class="p">;</span>

  <span class="k">if</span> <span class="p">(</span><span class="n">octree</span><span class="p">.</span><span class="n">voxelSearch</span> <span class="p">(</span><span class="n">searchPoint</span><span class="p">,</span> <span class="n">pointIdxVec</span><span class="p">))</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Neighbors within voxel search at (&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">searchPoint</span><span class="p">.</span><span class="n">x</span> 
     <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">searchPoint</span><span class="p">.</span><span class="n">y</span> 
     <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">searchPoint</span><span class="p">.</span><span class="n">z</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;)&quot;</span> 
     <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
              
    <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">pointIdxVec</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
   <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;    &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">pointIdxVec</span><span class="p">[</span><span class="n">i</span><span class="p">]].</span><span class="n">x</span> 
       <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">pointIdxVec</span><span class="p">[</span><span class="n">i</span><span class="p">]].</span><span class="n">y</span> 
       <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">pointIdxVec</span><span class="p">[</span><span class="n">i</span><span class="p">]].</span><span class="n">z</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="p">}</span>

  <span class="c1">// K nearest neighbor search</span>

  <span class="kt">int</span> <span class="n">K</span> <span class="o">=</span> <span class="mi">10</span><span class="p">;</span>

  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">pointIdxNKNSearch</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="n">pointNKNSquaredDistance</span><span class="p">;</span>

  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;K nearest neighbor search at (&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">searchPoint</span><span class="p">.</span><span class="n">x</span> 
            <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">searchPoint</span><span class="p">.</span><span class="n">y</span> 
            <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">searchPoint</span><span class="p">.</span><span class="n">z</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;) with K=&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">K</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="k">if</span> <span class="p">(</span><span class="n">octree</span><span class="p">.</span><span class="n">nearestKSearch</span> <span class="p">(</span><span class="n">searchPoint</span><span class="p">,</span> <span class="n">K</span><span class="p">,</span> <span class="n">pointIdxNKNSearch</span><span class="p">,</span> <span class="n">pointNKNSquaredDistance</span><span class="p">)</span> <span class="o">&gt;</span> <span class="mi">0</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">pointIdxNKNSearch</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
      <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;    &quot;</span>  <span class="o">&lt;&lt;</span>   <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span> <span class="n">pointIdxNKNSearch</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="p">].</span><span class="n">x</span> 
                <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span> <span class="n">pointIdxNKNSearch</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="p">].</span><span class="n">y</span> 
                <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span> <span class="n">pointIdxNKNSearch</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="p">].</span><span class="n">z</span> 
                <span class="o">&lt;&lt;</span> <span class="s">&quot; (squared distance: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">pointNKNSquaredDistance</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="p">}</span>

  <span class="c1">// Neighbors within radius search</span>

  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">pointIdxRadiusSearch</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="n">pointRadiusSquaredDistance</span><span class="p">;</span>

  <span class="kt">float</span> <span class="n">radius</span> <span class="o">=</span> <span class="mf">256.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Neighbors within radius search at (&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">searchPoint</span><span class="p">.</span><span class="n">x</span> 
      <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">searchPoint</span><span class="p">.</span><span class="n">y</span> 
      <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">searchPoint</span><span class="p">.</span><span class="n">z</span>
      <span class="o">&lt;&lt;</span> <span class="s">&quot;) with radius=&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">radius</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>


  <span class="k">if</span> <span class="p">(</span><span class="n">octree</span><span class="p">.</span><span class="n">radiusSearch</span> <span class="p">(</span><span class="n">searchPoint</span><span class="p">,</span> <span class="n">radius</span><span class="p">,</span> <span class="n">pointIdxRadiusSearch</span><span class="p">,</span> <span class="n">pointRadiusSquaredDistance</span><span class="p">)</span> <span class="o">&gt;</span> <span class="mi">0</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">pointIdxRadiusSearch</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
      <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;    &quot;</span>  <span class="o">&lt;&lt;</span>   <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span> <span class="n">pointIdxRadiusSearch</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="p">].</span><span class="n">x</span> 
                <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span> <span class="n">pointIdxRadiusSearch</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="p">].</span><span class="n">y</span> 
                <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span> <span class="n">pointIdxRadiusSearch</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="p">].</span><span class="n">z</span> 
                <span class="o">&lt;&lt;</span> <span class="s">&quot; (squared distance: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">pointRadiusSquaredDistance</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="p">}</span>

<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>Now, let&#8217;s explain the code in detail.</p>
<p>We fist define and instantiate a shared PointCloud structure and fill it with random points.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="c1">// Generate pointcloud data</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">=</span> <span class="mi">1000</span><span class="p">;</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">height</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">resize</span> <span class="p">(</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">*</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">height</span><span class="p">);</span>

  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">=</span> <span class="mf">1024.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">=</span> <span class="mf">1024.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">=</span> <span class="mf">1024.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>Then we create an octree instance which is initialized with its resolution. This octree keeps a vector of point indices within its leaf nodes.
The resolution parameter describes the length of the smalles voxels at lowest octree level. The depth of the octree is therefore a function of the resolution as well as
the spatial dimension of the pointcloud. If a bounding box of the pointcloud is know, it should be assigned to the octree by using the defineBoundingBox method.
Then we assign a pointer to the PointCloud and add all points to the octree.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="kt">float</span> <span class="n">resolution</span> <span class="o">=</span> <span class="mf">128.0f</span><span class="p">;</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">octree</span><span class="o">::</span><span class="n">OctreePointCloudSearch</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">octree</span> <span class="p">(</span><span class="n">resolution</span><span class="p">);</span>

  <span class="n">octree</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">octree</span><span class="p">.</span><span class="n">addPointsFromInputCloud</span> <span class="p">();</span>
</pre></div>
</div>
<p>Once the PointCloud is associated with an octree, we can perform search operations. The fist search method used here is &#8220;Neighbors within Voxel Search&#8221;. It assigns the search point to the corresponding
leaf node voxel and returns a vector of point indices. These indices relate to points which fall within the same voxel. The distance between
the search point and the search result depend therefore on the resolution parameter of the octree.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">pointIdxVec</span><span class="p">;</span>

  <span class="k">if</span> <span class="p">(</span><span class="n">octree</span><span class="p">.</span><span class="n">voxelSearch</span> <span class="p">(</span><span class="n">searchPoint</span><span class="p">,</span> <span class="n">pointIdxVec</span><span class="p">))</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Neighbors within voxel search at (&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">searchPoint</span><span class="p">.</span><span class="n">x</span> 
     <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">searchPoint</span><span class="p">.</span><span class="n">y</span> 
     <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">searchPoint</span><span class="p">.</span><span class="n">z</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;)&quot;</span> 
     <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
              
    <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">pointIdxVec</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
   <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;    &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">pointIdxVec</span><span class="p">[</span><span class="n">i</span><span class="p">]].</span><span class="n">x</span> 
       <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">pointIdxVec</span><span class="p">[</span><span class="n">i</span><span class="p">]].</span><span class="n">y</span> 
       <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">pointIdxVec</span><span class="p">[</span><span class="n">i</span><span class="p">]].</span><span class="n">z</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>Next, a K nearest neighbor search is demonstrated. In this example, K is set to 10. The &#8220;K Nearest Neighbor Search&#8221; method writes the search results into two separate vectors.
The first one, pointIdxNKNSearch, will contain the search result (indices referring to the associated PointCloud data set). The second vector holds corresponding squared distances
between the search point and the nearest neighbors.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// K nearest neighbor search</span>

  <span class="kt">int</span> <span class="n">K</span> <span class="o">=</span> <span class="mi">10</span><span class="p">;</span>

  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">pointIdxNKNSearch</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="n">pointNKNSquaredDistance</span><span class="p">;</span>

  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;K nearest neighbor search at (&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">searchPoint</span><span class="p">.</span><span class="n">x</span> 
            <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">searchPoint</span><span class="p">.</span><span class="n">y</span> 
            <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">searchPoint</span><span class="p">.</span><span class="n">z</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;) with K=&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">K</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="k">if</span> <span class="p">(</span><span class="n">octree</span><span class="p">.</span><span class="n">nearestKSearch</span> <span class="p">(</span><span class="n">searchPoint</span><span class="p">,</span> <span class="n">K</span><span class="p">,</span> <span class="n">pointIdxNKNSearch</span><span class="p">,</span> <span class="n">pointNKNSquaredDistance</span><span class="p">)</span> <span class="o">&gt;</span> <span class="mi">0</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">pointIdxNKNSearch</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
      <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;    &quot;</span>  <span class="o">&lt;&lt;</span>   <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span> <span class="n">pointIdxNKNSearch</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="p">].</span><span class="n">x</span> 
                <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span> <span class="n">pointIdxNKNSearch</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="p">].</span><span class="n">y</span> 
                <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span> <span class="n">pointIdxNKNSearch</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="p">].</span><span class="n">z</span> 
                <span class="o">&lt;&lt;</span> <span class="s">&quot; (squared distance: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">pointNKNSquaredDistance</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>The &#8220;Neighbors within Radius Search&#8221; works very similar to the &#8220;K Nearest Neighbor Search&#8221;. Its search results are written to two separate vectors describing
point indices and squares search point distances.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">pointIdxRadiusSearch</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="n">pointRadiusSquaredDistance</span><span class="p">;</span>

  <span class="kt">float</span> <span class="n">radius</span> <span class="o">=</span> <span class="mf">256.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Neighbors within radius search at (&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">searchPoint</span><span class="p">.</span><span class="n">x</span> 
      <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">searchPoint</span><span class="p">.</span><span class="n">y</span> 
      <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">searchPoint</span><span class="p">.</span><span class="n">z</span>
      <span class="o">&lt;&lt;</span> <span class="s">&quot;) with radius=&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">radius</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>


  <span class="k">if</span> <span class="p">(</span><span class="n">octree</span><span class="p">.</span><span class="n">radiusSearch</span> <span class="p">(</span><span class="n">searchPoint</span><span class="p">,</span> <span class="n">radius</span><span class="p">,</span> <span class="n">pointIdxRadiusSearch</span><span class="p">,</span> <span class="n">pointRadiusSquaredDistance</span><span class="p">)</span> <span class="o">&gt;</span> <span class="mi">0</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">pointIdxRadiusSearch</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
      <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;    &quot;</span>  <span class="o">&lt;&lt;</span>   <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span> <span class="n">pointIdxRadiusSearch</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="p">].</span><span class="n">x</span> 
                <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span> <span class="n">pointIdxRadiusSearch</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="p">].</span><span class="n">y</span> 
                <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span> <span class="n">pointIdxRadiusSearch</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="p">].</span><span class="n">z</span> 
                <span class="o">&lt;&lt;</span> <span class="s">&quot; (squared distance: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">pointRadiusSquaredDistance</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="p">}</span>
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

<span class="nb">project</span><span class="p">(</span><span class="s">octree_search</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.2</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">octree_search</span> <span class="s">octree_search.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">octree_search</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it. Simply do:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./octreesearch
</pre></div>
</div>
<p>You will see something similar to:</p>
<div class="highlight-python"><div class="highlight"><pre>Neighbors within voxel search at (974.82 188.793 138.779)
    903.656 82.8158 162.392
    1007.34 191.035 61.7727
    896.88 155.711 58.1942
K nearest neighbor search at (974.82 188.793 138.779) with K=10
    903.656 82.8158 162.392 (squared distance: 16853.1)
    903.18 247.058 54.3528 (squared distance: 15655)
    861.595 149.96 135.199 (squared distance: 14340.7)
    896.88 155.711 58.1942 (squared distance: 13663)
    995.889 116.224 219.077 (squared distance: 12157.9)
    885.852 238.41 160.966 (squared distance: 10869.5)
    900.807 220.317 77.1432 (squared distance: 10270.7)
    1002.46 117.236 184.594 (squared distance: 7983.59)
    1007.34 191.035 61.7727 (squared distance: 6992.54)
    930.13 223.335 174.763 (squared distance: 4485.15)
Neighbors within radius search at (974.82 188.793 138.779) with radius=109.783
    1007.34 191.035 61.7727 (squared distance: 6992.54)
    900.807 220.317 77.1432 (squared distance: 10270.7)
    885.852 238.41 160.966 (squared distance: 10869.5)
    1002.46 117.236 184.594 (squared distance: 7983.59)
    930.13 223.335 174.763 (squared distance: 4485.15)
</pre></div>
</div>
</div>
<div class="section" id="additional-details">
<h1>Additional Details</h1>
<p>Several octree types are provided by the PCL octree component. They basically differ by their individual leaf node characteristics.</p>
<ul class="simple">
<li>OctreePointCloudPointVector (equal to OctreePointCloud): This octree can hold a list of point indices at each leaf node.</li>
<li>OctreePointCloudSinglePoint: This octree class hold only a single point indices at each leaf node. Only the most recent point index that is assigned to the leaf node is stored.</li>
<li>OctreePointCloudOccupancy: This octree does not store any point information at its leaf nodes. It can be used for spatial occupancy checks.</li>
<li>OctreePointCloudDensity: This octree counts the amount of points within each leaf node voxel. It allows for spatial density queries.</li>
</ul>
<p>If octrees needs to be created at high rate, please have a look at the octree double buffering implementation ( Octree2BufBase class ). This class
keeps two parallel octree structures in the memory at the same time. In addition to search operations, this also enables spatial change detection. Furthermore, an advanced memory management reduces memory allocation
and deallocation operations during the octree building process. The double buffering octree implementation can be assigned to all OctreePointCloud classes via the template argument &#8220;OctreeT&#8221;.</p>
<p>All octrees support serialization and deserialization of the octree structure and the octree data content.</p>
</div>
<div class="section" id="conclusion">
<h1>Conclusion</h1>
<p>The PCL octree implementation is a powerful tools for spatial partitioning and search operation.</p>
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