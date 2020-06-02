<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation - Point Cloud Library (PCL)</title>
</head>

<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8" />
    <title>Tutorial: Hypothesis Verification for 3D Object Recognition &#8212; PCL 0.0 documentation</title>
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
            
  <div class="section" id="tutorial-hypothesis-verification-for-3d-object-recognition">
<span id="global-hypothesis-verification"></span><h1>Tutorial: Hypothesis Verification for 3D Object Recognition</h1>
<p>This tutorial aims at explaining how to do 3D object recognition in clutter by verifying model hypotheses
in cluttered and heavily occluded 3D scenes. After descriptor matching, the tutorial runs one of the
Correspondence Grouping algorithms available in PCL in order to cluster the set of point-to-point
correspondences, determining instances of object hypotheses in the scene. On these hypotheses,
the Global Hypothesis Verification algorithm is applied in order to
decrease the amount of false positives.</p>
</div>
<div class="section" id="suggested-readings-and-prerequisites">
<h1>Suggested readings and prerequisites</h1>
<p>This tutorial is the follow-up of a previous tutorial on object recognition: <a class="reference internal" href="correspondence_grouping.php#correspondence-grouping"><span class="std std-ref">3D Object Recognition based on Correspondence Grouping</span></a>
To understand this tutorial, we suggest first to read and understand that tutorial.</p>
<p>More details on the Global Hypothesis Verification method can be found here:
A. Aldoma, F. Tombari, L. Di Stefano, M. Vincze, <cite>A global hypothesis verification method for 3D object recognition</cite>, ECCV 2012</p>
<p>For more information on 3D Object Recognition in Clutter and on the standard feature-based recognition pipeline, we suggest this tutorial paper:
A. Aldoma, Z.C. Marton, F. Tombari, W. Wohlkinger, C. Potthast, B. Zeisl, R.B. Rusu, S. Gedikli, M. Vincze, “Point Cloud Library: Three-Dimensional Object Recognition and 6 DOF Pose Estimation”, IEEE Robotics and Automation Magazine, 2012</p>
</div>
<div class="section" id="the-code">
<h1>The Code</h1>
<p>Before starting, you should download from the GitHub folder: <a class="reference external" href="https://github.com/PointCloudLibrary/data/tree/master/tutorials/correspondence_grouping">Correspondence Grouping</a> the example PCD clouds
used in this tutorial (milk.pcd and milk_cartoon_all_small_clorox.pcd), and place the files in the source older.</p>
<p>Then copy and paste the following code into your editor and save it as <code class="docutils literal notranslate"><span class="pre">global_hypothesis_verification.cpp</span></code>.</p>
<div class="highlight-c++ notranslate"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre>  1
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
424
425
426
427
428
429
430
431
432
433
434
435
436
437
438
439
440
441
442
443
444
445
446
447
448
449
450
451
452
453
454
455
456
457
458
459
460
461
462
463
464
465
466
467
468
469
470
471
472
473
474
475
476
477
478
479
480
481
482
483
484
485
486
487
488
489
490
491
492
493
494
495
496
497
498
499
500
501
502
503
504
505
506
507
508
509
510
511
512
513
514
515
516
517
518
519
520
521
522
523
524
525
526
527</pre></div></td><td class="code"><div class="highlight"><pre><span></span><span class="cm">/*</span>
<span class="cm"> * Software License Agreement (BSD License)</span>
<span class="cm"> *</span>
<span class="cm"> *  Point Cloud Library (PCL) - www.pointclouds.org</span>
<span class="cm"> *  Copyright (c) 2014-, Open Perception, Inc.</span>
<span class="cm"> *</span>
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
<span class="cm"> *   * Neither the name of the copyright holder(s) nor the names of its</span>
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
<span class="cm"> */</span>

<span class="cp">#include</span> <span class="cpf">&lt;pcl/io/pcd_io.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/point_cloud.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/correspondence.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/features/normal_3d_omp.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/features/shot_omp.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/features/board.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/filters/uniform_sampling.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/recognition/cg/hough_3d.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/recognition/cg/geometric_consistency.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/recognition/hv/hv_go.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/registration/icp.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/visualization/pcl_visualizer.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/kdtree/kdtree_flann.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/kdtree/impl/kdtree_flann.hpp&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/common/transforms.h&gt; </span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/console/parse.h&gt;</span><span class="cp"></span>

<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span> <span class="n">PointType</span><span class="p">;</span>
<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span> <span class="n">NormalType</span><span class="p">;</span>
<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">ReferenceFrame</span> <span class="n">RFType</span><span class="p">;</span>
<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">SHOT352</span> <span class="n">DescriptorType</span><span class="p">;</span>

<span class="k">struct</span> <span class="n">CloudStyle</span>
<span class="p">{</span>
    <span class="kt">double</span> <span class="n">r</span><span class="p">;</span>
    <span class="kt">double</span> <span class="n">g</span><span class="p">;</span>
    <span class="kt">double</span> <span class="n">b</span><span class="p">;</span>
    <span class="kt">double</span> <span class="n">size</span><span class="p">;</span>

    <span class="n">CloudStyle</span> <span class="p">(</span><span class="kt">double</span> <span class="n">r</span><span class="p">,</span>
                <span class="kt">double</span> <span class="n">g</span><span class="p">,</span>
                <span class="kt">double</span> <span class="n">b</span><span class="p">,</span>
                <span class="kt">double</span> <span class="n">size</span><span class="p">)</span> <span class="o">:</span>
        <span class="n">r</span> <span class="p">(</span><span class="n">r</span><span class="p">),</span>
        <span class="n">g</span> <span class="p">(</span><span class="n">g</span><span class="p">),</span>
        <span class="n">b</span> <span class="p">(</span><span class="n">b</span><span class="p">),</span>
        <span class="n">size</span> <span class="p">(</span><span class="n">size</span><span class="p">)</span>
    <span class="p">{</span>
    <span class="p">}</span>
<span class="p">};</span>

