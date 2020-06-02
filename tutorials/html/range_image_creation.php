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
    
    <title>How to create a range image from a point cloud</title>
    
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
            
  <div class="section" id="how-to-create-a-range-image-from-a-point-cloud">
<span id="range-image-creation"></span><h1>How to create a range image from a point cloud</h1>
<p>This tutorial demonstrates how to create a range image from a point cloud and a given sensor position. The code creates an example point cloud of a rectangle floating in front of the observer.</p>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>First, create a file called, let&#8217;s say, <tt class="docutils literal"><span class="pre">range_image_creation.cpp</span></tt> in your favorite
editor, and place the following code inside it:</p>
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
29
30
31
32
33
34</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;pcl/range_image/range_image.h&gt;</span>

<span class="kt">int</span> <span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span> <span class="p">{</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">pointCloud</span><span class="p">;</span>
  
  <span class="c1">// Generate the data</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">float</span> <span class="n">y</span><span class="o">=-</span><span class="mf">0.5f</span><span class="p">;</span> <span class="n">y</span><span class="o">&lt;=</span><span class="mf">0.5f</span><span class="p">;</span> <span class="n">y</span><span class="o">+=</span><span class="mf">0.01f</span><span class="p">)</span> <span class="p">{</span>
    <span class="k">for</span> <span class="p">(</span><span class="kt">float</span> <span class="n">z</span><span class="o">=-</span><span class="mf">0.5f</span><span class="p">;</span> <span class="n">z</span><span class="o">&lt;=</span><span class="mf">0.5f</span><span class="p">;</span> <span class="n">z</span><span class="o">+=</span><span class="mf">0.01f</span><span class="p">)</span> <span class="p">{</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">point</span><span class="p">;</span>
      <span class="n">point</span><span class="p">.</span><span class="n">x</span> <span class="o">=</span> <span class="mf">2.0f</span> <span class="o">-</span> <span class="n">y</span><span class="p">;</span>
      <span class="n">point</span><span class="p">.</span><span class="n">y</span> <span class="o">=</span> <span class="n">y</span><span class="p">;</span>
      <span class="n">point</span><span class="p">.</span><span class="n">z</span> <span class="o">=</span> <span class="n">z</span><span class="p">;</span>
      <span class="n">pointCloud</span><span class="p">.</span><span class="n">points</span><span class="p">.</span><span class="n">push_back</span><span class="p">(</span><span class="n">point</span><span class="p">);</span>
    <span class="p">}</span>
  <span class="p">}</span>
  <span class="n">pointCloud</span><span class="p">.</span><span class="n">width</span> <span class="o">=</span> <span class="p">(</span><span class="kt">uint32_t</span><span class="p">)</span> <span class="n">pointCloud</span><span class="p">.</span><span class="n">points</span><span class="p">.</span><span class="n">size</span><span class="p">();</span>
  <span class="n">pointCloud</span><span class="p">.</span><span class="n">height</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
  
  <span class="c1">// We now want to create a range image from the above point cloud, with a 1deg angular resolution</span>
  <span class="kt">float</span> <span class="n">angularResolution</span> <span class="o">=</span> <span class="p">(</span><span class="kt">float</span><span class="p">)</span> <span class="p">(</span>  <span class="mf">1.0f</span> <span class="o">*</span> <span class="p">(</span><span class="n">M_PI</span><span class="o">/</span><span class="mf">180.0f</span><span class="p">));</span>  <span class="c1">//   1.0 degree in radians</span>
  <span class="kt">float</span> <span class="n">maxAngleWidth</span>     <span class="o">=</span> <span class="p">(</span><span class="kt">float</span><span class="p">)</span> <span class="p">(</span><span class="mf">360.0f</span> <span class="o">*</span> <span class="p">(</span><span class="n">M_PI</span><span class="o">/</span><span class="mf">180.0f</span><span class="p">));</span>  <span class="c1">// 360.0 degree in radians</span>
  <span class="kt">float</span> <span class="n">maxAngleHeight</span>    <span class="o">=</span> <span class="p">(</span><span class="kt">float</span><span class="p">)</span> <span class="p">(</span><span class="mf">180.0f</span> <span class="o">*</span> <span class="p">(</span><span class="n">M_PI</span><span class="o">/</span><span class="mf">180.0f</span><span class="p">));</span>  <span class="c1">// 180.0 degree in radians</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Affine3f</span> <span class="n">sensorPose</span> <span class="o">=</span> <span class="p">(</span><span class="n">Eigen</span><span class="o">::</span><span class="n">Affine3f</span><span class="p">)</span><span class="n">Eigen</span><span class="o">::</span><span class="n">Translation3f</span><span class="p">(</span><span class="mf">0.0f</span><span class="p">,</span> <span class="mf">0.0f</span><span class="p">,</span> <span class="mf">0.0f</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">RangeImage</span><span class="o">::</span><span class="n">CoordinateFrame</span> <span class="n">coordinate_frame</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">RangeImage</span><span class="o">::</span><span class="n">CAMERA_FRAME</span><span class="p">;</span>
  <span class="kt">float</span> <span class="n">noiseLevel</span><span class="o">=</span><span class="mf">0.00</span><span class="p">;</span>
  <span class="kt">float</span> <span class="n">minRange</span> <span class="o">=</span> <span class="mf">0.0f</span><span class="p">;</span>
  <span class="kt">int</span> <span class="n">borderSize</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
  
  <span class="n">pcl</span><span class="o">::</span><span class="n">RangeImage</span> <span class="n">rangeImage</span><span class="p">;</span>
  <span class="n">rangeImage</span><span class="p">.</span><span class="n">createFromPointCloud</span><span class="p">(</span><span class="n">pointCloud</span><span class="p">,</span> <span class="n">angularResolution</span><span class="p">,</span> <span class="n">maxAngleWidth</span><span class="p">,</span> <span class="n">maxAngleHeight</span><span class="p">,</span>
                                  <span class="n">sensorPose</span><span class="p">,</span> <span class="n">coordinate_frame</span><span class="p">,</span> <span class="n">noiseLevel</span><span class="p">,</span> <span class="n">minRange</span><span class="p">,</span> <span class="n">borderSize</span><span class="p">);</span>
  
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="n">rangeImage</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="explanation">
<h1>Explanation</h1>
<p>Lets look at this in parts:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="cp">#include &lt;pcl/range_image/range_image.h&gt;</span>

