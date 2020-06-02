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
    
    <title>How to use Normal Distributions Transform</title>
    
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
            
  <div class="section" id="how-to-use-normal-distributions-transform">
<span id="normal-distributions-transform"></span><h1>How to use Normal Distributions Transform</h1>
<p>In this tutorial we will describe how to use the Normal Distributions Transform (NDT) algorithm to determine a rigid transformation between two large point clouds, both over 100,000 points.  The NDT algorithm is a registration algorithm that uses standard optimization techniques applied to statistical models of 3D points to determine the most probable registration between two point clouds.  For more information on the inner workings of the NDT algorithm, see Dr. Martin Magnusson&#8217;s doctoral thesis, “The Three-Dimensional Normal Distributions Transform – an Efficient Representation for Registration, Surface Analysis, and Loop Detection.”</p>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>First, download the datasets <a class="reference external" href="https://raw.github.com/PointCloudLibrary/data/master/tutorials/room_scan1.pcd">room_scan1.pcd</a> and <a class="reference external" href="https://raw.github.com/PointCloudLibrary/data/master/tutorials/room_scan2.pcd">room_scan2.pcd</a> and save them to your disk.  These point clouds contain 360 degree scans of the same room from different perspectives.</p>
<p>Then, create a file in your favorite editor and place the following inside.  I used <tt class="docutils literal"><span class="pre">normal_distributions_transform.cpp</span></tt> for this tutorial.</p>
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
100
101
102
103
104
105
106
107
108
109</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;iostream&gt;</span>
<span class="cp">#include &lt;pcl/io/pcd_io.h&gt;</span>
<span class="cp">#include &lt;pcl/point_types.h&gt;</span>

<span class="cp">#include &lt;pcl/registration/ndt.h&gt;</span>
<span class="cp">#include &lt;pcl/filters/approximate_voxel_grid.h&gt;</span>

<span class="cp">#include &lt;pcl/visualization/pcl_visualizer.h&gt;</span>
<span class="cp">#include &lt;boost/thread/thread.hpp&gt;</span>

