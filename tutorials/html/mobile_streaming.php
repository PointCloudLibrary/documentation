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
    
    <title>Point Cloud Streaming to Mobile Devices with Real-time Visualization</title>
    
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
            
  <div class="section" id="point-cloud-streaming-to-mobile-devices-with-real-time-visualization">
<span id="mobile-streaming"></span><h1>Point Cloud Streaming to Mobile Devices with Real-time Visualization</h1>
<p>This tutorial describes how to send point cloud data over the network from a desktop server to a client running on a mobile
device.  The tutorial describes an example app, <em>PointCloudStreaming</em>, for the Android
operating system that receives point clouds over a TCP socket and renders them
using the VES and Kiwi mobile visualization framework.  The <em>PointCloudStreaming</em>
app acts as a client, and it connects to the server program <em>pcl_openni_mobile_server</em>.
The server program uses the <tt class="docutils literal"><span class="pre">pcl::OpenNIGrabber</span></tt> to generate point clouds from an
OpenNI compatible camera.  The tutorial <a class="reference internal" href="openni_grabber.php#openni-grabber"><em>The OpenNI Grabber Framework in PCL</em></a> provides a background
for working with the <tt class="docutils literal"><span class="pre">pcl::OpenNIGrabber</span></tt>.  This tutorial describes the client and server
programs and how to run them.</p>
<blockquote>
<div><iframe title="Point cloud streaming" width="500" height="281" src="http://player.vimeo.com/video/41377003" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowfullscreen></iframe></div></blockquote>
</div>
<div class="section" id="building-and-running-the-server">
<h1>Building and running the server</h1>
<p>The server program, <em>pcl_openni_mobile_server</em>, is included with PCL as an
example app.  Build PCL with the <strong>BUILD_apps</strong> option enabled, then run the
server program from the PCL build directory:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./bin/pcl_openni_mobile_server -p 11111
</pre></div>
</div>
<p>The server will start and listen on port 11111.  You must have an OpenNI compatible
camera connected in order to run the server, otherwise the program will abort
with an error message that the OpenNI grabber could not be initialized.  When the server
starts, it will open a visualization window.  The visualization window will refresh
once after each new point cloud is sent to the client.  The server runs until the
client disconnects.  The server uses a voxel grid filter and a bounding box region
to limit the number of points sent to the client.</p>
</div>
<div class="section" id="building-and-running-the-client">
<h1>Building and running the client</h1>
<p>The client is an Android app named <em>Point Cloud Streaming</em>.  The app
is implemented using the Android <a class="reference external" href="https://developer.android.com/reference/android/app/NativeActivity.html">NativeActivity</a>.
Using <em>NativeActivity</em>, an Android app can be implemented in pure
C++ code without writing components in Java.  The app uses APIs provided by the <a class="reference external" href="http://developer.android.com/tools/sdk/ndk/index.html">Android
NDK</a> to handle touch events
and app life cycle events.  While this is suitable for an example app, apps that
demand extra features and user interface elements will require implementations that mix
native code and Java components and APIs.</p>
<p>To build the <em>PointCloudStreaming</em> app, first build its main dependency, VES and Kiwi.
Follow the <a class="reference external" href="http://vtk.org/Wiki/VES/Developers_Guide">VES Developer&#8217;s Guide</a> for
instructions on setting up your environment for compiling Android applications and
how to build VES and Kiwi.  Next, edit the file <em>mobile_apps/android/PointCloudStreaming/tools.sh</em>
and enter the correct paths for your environment.  Set the ANDROID_NDK environment
variable to the location of your Android NDK installation, and then run the bash
scripts in order:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./configure_cmake.sh
$ ./configure_ant.sh
$ ./compile.sh
$ ./install.sh
</pre></div>
</div>
<p>Make sure your device is connected before running install.sh.  After running
install.sh, the <em>Point Cloud Streaming</em> app will be found on your device.  When
you start the app, it will automatically attempt to connect to the server program.
The app uses a text file to read the server host and port information that will
be used.  The first time the app runs, it will write a text file to the Android
device&#8217;s SD card at <em>/mnt/sdcard/PointCloudStreaming/appConfig.txt</em>.  You can edit this file to input the correct server host and
port information, or modify the file <em>mobile_apps/android/PointCloudStreaming/assets/appConfig.txt</em>
in the PCL source code repository and recompile the app.</p>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">Note, the app will not overwrite <em>appConfig.txt</em> on the SD card if it already exists.</p>
</div>
<p>When the app runs, it will display text on the screen indicating whether or not
the server connection was successful.  Upon successful connection to the server,
the app will begin receiving and rendering point clouds.  The camera can be
repositioned using single-touch and two-touch gestures.</p>
</div>
<div class="section" id="server-program-implementation">
<h1>Server program implementation</h1>
<p>The server is provided by the program pcl_openni_mobile_server and implemented
by the source file  <em>apps/src/openni_mobile_server.cpp</em>.  The program&#8217;s entry
point is the <em>main()</em> function.  It parses command line arguments and then creates
a <em>PCLMobileServer</em> object, passing the command line arguments as parameters to
the constructor.  The remainder of the program is handled by the <em>PCLMobileServer</em>
object.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="n">PCLMobileServer</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span><span class="o">&gt;</span> <span class="n">server</span> <span class="p">(</span><span class="n">device_id</span><span class="p">,</span> <span class="n">port</span><span class="p">,</span> <span class="n">leaf_x</span><span class="p">,</span> <span class="n">leaf_y</span><span class="p">,</span> <span class="n">leaf_z</span><span class="p">);</span>
<span class="n">server</span><span class="p">.</span><span class="n">run</span> <span class="p">();</span>
</pre></div>
</div>
<p>The <em>run()</em> method initializes some objects before entering the main server loop.
The first object to be initialized is the <tt class="docutils literal"><span class="pre">pcl::OpenNIGrabber</span></tt>.  The grabber is
used to generate point clouds from an OpenNI compatible camera.  Here are the first
few lines of the <em>run()</em> method:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="n">pcl</span><span class="o">::</span><span class="n">OpenNIGrabber</span> <span class="n">grabber</span> <span class="p">(</span><span class="n">device_id_</span><span class="p">);</span>
<span class="n">boost</span><span class="o">::</span><span class="n">function</span><span class="o">&lt;</span><span class="kt">void</span> <span class="p">(</span><span class="k">const</span> <span class="n">CloudConstPtr</span><span class="o">&amp;</span><span class="p">)</span><span class="o">&gt;</span> <span class="n">handler_function</span> <span class="o">=</span> <span class="n">boost</span><span class="o">::</span><span class="n">bind</span> <span class="p">(</span><span class="o">&amp;</span><span class="n">PCLMobileServer</span><span class="o">::</span><span class="n">handleIncomingCloud</span><span class="p">,</span> <span class="k">this</span><span class="p">,</span> <span class="n">_1</span><span class="p">);</span>
<span class="n">grabber</span><span class="p">.</span><span class="n">registerCallback</span> <span class="p">(</span><span class="n">handler_function</span><span class="p">);</span>
<span class="n">grabber</span><span class="p">.</span><span class="n">start</span> <span class="p">();</span>
</pre></div>
</div>
<p>The grabber is constructed and then the <em>handleIncomingCloud</em> method is bound and
registered as a callback on grabber.  This callback method is called for each new
point cloud that is generated.  The OpenNIGrabber runs in a separate thread, and
the <em>handleIncomingCloud</em> method is called on that thread.  This allows the
grabber is generate and process point clouds continuously while the server
loop runs in the main thread.  Here is the implementation of the <em>handleIncomingCloud()</em>
method:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">void</span>
<span class="nf">handleIncomingCloud</span> <span class="p">(</span><span class="k">const</span> <span class="n">CloudConstPtr</span><span class="o">&amp;</span> <span class="n">new_cloud</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">CloudPtr</span> <span class="n">temp_cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">Cloud</span><span class="p">);</span>
  <span class="n">voxel_grid_filter_</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">new_cloud</span><span class="p">);</span>
  <span class="n">voxel_grid_filter_</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">temp_cloud</span><span class="p">);</span>

  <span class="n">PointCloudBuffers</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">new_buffers</span> <span class="o">=</span> <span class="n">PointCloudBuffers</span><span class="o">::</span><span class="n">Ptr</span> <span class="p">(</span><span class="k">new</span> <span class="n">PointCloudBuffers</span><span class="p">);</span>
  <span class="n">CopyPointCloudToBuffers</span> <span class="p">(</span><span class="n">temp_cloud</span><span class="p">,</span> <span class="o">*</span><span class="n">new_buffers</span><span class="p">);</span>

  <span class="n">boost</span><span class="o">::</span><span class="n">mutex</span><span class="o">::</span><span class="n">scoped_lock</span> <span class="n">lock</span> <span class="p">(</span><span class="n">mutex_</span><span class="p">);</span>
  <span class="n">filtered_cloud_</span> <span class="o">=</span> <span class="n">temp_cloud</span><span class="p">;</span>
  <span class="n">buffers_</span> <span class="o">=</span> <span class="n">new_buffers</span><span class="p">;</span>
