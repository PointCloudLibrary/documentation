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
    
    <title>Identifying ground returns using ProgressiveMorphologicalFilter segmentation</title>
    
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
            
  <div class="section" id="progressive-morphological-filtering">
<span id="identifying-ground-returns-using-progressivemorphologicalfilter-segmentation"></span><h1>Identifying ground returns using ProgressiveMorphologicalFilter segmentation</h1>
<p>Implements the Progressive Morphological Filter for segmentation of ground
points.</p>
</div>
<div class="section" id="background">
<h1>Background</h1>
<p>A complete description of the algorithm can be found in the article <a class="reference external" href="http://users.cis.fiu.edu/~chens/PDF/TGRS.pdf">&#8220;A
Progressive Morphological Filter for Removing Nonground Measurements from
Airborne LIDAR Data&#8221;</a> by K.
Zhang, S.  Chen, D. Whitman, M. Shyu, J. Yan, and C. Zhang.</p>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>First, download the dataset <a class="reference external" href="https://raw.github.com/PointCloudLibrary/data/master/terrain/samp11-utm.pcd">samp11-utm.pcd</a>
and save it somewhere to disk.</p>
<p>Then, create a file, let&#8217;s say, <tt class="docutils literal"><span class="pre">bare_earth.cpp</span></tt> in your favorite editor, and
place the following inside it:</p>
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
53</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;iostream&gt;</span>
<span class="cp">#include &lt;pcl/io/pcd_io.h&gt;</span>
<span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/filters/extract_indices.h&gt;</span>
<span class="cp">#include &lt;pcl/segmentation/progressive_morphological_filter.h&gt;</span>

<span class="kt">int</span>
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_filtered</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointIndicesPtr</span> <span class="n">ground</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointIndices</span><span class="p">);</span>

  <span class="c1">// Fill in the cloud data</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PCDReader</span> <span class="n">reader</span><span class="p">;</span>
  <span class="c1">// Replace the path below with the path where you saved your file</span>
  <span class="n">reader</span><span class="p">.</span><span class="n">read</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;samp11-utm.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Cloud before filtering: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="o">*</span><span class="n">cloud</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="c1">// Create the filtering object</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">ProgressiveMorphologicalFilter</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">pmf</span><span class="p">;</span>
  <span class="n">pmf</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">pmf</span><span class="p">.</span><span class="n">setMaxWindowSize</span> <span class="p">(</span><span class="mi">20</span><span class="p">);</span>
  <span class="n">pmf</span><span class="p">.</span><span class="n">setSlope</span> <span class="p">(</span><span class="mf">1.0f</span><span class="p">);</span>
  <span class="n">pmf</span><span class="p">.</span><span class="n">setInitialDistance</span> <span class="p">(</span><span class="mf">0.5f</span><span class="p">);</span>
  <span class="n">pmf</span><span class="p">.</span><span class="n">setMaxDistance</span> <span class="p">(</span><span class="mf">3.0f</span><span class="p">);</span>
  <span class="n">pmf</span><span class="p">.</span><span class="n">extract</span> <span class="p">(</span><span class="n">ground</span><span class="o">-&gt;</span><span class="n">indices</span><span class="p">);</span>

  <span class="c1">// Create the filtering object</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">ExtractIndices</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">extract</span><span class="p">;</span>
  <span class="n">extract</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">extract</span><span class="p">.</span><span class="n">setIndices</span> <span class="p">(</span><span class="n">ground</span><span class="p">);</span>
  <span class="n">extract</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_filtered</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Ground cloud after filtering: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="o">*</span><span class="n">cloud_filtered</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PCDWriter</span> <span class="n">writer</span><span class="p">;</span>
  <span class="n">writer</span><span class="p">.</span><span class="n">write</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;samp11-utm_ground.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud_filtered</span><span class="p">,</span> <span class="nb">false</span><span class="p">);</span>

  <span class="c1">// Extract non-ground returns</span>
  <span class="n">extract</span><span class="p">.</span><span class="n">setNegative</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
  <span class="n">extract</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_filtered</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Object cloud after filtering: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="o">*</span><span class="n">cloud_filtered</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="n">writer</span><span class="p">.</span><span class="n">write</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;samp11-utm_object.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud_filtered</span><span class="p">,</span> <span class="nb">false</span><span class="p">);</span>

  <span class="k">return</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>Now, let&#8217;s break down the code piece by piece.</p>
