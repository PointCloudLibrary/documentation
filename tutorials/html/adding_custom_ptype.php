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
    
    <title>Adding your own custom PointT type</title>
    
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
            
  <div class="section" id="adding-your-own-custom-pointt-type">
<span id="adding-custom-ptype"></span><h1><a class="toc-backref" href="#id1">Adding your own custom <cite>PointT</cite> type</a></h1>
<p>The current document explains not only how to add your own <cite>PointT</cite> point type,
but also what templated point types are in PCL, why do they exist, and how are
they exposed. If you&#8217;re already familiar with this information, feel free to
skip to the last part of the document.</p>
<div class="contents topic" id="contents">
<p class="topic-title first">Contents</p>
<ul class="simple">
<li><a class="reference internal" href="#adding-your-own-custom-pointt-type" id="id1">Adding your own custom <cite>PointT</cite> type</a></li>
<li><a class="reference internal" href="#why-pointt-types" id="id2">Why <cite>PointT</cite> types</a></li>
<li><a class="reference internal" href="#what-pointt-types-are-available-in-pcl" id="id3">What <cite>PointT</cite> types are available in PCL?</a></li>
<li><a class="reference internal" href="#how-are-the-point-types-exposed" id="id4">How are the point types exposed?</a></li>
<li><a class="reference internal" href="#how-to-add-a-new-pointt-type" id="id5">How to add a new <cite>PointT</cite> type</a></li>
<li><a class="reference internal" href="#example" id="id6">Example</a></li>
</ul>
</div>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">The current document is valid only for PCL 0.x and 1.x. Note that at the time
of this writing we are expecting things to be changed in PCL 2.x.</p>
</div>
<p>PCL comes with a variety of pre-defined point types, ranging from SSE-aligned
structures for XYZ data, to more complex n-dimensional histogram
representations such as PFH (Point Feature Histograms). These types should be
enough to support all the algorithms and methods implemented in PCL. However,
there are cases where users would like to define new types. This document
describes the steps involved in defining your own custom PointT type and making
sure that your project can be compiled successfully and ran.</p>
</div>
<div class="section" id="why-pointt-types">
<h1><a class="toc-backref" href="#id2">Why <cite>PointT</cite> types</a></h1>
<p>PCL&#8217;s <cite>PointT</cite> legacy goes back to the days where it was a library developed
within <a class="reference external" href="http://www.ros.org">ROS</a>. The consensus then was that a <em>Point Cloud</em>
is a complicated <em>n-D</em> structure that needs to be able to represent different
types of information. However, the user should know and understand what types
of information need to be passed around, in order to make the code easier to
debug, think about optimizations, etc.</p>
<p>One example is represented by simple operations on <cite>XYZ</cite> data. The most
efficient way for SSE-enabled processors, is to store the 3 dimensions as
floats, followed by an extra float for padding:</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre>1
2
3
4
5
6
7</pre></div></td><td class="code"><div class="highlight"><pre><span class="k">struct</span> <span class="n">PointXYZ</span>
<span class="p">{</span>
  <span class="kt">float</span> <span class="n">x</span><span class="p">;</span>
  <span class="kt">float</span> <span class="n">y</span><span class="p">;</span>
  <span class="kt">float</span> <span class="n">z</span><span class="p">;</span>
  <span class="kt">float</span> <span class="n">padding</span><span class="p">;</span>
<span class="p">};</span>
</pre></div>
</td></tr></table></div>
<p>As an example however, in case an user is looking at compiling PCL on an
embedded platform, adding the extra padding can be a waste of memory.
Therefore, a simpler <cite>PointXYZ</cite> structure without the last float could be used
instead.</p>
<p>Moreover, if your application requires a <cite>PointXYZRGBNormal</cite> which contains
<cite>XYZ</cite> 3D data, <cite>RGB</cite> information (colors), and surface normals estimated at
each point, it is trivial to define a structure with all the above. Since all
algorithms in PCL should be templated, there are no other changes required
other than your structure definition.</p>
</div>
<div class="section" id="what-pointt-types-are-available-in-pcl">
<h1><a class="toc-backref" href="#id3">What <cite>PointT</cite> types are available in PCL?</a></h1>
<p>To cover all possible cases that we could think of, we defined a plethora of
point types in PCL. The following might be only a snippet, please see
<a class="reference external" href="https://github.com/PointCloudLibrary/pcl/blob/master/common/include/pcl/impl/point_types.hpp">point_types.hpp</a>
for the complete list.</p>
<p>This list is important, because before defining your own custom type, you need
to understand why the existing types were created they way they were. In
addition, the type that you want, might already be defined for you.</p>
<ul>
<li><p class="first"><cite>PointXYZ</cite> - Members: float x, y, z;</p>
<p>This is one of the most used data types, as it represents 3D xyz information
only. The 3 floats are padded with an additional float for SSE alignment. The
user can either access <cite>points[i].data[0]</cite> or <cite>points[i].x</cite> for accessing
say, the x coordinate.</p>
</li>
</ul>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">union</span>
<span class="p">{</span>
  <span class="kt">float</span> <span class="n">data</span><span class="p">[</span><span class="mi">4</span><span class="p">];</span>
  <span class="k">struct</span>
  <span class="p">{</span>
    <span class="kt">float</span> <span class="n">x</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">y</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">z</span><span class="p">;</span>
  <span class="p">};</span>
<span class="p">};</span>
</pre></div>
</div>
<ul>
<li><p class="first"><cite>PointXYZI</cite> - Members: float x, y, z, intensity;</p>
<p>Simple XYZ + intensity point type. In an ideal world, these 4 components
would create a single structure, SSE-aligned. However, because the majority
of point operations will either set the last component of the <cite>data[4]</cite> array
(from the xyz union) to 0 or 1 (for transformations), we cannot make
<cite>intensity</cite> a member of the same structure, as its contents will be
overwritten. For example, a dot product between two points will set their 4th
component to 0, otherwise the dot product doesn&#8217;t make sense, etc.</p>
<p>Therefore for SSE-alignment, we pad intensity with 3 extra floats.
Inefficient in terms of storage, but good in terms of memory alignment.</p>
</li>
</ul>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">union</span>
<span class="p">{</span>
  <span class="kt">float</span> <span class="n">data</span><span class="p">[</span><span class="mi">4</span><span class="p">];</span>
  <span class="k">struct</span>
  <span class="p">{</span>
    <span class="kt">float</span> <span class="n">x</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">y</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">z</span><span class="p">;</span>
  <span class="p">};</span>
<span class="p">};</span>
<span class="k">union</span>
<span class="p">{</span>
  <span class="k">struct</span>
  <span class="p">{</span>
    <span class="kt">float</span> <span class="n">intensity</span><span class="p">;</span>
  <span class="p">};</span>
  <span class="kt">float</span> <span class="n">data_c</span><span class="p">[</span><span class="mi">4</span><span class="p">];</span>
<span class="p">};</span>
</pre></div>
</div>
<ul>
<li><p class="first"><cite>PointXYZRGBA</cite> - Members: float x, y, z; uint32_t rgba;</p>
<p>Similar to <cite>PointXYZI</cite>, except <cite>rgba</cite> containts the RGBA information packed
into a single integer.</p>
</li>
</ul>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">union</span>
<span class="p">{</span>
  <span class="kt">float</span> <span class="n">data</span><span class="p">[</span><span class="mi">4</span><span class="p">];</span>
  <span class="k">struct</span>
  <span class="p">{</span>
    <span class="kt">float</span> <span class="n">x</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">y</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">z</span><span class="p">;</span>
  <span class="p">};</span>
<span class="p">};</span>
<span class="k">union</span>
<span class="p">{</span>
  <span class="k">struct</span>
  <span class="p">{</span>
    <span class="kt">uint32_t</span> <span class="n">rgba</span><span class="p">;</span>
  <span class="p">};</span>
  <span class="kt">float</span> <span class="n">data_c</span><span class="p">[</span><span class="mi">4</span><span class="p">];</span>
<span class="p">};</span>
</pre></div>
</div>
<ul>
<li><p class="first"><cite>PointXYZRGB</cite> - float x, y, z, rgb;</p>
<p>Similar to <cite>PointXYZRGBA</cite>, except <cite>rgb</cite> represents the RGBA information packed into a float.</p>
</li>
</ul>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">The reason why <cite>rgb</cite> data is being packed as a float comes from the early
development of PCL as part of the ROS project, where RGB data is still being
sent by wire as float numbers. We expect this data type to be dropped as
soon as all legacy code has been rewritten (most likely in PCL 2.x).</p>
</div>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">union</span>
<span class="p">{</span>
  <span class="kt">float</span> <span class="n">data</span><span class="p">[</span><span class="mi">4</span><span class="p">];</span>
  <span class="k">struct</span>
  <span class="p">{</span>
    <span class="kt">float</span> <span class="n">x</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">y</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">z</span><span class="p">;</span>
  <span class="p">};</span>
<span class="p">};</span>
<span class="k">union</span>
<span class="p">{</span>
  <span class="k">struct</span>
  <span class="p">{</span>
    <span class="kt">float</span> <span class="n">rgb</span><span class="p">;</span>
  <span class="p">};</span>
  <span class="kt">float</span> <span class="n">data_c</span><span class="p">[</span><span class="mi">4</span><span class="p">];</span>
<span class="p">};</span>
</pre></div>
</div>
<ul>
<li><p class="first"><cite>PointXY</cite> - float x, y;</p>
<p>Simple 2D x-y point structure.</p>
</li>
</ul>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">struct</span>
<span class="p">{</span>
  <span class="kt">float</span> <span class="n">x</span><span class="p">;</span>
  <span class="kt">float</span> <span class="n">y</span><span class="p">;</span>
<span class="p">};</span>
</pre></div>
</div>
<ul>
<li><p class="first"><cite>InterestPoint</cite> - float x, y, z, strength;</p>
<p>Similar to <cite>PointXYZI</cite>, except <cite>strength</cite> containts a measure of the strength
of the keypoint.</p>
</li>
</ul>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">union</span>
<span class="p">{</span>
  <span class="kt">float</span> <span class="n">data</span><span class="p">[</span><span class="mi">4</span><span class="p">];</span>
  <span class="k">struct</span>
  <span class="p">{</span>
    <span class="kt">float</span> <span class="n">x</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">y</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">z</span><span class="p">;</span>
  <span class="p">};</span>
<span class="p">};</span>
<span class="k">union</span>
<span class="p">{</span>
  <span class="k">struct</span>
  <span class="p">{</span>
    <span class="kt">float</span> <span class="n">strength</span><span class="p">;</span>
  <span class="p">};</span>
  <span class="kt">float</span> <span class="n">data_c</span><span class="p">[</span><span class="mi">4</span><span class="p">];</span>
<span class="p">};</span>
</pre></div>
</div>
<ul>
<li><p class="first"><cite>Normal</cite> - float normal[3], curvature;</p>
<p>One of the other most used data types, the <cite>Normal</cite> structure represents the
surface normal at a given point, and a measure of curvature (which is
obtained in the same call as a relationship between the eigenvalues of a
surface patch &#8211; see the <cite>NormalEstimation</cite> class API for more information).</p>
<p>Because operation on surface normals are quite common in PCL, we pad the 3
components with a fourth one, in order to be SSE-aligned and computationally
efficient. The user can either access <cite>points[i].data_n[0]</cite> or
<cite>points[i].normal[0]</cite> or <cite>points[i].normal_x</cite> for accessing say, the first
coordinate of the normal vector. Again, <cite>curvature</cite> cannot be stored in the
same structure as it would be overwritten by operations on the normal data.</p>
</li>
</ul>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">union</span>
<span class="p">{</span>
  <span class="kt">float</span> <span class="n">data_n</span><span class="p">[</span><span class="mi">4</span><span class="p">];</span>
  <span class="kt">float</span> <span class="n">normal</span><span class="p">[</span><span class="mi">3</span><span class="p">];</span>
  <span class="k">struct</span>
  <span class="p">{</span>
    <span class="kt">float</span> <span class="n">normal_x</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">normal_y</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">normal_z</span><span class="p">;</span>
  <span class="p">};</span>
<span class="p">}</span>
<span class="k">union</span>
<span class="p">{</span>
  <span class="k">struct</span>
  <span class="p">{</span>
    <span class="kt">float</span> <span class="n">curvature</span><span class="p">;</span>
  <span class="p">};</span>
  <span class="kt">float</span> <span class="n">data_c</span><span class="p">[</span><span class="mi">4</span><span class="p">];</span>
<span class="p">};</span>
</pre></div>
</div>
<ul>
<li><p class="first"><cite>PointNormal</cite> - float x, y, z; float normal[3], curvature;</p>
<p>A point structure that holds XYZ data, together with surface normals and
curvatures.</p>
</li>
</ul>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">union</span>
<span class="p">{</span>
  <span class="kt">float</span> <span class="n">data</span><span class="p">[</span><span class="mi">4</span><span class="p">];</span>
  <span class="k">struct</span>
  <span class="p">{</span>
    <span class="kt">float</span> <span class="n">x</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">y</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">z</span><span class="p">;</span>
  <span class="p">};</span>
<span class="p">};</span>
<span class="k">union</span>
<span class="p">{</span>
  <span class="kt">float</span> <span class="n">data_n</span><span class="p">[</span><span class="mi">4</span><span class="p">];</span>
  <span class="kt">float</span> <span class="n">normal</span><span class="p">[</span><span class="mi">3</span><span class="p">];</span>
  <span class="k">struct</span>
  <span class="p">{</span>
    <span class="kt">float</span> <span class="n">normal_x</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">normal_y</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">normal_z</span><span class="p">;</span>
  <span class="p">};</span>
<span class="p">};</span>
<span class="k">union</span>
<span class="p">{</span>
  <span class="k">struct</span>
  <span class="p">{</span>
    <span class="kt">float</span> <span class="n">curvature</span><span class="p">;</span>
  <span class="p">};</span>
  <span class="kt">float</span> <span class="n">data_c</span><span class="p">[</span><span class="mi">4</span><span class="p">];</span>
<span class="p">};</span>
</pre></div>
</div>
<ul>
<li><p class="first"><cite>PointXYZRGBNormal</cite> - float x, y, z, rgb, normal[3], curvature;</p>
<p>A point structure that holds XYZ data, and RGB colors, together with surface
normals and curvatures.</p>
</li>
</ul>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">union</span>
<span class="p">{</span>
  <span class="kt">float</span> <span class="n">data</span><span class="p">[</span><span class="mi">4</span><span class="p">];</span>
  <span class="k">struct</span>
  <span class="p">{</span>
    <span class="kt">float</span> <span class="n">x</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">y</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">z</span><span class="p">;</span>
  <span class="p">};</span>
<span class="p">};</span>
<span class="k">union</span>
<span class="p">{</span>
  <span class="kt">float</span> <span class="n">data_n</span><span class="p">[</span><span class="mi">4</span><span class="p">];</span>
  <span class="kt">float</span> <span class="n">normal</span><span class="p">[</span><span class="mi">3</span><span class="p">];</span>
  <span class="k">struct</span>
  <span class="p">{</span>
    <span class="kt">float</span> <span class="n">normal_x</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">normal_y</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">normal_z</span><span class="p">;</span>
  <span class="p">};</span>
<span class="p">}</span>
<span class="k">union</span>
<span class="p">{</span>
  <span class="k">struct</span>
  <span class="p">{</span>
    <span class="kt">float</span> <span class="n">rgb</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">curvature</span><span class="p">;</span>
  <span class="p">};</span>
  <span class="kt">float</span> <span class="n">data_c</span><span class="p">[</span><span class="mi">4</span><span class="p">];</span>
<span class="p">};</span>
</pre></div>
</div>
<ul>
<li><p class="first"><cite>PointXYZINormal</cite> - float x, y, z, intensity, normal[3], curvature;</p>
<p>A point structure that holds XYZ data, and intensity values, together with
surface normals and curvatures.</p>
</li>
</ul>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">union</span>
<span class="p">{</span>
  <span class="kt">float</span> <span class="n">data</span><span class="p">[</span><span class="mi">4</span><span class="p">];</span>
  <span class="k">struct</span>
  <span class="p">{</span>
    <span class="kt">float</span> <span class="n">x</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">y</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">z</span><span class="p">;</span>
  <span class="p">};</span>
<span class="p">};</span>
<span class="k">union</span>
<span class="p">{</span>
  <span class="kt">float</span> <span class="n">data_n</span><span class="p">[</span><span class="mi">4</span><span class="p">];</span>
  <span class="kt">float</span> <span class="n">normal</span><span class="p">[</span><span class="mi">3</span><span class="p">];</span>
  <span class="k">struct</span>
  <span class="p">{</span>
    <span class="kt">float</span> <span class="n">normal_x</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">normal_y</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">normal_z</span><span class="p">;</span>
  <span class="p">};</span>
<span class="p">}</span>
<span class="k">union</span>
<span class="p">{</span>
  <span class="k">struct</span>
  <span class="p">{</span>
    <span class="kt">float</span> <span class="n">intensity</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">curvature</span><span class="p">;</span>
  <span class="p">};</span>
  <span class="kt">float</span> <span class="n">data_c</span><span class="p">[</span><span class="mi">4</span><span class="p">];</span>
<span class="p">};</span>
</pre></div>
</div>
<ul>
<li><p class="first"><cite>PointWithRange</cite> - float x, y, z (union with float point[4]), range;</p>
<p>Similar to <cite>PointXYZI</cite>, except <cite>range</cite> containts a measure of the distance
from the acqusition viewpoint to the point in the world.</p>
</li>
</ul>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">union</span>
<span class="p">{</span>
  <span class="kt">float</span> <span class="n">data</span><span class="p">[</span><span class="mi">4</span><span class="p">];</span>
  <span class="k">struct</span>
  <span class="p">{</span>
    <span class="kt">float</span> <span class="n">x</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">y</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">z</span><span class="p">;</span>
  <span class="p">};</span>
<span class="p">};</span>
<span class="k">union</span>
<span class="p">{</span>
  <span class="k">struct</span>
  <span class="p">{</span>
    <span class="kt">float</span> <span class="n">range</span><span class="p">;</span>
  <span class="p">};</span>
  <span class="kt">float</span> <span class="n">data_c</span><span class="p">[</span><span class="mi">4</span><span class="p">];</span>
<span class="p">};</span>
</pre></div>
</div>
<ul>
<li><p class="first"><cite>PointWithViewpoint</cite> - float x, y, z, vp_x, vp_y, vp_z;</p>
<p>Similar to <cite>PointXYZI</cite>, except <cite>vp_x</cite>, <cite>vp_y</cite>, and <cite>vp_z</cite> containt the
acquisition viewpoint as a 3D point.</p>
</li>
</ul>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">union</span>
<span class="p">{</span>
  <span class="kt">float</span> <span class="n">data</span><span class="p">[</span><span class="mi">4</span><span class="p">];</span>
  <span class="k">struct</span>
  <span class="p">{</span>
    <span class="kt">float</span> <span class="n">x</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">y</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">z</span><span class="p">;</span>
  <span class="p">};</span>
<span class="p">};</span>
<span class="k">union</span>
<span class="p">{</span>
  <span class="k">struct</span>
  <span class="p">{</span>
    <span class="kt">float</span> <span class="n">vp_x</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">vp_y</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">vp_z</span><span class="p">;</span>
  <span class="p">};</span>
  <span class="kt">float</span> <span class="n">data_c</span><span class="p">[</span><span class="mi">4</span><span class="p">];</span>
<span class="p">};</span>
</pre></div>
</div>
<ul>
<li><p class="first"><cite>MomentInvariants</cite> - float j1, j2, j3;</p>
<p>Simple point type holding the 3 moment invariants at a surface patch. See
<cite>MomentInvariantsEstimation</cite> for more information.</p>
</li>
</ul>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">struct</span>
<span class="p">{</span>
  <span class="kt">float</span> <span class="n">j1</span><span class="p">,</span> <span class="n">j2</span><span class="p">,</span> <span class="n">j3</span><span class="p">;</span>
<span class="p">};</span>
</pre></div>
</div>
<ul>
<li><p class="first"><cite>PrincipalRadiiRSD</cite> - float r_min, r_max;</p>
<p>Simple point type holding the 2 RSD radii at a surface patch. See
<cite>RSDEstimation</cite> for more information.</p>
</li>
</ul>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">struct</span>
<span class="p">{</span>
  <span class="kt">float</span> <span class="n">r_min</span><span class="p">,</span> <span class="n">r_max</span><span class="p">;</span>
<span class="p">};</span>
</pre></div>
</div>
<ul>
<li><p class="first"><cite>Boundary</cite> - uint8_t boundary_point;</p>
<p>Simple point type holding whether the point is lying on a surface boundary or
not. See <cite>BoundaryEstimation</cite> for more information.</p>
</li>
</ul>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">struct</span>
<span class="p">{</span>
  <span class="kt">uint8_t</span> <span class="n">boundary_point</span><span class="p">;</span>
<span class="p">};</span>
</pre></div>
</div>
<ul>
<li><p class="first"><cite>PrincipalCurvatures</cite> - float principal_curvature[3], pc1, pc2;</p>
<p>Simple point type holding the principal curvatures of a given point. See
<cite>PrincipalCurvaturesEstimation</cite> for more information.</p>
</li>
</ul>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">struct</span>
<span class="p">{</span>
  <span class="k">union</span>
  <span class="p">{</span>
    <span class="kt">float</span> <span class="n">principal_curvature</span><span class="p">[</span><span class="mi">3</span><span class="p">];</span>
    <span class="k">struct</span>
    <span class="p">{</span>
      <span class="kt">float</span> <span class="n">principal_curvature_x</span><span class="p">;</span>
      <span class="kt">float</span> <span class="n">principal_curvature_y</span><span class="p">;</span>
      <span class="kt">float</span> <span class="n">principal_curvature_z</span><span class="p">;</span>
    <span class="p">};</span>
  <span class="p">};</span>
  <span class="kt">float</span> <span class="n">pc1</span><span class="p">;</span>
  <span class="kt">float</span> <span class="n">pc2</span><span class="p">;</span>
<span class="p">};</span>
</pre></div>
</div>
<ul>
<li><p class="first"><cite>PFHSignature125</cite> - float pfh[125];</p>
<p>Simple point type holding the PFH (Point Feature Histogram) of a given point.
See <cite>PFHEstimation</cite> for more information.</p>
</li>
</ul>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">struct</span>
<span class="p">{</span>
  <span class="kt">float</span> <span class="n">histogram</span><span class="p">[</span><span class="mi">125</span><span class="p">];</span>
<span class="p">};</span>
</pre></div>
</div>
<ul>
<li><p class="first"><cite>FPFHSignature33</cite> - float fpfh[33];</p>
<p>Simple point type holding the FPFH (Fast Point Feature Histogram) of a given
point.  See <cite>FPFHEstimation</cite> for more information.</p>
</li>
</ul>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">struct</span>
<span class="p">{</span>
  <span class="kt">float</span> <span class="n">histogram</span><span class="p">[</span><span class="mi">33</span><span class="p">];</span>
<span class="p">};</span>
</pre></div>
</div>
<ul>
<li><p class="first"><cite>VFHSignature308</cite> - float vfh[308];</p>
<p>Simple point type holding the VFH (Viewpoint Feature Histogram) of a given
point.  See <cite>VFHEstimation</cite> for more information.</p>
</li>
</ul>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">struct</span>
<span class="p">{</span>
  <span class="kt">float</span> <span class="n">histogram</span><span class="p">[</span><span class="mi">308</span><span class="p">];</span>
<span class="p">};</span>
</pre></div>
</div>
<ul>
<li><p class="first"><cite>Narf36</cite> - float x, y, z, roll, pitch, yaw; float descriptor[36];</p>
<p>Simple point type holding the NARF (Normally Aligned Radius Feature) of a given
point.  See <cite>NARFEstimation</cite> for more information.</p>
</li>
</ul>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">struct</span>
<span class="p">{</span>
  <span class="kt">float</span> <span class="n">x</span><span class="p">,</span> <span class="n">y</span><span class="p">,</span> <span class="n">z</span><span class="p">,</span> <span class="n">roll</span><span class="p">,</span> <span class="n">pitch</span><span class="p">,</span> <span class="n">yaw</span><span class="p">;</span>
  <span class="kt">float</span> <span class="n">descriptor</span><span class="p">[</span><span class="mi">36</span><span class="p">];</span>
<span class="p">};</span>
</pre></div>
</div>
<ul>
<li><p class="first"><cite>BorderDescription</cite> - int x, y; BorderTraits traits;</p>
<p>Simple point type holding the border type of a given point.  See
<cite>BorderEstimation</cite> for more information.</p>
</li>
</ul>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">struct</span>
<span class="p">{</span>
  <span class="kt">int</span> <span class="n">x</span><span class="p">,</span> <span class="n">y</span><span class="p">;</span>
  <span class="n">BorderTraits</span> <span class="n">traits</span><span class="p">;</span>
<span class="p">};</span>
</pre></div>
</div>
<ul>
<li><p class="first"><cite>IntensityGradient</cite> - float gradient[3];</p>
<p>Simple point type holding the intensity gradient of a given point.  See
<cite>IntensityGradientEstimation</cite> for more information.</p>
</li>
</ul>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">struct</span>
<span class="p">{</span>
  <span class="k">union</span>
  <span class="p">{</span>
    <span class="kt">float</span> <span class="n">gradient</span><span class="p">[</span><span class="mi">3</span><span class="p">];</span>
    <span class="k">struct</span>
    <span class="p">{</span>
      <span class="kt">float</span> <span class="n">gradient_x</span><span class="p">;</span>
      <span class="kt">float</span> <span class="n">gradient_y</span><span class="p">;</span>
      <span class="kt">float</span> <span class="n">gradient_z</span><span class="p">;</span>
    <span class="p">};</span>
  <span class="p">};</span>
<span class="p">};</span>
</pre></div>
</div>
<ul>
<li><p class="first"><cite>Histogram</cite> - float histogram[N];</p>
<blockquote>
<div><p>General purpose n-D histogram placeholder.</p>
</div></blockquote>
</li>
</ul>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">template</span> <span class="o">&lt;</span><span class="kt">int</span> <span class="n">N</span><span class="o">&gt;</span>
<span class="k">struct</span> <span class="n">Histogram</span>
<span class="p">{</span>
  <span class="kt">float</span> <span class="n">histogram</span><span class="p">[</span><span class="n">N</span><span class="p">];</span>
<span class="p">};</span>
</pre></div>
</div>
<ul>
<li><p class="first"><cite>PointWithScale</cite> - float x, y, z, scale;</p>
<p>Similar to <cite>PointXYZI</cite>, except <cite>scale</cite> containts the scale at which a certain
point was considered for a geometric operation (e.g. the radius of the sphere
for its nearest neighbors computation, the window size, etc).</p>
</li>
</ul>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">struct</span>
<span class="p">{</span>
  <span class="k">union</span>
  <span class="p">{</span>
    <span class="kt">float</span> <span class="n">data</span><span class="p">[</span><span class="mi">4</span><span class="p">];</span>
    <span class="k">struct</span>
    <span class="p">{</span>
      <span class="kt">float</span> <span class="n">x</span><span class="p">;</span>
      <span class="kt">float</span> <span class="n">y</span><span class="p">;</span>
      <span class="kt">float</span> <span class="n">z</span><span class="p">;</span>
    <span class="p">};</span>
  <span class="p">};</span>
  <span class="kt">float</span> <span class="n">scale</span><span class="p">;</span>
<span class="p">};</span>
</pre></div>
</div>
<ul>
<li><p class="first"><cite>PointSurfel</cite> - float x, y, z, normal[3], rgba, radius, confidence, curvature;</p>
<p>A complex point type containing XYZ data, surface normals, together with RGB
information, scale, confidence, and surface curvature.</p>
</li>
</ul>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">union</span>
<span class="p">{</span>
  <span class="kt">float</span> <span class="n">data</span><span class="p">[</span><span class="mi">4</span><span class="p">];</span>
  <span class="k">struct</span>
  <span class="p">{</span>
    <span class="kt">float</span> <span class="n">x</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">y</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">z</span><span class="p">;</span>
  <span class="p">};</span>
<span class="p">};</span>
<span class="k">union</span>
<span class="p">{</span>
  <span class="kt">float</span> <span class="n">data_n</span><span class="p">[</span><span class="mi">4</span><span class="p">];</span>
  <span class="kt">float</span> <span class="n">normal</span><span class="p">[</span><span class="mi">3</span><span class="p">];</span>
  <span class="k">struct</span>
  <span class="p">{</span>
    <span class="kt">float</span> <span class="n">normal_x</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">normal_y</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">normal_z</span><span class="p">;</span>
  <span class="p">};</span>
<span class="p">};</span>
<span class="k">union</span>
<span class="p">{</span>
  <span class="k">struct</span>
  <span class="p">{</span>
    <span class="kt">uint32_t</span> <span class="n">rgba</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">radius</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">confidence</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">curvature</span><span class="p">;</span>
  <span class="p">};</span>
  <span class="kt">float</span> <span class="n">data_c</span><span class="p">[</span><span class="mi">4</span><span class="p">];</span>
<span class="p">};</span>
</pre></div>
</div>
</div>
<div class="section" id="how-are-the-point-types-exposed">
<h1><a class="toc-backref" href="#id4">How are the point types exposed?</a></h1>
<p>Because of its large size, and because it&#8217;s a template library, including many
PCL algorithms in one source file can slow down the compilation process. At the
time of writing this document, most C++ compilers still haven&#8217;t been properly
optimized to deal with large sets of templated files, especially when
optimizations (<cite>-O2</cite> or <cite>-O3</cite>) are involved.</p>
<p>To speed up user code that includes and links against PCL, we are using
<em>explicit template instantiations</em>, by including all possible combinations in
which all algorithms could be called using the already defined point types from
PCL. This means that once PCL is compiled as a library, any user code will not
require to compile templated code, thus speeding up compile time. The trick
involves separating the templated implementations from the headers which
forward declare our classes and methods, and resolving at link time. Here&#8217;s a
fictitious example:</p>
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
15</pre></div></td><td class="code"><div class="highlight"><pre><span class="c1">// foo.h</span>

<span class="cp">#ifndef PCL_FOO_</span>
<span class="cp">#define PCL_FOO_</span>

<span class="k">template</span> <span class="o">&lt;</span><span class="k">typename</span> <span class="n">PointT</span><span class="o">&gt;</span>
<span class="k">class</span> <span class="nc">Foo</span>
<span class="p">{</span>
  <span class="nl">public:</span>
    <span class="kt">void</span>
    <span class="n">compute</span> <span class="p">(</span><span class="k">const</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="o">&amp;</span><span class="n">input</span><span class="p">,</span>
             <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="o">&amp;</span><span class="n">output</span><span class="p">);</span>
<span class="p">}</span>

<span class="cp">#endif </span><span class="c1">// PCL_FOO_</span>
</pre></div>
</td></tr></table></div>
<p>The above defines the header file which is usually included by all user code.
As we can see, we&#8217;re defining methods and classes, but we&#8217;re not implementing
anything yet.</p>
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
15</pre></div></td><td class="code"><div class="highlight"><pre><span class="c1">// impl/foo.hpp</span>

<span class="cp">#ifndef PCL_IMPL_FOO_</span>
<span class="cp">#define PCL_IMPL_FOO_</span>

<span class="cp">#include &quot;foo.h&quot;</span>

<span class="k">template</span> <span class="o">&lt;</span><span class="k">typename</span> <span class="n">PointT</span><span class="o">&gt;</span> <span class="kt">void</span>
<span class="n">Foo</span><span class="o">::</span><span class="n">compute</span> <span class="p">(</span><span class="k">const</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="o">&amp;</span><span class="n">input</span><span class="p">,</span>
              <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="o">&amp;</span><span class="n">output</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">output</span> <span class="o">=</span> <span class="n">input</span><span class="p">;</span>
<span class="p">}</span>

<span class="cp">#endif </span><span class="c1">// PCL_IMPL_FOO_</span>
</pre></div>
</td></tr></table></div>
<p>The above defines the actual template implementation of the method
<cite>Foo::compute</cite>. This should normally be hidden from user code.</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre>1
2
3
4
5
6
7
8
9</pre></div></td><td class="code"><div class="highlight"><pre><span class="c1">// foo.cpp</span>

<span class="cp">#include &quot;pcl/point_types.h&quot;</span>
<span class="cp">#include &quot;pcl/impl/instantiate.hpp&quot;</span>
<span class="cp">#include &quot;foo.h&quot;</span>
<span class="cp">#include &quot;impl/foo.hpp&quot;</span>

<span class="c1">// Instantiations of specific point types</span>
<span class="n">PCL_INSTANTIATE</span><span class="p">(</span><span class="n">Foo</span><span class="p">,</span> <span class="n">PCL_XYZ_POINT_TYPES</span><span class="p">));</span>
</pre></div>
</td></tr></table></div>
<p>And finally, the above shows the way the explicit instantiations are done in
PCL. The macro <cite>PCL_INSTANTIATE</cite> does nothing else but go over a given list of
types and creates an explicit instantiation for each. From <cite>pcl/include/pcl/impl/instantiate.hpp</cite>:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="c1">// PCL_INSTANTIATE: call to instantiate template TEMPLATE for all</span>
<span class="c1">// POINT_TYPES</span>

