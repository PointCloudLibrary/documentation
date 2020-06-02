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
    
    <title>Detecting people on a ground plane with RGB-D data</title>
    
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
            
  <div class="section" id="detecting-people-on-a-ground-plane-with-rgb-d-data">
<span id="ground-based-rgbd-people-detection"></span><h1>Detecting people on a ground plane with RGB-D data</h1>
<p>This tutorial aims at explaining how to detect people from RGB-D data with the pcl_people module.
With the proposed method, people standing/walking on a planar ground plane can be detected in real time with standard CPU computation.
This implementation corresponds to the people detection algorithm for RGB-D data presented in</p>
<ul class="simple">
<li><em>M. Munaro, F. Basso and E. Menegatti</em>. &#8220;Tracking people within groups with RGB-D data&#8221;. In Proceedings of the International Conference on Intelligent Robots and Systems (IROS) 2012, Vilamoura (Portugal), 2012.</li>
</ul>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>You can download the source code for this tutorial from <a class="reference download internal" href="_downloads/main_ground_based_people_detection.cpp"><tt class="xref download docutils literal"><span class="pre">here</span></tt></a>,
while the file containing the needed SVM parameters can be found <a class="reference download internal" href="_downloads/trainedLinearSVMForPeopleDetectionWithHOG.yaml"><tt class="xref download docutils literal"><span class="pre">here</span></tt></a>.
We implemented a people detection demo from a live RGB-D stream obtained with an OpenNI-compatible sensor (Microsoft Kinect, Asus Xtion, etc.).</p>
<p>Here it is the code:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="cp">#include &lt;pcl/console/parse.h&gt;</span>
<span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/visualization/pcl_visualizer.h&gt;    </span>
<span class="cp">#include &lt;pcl/io/openni_grabber.h&gt;</span>
<span class="cp">#include &lt;pcl/sample_consensus/sac_model_plane.h&gt;</span>
<span class="cp">#include &lt;pcl/people/ground_based_people_detection_app.h&gt;</span>

<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span> <span class="n">PointT</span><span class="p">;</span>
<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">PointCloudT</span><span class="p">;</span>

<span class="c1">// PCL viewer //</span>
<span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="n">viewer</span><span class="p">(</span><span class="s">&quot;PCL Viewer&quot;</span><span class="p">);</span>

<span class="c1">// Mutex: //</span>
<span class="n">boost</span><span class="o">::</span><span class="n">mutex</span> <span class="n">cloud_mutex</span><span class="p">;</span>

<span class="k">enum</span> <span class="p">{</span> <span class="n">COLS</span> <span class="o">=</span> <span class="mi">640</span><span class="p">,</span> <span class="n">ROWS</span> <span class="o">=</span> <span class="mi">480</span> <span class="p">};</span>

<span class="kt">int</span> <span class="nf">print_help</span><span class="p">()</span>
<span class="p">{</span>
  <span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;*******************************************************&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Ground based people detection app options:&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;   --help    &lt;show_this_help&gt;&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;   --svm     &lt;path_to_svm_file&gt;&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;   --conf    &lt;minimum_HOG_confidence (default = -1.5)&gt;&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;   --min_h   &lt;minimum_person_height (default = 1.3)&gt;&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;   --max_h   &lt;maximum_person_height (default = 2.3)&gt;&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;*******************************************************&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="k">return</span> <span class="mi">0</span><span class="p">;</span>
<span class="p">}</span>

<span class="kt">void</span> <span class="nf">cloud_cb_</span> <span class="p">(</span><span class="k">const</span> <span class="n">PointCloudT</span><span class="o">::</span><span class="n">ConstPtr</span> <span class="o">&amp;</span><span class="n">callback_cloud</span><span class="p">,</span> <span class="n">PointCloudT</span><span class="o">::</span><span class="n">Ptr</span><span class="o">&amp;</span> <span class="n">cloud</span><span class="p">,</span>
    <span class="kt">bool</span><span class="o">*</span> <span class="n">new_cloud_available_flag</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">cloud_mutex</span><span class="p">.</span><span class="n">lock</span> <span class="p">();</span>    <span class="c1">// for not overwriting the point cloud from another thread</span>
  <span class="o">*</span><span class="n">cloud</span> <span class="o">=</span> <span class="o">*</span><span class="n">callback_cloud</span><span class="p">;</span>
  <span class="o">*</span><span class="n">new_cloud_available_flag</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
  <span class="n">cloud_mutex</span><span class="p">.</span><span class="n">unlock</span> <span class="p">();</span>
<span class="p">}</span>

<span class="k">struct</span> <span class="n">callback_args</span><span class="p">{</span>
  <span class="c1">// structure used to pass arguments to the callback function</span>
  <span class="n">PointCloudT</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">clicked_points_3d</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">viewerPtr</span><span class="p">;</span>
<span class="p">};</span>
  
