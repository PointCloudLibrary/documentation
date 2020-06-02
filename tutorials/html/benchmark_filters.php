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
    
    <title>Filter Benchmarking</title>
    
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
            
  <div class="section" id="filter-benchmarking">
<span id="filterbenchmarking"></span><h1>Filter Benchmarking</h1>
<p>This document introduces benchmarking concepts for filtering algorithms. By
<em>benchmarking</em> here we refer to the possibility of testing different
parameters for each filter algorithm on a specific point cloud in an <strong>easy manner</strong>. The goal is to find the best paramaters of a certain filter that best describe the original point cloud without removing useful data.</p>
</div>
<div class="section" id="benchmarking-filter-algorithms">
<h1>Benchmarking Filter Algorithms</h1>
<p>To get rid of noisy data in a scan of a 3D scene or object, many filters could be applied to obtain the <em>cleanest</em> representation possible of the object or scene. These filters need to be tuned according to the characteristics of the raw data. A filter evaluation class can be implemented, similar to the <strong>FeatureEvaluationFramework</strong> to find these parameters.</p>
<div class="section" id="functionality">
<h2>1. Functionality</h2>
<p>The <strong>FilterEvaluationFramework</strong> object could be initialized by the following functions:</p>
<blockquote>
<div><ul class="simple">
<li>setInputCloud: <em>Load test cloud from .pcd file</em>;</li>
<li>setFilterTest: <em>Choose the filter algorithm to be tested</em>;</li>
<li>setParameters: <em>Specific to the Filter Algorithm</em>;</li>
<li>setThreshold: <em>A single or a range of threshold values for the evaluation metric</em>;</li>
</ul>
</div></blockquote>
</div>
<div class="section" id="filter-types-and-parameters">
<h2>2. Filter Types and Parameters</h2>
<p>Provide test classes for all the existing filters implemented in PCL.</p>
<blockquote>
<div><ul class="simple">
<li>StatisticalOutlierRemoval: <em>meanK and StddevMulThresh</em>;</li>
<li>RadiusOutlierRemoval: <em>radiusSearch and MinNeighborsInRadius</em>;</li>
<li>VoxelGrid: <em>LeafSize</em>;</li>
<li>etc..</li>
</ul>
</div></blockquote>
<p>Users should be able to add their custom filter implementations to the framework.</p>
</div>
<div class="section" id="evaluation">
<h2>3. Evaluation</h2>
<p>This benchmark should be able to evaluate the filter&#8217;s performance with the specified parameters. The Evaluation metrics should answer the following questions:</p>
<blockquote>
<div><ul class="simple">
<li>Did the filter remove useful data? (new holes)</li>
<li>Is the new filtered cloud a clear representation of the original? (same surface)</li>
<li>Computation Time?</li>
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