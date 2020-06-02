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
    
    <title>Building PCL’s dependencies from source on Windows</title>
    
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
            
  <div class="section" id="building-pcl-s-dependencies-from-source-on-windows">
<span id="compiling-pcl-dependencies-windows"></span><h1>Building PCL&#8217;s dependencies from source on Windows</h1>
<p>This tutorial explains how to build the Point Cloud Library needed dependencies <strong>from source</strong> on
Microsoft Windows platforms, and tries to guide you through the download and
the compilation process. As an example, we will be building the sources with Microsoft Visual Studio
2008 to get 32bit libraries. The procedure is almost the same for other compilers and for 64bit libraries.</p>
<blockquote>
<div><div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">Don&#8217;t forget that all the dependencies must be compiled using the same
compiler options and architecture specifications, i.e. you can&#8217;t mix 32 bit
libraries with 64 bit libraries.</p>
</div>
</div></blockquote>
<img alt="Microsoft Windows logo" class="align-right" src="_images/windows_logo.png" />
</div>
<div class="section" id="requirements">
<h1>Requirements</h1>
<p>In order to compile every component of the PCL library we need to download and
compile a series of 3rd party library dependencies:</p>
<blockquote>
<div><ul class="simple">
<li><strong>Boost</strong> version &gt;= 1.46.1 (<a class="reference external" href="http://www.boost.org/">http://www.boost.org/</a>)</li>
</ul>
<p>used for shared pointers, and threading. <strong>mandatory</strong></p>
<ul class="simple">
<li><strong>Eigen</strong> version &gt;= 3.0.0 (<a class="reference external" href="http://eigen.tuxfamily.org/">http://eigen.tuxfamily.org/</a>)</li>
</ul>
<p>used as the matrix backend for SSE optimized math. <strong>mandatory</strong></p>
<ul class="simple">
<li><strong>FLANN</strong> version &gt;= 1.6.8 (<a class="reference external" href="http://www.cs.ubc.ca/research/flann/">http://www.cs.ubc.ca/research/flann/</a>)</li>
</ul>
<p>used in <cite>kdtree</cite> for fast approximate nearest neighbors search. <strong>mandatory</strong></p>
<ul class="simple">
<li><strong>Visualization ToolKit (VTK)</strong> version &gt;= 5.6.1 (<a class="reference external" href="http://www.vtk.org/">http://www.vtk.org/</a>)</li>
</ul>
<p>used in <cite>visualization</cite> for 3D point cloud rendering and visualization. <strong>mandatory</strong></p>
<ul class="simple">
<li><strong>googletest</strong> version &gt;= 1.6.0 (<a class="reference external" href="http://code.google.com/p/googletest/">http://code.google.com/p/googletest/</a>)</li>
</ul>
<p>used to build test units. <strong>optional</strong></p>
<ul class="simple">
<li><strong>QHULL</strong> version &gt;= 2011.1 (<a class="reference external" href="http://www.qhull.org/">http://www.qhull.org/</a>)</li>
</ul>
<p>used for convex/concave hull decompositions in <cite>surface</cite>. <strong>optional</strong></p>
<ul class="simple">
<li><strong>OpenNI</strong> version &gt;= 1.1.0.25 (<a class="reference external" href="http://www.openni.org/">http://www.openni.org/</a>)</li>
</ul>
<p>used to grab point clouds from OpenNI compliant devices. <strong>optional</strong></p>
<ul class="simple">
<li><strong>Qt</strong> version &gt;= 4.6 (<a class="reference external" href="http://qt.digia.com/">http://qt.digia.com/</a>)</li>
</ul>
<p>used for developing applications with a graphical user interface (GUI) <strong>optional</strong></p>
<ul class="simple">
<li><strong>MPI</strong> version &gt;= 1.4 (<a class="reference external" href="http://www.mcs.anl.gov/research/projects/mpich2/">http://www.mcs.anl.gov/research/projects/mpich2/</a>)</li>
</ul>
<p><strong>optional</strong></p>
</div></blockquote>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">Though not a dependency per se, don&#8217;t forget that you also need the CMake
build system (<a class="reference external" href="http://www.cmake.org/">http://www.cmake.org/</a>), at least version <strong>2.8.3</strong>. A Git
client for Windows is also required to download the PCL source code.</p>
</div>
</div>
<div class="section" id="building-dependencies">
<h1>Building dependencies</h1>
<p>In this tutorial, we&#8217;ll be compiling these libraries versions:</p>
<div class="highlight-python"><div class="highlight"><pre>Boost : 1.48.0
Flann : 1.7.1
Qhull : 2011.1
Qt    : 4.8.0
VTK   : 5.8.0
GTest : 1.6.0
</pre></div>
</div>
<p>Let&#8217;s unpack all our libraries in C:/PCL_dependencies so that it would like
like:</p>
<div class="highlight-python"><div class="highlight"><pre>C:/PCL_dependencies
C:/PCL_dependencies/boost-cmake
C:/PCL_dependencies/eigen
C:/PCL_dependencies/flann-1.7.1-src
C:/PCL_dependencies/gtest-1.6.0
C:/PCL_dependencies/qhull
C:/PCL_dependencies/VTK
</pre></div>
</div>
<ul>
<li><p class="first"><strong>Boost</strong> :</p>
<blockquote>
<div><p>Let&#8217;s start with <cite>Boost</cite>. We will be using the <cite>CMake-able Boost</cite> project which provide a CMake based build system
for Boost.
As a dependency of MPI Boost module (optional), you need first to download and install MPI from the link above. Choose &#8220;Win IA32 binary&#8221;
if you are building 32 bit PCL libraries, or &#8220;Win X86_64 binary&#8221; if you are building 64 bit binaries.
If you do not need it, you can skip this, and remove &#8220;mpi&#8221; from the modules list later.</p>
<p>To build Boost, open the CMake-gui and fill in the fields:</p>
<div class="highlight-python"><div class="highlight"><pre>Where is my source code: C:/PCL_dependencies/boost-cmake
Where to build binaries: C:/PCL_dependencies/boost-cmake/build
</pre></div>
</div>
<p>Before clicking on &#8220;Configure&#8221;, click on &#8220;Add Entry&#8221; button in the top right of CMake gui, in
the popup window, fill the fiels as follows:</p>
<div class="highlight-python"><div class="highlight"><pre>Name  : LIBPREFIX
Type  : STRING
Value : lib
</pre></div>
</div>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p>If you are using <strong>Visual Studio 2010</strong>, then add also these 3 CMake entries before clicking &#8220;Configure&#8221;:</p>
<div class="last highlight-python"><div class="highlight"><pre>Name  : BOOST_TOOLSET
Type  : STRING
Value : vc100

Name  : BOOST_COMPILER
Type  : STRING
Value : msvc

Name  : BOOST_COMPILER_VERSION
Type  : STRING
Value : 10.0
</pre></div>
</div>
</div>
<p>Hit the &#8220;Configure&#8221; button and CMake will tell that the binaries folder doesn&#8217;t exist yet
(e.g., <em>C:/PCL_dependencies/boost-cmake/build</em>) and it will ask for a confirmation.</p>
<p>Proceed and be sure to choose the correct &#8220;Generator&#8221; on the next window. So, we choose &#8220;Visual Studio 9 2008&#8221;
generator.</p>
<img alt="CMake generator selection" class="align-center" src="_images/cmake_cminpack_3.png" />
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">If you want to build 64 bit libraries, then choose &#8220;Visual Studio 9 2008 Win64&#8221; as generator.</p>
</div>
<p>By default, all of the Boost modules will be built. If you want to build only the required modules for PCL,
then fill the <strong>BUILD_PROJECTS</strong> CMake entry (which is set to <cite>ALL</cite> by default) with a semicolon-seperated
list of boost modules:</p>
<div class="highlight-python"><div class="highlight"><pre>BUILD_PROJECTS : system;filesystem;date_time;thread;iostreams;tr1;serialization;mpi
</pre></div>
</div>
<p>Also, uncheck the <strong>ENABLE_STATIC_RUNTIME</strong> checkbox. Then, click &#8220;Configure&#8221; again. If you get some
errors related to Python, then uncheck <strong>WITH_PYTHON</strong> checkbox, and click &#8220;Configure&#8221; again.
Now, in the CMake log, you should see something like:</p>
<div class="highlight-python"><div class="highlight"><pre>Reading boost project directories (per BUILD_PROJECTS)

+ date_time
+ thread
+ serialization
+ system
+ filesystem
+ mpi
+-- optional python bindings disabled since PYTHON_FOUND is false.
+ tr1
</pre></div>
</div>
<p>Now, click &#8220;Generate&#8221;. A Visual Studio solution file will be genrated inside the build folder
(e.g. C:/PCL_dependencies/boost-cmake/build). Open the <cite>Boost.sln</cite> file, then right click on
<cite>INSTALL</cite> project and choose <cite>Build</cite>. The <a href="#id1"><span class="problematic" id="id2">`</span></a>INSTALL`project will trigger the build of all the projects
in the solution file, and then will install the build libraries along with the header files to the default
installation folder (e.g. C:/Program Files (x86)/Boost).</p>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p>If you are building the mpi boost module, and you are using CMake &lt;= 2.8.7, you may run into the following error:</p>
<div class="highlight-python"><div class="highlight"><pre>LINK : fatal error LNK1104: cannot open file &#39;C:\Program Files\MPICH2\lib\mpi.lib C:\Program Files\MPICH2\lib\cxx.lib&#39;
</pre></div>
</div>
<p>As a workaround (until CMake 2.8.8 is out), go back to CMake gui, check the &#8220;Advanced&#8221; checkbox at the top right of
CMake window, and edit these entries as follows (please adjust the paths according to your system):</p>
<div class="highlight-python"><div class="highlight"><pre>MPI_CXX_LIBRARIES : C:/Program Files/MPICH2/lib/cxx.lib;C:/Program Files/MPICH2/lib/mpi.lib
MPI_LIBRARY       : C:/Program Files/MPICH2/lib/mpi.lib
</pre></div>
</div>
<p class="last">Then, click &#8220;Generate&#8221;. Visual Studio will ask you to reload the solution, then re build the <strong>INSTALL</strong> project.</p>
</div>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">If you get some errors during the installation process, it could be caused by the UAC of MS Windows
Vista or Seven. To fix this, close Visual Studio, right click on its icon on the Desktop or in the Start Menu,
and choose &#8220;Run as administrator&#8221;. Then Open the <cite>Boost.sln</cite> file, and build the <strong>INSTALL</strong> project.</p>
</div>
</div></blockquote>
</li>
<li><p class="first"><strong>Eigen</strong> :</p>
<blockquote>
<div><p>Eigen is a headers only library, so you can use the Eigen installer provided on the
<a class="reference external" href="http://www.pointclouds.org/downloads/windows.html">downloads page</a>.</p>
</div></blockquote>
</li>
<li><p class="first"><strong>Flann</strong> :</p>
<blockquote>
<div><p>Let&#8217;s move on to <cite>FLANN</cite>. Then open CMake-gui and fill in the fields:</p>
<div class="highlight-python"><div class="highlight"><pre>Where is my source code: C:/PCL_dependencies/flann-1.7.1-src
Where to build binaries: C:/PCL_dependencies/flann-1.7.1-src/build
</pre></div>
</div>
<p>Hit the &#8220;Configure&#8221; button. Proceed and be sure to choose the correct &#8220;Generator&#8221; on the next window.
You can safley ignore any warning message about hdf5.</p>
<p>Now, on my machine I had to manually set the <cite>BUILD_PYTHON_BINDINGS</cite>
and <cite>BUILD_MATLAB_BINDINGS</cite> to OFF otherwise it would not continue to the next
step as it is complaining about unable to find Python and Matlab. Click on
&#8220;Advanced mode&#8221; and find them, or alternatively, add those entries by clicking
on the &#8220;Add Entry&#8221; button in the top right of the CMake-gui window.  Add one
entry named &#8220;BUILD_PYTHON_BINDINGS&#8221;, set its type to &#8220;Bool&#8221; and its value to
unchecked. Do the same with the &#8220;BUILD_MATLAB_BINDINGS&#8221; entry.</p>
<p>Now hit the &#8220;Configure&#8221; button and it should work. Go for the &#8220;Generate&#8221; This will generate
the required project files/makefiles to build the library. Now you can simply
go to <cite>C:/PCL_dependencies/flann-1.7.1-src/build</cite> and proceed with the compilation using
your toolchain. In case you use Visual Studio, you will find the Visual Studio
Solution file in that folder.</p>
<p>Build the <strong>INSTALL</strong> project in <strong>release</strong> mode.</p>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">If you don&#8217;t have a Python interpreter installed CMake would probably not allow you
to generate the project files. To solve this problem you can install the Python interpreter
(<a class="reference external" href="https://www.python.org/download/windows/">https://www.python.org/download/windows/</a>) or comment the <cite>add_subdirectory( test )</cite> line
from C:/PCL_dependencies/flann-1.7.1-src/CMakeLists.txt .</p>
</div>
</div></blockquote>
</li>
<li><p class="first"><strong>QHull</strong> :</p>
<blockquote>
<div><p>Setup the CMake fields with the <cite>qhull</cite> paths:</p>
<div class="highlight-python"><div class="highlight"><pre>Where is my source code: C:/PCL_dependencies/qhull-2011.1
Where to build binaries: C:/PCL_dependencies/qhull-2011.1/build
</pre></div>
</div>
<p>Before clicking on &#8220;Configure&#8221;, click on &#8220;Add Entry&#8221; button in the top right of CMake gui, in
the popup window, fill the fiels as follows:</p>
<div class="highlight-python"><div class="highlight"><pre>Name  : CMAKE_DEBUG_POSTFIX
Type  : STRING
Value : _d
</pre></div>
</div>
<p>Then click &#8220;Ok&#8221;. This entry will define a postfix to distinguish between debug and release
libraries.</p>
<p>Then hit &#8220;Configure&#8221; twice and &#8220;Generate&#8221;. Then build the <strong>INSTALL</strong> project, both in
<strong>debug</strong> and <strong>release</strong> configuration.</p>
</div></blockquote>
</li>
<li><p class="first"><strong>VTK</strong> :</p>
<blockquote>
<div><div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">If you want to build PCL GUI tools, you need to build VTK with Qt support, so obviously, you need to build/install Qt before VTK.</p>
</div>
<p>To configure Qt, we need to have Perl installed on your system. If it is not, just download and install it from <a class="reference external" href="http://strawberryperl.com">http://strawberryperl.com</a>.</p>
<p>To build Qt from sources, download the source archive from Qt website. Unpack it some where on your disk (C:\Qt\4.8.0 e.g. for Qt 4.8.0).
Then open a <cite>Visual Studio Command Prompt</cite> :</p>
<p>Click <strong>Start</strong>, point to <strong>All Programs</strong>, point to <strong>Microsoft Visual Studio 20XX</strong>, point to <strong>Visual Studio Tools</strong>,
and then click <strong>Visual Studio Command Prompt</strong> if you are building in 32bit, or <strong>Visual Studio x64 Win64 Command Prompt</strong>
if you are building in 64bit.</p>
<p>In the command prompt, <strong>cd</strong> to Qt directory:</p>
<div class="highlight-python"><div class="highlight"><pre>prompt&gt; cd c:\Qt\4.8.0
</pre></div>
</div>
<p>We configure a minimal build of Qt using the Open Source licence. If you need a custom build, adjust the options as needed:</p>
<div class="highlight-python"><div class="highlight"><pre>prompt&gt; configure -opensource -confirm-license -fast -debug-and-release -nomake examples -nomake demos -no-qt3support -no-xmlpatterns -no-multimedia -no-phonon -no-accessibility -no-openvg -no-webkit -no-script -no-scripttools -no-dbus -no-declarative
</pre></div>
</div>
<p>Now, let&#8217;s build Qt:</p>
<div class="highlight-python"><div class="highlight"><pre><span class="n">prompt</span><span class="o">&gt;</span> <span class="n">nmake</span>
</pre></div>
</div>
<p>Now, we can clear all the intermediate files to free some disk space:</p>
<div class="highlight-python"><div class="highlight"><pre>prompt&gt; nmake clean
</pre></div>
</div>
<p>We&#8217;re done with Qt! But before building VTK, we need to set an environment variable:</p>
<div class="highlight-python"><div class="highlight"><pre>QtDir = C:\Qt\4.8.0
</pre></div>
</div>
<p>and then, append <cite>%QtDir%\bin</cite> to your PATH environment variable.</p>
<p>Now, configure VTK using CMake (make sure to restart CMake after setting the environment variables).
First, setup the CMake fields with the <cite>VTK</cite> paths, e.g.:</p>
<div class="highlight-python"><div class="highlight"><pre>Where is my source code: C:/PCL_dependencies/VTK
Where to build binaries: C:/PCL_dependencies/VTK/bin32
</pre></div>
</div>
<p>Then hit &#8220;Configure&#8221;. Check this checkbox and click &#8220;Configure&#8221;:</p>
<div class="highlight-python"><div class="highlight"><pre><span class="n">VTK_USE_QT</span>
</pre></div>
</div>
<p>Make sure CMake did find Qt by looking at <cite>QT_QMAKE_EXECUTABLE</cite> CMake entry. If not, set it to the path of <cite>qmake.exe</cite>,
e.g. <cite>C:\Qt\4.8.0\bin\qmake.exe</cite>, then click &#8220;Configure&#8221;.</p>
<p>If Qt is found, then check this checkbox and click &#8220;Configure&#8221;:</p>
<div class="highlight-python"><div class="highlight"><pre><span class="n">VTK_USE_QVTK_QTOPENGL</span>
</pre></div>
</div>
<p>Then, click &#8220;Generate&#8221;, open the generated solution file, and build it in debug and release.</p>
<p>That&#8217;s it, we&#8217;re done with the dependencies!</p>
</div></blockquote>
</li>
<li><p class="first"><strong>GTest</strong> :</p>
<blockquote>
<div><p>In case you want PCL tests (not recommanded for users), you need to compile the <cite>googletest</cite> library (GTest).
Setup the CMake fields as usual:</p>
<div class="highlight-python"><div class="highlight"><pre>Where is my source code: C:/PCL_dependencies/gtest-1.6.0
Where to build binaries: C:/PCL_dependencies/gtest-1.6.0/bin32
</pre></div>
</div>
<p>Hit &#8220;Configure&#8221; and set the following options:</p>
<div class="highlight-python"><div class="highlight"><pre>BUILD_SHARED_LIBS                OFF
gtest_force_shared_crt           ON
</pre></div>
</div>
<p>Generate and build the resulting project.</p>
</div></blockquote>
</li>
</ul>
</div>
<div class="section" id="building-pcl">
<h1>Building PCL</h1>
<p>Now that you built and installed PCL dependencies, you can follow the &#8220;<a class="reference internal" href="compiling_pcl_windows.php#compiling-pcl-windows"><em>Compiling PCL from source on Windows</em></a>&#8221; tutorial
to build PCL itself.</p>
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