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
    
    <title>Smoothing and normal estimation based on polynomial reconstruction</title>
    
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
            
  <div class="section" id="smoothing-and-normal-estimation-based-on-polynomial-reconstruction">
<span id="moving-least-squares"></span><h1>Smoothing and normal estimation based on polynomial reconstruction</h1>
<p>This tutorial explains how a Moving Least Squares (MLS) surface reconstruction
method can be used to smooth and resample noisy data. Please see an example in
the video below:</p>
<iframe title="Smoothing and normal estimation based on polynomial reconstruction" width="480" height="390" src="http://www.youtube.com/embed/FqHroDuo_I8?rel=0" frameborder="0" allowfullscreen></iframe><p>Some of the data irregularities (caused by small distance measurement errors)
are very hard to remove using statistical analysis. To create complete models,
glossy surfaces as well as occlusions in the data must be accounted for. In
situations where additional scans are impossible to acquire, a solution is to
use a resampling algorithm, which attempts to recreate the missing parts of the
surface by higher order polynomial interpolations between the surrounding data
points. By performing resampling, these small errors can be corrected and the
&#8220;double walls&#8221; artifacts resulted from registering multiple scans together can
be smoothed.</p>
<img alt="_images/resampling_1.jpg" src="_images/resampling_1.jpg" />
<p>On the left side of the figure above, we see the effect or estimating surface
normals in a dataset comprised of two registered point clouds together. Due to
alignment errors, the resultant normals are noisy. On the right side we see the
effects of surface normal estimation in the same dataset after it has been
smoothed with a Moving Least Squares algorithm. Plotting the curvatures at each
point as a measure of the eigenvalue relationship before and after resampling,
we obtain:</p>
<img alt="_images/resampling_2.jpg" src="_images/resampling_2.jpg" />
<p>To approximate the surface defined by a local neighborhood of points
<strong>p1</strong>, <strong>p2</strong> ... <strong>pk</strong> at a point <strong>q</strong> we use a bivariate polynomial height function
defined on a on a robustly computed reference plane.</p>
<iframe title="Removing noisy data through resampling" width="480" height="390" src="http://www.youtube.com/embed/N5AgC0KEcw0?rel=0" frameborder="0" allowfullscreen></iframe></div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>First, create a file, let&#8217;s say, <tt class="docutils literal"><span class="pre">resampling.cpp</span></tt> in your favorite
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
36</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/io/pcd_io.h&gt;</span>
<span class="cp">#include &lt;pcl/kdtree/kdtree_flann.h&gt;</span>
<span class="cp">#include &lt;pcl/surface/mls.h&gt;</span>

<span class="kt">int</span>
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="c1">// Load input file into a PointCloud&lt;T&gt; with an appropriate type</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="c1">// Load bun0.pcd -- should be available with the PCL archive in test </span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="s">&quot;bun0.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud</span><span class="p">);</span>

  <span class="c1">// Create a KD-Tree</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">tree</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="c1">// Output has the PointNormal type in order to store the normals calculated by MLS</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointNormal</span><span class="o">&gt;</span> <span class="n">mls_points</span><span class="p">;</span>

  <span class="c1">// Init object (second point type is for the normals, even if unused)</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">MovingLeastSquares</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointNormal</span><span class="o">&gt;</span> <span class="n">mls</span><span class="p">;</span>
 
  <span class="n">mls</span><span class="p">.</span><span class="n">setComputeNormals</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>

  <span class="c1">// Set parameters</span>
  <span class="n">mls</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">mls</span><span class="p">.</span><span class="n">setPolynomialFit</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
  <span class="n">mls</span><span class="p">.</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">tree</span><span class="p">);</span>
  <span class="n">mls</span><span class="p">.</span><span class="n">setSearchRadius</span> <span class="p">(</span><span class="mf">0.03</span><span class="p">);</span>

  <span class="c1">// Reconstruct</span>
  <span class="n">mls</span><span class="p">.</span><span class="n">process</span> <span class="p">(</span><span class="n">mls_points</span><span class="p">);</span>

  <span class="c1">// Save output</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">savePCDFile</span> <span class="p">(</span><span class="s">&quot;bun0-mls.pcd&quot;</span><span class="p">,</span> <span class="n">mls_points</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
<p>You should be able to find the input file at <em>pcl/test/bun0.pcd</em>.</p>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>Now, let&#8217;s break down the code piece by piece.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="c1">// Load bun0.pcd -- should be available with the PCL archive in test </span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="s">&quot;bun0.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud</span><span class="p">);</span>
</pre></div>
</div>
<p>as the example PCD has only XYZ coordinates, we load it into a
PointCloud&lt;PointXYZ&gt;. These fields are mandatory for the method, other ones are
allowed and will be preserved.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">mls</span><span class="p">.</span><span class="n">setComputeNormals</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
</pre></div>
</div>
<p>if normal estimation is not required, this step can be skipped.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">MovingLeastSquares</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointNormal</span><span class="o">&gt;</span> <span class="n">mls</span><span class="p">;</span>
</pre></div>
</div>
<p>the first template type is used for the input and output cloud. Only the XYZ
dimensions of the input are smoothed in the output.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">mls</span><span class="p">.</span><span class="n">setPolynomialFit</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
</pre></div>
</div>
<p>polynomial fitting could be disabled for speeding up smoothing. Please consult
the code API (<a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_moving_least_squares.html">MovingLeastSquares</a>) for default
values and additional parameters to control the smoothing process.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Save output</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">savePCDFile</span> <span class="p">(</span><span class="s">&quot;bun0-mls.pcd&quot;</span><span class="p">,</span> <span class="n">mls_points</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</div>
<p>if the normals and the original dimensions need to be in the same cloud, the
fields have to be concatenated.</p>
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

<span class="nb">project</span><span class="p">(</span><span class="s">resampling</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.2</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">resampling</span> <span class="s">resampling.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">resampling</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it. Simply do:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./resampling
</pre></div>
</div>
<p>You can view the smoothed cloud for example by executing:</p>
<div class="highlight-python"><div class="highlight"><pre>$ pcl_viewer bun0-mls.pcd
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