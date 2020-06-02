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
    
    <title>Moment of inertia and eccentricity based descriptors</title>
    
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
            
  <div class="section" id="moment-of-inertia-and-eccentricity-based-descriptors">
<span id="moment-of-inertia"></span><h1>Moment of inertia and eccentricity based descriptors</h1>
<p>In this tutorial we will learn how to use the <cite>pcl::MomentOfInertiaEstimation</cite> class in order to obtain descriptors based on
eccentricity and moment of inertia. This class also allows to extract axis aligned and oriented bounding boxes of the cloud.
But keep in mind that extracted OBB is not the minimal possible bounding box.</p>
</div>
<div class="section" id="theoretical-primer">
<h1>Theoretical Primer</h1>
<p>The idea of the feature extraction method is as follows.
First of all the covariance matrix of the point cloud is calculated and its eigen values and vectors are extracted.
You can consider that the resultant eigen vectors are normalized and always form the right-handed coordinate system
(major eigen vector represents X-axis and the minor vector represents Z-axis). On the next step the iteration process takes place.
On each iteration major eigen vector is rotated. Rotation order is always the same and is performed around the other
eigen vectors, this provides the invariance to rotation of the point cloud. Henceforth, we will refer to this rotated major vector as current axis.</p>
<a class="reference internal image-reference" href="_images/eigen_vectors.png"><img alt="_images/eigen_vectors.png" src="_images/eigen_vectors.png" style="height: 360px;" /></a>
<p>For every current axis moment of inertia is calculated. Moreover, current axis is also used for eccentricity calculation.
For this reason current vector is treated as normal vector of the plane and the input cloud is projected onto it.
After that eccentricity is calculated for the obtained projection.</p>
<a class="reference internal image-reference" href="_images/projected_cloud.png"><img alt="_images/projected_cloud.png" src="_images/projected_cloud.png" style="height: 360px;" /></a>
<p>Implemented class also provides methods for getting AABB and OBB. Oriented bounding box is computed as AABB along eigen vectors.</p>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>First of all you will need the point cloud for this tutorial.
<a class="reference external" href="https://github.com/PointCloudLibrary/data/blob/master/tutorials/min_cut_segmentation_tutorial.pcd">This</a> is the one presented on the screenshots.
Next what you need to do is to create a file <tt class="docutils literal"><span class="pre">moment_of_inertia.cpp</span></tt> in any editor you prefer and copy the following code inside of it:</p>
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
107</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;pcl/features/moment_of_inertia_estimation.h&gt;</span>
<span class="cp">#include &lt;vector&gt;</span>
<span class="cp">#include &lt;pcl/io/pcd_io.h&gt;</span>
<span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/visualization/cloud_viewer.h&gt;</span>
<span class="cp">#include &lt;boost/thread/thread.hpp&gt;</span>