<span class="p">}</span>
</pre></div>
</div>
<p>The new cloud is filtered through a voxel grid filter.  The result of the voxel
grid filter is then copied into a <em>PointCloudBuffers</em> object.  This object
is a struct that contains the buffers that will be sent over the TCP
socket to the client:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">struct</span> <span class="n">PointCloudBuffers</span>
<span class="p">{</span>
  <span class="k">typedef</span> <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">PointCloudBuffers</span><span class="o">&gt;</span> <span class="n">Ptr</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">short</span><span class="o">&gt;</span> <span class="n">points</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">unsigned</span> <span class="kt">char</span><span class="o">&gt;</span> <span class="n">rgb</span><span class="p">;</span>
<span class="p">};</span>
</pre></div>
</div>
<p>The <em>PointCloudBuffers</em> struct contains two vectors, one for points and one
for rgb colors.  The points vector is defined using short.  Each xyz point
coordinate of the point cloud is converted from float to short in order to
reduce the number of bytes required to represent the coordinate.  This conversion
results in a loss of precision, but the assumption is that the point clouds generated
by the <tt class="docutils literal"><span class="pre">pcl::OpenNIGrabber</span></tt> will have units in meters and the extent of the point
cloud will be limited to only several meters.  The short data type contains
enough bits to acceptably represent such value ranges for the purposes of
visualization.</p>
<p>The conversion from float to short is performed by the <em>CopyPointCloudToBuffers</em>
function.  The function also defines a fixed, axis aligned bounding box, outside
of which points will be culled.  The function loops over all the points in the
point cloud and copies the xyz and rgb values into buffers, while skipping points
that lie outside of the predefined bounding box or contain NaN values.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">void</span>
<span class="nf">CopyPointCloudToBuffers</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span><span class="o">&gt;::</span><span class="n">ConstPtr</span> <span class="n">cloud</span><span class="p">,</span> <span class="n">PointCloudBuffers</span><span class="o">&amp;</span> <span class="n">cloud_buffers</span><span class="p">)</span>
<span class="p">{</span>
  <span class="k">const</span> <span class="kt">size_t</span> <span class="n">nr_points</span> <span class="o">=</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span>

  <span class="n">cloud_buffers</span><span class="p">.</span><span class="n">points</span><span class="p">.</span><span class="n">resize</span> <span class="p">(</span><span class="n">nr_points</span><span class="o">*</span><span class="mi">3</span><span class="p">);</span>
  <span class="n">cloud_buffers</span><span class="p">.</span><span class="n">rgb</span><span class="p">.</span><span class="n">resize</span> <span class="p">(</span><span class="n">nr_points</span><span class="o">*</span><span class="mi">3</span><span class="p">);</span>

  <span class="k">const</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span>  <span class="n">bounds_min</span> <span class="p">(</span><span class="o">-</span><span class="mf">0.9</span><span class="p">,</span> <span class="o">-</span><span class="mf">0.8</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">);</span>
  <span class="k">const</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span>  <span class="n">bounds_max</span> <span class="p">(</span><span class="mf">0.9</span><span class="p">,</span> <span class="mf">3.0</span><span class="p">,</span> <span class="mf">3.3</span><span class="p">);</span>

  <span class="kt">size_t</span> <span class="n">j</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">nr_points</span><span class="p">;</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>

    <span class="k">const</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span><span class="o">&amp;</span> <span class="n">point</span> <span class="o">=</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">];</span>

    <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">pcl_isfinite</span> <span class="p">(</span><span class="n">point</span><span class="p">.</span><span class="n">x</span><span class="p">)</span> <span class="o">||</span>
        <span class="o">!</span><span class="n">pcl_isfinite</span> <span class="p">(</span><span class="n">point</span><span class="p">.</span><span class="n">y</span><span class="p">)</span> <span class="o">||</span>
        <span class="o">!</span><span class="n">pcl_isfinite</span> <span class="p">(</span><span class="n">point</span><span class="p">.</span><span class="n">z</span><span class="p">))</span>
      <span class="k">continue</span><span class="p">;</span>

    <span class="k">if</span> <span class="p">(</span><span class="n">point</span><span class="p">.</span><span class="n">x</span> <span class="o">&lt;</span> <span class="n">bounds_min</span><span class="p">.</span><span class="n">x</span> <span class="o">||</span>
        <span class="n">point</span><span class="p">.</span><span class="n">y</span> <span class="o">&lt;</span> <span class="n">bounds_min</span><span class="p">.</span><span class="n">y</span> <span class="o">||</span>
        <span class="n">point</span><span class="p">.</span><span class="n">z</span> <span class="o">&lt;</span> <span class="n">bounds_min</span><span class="p">.</span><span class="n">z</span> <span class="o">||</span>
        <span class="n">point</span><span class="p">.</span><span class="n">x</span> <span class="o">&gt;</span> <span class="n">bounds_max</span><span class="p">.</span><span class="n">x</span> <span class="o">||</span>
        <span class="n">point</span><span class="p">.</span><span class="n">y</span> <span class="o">&gt;</span> <span class="n">bounds_max</span><span class="p">.</span><span class="n">y</span> <span class="o">||</span>
        <span class="n">point</span><span class="p">.</span><span class="n">z</span> <span class="o">&gt;</span> <span class="n">bounds_max</span><span class="p">.</span><span class="n">z</span><span class="p">)</span>
      <span class="k">continue</span><span class="p">;</span>

    <span class="k">const</span> <span class="kt">int</span> <span class="n">conversion_factor</span> <span class="o">=</span> <span class="mi">500</span><span class="p">;</span>

    <span class="n">cloud_buffers</span><span class="p">.</span><span class="n">points</span><span class="p">[</span><span class="n">j</span><span class="o">*</span><span class="mi">3</span> <span class="o">+</span> <span class="mi">0</span><span class="p">]</span> <span class="o">=</span> <span class="k">static_cast</span><span class="o">&lt;</span><span class="kt">short</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">point</span><span class="p">.</span><span class="n">x</span> <span class="o">*</span> <span class="n">conversion_factor</span><span class="p">);</span>
    <span class="n">cloud_buffers</span><span class="p">.</span><span class="n">points</span><span class="p">[</span><span class="n">j</span><span class="o">*</span><span class="mi">3</span> <span class="o">+</span> <span class="mi">1</span><span class="p">]</span> <span class="o">=</span> <span class="k">static_cast</span><span class="o">&lt;</span><span class="kt">short</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">point</span><span class="p">.</span><span class="n">y</span> <span class="o">*</span> <span class="n">conversion_factor</span><span class="p">);</span>
    <span class="n">cloud_buffers</span><span class="p">.</span><span class="n">points</span><span class="p">[</span><span class="n">j</span><span class="o">*</span><span class="mi">3</span> <span class="o">+</span> <span class="mi">2</span><span class="p">]</span> <span class="o">=</span> <span class="k">static_cast</span><span class="o">&lt;</span><span class="kt">short</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">point</span><span class="p">.</span><span class="n">z</span> <span class="o">*</span> <span class="n">conversion_factor</span><span class="p">);</span>

    <span class="n">cloud_buffers</span><span class="p">.</span><span class="n">rgb</span><span class="p">[</span><span class="n">j</span><span class="o">*</span><span class="mi">3</span> <span class="o">+</span> <span class="mi">0</span><span class="p">]</span> <span class="o">=</span> <span class="n">point</span><span class="p">.</span><span class="n">r</span><span class="p">;</span>
    <span class="n">cloud_buffers</span><span class="p">.</span><span class="n">rgb</span><span class="p">[</span><span class="n">j</span><span class="o">*</span><span class="mi">3</span> <span class="o">+</span> <span class="mi">1</span><span class="p">]</span> <span class="o">=</span> <span class="n">point</span><span class="p">.</span><span class="n">g</span><span class="p">;</span>
    <span class="n">cloud_buffers</span><span class="p">.</span><span class="n">rgb</span><span class="p">[</span><span class="n">j</span><span class="o">*</span><span class="mi">3</span> <span class="o">+</span> <span class="mi">2</span><span class="p">]</span> <span class="o">=</span> <span class="n">point</span><span class="p">.</span><span class="n">b</span><span class="p">;</span>

    <span class="n">j</span><span class="o">++</span><span class="p">;</span>
  <span class="p">}</span>

  <span class="n">cloud_buffers</span><span class="p">.</span><span class="n">points</span><span class="p">.</span><span class="n">resize</span> <span class="p">(</span><span class="n">j</span> <span class="o">*</span> <span class="mi">3</span><span class="p">);</span>
  <span class="n">cloud_buffers</span><span class="p">.</span><span class="n">rgb</span><span class="p">.</span><span class="n">resize</span> <span class="p">(</span><span class="n">j</span> <span class="o">*</span> <span class="mi">3</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</div>
<p>The server program opens a TCP socket and waits for a client connection using
APIs provided by boost::asio and boost::asio::tcp.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="n">boost</span><span class="o">::</span><span class="n">asio</span><span class="o">::</span><span class="n">io_service</span> <span class="n">io_service</span><span class="p">;</span>
<span class="n">tcp</span><span class="o">::</span><span class="n">endpoint</span> <span class="n">endpoint</span> <span class="p">(</span><span class="n">tcp</span><span class="o">::</span><span class="n">v4</span> <span class="p">(),</span> <span class="k">static_cast</span><span class="o">&lt;</span><span class="kt">unsigned</span> <span class="kt">short</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">port_</span><span class="p">));</span>
<span class="n">tcp</span><span class="o">::</span><span class="n">acceptor</span> <span class="n">acceptor</span> <span class="p">(</span><span class="n">io_service</span><span class="p">,</span> <span class="n">endpoint</span><span class="p">);</span>
<span class="n">tcp</span><span class="o">::</span><span class="n">socket</span> <span class="n">socket</span> <span class="p">(</span><span class="n">io_service</span><span class="p">);</span>

<span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Listening on port &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">port_</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;...&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
<span class="n">acceptor</span><span class="p">.</span><span class="n">accept</span> <span class="p">(</span><span class="n">socket</span><span class="p">);</span>

<span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Client connected.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
</pre></div>
</div>
<p>After a successful connection, the program enters the main server loop:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">viewer_</span><span class="p">.</span><span class="n">wasStopped</span> <span class="p">())</span>
<span class="p">{</span>

  <span class="c1">// wait for client</span>
  <span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">nr_points</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">asio</span><span class="o">::</span><span class="n">read</span> <span class="p">(</span><span class="n">socket</span><span class="p">,</span> <span class="n">boost</span><span class="o">::</span><span class="n">asio</span><span class="o">::</span><span class="n">buffer</span> <span class="p">(</span><span class="o">&amp;</span><span class="n">nr_points</span><span class="p">,</span> <span class="k">sizeof</span> <span class="p">(</span><span class="n">nr_points</span><span class="p">)));</span>

  <span class="n">PointCloudBuffers</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">buffers_to_send</span> <span class="o">=</span> <span class="n">getLatestBuffers</span> <span class="p">();</span>

  <span class="n">nr_points</span> <span class="o">=</span> <span class="k">static_cast</span><span class="o">&lt;</span><span class="kt">unsigned</span> <span class="kt">int</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">buffers_to_send</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span><span class="p">()</span><span class="o">/</span><span class="mi">3</span><span class="p">);</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">asio</span><span class="o">::</span><span class="n">write</span> <span class="p">(</span><span class="n">socket</span><span class="p">,</span> <span class="n">boost</span><span class="o">::</span><span class="n">asio</span><span class="o">::</span><span class="n">buffer</span> <span class="p">(</span><span class="o">&amp;</span><span class="n">nr_points</span><span class="p">,</span> <span class="k">sizeof</span> <span class="p">(</span><span class="n">nr_points</span><span class="p">)));</span>

  <span class="k">if</span> <span class="p">(</span><span class="n">nr_points</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">boost</span><span class="o">::</span><span class="n">asio</span><span class="o">::</span><span class="n">write</span> <span class="p">(</span><span class="n">socket</span><span class="p">,</span> <span class="n">boost</span><span class="o">::</span><span class="n">asio</span><span class="o">::</span><span class="n">buffer</span> <span class="p">(</span><span class="o">&amp;</span><span class="n">buffers_to_send</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">front</span><span class="p">(),</span> <span class="n">nr_points</span> <span class="o">*</span> <span class="mi">3</span> <span class="o">*</span> <span class="k">sizeof</span> <span class="p">(</span><span class="kt">short</span><span class="p">)));</span>
    <span class="n">boost</span><span class="o">::</span><span class="n">asio</span><span class="o">::</span><span class="n">write</span> <span class="p">(</span><span class="n">socket</span><span class="p">,</span> <span class="n">boost</span><span class="o">::</span><span class="n">asio</span><span class="o">::</span><span class="n">buffer</span> <span class="p">(</span><span class="o">&amp;</span><span class="n">buffers_to_send</span><span class="o">-&gt;</span><span class="n">rgb</span><span class="p">.</span><span class="n">front</span><span class="p">(),</span> <span class="n">nr_points</span> <span class="o">*</span> <span class="mi">3</span> <span class="o">*</span> <span class="k">sizeof</span> <span class="p">(</span><span class="kt">unsigned</span> <span class="kt">char</span><span class="p">)));</span>
  <span class="p">}</span>

  <span class="n">counter</span><span class="o">++</span><span class="p">;</span>

  <span class="kt">double</span> <span class="n">new_time</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">getTime</span> <span class="p">();</span>
  <span class="kt">double</span> <span class="n">elapsed_time</span> <span class="o">=</span> <span class="n">new_time</span> <span class="o">-</span> <span class="n">start_time</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">elapsed_time</span> <span class="o">&gt;</span> <span class="mf">1.0</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="kt">double</span> <span class="n">frames_per_second</span> <span class="o">=</span> <span class="n">counter</span> <span class="o">/</span> <span class="n">elapsed_time</span><span class="p">;</span>
    <span class="n">start_time</span> <span class="o">=</span> <span class="n">new_time</span><span class="p">;</span>
    <span class="n">counter</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;fps: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">frames_per_second</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="p">}</span>

  <span class="n">viewer_</span><span class="p">.</span><span class="n">showCloud</span> <span class="p">(</span><span class="n">getLatestPointCloud</span> <span class="p">());</span>
<span class="p">}</span>
</pre></div>
</div>
<p>The first part of the loop waits for a message from the client.  It reads 4 bytes from
the client, but does not actually read the value sent.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="c1">// wait for client</span>
<span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">nr_points</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
<span class="n">boost</span><span class="o">::</span><span class="n">asio</span><span class="o">::</span><span class="n">read</span> <span class="p">(</span><span class="n">socket</span><span class="p">,</span> <span class="n">boost</span><span class="o">::</span><span class="n">asio</span><span class="o">::</span><span class="n">buffer</span> <span class="p">(</span><span class="o">&amp;</span><span class="n">nr_points</span><span class="p">,</span> <span class="k">sizeof</span> <span class="p">(</span><span class="n">nr_points</span><span class="p">)));</span>
</pre></div>
</div>
<p>You could extend the example code so that the client actually sends some usable
information to the server, such as new leaf size parameters to set on the voxel grid filter.</p>
<p>Next, the loop gets the latest point cloud buffers that were generated by the OpenNI grabber
callback function, and sends information about the buffer&#8217;s number of points to the client:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="n">PointCloudBuffers</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">buffers_to_send</span> <span class="o">=</span> <span class="n">getLatestBuffers</span> <span class="p">();</span>

