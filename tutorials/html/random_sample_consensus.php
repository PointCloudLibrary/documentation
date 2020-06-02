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
    
    <title>How to use Random Sample Consensus model</title>
    
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
            
  <div class="section" id="how-to-use-random-sample-consensus-model">
<span id="random-sample-consensus"></span><h1>How to use Random Sample Consensus model</h1>
<p>In this tutorial we learn how to use a RandomSampleConsensus with a plane model to obtain the cloud fitting to this model.</p>
</div>
<div class="section" id="theoretical-primer">
<h1>Theoretical Primer</h1>
<p>The abbreviation of &#8220;RANdom SAmple Consensus&#8221; is RANSAC, and it is an iterative method that is used to estimate parameters of a meathematical model from a set of data containing outliers.  This algorithm was published by Fischler and Bolles in 1981.  The RANSAC algorithm assumes that all of the data we are looking at is comprised of both inliers and outliers.  Inliers can be explained by a model with a particular set of parameter values, while outliers do not fit that model in any circumstance.  Another necessary assumption is that a procedure which can optimally estimate the parameters of the chosen model from the data is available.</p>
<p>From <a class="reference internal" href="#wikipedia" id="id1">[Wikipedia]</a>:</p>
<blockquote>
<div><p><em>The input to the RANSAC algorithm is a set of observed data values, a parameterized model which can explain or be fitted to the observations, and some confidence parameters.</em></p>
<p><em>RANSAC achieves its goal by iteratively selecting a random subset of the original data. These data are hypothetical inliers and this hypothesis is then tested as follows:</em></p>
<blockquote>
<div><ol class="arabic simple">
<li><em>A model is fitted to the hypothetical inliers, i.e. all free parameters of the model are reconstructed from the inliers.</em></li>
<li><em>All other data are then tested against the fitted model and, if a point fits well to the estimated model, also considered as a hypothetical inlier.</em></li>
<li><em>The estimated model is reasonably good if sufficiently many points have been classified as hypothetical inliers.</em></li>
<li><em>The model is reestimated from all hypothetical inliers, because it has only been estimated from the initial set of hypothetical inliers.</em></li>
<li><em>Finally, the model is evaluated by estimating the error of the inliers relative to the model.</em></li>
</ol>
</div></blockquote>
<p><em>This procedure is repeated a fixed number of times, each time producing either a model which is rejected because too few points are classified as inliers or a refined model together with a corresponding error measure. In the latter case, we keep the refined model if its error is lower than the last saved model.</em></p>
<p><em>...</em></p>
<p><em>An advantage of RANSAC is its ability to do robust estimation of the model parameters, i.e., it can estimate the parameters with a high degree of accuracy even when a significant number of outliers are present in the data set. A disadvantage of RANSAC is that there is no upper bound on the time it takes to compute these parameters. When the number of iterations computed is limited the solution obtained may not be optimal, and it may not even be one that fits the data in a good way. In this way RANSAC offers a trade-off; by computing a greater number of iterations the probability of a reasonable model being produced is increased. Another disadvantage of RANSAC is that it requires the setting of problem-specific thresholds.</em></p>
<p><em>RANSAC can only estimate one model for a particular data set. As for any one-model approach when two (or more) models exist, RANSAC may fail to find either one.</em></p>
</div></blockquote>
<a class="reference internal image-reference" href="_images/random_sample_example1.png"><img alt="_images/random_sample_example1.png" class="align-left" src="_images/random_sample_example1.png" style="height: 200px;" /></a>
<a class="reference internal image-reference" href="_images/random_sample_example2.png"><img alt="_images/random_sample_example2.png" class="align-right" src="_images/random_sample_example2.png" style="height: 200px;" /></a>
<p>The pictures to the left and right (From <a class="reference internal" href="#wikipedia" id="id2">[Wikipedia]</a>) show a simple application of the RANSAC algorithm on a 2-dimensional set of data. The image on our left is a visual representation of a data set containg both inliers and outliers.  The image on our right shows all of the outliers in red, and shows inliers in blue.  The blue line is the result of the work done by RANSAC.  In this case the model that we are trying to fit to the data is a line, and it looks like it&#8217;s a fairly good fit to our data.</p>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>Create a file, let&#8217;s say, <tt class="docutils literal"><span class="pre">random_sample_consensus.cpp</span></tt> in your favorite editor and place the following inside:</p>
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
103</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;iostream&gt;</span>
<span class="cp">#include &lt;pcl/console/parse.h&gt;</span>
<span class="cp">#include &lt;pcl/filters/extract_indices.h&gt;</span>
<span class="cp">#include &lt;pcl/io/pcd_io.h&gt;</span>
<span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/sample_consensus/ransac.h&gt;</span>
<span class="cp">#include &lt;pcl/sample_consensus/sac_model_plane.h&gt;</span>
<span class="cp">#include &lt;pcl/sample_consensus/sac_model_sphere.h&gt;</span>
<span class="cp">#include &lt;pcl/visualization/pcl_visualizer.h&gt;</span>
<span class="cp">#include &lt;boost/thread/thread.hpp&gt;</span>

