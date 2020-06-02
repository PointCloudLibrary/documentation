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
    
    <title>The PCL Registration API</title>
    
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
            
  <div class="section" id="the-pcl-registration-api">
<span id="registration-api"></span><h1>The PCL Registration API</h1>
<p>The problem of consistently aligning various 3D point cloud data views into a
complete model is known as <strong>registration</strong>. Its goal is to find the relative
positions and orientations of the separately acquired views in a global
coordinate framework, such that the intersecting areas between them overlap
perfectly. For every set of point cloud datasets acquired from different views,
we therefore need a system that is able to align them together into a single
point cloud model, so that subsequent processing steps such as segmentation and
object reconstruction can be applied.</p>
<img alt="_images/scans.jpg" class="align-center" src="_images/scans.jpg" />
<p>A motivation example in this sense is given in the figure above, where a set of
six individual datasets has been acquired using a tilting 2D laser unit. Since
each individual scan represents only a small part of the surrounding world, it
is imperative to find ways to register them together, thus creating the complete
point cloud model as shown in the figure below.</p>
<img alt="_images/s1-6.jpg" class="align-center" src="_images/s1-6.jpg" />
<p>The algorithmic work in the PCL registration library is motivated by finding
correct point correspondences in the given input datasets, and estimating rigid
transformations that can rotate and translate each individual dataset into a
consistent global coordinate framework. This registration paradigm becomes
easily solvable if the point correspondences are perfectly known in the input
datasets. This means that a selected list of points in one dataset have to
&#8220;coincide&#8221; from a feature representation point of view with a list of points
from another dataset. Additionally, if the correspondences estimated are
&#8220;perfect&#8221;, then the registration problem has a closed form solution.</p>
<p>PCL contains a set of powerful algorithms that allow the estimation of multiple
sets of correspondences, as well as methods for rejecting bad correspondences,
and estimating transformations in a robust manner from them. The following
sections will describe each of them individually.</p>
</div>
<div class="section" id="an-overview-of-pairwise-registration">
<h1>An overview of pairwise registration</h1>
<p>We sometimes refer to the problem of registering a pair of point cloud datasets
together as <em>pairwise registration</em>, and its output is usually a rigid
transformation matrix (4x4) representing the rotation and translation that would
have to be applied on one of the datasets (let&#8217;s call it <em>source</em>) in order for
it to be perfectly aligned with the other dataset (let&#8217;s call it <em>target</em>, or
<em>model</em>).</p>
<p>The steps performed in a <em>pairwise registration</em> step are shown in the diagram
below. Please note that we are representing a single iteration of the algorithm.
The programmer can decide to loop over any or all of the steps.</p>
<img alt="_images/block_diagram_single_iteration.jpg" class="align-center" src="_images/block_diagram_single_iteration.jpg" />
<p>The computational steps for two datasets are straighforward:</p>
<blockquote>
<div><ul class="simple">
<li>from a set of points, identify <strong>interest points</strong> (i.e., <strong>keypoints</strong>) that best represent the scene in both datasets;</li>
<li>at each keypoint, compute a <strong>feature descriptor</strong>;</li>
<li>from the set of <strong>feature descriptors</strong> together with their XYZ positions in the two datasets, estimate a set of <strong>correspondences</strong>, based on the similarities between features and positions;</li>
<li>given that the data is assumed to be noisy, not all correspondences are valid, so reject those bad correspondences that contribute negatively to the registration process;</li>
<li>from the remaining set of good correspondences, estimate a motion transformation.</li>
</ul>
</div></blockquote>
</div>
<div class="section" id="registration-modules">
<h1>Registration modules</h1>
<p>Let&#8217;s have a look at the single steps of the pipeline.</p>
<div class="section" id="keypoints">
<h2>Keypoints</h2>
<p>A keypoint is an interest point that has a &#8220;special property&#8221; in the scene,
like the corner of a book, or the letter &#8220;P&#8221; on a book that has written &#8220;PCL&#8221;
on it. There are a number of different keypoints available in PCL like NARF,
SIFT and FAST. Alternatively you can take every point, or a subset, as
keypoints as well. The problem with &#8220;feeding two kinect datasets into a correspondence estimation&#8221; directly is that you have 300k points in each frame, so there can be 300k^2 correspondences.</p>
</div>
<div class="section" id="feature-descriptors">
<h2>Feature descriptors</h2>
<p>Based on the keypoints found we have to extract [features](<a class="reference external" href="http://www.pointclouds.org/documentation/tutorials/how_features_work.php">http://www.pointclouds.org/documentation/tutorials/how_features_work.php</a>), where we assemble the information and generate vectors to compare them with each other. Again there
is a number of feature options to choose from, for example NARF, FPFH, BRIEF or
SIFT.</p>
</div>
<div class="section" id="correspondences-estimation">
<h2>Correspondences estimation</h2>
<p>Given two sets of feature vectors coming from two acquired scans we have to
find corresponding features to find overlapping parts in the data. Depending on
the feature type we can use different methods to find the correspondences.</p>
<p>For <em>point matching</em> (using the points&#8217; xyz-coordinates as features) different
methods exist for organized and unorganized data:</p>
<ul class="simple">
<li>brute force matching,</li>
<li>kd-tree nearest neighbor search (FLANN),</li>
<li>searching in the image space of organized data, and</li>
<li>searching in the index space of organized data.</li>
</ul>
<p>For <em>feature matching</em> (not using the points&#8217; coordinates, but certain features)
only the following methods exist:</p>
<ul class="simple">
<li>brute force matching and</li>
<li>kd-tree nearest neighbor search (FLANN).</li>
</ul>
<p>In addition to the search, two types of correspondence estimation are
distinguished:</p>
<ul class="simple">
<li>Direct correspondence estimation (default) searches for correspondences
in cloud B for every point in cloud A .</li>
<li>&#8220;Reciprocal&#8221; correspondence estimation searches for correspondences from
cloud A to cloud B, and from B to A and only use the intersection.</li>
</ul>
</div>
<div class="section" id="correspondences-rejection">
<h2>Correspondences rejection</h2>
<p>Naturally, not all estimated correspondences are correct.
Since wrong correspondences can negatively affect the estimation of the final
transformation, they need to be rejected.
This could be done using RANSAC or by trimming down the amount and using only a
certain percent of the found correspondences.</p>
<p>A special case are one to many correspondences where one point in the model
corresponds to a number of points in the source. These could be filtered by
using only the one with the smallest distance or  by checking for other
matchings near by.</p>
</div>
<div class="section" id="transformation-estimation">
<h2>Transformation estimation</h2>
<p>The last step is to actually compute the transformation.</p>
<ul class="simple">
<li>evaluate some error metric based on correspondence</li>
<li>estimate a (rigid) transformation between camera poses (motion estimate) and minimize error metric</li>
<li>optimize the structure of the points</li>
<li>Examples:
- SVD for motion estimate;
- Levenberg-Marquardt with different kernels for motion estimate;</li>
<li>use the rigid transformation to rotate/translate the source onto the target,
and potentially run an internal ICP loop with either all points or a subset
of points or the keypoints</li>
<li>iterate until some convergence criterion is met</li>
</ul>
</div>
<div class="section" id="example-pipelines">
<h2>Example pipelines</h2>
<div class="section" id="iterative-closest-point">
<h3>Iterative Closest Point</h3>
<ol class="arabic simple">
<li>Search for correspondences.</li>
<li>Reject bad correspondences.</li>
<li>Estimate a transformation using the good correspondences.</li>
<li>Iterate.</li>
</ol>
</div>
<div class="section" id="feature-based-registration">
<h3>Feature based registration</h3>
<ol class="arabic simple">
<li>use SIFT Keypoints (pcl::SIFT...something)</li>
<li>use FPFH descriptors (pcl::FPFHEstimation) at the keypoints (see our tutorials for that, like http://www.pointclouds.org/media/rss2011.html)</li>
<li>get the FPFH descriptors and estimate correspondences using pcl::CorrespondenceEstimation</li>
<li>reject bad correspondences using one or many of the pcl::CorrespondenceRejectionXXX methods</li>
<li>finally get a transformation as mentioned above</li>
</ol>
</div>
</div>
</div>
<div class="section" id="example-1-office-scene-kinect-data">
<h1>Example 1: Office scene, Kinect data</h1>
</div>
<div class="section" id="example-2-outdoor-scene-laser-riegl-data">
<h1>Example 2: Outdoor scene, Laser (Riegl) data</h1>
</div>
<div class="section" id="example-3-indoor-scene-laser-sick-data">
<h1>Example 3: Indoor scene, Laser (SICK) data</h1>
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