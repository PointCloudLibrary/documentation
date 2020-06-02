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
    
    <title>Construct a concave or convex hull polygon for a plane model</title>
    
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
            
  <div class="section" id="construct-a-concave-or-convex-hull-polygon-for-a-plane-model">
<span id="hull-2d"></span><h1>Construct a concave or convex hull polygon for a plane model</h1>
<p>In this tutorial we will learn how to calculate a simple 2D hull polygon
(concave or convex) for a set of points supported by a plane.</p>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>First, download the dataset <a class="reference external" href="https://raw.github.com/PointCloudLibrary/data/master/tutorials/table_scene_mug_stereo_textured.pcd">table_scene_mug_stereo_textured.pcd</a>
and save it somewhere to disk.</p>
<p>Then, create a file, let&#8217;s say, <tt class="docutils literal"><span class="pre">concave_hull_2d.cpp</span></tt> or
<tt class="docutils literal"><span class="pre">convex_hull2d.cpp</span></tt> in your favorite editor and place the following inside:</p>
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
69</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;pcl/ModelCoefficients.h&gt;</span>
<span class="cp">#include &lt;pcl/io/pcd_io.h&gt;</span>
<span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/sample_consensus/method_types.h&gt;</span>
<span class="cp">#include &lt;pcl/sample_consensus/model_types.h&gt;</span>
<span class="cp">#include &lt;pcl/filters/passthrough.h&gt;</span>
<span class="cp">#include &lt;pcl/filters/project_inliers.h&gt;</span>
<span class="cp">#include &lt;pcl/segmentation/sac_segmentation.h&gt;</span>
<span class="cp">#include &lt;pcl/surface/concave_hull.h&gt;</span>

<span class="kt">int</span>
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">),</span> 
                                      <span class="n">cloud_filtered</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">),</span> 
                                      <span class="n">cloud_projected</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PCDReader</span> <span class="n">reader</span><span class="p">;</span>

  <span class="n">reader</span><span class="p">.</span><span class="n">read</span> <span class="p">(</span><span class="s">&quot;table_scene_mug_stereo_textured.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud</span><span class="p">);</span>
  <span class="c1">// Build a filter to remove spurious NaNs</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PassThrough</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">pass</span><span class="p">;</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">setFilterFieldName</span> <span class="p">(</span><span class="s">&quot;z&quot;</span><span class="p">);</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">setFilterLimits</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mf">1.1</span><span class="p">);</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_filtered</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;PointCloud after filtering has: &quot;</span>
            <span class="o">&lt;&lt;</span> <span class="n">cloud_filtered</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; data points.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">ModelCoefficients</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">coefficients</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">ModelCoefficients</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointIndices</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">inliers</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointIndices</span><span class="p">);</span>
  <span class="c1">// Create the segmentation object</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">SACSegmentation</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">seg</span><span class="p">;</span>
  <span class="c1">// Optional</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setOptimizeCoefficients</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
  <span class="c1">// Mandatory</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setModelType</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">SACMODEL_PLANE</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setMethodType</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">SAC_RANSAC</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setDistanceThreshold</span> <span class="p">(</span><span class="mf">0.01</span><span class="p">);</span>

  <span class="n">seg</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_filtered</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">segment</span> <span class="p">(</span><span class="o">*</span><span class="n">inliers</span><span class="p">,</span> <span class="o">*</span><span class="n">coefficients</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;PointCloud after segmentation has: &quot;</span>
            <span class="o">&lt;&lt;</span> <span class="n">inliers</span><span class="o">-&gt;</span><span class="n">indices</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; inliers.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="c1">// Project the model inliers</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">ProjectInliers</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">proj</span><span class="p">;</span>
  <span class="n">proj</span><span class="p">.</span><span class="n">setModelType</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">SACMODEL_PLANE</span><span class="p">);</span>
  <span class="n">proj</span><span class="p">.</span><span class="n">setIndices</span> <span class="p">(</span><span class="n">inliers</span><span class="p">);</span>
  <span class="n">proj</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_filtered</span><span class="p">);</span>
  <span class="n">proj</span><span class="p">.</span><span class="n">setModelCoefficients</span> <span class="p">(</span><span class="n">coefficients</span><span class="p">);</span>
  <span class="n">proj</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_projected</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;PointCloud after projection has: &quot;</span>
            <span class="o">&lt;&lt;</span> <span class="n">cloud_projected</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; data points.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="c1">// Create a Concave Hull representation of the projected inliers</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_hull</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">ConcaveHull</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">chull</span><span class="p">;</span>
  <span class="n">chull</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_projected</span><span class="p">);</span>
  <span class="n">chull</span><span class="p">.</span><span class="n">setAlpha</span> <span class="p">(</span><span class="mf">0.1</span><span class="p">);</span>
  <span class="n">chull</span><span class="p">.</span><span class="n">reconstruct</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_hull</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Concave hull has: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud_hull</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot; data points.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PCDWriter</span> <span class="n">writer</span><span class="p">;</span>
  <span class="n">writer</span><span class="p">.</span><span class="n">write</span> <span class="p">(</span><span class="s">&quot;table_scene_mug_stereo_textured_hull.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud_hull</span><span class="p">,</span> <span class="nb">false</span><span class="p">);</span>

  <span class="k">return</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">This tutorial is written for assuming you are looking for the <strong>CONCAVE</strong> hull.
