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
    
    <title>Compiling PCL from source on Windows</title>
    
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
            
  <div class="section" id="compiling-pcl-from-source-on-windows">
<span id="compiling-pcl-windows"></span><h1>Compiling PCL from source on Windows</h1>
<p>This tutorial explains how to build the Point Cloud Library <strong>from source</strong> on
Microsoft Windows platforms. In this tutorial, we assume that you have built and installed
all the required dependencies, or that you have installed them using the dependencies
installers provided on the <a class="reference external" href="http://www.pointclouds.org/downloads/windows.html">downloads page</a>.</p>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">If you installed PCL using one of the <strong>all-in-one</strong> provided installers, then this tutorial is not for you.
The <strong>all-in-one</strong> installer already contains prebuilt PCL binaries which are ready to be used without any compilation step.</p>
</div>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">If there is no installers for your compiler, it is recommended that you build the dependencies
out of source. The <a class="reference internal" href="compiling_pcl_dependencies_windows.php#compiling-pcl-dependencies-windows"><em>Building PCL&#8217;s dependencies from source on Windows</em></a> tutorial should guide you through the download
and the compilation of all the required dependencies.</p>
</div>
<img alt="Microsoft Windows logo" class="align-right" src="_images/windows_logo.png" />
</div>
<div class="section" id="requirements">
<h1>Requirements</h1>
<p>we assume that you have built and installed all the required dependencies, or that you have installed
them using the dependencies installers provided on the <a class="reference external" href="http://www.pointclouds.org/downloads/windows.html">downloads page</a>.
Installing them to the default locations will make configuring PCL easier.</p>
<ul class="simple">
<li><strong>Boost</strong></li>
</ul>
<p>used for shared pointers, and threading. <strong>mandatory</strong></p>
<ul class="simple">
<li><strong>Eigen</strong></li>
</ul>
<p>used as the matrix backend for SSE optimized math. <strong>mandatory</strong></p>
<ul class="simple">
<li><strong>FLANN</strong></li>
</ul>
<p>used in <cite>kdtree</cite> for fast approximate nearest neighbors search. <strong>mandatory</strong></p>
<ul class="simple">
<li><strong>Visualization ToolKit (VTK)</strong></li>
</ul>
<p>used in <cite>visualization</cite> for 3D point cloud rendering and visualization. <strong>mandatory</strong></p>
<ul class="simple">
<li><strong>Qt</strong></li>
</ul>
<p>used for applications with a graphical user interface (GUI) <strong>optional</strong></p>
<ul class="simple">
<li><strong>QHULL</strong></li>
</ul>
<p>used for convex/concave hull decompositions in <cite>surface</cite>. <strong>optional</strong></p>
<ul class="simple">
<li><strong>OpenNI</strong> and patched <strong>Sensor Module</strong></li>
</ul>
<p>used to grab point clouds from OpenNI compliant devices. <strong>optional</strong></p>
<ul class="simple">
<li><strong>GTest</strong> version &gt;= 1.6.0 (<a class="reference external" href="http://code.google.com/p/googletest/">http://code.google.com/p/googletest/</a>)</li>
</ul>
<p>is needed only to build PCL tests. We do not provide GTest installers. <strong>optional</strong></p>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">Though not a dependency per se, don&#8217;t forget that you also need the CMake
build system (<a class="reference external" href="http://www.cmake.org/">http://www.cmake.org/</a>), at least version <strong>2.8.7</strong>. A Git client
for Windows is also required to download the PCL source code.</p>
</div>
</div>
<div class="section" id="downloading-pcl-source-code">
<h1>Downloading PCL source code</h1>
<p>To build the current official release, download the source archive from
<a class="reference external" href="http://pointclouds.org/downloads/">http://pointclouds.org/downloads/</a> and extract it somewhere on your disk, say C:\PCL\PCL-1.5.1-Source.
In this case, you can go directly to Configuring PCL section, and pay attention to adjust the
paths accordingly.</p>
<p>Or, you might want to build an experimental version
of PCL to test some new features not yet available in the official releases.
For this, you will need git ( <a class="reference external" href="http://git-scm.com/download">http://git-scm.com/download</a> ).</p>
<p>The invocation to download the source code is thus, using a command line:</p>
<blockquote>
<div>cd wherever/you/want/to/put/the/repo/
git clone <a class="reference external" href="https://github.com/PointCloudLibrary/pcl.git">https://github.com/PointCloudLibrary/pcl.git</a></div></blockquote>
<p>You could also use Github for Windows (<a class="reference external" href="https://windows.github.com/">https://windows.github.com/</a>), but that is potentially more
troublesome than setting up git on windows.</p>
</div>
<div class="section" id="configuring-pcl">
<h1>Configuring PCL</h1>
<p>On Windows, we recommend to build <strong>shared</strong> PCL libraries with <strong>static</strong> dependencies. In this tutorial, we will use
static dependencies when possible to build shared PCL. You can easily switch to using shared dependencies. Then, you need
to make sure you put the dependencies&#8217; dlls either in your <cite>PATH</cite> or in the same folder as PCL dlls and executables.
You can also build static PCL libraries if you want.</p>
<p>Run the CMake-gui application and fill in the fields:</p>
<div class="highlight-python"><div class="highlight"><pre>Where is the source code   : C:/PCL/pcl
Where to build the binaries: C:/PCL
</pre></div>
</div>
<p>Now hit the &#8220;Configure&#8221; button. You will be asked for a <cite>generator</cite>. A generator is simply a compiler.</p>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p>In this tutorial, we will be using Microsoft Visual C++ 2010 compiler. If you want to build 32bit PCL, then pick the
&#8220;<strong>Visual Studio 10</strong>&#8221; generator. If you want to build 64bit PCL, then pick the &#8220;<strong>Visual Studio 10 Win64</strong>&#8221;.</p>
<p class="last">Make sure you have installed the right third party dependencies. You cannot mix 32bit and 64bit code, and it is
highly recommanded to not mix codes compiled with different compilers.</p>
</div>
<img alt="Choosing a generator" class="align-center" src="_images/cmake_generator.png" />
<p>In the remaining of this tutorial, we will be using &#8220;<strong>Visual Studio 10 Win64</strong>&#8221; generator. Once you picked your generator,
hit finish to close the dialog window. CMake will start configuring PCL and looking for its dependencies. For example, we
can get this output :</p>
<img alt="CMake configure result" class="align-center" src="_images/cmake_configure_noerror.png" />
<p>The upper part of CMake window contains a list of CMake variables and its respective values. The lower part contains some logging
output that can help figure out what is happening. We can see, for example, that VTK was not found, thus, the visualization module
will not get built.</p>
<p>Before solving the VTK issue, let&#8217;s organize the CMake variables in groups by checking the <cite>Grouped</cite> checkbox in the top right of
CMake window. Let&#8217;s check also the <cite>Advanced</cite> checkbox to show some advanced CMake variables. Now, if we want to look for a specific
variable value, we can either browse the CMake variables to look for it, or we can use the <cite>Search:</cite> field to type the variable name.</p>
<img alt="CMake groupped and advanced variables" class="align-center" src="_images/cmake_grouped_advanced.png" />
<p>Let&#8217;s check whether CMake did actually find the needed third party dependencies or not :</p>
<ul>
<li><p class="first"><strong>Boost</strong> :</p>
<blockquote>
<div><p>CMake was not able to find boost automatically. No problem, we will help it find it :) . If CMake has found your
boost installation, then skip to the next bullet item.</p>
<img alt="Boost" class="align-center" src="_images/cmake_boost.png" />
<p>Let&#8217;s tell CMake where boost headers are by specifiying the headers path in <strong>Boost_INCLUDE_DIR</strong> variable. For example, my boost
headers are in C:\Program Files\PCL-Boost\include (C:\Program Files\Boost\include for newer installers).
Then, let&#8217;s hit <cite>configure</cite> again ! Hopefully, CMake is now able to find all the other items (the libraries).</p>
<img alt="Boost" class="align-center" src="_images/cmake_boost_found.png" />
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">This behaviour is not common for all libraries. Generally, if CMake is not able to find a specific library or package, we have to
manually set the values of all the CMake related variables. Hopefully, the CMake script responsible of finding boost
is able to find libraries using the headers path.</p>
</div>
</div></blockquote>
</li>
<li><p class="first"><strong>Eigen</strong> :</p>
<blockquote>
<div><p>Eigen is a header-only library, thus, we need only <strong>EIGEN_INCLUDE_DIR</strong> to be set. Hopefully, CMake did fing Eigen.</p>
<img alt="Eigen include dir" class="align-center" src="_images/cmake_eigen_include_dir.png" />
</div></blockquote>
</li>
<li><p class="first"><strong>FLANN</strong> :</p>
<blockquote>
<div><p>CMake was able to find my FLANN installation. By default on windows, PCL will pick the static FLANN libraries
with <cite>_s</cite> suffix. Thus, the <strong>FLANN_IS_STATIC</strong> checkbox is checked by default.</p>
<img alt="FLANN" class="align-center" src="_images/cmake_flann.png" />
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">If you rather want to use the <strong>shared</strong> FLANN libraries (those without the <cite>_s</cite> suffix), you need to manually edit the
<strong>FLANN_LIBRARY</strong> and <strong>FLANN_LIBRARY_DEBUG</strong> variables to remove the <cite>_s</cite> suffix and do not forget to uncheck
<strong>FLANN_IS_STATIC</strong>. Make sure the FLANN dlls are either in your PATH or in the same folder as your executables.</p>
</div>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">In recent PCL, the <strong>FLANN_IS_STATIC</strong> checkbox no longer exists.</p>
</div>
</div></blockquote>
</li>
<li><p class="first"><strong>Qt</strong> :</p>
<blockquote>
<div><p>It is highly recommended to install Qt to the default path suggested by the installer. You need then to define an
environment variable named <strong>QTDIR</strong> to point to Qt installation path (e.g. <cite>C:\Qt\4.8.0</cite>). Also, you need to
append the bin folder to the <strong>PATH</strong> environment variable. Once you modify the environment variables, you need to
restart CMake and click &#8220;Configure&#8221; again. If Qt is not found, you need at least to fill <strong>QT_QMAKE_EXECUTABLE</strong>
CMake entry with the path of <cite>qmake.exe</cite> (e.g. C:\Qt\4.8.0\bin\qmake.exe), then click &#8220;Configure&#8221;.</p>
</div></blockquote>
</li>
<li><p class="first"><strong>VTK</strong> :</p>
<blockquote>
<div><p>CMake did not find my VTK installation. There is only one VTK related CMake variable called <strong>VTK_DIR</strong>. We have to set it
to the path of the folder containing <strong>VTKConfig.cmake</strong>, which is in my case : C:\Program Files\VTK 5.6\lib\vtk-5.6
(C:\Program Files\VTK 5.8.0\lib\vtk-5.8 for VTK 5.8).
After you set <strong>VTK_DIR</strong>, hit <cite>configure</cite> again.</p>
<img alt="VTK" class="align-center" src="_images/cmake_vtk_configure.png" />
<p>After clicking <cite>configure</cite>, in the logging window, we can see that VTK is found, but the <cite>visualization</cite> module is still
disabled <cite>manually</cite>. We have then to enable it by checking the <strong>BUILD_visualization</strong> checkbox. You can also do the same thing
with the <cite>apps</cite> module. Then, hit <cite>configure</cite> again.</p>
<img alt="VTK found, enable visualization" class="align-center" src="_images/cmake_vtk_found_enable_visualization.png" />
</div></blockquote>
</li>
<li><p class="first"><strong>QHull</strong> :</p>
<blockquote>
<div><p>CMake was able to find my QHull installation. By default on windows, PCL will pick the static QHull libraries
with <cite>static</cite> suffix.</p>
<img alt="QHull" class="align-center" src="_images/cmake_qhull.png" />
</div></blockquote>
</li>
<li><p class="first"><strong>OpenNI</strong> :</p>
<blockquote>
<div><p>CMake was able to find my OpenNI installation.</p>
<img alt="OpenNI" class="align-center" src="_images/cmake_openni.png" />
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">CMake do not look for the installed OpenNI Sensor module. It is needed at runtime.</p>
</div>
</div></blockquote>
</li>
<li><p class="first"><strong>GTest</strong> :</p>
<blockquote>
<div><p>If you want to build PCL tests, you need to download GTest and build it yourself. In this tutorial, we will not build tests.</p>
</div></blockquote>
</li>
</ul>
<p>Once CMake has found all the needed dependencies, let&#8217;s see the PCL specific CMake variables :</p>
<img alt="PCL" class="align-center" src="_images/cmake_pcl.png" />
<ul class="simple">
<li><strong>PCL_SHARED_LIBS</strong> is checked by default. Uncheck it if you want static PCL libs (not recommanded).</li>
<li><strong>CMAKE_INSTALL_PREFIX</strong> is where PCL will be installed after building it (more information on this later).</li>
</ul>
<p>If you have the Pro version of Microsoft Visual Studio, you can check <strong>USE_PROJECT_FOLDERS</strong> checkbox to organize PCL
projects in folders within the PCL solution. If you have an express edition, it is recommended to keep it unchecked, as in
express editions, project folders are disabled.</p>
<p>Once PCL configuration is ok, hit the <cite>Generate</cite> button. CMake will then generate Visual Studio project files (vcproj files)
and the main solution file (PCL.sln) in C:\PCL directory.</p>
</div>
<div class="section" id="building-pcl">
<h1>Building PCL</h1>
<p>Open that generated solution file (PCL.sln) to finally build the PCL libraries. This is how your solution will look like
whether you enabled <strong>USE_PROJECT_FOLDERS</strong> (left) or not (right).</p>
<img alt="PCL solution with project folders" class="align-center" src="_images/pcl_solution_with_projects_folder.png" />
<p>Building the &#8220;ALL_BUILD&#8221; project will build everything.</p>
<img alt="Build ALL_BUILD project" class="align-center" src="_images/msvc_build_build_all.jpg" />
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">Make sure to build the &#8220;ALL_BUILD&#8221; project in both <strong>debug</strong> and <strong>release</strong> mode.</p>
</div>
</div>
<div class="section" id="installing-pcl">
<h1>Installing PCL</h1>
<p>To install the built libraries and executbles, you need to build the &#8220;INSTALL&#8221; project in the solution explorer.
This utility project will copy PCL headers, libraries and executable to the directory defined by the <strong>CMAKE_INSTALL_PREFIX</strong>
CMake variable.</p>
<img alt="Build INSTALL project" class="align-center" src="_images/msvc_build_install.jpg" />
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">Make sure to build the &#8220;INSTALL&#8221; project in both <strong>debug</strong> and <strong>release</strong> mode.</p>
</div>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">It is highly recommanded to add the bin folder in PCL installation tree (e.g. C:\Program Files\PCL\bin)
to your <strong>PATH</strong> environment variable.</p>
</div>
</div>
<div class="section" id="advanced-topics">
<h1>Advanced topics</h1>
<ul>
<li><p class="first"><strong>Building PCL Tests</strong> :</p>
<blockquote>
<div><p>If you want to build PCL tests, you need to download <cite>GTest</cite> 1.6 (<a class="reference external" href="http://code.google.com/p/googletest/">http://code.google.com/p/googletest/</a>) and build it yourself.
Make sure, when you configure GTest via CMake to check the <strong>gtest_force_shared_crt</strong> checkbox. You need, as usual, to build
<cite>GTest</cite> in both <strong>release</strong> and <strong>debug</strong>.</p>
<p>Back to PCL&#8217;s CMake settings, you have to fill the <strong>GTEST_*</strong> CMake entries (include directory, gtest libraries (debug and release)
and gtestmain libraries (debug and release)). Then, you have to check <strong>BUILD_TEST</strong> and <strong>BUILD_global_tests</strong> CMake checkboxes,
and hit <cite>Configure</cite> and <cite>Generate</cite>.</p>
</div></blockquote>
</li>
<li><p class="first"><strong>Building the documentation</strong> :</p>
<blockquote>
<div><p>You can build the doxygen documentation of PCL in order to have a local up-to-date api documentation. For this, you need
Doxygen (<a class="reference external" href="http://www.doxygen.org">http://www.doxygen.org</a>). You will need also the Graph Visualization Software (GraphViz, <a class="reference external" href="http://www.graphviz.org/">http://www.graphviz.org/</a>)
to get the doxygen graphics, specifically the <cite>dot</cite> executable.</p>
<p>Once you installed these two packages, hit <cite>Configure</cite>. Three CMake variables should be set (if CMake cannot find them,
you can fill them manually) :</p>
<ul class="simple">
<li><em>DOXYGEN_EXECUTABLE</em> : path to <cite>doxygen.exe</cite> (e.g. C:/Program Files (x86)/doxygen/bin/doxygen.exe)</li>
<li><em>DOXYGEN_DOT_EXECUTABLE</em> : path to <cite>dot.exe</cite> from GraphViz (e.g. C:/Program Files (x86)/Graphviz2.26.3/bin/dot.exe)</li>
<li><em>DOXYGEN_DOT_PATH</em> : path of the folder containing <cite>dot.exe</cite> from GraphViz (e.g. C:/Program Files (x86)/Graphviz2.26.3/bin)</li>
</ul>
<p>Then, you need to enable the <cite>documentation</cite> project in Visual Studio by checking the <strong>BUILD_DOCUMENTATION</strong> checkbox in CMake.</p>
<p>You can also build one single CHM file that will gather all the generated html files into one file. You need the <a class="reference external" href="http://www.microsoft.com/en-us/download/details.aspx?id=21138">Microsoft
HTML HELP Workshop</a>.
After you install the <cite>Microsoft HTML HELP Workshop</cite>, hit <cite>Configure</cite>. If CMake is not able to find <strong>HTML_HEL_COMPILER</strong>, then fill
it manually with the path to <cite>hhc.exe</cite> (e.g. C:/Program Files (x86)/HTML Help Workshop/hhc.exe), then click <cite>Configure</cite> and <cite>Generate</cite>.</p>
<p>Now, in PCL Visual Studio solution, you will have a new project called <cite>doc</cite>. To generate the documentation files, right click on it,
and choose <cite>Build</cite>. Then, you can build the <cite>INSTALL</cite> project so that the generated documentation files get copied to
<strong>CMAKE_INSTALL_PREFIX</strong>/PCL/share/doc/pcl/html folder (e.g. C:\Program Files\PCL\share\doc\pcl\html).</p>
</div></blockquote>
</li>
</ul>
</div>
<div class="section" id="using-pcl">
<h1>Using PCL</h1>
<p>We finally managed to compile the Point Cloud Library (PCL) as binaries for
Windows. You can start using them in your project by following the
<a class="reference internal" href="using_pcl_pcl_config.php#using-pcl-pcl-config"><em>Using PCL in your own project</em></a> tutorial.</p>
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