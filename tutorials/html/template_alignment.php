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
    
    <title>Aligning object templates to a point cloud</title>
    
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
            
  <div class="section" id="aligning-object-templates-to-a-point-cloud">
<span id="template-alignment"></span><h1>Aligning object templates to a point cloud</h1>
<p>This tutorial gives an example of how some of the tools covered in the other tutorials can be combined to solve a higher level problem &#8212; aligning a previously captured model of an object to some newly captured data.  In this specific example, we&#8217;ll take a depth image that contains a person and try to fit some previously captured templates of their face; this will allow us to determine the position and orientation of the face in the scene.</p>
<iframe width="560" height="349" style="margin-left:50px" src="http://www.youtube.com/embed/1T5HxTTgE4I" frameborder="0" allowfullscreen></iframe><p>We can use the code below to fit a template of a person&#8217;s face (the blue points) to a new point cloud (the green points).</p>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>First, download the datasets from <a class="reference external" href="https://github.com/PointCloudLibrary/data/tree/master/tutorials/template_alignment">github.com/PointCloudLibrary/data/tree/master/tutorials/template_alignment/</a>
and extract the files.</p>
<p>Next, copy and paste the following code into your editor and save it as <tt class="docutils literal"><span class="pre">template_alignment.cpp</span></tt> (or download the source file <a class="reference download internal" href="_downloads/template_alignment.cpp"><tt class="xref download docutils literal"><span class="pre">here</span></tt></a>).</p>
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
219
220
221
222
223
224
225
226
227
228
229
230
231
232
233
234
235
236
237
238
239
240
241
242
243
244
245
246
247
248
249
250
251
252
253
254
255
256
257
258
259
260
261
262
263
264
265
266
267
268
269
270
271
272
273
274
275
276
277
278
279
280
281
282
283
284
285
286
287
288
289
290
291
292
293
294
295
296
297
298
299
300
301
302
303
304
305
306
307
308
309</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;limits&gt;</span>
<span class="cp">#include &lt;fstream&gt;</span>
<span class="cp">#include &lt;vector&gt;</span>
<span class="cp">#include &lt;Eigen/Core&gt;</span>
<span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/point_cloud.h&gt;</span>
<span class="cp">#include &lt;pcl/io/pcd_io.h&gt;</span>
<span class="cp">#include &lt;pcl/kdtree/kdtree_flann.h&gt;</span>
<span class="cp">#include &lt;pcl/filters/passthrough.h&gt;</span>
<span class="cp">#include &lt;pcl/filters/voxel_grid.h&gt;</span>
<span class="cp">#include &lt;pcl/features/normal_3d.h&gt;</span>
<span class="cp">#include &lt;pcl/features/fpfh.h&gt;</span>
<span class="cp">#include &lt;pcl/registration/ia_ransac.h&gt;</span>

<span class="k">class</span> <span class="nc">FeatureCloud</span>
<span class="p">{</span>
  <span class="nl">public:</span>
    <span class="c1">// A bit of shorthand</span>
    <span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">PointCloud</span><span class="p">;</span>
    <span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span> <span class="n">SurfaceNormals</span><span class="p">;</span>
    <span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">FPFHSignature33</span><span class="o">&gt;</span> <span class="n">LocalFeatures</span><span class="p">;</span>
    <span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">SearchMethod</span><span class="p">;</span>

    <span class="n">FeatureCloud</span> <span class="p">()</span> <span class="o">:</span>
      <span class="n">search_method_xyz_</span> <span class="p">(</span><span class="k">new</span> <span class="n">SearchMethod</span><span class="p">),</span>
      <span class="n">normal_radius_</span> <span class="p">(</span><span class="mf">0.02f</span><span class="p">),</span>
      <span class="n">feature_radius_</span> <span class="p">(</span><span class="mf">0.02f</span><span class="p">)</span>
    <span class="p">{}</span>

    <span class="o">~</span><span class="n">FeatureCloud</span> <span class="p">()</span> <span class="p">{}</span>

    <span class="c1">// Process the given cloud</span>
    <span class="kt">void</span>
    <span class="n">setInputCloud</span> <span class="p">(</span><span class="n">PointCloud</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">xyz</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">xyz_</span> <span class="o">=</span> <span class="n">xyz</span><span class="p">;</span>
      <span class="n">processInput</span> <span class="p">();</span>
    <span class="p">}</span>

    <span class="c1">// Load and process the cloud in the given PCD file</span>
    <span class="kt">void</span>
    <span class="n">loadInputCloud</span> <span class="p">(</span><span class="k">const</span> <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="o">&amp;</span><span class="n">pcd_file</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">xyz_</span> <span class="o">=</span> <span class="n">PointCloud</span><span class="o">::</span><span class="n">Ptr</span> <span class="p">(</span><span class="k">new</span> <span class="n">PointCloud</span><span class="p">);</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="n">pcd_file</span><span class="p">,</span> <span class="o">*</span><span class="n">xyz_</span><span class="p">);</span>
      <span class="n">processInput</span> <span class="p">();</span>
    <span class="p">}</span>

    <span class="c1">// Get a pointer to the cloud 3D points</span>
    <span class="n">PointCloud</span><span class="o">::</span><span class="n">Ptr</span>
    <span class="n">getPointCloud</span> <span class="p">()</span> <span class="k">const</span>
    <span class="p">{</span>
      <span class="k">return</span> <span class="p">(</span><span class="n">xyz_</span><span class="p">);</span>
    <span class="p">}</span>

    <span class="c1">// Get a pointer to the cloud of 3D surface normals</span>
    <span class="n">SurfaceNormals</span><span class="o">::</span><span class="n">Ptr</span>
    <span class="n">getSurfaceNormals</span> <span class="p">()</span> <span class="k">const</span>
    <span class="p">{</span>
      <span class="k">return</span> <span class="p">(</span><span class="n">normals_</span><span class="p">);</span>
    <span class="p">}</span>

    <span class="c1">// Get a pointer to the cloud of feature descriptors</span>
    <span class="n">LocalFeatures</span><span class="o">::</span><span class="n">Ptr</span>
    <span class="n">getLocalFeatures</span> <span class="p">()</span> <span class="k">const</span>
    <span class="p">{</span>
      <span class="k">return</span> <span class="p">(</span><span class="n">features_</span><span class="p">);</span>
    <span class="p">}</span>

  <span class="nl">protected:</span>
    <span class="c1">// Compute the surface normals and local features</span>
    <span class="kt">void</span>
    <span class="n">processInput</span> <span class="p">()</span>
    <span class="p">{</span>
      <span class="n">computeSurfaceNormals</span> <span class="p">();</span>
      <span class="n">computeLocalFeatures</span> <span class="p">();</span>
    <span class="p">}</span>

    <span class="c1">// Compute the surface normals</span>
    <span class="kt">void</span>
    <span class="n">computeSurfaceNormals</span> <span class="p">()</span>
    <span class="p">{</span>
      <span class="n">normals_</span> <span class="o">=</span> <span class="n">SurfaceNormals</span><span class="o">::</span><span class="n">Ptr</span> <span class="p">(</span><span class="k">new</span> <span class="n">SurfaceNormals</span><span class="p">);</span>

      <span class="n">pcl</span><span class="o">::</span><span class="n">NormalEstimation</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span> <span class="n">norm_est</span><span class="p">;</span>
      <span class="n">norm_est</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">xyz_</span><span class="p">);</span>
      <span class="n">norm_est</span><span class="p">.</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">search_method_xyz_</span><span class="p">);</span>
      <span class="n">norm_est</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="n">normal_radius_</span><span class="p">);</span>
      <span class="n">norm_est</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">normals_</span><span class="p">);</span>
    <span class="p">}</span>

    <span class="c1">// Compute the local feature descriptors</span>
    <span class="kt">void</span>
    <span class="n">computeLocalFeatures</span> <span class="p">()</span>
    <span class="p">{</span>
      <span class="n">features_</span> <span class="o">=</span> <span class="n">LocalFeatures</span><span class="o">::</span><span class="n">Ptr</span> <span class="p">(</span><span class="k">new</span> <span class="n">LocalFeatures</span><span class="p">);</span>

      <span class="n">pcl</span><span class="o">::</span><span class="n">FPFHEstimation</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">FPFHSignature33</span><span class="o">&gt;</span> <span class="n">fpfh_est</span><span class="p">;</span>
      <span class="n">fpfh_est</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">xyz_</span><span class="p">);</span>
      <span class="n">fpfh_est</span><span class="p">.</span><span class="n">setInputNormals</span> <span class="p">(</span><span class="n">normals_</span><span class="p">);</span>
      <span class="n">fpfh_est</span><span class="p">.</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">search_method_xyz_</span><span class="p">);</span>
      <span class="n">fpfh_est</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="n">feature_radius_</span><span class="p">);</span>
      <span class="n">fpfh_est</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">features_</span><span class="p">);</span>
    <span class="p">}</span>

  <span class="nl">private:</span>
    <span class="c1">// Point cloud data</span>
    <span class="n">PointCloud</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">xyz_</span><span class="p">;</span>
    <span class="n">SurfaceNormals</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">normals_</span><span class="p">;</span>
    <span class="n">LocalFeatures</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">features_</span><span class="p">;</span>
    <span class="n">SearchMethod</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">search_method_xyz_</span><span class="p">;</span>

    <span class="c1">// Parameters</span>
    <span class="kt">float</span> <span class="n">normal_radius_</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">feature_radius_</span><span class="p">;</span>
