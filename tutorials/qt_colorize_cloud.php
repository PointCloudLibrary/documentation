<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation - Point Cloud Library (PCL)</title>
</head>

<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8" />
    <title>Create a PCL visualizer in Qt to colorize clouds &#8212; PCL 0.0 documentation</title>
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
            
  <div class="section" id="create-a-pcl-visualizer-in-qt-to-colorize-clouds">
<span id="qt-colorize-cloud"></span><h1><a class="toc-backref" href="#id1">Create a PCL visualizer in Qt to colorize clouds</a></h1>
<p>Please read and do the <a class="reference external" href="http://www.pointclouds.org/documentation/tutorials/qt_visualizer.php">PCL + Qt tutorial</a> first;
only the coloring part is explained in details here.</p>
<p>In this tutorial we will learn how to color clouds by computing a <a class="reference external" href="https://en.wikipedia.org/wiki/Lookup_table">Look Up Table (LUT)</a>,
compared to the first tutorial this tutorial shows you how to connect multiple slots to one function. It also showcases how to load and save
files within the Qt interface.</p>
<div class="line-block">
<div class="line">The tutorial was tested on Linux Ubuntu 12.04 and 14.04. It also seems to be working fine on Windows 8.1 x64.</div>
<div class="line">Feel free to push modifications into the git repo to make this code/tutorial compatible with your platform !</div>
</div>
<div class="contents topic" id="contents">
<p class="topic-title">Contents</p>
<ul class="simple">
<li><p><a class="reference internal" href="#create-a-pcl-visualizer-in-qt-to-colorize-clouds" id="id1">Create a PCL visualizer in Qt to colorize clouds</a></p>
<ul>
<li><p><a class="reference internal" href="#the-project" id="id2">The project</a></p></li>
<li><p><a class="reference internal" href="#user-interface-ui" id="id3">User interface (UI)</a></p></li>
<li><p><a class="reference internal" href="#the-code" id="id4">The code</a></p>
<ul>
<li><p><a class="reference internal" href="#pclviewer-h" id="id5">pclviewer.h</a></p></li>
<li><p><a class="reference internal" href="#pclviewer-cpp" id="id6">pclviewer.cpp</a></p></li>
</ul>
</li>
<li><p><a class="reference internal" href="#compiling-and-running" id="id7">Compiling and running</a></p></li>
</ul>
</li>
</ul>
</div>
<div class="section" id="the-project">
<h2><a class="toc-backref" href="#id2">The project</a></h2>
<p>As for the other tutorial, we use <a class="reference external" href="https://en.wikipedia.org/wiki/CMake">cmake</a> instead of <a class="reference external" href="http://qt-project.org/doc/qt-4.8/qmake-manual.html">qmake</a>.
This is how I organized the project: the build folder contains all built files and the src folder holds all sources files</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>.
├── build
└── src
    ├── CMakeLists.txt
    ├── main.cpp
    ├── pclviewer.cpp
    ├── pclviewer.h
    ├── pclviewer.ui
    └── pcl_visualizer.pro
</pre></div>
</div>
<p>If you want to change this layout you will have to do minor modifications in the code, especially line 2 of <code class="docutils literal notranslate"><span class="pre">pclviewer.cpp</span></code>
Create the folder tree and download the sources files from <a class="reference external" href="https://github.com/PointCloudLibrary/pcl/tree/master/doc/tutorials/content/sources/qt_colorize_cloud">github</a>.</p>
<div class="admonition note">
<p class="admonition-title">Note</p>
<p>File paths should not contain any special character or the compilation might fail with a <code class="docutils literal notranslate"><span class="pre">moc:</span> <span class="pre">Cannot</span> <span class="pre">open</span> <span class="pre">options</span> <span class="pre">file</span> <span class="pre">specified</span> <span class="pre">with</span> <span class="pre">&#64;</span></code> error message.</p>
</div>
</div>
<div class="section" id="user-interface-ui">
<h2><a class="toc-backref" href="#id3">User interface (UI)</a></h2>
<p>The UI looks like this:</p>
<a class="reference internal image-reference" href="_images/ui.png"><img alt="_images/ui.png" src="_images/ui.png" style="height: 499px;" /></a>
<p>The vertical spacers are here to make sure everything moves fine when you re-size the window; the QVTK widget size has been set to a minimum size of
640 x 480 pixel, the layout makes sure that the QVTK widget expands when you re-size the application window.</p>
</div>
<div class="section" id="the-code">
<h2><a class="toc-backref" href="#id4">The code</a></h2>
<p>Now, let’s break down the code piece by piece.</p>
<div class="section" id="pclviewer-h">
<h3><a class="toc-backref" href="#id5">pclviewer.h</a></h3>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="k">public</span> <span class="nl">Q_SLOTS</span><span class="p">:</span>
    <span class="cm">/** @brief Triggered whenever the &quot;Save file&quot; button is clicked */</span>
    <span class="kt">void</span>
    <span class="n">saveFileButtonPressed</span> <span class="p">();</span>

    <span class="cm">/** @brief Triggered whenever the &quot;Load file&quot; button is clicked */</span>
    <span class="kt">void</span>
    <span class="nf">loadFileButtonPressed</span> <span class="p">();</span>

    <span class="cm">/** @brief Triggered whenever a button in the &quot;Color on axis&quot; group is clicked */</span>
    <span class="kt">void</span>
    <span class="nf">axisChosen</span> <span class="p">();</span>

    <span class="cm">/** @brief Triggered whenever a button in the &quot;Color mode&quot; group is clicked */</span>
    <span class="kt">void</span>
    <span class="nf">lookUpTableChosen</span> <span class="p">();</span>
