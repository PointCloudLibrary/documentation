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
    
    <title>Point Cloud Compression</title>
    
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
            
  <div class="section" id="point-cloud-compression">
<span id="octree-compression"></span><h1>Point Cloud Compression</h1>
<p>Point clouds consist of huge data sets describing three dimensional points associated with
additional information such as distance, color, normals, etc. Additionally, they can be created at high rate and therefore occupy a significant amount
of memory resources. Once point clouds have to be stored or transmitted over rate-limited communication channels,
methods for compressing this kind of data become highly interesting. The Point Cloud Library provides point cloud compression functionality. It allows for encoding all kinds of point clouds including &#8220;unorganized&#8221; point clouds that are characterized by
non-existing point references, varying point size, resolution, density and/or point ordering. Furthermore, the underlying octree data structure
enables to efficiently merge point cloud data from several sources.</p>
<p><img alt="octreeCompression" src="_images/compression_tutorial.png" /></p>
<blockquote>
<div></div></blockquote>
<p>In the following, we explain how single point clouds as well
as streams of points clouds can be efficiently compressed.
In the presented example, we capture point clouds with the OpenNIGrabber to be compressed using the PCL point cloud compression techniques.</p>
</div>
<div class="section" id="the-code">
<h1>The code:</h1>
<p>First, create a file, let&#8217;s say, <tt class="docutils literal"><span class="pre">point_cloud_compression.cpp</span></tt> and place the following inside it:</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
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
99</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;pcl/point_cloud.h&gt;</span>
<span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/io/openni_grabber.h&gt;</span>
<span class="cp">#include &lt;pcl/visualization/cloud_viewer.h&gt;</span>

<span class="cp">#include &lt;pcl/compression/octree_pointcloud_compression.h&gt;</span>

<span class="cp">#include &lt;stdio.h&gt;</span>
<span class="cp">#include &lt;sstream&gt;</span>
<span class="cp">#include &lt;stdlib.h&gt;</span>

<span class="cp">#ifdef WIN32</span>
<span class="cp"># define sleep(x) Sleep((x)*1000)</span>
<span class="cp">#endif</span>