<span class="kt">void</span>
<span class="nf">pp_callback</span> <span class="p">(</span><span class="k">const</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointPickingEvent</span><span class="o">&amp;</span> <span class="n">event</span><span class="p">,</span> <span class="kt">void</span><span class="o">*</span> <span class="n">args</span><span class="p">)</span>
<span class="p">{</span>
  <span class="k">struct</span> <span class="n">callback_args</span><span class="o">*</span> <span class="n">data</span> <span class="o">=</span> <span class="p">(</span><span class="k">struct</span> <span class="n">callback_args</span> <span class="o">*</span><span class="p">)</span><span class="n">args</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">event</span><span class="p">.</span><span class="n">getPointIndex</span> <span class="p">()</span> <span class="o">==</span> <span class="o">-</span><span class="mi">1</span><span class="p">)</span>
    <span class="k">return</span><span class="p">;</span>
  <span class="n">PointT</span> <span class="n">current_point</span><span class="p">;</span>
  <span class="n">event</span><span class="p">.</span><span class="n">getPoint</span><span class="p">(</span><span class="n">current_point</span><span class="p">.</span><span class="n">x</span><span class="p">,</span> <span class="n">current_point</span><span class="p">.</span><span class="n">y</span><span class="p">,</span> <span class="n">current_point</span><span class="p">.</span><span class="n">z</span><span class="p">);</span>
  <span class="n">data</span><span class="o">-&gt;</span><span class="n">clicked_points_3d</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">push_back</span><span class="p">(</span><span class="n">current_point</span><span class="p">);</span>
  <span class="c1">// Draw clicked points in red:</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">red</span> <span class="p">(</span><span class="n">data</span><span class="o">-&gt;</span><span class="n">clicked_points_3d</span><span class="p">,</span> <span class="mi">255</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">data</span><span class="o">-&gt;</span><span class="n">viewerPtr</span><span class="o">-&gt;</span><span class="n">removePointCloud</span><span class="p">(</span><span class="s">&quot;clicked_points&quot;</span><span class="p">);</span>
  <span class="n">data</span><span class="o">-&gt;</span><span class="n">viewerPtr</span><span class="o">-&gt;</span><span class="n">addPointCloud</span><span class="p">(</span><span class="n">data</span><span class="o">-&gt;</span><span class="n">clicked_points_3d</span><span class="p">,</span> <span class="n">red</span><span class="p">,</span> <span class="s">&quot;clicked_points&quot;</span><span class="p">);</span>
  <span class="n">data</span><span class="o">-&gt;</span><span class="n">viewerPtr</span><span class="o">-&gt;</span><span class="n">setPointCloudRenderingProperties</span><span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="s">&quot;clicked_points&quot;</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="n">current_point</span><span class="p">.</span><span class="n">x</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">current_point</span><span class="p">.</span><span class="n">y</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">current_point</span><span class="p">.</span><span class="n">z</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
<span class="p">}</span>

