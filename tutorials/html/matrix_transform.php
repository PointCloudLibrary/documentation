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
    
    <title>Using a matrix to transform a point cloud</title>
    
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
            
  <div class="section" id="using-a-matrix-to-transform-a-point-cloud">
<span id="matrix-transform"></span><h1>Using a matrix to transform a point cloud</h1>
<p>In this tutorial we will learn how to transform a point cloud using a 4x4 matrix.
We will apply a rotation and a translation to a loaded point cloud and display then
result.</p>
<p>This program is able to load one PCD or PLY file; apply a matrix transformation on it
and display the original and transformed point cloud.</p>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>First, create a file, let&#8217;s say, <tt class="docutils literal"><span class="pre">matrix_transform.cpp</span></tt> in your favorite
editor, and place the following code inside it:</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre>  1
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
 50
 51
 52
 53
 54
 55
 56
 57
 58
 59
 60
 61
 62
 63
 64
 65
 66
 67
 68
 69
 70
 71
 72
 73
 74
 75
 76
 77
 78
 79
 80
 81
 82
 83
 84
 85
 86
 87
 88
 89
 90
 91
 92
 93
 94
 95
 96
 97
 98
 99
100
101
102
103
104
105
106
107
108
109
110
111
112
113
114
115
116
117
118
119
120
121
122
123
124
125
126
127
128
129
130
131
132
133
134
135
136</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;iostream&gt;</span>

<span class="cp">#include &lt;pcl/io/pcd_io.h&gt;</span>
<span class="cp">#include &lt;pcl/io/ply_io.h&gt;</span>
<span class="cp">#include &lt;pcl/point_cloud.h&gt;</span>
<span class="cp">#include &lt;pcl/console/parse.h&gt;</span>
<span class="cp">#include &lt;pcl/common/transforms.h&gt;</span>
<span class="cp">#include &lt;pcl/visualization/pcl_visualizer.h&gt;</span>

<span class="c1">// This function displays the help</span>
<span class="kt">void</span>
<span class="nf">showHelp</span><span class="p">(</span><span class="kt">char</span> <span class="o">*</span> <span class="n">program_name</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Usage: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">program_name</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; cloud_filename.[pcd|ply]&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;-h:  Show this help.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
<span class="p">}</span>

