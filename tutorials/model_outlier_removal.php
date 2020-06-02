<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation - Point Cloud Library (PCL)</title>
</head>

<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8" />
    <title>Filtering a PointCloud using ModelOutlierRemoval &#8212; PCL 0.0 documentation</title>
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
            
  <div class="section" id="filtering-a-pointcloud-using-modeloutlierremoval">
<span id="model-outlier-removal"></span><h1>Filtering a PointCloud using ModelOutlierRemoval</h1>
<p>This tutorial demonstrates how to extract parametric models for example for planes or spheres
out of a PointCloud by using SAC_Models with known coefficients.
If you don’t know the models coefficients take a look at the <a class="reference internal" href="random_sample_consensus.php#random-sample-consensus"><span class="std std-ref">How to use Random Sample Consensus model</span></a> tutorial.</p>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>First, create a file, let’s call it <code class="docutils literal notranslate"><span class="pre">model_outlier_removal.cpp</span></code>, in your favorite
editor, and place the following inside it:</p>
<div class="highlight-cpp notranslate"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
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
71</pre></div></td><td class="code"><div class="highlight"><pre><span></span><span class="cp">#include</span> <span class="cpf">&lt;iostream&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/point_types.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/filters/model_outlier_removal.h&gt;</span><span class="cp"></span>