<span class="cp">#define PCL_INSTANTIATE_IMPL(r, TEMPLATE, POINT_TYPE) \</span>
<span class="cp">  BOOST_PP_CAT(PCL_INSTANTIATE_, TEMPLATE)(POINT_TYPE)</span>

<span class="cp">#define PCL_INSTANTIATE(TEMPLATE, POINT_TYPES)        \</span>
<span class="cp">  BOOST_PP_SEQ_FOR_EACH(PCL_INSTANTIATE_IMPL, TEMPLATE, POINT_TYPES);</span>
</pre></div>
</div>
<p>Where <cite>PCL_XYZ_POINT_TYPES</cite> is (from <cite>pcl/include/pcl/impl/point_types.hpp</cite>):</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="c1">// Define all point types that include XYZ data</span>
<span class="cp">#define PCL_XYZ_POINT_TYPES   \</span>
<span class="cp">  (pcl::PointXYZ)             \</span>
<span class="cp">  (pcl::PointXYZI)            \</span>
<span class="cp">  (pcl::PointXYZRGBA)         \</span>
<span class="cp">  (pcl::PointXYZRGB)          \</span>
<span class="cp">  (pcl::InterestPoint)        \</span>
<span class="cp">  (pcl::PointNormal)          \</span>
<span class="cp">  (pcl::PointXYZRGBNormal)    \</span>
<span class="cp">  (pcl::PointXYZINormal)      \</span>
<span class="cp">  (pcl::PointWithRange)       \</span>
<span class="cp">  (pcl::PointWithViewpoint)   \</span>
<span class="cp">  (pcl::PointWithScale)</span>
</pre></div>
</div>
<p>Basically, if you only want to explicitly instantiate <cite>Foo</cite> for
<cite>pcl::PointXYZ</cite>, you don&#8217;t need to use the macro, as something as simple as the
following would do:</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre>1
2
3
4
5
6
7
8</pre></div></td><td class="code"><div class="highlight"><pre><span class="c1">// foo.cpp</span>

