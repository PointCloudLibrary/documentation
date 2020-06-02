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
    
    <title>Vertical SSE for PCL2.0</title>
    
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
            
  <div class="section" id="vertical-sse-for-pcl2-0">
<span id="vertical-sse"></span><h1>Vertical SSE for PCL2.0</h1>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">All code is available from
<a class="reference external" href="https://kforge.ros.org/projects/mihelich/services/pcl_simd/">https://kforge.ros.org/projects/mihelich/services/pcl_simd/</a>.</p>
</div>
<div class="section" id="representing-point-data">
<h2>Representing point data</h2>
<p>In PCL currently, points are stored with their fields interleaved. For the
simplest <a class="reference external" href="http://docs.pointclouds.org/trunk/structpcl_1_1_point_x_y_z.html">PointXYZ</a> type, this looks like:</p>
<div class="highlight-python"><div class="highlight"><pre>XYZ_XYZ_XYZ_XYZ_ ...
</pre></div>
</div>
<p>where <tt class="docutils literal"><span class="pre">_</span></tt> denotes an extra padding <tt class="docutils literal"><span class="pre">float</span></tt> so that each point is 16-byte
aligned. Operating on <tt class="docutils literal"><span class="pre">XYZ_</span></tt> data efficiently often requires the use of
<em>horizontal</em> SSE instructions, which perform computations using multiple
elements of the same SSE register.</p>
<p>This representation is also known as <em>Array-of-Structures</em> (AoS). <tt class="docutils literal"><span class="pre">PointXYZ</span></tt>
is defined as a struct, and all fields for an individual point are stored
together in memory.</p>
<p>Instead a <em>vertical</em> representation, aka <em>Structure-of-Arrays</em> (SoA), can be
used:</p>
<div class="highlight-python"><div class="highlight"><pre>XXXXXXXX ...
YYYYYYYY ...
ZZZZZZZZ ...
</pre></div>
</div>
<p>This layout fits traditional vertical SSE processing better. Most arithmetic
SSE instructions are binary operations on corresponding elements of two SSE
registers.</p>
</div>
<div class="section" id="why-does-pcl-use-aos">
<h2>Why does PCL use AoS?</h2>
<p>PCL&#8217;s use of AoS, normally non-optimal, does have its logic. In PCL, frequently
we wish to process only some (indexed / valid) subset of a point cloud. Besides
dense processing of all points, we then have two other cases.</p>
<div class="section" id="indexed-subsets">
<h3>Indexed subsets</h3>
<p>PCL operators routinely provide a <span>setIndices()</span> method, ordering them to use only certain points
identified by index. With the AoS representation, each individual point can be
used in an SSE register via a simple aligned load. Indexed access therefore
does not much complicate an SSE-optimized implementation.</p>
<p>Vertical SSE (in the dense case) processes four adjacent points simultaneously,
and indexed access breaks the adjacency requirement. Instead of an aligned
load, the implementation must &#8220;gather&#8221; the data for the next four indexed
points (spread out in memory).</p>
</div>
<div class="section" id="organized-point-clouds">
<h3>Organized point clouds</h3>
<p>PCL permits point clouds with missing data. For imager-based 3D sensors, this
allows point clouds to retain their 2D structure, making it trivial to identify
nearby points. Invalid points have each field set to NaN, so that it is clear
when invalid data is accidentally used in a computation.</p>
<p>Handling invalid points in PCL (with AoS) is again rather simple. For each
point, check if <tt class="docutils literal"><span class="pre">X</span></tt> is NaN; if so, ignore it.</p>
<p>The SoA situation is much more complicated. Since we operate on four points at
a time, we have to check if any of the four points are invalid. If so, it
becomes very tricky to use SSE at all without destroying our result. Masking
tricks are possible, but imply some overhead over the simple dense code.</p>
</div>
</div>
<div class="section" id="horizontal-or-vertical">
<h2>Horizontal or vertical?</h2>
<p>Both representations have pros and cons.</p>
<p><strong>Horizontal</strong></p>
<ul class="simple">
<li>Pros<ul>
<li>More intuitive, easier to write code for</li>
<li>Handling indexed subsets is simple - can still use aligned loads</li>
<li>Handling NaNs also simple</li>
</ul>
</li>
<li>Cons<ul>
<li>Clearly slower at dense processing</li>
<li>Waste space and computation on padding elements</li>
</ul>
</li>
</ul>
<p><strong>Vertical</strong></p>
<ul class="simple">
<li>Pros<ul>
<li>Clearly faster at dense processing</li>
<li>No wasted space - only 3/4 as many loads required</li>
<li>No wasted computation</li>
<li>May have less loop overhead, since you process 4 points per iteration
instead of 1</li>
</ul>
</li>
<li>Cons<ul>
<li>Less intuitive</li>
<li>Indexed subsets require gathering data for non-adjacent points</li>
<li>Handling NaNs is complicated</li>
</ul>
</li>
</ul>
</div>
<div class="section" id="data-structures">
<h2>Data structures</h2>
<p>For benchmarking, we define two very simple point cloud representations:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="c1">// Array-of-Structures</span>
<span class="k">struct</span> <span class="n">AOS</span>
<span class="p">{</span>
  <span class="kt">float</span> <span class="n">x</span><span class="p">;</span>
  <span class="kt">float</span> <span class="n">y</span><span class="p">;</span>
  <span class="kt">float</span> <span class="n">z</span><span class="p">;</span>
  <span class="kt">float</span> <span class="n">h</span><span class="p">;</span>
<span class="p">};</span>

<span class="c1">// Structure-of-Arrays</span>
<span class="k">struct</span> <span class="n">SOA</span>
<span class="p">{</span>
  <span class="kt">float</span><span class="o">*</span> <span class="n">x</span><span class="p">;</span>
  <span class="kt">float</span><span class="o">*</span> <span class="n">y</span><span class="p">;</span>
  <span class="kt">float</span><span class="o">*</span> <span class="n">z</span><span class="p">;</span>
  <span class="kt">size_t</span> <span class="n">size</span><span class="p">;</span>
<span class="p">};</span>
</pre></div>
</div>
</div>
<div class="section" id="computations-considered">
<h2>Computations considered</h2>
<p>We benchmark two basic operations:</p>
<ul class="simple">
<li>Compute the dot product of every point in a cloud with a given point</li>
<li>Compute the centroid of a point cloud</li>
</ul>
<p>For both operations, we implemented several versions covering the space of:</p>
<ul class="simple">
<li>Horizontal (AoS) or vertical (SoA)</li>
<li>Dense or indexed</li>
<li>SSE instruction set</li>
</ul>
<p>Representative examples are listed below.</p>
<div class="section" id="dot-product">
<h3>Dot product</h3>
<p>Vertical (SoA), SSE2-optimized:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">void</span> <span class="nf">dotSSE2</span> <span class="p">(</span><span class="k">const</span> <span class="n">SOA</span><span class="o">&amp;</span> <span class="n">vectors</span><span class="p">,</span> <span class="k">const</span> <span class="n">AOS</span><span class="o">&amp;</span> <span class="n">vector</span><span class="p">,</span>
              <span class="kt">float</span><span class="o">*</span> <span class="n">result</span><span class="p">,</span> <span class="kt">unsigned</span> <span class="kt">long</span> <span class="n">size</span><span class="p">)</span>
