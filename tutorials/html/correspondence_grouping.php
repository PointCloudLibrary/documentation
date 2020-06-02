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
    
    <title>3D Object Recognition based on Correspondence Grouping</title>
    
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
            
  <div class="section" id="d-object-recognition-based-on-correspondence-grouping">
<span id="correspondence-grouping"></span><h1>3D Object Recognition based on Correspondence Grouping</h1>
<p>This tutorial aims at explaining how to perform 3D Object Recognition based on the pcl_recognition module.
Specifically, it explains how to use Correspondence Grouping algorithms in order to cluster the set of point-to-point correspondences obtained after the 3D descriptor matching stage into model instances that are present in the current scene.
For each cluster, representing a possible model instance in the scene, the Correspondence Grouping algorithms also output the transformation matrix identifying the 6DOF pose estimation of that model in the current scene.</p>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>Before you begin, you should download the dataset used in this tutorial from <a class="reference external" href="https://raw.github.com/PointCloudLibrary/data/master/tutorials/correspondence_grouping">github.com/PointCloudLibrary/data/tree/master/tutorials/correspondence_grouping</a>
and extract the files in a folder of your convenience.</p>
<p>Also, copy and paste the following code into your editor and save it as <tt class="docutils literal"><span class="pre">correspondence_grouping.cpp</span></tt> (or download the source file <a class="reference download internal" href="_downloads/correspondence_grouping.cpp"><tt class="xref download docutils literal"><span class="pre">here</span></tt></a>).</p>
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
374
375
376
377
378
379
380
381
382
383
384
385
386
387
388
389
390
391
392
393
394
395
396
397
398
399
400
401
402
403
404
405
406
407
408
409
410
411
412
413
414
415
416
417
418
419
420
421
422
423
424</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;pcl/io/pcd_io.h&gt;</span>
<span class="cp">#include &lt;pcl/point_cloud.h&gt;</span>
<span class="cp">#include &lt;pcl/correspondence.h&gt;</span>
<span class="cp">#include &lt;pcl/features/normal_3d_omp.h&gt;</span>
<span class="cp">#include &lt;pcl/features/shot_omp.h&gt;</span>
<span class="cp">#include &lt;pcl/features/board.h&gt;</span>
<span class="cp">#include &lt;pcl/keypoints/uniform_sampling.h&gt;</span>
<span class="cp">#include &lt;pcl/recognition/cg/hough_3d.h&gt;</span>
<span class="cp">#include &lt;pcl/recognition/cg/geometric_consistency.h&gt;</span>
<span class="cp">#include &lt;pcl/visualization/pcl_visualizer.h&gt;</span>
<span class="cp">#include &lt;pcl/kdtree/kdtree_flann.h&gt;</span>
<span class="cp">#include &lt;pcl/kdtree/impl/kdtree_flann.hpp&gt;</span>
<span class="cp">#include &lt;pcl/common/transforms.h&gt;</span>
<span class="cp">#include &lt;pcl/console/parse.h&gt;</span>

<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span> <span class="n">PointType</span><span class="p">;</span>
<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span> <span class="n">NormalType</span><span class="p">;</span>
<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">ReferenceFrame</span> <span class="n">RFType</span><span class="p">;</span>
<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">SHOT352</span> <span class="n">DescriptorType</span><span class="p">;</span>

<span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">model_filename_</span><span class="p">;</span>
<span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">scene_filename_</span><span class="p">;</span>

<span class="c1">//Algorithm params</span>
<span class="kt">bool</span> <span class="nf">show_keypoints_</span> <span class="p">(</span><span class="nb">false</span><span class="p">);</span>
<span class="kt">bool</span> <span class="nf">show_correspondences_</span> <span class="p">(</span><span class="nb">false</span><span class="p">);</span>
<span class="kt">bool</span> <span class="nf">use_cloud_resolution_</span> <span class="p">(</span><span class="nb">false</span><span class="p">);</span>
<span class="kt">bool</span> <span class="nf">use_hough_</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
<span class="kt">float</span> <span class="nf">model_ss_</span> <span class="p">(</span><span class="mf">0.01f</span><span class="p">);</span>
<span class="kt">float</span> <span class="nf">scene_ss_</span> <span class="p">(</span><span class="mf">0.03f</span><span class="p">);</span>
<span class="kt">float</span> <span class="nf">rf_rad_</span> <span class="p">(</span><span class="mf">0.015f</span><span class="p">);</span>
<span class="kt">float</span> <span class="nf">descr_rad_</span> <span class="p">(</span><span class="mf">0.02f</span><span class="p">);</span>
<span class="kt">float</span> <span class="nf">cg_size_</span> <span class="p">(</span><span class="mf">0.01f</span><span class="p">);</span>
<span class="kt">float</span> <span class="nf">cg_thresh_</span> <span class="p">(</span><span class="mf">5.0f</span><span class="p">);</span>

<span class="kt">void</span>
<span class="nf">showHelp</span> <span class="p">(</span><span class="kt">char</span> <span class="o">*</span><span class="n">filename</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;***************************************************************************&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;*                                                                         *&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;*             Correspondence Grouping Tutorial - Usage Guide              *&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;*                                                                         *&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;***************************************************************************&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Usage: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">filename</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; model_filename.pcd scene_filename.pcd [Options]&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Options:&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     -h:                     Show this help.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     -k:                     Show used keypoints.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     -c:                     Show used correspondences.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     -r:                     Compute the model cloud resolution and multiply&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;                             each radius given by that value.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     --algorithm (Hough|GC): Clustering algorithm used (default Hough).&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     --model_ss val:         Model uniform sampling radius (default 0.01)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     --scene_ss val:         Scene uniform sampling radius (default 0.03)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     --rf_rad val:           Reference frame radius (default 0.015)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     --descr_rad val:        Descriptor radius (default 0.02)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     --cg_size val:          Cluster size (default 0.01)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     --cg_thresh val:        Clustering threshold (default 5)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
<span class="p">}</span>

<span class="kt">void</span>
<span class="nf">parseCommandLine</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span> <span class="o">*</span><span class="n">argv</span><span class="p">[])</span>
<span class="p">{</span>
  <span class="c1">//Show help</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_switch</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-h&quot;</span><span class="p">))</span>
  <span class="p">{</span>
    <span class="n">showHelp</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
    <span class="n">exit</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="c1">//Model &amp; scene filenames</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">filenames</span><span class="p">;</span>
  <span class="n">filenames</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_file_extension_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;.pcd&quot;</span><span class="p">);</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">filenames</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">!=</span> <span class="mi">2</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Filenames missing.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
    <span class="n">showHelp</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
    <span class="n">exit</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="n">model_filename_</span> <span class="o">=</span> <span class="n">argv</span><span class="p">[</span><span class="n">filenames</span><span class="p">[</span><span class="mi">0</span><span class="p">]];</span>
  <span class="n">scene_filename_</span> <span class="o">=</span> <span class="n">argv</span><span class="p">[</span><span class="n">filenames</span><span class="p">[</span><span class="mi">1</span><span class="p">]];</span>

  <span class="c1">//Program behavior</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_switch</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-k&quot;</span><span class="p">))</span>
  <span class="p">{</span>
    <span class="n">show_keypoints_</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
  <span class="p">}</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_switch</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-c&quot;</span><span class="p">))</span>
  <span class="p">{</span>
    <span class="n">show_correspondences_</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
  <span class="p">}</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_switch</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-r&quot;</span><span class="p">))</span>
  <span class="p">{</span>
    <span class="n">use_cloud_resolution_</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
  <span class="p">}</span>

  <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">used_algorithm</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--algorithm&quot;</span><span class="p">,</span> <span class="n">used_algorithm</span><span class="p">)</span> <span class="o">!=</span> <span class="o">-</span><span class="mi">1</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">used_algorithm</span><span class="p">.</span><span class="n">compare</span> <span class="p">(</span><span class="s">&quot;Hough&quot;</span><span class="p">)</span> <span class="o">==</span> <span class="mi">0</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">use_hough_</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
    <span class="p">}</span><span class="k">else</span> <span class="k">if</span> <span class="p">(</span><span class="n">used_algorithm</span><span class="p">.</span><span class="n">compare</span> <span class="p">(</span><span class="s">&quot;GC&quot;</span><span class="p">)</span> <span class="o">==</span> <span class="mi">0</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">use_hough_</span> <span class="o">=</span> <span class="nb">false</span><span class="p">;</span>
    <span class="p">}</span>
    <span class="k">else</span>
    <span class="p">{</span>
      <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Wrong algorithm name.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
      <span class="n">showHelp</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
      <span class="n">exit</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
    <span class="p">}</span>
  <span class="p">}</span>

  <span class="c1">//General parameters</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--model_ss&quot;</span><span class="p">,</span> <span class="n">model_ss_</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--scene_ss&quot;</span><span class="p">,</span> <span class="n">scene_ss_</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--rf_rad&quot;</span><span class="p">,</span> <span class="n">rf_rad_</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--descr_rad&quot;</span><span class="p">,</span> <span class="n">descr_rad_</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--cg_size&quot;</span><span class="p">,</span> <span class="n">cg_size_</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--cg_thresh&quot;</span><span class="p">,</span> <span class="n">cg_thresh_</span><span class="p">);</span>
