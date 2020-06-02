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
    
    <title>How 3D Features work in PCL</title>
    
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
            
  <div class="section" id="how-3d-features-work-in-pcl">
<span id="how-3d-features-work"></span><h1>How 3D Features work in PCL</h1>
<p>This document presents an introduction to the 3D feature estimation
methodologies in PCL, and serves as a guide for users or developers that are
interested in the internals of the <cite>pcl::Feature</cite> class.</p>
</div>
<div class="section" id="theoretical-primer">
<h1>Theoretical primer</h1>
<p>From <a class="reference internal" href="#rusudissertation" id="id1">[RusuDissertation]</a>:</p>
<blockquote>
<div><p><em>In their native representation,</em> <strong>points</strong> <em>as defined in the concept of 3D mapping systems are simply represented using their Cartesian coordinates x, y, z, with respect to a given origin. Assuming that the origin of the coordinate system does not change over time, there could be two points p1 and p2 , acquired at t1 and t2 , having the same coordinates. Comparing these points however is an ill-posed problem, because even though they are equal with respect to some distance measure (e.g. Euclidean metric), they could be sampled on completely different surfaces, and thus represent totally different information when taken together with the other surrounding points in their vicinity. That is because there are no guarantees that the world has not changed between t1 and t2. Some acquisition devices might provide extra information for a sampled point, such as an intensity or surface remission value, or even a color, however that does not solve the problem completely and the comparison remains ambiguous.</em></p>
<p><em>Applications which need to compare points for various reasons require better characteristics and metrics to be able to distinguish between geometric surfaces. The concept of a 3D point as a singular entity with Cartesian coordinates therefore disappears, and a new concept, that of</em> <strong>local descriptor</strong> <em>takes its place. The literature is abundant of different naming schemes
describing the same conceptualization, such as</em> <strong>shape descriptors</strong> <em>or</em> <strong>geometric features</strong> <em>but for the remaining of this document they will be referred to as</em> <strong>point feature representations.</strong></p>
<p><em>...</em></p>
<p><em>By including the surrounding neighbors, the underlying sampled surface geometry can be inferred and captured in the feature formulation, which contributes to solving the ambiguity comparison problem. Ideally, the resultant features would be very similar (with respect to some metric) for points residing on the same or similar surfaces, and different for points found on different surfaces, as shown in the figure below. A</em> <strong>good</strong> <em>point feature representation distinguishes itself from a</em> <strong>bad</strong> <em>one, by being able to capture the same local surface characteristics in the presence of:</em></p>
<blockquote>
<div><ul class="simple">
<li><strong>rigid transformations</strong> - <em>that is, 3D rotations and 3D translations in the data should not influence the resultant feature vector F estimation;</em></li>
<li><strong>varying sampling density</strong> - <em>in principle, a local surface patch sampled more or less densely should have the same feature vector signature;</em></li>
<li><strong>noise</strong> - <em>the point feature representation must retain the same or very similar values in its feature vector in the presence of mild noise in the data.</em></li>
</ul>
<img alt="_images/good_features.jpg" class="align-center" src="_images/good_features.jpg" />
</div></blockquote>
</div></blockquote>
<p>In general, PCL features use approximate methods to compute the nearest neighbors of a query point, using fast kd-tree queries. There are two types of queries that we&#8217;re interested in:</p>
<ul class="simple">
<li>determine the <strong>k</strong> (user given parameter) neighbors of a query point (also known as <em>k-search</em>);</li>
<li>determine <strong>all the neighbors</strong> of a query point within a sphere of radius <strong>r</strong> (also known as <em>radius-search</em>).</li>
</ul>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">For a discussion on what the right <strong>k</strong> or <strong>r</strong> values should be, please see <a class="reference internal" href="#rusudissertation" id="id2">[RusuDissertation]</a>.</p>
</div>
</div>
<div class="section" id="terminology">
<h1>Terminology</h1>
<p>For the reminder of this article, we will make certain abbreviations and
introduce certain notations, to simplify the in-text explanations. Please see
the table below for a reference on each of the terms used.</p>
<table border="1" class="docutils">
<colgroup>
<col width="21%" />
<col width="79%" />
</colgroup>
<thead valign="bottom">
<tr class="row-odd"><th class="head">term</th>
<th class="head">explanation</th>
</tr>
</thead>
<tbody valign="top">
<tr class="row-even"><td>Foo</td>
<td>a class named <cite>Foo</cite></td>
</tr>
<tr class="row-odd"><td>FooPtr</td>
<td><p class="first">a boost shared pointer to a class <cite>Foo</cite>,</p>
<p class="last">e.g., <cite>boost::shared_ptr&lt;Foo&gt;</cite></p>
</td>
</tr>
<tr class="row-even"><td>FooConstPtr</td>
<td><p class="first">a const boost shared pointer to a class <cite>Foo</cite>,</p>
<p class="last">e.g., <cite>const boost::shared_ptr&lt;const Foo&gt;</cite></p>
</td>
</tr>
</tbody>
</table>
</div>
<div class="section" id="how-to-pass-the-input">
<h1>How to pass the input</h1>
<p>As almost all classes in PCL that inherit from the base <cite>pcl::PCLBase</cite> class,
the <cite>pcl::Feature</cite> class accepts input data in two different ways:</p>
<blockquote>
<div><ol class="arabic">
<li><p class="first">an entire point cloud dataset, given via <strong>setInputCloud (PointCloudConstPtr &amp;)</strong> - <strong>mandatory</strong></p>
<p>Any feature estimation class with attempt to estimate a feature at <strong>every</strong> point in the given input cloud.</p>
</li>
<li><p class="first">a subset of a point cloud dataset, given via <strong>setInputCloud (PointCloudConstPtr &amp;)</strong> and <strong>setIndices (IndicesConstPtr &amp;)</strong> - <strong>optional</strong></p>
<p>Any feature estimation class will attempt to estimate a feature at every point in the given input cloud that has an index in the given indices list. <em>By default, if no set of indices is given, all points in the cloud will be considered.*</em></p>
</li>
</ol>
</div></blockquote>
<p>In addition, the set of point neighbors to be used, can be specified through an additional call, <strong>setSearchSurface (PointCloudConstPtr &amp;)</strong>. This call is optional, and when the search surface is not given, the input point cloud dataset is used instead by default.</p>
<p>Because <strong>setInputCloud()</strong> is always required, there are up to four combinations that we can create using <em>&lt;setInputCloud(), setIndices(), setSearchSurface()&gt;</em>. Say we have two point clouds, P={p_1, p_2, ...p_n} and Q={q_1, q_2, ..., q_n}. The image below presents all four cases:</p>
<img alt="_images/features_input_explained.png" class="align-center" src="_images/features_input_explained.png" />
<ul>
<li><p class="first"><strong>setIndices() = false, setSearchSurface() = false</strong> - this is without a doubt the most used case in PCL, where the user is just feeding in a single PointCloud dataset and expects a certain feature estimated at <em>all the points in the cloud</em>.</p>
<p>Since we do not expect to maintain different implementation copies based on whether a set of indices and/or the search surface is given, whenever <strong>indices = false</strong>, PCL creates a set of internal indices (as a <cite>std::vector&lt;int&gt;</cite>) that basically point to the entire dataset (indices=1..N, where N is the number of points in the cloud).</p>
<p>In the figure above, this corresponds to the leftmost case. First, we estimate the nearest neighbors of p_1, then the nearest neighbors of p_2, and so on, until we exhaust all the points in P.</p>
</li>
<li><p class="first"><strong>setIndices() = true, setSearchSurface() = false</strong> - as previously mentioned, the feature estimation method will only compute features for those points which have an index in the given indices vector;</p>
<p>In the figure above, this corresponds to the second case. Here, we assume that p_2&#8217;s index is not part of the indices vector given, so no neighbors or features will be estimated at p2.</p>
</li>
<li><p class="first"><strong>setIndices() = false, setSearchSurface() = true</strong> - as in the first case, features will be estimated for all points given as input, but, the underlying neighboring surface given in <strong>setSearchSurface()</strong> will be used to obtain nearest neighbors for the input points, rather than the input cloud itself;</p>
<p>In the figure above, this corresponds to the third case. If Q={q_1, q_2} is another cloud given as input, different than P, and P is the search surface for Q, then the neighbors of q_1 and q_2 will be computed from P.</p>
</li>
<li><p class="first"><strong>setIndices() = true, setSearchSurface() = true</strong> - this is probably the rarest case, where both indices and a search surface is given. In this case, features will be estimated for only a subset from the &lt;input, indices&gt; pair, using the search surface information given in <strong>setSearchSurface()</strong>.</p>
<p>Finally, un the figure above, this corresponds to the last (rightmost) case. Here, we assume that q_2&#8217;s index is not part of the indices vector given for Q, so no neighbors or features will be estimated at q2.</p>
</li>
</ul>
<p>The most useful example when <strong>setSearchSurface()</strong> should be used, is when we have a very dense input dataset, but we do not want to estimate features at all the points in it, but rather at some keypoints discovered using the methods in <cite>pcl_keypoints</cite>, or at a downsampled version of the cloud (e.g., obtained using a <cite>pcl::VoxelGrid&lt;T&gt;</cite> filter). In this case, we pass the downsampled/keypoints input via <strong>setInputCloud()</strong>, and the original data as <strong>setSearchSurface()</strong>.</p>
</div>
<div class="section" id="an-example-for-normal-estimation">
<h1>An example for normal estimation</h1>
<p>Once determined, the neighboring points of a query point can be used to estimate a local feature representation that captures the geometry of the underlying sampled surface around the query point. An important problem in describing the geometry of the surface is to first infer its orientation in a coordinate system, that is, estimate its normal. Surface normals are important properties of a surface and are heavily used in many areas such as computer graphics applications to apply the correct light sources that generate shadings and other visual effects (See <a class="reference internal" href="#rusudissertation" id="id3">[RusuDissertation]</a> for more information).</p>
<p>The following code snippet will estimate a set of surface normals for all the points in the input dataset.</p>
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
28</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/features/normal_3d.h&gt;</span>