<span class="n">CloudStyle</span> <span class="nf">style_white</span> <span class="p">(</span><span class="mf">255.0</span><span class="p">,</span> <span class="mf">255.0</span><span class="p">,</span> <span class="mf">255.0</span><span class="p">,</span> <span class="mf">4.0</span><span class="p">);</span>
<span class="n">CloudStyle</span> <span class="nf">style_red</span> <span class="p">(</span><span class="mf">255.0</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mf">3.0</span><span class="p">);</span>
<span class="n">CloudStyle</span> <span class="nf">style_green</span> <span class="p">(</span><span class="mf">0.0</span><span class="p">,</span> <span class="mf">255.0</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mf">5.0</span><span class="p">);</span>
<span class="n">CloudStyle</span> <span class="nf">style_cyan</span> <span class="p">(</span><span class="mf">93.0</span><span class="p">,</span> <span class="mf">200.0</span><span class="p">,</span> <span class="mf">217.0</span><span class="p">,</span> <span class="mf">4.0</span><span class="p">);</span>
<span class="n">CloudStyle</span> <span class="nf">style_violet</span> <span class="p">(</span><span class="mf">255.0</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mf">255.0</span><span class="p">,</span> <span class="mf">8.0</span><span class="p">);</span>

<span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">model_filename_</span><span class="p">;</span>
<span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">scene_filename_</span><span class="p">;</span>

<span class="c1">//Algorithm params </span>
<span class="kt">bool</span> <span class="nf">show_keypoints_</span> <span class="p">(</span><span class="nb">false</span><span class="p">);</span>
<span class="kt">bool</span> <span class="nf">use_hough_</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
<span class="kt">float</span> <span class="nf">model_ss_</span> <span class="p">(</span><span class="mf">0.02f</span><span class="p">);</span>
<span class="kt">float</span> <span class="nf">scene_ss_</span> <span class="p">(</span><span class="mf">0.02f</span><span class="p">);</span>
<span class="kt">float</span> <span class="nf">rf_rad_</span> <span class="p">(</span><span class="mf">0.015f</span><span class="p">);</span>
<span class="kt">float</span> <span class="nf">descr_rad_</span> <span class="p">(</span><span class="mf">0.02f</span><span class="p">);</span>
<span class="kt">float</span> <span class="nf">cg_size_</span> <span class="p">(</span><span class="mf">0.01f</span><span class="p">);</span>
<span class="kt">float</span> <span class="nf">cg_thresh_</span> <span class="p">(</span><span class="mf">5.0f</span><span class="p">);</span>
<span class="kt">int</span> <span class="nf">icp_max_iter_</span> <span class="p">(</span><span class="mi">5</span><span class="p">);</span>
<span class="kt">float</span> <span class="nf">icp_corr_distance_</span> <span class="p">(</span><span class="mf">0.005f</span><span class="p">);</span>
<span class="kt">float</span> <span class="nf">hv_resolution_</span> <span class="p">(</span><span class="mf">0.005f</span><span class="p">);</span>
<span class="kt">float</span> <span class="nf">hv_occupancy_grid_resolution_</span> <span class="p">(</span><span class="mf">0.01f</span><span class="p">);</span>
<span class="kt">float</span> <span class="nf">hv_clutter_reg_</span> <span class="p">(</span><span class="mf">5.0f</span><span class="p">);</span>
<span class="kt">float</span> <span class="nf">hv_inlier_th_</span> <span class="p">(</span><span class="mf">0.005f</span><span class="p">);</span>
<span class="kt">float</span> <span class="nf">hv_occlusion_th_</span> <span class="p">(</span><span class="mf">0.01f</span><span class="p">);</span>
<span class="kt">float</span> <span class="nf">hv_rad_clutter_</span> <span class="p">(</span><span class="mf">0.03f</span><span class="p">);</span>
<span class="kt">float</span> <span class="nf">hv_regularizer_</span> <span class="p">(</span><span class="mf">3.0f</span><span class="p">);</span>
<span class="kt">float</span> <span class="nf">hv_rad_normals_</span> <span class="p">(</span><span class="mf">0.05</span><span class="p">);</span>
<span class="kt">bool</span> <span class="nf">hv_detect_clutter_</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>

<span class="cm">/**</span>
<span class="cm"> * Prints out Help message</span>
<span class="cm"> * @param filename Runnable App Name</span>
<span class="cm"> */</span>
<span class="kt">void</span>
<span class="nf">showHelp</span> <span class="p">(</span><span class="kt">char</span> <span class="o">*</span><span class="n">filename</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;***************************************************************************&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;*                                                                         *&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;*          Global Hypothese Verification Tutorial - Usage Guide          *&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;*                                                                         *&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;***************************************************************************&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Usage: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">filename</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; model_filename.pcd scene_filename.pcd [Options]&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Options:&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     -h:                          Show this help.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     -k:                          Show keypoints.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     --algorithm (Hough|GC):      Clustering algorithm used (default Hough).&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     --model_ss val:              Model uniform sampling radius (default &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">model_ss_</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     --scene_ss val:              Scene uniform sampling radius (default &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">scene_ss_</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     --rf_rad val:                Reference frame radius (default &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">rf_rad_</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     --descr_rad val:             Descriptor radius (default &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">descr_rad_</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     --cg_size val:               Cluster size (default &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cg_size_</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     --cg_thresh val:             Clustering threshold (default &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cg_thresh_</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     --icp_max_iter val:          ICP max iterations number (default &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">icp_max_iter_</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     --icp_corr_distance val:     ICP correspondence distance (default &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">icp_corr_distance_</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     --hv_clutter_reg val:        Clutter Regularizer (default &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">hv_clutter_reg_</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     --hv_inlier_th val:          Inlier threshold (default &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">hv_inlier_th_</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     --hv_occlusion_th val:       Occlusion threshold (default &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">hv_occlusion_th_</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     --hv_rad_clutter val:        Clutter radius (default &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">hv_rad_clutter_</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     --hv_regularizer val:        Regularizer value (default &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">hv_regularizer_</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     --hv_rad_normals val:        Normals radius (default &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">hv_rad_normals_</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;     --hv_detect_clutter val:     TRUE if clutter detect enabled (default &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">hv_detect_clutter_</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
<span class="p">}</span>

<span class="cm">/**</span>
<span class="cm"> * Parses Command Line Arguments (Argc,Argv)</span>
<span class="cm"> * @param argc</span>
<span class="cm"> * @param argv</span>
<span class="cm"> */</span>
<span class="kt">void</span>
<span class="nf">parseCommandLine</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span>
                  <span class="kt">char</span> <span class="o">*</span><span class="n">argv</span><span class="p">[])</span>
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

  <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">used_algorithm</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--algorithm&quot;</span><span class="p">,</span> <span class="n">used_algorithm</span><span class="p">)</span> <span class="o">!=</span> <span class="o">-</span><span class="mi">1</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">used_algorithm</span><span class="p">.</span><span class="n">compare</span> <span class="p">(</span><span class="s">&quot;Hough&quot;</span><span class="p">)</span> <span class="o">==</span> <span class="mi">0</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">use_hough_</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
    <span class="p">}</span>
    <span class="k">else</span> <span class="k">if</span> <span class="p">(</span><span class="n">used_algorithm</span><span class="p">.</span><span class="n">compare</span> <span class="p">(</span><span class="s">&quot;GC&quot;</span><span class="p">)</span> <span class="o">==</span> <span class="mi">0</span><span class="p">)</span>
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
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--icp_max_iter&quot;</span><span class="p">,</span> <span class="n">icp_max_iter_</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--icp_corr_distance&quot;</span><span class="p">,</span> <span class="n">icp_corr_distance_</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--hv_clutter_reg&quot;</span><span class="p">,</span> <span class="n">hv_clutter_reg_</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--hv_inlier_th&quot;</span><span class="p">,</span> <span class="n">hv_inlier_th_</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--hv_occlusion_th&quot;</span><span class="p">,</span> <span class="n">hv_occlusion_th_</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--hv_rad_clutter&quot;</span><span class="p">,</span> <span class="n">hv_rad_clutter_</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--hv_regularizer&quot;</span><span class="p">,</span> <span class="n">hv_regularizer_</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--hv_rad_normals&quot;</span><span class="p">,</span> <span class="n">hv_rad_normals_</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--hv_detect_clutter&quot;</span><span class="p">,</span> <span class="n">hv_detect_clutter_</span><span class="p">);</span>
<span class="p">}</span>

<span class="kt">int</span>
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span>
      <span class="kt">char</span> <span class="o">*</span><span class="n">argv</span><span class="p">[])</span>
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

  <span class="cm">/**</span>
<span class="cm">   * Load Clouds</span>
<span class="cm">   */</span>
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

  <span class="cm">/**</span>
<span class="cm">   * Compute Normals</span>
<span class="cm">   */</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">NormalEstimationOMP</span><span class="o">&lt;</span><span class="n">PointType</span><span class="p">,</span> <span class="n">NormalType</span><span class="o">&gt;</span> <span class="n">norm_est</span><span class="p">;</span>
  <span class="n">norm_est</span><span class="p">.</span><span class="n">setKSearch</span> <span class="p">(</span><span class="mi">10</span><span class="p">);</span>
  <span class="n">norm_est</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">model</span><span class="p">);</span>
  <span class="n">norm_est</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">model_normals</span><span class="p">);</span>

  <span class="n">norm_est</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">scene</span><span class="p">);</span>
  <span class="n">norm_est</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">scene_normals</span><span class="p">);</span>

  <span class="cm">/**</span>
<span class="cm">   *  Downsample Clouds to Extract keypoints</span>
<span class="cm">   */</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">UniformSampling</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="n">uniform_sampling</span><span class="p">;</span>
  <span class="n">uniform_sampling</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">model</span><span class="p">);</span>
  <span class="n">uniform_sampling</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="n">model_ss_</span><span class="p">);</span>
  <span class="n">uniform_sampling</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">model_keypoints</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Model total points: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">model</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;; Selected Keypoints: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">model_keypoints</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="n">uniform_sampling</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">scene</span><span class="p">);</span>
  <span class="n">uniform_sampling</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="n">scene_ss_</span><span class="p">);</span>
  <span class="n">uniform_sampling</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">scene_keypoints</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Scene total points: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">scene</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;; Selected Keypoints: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">scene_keypoints</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="cm">/**</span>
<span class="cm">   *  Compute Descriptor for keypoints</span>
<span class="cm">   */</span>
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

  <span class="cm">/**</span>
<span class="cm">   *  Find Model-Scene Correspondences with KdTree</span>
<span class="cm">   */</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">CorrespondencesPtr</span> <span class="n">model_scene_corrs</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Correspondences</span> <span class="p">());</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">KdTreeFLANN</span><span class="o">&lt;</span><span class="n">DescriptorType</span><span class="o">&gt;</span> <span class="n">match_search</span><span class="p">;</span>
  <span class="n">match_search</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">model_descriptors</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">model_good_keypoints_indices</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">scene_good_keypoints_indices</span><span class="p">;</span>

  <span class="k">for</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">scene_descriptors</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">neigh_indices</span> <span class="p">(</span><span class="mi">1</span><span class="p">);</span>
    <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="n">neigh_sqr_dists</span> <span class="p">(</span><span class="mi">1</span><span class="p">);</span>
    <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">std</span><span class="o">::</span><span class="n">isfinite</span> <span class="p">(</span><span class="n">scene_descriptors</span><span class="o">-&gt;</span><span class="n">at</span> <span class="p">(</span><span class="n">i</span><span class="p">).</span><span class="n">descriptor</span><span class="p">[</span><span class="mi">0</span><span class="p">]))</span>  <span class="c1">//skipping NaNs</span>
    <span class="p">{</span>
      <span class="k">continue</span><span class="p">;</span>
    <span class="p">}</span>
    <span class="kt">int</span> <span class="n">found_neighs</span> <span class="o">=</span> <span class="n">match_search</span><span class="p">.</span><span class="n">nearestKSearch</span> <span class="p">(</span><span class="n">scene_descriptors</span><span class="o">-&gt;</span><span class="n">at</span> <span class="p">(</span><span class="n">i</span><span class="p">),</span> <span class="mi">1</span><span class="p">,</span> <span class="n">neigh_indices</span><span class="p">,</span> <span class="n">neigh_sqr_dists</span><span class="p">);</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">found_neighs</span> <span class="o">==</span> <span class="mi">1</span> <span class="o">&amp;&amp;</span> <span class="n">neigh_sqr_dists</span><span class="p">[</span><span class="mi">0</span><span class="p">]</span> <span class="o">&lt;</span> <span class="mf">0.25f</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">Correspondence</span> <span class="n">corr</span> <span class="p">(</span><span class="n">neigh_indices</span><span class="p">[</span><span class="mi">0</span><span class="p">],</span> <span class="k">static_cast</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">i</span><span class="p">),</span> <span class="n">neigh_sqr_dists</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
      <span class="n">model_scene_corrs</span><span class="o">-&gt;</span><span class="n">push_back</span> <span class="p">(</span><span class="n">corr</span><span class="p">);</span>
      <span class="n">model_good_keypoints_indices</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">corr</span><span class="p">.</span><span class="n">index_query</span><span class="p">);</span>
      <span class="n">scene_good_keypoints_indices</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">corr</span><span class="p">.</span><span class="n">index_match</span><span class="p">);</span>
    <span class="p">}</span>
  <span class="p">}</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">model_good_kp</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">scene_good_kp</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">copyPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">model_keypoints</span><span class="p">,</span> <span class="n">model_good_keypoints_indices</span><span class="p">,</span> <span class="o">*</span><span class="n">model_good_kp</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">copyPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">scene_keypoints</span><span class="p">,</span> <span class="n">scene_good_keypoints_indices</span><span class="p">,</span> <span class="o">*</span><span class="n">scene_good_kp</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Correspondences found: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">model_scene_corrs</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="cm">/**</span>
