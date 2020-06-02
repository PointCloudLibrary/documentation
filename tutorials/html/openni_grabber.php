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
    
    <title>The OpenNI Grabber Framework in PCL</title>
    
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
            
  <div class="section" id="the-openni-grabber-framework-in-pcl">
<span id="openni-grabber"></span><h1>The OpenNI Grabber Framework in PCL</h1>
<p>As of PCL 1.0, we offer a new generic grabber interface to provide a smooth and
convenient access to different devices and their drivers, file formats and
other sources of data.</p>
<p>The first driver that we incorporated is the new OpenNI Grabber, which makes it
a breeze to request data streams from OpenNI compatible cameras. This tutorial
presents how to set up and use the grabber, and since it&#8217;s so simple, we can
keep it short :).</p>
<p>The cameras that we have tested so far are the <a class="reference external" href="http://www.primesense.com/">Primesense Reference Design</a>, <a class="reference external" href="http://www.xbox.com/kinect/">Microsoft Kinect</a> and <a class="reference external" href="http://event.asus.com/wavi/product/WAVI_Pro.aspx">Asus Xtion Pro</a> cameras:</p>
<a class="reference internal image-reference" href="_images/openni_cams.jpg"><img alt="_images/openni_cams.jpg" src="_images/openni_cams.jpg" style="height: 390px;" /></a>
</div>
<div class="section" id="simple-example">
<h1>Simple Example</h1>
<p>In <em>visualization</em>, there is a very short piece of code which contains all that
is required to set up a <em>pcl::PointCloud&lt;XYZ&gt;</em> or <em>pcl::PointCloud&lt;XYZRGB&gt;</em>
cloud callback.</p>
<p>Here is a screenshot and a video of the PCL OpenNI Viewer in action, which uses
the OpenNI Grabber.</p>
<a class="reference external image-reference" href="_images/pcl_openni_viewer.jpg"><img alt="_images/pcl_openni_viewer.jpg" src="_images/pcl_openni_viewer.jpg" style="height: 390px;" /></a>
<iframe title="PCL OpenNI Viewer example" width="480" height="390" src="http://www.youtube.com/embed/x3SaWQkPsPI?rel=0" frameborder="0" allowfullscreen></iframe><p>So let&#8217;s look at the code. From <em>visualization/tools/openni_viewer_simple.cpp</em></p>
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
42</pre></div></td><td class="code"><div class="highlight"><pre> #include &lt;pcl/io/openni_grabber.h&gt;
 #include &lt;pcl/visualization/cloud_viewer.h&gt;

 class SimpleOpenNIViewer
 {
   public:
     SimpleOpenNIViewer () : viewer (&quot;PCL OpenNI Viewer&quot;) {}

     void cloud_cb_ (const pcl::PointCloud&lt;pcl::PointXYZ&gt;::ConstPtr &amp;cloud)
     {
       if (!viewer.wasStopped())
         viewer.showCloud (cloud);
     }

     void run ()
     {
       pcl::Grabber* interface = new pcl::OpenNIGrabber();

       boost::function&lt;void (const pcl::PointCloud&lt;pcl::PointXYZ&gt;::ConstPtr&amp;)&gt; f =
         boost::bind (&amp;SimpleOpenNIViewer::cloud_cb_, this, _1);

       interface-&gt;registerCallback (f);

       interface-&gt;start ();

       while (!viewer.wasStopped())
       {
         boost::this_thread::sleep (boost::posix_time::seconds (1));
       }

       interface-&gt;stop ();
     }

     pcl::visualization::CloudViewer viewer;
 };

 int main ()
 {
   SimpleOpenNIViewer v;
   v.run ();
   return 0;
 }