<span class="c1">// This is the main function</span>
<span class="kt">int</span>
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>

  <span class="c1">// Show help</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_switch</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-h&quot;</span><span class="p">)</span> <span class="o">||</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_switch</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--help&quot;</span><span class="p">))</span> <span class="p">{</span>
    <span class="n">showHelp</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
    <span class="k">return</span> <span class="mi">0</span><span class="p">;</span>
  <span class="p">}</span>

  <span class="c1">// Fetch point cloud filename in arguments | Works with PCD and PLY files</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">filenames</span><span class="p">;</span>
  <span class="kt">bool</span> <span class="n">file_is_pcd</span> <span class="o">=</span> <span class="nb">false</span><span class="p">;</span>

  <span class="n">filenames</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_file_extension_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;.ply&quot;</span><span class="p">);</span>

  <span class="k">if</span> <span class="p">(</span><span class="n">filenames</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">!=</span> <span class="mi">1</span><span class="p">)</span>  <span class="p">{</span>
    <span class="n">filenames</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_file_extension_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;.pcd&quot;</span><span class="p">);</span>

    <span class="k">if</span> <span class="p">(</span><span class="n">filenames</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">!=</span> <span class="mi">1</span><span class="p">)</span> <span class="p">{</span>
      <span class="n">showHelp</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
      <span class="k">return</span> <span class="o">-</span><span class="mi">1</span><span class="p">;</span>
    <span class="p">}</span> <span class="k">else</span> <span class="p">{</span>
      <span class="n">file_is_pcd</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
    <span class="p">}</span>
  <span class="p">}</span>

  <span class="c1">// Load file | Works with PCD and PLY files</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">source_cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">());</span>

  <span class="k">if</span> <span class="p">(</span><span class="n">file_is_pcd</span><span class="p">)</span> <span class="p">{</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="n">filenames</span><span class="p">[</span><span class="mi">0</span><span class="p">]],</span> <span class="o">*</span><span class="n">source_cloud</span><span class="p">)</span> <span class="o">&lt;</span> <span class="mi">0</span><span class="p">)</span>  <span class="p">{</span>
      <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Error loading point cloud &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">argv</span><span class="p">[</span><span class="n">filenames</span><span class="p">[</span><span class="mi">0</span><span class="p">]]</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
      <span class="n">showHelp</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
      <span class="k">return</span> <span class="o">-</span><span class="mi">1</span><span class="p">;</span>
    <span class="p">}</span>
  <span class="p">}</span> <span class="k">else</span> <span class="p">{</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPLYFile</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="n">filenames</span><span class="p">[</span><span class="mi">0</span><span class="p">]],</span> <span class="o">*</span><span class="n">source_cloud</span><span class="p">)</span> <span class="o">&lt;</span> <span class="mi">0</span><span class="p">)</span>  <span class="p">{</span>
      <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Error loading point cloud &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">argv</span><span class="p">[</span><span class="n">filenames</span><span class="p">[</span><span class="mi">0</span><span class="p">]]</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
      <span class="n">showHelp</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
      <span class="k">return</span> <span class="o">-</span><span class="mi">1</span><span class="p">;</span>
    <span class="p">}</span>
  <span class="p">}</span>

  <span class="cm">/* Reminder: how transformation matrices work :</span>

<span class="cm">           |-------&gt; This column is the translation</span>
<span class="cm">    | 1 0 0 x |  \</span>
<span class="cm">    | 0 1 0 y |   }-&gt; The identity 3x3 matrix (no rotation) on the left</span>
<span class="cm">    | 0 0 1 z |  /</span>
<span class="cm">    | 0 0 0 1 |    -&gt; We do not use this line (and it has to stay 0,0,0,1)</span>

<span class="cm">    METHOD #1: Using a Matrix4f</span>
<span class="cm">    This is the &quot;manual&quot; method, perfect to understand but error prone !</span>
<span class="cm">  */</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span> <span class="n">transform_1</span> <span class="o">=</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span><span class="o">::</span><span class="n">Identity</span><span class="p">();</span>

  <span class="c1">// Define a rotation matrix (see https://en.wikipedia.org/wiki/Rotation_matrix)</span>
  <span class="kt">float</span> <span class="n">theta</span> <span class="o">=</span> <span class="n">M_PI</span><span class="o">/</span><span class="mi">4</span><span class="p">;</span> <span class="c1">// The angle of rotation in radians</span>
  <span class="n">transform_1</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">0</span><span class="p">)</span> <span class="o">=</span> <span class="n">cos</span> <span class="p">(</span><span class="n">theta</span><span class="p">);</span>
  <span class="n">transform_1</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">1</span><span class="p">)</span> <span class="o">=</span> <span class="o">-</span><span class="n">sin</span><span class="p">(</span><span class="n">theta</span><span class="p">);</span>
  <span class="n">transform_1</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span><span class="mi">0</span><span class="p">)</span> <span class="o">=</span> <span class="n">sin</span> <span class="p">(</span><span class="n">theta</span><span class="p">);</span>
  <span class="n">transform_1</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span><span class="mi">1</span><span class="p">)</span> <span class="o">=</span> <span class="n">cos</span> <span class="p">(</span><span class="n">theta</span><span class="p">);</span>
  <span class="c1">//    (row, column)</span>

  <span class="c1">// Define a translation of 2.5 meters on the x axis.</span>
  <span class="n">transform_1</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">3</span><span class="p">)</span> <span class="o">=</span> <span class="mf">2.5</span><span class="p">;</span>

  <span class="c1">// Print the transformation</span>
  <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;Method #1: using a Matrix4f</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="n">transform_1</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="cm">/*  METHOD #2: Using a Affine3f</span>
