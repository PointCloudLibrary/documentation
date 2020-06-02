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
    
    <title>Detecting people and their poses using PointCloud Library</title>
    
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
            
  <div class="section" id="detecting-people-and-their-poses-using-pointcloud-library">
<span id="gpu-people"></span><h1>Detecting people and their poses using PointCloud Library</h1>
<p>In this tutorial we will learn how detect a person and its pose in a pointcloud.
This is based on work from Koen Buys, Cedric Cagniart, Anatoly Bashkeev and Caroline Pantofaru, this
has been presented on ICRA2012 and IROS2012 and an official reference for a journal paper is in progress. A coarse outline of how it works can be seen in the following video.</p>
<blockquote>
<div><iframe width="560" height="315" src="http://www.youtube.com/embed/Wd4OM8wOO1E?rel=0" frameborder="0" allowfullscreen></iframe></div></blockquote>
<p>This shows how to detect people with an Primesense device, the full version
working on oni and pcd files can be found in the git master.
The code assumes a organised and projectable pointcloud, and should work with other
sensors then the Primesense device.</p>
<blockquote>
<div><a class="reference internal image-reference" href="_images/ss26_1.jpg"><img alt="_images/ss26_1.jpg" src="_images/ss26_1.jpg" style="width: 400pt; height: 372pt;" /></a>
<a class="reference internal image-reference" href="_images/ss26_2.jpg"><img alt="_images/ss26_2.jpg" src="_images/ss26_2.jpg" style="width: 400pt; height: 372pt;" /></a>
</div></blockquote>
<p>In order to run the code you&#8217;ll need a decent Nvidia GPU with Fermi or Kepler architecture, have a look
at the GPU installation tutorial to get up and running with your GPU installation.</p>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>The full version of this code can be found in PCL gpu/people/tools,
the following is a reduced version for the tutorial.
This version can be found in doc/tutorials/content/sources/gpu/people_detect.</p>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>Now, let&#8217;s break down the code piece by piece. Starting from the main routine.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">int</span> <span class="nf">main</span><span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="c1">// selecting GPU and prining info</span>
  <span class="kt">int</span> <span class="n">device</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="n">pc</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-gpu&quot;</span><span class="p">,</span> <span class="n">device</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">gpu</span><span class="o">::</span><span class="n">setDevice</span> <span class="p">(</span><span class="n">device</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">gpu</span><span class="o">::</span><span class="n">printShortCudaDeviceInfo</span> <span class="p">(</span><span class="n">device</span><span class="p">);</span>

  <span class="c1">// selecting data source</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Grabber</span><span class="o">&gt;</span> <span class="n">capture</span><span class="p">;</span>
  <span class="n">capture</span><span class="p">.</span><span class="n">reset</span><span class="p">(</span> <span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">OpenNIGrabber</span><span class="p">()</span> <span class="p">);</span>

  <span class="c1">//selecting tree files</span>
  <span class="n">vector</span><span class="o">&lt;</span><span class="n">string</span><span class="o">&gt;</span> <span class="n">tree_files</span><span class="p">;</span>
  <span class="n">tree_files</span><span class="p">.</span><span class="n">push_back</span><span class="p">(</span><span class="s">&quot;Data/forest1/tree_20.txt&quot;</span><span class="p">);</span>
  <span class="n">tree_files</span><span class="p">.</span><span class="n">push_back</span><span class="p">(</span><span class="s">&quot;Data/forest2/tree_20.txt&quot;</span><span class="p">);</span>
  <span class="n">tree_files</span><span class="p">.</span><span class="n">push_back</span><span class="p">(</span><span class="s">&quot;Data/forest3/tree_20.txt&quot;</span><span class="p">);</span>
  <span class="n">tree_files</span><span class="p">.</span><span class="n">push_back</span><span class="p">(</span><span class="s">&quot;Data/forest4/tree_20.txt&quot;</span><span class="p">);</span>

  <span class="n">pc</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-tree0&quot;</span><span class="p">,</span> <span class="n">tree_files</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
  <span class="n">pc</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-tree1&quot;</span><span class="p">,</span> <span class="n">tree_files</span><span class="p">[</span><span class="mi">1</span><span class="p">]);</span>
  <span class="n">pc</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-tree2&quot;</span><span class="p">,</span> <span class="n">tree_files</span><span class="p">[</span><span class="mi">2</span><span class="p">]);</span>
  <span class="n">pc</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-tree3&quot;</span><span class="p">,</span> <span class="n">tree_files</span><span class="p">[</span><span class="mi">3</span><span class="p">]);</span>

  <span class="kt">int</span> <span class="n">num_trees</span> <span class="o">=</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span><span class="n">tree_files</span><span class="p">.</span><span class="n">size</span><span class="p">();</span>
  <span class="n">pc</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-numTrees&quot;</span><span class="p">,</span> <span class="n">num_trees</span><span class="p">);</span>

  <span class="n">tree_files</span><span class="p">.</span><span class="n">resize</span><span class="p">(</span><span class="n">num_trees</span><span class="p">);</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">num_trees</span> <span class="o">==</span> <span class="mi">0</span> <span class="o">||</span> <span class="n">num_trees</span> <span class="o">&gt;</span> <span class="mi">4</span><span class="p">)</span>
    <span class="k">return</span> <span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Invalid number of trees&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">endl</span><span class="p">,</span> <span class="o">-</span><span class="mi">1</span><span class="p">;</span>

  <span class="n">try</span>
  <span class="p">{</span>
    <span class="c1">// loading trees</span>
    <span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">gpu</span><span class="o">::</span><span class="n">people</span><span class="o">::</span><span class="n">RDFBodyPartsDetector</span> <span class="n">RDFBodyPartsDetector</span><span class="p">;</span>
    <span class="n">RDFBodyPartsDetector</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">rdf</span><span class="p">(</span><span class="k">new</span> <span class="n">RDFBodyPartsDetector</span><span class="p">(</span><span class="n">tree_files</span><span class="p">));</span>
    <span class="n">PCL_INFO</span><span class="p">(</span><span class="s">&quot;Loaded files into rdf&quot;</span><span class="p">);</span>

    <span class="c1">// Create the app</span>
    <span class="n">PeoplePCDApp</span> <span class="n">app</span><span class="p">(</span><span class="o">*</span><span class="n">capture</span><span class="p">);</span>
    <span class="n">app</span><span class="p">.</span><span class="n">people_detector_</span><span class="p">.</span><span class="n">rdf_detector_</span> <span class="o">=</span> <span class="n">rdf</span><span class="p">;</span>

    <span class="c1">// executing</span>
    <span class="n">app</span><span class="p">.</span><span class="n">startMainLoop</span> <span class="p">();</span>
  <span class="p">}</span>
  <span class="k">catch</span> <span class="p">(</span><span class="k">const</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PCLException</span><span class="o">&amp;</span> <span class="n">e</span><span class="p">)</span> <span class="p">{</span> <span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;PCLException: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">e</span><span class="p">.</span><span class="n">detailedMessage</span><span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="n">endl</span><span class="p">;</span> <span class="p">}</span>  
  <span class="k">catch</span> <span class="p">(</span><span class="k">const</span> <span class="n">std</span><span class="o">::</span><span class="n">runtime_error</span><span class="o">&amp;</span> <span class="n">e</span><span class="p">)</span> <span class="p">{</span> <span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="n">e</span><span class="p">.</span><span class="n">what</span><span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="n">endl</span><span class="p">;</span> <span class="p">}</span>
  <span class="k">catch</span> <span class="p">(</span><span class="k">const</span> <span class="n">std</span><span class="o">::</span><span class="n">bad_alloc</span><span class="o">&amp;</span> <span class="cm">/*e*/</span><span class="p">)</span> <span class="p">{</span> <span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Bad alloc&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">endl</span><span class="p">;</span> <span class="p">}</span>
  <span class="k">catch</span> <span class="p">(</span><span class="k">const</span> <span class="n">std</span><span class="o">::</span><span class="n">exception</span><span class="o">&amp;</span> <span class="cm">/*e*/</span><span class="p">)</span> <span class="p">{</span> <span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Exception&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">endl</span><span class="p">;</span> <span class="p">}</span>

  <span class="k">return</span> <span class="mi">0</span><span class="p">;</span>
<span class="p">}</span>
</pre></div>
</div>
<p>First the GPU device is set, by default this is the first GPU found in the bus, but if you have multiple GPU&#8217;s in
your system, this allows you to select a specific one.
Then a OpenNI Capture is made, see the OpenNI Grabber tutorial for more info on this. (TODO add link)</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">vector</span><span class="o">&lt;</span><span class="n">string</span><span class="o">&gt;</span> <span class="n">tree_files</span><span class="p">;</span>
  <span class="n">tree_files</span><span class="p">.</span><span class="n">push_back</span><span class="p">(</span><span class="s">&quot;Data/forest1/tree_20.txt&quot;</span><span class="p">);</span>
  <span class="n">tree_files</span><span class="p">.</span><span class="n">push_back</span><span class="p">(</span><span class="s">&quot;Data/forest2/tree_20.txt&quot;</span><span class="p">);</span>
  <span class="n">tree_files</span><span class="p">.</span><span class="n">push_back</span><span class="p">(</span><span class="s">&quot;Data/forest3/tree_20.txt&quot;</span><span class="p">);</span>
  <span class="n">tree_files</span><span class="p">.</span><span class="n">push_back</span><span class="p">(</span><span class="s">&quot;Data/forest4/tree_20.txt&quot;</span><span class="p">);</span>

  <span class="n">pc</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-tree0&quot;</span><span class="p">,</span> <span class="n">tree_files</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
  <span class="n">pc</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-tree1&quot;</span><span class="p">,</span> <span class="n">tree_files</span><span class="p">[</span><span class="mi">1</span><span class="p">]);</span>
  <span class="n">pc</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-tree2&quot;</span><span class="p">,</span> <span class="n">tree_files</span><span class="p">[</span><span class="mi">2</span><span class="p">]);</span>
  <span class="n">pc</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-tree3&quot;</span><span class="p">,</span> <span class="n">tree_files</span><span class="p">[</span><span class="mi">3</span><span class="p">]);</span>