<span class="kt">int</span>
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="c1">// Loading first scan of room.</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">target_cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;room_scan1.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">target_cloud</span><span class="p">)</span> <span class="o">==</span> <span class="o">-</span><span class="mi">1</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">PCL_ERROR</span> <span class="p">(</span><span class="s">&quot;Couldn&#39;t read file room_scan1.pcd </span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Loaded &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">target_cloud</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; data points from room_scan1.pcd&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="c1">// Loading second scan of room from new perspective.</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">input_cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;room_scan2.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">input_cloud</span><span class="p">)</span> <span class="o">==</span> <span class="o">-</span><span class="mi">1</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">PCL_ERROR</span> <span class="p">(</span><span class="s">&quot;Couldn&#39;t read file room_scan2.pcd </span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Loaded &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">input_cloud</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; data points from room_scan2.pcd&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="c1">// Filtering input scan to roughly 10% of original size to increase speed of registration.</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">filtered_cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">ApproximateVoxelGrid</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">approximate_voxel_filter</span><span class="p">;</span>
  <span class="n">approximate_voxel_filter</span><span class="p">.</span><span class="n">setLeafSize</span> <span class="p">(</span><span class="mf">0.2</span><span class="p">,</span> <span class="mf">0.2</span><span class="p">,</span> <span class="mf">0.2</span><span class="p">);</span>
  <span class="n">approximate_voxel_filter</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">input_cloud</span><span class="p">);</span>
  <span class="n">approximate_voxel_filter</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">filtered_cloud</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Filtered cloud contains &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">filtered_cloud</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">()</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot; data points from room_scan2.pcd&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="c1">// Initializing Normal Distributions Transform (NDT).</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">NormalDistributionsTransform</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">ndt</span><span class="p">;</span>

  <span class="c1">// Setting scale dependent NDT parameters</span>
  <span class="c1">// Setting minimum transformation difference for termination condition.</span>
  <span class="n">ndt</span><span class="p">.</span><span class="n">setTransformationEpsilon</span> <span class="p">(</span><span class="mf">0.01</span><span class="p">);</span>
  <span class="c1">// Setting maximum step size for More-Thuente line search.</span>
  <span class="n">ndt</span><span class="p">.</span><span class="n">setStepSize</span> <span class="p">(</span><span class="mf">0.1</span><span class="p">);</span>
  <span class="c1">//Setting Resolution of NDT grid structure (VoxelGridCovariance).</span>
  <span class="n">ndt</span><span class="p">.</span><span class="n">setResolution</span> <span class="p">(</span><span class="mf">1.0</span><span class="p">);</span>

  <span class="c1">// Setting max number of registration iterations.</span>
  <span class="n">ndt</span><span class="p">.</span><span class="n">setMaximumIterations</span> <span class="p">(</span><span class="mi">35</span><span class="p">);</span>

  <span class="c1">// Setting point cloud to be aligned.</span>
  <span class="n">ndt</span><span class="p">.</span><span class="n">setInputSource</span> <span class="p">(</span><span class="n">filtered_cloud</span><span class="p">);</span>
  <span class="c1">// Setting point cloud to be aligned to.</span>
  <span class="n">ndt</span><span class="p">.</span><span class="n">setInputTarget</span> <span class="p">(</span><span class="n">target_cloud</span><span class="p">);</span>

  <span class="c1">// Set initial alignment estimate found using robot odometry.</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">AngleAxisf</span> <span class="n">init_rotation</span> <span class="p">(</span><span class="mf">0.6931</span><span class="p">,</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span><span class="o">::</span><span class="n">UnitZ</span> <span class="p">());</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Translation3f</span> <span class="n">init_translation</span> <span class="p">(</span><span class="mf">1.79387</span><span class="p">,</span> <span class="mf">0.720047</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span> <span class="n">init_guess</span> <span class="o">=</span> <span class="p">(</span><span class="n">init_translation</span> <span class="o">*</span> <span class="n">init_rotation</span><span class="p">).</span><span class="n">matrix</span> <span class="p">();</span>

  <span class="c1">// Calculating required rigid transform to align the input cloud to the target cloud.</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">output_cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">ndt</span><span class="p">.</span><span class="n">align</span> <span class="p">(</span><span class="o">*</span><span class="n">output_cloud</span><span class="p">,</span> <span class="n">init_guess</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Normal Distributions Transform has converged:&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">ndt</span><span class="p">.</span><span class="n">hasConverged</span> <span class="p">()</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot; score: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">ndt</span><span class="p">.</span><span class="n">getFitnessScore</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="c1">// Transforming unfiltered, input cloud using found transform.</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">transformPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">input_cloud</span><span class="p">,</span> <span class="o">*</span><span class="n">output_cloud</span><span class="p">,</span> <span class="n">ndt</span><span class="p">.</span><span class="n">getFinalTransformation</span> <span class="p">());</span>

  <span class="c1">// Saving transformed input cloud.</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">savePCDFileASCII</span> <span class="p">(</span><span class="s">&quot;room_scan2_transformed.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">output_cloud</span><span class="p">);</span>

  <span class="c1">// Initializing point cloud visualizer</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span>
  <span class="n">viewer_final</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="p">(</span><span class="s">&quot;3D Viewer&quot;</span><span class="p">));</span>
  <span class="n">viewer_final</span><span class="o">-&gt;</span><span class="n">setBackgroundColor</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>

  <span class="c1">// Coloring and visualizing target cloud (red).</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span>
  <span class="n">target_color</span> <span class="p">(</span><span class="n">target_cloud</span><span class="p">,</span> <span class="mi">255</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">viewer_final</span><span class="o">-&gt;</span><span class="n">addPointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">target_cloud</span><span class="p">,</span> <span class="n">target_color</span><span class="p">,</span> <span class="s">&quot;target cloud&quot;</span><span class="p">);</span>
  <span class="n">viewer_final</span><span class="o">-&gt;</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span>
                                                  <span class="mi">1</span><span class="p">,</span> <span class="s">&quot;target cloud&quot;</span><span class="p">);</span>

  <span class="c1">// Coloring and visualizing transformed input cloud (green).</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span>
  <span class="n">output_color</span> <span class="p">(</span><span class="n">output_cloud</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">255</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">viewer_final</span><span class="o">-&gt;</span><span class="n">addPointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">output_cloud</span><span class="p">,</span> <span class="n">output_color</span><span class="p">,</span> <span class="s">&quot;output cloud&quot;</span><span class="p">);</span>
  <span class="n">viewer_final</span><span class="o">-&gt;</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span>
                                                  <span class="mi">1</span><span class="p">,</span> <span class="s">&quot;output cloud&quot;</span><span class="p">);</span>

  <span class="c1">// Starting visualizer</span>
  <span class="n">viewer_final</span><span class="o">-&gt;</span><span class="n">addCoordinateSystem</span> <span class="p">(</span><span class="mf">1.0</span><span class="p">,</span> <span class="s">&quot;global&quot;</span><span class="p">);</span>
  <span class="n">viewer_final</span><span class="o">-&gt;</span><span class="n">initCameraParameters</span> <span class="p">();</span>

  <span class="c1">// Wait until visualizer window is closed.</span>
  <span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">viewer_final</span><span class="o">-&gt;</span><span class="n">wasStopped</span> <span class="p">())</span>
  <span class="p">{</span>
    <span class="n">viewer_final</span><span class="o">-&gt;</span><span class="n">spinOnce</span> <span class="p">(</span><span class="mi">100</span><span class="p">);</span>
    <span class="n">boost</span><span class="o">::</span><span class="n">this_thread</span><span class="o">::</span><span class="n">sleep</span> <span class="p">(</span><span class="n">boost</span><span class="o">::</span><span class="n">posix_time</span><span class="o">::</span><span class="n">microseconds</span> <span class="p">(</span><span class="mi">100000</span><span class="p">));</span>
  <span class="p">}</span>

  <span class="k">return</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>Now, let&#8217;s breakdown this code piece by piece.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="cp">#include &lt;pcl/registration/ndt.h&gt;</span>