<span class="cm">    This method is easier and less error prone</span>
<span class="cm">  */</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Affine3f</span> <span class="n">transform_2</span> <span class="o">=</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Affine3f</span><span class="o">::</span><span class="n">Identity</span><span class="p">();</span>

  <span class="c1">// Define a translation of 2.5 meters on the x axis.</span>
  <span class="n">transform_2</span><span class="p">.</span><span class="n">translation</span><span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="mf">2.5</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">;</span>

  <span class="c1">// The same rotation matrix as before; tetha radians arround Z axis</span>
  <span class="n">transform_2</span><span class="p">.</span><span class="n">rotate</span> <span class="p">(</span><span class="n">Eigen</span><span class="o">::</span><span class="n">AngleAxisf</span> <span class="p">(</span><span class="n">theta</span><span class="p">,</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span><span class="o">::</span><span class="n">UnitZ</span><span class="p">()));</span>

  <span class="c1">// Print the transformation</span>
  <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;</span><span class="se">\n</span><span class="s">Method #2: using an Affine3f</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="n">transform_2</span><span class="p">.</span><span class="n">matrix</span><span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="c1">// Executing the transformation</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">transformed_cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="c1">// You can either apply transform_1 or transform_2; they are the same</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">transformPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">source_cloud</span><span class="p">,</span> <span class="o">*</span><span class="n">transformed_cloud</span><span class="p">,</span> <span class="n">transform_2</span><span class="p">);</span>

  <span class="c1">// Visualization</span>
  <span class="n">printf</span><span class="p">(</span>  <span class="s">&quot;</span><span class="se">\n</span><span class="s">Point cloud colors :  white  = original point cloud</span><span class="se">\n</span><span class="s">&quot;</span>
      <span class="s">&quot;                        red  = transformed point cloud</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="n">viewer</span> <span class="p">(</span><span class="s">&quot;Matrix transformation example&quot;</span><span class="p">);</span>

   <span class="c1">// Define R,G,B colors for the point cloud</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">source_cloud_color_handler</span> <span class="p">(</span><span class="n">source_cloud</span><span class="p">,</span> <span class="mi">255</span><span class="p">,</span> <span class="mi">255</span><span class="p">,</span> <span class="mi">255</span><span class="p">);</span>
  <span class="c1">// We add the point cloud to the viewer and pass the color handler</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">source_cloud</span><span class="p">,</span> <span class="n">source_cloud_color_handler</span><span class="p">,</span> <span class="s">&quot;original_cloud&quot;</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">transformed_cloud_color_handler</span> <span class="p">(</span><span class="n">transformed_cloud</span><span class="p">,</span> <span class="mi">230</span><span class="p">,</span> <span class="mi">20</span><span class="p">,</span> <span class="mi">20</span><span class="p">);</span> <span class="c1">// Red</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">transformed_cloud</span><span class="p">,</span> <span class="n">transformed_cloud_color_handler</span><span class="p">,</span> <span class="s">&quot;transformed_cloud&quot;</span><span class="p">);</span>

  <span class="n">viewer</span><span class="p">.</span><span class="n">addCoordinateSystem</span> <span class="p">(</span><span class="mf">1.0</span><span class="p">,</span> <span class="s">&quot;cloud&quot;</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">setBackgroundColor</span><span class="p">(</span><span class="mf">0.05</span><span class="p">,</span> <span class="mf">0.05</span><span class="p">,</span> <span class="mf">0.05</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span> <span class="c1">// Setting background to a dark grey</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span> <span class="mi">2</span><span class="p">,</span> <span class="s">&quot;original_cloud&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span> <span class="mi">2</span><span class="p">,</span> <span class="s">&quot;transformed_cloud&quot;</span><span class="p">);</span>
  <span class="c1">//viewer.setPosition(800, 400); // Setting visualiser window position</span>

  <span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">viewer</span><span class="p">.</span><span class="n">wasStopped</span> <span class="p">())</span> <span class="p">{</span> <span class="c1">// Display the visualiser until &#39;q&#39; key is pressed</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">spinOnce</span> <span class="p">();</span>
  <span class="p">}</span>

  <span class="k">return</span> <span class="mi">0</span><span class="p">;</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>Now, let&#8217;s break down the code piece by piece.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="cp">#include &lt;iostream&gt;</span>

<span class="cp">#include &lt;pcl/io/pcd_io.h&gt;</span>
<span class="cp">#include &lt;pcl/io/ply_io.h&gt;</span>
<span class="cp">#include &lt;pcl/point_cloud.h&gt;</span>
<span class="cp">#include &lt;pcl/console/parse.h&gt;</span>
<span class="cp">#include &lt;pcl/common/transforms.h&gt;</span>
<span class="cp">#include &lt;pcl/visualization/pcl_visualizer.h&gt;</span>
</pre></div>
</div>
<p>We include all the headers we will make use of.
<strong>#include &lt;pcl/common/transforms.h&gt;</strong> allows us to use <strong>pcl::transformPointCloud</strong> function.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="c1">// This function displays the help</span>
<span class="kt">void</span>
<span class="nf">showHelp</span><span class="p">(</span><span class="kt">char</span> <span class="o">*</span> <span class="n">program_name</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Usage: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">program_name</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; cloud_filename.[pcd|ply]&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;-h:  Show this help.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
<span class="p">}</span>
</pre></div>
</div>
<p>This function display the help in case the user didn&#8217;t provide expected arguments.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Show help</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_switch</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-h&quot;</span><span class="p">)</span> <span class="o">||</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_switch</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--help&quot;</span><span class="p">))</span> <span class="p">{</span>
    <span class="n">showHelp</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
    <span class="k">return</span> <span class="mi">0</span><span class="p">;</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>We parse the arguments on the command line, either using <strong>-h</strong> or <strong>&#8211;help</strong> will
display the help. This terminates the program</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Fetch point cloud filename in arguments | Works with PCD and PLY files</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">filenames</span><span class="p">;</span>
  <span class="kt">bool</span> <span class="n">file_is_pcd</span> <span class="o">=</span> <span class="nb">false</span><span class="p">;</span>

  <span class="n">filenames</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_file_extension_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;.ply&quot;</span><span class="p">);</span>

  <span class="k">if</span> <span class="p">(</span><span class="n">filenames</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">!=</span> <span class="mi">1</span><span class="p">)</span>  <span class="p">{</span>
    <span class="n">filenames</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_file_extension_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;.pcd&quot;</span><span class="p">);</span>

    <span class="k">if</span> <span class="p">(</span><span class="n">filenames</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">!=</span> <span class="mi">1</span><span class="p">)</span> <span class="p">{</span>
      <span class="n">showHelp</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
      <span class="k">return</span> <span class="o">-</span><span class="mi">1</span><span class="p">;</span>
    <span class="p">}</span> <span class="k">else</span> <span class="p">{</span>
      <span class="n">file_is_pcd</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
    <span class="p">}</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>We look for .ply or .pcd filenames in the arguments. If not found; terminate the program.
