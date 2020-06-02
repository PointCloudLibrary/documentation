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
    
    <title>Interactive Iterative Closest Point</title>
    
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
            
  <div class="section" id="interactive-iterative-closest-point">
<span id="interactive-icp"></span><h1>Interactive Iterative Closest Point</h1>
<p>This tutorial will teach you how to write an interactive ICP viewer. The program will
load a point cloud and apply a rigid transformation on it. After that the ICP algorithm will
align the transformed point cloud with the original. Each time the user presses &#8220;space&#8221;
an ICP iteration is done and the viewer is refreshed.</p>
</div>
<div class="section" id="creating-a-mesh-with-blender">
<h1>Creating a mesh with Blender</h1>
<p>You can easily create a sample point cloud with Blender.
Install and open Blender then delete the cube in the scene by pressing &#8220;Del&#8221; key :</p>
<a class="reference internal image-reference" href="_images/del_cube.png"><img alt="_images/del_cube.png" src="_images/del_cube.png" style="height: 285px;" /></a>
<p>Add a monkey mesh in the scene :</p>
<a class="reference internal image-reference" href="_images/add_monkey.png"><img alt="_images/add_monkey.png" src="_images/add_monkey.png" style="height: 328px;" /></a>
<p>Subdivide the original mesh to make it more dense :</p>
<a class="reference internal image-reference" href="_images/add_sub.png"><img alt="_images/add_sub.png" src="_images/add_sub.png" style="height: 500px;" /></a>
<p>Configure the subdivision to 2 or 3 for example : dont forget to apply the modifier</p>
<a class="reference internal image-reference" href="_images/sub2.png"><img alt="_images/sub2.png" src="_images/sub2.png" style="height: 203px;" /></a>
<p>Export the mesh into a PLY file :</p>
<a class="reference internal image-reference" href="_images/export.png"><img alt="_images/export.png" src="_images/export.png" style="height: 481px;" /></a>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>First, create a file, let&#8217;s say, <tt class="docutils literal"><span class="pre">interactive_icp.cpp</span></tt> in your favorite
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
167
168
169
170
171
172
173
174
175</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;iostream&gt;</span>
<span class="cp">#include &lt;string&gt;</span>

<span class="cp">#include &lt;pcl/io/ply_io.h&gt;</span>
<span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/registration/icp.h&gt;</span>
<span class="cp">#include &lt;pcl/visualization/pcl_visualizer.h&gt;</span>

<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">PointT</span><span class="p">;</span>
<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">PointCloudT</span><span class="p">;</span>

<span class="kt">bool</span> <span class="n">next_iteration</span> <span class="o">=</span> <span class="nb">false</span><span class="p">;</span>

<span class="kt">void</span> <span class="nf">printMatix4f</span><span class="p">(</span><span class="k">const</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span> <span class="o">&amp;</span> <span class="n">matrix</span><span class="p">)</span> <span class="p">{</span>

	<span class="n">printf</span> <span class="p">(</span><span class="s">&quot;Rotation matrix :</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
	<span class="n">printf</span> <span class="p">(</span><span class="s">&quot;    | %6.3f %6.3f %6.3f | </span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">matrix</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">0</span><span class="p">),</span> <span class="n">matrix</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">1</span><span class="p">),</span> <span class="n">matrix</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">2</span><span class="p">));</span>
	<span class="n">printf</span> <span class="p">(</span><span class="s">&quot;R = | %6.3f %6.3f %6.3f | </span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">matrix</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span><span class="mi">0</span><span class="p">),</span> <span class="n">matrix</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span><span class="mi">1</span><span class="p">),</span> <span class="n">matrix</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span><span class="mi">2</span><span class="p">));</span>
	<span class="n">printf</span> <span class="p">(</span><span class="s">&quot;    | %6.3f %6.3f %6.3f | </span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">matrix</span> <span class="p">(</span><span class="mi">2</span><span class="p">,</span><span class="mi">0</span><span class="p">),</span> <span class="n">matrix</span> <span class="p">(</span><span class="mi">2</span><span class="p">,</span><span class="mi">1</span><span class="p">),</span> <span class="n">matrix</span> <span class="p">(</span><span class="mi">2</span><span class="p">,</span><span class="mi">2</span><span class="p">));</span>
	<span class="n">printf</span> <span class="p">(</span><span class="s">&quot;Translation vector :</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
	<span class="n">printf</span> <span class="p">(</span><span class="s">&quot;t = &lt; %6.3f, %6.3f, %6.3f &gt;</span><span class="se">\n\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">matrix</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">3</span><span class="p">),</span> <span class="n">matrix</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span><span class="mi">3</span><span class="p">),</span> <span class="n">matrix</span> <span class="p">(</span><span class="mi">2</span><span class="p">,</span><span class="mi">3</span><span class="p">));</span>
<span class="p">}</span>

