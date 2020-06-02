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
    
    <title>Implicit Shape Model</title>
    
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
            
  <div class="section" id="implicit-shape-model">
<span id="id1"></span><h1>Implicit Shape Model</h1>
<p>In this tutorial we will learn how to use the implicit shape model algorithm implemented in the <tt class="docutils literal"><span class="pre">pcl::ism::ImplicitShapeModel</span></tt> class.
This algorithm was described in the article <a class="reference external" href="http://homes.esat.kuleuven.be/~jknopp/papers/2010eccv_3d_paper.pdf">&#8220;Hough Transforms and 3D SURF for robust three dimensional classification&#8221;</a> by Jan Knopp, Mukta Prasad, Geert Willems, Radu Timofte, and Luc Van Gool.
This algorithm is a combination of generalized Hough transform and the Bag of Features approach and its purpose is as follows. Having some training set - point clouds of different objects of the known class - the algorithm computes a certain model which will be later used to predict an object center in the given cloud that wasn&#8217;t a part of the training set.</p>
</div>
<div class="section" id="theoretical-primer">
<h1>Theoretical Primer</h1>
<dl class="docutils">
<dt>The algorithm consists of two steps, the first one is training, and the second is recognition of the objects in the clouds that weren&#8217;t in the training set. Let&#8217;s take a look at how the training is done. It consists of six steps:</dt>
<dd><ol class="first last arabic">
<li><p class="first">First of all the keypoint detection is made. In the given implementation it&#8217;s just a simplification of the training clouds. At this step all the point clouds are simplified by the means of the voxel grid approach; remaining points are declared as keypoints.</p>
</li>
<li><p class="first">For every keypoint features are estimated. In the example below the FPFH estimation is used.</p>
</li>
<li><p class="first">All features are clustered with the help of k-means algorithm to construct a dictionary of visual (or geometric) words. Obtained clusters represent visual words. Every feature in the cluster is the instance of this visual word.</p>
</li>
<li><p class="first">For every single instance the direction to center is computed - a direction from the keypoint (from which the feature was obtained) to the center of mass of the given cloud.</p>
</li>
<li><p class="first">For each visual word the statistical weight is calculated by the formula:</p>
<p class="centered">
<strong><span class="math">W_{st}(c_i,v_j)=\frac{1}{n_{vw}(c_i)} \frac{1}{n_{vot}(v_j)} \frac{\frac{n_{vot}(c_i,v_j)}{n_{ftr}(c_i)}}{\sum_{c_k\in C}\frac{n_{vot}(c_k,v_j)}{n_{ftr}(c_k)}}</span></strong></p><p>The statistical weight <span class="math">W_{st}(c_i,v_j)</span> weights all the votes cast by visual word <span class="math">v_j</span> for class <span class="math">c_i</span>. Here <span class="math">n_{vot}(v_j)</span> is the total number of votes from visual word <span class="math">v_j</span>, <span class="math">n_{vot}(c_i,v_j)</span> is the number of votes for class <span class="math">c_i</span> from <span class="math">v_j</span>, <span class="math">n_{vw}(c_i)</span> is the number of visual words that vote for class <span class="math">c_i</span>, <span class="math">n_{ftr}(c_i)</span> is the number of features from which <span class="math">c_i</span> was learned. <span class="math">C</span> is the set of all classes.</p>
</li>
<li><p class="first">For every keypoint (point for which feature was estimated) the learned weight is calculated by the formula:</p>
<p class="centered">
<strong><span class="math">W_{lrn}(\lambda_{ij})=f(\{e^{-\frac{{d_a(\lambda_{ij})}^2}{\sigma^2}} \mid a \in A\})</span></strong></p><p>Authors of the article define <span class="math">\lambda_{ij}</span> as the vote cast by a particular instance of visual word <span class="math">v_j</span> on a particular training shape of class <span class="math">c_i</span>; that is, <span class="math">\lambda_{ij}</span> records the distance of the particular instance of visual word <span class="math">v_j</span> to the center of the training shape on which it was found. Here <span class="math">A</span> is the set of all features associated with word <span class="math">v_j</span> on a shape of class <span class="math">c_i</span>. The recommend value for <span class="math">\sigma</span> is 10% of the shape size. Function <span class="math">f</span> is simply a median. <span class="math">d_a(\lambda_{ij})</span> is the Euclidean distance between voted and actual center.</p>
</li>
</ol>
</dd>
<dt>After the training process is done and the trained model (weights, directions etc.) is obtained, the process of object search (or recognition) takes place. It consists of next four steps:</dt>
<dd><ol class="first last arabic">
<li><p class="first">Keypoint detection.</p>
</li>
<li><p class="first">Feature estimation for every keypoint of the cloud.</p>
</li>
<li><p class="first">For each feature the search for the nearest visual word (that is a cluster) in the dictionary is made.</p>
</li>
<li><p class="first">For every feature</p>
<ul>
<li><p class="first">For every instance(which casts a vote for the class of interest) of every visual word from the trained model</p>
<ul>
<li><p class="first">Add vote with the corresponding direction and vote power computed by the formula</p>
<p class="centered">
<strong><span class="math">W(\lambda_{ij})=W_{st}(v_j,c_i) * W_{lrn}(\lambda_{ij})</span></strong></p></li>
</ul>
</li>
</ul>
</li>
<li><p class="first">Previous step gives us a set of directions to the expected center and the power for each vote. In order to get single point that corresponds to center these votes need to be analysed. For this purpose algorithm uses the non maxima suppression approach. User just needs to pass the radius of the object of interest and the rest will be done by the <tt class="docutils literal"><span class="pre">ISMVoteList::findStrongestPeaks</span> <span class="pre">()</span></tt> method.</p>
</li>
</ol>
</dd>
</dl>
<p>For more comprehensive information please refer to the article
<a class="reference external" href="http://homes.esat.kuleuven.be/~jknopp/papers/2010eccv_3d_paper.pdf">&#8220;Hough Transforms and 3D SURF for robust three dimensional classification&#8221;</a>.</p>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>First of all you will need the set of point clouds for this tutorial - training set and set of clouds for recognition.
Below is the list of clouds that are well suited for this tutorial (they were borrowed from the Ohio dataset).</p>
<dl class="docutils">
<dt>Clouds for training:</dt>
<dd><ul class="first last simple">
<li><a class="reference external" href="https://raw.github.com/PointCloudLibrary/data/master/tutorials/ism_train_cat.pcd">Cat (train)</a></li>
<li><a class="reference external" href="https://raw.github.com/PointCloudLibrary/data/master/tutorials/ism_train_horse.pcd">Horse (train)</a></li>
<li><a class="reference external" href="https://raw.github.com/PointCloudLibrary/data/master/tutorials/ism_train_lioness.pcd">Lioness (train)</a></li>
<li><a class="reference external" href="https://raw.github.com/PointCloudLibrary/data/master/tutorials/ism_train_michael.pcd">Michael (train)</a></li>
<li><a class="reference external" href="https://raw.github.com/PointCloudLibrary/data/master/tutorials/ism_train_wolf.pcd">Wolf (train)</a></li>
</ul>
</dd>
<dt>Clouds for testing:</dt>
<dd><ul class="first last simple">
<li><a class="reference external" href="https://raw.github.com/PointCloudLibrary/data/master/tutorials/ism_test_cat.pcd">Cat</a></li>
<li><a class="reference external" href="https://raw.github.com/PointCloudLibrary/data/master/tutorials/ism_test_horse.pcd">Horse</a></li>
<li><a class="reference external" href="https://raw.github.com/PointCloudLibrary/data/master/tutorials/ism_test_lioness.pcd">Lioness</a></li>
<li><a class="reference external" href="https://raw.github.com/PointCloudLibrary/data/master/tutorials/ism_test_michael.pcd">Michael</a></li>
<li><a class="reference external" href="https://raw.github.com/PointCloudLibrary/data/master/tutorials/ism_test_wolf.pcd">Wolf</a></li>
</ul>
</dd>
</dl>
<p>Next what you need to do is to create a file <tt class="docutils literal"><span class="pre">implicit_shape_model.cpp</span></tt> in any editor you prefer and copy the following code inside of it:</p>
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
121</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;iostream&gt;</span>
<span class="cp">#include &lt;pcl/io/pcd_io.h&gt;</span>
<span class="cp">#include &lt;pcl/features/normal_3d.h&gt;</span>
<span class="cp">#include &lt;pcl/features/feature.h&gt;</span>
<span class="cp">#include &lt;pcl/visualization/cloud_viewer.h&gt;</span>
<span class="cp">#include &lt;pcl/features/fpfh.h&gt;</span>
<span class="cp">#include &lt;pcl/features/impl/fpfh.hpp&gt;</span>
<span class="cp">#include &lt;pcl/recognition/implicit_shape_model.h&gt;</span>
<span class="cp">#include &lt;pcl/recognition/impl/implicit_shape_model.hpp&gt;</span>

