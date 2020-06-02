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
    
    <title>Compiling PCL and its dependencies from MacPorts and source on Mac OS X</title>
    
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
            
  <div class="section" id="compiling-pcl-and-its-dependencies-from-macports-and-source-on-mac-os-x">
<span id="compiling-pcl-macosx"></span><h1>Compiling PCL and its dependencies from MacPorts and source on Mac OS X</h1>
<p>This tutorial explains how to build the Point Cloud Library
<strong>from MacPorts and source</strong> on Mac OS X platforms, and tries to guide you
through the download and building <em>of all the required dependencies</em>.</p>
<img alt="Mac OS X logo" class="align-right" src="_images/macosx_logo.png" />
</div>
<div class="section" id="prerequisites">
<span id="macosx-prerequisites"></span><h1>Prerequisites</h1>
<p>Before getting started download and install the following prerequisites for
Mac OS X:</p>
<ul>
<li><dl class="first docutils">
<dt><strong>XCode</strong> (<a class="reference external" href="https://developer.apple.com/xcode/">https://developer.apple.com/xcode/</a>)</dt>
<dd><p class="first last">Apple’s powerful integrated development environment</p>
</dd>
</dl>
</li>
<li><dl class="first docutils">
<dt><strong>MacPorts</strong> (<a class="reference external" href="http://www.macports.org">http://www.macports.org</a>)</dt>
<dd><p class="first last">An open-source community initiative to design an easy-to-use
system for compiling, installing, and upgrading  either command-line, X11 or
Aqua based open-source software on the Mac OS X operating system.</p>
</dd>
</dl>
</li>
</ul>
</div>
<div class="section" id="pcl-dependencies">
<span id="macosx-dependencies"></span><h1>PCL Dependencies</h1>
<p>In order to compile every component of the PCL library we need to download and
compile a series of 3rd party library dependencies.  We&#8217;ll cover the building,
compiling and installing of everything in the following sections:</p>
<div class="section" id="required">
<h2>Required</h2>
<p>The following libraries are <strong>Required</strong> to build PCL.</p>
<ul>
<li><dl class="first docutils">
<dt><strong>CMake</strong> version &gt;= 2.8.3 (<a class="reference external" href="http://www.cmake.org">http://www.cmake.org</a>)</dt>
<dd><p class="first">Cross-platform, open-source build system.</p>
<div class="last admonition note">
<p class="first admonition-title">Note</p>
<p class="last">Though not a dependency per se, the PCL community relies heavily on CMake
for the libraries build process.</p>
</div>
</dd>
</dl>
</li>
<li><dl class="first docutils">
<dt><strong>Boost</strong> version &gt;= 1.46.1 (<a class="reference external" href="http://www.boost.org/">http://www.boost.org/</a>)</dt>
<dd><p class="first last">Provides free peer-reviewed portable C++ source libraries.  Used for shared
pointers, and threading.</p>
</dd>
</dl>
</li>
<li><dl class="first docutils">
<dt><strong>Eigen</strong> version &gt;= 3.0.0 (<a class="reference external" href="http://eigen.tuxfamily.org/">http://eigen.tuxfamily.org/</a>)</dt>
<dd><p class="first last">Unified matrix library.  Used as the matrix backend for SSE optimized math.</p>
</dd>
</dl>
</li>
<li><p class="first"><strong>FLANN</strong> version &gt;= 1.6.8
(<a class="reference external" href="http://www.cs.ubc.ca/research/flann/">http://www.cs.ubc.ca/research/flann/</a>)
Library for performing fast approximate nearest neighbor searches in high
dimensional spaces.  Used in <cite>kdtree</cite> for fast approximate nearest neighbors
search.</p>
</li>
<li><dl class="first docutils">
<dt><strong>Visualization ToolKit (VTK)</strong> version &gt;= 5.6.1 (<a class="reference external" href="http://www.vtk.org/">http://www.vtk.org/</a>)</dt>
<dd><p class="first last">Software system for 3D computer graphics, image processing and visualization.
Used in <cite>visualization</cite> for 3D point cloud rendering and visualization.</p>
</dd>
</dl>
</li>
</ul>
</div>
<div class="section" id="optional">
<h2>Optional</h2>
<p>The following libraries are <strong>Optional</strong> and provide extended functionality
within PCL, ie Kinect support.</p>
<ul>
<li><dl class="first docutils">
<dt><strong>Qhull</strong> version &gt;= 2011.1 (<a class="reference external" href="http://www.qhull.org/">http://www.qhull.org/</a>)</dt>
<dd><p class="first last">computes the convex hull, Delaunay triangulation, Voronoi diagram, halfspace
intersection about a point, furthest-site Delaunay triangulation, and
furthest-site Voronoi diagram.  Used for convex/concave hull decompositions
in <cite>surface</cite>.</p>
</dd>
</dl>
</li>
<li><dl class="first docutils">
<dt><strong>libusb</strong> (<a class="reference external" href="http://www.libusb.org/">http://www.libusb.org/</a>)</dt>
<dd><p class="first last">A library that gives user level applications uniform access to USB devices
across many different operating systems.</p>
</dd>
</dl>
</li>
<li><dl class="first docutils">
<dt><strong>PCL Patched OpenNI/Sensor</strong> (<a class="reference external" href="http://www.openni.org/">http://www.openni.org/</a>)</dt>
<dd><p class="first last">The OpenNI Framework provides the interface for physical devices and for
middleware components. Used to grab point clouds from OpenNI compliant
devices.</p>
</dd>
</dl>
</li>
</ul>
</div>
<div class="section" id="advanced-developers">
<h2>Advanced (Developers)</h2>
<p>The following libraries are <strong>Advanced</strong> and provide additional functionality
for PCL developers:</p>
<ul>
<li><dl class="first docutils">
<dt><strong>googletest</strong> version &gt;= 1.6.0 (<a class="reference external" href="http://code.google.com/p/googletest/">http://code.google.com/p/googletest/</a>)</dt>
<dd><p class="first last">Google&#8217;s framework for writing C++ tests on a variety of platforms. Used
to build test units.</p>
</dd>
</dl>
</li>
<li><dl class="first docutils">
<dt><strong>Doxygen</strong> (<a class="reference external" href="http://www.doxygen.org">http://www.doxygen.org</a>)</dt>
<dd><p class="first last">A documentation system for C++, C, Java, Objective-C, Python, IDL (Corba and
Microsoft flavors), Fortran, VHDL, PHP, C#, and to some extent D.</p>
</dd>
</dl>
</li>
<li><dl class="first docutils">
<dt><strong>Sphinx</strong> (<a class="reference external" href="http://sphinx-doc.org/">http://sphinx-doc.org/</a>)</dt>
<dd><p class="first last">A tool that makes it easy to create intelligent and beautiful
documentation.</p>
</dd>
</dl>
</li>
</ul>
</div>
</div>
<div class="section" id="building-compiling-and-installing-pcl-dependencies">
<span id="macosx-building-prerequisites"></span><h1>Building, Compiling and Installing PCL Dependencies</h1>
<p>By now you should have downloaded and installed the latest versions of XCode and
MacPorts under the <a class="reference internal" href="#macosx-prerequisites"><em>Prerequisites</em></a> section.  We&#8217;ll be installing most
dependencies available via MacPorts and the rest will be built from source.</p>
<div class="section" id="install-cmake">
<h2>Install CMake</h2>
<div class="highlight-python"><div class="highlight"><pre>$ sudo port install cmake
</pre></div>
</div>
</div>
<div class="section" id="install-boost">
<h2>Install Boost</h2>
<div class="highlight-python"><div class="highlight"><pre>$ sudo port install boost
</pre></div>
</div>
</div>
<div class="section" id="install-eigen">
<h2>Install Eigen</h2>
<div class="highlight-python"><div class="highlight"><pre>$ sudo port install eigen3
</pre></div>
</div>
</div>
<div class="section" id="install-flann">
<h2>Install FLANN</h2>
<div class="highlight-python"><div class="highlight"><pre>$ sudo port install flann
</pre></div>
</div>
</div>
<div class="section" id="install-vtk">
<h2>Install VTK</h2>
<p>To install via MacPorts:</p>
<div class="highlight-python"><div class="highlight"><pre>$ sudo port install vtk5 +qt4_mac
</pre></div>
</div>
<p>To install from source download the source from
<a class="reference external" href="http://www.vtk.org/VTK/resources/software.html">http://www.vtk.org/VTK/resources/software.html</a></p>
<p>Follow the README.html for compiling on UNIX / Cygwin / Mac OSX:</p>
<div class="highlight-python"><div class="highlight"><pre>$ cd VTK
$ mkdir VTK-build
$ cd VTK-build
$ ccmake ../VTK
</pre></div>
</div>
<dl class="docutils">
<dt>Within the CMake configuration:</dt>
<dd><p class="first">Press [c] for initial configuration</p>
<p>Press [t] to get into advanced mode and change the following:</p>
<div class="highlight-python"><div class="highlight"><pre>VTK_USE_CARBON:OFF
VTK_USE_COCOA:ON
VTK_USE_X:OFF
</pre></div>
</div>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">VTK <em>must</em> be built with Cocoa support and <em>must</em> be installed,
in order for the visualization module to be able to compile. If you do
not require visualisation, you may omit this step.</p>
</div>
<p>Press [g] to generate the make files.</p>
<p class="last">Press [q] to quit.</p>
</dd>
</dl>
<p>Then run:</p>
<div class="highlight-python"><div class="highlight"><pre>$ make &amp;&amp; make install
</pre></div>
</div>
</div>
<div class="section" id="install-qhull">
<h2>Install Qhull</h2>
<div class="highlight-python"><div class="highlight"><pre>$ sudo port install qhull
</pre></div>
</div>
</div>
<div class="section" id="install-libusb">
<h2>Install libusb</h2>
<div class="highlight-python"><div class="highlight"><pre>$ sudo port install libusb-devel +universal
</pre></div>
</div>
</div>
<div class="section" id="install-patched-openni-and-sensor">
<h2>Install Patched OpenNI and Sensor</h2>
<p>Download the patched versions of OpenNI and Sensor from the PCL downloads page
<a class="reference external" href="http://pointclouds.org/downloads/macosx.html">http://pointclouds.org/downloads/macosx.html</a></p>
<p>Extract, build, fix permissions and install OpenNI:</p>
<div class="highlight-python"><div class="highlight"><pre>$ unzip openni_osx.zip -d openni_osx
$ cd openni_osx/Redist
$ chmod -R a+r Bin Include Lib
$ chmod -R a+x Bin Lib
$ chmod a+x Include/MacOSX Include/Linux-*
$ sudo ./install
</pre></div>
</div>
<p>In addition the following primesense xml config found within the patched OpenNI
download needs its permissions fixed and copied to the correct location to for
the Kinect to work on Mac OS X:</p>
<div class="highlight-python"><div class="highlight"><pre>$ chmod a+r openni_osx/Redist/Samples/Config/SamplesConfig.xml
$ sudo cp openni_osx/Redist/Samples/Config/SamplesConfig.xml /etc/primesense/
</pre></div>
</div>
<p>Extract, build, fix permissions and install Sensor:</p>
<div class="highlight-python"><div class="highlight"><pre>$ unzip ps_engine_osx.zip -d ps_engine_osx
$ cd ps_engine_osx/Redist
$ chmod -R a+r Bin Lib Config Install
$ chmod -R a+x Bin Lib
$ sudo ./install
</pre></div>
</div>
</div>
</div>
<div class="section" id="building-pcl">
<span id="macosx-building-pcl"></span><h1>Building PCL</h1>
<p>At this point you should have everything needed installed to build PCL with
almost no additional configuration.</p>
<p>Checkout the PCL source from the Github:</p>
<blockquote>
<div>$ git clone <a class="reference external" href="https://github.com/PointCloudLibrary/pcl">https://github.com/PointCloudLibrary/pcl</a>
$ cd pcl</div></blockquote>
<p>Create the build directories, configure CMake, build and install:</p>
<div class="highlight-python"><div class="highlight"><pre>$ mkdir build
$ cd build
$ cmake ..
$ make
$ sudo make install
</pre></div>
</div>
<p>The customization of the build process is out of the scope of this tutorial and
is covered in greater detail in the <a class="reference internal" href="building_pcl.php#building-pcl"><em>Customizing the PCL build process</em></a> tutorial.</p>
</div>
<div class="section" id="using-pcl">
<h1>Using PCL</h1>
<p>We finally managed to compile the Point Cloud Library (PCL) for Mac OS X. You
can start using them in your project by following the <a class="reference internal" href="using_pcl_pcl_config.php#using-pcl-pcl-config"><em>Using PCL in your own project</em></a> tutorial.</p>
</div>
<div class="section" id="macosx-advanced">
<span id="id1"></span><h1>Advanced (Developers)</h1>
<div class="section" id="testing-googletest">
<h2>Testing (googletest)</h2>
</div>
<div class="section" id="api-documentation-doxygen">
<h2>API Documentation (Doxygen)</h2>
<p>Install Doxygen via MacPorts:</p>
<div class="highlight-python"><div class="highlight"><pre>$ sudo port install doxygen
</pre></div>
</div>
<p>Or install the Prebuilt binary for Mac OS X
(<a class="reference external" href="http://www.stack.nl/~dimitri/doxygen/download.html#latestsrc">http://www.stack.nl/~dimitri/doxygen/download.html#latestsrc</a>)</p>
<p>After installed you can build the documentation:</p>
<div class="highlight-python"><div class="highlight"><pre>$ make doc
</pre></div>
</div>
</div>
<div class="section" id="tutorials-sphinx">
<h2>Tutorials (Sphinx)</h2>
<p>In addition to the API documentation there is also tutorial documentation built
using Sphinx.  The easiest way to get this installed is using pythons
<cite>easy_install</cite>:</p>
<div class="highlight-python"><div class="highlight"><pre>$ easy_install -U Sphinx
</pre></div>
</div>
<p>The Sphinx documentation also requires the third party contrib extension
<cite>sphinxcontrib-doxylink</cite> (<a class="reference external" href="https://pypi.python.org/pypi/sphinxcontrib-doxylink">https://pypi.python.org/pypi/sphinxcontrib-doxylink</a>)
to reference the Doxygen built documentation.</p>
<p>To install from source you&#8217;ll also need Mercurial:</p>
<div class="highlight-python"><div class="highlight"><pre>$ sudo port install mercurial
$ hg clone http://bitbucket.org/birkenfeld/sphinx-contrib
$ cd sphinx-contrib/doxylink
$ python setup.py install
</pre></div>
</div>
<p>After installed you can build the tutorials:</p>
<div class="highlight-python"><div class="highlight"><pre>$ make Tutorials
</pre></div>
</div>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">Sphinx can be installed via MacPorts but is a bit of a pain getting all the
PYTHON_PATH&#8217;s in order</p>
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