<span class="cm">   *  Clustering</span>
<span class="cm">   */</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span><span class="p">,</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">aligned_allocator</span><span class="o">&lt;</span><span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="n">rototranslations</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span> <span class="o">&lt;</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Correspondences</span> <span class="o">&gt;</span> <span class="n">clustered_corrs</span><span class="p">;</span>

  <span class="k">if</span> <span class="p">(</span><span class="n">use_hough_</span><span class="p">)</span>
  <span class="p">{</span>
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

    <span class="n">clusterer</span><span class="p">.</span><span class="n">recognize</span> <span class="p">(</span><span class="n">rototranslations</span><span class="p">,</span> <span class="n">clustered_corrs</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="k">else</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">GeometricConsistencyGrouping</span><span class="o">&lt;</span><span class="n">PointType</span><span class="p">,</span> <span class="n">PointType</span><span class="o">&gt;</span> <span class="n">gc_clusterer</span><span class="p">;</span>
    <span class="n">gc_clusterer</span><span class="p">.</span><span class="n">setGCSize</span> <span class="p">(</span><span class="n">cg_size_</span><span class="p">);</span>
    <span class="n">gc_clusterer</span><span class="p">.</span><span class="n">setGCThreshold</span> <span class="p">(</span><span class="n">cg_thresh_</span><span class="p">);</span>

    <span class="n">gc_clusterer</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">model_keypoints</span><span class="p">);</span>
    <span class="n">gc_clusterer</span><span class="p">.</span><span class="n">setSceneCloud</span> <span class="p">(</span><span class="n">scene_keypoints</span><span class="p">);</span>
    <span class="n">gc_clusterer</span><span class="p">.</span><span class="n">setModelSceneCorrespondences</span> <span class="p">(</span><span class="n">model_scene_corrs</span><span class="p">);</span>

    <span class="n">gc_clusterer</span><span class="p">.</span><span class="n">recognize</span> <span class="p">(</span><span class="n">rototranslations</span><span class="p">,</span> <span class="n">clustered_corrs</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="cm">/**</span>
<span class="cm">   * Stop if no instances</span>
<span class="cm">   */</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">rototranslations</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;=</span> <span class="mi">0</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;*** No instances found! ***&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="k">return</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="k">else</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Recognized Instances: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">rototranslations</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="p">}</span>

  <span class="cm">/**</span>
<span class="cm">   * Generates clouds for each instances found </span>
<span class="cm">   */</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;::</span><span class="n">ConstPtr</span><span class="o">&gt;</span> <span class="n">instances</span><span class="p">;</span>

  <span class="k">for</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">rototranslations</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">rotated_model</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="p">());</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">transformPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">model</span><span class="p">,</span> <span class="o">*</span><span class="n">rotated_model</span><span class="p">,</span> <span class="n">rototranslations</span><span class="p">[</span><span class="n">i</span><span class="p">]);</span>
    <span class="n">instances</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">rotated_model</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="cm">/**</span>
