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
    
    <title>Tracking object in real time</title>
    
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
            
  <div class="section" id="tracking-object-in-real-time">
<span id="tracking"></span><h1>Tracking object in real time</h1>
<p>This tutorial explains 6D object tracking and show example code(tracking_sample.cpp) using pcl::tracking libraries. Implementing this example code, you can see the segment track the target object even if you move tracked object or your sensor device. In example, first, you should initialize tracker and you have to pass target object&#8217;s point cloud to tracker so that tracker should know what to track. So, before this tutorial, you need to make segmented model with PCD file beforehand. Setting the model to tracker, it starts tracking the object.</p>
<p>Following figure shows how  looks like when trakcing works successfully.</p>
<div class="figure align-center">
<a class="reference internal image-reference" href="_images/mergePicture.png"><img alt="_images/mergePicture.png" src="_images/mergePicture.png" style="height: 600px;" /></a>
<p class="caption">fig1: The blue model tracks the cup successfully with red particles.</p>
</div>
</div>
<div class="section" id="details">
<h1>Details</h1>
<p>The pcl_tracking library contains data structures and mechanism for 3D tracking which uses Particle Filter Algorithm. This tracking will enable you to implement 6D-pose (position and rotation) tracking which is optimized to run in real time.</p>
<dl class="docutils">
<dt>At each loop, tracking program proceeds along with following algorythm.(see fig2)</dt>
<dd><ol class="first last arabic simple">
<li>(At  t = t - 1) At first, using previous Pariticle&#8217;s information about position and rotation, it will predict each position and rotation of them at the next frame.</li>
<li>Next, we calculate weights of those particles with the likelihood formula below.(you can select which likelihood function you use)</li>
<li>Finally, we use the evaluate function which compares real point cloud data from depth sensor  with the predicted particles, and resample particles.</li>
</ol>
</dd>
</dl>
<div class="math">
<p><span class="math">L_j = L_distance ( \times L_color )

w = \sum_ L_j</span></p>
</div><div class="figure align-center">
<a class="reference internal image-reference" href="_images/slideCapture.png"><img alt="_images/slideCapture.png" src="_images/slideCapture.png" style="height: 400px;" /></a>
<p class="caption">fig2: The process of tracking Particle Filter</p>
</div>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>Create three files,  paste following code with your editor and save it as tracking_sample.cpp.</p>
<p>tracking_sample.cpp</p>
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
285</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;pcl/point_cloud.h&gt;</span>
<span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/io/openni_grabber.h&gt;</span>
<span class="cp">#include &lt;pcl/console/parse.h&gt;</span>
<span class="cp">#include &lt;pcl/common/time.h&gt;</span>
<span class="cp">#include &lt;pcl/common/centroid.h&gt;</span>

<span class="cp">#include &lt;pcl/visualization/cloud_viewer.h&gt;</span>
<span class="cp">#include &lt;pcl/visualization/pcl_visualizer.h&gt;</span>
<span class="cp">#include &lt;pcl/io/pcd_io.h&gt;</span>

<span class="cp">#include &lt;pcl/filters/passthrough.h&gt;</span>
<span class="cp">#include &lt;pcl/filters/voxel_grid.h&gt;</span>
<span class="cp">#include &lt;pcl/filters/approximate_voxel_grid.h&gt;</span>

<span class="cp">#include &lt;pcl/sample_consensus/method_types.h&gt;</span>
<span class="cp">#include &lt;pcl/sample_consensus/model_types.h&gt;</span>

<span class="cp">#include &lt;pcl/search/pcl_search.h&gt;</span>
<span class="cp">#include &lt;pcl/common/transforms.h&gt;</span>

<span class="cp">#include &lt;boost/format.hpp&gt;</span>

<span class="cp">#include &lt;pcl/tracking/tracking.h&gt;</span>
<span class="cp">#include &lt;pcl/tracking/particle_filter.h&gt;</span>
<span class="cp">#include &lt;pcl/tracking/kld_adaptive_particle_filter_omp.h&gt;</span>
<span class="cp">#include &lt;pcl/tracking/particle_filter_omp.h&gt;</span>
<span class="cp">#include &lt;pcl/tracking/coherence.h&gt;</span>
<span class="cp">#include &lt;pcl/tracking/distance_coherence.h&gt;</span>
<span class="cp">#include &lt;pcl/tracking/hsv_color_coherence.h&gt;</span>
<span class="cp">#include &lt;pcl/tracking/approx_nearest_pair_point_cloud_coherence.h&gt;</span>
<span class="cp">#include &lt;pcl/tracking/nearest_pair_point_cloud_coherence.h&gt;</span>

<span class="k">using</span> <span class="k">namespace</span> <span class="n">pcl</span><span class="o">::</span><span class="n">tracking</span><span class="p">;</span>