</pre></div>
</td></tr></table></div>
<p>As you can see, the <em>run ()</em> function of <em>SimpleOpenNIViewer</em> first creates a
new <em>OpenNIGrabber</em> interface. The next line might seem a bit intimidating at
first, but it&#8217;s not that bad. We create a <em>boost::bind</em> object with the address
of the callback <em>cloud_cb_</em>, we pass a reference to our <em>SimpleOpenNIViewer</em>
and the argument palce holder <em>_1</em>.</p>
<p>The <em>bind</em> then gets casted to a <em>boost::function</em> object which is templated on
the callback function type, in this case <em>void (const
pcl::PointCloud&lt;pcl::PointXYZ&gt;::ConstPtr&amp;)</em>. The resulting function object can
the be registered with the <em>OpenNIGrabber</em> and subsequently started.  Note that
the <em>stop ()</em> method does not necessarily need to be called, as the destructor
takes care of that.</p>
</div>
<div class="section" id="additional-details">
<h1>Additional Details</h1>
<p>The <em>OpenNIGrabber</em> offers more than one datatype, which is the reason we made
the <em>Grabber</em> interface so generic, leading to the relatively complicated
<em>boost::bind</em> line. In fact, we can register the following callback types as of
this writing:</p>
<ul>
<li><p class="first"><cite>void (const boost::shared_ptr&lt;const pcl::PointCloud&lt;pcl::PointXYZRGB&gt; &gt;&amp;)</cite></p>
</li>
<li><p class="first"><cite>void (const boost::shared_ptr&lt;const pcl::PointCloud&lt;pcl::PointXYZ&gt; &gt;&amp;)</cite></p>
</li>
<li><p class="first"><cite>void (const boost::shared_ptr&lt;openni_wrapper::Image&gt;&amp;)</cite></p>
<p>This provides just the RGB image from the built-in camera.</p>
</li>
<li><p class="first"><cite>void (const boost::shared_ptr&lt;openni_wrapper::DepthImage&gt;&amp;)</cite></p>
<p>This provides the depth image, without any color or intensity information</p>
</li>
<li><p class="first"><cite>void (const boost::shared_ptr&lt;openni_wrapper::Image&gt;&amp;, const boost::shared_ptr&lt;openni_wrapper::DepthImage&gt;&amp;, float constant)</cite></p>
<p>When a callback of this type is registered, the grabber sends both RGB
image and depth image and the constant (<em>1 / focal length</em>), which you need
if you want to do your own disparity conversion.</p>
</li>
</ul>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">All callback types that need a depth _and_ image stream have a
synchronization mechanism enabled which ensures consistent depth and image
data. This introduces a small lag, since the synchronizer needs to wait at
least for one more set of images before sending the first ones.</p>
</div>
</div>
<div class="section" id="starting-and-stopping-streams">
<h1>Starting and stopping streams</h1>
<p>The <em>registerCallback</em> call returns a <em>boost::signals2::connection</em> object,
which we ignore in the above example. However, if you want to interrupt or
cancel one or more of the registered data streams, you can call disconnect the
callback without stopping the whole grabber:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="n">boost</span><span class="o">::</span><span class="n">signals2</span><span class="o">::</span><span class="n">connection</span> <span class="o">=</span> <span class="n">interface</span> <span class="p">(</span><span class="n">registerCallback</span> <span class="p">(</span><span class="n">f</span><span class="p">));</span>

<span class="c1">// ...</span>

<span class="k">if</span> <span class="p">(</span><span class="n">c</span><span class="p">.</span><span class="n">connected</span> <span class="p">())</span>
  <span class="n">c</span><span class="p">.</span><span class="n">disconnect</span> <span class="p">();</span>
</pre></div>
</div>
</div>
<div class="section" id="benchmark">
<h1>Benchmark</h1>
<p>The following code snippet will attempt to subscribe to both the <em>depth</em> and
<em>color</em> streams, and is provided as a way to benchmark your system. If your
computer is too slow, and you might not be able to get ~29Hz+, please contact
us. We might be able to optimize the code even further.</p>
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
51</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;pcl/point_cloud.h&gt;</span>
<span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/io/openni_grabber.h&gt;</span>
<span class="cp">#include &lt;pcl/common/time.h&gt;</span>

