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
    
    <title>Reading Point Cloud data from PCD files</title>
    
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
            
  <div class="section" id="reading-point-cloud-data-from-pcd-files">
<span id="reading-pcd"></span><h1>Reading Point Cloud data from PCD files</h1>
<p>In this tutorial we will learn how to read point cloud data from a PCD file.</p>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>First, create a file called, let&#8217;s say, <tt class="docutils literal"><span class="pre">pcd_read.cpp</span></tt> in your favorite
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
25</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;iostream&gt;</span>
<span class="cp">#include &lt;pcl/io/pcd_io.h&gt;</span>
<span class="cp">#include &lt;pcl/point_types.h&gt;</span>

<span class="kt">int</span>
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;test_pcd.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud</span><span class="p">)</span> <span class="o">==</span> <span class="o">-</span><span class="mi">1</span><span class="p">)</span> <span class="c1">//* load the file</span>
  <span class="p">{</span>
    <span class="n">PCL_ERROR</span> <span class="p">(</span><span class="s">&quot;Couldn&#39;t read file test_pcd.pcd </span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Loaded &quot;</span>
            <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">*</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">height</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot; data points from test_pcd.pcd with the following fields: &quot;</span>
            <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;    &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span>
              <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span>    <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span>
              <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span>    <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="k">return</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>Now, let&#8217;s break down the code piece by piece.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
</pre></div>
</div>
<p>creates a PointCloud&lt;PointXYZ&gt; boost shared pointer and initializes it.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;test_pcd.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud</span><span class="p">)</span> <span class="o">==</span> <span class="o">-</span><span class="mi">1</span><span class="p">)</span> <span class="c1">//* load the file</span>
  <span class="p">{</span>
    <span class="n">PCL_ERROR</span> <span class="p">(</span><span class="s">&quot;Couldn&#39;t read file test_pcd.pcd </span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>loads the PointCloud data from disk (we assume that test_pcd.pcd has already
been created from the previous tutorial) into the binary blob.</p>
<p>Alternatively, you can read a PCLPointCloud2 blob (available only in PCL 1.x). Due
to the dynamic nature of point clouds, we prefer to read them as binary blobs,
and then convert to the actual representation that we want to use.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="n">pcl</span><span class="o">::</span><span class="n">PCLPointCloud2</span> <span class="n">cloud_blob</span><span class="p">;</span>
<span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="s">&quot;test_pcd.pcd&quot;</span><span class="p">,</span> <span class="n">cloud_blob</span><span class="p">);</span>
<span class="n">pcl</span><span class="o">::</span><span class="n">fromPCLPointCloud2</span> <span class="p">(</span><span class="n">cloud_blob</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud</span><span class="p">);</span> <span class="c1">//* convert from pcl/PCLPointCloud2 to pcl::PointCloud&lt;T&gt;</span>
</pre></div>
</div>
<p>reads and converts the binary blob into the templated PointCloud format, here
using pcl::PointXYZ as the underlying point type.</p>
<p>Finally:</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;    &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span>
              <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span>    <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span>
              <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span>    <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
</pre></div>
</div>
<p>is used to show the data that was loaded from file.</p>
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

<span class="nb">project</span><span class="p">(</span><span class="s">pcd_read</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.2</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">pcd_read</span> <span class="s">pcd_read.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">pcd_read</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it. Simply do:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./pcd_read
</pre></div>
</div>
<p>You will see something similar to:</p>
<div class="highlight-python"><div class="highlight"><pre>Loaded 5 data points from test_pcd.pcd with the following fields: x y z
  0.35222 -0.15188 -0.1064
  -0.39741 -0.47311 0.2926
  -0.7319 0.6671 0.4413
  -0.73477 0.85458 -0.036173
  -0.4607 -0.27747 -0.91676
</pre></div>
</div>
<p>Note that if the file test_pcd.pcd does not exist (either it hasn&#8217;t been
created or it has been erased), you should get an error message such as:</p>
<div class="highlight-python"><div class="highlight"><pre>Couldn&#39;t read file test_pcd.pcd
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