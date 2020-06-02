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
    
    <title>Cluster Recognition and 6DOF Pose Estimation using VFH descriptors</title>
    
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
            
  <div class="section" id="cluster-recognition-and-6dof-pose-estimation-using-vfh-descriptors">
<span id="vfh-recognition"></span><h1>Cluster Recognition and 6DOF Pose Estimation using VFH descriptors</h1>
<p>As previously described in <a class="reference internal" href="vfh_estimation.php#vfh-estimation"><em>Estimating VFH signatures for a set of points</em></a>, Viewpoint Feature Histograms
(VFH) are powerful <em>meta-local</em> descriptors, created for the purpose of
recognition and pose estimation for <strong>clusters</strong> of points. We here refer to a
<strong>cluster</strong> as a collection of 3D points, most of the time representing a
particular object or part of a scene, obtained through some segmentation or
detection mechanisms (please see <a class="reference internal" href="cluster_extraction.php#cluster-extraction"><em>Euclidean Cluster Extraction</em></a> for an example).</p>
<p>Our goal here is not to provide an ultimate recognition tool, but rather a
mechanism for obtaining <strong>candidates</strong> that <em>could potentially be the
cluster/object that is searched for</em>, together with its 6DOF pose in space.
With this in mind, we will be formulating the <em>recognition</em> problem as a
<em>nearest neighbor estimation</em> problem. So given a set of <em>training data</em>, we
will use efficient nearest neighbor search structures such as <em>kd-trees</em> and
return a set of potential candidates with sorted distances to the query object,
rather than an absolute <em>&#8220;this is the object that we were searching for&#8221;</em> kind
of response. The reader can imagine that such a system becomes much more useful
as we can explicitly reason about failures (false positives, or true
negatives).</p>
<p>For the purpose of this tutorial, the application example could be formulated as follows:</p>
<blockquote>
<div><ul class="simple">
<li>Training stage:<ul>
<li>given a scene with 1 object that is easily separable as a cluster;</li>
<li>use a ground-truth system to obtain its pose (see the discussion below);</li>
<li>rotate around the object or rotate the object with respect to the camera, and compute a VFH descriptor for each view;</li>
<li>store the views, and build a kd-tree representation.</li>
</ul>
</li>
<li>Testing stage:<ul>
<li>given a scene with objects that can be separated as individual clusters, first extract the clusters;</li>
<li>for each cluster, compute a VFH descriptor from the current camera position;</li>
<li>use the VFH descriptor to search for candidates in the trained kd-tree.</li>
</ul>
</li>
</ul>
</div></blockquote>
<p>We hope the above makes sense. Basically we&#8217;re first going to create the set of
objects that we try to later on recognize, and then we will use that to obtain
valid candidates for objects in the scene.</p>
<p>A good example of a ground-truth system could be a simple rotating pan-tilt
unit such as the one in the figure below. Placing an object on the unit, and
moving it with some increments in both horizontal and vertical, can result in a
perfect ground-truth system for small objects. A cheaper solution could be to
use a marker-based system (e.g., checkerboard) and rotate the camera/table
manually.</p>
<img alt="_images/pan_tilt.jpg" class="align-center" src="_images/pan_tilt.jpg" />
<p>Our Kd-Tree implementation of choice for the purpose of this tutorial is of
course, <a class="reference external" href="http://www.cs.ubc.ca/research/flann/">FLANN</a>.</p>
</div>
<div class="section" id="training">
<h1>Training</h1>
<p>We begin the training by assuming that the <em>objects</em> are already separated as
individual clusters (see <a class="reference internal" href="cluster_extraction.php#cluster-extraction"><em>Euclidean Cluster Extraction</em></a>), as shown in the figure
below:</p>
<img alt="_images/scene_raw.jpg" src="_images/scene_raw.jpg" />
<img alt="_images/scene_segmented.jpg" src="_images/scene_segmented.jpg" />
<p>Since we&#8217;re only trying to cover the explicity training/testing of VFH
signatures in this tutorial, we provide a set of datasets already collected at:
<a class="reference external" href="https://raw.github.com/PointCloudLibrary/data/master/tutorials/vfh_recognition/vfh_recognition_tutorial_data.tbz">vfh_recognition_tutorial_data.tbz</a>.
The data is a subset of the objects presented in the figure below (left), and
look like the point clouds on the right. We used the pan-tilt table shown above
to acquire the data.</p>
<img alt="_images/objects.jpg" src="_images/objects.jpg" />
<img alt="_images/training.jpg" src="_images/training.jpg" />
<p>Next, copy and paste the following code into your editor and save it as
<tt class="docutils literal"><span class="pre">build_tree.cpp</span></tt>.</p>
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
103
104
105
106
107
108
109
110
111
112
113
114
115
116
117
118
119
120
121
122
123
124
125
126
127
128
129
130
131
132
133
134
135
136
137</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/point_cloud.h&gt;</span>
<span class="cp">#include &lt;pcl/console/parse.h&gt;</span>
<span class="cp">#include &lt;pcl/console/print.h&gt;</span>
<span class="cp">#include &lt;pcl/io/pcd_io.h&gt;</span>
<span class="cp">#include &lt;boost/filesystem.hpp&gt;</span>
<span class="cp">#include &lt;flann/flann.h&gt;</span>
<span class="cp">#include &lt;flann/io/hdf5.h&gt;</span>
<span class="cp">#include &lt;fstream&gt;</span>

<span class="k">typedef</span> <span class="n">std</span><span class="o">::</span><span class="n">pair</span><span class="o">&lt;</span><span class="n">std</span><span class="o">::</span><span class="n">string</span><span class="p">,</span> <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="n">vfh_model</span><span class="p">;</span>

<span class="cm">/** \brief Loads an n-D histogram file as a VFH signature</span>
<span class="cm">  * \param path the input file name</span>
<span class="cm">  * \param vfh the resultant VFH model</span>
<span class="cm">  */</span>
<span class="kt">bool</span>
<span class="nf">loadHist</span> <span class="p">(</span><span class="k">const</span> <span class="n">boost</span><span class="o">::</span><span class="n">filesystem</span><span class="o">::</span><span class="n">path</span> <span class="o">&amp;</span><span class="n">path</span><span class="p">,</span> <span class="n">vfh_model</span> <span class="o">&amp;</span><span class="n">vfh</span><span class="p">)</span>
<span class="p">{</span>
  <span class="kt">int</span> <span class="n">vfh_idx</span><span class="p">;</span>
  <span class="c1">// Load the file as a PCD</span>
  <span class="n">try</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">PCLPointCloud2</span> <span class="n">cloud</span><span class="p">;</span>
    <span class="kt">int</span> <span class="n">version</span><span class="p">;</span>
    <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector4f</span> <span class="n">origin</span><span class="p">;</span>
    <span class="n">Eigen</span><span class="o">::</span><span class="n">Quaternionf</span> <span class="n">orientation</span><span class="p">;</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">PCDReader</span> <span class="n">r</span><span class="p">;</span>
    <span class="kt">int</span> <span class="n">type</span><span class="p">;</span> <span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">idx</span><span class="p">;</span>
    <span class="n">r</span><span class="p">.</span><span class="n">readHeader</span> <span class="p">(</span><span class="n">path</span><span class="p">.</span><span class="n">string</span> <span class="p">(),</span> <span class="n">cloud</span><span class="p">,</span> <span class="n">origin</span><span class="p">,</span> <span class="n">orientation</span><span class="p">,</span> <span class="n">version</span><span class="p">,</span> <span class="n">type</span><span class="p">,</span> <span class="n">idx</span><span class="p">);</span>

    <span class="n">vfh_idx</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">getFieldIndex</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="s">&quot;vfh&quot;</span><span class="p">);</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">vfh_idx</span> <span class="o">==</span> <span class="o">-</span><span class="mi">1</span><span class="p">)</span>
      <span class="k">return</span> <span class="p">(</span><span class="nb">false</span><span class="p">);</span>
    <span class="k">if</span> <span class="p">((</span><span class="kt">int</span><span class="p">)</span><span class="n">cloud</span><span class="p">.</span><span class="n">width</span> <span class="o">*</span> <span class="n">cloud</span><span class="p">.</span><span class="n">height</span> <span class="o">!=</span> <span class="mi">1</span><span class="p">)</span>
      <span class="k">return</span> <span class="p">(</span><span class="nb">false</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="k">catch</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">InvalidConversionException</span> <span class="n">e</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="k">return</span> <span class="p">(</span><span class="nb">false</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="c1">// Treat the VFH signature as a single Point Cloud</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">VFHSignature308</span><span class="o">&gt;</span> <span class="n">point</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="n">path</span><span class="p">.</span><span class="n">string</span> <span class="p">(),</span> <span class="n">point</span><span class="p">);</span>
  <span class="n">vfh</span><span class="p">.</span><span class="n">second</span><span class="p">.</span><span class="n">resize</span> <span class="p">(</span><span class="mi">308</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">vector</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PCLPointField</span><span class="o">&gt;</span> <span class="n">fields</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">getFieldIndex</span> <span class="p">(</span><span class="n">point</span><span class="p">,</span> <span class="s">&quot;vfh&quot;</span><span class="p">,</span> <span class="n">fields</span><span class="p">);</span>

  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">fields</span><span class="p">[</span><span class="n">vfh_idx</span><span class="p">].</span><span class="n">count</span><span class="p">;</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">vfh</span><span class="p">.</span><span class="n">second</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="o">=</span> <span class="n">point</span><span class="p">.</span><span class="n">points</span><span class="p">[</span><span class="mi">0</span><span class="p">].</span><span class="n">histogram</span><span class="p">[</span><span class="n">i</span><span class="p">];</span>
  <span class="p">}</span>
  <span class="n">vfh</span><span class="p">.</span><span class="n">first</span> <span class="o">=</span> <span class="n">path</span><span class="p">.</span><span class="n">string</span> <span class="p">();</span>
  <span class="k">return</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
<span class="p">}</span>