<span class="kt">int</span> <span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="k">if</span><span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_switch</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--help&quot;</span><span class="p">)</span> <span class="o">||</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_switch</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-h&quot;</span><span class="p">))</span>
        <span class="k">return</span> <span class="n">print_help</span><span class="p">();</span>

  <span class="c1">// Algorithm parameters:</span>
  <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">svm_filename</span> <span class="o">=</span> <span class="s">&quot;../../people/data/trainedLinearSVMForPeopleDetectionWithHOG.yaml&quot;</span><span class="p">;</span>
  <span class="kt">float</span> <span class="n">min_confidence</span> <span class="o">=</span> <span class="o">-</span><span class="mf">1.5</span><span class="p">;</span>
  <span class="kt">float</span> <span class="n">min_height</span> <span class="o">=</span> <span class="mf">1.3</span><span class="p">;</span>
  <span class="kt">float</span> <span class="n">max_height</span> <span class="o">=</span> <span class="mf">2.3</span><span class="p">;</span>
  <span class="kt">float</span> <span class="n">voxel_size</span> <span class="o">=</span> <span class="mf">0.06</span><span class="p">;</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix3f</span> <span class="n">rgb_intrinsics_matrix</span><span class="p">;</span>
  <span class="n">rgb_intrinsics_matrix</span> <span class="o">&lt;&lt;</span> <span class="mi">525</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mf">319.5</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mi">525</span><span class="p">,</span> <span class="mf">239.5</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">;</span> <span class="c1">// Kinect RGB camera intrinsics</span>

  <span class="c1">// Read if some parameters are passed from command line:</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--svm&quot;</span><span class="p">,</span> <span class="n">svm_filename</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--conf&quot;</span><span class="p">,</span> <span class="n">min_confidence</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--min_h&quot;</span><span class="p">,</span> <span class="n">min_height</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--max_h&quot;</span><span class="p">,</span> <span class="n">max_height</span><span class="p">);</span>

  <span class="c1">// Read Kinect live stream:</span>
  <span class="n">PointCloudT</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">PointCloudT</span><span class="p">);</span>
  <span class="kt">bool</span> <span class="n">new_cloud_available_flag</span> <span class="o">=</span> <span class="nb">false</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">Grabber</span><span class="o">*</span> <span class="n">interface</span> <span class="o">=</span> <span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">OpenNIGrabber</span><span class="p">();</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">function</span><span class="o">&lt;</span><span class="kt">void</span> <span class="p">(</span><span class="k">const</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span><span class="o">&gt;::</span><span class="n">ConstPtr</span><span class="o">&amp;</span><span class="p">)</span><span class="o">&gt;</span> <span class="n">f</span> <span class="o">=</span>
      <span class="n">boost</span><span class="o">::</span><span class="n">bind</span> <span class="p">(</span><span class="o">&amp;</span><span class="n">cloud_cb_</span><span class="p">,</span> <span class="n">_1</span><span class="p">,</span> <span class="n">cloud</span><span class="p">,</span> <span class="o">&amp;</span><span class="n">new_cloud_available_flag</span><span class="p">);</span>
  <span class="n">interface</span><span class="o">-&gt;</span><span class="n">registerCallback</span> <span class="p">(</span><span class="n">f</span><span class="p">);</span>
  <span class="n">interface</span><span class="o">-&gt;</span><span class="n">start</span> <span class="p">();</span>

  <span class="c1">// Wait for the first frame:</span>
  <span class="k">while</span><span class="p">(</span><span class="o">!</span><span class="n">new_cloud_available_flag</span><span class="p">)</span> 
    <span class="n">boost</span><span class="o">::</span><span class="n">this_thread</span><span class="o">::</span><span class="n">sleep</span><span class="p">(</span><span class="n">boost</span><span class="o">::</span><span class="n">posix_time</span><span class="o">::</span><span class="n">milliseconds</span><span class="p">(</span><span class="mi">1</span><span class="p">));</span>
  <span class="n">new_cloud_available_flag</span> <span class="o">=</span> <span class="nb">false</span><span class="p">;</span>

  <span class="n">cloud_mutex</span><span class="p">.</span><span class="n">lock</span> <span class="p">();</span>    <span class="c1">// for not overwriting the point cloud</span>

  <span class="c1">// Display pointcloud:</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerRGBField</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">rgb</span><span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">rgb</span><span class="p">,</span> <span class="s">&quot;input_cloud&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">setCameraPosition</span><span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">0</span><span class="p">,</span><span class="o">-</span><span class="mi">2</span><span class="p">,</span><span class="mi">0</span><span class="p">,</span><span class="o">-</span><span class="mi">1</span><span class="p">,</span><span class="mi">0</span><span class="p">,</span><span class="mi">0</span><span class="p">);</span>

  <span class="c1">// Add point picking callback to viewer:</span>
  <span class="k">struct</span> <span class="n">callback_args</span> <span class="n">cb_args</span><span class="p">;</span>
  <span class="n">PointCloudT</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">clicked_points_3d</span> <span class="p">(</span><span class="k">new</span> <span class="n">PointCloudT</span><span class="p">);</span>
  <span class="n">cb_args</span><span class="p">.</span><span class="n">clicked_points_3d</span> <span class="o">=</span> <span class="n">clicked_points_3d</span><span class="p">;</span>
  <span class="n">cb_args</span><span class="p">.</span><span class="n">viewerPtr</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">::</span><span class="n">Ptr</span><span class="p">(</span><span class="o">&amp;</span><span class="n">viewer</span><span class="p">);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">registerPointPickingCallback</span> <span class="p">(</span><span class="n">pp_callback</span><span class="p">,</span> <span class="p">(</span><span class="kt">void</span><span class="o">*</span><span class="p">)</span><span class="o">&amp;</span><span class="n">cb_args</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Shift+click on three floor points, then press &#39;Q&#39;...&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="c1">// Spin until &#39;Q&#39; is pressed:</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">spin</span><span class="p">();</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;done.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  
  <span class="n">cloud_mutex</span><span class="p">.</span><span class="n">unlock</span> <span class="p">();</span>    

  <span class="c1">// Ground plane estimation:</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">VectorXf</span> <span class="n">ground_coeffs</span><span class="p">;</span>
  <span class="n">ground_coeffs</span><span class="p">.</span><span class="n">resize</span><span class="p">(</span><span class="mi">4</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">clicked_points_indices</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">clicked_points_3d</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span><span class="p">();</span> <span class="n">i</span><span class="o">++</span><span class="p">)</span>
    <span class="n">clicked_points_indices</span><span class="p">.</span><span class="n">push_back</span><span class="p">(</span><span class="n">i</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">SampleConsensusModelPlane</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">model_plane</span><span class="p">(</span><span class="n">clicked_points_3d</span><span class="p">);</span>
  <span class="n">model_plane</span><span class="p">.</span><span class="n">computeModelCoefficients</span><span class="p">(</span><span class="n">clicked_points_indices</span><span class="p">,</span><span class="n">ground_coeffs</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Ground plane: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">ground_coeffs</span><span class="p">(</span><span class="mi">0</span><span class="p">)</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">ground_coeffs</span><span class="p">(</span><span class="mi">1</span><span class="p">)</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">ground_coeffs</span><span class="p">(</span><span class="mi">2</span><span class="p">)</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">ground_coeffs</span><span class="p">(</span><span class="mi">3</span><span class="p">)</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="c1">// Initialize new viewer:</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="n">viewer</span><span class="p">(</span><span class="s">&quot;PCL Viewer&quot;</span><span class="p">);</span>          <span class="c1">// viewer initialization</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">setCameraPosition</span><span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">0</span><span class="p">,</span><span class="o">-</span><span class="mi">2</span><span class="p">,</span><span class="mi">0</span><span class="p">,</span><span class="o">-</span><span class="mi">1</span><span class="p">,</span><span class="mi">0</span><span class="p">,</span><span class="mi">0</span><span class="p">);</span>

  <span class="c1">// Create classifier for people detection:  </span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">people</span><span class="o">::</span><span class="n">PersonClassifier</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">RGB</span><span class="o">&gt;</span> <span class="n">person_classifier</span><span class="p">;</span>
  <span class="n">person_classifier</span><span class="p">.</span><span class="n">loadSVMFromFile</span><span class="p">(</span><span class="n">svm_filename</span><span class="p">);</span>   <span class="c1">// load trained SVM</span>

  <span class="c1">// People detection app initialization:</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">people</span><span class="o">::</span><span class="n">GroundBasedPeopleDetectionApp</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">people_detector</span><span class="p">;</span>    <span class="c1">// people detection object</span>
  <span class="n">people_detector</span><span class="p">.</span><span class="n">setVoxelSize</span><span class="p">(</span><span class="n">voxel_size</span><span class="p">);</span>                        <span class="c1">// set the voxel size</span>
  <span class="n">people_detector</span><span class="p">.</span><span class="n">setIntrinsics</span><span class="p">(</span><span class="n">rgb_intrinsics_matrix</span><span class="p">);</span>            <span class="c1">// set RGB camera intrinsic parameters</span>
  <span class="n">people_detector</span><span class="p">.</span><span class="n">setClassifier</span><span class="p">(</span><span class="n">person_classifier</span><span class="p">);</span>                <span class="c1">// set person classifier</span>
  <span class="n">people_detector</span><span class="p">.</span><span class="n">setHeightLimits</span><span class="p">(</span><span class="n">min_height</span><span class="p">,</span> <span class="n">max_height</span><span class="p">);</span>         <span class="c1">// set person classifier</span>
<span class="c1">//  people_detector.setSensorPortraitOrientation(true);             // set sensor orientation to vertical</span>

  <span class="c1">// For timing:</span>
  <span class="k">static</span> <span class="kt">unsigned</span> <span class="n">count</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="k">static</span> <span class="kt">double</span> <span class="n">last</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">getTime</span> <span class="p">();</span>

  <span class="c1">// Main loop:</span>
  <span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">viewer</span><span class="p">.</span><span class="n">wasStopped</span><span class="p">())</span>
  <span class="p">{</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">new_cloud_available_flag</span> <span class="o">&amp;&amp;</span> <span class="n">cloud_mutex</span><span class="p">.</span><span class="n">try_lock</span> <span class="p">())</span>    <span class="c1">// if a new cloud is available</span>
    <span class="p">{</span>
      <span class="n">new_cloud_available_flag</span> <span class="o">=</span> <span class="nb">false</span><span class="p">;</span>

      <span class="c1">// Perform people detection on the new cloud:</span>
      <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">people</span><span class="o">::</span><span class="n">PersonCluster</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="n">clusters</span><span class="p">;</span>   <span class="c1">// vector containing persons clusters</span>
      <span class="n">people_detector</span><span class="p">.</span><span class="n">setInputCloud</span><span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
      <span class="n">people_detector</span><span class="p">.</span><span class="n">setGround</span><span class="p">(</span><span class="n">ground_coeffs</span><span class="p">);</span>                    <span class="c1">// set floor coefficients</span>
      <span class="n">people_detector</span><span class="p">.</span><span class="n">compute</span><span class="p">(</span><span class="n">clusters</span><span class="p">);</span>                           <span class="c1">// perform people detection</span>

      <span class="n">ground_coeffs</span> <span class="o">=</span> <span class="n">people_detector</span><span class="p">.</span><span class="n">getGround</span><span class="p">();</span>                 <span class="c1">// get updated floor coefficients</span>

      <span class="c1">// Draw cloud and people bounding boxes in the viewer:</span>
      <span class="n">viewer</span><span class="p">.</span><span class="n">removeAllPointClouds</span><span class="p">();</span>
      <span class="n">viewer</span><span class="p">.</span><span class="n">removeAllShapes</span><span class="p">();</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerRGBField</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">rgb</span><span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
      <span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">rgb</span><span class="p">,</span> <span class="s">&quot;input_cloud&quot;</span><span class="p">);</span>
      <span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">k</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
      <span class="k">for</span><span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">people</span><span class="o">::</span><span class="n">PersonCluster</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="o">&gt;::</span><span class="n">iterator</span> <span class="n">it</span> <span class="o">=</span> <span class="n">clusters</span><span class="p">.</span><span class="n">begin</span><span class="p">();</span> <span class="n">it</span> <span class="o">!=</span> <span class="n">clusters</span><span class="p">.</span><span class="n">end</span><span class="p">();</span> <span class="o">++</span><span class="n">it</span><span class="p">)</span>
      <span class="p">{</span>
        <span class="k">if</span><span class="p">(</span><span class="n">it</span><span class="o">-&gt;</span><span class="n">getPersonConfidence</span><span class="p">()</span> <span class="o">&gt;</span> <span class="n">min_confidence</span><span class="p">)</span>             <span class="c1">// draw only people with confidence above a threshold</span>
        <span class="p">{</span>
          <span class="c1">// draw theoretical person bounding box in the PCL viewer:</span>
          <span class="n">it</span><span class="o">-&gt;</span><span class="n">drawTBoundingBox</span><span class="p">(</span><span class="n">viewer</span><span class="p">,</span> <span class="n">k</span><span class="p">);</span>
          <span class="n">k</span><span class="o">++</span><span class="p">;</span>
        <span class="p">}</span>
      <span class="p">}</span>
      <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="n">k</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; people found&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
      <span class="n">viewer</span><span class="p">.</span><span class="n">spinOnce</span><span class="p">();</span>

      <span class="c1">// Display average framerate:</span>
      <span class="k">if</span> <span class="p">(</span><span class="o">++</span><span class="n">count</span> <span class="o">==</span> <span class="mi">30</span><span class="p">)</span>
      <span class="p">{</span>
        <span class="kt">double</span> <span class="n">now</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">getTime</span> <span class="p">();</span>
        <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Average framerate: &quot;</span> <span class="o">&lt;&lt;</span> <span class="kt">double</span><span class="p">(</span><span class="n">count</span><span class="p">)</span><span class="o">/</span><span class="kt">double</span><span class="p">(</span><span class="n">now</span> <span class="o">-</span> <span class="n">last</span><span class="p">)</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; Hz&quot;</span> <span class="o">&lt;&lt;</span>  <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
        <span class="n">count</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
        <span class="n">last</span> <span class="o">=</span> <span class="n">now</span><span class="p">;</span>
      <span class="p">}</span>
      <span class="n">cloud_mutex</span><span class="p">.</span><span class="n">unlock</span> <span class="p">();</span>
    <span class="p">}</span>
  <span class="p">}</span>

  <span class="k">return</span> <span class="mi">0</span><span class="p">;</span>
<span class="p">}</span>
</pre></div>
</div>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>Now, let&#8217;s break down the code piece by piece.</p>
<p>The first lines allow to print a help text showing the command line parameters that can be set when launching the executable.
No parameter is needed by default, but you can optionally set the path to the file containing the trained SVM
for people detection (<tt class="docutils literal"><span class="pre">--svm</span></tt>) and the minimum HOG confidence allowed (<tt class="docutils literal"><span class="pre">--conf</span></tt>). Moreover, the minimum (<tt class="docutils literal"><span class="pre">min_h</span></tt>) and
maximum (<tt class="docutils literal"><span class="pre">max_h</span></tt>) height of people can be set. If no parameter is set, the default values are used.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">int</span> <span class="nf">print_help</span><span class="p">()</span>
<span class="p">{</span>
  <span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;*******************************************************&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Ground based people detection app options:&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;   --help    &lt;show_this_help&gt;&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;   --svm     &lt;path_to_svm_file&gt;&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;   --conf    &lt;minimum_HOG_confidence (default = -1.5)&gt;&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;   --min_h   &lt;minimum_person_height (default = 1.3)&gt;&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;   --max_h   &lt;maximum_person_height (default = 2.3)&gt;&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;*******************************************************&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="k">return</span> <span class="mi">0</span><span class="p">;</span>
<span class="p">}</span>
</pre></div>
</div>
<p>Here, the callback used for grabbing pointclouds with OpenNI is defined.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">void</span> <span class="nf">cloud_cb_</span> <span class="p">(</span><span class="k">const</span> <span class="n">PointCloudT</span><span class="o">::</span><span class="n">ConstPtr</span> <span class="o">&amp;</span><span class="n">callback_cloud</span><span class="p">,</span> <span class="n">PointCloudT</span><span class="o">::</span><span class="n">Ptr</span><span class="o">&amp;</span> <span class="n">cloud</span><span class="p">,</span>
    <span class="kt">bool</span><span class="o">*</span> <span class="n">new_cloud_available_flag</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">cloud_mutex</span><span class="p">.</span><span class="n">lock</span> <span class="p">();</span>    <span class="c1">// for not overwriting the point cloud from another thread</span>
  <span class="o">*</span><span class="n">cloud</span> <span class="o">=</span> <span class="o">*</span><span class="n">callback_cloud</span><span class="p">;</span>
  <span class="o">*</span><span class="n">new_cloud_available_flag</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
  <span class="n">cloud_mutex</span><span class="p">.</span><span class="n">unlock</span> <span class="p">();</span>
<span class="p">}</span>
</pre></div>
</div>
<p>The people detection algorithm used makes the assumption that people stand/walk on a planar ground plane.
Thus, it requires to know the equation of the ground plane in order to perform people detection.
In this tutorial, the ground plane is manually initialized by the user by selecting three floor points
from the first acquired pointcloud.
In the following lines, the callback function used for ground plane initialization is shown, together with
the structure used to pass arguments to this callback.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">struct</span> <span class="n">callback_args</span><span class="p">{</span>
  <span class="c1">// structure used to pass arguments to the callback function</span>
  <span class="n">PointCloudT</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">clicked_points_3d</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">viewerPtr</span><span class="p">;</span>
<span class="p">};</span>
  