</pre></div>
</div>
<p>These are the public slots triggered by the buttons in the UI.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="k">protected</span><span class="o">:</span>
    <span class="cm">/** @brief The PCL visualizer object */</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">viewer_</span><span class="p">;</span>

    <span class="cm">/** @brief The point cloud displayed */</span>
    <span class="n">PointCloudT</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">cloud_</span><span class="p">;</span>

    <span class="cm">/** @brief 0 = x | 1 = y | 2 = z */</span>
    <span class="kt">int</span> <span class="n">filtering_axis_</span><span class="p">;</span>

    <span class="cm">/** @brief Holds the color mode for @ref colorCloudDistances */</span>
    <span class="kt">int</span> <span class="n">color_mode_</span><span class="p">;</span>

    <span class="cm">/** @brief Color point cloud on X,Y or Z axis using a Look-Up Table (LUT)</span>
<span class="cm">     * Computes a LUT and color the cloud accordingly, available color palettes are :</span>
<span class="cm">     *</span>
<span class="cm">     *  Values are on a scale from 0 to 255:</span>
<span class="cm">     *  0. Blue (= 0) -&gt; Red (= 255), this is the default value</span>
<span class="cm">     *  1. Green (= 0) -&gt; Magenta (= 255)</span>
<span class="cm">     *  2. White (= 0) -&gt; Red (= 255)</span>
<span class="cm">     *  3. Grey (&lt; 128) / Red (&gt; 128)</span>
<span class="cm">     *  4. Blue -&gt; Green -&gt; Red (~ rainbow)</span>
<span class="cm">     *</span>
<span class="cm">     * @warning If there&#39;s an outlier in the data the color may seem uniform because of this outlier!</span>
<span class="cm">     * @note A boost rounding exception error will be thrown if used with a non dense point cloud</span>
<span class="cm">     */</span>
    <span class="kt">void</span>
    <span class="nf">colorCloudDistances</span> <span class="p">();</span>
</pre></div>
</div>
<dl class="simple">
<dt>These are the protected members of our class;</dt><dd><ul class="simple">
<li><p><code class="docutils literal notranslate"><span class="pre">viewer_</span></code> is the visualizer object</p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">cloud_</span></code> holds the point cloud displayed</p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">filtering_axis_</span></code> stores on which axis we want to filter the point cloud. We need this variable because we only have one slot for 3 axes.</p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">color_mode_</span></code> stores the color mode for the colorization, we need this variable for the same reason we need <code class="docutils literal notranslate"><span class="pre">filtering_axis_</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">colorCloudDistances</span> <span class="pre">()</span></code> is the member function that actually colorize the point cloud.</p></li>
</ul>
</dd>
</dl>
</div>
<div class="section" id="pclviewer-cpp">
<h3><a class="toc-backref" href="#id6">pclviewer.cpp</a></h3>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="n">PCLViewer</span><span class="o">::</span><span class="n">PCLViewer</span> <span class="p">(</span><span class="n">QWidget</span> <span class="o">*</span><span class="n">parent</span><span class="p">)</span> <span class="o">:</span>
    <span class="n">QMainWindow</span> <span class="p">(</span><span class="n">parent</span><span class="p">),</span>
    <span class="n">ui</span> <span class="p">(</span><span class="k">new</span> <span class="n">Ui</span><span class="o">::</span><span class="n">PCLViewer</span><span class="p">),</span>
    <span class="n">filtering_axis_</span> <span class="p">(</span><span class="mi">1</span><span class="p">),</span>  <span class="c1">// = y</span>
    <span class="n">color_mode_</span> <span class="p">(</span><span class="mi">4</span><span class="p">)</span>  <span class="c1">// = Rainbow</span>
</pre></div>
</div>
<p>We initialize the members of our class to default values (note that theses values should match with the UI buttons ticked)</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="p">{</span>
  <span class="n">ui</span><span class="o">-&gt;</span><span class="n">setupUi</span> <span class="p">(</span><span class="k">this</span><span class="p">);</span>
  <span class="k">this</span><span class="o">-&gt;</span><span class="n">setWindowTitle</span> <span class="p">(</span><span class="s">&quot;PCL viewer&quot;</span><span class="p">);</span>

  <span class="c1">// Setup the cloud pointer</span>
  <span class="n">cloud_</span><span class="p">.</span><span class="n">reset</span> <span class="p">(</span><span class="k">new</span> <span class="n">PointCloudT</span><span class="p">);</span>
  <span class="c1">// The number of points in the cloud</span>
  <span class="n">cloud_</span><span class="o">-&gt;</span><span class="n">resize</span> <span class="p">(</span><span class="mi">500</span><span class="p">);</span>

  <span class="c1">// Fill the cloud with random points</span>
  <span class="k">for</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">cloud_</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">cloud_</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">cloud_</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">cloud_</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>Here we initialize the UI, window title and generate a random point cloud (500 points), note we don’t care about the color for now.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="c1">// Set up the QVTK window</span>
  <span class="n">viewer_</span><span class="p">.</span><span class="n">reset</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="p">(</span><span class="s">&quot;viewer&quot;</span><span class="p">,</span> <span class="nb">false</span><span class="p">));</span>
  <span class="n">viewer_</span><span class="o">-&gt;</span><span class="n">setBackgroundColor</span> <span class="p">(</span><span class="mf">0.1</span><span class="p">,</span> <span class="mf">0.1</span><span class="p">,</span> <span class="mf">0.1</span><span class="p">);</span>
  <span class="n">ui</span><span class="o">-&gt;</span><span class="n">qvtkWidget</span><span class="o">-&gt;</span><span class="n">SetRenderWindow</span> <span class="p">(</span><span class="n">viewer_</span><span class="o">-&gt;</span><span class="n">getRenderWindow</span> <span class="p">());</span>
  <span class="n">viewer_</span><span class="o">-&gt;</span><span class="n">setupInteractor</span> <span class="p">(</span><span class="n">ui</span><span class="o">-&gt;</span><span class="n">qvtkWidget</span><span class="o">-&gt;</span><span class="n">GetInteractor</span> <span class="p">(),</span> <span class="n">ui</span><span class="o">-&gt;</span><span class="n">qvtkWidget</span><span class="o">-&gt;</span><span class="n">GetRenderWindow</span> <span class="p">());</span>
  <span class="n">ui</span><span class="o">-&gt;</span><span class="n">qvtkWidget</span><span class="o">-&gt;</span><span class="n">update</span> <span class="p">();</span>
