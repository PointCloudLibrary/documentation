<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation - Point Cloud Library (PCL)</title>
</head>

<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8" />
    <title>Cylinder model segmentation &#8212; PCL 0.0 documentation</title>
    <link rel="stylesheet" href="_static/sphinxdoc.css" type="text/css" />
    <link rel="stylesheet" href="_static/pygments.css" type="text/css" />
    <script id="documentation_options" data-url_root="./" src="_static/documentation_options.js"></script>
    <script src="_static/jquery.js"></script>
    <script src="_static/underscore.js"></script>
    <script src="_static/doctools.js"></script>
    <script src="_static/language_data.js"></script>
    <link rel="search" title="Search" href="search.php" />
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

  </head><body>

    <div class="document">
      <div class="documentwrapper">
          <div class="body" role="main">
            
  <div class="section" id="cylinder-model-segmentation">
<span id="cylinder-segmentation"></span><h1>Cylinder model segmentation</h1>
<p>This tutorial exemplifies how to run a Sample Consensus segmentation for
cylindrical models. To make the example a bit more practical, the following
operations are applied to the input dataset (in order):</p>
<ul class="simple">
<li><p>data points further away than 1.5 meters are filtered</p></li>
<li><p>surface normals at each point are estimated</p></li>
<li><p>a plane model (describing the table in our demo dataset) is segmented and saved to disk</p></li>
<li><p>a cylindrical model (describing the mug in our demo dataset) is segmented and saved to disk</p></li>
</ul>
<iframe title="Cylinder model segmentation" width="480" height="390" src="http://www.youtube.com/embed/SjbEDEGAeTk?rel=0" frameborder="0" allowfullscreen></iframe><div class="admonition note">
<p class="admonition-title">Note</p>
<p>The cylindrical model is not perfect due to the presence of noise in the data.</p>
</div>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>First, download the dataset <a class="reference external" href="https://raw.github.com/PointCloudLibrary/data/master/tutorials/table_scene_mug_stereo_textured.pcd">table_scene_mug_stereo_textured.pcd</a>
and save it somewhere to disk.</p>
<p>Then, create a file, let’s say, <code class="docutils literal notranslate"><span class="pre">cylinder_segmentation.cpp</span></code> in your favorite
editor, and place the following inside it:</p>
<div class="highlight-cpp notranslate"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre>  1
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
100
101
102
103
104
105
106
107
108
109
110
111
112
113</pre></div></td><td class="code"><div class="highlight"><pre><span></span><span class="cp">#include</span> <span class="cpf">&lt;pcl/ModelCoefficients.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/io/pcd_io.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/point_types.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/filters/extract_indices.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/filters/passthrough.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/features/normal_3d.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/sample_consensus/method_types.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/sample_consensus/model_types.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/segmentation/sac_segmentation.h&gt;</span><span class="cp"></span>

<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">PointT</span><span class="p">;</span>