<span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span>
<span class="n">simpleVis</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">ConstPtr</span> <span class="n">cloud</span><span class="p">)</span>
<span class="p">{</span>
  <span class="c1">// --------------------------------------------</span>
  <span class="c1">// -----Open 3D viewer and add point cloud-----</span>
  <span class="c1">// --------------------------------------------</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">viewer</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="p">(</span><span class="s">&quot;3D Viewer&quot;</span><span class="p">));</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setBackgroundColor</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addPointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="s">&quot;sample cloud&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span> <span class="mi">3</span><span class="p">,</span> <span class="s">&quot;sample cloud&quot;</span><span class="p">);</span>
  <span class="c1">//viewer-&gt;addCoordinateSystem (1.0, &quot;global&quot;);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">initCameraParameters</span> <span class="p">();</span>
  <span class="k">return</span> <span class="p">(</span><span class="n">viewer</span><span class="p">);</span>
<span class="p">}</span>

<span class="kt">int</span>
<span class="n">main</span><span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="c1">// initialize PointClouds</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">final</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="c1">// populate our PointCloud with points</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">width</span>    <span class="o">=</span> <span class="mi">500</span><span class="p">;</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">height</span>   <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">is_dense</span> <span class="o">=</span> <span class="nb">false</span><span class="p">;</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">resize</span> <span class="p">(</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">*</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">height</span><span class="p">);</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-s&quot;</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span> <span class="o">||</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-sf&quot;</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0</span><span class="p">);</span>
      <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0</span><span class="p">);</span>
      <span class="k">if</span> <span class="p">(</span><span class="n">i</span> <span class="o">%</span> <span class="mi">5</span> <span class="o">==</span> <span class="mi">0</span><span class="p">)</span>
        <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0</span><span class="p">);</span>
      <span class="k">else</span> <span class="nf">if</span><span class="p">(</span><span class="n">i</span> <span class="o">%</span> <span class="mi">2</span> <span class="o">==</span> <span class="mi">0</span><span class="p">)</span>
        <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">=</span>  <span class="n">sqrt</span><span class="p">(</span> <span class="mi">1</span> <span class="o">-</span> <span class="p">(</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">*</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span><span class="p">)</span>
                                      <span class="o">-</span> <span class="p">(</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">*</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span><span class="p">));</span>
      <span class="k">else</span>
        <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">=</span>  <span class="o">-</span> <span class="n">sqrt</span><span class="p">(</span> <span class="mi">1</span> <span class="o">-</span> <span class="p">(</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">*</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span><span class="p">)</span>
                                        <span class="o">-</span> <span class="p">(</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">*</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span><span class="p">));</span>
    <span class="p">}</span>
    <span class="k">else</span>
    <span class="p">{</span>
      <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0</span><span class="p">);</span>
      <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0</span><span class="p">);</span>
      <span class="k">if</span><span class="p">(</span> <span class="n">i</span> <span class="o">%</span> <span class="mi">2</span> <span class="o">==</span> <span class="mi">0</span><span class="p">)</span>
        <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0</span><span class="p">);</span>
      <span class="k">else</span>
        <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">=</span> <span class="o">-</span><span class="mi">1</span> <span class="o">*</span> <span class="p">(</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">+</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span><span class="p">);</span>
    <span class="p">}</span>
  <span class="p">}</span>

  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">inliers</span><span class="p">;</span>

  <span class="c1">// created RandomSampleConsensus object and compute the appropriated model</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">SampleConsensusModelSphere</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span>
    <span class="n">model_s</span><span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">SampleConsensusModelSphere</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="p">));</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">SampleConsensusModelPlane</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span>
    <span class="n">model_p</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">SampleConsensusModelPlane</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="p">));</span>
  <span class="k">if</span><span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-f&quot;</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">RandomSampleConsensus</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">ransac</span> <span class="p">(</span><span class="n">model_p</span><span class="p">);</span>
    <span class="n">ransac</span><span class="p">.</span><span class="n">setDistanceThreshold</span> <span class="p">(</span><span class="mf">.01</span><span class="p">);</span>
    <span class="n">ransac</span><span class="p">.</span><span class="n">computeModel</span><span class="p">();</span>
    <span class="n">ransac</span><span class="p">.</span><span class="n">getInliers</span><span class="p">(</span><span class="n">inliers</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="k">else</span> <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-sf&quot;</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span> <span class="p">)</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">RandomSampleConsensus</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">ransac</span> <span class="p">(</span><span class="n">model_s</span><span class="p">);</span>
    <span class="n">ransac</span><span class="p">.</span><span class="n">setDistanceThreshold</span> <span class="p">(</span><span class="mf">.01</span><span class="p">);</span>
    <span class="n">ransac</span><span class="p">.</span><span class="n">computeModel</span><span class="p">();</span>
    <span class="n">ransac</span><span class="p">.</span><span class="n">getInliers</span><span class="p">(</span><span class="n">inliers</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="c1">// copies all inliers of the model computed to another PointCloud</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">copyPointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">(</span><span class="o">*</span><span class="n">cloud</span><span class="p">,</span> <span class="n">inliers</span><span class="p">,</span> <span class="o">*</span><span class="n">final</span><span class="p">);</span>

  <span class="c1">// creates the visualization object and adds either our orignial cloud or all of the inliers</span>
  <span class="c1">// depending on the command line arguments specified.</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">viewer</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-f&quot;</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span> <span class="o">||</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-sf&quot;</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span><span class="p">)</span>
    <span class="n">viewer</span> <span class="o">=</span> <span class="n">simpleVis</span><span class="p">(</span><span class="n">final</span><span class="p">);</span>
  <span class="k">else</span>
    <span class="n">viewer</span> <span class="o">=</span> <span class="n">simpleVis</span><span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">viewer</span><span class="o">-&gt;</span><span class="n">wasStopped</span> <span class="p">())</span>
  <span class="p">{</span>
    <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">spinOnce</span> <span class="p">(</span><span class="mi">100</span><span class="p">);</span>
    <span class="n">boost</span><span class="o">::</span><span class="n">this_thread</span><span class="o">::</span><span class="n">sleep</span> <span class="p">(</span><span class="n">boost</span><span class="o">::</span><span class="n">posix_time</span><span class="o">::</span><span class="n">microseconds</span> <span class="p">(</span><span class="mi">100000</span><span class="p">));</span>
  <span class="p">}</span>
  <span class="k">return</span> <span class="mi">0</span><span class="p">;</span>
 <span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>The following source code initializes two PointClouds and fills one of them with points.  The majority of these points are placed in the cloud according to a model, but a fraction (1/5) of them are given arbitrary locations.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// initialize PointClouds</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">final</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="c1">// populate our PointCloud with points</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">width</span>    <span class="o">=</span> <span class="mi">500</span><span class="p">;</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">height</span>   <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">is_dense</span> <span class="o">=</span> <span class="nb">false</span><span class="p">;</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">resize</span> <span class="p">(</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">*</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">height</span><span class="p">);</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-s&quot;</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span> <span class="o">||</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-sf&quot;</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0</span><span class="p">);</span>
      <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0</span><span class="p">);</span>
      <span class="k">if</span> <span class="p">(</span><span class="n">i</span> <span class="o">%</span> <span class="mi">5</span> <span class="o">==</span> <span class="mi">0</span><span class="p">)</span>
        <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0</span><span class="p">);</span>
      <span class="k">else</span> <span class="nf">if</span><span class="p">(</span><span class="n">i</span> <span class="o">%</span> <span class="mi">2</span> <span class="o">==</span> <span class="mi">0</span><span class="p">)</span>
        <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">=</span>  <span class="n">sqrt</span><span class="p">(</span> <span class="mi">1</span> <span class="o">-</span> <span class="p">(</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">*</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span><span class="p">)</span>
                                      <span class="o">-</span> <span class="p">(</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">*</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span><span class="p">));</span>
      <span class="k">else</span>
        <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">=</span>  <span class="o">-</span> <span class="n">sqrt</span><span class="p">(</span> <span class="mi">1</span> <span class="o">-</span> <span class="p">(</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">*</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span><span class="p">)</span>
                                        <span class="o">-</span> <span class="p">(</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">*</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span><span class="p">));</span>
    <span class="p">}</span>
    <span class="k">else</span>
    <span class="p">{</span>
      <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0</span><span class="p">);</span>
      <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0</span><span class="p">);</span>
      <span class="k">if</span><span class="p">(</span> <span class="n">i</span> <span class="o">%</span> <span class="mi">2</span> <span class="o">==</span> <span class="mi">0</span><span class="p">)</span>
        <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0</span><span class="p">);</span>
      <span class="k">else</span>
        <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">=</span> <span class="o">-</span><span class="mi">1</span> <span class="o">*</span> <span class="p">(</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">+</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span><span class="p">);</span>
    <span class="p">}</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>Next we create a vector of ints that can store the locations of our inlier points from our PointCloud and now we can build our RandomSampleConsensus object using either a plane or a sphere model from our input cloud.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">inliers</span><span class="p">;</span>

  <span class="c1">// created RandomSampleConsensus object and compute the appropriated model</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">SampleConsensusModelSphere</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span>
    <span class="n">model_s</span><span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">SampleConsensusModelSphere</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="p">));</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">SampleConsensusModelPlane</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span>
    <span class="n">model_p</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">SampleConsensusModelPlane</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="p">));</span>
  <span class="k">if</span><span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-f&quot;</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">RandomSampleConsensus</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">ransac</span> <span class="p">(</span><span class="n">model_p</span><span class="p">);</span>
    <span class="n">ransac</span><span class="p">.</span><span class="n">setDistanceThreshold</span> <span class="p">(</span><span class="mf">.01</span><span class="p">);</span>
    <span class="n">ransac</span><span class="p">.</span><span class="n">computeModel</span><span class="p">();</span>
    <span class="n">ransac</span><span class="p">.</span><span class="n">getInliers</span><span class="p">(</span><span class="n">inliers</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="k">else</span> <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-sf&quot;</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span> <span class="p">)</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">RandomSampleConsensus</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">ransac</span> <span class="p">(</span><span class="n">model_s</span><span class="p">);</span>
    <span class="n">ransac</span><span class="p">.</span><span class="n">setDistanceThreshold</span> <span class="p">(</span><span class="mf">.01</span><span class="p">);</span>
    <span class="n">ransac</span><span class="p">.</span><span class="n">computeModel</span><span class="p">();</span>
    <span class="n">ransac</span><span class="p">.</span><span class="n">getInliers</span><span class="p">(</span><span class="n">inliers</span><span class="p">);</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>This last bit of code copies all of the points that fit our model to another cloud and then display either that or our original cloud in the viewer.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// copies all inliers of the model computed to another PointCloud</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">copyPointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">(</span><span class="o">*</span><span class="n">cloud</span><span class="p">,</span> <span class="n">inliers</span><span class="p">,</span> <span class="o">*</span><span class="n">final</span><span class="p">);</span>

  <span class="c1">// creates the visualization object and adds either our orignial cloud or all of the inliers</span>
  <span class="c1">// depending on the command line arguments specified.</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">viewer</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-f&quot;</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span> <span class="o">||</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-sf&quot;</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span><span class="p">)</span>
    <span class="n">viewer</span> <span class="o">=</span> <span class="n">simpleVis</span><span class="p">(</span><span class="n">final</span><span class="p">);</span>
  <span class="k">else</span>
    <span class="n">viewer</span> <span class="o">=</span> <span class="n">simpleVis</span><span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
