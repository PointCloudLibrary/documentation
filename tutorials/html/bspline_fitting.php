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
    
    <title>Fitting trimmed B-splines to unordered point clouds</title>
    
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
            
  <div class="section" id="fitting-trimmed-b-splines-to-unordered-point-clouds">
<span id="bspline-fitting"></span><h1>Fitting trimmed B-splines to unordered point clouds</h1>
<p>This tutorial explains how to run a B-spline fitting algorithm on a
point-cloud, to obtain a smooth, parametric surface representation.
The algorithm consists of the following steps:</p>
<ul class="simple">
<li>Initialization of the B-spline surface by using the Principal Component Analysis (PCA). This
assumes that the point-cloud has two main orientations, i.e. that it is roughly planar.</li>
<li>Refinement and fitting of the B-spline surface.</li>
<li>Circular initialization of the B-spline curve. Here we assume that the point-cloud is
compact, i.e. no separated clusters.</li>
<li>Fitting of the B-spline curve.</li>
<li>Triangulation of the trimmed B-spline surface.</li>
</ul>
<p>In this video, the algorithm is applied to the frontal scan of the stanford bunny (204800 points):</p>
<iframe title="Trimmed B-spline surface fitting" width="480" height="390" src="http://www.youtube.com/embed/trH2kWELvyw?rel=0" frameborder="0" allowfullscreen></iframe></div>
<div class="section" id="theoretical-background">
<h1>Theoretical background</h1>
<p>Theoretical information on the algorithm can be found in this <a class="reference external" href="http://pointclouds.org/blog/trcs/moerwald/index.php">report</a> and in my <a class="reference external" href="http://users.acin.tuwien.ac.at/tmoerwald/?site=3">PhD thesis</a>.</p>
</div>
<div class="section" id="pcl-installation-settings">
<h1>PCL installation settings</h1>
<p>Please note that the modules for NURBS and B-splines are not enabled by default.
Make sure you enable &#8220;BUILD_surface_on_nurbs&#8221; in your ccmake configuration, by setting it to ON.</p>
<p>If your license permits, also enable &#8220;USE_UMFPACK&#8221; for sparse linear solving.
This requires SuiteSparse (libsuitesparse-dev in Ubuntu) which is faster,
allows more degrees of freedom (i.e. control points) and more data points.</p>
<p>The program created during this tutorial is available in
<em>pcl/examples/surface/example_nurbs_fitting_surface.cpp</em> and is built when
&#8220;BUILD_examples&#8221; is set to ON. This will create the binary called <em>pcl_example_nurbs_fitting_surface</em>
in your <em>bin</em> folder.</p>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>The cpp file used in this tutorial can be found in <em>pcl/doc/tutorials/content/sources/bspline_fitting/bspline_fitting.cpp</em>.
You can find the input file at <em>pcl/test/bunny.pcd</em>.</p>
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
175
176
177
178
179
180
181
182
183
184
185
186
187
188
189
190
191
192
193
194
195
196
197
198
199
200
201
202
203
204
205
206
207
208
209
210
211
212
213
214
215
216
217
218
219</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;pcl/point_cloud.h&gt;</span>
<span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/io/pcd_io.h&gt;</span>

<span class="cp">#include &lt;pcl/visualization/pcl_visualizer.h&gt;</span>
<span class="cp">#include &lt;pcl/surface/on_nurbs/fitting_surface_tdm.h&gt;</span>
<span class="cp">#include &lt;pcl/surface/on_nurbs/fitting_curve_2d_asdm.h&gt;</span>
<span class="cp">#include &lt;pcl/surface/on_nurbs/triangulation.h&gt;</span>

<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">Point</span><span class="p">;</span>

<span class="kt">void</span>
<span class="nf">PointCloud2Vector3d</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">Point</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">on_nurbs</span><span class="o">::</span><span class="n">vector_vec3d</span> <span class="o">&amp;</span><span class="n">data</span><span class="p">);</span>

<span class="kt">void</span>
<span class="nf">visualizeCurve</span> <span class="p">(</span><span class="n">ON_NurbsCurve</span> <span class="o">&amp;</span><span class="n">curve</span><span class="p">,</span>
                <span class="n">ON_NurbsSurface</span> <span class="o">&amp;</span><span class="n">surface</span><span class="p">,</span>
                <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="o">&amp;</span><span class="n">viewer</span><span class="p">);</span>