<span class="kt">int</span>
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">argc</span> <span class="o">==</span> <span class="mi">0</span> <span class="o">||</span> <span class="n">argc</span> <span class="o">%</span> <span class="mi">2</span> <span class="o">==</span> <span class="mi">0</span><span class="p">)</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>

  <span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">number_of_training_clouds</span> <span class="o">=</span> <span class="p">(</span><span class="n">argc</span> <span class="o">-</span> <span class="mi">3</span><span class="p">)</span> <span class="o">/</span> <span class="mi">2</span><span class="p">;</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">NormalEstimation</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span> <span class="n">normal_estimator</span><span class="p">;</span>
  <span class="n">normal_estimator</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="mf">25.0</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span><span class="o">&gt;</span> <span class="n">training_clouds</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;::</span><span class="n">Ptr</span><span class="o">&gt;</span> <span class="n">training_normals</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">unsigned</span> <span class="kt">int</span><span class="o">&gt;</span> <span class="n">training_classes</span><span class="p">;</span>

  <span class="k">for</span> <span class="p">(</span><span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">i_cloud</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i_cloud</span> <span class="o">&lt;</span> <span class="n">number_of_training_clouds</span> <span class="o">-</span> <span class="mi">1</span><span class="p">;</span> <span class="n">i_cloud</span><span class="o">++</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">tr_cloud</span><span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">());</span>
    <span class="k">if</span> <span class="p">(</span> <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="n">i_cloud</span> <span class="o">*</span> <span class="mi">2</span> <span class="o">+</span> <span class="mi">1</span><span class="p">],</span> <span class="o">*</span><span class="n">tr_cloud</span><span class="p">)</span> <span class="o">==</span> <span class="o">-</span><span class="mi">1</span> <span class="p">)</span>
      <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>

    <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">tr_normals</span> <span class="o">=</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span><span class="p">)</span><span class="o">-&gt;</span><span class="n">makeShared</span> <span class="p">();</span>
    <span class="n">normal_estimator</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">tr_cloud</span><span class="p">);</span>
    <span class="n">normal_estimator</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">tr_normals</span><span class="p">);</span>

    <span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">tr_class</span> <span class="o">=</span> <span class="k">static_cast</span><span class="o">&lt;</span><span class="kt">unsigned</span> <span class="kt">int</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">strtol</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="n">i_cloud</span> <span class="o">*</span> <span class="mi">2</span> <span class="o">+</span> <span class="mi">2</span><span class="p">],</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">10</span><span class="p">));</span>

    <span class="n">training_clouds</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">tr_cloud</span><span class="p">);</span>
    <span class="n">training_normals</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">tr_normals</span><span class="p">);</span>
    <span class="n">training_classes</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">tr_class</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">FPFHEstimation</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Histogram</span><span class="o">&lt;</span><span class="mi">153</span><span class="o">&gt;</span> <span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">fpfh</span>
    <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">FPFHEstimation</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Histogram</span><span class="o">&lt;</span><span class="mi">153</span><span class="o">&gt;</span> <span class="o">&gt;</span><span class="p">);</span>
  <span class="n">fpfh</span><span class="o">-&gt;</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="mf">30.0</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">Feature</span><span class="o">&lt;</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Histogram</span><span class="o">&lt;</span><span class="mi">153</span><span class="o">&gt;</span> <span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">feature_estimator</span><span class="p">(</span><span class="n">fpfh</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">ism</span><span class="o">::</span><span class="n">ImplicitShapeModelEstimation</span><span class="o">&lt;</span><span class="mi">153</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span> <span class="n">ism</span><span class="p">;</span>
  <span class="n">ism</span><span class="p">.</span><span class="n">setFeatureEstimator</span><span class="p">(</span><span class="n">feature_estimator</span><span class="p">);</span>
  <span class="n">ism</span><span class="p">.</span><span class="n">setTrainingClouds</span> <span class="p">(</span><span class="n">training_clouds</span><span class="p">);</span>
  <span class="n">ism</span><span class="p">.</span><span class="n">setTrainingNormals</span> <span class="p">(</span><span class="n">training_normals</span><span class="p">);</span>
  <span class="n">ism</span><span class="p">.</span><span class="n">setTrainingClasses</span> <span class="p">(</span><span class="n">training_classes</span><span class="p">);</span>
  <span class="n">ism</span><span class="p">.</span><span class="n">setSamplingSize</span> <span class="p">(</span><span class="mf">2.0f</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">ism</span><span class="o">::</span><span class="n">ImplicitShapeModelEstimation</span><span class="o">&lt;</span><span class="mi">153</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;::</span><span class="n">ISMModelPtr</span> <span class="n">model</span> <span class="o">=</span> <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">features</span><span class="o">::</span><span class="n">ISMModel</span><span class="o">&gt;</span>
    <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">features</span><span class="o">::</span><span class="n">ISMModel</span><span class="p">);</span>
  <span class="n">ism</span><span class="p">.</span><span class="n">trainISM</span> <span class="p">(</span><span class="n">model</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">file</span> <span class="p">(</span><span class="s">&quot;trained_ism_model.txt&quot;</span><span class="p">);</span>
  <span class="n">model</span><span class="o">-&gt;</span><span class="n">saveModelToFile</span> <span class="p">(</span><span class="n">file</span><span class="p">);</span>

  <span class="n">model</span><span class="o">-&gt;</span><span class="n">loadModelFromfile</span> <span class="p">(</span><span class="n">file</span><span class="p">);</span>

  <span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">testing_class</span> <span class="o">=</span> <span class="k">static_cast</span><span class="o">&lt;</span><span class="kt">unsigned</span> <span class="kt">int</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">strtol</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="n">argc</span> <span class="o">-</span> <span class="mi">1</span><span class="p">],</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">10</span><span class="p">));</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">testing_cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="k">if</span> <span class="p">(</span> <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="n">argc</span> <span class="o">-</span> <span class="mi">2</span><span class="p">],</span> <span class="o">*</span><span class="n">testing_cloud</span><span class="p">)</span> <span class="o">==</span> <span class="o">-</span><span class="mi">1</span> <span class="p">)</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">testing_normals</span> <span class="o">=</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span><span class="p">)</span><span class="o">-&gt;</span><span class="n">makeShared</span> <span class="p">();</span>
  <span class="n">normal_estimator</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">testing_cloud</span><span class="p">);</span>
  <span class="n">normal_estimator</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">testing_normals</span><span class="p">);</span>

  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">features</span><span class="o">::</span><span class="n">ISMVoteList</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="n">vote_list</span> <span class="o">=</span> <span class="n">ism</span><span class="p">.</span><span class="n">findObjects</span> <span class="p">(</span>
    <span class="n">model</span><span class="p">,</span>
    <span class="n">testing_cloud</span><span class="p">,</span>
    <span class="n">testing_normals</span><span class="p">,</span>
    <span class="n">testing_class</span><span class="p">);</span>

  <span class="kt">double</span> <span class="n">radius</span> <span class="o">=</span> <span class="n">model</span><span class="o">-&gt;</span><span class="n">sigmas_</span><span class="p">[</span><span class="n">testing_class</span><span class="p">]</span> <span class="o">*</span> <span class="mf">10.0</span><span class="p">;</span>
  <span class="kt">double</span> <span class="n">sigma</span> <span class="o">=</span> <span class="n">model</span><span class="o">-&gt;</span><span class="n">sigmas_</span><span class="p">[</span><span class="n">testing_class</span><span class="p">];</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">ISMPeak</span><span class="p">,</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">aligned_allocator</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">ISMPeak</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="n">strongest_peaks</span><span class="p">;</span>
  <span class="n">vote_list</span><span class="o">-&gt;</span><span class="n">findStrongestPeaks</span> <span class="p">(</span><span class="n">strongest_peaks</span><span class="p">,</span> <span class="n">testing_class</span><span class="p">,</span> <span class="n">radius</span><span class="p">,</span> <span class="n">sigma</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">colored_cloud</span> <span class="o">=</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span><span class="p">)</span><span class="o">-&gt;</span><span class="n">makeShared</span> <span class="p">();</span>
  <span class="n">colored_cloud</span><span class="o">-&gt;</span><span class="n">height</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="n">colored_cloud</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span> <span class="n">point</span><span class="p">;</span>
  <span class="n">point</span><span class="p">.</span><span class="n">r</span> <span class="o">=</span> <span class="mi">255</span><span class="p">;</span>
  <span class="n">point</span><span class="p">.</span><span class="n">g</span> <span class="o">=</span> <span class="mi">255</span><span class="p">;</span>
  <span class="n">point</span><span class="p">.</span><span class="n">b</span> <span class="o">=</span> <span class="mi">255</span><span class="p">;</span>

  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i_point</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i_point</span> <span class="o">&lt;</span> <span class="n">testing_cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="n">i_point</span><span class="o">++</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">point</span><span class="p">.</span><span class="n">x</span> <span class="o">=</span> <span class="n">testing_cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i_point</span><span class="p">].</span><span class="n">x</span><span class="p">;</span>
    <span class="n">point</span><span class="p">.</span><span class="n">y</span> <span class="o">=</span> <span class="n">testing_cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i_point</span><span class="p">].</span><span class="n">y</span><span class="p">;</span>
    <span class="n">point</span><span class="p">.</span><span class="n">z</span> <span class="o">=</span> <span class="n">testing_cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i_point</span><span class="p">].</span><span class="n">z</span><span class="p">;</span>
    <span class="n">colored_cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">point</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="n">colored_cloud</span><span class="o">-&gt;</span><span class="n">height</span> <span class="o">+=</span> <span class="n">testing_cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span>

  <span class="n">point</span><span class="p">.</span><span class="n">r</span> <span class="o">=</span> <span class="mi">255</span><span class="p">;</span>
  <span class="n">point</span><span class="p">.</span><span class="n">g</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="n">point</span><span class="p">.</span><span class="n">b</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i_vote</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i_vote</span> <span class="o">&lt;</span> <span class="n">strongest_peaks</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="n">i_vote</span><span class="o">++</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">point</span><span class="p">.</span><span class="n">x</span> <span class="o">=</span> <span class="n">strongest_peaks</span><span class="p">[</span><span class="n">i_vote</span><span class="p">].</span><span class="n">x</span><span class="p">;</span>
    <span class="n">point</span><span class="p">.</span><span class="n">y</span> <span class="o">=</span> <span class="n">strongest_peaks</span><span class="p">[</span><span class="n">i_vote</span><span class="p">].</span><span class="n">y</span><span class="p">;</span>
    <span class="n">point</span><span class="p">.</span><span class="n">z</span> <span class="o">=</span> <span class="n">strongest_peaks</span><span class="p">[</span><span class="n">i_vote</span><span class="p">].</span><span class="n">z</span><span class="p">;</span>
    <span class="n">colored_cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">point</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="n">colored_cloud</span><span class="o">-&gt;</span><span class="n">height</span> <span class="o">+=</span> <span class="n">strongest_peaks</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">CloudViewer</span> <span class="n">viewer</span> <span class="p">(</span><span class="s">&quot;Result viewer&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">showCloud</span> <span class="p">(</span><span class="n">colored_cloud</span><span class="p">);</span>
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
<p>Now let&#8217;s study out what is the purpose of this code. The first lines of interest are these:</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="k">for</span> <span class="p">(</span><span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">i_cloud</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i_cloud</span> <span class="o">&lt;</span> <span class="n">number_of_training_clouds</span> <span class="o">-</span> <span class="mi">1</span><span class="p">;</span> <span class="n">i_cloud</span><span class="o">++</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">tr_cloud</span><span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">());</span>
    <span class="k">if</span> <span class="p">(</span> <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="n">i_cloud</span> <span class="o">*</span> <span class="mi">2</span> <span class="o">+</span> <span class="mi">1</span><span class="p">],</span> <span class="o">*</span><span class="n">tr_cloud</span><span class="p">)</span> <span class="o">==</span> <span class="o">-</span><span class="mi">1</span> <span class="p">)</span>
      <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>

    <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">tr_normals</span> <span class="o">=</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span><span class="p">)</span><span class="o">-&gt;</span><span class="n">makeShared</span> <span class="p">();</span>
    <span class="n">normal_estimator</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">tr_cloud</span><span class="p">);</span>
    <span class="n">normal_estimator</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">tr_normals</span><span class="p">);</span>

    <span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">tr_class</span> <span class="o">=</span> <span class="k">static_cast</span><span class="o">&lt;</span><span class="kt">unsigned</span> <span class="kt">int</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">strtol</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="n">i_cloud</span> <span class="o">*</span> <span class="mi">2</span> <span class="o">+</span> <span class="mi">2</span><span class="p">],</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">10</span><span class="p">));</span>

    <span class="n">training_clouds</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">tr_cloud</span><span class="p">);</span>
    <span class="n">training_normals</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">tr_normals</span><span class="p">);</span>
    <span class="n">training_classes</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">tr_class</span><span class="p">);</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>These lines simply load the clouds that will be used for training. Algorithm requires normals so this is the place where they are computed.