<span class="kt">int</span> <span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span> <span class="p">{</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">pointCloud</span><span class="p">;</span>
  
  <span class="c1">// Generate the data</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">float</span> <span class="n">y</span><span class="o">=-</span><span class="mf">0.5f</span><span class="p">;</span> <span class="n">y</span><span class="o">&lt;=</span><span class="mf">0.5f</span><span class="p">;</span> <span class="n">y</span><span class="o">+=</span><span class="mf">0.01f</span><span class="p">)</span> <span class="p">{</span>
    <span class="k">for</span> <span class="p">(</span><span class="kt">float</span> <span class="n">z</span><span class="o">=-</span><span class="mf">0.5f</span><span class="p">;</span> <span class="n">z</span><span class="o">&lt;=</span><span class="mf">0.5f</span><span class="p">;</span> <span class="n">z</span><span class="o">+=</span><span class="mf">0.01f</span><span class="p">)</span> <span class="p">{</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">point</span><span class="p">;</span>
      <span class="n">point</span><span class="p">.</span><span class="n">x</span> <span class="o">=</span> <span class="mf">2.0f</span> <span class="o">-</span> <span class="n">y</span><span class="p">;</span>
      <span class="n">point</span><span class="p">.</span><span class="n">y</span> <span class="o">=</span> <span class="n">y</span><span class="p">;</span>
      <span class="n">point</span><span class="p">.</span><span class="n">z</span> <span class="o">=</span> <span class="n">z</span><span class="p">;</span>
      <span class="n">pointCloud</span><span class="p">.</span><span class="n">points</span><span class="p">.</span><span class="n">push_back</span><span class="p">(</span><span class="n">point</span><span class="p">);</span>
    <span class="p">}</span>
  <span class="p">}</span>
  <span class="n">pointCloud</span><span class="p">.</span><span class="n">width</span> <span class="o">=</span> <span class="p">(</span><span class="kt">uint32_t</span><span class="p">)</span> <span class="n">pointCloud</span><span class="p">.</span><span class="n">points</span><span class="p">.</span><span class="n">size</span><span class="p">();</span>
  <span class="n">pointCloud</span><span class="p">.</span><span class="n">height</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
