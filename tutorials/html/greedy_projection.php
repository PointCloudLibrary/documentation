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
    
    <title>Fast triangulation of unordered point clouds</title>
    
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
            
  <div class="section" id="fast-triangulation-of-unordered-point-clouds">
<span id="greedy-triangulation"></span><h1>Fast triangulation of unordered point clouds</h1>
<p>This tutorial explains how to run a greedy surface triangulation algorithm on a
PointCloud with normals, to obtain a triangle mesh based on projections of the
local neighborhoods. An example of the method&#8217;s output can be seen here:</p>
<iframe title="Surface Triangulation and Point Cloud Classification" width="480" height="390" src="http://www.youtube.com/embed/VALTnZCyWc0?rel=0" frameborder="0" allowfullscreen></iframe></div>
<div class="section" id="background-algorithm-and-parameters">
<h1>Background: algorithm and parameters</h1>
<p>The method works by maintaining a list of points from which the mesh can be
grown (&#8220;fringe&#8221; points) and extending it until all possible points are
connected. It can deal with unorganized points, coming from one or multiple
scans, and having multiple connected parts. It works best if the surface is
locally smooth and there are smooth transitions between areas with different
point densities.</p>
<p>Triangulation is performed locally, by projecting the local neighborhood of a
point along the point&#8217;s normal, and connecting unconnected points. Thus, the
following parameters can be set:</p>
<ul class="simple">
<li><em>setMaximumNearestNeighbors(unsigned)</em> and <em>setMu(double)</em> control the size of
the neighborhood. The former defines how many neighbors are searched for,
while the latter specifies the maximum acceptable distance for a point to be
considered, relative to the distance of the nearest point (in order to adjust
to changing densities). Typical values are 50-100 and 2.5-3 (or 1.5 for
grids).</li>
<li><em>setSearchRadius(double)</em> is practically the maximum edge length for every
triangle. This has to be set by the user such that to allow for the biggest
triangles that should be possible.</li>
<li><em>setMinimumAngle(double)</em> and <em>setMaximumAngle(double)</em> are the minimum and
maximum angles in each triangle. While the first is not guaranteed, the
second is. Typical values are 10 and 120 degrees (in radians).</li>
<li><em>setMaximumSurfaceAgle(double)</em> and <em>setNormalConsistency(bool)</em> are meant to
deal with the cases where there are sharp edges or corners and where two
sides of a surface run very close to each other. To achieve this, points are
not connected to the current point if their normals deviate more than the
specified angle (note that most surface normal estimation methods produce
smooth transitions between normal angles even at sharp edges). This angle is
computed as the angle between the lines defined by the normals (disregarding
the normal&#8217;s direction) if the normal-consistency-flag is not set, as not all
normal estimation methods can guarantee consistently oriented normals.
Typically, 45 degrees (in radians) and false works on most datasets.</li>
</ul>
<p>Please see the example below, and you can consult the following paper and its
references for more details:</p>
<div class="highlight-python"><div class="highlight"><pre>@InProceedings{Marton09ICRA,
  author    = {Zoltan Csaba Marton and Radu Bogdan Rusu and Michael Beetz},
  title     = {{On Fast Surface Reconstruction Methods for Large and Noisy Datasets}},
  booktitle = {Proceedings of the IEEE International Conference on Robotics and Automation (ICRA)},
  month     = {May 12-17},
  year      = {2009},
  address   = {Kobe, Japan},
}
</pre></div>
</div>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>First, create a file, let&#8217;s say, <tt class="docutils literal"><span class="pre">greedy_projection.cpp</span></tt> in your favorite
editor, and place the following code inside it:</p>
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
63</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/io/pcd_io.h&gt;</span>
<span class="cp">#include &lt;pcl/kdtree/kdtree_flann.h&gt;</span>
<span class="cp">#include &lt;pcl/features/normal_3d.h&gt;</span>
<span class="cp">#include &lt;pcl/surface/gp3.h&gt;</span>

<span class="kt">int</span>
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="c1">// Load input file into a PointCloud&lt;T&gt; with an appropriate type</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PCLPointCloud2</span> <span class="n">cloud_blob</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="s">&quot;bun0.pcd&quot;</span><span class="p">,</span> <span class="n">cloud_blob</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">fromPCLPointCloud2</span> <span class="p">(</span><span class="n">cloud_blob</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud</span><span class="p">);</span>
  <span class="c1">//* the data should be available in cloud</span>

  <span class="c1">// Normal estimation*</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">NormalEstimation</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span> <span class="n">n</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">normals</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">tree</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">tree</span><span class="o">-&gt;</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">n</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">n</span><span class="p">.</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">tree</span><span class="p">);</span>
  <span class="n">n</span><span class="p">.</span><span class="n">setKSearch</span> <span class="p">(</span><span class="mi">20</span><span class="p">);</span>
  <span class="n">n</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">normals</span><span class="p">);</span>
  <span class="c1">//* normals should not contain the point normals + surface curvatures</span>

  <span class="c1">// Concatenate the XYZ and normal fields*</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointNormal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_with_normals</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointNormal</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">concatenateFields</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud</span><span class="p">,</span> <span class="o">*</span><span class="n">normals</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud_with_normals</span><span class="p">);</span>
  <span class="c1">//* cloud_with_normals = cloud + normals</span>

  <span class="c1">// Create search tree*</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointNormal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">tree2</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointNormal</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">tree2</span><span class="o">-&gt;</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_with_normals</span><span class="p">);</span>

  <span class="c1">// Initialize objects</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">GreedyProjectionTriangulation</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointNormal</span><span class="o">&gt;</span> <span class="n">gp3</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PolygonMesh</span> <span class="n">triangles</span><span class="p">;</span>

  <span class="c1">// Set the maximum distance between connected points (maximum edge length)</span>
  <span class="n">gp3</span><span class="p">.</span><span class="n">setSearchRadius</span> <span class="p">(</span><span class="mf">0.025</span><span class="p">);</span>

  <span class="c1">// Set typical values for the parameters</span>
  <span class="n">gp3</span><span class="p">.</span><span class="n">setMu</span> <span class="p">(</span><span class="mf">2.5</span><span class="p">);</span>
  <span class="n">gp3</span><span class="p">.</span><span class="n">setMaximumNearestNeighbors</span> <span class="p">(</span><span class="mi">100</span><span class="p">);</span>
  <span class="n">gp3</span><span class="p">.</span><span class="n">setMaximumSurfaceAngle</span><span class="p">(</span><span class="n">M_PI</span><span class="o">/</span><span class="mi">4</span><span class="p">);</span> <span class="c1">// 45 degrees</span>
  <span class="n">gp3</span><span class="p">.</span><span class="n">setMinimumAngle</span><span class="p">(</span><span class="n">M_PI</span><span class="o">/</span><span class="mi">18</span><span class="p">);</span> <span class="c1">// 10 degrees</span>
  <span class="n">gp3</span><span class="p">.</span><span class="n">setMaximumAngle</span><span class="p">(</span><span class="mi">2</span><span class="o">*</span><span class="n">M_PI</span><span class="o">/</span><span class="mi">3</span><span class="p">);</span> <span class="c1">// 120 degrees</span>
  <span class="n">gp3</span><span class="p">.</span><span class="n">setNormalConsistency</span><span class="p">(</span><span class="nb">false</span><span class="p">);</span>

  <span class="c1">// Get result</span>
  <span class="n">gp3</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_with_normals</span><span class="p">);</span>
  <span class="n">gp3</span><span class="p">.</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">tree2</span><span class="p">);</span>
  <span class="n">gp3</span><span class="p">.</span><span class="n">reconstruct</span> <span class="p">(</span><span class="n">triangles</span><span class="p">);</span>

  <span class="c1">// Additional vertex information</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">parts</span> <span class="o">=</span> <span class="n">gp3</span><span class="p">.</span><span class="n">getPartIDs</span><span class="p">();</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">states</span> <span class="o">=</span> <span class="n">gp3</span><span class="p">.</span><span class="n">getPointStates</span><span class="p">();</span>

  <span class="c1">// Finish</span>
  <span class="k">return</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
<p>The input file you can find at pcl/test/bun0.pcd</p>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>Now, let&#8217;s break down the code piece by piece.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Load input file into a PointCloud&lt;T&gt; with an appropriate type</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PCLPointCloud2</span> <span class="n">cloud_blob</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="s">&quot;bun0.pcd&quot;</span><span class="p">,</span> <span class="n">cloud_blob</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">fromPCLPointCloud2</span> <span class="p">(</span><span class="n">cloud_blob</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud</span><span class="p">);</span>
  <span class="c1">//* the data should be available in cloud</span>
</pre></div>
</div>
<p>as the example PCD has only XYZ coordinates, we load it into a
PointCloud&lt;PointXYZ&gt;.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Normal estimation*</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">NormalEstimation</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span> <span class="n">n</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">normals</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">tree</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">tree</span><span class="o">-&gt;</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">n</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">n</span><span class="p">.</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">tree</span><span class="p">);</span>
  <span class="n">n</span><span class="p">.</span><span class="n">setKSearch</span> <span class="p">(</span><span class="mi">20</span><span class="p">);</span>
  <span class="n">n</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">normals</span><span class="p">);</span>
  <span class="c1">//* normals should not contain the point normals + surface curvatures</span>
</pre></div>
</div>
<p>the method requires normals, so they are estimated using the standard method
from PCL.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Concatenate the XYZ and normal fields*</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointNormal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_with_normals</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointNormal</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">concatenateFields</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud</span><span class="p">,</span> <span class="o">*</span><span class="n">normals</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud_with_normals</span><span class="p">);</span>
  <span class="c1">//* cloud_with_normals = cloud + normals</span>
</pre></div>
</div>
<p>Since coordinates and normals need to be in the same PointCloud, we create a PointNormal type point cloud.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Create search tree*</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointNormal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">tree2</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointNormal</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">tree2</span><span class="o">-&gt;</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_with_normals</span><span class="p">);</span>

  <span class="c1">// Initialize objects</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">GreedyProjectionTriangulation</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointNormal</span><span class="o">&gt;</span> <span class="n">gp3</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PolygonMesh</span> <span class="n">triangles</span><span class="p">;</span>