<span class="p">};</span>

<span class="k">class</span> <span class="nc">TemplateAlignment</span>
<span class="p">{</span>
  <span class="nl">public:</span>

    <span class="c1">// A struct for storing alignment results</span>
    <span class="k">struct</span> <span class="n">Result</span>
    <span class="p">{</span>
      <span class="kt">float</span> <span class="n">fitness_score</span><span class="p">;</span>
      <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span> <span class="n">final_transformation</span><span class="p">;</span>
      <span class="n">EIGEN_MAKE_ALIGNED_OPERATOR_NEW</span>
    <span class="p">};</span>

    <span class="n">TemplateAlignment</span> <span class="p">()</span> <span class="o">:</span>
      <span class="n">min_sample_distance_</span> <span class="p">(</span><span class="mf">0.05f</span><span class="p">),</span>
      <span class="n">max_correspondence_distance_</span> <span class="p">(</span><span class="mf">0.01f</span><span class="o">*</span><span class="mf">0.01f</span><span class="p">),</span>
      <span class="n">nr_iterations_</span> <span class="p">(</span><span class="mi">500</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="c1">// Intialize the parameters in the Sample Consensus Intial Alignment (SAC-IA) algorithm</span>
      <span class="n">sac_ia_</span><span class="p">.</span><span class="n">setMinSampleDistance</span> <span class="p">(</span><span class="n">min_sample_distance_</span><span class="p">);</span>
      <span class="n">sac_ia_</span><span class="p">.</span><span class="n">setMaxCorrespondenceDistance</span> <span class="p">(</span><span class="n">max_correspondence_distance_</span><span class="p">);</span>
      <span class="n">sac_ia_</span><span class="p">.</span><span class="n">setMaximumIterations</span> <span class="p">(</span><span class="n">nr_iterations_</span><span class="p">);</span>
    <span class="p">}</span>

    <span class="o">~</span><span class="n">TemplateAlignment</span> <span class="p">()</span> <span class="p">{}</span>

    <span class="c1">// Set the given cloud as the target to which the templates will be aligned</span>
    <span class="kt">void</span>
    <span class="n">setTargetCloud</span> <span class="p">(</span><span class="n">FeatureCloud</span> <span class="o">&amp;</span><span class="n">target_cloud</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">target_</span> <span class="o">=</span> <span class="n">target_cloud</span><span class="p">;</span>
      <span class="n">sac_ia_</span><span class="p">.</span><span class="n">setInputTarget</span> <span class="p">(</span><span class="n">target_cloud</span><span class="p">.</span><span class="n">getPointCloud</span> <span class="p">());</span>
      <span class="n">sac_ia_</span><span class="p">.</span><span class="n">setTargetFeatures</span> <span class="p">(</span><span class="n">target_cloud</span><span class="p">.</span><span class="n">getLocalFeatures</span> <span class="p">());</span>
    <span class="p">}</span>

    <span class="c1">// Add the given cloud to the list of template clouds</span>
    <span class="kt">void</span>
    <span class="n">addTemplateCloud</span> <span class="p">(</span><span class="n">FeatureCloud</span> <span class="o">&amp;</span><span class="n">template_cloud</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">templates_</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">template_cloud</span><span class="p">);</span>
    <span class="p">}</span>

    <span class="c1">// Align the given template cloud to the target specified by setTargetCloud ()</span>
    <span class="kt">void</span>
    <span class="n">align</span> <span class="p">(</span><span class="n">FeatureCloud</span> <span class="o">&amp;</span><span class="n">template_cloud</span><span class="p">,</span> <span class="n">TemplateAlignment</span><span class="o">::</span><span class="n">Result</span> <span class="o">&amp;</span><span class="n">result</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">sac_ia_</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">template_cloud</span><span class="p">.</span><span class="n">getPointCloud</span> <span class="p">());</span>
      <span class="n">sac_ia_</span><span class="p">.</span><span class="n">setSourceFeatures</span> <span class="p">(</span><span class="n">template_cloud</span><span class="p">.</span><span class="n">getLocalFeatures</span> <span class="p">());</span>

      <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">registration_output</span><span class="p">;</span>
      <span class="n">sac_ia_</span><span class="p">.</span><span class="n">align</span> <span class="p">(</span><span class="n">registration_output</span><span class="p">);</span>

      <span class="n">result</span><span class="p">.</span><span class="n">fitness_score</span> <span class="o">=</span> <span class="p">(</span><span class="kt">float</span><span class="p">)</span> <span class="n">sac_ia_</span><span class="p">.</span><span class="n">getFitnessScore</span> <span class="p">(</span><span class="n">max_correspondence_distance_</span><span class="p">);</span>
      <span class="n">result</span><span class="p">.</span><span class="n">final_transformation</span> <span class="o">=</span> <span class="n">sac_ia_</span><span class="p">.</span><span class="n">getFinalTransformation</span> <span class="p">();</span>
    <span class="p">}</span>

    <span class="c1">// Align all of template clouds set by addTemplateCloud to the target specified by setTargetCloud ()</span>
    <span class="kt">void</span>
    <span class="n">alignAll</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">TemplateAlignment</span><span class="o">::</span><span class="n">Result</span><span class="p">,</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">aligned_allocator</span><span class="o">&lt;</span><span class="n">Result</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="o">&amp;</span><span class="n">results</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">results</span><span class="p">.</span><span class="n">resize</span> <span class="p">(</span><span class="n">templates_</span><span class="p">.</span><span class="n">size</span> <span class="p">());</span>
      <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">templates_</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
      <span class="p">{</span>
        <span class="n">align</span> <span class="p">(</span><span class="n">templates_</span><span class="p">[</span><span class="n">i</span><span class="p">],</span> <span class="n">results</span><span class="p">[</span><span class="n">i</span><span class="p">]);</span>
      <span class="p">}</span>
    <span class="p">}</span>

    <span class="c1">// Align all of template clouds to the target cloud to find the one with best alignment score</span>
    <span class="kt">int</span>
    <span class="n">findBestAlignment</span> <span class="p">(</span><span class="n">TemplateAlignment</span><span class="o">::</span><span class="n">Result</span> <span class="o">&amp;</span><span class="n">result</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="c1">// Align all of the templates to the target cloud</span>
      <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">Result</span><span class="p">,</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">aligned_allocator</span><span class="o">&lt;</span><span class="n">Result</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="n">results</span><span class="p">;</span>
      <span class="n">alignAll</span> <span class="p">(</span><span class="n">results</span><span class="p">);</span>

      <span class="c1">// Find the template with the best (lowest) fitness score</span>
      <span class="kt">float</span> <span class="n">lowest_score</span> <span class="o">=</span> <span class="n">std</span><span class="o">::</span><span class="n">numeric_limits</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;::</span><span class="n">infinity</span> <span class="p">();</span>
      <span class="kt">int</span> <span class="n">best_template</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
      <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">results</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
      <span class="p">{</span>
        <span class="k">const</span> <span class="n">Result</span> <span class="o">&amp;</span><span class="n">r</span> <span class="o">=</span> <span class="n">results</span><span class="p">[</span><span class="n">i</span><span class="p">];</span>
        <span class="k">if</span> <span class="p">(</span><span class="n">r</span><span class="p">.</span><span class="n">fitness_score</span> <span class="o">&lt;</span> <span class="n">lowest_score</span><span class="p">)</span>
        <span class="p">{</span>
          <span class="n">lowest_score</span> <span class="o">=</span> <span class="n">r</span><span class="p">.</span><span class="n">fitness_score</span><span class="p">;</span>
          <span class="n">best_template</span> <span class="o">=</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span> <span class="n">i</span><span class="p">;</span>
        <span class="p">}</span>
      <span class="p">}</span>

      <span class="c1">// Output the best alignment</span>
      <span class="n">result</span> <span class="o">=</span> <span class="n">results</span><span class="p">[</span><span class="n">best_template</span><span class="p">];</span>
      <span class="k">return</span> <span class="p">(</span><span class="n">best_template</span><span class="p">);</span>
    <span class="p">}</span>

  <span class="nl">private:</span>
    <span class="c1">// A list of template clouds and the target to which they will be aligned</span>
    <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">FeatureCloud</span><span class="o">&gt;</span> <span class="n">templates_</span><span class="p">;</span>
    <span class="n">FeatureCloud</span> <span class="n">target_</span><span class="p">;</span>

    <span class="c1">// The Sample Consensus Initial Alignment (SAC-IA) registration routine and its parameters</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">SampleConsensusInitialAlignment</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">FPFHSignature33</span><span class="o">&gt;</span> <span class="n">sac_ia_</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">min_sample_distance_</span><span class="p">;</span>
    <span class="kt">float</span> <span class="n">max_correspondence_distance_</span><span class="p">;</span>
    <span class="kt">int</span> <span class="n">nr_iterations_</span><span class="p">;</span>
