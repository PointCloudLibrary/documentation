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
    
    <title>Table of contents</title>
    
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
    <link rel="top" title="None" href="#" />
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
            
  <div class="toctree-wrapper compound">
<ul class="simple">
</ul>
</div>
<p>The following links describe a set of basic PCL tutorials. Please note that
their source codes may already be provided as part of the PCL regular releases,
so check there before you start copy &amp; pasting the code. The list of tutorials
below is automatically generated from reST files located in our git repository.</p>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">Before you start reading, please make sure that you go through the higher-level overview documentation at <a class="reference external" href="http://www.pointclouds.org/documentation/">http://www.pointclouds.org/documentation/</a>, under <strong>Getting Started</strong>. Thank you.</p>
</div>
<p>As always, we would be happy to hear your comments and receive your
contributions on any tutorial.</p>
<div class="section" id="table-of-contents">
<h1>Table of contents</h1>
<blockquote>
<div><ul class="simple">
<li><a class="reference internal" href="#basic-usage"><em>Basic Usage</em></a></li>
<li><a class="reference internal" href="#advanced-usage"><em>Advanced Usage</em></a></li>
<li><a class="reference internal" href="#applications-tutorial"><em>Applications</em></a></li>
<li><a class="reference internal" href="#features-tutorial"><em>Features</em></a></li>
<li><a class="reference internal" href="#filtering-tutorial"><em>Filtering</em></a></li>
<li><a class="reference internal" href="#i-o"><em>I/O</em></a></li>
<li><a class="reference internal" href="#keypoints-tutorial"><em>Keypoints</em></a></li>
<li><a class="reference internal" href="#kdtree-tutorial"><em>KdTree</em></a></li>
<li><a class="reference internal" href="#octree-tutorial"><em>Octree</em></a></li>
<li><a class="reference internal" href="#range-images"><em>Range Images</em></a></li>
<li><a class="reference internal" href="#recognition-tutorial"><em>Recognition</em></a></li>
<li><a class="reference internal" href="#registration-tutorial"><em>Registration</em></a></li>
<li><a class="reference internal" href="#sample-consensus"><em>Sample Consensus</em></a></li>
<li><a class="reference internal" href="#segmentation-tutorial"><em>Segmentation</em></a></li>
<li><a class="reference internal" href="#surface-tutorial"><em>Surface</em></a></li>
<li><a class="reference internal" href="#visualization-tutorial"><em>Visualization</em></a></li>
<li><a class="reference internal" href="#gpu"><em>GPU</em></a></li>
</ul>
</div></blockquote>
</div>
<div class="section" id="basic-usage">
<span id="id1"></span><h1>Basic Usage</h1>
<blockquote>
<div><ul>
<li><p class="first"><a class="reference internal" href="walkthrough.php#walkthrough"><em>PCL Walkthrough</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="6%" />
<col width="94%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/pcl_logo.png"><img alt="mi_0" src="_images/pcl_logo.png" style="height: 75px;" /></a></td>
<td><p class="first">Title: <strong>PCL Functionality Walkthrough</strong></p>
<p>Author: <em>Razvan G. Mihalyi</em></p>
<p>Compatibility: &gt; PCL 1.6</p>
<p class="last">Takes the reader through all of the PCL modules and offers basic explanations on their functionalities.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="basic_structures.php#basic-structures"><em>Getting Started / Basic Structures</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="6%" />
<col width="94%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/pcl_logo.png"><img alt="mi_1" src="_images/pcl_logo.png" style="height: 75px;" /></a></td>
<td><p class="first">Title: <strong>Getting Started / Basic Structures</strong></p>
<p>Author: <em>Radu B. Rusu</em></p>
<p>Compatibility: &gt; PCL 1.0</p>
<p class="last">Presents the basic data structures in PCL and discusses their usage with a simple code example.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="using_pcl_pcl_config.php#using-pcl-pcl-config"><em>Using PCL in your own project</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="7%" />
<col width="93%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/pcl_logo.png"><img alt="mi_2" src="_images/pcl_logo.png" style="height: 75px;" /></a></td>
<td><p class="first">Title: <strong>Using PCL in your own project</strong></p>
<p>Author: <em>Nizar Sallem</em></p>
<p>Compatibility: &gt; PCL 1.0</p>
<p class="last">In this tutorial, we will learn how to link your own project to PCL using cmake.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="building_pcl.php#building-pcl"><em>Customizing the PCL build process</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="5%" />
<col width="95%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/pcl_ccmake.png"><img alt="mi_3" src="_images/pcl_ccmake.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Explaining PCL&#8217;s cmake options</strong></p>
<p>Author: <em>Nizar Sallem</em></p>
<p>Compatibility: &gt; PCL 1.0</p>
<p class="last">In this tutorial, we will explain the basic PCL cmake options, and ways to tweak them to fit your project.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="compiling_pcl_dependencies_windows.php#compiling-pcl-dependencies-windows"><em>Building PCL&#8217;s dependencies from source on Windows</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="5%" />
<col width="95%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/windows_logo.png"><img alt="mi_4" src="_images/windows_logo.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Compiling PCL&#8217;s dependencies from source on Windows</strong></p>
<p>Authors: <em>Alessio Placitelli</em> and <em>Mourad Boufarguine</em></p>
<p>Compatibility: &gt; PCL 1.0</p>
<p class="last">In this tutorial, we will explain how to compile PCL&#8217;s 3rd party dependencies from source on Microsoft Windows.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="compiling_pcl_windows.php#compiling-pcl-windows"><em>Compiling PCL from source on Windows</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="8%" />
<col width="93%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/windows_logo.png"><img alt="mi_5" src="_images/windows_logo.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Compiling PCL on Windows</strong></p>
<p>Author: <em>Mourad Boufarguine</em></p>
<p>Compatibility: &gt; PCL 1.0</p>
<p class="last">In this tutorial, we will explain how to compile PCL on Microsoft Windows.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="compiling_pcl_macosx.php#compiling-pcl-macosx"><em>Compiling PCL and its dependencies from MacPorts and source on Mac OS X</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="5%" />
<col width="95%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/macosx_logo.png"><img alt="mi_6" src="_images/macosx_logo.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Compiling PCL and its dependencies from MacPorts and source on Mac OS X</strong></p>
<p>Author: <em>Justin Rosen</em></p>
<p>Compatibility: &gt; PCL 1.0</p>
<p class="last">This tutorial explains how to build the Point Cloud Library <strong>from MacPorts and source</strong> on Mac OS X platforms.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="installing_homebrew.php#installing-homebrew"><em>Installing on Mac OS X using Homebrew</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="4%" />
<col width="96%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/macosx_logo.png"><img alt="mi_7" src="_images/macosx_logo.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Installing on Mac OS X using Homebrew</strong></p>
<p>Author: <em>Geoffrey Biggs</em></p>
<p>Compatibility: &gt; PCL 1.2</p>
<p class="last">This tutorial explains how to install the Point Cloud Library on Mac OS X using Homebrew. Both direct installation and compiling PCL from source are explained.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="using_pcl_with_eclipse.php#using-pcl-with-eclipse"><em>Using PCL with Eclipse</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="8%" />
<col width="92%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/pcl_logo.png"><img alt="mi_8" src="_images/pcl_logo.png" style="height: 75px;" /></a></td>
<td><p class="first">Title: <strong>Using Eclipse as your PCL editor</strong></p>
<p>Author: <em>Koen Buys</em></p>
<p>Compatibility: PCL git master</p>
<p class="last">This tutorial shows you how to get your PCL as a project in Eclipse.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="qt_visualizer.php#qt-visualizer"><em>Create a PCL visualizer in Qt with cmake</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="7%" />
<col width="93%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/qt.png"><img alt="mi_9" src="_images/qt.png" style="height: 128px;" /></a></td>
<td><p class="first">Title: <strong>Create a PCL visualizer in Qt with cmake</strong></p>
<p>Author: <em>Victor Lamoine</em></p>
<p>Compatibility: &gt; PCL 1.5</p>
<p class="last">This tutorial shows you how to create a PCL visualizer within a Qt application.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="matrix_transform.php#matrix-transform"><em>Using a matrix to transform a point cloud</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="9%" />
<col width="91%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/cube.png"><img alt="mi_10" src="_images/cube.png" style="height: 120px;" /></a></td>
<td><p class="first">Title: <strong>Using matrixes to transform a point cloud</strong></p>
<p>Author: <em>Victor Lamoine</em></p>
<p>Compatibility: &gt; PCL 1.5</p>
<p class="last">This tutorial shows you how to transform a point cloud using a matrix.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
</ul>
</div></blockquote>
</div>
<div class="section" id="advanced-usage">
<span id="id2"></span><h1>Advanced Usage</h1>
<blockquote>
<div><ul>
<li><p class="first"><a class="reference internal" href="adding_custom_ptype.php#adding-custom-ptype"><em>Adding your own custom PointT type</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="4%" />
<col width="96%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/pcl_logo.png"><img alt="au_1" src="_images/pcl_logo.png" style="height: 75px;" /></a></td>
<td><p class="first">Title: <strong>Adding your own custom PointT point type</strong></p>
<p>Author: <em>Radu B. Rusu</em></p>
<p>Compatibility: &gt; PCL 0.9, &lt; PCL 2.0</p>
<p class="last">This document explains what templated point types are in PCL, why do they exist, and how to create and use your own <cite>PointT</cite> point type.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="writing_new_classes.php#writing-new-classes"><em>Writing a new PCL class</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="4%" />
<col width="96%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/pcl_logo.png"><img alt="au_2" src="_images/pcl_logo.png" style="height: 75px;" /></a></td>
<td><p class="first">Title: <strong>Writing a new PCL class</strong></p>
<p>Author: <em>Radu B. Rusu, Luca Penasa</em></p>
<p>Compatibility: &gt; PCL 0.9, &lt; PCL 2.0</p>
<p class="last">This short guide is to serve as both a HowTo and a FAQ for writing new PCL classes, either from scratch, or by adapting old code.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
</ul>
</div></blockquote>
</div>
<div class="section" id="features">
<span id="features-tutorial"></span><h1>Features</h1>
<blockquote>
<div><ul>
<li><p class="first"><a class="reference internal" href="how_features_work.php#how-3d-features-work"><em>How 3D Features work in PCL</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="6%" />
<col width="94%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/good_features_small.jpg"><img alt="fe_1" src="_images/good_features_small.jpg" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>How 3D features work</strong></p>
<p>Author: <em>Radu B. Rusu</em></p>
<p>Compatibility: &gt; PCL 1.0</p>
<p class="last">This document presents a basic introduction to the 3D feature estimation methodologies in PCL.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="normal_estimation.php#normal-estimation"><em>Estimating Surface Normals in a PointCloud</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="5%" />
<col width="95%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/normal_estimation.png"><img alt="fe_2" src="_images/normal_estimation.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Estimating Surface Normals in a PointCloud</strong></p>
<p>Author: <em>Radu B. Rusu</em></p>
<p>Compatibility: &gt; PCL 1.0</p>
<p class="last">This tutorial discusses the theoretical and implementation details of the surface normal estimation module in PCL.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="normal_estimation_using_integral_images.php#normal-estimation-using-integral-images"><em>Normal Estimation Using Integral Images</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="5%" />
<col width="95%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/normal_estimation_ii.png"><img alt="fe_3" src="_images/normal_estimation_ii.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Normal Estimation Using Integral Images</strong></p>
<p>Author: <em>Stefan Holzer</em></p>
<p>Compatibility: &gt; PCL 1.0</p>
<p class="last">In this tutorial we will learn how to compute normals for an organized point cloud using integral images.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="pfh_estimation.php#pfh-estimation"><em>Point Feature Histograms (PFH) descriptors</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="4%" />
<col width="96%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/pfh_estimation.png"><img alt="fe_4" src="_images/pfh_estimation.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Point Feature Histograms (PFH) descriptors</strong></p>
<p>Author: <em>Radu B. Rusu</em></p>
<p>Compatibility: &gt; PCL 1.0</p>
<p class="last">This tutorial introduces a family of 3D feature descriptors called PFH (Point Feature Histograms) and discusses their implementation details from PCL&#8217;s perspective.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="fpfh_estimation.php#fpfh-estimation"><em>Fast Point Feature Histograms (FPFH) descriptors</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="4%" />
<col width="96%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/fpfh_estimation.jpg"><img alt="fe_5" src="_images/fpfh_estimation.jpg" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Fast Point Feature Histograms (FPFH) descriptors</strong></p>
<p>Author: <em>Radu B. Rusu</em></p>
<p>Compatibility: &gt; PCL 1.3</p>
<p class="last">This tutorial introduces the FPFH (Fast Point Feature Histograms) 3D descriptor and discusses their implementation details from PCL&#8217;s perspective.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="vfh_estimation.php#vfh-estimation"><em>Estimating VFH signatures for a set of points</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="3%" />
<col width="97%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/vfh_estimation.png"><img alt="fe_6" src="_images/vfh_estimation.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Estimating VFH signatures for a set of points</strong></p>
<p>Author: <em>Radu B. Rusu</em></p>
<p>Compatibility: &gt; PCL 0.8</p>
<p class="last">This document describes the Viewpoint Feature Histogram (VFH) descriptor, a novel representation for point clusters for the problem of Cluster (e.g., Object) Recognition and 6DOF Pose Estimation.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="narf_feature_extraction.php#narf-feature-extraction"><em>How to extract NARF Features from a range image</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="7%" />
<col width="93%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/narf_keypoint_extraction.png"><img alt="fe_7" src="_images/narf_keypoint_extraction.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>How to extract NARF features from a range image</strong></p>
<p>Author: <em>Bastian Steder</em></p>
<p>Compatibility: &gt; 1.3</p>
<p class="last">In this tutorial, we will learn how to extract NARF features from a range image.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="moment_of_inertia.php#moment-of-inertia"><em>Moment of inertia and eccentricity based descriptors</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="4%" />
<col width="96%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/moment_of_inertia.png"><img alt="fe_8" src="_images/moment_of_inertia.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Moment of inertia and eccentricity based descriptors</strong></p>
<p>Author: <em>Sergey Ushakov</em></p>
<p>Compatibility: &gt; PCL 1.7</p>
<p class="last">In this tutorial we will learn how to compute moment of inertia and eccentricity of the cloud. In addition to this we will learn how to extract AABB and OBB.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="rops_feature.php#rops-feature"><em>RoPs (Rotational Projection Statistics) feature</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="9%" />
<col width="91%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/rops_feature.png"><img alt="fe_9" src="_images/rops_feature.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>RoPs (Rotational Projection Statistics) feature</strong></p>
<p>Author: <em>Sergey Ushakov</em></p>
<p>Compatibility: &gt; PCL 1.7</p>
<p class="last">In this tutorial we will learn how to compute RoPS feature.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
</ul>
</div></blockquote>
</div>
<div class="section" id="filtering">
<span id="filtering-tutorial"></span><h1>Filtering</h1>
<blockquote>
<div><ul>
<li><p class="first"><a class="reference internal" href="passthrough.php#passthrough"><em>Filtering a PointCloud using a PassThrough filter</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="4%" />
<col width="96%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/passthrough.png"><img alt="fi_1" src="_images/passthrough.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Filtering a PointCloud using a PassThrough filter</strong></p>
<p>Author: <em>Radu B. Rusu</em></p>
<p>Compatibility: &gt; PCL 1.0</p>
<p class="last">In this tutorial, we will learn how to remove points whose values fall inside/outside a user given interval along a specified dimension.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="voxel_grid.php#voxelgrid"><em>Downsampling a PointCloud using a VoxelGrid filter</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="6%" />
<col width="94%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/voxel_grid.jpg"><img alt="fi_2" src="_images/voxel_grid.jpg" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Downsampling a PointCloud using a VoxelGrid filter</strong></p>
<p>Author: <em>Radu B. Rusu</em></p>
<p>Compatibility: &gt; PCL 1.0</p>
<p class="last">In this tutorial, we will learn how to downsample (i.e., reduce the number of points) a Point Cloud.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="statistical_outlier.php#statistical-outlier-removal"><em>Removing outliers using a StatisticalOutlierRemoval filter</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="5%" />
<col width="95%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/statistical_removal.jpg"><img alt="fi_3" src="_images/statistical_removal.jpg" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Removing sparse outliers using StatisticalOutlierRemoval</strong></p>
<p>Author: <em>Radu B. Rusu</em></p>
<p>Compatibility: &gt; PCL 1.0</p>
<p class="last">In this tutorial, we will learn how to remove sparse outliers from noisy data, using StatisticalRemoval.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="project_inliers.php#project-inliers"><em>Projecting points using a parametric model</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="6%" />
<col width="94%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/project_inliers.png"><img alt="fi_4" src="_images/project_inliers.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Projecting points using a parametric model</strong></p>
<p>Author: <em>Radu B. Rusu</em></p>
<p>Compatibility: &gt; PCL 1.0</p>
<p class="last">In this tutorial, we will learn how to project points to a parametric model (i.e., plane).</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="extract_indices.php#extract-indices"><em>Extracting indices from a PointCloud</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="6%" />
<col width="94%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/extract_indices.jpg"><img alt="fi_5" src="_images/extract_indices.jpg" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Extracting indices from a PointCloud</strong></p>
<p>Author: <em>Radu B. Rusu</em></p>
<p>Compatibility: &gt; PCL 1.0</p>
<p class="last">In this tutorial, we will learn how to extract a set of indices given by a segmentation algorithm.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="remove_outliers.php#remove-outliers"><em>Removing outliers using a Conditional or RadiusOutlier removal</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="5%" />
<col width="95%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/radius_outlier.png"><img alt="fi_6" src="_images/radius_outlier.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Removing outliers using a Conditional or RadiusOutlier removal</strong></p>
<p>Author: <em>Gabe O&#8217;Leary</em></p>
<p>Compatibility: &gt; PCL 1.0</p>
<p class="last">In this tutorial, we will learn how to remove outliers from noisy data, using ConditionalRemoval, RadiusOutlierRemoval.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
</ul>
</div></blockquote>
</div>
<div class="section" id="i-o">
<span id="id3"></span><h1>I/O</h1>
<blockquote>
<div><ul>
<li><p class="first"><a class="reference internal" href="pcd_file_format.php#pcd-file-format"><em>The PCD (Point Cloud Data) file format</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="7%" />
<col width="93%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/PCD_icon.png"><img alt="i_o0" src="_images/PCD_icon.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>The PCD (Point Cloud Data) file format</strong></p>
<p>Author: <em>Radu B. Rusu</em></p>
<p>Compatibility: &gt; PCL 0.9</p>
<p class="last">This document describes the PCD file format, and the way it is used inside PCL.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="reading_pcd.php#reading-pcd"><em>Reading Point Cloud data from PCD files</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="8%" />
<col width="93%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/read_pcd.jpg"><img alt="i_o1" src="_images/read_pcd.jpg" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Reading Point Cloud data from PCD files</strong></p>
<p>Author: <em>Radu B. Rusu</em></p>
<p>Compatibility: &gt; PCL 1.0</p>
<p class="last">In this tutorial, we will learn how to read a Point Cloud from a PCD file.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="writing_pcd.php#writing-pcd"><em>Writing Point Cloud data to PCD files</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="8%" />
<col width="92%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/write_pcd.jpg"><img alt="i_o2" src="_images/write_pcd.jpg" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Writing Point Cloud data to PCD files</strong></p>
<p>Author: <em>Radu B. Rusu</em></p>
<p>Compatibility: &gt; PCL 1.0</p>
<p class="last">In this tutorial, we will learn how to write a Point Cloud to a PCD file.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="concatenate_clouds.php#concatenate-clouds"><em>Concatenate the points of two Point Clouds</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="3%" />
<col width="97%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/concatenate_fields.jpg"><img alt="i_o3" src="_images/concatenate_fields.jpg" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Concatenate the fields or points of two Point Clouds</strong></p>
<p>Author: <em>Gabe O&#8217;Leary / Radu B. Rusu</em></p>
<p>Compatibility: &gt; PCL 1.0</p>
<p class="last">In this tutorial, we will learn how to concatenate both the fields and the point data of two Point Clouds.  When concatenating fields, one PointClouds contains only <em>XYZ</em> data, and the other contains <em>Surface Normal</em> information.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="openni_grabber.php#openni-grabber"><em>The OpenNI Grabber Framework in PCL</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="7%" />
<col width="93%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/openni_grabber.png"><img alt="i_o4" src="_images/openni_grabber.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Grabbing Point Clouds from an OpenNI camera</strong></p>
<p>Author: <em>Nico Blodow</em></p>
<p>Compatibility: &gt; PCL 1.0</p>
<p class="last">In this tutorial, we will learn how to acquire point cloud data from an OpenNI camera.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="hdl_grabber.php#hdl-grabber"><em>The Velodyne High Definition LiDAR (HDL) Grabber</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="7%" />
<col width="93%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/hdl_grabber.png"><img alt="i_o5" src="_images/hdl_grabber.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Grabbing Point Clouds from a Velodyne High Definition LiDAR (HDL)</strong></p>
<p>Author: <em>Keven Ring</em></p>
<p>Compatibility: &gt;= PCL 1.7</p>
<p class="last">In this tutorial, we will learn how to acquire point cloud data from a Velodyne HDL.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="dinast_grabber.php#dinast-grabber"><em>The PCL Dinast Grabber Framework</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="7%" />
<col width="93%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/dinast_cyclopes.png"><img alt="i_o6" src="_images/dinast_cyclopes.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Grabbing Point Clouds from Dinast Cameras</strong></p>
<p>Author: <em>Marco A. Gutierrez</em></p>
<p>Compatibility: &gt;= PCL 1.7</p>
<p class="last">In this tutorial, we will learn how to acquire point cloud data from a Dinast camera.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
</ul>
</div></blockquote>
</div>
<div class="section" id="keypoints">
<span id="keypoints-tutorial"></span><h1>Keypoints</h1>
<blockquote>
<div><ul>
<li><p class="first"><a class="reference internal" href="narf_keypoint_extraction.php#narf-keypoint-extraction"><em>How to extract NARF keypoint from a range image</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="7%" />
<col width="93%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/narf_keypoint_extraction.png"><img alt="kp_1" src="_images/narf_keypoint_extraction.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>How to extract NARF keypoints from a range image</strong></p>
<p>Author: <em>Bastian Steder</em></p>
<p>Compatibility: &gt; 1.3</p>
<p class="last">In this tutorial, we will learn how to extract NARF keypoints from a range image.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
</ul>
</div></blockquote>
</div>
<div class="section" id="kdtree">
<span id="kdtree-tutorial"></span><h1>KdTree</h1>
<blockquote>
<div><ul>
<li><p class="first"><a class="reference internal" href="kdtree_search.php#kdtree-search"><em>How to use a KdTree to search</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="6%" />
<col width="94%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/kdtree_search.png"><img alt="kd_1" src="_images/kdtree_search.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>KdTree Search</strong></p>
<p>Author: <em>Gabe O&#8217;Leary</em></p>
<p>Compatibility: &gt; PCL 1.0</p>
<p class="last">In this tutorial, we will learn how to search using the nearest neighbor method for k-d trees</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
</ul>
</div></blockquote>
</div>
<div class="section" id="octree">
<span id="octree-tutorial"></span><h1>Octree</h1>
<blockquote>
<div><ul>
<li><p class="first"><a class="reference internal" href="compression.php#octree-compression"><em>Point Cloud Compression</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="6%" />
<col width="94%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/compression_tutorial.png"><img alt="oc_1" src="_images/compression_tutorial.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Point cloud compression</strong></p>
<p>Author: <em>Julius Kammerl</em></p>
<p>Compatibility: &gt; PCL 1.0</p>
<p class="last">In this tutorial, we will learn how to compress a single point cloud and streams of point clouds.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="octree.php#octree-search"><em>Spatial Partitioning and Search Operations with Octrees</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="5%" />
<col width="95%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/octree_img.png"><img alt="oc_2" src="_images/octree_img.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Octrees for spatial partitioning and neighbor search</strong></p>
<p>Author: <em>Julius Kammerl</em></p>
<p>Compatibility: &gt; PCL 1.0</p>
<p class="last">In this tutorial, we will learn how to use octrees for spatial partitioning and nearest neighbor search.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="octree_change.php#octree-change-detection"><em>Spatial change detection on unorganized point cloud data</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="6%" />
<col width="94%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/changedetectionThumb.png"><img alt="oc_3" src="_images/changedetectionThumb.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Spatial change detection on unorganized point cloud data</strong></p>
<p>Author: <em>Julius Kammerl</em></p>
<p>Compatibility: &gt; PCL 1.0</p>
<p class="last">In this tutorial, we will learn how to use octrees for detecting spatial changes within point clouds.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
</ul>
</div></blockquote>
</div>
<div class="section" id="range-images">
<span id="id4"></span><h1>Range Images</h1>
<blockquote>
<div><ul>
<li><p class="first"><a class="reference internal" href="range_image_creation.php#range-image-creation"><em>How to create a range image from a point cloud</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="6%" />
<col width="94%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/range_image_visualization.png"><img alt="ri_1" src="_images/range_image_visualization.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Creating Range Images from Point Clouds</strong></p>
<p>Author: <em>Bastian Steder</em></p>
<p>Compatibility: &gt; PCL 1.0</p>
<p class="last">This tutorial demonstrates how to create a range image from a point cloud and a given sensor position.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="range_image_border_extraction.php#range-image-border-extraction"><em>How to extract borders from range images</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="5%" />
<col width="95%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/range_image_border_points.png"><img alt="ri_2" src="_images/range_image_border_points.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Extracting borders from Range Images</strong></p>
<p>Author: <em>Bastian Steder</em></p>
<p>Compatibility: &gt; PCL 1.3</p>
<p class="last">This tutorial demonstrates how to extract borders (traversals from foreground to background) from a range image.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
</ul>
</div></blockquote>
</div>
<div class="section" id="recognition">
<span id="recognition-tutorial"></span><h1>Recognition</h1>
<blockquote>
<div><ul>
<li><p class="first"><a class="reference internal" href="correspondence_grouping.php#correspondence-grouping"><em>3D Object Recognition based on Correspondence Grouping</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="5%" />
<col width="95%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/correspondence_grouping.jpg"><img alt="rc_1" src="_images/correspondence_grouping.jpg" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>The PCL Recognition API</strong></p>
<p>Author: <em>Tommaso Cavallari, Federico Tombari</em></p>
<p>Compatibility: &gt; PCL 1.6</p>
<p class="last">This tutorial aims at explaining how to perform 3D Object Recognition based on the pcl_recognition module.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="implicit_shape_model.php#implicit-shape-model"><em>Implicit Shape Model</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="5%" />
<col width="95%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/implicit_shape_model.png"><img alt="rc_2" src="_images/implicit_shape_model.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Implicit Shape Model</strong></p>
<p>Author: <em>Sergey Ushakov</em></p>
<p>Compatibility: &gt; PCL 1.7</p>
<p class="last">In this tutorial we will learn how the Implicit Shape Model algorithm works and how to use it for finding objects centers.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
</ul>
</div></blockquote>
</div>
<div class="section" id="registration">
<span id="registration-tutorial"></span><h1>Registration</h1>
<blockquote>
<div><ul>
<li><p class="first"><a class="reference internal" href="registration_api.php#registration-api"><em>The PCL Registration API</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="3%" />
<col width="97%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/registration_api.png"><img alt="re_1" src="_images/registration_api.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>The PCL Registration API</strong></p>
<p>Author: <em>Dirk Holz, Radu B. Rusu, Jochen Sprickerhof</em></p>
<p>Compatibility: &gt; PCL 1.5</p>
<p class="last">In this document, we describe the point cloud registration API and its modules: the estimation and rejection of point correspondences, and the estimation of rigid transformations.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="iterative_closest_point.php#iterative-closest-point"><em>How to use iterative closest point</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="4%" />
<col width="96%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/iterative_closest_point.gif"><img alt="re_2" src="_images/iterative_closest_point.gif" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>How to use iterative closest point algorithm</strong></p>
<p>Author: <em>Gabe O&#8217;Leary</em></p>
<p>Compatibility: &gt; PCL 1.0</p>
<p class="last">This tutorial gives an example of how to use the iterative closest point algorithm to see if one PointCloud is just a rigid transformation of another PointCloud.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="pairwise_incremental_registration.php#pairwise-incremental-registration"><em>How to incrementally register pairs of clouds</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="4%" />
<col width="96%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/iterative_closest_point.gif"><img alt="re_3" src="_images/iterative_closest_point.gif" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>How to incrementally register pairs of clouds</strong></p>
<p>Author: <em>Raphael Favier</em></p>
<p>Compatibility: &gt; PCL 1.4</p>
<p class="last">This document demonstrates using the Iterative Closest Point algorithm in order to incrementally register a series of point clouds two by two.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="interactive_icp.php#interactive-icp"><em>Interactive Iterative Closest Point</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="8%" />
<col width="92%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/monkey.png"><img alt="re_7" src="_images/monkey.png" style="height: 120px;" /></a></td>
<td><p class="first">Title: <strong>Interactive ICP</strong></p>
<p>Author: <em>Victor Lamoine</em></p>
<p>Compatibility: &gt; PCL 1.5</p>
<p class="last">This tutorial will teach you how to build an interactive ICP program</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="normal_distributions_transform.php#normal-distributions-transform"><em>How to use Normal Distributions Transform</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="5%" />
<col width="95%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/normal_distributions_transform.gif"><img alt="re_4" src="_images/normal_distributions_transform.gif" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>How to use the Normal Distributions Transform algorithm</strong></p>
<p>Author: <em>Brian Okorn</em></p>
<p>Compatibility: &gt; PCL 1.6</p>
<p class="last">This document demonstrates using the Normal Distributions Transform algorithm to register two large point clouds.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="in_hand_scanner.php#in-hand-scanner"><em>In-hand scanner for small objects</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="5%" />
<col width="95%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/ihs_lion_model.png"><img alt="re_5" src="_images/ihs_lion_model.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>How to use the In-hand scanner for small objects</strong></p>
<p>Author: <em>Martin Saelzle</em></p>
<p>Compatibility: &gt;= PCL 1.7</p>
<p class="last">This document shows how to use the In-hand scanner applications to obtain colored models of small objects with RGB-D cameras.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="alignment_prerejective.php#alignment-prerejective"><em>Robust pose estimation of rigid objects</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="5%" />
<col width="95%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/alignment_prerejective_1.png"><img alt="re_6" src="_images/alignment_prerejective_1.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Robust pose estimation of rigid objects</strong></p>
<p>Author: <em>Anders Glent Buch</em></p>
<p>Compatibility: &gt;= PCL 1.7</p>
<p class="last">In this tutorial, we show how to find the alignment pose of a rigid object in a scene with clutter and occlusions.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
</ul>
</div></blockquote>
</div>
<div class="section" id="sample-consensus">
<span id="id5"></span><h1>Sample Consensus</h1>
<blockquote>
<div><ul>
<li><p class="first"><a class="reference internal" href="random_sample_consensus.php#random-sample-consensus"><em>How to use Random Sample Consensus model</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="5%" />
<col width="95%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/ransac_outliers_plane.png"><img alt="sc_1" src="_images/ransac_outliers_plane.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>How to use Random Sample Consensus model</strong></p>
<p>Author: <em>Gabe O&#8217;Leary</em></p>
<p>Compatibility: &gt; PCL 1.0</p>
<p class="last">In this tutorial we learn how to use a RandomSampleConsensus with a plane model to obtain the cloud fitting to this model.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
</ul>
</div></blockquote>
</div>
<div class="section" id="segmentation">
<span id="segmentation-tutorial"></span><h1>Segmentation</h1>
<blockquote>
<div><ul>
<li><p class="first"><a class="reference internal" href="planar_segmentation.php#planar-segmentation"><em>Plane model segmentation</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="6%" />
<col width="94%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/planar_segmentation.jpg"><img alt="se_1" src="_images/planar_segmentation.jpg" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Plane model segmentation</strong></p>
<p>Author: <em>Radu B. Rusu</em></p>
<p>Compatibility: &gt; PCL 1.3</p>
<p class="last">In this tutorial, we will learn how to segment arbitrary plane models from a given point cloud dataset.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="cylinder_segmentation.php#cylinder-segmentation"><em>Cylinder model segmentation</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="5%" />
<col width="95%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/cylinder_segmentation.jpg"><img alt="se_2" src="_images/cylinder_segmentation.jpg" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Cylinder model segmentation</strong></p>
<p>Author: <em>Radu B. Rusu</em></p>
<p>Compatibility: &gt; PCL 1.3</p>
<p class="last">In this tutorial, we will learn how to segment arbitrary cylindrical models from a given point cloud dataset.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="cluster_extraction.php#cluster-extraction"><em>Euclidean Cluster Extraction</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="5%" />
<col width="95%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/cluster_extraction.jpg"><img alt="se_3" src="_images/cluster_extraction.jpg" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Euclidean Cluster Extraction</strong></p>
<p>Author: <em>Serkan Tuerker</em></p>
<p>Compatibility: &gt; PCL 1.3</p>
<p class="last">In this tutorial we will learn how to extract Euclidean clusters with the <tt class="docutils literal"><span class="pre">pcl::EuclideanClusterExtraction</span></tt> class.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="region_growing_segmentation.php#region-growing-segmentation"><em>Region growing segmentation</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="7%" />
<col width="93%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/region_growing_segmentation.jpg"><img alt="se_4" src="_images/region_growing_segmentation.jpg" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Region Growing Segmentation</strong></p>
<p>Author: <em>Sergey Ushakov</em></p>
<p>Compatibility: &gt;= PCL 1.7</p>
<p class="last">In this tutorial we will learn how to use region growing segmentation algorithm.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="region_growing_rgb_segmentation.php#region-growing-rgb-segmentation"><em>Color-based region growing segmentation</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="6%" />
<col width="94%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/region_growing_rgb_segmentation.jpg"><img alt="se_5" src="_images/region_growing_rgb_segmentation.jpg" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Color-based Region Growing Segmentation</strong></p>
<p>Author: <em>Sergey Ushakov</em></p>
<p>Compatibility: &gt;= PCL 1.7</p>
<p class="last">In this tutorial we will learn how to use color-based region growing segmentation algorithm.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="min_cut_segmentation.php#min-cut-segmentation"><em>Min-Cut Based Segmentation</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="7%" />
<col width="93%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/min_cut_segmentation.jpg"><img alt="se_6" src="_images/min_cut_segmentation.jpg" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Min-Cut Based Segmentation</strong></p>
<p>Author: <em>Sergey Ushakov</em></p>
<p>Compatibility: &gt;= PCL 1.7</p>
<p class="last">In this tutorial we will learn how to use min-cut based segmentation algorithm.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="conditional_euclidean_clustering.php#conditional-euclidean-clustering"><em>Conditional Euclidean Clustering</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="5%" />
<col width="95%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/conditional_euclidean_clustering.jpg"><img alt="se_7" src="_images/conditional_euclidean_clustering.jpg" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Conditional Euclidean Clustering</strong></p>
<p>Author: <em>Frits Florentinus</em></p>
<p>Compatibility: &gt;= PCL 1.7</p>
<p class="last">This tutorial describes how to use the Conditional Euclidean Clustering class in PCL:
A segmentation algorithm that clusters points based on Euclidean distance and a user-customizable condition that needs to hold.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="don_segmentation.php#don-segmentation"><em>Difference of Normals Based Segmentation</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="6%" />
<col width="94%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/don_segmentation.png"><img alt="se_8" src="_images/don_segmentation.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Difference of Normals Based Segmentation</strong></p>
<p>Author: <em>Yani Ioannou</em></p>
<p>Compatibility: &gt;= PCL 1.7</p>
<p class="last">In this tutorial we will learn how to use the difference of normals feature for segmentation.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="supervoxel_clustering.php#supervoxel-clustering"><em>Clustering of Pointclouds into Supervoxels - Theoretical primer</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="6%" />
<col width="94%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/supervoxel_clustering_small.png"><img alt="se_9" src="_images/supervoxel_clustering_small.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Supervoxel Clustering</strong></p>
<p>Author: <em>Jeremie Papon</em></p>
<p>Compatibility: &gt;= PCL 1.7</p>
<p class="last">In this tutorial, we show to break a pointcloud into the mid-level supervoxel representation.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="progressive_morphological_filtering.php#progressive-morphological-filtering"><em>Identifying ground returns using ProgressiveMorphologicalFilter segmentation</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="7%" />
<col width="93%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/progressive_morphological_filter.png"><img alt="se_10" src="_images/progressive_morphological_filter.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Progressive Morphological Filtering</strong></p>
<p>Author: <em>Brad Chambers</em></p>
<p>Compatibility: &gt;= PCL 1.8</p>
<p class="last">In this tutorial, we show how to segment a point cloud into ground and non-ground returns.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
</ul>
</div></blockquote>
</div>
<div class="section" id="surface">
<span id="surface-tutorial"></span><h1>Surface</h1>
<blockquote>
<div><ul>
<li><p class="first"><a class="reference internal" href="resampling.php#moving-least-squares"><em>Smoothing and normal estimation based on polynomial reconstruction</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="4%" />
<col width="96%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/resampling.jpg"><img alt="su_1" src="_images/resampling.jpg" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Smoothing and normal estimation based on polynomial reconstruction</strong></p>
<p>Author: <em>Zoltan-Csaba Marton, Alexandru E. Ichim</em></p>
<p>Compatibility: &gt; PCL 1.6</p>
<p class="last">In this tutorial, we will learn how to construct and run a Moving Least Squares (MLS) algorithm to obtain smoothed XYZ coordinates and normals.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="hull_2d.php#hull-2d"><em>Construct a concave or convex hull polygon for a plane model</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="4%" />
<col width="96%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/convex_hull_2d.jpg"><img alt="su_2" src="_images/convex_hull_2d.jpg" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Construct a concave or convex hull polygon for a plane model</strong></p>
<p>Author: <em>Gabe O&#8217;Leary, Radu B. Rusu</em></p>
<p>Compatibility: &gt; PCL 1.0</p>
<p class="last">In this tutorial we will learn how to calculate a simple 2D concave or convex hull polygon for a set of points supported by a plane.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="greedy_projection.php#greedy-triangulation"><em>Fast triangulation of unordered point clouds</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="3%" />
<col width="97%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/greedy_triangulation.png"><img alt="su_3" src="_images/greedy_triangulation.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Fast triangulation of unordered point clouds</strong></p>
<p>Author: <em>Zoltan-Csaba Marton</em></p>
<p>Compatibility: &gt; PCL 1.0</p>
<p class="last">In this tutorial we will learn how to run a greedy triangulation algorithm on a PointCloud with normals to obtain a triangle mesh based on projections of the local neighborhood.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="bspline_fitting.php#bspline-fitting"><em>Fitting trimmed B-splines to unordered point clouds</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="5%" />
<col width="95%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/bspline_bunny.png"><img alt="su_4" src="_images/bspline_bunny.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Fitting trimmed B-splines to unordered point clouds</strong></p>
<p>Author: <em>Thomas Mörwald</em></p>
<p>Compatibility: &gt; PCL 1.7</p>
<p class="last">In this tutorial we will learn how to reconstruct a smooth surface from an unordered point-cloud by fitting trimmed B-splines.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
</ul>
</div></blockquote>
</div>
<div class="section" id="visualization">
<span id="visualization-tutorial"></span><h1>Visualization</h1>
<blockquote>
<div><ul>
<li><p class="first"><a class="reference internal" href="cloud_viewer.php#cloud-viewer"><em>The CloudViewer</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="8%" />
<col width="92%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/cloud_viewer.jpg"><img alt="vi_1" src="_images/cloud_viewer.jpg" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Visualizing Point Clouds</strong></p>
<p>Author: <em>Ethan Rublee</em></p>
<p>Compatibility: &gt; PCL 1.0</p>
<p class="last">This tutorial demonstrates how to use the pcl visualization tools.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="range_image_visualization.php#range-image-visualization"><em>How to visualize a range image</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="7%" />
<col width="93%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/range_image_visualization.png"><img alt="vi_2" src="_images/range_image_visualization.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Visualizing Range Images</strong></p>
<p>Author: <em>Bastian Steder</em></p>
<p>Compatibility: &gt; PCL 1.3</p>
<p class="last">This tutorial demonstrates how to use the pcl visualization tools for range images.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="pcl_visualizer.php#pcl-visualizer"><em>PCLVisualizer</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="5%" />
<col width="95%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/pcl_visualizer_viewports.png"><img alt="vi_3" src="_images/pcl_visualizer_viewports.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>PCLVisualizer</strong></p>
<p>Author: <em>Geoffrey Biggs</em></p>
<p>Compatibility: &gt; PCL 1.3</p>
<p class="last">This tutorial demonstrates how to use the PCLVisualizer class for powerful visualisation of point clouds and related data.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="pcl_plotter.php#pcl-plotter"><em>PCLPlotter</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="4%" />
<col width="96%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/pcl_plotter_comprational.png"><img alt="vi_4" src="_images/pcl_plotter_comprational.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>PCLPlotter</strong></p>
<p>Author: <em>Kripasindhu Sarkar</em></p>
<p>Compatibility: &gt; PCL 1.7</p>
<p class="last">This tutorial demonstrates how to use the PCLPlotter class for powerful visualisation of plots, charts and histograms of raw data and explicit functions.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
</ul>
</div></blockquote>
</div>
<div class="section" id="applications">
<span id="applications-tutorial"></span><h1>Applications</h1>
<blockquote>
<div><ul>
<li><p class="first"><a class="reference internal" href="template_alignment.php#template-alignment"><em>Aligning object templates to a point cloud</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="3%" />
<col width="97%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/template_alignment_1.jpg"><img alt="ap_1" src="_images/template_alignment_1.jpg" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Aligning object templates to a point cloud</strong></p>
<p>Author: <em>Michael Dixon</em></p>
<p>Compatibility: &gt; PCL 1.3</p>
<p class="last">This tutorial gives an example of how some of the tools covered in the previous tutorials can be combined to solve a higher level problem &#8212; aligning a previously captured model of an object to some newly captured data.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="vfh_recognition.php#vfh-recognition"><em>Cluster Recognition and 6DOF Pose Estimation using VFH descriptors</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="4%" />
<col width="96%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/vfh_recognition.jpg"><img alt="ap_2" src="_images/vfh_recognition.jpg" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Cluster Recognition and 6DOF Pose Estimation using VFH descriptors</strong></p>
<p>Author: <em>Radu B. Rusu</em></p>
<p>Compatibility: &gt; PCL 0.8</p>
<p class="last">In this tutorial we show how the Viewpoint Feature Histogram (VFH) descriptor can be used to recognize similar clusters in terms of their geometry.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="mobile_streaming.php#mobile-streaming"><em>Point Cloud Streaming to Mobile Devices with Real-time Visualization</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="4%" />
<col width="96%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/mobile_streaming_1.jpg"><img alt="ap_3" src="_images/mobile_streaming_1.jpg" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Point Cloud Streaming to Mobile Devices with Real-time Visualization</strong></p>
<p>Author: <em>Pat Marion</em></p>
<p>Compatibility: &gt; PCL 1.3</p>
<p class="last">This tutorial describes how to send point cloud data over the network from a desktop server to a client running on a mobile device.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="ground_based_rgbd_people_detection.php#ground-based-rgbd-people-detection"><em>Detecting people on a ground plane with RGB-D data</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="6%" />
<col width="94%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/Index_photo.jpg"><img alt="ap_5" src="_images/Index_photo.jpg" style="height: 120px;" /></a></td>
<td><p class="first">Title: <strong>Detecting people on a ground plane with RGB-D data</strong></p>
<p>Author: <em>Matteo Munaro</em></p>
<p>Compatibility: &gt;= PCL 1.7</p>
<p class="last">This tutorial presents a method for detecting people on a ground plane with RGB-D data.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
</ul>
</div></blockquote>
</div>
<div class="section" id="gpu">
<span id="id6"></span><h1>GPU</h1>
<blockquote>
<div><blockquote>
<div><ul>
<li><p class="first"><a class="reference internal" href="gpu_install.php#gpu-install"><em>Configuring your PC to use your Nvidia GPU with PCL</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="8%" />
<col width="92%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/PCD_icon.png"><img alt="gp_1" src="_images/PCD_icon.png" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>GPU Installation</strong></p>
<p>Author: <em>Koen Buys</em></p>
<p>Compatibility: PCL git master</p>
<p class="last">This tutorial explains how to configure PCL to use with a Nvidia GPU</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="using_kinfu_large_scale.php#using-kinfu-large-scale"><em>Using Kinfu Large Scale to generate a textured mesh</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="3%" />
<col width="97%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/using_kinfu_large_scale.jpg"><img alt="ap_4" src="_images/using_kinfu_large_scale.jpg" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>Using Kinfu Large Scale to generate a textured mesh</strong></p>
<p>Author: <em>Francisco Heredia and Raphael Favier</em></p>
<p>Compatibility: PCL git master</p>
<p class="last">This tutorial demonstrates how to use KinFu Large Scale to produce a mesh from a room, and apply texture information in post-processing for a more appealing visual result.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
<li><p class="first"><a class="reference internal" href="gpu_people.php#gpu-people"><em>Detecting people and their poses using PointCloud Library</em></a></p>
<blockquote>
<div><table border="1" class="docutils">
<colgroup>
<col width="9%" />
<col width="91%" />
</colgroup>
<tbody valign="top">
<tr class="row-odd"><td><a class="reference internal" href="_images/c2_100.jpg"><img alt="gp_2" src="_images/c2_100.jpg" style="height: 100px;" /></a></td>
<td><p class="first">Title: <strong>People Detection</strong></p>
<p>Author: <em>Koen Buys</em></p>
<p>Compatibility: PCL git master</p>
<p class="last">This tutorial presents a method for people and pose detection.</p>
</td>
</tr>
</tbody>
</table>
</div></blockquote>
</li>
</ul>
</div></blockquote>
</div></blockquote>
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