<span class="p">{</span>
  <span class="kt">float</span> <span class="n">x</span> <span class="o">=</span> <span class="n">vector</span><span class="p">.</span><span class="n">x</span><span class="p">,</span> <span class="n">y</span> <span class="o">=</span> <span class="n">vector</span><span class="p">.</span><span class="n">y</span><span class="p">,</span> <span class="n">z</span> <span class="o">=</span> <span class="n">vector</span><span class="p">.</span><span class="n">z</span><span class="p">;</span>

  <span class="c1">// Broadcast X, Y, Z of constant vector into 3 SSE registers</span>
  <span class="kr">__m128</span> <span class="n">vX</span>  <span class="o">=</span> <span class="n">_mm_set_ps1</span><span class="p">(</span><span class="n">x</span><span class="p">);</span>
  <span class="kr">__m128</span> <span class="n">vY</span>  <span class="o">=</span> <span class="n">_mm_set_ps1</span><span class="p">(</span><span class="n">y</span><span class="p">);</span>
  <span class="kr">__m128</span> <span class="n">vZ</span>  <span class="o">=</span> <span class="n">_mm_set_ps1</span><span class="p">(</span><span class="n">z</span><span class="p">);</span>
  <span class="kr">__m128</span> <span class="n">X</span><span class="p">,</span> <span class="n">Y</span><span class="p">,</span> <span class="n">Z</span><span class="p">;</span>

  <span class="kt">unsigned</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span> <span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">size</span> <span class="o">-</span> <span class="mi">3</span><span class="p">;</span> <span class="n">i</span> <span class="o">+=</span> <span class="mi">4</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="c1">// Load data for next 4 points</span>
    <span class="n">X</span> <span class="o">=</span> <span class="n">_mm_load_ps</span> <span class="p">(</span><span class="n">vectors</span><span class="p">.</span><span class="n">x</span> <span class="o">+</span> <span class="n">i</span><span class="p">);</span>
    <span class="n">Y</span> <span class="o">=</span> <span class="n">_mm_load_ps</span> <span class="p">(</span><span class="n">vectors</span><span class="p">.</span><span class="n">y</span> <span class="o">+</span> <span class="n">i</span><span class="p">);</span>
    <span class="n">Z</span> <span class="o">=</span> <span class="n">_mm_load_ps</span> <span class="p">(</span><span class="n">vectors</span><span class="p">.</span><span class="n">z</span> <span class="o">+</span> <span class="n">i</span><span class="p">);</span>

    <span class="c1">// Compute X*X&#39;+Y*Y&#39;+Z*Z&#39; for each point</span>
    <span class="n">X</span> <span class="o">=</span> <span class="n">_mm_mul_ps</span> <span class="p">(</span><span class="n">X</span><span class="p">,</span> <span class="n">vX</span><span class="p">);</span>
    <span class="n">Y</span> <span class="o">=</span> <span class="n">_mm_mul_ps</span> <span class="p">(</span><span class="n">Y</span><span class="p">,</span> <span class="n">vY</span><span class="p">);</span>
    <span class="n">Z</span> <span class="o">=</span> <span class="n">_mm_mul_ps</span> <span class="p">(</span><span class="n">Z</span><span class="p">,</span> <span class="n">vZ</span><span class="p">);</span>
    <span class="n">X</span> <span class="o">=</span> <span class="n">_mm_add_ps</span> <span class="p">(</span><span class="n">X</span><span class="p">,</span> <span class="n">Y</span><span class="p">);</span>
    <span class="n">X</span> <span class="o">=</span> <span class="n">_mm_add_ps</span> <span class="p">(</span><span class="n">X</span><span class="p">,</span> <span class="n">Z</span><span class="p">);</span>

    <span class="c1">// Store results</span>
    <span class="n">_mm_store_ps</span><span class="p">(</span><span class="n">result</span> <span class="o">+</span> <span class="n">i</span><span class="p">,</span> <span class="n">X</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="c1">// Handle any leftovers at the end</span>
  <span class="k">for</span> <span class="p">(</span> <span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">size</span><span class="p">;</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">result</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="o">=</span> <span class="n">vectors</span><span class="p">.</span><span class="n">x</span><span class="p">[</span><span class="n">i</span><span class="p">]</span><span class="o">*</span><span class="n">x</span> <span class="o">+</span> <span class="n">vectors</span><span class="p">.</span><span class="n">y</span><span class="p">[</span><span class="n">i</span><span class="p">]</span><span class="o">*</span><span class="n">y</span> <span class="o">+</span> <span class="n">vectors</span><span class="p">.</span><span class="n">z</span><span class="p">[</span><span class="n">i</span><span class="p">]</span><span class="o">*</span><span class="n">z</span><span class="p">;</span>
  <span class="p">}</span>
<span class="p">}</span>
</pre></div>
</div>
<p>Horizontal (AoS), SSE4.1-optimized (with horizontal DPPS instruction):</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">void</span> <span class="nf">dotSSE4_1</span> <span class="p">(</span><span class="k">const</span> <span class="n">AOS</span><span class="o">*</span> <span class="n">vectors</span><span class="p">,</span> <span class="k">const</span> <span class="n">AOS</span><span class="o">&amp;</span> <span class="n">vector</span><span class="p">,</span>
                <span class="kt">float</span><span class="o">*</span> <span class="n">result</span><span class="p">,</span> <span class="kt">unsigned</span> <span class="kt">long</span> <span class="n">size</span><span class="p">)</span>
<span class="p">{</span>
  <span class="c1">// Load constant vector into an SSE register</span>
  <span class="kr">__m128</span> <span class="n">vec</span> <span class="o">=</span> <span class="n">_mm_load_ps</span> <span class="p">((</span><span class="k">const</span> <span class="kt">float</span><span class="o">*</span><span class="p">)</span> <span class="o">&amp;</span><span class="n">vector</span><span class="p">);</span>
  <span class="kr">__m128</span> <span class="n">XYZH</span><span class="p">;</span>

  <span class="c1">// Set mask to ignore the padding elements</span>
  <span class="k">const</span> <span class="kt">int</span> <span class="n">mask</span> <span class="o">=</span> <span class="mi">123</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">unsigned</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">size</span><span class="p">;</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="c1">// Load next point</span>
    <span class="n">XYZH</span> <span class="o">=</span> <span class="n">_mm_load_ps</span> <span class="p">((</span><span class="k">const</span> <span class="kt">float</span><span class="o">*</span><span class="p">)(</span><span class="n">vectors</span> <span class="o">+</span> <span class="n">i</span><span class="p">));</span>

    <span class="c1">// Dot product from SSE4.1</span>
    <span class="n">XYZH</span> <span class="o">=</span> <span class="n">_mm_dp_ps</span> <span class="p">(</span><span class="n">XYZH</span><span class="p">,</span> <span class="n">vec</span><span class="p">,</span> <span class="n">mask</span><span class="p">);</span>

    <span class="c1">// Store single result (the bottom register element)</span>
    <span class="n">_mm_store_ss</span> <span class="p">(</span><span class="o">&amp;</span><span class="p">(</span><span class="n">result</span> <span class="p">[</span><span class="n">i</span><span class="p">]),</span> <span class="n">XYZH</span><span class="p">);</span>
  <span class="p">}</span>