<span class="cp">#include &quot;pcl/point_types.h&quot;</span>
<span class="cp">#include &quot;pcl/impl/instantiate.hpp&quot;</span>
<span class="cp">#include &quot;foo.h&quot;</span>
<span class="cp">#include &quot;impl/foo.hpp&quot;</span>

<span class="k">template</span> <span class="k">class</span> <span class="nc">Foo</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">;</span>
</pre></div>
</td></tr></table></div>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">For more information about explicit instantiations, please see <em>C++ Templates
- The Complete Guide</em>, by David Vandervoorde and Nicolai M. Josuttis.</p>
</div>
</div>
<div class="section" id="how-to-add-a-new-pointt-type">
<h1><a class="toc-backref" href="#id5">How to add a new <cite>PointT</cite> type</a></h1>
<p>To add a new point type, you first have to define it. For example:</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre>1
2
3
4</pre></div></td><td class="code"><div class="highlight"><pre><span class="k">struct</span> <span class="n">MyPointType</span>
<span class="p">{</span>
  <span class="kt">float</span> <span class="n">test</span><span class="p">;</span>
<span class="p">};</span>
</pre></div>
</td></tr></table></div>
<p>Then, you need to make sure your code includes the template header
implementation of the specific class/algorithm in PCL that you want your new
point type <cite>MyPointType</cite> to work with. For example, say you want to use
<cite>pcl::PassThrough</cite>. All you would have to do is:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="cp">#define PCL_NO_PRECOMPILE</span>
<span class="cp">#include &lt;pcl/filters/passthrough.h&gt;</span>
<span class="cp">#include &lt;pcl/filters/impl/passthrough.hpp&gt;</span>