<span class="k">class</span> <span class="nc">SimpleOpenNIViewer</span>
<span class="p">{</span>
<span class="nl">public:</span>
  <span class="n">SimpleOpenNIViewer</span> <span class="p">()</span> <span class="o">:</span>
    <span class="n">viewer</span> <span class="p">(</span><span class="s">&quot; Point Cloud Compression Example&quot;</span><span class="p">)</span>
  <span class="p">{</span>
  <span class="p">}</span>

  <span class="kt">void</span>
  <span class="n">cloud_cb_</span> <span class="p">(</span><span class="k">const</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span><span class="o">&gt;::</span><span class="n">ConstPtr</span> <span class="o">&amp;</span><span class="n">cloud</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">viewer</span><span class="p">.</span><span class="n">wasStopped</span> <span class="p">())</span>
    <span class="p">{</span>
      <span class="c1">// stringstream to store compressed point cloud</span>
      <span class="n">std</span><span class="o">::</span><span class="n">stringstream</span> <span class="n">compressedData</span><span class="p">;</span>
      <span class="c1">// output pointcloud</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloudOut</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span><span class="o">&gt;</span> <span class="p">());</span>

      <span class="c1">// compress point cloud</span>
      <span class="n">PointCloudEncoder</span><span class="o">-&gt;</span><span class="n">encodePointCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">compressedData</span><span class="p">);</span>

      <span class="c1">// decompress point cloud</span>
      <span class="n">PointCloudDecoder</span><span class="o">-&gt;</span><span class="n">decodePointCloud</span> <span class="p">(</span><span class="n">compressedData</span><span class="p">,</span> <span class="n">cloudOut</span><span class="p">);</span>


      <span class="c1">// show decompressed point cloud</span>
      <span class="n">viewer</span><span class="p">.</span><span class="n">showCloud</span> <span class="p">(</span><span class="n">cloudOut</span><span class="p">);</span>
    <span class="p">}</span>
  <span class="p">}</span>

  <span class="kt">void</span>
  <span class="n">run</span> <span class="p">()</span>
  <span class="p">{</span>

    <span class="kt">bool</span> <span class="n">showStatistics</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>

    <span class="c1">// for a full list of profiles see: /io/include/pcl/compression/compression_profiles.h</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">compression_Profiles_e</span> <span class="n">compressionProfile</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">MED_RES_ONLINE_COMPRESSION_WITH_COLOR</span><span class="p">;</span>

    <span class="c1">// instantiate point cloud compression for encoding and decoding</span>
    <span class="n">PointCloudEncoder</span> <span class="o">=</span> <span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">OctreePointCloudCompression</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">compressionProfile</span><span class="p">,</span> <span class="n">showStatistics</span><span class="p">);</span>
    <span class="n">PointCloudDecoder</span> <span class="o">=</span> <span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">OctreePointCloudCompression</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span><span class="o">&gt;</span> <span class="p">();</span>

    <span class="c1">// create a new grabber for OpenNI devices</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">Grabber</span><span class="o">*</span> <span class="n">interface</span> <span class="o">=</span> <span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">OpenNIGrabber</span> <span class="p">();</span>

    <span class="c1">// make callback function from member function</span>
    <span class="n">boost</span><span class="o">::</span><span class="n">function</span><span class="o">&lt;</span><span class="kt">void</span>
    <span class="p">(</span><span class="k">const</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span><span class="o">&gt;::</span><span class="n">ConstPtr</span><span class="o">&amp;</span><span class="p">)</span><span class="o">&gt;</span> <span class="n">f</span> <span class="o">=</span> <span class="n">boost</span><span class="o">::</span><span class="n">bind</span> <span class="p">(</span><span class="o">&amp;</span><span class="n">SimpleOpenNIViewer</span><span class="o">::</span><span class="n">cloud_cb_</span><span class="p">,</span> <span class="k">this</span><span class="p">,</span> <span class="n">_1</span><span class="p">);</span>

    <span class="c1">// connect callback function for desired signal. In this case its a point cloud with color values</span>
    <span class="n">boost</span><span class="o">::</span><span class="n">signals2</span><span class="o">::</span><span class="n">connection</span> <span class="n">c</span> <span class="o">=</span> <span class="n">interface</span><span class="o">-&gt;</span><span class="n">registerCallback</span> <span class="p">(</span><span class="n">f</span><span class="p">);</span>

    <span class="c1">// start receiving point clouds</span>
    <span class="n">interface</span><span class="o">-&gt;</span><span class="n">start</span> <span class="p">();</span>

    <span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">viewer</span><span class="p">.</span><span class="n">wasStopped</span> <span class="p">())</span>
    <span class="p">{</span>
      <span class="n">sleep</span> <span class="p">(</span><span class="mi">1</span><span class="p">);</span>
    <span class="p">}</span>

    <span class="n">interface</span><span class="o">-&gt;</span><span class="n">stop</span> <span class="p">();</span>

    <span class="c1">// delete point cloud compression instances</span>
    <span class="k">delete</span> <span class="p">(</span><span class="n">PointCloudEncoder</span><span class="p">);</span>
    <span class="k">delete</span> <span class="p">(</span><span class="n">PointCloudDecoder</span><span class="p">);</span>

  <span class="p">}</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">CloudViewer</span> <span class="n">viewer</span><span class="p">;</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">OctreePointCloudCompression</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span><span class="o">&gt;*</span> <span class="n">PointCloudEncoder</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">OctreePointCloudCompression</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span><span class="o">&gt;*</span> <span class="n">PointCloudDecoder</span><span class="p">;</span>

<span class="p">};</span>

<span class="kt">int</span>
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span> <span class="o">**</span><span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">SimpleOpenNIViewer</span> <span class="n">v</span><span class="p">;</span>
  <span class="n">v</span><span class="p">.</span><span class="n">run</span> <span class="p">();</span>

  <span class="k">return</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>Now, let&#8217;s discuss the code in detail. Let&#8217;s start at the main() function: First we create a new SimpleOpenNIViewer instance and call its run() method.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">int</span>
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span> <span class="o">**</span><span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">SimpleOpenNIViewer</span> <span class="n">v</span><span class="p">;</span>
  <span class="n">v</span><span class="p">.</span><span class="n">run</span> <span class="p">();</span>

  <span class="k">return</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</div>
