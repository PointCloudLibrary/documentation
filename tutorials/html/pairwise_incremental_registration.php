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
    
    <title>How to incrementally register pairs of clouds</title>
    
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
            
  <div class="section" id="how-to-incrementally-register-pairs-of-clouds">
<span id="pairwise-incremental-registration"></span><h1>How to incrementally register pairs of clouds</h1>
<p>This document demonstrates using the Iterative Closest Point algorithm in order
to incrementally register a series of point clouds two by two.</p>
<div class="line-block">
<div class="line">The idea is to transform all the clouds in the first cloud&#8217;s frame.</div>
<div class="line">This is done by finding the best transform between each consecutive cloud, and accumulating these transforms over the whole set of clouds.</div>
</div>
<div class="line-block">
<div class="line">Your data set should consist of clouds that have been roughly pre-aligned in a common frame (e.g. in a robot&#8217;s odometry or map frame) and overlap with one another.</div>
<div class="line">We provide a set of clouds at <a class="reference external" href="https://github.com/PointCloudLibrary/data/tree/master/tutorials/pairwise">github.com/PointCloudLibrary/data/tree/master/tutorials/pairwise/</a>.</div>
</div>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
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
309
310
311
312
313
314
315
316
317
318
319
320
321
322
323
324
325
326
327
328
329
330
331
332
333
334
335
336
337
338
339
340
341
342
343
344
345
346
347
348
349
350
351
352
353
354
355
356
357
358
359
360
361
362
363
364
365
366
367
368
369
370
371
372
373
374</pre></div></td><td class="code"><div class="highlight"><pre><span class="cm">/*</span>
<span class="cm"> * Software License Agreement (BSD License)</span>
<span class="cm"> *</span>
<span class="cm"> *  Copyright (c) 2010, Willow Garage, Inc.</span>
<span class="cm"> *  All rights reserved.</span>
<span class="cm"> *</span>
<span class="cm"> *  Redistribution and use in source and binary forms, with or without</span>
<span class="cm"> *  modification, are permitted provided that the following conditions</span>
<span class="cm"> *  are met:</span>
<span class="cm"> *</span>
<span class="cm"> *   * Redistributions of source code must retain the above copyright</span>
<span class="cm"> *     notice, this list of conditions and the following disclaimer.</span>
<span class="cm"> *   * Redistributions in binary form must reproduce the above</span>
<span class="cm"> *     copyright notice, this list of conditions and the following</span>
<span class="cm"> *     disclaimer in the documentation and/or other materials provided</span>
<span class="cm"> *     with the distribution.</span>
<span class="cm"> *   * Neither the name of Willow Garage, Inc. nor the names of its</span>
<span class="cm"> *     contributors may be used to endorse or promote products derived</span>
<span class="cm"> *     from this software without specific prior written permission.</span>
<span class="cm"> *</span>
<span class="cm"> *  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS</span>
<span class="cm"> *  &quot;AS IS&quot; AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT</span>
<span class="cm"> *  LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS</span>
<span class="cm"> *  FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE</span>
<span class="cm"> *  COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,</span>
<span class="cm"> *  INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,</span>
<span class="cm"> *  BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;</span>
<span class="cm"> *  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER</span>
<span class="cm"> *  CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT</span>
<span class="cm"> *  LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN</span>
<span class="cm"> *  ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE</span>
<span class="cm"> *  POSSIBILITY OF SUCH DAMAGE.</span>
<span class="cm"> *</span>
<span class="cm"> * $Id$</span>
<span class="cm"> *</span>
<span class="cm"> */</span>

<span class="cm">/* \author Radu Bogdan Rusu</span>
<span class="cm"> * adaptation Raphael Favier*/</span>

<span class="cp">#include &lt;boost/make_shared.hpp&gt;</span>
<span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/point_cloud.h&gt;</span>
<span class="cp">#include &lt;pcl/point_representation.h&gt;</span>

<span class="cp">#include &lt;pcl/io/pcd_io.h&gt;</span>

<span class="cp">#include &lt;pcl/filters/voxel_grid.h&gt;</span>
<span class="cp">#include &lt;pcl/filters/filter.h&gt;</span>

<span class="cp">#include &lt;pcl/features/normal_3d.h&gt;</span>

<span class="cp">#include &lt;pcl/registration/icp.h&gt;</span>
<span class="cp">#include &lt;pcl/registration/icp_nl.h&gt;</span>
<span class="cp">#include &lt;pcl/registration/transforms.h&gt;</span>

<span class="cp">#include &lt;pcl/visualization/pcl_visualizer.h&gt;</span>

<span class="k">using</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerGenericField</span><span class="p">;</span>
<span class="k">using</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="p">;</span>

<span class="c1">//convenient typedefs</span>
<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">PointT</span><span class="p">;</span>
<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">PointCloud</span><span class="p">;</span>
<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointNormal</span> <span class="n">PointNormalT</span><span class="p">;</span>
<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointNormalT</span><span class="o">&gt;</span> <span class="n">PointCloudWithNormals</span><span class="p">;</span>

<span class="c1">// This is a tutorial so we can afford having global variables </span>
	<span class="c1">//our visualizer</span>
	<span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="o">*</span><span class="n">p</span><span class="p">;</span>
	<span class="c1">//its left and right viewports</span>
	<span class="kt">int</span> <span class="n">vp_1</span><span class="p">,</span> <span class="n">vp_2</span><span class="p">;</span>

<span class="c1">//convenient structure to handle our pointclouds</span>
<span class="k">struct</span> <span class="n">PCD</span>
<span class="p">{</span>
  <span class="n">PointCloud</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">cloud</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">f_name</span><span class="p">;</span>

  <span class="n">PCD</span><span class="p">()</span> <span class="o">:</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">PointCloud</span><span class="p">)</span> <span class="p">{};</span>
<span class="p">};</span>

<span class="k">struct</span> <span class="n">PCDComparator</span>
<span class="p">{</span>
  <span class="kt">bool</span> <span class="k">operator</span> <span class="p">()</span> <span class="p">(</span><span class="k">const</span> <span class="n">PCD</span><span class="o">&amp;</span> <span class="n">p1</span><span class="p">,</span> <span class="k">const</span> <span class="n">PCD</span><span class="o">&amp;</span> <span class="n">p2</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="k">return</span> <span class="p">(</span><span class="n">p1</span><span class="p">.</span><span class="n">f_name</span> <span class="o">&lt;</span> <span class="n">p2</span><span class="p">.</span><span class="n">f_name</span><span class="p">);</span>
  <span class="p">}</span>
<span class="p">};</span>


<span class="c1">// Define a new point representation for &lt; x, y, z, curvature &gt;</span>
<span class="k">class</span> <span class="nc">MyPointRepresentation</span> <span class="o">:</span> <span class="k">public</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointRepresentation</span> <span class="o">&lt;</span><span class="n">PointNormalT</span><span class="o">&gt;</span>
<span class="p">{</span>
  <span class="k">using</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointRepresentation</span><span class="o">&lt;</span><span class="n">PointNormalT</span><span class="o">&gt;::</span><span class="n">nr_dimensions_</span><span class="p">;</span>
<span class="nl">public:</span>
  <span class="n">MyPointRepresentation</span> <span class="p">()</span>
  <span class="p">{</span>
    <span class="c1">// Define the number of dimensions</span>
    <span class="n">nr_dimensions_</span> <span class="o">=</span> <span class="mi">4</span><span class="p">;</span>
  <span class="p">}</span>

  <span class="c1">// Override the copyToFloatArray method to define our feature vector</span>
  <span class="k">virtual</span> <span class="kt">void</span> <span class="n">copyToFloatArray</span> <span class="p">(</span><span class="k">const</span> <span class="n">PointNormalT</span> <span class="o">&amp;</span><span class="n">p</span><span class="p">,</span> <span class="kt">float</span> <span class="o">*</span> <span class="n">out</span><span class="p">)</span> <span class="k">const</span>
  <span class="p">{</span>
    <span class="c1">// &lt; x, y, z, curvature &gt;</span>
    <span class="n">out</span><span class="p">[</span><span class="mi">0</span><span class="p">]</span> <span class="o">=</span> <span class="n">p</span><span class="p">.</span><span class="n">x</span><span class="p">;</span>
    <span class="n">out</span><span class="p">[</span><span class="mi">1</span><span class="p">]</span> <span class="o">=</span> <span class="n">p</span><span class="p">.</span><span class="n">y</span><span class="p">;</span>
    <span class="n">out</span><span class="p">[</span><span class="mi">2</span><span class="p">]</span> <span class="o">=</span> <span class="n">p</span><span class="p">.</span><span class="n">z</span><span class="p">;</span>
    <span class="n">out</span><span class="p">[</span><span class="mi">3</span><span class="p">]</span> <span class="o">=</span> <span class="n">p</span><span class="p">.</span><span class="n">curvature</span><span class="p">;</span>
  <span class="p">}</span>
<span class="p">};</span>


<span class="c1">////////////////////////////////////////////////////////////////////////////////</span>
<span class="cm">/** \brief Display source and target on the first viewport of the visualizer</span>
<span class="cm"> *</span>
<span class="cm"> */</span>
<span class="kt">void</span> <span class="nf">showCloudsLeft</span><span class="p">(</span><span class="k">const</span> <span class="n">PointCloud</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">cloud_target</span><span class="p">,</span> <span class="k">const</span> <span class="n">PointCloud</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">cloud_source</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">p</span><span class="o">-&gt;</span><span class="n">removePointCloud</span> <span class="p">(</span><span class="s">&quot;vp1_target&quot;</span><span class="p">);</span>
  <span class="n">p</span><span class="o">-&gt;</span><span class="n">removePointCloud</span> <span class="p">(</span><span class="s">&quot;vp1_source&quot;</span><span class="p">);</span>

  <span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">tgt_h</span> <span class="p">(</span><span class="n">cloud_target</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">255</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">src_h</span> <span class="p">(</span><span class="n">cloud_source</span><span class="p">,</span> <span class="mi">255</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">p</span><span class="o">-&gt;</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">cloud_target</span><span class="p">,</span> <span class="n">tgt_h</span><span class="p">,</span> <span class="s">&quot;vp1_target&quot;</span><span class="p">,</span> <span class="n">vp_1</span><span class="p">);</span>
  <span class="n">p</span><span class="o">-&gt;</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">cloud_source</span><span class="p">,</span> <span class="n">src_h</span><span class="p">,</span> <span class="s">&quot;vp1_source&quot;</span><span class="p">,</span> <span class="n">vp_1</span><span class="p">);</span>

  <span class="n">PCL_INFO</span> <span class="p">(</span><span class="s">&quot;Press q to begin the registration.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
  <span class="n">p</span><span class="o">-&gt;</span> <span class="n">spin</span><span class="p">();</span>
<span class="p">}</span>


<span class="c1">////////////////////////////////////////////////////////////////////////////////</span>
<span class="cm">/** \brief Display source and target on the second viewport of the visualizer</span>
<span class="cm"> *</span>
<span class="cm"> */</span>
<span class="kt">void</span> <span class="nf">showCloudsRight</span><span class="p">(</span><span class="k">const</span> <span class="n">PointCloudWithNormals</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">cloud_target</span><span class="p">,</span> <span class="k">const</span> <span class="n">PointCloudWithNormals</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">cloud_source</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">p</span><span class="o">-&gt;</span><span class="n">removePointCloud</span> <span class="p">(</span><span class="s">&quot;source&quot;</span><span class="p">);</span>
  <span class="n">p</span><span class="o">-&gt;</span><span class="n">removePointCloud</span> <span class="p">(</span><span class="s">&quot;target&quot;</span><span class="p">);</span>


  <span class="n">PointCloudColorHandlerGenericField</span><span class="o">&lt;</span><span class="n">PointNormalT</span><span class="o">&gt;</span> <span class="n">tgt_color_handler</span> <span class="p">(</span><span class="n">cloud_target</span><span class="p">,</span> <span class="s">&quot;curvature&quot;</span><span class="p">);</span>
  <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">tgt_color_handler</span><span class="p">.</span><span class="n">isCapable</span> <span class="p">())</span>
      <span class="n">PCL_WARN</span> <span class="p">(</span><span class="s">&quot;Cannot create curvature color handler!&quot;</span><span class="p">);</span>

  <span class="n">PointCloudColorHandlerGenericField</span><span class="o">&lt;</span><span class="n">PointNormalT</span><span class="o">&gt;</span> <span class="n">src_color_handler</span> <span class="p">(</span><span class="n">cloud_source</span><span class="p">,</span> <span class="s">&quot;curvature&quot;</span><span class="p">);</span>
  <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">src_color_handler</span><span class="p">.</span><span class="n">isCapable</span> <span class="p">())</span>
      <span class="n">PCL_WARN</span> <span class="p">(</span><span class="s">&quot;Cannot create curvature color handler!&quot;</span><span class="p">);</span>


  <span class="n">p</span><span class="o">-&gt;</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">cloud_target</span><span class="p">,</span> <span class="n">tgt_color_handler</span><span class="p">,</span> <span class="s">&quot;target&quot;</span><span class="p">,</span> <span class="n">vp_2</span><span class="p">);</span>
  <span class="n">p</span><span class="o">-&gt;</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">cloud_source</span><span class="p">,</span> <span class="n">src_color_handler</span><span class="p">,</span> <span class="s">&quot;source&quot;</span><span class="p">,</span> <span class="n">vp_2</span><span class="p">);</span>

  <span class="n">p</span><span class="o">-&gt;</span><span class="n">spinOnce</span><span class="p">();</span>
<span class="p">}</span>

<span class="c1">////////////////////////////////////////////////////////////////////////////////</span>
<span class="cm">/** \brief Load a set of PCD files that we want to register together</span>
<span class="cm">  * \param argc the number of arguments (pass from main ())</span>
<span class="cm">  * \param argv the actual command line arguments (pass from main ())</span>
<span class="cm">  * \param models the resultant vector of point cloud datasets</span>
<span class="cm">  */</span>
<span class="kt">void</span> <span class="nf">loadData</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span> <span class="o">**</span><span class="n">argv</span><span class="p">,</span> <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">PCD</span><span class="p">,</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">aligned_allocator</span><span class="o">&lt;</span><span class="n">PCD</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="o">&amp;</span><span class="n">models</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">extension</span> <span class="p">(</span><span class="s">&quot;.pcd&quot;</span><span class="p">);</span>
  <span class="c1">// Suppose the first argument is the actual test model</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">int</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">argc</span><span class="p">;</span> <span class="n">i</span><span class="o">++</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">fname</span> <span class="o">=</span> <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="n">i</span><span class="p">]);</span>
    <span class="c1">// Needs to be at least 5: .plot</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">fname</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;=</span> <span class="n">extension</span><span class="p">.</span><span class="n">size</span> <span class="p">())</span>
      <span class="k">continue</span><span class="p">;</span>

    <span class="n">std</span><span class="o">::</span><span class="n">transform</span> <span class="p">(</span><span class="n">fname</span><span class="p">.</span><span class="n">begin</span> <span class="p">(),</span> <span class="n">fname</span><span class="p">.</span><span class="n">end</span> <span class="p">(),</span> <span class="n">fname</span><span class="p">.</span><span class="n">begin</span> <span class="p">(),</span> <span class="p">(</span><span class="kt">int</span><span class="p">(</span><span class="o">*</span><span class="p">)(</span><span class="kt">int</span><span class="p">))</span><span class="n">tolower</span><span class="p">);</span>

    <span class="c1">//check that the argument is a pcd file</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">fname</span><span class="p">.</span><span class="n">compare</span> <span class="p">(</span><span class="n">fname</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">-</span> <span class="n">extension</span><span class="p">.</span><span class="n">size</span> <span class="p">(),</span> <span class="n">extension</span><span class="p">.</span><span class="n">size</span> <span class="p">(),</span> <span class="n">extension</span><span class="p">)</span> <span class="o">==</span> <span class="mi">0</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="c1">// Load the cloud and saves it into the global list of models</span>
      <span class="n">PCD</span> <span class="n">m</span><span class="p">;</span>
      <span class="n">m</span><span class="p">.</span><span class="n">f_name</span> <span class="o">=</span> <span class="n">argv</span><span class="p">[</span><span class="n">i</span><span class="p">];</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="n">i</span><span class="p">],</span> <span class="o">*</span><span class="n">m</span><span class="p">.</span><span class="n">cloud</span><span class="p">);</span>
      <span class="c1">//remove NAN points from the cloud</span>
      <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">indices</span><span class="p">;</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">removeNaNFromPointCloud</span><span class="p">(</span><span class="o">*</span><span class="n">m</span><span class="p">.</span><span class="n">cloud</span><span class="p">,</span><span class="o">*</span><span class="n">m</span><span class="p">.</span><span class="n">cloud</span><span class="p">,</span> <span class="n">indices</span><span class="p">);</span>

      <span class="n">models</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">m</span><span class="p">);</span>
    <span class="p">}</span>
  <span class="p">}</span>