<span class="c1">// the rest of the code goes here</span>
</pre></div>
</div>
<p>If your code is part of the library, which gets used by others, it might also
make sense to try to use explicit instantiations for your <cite>MyPointType</cite> types,
for any classes that you expose (from PCL our outside PCL).</p>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">Starting with PCL-1.7 you need to define PCL_NO_PRECOMPILE before you include
any PCL headers to include the templated algorithms as well.</p>
</div>
</div>
<div class="section" id="example">
<h1><a class="toc-backref" href="#id6">Example</a></h1>
<p>The following code snippet example creates a new point type that contains XYZ
data (SSE padded), together with a test float.</p>
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
34
35</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#define PCL_NO_PRECOMPILE</span>
<span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/point_cloud.h&gt;</span>
<span class="cp">#include &lt;pcl/io/pcd_io.h&gt;</span>

<span class="k">struct</span> <span class="n">MyPointType</span>
<span class="p">{</span>
  <span class="n">PCL_ADD_POINT4D</span><span class="p">;</span>                  <span class="c1">// preferred way of adding a XYZ+padding</span>
  <span class="kt">float</span> <span class="n">test</span><span class="p">;</span>
  <span class="n">EIGEN_MAKE_ALIGNED_OPERATOR_NEW</span>   <span class="c1">// make sure our new allocators are aligned</span>
<span class="p">}</span> <span class="n">EIGEN_ALIGN16</span><span class="p">;</span>                    <span class="c1">// enforce SSE padding for correct memory alignment</span>