<span class="p">{</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="p">...</span> <span class="n">read</span><span class="p">,</span> <span class="n">pass</span> <span class="n">in</span> <span class="n">or</span> <span class="n">create</span> <span class="n">a</span> <span class="n">point</span> <span class="n">cloud</span> <span class="p">...</span>

  <span class="c1">// Create the normal estimation class, and pass the input dataset to it</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">NormalEstimation</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span> <span class="n">ne</span><span class="p">;</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>

  <span class="c1">// Create an empty kdtree representation, and pass it to the normal estimation object.</span>
  <span class="c1">// Its content will be filled inside the object, based on the given input dataset (as no other search surface is given).</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">tree</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">tree</span><span class="p">);</span>

  <span class="c1">// Output datasets</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_normals</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="c1">// Use all neighbors in a sphere of radius 3cm</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="mf">0.03</span><span class="p">);</span>

  <span class="c1">// Compute the features</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_normals</span><span class="p">);</span>

  <span class="c1">// cloud_normals-&gt;points.size () should have the same size as the input cloud-&gt;points.size ()</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
<p>The following code snippet will estimate a set of surface normals for a subset of the points in the input dataset.</p>
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
36</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/features/normal_3d.h&gt;</span>

<span class="p">{</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="p">...</span> <span class="n">read</span><span class="p">,</span> <span class="n">pass</span> <span class="n">in</span> <span class="n">or</span> <span class="n">create</span> <span class="n">a</span> <span class="n">point</span> <span class="n">cloud</span> <span class="p">...</span>

  <span class="c1">// Create a set of indices to be used. For simplicity, we&#39;re going to be using the first 10% of the points in cloud</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">indices</span> <span class="p">(</span><span class="n">floor</span> <span class="p">(</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">/</span> <span class="mi">10</span><span class="p">));</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">indices</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span> <span class="n">indices</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="o">=</span> <span class="n">i</span><span class="p">;</span>

  <span class="c1">// Create the normal estimation class, and pass the input dataset to it</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">NormalEstimation</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span> <span class="n">ne</span><span class="p">;</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>

  <span class="c1">// Pass the indices</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="n">indicesptr</span> <span class="p">(</span><span class="k">new</span> <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">indices</span><span class="p">));</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">setIndices</span> <span class="p">(</span><span class="n">indicesptr</span><span class="p">);</span>

  <span class="c1">// Create an empty kdtree representation, and pass it to the normal estimation object.</span>
  <span class="c1">// Its content will be filled inside the object, based on the given input dataset (as no other search surface is given).</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">tree</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">tree</span><span class="p">);</span>

  <span class="c1">// Output datasets</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_normals</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="c1">// Use all neighbors in a sphere of radius 3cm</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="mf">0.03</span><span class="p">);</span>

  <span class="c1">// Compute the features</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_normals</span><span class="p">);</span>

  <span class="c1">// cloud_normals-&gt;points.size () should have the same size as the input indicesptr-&gt;size ()</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
<p>Finally, the following code snippet will estimate a set of surface normals for all the points in the input dataset, but will estimate their nearest neighbors using another dataset. As previously mentioned, a good usecase for this is when the input is a downsampled version of the surface.</p>
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
34</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/features/normal_3d.h&gt;</span>

<span class="p">{</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_downsampled</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="p">...</span> <span class="n">read</span><span class="p">,</span> <span class="n">pass</span> <span class="n">in</span> <span class="n">or</span> <span class="n">create</span> <span class="n">a</span> <span class="n">point</span> <span class="n">cloud</span> <span class="p">...</span>

  <span class="p">...</span> <span class="n">create</span> <span class="n">a</span> <span class="n">downsampled</span> <span class="n">version</span> <span class="n">of</span> <span class="n">it</span> <span class="p">...</span>

  <span class="c1">// Create the normal estimation class, and pass the input dataset to it</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">NormalEstimation</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span> <span class="n">ne</span><span class="p">;</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_downsampled</span><span class="p">);</span>

  <span class="c1">// Pass the original data (before downsampling) as the search surface</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">setSearchSurface</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>

  <span class="c1">// Create an empty kdtree representation, and pass it to the normal estimation object.</span>
  <span class="c1">// Its content will be filled inside the object, based on the given surface dataset.</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">tree</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">tree</span><span class="p">);</span>

  <span class="c1">// Output datasets</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_normals</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="c1">// Use all neighbors in a sphere of radius 3cm</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="mf">0.03</span><span class="p">);</span>

  <span class="c1">// Compute the features</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_normals</span><span class="p">);</span>

  <span class="c1">// cloud_normals-&gt;points.size () should have the same size as the input cloud_downsampled-&gt;points.size ()</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
<table class="docutils citation" frame="void" id="rusudissertation" rules="none">
<colgroup><col class="label" /><col /></colgroup>
<tbody valign="top">
<tr><td class="label">[RusuDissertation]</td><td><em>(<a class="fn-backref" href="#id1">1</a>, <a class="fn-backref" href="#id2">2</a>, <a class="fn-backref" href="#id3">3</a>)</em> <a class="reference external" href="http://files.rbrusu.com/publications/RusuPhDThesis.pdf">http://files.rbrusu.com/publications/RusuPhDThesis.pdf</a></td></tr>
</tbody>
</table>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">&#64;PhDThesis{RusuDoctoralDissertation,
author = {Radu Bogdan Rusu},
title  = {Semantic 3D Object Maps for Everyday Manipulation in Human Living Environments},
school = {Computer Science department, Technische Universitaet Muenchen, Germany},
year   = {2009},
month  = {October}
}</p>
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