<p>In the run() function, we create instances of the OctreePointCloudCompression class for encoding and decoding.
They can take compression profiles as an arguments for configuring the compression algorithm. The provided compression profiles predefine
common parameter sets for point clouds captured by openNI devices. In this example, we use the <strong>MED_RES_ONLINE_COMPRESSION_WITH_COLOR</strong> profile which
applies a coordinate encoding precision of 5 cubic millimeter and enables color component encoding. It is further optimized for fast online compression.
A full list of compression profiles including their configuration can be found in the file
&#8220;/io/include/pcl/compression/compression_profiles.h&#8221;.
A full parametrization of the compression algorithm is also possible in the OctreePointCloudCompression constructor using the MANUAL_CONFIGURATION profile.
For further details on advanced parametrization, please have a look at section &#8220;Advanced Parametrization&#8221;.</p>
<div class="highlight-cpp"><div class="highlight"><pre>    <span class="kt">bool</span> <span class="n">showStatistics</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>

    <span class="c1">// for a full list of profiles see: /io/include/pcl/compression/compression_profiles.h</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">compression_Profiles_e</span> <span class="n">compressionProfile</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">MED_RES_ONLINE_COMPRESSION_WITH_COLOR</span><span class="p">;</span>

    <span class="c1">// instantiate point cloud compression for encoding and decoding</span>
    <span class="n">PointCloudEncoder</span> <span class="o">=</span> <span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">OctreePointCloudCompression</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">compressionProfile</span><span class="p">,</span> <span class="n">showStatistics</span><span class="p">);</span>
    <span class="n">PointCloudDecoder</span> <span class="o">=</span> <span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">OctreePointCloudCompression</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span><span class="o">&gt;</span> <span class="p">();</span>
</pre></div>
</div>
<p>The following code instantiates a new grabber for an OpenNI device and starts the interface callback loop.</p>
<div class="highlight-cpp"><div class="highlight"><pre>    <span class="c1">// create a new grabber for OpenNI devices</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">Grabber</span><span class="o">*</span> <span class="n">interface</span> <span class="o">=</span> <span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">OpenNIGrabber</span> <span class="p">();</span>

    <span class="c1">// make callback function from member function</span>
    <span class="n">boost</span><span class="o">::</span><span class="n">function</span><span class="o">&lt;</span><span class="kt">void</span>
    <span class="p">(</span><span class="k">const</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span><span class="o">&gt;::</span><span class="n">ConstPtr</span><span class="o">&amp;</span><span class="p">)</span><span class="o">&gt;</span> <span class="n">f</span> <span class="o">=</span> <span class="n">boost</span><span class="o">::</span><span class="n">bind</span> <span class="p">(</span><span class="o">&amp;</span><span class="n">SimpleOpenNIViewer</span><span class="o">::</span><span class="n">cloud_cb_</span><span class="p">,</span> <span class="k">this</span><span class="p">,</span> <span class="n">_1</span><span class="p">);</span>

    <span class="c1">// connect callback function for desired signal. In this case its a point cloud with color values</span>
    <span class="n">boost</span><span class="o">::</span><span class="n">signals2</span><span class="o">::</span><span class="n">connection</span> <span class="n">c</span> <span class="o">=</span> <span class="n">interface</span><span class="o">-&gt;</span><span class="n">registerCallback</span> <span class="p">(</span><span class="n">f</span><span class="p">);</span>

    <span class="c1">// start receiving point clouds</span>
    <span class="n">interface</span><span class="o">-&gt;</span><span class="n">start</span> <span class="p">();</span>

    <span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">viewer</span><span class="p">.</span><span class="n">wasStopped</span> <span class="p">())</span>
    <span class="p">{</span>
      <span class="n">sleep</span> <span class="p">(</span><span class="mi">1</span><span class="p">);</span>
    <span class="p">}</span>

    <span class="n">interface</span><span class="o">-&gt;</span><span class="n">stop</span> <span class="p">();</span>
