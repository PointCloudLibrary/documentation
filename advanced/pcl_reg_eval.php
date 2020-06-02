<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation - Point Cloud Library (PCL)</title>
</head>

<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8" />
    <title>Evaluating pcl/registration</title>
    <link rel="stylesheet" href="_static/sphinxdoc.css" type="text/css" />
    <link rel="stylesheet" href="_static/pygments.css" type="text/css" />
    <script id="documentation_options" data-url_root="./" src="_static/documentation_options.js"></script>
    <script src="_static/jquery.js"></script>
    <script src="_static/underscore.js"></script>
    <script src="_static/doctools.js"></script>
    <script src="_static/language_data.js"></script>
    <link rel="search" title="Search" href="search.php" />
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

  </head><body>

    <div class="document">
      <div class="documentwrapper">
          <div class="body" role="main">
            
  <div class="section" id="evaluating-pcl-registration">
<h1>Evaluating pcl/registration</h1>
<p>This is a collection of ideas on how to build an evaluation framework of pcl/registration.</p>
<div class="section" id="data-generation">
<h2>Data generation</h2>
<ul class="simple">
<li><p>synthetic data</p></li>
<li><p>real word data (how to get ground truth?)
- Kinect
- PR2 laser scanner
- SICK laser data
- small range 3D scanner
- mid range 3D scanner (Faro)
- high end 3D scanner (Riegl, Velodyne)</p></li>
<li><p>Point Types
- 2D(?)
- 3D
- RGB</p></li>
<li><p>dynamics
- static scans
- scanning while driving (e.g. robots)</p></li>
<li><p>size
- room
- building
- outdoor (street)</p></li>
</ul>
</div>
<div class="section" id="architecture">
<h2>Architecture</h2>
<ul class="simple">
<li><p>some lib for polygonal data</p></li>
<li><p>modeling different sensors</p></li>
<li><p>modeling noise</p></li>
<li><p>add a trajectory file</p></li>
<li><p>output a pile of .pcd files</p></li>
<li><p>integrate command line tools from PCL grandfather</p></li>
</ul>
</div>
<div class="section" id="evaluating-different-algorithms">
<h2>Evaluating different algorithms</h2>
<div class="section" id="icp">
<h3>ICP</h3>
<ul class="simple">
<li><p>how does the algorithm cope with outliers</p></li>
<li><p>how are the point pairs evaluated:</p>
<ul>
<li><p>does it use normal or RGB information</p></li>
<li><p>does it weight the pairs differently</p></li>
<li><p>which kind of point pairs are used:</p>
<ul>
<li><p>one-to-one</p></li>
<li><p>one-to-many</p></li>
<li><p>many-to-many</p></li>
</ul>
</li>
</ul>
</li>
</ul>
</div>
</div>
<div class="section" id="similar-projects">
<h2>Similar Projects</h2>
<ul class="simple">
<li><p><a class="reference external" href="http://stanford.edu/~avsegal/resources/papers/Generalized_ICP.pdf">GICP</a></p></li>
<li><p>Gazebo</p></li>
<li><p><a class="reference external" href="http://kaspar.informatik.uni-freiburg.de/~slamEvaluation/index.php">slam benchmarking</a></p></li>
<li><p><a class="reference external" href="http://slameval.willowgarage.com/workshop/">Automated SLAM Evaluation</a></p></li>
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