<span class="kt">int</span>
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span> <span class="o">*</span><span class="n">argv</span><span class="p">[])</span>
<span class="p">{</span>
  <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">pcd_file</span><span class="p">,</span> <span class="n">file_3dm</span><span class="p">;</span>

  <span class="k">if</span> <span class="p">(</span><span class="n">argc</span> <span class="o">&lt;</span> <span class="mi">3</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;</span><span class="se">\n</span><span class="s">Usage: pcl_example_nurbs_fitting_surface pcd&lt;PointXYZ&gt;-in-file 3dm-out-file</span><span class="se">\n\n</span><span class="s">&quot;</span><span class="p">);</span>
    <span class="n">exit</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="n">pcd_file</span> <span class="o">=</span> <span class="n">argv</span><span class="p">[</span><span class="mi">1</span><span class="p">];</span>
  <span class="n">file_3dm</span> <span class="o">=</span> <span class="n">argv</span><span class="p">[</span><span class="mi">2</span><span class="p">];</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="n">viewer</span> <span class="p">(</span><span class="s">&quot;B-spline surface fitting&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">setSize</span> <span class="p">(</span><span class="mi">800</span><span class="p">,</span> <span class="mi">600</span><span class="p">);</span>

  <span class="c1">// ############################################################################</span>
  <span class="c1">// load point cloud</span>

  <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;  loading %s</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">pcd_file</span><span class="p">.</span><span class="n">c_str</span> <span class="p">());</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">Point</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">Point</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PCLPointCloud2</span> <span class="n">cloud2</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">on_nurbs</span><span class="o">::</span><span class="n">NurbsDataSurface</span> <span class="n">data</span><span class="p">;</span>

  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="n">pcd_file</span><span class="p">,</span> <span class="n">cloud2</span><span class="p">)</span> <span class="o">==</span> <span class="o">-</span><span class="mi">1</span><span class="p">)</span>
    <span class="k">throw</span> <span class="n">std</span><span class="o">::</span><span class="n">runtime_error</span> <span class="p">(</span><span class="s">&quot;  PCD file not found.&quot;</span><span class="p">);</span>

  <span class="n">fromPCLPointCloud2</span> <span class="p">(</span><span class="n">cloud2</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">PointCloud2Vector3d</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">data</span><span class="p">.</span><span class="n">interior</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">Point</span><span class="o">&gt;</span> <span class="n">handler</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">255</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span><span class="o">&lt;</span><span class="n">Point</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">handler</span><span class="p">,</span> <span class="s">&quot;cloud_cylinder&quot;</span><span class="p">);</span>
  <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;  %lu points in data set</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">());</span>

  <span class="c1">// ############################################################################</span>
  <span class="c1">// fit B-spline surface</span>

  <span class="c1">// parameters</span>
  <span class="kt">unsigned</span> <span class="n">order</span> <span class="p">(</span><span class="mi">3</span><span class="p">);</span>
  <span class="kt">unsigned</span> <span class="n">refinement</span> <span class="p">(</span><span class="mi">5</span><span class="p">);</span>
  <span class="kt">unsigned</span> <span class="n">iterations</span> <span class="p">(</span><span class="mi">10</span><span class="p">);</span>
  <span class="kt">unsigned</span> <span class="n">mesh_resolution</span> <span class="p">(</span><span class="mi">256</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">on_nurbs</span><span class="o">::</span><span class="n">FittingSurface</span><span class="o">::</span><span class="n">Parameter</span> <span class="n">params</span><span class="p">;</span>
  <span class="n">params</span><span class="p">.</span><span class="n">interior_smoothness</span> <span class="o">=</span> <span class="mf">0.2</span><span class="p">;</span>
  <span class="n">params</span><span class="p">.</span><span class="n">interior_weight</span> <span class="o">=</span> <span class="mf">1.0</span><span class="p">;</span>
  <span class="n">params</span><span class="p">.</span><span class="n">boundary_smoothness</span> <span class="o">=</span> <span class="mf">0.2</span><span class="p">;</span>
  <span class="n">params</span><span class="p">.</span><span class="n">boundary_weight</span> <span class="o">=</span> <span class="mf">0.0</span><span class="p">;</span>

  <span class="c1">// initialize</span>
  <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;  surface fitting ...</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
  <span class="n">ON_NurbsSurface</span> <span class="n">nurbs</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">on_nurbs</span><span class="o">::</span><span class="n">FittingSurface</span><span class="o">::</span><span class="n">initNurbsPCABoundingBox</span> <span class="p">(</span><span class="n">order</span><span class="p">,</span> <span class="o">&amp;</span><span class="n">data</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">on_nurbs</span><span class="o">::</span><span class="n">FittingSurface</span> <span class="n">fit</span> <span class="p">(</span><span class="o">&amp;</span><span class="n">data</span><span class="p">,</span> <span class="n">nurbs</span><span class="p">);</span>
  <span class="c1">//  fit.setQuiet (false); // enable/disable debug output</span>

  <span class="c1">// mesh for visualization</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PolygonMesh</span> <span class="n">mesh</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">mesh_cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Vertices</span><span class="o">&gt;</span> <span class="n">mesh_vertices</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">mesh_id</span> <span class="o">=</span> <span class="s">&quot;mesh_nurbs&quot;</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">on_nurbs</span><span class="o">::</span><span class="n">Triangulation</span><span class="o">::</span><span class="n">convertSurface2PolygonMesh</span> <span class="p">(</span><span class="n">fit</span><span class="p">.</span><span class="n">m_nurbs</span><span class="p">,</span> <span class="n">mesh</span><span class="p">,</span> <span class="n">mesh_resolution</span><span class="p">);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">addPolygonMesh</span> <span class="p">(</span><span class="n">mesh</span><span class="p">,</span> <span class="n">mesh_id</span><span class="p">);</span>

  <span class="c1">// surface refinement</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">unsigned</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">refinement</span><span class="p">;</span> <span class="n">i</span><span class="o">++</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">fit</span><span class="p">.</span><span class="n">refine</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
    <span class="n">fit</span><span class="p">.</span><span class="n">refine</span> <span class="p">(</span><span class="mi">1</span><span class="p">);</span>
    <span class="n">fit</span><span class="p">.</span><span class="n">assemble</span> <span class="p">(</span><span class="n">params</span><span class="p">);</span>
    <span class="n">fit</span><span class="p">.</span><span class="n">solve</span> <span class="p">();</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">on_nurbs</span><span class="o">::</span><span class="n">Triangulation</span><span class="o">::</span><span class="n">convertSurface2Vertices</span> <span class="p">(</span><span class="n">fit</span><span class="p">.</span><span class="n">m_nurbs</span><span class="p">,</span> <span class="n">mesh_cloud</span><span class="p">,</span> <span class="n">mesh_vertices</span><span class="p">,</span> <span class="n">mesh_resolution</span><span class="p">);</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">updatePolygonMesh</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">mesh_cloud</span><span class="p">,</span> <span class="n">mesh_vertices</span><span class="p">,</span> <span class="n">mesh_id</span><span class="p">);</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">spinOnce</span> <span class="p">();</span>
  <span class="p">}</span>

  <span class="c1">// surface fitting with final refinement level</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">unsigned</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">iterations</span><span class="p">;</span> <span class="n">i</span><span class="o">++</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">fit</span><span class="p">.</span><span class="n">assemble</span> <span class="p">(</span><span class="n">params</span><span class="p">);</span>
    <span class="n">fit</span><span class="p">.</span><span class="n">solve</span> <span class="p">();</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">on_nurbs</span><span class="o">::</span><span class="n">Triangulation</span><span class="o">::</span><span class="n">convertSurface2Vertices</span> <span class="p">(</span><span class="n">fit</span><span class="p">.</span><span class="n">m_nurbs</span><span class="p">,</span> <span class="n">mesh_cloud</span><span class="p">,</span> <span class="n">mesh_vertices</span><span class="p">,</span> <span class="n">mesh_resolution</span><span class="p">);</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">updatePolygonMesh</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">mesh_cloud</span><span class="p">,</span> <span class="n">mesh_vertices</span><span class="p">,</span> <span class="n">mesh_id</span><span class="p">);</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">spinOnce</span> <span class="p">();</span>
  <span class="p">}</span>

  <span class="c1">// ############################################################################</span>
  <span class="c1">// fit B-spline curve</span>

  <span class="c1">// parameters</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">on_nurbs</span><span class="o">::</span><span class="n">FittingCurve2dAPDM</span><span class="o">::</span><span class="n">FitParameter</span> <span class="n">curve_params</span><span class="p">;</span>
  <span class="n">curve_params</span><span class="p">.</span><span class="n">addCPsAccuracy</span> <span class="o">=</span> <span class="mf">5e-2</span><span class="p">;</span>
  <span class="n">curve_params</span><span class="p">.</span><span class="n">addCPsIteration</span> <span class="o">=</span> <span class="mi">3</span><span class="p">;</span>
  <span class="n">curve_params</span><span class="p">.</span><span class="n">maxCPs</span> <span class="o">=</span> <span class="mi">200</span><span class="p">;</span>
  <span class="n">curve_params</span><span class="p">.</span><span class="n">accuracy</span> <span class="o">=</span> <span class="mf">1e-3</span><span class="p">;</span>
  <span class="n">curve_params</span><span class="p">.</span><span class="n">iterations</span> <span class="o">=</span> <span class="mi">100</span><span class="p">;</span>

  <span class="n">curve_params</span><span class="p">.</span><span class="n">param</span><span class="p">.</span><span class="n">closest_point_resolution</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="n">curve_params</span><span class="p">.</span><span class="n">param</span><span class="p">.</span><span class="n">closest_point_weight</span> <span class="o">=</span> <span class="mf">1.0</span><span class="p">;</span>
  <span class="n">curve_params</span><span class="p">.</span><span class="n">param</span><span class="p">.</span><span class="n">closest_point_sigma2</span> <span class="o">=</span> <span class="mf">0.1</span><span class="p">;</span>
  <span class="n">curve_params</span><span class="p">.</span><span class="n">param</span><span class="p">.</span><span class="n">interior_sigma2</span> <span class="o">=</span> <span class="mf">0.00001</span><span class="p">;</span>
  <span class="n">curve_params</span><span class="p">.</span><span class="n">param</span><span class="p">.</span><span class="n">smooth_concavity</span> <span class="o">=</span> <span class="mf">1.0</span><span class="p">;</span>
  <span class="n">curve_params</span><span class="p">.</span><span class="n">param</span><span class="p">.</span><span class="n">smoothness</span> <span class="o">=</span> <span class="mf">1.0</span><span class="p">;</span>

  <span class="c1">// initialisation (circular)</span>
  <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;  curve fitting ...</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">on_nurbs</span><span class="o">::</span><span class="n">NurbsDataCurve2d</span> <span class="n">curve_data</span><span class="p">;</span>
  <span class="n">curve_data</span><span class="p">.</span><span class="n">interior</span> <span class="o">=</span> <span class="n">data</span><span class="p">.</span><span class="n">interior_param</span><span class="p">;</span>
  <span class="n">curve_data</span><span class="p">.</span><span class="n">interior_weight_function</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
  <span class="n">ON_NurbsCurve</span> <span class="n">curve_nurbs</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">on_nurbs</span><span class="o">::</span><span class="n">FittingCurve2dAPDM</span><span class="o">::</span><span class="n">initNurbsCurve2D</span> <span class="p">(</span><span class="n">order</span><span class="p">,</span> <span class="n">curve_data</span><span class="p">.</span><span class="n">interior</span><span class="p">);</span>

  <span class="c1">// curve fitting</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">on_nurbs</span><span class="o">::</span><span class="n">FittingCurve2dASDM</span> <span class="n">curve_fit</span> <span class="p">(</span><span class="o">&amp;</span><span class="n">curve_data</span><span class="p">,</span> <span class="n">curve_nurbs</span><span class="p">);</span>
  <span class="c1">// curve_fit.setQuiet (false); // enable/disable debug output</span>
  <span class="n">curve_fit</span><span class="p">.</span><span class="n">fitting</span> <span class="p">(</span><span class="n">curve_params</span><span class="p">);</span>
  <span class="n">visualizeCurve</span> <span class="p">(</span><span class="n">curve_fit</span><span class="p">.</span><span class="n">m_nurbs</span><span class="p">,</span> <span class="n">fit</span><span class="p">.</span><span class="n">m_nurbs</span><span class="p">,</span> <span class="n">viewer</span><span class="p">);</span>

  <span class="c1">// ############################################################################</span>
  <span class="c1">// triangulation of trimmed surface</span>

  <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;  triangulate trimmed surface ...</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">removePolygonMesh</span> <span class="p">(</span><span class="n">mesh_id</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">on_nurbs</span><span class="o">::</span><span class="n">Triangulation</span><span class="o">::</span><span class="n">convertTrimmedSurface2PolygonMesh</span> <span class="p">(</span><span class="n">fit</span><span class="p">.</span><span class="n">m_nurbs</span><span class="p">,</span> <span class="n">curve_fit</span><span class="p">.</span><span class="n">m_nurbs</span><span class="p">,</span> <span class="n">mesh</span><span class="p">,</span>
                                                                   <span class="n">mesh_resolution</span><span class="p">);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">addPolygonMesh</span> <span class="p">(</span><span class="n">mesh</span><span class="p">,</span> <span class="n">mesh_id</span><span class="p">);</span>


  <span class="c1">// save trimmed B-spline surface</span>
  <span class="k">if</span> <span class="p">(</span> <span class="n">fit</span><span class="p">.</span><span class="n">m_nurbs</span><span class="p">.</span><span class="n">IsValid</span><span class="p">()</span> <span class="p">)</span>
  <span class="p">{</span>
    <span class="n">ONX_Model</span> <span class="n">model</span><span class="p">;</span>
    <span class="n">ONX_Model_Object</span><span class="o">&amp;</span> <span class="n">surf</span> <span class="o">=</span> <span class="n">model</span><span class="p">.</span><span class="n">m_object_table</span><span class="p">.</span><span class="n">AppendNew</span><span class="p">();</span>
    <span class="n">surf</span><span class="p">.</span><span class="n">m_object</span> <span class="o">=</span> <span class="k">new</span> <span class="n">ON_NurbsSurface</span><span class="p">(</span><span class="n">fit</span><span class="p">.</span><span class="n">m_nurbs</span><span class="p">);</span>
    <span class="n">surf</span><span class="p">.</span><span class="n">m_bDeleteObject</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
    <span class="n">surf</span><span class="p">.</span><span class="n">m_attributes</span><span class="p">.</span><span class="n">m_layer_index</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
    <span class="n">surf</span><span class="p">.</span><span class="n">m_attributes</span><span class="p">.</span><span class="n">m_name</span> <span class="o">=</span> <span class="s">&quot;surface&quot;</span><span class="p">;</span>

    <span class="n">ONX_Model_Object</span><span class="o">&amp;</span> <span class="n">curv</span> <span class="o">=</span> <span class="n">model</span><span class="p">.</span><span class="n">m_object_table</span><span class="p">.</span><span class="n">AppendNew</span><span class="p">();</span>
    <span class="n">curv</span><span class="p">.</span><span class="n">m_object</span> <span class="o">=</span> <span class="k">new</span> <span class="n">ON_NurbsCurve</span><span class="p">(</span><span class="n">curve_fit</span><span class="p">.</span><span class="n">m_nurbs</span><span class="p">);</span>
    <span class="n">curv</span><span class="p">.</span><span class="n">m_bDeleteObject</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
    <span class="n">curv</span><span class="p">.</span><span class="n">m_attributes</span><span class="p">.</span><span class="n">m_layer_index</span> <span class="o">=</span> <span class="mi">2</span><span class="p">;</span>
    <span class="n">curv</span><span class="p">.</span><span class="n">m_attributes</span><span class="p">.</span><span class="n">m_name</span> <span class="o">=</span> <span class="s">&quot;trimming curve&quot;</span><span class="p">;</span>

    <span class="n">model</span><span class="p">.</span><span class="n">Write</span><span class="p">(</span><span class="n">file_3dm</span><span class="p">.</span><span class="n">c_str</span><span class="p">());</span>
    <span class="n">printf</span><span class="p">(</span><span class="s">&quot;  model saved: %s</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">file_3dm</span><span class="p">.</span><span class="n">c_str</span><span class="p">());</span>
  <span class="p">}</span>

  <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;  ... done.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>

  <span class="n">viewer</span><span class="p">.</span><span class="n">spin</span> <span class="p">();</span>
  <span class="k">return</span> <span class="mi">0</span><span class="p">;</span>
<span class="p">}</span>

