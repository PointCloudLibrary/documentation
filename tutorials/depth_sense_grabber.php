<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation - Point Cloud Library (PCL)</title>
</head>

<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8" />
    <title>Grabbing point clouds from DepthSense cameras &#8212; PCL 0.0 documentation</title>
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
            
  <div class="section" id="grabbing-point-clouds-from-depthsense-cameras">
<span id="depth-sense-grabber"></span><h1><a class="toc-backref" href="#id2">Grabbing point clouds from DepthSense cameras</a></h1>
<p>In PCL 1.8.0 a new grabber for <a class="reference external" href="http://www.softkinetic.com/Products/DepthSenseCameras">DepthSense</a>
cameras was added. It is based on DepthSense SDK and, as such, should work with
any camera supported by the SDK (e.g. <a class="reference external" href="http://us.creative.com/p/web-cameras/creative-senz3d">Creative Senz3D</a>,
<a class="reference external" href="http://www.softkinetic.com/Store/ProductID/6">DepthSense DS325</a>).</p>
<div class="admonition note">
<p class="admonition-title">Note</p>
<p>This grabber obsoletes <cite>PXCGrabber</cite>, which was a Windows-only solution
based on discontinued <a class="reference external" href="https://web.archive.org/web/20141228120859/https://software.intel.com/en-us/perceptual-computing-sdk">Intel Perceptual Computing SDK</a>.</p>
</div>
<p>In this tutorial we will learn how to setup and use DepthSense cameras within
PCL on both Linux and Windows platforms.</p>
<img alt="_images/creative_camera.jpg" class="align-center" src="_images/creative_camera.jpg" />
<div class="contents topic" id="contents">
<p class="topic-title">Contents</p>
<ul class="simple">
<li><p><a class="reference internal" href="#grabbing-point-clouds-from-depthsense-cameras" id="id2">Grabbing point clouds from DepthSense cameras</a></p></li>
<li><p><a class="reference internal" href="#depthsense-sdk-installation" id="id3">DepthSense SDK installation</a></p>
<ul>
<li><p><a class="reference internal" href="#linux" id="id4">Linux</a></p></li>
<li><p><a class="reference internal" href="#windows" id="id5">Windows</a></p></li>
</ul>
</li>
<li><p><a class="reference internal" href="#pcl-configuration" id="id6">PCL configuration</a></p></li>
<li><p><a class="reference internal" href="#depthsense-viewer" id="id7">DepthSense Viewer</a></p></li>
</ul>
</div>
</div>
<div class="section" id="depthsense-sdk-installation">
<h1><a class="toc-backref" href="#id3">DepthSense SDK installation</a></h1>
<p>Download and install the SDK from <a class="reference external" href="http://www.softkinetic.com/support/download.aspx">SoftKinetic website</a>.
Note that to obtain Linux drivers you need to register (free of charge).</p>
<div class="section" id="linux">
<h2><a class="toc-backref" href="#id4">Linux</a></h2>
<p>The Linux version of camera driver was built against an outdated version of
<cite>libudev</cite>, so it will not work unless you have version 0.13 of this library
installed (for example Ubuntu 14.04 comes with a newer version). There are
several easy ways to solve this problem, see <a class="reference external" href="https://web.archive.org/web/20150326145256/http://choorucode.com/2014/05/06/depthsense-error-some-dll-files-are-missing/">this</a>
or <a class="reference external" href="https://ph4m.wordpress.com/2014/02/11/getting-softkinetics-depthsense-sdk-to-work-on-arch-linux/">this</a>
blog post.</p>
<p>Furthermore, the Linux version of SDK is shipped with its own <cite>libusb-1.0.so</cite>
library. You may have this library already installed on your system (e.g.
because it is required by some other grabbers). In this case there will be
conflicts, which will manifest in a flood of CMake warnings during configuration
stage. To avoid this simply delete the corresponding files from the SDK
installation path:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>$ sudo rm /opt/softkinetic/DepthSenseSDK/lib/libusb-1.0*
</pre></div>
</div>
<p>You can verify your installation by plugging in the camera and running the
viewer app distributed with the SDK:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>$ /opt/softkinetic/DepthSenseSDK/bin/DepthSenseViewer --standalone
</pre></div>
</div>
</div>
<div class="section" id="windows">
<h2><a class="toc-backref" href="#id5">Windows</a></h2>
<p>After the installation is completed you need to add the SDK path to the <cite>PATH</cite>
environment variable. The installation path itself is stored in
<cite>DEPTHSENSESDK64</cite> (on a 64-bit system) environment variable, thus you need to
append <cite>;%DEPTHSENSESDK64%\bin</cite> to your path. Do not forget to re-login for the
changes to take effect.</p>
<p>Verify installation by running <cite>DepthSenseViewer.exe</cite> in command prompt.</p>
</div>
</div>
<div class="section" id="pcl-configuration">
<h1><a class="toc-backref" href="#id6">PCL configuration</a></h1>
<p>You need at least PCL 1.8.0 to be able to use the DepthSense SDK. The
<code class="docutils literal notranslate"><span class="pre">WITH_DSSDK</span></code> option should be enabled in the CMake configuration.</p>
</div>
<div class="section" id="depthsense-viewer">
<h1><a class="toc-backref" href="#id7">DepthSense Viewer</a></h1>
<p>The grabber is accompanied by an example tool <a class="reference external" href="https://github.com/PointCloudLibrary/pcl/blob/master/visualization/tools/depth_sense_viewer.cpp">pcl_depth_sense_viewer</a>
which can be used to view and save point clouds coming from a DepthSense device.
Internally it uses the <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_depth_sense_grabber.html">DepthSenseGrabber</a>
class that implements the standard PCL grabber interface.</p>
<p>You can run the tool with <cite>–help</cite> option to view the usage guide.</p>
<p>The video below demonstrates the features of the DepthSense viewer tool. Please
note that the bilateral filtering (which can be observed in the end of the
video) is currently disabled is the tool.</p>
<center><iframe title="DepthSense viewer" width="560" height="315" src="https://www.youtube.com/embed/W3_VYiiEPjQ" frameborder="0" allowfullscreen></iframe></center></div>


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