</pre></div>
</div>
<p>Here we set up the QVTK window.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="c1">// Connect &quot;Load&quot; and &quot;Save&quot; buttons and their functions</span>
  <span class="n">connect</span> <span class="p">(</span><span class="n">ui</span><span class="o">-&gt;</span><span class="n">pushButton_load</span><span class="p">,</span> <span class="n">SIGNAL</span><span class="p">(</span><span class="n">clicked</span> <span class="p">()),</span> <span class="k">this</span><span class="p">,</span> <span class="n">SLOT</span><span class="p">(</span><span class="n">loadFileButtonPressed</span> <span class="p">()));</span>
  <span class="n">connect</span> <span class="p">(</span><span class="n">ui</span><span class="o">-&gt;</span><span class="n">pushButton_save</span><span class="p">,</span> <span class="n">SIGNAL</span><span class="p">(</span><span class="n">clicked</span> <span class="p">()),</span> <span class="k">this</span><span class="p">,</span> <span class="n">SLOT</span><span class="p">(</span><span class="n">saveFileButtonPressed</span> <span class="p">()));</span>

  <span class="c1">// Connect X,Y,Z radio buttons and their functions</span>
  <span class="n">connect</span> <span class="p">(</span><span class="n">ui</span><span class="o">-&gt;</span><span class="n">radioButton_x</span><span class="p">,</span> <span class="n">SIGNAL</span><span class="p">(</span><span class="n">clicked</span> <span class="p">()),</span> <span class="k">this</span><span class="p">,</span> <span class="n">SLOT</span><span class="p">(</span><span class="n">axisChosen</span> <span class="p">()));</span>
  <span class="n">connect</span> <span class="p">(</span><span class="n">ui</span><span class="o">-&gt;</span><span class="n">radioButton_y</span><span class="p">,</span> <span class="n">SIGNAL</span><span class="p">(</span><span class="n">clicked</span> <span class="p">()),</span> <span class="k">this</span><span class="p">,</span> <span class="n">SLOT</span><span class="p">(</span><span class="n">axisChosen</span> <span class="p">()));</span>
  <span class="n">connect</span> <span class="p">(</span><span class="n">ui</span><span class="o">-&gt;</span><span class="n">radioButton_z</span><span class="p">,</span> <span class="n">SIGNAL</span><span class="p">(</span><span class="n">clicked</span> <span class="p">()),</span> <span class="k">this</span><span class="p">,</span> <span class="n">SLOT</span><span class="p">(</span><span class="n">axisChosen</span> <span class="p">()));</span>

  <span class="n">connect</span> <span class="p">(</span><span class="n">ui</span><span class="o">-&gt;</span><span class="n">radioButton_BlueRed</span><span class="p">,</span> <span class="n">SIGNAL</span><span class="p">(</span><span class="n">clicked</span> <span class="p">()),</span> <span class="k">this</span><span class="p">,</span> <span class="n">SLOT</span><span class="p">(</span><span class="n">lookUpTableChosen</span><span class="p">()));</span>
  <span class="n">connect</span> <span class="p">(</span><span class="n">ui</span><span class="o">-&gt;</span><span class="n">radioButton_GreenMagenta</span><span class="p">,</span> <span class="n">SIGNAL</span><span class="p">(</span><span class="n">clicked</span> <span class="p">()),</span> <span class="k">this</span><span class="p">,</span> <span class="n">SLOT</span><span class="p">(</span><span class="n">lookUpTableChosen</span><span class="p">()));</span>
  <span class="n">connect</span> <span class="p">(</span><span class="n">ui</span><span class="o">-&gt;</span><span class="n">radioButton_WhiteRed</span><span class="p">,</span> <span class="n">SIGNAL</span><span class="p">(</span><span class="n">clicked</span> <span class="p">()),</span> <span class="k">this</span><span class="p">,</span> <span class="n">SLOT</span><span class="p">(</span><span class="n">lookUpTableChosen</span><span class="p">()));</span>
  <span class="n">connect</span> <span class="p">(</span><span class="n">ui</span><span class="o">-&gt;</span><span class="n">radioButton_GreyRed</span><span class="p">,</span> <span class="n">SIGNAL</span><span class="p">(</span><span class="n">clicked</span> <span class="p">()),</span> <span class="k">this</span><span class="p">,</span> <span class="n">SLOT</span><span class="p">(</span><span class="n">lookUpTableChosen</span><span class="p">()));</span>
  <span class="n">connect</span> <span class="p">(</span><span class="n">ui</span><span class="o">-&gt;</span><span class="n">radioButton_Rainbow</span><span class="p">,</span> <span class="n">SIGNAL</span><span class="p">(</span><span class="n">clicked</span> <span class="p">()),</span> <span class="k">this</span><span class="p">,</span> <span class="n">SLOT</span><span class="p">(</span><span class="n">lookUpTableChosen</span><span class="p">()));</span>
</pre></div>
</div>
<p>At this point we connect SLOTS and their functions to ensure that each UI elements has an use.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="c1">// Color the randomly generated cloud</span>
  <span class="n">colorCloudDistances</span> <span class="p">();</span>
  <span class="n">viewer_</span><span class="o">-&gt;</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">cloud_</span><span class="p">,</span> <span class="s">&quot;cloud&quot;</span><span class="p">);</span>
  <span class="n">viewer_</span><span class="o">-&gt;</span><span class="n">resetCamera</span> <span class="p">();</span>
  <span class="n">ui</span><span class="o">-&gt;</span><span class="n">qvtkWidget</span><span class="o">-&gt;</span><span class="n">update</span> <span class="p">();</span>