<span class="cm">   * ICP</span>
<span class="cm">   */</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;::</span><span class="n">ConstPtr</span><span class="o">&gt;</span> <span class="n">registered_instances</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="nb">true</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;--- ICP ---------&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

    <span class="k">for</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">rototranslations</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">IterativeClosestPoint</span><span class="o">&lt;</span><span class="n">PointType</span><span class="p">,</span> <span class="n">PointType</span><span class="o">&gt;</span> <span class="n">icp</span><span class="p">;</span>
      <span class="n">icp</span><span class="p">.</span><span class="n">setMaximumIterations</span> <span class="p">(</span><span class="n">icp_max_iter_</span><span class="p">);</span>
      <span class="n">icp</span><span class="p">.</span><span class="n">setMaxCorrespondenceDistance</span> <span class="p">(</span><span class="n">icp_corr_distance_</span><span class="p">);</span>
      <span class="n">icp</span><span class="p">.</span><span class="n">setInputTarget</span> <span class="p">(</span><span class="n">scene</span><span class="p">);</span>
      <span class="n">icp</span><span class="p">.</span><span class="n">setInputSource</span> <span class="p">(</span><span class="n">instances</span><span class="p">[</span><span class="n">i</span><span class="p">]);</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">registered</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span><span class="p">);</span>
      <span class="n">icp</span><span class="p">.</span><span class="n">align</span> <span class="p">(</span><span class="o">*</span><span class="n">registered</span><span class="p">);</span>
      <span class="n">registered_instances</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">registered</span><span class="p">);</span>
      <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Instance &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">i</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; &quot;</span><span class="p">;</span>
      <span class="k">if</span> <span class="p">(</span><span class="n">icp</span><span class="p">.</span><span class="n">hasConverged</span> <span class="p">())</span>
      <span class="p">{</span>
        <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Aligned!&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
      <span class="p">}</span>
      <span class="k">else</span>
      <span class="p">{</span>
        <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Not Aligned!&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
      <span class="p">}</span>
    <span class="p">}</span>

    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;-----------------&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="p">}</span>

  <span class="cm">/**</span>
<span class="cm">   * Hypothesis Verification</span>
<span class="cm">   */</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;--- Hypotheses Verification ---&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">bool</span><span class="o">&gt;</span> <span class="n">hypotheses_mask</span><span class="p">;</span>  <span class="c1">// Mask Vector to identify positive hypotheses</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">GlobalHypothesesVerification</span><span class="o">&lt;</span><span class="n">PointType</span><span class="p">,</span> <span class="n">PointType</span><span class="o">&gt;</span> <span class="n">GoHv</span><span class="p">;</span>

  <span class="n">GoHv</span><span class="p">.</span><span class="n">setSceneCloud</span> <span class="p">(</span><span class="n">scene</span><span class="p">);</span>  <span class="c1">// Scene Cloud</span>
  <span class="n">GoHv</span><span class="p">.</span><span class="n">addModels</span> <span class="p">(</span><span class="n">registered_instances</span><span class="p">,</span> <span class="nb">true</span><span class="p">);</span>  <span class="c1">//Models to verify</span>
  <span class="n">GoHv</span><span class="p">.</span><span class="n">setResolution</span> <span class="p">(</span><span class="n">hv_resolution_</span><span class="p">);</span>
  <span class="n">GoHv</span><span class="p">.</span><span class="n">setResolutionOccupancyGrid</span> <span class="p">(</span><span class="n">hv_occupancy_grid_resolution_</span><span class="p">);</span>
  <span class="n">GoHv</span><span class="p">.</span><span class="n">setInlierThreshold</span> <span class="p">(</span><span class="n">hv_inlier_th_</span><span class="p">);</span>
  <span class="n">GoHv</span><span class="p">.</span><span class="n">setOcclusionThreshold</span> <span class="p">(</span><span class="n">hv_occlusion_th_</span><span class="p">);</span>
  <span class="n">GoHv</span><span class="p">.</span><span class="n">setRegularizer</span> <span class="p">(</span><span class="n">hv_regularizer_</span><span class="p">);</span>
  <span class="n">GoHv</span><span class="p">.</span><span class="n">setRadiusClutter</span> <span class="p">(</span><span class="n">hv_rad_clutter_</span><span class="p">);</span>
  <span class="n">GoHv</span><span class="p">.</span><span class="n">setClutterRegularizer</span> <span class="p">(</span><span class="n">hv_clutter_reg_</span><span class="p">);</span>
  <span class="n">GoHv</span><span class="p">.</span><span class="n">setDetectClutter</span> <span class="p">(</span><span class="n">hv_detect_clutter_</span><span class="p">);</span>
  <span class="n">GoHv</span><span class="p">.</span><span class="n">setRadiusNormals</span> <span class="p">(</span><span class="n">hv_rad_normals_</span><span class="p">);</span>

  <span class="n">GoHv</span><span class="p">.</span><span class="n">verify</span> <span class="p">();</span>
  <span class="n">GoHv</span><span class="p">.</span><span class="n">getMask</span> <span class="p">(</span><span class="n">hypotheses_mask</span><span class="p">);</span>  <span class="c1">// i-element TRUE if hvModels[i] verifies hypotheses</span>

  <span class="k">for</span> <span class="p">(</span><span class="kt">int</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">hypotheses_mask</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="n">i</span><span class="o">++</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">hypotheses_mask</span><span class="p">[</span><span class="n">i</span><span class="p">])</span>
    <span class="p">{</span>
      <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Instance &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">i</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; is GOOD! &lt;---&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="p">}</span>
    <span class="k">else</span>
    <span class="p">{</span>
      <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Instance &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">i</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; is bad!&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="p">}</span>
  <span class="p">}</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;-------------------------------&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="cm">/**</span>
<span class="cm">   *  Visualization</span>
<span class="cm">   */</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="n">viewer</span> <span class="p">(</span><span class="s">&quot;Hypotheses Verification&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">scene</span><span class="p">,</span> <span class="s">&quot;scene_cloud&quot;</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">off_scene_model</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">off_scene_model_keypoints</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="p">());</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">off_model_good_kp</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">transformPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">model</span><span class="p">,</span> <span class="o">*</span><span class="n">off_scene_model</span><span class="p">,</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">),</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Quaternionf</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">));</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">transformPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">model_keypoints</span><span class="p">,</span> <span class="o">*</span><span class="n">off_scene_model_keypoints</span><span class="p">,</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">),</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Quaternionf</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">));</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">transformPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">model_good_kp</span><span class="p">,</span> <span class="o">*</span><span class="n">off_model_good_kp</span><span class="p">,</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3f</span> <span class="p">(</span><span class="o">-</span><span class="mi">1</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">),</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Quaternionf</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">));</span>

  <span class="k">if</span> <span class="p">(</span><span class="n">show_keypoints_</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">CloudStyle</span> <span class="n">modelStyle</span> <span class="o">=</span> <span class="n">style_white</span><span class="p">;</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="n">off_scene_model_color_handler</span> <span class="p">(</span><span class="n">off_scene_model</span><span class="p">,</span> <span class="n">modelStyle</span><span class="p">.</span><span class="n">r</span><span class="p">,</span> <span class="n">modelStyle</span><span class="p">.</span><span class="n">g</span><span class="p">,</span> <span class="n">modelStyle</span><span class="p">.</span><span class="n">b</span><span class="p">);</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">off_scene_model</span><span class="p">,</span> <span class="n">off_scene_model_color_handler</span><span class="p">,</span> <span class="s">&quot;off_scene_model&quot;</span><span class="p">);</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span> <span class="n">modelStyle</span><span class="p">.</span><span class="n">size</span><span class="p">,</span> <span class="s">&quot;off_scene_model&quot;</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="k">if</span> <span class="p">(</span><span class="n">show_keypoints_</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">CloudStyle</span> <span class="n">goodKeypointStyle</span> <span class="o">=</span> <span class="n">style_violet</span><span class="p">;</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="n">model_good_keypoints_color_handler</span> <span class="p">(</span><span class="n">off_model_good_kp</span><span class="p">,</span> <span class="n">goodKeypointStyle</span><span class="p">.</span><span class="n">r</span><span class="p">,</span> <span class="n">goodKeypointStyle</span><span class="p">.</span><span class="n">g</span><span class="p">,</span>
                                                                                                    <span class="n">goodKeypointStyle</span><span class="p">.</span><span class="n">b</span><span class="p">);</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">off_model_good_kp</span><span class="p">,</span> <span class="n">model_good_keypoints_color_handler</span><span class="p">,</span> <span class="s">&quot;model_good_keypoints&quot;</span><span class="p">);</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span> <span class="n">goodKeypointStyle</span><span class="p">.</span><span class="n">size</span><span class="p">,</span> <span class="s">&quot;model_good_keypoints&quot;</span><span class="p">);</span>

    <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="n">scene_good_keypoints_color_handler</span> <span class="p">(</span><span class="n">scene_good_kp</span><span class="p">,</span> <span class="n">goodKeypointStyle</span><span class="p">.</span><span class="n">r</span><span class="p">,</span> <span class="n">goodKeypointStyle</span><span class="p">.</span><span class="n">g</span><span class="p">,</span>
                                                                                                    <span class="n">goodKeypointStyle</span><span class="p">.</span><span class="n">b</span><span class="p">);</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">scene_good_kp</span><span class="p">,</span> <span class="n">scene_good_keypoints_color_handler</span><span class="p">,</span> <span class="s">&quot;scene_good_keypoints&quot;</span><span class="p">);</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span> <span class="n">goodKeypointStyle</span><span class="p">.</span><span class="n">size</span><span class="p">,</span> <span class="s">&quot;scene_good_keypoints&quot;</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="k">for</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">instances</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">stringstream</span> <span class="n">ss_instance</span><span class="p">;</span>
    <span class="n">ss_instance</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;instance_&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">i</span><span class="p">;</span>

    <span class="n">CloudStyle</span> <span class="n">clusterStyle</span> <span class="o">=</span> <span class="n">style_red</span><span class="p">;</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="n">instance_color_handler</span> <span class="p">(</span><span class="n">instances</span><span class="p">[</span><span class="n">i</span><span class="p">],</span> <span class="n">clusterStyle</span><span class="p">.</span><span class="n">r</span><span class="p">,</span> <span class="n">clusterStyle</span><span class="p">.</span><span class="n">g</span><span class="p">,</span> <span class="n">clusterStyle</span><span class="p">.</span><span class="n">b</span><span class="p">);</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">instances</span><span class="p">[</span><span class="n">i</span><span class="p">],</span> <span class="n">instance_color_handler</span><span class="p">,</span> <span class="n">ss_instance</span><span class="p">.</span><span class="n">str</span> <span class="p">());</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span> <span class="n">clusterStyle</span><span class="p">.</span><span class="n">size</span><span class="p">,</span> <span class="n">ss_instance</span><span class="p">.</span><span class="n">str</span> <span class="p">());</span>

    <span class="n">CloudStyle</span> <span class="n">registeredStyles</span> <span class="o">=</span> <span class="n">hypotheses_mask</span><span class="p">[</span><span class="n">i</span><span class="p">]</span> <span class="o">?</span> <span class="nl">style_green</span> <span class="p">:</span> <span class="n">style_cyan</span><span class="p">;</span>
    <span class="n">ss_instance</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;_registered&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="n">registered_instance_color_handler</span> <span class="p">(</span><span class="n">registered_instances</span><span class="p">[</span><span class="n">i</span><span class="p">],</span> <span class="n">registeredStyles</span><span class="p">.</span><span class="n">r</span><span class="p">,</span>
                                                                                                   <span class="n">registeredStyles</span><span class="p">.</span><span class="n">g</span><span class="p">,</span> <span class="n">registeredStyles</span><span class="p">.</span><span class="n">b</span><span class="p">);</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">registered_instances</span><span class="p">[</span><span class="n">i</span><span class="p">],</span> <span class="n">registered_instance_color_handler</span><span class="p">,</span> <span class="n">ss_instance</span><span class="p">.</span><span class="n">str</span> <span class="p">());</span>
    <span class="n">viewer</span><span class="p">.</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span> <span class="n">registeredStyles</span><span class="p">.</span><span class="n">size</span><span class="p">,</span> <span class="n">ss_instance</span><span class="p">.</span><span class="n">str</span> <span class="p">());</span>
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
<p>Take a look at the various parts of the code to see how it works.</p>
<div class="section" id="input-parameters">
<h2>Input Parameters</h2>
<div class="highlight-c++ notranslate"><div class="highlight"><pre><span></span><span class="kt">bool</span> <span class="nf">hv_detect_clutter_</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>