<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span> <span class="n">RefPointType</span><span class="p">;</span>
<span class="k">typedef</span> <span class="n">ParticleXYZRPY</span> <span class="n">ParticleT</span><span class="p">;</span>
<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span><span class="o">&gt;</span> <span class="n">Cloud</span><span class="p">;</span>
<span class="k">typedef</span> <span class="n">Cloud</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">CloudPtr</span><span class="p">;</span>
<span class="k">typedef</span> <span class="n">Cloud</span><span class="o">::</span><span class="n">ConstPtr</span> <span class="n">CloudConstPtr</span><span class="p">;</span>
<span class="k">typedef</span> <span class="n">ParticleFilterTracker</span><span class="o">&lt;</span><span class="n">RefPointType</span><span class="p">,</span> <span class="n">ParticleT</span><span class="o">&gt;</span> <span class="n">ParticleFilter</span><span class="p">;</span>

<span class="n">CloudPtr</span> <span class="n">cloud_pass_</span><span class="p">;</span>
<span class="n">CloudPtr</span> <span class="n">cloud_pass_downsampled_</span><span class="p">;</span>
<span class="n">CloudPtr</span> <span class="n">target_cloud</span><span class="p">;</span>

<span class="n">boost</span><span class="o">::</span><span class="n">mutex</span> <span class="n">mtx_</span><span class="p">;</span>
<span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">ParticleFilter</span><span class="o">&gt;</span> <span class="n">tracker_</span><span class="p">;</span>
<span class="kt">bool</span> <span class="n">new_cloud_</span><span class="p">;</span>
<span class="kt">double</span> <span class="n">downsampling_grid_size_</span><span class="p">;</span>
<span class="kt">int</span> <span class="n">counter</span><span class="p">;</span>


<span class="c1">//Filter along a specified dimension</span>
<span class="kt">void</span> <span class="nf">filterPassThrough</span> <span class="p">(</span><span class="k">const</span> <span class="n">CloudConstPtr</span> <span class="o">&amp;</span><span class="n">cloud</span><span class="p">,</span> <span class="n">Cloud</span> <span class="o">&amp;</span><span class="n">result</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PassThrough</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span><span class="o">&gt;</span> <span class="n">pass</span><span class="p">;</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">setFilterFieldName</span> <span class="p">(</span><span class="s">&quot;z&quot;</span><span class="p">);</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">setFilterLimits</span> <span class="p">(</span><span class="mf">0.0</span><span class="p">,</span> <span class="mf">10.0</span><span class="p">);</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">setKeepOrganized</span> <span class="p">(</span><span class="nb">false</span><span class="p">);</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">pass</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="n">result</span><span class="p">);</span>
<span class="p">}</span>


<span class="kt">void</span> <span class="nf">gridSampleApprox</span> <span class="p">(</span><span class="k">const</span> <span class="n">CloudConstPtr</span> <span class="o">&amp;</span><span class="n">cloud</span><span class="p">,</span> <span class="n">Cloud</span> <span class="o">&amp;</span><span class="n">result</span><span class="p">,</span> <span class="kt">double</span> <span class="n">leaf_size</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">ApproximateVoxelGrid</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span><span class="o">&gt;</span> <span class="n">grid</span><span class="p">;</span>
  <span class="n">grid</span><span class="p">.</span><span class="n">setLeafSize</span> <span class="p">(</span><span class="k">static_cast</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">leaf_size</span><span class="p">),</span> <span class="k">static_cast</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">leaf_size</span><span class="p">),</span> <span class="k">static_cast</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">leaf_size</span><span class="p">));</span>
  <span class="n">grid</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">grid</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="n">result</span><span class="p">);</span>
<span class="p">}</span>


<span class="c1">//Draw the current particles</span>
<span class="kt">bool</span>
<span class="nf">drawParticles</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&amp;</span> <span class="n">viz</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">ParticleFilter</span><span class="o">::</span><span class="n">PointCloudStatePtr</span> <span class="n">particles</span> <span class="o">=</span> <span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">getParticles</span> <span class="p">();</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">particles</span> <span class="o">&amp;&amp;</span> <span class="n">new_cloud_</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="c1">//Set pointCloud with particle&#39;s points</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">particle_cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">());</span>
      <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">particles</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="n">i</span><span class="o">++</span><span class="p">)</span>
	<span class="p">{</span>
	  <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">point</span><span class="p">;</span>
          
	  <span class="n">point</span><span class="p">.</span><span class="n">x</span> <span class="o">=</span> <span class="n">particles</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span><span class="p">;</span>
	  <span class="n">point</span><span class="p">.</span><span class="n">y</span> <span class="o">=</span> <span class="n">particles</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span><span class="p">;</span>
	  <span class="n">point</span><span class="p">.</span><span class="n">z</span> <span class="o">=</span> <span class="n">particles</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span><span class="p">;</span>
	  <span class="n">particle_cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">point</span><span class="p">);</span>
	<span class="p">}</span>

      <span class="c1">//Draw red particles </span>
      <span class="p">{</span>
	<span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">red_color</span> <span class="p">(</span><span class="n">particle_cloud</span><span class="p">,</span> <span class="mi">250</span><span class="p">,</span> <span class="mi">99</span><span class="p">,</span> <span class="mi">71</span><span class="p">);</span>

	<span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">viz</span><span class="p">.</span><span class="n">updatePointCloud</span> <span class="p">(</span><span class="n">particle_cloud</span><span class="p">,</span> <span class="n">red_color</span><span class="p">,</span> <span class="s">&quot;particle cloud&quot;</span><span class="p">))</span>
	  <span class="n">viz</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">particle_cloud</span><span class="p">,</span> <span class="n">red_color</span><span class="p">,</span> <span class="s">&quot;particle cloud&quot;</span><span class="p">);</span>
      <span class="p">}</span>
      <span class="k">return</span> <span class="nb">true</span><span class="p">;</span>
    <span class="p">}</span>
  <span class="k">else</span>
    <span class="p">{</span>
      <span class="k">return</span> <span class="nb">false</span><span class="p">;</span>
    <span class="p">}</span>