<span class="p">}</span>


<span class="c1">////////////////////////////////////////////////////////////////////////////////</span>
<span class="cm">/** \brief Align a pair of PointCloud datasets and return the result</span>
<span class="cm">  * \param cloud_src the source PointCloud</span>
<span class="cm">  * \param cloud_tgt the target PointCloud</span>
<span class="cm">  * \param output the resultant aligned source PointCloud</span>
<span class="cm">  * \param final_transform the resultant transform between source and target</span>
<span class="cm">  */</span>
<span class="kt">void</span> <span class="nf">pairAlign</span> <span class="p">(</span><span class="k">const</span> <span class="n">PointCloud</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">cloud_src</span><span class="p">,</span> <span class="k">const</span> <span class="n">PointCloud</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">cloud_tgt</span><span class="p">,</span> <span class="n">PointCloud</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">output</span><span class="p">,</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span> <span class="o">&amp;</span><span class="n">final_transform</span><span class="p">,</span> <span class="kt">bool</span> <span class="n">downsample</span> <span class="o">=</span> <span class="nb">false</span><span class="p">)</span>
<span class="p">{</span>
  <span class="c1">//</span>
  <span class="c1">// Downsample for consistency and speed</span>
  <span class="c1">// \note enable this for large datasets</span>
  <span class="n">PointCloud</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">src</span> <span class="p">(</span><span class="k">new</span> <span class="n">PointCloud</span><span class="p">);</span>
  <span class="n">PointCloud</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">tgt</span> <span class="p">(</span><span class="k">new</span> <span class="n">PointCloud</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">VoxelGrid</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">grid</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">downsample</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">grid</span><span class="p">.</span><span class="n">setLeafSize</span> <span class="p">(</span><span class="mf">0.05</span><span class="p">,</span> <span class="mf">0.05</span><span class="p">,</span> <span class="mf">0.05</span><span class="p">);</span>
    <span class="n">grid</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_src</span><span class="p">);</span>
    <span class="n">grid</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">src</span><span class="p">);</span>

    <span class="n">grid</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_tgt</span><span class="p">);</span>
    <span class="n">grid</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">tgt</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="k">else</span>
  <span class="p">{</span>
    <span class="n">src</span> <span class="o">=</span> <span class="n">cloud_src</span><span class="p">;</span>
    <span class="n">tgt</span> <span class="o">=</span> <span class="n">cloud_tgt</span><span class="p">;</span>
  <span class="p">}</span>


  <span class="c1">// Compute surface normals and curvature</span>
  <span class="n">PointCloudWithNormals</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">points_with_normals_src</span> <span class="p">(</span><span class="k">new</span> <span class="n">PointCloudWithNormals</span><span class="p">);</span>
  <span class="n">PointCloudWithNormals</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">points_with_normals_tgt</span> <span class="p">(</span><span class="k">new</span> <span class="n">PointCloudWithNormals</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">NormalEstimation</span><span class="o">&lt;</span><span class="n">PointT</span><span class="p">,</span> <span class="n">PointNormalT</span><span class="o">&gt;</span> <span class="n">norm_est</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">tree</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">norm_est</span><span class="p">.</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">tree</span><span class="p">);</span>
  <span class="n">norm_est</span><span class="p">.</span><span class="n">setKSearch</span> <span class="p">(</span><span class="mi">30</span><span class="p">);</span>
  
  <span class="n">norm_est</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">src</span><span class="p">);</span>
  <span class="n">norm_est</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">points_with_normals_src</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">copyPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">src</span><span class="p">,</span> <span class="o">*</span><span class="n">points_with_normals_src</span><span class="p">);</span>

  <span class="n">norm_est</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">tgt</span><span class="p">);</span>
  <span class="n">norm_est</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">points_with_normals_tgt</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">copyPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">tgt</span><span class="p">,</span> <span class="o">*</span><span class="n">points_with_normals_tgt</span><span class="p">);</span>

  <span class="c1">//</span>
  <span class="c1">// Instantiate our custom point representation (defined above) ...</span>
  <span class="n">MyPointRepresentation</span> <span class="n">point_representation</span><span class="p">;</span>
  <span class="c1">// ... and weight the &#39;curvature&#39; dimension so that it is balanced against x, y, and z</span>
  <span class="kt">float</span> <span class="n">alpha</span><span class="p">[</span><span class="mi">4</span><span class="p">]</span> <span class="o">=</span> <span class="p">{</span><span class="mf">1.0</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">};</span>
  <span class="n">point_representation</span><span class="p">.</span><span class="n">setRescaleValues</span> <span class="p">(</span><span class="n">alpha</span><span class="p">);</span>

  <span class="c1">//</span>
  <span class="c1">// Align</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">IterativeClosestPointNonLinear</span><span class="o">&lt;</span><span class="n">PointNormalT</span><span class="p">,</span> <span class="n">PointNormalT</span><span class="o">&gt;</span> <span class="n">reg</span><span class="p">;</span>
  <span class="n">reg</span><span class="p">.</span><span class="n">setTransformationEpsilon</span> <span class="p">(</span><span class="mf">1e-6</span><span class="p">);</span>
  <span class="c1">// Set the maximum distance between two correspondences (src&lt;-&gt;tgt) to 10cm</span>
  <span class="c1">// Note: adjust this based on the size of your datasets</span>
  <span class="n">reg</span><span class="p">.</span><span class="n">setMaxCorrespondenceDistance</span> <span class="p">(</span><span class="mf">0.1</span><span class="p">);</span>  
  <span class="c1">// Set the point representation</span>
  <span class="n">reg</span><span class="p">.</span><span class="n">setPointRepresentation</span> <span class="p">(</span><span class="n">boost</span><span class="o">::</span><span class="n">make_shared</span><span class="o">&lt;</span><span class="k">const</span> <span class="n">MyPointRepresentation</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">point_representation</span><span class="p">));</span>

  <span class="n">reg</span><span class="p">.</span><span class="n">setInputSource</span> <span class="p">(</span><span class="n">points_with_normals_src</span><span class="p">);</span>
  <span class="n">reg</span><span class="p">.</span><span class="n">setInputTarget</span> <span class="p">(</span><span class="n">points_with_normals_tgt</span><span class="p">);</span>



  <span class="c1">//</span>
  <span class="c1">// Run the same optimization in a loop and visualize the results</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span> <span class="n">Ti</span> <span class="o">=</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span><span class="o">::</span><span class="n">Identity</span> <span class="p">(),</span> <span class="n">prev</span><span class="p">,</span> <span class="n">targetToSource</span><span class="p">;</span>
  <span class="n">PointCloudWithNormals</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">reg_result</span> <span class="o">=</span> <span class="n">points_with_normals_src</span><span class="p">;</span>
  <span class="n">reg</span><span class="p">.</span><span class="n">setMaximumIterations</span> <span class="p">(</span><span class="mi">2</span><span class="p">);</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">int</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="mi">30</span><span class="p">;</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">PCL_INFO</span> <span class="p">(</span><span class="s">&quot;Iteration Nr. %d.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">i</span><span class="p">);</span>

    <span class="c1">// save cloud for visualization purpose</span>
    <span class="n">points_with_normals_src</span> <span class="o">=</span> <span class="n">reg_result</span><span class="p">;</span>

    <span class="c1">// Estimate</span>
    <span class="n">reg</span><span class="p">.</span><span class="n">setInputSource</span> <span class="p">(</span><span class="n">points_with_normals_src</span><span class="p">);</span>
    <span class="n">reg</span><span class="p">.</span><span class="n">align</span> <span class="p">(</span><span class="o">*</span><span class="n">reg_result</span><span class="p">);</span>

		<span class="c1">//accumulate transformation between each Iteration</span>
    <span class="n">Ti</span> <span class="o">=</span> <span class="n">reg</span><span class="p">.</span><span class="n">getFinalTransformation</span> <span class="p">()</span> <span class="o">*</span> <span class="n">Ti</span><span class="p">;</span>

		<span class="c1">//if the difference between this transformation and the previous one</span>
		<span class="c1">//is smaller than the threshold, refine the process by reducing</span>
		<span class="c1">//the maximal correspondence distance</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">fabs</span> <span class="p">((</span><span class="n">reg</span><span class="p">.</span><span class="n">getLastIncrementalTransformation</span> <span class="p">()</span> <span class="o">-</span> <span class="n">prev</span><span class="p">).</span><span class="n">sum</span> <span class="p">())</span> <span class="o">&lt;</span> <span class="n">reg</span><span class="p">.</span><span class="n">getTransformationEpsilon</span> <span class="p">())</span>
      <span class="n">reg</span><span class="p">.</span><span class="n">setMaxCorrespondenceDistance</span> <span class="p">(</span><span class="n">reg</span><span class="p">.</span><span class="n">getMaxCorrespondenceDistance</span> <span class="p">()</span> <span class="o">-</span> <span class="mf">0.001</span><span class="p">);</span>
    
    <span class="n">prev</span> <span class="o">=</span> <span class="n">reg</span><span class="p">.</span><span class="n">getLastIncrementalTransformation</span> <span class="p">();</span>

    <span class="c1">// visualize current state</span>
    <span class="n">showCloudsRight</span><span class="p">(</span><span class="n">points_with_normals_tgt</span><span class="p">,</span> <span class="n">points_with_normals_src</span><span class="p">);</span>
  <span class="p">}</span>

	<span class="c1">//</span>
  <span class="c1">// Get the transformation from target to source</span>
  <span class="n">targetToSource</span> <span class="o">=</span> <span class="n">Ti</span><span class="p">.</span><span class="n">inverse</span><span class="p">();</span>

  <span class="c1">//</span>
  <span class="c1">// Transform target back in source frame</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">transformPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_tgt</span><span class="p">,</span> <span class="o">*</span><span class="n">output</span><span class="p">,</span> <span class="n">targetToSource</span><span class="p">);</span>

  <span class="n">p</span><span class="o">-&gt;</span><span class="n">removePointCloud</span> <span class="p">(</span><span class="s">&quot;source&quot;</span><span class="p">);</span>
  <span class="n">p</span><span class="o">-&gt;</span><span class="n">removePointCloud</span> <span class="p">(</span><span class="s">&quot;target&quot;</span><span class="p">);</span>

  <span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">cloud_tgt_h</span> <span class="p">(</span><span class="n">output</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">255</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">cloud_src_h</span> <span class="p">(</span><span class="n">cloud_src</span><span class="p">,</span> <span class="mi">255</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">p</span><span class="o">-&gt;</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">output</span><span class="p">,</span> <span class="n">cloud_tgt_h</span><span class="p">,</span> <span class="s">&quot;target&quot;</span><span class="p">,</span> <span class="n">vp_2</span><span class="p">);</span>
  <span class="n">p</span><span class="o">-&gt;</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">cloud_src</span><span class="p">,</span> <span class="n">cloud_src_h</span><span class="p">,</span> <span class="s">&quot;source&quot;</span><span class="p">,</span> <span class="n">vp_2</span><span class="p">);</span>

	<span class="n">PCL_INFO</span> <span class="p">(</span><span class="s">&quot;Press q to continue the registration.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
  <span class="n">p</span><span class="o">-&gt;</span><span class="n">spin</span> <span class="p">();</span>

  <span class="n">p</span><span class="o">-&gt;</span><span class="n">removePointCloud</span> <span class="p">(</span><span class="s">&quot;source&quot;</span><span class="p">);</span> 
  <span class="n">p</span><span class="o">-&gt;</span><span class="n">removePointCloud</span> <span class="p">(</span><span class="s">&quot;target&quot;</span><span class="p">);</span>

  <span class="c1">//add the source to the transformed target</span>
  <span class="o">*</span><span class="n">output</span> <span class="o">+=</span> <span class="o">*</span><span class="n">cloud_src</span><span class="p">;</span>
  
  <span class="n">final_transform</span> <span class="o">=</span> <span class="n">targetToSource</span><span class="p">;</span>
 <span class="p">}</span>


<span class="cm">/* ---[ */</span>
<span class="kt">int</span> <span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="c1">// Load data</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">PCD</span><span class="p">,</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">aligned_allocator</span><span class="o">&lt;</span><span class="n">PCD</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="n">data</span><span class="p">;</span>
  <span class="n">loadData</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="n">data</span><span class="p">);</span>

  <span class="c1">// Check user input</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">data</span><span class="p">.</span><span class="n">empty</span> <span class="p">())</span>
  <span class="p">{</span>
    <span class="n">PCL_ERROR</span> <span class="p">(</span><span class="s">&quot;Syntax is: %s &lt;source.pcd&gt; &lt;target.pcd&gt; [*]&quot;</span><span class="p">,</span> <span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
    <span class="n">PCL_ERROR</span> <span class="p">(</span><span class="s">&quot;[*] - multiple files can be added. The registration results of (i, i+1) will be registered against (i+2), etc&quot;</span><span class="p">);</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="n">PCL_INFO</span> <span class="p">(</span><span class="s">&quot;Loaded %d datasets.&quot;</span><span class="p">,</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span><span class="n">data</span><span class="p">.</span><span class="n">size</span> <span class="p">());</span>
  
  <span class="c1">// Create a PCLVisualizer object</span>
  <span class="n">p</span> <span class="o">=</span> <span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;Pairwise Incremental Registration example&quot;</span><span class="p">);</span>
  <span class="n">p</span><span class="o">-&gt;</span><span class="n">createViewPort</span> <span class="p">(</span><span class="mf">0.0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mf">0.5</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">,</span> <span class="n">vp_1</span><span class="p">);</span>
  <span class="n">p</span><span class="o">-&gt;</span><span class="n">createViewPort</span> <span class="p">(</span><span class="mf">0.5</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">,</span> <span class="n">vp_2</span><span class="p">);</span>

	<span class="n">PointCloud</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">result</span> <span class="p">(</span><span class="k">new</span> <span class="n">PointCloud</span><span class="p">),</span> <span class="n">source</span><span class="p">,</span> <span class="n">target</span><span class="p">;</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span> <span class="n">GlobalTransform</span> <span class="o">=</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span><span class="o">::</span><span class="n">Identity</span> <span class="p">(),</span> <span class="n">pairTransform</span><span class="p">;</span>
  
  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">data</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">source</span> <span class="o">=</span> <span class="n">data</span><span class="p">[</span><span class="n">i</span><span class="o">-</span><span class="mi">1</span><span class="p">].</span><span class="n">cloud</span><span class="p">;</span>
    <span class="n">target</span> <span class="o">=</span> <span class="n">data</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">cloud</span><span class="p">;</span>

    <span class="c1">// Add visualization data</span>
    <span class="n">showCloudsLeft</span><span class="p">(</span><span class="n">source</span><span class="p">,</span> <span class="n">target</span><span class="p">);</span>

    <span class="n">PointCloud</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">temp</span> <span class="p">(</span><span class="k">new</span> <span class="n">PointCloud</span><span class="p">);</span>
    <span class="n">PCL_INFO</span> <span class="p">(</span><span class="s">&quot;Aligning %s (%d) with %s (%d).</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">data</span><span class="p">[</span><span class="n">i</span><span class="o">-</span><span class="mi">1</span><span class="p">].</span><span class="n">f_name</span><span class="p">.</span><span class="n">c_str</span> <span class="p">(),</span> <span class="n">source</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">(),</span> <span class="n">data</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">f_name</span><span class="p">.</span><span class="n">c_str</span> <span class="p">(),</span> <span class="n">target</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">());</span>
    <span class="n">pairAlign</span> <span class="p">(</span><span class="n">source</span><span class="p">,</span> <span class="n">target</span><span class="p">,</span> <span class="n">temp</span><span class="p">,</span> <span class="n">pairTransform</span><span class="p">,</span> <span class="nb">true</span><span class="p">);</span>

    <span class="c1">//transform current pair into the global transform</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">transformPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">temp</span><span class="p">,</span> <span class="o">*</span><span class="n">result</span><span class="p">,</span> <span class="n">GlobalTransform</span><span class="p">);</span>

    <span class="c1">//update the global transform</span>
    <span class="n">GlobalTransform</span> <span class="o">=</span> <span class="n">GlobalTransform</span> <span class="o">*</span> <span class="n">pairTransform</span><span class="p">;</span>

		<span class="c1">//save aligned pair, transformed into the first cloud&#39;s frame</span>
    <span class="n">std</span><span class="o">::</span><span class="n">stringstream</span> <span class="n">ss</span><span class="p">;</span>
    <span class="n">ss</span> <span class="o">&lt;&lt;</span> <span class="n">i</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;.pcd&quot;</span><span class="p">;</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">savePCDFile</span> <span class="p">(</span><span class="n">ss</span><span class="p">.</span><span class="n">str</span> <span class="p">(),</span> <span class="o">*</span><span class="n">result</span><span class="p">,</span> <span class="nb">true</span><span class="p">);</span>

  <span class="p">}</span>
<span class="p">}</span>
<span class="cm">/* ]--- */</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<div class="line-block">
<div class="line">Let&#8217;s breakdown this code piece by piece.</div>
<div class="line">We will first make a quick run through the declarations. Then, we will study the registering functions.</div>
</div>
<div class="section" id="declarations">
<h2>Declarations</h2>
<p>These are the header files that contain the definitions for all of the classes which we will use.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="cp">#include &lt;boost/make_shared.hpp&gt;</span>
<span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/point_cloud.h&gt;</span>
<span class="cp">#include &lt;pcl/point_representation.h&gt;</span>

<span class="cp">#include &lt;pcl/io/pcd_io.h&gt;</span>

<span class="cp">#include &lt;pcl/filters/voxel_grid.h&gt;</span>
<span class="cp">#include &lt;pcl/filters/filter.h&gt;</span>

<span class="cp">#include &lt;pcl/features/normal_3d.h&gt;</span>

<span class="cp">#include &lt;pcl/registration/icp.h&gt;</span>
<span class="cp">#include &lt;pcl/registration/icp_nl.h&gt;</span>
<span class="cp">#include &lt;pcl/registration/transforms.h&gt;</span>

<span class="cp">#include &lt;pcl/visualization/pcl_visualizer.h&gt;</span>
</pre></div>
</div>
<p>Creates global variables for visualization purpose</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="c1">// This is a tutorial so we can afford having global variables </span>
	<span class="c1">//our visualizer</span>
	<span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="o">*</span><span class="n">p</span><span class="p">;</span>
	<span class="c1">//its left and right viewports</span>
	<span class="kt">int</span> <span class="n">vp_1</span><span class="p">,</span> <span class="n">vp_2</span><span class="p">;</span>
</pre></div>
</div>
<p>Declare a convenient structure that allow us to handle clouds as couple [points - filename]</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">struct</span> <span class="n">PCD</span>
<span class="p">{</span>
  <span class="n">PointCloud</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">cloud</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">f_name</span><span class="p">;</span>

  <span class="n">PCD</span><span class="p">()</span> <span class="o">:</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">PointCloud</span><span class="p">)</span> <span class="p">{};</span>
<span class="p">};</span>
</pre></div>
</div>
<p>Define a new point representation (see <a class="reference internal" href="adding_custom_ptype.php#adding-custom-ptype"><em>Adding your own custom PointT type</em></a> for more on the subject)</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="c1">// Define a new point representation for &lt; x, y, z, curvature &gt;</span>
<span class="k">class</span> <span class="nc">MyPointRepresentation</span> <span class="o">:</span> <span class="k">public</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointRepresentation</span> <span class="o">&lt;</span><span class="n">PointNormalT</span><span class="o">&gt;</span>
<span class="p">{</span>
  <span class="k">using</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointRepresentation</span><span class="o">&lt;</span><span class="n">PointNormalT</span><span class="o">&gt;::</span><span class="n">nr_dimensions_</span><span class="p">;</span>
<span class="nl">public:</span>
  <span class="n">MyPointRepresentation</span> <span class="p">()</span>
  <span class="p">{</span>
    <span class="c1">// Define the number of dimensions</span>
    <span class="n">nr_dimensions_</span> <span class="o">=</span> <span class="mi">4</span><span class="p">;</span>
  <span class="p">}</span>

  <span class="c1">// Override the copyToFloatArray method to define our feature vector</span>
  <span class="k">virtual</span> <span class="kt">void</span> <span class="n">copyToFloatArray</span> <span class="p">(</span><span class="k">const</span> <span class="n">PointNormalT</span> <span class="o">&amp;</span><span class="n">p</span><span class="p">,</span> <span class="kt">float</span> <span class="o">*</span> <span class="n">out</span><span class="p">)</span> <span class="k">const</span>
  <span class="p">{</span>
    <span class="c1">// &lt; x, y, z, curvature &gt;</span>
    <span class="n">out</span><span class="p">[</span><span class="mi">0</span><span class="p">]</span> <span class="o">=</span> <span class="n">p</span><span class="p">.</span><span class="n">x</span><span class="p">;</span>
    <span class="n">out</span><span class="p">[</span><span class="mi">1</span><span class="p">]</span> <span class="o">=</span> <span class="n">p</span><span class="p">.</span><span class="n">y</span><span class="p">;</span>
    <span class="n">out</span><span class="p">[</span><span class="mi">2</span><span class="p">]</span> <span class="o">=</span> <span class="n">p</span><span class="p">.</span><span class="n">z</span><span class="p">;</span>
    <span class="n">out</span><span class="p">[</span><span class="mi">3</span><span class="p">]</span> <span class="o">=</span> <span class="n">p</span><span class="p">.</span><span class="n">curvature</span><span class="p">;</span>
  <span class="p">}</span>
<span class="p">};</span>
</pre></div>
</div>
</div>
<div class="section" id="registering-functions">
<h2>Registering functions</h2>
<p>Let&#8217;s see how are our functions organized.</p>
<div class="line-block">
<div class="line">The main function checks the user input, loads the data in a vector and starts the pair-registration process..</div>
<div class="line">After a transform is found for a pair, the pair is transformed into the first cloud&#8217;s frame, and the global transformation is updated.</div>
</div>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">int</span> <span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="c1">// Load data</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">PCD</span><span class="p">,</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">aligned_allocator</span><span class="o">&lt;</span><span class="n">PCD</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="n">data</span><span class="p">;</span>
  <span class="n">loadData</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="n">data</span><span class="p">);</span>

  <span class="c1">// Check user input</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">data</span><span class="p">.</span><span class="n">empty</span> <span class="p">())</span>
  <span class="p">{</span>
    <span class="n">PCL_ERROR</span> <span class="p">(</span><span class="s">&quot;Syntax is: %s &lt;source.pcd&gt; &lt;target.pcd&gt; [*]&quot;</span><span class="p">,</span> <span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
    <span class="n">PCL_ERROR</span> <span class="p">(</span><span class="s">&quot;[*] - multiple files can be added. The registration results of (i, i+1) will be registered against (i+2), etc&quot;</span><span class="p">);</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="n">PCL_INFO</span> <span class="p">(</span><span class="s">&quot;Loaded %d datasets.&quot;</span><span class="p">,</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span><span class="n">data</span><span class="p">.</span><span class="n">size</span> <span class="p">());</span>
  
  <span class="c1">// Create a PCLVisualizer object</span>
  <span class="n">p</span> <span class="o">=</span> <span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;Pairwise Incremental Registration example&quot;</span><span class="p">);</span>
  <span class="n">p</span><span class="o">-&gt;</span><span class="n">createViewPort</span> <span class="p">(</span><span class="mf">0.0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mf">0.5</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">,</span> <span class="n">vp_1</span><span class="p">);</span>
  <span class="n">p</span><span class="o">-&gt;</span><span class="n">createViewPort</span> <span class="p">(</span><span class="mf">0.5</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">,</span> <span class="n">vp_2</span><span class="p">);</span>

	<span class="n">PointCloud</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">result</span> <span class="p">(</span><span class="k">new</span> <span class="n">PointCloud</span><span class="p">),</span> <span class="n">source</span><span class="p">,</span> <span class="n">target</span><span class="p">;</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span> <span class="n">GlobalTransform</span> <span class="o">=</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span><span class="o">::</span><span class="n">Identity</span> <span class="p">(),</span> <span class="n">pairTransform</span><span class="p">;</span>
  
  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">data</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">source</span> <span class="o">=</span> <span class="n">data</span><span class="p">[</span><span class="n">i</span><span class="o">-</span><span class="mi">1</span><span class="p">].</span><span class="n">cloud</span><span class="p">;</span>
    <span class="n">target</span> <span class="o">=</span> <span class="n">data</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">cloud</span><span class="p">;</span>

    <span class="c1">// Add visualization data</span>
    <span class="n">showCloudsLeft</span><span class="p">(</span><span class="n">source</span><span class="p">,</span> <span class="n">target</span><span class="p">);</span>

    <span class="n">PointCloud</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">temp</span> <span class="p">(</span><span class="k">new</span> <span class="n">PointCloud</span><span class="p">);</span>
    <span class="n">PCL_INFO</span> <span class="p">(</span><span class="s">&quot;Aligning %s (%d) with %s (%d).</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">data</span><span class="p">[</span><span class="n">i</span><span class="o">-</span><span class="mi">1</span><span class="p">].</span><span class="n">f_name</span><span class="p">.</span><span class="n">c_str</span> <span class="p">(),</span> <span class="n">source</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">(),</span> <span class="n">data</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">f_name</span><span class="p">.</span><span class="n">c_str</span> <span class="p">(),</span> <span class="n">target</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">());</span>
    <span class="n">pairAlign</span> <span class="p">(</span><span class="n">source</span><span class="p">,</span> <span class="n">target</span><span class="p">,</span> <span class="n">temp</span><span class="p">,</span> <span class="n">pairTransform</span><span class="p">,</span> <span class="nb">true</span><span class="p">);</span>

    <span class="c1">//transform current pair into the global transform</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">transformPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">temp</span><span class="p">,</span> <span class="o">*</span><span class="n">result</span><span class="p">,</span> <span class="n">GlobalTransform</span><span class="p">);</span>

    <span class="c1">//update the global transform</span>
    <span class="n">GlobalTransform</span> <span class="o">=</span> <span class="n">GlobalTransform</span> <span class="o">*</span> <span class="n">pairTransform</span><span class="p">;</span>

		<span class="c1">//save aligned pair, transformed into the first cloud&#39;s frame</span>
    <span class="n">std</span><span class="o">::</span><span class="n">stringstream</span> <span class="n">ss</span><span class="p">;</span>
    <span class="n">ss</span> <span class="o">&lt;&lt;</span> <span class="n">i</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;.pcd&quot;</span><span class="p">;</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">savePCDFile</span> <span class="p">(</span><span class="n">ss</span><span class="p">.</span><span class="n">str</span> <span class="p">(),</span> <span class="o">*</span><span class="n">result</span><span class="p">,</span> <span class="nb">true</span><span class="p">);</span>

  <span class="p">}</span>
<span class="p">}</span>
<span class="cm">/* ]--- */</span>
</pre></div>
</div>
<div class="line-block">
<div class="line">Loading data is pretty straightforward. We iterate other the program&#8217;s arguments.</div>
<div class="line">For each argument, we check if it links to a pcd file. If so, we create a PCD object that is added to the vector of clouds.</div>
</div>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">void</span> <span class="nf">loadData</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span> <span class="o">**</span><span class="n">argv</span><span class="p">,</span> <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">PCD</span><span class="p">,</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">aligned_allocator</span><span class="o">&lt;</span><span class="n">PCD</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="o">&amp;</span><span class="n">models</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">extension</span> <span class="p">(</span><span class="s">&quot;.pcd&quot;</span><span class="p">);</span>
  <span class="c1">// Suppose the first argument is the actual test model</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">int</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">argc</span><span class="p">;</span> <span class="n">i</span><span class="o">++</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">fname</span> <span class="o">=</span> <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="n">i</span><span class="p">]);</span>
    <span class="c1">// Needs to be at least 5: .plot</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">fname</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;=</span> <span class="n">extension</span><span class="p">.</span><span class="n">size</span> <span class="p">())</span>
      <span class="k">continue</span><span class="p">;</span>

    <span class="n">std</span><span class="o">::</span><span class="n">transform</span> <span class="p">(</span><span class="n">fname</span><span class="p">.</span><span class="n">begin</span> <span class="p">(),</span> <span class="n">fname</span><span class="p">.</span><span class="n">end</span> <span class="p">(),</span> <span class="n">fname</span><span class="p">.</span><span class="n">begin</span> <span class="p">(),</span> <span class="p">(</span><span class="kt">int</span><span class="p">(</span><span class="o">*</span><span class="p">)(</span><span class="kt">int</span><span class="p">))</span><span class="n">tolower</span><span class="p">);</span>

    <span class="c1">//check that the argument is a pcd file</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">fname</span><span class="p">.</span><span class="n">compare</span> <span class="p">(</span><span class="n">fname</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">-</span> <span class="n">extension</span><span class="p">.</span><span class="n">size</span> <span class="p">(),</span> <span class="n">extension</span><span class="p">.</span><span class="n">size</span> <span class="p">(),</span> <span class="n">extension</span><span class="p">)</span> <span class="o">==</span> <span class="mi">0</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="c1">// Load the cloud and saves it into the global list of models</span>
      <span class="n">PCD</span> <span class="n">m</span><span class="p">;</span>
      <span class="n">m</span><span class="p">.</span><span class="n">f_name</span> <span class="o">=</span> <span class="n">argv</span><span class="p">[</span><span class="n">i</span><span class="p">];</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="n">i</span><span class="p">],</span> <span class="o">*</span><span class="n">m</span><span class="p">.</span><span class="n">cloud</span><span class="p">);</span>
      <span class="c1">//remove NAN points from the cloud</span>
      <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">indices</span><span class="p">;</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">removeNaNFromPointCloud</span><span class="p">(</span><span class="o">*</span><span class="n">m</span><span class="p">.</span><span class="n">cloud</span><span class="p">,</span><span class="o">*</span><span class="n">m</span><span class="p">.</span><span class="n">cloud</span><span class="p">,</span> <span class="n">indices</span><span class="p">);</span>

      <span class="n">models</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">m</span><span class="p">);</span>
    <span class="p">}</span>
  <span class="p">}</span>