<span class="kt">int</span> <span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">argc</span> <span class="o">!=</span> <span class="mi">2</span><span class="p">)</span>
    <span class="k">return</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">1</span><span class="p">],</span> <span class="o">*</span><span class="n">cloud</span><span class="p">)</span> <span class="o">==</span> <span class="o">-</span><span class="mi">1</span><span class="p">)</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">MomentOfInertiaEstimation</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">feature_extractor</span><span class="p">;</span>
  <span class="n">feature_extractor</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">feature_extractor</span><span class="p">.</span><span class="n">compute</span> <span class="p">();</span>

  <span class="n">std</span><span class="o">::</span><span class="n">vector</span> <span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="n">moment_of_inertia</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span> <span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="n">eccentricity</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">min_point_AABB</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">max_point_AABB</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">min_point_OBB</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">max_point_OBB</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">position_OBB</span><span class="p">;</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix3f</span> <span class="n">rotational_matrix_OBB</span><span class="p">;</span>
  <span class="kt">float</span> <span class="n">major_value</span><span class="p">,</span> <span class="n">middle_value</span><span class="p">,</span> <span class="n">minor_value</span><span class="p">;</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span> <span class="n">major_vector</span><span class="p">,</span> <span class="n">middle_vector</span><span class="p">,</span> <span class="n">minor_vector</span><span class="p">;</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span> <span class="n">mass_center</span><span class="p">;</span>

  <span class="n">feature_extractor</span><span class="p">.</span><span class="n">getMomentOfInertia</span> <span class="p">(</span><span class="n">moment_of_inertia</span><span class="p">);</span>
  <span class="n">feature_extractor</span><span class="p">.</span><span class="n">getEccentricity</span> <span class="p">(</span><span class="n">eccentricity</span><span class="p">);</span>
  <span class="n">feature_extractor</span><span class="p">.</span><span class="n">getAABB</span> <span class="p">(</span><span class="n">min_point_AABB</span><span class="p">,</span> <span class="n">max_point_AABB</span><span class="p">);</span>
  <span class="n">feature_extractor</span><span class="p">.</span><span class="n">getOBB</span> <span class="p">(</span><span class="n">min_point_OBB</span><span class="p">,</span> <span class="n">max_point_OBB</span><span class="p">,</span> <span class="n">position_OBB</span><span class="p">,</span> <span class="n">rotational_matrix_OBB</span><span class="p">);</span>
  <span class="n">feature_extractor</span><span class="p">.</span><span class="n">getEigenValues</span> <span class="p">(</span><span class="n">major_value</span><span class="p">,</span> <span class="n">middle_value</span><span class="p">,</span> <span class="n">minor_value</span><span class="p">);</span>
  <span class="n">feature_extractor</span><span class="p">.</span><span class="n">getEigenVectors</span> <span class="p">(</span><span class="n">major_vector</span><span class="p">,</span> <span class="n">middle_vector</span><span class="p">,</span> <span class="n">minor_vector</span><span class="p">);</span>
  <span class="n">feature_extractor</span><span class="p">.</span><span class="n">getMassCenter</span> <span class="p">(</span><span class="n">mass_center</span><span class="p">);</span>

  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">viewer</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="p">(</span><span class="s">&quot;3D Viewer&quot;</span><span class="p">));</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setBackgroundColor</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addCoordinateSystem</span> <span class="p">(</span><span class="mf">1.0</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">initCameraParameters</span> <span class="p">();</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addPointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="s">&quot;sample cloud&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addCube</span> <span class="p">(</span><span class="n">min_point_AABB</span><span class="p">.</span><span class="n">x</span><span class="p">,</span> <span class="n">max_point_AABB</span><span class="p">.</span><span class="n">x</span><span class="p">,</span> <span class="n">min_point_AABB</span><span class="p">.</span><span class="n">y</span><span class="p">,</span> <span class="n">max_point_AABB</span><span class="p">.</span><span class="n">y</span><span class="p">,</span> <span class="n">min_point_AABB</span><span class="p">.</span><span class="n">z</span><span class="p">,</span> <span class="n">max_point_AABB</span><span class="p">.</span><span class="n">z</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="s">&quot;AABB&quot;</span><span class="p">);</span>

  <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span> <span class="n">position</span> <span class="p">(</span><span class="n">position_OBB</span><span class="p">.</span><span class="n">x</span><span class="p">,</span> <span class="n">position_OBB</span><span class="p">.</span><span class="n">y</span><span class="p">,</span> <span class="n">position_OBB</span><span class="p">.</span><span class="n">z</span><span class="p">);</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Quaternionf</span> <span class="n">quat</span> <span class="p">(</span><span class="n">rotational_matrix_OBB</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addCube</span> <span class="p">(</span><span class="n">position</span><span class="p">,</span> <span class="n">quat</span><span class="p">,</span> <span class="n">max_point_OBB</span><span class="p">.</span><span class="n">x</span> <span class="o">-</span> <span class="n">min_point_OBB</span><span class="p">.</span><span class="n">x</span><span class="p">,</span> <span class="n">max_point_OBB</span><span class="p">.</span><span class="n">y</span> <span class="o">-</span> <span class="n">min_point_OBB</span><span class="p">.</span><span class="n">y</span><span class="p">,</span> <span class="n">max_point_OBB</span><span class="p">.</span><span class="n">z</span> <span class="o">-</span> <span class="n">min_point_OBB</span><span class="p">.</span><span class="n">z</span><span class="p">,</span> <span class="s">&quot;OBB&quot;</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">center</span> <span class="p">(</span><span class="n">mass_center</span> <span class="p">(</span><span class="mi">0</span><span class="p">),</span> <span class="n">mass_center</span> <span class="p">(</span><span class="mi">1</span><span class="p">),</span> <span class="n">mass_center</span> <span class="p">(</span><span class="mi">2</span><span class="p">));</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">x_axis</span> <span class="p">(</span><span class="n">major_vector</span> <span class="p">(</span><span class="mi">0</span><span class="p">)</span> <span class="o">+</span> <span class="n">mass_center</span> <span class="p">(</span><span class="mi">0</span><span class="p">),</span> <span class="n">major_vector</span> <span class="p">(</span><span class="mi">1</span><span class="p">)</span> <span class="o">+</span> <span class="n">mass_center</span> <span class="p">(</span><span class="mi">1</span><span class="p">),</span> <span class="n">major_vector</span> <span class="p">(</span><span class="mi">2</span><span class="p">)</span> <span class="o">+</span> <span class="n">mass_center</span> <span class="p">(</span><span class="mi">2</span><span class="p">));</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">y_axis</span> <span class="p">(</span><span class="n">middle_vector</span> <span class="p">(</span><span class="mi">0</span><span class="p">)</span> <span class="o">+</span> <span class="n">mass_center</span> <span class="p">(</span><span class="mi">0</span><span class="p">),</span> <span class="n">middle_vector</span> <span class="p">(</span><span class="mi">1</span><span class="p">)</span> <span class="o">+</span> <span class="n">mass_center</span> <span class="p">(</span><span class="mi">1</span><span class="p">),</span> <span class="n">middle_vector</span> <span class="p">(</span><span class="mi">2</span><span class="p">)</span> <span class="o">+</span> <span class="n">mass_center</span> <span class="p">(</span><span class="mi">2</span><span class="p">));</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">z_axis</span> <span class="p">(</span><span class="n">minor_vector</span> <span class="p">(</span><span class="mi">0</span><span class="p">)</span> <span class="o">+</span> <span class="n">mass_center</span> <span class="p">(</span><span class="mi">0</span><span class="p">),</span> <span class="n">minor_vector</span> <span class="p">(</span><span class="mi">1</span><span class="p">)</span> <span class="o">+</span> <span class="n">mass_center</span> <span class="p">(</span><span class="mi">1</span><span class="p">),</span> <span class="n">minor_vector</span> <span class="p">(</span><span class="mi">2</span><span class="p">)</span> <span class="o">+</span> <span class="n">mass_center</span> <span class="p">(</span><span class="mi">2</span><span class="p">));</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addLine</span> <span class="p">(</span><span class="n">center</span><span class="p">,</span> <span class="n">x_axis</span><span class="p">,</span> <span class="mf">1.0f</span><span class="p">,</span> <span class="mf">0.0f</span><span class="p">,</span> <span class="mf">0.0f</span><span class="p">,</span> <span class="s">&quot;major eigen vector&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addLine</span> <span class="p">(</span><span class="n">center</span><span class="p">,</span> <span class="n">y_axis</span><span class="p">,</span> <span class="mf">0.0f</span><span class="p">,</span> <span class="mf">1.0f</span><span class="p">,</span> <span class="mf">0.0f</span><span class="p">,</span> <span class="s">&quot;middle eigen vector&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addLine</span> <span class="p">(</span><span class="n">center</span><span class="p">,</span> <span class="n">z_axis</span><span class="p">,</span> <span class="mf">0.0f</span><span class="p">,</span> <span class="mf">0.0f</span><span class="p">,</span> <span class="mf">1.0f</span><span class="p">,</span> <span class="s">&quot;minor eigen vector&quot;</span><span class="p">);</span>

  <span class="c1">//Eigen::Vector3f p1 (min_point_OBB.x, min_point_OBB.y, min_point_OBB.z);</span>
  <span class="c1">//Eigen::Vector3f p2 (min_point_OBB.x, min_point_OBB.y, max_point_OBB.z);</span>
  <span class="c1">//Eigen::Vector3f p3 (max_point_OBB.x, min_point_OBB.y, max_point_OBB.z);</span>
  <span class="c1">//Eigen::Vector3f p4 (max_point_OBB.x, min_point_OBB.y, min_point_OBB.z);</span>
  <span class="c1">//Eigen::Vector3f p5 (min_point_OBB.x, max_point_OBB.y, min_point_OBB.z);</span>
  <span class="c1">//Eigen::Vector3f p6 (min_point_OBB.x, max_point_OBB.y, max_point_OBB.z);</span>
  <span class="c1">//Eigen::Vector3f p7 (max_point_OBB.x, max_point_OBB.y, max_point_OBB.z);</span>
  <span class="c1">//Eigen::Vector3f p8 (max_point_OBB.x, max_point_OBB.y, min_point_OBB.z);</span>

  <span class="c1">//p1 = rotational_matrix_OBB * p1 + position;</span>
  <span class="c1">//p2 = rotational_matrix_OBB * p2 + position;</span>
  <span class="c1">//p3 = rotational_matrix_OBB * p3 + position;</span>
  <span class="c1">//p4 = rotational_matrix_OBB * p4 + position;</span>
  <span class="c1">//p5 = rotational_matrix_OBB * p5 + position;</span>
  <span class="c1">//p6 = rotational_matrix_OBB * p6 + position;</span>
  <span class="c1">//p7 = rotational_matrix_OBB * p7 + position;</span>
  <span class="c1">//p8 = rotational_matrix_OBB * p8 + position;</span>

  <span class="c1">//pcl::PointXYZ pt1 (p1 (0), p1 (1), p1 (2));</span>
  <span class="c1">//pcl::PointXYZ pt2 (p2 (0), p2 (1), p2 (2));</span>
  <span class="c1">//pcl::PointXYZ pt3 (p3 (0), p3 (1), p3 (2));</span>
  <span class="c1">//pcl::PointXYZ pt4 (p4 (0), p4 (1), p4 (2));</span>
  <span class="c1">//pcl::PointXYZ pt5 (p5 (0), p5 (1), p5 (2));</span>
  <span class="c1">//pcl::PointXYZ pt6 (p6 (0), p6 (1), p6 (2));</span>
  <span class="c1">//pcl::PointXYZ pt7 (p7 (0), p7 (1), p7 (2));</span>
  <span class="c1">//pcl::PointXYZ pt8 (p8 (0), p8 (1), p8 (2));</span>

  <span class="c1">//viewer-&gt;addLine (pt1, pt2, 1.0, 0.0, 0.0, &quot;1 edge&quot;);</span>
  <span class="c1">//viewer-&gt;addLine (pt1, pt4, 1.0, 0.0, 0.0, &quot;2 edge&quot;);</span>
  <span class="c1">//viewer-&gt;addLine (pt1, pt5, 1.0, 0.0, 0.0, &quot;3 edge&quot;);</span>
  <span class="c1">//viewer-&gt;addLine (pt5, pt6, 1.0, 0.0, 0.0, &quot;4 edge&quot;);</span>
  <span class="c1">//viewer-&gt;addLine (pt5, pt8, 1.0, 0.0, 0.0, &quot;5 edge&quot;);</span>
  <span class="c1">//viewer-&gt;addLine (pt2, pt6, 1.0, 0.0, 0.0, &quot;6 edge&quot;);</span>
  <span class="c1">//viewer-&gt;addLine (pt6, pt7, 1.0, 0.0, 0.0, &quot;7 edge&quot;);</span>
  <span class="c1">//viewer-&gt;addLine (pt7, pt8, 1.0, 0.0, 0.0, &quot;8 edge&quot;);</span>
  <span class="c1">//viewer-&gt;addLine (pt2, pt3, 1.0, 0.0, 0.0, &quot;9 edge&quot;);</span>
  <span class="c1">//viewer-&gt;addLine (pt4, pt8, 1.0, 0.0, 0.0, &quot;10 edge&quot;);</span>
  <span class="c1">//viewer-&gt;addLine (pt3, pt4, 1.0, 0.0, 0.0, &quot;11 edge&quot;);</span>
  <span class="c1">//viewer-&gt;addLine (pt3, pt7, 1.0, 0.0, 0.0, &quot;12 edge&quot;);</span>

  <span class="k">while</span><span class="p">(</span><span class="o">!</span><span class="n">viewer</span><span class="o">-&gt;</span><span class="n">wasStopped</span><span class="p">())</span>
  <span class="p">{</span>
    <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">spinOnce</span> <span class="p">(</span><span class="mi">100</span><span class="p">);</span>
    <span class="n">boost</span><span class="o">::</span><span class="n">this_thread</span><span class="o">::</span><span class="n">sleep</span> <span class="p">(</span><span class="n">boost</span><span class="o">::</span><span class="n">posix_time</span><span class="o">::</span><span class="n">microseconds</span> <span class="p">(</span><span class="mi">100000</span><span class="p">));</span>
  <span class="p">}</span>

  <span class="k">return</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>Now let&#8217;s study out what is the purpose of this code. First few lines will be omitted, as they are obvious.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">1</span><span class="p">],</span> <span class="o">*</span><span class="n">cloud</span><span class="p">)</span> <span class="o">==</span> <span class="o">-</span><span class="mi">1</span><span class="p">)</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
</pre></div>
</div>
<p>These lines are simply loading the cloud from the .pcd file.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">MomentOfInertiaEstimation</span> <span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">feature_extractor</span><span class="p">;</span>
  <span class="n">feature_extractor</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">feature_extractor</span><span class="p">.</span><span class="n">compute</span> <span class="p">();</span>
</pre></div>
</div>
<p>Here is the line where the instantiation of the <tt class="docutils literal"><span class="pre">pcl::MomentOfInertiaEstimation</span></tt> class takes place.
Immediately after that we set the input cloud and start the computational process, that easy.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">std</span><span class="o">::</span><span class="n">vector</span> <span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="n">moment_of_inertia</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span> <span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="n">eccentricity</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">min_point_AABB</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">max_point_AABB</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">min_point_OBB</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">max_point_OBB</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">position_OBB</span><span class="p">;</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix3f</span> <span class="n">rotational_matrix_OBB</span><span class="p">;</span>
  <span class="kt">float</span> <span class="n">major_value</span><span class="p">,</span> <span class="n">middle_value</span><span class="p">,</span> <span class="n">minor_value</span><span class="p">;</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span> <span class="n">major_vector</span><span class="p">,</span> <span class="n">middle_vector</span><span class="p">,</span> <span class="n">minor_vector</span><span class="p">;</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span> <span class="n">mass_center</span><span class="p">;</span>
</pre></div>
</div>
<p>This is were we declare all necessary variables needed to store descriptors and bounding boxes.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">feature_extractor</span><span class="p">.</span><span class="n">getMomentOfInertia</span> <span class="p">(</span><span class="n">moment_of_inertia</span><span class="p">);</span>
  <span class="n">feature_extractor</span><span class="p">.</span><span class="n">getEccentricity</span> <span class="p">(</span><span class="n">eccentricity</span><span class="p">);</span>
  <span class="n">feature_extractor</span><span class="p">.</span><span class="n">getAABB</span> <span class="p">(</span><span class="n">min_point_AABB</span><span class="p">,</span> <span class="n">max_point_AABB</span><span class="p">);</span>
  <span class="n">feature_extractor</span><span class="p">.</span><span class="n">getOBB</span> <span class="p">(</span><span class="n">min_point_OBB</span><span class="p">,</span> <span class="n">max_point_OBB</span><span class="p">,</span> <span class="n">position_OBB</span><span class="p">,</span> <span class="n">rotational_matrix_OBB</span><span class="p">);</span>
  <span class="n">feature_extractor</span><span class="p">.</span><span class="n">getEigenValues</span> <span class="p">(</span><span class="n">major_value</span><span class="p">,</span> <span class="n">middle_value</span><span class="p">,</span> <span class="n">minor_value</span><span class="p">);</span>
  <span class="n">feature_extractor</span><span class="p">.</span><span class="n">getEigenVectors</span> <span class="p">(</span><span class="n">major_vector</span><span class="p">,</span> <span class="n">middle_vector</span><span class="p">,</span> <span class="n">minor_vector</span><span class="p">);</span>
  <span class="n">feature_extractor</span><span class="p">.</span><span class="n">getMassCenter</span> <span class="p">(</span><span class="n">mass_center</span><span class="p">);</span>
</pre></div>
</div>
<p>These lines show how to access computed descriptors and other features.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">viewer</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="p">(</span><span class="s">&quot;3D Viewer&quot;</span><span class="p">));</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setBackgroundColor</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addCoordinateSystem</span> <span class="p">(</span><span class="mf">1.0</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">initCameraParameters</span> <span class="p">();</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addPointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="s">&quot;sample cloud&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addCube</span> <span class="p">(</span><span class="n">min_point_AABB</span><span class="p">.</span><span class="n">x</span><span class="p">,</span> <span class="n">max_point_AABB</span><span class="p">.</span><span class="n">x</span><span class="p">,</span> <span class="n">min_point_AABB</span><span class="p">.</span><span class="n">y</span><span class="p">,</span> <span class="n">max_point_AABB</span><span class="p">.</span><span class="n">y</span><span class="p">,</span> <span class="n">min_point_AABB</span><span class="p">.</span><span class="n">z</span><span class="p">,</span> <span class="n">max_point_AABB</span><span class="p">.</span><span class="n">z</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="s">&quot;AABB&quot;</span><span class="p">);</span>
</pre></div>
</div>
<p>These lines simply create the instance of <tt class="docutils literal"><span class="pre">PCLVisualizer</span></tt> class for result visualization.
Here we also add the cloud and the AABB for visualization.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span> <span class="n">position</span> <span class="p">(</span><span class="n">position_OBB</span><span class="p">.</span><span class="n">x</span><span class="p">,</span> <span class="n">position_OBB</span><span class="p">.</span><span class="n">y</span><span class="p">,</span> <span class="n">position_OBB</span><span class="p">.</span><span class="n">z</span><span class="p">);</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Quaternionf</span> <span class="n">quat</span> <span class="p">(</span><span class="n">rotational_matrix_OBB</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addCube</span> <span class="p">(</span><span class="n">position</span><span class="p">,</span> <span class="n">quat</span><span class="p">,</span> <span class="n">max_point_OBB</span><span class="p">.</span><span class="n">x</span> <span class="o">-</span> <span class="n">min_point_OBB</span><span class="p">.</span><span class="n">x</span><span class="p">,</span> <span class="n">max_point_OBB</span><span class="p">.</span><span class="n">y</span> <span class="o">-</span> <span class="n">min_point_OBB</span><span class="p">.</span><span class="n">y</span><span class="p">,</span> <span class="n">max_point_OBB</span><span class="p">.</span><span class="n">z</span> <span class="o">-</span> <span class="n">min_point_OBB</span><span class="p">.</span><span class="n">z</span><span class="p">,</span> <span class="s">&quot;OBB&quot;</span><span class="p">);</span>
</pre></div>
</div>
<p>Visualization of the OBB is little more complex. So here we create a quaternion from the rotational matrix, set OBBs position
and pass it to the visualizer.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">center</span> <span class="p">(</span><span class="n">mass_center</span> <span class="p">(</span><span class="mi">0</span><span class="p">),</span> <span class="n">mass_center</span> <span class="p">(</span><span class="mi">1</span><span class="p">),</span> <span class="n">mass_center</span> <span class="p">(</span><span class="mi">2</span><span class="p">));</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">x_axis</span> <span class="p">(</span><span class="n">major_vector</span> <span class="p">(</span><span class="mi">0</span><span class="p">)</span> <span class="o">+</span> <span class="n">mass_center</span> <span class="p">(</span><span class="mi">0</span><span class="p">),</span> <span class="n">major_vector</span> <span class="p">(</span><span class="mi">1</span><span class="p">)</span> <span class="o">+</span> <span class="n">mass_center</span> <span class="p">(</span><span class="mi">1</span><span class="p">),</span> <span class="n">major_vector</span> <span class="p">(</span><span class="mi">2</span><span class="p">)</span> <span class="o">+</span> <span class="n">mass_center</span> <span class="p">(</span><span class="mi">2</span><span class="p">));</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">y_axis</span> <span class="p">(</span><span class="n">middle_vector</span> <span class="p">(</span><span class="mi">0</span><span class="p">)</span> <span class="o">+</span> <span class="n">mass_center</span> <span class="p">(</span><span class="mi">0</span><span class="p">),</span> <span class="n">middle_vector</span> <span class="p">(</span><span class="mi">1</span><span class="p">)</span> <span class="o">+</span> <span class="n">mass_center</span> <span class="p">(</span><span class="mi">1</span><span class="p">),</span> <span class="n">middle_vector</span> <span class="p">(</span><span class="mi">2</span><span class="p">)</span> <span class="o">+</span> <span class="n">mass_center</span> <span class="p">(</span><span class="mi">2</span><span class="p">));</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">z_axis</span> <span class="p">(</span><span class="n">minor_vector</span> <span class="p">(</span><span class="mi">0</span><span class="p">)</span> <span class="o">+</span> <span class="n">mass_center</span> <span class="p">(</span><span class="mi">0</span><span class="p">),</span> <span class="n">minor_vector</span> <span class="p">(</span><span class="mi">1</span><span class="p">)</span> <span class="o">+</span> <span class="n">mass_center</span> <span class="p">(</span><span class="mi">1</span><span class="p">),</span> <span class="n">minor_vector</span> <span class="p">(</span><span class="mi">2</span><span class="p">)</span> <span class="o">+</span> <span class="n">mass_center</span> <span class="p">(</span><span class="mi">2</span><span class="p">));</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addLine</span> <span class="p">(</span><span class="n">center</span><span class="p">,</span> <span class="n">x_axis</span><span class="p">,</span> <span class="mf">1.0f</span><span class="p">,</span> <span class="mf">0.0f</span><span class="p">,</span> <span class="mf">0.0f</span><span class="p">,</span> <span class="s">&quot;major eigen vector&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addLine</span> <span class="p">(</span><span class="n">center</span><span class="p">,</span> <span class="n">y_axis</span><span class="p">,</span> <span class="mf">0.0f</span><span class="p">,</span> <span class="mf">1.0f</span><span class="p">,</span> <span class="mf">0.0f</span><span class="p">,</span> <span class="s">&quot;middle eigen vector&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addLine</span> <span class="p">(</span><span class="n">center</span><span class="p">,</span> <span class="n">z_axis</span><span class="p">,</span> <span class="mf">0.0f</span><span class="p">,</span> <span class="mf">0.0f</span><span class="p">,</span> <span class="mf">1.0f</span><span class="p">,</span> <span class="s">&quot;minor eigen vector&quot;</span><span class="p">);</span>
</pre></div>
</div>
<p>This lines are responsible for eigen vectors visualization.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">//Eigen::Vector3f p1 (min_point_OBB.x, min_point_OBB.y, min_point_OBB.z);</span>
  <span class="c1">//Eigen::Vector3f p2 (min_point_OBB.x, min_point_OBB.y, max_point_OBB.z);</span>
  <span class="c1">//Eigen::Vector3f p3 (max_point_OBB.x, min_point_OBB.y, max_point_OBB.z);</span>
  <span class="c1">//Eigen::Vector3f p4 (max_point_OBB.x, min_point_OBB.y, min_point_OBB.z);</span>
  <span class="c1">//Eigen::Vector3f p5 (min_point_OBB.x, max_point_OBB.y, min_point_OBB.z);</span>
  <span class="c1">//Eigen::Vector3f p6 (min_point_OBB.x, max_point_OBB.y, max_point_OBB.z);</span>
  <span class="c1">//Eigen::Vector3f p7 (max_point_OBB.x, max_point_OBB.y, max_point_OBB.z);</span>
  <span class="c1">//Eigen::Vector3f p8 (max_point_OBB.x, max_point_OBB.y, min_point_OBB.z);</span>

  <span class="c1">//p1 = rotational_matrix_OBB * p1 + position;</span>
  <span class="c1">//p2 = rotational_matrix_OBB * p2 + position;</span>
  <span class="c1">//p3 = rotational_matrix_OBB * p3 + position;</span>
  <span class="c1">//p4 = rotational_matrix_OBB * p4 + position;</span>
  <span class="c1">//p5 = rotational_matrix_OBB * p5 + position;</span>
  <span class="c1">//p6 = rotational_matrix_OBB * p6 + position;</span>
  <span class="c1">//p7 = rotational_matrix_OBB * p7 + position;</span>
  <span class="c1">//p8 = rotational_matrix_OBB * p8 + position;</span>

  <span class="c1">//pcl::PointXYZ pt1 (p1 (0), p1 (1), p1 (2));</span>
  <span class="c1">//pcl::PointXYZ pt2 (p2 (0), p2 (1), p2 (2));</span>
  <span class="c1">//pcl::PointXYZ pt3 (p3 (0), p3 (1), p3 (2));</span>
  <span class="c1">//pcl::PointXYZ pt4 (p4 (0), p4 (1), p4 (2));</span>
  <span class="c1">//pcl::PointXYZ pt5 (p5 (0), p5 (1), p5 (2));</span>
  <span class="c1">//pcl::PointXYZ pt6 (p6 (0), p6 (1), p6 (2));</span>
  <span class="c1">//pcl::PointXYZ pt7 (p7 (0), p7 (1), p7 (2));</span>
  <span class="c1">//pcl::PointXYZ pt8 (p8 (0), p8 (1), p8 (2));</span>

  <span class="c1">//viewer-&gt;addLine (pt1, pt2, 1.0, 0.0, 0.0, &quot;1 edge&quot;);</span>
  <span class="c1">//viewer-&gt;addLine (pt1, pt4, 1.0, 0.0, 0.0, &quot;2 edge&quot;);</span>
  <span class="c1">//viewer-&gt;addLine (pt1, pt5, 1.0, 0.0, 0.0, &quot;3 edge&quot;);</span>
  <span class="c1">//viewer-&gt;addLine (pt5, pt6, 1.0, 0.0, 0.0, &quot;4 edge&quot;);</span>
  <span class="c1">//viewer-&gt;addLine (pt5, pt8, 1.0, 0.0, 0.0, &quot;5 edge&quot;);</span>
  <span class="c1">//viewer-&gt;addLine (pt2, pt6, 1.0, 0.0, 0.0, &quot;6 edge&quot;);</span>
  <span class="c1">//viewer-&gt;addLine (pt6, pt7, 1.0, 0.0, 0.0, &quot;7 edge&quot;);</span>
  <span class="c1">//viewer-&gt;addLine (pt7, pt8, 1.0, 0.0, 0.0, &quot;8 edge&quot;);</span>
  <span class="c1">//viewer-&gt;addLine (pt2, pt3, 1.0, 0.0, 0.0, &quot;9 edge&quot;);</span>
  <span class="c1">//viewer-&gt;addLine (pt4, pt8, 1.0, 0.0, 0.0, &quot;10 edge&quot;);</span>
  <span class="c1">//viewer-&gt;addLine (pt3, pt4, 1.0, 0.0, 0.0, &quot;11 edge&quot;);</span>
  <span class="c1">//viewer-&gt;addLine (pt3, pt7, 1.0, 0.0, 0.0, &quot;12 edge&quot;);</span>
</pre></div>
</div>
<p>This huge amount of code shows how to work with the oriented bounding box. Note that you need to rotate each of the vertices of the OBB.
This code does the same thing as <tt class="docutils literal"><span class="pre">PCLVisualizer::addCube</span> <span class="pre">()</span></tt> method. Its only purpose is to show how to work with OBB
if you don&#8217;t have such usable method as <tt class="docutils literal"><span class="pre">PCLVisualizer::addCube</span> <span class="pre">()</span></tt>.</p>
<p>Few lines that left simply launch the visualization process.</p>
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

<span class="nb">project</span><span class="p">(</span><span class="s">moment_of_inertia</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.8</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">moment_of_inertia</span> <span class="s">moment_of_inertia.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">moment_of_inertia</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it. Simply do:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./moment_of_inertia lamppost.pcd
</pre></div>
</div>
<p>You should see something similar to this image. Here AABB is yellow, OBB is red. You can also see the eigen vectors.</p>
<a class="reference internal image-reference" href="_images/moment_of_inertia.png"><img alt="_images/moment_of_inertia.png" src="_images/moment_of_inertia.png" style="height: 360px;" /></a>
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