<span class="cm">/**</span>
<span class="cm"> * Prints out Help message</span>
<span class="cm"> * @param filename Runnable App Name</span>
<span class="cm"> */</span>
</pre></div>
</div>
<div class="highlight-c++ notranslate"><div class="highlight"><pre><span></span><span class="p">}</span>

<span class="cm">/**</span>
<span class="cm"> * Parses Command Line Arguments (Argc,Argv)</span>
<span class="cm"> * @param argc</span>
<span class="cm"> * @param argv</span>
<span class="cm"> */</span>
<span class="kt">void</span>
</pre></div>
</div>
<p><code class="docutils literal notranslate"><span class="pre">showHelp</span></code> function prints out the input parameters accepted by the program. <code class="docutils literal notranslate"><span class="pre">parseCommandLine</span></code> binds the user input with program parameters.</p>
<p>The only two mandatory parameters are <code class="docutils literal notranslate"><span class="pre">model_filename</span></code> and <code class="docutils literal notranslate"><span class="pre">scene_filename</span></code> (all other parameters are initialized with a default value).
Other usefuls commands are:</p>
<ul class="simple">
<li><p><code class="docutils literal notranslate"><span class="pre">--algorithm</span> <span class="pre">(Hough|GC)</span></code> used to switch clustering algorithm. See <a class="reference internal" href="correspondence_grouping.php#correspondence-grouping"><span class="std std-ref">3D Object Recognition based on Correspondence Grouping</span></a>.</p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">-k</span></code> shows the keypoints used to compute the correspondences</p></li>
</ul>
<p>Hypotheses Verification parameters are:</p>
<ul class="simple">
<li><p><code class="docutils literal notranslate"><span class="pre">--hv_clutter_reg</span> <span class="pre">val:</span>&#160;&#160;&#160;&#160;&#160;&#160;&#160; <span class="pre">Clutter</span> <span class="pre">Regularizer</span> <span class="pre">(default</span> <span class="pre">5.0)</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">--hv_inlier_th</span> <span class="pre">val:</span>&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160; <span class="pre">Inlier</span> <span class="pre">threshold</span> <span class="pre">(default</span> <span class="pre">0.005)</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">--hv_occlusion_th</span> <span class="pre">val:</span>&#160;&#160;&#160;&#160;&#160;&#160; <span class="pre">Occlusion</span> <span class="pre">threshold</span> <span class="pre">(default</span> <span class="pre">0.01)</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">--hv_rad_clutter</span> <span class="pre">val:</span>&#160;&#160;&#160;&#160;&#160;&#160;&#160; <span class="pre">Clutter</span> <span class="pre">radius</span> <span class="pre">(default</span> <span class="pre">0.03)</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">--hv_regularizer</span> <span class="pre">val:</span>&#160;&#160;&#160;&#160;&#160;&#160;&#160; <span class="pre">Regularizer</span> <span class="pre">value</span> <span class="pre">(default</span> <span class="pre">3.0)</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">--hv_rad_normals</span> <span class="pre">val:</span>&#160;&#160;&#160;&#160;&#160;&#160;&#160; <span class="pre">Normals</span> <span class="pre">radius</span> <span class="pre">(default</span> <span class="pre">0.05)</span></code></p></li>
<li><p><code class="docutils literal notranslate"><span class="pre">--hv_detect_clutter</span> <span class="pre">val:</span>&#160;&#160;&#160;&#160; <span class="pre">TRUE</span> <span class="pre">if</span> <span class="pre">clutter</span> <span class="pre">detect</span> <span class="pre">enabled</span> <span class="pre">(default</span> <span class="pre">true)</span></code></p></li>
</ul>
<p>More details on the Global Hypothesis Verification parameters can be found here:
A. Aldoma, F. Tombari, L. Di Stefano, M. Vincze, <cite>A global hypothesis verification method for 3D object recognition</cite>, ECCV 2012.</p>
</div>
<div class="section" id="helpers">
<h2>Helpers</h2>
<div class="highlight-c++ notranslate"><div class="highlight"><pre><span></span><span class="k">struct</span> <span class="n">CloudStyle</span>
<span class="p">{</span>
    <span class="kt">double</span> <span class="n">r</span><span class="p">;</span>
    <span class="kt">double</span> <span class="n">g</span><span class="p">;</span>
    <span class="kt">double</span> <span class="n">b</span><span class="p">;</span>
    <span class="kt">double</span> <span class="n">size</span><span class="p">;</span>

    <span class="n">CloudStyle</span> <span class="p">(</span><span class="kt">double</span> <span class="n">r</span><span class="p">,</span>
                <span class="kt">double</span> <span class="n">g</span><span class="p">,</span>
                <span class="kt">double</span> <span class="n">b</span><span class="p">,</span>
                <span class="kt">double</span> <span class="n">size</span><span class="p">)</span> <span class="o">:</span>
        <span class="n">r</span> <span class="p">(</span><span class="n">r</span><span class="p">),</span>
        <span class="n">g</span> <span class="p">(</span><span class="n">g</span><span class="p">),</span>
        <span class="n">b</span> <span class="p">(</span><span class="n">b</span><span class="p">),</span>
        <span class="n">size</span> <span class="p">(</span><span class="n">size</span><span class="p">)</span>
    <span class="p">{</span>
    <span class="p">}</span>
<span class="p">};</span>

