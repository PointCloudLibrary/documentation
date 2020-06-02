<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation - Point Cloud Library (PCL)</title>
</head>

<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8" />
    <title>Using PCL in your own project &#8212; PCL 0.0 documentation</title>
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
contains a lonely cpp file name <code class="docutils literal notranslate"><span class="pre">pcd_write.cpp</span></code> (copy it from the
<a class="reference internal" href="writing_pcd.php#writing-pcd"><span class="std std-ref">Writing Point Cloud data to PCD files</span></a> tutorial). In the same folder, create a file named
CMakeLists.txt that contains:</p>
<div class="highlight-cmake notranslate"><div class="highlight"><pre><span></span><span class="nb">cmake_minimum_required</span><span class="p">(</span><span class="s">VERSION</span> <span class="s">2.6</span> <span class="s">FATAL_ERROR</span><span class="p">)</span>
<span class="nb">project</span><span class="p">(</span><span class="s">MY_GRAND_PROJECT</span><span class="p">)</span>
<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.3</span> <span class="s">REQUIRED</span> <span class="s">COMPONENTS</span> <span class="s">common</span> <span class="s">io</span><span class="p">)</span>
<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_executable</span><span class="p">(</span><span class="s">pcd_write_test</span> <span class="s">pcd_write.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span><span class="p">(</span><span class="s">pcd_write_test</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</div>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>Now, let’s see what we did.</p>
<div class="highlight-cmake notranslate"><div class="highlight"><pre><span></span><span class="nb">cmake_minimum_required</span><span class="p">(</span><span class="s">VERSION</span> <span class="s">2.6</span> <span class="s">FATAL_ERROR</span><span class="p">)</span>
</pre></div>
</div>
<p>This is mandatory for cmake, and since we are making very basic
project we don’t need features from cmake 2.8 or higher.</p>
<div class="highlight-cmake notranslate"><div class="highlight"><pre><span></span><span class="nb">project</span><span class="p">(</span><span class="s">MY_GRAND_PROJECT</span><span class="p">)</span>
</pre></div>
</div>
<p>This line names your project and sets some useful cmake variables
such as those to refer to the source directory
(MY_GRAND_PROJECT_SOURCE_DIR) and the directory from which you are
invoking cmake (MY_GRAND_PROJECT_BINARY_DIR).</p>
<div class="highlight-cmake notranslate"><div class="highlight"><pre><span></span><span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.3</span> <span class="s">REQUIRED</span> <span class="s">COMPONENTS</span> <span class="s">common</span> <span class="s">io</span><span class="p">)</span>
</pre></div>
</div>
<p>We are requesting to find the PCL package at minimum version 1.3. We
also says that it is <code class="docutils literal notranslate"><span class="pre">REQUIRED</span></code> meaning that cmake will fail
gracefully if it can’t be found. As PCL is modular one can request:</p>
<ul class="simple">
<li><p>only one component: find_package(PCL 1.3 REQUIRED COMPONENTS io)</p></li>
<li><p>several: find_package(PCL 1.3 REQUIRED COMPONENTS io common)</p></li>
<li><p>all existing: find_package(PCL 1.3 REQUIRED)</p></li>
</ul>
<div class="highlight-cmake notranslate"><div class="highlight"><pre><span></span><span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</div>
<p>When PCL is found, several related variables are set:</p>
<ul class="simple">
<li><p><cite>PCL_FOUND</cite>: set to 1 if PCL is found, otherwise unset</p></li>
<li><p><cite>PCL_INCLUDE_DIRS</cite>: set to the paths to PCL installed headers and
the dependency headers</p></li>
<li><p><cite>PCL_LIBRARIES</cite>: set to the file names of the built and installed PCL libraries</p></li>
<li><p><cite>PCL_LIBRARY_DIRS</cite>: set to the paths to where PCL libraries and 3rd
party dependencies reside</p></li>
<li><p><cite>PCL_VERSION</cite>: the version of the found PCL</p></li>
<li><p><cite>PCL_COMPONENTS</cite>: lists all available components</p></li>
<li><p><cite>PCL_DEFINITIONS</cite>: lists the needed preprocessor definitions and compiler flags</p></li>
</ul>
<p>To let cmake know about external headers you include in your project,
one needs to use <code class="docutils literal notranslate"><span class="pre">include_directories()</span></code> macro. In our case
<code class="docutils literal notranslate"><span class="pre">PCL_INCLUDE_DIRS</span></code>, contains exactly what we need, thus we ask cmake
to search the paths it contains for a header potentially included.</p>
<div class="highlight-cmake notranslate"><div class="highlight"><pre><span></span><span class="nb">add_executable</span><span class="p">(</span><span class="s">pcd_write_test</span> <span class="s">pcd_write.cpp</span><span class="p">)</span>
</pre></div>
</div>
<p>Here, we tell cmake that we are trying to make an executable file
named <code class="docutils literal notranslate"><span class="pre">pcd_write_test</span></code> from one single source file
<code class="docutils literal notranslate"><span class="pre">pcd_write.cpp</span></code>. CMake will take care of the suffix (<code class="docutils literal notranslate"><span class="pre">.exe</span></code> on
Windows platform and blank on UNIX) and the permissions.</p>
<div class="highlight-cmake notranslate"><div class="highlight"><pre><span></span><span class="nb">target_link_libraries</span><span class="p">(</span><span class="s">pcd_write_test</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</div>
<p>The executable we are building makes call to PCL functions. So far, we
have only included the PCL headers so the compilers knows about the
methods we are calling. We need also to make the linker knows about
the libraries we are linking against. As said before the, PCL
found libraries are referred to using <code class="docutils literal notranslate"><span class="pre">PCL_LIBRARIES</span></code> variable, all
that remains is to trigger the link operation which we do calling
<code class="docutils literal notranslate"><span class="pre">target_link_libraries()</span></code> macro.
PCLConfig.cmake uses a CMake special feature named <cite>EXPORT</cite> which
allows for using others’ projects targets as if you built them
yourself. When you are using such targets they are called <cite>imported
targets</cite> and acts just like any other target.</p>
</div>
<div class="section" id="compiling-and-running-the-project">
<h1>Compiling and running the project</h1>
<div class="section" id="using-command-line-cmake">
<h2>Using command line CMake</h2>
<p>Make a directory called <code class="docutils literal notranslate"><span class="pre">build</span></code>, in which the compilation will be
done. Do:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>$ cd /PATH/TO/MY/GRAND/PROJECT
$ mkdir build
$ cd build
$ cmake ..
</pre></div>
</div>
<p>You will see something similar to:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="o">--</span> <span class="n">The</span> <span class="n">C</span> <span class="n">compiler</span> <span class="n">identification</span> <span class="ow">is</span> <span class="n">GNU</span>
<span class="o">--</span> <span class="n">The</span> <span class="n">CXX</span> <span class="n">compiler</span> <span class="n">identification</span> <span class="ow">is</span> <span class="n">GNU</span>
<span class="o">--</span> <span class="n">Check</span> <span class="k">for</span> <span class="n">working</span> <span class="n">C</span> <span class="n">compiler</span><span class="p">:</span> <span class="o">/</span><span class="n">usr</span><span class="o">/</span><span class="nb">bin</span><span class="o">/</span><span class="n">gcc</span>
<span class="o">--</span> <span class="n">Check</span> <span class="k">for</span> <span class="n">working</span> <span class="n">C</span> <span class="n">compiler</span><span class="p">:</span> <span class="o">/</span><span class="n">usr</span><span class="o">/</span><span class="nb">bin</span><span class="o">/</span><span class="n">gcc</span> <span class="o">--</span> <span class="n">works</span>
<span class="o">--</span> <span class="n">Detecting</span> <span class="n">C</span> <span class="n">compiler</span> <span class="n">ABI</span> <span class="n">info</span>
<span class="o">--</span> <span class="n">Detecting</span> <span class="n">C</span> <span class="n">compiler</span> <span class="n">ABI</span> <span class="n">info</span> <span class="o">-</span> <span class="n">done</span>
<span class="o">--</span> <span class="n">Check</span> <span class="k">for</span> <span class="n">working</span> <span class="n">CXX</span> <span class="n">compiler</span><span class="p">:</span> <span class="o">/</span><span class="n">usr</span><span class="o">/</span><span class="nb">bin</span><span class="o">/</span><span class="n">c</span><span class="o">++</span>
<span class="o">--</span> <span class="n">Check</span> <span class="k">for</span> <span class="n">working</span> <span class="n">CXX</span> <span class="n">compiler</span><span class="p">:</span> <span class="o">/</span><span class="n">usr</span><span class="o">/</span><span class="nb">bin</span><span class="o">/</span><span class="n">c</span><span class="o">++</span> <span class="o">--</span> <span class="n">works</span>
<span class="o">--</span> <span class="n">Detecting</span> <span class="n">CXX</span> <span class="n">compiler</span> <span class="n">ABI</span> <span class="n">info</span>
<span class="o">--</span> <span class="n">Detecting</span> <span class="n">CXX</span> <span class="n">compiler</span> <span class="n">ABI</span> <span class="n">info</span> <span class="o">-</span> <span class="n">done</span>
<span class="o">--</span> <span class="n">Found</span> <span class="n">PCL_IO</span><span class="p">:</span> <span class="o">/</span><span class="n">usr</span><span class="o">/</span><span class="n">local</span><span class="o">/</span><span class="n">lib</span><span class="o">/</span><span class="n">libpcl_io</span><span class="o">.</span><span class="n">so</span>
<span class="o">--</span> <span class="n">Found</span> <span class="n">PCL</span><span class="p">:</span> <span class="o">/</span><span class="n">usr</span><span class="o">/</span><span class="n">local</span><span class="o">/</span><span class="n">lib</span><span class="o">/</span><span class="n">libpcl_io</span><span class="o">.</span><span class="n">so</span> <span class="p">(</span><span class="n">Required</span> <span class="ow">is</span> <span class="n">at</span> <span class="n">least</span> <span class="n">version</span> <span class="s2">&quot;1.0&quot;</span><span class="p">)</span>
<span class="o">--</span> <span class="n">Configuring</span> <span class="n">done</span>
<span class="o">--</span> <span class="n">Generating</span> <span class="n">done</span>
<span class="o">--</span> <span class="n">Build</span> <span class="n">files</span> <span class="n">have</span> <span class="n">been</span> <span class="n">written</span> <span class="n">to</span><span class="p">:</span> <span class="o">/</span><span class="n">PATH</span><span class="o">/</span><span class="n">TO</span><span class="o">/</span><span class="n">MY</span><span class="o">/</span><span class="n">GRAND</span><span class="o">/</span><span class="n">PROJECT</span><span class="o">/</span><span class="n">build</span>
</pre></div>
</div>
<p>If you want to see what is written on the CMake cache:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">CMAKE_BUILD_TYPE</span>
<span class="n">CMAKE_INSTALL_PREFIX</span>             <span class="o">/</span><span class="n">usr</span><span class="o">/</span><span class="n">local</span>
<span class="n">PCL_DIR</span>                          <span class="o">/</span><span class="n">usr</span><span class="o">/</span><span class="n">local</span><span class="o">/</span><span class="n">share</span><span class="o">/</span><span class="n">pcl</span>
</pre></div>
</div>
<p>Now, we can build up our project, simply typing:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>$ make
</pre></div>
</div>
<p>The result should be as follow:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">Scanning</span> <span class="n">dependencies</span> <span class="n">of</span> <span class="n">target</span> <span class="n">pcd_write_test</span>
<span class="p">[</span><span class="mi">100</span><span class="o">%</span><span class="p">]</span> <span class="n">Building</span> <span class="n">CXX</span> <span class="nb">object</span>
<span class="n">CMakeFiles</span><span class="o">/</span><span class="n">pcd_write_test</span><span class="o">.</span><span class="n">dir</span><span class="o">/</span><span class="n">pcd_write</span><span class="o">.</span><span class="n">cpp</span><span class="o">.</span><span class="n">o</span>
<span class="n">Linking</span> <span class="n">CXX</span> <span class="n">executable</span> <span class="n">pcd_write_test</span>
<span class="p">[</span><span class="mi">100</span><span class="o">%</span><span class="p">]</span> <span class="n">Built</span> <span class="n">target</span> <span class="n">pcd_write_test</span>
</pre></div>
</div>
<p>The project is now compiled, linked and ready to test:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>$ ./pcd_write_test
</pre></div>
</div>
<p>Which leads to this:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">Saved</span> <span class="mi">5</span> <span class="n">data</span> <span class="n">points</span> <span class="n">to</span> <span class="n">test_pcd</span><span class="o">.</span><span class="n">pcd</span><span class="o">.</span>
  <span class="mf">0.352222</span> <span class="o">-</span><span class="mf">0.151883</span> <span class="o">-</span><span class="mf">0.106395</span>
  <span class="o">-</span><span class="mf">0.397406</span> <span class="o">-</span><span class="mf">0.473106</span> <span class="mf">0.292602</span>
  <span class="o">-</span><span class="mf">0.731898</span> <span class="mf">0.667105</span> <span class="mf">0.441304</span>
  <span class="o">-</span><span class="mf">0.734766</span> <span class="mf">0.854581</span> <span class="o">-</span><span class="mf">0.0361733</span>
  <span class="o">-</span><span class="mf">0.4607</span> <span class="o">-</span><span class="mf">0.277468</span> <span class="o">-</span><span class="mf">0.916762</span>
</pre></div>
</div>
</div>
<div class="section" id="using-cmake-gui-e-g-windows">
<h2>Using CMake gui (e.g. Windows)</h2>
<p>Run CMake GUI, and fill these fields :</p>
<blockquote>
<div><ul class="simple">
<li><p><code class="docutils literal notranslate"><span class="pre">Where</span> <span class="pre">is</span> <span class="pre">the</span> <span class="pre">source</span> <span class="pre">code</span></code> : this is the folder containing the CMakeLists.txt file and the sources.</p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">Where</span> <span class="pre">to</span> <span class="pre">build</span> <span class="pre">the</span> <span class="pre">binaries</span></code> : this is where the Visual Studio project files will be generated</p></li>
</ul>
</div></blockquote>
<p>Then, click <code class="docutils literal notranslate"><span class="pre">Configure</span></code>. You will be prompted for a generator/compiler. Then click the <code class="docutils literal notranslate"><span class="pre">Generate</span></code>
button. If there is no errors, the project files will be generated into the <code class="docutils literal notranslate"><span class="pre">Where</span> <span class="pre">to</span> <span class="pre">build</span> <span class="pre">the</span> <span class="pre">binaries</span></code>
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
<div class="highlight-cmake notranslate"><div class="highlight"><pre><span></span><span class="nb">set</span><span class="p">(</span><span class="s">PCL_DIR</span> <span class="s2">&quot;/path/to/PCLConfig.cmake&quot;</span><span class="p">)</span>
</pre></div>
</div>
<p>before this one:</p>
<div class="highlight-cmake notranslate"><div class="highlight"><pre><span></span>find_package(PCL 1.3 REQUIRED COMPONENTS common io)
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