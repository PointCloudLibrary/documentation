<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation - Point Cloud Library (PCL)</title>
</head>

<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8" />
    <title>Removing outliers using a StatisticalOutlierRemoval filter &#8212; PCL 0.0 documentation</title>
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
            
  <div class="section" id="removing-outliers-using-a-statisticaloutlierremoval-filter">
<span id="statistical-outlier-removal"></span><h1>Removing outliers using a StatisticalOutlierRemoval filter</h1>
<p>In this tutorial we will learn how to remove noisy measurements, e.g. outliers,
from a point cloud dataset using statistical analysis techniques.</p>
<iframe title="Removing outliers using a StatisticalOutlierRemoval filter" width="480" height="390" src="http://www.youtube.com/embed/RjQPp2_GRnI?rel=0" frameborder="0" allowfullscreen></iframe></div>
<div class="section" id="background">
<h1>Background</h1>
<p>Laser scans typically generate point cloud datasets of varying point densities.
Additionally, measurement errors lead to sparse outliers which corrupt the
results even more.  This complicates the estimation of local point cloud
characteristics such as surface normals or curvature changes, leading to
erroneous values, which in turn might cause point cloud registration failures.
Some of these irregularities can be solved by performing a statistical analysis
on each point’s neighborhood, and trimming those which do not meet a certain
criterion.  Our sparse outlier removal is based on the computation of the
distribution of point to neighbors distances in the input dataset. For each
point, we compute the mean distance from it to all its neighbors. By assuming
that the resulted distribution is Gaussian with a mean and a standard
deviation, all points whose mean distances are outside an interval defined by
the global distances mean and standard deviation can be considered as outliers
and trimmed from the dataset.</p>
<p>The following picture shows the effects of the sparse outlier analysis and
removal: the original dataset is shown on the left, while the resultant one on
the right. The graphic shows the mean k-nearest neighbor distances in a point
neighborhood before and after filtering.</p>
<img alt="_images/statistical_removal_2.jpg" src="_images/statistical_removal_2.jpg" />
</div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>First, download the dataset <a class="reference external" href="https://raw.github.com/PointCloudLibrary/data/master/tutorials/table_scene_lms400.pcd">table_scene_lms400.pcd</a>
and save it somewhere to disk.</p>
<p>Then, create a file, let’s say, <code class="docutils literal notranslate"><span class="pre">statistical_removal.cpp</span></code> in your favorite
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
38</pre></div></td><td class="code"><div class="highlight"><pre><span></span><span class="cp">#include</span> <span class="cpf">&lt;iostream&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/io/pcd_io.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/point_types.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/filters/statistical_outlier_removal.h&gt;</span><span class="cp"></span>

<span class="kt">int</span>
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_filtered</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="c1">// Fill in the cloud data</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PCDReader</span> <span class="n">reader</span><span class="p">;</span>
  <span class="c1">// Replace the path below with the path where you saved your file</span>
  <span class="n">reader</span><span class="p">.</span><span class="n">read</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;table_scene_lms400.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Cloud before filtering: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="o">*</span><span class="n">cloud</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="c1">// Create the filtering object</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">StatisticalOutlierRemoval</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">sor</span><span class="p">;</span>
  <span class="n">sor</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">sor</span><span class="p">.</span><span class="n">setMeanK</span> <span class="p">(</span><span class="mi">50</span><span class="p">);</span>
  <span class="n">sor</span><span class="p">.</span><span class="n">setStddevMulThresh</span> <span class="p">(</span><span class="mf">1.0</span><span class="p">);</span>
  <span class="n">sor</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_filtered</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Cloud after filtering: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="o">*</span><span class="n">cloud_filtered</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PCDWriter</span> <span class="n">writer</span><span class="p">;</span>
  <span class="n">writer</span><span class="p">.</span><span class="n">write</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;table_scene_lms400_inliers.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud_filtered</span><span class="p">,</span> <span class="nb">false</span><span class="p">);</span>

  <span class="n">sor</span><span class="p">.</span><span class="n">setNegative</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
  <span class="n">sor</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_filtered</span><span class="p">);</span>
  <span class="n">writer</span><span class="p">.</span><span class="n">write</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;table_scene_lms400_outliers.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud_filtered</span><span class="p">,</span> <span class="nb">false</span><span class="p">);</span>

  <span class="k">return</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>Now, let’s break down the code piece by piece.</p>
