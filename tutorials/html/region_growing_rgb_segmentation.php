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
    
    <title>Color-based region growing segmentation</title>
    
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
            
  <div class="section" id="color-based-region-growing-segmentation">
<span id="region-growing-rgb-segmentation"></span><h1>Color-based region growing segmentation</h1>
<p>In this tutorial we will learn how to use the color-based region growing algorithm implemented in the <tt class="docutils literal"><span class="pre">pcl::RegionGrowingRGB</span></tt> class.
This algorithm is based on the same concept as the <tt class="docutils literal"><span class="pre">pcl::RegionGrowing</span></tt> that is described in the <a class="reference internal" href="region_growing_segmentation.php#region-growing-segmentation"><em>Region growing segmentation</em></a> tutorial.
If you are interested in the understanding of the base idea, please refer to the mentioned tutorial.</p>
<p>There are two main differences in the color-based algorithm. The first one is that it uses color instead of normals.
The second is that it uses the merging algorithm for over- and under- segmentation control.
Let&#8217;s take a look at how it is done. After the segmentation, an attempt for merging clusters with close colors is made.
Two neighbouring clusters with a small difference between average color are merged together.
Then the second merging step takes place. During this step every single cluster is verified by the number of points that it contains.
If this number is less than the user-defined value than current cluster is merged with the closest neighbouring cluster.</p>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>This tutorial requires colored cloud. You can use <a class="reference external" href="https://raw.github.com/PointCloudLibrary/data/master/tutorials/region_growing_rgb_tutorial.pcd">this one</a>.
Next what you need to do is to create a file <tt class="docutils literal"><span class="pre">region_growing_rgb_segmentation.cpp</span></tt> in any editor you prefer and copy the following code inside of it:</p>
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
51</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;iostream&gt;</span>
<span class="cp">#include &lt;vector&gt;</span>
<span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/io/pcd_io.h&gt;</span>
<span class="cp">#include &lt;pcl/search/search.h&gt;</span>
<span class="cp">#include &lt;pcl/search/kdtree.h&gt;</span>
<span class="cp">#include &lt;pcl/visualization/cloud_viewer.h&gt;</span>
<span class="cp">#include &lt;pcl/filters/passthrough.h&gt;</span>
<span class="cp">#include &lt;pcl/segmentation/region_growing_rgb.h&gt;</span>