The bool <strong>file_is_pcd</strong> will help us choose between loading PCD or PLY file.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Load file | Works with PCD and PLY files</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">source_cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">());</span>

  <span class="k">if</span> <span class="p">(</span><span class="n">file_is_pcd</span><span class="p">)</span> <span class="p">{</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="n">filenames</span><span class="p">[</span><span class="mi">0</span><span class="p">]],</span> <span class="o">*</span><span class="n">source_cloud</span><span class="p">)</span> <span class="o">&lt;</span> <span class="mi">0</span><span class="p">)</span>  <span class="p">{</span>
      <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Error loading point cloud &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">argv</span><span class="p">[</span><span class="n">filenames</span><span class="p">[</span><span class="mi">0</span><span class="p">]]</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
      <span class="n">showHelp</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
      <span class="k">return</span> <span class="o">-</span><span class="mi">1</span><span class="p">;</span>
    <span class="p">}</span>
  <span class="p">}</span> <span class="k">else</span> <span class="p">{</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPLYFile</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="n">filenames</span><span class="p">[</span><span class="mi">0</span><span class="p">]],</span> <span class="o">*</span><span class="n">source_cloud</span><span class="p">)</span> <span class="o">&lt;</span> <span class="mi">0</span><span class="p">)</span>  <span class="p">{</span>
      <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Error loading point cloud &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">argv</span><span class="p">[</span><span class="n">filenames</span><span class="p">[</span><span class="mi">0</span><span class="p">]]</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
      <span class="n">showHelp</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
      <span class="k">return</span> <span class="o">-</span><span class="mi">1</span><span class="p">;</span>
    <span class="p">}</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>We now load the PCD/PLY file and check if the file was loaded successfuly. Otherwise terminate