After the loop is passed all clouds will be inserted  to the training_clouds vector. training_normals and training_classes will store normals and class index for the corresponding object.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">FPFHEstimation</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Histogram</span><span class="o">&lt;</span><span class="mi">153</span><span class="o">&gt;</span> <span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">fpfh</span>
    <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">FPFHEstimation</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Histogram</span><span class="o">&lt;</span><span class="mi">153</span><span class="o">&gt;</span> <span class="o">&gt;</span><span class="p">);</span>
  <span class="n">fpfh</span><span class="o">-&gt;</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="mf">30.0</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">Feature</span><span class="o">&lt;</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Histogram</span><span class="o">&lt;</span><span class="mi">153</span><span class="o">&gt;</span> <span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">feature_estimator</span><span class="p">(</span><span class="n">fpfh</span><span class="p">);</span>
</pre></div>
</div>
<p>Here the instance of feature estimator is created, in our case it is the FPFH. It must be fully set up before it will be passed to the ISM algorithm. So this is the place where we define all feature estimation settings.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">ism</span><span class="o">::</span><span class="n">ImplicitShapeModelEstimation</span><span class="o">&lt;</span><span class="mi">153</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span> <span class="n">ism</span><span class="p">;</span>
</pre></div>
</div>
<p>This line simply creates an instance of the <tt class="docutils literal"><span class="pre">pcl::ism::ImplicitShapeModelEstimation</span></tt>. It is a template class that has three parameters:</p>
<blockquote>
<div><ul class="simple">
<li>FeatureSize - size of the features (histograms) to compute</li>
<li>PointT - type of points to work with</li>
<li>NormalT - type of normals to use</li>
</ul>
</div></blockquote>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">ism</span><span class="p">.</span><span class="n">setFeatureEstimator</span><span class="p">(</span><span class="n">feature_estimator</span><span class="p">);</span>
  <span class="n">ism</span><span class="p">.</span><span class="n">setTrainingClouds</span> <span class="p">(</span><span class="n">training_clouds</span><span class="p">);</span>
  <span class="n">ism</span><span class="p">.</span><span class="n">setTrainingNormals</span> <span class="p">(</span><span class="n">training_normals</span><span class="p">);</span>
  <span class="n">ism</span><span class="p">.</span><span class="n">setTrainingClasses</span> <span class="p">(</span><span class="n">training_classes</span><span class="p">);</span>
  <span class="n">ism</span><span class="p">.</span><span class="n">setSamplingSize</span> <span class="p">(</span><span class="mf">2.0f</span><span class="p">);</span>