<span class="p">}</span>

<span class="kt">double</span>
<span class="nf">computeCloudResolution</span> <span class="p">(</span><span class="k">const</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;::</span><span class="n">ConstPtr</span> <span class="o">&amp;</span><span class="n">cloud</span><span class="p">)</span>
<span class="p">{</span>
  <span class="kt">double</span> <span class="n">res</span> <span class="o">=</span> <span class="mf">0.0</span><span class="p">;</span>
  <span class="kt">int</span> <span class="n">n_points</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="kt">int</span> <span class="n">nres</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">indices</span> <span class="p">(</span><span class="mi">2</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="n">sqr_distances</span> <span class="p">(</span><span class="mi">2</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="n">tree</span><span class="p">;</span>
  <span class="n">tree</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>

  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="k">if</span> <span class="p">(</span><span class="o">!</span> <span class="n">pcl_isfinite</span> <span class="p">((</span><span class="o">*</span><span class="n">cloud</span><span class="p">)[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span><span class="p">))</span>
    <span class="p">{</span>
      <span class="k">continue</span><span class="p">;</span>
    <span class="p">}</span>
    <span class="c1">//Considering the second neighbor since the first is the point itself.</span>
    <span class="n">nres</span> <span class="o">=</span> <span class="n">tree</span><span class="p">.</span><span class="n">nearestKSearch</span> <span class="p">(</span><span class="n">i</span><span class="p">,</span> <span class="mi">2</span><span class="p">,</span> <span class="n">indices</span><span class="p">,</span> <span class="n">sqr_distances</span><span class="p">);</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">nres</span> <span class="o">==</span> <span class="mi">2</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">res</span> <span class="o">+=</span> <span class="n">sqrt</span> <span class="p">(</span><span class="n">sqr_distances</span><span class="p">[</span><span class="mi">1</span><span class="p">]);</span>
      <span class="o">++</span><span class="n">n_points</span><span class="p">;</span>
    <span class="p">}</span>
  <span class="p">}</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">n_points</span> <span class="o">!=</span> <span class="mi">0</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">res</span> <span class="o">/=</span> <span class="n">n_points</span><span class="p">;</span>
  <span class="p">}</span>
  <span class="k">return</span> <span class="n">res</span><span class="p">;</span>
<span class="p">}</span>