<span class="kt">void</span>
<span class="nf">keyboardEventOccurred</span><span class="p">(</span><span class="k">const</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">KeyboardEvent</span><span class="o">&amp;</span> <span class="n">event</span><span class="p">,</span> <span class="kt">void</span><span class="o">*</span> <span class="n">nothing</span><span class="p">)</span>
<span class="p">{</span>
	<span class="k">if</span> <span class="p">(</span><span class="n">event</span><span class="p">.</span><span class="n">getKeySym</span><span class="p">()</span> <span class="o">==</span> <span class="s">&quot;space&quot;</span> <span class="o">&amp;&amp;</span> <span class="n">event</span><span class="p">.</span><span class="n">keyDown</span><span class="p">())</span>
		<span class="n">next_iteration</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
<span class="p">}</span>

<span class="kt">int</span>
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">*</span> <span class="n">argv</span><span class="p">[])</span>
<span class="p">{</span>
	<span class="c1">// The point clouds we will be using</span>
	<span class="n">PointCloudT</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">cloud_in</span> 	<span class="p">(</span><span class="k">new</span> <span class="n">PointCloudT</span><span class="p">);</span> <span class="c1">// Original point cloud</span>
	<span class="n">PointCloudT</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">cloud_tr</span>	<span class="p">(</span><span class="k">new</span> <span class="n">PointCloudT</span><span class="p">);</span> <span class="c1">// Transformed point cloud</span>
	<span class="n">PointCloudT</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">cloud_icp</span>	<span class="p">(</span><span class="k">new</span> <span class="n">PointCloudT</span><span class="p">);</span> <span class="c1">// ICP output point cloud</span>

	<span class="c1">// Checking program arguments</span>
	<span class="k">if</span> <span class="p">(</span><span class="n">argc</span> <span class="o">&lt;</span> <span class="mi">2</span><span class="p">)</span> <span class="p">{</span>
		<span class="n">printf</span> <span class="p">(</span><span class="s">&quot;Usage :</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
		<span class="n">printf</span> <span class="p">(</span><span class="s">&quot;</span><span class="se">\t\t</span><span class="s">%s file.ply number_of_ICP_iterations</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
		<span class="n">PCL_ERROR</span><span class="p">(</span><span class="s">&quot;Provide one ply file.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
		<span class="k">return</span> <span class="o">-</span><span class="mi">1</span><span class="p">;</span>
	<span class="p">}</span>

	<span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPLYFile</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">1</span><span class="p">],</span> <span class="o">*</span><span class="n">cloud_in</span><span class="p">)</span> <span class="o">&lt;</span> <span class="mi">0</span><span class="p">)</span>	<span class="p">{</span>
		<span class="n">PCL_ERROR</span><span class="p">(</span><span class="s">&quot;Error loading cloud %s.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">argv</span><span class="p">[</span><span class="mi">1</span><span class="p">]);</span>
		<span class="k">return</span> <span class="o">-</span><span class="mi">1</span><span class="p">;</span>
	<span class="p">}</span>

	<span class="kt">int</span> <span class="n">iterations</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
	<span class="c1">// If the user passed the number of iteration as an argument</span>
	<span class="k">if</span> <span class="p">(</span><span class="n">argc</span> <span class="o">&gt;</span> <span class="mi">2</span><span class="p">)</span> <span class="p">{</span>
		<span class="n">iterations</span> <span class="o">=</span> <span class="n">atoi</span><span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">2</span><span class="p">]);</span>
	<span class="p">}</span>

	<span class="k">if</span> <span class="p">(</span><span class="n">iterations</span> <span class="o">&lt;</span> <span class="mi">1</span><span class="p">)</span> <span class="p">{</span>
		<span class="n">PCL_ERROR</span><span class="p">(</span><span class="s">&quot;Number of initial iterations must be &gt;= 1</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
		<span class="k">return</span> <span class="o">-</span><span class="mi">1</span><span class="p">;</span>
	<span class="p">}</span>

	<span class="n">printf</span> <span class="p">(</span><span class="s">&quot;</span><span class="se">\n</span><span class="s">Loaded file %s with %d points successfully</span><span class="se">\n\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">argv</span><span class="p">[</span><span class="mi">1</span><span class="p">],</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span><span class="n">cloud_in</span><span class="o">-&gt;</span><span class="n">size</span><span class="p">());</span>

	<span class="c1">// Defining a rotation matrix and translation vector</span>
	<span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span> <span class="n">transformation_matrix</span> <span class="o">=</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span><span class="o">::</span><span class="n">Identity</span><span class="p">();</span>

	<span class="c1">// A rotation matrix (see https://en.wikipedia.org/wiki/Rotation_matrix)</span>
	<span class="kt">float</span> <span class="n">theta</span> <span class="o">=</span> <span class="n">M_PI</span><span class="o">/</span><span class="mi">8</span><span class="p">;</span> <span class="c1">// The angle of rotation in radians</span>
	<span class="n">transformation_matrix</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">0</span><span class="p">)</span> <span class="o">=</span> <span class="n">cos</span><span class="p">(</span><span class="n">theta</span><span class="p">);</span>
	<span class="n">transformation_matrix</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">1</span><span class="p">)</span> <span class="o">=</span> <span class="o">-</span><span class="n">sin</span><span class="p">(</span><span class="n">theta</span><span class="p">);</span>
	<span class="n">transformation_matrix</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span><span class="mi">0</span><span class="p">)</span> <span class="o">=</span> <span class="n">sin</span><span class="p">(</span><span class="n">theta</span><span class="p">);</span>
	<span class="n">transformation_matrix</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span><span class="mi">1</span><span class="p">)</span> <span class="o">=</span> <span class="n">cos</span><span class="p">(</span><span class="n">theta</span><span class="p">);</span>

	<span class="c1">// A translation on Z axis (0.4 meters)</span>
	<span class="n">transformation_matrix</span> <span class="p">(</span><span class="mi">2</span><span class="p">,</span><span class="mi">3</span><span class="p">)</span> <span class="o">=</span> <span class="mf">0.4</span><span class="p">;</span>

	<span class="c1">// Display in terminal the transformation matrix</span>
	<span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Applying this rigid transformation to: cloud_in -&gt; cloud_icp&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
	<span class="n">printMatix4f</span><span class="p">(</span><span class="n">transformation_matrix</span><span class="p">);</span>

	<span class="c1">// Executing the transformation</span>
	<span class="n">pcl</span><span class="o">::</span><span class="n">transformPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_in</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud_icp</span><span class="p">,</span> <span class="n">transformation_matrix</span><span class="p">);</span>
	<span class="o">*</span><span class="n">cloud_tr</span> <span class="o">=</span> <span class="o">*</span><span class="n">cloud_icp</span><span class="p">;</span> <span class="c1">// We backup cloud_icp into cloud_tr for later use</span>

	<span class="c1">// The Iterative Closest Point algorithm</span>
	<span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Initial iterations number is set to : &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">iterations</span><span class="p">;</span>
	<span class="n">pcl</span><span class="o">::</span><span class="n">IterativeClosestPoint</span><span class="o">&lt;</span><span class="n">PointT</span><span class="p">,</span> <span class="n">PointT</span><span class="o">&gt;</span> <span class="n">icp</span><span class="p">;</span>
	<span class="n">icp</span><span class="p">.</span><span class="n">setMaximumIterations</span><span class="p">(</span><span class="n">iterations</span><span class="p">);</span>
	<span class="n">icp</span><span class="p">.</span><span class="n">setInputSource</span><span class="p">(</span><span class="n">cloud_icp</span><span class="p">);</span>
	<span class="n">icp</span><span class="p">.</span><span class="n">setInputTarget</span><span class="p">(</span><span class="n">cloud_in</span><span class="p">);</span>
	<span class="n">icp</span><span class="p">.</span><span class="n">align</span><span class="p">(</span><span class="o">*</span><span class="n">cloud_icp</span><span class="p">);</span>
	<span class="n">icp</span><span class="p">.</span><span class="n">setMaximumIterations</span><span class="p">(</span><span class="mi">1</span><span class="p">);</span> <span class="c1">// For the next time we will call .align() function</span>

	<span class="k">if</span> <span class="p">(</span><span class="n">icp</span><span class="p">.</span><span class="n">hasConverged</span><span class="p">())</span> <span class="p">{</span>
		<span class="n">printf</span><span class="p">(</span><span class="s">&quot;</span><span class="se">\n</span><span class="s">ICP has converged, score is %+.0e</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">icp</span><span class="p">.</span><span class="n">getFitnessScore</span><span class="p">());</span>
		<span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;</span><span class="se">\n</span><span class="s">ICP transformation &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">iterations</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; : cloud_icp -&gt; cloud_in&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
		<span class="n">transformation_matrix</span> <span class="o">=</span> <span class="n">icp</span><span class="p">.</span><span class="n">getFinalTransformation</span><span class="p">();</span>
		<span class="n">printMatix4f</span><span class="p">(</span><span class="n">transformation_matrix</span><span class="p">);</span>
	<span class="p">}</span> <span class="k">else</span> <span class="p">{</span>
		<span class="n">PCL_ERROR</span> <span class="p">(</span><span class="s">&quot;</span><span class="se">\n</span><span class="s">ICP has not converged.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
		<span class="k">return</span> <span class="o">-</span><span class="mi">1</span><span class="p">;</span>
	<span class="p">}</span>

	<span class="c1">// Visualization</span>
	<span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="n">viewer</span> <span class="p">(</span><span class="s">&quot;ICP demo&quot;</span><span class="p">);</span>
	<span class="c1">// Create two verticaly separated viewports</span>
	<span class="kt">int</span> <span class="n">v1</span><span class="p">(</span><span class="mi">0</span><span class="p">);</span> <span class="kt">int</span> <span class="n">v2</span><span class="p">(</span><span class="mi">1</span><span class="p">);</span>
	<span class="n">viewer</span><span class="p">.</span><span class="n">createViewPort</span> <span class="p">(</span><span class="mf">0.0</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mf">0.5</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">,</span> <span class="n">v1</span><span class="p">);</span>
	<span class="n">viewer</span><span class="p">.</span><span class="n">createViewPort</span> <span class="p">(</span><span class="mf">0.5</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">,</span> <span class="n">v2</span><span class="p">);</span>

	<span class="c1">// The color we will be using</span>
	<span class="kt">float</span> <span class="n">bckgr_gray_level</span> <span class="o">=</span> <span class="mf">0.0</span><span class="p">;</span> <span class="c1">// Black</span>
	<span class="kt">float</span> <span class="n">txt_gray_lvl</span> <span class="o">=</span> <span class="mf">1.0</span><span class="o">-</span><span class="n">bckgr_gray_level</span><span class="p">;</span> 

	<span class="c1">// Original point cloud is white</span>
	<span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">cloud_in_color_h</span> <span class="p">(</span><span class="n">cloud_in</span><span class="p">,</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span><span class="mi">255</span><span class="o">*</span> <span class="n">txt_gray_lvl</span><span class="p">,</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span><span class="mi">255</span><span class="o">*</span> <span class="n">txt_gray_lvl</span><span class="p">,</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span><span class="mi">255</span><span class="o">*</span> <span class="n">txt_gray_lvl</span><span class="p">);</span>
	<span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">cloud_in</span><span class="p">,</span> <span class="n">cloud_in_color_h</span><span class="p">,</span> <span class="s">&quot;cloud_in_v1&quot;</span><span class="p">,</span> <span class="n">v1</span><span class="p">);</span>
	<span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">cloud_in</span><span class="p">,</span> <span class="n">cloud_in_color_h</span><span class="p">,</span> <span class="s">&quot;cloud_in_v2&quot;</span><span class="p">,</span> <span class="n">v2</span><span class="p">);</span>

	<span class="c1">// Transformed point cloud is green</span>
	<span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">cloud_tr_color_h</span> <span class="p">(</span><span class="n">cloud_tr</span><span class="p">,</span> <span class="mi">20</span><span class="p">,</span> <span class="mi">180</span><span class="p">,</span> <span class="mi">20</span><span class="p">);</span>
	<span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">cloud_tr</span><span class="p">,</span> <span class="n">cloud_tr_color_h</span><span class="p">,</span> <span class="s">&quot;cloud_tr_v1&quot;</span><span class="p">,</span> <span class="n">v1</span><span class="p">);</span>

	<span class="c1">// ICP aligned point cloud is red</span>
	<span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">cloud_icp_color_h</span> <span class="p">(</span><span class="n">cloud_icp</span><span class="p">,</span> <span class="mi">180</span><span class="p">,</span> <span class="mi">20</span><span class="p">,</span> <span class="mi">20</span><span class="p">);</span>
	<span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">cloud_icp</span><span class="p">,</span> <span class="n">cloud_icp_color_h</span><span class="p">,</span> <span class="s">&quot;cloud_icp_v2&quot;</span><span class="p">,</span> <span class="n">v2</span><span class="p">);</span>

	<span class="c1">// Adding text descriptions in each viewport</span>
	<span class="n">viewer</span><span class="p">.</span><span class="n">addText</span><span class="p">(</span><span class="s">&quot;White: Original point cloud</span><span class="se">\n</span><span class="s">Green: Matrix transformed point cloud&quot;</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="mi">15</span><span class="p">,</span> <span class="mi">16</span><span class="p">,</span> <span class="n">txt_gray_lvl</span><span class="p">,</span> <span class="n">txt_gray_lvl</span><span class="p">,</span> <span class="n">txt_gray_lvl</span><span class="p">,</span> <span class="s">&quot;icp_info_1&quot;</span><span class="p">,</span> <span class="n">v1</span><span class="p">);</span>
	<span class="n">viewer</span><span class="p">.</span><span class="n">addText</span><span class="p">(</span><span class="s">&quot;White: Original point cloud</span><span class="se">\n</span><span class="s">Red: ICP aligned point cloud&quot;</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="mi">15</span><span class="p">,</span> <span class="mi">16</span><span class="p">,</span> <span class="n">txt_gray_lvl</span><span class="p">,</span> <span class="n">txt_gray_lvl</span><span class="p">,</span> <span class="n">txt_gray_lvl</span><span class="p">,</span> <span class="s">&quot;icp_info_2&quot;</span><span class="p">,</span> <span class="n">v2</span><span class="p">);</span>

	<span class="n">std</span><span class="o">::</span><span class="n">stringstream</span> <span class="n">ss</span><span class="p">;</span> <span class="n">ss</span> <span class="o">&lt;&lt;</span> <span class="n">iterations</span><span class="p">;</span>
	<span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">iterations_cnt</span> <span class="o">=</span> <span class="s">&quot;ICP iterations = &quot;</span> <span class="o">+</span> <span class="n">ss</span><span class="p">.</span><span class="n">str</span><span class="p">();</span>
	<span class="n">viewer</span><span class="p">.</span><span class="n">addText</span><span class="p">(</span><span class="n">iterations_cnt</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="mi">60</span><span class="p">,</span> <span class="mi">16</span><span class="p">,</span> <span class="n">txt_gray_lvl</span><span class="p">,</span> <span class="n">txt_gray_lvl</span><span class="p">,</span> <span class="n">txt_gray_lvl</span><span class="p">,</span> <span class="s">&quot;iterations_cnt&quot;</span><span class="p">,</span> <span class="n">v2</span><span class="p">);</span>

	<span class="c1">// Set background color</span>
	<span class="n">viewer</span><span class="p">.</span><span class="n">setBackgroundColor</span><span class="p">(</span><span class="n">bckgr_gray_level</span><span class="p">,</span> <span class="n">bckgr_gray_level</span><span class="p">,</span> <span class="n">bckgr_gray_level</span><span class="p">,</span> <span class="n">v1</span><span class="p">);</span>
	<span class="n">viewer</span><span class="p">.</span><span class="n">setBackgroundColor</span><span class="p">(</span><span class="n">bckgr_gray_level</span><span class="p">,</span> <span class="n">bckgr_gray_level</span><span class="p">,</span> <span class="n">bckgr_gray_level</span><span class="p">,</span> <span class="n">v2</span><span class="p">);</span>

	<span class="c1">// Set camera position and orientation</span>
	<span class="n">viewer</span><span class="p">.</span><span class="n">setCameraPosition</span><span class="p">(</span><span class="o">-</span><span class="mf">3.68332</span><span class="p">,</span> <span class="mf">2.94092</span><span class="p">,</span> <span class="mf">5.71266</span><span class="p">,</span> <span class="mf">0.289847</span><span class="p">,</span> <span class="mf">0.921947</span><span class="p">,</span> <span class="o">-</span><span class="mf">0.256907</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
	<span class="n">viewer</span><span class="p">.</span><span class="n">setSize</span><span class="p">(</span><span class="mi">1280</span><span class="p">,</span> <span class="mi">1024</span><span class="p">);</span> <span class="c1">// Visualiser window size</span>

	<span class="c1">// Register keyboard callback :</span>
	<span class="n">viewer</span><span class="p">.</span><span class="n">registerKeyboardCallback</span><span class="p">(</span><span class="o">&amp;</span><span class="n">keyboardEventOccurred</span><span class="p">,</span> <span class="p">(</span><span class="kt">void</span><span class="o">*</span><span class="p">)</span> <span class="nb">NULL</span><span class="p">);</span>

	<span class="c1">// Display the visualiser</span>
	<span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">viewer</span><span class="p">.</span><span class="n">wasStopped</span> <span class="p">())</span> <span class="p">{</span>
		<span class="n">viewer</span><span class="p">.</span><span class="n">spinOnce</span> <span class="p">();</span>
		
		<span class="c1">// The user pressed &quot;space&quot; :</span>
		<span class="k">if</span> <span class="p">(</span><span class="n">next_iteration</span><span class="p">)</span> <span class="p">{</span>
			<span class="n">icp</span><span class="p">.</span><span class="n">align</span><span class="p">(</span><span class="o">*</span><span class="n">cloud_icp</span><span class="p">);</span>

			<span class="k">if</span> <span class="p">(</span><span class="n">icp</span><span class="p">.</span><span class="n">hasConverged</span><span class="p">())</span> <span class="p">{</span>
				<span class="n">printf</span><span class="p">(</span><span class="s">&quot;</span><span class="se">\033</span><span class="s">[11A&quot;</span><span class="p">);</span> <span class="c1">// Go up 11 lines in terminal output.</span>
				<span class="n">printf</span><span class="p">(</span><span class="s">&quot;</span><span class="se">\n</span><span class="s">ICP has converged, score is %+.0e</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">icp</span><span class="p">.</span><span class="n">getFitnessScore</span><span class="p">());</span>
				<span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;</span><span class="se">\n</span><span class="s">ICP transformation &quot;</span> <span class="o">&lt;&lt;</span> <span class="o">++</span><span class="n">iterations</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; : cloud_icp -&gt; cloud_in&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
				<span class="n">transformation_matrix</span> <span class="o">*=</span> <span class="n">icp</span><span class="p">.</span><span class="n">getFinalTransformation</span><span class="p">();</span>	<span class="c1">// This is not very accurate !</span>
				<span class="n">printMatix4f</span><span class="p">(</span><span class="n">transformation_matrix</span><span class="p">);</span>					<span class="c1">// Print the transformation between original pose and current pose</span>

				<span class="n">ss</span><span class="p">.</span><span class="n">str</span> <span class="p">(</span><span class="s">&quot;&quot;</span><span class="p">);</span> <span class="n">ss</span> <span class="o">&lt;&lt;</span> <span class="n">iterations</span><span class="p">;</span>
				<span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">iterations_cnt</span> <span class="o">=</span> <span class="s">&quot;ICP iterations = &quot;</span> <span class="o">+</span> <span class="n">ss</span><span class="p">.</span><span class="n">str</span><span class="p">();</span>
				<span class="n">viewer</span><span class="p">.</span><span class="n">updateText</span> <span class="p">(</span><span class="n">iterations_cnt</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="mi">60</span><span class="p">,</span> <span class="mi">16</span><span class="p">,</span> <span class="n">txt_gray_lvl</span><span class="p">,</span> <span class="n">txt_gray_lvl</span><span class="p">,</span> <span class="n">txt_gray_lvl</span><span class="p">,</span> <span class="s">&quot;iterations_cnt&quot;</span><span class="p">);</span>
				<span class="n">viewer</span><span class="p">.</span><span class="n">updatePointCloud</span> <span class="p">(</span><span class="n">cloud_icp</span><span class="p">,</span> <span class="n">cloud_icp_color_h</span><span class="p">,</span> <span class="s">&quot;cloud_icp_v2&quot;</span><span class="p">);</span>
			<span class="p">}</span> <span class="k">else</span> <span class="p">{</span>
				<span class="n">PCL_ERROR</span> <span class="p">(</span><span class="s">&quot;</span><span class="se">\n</span><span class="s">ICP has not converged.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
				<span class="k">return</span> <span class="o">-</span><span class="mi">1</span><span class="p">;</span>
			<span class="p">}</span>
		<span class="p">}</span>
		<span class="n">next_iteration</span> <span class="o">=</span> <span class="nb">false</span><span class="p">;</span>
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
<span class="cp">#include &lt;string&gt;</span>

<span class="cp">#include &lt;pcl/io/ply_io.h&gt;</span>
<span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/registration/icp.h&gt;</span>
<span class="cp">#include &lt;pcl/visualization/pcl_visualizer.h&gt;</span>
</pre></div>
</div>
<p>We include all the headers we will make use of.
<strong>#include &lt;pcl/registration/ia_ransac.h&gt;</strong> allows us to use <strong>pcl::transformPointCloud</strong> function.
<strong>#include &lt;pcl/console/parse.h&gt;&gt;</strong> allows us to use parse the arguments given to the program.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">PointT</span><span class="p">;</span>
<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">PointCloudT</span><span class="p">;</span>

<span class="kt">bool</span> <span class="n">next_iteration</span> <span class="o">=</span> <span class="nb">false</span><span class="p">;</span>
</pre></div>
</div>
<p>Two typedefs to simplify declarations and code reading.
The bool will help us know when the user asks for the next iteration of ICP</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">void</span> <span class="nf">printMatix4f</span><span class="p">(</span><span class="k">const</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span> <span class="o">&amp;</span> <span class="n">matrix</span><span class="p">)</span> <span class="p">{</span>

	<span class="n">printf</span> <span class="p">(</span><span class="s">&quot;Rotation matrix :</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
	<span class="n">printf</span> <span class="p">(</span><span class="s">&quot;    | %6.3f %6.3f %6.3f | </span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">matrix</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">0</span><span class="p">),</span> <span class="n">matrix</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">1</span><span class="p">),</span> <span class="n">matrix</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">2</span><span class="p">));</span>
	<span class="n">printf</span> <span class="p">(</span><span class="s">&quot;R = | %6.3f %6.3f %6.3f | </span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">matrix</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span><span class="mi">0</span><span class="p">),</span> <span class="n">matrix</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span><span class="mi">1</span><span class="p">),</span> <span class="n">matrix</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span><span class="mi">2</span><span class="p">));</span>
	<span class="n">printf</span> <span class="p">(</span><span class="s">&quot;    | %6.3f %6.3f %6.3f | </span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">matrix</span> <span class="p">(</span><span class="mi">2</span><span class="p">,</span><span class="mi">0</span><span class="p">),</span> <span class="n">matrix</span> <span class="p">(</span><span class="mi">2</span><span class="p">,</span><span class="mi">1</span><span class="p">),</span> <span class="n">matrix</span> <span class="p">(</span><span class="mi">2</span><span class="p">,</span><span class="mi">2</span><span class="p">));</span>
	<span class="n">printf</span> <span class="p">(</span><span class="s">&quot;Translation vector :</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
	<span class="n">printf</span> <span class="p">(</span><span class="s">&quot;t = &lt; %6.3f, %6.3f, %6.3f &gt;</span><span class="se">\n\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">matrix</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">3</span><span class="p">),</span> <span class="n">matrix</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span><span class="mi">3</span><span class="p">),</span> <span class="n">matrix</span> <span class="p">(</span><span class="mi">2</span><span class="p">,</span><span class="mi">3</span><span class="p">));</span>
<span class="p">}</span>
</pre></div>
</div>
<p>This functions takes the reference of a 4x4 matrix and prints the rigid transformation in an human
readable way.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">void</span>
<span class="nf">keyboardEventOccurred</span><span class="p">(</span><span class="k">const</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">KeyboardEvent</span><span class="o">&amp;</span> <span class="n">event</span><span class="p">,</span> <span class="kt">void</span><span class="o">*</span> <span class="n">nothing</span><span class="p">)</span>
<span class="p">{</span>
	<span class="k">if</span> <span class="p">(</span><span class="n">event</span><span class="p">.</span><span class="n">getKeySym</span><span class="p">()</span> <span class="o">==</span> <span class="s">&quot;space&quot;</span> <span class="o">&amp;&amp;</span> <span class="n">event</span><span class="p">.</span><span class="n">keyDown</span><span class="p">())</span>
		<span class="n">next_iteration</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
<span class="p">}</span>
</pre></div>
</div>
<p>This function is the callback for the viewer. This function will be called whenever a key is pressed
when the viewer window is on top. If &#8220;space&#8221; is hit; set the bool to true.</p>
<div class="highlight-cpp"><div class="highlight"><pre>	<span class="c1">// The point clouds we will be using</span>
	<span class="n">PointCloudT</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">cloud_in</span> 	<span class="p">(</span><span class="k">new</span> <span class="n">PointCloudT</span><span class="p">);</span> <span class="c1">// Original point cloud</span>
	<span class="n">PointCloudT</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">cloud_tr</span>	<span class="p">(</span><span class="k">new</span> <span class="n">PointCloudT</span><span class="p">);</span> <span class="c1">// Transformed point cloud</span>
	<span class="n">PointCloudT</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">cloud_icp</span>	<span class="p">(</span><span class="k">new</span> <span class="n">PointCloudT</span><span class="p">);</span> <span class="c1">// ICP output point cloud</span>
</pre></div>
</div>
<p>The 3 point clouds we will use to store the data.</p>
<div class="highlight-cpp"><div class="highlight"><pre>	<span class="c1">// Checking program arguments</span>
	<span class="k">if</span> <span class="p">(</span><span class="n">argc</span> <span class="o">&lt;</span> <span class="mi">2</span><span class="p">)</span> <span class="p">{</span>
		<span class="n">printf</span> <span class="p">(</span><span class="s">&quot;Usage :</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
		<span class="n">printf</span> <span class="p">(</span><span class="s">&quot;</span><span class="se">\t\t</span><span class="s">%s file.ply number_of_ICP_iterations</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
		<span class="n">PCL_ERROR</span><span class="p">(</span><span class="s">&quot;Provide one ply file.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
		<span class="k">return</span> <span class="o">-</span><span class="mi">1</span><span class="p">;</span>
	<span class="p">}</span>

	<span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPLYFile</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">1</span><span class="p">],</span> <span class="o">*</span><span class="n">cloud_in</span><span class="p">)</span> <span class="o">&lt;</span> <span class="mi">0</span><span class="p">)</span>	<span class="p">{</span>
		<span class="n">PCL_ERROR</span><span class="p">(</span><span class="s">&quot;Error loading cloud %s.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">argv</span><span class="p">[</span><span class="mi">1</span><span class="p">]);</span>
		<span class="k">return</span> <span class="o">-</span><span class="mi">1</span><span class="p">;</span>
	<span class="p">}</span>

	<span class="kt">int</span> <span class="n">iterations</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
	<span class="c1">// If the user passed the number of iteration as an argument</span>
	<span class="k">if</span> <span class="p">(</span><span class="n">argc</span> <span class="o">&gt;</span> <span class="mi">2</span><span class="p">)</span> <span class="p">{</span>
		<span class="n">iterations</span> <span class="o">=</span> <span class="n">atoi</span><span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">2</span><span class="p">]);</span>
	<span class="p">}</span>

	<span class="k">if</span> <span class="p">(</span><span class="n">iterations</span> <span class="o">&lt;</span> <span class="mi">1</span><span class="p">)</span> <span class="p">{</span>
		<span class="n">PCL_ERROR</span><span class="p">(</span><span class="s">&quot;Number of initial iterations must be &gt;= 1</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
		<span class="k">return</span> <span class="o">-</span><span class="mi">1</span><span class="p">;</span>
	<span class="p">}</span>

	<span class="n">printf</span> <span class="p">(</span><span class="s">&quot;</span><span class="se">\n</span><span class="s">Loaded file %s with %d points successfully</span><span class="se">\n\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">argv</span><span class="p">[</span><span class="mi">1</span><span class="p">],</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span><span class="n">cloud_in</span><span class="o">-&gt;</span><span class="n">size</span><span class="p">());</span>
</pre></div>
</div>
<p>We check the arguments of the program, try to load the PLY file and set
the number of initial ICP iterations.</p>
<div class="highlight-cpp"><div class="highlight"><pre>	<span class="c1">// Defining a rotation matrix and translation vector</span>
	<span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span> <span class="n">transformation_matrix</span> <span class="o">=</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span><span class="o">::</span><span class="n">Identity</span><span class="p">();</span>

	<span class="c1">// A rotation matrix (see https://en.wikipedia.org/wiki/Rotation_matrix)</span>
	<span class="kt">float</span> <span class="n">theta</span> <span class="o">=</span> <span class="n">M_PI</span><span class="o">/</span><span class="mi">8</span><span class="p">;</span> <span class="c1">// The angle of rotation in radians</span>
	<span class="n">transformation_matrix</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">0</span><span class="p">)</span> <span class="o">=</span> <span class="n">cos</span><span class="p">(</span><span class="n">theta</span><span class="p">);</span>
	<span class="n">transformation_matrix</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">1</span><span class="p">)</span> <span class="o">=</span> <span class="o">-</span><span class="n">sin</span><span class="p">(</span><span class="n">theta</span><span class="p">);</span>
	<span class="n">transformation_matrix</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span><span class="mi">0</span><span class="p">)</span> <span class="o">=</span> <span class="n">sin</span><span class="p">(</span><span class="n">theta</span><span class="p">);</span>
	<span class="n">transformation_matrix</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span><span class="mi">1</span><span class="p">)</span> <span class="o">=</span> <span class="n">cos</span><span class="p">(</span><span class="n">theta</span><span class="p">);</span>

	<span class="c1">// A translation on Z axis (0.4 meters)</span>
	<span class="n">transformation_matrix</span> <span class="p">(</span><span class="mi">2</span><span class="p">,</span><span class="mi">3</span><span class="p">)</span> <span class="o">=</span> <span class="mf">0.4</span><span class="p">;</span>

	<span class="c1">// Display in terminal the transformation matrix</span>
	<span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Applying this rigid transformation to: cloud_in -&gt; cloud_icp&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
	<span class="n">printMatix4f</span><span class="p">(</span><span class="n">transformation_matrix</span><span class="p">);</span>

	<span class="c1">// Executing the transformation</span>
	<span class="n">pcl</span><span class="o">::</span><span class="n">transformPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_in</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud_icp</span><span class="p">,</span> <span class="n">transformation_matrix</span><span class="p">);</span>
	<span class="o">*</span><span class="n">cloud_tr</span> <span class="o">=</span> <span class="o">*</span><span class="n">cloud_icp</span><span class="p">;</span> <span class="c1">// We backup cloud_icp into cloud_tr for later use</span>
</pre></div>
</div>
<p>We transform the original point cloud using a rigid matrix transformation.
See the related tutorial in PCL documentation for more information.
<strong>cloud_in</strong> contains the original point cloud.
<strong>cloud_tr</strong> and <strong>cloud_icp</strong> contains the translated/rotated point cloud.
<strong>cloud_tr</strong> is a backup we will use for display (green point cloud).</p>
<div class="highlight-cpp"><div class="highlight"><pre>	<span class="c1">// The Iterative Closest Point algorithm</span>
	<span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Initial iterations number is set to : &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">iterations</span><span class="p">;</span>
	<span class="n">pcl</span><span class="o">::</span><span class="n">IterativeClosestPoint</span><span class="o">&lt;</span><span class="n">PointT</span><span class="p">,</span> <span class="n">PointT</span><span class="o">&gt;</span> <span class="n">icp</span><span class="p">;</span>
	<span class="n">icp</span><span class="p">.</span><span class="n">setMaximumIterations</span><span class="p">(</span><span class="n">iterations</span><span class="p">);</span>
	<span class="n">icp</span><span class="p">.</span><span class="n">setInputSource</span><span class="p">(</span><span class="n">cloud_icp</span><span class="p">);</span>
	<span class="n">icp</span><span class="p">.</span><span class="n">setInputTarget</span><span class="p">(</span><span class="n">cloud_in</span><span class="p">);</span>
	<span class="n">icp</span><span class="p">.</span><span class="n">align</span><span class="p">(</span><span class="o">*</span><span class="n">cloud_icp</span><span class="p">);</span>
	<span class="n">icp</span><span class="p">.</span><span class="n">setMaximumIterations</span><span class="p">(</span><span class="mi">1</span><span class="p">);</span> <span class="c1">// For the next time we will call .align() function</span>
</pre></div>
</div>
<p>This is the creation of the ICP object. We set the parameters of the ICP algorithm.
<strong>setMaximumIterations(iterations)</strong> sets the number of initial iterations to do (1
is the default value). We then transform the point cloud into <strong>cloud_icp</strong>.
After the first alignment we set ICP max iterations to 1 for all the next times this
ICP object will be used (when the user presses &#8220;space&#8221;).</p>
<div class="highlight-cpp"><div class="highlight"><pre>	<span class="k">if</span> <span class="p">(</span><span class="n">icp</span><span class="p">.</span><span class="n">hasConverged</span><span class="p">())</span> <span class="p">{</span>
		<span class="n">printf</span><span class="p">(</span><span class="s">&quot;</span><span class="se">\n</span><span class="s">ICP has converged, score is %+.0e</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">icp</span><span class="p">.</span><span class="n">getFitnessScore</span><span class="p">());</span>
		<span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;</span><span class="se">\n</span><span class="s">ICP transformation &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">iterations</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; : cloud_icp -&gt; cloud_in&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
		<span class="n">transformation_matrix</span> <span class="o">=</span> <span class="n">icp</span><span class="p">.</span><span class="n">getFinalTransformation</span><span class="p">();</span>
		<span class="n">printMatix4f</span><span class="p">(</span><span class="n">transformation_matrix</span><span class="p">);</span>
	<span class="p">}</span> <span class="k">else</span> <span class="p">{</span>
		<span class="n">PCL_ERROR</span> <span class="p">(</span><span class="s">&quot;</span><span class="se">\n</span><span class="s">ICP has not converged.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
		<span class="k">return</span> <span class="o">-</span><span class="mi">1</span><span class="p">;</span>
	<span class="p">}</span>
</pre></div>
</div>
<p>Check if the ICP algorithm converged; otherwise exit the program.
In case of success we store the transformation matrix in a 4x4 matrix and
then print the rigid matrix transformation. The reason why we store this
matrix is explained later.</p>
<div class="highlight-cpp"><div class="highlight"><pre>	<span class="c1">// Visualization</span>
	<span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="n">viewer</span> <span class="p">(</span><span class="s">&quot;ICP demo&quot;</span><span class="p">);</span>
	<span class="c1">// Create two verticaly separated viewports</span>
	<span class="kt">int</span> <span class="nf">v1</span><span class="p">(</span><span class="mi">0</span><span class="p">);</span> <span class="kt">int</span> <span class="nf">v2</span><span class="p">(</span><span class="mi">1</span><span class="p">);</span>
	<span class="n">viewer</span><span class="p">.</span><span class="n">createViewPort</span> <span class="p">(</span><span class="mf">0.0</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mf">0.5</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">,</span> <span class="n">v1</span><span class="p">);</span>
	<span class="n">viewer</span><span class="p">.</span><span class="n">createViewPort</span> <span class="p">(</span><span class="mf">0.5</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">,</span> <span class="n">v2</span><span class="p">);</span>

	<span class="c1">// The color we will be using</span>
	<span class="kt">float</span> <span class="n">bckgr_gray_level</span> <span class="o">=</span> <span class="mf">0.0</span><span class="p">;</span> <span class="c1">// Black</span>
	<span class="kt">float</span> <span class="n">txt_gray_lvl</span> <span class="o">=</span> <span class="mf">1.0</span><span class="o">-</span><span class="n">bckgr_gray_level</span><span class="p">;</span> 
</pre></div>
</div>
<p>For the visualization we create two viewports in the visualizer vertically
separated. <strong>bckgr_gray_level</strong> and <strong>txt_gray_lvl</strong> are variables to easily
switch from white background &amp; black text/point cloud to black background &amp;
white text/point cloud.</p>
<div class="highlight-cpp"><div class="highlight"><pre>	<span class="c1">// Original point cloud is white</span>
	<span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">cloud_in_color_h</span> <span class="p">(</span><span class="n">cloud_in</span><span class="p">,</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span><span class="mi">255</span><span class="o">*</span> <span class="n">txt_gray_lvl</span><span class="p">,</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span><span class="mi">255</span><span class="o">*</span> <span class="n">txt_gray_lvl</span><span class="p">,</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span><span class="mi">255</span><span class="o">*</span> <span class="n">txt_gray_lvl</span><span class="p">);</span>
	<span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">cloud_in</span><span class="p">,</span> <span class="n">cloud_in_color_h</span><span class="p">,</span> <span class="s">&quot;cloud_in_v1&quot;</span><span class="p">,</span> <span class="n">v1</span><span class="p">);</span>
	<span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">cloud_in</span><span class="p">,</span> <span class="n">cloud_in_color_h</span><span class="p">,</span> <span class="s">&quot;cloud_in_v2&quot;</span><span class="p">,</span> <span class="n">v2</span><span class="p">);</span>

	<span class="c1">// Transformed point cloud is green</span>
	<span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">cloud_tr_color_h</span> <span class="p">(</span><span class="n">cloud_tr</span><span class="p">,</span> <span class="mi">20</span><span class="p">,</span> <span class="mi">180</span><span class="p">,</span> <span class="mi">20</span><span class="p">);</span>
	<span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">cloud_tr</span><span class="p">,</span> <span class="n">cloud_tr_color_h</span><span class="p">,</span> <span class="s">&quot;cloud_tr_v1&quot;</span><span class="p">,</span> <span class="n">v1</span><span class="p">);</span>

	<span class="c1">// ICP aligned point cloud is red</span>
	<span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">cloud_icp_color_h</span> <span class="p">(</span><span class="n">cloud_icp</span><span class="p">,</span> <span class="mi">180</span><span class="p">,</span> <span class="mi">20</span><span class="p">,</span> <span class="mi">20</span><span class="p">);</span>
	<span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">cloud_icp</span><span class="p">,</span> <span class="n">cloud_icp_color_h</span><span class="p">,</span> <span class="s">&quot;cloud_icp_v2&quot;</span><span class="p">,</span> <span class="n">v2</span><span class="p">);</span>
</pre></div>
</div>
<p>We add the original point cloud in the 2 viewports and display it the same color
as <strong>txt_gray_lvl</strong>. We add the point cloud we transformed using the matrix in the left
viewport in green and the point cloud aligned with ICP in red (right viewport).</p>
<div class="highlight-cpp"><div class="highlight"><pre>	<span class="c1">// Adding text descriptions in each viewport</span>
	<span class="n">viewer</span><span class="p">.</span><span class="n">addText</span><span class="p">(</span><span class="s">&quot;White: Original point cloud</span><span class="se">\n</span><span class="s">Green: Matrix transformed point cloud&quot;</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="mi">15</span><span class="p">,</span> <span class="mi">16</span><span class="p">,</span> <span class="n">txt_gray_lvl</span><span class="p">,</span> <span class="n">txt_gray_lvl</span><span class="p">,</span> <span class="n">txt_gray_lvl</span><span class="p">,</span> <span class="s">&quot;icp_info_1&quot;</span><span class="p">,</span> <span class="n">v1</span><span class="p">);</span>
	<span class="n">viewer</span><span class="p">.</span><span class="n">addText</span><span class="p">(</span><span class="s">&quot;White: Original point cloud</span><span class="se">\n</span><span class="s">Red: ICP aligned point cloud&quot;</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="mi">15</span><span class="p">,</span> <span class="mi">16</span><span class="p">,</span> <span class="n">txt_gray_lvl</span><span class="p">,</span> <span class="n">txt_gray_lvl</span><span class="p">,</span> <span class="n">txt_gray_lvl</span><span class="p">,</span> <span class="s">&quot;icp_info_2&quot;</span><span class="p">,</span> <span class="n">v2</span><span class="p">);</span>

	<span class="n">std</span><span class="o">::</span><span class="n">stringstream</span> <span class="n">ss</span><span class="p">;</span> <span class="n">ss</span> <span class="o">&lt;&lt;</span> <span class="n">iterations</span><span class="p">;</span>
	<span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">iterations_cnt</span> <span class="o">=</span> <span class="s">&quot;ICP iterations = &quot;</span> <span class="o">+</span> <span class="n">ss</span><span class="p">.</span><span class="n">str</span><span class="p">();</span>
	<span class="n">viewer</span><span class="p">.</span><span class="n">addText</span><span class="p">(</span><span class="n">iterations_cnt</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="mi">60</span><span class="p">,</span> <span class="mi">16</span><span class="p">,</span> <span class="n">txt_gray_lvl</span><span class="p">,</span> <span class="n">txt_gray_lvl</span><span class="p">,</span> <span class="n">txt_gray_lvl</span><span class="p">,</span> <span class="s">&quot;iterations_cnt&quot;</span><span class="p">,</span> <span class="n">v2</span><span class="p">);</span>
</pre></div>
</div>
<p>We add descriptions for the point clouds in each viewport so the user knows what is what.
The string stream ss is needed to transform the integer <strong>iterations</strong> into a string.</p>
<div class="highlight-cpp"><div class="highlight"><pre>	<span class="c1">// Set background color</span>
	<span class="n">viewer</span><span class="p">.</span><span class="n">setBackgroundColor</span><span class="p">(</span><span class="n">bckgr_gray_level</span><span class="p">,</span> <span class="n">bckgr_gray_level</span><span class="p">,</span> <span class="n">bckgr_gray_level</span><span class="p">,</span> <span class="n">v1</span><span class="p">);</span>
	<span class="n">viewer</span><span class="p">.</span><span class="n">setBackgroundColor</span><span class="p">(</span><span class="n">bckgr_gray_level</span><span class="p">,</span> <span class="n">bckgr_gray_level</span><span class="p">,</span> <span class="n">bckgr_gray_level</span><span class="p">,</span> <span class="n">v2</span><span class="p">);</span>

	<span class="c1">// Set camera position and orientation</span>
	<span class="n">viewer</span><span class="p">.</span><span class="n">setCameraPosition</span><span class="p">(</span><span class="o">-</span><span class="mf">3.68332</span><span class="p">,</span> <span class="mf">2.94092</span><span class="p">,</span> <span class="mf">5.71266</span><span class="p">,</span> <span class="mf">0.289847</span><span class="p">,</span> <span class="mf">0.921947</span><span class="p">,</span> <span class="o">-</span><span class="mf">0.256907</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
	<span class="n">viewer</span><span class="p">.</span><span class="n">setSize</span><span class="p">(</span><span class="mi">1280</span><span class="p">,</span> <span class="mi">1024</span><span class="p">);</span> <span class="c1">// Visualiser window size</span>

	<span class="c1">// Register keyboard callback :</span>
	<span class="n">viewer</span><span class="p">.</span><span class="n">registerKeyboardCallback</span><span class="p">(</span><span class="o">&amp;</span><span class="n">keyboardEventOccurred</span><span class="p">,</span> <span class="p">(</span><span class="kt">void</span><span class="o">*</span><span class="p">)</span> <span class="nb">NULL</span><span class="p">);</span>
</pre></div>
</div>
<p>We set the two viewports background color according to <strong>bckgr_gray_level</strong>.
To get the camera parameters I simply pressed &#8220;C&#8221; in the viewer. Then I copied the
parameters into this function to save the camera position / orientation / focal point.
The function <strong>registerKeyboardCallback</strong> allows us to call a function whenever the
users pressed a keyboard key when viewer windows is on top.</p>
<div class="highlight-cpp"><div class="highlight"><pre>	<span class="c1">// Display the visualiser</span>
	<span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">viewer</span><span class="p">.</span><span class="n">wasStopped</span> <span class="p">())</span> <span class="p">{</span>
		<span class="n">viewer</span><span class="p">.</span><span class="n">spinOnce</span> <span class="p">();</span>
</pre></div>
</div>
<p>This is the normal behaviour if no key is pressed. The viewer waits to exit.</p>
<div class="highlight-cpp"><div class="highlight"><pre>		<span class="c1">// The user pressed &quot;space&quot; :</span>
		<span class="k">if</span> <span class="p">(</span><span class="n">next_iteration</span><span class="p">)</span> <span class="p">{</span>
			<span class="n">icp</span><span class="p">.</span><span class="n">align</span><span class="p">(</span><span class="o">*</span><span class="n">cloud_icp</span><span class="p">);</span>
</pre></div>
</div>
<p>If the user press any key of the keyboard, the function <strong>keyboardEventOccurred</strong> is called;
this function checks if the key is &#8220;space&#8221; or not. If yes the global bool <strong>next_iteration</strong>
is set to true, allowing the viewer loop to enter the next part of the code: the ICP object
is called to align the meshes. Remember we already configured this object input/output clouds
and we set max iterations to 1 in lines 90-93.</p>
<div class="highlight-cpp"><div class="highlight"><pre>			<span class="k">if</span> <span class="p">(</span><span class="n">icp</span><span class="p">.</span><span class="n">hasConverged</span><span class="p">())</span> <span class="p">{</span>
				<span class="n">printf</span><span class="p">(</span><span class="s">&quot;</span><span class="se">\033</span><span class="s">[11A&quot;</span><span class="p">);</span> <span class="c1">// Go up 11 lines in terminal output.</span>
				<span class="n">printf</span><span class="p">(</span><span class="s">&quot;</span><span class="se">\n</span><span class="s">ICP has converged, score is %+.0e</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">icp</span><span class="p">.</span><span class="n">getFitnessScore</span><span class="p">());</span>
				<span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;</span><span class="se">\n</span><span class="s">ICP transformation &quot;</span> <span class="o">&lt;&lt;</span> <span class="o">++</span><span class="n">iterations</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; : cloud_icp -&gt; cloud_in&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
				<span class="n">transformation_matrix</span> <span class="o">*=</span> <span class="n">icp</span><span class="p">.</span><span class="n">getFinalTransformation</span><span class="p">();</span>	<span class="c1">// This is not very accurate !</span>
				<span class="n">printMatix4f</span><span class="p">(</span><span class="n">transformation_matrix</span><span class="p">);</span>					<span class="c1">// Print the transformation between original pose and current pose</span>

				<span class="n">ss</span><span class="p">.</span><span class="n">str</span> <span class="p">(</span><span class="s">&quot;&quot;</span><span class="p">);</span> <span class="n">ss</span> <span class="o">&lt;&lt;</span> <span class="n">iterations</span><span class="p">;</span>
				<span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">iterations_cnt</span> <span class="o">=</span> <span class="s">&quot;ICP iterations = &quot;</span> <span class="o">+</span> <span class="n">ss</span><span class="p">.</span><span class="n">str</span><span class="p">();</span>
				<span class="n">viewer</span><span class="p">.</span><span class="n">updateText</span> <span class="p">(</span><span class="n">iterations_cnt</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="mi">60</span><span class="p">,</span> <span class="mi">16</span><span class="p">,</span> <span class="n">txt_gray_lvl</span><span class="p">,</span> <span class="n">txt_gray_lvl</span><span class="p">,</span> <span class="n">txt_gray_lvl</span><span class="p">,</span> <span class="s">&quot;iterations_cnt&quot;</span><span class="p">);</span>
				<span class="n">viewer</span><span class="p">.</span><span class="n">updatePointCloud</span> <span class="p">(</span><span class="n">cloud_icp</span><span class="p">,</span> <span class="n">cloud_icp_color_h</span><span class="p">,</span> <span class="s">&quot;cloud_icp_v2&quot;</span><span class="p">);</span>
			<span class="p">}</span> <span class="k">else</span> <span class="p">{</span>
				<span class="n">PCL_ERROR</span> <span class="p">(</span><span class="s">&quot;</span><span class="se">\n</span><span class="s">ICP has not converged.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
				<span class="k">return</span> <span class="o">-</span><span class="mi">1</span><span class="p">;</span>
			<span class="p">}</span>
</pre></div>
</div>
<p>As before we check if ICP as converged, if not we exit the program.
<strong>printf(&#8220;033[11A&#8221;);</strong> is a little trick to go up 11 lines in the terminal to write
over the last matrix displayed. In short it allows to replace text instead of writing
new lines; making the ouptut more readable.
We increment <strong>iterations</strong> to update the text value in the visualizer.</p>
<p>Now we want to display the rigid transformation from the original transformed point cloud to
the current alignment made by ICP. The function <strong>getFinalTransformation()</strong> returns the rigid
matrix transformation done during the iterations (here: 1 iteration). This means that if you have already
done 10 iterations this function returns the matrix to transform the point cloud from the iteration 10 to 11.</p>
<p>This is not what we want. If we multiply the last matrix with the new one the result is the transformation matrix from
the start to the current iteration. This is basically how it works</p>
<div class="highlight-python"><div class="highlight"><pre>matrix[ICP 0-&gt;1]*matrix[ICP 1-&gt;2]*matrix[ICP 2-&gt;3] = matrix[ICP 0-&gt;3]
</pre></div>
</div>
<p>While this is mathematically true, you will easilly notice that this is not true in this program due to roundings.
This is why I introduced the initial ICP iteration parameters. Try to launch the program with 20 initial iterations
and save the matrix in a text file. Launch the same program with 1 initial iteration and press space till you go to 20
iterations. You will a notice a slight difference. The matrix with 20 initial iterations is much more accurate than the
one multiplied 19 times.</p>
<div class="highlight-cpp"><div class="highlight"><pre>		<span class="n">next_iteration</span> <span class="o">=</span> <span class="nb">false</span><span class="p">;</span>
	<span class="p">}</span>
 <span class="k">return</span> <span class="mi">0</span><span class="p">;</span>
<span class="p">}</span>
</pre></div>
</div>
<p>We set the bool to false and the rest is the ending of the program.</p>
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

<span class="nb">project</span><span class="p">(</span><span class="s">pcl-interactive_icp</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.5</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">interactive_icp</span> <span class="s">interactive_icp.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">interactive_icp</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it. Simply do:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./interactive_icp monkey.ply 1
</pre></div>
</div>
<p>Remember that the matrix displayed is not very accurate if you do a lot of iterations
by pressing &#8220;space&#8221;.</p>
<p>You will see something similar to this:</p>
<div class="highlight-python"><div class="highlight"><pre>[pcl::PLYReader] monkey.ply:24: property &#39;float32 focal&#39; of element &#39;camera&#39; is not handled
[pcl::PLYReader] monkey.ply:25: property &#39;float32 scalex&#39; of element &#39;camera&#39; is not handled
[pcl::PLYReader] monkey.ply:26: property &#39;float32 scaley&#39; of element &#39;camera&#39; is not handled
[pcl::PLYReader] monkey.ply:27: property &#39;float32 centerx&#39; of element &#39;camera&#39; is not handled
[pcl::PLYReader] monkey.ply:28: property &#39;float32 centery&#39; of element &#39;camera&#39; is not handled
[pcl::PLYReader] monkey.ply:31: property &#39;float32 k1&#39; of element &#39;camera&#39; is not handled
[pcl::PLYReader] monkey.ply:32: property &#39;float32 k2&#39; of element &#39;camera&#39; is not handled

Loaded file monkey.ply with 125952 points successfully

Applying this rigid transformation to: cloud_in -&gt; cloud_icp
Rotation matrix :
    |  0.924 -0.383  0.000 |
R = |  0.383  0.924  0.000 |
    |  0.000  0.000  1.000 |
Translation vector :
t = &lt;  0.000,  0.000,  0.400 &gt;

Initial iterations number is set to : 1
ICP has converged, score is +7e-03

ICP transformation 1 : cloud_icp -&gt; cloud_in
Rotation matrix :
    |  0.996  0.070 -0.046 |
R = | -0.072  0.997 -0.039 |
    |  0.043  0.042  0.998 |
Translation vector :
t = &lt;  0.038,  0.058, -0.211 &gt;
</pre></div>
</div>
<p>If ICP did a perfect job the two matrices should have exactly the same values and
the matrix found by ICP should have inverted signs outside the diagonal. For example</p>
<div class="highlight-python"><div class="highlight"><pre>    |  0.924 -0.383  0.000 |
R = |  0.383  0.924  0.000 |
    |  0.000  0.000  1.000 |
Translation vector :
t = &lt;  0.000,  0.000,  0.400 &gt;

    |  0.924  0.383  0.000 |
R = | -0.383  0.924  0.000 |
    |  0.000  0.000  1.000 |
Translation vector :
t = &lt;  0.000,  0.000, -0.400 &gt;
</pre></div>
</div>
<a class="reference internal image-reference" href="_images/icp-1.png"><img alt="_images/icp-1.png" src="_images/icp-1.png" style="height: 605px;" /></a>
<p>After 25 iterations the models fits perfectly the original cloud. Remember that this is an easy job for ICP because
you are asking to align two identical point clouds !</p>
<a class="reference internal image-reference" href="_images/animation.gif"><img alt="_images/animation.gif" src="_images/animation.gif" style="height: 630px;" /></a>
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