<span class="cp">#include &lt;pcl/filters/approximate_voxel_grid.h&gt;</span>
</pre></div>
</div>
<p>These are the required header files to use Normal Distributions Transform algorithm and a filter used to down sample the data.  The filter can be exchanged for other filters but I have found the approximate voxel filter to produce the best results.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Loading first scan of room.</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">target_cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;room_scan1.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">target_cloud</span><span class="p">)</span> <span class="o">==</span> <span class="o">-</span><span class="mi">1</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">PCL_ERROR</span> <span class="p">(</span><span class="s">&quot;Couldn&#39;t read file room_scan1.pcd </span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Loaded &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">target_cloud</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; data points from room_scan1.pcd&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="c1">// Loading second scan of room from new perspective.</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">input_cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;room_scan2.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">input_cloud</span><span class="p">)</span> <span class="o">==</span> <span class="o">-</span><span class="mi">1</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">PCL_ERROR</span> <span class="p">(</span><span class="s">&quot;Couldn&#39;t read file room_scan2.pcd </span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Loaded &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">input_cloud</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; data points from room_scan2.pcd&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
</pre></div>
</div>
<p>The above code loads the two pcd file into pcl::PointCloud&lt;pcl::PointXYZ&gt; boost shared pointers.  The input cloud will be transformed into the reference frame of the target cloud.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Filtering input scan to roughly 10% of original size to increase speed of registration.</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">filtered_cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">ApproximateVoxelGrid</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">approximate_voxel_filter</span><span class="p">;</span>
  <span class="n">approximate_voxel_filter</span><span class="p">.</span><span class="n">setLeafSize</span> <span class="p">(</span><span class="mf">0.2</span><span class="p">,</span> <span class="mf">0.2</span><span class="p">,</span> <span class="mf">0.2</span><span class="p">);</span>
  <span class="n">approximate_voxel_filter</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">input_cloud</span><span class="p">);</span>
  <span class="n">approximate_voxel_filter</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">filtered_cloud</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Filtered cloud contains &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">filtered_cloud</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">()</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot; data points from room_scan2.pcd&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
