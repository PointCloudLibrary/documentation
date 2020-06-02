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
    
    <title>Min-Cut Based Segmentation</title>
    
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
            
  <div class="section" id="min-cut-based-segmentation">
<span id="min-cut-segmentation"></span><h1>Min-Cut Based Segmentation</h1>
<p>In this tutorial we will learn how to use the min-cut based segmentation algorithm implemented in the <tt class="docutils literal"><span class="pre">pcl::MinCutSegmentation</span></tt> class.
This algorithm makes a binary segmentation of the given input cloud. Having objects center and its radius the algorithm divides the cloud on two sets:
foreground and background points (points that belong to the object and those that do not belong).</p>
</div>
<div class="section" id="theoretical-primer">
<h1>Theoretical Primer</h1>
<p>The idea of this algorithm is as follows:</p>
<blockquote>
<div><ol class="arabic">
<li><p class="first">For the given point cloud algorithm constructs the graph that contains every single point of the cloud as a set of vertices and two more vertices
called source and sink. Every vertex of the graph that corresponds to the point is connected with source and sink with the edges.
In addition to these, every vertex (except source and sink) has edges that connect the corresponding point with its nearest neighbours.</p>
</li>
<li><p class="first">Algorithm assigns weights for every edge. There are three different types of weight. Let&#8217;s examine them:</p>
<blockquote>
<div><ul>
<li><p class="first">First of all it assigns weight to the edges between clouds points. This weight is called smooth cost and is calculated by the formula:</p>
<p class="centered">
<strong><span class="math">smoothCost=e^{-(\frac{dist}{ \sigma })^2}</span></strong></p><p>Here <span class="math">dist</span> is the distance between points. The farther away the points are, the more is probability that the edge will be cut.</p>
</li>
<li><p class="first">Next step the algorithm sets data cost. It consists of foreground and background penalties.
The first one is the weight for those edges that connect clouds points with the source vertex and has the constant user-defined value.
The second one is assigned to the edges that connect points with the sink vertex and is calculated by the formula:</p>
<p class="centered">
<strong><span class="math">backgroundPenalty=(\frac{distanceToCenter}{radius})</span></strong></p><p>Here <span class="math">distanceToCenter</span> is the distance to the expected center of the object in the horizontal plane:</p>
<p class="centered">
<strong><span class="math">distanceToCenter=\sqrt{(x-centerX)^2+(y-centerY)^2}</span></strong></p><p>Radius that occurs in the formula is the input parameter for this algorithm and can be roughly considered as the range from objects center
outside of which there are no points that belong to foreground (objects horizontal radius).</p>
</li>
</ul>
</div></blockquote>
</li>
<li><p class="first">After all the preparations the search of the minimum cut is made. Based on an analysis of this cut, cloud is divided on forground and
background points.</p>
</li>
</ol>
</div></blockquote>
<p>For more comprehensive information please refer to the article
<a class="reference external" href="http://gfx.cs.princeton.edu/pubs/Golovinskiy_2009_MBS/index.php">&#8220;Min-Cut Based Segmentation of Point Clouds&#8221;</a>.</p>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>First of all you will need the point cloud for this tutorial.
<a class="reference external" href="https://raw.github.com/PointCloudLibrary/data/master/tutorials/min_cut_segmentation_tutorial.pcd">This</a> is a good one for the purposes of the algorithm.
Next what you need to do is to create a file <tt class="docutils literal"><span class="pre">min_cut_segmentation.cpp</span></tt> in any editor you prefer and copy the following code inside of it:</p>
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
55</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;iostream&gt;</span>
<span class="cp">#include &lt;vector&gt;</span>
<span class="cp">#include &lt;pcl/io/pcd_io.h&gt;</span>
<span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/visualization/cloud_viewer.h&gt;</span>
<span class="cp">#include &lt;pcl/filters/passthrough.h&gt;</span>
<span class="cp">#include &lt;pcl/segmentation/min_cut_segmentation.h&gt;</span>