<span class="kt">int</span>
<span class="nf">main</span> <span class="p">()</span>
<span class="p">{</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_sphere_filtered</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="c1">// 1. Generate cloud data</span>
  <span class="kt">int</span> <span class="n">noise_size</span> <span class="o">=</span> <span class="mi">5</span><span class="p">;</span>
  <span class="kt">int</span> <span class="n">sphere_data_size</span> <span class="o">=</span> <span class="mi">10</span><span class="p">;</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">=</span> <span class="n">noise_size</span> <span class="o">+</span> <span class="n">sphere_data_size</span><span class="p">;</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">height</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">resize</span> <span class="p">(</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">*</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">height</span><span class="p">);</span>
  <span class="c1">// 1.1 Add noise</span>
  <span class="k">for</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">noise_size</span><span class="p">;</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="c1">// 1.2 Add sphere:</span>
  <span class="kt">double</span> <span class="n">rand_x1</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
  <span class="kt">double</span> <span class="n">rand_x2</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="n">noise_size</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">noise_size</span> <span class="o">+</span> <span class="n">sphere_data_size</span><span class="p">;</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="c1">// See: http://mathworld.wolfram.com/SpherePointPicking.html</span>
    <span class="k">while</span> <span class="p">(</span><span class="n">pow</span> <span class="p">(</span><span class="n">rand_x1</span><span class="p">,</span> <span class="mi">2</span><span class="p">)</span> <span class="o">+</span> <span class="n">pow</span> <span class="p">(</span><span class="n">rand_x2</span><span class="p">,</span> <span class="mi">2</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">1</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">rand_x1</span> <span class="o">=</span> <span class="p">(</span><span class="n">rand</span> <span class="p">()</span> <span class="o">%</span> <span class="mi">100</span><span class="p">)</span> <span class="o">/</span> <span class="p">(</span><span class="mf">50.0f</span><span class="p">)</span> <span class="o">-</span> <span class="mi">1</span><span class="p">;</span>
      <span class="n">rand_x2</span> <span class="o">=</span> <span class="p">(</span><span class="n">rand</span> <span class="p">()</span> <span class="o">%</span> <span class="mi">100</span><span class="p">)</span> <span class="o">/</span> <span class="p">(</span><span class="mf">50.0f</span><span class="p">)</span> <span class="o">-</span> <span class="mi">1</span><span class="p">;</span>
    <span class="p">}</span>
    <span class="kt">double</span> <span class="n">pre_calc</span> <span class="o">=</span> <span class="n">sqrt</span> <span class="p">(</span><span class="mi">1</span> <span class="o">-</span> <span class="n">pow</span> <span class="p">(</span><span class="n">rand_x1</span><span class="p">,</span> <span class="mi">2</span><span class="p">)</span> <span class="o">-</span> <span class="n">pow</span> <span class="p">(</span><span class="n">rand_x2</span><span class="p">,</span> <span class="mi">2</span><span class="p">));</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">=</span> <span class="mi">2</span> <span class="o">*</span> <span class="n">rand_x1</span> <span class="o">*</span> <span class="n">pre_calc</span><span class="p">;</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">=</span> <span class="mi">2</span> <span class="o">*</span> <span class="n">rand_x2</span> <span class="o">*</span> <span class="n">pre_calc</span><span class="p">;</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">=</span> <span class="mi">1</span> <span class="o">-</span> <span class="mi">2</span> <span class="o">*</span> <span class="p">(</span><span class="n">pow</span> <span class="p">(</span><span class="n">rand_x1</span><span class="p">,</span> <span class="mi">2</span><span class="p">)</span> <span class="o">+</span> <span class="n">pow</span> <span class="p">(</span><span class="n">rand_x2</span><span class="p">,</span> <span class="mi">2</span><span class="p">));</span>
    <span class="n">rand_x1</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
    <span class="n">rand_x2</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
  <span class="p">}</span>

  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Cloud before filtering: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;    &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="c1">// 2. filter sphere:</span>
  <span class="c1">// 2.1 generate model:</span>
  <span class="c1">// modelparameter for this sphere:</span>
  <span class="c1">// position.x: 0, position.y: 0, position.z:0, radius: 1</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">ModelCoefficients</span> <span class="n">sphere_coeff</span><span class="p">;</span>
  <span class="n">sphere_coeff</span><span class="p">.</span><span class="n">values</span><span class="p">.</span><span class="n">resize</span> <span class="p">(</span><span class="mi">4</span><span class="p">);</span>
  <span class="n">sphere_coeff</span><span class="p">.</span><span class="n">values</span><span class="p">[</span><span class="mi">0</span><span class="p">]</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="n">sphere_coeff</span><span class="p">.</span><span class="n">values</span><span class="p">[</span><span class="mi">1</span><span class="p">]</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="n">sphere_coeff</span><span class="p">.</span><span class="n">values</span><span class="p">[</span><span class="mi">2</span><span class="p">]</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="n">sphere_coeff</span><span class="p">.</span><span class="n">values</span><span class="p">[</span><span class="mi">3</span><span class="p">]</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">ModelOutlierRemoval</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">sphere_filter</span><span class="p">;</span>
  <span class="n">sphere_filter</span><span class="p">.</span><span class="n">setModelCoefficients</span> <span class="p">(</span><span class="n">sphere_coeff</span><span class="p">);</span>
  <span class="n">sphere_filter</span><span class="p">.</span><span class="n">setThreshold</span> <span class="p">(</span><span class="mf">0.05</span><span class="p">);</span>
  <span class="n">sphere_filter</span><span class="p">.</span><span class="n">setModelType</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">SACMODEL_SPHERE</span><span class="p">);</span>
  <span class="n">sphere_filter</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">sphere_filter</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_sphere_filtered</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Sphere after filtering: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">cloud_sphere_filtered</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;    &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud_sphere_filtered</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud_sphere_filtered</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud_sphere_filtered</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span>
        <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="k">return</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>Now, let’s break down the code piece by piece.</p>
<p>In the following lines, we define the PointClouds structures, fill in noise, random points
on a plane as well as random points on a sphere and display its content to screen.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="p">{</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_sphere_filtered</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="c1">// 1. Generate cloud data</span>
  <span class="kt">int</span> <span class="n">noise_size</span> <span class="o">=</span> <span class="mi">5</span><span class="p">;</span>
  <span class="kt">int</span> <span class="n">sphere_data_size</span> <span class="o">=</span> <span class="mi">10</span><span class="p">;</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">=</span> <span class="n">noise_size</span> <span class="o">+</span> <span class="n">sphere_data_size</span><span class="p">;</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">height</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">resize</span> <span class="p">(</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">*</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">height</span><span class="p">);</span>
  <span class="c1">// 1.1 Add noise</span>
  <span class="k">for</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">noise_size</span><span class="p">;</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="c1">// 1.2 Add sphere:</span>
  <span class="kt">double</span> <span class="n">rand_x1</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
  <span class="kt">double</span> <span class="n">rand_x2</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="n">noise_size</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">noise_size</span> <span class="o">+</span> <span class="n">sphere_data_size</span><span class="p">;</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="c1">// See: http://mathworld.wolfram.com/SpherePointPicking.html</span>
    <span class="k">while</span> <span class="p">(</span><span class="n">pow</span> <span class="p">(</span><span class="n">rand_x1</span><span class="p">,</span> <span class="mi">2</span><span class="p">)</span> <span class="o">+</span> <span class="n">pow</span> <span class="p">(</span><span class="n">rand_x2</span><span class="p">,</span> <span class="mi">2</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">1</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">rand_x1</span> <span class="o">=</span> <span class="p">(</span><span class="n">rand</span> <span class="p">()</span> <span class="o">%</span> <span class="mi">100</span><span class="p">)</span> <span class="o">/</span> <span class="p">(</span><span class="mf">50.0f</span><span class="p">)</span> <span class="o">-</span> <span class="mi">1</span><span class="p">;</span>
      <span class="n">rand_x2</span> <span class="o">=</span> <span class="p">(</span><span class="n">rand</span> <span class="p">()</span> <span class="o">%</span> <span class="mi">100</span><span class="p">)</span> <span class="o">/</span> <span class="p">(</span><span class="mf">50.0f</span><span class="p">)</span> <span class="o">-</span> <span class="mi">1</span><span class="p">;</span>
    <span class="p">}</span>
    <span class="kt">double</span> <span class="n">pre_calc</span> <span class="o">=</span> <span class="n">sqrt</span> <span class="p">(</span><span class="mi">1</span> <span class="o">-</span> <span class="n">pow</span> <span class="p">(</span><span class="n">rand_x1</span><span class="p">,</span> <span class="mi">2</span><span class="p">)</span> <span class="o">-</span> <span class="n">pow</span> <span class="p">(</span><span class="n">rand_x2</span><span class="p">,</span> <span class="mi">2</span><span class="p">));</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">=</span> <span class="mi">2</span> <span class="o">*</span> <span class="n">rand_x1</span> <span class="o">*</span> <span class="n">pre_calc</span><span class="p">;</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">=</span> <span class="mi">2</span> <span class="o">*</span> <span class="n">rand_x2</span> <span class="o">*</span> <span class="n">pre_calc</span><span class="p">;</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">=</span> <span class="mi">1</span> <span class="o">-</span> <span class="mi">2</span> <span class="o">*</span> <span class="p">(</span><span class="n">pow</span> <span class="p">(</span><span class="n">rand_x1</span><span class="p">,</span> <span class="mi">2</span><span class="p">)</span> <span class="o">+</span> <span class="n">pow</span> <span class="p">(</span><span class="n">rand_x2</span><span class="p">,</span> <span class="mi">2</span><span class="p">));</span>
    <span class="n">rand_x1</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
    <span class="n">rand_x2</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
  <span class="p">}</span>

  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Cloud before filtering: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;    &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
</pre></div>
</div>
<p>Finally we extract the sphere using ModelOutlierRemoval.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="c1">// position.x: 0, position.y: 0, position.z:0, radius: 1</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">ModelCoefficients</span> <span class="n">sphere_coeff</span><span class="p">;</span>
  <span class="n">sphere_coeff</span><span class="p">.</span><span class="n">values</span><span class="p">.</span><span class="n">resize</span> <span class="p">(</span><span class="mi">4</span><span class="p">);</span>
  <span class="n">sphere_coeff</span><span class="p">.</span><span class="n">values</span><span class="p">[</span><span class="mi">0</span><span class="p">]</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="n">sphere_coeff</span><span class="p">.</span><span class="n">values</span><span class="p">[</span><span class="mi">1</span><span class="p">]</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="n">sphere_coeff</span><span class="p">.</span><span class="n">values</span><span class="p">[</span><span class="mi">2</span><span class="p">]</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="n">sphere_coeff</span><span class="p">.</span><span class="n">values</span><span class="p">[</span><span class="mi">3</span><span class="p">]</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">ModelOutlierRemoval</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">sphere_filter</span><span class="p">;</span>
  <span class="n">sphere_filter</span><span class="p">.</span><span class="n">setModelCoefficients</span> <span class="p">(</span><span class="n">sphere_coeff</span><span class="p">);</span>
  <span class="n">sphere_filter</span><span class="p">.</span><span class="n">setThreshold</span> <span class="p">(</span><span class="mf">0.05</span><span class="p">);</span>
  <span class="n">sphere_filter</span><span class="p">.</span><span class="n">setModelType</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">SACMODEL_SPHERE</span><span class="p">);</span>
</pre></div>
</div>
</div>
<div class="section" id="compiling-and-running-the-program">
<h1>Compiling and running the program</h1>
<p>Add the following lines to your CMakeLists.txt file:</p>
<div class="highlight-cmake notranslate"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
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
12</pre></div></td><td class="code"><div class="highlight"><pre><span></span><span class="nb">cmake_minimum_required</span><span class="p">(</span><span class="s">VERSION</span> <span class="s">2.8</span> <span class="s">FATAL_ERROR</span><span class="p">)</span>

<span class="nb">project</span><span class="p">(</span><span class="s">model_outlier_removal</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.7</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">model_outlier_removal</span> <span class="s">model_outlier_removal.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">model_outlier_removal</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it. Simply do:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>$ ./model_outlier_removal
</pre></div>
</div>
<p>You will see something similar to:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">Cloud</span> <span class="n">before</span> <span class="n">filtering</span><span class="p">:</span>
  <span class="mf">0.352222</span> <span class="o">-</span><span class="mf">0.151883</span> <span class="o">-</span><span class="mf">0.106395</span>
  <span class="o">-</span><span class="mf">0.397406</span> <span class="o">-</span><span class="mf">0.473106</span> <span class="mf">0.292602</span>
  <span class="o">-</span><span class="mf">0.731898</span> <span class="mf">0.667105</span> <span class="mf">0.441304</span>
  <span class="o">-</span><span class="mf">0.734766</span> <span class="mf">0.854581</span> <span class="o">-</span><span class="mf">0.0361733</span>
  <span class="o">-</span><span class="mf">0.4607</span> <span class="o">-</span><span class="mf">0.277468</span> <span class="o">-</span><span class="mf">0.916762</span>
  <span class="o">-</span><span class="mf">0.82</span> <span class="o">-</span><span class="mf">0.341666</span> <span class="mf">0.4592</span>
  <span class="o">-</span><span class="mf">0.728589</span> <span class="mf">0.667873</span> <span class="mf">0.152</span>
  <span class="o">-</span><span class="mf">0.3134</span> <span class="o">-</span><span class="mf">0.873043</span> <span class="o">-</span><span class="mf">0.3736</span>
  <span class="mf">0.62553</span> <span class="mf">0.590779</span> <span class="mf">0.5096</span>
  <span class="o">-</span><span class="mf">0.54048</span> <span class="mf">0.823588</span> <span class="o">-</span><span class="mf">0.172</span>
  <span class="o">-</span><span class="mf">0.707627</span> <span class="mf">0.424576</span> <span class="mf">0.5648</span>
  <span class="o">-</span><span class="mf">0.83153</span> <span class="mf">0.523556</span> <span class="mf">0.1856</span>
  <span class="o">-</span><span class="mf">0.513903</span> <span class="o">-</span><span class="mf">0.719464</span> <span class="mf">0.4672</span>
  <span class="mf">0.291534</span> <span class="mf">0.692393</span> <span class="mf">0.66</span>
  <span class="mf">0.258758</span> <span class="mf">0.654505</span> <span class="o">-</span><span class="mf">0.7104</span>
<span class="n">Sphere</span> <span class="n">after</span> <span class="n">filtering</span><span class="p">:</span>
  <span class="o">-</span><span class="mf">0.82</span> <span class="o">-</span><span class="mf">0.341666</span> <span class="mf">0.4592</span>
  <span class="o">-</span><span class="mf">0.728589</span> <span class="mf">0.667873</span> <span class="mf">0.152</span>
  <span class="o">-</span><span class="mf">0.3134</span> <span class="o">-</span><span class="mf">0.873043</span> <span class="o">-</span><span class="mf">0.3736</span>
  <span class="mf">0.62553</span> <span class="mf">0.590779</span> <span class="mf">0.5096</span>
  <span class="o">-</span><span class="mf">0.54048</span> <span class="mf">0.823588</span> <span class="o">-</span><span class="mf">0.172</span>
  <span class="o">-</span><span class="mf">0.707627</span> <span class="mf">0.424576</span> <span class="mf">0.5648</span>
  <span class="o">-</span><span class="mf">0.83153</span> <span class="mf">0.523556</span> <span class="mf">0.1856</span>
  <span class="o">-</span><span class="mf">0.513903</span> <span class="o">-</span><span class="mf">0.719464</span> <span class="mf">0.4672</span>
  <span class="mf">0.291534</span> <span class="mf">0.692393</span> <span class="mf">0.66</span>
  <span class="mf">0.258758</span> <span class="mf">0.654505</span> <span class="o">-</span><span class="mf">0.7104</span>
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