<span class="kt">int</span>
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span> <span class="o">*</span><span class="n">argv</span><span class="p">[])</span>
<span class="p">{</span>
  <span class="n">parseCommandLine</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">model</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">model_keypoints</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">scene</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">scene_keypoints</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">NormalType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">model_normals</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">NormalType</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">NormalType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">scene_normals</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">NormalType</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">DescriptorType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">model_descriptors</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">DescriptorType</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">DescriptorType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">scene_descriptors</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">DescriptorType</span><span class="o">&gt;</span> <span class="p">());</span>

  <span class="c1">//</span>
  <span class="c1">//  Load clouds</span>
  <span class="c1">//</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="n">model_filename_</span><span class="p">,</span> <span class="o">*</span><span class="n">model</span><span class="p">)</span> <span class="o">&lt;</span> <span class="mi">0</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Error loading model cloud.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="n">showHelp</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="n">scene_filename_</span><span class="p">,</span> <span class="o">*</span><span class="n">scene</span><span class="p">)</span> <span class="o">&lt;</span> <span class="mi">0</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Error loading scene cloud.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="n">showHelp</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="c1">//</span>
  <span class="c1">//  Set up resolution invariance</span>
  <span class="c1">//</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">use_cloud_resolution_</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="kt">float</span> <span class="n">resolution</span> <span class="o">=</span> <span class="k">static_cast</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">computeCloudResolution</span> <span class="p">(</span><span class="n">model</span><span class="p">));</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">resolution</span> <span class="o">!=</span> <span class="mf">0.0f</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">model_ss_</span>   <span class="o">*=</span> <span class="n">resolution</span><span class="p">;</span>
      <span class="n">scene_ss_</span>   <span class="o">*=</span> <span class="n">resolution</span><span class="p">;</span>
      <span class="n">rf_rad_</span>     <span class="o">*=</span> <span class="n">resolution</span><span class="p">;</span>
      <span class="n">descr_rad_</span>  <span class="o">*=</span> <span class="n">resolution</span><span class="p">;</span>
      <span class="n">cg_size_</span>    <span class="o">*=</span> <span class="n">resolution</span><span class="p">;</span>
    <span class="p">}</span>

    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Model resolution:       &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">resolution</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Model sampling size:    &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">model_ss_</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Scene sampling size:    &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">scene_ss_</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;LRF support radius:     &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">rf_rad_</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;SHOT descriptor radius: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">descr_rad_</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Clustering bin size:    &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cg_size_</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="p">}</span>

  <span class="c1">//</span>
  <span class="c1">//  Compute Normals</span>
  <span class="c1">//</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">NormalEstimationOMP</span><span class="o">&lt;</span><span class="n">PointType</span><span class="p">,</span> <span class="n">NormalType</span><span class="o">&gt;</span> <span class="n">norm_est</span><span class="p">;</span>
  <span class="n">norm_est</span><span class="p">.</span><span class="n">setKSearch</span> <span class="p">(</span><span class="mi">10</span><span class="p">);</span>
  <span class="n">norm_est</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">model</span><span class="p">);</span>
  <span class="n">norm_est</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">model_normals</span><span class="p">);</span>

  <span class="n">norm_est</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">scene</span><span class="p">);</span>
  <span class="n">norm_est</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">scene_normals</span><span class="p">);</span>

  <span class="c1">//</span>
  <span class="c1">//  Downsample Clouds to Extract keypoints</span>
  <span class="c1">//</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">sampled_indices</span><span class="p">;</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">UniformSampling</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="n">uniform_sampling</span><span class="p">;</span>
  <span class="n">uniform_sampling</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">model</span><span class="p">);</span>
  <span class="n">uniform_sampling</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="n">model_ss_</span><span class="p">);</span>
  <span class="n">uniform_sampling</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="n">sampled_indices</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">copyPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">model</span><span class="p">,</span> <span class="n">sampled_indices</span><span class="p">.</span><span class="n">points</span><span class="p">,</span> <span class="o">*</span><span class="n">model_keypoints</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Model total points: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">model</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;; Selected Keypoints: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">model_keypoints</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="n">uniform_sampling</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">scene</span><span class="p">);</span>
  <span class="n">uniform_sampling</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="n">scene_ss_</span><span class="p">);</span>
  <span class="n">uniform_sampling</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="n">sampled_indices</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">copyPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">scene</span><span class="p">,</span> <span class="n">sampled_indices</span><span class="p">.</span><span class="n">points</span><span class="p">,</span> <span class="o">*</span><span class="n">scene_keypoints</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Scene total points: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">scene</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;; Selected Keypoints: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">scene_keypoints</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>


  <span class="c1">//</span>
  <span class="c1">//  Compute Descriptor for keypoints</span>
  <span class="c1">//</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">SHOTEstimationOMP</span><span class="o">&lt;</span><span class="n">PointType</span><span class="p">,</span> <span class="n">NormalType</span><span class="p">,</span> <span class="n">DescriptorType</span><span class="o">&gt;</span> <span class="n">descr_est</span><span class="p">;</span>
  <span class="n">descr_est</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="n">descr_rad_</span><span class="p">);</span>

  <span class="n">descr_est</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">model_keypoints</span><span class="p">);</span>
  <span class="n">descr_est</span><span class="p">.</span><span class="n">setInputNormals</span> <span class="p">(</span><span class="n">model_normals</span><span class="p">);</span>
  <span class="n">descr_est</span><span class="p">.</span><span class="n">setSearchSurface</span> <span class="p">(</span><span class="n">model</span><span class="p">);</span>
  <span class="n">descr_est</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">model_descriptors</span><span class="p">);</span>

  <span class="n">descr_est</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">scene_keypoints</span><span class="p">);</span>
  <span class="n">descr_est</span><span class="p">.</span><span class="n">setInputNormals</span> <span class="p">(</span><span class="n">scene_normals</span><span class="p">);</span>
  <span class="n">descr_est</span><span class="p">.</span><span class="n">setSearchSurface</span> <span class="p">(</span><span class="n">scene</span><span class="p">);</span>
  <span class="n">descr_est</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">scene_descriptors</span><span class="p">);</span>

  <span class="c1">//</span>
  <span class="c1">//  Find Model-Scene Correspondences with KdTree</span>
  <span class="c1">//</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">CorrespondencesPtr</span> <span class="n">model_scene_corrs</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Correspondences</span> <span class="p">());</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">KdTreeFLANN</span><span class="o">&lt;</span><span class="n">DescriptorType</span><span class="o">&gt;</span> <span class="n">match_search</span><span class="p">;</span>
  <span class="n">match_search</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">model_descriptors</span><span class="p">);</span>

  <span class="c1">//  For each scene keypoint descriptor, find nearest neighbor into the model keypoints descriptor cloud and add it to the correspondences vector.</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">scene_descriptors</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">neigh_indices</span> <span class="p">(</span><span class="mi">1</span><span class="p">);</span>
    <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="n">neigh_sqr_dists</span> <span class="p">(</span><span class="mi">1</span><span class="p">);</span>
    <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">pcl_isfinite</span> <span class="p">(</span><span class="n">scene_descriptors</span><span class="o">-&gt;</span><span class="n">at</span> <span class="p">(</span><span class="n">i</span><span class="p">).</span><span class="n">descriptor</span><span class="p">[</span><span class="mi">0</span><span class="p">]))</span> <span class="c1">//skipping NaNs</span>
    <span class="p">{</span>
      <span class="k">continue</span><span class="p">;</span>
    <span class="p">}</span>
    <span class="kt">int</span> <span class="n">found_neighs</span> <span class="o">=</span> <span class="n">match_search</span><span class="p">.</span><span class="n">nearestKSearch</span> <span class="p">(</span><span class="n">scene_descriptors</span><span class="o">-&gt;</span><span class="n">at</span> <span class="p">(</span><span class="n">i</span><span class="p">),</span> <span class="mi">1</span><span class="p">,</span> <span class="n">neigh_indices</span><span class="p">,</span> <span class="n">neigh_sqr_dists</span><span class="p">);</span>
    <span class="k">if</span><span class="p">(</span><span class="n">found_neighs</span> <span class="o">==</span> <span class="mi">1</span> <span class="o">&amp;&amp;</span> <span class="n">neigh_sqr_dists</span><span class="p">[</span><span class="mi">0</span><span class="p">]</span> <span class="o">&lt;</span> <span class="mf">0.25f</span><span class="p">)</span> <span class="c1">//  add match only if the squared descriptor distance is less than 0.25 (SHOT descriptor distances are between 0 and 1 by design)</span>
    <span class="p">{</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">Correspondence</span> <span class="n">corr</span> <span class="p">(</span><span class="n">neigh_indices</span><span class="p">[</span><span class="mi">0</span><span class="p">],</span> <span class="k">static_cast</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">i</span><span class="p">),</span> <span class="n">neigh_sqr_dists</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
      <span class="n">model_scene_corrs</span><span class="o">-&gt;</span><span class="n">push_back</span> <span class="p">(</span><span class="n">corr</span><span class="p">);</span>
    <span class="p">}</span>
  <span class="p">}</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Correspondences found: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">model_scene_corrs</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="c1">//</span>
  <span class="c1">//  Actual Clustering</span>
  <span class="c1">//</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span><span class="p">,</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">aligned_allocator</span><span class="o">&lt;</span><span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="n">rototranslations</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Correspondences</span><span class="o">&gt;</span> <span class="n">clustered_corrs</span><span class="p">;</span>

  <span class="c1">//  Using Hough3D</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">use_hough_</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="c1">//</span>
    <span class="c1">//  Compute (Keypoints) Reference Frames only for Hough</span>
    <span class="c1">//</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">RFType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">model_rf</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">RFType</span><span class="o">&gt;</span> <span class="p">());</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">RFType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">scene_rf</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">RFType</span><span class="o">&gt;</span> <span class="p">());</span>

    <span class="n">pcl</span><span class="o">::</span><span class="n">BOARDLocalReferenceFrameEstimation</span><span class="o">&lt;</span><span class="n">PointType</span><span class="p">,</span> <span class="n">NormalType</span><span class="p">,</span> <span class="n">RFType</span><span class="o">&gt;</span> <span class="n">rf_est</span><span class="p">;</span>
    <span class="n">rf_est</span><span class="p">.</span><span class="n">setFindHoles</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
    <span class="n">rf_est</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="n">rf_rad_</span><span class="p">);</span>

    <span class="n">rf_est</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">model_keypoints</span><span class="p">);</span>
    <span class="n">rf_est</span><span class="p">.</span><span class="n">setInputNormals</span> <span class="p">(</span><span class="n">model_normals</span><span class="p">);</span>
    <span class="n">rf_est</span><span class="p">.</span><span class="n">setSearchSurface</span> <span class="p">(</span><span class="n">model</span><span class="p">);</span>
    <span class="n">rf_est</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">model_rf</span><span class="p">);</span>

    <span class="n">rf_est</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">scene_keypoints</span><span class="p">);</span>
    <span class="n">rf_est</span><span class="p">.</span><span class="n">setInputNormals</span> <span class="p">(</span><span class="n">scene_normals</span><span class="p">);</span>
    <span class="n">rf_est</span><span class="p">.</span><span class="n">setSearchSurface</span> <span class="p">(</span><span class="n">scene</span><span class="p">);</span>
    <span class="n">rf_est</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">scene_rf</span><span class="p">);</span>

    <span class="c1">//  Clustering</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">Hough3DGrouping</span><span class="o">&lt;</span><span class="n">PointType</span><span class="p">,</span> <span class="n">PointType</span><span class="p">,</span> <span class="n">RFType</span><span class="p">,</span> <span class="n">RFType</span><span class="o">&gt;</span> <span class="n">clusterer</span><span class="p">;</span>
    <span class="n">clusterer</span><span class="p">.</span><span class="n">setHoughBinSize</span> <span class="p">(</span><span class="n">cg_size_</span><span class="p">);</span>
    <span class="n">clusterer</span><span class="p">.</span><span class="n">setHoughThreshold</span> <span class="p">(</span><span class="n">cg_thresh_</span><span class="p">);</span>
    <span class="n">clusterer</span><span class="p">.</span><span class="n">setUseInterpolation</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
    <span class="n">clusterer</span><span class="p">.</span><span class="n">setUseDistanceWeight</span> <span class="p">(</span><span class="nb">false</span><span class="p">);</span>

    <span class="n">clusterer</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">model_keypoints</span><span class="p">);</span>
    <span class="n">clusterer</span><span class="p">.</span><span class="n">setInputRf</span> <span class="p">(</span><span class="n">model_rf</span><span class="p">);</span>
    <span class="n">clusterer</span><span class="p">.</span><span class="n">setSceneCloud</span> <span class="p">(</span><span class="n">scene_keypoints</span><span class="p">);</span>
    <span class="n">clusterer</span><span class="p">.</span><span class="n">setSceneRf</span> <span class="p">(</span><span class="n">scene_rf</span><span class="p">);</span>
    <span class="n">clusterer</span><span class="p">.</span><span class="n">setModelSceneCorrespondences</span> <span class="p">(</span><span class="n">model_scene_corrs</span><span class="p">);</span>

    <span class="c1">//clusterer.cluster (clustered_corrs);</span>
    <span class="n">clusterer</span><span class="p">.</span><span class="n">recognize</span> <span class="p">(</span><span class="n">rototranslations</span><span class="p">,</span> <span class="n">clustered_corrs</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="k">else</span> <span class="c1">// Using GeometricConsistency</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">GeometricConsistencyGrouping</span><span class="o">&lt;</span><span class="n">PointType</span><span class="p">,</span> <span class="n">PointType</span><span class="o">&gt;</span> <span class="n">gc_clusterer</span><span class="p">;</span>
    <span class="n">gc_clusterer</span><span class="p">.</span><span class="n">setGCSize</span> <span class="p">(</span><span class="n">cg_size_</span><span class="p">);</span>
    <span class="n">gc_clusterer</span><span class="p">.</span><span class="n">setGCThreshold</span> <span class="p">(</span><span class="n">cg_thresh_</span><span class="p">);</span>

    <span class="n">gc_clusterer</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">model_keypoints</span><span class="p">);</span>
    <span class="n">gc_clusterer</span><span class="p">.</span><span class="n">setSceneCloud</span> <span class="p">(</span><span class="n">scene_keypoints</span><span class="p">);</span>
    <span class="n">gc_clusterer</span><span class="p">.</span><span class="n">setModelSceneCorrespondences</span> <span class="p">(</span><span class="n">model_scene_corrs</span><span class="p">);</span>

    <span class="c1">//gc_clusterer.cluster (clustered_corrs);</span>
    <span class="n">gc_clusterer</span><span class="p">.</span><span class="n">recognize</span> <span class="p">(</span><span class="n">rototranslations</span><span class="p">,</span> <span class="n">clustered_corrs</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="c1">//</span>
  <span class="c1">//  Output results</span>
  <span class="c1">//</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Model instances found: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">rototranslations</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">rototranslations</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;</span><span class="se">\n</span><span class="s">    Instance &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">i</span> <span class="o">+</span> <span class="mi">1</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;:&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;        Correspondences belonging to this instance: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">clustered_corrs</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

    <span class="c1">// Print the rotation matrix and translation vector</span>
    <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix3f</span> <span class="n">rotation</span> <span class="o">=</span> <span class="n">rototranslations</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">block</span><span class="o">&lt;</span><span class="mi">3</span><span class="p">,</span><span class="mi">3</span><span class="o">&gt;</span><span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
    <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span> <span class="n">translation</span> <span class="o">=</span> <span class="n">rototranslations</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">block</span><span class="o">&lt;</span><span class="mi">3</span><span class="p">,</span><span class="mi">1</span><span class="o">&gt;</span><span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mi">3</span><span class="p">);</span>

    <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
    <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;            | %6.3f %6.3f %6.3f | </span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">0</span><span class="p">),</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">1</span><span class="p">),</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">2</span><span class="p">));</span>
    <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;        R = | %6.3f %6.3f %6.3f | </span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span><span class="mi">0</span><span class="p">),</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span><span class="mi">1</span><span class="p">),</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span><span class="mi">2</span><span class="p">));</span>
    <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;            | %6.3f %6.3f %6.3f | </span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">2</span><span class="p">,</span><span class="mi">0</span><span class="p">),</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">2</span><span class="p">,</span><span class="mi">1</span><span class="p">),</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">2</span><span class="p">,</span><span class="mi">2</span><span class="p">));</span>
    <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
    <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;        t = &lt; %0.3f, %0.3f, %0.3f &gt;</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">translation</span> <span class="p">(</span><span class="mi">0</span><span class="p">),</span> <span class="n">translation</span> <span class="p">(</span><span class="mi">1</span><span class="p">),</span> <span class="n">translation</span> <span class="p">(</span><span class="mi">2</span><span class="p">));</span>
  <span class="p">}</span>

  <span class="c1">//</span>
  <span class="c1">//  Visualization</span>
  <span class="c1">//</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="n">viewer</span> <span class="p">(</span><span class="s">&quot;Correspondence Grouping&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">scene</span><span class="p">,</span> <span class="s">&quot;scene_cloud&quot;</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">off_scene_model</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">off_scene_model_keypoints</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="p">());</span>

  <span class="k">if</span> <span class="p">(</span><span class="n">show_correspondences_</span> <span class="o">||</span> <span class="n">show_keypoints_</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="c1">//  We are translating the model so that it doesn&#39;t end in the middle of the scene representation</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">transformPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">model</span><span class="p">,</span> <span class="o">*</span><span class="n">off_scene_model</span><span class="p">,</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">,</span><span class="mi">0</span><span class="p">,</span><span class="mi">0</span><span class="p">),</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Quaternionf</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">));</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">transformPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">model_keypoints</span><span class="p">,</span> <span class="o">*</span><span class="n">off_scene_model_keypoints</span><span class="p">,</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">,</span><span class="mi">0</span><span class="p">,</span><span class="mi">0</span><span class="p">),</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Quaternionf</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">));</span>

    <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="n">off_scene_model_color_handler</span> <span class="p">(</span><span class="n">off_scene_model</span><span class="p">,</span> <span class="mi">255</span><span class="p">,</span> <span class="mi">255</span><span class="p">,</span> <span class="mi">128</span><span class="p">);</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">off_scene_model</span><span class="p">,</span> <span class="n">off_scene_model_color_handler</span><span class="p">,</span> <span class="s">&quot;off_scene_model&quot;</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="k">if</span> <span class="p">(</span><span class="n">show_keypoints_</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="n">scene_keypoints_color_handler</span> <span class="p">(</span><span class="n">scene_keypoints</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">255</span><span class="p">);</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">scene_keypoints</span><span class="p">,</span> <span class="n">scene_keypoints_color_handler</span><span class="p">,</span> <span class="s">&quot;scene_keypoints&quot;</span><span class="p">);</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span> <span class="mi">5</span><span class="p">,</span> <span class="s">&quot;scene_keypoints&quot;</span><span class="p">);</span>

    <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="n">off_scene_model_keypoints_color_handler</span> <span class="p">(</span><span class="n">off_scene_model_keypoints</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">255</span><span class="p">);</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">off_scene_model_keypoints</span><span class="p">,</span> <span class="n">off_scene_model_keypoints_color_handler</span><span class="p">,</span> <span class="s">&quot;off_scene_model_keypoints&quot;</span><span class="p">);</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span> <span class="mi">5</span><span class="p">,</span> <span class="s">&quot;off_scene_model_keypoints&quot;</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">rototranslations</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">rotated_model</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="p">());</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">transformPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">model</span><span class="p">,</span> <span class="o">*</span><span class="n">rotated_model</span><span class="p">,</span> <span class="n">rototranslations</span><span class="p">[</span><span class="n">i</span><span class="p">]);</span>

    <span class="n">std</span><span class="o">::</span><span class="n">stringstream</span> <span class="n">ss_cloud</span><span class="p">;</span>
    <span class="n">ss_cloud</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;instance&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">i</span><span class="p">;</span>

    <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="n">rotated_model_color_handler</span> <span class="p">(</span><span class="n">rotated_model</span><span class="p">,</span> <span class="mi">255</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">rotated_model</span><span class="p">,</span> <span class="n">rotated_model_color_handler</span><span class="p">,</span> <span class="n">ss_cloud</span><span class="p">.</span><span class="n">str</span> <span class="p">());</span>

    <span class="k">if</span> <span class="p">(</span><span class="n">show_correspondences_</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">j</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">j</span> <span class="o">&lt;</span> <span class="n">clustered_corrs</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">j</span><span class="p">)</span>
      <span class="p">{</span>
        <span class="n">std</span><span class="o">::</span><span class="n">stringstream</span> <span class="n">ss_line</span><span class="p">;</span>
        <span class="n">ss_line</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;correspondence_line&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">i</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;_&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">j</span><span class="p">;</span>
        <span class="n">PointType</span><span class="o">&amp;</span> <span class="n">model_point</span> <span class="o">=</span> <span class="n">off_scene_model_keypoints</span><span class="o">-&gt;</span><span class="n">at</span> <span class="p">(</span><span class="n">clustered_corrs</span><span class="p">[</span><span class="n">i</span><span class="p">][</span><span class="n">j</span><span class="p">].</span><span class="n">index_query</span><span class="p">);</span>
        <span class="n">PointType</span><span class="o">&amp;</span> <span class="n">scene_point</span> <span class="o">=</span> <span class="n">scene_keypoints</span><span class="o">-&gt;</span><span class="n">at</span> <span class="p">(</span><span class="n">clustered_corrs</span><span class="p">[</span><span class="n">i</span><span class="p">][</span><span class="n">j</span><span class="p">].</span><span class="n">index_match</span><span class="p">);</span>

        <span class="c1">//  We are drawing a line for each pair of clustered correspondences found between the model and the scene</span>
        <span class="n">viewer</span><span class="p">.</span><span class="n">addLine</span><span class="o">&lt;</span><span class="n">PointType</span><span class="p">,</span> <span class="n">PointType</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">model_point</span><span class="p">,</span> <span class="n">scene_point</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">255</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="n">ss_line</span><span class="p">.</span><span class="n">str</span> <span class="p">());</span>
      <span class="p">}</span>
    <span class="p">}</span>
  <span class="p">}</span>

  <span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">viewer</span><span class="p">.</span><span class="n">wasStopped</span> <span class="p">())</span>
  <span class="p">{</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">spinOnce</span> <span class="p">();</span>
  <span class="p">}</span>

  <span class="k">return</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="walkthrough">
