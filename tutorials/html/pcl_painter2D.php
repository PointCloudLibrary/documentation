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
    
    <title>PCLPainter2D</title>
    
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
            
  <div class="section" id="pclpainter2d">
<span id="pcl-painter2d"></span><h1>PCLPainter2D</h1>
<p>PCLPainter2D class provides a very simple interface (just like PCLPlotter) to draw 2D figures in a canvas or a view. One can add figures by simple <em>add*()</em> methods and in the end, show the canvas by simple <em>display*()</em> methods.</p>
<div class="section" id="basic-structure">
<h2>Basic structure</h2>
<p>Following is the usual way of using PCLPainter2D class.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="c1">//1. declare a Painter2D class</span>
<span class="n">PCLPainter2D</span> <span class="n">painter</span><span class="p">;</span>

<span class="c1">//2. add figures to the canvas by simple add*() methods. Use transform*() functions if required.</span>
<span class="n">painter</span><span class="p">.</span><span class="n">addCircle</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">0</span><span class="p">,</span><span class="mi">5</span><span class="p">);</span>
<span class="n">painter</span><span class="p">.</span><span class="n">addLine</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">0</span><span class="p">,</span> <span class="mi">5</span><span class="p">,</span><span class="mi">0</span><span class="p">);</span>

<span class="c1">//3. call a display*() (display (), spin (), spinOnce ()) method for the display of the canvas</span>
<span class="n">painter</span><span class="p">.</span><span class="n">display</span> <span class="p">();</span>
</pre></div>
</div>
</div>
<div class="section" id="discussions">
<h2>Discussions</h2>
<p>I am keeping this discussion here so that the design decision gets highlighted and is not lost in an unnoticed blog. Users who just want to learn this class can safely go ahead to the next section showing a complete example.</p>
<p>So, Lets see how 2D drawing works in VTK! The VTK user needs to first:</p>
<ol class="arabic simple">
<li>Make a subclass of vtkContextItem</li>
<li>Re-implement (override) Paint () of vtkContextItem. (shown in the figure)</li>
</ol>
<a class="reference internal image-reference" href="images/pcl_painter2D_contextItem.png"><img alt="images/pcl_painter2D_contextItem.png" class="align-center" src="images/pcl_painter2D_contextItem.png" style="width: 350px;" /></a>
<p>It would be really nice to have a vtkContextItem class which cuts off the overhead of subclassing and allows user to draw directly from the function calls. Unfortunately, we don&#8217;t have any (out of vtkChart, vtkPlot, vtkAxis,..., etc.) vtkContextItem class with that kind of behavior.</p>
<p>Thus, it maybe wise to have a class like Painter2D which can avoid subclassing in PCL and its rendering could be further optimized in the future.</p>
</div>
</div>
<div class="section" id="a-complete-example">
<h1>A complete example</h1>
<p>Following is a complete example depcting many usage of the Plotter. Copy it into a file named <tt class="docutils literal"><span class="pre">pcl_painter2D_demo.cpp</span></tt>.</p>
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
35
36
37
38
39
40
41
42
43
44
45
46
47
48
49
50</pre></div></td><td class="code"><div class="highlight"><pre><span class="cm">/* \author Kripasindhu Sarkar */</span>

<span class="cp">#include &lt;iostream&gt;</span>
<span class="cp">#include &lt;map&gt;</span>
<span class="cp">#include &lt;vector&gt;</span>
<span class="cp">#include &lt;pcl/visualization/pcl_painter2D.h&gt;</span>
<span class="c1">//----------------------------------------------------------------------------</span>

