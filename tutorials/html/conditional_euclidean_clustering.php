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
    
    <title>Conditional Euclidean Clustering</title>
    
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
            
  <div class="section" id="conditional-euclidean-clustering">
<span id="id1"></span><h1>Conditional Euclidean Clustering</h1>
<p>This tutorial describes how to use the <tt class="docutils literal"><span class="pre">pcl::ConditionalEuclideanClustering</span></tt> class:
A segmentation algorithm that clusters points based on Euclidean distance and a user-customizable condition that needs to hold.</p>
<p>This class uses the same greedy-like / region-growing / flood-filling approach that is used in <a class="reference internal" href="cluster_extraction.php#cluster-extraction"><em>Euclidean Cluster Extraction</em></a>, <a class="reference internal" href="region_growing_segmentation.php#region-growing-segmentation"><em>Region growing segmentation</em></a> and <a class="reference internal" href="region_growing_rgb_segmentation.php#region-growing-rgb-segmentation"><em>Color-based region growing segmentation</em></a>.
The advantage of using this class over the other classes is that the constraints for clustering (pure Euclidean, smoothness, RGB) are now customizable by the user.
Some disadvantages include: no initial seeding system, no over- and under-segmentation control, and the fact that calling a conditional function from inside the main computational loop is less time efficient.</p>
</div>
<div class="section" id="theoretical-primer">
<h1>Theoretical Primer</h1>
<p>The <a class="reference internal" href="cluster_extraction.php#cluster-extraction"><em>Euclidean Cluster Extraction</em></a> and <a class="reference internal" href="region_growing_segmentation.php#region-growing-segmentation"><em>Region growing segmentation</em></a> tutorials already explain the region growing algorithm very accurately.
The only addition to those explanations is that the condition that needs to hold for a neighbor to be merged into the current cluster, can now be fully customized.</p>
<p>As a cluster grows, it will evaluate the user-defined condition between points already inside the cluster and nearby candidate points.
The candidate points (nearest neighbor points) are found using a Euclidean radius search around each point in the cluster.
For each point within a resulting cluster, the condition needed to hold with at least one of its neighbors and NOT with all of its neighbors.</p>
<p>The Conditional Euclidean Clustering class can also automatically filter clusters based on a size constraint.
The clusters classified as too small or too large can still be retrieved afterwards.</p>
</div>
<div class="section" id="the-code">
<h1>The Code</h1>
<p>First, download the dataset <a class="reference external" href="https://raw.github.com/PointCloudLibrary/data/master/Trimble/Outdoor1/Statues_4.pcd">Statues_4.pcd</a> and save it somewhere to disk.
This is a very large data set of an outdoor environment where we aim to cluster the separate objects and also want to separate the building from the ground plane even though it is attached in a Euclidean sense.</p>
<p>Now create a file, let&#8217;s say, <tt class="docutils literal"><span class="pre">conditional_euclidean_clustering.cpp</span></tt> in your favorite editor, and place the following inside it:</p>
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
110
111
112
113
114
115
116
117</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/io/pcd_io.h&gt;</span>
<span class="cp">#include &lt;pcl/console/time.h&gt;</span>

<span class="cp">#include &lt;pcl/filters/voxel_grid.h&gt;</span>
<span class="cp">#include &lt;pcl/features/normal_3d.h&gt;</span>
<span class="cp">#include &lt;pcl/segmentation/conditional_euclidean_clustering.h&gt;</span>

<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZI</span> <span class="n">PointTypeIO</span><span class="p">;</span>
<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZINormal</span> <span class="n">PointTypeFull</span><span class="p">;</span>

<span class="kt">bool</span>
<span class="nf">enforceIntensitySimilarity</span> <span class="p">(</span><span class="k">const</span> <span class="n">PointTypeFull</span><span class="o">&amp;</span> <span class="n">point_a</span><span class="p">,</span> <span class="k">const</span> <span class="n">PointTypeFull</span><span class="o">&amp;</span> <span class="n">point_b</span><span class="p">,</span> <span class="kt">float</span> <span class="n">squared_distance</span><span class="p">)</span>
<span class="p">{</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">fabs</span> <span class="p">(</span><span class="n">point_a</span><span class="p">.</span><span class="n">intensity</span> <span class="o">-</span> <span class="n">point_b</span><span class="p">.</span><span class="n">intensity</span><span class="p">)</span> <span class="o">&lt;</span> <span class="mf">5.0f</span><span class="p">)</span>
    <span class="k">return</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
  <span class="k">else</span>
    <span class="k">return</span> <span class="p">(</span><span class="nb">false</span><span class="p">);</span>
<span class="p">}</span>