<span class="kt">int</span>
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="c1">// All the objects needed</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PCDReader</span> <span class="n">reader</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PassThrough</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">pass</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">NormalEstimation</span><span class="o">&lt;</span><span class="n">PointT</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span> <span class="n">ne</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">SACSegmentationFromNormals</span><span class="o">&lt;</span><span class="n">PointT</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span> <span class="n">seg</span><span class="p">;</span> 
  <span class="n">pcl</span><span class="o">::</span><span class="n">PCDWriter</span> <span class="n">writer</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">ExtractIndices</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">extract</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">ExtractIndices</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span> <span class="n">extract_normals</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">tree</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="p">());</span>

  <span class="c1">// Datasets</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_filtered</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_normals</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_filtered2</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_normals2</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">ModelCoefficients</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">coefficients_plane</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">ModelCoefficients</span><span class="p">),</span> <span class="n">coefficients_cylinder</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">ModelCoefficients</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointIndices</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">inliers_plane</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointIndices</span><span class="p">),</span> <span class="n">inliers_cylinder</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointIndices</span><span class="p">);</span>

  <span class="c1">// Read in the cloud data</span>
  <span class="n">reader</span><span class="p">.</span><span class="n">read</span> <span class="p">(</span><span class="s">&quot;table_scene_mug_stereo_textured.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;PointCloud has: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; data points.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="c1">// Build a passthrough filter to remove spurious NaNs</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">setFilterFieldName</span> <span class="p">(</span><span class="s">&quot;z&quot;</span><span class="p">);</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">setFilterLimits</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mf">1.5</span><span class="p">);</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_filtered</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;PointCloud after filtering has: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud_filtered</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; data points.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="c1">// Estimate point normals</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">tree</span><span class="p">);</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_filtered</span><span class="p">);</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">setKSearch</span> <span class="p">(</span><span class="mi">50</span><span class="p">);</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_normals</span><span class="p">);</span>

  <span class="c1">// Create the segmentation object for the planar model and set all the parameters</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setOptimizeCoefficients</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setModelType</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">SACMODEL_NORMAL_PLANE</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setNormalDistanceWeight</span> <span class="p">(</span><span class="mf">0.1</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setMethodType</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">SAC_RANSAC</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setMaxIterations</span> <span class="p">(</span><span class="mi">100</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setDistanceThreshold</span> <span class="p">(</span><span class="mf">0.03</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_filtered</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setInputNormals</span> <span class="p">(</span><span class="n">cloud_normals</span><span class="p">);</span>
  <span class="c1">// Obtain the plane inliers and coefficients</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">segment</span> <span class="p">(</span><span class="o">*</span><span class="n">inliers_plane</span><span class="p">,</span> <span class="o">*</span><span class="n">coefficients_plane</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Plane coefficients: &quot;</span> <span class="o">&lt;&lt;</span> <span class="o">*</span><span class="n">coefficients_plane</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="c1">// Extract the planar inliers from the input cloud</span>
  <span class="n">extract</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_filtered</span><span class="p">);</span>
  <span class="n">extract</span><span class="p">.</span><span class="n">setIndices</span> <span class="p">(</span><span class="n">inliers_plane</span><span class="p">);</span>
  <span class="n">extract</span><span class="p">.</span><span class="n">setNegative</span> <span class="p">(</span><span class="nb">false</span><span class="p">);</span>

  <span class="c1">// Write the planar inliers to disk</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_plane</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">extract</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_plane</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;PointCloud representing the planar component: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud_plane</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; data points.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">writer</span><span class="p">.</span><span class="n">write</span> <span class="p">(</span><span class="s">&quot;table_scene_mug_stereo_textured_plane.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud_plane</span><span class="p">,</span> <span class="nb">false</span><span class="p">);</span>

  <span class="c1">// Remove the planar inliers, extract the rest</span>
  <span class="n">extract</span><span class="p">.</span><span class="n">setNegative</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
  <span class="n">extract</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_filtered2</span><span class="p">);</span>
  <span class="n">extract_normals</span><span class="p">.</span><span class="n">setNegative</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
  <span class="n">extract_normals</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_normals</span><span class="p">);</span>
  <span class="n">extract_normals</span><span class="p">.</span><span class="n">setIndices</span> <span class="p">(</span><span class="n">inliers_plane</span><span class="p">);</span>
  <span class="n">extract_normals</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_normals2</span><span class="p">);</span>

  <span class="c1">// Create the segmentation object for cylinder segmentation and set all the parameters</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setOptimizeCoefficients</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setModelType</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">SACMODEL_CYLINDER</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setMethodType</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">SAC_RANSAC</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setNormalDistanceWeight</span> <span class="p">(</span><span class="mf">0.1</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setMaxIterations</span> <span class="p">(</span><span class="mi">10000</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setDistanceThreshold</span> <span class="p">(</span><span class="mf">0.05</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setRadiusLimits</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mf">0.1</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_filtered2</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setInputNormals</span> <span class="p">(</span><span class="n">cloud_normals2</span><span class="p">);</span>

  <span class="c1">// Obtain the cylinder inliers and coefficients</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">segment</span> <span class="p">(</span><span class="o">*</span><span class="n">inliers_cylinder</span><span class="p">,</span> <span class="o">*</span><span class="n">coefficients_cylinder</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Cylinder coefficients: &quot;</span> <span class="o">&lt;&lt;</span> <span class="o">*</span><span class="n">coefficients_cylinder</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="c1">// Write the cylinder inliers to disk</span>
  <span class="n">extract</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_filtered2</span><span class="p">);</span>
  <span class="n">extract</span><span class="p">.</span><span class="n">setIndices</span> <span class="p">(</span><span class="n">inliers_cylinder</span><span class="p">);</span>
  <span class="n">extract</span><span class="p">.</span><span class="n">setNegative</span> <span class="p">(</span><span class="nb">false</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_cylinder</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">extract</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_cylinder</span><span class="p">);</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">cloud_cylinder</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">empty</span> <span class="p">())</span> 
    <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Can&#39;t find the cylindrical component.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="k">else</span>
  <span class="p">{</span>
	  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;PointCloud representing the cylindrical component: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud_cylinder</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; data points.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
	  <span class="n">writer</span><span class="p">.</span><span class="n">write</span> <span class="p">(</span><span class="s">&quot;table_scene_mug_stereo_textured_cylinder.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud_cylinder</span><span class="p">,</span> <span class="nb">false</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="k">return</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>The only relevant lines are the lines below, as the other operations are
already described in the other tutorials.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="c1">// Create the segmentation object for cylinder segmentation and set all the parameters</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setOptimizeCoefficients</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setModelType</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">SACMODEL_CYLINDER</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setMethodType</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">SAC_RANSAC</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setNormalDistanceWeight</span> <span class="p">(</span><span class="mf">0.1</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setMaxIterations</span> <span class="p">(</span><span class="mi">10000</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setDistanceThreshold</span> <span class="p">(</span><span class="mf">0.05</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setRadiusLimits</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mf">0.1</span><span class="p">);</span>
</pre></div>
</div>
<p>As seen, we’re using a RANSAC robust estimator to obtain the cylinder
coefficients, and we’re imposing a distance threshold from each inlier point to
the model no greater than 5cm. In addition, we set the surface normals
influence to a weight of 0.1, and we limit the radius of the cylindrical model
to be smaller than 10cm.</p>
</div>
<div class="section" id="compiling-and-running-the-program">
<h1>Compiling and running the program</h1>
<p>Add the following lines to your CMakeLists.txt file:</p>
<div class="highlight-cmake notranslate"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
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
12</pre></div></td><td class="code"><div class="highlight"><pre><span></span><span class="nb">cmake_minimum_required</span><span class="p">(</span><span class="s">VERSION</span> <span class="s">2.8</span> <span class="s">FATAL_ERROR</span><span class="p">)</span>

<span class="nb">project</span><span class="p">(</span><span class="s">cylinder_segmentation</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.2</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">cylinder_segmentation</span> <span class="s">cylinder_segmentation.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">cylinder_segmentation</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it. Simply do:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>$ ./cylinder_segmentation
</pre></div>
</div>
<p>You will see something similar to:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">PointCloud</span> <span class="n">has</span><span class="p">:</span> <span class="mi">307200</span> <span class="n">data</span> <span class="n">points</span><span class="o">.</span>
<span class="n">PointCloud</span> <span class="n">after</span> <span class="n">filtering</span> <span class="n">has</span><span class="p">:</span> <span class="mi">139897</span> <span class="n">data</span> <span class="n">points</span><span class="o">.</span>
<span class="p">[</span><span class="n">pcl</span><span class="p">::</span><span class="n">SACSegmentationFromNormals</span><span class="p">::</span><span class="n">initSACModel</span><span class="p">]</span> <span class="n">Using</span> <span class="n">a</span> <span class="n">model</span> <span class="n">of</span> <span class="nb">type</span><span class="p">:</span> <span class="n">SACMODEL_NORMAL_PLANE</span>
<span class="p">[</span><span class="n">pcl</span><span class="p">::</span><span class="n">SACSegmentationFromNormals</span><span class="p">::</span><span class="n">initSACModel</span><span class="p">]</span> <span class="n">Setting</span> <span class="n">normal</span> <span class="n">distance</span> <span class="n">weight</span> <span class="n">to</span> <span class="mf">0.100000</span>
<span class="p">[</span><span class="n">pcl</span><span class="p">::</span><span class="n">SACSegmentationFromNormals</span><span class="p">::</span><span class="n">initSAC</span><span class="p">]</span> <span class="n">Using</span> <span class="n">a</span> <span class="n">method</span> <span class="n">of</span> <span class="nb">type</span><span class="p">:</span> <span class="n">SAC_RANSAC</span> <span class="k">with</span> <span class="n">a</span> <span class="n">model</span> <span class="n">threshold</span> <span class="n">of</span> <span class="mf">0.030000</span>
<span class="p">[</span><span class="n">pcl</span><span class="p">::</span><span class="n">SACSegmentationFromNormals</span><span class="p">::</span><span class="n">initSAC</span><span class="p">]</span> <span class="n">Setting</span> <span class="n">the</span> <span class="n">maximum</span> <span class="n">number</span> <span class="n">of</span> <span class="n">iterations</span> <span class="n">to</span> <span class="mi">100</span>
<span class="n">Plane</span> <span class="n">coefficients</span><span class="p">:</span> <span class="n">header</span><span class="p">:</span>
  <span class="n">seq</span><span class="p">:</span> <span class="mi">0</span>
  <span class="n">stamp</span><span class="p">:</span> <span class="mf">0.000000000</span>
  <span class="n">frame_id</span><span class="p">:</span>
<span class="n">values</span><span class="p">[]</span>
  <span class="n">values</span><span class="p">[</span><span class="mi">0</span><span class="p">]:</span> <span class="o">-</span><span class="mf">0.0161854</span>
  <span class="n">values</span><span class="p">[</span><span class="mi">1</span><span class="p">]:</span> <span class="mf">0.837724</span>
  <span class="n">values</span><span class="p">[</span><span class="mi">2</span><span class="p">]:</span> <span class="mf">0.545855</span>
  <span class="n">values</span><span class="p">[</span><span class="mi">3</span><span class="p">]:</span> <span class="o">-</span><span class="mf">0.528787</span>

<span class="n">PointCloud</span> <span class="n">representing</span> <span class="n">the</span> <span class="n">planar</span> <span class="n">component</span><span class="p">:</span> <span class="mi">117410</span> <span class="n">data</span> <span class="n">points</span><span class="o">.</span>
<span class="p">[</span><span class="n">pcl</span><span class="p">::</span><span class="n">SACSegmentationFromNormals</span><span class="p">::</span><span class="n">initSACModel</span><span class="p">]</span> <span class="n">Using</span> <span class="n">a</span> <span class="n">model</span> <span class="n">of</span> <span class="nb">type</span><span class="p">:</span> <span class="n">SACMODEL_CYLINDER</span>
<span class="p">[</span><span class="n">pcl</span><span class="p">::</span><span class="n">SACSegmentationFromNormals</span><span class="p">::</span><span class="n">initSACModel</span><span class="p">]</span> <span class="n">Setting</span> <span class="n">radius</span> <span class="n">limits</span> <span class="n">to</span> <span class="mf">0.000000</span><span class="o">/</span><span class="mf">0.100000</span>
<span class="p">[</span><span class="n">pcl</span><span class="p">::</span><span class="n">SACSegmentationFromNormals</span><span class="p">::</span><span class="n">initSACModel</span><span class="p">]</span> <span class="n">Setting</span> <span class="n">normal</span> <span class="n">distance</span> <span class="n">weight</span> <span class="n">to</span> <span class="mf">0.100000</span>
<span class="p">[</span><span class="n">pcl</span><span class="p">::</span><span class="n">SACSegmentationFromNormals</span><span class="p">::</span><span class="n">initSAC</span><span class="p">]</span> <span class="n">Using</span> <span class="n">a</span> <span class="n">method</span> <span class="n">of</span> <span class="nb">type</span><span class="p">:</span> <span class="n">SAC_RANSAC</span> <span class="k">with</span> <span class="n">a</span> <span class="n">model</span> <span class="n">threshold</span> <span class="n">of</span> <span class="mf">0.050000</span>
<span class="p">[</span><span class="n">pcl</span><span class="p">::</span><span class="n">SampleConsensusModelCylinder</span><span class="p">::</span><span class="n">optimizeModelCoefficients</span><span class="p">]</span> <span class="n">LM</span> <span class="n">solver</span> <span class="n">finished</span> <span class="k">with</span> <span class="n">exit</span> <span class="n">code</span> <span class="mi">2</span><span class="p">,</span> <span class="n">having</span> <span class="n">a</span> <span class="n">residual</span> <span class="n">norm</span> <span class="n">of</span> <span class="mf">0.322616</span><span class="o">.</span>
<span class="n">Initial</span> <span class="n">solution</span><span class="p">:</span> <span class="mf">0.0452105</span> <span class="mf">0.0924601</span> <span class="mf">0.790215</span> <span class="mf">0.20495</span> <span class="o">-</span><span class="mf">0.721649</span> <span class="o">-</span><span class="mf">0.661225</span> <span class="mf">0.0422902</span>
<span class="n">Final</span> <span class="n">solution</span><span class="p">:</span> <span class="mf">0.0452105</span> <span class="mf">0.0924601</span> <span class="mf">0.790215</span> <span class="mf">0.20495</span> <span class="o">-</span><span class="mf">0.721649</span> <span class="o">-</span><span class="mf">0.661225</span> <span class="mf">0.0396354</span>
<span class="n">Cylinder</span> <span class="n">coefficients</span><span class="p">:</span> <span class="n">header</span><span class="p">:</span>
  <span class="n">seq</span><span class="p">:</span> <span class="mi">0</span>
  <span class="n">stamp</span><span class="p">:</span> <span class="mf">0.000000000</span>
  <span class="n">frame_id</span><span class="p">:</span>
<span class="n">values</span><span class="p">[]</span>
  <span class="n">values</span><span class="p">[</span><span class="mi">0</span><span class="p">]:</span> <span class="mf">0.0452105</span>
  <span class="n">values</span><span class="p">[</span><span class="mi">1</span><span class="p">]:</span> <span class="mf">0.0924601</span>
  <span class="n">values</span><span class="p">[</span><span class="mi">2</span><span class="p">]:</span> <span class="mf">0.790215</span>
  <span class="n">values</span><span class="p">[</span><span class="mi">3</span><span class="p">]:</span> <span class="mf">0.20495</span>
  <span class="n">values</span><span class="p">[</span><span class="mi">4</span><span class="p">]:</span> <span class="o">-</span><span class="mf">0.721649</span>
  <span class="n">values</span><span class="p">[</span><span class="mi">5</span><span class="p">]:</span> <span class="o">-</span><span class="mf">0.661225</span>
  <span class="n">values</span><span class="p">[</span><span class="mi">6</span><span class="p">]:</span> <span class="mf">0.0396354</span>

<span class="n">PointCloud</span> <span class="n">representing</span> <span class="n">the</span> <span class="n">cylindrical</span> <span class="n">component</span><span class="p">:</span> <span class="mi">8625</span> <span class="n">data</span> <span class="n">points</span><span class="o">.</span>
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