<span class="p">};</span>

<span class="c1">// Align a collection of object templates to a sample point cloud</span>
<span class="kt">int</span>
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span> <span class="o">**</span><span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">argc</span> <span class="o">&lt;</span> <span class="mi">3</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;No target PCD file given!</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="c1">// Load the object templates specified in the object_templates.txt file</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">FeatureCloud</span><span class="o">&gt;</span> <span class="n">object_templates</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">ifstream</span> <span class="n">input_stream</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">1</span><span class="p">]);</span>
  <span class="n">object_templates</span><span class="p">.</span><span class="n">resize</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">pcd_filename</span><span class="p">;</span>
  <span class="k">while</span> <span class="p">(</span><span class="n">input_stream</span><span class="p">.</span><span class="n">good</span> <span class="p">())</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">getline</span> <span class="p">(</span><span class="n">input_stream</span><span class="p">,</span> <span class="n">pcd_filename</span><span class="p">);</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">pcd_filename</span><span class="p">.</span><span class="n">empty</span> <span class="p">()</span> <span class="o">||</span> <span class="n">pcd_filename</span><span class="p">.</span><span class="n">at</span> <span class="p">(</span><span class="mi">0</span><span class="p">)</span> <span class="o">==</span> <span class="sc">&#39;#&#39;</span><span class="p">)</span> <span class="c1">// Skip blank lines or comments</span>
      <span class="k">continue</span><span class="p">;</span>

    <span class="n">FeatureCloud</span> <span class="n">template_cloud</span><span class="p">;</span>
    <span class="n">template_cloud</span><span class="p">.</span><span class="n">loadInputCloud</span> <span class="p">(</span><span class="n">pcd_filename</span><span class="p">);</span>
    <span class="n">object_templates</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">template_cloud</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="n">input_stream</span><span class="p">.</span><span class="n">close</span> <span class="p">();</span>

  <span class="c1">// Load the target cloud PCD file</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">2</span><span class="p">],</span> <span class="o">*</span><span class="n">cloud</span><span class="p">);</span>

  <span class="c1">// Preprocess the cloud by...</span>
  <span class="c1">// ...removing distant points</span>
  <span class="k">const</span> <span class="kt">float</span> <span class="n">depth_limit</span> <span class="o">=</span> <span class="mf">1.0</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PassThrough</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">pass</span><span class="p">;</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">setFilterFieldName</span> <span class="p">(</span><span class="s">&quot;z&quot;</span><span class="p">);</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">setFilterLimits</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="n">depth_limit</span><span class="p">);</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud</span><span class="p">);</span>

  <span class="c1">// ... and downsampling the point cloud</span>
  <span class="k">const</span> <span class="kt">float</span> <span class="n">voxel_grid_size</span> <span class="o">=</span> <span class="mf">0.005f</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">VoxelGrid</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">vox_grid</span><span class="p">;</span>
  <span class="n">vox_grid</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">vox_grid</span><span class="p">.</span><span class="n">setLeafSize</span> <span class="p">(</span><span class="n">voxel_grid_size</span><span class="p">,</span> <span class="n">voxel_grid_size</span><span class="p">,</span> <span class="n">voxel_grid_size</span><span class="p">);</span>
  <span class="c1">//vox_grid.filter (*cloud); // Please see this http://www.pcl-developers.org/Possible-problem-in-new-VoxelGrid-implementation-from-PCL-1-5-0-td5490361.html</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">tempCloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span> 
  <span class="n">vox_grid</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">tempCloud</span><span class="p">);</span>
  <span class="n">cloud</span> <span class="o">=</span> <span class="n">tempCloud</span><span class="p">;</span> 

  <span class="c1">// Assign to the target FeatureCloud</span>
  <span class="n">FeatureCloud</span> <span class="n">target_cloud</span><span class="p">;</span>
  <span class="n">target_cloud</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>

  <span class="c1">// Set the TemplateAlignment inputs</span>
  <span class="n">TemplateAlignment</span> <span class="n">template_align</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">object_templates</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">template_align</span><span class="p">.</span><span class="n">addTemplateCloud</span> <span class="p">(</span><span class="n">object_templates</span><span class="p">[</span><span class="n">i</span><span class="p">]);</span>
  <span class="p">}</span>
  <span class="n">template_align</span><span class="p">.</span><span class="n">setTargetCloud</span> <span class="p">(</span><span class="n">target_cloud</span><span class="p">);</span>

  <span class="c1">// Find the best template alignment</span>
  <span class="n">TemplateAlignment</span><span class="o">::</span><span class="n">Result</span> <span class="n">best_alignment</span><span class="p">;</span>
  <span class="kt">int</span> <span class="n">best_index</span> <span class="o">=</span> <span class="n">template_align</span><span class="p">.</span><span class="n">findBestAlignment</span> <span class="p">(</span><span class="n">best_alignment</span><span class="p">);</span>
  <span class="k">const</span> <span class="n">FeatureCloud</span> <span class="o">&amp;</span><span class="n">best_template</span> <span class="o">=</span> <span class="n">object_templates</span><span class="p">[</span><span class="n">best_index</span><span class="p">];</span>

  <span class="c1">// Print the alignment fitness score (values less than 0.00002 are good)</span>
  <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;Best fitness score: %f</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">best_alignment</span><span class="p">.</span><span class="n">fitness_score</span><span class="p">);</span>

  <span class="c1">// Print the rotation matrix and translation vector</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix3f</span> <span class="n">rotation</span> <span class="o">=</span> <span class="n">best_alignment</span><span class="p">.</span><span class="n">final_transformation</span><span class="p">.</span><span class="n">block</span><span class="o">&lt;</span><span class="mi">3</span><span class="p">,</span><span class="mi">3</span><span class="o">&gt;</span><span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span> <span class="n">translation</span> <span class="o">=</span> <span class="n">best_alignment</span><span class="p">.</span><span class="n">final_transformation</span><span class="p">.</span><span class="n">block</span><span class="o">&lt;</span><span class="mi">3</span><span class="p">,</span><span class="mi">1</span><span class="o">&gt;</span><span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mi">3</span><span class="p">);</span>

  <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
  <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;    | %6.3f %6.3f %6.3f | </span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">0</span><span class="p">),</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">1</span><span class="p">),</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">2</span><span class="p">));</span>
  <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;R = | %6.3f %6.3f %6.3f | </span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span><span class="mi">0</span><span class="p">),</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span><span class="mi">1</span><span class="p">),</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span><span class="mi">2</span><span class="p">));</span>
  <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;    | %6.3f %6.3f %6.3f | </span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">2</span><span class="p">,</span><span class="mi">0</span><span class="p">),</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">2</span><span class="p">,</span><span class="mi">1</span><span class="p">),</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">2</span><span class="p">,</span><span class="mi">2</span><span class="p">));</span>
  <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
  <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;t = &lt; %0.3f, %0.3f, %0.3f &gt;</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">translation</span> <span class="p">(</span><span class="mi">0</span><span class="p">),</span> <span class="n">translation</span> <span class="p">(</span><span class="mi">1</span><span class="p">),</span> <span class="n">translation</span> <span class="p">(</span><span class="mi">2</span><span class="p">));</span>

  <span class="c1">// Save the aligned template for visualization</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">transformed_cloud</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">transformPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">best_template</span><span class="p">.</span><span class="n">getPointCloud</span> <span class="p">(),</span> <span class="n">transformed_cloud</span><span class="p">,</span> <span class="n">best_alignment</span><span class="p">.</span><span class="n">final_transformation</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">savePCDFileBinary</span> <span class="p">(</span><span class="s">&quot;output.pcd&quot;</span><span class="p">,</span> <span class="n">transformed_cloud</span><span class="p">);</span>

  <span class="k">return</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>Now, let&#8217;s break down the code piece by piece.</p>