<h1>Walkthrough</h1>
<p>Now let&#8217;s take a look at the various parts of the code to see how it works.</p>
<div class="section" id="helper-functions">
<h2>Helper Functions</h2>
<p>Let&#8217;s start with a couple of useful functions: the first one prints
on the console a short explanation of the several command line switches
that the program can accept.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">void</span>
<span class="nf">showHelp</span> <span class="p">(</span><span class="kt">char</span> <span class="o">*</span><span class="n">filename</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;***************************************************************************&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;*                                                                         *&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;*             Correspondence Grouping Tutorial - Usage Guide              *&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;*                                                                         *&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;***************************************************************************&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Usage: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">filename</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; model_filename.pcd scene_filename.pcd [Options]&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Options:&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     -h:                     Show this help.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     -k:                     Show used keypoints.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     -c:                     Show used correspondences.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     -r:                     Compute the model cloud resolution and multiply&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;                             each radius given by that value.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     --algorithm (Hough|GC): Clustering algorithm used (default Hough).&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     --model_ss val:         Model uniform sampling radius (default 0.01)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     --scene_ss val:         Scene uniform sampling radius (default 0.03)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     --rf_rad val:           Reference frame radius (default 0.015)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     --descr_rad val:        Descriptor radius (default 0.02)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     --cg_size val:          Cluster size (default 0.01)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     --cg_thresh val:        Clustering threshold (default 5)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
<span class="p">}</span>
</pre></div>
</div>
<p>The second function does the actual parsing of the command line
arguments in order to set the correct parameters for the execution.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">void</span>
<span class="nf">parseCommandLine</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span> <span class="o">*</span><span class="n">argv</span><span class="p">[])</span>
<span class="p">{</span>
  <span class="c1">//Show help</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_switch</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-h&quot;</span><span class="p">))</span>
  <span class="p">{</span>
    <span class="n">showHelp</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
    <span class="n">exit</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="c1">//Model &amp; scene filenames</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">filenames</span><span class="p">;</span>
  <span class="n">filenames</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_file_extension_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;.pcd&quot;</span><span class="p">);</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">filenames</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">!=</span> <span class="mi">2</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Filenames missing.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
    <span class="n">showHelp</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
    <span class="n">exit</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="n">model_filename_</span> <span class="o">=</span> <span class="n">argv</span><span class="p">[</span><span class="n">filenames</span><span class="p">[</span><span class="mi">0</span><span class="p">]];</span>
  <span class="n">scene_filename_</span> <span class="o">=</span> <span class="n">argv</span><span class="p">[</span><span class="n">filenames</span><span class="p">[</span><span class="mi">1</span><span class="p">]];</span>

  <span class="c1">//Program behavior</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_switch</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-k&quot;</span><span class="p">))</span>
  <span class="p">{</span>
    <span class="n">show_keypoints_</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
  <span class="p">}</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_switch</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-c&quot;</span><span class="p">))</span>
  <span class="p">{</span>
    <span class="n">show_correspondences_</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
  <span class="p">}</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_switch</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-r&quot;</span><span class="p">))</span>
  <span class="p">{</span>
    <span class="n">use_cloud_resolution_</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
  <span class="p">}</span>

  <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">used_algorithm</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--algorithm&quot;</span><span class="p">,</span> <span class="n">used_algorithm</span><span class="p">)</span> <span class="o">!=</span> <span class="o">-</span><span class="mi">1</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">used_algorithm</span><span class="p">.</span><span class="n">compare</span> <span class="p">(</span><span class="s">&quot;Hough&quot;</span><span class="p">)</span> <span class="o">==</span> <span class="mi">0</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">use_hough_</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
    <span class="p">}</span><span class="k">else</span> <span class="k">if</span> <span class="p">(</span><span class="n">used_algorithm</span><span class="p">.</span><span class="n">compare</span> <span class="p">(</span><span class="s">&quot;GC&quot;</span><span class="p">)</span> <span class="o">==</span> <span class="mi">0</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">use_hough_</span> <span class="o">=</span> <span class="nb">false</span><span class="p">;</span>
    <span class="p">}</span>
    <span class="k">else</span>
    <span class="p">{</span>
      <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Wrong algorithm name.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
      <span class="n">showHelp</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
      <span class="n">exit</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
    <span class="p">}</span>
  <span class="p">}</span>

  <span class="c1">//General parameters</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--model_ss&quot;</span><span class="p">,</span> <span class="n">model_ss_</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--scene_ss&quot;</span><span class="p">,</span> <span class="n">scene_ss_</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--rf_rad&quot;</span><span class="p">,</span> <span class="n">rf_rad_</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--descr_rad&quot;</span><span class="p">,</span> <span class="n">descr_rad_</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--cg_size&quot;</span><span class="p">,</span> <span class="n">cg_size_</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--cg_thresh&quot;</span><span class="p">,</span> <span class="n">cg_thresh_</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</div>
