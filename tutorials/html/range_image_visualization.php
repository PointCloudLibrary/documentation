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
    
    <title>How to visualize a range image</title>
    
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
            
  <div class="section" id="how-to-visualize-a-range-image">
<span id="range-image-visualization"></span><h1>How to visualize a range image</h1>
<p>This tutorial demonstrates how to visualize a range image with two different means. As a point cloud (since RangeImage is derived from PointCloud) in a 3D viewer and as a picture visualizing the range values as colors.</p>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>First, create a file called, let&#8217;s say, <tt class="docutils literal"><span class="pre">range_image_visualization.cpp</span></tt> in your favorite
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
136
137
138
139
140
141
142
143
144
145
146
147
148
149
150
151
152
153
154
155
156
157
158
159
160
161
162
163
164
165
166
167</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;iostream&gt;</span>

<span class="cp">#include &lt;boost/thread/thread.hpp&gt;</span>

<span class="cp">#include &lt;pcl/common/common_headers.h&gt;</span>
<span class="cp">#include &lt;pcl/range_image/range_image.h&gt;</span>
<span class="cp">#include &lt;pcl/io/pcd_io.h&gt;</span>
<span class="cp">#include &lt;pcl/visualization/range_image_visualizer.h&gt;</span>
<span class="cp">#include &lt;pcl/visualization/pcl_visualizer.h&gt;</span>
<span class="cp">#include &lt;pcl/console/parse.h&gt;</span>

<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">PointType</span><span class="p">;</span>

<span class="c1">// --------------------</span>
<span class="c1">// -----Parameters-----</span>
<span class="c1">// --------------------</span>
<span class="kt">float</span> <span class="n">angular_resolution_x</span> <span class="o">=</span> <span class="mf">0.5f</span><span class="p">,</span>
      <span class="n">angular_resolution_y</span> <span class="o">=</span> <span class="n">angular_resolution_x</span><span class="p">;</span>
<span class="n">pcl</span><span class="o">::</span><span class="n">RangeImage</span><span class="o">::</span><span class="n">CoordinateFrame</span> <span class="n">coordinate_frame</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">RangeImage</span><span class="o">::</span><span class="n">CAMERA_FRAME</span><span class="p">;</span>
<span class="kt">bool</span> <span class="n">live_update</span> <span class="o">=</span> <span class="nb">false</span><span class="p">;</span>

<span class="c1">// --------------</span>
<span class="c1">// -----Help-----</span>
<span class="c1">// --------------</span>
<span class="kt">void</span> 
<span class="nf">printUsage</span> <span class="p">(</span><span class="k">const</span> <span class="kt">char</span><span class="o">*</span> <span class="n">progName</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;</span><span class="se">\n\n</span><span class="s">Usage: &quot;</span><span class="o">&lt;&lt;</span><span class="n">progName</span><span class="o">&lt;&lt;</span><span class="s">&quot; [options] &lt;scene.pcd&gt;</span><span class="se">\n\n</span><span class="s">&quot;</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;Options:</span><span class="se">\n</span><span class="s">&quot;</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;-------------------------------------------</span><span class="se">\n</span><span class="s">&quot;</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;-rx &lt;float&gt;  angular resolution in degrees (default &quot;</span><span class="o">&lt;&lt;</span><span class="n">angular_resolution_x</span><span class="o">&lt;&lt;</span><span class="s">&quot;)</span><span class="se">\n</span><span class="s">&quot;</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;-ry &lt;float&gt;  angular resolution in degrees (default &quot;</span><span class="o">&lt;&lt;</span><span class="n">angular_resolution_y</span><span class="o">&lt;&lt;</span><span class="s">&quot;)</span><span class="se">\n</span><span class="s">&quot;</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;-c &lt;int&gt;     coordinate frame (default &quot;</span><span class="o">&lt;&lt;</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span><span class="n">coordinate_frame</span><span class="o">&lt;&lt;</span><span class="s">&quot;)</span><span class="se">\n</span><span class="s">&quot;</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;-l           live update - update the range image according to the selected view in the 3D viewer.</span><span class="se">\n</span><span class="s">&quot;</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;-h           this help</span><span class="se">\n</span><span class="s">&quot;</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;</span><span class="se">\n\n</span><span class="s">&quot;</span><span class="p">;</span>
<span class="p">}</span>

