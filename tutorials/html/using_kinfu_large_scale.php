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
    
    <title>Using Kinfu Large Scale to generate a textured mesh</title>
    
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
            
  <div class="section" id="using-kinfu-large-scale-to-generate-a-textured-mesh">
<span id="using-kinfu-large-scale"></span><h1>Using Kinfu Large Scale to generate a textured mesh</h1>
<p>This tutorial demonstrates how to use KinFu Large Scale to produce a mesh (in meters) from a room, and apply texture information in post-processing for a more appealing visual result. The first part of this tutorial shows how to obtain the TSDF cloud from KinFu Large Scale. The second part shows how to convert the TSDF cloud into a uniform mesh. The third part shows how to texture the obtained mesh using the RGB images and poses we obtained from KinFu Large Scale.</p>
</div>
<div class="section" id="part-1-running-pcl-kinfu-largescale-to-obtain-a-tsdf-cloud">
<h1>Part 1: Running pcl_kinfu_largeScale to obtain a TSDF cloud</h1>
<p><em>TSDF Cloud</em></p>
<p>This section describes the TSDF Cloud, which is the expected output of KinFu Large Scale. A TSDF cloud looks like the one in the following video.</p>
<blockquote>
<div><iframe width="420" height="315" src="http://www.youtube.com/embed/AjjSZufyprU" frameborder="0" allowfullscreen></iframe></div></blockquote>
<p>You may be wondering: <em>&#8220;What is the difference between a TSDF cloud and a normal point cloud?&#8221;</em> Well, a TSDF cloud <em>is</em> a point cloud. However, the TSDF cloud makes use of how the data is stored within GPU at KinFu runtime.</p>
<blockquote>
<div><a class="reference internal image-reference" href="_images/11.jpg"><img alt="_images/11.jpg" class="align-center" src="_images/11.jpg" style="width: 696pt; height: 326pt;" /></a>
<p><em>Figure 1: The cube is subdivided into a set of Voxels. These voxels are equal in size. The default size in meters for the cube is 3 meters per axis. The default voxel size is 512 per axis. Both the number of voxels and the size in meters give the amount of detail of our model.</em></p>
</div></blockquote>
<p>As you may already know, the way in which the TSDF volume is stored in GPU is a voxel grid. KinFu subdivides the physical space of the cube (e.g. 3 meters) into a voxel grid with a certain number of voxels per axis (say, 512 voxels per axis). The size in meters of the cube and the number of voxels give us the resolution of our cube. The quality of the model is proportional to these two parameters. However, modifying them affects directly the memory footprint for our TSDF volume in GPU. Further information on these properties can be found in the relevant papers.</p>
<p>At the time of data extraction, the grid is traversed from front to back, and the TSDF values are checked for each voxel. In the figure below, you may notice that the values range from -1 to 1.</p>
<blockquote>
<div><a class="reference internal image-reference" href="_images/12.jpg"><img alt="_images/12.jpg" class="align-center" src="_images/12.jpg" style="width: 400pt; height: 350pt;" /></a>
<p><em>Figure 2: A representation of the TSDF Volume grid in the GPU. Each element in the grid represents a voxel, and the value inside it represents the TSDF value. The TSDF value is the distance to the nearest isosurface. The TSDF has a positive value whenever we are &#8220;in front&#8221; of the surface, whereas it has a negative value when inside the isosurface. At the time of extraction, we avoid extracting the voxels with a value of 1, since they represent empty space, and are therefore of no use to our model.</em></p>
</div></blockquote>
<p>Since we want to minimize the required bandwidth between GPU and CPU, we will only extract the voxels with a TSDF value in the range [-1, 0.98]. We avoid extracting voxels with a value of 1 because they represent empty space. In this way we ensure that we only extract those voxels that are close to the isosurface. The TSDF cloud is not in meters. The X,Y,Z coordinates for each of the extracted points correspond to the voxel indices with respect to the world model.</p>
<p>As mentioned above, the TSDF cloud is a section of the TSDF volume grid; which is why the points are equally-spaced and uniformly-distributed. This can be observed when we zoom in the point cloud.</p>
<p><em>Running pcl_kinfu_largeScale</em></p>
<p>Finally, we are ready to start KinFu Large Scale. After building the git master, we will call the application:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./bin/pcl_kinfu_largeScale -r -et
</pre></div>
</div>
<p>The <em>-r</em> parameter enables registration, which is used for texture extraction. In particular, it allows us to extract the correct focal length. The <em>-et</em> parameter enables the texture extraction. By enabling this option, we will extract RGB images at the same time that we are scanning. All the RGB snapshots are saved in the KinFuSnapshots folder. Each RGB image will be saved with its corresponding camera pose. It is suggested to empty this directory before starting the scan, in this way we avoid using textures that do not correspond to our latest scan.</p>
<p>The video below shows the process of scanning a large area. Notice the smooth movements at the time of scanning. Furthermore, notice how a complex object (e.g. chair) is kept within sight at the time of shifting so that tracking does not get lost.</p>
<blockquote>
<div><ul class="simple">
<li>The shifting can be triggered by rotation or translation.</li>
<li>Every time we shift out part of the cube,  four main things happen: 1)We save the data in the slice that is shifted out and send it to the world model, which is stored in CPU. 2) We clear that slice to allow for new data to be added. 3) We shift the cube&#8217;s origin. 4) We retrieve existing data (if any) from the world model and load it to the TSDF volume. This is only present when we return to areas that we previously scanned.</li>
<li>Whenever we are satisfied with the area that we have scanned, we press the &#8220;L&#8221; key to let KinFu know that we are ready to perform the exit routine. However, the routine is not executed until we shift again.</li>
</ul>
</div></blockquote>
<p>What the exit routine will do is to get all the information regarding our model, comprise it in a point cloud and save it to disk as <em>world.pcd</em> The PCD file is saved in the same directory from where we run KinFu Large Scale.</p>
<p>Since we used the <em>-et</em> option, you will also find a folder called KinFuSnapshots, which contains all the RGB images and its corresponding poses for this scan. The following video demonstrates the scanning process and the generated output:</p>
<blockquote>
<div><iframe width="420" height="315" src="http://www.youtube.com/embed/rF1N-EEIJao" frameborder="0" allowfullscreen></iframe></div></blockquote>
<p>The next part of this tutorial will demonstrate how to get a mesh from the TSDF cloud.</p>
</div>
<div class="section" id="part-2-running-pcl-kinfu-largescale-mesh-output-to-convert-the-tsdf-cloud-into-a-mesh">
<h1>Part 2: Running pcl_kinfu_largeScale_mesh_output to convert the TSDF cloud into a mesh</h1>
<p>This section describes how to convert the TSDF Cloud, which is the expected output of KinFu Large Scale, into a mesh. For this purpose we will use the meshing application in KinFu Large Scale. The input for this application is the world model as a PCD file. The output is a set of meshes, since the world model is processed as a set of cubes.</p>
<p>The reason why we load the world model in cubes is because we have the limitation of memory in the GPU. A point of improvement for the meshing application could be to return the complete mesh instead of a set of meshes. Contributions welcome!</p>
<p>After we obtain a set of meshes, we process them in Meshlab in order to merge them as a single mesh. At this point it is important to mention that we need to save the mesh as a ply file without binary encoding.</p>
<p>The mesh is also simplified using quadric edge decimation. The reason for doing this is to reduce the time it takes to perform the UV mapping in the next step. The UV mapping is done for each face in the mesh. Therefore, by reducing the number of faces we reduce the time it takes to generate the texture.</p>
<p>We run this application with the command:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./bin/pcl_kinfu_largeScale_mesh_output world.pcd
</pre></div>
</div>
<p>where <em>world.pcd</em> is the world model we obtained from KinFu Large Scale. The following video shows the process of creating, merging, and simplifying the meshes into a single mesh which we will use for texturing.</p>
<blockquote>
<div><iframe width="420" height="315" src="http://www.youtube.com/embed/XMJ-ikSZAOE" frameborder="0" allowfullscreen></iframe></div></blockquote>
<p>The next part of this tutorial will demonstrate how to generate the texture for the mesh we have just created.</p>
</div>
<div class="section" id="part-3-running-pcl-kinfu-largescale-texture-output-to-generate-the-texture">
<h1>Part 3: Running pcl_kinfu_largeScale_texture_output to generate the texture</h1>
<p>This section describes how to generate the textures for the mesh we created in the previous step. The input for this application is the merged mesh, as well as the RGB captures and poses we saved during the scanning in part 1. The RGB captures and poses should be in the KinFuSnapshots folder. We select the most representative snapshots for the sake of time. Each snapshot must have its corresponding camera pose in a text file in the same folder.</p>
<p>The generated PLY mesh must be in the same folder as the snapshots and camera poses. The output will be generated as an OBJ file with its corresponding MTL file. The former contains data about the mesh, whereas the latter contains information about the texture. Unfortunately at this point some of the generated textures may seen patched, this is based on how the RGB camera in the Kinect adapts to light. A potential area of improvement could be to equalize the color tones in the images. Contributions welcome!</p>
<p>In order to run the texturing application, we use the following command:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./bin/pcl_kinfu_largeScale_texture_output path/to/merged_mesh.ply
</pre></div>
</div>
<p>The following video shows the process in detail. It also shows the final output for this tutorial.</p>
<blockquote>
<div><iframe width="420" height="315" src="http://www.youtube.com/embed/7S7Jj-4cKHs" frameborder="0" allowfullscreen></iframe></div></blockquote>
</div>
<div class="section" id="output">
<h1>Output</h1>
<p>The viewer below displays a sample of the output obtained after the entire pipeline. The mesh was decimated, and the faces were removed so that only the points remained. So, the output mesh was converted from mesh (.ply) to point cloud (.pcd) to show it in this tutorial. The vertex count is ~900k points.</p>
<iframe src="http://pointclouds.org/assets/viewer/pcl_viewer.html?load=https://raw.github.com/PointCloudLibrary/data/master/tutorials/kinfu_large_scale/Tutorial_Cloud_Couch_bin_compressed.pcd&scale=0.004&psize=1" align="center" width="600" height="400" marginwidth="0" marginheight="0" frameborder='no' allowfullscreen mozallowfullscreen webkitallowfullscreen style="max-width: 100%;"></iframe><p>To further demonstrate the capabilities of KinFu Large Scale, we made another example with a room.</p>
<iframe src="http://pointclouds.org/assets/viewer/pcl_viewer.html?load=https://raw.github.com/PointCloudLibrary/data/master/tutorials/kinfu_large_scale/using_kinfu_large_scale_output.pcd&scale=0.004&psize=1" align="center" width="600" height="400" marginwidth="0" marginheight="0" frameborder='no' allowfullscreen mozallowfullscreen webkitallowfullscreen style="max-width: 100%;"></iframe></div>
<div class="section" id="general-recommendations">
<h1>General Recommendations</h1>
<p>There is a set of recommendations that we want to mention regarding the use of KinFu Large Scale. These recommendations are listed below:</p>
<blockquote>
<div><ol class="arabic simple">
<li><strong>Scan scenes with enough details for ICP:</strong> It is a known fact that ICP does not perform well in scenes with few details, or where there are a lot of co-planer surfaces. In other words, if the only thing you have is a wall and floor, most probably the tracking will not perform well.</li>
<li><strong>Frame rate is less than original KinFu:</strong> The code in Kinfu largescale is experimental. There are still many areas in which the performance can be optimized to provide a faster execution. In our tests, the obtained frame rate is around 20 fps. We are using a GTX480 and 4GB of RAM. The decrease in frame rate is mainly because of two things. First, that the code has not yet been completely optimized. Second, that additional operations are taking place in the frame processing loop as a result of the large scale implementation.</li>
<li><strong>Scan smoothly:</strong> Since there are more things happening per frame, KinFu Large Scale may not respond as fast as the original KinFu. Data is exchanged between GPU and CPU especially at the time of shifting. Performing smooth movements, in particular at the time of shifting, decreases the risk of losing the camera pose tracking. Be patient and you will get good results.</li>
</ol>
</div></blockquote>
</div>
<div class="section" id="related-executables">
<h1>Related Executables</h1>
<p>There are three executables related to this tutorial:</p>
<blockquote>
<div><ul class="simple">
<li><strong>pcl_kinfu_largeScale:</strong> In charge of obtaining the scan of the room. Its functionality is almost the same as KinFu, except that it includes the capability of shifting the cube that is being scanned to allow for large area 3D reconstruction. The output from this application is the world reconstructed model as a TSDF cloud. The concept of TSDF cloud will be explained better below. Another output from this application is a set of RGB screenshots and their corresponding camera poses.</li>
<li><strong>pcl_kinfu_largeScale_mesh_output:</strong> This application is in charge of generating a set of meshes from the extracted TSDF world cloud. The TSDF world model is processed as cubes of points and generates a mesh for each of these cubes.</li>
<li>As an additional processing step, the current state of the implementation requires that the output meshes are merged in the software of your preference. In other words, the output of the meshing application is given as a set of mesh cubes. This tutorial has been done using with Meshlab (<em>merge visible layers</em> function in Meshlab). Since the following step is performed on a per-face basis, it is also optional to decimate the mesh in order to decrease the time it takes to generate the texture.</li>
<li><strong>pcl_kinfu_largeScale_texture_output:</strong> After the meshes are generated and merged into one, this application is in charge of using the RGB screenshots and their corresponding camera poses taken during the scan to perform UV mapping in order to reconstruct the texture of the model.</li>
</ul>
</div></blockquote>
</div>
<div class="section" id="conclusion">
<h1>Conclusion</h1>
<p>In this tutorial we have shown the pipeline from scanning to final texturing using KinFu Large Scale. The - <em>experimental</em> - code is available in the master branch of PCL.</p>
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