<p>We&#8217;ll start by examining the <em>FeatureCloud</em> class.  This class is defined in order to provide a convenient method for computing and storing point clouds with local feature descriptors for each point.</p>
<p>The constructor creates a new <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_kd_tree_f_l_a_n_n.html">KdTreeFLANN</a> object and initializes the radius parameters that will be used when computing surface normals and local features.</p>
<div class="highlight-cpp"><div class="highlight"><pre>    <span class="n">FeatureCloud</span> <span class="p">()</span> <span class="o">:</span>
      <span class="n">search_method_xyz_</span> <span class="p">(</span><span class="k">new</span> <span class="n">SearchMethod</span><span class="p">),</span>
      <span class="n">normal_radius_</span> <span class="p">(</span><span class="mf">0.02f</span><span class="p">),</span>
      <span class="n">feature_radius_</span> <span class="p">(</span><span class="mf">0.02f</span><span class="p">)</span>
    <span class="p">{}</span>
</pre></div>
</div>
<p>Then we define methods for setting the input cloud, either by passing a shared pointer to a PointCloud or by providing the name of a PCD file to load.  In either case, after setting the input, <em>processInput</em> is called, which will compute the local feature descriptors as described later.</p>
<div class="highlight-cpp"><div class="highlight"><pre>    <span class="c1">// Process the given cloud</span>
    <span class="kt">void</span>
    <span class="nf">setInputCloud</span> <span class="p">(</span><span class="n">PointCloud</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">xyz</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">xyz_</span> <span class="o">=</span> <span class="n">xyz</span><span class="p">;</span>
      <span class="n">processInput</span> <span class="p">();</span>
    <span class="p">}</span>

    <span class="c1">// Load and process the cloud in the given PCD file</span>
    <span class="kt">void</span>
    <span class="nf">loadInputCloud</span> <span class="p">(</span><span class="k">const</span> <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="o">&amp;</span><span class="n">pcd_file</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">xyz_</span> <span class="o">=</span> <span class="n">PointCloud</span><span class="o">::</span><span class="n">Ptr</span> <span class="p">(</span><span class="k">new</span> <span class="n">PointCloud</span><span class="p">);</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="n">pcd_file</span><span class="p">,</span> <span class="o">*</span><span class="n">xyz_</span><span class="p">);</span>
      <span class="n">processInput</span> <span class="p">();</span>
    <span class="p">}</span>