<span class="kt">void</span>
<span class="nf">PointCloud2Vector3d</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">Point</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">on_nurbs</span><span class="o">::</span><span class="n">vector_vec3d</span> <span class="o">&amp;</span><span class="n">data</span><span class="p">)</span>
<span class="p">{</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">unsigned</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">();</span> <span class="n">i</span><span class="o">++</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">Point</span> <span class="o">&amp;</span><span class="n">p</span> <span class="o">=</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">at</span> <span class="p">(</span><span class="n">i</span><span class="p">);</span>
    <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">pcl_isnan</span> <span class="p">(</span><span class="n">p</span><span class="p">.</span><span class="n">x</span><span class="p">)</span> <span class="o">&amp;&amp;</span> <span class="o">!</span><span class="n">pcl_isnan</span> <span class="p">(</span><span class="n">p</span><span class="p">.</span><span class="n">y</span><span class="p">)</span> <span class="o">&amp;&amp;</span> <span class="o">!</span><span class="n">pcl_isnan</span> <span class="p">(</span><span class="n">p</span><span class="p">.</span><span class="n">z</span><span class="p">))</span>
      <span class="n">data</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3d</span> <span class="p">(</span><span class="n">p</span><span class="p">.</span><span class="n">x</span><span class="p">,</span> <span class="n">p</span><span class="p">.</span><span class="n">y</span><span class="p">,</span> <span class="n">p</span><span class="p">.</span><span class="n">z</span><span class="p">));</span>
  <span class="p">}</span>