<span class="cm">/** \brief Load a set of VFH features that will act as the model (training data)</span>
<span class="cm">  * \param argc the number of arguments (pass from main ())</span>
<span class="cm">  * \param argv the actual command line arguments (pass from main ())</span>
<span class="cm">  * \param extension the file extension containing the VFH features</span>
<span class="cm">  * \param models the resultant vector of histogram models</span>
<span class="cm">  */</span>
<span class="kt">void</span>
<span class="nf">loadFeatureModels</span> <span class="p">(</span><span class="k">const</span> <span class="n">boost</span><span class="o">::</span><span class="n">filesystem</span><span class="o">::</span><span class="n">path</span> <span class="o">&amp;</span><span class="n">base_dir</span><span class="p">,</span> <span class="k">const</span> <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="o">&amp;</span><span class="n">extension</span><span class="p">,</span> 
                   <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">vfh_model</span><span class="o">&gt;</span> <span class="o">&amp;</span><span class="n">models</span><span class="p">)</span>
<span class="p">{</span>
  <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">boost</span><span class="o">::</span><span class="n">filesystem</span><span class="o">::</span><span class="n">exists</span> <span class="p">(</span><span class="n">base_dir</span><span class="p">)</span> <span class="o">&amp;&amp;</span> <span class="o">!</span><span class="n">boost</span><span class="o">::</span><span class="n">filesystem</span><span class="o">::</span><span class="n">is_directory</span> <span class="p">(</span><span class="n">base_dir</span><span class="p">))</span>
    <span class="k">return</span><span class="p">;</span>

  <span class="k">for</span> <span class="p">(</span><span class="n">boost</span><span class="o">::</span><span class="n">filesystem</span><span class="o">::</span><span class="n">directory_iterator</span> <span class="n">it</span> <span class="p">(</span><span class="n">base_dir</span><span class="p">);</span> <span class="n">it</span> <span class="o">!=</span> <span class="n">boost</span><span class="o">::</span><span class="n">filesystem</span><span class="o">::</span><span class="n">directory_iterator</span> <span class="p">();</span> <span class="o">++</span><span class="n">it</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">boost</span><span class="o">::</span><span class="n">filesystem</span><span class="o">::</span><span class="n">is_directory</span> <span class="p">(</span><span class="n">it</span><span class="o">-&gt;</span><span class="n">status</span> <span class="p">()))</span>
    <span class="p">{</span>
      <span class="n">std</span><span class="o">::</span><span class="n">stringstream</span> <span class="n">ss</span><span class="p">;</span>
      <span class="n">ss</span> <span class="o">&lt;&lt;</span> <span class="n">it</span><span class="o">-&gt;</span><span class="n">path</span> <span class="p">();</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_highlight</span> <span class="p">(</span><span class="s">&quot;Loading %s (%lu models loaded so far).</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">ss</span><span class="p">.</span><span class="n">str</span> <span class="p">().</span><span class="n">c_str</span> <span class="p">(),</span> <span class="p">(</span><span class="kt">unsigned</span> <span class="kt">long</span><span class="p">)</span><span class="n">models</span><span class="p">.</span><span class="n">size</span> <span class="p">());</span>
      <span class="n">loadFeatureModels</span> <span class="p">(</span><span class="n">it</span><span class="o">-&gt;</span><span class="n">path</span> <span class="p">(),</span> <span class="n">extension</span><span class="p">,</span> <span class="n">models</span><span class="p">);</span>
    <span class="p">}</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">boost</span><span class="o">::</span><span class="n">filesystem</span><span class="o">::</span><span class="n">is_regular_file</span> <span class="p">(</span><span class="n">it</span><span class="o">-&gt;</span><span class="n">status</span> <span class="p">())</span> <span class="o">&amp;&amp;</span> <span class="n">boost</span><span class="o">::</span><span class="n">filesystem</span><span class="o">::</span><span class="n">extension</span> <span class="p">(</span><span class="n">it</span><span class="o">-&gt;</span><span class="n">path</span> <span class="p">())</span> <span class="o">==</span> <span class="n">extension</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">vfh_model</span> <span class="n">m</span><span class="p">;</span>
      <span class="k">if</span> <span class="p">(</span><span class="n">loadHist</span> <span class="p">(</span><span class="n">base_dir</span> <span class="o">/</span> <span class="n">it</span><span class="o">-&gt;</span><span class="n">path</span> <span class="p">().</span><span class="n">filename</span> <span class="p">(),</span> <span class="n">m</span><span class="p">))</span>
        <span class="n">models</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">m</span><span class="p">);</span>
    <span class="p">}</span>
  <span class="p">}</span>
<span class="p">}</span>

<span class="kt">int</span>
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">argc</span> <span class="o">&lt;</span> <span class="mi">2</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">PCL_ERROR</span> <span class="p">(</span><span class="s">&quot;Need at least two parameters! Syntax is: %s [model_directory] [options]</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">extension</span> <span class="p">(</span><span class="s">&quot;.pcd&quot;</span><span class="p">);</span>
  <span class="n">transform</span> <span class="p">(</span><span class="n">extension</span><span class="p">.</span><span class="n">begin</span> <span class="p">(),</span> <span class="n">extension</span><span class="p">.</span><span class="n">end</span> <span class="p">(),</span> <span class="n">extension</span><span class="p">.</span><span class="n">begin</span> <span class="p">(),</span> <span class="p">(</span><span class="kt">int</span><span class="p">(</span><span class="o">*</span><span class="p">)(</span><span class="kt">int</span><span class="p">))</span><span class="n">tolower</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">kdtree_idx_file_name</span> <span class="o">=</span> <span class="s">&quot;kdtree.idx&quot;</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">training_data_h5_file_name</span> <span class="o">=</span> <span class="s">&quot;training_data.h5&quot;</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">training_data_list_file_name</span> <span class="o">=</span> <span class="s">&quot;training_data.list&quot;</span><span class="p">;</span>

  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">vfh_model</span><span class="o">&gt;</span> <span class="n">models</span><span class="p">;</span>

  <span class="c1">// Load the model histograms</span>
  <span class="n">loadFeatureModels</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">1</span><span class="p">],</span> <span class="n">extension</span><span class="p">,</span> <span class="n">models</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_highlight</span> <span class="p">(</span><span class="s">&quot;Loaded %d VFH models. Creating training data %s/%s.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> 
      <span class="p">(</span><span class="kt">int</span><span class="p">)</span><span class="n">models</span><span class="p">.</span><span class="n">size</span> <span class="p">(),</span> <span class="n">training_data_h5_file_name</span><span class="p">.</span><span class="n">c_str</span> <span class="p">(),</span> <span class="n">training_data_list_file_name</span><span class="p">.</span><span class="n">c_str</span> <span class="p">());</span>

  <span class="c1">// Convert data into FLANN format</span>
  <span class="n">flann</span><span class="o">::</span><span class="n">Matrix</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="n">data</span> <span class="p">(</span><span class="k">new</span> <span class="kt">float</span><span class="p">[</span><span class="n">models</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">*</span> <span class="n">models</span><span class="p">[</span><span class="mi">0</span><span class="p">].</span><span class="n">second</span><span class="p">.</span><span class="n">size</span> <span class="p">()],</span> <span class="n">models</span><span class="p">.</span><span class="n">size</span> <span class="p">(),</span> <span class="n">models</span><span class="p">[</span><span class="mi">0</span><span class="p">].</span><span class="n">second</span><span class="p">.</span><span class="n">size</span> <span class="p">());</span>

  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">data</span><span class="p">.</span><span class="n">rows</span><span class="p">;</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
    <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">j</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">j</span> <span class="o">&lt;</span> <span class="n">data</span><span class="p">.</span><span class="n">cols</span><span class="p">;</span> <span class="o">++</span><span class="n">j</span><span class="p">)</span>
      <span class="n">data</span><span class="p">[</span><span class="n">i</span><span class="p">][</span><span class="n">j</span><span class="p">]</span> <span class="o">=</span> <span class="n">models</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">second</span><span class="p">[</span><span class="n">j</span><span class="p">];</span>

  <span class="c1">// Save data to disk (list of models)</span>
  <span class="n">flann</span><span class="o">::</span><span class="n">save_to_file</span> <span class="p">(</span><span class="n">data</span><span class="p">,</span> <span class="n">training_data_h5_file_name</span><span class="p">,</span> <span class="s">&quot;training_data&quot;</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">ofstream</span> <span class="n">fs</span><span class="p">;</span>
  <span class="n">fs</span><span class="p">.</span><span class="n">open</span> <span class="p">(</span><span class="n">training_data_list_file_name</span><span class="p">.</span><span class="n">c_str</span> <span class="p">());</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">models</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
    <span class="n">fs</span> <span class="o">&lt;&lt;</span> <span class="n">models</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">first</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
  <span class="n">fs</span><span class="p">.</span><span class="n">close</span> <span class="p">();</span>
 
  <span class="c1">// Build the tree index and save it to disk</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_error</span> <span class="p">(</span><span class="s">&quot;Building the kdtree index (%s) for %d elements...</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">kdtree_idx_file_name</span><span class="p">.</span><span class="n">c_str</span> <span class="p">(),</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span><span class="n">data</span><span class="p">.</span><span class="n">rows</span><span class="p">);</span>
  <span class="n">flann</span><span class="o">::</span><span class="n">Index</span><span class="o">&lt;</span><span class="n">flann</span><span class="o">::</span><span class="n">ChiSquareDistance</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="n">index</span> <span class="p">(</span><span class="n">data</span><span class="p">,</span> <span class="n">flann</span><span class="o">::</span><span class="n">LinearIndexParams</span> <span class="p">());</span>
  <span class="c1">//flann::Index&lt;flann::ChiSquareDistance&lt;float&gt; &gt; index (data, flann::KDTreeIndexParams (4));</span>
  <span class="n">index</span><span class="p">.</span><span class="n">buildIndex</span> <span class="p">();</span>
  <span class="n">index</span><span class="p">.</span><span class="n">save</span> <span class="p">(</span><span class="n">kdtree_idx_file_name</span><span class="p">);</span>
  <span class="k">delete</span><span class="p">[]</span> <span class="n">data</span><span class="p">.</span><span class="n">ptr</span> <span class="p">();</span>

  <span class="k">return</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
<p>In the following paragraphs we will explain what the above code does (or should
do). We&#8217;ll begin with the <tt class="docutils literal"><span class="pre">main</span></tt> function.</p>
<p>We begin by loading a set of feature models from a directory given as the first
command line argument (see details for running the example below). The
<tt class="docutils literal"><span class="pre">loadFeatureModels</span></tt> method does nothing but recursively traverse a set of
directories and subdirectories, and loads in all <em>.PCD</em> files it finds. In
<tt class="docutils literal"><span class="pre">loadFeatureModels</span></tt>, we call <tt class="docutils literal"><span class="pre">loadHist</span></tt>, which will attempt to open each
PCD file found, read its header, and check whether it contains a VFH signature
or not. Together with the VFH signature we also store the PCD file name into a
<tt class="docutils literal"><span class="pre">vfh_model</span></tt> pair.</p>
<p>Once all VFH features have been loaded, we convert them to FLANN format, using:</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Convert data into FLANN format</span>
  <span class="n">flann</span><span class="o">::</span><span class="n">Matrix</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="n">data</span> <span class="p">(</span><span class="k">new</span> <span class="kt">float</span><span class="p">[</span><span class="n">models</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">*</span> <span class="n">models</span><span class="p">[</span><span class="mi">0</span><span class="p">].</span><span class="n">second</span><span class="p">.</span><span class="n">size</span> <span class="p">()],</span> <span class="n">models</span><span class="p">.</span><span class="n">size</span> <span class="p">(),</span> <span class="n">models</span><span class="p">[</span><span class="mi">0</span><span class="p">].</span><span class="n">second</span><span class="p">.</span><span class="n">size</span> <span class="p">());</span>

  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">data</span><span class="p">.</span><span class="n">rows</span><span class="p">;</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
    <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">j</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">j</span> <span class="o">&lt;</span> <span class="n">data</span><span class="p">.</span><span class="n">cols</span><span class="p">;</span> <span class="o">++</span><span class="n">j</span><span class="p">)</span>
      <span class="n">data</span><span class="p">[</span><span class="n">i</span><span class="p">][</span><span class="n">j</span><span class="p">]</span> <span class="o">=</span> <span class="n">models</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">second</span><span class="p">[</span><span class="n">j</span><span class="p">];</span>
</pre></div>
</div>
<p>Since we&#8217;re lazy, and we want to use this data (and not reload it again by crawling the directory structure in the testing phase), we dump the data to disk:</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Save data to disk (list of models)</span>
  <span class="n">flann</span><span class="o">::</span><span class="n">save_to_file</span> <span class="p">(</span><span class="n">data</span><span class="p">,</span> <span class="n">training_data_h5_file_name</span><span class="p">,</span> <span class="s">&quot;training_data&quot;</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">ofstream</span> <span class="n">fs</span><span class="p">;</span>
  <span class="n">fs</span><span class="p">.</span><span class="n">open</span> <span class="p">(</span><span class="n">training_data_list_file_name</span><span class="p">.</span><span class="n">c_str</span> <span class="p">());</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">models</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
    <span class="n">fs</span> <span class="o">&lt;&lt;</span> <span class="n">models</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">first</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
  <span class="n">fs</span><span class="p">.</span><span class="n">close</span> <span class="p">();</span>
</pre></div>
</div>
<p>Finally, we create the KdTree, and save its structure to disk:</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_error</span> <span class="p">(</span><span class="s">&quot;Building the kdtree index (%s) for %d elements...</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">kdtree_idx_file_name</span><span class="p">.</span><span class="n">c_str</span> <span class="p">(),</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span><span class="n">data</span><span class="p">.</span><span class="n">rows</span><span class="p">);</span>
  <span class="n">flann</span><span class="o">::</span><span class="n">Index</span><span class="o">&lt;</span><span class="n">flann</span><span class="o">::</span><span class="n">ChiSquareDistance</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="n">index</span> <span class="p">(</span><span class="n">data</span><span class="p">,</span> <span class="n">flann</span><span class="o">::</span><span class="n">LinearIndexParams</span> <span class="p">());</span>
  <span class="c1">//flann::Index&lt;flann::ChiSquareDistance&lt;float&gt; &gt; index (data, flann::KDTreeIndexParams (4));</span>
  <span class="n">index</span><span class="p">.</span><span class="n">buildIndex</span> <span class="p">();</span>
  <span class="n">index</span><span class="p">.</span><span class="n">save</span> <span class="p">(</span><span class="n">kdtree_idx_file_name</span><span class="p">);</span>
</pre></div>
</div>
<p>Here we will use a <tt class="docutils literal"><span class="pre">LinearIndex</span></tt>, which does a brute-force search using a
Chi-Square distance metric (see <a class="reference internal" href="vfh_estimation.php#vfh" id="id1">[VFH]</a> for more information). For building a
proper kd-tree, comment line 1 and uncomment line 2 in the code snippet above.
The most important difference between a LinearIndex and a KDTreeIndex in FLANN
is that the KDTree will be much faster, while producing approximate nearest
neighbor results, rather than absolute.</p>
<p>So, we&#8217;re done with training. To summarize:</p>
<blockquote>
<div><ol class="arabic simple">
<li>we crawled a directory structure, looked at all the .PCD files we found, tested them whether they are VFH signatures and loaded them in memory;</li>
<li>we converted the data into FLANN format and dumped it to disk;</li>
<li>we built a kd-tree structure and dumped it to disk.</li>
</ol>
</div></blockquote>
</div>
<div class="section" id="testing">
<h1>Testing</h1>
<p>In the testing phase, we will illustrate how the system works by randomly
loading one of the files used in the training phase (feel free to supply your
own file here!), and checking the results of the tree.</p>
<p>Begin by copying and pasting the following code into your editor and save it as
<tt class="docutils literal"><span class="pre">nearest_neighbors.cpp</span></tt>.</p>
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
103
104
105
106
107
108
109
110
111
112
113
114
115
116
117
118
119
120
121
122
123
124
125
126
127
128
129
130
131
132
133
134
135
136
137
138
139
140
141
142
143
144
145
146
147
148
149
150
151
152
153
154
155
156
157
158
159
160
161
162
163
164
165
166
167
168
169
170
171
172
173
174
175
176
177
178
179
180
181
182
183
184
185
186
187
188
189
190
191
192
193
194
195
196
197
198
199
200
201
202
203
204
205
206
207
208
209
210
211
212
213
214
215
216
217
218
219
220
221
222
223
224
225
226
227
228
229
230
231
232
233
234
235
236
237
238
239
240
241
242
243
244
245
246
247
248
249
250
251
252
253
254
255
256
257
258
259
260
261
262
263
264
265
266
267
268
269
270
271
272
273
274</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/point_cloud.h&gt;</span>
<span class="cp">#include &lt;pcl/common/common.h&gt;</span>
<span class="cp">#include &lt;pcl/common/transforms.h&gt;</span>
<span class="cp">#include &lt;pcl/visualization/pcl_visualizer.h&gt;</span>
<span class="cp">#include &lt;pcl/console/parse.h&gt;</span>
<span class="cp">#include &lt;pcl/console/print.h&gt;</span>
<span class="cp">#include &lt;pcl/io/pcd_io.h&gt;</span>
<span class="cp">#include &lt;iostream&gt;</span>
<span class="cp">#include &lt;flann/flann.h&gt;</span>
<span class="cp">#include &lt;flann/io/hdf5.h&gt;</span>
<span class="cp">#include &lt;boost/filesystem.hpp&gt;</span>

<span class="k">typedef</span> <span class="n">std</span><span class="o">::</span><span class="n">pair</span><span class="o">&lt;</span><span class="n">std</span><span class="o">::</span><span class="n">string</span><span class="p">,</span> <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="n">vfh_model</span><span class="p">;</span>

<span class="cm">/** \brief Loads an n-D histogram file as a VFH signature</span>
<span class="cm">  * \param path the input file name</span>
<span class="cm">  * \param vfh the resultant VFH model</span>
<span class="cm">  */</span>
<span class="kt">bool</span>
<span class="nf">loadHist</span> <span class="p">(</span><span class="k">const</span> <span class="n">boost</span><span class="o">::</span><span class="n">filesystem</span><span class="o">::</span><span class="n">path</span> <span class="o">&amp;</span><span class="n">path</span><span class="p">,</span> <span class="n">vfh_model</span> <span class="o">&amp;</span><span class="n">vfh</span><span class="p">)</span>
<span class="p">{</span>
  <span class="kt">int</span> <span class="n">vfh_idx</span><span class="p">;</span>
  <span class="c1">// Load the file as a PCD</span>
  <span class="n">try</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">PCLPointCloud2</span> <span class="n">cloud</span><span class="p">;</span>
    <span class="kt">int</span> <span class="n">version</span><span class="p">;</span>
    <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector4f</span> <span class="n">origin</span><span class="p">;</span>
    <span class="n">Eigen</span><span class="o">::</span><span class="n">Quaternionf</span> <span class="n">orientation</span><span class="p">;</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">PCDReader</span> <span class="n">r</span><span class="p">;</span>
    <span class="kt">int</span> <span class="n">type</span><span class="p">;</span> <span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">idx</span><span class="p">;</span>
    <span class="n">r</span><span class="p">.</span><span class="n">readHeader</span> <span class="p">(</span><span class="n">path</span><span class="p">.</span><span class="n">string</span> <span class="p">(),</span> <span class="n">cloud</span><span class="p">,</span> <span class="n">origin</span><span class="p">,</span> <span class="n">orientation</span><span class="p">,</span> <span class="n">version</span><span class="p">,</span> <span class="n">type</span><span class="p">,</span> <span class="n">idx</span><span class="p">);</span>

    <span class="n">vfh_idx</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">getFieldIndex</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="s">&quot;vfh&quot;</span><span class="p">);</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">vfh_idx</span> <span class="o">==</span> <span class="o">-</span><span class="mi">1</span><span class="p">)</span>
      <span class="k">return</span> <span class="p">(</span><span class="nb">false</span><span class="p">);</span>
    <span class="k">if</span> <span class="p">((</span><span class="kt">int</span><span class="p">)</span><span class="n">cloud</span><span class="p">.</span><span class="n">width</span> <span class="o">*</span> <span class="n">cloud</span><span class="p">.</span><span class="n">height</span> <span class="o">!=</span> <span class="mi">1</span><span class="p">)</span>
      <span class="k">return</span> <span class="p">(</span><span class="nb">false</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="k">catch</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">InvalidConversionException</span> <span class="n">e</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="k">return</span> <span class="p">(</span><span class="nb">false</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="c1">// Treat the VFH signature as a single Point Cloud</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">VFHSignature308</span><span class="o">&gt;</span> <span class="n">point</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="n">path</span><span class="p">.</span><span class="n">string</span> <span class="p">(),</span> <span class="n">point</span><span class="p">);</span>
  <span class="n">vfh</span><span class="p">.</span><span class="n">second</span><span class="p">.</span><span class="n">resize</span> <span class="p">(</span><span class="mi">308</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">vector</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PCLPointField</span><span class="o">&gt;</span> <span class="n">fields</span><span class="p">;</span>
  <span class="n">getFieldIndex</span> <span class="p">(</span><span class="n">point</span><span class="p">,</span> <span class="s">&quot;vfh&quot;</span><span class="p">,</span> <span class="n">fields</span><span class="p">);</span>

  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">fields</span><span class="p">[</span><span class="n">vfh_idx</span><span class="p">].</span><span class="n">count</span><span class="p">;</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">vfh</span><span class="p">.</span><span class="n">second</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="o">=</span> <span class="n">point</span><span class="p">.</span><span class="n">points</span><span class="p">[</span><span class="mi">0</span><span class="p">].</span><span class="n">histogram</span><span class="p">[</span><span class="n">i</span><span class="p">];</span>
  <span class="p">}</span>
  <span class="n">vfh</span><span class="p">.</span><span class="n">first</span> <span class="o">=</span> <span class="n">path</span><span class="p">.</span><span class="n">string</span> <span class="p">();</span>
  <span class="k">return</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
<span class="p">}</span>


<span class="cm">/** \brief Search for the closest k neighbors</span>
<span class="cm">  * \param index the tree</span>
<span class="cm">  * \param model the query model</span>
<span class="cm">  * \param k the number of neighbors to search for</span>
<span class="cm">  * \param indices the resultant neighbor indices</span>
<span class="cm">  * \param distances the resultant neighbor distances</span>
<span class="cm">  */</span>
<span class="kr">inline</span> <span class="kt">void</span>
<span class="nf">nearestKSearch</span> <span class="p">(</span><span class="n">flann</span><span class="o">::</span><span class="n">Index</span><span class="o">&lt;</span><span class="n">flann</span><span class="o">::</span><span class="n">ChiSquareDistance</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="o">&amp;</span><span class="n">index</span><span class="p">,</span> <span class="k">const</span> <span class="n">vfh_model</span> <span class="o">&amp;</span><span class="n">model</span><span class="p">,</span> 
                <span class="kt">int</span> <span class="n">k</span><span class="p">,</span> <span class="n">flann</span><span class="o">::</span><span class="n">Matrix</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="o">&amp;</span><span class="n">indices</span><span class="p">,</span> <span class="n">flann</span><span class="o">::</span><span class="n">Matrix</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="o">&amp;</span><span class="n">distances</span><span class="p">)</span>
<span class="p">{</span>
  <span class="c1">// Query point</span>
  <span class="n">flann</span><span class="o">::</span><span class="n">Matrix</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="n">p</span> <span class="o">=</span> <span class="n">flann</span><span class="o">::</span><span class="n">Matrix</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span><span class="p">(</span><span class="k">new</span> <span class="kt">float</span><span class="p">[</span><span class="n">model</span><span class="p">.</span><span class="n">second</span><span class="p">.</span><span class="n">size</span> <span class="p">()],</span> <span class="mi">1</span><span class="p">,</span> <span class="n">model</span><span class="p">.</span><span class="n">second</span><span class="p">.</span><span class="n">size</span> <span class="p">());</span>
  <span class="n">memcpy</span> <span class="p">(</span><span class="o">&amp;</span><span class="n">p</span><span class="p">.</span><span class="n">ptr</span> <span class="p">()[</span><span class="mi">0</span><span class="p">],</span> <span class="o">&amp;</span><span class="n">model</span><span class="p">.</span><span class="n">second</span><span class="p">[</span><span class="mi">0</span><span class="p">],</span> <span class="n">p</span><span class="p">.</span><span class="n">cols</span> <span class="o">*</span> <span class="n">p</span><span class="p">.</span><span class="n">rows</span> <span class="o">*</span> <span class="k">sizeof</span> <span class="p">(</span><span class="kt">float</span><span class="p">));</span>

  <span class="n">indices</span> <span class="o">=</span> <span class="n">flann</span><span class="o">::</span><span class="n">Matrix</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span><span class="p">(</span><span class="k">new</span> <span class="kt">int</span><span class="p">[</span><span class="n">k</span><span class="p">],</span> <span class="mi">1</span><span class="p">,</span> <span class="n">k</span><span class="p">);</span>
  <span class="n">distances</span> <span class="o">=</span> <span class="n">flann</span><span class="o">::</span><span class="n">Matrix</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span><span class="p">(</span><span class="k">new</span> <span class="kt">float</span><span class="p">[</span><span class="n">k</span><span class="p">],</span> <span class="mi">1</span><span class="p">,</span> <span class="n">k</span><span class="p">);</span>
  <span class="n">index</span><span class="p">.</span><span class="n">knnSearch</span> <span class="p">(</span><span class="n">p</span><span class="p">,</span> <span class="n">indices</span><span class="p">,</span> <span class="n">distances</span><span class="p">,</span> <span class="n">k</span><span class="p">,</span> <span class="n">flann</span><span class="o">::</span><span class="n">SearchParams</span> <span class="p">(</span><span class="mi">512</span><span class="p">));</span>
  <span class="k">delete</span><span class="p">[]</span> <span class="n">p</span><span class="p">.</span><span class="n">ptr</span> <span class="p">();</span>
<span class="p">}</span>

<span class="cm">/** \brief Load the list of file model names from an ASCII file</span>
<span class="cm">  * \param models the resultant list of model name</span>
<span class="cm">  * \param filename the input file name</span>
<span class="cm">  */</span>
<span class="kt">bool</span>
<span class="nf">loadFileList</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">vfh_model</span><span class="o">&gt;</span> <span class="o">&amp;</span><span class="n">models</span><span class="p">,</span> <span class="k">const</span> <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="o">&amp;</span><span class="n">filename</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">ifstream</span> <span class="n">fs</span><span class="p">;</span>
  <span class="n">fs</span><span class="p">.</span><span class="n">open</span> <span class="p">(</span><span class="n">filename</span><span class="p">.</span><span class="n">c_str</span> <span class="p">());</span>
  <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">fs</span><span class="p">.</span><span class="n">is_open</span> <span class="p">()</span> <span class="o">||</span> <span class="n">fs</span><span class="p">.</span><span class="n">fail</span> <span class="p">())</span>
    <span class="k">return</span> <span class="p">(</span><span class="nb">false</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">line</span><span class="p">;</span>
  <span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">fs</span><span class="p">.</span><span class="n">eof</span> <span class="p">())</span>
  <span class="p">{</span>
    <span class="n">getline</span> <span class="p">(</span><span class="n">fs</span><span class="p">,</span> <span class="n">line</span><span class="p">);</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">line</span><span class="p">.</span><span class="n">empty</span> <span class="p">())</span>
      <span class="k">continue</span><span class="p">;</span>
    <span class="n">vfh_model</span> <span class="n">m</span><span class="p">;</span>
    <span class="n">m</span><span class="p">.</span><span class="n">first</span> <span class="o">=</span> <span class="n">line</span><span class="p">;</span>
    <span class="n">models</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">m</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="n">fs</span><span class="p">.</span><span class="n">close</span> <span class="p">();</span>
  <span class="k">return</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
<span class="p">}</span>

<span class="kt">int</span>
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="kt">int</span> <span class="n">k</span> <span class="o">=</span> <span class="mi">6</span><span class="p">;</span>

  <span class="kt">double</span> <span class="n">thresh</span> <span class="o">=</span> <span class="n">DBL_MAX</span><span class="p">;</span>     <span class="c1">// No threshold, disabled by default</span>

  <span class="k">if</span> <span class="p">(</span><span class="n">argc</span> <span class="o">&lt;</span> <span class="mi">2</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_error</span> 
      <span class="p">(</span><span class="s">&quot;Need at least three parameters! Syntax is: %s &lt;query_vfh_model.pcd&gt; [options] {kdtree.idx} {training_data.h5} {training_data.list}</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_info</span> <span class="p">(</span><span class="s">&quot;    where [options] are:  -k      = number of nearest neighbors to search for in the tree (default: &quot;</span><span class="p">);</span> 
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_value</span> <span class="p">(</span><span class="s">&quot;%d&quot;</span><span class="p">,</span> <span class="n">k</span><span class="p">);</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_info</span> <span class="p">(</span><span class="s">&quot;)</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_info</span> <span class="p">(</span><span class="s">&quot;                          -thresh = maximum distance threshold for a model to be considered VALID (default: &quot;</span><span class="p">);</span> 
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_value</span> <span class="p">(</span><span class="s">&quot;%f&quot;</span><span class="p">,</span> <span class="n">thresh</span><span class="p">);</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_info</span> <span class="p">(</span><span class="s">&quot;)</span><span class="se">\n\n</span><span class="s">&quot;</span><span class="p">);</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">extension</span> <span class="p">(</span><span class="s">&quot;.pcd&quot;</span><span class="p">);</span>
  <span class="n">transform</span> <span class="p">(</span><span class="n">extension</span><span class="p">.</span><span class="n">begin</span> <span class="p">(),</span> <span class="n">extension</span><span class="p">.</span><span class="n">end</span> <span class="p">(),</span> <span class="n">extension</span><span class="p">.</span><span class="n">begin</span> <span class="p">(),</span> <span class="p">(</span><span class="kt">int</span><span class="p">(</span><span class="o">*</span><span class="p">)(</span><span class="kt">int</span><span class="p">))</span><span class="n">tolower</span><span class="p">);</span>

  <span class="c1">// Load the test histogram</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">pcd_indices</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_file_extension_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;.pcd&quot;</span><span class="p">);</span>
  <span class="n">vfh_model</span> <span class="n">histogram</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">loadHist</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="n">pcd_indices</span><span class="p">.</span><span class="n">at</span> <span class="p">(</span><span class="mi">0</span><span class="p">)],</span> <span class="n">histogram</span><span class="p">))</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_error</span> <span class="p">(</span><span class="s">&quot;Cannot load test file %s</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">argv</span><span class="p">[</span><span class="n">pcd_indices</span><span class="p">.</span><span class="n">at</span> <span class="p">(</span><span class="mi">0</span><span class="p">)]);</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-thresh&quot;</span><span class="p">,</span> <span class="n">thresh</span><span class="p">);</span>
  <span class="c1">// Search for the k closest matches</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-k&quot;</span><span class="p">,</span> <span class="n">k</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_highlight</span> <span class="p">(</span><span class="s">&quot;Using &quot;</span><span class="p">);</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_value</span> <span class="p">(</span><span class="s">&quot;%d&quot;</span><span class="p">,</span> <span class="n">k</span><span class="p">);</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_info</span> <span class="p">(</span><span class="s">&quot; nearest neighbors.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">kdtree_idx_file_name</span> <span class="o">=</span> <span class="s">&quot;kdtree.idx&quot;</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">training_data_h5_file_name</span> <span class="o">=</span> <span class="s">&quot;training_data.h5&quot;</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">training_data_list_file_name</span> <span class="o">=</span> <span class="s">&quot;training_data.list&quot;</span><span class="p">;</span>

  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">vfh_model</span><span class="o">&gt;</span> <span class="n">models</span><span class="p">;</span>
  <span class="n">flann</span><span class="o">::</span><span class="n">Matrix</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">k_indices</span><span class="p">;</span>
  <span class="n">flann</span><span class="o">::</span><span class="n">Matrix</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="n">k_distances</span><span class="p">;</span>
  <span class="n">flann</span><span class="o">::</span><span class="n">Matrix</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="n">data</span><span class="p">;</span>
  <span class="c1">// Check if the data has already been saved to disk</span>
  <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">boost</span><span class="o">::</span><span class="n">filesystem</span><span class="o">::</span><span class="n">exists</span> <span class="p">(</span><span class="s">&quot;training_data.h5&quot;</span><span class="p">)</span> <span class="o">||</span> <span class="o">!</span><span class="n">boost</span><span class="o">::</span><span class="n">filesystem</span><span class="o">::</span><span class="n">exists</span> <span class="p">(</span><span class="s">&quot;training_data.list&quot;</span><span class="p">))</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_error</span> <span class="p">(</span><span class="s">&quot;Could not find training data models files %s and %s!</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> 
        <span class="n">training_data_h5_file_name</span><span class="p">.</span><span class="n">c_str</span> <span class="p">(),</span> <span class="n">training_data_list_file_name</span><span class="p">.</span><span class="n">c_str</span> <span class="p">());</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="k">else</span>
  <span class="p">{</span>
    <span class="n">loadFileList</span> <span class="p">(</span><span class="n">models</span><span class="p">,</span> <span class="n">training_data_list_file_name</span><span class="p">);</span>
    <span class="n">flann</span><span class="o">::</span><span class="n">load_from_file</span> <span class="p">(</span><span class="n">data</span><span class="p">,</span> <span class="n">training_data_h5_file_name</span><span class="p">,</span> <span class="s">&quot;training_data&quot;</span><span class="p">);</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_highlight</span> <span class="p">(</span><span class="s">&quot;Training data found. Loaded %d VFH models from %s/%s.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> 
        <span class="p">(</span><span class="kt">int</span><span class="p">)</span><span class="n">data</span><span class="p">.</span><span class="n">rows</span><span class="p">,</span> <span class="n">training_data_h5_file_name</span><span class="p">.</span><span class="n">c_str</span> <span class="p">(),</span> <span class="n">training_data_list_file_name</span><span class="p">.</span><span class="n">c_str</span> <span class="p">());</span>
  <span class="p">}</span>

  <span class="c1">// Check if the tree index has already been saved to disk</span>
  <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">boost</span><span class="o">::</span><span class="n">filesystem</span><span class="o">::</span><span class="n">exists</span> <span class="p">(</span><span class="n">kdtree_idx_file_name</span><span class="p">))</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_error</span> <span class="p">(</span><span class="s">&quot;Could not find kd-tree index in file %s!&quot;</span><span class="p">,</span> <span class="n">kdtree_idx_file_name</span><span class="p">.</span><span class="n">c_str</span> <span class="p">());</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="k">else</span>
  <span class="p">{</span>
    <span class="n">flann</span><span class="o">::</span><span class="n">Index</span><span class="o">&lt;</span><span class="n">flann</span><span class="o">::</span><span class="n">ChiSquareDistance</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="n">index</span> <span class="p">(</span><span class="n">data</span><span class="p">,</span> <span class="n">flann</span><span class="o">::</span><span class="n">SavedIndexParams</span> <span class="p">(</span><span class="s">&quot;kdtree.idx&quot;</span><span class="p">));</span>
    <span class="n">index</span><span class="p">.</span><span class="n">buildIndex</span> <span class="p">();</span>
    <span class="n">nearestKSearch</span> <span class="p">(</span><span class="n">index</span><span class="p">,</span> <span class="n">histogram</span><span class="p">,</span> <span class="n">k</span><span class="p">,</span> <span class="n">k_indices</span><span class="p">,</span> <span class="n">k_distances</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="c1">// Output the results on screen</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_highlight</span> <span class="p">(</span><span class="s">&quot;The closest %d neighbors for %s are:</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">k</span><span class="p">,</span> <span class="n">argv</span><span class="p">[</span><span class="n">pcd_indices</span><span class="p">[</span><span class="mi">0</span><span class="p">]]);</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">int</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">k</span><span class="p">;</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_info</span> <span class="p">(</span><span class="s">&quot;    %d - %s (%d) with a distance of: %f</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> 
        <span class="n">i</span><span class="p">,</span> <span class="n">models</span><span class="p">.</span><span class="n">at</span> <span class="p">(</span><span class="n">k_indices</span><span class="p">[</span><span class="mi">0</span><span class="p">][</span><span class="n">i</span><span class="p">]).</span><span class="n">first</span><span class="p">.</span><span class="n">c_str</span> <span class="p">(),</span> <span class="n">k_indices</span><span class="p">[</span><span class="mi">0</span><span class="p">][</span><span class="n">i</span><span class="p">],</span> <span class="n">k_distances</span><span class="p">[</span><span class="mi">0</span><span class="p">][</span><span class="n">i</span><span class="p">]);</span>

  <span class="c1">// Load the results</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="n">p</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;VFH Cluster Classifier&quot;</span><span class="p">);</span>
  <span class="kt">int</span> <span class="n">y_s</span> <span class="o">=</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span><span class="n">floor</span> <span class="p">(</span><span class="n">sqrt</span> <span class="p">((</span><span class="kt">double</span><span class="p">)</span><span class="n">k</span><span class="p">));</span>
  <span class="kt">int</span> <span class="n">x_s</span> <span class="o">=</span> <span class="n">y_s</span> <span class="o">+</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span><span class="n">ceil</span> <span class="p">((</span><span class="n">k</span> <span class="o">/</span> <span class="p">(</span><span class="kt">double</span><span class="p">)</span><span class="n">y_s</span><span class="p">)</span> <span class="o">-</span> <span class="n">y_s</span><span class="p">);</span>
  <span class="kt">double</span> <span class="n">x_step</span> <span class="o">=</span> <span class="p">(</span><span class="kt">double</span><span class="p">)(</span><span class="mi">1</span> <span class="o">/</span> <span class="p">(</span><span class="kt">double</span><span class="p">)</span><span class="n">x_s</span><span class="p">);</span>
  <span class="kt">double</span> <span class="n">y_step</span> <span class="o">=</span> <span class="p">(</span><span class="kt">double</span><span class="p">)(</span><span class="mi">1</span> <span class="o">/</span> <span class="p">(</span><span class="kt">double</span><span class="p">)</span><span class="n">y_s</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_highlight</span> <span class="p">(</span><span class="s">&quot;Preparing to load &quot;</span><span class="p">);</span> 
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_value</span> <span class="p">(</span><span class="s">&quot;%d&quot;</span><span class="p">,</span> <span class="n">k</span><span class="p">);</span> 
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_info</span> <span class="p">(</span><span class="s">&quot; files (&quot;</span><span class="p">);</span> 
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_value</span> <span class="p">(</span><span class="s">&quot;%d&quot;</span><span class="p">,</span> <span class="n">x_s</span><span class="p">);</span>    
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_info</span> <span class="p">(</span><span class="s">&quot;x&quot;</span><span class="p">);</span> 
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_value</span> <span class="p">(</span><span class="s">&quot;%d&quot;</span><span class="p">,</span> <span class="n">y_s</span><span class="p">);</span> 
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_info</span> <span class="p">(</span><span class="s">&quot; / &quot;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_value</span> <span class="p">(</span><span class="s">&quot;%f&quot;</span><span class="p">,</span> <span class="n">x_step</span><span class="p">);</span> 
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_info</span> <span class="p">(</span><span class="s">&quot;x&quot;</span><span class="p">);</span> 
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_value</span> <span class="p">(</span><span class="s">&quot;%f&quot;</span><span class="p">,</span> <span class="n">y_step</span><span class="p">);</span> 
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_info</span> <span class="p">(</span><span class="s">&quot;)</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>

  <span class="kt">int</span> <span class="n">viewport</span> <span class="o">=</span> <span class="mi">0</span><span class="p">,</span> <span class="n">l</span> <span class="o">=</span> <span class="mi">0</span><span class="p">,</span> <span class="n">m</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">int</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">k</span><span class="p">;</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">cloud_name</span> <span class="o">=</span> <span class="n">models</span><span class="p">.</span><span class="n">at</span> <span class="p">(</span><span class="n">k_indices</span><span class="p">[</span><span class="mi">0</span><span class="p">][</span><span class="n">i</span><span class="p">]).</span><span class="n">first</span><span class="p">;</span>
    <span class="n">boost</span><span class="o">::</span><span class="n">replace_last</span> <span class="p">(</span><span class="n">cloud_name</span><span class="p">,</span> <span class="s">&quot;_vfh&quot;</span><span class="p">,</span> <span class="s">&quot;&quot;</span><span class="p">);</span>

    <span class="n">p</span><span class="p">.</span><span class="n">createViewPort</span> <span class="p">(</span><span class="n">l</span> <span class="o">*</span> <span class="n">x_step</span><span class="p">,</span> <span class="n">m</span> <span class="o">*</span> <span class="n">y_step</span><span class="p">,</span> <span class="p">(</span><span class="n">l</span> <span class="o">+</span> <span class="mi">1</span><span class="p">)</span> <span class="o">*</span> <span class="n">x_step</span><span class="p">,</span> <span class="p">(</span><span class="n">m</span> <span class="o">+</span> <span class="mi">1</span><span class="p">)</span> <span class="o">*</span> <span class="n">y_step</span><span class="p">,</span> <span class="n">viewport</span><span class="p">);</span>
    <span class="n">l</span><span class="o">++</span><span class="p">;</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">l</span> <span class="o">&gt;=</span> <span class="n">x_s</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">l</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
      <span class="n">m</span><span class="o">++</span><span class="p">;</span>
    <span class="p">}</span>

    <span class="n">pcl</span><span class="o">::</span><span class="n">PCLPointCloud2</span> <span class="n">cloud</span><span class="p">;</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_highlight</span> <span class="p">(</span><span class="n">stderr</span><span class="p">,</span> <span class="s">&quot;Loading &quot;</span><span class="p">);</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_value</span> <span class="p">(</span><span class="n">stderr</span><span class="p">,</span> <span class="s">&quot;%s &quot;</span><span class="p">,</span> <span class="n">cloud_name</span><span class="p">.</span><span class="n">c_str</span> <span class="p">());</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="n">cloud_name</span><span class="p">,</span> <span class="n">cloud</span><span class="p">)</span> <span class="o">==</span> <span class="o">-</span><span class="mi">1</span><span class="p">)</span>
      <span class="k">break</span><span class="p">;</span>

    <span class="c1">// Convert from blob to PointCloud</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">cloud_xyz</span><span class="p">;</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">fromPCLPointCloud2</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">cloud_xyz</span><span class="p">);</span>

    <span class="k">if</span> <span class="p">(</span><span class="n">cloud_xyz</span><span class="p">.</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">==</span> <span class="mi">0</span><span class="p">)</span>
      <span class="k">break</span><span class="p">;</span>

    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_info</span> <span class="p">(</span><span class="s">&quot;[done, &quot;</span><span class="p">);</span> 
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_value</span> <span class="p">(</span><span class="s">&quot;%d&quot;</span><span class="p">,</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span><span class="n">cloud_xyz</span><span class="p">.</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">());</span> 
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_info</span> <span class="p">(</span><span class="s">&quot; points]</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_info</span> <span class="p">(</span><span class="s">&quot;Available dimensions: &quot;</span><span class="p">);</span> 
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_value</span> <span class="p">(</span><span class="s">&quot;%s</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">getFieldsList</span> <span class="p">(</span><span class="n">cloud</span><span class="p">).</span><span class="n">c_str</span> <span class="p">());</span>

    <span class="c1">// Demean the cloud</span>
    <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector4f</span> <span class="n">centroid</span><span class="p">;</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">compute3DCentroid</span> <span class="p">(</span><span class="n">cloud_xyz</span><span class="p">,</span> <span class="n">centroid</span><span class="p">);</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_xyz_demean</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">demeanPointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud_xyz</span><span class="p">,</span> <span class="n">centroid</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud_xyz_demean</span><span class="p">);</span>
    <span class="c1">// Add to renderer*</span>
    <span class="n">p</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">cloud_xyz_demean</span><span class="p">,</span> <span class="n">cloud_name</span><span class="p">,</span> <span class="n">viewport</span><span class="p">);</span>
    
    <span class="c1">// Check if the model found is within our inlier tolerance</span>
    <span class="n">std</span><span class="o">::</span><span class="n">stringstream</span> <span class="n">ss</span><span class="p">;</span>
    <span class="n">ss</span> <span class="o">&lt;&lt;</span> <span class="n">k_distances</span><span class="p">[</span><span class="mi">0</span><span class="p">][</span><span class="n">i</span><span class="p">];</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">k_distances</span><span class="p">[</span><span class="mi">0</span><span class="p">][</span><span class="n">i</span><span class="p">]</span> <span class="o">&gt;</span> <span class="n">thresh</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">p</span><span class="p">.</span><span class="n">addText</span> <span class="p">(</span><span class="n">ss</span><span class="p">.</span><span class="n">str</span> <span class="p">(),</span> <span class="mi">20</span><span class="p">,</span> <span class="mi">30</span><span class="p">,</span> <span class="mi">1</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="n">ss</span><span class="p">.</span><span class="n">str</span> <span class="p">(),</span> <span class="n">viewport</span><span class="p">);</span>  <span class="c1">// display the text with red</span>

      <span class="c1">// Create a red line</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">min_p</span><span class="p">,</span> <span class="n">max_p</span><span class="p">;</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">getMinMax3D</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_xyz_demean</span><span class="p">,</span> <span class="n">min_p</span><span class="p">,</span> <span class="n">max_p</span><span class="p">);</span>
      <span class="n">std</span><span class="o">::</span><span class="n">stringstream</span> <span class="n">line_name</span><span class="p">;</span>
      <span class="n">line_name</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;line_&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">i</span><span class="p">;</span>
      <span class="n">p</span><span class="p">.</span><span class="n">addLine</span> <span class="p">(</span><span class="n">min_p</span><span class="p">,</span> <span class="n">max_p</span><span class="p">,</span> <span class="mi">1</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="n">line_name</span><span class="p">.</span><span class="n">str</span> <span class="p">(),</span> <span class="n">viewport</span><span class="p">);</span>
      <span class="n">p</span><span class="p">.</span><span class="n">setShapeRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_LINE_WIDTH</span><span class="p">,</span> <span class="mi">5</span><span class="p">,</span> <span class="n">line_name</span><span class="p">.</span><span class="n">str</span> <span class="p">(),</span> <span class="n">viewport</span><span class="p">);</span>
    <span class="p">}</span>
    <span class="k">else</span>
      <span class="n">p</span><span class="p">.</span><span class="n">addText</span> <span class="p">(</span><span class="n">ss</span><span class="p">.</span><span class="n">str</span> <span class="p">(),</span> <span class="mi">20</span><span class="p">,</span> <span class="mi">30</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">1</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="n">ss</span><span class="p">.</span><span class="n">str</span> <span class="p">(),</span> <span class="n">viewport</span><span class="p">);</span>

    <span class="c1">// Increase the font size for the score*</span>
    <span class="n">p</span><span class="p">.</span><span class="n">setShapeRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_FONT_SIZE</span><span class="p">,</span> <span class="mi">18</span><span class="p">,</span> <span class="n">ss</span><span class="p">.</span><span class="n">str</span> <span class="p">(),</span> <span class="n">viewport</span><span class="p">);</span>

    <span class="c1">// Add the cluster name</span>
    <span class="n">p</span><span class="p">.</span><span class="n">addText</span> <span class="p">(</span><span class="n">cloud_name</span><span class="p">,</span> <span class="mi">20</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="n">cloud_name</span><span class="p">,</span> <span class="n">viewport</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="c1">// Add coordianate systems to all viewports</span>
  <span class="n">p</span><span class="p">.</span><span class="n">addCoordinateSystem</span> <span class="p">(</span><span class="mf">0.1</span><span class="p">,</span> <span class="s">&quot;global&quot;</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>

  <span class="n">p</span><span class="p">.</span><span class="n">spin</span> <span class="p">();</span>
  <span class="k">return</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
<p>The above code snippet is slightly larger, because we also included some
visualization routines and some other &#8220;eye candy&#8221; stuff.</p>
<p>In lines:</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">pcd_indices</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_file_extension_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;.pcd&quot;</span><span class="p">);</span>
  <span class="n">vfh_model</span> <span class="n">histogram</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">loadHist</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="n">pcd_indices</span><span class="p">.</span><span class="n">at</span> <span class="p">(</span><span class="mi">0</span><span class="p">)],</span> <span class="n">histogram</span><span class="p">))</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_error</span> <span class="p">(</span><span class="s">&quot;Cannot load test file %s</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">argv</span><span class="p">[</span><span class="n">pcd_indices</span><span class="p">.</span><span class="n">at</span> <span class="p">(</span><span class="mi">0</span><span class="p">)]);</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-thresh&quot;</span><span class="p">,</span> <span class="n">thresh</span><span class="p">);</span>
  <span class="c1">// Search for the k closest matches</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-k&quot;</span><span class="p">,</span> <span class="n">k</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_highlight</span> <span class="p">(</span><span class="s">&quot;Using &quot;</span><span class="p">);</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_value</span> <span class="p">(</span><span class="s">&quot;%d&quot;</span><span class="p">,</span> <span class="n">k</span><span class="p">);</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_info</span> <span class="p">(</span><span class="s">&quot; nearest neighbors.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
</pre></div>
</div>
<p>we load the first given user histogram (and ignore the rest). Then we proceed
at checking two command line parameters, namely <tt class="docutils literal"><span class="pre">-k</span></tt> which will define how
many nearest neighbors to check and display on screen, and <tt class="docutils literal"><span class="pre">-thresh</span></tt> which
defines a maximum distance metric after which we will start displaying red
lines (i.e., crossing) over the <strong>k</strong> models found on screen (eye candy!).</p>
<p>In lines:</p>
<div class="highlight-cpp"><div class="highlight"><pre>    <span class="n">loadFileList</span> <span class="p">(</span><span class="n">models</span><span class="p">,</span> <span class="n">training_data_list_file_name</span><span class="p">);</span>
    <span class="n">flann</span><span class="o">::</span><span class="n">load_from_file</span> <span class="p">(</span><span class="n">data</span><span class="p">,</span> <span class="n">training_data_h5_file_name</span><span class="p">,</span> <span class="s">&quot;training_data&quot;</span><span class="p">);</span>
</pre></div>
</div>
<p>we load the training data from disk, together with the list of file names that
we previously stored in <tt class="docutils literal"><span class="pre">build_tree.cpp</span></tt>. Then, we read the kd-tree and rebuild the index:</p>
<div class="highlight-cpp"><div class="highlight"><pre>    <span class="n">flann</span><span class="o">::</span><span class="n">Index</span><span class="o">&lt;</span><span class="n">flann</span><span class="o">::</span><span class="n">ChiSquareDistance</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="n">index</span> <span class="p">(</span><span class="n">data</span><span class="p">,</span> <span class="n">flann</span><span class="o">::</span><span class="n">SavedIndexParams</span> <span class="p">(</span><span class="s">&quot;kdtree.idx&quot;</span><span class="p">));</span>
    <span class="n">index</span><span class="p">.</span><span class="n">buildIndex</span> <span class="p">();</span>
</pre></div>
</div>
<p>Here we need to make sure that we use the <strong>exact</strong> distance metric
(<tt class="docutils literal"><span class="pre">ChiSquareDistance</span></tt> in this case), as the one that we used while creating
the tree. The most important part of the code comes here:</p>
<div class="highlight-cpp"><div class="highlight"><pre>    <span class="n">nearestKSearch</span> <span class="p">(</span><span class="n">index</span><span class="p">,</span> <span class="n">histogram</span><span class="p">,</span> <span class="n">k</span><span class="p">,</span> <span class="n">k_indices</span><span class="p">,</span> <span class="n">k_distances</span><span class="p">);</span>
</pre></div>
</div>
<p>Inside <tt class="docutils literal"><span class="pre">nearestKSearch</span></tt>, we first convert the query point to FLANN format:</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">flann</span><span class="o">::</span><span class="n">Matrix</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="n">p</span> <span class="o">=</span> <span class="n">flann</span><span class="o">::</span><span class="n">Matrix</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span><span class="p">(</span><span class="k">new</span> <span class="kt">float</span><span class="p">[</span><span class="n">model</span><span class="p">.</span><span class="n">second</span><span class="p">.</span><span class="n">size</span> <span class="p">()],</span> <span class="mi">1</span><span class="p">,</span> <span class="n">model</span><span class="p">.</span><span class="n">second</span><span class="p">.</span><span class="n">size</span> <span class="p">());</span>
  <span class="n">memcpy</span> <span class="p">(</span><span class="o">&amp;</span><span class="n">p</span><span class="p">.</span><span class="n">ptr</span> <span class="p">()[</span><span class="mi">0</span><span class="p">],</span> <span class="o">&amp;</span><span class="n">model</span><span class="p">.</span><span class="n">second</span><span class="p">[</span><span class="mi">0</span><span class="p">],</span> <span class="n">p</span><span class="p">.</span><span class="n">cols</span> <span class="o">*</span> <span class="n">p</span><span class="p">.</span><span class="n">rows</span> <span class="o">*</span> <span class="k">sizeof</span> <span class="p">(</span><span class="kt">float</span><span class="p">));</span>
</pre></div>
</div>
<p>Followed by obtaining the resultant nearest neighbor indices and distances for the query in:</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">indices</span> <span class="o">=</span> <span class="n">flann</span><span class="o">::</span><span class="n">Matrix</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span><span class="p">(</span><span class="k">new</span> <span class="kt">int</span><span class="p">[</span><span class="n">k</span><span class="p">],</span> <span class="mi">1</span><span class="p">,</span> <span class="n">k</span><span class="p">);</span>
  <span class="n">distances</span> <span class="o">=</span> <span class="n">flann</span><span class="o">::</span><span class="n">Matrix</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span><span class="p">(</span><span class="k">new</span> <span class="kt">float</span><span class="p">[</span><span class="n">k</span><span class="p">],</span> <span class="mi">1</span><span class="p">,</span> <span class="n">k</span><span class="p">);</span>
  <span class="n">index</span><span class="p">.</span><span class="n">knnSearch</span> <span class="p">(</span><span class="n">p</span><span class="p">,</span> <span class="n">indices</span><span class="p">,</span> <span class="n">distances</span><span class="p">,</span> <span class="n">k</span><span class="p">,</span> <span class="n">flann</span><span class="o">::</span><span class="n">SearchParams</span> <span class="p">(</span><span class="mi">512</span><span class="p">));</span>
</pre></div>
</div>
<p>Lines:</p>
<div class="highlight-cpp"><div class="highlight"><pre>    <span class="n">index</span><span class="p">.</span><span class="n">buildIndex</span> <span class="p">();</span>
    <span class="n">nearestKSearch</span> <span class="p">(</span><span class="n">index</span><span class="p">,</span> <span class="n">histogram</span><span class="p">,</span> <span class="n">k</span><span class="p">,</span> <span class="n">k_indices</span><span class="p">,</span> <span class="n">k_distances</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="c1">// Output the results on screen</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_highlight</span> <span class="p">(</span><span class="s">&quot;The closest %d neighbors for %s are:</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">k</span><span class="p">,</span> <span class="n">argv</span><span class="p">[</span><span class="n">pcd_indices</span><span class="p">[</span><span class="mi">0</span><span class="p">]]);</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">int</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">k</span><span class="p">;</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_info</span> <span class="p">(</span><span class="s">&quot;    %d - %s (%d) with a distance of: %f</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> 
        <span class="n">i</span><span class="p">,</span> <span class="n">models</span><span class="p">.</span><span class="n">at</span> <span class="p">(</span><span class="n">k_indices</span><span class="p">[</span><span class="mi">0</span><span class="p">][</span><span class="n">i</span><span class="p">]).</span><span class="n">first</span><span class="p">.</span><span class="n">c_str</span> <span class="p">(),</span> <span class="n">k_indices</span><span class="p">[</span><span class="mi">0</span><span class="p">][</span><span class="n">i</span><span class="p">],</span> <span class="n">k_distances</span><span class="p">[</span><span class="mi">0</span><span class="p">][</span><span class="n">i</span><span class="p">]);</span>

  <span class="c1">// Load the results</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="n">p</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;VFH Cluster Classifier&quot;</span><span class="p">);</span>
  <span class="kt">int</span> <span class="n">y_s</span> <span class="o">=</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span><span class="n">floor</span> <span class="p">(</span><span class="n">sqrt</span> <span class="p">((</span><span class="kt">double</span><span class="p">)</span><span class="n">k</span><span class="p">));</span>
  <span class="kt">int</span> <span class="n">x_s</span> <span class="o">=</span> <span class="n">y_s</span> <span class="o">+</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span><span class="n">ceil</span> <span class="p">((</span><span class="n">k</span> <span class="o">/</span> <span class="p">(</span><span class="kt">double</span><span class="p">)</span><span class="n">y_s</span><span class="p">)</span> <span class="o">-</span> <span class="n">y_s</span><span class="p">);</span>
  <span class="kt">double</span> <span class="n">x_step</span> <span class="o">=</span> <span class="p">(</span><span class="kt">double</span><span class="p">)(</span><span class="mi">1</span> <span class="o">/</span> <span class="p">(</span><span class="kt">double</span><span class="p">)</span><span class="n">x_s</span><span class="p">);</span>
</pre></div>
</div>
<p>create a <tt class="docutils literal"><span class="pre">PCLVisualizer</span></tt> object, and sets up a set of different viewports (e.g., splits the screen into different chunks), which will be enabled in:</p>
<div class="highlight-cpp"><div class="highlight"><pre>    <span class="n">p</span><span class="p">.</span><span class="n">createViewPort</span> <span class="p">(</span><span class="n">l</span> <span class="o">*</span> <span class="n">x_step</span><span class="p">,</span> <span class="n">m</span> <span class="o">*</span> <span class="n">y_step</span><span class="p">,</span> <span class="p">(</span><span class="n">l</span> <span class="o">+</span> <span class="mi">1</span><span class="p">)</span> <span class="o">*</span> <span class="n">x_step</span><span class="p">,</span> <span class="p">(</span><span class="n">m</span> <span class="o">+</span> <span class="mi">1</span><span class="p">)</span> <span class="o">*</span> <span class="n">y_step</span><span class="p">,</span> <span class="n">viewport</span><span class="p">);</span>
</pre></div>
</div>
<p>Using the file names representing the models that we previously obtained in
<tt class="docutils literal"><span class="pre">loadFileList</span></tt>, we proceed at loading the model file names using:</p>
<div class="highlight-cpp"><div class="highlight"><pre>    <span class="n">pcl</span><span class="o">::</span><span class="n">PCLPointCloud2</span> <span class="n">cloud</span><span class="p">;</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_highlight</span> <span class="p">(</span><span class="n">stderr</span><span class="p">,</span> <span class="s">&quot;Loading &quot;</span><span class="p">);</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_value</span> <span class="p">(</span><span class="n">stderr</span><span class="p">,</span> <span class="s">&quot;%s &quot;</span><span class="p">,</span> <span class="n">cloud_name</span><span class="p">.</span><span class="n">c_str</span> <span class="p">());</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="n">cloud_name</span><span class="p">,</span> <span class="n">cloud</span><span class="p">)</span> <span class="o">==</span> <span class="o">-</span><span class="mi">1</span><span class="p">)</span>
      <span class="k">break</span><span class="p">;</span>

    <span class="c1">// Convert from blob to PointCloud</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">cloud_xyz</span><span class="p">;</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">fromPCLPointCloud2</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">cloud_xyz</span><span class="p">);</span>
</pre></div>
</div>
<p>For visualization purposes, we demean the point cloud by computing its centroid and then subtracting it:</p>
<div class="highlight-cpp"><div class="highlight"><pre>    <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector4f</span> <span class="n">centroid</span><span class="p">;</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">compute3DCentroid</span> <span class="p">(</span><span class="n">cloud_xyz</span><span class="p">,</span> <span class="n">centroid</span><span class="p">);</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_xyz_demean</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">demeanPointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud_xyz</span><span class="p">,</span> <span class="n">centroid</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud_xyz_demean</span><span class="p">);</span>
    <span class="c1">// Add to renderer*</span>
    <span class="n">p</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">cloud_xyz_demean</span><span class="p">,</span> <span class="n">cloud_name</span><span class="p">,</span> <span class="n">viewport</span><span class="p">);</span>
</pre></div>
</div>
<p>Finally we check if the distance obtained by <tt class="docutils literal"><span class="pre">nearestKSearch</span></tt> is larger than the user given threshold, and if it is, we display a red line over the cloud that is being rendered in the viewport:</p>
<div class="highlight-cpp"><div class="highlight"><pre>      <span class="c1">// Create a red line</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">min_p</span><span class="p">,</span> <span class="n">max_p</span><span class="p">;</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">getMinMax3D</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_xyz_demean</span><span class="p">,</span> <span class="n">min_p</span><span class="p">,</span> <span class="n">max_p</span><span class="p">);</span>
      <span class="n">std</span><span class="o">::</span><span class="n">stringstream</span> <span class="n">line_name</span><span class="p">;</span>
      <span class="n">line_name</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;line_&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">i</span><span class="p">;</span>
      <span class="n">p</span><span class="p">.</span><span class="n">addLine</span> <span class="p">(</span><span class="n">min_p</span><span class="p">,</span> <span class="n">max_p</span><span class="p">,</span> <span class="mi">1</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="n">line_name</span><span class="p">.</span><span class="n">str</span> <span class="p">(),</span> <span class="n">viewport</span><span class="p">);</span>
      <span class="n">p</span><span class="p">.</span><span class="n">setShapeRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_LINE_WIDTH</span><span class="p">,</span> <span class="mi">5</span><span class="p">,</span> <span class="n">line_name</span><span class="p">.</span><span class="n">str</span> <span class="p">(),</span> <span class="n">viewport</span><span class="p">);</span>
</pre></div>
</div>
</div>
<div class="section" id="compiling-and-running-the-code">
<h1>Compiling and running the code</h1>
<p>Create a new <tt class="docutils literal"><span class="pre">CMakeLists.txt</span></tt> file, and put the following content into it</p>
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
29</pre></div></td><td class="code"><div class="highlight"><pre><span class="nb">cmake_minimum_required</span><span class="p">(</span><span class="s">VERSION</span> <span class="s">2.8</span> <span class="s">FATAL_ERROR</span><span class="p">)</span>

<span class="c"># we need FindFLANN.cmake </span>
<span class="nb">list</span><span class="p">(</span><span class="s">APPEND</span> <span class="s">CMAKE_MODULE_PATH</span> <span class="o">${</span><span class="nv">CMAKE_CURRENT_SOURCE_DIR</span><span class="o">}</span><span class="p">)</span>

<span class="nb">project</span><span class="p">(</span><span class="s">vfh_cluster_classifier</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.2</span> <span class="s">REQUIRED</span><span class="p">)</span>
<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">HDF5</span><span class="p">)</span>
<span class="nb">if</span><span class="p">(</span><span class="s">HDF5_FOUND</span><span class="p">)</span>

  <span class="nb">find_package</span><span class="p">(</span><span class="s">FLANN</span><span class="p">)</span>
  <span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">FLANN_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>

  <span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">HDF5_INCLUDE_DIR</span><span class="o">}</span><span class="p">)</span>

  <span class="nb">add_executable</span><span class="p">(</span><span class="s">build_tree</span> <span class="s">build_tree.cpp</span><span class="p">)</span>
  <span class="nb">target_link_libraries</span><span class="p">(</span><span class="s">build_tree</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span> <span class="o">${</span><span class="nv">Boost_LIBRARIES</span><span class="o">}</span>
                                 <span class="o">${</span><span class="nv">FLANN_LIBRARIES</span><span class="o">}</span> <span class="o">${</span><span class="nv">HDF5_hdf5_LIBRARY</span><span class="o">}</span><span class="p">)</span>

  <span class="nb">add_executable</span><span class="p">(</span><span class="s">nearest_neighbors</span> <span class="s">nearest_neighbors.cpp</span><span class="p">)</span>
  <span class="nb">target_link_libraries</span><span class="p">(</span><span class="s">nearest_neighbors</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span> 
                                        <span class="o">${</span><span class="nv">Boost_LIBRARIES</span><span class="o">}</span> <span class="o">${</span><span class="nv">FLANN_LIBRARIES</span><span class="o">}</span> <span class="o">${</span><span class="nv">HDF5_hdf5_LIBRARY</span><span class="o">}</span> 
                                        <span class="p">)</span>
<span class="nb">endif</span><span class="p">(</span><span class="s">HDF5_FOUND</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">If you are running this tutorial on Windows, you have to install (<a class="reference external" href="http://www.hdfgroup.org/ftp/HDF5/current/bin/windows/">HDF5 1.8.7 Shared Library</a>). If CMake is not able to find HDF5,
you can manually supply the include directory in HDF5_INCLUDE_DIR variable and the full path of <strong>hdf5dll.lib</strong> in HDF5_hdf5_LIBRARY variable.
Make sure that the needed dlls are in the same folder as the executables.</p>
</div>
<p>The above assumes that your two source files (<tt class="docutils literal"><span class="pre">build_tree.cpp</span></tt> and <tt class="docutils literal"><span class="pre">nearest_neighbors.cpp</span></tt>) are stored into the <em>src/</em> subdirectory.</p>
<p>Then, make sure that the datasets you downloaded (<a class="reference external" href="https://raw.github.com/PointCloudLibrary/data/master/tutorials/vfh_recognition/vfh_recognition_tutorial_data.tbz">vfh_recognition_tutorial_data.tbz</a>) are unpacked in this directory, thus creating a <em>data/</em> subdirectory.</p>
<p>After you have made the executable, you can run them like so:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./build/build_tree data/
</pre></div>
</div>
<p>You should see the following output on screen:</p>
<div class="highlight-python"><div class="highlight"><pre>&gt; Loading data/001.324.25 (0 models loaded so far).
&gt; Loading data/800.919.49 (13 models loaded so far).
&gt; Loading data/100.922.16 (27 models loaded so far).
&gt; Loading data/901.125.07 (47 models loaded so far).
&gt; Loading data/000.580.67 (65 models loaded so far).
&gt; Loading data/463.156.00 (81 models loaded so far).
&gt; Loading data/401.431.44 (97 models loaded so far).
&gt; Loading data/100.919.00 (113 models loaded so far).
&gt; Loading data/401.324.52 (134 models loaded so far).
&gt; Loading data/201.327.78 (150 models loaded so far).
&gt; Loading data/300.151.23 (166 models loaded so far).
&gt; Loading data/200.921.07 (180 models loaded so far).
&gt; Loaded 195 VFH models. Creating training data training_data.h5/training_data.list.
Building the kdtree index (kdtree.idx) for 195 elements...
</pre></div>
</div>
<p>The above crawled the <em>data/</em> subdirectory, and created a kd-tree with 195 entries. To run the nearest neighbor testing example, you have two options:</p>
<blockquote>
<div><ol class="arabic">
<li><p class="first">Either run the following command manually, and select one of the datasets that we provided as a testing sample, like this:</p>
<div class="highlight-python"><div class="highlight"><pre>./build/nearest_neighbors -k 16 -thresh 50 data/000.580.67/1258730231333_cluster_0_nxyz_vfh.pcd
</pre></div>
</div>
</li>
<li><p class="first">Or, if you are on a linux system, you can place the following on a bash script file (e.g., <tt class="docutils literal"><span class="pre">test.sh</span></tt>):</p>
<div class="highlight-python"><div class="highlight"><pre>#!/bin/bash

# Example directory containing _vfh.pcd files
DATA=data

# Inlier distance threshold
thresh=50

# Get the closest K nearest neighbors
k=16

for i in `find $DATA -type d -name &quot;*&quot;`
do
  echo $i
  for j in `find $i -type f \( -iname &quot;*cluster*_vfh.pcd&quot; \) | sort -R`
  do
    echo $j
    ./build/nearest_neighbors -k $k -thresh $thresh $j -cam &quot;0.403137,0.868471/0,0,0/-0.0932051,-0.201608,-0.518939/-0.00471487,-0.931831,0.362863/1464,764/6,72&quot;
  done
done
</pre></div>
</div>
</li>
</ol>
<blockquote>
<div><p>and run the script like this:</p>
<div class="highlight-python"><div class="highlight"><pre>bash test.sh
</pre></div>
</div>
</div></blockquote>
</div></blockquote>
<p>You should see <em>recognition</em> examples like the ones shown below:</p>
<img alt="_images/vfh_example1.jpg" src="_images/vfh_example1.jpg" />
<img alt="_images/vfh_example2.jpg" src="_images/vfh_example2.jpg" />
<img alt="_images/vfh_example3.jpg" src="_images/vfh_example3.jpg" />
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