</pre></div>
</div>
<p>We also define some public accessor methods that can be used to get shared pointers to the points, surface normals, and local feature descriptors.</p>
<div class="highlight-cpp"><div class="highlight"><pre>    <span class="c1">// Get a pointer to the cloud 3D points</span>
    <span class="n">PointCloud</span><span class="o">::</span><span class="n">Ptr</span>
    <span class="n">getPointCloud</span> <span class="p">()</span> <span class="k">const</span>
    <span class="p">{</span>
      <span class="k">return</span> <span class="p">(</span><span class="n">xyz_</span><span class="p">);</span>
    <span class="p">}</span>

    <span class="c1">// Get a pointer to the cloud of 3D surface normals</span>
    <span class="n">SurfaceNormals</span><span class="o">::</span><span class="n">Ptr</span>
    <span class="n">getSurfaceNormals</span> <span class="p">()</span> <span class="k">const</span>
    <span class="p">{</span>
      <span class="k">return</span> <span class="p">(</span><span class="n">normals_</span><span class="p">);</span>
    <span class="p">}</span>

    <span class="c1">// Get a pointer to the cloud of feature descriptors</span>
    <span class="n">LocalFeatures</span><span class="o">::</span><span class="n">Ptr</span>
    <span class="n">getLocalFeatures</span> <span class="p">()</span> <span class="k">const</span>
    <span class="p">{</span>
      <span class="k">return</span> <span class="p">(</span><span class="n">features_</span><span class="p">);</span>
    <span class="p">}</span>
</pre></div>
</div>
<p>Next we define the method for processing the input point cloud, which first computes the cloud&#8217;s surface normals and then computes its local features.</p>
<div class="highlight-cpp"><div class="highlight"><pre>    <span class="c1">// Compute the surface normals and local features</span>
    <span class="kt">void</span>
    <span class="nf">processInput</span> <span class="p">()</span>
    <span class="p">{</span>
      <span class="n">computeSurfaceNormals</span> <span class="p">();</span>
      <span class="n">computeLocalFeatures</span> <span class="p">();</span>
    <span class="p">}</span>
</pre></div>
</div>
<p>We use PCL&#8217;s <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_normal_estimation.html">NormalEstimation</a> class to compute the surface normals. To do so, we must specify the input point cloud, the KdTree to use when searching for neighboring points, and the radius that defines each point&#8217;s neighborhood.  We then compute the surface normals and store them in a member variable for later use.</p>
<div class="highlight-cpp"><div class="highlight"><pre>    <span class="c1">// Compute the surface normals</span>
    <span class="kt">void</span>
    <span class="nf">computeSurfaceNormals</span> <span class="p">()</span>
    <span class="p">{</span>
      <span class="n">normals_</span> <span class="o">=</span> <span class="n">SurfaceNormals</span><span class="o">::</span><span class="n">Ptr</span> <span class="p">(</span><span class="k">new</span> <span class="n">SurfaceNormals</span><span class="p">);</span>

      <span class="n">pcl</span><span class="o">::</span><span class="n">NormalEstimation</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span> <span class="n">norm_est</span><span class="p">;</span>
      <span class="n">norm_est</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">xyz_</span><span class="p">);</span>
      <span class="n">norm_est</span><span class="p">.</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">search_method_xyz_</span><span class="p">);</span>
      <span class="n">norm_est</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="n">normal_radius_</span><span class="p">);</span>
      <span class="n">norm_est</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">normals_</span><span class="p">);</span>
    <span class="p">}</span>
</pre></div>
</div>
<p>Similarly, we use PCL&#8217;s <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_f_p_f_h_estimation.html">FPFHEstimation</a> class to compute &#8220;Fast Point Feature Histogram&#8221; descriptors from the input point cloud and its surface normals.</p>
<div class="highlight-cpp"><div class="highlight"><pre>    <span class="c1">// Compute the local feature descriptors</span>
    <span class="kt">void</span>
    <span class="nf">computeLocalFeatures</span> <span class="p">()</span>
    <span class="p">{</span>
      <span class="n">features_</span> <span class="o">=</span> <span class="n">LocalFeatures</span><span class="o">::</span><span class="n">Ptr</span> <span class="p">(</span><span class="k">new</span> <span class="n">LocalFeatures</span><span class="p">);</span>

      <span class="n">pcl</span><span class="o">::</span><span class="n">FPFHEstimation</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">FPFHSignature33</span><span class="o">&gt;</span> <span class="n">fpfh_est</span><span class="p">;</span>
      <span class="n">fpfh_est</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">xyz_</span><span class="p">);</span>
      <span class="n">fpfh_est</span><span class="p">.</span><span class="n">setInputNormals</span> <span class="p">(</span><span class="n">normals_</span><span class="p">);</span>
      <span class="n">fpfh_est</span><span class="p">.</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">search_method_xyz_</span><span class="p">);</span>
      <span class="n">fpfh_est</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="n">feature_radius_</span><span class="p">);</span>
      <span class="n">fpfh_est</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">features_</span><span class="p">);</span>
    <span class="p">}</span>
</pre></div>
</div>
<p>The methods described above serve to encapsulate the work needed to compute feature descriptors and store them with their corresponding 3D point cloud.</p>
<p>Now we&#8217;ll examine the <em>TemplateAlignment</em> class, which as the name suggests, will be used to perform template alignment (also referred to as template fitting/matching/registration).  A template is typically a small group of pixels or points that represents a known part of a larger object or scene.  By registering a template to a new image or point cloud, you can determine the position and orientation of the object that the template represents.</p>
<p>We start by defining a structure to store the alignment results.  It contains a floating point value that represents the &#8220;fitness&#8221; of the alignment (a lower number means a better alignment) and a transformation matrix that describes how template points should be rotated and translated in order to best align with the points in the target cloud.</p>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">Because we are including an Eigen::Matrix4f in this struct, we need to include the EIGEN_MAKE_ALIGNED_OPERATOR_NEW macro, which will overload the struct&#8217;s &#8220;operator new&#8221; so that it will generate 16-bytes-aligned pointers.  If you&#8217;re curious, you can find more information about this issue <a class="reference external" href="http://eigen.tuxfamily.org/dox/group__TopicStructHavingEigenMembers.html">here</a>.</p>
</div>
<div class="highlight-cpp"><div class="highlight"><pre>    <span class="c1">// A struct for storing alignment results</span>
    <span class="k">struct</span> <span class="n">Result</span>
    <span class="p">{</span>
      <span class="kt">float</span> <span class="n">fitness_score</span><span class="p">;</span>
      <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span> <span class="n">final_transformation</span><span class="p">;</span>
      <span class="n">EIGEN_MAKE_ALIGNED_OPERATOR_NEW</span>
    <span class="p">};</span>