the program.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="cm">/* Reminder: how transformation matrices work :</span>

<span class="cm">           |-------&gt; This column is the translation</span>
<span class="cm">    | 1 0 0 x |  \</span>
<span class="cm">    | 0 1 0 y |   }-&gt; The identity 3x3 matrix (no rotation) on the left</span>
<span class="cm">    | 0 0 1 z |  /</span>
<span class="cm">    | 0 0 0 1 |    -&gt; We do not use this line (and it has to stay 0,0,0,1)</span>

<span class="cm">    METHOD #1: Using a Matrix4f</span>
<span class="cm">    This is the &quot;manual&quot; method, perfect to understand but error prone !</span>
<span class="cm">  */</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span> <span class="n">transform_1</span> <span class="o">=</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span><span class="o">::</span><span class="n">Identity</span><span class="p">();</span>
</pre></div>
</div>
<p>This is a first approach to create a transformation. This will help you understand how transformation matrices work.
We initialize a 4x4 matrix to identity;</p>
<div class="highlight-python"><div class="highlight"><pre>    |  1  0  0  0  |
i = |  0  1  0  0  |
    |  0  0  1  0  |
    |  0  0  0  1  |
</pre></div>
</div>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">The identity matrix is the equivalent of &#8220;1&#8221; when multiplying numbers; it changes nothing.
It is a square matrix with ones on the main diagonal and zeros elsewhere.</p>
</div>
<p>This means no transformation (no rotation and no translation). We do not use the
last row of the matrix.</p>
<p>The first 3 rows and colums (top left) components are the rotation
matrix. The first 3 rows of the last column is the translation.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Define a rotation matrix (see https://en.wikipedia.org/wiki/Rotation_matrix)</span>
  <span class="kt">float</span> <span class="n">theta</span> <span class="o">=</span> <span class="n">M_PI</span><span class="o">/</span><span class="mi">4</span><span class="p">;</span> <span class="c1">// The angle of rotation in radians</span>
  <span class="n">transform_1</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">0</span><span class="p">)</span> <span class="o">=</span> <span class="n">cos</span> <span class="p">(</span><span class="n">theta</span><span class="p">);</span>
  <span class="n">transform_1</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">1</span><span class="p">)</span> <span class="o">=</span> <span class="o">-</span><span class="n">sin</span><span class="p">(</span><span class="n">theta</span><span class="p">);</span>
  <span class="n">transform_1</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span><span class="mi">0</span><span class="p">)</span> <span class="o">=</span> <span class="n">sin</span> <span class="p">(</span><span class="n">theta</span><span class="p">);</span>
  <span class="n">transform_1</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span><span class="mi">1</span><span class="p">)</span> <span class="o">=</span> <span class="n">cos</span> <span class="p">(</span><span class="n">theta</span><span class="p">);</span>
  <span class="c1">//    (row, column)</span>

  <span class="c1">// Define a translation of 2.5 meters on the x axis.</span>
  <span class="n">transform_1</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">3</span><span class="p">)</span> <span class="o">=</span> <span class="mf">2.5</span><span class="p">;</span>

  <span class="c1">// Print the transformation</span>
  <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;Method #1: using a Matrix4f</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="n">transform_1</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
</pre></div>
</div>
<p>Here we defined a 45° (PI/4) rotation around the Z axis and a translation on the X axis.
This is the transformation we just defined</p>
<div class="highlight-python"><div class="highlight"><pre>    |  cos(θ) -sin(θ)  0.0 |
R = |  sin(θ)  cos(θ)  0.0 |
    |  0.0     0.0     1.0 |