<p>It&#8217;s important to say that the only command line parameters <em>required</em> when executing this tutorial are the filenames of the
model and the scene, in this exact order. All other parameters are set
to a default value that will make the tutorial work correctly
with the supplied dataset, although with different models and scene some parameter values might need to be adjusted. You can play around with them to see how they influence the final result.</p>
<p>You can choose between two correspondence clustering algorithms with the command line switch <tt class="docutils literal"><span class="pre">--algorithm</span> <span class="pre">(Hough|GC)</span></tt></p>
<blockquote>
<div><ul>
<li><dl class="first docutils">
<dt><strong>Hough (default)</strong></dt>
<dd><p class="first">This is a clustering algorithm based on a 3D Hough voting scheme described in:</p>
<blockquote class="last">
<div><p><em>F. Tombari and L. Di Stefano:</em> &#8220;Object recognition in 3D scenes with occlusions and clutter by Hough voting&#8221;, 4th Pacific-Rim Symposium on Image and Video Technology, 2010.</p>
</div></blockquote>
</dd>
</dl>
</li>
<li><dl class="first docutils">
<dt><strong>GC</strong></dt>
<dd><p class="first">This is a geometric consistency clustering algorithm enforcing simple geometric constraints between pairs of correspondences. It builds on the proposal presented in:</p>
<blockquote class="last">
<div><p><em>H. Chen and B. Bhanu:</em> &#8220;3D free-form object recognition in range images using local surface patches&#8221;, Pattern Recognition Letters, vol. 28, no. 10, pp. 1252-1262, 2007.</p>
</div></blockquote>
</dd>
</dl>
</li>
</ul>
</div></blockquote>
<p>Some other interesting switches are <tt class="docutils literal"><span class="pre">-k</span></tt>, <tt class="docutils literal"><span class="pre">-c</span></tt> and <tt class="docutils literal"><span class="pre">-r</span></tt>:</p>
<blockquote>
<div><ul class="simple">
<li><tt class="docutils literal"><span class="pre">-k</span></tt> shows the keypoints used to compute the correspondences as a blue overlay into the PCL visualizer.</li>
<li><tt class="docutils literal"><span class="pre">-c</span></tt> draws a line connecting each pair of model-scene correspondences that <em>survived</em> the clustering process.</li>
<li><tt class="docutils literal"><span class="pre">-r</span></tt> estimates the spatial resolution for the model point cloud and afterwards considers the radii used as parameters as if they were given in units of cloud resolution; thus achieving some sort of resolution invariance that might be useful when using this tutorial with the same command line and different point clouds.</li>
</ul>
</div></blockquote>
<p>The next function performs the spatial resolution computation for a given point cloud averaging the distance between each cloud point and its nearest neighbor.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">double</span>
<span class="nf">computeCloudResolution</span> <span class="p">(</span><span class="k">const</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;::</span><span class="n">ConstPtr</span> <span class="o">&amp;</span><span class="n">cloud</span><span class="p">)</span>
<span class="p">{</span>
  <span class="kt">double</span> <span class="n">res</span> <span class="o">=</span> <span class="mf">0.0</span><span class="p">;</span>
  <span class="kt">int</span> <span class="n">n_points</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="kt">int</span> <span class="n">nres</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">indices</span> <span class="p">(</span><span class="mi">2</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="n">sqr_distances</span> <span class="p">(</span><span class="mi">2</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="n">tree</span><span class="p">;</span>
  <span class="n">tree</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>

  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="k">if</span> <span class="p">(</span><span class="o">!</span> <span class="n">pcl_isfinite</span> <span class="p">((</span><span class="o">*</span><span class="n">cloud</span><span class="p">)[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span><span class="p">))</span>
    <span class="p">{</span>
      <span class="k">continue</span><span class="p">;</span>
    <span class="p">}</span>
    <span class="c1">//Considering the second neighbor since the first is the point itself.</span>
    <span class="n">nres</span> <span class="o">=</span> <span class="n">tree</span><span class="p">.</span><span class="n">nearestKSearch</span> <span class="p">(</span><span class="n">i</span><span class="p">,</span> <span class="mi">2</span><span class="p">,</span> <span class="n">indices</span><span class="p">,</span> <span class="n">sqr_distances</span><span class="p">);</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">nres</span> <span class="o">==</span> <span class="mi">2</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">res</span> <span class="o">+=</span> <span class="n">sqrt</span> <span class="p">(</span><span class="n">sqr_distances</span><span class="p">[</span><span class="mi">1</span><span class="p">]);</span>
      <span class="o">++</span><span class="n">n_points</span><span class="p">;</span>
    <span class="p">}</span>
  <span class="p">}</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">n_points</span> <span class="o">!=</span> <span class="mi">0</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">res</span> <span class="o">/=</span> <span class="n">n_points</span><span class="p">;</span>
  <span class="p">}</span>
  <span class="k">return</span> <span class="n">res</span><span class="p">;</span>
<span class="p">}</span>
</pre></div>
</div>
</div>
<div class="section" id="clustering-pipeline">
<h2>Clustering Pipeline</h2>
<p>The main function, which performs the actual clustering, is quite straightforward. We will take a look at each part of code as they appear in the proposed example.</p>
<p>First, the program parses the command line arguments and
loads the model and scene clouds from disk (using the filenames
supplied by the user).</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">parseCommandLine</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">);</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="n">model_filename_</span><span class="p">,</span> <span class="o">*</span><span class="n">model</span><span class="p">)</span> <span class="o">&lt;</span> <span class="mi">0</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Error loading model cloud.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="n">showHelp</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="n">scene_filename_</span><span class="p">,</span> <span class="o">*</span><span class="n">scene</span><span class="p">)</span> <span class="o">&lt;</span> <span class="mi">0</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Error loading scene cloud.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="n">showHelp</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
    <span class="k">return</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>As a second step, only if resolution invariance flag has been enabled in the command line, the program adjusts the radii that will be used in the next sections by multiplying them for the estimated model cloud resolution.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="k">if</span> <span class="p">(</span><span class="n">use_cloud_resolution_</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="kt">float</span> <span class="n">resolution</span> <span class="o">=</span> <span class="k">static_cast</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">computeCloudResolution</span> <span class="p">(</span><span class="n">model</span><span class="p">));</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">resolution</span> <span class="o">!=</span> <span class="mf">0.0f</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">model_ss_</span>   <span class="o">*=</span> <span class="n">resolution</span><span class="p">;</span>
      <span class="n">scene_ss_</span>   <span class="o">*=</span> <span class="n">resolution</span><span class="p">;</span>
      <span class="n">rf_rad_</span>     <span class="o">*=</span> <span class="n">resolution</span><span class="p">;</span>
      <span class="n">descr_rad_</span>  <span class="o">*=</span> <span class="n">resolution</span><span class="p">;</span>
      <span class="n">cg_size_</span>    <span class="o">*=</span> <span class="n">resolution</span><span class="p">;</span>
    <span class="p">}</span>

    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Model resolution:       &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">resolution</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Model sampling size:    &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">model_ss_</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Scene sampling size:    &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">scene_ss_</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;LRF support radius:     &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">rf_rad_</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;SHOT descriptor radius: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">descr_rad_</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Clustering bin size:    &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cg_size_</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>Next, it computes the normals for each point of both the model and the scene cloud with the  <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_normal_estimation_o_m_p.html">NormalEstimationOMP</a> estimator, using the 10 nearest neighbors of each point (this parameter seems to be fairly ok for many datasets, not just for the one provided).</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">NormalEstimationOMP</span><span class="o">&lt;</span><span class="n">PointType</span><span class="p">,</span> <span class="n">NormalType</span><span class="o">&gt;</span> <span class="n">norm_est</span><span class="p">;</span>
  <span class="n">norm_est</span><span class="p">.</span><span class="n">setKSearch</span> <span class="p">(</span><span class="mi">10</span><span class="p">);</span>
  <span class="n">norm_est</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">model</span><span class="p">);</span>
  <span class="n">norm_est</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">model_normals</span><span class="p">);</span>

  <span class="n">norm_est</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">scene</span><span class="p">);</span>
  <span class="n">norm_est</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">scene_normals</span><span class="p">);</span>