</pre></div>
</div>
<p>In the constructor, we initialize the <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_sample_consensus_initial_alignment.html">SampleConsensusInitialAlignment</a> (SAC-IA) object that we&#8217;ll be using to perform the alignment, providing values for each of its parameters.  (Note: the maximum correspondence distance is actually specified as squared distance; for this example, we&#8217;ve decided to truncate the error with an upper limit of 1 cm, so we pass in 0.01 squared.)</p>
<div class="highlight-cpp"><div class="highlight"><pre>    <span class="n">TemplateAlignment</span> <span class="p">()</span> <span class="o">:</span>
      <span class="n">min_sample_distance_</span> <span class="p">(</span><span class="mf">0.05f</span><span class="p">),</span>
      <span class="n">max_correspondence_distance_</span> <span class="p">(</span><span class="mf">0.01f</span><span class="o">*</span><span class="mf">0.01f</span><span class="p">),</span>
      <span class="n">nr_iterations_</span> <span class="p">(</span><span class="mi">500</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="c1">// Intialize the parameters in the Sample Consensus Intial Alignment (SAC-IA) algorithm</span>
      <span class="n">sac_ia_</span><span class="p">.</span><span class="n">setMinSampleDistance</span> <span class="p">(</span><span class="n">min_sample_distance_</span><span class="p">);</span>
      <span class="n">sac_ia_</span><span class="p">.</span><span class="n">setMaxCorrespondenceDistance</span> <span class="p">(</span><span class="n">max_correspondence_distance_</span><span class="p">);</span>
      <span class="n">sac_ia_</span><span class="p">.</span><span class="n">setMaximumIterations</span> <span class="p">(</span><span class="n">nr_iterations_</span><span class="p">);</span>
    <span class="p">}</span>
</pre></div>
</div>
<p>Next we define a method for setting the target cloud (i.e., the cloud to which the templates will be aligned), which sets the inputs of SAC-IA alignment algorithm.</p>
<div class="highlight-cpp"><div class="highlight"><pre>    <span class="c1">// Set the given cloud as the target to which the templates will be aligned</span>
    <span class="kt">void</span>
    <span class="nf">setTargetCloud</span> <span class="p">(</span><span class="n">FeatureCloud</span> <span class="o">&amp;</span><span class="n">target_cloud</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">target_</span> <span class="o">=</span> <span class="n">target_cloud</span><span class="p">;</span>
      <span class="n">sac_ia_</span><span class="p">.</span><span class="n">setInputTarget</span> <span class="p">(</span><span class="n">target_cloud</span><span class="p">.</span><span class="n">getPointCloud</span> <span class="p">());</span>
      <span class="n">sac_ia_</span><span class="p">.</span><span class="n">setTargetFeatures</span> <span class="p">(</span><span class="n">target_cloud</span><span class="p">.</span><span class="n">getLocalFeatures</span> <span class="p">());</span>
    <span class="p">}</span>
</pre></div>
</div>
<p>We then define a method for specifying which template or templates to attempt to align.  Each call to this method will add the given template cloud to an internal vector of FeatureClouds and store them for future use.</p>
<div class="highlight-cpp"><div class="highlight"><pre>    <span class="c1">// Add the given cloud to the list of template clouds</span>
    <span class="kt">void</span>
    <span class="nf">addTemplateCloud</span> <span class="p">(</span><span class="n">FeatureCloud</span> <span class="o">&amp;</span><span class="n">template_cloud</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">templates_</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">template_cloud</span><span class="p">);</span>
    <span class="p">}</span>
</pre></div>
</div>
<p>Next we define our alignment method.  This method takes a template as input and aligns it to the target cloud that was specified by calling <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_registration.html#a4c4e69008295052913c76175797b99a9">setInputTarget()</a>.  It works by setting the given template as the SAC-IA algorithm&#8217;s source cloud and then calling its <span>align</span> method to align the source to the target.  Note that the <span>align</span> method requires us to pass in a point cloud that will store the newly aligned source cloud, but we can ignore this output for our application.  Instead, we call SAC-IA&#8217;s accessor methods to get the alignment&#8217;s fitness score and final transformation matrix (the rigid transformation from the source cloud to the target), and we output them as a Result struct.</p>
<div class="highlight-cpp"><div class="highlight"><pre>    <span class="c1">// Align the given template cloud to the target specified by setTargetCloud ()</span>
    <span class="kt">void</span>
    <span class="nf">align</span> <span class="p">(</span><span class="n">FeatureCloud</span> <span class="o">&amp;</span><span class="n">template_cloud</span><span class="p">,</span> <span class="n">TemplateAlignment</span><span class="o">::</span><span class="n">Result</span> <span class="o">&amp;</span><span class="n">result</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">sac_ia_</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">template_cloud</span><span class="p">.</span><span class="n">getPointCloud</span> <span class="p">());</span>
      <span class="n">sac_ia_</span><span class="p">.</span><span class="n">setSourceFeatures</span> <span class="p">(</span><span class="n">template_cloud</span><span class="p">.</span><span class="n">getLocalFeatures</span> <span class="p">());</span>

      <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">registration_output</span><span class="p">;</span>
      <span class="n">sac_ia_</span><span class="p">.</span><span class="n">align</span> <span class="p">(</span><span class="n">registration_output</span><span class="p">);</span>

      <span class="n">result</span><span class="p">.</span><span class="n">fitness_score</span> <span class="o">=</span> <span class="p">(</span><span class="kt">float</span><span class="p">)</span> <span class="n">sac_ia_</span><span class="p">.</span><span class="n">getFitnessScore</span> <span class="p">(</span><span class="n">max_correspondence_distance_</span><span class="p">);</span>
      <span class="n">result</span><span class="p">.</span><span class="n">final_transformation</span> <span class="o">=</span> <span class="n">sac_ia_</span><span class="p">.</span><span class="n">getFinalTransformation</span> <span class="p">();</span>
    <span class="p">}</span>
</pre></div>
</div>
<p>Because this class is designed to work with multiple templates, we also define a method for aligning all of the templates to the target cloud and storing the results in a vector of Result structs.</p>
<div class="highlight-cpp"><div class="highlight"><pre>    <span class="c1">// Align all of template clouds set by addTemplateCloud to the target specified by setTargetCloud ()</span>
    <span class="kt">void</span>
    <span class="nf">alignAll</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">TemplateAlignment</span><span class="o">::</span><span class="n">Result</span><span class="p">,</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">aligned_allocator</span><span class="o">&lt;</span><span class="n">Result</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="o">&amp;</span><span class="n">results</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">results</span><span class="p">.</span><span class="n">resize</span> <span class="p">(</span><span class="n">templates_</span><span class="p">.</span><span class="n">size</span> <span class="p">());</span>
      <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">templates_</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
      <span class="p">{</span>
        <span class="n">align</span> <span class="p">(</span><span class="n">templates_</span><span class="p">[</span><span class="n">i</span><span class="p">],</span> <span class="n">results</span><span class="p">[</span><span class="n">i</span><span class="p">]);</span>
      <span class="p">}</span>
    <span class="p">}</span>