t = &lt; 2.5, 0.0, 0.0 &gt;
</pre></div>
</div>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="cm">/*  METHOD #2: Using a Affine3f</span>
<span class="cm">    This method is easier and less error prone</span>
<span class="cm">  */</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Affine3f</span> <span class="n">transform_2</span> <span class="o">=</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Affine3f</span><span class="o">::</span><span class="n">Identity</span><span class="p">();</span>

  <span class="c1">// Define a translation of 2.5 meters on the x axis.</span>
  <span class="n">transform_2</span><span class="p">.</span><span class="n">translation</span><span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="mf">2.5</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">;</span>

  <span class="c1">// The same rotation matrix as before; tetha radians arround Z axis</span>
  <span class="n">transform_2</span><span class="p">.</span><span class="n">rotate</span> <span class="p">(</span><span class="n">Eigen</span><span class="o">::</span><span class="n">AngleAxisf</span> <span class="p">(</span><span class="n">theta</span><span class="p">,</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span><span class="o">::</span><span class="n">UnitZ</span><span class="p">()));</span>

  <span class="c1">// Print the transformation</span>
  <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;</span><span class="se">\n</span><span class="s">Method #2: using an Affine3f</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="n">transform_2</span><span class="p">.</span><span class="n">matrix</span><span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
