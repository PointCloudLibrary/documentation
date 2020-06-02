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
    
    <title>Estimating VFH signatures for a set of points</title>
    
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
            
  <div class="section" id="estimating-vfh-signatures-for-a-set-of-points">
<span id="vfh-estimation"></span><h1>Estimating VFH signatures for a set of points</h1>
<p>This document describes the Viewpoint Feature Histogram (<a class="reference internal" href="#vfh" id="id1">[VFH]</a>) descriptor, a
novel representation for point clusters for the problem of Cluster (e.g.,
Object) Recognition and 6DOF Pose Estimation.</p>
<p>The image below exhibits an example of VFH <em>recognition</em> and pose estimation.
Given a set of train data (top row, bottom row except the leftmost point
cloud), a model is learned and then a cloud (bottom left part) is used to
query/test the model. The matched results in order from best to worst go from
left to right starting at bottom left. For more information please see
<a class="reference internal" href="vfh_recognition.php#vfh-recognition"><em>Cluster Recognition and 6DOF Pose Estimation using VFH descriptors</em></a> and/or <a class="reference internal" href="#vfh" id="id2">[VFH]</a>.</p>
<img alt="_images/vfh_example.jpg" class="align-center" src="_images/vfh_example.jpg" />
</div>
<div class="section" id="theoretical-primer">
<h1>Theoretical primer</h1>
<p>The Viewpoint Feature Histogram (or VFH) has its roots in the FPFH descriptor
(see <a class="reference internal" href="fpfh_estimation.php#fpfh-estimation"><em>Fast Point Feature Histograms (FPFH) descriptors</em></a>). Due to its speed and discriminative power, we
decided to leverage the strong recognition results of FPFH, but to add in
viewpoint variance while retaining invariance to scale.</p>
<p>Our contribution to the problem of object recognition and pose identification
was to extend the FPFH to be estimated for the entire object cluster (as seen
in the figure below), and to compute additional statistics between the
viewpoint direction and the normals estimated at each point. To do this, we
used the key idea of mixing the viewpoint direction directly into the relative
normal angle calculation in the FPFH.</p>
<img alt="_images/first_component.jpg" class="align-center" src="_images/first_component.jpg" />
<p>The viewpoint component is computed by collecting a histogram of the angles
that the viewpoint direction makes with each normal. Note, we do not mean the
view angle to each normal as this would not be scale invariant, but instead we
mean the angle between the central viewpoint direction translated to each
normal. The second component measures the relative pan, tilt and yaw angles as
described in <a class="reference internal" href="fpfh_estimation.php#fpfh-estimation"><em>Fast Point Feature Histograms (FPFH) descriptors</em></a> but now measured between the viewpoint
direction at the central point and each of the normals on the surface.</p>
<img alt="_images/second_component.jpg" class="align-center" src="_images/second_component.jpg" />
<p>The new assembled feature is therefore called the Viewpoint Feature Histogram (VFH). The figure below presents this idea with the new feature consisting of two parts:</p>
<blockquote>
<div><ol class="arabic simple">
<li>a viewpoint direction component and</li>
<li>a surface shape component comprised of an extended FPFH.</li>
</ol>
</div></blockquote>
<img alt="_images/vfh_histogram.jpg" class="align-center" src="_images/vfh_histogram.jpg" />
</div>
<div class="section" id="estimating-vfh-features">
<h1>Estimating VFH features</h1>
<p>The Viewpoint Feature Histogram is implemented in PCL as part of the
<a class="reference external" href="http://docs.pointclouds.org/trunk/a02944.html">pcl_features</a>
library.</p>
<p>The default VFH implementation uses 45 binning subdivisions for each of the
three extended FPFH values, plus another 45 binning subdivisions for the distances between each point and the centroid and 128 binning subdivisions for the viewpoint
component, which results in a 308-byte array of float values. These are stored
in a <strong>pcl::VFHSignature308</strong> point type.</p>
<p>The major difference between the PFH/FPFH descriptors and VFH, is that for a
given point cloud dataset, only a single VFH descriptor will be estimated,
while the resultant PFH/FPFH data will have the same number of entries as the
number of points in the cloud.</p>
<p>The following code snippet will estimate a set of VFH features for all the
points in the input dataset.</p>
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
29</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/features/vfh.h&gt;</span>