</pre></div>
</div>
<p>Here the instance is provided with the training data and feature estimator. The last line provides sampling size value used for cloud simplification as mentioned before.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">ism</span><span class="o">::</span><span class="n">ImplicitShapeModelEstimation</span><span class="o">&lt;</span><span class="mi">153</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;::</span><span class="n">ISMModelPtr</span> <span class="n">model</span> <span class="o">=</span> <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">features</span><span class="o">::</span><span class="n">ISMModel</span><span class="o">&gt;</span>
    <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">features</span><span class="o">::</span><span class="n">ISMModel</span><span class="p">);</span>
  <span class="n">ism</span><span class="p">.</span><span class="n">trainISM</span> <span class="p">(</span><span class="n">model</span><span class="p">);</span>
</pre></div>
</div>
<p>These lines simply launch the training process.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">file</span> <span class="p">(</span><span class="s">&quot;trained_ism_model.txt&quot;</span><span class="p">);</span>
  <span class="n">model</span><span class="o">-&gt;</span><span class="n">saveModelToFile</span> <span class="p">(</span><span class="n">file</span><span class="p">);</span>
</pre></div>
</div>
<p>Here the trained model that was obtained during the training process is saved to file for possible reuse.</p>
<p>The remaining part of the code may be moved with a few changes to another .cpp file and be presented as a separate program that is responsible for classification.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">model</span><span class="o">-&gt;</span><span class="n">loadModelFromfile</span> <span class="p">(</span><span class="n">file</span><span class="p">);</span>
</pre></div>
</div>
<p>This line loads trained model from file. It is not necessary, because we already have the trained model. It is given to show how you can load the precomputed model.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">testing_class</span> <span class="o">=</span> <span class="k">static_cast</span><span class="o">&lt;</span><span class="kt">unsigned</span> <span class="kt">int</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">strtol</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="n">argc</span> <span class="o">-</span> <span class="mi">1</span><span class="p">],</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">10</span><span class="p">));</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">testing_cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="k">if</span> <span class="p">(</span> <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="n">argc</span> <span class="o">-</span> <span class="mi">2</span><span class="p">],</span> <span class="o">*</span><span class="n">testing_cloud</span><span class="p">)</span> <span class="o">==</span> <span class="o">-</span><span class="mi">1</span> <span class="p">)</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">testing_normals</span> <span class="o">=</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span><span class="p">)</span><span class="o">-&gt;</span><span class="n">makeShared</span> <span class="p">();</span>
  <span class="n">normal_estimator</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">testing_cloud</span><span class="p">);</span>
  <span class="n">normal_estimator</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">testing_normals</span><span class="p">);</span>