</pre></div>
</div>
<p>We call the coloring function, add the point cloud and refresh the QVTK viewer.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="kt">void</span>
<span class="n">PCLViewer</span><span class="o">::</span><span class="n">loadFileButtonPressed</span> <span class="p">()</span>
<span class="p">{</span>
  <span class="c1">// You might want to change &quot;/home/&quot; if you&#39;re not on an *nix platform</span>
  <span class="n">QString</span> <span class="n">filename</span> <span class="o">=</span> <span class="n">QFileDialog</span><span class="o">::</span><span class="n">getOpenFileName</span> <span class="p">(</span><span class="k">this</span><span class="p">,</span> <span class="n">tr</span> <span class="p">(</span><span class="s">&quot;Open point cloud&quot;</span><span class="p">),</span> <span class="s">&quot;/home/&quot;</span><span class="p">,</span> <span class="n">tr</span> <span class="p">(</span><span class="s">&quot;Point cloud data (*.pcd *.ply)&quot;</span><span class="p">));</span>

  <span class="n">PCL_INFO</span><span class="p">(</span><span class="s">&quot;File chosen: %s</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">filename</span><span class="p">.</span><span class="n">toStdString</span> <span class="p">().</span><span class="n">c_str</span> <span class="p">());</span>
  <span class="n">PointCloudT</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">cloud_tmp</span> <span class="p">(</span><span class="k">new</span> <span class="n">PointCloudT</span><span class="p">);</span>

  <span class="k">if</span> <span class="p">(</span><span class="n">filename</span><span class="p">.</span><span class="n">isEmpty</span> <span class="p">())</span>
    <span class="k">return</span><span class="p">;</span>

  <span class="kt">int</span> <span class="n">return_status</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">filename</span><span class="p">.</span><span class="n">endsWith</span> <span class="p">(</span><span class="s">&quot;.pcd&quot;</span><span class="p">,</span> <span class="n">Qt</span><span class="o">::</span><span class="n">CaseInsensitive</span><span class="p">))</span>
    <span class="n">return_status</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="n">filename</span><span class="p">.</span><span class="n">toStdString</span> <span class="p">(),</span> <span class="o">*</span><span class="n">cloud_tmp</span><span class="p">);</span>
  <span class="k">else</span>
    <span class="n">return_status</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPLYFile</span> <span class="p">(</span><span class="n">filename</span><span class="p">.</span><span class="n">toStdString</span> <span class="p">(),</span> <span class="o">*</span><span class="n">cloud_tmp</span><span class="p">);</span>

  <span class="k">if</span> <span class="p">(</span><span class="n">return_status</span> <span class="o">!=</span> <span class="mi">0</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">PCL_ERROR</span><span class="p">(</span><span class="s">&quot;Error reading point cloud %s</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">filename</span><span class="p">.</span><span class="n">toStdString</span> <span class="p">().</span><span class="n">c_str</span> <span class="p">());</span>
    <span class="k">return</span><span class="p">;</span>
  <span class="p">}</span>

  <span class="c1">// If point cloud contains NaN values, remove them before updating the visualizer point cloud</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">cloud_tmp</span><span class="o">-&gt;</span><span class="n">is_dense</span><span class="p">)</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">copyPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_tmp</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud_</span><span class="p">);</span>
  <span class="k">else</span>
  <span class="p">{</span>
    <span class="n">PCL_WARN</span><span class="p">(</span><span class="s">&quot;Cloud is not dense! Non finite points will be removed</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
    <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">vec</span><span class="p">;</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">removeNaNFromPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_tmp</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud_</span><span class="p">,</span> <span class="n">vec</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="n">colorCloudDistances</span> <span class="p">();</span>
  <span class="n">viewer_</span><span class="o">-&gt;</span><span class="n">updatePointCloud</span> <span class="p">(</span><span class="n">cloud_</span><span class="p">,</span> <span class="s">&quot;cloud&quot;</span><span class="p">);</span>
  <span class="n">viewer_</span><span class="o">-&gt;</span><span class="n">resetCamera</span> <span class="p">();</span>
  <span class="n">ui</span><span class="o">-&gt;</span><span class="n">qvtkWidget</span><span class="o">-&gt;</span><span class="n">update</span> <span class="p">();</span>
<span class="p">}</span>
</pre></div>
</div>
<p>This functions deals with opening files, it supports both <code class="docutils literal notranslate"><span class="pre">pcd</span></code> and <code class="docutils literal notranslate"><span class="pre">ply</span></code> files.
The LUT computing will only work if the point cloud is dense (only finite values) so we remove NaN values from the point cloud if needed.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="kt">void</span>
<span class="n">PCLViewer</span><span class="o">::</span><span class="n">saveFileButtonPressed</span> <span class="p">()</span>
<span class="p">{</span>
  <span class="c1">// You might want to change &quot;/home/&quot; if you&#39;re not on an *nix platform</span>
  <span class="n">QString</span> <span class="n">filename</span> <span class="o">=</span> <span class="n">QFileDialog</span><span class="o">::</span><span class="n">getSaveFileName</span><span class="p">(</span><span class="k">this</span><span class="p">,</span> <span class="n">tr</span> <span class="p">(</span><span class="s">&quot;Open point cloud&quot;</span><span class="p">),</span> <span class="s">&quot;/home/&quot;</span><span class="p">,</span> <span class="n">tr</span> <span class="p">(</span><span class="s">&quot;Point cloud data (*.pcd *.ply)&quot;</span><span class="p">));</span>

  <span class="n">PCL_INFO</span><span class="p">(</span><span class="s">&quot;File chosen: %s</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">filename</span><span class="p">.</span><span class="n">toStdString</span> <span class="p">().</span><span class="n">c_str</span> <span class="p">());</span>

  <span class="k">if</span> <span class="p">(</span><span class="n">filename</span><span class="p">.</span><span class="n">isEmpty</span> <span class="p">())</span>
    <span class="k">return</span><span class="p">;</span>

  <span class="kt">int</span> <span class="n">return_status</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">filename</span><span class="p">.</span><span class="n">endsWith</span> <span class="p">(</span><span class="s">&quot;.pcd&quot;</span><span class="p">,</span> <span class="n">Qt</span><span class="o">::</span><span class="n">CaseInsensitive</span><span class="p">))</span>
    <span class="n">return_status</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">savePCDFileBinary</span> <span class="p">(</span><span class="n">filename</span><span class="p">.</span><span class="n">toStdString</span> <span class="p">(),</span> <span class="o">*</span><span class="n">cloud_</span><span class="p">);</span>
  <span class="k">else</span> <span class="nf">if</span> <span class="p">(</span><span class="n">filename</span><span class="p">.</span><span class="n">endsWith</span> <span class="p">(</span><span class="s">&quot;.ply&quot;</span><span class="p">,</span> <span class="n">Qt</span><span class="o">::</span><span class="n">CaseInsensitive</span><span class="p">))</span>
    <span class="n">return_status</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">savePLYFileBinary</span> <span class="p">(</span><span class="n">filename</span><span class="p">.</span><span class="n">toStdString</span> <span class="p">(),</span> <span class="o">*</span><span class="n">cloud_</span><span class="p">);</span>
  <span class="k">else</span>
  <span class="p">{</span>
    <span class="n">filename</span><span class="p">.</span><span class="n">append</span><span class="p">(</span><span class="s">&quot;.ply&quot;</span><span class="p">);</span>
    <span class="n">return_status</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">savePLYFileBinary</span> <span class="p">(</span><span class="n">filename</span><span class="p">.</span><span class="n">toStdString</span> <span class="p">(),</span> <span class="o">*</span><span class="n">cloud_</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="k">if</span> <span class="p">(</span><span class="n">return_status</span> <span class="o">!=</span> <span class="mi">0</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">PCL_ERROR</span><span class="p">(</span><span class="s">&quot;Error writing point cloud %s</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">filename</span><span class="p">.</span><span class="n">toStdString</span> <span class="p">().</span><span class="n">c_str</span> <span class="p">());</span>
    <span class="k">return</span><span class="p">;</span>
  <span class="p">}</span>
<span class="p">}</span>
</pre></div>
</div>
<div class="line-block">
<div class="line">This functions deals with saving the displayed file, it supports both <code class="docutils literal notranslate"><span class="pre">pcd</span></code> and <code class="docutils literal notranslate"><span class="pre">ply</span></code> files.</div>
<div class="line">As said before, if the user doesn’t append an extension to the file name, <code class="docutils literal notranslate"><span class="pre">ply</span></code> will be automatically added.</div>
</div>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="kt">void</span>
<span class="n">PCLViewer</span><span class="o">::</span><span class="n">axisChosen</span> <span class="p">()</span>
<span class="p">{</span>
  <span class="c1">// Only 1 of the button can be checked at the time (mutual exclusivity) in a group of radio buttons</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">ui</span><span class="o">-&gt;</span><span class="n">radioButton_x</span><span class="o">-&gt;</span><span class="n">isChecked</span> <span class="p">())</span>
  <span class="p">{</span>
    <span class="n">PCL_INFO</span><span class="p">(</span><span class="s">&quot;x filtering chosen</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
    <span class="n">filtering_axis_</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="p">}</span>
  <span class="k">else</span> <span class="k">if</span> <span class="p">(</span><span class="n">ui</span><span class="o">-&gt;</span><span class="n">radioButton_y</span><span class="o">-&gt;</span><span class="n">isChecked</span> <span class="p">())</span>
  <span class="p">{</span>
    <span class="n">PCL_INFO</span><span class="p">(</span><span class="s">&quot;y filtering chosen</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
    <span class="n">filtering_axis_</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
  <span class="p">}</span>
  <span class="k">else</span>
  <span class="p">{</span>
    <span class="n">PCL_INFO</span><span class="p">(</span><span class="s">&quot;z filtering chosen</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
    <span class="n">filtering_axis_</span> <span class="o">=</span> <span class="mi">2</span><span class="p">;</span>
  <span class="p">}</span>

  <span class="n">colorCloudDistances</span> <span class="p">();</span>
  <span class="n">viewer_</span><span class="o">-&gt;</span><span class="n">updatePointCloud</span> <span class="p">(</span><span class="n">cloud_</span><span class="p">,</span> <span class="s">&quot;cloud&quot;</span><span class="p">);</span>
  <span class="n">ui</span><span class="o">-&gt;</span><span class="n">qvtkWidget</span><span class="o">-&gt;</span><span class="n">update</span> <span class="p">();</span>
<span class="p">}</span>
</pre></div>
</div>
<p>This function is called whenever one of the three radio buttons X,Y,Z are clicked, it determines which radio button is clicked and changes
the <code class="docutils literal notranslate"><span class="pre">filtering_axis_</span></code> member accordingly.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="kt">void</span>
<span class="n">PCLViewer</span><span class="o">::</span><span class="n">lookUpTableChosen</span> <span class="p">()</span>
<span class="p">{</span>
  <span class="c1">// Only 1 of the button can be checked at the time (mutual exclusivity) in a group of radio buttons</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">ui</span><span class="o">-&gt;</span><span class="n">radioButton_BlueRed</span><span class="o">-&gt;</span><span class="n">isChecked</span> <span class="p">())</span>
  <span class="p">{</span>
    <span class="n">PCL_INFO</span><span class="p">(</span><span class="s">&quot;Blue -&gt; Red LUT chosen</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
    <span class="n">color_mode_</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="p">}</span>
  <span class="k">else</span> <span class="k">if</span> <span class="p">(</span><span class="n">ui</span><span class="o">-&gt;</span><span class="n">radioButton_GreenMagenta</span><span class="o">-&gt;</span><span class="n">isChecked</span> <span class="p">())</span>
  <span class="p">{</span>
    <span class="n">PCL_INFO</span><span class="p">(</span><span class="s">&quot;Green -&gt; Magenta LUT chosen</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
    <span class="n">color_mode_</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
  <span class="p">}</span>
  <span class="k">else</span> <span class="k">if</span> <span class="p">(</span><span class="n">ui</span><span class="o">-&gt;</span><span class="n">radioButton_WhiteRed</span><span class="o">-&gt;</span><span class="n">isChecked</span> <span class="p">())</span>
  <span class="p">{</span>
    <span class="n">PCL_INFO</span><span class="p">(</span><span class="s">&quot;White -&gt; Red LUT chosen</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
    <span class="n">color_mode_</span> <span class="o">=</span> <span class="mi">2</span><span class="p">;</span>
  <span class="p">}</span>
  <span class="k">else</span> <span class="k">if</span> <span class="p">(</span><span class="n">ui</span><span class="o">-&gt;</span><span class="n">radioButton_GreyRed</span><span class="o">-&gt;</span><span class="n">isChecked</span> <span class="p">())</span>
  <span class="p">{</span>
    <span class="n">PCL_INFO</span><span class="p">(</span><span class="s">&quot;Grey / Red LUT chosen</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
    <span class="n">color_mode_</span> <span class="o">=</span> <span class="mi">3</span><span class="p">;</span>
  <span class="p">}</span>
  <span class="k">else</span>
  <span class="p">{</span>
    <span class="n">PCL_INFO</span><span class="p">(</span><span class="s">&quot;Rainbow LUT chosen</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
    <span class="n">color_mode_</span> <span class="o">=</span> <span class="mi">4</span><span class="p">;</span>
  <span class="p">}</span>

  <span class="n">colorCloudDistances</span> <span class="p">();</span>
  <span class="n">viewer_</span><span class="o">-&gt;</span><span class="n">updatePointCloud</span> <span class="p">(</span><span class="n">cloud_</span><span class="p">,</span> <span class="s">&quot;cloud&quot;</span><span class="p">);</span>
  <span class="n">ui</span><span class="o">-&gt;</span><span class="n">qvtkWidget</span><span class="o">-&gt;</span><span class="n">update</span> <span class="p">();</span>
<span class="p">}</span>
</pre></div>
</div>
<p>This function is called whenever one of the radio buttons in the color list is clicked, the <code class="docutils literal notranslate"><span class="pre">color_mode_</span></code> member is modified accordingly.
We also call the coloring function and update the cloud / QVTK widget.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="kt">void</span>
<span class="n">PCLViewer</span><span class="o">::</span><span class="n">colorCloudDistances</span> <span class="p">()</span>
<span class="p">{</span>
  <span class="c1">// Find the minimum and maximum values along the selected axis</span>
  <span class="kt">double</span> <span class="n">min</span><span class="p">,</span> <span class="n">max</span><span class="p">;</span>
  <span class="c1">// Set an initial value</span>
  <span class="k">switch</span> <span class="p">(</span><span class="n">filtering_axis_</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="k">case</span> <span class="mi">0</span><span class="o">:</span>  <span class="c1">// x</span>
      <span class="n">min</span> <span class="o">=</span> <span class="n">cloud_</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="mi">0</span><span class="p">].</span><span class="n">x</span><span class="p">;</span>
      <span class="n">max</span> <span class="o">=</span> <span class="n">cloud_</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="mi">0</span><span class="p">].</span><span class="n">x</span><span class="p">;</span>
      <span class="k">break</span><span class="p">;</span>
    <span class="k">case</span> <span class="mi">1</span><span class="o">:</span>  <span class="c1">// y</span>
      <span class="n">min</span> <span class="o">=</span> <span class="n">cloud_</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="mi">0</span><span class="p">].</span><span class="n">y</span><span class="p">;</span>
      <span class="n">max</span> <span class="o">=</span> <span class="n">cloud_</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="mi">0</span><span class="p">].</span><span class="n">y</span><span class="p">;</span>
      <span class="k">break</span><span class="p">;</span>
    <span class="k">default</span><span class="o">:</span>  <span class="c1">// z</span>
      <span class="n">min</span> <span class="o">=</span> <span class="n">cloud_</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="mi">0</span><span class="p">].</span><span class="n">z</span><span class="p">;</span>
      <span class="n">max</span> <span class="o">=</span> <span class="n">cloud_</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="mi">0</span><span class="p">].</span><span class="n">z</span><span class="p">;</span>
      <span class="k">break</span><span class="p">;</span>
  <span class="p">}</span>
</pre></div>
</div>
<div class="line-block">
<div class="line">This is the core function of the application. We are going to color the cloud following a color scheme.</div>
</div>
<p>The point cloud is going to be colored following one direction, we first need to know where it starts and where it ends
(the minimum &amp; maximum point values along the chosen axis). We first set the initial minimal value to the first point value
(this is safe because we removed NaN points from the point clouds). The switch case allows us to deal with the 3 different axes.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="c1">// Search for the minimum/maximum</span>
  <span class="k">for</span> <span class="p">(</span><span class="n">PointCloudT</span><span class="o">::</span><span class="n">iterator</span> <span class="n">cloud_it</span> <span class="o">=</span> <span class="n">cloud_</span><span class="o">-&gt;</span><span class="n">begin</span> <span class="p">();</span> <span class="n">cloud_it</span> <span class="o">!=</span> <span class="n">cloud_</span><span class="o">-&gt;</span><span class="n">end</span> <span class="p">();</span> <span class="o">++</span><span class="n">cloud_it</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="k">switch</span> <span class="p">(</span><span class="n">filtering_axis_</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="k">case</span> <span class="mi">0</span><span class="o">:</span>  <span class="c1">// x</span>
        <span class="k">if</span> <span class="p">(</span><span class="n">min</span> <span class="o">&gt;</span> <span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">x</span><span class="p">)</span>
          <span class="n">min</span> <span class="o">=</span> <span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">x</span><span class="p">;</span>

        <span class="k">if</span> <span class="p">(</span><span class="n">max</span> <span class="o">&lt;</span> <span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">x</span><span class="p">)</span>
          <span class="n">max</span> <span class="o">=</span> <span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">x</span><span class="p">;</span>
        <span class="k">break</span><span class="p">;</span>
      <span class="k">case</span> <span class="mi">1</span><span class="o">:</span>  <span class="c1">// y</span>
        <span class="k">if</span> <span class="p">(</span><span class="n">min</span> <span class="o">&gt;</span> <span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">y</span><span class="p">)</span>
          <span class="n">min</span> <span class="o">=</span> <span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">y</span><span class="p">;</span>

        <span class="k">if</span> <span class="p">(</span><span class="n">max</span> <span class="o">&lt;</span> <span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">y</span><span class="p">)</span>
          <span class="n">max</span> <span class="o">=</span> <span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">y</span><span class="p">;</span>
        <span class="k">break</span><span class="p">;</span>
      <span class="k">default</span><span class="o">:</span>  <span class="c1">// z</span>
        <span class="k">if</span> <span class="p">(</span><span class="n">min</span> <span class="o">&gt;</span> <span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">z</span><span class="p">)</span>
          <span class="n">min</span> <span class="o">=</span> <span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">z</span><span class="p">;</span>

        <span class="k">if</span> <span class="p">(</span><span class="n">max</span> <span class="o">&lt;</span> <span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">z</span><span class="p">)</span>
          <span class="n">max</span> <span class="o">=</span> <span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">z</span><span class="p">;</span>
        <span class="k">break</span><span class="p">;</span>
    <span class="p">}</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>We then loop through the whole cloud to find the minimum and maximum values.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="c1">// Compute LUT scaling to fit the full histogram spectrum</span>
  <span class="kt">double</span> <span class="n">lut_scale</span> <span class="o">=</span> <span class="mf">255.0</span> <span class="o">/</span> <span class="p">(</span><span class="n">max</span> <span class="o">-</span> <span class="n">min</span><span class="p">);</span>  <span class="c1">// max is 255, min is 0</span>

  <span class="k">if</span> <span class="p">(</span><span class="n">min</span> <span class="o">==</span> <span class="n">max</span><span class="p">)</span>  <span class="c1">// In case the cloud is flat on the chosen direction (x,y or z)</span>
    <span class="n">lut_scale</span> <span class="o">=</span> <span class="mf">1.0</span><span class="p">;</span>  <span class="c1">// Avoid rounding error in boost</span>
</pre></div>
</div>
<p>Here we compute the scaling, RGB values are coded from 0 to 255 (as integers), we need to scale our distances so that the
minimum distance equals 0 (in RGB scale) and the maximum distance 255 (in RGB scale).
The <code class="docutils literal notranslate"><span class="pre">if</span></code> condition is here in case of a perfectly flat point cloud and avoids exceptions thrown by boost.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="k">for</span> <span class="p">(</span><span class="n">PointCloudT</span><span class="o">::</span><span class="n">iterator</span> <span class="n">cloud_it</span> <span class="o">=</span> <span class="n">cloud_</span><span class="o">-&gt;</span><span class="n">begin</span> <span class="p">();</span> <span class="n">cloud_it</span> <span class="o">!=</span> <span class="n">cloud_</span><span class="o">-&gt;</span><span class="n">end</span> <span class="p">();</span> <span class="o">++</span><span class="n">cloud_it</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="kt">int</span> <span class="n">value</span><span class="p">;</span>
    <span class="k">switch</span> <span class="p">(</span><span class="n">filtering_axis_</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="k">case</span> <span class="mi">0</span><span class="o">:</span>  <span class="c1">// x</span>
        <span class="n">value</span> <span class="o">=</span> <span class="n">std</span><span class="o">::</span><span class="n">lround</span> <span class="p">(</span> <span class="p">(</span><span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">x</span> <span class="o">-</span> <span class="n">min</span><span class="p">)</span> <span class="o">*</span> <span class="n">lut_scale</span><span class="p">);</span>  <span class="c1">// Round the number to the closest integer</span>
        <span class="k">break</span><span class="p">;</span>
      <span class="k">case</span> <span class="mi">1</span><span class="o">:</span>  <span class="c1">// y</span>
        <span class="n">value</span> <span class="o">=</span> <span class="n">std</span><span class="o">::</span><span class="n">lround</span> <span class="p">(</span> <span class="p">(</span><span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">y</span> <span class="o">-</span> <span class="n">min</span><span class="p">)</span> <span class="o">*</span> <span class="n">lut_scale</span><span class="p">);</span>
        <span class="k">break</span><span class="p">;</span>
      <span class="k">default</span><span class="o">:</span>  <span class="c1">// z</span>
        <span class="n">value</span> <span class="o">=</span> <span class="n">std</span><span class="o">::</span><span class="n">lround</span> <span class="p">(</span> <span class="p">(</span><span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">z</span> <span class="o">-</span> <span class="n">min</span><span class="p">)</span> <span class="o">*</span> <span class="n">lut_scale</span><span class="p">);</span>
        <span class="k">break</span><span class="p">;</span>
    <span class="p">}</span>
</pre></div>
</div>
<p>We have computed how much we need to scale the distances to fit the RGB scale, we first need to round the <code class="docutils literal notranslate"><span class="pre">double</span></code> values to the closest <code class="docutils literal notranslate"><span class="pre">integer</span></code>
because colors are coded as integers.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>    <span class="c1">// Apply color to the cloud</span>
    <span class="k">switch</span> <span class="p">(</span><span class="n">color_mode_</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="k">case</span> <span class="mi">0</span><span class="o">:</span>
        <span class="c1">// Blue (= min) -&gt; Red (= max)</span>
        <span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">r</span> <span class="o">=</span> <span class="n">value</span><span class="p">;</span>
        <span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">g</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
        <span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">b</span> <span class="o">=</span> <span class="mi">255</span> <span class="o">-</span> <span class="n">value</span><span class="p">;</span>
        <span class="k">break</span><span class="p">;</span>
</pre></div>
</div>
<p>This is where we apply the color level we have computed to the point cloud R,G,B values.
You can do whatever you want here, the simplest option is to apply the 3 channels (R,G,B) to the <code class="docutils literal notranslate"><span class="pre">value</span></code> computed, this means that the
minimum distance will translate into dark (black = 0,0,0) points and maximal distances into white (255,255,255) points.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>      <span class="k">case</span> <span class="mi">1</span><span class="o">:</span>
        <span class="c1">// Green (= min) -&gt; Magenta (= max)</span>
        <span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">r</span> <span class="o">=</span> <span class="n">value</span><span class="p">;</span>
        <span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">g</span> <span class="o">=</span> <span class="mi">255</span> <span class="o">-</span> <span class="n">value</span><span class="p">;</span>
        <span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">b</span> <span class="o">=</span> <span class="n">value</span><span class="p">;</span>
        <span class="k">break</span><span class="p">;</span>
      <span class="k">case</span> <span class="mi">2</span><span class="o">:</span>
        <span class="c1">// White (= min) -&gt; Red (= max)</span>
        <span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">r</span> <span class="o">=</span> <span class="mi">255</span><span class="p">;</span>
        <span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">g</span> <span class="o">=</span> <span class="mi">255</span> <span class="o">-</span> <span class="n">value</span><span class="p">;</span>
        <span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">b</span> <span class="o">=</span> <span class="mi">255</span> <span class="o">-</span> <span class="n">value</span><span class="p">;</span>
        <span class="k">break</span><span class="p">;</span>
      <span class="k">case</span> <span class="mi">3</span><span class="o">:</span>
        <span class="c1">// Grey (&lt; 128) / Red (&gt; 128)</span>
        <span class="k">if</span> <span class="p">(</span><span class="n">value</span> <span class="o">&gt;</span> <span class="mi">128</span><span class="p">)</span>
        <span class="p">{</span>
          <span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">r</span> <span class="o">=</span> <span class="mi">255</span><span class="p">;</span>
          <span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">g</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
          <span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">b</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
        <span class="p">}</span>
        <span class="k">else</span>
        <span class="p">{</span>
          <span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">r</span> <span class="o">=</span> <span class="mi">128</span><span class="p">;</span>
          <span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">g</span> <span class="o">=</span> <span class="mi">128</span><span class="p">;</span>
          <span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">b</span> <span class="o">=</span> <span class="mi">128</span><span class="p">;</span>
        <span class="p">}</span>
        <span class="k">break</span><span class="p">;</span>
      <span class="k">default</span><span class="o">:</span>
        <span class="c1">// Blue -&gt; Green -&gt; Red (~ rainbow)</span>
        <span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">r</span> <span class="o">=</span> <span class="n">value</span> <span class="o">&gt;</span> <span class="mi">128</span> <span class="o">?</span> <span class="p">(</span><span class="n">value</span> <span class="o">-</span> <span class="mi">128</span><span class="p">)</span> <span class="o">*</span> <span class="mi">2</span> <span class="o">:</span> <span class="mi">0</span><span class="p">;</span>  <span class="c1">// r[128] = 0, r[255] = 255</span>
        <span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">g</span> <span class="o">=</span> <span class="n">value</span> <span class="o">&lt;</span> <span class="mi">128</span> <span class="o">?</span> <span class="mi">2</span> <span class="o">*</span> <span class="nl">value</span> <span class="p">:</span> <span class="mi">255</span> <span class="o">-</span> <span class="p">(</span> <span class="p">(</span><span class="n">value</span> <span class="o">-</span> <span class="mi">128</span><span class="p">)</span> <span class="o">*</span> <span class="mi">2</span><span class="p">);</span>  <span class="c1">// g[0] = 0, g[128] = 255, g[255] = 0</span>
        <span class="n">cloud_it</span><span class="o">-&gt;</span><span class="n">b</span> <span class="o">=</span> <span class="n">value</span> <span class="o">&lt;</span> <span class="mi">128</span> <span class="o">?</span> <span class="mi">255</span> <span class="o">-</span> <span class="p">(</span><span class="mi">2</span> <span class="o">*</span> <span class="n">value</span><span class="p">)</span> <span class="o">:</span> <span class="mi">0</span><span class="p">;</span>  <span class="c1">// b[0] = 255, b[128] = 0</span>
    <span class="p">}</span>
  <span class="p">}</span>
<span class="p">}</span>
</pre></div>
</div>
<p>These are examples of coloring schemes, if you are wondering how it works, simply plot the computed values into a spreadsheet software.</p>
</div>
</div>
<div class="section" id="compiling-and-running">
<h2><a class="toc-backref" href="#id7">Compiling and running</a></h2>
<dl class="simple">
<dt>There are two options here :</dt><dd><ul class="simple">
<li><p>You have configured the Qt project (see <a class="reference external" href="http://www.pointclouds.org/documentation/tutorials/qt_visualizer.php#qt-configuration">Qt visualizer tutorial</a>) and you can compile/run just by clicking on the bottom left “Play” button.</p></li>
<li><p>You didn’t configure the Qt project; just go to the build folder an run <code class="docutils literal notranslate"><span class="pre">cmake</span> <span class="pre">../src</span> <span class="pre">&amp;&amp;</span> <span class="pre">make</span> <span class="pre">-j2</span> <span class="pre">&amp;&amp;</span> <span class="pre">./pcl_visualizer</span></code></p></li>
</ul>
</dd>
</dl>
<p>Note that if you don’t specify a extension when saving the file, the file will be saved as a binary PLY file.</p>
<a class="reference internal image-reference" href="_images/colorize_cloud.gif"><img alt="_images/colorize_cloud.gif" src="_images/colorize_cloud.gif" style="height: 526px;" /></a>
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