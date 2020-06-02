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
    
    <title>PCLPlotter</title>
    
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
            
  <div class="section" id="pclplotter">
<span id="pcl-plotter"></span><h1>PCLPlotter</h1>
<p>PCLPlotter provides a very straightforward and easy interface for plotting graphs. One can visualize all sort of important plots -
from polynomial functions to histograms - inside the library without going to any other softwares (like MATLAB).
Please go through the <a class="reference external" href="http://docs.pointclouds.org/trunk/a01175.html">documentation</a> when some specific concepts are introduced in this tutorial to know the exact method signatures.</p>
<p>The code for the visualization of a plot are usually as simple as the following snippet.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="cp">#include&lt;vector&gt;</span>
<span class="cp">#include&lt;iostream&gt;</span>
<span class="cp">#include&lt;utility&gt;</span>

<span class="cp">#include&lt;pcl/visualization/pcl_plotter.h&gt;</span>
<span class="c1">//...</span>

<span class="k">using</span> <span class="k">namespace</span> <span class="n">std</span><span class="p">;</span>

<span class="kt">int</span>
<span class="nf">main</span> <span class="p">()</span>
<span class="p">{</span>
  <span class="c1">//defining a plotter</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLPlotter</span> <span class="o">*</span> <span class="n">plotter</span> <span class="o">=</span> <span class="k">new</span> <span class="n">PCLPlotter</span> <span class="p">();</span>

  <span class="c1">//defining the polynomial function, y = x^2. Index of x^2 is 1, rest is 0</span>
  <span class="n">vector</span><span class="o">&lt;</span><span class="kt">double</span><span class="o">&gt;</span> <span class="n">func1</span> <span class="p">(</span><span class="mi">3</span><span class="p">,</span><span class="mi">0</span><span class="p">);</span>
  <span class="n">func1</span><span class="p">[</span><span class="mi">2</span><span class="p">]</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>

  <span class="c1">//adding the polynomial func1 to the plotter with [-10, 10] as the range in X axis and &quot;y = x^2&quot; as title</span>
  <span class="n">plotter</span><span class="o">-&gt;</span><span class="n">addPlotData</span> <span class="p">(</span><span class="n">func1</span><span class="p">,</span> <span class="o">-</span><span class="mi">10</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="s">&quot;y = x^2&quot;</span><span class="p">);</span>

  <span class="c1">//display the plot, DONE!</span>
  <span class="n">plotter</span><span class="o">-&gt;</span><span class="n">plot</span> <span class="p">();</span>

  <span class="k">return</span> <span class="mi">0</span><span class="p">;</span>
<span class="p">}</span>
</pre></div>
</div>
<p>If this program is compiled and run, you will get the following output</p>
<a class="reference internal image-reference" href="_images/pcl_plotter_x2.png"><img alt="_images/pcl_plotter_x2.png" src="_images/pcl_plotter_x2.png" style="width: 640px;" /></a>
<div class="section" id="basic-code-structure">
<h2>Basic code structure</h2>
<p>The following snippet shows the basic structure of code for using PCLPlotter</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="c1">//1. define a plotter. Change the colorscheme if you want some different colorscheme in auto-coloring.</span>
<span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLPlotter</span> <span class="o">*</span><span class="n">plotter</span> <span class="o">=</span> <span class="k">new</span> <span class="n">PCLPlotter</span> <span class="p">(</span><span class="s">&quot;My Plotter&quot;</span><span class="p">);</span>

<span class="p">...</span>
<span class="c1">//2. add data to be plotted using addPlotData* () functions</span>
<span class="n">plotter</span><span class="o">-&gt;</span><span class="n">addPlotData</span><span class="o">*</span> <span class="p">();</span>

<span class="p">...</span>
<span class="c1">//3. add some properties if required</span>
<span class="n">plotter</span><span class="o">-&gt;</span><span class="n">setWindowSize</span> <span class="p">(</span><span class="mi">900</span><span class="p">,</span> <span class="mi">600</span><span class="p">);</span>
<span class="n">plotter</span><span class="o">-&gt;</span><span class="n">setYTitle</span> <span class="p">(</span><span class="s">&quot;this is my own function&quot;</span><span class="p">);</span>

