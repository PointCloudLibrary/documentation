<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation - Point Cloud Library (PCL)</title>
</head>

<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8" />
    <title>How to use iterative closest point &#8212; PCL 0.0 documentation</title>
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
            
  <div class="section" id="how-to-use-iterative-closest-point">
<span id="iterative-closest-point"></span><h1>How to use iterative closest point</h1>
<p>This document demonstrates using the Iterative Closest Point algorithm in your
code which can determine if one PointCloud is just a rigid transformation of
another by minimizing the distances between the points of two pointclouds and
rigidly transforming them.</p>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
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
48</pre></div></td><td class="code"><div class="highlight"><pre><span></span><span class="cp">#include</span> <span class="cpf">&lt;iostream&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/io/pcd_io.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/point_types.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/registration/icp.h&gt;</span><span class="cp"></span>

<span class="kt">int</span>
 <span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_in</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">(</span><span class="mi">5</span><span class="p">,</span><span class="mi">1</span><span class="p">));</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_out</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="c1">// Fill in the CloudIn data</span>
  <span class="k">for</span> <span class="p">(</span><span class="k">auto</span><span class="o">&amp;</span> <span class="nl">point</span> <span class="p">:</span> <span class="o">*</span><span class="n">cloud_in</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">point</span><span class="p">.</span><span class="n">x</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span><span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">point</span><span class="p">.</span><span class="n">y</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span><span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">point</span><span class="p">.</span><span class="n">z</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span><span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
  <span class="p">}</span>
  
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Saved &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud_in</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; data points to input:&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
      
  <span class="k">for</span> <span class="p">(</span><span class="k">auto</span><span class="o">&amp;</span> <span class="nl">point</span> <span class="p">:</span> <span class="o">*</span><span class="n">cloud_in</span><span class="p">)</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="n">point</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
      
  <span class="o">*</span><span class="n">cloud_out</span> <span class="o">=</span> <span class="o">*</span><span class="n">cloud_in</span><span class="p">;</span>
  
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;size:&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud_out</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span><span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="k">auto</span><span class="o">&amp;</span> <span class="nl">point</span> <span class="p">:</span> <span class="o">*</span><span class="n">cloud_out</span><span class="p">)</span>
    <span class="n">point</span><span class="p">.</span><span class="n">x</span> <span class="o">+=</span> <span class="mf">0.7f</span><span class="p">;</span>

  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Transformed &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud_in</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; data points:&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
      
  <span class="k">for</span> <span class="p">(</span><span class="k">auto</span><span class="o">&amp;</span> <span class="nl">point</span> <span class="p">:</span> <span class="o">*</span><span class="n">cloud_out</span><span class="p">)</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="n">point</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">IterativeClosestPoint</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">icp</span><span class="p">;</span>
  <span class="n">icp</span><span class="p">.</span><span class="n">setInputSource</span><span class="p">(</span><span class="n">cloud_in</span><span class="p">);</span>
  <span class="n">icp</span><span class="p">.</span><span class="n">setInputTarget</span><span class="p">(</span><span class="n">cloud_out</span><span class="p">);</span>
  
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">Final</span><span class="p">;</span>
  <span class="n">icp</span><span class="p">.</span><span class="n">align</span><span class="p">(</span><span class="n">Final</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;has converged:&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">icp</span><span class="p">.</span><span class="n">hasConverged</span><span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; score: &quot;</span> <span class="o">&lt;&lt;</span>
  <span class="n">icp</span><span class="p">.</span><span class="n">getFitnessScore</span><span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="n">icp</span><span class="p">.</span><span class="n">getFinalTransformation</span><span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

 <span class="k">return</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>Now, let’s breakdown this code piece by piece.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="cp">#include</span> <span class="cpf">&lt;iostream&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/io/pcd_io.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/point_types.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/registration/icp.h&gt;</span><span class="cp"></span>
</pre></div>
</div>
<p>these are the header files that contain the definitions for all of the classes which we will use.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_in</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">(</span><span class="mi">5</span><span class="p">,</span><span class="mi">1</span><span class="p">));</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_out</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
</pre></div>
</div>
<p>Creates two pcl::PointCloud&lt;pcl::PointXYZ&gt; boost shared pointers and initializes them. The type of each point is set to PointXYZ in the pcl namespace which is:</p>
<blockquote>
<div><div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="c1">// \brief A point structure representing Euclidean xyz coordinates.</span>
<span class="k">struct</span> <span class="n">PointXYZ</span>
<span class="p">{</span>
  <span class="kt">float</span> <span class="n">x</span><span class="p">;</span>
  <span class="kt">float</span> <span class="n">y</span><span class="p">;</span>
  <span class="kt">float</span> <span class="n">z</span><span class="p">;</span>
<span class="p">};</span>
</pre></div>
</div>
</div></blockquote>
<p>The lines:</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="c1">// Fill in the CloudIn data</span>
  <span class="k">for</span> <span class="p">(</span><span class="k">auto</span><span class="o">&amp;</span> <span class="nl">point</span> <span class="p">:</span> <span class="o">*</span><span class="n">cloud_in</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">point</span><span class="p">.</span><span class="n">x</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span><span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">point</span><span class="p">.</span><span class="n">y</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span><span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">point</span><span class="p">.</span><span class="n">z</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span><span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
  <span class="p">}</span>
  
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Saved &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud_in</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; data points to input:&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
      
  <span class="k">for</span> <span class="p">(</span><span class="k">auto</span><span class="o">&amp;</span> <span class="nl">point</span> <span class="p">:</span> <span class="o">*</span><span class="n">cloud_in</span><span class="p">)</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="n">point</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
      
  <span class="o">*</span><span class="n">cloud_out</span> <span class="o">=</span> <span class="o">*</span><span class="n">cloud_in</span><span class="p">;</span>
  
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;size:&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud_out</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span><span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="k">auto</span><span class="o">&amp;</span> <span class="nl">point</span> <span class="p">:</span> <span class="o">*</span><span class="n">cloud_out</span><span class="p">)</span>
    <span class="n">point</span><span class="p">.</span><span class="n">x</span> <span class="o">+=</span> <span class="mf">0.7f</span><span class="p">;</span>
