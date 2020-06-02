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
    
    <title>Extracting indices from a PointCloud</title>
    
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
            
  <div class="section" id="extracting-indices-from-a-pointcloud">
<span id="extract-indices"></span><h1>Extracting indices from a PointCloud</h1>
<p>In this tutorial we will learn how to use an <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_extract_indices.html">ExtractIndices</a> filter to extract a subset of
points from a point cloud based on the indices output by a segmentation algorithm. In order to not complicate the
tutorial, the segmentation algorithm is not explained here. Please check
the <a class="reference internal" href="planar_segmentation.php#planar-segmentation"><em>Plane model segmentation</em></a> tutorial for more information.</p>
<iframe title="Extracting indices from a PointCloud" width="480" height="390" src="http://www.youtube.com/embed/ZTK7NR1Xx4c?rel=0" frameborder="0" allowfullscreen></iframe></div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>First, download the dataset <a class="reference external" href="https://raw.github.com/PointCloudLibrary/data/master/tutorials/table_scene_lms400.pcd">table_scene_lms400.pcd</a>
and save it somewhere to disk.</p>
<p>Then, create a file, let&#8217;s say, <tt class="docutils literal"><span class="pre">extract_indices.cpp</span></tt> in your favorite
editor, and place the following inside it:</p>
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
85</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;iostream&gt;</span>
<span class="cp">#include &lt;pcl/ModelCoefficients.h&gt;</span>
<span class="cp">#include &lt;pcl/io/pcd_io.h&gt;</span>
<span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/sample_consensus/method_types.h&gt;</span>
<span class="cp">#include &lt;pcl/sample_consensus/model_types.h&gt;</span>
<span class="cp">#include &lt;pcl/segmentation/sac_segmentation.h&gt;</span>
<span class="cp">#include &lt;pcl/filters/voxel_grid.h&gt;</span>
<span class="cp">#include &lt;pcl/filters/extract_indices.h&gt;</span>

<span class="kt">int</span>
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PCLPointCloud2</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">cloud_blob</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PCLPointCloud2</span><span class="p">),</span> <span class="n">cloud_filtered_blob</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PCLPointCloud2</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_filtered</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">),</span> <span class="n">cloud_p</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">),</span> <span class="n">cloud_f</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="c1">// Fill in the cloud data</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PCDReader</span> <span class="n">reader</span><span class="p">;</span>
  <span class="n">reader</span><span class="p">.</span><span class="n">read</span> <span class="p">(</span><span class="s">&quot;table_scene_lms400.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud_blob</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;PointCloud before filtering: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud_blob</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">*</span> <span class="n">cloud_blob</span><span class="o">-&gt;</span><span class="n">height</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; data points.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="c1">// Create the filtering object: downsample the dataset using a leaf size of 1cm</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">VoxelGrid</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PCLPointCloud2</span><span class="o">&gt;</span> <span class="n">sor</span><span class="p">;</span>
  <span class="n">sor</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_blob</span><span class="p">);</span>
  <span class="n">sor</span><span class="p">.</span><span class="n">setLeafSize</span> <span class="p">(</span><span class="mf">0.01f</span><span class="p">,</span> <span class="mf">0.01f</span><span class="p">,</span> <span class="mf">0.01f</span><span class="p">);</span>
  <span class="n">sor</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_filtered_blob</span><span class="p">);</span>

  <span class="c1">// Convert to the templated PointCloud</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">fromPCLPointCloud2</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_filtered_blob</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud_filtered</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;PointCloud after filtering: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud_filtered</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">*</span> <span class="n">cloud_filtered</span><span class="o">-&gt;</span><span class="n">height</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; data points.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="c1">// Write the downsampled version to disk</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PCDWriter</span> <span class="n">writer</span><span class="p">;</span>
  <span class="n">writer</span><span class="p">.</span><span class="n">write</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;table_scene_lms400_downsampled.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud_filtered</span><span class="p">,</span> <span class="nb">false</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">ModelCoefficients</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">coefficients</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">ModelCoefficients</span> <span class="p">());</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointIndices</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">inliers</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointIndices</span> <span class="p">());</span>
  <span class="c1">// Create the segmentation object</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">SACSegmentation</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">seg</span><span class="p">;</span>
  <span class="c1">// Optional</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setOptimizeCoefficients</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
  <span class="c1">// Mandatory</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setModelType</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">SACMODEL_PLANE</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setMethodType</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">SAC_RANSAC</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setMaxIterations</span> <span class="p">(</span><span class="mi">1000</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setDistanceThreshold</span> <span class="p">(</span><span class="mf">0.01</span><span class="p">);</span>

  <span class="c1">// Create the filtering object</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">ExtractIndices</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">extract</span><span class="p">;</span>

  <span class="kt">int</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">,</span> <span class="n">nr_points</span> <span class="o">=</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span> <span class="n">cloud_filtered</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span>
  <span class="c1">// While 30% of the original cloud is still there</span>
  <span class="k">while</span> <span class="p">(</span><span class="n">cloud_filtered</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">&gt;</span> <span class="mf">0.3</span> <span class="o">*</span> <span class="n">nr_points</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="c1">// Segment the largest planar component from the remaining cloud</span>
    <span class="n">seg</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_filtered</span><span class="p">);</span>
    <span class="n">seg</span><span class="p">.</span><span class="n">segment</span> <span class="p">(</span><span class="o">*</span><span class="n">inliers</span><span class="p">,</span> <span class="o">*</span><span class="n">coefficients</span><span class="p">);</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">inliers</span><span class="o">-&gt;</span><span class="n">indices</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">==</span> <span class="mi">0</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Could not estimate a planar model for the given dataset.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
      <span class="k">break</span><span class="p">;</span>
    <span class="p">}</span>

    <span class="c1">// Extract the inliers</span>
    <span class="n">extract</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_filtered</span><span class="p">);</span>
    <span class="n">extract</span><span class="p">.</span><span class="n">setIndices</span> <span class="p">(</span><span class="n">inliers</span><span class="p">);</span>
    <span class="n">extract</span><span class="p">.</span><span class="n">setNegative</span> <span class="p">(</span><span class="nb">false</span><span class="p">);</span>
    <span class="n">extract</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_p</span><span class="p">);</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;PointCloud representing the planar component: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud_p</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">*</span> <span class="n">cloud_p</span><span class="o">-&gt;</span><span class="n">height</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; data points.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

    <span class="n">std</span><span class="o">::</span><span class="n">stringstream</span> <span class="n">ss</span><span class="p">;</span>
    <span class="n">ss</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;table_scene_lms400_plane_&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">i</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;.pcd&quot;</span><span class="p">;</span>
    <span class="n">writer</span><span class="p">.</span><span class="n">write</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">ss</span><span class="p">.</span><span class="n">str</span> <span class="p">(),</span> <span class="o">*</span><span class="n">cloud_p</span><span class="p">,</span> <span class="nb">false</span><span class="p">);</span>

    <span class="c1">// Create the filtering object</span>
    <span class="n">extract</span><span class="p">.</span><span class="n">setNegative</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
    <span class="n">extract</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_f</span><span class="p">);</span>
    <span class="n">cloud_filtered</span><span class="p">.</span><span class="n">swap</span> <span class="p">(</span><span class="n">cloud_f</span><span class="p">);</span>
    <span class="n">i</span><span class="o">++</span><span class="p">;</span>
  <span class="p">}</span>

  <span class="k">return</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>Now, let&#8217;s break down the code piece by piece, skipping the obvious.</p>
