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
    
    <title>The Velodyne High Definition LiDAR (HDL) Grabber</title>
    
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
            
  <div class="section" id="the-velodyne-high-definition-lidar-hdl-grabber">
<span id="hdl-grabber"></span><h1>The Velodyne High Definition LiDAR (HDL) Grabber</h1>
<p>The Velodyne HDL is a network-based 3D LiDAR system that produces
360 degree point clouds containing over 700,000 points every second.</p>
<p>The HDL Grabber provided in PCL mimicks other Grabbers, making it <em>almost</em>
plug-and-play.  Because the HDL devices are network based, however, there
are a few gotchas on some platforms.</p>
<p>The HDL Grabber supports the original HDL-64e as well as the HDL-32e.
More information on those sensors can be found at <a class="reference external" href="http://velodynelidar.com/lidar/lidar.aspx">Velodyne&#8217;s Web Site</a></p>
</div>
<div class="section" id="basic-network-setup">
<h1>Basic Network Setup</h1>
<p>The Velodyne HDL uses network packets to provide range and intensity
data for each of the lasers in the device.  The HDL-64e consists of
64 lasers, while the HDL-32e consists of 32.</p>
<p>The HDL-64e and HDL-32e, by default, produce UDP network packets
on the 192.168.3 subnet.  Starting with the HDL-32e (Firmware Version 2),
the user can customize this network subnet.</p>
<p>The HDL can be connected either directly into your computer, or into a
network switch (to include a network switch with a built-in Wireless Access Point).
Regardless, one of your computer&#8217;s Network Interface Cards (NIC) [whether hard-wired
RJ-45 connection or wireless] needs to be configured to be on this 192.168.3 subnet.
Consult your operating system documentation on how to perform this.</p>
<p>In addition to the NIC settings, you may need to alter your operating system&#8217;s firewall rules.  The
HDL produces packets on port 2368 (by default).  The HDL-32e with Firmware Version 2
can be set to use a different port.  Consult your firewall documentation to open
this port in your firewall.</p>
<p>Lastly, modern Linux kernels have advanced network attack guards that go beyond basic firewall
rules.  The HDL-32e produces UDP packets that may be filtered by the OS using one of these
attack guards.  You will need to disable the <em>rp_filter</em> guard for the appropriate NIC.
For more information on how to disable this filter, please see the section below entitled
<a class="reference internal" href="#disabling-reverse-path-filter">Disabling Reverse Path Filter</a></p>
</div>
<div class="section" id="pcap-files">
<h1>PCAP Files</h1>
<p><a class="reference external" href="http://www.wireshark.org/">Wireshark</a> is a popular Network Packet Analyzer Program which
is available for most platforms, including Linux, MacOS and Windows.  This tool uses a defacto
standard network packet capture file format called <a class="reference external" href="http://en.wikipedia.org/wiki/Pcap">PCAP</a>.
Many publically available Velodyne HDL packet captures use this PCAP file format as a means of
recording and playback.  These PCAP files can be used with the HDL Grabber if PCL is compiled with
PCAP support.</p>
<p>Velodyne provides sample PCAP files on their <a class="reference external" href="http://velodyne.com/lidar/doc/Sample%20sets/HDL-32/">website</a></p>
</div>
<div class="section" id="compiling-the-hdl-grabber-with-pcap-support">
<h1>Compiling the HDL Grabber with PCAP support</h1>
<p>On Linux, this involves installing libpcap-dev (Ubuntu) or libpcap-devel (Fedora).  CMake should
find the pcap libraries, and automatically configure PCL to use them.</p>
<p>On Windows, this involves installing both the <a class="reference external" href="http://www.winpcap.org/install/default.htm">WinPCAP installer</a>
and the <a class="reference external" href="http://www.winpcap.org/devel.htm">WinPCAP developer&#8217;s pack</a>.  You will also need to set an
environment variable <strong>PCAPDIR</strong> to the directory where you unzipped the developer&#8217;s pack.  Once that is
done, you should be able to run CMake again, and it should locate the appropriate files.</p>
<p>Note - You do not need to compile the HDL Grabber with support for PCAP.  It is <strong>only</strong> required if
you will be replaying PCAP files through the grabber.</p>
</div>
<div class="section" id="sample-program">
<h1>Sample Program</h1>
<p>In <em>visualization</em>, there is a very short piece of code which contains all that
is required to set up a <em>pcl::PointCloud&lt;XYZ&gt;</em>, <em>pcl::PointCloud&lt;XYZI&gt; or *pcl::PointCloud&lt;XYZRGB&gt;</em>
cloud callback.</p>
<p>Here is a screenshot of the PCL HDL Viewer in action, which uses the HDL Grabber.</p>
<a class="reference external image-reference" href="_images/pcl_hdl_viewer.png"><img alt="_images/pcl_hdl_viewer.png" src="_images/pcl_hdl_viewer.png" style="height: 390px;" /></a>
<p>So let&#8217;s look at the code. The following represents a simplified version of <em>visualization/tools/hdl_viewer_simple.cpp</em></p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre>  1
  2
  3
  4
  5
  6
  7
  8
  9
 10
 11
 12
 13
 14
 15
 16
 17
 18
 19
 20
 21
 22
 23
 24
 25
 26
 27
 28
 29
 30
 31
 32
 33
 34
 35
 36
 37
 38
 39
 40
 41
 42
 43
 44
 45
 46
 47
 48
 49
 50
 51
 52
 53
 54
 55
 56
 57
 58
 59
 60
 61
 62
 63
 64
 65
 66
 67
 68
 69
 70
 71
 72
 73
 74
 75
 76
 77
 78
 79
 80
 81
 82
 83
 84
 85
 86
 87
 88
 89
 90
 91
 92
 93
 94
 95
 96
 97
 98
 99