<span class="p">}</span>

<span class="c1">//Draw model reference point cloud</span>
<span class="kt">void</span>
<span class="nf">drawResult</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&amp;</span> <span class="n">viz</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">ParticleXYZRPY</span> <span class="n">result</span> <span class="o">=</span> <span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">getResult</span> <span class="p">();</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Affine3f</span> <span class="n">transformation</span> <span class="o">=</span> <span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">toEigenMatrix</span> <span class="p">(</span><span class="n">result</span><span class="p">);</span>

  <span class="c1">//move close to camera a little for better visualization</span>
  <span class="n">transformation</span><span class="p">.</span><span class="n">translation</span> <span class="p">()</span> <span class="o">+=</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span> <span class="p">(</span><span class="mf">0.0f</span><span class="p">,</span> <span class="mf">0.0f</span><span class="p">,</span> <span class="o">-</span><span class="mf">0.005f</span><span class="p">);</span>
  <span class="n">CloudPtr</span> <span class="n">result_cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">Cloud</span> <span class="p">());</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">transformPointCloud</span><span class="o">&lt;</span><span class="n">RefPointType</span><span class="o">&gt;</span> <span class="p">(</span><span class="o">*</span><span class="p">(</span><span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">getReferenceCloud</span> <span class="p">()),</span> <span class="o">*</span><span class="n">result_cloud</span><span class="p">,</span> <span class="n">transformation</span><span class="p">);</span>

  <span class="c1">//Draw blue model reference point cloud</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">RefPointType</span><span class="o">&gt;</span> <span class="n">blue_color</span> <span class="p">(</span><span class="n">result_cloud</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">255</span><span class="p">);</span>

    <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">viz</span><span class="p">.</span><span class="n">updatePointCloud</span> <span class="p">(</span><span class="n">result_cloud</span><span class="p">,</span> <span class="n">blue_color</span><span class="p">,</span> <span class="s">&quot;resultcloud&quot;</span><span class="p">))</span>
      <span class="n">viz</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">result_cloud</span><span class="p">,</span> <span class="n">blue_color</span><span class="p">,</span> <span class="s">&quot;resultcloud&quot;</span><span class="p">);</span>
  <span class="p">}</span>
<span class="p">}</span>

<span class="c1">//visualization&#39;s callback function</span>
<span class="kt">void</span>
<span class="nf">viz_cb</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&amp;</span> <span class="n">viz</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">mutex</span><span class="o">::</span><span class="n">scoped_lock</span> <span class="n">lock</span> <span class="p">(</span><span class="n">mtx_</span><span class="p">);</span>
    
  <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">cloud_pass_</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">boost</span><span class="o">::</span><span class="n">this_thread</span><span class="o">::</span><span class="n">sleep</span> <span class="p">(</span><span class="n">boost</span><span class="o">::</span><span class="n">posix_time</span><span class="o">::</span><span class="n">seconds</span> <span class="p">(</span><span class="mi">1</span><span class="p">));</span>
      <span class="k">return</span><span class="p">;</span>
   <span class="p">}</span>

  <span class="c1">//Draw downsampled point cloud from sensor    </span>
  <span class="k">if</span> <span class="p">(</span><span class="n">new_cloud_</span> <span class="o">&amp;&amp;</span> <span class="n">cloud_pass_downsampled_</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">CloudPtr</span> <span class="n">cloud_pass</span><span class="p">;</span>
      <span class="n">cloud_pass</span> <span class="o">=</span> <span class="n">cloud_pass_downsampled_</span><span class="p">;</span>
    
      <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">viz</span><span class="p">.</span><span class="n">updatePointCloud</span> <span class="p">(</span><span class="n">cloud_pass</span><span class="p">,</span> <span class="s">&quot;cloudpass&quot;</span><span class="p">))</span>
	<span class="p">{</span>
	  <span class="n">viz</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">cloud_pass</span><span class="p">,</span> <span class="s">&quot;cloudpass&quot;</span><span class="p">);</span>
	  <span class="n">viz</span><span class="p">.</span><span class="n">resetCameraViewpoint</span> <span class="p">(</span><span class="s">&quot;cloudpass&quot;</span><span class="p">);</span>
	<span class="p">}</span>
      <span class="kt">bool</span> <span class="n">ret</span> <span class="o">=</span> <span class="n">drawParticles</span> <span class="p">(</span><span class="n">viz</span><span class="p">);</span>
      <span class="k">if</span> <span class="p">(</span><span class="n">ret</span><span class="p">)</span>
        <span class="n">drawResult</span> <span class="p">(</span><span class="n">viz</span><span class="p">);</span>
    <span class="p">}</span>
  <span class="n">new_cloud_</span> <span class="o">=</span> <span class="nb">false</span><span class="p">;</span>