<span class="p">...</span>
<span class="c1">//4. display the plot</span>
<span class="n">plotter</span><span class="o">-&gt;</span><span class="n">plot</span> <span class="p">()</span>
</pre></div>
</div>
<p>All the subsequent sections will elaborate the above concept in detail.</p>
</div>
</div>
<div class="section" id="auto-coloring">
<h1>Auto-coloring</h1>
<p>You have the choice to add your own color to the plot in addPlotData*() functions. But if left empty, the plotter will auto-color depending upon a color-scheme.
The default color-scheme is <tt class="docutils literal"><span class="pre">vtkColorSeries::SPECTRUM</span></tt> which contains 7 different (normal) hues over the entire spectrum. The other values are <tt class="docutils literal"><span class="pre">vtkColorSeries::WARM</span></tt>, <tt class="docutils literal"><span class="pre">vtkColorSeries::COOL</span></tt>, <tt class="docutils literal"><span class="pre">vtkColorSeries::BLUES</span></tt>, <tt class="docutils literal"><span class="pre">vtkColorSeries::WILD_FLOWER</span></tt>, <tt class="docutils literal"><span class="pre">vtkColorSeries::CITRUS</span></tt>.
You can change the colorscheme by  <em>setColorScheme ()</em> function. To reflect the effect of the color-scheme to all the plots call this function before calling any <em>addPlotData*()</em> functions.</p>
</div>
<div class="section" id="different-types-of-plot-input">
<h1>Different types of plot input</h1>
<p>Have a look at the <em>addPlotData()</em> functions in the documentation for their detailed signatures. The prototypes pretty much tell about their functionalities. The following subsections contains some of the important input method of the plot.</p>
<div class="section" id="point-correspondences">
<h2>Point-Correspondences</h2>
<p>This the most fundamental way of providing input. Provide the point correspondences, that is (x,y) coordinates, for the plot using a vector&lt;pair&gt; in <em>addPlotData</em></p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="n">vector</span><span class="o">&lt;</span><span class="n">pair</span><span class="o">&lt;</span><span class="kt">double</span><span class="p">,</span> <span class="kt">double</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="n">data</span><span class="p">;</span>
<span class="n">populateData</span> <span class="p">(</span><span class="n">data</span><span class="p">);</span>
<span class="n">plotter</span><span class="o">-&gt;</span><span class="n">addPlotData</span> <span class="p">(</span><span class="n">data</span><span class="p">,</span><span class="s">&quot;cos&quot;</span><span class="p">);</span>
<span class="p">...</span>
</pre></div>
</div>
<p>The other ways of input for point correspondences are two arrays of same length denoting the X and Y values of the correspondences.</p>
</div>
<div class="section" id="table">
<h2>Table</h2>
<p>This is same as the previous one except the fact that the user stores the correspondences in a text file in the form of an space delimited table. This forms a substitute for the plotting using MS Excel. A very simple executable (without decoration) which performs the functionalities of MS Excel Plotter will be the following.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="cp">#include&lt;pcl/visualization/pcl_plotter.h&gt;</span>

<span class="kt">int</span>
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span> <span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLPlotter</span> <span class="o">*</span> <span class="n">plotter</span> <span class="o">=</span> <span class="k">new</span> <span class="n">PCLPlotter</span> <span class="p">();</span>
  <span class="n">plotter</span><span class="o">-&gt;</span><span class="n">addPlotData</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">1</span><span class="p">]);</span>
  <span class="n">plotter</span><span class="o">-&gt;</span><span class="n">plot</span> <span class="p">();</span>

  <span class="k">return</span> <span class="mi">0</span><span class="p">;</span>
