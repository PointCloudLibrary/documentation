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
    
    <title>Evaluating pcl/registration</title>
    
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
            
  <div class="section" id="evaluating-pcl-registration">
<h1>Evaluating pcl/registration</h1>
<p>This is a collection of ideas on how to build an evaluation framework of pcl/registration.</p>
<div class="section" id="data-generation">
<h2>Data generation</h2>
<ul class="simple">
<li>synthetic data</li>
<li>real word data (how to get ground truth?)</li>
</ul>
<blockquote>
<div><ul class="simple">
<li>Kinect</li>
<li>PR2 laser scanner</li>
<li>SICK laser data</li>
<li>small range 3D scanner</li>
<li>mid range 3D scanner (Faro)</li>
<li>high end 3D scanner (Riegl, Velodyne)</li>
</ul>
</div></blockquote>
<ul class="simple">
<li>Point Types</li>
</ul>
<blockquote>
<div><ul class="simple">
<li>2D(?)</li>
<li>3D</li>
<li>RGB</li>
</ul>
</div></blockquote>
<ul class="simple">
<li>dynamics</li>
</ul>
<blockquote>
<div><ul class="simple">
<li>static scans</li>
<li>scanning while driving (e.g. robots)</li>
</ul>
</div></blockquote>
<ul class="simple">
<li>size</li>
</ul>
<blockquote>
<div><ul class="simple">
<li>room</li>
<li>building</li>
<li>outdoor (street)</li>
</ul>
</div></blockquote>
</div>
<div class="section" id="architecture">
<h2>Architecture</h2>
<ul class="simple">
<li>some lib for polygonal data</li>
<li>modeling different sensors</li>
<li>modeling noise</li>
<li>add a trajectory file</li>
<li>output a pile of .pcd files</li>
<li>integrate command line tools from PCL grandfather</li>
</ul>
</div>
<div class="section" id="evaluating-different-algorithms">
<h2>Evaluating different algorithms</h2>
<div class="section" id="icp">
<h3>ICP</h3>
<ul class="simple">
<li>how does the algorithm cope with outliers</li>
<li>how are the point pairs evaluated:</li>
</ul>
<blockquote>
<div><ul class="simple">
<li>does it use normal or RGB information</li>
<li>does it weight the pairs differently</li>
<li>which kind of point pairs are used:</li>
</ul>
<blockquote>
<div><ul class="simple">
<li>one-to-one</li>
<li>one-to-many</li>
<li>many-to-many</li>
</ul>
</div></blockquote>
</div></blockquote>
</div>
</div>
<div class="section" id="similar-projects">
<h2>Similar Projects</h2>
<ul class="simple">
<li><a class="reference external" href="http://stanford.edu/~avsegal/resources/papers/Generalized_ICP.pdf">GICP</a></li>
<li>Gazebo</li>
<li><a class="reference external" href="http://kaspar.informatik.uni-freiburg.de/~slamEvaluation/index.php">slam benchmarking</a></li>
<li><a class="reference external" href="http://slameval.willowgarage.com/workshop/">Automated SLAM Evaluation</a></li>
</ul>
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