</pre></div>
</div>
<p>This section filters the input cloud to improve registration time.  Any filter that downsamples the data uniformly can work for this section.  The target cloud does not need be filtered because voxel grid data structure used by the NDT algorithm does not use individual points, but instead uses the statistical data of the points contained in each of its data structures voxel cells.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Initializing Normal Distributions Transform (NDT).</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">NormalDistributionsTransform</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">ndt</span><span class="p">;</span>
</pre></div>
</div>
<p>Here we create the NDT algorithm with the default values.  The internal data structures are not initialized until later.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Setting scale dependent NDT parameters</span>
  <span class="c1">// Setting minimum transformation difference for termination condition.</span>
  <span class="n">ndt</span><span class="p">.</span><span class="n">setTransformationEpsilon</span> <span class="p">(</span><span class="mf">0.01</span><span class="p">);</span>
  <span class="c1">// Setting maximum step size for More-Thuente line search.</span>
  <span class="n">ndt</span><span class="p">.</span><span class="n">setStepSize</span> <span class="p">(</span><span class="mf">0.1</span><span class="p">);</span>
  <span class="c1">//Setting Resolution of NDT grid structure (VoxelGridCovariance).</span>
  <span class="n">ndt</span><span class="p">.</span><span class="n">setResolution</span> <span class="p">(</span><span class="mf">1.0</span><span class="p">);</span>
</pre></div>
</div>
<p>Next we need to modify some of the scale dependent parameters.  Because the NDT algorithm uses a voxelized data structure and More-Thuente line search, some parameters need to be scaled to fit the data set.  The above parameters seem to work well on the scale we are working with, size of a room, but they would need to be significantly decreased to handle smaller objects, such as scans of a coffee mug.</p>
<p>The Transformation Epsilon parameter defines minimum, allowable,  incremental change of the transformation vector, [x, y, z, roll, pitch, yaw] in meters and radians respectively.  Once the incremental change dips below this threshold, the alignment terminates.  The Step Size parameter defines the maximum step length allowed by the More-Thuente line search.  This line search algorithm determines the best step length below this maximum value, shrinking the step length as you near the optimal solution.  Larger maximum step lengths will be able to clear greater distances in fewer iterations but run the risk of overshooting and ending up in an undesirable local minimum.  Finally, the Resolution parameter defines the voxel resolution of the internal NDT grid structure.  This structure is easily searchable and each voxel contain the statistical data, mean, covariance, etc., associated with the points it contains.  The statistical data is used to model the cloud as a set of multivariate Gaussian distributions and allows us to calculate and optimize the probability of the existence of points at any position within the voxel.  This parameter is the most scale dependent.  It needs to be large enough for each voxel to contain at least  6 points but small enough to uniquely describe the environment.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Setting max number of registration iterations.</span>
  <span class="n">ndt</span><span class="p">.</span><span class="n">setMaximumIterations</span> <span class="p">(</span><span class="mi">35</span><span class="p">);</span>
