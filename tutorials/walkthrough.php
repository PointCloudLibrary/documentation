<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation - Point Cloud Library (PCL)</title>
</head>

<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8" />
    <title>PCL Walkthrough &#8212; PCL 0.0 documentation</title>
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
            
  <div class="section" id="pcl-walkthrough">
<span id="walkthrough"></span><h1>PCL Walkthrough</h1>
<p>This tutorials will walk you through the components of your PCL installation, providing short descriptions of the modules, indicating where they are located and also listing the interaction between different components.</p>
<div class="line-block">
<div class="line"><br /></div>
</div>
</div>
<div class="section" id="overview">
<span id="top"></span><h1>Overview</h1>
<p>PCL is split in a number of modular libraries. The most important set of released PCL modules is shown below:</p>
<table class="docutils align-default">
<colgroup>
<col style="width: 33%" />
<col style="width: 33%" />
<col style="width: 33%" />
</colgroup>
<tbody>
<tr class="row-odd"><td><p><a class="reference internal" href="#filters">Filters</a></p></td>
<td><p><a class="reference internal" href="#features">Features</a></p></td>
<td><p><a class="reference internal" href="#keypoints">Keypoints</a></p></td>
</tr>
<tr class="row-even"><td><p><img alt="filters_small" src="_images/filters_small.jpg" /></p></td>
<td><p><img alt="features_small" src="_images/features_small.jpg" /></p></td>
<td><p><img alt="keypoints_small" src="_images/keypoints_small.jpg" /></p></td>
</tr>
<tr class="row-odd"><td><p><a class="reference internal" href="#registration">Registration</a></p></td>
<td><p><a class="reference internal" href="#kdtree">KdTree</a></p></td>
<td><p><a class="reference internal" href="#octree">Octree</a></p></td>
</tr>
<tr class="row-even"><td><p><img alt="registration_small" src="_images/registration_small.jpg" /></p></td>
<td><p><img alt="kdtree_small" src="_images/kdtree_small.png" /></p></td>
<td><p><img alt="octree_small" src="_images/octree_small.png" /></p></td>
</tr>
<tr class="row-odd"><td><p><a class="reference internal" href="#segmentation">Segmentation</a></p></td>
<td><p><a class="reference internal" href="#sample-consensus">Sample Consensus</a></p></td>
<td><p><a class="reference internal" href="#surface">Surface</a></p></td>
</tr>
<tr class="row-even"><td><p><img alt="segmentation_small" src="_images/segmentation_small.jpg" /></p></td>
<td><p><img alt="sample_consensus_small" src="_images/sample_consensus_small.jpg" /></p></td>
<td><p><img alt="surface_small" src="_images/surface_small.jpg" /></p></td>
</tr>
<tr class="row-odd"><td><p><a class="reference internal" href="#range-image">Range Image</a></p></td>
<td><p><a class="reference internal" href="#i-o">I/O</a></p></td>
<td><p><a class="reference internal" href="#visualization">Visualization</a></p></td>
</tr>
<tr class="row-even"><td><p><img alt="range_image_small" src="_images/range_image_small.jpg" /></p></td>
<td><p><img alt="io_small" src="_images/io_small.jpg" /></p></td>
<td><p><img alt="visualization_small" src="_images/visualization_small.png" /></p></td>
</tr>
<tr class="row-odd"><td><p>Common</p></td>
<td><p><a class="reference internal" href="#search">Search</a></p></td>
<td></td>
</tr>
<tr class="row-even"><td><p><img alt="pcl_logo" src="_images/pcl_logo.png" /></p></td>
<td><p><img alt="pcl_logo" src="_images/pcl_logo.png" /></p></td>
<td></td>
</tr>
</tbody>
</table>
<div class="line-block">
<div class="line"><br /></div>
</div>
<div class="line-block">
<div class="line"><br /></div>
</div>
</div>
<div class="section" id="filters">
<span id="id1"></span><h1>Filters</h1>
<p><strong>Background</strong></p>
<blockquote>
<div><p>An example of noise removal is presented in the figure below. Due to measurement errors, certain datasets present a large number of shadow points. This complicates the estimation of local point cloud 3D features. Some of these outliers can be filtered by performing a statistical analysis on each point’s neighborhood, and trimming those that do not meet a certain criteria. The sparse outlier removal implementation in PCL is based on the computation of the distribution of point to neighbor distances in the input dataset. For each point, the mean distance from it to all its neighbors is computed. By assuming that the resulting distribution is Gaussian with a mean and a standard deviation, all points whose mean distances are outside an interval defined by the global distances mean and standard deviation can be considered as outliers and trimmed from the dataset.</p>
</div></blockquote>
<img alt="_images/statistical_removal_2.jpg" src="_images/statistical_removal_2.jpg" />
<p><strong>Documentation:</strong> <a class="reference external" href="http://docs.pointclouds.org/trunk/group__filters.html">http://docs.pointclouds.org/trunk/group__filters.html</a></p>
<p><strong>Tutorials:</strong> <a class="reference external" href="http://pointclouds.org/documentation/tutorials/#filtering-tutorial">http://pointclouds.org/documentation/tutorials/#filtering-tutorial</a></p>
<p><strong>Interacts with:</strong></p>
<blockquote>
<div><ul class="simple">
<li><p><a class="reference internal" href="#sample-consensus">Sample Consensus</a></p></li>
<li><p><a class="reference internal" href="#kdtree">Kdtree</a></p></li>
<li><p><a class="reference internal" href="#octree">Octree</a></p></li>
</ul>
</div></blockquote>
<p><strong>Location:</strong></p>
<blockquote>
<div><ul class="simple">
<li><dl class="simple">
<dt>MAC OS X (Homebrew installation)</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/pcl-$(PCL_VERSION)/pcl/filters/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)</span></code> is the <code class="docutils literal notranslate"><span class="pre">cmake</span></code> installation prefix <code class="docutils literal notranslate"><span class="pre">CMAKE_INSTALL_PREFIX</span></code>, e.g., <code class="docutils literal notranslate"><span class="pre">/usr/local/</span></code></p></li>
</ul>
</dd>
</dl>
</li>
<li><dl class="simple">
<dt>Linux</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/pcl-$(PCL_VERSION)/pcl/filters/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)</span></code> is the <code class="docutils literal notranslate"><span class="pre">cmake</span></code> installation prefix <code class="docutils literal notranslate"><span class="pre">CMAKE_INSTALL_PREFIX</span></code>, e.g., <code class="docutils literal notranslate"><span class="pre">/usr/local/</span></code></p></li>
</ul>
</dd>
</dl>
</li>
<li><dl class="simple">
<dt>Windows</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)/include/pcl-$(PCL_VERSION)/pcl/filters/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)</span></code> is the PCL installation directory, e.g.,  <code class="docutils literal notranslate"><span class="pre">C:\Program</span> <span class="pre">Files\PCL</span> <span class="pre">$(PCL_VERSION)\</span></code></p></li>
</ul>
</dd>
</dl>
</li>
</ul>
</div></blockquote>
<p><a class="reference internal" href="#top">Top</a></p>
</div>
<div class="section" id="features">
<span id="id2"></span><h1>Features</h1>
<p><strong>Background</strong></p>
<blockquote>
<div><p>A theoretical primer explaining how features work in PCL can be found in the <a class="reference external" href="http://pointclouds.org/documentation/tutorials/how_features_work.php">3D Features tutorial</a>.</p>
<p>The <em>features</em> library contains data structures and mechanisms for 3D feature estimation from point cloud data. 3D features are representations at certain 3D points, or positions, in space, which describe geometrical patterns based on the information available around the point. The data space selected around the query point is usually referred to as the <em>k-neighborhood</em>.</p>
<p>The following figure shows a simple example of a selected query point, and its selected k-neighborhood.</p>
<img alt="_images/features_normal.jpg" src="_images/features_normal.jpg" />
<p>An example of two of the most widely used geometric point features are the underlying surface’s estimated curvature and normal at a query point <code class="docutils literal notranslate"><span class="pre">p</span></code>. Both of them are considered local features, as they characterize a point using the information provided by its <code class="docutils literal notranslate"><span class="pre">k</span></code> closest point neighbors. For determining these neighbors efficiently, the input dataset is usually split into smaller chunks using spatial decomposition techniques such as octrees or kD-trees, and then closest point searches are performed in that space. Depending on the application one can opt for either determining a fixed number of <code class="docutils literal notranslate"><span class="pre">k</span></code> points in the vicinity of <code class="docutils literal notranslate"><span class="pre">p</span></code>, or all points which are found inside of a sphere of radius <code class="docutils literal notranslate"><span class="pre">r</span></code> centered at <code class="docutils literal notranslate"><span class="pre">p</span></code>. Unarguably, one the easiest methods for estimating the surface normals and curvature changes at a point <code class="docutils literal notranslate"><span class="pre">p</span></code> is to perform an eigendecomposition (i.e., compute the eigenvectors and eigenvalues) of the k-neighborhood point surface patch. Thus, the eigenvector corresponding to the smallest eigenvalue will approximate the surface normal <code class="docutils literal notranslate"><span class="pre">n</span></code> at point <code class="docutils literal notranslate"><span class="pre">p</span></code>, while the surface curvature change will be estimated from the eigenvalues as <img class="math" src="_images/math/f8aa0f2172b694d84f06456742224fa113375772.png" alt="\frac{\lambda_0}{\lambda_0+\lambda_1+\lambda_2}"/> with <img class="math" src="_images/math/ada5acda5bf37d70a00c27737dfb6ae5bb8a5c6c.png" alt="\lambda_0&lt;\lambda_1&lt;\lambda_2"/>.</p>
<img alt="_images/features_bunny.jpg" src="_images/features_bunny.jpg" />
<div class="line-block">
<div class="line"><br /></div>
</div>
</div></blockquote>
<p><strong>Documentation:</strong> <a class="reference external" href="http://docs.pointclouds.org/trunk/group__features.html">http://docs.pointclouds.org/trunk/group__features.html</a></p>
<p><strong>Tutorials:</strong> <a class="reference external" href="http://pointclouds.org/documentation/tutorials/#features-tutorial">http://pointclouds.org/documentation/tutorials/#features-tutorial</a></p>
<p><strong>Interacts with:</strong></p>
<blockquote>
<div><ul class="simple">
<li><p><a class="reference internal" href="#common">Common</a></p></li>
<li><p><a class="reference internal" href="#search">Search</a></p></li>
<li><p><a class="reference internal" href="#kdtree">KdTree</a></p></li>
<li><p><a class="reference internal" href="#octree">Octree</a></p></li>
<li><p><a class="reference internal" href="#range-image">Range Image</a></p></li>
</ul>
</div></blockquote>
<p><strong>Location:</strong></p>
<blockquote>
<div><ul class="simple">
<li><dl class="simple">
<dt>MAC OS X (Homebrew installation)</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/pcl-$(PCL_VERSION)/pcl/features/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)</span></code> is the <code class="docutils literal notranslate"><span class="pre">cmake</span></code> installation prefix <code class="docutils literal notranslate"><span class="pre">CMAKE_INSTALL_PREFIX</span></code>, e.g., <code class="docutils literal notranslate"><span class="pre">/usr/local/</span></code></p></li>
</ul>
</dd>
</dl>
</li>
<li><dl class="simple">
<dt>Linux</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/pcl-$(PCL_VERSION)/pcl/filters/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)</span></code> is the <code class="docutils literal notranslate"><span class="pre">cmake</span></code> installation prefix <code class="docutils literal notranslate"><span class="pre">CMAKE_INSTALL_PREFIX</span></code>, e.g., <code class="docutils literal notranslate"><span class="pre">/usr/local/</span></code></p></li>
</ul>
</dd>
</dl>
</li>
<li><dl class="simple">
<dt>Windows</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)/include/pcl-$(PCL_VERSION)/pcl/features/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)</span></code> is the PCL installation directory, e.g.,  <code class="docutils literal notranslate"><span class="pre">C:\Program</span> <span class="pre">Files\PCL</span> <span class="pre">$(PCL_VERSION)\</span></code></p></li>
</ul>
</dd>
</dl>
</li>
</ul>
</div></blockquote>
<p><a class="reference internal" href="#top">Top</a></p>
</div>
<div class="section" id="keypoints">
<span id="id3"></span><h1>Keypoints</h1>
<p><strong>Background</strong></p>
<blockquote>
<div><p>The <em>keypoints</em> library contains implementations of two point cloud keypoint detection algorithms. Keypoints (also referred to as <a class="reference external" href="http://en.wikipedia.org/wiki/Interest_point_detection">interest points</a>) are points in an image or point cloud that are stable, distinctive, and can be identified using a well-defined detection criterion. Typically, the number of interest points in a point cloud will be much smaller than the total number of points in the cloud, and when used in combination with local feature descriptors at each keypoint, the keypoints and descriptors can be used to form a compact—yet descriptive—representation of the original data.</p>
<p>The figure below shows the output of NARF keypoints extraction from a range image:</p>
<img alt="_images/narf_keypoint_extraction.png" src="_images/narf_keypoint_extraction.png" />
</div></blockquote>
<div class="line-block">
<div class="line"><br /></div>
</div>
<p><strong>Documentation:</strong> <a class="reference external" href="http://docs.pointclouds.org/trunk/group__keypoints.html">http://docs.pointclouds.org/trunk/group__keypoints.html</a></p>
<p><strong>Tutorials:</strong> <a class="reference external" href="http://pointclouds.org/documentation/tutorials/#keypoints-tutorial">http://pointclouds.org/documentation/tutorials/#keypoints-tutorial</a></p>
<p><strong>Interacts with:</strong></p>
<blockquote>
<div><ul class="simple">
<li><p><a class="reference internal" href="#common">Common</a></p></li>
<li><p><a class="reference internal" href="#search">Search</a></p></li>
<li><p><a class="reference internal" href="#kdtree">KdTree</a></p></li>
<li><p><a class="reference internal" href="#octree">Octree</a></p></li>
<li><p><a class="reference internal" href="#range-image">Range Image</a></p></li>
<li><p><a class="reference internal" href="#features">Features</a></p></li>
<li><p><a class="reference internal" href="#filters">Filters</a></p></li>
</ul>
</div></blockquote>
<p><strong>Location:</strong></p>
<blockquote>
<div><ul class="simple">
<li><dl class="simple">
<dt>MAC OS X (Homebrew installation)</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/pcl-$(PCL_VERSION)/pcl/keypoints/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)</span></code> is the <code class="docutils literal notranslate"><span class="pre">cmake</span></code> installation prefix <code class="docutils literal notranslate"><span class="pre">CMAKE_INSTALL_PREFIX</span></code>, e.g., <code class="docutils literal notranslate"><span class="pre">/usr/local/</span></code></p></li>
</ul>
</dd>
</dl>
</li>
<li><dl class="simple">
<dt>Linux</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/pcl-$(PCL_VERSION)/pcl/filters/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)</span></code> is the <code class="docutils literal notranslate"><span class="pre">cmake</span></code> installation prefix <code class="docutils literal notranslate"><span class="pre">CMAKE_INSTALL_PREFIX</span></code>, e.g., <code class="docutils literal notranslate"><span class="pre">/usr/local/</span></code></p></li>
</ul>
</dd>
</dl>
</li>
<li><dl class="simple">
<dt>Windows</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)/include/pcl-$(PCL_VERSION)/pcl/keypoints/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)</span></code> is the PCL installation directory, e.g.,  <code class="docutils literal notranslate"><span class="pre">C:\Program</span> <span class="pre">Files\PCL</span> <span class="pre">$(PCL_VERSION)\</span></code></p></li>
</ul>
</dd>
</dl>
</li>
</ul>
</div></blockquote>
<p><a class="reference internal" href="#top">Top</a></p>
</div>
<div class="section" id="registration">
<span id="id4"></span><h1>Registration</h1>
<p><strong>Background</strong></p>
<blockquote>
<div><p>Combining several datasets into a global consistent model is usually performed using a technique called registration. The key idea is to identify corresponding points between the data sets and find a transformation that minimizes the distance (alignment error) between corresponding points. This process is repeated, since correspondence search is affected by the relative position and orientation of the data sets. Once the alignment errors fall below a given threshold, the registration is said to be complete.</p>
<p>The <em>registration</em> library implements a plethora of point cloud registration algorithms for both organized and unorganized (general purpose) datasets. For instance, PCL contains a set of powerful algorithms that allow the estimation of multiple sets of correspondences, as well as methods for rejecting bad correspondences, and estimating transformations in a robust manner.</p>
<img alt="_images/scans.jpg" src="_images/scans.jpg" />
<div class="line-block">
<div class="line"><br /></div>
</div>
<img alt="_images/s1-6.jpg" src="_images/s1-6.jpg" />
</div></blockquote>
<div class="line-block">
<div class="line"><br /></div>
</div>
<p><strong>Documentation:</strong> <a class="reference external" href="http://docs.pointclouds.org/trunk/group__registration.html">http://docs.pointclouds.org/trunk/group__registration.html</a></p>
<p><strong>Tutorials:</strong> <a class="reference external" href="http://pointclouds.org/documentation/tutorials/#registration-tutorial">http://pointclouds.org/documentation/tutorials/#registration-tutorial</a></p>
<p><strong>Interacts with:</strong></p>
<blockquote>
<div><ul class="simple">
<li><p><a class="reference internal" href="#common">Common</a></p></li>
<li><p><a class="reference internal" href="#kdtree">KdTree</a></p></li>
<li><p><a class="reference internal" href="#sample-consensus">Sample Consensus</a></p></li>
<li><p><a class="reference internal" href="#features">Features</a></p></li>
</ul>
</div></blockquote>
<p><strong>Location:</strong></p>
<blockquote>
<div><ul class="simple">
<li><dl class="simple">
<dt>MAC OS X (Homebrew installation)</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/pcl-$(PCL_VERSION)/pcl/registration/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)</span></code> is the <code class="docutils literal notranslate"><span class="pre">cmake</span></code> installation prefix <code class="docutils literal notranslate"><span class="pre">CMAKE_INSTALL_PREFIX</span></code>, e.g., <code class="docutils literal notranslate"><span class="pre">/usr/local/</span></code></p></li>
</ul>
</dd>
</dl>
</li>
<li><dl class="simple">
<dt>Linux</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/pcl-$(PCL_VERSION)/pcl/filters/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)</span></code> is the <code class="docutils literal notranslate"><span class="pre">cmake</span></code> installation prefix <code class="docutils literal notranslate"><span class="pre">CMAKE_INSTALL_PREFIX</span></code>, e.g., <code class="docutils literal notranslate"><span class="pre">/usr/local/</span></code></p></li>
</ul>
</dd>
</dl>
</li>
<li><dl class="simple">
<dt>Windows</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)/include/pcl-$(PCL_VERSION)/pcl/registration/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)</span></code> is the PCL installation directory, e.g.,  <code class="docutils literal notranslate"><span class="pre">C:\Program</span> <span class="pre">Files\PCL</span> <span class="pre">$(PCL_VERSION)\</span></code></p></li>
</ul>
</dd>
</dl>
</li>
</ul>
</div></blockquote>
<p><a class="reference internal" href="#top">Top</a></p>
</div>
<div class="section" id="kd-tree">
<span id="kdtree"></span><h1>Kd-tree</h1>
<p><strong>Background</strong></p>
<blockquote>
<div><p>A theoretical primer explaining how Kd-trees work can be found in the <a class="reference external" href="http://pointclouds.org/documentation/tutorials/kdtree_search.php#kdtree-search">Kd-tree tutorial</a>.</p>
<p>The <em>kdtree</em> library provides the kd-tree data-structure, using <a class="reference external" href="http://www.cs.ubc.ca/~mariusm/index.php/FLANN/FLANN">FLANN</a>, that allows for fast <a class="reference external" href="http://en.wikipedia.org/wiki/Nearest_neighbor_search">nearest neighbor searches</a>.</p>
<p>A <a class="reference external" href="http://en.wikipedia.org/wiki/Kd-tree">Kd-tree</a> (k-dimensional tree) is a space-partitioning data structure that stores a set of k-dimensional points in a tree structure that enables efficient range searches and nearest neighbor searches. Nearest neighbor searches are a core operation when working with point cloud data and can be used to find correspondences between groups of points or feature descriptors or to define the local neighborhood around a point or points.</p>
<img alt="_images/3dtree.png" src="_images/3dtree.png" />
<img alt="_images/kdtree_mug.jpg" src="_images/kdtree_mug.jpg" />
</div></blockquote>
<div class="line-block">
<div class="line"><br /></div>
</div>
<p><strong>Documentation:</strong> <a class="reference external" href="http://docs.pointclouds.org/trunk/group__kdtree.html">http://docs.pointclouds.org/trunk/group__kdtree.html</a></p>
<p><strong>Tutorials:</strong> <a class="reference external" href="http://pointclouds.org/documentation/tutorials/#kdtree-tutorial">http://pointclouds.org/documentation/tutorials/#kdtree-tutorial</a></p>
<p><strong>Interacts with:</strong> <a class="reference internal" href="#common">Common</a></p>
<p><strong>Location:</strong></p>
<blockquote>
<div><ul class="simple">
<li><dl class="simple">
<dt>MAC OS X (Homebrew installation)</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/pcl-$(PCL_VERSION)/pcl/kdtree/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)</span></code> is the <code class="docutils literal notranslate"><span class="pre">cmake</span></code> installation prefix <code class="docutils literal notranslate"><span class="pre">CMAKE_INSTALL_PREFIX</span></code>, e.g., <code class="docutils literal notranslate"><span class="pre">/usr/local/</span></code></p></li>
</ul>
</dd>
</dl>
</li>
<li><dl class="simple">
<dt>Linux</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/pcl-$(PCL_VERSION)/pcl/filters/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)</span></code> is the <code class="docutils literal notranslate"><span class="pre">cmake</span></code> installation prefix <code class="docutils literal notranslate"><span class="pre">CMAKE_INSTALL_PREFIX</span></code>, e.g., <code class="docutils literal notranslate"><span class="pre">/usr/local/</span></code></p></li>
</ul>
</dd>
</dl>
</li>
<li><dl class="simple">
<dt>Windows</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)/include/pcl-$(PCL_VERSION)/pcl/kdtree/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)</span></code> is the PCL installation directory, e.g.,  <code class="docutils literal notranslate"><span class="pre">C:\Program</span> <span class="pre">Files\PCL</span> <span class="pre">$(PCL_VERSION)\</span></code></p></li>
</ul>
</dd>
</dl>
</li>
</ul>
</div></blockquote>
<p><a class="reference internal" href="#top">Top</a></p>
</div>
<div class="section" id="octree">
<span id="id6"></span><h1>Octree</h1>
<p><strong>Background</strong></p>
<blockquote>
<div><p>The <em>octree</em> library provides efficient methods for creating a hierarchical tree data structure from point cloud data. This enables spatial partitioning, downsampling and search operations on the point data set. Each octree node has either eight children or no children. The root node describes a cubic bounding box which encapsulates all points. At every tree level, this space becomes subdivided by a factor of 2 which results in an increased voxel resolution.</p>
<p>The <em>octree</em> implementation provides efficient nearest neighbor search routines, such as “Neighbors within Voxel Search”, “K Nearest Neighbor Search” and “Neighbors within Radius Search”. It automatically adjusts its dimension to the point data set. A set of leaf node classes provide additional functionality, such as spacial “occupancy” and “point density per voxel” checks. Functions for serialization and deserialization enable to efficiently encode the octree structure into a binary format. Furthermore, a memory pool implementation reduces expensive memory allocation and deallocation operations in scenarios where octrees needs to be created at high rate.</p>
<p>The following figure illustrates the voxel bounding boxes of an octree nodes at lowest tree level. The octree voxels are surrounding every 3D point from the Stanford bunny’s surface. The red dots represent the point data. This image is created with the <a class="reference internal" href="#octree-viewer">octree_viewer</a>.</p>
<img alt="_images/octree_bunny.jpg" src="_images/octree_bunny.jpg" />
</div></blockquote>
<div class="line-block">
<div class="line"><br /></div>
</div>
<p><strong>Documentation:</strong> <a class="reference external" href="http://docs.pointclouds.org/trunk/group__octree.html">http://docs.pointclouds.org/trunk/group__octree.html</a></p>
<p><strong>Tutorials:</strong> <a class="reference external" href="http://pointclouds.org/documentation/tutorials/#octree-tutorial">http://pointclouds.org/documentation/tutorials/#octree-tutorial</a></p>
<p><strong>Interacts with:</strong> <a class="reference internal" href="#common">Common</a></p>
<p><strong>Location:</strong></p>
<blockquote>
<div><ul class="simple">
<li><dl class="simple">
<dt>MAC OS X (Homebrew installation)</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/pcl-$(PCL_VERSION)/pcl/octree/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)</span></code> is the <code class="docutils literal notranslate"><span class="pre">cmake</span></code> installation prefix <code class="docutils literal notranslate"><span class="pre">CMAKE_INSTALL_PREFIX</span></code>, e.g., <code class="docutils literal notranslate"><span class="pre">/usr/local/</span></code></p></li>
</ul>
</dd>
</dl>
</li>
<li><dl class="simple">
<dt>Linux</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/pcl-$(PCL_VERSION)/pcl/filters/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)</span></code> is the <code class="docutils literal notranslate"><span class="pre">cmake</span></code> installation prefix <code class="docutils literal notranslate"><span class="pre">CMAKE_INSTALL_PREFIX</span></code>, e.g., <code class="docutils literal notranslate"><span class="pre">/usr/local/</span></code></p></li>
</ul>
</dd>
</dl>
</li>
<li><dl class="simple">
<dt>Windows</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)/include/pcl-$(PCL_VERSION)/pcl/octree/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)</span></code> is the PCL installation directory, e.g.,  <code class="docutils literal notranslate"><span class="pre">C:\Program</span> <span class="pre">Files\PCL</span> <span class="pre">$(PCL_VERSION)\</span></code></p></li>
</ul>
</dd>
</dl>
</li>
</ul>
</div></blockquote>
<p><a class="reference internal" href="#top">Top</a></p>
</div>
<div class="section" id="segmentation">
<span id="id7"></span><h1>Segmentation</h1>
<p><strong>Background</strong></p>
<blockquote>
<div><p>The <em>segmentation</em> library contains algorithms for segmenting a point cloud into distinct clusters. These algorithms are best suited for processing a point cloud that is composed of a number of spatially isolated regions. In such cases, clustering is often used to break the cloud down into its constituent parts, which can then be processed independently.</p>
<p>A theoretical primer explaining how clustering methods work can be found in the <a class="reference external" href="http://pointclouds.org/documentation/tutorials/cluster_extraction.php#cluster-extraction">cluster extraction tutorial</a>.
The two figures illustrate the results of plane model segmentation (left) and cylinder model segmentation (right).</p>
<img alt="_images/plane_model_seg.jpg" src="_images/plane_model_seg.jpg" />
<img alt="_images/cylinder_model_seg.jpg" src="_images/cylinder_model_seg.jpg" />
</div></blockquote>
<div class="line-block">
<div class="line"><br /></div>
</div>
<p><strong>Documentation:</strong> <a class="reference external" href="http://docs.pointclouds.org/trunk/group__segmentation.html">http://docs.pointclouds.org/trunk/group__segmentation.html</a></p>
<p><strong>Tutorials:</strong> <a class="reference external" href="http://pointclouds.org/documentation/tutorials/#segmentation-tutorial">http://pointclouds.org/documentation/tutorials/#segmentation-tutorial</a></p>
<p><strong>Interacts with:</strong></p>
<blockquote>
<div><ul class="simple">
<li><p><a class="reference internal" href="#common">Common</a></p></li>
<li><p><a class="reference internal" href="#search">Search</a></p></li>
<li><p><a class="reference internal" href="#sample-consensus">Sample Consensus</a></p></li>
<li><p><a class="reference internal" href="#kdtree">KdTree</a></p></li>
<li><p><a class="reference internal" href="#octree">Octree</a></p></li>
</ul>
</div></blockquote>
<p><strong>Location:</strong></p>
<blockquote>
<div><ul class="simple">
<li><dl class="simple">
<dt>MAC OS X (Homebrew installation)</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/pcl-$(PCL_VERSION)/pcl/segmentation/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)</span></code> is the <code class="docutils literal notranslate"><span class="pre">cmake</span></code> installation prefix <code class="docutils literal notranslate"><span class="pre">CMAKE_INSTALL_PREFIX</span></code>, e.g., <code class="docutils literal notranslate"><span class="pre">/usr/local/</span></code></p></li>
</ul>
</dd>
</dl>
</li>
<li><dl class="simple">
<dt>Linux</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/pcl-$(PCL_VERSION)/pcl/filters/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)</span></code> is the <code class="docutils literal notranslate"><span class="pre">cmake</span></code> installation prefix <code class="docutils literal notranslate"><span class="pre">CMAKE_INSTALL_PREFIX</span></code>, e.g., <code class="docutils literal notranslate"><span class="pre">/usr/local/</span></code></p></li>
</ul>
</dd>
</dl>
</li>
<li><dl class="simple">
<dt>Windows</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)/include/pcl-$(PCL_VERSION)/pcl/segmentation/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)</span></code> is the PCL installation directory, e.g.,  <code class="docutils literal notranslate"><span class="pre">C:\Program</span> <span class="pre">Files\PCL</span> <span class="pre">$(PCL_VERSION)\</span></code></p></li>
</ul>
</dd>
</dl>
</li>
</ul>
</div></blockquote>
<p><a class="reference internal" href="#top">Top</a></p>
</div>
<div class="section" id="sample-consensus">
<span id="id8"></span><h1>Sample Consensus</h1>
<p><strong>Background</strong></p>
<blockquote>
<div><p>The <em>sample_consensus</em> library holds SAmple Consensus (SAC) methods like RANSAC and models like planes and cylinders. These can combined freely in order to detect specific models and their parameters in point clouds.</p>
<p>A theoretical primer explaining how sample consensus algorithms work can be found in the <a class="reference external" href="http://pointclouds.org/documentation/tutorials/random_sample_consensus.php#random-sample-consensus">Random Sample Consensus tutorial</a></p>
<p>Some of the models implemented in this library include: lines, planes, cylinders, and spheres. Plane fitting is often applied to the task of detecting common indoor surfaces, such as walls, floors, and table tops. Other models can be used to detect and segment objects with common geometric structures (e.g., fitting a cylinder model to a mug).</p>
<img alt="_images/sample_consensus_planes_cylinders.jpg" src="_images/sample_consensus_planes_cylinders.jpg" />
</div></blockquote>
<div class="line-block">
<div class="line"><br /></div>
</div>
<p><strong>Documentation:</strong> <a class="reference external" href="http://docs.pointclouds.org/trunk/group__sample__consensus.html">http://docs.pointclouds.org/trunk/group__sample__consensus.html</a></p>
<p><strong>Tutorials:</strong> <a class="reference external" href="http://pointclouds.org/documentation/tutorials/#sample-consensus">http://pointclouds.org/documentation/tutorials/#sample-consensus</a></p>
<p><strong>Interacts with:</strong> <a class="reference internal" href="#common">Common</a></p>
<p><strong>Location:</strong></p>
<blockquote>
<div><ul class="simple">
<li><dl class="simple">
<dt>MAC OS X (Homebrew installation)</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/pcl-$(PCL_VERSION)/pcl/sample_consensus/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)</span></code> is the <code class="docutils literal notranslate"><span class="pre">cmake</span></code> installation prefix <code class="docutils literal notranslate"><span class="pre">CMAKE_INSTALL_PREFIX</span></code>, e.g., <code class="docutils literal notranslate"><span class="pre">/usr/local/</span></code></p></li>
</ul>
</dd>
</dl>
</li>
<li><dl class="simple">
<dt>Linux</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/pcl-$(PCL_VERSION)/pcl/filters/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)</span></code> is the <code class="docutils literal notranslate"><span class="pre">cmake</span></code> installation prefix <code class="docutils literal notranslate"><span class="pre">CMAKE_INSTALL_PREFIX</span></code>, e.g., <code class="docutils literal notranslate"><span class="pre">/usr/local/</span></code></p></li>
</ul>
</dd>
</dl>
</li>
<li><dl class="simple">
<dt>Windows</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)/include/pcl-$(PCL_VERSION)/pcl/sample_consensus/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)</span></code> is the PCL installation directory, e.g.,  <code class="docutils literal notranslate"><span class="pre">C:\Program</span> <span class="pre">Files\PCL</span> <span class="pre">$(PCL_VERSION)\</span></code></p></li>
</ul>
</dd>
</dl>
</li>
</ul>
</div></blockquote>
<p><a class="reference internal" href="#top">Top</a></p>
</div>
<div class="section" id="surface">
<span id="id9"></span><h1>Surface</h1>
<p><strong>Background</strong></p>
<blockquote>
<div><p>The <em>surface</em> library deals with reconstructing the original surfaces from 3D scans. Depending on the task at hand, this can be for example the hull, a mesh representation or a smoothed/resampled surface with normals.</p>
<p>Smoothing and resampling can be important if the cloud is noisy, or if it is composed of multiple scans that are not aligned perfectly. The complexity of the surface estimation can be adjusted, and normals can be estimated in the same step if needed.</p>
<img alt="_images/resampling_1.jpg" src="_images/resampling_1.jpg" />
<p>Meshing is a general way to create a surface out of points, and currently there are two algorithms provided: a very fast triangulation of the original points, and a slower meshing that does smoothing and hole filling as well.</p>
<img alt="_images/surface_meshing.jpg" src="_images/surface_meshing.jpg" />
<p>Creating a convex or concave hull is useful for example when there is a need for a simplified surface representation or when boundaries need to be extracted.</p>
<img alt="_images/surface_hull.jpg" src="_images/surface_hull.jpg" />
</div></blockquote>
<div class="line-block">
<div class="line"><br /></div>
</div>
<p><strong>Documentation:</strong> <a class="reference external" href="http://docs.pointclouds.org/trunk/group__surface.html">http://docs.pointclouds.org/trunk/group__surface.html</a></p>
<p><strong>Tutorials:</strong> <a class="reference external" href="http://pointclouds.org/documentation/tutorials/#surface-tutorial">http://pointclouds.org/documentation/tutorials/#surface-tutorial</a></p>
<p><strong>Interacts with:</strong></p>
<blockquote>
<div><ul class="simple">
<li><p><a class="reference internal" href="#common">Common</a></p></li>
<li><p><a class="reference internal" href="#search">Search</a></p></li>
<li><p><a class="reference internal" href="#kdtree">KdTree</a></p></li>
<li><p><a class="reference internal" href="#octree">Octree</a></p></li>
</ul>
</div></blockquote>
<p><strong>Location:</strong></p>
<blockquote>
<div><ul class="simple">
<li><dl class="simple">
<dt>MAC OS X (Homebrew installation)</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/pcl-$(PCL_VERSION)/pcl/surface/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)</span></code> is the <code class="docutils literal notranslate"><span class="pre">cmake</span></code> installation prefix <code class="docutils literal notranslate"><span class="pre">CMAKE_INSTALL_PREFIX</span></code>, e.g., <code class="docutils literal notranslate"><span class="pre">/usr/local/</span></code></p></li>
</ul>
</dd>
</dl>
</li>
<li><dl class="simple">
<dt>Linux</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/pcl-$(PCL_VERSION)/pcl/filters/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)</span></code> is the <code class="docutils literal notranslate"><span class="pre">cmake</span></code> installation prefix <code class="docutils literal notranslate"><span class="pre">CMAKE_INSTALL_PREFIX</span></code>, e.g., <code class="docutils literal notranslate"><span class="pre">/usr/local/</span></code></p></li>
</ul>
</dd>
</dl>
</li>
<li><dl class="simple">
<dt>Windows</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)/include/pcl-$(PCL_VERSION)/pcl/surface/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)</span></code> is the PCL installation directory, e.g.,  <code class="docutils literal notranslate"><span class="pre">C:\Program</span> <span class="pre">Files\PCL</span> <span class="pre">$(PCL_VERSION)\</span></code></p></li>
</ul>
</dd>
</dl>
</li>
</ul>
</div></blockquote>
<p><a class="reference internal" href="#top">Top</a></p>
</div>
<div class="section" id="range-image">
<span id="id10"></span><h1>Range Image</h1>
<p><strong>Background</strong></p>
<blockquote>
<div><p>The <em>range_image</em> library contains two classes for representing and working with range images. A range image (or depth map) is an image whose pixel values represent a distance or depth from the sensor’s origin. Range images are a common 3D representation and are often generated by stereo or time-of-flight cameras. With knowledge of the camera’s intrinsic calibration parameters, a range image can be converted into a point cloud.</p>
<p>Note: <em>range_image</em> is now a part of <a class="reference internal" href="#common">Common</a> module.</p>
<img alt="_images/range_image1.jpg" src="_images/range_image1.jpg" />
</div></blockquote>
<div class="line-block">
<div class="line"><br /></div>
</div>
<p><strong>Tutorials:</strong> <a class="reference external" href="http://pointclouds.org/documentation/tutorials/#range-images">http://pointclouds.org/documentation/tutorials/#range-images</a></p>
<p><strong>Interacts with:</strong> <a class="reference internal" href="#common">Common</a></p>
<p><strong>Location:</strong></p>
<blockquote>
<div><ul class="simple">
<li><dl class="simple">
<dt>MAC OS X (Homebrew installation)</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/pcl-$(PCL_VERSION)/pcl/range_image/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)</span></code> is the <code class="docutils literal notranslate"><span class="pre">cmake</span></code> installation prefix <code class="docutils literal notranslate"><span class="pre">CMAKE_INSTALL_PREFIX</span></code>, e.g., <code class="docutils literal notranslate"><span class="pre">/usr/local/</span></code></p></li>
</ul>
</dd>
</dl>
</li>
<li><dl class="simple">
<dt>Linux</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/pcl-$(PCL_VERSION)/pcl/filters/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)</span></code> is the <code class="docutils literal notranslate"><span class="pre">cmake</span></code> installation prefix <code class="docutils literal notranslate"><span class="pre">CMAKE_INSTALL_PREFIX</span></code>, e.g., <code class="docutils literal notranslate"><span class="pre">/usr/local/</span></code></p></li>
</ul>
</dd>
</dl>
</li>
<li><dl class="simple">
<dt>Windows</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)/include/pcl-$(PCL_VERSION)/pcl/range_image/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)</span></code> is the PCL installation directory, e.g.,  <code class="docutils literal notranslate"><span class="pre">C:\Program</span> <span class="pre">Files\PCL</span> <span class="pre">$(PCL_VERSION)\</span></code></p></li>
</ul>
</dd>
</dl>
</li>
</ul>
</div></blockquote>
<p><a class="reference internal" href="#top">Top</a></p>
</div>
<div class="section" id="i-o">
<span id="id11"></span><h1>I/O</h1>
<p><strong>Background</strong></p>
<blockquote>
<div><blockquote>
<div><p>The <em>io</em> library contains classes and functions for reading and writing point cloud data (PCD) files, as well as capturing point clouds from a variety of sensing devices. An introduction to some of these capabilities can be found in the following tutorials:</p>
</div></blockquote>
<ul class="simple">
<li><p><a class="reference external" href="http://pointclouds.org/documentation/tutorials/pcd_file_format.php#pcd-file-format">The PCD (Point Cloud Data) file format</a></p></li>
<li><p><a class="reference external" href="http://pointclouds.org/documentation/tutorials/reading_pcd.php#reading-pcd">Reading PointCloud data from PCD files</a></p></li>
<li><p><a class="reference external" href="http://pointclouds.org/documentation/tutorials/writing_pcd.php#writing-pcd">Writing PointCloud data to PCD files</a></p></li>
<li><p><a class="reference external" href="http://pointclouds.org/documentation/tutorials/openni_grabber.php#openni-grabber">The OpenNI Grabber Framework in PCL</a></p></li>
</ul>
</div></blockquote>
<div class="line-block">
<div class="line"><br /></div>
</div>
<p><strong>Documentation:</strong> <a class="reference external" href="http://docs.pointclouds.org/trunk/group__io.html">http://docs.pointclouds.org/trunk/group__io.html</a></p>
<p><strong>Tutorials:</strong> <a class="reference external" href="http://pointclouds.org/documentation/tutorials/#i-o">http://pointclouds.org/documentation/tutorials/#i-o</a></p>
<p><strong>Interacts with:</strong></p>
<blockquote>
<div><ul class="simple">
<li><p><a class="reference internal" href="#common">Common</a></p></li>
<li><p><a class="reference internal" href="#octree">Octree</a></p></li>
<li><p>OpenNI for kinect handling</p></li>
</ul>
</div></blockquote>
<p><strong>Location:</strong></p>
<blockquote>
<div><ul class="simple">
<li><dl class="simple">
<dt>MAC OS X (Homebrew installation)</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/pcl-$(PCL_VERSION)/pcl/io/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)</span></code> is the <code class="docutils literal notranslate"><span class="pre">cmake</span></code> installation prefix <code class="docutils literal notranslate"><span class="pre">CMAKE_INSTALL_PREFIX</span></code>, e.g., <code class="docutils literal notranslate"><span class="pre">/usr/local/</span></code></p></li>
</ul>
</dd>
</dl>
</li>
<li><dl class="simple">
<dt>Linux</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/pcl-$(PCL_VERSION)/pcl/filters/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)</span></code> is the <code class="docutils literal notranslate"><span class="pre">cmake</span></code> installation prefix <code class="docutils literal notranslate"><span class="pre">CMAKE_INSTALL_PREFIX</span></code>, e.g., <code class="docutils literal notranslate"><span class="pre">/usr/local/</span></code></p></li>
</ul>
</dd>
</dl>
</li>
<li><dl class="simple">
<dt>Windows</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)/include/pcl-$(PCL_VERSION)/pcl/io/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)</span></code> is the PCL installation directory, e.g.,  <code class="docutils literal notranslate"><span class="pre">C:\Program</span> <span class="pre">Files\PCL</span> <span class="pre">$(PCL_VERSION)\</span></code></p></li>
</ul>
</dd>
</dl>
</li>
</ul>
</div></blockquote>
<p><a class="reference internal" href="#top">Top</a></p>
</div>
<div class="section" id="visualization">
<span id="id12"></span><h1>Visualization</h1>
<p><strong>Background</strong></p>
<blockquote>
<div><p>The <em>visualization</em> library was built for the purpose of being able to quickly prototype and visualize the results of algorithms operating on 3D point cloud data. Similar to OpenCV’s <em>highgui</em> routines for displaying 2D images and for drawing basic 2D shapes on screen, the library offers:</p>
<p>methods for rendering and setting visual properties (colors, point sizes, opacity, etc) for any n-D point cloud datasets in <code class="docutils literal notranslate"><span class="pre">pcl::PointCloud&lt;T&gt;</span> <span class="pre">format;</span></code></p>
<img alt="_images/bunny1.jpg" src="_images/bunny1.jpg" />
<p>methods for drawing basic 3D shapes on screen (e.g., cylinders, spheres,lines, polygons, etc) either from sets of points or from parametric equations;</p>
<img alt="_images/shapes1.jpg" src="_images/shapes1.jpg" />
<p>a histogram visualization module (PCLHistogramVisualizer) for 2D plots;</p>
<img alt="_images/histogram1.jpg" src="_images/histogram1.jpg" />
<p>a multitude of Geometry and Color handlers for pcl::PointCloud&lt;T&gt; datasets;</p>
<img alt="_images/normals1.jpg" src="_images/normals1.jpg" />
<div class="line-block">
<div class="line"><br /></div>
</div>
<img alt="_images/pcs1.jpg" src="_images/pcs1.jpg" />
<p>a <code class="docutils literal notranslate"><span class="pre">pcl::RangeImage</span></code> visualization module.</p>
<img alt="_images/range_image1.jpg" src="_images/range_image1.jpg" />
<p>The package makes use of the VTK library for 3D rendering for range image and 2D operations.</p>
<p>For implementing your own visualizers, take a look at the tests and examples accompanying the library.</p>
</div></blockquote>
<div class="line-block">
<div class="line"><br /></div>
</div>
<p><strong>Documentation:</strong> <a class="reference external" href="http://docs.pointclouds.org/trunk/group__visualization.html">http://docs.pointclouds.org/trunk/group__visualization.html</a></p>
<p><strong>Tutorials:</strong> <a class="reference external" href="http://pointclouds.org/documentation/tutorials/#visualization-tutorial">http://pointclouds.org/documentation/tutorials/#visualization-tutorial</a></p>
<p><strong>Interacts with:</strong></p>
<blockquote>
<div><ul class="simple">
<li><p><a class="reference internal" href="#common">Common</a></p></li>
<li><p><a class="reference internal" href="#i-o">I/O</a></p></li>
<li><p><a class="reference internal" href="#kdtree">KdTree</a></p></li>
<li><p><a class="reference internal" href="#range-image">Range Image</a></p></li>
<li><p>VTK</p></li>
</ul>
</div></blockquote>
<p><strong>Location:</strong></p>
<blockquote>
<div><ul class="simple">
<li><dl class="simple">
<dt>MAC OS X (Homebrew installation)</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/pcl-$(PCL_VERSION)/pcl/visualization/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)</span></code> is the <code class="docutils literal notranslate"><span class="pre">cmake</span></code> installation prefix <code class="docutils literal notranslate"><span class="pre">CMAKE_INSTALL_PREFIX</span></code>, e.g., <code class="docutils literal notranslate"><span class="pre">/usr/local/</span></code></p></li>
</ul>
</dd>
</dl>
</li>
<li><dl class="simple">
<dt>Linux</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/pcl-$(PCL_VERSION)/pcl/filters/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)</span></code> is the <code class="docutils literal notranslate"><span class="pre">cmake</span></code> installation prefix <code class="docutils literal notranslate"><span class="pre">CMAKE_INSTALL_PREFIX</span></code>, e.g., <code class="docutils literal notranslate"><span class="pre">/usr/local/</span></code></p></li>
</ul>
</dd>
</dl>
</li>
<li><dl class="simple">
<dt>Windows</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)/include/pcl-$(PCL_VERSION)/pcl/visualization/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)</span></code> is the PCL installation directory, e.g.,  <code class="docutils literal notranslate"><span class="pre">C:\Program</span> <span class="pre">Files\PCL</span> <span class="pre">$(PCL_VERSION)\</span></code></p></li>
</ul>
</dd>
</dl>
</li>
</ul>
</div></blockquote>
<p><a class="reference internal" href="#top">Top</a></p>
</div>
<div class="section" id="common">
<span id="id13"></span><h1>Common</h1>
<p><strong>Background</strong></p>
<blockquote>
<div><p>The <em>common</em> library contains the common data structures and methods used by the majority of PCL libraries. The core data structures include the PointCloud class and a multitude of point types that are used to represent points, surface normals, RGB color values, feature descriptors, etc. It also contains numerous functions for computing distances/norms, means and covariances, angular conversions, geometric transformations, and more.</p>
</div></blockquote>
<p><strong>Location:</strong></p>
<blockquote>
<div><ul class="simple">
<li><dl class="simple">
<dt>MAC OS X (Homebrew installation)</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/pcl-$(PCL_VERSION)/pcl/common/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)</span></code> is the <code class="docutils literal notranslate"><span class="pre">cmake</span></code> installation prefix <code class="docutils literal notranslate"><span class="pre">CMAKE_INSTALL_PREFIX</span></code>, e.g., <code class="docutils literal notranslate"><span class="pre">/usr/local/</span></code></p></li>
</ul>
</dd>
</dl>
</li>
<li><dl class="simple">
<dt>Linux</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/pcl-$(PCL_VERSION)/pcl/common/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)</span></code> is the <code class="docutils literal notranslate"><span class="pre">cmake</span></code> installation prefix <code class="docutils literal notranslate"><span class="pre">CMAKE_INSTALL_PREFIX</span></code>, e.g., <code class="docutils literal notranslate"><span class="pre">/usr/local/</span></code></p></li>
</ul>
</dd>
</dl>
</li>
<li><dl class="simple">
<dt>Windows</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)/include/pcl-$(PCL_VERSION)/pcl/common/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)</span></code> is the PCL installation directory, e.g.,  <code class="docutils literal notranslate"><span class="pre">C:\Program</span> <span class="pre">Files\PCL</span> <span class="pre">$(PCL_VERSION)\</span></code></p></li>
</ul>
</dd>
</dl>
</li>
</ul>
</div></blockquote>
<p><a class="reference internal" href="#top">Top</a></p>
</div>
<div class="section" id="search">
<span id="id14"></span><h1>Search</h1>
<p><strong>Background</strong></p>
<blockquote>
<div><blockquote>
<div><p>The <em>search</em> library provides methods for searching for nearest neighbors using different data structures, including:</p>
</div></blockquote>
<ul class="simple">
<li><p><a class="reference internal" href="#kdtree">KdTree</a></p></li>
<li><p><a class="reference internal" href="#octree">Octree</a></p></li>
<li><p>brute force</p></li>
<li><p>specialized search for organized datasets</p></li>
</ul>
</div></blockquote>
<div class="line-block">
<div class="line"><br /></div>
</div>
<p><strong>Interacts with:</strong></p>
<blockquote>
<div><ul class="simple">
<li><p><a class="reference internal" href="#common">Common</a></p></li>
<li><p><a class="reference internal" href="#kdtree">Kdtree</a></p></li>
<li><p><a class="reference internal" href="#octree">Octree</a></p></li>
</ul>
</div></blockquote>
<dl class="simple">
<dt><strong>Location:</strong></dt><dd><ul class="simple">
<li><dl class="simple">
<dt>MAC OS X (Homebrew installation)</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/pcl-$(PCL_VERSION)/pcl/search/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)</span></code> is the <code class="docutils literal notranslate"><span class="pre">cmake</span></code> installation prefix <code class="docutils literal notranslate"><span class="pre">CMAKE_INSTALL_PREFIX</span></code>, e.g., <code class="docutils literal notranslate"><span class="pre">/usr/local/</span></code></p></li>
</ul>
</dd>
</dl>
</li>
<li><dl class="simple">
<dt>Linux</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/pcl-$(PCL_VERSION)/pcl/search/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_PREFIX)</span></code> is the <code class="docutils literal notranslate"><span class="pre">cmake</span></code> installation prefix <code class="docutils literal notranslate"><span class="pre">CMAKE_INSTALL_PREFIX</span></code>, e.g., <code class="docutils literal notranslate"><span class="pre">/usr/local/</span></code></p></li>
</ul>
</dd>
</dl>
</li>
<li><dl class="simple">
<dt>Windows</dt><dd><ul>
<li><p>Header files: <code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)/include/pcl-$(PCL_VERSION)/pcl/search/</span></code></p></li>
<li><p><a class="reference internal" href="#binaries">Binaries</a>: <code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)/bin/</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">$(PCL_DIRECTORY)</span></code> is the PCL installation directory, e.g.,  <code class="docutils literal notranslate"><span class="pre">C:\Program</span> <span class="pre">Files\PCL</span> <span class="pre">$(PCL_VERSION)\</span></code></p></li>
</ul>
</dd>
</dl>
</li>
</ul>
</dd>
</dl>
<p><a class="reference internal" href="#top">Top</a></p>
</div>
<div class="section" id="binaries">
<span id="id15"></span><h1>Binaries</h1>
<p>This section provides a quick reference for some of the common tools in PCL.</p>
<blockquote>
<div><ul>
<li><p><code class="docutils literal notranslate"><span class="pre">pcl_viewer</span></code>: a quick way for visualizing PCD (Point Cloud Data) files. More information about PCD files can be found in the <a class="reference external" href="http://pointclouds.org/documentation/tutorials/pcd_file_format.php">PCD file format tutorial</a>.</p>
<blockquote>
<div><p><strong>Syntax is: pcl_viewer &lt;file_name 1..N&gt;.&lt;pcd or vtk&gt; &lt;options&gt;</strong>, where options are:</p>
<blockquote>
<div><p>-bc r,g,b                = background color</p>
<p>-fc r,g,b                = foreground color</p>
<p>-ps X                    = point size (1..64)</p>
<p>-opaque X                = rendered point cloud opacity (0..1)</p>
<p>-ax n                    = enable on-screen display of XYZ axes and scale them to n</p>
<p>-ax_pos X,Y,Z            = if axes are enabled, set their X,Y,Z position in space (default 0,0,0)</p>
<p>-cam (*)                 = use given camera settings as initial view</p>
<blockquote>
<div><p>(*) [Clipping Range / Focal Point / Position / ViewUp / Distance / Field of View Y / Window Size / Window Pos] or use a &lt;filename.cam&gt; that contains the same information.</p>
</div></blockquote>
<p>-multiview 0/1           = enable/disable auto-multi viewport rendering (default disabled)</p>
<p>-normals 0/X             = disable/enable the display of every Xth point’s surface normal as lines (default disabled)
-normals_scale X         = resize the normal unit vector size to X (default 0.02)</p>
<p>-pc 0/X                  = disable/enable the display of every Xth point’s principal curvatures as lines (default disabled)
-pc_scale X              = resize the principal curvatures vectors size to X (default 0.02)</p>
</div></blockquote>
<p><em>(Note: for multiple .pcd files, provide multiple -{fc,ps,opaque} parameters; they will be automatically assigned to the right file)</em></p>
<p><strong>Usage example:</strong></p>
<p><code class="docutils literal notranslate"><span class="pre">pcl_viewer</span> <span class="pre">-multiview</span> <span class="pre">1</span> <span class="pre">data/partial_cup_model.pcd</span> <span class="pre">data/partial_cup_model.pcd</span> <span class="pre">data/partial_cup_model.pcd</span></code></p>
<p>The above will load the partial_cup_model.pcd file 3 times, and will create a multi-viewport rendering (-multiview 1).</p>
<img alt="_images/ex11.jpg" src="_images/ex11.jpg" />
</div></blockquote>
</li>
</ul>
</div></blockquote>
<div class="line-block">
<div class="line"><br /></div>
</div>
<blockquote>
<div><ul>
<li><p><code class="docutils literal notranslate"><span class="pre">pcd_convert_NaN_nan</span></code>: converts “NaN” values to “nan” values. <em>(Note: Starting with PCL version 1.0.1 the string representation for NaN is “nan”.)</em></p>
<blockquote>
<div><p><strong>Usage example:</strong></p>
<p><code class="docutils literal notranslate"><span class="pre">pcd_convert_NaN_nan</span> <span class="pre">input.pcd</span> <span class="pre">output.pcd</span></code></p>
</div></blockquote>
</li>
<li><p><code class="docutils literal notranslate"><span class="pre">convert_pcd_ascii_binary</span></code>: converts PCD (Point Cloud Data) files from ASCII to binary and viceversa.</p>
<blockquote>
<div><p><strong>Usage example:</strong></p>
<p><code class="docutils literal notranslate"><span class="pre">convert_pcd_ascii_binary</span> <span class="pre">&lt;file_in.pcd&gt;</span> <span class="pre">&lt;file_out.pcd&gt;</span> <span class="pre">0/1/2</span> <span class="pre">(ascii/binary/binary_compressed)</span> <span class="pre">[precision</span> <span class="pre">(ASCII)]</span></code></p>
</div></blockquote>
</li>
<li><p><code class="docutils literal notranslate"><span class="pre">concatenate_points_pcd</span></code>: concatenates the points of two or more PCD (Point Cloud Data) files into a single PCD file.</p>
<blockquote>
<div><p><strong>Usage example:</strong></p>
<p><code class="docutils literal notranslate"><span class="pre">concatenate_points_pcd</span> <span class="pre">&lt;filename</span> <span class="pre">1..N.pcd&gt;</span></code></p>
<p><em>(Note: the resulting PCD file will be ``output.pcd``)</em></p>
</div></blockquote>
</li>
<li><p><code class="docutils literal notranslate"><span class="pre">pcd2vtk</span></code>: converts PCD (Point Cloud Data) files to the <a class="reference external" href="http://www.vtk.org/VTK/img/file-formats.pdf">VTK format</a>.</p>
<blockquote>
<div><p><strong>Usage example:</strong></p>
<p><code class="docutils literal notranslate"><span class="pre">pcd2vtk</span> <span class="pre">input.pcd</span> <span class="pre">output.vtk</span></code></p>
</div></blockquote>
</li>
<li><p><code class="docutils literal notranslate"><span class="pre">pcd2ply</span></code>: converts PCD (Point Cloud Data) files to the <a class="reference external" href="http://en.wikipedia.org/wiki/PLY_%28file_format%29">PLY format</a>.</p>
<blockquote>
<div><p><strong>Usage example:</strong></p>
<p><code class="docutils literal notranslate"><span class="pre">pcd2ply</span> <span class="pre">input.pcd</span> <span class="pre">output.ply</span></code></p>
</div></blockquote>
</li>
<li><p><code class="docutils literal notranslate"><span class="pre">mesh2pcd</span></code>: convert a CAD model to a PCD (Point Cloud Data) file, using ray tracing operations.</p>
<blockquote>
<div><p><strong>Syntax is: mesh2pcd input.{ply,obj} output.pcd &lt;options&gt;</strong>, where options are:</p>
<blockquote>
<div><p>-level X      = tessellated sphere level (default: 2)</p>
<p>-resolution X = the sphere resolution in angle increments (default: 100 deg)</p>
<p>-leaf_size X  = the XYZ leaf size for the VoxelGrid – for data reduction (default: 0.010000 m)</p>
</div></blockquote>
</div></blockquote>
</li>
</ul>
<ul id="octree-viewer">
<li><p><code class="docutils literal notranslate"><span class="pre">octree_viewer</span></code>: allows the visualization of <a class="reference internal" href="#octree">octrees</a></p>
<blockquote>
<div><p><strong>Syntax is: octree_viewer &lt;file_name.pcd&gt; &lt;octree resolution&gt;</strong></p>
<p><strong>Usage example:</strong></p>
<p><code class="docutils literal notranslate"><span class="pre">Example:</span> <span class="pre">./octree_viewer</span> <span class="pre">../../test/bunny.pcd</span> <span class="pre">0.02</span></code></p>
<img alt="_images/octree_bunny2.png" src="_images/octree_bunny2.png" />
</div></blockquote>
</li>
</ul>
</div></blockquote>
<p><a class="reference internal" href="#top">Top</a></p>
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