100
101</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;pcl/point_cloud.h&gt;</span>
<span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/io/hdl_grabber.h&gt;</span>
<span class="cp">#include &lt;pcl/visualization/point_cloud_color_handlers.h&gt;</span>
<span class="cp">#include &lt;pcl/visualization/cloud_viewer.h&gt;</span>
<span class="cp">#include &lt;pcl/console/parse.h&gt;</span>

<span class="k">using</span> <span class="k">namespace</span> <span class="n">std</span><span class="p">;</span>
<span class="k">using</span> <span class="k">namespace</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="p">;</span>
<span class="k">using</span> <span class="k">namespace</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="p">;</span>

<span class="k">class</span> <span class="nc">SimpleHDLViewer</span>
<span class="p">{</span>
  <span class="nl">public:</span>
    <span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZI</span><span class="o">&gt;</span> <span class="n">Cloud</span><span class="p">;</span>
    <span class="k">typedef</span> <span class="k">typename</span> <span class="n">Cloud</span><span class="o">::</span><span class="n">ConstPtr</span> <span class="n">CloudConstPtr</span><span class="p">;</span>

    <span class="n">SimpleHDLViewer</span> <span class="p">(</span><span class="n">Grabber</span><span class="o">&amp;</span> <span class="n">grabber</span><span class="p">,</span>
        <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandler</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZI</span><span class="o">&gt;</span> <span class="o">&amp;</span><span class="n">handler</span><span class="p">)</span> <span class="o">:</span>
        <span class="n">cloud_viewer_</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="p">(</span><span class="s">&quot;PCL HDL Cloud&quot;</span><span class="p">)),</span>
        <span class="n">grabber_</span> <span class="p">(</span><span class="n">grabber</span><span class="p">),</span>
        <span class="n">handler_</span> <span class="p">(</span><span class="n">handler</span><span class="p">)</span>
    <span class="p">{</span>
    <span class="p">}</span>

    <span class="kt">void</span> <span class="n">cloud_callback</span> <span class="p">(</span><span class="k">const</span> <span class="n">CloudConstPtr</span><span class="o">&amp;</span> <span class="n">cloud</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">boost</span><span class="o">::</span><span class="n">mutex</span><span class="o">::</span><span class="n">scoped_lock</span> <span class="n">lock</span> <span class="p">(</span><span class="n">cloud_mutex_</span><span class="p">);</span>
      <span class="n">cloud_</span> <span class="o">=</span> <span class="n">cloud</span><span class="p">;</span>
    <span class="p">}</span>

    <span class="kt">void</span> <span class="n">run</span> <span class="p">()</span>
    <span class="p">{</span>
      <span class="n">cloud_viewer_</span><span class="o">-&gt;</span><span class="n">addCoordinateSystem</span> <span class="p">(</span><span class="mf">3.0</span><span class="p">);</span>
      <span class="n">cloud_viewer_</span><span class="o">-&gt;</span><span class="n">setBackgroundColor</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
      <span class="n">cloud_viewer_</span><span class="o">-&gt;</span><span class="n">initCameraParameters</span> <span class="p">();</span>
      <span class="n">cloud_viewer_</span><span class="o">-&gt;</span><span class="n">setCameraPosition</span> <span class="p">(</span><span class="mf">0.0</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mf">30.0</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
      <span class="n">cloud_viewer_</span><span class="o">-&gt;</span><span class="n">setCameraClipDistances</span> <span class="p">(</span><span class="mf">0.0</span><span class="p">,</span> <span class="mf">50.0</span><span class="p">);</span>

      <span class="n">boost</span><span class="o">::</span><span class="n">function</span><span class="o">&lt;</span><span class="kt">void</span> <span class="p">(</span><span class="k">const</span> <span class="n">CloudConstPtr</span><span class="o">&amp;</span><span class="p">)</span><span class="o">&gt;</span> <span class="n">cloud_cb</span> <span class="o">=</span> <span class="n">boost</span><span class="o">::</span><span class="n">bind</span> <span class="p">(</span>
          <span class="o">&amp;</span><span class="n">SimpleHDLViewer</span><span class="o">::</span><span class="n">cloud_callback</span><span class="p">,</span> <span class="k">this</span><span class="p">,</span> <span class="n">_1</span><span class="p">);</span>
      <span class="n">boost</span><span class="o">::</span><span class="n">signals2</span><span class="o">::</span><span class="n">connection</span> <span class="n">cloud_connection</span> <span class="o">=</span> <span class="n">grabber_</span><span class="p">.</span><span class="n">registerCallback</span> <span class="p">(</span>
          <span class="n">cloud_cb</span><span class="p">);</span>

      <span class="n">grabber_</span><span class="p">.</span><span class="n">start</span> <span class="p">();</span>

      <span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">cloud_viewer_</span><span class="o">-&gt;</span><span class="n">wasStopped</span> <span class="p">())</span>
      <span class="p">{</span>
        <span class="n">CloudConstPtr</span> <span class="n">cloud</span><span class="p">;</span>

        <span class="c1">// See if we can get a cloud</span>
        <span class="k">if</span> <span class="p">(</span><span class="n">cloud_mutex_</span><span class="p">.</span><span class="n">try_lock</span> <span class="p">())</span>
        <span class="p">{</span>
          <span class="n">cloud_</span><span class="p">.</span><span class="n">swap</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
          <span class="n">cloud_mutex_</span><span class="p">.</span><span class="n">unlock</span> <span class="p">();</span>
        <span class="p">}</span>

        <span class="k">if</span> <span class="p">(</span><span class="n">cloud</span><span class="p">)</span>
        <span class="p">{</span>
          <span class="n">handler_</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
          <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">cloud_viewer_</span><span class="o">-&gt;</span><span class="n">updatePointCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">handler_</span><span class="p">,</span> <span class="s">&quot;HDL&quot;</span><span class="p">))</span>
            <span class="n">cloud_viewer_</span><span class="o">-&gt;</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">handler_</span><span class="p">,</span> <span class="s">&quot;HDL&quot;</span><span class="p">);</span>

          <span class="n">cloud_viewer_</span><span class="o">-&gt;</span><span class="n">spinOnce</span> <span class="p">();</span>
        <span class="p">}</span>

        <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">grabber_</span><span class="p">.</span><span class="n">isRunning</span> <span class="p">())</span>
          <span class="n">cloud_viewer_</span><span class="o">-&gt;</span><span class="n">spin</span> <span class="p">();</span>

        <span class="n">boost</span><span class="o">::</span><span class="n">this_thread</span><span class="o">::</span><span class="n">sleep</span> <span class="p">(</span><span class="n">boost</span><span class="o">::</span><span class="n">posix_time</span><span class="o">::</span><span class="n">microseconds</span> <span class="p">(</span><span class="mi">100</span><span class="p">));</span>
      <span class="p">}</span>

      <span class="n">grabber_</span><span class="p">.</span><span class="n">stop</span> <span class="p">();</span>

      <span class="n">cloud_connection</span><span class="p">.</span><span class="n">disconnect</span> <span class="p">();</span>
    <span class="p">}</span>

    <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">cloud_viewer_</span><span class="p">;</span>

    <span class="n">pcl</span><span class="o">::</span><span class="n">Grabber</span><span class="o">&amp;</span> <span class="n">grabber_</span><span class="p">;</span>
    <span class="n">boost</span><span class="o">::</span><span class="n">mutex</span> <span class="n">cloud_mutex_</span><span class="p">;</span>

    <span class="n">CloudConstPtr</span> <span class="n">cloud_</span><span class="p">;</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandler</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZI</span><span class="o">&gt;</span> <span class="o">&amp;</span><span class="n">handler_</span><span class="p">;</span>