<span class="kt">int</span> <span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span> <span class="o">*</span> <span class="n">argv</span> <span class="p">[])</span>
<span class="p">{</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLPainter2D</span> <span class="o">*</span><span class="n">painter</span> <span class="o">=</span> <span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLPainter2D</span><span class="p">();</span>
  
  <span class="kt">int</span> <span class="n">winw</span> <span class="o">=</span> <span class="mi">800</span><span class="p">,</span> <span class="n">winh</span> <span class="o">=</span> <span class="mi">600</span><span class="p">;</span>
  <span class="n">painter</span><span class="o">-&gt;</span><span class="n">setWindowSize</span> <span class="p">(</span><span class="n">winw</span><span class="p">,</span> <span class="n">winh</span><span class="p">);</span>
  <span class="kt">int</span> <span class="n">xpos</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="kt">int</span> <span class="n">r</span> <span class="o">=</span> <span class="n">winw</span><span class="p">;</span>
  <span class="kt">int</span> <span class="n">R</span> <span class="o">=</span> <span class="mi">50</span><span class="p">;</span>
  <span class="kt">int</span> <span class="n">inc</span> <span class="o">=</span> <span class="mi">5</span><span class="p">;</span>
  <span class="kt">int</span> <span class="n">noc</span> <span class="o">=</span> <span class="n">winw</span><span class="o">/</span><span class="n">R</span><span class="p">;</span>
  
  <span class="k">while</span> <span class="p">(</span><span class="mi">1</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="c1">//draw noc no of circles</span>
    <span class="k">for</span> <span class="p">(</span><span class="kt">int</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">noc</span><span class="p">;</span> <span class="n">i</span><span class="o">++</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="k">if</span> <span class="p">(</span><span class="n">i</span> <span class="o">%</span> <span class="mi">2</span><span class="p">)</span> 
        <span class="n">painter</span><span class="o">-&gt;</span><span class="n">setBrushColor</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">200</span><span class="p">);</span>
      <span class="k">else</span>
        <span class="n">painter</span><span class="o">-&gt;</span><span class="n">setBrushColor</span> <span class="p">(</span><span class="mi">255</span><span class="p">,</span> <span class="mi">255</span><span class="p">,</span> <span class="mi">255</span><span class="p">,</span> <span class="mi">200</span><span class="p">);</span>
      
      <span class="kt">int</span> <span class="n">rad</span> <span class="o">=</span> <span class="n">r</span> <span class="o">-</span> <span class="n">i</span><span class="o">*</span><span class="n">R</span><span class="p">;</span>
      <span class="k">if</span> <span class="p">(</span><span class="n">rad</span> <span class="o">&lt;</span> <span class="mi">0</span><span class="p">)</span> <span class="p">{</span> <span class="n">rad</span> <span class="o">=</span> <span class="n">winw</span> <span class="o">+</span> <span class="n">rad</span><span class="p">;}</span>
      
      <span class="n">painter</span><span class="o">-&gt;</span><span class="n">addCircle</span> <span class="p">(</span><span class="n">winw</span><span class="o">/</span><span class="mi">2</span><span class="p">,</span> <span class="n">winh</span><span class="o">/</span><span class="mi">2</span><span class="p">,</span> <span class="n">rad</span><span class="p">);</span>
    <span class="p">}</span>
    
    <span class="n">r</span> <span class="o">-=</span> <span class="n">inc</span><span class="p">;</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">r</span> <span class="o">&lt;</span> <span class="n">winw</span><span class="o">-</span><span class="n">R</span><span class="p">)</span> <span class="n">r</span> <span class="o">=</span> <span class="n">winw</span> <span class="o">+</span> <span class="n">R</span><span class="p">;</span>

    <span class="n">painter</span><span class="o">-&gt;</span><span class="n">setBrushColor</span> <span class="p">(</span><span class="mi">255</span><span class="p">,</span><span class="mi">0</span><span class="p">,</span><span class="mi">0</span><span class="p">,</span><span class="mi">100</span><span class="p">);</span>
    <span class="n">painter</span><span class="o">-&gt;</span><span class="n">addRect</span> <span class="p">((</span><span class="n">xpos</span> <span class="o">+=</span> <span class="n">inc</span><span class="p">)</span> <span class="o">%</span> <span class="n">winw</span><span class="p">,</span> <span class="mi">100</span><span class="p">,</span> <span class="mi">100</span><span class="p">,</span> <span class="mi">100</span><span class="p">);</span>

    <span class="c1">//display</span>
    <span class="n">painter</span><span class="o">-&gt;</span><span class="n">spinOnce</span> <span class="p">();</span>
    <span class="n">painter</span><span class="o">-&gt;</span><span class="n">clearFigures</span> <span class="p">();</span>
  <span class="p">}</span>


  <span class="k">return</span> <span class="mi">0</span><span class="p">;</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
<div class="section" id="compiling-and-running-the-program">
<h2>Compiling and running the program</h2>
<p>Add the following lines to your <cite>CMakeLists.txt</cite> file:</p>
<div class="highlight-cmake"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre>1
2
3
4
5
6
7
8</pre></div></td><td class="code"><div class="highlight"><pre><span class="nb">cmake_minimum_required</span><span class="p">(</span><span class="s">VERSION</span> <span class="s">2.6</span> <span class="s">FATAL_ERROR</span><span class="p">)</span>
<span class="nb">project</span><span class="p">(</span><span class="s">pcl_painter2D_demo</span><span class="p">)</span>
<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.7</span><span class="p">)</span>
<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_executable</span><span class="p">(</span><span class="s">pcl_painter2D_demo</span> <span class="s">pcl_painter2D_demo.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span><span class="p">(</span><span class="s">pcl_painter2D_demo</span> <span class="o">${</span><span class="nv">PCL_COMMON_LIBRARIES</span><span class="o">}</span> <span class="o">${</span><span class="nv">PCL_VISUALIZATION_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>Compile and run the code by the following commands</p>
<div class="highlight-python"><div class="highlight"><pre>$ cmake .
$ make
$ ./pcl_painter2D_demo
</pre></div>
</div>
</div>
<div class="section" id="video">
<h2>Video</h2>
<p>The following video shows the the output of the demo.</p>
<iframe width="420" height="315" src="http://www.youtube.com/embed/0kPwTds7HSk" frameborder="0" allowfullscreen></iframe></div>
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