</pre></div>
</div>
<p>This parameter controls the maximum number of iterations the optimizer can run.  For the most part, the optimizer will terminate on the Transformation Epsilon before hitting this limit but this helps prevent it from running for too long in the wrong direction.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Setting point cloud to be aligned.</span>
  <span class="n">ndt</span><span class="p">.</span><span class="n">setInputSource</span> <span class="p">(</span><span class="n">filtered_cloud</span><span class="p">);</span>
  <span class="c1">// Setting point cloud to be aligned to.</span>
  <span class="n">ndt</span><span class="p">.</span><span class="n">setInputTarget</span> <span class="p">(</span><span class="n">target_cloud</span><span class="p">);</span>
</pre></div>
</div>
<p>Here, we pass the point clouds to the NDT registration program.  The input cloud is the cloud that will be transformed and the target cloud is the reference frame to which the input cloud will be aligned.  When the target cloud is added, the NDT algorithm&#8217;s internal data structure is initialized using the target cloud data.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Set initial alignment estimate found using robot odometry.</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">AngleAxisf</span> <span class="n">init_rotation</span> <span class="p">(</span><span class="mf">0.6931</span><span class="p">,</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span><span class="o">::</span><span class="n">UnitZ</span> <span class="p">());</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Translation3f</span> <span class="n">init_translation</span> <span class="p">(</span><span class="mf">1.79387</span><span class="p">,</span> <span class="mf">0.720047</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span> <span class="n">init_guess</span> <span class="o">=</span> <span class="p">(</span><span class="n">init_translation</span> <span class="o">*</span> <span class="n">init_rotation</span><span class="p">).</span><span class="n">matrix</span> <span class="p">();</span>
</pre></div>
</div>
<p>In this section of code, we create an initial guess about the transformation needed to align the point clouds.  Though the algorithm can be run without such an initial transformation, you tend to get better results with one, particularly if there is a large discrepancy between reference frames.  In robotic applications, such as the ones used to generate this data set, the initial transformation is usually generated using odometry data.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Calculating required rigid transform to align the input cloud to the target cloud.</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">output_cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">ndt</span><span class="p">.</span><span class="n">align</span> <span class="p">(</span><span class="o">*</span><span class="n">output_cloud</span><span class="p">,</span> <span class="n">init_guess</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Normal Distributions Transform has converged:&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">ndt</span><span class="p">.</span><span class="n">hasConverged</span> <span class="p">()</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot; score: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">ndt</span><span class="p">.</span><span class="n">getFitnessScore</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
</pre></div>
</div>
<p>Finally, we are ready to align the point clouds.  The resulting transformed input cloud is stored in the output cloud.  We then display the results of the alignment as well as the Euclidean fitness score, calculated as the sum of squared distances from the output cloud to the closest point in the target cloud.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Transforming unfiltered, input cloud using found transform.</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">transformPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">input_cloud</span><span class="p">,</span> <span class="o">*</span><span class="n">output_cloud</span><span class="p">,</span> <span class="n">ndt</span><span class="p">.</span><span class="n">getFinalTransformation</span> <span class="p">());</span>

  <span class="c1">// Saving transformed input cloud.</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">savePCDFileASCII</span> <span class="p">(</span><span class="s">&quot;room_scan2_transformed.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">output_cloud</span><span class="p">);</span>