<span class="p">};</span>

<span class="kt">int</span> <span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span> <span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">hdlCalibration</span><span class="p">,</span> <span class="n">pcapFile</span><span class="p">;</span>

  <span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-calibrationFile&quot;</span><span class="p">,</span> <span class="n">hdlCalibration</span><span class="p">);</span>
  <span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-pcapFile&quot;</span><span class="p">,</span> <span class="n">pcapFile</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">HDLGrabber</span> <span class="n">grabber</span> <span class="p">(</span><span class="n">hdlCalibration</span><span class="p">,</span> <span class="n">pcapFile</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerGenericField</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZI</span><span class="o">&gt;</span> <span class="n">color_handler</span> <span class="p">(</span><span class="s">&quot;intensity&quot;</span><span class="p">);</span>

  <span class="n">SimpleHDLViewer</span><span class="o">&lt;</span><span class="n">PointXYZI</span><span class="o">&gt;</span> <span class="n">v</span> <span class="p">(</span><span class="n">grabber</span><span class="p">,</span> <span class="n">color_handler</span><span class="p">);</span>
  <span class="n">v</span><span class="p">.</span><span class="n">run</span> <span class="p">();</span>
  <span class="k">return</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="additional-details">
<h1>Additional Details</h1>
<p>The <em>HDL Grabber</em> offers more than one datatype, which is the reason we made
the <em>Grabber</em> interface so generic, leading to the relatively complicated
<em>boost::bind</em> line. In fact, we can register the following callback types as of
this writing:</p>
<ul class="simple">
<li><cite>void (const boost::shared_ptr&lt;const pcl::PointCloud&lt;pcl::PointXYZRGB&gt; &gt;&amp;)</cite></li>
</ul>
</div>
<div class="section" id="compiling-and-running-the-program">
<h1>Compiling and running the program</h1>
<p>Add the following lines to your CMakeLists.txt file:</p>
<div class="highlight-cmake"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
 2
 3
 4
 5
 6
 7
 8
 9