<span class="p">{</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">normals</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span> <span class="p">());</span>

  <span class="p">...</span> <span class="n">read</span><span class="p">,</span> <span class="n">pass</span> <span class="n">in</span> <span class="n">or</span> <span class="n">create</span> <span class="n">a</span> <span class="n">point</span> <span class="n">cloud</span> <span class="n">with</span> <span class="n">normals</span> <span class="p">...</span>
  <span class="p">...</span> <span class="p">(</span><span class="n">note</span><span class="o">:</span> <span class="n">you</span> <span class="n">can</span> <span class="n">create</span> <span class="n">a</span> <span class="n">single</span> <span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;</span> <span class="k">if</span> <span class="n">you</span> <span class="n">want</span><span class="p">)</span> <span class="p">...</span>

  <span class="c1">// Create the VFH estimation class, and pass the input dataset+normals to it</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">VFHEstimation</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">VFHSignature308</span><span class="o">&gt;</span> <span class="n">vfh</span><span class="p">;</span>
  <span class="n">vfh</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">vfh</span><span class="p">.</span><span class="n">setInputNormals</span> <span class="p">(</span><span class="n">normals</span><span class="p">);</span>
  <span class="c1">// alternatively, if cloud is of type PointNormal, do vfh.setInputNormals (cloud);</span>

  <span class="c1">// Create an empty kdtree representation, and pass it to the FPFH estimation object.</span>
  <span class="c1">// Its content will be filled inside the object, based on the given input dataset (as no other search surface is given).</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">tree</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">vfh</span><span class="p">.</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">tree</span><span class="p">);</span>

  <span class="c1">// Output datasets</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">VFHSignature308</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">vfhs</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">VFHSignature308</span><span class="o">&gt;</span> <span class="p">());</span>

  <span class="c1">// Compute the features</span>
  <span class="n">vfh</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">vfhs</span><span class="p">);</span>

  <span class="c1">// vfhs-&gt;points.size () should be of size 1*</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="visualizing-vfh-signatures">
<h1>Visualizing VFH signatures</h1>
<p><em>libpcl_visualization</em> contains a special <strong>PCLHistogramVisualization</strong> class,
which is also used by <strong>pcl_viewer</strong> to automaticall display the VFH
descriptors as a histogram of float values. For more information, please see
<a class="reference external" href="http://www.pointclouds.org/documentation/overview/visualization.php">http://www.pointclouds.org/documentation/overview/visualization.php</a>.</p>
<img alt="_images/vfh_histogram_visualized.jpg" class="align-center" src="_images/vfh_histogram_visualized.jpg" />
<table class="docutils citation" frame="void" id="vfh" rules="none">
<colgroup><col class="label" /><col /></colgroup>
<tbody valign="top">
<tr><td class="label">[VFH]</td><td><em>(<a class="fn-backref" href="#id1">1</a>, <a class="fn-backref" href="#id2">2</a>)</em> <a class="reference external" href="http://www.willowgarage.com/sites/default/files/Rusu10IROS.pdf">http://www.willowgarage.com/sites/default/files/Rusu10IROS.pdf</a></td></tr>
</tbody>
</table>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">&#64;InProceedings{Rusu10IROS,
author = {Radu Bogdan Rusu and Gary Bradski and Romain Thibaux and John Hsu},
title = {Fast 3D Recognition and Pose Using the Viewpoint Feature Histogram},
booktitle = {Proceedings of the 23rd IEEE/RSJ International Conference on Intelligent Robots and Systems (IROS)},
year = {2010},
address = {Taipei, Taiwan},
month = {October}
}</p>
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