<span class="p">}</span>
</pre></div>
</div>
</div>
<div class="section" id="polynomial-and-rational-functions">
<h2>Polynomial and Rational Functions</h2>
<p>Polynomial are defined in terms of vector of coefficients and Rational functions are defined in terms of pair of polynomial (pair of numerator and denominator) . See the definition in the documentation. The following snippet plots the function y = 1/x</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="n">vector</span><span class="o">&lt;</span><span class="kt">double</span><span class="o">&gt;</span> <span class="n">func1</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span><span class="mi">0</span><span class="p">);</span>
<span class="n">func1</span><span class="p">[</span><span class="mi">0</span><span class="p">]</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
<span class="n">vector</span><span class="o">&lt;</span><span class="kt">double</span><span class="o">&gt;</span> <span class="n">func2</span> <span class="p">(</span><span class="mi">2</span><span class="p">,</span><span class="mi">0</span><span class="p">);</span>
<span class="n">func1</span><span class="p">[</span><span class="mi">1</span><span class="p">]</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>

<span class="n">plotter</span><span class="o">-&gt;</span><span class="n">addPlotData</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="n">make_pair</span> <span class="p">(</span><span class="n">func1</span><span class="p">,</span> <span class="n">func2</span><span class="p">),</span><span class="o">-</span><span class="mi">10</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="s">&quot;y = 1/x&quot;</span><span class="p">);</span>
<span class="p">...</span>
</pre></div>
</div>
</div>
<div class="section" id="a-custom-explicit-function">
<h2>A custom explicit function</h2>
<p>User can specify a custom function, <em>f</em> depicting the relation: <em>Y = f(X)</em> in the form of a callback</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="kt">double</span>
<span class="n">identity</span> <span class="p">(</span><span class="kt">double</span> <span class="n">val</span><span class="p">)</span>
<span class="p">{</span>
  <span class="k">return</span> <span class="n">val</span><span class="p">;</span>
<span class="p">}</span>
<span class="p">...</span>

<span class="p">...</span>
<span class="n">plotter</span><span class="o">-&gt;</span><span class="n">addPlotData</span> <span class="p">(</span><span class="n">identity</span><span class="p">,</span><span class="o">-</span><span class="mi">10</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span><span class="s">&quot;identity&quot;</span><span class="p">);</span>
<span class="p">...</span>
</pre></div>
</div>
</div>
</div>
<div class="section" id="adding-other-properties-and-decorations">
<h1>Adding other properties and decorations</h1>
<p>One can add other properties of the plot like <em>title</em>, <em>legends</em>, <em>background colours</em> etc. You can call these functions at any time before any display (<em>plot()/spin*()</em>) function call.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="n">plotter</span><span class="o">-&gt;</span><span class="n">setTitle</span> <span class="p">(</span><span class="s">&quot;My plot&quot;</span><span class="p">);</span> <span class="c1">//global title</span>
<span class="n">plotter</span><span class="o">-&gt;</span><span class="n">setXTitle</span> <span class="p">(</span><span class="s">&quot;degrees&quot;</span><span class="p">);</span>
<span class="n">plotter</span><span class="o">-&gt;</span><span class="n">setYTitle</span> <span class="p">(</span><span class="s">&quot;cos&quot;</span><span class="p">);</span>
<span class="n">plotter</span><span class="o">-&gt;</span><span class="n">setShowLegend</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span> <span class="c1">//show legends</span>
<span class="p">...</span>
<span class="n">plotter</span><span class="o">-&gt;</span><span class="n">plot</span> <span class="p">();</span>
<span class="p">...</span>
</pre></div>
</div>
</div>
<div class="section" id="other-functionalities">
<h1>Other Functionalities</h1>
<p>PCLPlotter provides few other important functionalities other than plotting given a well defined plots and correspondences. These includes a histogram plotting functions and all functionalities of the legacy class PCLHistogramVisualizer.</p>
<div class="section" id="plotting-histogram">
<h2>&#8216;Plotting&#8217; Histogram</h2>
<p>PCLPlotter provides a very convenient MATLAB like histogram plotting function (<a class="reference external" href="http://www.mathworks.fr/fr/help/matlab/ref/hist.html">hist()</a> in MATLAB). It takes raw data and bins them according to their frequency and plot them as bar chart.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>

<span class="n">vector</span><span class="o">&lt;</span><span class="kt">double</span><span class="o">&gt;</span> <span class="n">freqdata</span> <span class="o">=</span> <span class="n">generateNomalDistData</span> <span class="p">();</span>

