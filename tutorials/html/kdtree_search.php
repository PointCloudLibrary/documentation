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
    
    <title>How to use a KdTree to search</title>
    
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
            
  <div class="section" id="how-to-use-a-kdtree-to-search">
<span id="kdtree-search"></span><h1>How to use a KdTree to search</h1>
<p>In this tutorial we will go over how to use a KdTree for finding the K nearest neighbors of a specific point or location, and then we will also go over how to find all neighbors within some radius specified by the user (in this case random).</p>
</div>
<div class="section" id="theoretical-primer">
<h1>Theoretical primer</h1>
<p>A k-d tree, or k-dimensional tree, is a data structure used in computer science for organizing some number of points in a space with k dimensions.  It is a binary search tree with other constraints imposed on it. K-d trees are very useful for range and nearest neighbor searches.  For our purposes we will generally only be dealing with point clouds in three dimensions, so all of our k-d trees will be three-dimensional.  Each level of a k-d tree splits all children along a specific dimension, using a hyperplane that is perpendicular to the corresponding axis.  At the root of the tree all children will be split based on the first dimension (i.e. if the first dimension coordinate is less than the root it will be in the left-sub tree and if it is greater than the root it will obviously be in the right sub-tree).  Each level down in the tree divides on the next dimension, returning to the first dimension once all others have been exhausted.  They most efficient way to build a k-d tree is to use a partition method like the one Quick Sort uses to place the median point at the root and everything with a smaller one dimensional value to the left and larger to the right.  You then repeat this procedure on both the left and right sub-trees until the last trees that you are to partition are only composed of one element.</p>
<p>From <a class="reference internal" href="random_sample_consensus.php#wikipedia" id="id1">[Wikipedia]</a>:</p>
<div class="figure align-center">
<img alt="Example of a 2-d k-d tree" src="_images/2d_kdtree.png" />
<p class="caption">This is an example of a 2-dimensional k-d tree</p>
</div>
<div class="figure align-center">
<img alt="" src="_images/nn_kdtree.gif" />
<p class="caption">This is a demonstration of hour the Nearest-Neighbor search works.</p>
</div>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>Create a file, let&#8217;s say, <tt class="docutils literal"><span class="pre">kdtree_search.cpp</span></tt> in your favorite editor and place the following inside:</p>
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
82</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;pcl/point_cloud.h&gt;</span>
<span class="cp">#include &lt;pcl/kdtree/kdtree_flann.h&gt;</span>

<span class="cp">#include &lt;iostream&gt;</span>
<span class="cp">#include &lt;vector&gt;</span>
<span class="cp">#include &lt;ctime&gt;</span>