<span class="n">CloudStyle</span> <span class="nf">style_white</span> <span class="p">(</span><span class="mf">255.0</span><span class="p">,</span> <span class="mf">255.0</span><span class="p">,</span> <span class="mf">255.0</span><span class="p">,</span> <span class="mf">4.0</span><span class="p">);</span>
<span class="n">CloudStyle</span> <span class="nf">style_red</span> <span class="p">(</span><span class="mf">255.0</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mf">3.0</span><span class="p">);</span>
<span class="n">CloudStyle</span> <span class="nf">style_green</span> <span class="p">(</span><span class="mf">0.0</span><span class="p">,</span> <span class="mf">255.0</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mf">5.0</span><span class="p">);</span>
<span class="n">CloudStyle</span> <span class="nf">style_cyan</span> <span class="p">(</span><span class="mf">93.0</span><span class="p">,</span> <span class="mf">200.0</span><span class="p">,</span> <span class="mf">217.0</span><span class="p">,</span> <span class="mf">4.0</span><span class="p">);</span>
<span class="n">CloudStyle</span> <span class="nf">style_violet</span> <span class="p">(</span><span class="mf">255.0</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mf">255.0</span><span class="p">,</span> <span class="mf">8.0</span><span class="p">);</span>
</pre></div>
</div>
<p>This simple struct is used to create <cite>Color</cite> presets for the clouds being visualized.</p>
</div>
<div class="section" id="clustering">
<h2>Clustering</h2>
<p>The code below implements a full Clustering Pipeline: the input of the pipeline is a pair of point clouds (the <code class="docutils literal notranslate"><span class="pre">model</span></code> and the <code class="docutils literal notranslate"><span class="pre">scene</span></code>), and the output is</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">std</span><span class="p">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">Eigen</span><span class="p">::</span><span class="n">Matrix4f</span><span class="p">,</span> <span class="n">Eigen</span><span class="p">::</span><span class="n">aligned_allocator</span><span class="o">&lt;</span><span class="n">Eigen</span><span class="p">::</span><span class="n">Matrix4f</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="n">rototranslations</span><span class="p">;</span>
</pre></div>
</div>
<p><code class="docutils literal notranslate"><span class="pre">rototraslations</span></code> represents a list of <cite>coarsely</cite> transformed models (“object hypotheses”) in the scene.</p>
<p>Take a look at the full pipeline:</p>
<div class="highlight-c++ notranslate"><div class="highlight"><pre><span></span>  <span class="p">}</span>

  <span class="cm">/**</span>
<span class="cm">   * Compute Normals</span>
<span class="cm">   */</span>
<span class="hll">  <span class="n">pcl</span><span class="o">::</span><span class="n">NormalEstimationOMP</span><span class="o">&lt;</span><span class="n">PointType</span><span class="p">,</span> <span class="n">NormalType</span><span class="o">&gt;</span> <span class="n">norm_est</span><span class="p">;</span>
</span>  <span class="n">norm_est</span><span class="p">.</span><span class="n">setKSearch</span> <span class="p">(</span><span class="mi">10</span><span class="p">);</span>
  <span class="n">norm_est</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">model</span><span class="p">);</span>
<span class="hll">  <span class="n">norm_est</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">model_normals</span><span class="p">);</span>
</span>
  <span class="n">norm_est</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">scene</span><span class="p">);</span>
  <span class="n">norm_est</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">scene_normals</span><span class="p">);</span>

  <span class="cm">/**</span>
<span class="cm">   *  Downsample Clouds to Extract keypoints</span>
<span class="cm">   */</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">UniformSampling</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="n">uniform_sampling</span><span class="p">;</span>
  <span class="n">uniform_sampling</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">model</span><span class="p">);</span>
  <span class="n">uniform_sampling</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="n">model_ss_</span><span class="p">);</span>
  <span class="n">uniform_sampling</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">model_keypoints</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Model total points: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">model</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;; Selected Keypoints: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">model_keypoints</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="n">uniform_sampling</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">scene</span><span class="p">);</span>
  <span class="n">uniform_sampling</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="n">scene_ss_</span><span class="p">);</span>
  <span class="n">uniform_sampling</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">scene_keypoints</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Scene total points: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">scene</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;; Selected Keypoints: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">scene_keypoints</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="cm">/**</span>
<span class="cm">   *  Compute Descriptor for keypoints</span>
<span class="cm">   */</span>
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

  <span class="cm">/**</span>
<span class="cm">   *  Find Model-Scene Correspondences with KdTree</span>
<span class="cm">   */</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">CorrespondencesPtr</span> <span class="n">model_scene_corrs</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Correspondences</span> <span class="p">());</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">KdTreeFLANN</span><span class="o">&lt;</span><span class="n">DescriptorType</span><span class="o">&gt;</span> <span class="n">match_search</span><span class="p">;</span>
  <span class="n">match_search</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">model_descriptors</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">model_good_keypoints_indices</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">scene_good_keypoints_indices</span><span class="p">;</span>

  <span class="k">for</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">scene_descriptors</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">neigh_indices</span> <span class="p">(</span><span class="mi">1</span><span class="p">);</span>
    <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="n">neigh_sqr_dists</span> <span class="p">(</span><span class="mi">1</span><span class="p">);</span>
    <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">std</span><span class="o">::</span><span class="n">isfinite</span> <span class="p">(</span><span class="n">scene_descriptors</span><span class="o">-&gt;</span><span class="n">at</span> <span class="p">(</span><span class="n">i</span><span class="p">).</span><span class="n">descriptor</span><span class="p">[</span><span class="mi">0</span><span class="p">]))</span>  <span class="c1">//skipping NaNs</span>
    <span class="p">{</span>
      <span class="k">continue</span><span class="p">;</span>
    <span class="p">}</span>
    <span class="kt">int</span> <span class="n">found_neighs</span> <span class="o">=</span> <span class="n">match_search</span><span class="p">.</span><span class="n">nearestKSearch</span> <span class="p">(</span><span class="n">scene_descriptors</span><span class="o">-&gt;</span><span class="n">at</span> <span class="p">(</span><span class="n">i</span><span class="p">),</span> <span class="mi">1</span><span class="p">,</span> <span class="n">neigh_indices</span><span class="p">,</span> <span class="n">neigh_sqr_dists</span><span class="p">);</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">found_neighs</span> <span class="o">==</span> <span class="mi">1</span> <span class="o">&amp;&amp;</span> <span class="n">neigh_sqr_dists</span><span class="p">[</span><span class="mi">0</span><span class="p">]</span> <span class="o">&lt;</span> <span class="mf">0.25f</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">Correspondence</span> <span class="n">corr</span> <span class="p">(</span><span class="n">neigh_indices</span><span class="p">[</span><span class="mi">0</span><span class="p">],</span> <span class="k">static_cast</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">i</span><span class="p">),</span> <span class="n">neigh_sqr_dists</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
      <span class="n">model_scene_corrs</span><span class="o">-&gt;</span><span class="n">push_back</span> <span class="p">(</span><span class="n">corr</span><span class="p">);</span>
      <span class="n">model_good_keypoints_indices</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">corr</span><span class="p">.</span><span class="n">index_query</span><span class="p">);</span>
      <span class="n">scene_good_keypoints_indices</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">corr</span><span class="p">.</span><span class="n">index_match</span><span class="p">);</span>
    <span class="p">}</span>
  <span class="p">}</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">model_good_kp</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">scene_good_kp</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">copyPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">model_keypoints</span><span class="p">,</span> <span class="n">model_good_keypoints_indices</span><span class="p">,</span> <span class="o">*</span><span class="n">model_good_kp</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">copyPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">scene_keypoints</span><span class="p">,</span> <span class="n">scene_good_keypoints_indices</span><span class="p">,</span> <span class="o">*</span><span class="n">scene_good_kp</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Correspondences found: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">model_scene_corrs</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="cm">/**</span>
