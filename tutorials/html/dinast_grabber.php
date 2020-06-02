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
    
    <title>The PCL Dinast Grabber Framework</title>
    
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
            
  <div class="section" id="the-pcl-dinast-grabber-framework">
<span id="dinast-grabber"></span><h1>The PCL Dinast Grabber Framework</h1>
<p>At PCL 1.7 we offer a new driver for Dinast Cameras making use of the generic grabber interface that is present since PCL 1.0. This tutorial shows, in a nutshell, how to set up the pcl grabber to obtain data from the cameras.</p>
<p>So far it has been currently tested with the <a class="reference external" href="http://dinast.com/ipa-1110-cyclopes-ii/">IPA-1110, Cyclopes II</a> and the <a class="reference external" href="http://dinast.com/ipa-1002-ng-t-less-ng-next-generation/">IPA-1002 ng T-Less NG</a> but it is meant to work properly on the rest of the Dinast devices, since manufacturer specifications has been taken into account.</p>
<a class="reference internal image-reference" href="_images/dinast_cameras.png"><img alt="_images/dinast_cameras.png" class="align-center" src="_images/dinast_cameras.png" style="height: 290px;" /></a>
</div>
<div class="section" id="small-example">
<h1>Small example</h1>
<p>As the Dinast Grabber implements the generic grabber interface you will see high usage similarities with other pcl grabbers. In <em>applications</em> you can find a small example that contains the code required to set up a pcl::PointCloud&lt;XYZI&gt; callback to a Dinast camera device.</p>
<p>Here you can see a screenshot of the PCL Cloud Viewer showing the data from a cup laying on a table obtained through the Dinast Grabber interface:</p>
<a class="reference internal image-reference" href="_images/dinast_cup.png"><img alt="_images/dinast_cup.png" class="align-center" src="_images/dinast_cup.png" style="height: 390px;" /></a>
<p>And this is a video of the PCL Cloud Viewer showing the point cloud data corresponding to a face:</p>
<center><iframe title="PCL Dinast Grabber example" width="480" height="390" src="https://www.youtube.com/embed/6hj57RfEMBI?rel=0" frameborder="0" allowfullscreen></iframe></center><p>Dinast Grabber currently offer this data type, as is the one currently available from Dinast devices:</p>
<ul class="simple">
<li><cite>void (const boost::shared_ptr&lt;const pcl::PointCloud&lt;pcl::PointXYZI&gt; &gt;&amp;)</cite></li>
</ul>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>The code from <em>apps/src/dinast_grabber_example.cpp</em> will be used for this tutorial:</p>
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
65</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;pcl/common/time.h&gt;</span>
<span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/io/dinast_grabber.h&gt;</span>
<span class="cp">#include &lt;pcl/visualization/cloud_viewer.h&gt;</span>