</pre></div>
</div>
<p>fill in the PointCloud structure with random point values, and set the appropriate parameters (width, height, is_dense).  Also, they output the number of points saved, and their actual data values.</p>
<p>Then:</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Transformed &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud_in</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; data points:&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
      
  <span class="k">for</span> <span class="p">(</span><span class="k">auto</span><span class="o">&amp;</span> <span class="nl">point</span> <span class="p">:</span> <span class="o">*</span><span class="n">cloud_out</span><span class="p">)</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="n">point</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">IterativeClosestPoint</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">icp</span><span class="p">;</span>
</pre></div>
</div>
<p>performs a simple rigid transform on the pointcloud and again outputs the data values.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="n">icp</span><span class="p">.</span><span class="n">setInputSource</span><span class="p">(</span><span class="n">cloud_in</span><span class="p">);</span>
  <span class="n">icp</span><span class="p">.</span><span class="n">setInputTarget</span><span class="p">(</span><span class="n">cloud_out</span><span class="p">);</span>
  
</pre></div>
</div>
<p>This creates an instance of an IterativeClosestPoint and gives it some useful information.  “icp.setInputSource(cloud_in);” sets cloud_in as the PointCloud to begin from and “icp.setInputTarget(cloud_out);” sets cloud_out as the PointCloud which we want cloud_in to look like.</p>
<p>Next,</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">Final</span><span class="p">;</span>
  <span class="n">icp</span><span class="p">.</span><span class="n">align</span><span class="p">(</span><span class="n">Final</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;has converged:&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">icp</span><span class="p">.</span><span class="n">hasConverged</span><span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; score: &quot;</span> <span class="o">&lt;&lt;</span>
  <span class="n">icp</span><span class="p">.</span><span class="n">getFitnessScore</span><span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
</pre></div>
</div>
<p>Creates a pcl::PointCloud&lt;pcl::PointXYZ&gt; to which the IterativeClosestPoint can save the resultant cloud after applying the algorithm.  If the two PointClouds align correctly (meaning they are both the same cloud merely with some kind of rigid transformation applied to one of them) then icp.hasConverged() = 1 (true).  It then outputs the fitness score of the final transformation and some information about it.</p>
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

<span class="nb">project</span><span class="p">(</span><span class="s">iterative_closest_point</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.2</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">iterative_closest_point</span> <span class="s">iterative_closest_point.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">iterative_closest_point</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it. Simply do:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>$ ./iterative_closest_point
</pre></div>
</div>
<p>You will see something similar to:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">Saved</span> <span class="mi">5</span> <span class="n">data</span> <span class="n">points</span> <span class="n">to</span> <span class="nb">input</span><span class="p">:</span>
 <span class="mf">0.352222</span> <span class="o">-</span><span class="mf">0.151883</span> <span class="o">-</span><span class="mf">0.106395</span>
<span class="o">-</span><span class="mf">0.397406</span> <span class="o">-</span><span class="mf">0.473106</span> <span class="mf">0.292602</span>
<span class="o">-</span><span class="mf">0.731898</span> <span class="mf">0.667105</span> <span class="mf">0.441304</span>
<span class="o">-</span><span class="mf">0.734766</span> <span class="mf">0.854581</span> <span class="o">-</span><span class="mf">0.0361733</span>
<span class="o">-</span><span class="mf">0.4607</span> <span class="o">-</span><span class="mf">0.277468</span> <span class="o">-</span><span class="mf">0.916762</span>
<span class="n">size</span><span class="p">:</span><span class="mi">5</span>
<span class="n">Transformed</span> <span class="mi">5</span> <span class="n">data</span> <span class="n">points</span><span class="p">:</span>
 <span class="mf">1.05222</span> <span class="o">-</span><span class="mf">0.151883</span> <span class="o">-</span><span class="mf">0.106395</span>
 <span class="mf">0.302594</span> <span class="o">-</span><span class="mf">0.473106</span> <span class="mf">0.292602</span>
<span class="o">-</span><span class="mf">0.0318983</span> <span class="mf">0.667105</span> <span class="mf">0.441304</span>
<span class="o">-</span><span class="mf">0.0347655</span> <span class="mf">0.854581</span> <span class="o">-</span><span class="mf">0.0361733</span>
      <span class="mf">0.2393</span> <span class="o">-</span><span class="mf">0.277468</span> <span class="o">-</span><span class="mf">0.916762</span>
<span class="p">[</span><span class="n">pcl</span><span class="p">::</span><span class="n">SampleConsensusModelRegistration</span><span class="p">::</span><span class="n">setInputCloud</span><span class="p">]</span> <span class="n">Estimated</span> <span class="n">a</span> <span class="n">sample</span>
<span class="n">selection</span> <span class="n">distance</span> <span class="n">threshold</span> <span class="n">of</span><span class="p">:</span> <span class="mf">0.200928</span>
<span class="p">[</span><span class="n">pcl</span><span class="p">::</span><span class="n">IterativeClosestPoint</span><span class="p">::</span><span class="n">computeTransformation</span><span class="p">]</span> <span class="n">Number</span> <span class="n">of</span>
<span class="n">correspondences</span> <span class="mi">4</span> <span class="p">[</span><span class="mf">80.000000</span><span class="o">%</span><span class="p">]</span> <span class="n">out</span> <span class="n">of</span> <span class="mi">5</span> <span class="n">points</span> <span class="p">[</span><span class="mf">100.0</span><span class="o">%</span><span class="p">],</span> <span class="n">RANSAC</span> <span class="n">rejected</span><span class="p">:</span>
<span class="mi">1</span> <span class="p">[</span><span class="mf">20.000000</span><span class="o">%</span><span class="p">]</span><span class="o">.</span>
<span class="p">[</span><span class="n">pcl</span><span class="p">::</span><span class="n">IterativeClosestPoint</span><span class="p">::</span><span class="n">computeTransformation</span><span class="p">]</span> <span class="n">Convergence</span> <span class="n">reached</span><span class="o">.</span>
<span class="n">Number</span> <span class="n">of</span> <span class="n">iterations</span><span class="p">:</span> <span class="mi">1</span> <span class="n">out</span> <span class="n">of</span> <span class="mf">0.</span> <span class="n">Transformation</span> <span class="n">difference</span><span class="p">:</span> <span class="mf">0.700001</span>
<span class="n">has</span> <span class="n">converged</span><span class="p">:</span><span class="mi">1</span> <span class="n">score</span><span class="p">:</span> <span class="mf">1.95122e-14</span>
          <span class="mi">1</span>  <span class="mf">4.47035e-08</span> <span class="o">-</span><span class="mf">3.25963e-09</span>          <span class="mf">0.7</span>
<span class="mf">2.98023e-08</span>            <span class="mi">1</span> <span class="o">-</span><span class="mf">1.08499e-07</span> <span class="o">-</span><span class="mf">2.98023e-08</span>
<span class="mf">1.30385e-08</span> <span class="o">-</span><span class="mf">1.67638e-08</span>            <span class="mi">1</span>  <span class="mf">1.86265e-08</span>
          <span class="mi">0</span>            <span class="mi">0</span>            <span class="mi">0</span>            <span class="mi">1</span>
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