<span class="p">}</span>
</pre></div>
</div>
</div>
<div class="section" id="centroid">
<h3>Centroid</h3>
<p>Vertical (SoA), SSE2-optimized:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">void</span> <span class="nf">centroidSSE2</span> <span class="p">(</span><span class="k">const</span> <span class="n">SOA</span><span class="o">&amp;</span> <span class="n">vectors</span><span class="p">,</span> <span class="n">AOS</span><span class="o">&amp;</span> <span class="n">result</span><span class="p">,</span> <span class="kt">size_t</span> <span class="n">size</span><span class="p">)</span>
<span class="p">{</span>
  <span class="kr">__m128</span> <span class="n">X_sum</span> <span class="o">=</span> <span class="n">_mm_setzero_ps</span><span class="p">();</span>
  <span class="kr">__m128</span> <span class="n">Y_sum</span> <span class="o">=</span> <span class="n">_mm_setzero_ps</span><span class="p">();</span>
  <span class="kr">__m128</span> <span class="n">Z_sum</span> <span class="o">=</span> <span class="n">_mm_setzero_ps</span><span class="p">();</span>
  <span class="kr">__m128</span> <span class="n">X</span><span class="p">,</span> <span class="n">Y</span><span class="p">,</span> <span class="n">Z</span><span class="p">;</span>

  <span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span> <span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">size</span> <span class="o">-</span> <span class="mi">3</span><span class="p">;</span> <span class="n">i</span> <span class="o">+=</span> <span class="mi">4</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="c1">// Load next 4 points</span>
    <span class="n">X</span> <span class="o">=</span> <span class="n">_mm_load_ps</span> <span class="p">(</span><span class="n">vectors</span><span class="p">.</span><span class="n">x</span> <span class="o">+</span> <span class="n">i</span><span class="p">);</span>
    <span class="n">Y</span> <span class="o">=</span> <span class="n">_mm_load_ps</span> <span class="p">(</span><span class="n">vectors</span><span class="p">.</span><span class="n">y</span> <span class="o">+</span> <span class="n">i</span><span class="p">);</span>
    <span class="n">Z</span> <span class="o">=</span> <span class="n">_mm_load_ps</span> <span class="p">(</span><span class="n">vectors</span><span class="p">.</span><span class="n">z</span> <span class="o">+</span> <span class="n">i</span><span class="p">);</span>

    <span class="c1">// Accumulate 4 sums in each dimension</span>
    <span class="n">X_sum</span> <span class="o">=</span> <span class="n">_mm_add_ps</span><span class="p">(</span><span class="n">X_sum</span><span class="p">,</span> <span class="n">X</span><span class="p">);</span>
    <span class="n">Y_sum</span> <span class="o">=</span> <span class="n">_mm_add_ps</span><span class="p">(</span><span class="n">Y_sum</span><span class="p">,</span> <span class="n">Y</span><span class="p">);</span>
    <span class="n">Z_sum</span> <span class="o">=</span> <span class="n">_mm_add_ps</span><span class="p">(</span><span class="n">Z_sum</span><span class="p">,</span> <span class="n">Z</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="c1">// Horizontal adds (HADD from SSE3 could help slightly)</span>
  <span class="kt">float</span><span class="o">*</span> <span class="n">pX</span> <span class="o">=</span> <span class="k">reinterpret_cast</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">*&gt;</span><span class="p">(</span><span class="o">&amp;</span><span class="n">X_sum</span><span class="p">);</span>
  <span class="kt">float</span><span class="o">*</span> <span class="n">pY</span> <span class="o">=</span> <span class="k">reinterpret_cast</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">*&gt;</span><span class="p">(</span><span class="o">&amp;</span><span class="n">Y_sum</span><span class="p">);</span>
  <span class="kt">float</span><span class="o">*</span> <span class="n">pZ</span> <span class="o">=</span> <span class="k">reinterpret_cast</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">*&gt;</span><span class="p">(</span><span class="o">&amp;</span><span class="n">Z_sum</span><span class="p">);</span>
  <span class="n">result</span><span class="p">.</span><span class="n">x</span> <span class="o">=</span> <span class="n">pX</span><span class="p">[</span><span class="mi">0</span><span class="p">]</span> <span class="o">+</span> <span class="n">pX</span><span class="p">[</span><span class="mi">1</span><span class="p">]</span> <span class="o">+</span> <span class="n">pX</span><span class="p">[</span><span class="mi">2</span><span class="p">]</span> <span class="o">+</span> <span class="n">pX</span><span class="p">[</span><span class="mi">3</span><span class="p">];</span>
  <span class="n">result</span><span class="p">.</span><span class="n">y</span> <span class="o">=</span> <span class="n">pY</span><span class="p">[</span><span class="mi">0</span><span class="p">]</span> <span class="o">+</span> <span class="n">pY</span><span class="p">[</span><span class="mi">1</span><span class="p">]</span> <span class="o">+</span> <span class="n">pY</span><span class="p">[</span><span class="mi">2</span><span class="p">]</span> <span class="o">+</span> <span class="n">pY</span><span class="p">[</span><span class="mi">3</span><span class="p">];</span>
  <span class="n">result</span><span class="p">.</span><span class="n">z</span> <span class="o">=</span> <span class="n">pZ</span><span class="p">[</span><span class="mi">0</span><span class="p">]</span> <span class="o">+</span> <span class="n">pZ</span><span class="p">[</span><span class="mi">1</span><span class="p">]</span> <span class="o">+</span> <span class="n">pZ</span><span class="p">[</span><span class="mi">2</span><span class="p">]</span> <span class="o">+</span> <span class="n">pZ</span><span class="p">[</span><span class="mi">3</span><span class="p">];</span>

  <span class="c1">// Leftover points</span>
  <span class="k">for</span> <span class="p">(</span> <span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">size</span><span class="p">;</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">result</span><span class="p">.</span><span class="n">x</span> <span class="o">+=</span> <span class="n">vectors</span><span class="p">.</span><span class="n">x</span><span class="p">[</span><span class="n">i</span><span class="p">];</span>
    <span class="n">result</span><span class="p">.</span><span class="n">y</span> <span class="o">+=</span> <span class="n">vectors</span><span class="p">.</span><span class="n">y</span><span class="p">[</span><span class="n">i</span><span class="p">];</span>
    <span class="n">result</span><span class="p">.</span><span class="n">z</span> <span class="o">+=</span> <span class="n">vectors</span><span class="p">.</span><span class="n">z</span><span class="p">[</span><span class="n">i</span><span class="p">];</span>
  <span class="p">}</span>

  <span class="c1">// Average</span>
  <span class="kt">float</span> <span class="n">inv_size</span> <span class="o">=</span> <span class="mf">1.0f</span> <span class="o">/</span> <span class="n">size</span><span class="p">;</span>
  <span class="n">result</span><span class="p">.</span><span class="n">x</span> <span class="o">*=</span> <span class="n">inv_size</span><span class="p">;</span>
  <span class="n">result</span><span class="p">.</span><span class="n">y</span> <span class="o">*=</span> <span class="n">inv_size</span><span class="p">;</span>
  <span class="n">result</span><span class="p">.</span><span class="n">z</span> <span class="o">*=</span> <span class="n">inv_size</span><span class="p">;</span>
<span class="p">}</span>
</pre></div>
</div>
<p>Horizontal (AoS), SSE2-optimized:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">void</span> <span class="nf">centroidSSE2</span> <span class="p">(</span><span class="k">const</span> <span class="n">AOS</span><span class="o">*</span> <span class="n">vectors</span><span class="p">,</span> <span class="n">AOS</span><span class="o">&amp;</span> <span class="n">result</span><span class="p">,</span> <span class="kt">size_t</span> <span class="n">size</span><span class="p">)</span>
<span class="p">{</span>
  <span class="kr">__m128</span> <span class="n">sum</span> <span class="o">=</span> <span class="n">_mm_setzero_ps</span><span class="p">();</span>

  <span class="k">for</span> <span class="p">(</span><span class="kt">unsigned</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">size</span><span class="p">;</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="kr">__m128</span> <span class="n">XYZH</span> <span class="o">=</span> <span class="n">_mm_load_ps</span> <span class="p">((</span><span class="k">const</span> <span class="kt">float</span><span class="o">*</span><span class="p">)(</span><span class="n">vectors</span> <span class="o">+</span> <span class="n">i</span><span class="p">));</span>
    <span class="n">sum</span> <span class="o">=</span> <span class="n">_mm_add_ps</span><span class="p">(</span><span class="n">sum</span><span class="p">,</span> <span class="n">XYZH</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="n">_mm_store_ps</span><span class="p">((</span><span class="kt">float</span><span class="o">*</span><span class="p">)</span><span class="o">&amp;</span><span class="n">result</span><span class="p">,</span> <span class="n">sum</span><span class="p">);</span>

  <span class="kt">float</span> <span class="n">inv_size</span> <span class="o">=</span> <span class="mf">1.0f</span> <span class="o">/</span> <span class="n">size</span><span class="p">;</span>
  <span class="n">result</span><span class="p">.</span><span class="n">x</span> <span class="o">*=</span> <span class="n">inv_size</span><span class="p">;</span>
  <span class="n">result</span><span class="p">.</span><span class="n">y</span> <span class="o">*=</span> <span class="n">inv_size</span><span class="p">;</span>
  <span class="n">result</span><span class="p">.</span><span class="n">z</span> <span class="o">*=</span> <span class="n">inv_size</span><span class="p">;</span>
<span class="p">}</span>
</pre></div>
</div>
</div>
<div class="section" id="indexed">
<h3>Indexed</h3>
<p>When using point indices, the vertical implementation can no longer use aligned
loads. Instead it&#8217;s best to use the <tt class="docutils literal"><span class="pre">_mm_set_ps</span></tt> intrinsic to gather the next
four points.</p>
<p>Vertical (SoA) dot product, SSE2-optimized:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">void</span> <span class="nf">dotIndexedSSE2</span> <span class="p">(</span><span class="k">const</span> <span class="n">SOA</span><span class="o">&amp;</span> <span class="n">vectors</span><span class="p">,</span> <span class="k">const</span> <span class="n">AOS</span><span class="o">&amp;</span> <span class="n">vector</span><span class="p">,</span>
                     <span class="k">const</span> <span class="kt">int</span><span class="o">*</span> <span class="n">indices</span><span class="p">,</span> <span class="kt">float</span><span class="o">*</span> <span class="n">result</span><span class="p">,</span> <span class="kt">unsigned</span> <span class="kt">long</span> <span class="n">size</span><span class="p">)</span>
<span class="p">{</span>
  <span class="kt">float</span> <span class="n">x</span> <span class="o">=</span> <span class="n">vector</span><span class="p">.</span><span class="n">x</span><span class="p">,</span> <span class="n">y</span> <span class="o">=</span> <span class="n">vector</span><span class="p">.</span><span class="n">y</span><span class="p">,</span> <span class="n">z</span> <span class="o">=</span> <span class="n">vector</span><span class="p">.</span><span class="n">z</span><span class="p">;</span>

  <span class="kr">__m128</span> <span class="n">vX</span>  <span class="o">=</span> <span class="n">_mm_set_ps1</span><span class="p">(</span><span class="n">x</span><span class="p">);</span>
  <span class="kr">__m128</span> <span class="n">vY</span>  <span class="o">=</span> <span class="n">_mm_set_ps1</span><span class="p">(</span><span class="n">y</span><span class="p">);</span>
  <span class="kr">__m128</span> <span class="n">vZ</span>  <span class="o">=</span> <span class="n">_mm_set_ps1</span><span class="p">(</span><span class="n">z</span><span class="p">);</span>
  <span class="kr">__m128</span> <span class="n">X</span><span class="p">,</span> <span class="n">Y</span><span class="p">,</span> <span class="n">Z</span><span class="p">;</span>

  <span class="kt">unsigned</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span> <span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">size</span> <span class="o">-</span> <span class="mi">3</span><span class="p">;</span> <span class="n">i</span> <span class="o">+=</span> <span class="mi">4</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="kt">int</span> <span class="n">i0</span> <span class="o">=</span> <span class="n">indices</span><span class="p">[</span><span class="n">i</span> <span class="o">+</span> <span class="mi">0</span><span class="p">];</span>
    <span class="kt">int</span> <span class="n">i1</span> <span class="o">=</span> <span class="n">indices</span><span class="p">[</span><span class="n">i</span> <span class="o">+</span> <span class="mi">1</span><span class="p">];</span>
    <span class="kt">int</span> <span class="n">i2</span> <span class="o">=</span> <span class="n">indices</span><span class="p">[</span><span class="n">i</span> <span class="o">+</span> <span class="mi">2</span><span class="p">];</span>
    <span class="kt">int</span> <span class="n">i3</span> <span class="o">=</span> <span class="n">indices</span><span class="p">[</span><span class="n">i</span> <span class="o">+</span> <span class="mi">3</span><span class="p">];</span>

    <span class="c1">// Gather next four indexed points</span>
    <span class="n">X</span> <span class="o">=</span> <span class="n">_mm_set_ps</span><span class="p">(</span><span class="n">vectors</span><span class="p">.</span><span class="n">x</span><span class="p">[</span><span class="n">i3</span><span class="p">],</span> <span class="n">vectors</span><span class="p">.</span><span class="n">x</span><span class="p">[</span><span class="n">i2</span><span class="p">],</span> <span class="n">vectors</span><span class="p">.</span><span class="n">x</span><span class="p">[</span><span class="n">i1</span><span class="p">],</span> <span class="n">vectors</span><span class="p">.</span><span class="n">x</span><span class="p">[</span><span class="n">i0</span><span class="p">]);</span>
    <span class="n">Y</span> <span class="o">=</span> <span class="n">_mm_set_ps</span><span class="p">(</span><span class="n">vectors</span><span class="p">.</span><span class="n">y</span><span class="p">[</span><span class="n">i3</span><span class="p">],</span> <span class="n">vectors</span><span class="p">.</span><span class="n">y</span><span class="p">[</span><span class="n">i2</span><span class="p">],</span> <span class="n">vectors</span><span class="p">.</span><span class="n">y</span><span class="p">[</span><span class="n">i1</span><span class="p">],</span> <span class="n">vectors</span><span class="p">.</span><span class="n">y</span><span class="p">[</span><span class="n">i0</span><span class="p">]);</span>
    <span class="n">Z</span> <span class="o">=</span> <span class="n">_mm_set_ps</span><span class="p">(</span><span class="n">vectors</span><span class="p">.</span><span class="n">z</span><span class="p">[</span><span class="n">i3</span><span class="p">],</span> <span class="n">vectors</span><span class="p">.</span><span class="n">z</span><span class="p">[</span><span class="n">i2</span><span class="p">],</span> <span class="n">vectors</span><span class="p">.</span><span class="n">z</span><span class="p">[</span><span class="n">i1</span><span class="p">],</span> <span class="n">vectors</span><span class="p">.</span><span class="n">z</span><span class="p">[</span><span class="n">i0</span><span class="p">]);</span>

    <span class="c1">// Computation</span>
    <span class="n">X</span> <span class="o">=</span> <span class="n">_mm_mul_ps</span> <span class="p">(</span><span class="n">X</span><span class="p">,</span> <span class="n">vX</span><span class="p">);</span>
    <span class="n">Y</span> <span class="o">=</span> <span class="n">_mm_mul_ps</span> <span class="p">(</span><span class="n">Y</span><span class="p">,</span> <span class="n">vY</span><span class="p">);</span>
    <span class="n">Z</span> <span class="o">=</span> <span class="n">_mm_mul_ps</span> <span class="p">(</span><span class="n">Z</span><span class="p">,</span> <span class="n">vZ</span><span class="p">);</span>
    <span class="n">X</span> <span class="o">=</span> <span class="n">_mm_add_ps</span> <span class="p">(</span><span class="n">X</span><span class="p">,</span> <span class="n">Y</span><span class="p">);</span>
    <span class="n">X</span> <span class="o">=</span> <span class="n">_mm_add_ps</span> <span class="p">(</span><span class="n">X</span><span class="p">,</span> <span class="n">Z</span><span class="p">);</span>

    <span class="c1">// Store result</span>
    <span class="n">_mm_store_ps</span><span class="p">(</span><span class="n">result</span> <span class="o">+</span> <span class="n">i</span><span class="p">,</span> <span class="n">X</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="k">for</span> <span class="p">(</span> <span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">size</span><span class="p">;</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="kt">int</span> <span class="n">idx</span> <span class="o">=</span> <span class="n">indices</span><span class="p">[</span><span class="n">i</span><span class="p">];</span>
    <span class="n">result</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="o">=</span> <span class="n">vectors</span><span class="p">.</span><span class="n">x</span><span class="p">[</span><span class="n">idx</span><span class="p">]</span><span class="o">*</span><span class="n">x</span> <span class="o">+</span> <span class="n">vectors</span><span class="p">.</span><span class="n">y</span><span class="p">[</span><span class="n">idx</span><span class="p">]</span><span class="o">*</span><span class="n">x</span> <span class="o">+</span> <span class="n">vectors</span><span class="p">.</span><span class="n">z</span><span class="p">[</span><span class="n">idx</span><span class="p">]</span><span class="o">*</span><span class="n">z</span><span class="p">;</span>
  <span class="p">}</span>
<span class="p">}</span>
</pre></div>
</div>
</div>
</div>
<div class="section" id="benchmarks-random-data">
<h2>Benchmarks (random data)</h2>
<p>The test point cloud is randomly generated, 640x480, dense. Each operation is
repeated 1000 times.</p>
<p>For indexed tests, the indices list every 4th point. More random index patterns
would change execution time by affecting caching and prefetching, but I&#8217;d
expect such effects to be similar for horizontal and vertical code.</p>
<p>&#8220;Scalar&#8221; code uses no vector instructions, otherwise the instruction set is
listed. A trailing u# means the code was unrolled by factor #.</p>
<div class="section" id="id1">
<h3>Dot product</h3>
<div class="section" id="dense">
<h4>Dense</h4>
<div class="highlight-python"><div class="highlight"><pre>Horizontal (AOS)
  Scalar:   0.621674 seconds
  SSE2:     0.756300 seconds
  SSE4.1:   0.532441 seconds
  SSE4.1u4: 0.476841 seconds
Vertical (SOA)
  Scalar:   0.519625 seconds
  SSE2:     0.215499 seconds
</pre></div>
</div>
<p>The vertical SSE2 code is the clear winner, more than twice as fast as
horizontal code even with the special horizontal dot product from SSE4.1.</p>
<p>On the first i7 I used, horizontal SSE4.1 was actually the <em>slowest</em>
implementation. Unrolling it x4 helped significantly, although it was still
much worse than vertical SSE2. I attributed this to the very high latency of
the DPPS instruction; it takes 11 cycles before the result can be stored.
Unrolling helps hide the latency by providing more computation to do during
that time. I don&#8217;t know why the results from my office i7 (shown above) are so
different.</p>
</div>
<div class="section" id="id2">
<h4>Indexed</h4>
<div class="highlight-python"><div class="highlight"><pre>Horizontal (AOS)
  Scalar:   0.271768 seconds
  SSE2:     0.276114 seconds
  SSE4.1:   0.259613 seconds
Vertical (SOA)
  Scalar:   0.193394 seconds
  SSE2:     0.177262 seconds
</pre></div>
</div>
<p>SSE optimization actually gives meager benefits in both the horizontal and
vertical cases. However vertical SSE2 is still the winner.</p>
</div>
</div>
<div class="section" id="id3">
<h3>Centroid</h3>
<p>The story for centroid is similar; vertical SSE2 is fastest, significantly so
for dense data.</p>
<div class="section" id="id4">
<h4>Dense</h4>
<div class="highlight-python"><div class="highlight"><pre>Horizontal (AOS)
  Scalar:  0.628597 seconds
  SSE2:    0.326645 seconds
  SSE2u2:  0.247539 seconds
  SSE2u4:  0.236474 seconds
Vertical (SOA)
  Scalar:  0.711040 seconds
  SSE2:    0.149806 seconds
</pre></div>
</div>
</div>
<div class="section" id="id5">
<h4>Indexed</h4>
<div class="highlight-python"><div class="highlight"><pre>Horizontal (AOS)
  Scalar:  0.256237 seconds
  SSE2:    0.195724 seconds
Vertical (SOA)
  Scalar:  0.194030 seconds
  SSE2:    0.166639 seconds
</pre></div>
</div>
</div>
</div>
</div>
<div class="section" id="vertical-sse-for-organized-point-clouds">
<h2>Vertical SSE for organized point clouds</h2>
<p>We still need a way to effectively use vertical SSE for organized point clouds
(containing NaNs). A promising approach is to compute a <em>run-length encoding</em>
(RLE) of the valid points as a preprocessing step. The data structure is very
simple:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">struct</span> <span class="n">RlePair</span>
<span class="p">{</span>
  <span class="kt">size_t</span> <span class="n">good</span><span class="p">;</span>
  <span class="kt">size_t</span> <span class="n">skip</span><span class="p">;</span>
<span class="p">};</span>
<span class="k">typedef</span> <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">RlePair</span><span class="o">&gt;</span> <span class="n">RLE</span><span class="p">;</span>
</pre></div>
</div>
<p>The RLE counts the length of alternating runs of valid and invalid points. Once
computed, it allows us to process only valid points without explicitly checking
each one for NaNs. In fact, operations become <tt class="docutils literal"><span class="pre">O(#valid</span> <span class="pre">points)</span></tt> instead of
<tt class="docutils literal"><span class="pre">O(#total</span> <span class="pre">points)</span></tt>, which can itself be a win if many points are invalid.</p>
<p>In real scenes, valid points are clustered together (into objects), so valid
(and invalid) runs should be lengthy on average. A long run of valid points can
be split into &lt;4 beginning points, &lt;4 final points, and a run of aligned, valid
point data which can be safely processed with vertical SSE.</p>
</div>
<div class="section" id="abstracting-point-iteration">
<h2>Abstracting point iteration</h2>
<p>We are still left with three distinct cases for processing point clouds,
requiring different methods of iterating over point data:</p>
<ul class="simple">
<li>Dense (no NaNs)</li>
<li>Indexed</li>
<li>Organized (contains NaNs)</li>
</ul>
<p>Writing and maintaining three copies of each PCL algorithm is a huge burden.
The RLE for organized data in particular imposes a relatively complicated
iteration method. Ideally we should be able to write the computational core of
an algorithm only once, and have it work efficiently in each of the three cases.</p>
<p>Currently PCL does not meet this goal. In fact, core algorithms tend to have
four near-identical implementations:</p>
<ul class="simple">
<li>Dense</li>
<li>Dense indexed</li>
<li>Organized</li>
<li>Organized indexed</li>
</ul>
<p>I think it&#8217;s unnecessary to distinguish between &#8220;dense indexed&#8221; and &#8220;organized
indexed&#8221;, if we require that indices point to valid data.</p>
<div class="section" id="writing-algorithms-as-computational-kernels">
<h3>Writing algorithms as computational kernels</h3>
<p>As an experiment, I rewrote the vertical centroid as a <em>kernel</em> class. This
implements only the computation, without worrying about the memory layout of
the whole cloud:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">struct</span> <span class="n">CentroidKernel</span>
<span class="p">{</span>
  <span class="c1">// State</span>
  <span class="kt">float</span> <span class="n">x_sum</span><span class="p">,</span> <span class="n">y_sum</span><span class="p">,</span> <span class="n">z_sum</span><span class="p">;</span>
  <span class="kr">__m128</span> <span class="n">X_sum</span><span class="p">,</span> <span class="n">Y_sum</span><span class="p">,</span> <span class="n">Z_sum</span><span class="p">;</span>
  <span class="kt">size_t</span> <span class="n">count</span><span class="p">;</span>
  <span class="n">AOS</span> <span class="n">result</span><span class="p">;</span>

  <span class="kt">void</span> <span class="nf">init</span><span class="p">()</span>
  <span class="p">{</span>
    <span class="c1">// Initialization</span>
    <span class="n">x_sum</span> <span class="o">=</span> <span class="n">y_sum</span> <span class="o">=</span> <span class="n">z_sum</span> <span class="o">=</span> <span class="mf">0.0f</span><span class="p">;</span>
    <span class="n">X_sum</span> <span class="o">=</span> <span class="n">_mm_setzero_ps</span><span class="p">();</span>
    <span class="n">Y_sum</span> <span class="o">=</span> <span class="n">_mm_setzero_ps</span><span class="p">();</span>
    <span class="n">Z_sum</span> <span class="o">=</span> <span class="n">_mm_setzero_ps</span><span class="p">();</span>
    <span class="n">count</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="p">}</span>

  <span class="c1">// Scalar operator</span>
  <span class="kr">inline</span> <span class="kt">void</span> <span class="nf">operator</span><span class="p">()</span> <span class="p">(</span><span class="kt">float</span> <span class="n">x</span><span class="p">,</span> <span class="kt">float</span> <span class="n">y</span><span class="p">,</span> <span class="kt">float</span> <span class="n">z</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">x_sum</span> <span class="o">+=</span> <span class="n">x</span><span class="p">;</span>
    <span class="n">y_sum</span> <span class="o">+=</span> <span class="n">y</span><span class="p">;</span>
    <span class="n">z_sum</span> <span class="o">+=</span> <span class="n">z</span><span class="p">;</span>
    <span class="o">++</span><span class="n">count</span><span class="p">;</span>
  <span class="p">}</span>

  <span class="c1">// SIMD operator</span>
  <span class="kr">inline</span> <span class="kt">void</span> <span class="nf">operator</span><span class="p">()</span> <span class="p">(</span><span class="kr">__m128</span> <span class="n">X</span><span class="p">,</span> <span class="kr">__m128</span> <span class="n">Y</span><span class="p">,</span> <span class="kr">__m128</span> <span class="n">Z</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">X_sum</span> <span class="o">=</span> <span class="n">_mm_add_ps</span><span class="p">(</span><span class="n">X_sum</span><span class="p">,</span> <span class="n">X</span><span class="p">);</span>
    <span class="n">Y_sum</span> <span class="o">=</span> <span class="n">_mm_add_ps</span><span class="p">(</span><span class="n">Y_sum</span><span class="p">,</span> <span class="n">Y</span><span class="p">);</span>
    <span class="n">Z_sum</span> <span class="o">=</span> <span class="n">_mm_add_ps</span><span class="p">(</span><span class="n">Z_sum</span><span class="p">,</span> <span class="n">Z</span><span class="p">);</span>
    <span class="n">count</span> <span class="o">+=</span> <span class="mi">4</span><span class="p">;</span>
  <span class="p">}</span>

  <span class="kt">void</span> <span class="nf">reduce</span><span class="p">()</span>
  <span class="p">{</span>
    <span class="kt">float</span><span class="o">*</span> <span class="n">pX</span> <span class="o">=</span> <span class="k">reinterpret_cast</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">*&gt;</span><span class="p">(</span><span class="o">&amp;</span><span class="n">X_sum</span><span class="p">);</span>
    <span class="kt">float</span><span class="o">*</span> <span class="n">pY</span> <span class="o">=</span> <span class="k">reinterpret_cast</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">*&gt;</span><span class="p">(</span><span class="o">&amp;</span><span class="n">Y_sum</span><span class="p">);</span>
    <span class="kt">float</span><span class="o">*</span> <span class="n">pZ</span> <span class="o">=</span> <span class="k">reinterpret_cast</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">*&gt;</span><span class="p">(</span><span class="o">&amp;</span><span class="n">Z_sum</span><span class="p">);</span>
    <span class="n">result</span><span class="p">.</span><span class="n">x</span> <span class="o">=</span> <span class="n">pX</span><span class="p">[</span><span class="mi">0</span><span class="p">]</span> <span class="o">+</span> <span class="n">pX</span><span class="p">[</span><span class="mi">1</span><span class="p">]</span> <span class="o">+</span> <span class="n">pX</span><span class="p">[</span><span class="mi">2</span><span class="p">]</span> <span class="o">+</span> <span class="n">pX</span><span class="p">[</span><span class="mi">3</span><span class="p">]</span> <span class="o">+</span> <span class="n">x_sum</span><span class="p">;</span>
    <span class="n">result</span><span class="p">.</span><span class="n">y</span> <span class="o">=</span> <span class="n">pY</span><span class="p">[</span><span class="mi">0</span><span class="p">]</span> <span class="o">+</span> <span class="n">pY</span><span class="p">[</span><span class="mi">1</span><span class="p">]</span> <span class="o">+</span> <span class="n">pY</span><span class="p">[</span><span class="mi">2</span><span class="p">]</span> <span class="o">+</span> <span class="n">pY</span><span class="p">[</span><span class="mi">3</span><span class="p">]</span> <span class="o">+</span> <span class="n">y_sum</span><span class="p">;</span>
    <span class="n">result</span><span class="p">.</span><span class="n">z</span> <span class="o">=</span> <span class="n">pZ</span><span class="p">[</span><span class="mi">0</span><span class="p">]</span> <span class="o">+</span> <span class="n">pZ</span><span class="p">[</span><span class="mi">1</span><span class="p">]</span> <span class="o">+</span> <span class="n">pZ</span><span class="p">[</span><span class="mi">2</span><span class="p">]</span> <span class="o">+</span> <span class="n">pZ</span><span class="p">[</span><span class="mi">3</span><span class="p">]</span> <span class="o">+</span> <span class="n">z_sum</span><span class="p">;</span>

    <span class="kt">float</span> <span class="n">inv_count</span> <span class="o">=</span> <span class="mf">1.0f</span> <span class="o">/</span> <span class="n">count</span><span class="p">;</span>
    <span class="n">result</span><span class="p">.</span><span class="n">x</span> <span class="o">*=</span> <span class="n">inv_count</span><span class="p">;</span>
    <span class="n">result</span><span class="p">.</span><span class="n">y</span> <span class="o">*=</span> <span class="n">inv_count</span><span class="p">;</span>
    <span class="n">result</span><span class="p">.</span><span class="n">z</span> <span class="o">*=</span> <span class="n">inv_count</span><span class="p">;</span>
  <span class="p">}</span>
<span class="p">};</span>
</pre></div>
</div>
</div>
<div class="section" id="kernel-applicators">
<h3>Kernel applicators</h3>
<p>We can then define <em>applicator</em> functions that apply a kernel to a particular
case of point cloud. The dense version simply uses aligned loads:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">template</span> <span class="o">&lt;</span><span class="k">typename</span> <span class="n">Kernel</span><span class="o">&gt;</span>
<span class="kt">void</span> <span class="n">applyDense</span> <span class="p">(</span><span class="n">Kernel</span><span class="o">&amp;</span> <span class="n">kernel</span><span class="p">,</span> <span class="k">const</span> <span class="n">SOA</span><span class="o">&amp;</span> <span class="n">pts</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">kernel</span><span class="p">.</span><span class="n">init</span><span class="p">();</span>

  <span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span> <span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">pts</span><span class="p">.</span><span class="n">size</span> <span class="o">-</span> <span class="mi">3</span><span class="p">;</span> <span class="n">i</span> <span class="o">+=</span> <span class="mi">4</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="kr">__m128</span> <span class="n">X</span> <span class="o">=</span> <span class="n">_mm_load_ps</span> <span class="p">(</span><span class="n">pts</span><span class="p">.</span><span class="n">x</span> <span class="o">+</span> <span class="n">i</span><span class="p">);</span>
    <span class="kr">__m128</span> <span class="n">Y</span> <span class="o">=</span> <span class="n">_mm_load_ps</span> <span class="p">(</span><span class="n">pts</span><span class="p">.</span><span class="n">y</span> <span class="o">+</span> <span class="n">i</span><span class="p">);</span>
    <span class="kr">__m128</span> <span class="n">Z</span> <span class="o">=</span> <span class="n">_mm_load_ps</span> <span class="p">(</span><span class="n">pts</span><span class="p">.</span><span class="n">z</span> <span class="o">+</span> <span class="n">i</span><span class="p">);</span>

    <span class="n">kernel</span><span class="p">(</span><span class="n">X</span><span class="p">,</span> <span class="n">Y</span><span class="p">,</span> <span class="n">Z</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="k">for</span> <span class="p">(</span> <span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">pts</span><span class="p">.</span><span class="n">size</span><span class="p">;</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">kernel</span><span class="p">(</span><span class="n">pts</span><span class="p">.</span><span class="n">x</span><span class="p">[</span><span class="n">i</span><span class="p">],</span> <span class="n">pts</span><span class="p">.</span><span class="n">y</span><span class="p">[</span><span class="n">i</span><span class="p">],</span> <span class="n">pts</span><span class="p">.</span><span class="n">z</span><span class="p">[</span><span class="n">i</span><span class="p">]);</span>
  <span class="p">}</span>

  <span class="n">kernel</span><span class="p">.</span><span class="n">reduce</span><span class="p">();</span>
<span class="p">}</span>
</pre></div>
</div>
<p>The indexed version performs the necessary data gathering:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">template</span> <span class="o">&lt;</span><span class="k">typename</span> <span class="n">Kernel</span><span class="o">&gt;</span>
<span class="kt">void</span> <span class="n">applySparse</span> <span class="p">(</span><span class="n">Kernel</span><span class="o">&amp;</span> <span class="n">kernel</span><span class="p">,</span> <span class="k">const</span> <span class="n">SOA</span><span class="o">&amp;</span> <span class="n">pts</span><span class="p">,</span>
                  <span class="k">const</span> <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;&amp;</span> <span class="n">indices</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">kernel</span><span class="p">.</span><span class="n">init</span><span class="p">();</span>

  <span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span> <span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">indices</span><span class="p">.</span><span class="n">size</span><span class="p">()</span> <span class="o">-</span> <span class="mi">3</span><span class="p">;</span> <span class="n">i</span> <span class="o">+=</span> <span class="mi">4</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="kt">int</span> <span class="n">i0</span> <span class="o">=</span> <span class="n">indices</span><span class="p">[</span><span class="n">i</span> <span class="o">+</span> <span class="mi">0</span><span class="p">];</span>
    <span class="kt">int</span> <span class="n">i1</span> <span class="o">=</span> <span class="n">indices</span><span class="p">[</span><span class="n">i</span> <span class="o">+</span> <span class="mi">1</span><span class="p">];</span>
    <span class="kt">int</span> <span class="n">i2</span> <span class="o">=</span> <span class="n">indices</span><span class="p">[</span><span class="n">i</span> <span class="o">+</span> <span class="mi">2</span><span class="p">];</span>
    <span class="kt">int</span> <span class="n">i3</span> <span class="o">=</span> <span class="n">indices</span><span class="p">[</span><span class="n">i</span> <span class="o">+</span> <span class="mi">3</span><span class="p">];</span>

    <span class="c1">// Gather next four indexed points</span>
    <span class="kr">__m128</span> <span class="n">X</span> <span class="o">=</span> <span class="n">_mm_set_ps</span><span class="p">(</span><span class="n">pts</span><span class="p">.</span><span class="n">x</span><span class="p">[</span><span class="n">i3</span><span class="p">],</span> <span class="n">pts</span><span class="p">.</span><span class="n">x</span><span class="p">[</span><span class="n">i2</span><span class="p">],</span> <span class="n">pts</span><span class="p">.</span><span class="n">x</span><span class="p">[</span><span class="n">i1</span><span class="p">],</span> <span class="n">pts</span><span class="p">.</span><span class="n">x</span><span class="p">[</span><span class="n">i0</span><span class="p">]);</span>
    <span class="kr">__m128</span> <span class="n">Y</span> <span class="o">=</span> <span class="n">_mm_set_ps</span><span class="p">(</span><span class="n">pts</span><span class="p">.</span><span class="n">y</span><span class="p">[</span><span class="n">i3</span><span class="p">],</span> <span class="n">pts</span><span class="p">.</span><span class="n">y</span><span class="p">[</span><span class="n">i2</span><span class="p">],</span> <span class="n">pts</span><span class="p">.</span><span class="n">y</span><span class="p">[</span><span class="n">i1</span><span class="p">],</span> <span class="n">pts</span><span class="p">.</span><span class="n">y</span><span class="p">[</span><span class="n">i0</span><span class="p">]);</span>
    <span class="kr">__m128</span> <span class="n">Z</span> <span class="o">=</span> <span class="n">_mm_set_ps</span><span class="p">(</span><span class="n">pts</span><span class="p">.</span><span class="n">z</span><span class="p">[</span><span class="n">i3</span><span class="p">],</span> <span class="n">pts</span><span class="p">.</span><span class="n">z</span><span class="p">[</span><span class="n">i2</span><span class="p">],</span> <span class="n">pts</span><span class="p">.</span><span class="n">z</span><span class="p">[</span><span class="n">i1</span><span class="p">],</span> <span class="n">pts</span><span class="p">.</span><span class="n">z</span><span class="p">[</span><span class="n">i0</span><span class="p">]);</span>

    <span class="n">kernel</span><span class="p">(</span><span class="n">X</span><span class="p">,</span> <span class="n">Y</span><span class="p">,</span> <span class="n">Z</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="k">for</span> <span class="p">(</span> <span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">indices</span><span class="p">.</span><span class="n">size</span><span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="kt">int</span> <span class="n">idx</span> <span class="o">=</span> <span class="n">indices</span><span class="p">[</span><span class="n">i</span><span class="p">];</span>
    <span class="n">kernel</span><span class="p">(</span><span class="n">pts</span><span class="p">.</span><span class="n">x</span><span class="p">[</span><span class="n">idx</span><span class="p">],</span> <span class="n">pts</span><span class="p">.</span><span class="n">y</span><span class="p">[</span><span class="n">idx</span><span class="p">],</span> <span class="n">pts</span><span class="p">.</span><span class="n">z</span><span class="p">[</span><span class="n">idx</span><span class="p">]);</span>
  <span class="p">}</span>

  <span class="n">kernel</span><span class="p">.</span><span class="n">reduce</span><span class="p">();</span>
<span class="p">}</span>
</pre></div>
</div>
<p>The organized version is most complicated, and uses the RLE to vectorize as
much of the computation as possible:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">template</span> <span class="o">&lt;</span><span class="k">typename</span> <span class="n">Kernel</span><span class="o">&gt;</span>
<span class="kt">void</span> <span class="n">applyOrganized</span> <span class="p">(</span><span class="n">Kernel</span><span class="o">&amp;</span> <span class="n">kernel</span><span class="p">,</span> <span class="k">const</span> <span class="n">SOA</span><span class="o">&amp;</span> <span class="n">pts</span><span class="p">,</span> <span class="k">const</span> <span class="n">RLE</span><span class="o">&amp;</span> <span class="n">rle</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">kernel</span><span class="p">.</span><span class="n">init</span><span class="p">();</span>

  <span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="n">RLE</span><span class="o">::</span><span class="n">const_iterator</span> <span class="n">rle_it</span> <span class="o">=</span> <span class="n">rle</span><span class="p">.</span><span class="n">begin</span><span class="p">();</span> <span class="n">rle_it</span> <span class="o">!=</span> <span class="n">rle</span><span class="p">.</span><span class="n">end</span><span class="p">();</span> <span class="o">++</span><span class="n">rle_it</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="c1">// Process current stretch of good pixels</span>
    <span class="kt">size_t</span> <span class="n">good</span> <span class="o">=</span> <span class="n">rle_it</span><span class="o">-&gt;</span><span class="n">good</span><span class="p">;</span>
    <span class="kt">size_t</span> <span class="n">skip</span> <span class="o">=</span> <span class="n">rle_it</span><span class="o">-&gt;</span><span class="n">skip</span><span class="p">;</span>
    <span class="kt">size_t</span> <span class="n">good_end</span> <span class="o">=</span> <span class="n">i</span> <span class="o">+</span> <span class="n">good</span><span class="p">;</span>

    <span class="c1">// Any unaligned points at start</span>
    <span class="kt">size_t</span> <span class="n">unaligned_end</span> <span class="o">=</span> <span class="n">std</span><span class="o">::</span><span class="n">min</span><span class="p">(</span> <span class="p">(</span><span class="n">i</span> <span class="o">+</span> <span class="mi">3</span><span class="p">)</span> <span class="o">&amp;</span> <span class="o">~</span><span class="mi">3</span><span class="p">,</span> <span class="n">good_end</span> <span class="p">);</span>
    <span class="k">for</span> <span class="p">(</span> <span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">unaligned_end</span><span class="p">;</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
      <span class="n">kernel</span><span class="p">(</span><span class="n">pts</span><span class="p">.</span><span class="n">x</span><span class="p">[</span><span class="n">i</span><span class="p">],</span> <span class="n">pts</span><span class="p">.</span><span class="n">y</span><span class="p">[</span><span class="n">i</span><span class="p">],</span> <span class="n">pts</span><span class="p">.</span><span class="n">z</span><span class="p">[</span><span class="n">i</span><span class="p">]);</span>
    <span class="c1">// Aligned SIMD point data</span>
    <span class="k">for</span> <span class="p">(</span> <span class="p">;</span> <span class="n">i</span> <span class="o">+</span> <span class="mi">4</span> <span class="o">&lt;=</span> <span class="n">good_end</span><span class="p">;</span> <span class="n">i</span> <span class="o">+=</span> <span class="mi">4</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="kr">__m128</span> <span class="n">X</span> <span class="o">=</span> <span class="n">_mm_load_ps</span> <span class="p">(</span><span class="n">pts</span><span class="p">.</span><span class="n">x</span> <span class="o">+</span> <span class="n">i</span><span class="p">);</span>
      <span class="kr">__m128</span> <span class="n">Y</span> <span class="o">=</span> <span class="n">_mm_load_ps</span> <span class="p">(</span><span class="n">pts</span><span class="p">.</span><span class="n">y</span> <span class="o">+</span> <span class="n">i</span><span class="p">);</span>
      <span class="kr">__m128</span> <span class="n">Z</span> <span class="o">=</span> <span class="n">_mm_load_ps</span> <span class="p">(</span><span class="n">pts</span><span class="p">.</span><span class="n">z</span> <span class="o">+</span> <span class="n">i</span><span class="p">);</span>

      <span class="n">kernel</span><span class="p">(</span><span class="n">X</span><span class="p">,</span> <span class="n">Y</span><span class="p">,</span> <span class="n">Z</span><span class="p">);</span>
    <span class="p">}</span>
    <span class="c1">// &lt;4 remaining points</span>
    <span class="k">for</span> <span class="p">(</span> <span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">good_end</span><span class="p">;</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
      <span class="n">kernel</span><span class="p">(</span><span class="n">pts</span><span class="p">.</span><span class="n">x</span><span class="p">[</span><span class="n">i</span><span class="p">],</span> <span class="n">pts</span><span class="p">.</span><span class="n">y</span><span class="p">[</span><span class="n">i</span><span class="p">],</span> <span class="n">pts</span><span class="p">.</span><span class="n">z</span><span class="p">[</span><span class="n">i</span><span class="p">]);</span>

    <span class="c1">// Skip the following stretch of NaNs</span>
    <span class="n">i</span> <span class="o">+=</span> <span class="n">skip</span><span class="p">;</span>
  <span class="p">}</span>

  <span class="n">kernel</span><span class="p">.</span><span class="n">reduce</span><span class="p">();</span>
<span class="p">}</span>
</pre></div>
</div>
<p>The kernel + applicator combinations for the dense and indexed cases were added
to the centroid benchmark for random point data, and show identical performance
to the hand-written vertical SSE2 code.</p>
<p>The above code is written with simplicity in mind. The biggest improvement
would be to combine the scalar and SSE <tt class="docutils literal"><span class="pre">operator()</span> <span class="pre">(...)</span></tt> functions; this
could possibly be achieved by using <tt class="docutils literal"><span class="pre">Eigen::Array</span></tt> as an SSE backend (similar
to how <tt class="docutils literal"><span class="pre">Eigen::Matrix</span></tt> maps are currently used), something like:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="c1">// N can be 1 or 4</span>
<span class="k">template</span> <span class="o">&lt;</span><span class="kt">int</span> <span class="n">N</span><span class="o">&gt;</span>
<span class="kt">void</span> <span class="k">operator</span><span class="p">()</span> <span class="p">(</span><span class="k">const</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Array</span><span class="o">&lt;</span><span class="kt">float</span><span class="p">,</span> <span class="n">N</span><span class="p">,</span> <span class="mi">1</span><span class="o">&gt;&amp;</span> <span class="n">x</span><span class="p">,</span>
                 <span class="k">const</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Array</span><span class="o">&lt;</span><span class="kt">float</span><span class="p">,</span> <span class="n">N</span><span class="p">,</span> <span class="mi">1</span><span class="o">&gt;&amp;</span> <span class="n">y</span><span class="p">,</span>
                 <span class="k">const</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Array</span><span class="o">&lt;</span><span class="kt">float</span><span class="p">,</span> <span class="n">N</span><span class="p">,</span> <span class="mi">1</span><span class="o">&gt;&amp;</span> <span class="n">z</span><span class="p">);</span>
</pre></div>
</div>
</div>
</div>
<div class="section" id="benchmarks-real-point-clouds">
<h2>Benchmarks (real point clouds)</h2>
<p>Finally, we compare <tt class="docutils literal"><span class="pre">CentroidKernel</span></tt> + applicator to
<tt class="docutils literal"><span class="pre">pcl::compute3DCentroid()</span></tt> for several real organized (and one dense) point
clouds.</p>
<p>The point clouds used are:</p>
<ul class="simple">
<li><a class="reference external" href="https://github.com/PointCloudLibrary/data/tree/master/tutorials/pairwise">capture000X.pcd</a></li>
<li><a class="reference external" href="https://github.com/PointCloudLibrary/data/blob/master/tutorials/table_scene_lms400.pcd?raw=true">table_scene_mug_stereo_textured.pcd</a></li>
<li><a class="reference external" href="https://github.com/PointCloudLibrary/data/blob/master/tutorials/table_scene_lms400.pcd?raw=true">table_scene_lms400.pcd</a></li>
</ul>
<p><tt class="docutils literal"><span class="pre">capture0001.pcd</span></tt> (organized, 640x480, 57553 NaNs):</p>
<div class="highlight-python"><div class="highlight"><pre>PCL:    0.926901 seconds

RLE:    0.348173 seconds
Kernel: 0.174194 seconds
</pre></div>
</div>
<p><tt class="docutils literal"><span class="pre">capture0002.pcd</span></tt> (organized, 640x480, 57269 NaNs):</p>
<div class="highlight-python"><div class="highlight"><pre>PCL:    0.931111 seconds

RLE:    0.345437 seconds
Kernel: 0.171373 seconds
</pre></div>
</div>
<p>Even if you include the RLE computation time (which could be amortized over
several operations, and perhaps optimized) in the total, the vertical kernel
beats the current PCL implementation. Discounting RLE, it&#8217;s more than 5x faster.</p>
<p><tt class="docutils literal"><span class="pre">table_scene_mug_stereo_textured.pcd</span></tt> (organized, 640x480, 97920 NaNs):</p>
<div class="highlight-python"><div class="highlight"><pre>PCL:    3.36001 seconds

RLE:    0.379737 seconds
Kernel: 0.183159 seconds
</pre></div>
</div>
<p>The very poor performance of PCL on the mug scene is a mystery to me. Perhaps
the larger number of NaNs has an effect?</p>
<p><tt class="docutils literal"><span class="pre">table_scene_lms400.pcd</span></tt> (dense, 460400 pts):</p>
<div class="highlight-python"><div class="highlight"><pre>PCL:    0.678805 seconds

RLE:    N/A
Kernel: 0.242546 seconds
</pre></div>
</div>
</div>
<div class="section" id="conclusions">
<h2>Conclusions</h2>
<p>For the simple operations considered here, vertical SSE is a huge win. In the
best case, this suggests that much of PCL could get at least a 3x speedup by
switching to the more SSE-friendly memory layout.</p>
<p>Vertical SSE presents some complications in usage and implementation for PCL,
but good solutions (RLE, kernel abstraction) are possible.</p>
<p>Looking at instruction sets, vertical SSE is especially advantageous both on
older and very new processors. On older processors, because it makes excellent
use of SSE2 instructions, whereas horizontal SSE may require horizontal
instructions (introduced in SSE3 and later) for good performance. On new
processors, because the latest AVX extensions expand SSE register to 256 bits,
allowing 8 floating point operations at a time instead of 4. The vertical SSE
techniques shown here trivially extend to AVX, and future instruction sets will
likely expand SSE registers even further. The upcoming AVX2 extensions add
dedicated <em>gather</em> instructions, which should improve performance with indices.</p>
</div>
<div class="section" id="remaining-questions">
<h2>Remaining questions</h2>
<p>Are there PCL algorithms that aren&#8217;t easily implementable in the proposed
kernel style?</p>
<p>How to handle nearest neighbor searches? These may be hard to vectorize.</p>
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