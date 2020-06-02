<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation - Point Cloud Library (PCL)</title>
</head>

<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8" />
    <title>Grabbing point clouds / meshes from davidSDK scanners &#8212; PCL 0.0 documentation</title>
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
            
  <div class="section" id="grabbing-point-clouds-meshes-from-davidsdk-scanners">
<span id="david-sdk"></span><h1><a class="toc-backref" href="#id15">Grabbing point clouds / meshes from davidSDK scanners</a></h1>
<p>In this tutorial we will learn how to use the <a class="reference external" href="http://www.david-3d.com/en/products/david-sdk">davidSDK</a> through PCL. This tutorial will show you how to configure PCL and how to use the examples to fetch point clouds/meshes/images from a davidSDK compliant device (such as the <a class="reference external" href="http://www.david-3d.com/en/products/sls-2">SLS-2</a>).</p>
<div class="contents topic" id="contents">
<p class="topic-title">Contents</p>
<ul class="simple">
<li><p><a class="reference internal" href="#grabbing-point-clouds-meshes-from-davidsdk-scanners" id="id15">Grabbing point clouds / meshes from davidSDK scanners</a></p>
<ul>
<li><p><a class="reference internal" href="#install-davidsdk" id="id16">Install davidSDK</a></p></li>
<li><p><a class="reference internal" href="#configuring-pcl" id="id17">Configuring PCL</a></p></li>
<li><p><a class="reference internal" href="#platform-specific-directives" id="id18">Platform specific directives</a></p></li>
<li><p><a class="reference internal" href="#file-formats" id="id19">File formats</a></p></li>
<li><p><a class="reference internal" href="#calibration" id="id20">Calibration</a></p></li>
<li><p><a class="reference internal" href="#using-the-example" id="id21">Using the example</a></p></li>
</ul>
</li>
</ul>
</div>
<div class="section" id="install-davidsdk">
<h2><a class="toc-backref" href="#id16">Install davidSDK</a></h2>
<p>You need a davidSDK to run the SDK on the server side, the official davidSDK does not come with a Makefile or a CMake project. An un-official fork provides a CMake project that enables to easily use the SDK under Linux (with minor tweaks)</p>
<blockquote>
<div><ul class="simple">
<li><p><a class="reference external" href="http://www.david-3d.com/en/support/downloads">Official davidSDK download page</a></p></li>
<li><p><a class="reference external" href="https://gitlab.com/InstitutMaupertuis/davidSDK">Victor Lamoine davidSDK fork</a></p></li>
</ul>
</div></blockquote>
<p>Please test <a class="reference external" href="https://gitlab.com/InstitutMaupertuis/davidSDK/blob/master/README.md#example-project-using-the-davidsdk">the example project</a> before going further.</p>
<div class="admonition note">
<p class="admonition-title">Note</p>
<p>If you use the trial version of the server, the only format available is OBJ (used by default)</p>
</div>
</div>
<div class="section" id="configuring-pcl">
<h2><a class="toc-backref" href="#id17">Configuring PCL</a></h2>
<p>You need at least PCL 1.8.0 to be able to use the davidSDK. You need to make sure <code class="docutils literal notranslate"><span class="pre">WITH_DAVIDSDK</span></code> is set to <code class="docutils literal notranslate"><span class="pre">true</span></code> in the CMake configuration (it should be set to true by default if you have used the un-official davidSDK fork).</p>
<p>The default following values can be tweaked into CMake if you don’t have a standard installation, for example:</p>
<div class="highlight-cmake notranslate"><div class="highlight"><pre><span></span>DAVIDSDK_ABI_DIR     /opt/davidsdk
</pre></div>
</div>
<p>You can deactivate building the davidSDK support by setting <code class="docutils literal notranslate"><span class="pre">BUILD_DAVIDSDK</span></code> to false. Compile and install PCL.</p>
</div>
<div class="section" id="platform-specific-directives">
<h2><a class="toc-backref" href="#id18">Platform specific directives</a></h2>
<p>It should be easy to use the davidSDK PCL support if you are using PCL on the davidSDK server; the meshes are locally exported on the storage drive and then loaded into PCL as point clouds/meshes. If you are using a Linux distribution you will need to configure more things for the davidSDK PCL implementation to work, create a temporary directory for the davidSDK meshes storage:</p>
<div class="highlight-bash notranslate"><div class="highlight"><pre><span></span>mkdir -p /var/tmp/davidsdk
sudo chmod <span class="m">755</span> /var/tmp/davidsdk
</pre></div>
</div>
<p>Edit samba configuration (samba must be installed first):</p>
<div class="highlight-bash notranslate"><div class="highlight"><pre><span></span><span class="nb">echo</span> -e <span class="s2">&quot;[davidsdk]\n\</span>
<span class="s2"> path = /var/tmp/davidsdk\n\</span>
<span class="s2"> public = yes\n\</span>
<span class="s2"> writeable = yes\n\</span>
<span class="s2"> browseable = yes\n\</span>
<span class="s2"> guest ok = yes\n\</span>
<span class="s2"> create mask = 0775&quot;</span> <span class="p">|</span><span class="se">\</span>
sudo tee -a /etc/samba/smb.conf
</pre></div>
</div>
<p>Restard samba server:</p>
<div class="highlight-bash notranslate"><div class="highlight"><pre><span></span>sudo service smbd restart
</pre></div>
</div>
<p>Use the <a href="#id1"><span class="problematic" id="id2">:pcl:`setLocalAndRemotePaths &lt;pcl::DavidSDKGrabber::setLocalAndRemotePaths&gt;`</span></a> function to set the local and remote paths, if you use the same path as above; this doesn’t have to be called if the server is running of the same machine as the client.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="n">davidsdk_ptr</span><span class="o">-&gt;</span><span class="n">setLocalAndRemotePaths</span> <span class="p">(</span><span class="s">&quot;/var/tmp/davidsdk/&quot;</span><span class="p">,</span> <span class="s">&quot;</span><span class="se">\\\\</span><span class="s">name_of_machine</span><span class="se">\\</span><span class="s">davidsdk</span><span class="se">\\</span><span class="s">&quot;</span><span class="p">);</span>
</pre></div>
</div>
<div class="admonition note">
<p class="admonition-title">Note</p>
<p>If you get a Error_Fail = -107 error, it is most probably a write access missing in the temporary directory.</p>
</div>
</div>
<div class="section" id="file-formats">
<h2><a class="toc-backref" href="#id19">File formats</a></h2>
<p>Three file formats are available to export the meshes / clouds.</p>
<ul class="simple">
<li><p>STL: No texture support, binary format</p></li>
<li><p>OBJ: Texture support, no binary format available</p></li>
<li><p>PLY: Texture support, binary format is available but davidSDK uses ASCII format</p></li>
</ul>
<p>Use the <a href="#id3"><span class="problematic" id="id4">:pcl:`setFileFormatToOBJ &lt;pcl::DavidSDKGrabber::setFileFormatToOBJ&gt;`</span></a>,
<a href="#id5"><span class="problematic" id="id6">:pcl:`setFileFormatToPLY &lt;pcl::DavidSDKGrabber::setFileFormatToPLY&gt;`</span></a>,
<a href="#id7"><span class="problematic" id="id8">:pcl:`setFileFormatToSTL &lt;pcl::DavidSDKGrabber::setFileFormatToSTL&gt;`</span></a> to choose between the different formats.</p>
<p>The default format used is OBJ. (it is compatible with davidSDK server trial version)</p>
</div>
<div class="section" id="calibration">
<h2><a class="toc-backref" href="#id20">Calibration</a></h2>
<p>In order to use the davidSDK scanner the camera and the projector must be calibrated. This can be done by calling the <a href="#id9"><span class="problematic" id="id10">:pcl:`calibrate &lt;pcl::DavidSDKGrabber::calibrate&gt;`</span></a> function of the DavidSDKGrabber object, if the calibration fails, please check <a class="reference external" href="http://wiki.david-3d.com/david-wiki">the wiki</a>.</p>
<p>The davidSDK will only allow you to scan if the scanner is calibrated, the davidSDK provides functions to load and save configuration files for the calibration. Also note that the davidSDK server will automatically reload the last calibration data when restarted.</p>
</div>
<div class="section" id="using-the-example">
<h2><a class="toc-backref" href="#id21">Using the example</a></h2>
<p>The <a class="reference external" href="https://github.com/PointCloudLibrary/pcl/blob/master/visualization/tools/davidsdk_viewer.cpp">pcl_davidsdk_viewer</a> example shows how to display a point cloud grabbed from a davidSDK device using the <a href="#id11"><span class="problematic" id="id12">:pcl:`DavidSDKGrabber &lt;pcl::DavidSDKGrabber&gt;`</span></a> class.</p>
<p>When using the DavidSDKGrabber you must connect to the server first; if the server is running locally you don’t need to specify an IP address. If you are using davidSDK over a network just call <a href="#id13"><span class="problematic" id="id14">:pcl:`connect &lt;pcl::DavidSDKGrabber::connect&gt;`</span></a> with the address IP as a string, please also check that the connection didn’t failed:</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="n">davidsdk_ptr</span><span class="o">-&gt;</span><span class="n">connect</span> <span class="p">(</span><span class="s">&quot;192.168.1.50&quot;</span><span class="p">);</span>
<span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">davidsdk_ptr</span><span class="o">-&gt;</span><span class="n">isConnected</span> <span class="p">())</span>
<span class="p">{</span>
  <span class="n">PCL_ERROR</span> <span class="p">(</span><span class="s">&quot;Cannot connect to davidSDK server.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
  <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</div>
<a class="reference internal image-reference" href="_images/davidsdk_viewer.jpg"><img alt="_images/davidsdk_viewer.jpg" src="_images/davidsdk_viewer.jpg" style="height: 550px;" /></a>
<div class="admonition warning">
<p class="admonition-title">Warning</p>
<p>Fetching clouds/meshes from the davidSDK is very slow because the point clouds/meshes are sent through the JSON interface.
Do not expect better performance than 0.07 FPS (using STL format gives best performance).</p>
</div>
<p>Another example is available in <a class="reference external" href="https://github.com/PointCloudLibrary/pcl/blob/master/doc/tutorials/content/sources/davidsdk/">PCL sources</a>, it uses OpenCV to display davidSDK images and the PCLVisualizer to display the point cloud at the same time.</p>
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