10
11
12</pre></div></td><td class="code"><div class="highlight"><pre><span class="nb">cmake_minimum_required</span><span class="p">(</span><span class="s">VERSION</span> <span class="s">2.8</span> <span class="s">FATAL_ERROR</span><span class="p">)</span>

<span class="nb">project</span><span class="p">(</span><span class="s">openni_grabber</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.2</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">openni_grabber</span> <span class="s">openni_grabber.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">openni_grabber</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="id1">
<h1><span class="target" id="disabling-reverse-path-filter">Disabling Reverse Path Filter</span></h1>
<p>First off, let&#8217;s understand what the <strong>Reverse Path Filter</strong> is all about.  A TCP or UDP packet
contains, amongst other information, a <strong>DESTINATION IP ADDRESS</strong> and a <strong>SOURCE IP ADDRESS</strong>.
The Destination IP Address represents where the packet will go.  In the case of a UDP packet, this
is usually the broadcast network IP Address (eg, 255.255.255.255 for a global broadcast, or
x.y.z.255 for a Class C Network [where x.y.z are the first three octets of a Class C network, such as
192.168.1]).</p>
<p>The Source IP Address, on the otherhand, indicates where the packet originated from.  Packets
can be hand-crafted for spoofing-type attacks (eg, pretending to come from somewhere they really
didn&#8217;t). The Reverse Path Filter attempts to detect these instances.  The default rule that it uses is
that if a packet is received on Network Interface <em>A</em>, then if there is no <strong>route</strong> to the <strong>Source IP Address</strong>
on Network Interface <em>A</em>, then it will be dropped by the kernel.</p>
<p>So, what does this mean for the HDL-32e?  Well, by default, the Source IP Address is 192.168.X.Y, where
<strong>X</strong> and <strong>Y</strong> are the last 4 digits of the device&#8217;s serial number.  Remember, the default <em>Destination</em> IP
address for the HDL-32e is the 192.168.3 network.</p>
<p>If you have a single Network Interface, you will have a default route (that is a route to all other networks)
going out that single Network Interface.  To receive the HDL packets, that Network Interface will need to be
on the 192.168.3 subnet.  And all will be good because there is a route from your single Network Interface to
the <strong>packet&#8217;s Source IP Address</strong>, through your single Network Interface.</p>
<p>Ahh, but what happens when you have <em>two</em> Network Interfaces, for example, on to the <strong>internet</strong>, and one
dedicated to the HDL?  In that case, your primary NIC will have a default route to all other networks, but
the one that is dedicated to the HDL won&#8217;t.  By default, it won&#8217;t have a default route, and in fact,
it will only have a route to the 192.168.3 subnet.</p>
<p>That means that when the HDL packet is received by the Linux Kernel, it will determine that there is no route
from the secondary NIC back to the HDL packet Source IP Address, and drop the packet altogether.</p>
<p>The maddening thing about this is that if you were to run tcpdump or wireshark (two network packet sniffer programs),
you would see that the HDL packets were arriving at the NIC card!  The reason for this is that programs like
tcpdump and wireshark use something called <em>promiscuous mode</em> that allows them to receive all packets <strong>BEFORE</strong>
the Linux Kernel does.</p>
<p>So, there are a couple of solutions to this problem.  First, you could use a single NIC, and your computer will
be dedicated to the HDL.  You won&#8217;t have to do anything except change network IP addresses when you want to
connect to an alternate network.  For those that desire a second NIC, there are several options.  First, you
can set up a route back to the source network that traverses the second NIC.  Note, the Linux Kernel does not
actually try to connect back to the source network, it just ensures that there is a path to it.  This option
works well in practice.  The other option is to modify the RP Filter setting.  There are two possible modes -
turn it off completely, or relax the rules to see if there is a route back to that network via <em>any</em>
NIC on the computer.</p>
<p>Here are the options again for a multi-NIC system, with corresponding Linux Commands.</p>
<ol class="arabic simple">
<li>Add a route back to the HDL</li>
</ol>
<p>First off, let&#8217;s look at the interface settings for our two NICS:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ifconfig
</pre></div>
</div>
<p>returns the following details (some items removed for brevity):</p>
<div class="highlight-python"><div class="highlight"><pre>em1: flags=4163&lt;UP,BROADCAST,RUNNING,MULTICAST&gt;  mtu 1500
     inet 192.168.128.108  netmask 255.255.255.0  broadcast 192.168.128.255

eth0: flags=4163&lt;UP,BROADCAST,RUNNING,MULTICAST&gt;  mtu 1500
     inet 192.168.3.1  netmask 255.255.255.0  broadcast 192.168.3.255
</pre></div>
</div>
<p>Next, let&#8217;s look at our routing table (again, some items removed for brevity):</p>
<div class="highlight-python"><div class="highlight"><pre>$ route -n

Kernel IP routing table
Destination     Gateway         Genmask         Flags Metric Ref    Use Iface
0.0.0.0         192.168.128.1   0.0.0.0         UG    0      0        0 em1
192.168.3.0     0.0.0.0         255.255.255.0   U     0      0        0 eth0
192.168.128.0   0.0.0.0         255.255.255.0   U     0      0        0 em1
</pre></div>
</div>
<p>To add a route to the HDL, assume that the HDL Source IP is 192.168.12.84.  You would use the
following command:</p>
<div class="highlight-python"><div class="highlight"><pre>$ sudo route add -net 192.168.12.0/24 eth0
</pre></div>
</div>
<p>To verify that the route has been added, type the following:</p>
<div class="highlight-python"><div class="highlight"><pre>$ route -n

Kernel IP routing table
Destination     Gateway         Genmask         Flags Metric Ref    Use Iface
0.0.0.0         192.168.128.1   0.0.0.0         UG    0      0        0 em1
192.168.3.0     0.0.0.0         255.255.255.0   U     0      0        0 eth0
192.168.12.0    0.0.0.0         255.255.255.0   U     0      0        0 eth0
192.168.128.0   0.0.0.0         255.255.255.0   U     0      0        0 em1
</pre></div>
</div>
<p>Now, there is a route back to the source IP address of the HDL on the same interface
that the packet came from!</p>
<p>However, what if, for some reason (like you already use the 192.168.12 subnet on your computer or
network, and setting the route won&#8217;t work).  That&#8217;s what option #2 and #3 are for.</p>
<ol class="arabic simple" start="2">
<li>Relaxing the Reverse Path Filter</li>
</ol>
<p>TODO</p>
<ol class="arabic simple" start="3">
<li>Disabling the Reverse Path Filter</li>
</ol>
<p>TODO</p>
</div>
<div class="section" id="troubleshooting">
<h1>Troubleshooting</h1>
</div>
<div class="section" id="conclusion">
<h1>Conclusion</h1>
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