<span class="p">}</span>

<span class="c1">//OpenNI Grabber&#39;s cloud Callback function</span>
<span class="kt">void</span>
<span class="nf">cloud_cb</span> <span class="p">(</span><span class="k">const</span> <span class="n">CloudConstPtr</span> <span class="o">&amp;</span><span class="n">cloud</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">mutex</span><span class="o">::</span><span class="n">scoped_lock</span> <span class="n">lock</span> <span class="p">(</span><span class="n">mtx_</span><span class="p">);</span>
  <span class="n">cloud_pass_</span><span class="p">.</span><span class="n">reset</span> <span class="p">(</span><span class="k">new</span> <span class="n">Cloud</span><span class="p">);</span>
  <span class="n">cloud_pass_downsampled_</span><span class="p">.</span><span class="n">reset</span> <span class="p">(</span><span class="k">new</span> <span class="n">Cloud</span><span class="p">);</span>
  <span class="n">filterPassThrough</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud_pass_</span><span class="p">);</span>
  <span class="n">gridSampleApprox</span> <span class="p">(</span><span class="n">cloud_pass_</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud_pass_downsampled_</span><span class="p">,</span> <span class="n">downsampling_grid_size_</span><span class="p">);</span>

  <span class="k">if</span><span class="p">(</span><span class="n">counter</span> <span class="o">&lt;</span> <span class="mi">10</span><span class="p">){</span>
	<span class="n">counter</span><span class="o">++</span><span class="p">;</span>
  <span class="p">}</span><span class="k">else</span><span class="p">{</span>
  	<span class="c1">//Track the object</span>
	<span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_pass_downsampled_</span><span class="p">);</span>
	<span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">compute</span> <span class="p">();</span>
	<span class="n">new_cloud_</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
  <span class="p">}</span>
<span class="p">}</span>