</pre></div>
</div>
<p>In the callback function executed by the OpenNIGrabber capture loop, we first compress the captured point cloud into a stringstream buffer. That follows a
decompression step, which decodes the compressed binary data into a new point cloud object. The decoded point cloud is then sent to the point cloud viewer.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="kt">void</span>
  <span class="nf">cloud_cb_</span> <span class="p">(</span><span class="k">const</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span><span class="o">&gt;::</span><span class="n">ConstPtr</span> <span class="o">&amp;</span><span class="n">cloud</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">viewer</span><span class="p">.</span><span class="n">wasStopped</span> <span class="p">())</span>
    <span class="p">{</span>
      <span class="c1">// stringstream to store compressed point cloud</span>
      <span class="n">std</span><span class="o">::</span><span class="n">stringstream</span> <span class="n">compressedData</span><span class="p">;</span>
      <span class="c1">// output pointcloud</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloudOut</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span><span class="o">&gt;</span> <span class="p">());</span>

      <span class="c1">// compress point cloud</span>
      <span class="n">PointCloudEncoder</span><span class="o">-&gt;</span><span class="n">encodePointCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">compressedData</span><span class="p">);</span>

      <span class="c1">// decompress point cloud</span>
      <span class="n">PointCloudDecoder</span><span class="o">-&gt;</span><span class="n">decodePointCloud</span> <span class="p">(</span><span class="n">compressedData</span><span class="p">,</span> <span class="n">cloudOut</span><span class="p">);</span>


      <span class="c1">// show decompressed point cloud</span>
      <span class="n">viewer</span><span class="p">.</span><span class="n">showCloud</span> <span class="p">(</span><span class="n">cloudOut</span><span class="p">);</span>
    <span class="p">}</span>
  <span class="p">}</span>
</pre></div>
</div>
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