</pre></div>
</div>
<p>Immediately after the alignment process, the output cloud will contain a transformed version of the filtered input cloud because we passed the algorithm a filtered point cloud, as opposed to the original input cloud.  To obtain the aligned version of the original cloud, we extract the final transformation from the NDT algorithm and transform our original input cloud.  We can now save this cloud to file <tt class="docutils literal"><span class="pre">room_scan2_transformed.pcd</span></tt> for future use.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Initializing point cloud visualizer</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span>
  <span class="n">viewer_final</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="p">(</span><span class="s">&quot;3D Viewer&quot;</span><span class="p">));</span>
  <span class="n">viewer_final</span><span class="o">-&gt;</span><span class="n">setBackgroundColor</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>

  <span class="c1">// Coloring and visualizing target cloud (red).</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span>
  <span class="n">target_color</span> <span class="p">(</span><span class="n">target_cloud</span><span class="p">,</span> <span class="mi">255</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">viewer_final</span><span class="o">-&gt;</span><span class="n">addPointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">target_cloud</span><span class="p">,</span> <span class="n">target_color</span><span class="p">,</span> <span class="s">&quot;target cloud&quot;</span><span class="p">);</span>
  <span class="n">viewer_final</span><span class="o">-&gt;</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span>
                                                  <span class="mi">1</span><span class="p">,</span> <span class="s">&quot;target cloud&quot;</span><span class="p">);</span>

  <span class="c1">// Coloring and visualizing transformed input cloud (green).</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span>
  <span class="n">output_color</span> <span class="p">(</span><span class="n">output_cloud</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">255</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">viewer_final</span><span class="o">-&gt;</span><span class="n">addPointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">output_cloud</span><span class="p">,</span> <span class="n">output_color</span><span class="p">,</span> <span class="s">&quot;output cloud&quot;</span><span class="p">);</span>
  <span class="n">viewer_final</span><span class="o">-&gt;</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span>
                                                  <span class="mi">1</span><span class="p">,</span> <span class="s">&quot;output cloud&quot;</span><span class="p">);</span>

  <span class="c1">// Starting visualizer</span>
  <span class="n">viewer_final</span><span class="o">-&gt;</span><span class="n">addCoordinateSystem</span> <span class="p">(</span><span class="mf">1.0</span><span class="p">,</span> <span class="s">&quot;global&quot;</span><span class="p">);</span>
  <span class="n">viewer_final</span><span class="o">-&gt;</span><span class="n">initCameraParameters</span> <span class="p">();</span>

  <span class="c1">// Wait until visualizer window is closed.</span>
  <span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">viewer_final</span><span class="o">-&gt;</span><span class="n">wasStopped</span> <span class="p">())</span>
  <span class="p">{</span>
    <span class="n">viewer_final</span><span class="o">-&gt;</span><span class="n">spinOnce</span> <span class="p">(</span><span class="mi">100</span><span class="p">);</span>
    <span class="n">boost</span><span class="o">::</span><span class="n">this_thread</span><span class="o">::</span><span class="n">sleep</span> <span class="p">(</span><span class="n">boost</span><span class="o">::</span><span class="n">posix_time</span><span class="o">::</span><span class="n">microseconds</span> <span class="p">(</span><span class="mi">100000</span><span class="p">));</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>This next part is unnecessary but I like to visually see the results of my labors.  With PCL&#8217;s  visualizer classes, this can be easily accomplished.  We first generate a visualizer with a black background.  Then we colorize our target and output cloud, red and green respectively, and load them into the visualizer.  Finally we start the visualizer and wait for the window to be closed.</p>
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
12
13</pre></div></td><td class="code"><div class="highlight"><pre><span class="nb">cmake_minimum_required</span><span class="p">(</span><span class="s">VERSION</span> <span class="s">2.8</span> <span class="s">FATAL_ERROR</span><span class="p">)</span>

<span class="nb">project</span><span class="p">(</span><span class="s">normal_distributions_transform</span><span class="p">)</span>

<span class="nb">FIND_PACKAGE</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.5</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>


<span class="nb">add_executable</span><span class="p">(</span><span class="s">normal_distributions_transform</span> <span class="s">normal_distributions_transform.cpp</span> <span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">normal_distributions_transform</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it. Simply do:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./normal_distributions_transform
</pre></div>
</div>
<p>You should see results similar those below as well as a visualization of the aligned point clouds.  Happy Coding:</p>
<div class="highlight-python"><div class="highlight"><pre>Loaded 112586 data points from room_scan1.pcd
Loaded 112624 data points from room_scan2.pcd
Filtered cloud contains 12433 data points from room_scan2.pcd
Normal Distributions Transform has converged:1 score: 0.638694
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