</pre></div>
</div>
<p>This includes the necessary range image header, starts the main and generates a point cloud that represents a rectangle.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="kt">float</span> <span class="n">angularResolution</span> <span class="o">=</span> <span class="p">(</span><span class="kt">float</span><span class="p">)</span> <span class="p">(</span>  <span class="mf">1.0f</span> <span class="o">*</span> <span class="p">(</span><span class="n">M_PI</span><span class="o">/</span><span class="mf">180.0f</span><span class="p">));</span>  <span class="c1">//   1.0 degree in radians</span>
  <span class="kt">float</span> <span class="n">maxAngleWidth</span>     <span class="o">=</span> <span class="p">(</span><span class="kt">float</span><span class="p">)</span> <span class="p">(</span><span class="mf">360.0f</span> <span class="o">*</span> <span class="p">(</span><span class="n">M_PI</span><span class="o">/</span><span class="mf">180.0f</span><span class="p">));</span>  <span class="c1">// 360.0 degree in radians</span>
  <span class="kt">float</span> <span class="n">maxAngleHeight</span>    <span class="o">=</span> <span class="p">(</span><span class="kt">float</span><span class="p">)</span> <span class="p">(</span><span class="mf">180.0f</span> <span class="o">*</span> <span class="p">(</span><span class="n">M_PI</span><span class="o">/</span><span class="mf">180.0f</span><span class="p">));</span>  <span class="c1">// 180.0 degree in radians</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Affine3f</span> <span class="n">sensorPose</span> <span class="o">=</span> <span class="p">(</span><span class="n">Eigen</span><span class="o">::</span><span class="n">Affine3f</span><span class="p">)</span><span class="n">Eigen</span><span class="o">::</span><span class="n">Translation3f</span><span class="p">(</span><span class="mf">0.0f</span><span class="p">,</span> <span class="mf">0.0f</span><span class="p">,</span> <span class="mf">0.0f</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">RangeImage</span><span class="o">::</span><span class="n">CoordinateFrame</span> <span class="n">coordinate_frame</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">RangeImage</span><span class="o">::</span><span class="n">CAMERA_FRAME</span><span class="p">;</span>
  <span class="kt">float</span> <span class="n">noiseLevel</span><span class="o">=</span><span class="mf">0.00</span><span class="p">;</span>
  <span class="kt">float</span> <span class="n">minRange</span> <span class="o">=</span> <span class="mf">0.0f</span><span class="p">;</span>
  <span class="kt">int</span> <span class="n">borderSize</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
</pre></div>
</div>
<p>This part defines the parameters for the range image we want to create.</p>
<p>The angular resolution is supposed to be 1 degree, meaning the beams represented by neighboring pixels differ by one degree.</p>
<p>maxAngleWidth=360 and maxAngleHeight=180 mean that the range sensor we are simulating has a complete 360 degree view of the surrounding. You can always use this setting, since the range image will be cropped to only the areas where something was observed automatically. Yet you can save some computation by reducing the values. E.g. for a laser scanner with a 180 degree view facing forward, where no points behind the sensor can be observed, maxAngleWidth=180 is enough.</p>
<p>sensorPose defines the 6DOF position of the virtual sensor as the origin with roll=pitch=yaw=0.</p>
<p>coordinate_frame=CAMERA_FRAME tells the system that x is facing right, y downwards and the z axis is forward. An alternative would be LASER_FRAME, with x facing forward, y to the left and z upwards.</p>
<p>For noiseLevel=0 the range image is created using a normal z-buffer. Yet if you want to average over points falling in the same cell you can use a higher value. 0.05 would mean, that all point with a maximum distance of 5cm to the closest point are used to calculate the range.</p>
<p>If minRange is greater 0 all points that are closer will be ignored.</p>
<p>borderSize greater 0 will leave a border of unobserved points around the image when cropping it.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">RangeImage</span> <span class="n">rangeImage</span><span class="p">;</span>
  <span class="n">rangeImage</span><span class="p">.</span><span class="n">createFromPointCloud</span><span class="p">(</span><span class="n">pointCloud</span><span class="p">,</span> <span class="n">angularResolution</span><span class="p">,</span> <span class="n">maxAngleWidth</span><span class="p">,</span> <span class="n">maxAngleHeight</span><span class="p">,</span>
                                  <span class="n">sensorPose</span><span class="p">,</span> <span class="n">coordinate_frame</span><span class="p">,</span> <span class="n">noiseLevel</span><span class="p">,</span> <span class="n">minRange</span><span class="p">,</span> <span class="n">borderSize</span><span class="p">);</span>
  
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="n">rangeImage</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
</pre></div>
</div>
<p>The remaining code creates the range image from the point cloud with the given paramters and outputs some information on the terminal.</p>
<p>The range image is derived from the PointCloud class and its points have the members x,y,z and range. There are three kinds of points. Valid points have a real range greater zero. Unobserved points have x=y=z=NAN and range=-INFINITY. Far range points have x=y=z=NAN and range=INFINITY.</p>
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
12</pre></div></td><td class="code"><div class="highlight"><pre><span class="nb">cmake_minimum_required</span><span class="p">(</span><span class="s">VERSION</span> <span class="s">2.6</span> <span class="s">FATAL_ERROR</span><span class="p">)</span>

<span class="nb">project</span><span class="p">(</span><span class="s">range_image_creation</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.2</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">range_image_creation</span> <span class="s">range_image_creation.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">range_image_creation</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it. Simply do:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./range_image_creation
</pre></div>
</div>
<p>You should see the following:</p>
<div class="highlight-python"><div class="highlight"><pre>range image of size 42x36 with angular resolution 1deg/pixel and 1512 points
</pre></div>
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