<p>After the data has been loaded from the input .PCD file, we create a
<a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_voxel_grid.html">VoxelGrid</a> filter, to downsample the data. The rationale behind data
downsampling here is just to speed things up &#8211; less points means less time
needed to spend within the segmentation loop.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">VoxelGrid</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PCLPointCloud2</span><span class="o">&gt;</span> <span class="n">sor</span><span class="p">;</span>
  <span class="n">sor</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_blob</span><span class="p">);</span>
  <span class="n">sor</span><span class="p">.</span><span class="n">setLeafSize</span> <span class="p">(</span><span class="mf">0.01f</span><span class="p">,</span> <span class="mf">0.01f</span><span class="p">,</span> <span class="mf">0.01f</span><span class="p">);</span>
  <span class="n">sor</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_filtered_blob</span><span class="p">);</span>
</pre></div>
</div>
<p>The next block of code deals with the parametric segmentation. To keep the
tutorial simple, its its explanation will be skipped for now. Please see the
<strong>segmentation</strong> tutorials (in particular <a class="reference internal" href="planar_segmentation.php#planar-segmentation"><em>Plane model segmentation</em></a>) for more
information.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">ModelCoefficients</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">coefficients</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">ModelCoefficients</span> <span class="p">());</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointIndices</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">inliers</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointIndices</span> <span class="p">());</span>
  <span class="c1">// Create the segmentation object</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">SACSegmentation</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">seg</span><span class="p">;</span>
  <span class="c1">// Optional</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setOptimizeCoefficients</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
  <span class="c1">// Mandatory</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setModelType</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">SACMODEL_PLANE</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setMethodType</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">SAC_RANSAC</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setMaxIterations</span> <span class="p">(</span><span class="mi">1000</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setDistanceThreshold</span> <span class="p">(</span><span class="mf">0.01</span><span class="p">);</span>
</pre></div>
</div>
<p>The line</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">ExtractIndices</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">extract</span><span class="p">;</span>
</pre></div>
</div>
<p>and</p>
<div class="highlight-cpp"><div class="highlight"><pre>    <span class="n">extract</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_filtered</span><span class="p">);</span>
    <span class="n">extract</span><span class="p">.</span><span class="n">setIndices</span> <span class="p">(</span><span class="n">inliers</span><span class="p">);</span>
    <span class="n">extract</span><span class="p">.</span><span class="n">setNegative</span> <span class="p">(</span><span class="nb">false</span><span class="p">);</span>
    <span class="n">extract</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_p</span><span class="p">);</span>
</pre></div>
</div>
<p>represent the actual indices <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_extract_indices.html">extraction filter</a>. To process multiple models, we
run the process in a loop, and after each model is extracted, we go back to
obtain the remaining points, and iterate. The <em>inliers</em> are obtained from the segmentation process, as follows:</p>
<div class="highlight-cpp"><div class="highlight"><pre>    <span class="n">seg</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_filtered</span><span class="p">);</span>
    <span class="n">seg</span><span class="p">.</span><span class="n">segment</span> <span class="p">(</span><span class="o">*</span><span class="n">inliers</span><span class="p">,</span> <span class="o">*</span><span class="n">coefficients</span><span class="p">);</span>
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

<span class="nb">project</span><span class="p">(</span><span class="s">extract_indices</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.2</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">extract_indices</span> <span class="s">extract_indices.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">extract_indices</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it. Simply do:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./extract_indices
</pre></div>
</div>
<p>You will see something similar to:</p>
<div class="highlight-python"><div class="highlight"><pre>PointCloud before filtering: 460400 data points.
PointCloud after filtering: 41049 data points.
PointCloud representing the planar component: 20164 data points.
PointCloud representing the planar component: 12129 data points.
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