<span class="n">plotter</span><span class="o">-&gt;</span><span class="n">addHistogramData</span> <span class="p">(</span><span class="n">freqdata</span><span class="p">,</span><span class="mi">10</span><span class="p">);</span> <span class="c1">//number of bins are 10</span>

<span class="n">plotter</span><span class="o">-&gt;</span><span class="n">plot</span> <span class="p">();</span>
<span class="p">...</span>
</pre></div>
</div>
</div>
<div class="section" id="pclhistogramvisualizer-functions">
<h2>PCLHistogramVisualizer functions</h2>
<p>All functionalities of PCLHistogramVisualizer has been rewritten and added to the plotter with their signature retained. See the documentation for method details.</p>
</div>
</div>
<div class="section" id="display">
<h1>Display</h1>
<p>To display all the plots added use the simple function - <em>plot()</em>. PCLPlotter is also provided with the legacy <em>spin*()</em> functions which can be used for animations or updating the plots with time.
The following snippet shows the functionality.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>

<span class="c1">//data and callback defined here</span>
<span class="p">...</span>

<span class="n">plotter</span><span class="o">-&gt;</span><span class="n">addPlotData</span> <span class="p">(</span><span class="n">func1</span><span class="p">,</span> <span class="o">-</span><span class="mi">10</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="s">&quot;y = x^2&quot;</span><span class="p">);</span>
<span class="n">plotter</span><span class="o">-&gt;</span><span class="n">spinOnce</span> <span class="p">(</span><span class="mi">2000</span><span class="p">);</span>    <span class="c1">//display the plot for 2 seconds</span>

<span class="n">plotter</span><span class="o">-&gt;</span><span class="n">clearPlots</span> <span class="p">();</span>
<span class="n">plotter</span><span class="o">-&gt;</span><span class="n">addPlotData</span> <span class="p">(</span><span class="n">identity</span><span class="p">,</span><span class="o">-</span><span class="mi">10</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span><span class="s">&quot;identity&quot;</span><span class="p">);</span>
<span class="n">plotter</span><span class="o">-&gt;</span><span class="n">spinOnce</span> <span class="p">(</span><span class="mi">2000</span><span class="p">);</span>

<span class="n">plotter</span><span class="o">-&gt;</span><span class="n">clearPlots</span> <span class="p">();</span>
<span class="n">plotter</span><span class="o">-&gt;</span><span class="n">addPlotData</span> <span class="p">(</span><span class="n">abs</span><span class="p">,</span><span class="o">-</span><span class="mi">5</span><span class="p">,</span> <span class="mi">5</span><span class="p">,</span><span class="s">&quot;abs&quot;</span><span class="p">);</span>
<span class="n">plotter</span><span class="o">-&gt;</span><span class="n">spinOnce</span> <span class="p">(</span><span class="mi">2000</span><span class="p">);</span>
<span class="p">...</span>
</pre></div>
</div>
</div>
<div class="section" id="demo">
<h1>Demo</h1>
<p>Following is a complete example depicting many usage of the Plotter. Copy it into a file named <tt class="docutils literal"><span class="pre">pcl_plotter_demo.cpp</span></tt>.</p>
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
101
102
103
104
105
106
107
108
109
110</pre></div></td><td class="code"><div class="highlight"><pre><span class="cm">/* \author Kripasindhu Sarkar */</span>

<span class="cp">#include&lt;pcl/visualization/pcl_plotter.h&gt;</span>

<span class="cp">#include&lt;iostream&gt;</span>
<span class="cp">#include&lt;vector&gt;</span>
<span class="cp">#include&lt;utility&gt;</span>
<span class="cp">#include&lt;math.h&gt;  </span><span class="c1">//for abs()</span>

<span class="k">using</span> <span class="k">namespace</span> <span class="n">std</span><span class="p">;</span>
<span class="k">using</span> <span class="k">namespace</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="p">;</span>