<span class="kt">int</span>
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">Search</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">tree</span> <span class="o">=</span> <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">Search</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="k">if</span> <span class="p">(</span> <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;region_growing_rgb_tutorial.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud</span><span class="p">)</span> <span class="o">==</span> <span class="o">-</span><span class="mi">1</span> <span class="p">)</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Cloud reading failed.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">IndicesPtr</span> <span class="n">indices</span> <span class="p">(</span><span class="k">new</span> <span class="n">std</span><span class="o">::</span><span class="n">vector</span> <span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PassThrough</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="n">pass</span><span class="p">;</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">setFilterFieldName</span> <span class="p">(</span><span class="s">&quot;z&quot;</span><span class="p">);</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">setFilterLimits</span> <span class="p">(</span><span class="mf">0.0</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">);</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">indices</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">RegionGrowingRGB</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="n">reg</span><span class="p">;</span>
  <span class="n">reg</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">reg</span><span class="p">.</span><span class="n">setIndices</span> <span class="p">(</span><span class="n">indices</span><span class="p">);</span>
  <span class="n">reg</span><span class="p">.</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">tree</span><span class="p">);</span>
  <span class="n">reg</span><span class="p">.</span><span class="n">setDistanceThreshold</span> <span class="p">(</span><span class="mi">10</span><span class="p">);</span>
  <span class="n">reg</span><span class="p">.</span><span class="n">setPointColorThreshold</span> <span class="p">(</span><span class="mi">6</span><span class="p">);</span>
  <span class="n">reg</span><span class="p">.</span><span class="n">setRegionColorThreshold</span> <span class="p">(</span><span class="mi">5</span><span class="p">);</span>
  <span class="n">reg</span><span class="p">.</span><span class="n">setMinClusterSize</span> <span class="p">(</span><span class="mi">600</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">vector</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointIndices</span><span class="o">&gt;</span> <span class="n">clusters</span><span class="p">;</span>
  <span class="n">reg</span><span class="p">.</span><span class="n">extract</span> <span class="p">(</span><span class="n">clusters</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">colored_cloud</span> <span class="o">=</span> <span class="n">reg</span><span class="p">.</span><span class="n">getColoredCloud</span> <span class="p">();</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">CloudViewer</span> <span class="n">viewer</span> <span class="p">(</span><span class="s">&quot;Cluster viewer&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">showCloud</span> <span class="p">(</span><span class="n">colored_cloud</span><span class="p">);</span>
  <span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">viewer</span><span class="p">.</span><span class="n">wasStopped</span> <span class="p">())</span>
  <span class="p">{</span>
    <span class="n">boost</span><span class="o">::</span><span class="n">this_thread</span><span class="o">::</span><span class="n">sleep</span> <span class="p">(</span><span class="n">boost</span><span class="o">::</span><span class="n">posix_time</span><span class="o">::</span><span class="n">microseconds</span> <span class="p">(</span><span class="mi">100</span><span class="p">));</span>
  <span class="p">}</span>

  <span class="k">return</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>Now let&#8217;s study out what is the purpose of this code.</p>
<p>Let&#8217;s take a look at first lines that are of interest:</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="k">if</span> <span class="p">(</span> <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;region_growing_rgb_tutorial.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud</span><span class="p">)</span> <span class="o">==</span> <span class="o">-</span><span class="mi">1</span> <span class="p">)</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Cloud reading failed.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>They are simply loading the cloud from the .pcd file. Note that points must have the color.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">RegionGrowingRGB</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="n">reg</span><span class="p">;</span>
</pre></div>
</div>
<p>This line is responsible for <tt class="docutils literal"><span class="pre">pcl::RegionGrowingRGB</span></tt> instantiation. This class has two parameters:</p>
<ul class="simple">
<li>PointT - type of points to use(in the given example it is <tt class="docutils literal"><span class="pre">pcl::PointXYZRGB</span></tt>)</li>
<li>NormalT - type of normals to use. Insofar as <tt class="docutils literal"><span class="pre">pcl::RegionGrowingRGB</span></tt> is derived from the <tt class="docutils literal"><span class="pre">pcl::RegionGrowing</span></tt>, it can use both tests at the same time:
color test and normal test. The given example uses only the first one, therefore type of normals is not used.</li>
</ul>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">reg</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">reg</span><span class="p">.</span><span class="n">setIndices</span> <span class="p">(</span><span class="n">indices</span><span class="p">);</span>
  <span class="n">reg</span><span class="p">.</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">tree</span><span class="p">);</span>
</pre></div>
</div>
<p>These lines provide the instance with the input cloud, indices and search method.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">reg</span><span class="p">.</span><span class="n">setDistanceThreshold</span> <span class="p">(</span><span class="mi">10</span><span class="p">);</span>
</pre></div>
</div>
<p>Here the distance threshold is set. It is used to determine whether the point is neighbouring or not. If the point is located at a distance less than
the given threshold, then it is considered to be neighbouring. It is used for clusters neighbours search.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">reg</span><span class="p">.</span><span class="n">setPointColorThreshold</span> <span class="p">(</span><span class="mi">6</span><span class="p">);</span>
</pre></div>
</div>
<p>This line sets the color threshold. Just as angle threshold is used for testing points normals in <tt class="docutils literal"><span class="pre">pcl::RegionGrowing</span></tt>
to determine if the point belongs to cluster, this value is used for testing points colors.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">reg</span><span class="p">.</span><span class="n">setRegionColorThreshold</span> <span class="p">(</span><span class="mi">5</span><span class="p">);</span>
</pre></div>
</div>
<p>Here the color threshold for clusters is set. This value is similar to the previous, but is used when the merging process takes place.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">reg</span><span class="p">.</span><span class="n">setMinClusterSize</span> <span class="p">(</span><span class="mi">600</span><span class="p">);</span>
</pre></div>
</div>
<p>This value is similar to that which was used in the <a class="reference internal" href="region_growing_segmentation.php#region-growing-segmentation"><em>Region growing segmentation</em></a> tutorial. In addition to that, it is used for merging process mentioned in the begining.
If cluster has less points than was set through <tt class="docutils literal"><span class="pre">setMinClusterSize</span></tt> method, then it will be merged with the nearest neighbour.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">std</span><span class="o">::</span><span class="n">vector</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointIndices</span><span class="o">&gt;</span> <span class="n">clusters</span><span class="p">;</span>
  <span class="n">reg</span><span class="p">.</span><span class="n">extract</span> <span class="p">(</span><span class="n">clusters</span><span class="p">);</span>
</pre></div>
</div>
<p>Here is the place where the algorithm is launched. It will return the array of clusters when the segmentation process will be over.</p>
<p>Remaining lines are responsible for the visualization of the colored cloud, where each cluster has its own color.</p>
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

<span class="nb">project</span><span class="p">(</span><span class="s">region_growing_rgb_segmentation</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.5</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">region_growing_rgb_segmentation</span> <span class="s">region_growing_rgb_segmentation.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">region_growing_rgb_segmentation</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it. Simply do:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./region_growing_rgb_segmentation
</pre></div>
</div>
<p>After the segmentation the cloud viewer window will be opened and you will see something similar to this image:</p>
<a class="reference internal image-reference" href="_images/region_growing_rgb_segmentation.jpg"><img alt="_images/region_growing_rgb_segmentation.jpg" src="_images/region_growing_rgb_segmentation.jpg" style="height: 500px;" /></a>
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