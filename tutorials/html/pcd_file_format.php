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
    
    <title>The PCD (Point Cloud Data) file format</title>
    
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
            
  <div class="section" id="the-pcd-point-cloud-data-file-format">
<span id="pcd-file-format"></span><h1>The PCD (<strong>P</strong>oint <strong>C</strong>loud <strong>D</strong>ata) file format</h1>
<p>This document describes the PCD (Point Cloud Data) file format, and the way it
is used inside Point Cloud Library (PCL).</p>
<img alt="PCD file format icon" class="align-right" src="_images/PCD_icon.png" />
</div>
<div class="section" id="why-a-new-file-format">
<h1>Why a new file format?</h1>
<p>The PCD file format is not meant to reinvent the wheel, but rather to
complement existing file formats that for one reason or another did not/do not
support some of the extensions that PCL brings to n-D point cloud processing.</p>
<p>PCD is not the first file type to support 3D point cloud data. The computer
graphics and computational geometry communities in particular, have created
numerous formats to describe arbitrary polygons and point clouds acquired using
laser scanners. Some of these formats include:</p>
<ul class="simple">
<li><a class="reference external" href="http://en.wikipedia.org/wiki/PLY_(file_format)">PLY</a> - a polygon file format, developed at Stanford University by Turk et al</li>
<li><a class="reference external" href="http://en.wikipedia.org/wiki/STL_(file_format)">STL</a> - a file format native to the stereolithography CAD software created by 3D Systems</li>
<li><a class="reference external" href="http://en.wikipedia.org/wiki/Wavefront_.obj_file">OBJ</a> - a geometry definition file format first developed by Wavefront Technologies</li>
<li><a class="reference external" href="http://en.wikipedia.org/wiki/X3D">X3D</a> - the ISO standard XML-based file format for representing 3D computer graphics data</li>
<li><a class="reference external" href="http://en.wikipedia.org/wiki/Category:Graphics_file_formats">and many others</a></li>
</ul>
<p>All the above file formats suffer from several shortcomings, as explained in
the next sections &#8211; which is natural, as they were created for a different
purpose and at different times, before today&#8217;s sensing technologies and
algorithms had been invented.</p>
</div>
<div class="section" id="pcd-versions">
<h1>PCD versions</h1>
<p>PCD file formats might have different revision numbers, prior to the release of
Point Cloud Library (PCL) version 1.0. These are numbered with PCD_Vx (e.g.,
PCD_V5, PCD_V6, PCD_V7, etc) and represent version numbers 0.x for the PCD
file.</p>
<p>The official entry point for the PCD file format in PCL however should be
version <strong>0.7 (PCD_V7)</strong>.</p>
</div>
<div class="section" id="file-format-header">
<h1>File format header</h1>
<p>Each PCD file contains a header that identifies and declares certain properties
of the point cloud data stored in the file. The header of a PCD must be encoded
in ASCII.</p>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">Each header entry as well as ascii point data (see below) specified in a PCD
file, is separated using new lines (\n).</p>
</div>
<p>As of version 0.7, the PCD header contains the following entries:</p>
<ul>
<li><p class="first"><strong>VERSION</strong> - specifies the PCD file version</p>
</li>
<li><p class="first"><strong>FIELDS</strong> - specifies the name of each dimension/field that a point can
have. Examples:</p>
<div class="highlight-python"><div class="highlight"><pre>FIELDS x y z                                # XYZ data
FIELDS x y z rgb                            # XYZ + colors
FIELDS x y z normal_x normal_y normal_z     # XYZ + surface normals
FIELDS j1 j2 j3                             # moment invariants
...
</pre></div>
</div>
</li>
<li><p class="first"><strong>SIZE</strong> - specifies the size of each dimension in bytes. Examples:</p>
<ul class="simple">
<li><em>unsigned char</em>/<em>char</em> has 1 byte</li>
<li><em>unsigned short</em>/<em>short</em> has 2 bytes</li>
<li><em>unsigned int</em>/<em>int</em>/<em>float</em> has 4 bytes</li>
<li><em>double</em> has 8 bytes</li>
</ul>
</li>
<li><p class="first"><strong>TYPE</strong> - specifies the type of each dimension as a char. The current accepted types are:</p>
<ul class="simple">
<li><strong>I</strong> - represents signed types int8 (<em>char</em>), int16 (<em>short</em>), and int32 (<em>int</em>)</li>
<li><strong>U</strong> - represents unsigned types uint8 (<em>unsigned char</em>), uint16 (<em>unsigned short</em>), uint32 (<em>unsigned int</em>)</li>
<li><strong>F</strong> - represents float types</li>
</ul>
</li>
<li><p class="first"><strong>COUNT</strong> - specifies how many elements does each dimension have. For
example, <em>x</em> data usually has 1 element, but a feature descriptor like the
<em>VFH</em> has 308. Basically this is a way to introduce n-D histogram descriptors
at each point, and treating them as a single contiguous block of memory. By
default, if <em>COUNT</em> is not present, all dimensions&#8217; count is set to 1.</p>
</li>
<li><p class="first"><strong>WIDTH</strong> - specifies the width of the point cloud dataset in the number of
points. <em>WIDTH</em> has two meanings:</p>
<ul class="simple">
<li>it can specify the total number of points in the cloud (equal with <strong>POINTS</strong> see below) for unorganized datasets;</li>
<li>it can specify the width (total number of points in a row) of an organized point cloud dataset.</li>
</ul>
<p>Also see <strong>HEIGHT</strong>.</p>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">An <strong>organized point cloud</strong> dataset is the name given to point clouds that
resemble an organized image (or matrix) like structure, where the data is
split into rows and columns. Examples of such point clouds include data
coming from stereo cameras or Time Of Flight cameras. The advantages of a
organized dataset is that by knowing the relationship between adjacent
points (e.g. pixels), nearest neighbor operations are much more efficient,
thus speeding up the computation and lowering the costs of certain
algorithms in PCL.</p>
</div>
<p>Examples:</p>
<div class="highlight-python"><div class="highlight"><pre>WIDTH 640     # there are 640 points per line
</pre></div>
</div>
</li>
<li><p class="first"><strong>HEIGHT</strong> - specifies the height of the point cloud dataset in the number of points. <em>HEIGHT</em> has two meanings:</p>
<ul class="simple">
<li>it can specify the height (total number of rows) of an organized point cloud dataset;</li>
<li>it is set to <strong>1</strong> for unorganized datasets (<em>thus used to check whether a dataset is organized or not</em>).</li>
</ul>
<p>Example:</p>
<div class="highlight-python"><div class="highlight"><pre>WIDTH 640       # Image-like organized structure, with 640 rows and 480 columns,
HEIGHT 480      # thus 640*480=307200 points total in the dataset
</pre></div>
</div>
<p>Example:</p>
<div class="highlight-python"><div class="highlight"><pre>WIDTH 307200
HEIGHT 1        # unorganized point cloud dataset with 307200 points
</pre></div>
</div>
</li>
<li><p class="first"><strong>VIEWPOINT</strong> - specifies an acquisition viewpoint for the points in the
dataset. This could potentially be later on used for building transforms
between different coordinate systems, or for aiding with features such as
surface normals, that need a consistent orientation.</p>
<p>The viewpoint information is specified as a translation (tx ty tz) +
quaternion (qw qx qy qz). The default value is:</p>
<div class="highlight-python"><div class="highlight"><pre>VIEWPOINT 0 0 0 1 0 0 0
</pre></div>
</div>
</li>
<li><p class="first"><strong>POINTS</strong> - specifies the total number of points in the cloud. As of version
0.7, its purpose is a bit redundant, so we&#8217;re expecting this to be removed in
future versions.</p>
<p>Example:</p>
<div class="highlight-python"><div class="highlight"><pre>POINTS 307200   # the total number of points in the cloud
</pre></div>
</div>
</li>
<li><p class="first"><strong>DATA</strong> - specifies the data type that the point cloud data is stored in. As
of version 0.7, two data types are supported: <em>ascii</em> and <em>binary</em>. See the
next section for more details.</p>
</li>
</ul>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">The next bytes directly after the header&#8217;s last line (<strong>DATA</strong>) are
considered part of the point cloud data, and will be interpreted as such.</p>
</div>
<div class="admonition warning">
<p class="first admonition-title">Warning</p>
<p>The header entries must be specified <strong>precisely</strong> in the above order, that is:</p>
<div class="last highlight-python"><div class="highlight"><pre><span class="n">VERSION</span>
<span class="n">FIELDS</span>
<span class="n">SIZE</span>
<span class="n">TYPE</span>
<span class="n">COUNT</span>
<span class="n">WIDTH</span>
<span class="n">HEIGHT</span>
<span class="n">VIEWPOINT</span>
<span class="n">POINTS</span>
<span class="n">DATA</span>
</pre></div>
</div>
</div>
</div>
<div class="section" id="data-storage-types">
<h1>Data storage types</h1>
<p>As of version 0.7, the <strong>.PCD</strong> file format uses two different modes for storing data:</p>
<ul>
<li><p class="first">in <strong>ASCII</strong> form, with each point on a new line:</p>
<div class="highlight-python"><div class="highlight"><pre><span class="n">p_1</span>
<span class="n">p_2</span>
<span class="n">p_3</span>
<span class="n">p_4</span>
<span class="o">...</span>