</pre></div>
</div>
<p>Finally, we define a method that will align all of the templates to the target cloud and return the index of the best match and its corresponding Result struct.</p>
<div class="highlight-cpp"><div class="highlight"><pre>    <span class="c1">// Align all of template clouds to the target cloud to find the one with best alignment score</span>
    <span class="kt">int</span>
    <span class="nf">findBestAlignment</span> <span class="p">(</span><span class="n">TemplateAlignment</span><span class="o">::</span><span class="n">Result</span> <span class="o">&amp;</span><span class="n">result</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="c1">// Align all of the templates to the target cloud</span>
      <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">Result</span><span class="p">,</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">aligned_allocator</span><span class="o">&lt;</span><span class="n">Result</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="n">results</span><span class="p">;</span>
      <span class="n">alignAll</span> <span class="p">(</span><span class="n">results</span><span class="p">);</span>

      <span class="c1">// Find the template with the best (lowest) fitness score</span>
      <span class="kt">float</span> <span class="n">lowest_score</span> <span class="o">=</span> <span class="n">std</span><span class="o">::</span><span class="n">numeric_limits</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;::</span><span class="n">infinity</span> <span class="p">();</span>
      <span class="kt">int</span> <span class="n">best_template</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
      <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">results</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
      <span class="p">{</span>
        <span class="k">const</span> <span class="n">Result</span> <span class="o">&amp;</span><span class="n">r</span> <span class="o">=</span> <span class="n">results</span><span class="p">[</span><span class="n">i</span><span class="p">];</span>
        <span class="k">if</span> <span class="p">(</span><span class="n">r</span><span class="p">.</span><span class="n">fitness_score</span> <span class="o">&lt;</span> <span class="n">lowest_score</span><span class="p">)</span>
        <span class="p">{</span>
          <span class="n">lowest_score</span> <span class="o">=</span> <span class="n">r</span><span class="p">.</span><span class="n">fitness_score</span><span class="p">;</span>
          <span class="n">best_template</span> <span class="o">=</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span> <span class="n">i</span><span class="p">;</span>
        <span class="p">}</span>
      <span class="p">}</span>

      <span class="c1">// Output the best alignment</span>
      <span class="n">result</span> <span class="o">=</span> <span class="n">results</span><span class="p">[</span><span class="n">best_template</span><span class="p">];</span>
      <span class="k">return</span> <span class="p">(</span><span class="n">best_template</span><span class="p">);</span>
    <span class="p">}</span>
</pre></div>
</div>
<p>Now that we have a class that handles aligning object templates, we&#8217;ll apply it to the the problem of face alignment.  In the supplied data files, we&#8217;ve included six template point clouds that we created from different views of a person&#8217;s face.  Each one was downsampled to a spacing of 5mm and manually cropped to include only points from the face.  In the following code, we show how to use our <em>TemplateAlignment</em> class to locate the position and orientation of the person&#8217;s face in a new cloud.</p>
<p>First, we load the object template clouds.  We&#8217;ve stored our templates as .PCD files, and we&#8217;ve listed their names in a file called <tt class="docutils literal"><span class="pre">object_templates.txt</span></tt>.  Here, we read in each file name, load it into a FeatureCloud, and store the FeatureCloud in a vector for later.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Load the object templates specified in the object_templates.txt file</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">FeatureCloud</span><span class="o">&gt;</span> <span class="n">object_templates</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">ifstream</span> <span class="n">input_stream</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">1</span><span class="p">]);</span>
  <span class="n">object_templates</span><span class="p">.</span><span class="n">resize</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">pcd_filename</span><span class="p">;</span>
  <span class="k">while</span> <span class="p">(</span><span class="n">input_stream</span><span class="p">.</span><span class="n">good</span> <span class="p">())</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">getline</span> <span class="p">(</span><span class="n">input_stream</span><span class="p">,</span> <span class="n">pcd_filename</span><span class="p">);</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">pcd_filename</span><span class="p">.</span><span class="n">empty</span> <span class="p">()</span> <span class="o">||</span> <span class="n">pcd_filename</span><span class="p">.</span><span class="n">at</span> <span class="p">(</span><span class="mi">0</span><span class="p">)</span> <span class="o">==</span> <span class="sc">&#39;#&#39;</span><span class="p">)</span> <span class="c1">// Skip blank lines or comments</span>
      <span class="k">continue</span><span class="p">;</span>

    <span class="n">FeatureCloud</span> <span class="n">template_cloud</span><span class="p">;</span>
    <span class="n">template_cloud</span><span class="p">.</span><span class="n">loadInputCloud</span> <span class="p">(</span><span class="n">pcd_filename</span><span class="p">);</span>
    <span class="n">object_templates</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">template_cloud</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="n">input_stream</span><span class="p">.</span><span class="n">close</span> <span class="p">();</span>
</pre></div>
</div>
<p>Next we load the target cloud (from the filename supplied on the command line).</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Load the target cloud PCD file</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">2</span><span class="p">],</span> <span class="o">*</span><span class="n">cloud</span><span class="p">);</span>
</pre></div>
</div>
<p>We then perform a little pre-processing on the data to get it ready for alignment.  The first step is to filter out any background points.  In this example we assume the person we&#8217;re trying to align to will be less than 1 meter away, so we apply a pass-through filter, filtering on the &#8220;z&#8221; field (i.e., depth) with limits of 0 to 1.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Preprocess the cloud by...</span>
  <span class="c1">// ...removing distant points</span>
  <span class="k">const</span> <span class="kt">float</span> <span class="n">depth_limit</span> <span class="o">=</span> <span class="mf">1.0</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PassThrough</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">pass</span><span class="p">;</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">setFilterFieldName</span> <span class="p">(</span><span class="s">&quot;z&quot;</span><span class="p">);</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">setFilterLimits</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="n">depth_limit</span><span class="p">);</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud</span><span class="p">);</span>
</pre></div>
</div>
<p>We also downsample the point cloud with a spacing of 5mm, which reduces the ammount of computation that&#8217;s required.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// ... and downsampling the point cloud</span>
  <span class="k">const</span> <span class="kt">float</span> <span class="n">voxel_grid_size</span> <span class="o">=</span> <span class="mf">0.005f</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">VoxelGrid</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">vox_grid</span><span class="p">;</span>
  <span class="n">vox_grid</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">vox_grid</span><span class="p">.</span><span class="n">setLeafSize</span> <span class="p">(</span><span class="n">voxel_grid_size</span><span class="p">,</span> <span class="n">voxel_grid_size</span><span class="p">,</span> <span class="n">voxel_grid_size</span><span class="p">);</span>
  <span class="c1">//vox_grid.filter (*cloud); // Please see this http://www.pcl-developers.org/Possible-problem-in-new-VoxelGrid-implementation-from-PCL-1-5-0-td5490361.html</span>