<span class="cm">   *  Clustering</span>
<span class="cm">   */</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span><span class="p">,</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">aligned_allocator</span><span class="o">&lt;</span><span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="n">rototranslations</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span> <span class="o">&lt;</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Correspondences</span> <span class="o">&gt;</span> <span class="n">clustered_corrs</span><span class="p">;</span>

  <span class="k">if</span> <span class="p">(</span><span class="n">use_hough_</span><span class="p">)</span>
  <span class="p">{</span>
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

    <span class="n">clusterer</span><span class="p">.</span><span class="n">recognize</span> <span class="p">(</span><span class="n">rototranslations</span><span class="p">,</span> <span class="n">clustered_corrs</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="k">else</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">GeometricConsistencyGrouping</span><span class="o">&lt;</span><span class="n">PointType</span><span class="p">,</span> <span class="n">PointType</span><span class="o">&gt;</span> <span class="n">gc_clusterer</span><span class="p">;</span>
    <span class="n">gc_clusterer</span><span class="p">.</span><span class="n">setGCSize</span> <span class="p">(</span><span class="n">cg_size_</span><span class="p">);</span>
    <span class="n">gc_clusterer</span><span class="p">.</span><span class="n">setGCThreshold</span> <span class="p">(</span><span class="n">cg_thresh_</span><span class="p">);</span>

    <span class="n">gc_clusterer</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">model_keypoints</span><span class="p">);</span>
    <span class="n">gc_clusterer</span><span class="p">.</span><span class="n">setSceneCloud</span> <span class="p">(</span><span class="n">scene_keypoints</span><span class="p">);</span>
    <span class="n">gc_clusterer</span><span class="p">.</span><span class="n">setModelSceneCorrespondences</span> <span class="p">(</span><span class="n">model_scene_corrs</span><span class="p">);</span>

    <span class="n">gc_clusterer</span><span class="p">.</span><span class="n">recognize</span> <span class="p">(</span><span class="n">rototranslations</span><span class="p">,</span> <span class="n">clustered_corrs</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="cm">/**</span>
</pre></div>
</div>
<p>For a full explanation of the above code see <a class="reference external" href="http://pointclouds.org/documentation/tutorials/correspondence_grouping.php">3D Object Recognition based on Correspondence Grouping</a>.</p>
</div>
<div class="section" id="model-in-scene-projection">
<h2>Model-in-Scene Projection</h2>
<p>To improve the <cite>coarse</cite> transformation associated to each object hypothesis, we apply some ICP iterations.
We create a <code class="docutils literal notranslate"><span class="pre">instances</span></code> list to store the “coarse” transformations :</p>
<div class="highlight-c++ notranslate"><div class="highlight"><pre><span></span>   */
  std::vector&lt;pcl::PointCloud&lt;PointType&gt;::ConstPtr&gt; instances;

  for (std::size_t i = 0; i &lt; rototranslations.size (); ++i)
  {
    pcl::PointCloud&lt;PointType&gt;::Ptr rotated_model (new pcl::PointCloud&lt;PointType&gt; ());
    pcl::transformPointCloud (*model, *rotated_model, rototranslations[i]);
    instances.push_back (rotated_model);
  }

  /**
</pre></div>
</div>
<p>then, we run ICP on the <code class="docutils literal notranslate"><span class="pre">instances</span></code> wrt. the <code class="docutils literal notranslate"><span class="pre">scene</span></code> to obtain the <code class="docutils literal notranslate"><span class="pre">registered_instances</span></code>:</p>
<div class="highlight-c++ notranslate"><div class="highlight"><pre><span></span>   */
  std::vector&lt;pcl::PointCloud&lt;PointType&gt;::ConstPtr&gt; registered_instances;
  if (true)
  {
    std::cout &lt;&lt; &quot;--- ICP ---------&quot; &lt;&lt; std::endl;

    for (std::size_t i = 0; i &lt; rototranslations.size (); ++i)
    {
      pcl::IterativeClosestPoint&lt;PointType, PointType&gt; icp;
      icp.setMaximumIterations (icp_max_iter_);
      icp.setMaxCorrespondenceDistance (icp_corr_distance_);
      icp.setInputTarget (scene);
      icp.setInputSource (instances[i]);
      pcl::PointCloud&lt;PointType&gt;::Ptr registered (new pcl::PointCloud&lt;PointType&gt;);
      icp.align (*registered);
      registered_instances.push_back (registered);
      std::cout &lt;&lt; &quot;Instance &quot; &lt;&lt; i &lt;&lt; &quot; &quot;;
      if (icp.hasConverged ())
      {
        std::cout &lt;&lt; &quot;Aligned!&quot; &lt;&lt; std::endl;
      }
      else
      {
        std::cout &lt;&lt; &quot;Not Aligned!&quot; &lt;&lt; std::endl;
      }
    }

    std::cout &lt;&lt; &quot;-----------------&quot; &lt;&lt; std::endl &lt;&lt; std::endl;
  }

  /**
</pre></div>
</div>
</div>
<div class="section" id="hypotheses-verification">
<h2>Hypotheses Verification</h2>
<div class="highlight-c++ notranslate"><div class="highlight"><pre><span></span>   */
  std::cout &lt;&lt; &quot;--- Hypotheses Verification ---&quot; &lt;&lt; std::endl;
  std::vector&lt;bool&gt; hypotheses_mask;  // Mask Vector to identify positive hypotheses

  pcl::GlobalHypothesesVerification&lt;PointType, PointType&gt; GoHv;

  GoHv.setSceneCloud (scene);  // Scene Cloud
  GoHv.addModels (registered_instances, true);  //Models to verify
  GoHv.setResolution (hv_resolution_);
  GoHv.setResolutionOccupancyGrid (hv_occupancy_grid_resolution_);
  GoHv.setInlierThreshold (hv_inlier_th_);
  GoHv.setOcclusionThreshold (hv_occlusion_th_);
  GoHv.setRegularizer (hv_regularizer_);
  GoHv.setRadiusClutter (hv_rad_clutter_);
  GoHv.setClutterRegularizer (hv_clutter_reg_);
  GoHv.setDetectClutter (hv_detect_clutter_);
  GoHv.setRadiusNormals (hv_rad_normals_);

  GoHv.verify ();
  GoHv.getMask (hypotheses_mask);  // i-element TRUE if hvModels[i] verifies hypotheses

  for (int i = 0; i &lt; hypotheses_mask.size (); i++)
  {
    if (hypotheses_mask[i])
    {
      std::cout &lt;&lt; &quot;Instance &quot; &lt;&lt; i &lt;&lt; &quot; is GOOD! &lt;---&quot; &lt;&lt; std::endl;
    }
    else
    {
      std::cout &lt;&lt; &quot;Instance &quot; &lt;&lt; i &lt;&lt; &quot; is bad!&quot; &lt;&lt; std::endl;
    }
  }
  std::cout &lt;&lt; &quot;-------------------------------&quot; &lt;&lt; std::endl;

</pre></div>
</div>
<p><code class="docutils literal notranslate"><span class="pre">GlobalHypothesesVerification</span></code> takes as input a list of <code class="docutils literal notranslate"><span class="pre">registered_instances</span></code> and a <code class="docutils literal notranslate"><span class="pre">scene</span></code> so we can <code class="docutils literal notranslate"><span class="pre">verify()</span></code> them
to get a <code class="docutils literal notranslate"><span class="pre">hypotheses_mask</span></code>: this is a <cite>bool</cite> array where <code class="docutils literal notranslate"><span class="pre">hypotheses_mask[i]</span></code> is <code class="docutils literal notranslate"><span class="pre">TRUE</span></code> if <code class="docutils literal notranslate"><span class="pre">registered_instances[i]</span></code> is a
verified hypothesis, <code class="docutils literal notranslate"><span class="pre">FALSE</span></code> if it has been classified as a False Positive (hence, must be rejected).</p>
</div>
<div class="section" id="visualization">
<h2>Visualization</h2>
<p>The first part of the Visualization code section is pretty simple, with <code class="docutils literal notranslate"><span class="pre">-k</span></code> options the program displays <cite>goog keypoints</cite> in model and in scene
with a <code class="docutils literal notranslate"><span class="pre">styleViolet</span></code> color.</p>
<p>Later we iterate on <code class="docutils literal notranslate"><span class="pre">instances</span></code>, and each <code class="docutils literal notranslate"><span class="pre">instances[i]</span></code> will be displayed in <cite>Viewer</cite> with a <code class="docutils literal notranslate"><span class="pre">styleRed</span></code> color.
Each <code class="docutils literal notranslate"><span class="pre">registered_instances[i]</span></code> will be displayed with two optional colors: <code class="docutils literal notranslate"><span class="pre">styleGreen</span></code> if the current instance is verified (<code class="docutils literal notranslate"><span class="pre">hypotheses_mask[i]</span></code> is <code class="docutils literal notranslate"><span class="pre">TRUE</span></code>),  <code class="docutils literal notranslate"><span class="pre">styleCyan</span></code> otherwise.</p>
<div class="highlight-c++ notranslate"><div class="highlight"><pre><span></span>   *  Visualization
   */
  pcl::visualization::PCLVisualizer viewer (&quot;Hypotheses Verification&quot;);
  viewer.addPointCloud (scene, &quot;scene_cloud&quot;);

  pcl::PointCloud&lt;PointType&gt;::Ptr off_scene_model (new pcl::PointCloud&lt;PointType&gt; ());
  pcl::PointCloud&lt;PointType&gt;::Ptr off_scene_model_keypoints (new pcl::PointCloud&lt;PointType&gt; ());

  pcl::PointCloud&lt;PointType&gt;::Ptr off_model_good_kp (new pcl::PointCloud&lt;PointType&gt; ());
  pcl::transformPointCloud (*model, *off_scene_model, Eigen::Vector3f (-1, 0, 0), Eigen::Quaternionf (1, 0, 0, 0));
  pcl::transformPointCloud (*model_keypoints, *off_scene_model_keypoints, Eigen::Vector3f (-1, 0, 0), Eigen::Quaternionf (1, 0, 0, 0));
  pcl::transformPointCloud (*model_good_kp, *off_model_good_kp, Eigen::Vector3f (-1, 0, 0), Eigen::Quaternionf (1, 0, 0, 0));

  if (show_keypoints_)
  {
    CloudStyle modelStyle = style_white;
    pcl::visualization::PointCloudColorHandlerCustom&lt;PointType&gt; off_scene_model_color_handler (off_scene_model, modelStyle.r, modelStyle.g, modelStyle.b);
    viewer.addPointCloud (off_scene_model, off_scene_model_color_handler, &quot;off_scene_model&quot;);
    viewer.setPointCloudRenderingProperties (pcl::visualization::PCL_VISUALIZER_POINT_SIZE, modelStyle.size, &quot;off_scene_model&quot;);
  }

  if (show_keypoints_)
  {
    CloudStyle goodKeypointStyle = style_violet;
    pcl::visualization::PointCloudColorHandlerCustom&lt;PointType&gt; model_good_keypoints_color_handler (off_model_good_kp, goodKeypointStyle.r, goodKeypointStyle.g,
                                                                                                    goodKeypointStyle.b);
    viewer.addPointCloud (off_model_good_kp, model_good_keypoints_color_handler, &quot;model_good_keypoints&quot;);
    viewer.setPointCloudRenderingProperties (pcl::visualization::PCL_VISUALIZER_POINT_SIZE, goodKeypointStyle.size, &quot;model_good_keypoints&quot;);

    pcl::visualization::PointCloudColorHandlerCustom&lt;PointType&gt; scene_good_keypoints_color_handler (scene_good_kp, goodKeypointStyle.r, goodKeypointStyle.g,
                                                                                                    goodKeypointStyle.b);
    viewer.addPointCloud (scene_good_kp, scene_good_keypoints_color_handler, &quot;scene_good_keypoints&quot;);
    viewer.setPointCloudRenderingProperties (pcl::visualization::PCL_VISUALIZER_POINT_SIZE, goodKeypointStyle.size, &quot;scene_good_keypoints&quot;);
  }

  for (std::size_t i = 0; i &lt; instances.size (); ++i)
  {
    std::stringstream ss_instance;
    ss_instance &lt;&lt; &quot;instance_&quot; &lt;&lt; i;

    CloudStyle clusterStyle = style_red;
    pcl::visualization::PointCloudColorHandlerCustom&lt;PointType&gt; instance_color_handler (instances[i], clusterStyle.r, clusterStyle.g, clusterStyle.b);
    viewer.addPointCloud (instances[i], instance_color_handler, ss_instance.str ());
    viewer.setPointCloudRenderingProperties (pcl::visualization::PCL_VISUALIZER_POINT_SIZE, clusterStyle.size, ss_instance.str ());

    CloudStyle registeredStyles = hypotheses_mask[i] ? style_green : style_cyan;
    ss_instance &lt;&lt; &quot;_registered&quot; &lt;&lt; std::endl;
    pcl::visualization::PointCloudColorHandlerCustom&lt;PointType&gt; registered_instance_color_handler (registered_instances[i], registeredStyles.r,
                                                                                                   registeredStyles.g, registeredStyles.b);
    viewer.addPointCloud (registered_instances[i], registered_instance_color_handler, ss_instance.str ());
    viewer.setPointCloudRenderingProperties (pcl::visualization::PCL_VISUALIZER_POINT_SIZE, registeredStyles.size, ss_instance.str ());
  }

  while (!viewer.wasStopped ())
  {
    viewer.spinOnce ();
  }

</pre></div>
</div>
</div>
</div>
<div class="section" id="compiling-and-running-the-program">
<h1>Compiling and running the program</h1>
<p>Create a <code class="docutils literal notranslate"><span class="pre">CMakeLists.txt</span></code> file and add the following lines into it:</p>
<div class="highlight-cmake notranslate"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
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
13</pre></div></td><td class="code"><div class="highlight"><pre><span></span><span class="nb">cmake_minimum_required</span><span class="p">(</span><span class="s">VERSION</span> <span class="s">2.6</span> <span class="s">FATAL_ERROR</span><span class="p">)</span>

<span class="nb">project</span><span class="p">(</span><span class="s">global_hypothesis_verification</span><span class="p">)</span>

<span class="c">#Pcl</span>
<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.7</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">global_hypothesis_verification</span> <span class="s">global_hypothesis_verification.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">global_hypothesis_verification</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have created the executable, you can then launch it following this example:</p>
<div class="doctest highlight-default notranslate"><div class="highlight"><pre><span></span><span class="gp">&gt;&gt;&gt; </span><span class="o">./</span><span class="n">global_hypothesis_verification</span> <span class="n">milk</span><span class="o">.</span><span class="n">pcd</span> <span class="n">milk_cartoon_all_small_clorox</span><span class="o">.</span><span class="n">pcd</span>
</pre></div>
</div>
<div class="figure align-default">
<img alt="Original Scene Image" src="_images/original.png" />
</div>
<p>Original Scene Image</p>
<div class="figure align-default" id="id1">
<img alt="_images/single.png" src="_images/single.png" />
<p class="caption"><span class="caption-text">Valid Hypothesis (Green) with simple parameters</span></p>
</div>
<p>You can simulate more false positives by using a larger bin size parameter for the Hough Voting Correspondence Grouping algorithm:</p>
<div class="doctest highlight-default notranslate"><div class="highlight"><pre><span></span><span class="gp">&gt;&gt;&gt; </span><span class="o">./</span><span class="n">global_hypothesis_verification</span> <span class="n">milk</span><span class="o">.</span><span class="n">pcd</span> <span class="n">milk_cartoon_all_small_clorox</span><span class="o">.</span><span class="n">pcd</span> <span class="o">--</span><span class="n">cg_size</span> <span class="mf">0.035</span>
</pre></div>
</div>
<div class="figure align-default" id="id2">
<img alt="_images/multiple.png" src="_images/multiple.png" />
<p class="caption"><span class="caption-text">Valid Hypothesis (Green) among 9 false positives</span></p>
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