<span class="kt">void</span> 
<span class="nf">setViewerPose</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&amp;</span> <span class="n">viewer</span><span class="p">,</span> <span class="k">const</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Affine3f</span><span class="o">&amp;</span> <span class="n">viewer_pose</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span> <span class="n">pos_vector</span> <span class="o">=</span> <span class="n">viewer_pose</span> <span class="o">*</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span><span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span> <span class="n">look_at_vector</span> <span class="o">=</span> <span class="n">viewer_pose</span><span class="p">.</span><span class="n">rotation</span> <span class="p">()</span> <span class="o">*</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span><span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">1</span><span class="p">)</span> <span class="o">+</span> <span class="n">pos_vector</span><span class="p">;</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span> <span class="n">up_vector</span> <span class="o">=</span> <span class="n">viewer_pose</span><span class="p">.</span><span class="n">rotation</span> <span class="p">()</span> <span class="o">*</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span><span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="o">-</span><span class="mi">1</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">setCameraPosition</span> <span class="p">(</span><span class="n">pos_vector</span><span class="p">[</span><span class="mi">0</span><span class="p">],</span> <span class="n">pos_vector</span><span class="p">[</span><span class="mi">1</span><span class="p">],</span> <span class="n">pos_vector</span><span class="p">[</span><span class="mi">2</span><span class="p">],</span>
                            <span class="n">look_at_vector</span><span class="p">[</span><span class="mi">0</span><span class="p">],</span> <span class="n">look_at_vector</span><span class="p">[</span><span class="mi">1</span><span class="p">],</span> <span class="n">look_at_vector</span><span class="p">[</span><span class="mi">2</span><span class="p">],</span>
                            <span class="n">up_vector</span><span class="p">[</span><span class="mi">0</span><span class="p">],</span> <span class="n">up_vector</span><span class="p">[</span><span class="mi">1</span><span class="p">],</span> <span class="n">up_vector</span><span class="p">[</span><span class="mi">2</span><span class="p">]);</span>
<span class="p">}</span>

