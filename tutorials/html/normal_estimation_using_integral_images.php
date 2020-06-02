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
    
    <title>Normal Estimation Using Integral Images</title>
    
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
            
  <div class="section" id="normal-estimation-using-integral-images">
<span id="id1"></span><h1>Normal Estimation Using Integral Images</h1>
<p>In this tutorial we will learn how to compute normals for an organized point
cloud using integral images.</p>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>First, create a file, let&#8217;s say, <tt class="docutils literal"><span class="pre">normal_estimation_using_integral_images.cpp</span></tt> in your favorite
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
33</pre></div></td><td class="code"><div class="highlight"><pre>     #include &lt;pcl/io/io.h&gt;
     #include &lt;pcl/io/pcd_io.h&gt;
     #include &lt;pcl/features/integral_image_normal.h&gt;
     #include &lt;pcl/visualization/cloud_viewer.h&gt;

     int
     main ()
     {
             // load point cloud
             pcl::PointCloud&lt;pcl::PointXYZ&gt;::Ptr cloud (new pcl::PointCloud&lt;pcl::PointXYZ&gt;);
             pcl::io::loadPCDFile (&quot;table_scene_mug_stereo_textured.pcd&quot;, *cloud);

             // estimate normals
             pcl::PointCloud&lt;pcl::Normal&gt;::Ptr normals (new pcl::PointCloud&lt;pcl::Normal&gt;);

             pcl::IntegralImageNormalEstimation&lt;pcl::PointXYZ, pcl::Normal&gt; ne;
             ne.setNormalEstimationMethod (ne.AVERAGE_3D_GRADIENT);
             ne.setMaxDepthChangeFactor(0.02f);
             ne.setNormalSmoothingSize(10.0f);
             ne.setInputCloud(cloud);
             ne.compute(*normals);

             // visualize normals
             pcl::visualization::PCLVisualizer viewer(&quot;PCL Viewer&quot;);
             viewer.setBackgroundColor (0.0, 0.0, 0.5);
             viewer.addPointCloudNormals&lt;pcl::PointXYZ,pcl::Normal&gt;(cloud, normals);

             while (!viewer.wasStopped ())
             {
               viewer.spinOnce ();
             }
             return 0;
     }
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>Now, let&#8217;s break down the code piece by piece. In the first part we load a
point cloud from a file:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
<span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="s">&quot;table_scene_mug_stereo_textured.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud</span><span class="p">);</span>
</pre></div>
</div>
<p>In the second part we create an object for the normal estimation and compute
the normals:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="c1">// estimate normals</span>
<span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">normals</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span><span class="p">);</span>

<span class="n">pcl</span><span class="o">::</span><span class="n">IntegralImageNormalEstimation</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span> <span class="n">ne</span><span class="p">;</span>
<span class="n">ne</span><span class="p">.</span><span class="n">setNormalEstimationMethod</span> <span class="p">(</span><span class="n">ne</span><span class="p">.</span><span class="n">AVERAGE_3D_GRADIENT</span><span class="p">);</span>
<span class="n">ne</span><span class="p">.</span><span class="n">setMaxDepthChangeFactor</span><span class="p">(</span><span class="mf">0.02f</span><span class="p">);</span>
<span class="n">ne</span><span class="p">.</span><span class="n">setNormalSmoothingSize</span><span class="p">(</span><span class="mf">10.0f</span><span class="p">);</span>
<span class="n">ne</span><span class="p">.</span><span class="n">setInputCloud</span><span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
<span class="n">ne</span><span class="p">.</span><span class="n">compute</span><span class="p">(</span><span class="o">*</span><span class="n">normals</span><span class="p">);</span>
</pre></div>
</div>
<p>The following normal estimation methods are available:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">enum</span> <span class="n">NormalEstimationMethod</span>
<span class="p">{</span>
  <span class="n">COVARIANCE_MATRIX</span><span class="p">,</span>
  <span class="n">AVERAGE_3D_GRADIENT</span><span class="p">,</span>
  <span class="n">AVERAGE_DEPTH_CHANGE</span>
<span class="p">};</span>
</pre></div>
</div>
<p>The COVARIANCE_MATRIX mode creates 9 integral images to compute the normal for
a specific point from the covariance matrix of its local neighborhood. The
AVERAGE_3D_GRADIENT mode creates 6 integral images to compute smoothed versions
of horizontal and vertical 3D gradients and computes the normals using the
cross-product between these two gradients. The AVERAGE_DEPTH_CHANGE mode
creates only a single integral image and computes the normals from the average
depth changes.</p>
<p>In the last part we visualize the point cloud and the corresponding normals:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="c1">// visualize normals</span>
<span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="n">viewer</span><span class="p">(</span><span class="s">&quot;PCL Viewer&quot;</span><span class="p">);</span>
<span class="n">viewer</span><span class="p">.</span><span class="n">setBackgroundColor</span> <span class="p">(</span><span class="mf">0.0</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mf">0.5</span><span class="p">);</span>
<span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloudNormals</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span><span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">normals</span><span class="p">);</span>

<span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">viewer</span><span class="p">.</span><span class="n">wasStopped</span> <span class="p">())</span>
<span class="p">{</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">spinOnce</span> <span class="p">();</span>
<span class="p">}</span>
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