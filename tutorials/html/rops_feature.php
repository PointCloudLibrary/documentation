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
    
    <title>RoPs (Rotational Projection Statistics) feature</title>
    
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
            
  <div class="section" id="rops-rotational-projection-statistics-feature">
<span id="rops-feature"></span><h1>RoPs (Rotational Projection Statistics) feature</h1>
<p>In this tutorial we will learn how to use the <cite>pcl::ROPSEstimation</cite> class in order to extract points features.
The feature extraction method implemented in this class was proposed by Yulan Guo, Ferdous Sohel, Mohammed Bennamoun, Min Lu and
Jianwei Wanalso in their article &#8220;Rotational Projection Statistics for 3D Local Surface Description and Object Recognition&#8221;</p>
</div>
<div class="section" id="theoretical-primer">
<h1>Theoretical Primer</h1>
<p>The idea of the feature extraction method is as follows.
Having a mesh and a set of points for which feature must be computed we perform some simple steps. First of all for a given point of interest
the local surface is cropped. Local surface consists of the points and triangles that are within the given support radius.
For the given local surface LRF (Local Reference Frame) is computed. LRF is simply a triplet of vectors,
the comprehensive information about how these vectors are computed you can find in the article.
What is really important is that using these vectors we can provide the invariance to the rotation of the cloud. To do that, we simply
translate points of the local surface in such way that point of interest became the origin, after that we rotate local surface so that the
LRF vectors were aligned with the Ox, Oy and Oz axes. Having this done, we then start the feature extraction.
For every axis Ox, Oy and Oz the following steps are performed, we will refer to these axes as current axis:</p>
<blockquote>
<div><ul class="simple">
<li>local surface is rotated around the current axis by a given angle;</li>
<li>points of the rotated local surface are projected onto three planes XY, XZ and YZ;</li>
<li>for each projection distribution matrix is built, this matrix simply shows how much points fall onto each bin. Number of bins represents the matrix dimension and is the parameter of the algorithm, as well as the support radius;</li>
<li>for each distribution matrix central moments are calculated: M11, M12, M21, M22, E. Here E is the Shannon entropy;</li>
<li>calculated values are then concatenated to form the sub-feature.</li>
</ul>
</div></blockquote>
<p>We iterate through these steps several times. Number of iterations depends on the given number of rotations.
Sub-features for different axes are concatenated to form the final RoPS descriptor.</p>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>For this tutorial we will use the model from the Queen&#8217;s Dataset. You can choose any other point cloud, but in order to make the
code work you will need to use the triangulation algorithm in order to obtain polygons. You can find the proposed model here:</p>
<blockquote>
<div><ul class="simple">
<li><a class="reference external" href="https://github.com/PointCloudLibrary/data/blob/master/tutorials/min_cut_segmentation_tutorial.pcd">points</a> - contains the point cloud</li>
<li><a href="#id1"><span class="problematic" id="id2">`</span></a>indices - contains indices of the points for which RoPs must be computed</li>
<li><a href="#id3"><span class="problematic" id="id4">`</span></a>triangles - contains the polygons</li>
</ul>
</div></blockquote>
<p>Next what you need to do is to create a file <tt class="docutils literal"><span class="pre">rops_feature.cpp</span></tt> in any editor you prefer and copy the following code inside of it:</p>
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
64</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;pcl/features/rops_estimation.h&gt;</span>
<span class="cp">#include &lt;pcl/io/pcd_io.h&gt;</span>