<span class="n">POINT_CLOUD_REGISTER_POINT_STRUCT</span> <span class="p">(</span><span class="n">MyPointType</span><span class="p">,</span>           <span class="c1">// here we assume a XYZ + &quot;test&quot; (as fields)</span>
                                   <span class="p">(</span><span class="kt">float</span><span class="p">,</span> <span class="n">x</span><span class="p">,</span> <span class="n">x</span><span class="p">)</span>
                                   <span class="p">(</span><span class="kt">float</span><span class="p">,</span> <span class="n">y</span><span class="p">,</span> <span class="n">y</span><span class="p">)</span>
                                   <span class="p">(</span><span class="kt">float</span><span class="p">,</span> <span class="n">z</span><span class="p">,</span> <span class="n">z</span><span class="p">)</span>
                                   <span class="p">(</span><span class="kt">float</span><span class="p">,</span> <span class="n">test</span><span class="p">,</span> <span class="n">test</span><span class="p">)</span>
<span class="p">)</span>


<span class="kt">int</span>
<span class="n">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">MyPointType</span><span class="o">&gt;</span> <span class="n">cloud</span><span class="p">;</span>
  <span class="n">cloud</span><span class="p">.</span><span class="n">points</span><span class="p">.</span><span class="n">resize</span> <span class="p">(</span><span class="mi">2</span><span class="p">);</span>
  <span class="n">cloud</span><span class="p">.</span><span class="n">width</span> <span class="o">=</span> <span class="mi">2</span><span class="p">;</span>
  <span class="n">cloud</span><span class="p">.</span><span class="n">height</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>

  <span class="n">cloud</span><span class="p">.</span><span class="n">points</span><span class="p">[</span><span class="mi">0</span><span class="p">].</span><span class="n">test</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
  <span class="n">cloud</span><span class="p">.</span><span class="n">points</span><span class="p">[</span><span class="mi">1</span><span class="p">].</span><span class="n">test</span> <span class="o">=</span> <span class="mi">2</span><span class="p">;</span>
  <span class="n">cloud</span><span class="p">.</span><span class="n">points</span><span class="p">[</span><span class="mi">0</span><span class="p">].</span><span class="n">x</span> <span class="o">=</span> <span class="n">cloud</span><span class="p">.</span><span class="n">points</span><span class="p">[</span><span class="mi">0</span><span class="p">].</span><span class="n">y</span> <span class="o">=</span> <span class="n">cloud</span><span class="p">.</span><span class="n">points</span><span class="p">[</span><span class="mi">0</span><span class="p">].</span><span class="n">z</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="n">cloud</span><span class="p">.</span><span class="n">points</span><span class="p">[</span><span class="mi">1</span><span class="p">].</span><span class="n">x</span> <span class="o">=</span> <span class="n">cloud</span><span class="p">.</span><span class="n">points</span><span class="p">[</span><span class="mi">1</span><span class="p">].</span><span class="n">y</span> <span class="o">=</span> <span class="n">cloud</span><span class="p">.</span><span class="n">points</span><span class="p">[</span><span class="mi">1</span><span class="p">].</span><span class="n">z</span> <span class="o">=</span> <span class="mi">3</span><span class="p">;</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">savePCDFile</span> <span class="p">(</span><span class="s">&quot;test.pcd&quot;</span><span class="p">,</span> <span class="n">cloud</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
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