<span class="n">p_n</span>
</pre></div>
</div>
</li>
</ul>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">Starting with PCL version 1.0.1 the string representation for NaN is &#8220;nan&#8221;.</p>
</div>
<ul class="simple">
<li>in <strong>binary</strong> form, where the data is a complete memory copy of the
<cite>pcl::PointCloud.points</cite> array/vector. On Linux systems, we use <cite>mmap</cite>/<cite>munmap</cite>
operations for the fastest possible read/write access to the data.</li>
</ul>
<p>Storing point cloud data in both a simple ascii form with each point on a line,
space or tab separated, without any other characters on it, as well as in a
binary dump format, allows us to have the best of both worlds: simplicity and
speed, depending on the underlying application. The ascii format allows users
to open up point cloud files and plot them using standard software tools like
<cite>gnuplot</cite> or manipulate them using tools like <cite>sed</cite>, <cite>awk</cite>, etc.</p>
</div>
<div class="section" id="advantages-over-other-file-formats">
<h1>Advantages over other file formats</h1>
<p>Having PCD as (yet another) file format can be seen as PCL suffering from the <cite>not invented here</cite> syndrome. In reality, this is not the case, as none of the above mentioned file formats offers the flexibility and speed of PCD files. Some of the clearly stated advantages include:</p>
<ul class="simple">
<li>the ability to store and process organized point cloud datasets &#8211; this is of
extreme importance for real time applications, and research areas such as
augmented reality, robotics, etc;</li>
<li>binary <cite>mmap</cite>/<cite>munmap</cite> data types are the fastest possible way of loading and
saving data to disk.</li>
<li>storing different data types (all primitives supported: char, short, int,
float, double) allows the point cloud data to be flexible and efficient with
respect to storage and processing. Invalid point dimensions are usually
stored as NAN types.</li>
<li>n-D histograms for feature descriptors &#8211; very important for 3D
perception/computer vision applications</li>
</ul>
<p>An additional advantage is that by controlling the file format, we can best
adapt it to PCL, and thus obtain the highest performance with respect to PCL
applications, rather than adapting a different file format to PCL as the native
type and inducing additional delays through conversion functions.</p>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">Though PCD (Point Cloud Data) is the <em>native</em> file format in PCL, the
<cite>pcl_io</cite> library should offer the possibility to save and load data in all
the other aforementioned file formats too.</p>
</div>
</div>
<div class="section" id="example">
<h1>Example</h1>
<p>A snippet of a PCD file is attached below. It is left to the reader to
interpret the data and see what it means. :) Have fun!:</p>
<div class="highlight-python"><div class="highlight"><pre># .PCD v.7 - Point Cloud Data file format
VERSION .7
FIELDS x y z rgb
SIZE 4 4 4 4
TYPE F F F F
COUNT 1 1 1 1
WIDTH 213
HEIGHT 1
VIEWPOINT 0 0 0 1 0 0 0
POINTS 213
DATA ascii
0.93773 0.33763 0 4.2108e+06
0.90805 0.35641 0 4.2108e+06
0.81915 0.32 0 4.2108e+06
0.97192 0.278 0 4.2108e+06
0.944 0.29474 0 4.2108e+06
0.98111 0.24247 0 4.2108e+06
0.93655 0.26143 0 4.2108e+06
0.91631 0.27442 0 4.2108e+06
0.81921 0.29315 0 4.2108e+06
0.90701 0.24109 0 4.2108e+06
0.83239 0.23398 0 4.2108e+06
0.99185 0.2116 0 4.2108e+06
0.89264 0.21174 0 4.2108e+06
0.85082 0.21212 0 4.2108e+06
0.81044 0.32222 0 4.2108e+06
0.74459 0.32192 0 4.2108e+06
0.69927 0.32278 0 4.2108e+06
0.8102 0.29315 0 4.2108e+06
0.75504 0.29765 0 4.2108e+06
0.8102 0.24399 0 4.2108e+06
0.74995 0.24723 0 4.2108e+06
0.68049 0.29768 0 4.2108e+06
0.66509 0.29002 0 4.2108e+06
0.69441 0.2526 0 4.2108e+06
0.62807 0.22187 0 4.2108e+06
0.58706 0.32199 0 4.2108e+06
0.52125 0.31955 0 4.2108e+06
0.49351 0.32282 0 4.2108e+06
0.44313 0.32169 0 4.2108e+06
0.58678 0.2929 0 4.2108e+06
0.53436 0.29164 0 4.2108e+06
0.59308 0.24134 0 4.2108e+06
0.5357 0.2444 0 4.2108e+06
0.50043 0.31235 0 4.2108e+06
0.44107 0.29711 0 4.2108e+06
0.50727 0.22193 0 4.2108e+06
0.43957 0.23976 0 4.2108e+06
0.8105 0.21112 0 4.2108e+06
0.73555 0.2114 0 4.2108e+06
0.69907 0.21082 0 4.2108e+06
0.63327 0.21154 0 4.2108e+06
0.59165 0.21201 0 4.2108e+06
0.52477 0.21491 0 4.2108e+06
0.49375 0.21006 0 4.2108e+06
0.4384 0.19632 0 4.2108e+06
0.43425 0.16052 0 4.2108e+06
0.3787 0.32173 0 4.2108e+06
0.33444 0.3216 0 4.2108e+06
0.23815 0.32199 0 4.808e+06
0.3788 0.29315 0 4.2108e+06
0.33058 0.31073 0 4.2108e+06
0.3788 0.24399 0 4.2108e+06
0.30249 0.29189 0 4.2108e+06
0.23492 0.29446 0 4.808e+06
0.29465 0.24399 0 4.2108e+06
0.23514 0.24172 0 4.808e+06
0.18836 0.32277 0 4.808e+06
0.15992 0.32176 0 4.808e+06
0.08642 0.32181 0 4.808e+06
0.039994 0.32283 0 4.808e+06
0.20039 0.31211 0 4.808e+06
0.1417 0.29506 0 4.808e+06
0.20921 0.22332 0 4.808e+06
0.13884 0.24227 0 4.808e+06
0.085123 0.29441 0 4.808e+06
0.048446 0.31279 0 4.808e+06
0.086957 0.24399 0 4.808e+06
0.3788 0.21189 0 4.2108e+06
0.29465 0.19323 0 4.2108e+06
0.23755 0.19348 0 4.808e+06
0.29463 0.16054 0 4.2108e+06
0.23776 0.16054 0 4.808e+06
0.19016 0.21038 0 4.808e+06
0.15704 0.21245 0 4.808e+06
0.08678 0.21169 0 4.808e+06
0.012746 0.32168 0 4.808e+06
-0.075715 0.32095 0 4.808e+06
-0.10622 0.32304 0 4.808e+06
-0.16391 0.32118 0 4.808e+06
0.00088411 0.29487 0 4.808e+06
-0.057568 0.29457 0 4.808e+06
-0.0034333 0.24399 0 4.808e+06
-0.055185 0.24185 0 4.808e+06
-0.10983 0.31352 0 4.808e+06
-0.15082 0.29453 0 4.808e+06
-0.11534 0.22049 0 4.808e+06
-0.15155 0.24381 0 4.808e+06
-0.1912 0.32173 0 4.808e+06
-0.281 0.3185 0 4.808e+06
-0.30791 0.32307 0 4.808e+06
-0.33854 0.32148 0 4.808e+06
-0.21248 0.29805 0 4.808e+06
-0.26372 0.29905 0 4.808e+06
-0.22562 0.24399 0 4.808e+06
-0.25035 0.2371 0 4.808e+06
-0.29941 0.31191 0 4.808e+06
-0.35845 0.2954 0 4.808e+06
-0.29231 0.22236 0 4.808e+06
-0.36101 0.24172 0 4.808e+06
-0.0034393 0.21129 0 4.808e+06
-0.07306 0.21304 0 4.808e+06
-0.10579 0.2099 0 4.808e+06
-0.13642 0.21411 0 4.808e+06
-0.22562 0.19323 0 4.808e+06
-0.24439 0.19799 0 4.808e+06
-0.22591 0.16041 0 4.808e+06
-0.23466 0.16082 0 4.808e+06
-0.3077 0.20998 0 4.808e+06
-0.3413 0.21239 0 4.808e+06
-0.40551 0.32178 0 4.2108e+06
-0.50568 0.3218 0 4.2108e+06
-0.41732 0.30844 0 4.2108e+06
-0.44237 0.28859 0 4.2108e+06
-0.41591 0.22004 0 4.2108e+06
-0.44803 0.24236 0 4.2108e+06
-0.50623 0.29315 0 4.2108e+06
-0.50916 0.24296 0 4.2108e+06
-0.57019 0.22334 0 4.2108e+06
-0.59611 0.32199 0 4.2108e+06
-0.65104 0.32199 0 4.2108e+06
-0.72566 0.32129 0 4.2108e+06
-0.75538 0.32301 0 4.2108e+06
-0.59653 0.29315 0 4.2108e+06
-0.65063 0.29315 0 4.2108e+06
-0.59478 0.24245 0 4.2108e+06
-0.65063 0.24399 0 4.2108e+06
-0.70618 0.29525 0 4.2108e+06
-0.76203 0.31284 0 4.2108e+06
-0.70302 0.24183 0 4.2108e+06
-0.77062 0.22133 0 4.2108e+06
-0.41545 0.21099 0 4.2108e+06
-0.45004 0.19812 0 4.2108e+06
-0.4475 0.1673 0 4.2108e+06
-0.52031 0.21236 0 4.2108e+06
-0.55182 0.21045 0 4.2108e+06
-0.5965 0.21131 0 4.2108e+06
-0.65064 0.2113 0 4.2108e+06
-0.72216 0.21286 0 4.2108e+06
-0.7556 0.20987 0 4.2108e+06
-0.78343 0.31973 0 4.2108e+06
-0.87572 0.32111 0 4.2108e+06
-0.90519 0.32263 0 4.2108e+06
-0.95526 0.34127 0 4.2108e+06
-0.79774 0.29271 0 4.2108e+06
-0.85618 0.29497 0 4.2108e+06
-0.79975 0.24326 0 4.2108e+06
-0.8521 0.24246 0 4.2108e+06
-0.91157 0.31224 0 4.2108e+06
-0.95031 0.29572 0 4.2108e+06
-0.92223 0.2213 0 4.2108e+06
-0.94979 0.24354 0 4.2108e+06
-0.78641 0.21505 0 4.2108e+06
-0.87094 0.21237 0 4.2108e+06
-0.90637 0.20934 0 4.2108e+06
-0.93777 0.21481 0 4.2108e+06
0.22244 -0.0296 0 4.808e+06
0.2704 -0.078167 0 4.808e+06
0.24416 -0.056883 0 4.808e+06
0.27311 -0.10653 0 4.808e+06
0.26172 -0.10653 0 4.808e+06
0.2704 -0.1349 0 4.808e+06
0.24428 -0.15599 0 4.808e+06
0.19017 -0.025297 0 4.808e+06
0.14248 -0.02428 0 4.808e+06
0.19815 -0.037432 0 4.808e+06
0.14248 -0.03515 0 4.808e+06
0.093313 -0.02428 0 4.808e+06
0.044144 -0.02428 0 4.808e+06
0.093313 -0.03515 0 4.808e+06
0.044144 -0.03515 0 4.808e+06
0.21156 -0.17357 0 4.808e+06
0.029114 -0.12594 0 4.2108e+06
0.036583 -0.15619 0 4.2108e+06
0.22446 -0.20514 0 4.808e+06
0.2208 -0.2369 0 4.808e+06
0.2129 -0.208 0 4.808e+06
0.19316 -0.25672 0 4.808e+06
0.14497 -0.27484 0 4.808e+06
0.030167 -0.18748 0 4.2108e+06
0.1021 -0.27453 0 4.808e+06
0.1689 -0.2831 0 4.808e+06
0.13875 -0.28647 0 4.808e+06
0.086993 -0.29568 0 4.808e+06
0.044924 -0.3154 0 4.808e+06
-0.0066125 -0.02428 0 4.808e+06
-0.057362 -0.02428 0 4.808e+06
-0.0066125 -0.03515 0 4.808e+06
-0.057362 -0.03515 0 4.808e+06
-0.10653 -0.02428 0 4.808e+06
-0.15266 -0.025282 0 4.808e+06
-0.10653 -0.03515 0 4.808e+06
-0.16036 -0.037257 0 4.808e+06
0.0083286 -0.1259 0 4.2108e+06
0.0007442 -0.15603 0 4.2108e+06
-0.1741 -0.17381 0 4.808e+06
-0.18502 -0.02954 0 4.808e+06
-0.20707 -0.056403 0 4.808e+06
-0.23348 -0.07764 0 4.808e+06
-0.2244 -0.10653 0 4.808e+06
-0.23604 -0.10652 0 4.808e+06
-0.20734 -0.15641 0 4.808e+06
-0.23348 -0.13542 0 4.808e+06
0.0061083 -0.18729 0 4.2108e+06
-0.066235 -0.27472 0 4.808e+06
-0.17577 -0.20789 0 4.808e+06
-0.10861 -0.27494 0 4.808e+06
-0.15584 -0.25716 0 4.808e+06
-0.0075775 -0.31546 0 4.808e+06
-0.050817 -0.29595 0 4.808e+06
-0.10306 -0.28653 0 4.808e+06
-0.1319 -0.2831 0 4.808e+06
-0.18716 -0.20571 0 4.808e+06
-0.18369 -0.23729 0 4.808e+06
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