<span class="kt">int</span> <span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">argc</span> <span class="o">!=</span> <span class="mi">4</span><span class="p">)</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">1</span><span class="p">],</span> <span class="o">*</span><span class="n">cloud</span><span class="p">)</span> <span class="o">==</span> <span class="o">-</span><span class="mi">1</span><span class="p">)</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PointIndicesPtr</span> <span class="n">indices</span> <span class="o">=</span> <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointIndices</span><span class="o">&gt;</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointIndices</span> <span class="p">());</span>
  <span class="n">std</span><span class="o">::</span><span class="n">ifstream</span> <span class="n">indices_file</span><span class="p">;</span>
  <span class="n">indices_file</span><span class="p">.</span><span class="n">open</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">2</span><span class="p">],</span> <span class="n">std</span><span class="o">::</span><span class="n">ifstream</span><span class="o">::</span><span class="n">in</span><span class="p">);</span>
  <span class="k">for</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">line</span><span class="p">;</span> <span class="n">std</span><span class="o">::</span><span class="n">getline</span> <span class="p">(</span><span class="n">indices_file</span><span class="p">,</span> <span class="n">line</span><span class="p">);)</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">istringstream</span> <span class="n">in</span> <span class="p">(</span><span class="n">line</span><span class="p">);</span>
    <span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">index</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
    <span class="n">in</span> <span class="o">&gt;&gt;</span> <span class="n">index</span><span class="p">;</span>
    <span class="n">indices</span><span class="o">-&gt;</span><span class="n">indices</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">index</span> <span class="o">-</span> <span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="n">indices_file</span><span class="p">.</span><span class="n">close</span> <span class="p">();</span>

  <span class="n">std</span><span class="o">::</span><span class="n">vector</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Vertices</span><span class="o">&gt;</span> <span class="n">triangles</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">ifstream</span> <span class="n">triangles_file</span><span class="p">;</span>
  <span class="n">triangles_file</span><span class="p">.</span><span class="n">open</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">3</span><span class="p">],</span> <span class="n">std</span><span class="o">::</span><span class="n">ifstream</span><span class="o">::</span><span class="n">in</span><span class="p">);</span>
  <span class="k">for</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">line</span><span class="p">;</span> <span class="n">std</span><span class="o">::</span><span class="n">getline</span> <span class="p">(</span><span class="n">triangles_file</span><span class="p">,</span> <span class="n">line</span><span class="p">);)</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">Vertices</span> <span class="n">triangle</span><span class="p">;</span>
    <span class="n">std</span><span class="o">::</span><span class="n">istringstream</span> <span class="n">in</span> <span class="p">(</span><span class="n">line</span><span class="p">);</span>
    <span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">vertex</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
    <span class="n">in</span> <span class="o">&gt;&gt;</span> <span class="n">vertex</span><span class="p">;</span>
    <span class="n">triangle</span><span class="p">.</span><span class="n">vertices</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">vertex</span> <span class="o">-</span> <span class="mi">1</span><span class="p">);</span>
    <span class="n">in</span> <span class="o">&gt;&gt;</span> <span class="n">vertex</span><span class="p">;</span>
    <span class="n">triangle</span><span class="p">.</span><span class="n">vertices</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">vertex</span> <span class="o">-</span> <span class="mi">1</span><span class="p">);</span>
    <span class="n">in</span> <span class="o">&gt;&gt;</span> <span class="n">vertex</span><span class="p">;</span>
    <span class="n">triangle</span><span class="p">.</span><span class="n">vertices</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">vertex</span> <span class="o">-</span> <span class="mi">1</span><span class="p">);</span>
    <span class="n">triangles</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">triangle</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="kt">float</span> <span class="n">support_radius</span> <span class="o">=</span> <span class="mf">0.0285f</span><span class="p">;</span>
  <span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">number_of_partition_bins</span> <span class="o">=</span> <span class="mi">5</span><span class="p">;</span>
  <span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">number_of_rotations</span> <span class="o">=</span> <span class="mi">3</span><span class="p">;</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">search_method</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">search_method</span><span class="o">-&gt;</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">ROPSEstimation</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Histogram</span> <span class="o">&lt;</span><span class="mi">135</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="n">feature_estimator</span><span class="p">;</span>
  <span class="n">feature_estimator</span><span class="p">.</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">search_method</span><span class="p">);</span>
  <span class="n">feature_estimator</span><span class="p">.</span><span class="n">setSearchSurface</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">feature_estimator</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">feature_estimator</span><span class="p">.</span><span class="n">setIndices</span> <span class="p">(</span><span class="n">indices</span><span class="p">);</span>
  <span class="n">feature_estimator</span><span class="p">.</span><span class="n">setTriangles</span> <span class="p">(</span><span class="n">triangles</span><span class="p">);</span>
  <span class="n">feature_estimator</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="n">support_radius</span><span class="p">);</span>
  <span class="n">feature_estimator</span><span class="p">.</span><span class="n">setNumberOfPartitionBins</span> <span class="p">(</span><span class="n">number_of_partition_bins</span><span class="p">);</span>
  <span class="n">feature_estimator</span><span class="p">.</span><span class="n">setNumberOfRotations</span> <span class="p">(</span><span class="n">number_of_rotations</span><span class="p">);</span>
  <span class="n">feature_estimator</span><span class="p">.</span><span class="n">setSupportRadius</span> <span class="p">(</span><span class="n">support_radius</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Histogram</span> <span class="o">&lt;</span><span class="mi">135</span><span class="o">&gt;</span> <span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">histograms</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Histogram</span> <span class="o">&lt;</span><span class="mi">135</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">feature_estimator</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">histograms</span><span class="p">);</span>

  <span class="k">return</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>Now let&#8217;s study out what is the purpose of this code.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">1</span><span class="p">],</span> <span class="o">*</span><span class="n">cloud</span><span class="p">)</span> <span class="o">==</span> <span class="o">-</span><span class="mi">1</span><span class="p">)</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
</pre></div>
</div>
<p>These lines are simply loading the cloud from the .pcd file.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">PointIndicesPtr</span> <span class="n">indices</span> <span class="o">=</span> <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointIndices</span><span class="o">&gt;</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointIndices</span> <span class="p">());</span>
  <span class="n">std</span><span class="o">::</span><span class="n">ifstream</span> <span class="n">indices_file</span><span class="p">;</span>
  <span class="n">indices_file</span><span class="p">.</span><span class="n">open</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">2</span><span class="p">],</span> <span class="n">std</span><span class="o">::</span><span class="n">ifstream</span><span class="o">::</span><span class="n">in</span><span class="p">);</span>
  <span class="k">for</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">line</span><span class="p">;</span> <span class="n">std</span><span class="o">::</span><span class="n">getline</span> <span class="p">(</span><span class="n">indices_file</span><span class="p">,</span> <span class="n">line</span><span class="p">);)</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">istringstream</span> <span class="n">in</span> <span class="p">(</span><span class="n">line</span><span class="p">);</span>
    <span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">index</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
    <span class="n">in</span> <span class="o">&gt;&gt;</span> <span class="n">index</span><span class="p">;</span>
    <span class="n">indices</span><span class="o">-&gt;</span><span class="n">indices</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">index</span> <span class="o">-</span> <span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="n">indices_file</span><span class="p">.</span><span class="n">close</span> <span class="p">();</span>
</pre></div>
</div>
<p>Here the indices of points for which RoPS feature must be computed are loaded. You can comment it and compute features for every single point in the cloud.
if you want.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">std</span><span class="o">::</span><span class="n">vector</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Vertices</span><span class="o">&gt;</span> <span class="n">triangles</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">ifstream</span> <span class="n">triangles_file</span><span class="p">;</span>
  <span class="n">triangles_file</span><span class="p">.</span><span class="n">open</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">3</span><span class="p">],</span> <span class="n">std</span><span class="o">::</span><span class="n">ifstream</span><span class="o">::</span><span class="n">in</span><span class="p">);</span>
  <span class="k">for</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">line</span><span class="p">;</span> <span class="n">std</span><span class="o">::</span><span class="n">getline</span> <span class="p">(</span><span class="n">triangles_file</span><span class="p">,</span> <span class="n">line</span><span class="p">);)</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">Vertices</span> <span class="n">triangle</span><span class="p">;</span>
    <span class="n">std</span><span class="o">::</span><span class="n">istringstream</span> <span class="n">in</span> <span class="p">(</span><span class="n">line</span><span class="p">);</span>
    <span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">vertex</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
    <span class="n">in</span> <span class="o">&gt;&gt;</span> <span class="n">vertex</span><span class="p">;</span>
    <span class="n">triangle</span><span class="p">.</span><span class="n">vertices</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">vertex</span> <span class="o">-</span> <span class="mi">1</span><span class="p">);</span>
    <span class="n">in</span> <span class="o">&gt;&gt;</span> <span class="n">vertex</span><span class="p">;</span>
    <span class="n">triangle</span><span class="p">.</span><span class="n">vertices</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">vertex</span> <span class="o">-</span> <span class="mi">1</span><span class="p">);</span>
    <span class="n">in</span> <span class="o">&gt;&gt;</span> <span class="n">vertex</span><span class="p">;</span>
    <span class="n">triangle</span><span class="p">.</span><span class="n">vertices</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">vertex</span> <span class="o">-</span> <span class="mi">1</span><span class="p">);</span>
    <span class="n">triangles</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">triangle</span><span class="p">);</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>These lines are loading the information about the polygons. You can replace them with the code for the triangulation if you have only the point cloud
instead of the mesh.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="kt">float</span> <span class="n">support_radius</span> <span class="o">=</span> <span class="mf">0.0285f</span><span class="p">;</span>
  <span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">number_of_partition_bins</span> <span class="o">=</span> <span class="mi">5</span><span class="p">;</span>
  <span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">number_of_rotations</span> <span class="o">=</span> <span class="mi">3</span><span class="p">;</span>
</pre></div>
</div>
<p>These code defines important algorithm parameters: support radius for local surface cropping, number of partition bins
used to form the distribution matrix and the number of rotations. The last parameter affects the length of the descriptor.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">search_method</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">search_method</span><span class="o">-&gt;</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
</pre></div>
</div>
<p>These lines set up the search method that will be used by the algorithm.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">ROPSEstimation</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Histogram</span> <span class="o">&lt;</span><span class="mi">135</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="n">feature_estimator</span><span class="p">;</span>
  <span class="n">feature_estimator</span><span class="p">.</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">search_method</span><span class="p">);</span>
  <span class="n">feature_estimator</span><span class="p">.</span><span class="n">setSearchSurface</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">feature_estimator</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">feature_estimator</span><span class="p">.</span><span class="n">setIndices</span> <span class="p">(</span><span class="n">indices</span><span class="p">);</span>
  <span class="n">feature_estimator</span><span class="p">.</span><span class="n">setTriangles</span> <span class="p">(</span><span class="n">triangles</span><span class="p">);</span>
  <span class="n">feature_estimator</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="n">support_radius</span><span class="p">);</span>
  <span class="n">feature_estimator</span><span class="p">.</span><span class="n">setNumberOfPartitionBins</span> <span class="p">(</span><span class="n">number_of_partition_bins</span><span class="p">);</span>
  <span class="n">feature_estimator</span><span class="p">.</span><span class="n">setNumberOfRotations</span> <span class="p">(</span><span class="n">number_of_rotations</span><span class="p">);</span>
  <span class="n">feature_estimator</span><span class="p">.</span><span class="n">setSupportRadius</span> <span class="p">(</span><span class="n">support_radius</span><span class="p">);</span>
</pre></div>
</div>
<p>Here is the place where the instantiation of the <tt class="docutils literal"><span class="pre">pcl::ROPSEstimation</span></tt> class takes place. It has two parameters:</p>
<blockquote>
<div><ul class="simple">
<li>PointInT - type of the input points;</li>
<li>PointOutT - type of the output points.</li>
</ul>
</div></blockquote>
<p>Immediately after that we set the input all the necessary data neede for the feature computation.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Histogram</span> <span class="o">&lt;</span><span class="mi">135</span><span class="o">&gt;</span> <span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">histograms</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Histogram</span> <span class="o">&lt;</span><span class="mi">135</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">feature_estimator</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">histograms</span><span class="p">);</span>
</pre></div>
</div>
<p>Here is the place where the computational process is launched.</p>
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

<span class="nb">project</span><span class="p">(</span><span class="s">rops_feature</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.8</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">rops_feature</span> <span class="s">rops_feature.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">rops_feature</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it. Simply do:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./rops_feature points.pcd indices.txt triangles.txt
</pre></div>
</div>
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