<span class="k">template</span> <span class="o">&lt;</span><span class="k">typename</span> <span class="n">PointType</span><span class="o">&gt;</span>
<span class="k">class</span> <span class="nc">DinastProcessor</span>
<span class="p">{</span>
  <span class="nl">public:</span>

    <span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="n">Cloud</span><span class="p">;</span>
    <span class="k">typedef</span> <span class="k">typename</span> <span class="n">Cloud</span><span class="o">::</span><span class="n">ConstPtr</span> <span class="n">CloudConstPtr</span><span class="p">;</span>

    <span class="n">DinastProcessor</span><span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">Grabber</span><span class="o">&amp;</span> <span class="n">grabber</span><span class="p">)</span> <span class="o">:</span> <span class="n">interface</span><span class="p">(</span><span class="n">grabber</span><span class="p">),</span> <span class="n">viewer</span><span class="p">(</span><span class="s">&quot;Dinast Cloud Viewer&quot;</span><span class="p">)</span> <span class="p">{}</span>

    <span class="kt">void</span>
    <span class="n">cloud_cb_</span> <span class="p">(</span><span class="n">CloudConstPtr</span> <span class="n">cloud_cb</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="k">static</span> <span class="kt">unsigned</span> <span class="n">count</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
      <span class="k">static</span> <span class="kt">double</span> <span class="n">last</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">getTime</span> <span class="p">();</span>
      <span class="k">if</span> <span class="p">(</span><span class="o">++</span><span class="n">count</span> <span class="o">==</span> <span class="mi">30</span><span class="p">)</span>
      <span class="p">{</span>
        <span class="kt">double</span> <span class="n">now</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">getTime</span> <span class="p">();</span>
        <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Average framerate: &quot;</span> <span class="o">&lt;&lt;</span> <span class="kt">double</span><span class="p">(</span><span class="n">count</span><span class="p">)</span><span class="o">/</span><span class="kt">double</span><span class="p">(</span><span class="n">now</span> <span class="o">-</span> <span class="n">last</span><span class="p">)</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; Hz&quot;</span> <span class="o">&lt;&lt;</span>  <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
        <span class="n">count</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
        <span class="n">last</span> <span class="o">=</span> <span class="n">now</span><span class="p">;</span>
      <span class="p">}</span>
      <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">viewer</span><span class="p">.</span><span class="n">wasStopped</span><span class="p">())</span>
        <span class="n">viewer</span><span class="p">.</span><span class="n">showCloud</span><span class="p">(</span><span class="n">cloud_cb</span><span class="p">);</span>
    <span class="p">}</span>

    <span class="kt">int</span>
    <span class="n">run</span> <span class="p">()</span>
    <span class="p">{</span>

      <span class="n">boost</span><span class="o">::</span><span class="n">function</span><span class="o">&lt;</span><span class="kt">void</span> <span class="p">(</span><span class="k">const</span> <span class="n">CloudConstPtr</span><span class="o">&amp;</span><span class="p">)</span><span class="o">&gt;</span> <span class="n">f</span> <span class="o">=</span>
        <span class="n">boost</span><span class="o">::</span><span class="n">bind</span> <span class="p">(</span><span class="o">&amp;</span><span class="n">DinastProcessor</span><span class="o">::</span><span class="n">cloud_cb_</span><span class="p">,</span> <span class="k">this</span><span class="p">,</span> <span class="n">_1</span><span class="p">);</span>

      <span class="n">boost</span><span class="o">::</span><span class="n">signals2</span><span class="o">::</span><span class="n">connection</span> <span class="n">c</span> <span class="o">=</span> <span class="n">interface</span><span class="p">.</span><span class="n">registerCallback</span> <span class="p">(</span><span class="n">f</span><span class="p">);</span>

      <span class="n">interface</span><span class="p">.</span><span class="n">start</span> <span class="p">();</span>

      <span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">viewer</span><span class="p">.</span><span class="n">wasStopped</span><span class="p">())</span>
      <span class="p">{</span>
        <span class="n">boost</span><span class="o">::</span><span class="n">this_thread</span><span class="o">::</span><span class="n">sleep</span> <span class="p">(</span><span class="n">boost</span><span class="o">::</span><span class="n">posix_time</span><span class="o">::</span><span class="n">seconds</span> <span class="p">(</span><span class="mi">1</span><span class="p">));</span>
      <span class="p">}</span>

      <span class="n">interface</span><span class="p">.</span><span class="n">stop</span> <span class="p">();</span>

      <span class="k">return</span><span class="p">(</span><span class="mi">0</span><span class="p">);</span>
    <span class="p">}</span>

    <span class="n">pcl</span><span class="o">::</span><span class="n">Grabber</span><span class="o">&amp;</span> <span class="n">interface</span><span class="p">;</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">CloudViewer</span> <span class="n">viewer</span><span class="p">;</span>

<span class="p">};</span>