<p>The following lines of code will read the point cloud data from disk.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="c1">// Fill in the cloud data</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PCDReader</span> <span class="n">reader</span><span class="p">;</span>
  <span class="c1">// Replace the path below with the path where you saved your file</span>
  <span class="n">reader</span><span class="p">.</span><span class="n">read</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;table_scene_lms400.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud</span><span class="p">);</span>
</pre></div>
</div>
<p>Then, a <em>pcl::StatisticalOutlierRemoval</em> filter is created. The number of
neighbors to analyze for each point is set to 50, and the standard deviation
multiplier to 1. What this means is that all points who have a distance larger
than 1 standard deviation of the mean distance to the query point will be
marked as outliers and removed. The output is computed and stored in
<em>cloud_filtered</em>.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="c1">// Create the filtering object</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">StatisticalOutlierRemoval</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">sor</span><span class="p">;</span>
  <span class="n">sor</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">sor</span><span class="p">.</span><span class="n">setMeanK</span> <span class="p">(</span><span class="mi">50</span><span class="p">);</span>
  <span class="n">sor</span><span class="p">.</span><span class="n">setStddevMulThresh</span> <span class="p">(</span><span class="mf">1.0</span><span class="p">);</span>
  <span class="n">sor</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_filtered</span><span class="p">);</span>
</pre></div>
</div>
<p>The remaining data (inliers) is written to disk for later inspection.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="n">pcl</span><span class="o">::</span><span class="n">PCDWriter</span> <span class="n">writer</span><span class="p">;</span>
  <span class="n">writer</span><span class="p">.</span><span class="n">write</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;table_scene_lms400_inliers.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud_filtered</span><span class="p">,</span> <span class="nb">false</span><span class="p">);</span>
</pre></div>
</div>
<p>Then, the filter is called with the same parameters, but with the output
negated, to obtain the outliers (e.g., the points that were filtered).</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="n">sor</span><span class="p">.</span><span class="n">setNegative</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
  <span class="n">sor</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_filtered</span><span class="p">);</span>
</pre></div>
</div>
<p>And the data is written back to disk.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="n">writer</span><span class="p">.</span><span class="n">write</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;table_scene_lms400_outliers.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud_filtered</span><span class="p">,</span> <span class="nb">false</span><span class="p">);</span>
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

<span class="nb">project</span><span class="p">(</span><span class="s">statistical_removal</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.2</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">statistical_removal</span> <span class="s">statistical_removal.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">statistical_removal</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it. Simply do:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>$ ./statistical_removal
</pre></div>
</div>
<p>You will see something similar to:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">Cloud</span> <span class="n">before</span> <span class="n">filtering</span><span class="p">:</span>
<span class="n">header</span><span class="p">:</span>
<span class="n">seq</span><span class="p">:</span> <span class="mi">0</span>
<span class="n">stamp</span><span class="p">:</span> <span class="mf">0.000000000</span>
<span class="n">frame_id</span><span class="p">:</span>
<span class="n">points</span><span class="p">[]:</span> <span class="mi">460400</span>
<span class="n">width</span><span class="p">:</span> <span class="mi">460400</span>
<span class="n">height</span><span class="p">:</span> <span class="mi">1</span>
<span class="n">is_dense</span><span class="p">:</span> <span class="mi">0</span>

<span class="n">Cloud</span> <span class="n">after</span> <span class="n">filtering</span><span class="p">:</span>
<span class="n">header</span><span class="p">:</span>
<span class="n">seq</span><span class="p">:</span> <span class="mi">0</span>
<span class="n">stamp</span><span class="p">:</span> <span class="mf">0.000000000</span>
<span class="n">frame_id</span><span class="p">:</span>
<span class="n">points</span><span class="p">[]:</span> <span class="mi">429398</span>
<span class="n">width</span><span class="p">:</span> <span class="mi">429398</span>
<span class="n">height</span><span class="p">:</span> <span class="mi">1</span>
<span class="n">is_dense</span><span class="p">:</span> <span class="mi">0</span>
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