<span class="kt">int</span>
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">srand</span> <span class="p">(</span><span class="n">time</span> <span class="p">(</span><span class="nb">NULL</span><span class="p">));</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="c1">// Generate pointcloud data</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">=</span> <span class="mi">1000</span><span class="p">;</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">height</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">resize</span> <span class="p">(</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">*</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">height</span><span class="p">);</span>

  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">=</span> <span class="mf">1024.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">=</span> <span class="mf">1024.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">=</span> <span class="mf">1024.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">KdTreeFLANN</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">kdtree</span><span class="p">;</span>

  <span class="n">kdtree</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">searchPoint</span><span class="p">;</span>

  <span class="n">searchPoint</span><span class="p">.</span><span class="n">x</span> <span class="o">=</span> <span class="mf">1024.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
  <span class="n">searchPoint</span><span class="p">.</span><span class="n">y</span> <span class="o">=</span> <span class="mf">1024.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
  <span class="n">searchPoint</span><span class="p">.</span><span class="n">z</span> <span class="o">=</span> <span class="mf">1024.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>

  <span class="c1">// K nearest neighbor search</span>

  <span class="kt">int</span> <span class="n">K</span> <span class="o">=</span> <span class="mi">10</span><span class="p">;</span>

  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">pointIdxNKNSearch</span><span class="p">(</span><span class="n">K</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="n">pointNKNSquaredDistance</span><span class="p">(</span><span class="n">K</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;K nearest neighbor search at (&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">searchPoint</span><span class="p">.</span><span class="n">x</span> 
            <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">searchPoint</span><span class="p">.</span><span class="n">y</span> 
            <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">searchPoint</span><span class="p">.</span><span class="n">z</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;) with K=&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">K</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="k">if</span> <span class="p">(</span> <span class="n">kdtree</span><span class="p">.</span><span class="n">nearestKSearch</span> <span class="p">(</span><span class="n">searchPoint</span><span class="p">,</span> <span class="n">K</span><span class="p">,</span> <span class="n">pointIdxNKNSearch</span><span class="p">,</span> <span class="n">pointNKNSquaredDistance</span><span class="p">)</span> <span class="o">&gt;</span> <span class="mi">0</span> <span class="p">)</span>
  <span class="p">{</span>
    <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">pointIdxNKNSearch</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
      <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;    &quot;</span>  <span class="o">&lt;&lt;</span>   <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span> <span class="n">pointIdxNKNSearch</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="p">].</span><span class="n">x</span> 
                <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span> <span class="n">pointIdxNKNSearch</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="p">].</span><span class="n">y</span> 
                <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span> <span class="n">pointIdxNKNSearch</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="p">].</span><span class="n">z</span> 
                <span class="o">&lt;&lt;</span> <span class="s">&quot; (squared distance: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">pointNKNSquaredDistance</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="p">}</span>

  <span class="c1">// Neighbors within radius search</span>

  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">pointIdxRadiusSearch</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="n">pointRadiusSquaredDistance</span><span class="p">;</span>

  <span class="kt">float</span> <span class="n">radius</span> <span class="o">=</span> <span class="mf">256.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Neighbors within radius search at (&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">searchPoint</span><span class="p">.</span><span class="n">x</span> 
            <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">searchPoint</span><span class="p">.</span><span class="n">y</span> 
            <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">searchPoint</span><span class="p">.</span><span class="n">z</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;) with radius=&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">radius</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>


  <span class="k">if</span> <span class="p">(</span> <span class="n">kdtree</span><span class="p">.</span><span class="n">radiusSearch</span> <span class="p">(</span><span class="n">searchPoint</span><span class="p">,</span> <span class="n">radius</span><span class="p">,</span> <span class="n">pointIdxRadiusSearch</span><span class="p">,</span> <span class="n">pointRadiusSquaredDistance</span><span class="p">)</span> <span class="o">&gt;</span> <span class="mi">0</span> <span class="p">)</span>
  <span class="p">{</span>
    <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">pointIdxRadiusSearch</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
      <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;    &quot;</span>  <span class="o">&lt;&lt;</span>   <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span> <span class="n">pointIdxRadiusSearch</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="p">].</span><span class="n">x</span> 
                <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span> <span class="n">pointIdxRadiusSearch</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="p">].</span><span class="n">y</span> 
                <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span> <span class="n">pointIdxRadiusSearch</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="p">].</span><span class="n">z</span> 
                <span class="o">&lt;&lt;</span> <span class="s">&quot; (squared distance: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">pointRadiusSquaredDistance</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="p">}</span>


  <span class="k">return</span> <span class="mi">0</span><span class="p">;</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>The following code first seeds rand() with the system time and then creates and fills a PointCloud with random data.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">srand</span> <span class="p">(</span><span class="n">time</span> <span class="p">(</span><span class="nb">NULL</span><span class="p">));</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="c1">// Generate pointcloud data</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">=</span> <span class="mi">1000</span><span class="p">;</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">height</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">resize</span> <span class="p">(</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">*</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">height</span><span class="p">);</span>

  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">=</span> <span class="mf">1024.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">=</span> <span class="mf">1024.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">=</span> <span class="mf">1024.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>This next bit of code creates our kdtree object and sets our randomly created cloud as the input.  Then we create a &#8220;searchPoint&#8221; which is assigned random coordinates.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">KdTreeFLANN</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">kdtree</span><span class="p">;</span>

  <span class="n">kdtree</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">searchPoint</span><span class="p">;</span>

  <span class="n">searchPoint</span><span class="p">.</span><span class="n">x</span> <span class="o">=</span> <span class="mf">1024.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
  <span class="n">searchPoint</span><span class="p">.</span><span class="n">y</span> <span class="o">=</span> <span class="mf">1024.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
  <span class="n">searchPoint</span><span class="p">.</span><span class="n">z</span> <span class="o">=</span> <span class="mf">1024.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
</pre></div>
</div>
<p>Now we create an integer (and set it equal to 10) and two vectors for storing our K nearest neighbors from the search.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// K nearest neighbor search</span>

  <span class="kt">int</span> <span class="n">K</span> <span class="o">=</span> <span class="mi">10</span><span class="p">;</span>

  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">pointIdxNKNSearch</span><span class="p">(</span><span class="n">K</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="n">pointNKNSquaredDistance</span><span class="p">(</span><span class="n">K</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;K nearest neighbor search at (&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">searchPoint</span><span class="p">.</span><span class="n">x</span> 
            <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">searchPoint</span><span class="p">.</span><span class="n">y</span> 
            <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">searchPoint</span><span class="p">.</span><span class="n">z</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;) with K=&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">K</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
</pre></div>
</div>
<p>Assuming that our KdTree returns more than 0 closest neighbors it then prints out the locations of all 10 closest neighbors to our random &#8220;searchPoint&#8221; which have been stored in our previously created vectors.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="k">if</span> <span class="p">(</span> <span class="n">kdtree</span><span class="p">.</span><span class="n">nearestKSearch</span> <span class="p">(</span><span class="n">searchPoint</span><span class="p">,</span> <span class="n">K</span><span class="p">,</span> <span class="n">pointIdxNKNSearch</span><span class="p">,</span> <span class="n">pointNKNSquaredDistance</span><span class="p">)</span> <span class="o">&gt;</span> <span class="mi">0</span> <span class="p">)</span>
  <span class="p">{</span>
    <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">pointIdxNKNSearch</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
      <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;    &quot;</span>  <span class="o">&lt;&lt;</span>   <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span> <span class="n">pointIdxNKNSearch</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="p">].</span><span class="n">x</span> 
                <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span> <span class="n">pointIdxNKNSearch</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="p">].</span><span class="n">y</span> 
                <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span> <span class="n">pointIdxNKNSearch</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="p">].</span><span class="n">z</span> 
                <span class="o">&lt;&lt;</span> <span class="s">&quot; (squared distance: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">pointNKNSquaredDistance</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>Now our code demonstrates finding all neighbors to our given &#8220;searchPoint&#8221; within some (randomly generated) radius.  It again creates 2 vectors for storing information about our neighbors.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Neighbors within radius search</span>

  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">pointIdxRadiusSearch</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="n">pointRadiusSquaredDistance</span><span class="p">;</span>

  <span class="kt">float</span> <span class="n">radius</span> <span class="o">=</span> <span class="mf">256.0f</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
</pre></div>
</div>
<p>Again, like before if our KdTree returns more than 0 neighbors within the specified radius it prints out the coordinates of these points which have been stored in our vectors.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="k">if</span> <span class="p">(</span> <span class="n">kdtree</span><span class="p">.</span><span class="n">radiusSearch</span> <span class="p">(</span><span class="n">searchPoint</span><span class="p">,</span> <span class="n">radius</span><span class="p">,</span> <span class="n">pointIdxRadiusSearch</span><span class="p">,</span> <span class="n">pointRadiusSquaredDistance</span><span class="p">)</span> <span class="o">&gt;</span> <span class="mi">0</span> <span class="p">)</span>
  <span class="p">{</span>
    <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">pointIdxRadiusSearch</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
      <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;    &quot;</span>  <span class="o">&lt;&lt;</span>   <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span> <span class="n">pointIdxRadiusSearch</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="p">].</span><span class="n">x</span> 
                <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span> <span class="n">pointIdxRadiusSearch</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="p">].</span><span class="n">y</span> 
                <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span> <span class="n">pointIdxRadiusSearch</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="p">].</span><span class="n">z</span> 
                <span class="o">&lt;&lt;</span> <span class="s">&quot; (squared distance: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">pointRadiusSquaredDistance</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="p">}</span>
</pre></div>
</div>
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

<span class="nb">project</span><span class="p">(</span><span class="s">kdtree_search</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.2</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">kdtree_search</span> <span class="s">kdtree_search.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">kdtree_search</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it. Simply do:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./kdtree_search
</pre></div>
</div>
<p>Once you have run it you should see something similar to this:</p>
<div class="highlight-python"><div class="highlight"><pre>K nearest neighbor search at (455.807 417.256 406.502) with K=10
  494.728 371.875 351.687 (squared distance: 6578.99)
  506.066 420.079 478.278 (squared distance: 7685.67)
  368.546 427.623 416.388 (squared distance: 7819.75)
  474.832 383.041 323.293 (squared distance: 8456.34)
  470.992 334.084 468.459 (squared distance: 10986.9)
  560.884 417.637 364.518 (squared distance: 12803.8)
  466.703 475.716 306.269 (squared distance: 13582.9)
  456.907 336.035 304.529 (squared distance: 16996.7)
  452.288 387.943 279.481 (squared distance: 17005.9)
  476.642 410.422 268.057 (squared distance: 19647.9)
Neighbors within radius search at (455.807 417.256 406.502) with radius=225.932
  494.728 371.875 351.687 (squared distance: 6578.99)
  506.066 420.079 478.278 (squared distance: 7685.67)
  368.546 427.623 416.388 (squared distance: 7819.75)
  474.832 383.041 323.293 (squared distance: 8456.34)
  470.992 334.084 468.459 (squared distance: 10986.9)
  560.884 417.637 364.518 (squared distance: 12803.8)
  466.703 475.716 306.269 (squared distance: 13582.9)
  456.907 336.035 304.529 (squared distance: 16996.7)
  452.288 387.943 279.481 (squared distance: 17005.9)
  476.642 410.422 268.057 (squared distance: 19647.9)
  499.429 541.532 351.35 (squared distance: 20389)
  574.418 452.961 334.7 (squared distance: 20498.9)
  336.785 391.057 488.71 (squared distance: 21611)
  319.765 406.187 350.955 (squared distance: 21715.6)
  528.89 289.583 378.979 (squared distance: 22399.1)
  504.509 459.609 541.732 (squared distance: 22452.8)
  539.854 349.333 300.395 (squared distance: 22936.3)
  548.51 458.035 292.812 (squared distance: 23182.1)
  546.284 426.67 535.989 (squared distance: 25041.6)
  577.058 390.276 508.597 (squared distance: 25853.1)
  543.16 458.727 276.859 (squared distance: 26157.5)
  613.997 387.397 443.207 (squared distance: 27262.7)
  608.235 467.363 327.264 (squared distance: 32023.6)
  506.842 591.736 391.923 (squared distance: 33260.3)
  529.842 475.715 241.532 (squared distance: 36113.7)
  485.822 322.623 244.347 (squared distance: 36150.5)
  362.036 318.014 269.201 (squared distance: 37493.6)
  493.806 600.083 462.742 (squared distance: 38032.3)
  392.315 368.085 585.37 (squared distance: 38442.9)
  303.826 428.659 533.642 (squared distance: 39392.8)
  616.492 424.551 289.524 (squared distance: 39556.8)
  320.563 333.216 278.242 (squared distance: 41804.5)
  646.599 502.256 424.46 (squared distance: 43948.8)
  556.202 325.013 568.252 (squared distance: 44751)
  291.27 497.352 515.938 (squared distance: 45463.9)
  286.483 322.401 495.377 (squared distance: 45567.2)
  367.288 550.421 550.551 (squared distance: 46318.6)
  595.122 582.77 394.894 (squared distance: 46938.1)
  256.784 499.401 379.931 (squared distance: 47064.1)
  430.782 230.854 293.829 (squared distance: 48067.2)
  261.051 486.593 329.854 (squared distance: 48612.7)
  602.061 327.892 545.269 (squared distance: 48632.4)
  347.074 610.994 395.622 (squared distance: 49475.6)
  482.876 284.894 583.888 (squared distance: 49718.6)
  356.962 247.285 514.959 (squared distance: 50423.7)
  282.065 509.488 516.216 (squared distance: 50730.4)
</pre></div>
</div>
<table class="docutils citation" frame="void" id="wikipedia" rules="none">
<colgroup><col class="label" /><col /></colgroup>
<tbody valign="top">
<tr><td class="label"><a class="fn-backref" href="#id1">[Wikipedia]</a></td><td><a class="reference external" href="http://en.wikipedia.org/wiki/K-d_tree">http://en.wikipedia.org/wiki/K-d_tree</a></td></tr>
</tbody>
</table>
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