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
    
    <title>Using DistCC to speed up compilation</title>
    
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
    <link rel="next" title="Compiler optimizations" href="compiler_optimizations.php" />
    <link rel="prev" title="Using CCache to speed up compilation" href="c_cache.php" />
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
            
  <div class="section" id="using-distcc-to-speed-up-compilation">
<span id="distc"></span><h1>Using DistCC to speed up compilation</h1>
<p><a class="reference external" href="http://distcc.org/">distcc</a> is a program to distribute builds of C, C++,
Objective C or Objective C++ code across several machines on a network.
<cite>distcc</cite> should always generate the same results as a local build, is simple to
install and use, and is usually much faster than a local compile.</p>
<p><cite>distcc</cite> does not require all machines to share a filesystem, have synchronized
clocks, or to have the same libraries or header files installed. They can even
have different processors or operating systems, if cross-compilers are
installed.</p>
<p><cite>distcc</cite> is usually very easy to install &#8211; just follow the installation
instructions on its web page. Here&#8217;s an example for Ubuntu systems:</p>
<div class="highlight-python"><div class="highlight"><pre>sudo apt-get install distcc
</pre></div>
</div>
<p>In each distributed build environment, there are usually two different roles:</p>
<blockquote>
<div><ul>
<li><p class="first"><strong>server</strong></p>
<p>Here, we call the <em>server</em>, the actual workstation/computer that is running
a <cite>distcc</cite> daemon, and will perform the compilation. To run a <cite>distcc</cite>
daemon on an Ubuntu system for example, you need to start the daemon,
usually with something along the lines of:</p>
<div class="highlight-python"><div class="highlight"><pre>/etc/init.d/distcc start
</pre></div>
</div>
<p>Once started, you should notice a few <cite>distcc</cite> processes idle-ing:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ps axw | grep distcc
...
30042 ?        SN     0:00 /usr/bin/distccd --pid-file=/var/run/distccd.pid --log-file=/var/log/distccd.log --daemon --allow 127.0.0.1 --allow 10.0.0.0/21 --listen 0.0.0.0 --nice 10 --zeroconf
30043 ?        SN     0:00 /usr/bin/distccd --pid-file=/var/run/distccd.pid --log-file=/var/log/distccd.log --daemon --allow 127.0.0.1 --allow 10.0.0.0/21 --listen 0.0.0.0 --nice 10 --zeroconf
30044 ?        SN     0:00 /usr/bin/distccd --pid-file=/var/run/distccd.pid --log-file=/var/log/distccd.log --daemon --allow 127.0.0.1 --allow 10.0.0.0/21 --listen 0.0.0.0 --nice 10 --zeroconf
</pre></div>
</div>
<p>Let&#8217;s assume for the sake of this example, that we have two machines,
<em>wgsc11</em> and <em>wgsc12</em>, with <cite>distcc</cite> installed and running as a server
daemon. These are the machines that we would like use to speed up the
compilation of the PCL source tree.</p>
</li>
<li><p class="first"><strong>client</strong></p>
<p>Here by client we refer to the workstation/computer that contains the source
code to be compiled, in our case, where the PCL source code tree resides.</p>
<p>The first thing that we need to do is tell <cite>cmake</cite> to use <cite>distcc</cite> instead
of the default compiler. The easiest way to do this is to invoke <cite>cmake</cite>
with pre-flags, like:</p>
<div class="highlight-python"><div class="highlight"><pre>[pcl] $ mkdir build &amp;&amp; cd build
[pcl/build] $ CC=&quot;distcc gcc&quot; CXX=&quot;distcc g++&quot; cmake ..
</pre></div>
</div>
<p>Sometimes compiling on systems supporting different SSE extensions will lead
to problems. Setting PCL_ENABLE_SSE to false will solve this, like:</p>
<div class="highlight-python"><div class="highlight"><pre>[pcl/build] $ CC=&quot;distcc gcc&quot; CXX=&quot;distcc g++&quot; cmake -DPCL_ENABLE_SSE:BOOL=FALSE ../pcl
</pre></div>
</div>
<p>The output of <tt class="docutils literal"><span class="pre">CC=&quot;distcc</span> <span class="pre">gcc&quot;</span> <span class="pre">CXX=&quot;distcc</span> <span class="pre">g++&quot;</span> <span class="pre">cmake</span> <span class="pre">..</span></tt> will generate
something like this. Please note that this is just an example and that the
messages might vary depending on your operating system and the way your
library dependencies were compiled/installed:</p>
<div class="highlight-bash"><div class="highlight"><pre>-- The C compiler identification is GNU
-- The CXX compiler identification is GNU
-- Check <span class="k">for </span>working C compiler: /usr/bin/distcc
-- Check <span class="k">for </span>working C compiler: /usr/bin/distcc -- works
-- Detecting C compiler ABI info
-- Detecting C compiler ABI info - <span class="k">done</span>
-- Check <span class="k">for </span>working CXX compiler: /usr/bin/distcc
-- Check <span class="k">for </span>working CXX compiler: /usr/bin/distcc -- works
-- Detecting CXX compiler ABI info
-- Detecting CXX compiler ABI info - <span class="k">done</span>
-- Performing Test HAVE_SSE3_EXTENSIONS
-- Performing Test HAVE_SSE3_EXTENSIONS - Success
-- Performing Test HAVE_SSE2_EXTENSIONS
-- Performing Test HAVE_SSE2_EXTENSIONS - Success
-- Performing Test HAVE_SSE_EXTENSIONS
-- Performing Test HAVE_SSE_EXTENSIONS - Success
-- Found SSE3 extensions, using flags: -msse3 -mfpmath<span class="o">=</span>sse
-- Boost version: 1.42.0
-- Found the following Boost libraries:
--   system
--   filesystem
--   thread
--   date_time
--   iostreams
-- checking <span class="k">for </span>module <span class="s1">&#39;eigen3&#39;</span>
--   found eigen3, version 3.0.0
-- Found Eigen: /usr/include/eigen3
-- Eigen found <span class="o">(</span>include: /usr/include/eigen3<span class="o">)</span>
-- checking <span class="k">for </span>module <span class="s1">&#39;flann&#39;</span>
--   found flann, version 1.6.8
-- Found Flann: /usr/lib64/libflann_cpp_s.a
-- FLANN found <span class="o">(</span>include: /usr/include, lib: optimized;/usr/lib64/libflann_cpp_s.a;debug;/usr/lib64/libflann_cpp.so<span class="o">)</span>
-- checking <span class="k">for </span>module <span class="s1">&#39;cminpack&#39;</span>
--   found cminpack, version 1.0.90
-- Found CMinpack: /usr/lib64/libcminpack.so
-- CMinPack found <span class="o">(</span>include: /usr/include/cminpack-1, libs: optimized;/usr/lib64/libcminpack.so;debug;/usr/lib64/libcminpack.so<span class="o">)</span>
-- Try OpenMP C <span class="nv">flag</span> <span class="o">=</span> <span class="o">[</span>-fopenmp<span class="o">]</span>
-- Performing Test OpenMP_FLAG_DETECTED
-- Performing Test OpenMP_FLAG_DETECTED - Success
-- Try OpenMP CXX <span class="nv">flag</span> <span class="o">=</span> <span class="o">[</span>-fopenmp<span class="o">]</span>
-- Performing Test OpenMP_FLAG_DETECTED
-- Performing Test OpenMP_FLAG_DETECTED - Success
-- Found OpenMP: -fopenmp
-- Found OpenNI: /usr/lib/libOpenNI.so
-- OpenNI found <span class="o">(</span>include: /usr/include/openni, lib: /usr/lib/libOpenNI.so<span class="o">)</span>
-- ROS_ROOT /opt/ros/diamondback/ros
-- Found ROS; USE_ROS is OFF
-- Found GTest: /usr/lib/libgtest.so
-- Tests will be built
-- Found Qhull: /usr/lib/libqhull.so
-- QHULL found <span class="o">(</span>include: /usr/include/qhull, lib: optimized;/usr/lib/libqhull.so;debug;/usr/lib/libqhull.so<span class="o">)</span>
-- VTK found <span class="o">(</span>include: /usr/include/vtk-5.4;/usr/include;/usr/include;/usr/include;/usr/include;/usr/include;/usr/include;/usr/include;/usr/include;/usr/include;/usr/include;/usr/include;/usr/include;/usr/include;/usr/include;/usr/include;/usr/include;/usr/include;/usr/include;/usr/include;/usr/lib/openmpi/include;/usr/lib/openmpi/include/openmpi;/usr/include/tcl8.5;/usr/include/python2.6;/usr/include/tcl8.5;/usr/lib/jvm/default-java/include;/usr/lib/jvm/default-java/include;/usr/lib/jvm/default-java/include;/usr/include;/usr/include;/usr/include;/usr/include;/usr/include;/usr/include/libxml2;/usr/include;/usr/include/freetype2, lib: /usr/lib/vtk-5.4<span class="o">)</span>
-- Found Doxygen: /usr/bin/doxygen
-- Found CPack generators: DEB
-- The following subsystems will be built:
--   common
--   octree
--   io
--   kdtree
--   range_image
--   features
--   sample_consensus
--   keypoints
--   filters
--   registration
--   segmentation
--   surface
--   visualization
--   global_tests
-- The following subsystems will not be built:
-- Configuring <span class="k">done</span>
-- Generating <span class="k">done</span>
-- Build files have been written to: /work/PCL/pcl/trunk/build
</pre></div>
</div>
</li>
</ul>
<blockquote>
<div><p>The important lines are:</p>
<div class="highlight-python"><div class="highlight"><pre>-- Check for working C compiler: /usr/bin/distcc
-- Check for working C compiler: /usr/bin/distcc -- works
-- Detecting C compiler ABI info
-- Detecting C compiler ABI info - done
-- Check for working CXX compiler: /usr/bin/distcc
-- Check for working CXX compiler: /usr/bin/distcc -- works
</pre></div>
</div>
<p>The next step is to tell <cite>distcc</cite> which hosts it should use. Here we can
decide whether we want to use the local workstation for compilation too, or
just the machines running a <cite>distcc</cite> daemon (<em>wgsc11</em> and <em>wgsc12</em> in our
example). The easiest way to pass this information to <cite>distcc</cite> is via
environment variables. For example:</p>
<div class="highlight-python"><div class="highlight"><pre>export DISTCC_HOSTS=&#39;localhost wgsc11 wgsc12&#39;
</pre></div>
</div>
<p>will tell <cite>distcc</cite> to use the local machine, as well as both the <cite>distcc</cite>
servers, while:</p>
<div class="highlight-python"><div class="highlight"><pre>export DISTCC_HOSTS=&#39;wgsc11 wgsc12&#39;
</pre></div>
</div>
<p>will only use the <em>wgsc11</em> and <em>wgsc12</em> machines.</p>
<p>Finally, the last step is to increase the number of parallel compile units we should use. For example:</p>
<div class="highlight-python"><div class="highlight"><pre>[pcl/build] $ make -j32
</pre></div>
</div>
<p>will start <strong>32 processes</strong> and distribute them equally on the two <cite>distcc</cite> machines.</p>
</div></blockquote>
</div></blockquote>
<p>The following plot shows an example of multiple <tt class="docutils literal"><span class="pre">make</span> <span class="pre">-jX</span></tt> invocations, for X
ranging from 1 to 13. As it can be seen, the overall compile time is
drastically reduced by using <cite>distcc</cite>, in this case with the CPU on the client
machine almost idleing while the <em>wgsc11</em> and <em>wgsc12</em> machines do most of the
work. The reason why the plot &#8220;saturates&#8221; is due to conditional dependencies in
the compilation process, where certain libraries or binaries require others to
be compiled first.</p>
<img alt="_images/distcc_plot.png" src="_images/distcc_plot.png" />
<p>For more information on how to configure <cite>distcc</cite> please visit <a class="reference external" href="http://distcc.org">http://distcc.org</a>.</p>
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