</pre></div>
</div>
<p>Then it downsamples each cloud in order to find a small number
of keypoints, which will then be associated to a 3D descriptor in order to perform keypoint matching and determine point-to-point correspondences. The radii used for the
<a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_uniform_sampling.html">UniformSampling</a> are either the ones set with the command line switches or the defaults.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">sampled_indices</span><span class="p">;</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">UniformSampling</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="n">uniform_sampling</span><span class="p">;</span>
  <span class="n">uniform_sampling</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">model</span><span class="p">);</span>
  <span class="n">uniform_sampling</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="n">model_ss_</span><span class="p">);</span>
  <span class="n">uniform_sampling</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="n">sampled_indices</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">copyPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">model</span><span class="p">,</span> <span class="n">sampled_indices</span><span class="p">.</span><span class="n">points</span><span class="p">,</span> <span class="o">*</span><span class="n">model_keypoints</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Model total points: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">model</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;; Selected Keypoints: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">model_keypoints</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="n">uniform_sampling</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">scene</span><span class="p">);</span>
  <span class="n">uniform_sampling</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="n">scene_ss_</span><span class="p">);</span>
  <span class="n">uniform_sampling</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="n">sampled_indices</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">copyPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">scene</span><span class="p">,</span> <span class="n">sampled_indices</span><span class="p">.</span><span class="n">points</span><span class="p">,</span> <span class="o">*</span><span class="n">scene_keypoints</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Scene total points: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">scene</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;; Selected Keypoints: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">scene_keypoints</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
</pre></div>
</div>
<p>The next stage consists in associating a 3D descriptor to each model and scene keypoint. In our tutorial, we compute SHOT descriptors using <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_s_h_o_t_estimation_o_m_p.html">SHOTEstimationOMP</a>.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">SHOTEstimationOMP</span><span class="o">&lt;</span><span class="n">PointType</span><span class="p">,</span> <span class="n">NormalType</span><span class="p">,</span> <span class="n">DescriptorType</span><span class="o">&gt;</span> <span class="n">descr_est</span><span class="p">;</span>
  <span class="n">descr_est</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="n">descr_rad_</span><span class="p">);</span>

  <span class="n">descr_est</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">model_keypoints</span><span class="p">);</span>
  <span class="n">descr_est</span><span class="p">.</span><span class="n">setInputNormals</span> <span class="p">(</span><span class="n">model_normals</span><span class="p">);</span>
  <span class="n">descr_est</span><span class="p">.</span><span class="n">setSearchSurface</span> <span class="p">(</span><span class="n">model</span><span class="p">);</span>
  <span class="n">descr_est</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">model_descriptors</span><span class="p">);</span>

  <span class="n">descr_est</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">scene_keypoints</span><span class="p">);</span>
  <span class="n">descr_est</span><span class="p">.</span><span class="n">setInputNormals</span> <span class="p">(</span><span class="n">scene_normals</span><span class="p">);</span>
  <span class="n">descr_est</span><span class="p">.</span><span class="n">setSearchSurface</span> <span class="p">(</span><span class="n">scene</span><span class="p">);</span>
  <span class="n">descr_est</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">scene_descriptors</span><span class="p">);</span>
</pre></div>
</div>
<p>Now we need to determine point-to-point correspondences between
model descriptors and scene descriptors. To do this, the program uses a <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_kd_tree_f_l_a_n_n.html">KdTreeFLANN</a> whose input cloud has been set to the cloud containing the model descriptors.
For each descriptor associated to a scene keypoint, it efficiently finds the most
similar model descriptor based on the Euclidean distance, and it adds this pair to a <a class="reference external" href="http://docs.pointclouds.org/trunk/namespacepcl.html#a66ad9b4a33f4301faff229f2867080a6">Correspondences</a> vector (only if the two descriptors are similar enough, i.e. their squared distance is less than a threshold, set to 0.25).</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">CorrespondencesPtr</span> <span class="n">model_scene_corrs</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Correspondences</span> <span class="p">());</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">KdTreeFLANN</span><span class="o">&lt;</span><span class="n">DescriptorType</span><span class="o">&gt;</span> <span class="n">match_search</span><span class="p">;</span>
  <span class="n">match_search</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">model_descriptors</span><span class="p">);</span>

  <span class="c1">//  For each scene keypoint descriptor, find nearest neighbor into the model keypoints descriptor cloud and add it to the correspondences vector.</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">scene_descriptors</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">neigh_indices</span> <span class="p">(</span><span class="mi">1</span><span class="p">);</span>
    <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="n">neigh_sqr_dists</span> <span class="p">(</span><span class="mi">1</span><span class="p">);</span>
    <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">pcl_isfinite</span> <span class="p">(</span><span class="n">scene_descriptors</span><span class="o">-&gt;</span><span class="n">at</span> <span class="p">(</span><span class="n">i</span><span class="p">).</span><span class="n">descriptor</span><span class="p">[</span><span class="mi">0</span><span class="p">]))</span> <span class="c1">//skipping NaNs</span>
    <span class="p">{</span>
      <span class="k">continue</span><span class="p">;</span>
    <span class="p">}</span>
    <span class="kt">int</span> <span class="n">found_neighs</span> <span class="o">=</span> <span class="n">match_search</span><span class="p">.</span><span class="n">nearestKSearch</span> <span class="p">(</span><span class="n">scene_descriptors</span><span class="o">-&gt;</span><span class="n">at</span> <span class="p">(</span><span class="n">i</span><span class="p">),</span> <span class="mi">1</span><span class="p">,</span> <span class="n">neigh_indices</span><span class="p">,</span> <span class="n">neigh_sqr_dists</span><span class="p">);</span>
    <span class="k">if</span><span class="p">(</span><span class="n">found_neighs</span> <span class="o">==</span> <span class="mi">1</span> <span class="o">&amp;&amp;</span> <span class="n">neigh_sqr_dists</span><span class="p">[</span><span class="mi">0</span><span class="p">]</span> <span class="o">&lt;</span> <span class="mf">0.25f</span><span class="p">)</span> <span class="c1">//  add match only if the squared descriptor distance is less than 0.25 (SHOT descriptor distances are between 0 and 1 by design)</span>
    <span class="p">{</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">Correspondence</span> <span class="n">corr</span> <span class="p">(</span><span class="n">neigh_indices</span><span class="p">[</span><span class="mi">0</span><span class="p">],</span> <span class="k">static_cast</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">i</span><span class="p">),</span> <span class="n">neigh_sqr_dists</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
      <span class="n">model_scene_corrs</span><span class="o">-&gt;</span><span class="n">push_back</span> <span class="p">(</span><span class="n">corr</span><span class="p">);</span>
    <span class="p">}</span>
  <span class="p">}</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Correspondences found: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">model_scene_corrs</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
