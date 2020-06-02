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
    
    <title>The CloudViewer</title>
    
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
            
  <div class="section" id="the-cloudviewer">
<span id="cloud-viewer"></span><h1>The CloudViewer</h1>
<p>The CloudViewer is a straight forward, simple point cloud visualization, meant
to get you up and viewing clouds in as little code as possible.</p>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">The CloudViewer class is <strong>NOT</strong> meant to be used in multi-threaded
applications! Please check the documentation on
<a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1visualization_1_1_p_c_l_visualizer.html">PCLVisualizer</a> or read the <a class="reference internal" href="pcl_visualizer.php#pcl-visualizer"><em>PCLVisualizer</em></a> tutorial
for thread safe visualization.</p>
</div>
</div>
<div class="section" id="simple-cloud-visualization">
<h1>Simple Cloud Visualization</h1>
<p>If you just want to visualize something in your app with a few lines of code,
use a snippet like the following one:</p>
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
13</pre></div></td><td class="code"><div class="highlight"><pre> #include &lt;pcl/visualization/cloud_viewer.h&gt;
 //...
 void
 foo ()
 {
   pcl::PointCloud&lt;pcl::PointXYZRGB&gt;::Ptr cloud;
   //... populate cloud
   pcl::visualization::CloudViewer viewer (&quot;Simple Cloud Viewer&quot;);
   viewer.showCloud (cloud);
   while (!viewer.wasStopped ())
   {
   }
 }
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="a-more-complete-sample">
<h1>A more complete sample:</h1>
<p>The following shows how to run code on the visualization thread.  The PCLVisualizer is
the back end of the CloudViewer, but its running in its own thread.  To access it you
must use callback functions, to avoid the visualization concurrency issues.  However
care must be taken to avoid race conditions in your code, as the callbacks will be
called from the visualization thread.</p>
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
61</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;pcl/visualization/cloud_viewer.h&gt;</span>
<span class="cp">#include &lt;iostream&gt;</span>
<span class="cp">#include &lt;pcl/io/io.h&gt;</span>
<span class="cp">#include &lt;pcl/io/pcd_io.h&gt;</span>
    
<span class="kt">int</span> <span class="n">user_data</span><span class="p">;</span>
    
<span class="kt">void</span> 
<span class="nf">viewerOneOff</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&amp;</span> <span class="n">viewer</span><span class="p">)</span>
<span class="p">{</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">setBackgroundColor</span> <span class="p">(</span><span class="mf">1.0</span><span class="p">,</span> <span class="mf">0.5</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">);</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">o</span><span class="p">;</span>
    <span class="n">o</span><span class="p">.</span><span class="n">x</span> <span class="o">=</span> <span class="mf">1.0</span><span class="p">;</span>
    <span class="n">o</span><span class="p">.</span><span class="n">y</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
    <span class="n">o</span><span class="p">.</span><span class="n">z</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">addSphere</span> <span class="p">(</span><span class="n">o</span><span class="p">,</span> <span class="mf">0.25</span><span class="p">,</span> <span class="s">&quot;sphere&quot;</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;i only run once&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    
<span class="p">}</span>
    
<span class="kt">void</span> 
<span class="nf">viewerPsycho</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&amp;</span> <span class="n">viewer</span><span class="p">)</span>
<span class="p">{</span>
    <span class="k">static</span> <span class="kt">unsigned</span> <span class="n">count</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
    <span class="n">std</span><span class="o">::</span><span class="n">stringstream</span> <span class="n">ss</span><span class="p">;</span>
    <span class="n">ss</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Once per viewer loop: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">count</span><span class="o">++</span><span class="p">;</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">removeShape</span> <span class="p">(</span><span class="s">&quot;text&quot;</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">addText</span> <span class="p">(</span><span class="n">ss</span><span class="p">.</span><span class="n">str</span><span class="p">(),</span> <span class="mi">200</span><span class="p">,</span> <span class="mi">300</span><span class="p">,</span> <span class="s">&quot;text&quot;</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
    
    <span class="c1">//FIXME: possible race condition here:</span>
    <span class="n">user_data</span><span class="o">++</span><span class="p">;</span>
<span class="p">}</span>
    
<span class="kt">int</span> 
<span class="nf">main</span> <span class="p">()</span>
<span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span><span class="o">&gt;</span><span class="p">);</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="s">&quot;my_point_cloud.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud</span><span class="p">);</span>
    
    <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">CloudViewer</span> <span class="n">viewer</span><span class="p">(</span><span class="s">&quot;Cloud Viewer&quot;</span><span class="p">);</span>
    
    <span class="c1">//blocks until the cloud is actually rendered</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">showCloud</span><span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
    
    <span class="c1">//use the following functions to get access to the underlying more advanced/powerful</span>
    <span class="c1">//PCLVisualizer</span>
    
    <span class="c1">//This will only get called once</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">runOnVisualizationThreadOnce</span> <span class="p">(</span><span class="n">viewerOneOff</span><span class="p">);</span>
    
    <span class="c1">//This will get called once per visualization iteration</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">runOnVisualizationThread</span> <span class="p">(</span><span class="n">viewerPsycho</span><span class="p">);</span>
    <span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">viewer</span><span class="p">.</span><span class="n">wasStopped</span> <span class="p">())</span>
    <span class="p">{</span>
    <span class="c1">//you can also do cool processing here</span>
    <span class="c1">//FIXME: Note that this is running in a separate thread from viewerPsycho</span>
    <span class="c1">//and you should guard against race conditions yourself...</span>
    <span class="n">user_data</span><span class="o">++</span><span class="p">;</span>
    <span class="p">}</span>
    <span class="k">return</span> <span class="mi">0</span><span class="p">;</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="compiling-and-running-the-program">
<h1>Compiling and running the program</h1>
<p>Add the following lines to your <cite>CMakeLists.txt</cite> file:</p>
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

<span class="nb">project</span><span class="p">(</span><span class="s">cloud_viewer</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.2</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">cloud_viewer</span> <span class="s">cloud_viewer.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">cloud_viewer</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it like so:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./cloud_viewer
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