<span class="kt">bool</span>
<span class="nf">enforceCurvatureOrIntensitySimilarity</span> <span class="p">(</span><span class="k">const</span> <span class="n">PointTypeFull</span><span class="o">&amp;</span> <span class="n">point_a</span><span class="p">,</span> <span class="k">const</span> <span class="n">PointTypeFull</span><span class="o">&amp;</span> <span class="n">point_b</span><span class="p">,</span> <span class="kt">float</span> <span class="n">squared_distance</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Map</span><span class="o">&lt;</span><span class="k">const</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span><span class="o">&gt;</span> <span class="n">point_a_normal</span> <span class="o">=</span> <span class="n">point_a</span><span class="p">.</span><span class="n">normal</span><span class="p">,</span> <span class="n">point_b_normal</span> <span class="o">=</span> <span class="n">point_b</span><span class="p">.</span><span class="n">normal</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">fabs</span> <span class="p">(</span><span class="n">point_a</span><span class="p">.</span><span class="n">intensity</span> <span class="o">-</span> <span class="n">point_b</span><span class="p">.</span><span class="n">intensity</span><span class="p">)</span> <span class="o">&lt;</span> <span class="mf">5.0f</span><span class="p">)</span>
    <span class="k">return</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">fabs</span> <span class="p">(</span><span class="n">point_a_normal</span><span class="p">.</span><span class="n">dot</span> <span class="p">(</span><span class="n">point_b_normal</span><span class="p">))</span> <span class="o">&lt;</span> <span class="mf">0.05</span><span class="p">)</span>
    <span class="k">return</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
  <span class="k">return</span> <span class="p">(</span><span class="nb">false</span><span class="p">);</span>
<span class="p">}</span>

<span class="kt">bool</span>
<span class="nf">customRegionGrowing</span> <span class="p">(</span><span class="k">const</span> <span class="n">PointTypeFull</span><span class="o">&amp;</span> <span class="n">point_a</span><span class="p">,</span> <span class="k">const</span> <span class="n">PointTypeFull</span><span class="o">&amp;</span> <span class="n">point_b</span><span class="p">,</span> <span class="kt">float</span> <span class="n">squared_distance</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Map</span><span class="o">&lt;</span><span class="k">const</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span><span class="o">&gt;</span> <span class="n">point_a_normal</span> <span class="o">=</span> <span class="n">point_a</span><span class="p">.</span><span class="n">normal</span><span class="p">,</span> <span class="n">point_b_normal</span> <span class="o">=</span> <span class="n">point_b</span><span class="p">.</span><span class="n">normal</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">squared_distance</span> <span class="o">&lt;</span> <span class="mi">10000</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">fabs</span> <span class="p">(</span><span class="n">point_a</span><span class="p">.</span><span class="n">intensity</span> <span class="o">-</span> <span class="n">point_b</span><span class="p">.</span><span class="n">intensity</span><span class="p">)</span> <span class="o">&lt;</span> <span class="mf">8.0f</span><span class="p">)</span>
      <span class="k">return</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">fabs</span> <span class="p">(</span><span class="n">point_a_normal</span><span class="p">.</span><span class="n">dot</span> <span class="p">(</span><span class="n">point_b_normal</span><span class="p">))</span> <span class="o">&lt;</span> <span class="mf">0.06</span><span class="p">)</span>
      <span class="k">return</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="k">else</span>
  <span class="p">{</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">fabs</span> <span class="p">(</span><span class="n">point_a</span><span class="p">.</span><span class="n">intensity</span> <span class="o">-</span> <span class="n">point_b</span><span class="p">.</span><span class="n">intensity</span><span class="p">)</span> <span class="o">&lt;</span> <span class="mf">3.0f</span><span class="p">)</span>
      <span class="k">return</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="k">return</span> <span class="p">(</span><span class="nb">false</span><span class="p">);</span>
<span class="p">}</span>