<span class="kt">void</span>
<span class="nf">generateData</span> <span class="p">(</span><span class="kt">double</span> <span class="o">*</span><span class="n">ax</span><span class="p">,</span> <span class="kt">double</span> <span class="o">*</span><span class="n">acos</span><span class="p">,</span> <span class="kt">double</span> <span class="o">*</span><span class="n">asin</span><span class="p">,</span> <span class="kt">int</span> <span class="n">numPoints</span><span class="p">)</span>
<span class="p">{</span>
  <span class="kt">double</span> <span class="n">inc</span> <span class="o">=</span> <span class="mf">7.5</span> <span class="o">/</span> <span class="p">(</span><span class="n">numPoints</span> <span class="o">-</span> <span class="mi">1</span><span class="p">);</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">int</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">numPoints</span><span class="p">;</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">ax</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="o">=</span> <span class="n">i</span><span class="o">*</span><span class="n">inc</span><span class="p">;</span>
    <span class="n">acos</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="o">=</span> <span class="n">cos</span> <span class="p">(</span><span class="n">i</span> <span class="o">*</span> <span class="n">inc</span><span class="p">);</span>
    <span class="n">asin</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="o">=</span> <span class="n">sin</span> <span class="p">(</span><span class="n">i</span> <span class="o">*</span> <span class="n">inc</span><span class="p">);</span>
  <span class="p">}</span>
<span class="p">}</span>

<span class="c1">//.....................callback functions defining Y= f(X)....................</span>
<span class="kt">double</span>
<span class="nf">step</span> <span class="p">(</span><span class="kt">double</span> <span class="n">val</span><span class="p">)</span>
<span class="p">{</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">val</span> <span class="o">&gt;</span> <span class="mi">0</span><span class="p">)</span>
    <span class="k">return</span> <span class="p">(</span><span class="kt">double</span><span class="p">)</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span> <span class="n">val</span><span class="p">;</span>
  <span class="k">else</span>
    <span class="k">return</span> <span class="p">(</span><span class="kt">double</span><span class="p">)</span> <span class="p">((</span><span class="kt">int</span><span class="p">)</span> <span class="n">val</span> <span class="o">-</span> <span class="mi">1</span><span class="p">);</span>
<span class="p">}</span>

<span class="kt">double</span>
<span class="nf">identity</span> <span class="p">(</span><span class="kt">double</span> <span class="n">val</span><span class="p">)</span>
<span class="p">{</span>
  <span class="k">return</span> <span class="n">val</span><span class="p">;</span>
<span class="p">}</span>
<span class="c1">//............................................................................</span>

