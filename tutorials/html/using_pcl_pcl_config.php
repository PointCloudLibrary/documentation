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
    
    <title>Using PCL in your own project</title>
    
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
            
  <div class="section" id="using-pcl-in-your-own-project">
<span id="using-pcl-pcl-config"></span><h1>Using PCL in your own project</h1>
<p>This tutorial explains how to use PCL in your own projects.</p>
</div>
<div class="section" id="prerequisites">
<h1>Prerequisites</h1>
<p>We assume you have downloaded, compiled and installed PCL on your
machine.</p>
</div>
<div class="section" id="project-settings">
<h1>Project settings</h1>
<p>Let us say the project is placed under /PATH/TO/MY/GRAND/PROJECT that
contains a lonely cpp file name <tt class="docutils literal"><span class="pre">pcd_write.cpp</span></tt> (copy it from the
<a class="reference internal" href="writing_pcd.php#writing-pcd"><em>Writing Point Cloud data to PCD files</em></a> tutorial). In the same folder, create a file named
CMakeLists.txt that contains:</p>
<div class="highlight-cmake"><div class="highlight"><pre><span class="nb">cmake_minimum_required</span><span class="p">(</span><span class="s">VERSION</span> <span class="s">2.6</span> <span class="s">FATAL_ERROR</span><span class="p">)</span>
<span class="nb">project</span><span class="p">(</span><span class="s">MY_GRAND_PROJECT</span><span class="p">)</span>
<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.3</span> <span class="s">REQUIRED</span> <span class="s">COMPONENTS</span> <span class="s">common</span> <span class="s">io</span><span class="p">)</span>
<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_executable</span><span class="p">(</span><span class="s">pcd_write_test</span> <span class="s">pcd_write.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span><span class="p">(</span><span class="s">pcd_write_test</span> <span class="o">${</span><span class="nv">PCL_COMMON_LIBRARIES</span><span class="o">}</span> <span class="o">${</span><span class="nv">PCL_IO_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</div>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>Now, let&#8217;s see what we did.</p>
<div class="highlight-cmake"><div class="highlight"><pre><span class="nb">cmake_minimum_required</span><span class="p">(</span><span class="s">VERSION</span> <span class="s">2.6</span> <span class="s">FATAL_ERROR</span><span class="p">)</span>
</pre></div>
</div>
<p>This is mandatory for cmake, and since we are making very basic
project we don&#8217;t need features from cmake 2.8 or higher.</p>
<div class="highlight-cmake"><div class="highlight"><pre><span class="nb">project</span><span class="p">(</span><span class="s">MY_GRAND_PROJECT</span><span class="p">)</span>
</pre></div>
</div>
<p>This line names your project and sets some useful cmake variables
such as those to refer to the source directory
(MY_GRAND_PROJECT_SOURCE_DIR) and the directory from which you are
invoking cmake (MY_GRAND_PROJECT_BINARY_DIR).</p>
<div class="highlight-cmake"><div class="highlight"><pre><span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.3</span> <span class="s">REQUIRED</span> <span class="s">COMPONENTS</span> <span class="s">common</span> <span class="s">io</span><span class="p">)</span>
</pre></div>
</div>
<p>We are requesting to find the PCL package at minimum version 1.3. We
also says that it is <tt class="docutils literal"><span class="pre">REQUIRED</span></tt> meaning that cmake will fail
gracefully if it can&#8217;t be found. As PCL is modular one can request:</p>
<ul class="simple">
<li>only one component: find_package(PCL 1.3 REQUIRED COMPONENTS io)</li>
<li>several: find_package(PCL 1.3 REQUIRED COMPONENTS io common)</li>
<li>all existing: find_package(PCL 1.3 REQUIRED)</li>
</ul>
<div class="highlight-cmake"><div class="highlight"><pre><span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</div>
<p>When PCL is found, several related variables are set:</p>
<ul class="simple">
<li><cite>PCL_FOUND</cite>: set to 1 if PCL is found, otherwise unset</li>
<li><cite>PCL_INCLUDE_DIRS</cite>: set to the paths to PCL installed headers and
the dependency headers</li>
<li><cite>PCL_LIBRARIES</cite>: set to the file names of the built and installed PCL libraries</li>
<li><cite>PCL_LIBRARY_DIRS</cite>: set to the paths to where PCL libraries and 3rd
party dependencies reside</li>
<li><cite>PCL_VERSION</cite>: the version of the found PCL</li>
<li><cite>PCL_COMPONENTS</cite>: lists all available components</li>
<li><cite>PCL_DEFINITIONS</cite>: lists the needed preprocessor definitions and compiler flags</li>
</ul>
<p>To let cmake know about external headers you include in your project,
one needs to use <tt class="docutils literal"><span class="pre">include_directories()</span></tt> macro. In our case
<tt class="docutils literal"><span class="pre">PCL_INCLUDE_DIRS</span></tt>, contains exactly what we need, thus we ask cmake
to search the paths it contains for a header potentially included.</p>
<div class="highlight-cmake"><div class="highlight"><pre><span class="nb">add_executable</span><span class="p">(</span><span class="s">pcd_write_test</span> <span class="s">pcd_write.cpp</span><span class="p">)</span>
</pre></div>
</div>
<p>Here, we tell cmake that we are trying to make an executable file
named <tt class="docutils literal"><span class="pre">pcd_write_test</span></tt> from one single source file
<tt class="docutils literal"><span class="pre">pcd_write.cpp</span></tt>. CMake will take care of the suffix (<tt class="docutils literal"><span class="pre">.exe</span></tt> on
Windows platform and blank on UNIX) and the permissions.</p>
<div class="highlight-cmake"><div class="highlight"><pre><span class="nb">target_link_libraries</span><span class="p">(</span><span class="s">pcd_write_test</span> <span class="o">${</span><span class="nv">PCL_COMMON_LIBRARIES</span><span class="o">}</span> <span class="o">${</span><span class="nv">PCL_IO_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</div>
<p>The executable we are building makes call to PCL functions. So far, we
have only included the PCL headers so the compilers knows about the
methods we are calling. We need also to make the linker knows about
the libraries we are linking against. As said before the, PCL
found libraries are refered to using <tt class="docutils literal"><span class="pre">PCL_LIBRARIES</span></tt> variable, all
that remains is to trigger the link operation which we do calling
<tt class="docutils literal"><span class="pre">target_link_libraries()</span></tt> macro.
PCLConfig.cmake uses a CMake special feature named <cite>EXPORT</cite> which
allows for using others&#8217; projects targets as if you built them
yourself. When you are using such targets they are called <cite>imported
targets</cite> and acts just like anyother target.</p>
</div>
<div class="section" id="compiling-and-running-the-project">
<h1>Compiling and running the project</h1>
<div class="section" id="using-command-line-cmake">
<h2>Using command line CMake</h2>
<p>Make a directory called <tt class="docutils literal"><span class="pre">build</span></tt>, in which the compilation will be
done. Do:</p>
<div class="highlight-python"><div class="highlight"><pre>$ cd /PATH/TO/MY/GRAND/PROJECT
$ mkdir build
$ cd build
$ cmake ..
</pre></div>
</div>
<p>You will see something similar to:</p>
<div class="highlight-python"><div class="highlight"><pre>-- The C compiler identification is GNU
-- The CXX compiler identification is GNU
-- Check for working C compiler: /usr/bin/gcc
-- Check for working C compiler: /usr/bin/gcc -- works
-- Detecting C compiler ABI info
-- Detecting C compiler ABI info - done
-- Check for working CXX compiler: /usr/bin/c++
-- Check for working CXX compiler: /usr/bin/c++ -- works
-- Detecting CXX compiler ABI info
-- Detecting CXX compiler ABI info - done
-- Found PCL_IO: /usr/local/lib/libpcl_io.so
-- Found PCL: /usr/local/lib/libpcl_io.so (Required is at least version &quot;1.0&quot;)
-- Configuring done
-- Generating done
-- Build files have been written to: /PATH/TO/MY/GRAND/PROJECT/build
</pre></div>
</div>
<p>If you want to see what is written on the CMake cache:</p>
<div class="highlight-python"><div class="highlight"><pre><span class="n">CMAKE_BUILD_TYPE</span>
<span class="n">CMAKE_INSTALL_PREFIX</span>             <span class="o">/</span><span class="n">usr</span><span class="o">/</span><span class="n">local</span>
<span class="n">PCL_DIR</span>                          <span class="o">/</span><span class="n">usr</span><span class="o">/</span><span class="n">local</span><span class="o">/</span><span class="n">share</span><span class="o">/</span><span class="n">pcl</span>
</pre></div>
</div>
<p>Now, we can build up our project, simply typing:</p>
<div class="highlight-python"><div class="highlight"><pre>$ make
</pre></div>
</div>
<p>The result should be as follow:</p>
<div class="highlight-python"><div class="highlight"><pre>Scanning dependencies of target pcd_write_test
[100%] Building CXX object
CMakeFiles/pcd_write_test.dir/pcd_write.cpp.o
Linking CXX executable pcd_write_test
[100%] Built target pcd_write_test
</pre></div>
</div>
<p>The project is now compiled, linked and ready to test:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./pcd_write_test
</pre></div>
</div>
<p>Which leads to this:</p>
<div class="highlight-python"><div class="highlight"><pre>Saved 5 data points to test_pcd.pcd.
  0.352222 -0.151883 -0.106395
  -0.397406 -0.473106 0.292602
  -0.731898 0.667105 0.441304
  -0.734766 0.854581 -0.0361733
  -0.4607 -0.277468 -0.916762
</pre></div>
</div>
</div>
<div class="section" id="using-cmake-gui-e-g-windows">
<h2>Using CMake gui (e.g. Windows)</h2>
<p>Run CMake GUI, and fill these fields :</p>
<blockquote>
<div><ul class="simple">
<li><tt class="docutils literal"><span class="pre">Where</span> <span class="pre">is</span> <span class="pre">the</span> <span class="pre">source</span> <span class="pre">code</span></tt> : this is the folder containing the CMakeLists.txt file and the sources.</li>
<li><tt class="docutils literal"><span class="pre">Where</span> <span class="pre">to</span> <span class="pre">build</span> <span class="pre">the</span> <span class="pre">binaries</span></tt> : this is where the Visual Studio project files will be generated</li>
</ul>
</div></blockquote>
<p>Then, click <tt class="docutils literal"><span class="pre">Configure</span></tt>. You will be prompted for a generator/compiler. Then click the <tt class="docutils literal"><span class="pre">Generate</span></tt>
button. If there is no errors, the project files will be generated into the <tt class="docutils literal"><span class="pre">Where</span> <span class="pre">to</span> <span class="pre">build</span> <span class="pre">the</span> <span class="pre">binaries</span></tt>
folder.</p>
<p>Open the sln file, and build your project!</p>
</div>
</div>
<div class="section" id="weird-installations">
<h1>Weird installations</h1>
<p>CMake has a list of default searchable paths where it seeks for
FindXXX.cmake or XXXConfig.cmake. If you happen to install in some non
obvious repository (let us say in <cite>Documents</cite> for evils) then you can
help cmake find PCLConfig.cmake adding this line:</p>
<div class="highlight-cmake"><div class="highlight"><pre><span class="nb">set</span><span class="p">(</span><span class="s">PCL_DIR</span> <span class="s2">&quot;/path/to/PCLConfig.cmake&quot;</span><span class="p">)</span>
</pre></div>
</div>
<p>before this one:</p>
<div class="highlight-cmake"><div class="highlight"><pre>find_package(PCL 1.3 REQUIRED COMPONENTS common io)
  ...
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