<span class="kt">int</span>
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="c1">// Data containers used</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointTypeIO</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_in</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointTypeIO</span><span class="o">&gt;</span><span class="p">),</span> <span class="n">cloud_out</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointTypeIO</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointTypeFull</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_with_normals</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointTypeFull</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">IndicesClustersPtr</span> <span class="n">clusters</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">IndicesClusters</span><span class="p">),</span> <span class="n">small_clusters</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">IndicesClusters</span><span class="p">),</span> <span class="n">large_clusters</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">IndicesClusters</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">PointTypeIO</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">search_tree</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">PointTypeIO</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">TicToc</span> <span class="n">tt</span><span class="p">;</span>

  <span class="c1">// Load the input point cloud</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Loading...</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">tt</span><span class="p">.</span><span class="n">tic</span> <span class="p">();</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="s">&quot;Statues_4.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud_in</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;&gt;&gt; Done: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">tt</span><span class="p">.</span><span class="n">toc</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; ms, &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud_in</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; points</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>

  <span class="c1">// Downsample the cloud using a Voxel Grid class</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Downsampling...</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">tt</span><span class="p">.</span><span class="n">tic</span> <span class="p">();</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">VoxelGrid</span><span class="o">&lt;</span><span class="n">PointTypeIO</span><span class="o">&gt;</span> <span class="n">vg</span><span class="p">;</span>
  <span class="n">vg</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_in</span><span class="p">);</span>
  <span class="n">vg</span><span class="p">.</span><span class="n">setLeafSize</span> <span class="p">(</span><span class="mf">80.0</span><span class="p">,</span> <span class="mf">80.0</span><span class="p">,</span> <span class="mf">80.0</span><span class="p">);</span>
  <span class="n">vg</span><span class="p">.</span><span class="n">setDownsampleAllData</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
  <span class="n">vg</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_out</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;&gt;&gt; Done: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">tt</span><span class="p">.</span><span class="n">toc</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; ms, &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud_out</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; points</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>

  <span class="c1">// Set up a Normal Estimation class and merge data in cloud_with_normals</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Computing normals...</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">tt</span><span class="p">.</span><span class="n">tic</span> <span class="p">();</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">copyPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_out</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud_with_normals</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">NormalEstimation</span><span class="o">&lt;</span><span class="n">PointTypeIO</span><span class="p">,</span> <span class="n">PointTypeFull</span><span class="o">&gt;</span> <span class="n">ne</span><span class="p">;</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_out</span><span class="p">);</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">search_tree</span><span class="p">);</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="mf">300.0</span><span class="p">);</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_with_normals</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;&gt;&gt; Done: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">tt</span><span class="p">.</span><span class="n">toc</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; ms</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>

  <span class="c1">// Set up a Conditional Euclidean Clustering class</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Segmenting to clusters...</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">tt</span><span class="p">.</span><span class="n">tic</span> <span class="p">();</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">ConditionalEuclideanClustering</span><span class="o">&lt;</span><span class="n">PointTypeFull</span><span class="o">&gt;</span> <span class="n">cec</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
  <span class="n">cec</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_with_normals</span><span class="p">);</span>
  <span class="n">cec</span><span class="p">.</span><span class="n">setConditionFunction</span> <span class="p">(</span><span class="o">&amp;</span><span class="n">customRegionGrowing</span><span class="p">);</span>
  <span class="n">cec</span><span class="p">.</span><span class="n">setClusterTolerance</span> <span class="p">(</span><span class="mf">500.0</span><span class="p">);</span>
  <span class="n">cec</span><span class="p">.</span><span class="n">setMinClusterSize</span> <span class="p">(</span><span class="n">cloud_with_normals</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">/</span> <span class="mi">1000</span><span class="p">);</span>
  <span class="n">cec</span><span class="p">.</span><span class="n">setMaxClusterSize</span> <span class="p">(</span><span class="n">cloud_with_normals</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">/</span> <span class="mi">5</span><span class="p">);</span>
  <span class="n">cec</span><span class="p">.</span><span class="n">segment</span> <span class="p">(</span><span class="o">*</span><span class="n">clusters</span><span class="p">);</span>
  <span class="n">cec</span><span class="p">.</span><span class="n">getRemovedClusters</span> <span class="p">(</span><span class="n">small_clusters</span><span class="p">,</span> <span class="n">large_clusters</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;&gt;&gt; Done: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">tt</span><span class="p">.</span><span class="n">toc</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; ms</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>

  <span class="c1">// Using the intensity channel for lazy visualization of the output</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">int</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">small_clusters</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
    <span class="k">for</span> <span class="p">(</span><span class="kt">int</span> <span class="n">j</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">j</span> <span class="o">&lt;</span> <span class="p">(</span><span class="o">*</span><span class="n">small_clusters</span><span class="p">)[</span><span class="n">i</span><span class="p">].</span><span class="n">indices</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">j</span><span class="p">)</span>
      <span class="n">cloud_out</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[(</span><span class="o">*</span><span class="n">small_clusters</span><span class="p">)[</span><span class="n">i</span><span class="p">].</span><span class="n">indices</span><span class="p">[</span><span class="n">j</span><span class="p">]].</span><span class="n">intensity</span> <span class="o">=</span> <span class="o">-</span><span class="mf">2.0</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">int</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">large_clusters</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
    <span class="k">for</span> <span class="p">(</span><span class="kt">int</span> <span class="n">j</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">j</span> <span class="o">&lt;</span> <span class="p">(</span><span class="o">*</span><span class="n">large_clusters</span><span class="p">)[</span><span class="n">i</span><span class="p">].</span><span class="n">indices</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">j</span><span class="p">)</span>
      <span class="n">cloud_out</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[(</span><span class="o">*</span><span class="n">large_clusters</span><span class="p">)[</span><span class="n">i</span><span class="p">].</span><span class="n">indices</span><span class="p">[</span><span class="n">j</span><span class="p">]].</span><span class="n">intensity</span> <span class="o">=</span> <span class="o">+</span><span class="mf">10.0</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">int</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">clusters</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="kt">int</span> <span class="n">label</span> <span class="o">=</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">%</span> <span class="mi">8</span><span class="p">;</span>
    <span class="k">for</span> <span class="p">(</span><span class="kt">int</span> <span class="n">j</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">j</span> <span class="o">&lt;</span> <span class="p">(</span><span class="o">*</span><span class="n">clusters</span><span class="p">)[</span><span class="n">i</span><span class="p">].</span><span class="n">indices</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">j</span><span class="p">)</span>
      <span class="n">cloud_out</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[(</span><span class="o">*</span><span class="n">clusters</span><span class="p">)[</span><span class="n">i</span><span class="p">].</span><span class="n">indices</span><span class="p">[</span><span class="n">j</span><span class="p">]].</span><span class="n">intensity</span> <span class="o">=</span> <span class="n">label</span><span class="p">;</span>
  <span class="p">}</span>

  <span class="c1">// Save the output point cloud</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Saving...</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">tt</span><span class="p">.</span><span class="n">tic</span> <span class="p">();</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">savePCDFile</span> <span class="p">(</span><span class="s">&quot;output.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud_out</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;&gt;&gt; Done: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">tt</span><span class="p">.</span><span class="n">toc</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; ms</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>

  <span class="k">return</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="the-explanation">
<h1>The Explanation</h1>
<p>Since the Conditional Euclidean Clustering class is for more advanced users, I will skip explanation of the more obvious parts of the code:</p>
<blockquote>
<div><ul class="simple">
<li><tt class="docutils literal"><span class="pre">pcl::io::loadPCDFile</span></tt> and <tt class="docutils literal"><span class="pre">pcl::io::savePCDFile</span></tt> are used for loading and saving the point cloud data.</li>
<li><tt class="docutils literal"><span class="pre">pcl::console::TicToc</span></tt> is used for easy output of timing results.</li>
<li><a class="reference internal" href="voxel_grid.php#voxelgrid"><em>Downsampling a PointCloud using a VoxelGrid filter</em></a> is being used (lines 66-73) to downsample the cloud and give a more equalized point density.</li>
<li><a class="reference internal" href="normal_estimation.php#normal-estimation"><em>Estimating Surface Normals in a PointCloud</em></a> is being used (lines 75-83)  to estimate normals which will be appended to the point information;
The Conditional Euclidean Clustering class will be templated with <tt class="docutils literal"><span class="pre">pcl::PoitnXYZINormal</span></tt>, containing x, y, z, intensity, normal and curvature information to use in the condition function.</li>
</ul>
</div></blockquote>
<p>Lines 85-95 set up the Conditional Euclidean Clustering class for use:</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Set up a Conditional Euclidean Clustering class</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Segmenting to clusters...</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">tt</span><span class="p">.</span><span class="n">tic</span> <span class="p">();</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">ConditionalEuclideanClustering</span><span class="o">&lt;</span><span class="n">PointTypeFull</span><span class="o">&gt;</span> <span class="n">cec</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
  <span class="n">cec</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_with_normals</span><span class="p">);</span>
  <span class="n">cec</span><span class="p">.</span><span class="n">setConditionFunction</span> <span class="p">(</span><span class="o">&amp;</span><span class="n">customRegionGrowing</span><span class="p">);</span>
  <span class="n">cec</span><span class="p">.</span><span class="n">setClusterTolerance</span> <span class="p">(</span><span class="mf">500.0</span><span class="p">);</span>
  <span class="n">cec</span><span class="p">.</span><span class="n">setMinClusterSize</span> <span class="p">(</span><span class="n">cloud_with_normals</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">/</span> <span class="mi">1000</span><span class="p">);</span>
  <span class="n">cec</span><span class="p">.</span><span class="n">setMaxClusterSize</span> <span class="p">(</span><span class="n">cloud_with_normals</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">/</span> <span class="mi">5</span><span class="p">);</span>
  <span class="n">cec</span><span class="p">.</span><span class="n">segment</span> <span class="p">(</span><span class="o">*</span><span class="n">clusters</span><span class="p">);</span>
  <span class="n">cec</span><span class="p">.</span><span class="n">getRemovedClusters</span> <span class="p">(</span><span class="n">small_clusters</span><span class="p">,</span> <span class="n">large_clusters</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;&gt;&gt; Done: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">tt</span><span class="p">.</span><span class="n">toc</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; ms</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
</pre></div>
</div>
<p>A more elaborate description of the different lines of code:</p>
<blockquote>
<div><ul class="simple">
<li>The class is initialized with TRUE.
This will allow extraction of clusters that are too small or too large.
It saves some computation time and memory if the class is initialized without this.</li>
<li>The input data for the class can be specified using methods derived from the <tt class="docutils literal"><span class="pre">PCLBase</span></tt> class, i.e.: <tt class="docutils literal"><span class="pre">setInputCloud</span></tt> and <tt class="docutils literal"><span class="pre">setIndices</span></tt>.</li>
<li>As a cluster grows, it will evaluate a user-defined condition between points already inside the cluster and nearby candidate points.
More on the condition function can be read further below.</li>
<li>The cluster tolerance is the radius for the k-NN searching, used to find the candidate points.</li>
<li>Clusters that make up less than 0.1% of the cloud&#8217;s total points are considered too small.</li>
<li>Clusters that make up more than 20% of the cloud&#8217;s total points are considered too large.</li>
<li>The resulting clusters are stored in the <tt class="docutils literal"><span class="pre">pcl::IndicesClusters</span></tt> format, which is an array of indices-arrays, indexing points of the input point cloud.</li>
<li>Too small clusters or too large clusters are not passed to the main output but can instead be retrieved in separate <tt class="docutils literal"><span class="pre">pcl::IndicesClusters</span></tt> data containers, but only is the class was initialized with TRUE.</li>
</ul>
</div></blockquote>
<p>Lines 12-49 show some examples of condition functions:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">bool</span>
<span class="nf">enforceIntensitySimilarity</span> <span class="p">(</span><span class="k">const</span> <span class="n">PointTypeFull</span><span class="o">&amp;</span> <span class="n">point_a</span><span class="p">,</span> <span class="k">const</span> <span class="n">PointTypeFull</span><span class="o">&amp;</span> <span class="n">point_b</span><span class="p">,</span> <span class="kt">float</span> <span class="n">squared_distance</span><span class="p">)</span>
<span class="p">{</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">fabs</span> <span class="p">(</span><span class="n">point_a</span><span class="p">.</span><span class="n">intensity</span> <span class="o">-</span> <span class="n">point_b</span><span class="p">.</span><span class="n">intensity</span><span class="p">)</span> <span class="o">&lt;</span> <span class="mf">5.0f</span><span class="p">)</span>
    <span class="k">return</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
  <span class="k">else</span>
    <span class="k">return</span> <span class="p">(</span><span class="nb">false</span><span class="p">);</span>
<span class="p">}</span>

<span class="kt">bool</span>
<span class="nf">enforceCurvatureOrIntensitySimilarity</span> <span class="p">(</span><span class="k">const</span> <span class="n">PointTypeFull</span><span class="o">&amp;</span> <span class="n">point_a</span><span class="p">,</span> <span class="k">const</span> <span class="n">PointTypeFull</span><span class="o">&amp;</span> <span class="n">point_b</span><span class="p">,</span> <span class="kt">float</span> <span class="n">squared_distance</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Map</span><span class="o">&lt;</span><span class="k">const</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span><span class="o">&gt;</span> <span class="n">point_a_normal</span> <span class="o">=</span> <span class="n">point_a</span><span class="p">.</span><span class="n">normal</span><span class="p">,</span> <span class="n">point_b_normal</span> <span class="o">=</span> <span class="n">point_b</span><span class="p">.</span><span class="n">normal</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">fabs</span> <span class="p">(</span><span class="n">point_a</span><span class="p">.</span><span class="n">intensity</span> <span class="o">-</span> <span class="n">point_b</span><span class="p">.</span><span class="n">intensity</span><span class="p">)</span> <span class="o">&lt;</span> <span class="mf">5.0f</span><span class="p">)</span>
    <span class="k">return</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">fabs</span> <span class="p">(</span><span class="n">point_a_normal</span><span class="p">.</span><span class="n">dot</span> <span class="p">(</span><span class="n">point_b_normal</span><span class="p">))</span> <span class="o">&lt;</span> <span class="mf">0.05</span><span class="p">)</span>
    <span class="k">return</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
  <span class="k">return</span> <span class="p">(</span><span class="nb">false</span><span class="p">);</span>
<span class="p">}</span>

<span class="kt">bool</span>
<span class="nf">customRegionGrowing</span> <span class="p">(</span><span class="k">const</span> <span class="n">PointTypeFull</span><span class="o">&amp;</span> <span class="n">point_a</span><span class="p">,</span> <span class="k">const</span> <span class="n">PointTypeFull</span><span class="o">&amp;</span> <span class="n">point_b</span><span class="p">,</span> <span class="kt">float</span> <span class="n">squared_distance</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Map</span><span class="o">&lt;</span><span class="k">const</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span><span class="o">&gt;</span> <span class="n">point_a_normal</span> <span class="o">=</span> <span class="n">point_a</span><span class="p">.</span><span class="n">normal</span><span class="p">,</span> <span class="n">point_b_normal</span> <span class="o">=</span> <span class="n">point_b</span><span class="p">.</span><span class="n">normal</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">squared_distance</span> <span class="o">&lt;</span> <span class="mi">10000</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">fabs</span> <span class="p">(</span><span class="n">point_a</span><span class="p">.</span><span class="n">intensity</span> <span class="o">-</span> <span class="n">point_b</span><span class="p">.</span><span class="n">intensity</span><span class="p">)</span> <span class="o">&lt;</span> <span class="mf">8.0f</span><span class="p">)</span>
      <span class="k">return</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">fabs</span> <span class="p">(</span><span class="n">point_a_normal</span><span class="p">.</span><span class="n">dot</span> <span class="p">(</span><span class="n">point_b_normal</span><span class="p">))</span> <span class="o">&lt;</span> <span class="mf">0.06</span><span class="p">)</span>
      <span class="k">return</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="k">else</span>
  <span class="p">{</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">fabs</span> <span class="p">(</span><span class="n">point_a</span><span class="p">.</span><span class="n">intensity</span> <span class="o">-</span> <span class="n">point_b</span><span class="p">.</span><span class="n">intensity</span><span class="p">)</span> <span class="o">&lt;</span> <span class="mf">3.0f</span><span class="p">)</span>
      <span class="k">return</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="k">return</span> <span class="p">(</span><span class="nb">false</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</div>
<p>The format of the condition function is fixed:</p>
<blockquote>
<div><ul class="simple">
<li>The first two input arguments need to be of the same type as the templated type used in the Conditional Euclidean Clustering class.
These arguments will pass the point information for the current seed point (first argument) and the current candidate point (second argument).</li>
<li>The third input argument needs to be a float.
This argument will pass the squared distance between the seed and candidate point.
Although this information is also computable using the first two arguments, it is already provided by the underlying nearest neighbor search and can be used to easily make a distance dependent condition function.</li>
<li>The output argument needs to be a boolean.
Returning TRUE will merge the candidate point into the cluster of the seed point.
Returning FALSE will not merge the candidate point through this particular point-pair, however, it is still possible that the two points will end up in the same cluster through a different point-pair relationship.</li>
</ul>
</div></blockquote>
<p>These example condition functions are just to give an indication of how to use them.
For instance, the second condition function will grow clusters as long as they are similar in surface normal direction OR similar in intensity value.
This should hopefully cluster buildings of similar texture as one cluster, but not merge them into the same cluster as adjacent objects.
This is going to work out if the intensity is different enough from nearby objects AND the nearby objects are not sharing a nearby surface with the same normal.
The third condition function is similar to the second but has different constraints depending on the distance between the points.</p>
<p>Lines 97-109 contain a piece of code that is a quick and dirty fix to visualize the result:</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Using the intensity channel for lazy visualization of the output</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">int</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">small_clusters</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
    <span class="k">for</span> <span class="p">(</span><span class="kt">int</span> <span class="n">j</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">j</span> <span class="o">&lt;</span> <span class="p">(</span><span class="o">*</span><span class="n">small_clusters</span><span class="p">)[</span><span class="n">i</span><span class="p">].</span><span class="n">indices</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">j</span><span class="p">)</span>
      <span class="n">cloud_out</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[(</span><span class="o">*</span><span class="n">small_clusters</span><span class="p">)[</span><span class="n">i</span><span class="p">].</span><span class="n">indices</span><span class="p">[</span><span class="n">j</span><span class="p">]].</span><span class="n">intensity</span> <span class="o">=</span> <span class="o">-</span><span class="mf">2.0</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">int</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">large_clusters</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
    <span class="k">for</span> <span class="p">(</span><span class="kt">int</span> <span class="n">j</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">j</span> <span class="o">&lt;</span> <span class="p">(</span><span class="o">*</span><span class="n">large_clusters</span><span class="p">)[</span><span class="n">i</span><span class="p">].</span><span class="n">indices</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">j</span><span class="p">)</span>
      <span class="n">cloud_out</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[(</span><span class="o">*</span><span class="n">large_clusters</span><span class="p">)[</span><span class="n">i</span><span class="p">].</span><span class="n">indices</span><span class="p">[</span><span class="n">j</span><span class="p">]].</span><span class="n">intensity</span> <span class="o">=</span> <span class="o">+</span><span class="mf">10.0</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">int</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">clusters</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="kt">int</span> <span class="n">label</span> <span class="o">=</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">%</span> <span class="mi">8</span><span class="p">;</span>
    <span class="k">for</span> <span class="p">(</span><span class="kt">int</span> <span class="n">j</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">j</span> <span class="o">&lt;</span> <span class="p">(</span><span class="o">*</span><span class="n">clusters</span><span class="p">)[</span><span class="n">i</span><span class="p">].</span><span class="n">indices</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">j</span><span class="p">)</span>
      <span class="n">cloud_out</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[(</span><span class="o">*</span><span class="n">clusters</span><span class="p">)[</span><span class="n">i</span><span class="p">].</span><span class="n">indices</span><span class="p">[</span><span class="n">j</span><span class="p">]].</span><span class="n">intensity</span> <span class="o">=</span> <span class="n">label</span><span class="p">;</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>When the output point cloud is opened with PCL&#8217;s standard PCD viewer, pressing &#8216;5&#8217; will switch to the intenisty channel visualization.
The too-small clusters will be colored red, the too-large clusters will be colored blue, and the actual clusters/objects of interest will be colored randomly in between yellow and cyan hues.</p>
</div>
<div class="section" id="compiling-and-running-the-program">
<h1>Compiling and running the program</h1>
<p>Add the following lines to your CMakeLists.txt</p>
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

<span class="nb">project</span><span class="p">(</span><span class="s">conditional_euclidean_clustering</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.7</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">conditional_euclidean_clustering</span> <span class="s">conditional_euclidean_clustering.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">conditional_euclidean_clustering</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it. Simply do:</p>
<blockquote>
<div>$ ./conditional_euclidean_clustering</div></blockquote>
<p>The resulting output point cloud can be opened like so:</p>
<blockquote>
<div>$ ./pcl_viewer output.pcd</div></blockquote>
<p>You should see something similar to this:</p>
<img alt="Output Cluster Extraction" class="align-center" src="_images/conditional_euclidean_clustering.jpg" />
<p>This result is sub-optimal but it gives an idea of what can be achieved with this class.
The mathematics and heuristics behind the customizable condition are now the responsibility of the user.</p>
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