</pre></div>
</div>
<p>The above lines deal with the initialization of the required objects.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Set the maximum distance between connected points (maximum edge length)</span>
  <span class="n">gp3</span><span class="p">.</span><span class="n">setSearchRadius</span> <span class="p">(</span><span class="mf">0.025</span><span class="p">);</span>

  <span class="c1">// Set typical values for the parameters</span>
  <span class="n">gp3</span><span class="p">.</span><span class="n">setMu</span> <span class="p">(</span><span class="mf">2.5</span><span class="p">);</span>
  <span class="n">gp3</span><span class="p">.</span><span class="n">setMaximumNearestNeighbors</span> <span class="p">(</span><span class="mi">100</span><span class="p">);</span>
  <span class="n">gp3</span><span class="p">.</span><span class="n">setMaximumSurfaceAngle</span><span class="p">(</span><span class="n">M_PI</span><span class="o">/</span><span class="mi">4</span><span class="p">);</span> <span class="c1">// 45 degrees</span>
  <span class="n">gp3</span><span class="p">.</span><span class="n">setMinimumAngle</span><span class="p">(</span><span class="n">M_PI</span><span class="o">/</span><span class="mi">18</span><span class="p">);</span> <span class="c1">// 10 degrees</span>
  <span class="n">gp3</span><span class="p">.</span><span class="n">setMaximumAngle</span><span class="p">(</span><span class="mi">2</span><span class="o">*</span><span class="n">M_PI</span><span class="o">/</span><span class="mi">3</span><span class="p">);</span> <span class="c1">// 120 degrees</span>
  <span class="n">gp3</span><span class="p">.</span><span class="n">setNormalConsistency</span><span class="p">(</span><span class="nb">false</span><span class="p">);</span>