</pre></div>
</div>
<p>The last stage of the pipeline is the actual clustering of the
previously found correspondences.</p>
<p>The default algorithm is <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_hough3_d_grouping.html">Hough3DGrouping</a>, that is based on an Hough Voting process.
Please note that this algorithm needs to associate a Local Reference Frame (LRF) for each keypoint belonging to the clouds which are passed as arguments!
In this example, we explicitly compute the set of LRFs using the <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_b_o_a_r_d_local_reference_frame_estimation.html">BOARDLocalReferenceFrameEstimation</a> estimator before calling the clustering algorithm.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="k">if</span> <span class="p">(</span><span class="n">use_hough_</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="c1">//</span>
    <span class="c1">//  Compute (Keypoints) Reference Frames only for Hough</span>
    <span class="c1">//</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">RFType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">model_rf</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">RFType</span><span class="o">&gt;</span> <span class="p">());</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">RFType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">scene_rf</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">RFType</span><span class="o">&gt;</span> <span class="p">());</span>

    <span class="n">pcl</span><span class="o">::</span><span class="n">BOARDLocalReferenceFrameEstimation</span><span class="o">&lt;</span><span class="n">PointType</span><span class="p">,</span> <span class="n">NormalType</span><span class="p">,</span> <span class="n">RFType</span><span class="o">&gt;</span> <span class="n">rf_est</span><span class="p">;</span>
    <span class="n">rf_est</span><span class="p">.</span><span class="n">setFindHoles</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
    <span class="n">rf_est</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="n">rf_rad_</span><span class="p">);</span>

    <span class="n">rf_est</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">model_keypoints</span><span class="p">);</span>
    <span class="n">rf_est</span><span class="p">.</span><span class="n">setInputNormals</span> <span class="p">(</span><span class="n">model_normals</span><span class="p">);</span>
    <span class="n">rf_est</span><span class="p">.</span><span class="n">setSearchSurface</span> <span class="p">(</span><span class="n">model</span><span class="p">);</span>
    <span class="n">rf_est</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">model_rf</span><span class="p">);</span>

    <span class="n">rf_est</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">scene_keypoints</span><span class="p">);</span>
    <span class="n">rf_est</span><span class="p">.</span><span class="n">setInputNormals</span> <span class="p">(</span><span class="n">scene_normals</span><span class="p">);</span>
    <span class="n">rf_est</span><span class="p">.</span><span class="n">setSearchSurface</span> <span class="p">(</span><span class="n">scene</span><span class="p">);</span>
    <span class="n">rf_est</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">scene_rf</span><span class="p">);</span>

    <span class="c1">//  Clustering</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">Hough3DGrouping</span><span class="o">&lt;</span><span class="n">PointType</span><span class="p">,</span> <span class="n">PointType</span><span class="p">,</span> <span class="n">RFType</span><span class="p">,</span> <span class="n">RFType</span><span class="o">&gt;</span> <span class="n">clusterer</span><span class="p">;</span>
    <span class="n">clusterer</span><span class="p">.</span><span class="n">setHoughBinSize</span> <span class="p">(</span><span class="n">cg_size_</span><span class="p">);</span>
    <span class="n">clusterer</span><span class="p">.</span><span class="n">setHoughThreshold</span> <span class="p">(</span><span class="n">cg_thresh_</span><span class="p">);</span>
    <span class="n">clusterer</span><span class="p">.</span><span class="n">setUseInterpolation</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
    <span class="n">clusterer</span><span class="p">.</span><span class="n">setUseDistanceWeight</span> <span class="p">(</span><span class="nb">false</span><span class="p">);</span>

    <span class="n">clusterer</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">model_keypoints</span><span class="p">);</span>
    <span class="n">clusterer</span><span class="p">.</span><span class="n">setInputRf</span> <span class="p">(</span><span class="n">model_rf</span><span class="p">);</span>
    <span class="n">clusterer</span><span class="p">.</span><span class="n">setSceneCloud</span> <span class="p">(</span><span class="n">scene_keypoints</span><span class="p">);</span>
    <span class="n">clusterer</span><span class="p">.</span><span class="n">setSceneRf</span> <span class="p">(</span><span class="n">scene_rf</span><span class="p">);</span>
    <span class="n">clusterer</span><span class="p">.</span><span class="n">setModelSceneCorrespondences</span> <span class="p">(</span><span class="n">model_scene_corrs</span><span class="p">);</span>

    <span class="c1">//clusterer.cluster (clustered_corrs);</span>
    <span class="n">clusterer</span><span class="p">.</span><span class="n">recognize</span> <span class="p">(</span><span class="n">rototranslations</span><span class="p">,</span> <span class="n">clustered_corrs</span><span class="p">);</span>
  <span class="p">}</span>
</pre></div>
</div>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">It&#8217;s not necessary to explicitly compute the LRFs before calling the clustering algorithm. If the clouds which are fetched to the clustering algorithm do not have a set of LRFs associated, Hough3DGrouping automatically computes them before performing clustering. In particular, this happens when calling the <tt class="docutils literal"><span class="pre">recognize</span></tt> (or <tt class="docutils literal"><span class="pre">cluster</span></tt>) method without setting the LRFs: in this case you need to specify the radius of the LRF as an additional parameter for the clustering algorithm (with the <tt class="docutils literal"><span class="pre">setLocalRfSearchRadius</span></tt> method).</p>
</div>
<p>Alternatively to Hough3DGrouping, and by means of the appropriate command line switch described before, you might choose to employ the <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_geometric_consistency_grouping.html">GeometricConsistencyGrouping</a> algorithm. In this case the LRF computation is not needed so we are simply creating an instance of the algorithm class, passing the right parameters and invoking the <tt class="docutils literal"><span class="pre">recognize</span></tt> method.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="k">else</span> <span class="c1">// Using GeometricConsistency</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">GeometricConsistencyGrouping</span><span class="o">&lt;</span><span class="n">PointType</span><span class="p">,</span> <span class="n">PointType</span><span class="o">&gt;</span> <span class="n">gc_clusterer</span><span class="p">;</span>
    <span class="n">gc_clusterer</span><span class="p">.</span><span class="n">setGCSize</span> <span class="p">(</span><span class="n">cg_size_</span><span class="p">);</span>
    <span class="n">gc_clusterer</span><span class="p">.</span><span class="n">setGCThreshold</span> <span class="p">(</span><span class="n">cg_thresh_</span><span class="p">);</span>

    <span class="n">gc_clusterer</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">model_keypoints</span><span class="p">);</span>
    <span class="n">gc_clusterer</span><span class="p">.</span><span class="n">setSceneCloud</span> <span class="p">(</span><span class="n">scene_keypoints</span><span class="p">);</span>
    <span class="n">gc_clusterer</span><span class="p">.</span><span class="n">setModelSceneCorrespondences</span> <span class="p">(</span><span class="n">model_scene_corrs</span><span class="p">);</span>

    <span class="c1">//gc_clusterer.cluster (clustered_corrs);</span>
    <span class="n">gc_clusterer</span><span class="p">.</span><span class="n">recognize</span> <span class="p">(</span><span class="n">rototranslations</span><span class="p">,</span> <span class="n">clustered_corrs</span><span class="p">);</span>
  <span class="p">}</span>
</pre></div>
</div>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p>The <tt class="docutils literal"><span class="pre">recognize</span></tt> method returns a vector of <tt class="docutils literal"><span class="pre">Eigen::Matrix4f</span></tt> representing a transformation (rotation + translation) for each instance of the model found in the scene (obtained via Absolute Orientation) and a <strong>vector</strong> of <a class="reference external" href="http://docs.pointclouds.org/trunk/namespacepcl.html#a66ad9b4a33f4301faff229f2867080a6">Correspondences</a> (a vector of vectors of <a class="reference external" href="http://docs.pointclouds.org/trunk/namespacepcl.html#a66ad9b4a33f4301faff229f2867080a6">Correspondence</a>) representing the output of the clustering i.e. each element of this vector is in turn a set of correspondences, representing the correspondences associated to a specific model instance in the scene.</p>
<p class="last">If you <strong>only</strong> need the clustered correspondences because you are planning to use them in a different way, you can use the <tt class="docutils literal"><span class="pre">cluster</span></tt> method.</p>
</div>
</div>
<div class="section" id="output-and-visualization">
<h2>Output and Visualization</h2>
<p>We are almost at the end of this tutorial. The last few words are related to the part of the program that displays the results on the console and over a PCL Visualizer window.</p>
<p>As a first thing we are showing, for each instance of the model found into the scene, the transformation  matrix and the number of correspondences extracted by the clustering method.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Model instances found: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">rototranslations</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">rototranslations</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;</span><span class="se">\n</span><span class="s">    Instance &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">i</span> <span class="o">+</span> <span class="mi">1</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;:&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;        Correspondences belonging to this instance: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">clustered_corrs</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

    <span class="c1">// Print the rotation matrix and translation vector</span>
    <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix3f</span> <span class="n">rotation</span> <span class="o">=</span> <span class="n">rototranslations</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">block</span><span class="o">&lt;</span><span class="mi">3</span><span class="p">,</span><span class="mi">3</span><span class="o">&gt;</span><span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
    <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span> <span class="n">translation</span> <span class="o">=</span> <span class="n">rototranslations</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">block</span><span class="o">&lt;</span><span class="mi">3</span><span class="p">,</span><span class="mi">1</span><span class="o">&gt;</span><span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mi">3</span><span class="p">);</span>

    <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
    <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;            | %6.3f %6.3f %6.3f | </span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">0</span><span class="p">),</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">1</span><span class="p">),</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span><span class="mi">2</span><span class="p">));</span>
    <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;        R = | %6.3f %6.3f %6.3f | </span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span><span class="mi">0</span><span class="p">),</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span><span class="mi">1</span><span class="p">),</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span><span class="mi">2</span><span class="p">));</span>
    <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;            | %6.3f %6.3f %6.3f | </span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">2</span><span class="p">,</span><span class="mi">0</span><span class="p">),</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">2</span><span class="p">,</span><span class="mi">1</span><span class="p">),</span> <span class="n">rotation</span> <span class="p">(</span><span class="mi">2</span><span class="p">,</span><span class="mi">2</span><span class="p">));</span>
    <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
    <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;        t = &lt; %0.3f, %0.3f, %0.3f &gt;</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">translation</span> <span class="p">(</span><span class="mi">0</span><span class="p">),</span> <span class="n">translation</span> <span class="p">(</span><span class="mi">1</span><span class="p">),</span> <span class="n">translation</span> <span class="p">(</span><span class="mi">2</span><span class="p">));</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>The program then shows in a <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1visualization_1_1_p_c_l_visualizer.html">PCLVisualizer</a> window the scene cloud with a red overlay where an instance of the model has been found.
