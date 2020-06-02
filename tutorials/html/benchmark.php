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
    
    <title>Benchmarking 3D</title>
    
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
            
  <div class="section" id="benchmarking-3d">
<span id="benchmarking"></span><h1>Benchmarking 3D</h1>
<p>This document introduces benchmarking concepts for 3D algorithms. By
<em>benchmarking</em> here we refer to the posibility of testing different
computational pipelines in an <strong>easy manner</strong>. The goal is to test their
reproductibility with respect to a particular problem of general interest.</p>
</div>
<div class="section" id="benchmarking-object-recognition">
<h1>Benchmarking Object Recognition</h1>
<p>For the general problem of Object Recognition (identification, categorization,
detection, etc &#8211; all fall in the same category here), we identify the
following steps:</p>
<div class="section" id="training">
<h2>1. Training</h2>
<p>Users should be able to acquire training data from different inputs, including
but not limited to:</p>
<blockquote>
<div><ul class="simple">
<li>full triangle meshes (CAD models);</li>
<li>360-degree full point cloud models;</li>
<li>partial point cloud views:<ul>
<li>in clutter;</li>
<li>cleanly segmented.</li>
</ul>
</li>
</ul>
</div></blockquote>
</div>
<div class="section" id="keypoints">
<h2>2. Keypoints</h2>
<p>Computing higher level representation from the object&#8217;s appearance (texture + depth) should be done:</p>
<blockquote>
<div><ul class="simple">
<li><strong>densely</strong> - at every point/vertex in the input data;</li>
<li>at certain <strong>interest points</strong> (i.e., keypoints).</li>
</ul>
</div></blockquote>
<p>The detected keypoint might also contain some meta-information required by some descriptors, like scale or orientation.</p>
</div>
<div class="section" id="descriptors">
<h2>3. Descriptors</h2>
<p>A higher level representation as mentioned before will be herein represented by a <strong>feature descriptor</strong>. Feature descriptors can be:</p>
<blockquote>
<div><ul class="simple">
<li>2D (two-dimensional) &#8211; here we refer to those descriptors estimated solely from RGB texture data;</li>
<li>3D (three-dimensional) &#8211; here we refer to those descriptors estimated solely from XYZ/depth data;</li>
<li>a combination of the above.</li>
</ul>
</div></blockquote>
<p>In addtion, feature descriptors can be:</p>
<blockquote>
<div><ul class="simple">
<li><strong>local</strong> - estimated only at a set of discrete keypoints, using the information from neighboring pixels/points;</li>
<li><strong>global</strong>, or meta-local - estimated on entire objects or the entire input dataset.</li>
</ul>
</div></blockquote>
</div>
<div class="section" id="classification">
<h2>4. Classification</h2>
<p>The distribution of features should be classifiable into distinct, separable
classes. For local features, we identify two sets of techniques:</p>
<blockquote>
<div><ul class="simple">
<li><strong>bag of words</strong>;</li>
<li><strong>voting</strong>;</li>
<li><strong>supervised voting</strong> (regression from the description to the relative 3D location, e.g. Hough forest).</li>
</ul>
</div></blockquote>
<p>For global features, any general purpose classification technique should work (e.g., SVMs, nearest neighbors, etc).</p>
<p>In addition to classification, a substep of it could be considered
<strong>Registration</strong>. Here we refine the classification results using iterative
closest point techniques for example.</p>
</div>
<div class="section" id="evaluation">
<h2>5. Evaluation</h2>
<p>This pipeline should be able to evaluate the algorithm&#8217;s performance at
different tasks. Here are some requested tasks to support:</p>
<blockquote>
<div><ul class="simple">
<li>object id and pose</li>
<li>object id and segmentation</li>
<li>object id and bounding box</li>
<li>category and segmentation</li>
<li>category and bounding box</li>
</ul>
</div></blockquote>
<div class="section" id="metrics">
<h3>5.1 Metrics</h3>
<p>This pipeline should provide different metrics, since algorithms excel in
different areas. Here are some requested metrics:</p>
<blockquote>
<div><ul class="simple">
<li>precision-recall</li>
<li>time</li>
<li>average rank of correct id</li>
<li>area under curve of cumulative histogram of rank of correct id</li>
</ul>
</div></blockquote>
<div class="section" id="object-recognition-api">
<h4>Object Recognition API</h4>
<p>Here we describe a proposed set of classes that could be easily extended and
used for the purpose of benchmarking object recognition tasks.</p>
</div>
</div>
</div>
<div class="section" id="id1">
<h2>1. Training</h2>
</div>
<div class="section" id="id2">
<h2>2. Keypoints</h2>
</div>
<div class="section" id="id3">
<h2>3. Descriptors</h2>
</div>
<div class="section" id="id4">
<h2>4. Classification</h2>
</div>
<div class="section" id="id5">
<h2>5. Evaluation</h2>
<p>The evaluation output needs to be one of the following:</p>
<blockquote>
<div><ul class="simple">
<li>object id</li>
<li>object pose</li>
<li>object category</li>
<li>object bounding box</li>
<li>object mask</li>
</ul>
</div></blockquote>
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