<span class="p">}</span>

<span class="kt">void</span>
<span class="nf">visualizeCurve</span> <span class="p">(</span><span class="n">ON_NurbsCurve</span> <span class="o">&amp;</span><span class="n">curve</span><span class="p">,</span> <span class="n">ON_NurbsSurface</span> <span class="o">&amp;</span><span class="n">surface</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="o">&amp;</span><span class="n">viewer</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">curve_cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">on_nurbs</span><span class="o">::</span><span class="n">Triangulation</span><span class="o">::</span><span class="n">convertCurve2PointCloud</span> <span class="p">(</span><span class="n">curve</span><span class="p">,</span> <span class="n">surface</span><span class="p">,</span> <span class="n">curve_cloud</span><span class="p">,</span> <span class="mi">4</span><span class="p">);</span>
  <span class="k">for</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">curve_cloud</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">()</span> <span class="o">-</span> <span class="mi">1</span><span class="p">;</span> <span class="n">i</span><span class="o">++</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span> <span class="o">&amp;</span><span class="n">p1</span> <span class="o">=</span> <span class="n">curve_cloud</span><span class="o">-&gt;</span><span class="n">at</span> <span class="p">(</span><span class="n">i</span><span class="p">);</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span> <span class="o">&amp;</span><span class="n">p2</span> <span class="o">=</span> <span class="n">curve_cloud</span><span class="o">-&gt;</span><span class="n">at</span> <span class="p">(</span><span class="n">i</span> <span class="o">+</span> <span class="mi">1</span><span class="p">);</span>
    <span class="n">std</span><span class="o">::</span><span class="n">ostringstream</span> <span class="n">os</span><span class="p">;</span>
    <span class="n">os</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;line&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">i</span><span class="p">;</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">removeShape</span> <span class="p">(</span><span class="n">os</span><span class="p">.</span><span class="n">str</span> <span class="p">());</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">addLine</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">p1</span><span class="p">,</span> <span class="n">p2</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="n">os</span><span class="p">.</span><span class="n">str</span> <span class="p">());</span>
  <span class="p">}</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">curve_cps</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">int</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">curve</span><span class="p">.</span><span class="n">CVCount</span> <span class="p">();</span> <span class="n">i</span><span class="o">++</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">ON_3dPoint</span> <span class="n">p1</span><span class="p">;</span>
    <span class="n">curve</span><span class="p">.</span><span class="n">GetCV</span> <span class="p">(</span><span class="n">i</span><span class="p">,</span> <span class="n">p1</span><span class="p">);</span>

    <span class="kt">double</span> <span class="n">pnt</span><span class="p">[</span><span class="mi">3</span><span class="p">];</span>
    <span class="n">surface</span><span class="p">.</span><span class="n">Evaluate</span> <span class="p">(</span><span class="n">p1</span><span class="p">.</span><span class="n">x</span><span class="p">,</span> <span class="n">p1</span><span class="p">.</span><span class="n">y</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">3</span><span class="p">,</span> <span class="n">pnt</span><span class="p">);</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span> <span class="n">p2</span><span class="p">;</span>
    <span class="n">p2</span><span class="p">.</span><span class="n">x</span> <span class="o">=</span> <span class="kt">float</span> <span class="p">(</span><span class="n">pnt</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
    <span class="n">p2</span><span class="p">.</span><span class="n">y</span> <span class="o">=</span> <span class="kt">float</span> <span class="p">(</span><span class="n">pnt</span><span class="p">[</span><span class="mi">1</span><span class="p">]);</span>
    <span class="n">p2</span><span class="p">.</span><span class="n">z</span> <span class="o">=</span> <span class="kt">float</span> <span class="p">(</span><span class="n">pnt</span><span class="p">[</span><span class="mi">2</span><span class="p">]);</span>

    <span class="n">p2</span><span class="p">.</span><span class="n">r</span> <span class="o">=</span> <span class="mi">255</span><span class="p">;</span>
    <span class="n">p2</span><span class="p">.</span><span class="n">g</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
    <span class="n">p2</span><span class="p">.</span><span class="n">b</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>

    <span class="n">curve_cps</span><span class="o">-&gt;</span><span class="n">push_back</span> <span class="p">(</span><span class="n">p2</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">removePointCloud</span> <span class="p">(</span><span class="s">&quot;cloud_cps&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">curve_cps</span><span class="p">,</span> <span class="s">&quot;cloud_cps&quot;</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>Now, let&#8217;s break down the code piece by piece.
Lets start with the choice of the parameters for B-spline surface fitting:</p>
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
11</pre></div></td><td class="code"><div class="highlight"><pre>  <span class="c1">// parameters</span>
  <span class="kt">unsigned</span> <span class="nf">order</span> <span class="p">(</span><span class="mi">3</span><span class="p">);</span>
  <span class="kt">unsigned</span> <span class="nf">refinement</span> <span class="p">(</span><span class="mi">5</span><span class="p">);</span>
  <span class="kt">unsigned</span> <span class="nf">iterations</span> <span class="p">(</span><span class="mi">10</span><span class="p">);</span>
  <span class="kt">unsigned</span> <span class="nf">mesh_resolution</span> <span class="p">(</span><span class="mi">256</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">on_nurbs</span><span class="o">::</span><span class="n">FittingSurface</span><span class="o">::</span><span class="n">Parameter</span> <span class="n">params</span><span class="p">;</span>
  <span class="n">params</span><span class="p">.</span><span class="n">interior_smoothness</span> <span class="o">=</span> <span class="mf">0.2</span><span class="p">;</span>
  <span class="n">params</span><span class="p">.</span><span class="n">interior_weight</span> <span class="o">=</span> <span class="mf">1.0</span><span class="p">;</span>
  <span class="n">params</span><span class="p">.</span><span class="n">boundary_smoothness</span> <span class="o">=</span> <span class="mf">0.2</span><span class="p">;</span>
  <span class="n">params</span><span class="p">.</span><span class="n">boundary_weight</span> <span class="o">=</span> <span class="mf">0.0</span><span class="p">;</span>
</pre></div>
</td></tr></table></div>
<ul class="simple">
<li><em>order</em> is the polynomial order of the B-spline surface.</li>
<li><em>refinement</em> is the number of refinement iterations, where for each iteration control-points
are inserted, approximately doubling the control points in each parametric direction
of the B-spline surface.</li>
<li><em>iterations</em> is the number of iterations that are performed after refinement is completed.</li>
<li><em>mesh_resolution</em> the number of vertices in each parametric direction,
used for triangulation of the B-spline surface.</li>
</ul>
<p>Fitting:</p>
<ul class="simple">
<li><em>interior_smoothness</em> is the smoothness of the surface interior.</li>
<li><em>interior_weight</em> is the weight for optimization for the surface interior.</li>
<li><em>boundary_smoothness</em> is the smoothness of the surface boundary.</li>
<li><em>boundary_weight</em> is the weight for optimization for the surface boundary.</li>
</ul>
<p>Note, that the boundary in this case is not the trimming curve used later on.
The boundary can be used when a point-set exists that defines the boundary. Those points
can be declared in <em>pcl::on_nurbs::NurbsDataSurface::boundary</em>. In that case, when the
<em>boundary_weight</em> is greater than 0.0, the algorithm tries to align the domain boundaries
to these points. In our example we are trimming the surface anyway, so there is no need
for aligning the boundary.</p>
<div class="section" id="initialization-of-the-b-spline-surface">
<h2>Initialization of the B-spline surface</h2>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// initialize</span>
  <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;  surface fitting ...</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
  <span class="n">ON_NurbsSurface</span> <span class="n">nurbs</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">on_nurbs</span><span class="o">::</span><span class="n">FittingSurface</span><span class="o">::</span><span class="n">initNurbsPCABoundingBox</span> <span class="p">(</span><span class="n">order</span><span class="p">,</span> <span class="o">&amp;</span><span class="n">data</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">on_nurbs</span><span class="o">::</span><span class="n">FittingSurface</span> <span class="n">fit</span> <span class="p">(</span><span class="o">&amp;</span><span class="n">data</span><span class="p">,</span> <span class="n">nurbs</span><span class="p">);</span>
  <span class="c1">//  fit.setQuiet (false); // enable/disable debug output</span>
</pre></div>
</div>
<p>The command <em>initNurbsPCABoundingBox</em> uses PCA to create a coordinate systems, where the principal
eigenvectors point into the direction of the maximum, middle and minimum extension of the point-cloud.
The center of the coordinate system is located at the mean of the points.
To estimate the extension of the B-spline surface domain, a bounding box is computed in the plane formed
by the maximum and middle eigenvectors. That bounding box is used to initialize the B-spline surface with
its minimum number of control points, according to the polynomial degree chosen.</p>
<p>The surface fitting class <em>pcl::on_nurbs::FittingSurface</em> is initialized with the point data and the initial
B-spline.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// mesh for visualization</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PolygonMesh</span> <span class="n">mesh</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">mesh_cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Vertices</span><span class="o">&gt;</span> <span class="n">mesh_vertices</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">mesh_id</span> <span class="o">=</span> <span class="s">&quot;mesh_nurbs&quot;</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">on_nurbs</span><span class="o">::</span><span class="n">Triangulation</span><span class="o">::</span><span class="n">convertSurface2PolygonMesh</span> <span class="p">(</span><span class="n">fit</span><span class="p">.</span><span class="n">m_nurbs</span><span class="p">,</span> <span class="n">mesh</span><span class="p">,</span> <span class="n">mesh_resolution</span><span class="p">);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">addPolygonMesh</span> <span class="p">(</span><span class="n">mesh</span><span class="p">,</span> <span class="n">mesh_id</span><span class="p">);</span>
</pre></div>
</div>
<p>The <em>on_nurbs::Triangulation</em> class allows easy conversion between the <em>ON_NurbsSurface</em> and the <em>PolygonMesh</em> class,
for visualization of the B-spline surfaces. Note that NURBS are a generalization of B-splines,
and are therefore a valid container for B-splines, with all control-point weights = 1.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// surface refinement</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">unsigned</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">refinement</span><span class="p">;</span> <span class="n">i</span><span class="o">++</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">fit</span><span class="p">.</span><span class="n">refine</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
    <span class="n">fit</span><span class="p">.</span><span class="n">refine</span> <span class="p">(</span><span class="mi">1</span><span class="p">);</span>
    <span class="n">fit</span><span class="p">.</span><span class="n">assemble</span> <span class="p">(</span><span class="n">params</span><span class="p">);</span>
    <span class="n">fit</span><span class="p">.</span><span class="n">solve</span> <span class="p">();</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">on_nurbs</span><span class="o">::</span><span class="n">Triangulation</span><span class="o">::</span><span class="n">convertSurface2Vertices</span> <span class="p">(</span><span class="n">fit</span><span class="p">.</span><span class="n">m_nurbs</span><span class="p">,</span> <span class="n">mesh_cloud</span><span class="p">,</span> <span class="n">mesh_vertices</span><span class="p">,</span> <span class="n">mesh_resolution</span><span class="p">);</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">updatePolygonMesh</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">mesh_cloud</span><span class="p">,</span> <span class="n">mesh_vertices</span><span class="p">,</span> <span class="n">mesh_id</span><span class="p">);</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">spinOnce</span> <span class="p">();</span>
  <span class="p">}</span>
</pre></div>
</div>
</div>
<div class="section" id="refinement-and-fitting-of-the-b-spline-surface">
<h2>Refinement and fitting of the B-spline surface</h2>
<p>At this point of the code we have a B-spline surface with minimal number of control points.
Typically they are not enough to represent finer details of the underlying geometry
of the point-cloud. However, if we increase the control-points to our desired level of detail and
subsequently fit the refined B-spline, we run into problems. For robust fitting B-spline surfaces
the rule is:
&#8220;The higher the degree of freedom of the B-spline surface, the closer we have to be to the points to be approximated&#8221;.</p>
<p>This is the reason why we iteratively increase the degree of freedom by refinement in both directions (line 85-86),
and fit the B-spline surface to the point-cloud, getting closer to the final solution.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// surface fitting with final refinement level</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">unsigned</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">iterations</span><span class="p">;</span> <span class="n">i</span><span class="o">++</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">fit</span><span class="p">.</span><span class="n">assemble</span> <span class="p">(</span><span class="n">params</span><span class="p">);</span>
    <span class="n">fit</span><span class="p">.</span><span class="n">solve</span> <span class="p">();</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">on_nurbs</span><span class="o">::</span><span class="n">Triangulation</span><span class="o">::</span><span class="n">convertSurface2Vertices</span> <span class="p">(</span><span class="n">fit</span><span class="p">.</span><span class="n">m_nurbs</span><span class="p">,</span> <span class="n">mesh_cloud</span><span class="p">,</span> <span class="n">mesh_vertices</span><span class="p">,</span> <span class="n">mesh_resolution</span><span class="p">);</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">updatePolygonMesh</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">mesh_cloud</span><span class="p">,</span> <span class="n">mesh_vertices</span><span class="p">,</span> <span class="n">mesh_id</span><span class="p">);</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">spinOnce</span> <span class="p">();</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>After we reached the final level of refinement, the surface is further fitted to the point-cloud
for a pleasing end result.</p>
</div>
<div class="section" id="initialization-of-the-b-spline-curve">
<h2>Initialization of the B-spline curve</h2>
<p>Now that we have the surface fitted to the point-cloud, we want to cut off the overlapping regions of the surface.
To achieve this we project the point-cloud into the parametric domain using the closest points to the B-spline surface.
In this domain of R^2 we perform the weighted B-spline curve fitting, that creates a closed trimming curve that approximately
contains all the points.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// parameters</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">on_nurbs</span><span class="o">::</span><span class="n">FittingCurve2dAPDM</span><span class="o">::</span><span class="n">FitParameter</span> <span class="n">curve_params</span><span class="p">;</span>
  <span class="n">curve_params</span><span class="p">.</span><span class="n">addCPsAccuracy</span> <span class="o">=</span> <span class="mf">5e-2</span><span class="p">;</span>
  <span class="n">curve_params</span><span class="p">.</span><span class="n">addCPsIteration</span> <span class="o">=</span> <span class="mi">3</span><span class="p">;</span>
  <span class="n">curve_params</span><span class="p">.</span><span class="n">maxCPs</span> <span class="o">=</span> <span class="mi">200</span><span class="p">;</span>
  <span class="n">curve_params</span><span class="p">.</span><span class="n">accuracy</span> <span class="o">=</span> <span class="mf">1e-3</span><span class="p">;</span>
  <span class="n">curve_params</span><span class="p">.</span><span class="n">iterations</span> <span class="o">=</span> <span class="mi">100</span><span class="p">;</span>

  <span class="n">curve_params</span><span class="p">.</span><span class="n">param</span><span class="p">.</span><span class="n">closest_point_resolution</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="n">curve_params</span><span class="p">.</span><span class="n">param</span><span class="p">.</span><span class="n">closest_point_weight</span> <span class="o">=</span> <span class="mf">1.0</span><span class="p">;</span>
  <span class="n">curve_params</span><span class="p">.</span><span class="n">param</span><span class="p">.</span><span class="n">closest_point_sigma2</span> <span class="o">=</span> <span class="mf">0.1</span><span class="p">;</span>
  <span class="n">curve_params</span><span class="p">.</span><span class="n">param</span><span class="p">.</span><span class="n">interior_sigma2</span> <span class="o">=</span> <span class="mf">0.00001</span><span class="p">;</span>
  <span class="n">curve_params</span><span class="p">.</span><span class="n">param</span><span class="p">.</span><span class="n">smooth_concavity</span> <span class="o">=</span> <span class="mf">1.0</span><span class="p">;</span>
  <span class="n">curve_params</span><span class="p">.</span><span class="n">param</span><span class="p">.</span><span class="n">smoothness</span> <span class="o">=</span> <span class="mf">1.0</span><span class="p">;</span>
</pre></div>
</div>
<p>The topic of curve fitting goes a bit deeper into the thematics of B-splines. Here we assume that you are
familiar with the concept of B-splines, knot vectors, control-points, and so forth.
Please consider the curve being split into supporting regions which is bound by consecutive knots.
Also note that points that are inside and outside the curve are distinguished.</p>
<ul class="simple">
<li><em>addCPsAccuracy</em> the distance of the supporting region of the curve to the closest data points has to be below
this value, otherwise a control point is inserted.</li>
<li><em>addCPsIteration</em> inner iterations without inserting control points.</li>
<li><em>maxCPs</em> the maximum total number of control-points.</li>
<li><em>accuracy</em> the average fitting accuracy of the curve, w.r.t. the supporting regions.</li>
<li><em>iterations</em> maximum number of iterations performed.</li>
<li><em>closest_point_resolution</em> number of control points that must lie within each supporting region. (0 turns this constraint off)</li>
<li><em>closest_point_weight</em> weight for fitting the curve to its closest points.</li>
<li><em>closest_point_sigma2</em> threshold for closest points (disregard points that are further away from the curve).</li>
<li><em>interior_sigma2</em> threshold for interior points (disregard points that are further away from and lie within the curve).</li>
<li><em>smooth_concavity</em> value that leads to inward bending of the curve (0 = no bending; &lt;0 inward bending; &gt;0 outward bending).</li>
<li><em>smoothness</em> weight of smoothness term.</li>
</ul>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// initialisation (circular)</span>
  <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;  curve fitting ...</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">on_nurbs</span><span class="o">::</span><span class="n">NurbsDataCurve2d</span> <span class="n">curve_data</span><span class="p">;</span>
  <span class="n">curve_data</span><span class="p">.</span><span class="n">interior</span> <span class="o">=</span> <span class="n">data</span><span class="p">.</span><span class="n">interior_param</span><span class="p">;</span>
  <span class="n">curve_data</span><span class="p">.</span><span class="n">interior_weight_function</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
  <span class="n">ON_NurbsCurve</span> <span class="n">curve_nurbs</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">on_nurbs</span><span class="o">::</span><span class="n">FittingCurve2dAPDM</span><span class="o">::</span><span class="n">initNurbsCurve2D</span> <span class="p">(</span><span class="n">order</span><span class="p">,</span> <span class="n">curve_data</span><span class="p">.</span><span class="n">interior</span><span class="p">);</span>
</pre></div>
</div>
<p>The curve is initialized using a minimum number of control points to represent a circle, with the center located
at the mean of the point-cloud and the radius of the maximum distance of a point to the center.
Please note that interior weighting is enabled for all points with the command <em>curve_data.interior_weight_function.push_back (true)</em>.</p>
</div>
<div class="section" id="fitting-of-the-b-spline-curve">
<h2>Fitting of the B-spline curve</h2>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// curve fitting</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">on_nurbs</span><span class="o">::</span><span class="n">FittingCurve2dASDM</span> <span class="n">curve_fit</span> <span class="p">(</span><span class="o">&amp;</span><span class="n">curve_data</span><span class="p">,</span> <span class="n">curve_nurbs</span><span class="p">);</span>
  <span class="c1">// curve_fit.setQuiet (false); // enable/disable debug output</span>
  <span class="n">curve_fit</span><span class="p">.</span><span class="n">fitting</span> <span class="p">(</span><span class="n">curve_params</span><span class="p">);</span>
  <span class="n">visualizeCurve</span> <span class="p">(</span><span class="n">curve_fit</span><span class="p">.</span><span class="n">m_nurbs</span><span class="p">,</span> <span class="n">fit</span><span class="p">.</span><span class="n">m_nurbs</span><span class="p">,</span> <span class="n">viewer</span><span class="p">);</span>
</pre></div>
</div>
<p>Similar to the surface fitting approach, the curve is iteratively fitted and refined, as shown in the video.
Note how the curve tends to bend inwards at regions where it is not supported by any points.</p>
</div>
<div class="section" id="triangulation-of-the-trimmed-b-spline-surface">
<h2>Triangulation of the trimmed B-spline surface</h2>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// triangulation of trimmed surface</span>

  <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;  triangulate trimmed surface ...</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">removePolygonMesh</span> <span class="p">(</span><span class="n">mesh_id</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">on_nurbs</span><span class="o">::</span><span class="n">Triangulation</span><span class="o">::</span><span class="n">convertTrimmedSurface2PolygonMesh</span> <span class="p">(</span><span class="n">fit</span><span class="p">.</span><span class="n">m_nurbs</span><span class="p">,</span> <span class="n">curve_fit</span><span class="p">.</span><span class="n">m_nurbs</span><span class="p">,</span> <span class="n">mesh</span><span class="p">,</span>
                                                                   <span class="n">mesh_resolution</span><span class="p">);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">addPolygonMesh</span> <span class="p">(</span><span class="n">mesh</span><span class="p">,</span> <span class="n">mesh_id</span><span class="p">);</span>
</pre></div>
</div>
<p>After the curve fitting terminated, our geometric representation consists of a B-spline surface and a closed
B-spline curved, defined within the parametric domain of the B-spline surface. This is called trimmed B-spline surface.
In line 140 we can use the trimmed B-spline to create a triangular mesh. The triangulation algorithm first triangulates
the whole domain and afterwards removes triangles that lie outside of the trimming curve. Vertices of triangles
that intersect the trimming curve are clamped to the curve.</p>
<p>When running this example and switch to wire-frame mode (w), you will notice that the triangles are ordered in
a rectangular way, which is a result of the rectangular domain of the surface.</p>
</div>
</div>
<div class="section" id="some-hints">
<h1>Some hints</h1>
<p>Please bear in mind that the robustness of this algorithm heavily depends on the underlying data.
The parameters for B-spline fitting are designed to model the characteristics of this data.</p>
<ul class="simple">
<li>If you have holes or steps in your data, you might want to work with lower refinement levels and lower accuracy to
prevent the B-spline from folding and twisting. Moderately increasing of the smoothness might also work.</li>
<li>Try to introduce as much pre-conditioning and constraints to the parameters. E.g. if you know, that
the trimming curve is rather simple, then limit the number of maximum control points.</li>
<li>Start simple! Before giving up on gaining control over twisting and bending B-splines, I highly recommend
to start your fitting trials with a small number of control points (low refinement),
low accuracy but also low smoothness (B-splines have implicit smoothing property).</li>
</ul>
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
12</pre></div></td><td class="code"><div class="highlight"><pre><span class="nb">cmake_minimum_required</span><span class="p">(</span><span class="s">VERSION</span> <span class="s">2.8</span> <span class="s">FATAL_ERROR</span><span class="p">)</span>

<span class="nb">project</span><span class="p">(</span><span class="s">bspline_fitting</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.7</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">bspline_fitting</span> <span class="s">bspline_fitting.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">bspline_fitting</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it. Simply do:</p>
<blockquote>
<div>$ ./bspline_fitting ${PCL_ROOT}/test/bunny.pcd</div></blockquote>
</div>
<div class="section" id="saving-and-viewing-the-result">
<h1>Saving and viewing the result</h1>
<ul class="simple">
<li>Saving as OpenNURBS (3dm) file</li>
</ul>
<p>You can save the B-spline surface by using the commands provided by OpenNurbs:</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// save trimmed B-spline surface</span>
  <span class="k">if</span> <span class="p">(</span> <span class="n">fit</span><span class="p">.</span><span class="n">m_nurbs</span><span class="p">.</span><span class="n">IsValid</span><span class="p">()</span> <span class="p">)</span>
  <span class="p">{</span>
    <span class="n">ONX_Model</span> <span class="n">model</span><span class="p">;</span>
    <span class="n">ONX_Model_Object</span><span class="o">&amp;</span> <span class="n">surf</span> <span class="o">=</span> <span class="n">model</span><span class="p">.</span><span class="n">m_object_table</span><span class="p">.</span><span class="n">AppendNew</span><span class="p">();</span>
    <span class="n">surf</span><span class="p">.</span><span class="n">m_object</span> <span class="o">=</span> <span class="k">new</span> <span class="n">ON_NurbsSurface</span><span class="p">(</span><span class="n">fit</span><span class="p">.</span><span class="n">m_nurbs</span><span class="p">);</span>
    <span class="n">surf</span><span class="p">.</span><span class="n">m_bDeleteObject</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
    <span class="n">surf</span><span class="p">.</span><span class="n">m_attributes</span><span class="p">.</span><span class="n">m_layer_index</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
    <span class="n">surf</span><span class="p">.</span><span class="n">m_attributes</span><span class="p">.</span><span class="n">m_name</span> <span class="o">=</span> <span class="s">&quot;surface&quot;</span><span class="p">;</span>

    <span class="n">ONX_Model_Object</span><span class="o">&amp;</span> <span class="n">curv</span> <span class="o">=</span> <span class="n">model</span><span class="p">.</span><span class="n">m_object_table</span><span class="p">.</span><span class="n">AppendNew</span><span class="p">();</span>
    <span class="n">curv</span><span class="p">.</span><span class="n">m_object</span> <span class="o">=</span> <span class="k">new</span> <span class="n">ON_NurbsCurve</span><span class="p">(</span><span class="n">curve_fit</span><span class="p">.</span><span class="n">m_nurbs</span><span class="p">);</span>
    <span class="n">curv</span><span class="p">.</span><span class="n">m_bDeleteObject</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
    <span class="n">curv</span><span class="p">.</span><span class="n">m_attributes</span><span class="p">.</span><span class="n">m_layer_index</span> <span class="o">=</span> <span class="mi">2</span><span class="p">;</span>
    <span class="n">curv</span><span class="p">.</span><span class="n">m_attributes</span><span class="p">.</span><span class="n">m_name</span> <span class="o">=</span> <span class="s">&quot;trimming curve&quot;</span><span class="p">;</span>

    <span class="n">model</span><span class="p">.</span><span class="n">Write</span><span class="p">(</span><span class="n">file_3dm</span><span class="p">.</span><span class="n">c_str</span><span class="p">());</span>
    <span class="n">printf</span><span class="p">(</span><span class="s">&quot;  model saved: %s</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">file_3dm</span><span class="p">.</span><span class="n">c_str</span><span class="p">());</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>The files generated can be viewed with the pcl/examples/surface/example_nurbs_viewer_surface.cpp.</p>
<ul class="simple">
<li>Saving as triangle mesh into a vtk file</li>
</ul>
<p>You can save the triangle mesh for example by saving into a VTK file by:</p>
<blockquote>
<div>#include &lt;pcl/io/vtk_io.h&gt;
...
pcl::io::saveVTKFile (&#8220;mesh.vtk&#8221;, mesh);</div></blockquote>
<p>PCL also provides vtk conversion into other formats (PLY, OBJ).</p>
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