<span class="n">nr_points</span> <span class="o">=</span> <span class="k">static_cast</span><span class="o">&lt;</span><span class="kt">unsigned</span> <span class="kt">int</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">buffers_to_send</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span><span class="p">()</span><span class="o">/</span><span class="mi">3</span><span class="p">);</span>
<span class="n">boost</span><span class="o">::</span><span class="n">asio</span><span class="o">::</span><span class="n">write</span> <span class="p">(</span><span class="n">socket</span><span class="p">,</span> <span class="n">boost</span><span class="o">::</span><span class="n">asio</span><span class="o">::</span><span class="n">buffer</span> <span class="p">(</span><span class="o">&amp;</span><span class="n">nr_points</span><span class="p">,</span> <span class="k">sizeof</span> <span class="p">(</span><span class="n">nr_points</span><span class="p">)));</span>
</pre></div>
</div>
<p>Next, if there is a non-zero number of points, the server sends the xyz and rgb
buffers to the client:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">if</span> <span class="p">(</span><span class="n">nr_points</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">asio</span><span class="o">::</span><span class="n">write</span> <span class="p">(</span><span class="n">socket</span><span class="p">,</span> <span class="n">boost</span><span class="o">::</span><span class="n">asio</span><span class="o">::</span><span class="n">buffer</span> <span class="p">(</span><span class="o">&amp;</span><span class="n">buffers_to_send</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">front</span><span class="p">(),</span> <span class="n">nr_points</span> <span class="o">*</span> <span class="mi">3</span> <span class="o">*</span> <span class="k">sizeof</span> <span class="p">(</span><span class="kt">short</span><span class="p">)));</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">asio</span><span class="o">::</span><span class="n">write</span> <span class="p">(</span><span class="n">socket</span><span class="p">,</span> <span class="n">boost</span><span class="o">::</span><span class="n">asio</span><span class="o">::</span><span class="n">buffer</span> <span class="p">(</span><span class="o">&amp;</span><span class="n">buffers_to_send</span><span class="o">-&gt;</span><span class="n">rgb</span><span class="p">.</span><span class="n">front</span><span class="p">(),</span> <span class="n">nr_points</span> <span class="o">*</span> <span class="mi">3</span> <span class="o">*</span> <span class="k">sizeof</span> <span class="p">(</span><span class="kt">unsigned</span> <span class="kt">char</span><span class="p">)));</span>
<span class="p">}</span>
</pre></div>
</div>
<p>The remainder of the code in the server loop is responsible for refreshing the
server&#8217;s visualization window and incrementing a counter for tracking the number
of point clouds per second that are transferred.  The server runs indefinitely until
it is terminated or the connection drops.</p>
</div>
<div class="section" id="client-app-implementation">
<h1>Client app implementation</h1>
<p>The client application, an Android app named <em>PointCloudStreaming</em> is implemented
in a single C++ file, <em>mobile_apps/android/PointCloudStreaming/jni/PointCloudStreaming.cpp</em>.
The app implementation contains a lot of boiler plate code for initializing the OpenGL ES 2.0
rendering context, managing application life cycle using the Android NDK APIs, and
converting touch events into high level gestures.  Most of this code is outside of the
scope of this tutorial.  This tutorial will focus on the code in the client app that is
responsible for handling point cloud streaming.  In fact, the majority of the code that
handles point cloud streaming is contained in a class named <em>vesKiwiStreamingDataRepresentation</em>
found in the <em>kiwi</em> library, part of the VES and Kiwi mobile visualization framework.
The <em>vesKiwiStreamingDataRepresentation</em> is usable by any mobile application.</p>
<p>The <em>PointCloudStreaming</em> app, in <em>PointCloudStreaming.cpp</em> instantiates the
<em>vesKiwiStreamingDataRepresentation</em> in a function named <em>connect()</em> like this:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">bool</span> <span class="nf">connect</span><span class="p">(</span><span class="k">const</span> <span class="n">std</span><span class="o">::</span><span class="n">string</span><span class="o">&amp;</span> <span class="n">host</span><span class="p">,</span> <span class="kt">int</span> <span class="n">port</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">mIsConnected</span> <span class="o">=</span> <span class="nb">false</span><span class="p">;</span>

  <span class="n">std</span><span class="o">::</span><span class="n">stringstream</span> <span class="n">hostPort</span><span class="p">;</span>
  <span class="n">hostPort</span> <span class="o">&lt;&lt;</span> <span class="n">host</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;:&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">port</span><span class="p">;</span>
  <span class="k">this</span><span class="o">-&gt;</span><span class="n">showText</span><span class="p">(</span><span class="s">&quot;Connecting to &quot;</span> <span class="o">+</span> <span class="n">hostPort</span><span class="p">.</span><span class="n">str</span><span class="p">());</span>

  <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">mDataRep</span><span class="p">)</span> <span class="p">{</span>
    <span class="n">mDataRep</span> <span class="o">=</span> <span class="n">vesKiwiStreamingDataRepresentation</span><span class="o">::</span><span class="n">Ptr</span><span class="p">(</span><span class="k">new</span> <span class="n">vesKiwiStreamingDataRepresentation</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">mDataRep</span><span class="o">-&gt;</span><span class="n">connectToServer</span><span class="p">(</span><span class="n">host</span><span class="p">,</span> <span class="n">port</span><span class="p">))</span> <span class="p">{</span>
    <span class="k">this</span><span class="o">-&gt;</span><span class="n">showText</span><span class="p">(</span><span class="s">&quot;Connection failed to &quot;</span> <span class="o">+</span> <span class="n">hostPort</span><span class="p">.</span><span class="n">str</span><span class="p">());</span>
    <span class="k">return</span> <span class="nb">false</span><span class="p">;</span>
  <span class="p">}</span>

  <span class="k">this</span><span class="o">-&gt;</span><span class="n">showText</span><span class="p">(</span><span class="s">&quot;Connected to &quot;</span> <span class="o">+</span> <span class="n">hostPort</span><span class="p">.</span><span class="n">str</span><span class="p">());</span>
  <span class="n">mIsConnected</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
  <span class="n">mDataRep</span><span class="o">-&gt;</span><span class="n">initializeWithShader</span><span class="p">(</span><span class="n">mShader</span><span class="p">);</span>
  <span class="n">mDataRep</span><span class="o">-&gt;</span><span class="n">addSelfToRenderer</span><span class="p">(</span><span class="k">this</span><span class="o">-&gt;</span><span class="n">renderer</span><span class="p">());</span>
  <span class="k">this</span><span class="o">-&gt;</span><span class="n">resetView</span><span class="p">();</span>
  <span class="k">return</span> <span class="nb">true</span><span class="p">;</span>
<span class="p">}</span>
</pre></div>
</div>
<p>A new instance is lazy constructed and stored in <em>mDataRep</em>.  The <em>mDataRep</em> object provides
functionality for initializing the connection to the server and managing
the point cloud streaming after a successful connection.  After a successful connection
is made, the <em>connect()</em> function does not need to be called again.  The <em>mDataRep</em>
object starts a new thread which reads point cloud xyz and rgb values from the TCP socket
and converts them into VES data structures that are used for rendering.  The primary
data structure used is a <em>vesGeometryData</em> which will be described in more detail later.</p>
<p>At each render loop, the <em>willRender()</em> function is called:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">void</span> <span class="nf">willRender</span><span class="p">()</span>
<span class="p">{</span>
  <span class="k">this</span><span class="o">-&gt;</span><span class="n">Superclass</span><span class="o">::</span><span class="n">willRender</span><span class="p">();</span>

  <span class="k">if</span> <span class="p">(</span><span class="n">mIsConnected</span><span class="p">)</span> <span class="p">{</span>
    <span class="k">this</span><span class="o">-&gt;</span><span class="n">mDataRep</span><span class="o">-&gt;</span><span class="n">willRender</span><span class="p">(</span><span class="k">this</span><span class="o">-&gt;</span><span class="n">renderer</span><span class="p">());</span>
  <span class="p">}</span>
  <span class="k">else</span> <span class="p">{</span>
    <span class="k">this</span><span class="o">-&gt;</span><span class="n">connect</span><span class="p">(</span><span class="n">mHost</span><span class="p">,</span> <span class="n">mPort</span><span class="p">);</span>
  <span class="p">}</span>
<span class="p">}</span>
</pre></div>
</div>
<p>If there is not a valid connection to the server, then a connection is attempted,
otherwise the <em>willRender()</em> method of <em>mDataRep</em> is called.  The <em>mDataRep</em> object
uses this opportunity to swap in the most recent <em>vesGeometryData</em> data structure
in order to update the point cloud visualization before rendering the new frame.</p>
<p>Let&#8217;s now examine some of the code in <em>vesKiwiStreamingDataRepresentation</em>.  This class
is derived from <em>vesKiwiDataRepresentation</em>.  In kiwi, a <em>data representation</em>
is a high level class that contains all the custom logic required to render
a piece of data and control its appearance.  The <em>data representation</em> ties together
many different classes from VTK and VES to accomplish its task.  For example,
it may use VTK filters and data objects, convert VTK data objects into VES data structures,
and use VES rendering classes for managing shaders, textures, and appearance details.
Advanced <em>data representations</em>, such as those derived from <em>vesKiwiWidgetRepresentation</em>
use touch events and gestures to update the data object visualization.</p>
<p>In the case of <em>vesKiwiStreamingDataRepresentation</em>, it uses a TCP socket and a
thread in order to manage a real-time visualization of a point cloud stream sent
from the server.  The server connection is established in the <em>connectToServer()</em>
method:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">bool</span> <span class="n">vesKiwiStreamingDataRepresentation</span><span class="o">::</span><span class="n">connectToServer</span><span class="p">(</span><span class="k">const</span> <span class="n">std</span><span class="o">::</span><span class="n">string</span><span class="o">&amp;</span> <span class="n">host</span><span class="p">,</span> <span class="kt">int</span> <span class="n">port</span><span class="p">)</span>
<span class="p">{</span>
  <span class="k">return</span> <span class="p">(</span><span class="k">this</span><span class="o">-&gt;</span><span class="n">Internal</span><span class="o">-&gt;</span><span class="n">Comm</span><span class="o">-&gt;</span><span class="n">ConnectToServer</span><span class="p">(</span><span class="n">host</span><span class="p">.</span><span class="n">c_str</span><span class="p">(),</span> <span class="n">port</span><span class="p">)</span> <span class="o">==</span> <span class="mi">0</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</div>
<p>In the above code, <em>this-&gt;Internal-&gt;Comm</em> is an instance of a <em>vtkClientSocket</em>.
Rather than use <em>boost::asio::tcp</em>, kiwi makes use of the networking classes
provided by VTK.  After the connection is established, the client loop is started
in a new thread:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">this</span><span class="o">-&gt;</span><span class="n">Internal</span><span class="o">-&gt;</span><span class="n">ClientThreadId</span> <span class="o">=</span> <span class="k">this</span><span class="o">-&gt;</span><span class="n">Internal</span><span class="o">-&gt;</span><span class="n">MultiThreader</span><span class="o">-&gt;</span><span class="n">SpawnThread</span><span class="p">(</span><span class="n">ClientLoop</span><span class="p">,</span> <span class="k">this</span><span class="o">-&gt;</span><span class="n">Internal</span><span class="p">);</span>
</pre></div>
</div>
<p>The client loop is implemented by the <em>ClientLoop</em> function.  The <em>this-&gt;Internal</em> pointer
is passed to the <em>ClientLoop</em> function as an argument.  The client loop runs in a
new thread and uses the <em>this-&gt;Internal</em> pointer to communicate with the main thread.
Communication is performed safely using a mutex lock.  Here is the implementation of
the client loop:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="n">VTK_THREAD_RETURN_TYPE</span> <span class="nf">ClientLoop</span><span class="p">(</span><span class="kt">void</span><span class="o">*</span> <span class="n">arg</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">vtkMultiThreader</span><span class="o">::</span><span class="n">ThreadInfo</span><span class="o">*</span> <span class="n">threadInfo</span> <span class="o">=</span> <span class="k">static_cast</span><span class="o">&lt;</span><span class="n">vtkMultiThreader</span><span class="o">::</span><span class="n">ThreadInfo</span><span class="o">*&gt;</span><span class="p">(</span><span class="n">arg</span><span class="p">);</span>

  <span class="n">vesKiwiStreamingDataRepresentation</span><span class="o">::</span><span class="n">vesInternal</span><span class="o">*</span> <span class="n">selfInternal</span> <span class="o">=</span>
    <span class="k">static_cast</span><span class="o">&lt;</span><span class="n">vesKiwiStreamingDataRepresentation</span><span class="o">::</span><span class="n">vesInternal</span><span class="o">*&gt;</span><span class="p">(</span><span class="n">threadInfo</span><span class="o">-&gt;</span><span class="n">UserData</span><span class="p">);</span>

  <span class="kt">bool</span> <span class="n">shouldQuit</span> <span class="o">=</span> <span class="nb">false</span><span class="p">;</span>
  <span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">shouldQuit</span><span class="p">)</span> <span class="p">{</span>

      <span class="n">vesGeometryData</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">geometryData</span> <span class="o">=</span> <span class="n">ReceiveGeometryData</span><span class="p">(</span><span class="n">selfInternal</span><span class="o">-&gt;</span><span class="n">Comm</span><span class="p">.</span><span class="n">GetPointer</span><span class="p">());</span>

      <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">geometryData</span><span class="p">)</span> <span class="p">{</span>
        <span class="k">break</span><span class="p">;</span>
      <span class="p">}</span>

      <span class="n">selfInternal</span><span class="o">-&gt;</span><span class="n">Lock</span><span class="o">-&gt;</span><span class="n">Lock</span><span class="p">();</span>
      <span class="n">selfInternal</span><span class="o">-&gt;</span><span class="n">GeometryData</span> <span class="o">=</span> <span class="n">geometryData</span><span class="p">;</span>
      <span class="n">selfInternal</span><span class="o">-&gt;</span><span class="n">HaveNew</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
      <span class="n">shouldQuit</span> <span class="o">=</span> <span class="n">selfInternal</span><span class="o">-&gt;</span><span class="n">ShouldQuit</span><span class="p">;</span>
      <span class="n">selfInternal</span><span class="o">-&gt;</span><span class="n">Lock</span><span class="o">-&gt;</span><span class="n">Unlock</span><span class="p">();</span>
  <span class="p">}</span>

  <span class="k">return</span> <span class="n">VTK_THREAD_RETURN_VALUE</span><span class="p">;</span>
<span class="p">}</span>
</pre></div>
</div>
<p>The bulk of the work is carried out by <em>ReceiveGeometryData()</em>.  This function
is responsible for receiving point cloud xyz and rgb buffers over the TCP
socket and copying them into a new <em>vesGeometryData</em> object that is used for
rendering.  <em>ReceiveGeometryData()</em> is implemented like this:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="n">vesGeometryData</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">ReceiveGeometryData</span><span class="p">(</span><span class="n">vtkClientSocket</span><span class="o">*</span> <span class="n">comm</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">vtkNew</span><span class="o">&lt;</span><span class="n">vtkShortArray</span><span class="o">&gt;</span> <span class="n">points</span><span class="p">;</span>
  <span class="n">vtkNew</span><span class="o">&lt;</span><span class="n">vtkUnsignedCharArray</span><span class="o">&gt;</span> <span class="n">colors</span><span class="p">;</span>
  <span class="kt">double</span> <span class="n">startTime</span> <span class="o">=</span> <span class="n">vtkTimerLog</span><span class="o">::</span><span class="n">GetUniversalTime</span><span class="p">();</span>

  <span class="kt">int</span> <span class="n">numberOfPoints</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>

  <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">comm</span><span class="o">-&gt;</span><span class="n">Send</span><span class="p">(</span><span class="o">&amp;</span><span class="n">numberOfPoints</span><span class="p">,</span> <span class="mi">4</span><span class="p">))</span> <span class="p">{</span>
    <span class="k">return</span> <span class="n">vesGeometryData</span><span class="o">::</span><span class="n">Ptr</span><span class="p">();</span>
  <span class="p">}</span>
  <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">comm</span><span class="o">-&gt;</span><span class="n">Receive</span><span class="p">(</span><span class="o">&amp;</span><span class="n">numberOfPoints</span><span class="p">,</span> <span class="mi">4</span><span class="p">))</span> <span class="p">{</span>
    <span class="k">return</span> <span class="n">vesGeometryData</span><span class="o">::</span><span class="n">Ptr</span><span class="p">();</span>
  <span class="p">}</span>

  <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">numberOfPoints</span><span class="p">)</span> <span class="p">{</span>
    <span class="k">return</span> <span class="n">vesGeometryData</span><span class="o">::</span><span class="n">Ptr</span><span class="p">(</span><span class="k">new</span> <span class="n">vesGeometryData</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="n">points</span><span class="o">-&gt;</span><span class="n">SetNumberOfTuples</span><span class="p">(</span><span class="n">numberOfPoints</span><span class="o">*</span><span class="mi">3</span><span class="p">);</span>
  <span class="n">colors</span><span class="o">-&gt;</span><span class="n">SetNumberOfComponents</span><span class="p">(</span><span class="mi">3</span><span class="p">);</span>
  <span class="n">colors</span><span class="o">-&gt;</span><span class="n">SetNumberOfTuples</span><span class="p">(</span><span class="n">numberOfPoints</span><span class="p">);</span>

  <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">comm</span><span class="o">-&gt;</span><span class="n">Receive</span><span class="p">(</span><span class="n">points</span><span class="o">-&gt;</span><span class="n">GetVoidPointer</span><span class="p">(</span><span class="mi">0</span><span class="p">),</span> <span class="n">numberOfPoints</span> <span class="o">*</span> <span class="mi">3</span> <span class="o">*</span> <span class="mi">2</span><span class="p">))</span> <span class="p">{</span>
    <span class="k">return</span> <span class="n">vesGeometryData</span><span class="o">::</span><span class="n">Ptr</span><span class="p">();</span>
  <span class="p">}</span>
  <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">comm</span><span class="o">-&gt;</span><span class="n">Receive</span><span class="p">(</span><span class="n">colors</span><span class="o">-&gt;</span><span class="n">GetVoidPointer</span><span class="p">(</span><span class="mi">0</span><span class="p">),</span> <span class="n">numberOfPoints</span> <span class="o">*</span> <span class="mi">3</span><span class="p">))</span> <span class="p">{</span>
    <span class="k">return</span> <span class="n">vesGeometryData</span><span class="o">::</span><span class="n">Ptr</span><span class="p">();</span>
  <span class="p">}</span>

  <span class="kt">double</span> <span class="n">elapsed</span> <span class="o">=</span> <span class="n">vtkTimerLog</span><span class="o">::</span><span class="n">GetUniversalTime</span><span class="p">()</span> <span class="o">-</span> <span class="n">startTime</span><span class="p">;</span>
  <span class="kt">double</span> <span class="n">kb</span> <span class="o">=</span> <span class="n">points</span><span class="o">-&gt;</span><span class="n">GetActualMemorySize</span><span class="p">()</span> <span class="o">+</span> <span class="n">colors</span><span class="o">-&gt;</span><span class="n">GetActualMemorySize</span><span class="p">();</span>
  <span class="kt">double</span> <span class="n">mb</span> <span class="o">=</span> <span class="n">kb</span><span class="o">/</span><span class="mf">1024.0</span><span class="p">;</span>

  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="n">numberOfPoints</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; points in &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">elapsed</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; seconds &quot;</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;(&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">mb</span><span class="o">/</span> <span class="n">elapsed</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;mb/s)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>


  <span class="k">return</span> <span class="nf">CreateGeometryData</span><span class="p">(</span><span class="n">points</span><span class="p">.</span><span class="n">GetPointer</span><span class="p">(),</span> <span class="n">colors</span><span class="p">.</span><span class="n">GetPointer</span><span class="p">());</span>