<span class="nb">project</span><span class="p">(</span><span class="s">point_cloud_compression</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.2</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">point_cloud_compression</span> <span class="s">point_cloud_compression.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">point_cloud_compression</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it. Simply do:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./point_cloud_compression
</pre></div>
</div>
<p>You will see something similar to:</p>
<div class="highlight-python"><div class="highlight"><pre>[OpenNIGrabber] Number devices connected: 1
[OpenNIGrabber] 1. device on bus 002:17 is a Xbox NUI Camera (2ae) from Microsoft (45e) with serial id &#39;B00364707960044B&#39;
[OpenNIGrabber] device_id is not set or has unknown format: ! Using first device.
[OpenNIGrabber] Opened &#39;Xbox NUI Camera&#39; on bus 2:17 with serial number &#39;B00364707960044B&#39;
streams alive:  image,  depth_image
*** POINTCLOUD ENCODING ***
Frame ID: 1
Encoding Frame: Intra frame
Number of encoded points: 192721
XYZ compression percentage: 3.91049%
XYZ bytes per point: 0.469259 bytes
Color compression percentage: 15.4717%
Color bytes per point: 0.618869 bytes
Size of uncompressed point cloud: 3011.27 kBytes
Size of compressed point cloud: 204 kBytes
Total bytes per point: 1.08813 bytes
Total compression percentage: 6.8008%
Compression ratio: 14.7042

*** POINTCLOUD ENCODING ***
Frame ID: 2
Encoding Frame: Prediction frame
Number of encoded points: 192721
XYZ compression percentage: 3.8132%
XYZ bytes per point: 0.457584 bytes
Color compression percentage: 15.5448%
Color bytes per point: 0.62179 bytes
Size of uncompressed point cloud: 3011.27 kBytes
Size of compressed point cloud: 203 kBytes
Total bytes per point: 1.07937 bytes
Total compression percentage: 6.74609%
Compression ratio: 14.8234

*** POINTCLOUD ENCODING ***
Frame ID: 3
Encoding Frame: Prediction frame
Number of encoded points: 192721
XYZ compression percentage: 3.79962%
XYZ bytes per point: 0.455954 bytes
Color compression percentage: 15.2121%
Color bytes per point: 0.608486 bytes
Size of uncompressed point cloud: 3011.27 kBytes
Size of compressed point cloud: 200 kBytes
Total bytes per point: 1.06444 bytes
Total compression percentage: 6.65275%
Compression ratio: 15.0314

...
</pre></div>
</div>
</div>
<div class="section" id="compression-profiles">
<h1>Compression Profiles:</h1>
<p>Compression profiles define parameter sets for the PCL point cloud encoder. They are optimized for compression of
common point clouds retrieved from the OpenNI grabber.
Please note, that the decoder does not need to be parametrized as it detects and adopts the configuration used during encoding.
The following compression profiles are available:</p>
<blockquote>
<div><ul class="simple">
<li><strong>LOW_RES_ONLINE_COMPRESSION_WITHOUT_COLOR</strong> 1 cubic centimeter resolution, no color, fast online encoding</li>
<li><strong>LOW_RES_ONLINE_COMPRESSION_WITH_COLOR</strong> 1 cubic centimeter resolution, color, fast online encoding</li>
<li><strong>MED_RES_ONLINE_COMPRESSION_WITHOUT_COLOR</strong> 5 cubic milimeter resolution, no color, fast online encoding</li>
<li><strong>MED_RES_ONLINE_COMPRESSION_WITH_COLOR</strong> 5 cubic milimeter resolution, color, fast online encoding</li>
<li><strong>HIGH_RES_ONLINE_COMPRESSION_WITHOUT_COLOR</strong> 1 cubic milimeter resolution, no color, fast online encoding</li>
<li><strong>HIGH_RES_ONLINE_COMPRESSION_WITH_COLOR</strong> 1 cubic milimeter resolution, color, fast online encoding</li>
<li><strong>LOW_RES_OFFLINE_COMPRESSION_WITHOUT_COLOR</strong> 1 cubic centimeter resolution, no color, efficient offline encoding</li>
<li><strong>LOW_RES_OFFLINE_COMPRESSION_WITH_COLOR</strong> 1 cubic centimeter resolution, color, efficient offline encoding</li>
<li><strong>MED_RES_OFFLINE_COMPRESSION_WITHOUT_COLOR</strong> 5 cubic milimeter resolution, no color, efficient offline encoding</li>
<li><strong>MED_RES_OFFLINE_COMPRESSION_WITH_COLOR</strong> 5 cubic milimeter resolution, color, efficient offline encoding</li>
<li><strong>HIGH_RES_OFFLINE_COMPRESSION_WITHOUT_COLOR</strong> 1 cubic milimeter resolution, no color, efficient offline encoding</li>
<li><strong>HIGH_RES_OFFLINE_COMPRESSION_WITH_COLOR</strong> 1 cubic milimeter resolution, color, efficient offline encoding</li>
<li><strong>MANUAL_CONFIGURATION</strong> enables manual configuration for advanced parametrization</li>
</ul>
</div></blockquote>
</div>
<div class="section" id="advanced-parametrization">
<h1>Advanced parametrization:</h1>
<p>In order to have full access to all compression related parameters, the constructor of the OctreePointCloudCompression class can initialized with additional
compression parameters. Please note, that for enabling advanced parametrization, the compressionProfile_arg argument <strong>needs</strong> to be set to <strong>MANUAL_CONFIGURATION</strong>.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="n">OctreePointCloudCompression</span> <span class="p">(</span><span class="n">compression_Profiles_e</span> <span class="n">compressionProfile_arg</span><span class="p">,</span>
                             <span class="kt">bool</span> <span class="n">showStatistics_arg</span><span class="p">,</span>
                             <span class="k">const</span> <span class="kt">double</span> <span class="n">pointResolution_arg</span><span class="p">,</span>
                             <span class="k">const</span> <span class="kt">double</span> <span class="n">octreeResolution_arg</span><span class="p">,</span>
                             <span class="kt">bool</span> <span class="n">doVoxelGridDownDownSampling_arg</span><span class="p">,</span>
                             <span class="k">const</span> <span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">iFrameRate_arg</span><span class="p">,</span>
                             <span class="kt">bool</span> <span class="n">doColorEncoding_arg</span><span class="p">,</span>
                             <span class="k">const</span> <span class="kt">unsigned</span> <span class="kt">char</span> <span class="n">colorBitResolution_arg</span>
                            <span class="p">)</span>
</pre></div>
</div>
<p>The advanced parametrization is explained in the following:</p>
<blockquote>
<div><ul class="simple">
<li><strong>compressionProfile_arg</strong>: This parameter should be set to <strong>MANUAL_CONFIGURATION</strong> for enabling advanced parametrization.</li>
<li><strong>showStatistics_arg</strong>: Print compression related statistics to stdout.</li>
<li><strong>pointResolution_arg</strong>: Define coding precision for point coordinates. This parameter should be set to a value below the sensor noise.</li>
<li><strong>octreeResolution_arg</strong>: This parameter defines the voxel size of the deployed octree. A lower voxel resolution enables faster compression at, however,
decreased compression performance. This enables a trade-off between high frame/update rates and compression efficiency.</li>
<li><strong>doVoxelGridDownDownSampling_arg</strong>: If activated, only the hierarchical octree data structure is encoded. The decoder generated points at the voxel centers. In this
way, the point cloud becomes downsampled during compression while archieving high compression performance.</li>
<li><strong>iFrameRate_arg</strong>: The point cloud compression scheme differentially encodes point clouds.  In this way, differences between the incoming point cloud and the previously encoded pointcloud is encoded in order to archive maximum compression performance. The iFrameRate_arg allows to specify the rate of frames in the stream at which incoming point clouds are <strong>not</strong> differentially encoded (similar to I/P-frames in video coding).</li>
<li><strong>doColorEncoding_arg</strong>: This option enables color component encoding.</li>
<li><strong>colorBitResolution_arg</strong>: This parameter defines the amount of bits per color component to be encoded.</li>
</ul>
</div></blockquote>
</div>
<div class="section" id="command-line-tool-for-pcl-point-cloud-stream-compression">
<h1>Command line tool for PCL point cloud stream compression</h1>
<p>The pcl apps component contains a command line tool for point cloud compression
and streaming: Simply execute &#8221;./openni_stream_compression -?&#8221; to see a full
list of options (note: the output on screen may differ):</p>
<div class="highlight-python"><div class="highlight"><pre>PCL point cloud stream compression

usage: ./openni_stream_compression [mode] [profile] [parameters]

I/O:
    -f file  : file name

file compression mode:
    -x: encode point cloud stream to file
    -d: decode from file and display point cloud stream

network streaming mode:
    -s       : start server on localhost
    -c host  : connect to server and display decoded cloud stream

optional compression profile:
    -p profile : select compression profile:
                   -&quot;lowC&quot;  Low resolution with color
                   -&quot;lowNC&quot; Low resolution without color
                   -&quot;medC&quot; Medium resolution with color
                   -&quot;medNC&quot; Medium resolution without color
                   -&quot;highC&quot; High resolution with color
                   -&quot;highNC&quot; High resolution without color

optional compression parameters:
    -r prec  : point precision
    -o prec  : octree voxel size
    -v       : enable voxel-grid downsampling
    -a       : enable color coding
    -i rate  : i-frame rate
    -b bits  : bits/color component
    -t       : output statistics
    -e       : show input cloud during encoding

example:
    ./openni_stream_compression -x -p highC -t -f pc_compressed.pcc
</pre></div>
</div>
<p>In order to stream compressed point cloud via TCP/IP, you can start the server with:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./openni_stream_compression -s
</pre></div>
</div>
<p>It will listen on port 6666 for incoming connections. Now start the client with:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./openni_stream_compression -c SERVER_NAME
</pre></div>
</div>
<p>and remotely captured point clouds will be locally shown in the point cloud viewer.</p>
</div>
<div class="section" id="conclusion">
<h1>Conclusion</h1>
<p>This PCL point cloud compression enables to efficiently compress point clouds of any type and point cloud streams.</p>
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