<span class="kt">int</span>
<span class="nf">main</span> <span class="p">()</span>
<span class="p">{</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">DinastGrabber</span> <span class="n">grabber</span><span class="p">;</span>
  <span class="n">DinastProcessor</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZI</span><span class="o">&gt;</span> <span class="n">v</span> <span class="p">(</span><span class="n">grabber</span><span class="p">);</span>
  <span class="n">v</span><span class="p">.</span><span class="n">run</span> <span class="p">();</span>
  <span class="k">return</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>At first, when the constructor of DinastProcessor gets called, the Grabber and CloudViewer Classes are also initialized:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="n">DinastProcessor</span><span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">Grabber</span><span class="o">&amp;</span> <span class="n">grabber</span><span class="p">)</span> <span class="o">:</span> <span class="n">interface</span><span class="p">(</span><span class="n">grabber</span><span class="p">),</span> <span class="n">viewer</span><span class="p">(</span><span class="s">&quot;Dinast Cloud Viewer&quot;</span><span class="p">)</span> <span class="p">{}</span>
</pre></div>
</div>
<p>At the run function what we first have is actually the callback and its registration:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="n">boost</span><span class="o">::</span><span class="n">function</span><span class="o">&lt;</span><span class="kt">void</span> <span class="p">(</span><span class="k">const</span> <span class="n">CloudConstPtr</span><span class="o">&amp;</span><span class="p">)</span><span class="o">&gt;</span> <span class="n">f</span> <span class="o">=</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">bind</span> <span class="p">(</span><span class="o">&amp;</span><span class="n">DinastProcessor</span><span class="o">::</span><span class="n">cloud_cb_</span><span class="p">,</span> <span class="k">this</span><span class="p">,</span> <span class="n">_1</span><span class="p">);</span>

<span class="n">boost</span><span class="o">::</span><span class="n">signals2</span><span class="o">::</span><span class="n">connection</span> <span class="n">c</span> <span class="o">=</span> <span class="n">interface</span><span class="p">.</span><span class="n">registerCallback</span> <span class="p">(</span><span class="n">f</span><span class="p">);</span>
</pre></div>
</div>
<p>We create a <em>boost::bind</em> object with the address of the callback <em>cloud_cb_</em>, we pass a reference to our DinastProcessor and the argument place holder <em>_1</em>.
The bind then gets casted to a boost::function object which is templated on the callback function type, in this case <em>void (const CloudConstPtr&amp;)</em>. The resulting function object is then registered with the DinastGrabber interface.</p>
<p>The <em>registerCallback</em> call returns a <em>boost::signals2::connection</em> object, which we do not use in the this example. However, if you want to interrupt or cancel one or more of the registered data streams, you can call disconnect the callback without stopping the whole grabber:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="n">boost</span><span class="o">::</span><span class="n">signals2</span><span class="o">::</span><span class="n">connection</span> <span class="o">=</span> <span class="n">interface</span> <span class="p">(</span><span class="n">registerCallback</span> <span class="p">(</span><span class="n">f</span><span class="p">));</span>

<span class="c1">// ...</span>

<span class="k">if</span> <span class="p">(</span><span class="n">c</span><span class="p">.</span><span class="n">connected</span> <span class="p">())</span>
  <span class="n">c</span><span class="p">.</span><span class="n">disconnect</span> <span class="p">();</span>
</pre></div>
</div>
<p>After the callback is set up we start the interface.
Then we loop until the viewer is stopped. Finally interface is stopped although this is not actually needed since the destructor takes care of that.</p>
<p>On the callback function <em>cloud_cb_</em> we just do some framerate calculations and we show the obtained point cloud through the CloudViewer.</p>
</div>
<div class="section" id="testing-the-code">
<h1>Testing the code</h1>
<p>We will test the grabber with the previous example. Write down the whole code to a file called <em>dinast_grabber.cpp</em> at your preferred location. Then add this as a <em>CMakeLists.txt</em> file:</p>
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

<span class="nb">project</span><span class="p">(</span><span class="s">dinast_grabber</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.7</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">dinast_grabber</span> <span class="s">dinast_grabber.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">dinast_grabber</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>Then just proceed as a usual cmake compilation:</p>
<div class="highlight-python"><div class="highlight"><pre>$ cd /PATH/TO/DINAST_EXAMPLE
$ mkdir build
$ cd build
$ cmake
$ make
</pre></div>
</div>
<p>If everything went as expected you should now have a binary to test your Dinast device.
Go ahead, run it and you should be able to see the point cloud data from the camera:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./dinast_grabber
</pre></div>
</div>
</div>
<div class="section" id="troubleshooting">
<h1>Troubleshooting</h1>
<p><strong>Q:</strong> When I run the application I get an error similar to this one:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./dinast_grabber
libusb: 0.000000 error [op_open] libusb couldn&#39;t open USB device /dev/bus/usb/002/010: Permission denied.
libusb: 0.009155 error [op_open] libusb requires write access to USB device nodes.
</pre></div>
</div>
<p>Where the last numbers of the <em>/dev/bus/usb/...</em> might vary.</p>
<p><strong>A:</strong> This means you do not have permission to access the device. You can do a quick fix on the permissions of that specific device:</p>
<div class="highlight-python"><div class="highlight"><pre>$ sudo chmod 666 /dev/bus/usb/002/010
</pre></div>
</div>
<p>Or you can make this changes permanent for all future Dinast devices writing a rule for udev.
In debian-like systems it is usually done writing this:</p>
<div class="highlight-python"><div class="highlight"><pre># make dinast device mount with writing permissions (default is read only for unknown devices)
SUBSYSTEM==&quot;usb&quot;, ATTR{idProduct}==&quot;1402&quot;, ATTR{idVendor}==&quot;18d1&quot;, MODE:=&quot;0666&quot;, OWNER:=&quot;root&quot;, GROUP:=&quot;video&quot;
</pre></div>
</div>
<p>to a file like <em>/etc/udev/rules.d/60-dinast-usb.rules</em>.</p>
<p>If you still have problems you can always use the users mailing list: <em>pcl-users&#64;pointclouds.org</em> to find some extra help.</p>
</div>
<div class="section" id="conclusions">
<h1>Conclusions</h1>
<p>With this new grabber a new kind of short-range sensors are available through the PCL Grabber interface.
It is now a breeze to connect and obtain data from Dinast devices as you do with the rest of devices supported at PCL.</p>
<p>If you have any development suggestions on these or new devices you can contact us through <em>pcl-developers&#64;pointclouds.org</em>.</p>
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