</pre></div>
</div>
<p>The classification process needs the cloud and its normals as well as the training process. So these lines simply load the cloud and compute normals.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">features</span><span class="o">::</span><span class="n">ISMVoteList</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="n">vote_list</span> <span class="o">=</span> <span class="n">ism</span><span class="p">.</span><span class="n">findObjects</span> <span class="p">(</span>
    <span class="n">model</span><span class="p">,</span>
    <span class="n">testing_cloud</span><span class="p">,</span>
    <span class="n">testing_normals</span><span class="p">,</span>
    <span class="n">testing_class</span><span class="p">);</span>
</pre></div>
</div>
<p>This line launches the classification process. It tells the algorithm to look for the objects of type <tt class="docutils literal"><span class="pre">testing_class</span></tt> in the given cloud <tt class="docutils literal"><span class="pre">testing_cloud</span></tt>. Notice that the algorithm will use any trained model that you will pass. After the classification is done, the list of votes for center will be returned. <tt class="docutils literal"><span class="pre">pcl::ism::ISMVoteList</span></tt> is the separate class, which purpose is to help you to analyze the votes.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="kt">double</span> <span class="n">radius</span> <span class="o">=</span> <span class="n">model</span><span class="o">-&gt;</span><span class="n">sigmas_</span><span class="p">[</span><span class="n">testing_class</span><span class="p">]</span> <span class="o">*</span> <span class="mf">10.0</span><span class="p">;</span>
  <span class="kt">double</span> <span class="n">sigma</span> <span class="o">=</span> <span class="n">model</span><span class="o">-&gt;</span><span class="n">sigmas_</span><span class="p">[</span><span class="n">testing_class</span><span class="p">];</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">ISMPeak</span><span class="p">,</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">aligned_allocator</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">ISMPeak</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="n">strongest_peaks</span><span class="p">;</span>
  <span class="n">vote_list</span><span class="o">-&gt;</span><span class="n">findStrongestPeaks</span> <span class="p">(</span><span class="n">strongest_peaks</span><span class="p">,</span> <span class="n">testing_class</span><span class="p">,</span> <span class="n">radius</span><span class="p">,</span> <span class="n">sigma</span><span class="p">);</span>