<span class="kt">int</span>
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span> <span class="o">*</span> <span class="n">argv</span> <span class="p">[])</span>
<span class="p">{</span>
  <span class="c1">//defining a plotter</span>
  <span class="n">PCLPlotter</span> <span class="o">*</span><span class="n">plotter</span> <span class="o">=</span> <span class="k">new</span> <span class="n">PCLPlotter</span> <span class="p">(</span><span class="s">&quot;My Plotter&quot;</span><span class="p">);</span>

  <span class="c1">//setting some properties</span>
  <span class="n">plotter</span><span class="o">-&gt;</span><span class="n">setShowLegend</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>

  <span class="c1">//generating point correspondances</span>
  <span class="kt">int</span> <span class="n">numPoints</span> <span class="o">=</span> <span class="mi">69</span><span class="p">;</span>
  <span class="kt">double</span> <span class="n">ax</span><span class="p">[</span><span class="mi">100</span><span class="p">],</span> <span class="n">acos</span><span class="p">[</span><span class="mi">100</span><span class="p">],</span> <span class="n">asin</span><span class="p">[</span><span class="mi">100</span><span class="p">];</span>
  <span class="n">generateData</span> <span class="p">(</span><span class="n">ax</span><span class="p">,</span> <span class="n">acos</span><span class="p">,</span> <span class="n">asin</span><span class="p">,</span> <span class="n">numPoints</span><span class="p">);</span>

  <span class="c1">//adding plot data</span>
  <span class="n">plotter</span><span class="o">-&gt;</span><span class="n">addPlotData</span> <span class="p">(</span><span class="n">ax</span><span class="p">,</span> <span class="n">acos</span><span class="p">,</span> <span class="n">numPoints</span><span class="p">,</span> <span class="s">&quot;cos&quot;</span><span class="p">);</span>
  <span class="n">plotter</span><span class="o">-&gt;</span><span class="n">addPlotData</span> <span class="p">(</span><span class="n">ax</span><span class="p">,</span> <span class="n">asin</span><span class="p">,</span> <span class="n">numPoints</span><span class="p">,</span> <span class="s">&quot;sin&quot;</span><span class="p">);</span>

  <span class="c1">//display for 2 seconds</span>
  <span class="n">plotter</span><span class="o">-&gt;</span><span class="n">spinOnce</span> <span class="p">(</span><span class="mi">3000</span><span class="p">);</span>
  <span class="n">plotter</span><span class="o">-&gt;</span><span class="n">clearPlots</span> <span class="p">();</span>
  
  
  <span class="c1">//...................plotting implicit functions and custom callbacks....................</span>

  <span class="c1">//make a fixed axis</span>
  <span class="n">plotter</span><span class="o">-&gt;</span><span class="n">setYRange</span> <span class="p">(</span><span class="o">-</span><span class="mi">10</span><span class="p">,</span> <span class="mi">10</span><span class="p">);</span>

  <span class="c1">//defining polynomials</span>
  <span class="n">vector</span><span class="o">&lt;</span><span class="kt">double</span><span class="o">&gt;</span> <span class="n">func1</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">func1</span><span class="p">[</span><span class="mi">0</span><span class="p">]</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span> <span class="c1">//y = 1</span>
  <span class="n">vector</span><span class="o">&lt;</span><span class="kt">double</span><span class="o">&gt;</span> <span class="n">func2</span> <span class="p">(</span><span class="mi">3</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">func2</span><span class="p">[</span><span class="mi">2</span><span class="p">]</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span> <span class="c1">//y = x^2</span>

  <span class="n">plotter</span><span class="o">-&gt;</span><span class="n">addPlotData</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="n">make_pair</span> <span class="p">(</span><span class="n">func1</span><span class="p">,</span> <span class="n">func2</span><span class="p">),</span> <span class="o">-</span><span class="mi">10</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="s">&quot;y = 1/x^2&quot;</span><span class="p">,</span> <span class="mi">100</span><span class="p">,</span> <span class="n">vtkChart</span><span class="o">::</span><span class="n">POINTS</span><span class="p">);</span>
  <span class="n">plotter</span><span class="o">-&gt;</span><span class="n">spinOnce</span> <span class="p">(</span><span class="mi">2000</span><span class="p">);</span>

  <span class="n">plotter</span><span class="o">-&gt;</span><span class="n">addPlotData</span> <span class="p">(</span><span class="n">func2</span><span class="p">,</span> <span class="o">-</span><span class="mi">10</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="s">&quot;y = x^2&quot;</span><span class="p">);</span>
  <span class="n">plotter</span><span class="o">-&gt;</span><span class="n">spinOnce</span> <span class="p">(</span><span class="mi">2000</span><span class="p">);</span>

  <span class="c1">//callbacks</span>
  <span class="n">plotter</span><span class="o">-&gt;</span><span class="n">addPlotData</span> <span class="p">(</span><span class="n">identity</span><span class="p">,</span> <span class="o">-</span><span class="mi">10</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="s">&quot;identity&quot;</span><span class="p">);</span>
  <span class="n">plotter</span><span class="o">-&gt;</span><span class="n">spinOnce</span> <span class="p">(</span><span class="mi">2000</span><span class="p">);</span>

  <span class="n">plotter</span><span class="o">-&gt;</span><span class="n">addPlotData</span> <span class="p">(</span><span class="n">abs</span><span class="p">,</span> <span class="o">-</span><span class="mi">10</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="s">&quot;abs&quot;</span><span class="p">);</span>
  <span class="n">plotter</span><span class="o">-&gt;</span><span class="n">spinOnce</span> <span class="p">(</span><span class="mi">2000</span><span class="p">);</span>

  <span class="n">plotter</span><span class="o">-&gt;</span><span class="n">addPlotData</span> <span class="p">(</span><span class="n">step</span><span class="p">,</span> <span class="o">-</span><span class="mi">10</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="s">&quot;step&quot;</span><span class="p">,</span> <span class="mi">100</span><span class="p">,</span> <span class="n">vtkChart</span><span class="o">::</span><span class="n">POINTS</span><span class="p">);</span>
  <span class="n">plotter</span><span class="o">-&gt;</span><span class="n">spinOnce</span> <span class="p">(</span><span class="mi">2000</span><span class="p">);</span>

  <span class="n">plotter</span><span class="o">-&gt;</span><span class="n">clearPlots</span> <span class="p">();</span>

  <span class="c1">//........................A simple animation..............................</span>
  <span class="n">vector</span><span class="o">&lt;</span><span class="kt">double</span><span class="o">&gt;</span> <span class="n">fsq</span> <span class="p">(</span><span class="mi">3</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">fsq</span><span class="p">[</span><span class="mi">2</span><span class="p">]</span> <span class="o">=</span> <span class="o">-</span><span class="mi">100</span><span class="p">;</span> <span class="c1">//y = x^2</span>
  <span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">plotter</span><span class="o">-&gt;</span><span class="n">wasStopped</span> <span class="p">())</span>
  <span class="p">{</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">fsq</span><span class="p">[</span><span class="mi">2</span><span class="p">]</span> <span class="o">==</span> <span class="mi">100</span><span class="p">)</span> <span class="n">fsq</span><span class="p">[</span><span class="mi">2</span><span class="p">]</span> <span class="o">=</span> <span class="o">-</span><span class="mi">100</span><span class="p">;</span>
    <span class="n">fsq</span><span class="p">[</span><span class="mi">2</span><span class="p">]</span><span class="o">++</span><span class="p">;</span>
    <span class="kt">char</span> <span class="n">str</span><span class="p">[</span><span class="mi">50</span><span class="p">];</span>
    <span class="n">sprintf</span> <span class="p">(</span><span class="n">str</span><span class="p">,</span> <span class="s">&quot;y = %dx^2&quot;</span><span class="p">,</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span> <span class="n">fsq</span><span class="p">[</span><span class="mi">2</span><span class="p">]);</span>
    <span class="n">plotter</span><span class="o">-&gt;</span><span class="n">addPlotData</span> <span class="p">(</span><span class="n">fsq</span><span class="p">,</span> <span class="o">-</span><span class="mi">10</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="n">str</span><span class="p">);</span>

    <span class="n">plotter</span><span class="o">-&gt;</span><span class="n">spinOnce</span> <span class="p">(</span><span class="mi">100</span><span class="p">);</span>
    <span class="n">plotter</span><span class="o">-&gt;</span><span class="n">clearPlots</span> <span class="p">();</span>
  <span class="p">}</span>

  <span class="k">return</span> <span class="mi">1</span><span class="p">;</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
<div class="section" id="compiling-and-running-the-program">
<h2>Compiling and running the program</h2>
<p>Add the following lines to your <cite>CMakeLists.txt</cite> file:</p>
<div class="highlight-cmake"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre>1
2
3
4
5
6
7
8</pre></div></td><td class="code"><div class="highlight"><pre><span class="nb">cmake_minimum_required</span><span class="p">(</span><span class="s">VERSION</span> <span class="s">2.8</span> <span class="s">FATAL_ERROR</span><span class="p">)</span>
<span class="nb">project</span><span class="p">(</span><span class="s">pcl_plotter</span><span class="p">)</span>
<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.7</span> <span class="s">REQUIRED</span> <span class="s">COMPONENTS</span> <span class="s">common</span> <span class="s">visualization</span><span class="p">)</span>
<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_executable</span><span class="p">(</span><span class="s">pcl_plotter</span> <span class="s">pcl_plotter_demo.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span><span class="p">(</span><span class="s">pcl_plotter</span> <span class="o">${</span><span class="nv">PCL_COMMONLIBRARIES</span><span class="o">}</span> <span class="o">${</span><span class="nv">PCL_VISUALIZATION_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>Compile and run the code by the following commands</p>
<div class="highlight-python"><div class="highlight"><pre>$ cmake .
$ make
$ ./pcl_plotter_demo
</pre></div>
</div>
</div>
<div class="section" id="video">
<h2>Video</h2>
<p>The following video shows the the output of the demo.</p>
<iframe width="480" height="270" src="http://www.youtube.com/embed/2Xgd67nkwzs" frameborder="0" allowfullscreen></iframe></div>
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