</pre></div>
</div>
<p>This second approach is easier to understand and is less error prone.
Be carefull if you want to apply several rotations; rotations are not commutative ! This means than in most cases:
rotA * rotB != rotB * rotA.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Executing the transformation</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">transformed_cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="c1">// You can either apply transform_1 or transform_2; they are the same</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">transformPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">source_cloud</span><span class="p">,</span> <span class="o">*</span><span class="n">transformed_cloud</span><span class="p">,</span> <span class="n">transform_2</span><span class="p">);</span>
</pre></div>
</div>
<p>Now we apply this matrix on the point cloud <strong>source_cloud</strong> and we save the result in the
newly created <strong>transformed_cloud</strong>.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Visualization</span>
  <span class="n">printf</span><span class="p">(</span>  <span class="s">&quot;</span><span class="se">\n</span><span class="s">Point cloud colors :  white  = original point cloud</span><span class="se">\n</span><span class="s">&quot;</span>
      <span class="s">&quot;                        red  = transformed point cloud</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="n">viewer</span> <span class="p">(</span><span class="s">&quot;Matrix transformation example&quot;</span><span class="p">);</span>

   <span class="c1">// Define R,G,B colors for the point cloud</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">source_cloud_color_handler</span> <span class="p">(</span><span class="n">source_cloud</span><span class="p">,</span> <span class="mi">255</span><span class="p">,</span> <span class="mi">255</span><span class="p">,</span> <span class="mi">255</span><span class="p">);</span>
  <span class="c1">// We add the point cloud to the viewer and pass the color handler</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">source_cloud</span><span class="p">,</span> <span class="n">source_cloud_color_handler</span><span class="p">,</span> <span class="s">&quot;original_cloud&quot;</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">transformed_cloud_color_handler</span> <span class="p">(</span><span class="n">transformed_cloud</span><span class="p">,</span> <span class="mi">230</span><span class="p">,</span> <span class="mi">20</span><span class="p">,</span> <span class="mi">20</span><span class="p">);</span> <span class="c1">// Red</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">transformed_cloud</span><span class="p">,</span> <span class="n">transformed_cloud_color_handler</span><span class="p">,</span> <span class="s">&quot;transformed_cloud&quot;</span><span class="p">);</span>

  <span class="n">viewer</span><span class="p">.</span><span class="n">addCoordinateSystem</span> <span class="p">(</span><span class="mf">1.0</span><span class="p">,</span> <span class="s">&quot;cloud&quot;</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">setBackgroundColor</span><span class="p">(</span><span class="mf">0.05</span><span class="p">,</span> <span class="mf">0.05</span><span class="p">,</span> <span class="mf">0.05</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span> <span class="c1">// Setting background to a dark grey</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span> <span class="mi">2</span><span class="p">,</span> <span class="s">&quot;original_cloud&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span> <span class="mi">2</span><span class="p">,</span> <span class="s">&quot;transformed_cloud&quot;</span><span class="p">);</span>
  <span class="c1">//viewer.setPosition(800, 400); // Setting visualiser window position</span>

  <span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">viewer</span><span class="p">.</span><span class="n">wasStopped</span> <span class="p">())</span> <span class="p">{</span> <span class="c1">// Display the visualiser until &#39;q&#39; key is pressed</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">spinOnce</span> <span class="p">();</span>
  <span class="p">}</span>

  <span class="k">return</span> <span class="mi">0</span><span class="p">;</span>
</pre></div>
</div>
<p>We then visualize the result using the <strong>PCLVisualizer</strong>. The original point cloud will be
displayed white and the transformed one in red. The coordoniates axis will be displayed.
We also set the background color of the visualizer and the point display size.</p>
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

<span class="nb">project</span><span class="p">(</span><span class="s">pcl-matrix_transform</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.7</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">matrix_transform</span> <span class="s">matrix_transform.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">matrix_transform</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it. Simply do:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./matrix_transform cube.ply
</pre></div>
</div>
<p>You will see something similar to this:</p>
<div class="highlight-python"><div class="highlight"><pre>./matrix_transform cube.ply
[pcl::PLYReader] /home/victor/cube.ply:12: property &#39;list uint8 uint32 vertex_indices&#39; of element &#39;face&#39; is not handled
Method #1: using a Matrix4f
 0.707107 -0.707107         0       2.5
 0.707107  0.707107         0         0
        0         0         1         0
        0         0         0         1

Method #2: using an Affine3f
 0.707107 -0.707107         0       2.5
 0.707107  0.707107         0         0
        0         0         1         0
        0         0         0         1

Point cloud colors :  white   = original point cloud
                       red    = transformed point cloud
</pre></div>
</div>
<a class="reference internal image-reference" href="_images/cube_big.png"><img alt="_images/cube_big.png" src="_images/cube_big.png" style="height: 614px;" /></a>
</div>
<div class="section" id="more-about-transformations">
<h1>More about transformations</h1>
<div class="line-block">
<div class="line">So now you successfully transformed a point cloud using a transformation matrix.</div>
<div class="line">What if you want to transform a single point ? A vector ?</div>
</div>
<div class="line-block">
<div class="line">A point is defined in 3D space with its three coordinates; x,y,z (in a cartesian coordinate system).</div>
<div class="line">How can you multiply a vector (with 3 coordinates) with a 4x4 matrix ? You simply can&#8217;t ! If you don&#8217;t know why please refer to <a class="reference external" href="https://en.wikipedia.org/wiki/Matrix_multiplication">matrix multiplications on wikipedia</a>.</div>
</div>
<p>We need a vector with 4 components. What do you put in the last component ? It depends on what you want to do:</p>
<ul class="simple">
<li>If you want to transform a point: put 1 at the end of the vector so that the translation is taken in account.</li>
<li>If you want to transform the direction of a vector: put 0 at the end of the vector to ignore the translation.</li>
</ul>
<p>Here&#8217;s a quick example, we want to transform the following vector:</p>
<div class="highlight-python"><div class="highlight"><pre><span class="p">[</span><span class="mi">10</span><span class="p">,</span> <span class="mi">5</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">3</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="o">-</span><span class="mi">1</span><span class="p">]</span>
</pre></div>
</div>
<div class="line-block">
<div class="line">Where the first 3 components defines the origin coordinates and the last 3 components the direction.</div>
<div class="line">This vector starts at point 10, 5, 0 and ends at 13, 5, -1.</div>
</div>
<p>This is what you need to do to transform the vector:</p>
<div class="highlight-python"><div class="highlight"><pre>[10, 5, 0,  1] * 4x4_transformation_matrix
[3,  0, -1, 0] * 4x4_transformation_matrix
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