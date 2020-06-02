<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation - Point Cloud Library (PCL)</title>
</head>

<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8" />
    <title>In-hand scanner for small objects &#8212; PCL 0.0 documentation</title>
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
            
  <div class="section" id="in-hand-scanner-for-small-objects">
<span id="in-hand-scanner"></span><h1>In-hand scanner for small objects</h1>
<div class="section" id="introduction">
<h2>Introduction</h2>
<p>The purpose of the in-hand scanner application is to obtain a 3D model from a small object. The user is turning the object around in front of the sensor while the geometry is reconstructed gradually. The rest of this tutorial assumes the usage of the Kinect sensor because the parameters of the application are set up for the device.</p>
</div>
<div class="section" id="prerequisites">
<h2>Prerequisites</h2>
<ul class="simple">
<li><p>The object should be small with a size of about 10 to 20 cm. This results in about 10000 points in each frame after the object is segmented. The application is still usable with bigger objects but becomes very slow.</p></li>
<li><p>The object must be rigid because the used registration algorithm can’t align deformable objects.</p></li>
<li><p>The object must have prominent geometric features because the texture is not considered during registration. For example, a symmetric bottle can’t be reconstructed.</p></li>
<li><p>The incoming point cloud from the sensor must be organized. This property is needed for the normals estimation, mesh reconstruction and merging.</p></li>
<li><p>The color of the object must be different from the color of the user’s hands. Alternatively it is possible to wear gloves with a different color than the object.</p></li>
<li><p>No abrupt movements of the object while scanning.</p></li>
</ul>
</div>
<div class="section" id="how-it-works">
<h2>How it works</h2>
<p>The application generates an initial surface mesh and gradually integrates new points into a common model. The scanning pipeline consists of several components:</p>
<ul class="simple">
<li><p>Grabber: Communicates with the device and gives notice when new data is available.</p></li>
<li><p>Input data processing:</p>
<ul>
<li><p>Computes normals for the following processing stages.</p></li>
<li><p>Creates a <em>foreground</em> mask which stores ‘true’ if the input point is within a specified volume of interest (cropping volume). This mask is eroded a few pixels in order to remove border points.</p></li>
<li><p>The foreground points are segmented into <em>hand</em> and <em>object</em> regions by applying a threshold to the color in the HSV color space. The hands region is dilated a few pixels in order to reduce the risk of accidentally including hand points into the object cloud.</p></li>
<li><p>Only the object points are forwarded to the registration.</p></li>
</ul>
</li>
<li><p>Registration: Aligns the processed data cloud to the common model mesh using the Iterative Closest Point (ICP) algorithm. The components are:</p>
<ul>
<li><p>Fitness: Mean squared Euclidean distance of the correspondences after rejection.</p></li>
<li><p>Pre-selection: Discards model points that are facing away from the sensor.</p></li>
<li><p>Correspondence estimation: Nearest neighbor search using a kd-tree.</p></li>
<li><p>Correspondence rejection:</p>
<ul>
<li><p>Discards correspondences with a squared Euclidean distance higher than a threshold. The threshold is initialized with infinity (no rejection in the first iteration) and set to the fitness of the last iteration multiplied by an user defined factor.</p></li>
<li><p>Discards correspondences where the angle between their normals is higher than an user defined threshold.</p></li>
</ul>
</li>
<li><p>Transformation estimation: Minimization of the point to plane distance with the data cloud as source and model mesh as target.</p></li>
<li><p>Convergence criteria:</p>
<ul>
<li><p>Epsilon: Convergence is detected when the <em>change</em> of the fitness between the current and previous iteration becomes smaller than an user defined epsilon value.</p></li>
</ul>
</li>
<li><p>Failure criteria:</p>
<ul>
<li><p>Maximum number of iterations exceeded.</p></li>
<li><p>Fitness is bigger than an user defined threshold (evaluated at the state of convergence).</p></li>
<li><p>Overlap between the model mesh and data cloud is smaller than an user defined threshold (evaluated at the state of convergence).</p></li>
</ul>
</li>
</ul>
</li>
<li><p>Integration: Reconstructs an initial model mesh (unorganized) and merges the registered data clouds (organized) with the model.</p>
<ul>
<li><p>Merging is done by searching for the nearest neighbors from the data cloud to the model mesh and averaging out corresponding points if the angle between their normals is smaller than a given threshold. If the squared Euclidean distance is higher than a given squared distance threshold the data points are added to the mesh as new vertices. The organized nature of the data cloud is used to connect the faces.</p></li>
<li><p>The outlier rejection is based on the assumption that outliers can’t be observed from several <em>distinct</em> directions. Therefore each vertex stores a <em>visibility confidence</em> which is the number of unique directions from which it has been recorded. The vertices get a certain amount of time (maximum age) until they have to reach a minimum visibility confidence and else are removed from the mesh again. The vertices store an age which is initialized by zero and increased in each iteration. If the vertex had a correspondence in the current merging step the age is reset to zero. This setup makes sure that vertices that are currently being merged are always kept in the mesh regardless of their visibility confidence. Once the object has been turned around certain vertices can’t be seen anymore. The age increases until they reach the maximum age when it is decided if they are kept in the mesh or removed.</p></li>
</ul>
</li>
</ul>
</div>
<div class="section" id="the-application">
<h2>The application</h2>
<p>The following image shows the general layout of the application.</p>
<a class="reference internal image-reference" href="_images/ihs_application_layout.png"><img alt="Application layout" class="align-center" src="_images/ihs_application_layout.png" style="width: 870.4000000000001px; height: 561.6px;" /></a>
<p>The main canvas (1) is used for visualization of the data and for showing general information. The viewpoint can be changed with the mouse:</p>
<ul class="simple">
<li><p>Left button: Rotate</p></li>
<li><p>Middle button: Pan</p></li>
<li><p>Right button &amp; mouse wheel: Move towards to or away from the pivot of the virtual camera.</p></li>
</ul>
<p>The various states of the application can be triggered by keyboard shortcuts which are listed in the help (2) or shown in tooltips when moving the mouse over the buttons. Please click into the main canvas to make sure that key press events are processed (the canvas looses focus when parameters are changed in the settings).</p>
<p>The buttons (3) above the main canvas change the current state of the application and allow triggering certain processing steps:</p>
<ul class="simple">
<li><p>‘Input’: Shows the input cloud from the device.</p></li>
<li><p>‘Processed’: Shows the cloud after it went through input data processing. The cropping volume is shown as a wireframe box. The points that are removed during color segmentation are drawn blue.</p></li>
<li><p>‘Continuous registration’: Registers and integrates new data to the first acquired scan continuously until it is stopped manually.</p></li>
<li><p>‘Single registration’: Registers and integrates one new frame to the common model and returns to showing the processed input data.</p></li>
<li><p>‘Show model’: Shows the scanned model without further distractions.</p></li>
<li><p>‘Clean’: Removes all vertices that have a low visibility confidence.</p></li>
<li><p>‘Reset’: Deletes the scanned model.</p></li>
</ul>
<p>The buttons (4) set how the current data is drawn.</p>
<ul class="simple">
<li><p>‘Reset camera’: Resets the camera to the viewpoint of the device.</p></li>
<li><p>‘Coloring’: Toggles between several coloring modes:</p>
<ul>
<li><p>Original color of the data.</p></li>
<li><p>One color for all points.</p></li>
<li><p>Colormap according to the visibility confidence (red = low, green = high).</p></li>
</ul>
</li>
<li><p>‘Mesh representation’: Toggles the visualization type of the mesh:</p>
<ul>
<li><p>Points</p></li>
<li><p>Wireframe</p></li>
<li><p>Surface</p></li>
</ul>
</li>
</ul>
<p>The settings of the application are shown in the toolbox on the right (5). The values have been tuned for scanning small objects with the Kinect so most of them don’t have to be changed. The values that have to be adjusted before scanning are the ones in the ‘Input data processing’ tab as it is explained in the next section.</p>
<p>The scanned model can be saved from the menu bar (not shown).</p>
</div>
<div class="section" id="how-to-use-it">
<h2>How to use it</h2>
<p>In the following section I will go through the steps to scan in a model of the ‘lion’ object which is about 15 cm high.</p>
<a class="reference internal image-reference" href="_images/ihs_lion_photo.jpg"><img alt="Lion object." class="align-center" src="_images/ihs_lion_photo.jpg" style="width: 150.0px; height: 234.6px;" /></a>
<p>Once the application has connected to the device it shows the incoming data. The first step is to set up the thresholds for the object segmentation:</p>
<ul class="simple">
<li><p>Press ‘2’ to show the processed data.</p></li>
<li><p>Go to the ‘Input Data Processing’ settings and adjust the values for the cropping volume and the color segmentation as shown in the next image.</p></li>
<li><p>The color mask can be inverted if needed.</p></li>
<li><p>Keep the ‘erode size’ as small as possible. Make the ‘dilate size’ just big enough to remove most of the points on the hands.</p></li>
</ul>
<a class="reference internal image-reference" href="_images/ihs_input_data_processing.png"><img alt="Input data processing with the surface mesh representation." class="align-center" src="_images/ihs_input_data_processing.png" style="width: 870.4000000000001px; height: 561.6px;" /></a>
<p>Now start with the continuous registration (press ‘3’). This automatically changes the coloring to a colormap according to the input confidence. The goal is to turn the object around until the whole surface becomes green. For this each point has to be recorded from as many <em>different</em> directions as possible. In the following image the object has been turned about the vertical axis. The newest points in the front have not been recorded by enough directions yet (red, orange, white) while the points on the right side have been scanned in sufficiently (green).</p>
<a class="reference internal image-reference" href="_images/ihs_registration.png"><img alt="Continuous registration with the coloring according to the input confidence." class="align-center" src="_images/ihs_registration.png" style="width: 870.4000000000001px; height: 561.6px;" /></a>
<p>Avoid occluding the object by the hands and try to turn the object in such a way that as many geometric features of the shape are shown as possible. For example the lion object has one flat surface at the bottom (blue circle). It is not good to point this side directly towards to the sensor because the almost planar side has very few geometric features resulting in a bad alignment. Therefore it is best to include other sides while scanning as shown in the image. This procedure also helps reducing the error accumulation (loop closure problem).</p>
<a class="reference internal image-reference" href="_images/ihs_geometric_features.png"><img alt="Geometric features." class="align-center" src="_images/ihs_geometric_features.png" style="width: 870.4000000000001px; height: 561.6px;" /></a>
<p>After all sides have been scanned the registration can be stopped by pressing ‘5’ which shows the current model. Any remaining outliers can be removed by pressing ‘6’ (clean) as shown in the next image.</p>
<a class="reference internal image-reference" href="_images/ihs_cleanup.png"><img alt="Geometric features." class="align-center" src="_images/ihs_cleanup.png" style="width: 560.0px; height: 400.0px;" /></a>
<p>The eyes of the lion could not be scanned in because they were filtered out by the color segmentation. To circumvent this problem it is possible to resume the scanning procedure with the color segmentation disabled. Now one has to be very careful to keep the hands out of the cropping volume. This way it is possible to scan in additional parts as shown in the next image.</p>
<a class="reference internal image-reference" href="_images/ihs_color_segmentation_disabled.png"><img alt="Disabled color segmentation." class="align-center" src="_images/ihs_color_segmentation_disabled.png" style="width: 870.4000000000001px; height: 561.6px;" /></a>
<p>The following image shows the final model where the eyes have been scanned in as well. However this resulted integrating a few more isolated surface patches into the mesh (light blue). There are still small holes in the mesh which in theory could be closed by the application but this would take a long time.</p>
<a class="reference internal image-reference" href="_images/ihs_lion_model.png"><img alt="Lion model." class="align-center" src="_images/ihs_lion_model.png" style="width: 775.2px; height: 400.0px;" /></a>
<p>The parameters in the ‘Registration’ and ‘Integration’ settings have not been covered so far. The registration parameters are described in the application’s help and there is usually no need to make big adjustments. You might want to tweak some of the integration settings:</p>
<ul class="simple">
<li><p>Increasing the ‘maximum squared distance’ results in an increased mesh size for newly integrated points.</p></li>
<li><p>Increasing the ‘maximum age’ keeps vertices with a low input confidence longer in the mesh (delays the check for the visibility confidence).</p></li>
<li><p>Decreasing the ‘minimum directions’ (visibility confidence) increases the chance that points are kept in the mesh but this results a bigger noise and more accepted outliers as well.</p></li>
</ul>
</div>
<div class="section" id="future-work">
<h2>Future work</h2>
<ul class="simple">
<li><p>Improvement of the speed of the registration. It currently spends a great amount of time during the correspondence estimation (kd-tree). I tried to use different methods but the faster ones are not as accurate as needed.</p></li>
<li><p>There is currently no loop detection or loop closure implemented. The error accumulation is reduced by integrating new points into a common model but it is still possible that the borders don’t match when the object has been fully turned around.</p></li>
<li><p>The application tries to reconstruct the final mesh directly while scanning. The current meshing algorithm creates a preliminary surface mesh quickly. However filling all small holes takes a long time. Therefore running a hole filling algorithm every few frames would help speeding up the process. An alternative would be to run a manually triggered surface reconstruction algorithm once the general geometry of the object has been recorded.</p></li>
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