</pre></div>
</div>
<p>And after the pre-processing is finished, we create our target FeatureCloud.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">vox_grid</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">tempCloud</span><span class="p">);</span>
  <span class="n">cloud</span> <span class="o">=</span> <span class="n">tempCloud</span><span class="p">;</span> 
</pre></div>
</div>
<p>Next, we initialize our <em>TemplateAlignment</em> object.  For this, we need to add each of our template clouds and set the target cloud.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">FeatureCloud</span> <span class="n">target_cloud</span><span class="p">;</span>
  <span class="n">target_cloud</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>

  <span class="c1">// Set the TemplateAlignment inputs</span>
  <span class="n">TemplateAlignment</span> <span class="n">template_align</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">object_templates</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
</pre></div>
</div>
<p>Now that our <em>TemplateAlignment</em> object is initialized, we&#8217;re ready call the <em>findBestAlignment</em> method to determine which template best fits the given target cloud.  We store the alignment results in <em>best_alignment</em>.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="p">}</span>
  <span class="n">template_align</span><span class="p">.</span><span class="n">setTargetCloud</span> <span class="p">(</span><span class="n">target_cloud</span><span class="p">);</span>

  <span class="c1">// Find the best template alignment</span>
</pre></div>
</div>
<p>Next we output the results.  Looking at the fitness score (<em>best_alignment.fitness_score</em>) gives us an idea of how successful the alignment was, and looking at the transformation matrix (<em>best_alignment.final_transformation</em>) tells us the position and orientation of the object we aligned to in the target cloud.  Specifically, because it&#8217;s a rigid transformation, it can be decomposed into a 3-dimensional translation vector <span class="math">(t_x, t_y, t_z)</span> and a 3 x 3 rotation matrix <span class="math">R</span> as follows:</p>
<div class="math">
<p><span class="math">T = \left[ \begin{array}{cccc}
  &amp;   &amp;   &amp; t_x \\
  &amp; R &amp;   &amp; t_y \\
  &amp;   &amp;   &amp; t_z \\
0 &amp; 0 &amp; 0 &amp;  1  \end{array} \right]</span></p>
</div><div class="highlight-cpp"><div class="highlight"><pre>  <span class="kt">int</span> <span class="n">best_index</span> <span class="o">=</span> <span class="n">template_align</span><span class="p">.</span><span class="n">findBestAlignment</span> <span class="p">(</span><span class="n">best_alignment</span><span class="p">);</span>
  <span class="k">const</span> <span class="n">FeatureCloud</span> <span class="o">&amp;</span><span class="n">best_template</span> <span class="o">=</span> <span class="n">object_templates</span><span class="p">[</span><span class="n">best_index</span><span class="p">];</span>

  <span class="c1">// Print the alignment fitness score (values less than 0.00002 are good)</span>
  <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;Best fitness score: %f</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">best_alignment</span><span class="p">.</span><span class="n">fitness_score</span><span class="p">);</span>

  <span class="c1">// Print the rotation matrix and translation vector</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix3f</span> <span class="n">rotation</span> <span class="o">=</span> <span class="n">best_alignment</span><span class="p">.</span><span class="n">final_transformation</span><span class="p">.</span><span class="n">block</span><span class="o">&lt;</span><span class="mi">3</span><span class="p">,</span><span class="mi">3</span><span class="o">&gt;</span><span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span> <span class="n">translation</span> <span class="o">=</span> <span class="n">best_alignment</span><span class="p">.</span><span class="n">final_transformation</span><span class="p">.</span><span class="n">block</span><span class="o">&lt;</span><span class="mi">3</span><span class="p">,</span><span class="mi">1</span><span class="o">&gt;</span><span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mi">3</span><span class="p">);</span>

  <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
  <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;    | %6.3f %6.3f %6.3f | </span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">0</span><span class="p">),</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">1</span><span class="p">),</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">2</span><span class="p">));</span>
  <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;R = | %6.3f %6.3f %6.3f | </span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span><span class="mi">0</span><span class="p">),</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span><span class="mi">1</span><span class="p">),</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span><span class="mi">2</span><span class="p">));</span>
</pre></div>
</div>
<p>Finally, we take the best fitting template, apply the transform that aligns it to the target cloud, and save the aligned template out as a .PCD file so that we can visualize it later to see how well the alignment worked.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
  <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;t = &lt; %0.3f, %0.3f, %0.3f &gt;</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">translation</span> <span class="p">(</span><span class="mi">0</span><span class="p">),</span> <span class="n">translation</span> <span class="p">(</span><span class="mi">1</span><span class="p">),</span> <span class="n">translation</span> <span class="p">(</span><span class="mi">2</span><span class="p">));</span>

  <span class="c1">// Save the aligned template for visualization</span>
</pre></div>
</div>
</div>
<div class="section" id="compiling-and-running-the-program">
<h1>Compiling and running the program</h1>
<p>Add the following lines to your <cite>CMakeLists.txt</cite> file:</p>
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

<span class="nb">project</span><span class="p">(</span><span class="s">template_alignment</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.2</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">template_alignment</span> <span class="s">template_alignment.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">template_alignment</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it like so:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./template_alignment data/object_templates.txt data/person.pcd
</pre></div>
</div>
<p>After a few seconds, you will see output similar to:</p>
<div class="highlight-python"><div class="highlight"><pre>Best fitness score: 0.000009

    |  0.834  0.295  0.466 |
R = | -0.336  0.942  0.006 |
    | -0.437 -0.162  0.885 |

t = &lt; -0.373, -0.097, 0.087 &gt;
</pre></div>
</div>
<p>You can also use the <a class="reference external" href="http://www.pointclouds.org/documentation/overview/visualization.php">pcl_viewer</a> utility to visualize the aligned template and overlay it against the target cloud by running the following command:</p>
<div class="highlight-python"><div class="highlight"><pre>$ pcl_viewer data/person.pcd output.pcd
</pre></div>
</div>
<p>The clouds should look something like this:</p>
<a class="reference internal image-reference" href="_images/template_alignment_1.jpg"><img alt="_images/template_alignment_1.jpg" src="_images/template_alignment_1.jpg" style="height: 200px;" /></a>
<a class="reference internal image-reference" href="_images/template_alignment_2.jpg"><img alt="_images/template_alignment_2.jpg" src="_images/template_alignment_2.jpg" style="height: 200px;" /></a>
<a class="reference internal image-reference" href="_images/template_alignment_3.jpg"><img alt="_images/template_alignment_3.jpg" src="_images/template_alignment_3.jpg" style="height: 200px;" /></a>
<a class="reference internal image-reference" href="_images/template_alignment_4.jpg"><img alt="_images/template_alignment_4.jpg" src="_images/template_alignment_4.jpg" style="height: 200px;" /></a>
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