<span class="p">}</span>
</pre></div>
</div>
<p>The network communication code in <em>ReceiveGeometryData()</em> is written to match the
communication code in the server program.  First, a ready signal is sent from
the client to the server.  This signal is 4 bytes and is not actually used for
anything on the server side.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">int</span> <span class="n">numberOfPoints</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>

<span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">comm</span><span class="o">-&gt;</span><span class="n">Send</span><span class="p">(</span><span class="o">&amp;</span><span class="n">numberOfPoints</span><span class="p">,</span> <span class="mi">4</span><span class="p">))</span> <span class="p">{</span>
  <span class="k">return</span> <span class="n">vesGeometryData</span><span class="o">::</span><span class="n">Ptr</span><span class="p">();</span>
<span class="p">}</span>
</pre></div>
</div>
<p>The return value of <em>Send()</em> is checked to determine whether or not the
communication was successful.  If the connection was dropped then the function
aborts by returning a null <em>vesGeometryData</em> pointer.  The client loop is designed
to break out of the loop in the case of a null pointer, indicating a dropped
connection.  If the connection is still valid, but the incoming point cloud
contains zero points, then an empty <em>vesGeometryData</em> object is returned:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">numberOfPoints</span><span class="p">)</span> <span class="p">{</span>
  <span class="k">return</span> <span class="n">vesGeometryData</span><span class="o">::</span><span class="n">Ptr</span><span class="p">(</span><span class="k">new</span> <span class="n">vesGeometryData</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</div>
<p>If there is a non-zero number of points to receive, then the xyz and rgb data
is received into buffers:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="n">points</span><span class="o">-&gt;</span><span class="n">SetNumberOfTuples</span><span class="p">(</span><span class="n">numberOfPoints</span><span class="o">*</span><span class="mi">3</span><span class="p">);</span>
<span class="n">colors</span><span class="o">-&gt;</span><span class="n">SetNumberOfComponents</span><span class="p">(</span><span class="mi">3</span><span class="p">);</span>
<span class="n">colors</span><span class="o">-&gt;</span><span class="n">SetNumberOfTuples</span><span class="p">(</span><span class="n">numberOfPoints</span><span class="p">);</span>

<span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">comm</span><span class="o">-&gt;</span><span class="n">Receive</span><span class="p">(</span><span class="n">points</span><span class="o">-&gt;</span><span class="n">GetVoidPointer</span><span class="p">(</span><span class="mi">0</span><span class="p">),</span> <span class="n">numberOfPoints</span> <span class="o">*</span> <span class="mi">3</span> <span class="o">*</span> <span class="mi">2</span><span class="p">))</span> <span class="p">{</span>
  <span class="k">return</span> <span class="n">vesGeometryData</span><span class="o">::</span><span class="n">Ptr</span><span class="p">();</span>
<span class="p">}</span>
<span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">comm</span><span class="o">-&gt;</span><span class="n">Receive</span><span class="p">(</span><span class="n">colors</span><span class="o">-&gt;</span><span class="n">GetVoidPointer</span><span class="p">(</span><span class="mi">0</span><span class="p">),</span> <span class="n">numberOfPoints</span> <span class="o">*</span> <span class="mi">3</span><span class="p">))</span> <span class="p">{</span>
  <span class="k">return</span> <span class="n">vesGeometryData</span><span class="o">::</span><span class="n">Ptr</span><span class="p">();</span>