</pre></div>
</div>
<p>The implementation is based on a similar approach as Shotton et al. and thus needs off-line learned random
decision forests for labeling. The current implementation allows up to 4 decision trees to be loaded into
the forest. This is done by giving it the names of the text files to load.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="kt">int</span> <span class="n">num_trees</span> <span class="o">=</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span><span class="n">tree_files</span><span class="p">.</span><span class="n">size</span><span class="p">();</span>
  <span class="n">pc</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-numTrees&quot;</span><span class="p">,</span> <span class="n">num_trees</span><span class="p">);</span>
</pre></div>
</div>
<p>An additional parameter allows you to configure the number of trees to be loaded.</p>
<div class="highlight-cpp"><div class="highlight"><pre>    <span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">gpu</span><span class="o">::</span><span class="n">people</span><span class="o">::</span><span class="n">RDFBodyPartsDetector</span> <span class="n">RDFBodyPartsDetector</span><span class="p">;</span>
    <span class="n">RDFBodyPartsDetector</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">rdf</span><span class="p">(</span><span class="k">new</span> <span class="n">RDFBodyPartsDetector</span><span class="p">(</span><span class="n">tree_files</span><span class="p">));</span>
    <span class="n">PCL_INFO</span><span class="p">(</span><span class="s">&quot;Loaded files into rdf&quot;</span><span class="p">);</span>