<span class="c1">// --------------</span>
<span class="c1">// -----Main-----</span>
<span class="c1">// --------------</span>
<span class="kt">int</span> 
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="c1">// --------------------------------------</span>
  <span class="c1">// -----Parse Command Line Arguments-----</span>
  <span class="c1">// --------------------------------------</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-h&quot;</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">printUsage</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
    <span class="k">return</span> <span class="mi">0</span><span class="p">;</span>
  <span class="p">}</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-l&quot;</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">live_update</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Live update is on.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
  <span class="p">}</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-rx&quot;</span><span class="p">,</span> <span class="n">angular_resolution_x</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span><span class="p">)</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Setting angular resolution in x-direction to &quot;</span><span class="o">&lt;&lt;</span><span class="n">angular_resolution_x</span><span class="o">&lt;&lt;</span><span class="s">&quot;deg.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-ry&quot;</span><span class="p">,</span> <span class="n">angular_resolution_y</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span><span class="p">)</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Setting angular resolution in y-direction to &quot;</span><span class="o">&lt;&lt;</span><span class="n">angular_resolution_y</span><span class="o">&lt;&lt;</span><span class="s">&quot;deg.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
  <span class="kt">int</span> <span class="n">tmp_coordinate_frame</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-c&quot;</span><span class="p">,</span> <span class="n">tmp_coordinate_frame</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">coordinate_frame</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">RangeImage</span><span class="o">::</span><span class="n">CoordinateFrame</span> <span class="p">(</span><span class="n">tmp_coordinate_frame</span><span class="p">);</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Using coordinate frame &quot;</span><span class="o">&lt;&lt;</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span><span class="n">coordinate_frame</span><span class="o">&lt;&lt;</span><span class="s">&quot;.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
  <span class="p">}</span>
  <span class="n">angular_resolution_x</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">deg2rad</span> <span class="p">(</span><span class="n">angular_resolution_x</span><span class="p">);</span>
  <span class="n">angular_resolution_y</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">deg2rad</span> <span class="p">(</span><span class="n">angular_resolution_y</span><span class="p">);</span>
  
  <span class="c1">// ------------------------------------------------------------------</span>
  <span class="c1">// -----Read pcd file or create example point cloud if not given-----</span>
  <span class="c1">// ------------------------------------------------------------------</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">point_cloud_ptr</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;&amp;</span> <span class="n">point_cloud</span> <span class="o">=</span> <span class="o">*</span><span class="n">point_cloud_ptr</span><span class="p">;</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Affine3f</span> <span class="n">scene_sensor_pose</span> <span class="p">(</span><span class="n">Eigen</span><span class="o">::</span><span class="n">Affine3f</span><span class="o">::</span><span class="n">Identity</span> <span class="p">());</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">pcd_filename_indices</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_file_extension_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;pcd&quot;</span><span class="p">);</span>
  <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">pcd_filename_indices</span><span class="p">.</span><span class="n">empty</span> <span class="p">())</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">filename</span> <span class="o">=</span> <span class="n">argv</span><span class="p">[</span><span class="n">pcd_filename_indices</span><span class="p">[</span><span class="mi">0</span><span class="p">]];</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="n">filename</span><span class="p">,</span> <span class="n">point_cloud</span><span class="p">)</span> <span class="o">==</span> <span class="o">-</span><span class="mi">1</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Was not able to open file </span><span class="se">\&quot;</span><span class="s">&quot;</span><span class="o">&lt;&lt;</span><span class="n">filename</span><span class="o">&lt;&lt;</span><span class="s">&quot;</span><span class="se">\&quot;</span><span class="s">.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
      <span class="n">printUsage</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
      <span class="k">return</span> <span class="mi">0</span><span class="p">;</span>
    <span class="p">}</span>
    <span class="n">scene_sensor_pose</span> <span class="o">=</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Affine3f</span> <span class="p">(</span><span class="n">Eigen</span><span class="o">::</span><span class="n">Translation3f</span> <span class="p">(</span><span class="n">point_cloud</span><span class="p">.</span><span class="n">sensor_origin_</span><span class="p">[</span><span class="mi">0</span><span class="p">],</span>
                                                             <span class="n">point_cloud</span><span class="p">.</span><span class="n">sensor_origin_</span><span class="p">[</span><span class="mi">1</span><span class="p">],</span>
                                                             <span class="n">point_cloud</span><span class="p">.</span><span class="n">sensor_origin_</span><span class="p">[</span><span class="mi">2</span><span class="p">]))</span> <span class="o">*</span>
                        <span class="n">Eigen</span><span class="o">::</span><span class="n">Affine3f</span> <span class="p">(</span><span class="n">point_cloud</span><span class="p">.</span><span class="n">sensor_orientation_</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="k">else</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;</span><span class="se">\n</span><span class="s">No *.pcd file given =&gt; Genarating example point cloud.</span><span class="se">\n\n</span><span class="s">&quot;</span><span class="p">;</span>
    <span class="k">for</span> <span class="p">(</span><span class="kt">float</span> <span class="n">x</span><span class="o">=-</span><span class="mf">0.5f</span><span class="p">;</span> <span class="n">x</span><span class="o">&lt;=</span><span class="mf">0.5f</span><span class="p">;</span> <span class="n">x</span><span class="o">+=</span><span class="mf">0.01f</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="k">for</span> <span class="p">(</span><span class="kt">float</span> <span class="n">y</span><span class="o">=-</span><span class="mf">0.5f</span><span class="p">;</span> <span class="n">y</span><span class="o">&lt;=</span><span class="mf">0.5f</span><span class="p">;</span> <span class="n">y</span><span class="o">+=</span><span class="mf">0.01f</span><span class="p">)</span>
      <span class="p">{</span>
        <span class="n">PointType</span> <span class="n">point</span><span class="p">;</span>  <span class="n">point</span><span class="p">.</span><span class="n">x</span> <span class="o">=</span> <span class="n">x</span><span class="p">;</span>  <span class="n">point</span><span class="p">.</span><span class="n">y</span> <span class="o">=</span> <span class="n">y</span><span class="p">;</span>  <span class="n">point</span><span class="p">.</span><span class="n">z</span> <span class="o">=</span> <span class="mf">2.0f</span> <span class="o">-</span> <span class="n">y</span><span class="p">;</span>
        <span class="n">point_cloud</span><span class="p">.</span><span class="n">points</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">point</span><span class="p">);</span>
      <span class="p">}</span>
    <span class="p">}</span>
    <span class="n">point_cloud</span><span class="p">.</span><span class="n">width</span> <span class="o">=</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span> <span class="n">point_cloud</span><span class="p">.</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span>  <span class="n">point_cloud</span><span class="p">.</span><span class="n">height</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
  <span class="p">}</span>
  
  <span class="c1">// -----------------------------------------------</span>
  <span class="c1">// -----Create RangeImage from the PointCloud-----</span>
  <span class="c1">// -----------------------------------------------</span>
  <span class="kt">float</span> <span class="n">noise_level</span> <span class="o">=</span> <span class="mf">0.0</span><span class="p">;</span>
  <span class="kt">float</span> <span class="n">min_range</span> <span class="o">=</span> <span class="mf">0.0f</span><span class="p">;</span>
  <span class="kt">int</span> <span class="n">border_size</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">RangeImage</span><span class="o">&gt;</span> <span class="n">range_image_ptr</span><span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">RangeImage</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">RangeImage</span><span class="o">&amp;</span> <span class="n">range_image</span> <span class="o">=</span> <span class="o">*</span><span class="n">range_image_ptr</span><span class="p">;</span>   
  <span class="n">range_image</span><span class="p">.</span><span class="n">createFromPointCloud</span> <span class="p">(</span><span class="n">point_cloud</span><span class="p">,</span> <span class="n">angular_resolution_x</span><span class="p">,</span> <span class="n">angular_resolution_y</span><span class="p">,</span>
                                    <span class="n">pcl</span><span class="o">::</span><span class="n">deg2rad</span> <span class="p">(</span><span class="mf">360.0f</span><span class="p">),</span> <span class="n">pcl</span><span class="o">::</span><span class="n">deg2rad</span> <span class="p">(</span><span class="mf">180.0f</span><span class="p">),</span>
                                    <span class="n">scene_sensor_pose</span><span class="p">,</span> <span class="n">coordinate_frame</span><span class="p">,</span> <span class="n">noise_level</span><span class="p">,</span> <span class="n">min_range</span><span class="p">,</span> <span class="n">border_size</span><span class="p">);</span>
  
  <span class="c1">// --------------------------------------------</span>
  <span class="c1">// -----Open 3D viewer and add point cloud-----</span>
  <span class="c1">// --------------------------------------------</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="n">viewer</span> <span class="p">(</span><span class="s">&quot;3D Viewer&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">setBackgroundColor</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span> <span class="mi">1</span><span class="p">,</span> <span class="mi">1</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointWithRange</span><span class="o">&gt;</span> <span class="n">range_image_color_handler</span> <span class="p">(</span><span class="n">range_image_ptr</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">range_image_ptr</span><span class="p">,</span> <span class="n">range_image_color_handler</span><span class="p">,</span> <span class="s">&quot;range image&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span> <span class="mi">1</span><span class="p">,</span> <span class="s">&quot;range image&quot;</span><span class="p">);</span>
  <span class="c1">//viewer.addCoordinateSystem (1.0f, &quot;global&quot;);</span>
  <span class="c1">//PointCloudColorHandlerCustom&lt;PointType&gt; point_cloud_color_handler (point_cloud_ptr, 150, 150, 150);</span>
  <span class="c1">//viewer.addPointCloud (point_cloud_ptr, point_cloud_color_handler, &quot;original point cloud&quot;);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">initCameraParameters</span> <span class="p">();</span>
  <span class="n">setViewerPose</span><span class="p">(</span><span class="n">viewer</span><span class="p">,</span> <span class="n">range_image</span><span class="p">.</span><span class="n">getTransformationToWorldSystem</span> <span class="p">());</span>
  
  <span class="c1">// --------------------------</span>
  <span class="c1">// -----Show range image-----</span>
  <span class="c1">// --------------------------</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">RangeImageVisualizer</span> <span class="n">range_image_widget</span> <span class="p">(</span><span class="s">&quot;Range image&quot;</span><span class="p">);</span>
  <span class="n">range_image_widget</span><span class="p">.</span><span class="n">showRangeImage</span> <span class="p">(</span><span class="n">range_image</span><span class="p">);</span>
  
  <span class="c1">//--------------------</span>
  <span class="c1">// -----Main loop-----</span>
  <span class="c1">//--------------------</span>
  <span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">viewer</span><span class="p">.</span><span class="n">wasStopped</span> <span class="p">())</span>
  <span class="p">{</span>
    <span class="n">range_image_widget</span><span class="p">.</span><span class="n">spinOnce</span> <span class="p">();</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">spinOnce</span> <span class="p">();</span>
    <span class="n">pcl_sleep</span> <span class="p">(</span><span class="mf">0.01</span><span class="p">);</span>
    
    <span class="k">if</span> <span class="p">(</span><span class="n">live_update</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">scene_sensor_pose</span> <span class="o">=</span> <span class="n">viewer</span><span class="p">.</span><span class="n">getViewerPose</span><span class="p">();</span>
      <span class="n">range_image</span><span class="p">.</span><span class="n">createFromPointCloud</span> <span class="p">(</span><span class="n">point_cloud</span><span class="p">,</span> <span class="n">angular_resolution_x</span><span class="p">,</span> <span class="n">angular_resolution_y</span><span class="p">,</span>
                                        <span class="n">pcl</span><span class="o">::</span><span class="n">deg2rad</span> <span class="p">(</span><span class="mf">360.0f</span><span class="p">),</span> <span class="n">pcl</span><span class="o">::</span><span class="n">deg2rad</span> <span class="p">(</span><span class="mf">180.0f</span><span class="p">),</span>
                                        <span class="n">scene_sensor_pose</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">RangeImage</span><span class="o">::</span><span class="n">LASER_FRAME</span><span class="p">,</span> <span class="n">noise_level</span><span class="p">,</span> <span class="n">min_range</span><span class="p">,</span> <span class="n">border_size</span><span class="p">);</span>
      <span class="n">range_image_widget</span><span class="p">.</span><span class="n">showRangeImage</span> <span class="p">(</span><span class="n">range_image</span><span class="p">);</span>
    <span class="p">}</span>
  <span class="p">}</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="explanation">
<h1>Explanation</h1>
<p>In the beginning we do command line parsing, read a point cloud from disc (or create it if not provided) and create a range image. All of these steps are already covered in the &#8216;How to create a range image from a point cloud&#8217; tutorial.</p>
<p>The interesting part begins here:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="n">viewer</span> <span class="p">(</span><span class="s">&quot;3D Viewer&quot;</span><span class="p">);</span>
<span class="n">viewer</span><span class="p">.</span><span class="n">setBackgroundColor</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span> <span class="mi">1</span><span class="p">,</span> <span class="mi">1</span><span class="p">);</span>
<span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointWithRange</span><span class="o">&gt;</span> <span class="n">range_image_color_handler</span> <span class="p">(</span><span class="n">range_image_ptr</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
<span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">range_image_ptr</span><span class="p">,</span> <span class="n">range_image_color_handler</span><span class="p">,</span> <span class="s">&quot;range image&quot;</span><span class="p">);</span>
<span class="n">viewer</span><span class="p">.</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span> <span class="mi">1</span><span class="p">,</span> <span class="s">&quot;range image&quot;</span><span class="p">);</span>
<span class="c1">//viewer.addCoordinateSystem (1.0f);</span>
<span class="c1">//pcl::visualization::PointCloudColorHandlerCustom&lt;PointType&gt; point_cloud_color_handler (point_cloud_ptr, 150, 150, 150);</span>
<span class="c1">//viewer.addPointCloud (point_cloud_ptr, point_cloud_color_handler, &quot;original point cloud&quot;);</span>
<span class="n">viewer</span><span class="p">.</span><span class="n">initCameraParameters</span> <span class="p">();</span>
<span class="n">setViewerPose</span><span class="p">(</span><span class="n">viewer</span><span class="p">,</span> <span class="n">range_image</span><span class="p">.</span><span class="n">getTransformationToWorldSystem</span> <span class="p">());</span>
<span class="p">...</span>
</pre></div>
</div>
<p>This creates the 3D viewer object, sets the background color to white, adds the range image (as a point cloud) with color black and point size 1 and sets the viewing position in the viewer to the sensor position from the range image (using a function defined above the main). The commented part can be used to add a coordinate system and also visualize the original point cloud.</p>
<p>The next part visualizes the range image in 2D, using color coding for the range values:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">RangeImageVisualizer</span> <span class="n">range_image_widget</span> <span class="p">(</span><span class="s">&quot;Range image&quot;</span><span class="p">);</span>
<span class="n">range_image_widget</span><span class="p">.</span><span class="n">setRangeImage</span> <span class="p">(</span><span class="n">range_image</span><span class="p">);</span>
<span class="p">...</span>
</pre></div>
</div>
<p>Now we can start the main loop to keep the visualization alive, until the viewer window is closed:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">viewer</span><span class="p">.</span><span class="n">wasStopped</span> <span class="p">())</span>
<span class="p">{</span>
  <span class="n">range_image_widget</span><span class="p">.</span><span class="n">spinOnce</span> <span class="p">();</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">spinOnce</span> <span class="p">();</span>
  <span class="n">pcl_sleep</span> <span class="p">(</span><span class="mf">0.01</span><span class="p">);</span>
<span class="p">...</span>
</pre></div>
</div>
<p>range_image_widget.spinOnce() handles the current events of the RangeImageVisualizer and viewer.spinOnce() does the same for the 3D viewer.</p>
<p>Additionally there is the possibility to always update the 2D range image to correspond to the current percpective in the viewer window, which is activated using the command line parameter -l:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">live_update</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">scene_sensor_pose</span> <span class="o">=</span> <span class="n">viewer</span><span class="p">.</span><span class="n">getViewerPose</span><span class="p">();</span>
    <span class="n">range_image</span><span class="p">.</span><span class="n">createFromPointCloud</span> <span class="p">(</span><span class="n">point_cloud</span><span class="p">,</span> <span class="n">angular_resolution</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">deg2rad</span> <span class="p">(</span><span class="mf">360.0f</span><span class="p">),</span> <span class="n">pcl</span><span class="o">::</span><span class="n">deg2rad</span> <span class="p">(</span><span class="mf">180.0f</span><span class="p">),</span>
                                      <span class="n">scene_sensor_pose</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">RangeImage</span><span class="o">::</span><span class="n">LASER_FRAME</span><span class="p">,</span> <span class="n">noise_level</span><span class="p">,</span> <span class="n">min_range</span><span class="p">,</span> <span class="n">border_size</span><span class="p">);</span>
    <span class="n">range_image_widget</span><span class="p">.</span><span class="n">setRangeImage</span> <span class="p">(</span><span class="n">range_image</span><span class="p">);</span>
  <span class="p">}</span>
<span class="p">}</span>
<span class="p">...</span>
</pre></div>
</div>
<p>Here we first get the current viewing position from the viewer window and then recreate the range image.</p>
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

<span class="nb">project</span><span class="p">(</span><span class="s">range_image_visualization</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.3</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">range_image_visualization</span> <span class="s">range_image_visualization.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">range_image_visualization</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it. Simply do:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./range_image_visualization
</pre></div>
</div>
<p>This will use an autogenerated point cloud of a rectangle floating in space. It opens two windows, one a 3D viewer of the point cloud and one a visual version of the range image, where the range values are color coded.</p>
<p>You can also try it with a point cloud file from your hard drive:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./range_image_visualization &lt;point_cloud.pcd&gt;
</pre></div>
</div>
<p>You can also try the -l parameter to update the range image according to the current perspective in the viewer:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./range_image_visualization -l &lt;point_cloud.pcd&gt;
</pre></div>
</div>
<p>The output should look similar to this:</p>
<a class="reference internal image-reference" href="_images/range_image_visualization.png"><img alt="_images/range_image_visualization.png" src="_images/range_image_visualization.png" style="width: 500px;" /></a>
<p>Unseen areas (range -INFINITY) are shown in pale green and far ranges (range INFINITY - if available in the scan) in pale blue.</p>
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