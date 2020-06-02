<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation - Point Cloud Library (PCL)</title>
</head>

<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8" />
    <title>Compiling PCL from source on POSIX compliant systems &#8212; PCL 0.0 documentation</title>
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
            
  <div class="section" id="compiling-pcl-from-source-on-posix-compliant-systems">
<span id="compiling-pcl-posix"></span><h1><a class="toc-backref" href="#id2">Compiling PCL from source on POSIX compliant systems</a></h1>
<p>Though not a dependency per se, don’t forget that you also need the <a class="reference external" href="http://www.cmake.org/download/">CMake build system</a>, at least version 3.5.0.
Additional help on how to use the CMake build system is available <a class="reference external" href="http://www.pointclouds.org/documentation/tutorials/building_pcl.php#building-pcl">here</a>.</p>
<p>Please note that the following installation instructions are only valid for POSIX systems (e.g., Linux, MacOS) with an already installed make/gnu toolchain.
For instructions on how to download and compile PCL in Windows (which uses a slightly different process), please visit
<a class="reference external" href="http://www.pointclouds.org/documentation/tutorials/index.php">our tutorials page</a>.</p>
<div class="contents topic" id="contents">
<p class="topic-title">Contents</p>
<ul class="simple">
<li><p><a class="reference internal" href="#compiling-pcl-from-source-on-posix-compliant-systems" id="id2">Compiling PCL from source on POSIX compliant systems</a></p>
<ul>
<li><p><a class="reference internal" href="#stable" id="id3">Stable</a></p></li>
<li><p><a class="reference internal" href="#experimental" id="id4">Experimental</a></p></li>
<li><p><a class="reference internal" href="#dependencies" id="id5">Dependencies</a></p>
<ul>
<li><p><a class="reference internal" href="#mandatory" id="id6">Mandatory</a></p></li>
<li><p><a class="reference internal" href="#optional" id="id7">Optional</a></p></li>
</ul>
</li>
<li><p><a class="reference internal" href="#troubleshooting" id="id8">Troubleshooting</a></p>
<ul>
<li><p><a class="reference internal" href="#macos-x" id="id9">MacOS X</a></p></li>
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
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">tar</span> <span class="n">xvfj</span> <span class="n">pcl</span><span class="o">-</span><span class="n">pcl</span><span class="o">-</span><span class="mf">1.7</span><span class="o">.</span><span class="mf">2.</span><span class="n">tar</span><span class="o">.</span><span class="n">gz</span>
</pre></div>
</div>
<p>Change the directory to the pcl-pcl-1.7.2 (replace 1.7.2 with the correct version number) directory, and create a build directory in there:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">cd</span> <span class="n">pcl</span><span class="o">-</span><span class="n">pcl</span><span class="o">-</span><span class="mf">1.7</span><span class="o">.</span><span class="mi">2</span> <span class="o">&amp;&amp;</span> <span class="n">mkdir</span> <span class="n">build</span> <span class="o">&amp;&amp;</span> <span class="n">cd</span> <span class="n">build</span>
</pre></div>
</div>
<p>Run the CMake build system using the default options:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">cmake</span> <span class="o">..</span>
</pre></div>
</div>
<p>Or change them (uses cmake-curses-gui):</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">ccmake</span> <span class="o">..</span>
</pre></div>
</div>
<p>Please note that cmake might default to a debug build. If you want to compile a release build of PCL with enhanced compiler optimizations, you can change the build target to “Release” with “-DCMAKE_BUILD_TYPE=Release”:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">cmake</span> <span class="o">-</span><span class="n">DCMAKE_BUILD_TYPE</span><span class="o">=</span><span class="n">Release</span> <span class="o">..</span>
</pre></div>
</div>
<p>Finally compile everything (see <a class="reference external" href="http://www.pointclouds.org/documentation/advanced/compiler_optimizations.php">compiler_optimizations</a>):</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">make</span> <span class="o">-</span><span class="n">j2</span>
</pre></div>
</div>
<p>And install the result:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">make</span> <span class="o">-</span><span class="n">j2</span> <span class="n">install</span>
</pre></div>
</div>
<p>Or alternatively, if you did not change the variable which declares where PCL should be installed, do:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">sudo</span> <span class="n">make</span> <span class="o">-</span><span class="n">j2</span> <span class="n">install</span>
</pre></div>
</div>
<p>Here’s everything again, in case you want to copy &amp; paste it:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">cd</span> <span class="n">pcl</span><span class="o">-</span><span class="n">pcl</span><span class="o">-</span><span class="mf">1.7</span><span class="o">.</span><span class="mi">2</span> <span class="o">&amp;&amp;</span> <span class="n">mkdir</span> <span class="n">build</span> <span class="o">&amp;&amp;</span> <span class="n">cd</span> <span class="n">build</span>
<span class="n">cmake</span> <span class="o">-</span><span class="n">DCMAKE_BUILD_TYPE</span><span class="o">=</span><span class="n">Release</span> <span class="o">..</span>
<span class="n">make</span> <span class="o">-</span><span class="n">j2</span>
<span class="n">sudo</span> <span class="n">make</span> <span class="o">-</span><span class="n">j2</span> <span class="n">install</span>
</pre></div>
</div>
<p>Again, for a detailed tutorial on how to compile and install PCL and its dependencies in Microsoft Windows, please visit <a class="reference external" href="http://www.pointclouds.org/documentation/tutorials/index.php">our tutorials page</a>. Additional information for developers is available at the <a class="reference external" href="https://github.com/PointCloudLibrary/pcl/wiki">Github PCL Wiki</a>.</p>
</div>
<div class="section" id="experimental">
<h2><a class="toc-backref" href="#id4">Experimental</a></h2>
<p>If you are eager to try out a certain feature of PCL that is currently under development (or you plan on developing and contributing to PCL), we recommend you try checking out our source repository, as shown below. If you’re just interested in browsing our source code, you can do so by visiting <a class="reference external" href="https://github.com/PointCloudLibrary/pcl">https://github.com/PointCloudLibrary/pcl</a>.</p>
<p>Clone the repository:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">git</span> <span class="n">clone</span> <span class="n">https</span><span class="p">:</span><span class="o">//</span><span class="n">github</span><span class="o">.</span><span class="n">com</span><span class="o">/</span><span class="n">PointCloudLibrary</span><span class="o">/</span><span class="n">pcl</span> <span class="n">pcl</span><span class="o">-</span><span class="n">trunk</span>
</pre></div>
</div>
<p>Please note that above steps (3-5) are almost identical for compiling the experimental PCL trunk code:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">cd</span> <span class="n">pcl</span><span class="o">-</span><span class="n">trunk</span> <span class="o">&amp;&amp;</span> <span class="n">mkdir</span> <span class="n">build</span> <span class="o">&amp;&amp;</span> <span class="n">cd</span> <span class="n">build</span>
<span class="n">cmake</span> <span class="o">-</span><span class="n">DCMAKE_BUILD_TYPE</span><span class="o">=</span><span class="n">RelWithDebInfo</span> <span class="o">..</span>
<span class="n">make</span> <span class="o">-</span><span class="n">j2</span>
<span class="n">sudo</span> <span class="n">make</span> <span class="o">-</span><span class="n">j2</span> <span class="n">install</span>
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
<table class="docutils align-default">
<colgroup>
<col style="width: 51%" />
<col style="width: 14%" />
<col style="width: 20%" />
<col style="width: 15%" />
</colgroup>
<thead>
<tr class="row-odd"><th class="head"><p>Logo</p></th>
<th class="head"><p>Library</p></th>
<th class="head"><p>Minimum version</p></th>
<th class="head"><p>Mandatory</p></th>
</tr>
</thead>
<tbody>
<tr class="row-even"><td><img alt="_images/boost_logo.png" src="_images/boost_logo.png" />
</td>
<td><p>Boost</p></td>
<td><div class="line-block">
<div class="line">1.40 (without OpenNI)</div>
<div class="line">1.47 (with OpenNI)</div>
</div>
</td>
<td><p>pcl_*</p></td>
</tr>
<tr class="row-odd"><td><img alt="_images/eigen_logo.png" src="_images/eigen_logo.png" />
</td>
<td><p>Eigen</p></td>
<td><p>3.0</p></td>
<td><p>pcl_*</p></td>
</tr>
<tr class="row-even"><td><img alt="_images/flann_logo.png" src="_images/flann_logo.png" />
</td>
<td><p>FLANN</p></td>
<td><p>1.7.1</p></td>
<td><p>pcl_*</p></td>
</tr>
<tr class="row-odd"><td><img alt="_images/vtk_logo.png" src="_images/vtk_logo.png" />
</td>
<td><p>VTK</p></td>
<td><p>5.6</p></td>
<td><p>pcl_visualization</p></td>
</tr>
</tbody>
</table>
</div>
<div class="section" id="optional">
<h3><a class="toc-backref" href="#id7">Optional</a></h3>
<p>The following code libraries enable certain additional features for the PCL libraries shown below, and are thus <strong>optional</strong>:</p>
<table class="docutils align-default">
<colgroup>
<col style="width: 53%" />
<col style="width: 14%" />
<col style="width: 16%" />
<col style="width: 16%" />
</colgroup>
<thead>
<tr class="row-odd"><th class="head"><p>Logo</p></th>
<th class="head"><p>Library</p></th>
<th class="head"><p>Minimum version</p></th>
<th class="head"><p>Mandatory</p></th>
</tr>
</thead>
<tbody>
<tr class="row-even"><td><img alt="_images/qhull_logo.png" src="_images/qhull_logo.png" />
</td>
<td><p>Qhull</p></td>
<td><p>2011.1</p></td>
<td><p>pcl_surface</p></td>
</tr>
<tr class="row-odd"><td><img alt="_images/openni_logo.png" src="_images/openni_logo.png" />
</td>
<td><p>OpenNI</p></td>
<td><p>1.3</p></td>
<td><p>pcl_io</p></td>
</tr>
<tr class="row-even"><td><img alt="_images/cuda_logo.png" src="_images/cuda_logo.png" />
</td>
<td><p>CUDA</p></td>
<td><p>4.0</p></td>
<td><p>pcl_*</p></td>
</tr>
</tbody>
</table>
</div>
</div>
<div class="section" id="troubleshooting">
<h2><a class="toc-backref" href="#id8">Troubleshooting</a></h2>
<p>In certain situations, the instructions above might fail, either due to custom versions of certain library dependencies installed, or different operating systems than the ones we usually develop on, etc. This section here contains links to discussions held in our community regarding such cases. Please read it before posting new questions on the mailing list, and also <strong>use the search features provided by our forums</strong> - there’s no point in starting a new thread if an older one that discusses the same issue already exists.</p>
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