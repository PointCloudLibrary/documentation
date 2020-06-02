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
    
    <title>Removing outliers using a Conditional or RadiusOutlier removal</title>
    
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
            
  <div class="section" id="removing-outliers-using-a-conditional-or-radiusoutlier-removal">
<span id="remove-outliers"></span><h1>Removing outliers using a Conditional or RadiusOutlier removal</h1>
<p>This document demonstrates how to remove outliers from a PointCloud using several different methods in the filter module.  First we will look at how to use a ConditionalRemoval filter which removes all indices in the given input cloud that do not satisfy one or more given conditions.  Then we will learn how to us a RadiusOutlierRemoval filter which removes all indices in it&#8217;s input cloud that don&#8217;t have at least some number of neighbors within a certain range.</p>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>First, create a file, let&#8217;s say, <tt class="docutils literal"><span class="pre">remove_outliers.cpp</span></tt> in your favorite
editor, and place the following inside it:</p>
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
69</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;iostream&gt;</span>
<span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/filters/radius_outlier_removal.h&gt;</span>
<span class="cp">#include &lt;pcl/filters/conditional_removal.h&gt;</span>

<span class="kt">int</span>
 <span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">argc</span> <span class="o">!=</span> <span class="mi">2</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;please specify command line arg &#39;-r&#39; or &#39;-c&#39;&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="n">exit</span><span class="p">(</span><span class="mi">0</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_filtered</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="c1">// Fill in the cloud data</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">width</span>  <span class="o">=</span> <span class="mi">5</span><span class="p">;</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">height</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">resize</span> <span class="p">(</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">*</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">height</span><span class="p">);</span>

  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="k">if</span> <span class="p">(</span><span class="n">strcmp</span><span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">1</span><span class="p">],</span> <span class="s">&quot;-r&quot;</span><span class="p">)</span> <span class="o">==</span> <span class="mi">0</span><span class="p">){</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">RadiusOutlierRemoval</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">outrem</span><span class="p">;</span>
    <span class="c1">// build the filter</span>
    <span class="n">outrem</span><span class="p">.</span><span class="n">setInputCloud</span><span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
    <span class="n">outrem</span><span class="p">.</span><span class="n">setRadiusSearch</span><span class="p">(</span><span class="mf">0.8</span><span class="p">);</span>
    <span class="n">outrem</span><span class="p">.</span><span class="n">setMinNeighborsInRadius</span> <span class="p">(</span><span class="mi">2</span><span class="p">);</span>
    <span class="c1">// apply filter</span>
    <span class="n">outrem</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_filtered</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="k">else</span> <span class="k">if</span> <span class="p">(</span><span class="n">strcmp</span><span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">1</span><span class="p">],</span> <span class="s">&quot;-c&quot;</span><span class="p">)</span> <span class="o">==</span> <span class="mi">0</span><span class="p">){</span>
    <span class="c1">// build the condition</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">ConditionAnd</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">range_cond</span> <span class="p">(</span><span class="k">new</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">ConditionAnd</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">());</span>
    <span class="n">range_cond</span><span class="o">-&gt;</span><span class="n">addComparison</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">FieldComparison</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">ConstPtr</span> <span class="p">(</span><span class="k">new</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">FieldComparison</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;z&quot;</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">ComparisonOps</span><span class="o">::</span><span class="n">GT</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">)));</span>
    <span class="n">range_cond</span><span class="o">-&gt;</span><span class="n">addComparison</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">FieldComparison</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">ConstPtr</span> <span class="p">(</span><span class="k">new</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">FieldComparison</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;z&quot;</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">ComparisonOps</span><span class="o">::</span><span class="n">LT</span><span class="p">,</span> <span class="mf">0.8</span><span class="p">)));</span>
    <span class="c1">// build the filter</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">ConditionalRemoval</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">condrem</span> <span class="p">(</span><span class="n">range_cond</span><span class="p">);</span>
    <span class="n">condrem</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
    <span class="n">condrem</span><span class="p">.</span><span class="n">setKeepOrganized</span><span class="p">(</span><span class="nb">true</span><span class="p">);</span>
    <span class="c1">// apply filter</span>
    <span class="n">condrem</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_filtered</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="k">else</span><span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;please specify command line arg &#39;-r&#39; or &#39;-c&#39;&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="n">exit</span><span class="p">(</span><span class="mi">0</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Cloud before filtering: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;    &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span>
                        <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span>
                        <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="c1">// display pointcloud after filtering</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Cloud after filtering: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">cloud_filtered</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;    &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud_filtered</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span>
                        <span class="o">&lt;&lt;</span> <span class="n">cloud_filtered</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span>
                        <span class="o">&lt;&lt;</span> <span class="n">cloud_filtered</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="k">return</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="radiusoutlierremoval-background">
<h1>RadiusOutlierRemoval Background</h1>
<p>The picture below helps to visualize what the RadiusOutlierRemoval filter object does.  The user specifies a number of neighbors which every indice must have within a specified radius to remain in the PointCloud.  For example if 1 neighbor is specified, only the yellow point will be removed from the PointCloud.  If 2 neighbors are specified then both the yellow and green points will be removed from the PointCloud.</p>
<img alt="_images/radius_outlier.png" src="_images/radius_outlier.png" />
</div>
<div class="section" id="conditionalremoval-background">
<h1>ConditionalRemoval Background</h1>
<p>Not much to explain here, this filter object removes all points from the PointCloud that do not satisfy one or more conditions that are specified by the user.</p>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>Let&#8217;s break down the code piece by piece.</p>
<p>Some of the code in all 3 of these files is the exact same, so I will only explain what it does once.</p>
<p>Initially the program ensures that the user has specified a command line argument:</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="k">if</span> <span class="p">(</span><span class="n">argc</span> <span class="o">!=</span> <span class="mi">2</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;please specify command line arg &#39;-r&#39; or &#39;-c&#39;&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="n">exit</span><span class="p">(</span><span class="mi">0</span><span class="p">);</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>In the following lines, we first define the PointCloud structures and fill one of them with random points:</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_filtered</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="c1">// Fill in the cloud data</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">width</span>  <span class="o">=</span> <span class="mi">5</span><span class="p">;</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">height</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">resize</span> <span class="p">(</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">*</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">height</span><span class="p">);</span>

  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>Here is where things are a little bit different depending on which filter class is being used &#8211; an if statement involving the command line options divides the program flow.</p>
<p>For the <em>RadiusOutlierRemoval</em>, the user must specify &#8216;-r&#8217; as the command line argument so that this code is executed:</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="k">if</span> <span class="p">(</span><span class="n">strcmp</span><span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">1</span><span class="p">],</span> <span class="s">&quot;-r&quot;</span><span class="p">)</span> <span class="o">==</span> <span class="mi">0</span><span class="p">){</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">RadiusOutlierRemoval</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">outrem</span><span class="p">;</span>
    <span class="c1">// build the filter</span>
    <span class="n">outrem</span><span class="p">.</span><span class="n">setInputCloud</span><span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
    <span class="n">outrem</span><span class="p">.</span><span class="n">setRadiusSearch</span><span class="p">(</span><span class="mf">0.8</span><span class="p">);</span>
    <span class="n">outrem</span><span class="p">.</span><span class="n">setMinNeighborsInRadius</span> <span class="p">(</span><span class="mi">2</span><span class="p">);</span>
    <span class="c1">// apply filter</span>
    <span class="n">outrem</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_filtered</span><span class="p">);</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>Basically, we create the RadiusOutlierRemoval filter object, set its parameters and apply it to our input cloud.  The radius of search is set to 0.8, and a point must have a minimum of 2 neighbors in that radius to be kept as part of the PointCloud.</p>
<p>For the <em>ConditionalRemoval</em> class, the user must specify &#8216;-c&#8217; as the command line argument so that this code is executed:</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="k">else</span> <span class="nf">if</span> <span class="p">(</span><span class="n">strcmp</span><span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">1</span><span class="p">],</span> <span class="s">&quot;-c&quot;</span><span class="p">)</span> <span class="o">==</span> <span class="mi">0</span><span class="p">){</span>
    <span class="c1">// build the condition</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">ConditionAnd</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">range_cond</span> <span class="p">(</span><span class="k">new</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">ConditionAnd</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">());</span>
    <span class="n">range_cond</span><span class="o">-&gt;</span><span class="n">addComparison</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">FieldComparison</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">ConstPtr</span> <span class="p">(</span><span class="k">new</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">FieldComparison</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;z&quot;</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">ComparisonOps</span><span class="o">::</span><span class="n">GT</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">)));</span>
    <span class="n">range_cond</span><span class="o">-&gt;</span><span class="n">addComparison</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">FieldComparison</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">ConstPtr</span> <span class="p">(</span><span class="k">new</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">FieldComparison</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;z&quot;</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">ComparisonOps</span><span class="o">::</span><span class="n">LT</span><span class="p">,</span> <span class="mf">0.8</span><span class="p">)));</span>
    <span class="c1">// build the filter</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">ConditionalRemoval</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">condrem</span> <span class="p">(</span><span class="n">range_cond</span><span class="p">);</span>
    <span class="n">condrem</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
    <span class="n">condrem</span><span class="p">.</span><span class="n">setKeepOrganized</span><span class="p">(</span><span class="nb">true</span><span class="p">);</span>
    <span class="c1">// apply filter</span>
    <span class="n">condrem</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_filtered</span><span class="p">);</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>Basically, we create the condition which a given point must satisfy for it to remain in our PointCloud.  In this example, we use add two comparisons to the conditon: greater than (GT) 0.0 and less than (LT) 0.8.  This condition is then used to build the filter.</p>