</pre></div>
</div>
<p>There is some extra code that relates to the display of the PointClouds in the 3D Viewer, but I&#8217;m not going to explain that here.</p>
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

<span class="nb">project</span><span class="p">(</span><span class="s">random_sample_consensus</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.2</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">random_sample_consensus</span> <span class="s">random_sample_consensus.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">random_sample_consensus</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have built the executable, you can run it. Simply do:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./random_sample_consensus
</pre></div>
</div>
<p>to have a viewer window display that shows you the original PointCloud (with outliers) we have created.</p>
<a class="reference internal image-reference" href="_images/ransac_outliers_plane.png"><img alt="_images/ransac_outliers_plane.png" class="align-center" src="_images/ransac_outliers_plane.png" style="height: 400px;" /></a>
<p>Hit &#8216;r&#8217; on your keyboard to scale and center the viewer.  You can then click and drag to rotate the view.  You can tell there is very little organization to this PointCloud and that it contains many outliers.  Pressing &#8216;q&#8217; on your keyboard will close the viewer and end the program.  Now if you run the program with the following argument:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./random_sample_consensus -f
</pre></div>
</div>
<p>the program will display only the indices of the original PointCloud which satisfy the paticular model we have chosen (in this case plane) as found by RandomSampleConsens in the viewer.</p>
<a class="reference internal image-reference" href="_images/ransac_inliers_plane.png"><img alt="_images/ransac_inliers_plane.png" class="align-center" src="_images/ransac_inliers_plane.png" style="height: 400px;" /></a>
<p>Again hit &#8216;r&#8217; to scale and center the view and then click and drag with the mouse to rotate around the cloud.  You can see there are no longer any points that do not lie with in the plane model in this PointCloud. Hit &#8216;q&#8217; to exit the viewer and program.</p>
<p>There is also an example using a sphere in this program.  If you run it with:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./random_sample_consensus -s
</pre></div>
</div>
<p>It will generate and display a sphereical cloud and some outliers as well.</p>
<a class="reference internal image-reference" href="_images/ransac_outliers_sphere.png"><img alt="_images/ransac_outliers_sphere.png" class="align-center" src="_images/ransac_outliers_sphere.png" style="height: 400px;" /></a>
<p>Then when you run the program with:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./random_sample_consensus -sf
</pre></div>
</div>
<p>It will show you the result of applying RandomSampleConsensus to this data set with a spherical model.</p>
<a class="reference internal image-reference" href="_images/ransac_inliers_sphere.png"><img alt="_images/ransac_inliers_sphere.png" class="align-center" src="_images/ransac_inliers_sphere.png" style="height: 400px;" /></a>
<table class="docutils citation" frame="void" id="wikipedia" rules="none">
<colgroup><col class="label" /><col /></colgroup>
<tbody valign="top">
<tr><td class="label">[Wikipedia]</td><td><em>(<a class="fn-backref" href="#id1">1</a>, <a class="fn-backref" href="#id2">2</a>)</em> <a class="reference external" href="http://en.wikipedia.org/wiki/RANSAC">http://en.wikipedia.org/wiki/RANSAC</a></td></tr>
</tbody>
</table>
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