</pre></div>
</div>
<p>Then the RDF object is created, loading the trees upon creation.</p>
<div class="highlight-cpp"><div class="highlight"><pre>    <span class="c1">// Create the app</span>
    <span class="n">PeoplePCDApp</span> <span class="nf">app</span><span class="p">(</span><span class="o">*</span><span class="n">capture</span><span class="p">);</span>
    <span class="n">app</span><span class="p">.</span><span class="n">people_detector_</span><span class="p">.</span><span class="n">rdf_detector_</span> <span class="o">=</span> <span class="n">rdf</span><span class="p">;</span>

    <span class="c1">// executing</span>
    <span class="n">app</span><span class="p">.</span><span class="n">startMainLoop</span> <span class="p">();</span>
</pre></div>
</div>
<p>Now we create the application object, give it the pointer to the RDF object and start the loop.
Now we&#8217;ll have a look at the main loop.</p>
<div class="highlight-cpp"><div class="highlight"><pre>    <span class="kt">void</span>
    <span class="nf">startMainLoop</span> <span class="p">()</span>
    <span class="p">{</span>
      <span class="n">cloud_cb_</span> <span class="o">=</span> <span class="nb">false</span><span class="p">;</span>

      <span class="n">PCDGrabberBase</span><span class="o">*</span> <span class="n">ispcd</span> <span class="o">=</span> <span class="k">dynamic_cast</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PCDGrabberBase</span><span class="o">*&gt;</span><span class="p">(</span><span class="o">&amp;</span><span class="n">capture_</span><span class="p">);</span>
      <span class="k">if</span> <span class="p">(</span><span class="n">ispcd</span><span class="p">)</span>
        <span class="n">cloud_cb_</span><span class="o">=</span> <span class="nb">true</span><span class="p">;</span>

      <span class="k">typedef</span> <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">openni_wrapper</span><span class="o">::</span><span class="n">DepthImage</span><span class="o">&gt;</span> <span class="n">DepthImagePtr</span><span class="p">;</span>
      <span class="k">typedef</span> <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">openni_wrapper</span><span class="o">::</span><span class="n">Image</span><span class="o">&gt;</span> <span class="n">ImagePtr</span><span class="p">;</span>

      <span class="n">boost</span><span class="o">::</span><span class="n">function</span><span class="o">&lt;</span><span class="kt">void</span> <span class="p">(</span><span class="k">const</span> <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="k">const</span> <span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointXYZRGBA</span><span class="o">&gt;</span> <span class="o">&gt;&amp;</span><span class="p">)</span><span class="o">&gt;</span> <span class="n">func1</span> <span class="o">=</span> <span class="n">boost</span><span class="o">::</span><span class="n">bind</span> <span class="p">(</span><span class="o">&amp;</span><span class="n">PeoplePCDApp</span><span class="o">::</span><span class="n">source_cb1</span><span class="p">,</span> <span class="k">this</span><span class="p">,</span> <span class="n">_1</span><span class="p">);</span>
      <span class="n">boost</span><span class="o">::</span><span class="n">function</span><span class="o">&lt;</span><span class="kt">void</span> <span class="p">(</span><span class="k">const</span> <span class="n">ImagePtr</span><span class="o">&amp;</span><span class="p">,</span> <span class="k">const</span> <span class="n">DepthImagePtr</span><span class="o">&amp;</span><span class="p">,</span> <span class="kt">float</span> <span class="n">constant</span><span class="p">)</span><span class="o">&gt;</span> <span class="n">func2</span> <span class="o">=</span> <span class="n">boost</span><span class="o">::</span><span class="n">bind</span> <span class="p">(</span><span class="o">&amp;</span><span class="n">PeoplePCDApp</span><span class="o">::</span><span class="n">source_cb2</span><span class="p">,</span> <span class="k">this</span><span class="p">,</span> <span class="n">_1</span><span class="p">,</span> <span class="n">_2</span><span class="p">,</span> <span class="n">_3</span><span class="p">);</span>                  
      <span class="n">boost</span><span class="o">::</span><span class="n">signals2</span><span class="o">::</span><span class="n">connection</span> <span class="n">c</span> <span class="o">=</span> <span class="n">cloud_cb_</span> <span class="o">?</span> <span class="n">capture_</span><span class="p">.</span><span class="n">registerCallback</span> <span class="p">(</span><span class="n">func1</span><span class="p">)</span> <span class="o">:</span> <span class="n">capture_</span><span class="p">.</span><span class="n">registerCallback</span> <span class="p">(</span><span class="n">func2</span><span class="p">);</span>

      <span class="p">{</span>
        <span class="n">boost</span><span class="o">::</span><span class="n">unique_lock</span><span class="o">&lt;</span><span class="n">boost</span><span class="o">::</span><span class="n">mutex</span><span class="o">&gt;</span> <span class="n">lock</span><span class="p">(</span><span class="n">data_ready_mutex_</span><span class="p">);</span>

        <span class="n">try</span>
        <span class="p">{</span>
          <span class="n">capture_</span><span class="p">.</span><span class="n">start</span> <span class="p">();</span>
          <span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">exit_</span> <span class="o">&amp;&amp;</span> <span class="o">!</span><span class="n">final_view_</span><span class="p">.</span><span class="n">wasStopped</span><span class="p">())</span>
          <span class="p">{</span>
            <span class="kt">bool</span> <span class="n">has_data</span> <span class="o">=</span> <span class="n">data_ready_cond_</span><span class="p">.</span><span class="n">timed_wait</span><span class="p">(</span><span class="n">lock</span><span class="p">,</span> <span class="n">boost</span><span class="o">::</span><span class="n">posix_time</span><span class="o">::</span><span class="n">millisec</span><span class="p">(</span><span class="mi">100</span><span class="p">));</span>
            <span class="k">if</span><span class="p">(</span><span class="n">has_data</span><span class="p">)</span>
            <span class="p">{</span>
              <span class="n">SampledScopeTime</span> <span class="n">fps</span><span class="p">(</span><span class="n">time_ms_</span><span class="p">);</span>

              <span class="k">if</span> <span class="p">(</span><span class="n">cloud_cb_</span><span class="p">)</span>
                <span class="n">process_return_</span> <span class="o">=</span> <span class="n">people_detector_</span><span class="p">.</span><span class="n">process</span><span class="p">(</span><span class="n">cloud_host_</span><span class="p">.</span><span class="n">makeShared</span><span class="p">());</span>
              <span class="k">else</span>
                <span class="n">process_return_</span> <span class="o">=</span> <span class="n">people_detector_</span><span class="p">.</span><span class="n">process</span><span class="p">(</span><span class="n">depth_device_</span><span class="p">,</span> <span class="n">image_device_</span><span class="p">);</span>

              <span class="o">++</span><span class="n">counter_</span><span class="p">;</span>
            <span class="p">}</span>

            <span class="k">if</span><span class="p">(</span><span class="n">has_data</span> <span class="o">&amp;&amp;</span> <span class="p">(</span><span class="n">process_return_</span> <span class="o">==</span> <span class="mi">2</span><span class="p">))</span>
              <span class="n">visualizeAndWrite</span><span class="p">();</span>
          <span class="p">}</span>
          <span class="n">final_view_</span><span class="p">.</span><span class="n">spinOnce</span> <span class="p">(</span><span class="mi">3</span><span class="p">);</span>
        <span class="p">}</span>
        <span class="k">catch</span> <span class="p">(</span><span class="k">const</span> <span class="n">std</span><span class="o">::</span><span class="n">bad_alloc</span><span class="o">&amp;</span> <span class="cm">/*e*/</span><span class="p">)</span> <span class="p">{</span> <span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Bad alloc&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">endl</span><span class="p">;</span> <span class="p">}</span>
        <span class="k">catch</span> <span class="p">(</span><span class="k">const</span> <span class="n">std</span><span class="o">::</span><span class="n">exception</span><span class="o">&amp;</span> <span class="cm">/*e*/</span><span class="p">)</span> <span class="p">{</span> <span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Exception&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">endl</span><span class="p">;</span> <span class="p">}</span>

        <span class="n">capture_</span><span class="p">.</span><span class="n">stop</span> <span class="p">();</span>
      <span class="p">}</span>
      <span class="n">c</span><span class="p">.</span><span class="n">disconnect</span><span class="p">();</span>
    <span class="p">}</span>
</pre></div>
</div>
<p>This routine first connects a callback routine to the grabber and waits for valid data to arrive.
Each time the data arrives it will call the process function of the people detector, this is a
fully encapsulated method and will call the complete pipeline.
Once the pipeline completed processing, the results can be fetched as public structs or methods from the
people detector object. Have a look at doc.pointclouds.org for more documentation on the
available structs and methods.
The visualizeAndWrite method will illustrate one of the available methods of the people detector object:</p>
<div class="highlight-cpp"><div class="highlight"><pre>    <span class="kt">void</span>
    <span class="nf">visualizeAndWrite</span><span class="p">(</span><span class="kt">bool</span> <span class="n">write</span> <span class="o">=</span> <span class="nb">false</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="k">const</span> <span class="n">PeopleDetector</span><span class="o">::</span><span class="n">Labels</span><span class="o">&amp;</span> <span class="n">labels</span> <span class="o">=</span> <span class="n">people_detector_</span><span class="p">.</span><span class="n">rdf_detector_</span><span class="o">-&gt;</span><span class="n">getLabels</span><span class="p">();</span>
      <span class="n">people</span><span class="o">::</span><span class="n">colorizeLabels</span><span class="p">(</span><span class="n">color_map_</span><span class="p">,</span> <span class="n">labels</span><span class="p">,</span> <span class="n">cmap_device_</span><span class="p">);</span>

      <span class="kt">int</span> <span class="n">c</span><span class="p">;</span>
      <span class="n">cmap_host_</span><span class="p">.</span><span class="n">width</span> <span class="o">=</span> <span class="n">cmap_device_</span><span class="p">.</span><span class="n">cols</span><span class="p">();</span>
      <span class="n">cmap_host_</span><span class="p">.</span><span class="n">height</span> <span class="o">=</span> <span class="n">cmap_device_</span><span class="p">.</span><span class="n">rows</span><span class="p">();</span>
      <span class="n">cmap_host_</span><span class="p">.</span><span class="n">points</span><span class="p">.</span><span class="n">resize</span><span class="p">(</span><span class="n">cmap_host_</span><span class="p">.</span><span class="n">width</span> <span class="o">*</span> <span class="n">cmap_host_</span><span class="p">.</span><span class="n">height</span><span class="p">);</span>
      <span class="n">cmap_device_</span><span class="p">.</span><span class="n">download</span><span class="p">(</span><span class="n">cmap_host_</span><span class="p">.</span><span class="n">points</span><span class="p">,</span> <span class="n">c</span><span class="p">);</span>

      <span class="n">final_view_</span><span class="p">.</span><span class="n">showRGBImage</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">RGB</span><span class="o">&gt;</span><span class="p">(</span><span class="n">cmap_host_</span><span class="p">);</span>
      <span class="n">final_view_</span><span class="p">.</span><span class="n">spinOnce</span><span class="p">(</span><span class="mi">1</span><span class="p">,</span> <span class="nb">true</span><span class="p">);</span>

      <span class="k">if</span> <span class="p">(</span><span class="n">cloud_cb_</span><span class="p">)</span>
      <span class="p">{</span>
        <span class="n">depth_host_</span><span class="p">.</span><span class="n">width</span> <span class="o">=</span> <span class="n">people_detector_</span><span class="p">.</span><span class="n">depth_device1_</span><span class="p">.</span><span class="n">cols</span><span class="p">();</span>
        <span class="n">depth_host_</span><span class="p">.</span><span class="n">height</span> <span class="o">=</span> <span class="n">people_detector_</span><span class="p">.</span><span class="n">depth_device1_</span><span class="p">.</span><span class="n">rows</span><span class="p">();</span>
        <span class="n">depth_host_</span><span class="p">.</span><span class="n">points</span><span class="p">.</span><span class="n">resize</span><span class="p">(</span><span class="n">depth_host_</span><span class="p">.</span><span class="n">width</span> <span class="o">*</span> <span class="n">depth_host_</span><span class="p">.</span><span class="n">height</span><span class="p">);</span>
        <span class="n">people_detector_</span><span class="p">.</span><span class="n">depth_device1_</span><span class="p">.</span><span class="n">download</span><span class="p">(</span><span class="n">depth_host_</span><span class="p">.</span><span class="n">points</span><span class="p">,</span> <span class="n">c</span><span class="p">);</span>
      <span class="p">}</span>

      <span class="n">depth_view_</span><span class="p">.</span><span class="n">showShortImage</span><span class="p">(</span><span class="o">&amp;</span><span class="n">depth_host_</span><span class="p">.</span><span class="n">points</span><span class="p">[</span><span class="mi">0</span><span class="p">],</span> <span class="n">depth_host_</span><span class="p">.</span><span class="n">width</span><span class="p">,</span> <span class="n">depth_host_</span><span class="p">.</span><span class="n">height</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">5000</span><span class="p">,</span> <span class="nb">true</span><span class="p">);</span>
      <span class="n">depth_view_</span><span class="p">.</span><span class="n">spinOnce</span><span class="p">(</span><span class="mi">1</span><span class="p">,</span> <span class="nb">true</span><span class="p">);</span>

      <span class="k">if</span> <span class="p">(</span><span class="n">write</span><span class="p">)</span>
      <span class="p">{</span>
        <span class="k">if</span> <span class="p">(</span><span class="n">cloud_cb_</span><span class="p">)</span>
          <span class="n">savePNGFile</span><span class="p">(</span><span class="n">make_name</span><span class="p">(</span><span class="n">counter_</span><span class="p">,</span> <span class="s">&quot;ii&quot;</span><span class="p">),</span> <span class="n">cloud_host_</span><span class="p">);</span>
        <span class="k">else</span>
          <span class="n">savePNGFile</span><span class="p">(</span><span class="n">make_name</span><span class="p">(</span><span class="n">counter_</span><span class="p">,</span> <span class="s">&quot;ii&quot;</span><span class="p">),</span> <span class="n">rgba_host_</span><span class="p">);</span>
        <span class="n">savePNGFile</span><span class="p">(</span><span class="n">make_name</span><span class="p">(</span><span class="n">counter_</span><span class="p">,</span> <span class="s">&quot;c2&quot;</span><span class="p">),</span> <span class="n">cmap_host_</span><span class="p">);</span>
        <span class="n">savePNGFile</span><span class="p">(</span><span class="n">make_name</span><span class="p">(</span><span class="n">counter_</span><span class="p">,</span> <span class="s">&quot;s2&quot;</span><span class="p">),</span> <span class="n">labels</span><span class="p">);</span>
        <span class="n">savePNGFile</span><span class="p">(</span><span class="n">make_name</span><span class="p">(</span><span class="n">counter_</span><span class="p">,</span> <span class="s">&quot;d1&quot;</span><span class="p">),</span> <span class="n">people_detector_</span><span class="p">.</span><span class="n">depth_device1_</span><span class="p">);</span>
        <span class="n">savePNGFile</span><span class="p">(</span><span class="n">make_name</span><span class="p">(</span><span class="n">counter_</span><span class="p">,</span> <span class="s">&quot;d2&quot;</span><span class="p">),</span> <span class="n">people_detector_</span><span class="p">.</span><span class="n">depth_device2_</span><span class="p">);</span>
      <span class="p">}</span>
    <span class="p">}</span>
</pre></div>
</div>
<p>Line 144 calls the RDF getLabels method which returns the labels on the device, these however
are a discrete enum of the labels and are visually hard to recognize, so these are converted to
colors that illustrate each body part in line 145.
At this point the results are still stored in the device memory and need to be copied to the CPU
host memory, this is done in line 151. Afterwards the images are shown and stored to disk.</p>
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
12
13
14
15
16
17
18</pre></div></td><td class="code"><div class="highlight"><pre><span class="nb">cmake_minimum_required</span><span class="p">(</span><span class="s">VERSION</span> <span class="s">2.8</span> <span class="s">FATAL_ERROR</span><span class="p">)</span>

<span class="nb">project</span><span class="p">(</span><span class="s">people_detect</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.7</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="c">#Searching CUDA</span>
<span class="nb">FIND_PACKAGE</span><span class="p">(</span><span class="s">CUDA</span><span class="p">)</span>

<span class="c">#Include the FindCUDA script</span>
<span class="nb">INCLUDE</span><span class="p">(</span><span class="s">FindCUDA</span><span class="p">)</span>

<span class="nb">cuda_add_executable</span> <span class="p">(</span><span class="s">people_detect</span> <span class="s">src/people_detect.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">people_detect</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<dl class="docutils">
<dt>After you have made the executable, you can run it. Simply do:</dt>
<dd>$ ./people_detect</dd>
</dl>
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