<span class="k">class</span> <span class="nc">SimpleOpenNIProcessor</span>
<span class="p">{</span>
<span class="nl">public:</span>
  <span class="kt">void</span> <span class="n">cloud_cb_</span> <span class="p">(</span><span class="k">const</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span><span class="o">&gt;::</span><span class="n">ConstPtr</span> <span class="o">&amp;</span><span class="n">cloud</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="k">static</span> <span class="kt">unsigned</span> <span class="n">count</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
    <span class="k">static</span> <span class="kt">double</span> <span class="n">last</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">getTime</span> <span class="p">();</span>
    <span class="k">if</span> <span class="p">(</span><span class="o">++</span><span class="n">count</span> <span class="o">==</span> <span class="mi">30</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="kt">double</span> <span class="n">now</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">getTime</span> <span class="p">();</span>
      <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;distance of center pixel :&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span> <span class="p">[(</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">&gt;&gt;</span> <span class="mi">1</span><span class="p">)</span> <span class="o">*</span> <span class="p">(</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">height</span> <span class="o">+</span> <span class="mi">1</span><span class="p">)].</span><span class="n">z</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; mm. Average framerate: &quot;</span> <span class="o">&lt;&lt;</span> <span class="kt">double</span><span class="p">(</span><span class="n">count</span><span class="p">)</span><span class="o">/</span><span class="kt">double</span><span class="p">(</span><span class="n">now</span> <span class="o">-</span> <span class="n">last</span><span class="p">)</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; Hz&quot;</span> <span class="o">&lt;&lt;</span>  <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
      <span class="n">count</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
      <span class="n">last</span> <span class="o">=</span> <span class="n">now</span><span class="p">;</span>
    <span class="p">}</span>
  <span class="p">}</span>
  
  <span class="kt">void</span> <span class="n">run</span> <span class="p">()</span>
  <span class="p">{</span>
    <span class="c1">// create a new grabber for OpenNI devices</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">Grabber</span><span class="o">*</span> <span class="n">interface</span> <span class="o">=</span> <span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">OpenNIGrabber</span><span class="p">();</span>

    <span class="c1">// make callback function from member function</span>
    <span class="n">boost</span><span class="o">::</span><span class="n">function</span><span class="o">&lt;</span><span class="kt">void</span> <span class="p">(</span><span class="k">const</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span><span class="o">&gt;::</span><span class="n">ConstPtr</span><span class="o">&amp;</span><span class="p">)</span><span class="o">&gt;</span> <span class="n">f</span> <span class="o">=</span>
      <span class="n">boost</span><span class="o">::</span><span class="n">bind</span> <span class="p">(</span><span class="o">&amp;</span><span class="n">SimpleOpenNIProcessor</span><span class="o">::</span><span class="n">cloud_cb_</span><span class="p">,</span> <span class="k">this</span><span class="p">,</span> <span class="n">_1</span><span class="p">);</span>

    <span class="c1">// connect callback function for desired signal. In this case its a point cloud with color values</span>
    <span class="n">boost</span><span class="o">::</span><span class="n">signals2</span><span class="o">::</span><span class="n">connection</span> <span class="n">c</span> <span class="o">=</span> <span class="n">interface</span><span class="o">-&gt;</span><span class="n">registerCallback</span> <span class="p">(</span><span class="n">f</span><span class="p">);</span>

    <span class="c1">// start receiving point clouds</span>
    <span class="n">interface</span><span class="o">-&gt;</span><span class="n">start</span> <span class="p">();</span>

    <span class="c1">// wait until user quits program with Ctrl-C, but no busy-waiting -&gt; sleep (1);</span>
    <span class="k">while</span> <span class="p">(</span><span class="nb">true</span><span class="p">)</span>
      <span class="n">boost</span><span class="o">::</span><span class="n">this_thread</span><span class="o">::</span><span class="n">sleep</span> <span class="p">(</span><span class="n">boost</span><span class="o">::</span><span class="n">posix_time</span><span class="o">::</span><span class="n">seconds</span> <span class="p">(</span><span class="mi">1</span><span class="p">));</span>

    <span class="c1">// stop the grabber</span>
    <span class="n">interface</span><span class="o">-&gt;</span><span class="n">stop</span> <span class="p">();</span>
  <span class="p">}</span>
<span class="p">};</span>

<span class="kt">int</span> <span class="nf">main</span> <span class="p">()</span>
<span class="p">{</span>
  <span class="n">SimpleOpenNIProcessor</span> <span class="n">v</span><span class="p">;</span>
  <span class="n">v</span><span class="p">.</span><span class="n">run</span> <span class="p">();</span>
  <span class="k">return</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
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
<div class="section" id="troubleshooting">
<h1>Troubleshooting</h1>
<p>Q: I get an error that theres now device connected:</p>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">[OpenNIGrabber] No devices connected.
terminate called after throwing an instance of &#8216;pcl::PCLIOException&#8217;
what():  pcl::OpenNIGrabber::OpenNIGrabber(const std::string&amp;) in openni_grabber.cpp &#64; 69: Device could not be initialized or no devices found.
[1]    8709 abort      openni_viewer</p>
</div>
<p>A: most probably this is a problem with the XnSensorServer. Do you have the
ps-engine package installed? Is there a old process of XnSensorServer hanging
around, try kill it.</p>
<p>Q: I get an error about a closed network connection:</p>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">terminate called after throwing an instance of &#8216;pcl::PCLIOException&#8217;
what():  No matching device found. openni_wrapper::OpenNIDevice::OpenNIDevice(xn::Context&amp;, const xn::NodeInfo&amp;, const xn::NodeInfo&amp;, const xn::NodeInfo&amp;, const xn::NodeInfo&amp;) &#64; /home/andreas/pcl/pcl/trunk/io/src/openni_camera/openni_device.cpp &#64; 96 : creating depth generator failed. Reason: The network connection has been closed!</p>
</div>
<p>A: This error can occur with newer Linux kernels that include the <em>gspca_kinect</em> kernel module. The module claims the usb interface of the kinect and prevents OpenNI from doing so.
You can either remove the kernel module (<em>rmmod gspca_kinect</em>) or blacklist it (by executing <em>echo &#8220;blacklist gspca_kinect&#8221; &gt; /etc/modprobe.d/blacklist-psengine.conf</em> as root).
The OpenNI Ubuntu packages provided by PCL already include this fix, but you might need it in other distributions.</p>
</div>
<div class="section" id="conclusion">
<h1>Conclusion</h1>
<p>The Grabber interface is very powerful and general and makes it a breeze to
connect to OpenNI compatible cameras in your code. We are in the process of
writing a FileGrabber which can be used using the same interface, and can e.g.
load all Point Cloud files from a directory and provide them to the callback at
a certain rate. The only change required is
the allocation of the Grabber Object (<em>pcl::Grabber *g = new ...;</em>).</p>
<p>If you have a sensor which you would like to have available within PCL, just
let us know at <em>pcl-developers&#64;pointclouds.org</em>, and we will figure something
out.</p>
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