<span class="kt">int</span>
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">argc</span> <span class="o">&lt;</span> <span class="mi">3</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">PCL_WARN</span><span class="p">(</span><span class="s">&quot;Please set device_id pcd_filename(e.g. $ %s &#39;#1&#39; sample.pcd)</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
      <span class="n">exit</span> <span class="p">(</span><span class="mi">1</span><span class="p">);</span>
    <span class="p">}</span>

  <span class="c1">//read pcd file</span>
  <span class="n">target_cloud</span><span class="p">.</span><span class="n">reset</span><span class="p">(</span><span class="k">new</span> <span class="n">Cloud</span><span class="p">());</span>
  <span class="k">if</span><span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">2</span><span class="p">],</span> <span class="o">*</span><span class="n">target_cloud</span><span class="p">)</span> <span class="o">==</span> <span class="o">-</span><span class="mi">1</span><span class="p">){</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;pcd file not found&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="n">exit</span><span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">device_id</span> <span class="o">=</span> <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">1</span><span class="p">]);</span>  

  <span class="n">counter</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>

  <span class="c1">//Set parameters</span>
  <span class="n">new_cloud_</span>  <span class="o">=</span> <span class="nb">false</span><span class="p">;</span>
  <span class="n">downsampling_grid_size_</span> <span class="o">=</span>  <span class="mf">0.002</span><span class="p">;</span>

  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">double</span><span class="o">&gt;</span> <span class="n">default_step_covariance</span> <span class="o">=</span> <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">double</span><span class="o">&gt;</span> <span class="p">(</span><span class="mi">6</span><span class="p">,</span> <span class="mf">0.015</span> <span class="o">*</span> <span class="mf">0.015</span><span class="p">);</span>
  <span class="n">default_step_covariance</span><span class="p">[</span><span class="mi">3</span><span class="p">]</span> <span class="o">*=</span> <span class="mf">40.0</span><span class="p">;</span>
  <span class="n">default_step_covariance</span><span class="p">[</span><span class="mi">4</span><span class="p">]</span> <span class="o">*=</span> <span class="mf">40.0</span><span class="p">;</span>
  <span class="n">default_step_covariance</span><span class="p">[</span><span class="mi">5</span><span class="p">]</span> <span class="o">*=</span> <span class="mf">40.0</span><span class="p">;</span>

  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">double</span><span class="o">&gt;</span> <span class="n">initial_noise_covariance</span> <span class="o">=</span> <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">double</span><span class="o">&gt;</span> <span class="p">(</span><span class="mi">6</span><span class="p">,</span> <span class="mf">0.00001</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">double</span><span class="o">&gt;</span> <span class="n">default_initial_mean</span> <span class="o">=</span> <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">double</span><span class="o">&gt;</span> <span class="p">(</span><span class="mi">6</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">);</span>

  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">KLDAdaptiveParticleFilterOMPTracker</span><span class="o">&lt;</span><span class="n">RefPointType</span><span class="p">,</span> <span class="n">ParticleT</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="n">tracker</span>
    <span class="p">(</span><span class="k">new</span> <span class="n">KLDAdaptiveParticleFilterOMPTracker</span><span class="o">&lt;</span><span class="n">RefPointType</span><span class="p">,</span> <span class="n">ParticleT</span><span class="o">&gt;</span> <span class="p">(</span><span class="mi">8</span><span class="p">));</span>

  <span class="n">ParticleT</span> <span class="n">bin_size</span><span class="p">;</span>
  <span class="n">bin_size</span><span class="p">.</span><span class="n">x</span> <span class="o">=</span> <span class="mf">0.1f</span><span class="p">;</span>
  <span class="n">bin_size</span><span class="p">.</span><span class="n">y</span> <span class="o">=</span> <span class="mf">0.1f</span><span class="p">;</span>
  <span class="n">bin_size</span><span class="p">.</span><span class="n">z</span> <span class="o">=</span> <span class="mf">0.1f</span><span class="p">;</span>
  <span class="n">bin_size</span><span class="p">.</span><span class="n">roll</span> <span class="o">=</span> <span class="mf">0.1f</span><span class="p">;</span>
  <span class="n">bin_size</span><span class="p">.</span><span class="n">pitch</span> <span class="o">=</span> <span class="mf">0.1f</span><span class="p">;</span>
  <span class="n">bin_size</span><span class="p">.</span><span class="n">yaw</span> <span class="o">=</span> <span class="mf">0.1f</span><span class="p">;</span>


  <span class="c1">//Set all parameters for  KLDAdaptiveParticleFilterOMPTracker</span>
  <span class="n">tracker</span><span class="o">-&gt;</span><span class="n">setMaximumParticleNum</span> <span class="p">(</span><span class="mi">1000</span><span class="p">);</span>
  <span class="n">tracker</span><span class="o">-&gt;</span><span class="n">setDelta</span> <span class="p">(</span><span class="mf">0.99</span><span class="p">);</span>
  <span class="n">tracker</span><span class="o">-&gt;</span><span class="n">setEpsilon</span> <span class="p">(</span><span class="mf">0.2</span><span class="p">);</span>
  <span class="n">tracker</span><span class="o">-&gt;</span><span class="n">setBinSize</span> <span class="p">(</span><span class="n">bin_size</span><span class="p">);</span>

  <span class="c1">//Set all parameters for  ParticleFilter</span>
  <span class="n">tracker_</span> <span class="o">=</span> <span class="n">tracker</span><span class="p">;</span>
  <span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">setTrans</span> <span class="p">(</span><span class="n">Eigen</span><span class="o">::</span><span class="n">Affine3f</span><span class="o">::</span><span class="n">Identity</span> <span class="p">());</span>
  <span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">setStepNoiseCovariance</span> <span class="p">(</span><span class="n">default_step_covariance</span><span class="p">);</span>
  <span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">setInitialNoiseCovariance</span> <span class="p">(</span><span class="n">initial_noise_covariance</span><span class="p">);</span>
  <span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">setInitialNoiseMean</span> <span class="p">(</span><span class="n">default_initial_mean</span><span class="p">);</span>
  <span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">setIterationNum</span> <span class="p">(</span><span class="mi">1</span><span class="p">);</span>
  <span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">setParticleNum</span> <span class="p">(</span><span class="mi">600</span><span class="p">);</span>
  <span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">setResampleLikelihoodThr</span><span class="p">(</span><span class="mf">0.00</span><span class="p">);</span>
  <span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">setUseNormal</span> <span class="p">(</span><span class="nb">false</span><span class="p">);</span>


  <span class="c1">//Setup coherence object for tracking</span>
  <span class="n">ApproxNearestPairPointCloudCoherence</span><span class="o">&lt;</span><span class="n">RefPointType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">coherence</span> <span class="o">=</span> <span class="n">ApproxNearestPairPointCloudCoherence</span><span class="o">&lt;</span><span class="n">RefPointType</span><span class="o">&gt;::</span><span class="n">Ptr</span>
    <span class="p">(</span><span class="k">new</span> <span class="n">ApproxNearestPairPointCloudCoherence</span><span class="o">&lt;</span><span class="n">RefPointType</span><span class="o">&gt;</span> <span class="p">());</span>
    
  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">DistanceCoherence</span><span class="o">&lt;</span><span class="n">RefPointType</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="n">distance_coherence</span>
    <span class="o">=</span> <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">DistanceCoherence</span><span class="o">&lt;</span><span class="n">RefPointType</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="p">(</span><span class="k">new</span> <span class="n">DistanceCoherence</span><span class="o">&lt;</span><span class="n">RefPointType</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">coherence</span><span class="o">-&gt;</span><span class="n">addPointCoherence</span> <span class="p">(</span><span class="n">distance_coherence</span><span class="p">);</span>

  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">Octree</span><span class="o">&lt;</span><span class="n">RefPointType</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="n">search</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">Octree</span><span class="o">&lt;</span><span class="n">RefPointType</span><span class="o">&gt;</span> <span class="p">(</span><span class="mf">0.01</span><span class="p">));</span>
  <span class="n">coherence</span><span class="o">-&gt;</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">search</span><span class="p">);</span>
  <span class="n">coherence</span><span class="o">-&gt;</span><span class="n">setMaximumDistance</span> <span class="p">(</span><span class="mf">0.01</span><span class="p">);</span>

  <span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">setCloudCoherence</span> <span class="p">(</span><span class="n">coherence</span><span class="p">);</span>

  <span class="c1">//prepare the model of tracker&#39;s target</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector4f</span> <span class="n">c</span><span class="p">;</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Affine3f</span> <span class="n">trans</span> <span class="o">=</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Affine3f</span><span class="o">::</span><span class="n">Identity</span> <span class="p">();</span>
  <span class="n">CloudPtr</span> <span class="n">transed_ref</span> <span class="p">(</span><span class="k">new</span> <span class="n">Cloud</span><span class="p">);</span>
  <span class="n">CloudPtr</span> <span class="n">transed_ref_downsampled</span> <span class="p">(</span><span class="k">new</span> <span class="n">Cloud</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">compute3DCentroid</span><span class="o">&lt;</span><span class="n">RefPointType</span><span class="o">&gt;</span> <span class="p">(</span><span class="o">*</span><span class="n">target_cloud</span><span class="p">,</span> <span class="n">c</span><span class="p">);</span>
  <span class="n">trans</span><span class="p">.</span><span class="n">translation</span> <span class="p">().</span><span class="n">matrix</span> <span class="p">()</span> <span class="o">=</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span> <span class="p">(</span><span class="n">c</span><span class="p">[</span><span class="mi">0</span><span class="p">],</span> <span class="n">c</span><span class="p">[</span><span class="mi">1</span><span class="p">],</span> <span class="n">c</span><span class="p">[</span><span class="mi">2</span><span class="p">]);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">transformPointCloud</span><span class="o">&lt;</span><span class="n">RefPointType</span><span class="o">&gt;</span> <span class="p">(</span><span class="o">*</span><span class="n">target_cloud</span><span class="p">,</span> <span class="o">*</span><span class="n">transed_ref</span><span class="p">,</span> <span class="n">trans</span><span class="p">.</span><span class="n">inverse</span><span class="p">());</span>
  <span class="n">gridSampleApprox</span> <span class="p">(</span><span class="n">transed_ref</span><span class="p">,</span> <span class="o">*</span><span class="n">transed_ref_downsampled</span><span class="p">,</span> <span class="n">downsampling_grid_size_</span><span class="p">);</span>

  <span class="c1">//set reference model and trans</span>
  <span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">setReferenceCloud</span> <span class="p">(</span><span class="n">transed_ref_downsampled</span><span class="p">);</span>
  <span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">setTrans</span> <span class="p">(</span><span class="n">trans</span><span class="p">);</span>

  <span class="c1">//Setup OpenNIGrabber and viewer</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">CloudViewer</span><span class="o">*</span> <span class="n">viewer_</span> <span class="o">=</span> <span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">CloudViewer</span><span class="p">(</span><span class="s">&quot;PCL OpenNI Tracking Viewer&quot;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">Grabber</span><span class="o">*</span> <span class="n">interface</span> <span class="o">=</span> <span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">OpenNIGrabber</span> <span class="p">(</span><span class="n">device_id</span><span class="p">);</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">function</span><span class="o">&lt;</span><span class="kt">void</span> <span class="p">(</span><span class="k">const</span> <span class="n">CloudConstPtr</span><span class="o">&amp;</span><span class="p">)</span><span class="o">&gt;</span> <span class="n">f</span> <span class="o">=</span>
    <span class="n">boost</span><span class="o">::</span><span class="n">bind</span> <span class="p">(</span><span class="o">&amp;</span><span class="n">cloud_cb</span><span class="p">,</span> <span class="n">_1</span><span class="p">);</span>
  <span class="n">interface</span><span class="o">-&gt;</span><span class="n">registerCallback</span> <span class="p">(</span><span class="n">f</span><span class="p">);</span>
    
  <span class="n">viewer_</span><span class="o">-&gt;</span><span class="n">runOnVisualizationThread</span> <span class="p">(</span><span class="n">boost</span><span class="o">::</span><span class="n">bind</span><span class="p">(</span><span class="o">&amp;</span><span class="n">viz_cb</span><span class="p">,</span> <span class="n">_1</span><span class="p">),</span> <span class="s">&quot;viz_cb&quot;</span><span class="p">);</span>

  <span class="c1">//Start viewer and object tracking</span>
  <span class="n">interface</span><span class="o">-&gt;</span><span class="n">start</span><span class="p">();</span>
  <span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">viewer_</span><span class="o">-&gt;</span><span class="n">wasStopped</span> <span class="p">())</span>
    <span class="n">boost</span><span class="o">::</span><span class="n">this_thread</span><span class="o">::</span><span class="n">sleep</span><span class="p">(</span><span class="n">boost</span><span class="o">::</span><span class="n">posix_time</span><span class="o">::</span><span class="n">seconds</span><span class="p">(</span><span class="mi">1</span><span class="p">));</span>
  <span class="n">interface</span><span class="o">-&gt;</span><span class="n">stop</span><span class="p">();</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>Now, let&#8217;s break down the code piece by piece.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">//Set all parameters for  KLDAdaptiveParticleFilterOMPTracker</span>
  <span class="n">tracker</span><span class="o">-&gt;</span><span class="n">setMaximumParticleNum</span> <span class="p">(</span><span class="mi">1000</span><span class="p">);</span>
  <span class="n">tracker</span><span class="o">-&gt;</span><span class="n">setDelta</span> <span class="p">(</span><span class="mf">0.99</span><span class="p">);</span>
  <span class="n">tracker</span><span class="o">-&gt;</span><span class="n">setEpsilon</span> <span class="p">(</span><span class="mf">0.2</span><span class="p">);</span>
  <span class="n">tracker</span><span class="o">-&gt;</span><span class="n">setBinSize</span> <span class="p">(</span><span class="n">bin_size</span><span class="p">);</span>

  <span class="c1">//Set all parameters for  ParticleFilter</span>
  <span class="n">tracker_</span> <span class="o">=</span> <span class="n">tracker</span><span class="p">;</span>
  <span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">setTrans</span> <span class="p">(</span><span class="n">Eigen</span><span class="o">::</span><span class="n">Affine3f</span><span class="o">::</span><span class="n">Identity</span> <span class="p">());</span>
  <span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">setStepNoiseCovariance</span> <span class="p">(</span><span class="n">default_step_covariance</span><span class="p">);</span>
  <span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">setInitialNoiseCovariance</span> <span class="p">(</span><span class="n">initial_noise_covariance</span><span class="p">);</span>
  <span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">setInitialNoiseMean</span> <span class="p">(</span><span class="n">default_initial_mean</span><span class="p">);</span>
  <span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">setIterationNum</span> <span class="p">(</span><span class="mi">1</span><span class="p">);</span>
  <span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">setParticleNum</span> <span class="p">(</span><span class="mi">600</span><span class="p">);</span>
  <span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">setResampleLikelihoodThr</span><span class="p">(</span><span class="mf">0.00</span><span class="p">);</span>
  <span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">setUseNormal</span> <span class="p">(</span><span class="nb">false</span><span class="p">);</span>
</pre></div>
</div>
<p>First, in main function, these lines set the parameters for tracking.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">ApproxNearestPairPointCloudCoherence</span><span class="o">&lt;</span><span class="n">RefPointType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">coherence</span> <span class="o">=</span> <span class="n">ApproxNearestPairPointCloudCoherence</span><span class="o">&lt;</span><span class="n">RefPointType</span><span class="o">&gt;::</span><span class="n">Ptr</span>
    <span class="p">(</span><span class="k">new</span> <span class="n">ApproxNearestPairPointCloudCoherence</span><span class="o">&lt;</span><span class="n">RefPointType</span><span class="o">&gt;</span> <span class="p">());</span>
    
  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">DistanceCoherence</span><span class="o">&lt;</span><span class="n">RefPointType</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="n">distance_coherence</span>
    <span class="o">=</span> <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">DistanceCoherence</span><span class="o">&lt;</span><span class="n">RefPointType</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="p">(</span><span class="k">new</span> <span class="n">DistanceCoherence</span><span class="o">&lt;</span><span class="n">RefPointType</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">coherence</span><span class="o">-&gt;</span><span class="n">addPointCoherence</span> <span class="p">(</span><span class="n">distance_coherence</span><span class="p">);</span>

  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">Octree</span><span class="o">&lt;</span><span class="n">RefPointType</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="n">search</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">Octree</span><span class="o">&lt;</span><span class="n">RefPointType</span><span class="o">&gt;</span> <span class="p">(</span><span class="mf">0.01</span><span class="p">));</span>
  <span class="n">coherence</span><span class="o">-&gt;</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">search</span><span class="p">);</span>
  <span class="n">coherence</span><span class="o">-&gt;</span><span class="n">setMaximumDistance</span> <span class="p">(</span><span class="mf">0.01</span><span class="p">);</span>

  <span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">setCloudCoherence</span> <span class="p">(</span><span class="n">coherence</span><span class="p">);</span>
</pre></div>
</div>
<p>Here, we set likelihood function which tracker use when calculate weights.  You can add more likelihood function as you like. By default, there are normals likelihood and color likelihood functions. When you want to add other likelihood function, all you have to do is  initialize new Coherence Class and add the Coherence instance to coherence variable with addPointCoherence function.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">//prepare the model of tracker&#39;s target</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector4f</span> <span class="n">c</span><span class="p">;</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Affine3f</span> <span class="n">trans</span> <span class="o">=</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Affine3f</span><span class="o">::</span><span class="n">Identity</span> <span class="p">();</span>
  <span class="n">CloudPtr</span> <span class="nf">transed_ref</span> <span class="p">(</span><span class="k">new</span> <span class="n">Cloud</span><span class="p">);</span>
  <span class="n">CloudPtr</span> <span class="nf">transed_ref_downsampled</span> <span class="p">(</span><span class="k">new</span> <span class="n">Cloud</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">compute3DCentroid</span><span class="o">&lt;</span><span class="n">RefPointType</span><span class="o">&gt;</span> <span class="p">(</span><span class="o">*</span><span class="n">target_cloud</span><span class="p">,</span> <span class="n">c</span><span class="p">);</span>
  <span class="n">trans</span><span class="p">.</span><span class="n">translation</span> <span class="p">().</span><span class="n">matrix</span> <span class="p">()</span> <span class="o">=</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span> <span class="p">(</span><span class="n">c</span><span class="p">[</span><span class="mi">0</span><span class="p">],</span> <span class="n">c</span><span class="p">[</span><span class="mi">1</span><span class="p">],</span> <span class="n">c</span><span class="p">[</span><span class="mi">2</span><span class="p">]);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">transformPointCloud</span><span class="o">&lt;</span><span class="n">RefPointType</span><span class="o">&gt;</span> <span class="p">(</span><span class="o">*</span><span class="n">target_cloud</span><span class="p">,</span> <span class="o">*</span><span class="n">transed_ref</span><span class="p">,</span> <span class="n">trans</span><span class="p">.</span><span class="n">inverse</span><span class="p">());</span>
  <span class="n">gridSampleApprox</span> <span class="p">(</span><span class="n">transed_ref</span><span class="p">,</span> <span class="o">*</span><span class="n">transed_ref_downsampled</span><span class="p">,</span> <span class="n">downsampling_grid_size_</span><span class="p">);</span>

  <span class="c1">//set reference model and trans</span>
  <span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">setReferenceCloud</span> <span class="p">(</span><span class="n">transed_ref_downsampled</span><span class="p">);</span>
  <span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">setTrans</span> <span class="p">(</span><span class="n">trans</span><span class="p">);</span>
</pre></div>
</div>
<p>In this part, we set the point cloud loaded from pcd file as reference model to tracker and also set model&#8217;s transform values.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="k">if</span><span class="p">(</span><span class="n">counter</span> <span class="o">&lt;</span> <span class="mi">10</span><span class="p">){</span>
	<span class="n">counter</span><span class="o">++</span><span class="p">;</span>
  <span class="p">}</span><span class="k">else</span><span class="p">{</span>
  	<span class="c1">//Track the object</span>
	<span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_pass_downsampled_</span><span class="p">);</span>
	<span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">compute</span> <span class="p">();</span>
	<span class="n">new_cloud_</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>Until the counter variable become equal to 10, we ignore the input point cloud, because the point cloud at first few frames often have noise. After counter variable reach to 10 frame, at each loop, we set downsampled input point cloud to tracker and the tracker will compute particles movement.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">ParticleFilter</span><span class="o">::</span><span class="n">PointCloudStatePtr</span> <span class="n">particles</span> <span class="o">=</span> <span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">getParticles</span> <span class="p">();</span>
</pre></div>
</div>
<p>In drawParticles function, you can get particles&#8217;s positions by calling getParticles().</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">ParticleXYZRPY</span> <span class="n">result</span> <span class="o">=</span> <span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">getResult</span> <span class="p">();</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Affine3f</span> <span class="n">transformation</span> <span class="o">=</span> <span class="n">tracker_</span><span class="o">-&gt;</span><span class="n">toEigenMatrix</span> <span class="p">(</span><span class="n">result</span><span class="p">);</span>
</pre></div>
</div>
<p>In drawResult function, you can get model infomation about position and rotation.</p>
</div>
<div class="section" id="compiling-and-running-the-program">
<h1>Compiling and running the program</h1>
<p>Create a CMakeLists.txt file and add the following lines into it.</p>
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

<span class="nb">project</span><span class="p">(</span><span class="s">openni_tracking</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.7</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">tracking_sample</span> <span class="s">tracking_sample.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">tracking_sample</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>If you finish saving CMakeLists.txt, let&#8217;s prepare for running.</p>
<blockquote>
<div><ol class="arabic simple">
<li>Put the target object on a plane where there is nothing.</li>
<li>Put sensor device about 1 meter away from target.</li>
<li>Don&#8217;t move the target and the device until you launch tracking program.</li>
<li>Output only target point cloud with your other code (See <a class="reference internal" href="planar_segmentation.php#planar-segmentation"><em>Plane model segmentation</em></a> tutorial) and save as tracking_target.pcd</li>
</ol>
</div></blockquote>
<p>After you created model point cloud and the executable, you can then launch tracking_sample. Set device_id as second arguement and pcd file&#8217;s name you made in above 4 as third.</p>
<blockquote>
<div>$ ./tracking_sample “#1” tracking_target.pcd</div></blockquote>
<p>After few seconds, tracking will start working and you can move tracking object around. As you can see in following pictures, the blue point cloud is reference model segmentation&#8217;s cloud and the red one is particles&#8217; cloud.</p>
<a class="reference internal image-reference" href="_images/redone.png"><img alt="_images/redone.png" src="_images/redone.png" style="height: 400px;" /></a>
<a class="reference internal image-reference" href="_images/blueone.png"><img alt="_images/blueone.png" src="_images/blueone.png" style="height: 400px;" /></a>
</div>
<div class="section" id="more-advanced">
<h1>More Advanced</h1>
<p>If you want to see more flexible and useful tracking code which starts tracking without preparing to make segemented model beforehand, you should refer a tracking code  <a class="reference external" href="https://github.com/aginika/pcl/blob/master/apps/src/openni_tracking.cpp">https://github.com/aginika/pcl/blob/master/apps/src/openni_tracking.cpp</a>. It will show you better and more legible code. The above Figures  are windows when you implement that code.</p>
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