</pre></div>
</div>
<p>The above lines set the parameters, as explained above.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Get result</span>
  <span class="n">gp3</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_with_normals</span><span class="p">);</span>
  <span class="n">gp3</span><span class="p">.</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">tree2</span><span class="p">);</span>
  <span class="n">gp3</span><span class="p">.</span><span class="n">reconstruct</span> <span class="p">(</span><span class="n">triangles</span><span class="p">);</span>
</pre></div>
</div>
<p>The lines above set the input objects and perform the actual triangulation.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Additional vertex information</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">parts</span> <span class="o">=</span> <span class="n">gp3</span><span class="p">.</span><span class="n">getPartIDs</span><span class="p">();</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">states</span> <span class="o">=</span> <span class="n">gp3</span><span class="p">.</span><span class="n">getPointStates</span><span class="p">();</span>
</pre></div>
</div>
<p>for each point, the ID of the containing connected component and its &#8220;state&#8221;
(i.e. gp3.FREE, gp3.BOUNDARY or gp3.COMPLETED) can be retrieved.</p>
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

<span class="nb">project</span><span class="p">(</span><span class="s">greedy_projection</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.2</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">greedy_projection</span> <span class="s">greedy_projection.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">greedy_projection</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it. Simply do:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./greedy_projection
</pre></div>
</div>
</div>
<div class="section" id="saving-and-viewing-the-result">
<h1>Saving and viewing the result</h1>
<p>You can view the smoothed cloud for example by saving into a VTK file by:</p>
<div class="highlight-python"><div class="highlight"><pre><span class="c">#include &lt;pcl/io/vtk_io.h&gt;</span>
<span class="o">...</span>
<span class="n">saveVTKFile</span> <span class="p">(</span><span class="s">&quot;mesh.vtk&quot;</span><span class="p">,</span> <span class="n">triangles</span><span class="p">);</span>
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