<p>The following lines of code will read the point cloud data from disk.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Fill in the cloud data</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PCDReader</span> <span class="n">reader</span><span class="p">;</span>
  <span class="c1">// Replace the path below with the path where you saved your file</span>
  <span class="n">reader</span><span class="p">.</span><span class="n">read</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;samp11-utm.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud</span><span class="p">);</span>
</pre></div>
</div>
<p>Then, a <em>pcl::ProgressiveMorphologicalFilter</em> filter is created. The output
(the indices of ground returns) is computed and stored in <em>ground</em>.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Create the filtering object</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">ProgressiveMorphologicalFilter</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">pmf</span><span class="p">;</span>
  <span class="n">pmf</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">pmf</span><span class="p">.</span><span class="n">setMaxWindowSize</span> <span class="p">(</span><span class="mi">20</span><span class="p">);</span>
  <span class="n">pmf</span><span class="p">.</span><span class="n">setSlope</span> <span class="p">(</span><span class="mf">1.0f</span><span class="p">);</span>
  <span class="n">pmf</span><span class="p">.</span><span class="n">setInitialDistance</span> <span class="p">(</span><span class="mf">0.5f</span><span class="p">);</span>
  <span class="n">pmf</span><span class="p">.</span><span class="n">setMaxDistance</span> <span class="p">(</span><span class="mf">3.0f</span><span class="p">);</span>
  <span class="n">pmf</span><span class="p">.</span><span class="n">extract</span> <span class="p">(</span><span class="n">ground</span><span class="o">-&gt;</span><span class="n">indices</span><span class="p">);</span>
</pre></div>
</div>
<p>To extract the ground points, the ground indices are passed into a
<em>pcl::ExtractIndices</em> filter.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Create the filtering object</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">ExtractIndices</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">extract</span><span class="p">;</span>
  <span class="n">extract</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">extract</span><span class="p">.</span><span class="n">setIndices</span> <span class="p">(</span><span class="n">ground</span><span class="p">);</span>
  <span class="n">extract</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_filtered</span><span class="p">);</span>
</pre></div>
</div>
<p>The ground returns are written to disk for later inspection.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">PCDWriter</span> <span class="n">writer</span><span class="p">;</span>
  <span class="n">writer</span><span class="p">.</span><span class="n">write</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;samp11-utm_ground.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud_filtered</span><span class="p">,</span> <span class="nb">false</span><span class="p">);</span>
</pre></div>
</div>
<p>Then, the filter is called with the same parameters, but with the output
negated, to obtain the non-ground (object) returns.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Extract non-ground returns</span>
  <span class="n">extract</span><span class="p">.</span><span class="n">setNegative</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
  <span class="n">extract</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_filtered</span><span class="p">);</span>
</pre></div>
</div>
<p>And the data is written back to disk.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">writer</span><span class="p">.</span><span class="n">write</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;samp11-utm_object.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud_filtered</span><span class="p">,</span> <span class="nb">false</span><span class="p">);</span>
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

<span class="nb">project</span><span class="p">(</span><span class="s">bare_earth</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.7.2</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">bare_earth</span> <span class="s">bare_earth.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">bare_earth</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it. Simply do:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./bare_earth
</pre></div>
</div>
<p>You will see something similar to:</p>
<div class="highlight-python"><div class="highlight"><pre>Cloud before filtering:
points[]: 38010
width: 38010
height: 1
is_dense: 1
sensor origin (xyz): [0, 0, 0] / orientation (xyzw): [0, 0, 0, 1]

Ground cloud after filtering:
points[]: 18667
width: 18667
height: 1
is_dense: 1
sensor origin (xyz): [0, 0, 0] / orientation (xyzw): [0, 0, 0, 1]

Object cloud after filtering:
points[]: 19343
width: 19343
height: 1
is_dense: 1
sensor origin (xyz): [0, 0, 0] / orientation (xyzw): [0, 0, 0, 1]
</pre></div>
</div>
<p>You can also look at your outputs samp11-utm_inliers.pcd and
samp11-utm_outliers.pcd:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./pcl_viewer samp11-utm_ground.pcd samp11-utm_object.pcd
</pre></div>
</div>
<p>You are now able to see both the ground and object returns in one viewer. You
should see something similar to this:</p>
<a class="reference internal image-reference" href="_images/progressive_morphological_filter.png"><img alt="Output Progressive Morphological Filter" class="align-center" src="_images/progressive_morphological_filter.png" style="width: 600px;" /></a>
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