<span class="kt">void</span>
<span class="nf">pp_callback</span> <span class="p">(</span><span class="k">const</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointPickingEvent</span><span class="o">&amp;</span> <span class="n">event</span><span class="p">,</span> <span class="kt">void</span><span class="o">*</span> <span class="n">args</span><span class="p">)</span>
<span class="p">{</span>
  <span class="k">struct</span> <span class="n">callback_args</span><span class="o">*</span> <span class="n">data</span> <span class="o">=</span> <span class="p">(</span><span class="k">struct</span> <span class="n">callback_args</span> <span class="o">*</span><span class="p">)</span><span class="n">args</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">event</span><span class="p">.</span><span class="n">getPointIndex</span> <span class="p">()</span> <span class="o">==</span> <span class="o">-</span><span class="mi">1</span><span class="p">)</span>
    <span class="k">return</span><span class="p">;</span>
  <span class="n">PointT</span> <span class="n">current_point</span><span class="p">;</span>
  <span class="n">event</span><span class="p">.</span><span class="n">getPoint</span><span class="p">(</span><span class="n">current_point</span><span class="p">.</span><span class="n">x</span><span class="p">,</span> <span class="n">current_point</span><span class="p">.</span><span class="n">y</span><span class="p">,</span> <span class="n">current_point</span><span class="p">.</span><span class="n">z</span><span class="p">);</span>
  <span class="n">data</span><span class="o">-&gt;</span><span class="n">clicked_points_3d</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">push_back</span><span class="p">(</span><span class="n">current_point</span><span class="p">);</span>
  <span class="c1">// Draw clicked points in red:</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">red</span> <span class="p">(</span><span class="n">data</span><span class="o">-&gt;</span><span class="n">clicked_points_3d</span><span class="p">,</span> <span class="mi">255</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">data</span><span class="o">-&gt;</span><span class="n">viewerPtr</span><span class="o">-&gt;</span><span class="n">removePointCloud</span><span class="p">(</span><span class="s">&quot;clicked_points&quot;</span><span class="p">);</span>
  <span class="n">data</span><span class="o">-&gt;</span><span class="n">viewerPtr</span><span class="o">-&gt;</span><span class="n">addPointCloud</span><span class="p">(</span><span class="n">data</span><span class="o">-&gt;</span><span class="n">clicked_points_3d</span><span class="p">,</span> <span class="n">red</span><span class="p">,</span> <span class="s">&quot;clicked_points&quot;</span><span class="p">);</span>
  <span class="n">data</span><span class="o">-&gt;</span><span class="n">viewerPtr</span><span class="o">-&gt;</span><span class="n">setPointCloudRenderingProperties</span><span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="s">&quot;clicked_points&quot;</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="n">current_point</span><span class="p">.</span><span class="n">x</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">current_point</span><span class="p">.</span><span class="n">y</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">current_point</span><span class="p">.</span><span class="n">z</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
<span class="p">}</span>
</pre></div>
</div>
<div class="section" id="main">
<h2>Main:</h2>
<p>The main program starts by initializing the main parameters and reading the command line options.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">int</span> <span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="k">if</span><span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_switch</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--help&quot;</span><span class="p">)</span> <span class="o">||</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_switch</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-h&quot;</span><span class="p">))</span>
        <span class="k">return</span> <span class="n">print_help</span><span class="p">();</span>

  <span class="c1">// Algorithm parameters:</span>
  <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">svm_filename</span> <span class="o">=</span> <span class="s">&quot;../../people/data/trainedLinearSVMForPeopleDetectionWithHOG.yaml&quot;</span><span class="p">;</span>
  <span class="kt">float</span> <span class="n">min_confidence</span> <span class="o">=</span> <span class="o">-</span><span class="mf">1.5</span><span class="p">;</span>
  <span class="kt">float</span> <span class="n">min_height</span> <span class="o">=</span> <span class="mf">1.3</span><span class="p">;</span>
  <span class="kt">float</span> <span class="n">max_height</span> <span class="o">=</span> <span class="mf">2.3</span><span class="p">;</span>
  <span class="kt">float</span> <span class="n">voxel_size</span> <span class="o">=</span> <span class="mf">0.06</span><span class="p">;</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix3f</span> <span class="n">rgb_intrinsics_matrix</span><span class="p">;</span>
  <span class="n">rgb_intrinsics_matrix</span> <span class="o">&lt;&lt;</span> <span class="mi">525</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mf">319.5</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mi">525</span><span class="p">,</span> <span class="mf">239.5</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">;</span> <span class="c1">// Kinect RGB camera intrinsics</span>

  <span class="c1">// Read if some parameters are passed from command line:</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--svm&quot;</span><span class="p">,</span> <span class="n">svm_filename</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--conf&quot;</span><span class="p">,</span> <span class="n">min_confidence</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--min_h&quot;</span><span class="p">,</span> <span class="n">min_height</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--max_h&quot;</span><span class="p">,</span> <span class="n">max_height</span><span class="p">);</span>
