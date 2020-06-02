<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation - Point Cloud Library (PCL)</title>
</head>

<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8" />
    <title>Grabbing point clouds from Ensenso cameras &#8212; PCL 0.0 documentation</title>
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
            
  <div class="section" id="grabbing-point-clouds-from-ensenso-cameras">
<span id="ensenso-cameras"></span><h1><a class="toc-backref" href="#id1">Grabbing point clouds from Ensenso cameras</a></h1>
<p>In this tutorial we will learn how to use the <a class="reference external" href="http://en.ids-imaging.com/">IDS-Imaging</a> Ensenso cameras within PCL. This tutorial will show you how to configure PCL
and how to use the examples to fetch point clouds from the <a class="reference external" href="http://www.ensenso.de/">Ensenso</a>.</p>
<div class="contents topic" id="contents">
<p class="topic-title">Contents</p>
<ul class="simple">
<li><p><a class="reference internal" href="#grabbing-point-clouds-from-ensenso-cameras" id="id1">Grabbing point clouds from Ensenso cameras</a></p>
<ul>
<li><p><a class="reference internal" href="#install-ensenso-drivers" id="id2">Install Ensenso drivers</a></p></li>
<li><p><a class="reference internal" href="#configuring-pcl" id="id3">Configuring PCL</a></p></li>
<li><p><a class="reference internal" href="#using-the-example" id="id4">Using the example</a></p></li>
<li><p><a class="reference internal" href="#extrinsic-calibration" id="id5">Extrinsic calibration</a></p></li>
</ul>
</li>
</ul>
</div>
<div class="section" id="install-ensenso-drivers">
<h2><a class="toc-backref" href="#id2">Install Ensenso drivers</a></h2>
<p>The Ensenso drivers are free (as in beer) and available for download, for each of them follow the instructions provided:</p>
<blockquote>
<div><ul class="simple">
<li><p><a class="reference external" href="http://en.ids-imaging.com/download-ueye.html">uEye</a></p></li>
<li><p><a class="reference external" href="http://www.ensenso.de/download">Ensenso SDK</a></p></li>
</ul>
</div></blockquote>
<p>Plug-in the camera and test if the Ensenso is working, launch <code class="docutils literal notranslate"><span class="pre">nxView</span></code> in your terminal to check if you can actually use the camera.</p>
</div>
<div class="section" id="configuring-pcl">
<h2><a class="toc-backref" href="#id3">Configuring PCL</a></h2>
<p>You need at least PCL 1.8.0 to be able to use the Ensenso cameras. You need to make sure <code class="docutils literal notranslate"><span class="pre">WITH_ENSENSO</span></code> is set to <code class="docutils literal notranslate"><span class="pre">true</span></code> in the CMake
configuration (it should be set to true by default if you have followed the instructions before).</p>
<p>The default following values can be tweaked into cmake if you don’t have a standard installation, for example:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">ENSENSO_ABI_DIR</span>     <span class="o">/</span><span class="n">opt</span><span class="o">/</span><span class="n">ensenso_test</span><span class="o">/</span><span class="n">development</span><span class="o">/</span><span class="n">c</span>
</pre></div>
</div>
<p>You can deactivate building the Ensenso support by setting <code class="docutils literal notranslate"><span class="pre">WITH_ENSENSO</span></code> to false.
Compile and install PCL.</p>
</div>
<div class="section" id="using-the-example">
<h2><a class="toc-backref" href="#id4">Using the example</a></h2>
<p>The <a class="reference external" href="https://github.com/PointCloudLibrary/pcl/blob/master/visualization/tools/ensenso_viewer.cpp">pcl_ensenso_viewer</a> example shows how to
display a point cloud grabbed from an Ensenso device using the <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_ensenso_grabber.html">EnsensoGrabber</a> class.</p>
<p>Note that this program opens the TCP port of the nxLib tree, this allows you to open the nxLib tree with the nxTreeEdit program (port 24000).
The capture parameters (exposure, gain etc..) are set to default values.
If you have performed and stored an extrinsic calibration it will be temporary reset.</p>
<p>If you are using an Ensenso X device you have to calibrate the device before trying to run the PCL driver. If you don’t you will get an error like this:</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="n">ensenso_ptr</span><span class="o">-&gt;</span><span class="n">enumDevices</span> <span class="p">();</span>
<span class="n">ensenso_ptr</span><span class="o">-&gt;</span><span class="n">openTcpPort</span> <span class="p">();</span>
<span class="n">ensenso_ptr</span><span class="o">-&gt;</span><span class="n">openDevice</span> <span class="p">();</span>
<span class="n">ensenso_ptr</span><span class="o">-&gt;</span><span class="n">configureCapture</span> <span class="p">();</span>
<span class="n">ensenso_ptr</span><span class="o">-&gt;</span><span class="n">setExtrinsicCalibration</span> <span class="p">();</span>
</pre></div>
</div>
<p>The code is very similar to the <code class="docutils literal notranslate"><span class="pre">pcl_openni_viewer</span></code>.
All the Ensenso devices connected are listed and then the point cloud are fetched as fast as possible.</p>
<p>Here is an example of the terminal output</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>$ pcl_ensenso_viewer
Initialising nxLib
Number of connected cameras: 1
Serial No    Model   Status
140242   N10-1210-18   Available

Opening Ensenso stereo camera id = 0
FPS: 3.32594
FPS: 3.367
FPS: 3.79441
FPS: 4.01204
FPS: 4.07747
FPS: 4.20309
Closing Ensenso stereo camera
</pre></div>
</div>
<a class="reference internal image-reference" href="_images/ensenso_viewer.jpg"><img alt="_images/ensenso_viewer.jpg" src="_images/ensenso_viewer.jpg" style="height: 550px;" /></a>
<p>Another example is available in <a class="reference external" href="https://github.com/PointCloudLibrary/pcl/blob/master/doc/tutorials/content/sources/ensenso_cameras/">PCL sources</a>, it uses OpenCV to display Ensenso
images and the PCLVisualizer to display the point cloud at the same time.</p>
</div>
<div class="section" id="extrinsic-calibration">
<h2><a class="toc-backref" href="#id5">Extrinsic calibration</a></h2>
<p>If you want to perform extrinsic calibration of the sensor, please first make sure your EnsensoSDK version is greater than 1.3.
A fully automated extrinsic calibration ROS package is available to help you calibrate the sensor mounted on a robot arm,
the package can be found in the <a class="reference external" href="https://gitlab.com/InstitutMaupertuis/ensenso_extrinsic_calibration">Institut Maupertuis repository</a>.</p>
<p>The following video shows the automatic calibration procedure on a Fanuc R1000iA 80f industrial robot:</p>
<iframe width="800" height="500" src="https://www.youtube.com/embed/2g6gdx8fKX8" frameborder="0" allowfullscreen></iframe></div>
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