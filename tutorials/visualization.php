<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation - Point Cloud Library (PCL)</title>
</head>

<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8" />
    <title>PCL Visualization overview &#8212; PCL 0.0 documentation</title>
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
            
  <div class="section" id="pcl-visualization-overview">
<span id="visualization"></span><h1>PCL Visualization overview</h1>
<p>The <strong>pcl_visualization</strong> library was built for the purpose of being able to
quickly prototype and visualize the results of algorithms operating on 3D point
cloud data. Similar to OpenCV’s <strong>highgui</strong> routines for displaying 2D images
and for drawing basic 2D shapes on screen, the library offers:</p>
<blockquote>
<div><ul class="simple">
<li><p>methods for rendering and setting visual properties (colors, point sizes,
opacity, etc) for any n-D point cloud datasets in pcl::PointCloud&lt;T&gt; format;</p></li>
</ul>
<blockquote>
<div><img alt="_images/bunny.jpg" src="_images/bunny.jpg" />
</div></blockquote>
<ul class="simple">
<li><p>methods for drawing basic 3D shapes on screen (e.g., cylinders, spheres,
lines, polygons, etc) either from sets of points or from parametric
equations;</p></li>
</ul>
<blockquote>
<div><img alt="_images/shapes.jpg" src="_images/shapes.jpg" />
</div></blockquote>
<ul class="simple">
<li><p>a histogram visualization module (PCLHistogramVisualizer) for 2D plots;</p></li>
</ul>
<blockquote>
<div><img alt="_images/histogram.jpg" src="_images/histogram.jpg" />
</div></blockquote>
<ul class="simple">
<li><p>a multitude of Geometry and Color handler for pcl::PointCloud&lt;T&gt; datasets;</p></li>
</ul>
<blockquote>
<div><img alt="_images/normals.jpg" src="_images/normals.jpg" />
<img alt="_images/pcs.jpg" src="_images/pcs.jpg" />
</div></blockquote>
<ul class="simple">
<li><p>a pcl::RangeImage visualization module.</p></li>
</ul>
<blockquote>
<div><img alt="_images/range_image.jpg" src="_images/range_image.jpg" />
</div></blockquote>
</div></blockquote>
<p>The package makes use of the VTK library for 3D rendering for
range image and 2D operations.</p>
<p>For implementing your own visualizers, take a look at the tests and examples
accompanying the library.</p>
<div class="admonition note">
<p class="admonition-title">Note</p>
<p>Due to historical reasons, PCL 1.x stores RGB data as a packed float (to
preserve backward compatibility). To learn more about this, please see the
<a class="reference external" href="http://docs.pointclouds.org/trunk/structpcl_1_1_point_x_y_z_r_g_b.html">PointXYZRGB</a>.</p>
</div>
</div>
<div class="section" id="simple-cloud-visualization">
<h1>Simple Cloud Visualization</h1>
<p>If you just want to visualize something in your app with a few lines of code,
use a snippet like the following one:</p>
<div class="highlight-cpp notranslate"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
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
13</pre></div></td><td class="code"><div class="highlight"><pre><span></span> <span class="cp">#include</span> <span class="cpf">&lt;pcl_visualization/cloud_viewer.h&gt;</span><span class="cp"></span>
 <span class="c1">//...</span>
 <span class="kt">void</span>
 <span class="nf">foo</span> <span class="p">()</span>
 <span class="p">{</span>
   <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="n">cloud</span><span class="p">;</span>
   <span class="c1">//... populate cloud</span>
   <span class="n">pcl_visualization</span><span class="o">::</span><span class="n">CloudViewer</span> <span class="n">viewer</span><span class="p">(</span><span class="s">&quot;Simple Cloud Viewer&quot;</span><span class="p">);</span>
   <span class="n">viewer</span><span class="p">.</span><span class="n">showCloud</span><span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
   <span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">viewer</span><span class="p">.</span><span class="n">wasStopped</span><span class="p">())</span>
   <span class="p">{</span>
   <span class="p">}</span>
 <span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="pcd-viewer">
<h1>PCD Viewer</h1>
<p>A quick way for visualizing PCD (Point Cloud Data) files is by using
<strong>pcl_viewer</strong>. As of 0.2.7, pcl_viewer’s help screen looks like:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">Syntax</span> <span class="ow">is</span><span class="p">:</span> <span class="n">pcl_viewer</span> <span class="o">&lt;</span><span class="n">file_name</span> <span class="mf">1.</span><span class="o">.</span><span class="n">N</span><span class="o">&gt;.</span><span class="n">pcd</span> <span class="o">&lt;</span><span class="n">options</span><span class="o">&gt;</span>
  <span class="n">where</span> <span class="n">options</span> <span class="n">are</span><span class="p">:</span>
                     <span class="o">-</span><span class="n">bc</span> <span class="n">r</span><span class="p">,</span><span class="n">g</span><span class="p">,</span><span class="n">b</span>                <span class="o">=</span> <span class="n">background</span> <span class="n">color</span>
                     <span class="o">-</span><span class="n">fc</span> <span class="n">r</span><span class="p">,</span><span class="n">g</span><span class="p">,</span><span class="n">b</span>                <span class="o">=</span> <span class="n">foreground</span> <span class="n">color</span>
                     <span class="o">-</span><span class="n">ps</span> <span class="n">X</span>                    <span class="o">=</span> <span class="n">point</span> <span class="n">size</span> <span class="p">(</span><span class="mf">1.</span><span class="o">.</span><span class="mi">64</span><span class="p">)</span>
                     <span class="o">-</span><span class="n">opaque</span> <span class="n">X</span>                <span class="o">=</span> <span class="n">rendered</span> <span class="n">point</span> <span class="n">cloud</span> <span class="n">opacity</span> <span class="p">(</span><span class="mf">0.</span><span class="o">.</span><span class="mi">1</span><span class="p">)</span>
                     <span class="o">-</span><span class="n">ax</span> <span class="n">n</span>                    <span class="o">=</span> <span class="n">enable</span> <span class="n">on</span><span class="o">-</span><span class="n">screen</span> <span class="n">display</span> <span class="n">of</span> <span class="n">XYZ</span> <span class="n">axes</span> <span class="ow">and</span> <span class="n">scale</span> <span class="n">them</span> <span class="n">to</span> <span class="n">n</span>
                     <span class="o">-</span><span class="n">ax_pos</span> <span class="n">X</span><span class="p">,</span><span class="n">Y</span><span class="p">,</span><span class="n">Z</span>            <span class="o">=</span> <span class="k">if</span> <span class="n">axes</span> <span class="n">are</span> <span class="n">enabled</span><span class="p">,</span> <span class="nb">set</span> <span class="n">their</span> <span class="n">X</span><span class="p">,</span><span class="n">Y</span><span class="p">,</span><span class="n">Z</span> <span class="n">position</span> <span class="ow">in</span> <span class="n">space</span> <span class="p">(</span><span class="n">default</span> <span class="mi">0</span><span class="p">,</span><span class="mi">0</span><span class="p">,</span><span class="mi">0</span><span class="p">)</span>

                     <span class="o">-</span><span class="n">cam</span> <span class="p">(</span><span class="o">*</span><span class="p">)</span>                 <span class="o">=</span> <span class="n">use</span> <span class="n">given</span> <span class="n">camera</span> <span class="n">settings</span> <span class="k">as</span> <span class="n">initial</span> <span class="n">view</span>
 <span class="p">(</span><span class="o">*</span><span class="p">)</span> <span class="p">[</span><span class="n">Clipping</span> <span class="n">Range</span> <span class="o">/</span> <span class="n">Focal</span> <span class="n">Point</span> <span class="o">/</span> <span class="n">Position</span> <span class="o">/</span> <span class="n">ViewUp</span> <span class="o">/</span> <span class="n">Distance</span> <span class="o">/</span> <span class="n">Window</span> <span class="n">Size</span> <span class="o">/</span> <span class="n">Window</span> <span class="n">Pos</span><span class="p">]</span> <span class="ow">or</span> <span class="n">use</span> <span class="n">a</span> <span class="o">&lt;</span><span class="n">filename</span><span class="o">.</span><span class="n">cam</span><span class="o">&gt;</span> <span class="n">that</span> <span class="n">contains</span> <span class="n">the</span> <span class="n">same</span> <span class="n">information</span><span class="o">.</span>

                     <span class="o">-</span><span class="n">multiview</span> <span class="mi">0</span><span class="o">/</span><span class="mi">1</span>           <span class="o">=</span> <span class="n">enable</span><span class="o">/</span><span class="n">disable</span> <span class="n">auto</span><span class="o">-</span><span class="n">multi</span> <span class="n">viewport</span> <span class="n">rendering</span> <span class="p">(</span><span class="n">default</span> <span class="n">disabled</span><span class="p">)</span>


                     <span class="o">-</span><span class="n">normals</span> <span class="mi">0</span><span class="o">/</span><span class="n">X</span>             <span class="o">=</span> <span class="n">disable</span><span class="o">/</span><span class="n">enable</span> <span class="n">the</span> <span class="n">display</span> <span class="n">of</span> <span class="n">every</span> <span class="n">Xth</span> <span class="n">point</span><span class="s1">&#39;s surface normal as lines (default disabled)</span>
                     <span class="o">-</span><span class="n">normals_scale</span> <span class="n">X</span>         <span class="o">=</span> <span class="n">resize</span> <span class="n">the</span> <span class="n">normal</span> <span class="n">unit</span> <span class="n">vector</span> <span class="n">size</span> <span class="n">to</span> <span class="n">X</span> <span class="p">(</span><span class="n">default</span> <span class="mf">0.02</span><span class="p">)</span>

                     <span class="o">-</span><span class="n">pc</span> <span class="mi">0</span><span class="o">/</span><span class="n">X</span>                  <span class="o">=</span> <span class="n">disable</span><span class="o">/</span><span class="n">enable</span> <span class="n">the</span> <span class="n">display</span> <span class="n">of</span> <span class="n">every</span> <span class="n">Xth</span> <span class="n">point</span><span class="s1">&#39;s principal curvatures as lines (default disabled)</span>
                     <span class="o">-</span><span class="n">pc_scale</span> <span class="n">X</span>              <span class="o">=</span> <span class="n">resize</span> <span class="n">the</span> <span class="n">principal</span> <span class="n">curvatures</span> <span class="n">vectors</span> <span class="n">size</span> <span class="n">to</span> <span class="n">X</span> <span class="p">(</span><span class="n">default</span> <span class="mf">0.02</span><span class="p">)</span>


<span class="p">(</span><span class="n">Note</span><span class="p">:</span> <span class="k">for</span> <span class="n">multiple</span> <span class="o">.</span><span class="n">pcd</span> <span class="n">files</span><span class="p">,</span> <span class="n">provide</span> <span class="n">multiple</span> <span class="o">-</span><span class="p">{</span><span class="n">fc</span><span class="p">,</span><span class="n">ps</span><span class="p">}</span> <span class="n">parameters</span><span class="p">;</span> <span class="n">they</span> <span class="n">will</span> <span class="n">be</span> <span class="n">automatically</span> <span class="n">assigned</span> <span class="n">to</span> <span class="n">the</span> <span class="n">right</span> <span class="n">file</span><span class="p">)</span>
</pre></div>
</div>
</div>
<div class="section" id="usage-examples">
<h1>Usage examples</h1>
<div class="highlight-bash notranslate"><div class="highlight"><pre><span></span>$ pcl_viewer -multiview <span class="m">1</span> data/partial_cup_model.pcd data/partial_cup_model.pcd data/partial_cup_model.pcd
</pre></div>
</div>
<p>The above will load the <code class="docutils literal notranslate"><span class="pre">partial_cup_model.pcd</span></code> file 3 times, and will create a
multi-viewport rendering (<code class="docutils literal notranslate"><span class="pre">-multiview</span> <span class="pre">1</span></code>).</p>
<img alt="_images/ex1.jpg" src="_images/ex1.jpg" />
<p>Pressing <code class="docutils literal notranslate"><span class="pre">h</span></code> while the point clouds are being rendered will output the
following information on the console:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="o">|</span> <span class="n">Help</span><span class="p">:</span>
<span class="o">-------</span>
          <span class="n">p</span><span class="p">,</span> <span class="n">P</span>   <span class="p">:</span> <span class="n">switch</span> <span class="n">to</span> <span class="n">a</span> <span class="n">point</span><span class="o">-</span><span class="n">based</span> <span class="n">representation</span>
          <span class="n">w</span><span class="p">,</span> <span class="n">W</span>   <span class="p">:</span> <span class="n">switch</span> <span class="n">to</span> <span class="n">a</span> <span class="n">wireframe</span><span class="o">-</span><span class="n">based</span> <span class="n">representation</span> <span class="p">(</span><span class="n">where</span> <span class="n">available</span><span class="p">)</span>
          <span class="n">s</span><span class="p">,</span> <span class="n">S</span>   <span class="p">:</span> <span class="n">switch</span> <span class="n">to</span> <span class="n">a</span> <span class="n">surface</span><span class="o">-</span><span class="n">based</span> <span class="n">representation</span> <span class="p">(</span><span class="n">where</span> <span class="n">available</span><span class="p">)</span>

          <span class="n">j</span><span class="p">,</span> <span class="n">J</span>   <span class="p">:</span> <span class="n">take</span> <span class="n">a</span> <span class="o">.</span><span class="n">PNG</span> <span class="n">snapshot</span> <span class="n">of</span> <span class="n">the</span> <span class="n">current</span> <span class="n">window</span> <span class="n">view</span>
          <span class="n">c</span><span class="p">,</span> <span class="n">C</span>   <span class="p">:</span> <span class="n">display</span> <span class="n">current</span> <span class="n">camera</span><span class="o">/</span><span class="n">window</span> <span class="n">parameters</span>

         <span class="o">+</span> <span class="o">/</span> <span class="o">-</span>   <span class="p">:</span> <span class="n">increment</span><span class="o">/</span><span class="n">decrement</span> <span class="n">overall</span> <span class="n">point</span> <span class="n">size</span>

          <span class="n">g</span><span class="p">,</span> <span class="n">G</span>   <span class="p">:</span> <span class="n">display</span> <span class="n">scale</span> <span class="n">grid</span> <span class="p">(</span><span class="n">on</span><span class="o">/</span><span class="n">off</span><span class="p">)</span>
          <span class="n">u</span><span class="p">,</span> <span class="n">U</span>   <span class="p">:</span> <span class="n">display</span> <span class="n">lookup</span> <span class="n">table</span> <span class="p">(</span><span class="n">on</span><span class="o">/</span><span class="n">off</span><span class="p">)</span>

    <span class="n">r</span><span class="p">,</span> <span class="n">R</span> <span class="p">[</span><span class="o">+</span> <span class="n">ALT</span><span class="p">]</span> <span class="p">:</span> <span class="n">reset</span> <span class="n">camera</span> <span class="p">[</span><span class="n">to</span> <span class="n">viewpoint</span> <span class="o">=</span> <span class="p">{</span><span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">}</span> <span class="o">-&gt;</span> <span class="n">center_</span><span class="p">{</span><span class="n">x</span><span class="p">,</span> <span class="n">y</span><span class="p">,</span> <span class="n">z</span><span class="p">}]</span>

    <span class="n">ALT</span> <span class="o">+</span> <span class="n">s</span><span class="p">,</span> <span class="n">S</span>   <span class="p">:</span> <span class="n">turn</span> <span class="n">stereo</span> <span class="n">mode</span> <span class="n">on</span><span class="o">/</span><span class="n">off</span>
    <span class="n">ALT</span> <span class="o">+</span> <span class="n">f</span><span class="p">,</span> <span class="n">F</span>   <span class="p">:</span> <span class="n">switch</span> <span class="n">between</span> <span class="n">maximized</span> <span class="n">window</span> <span class="n">mode</span> <span class="ow">and</span> <span class="n">original</span> <span class="n">size</span>

          <span class="n">l</span><span class="p">,</span> <span class="n">L</span>           <span class="p">:</span> <span class="nb">list</span> <span class="nb">all</span> <span class="n">available</span> <span class="n">geometric</span> <span class="ow">and</span> <span class="n">color</span> <span class="n">handlers</span> <span class="k">for</span> <span class="n">the</span> <span class="n">current</span> <span class="n">actor</span> <span class="nb">map</span>
    <span class="n">ALT</span> <span class="o">+</span> <span class="mf">0.</span><span class="o">.</span><span class="mi">9</span> <span class="p">[</span><span class="o">+</span> <span class="n">CTRL</span><span class="p">]</span>  <span class="p">:</span> <span class="n">switch</span> <span class="n">between</span> <span class="n">different</span> <span class="n">geometric</span> <span class="n">handlers</span> <span class="p">(</span><span class="n">where</span> <span class="n">available</span><span class="p">)</span>
          <span class="mf">0.</span><span class="o">.</span><span class="mi">9</span> <span class="p">[</span><span class="o">+</span> <span class="n">CTRL</span><span class="p">]</span>  <span class="p">:</span> <span class="n">switch</span> <span class="n">between</span> <span class="n">different</span> <span class="n">color</span> <span class="n">handlers</span> <span class="p">(</span><span class="n">where</span> <span class="n">available</span><span class="p">)</span>
</pre></div>
</div>
<p>Pressing <code class="docutils literal notranslate"><span class="pre">l</span></code> will show the current list of available geometry/color handlers
for the datasets that we loaded. In this example:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">List</span> <span class="n">of</span> <span class="n">available</span> <span class="n">geometry</span> <span class="n">handlers</span> <span class="k">for</span> <span class="n">actor</span> <span class="n">partial_cup_model</span><span class="o">.</span><span class="n">pcd</span><span class="o">-</span><span class="mi">0</span><span class="p">:</span> <span class="n">xyz</span><span class="p">(</span><span class="mi">1</span><span class="p">)</span> <span class="n">normal_xyz</span><span class="p">(</span><span class="mi">2</span><span class="p">)</span>
<span class="n">List</span> <span class="n">of</span> <span class="n">available</span> <span class="n">color</span> <span class="n">handlers</span> <span class="k">for</span> <span class="n">actor</span> <span class="n">partial_cup_model</span><span class="o">.</span><span class="n">pcd</span><span class="o">-</span><span class="mi">0</span><span class="p">:</span> <span class="p">[</span><span class="n">random</span><span class="p">](</span><span class="mi">1</span><span class="p">)</span> <span class="n">x</span><span class="p">(</span><span class="mi">2</span><span class="p">)</span> <span class="n">y</span><span class="p">(</span><span class="mi">3</span><span class="p">)</span> <span class="n">z</span><span class="p">(</span><span class="mi">4</span><span class="p">)</span> <span class="n">normal_x</span><span class="p">(</span><span class="mi">5</span><span class="p">)</span> <span class="n">normal_y</span><span class="p">(</span><span class="mi">6</span><span class="p">)</span> <span class="n">normal_z</span><span class="p">(</span><span class="mi">7</span><span class="p">)</span> <span class="n">curvature</span><span class="p">(</span><span class="mi">8</span><span class="p">)</span> <span class="n">boundary</span><span class="p">(</span><span class="mi">9</span><span class="p">)</span> <span class="n">k</span><span class="p">(</span><span class="mi">10</span><span class="p">)</span> <span class="n">principal_curvature_x</span><span class="p">(</span><span class="mi">11</span><span class="p">)</span> <span class="n">principal_curvature_y</span><span class="p">(</span><span class="mi">12</span><span class="p">)</span> <span class="n">principal_curvature_z</span><span class="p">(</span><span class="mi">13</span><span class="p">)</span> <span class="n">pc1</span><span class="p">(</span><span class="mi">14</span><span class="p">)</span> <span class="n">pc2</span><span class="p">(</span><span class="mi">15</span><span class="p">)</span>
</pre></div>
</div>
<p>Switching to a <code class="docutils literal notranslate"><span class="pre">normal_xyz</span></code> geometric handler using <code class="docutils literal notranslate"><span class="pre">ALT+1</span></code> and then
pressing <code class="docutils literal notranslate"><span class="pre">8</span></code> to switch to a curvature color handler, should result in the
following:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>$ pcl_viewer -normals 100 data/partial_cup_model.pcd
</pre></div>
</div>
<img alt="_images/ex2.jpg" src="_images/ex2.jpg" />
<p>The above will load the <code class="docutils literal notranslate"><span class="pre">partial_cup_model.pcd</span></code> file and render its every
<code class="docutils literal notranslate"><span class="pre">100</span></code> th surface normal on screen.</p>
<div class="highlight-bash notranslate"><div class="highlight"><pre><span></span>$ pcl_viewer -pc <span class="m">100</span> data/partial_cup_model.pcd
</pre></div>
</div>
<img alt="_images/ex3.jpg" src="_images/ex3.jpg" />
<p>The above will load the <code class="docutils literal notranslate"><span class="pre">partial_cup_model.pcd</span></code> file and render its every
<code class="docutils literal notranslate"><span class="pre">100</span></code> th principal curvature (+surface normal) on screen.</p>
<img alt="_images/ex4.jpg" src="_images/ex4.jpg" />
<div class="highlight-bash notranslate"><div class="highlight"><pre><span></span>$ pcl_viewer data/bun000.pcd data/bun045.pcd -ax <span class="m">0</span>.5 -ps <span class="m">3</span> -ps <span class="m">1</span>
</pre></div>
</div>
<p>The above assumes that the <code class="docutils literal notranslate"><span class="pre">bun000.pcd</span></code> and <code class="docutils literal notranslate"><span class="pre">bun045.pcd</span></code> datasets have been
downloaded and are available. The results shown in the following picture were
obtained after pressing <code class="docutils literal notranslate"><span class="pre">u</span></code> and <code class="docutils literal notranslate"><span class="pre">g</span></code> to enable the lookup table and on-grid
display.</p>
<img alt="_images/ex5.jpg" src="_images/ex5.jpg" />
</div>
<div class="section" id="range-image-visualizer">
<h1>Range Image Visualizer</h1>
<p>A quick way for visualizing range images is by using the binary of the tutorial
for range_image_visualization:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>$ tutorial_range_image_visualization data/office_scene.pcd
</pre></div>
</div>
<p>The above will load the <code class="docutils literal notranslate"><span class="pre">office_scene.pcd</span></code> point cloud file, create a range
image from it and visualize both, the point cloud and the range image.</p>
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