</pre></div>
</div>
<p>These lines are responsible for finding strongest peaks among the votes. This search is based on the non-maximum suppression idea, that&#8217;s why the non-maximum radius is equal to the object radius that is taken from the trained model.</p>
<p>The rest of the code is simple enough. It is responsible for visualizing the cloud and computed strongest peaks which represent the estimated centers of the object of type <tt class="docutils literal"><span class="pre">testing_class</span></tt>.</p>
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
12
13</pre></div></td><td class="code"><div class="highlight"><pre><span class="nb">cmake_minimum_required</span><span class="p">(</span><span class="s">VERSION</span> <span class="s">2.8</span> <span class="s">FATAL_ERROR</span><span class="p">)</span>

<span class="nb">project</span><span class="p">(</span><span class="s">implicit_shape_model</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.5</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">set</span><span class="p">(</span><span class="s">CMAKE_BUILD_TYPE</span> <span class="s">Release</span><span class="p">)</span>
<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">implicit_shape_model</span> <span class="s">implicit_shape_model.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">implicit_shape_model</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>Note that here we tell the compiler that we want a release version of the binaries, because the process of training is too slow.
After you have made the executable, you can run it. Simply do:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./implicit_shape_model
      ism_train_cat.pcd      0
      ism_train_horse.pcd    1
      ism_train_lioness.pcd  2
      ism_train_michael.pcd  3
      ism_train_wolf.pcd     4
      ism_test_cat.pcd       0
</pre></div>
</div>
<p>Here you must pass the training clouds and the class of the object that it contains. The last two parameters are the cloud for testing and the class of interest that you are looking for in the testing cloud.</p>
<p>After the segmentation the cloud viewer window will be opened and you will see something similar to those images:</p>
<a class="reference internal image-reference" href="_images/ism_tutorial_1.png"><img alt="_images/ism_tutorial_1.png" src="_images/ism_tutorial_1.png" style="height: 180px;" /></a>
<a class="reference internal image-reference" href="_images/ism_tutorial_2.png"><img alt="_images/ism_tutorial_2.png" src="_images/ism_tutorial_2.png" style="height: 360px;" /></a>
<a class="reference internal image-reference" href="_images/ism_tutorial_3.png"><img alt="_images/ism_tutorial_3.png" src="_images/ism_tutorial_3.png" style="height: 400px;" /></a>
<p>Here the red point represents the predicted center of the object that corresponds to the class of interest.
If you will try to visualize the votes you will see something similar to this image where blue points are votes:</p>
<a class="reference internal image-reference" href="_images/implicit_shape_model.png"><img alt="_images/implicit_shape_model.png" src="_images/implicit_shape_model.png" style="height: 360px;" /></a>
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