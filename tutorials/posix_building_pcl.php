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
    
    <title>Build PCL from source on POSIX compliant systems</title>
    
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
            
  <div class="section" id="build-pcl-from-source-on-posix-compliant-systems">
<span id="posix-building-pcl"></span><h1><a class="toc-backref" href="#id2">Build PCL from source on POSIX compliant systems</a></h1>
<p>Though not a dependency per se, don’t forget that you also need the <a class="reference external" href="http://www.cmake.org/download/">CMake build system</a>, at least version 2.8.3.
Additional help on how to use the CMake build system is available <a class="reference external" href="http://www.pointclouds.org/documentation/tutorials/building_pcl.php#building-pcl">here</a>.</p>
<p>Please note that the following installation instructions are only valid for POSIX systems (e.g., Linux, MacOS) with an already installed make/gnu toolchain.
For instructions on how to download and compile PCL in Windows (which uses a slightly different process), please visit
<a class="reference external" href="http://www.pointclouds.org/documentation/tutorials/index.php">our tutorials page</a>.</p>
<div class="contents topic" id="contents">
<p class="topic-title first">Contents</p>
<ul class="simple">
<li><a class="reference internal" href="#build-pcl-from-source-on-posix-compliant-systems" id="id2">Build PCL from source on POSIX compliant systems</a><ul>
<li><a class="reference internal" href="#stable" id="id3">Stable</a></li>
<li><a class="reference internal" href="#experimental" id="id4">Experimental</a></li>
<li><a class="reference internal" href="#dependencies" id="id5">Dependencies</a><ul>
<li><a class="reference internal" href="#mandatory" id="id6">Mandatory</a></li>
<li><a class="reference internal" href="#optional" id="id7">Optional</a></li>
</ul>
</li>
<li><a class="reference internal" href="#troubleshooting" id="id8">Troubleshooting</a><ul>
<li><a class="reference internal" href="#macos-x" id="id9">MacOS X</a></li>
</ul>
</li>
</ul>
</li>
</ul>
</div>
<div class="section" id="stable">
<h2><a class="toc-backref" href="#id3">Stable</a></h2>
<p>For systems for which we do not offer precompiled binaries, you need to compile Point Cloud Library (PCL) from source. Here are the steps that you need to take:
Go to <a class="reference external" href="https://github.com/PointCloudLibrary/pcl/releases">Github</a> and download the version number of your choice.
Uncompress the tar-bzip archive, e.g. (replace 1.7.2 with the correct version number):</p>
<div class="highlight-python"><div class="highlight"><pre>tar xvfj pcl-pcl-1.7.2.tar.gz
</pre></div>
</div>
<p>Change the directory to the pcl-pcl-1.7.2 (replace 1.7.2 with the correct version number) directory, and create a build directory in there:</p>
<div class="highlight-python"><div class="highlight"><pre>cd pcl-pcl-1.7.2 &amp;&amp; mkdir build &amp;&amp; cd build
</pre></div>
</div>
<p>Run the CMake build system using the default options:</p>
<div class="highlight-python"><div class="highlight"><pre>cmake ..
</pre></div>
</div>
<p>Or change them (uses cmake-curses-gui):</p>
<div class="highlight-python"><div class="highlight"><pre>ccmake ..
</pre></div>
</div>
<p>Please note that cmake might default to a debug build. If you want to compile a release build of PCL with enhanced compiler optimizations, you can change the build target to &#8220;Release&#8221; with &#8220;-DCMAKE_BUILD_TYPE=Release&#8221;.</p>
<blockquote>
<div>cmake -DCMAKE_BUILD_TYPE=Release ..</div></blockquote>
<p>Finally compile everything (see <a class="reference external" href="http://www.pointclouds.org/documentation/advanced/compiler_optimizations.php">compiler_optimizations</a>):</p>
<div class="highlight-python"><div class="highlight"><pre><span class="n">make</span> <span class="o">-</span><span class="n">j2</span>
</pre></div>
</div>
<p>And install the result:</p>
<div class="highlight-python"><div class="highlight"><pre>make -j2 install
</pre></div>
</div>
<p>or alternatively, if you did not change the  variable which declares where PCL should be installed, do:</p>
<div class="highlight-python"><div class="highlight"><pre>sudo make -j2 install
</pre></div>
</div>
<p>Here&#8217;s everything again, in case you want to copy &amp; paste it:</p>
<div class="highlight-python"><div class="highlight"><pre>cd pcl-pcl-1.7.2 &amp;&amp; mkdir build &amp;&amp; cd build
cmake -DCMAKE_BUILD_TYPE=Release ..
make -j2
sudo make -j2 install
</pre></div>
</div>
<p>Again, for a detailed tutorial on how to compile and install PCL and its dependencies in Microsoft Windows, please visit <a class="reference external" href="http://www.pointclouds.org/documentation/tutorials/index.php">our tutorials page</a>. Additional information for developers is available at the <a class="reference external" href="https://github.com/PointCloudLibrary/pcl/wiki">Github PCL Wiki</a>.</p>
</div>
<div class="section" id="experimental">
<h2><a class="toc-backref" href="#id4">Experimental</a></h2>
<p>If you are eager to try out a certain feature of PCL that is currently under development (or you plan on developing and contributing to PCL), we recommend you try checking out our source repository, as shown below. If you&#8217;re just interested in browsing our source code, you can do so by visiting <a class="reference external" href="https://github.com/PointCloudLibrary/pcl">https://github.com/PointCloudLibrary/pcl</a>.</p>
<p>Clone the repository:</p>
<div class="highlight-python"><div class="highlight"><pre>git clone https://github.com/PointCloudLibrary/pcl pcl-trunk
</pre></div>
</div>
<p>Please note that above steps (3-5) are almost identical for compiling the experimental PCL trunk code:</p>
<div class="highlight-python"><div class="highlight"><pre>cd pcl-trunk &amp;&amp; mkdir build &amp;&amp; cd build
cmake -DCMAKE_BUILD_TYPE=RelWithDebInfo ..
make -j2
sudo make -j2 install
</pre></div>
</div>
</div>
<div class="section" id="dependencies">
<h2><a class="toc-backref" href="#id5">Dependencies</a></h2>
<p>Because PCL is split into a list of code libraries, the list of dependencies differs based on what you need to compile. The difference between mandatory and optional dependencies, is that a mandatory dependency is required in order for that particular PCL library to compile and function, while an optional dependency disables certain functionality within a PCL library but compiles the rest of the library that does not require the dependency.</p>
<div class="section" id="mandatory">
<h3><a class="toc-backref" href="#id6">Mandatory</a></h3>
<p>The following code libraries are <strong>required</strong> for the compilation and usage of the PCL libraries shown below:</p>
<p>pcl_* denotes all PCL libraries, meaning that the particular dependency is a strict requirement for the usage of anything in PCL.</p>
<table border="1" class="docutils">
<colgroup>
<col width="53%" />
<col width="14%" />
<col width="16%" />
<col width="16%" />
</colgroup>
<thead valign="bottom">
<tr class="row-odd"><th class="head">Logo</th>
<th class="head">Library</th>
<th class="head">Minimum version</th>
<th class="head">Mandatory</th>
</tr>
</thead>
<tbody valign="top">
<tr class="row-even"><td><img alt="_images/boost_logo.png" class="first last" src="_images/boost_logo.png" />
</td>
<td>Boost</td>
<td>1.54</td>
<td>pcl_*</td>
</tr>
<tr class="row-odd"><td><img alt="_images/eigen_logo.png" class="first last" src="_images/eigen_logo.png" />
</td>
<td>Eigen</td>
<td>3.0</td>
<td>pcl_*</td>
</tr>
<tr class="row-even"><td><img alt="_images/flann_logo.png" class="first last" src="_images/flann_logo.png" />
</td>
<td>FLANN</td>
<td>1.7.1</td>
<td>pcl_*</td>
</tr>
<tr class="row-odd"><td><img alt="_images/vtk_logo.png" class="first last" src="_images/vtk_logo.png" />
</td>
<td>VTK</td>
<td>5.6</td>
<td>pcl_visualization</td>
</tr>
</tbody>
</table>
</div>
<div class="section" id="optional">
<h3><a class="toc-backref" href="#id7">Optional</a></h3>
<p>The following code libraries enable certain additional features for the PCL libraries shown below, and are thus <strong>optional</strong>:</p>
<table border="1" class="docutils">
<colgroup>
<col width="53%" />
<col width="14%" />
<col width="16%" />
<col width="16%" />
</colgroup>
<thead valign="bottom">
<tr class="row-odd"><th class="head">Logo</th>
<th class="head">Library</th>
<th class="head">Minimum version</th>
<th class="head">Mandatory</th>
</tr>
</thead>
<tbody valign="top">
<tr class="row-even"><td><img alt="_images/qhull_logo.png" class="first last" src="_images/qhull_logo.png" />
</td>
<td>Qhull</td>
<td>2011.1</td>
<td>pcl_surface</td>
</tr>
<tr class="row-odd"><td><img alt="_images/openni_logo.png" class="first last" src="_images/openni_logo.png" />
</td>
<td>OpenNI</td>
<td>1.3</td>
<td>pcl_io</td>
</tr>
<tr class="row-even"><td><img alt="_images/cuda_logo.png" class="first last" src="_images/cuda_logo.png" />
</td>
<td>CUDA</td>
<td>4.0</td>
<td>pcl_*</td>
</tr>
</tbody>
</table>
</div>
</div>
<div class="section" id="troubleshooting">
<h2><a class="toc-backref" href="#id8">Troubleshooting</a></h2>
<p>In certain situations, the instructions above might fail, either due to custom versions of certain library dependencies installed, or different operating systems than the ones we usually develop on, etc. This section here contains links to discussions held in our community regarding such cases. Please read it before posting new questions on the mailing list, and also <strong>use the search features provided by our forums</strong> - there&#8217;s no point in starting a new thread if an older one that discusses the same issue already exists.</p>
<div class="section" id="macos-x">
<h3><a class="toc-backref" href="#id9">MacOS X</a></h3>
<p><a class="reference external" href="http://www.pcl-users.org/libGL-issue-when-running-visualization-apps-on-OSX-td3574302.html#a3574775">libGL issue when running visualization apps on OSX</a></p>
</div>
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