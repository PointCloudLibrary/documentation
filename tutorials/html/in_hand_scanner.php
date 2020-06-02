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
    
    <title>In-hand scanner for small objects</title>
    
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
            
  <div class="section" id="in-hand-scanner-for-small-objects">
<span id="in-hand-scanner"></span><h1>In-hand scanner for small objects</h1>
<div class="section" id="introduction">
<h2>Introduction</h2>
<p>The purpose of the in-hand scanner application is to obtain a 3D model from a small object. The user is turning the object around in front of the sensor while the geometry is reconstructed gradually. The rest of this tutorial assumes the usage of the Kinect sensor because the parameters of the application are set up for the device.</p>
</div>
<div class="section" id="prerequisites">
<h2>Prerequisites</h2>
<ul class="simple">
<li>The object should be small with a size of about 10 to 20 cm. This results in about 10000 points in each frame after the object is segmented. The application is still usable with bigger objects but becomes very slow.</li>
<li>The object must be rigid because the used registration algorithm can&#8217;t align deformable objects.</li>
<li>The object must have prominent geometric features because the texture is not considered during registration. For example, a symmetric bottle can&#8217;t be reconstructed.</li>
<li>The incoming point cloud from the sensor must be organized. This property is needed for the normals estimation, mesh reconstruction and merging.</li>
<li>The color of the object must be different from the color of the user&#8217;s hands. Alternatively it is possible to wear gloves with a different color than the object.</li>
<li>No abrupt movements of the object while scanning.</li>
</ul>
</div>
<div class="section" id="how-it-works">
<h2>How it works</h2>
<p>The application generates an initial surface mesh and gradually integrates new points into a common model. The scanning pipeline consists of several components:</p>
<ul class="simple">
<li>Grabber: Communicates with the device and gives notice when new data is available.</li>
<li>Input data processing:<ul>
<li>Computes normals for the following processing stages.</li>
<li>Creates a <em>foreground</em> mask which stores &#8216;true&#8217; if the input point is within a specified volume of interest (cropping volume). This mask is eroded a few pixels in order to remove border points.</li>
<li>The foreground points are segmented into <em>hand</em> and <em>object</em> regions by applying a threshold to the color in the HSV color space. The hands region is dilated a few pixels in order to reduce the risk of accidentally including hand points into the object cloud.</li>
<li>Only the object points are forwarded to the registration.</li>
</ul>
</li>
<li>Registration: Aligns the processed data cloud to the common model mesh using the Iterative Closest Point (ICP) algorithm. The components are:<ul>
<li>Fitness: Mean squared Euclidean distance of the correspondences after rejection.</li>
<li>Pre-selection: Discards model points that are facing away from the sensor.</li>
<li>Correspondence estimation: Nearest neighbor search using a kd-tree.</li>
<li>Correspondence rejection:<ul>
<li>Discards correspondences with a squared Euclidean distance higher than a threshold. The threshold is initialized with infinity (no rejection in the first iteration) and set to the fitness of the last iteration multiplied by an user defined factor.</li>
<li>Discards correspondences where the angle between their normals is higher than an user defined threshold.</li>
</ul>
</li>
<li>Transformation estimation: Minimization of the point to plane distance with the data cloud as source and model mesh as target.</li>
<li>Convergence criteria:<ul>
<li>Epsilon: Convergence is detected when the <em>change</em> of the fitness between the current and previous iteration becomes smaller than an user defined epsilon value.</li>
</ul>
</li>
<li>Failure criteria:<ul>
<li>Maximum number of iterations exceeded.</li>
<li>Fitness is bigger than an user defined threshold (evaluated at the state of convergence).</li>
<li>Overlap between the model mesh and data cloud is smaller than an user defined threshold (evaluated at the state of convergence).</li>
</ul>
</li>
</ul>
</li>
<li>Integration: Reconstructs an initial model mesh (unorganized) and merges the registered data clouds (organized) with the model.<ul>
<li>Merging is done by searching for the nearest neighbors from the data cloud to the model mesh and averaging out corresponding points if the angle between their normals is smaller than a given threshold. If the squared Euclidean distance is higher than a given squared distance threshold the data points are added to the mesh as new vertices. The organized nature of the data cloud is used to connect the faces.</li>
<li>The outlier rejection is based on the assumption that outliers can&#8217;t be observed from several <em>distinct</em> directions. Therefore each vertex stores a <em>visibility confidence</em> which is the number of unique directions from which it has been recorded. The vertices get a certain amount of time (maximum age) until they have to reach a minimum visibility confidence and else are removed from the mesh again. The vertices store an age which is initialized by zero and increased in each iteration. If the vertex had a correspondence in the current merging step the age is reset to zero. This setup makes sure that vertices that are currently being merged are always kept in the mesh regardless of their visibility confidence. Once the object has been turned around certain vertices can&#8217;t be seen anymore. The age increases until they reach the maximum age when it is decided if they are kept in the mesh or removed.</li>
</ul>
</li>
</ul>
</div>
<div class="section" id="the-application">
<h2>The application</h2>
<p>The following image shows the general layout of the application.</p>
<a class="reference internal image-reference" href="_images/ihs_application_layout.png"><img alt="Application layout" class="align-center" src="_images/ihs_application_layout.png" style="width: 870.4px; height: 561.6px;" /></a>
<p>The main canvas (1) is used for visualization of the data and for showing general information. The viewpoint can be changed with the mouse:</p>
<ul class="simple">
<li>Left button: Rotate</li>
<li>Middle button: Pan</li>
<li>Right button &amp; mouse wheel: Move towards to or away from the pivot of the virtual camera.</li>
</ul>
<p>The various states of the application can be triggered by keyboard shortcuts which are listed in the help (2) or shown in tooltips when moving the mouse over the buttons. Please click into the main canvas to make sure that key press events are processed (the canvas looses focus when parameters are changed in the settings).</p>
<p>The buttons (3) above the main canvas change the current state of the application and allow triggering certain processing steps:</p>
<ul class="simple">
<li>&#8216;Input&#8217;: Shows the input cloud from the device.</li>
<li>&#8216;Processed&#8217;: Shows the cloud after it went through input data processing. The cropping volume is shown as a wireframe box. The points that are removed during color segmentation are drawn blue.</li>
<li>&#8216;Continuous registration&#8217;: Registers and integrates new data to the first acquired scan continuously until it is stopped manually.</li>
<li>&#8216;Single registration&#8217;: Registers and integrates one new frame to the common model and returns to showing the processed input data.</li>
<li>&#8216;Show model&#8217;: Shows the scanned model without further distractions.</li>
<li>&#8216;Clean&#8217;: Removes all vertices that have a low visibility confidence.</li>
<li>&#8216;Reset&#8217;: Deletes the scanned model.</li>
</ul>
<p>The buttons (4) set how the current data is drawn.</p>
<ul class="simple">
<li>&#8216;Reset camera&#8217;: Resets the camera to the viewpoint of the device.</li>
<li>&#8216;Coloring&#8217;: Toggles between several coloring modes:<ul>
<li>Original color of the data.</li>
<li>One color for all points.</li>
<li>Colormap according to the visibility confidence (red = low, green = high).</li>
</ul>
</li>
<li>&#8216;Mesh representation&#8217;: Toggles the visualization type of the mesh:<ul>
<li>Points</li>
<li>Wireframe</li>
<li>Surface</li>
</ul>
</li>
</ul>
<p>The settings of the application are shown in the toolbox on the right (5). The values have been tuned for scanning small objects with the Kinect so most of them don&#8217;t have to be changed. The values that have to be adjusted before scanning are the ones in the &#8216;Input data processing&#8217; tab as it is explained in the next section.</p>
<p>The scanned model can be saved from the menu bar (not shown).</p>
</div>
<div class="section" id="how-to-use-it">
<h2>How to use it</h2>
<p>In the following section I will go through the steps to scan in a model of the &#8216;lion&#8217; object which is about 15 cm high.</p>
<a class="reference internal image-reference" href="_images/ihs_lion_photo.JPG"><img alt="Lion object." class="align-center" src="_images/ihs_lion_photo.JPG" style="width: 150.0px; height: 234.6px;" /></a>
<p>Once the application has connected to the device it shows the incoming data. The first step is to set up the thresholds for the object segmentation:</p>
<ul class="simple">
<li>Press &#8216;2&#8217; to show the processed data.</li>
<li>Go to the &#8216;Input Data Processing&#8217; settings and adjust the values for the cropping volume and the color segmentation as shown in the next image.</li>
<li>The color mask can be inverted if needed.</li>
<li>Keep the &#8216;erode size&#8217; as small as possible. Make the &#8216;dilate size&#8217; just big enough to remove most of the points on the hands.</li>
</ul>
<a class="reference internal image-reference" href="_images/ihs_input_data_processing.png"><img alt="Input data processing with the surface mesh representation." class="align-center" src="_images/ihs_input_data_processing.png" style="width: 870.4px; height: 561.6px;" /></a>
<p>Now start with the continuous registration (press &#8216;3&#8217;). This automatically changes the coloring to a colormap according to the input confidence. The goal is to turn the object around until the whole surface becomes green. For this each point has to be recorded from as many <em>different</em> directions as possible. In the following image the object has been turned about the vertical axis. The newest points in the front have not been recorded by enough directions yet (red, orange, white) while the points on the right side have been scanned in sufficiently (green).</p>
<a class="reference internal image-reference" href="_images/ihs_registration.png"><img alt="Continuous registration with the coloring according to the input confidence." class="align-center" src="_images/ihs_registration.png" style="width: 870.4px; height: 561.6px;" /></a>
<p>Avoid occluding the object by the hands and try to turn the object in such a way that as many geometric features of the shape are shown as possible. For example the lion object has one flat surface at the bottom (blue circle). It is not good to point this side directly towards to the sensor because the almost planar side has very few geometric features resulting in a bad alignment. Therefore it is best to include other sides while scanning as shown in the image. This procedure also helps reducing the error accumulation (loop closure problem).</p>
<a class="reference internal image-reference" href="_images/ihs_geometric_features.png"><img alt="Geometric features." class="align-center" src="_images/ihs_geometric_features.png" style="width: 870.4px; height: 561.6px;" /></a>
<p>After all sides have been scanned the registration can be stopped by pressing &#8216;5&#8217; which shows the current model. Any remaining outliers can be removed by pressing &#8216;6&#8217; (clean) as shown in the next image.</p>
<a class="reference internal image-reference" href="_images/ihs_cleanup.png"><img alt="Geometric features." class="align-center" src="_images/ihs_cleanup.png" style="width: 560.0px; height: 400.0px;" /></a>
<p>The eyes of the lion could not be scanned in because they were filtered out by the color segmentation. To circumvent this problem it is possible to resume the scanning procedure with the color segmentation disabled. Now one has to be very careful to keep the hands out of the cropping volume. This way it is possible to scan in additional parts as shown in the next image.</p>
<a class="reference internal image-reference" href="_images/ihs_color_segmentation_disabled.png"><img alt="Disabled color segmentation." class="align-center" src="_images/ihs_color_segmentation_disabled.png" style="width: 870.4px; height: 561.6px;" /></a>
<p>The following image shows the final model where the eyes have been scanned in as well. However this resulted integrating a few more isolated surface patches into the mesh (light blue). There are still small holes in the mesh which in theory could be closed by the application but this would take a long time.</p>
<a class="reference internal image-reference" href="_images/ihs_lion_model.png"><img alt="Lion model." class="align-center" src="_images/ihs_lion_model.png" style="width: 775.2px; height: 400.0px;" /></a>
<p>The parameters in the &#8216;Registration&#8217; and &#8216;Integration&#8217; settings have not been covered so far. The registration parameters are described in the application&#8217;s help and there is usually no need to make big adjustments. You might want to tweak some of the integration settings:</p>
<ul class="simple">
<li>Increasing the &#8216;maximum squared distance&#8217; results in an increased mesh size for newly integrated points.</li>
<li>Increasing the &#8216;maximum age&#8217; keeps vertices with a low input confidence longer in the mesh (delays the check for the visibility confidence).</li>
<li>Decreasing the &#8216;minimum directions&#8217; (visibility confidence) increases the chance that points are kept in the mesh but this results a bigger noise and more accepted outliers as well.</li>
</ul>
</div>
<div class="section" id="future-work">
<h2>Future work</h2>
<ul class="simple">
<li>Improvement of the speed of the registration. It currently spends a great amount of time during the correspondence estimation (kd-tree). I tried to use different methods but the faster ones are not as accurate as needed.</li>
<li>There is currently no loop detection or loop closure implemented. The error accumulation is reduced by integrating new points into a common model but it is still possible that the borders don&#8217;t match when the object has been fully turned around.</li>
<li>The application tries to reconstruct the final mesh directly while scanning. The current meshing algorithm creates a preliminary surface mesh quickly. However filling all small holes takes a long time. Therefore running a hole filling algorithm every few frames would help speeding up the process. An alternative would be to run a manually triggered surface reconstruction algorithm once the general geometry of the object has been recorded.</li>
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