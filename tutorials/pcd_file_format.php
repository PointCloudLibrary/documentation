<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation - Point Cloud Library (PCL)</title>
</head>

<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8" />
    <title>The PCD (Point Cloud Data) file format &#8212; PCL 0.0 documentation</title>
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
<li><p><a class="reference external" href="http://en.wikipedia.org/wiki/PLY_(file_format)">PLY</a> - a polygon file format, developed at Stanford University by Turk et al</p></li>
<li><p><a class="reference external" href="http://en.wikipedia.org/wiki/STL_(file_format)">STL</a> - a file format native to the stereolithography CAD software created by 3D Systems</p></li>
<li><p><a class="reference external" href="http://en.wikipedia.org/wiki/Wavefront_.obj_file">OBJ</a> - a geometry definition file format first developed by Wavefront Technologies</p></li>
<li><p><a class="reference external" href="http://en.wikipedia.org/wiki/X3D">X3D</a> - the ISO standard XML-based file format for representing 3D computer graphics data</p></li>
<li><p><a class="reference external" href="http://en.wikipedia.org/wiki/Category:Graphics_file_formats">and many others</a></p></li>
</ul>
<p>All the above file formats suffer from several shortcomings, as explained in
the next sections – which is natural, as they were created for a different
purpose and at different times, before today’s sensing technologies and
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
<p class="admonition-title">Note</p>
<p>Each header entry as well as ascii point data (see below) specified in a PCD
file, is separated using new lines (\n).</p>
</div>
<p>As of version 0.7, the PCD header contains the following entries:</p>
<ul>
<li><p><strong>VERSION</strong> - specifies the PCD file version</p></li>
<li><p><strong>FIELDS</strong> - specifies the name of each dimension/field that a point can
have. Examples:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">FIELDS</span> <span class="n">x</span> <span class="n">y</span> <span class="n">z</span>                                <span class="c1"># XYZ data</span>
<span class="n">FIELDS</span> <span class="n">x</span> <span class="n">y</span> <span class="n">z</span> <span class="n">rgb</span>                            <span class="c1"># XYZ + colors</span>
<span class="n">FIELDS</span> <span class="n">x</span> <span class="n">y</span> <span class="n">z</span> <span class="n">normal_x</span> <span class="n">normal_y</span> <span class="n">normal_z</span>     <span class="c1"># XYZ + surface normals</span>
<span class="n">FIELDS</span> <span class="n">j1</span> <span class="n">j2</span> <span class="n">j3</span>                             <span class="c1"># moment invariants</span>
<span class="o">...</span>
</pre></div>
</div>
</li>
<li><p><strong>SIZE</strong> - specifies the size of each dimension in bytes. Examples:</p>
<ul class="simple">
<li><p><em>unsigned char</em>/<em>char</em> has 1 byte</p></li>
<li><p><em>unsigned short</em>/<em>short</em> has 2 bytes</p></li>
<li><p><em>unsigned int</em>/<em>int</em>/<em>float</em> has 4 bytes</p></li>
<li><p><em>double</em> has 8 bytes</p></li>
</ul>
</li>
<li><p><strong>TYPE</strong> - specifies the type of each dimension as a char. The current accepted types are:</p>
<ul class="simple">
<li><p><strong>I</strong> - represents signed types int8 (<em>char</em>), int16 (<em>short</em>), and int32 (<em>int</em>)</p></li>
<li><p><strong>U</strong> - represents unsigned types uint8 (<em>unsigned char</em>), uint16 (<em>unsigned short</em>), uint32 (<em>unsigned int</em>)</p></li>
<li><p><strong>F</strong> - represents float types</p></li>
</ul>
</li>
<li><p><strong>COUNT</strong> - specifies how many elements does each dimension have. For
example, <em>x</em> data usually has 1 element, but a feature descriptor like the
<em>VFH</em> has 308. Basically this is a way to introduce n-D histogram descriptors
at each point, and treating them as a single contiguous block of memory. By
default, if <em>COUNT</em> is not present, all dimensions’ count is set to 1.</p></li>
<li><p><strong>WIDTH</strong> - specifies the width of the point cloud dataset in the number of
points. <em>WIDTH</em> has two meanings:</p>
<ul class="simple">
<li><p>it can specify the total number of points in the cloud (equal with <strong>POINTS</strong> see below) for unorganized datasets;</p></li>
<li><p>it can specify the width (total number of points in a row) of an organized point cloud dataset.</p></li>
</ul>
<p>Also see <strong>HEIGHT</strong>.</p>
<div class="admonition note">
<p class="admonition-title">Note</p>
<p>An <strong>organized point cloud</strong> dataset is the name given to point clouds that
resemble an organized image (or matrix) like structure, where the data is
split into rows and columns. Examples of such point clouds include data
coming from stereo cameras or Time Of Flight cameras. The advantages of a
organized dataset is that by knowing the relationship between adjacent
points (e.g. pixels), nearest neighbor operations are much more efficient,
thus speeding up the computation and lowering the costs of certain
algorithms in PCL.</p>
</div>
<p>Examples:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">WIDTH</span> <span class="mi">640</span>     <span class="c1"># there are 640 points per line</span>
</pre></div>
</div>
</li>
<li><p><strong>HEIGHT</strong> - specifies the height of the point cloud dataset in the number of points. <em>HEIGHT</em> has two meanings:</p>
<ul class="simple">
<li><p>it can specify the height (total number of rows) of an organized point cloud dataset;</p></li>
<li><p>it is set to <strong>1</strong> for unorganized datasets (<em>thus used to check whether a dataset is organized or not</em>).</p></li>
</ul>
<p>Example:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">WIDTH</span> <span class="mi">640</span>       <span class="c1"># Image-like organized structure, with 480 rows and 640 columns,</span>
<span class="n">HEIGHT</span> <span class="mi">480</span>      <span class="c1"># thus 640*480=307200 points total in the dataset</span>
</pre></div>
</div>
<p>Example:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">WIDTH</span> <span class="mi">307200</span>
<span class="n">HEIGHT</span> <span class="mi">1</span>        <span class="c1"># unorganized point cloud dataset with 307200 points</span>
</pre></div>
</div>
</li>
<li><p><strong>VIEWPOINT</strong> - specifies an acquisition viewpoint for the points in the
dataset. This could potentially be later on used for building transforms
between different coordinate systems, or for aiding with features such as
surface normals, that need a consistent orientation.</p>
<p>The viewpoint information is specified as a translation (tx ty tz) +
quaternion (qw qx qy qz). The default value is:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">VIEWPOINT</span> <span class="mi">0</span> <span class="mi">0</span> <span class="mi">0</span> <span class="mi">1</span> <span class="mi">0</span> <span class="mi">0</span> <span class="mi">0</span>
</pre></div>
</div>
</li>
<li><p><strong>POINTS</strong> - specifies the total number of points in the cloud. As of version
0.7, its purpose is a bit redundant, so we’re expecting this to be removed in
future versions.</p>
<p>Example:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">POINTS</span> <span class="mi">307200</span>   <span class="c1"># the total number of points in the cloud</span>
</pre></div>
</div>
</li>
<li><p><strong>DATA</strong> - specifies the data type that the point cloud data is stored in. As
of version 0.7, two data types are supported: <em>ascii</em> and <em>binary</em>. See the
next section for more details.</p></li>
</ul>
<div class="admonition note">
<p class="admonition-title">Note</p>
<p>The next bytes directly after the header’s last line (<strong>DATA</strong>) are
considered part of the point cloud data, and will be interpreted as such.</p>
</div>
<div class="admonition warning">
<p class="admonition-title">Warning</p>
<p>The header entries must be specified <strong>precisely</strong> in the above order, that is:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">VERSION</span>
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
<li><p>in <strong>ASCII</strong> form, with each point on a new line:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">p_1</span>
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
<p class="admonition-title">Note</p>
<p>Starting with PCL version 1.0.1 the string representation for NaN is “nan”.</p>
</div>
<ul class="simple">
<li><p>in <strong>binary</strong> form, where the data is a complete memory copy of the
<cite>pcl::PointCloud.points</cite> array/vector. On Linux systems, we use <cite>mmap</cite>/<cite>munmap</cite>
operations for the fastest possible read/write access to the data.</p></li>
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
<li><p>the ability to store and process organized point cloud datasets – this is of
extreme importance for real time applications, and research areas such as
augmented reality, robotics, etc;</p></li>
<li><p>binary <cite>mmap</cite>/<cite>munmap</cite> data types are the fastest possible way of loading and
saving data to disk.</p></li>
<li><p>storing different data types (all primitives supported: char, short, int,
float, double) allows the point cloud data to be flexible and efficient with
respect to storage and processing. Invalid point dimensions are usually
stored as NAN types.</p></li>
<li><p>n-D histograms for feature descriptors – very important for 3D
perception/computer vision applications</p></li>
</ul>
<p>An additional advantage is that by controlling the file format, we can best
adapt it to PCL, and thus obtain the highest performance with respect to PCL
applications, rather than adapting a different file format to PCL as the native
type and inducing additional delays through conversion functions.</p>
<div class="admonition note">
<p class="admonition-title">Note</p>
<p>Though PCD (Point Cloud Data) is the <em>native</em> file format in PCL, the
<cite>pcl_io</cite> library should offer the possibility to save and load data in all
the other aforementioned file formats too.</p>
</div>
</div>
<div class="section" id="example">
<h1>Example</h1>
<p>A snippet of a PCD file is attached below. It is left to the reader to
interpret the data and see what it means. :) Have fun!:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="c1"># .PCD v.7 - Point Cloud Data file format</span>
<span class="n">VERSION</span> <span class="o">.</span><span class="mi">7</span>
<span class="n">FIELDS</span> <span class="n">x</span> <span class="n">y</span> <span class="n">z</span> <span class="n">rgb</span>
<span class="n">SIZE</span> <span class="mi">4</span> <span class="mi">4</span> <span class="mi">4</span> <span class="mi">4</span>
<span class="n">TYPE</span> <span class="n">F</span> <span class="n">F</span> <span class="n">F</span> <span class="n">F</span>
<span class="n">COUNT</span> <span class="mi">1</span> <span class="mi">1</span> <span class="mi">1</span> <span class="mi">1</span>
<span class="n">WIDTH</span> <span class="mi">213</span>
<span class="n">HEIGHT</span> <span class="mi">1</span>
<span class="n">VIEWPOINT</span> <span class="mi">0</span> <span class="mi">0</span> <span class="mi">0</span> <span class="mi">1</span> <span class="mi">0</span> <span class="mi">0</span> <span class="mi">0</span>
<span class="n">POINTS</span> <span class="mi">213</span>
<span class="n">DATA</span> <span class="n">ascii</span>
<span class="mf">0.93773</span> <span class="mf">0.33763</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.90805</span> <span class="mf">0.35641</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.81915</span> <span class="mf">0.32</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.97192</span> <span class="mf">0.278</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.944</span> <span class="mf">0.29474</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.98111</span> <span class="mf">0.24247</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.93655</span> <span class="mf">0.26143</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.91631</span> <span class="mf">0.27442</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.81921</span> <span class="mf">0.29315</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.90701</span> <span class="mf">0.24109</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.83239</span> <span class="mf">0.23398</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.99185</span> <span class="mf">0.2116</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.89264</span> <span class="mf">0.21174</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.85082</span> <span class="mf">0.21212</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.81044</span> <span class="mf">0.32222</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.74459</span> <span class="mf">0.32192</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.69927</span> <span class="mf">0.32278</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.8102</span> <span class="mf">0.29315</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.75504</span> <span class="mf">0.29765</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.8102</span> <span class="mf">0.24399</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.74995</span> <span class="mf">0.24723</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.68049</span> <span class="mf">0.29768</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.66509</span> <span class="mf">0.29002</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.69441</span> <span class="mf">0.2526</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.62807</span> <span class="mf">0.22187</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.58706</span> <span class="mf">0.32199</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.52125</span> <span class="mf">0.31955</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.49351</span> <span class="mf">0.32282</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.44313</span> <span class="mf">0.32169</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.58678</span> <span class="mf">0.2929</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.53436</span> <span class="mf">0.29164</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.59308</span> <span class="mf">0.24134</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.5357</span> <span class="mf">0.2444</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.50043</span> <span class="mf">0.31235</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.44107</span> <span class="mf">0.29711</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.50727</span> <span class="mf">0.22193</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.43957</span> <span class="mf">0.23976</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.8105</span> <span class="mf">0.21112</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.73555</span> <span class="mf">0.2114</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.69907</span> <span class="mf">0.21082</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.63327</span> <span class="mf">0.21154</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.59165</span> <span class="mf">0.21201</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.52477</span> <span class="mf">0.21491</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.49375</span> <span class="mf">0.21006</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.4384</span> <span class="mf">0.19632</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.43425</span> <span class="mf">0.16052</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.3787</span> <span class="mf">0.32173</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.33444</span> <span class="mf">0.3216</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.23815</span> <span class="mf">0.32199</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.3788</span> <span class="mf">0.29315</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.33058</span> <span class="mf">0.31073</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.3788</span> <span class="mf">0.24399</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.30249</span> <span class="mf">0.29189</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.23492</span> <span class="mf">0.29446</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.29465</span> <span class="mf">0.24399</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.23514</span> <span class="mf">0.24172</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.18836</span> <span class="mf">0.32277</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.15992</span> <span class="mf">0.32176</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.08642</span> <span class="mf">0.32181</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.039994</span> <span class="mf">0.32283</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.20039</span> <span class="mf">0.31211</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.1417</span> <span class="mf">0.29506</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.20921</span> <span class="mf">0.22332</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.13884</span> <span class="mf">0.24227</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.085123</span> <span class="mf">0.29441</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.048446</span> <span class="mf">0.31279</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.086957</span> <span class="mf">0.24399</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.3788</span> <span class="mf">0.21189</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.29465</span> <span class="mf">0.19323</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.23755</span> <span class="mf">0.19348</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.29463</span> <span class="mf">0.16054</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.23776</span> <span class="mf">0.16054</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.19016</span> <span class="mf">0.21038</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.15704</span> <span class="mf">0.21245</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.08678</span> <span class="mf">0.21169</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.012746</span> <span class="mf">0.32168</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.075715</span> <span class="mf">0.32095</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.10622</span> <span class="mf">0.32304</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.16391</span> <span class="mf">0.32118</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.00088411</span> <span class="mf">0.29487</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.057568</span> <span class="mf">0.29457</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.0034333</span> <span class="mf">0.24399</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.055185</span> <span class="mf">0.24185</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.10983</span> <span class="mf">0.31352</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.15082</span> <span class="mf">0.29453</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.11534</span> <span class="mf">0.22049</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.15155</span> <span class="mf">0.24381</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.1912</span> <span class="mf">0.32173</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.281</span> <span class="mf">0.3185</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.30791</span> <span class="mf">0.32307</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.33854</span> <span class="mf">0.32148</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.21248</span> <span class="mf">0.29805</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.26372</span> <span class="mf">0.29905</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.22562</span> <span class="mf">0.24399</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.25035</span> <span class="mf">0.2371</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.29941</span> <span class="mf">0.31191</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.35845</span> <span class="mf">0.2954</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.29231</span> <span class="mf">0.22236</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.36101</span> <span class="mf">0.24172</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.0034393</span> <span class="mf">0.21129</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.07306</span> <span class="mf">0.21304</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.10579</span> <span class="mf">0.2099</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.13642</span> <span class="mf">0.21411</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.22562</span> <span class="mf">0.19323</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.24439</span> <span class="mf">0.19799</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.22591</span> <span class="mf">0.16041</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.23466</span> <span class="mf">0.16082</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.3077</span> <span class="mf">0.20998</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.3413</span> <span class="mf">0.21239</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.40551</span> <span class="mf">0.32178</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.50568</span> <span class="mf">0.3218</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.41732</span> <span class="mf">0.30844</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.44237</span> <span class="mf">0.28859</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.41591</span> <span class="mf">0.22004</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.44803</span> <span class="mf">0.24236</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.50623</span> <span class="mf">0.29315</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.50916</span> <span class="mf">0.24296</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.57019</span> <span class="mf">0.22334</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.59611</span> <span class="mf">0.32199</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.65104</span> <span class="mf">0.32199</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.72566</span> <span class="mf">0.32129</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.75538</span> <span class="mf">0.32301</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.59653</span> <span class="mf">0.29315</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.65063</span> <span class="mf">0.29315</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.59478</span> <span class="mf">0.24245</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.65063</span> <span class="mf">0.24399</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.70618</span> <span class="mf">0.29525</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.76203</span> <span class="mf">0.31284</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.70302</span> <span class="mf">0.24183</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.77062</span> <span class="mf">0.22133</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.41545</span> <span class="mf">0.21099</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.45004</span> <span class="mf">0.19812</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.4475</span> <span class="mf">0.1673</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.52031</span> <span class="mf">0.21236</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.55182</span> <span class="mf">0.21045</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.5965</span> <span class="mf">0.21131</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.65064</span> <span class="mf">0.2113</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.72216</span> <span class="mf">0.21286</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.7556</span> <span class="mf">0.20987</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.78343</span> <span class="mf">0.31973</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.87572</span> <span class="mf">0.32111</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.90519</span> <span class="mf">0.32263</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.95526</span> <span class="mf">0.34127</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.79774</span> <span class="mf">0.29271</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.85618</span> <span class="mf">0.29497</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.79975</span> <span class="mf">0.24326</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.8521</span> <span class="mf">0.24246</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.91157</span> <span class="mf">0.31224</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.95031</span> <span class="mf">0.29572</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.92223</span> <span class="mf">0.2213</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.94979</span> <span class="mf">0.24354</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.78641</span> <span class="mf">0.21505</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.87094</span> <span class="mf">0.21237</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.90637</span> <span class="mf">0.20934</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.93777</span> <span class="mf">0.21481</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.22244</span> <span class="o">-</span><span class="mf">0.0296</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.2704</span> <span class="o">-</span><span class="mf">0.078167</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.24416</span> <span class="o">-</span><span class="mf">0.056883</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.27311</span> <span class="o">-</span><span class="mf">0.10653</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.26172</span> <span class="o">-</span><span class="mf">0.10653</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.2704</span> <span class="o">-</span><span class="mf">0.1349</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.24428</span> <span class="o">-</span><span class="mf">0.15599</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.19017</span> <span class="o">-</span><span class="mf">0.025297</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.14248</span> <span class="o">-</span><span class="mf">0.02428</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.19815</span> <span class="o">-</span><span class="mf">0.037432</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.14248</span> <span class="o">-</span><span class="mf">0.03515</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.093313</span> <span class="o">-</span><span class="mf">0.02428</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.044144</span> <span class="o">-</span><span class="mf">0.02428</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.093313</span> <span class="o">-</span><span class="mf">0.03515</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.044144</span> <span class="o">-</span><span class="mf">0.03515</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.21156</span> <span class="o">-</span><span class="mf">0.17357</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.029114</span> <span class="o">-</span><span class="mf">0.12594</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.036583</span> <span class="o">-</span><span class="mf">0.15619</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.22446</span> <span class="o">-</span><span class="mf">0.20514</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.2208</span> <span class="o">-</span><span class="mf">0.2369</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.2129</span> <span class="o">-</span><span class="mf">0.208</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.19316</span> <span class="o">-</span><span class="mf">0.25672</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.14497</span> <span class="o">-</span><span class="mf">0.27484</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.030167</span> <span class="o">-</span><span class="mf">0.18748</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.1021</span> <span class="o">-</span><span class="mf">0.27453</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.1689</span> <span class="o">-</span><span class="mf">0.2831</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.13875</span> <span class="o">-</span><span class="mf">0.28647</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.086993</span> <span class="o">-</span><span class="mf">0.29568</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.044924</span> <span class="o">-</span><span class="mf">0.3154</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.0066125</span> <span class="o">-</span><span class="mf">0.02428</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.057362</span> <span class="o">-</span><span class="mf">0.02428</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.0066125</span> <span class="o">-</span><span class="mf">0.03515</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.057362</span> <span class="o">-</span><span class="mf">0.03515</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.10653</span> <span class="o">-</span><span class="mf">0.02428</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.15266</span> <span class="o">-</span><span class="mf">0.025282</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.10653</span> <span class="o">-</span><span class="mf">0.03515</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.16036</span> <span class="o">-</span><span class="mf">0.037257</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.0083286</span> <span class="o">-</span><span class="mf">0.1259</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="mf">0.0007442</span> <span class="o">-</span><span class="mf">0.15603</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.1741</span> <span class="o">-</span><span class="mf">0.17381</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.18502</span> <span class="o">-</span><span class="mf">0.02954</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.20707</span> <span class="o">-</span><span class="mf">0.056403</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.23348</span> <span class="o">-</span><span class="mf">0.07764</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.2244</span> <span class="o">-</span><span class="mf">0.10653</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.23604</span> <span class="o">-</span><span class="mf">0.10652</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.20734</span> <span class="o">-</span><span class="mf">0.15641</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.23348</span> <span class="o">-</span><span class="mf">0.13542</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="mf">0.0061083</span> <span class="o">-</span><span class="mf">0.18729</span> <span class="mi">0</span> <span class="mf">4.2108e+06</span>
<span class="o">-</span><span class="mf">0.066235</span> <span class="o">-</span><span class="mf">0.27472</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.17577</span> <span class="o">-</span><span class="mf">0.20789</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.10861</span> <span class="o">-</span><span class="mf">0.27494</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.15584</span> <span class="o">-</span><span class="mf">0.25716</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.0075775</span> <span class="o">-</span><span class="mf">0.31546</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.050817</span> <span class="o">-</span><span class="mf">0.29595</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.10306</span> <span class="o">-</span><span class="mf">0.28653</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.1319</span> <span class="o">-</span><span class="mf">0.2831</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.18716</span> <span class="o">-</span><span class="mf">0.20571</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
<span class="o">-</span><span class="mf">0.18369</span> <span class="o">-</span><span class="mf">0.23729</span> <span class="mi">0</span> <span class="mf">4.808e+06</span>
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