<p>In both cases the code above creates the filter object that we are going to use and sets certain parameters that are necessary for the filtering to take place.</p>
<p>The following code just outputs PointCloud before filtering and then after applying whatever filter object is used:</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Cloud before filtering: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;    &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span>
                        <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span>
                        <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="c1">// display pointcloud after filtering</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Cloud after filtering: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">cloud_filtered</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;    &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud_filtered</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span>
                        <span class="o">&lt;&lt;</span> <span class="n">cloud_filtered</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span>
                        <span class="o">&lt;&lt;</span> <span class="n">cloud_filtered</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
</pre></div>
</div>
</div>
<div class="section" id="compiling-and-running-remove-outliers-cpp">
<h1>Compiling and running remove_outliers.cpp</h1>
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

<span class="nb">project</span><span class="p">(</span><span class="s">remove_outliers</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.2</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">remove_outliers</span> <span class="s">remove_outliers.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">remove_outliers</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it.  If you would like to use ConditionalRemoval then simply do:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./remove_outliers -c
</pre></div>
</div>
<p>Otherwise, if you would like to use RadiusOutlierRemoval, simply do:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./remove_outliers -r
</pre></div>
</div>
<p>You will see something similar to (depending on which filter you are using):</p>
<div class="highlight-python"><div class="highlight"><pre>Cloud before filtering:
    0.352222 -0.151883 -0.106395
    -0.397406 -0.473106 0.292602
    -0.731898 0.667105 0.441304
    -0.734766 0.854581 -0.0361733
    -0.4607 -0.277468 -0.916762
Cloud after filtering:
    -0.397406 -0.473106 0.292602
    -0.731898 0.667105 0.441304
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