<span class="p">}</span>
</pre></div>
</div>
<p>The points object is a <em>vtkShortArray</em> and colors is a <em>vtkUnsignedCharArray</em>.
These types are analogous to std::vector&lt;short&gt; and std::vector&lt;unsigned char&gt;.
Finally, the buffers are copied into a new <em>vesGeometryData</em> object which will
be used for rendering.  The copy is performed by <em>CreateGeometryData()</em>:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="n">vesGeometryData</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">CreateGeometryData</span><span class="p">(</span><span class="n">vtkShortArray</span><span class="o">*</span> <span class="n">points</span><span class="p">,</span> <span class="n">vtkUnsignedCharArray</span><span class="o">*</span> <span class="n">colors</span><span class="p">)</span>
<span class="p">{</span>
  <span class="k">const</span> <span class="kt">int</span> <span class="n">numberOfPoints</span> <span class="o">=</span> <span class="n">points</span><span class="o">-&gt;</span><span class="n">GetNumberOfTuples</span><span class="p">()</span><span class="o">*</span><span class="n">points</span><span class="o">-&gt;</span><span class="n">GetNumberOfComponents</span><span class="p">()</span> <span class="o">/</span> <span class="mi">3</span><span class="p">;</span>

  <span class="n">vesSharedPtr</span><span class="o">&lt;</span><span class="n">vesGeometryData</span><span class="o">&gt;</span> <span class="n">output</span><span class="p">(</span><span class="k">new</span> <span class="n">vesGeometryData</span><span class="p">());</span>
  <span class="n">vesSourceDataP3f</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">sourceData</span><span class="p">(</span><span class="k">new</span> <span class="n">vesSourceDataP3f</span><span class="p">());</span>

  <span class="n">vesVertexDataP3f</span> <span class="n">vertexData</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">int</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">numberOfPoints</span><span class="p">;</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span> <span class="p">{</span>
    <span class="n">vertexData</span><span class="p">.</span><span class="n">m_position</span><span class="p">[</span><span class="mi">0</span><span class="p">]</span> <span class="o">=</span> <span class="n">points</span><span class="o">-&gt;</span><span class="n">GetValue</span><span class="p">(</span><span class="n">i</span><span class="o">*</span><span class="mi">3</span> <span class="o">+</span> <span class="mi">0</span><span class="p">);</span>
    <span class="n">vertexData</span><span class="p">.</span><span class="n">m_position</span><span class="p">[</span><span class="mi">1</span><span class="p">]</span> <span class="o">=</span> <span class="n">points</span><span class="o">-&gt;</span><span class="n">GetValue</span><span class="p">(</span><span class="n">i</span><span class="o">*</span><span class="mi">3</span> <span class="o">+</span> <span class="mi">1</span><span class="p">);</span>
    <span class="n">vertexData</span><span class="p">.</span><span class="n">m_position</span><span class="p">[</span><span class="mi">2</span><span class="p">]</span> <span class="o">=</span> <span class="n">points</span><span class="o">-&gt;</span><span class="n">GetValue</span><span class="p">(</span><span class="n">i</span><span class="o">*</span><span class="mi">3</span> <span class="o">+</span> <span class="mi">2</span><span class="p">);</span>
    <span class="n">sourceData</span><span class="o">-&gt;</span><span class="n">pushBack</span><span class="p">(</span><span class="n">vertexData</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="n">output</span><span class="o">-&gt;</span><span class="n">addSource</span><span class="p">(</span><span class="n">sourceData</span><span class="p">);</span>
  <span class="n">output</span><span class="o">-&gt;</span><span class="n">setName</span><span class="p">(</span><span class="s">&quot;PolyData&quot;</span><span class="p">);</span>

  <span class="n">vesPrimitive</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">pointPrimitive</span> <span class="p">(</span><span class="k">new</span> <span class="n">vesPrimitive</span><span class="p">());</span>
  <span class="n">pointPrimitive</span><span class="o">-&gt;</span><span class="n">setPrimitiveType</span><span class="p">(</span><span class="n">vesPrimitiveRenderType</span><span class="o">::</span><span class="n">Points</span><span class="p">);</span>
  <span class="n">pointPrimitive</span><span class="o">-&gt;</span><span class="n">setIndexCount</span><span class="p">(</span><span class="mi">1</span><span class="p">);</span>
  <span class="n">output</span><span class="o">-&gt;</span><span class="n">addPrimitive</span><span class="p">(</span><span class="n">pointPrimitive</span><span class="p">);</span>


  <span class="n">vesKiwiDataConversionTools</span><span class="o">::</span><span class="n">SetVertexColors</span><span class="p">(</span><span class="n">colors</span><span class="p">,</span> <span class="n">output</span><span class="p">);</span>
  <span class="k">return</span> <span class="n">output</span><span class="p">;</span>
<span class="p">}</span>
</pre></div>
</div>
<p>Remember, the network communication and construction of <em>vesGeometryData</em> occurs
on a thread.  The main thread is used by the application for rendering.  A
mutex lock is used to update the pointer to the most recent <em>vesGeometryData</em>
object constructed.  On the main thread, before each frame to be rendered, the
<em>vesKiwiStreamingDataRepresentation</em> has the opportunity to swap the
current <em>vesGeometryData</em> pointer with a new one.  This occurs in <em>willRender()</em>:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">void</span> <span class="n">vesKiwiStreamingDataRepresentation</span><span class="o">::</span><span class="n">willRender</span><span class="p">(</span><span class="n">vesSharedPtr</span><span class="o">&lt;</span><span class="n">vesRenderer</span><span class="o">&gt;</span> <span class="n">renderer</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">vesNotUsed</span><span class="p">(</span><span class="n">renderer</span><span class="p">);</span>

  <span class="k">this</span><span class="o">-&gt;</span><span class="n">Internal</span><span class="o">-&gt;</span><span class="n">Lock</span><span class="o">-&gt;</span><span class="n">Lock</span><span class="p">();</span>

  <span class="k">if</span> <span class="p">(</span><span class="k">this</span><span class="o">-&gt;</span><span class="n">Internal</span><span class="o">-&gt;</span><span class="n">HaveNew</span><span class="p">)</span> <span class="p">{</span>
    <span class="k">this</span><span class="o">-&gt;</span><span class="n">Internal</span><span class="o">-&gt;</span><span class="n">PolyDataRep</span><span class="o">-&gt;</span><span class="n">mapper</span><span class="p">()</span><span class="o">-&gt;</span><span class="n">setGeometryData</span><span class="p">(</span><span class="k">this</span><span class="o">-&gt;</span><span class="n">Internal</span><span class="o">-&gt;</span><span class="n">GeometryData</span><span class="p">);</span>
    <span class="k">this</span><span class="o">-&gt;</span><span class="n">Internal</span><span class="o">-&gt;</span><span class="n">HaveNew</span> <span class="o">=</span> <span class="nb">false</span><span class="p">;</span>
  <span class="p">}</span>

  <span class="k">this</span><span class="o">-&gt;</span><span class="n">Internal</span><span class="o">-&gt;</span><span class="n">Lock</span><span class="o">-&gt;</span><span class="n">Unlock</span><span class="p">();</span>
<span class="p">}</span>
</pre></div>
</div>
<p>By using threads, the network communication of the client loop is decoupled from
the application&#8217;s rendering loop.  The app is able to render the point cloud and
handle touch events to move the camera at interactive frame rates, even if the
network communication runs at a slower rate.</p>
</div>
<div class="section" id="conclusion">
<h1>Conclusion</h1>
<p>This tutorial has described client and server programs for streaming point
clouds to mobile devices.  The example client program runs on Android, but it is implemented
in native C++ code that is runnable on other mobile operating systems such
as iOS.</p>
<p>If one wants to develop their own streaming point cloud apps, a good starting
point would be to copy and rename the <em>vesKiwiStreamingDataRepresentation</em> class (instead of
deriving from it) to create a new class that can be modified to implement the client side communication.
The new class can be compiled directly with the new Android and iOS app being developed.
The source code of the VES and Kiwi mobile visualization framework contains additional examples of
Android and iOS apps.  These examples can also be used as starting points for developing new apps.
For more information, see the <a class="reference external" href="http://vtk.org/Wiki/VES">VES and Kiwi homepage</a>.</p>
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