<span class="p">}</span>
</pre></div>
</div>
<p>We now arrive to the actual pair registration.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">void</span> <span class="n">pairAlign</span> <span class="p">(</span><span class="k">const</span> <span class="n">PointCloud</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">cloud_src</span><span class="p">,</span> <span class="k">const</span> <span class="n">PointCloud</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">cloud_tgt</span><span class="p">,</span> <span class="n">PointCloud</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">output</span><span class="p">,</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span> <span class="o">&amp;</span><span class="n">final_transform</span><span class="p">,</span> <span class="kt">bool</span> <span class="n">downsample</span> <span class="o">=</span> <span class="nb">false</span><span class="p">)</span>
</pre></div>
</div>
<div class="line-block">
<div class="line">First, we optionally down sample our clouds. This is useful in the case of large datasets. Curvature are then computed (for visualization purpose).</div>
<div class="line">We then create the ICP object, set its parameters and link it to the two clouds we wish to align. Remember to adapt these to your own datasets.</div>
</div>
<blockquote>
<div><div class="highlight-cpp"><div class="highlight"><pre><span class="c1">// Align</span>
<span class="n">pcl</span><span class="o">::</span><span class="n">IterativeClosestPointNonLinear</span><span class="o">&lt;</span><span class="n">PointNormalT</span><span class="p">,</span> <span class="n">PointNormalT</span><span class="o">&gt;</span> <span class="n">reg</span><span class="p">;</span>
<span class="n">reg</span><span class="p">.</span><span class="n">setTransformationEpsilon</span> <span class="p">(</span><span class="mf">1e-6</span><span class="p">);</span>
<span class="c1">// Set the maximum distance between two correspondences (src&lt;-&gt;tgt) to 10cm</span>
<span class="c1">// Note: adjust this based on the size of your datasets</span>
<span class="n">reg</span><span class="p">.</span><span class="n">setMaxCorrespondenceDistance</span> <span class="p">(</span><span class="mf">0.1</span><span class="p">);</span>
<span class="c1">// Set the point representation</span>
<span class="n">reg</span><span class="p">.</span><span class="n">setPointRepresentation</span> <span class="p">(</span><span class="n">boost</span><span class="o">::</span><span class="n">make_shared</span><span class="o">&lt;</span><span class="k">const</span> <span class="n">MyPointRepresentation</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">point_representation</span><span class="p">));</span>

<span class="n">reg</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">points_with_normals_src</span><span class="p">);</span>
<span class="n">reg</span><span class="p">.</span><span class="n">setInputTarget</span> <span class="p">(</span><span class="n">points_with_normals_tgt</span><span class="p">);</span>
</pre></div>
</div>
</div></blockquote>
<div class="line-block">
<div class="line">As this is a tutorial, we wish to display the intermediate of the registration process.</div>
<div class="line">To this end, the ICP is limited to 2 registration iterations:</div>
</div>
<blockquote>
<div><div class="highlight-cpp"><div class="highlight"><pre><span class="n">reg</span><span class="p">.</span><span class="n">setMaximumIterations</span> <span class="p">(</span><span class="mi">2</span><span class="p">);</span>
</pre></div>
</div>
</div></blockquote>
<p>And is manually iterated (30 times in our case):</p>
<blockquote>
<div><div class="highlight-cpp"><div class="highlight"><pre><span class="k">for</span> <span class="p">(</span><span class="kt">int</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="mi">30</span><span class="p">;</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
<span class="p">{</span>
        <span class="p">[...]</span>
        <span class="n">points_with_normals_src</span> <span class="o">=</span> <span class="n">reg_result</span><span class="p">;</span>
        <span class="c1">// Estimate</span>
        <span class="n">reg</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">points_with_normals_src</span><span class="p">);</span>
        <span class="n">reg</span><span class="p">.</span><span class="n">align</span> <span class="p">(</span><span class="o">*</span><span class="n">reg_result</span><span class="p">);</span>
        <span class="p">[...]</span>
<span class="p">}</span>
</pre></div>
</div>
</div></blockquote>
<p>During each iteration, we keep track of and accumulate the transformations returned by the ICP:</p>
<blockquote>
<div><div class="highlight-cpp"><div class="highlight"><pre><span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span> <span class="n">Ti</span> <span class="o">=</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span><span class="o">::</span><span class="n">Identity</span> <span class="p">(),</span> <span class="n">prev</span><span class="p">,</span> <span class="n">targetToSource</span><span class="p">;</span>
<span class="p">[...]</span>
<span class="k">for</span> <span class="p">(</span><span class="kt">int</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="mi">30</span><span class="p">;</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
<span class="p">{</span>
       <span class="p">[...]</span>
       <span class="n">Ti</span> <span class="o">=</span> <span class="n">reg</span><span class="p">.</span><span class="n">getFinalTransformation</span> <span class="p">()</span> <span class="o">*</span> <span class="n">Ti</span><span class="p">;</span>
       <span class="p">[...]</span>
<span class="p">}</span>
</pre></div>
</div>
</div></blockquote>
<div class="line-block">
<div class="line">If the difference between the transform found at iteration N and the one found at iteration N-1 is smaller than the transform threshold passed to ICP,</div>
<div class="line">we refine the matching process by choosing closer correspondences between the source and the target:</div>
</div>
<blockquote>
<div><div class="highlight-cpp"><div class="highlight"><pre><span class="k">for</span> <span class="p">(</span><span class="kt">int</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="mi">30</span><span class="p">;</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
<span class="p">{</span>
 <span class="p">[...]</span>
 <span class="k">if</span> <span class="p">(</span><span class="n">fabs</span> <span class="p">((</span><span class="n">reg</span><span class="p">.</span><span class="n">getLastIncrementalTransformation</span> <span class="p">()</span> <span class="o">-</span> <span class="n">prev</span><span class="p">).</span><span class="n">sum</span> <span class="p">())</span> <span class="o">&lt;</span> <span class="n">reg</span><span class="p">.</span><span class="n">getTransformationEpsilon</span> <span class="p">())</span>
   <span class="n">reg</span><span class="p">.</span><span class="n">setMaxCorrespondenceDistance</span> <span class="p">(</span><span class="n">reg</span><span class="p">.</span><span class="n">getMaxCorrespondenceDistance</span> <span class="p">()</span> <span class="o">-</span> <span class="mf">0.001</span><span class="p">);</span>

 <span class="n">prev</span> <span class="o">=</span> <span class="n">reg</span><span class="p">.</span><span class="n">getLastIncrementalTransformation</span> <span class="p">();</span>
 <span class="p">[...]</span>
<span class="p">}</span>
</pre></div>
</div>
</div></blockquote>
<div class="line-block">
<div class="line">Once the best transformation has been found, we invert it (to get the transformation from target to source) and apply it to the target cloud.</div>
<div class="line">The transformed target is then added to the source and returned to the main function with the transformation.</div>
</div>
<blockquote>
<div><div class="highlight-cpp"><div class="highlight"><pre><span class="c1">//</span>
<span class="c1">// Get the transformation from target to source</span>
<span class="n">targetToSource</span> <span class="o">=</span> <span class="n">Ti</span><span class="p">.</span><span class="n">inverse</span><span class="p">();</span>

<span class="c1">//</span>
<span class="c1">// Transform target back in source frame</span>
<span class="n">pcl</span><span class="o">::</span><span class="n">transformPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_tgt</span><span class="p">,</span> <span class="o">*</span><span class="n">output</span><span class="p">,</span> <span class="n">targetToSource</span><span class="p">);</span>
<span class="p">[...]</span>
<span class="o">*</span><span class="n">output</span> <span class="o">+=</span> <span class="o">*</span><span class="n">cloud_tgt</span><span class="p">;</span>
<span class="n">final_transform</span> <span class="o">=</span> <span class="n">targetToSource</span><span class="p">;</span>
</pre></div>
</div>
</div></blockquote>
</div>
</div>
<div class="section" id="compiling-and-running-the-program">
<h1>Compiling and running the program</h1>
<p>Create a file named pairwise_incremental_registration.cpp and paste the full code in it.</p>
<p>Create CMakeLists.txt file and add the following line in it:</p>
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

<span class="nb">project</span><span class="p">(</span><span class="s">tuto-pairwise</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.4</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">pairwise_incremental_registration</span> <span class="s">pairwise_incremental_registration.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">pairwise_incremental_registration</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>Copy the files from <a class="reference external" href="https://raw.github.com/PointCloudLibrary/data/master/tutorials/pairwise/">github.com/PointCloudLibrary/data/tree/master/tutorials/pairwise</a> in your working folder.</p>
<p>After you have made the executable (cmake ., make), you can run it. Simply do:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./pairwise_incremental_registration capture000[1-5].pcd
</pre></div>
</div>
<p>You will see something similar to:</p>
<a class="reference internal image-reference" href="_images/1.png"><img alt="_images/1.png" src="_images/1.png" style="height: 300px;" /></a>
<a class="reference internal image-reference" href="_images/2.png"><img alt="_images/2.png" src="_images/2.png" style="height: 300px;" /></a>
<a class="reference internal image-reference" href="_images/3.png"><img alt="_images/3.png" src="_images/3.png" style="height: 300px;" /></a>
<p>Visualize the final results by running:</p>
<div class="highlight-python"><div class="highlight"><pre>$ pcl_viewer 1.pcd 2.pcd 3.pcd 4.pcd
</pre></div>
</div>
<a class="reference internal image-reference" href="_images/4.png"><img alt="_images/4.png" src="_images/4.png" style="height: 300px;" /></a>
<a class="reference internal image-reference" href="_images/5.png"><img alt="_images/5.png" src="_images/5.png" style="height: 300px;" /></a>
<p>NOTE: if you only see a black screen in your viewer, try adjusting the camera position with your mouse. This may happen with the sample PCD files of this tutorial.</p>
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