</pre></div>
</div>
</div>
<div class="section" id="ground-initialization">
<h2>Ground initialization:</h2>
<p>Then, the <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_grabber.html">pcl::Grabber</a> object is initialized in order to acquire RGB-D pointclouds and the program waits for
the first frame.
When the first pointcloud is acquired, it is displayed in the visualizer and the user is requested to select
three floor points by pressing <tt class="docutils literal"><span class="pre">shift+click</span></tt> as reported in the figure below.
After this, <tt class="docutils literal"><span class="pre">Q</span></tt> must be pressed in order to close the visualizer and let the program continue.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Read Kinect live stream:</span>
  <span class="n">PointCloudT</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">PointCloudT</span><span class="p">);</span>
  <span class="kt">bool</span> <span class="n">new_cloud_available_flag</span> <span class="o">=</span> <span class="nb">false</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">Grabber</span><span class="o">*</span> <span class="n">interface</span> <span class="o">=</span> <span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">OpenNIGrabber</span><span class="p">();</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">function</span><span class="o">&lt;</span><span class="kt">void</span> <span class="p">(</span><span class="k">const</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span><span class="o">&gt;::</span><span class="n">ConstPtr</span><span class="o">&amp;</span><span class="p">)</span><span class="o">&gt;</span> <span class="n">f</span> <span class="o">=</span>
      <span class="n">boost</span><span class="o">::</span><span class="n">bind</span> <span class="p">(</span><span class="o">&amp;</span><span class="n">cloud_cb_</span><span class="p">,</span> <span class="n">_1</span><span class="p">,</span> <span class="n">cloud</span><span class="p">,</span> <span class="o">&amp;</span><span class="n">new_cloud_available_flag</span><span class="p">);</span>
  <span class="n">interface</span><span class="o">-&gt;</span><span class="n">registerCallback</span> <span class="p">(</span><span class="n">f</span><span class="p">);</span>
  <span class="n">interface</span><span class="o">-&gt;</span><span class="n">start</span> <span class="p">();</span>

  <span class="c1">// Wait for the first frame:</span>
  <span class="k">while</span><span class="p">(</span><span class="o">!</span><span class="n">new_cloud_available_flag</span><span class="p">)</span> 
    <span class="n">boost</span><span class="o">::</span><span class="n">this_thread</span><span class="o">::</span><span class="n">sleep</span><span class="p">(</span><span class="n">boost</span><span class="o">::</span><span class="n">posix_time</span><span class="o">::</span><span class="n">milliseconds</span><span class="p">(</span><span class="mi">1</span><span class="p">));</span>
  <span class="n">new_cloud_available_flag</span> <span class="o">=</span> <span class="nb">false</span><span class="p">;</span>

  <span class="n">cloud_mutex</span><span class="p">.</span><span class="n">lock</span> <span class="p">();</span>    <span class="c1">// for not overwriting the point cloud</span>

  <span class="c1">// Display pointcloud:</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerRGBField</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">rgb</span><span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">rgb</span><span class="p">,</span> <span class="s">&quot;input_cloud&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">setCameraPosition</span><span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">0</span><span class="p">,</span><span class="o">-</span><span class="mi">2</span><span class="p">,</span><span class="mi">0</span><span class="p">,</span><span class="o">-</span><span class="mi">1</span><span class="p">,</span><span class="mi">0</span><span class="p">,</span><span class="mi">0</span><span class="p">);</span>

  <span class="c1">// Add point picking callback to viewer:</span>
  <span class="k">struct</span> <span class="n">callback_args</span> <span class="n">cb_args</span><span class="p">;</span>
  <span class="n">PointCloudT</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">clicked_points_3d</span> <span class="p">(</span><span class="k">new</span> <span class="n">PointCloudT</span><span class="p">);</span>
  <span class="n">cb_args</span><span class="p">.</span><span class="n">clicked_points_3d</span> <span class="o">=</span> <span class="n">clicked_points_3d</span><span class="p">;</span>
  <span class="n">cb_args</span><span class="p">.</span><span class="n">viewerPtr</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">::</span><span class="n">Ptr</span><span class="p">(</span><span class="o">&amp;</span><span class="n">viewer</span><span class="p">);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">registerPointPickingCallback</span> <span class="p">(</span><span class="n">pp_callback</span><span class="p">,</span> <span class="p">(</span><span class="kt">void</span><span class="o">*</span><span class="p">)</span><span class="o">&amp;</span><span class="n">cb_args</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Shift+click on three floor points, then press &#39;Q&#39;...&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="c1">// Spin until &#39;Q&#39; is pressed:</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">spin</span><span class="p">();</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;done.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  
  <span class="n">cloud_mutex</span><span class="p">.</span><span class="n">unlock</span> <span class="p">();</span>    
</pre></div>
</div>
<a class="reference internal image-reference" href="_images/Screen_floor.jpg"><img alt="_images/Screen_floor.jpg" class="align-center" src="_images/Screen_floor.jpg" style="height: 300pt;" /></a>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">When selecting the floor points, try to click on non collinear points that are distant from each other, in order to improve
plane estimation.</p>
</div>
<p>Given the three points, the ground plane is estimated with a Sample Consensus approach and the plane coefficients are
written to the command window.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Ground plane estimation:</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">VectorXf</span> <span class="n">ground_coeffs</span><span class="p">;</span>
  <span class="n">ground_coeffs</span><span class="p">.</span><span class="n">resize</span><span class="p">(</span><span class="mi">4</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">clicked_points_indices</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">clicked_points_3d</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span><span class="p">();</span> <span class="n">i</span><span class="o">++</span><span class="p">)</span>
    <span class="n">clicked_points_indices</span><span class="p">.</span><span class="n">push_back</span><span class="p">(</span><span class="n">i</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">SampleConsensusModelPlane</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">model_plane</span><span class="p">(</span><span class="n">clicked_points_3d</span><span class="p">);</span>
  <span class="n">model_plane</span><span class="p">.</span><span class="n">computeModelCoefficients</span><span class="p">(</span><span class="n">clicked_points_indices</span><span class="p">,</span><span class="n">ground_coeffs</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Ground plane: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">ground_coeffs</span><span class="p">(</span><span class="mi">0</span><span class="p">)</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">ground_coeffs</span><span class="p">(</span><span class="mi">1</span><span class="p">)</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">ground_coeffs</span><span class="p">(</span><span class="mi">2</span><span class="p">)</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">ground_coeffs</span><span class="p">(</span><span class="mi">3</span><span class="p">)</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
</pre></div>
</div>
<p>In the following lines, we can see the initialization of the SVM classifier by loading the pre-trained parameters
from file.
Moreover, a <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1people_1_1_ground_based_people_detection_app.html">GroundBasedPeopleDetectionApp</a> object is declared and the main
parameters are set. In this example, we can see how to set the voxel size used for downsampling the pointcloud,
the rgb camera intrinsic parameters, the <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1people_1_1_person_classifier.html">PersonClassifier</a> object and the height limits.
Other parameters could be set, such as the sensor orientation. If the sensor is vertically placed, the method
setSensorPortraitOrientation should be used to enable the vertical mode in <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1people_1_1_ground_based_people_detection_app.html">GroundBasedPeopleDetectionApp</a>.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Create classifier for people detection:  </span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">people</span><span class="o">::</span><span class="n">PersonClassifier</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">RGB</span><span class="o">&gt;</span> <span class="n">person_classifier</span><span class="p">;</span>
  <span class="n">person_classifier</span><span class="p">.</span><span class="n">loadSVMFromFile</span><span class="p">(</span><span class="n">svm_filename</span><span class="p">);</span>   <span class="c1">// load trained SVM</span>

  <span class="c1">// People detection app initialization:</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">people</span><span class="o">::</span><span class="n">GroundBasedPeopleDetectionApp</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">people_detector</span><span class="p">;</span>    <span class="c1">// people detection object</span>
  <span class="n">people_detector</span><span class="p">.</span><span class="n">setVoxelSize</span><span class="p">(</span><span class="n">voxel_size</span><span class="p">);</span>                        <span class="c1">// set the voxel size</span>
  <span class="n">people_detector</span><span class="p">.</span><span class="n">setIntrinsics</span><span class="p">(</span><span class="n">rgb_intrinsics_matrix</span><span class="p">);</span>            <span class="c1">// set RGB camera intrinsic parameters</span>
  <span class="n">people_detector</span><span class="p">.</span><span class="n">setClassifier</span><span class="p">(</span><span class="n">person_classifier</span><span class="p">);</span>                <span class="c1">// set person classifier</span>
  <span class="n">people_detector</span><span class="p">.</span><span class="n">setHeightLimits</span><span class="p">(</span><span class="n">min_height</span><span class="p">,</span> <span class="n">max_height</span><span class="p">);</span>         <span class="c1">// set person classifier</span>
<span class="c1">//  people_detector.setSensorPortraitOrientation(true);             // set sensor orientation to vertical</span>
</pre></div>
</div>
</div>
<div class="section" id="main-loop">
<h2>Main loop:</h2>
<p>In the main loop, new frames are acquired and processed until the application is terminated by the user.
The <tt class="docutils literal"><span class="pre">people_detector</span></tt> object receives as input the current cloud and the estimated ground coefficients and
computes people clusters properties, which are stored in <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1people_1_1_person_cluster.html">PersonCluster</a> objects.
The ground plane coefficients are re-estimated at every frame by using the previous frame estimate as initial condition.
This procedure allows to adapt to small changes which can occurr to the ground plane equation if the camera is slowly moving.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Main loop:</span>
  <span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">viewer</span><span class="p">.</span><span class="n">wasStopped</span><span class="p">())</span>
  <span class="p">{</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">new_cloud_available_flag</span> <span class="o">&amp;&amp;</span> <span class="n">cloud_mutex</span><span class="p">.</span><span class="n">try_lock</span> <span class="p">())</span>    <span class="c1">// if a new cloud is available</span>
    <span class="p">{</span>
      <span class="n">new_cloud_available_flag</span> <span class="o">=</span> <span class="nb">false</span><span class="p">;</span>

      <span class="c1">// Perform people detection on the new cloud:</span>
      <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">people</span><span class="o">::</span><span class="n">PersonCluster</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="n">clusters</span><span class="p">;</span>   <span class="c1">// vector containing persons clusters</span>
      <span class="n">people_detector</span><span class="p">.</span><span class="n">setInputCloud</span><span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
      <span class="n">people_detector</span><span class="p">.</span><span class="n">setGround</span><span class="p">(</span><span class="n">ground_coeffs</span><span class="p">);</span>                    <span class="c1">// set floor coefficients</span>
      <span class="n">people_detector</span><span class="p">.</span><span class="n">compute</span><span class="p">(</span><span class="n">clusters</span><span class="p">);</span>                           <span class="c1">// perform people detection</span>

      <span class="n">ground_coeffs</span> <span class="o">=</span> <span class="n">people_detector</span><span class="p">.</span><span class="n">getGround</span><span class="p">();</span>                 <span class="c1">// get updated floor coefficients</span>
</pre></div>
</div>
<p>The last part of the code is devoted to visualization. In particular, a green 3D bounding box is drawn for every
person with HOG confidence above the <tt class="docutils literal"><span class="pre">min_confidence</span></tt> threshold. The width of the bounding box is fixed, while
the height is determined as the distance between the top point of the person cluster and the ground plane.
The average framerate is also shown every 30 frames, to evaluate the runtime performance of the application.
Please note that this framerate includes the time necessary for grabbing the point clouds and for visualization.</p>
<div class="highlight-cpp"><div class="highlight"><pre>      <span class="c1">// Draw cloud and people bounding boxes in the viewer:</span>
      <span class="n">viewer</span><span class="p">.</span><span class="n">removeAllPointClouds</span><span class="p">();</span>
      <span class="n">viewer</span><span class="p">.</span><span class="n">removeAllShapes</span><span class="p">();</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerRGBField</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">rgb</span><span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
      <span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">rgb</span><span class="p">,</span> <span class="s">&quot;input_cloud&quot;</span><span class="p">);</span>
      <span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">k</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
      <span class="k">for</span><span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">people</span><span class="o">::</span><span class="n">PersonCluster</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="o">&gt;::</span><span class="n">iterator</span> <span class="n">it</span> <span class="o">=</span> <span class="n">clusters</span><span class="p">.</span><span class="n">begin</span><span class="p">();</span> <span class="n">it</span> <span class="o">!=</span> <span class="n">clusters</span><span class="p">.</span><span class="n">end</span><span class="p">();</span> <span class="o">++</span><span class="n">it</span><span class="p">)</span>
      <span class="p">{</span>
        <span class="k">if</span><span class="p">(</span><span class="n">it</span><span class="o">-&gt;</span><span class="n">getPersonConfidence</span><span class="p">()</span> <span class="o">&gt;</span> <span class="n">min_confidence</span><span class="p">)</span>             <span class="c1">// draw only people with confidence above a threshold</span>
        <span class="p">{</span>
          <span class="c1">// draw theoretical person bounding box in the PCL viewer:</span>
          <span class="n">it</span><span class="o">-&gt;</span><span class="n">drawTBoundingBox</span><span class="p">(</span><span class="n">viewer</span><span class="p">,</span> <span class="n">k</span><span class="p">);</span>
          <span class="n">k</span><span class="o">++</span><span class="p">;</span>
        <span class="p">}</span>
      <span class="p">}</span>
      <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="n">k</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; people found&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
      <span class="n">viewer</span><span class="p">.</span><span class="n">spinOnce</span><span class="p">();</span>

      <span class="c1">// Display average framerate:</span>
      <span class="k">if</span> <span class="p">(</span><span class="o">++</span><span class="n">count</span> <span class="o">==</span> <span class="mi">30</span><span class="p">)</span>
      <span class="p">{</span>
        <span class="kt">double</span> <span class="n">now</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">getTime</span> <span class="p">();</span>
        <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Average framerate: &quot;</span> <span class="o">&lt;&lt;</span> <span class="kt">double</span><span class="p">(</span><span class="n">count</span><span class="p">)</span><span class="o">/</span><span class="kt">double</span><span class="p">(</span><span class="n">now</span> <span class="o">-</span> <span class="n">last</span><span class="p">)</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; Hz&quot;</span> <span class="o">&lt;&lt;</span>  <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
        <span class="n">count</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
        <span class="n">last</span> <span class="o">=</span> <span class="n">now</span><span class="p">;</span>
      <span class="p">}</span>
      <span class="n">cloud_mutex</span><span class="p">.</span><span class="n">unlock</span> <span class="p">();</span>
</pre></div>
</div>
</div>
</div>
<div class="section" id="compiling-and-running-the-program">
<h1>Compiling and running the program</h1>
<p>Create a <cite>CMakeLists.txt</cite> file and add the following lines into it:</p>
<div class="highlight-cmake"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
 2
 3
 4
 5
 6
 7
 8
 9
10</pre></div></td><td class="code"><div class="highlight"><pre><span class="nb">cmake_minimum_required</span><span class="p">(</span><span class="s">VERSION</span> <span class="s">2.8</span> <span class="s">FATAL_ERROR</span><span class="p">)</span>
<span class="nb">project</span><span class="p">(</span><span class="s">ground_based_rgbd_people_detector</span><span class="p">)</span>
<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.7</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">ground_based_rgbd_people_detector</span> <span class="s">MACOSX_BUNDLE</span> <span class="s">src/main_ground_based_people_detection.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">ground_based_rgbd_people_detector</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<dl class="docutils">
<dt>After you have made the executable, you can run it. Simply do:</dt>
<dd>$ ./ground_based_rgbd_people_detector</dd>
</dl>
<p>The following images show some people detection results on a Kinect RGB-D stream.
The minimum and maximum height for people were set respectively to 1.3 and 2.3 meters, while the
minimum HOG confidence was set to -1.5.</p>
<a class="reference internal image-reference" href="_images/Screen3.jpg"><img alt="_images/Screen3.jpg" src="_images/Screen3.jpg" style="height: 300pt;" /></a>
<a class="reference internal image-reference" href="_images/Screen5.jpg"><img alt="_images/Screen5.jpg" src="_images/Screen5.jpg" style="height: 300pt;" /></a>
<a class="reference internal image-reference" href="_images/Screen8.jpg"><img alt="_images/Screen8.jpg" src="_images/Screen8.jpg" style="height: 300pt;" /></a>
<a class="reference internal image-reference" href="_images/Screen7.jpg"><img alt="_images/Screen7.jpg" src="_images/Screen7.jpg" style="height: 300pt;" /></a>
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