<span class="kt">int</span> <span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="k">if</span> <span class="p">(</span> <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;min_cut_segmentation_tutorial.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud</span><span class="p">)</span> <span class="o">==</span> <span class="o">-</span><span class="mi">1</span> <span class="p">)</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Cloud reading failed.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">IndicesPtr</span> <span class="n">indices</span> <span class="p">(</span><span class="k">new</span> <span class="n">std</span><span class="o">::</span><span class="n">vector</span> <span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PassThrough</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">pass</span><span class="p">;</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">setFilterFieldName</span> <span class="p">(</span><span class="s">&quot;z&quot;</span><span class="p">);</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">setFilterLimits</span> <span class="p">(</span><span class="mf">0.0</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">);</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">indices</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">MinCutSegmentation</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">seg</span><span class="p">;</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setIndices</span> <span class="p">(</span><span class="n">indices</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">foreground_points</span><span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">point</span><span class="p">;</span>
  <span class="n">point</span><span class="p">.</span><span class="n">x</span> <span class="o">=</span> <span class="mf">68.97</span><span class="p">;</span>
  <span class="n">point</span><span class="p">.</span><span class="n">y</span> <span class="o">=</span> <span class="o">-</span><span class="mf">18.55</span><span class="p">;</span>
  <span class="n">point</span><span class="p">.</span><span class="n">z</span> <span class="o">=</span> <span class="mf">0.57</span><span class="p">;</span>
  <span class="n">foreground_points</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">push_back</span><span class="p">(</span><span class="n">point</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setForegroundPoints</span> <span class="p">(</span><span class="n">foreground_points</span><span class="p">);</span>

  <span class="n">seg</span><span class="p">.</span><span class="n">setSigma</span> <span class="p">(</span><span class="mf">0.25</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setRadius</span> <span class="p">(</span><span class="mf">3.0433856</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setNumberOfNeighbours</span> <span class="p">(</span><span class="mi">14</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setSourceWeight</span> <span class="p">(</span><span class="mf">0.8</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">vector</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointIndices</span><span class="o">&gt;</span> <span class="n">clusters</span><span class="p">;</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">extract</span> <span class="p">(</span><span class="n">clusters</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Maximum flow is &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">seg</span><span class="p">.</span><span class="n">getMaxFlow</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">colored_cloud</span> <span class="o">=</span> <span class="n">seg</span><span class="p">.</span><span class="n">getColoredCloud</span> <span class="p">();</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">CloudViewer</span> <span class="n">viewer</span> <span class="p">(</span><span class="s">&quot;Cluster viewer&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">showCloud</span><span class="p">(</span><span class="n">colored_cloud</span><span class="p">);</span>
  <span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">viewer</span><span class="p">.</span><span class="n">wasStopped</span> <span class="p">())</span>
  <span class="p">{</span>
  <span class="p">}</span>

  <span class="k">return</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>Now let&#8217;s study out what is the purpose of this code. First few lines will be omitted, because they are obvious.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="k">if</span> <span class="p">(</span> <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;min_cut_segmentation_tutorial.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud</span><span class="p">)</span> <span class="o">==</span> <span class="o">-</span><span class="mi">1</span> <span class="p">)</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Cloud reading failed.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>These lines are simply loading the cloud from the .pcd file.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">IndicesPtr</span> <span class="n">indices</span> <span class="p">(</span><span class="k">new</span> <span class="n">std</span><span class="o">::</span><span class="n">vector</span> <span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PassThrough</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">pass</span><span class="p">;</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">setFilterFieldName</span> <span class="p">(</span><span class="s">&quot;z&quot;</span><span class="p">);</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">setFilterLimits</span> <span class="p">(</span><span class="mf">0.0</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">);</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">indices</span><span class="p">);</span>
</pre></div>
</div>
<p>This few lines are not necessary. Their only purpose is to show that <tt class="docutils literal"><span class="pre">pcl::MinCutSegmentation</span></tt> class can work with indices.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">MinCutSegmentation</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">seg</span><span class="p">;</span>
</pre></div>
</div>
<p>Here is the line where the instantiation of the <tt class="docutils literal"><span class="pre">pcl::MinCutSegmentation</span></tt> class takes place.
It is the tamplate class that has only one parameter - PointT - which says what type of points will be used.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">seg</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setIndices</span> <span class="p">(</span><span class="n">indices</span><span class="p">);</span>
</pre></div>
</div>
<p>These lines provide the algorithm with the cloud that must be segmented and the indices.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">foreground_points</span><span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">point</span><span class="p">;</span>
  <span class="n">point</span><span class="p">.</span><span class="n">x</span> <span class="o">=</span> <span class="mf">68.97</span><span class="p">;</span>
  <span class="n">point</span><span class="p">.</span><span class="n">y</span> <span class="o">=</span> <span class="o">-</span><span class="mf">18.55</span><span class="p">;</span>
  <span class="n">point</span><span class="p">.</span><span class="n">z</span> <span class="o">=</span> <span class="mf">0.57</span><span class="p">;</span>
  <span class="n">foreground_points</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">push_back</span><span class="p">(</span><span class="n">point</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setForegroundPoints</span> <span class="p">(</span><span class="n">foreground_points</span><span class="p">);</span>
</pre></div>
</div>
<p>As mentioned before, algorithm requires point that is known to be the objects center. These lines provide it.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">seg</span><span class="p">.</span><span class="n">setSigma</span> <span class="p">(</span><span class="mf">0.25</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setRadius</span> <span class="p">(</span><span class="mf">3.0433856</span><span class="p">);</span>
</pre></div>
</div>
<p>These lines set <span class="math">\sigma</span> and objects radius required for smooth cost calculation.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">seg</span><span class="p">.</span><span class="n">setNumberOfNeighbours</span> <span class="p">(</span><span class="mi">14</span><span class="p">);</span>
</pre></div>
</div>
<p>This line tells how much neighbours to find when constructing the graph. The more neighbours is set, the more number of edges it will contain.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">seg</span><span class="p">.</span><span class="n">setSourceWeight</span> <span class="p">(</span><span class="mf">0.8</span><span class="p">);</span>
</pre></div>
</div>
<p>Here is the line where foreground penalty is set.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">std</span><span class="o">::</span><span class="n">vector</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointIndices</span><span class="o">&gt;</span> <span class="n">clusters</span><span class="p">;</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">extract</span> <span class="p">(</span><span class="n">clusters</span><span class="p">);</span>
</pre></div>
</div>
<p>These lines are responsible for launching the algorithm. After the segmentation clusters will contain the result.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Maximum flow is &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">seg</span><span class="p">.</span><span class="n">getMaxFlow</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
</pre></div>
</div>
<p>You can easily access the flow value that was computed during the graph cut. This is exactly what happening here.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">colored_cloud</span> <span class="o">=</span> <span class="n">seg</span><span class="p">.</span><span class="n">getColoredCloud</span> <span class="p">();</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">CloudViewer</span> <span class="n">viewer</span> <span class="p">(</span><span class="s">&quot;Cluster viewer&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">showCloud</span><span class="p">(</span><span class="n">colored_cloud</span><span class="p">);</span>
  <span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">viewer</span><span class="p">.</span><span class="n">wasStopped</span> <span class="p">())</span>
  <span class="p">{</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>These lines simply create the instance of <tt class="docutils literal"><span class="pre">CloudViewer</span></tt> class for result visualization.</p>
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

<span class="nb">project</span><span class="p">(</span><span class="s">min_cut_segmentation</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.5</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">min_cut_segmentation</span> <span class="s">min_cut_segmentation.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">min_cut_segmentation</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it. Simply do:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./min_cut_segmentation
</pre></div>
</div>
<p>After the segmentation the cloud viewer window will be opened and you will see something similar to those images:</p>
<a class="reference internal image-reference" href="_images/min_cut_segmentation.jpg"><img alt="_images/min_cut_segmentation.jpg" src="_images/min_cut_segmentation.jpg" style="height: 360px;" /></a>
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