If the command line switches <tt class="docutils literal"><span class="pre">-k</span></tt> and <tt class="docutils literal"><span class="pre">-c</span></tt> have been used, the program also shows a &#8220;stand-alone&#8221; rendering of the model cloud. If keypoint visualization is enabled, keypoints are displayed as blue dots and if correspondence visualization has been enabled they are shown as a green line for each correspondence which <em>survived</em> the clustering process.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="n">viewer</span> <span class="p">(</span><span class="s">&quot;Correspondence Grouping&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">scene</span><span class="p">,</span> <span class="s">&quot;scene_cloud&quot;</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">off_scene_model</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">off_scene_model_keypoints</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="p">());</span>

  <span class="k">if</span> <span class="p">(</span><span class="n">show_correspondences_</span> <span class="o">||</span> <span class="n">show_keypoints_</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="c1">//  We are translating the model so that it doesn&#39;t end in the middle of the scene representation</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">transformPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">model</span><span class="p">,</span> <span class="o">*</span><span class="n">off_scene_model</span><span class="p">,</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">,</span><span class="mi">0</span><span class="p">,</span><span class="mi">0</span><span class="p">),</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Quaternionf</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">));</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">transformPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">model_keypoints</span><span class="p">,</span> <span class="o">*</span><span class="n">off_scene_model_keypoints</span><span class="p">,</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">,</span><span class="mi">0</span><span class="p">,</span><span class="mi">0</span><span class="p">),</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Quaternionf</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">));</span>

    <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="n">off_scene_model_color_handler</span> <span class="p">(</span><span class="n">off_scene_model</span><span class="p">,</span> <span class="mi">255</span><span class="p">,</span> <span class="mi">255</span><span class="p">,</span> <span class="mi">128</span><span class="p">);</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">off_scene_model</span><span class="p">,</span> <span class="n">off_scene_model_color_handler</span><span class="p">,</span> <span class="s">&quot;off_scene_model&quot;</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="k">if</span> <span class="p">(</span><span class="n">show_keypoints_</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="n">scene_keypoints_color_handler</span> <span class="p">(</span><span class="n">scene_keypoints</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">255</span><span class="p">);</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">scene_keypoints</span><span class="p">,</span> <span class="n">scene_keypoints_color_handler</span><span class="p">,</span> <span class="s">&quot;scene_keypoints&quot;</span><span class="p">);</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span> <span class="mi">5</span><span class="p">,</span> <span class="s">&quot;scene_keypoints&quot;</span><span class="p">);</span>

    <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="n">off_scene_model_keypoints_color_handler</span> <span class="p">(</span><span class="n">off_scene_model_keypoints</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">255</span><span class="p">);</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">off_scene_model_keypoints</span><span class="p">,</span> <span class="n">off_scene_model_keypoints_color_handler</span><span class="p">,</span> <span class="s">&quot;off_scene_model_keypoints&quot;</span><span class="p">);</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span> <span class="mi">5</span><span class="p">,</span> <span class="s">&quot;off_scene_model_keypoints&quot;</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">rototranslations</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">rotated_model</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="p">());</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">transformPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">model</span><span class="p">,</span> <span class="o">*</span><span class="n">rotated_model</span><span class="p">,</span> <span class="n">rototranslations</span><span class="p">[</span><span class="n">i</span><span class="p">]);</span>

    <span class="n">std</span><span class="o">::</span><span class="n">stringstream</span> <span class="n">ss_cloud</span><span class="p">;</span>
    <span class="n">ss_cloud</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;instance&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">i</span><span class="p">;</span>

    <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="n">rotated_model_color_handler</span> <span class="p">(</span><span class="n">rotated_model</span><span class="p">,</span> <span class="mi">255</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">rotated_model</span><span class="p">,</span> <span class="n">rotated_model_color_handler</span><span class="p">,</span> <span class="n">ss_cloud</span><span class="p">.</span><span class="n">str</span> <span class="p">());</span>

    <span class="k">if</span> <span class="p">(</span><span class="n">show_correspondences_</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">j</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">j</span> <span class="o">&lt;</span> <span class="n">clustered_corrs</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">j</span><span class="p">)</span>
      <span class="p">{</span>
        <span class="n">std</span><span class="o">::</span><span class="n">stringstream</span> <span class="n">ss_line</span><span class="p">;</span>
        <span class="n">ss_line</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;correspondence_line&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">i</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;_&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">j</span><span class="p">;</span>
        <span class="n">PointType</span><span class="o">&amp;</span> <span class="n">model_point</span> <span class="o">=</span> <span class="n">off_scene_model_keypoints</span><span class="o">-&gt;</span><span class="n">at</span> <span class="p">(</span><span class="n">clustered_corrs</span><span class="p">[</span><span class="n">i</span><span class="p">][</span><span class="n">j</span><span class="p">].</span><span class="n">index_query</span><span class="p">);</span>
        <span class="n">PointType</span><span class="o">&amp;</span> <span class="n">scene_point</span> <span class="o">=</span> <span class="n">scene_keypoints</span><span class="o">-&gt;</span><span class="n">at</span> <span class="p">(</span><span class="n">clustered_corrs</span><span class="p">[</span><span class="n">i</span><span class="p">][</span><span class="n">j</span><span class="p">].</span><span class="n">index_match</span><span class="p">);</span>

        <span class="c1">//  We are drawing a line for each pair of clustered correspondences found between the model and the scene</span>
        <span class="n">viewer</span><span class="p">.</span><span class="n">addLine</span><span class="o">&lt;</span><span class="n">PointType</span><span class="p">,</span> <span class="n">PointType</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">model_point</span><span class="p">,</span> <span class="n">scene_point</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">255</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="n">ss_line</span><span class="p">.</span><span class="n">str</span> <span class="p">());</span>
      <span class="p">}</span>
    <span class="p">}</span>
  <span class="p">}</span>

  <span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">viewer</span><span class="p">.</span><span class="n">wasStopped</span> <span class="p">())</span>
  <span class="p">{</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">spinOnce</span> <span class="p">();</span>
  <span class="p">}</span>
</pre></div>
</div>
</div>
</div>
<div class="section" id="compiling-and-running-the-program">
<h1>Compiling and running the program</h1>
<p>Create a <cite>CMakeLists.txt</cite> file and add the following lines into it:</p>
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

<span class="nb">project</span><span class="p">(</span><span class="s">correspondence_grouping</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.5</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">correspondence_grouping</span> <span class="s">correspondence_grouping.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">correspondence_grouping</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have created the executable, you can then launch it following this example:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./correspondence_grouping milk.pcd milk_cartoon_all_small_clorox.pcd
</pre></div>
</div>
<p>Or, alternatively, if you prefer specifying the radii in units of cloud resolution:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./correspondence_grouping milk.pcd milk_cartoon_all_small_clorox.pcd milk.pcd milk_cartoon_all_small_clorox.pcd -r --model_ss 7.5 --scene_ss 20 --rf_rad 10 --descr_rad 15 --cg_size 10
</pre></div>
</div>
<p>Remember to replace <tt class="docutils literal"><span class="pre">milk.pcd</span></tt> and <tt class="docutils literal"><span class="pre">milk_cartoon_all_small_clorox.pcd</span></tt> with model and scene filenames, in this exact order. If you want you can add other command line options as described at the beginning of this tutorial.</p>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">If you are using different point clouds and you don&#8217;t know how to set the various parameters for this tutorial you can use the <tt class="docutils literal"><span class="pre">-r</span></tt> flag and try setting the LRF and descriptor radii to 5, 10, 15 or 20 times the actual cloud resolution. After that you probably will have to tweak the values by hand to achieve the best results.</p>
</div>
<p>After a few seconds, you will see an output similar to:</p>
<div class="highlight-python"><div class="highlight"><pre>Model total points: 13704; Selected Keypoints: 732
Scene total points: 307200; Selected Keypoints: 3747

Correspondences found: 1768
Model instances found: 1

  Instance 1:
    Correspondences belonging to this instance: 24

        |  0.969 -0.120  0.217 |
    R = |  0.117  0.993  0.026 |
        | -0.218 -0.000  0.976 |

    t = &lt; -0.159, 0.212, -0.042 &gt;
</pre></div>
</div>
<p>The output window should look like this (depending on the command line options used):</p>
<a class="reference internal image-reference" href="_images/correspondence_grouping.jpg"><img alt="_images/correspondence_grouping.jpg" src="_images/correspondence_grouping.jpg" style="height: 400px;" /></a>
<a class="reference internal image-reference" href="_images/correspondence_grouping_k.jpg"><img alt="_images/correspondence_grouping_k.jpg" src="_images/correspondence_grouping_k.jpg" style="height: 400px;" /></a>
<a class="reference internal image-reference" href="_images/correspondence_grouping_c.jpg"><img alt="_images/correspondence_grouping_c.jpg" src="_images/correspondence_grouping_c.jpg" style="height: 400px;" /></a>
<a class="reference internal image-reference" href="_images/correspondence_grouping_k_c.jpg"><img alt="_images/correspondence_grouping_k_c.jpg" src="_images/correspondence_grouping_k_c.jpg" style="height: 400px;" /></a>
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