If you would like the <strong>CONVEX</strong> hull for a plane model, just replace concave
with convex at EVERY point in this tutorial, including the source file, file
names and the CMakeLists.txt file. You will also need to comment out
setAlpha(), as this is not applicable to convex hulls.</p>
</div>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>In the following lines of code, a segmentation object is created and some
parameters are set.  We use the SACMODEL_PLANE to segment this PointCloud, and
the method used to find this model is SAC_RANSAC.  The actual segmentation
takes place when <cite>seg.segment (*inliers, *coefficents);</cite> is called.  This
function stores all of the inlying points (on the plane) to <cite>inliers</cite>, and it
stores the coefficents to the plane <cite>(a * x + b * y + c * z = d)</cite> in
<cite>coefficients</cite>.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">PointIndices</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">inliers</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointIndices</span><span class="p">);</span>
  <span class="c1">// Create the segmentation object</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">SACSegmentation</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">seg</span><span class="p">;</span>
  <span class="c1">// Optional</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setOptimizeCoefficients</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
  <span class="c1">// Mandatory</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setModelType</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">SACMODEL_PLANE</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setMethodType</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">SAC_RANSAC</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setDistanceThreshold</span> <span class="p">(</span><span class="mf">0.01</span><span class="p">);</span>

  <span class="n">seg</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_filtered</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">segment</span> <span class="p">(</span><span class="o">*</span><span class="n">inliers</span><span class="p">,</span> <span class="o">*</span><span class="n">coefficients</span><span class="p">);</span>
</pre></div>
</div>
<p>The next bit of code projects the inliers onto the plane model and creates
another cloud.  One way that we could do this is by just extracting the inliers
that we found before, but in this case we are going to use the coefficents we
found before.  We set the model type we are looking for and then set the
coefficents, and from that the object knows which points to project from
cloud_filtered to cloud_projected.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">ProjectInliers</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">proj</span><span class="p">;</span>
  <span class="n">proj</span><span class="p">.</span><span class="n">setModelType</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">SACMODEL_PLANE</span><span class="p">);</span>
  <span class="n">proj</span><span class="p">.</span><span class="n">setIndices</span> <span class="p">(</span><span class="n">inliers</span><span class="p">);</span>
  <span class="n">proj</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_filtered</span><span class="p">);</span>
  <span class="n">proj</span><span class="p">.</span><span class="n">setModelCoefficients</span> <span class="p">(</span><span class="n">coefficients</span><span class="p">);</span>
  <span class="n">proj</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_projected</span><span class="p">);</span>
</pre></div>
</div>
<p>The real interesting part is in the lines below, where the ConcaveHull object
gets created and the reconstruction is performed:</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_hull</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">ConcaveHull</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">chull</span><span class="p">;</span>
  <span class="n">chull</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_projected</span><span class="p">);</span>
  <span class="n">chull</span><span class="p">.</span><span class="n">setAlpha</span> <span class="p">(</span><span class="mf">0.1</span><span class="p">);</span>
  <span class="n">chull</span><span class="p">.</span><span class="n">reconstruct</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_hull</span><span class="p">);</span>
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

<span class="nb">project</span><span class="p">(</span><span class="s">concave_hull_2d</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.2</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">concave_hull_2d</span> <span class="s">concave_hull_2d.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">concave_hull_2d</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it. Simply do:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./concave_hull_2d
</pre></div>
</div>
<p>You will see something similar to:</p>
<div class="highlight-python"><div class="highlight"><pre>PointCloud after filtering has: 139656 data points.
PointCloud after segmentation has: 123727 inliers.
PointCloud after projection has: 139656 data points.
Concave hull has: 457 data points.
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