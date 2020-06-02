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
    
    <title>Visualization of the NARF descriptor and descriptor distances</title>
    
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
            
  <div class="section" id="visualization-of-the-narf-descriptor-and-descriptor-distances">
<span id="narf-descriptor-visualization"></span><h1>Visualization of the NARF descriptor and descriptor distances</h1>
<p>This tutorial is about the visualization of how the NARF descriptor is
calculated and to test how the descriptor distances between certain points in a
range image behave. Compared to the other tuturials, this one is not really
about the code, but about trying the program and looking at the visualization.
Of course, nothing keeps you from having a look at it anyway.</p>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>First, create a file called, let&#8217;s say, <tt class="docutils literal"><span class="pre">narf_descriptor_visualization.cpp</span></tt> in your favorite
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
244</pre></div></td><td class="code"><div class="highlight"><pre><span class="cm">/* \author Bastian Steder */</span>

<span class="cp">#include &lt;iostream&gt;</span>

<span class="cp">#include &lt;boost/thread/thread.hpp&gt;</span>
<span class="cp">#include &lt;pcl/point_cloud.h&gt;</span>
<span class="cp">#include &lt;pcl/io/pcd_io.h&gt;</span>
<span class="cp">#include &lt;pcl/visualization/range_image_visualizer.h&gt;</span>
<span class="cp">#include &lt;pcl/range_image/range_image.h&gt;</span>
<span class="cp">#include &lt;pcl/features/narf.h&gt;</span>
<span class="cp">#include &lt;pcl/console/parse.h&gt;</span>

<span class="kt">float</span> <span class="n">angular_resolution</span> <span class="o">=</span> <span class="mf">0.5f</span><span class="p">;</span>
<span class="kt">int</span> <span class="n">rotation_invariant</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
<span class="kt">float</span> <span class="n">support_size</span> <span class="o">=</span> <span class="mf">0.3f</span><span class="p">;</span>
<span class="kt">int</span> <span class="n">descriptor_size</span> <span class="o">=</span> <span class="mi">36</span><span class="p">;</span>
<span class="n">pcl</span><span class="o">::</span><span class="n">RangeImage</span><span class="o">::</span><span class="n">CoordinateFrame</span> <span class="n">coordinate_frame</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">RangeImage</span><span class="o">::</span><span class="n">CAMERA_FRAME</span><span class="p">;</span>
<span class="kt">bool</span> <span class="n">setUnseenToMaxRange</span> <span class="o">=</span> <span class="nb">false</span><span class="p">;</span>

<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">PointType</span><span class="p">;</span>

<span class="kt">void</span> 
<span class="nf">printUsage</span> <span class="p">(</span><span class="k">const</span> <span class="kt">char</span><span class="o">*</span> <span class="n">progName</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;</span><span class="se">\n\n</span><span class="s">Usage: &quot;</span><span class="o">&lt;&lt;</span><span class="n">progName</span><span class="o">&lt;&lt;</span><span class="s">&quot; [options] &lt;scene.pcd&gt;</span><span class="se">\n\n</span><span class="s">&quot;</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;Options:</span><span class="se">\n</span><span class="s">&quot;</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;-------------------------------------------</span><span class="se">\n</span><span class="s">&quot;</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;-r &lt;float&gt;   angular resolution in degrees (default &quot;</span><span class="o">&lt;&lt;</span><span class="n">angular_resolution</span><span class="o">&lt;&lt;</span><span class="s">&quot;)</span><span class="se">\n</span><span class="s">&quot;</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;-s &lt;float&gt;   support size for the interest points (diameter of the used sphere - &quot;</span>
            <span class="o">&lt;&lt;</span>                                                     <span class="s">&quot;default &quot;</span><span class="o">&lt;&lt;</span><span class="n">support_size</span><span class="o">&lt;&lt;</span><span class="s">&quot;)</span><span class="se">\n</span><span class="s">&quot;</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;-d &lt;int&gt;     descriptor size (default &quot;</span><span class="o">&lt;&lt;</span><span class="n">descriptor_size</span><span class="o">&lt;&lt;</span><span class="s">&quot;)</span><span class="se">\n</span><span class="s">&quot;</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;-c &lt;int&gt;     coordinate frame of the input point cloud (default &quot;</span><span class="o">&lt;&lt;</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span><span class="n">coordinate_frame</span><span class="o">&lt;&lt;</span><span class="s">&quot;)</span><span class="se">\n</span><span class="s">&quot;</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;-o &lt;0/1&gt;     switch rotational invariant version of the feature on/off&quot;</span>
            <span class="o">&lt;&lt;</span>               <span class="s">&quot; (default &quot;</span><span class="o">&lt;&lt;</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span><span class="n">rotation_invariant</span><span class="o">&lt;&lt;</span><span class="s">&quot;)</span><span class="se">\n</span><span class="s">&quot;</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;-m           set unseen pixels to max range</span><span class="se">\n</span><span class="s">&quot;</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;-h           this help</span><span class="se">\n</span><span class="s">&quot;</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;</span><span class="se">\n\n</span><span class="s">&quot;</span><span class="p">;</span>
<span class="p">}</span>

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
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-m&quot;</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">setUnseenToMaxRange</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Setting unseen values in range image to maximum range readings.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
  <span class="p">}</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-o&quot;</span><span class="p">,</span> <span class="n">rotation_invariant</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span><span class="p">)</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Switching rotation invariant feature version &quot;</span><span class="o">&lt;&lt;</span> <span class="p">(</span><span class="n">rotation_invariant</span> <span class="o">?</span> <span class="s">&quot;on&quot;</span> <span class="o">:</span> <span class="s">&quot;off&quot;</span><span class="p">)</span><span class="o">&lt;&lt;</span><span class="s">&quot;.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
  <span class="kt">int</span> <span class="n">tmp_coordinate_frame</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-c&quot;</span><span class="p">,</span> <span class="n">tmp_coordinate_frame</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">coordinate_frame</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">RangeImage</span><span class="o">::</span><span class="n">CoordinateFrame</span> <span class="p">(</span><span class="n">tmp_coordinate_frame</span><span class="p">);</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Using coordinate frame &quot;</span><span class="o">&lt;&lt;</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span><span class="n">coordinate_frame</span><span class="o">&lt;&lt;</span><span class="s">&quot;.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
  <span class="p">}</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-s&quot;</span><span class="p">,</span> <span class="n">support_size</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span><span class="p">)</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Setting support size to &quot;</span><span class="o">&lt;&lt;</span><span class="n">support_size</span><span class="o">&lt;&lt;</span><span class="s">&quot;.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-d&quot;</span><span class="p">,</span> <span class="n">descriptor_size</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span><span class="p">)</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Setting descriptor size to &quot;</span><span class="o">&lt;&lt;</span><span class="n">descriptor_size</span><span class="o">&lt;&lt;</span><span class="s">&quot;.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-r&quot;</span><span class="p">,</span> <span class="n">angular_resolution</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span><span class="p">)</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Setting angular resolution to &quot;</span><span class="o">&lt;&lt;</span><span class="n">angular_resolution</span><span class="o">&lt;&lt;</span><span class="s">&quot;deg.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
  <span class="n">angular_resolution</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">deg2rad</span> <span class="p">(</span><span class="n">angular_resolution</span><span class="p">);</span>
  

  <span class="c1">// -----------------------</span>
  <span class="c1">// -----Read pcd file-----</span>
  <span class="c1">// -----------------------</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">point_cloud_ptr</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointType</span><span class="o">&gt;&amp;</span> <span class="n">point_cloud</span> <span class="o">=</span> <span class="o">*</span><span class="n">point_cloud_ptr</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointWithViewpoint</span><span class="o">&gt;</span> <span class="n">far_ranges</span><span class="p">;</span>
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
    <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">far_ranges_filename</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">getFilenameWithoutExtension</span> <span class="p">(</span><span class="n">filename</span><span class="p">)</span><span class="o">+</span><span class="s">&quot;_far_ranges.pcd&quot;</span><span class="p">;</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="n">far_ranges_filename</span><span class="p">.</span><span class="n">c_str</span> <span class="p">(),</span> <span class="n">far_ranges</span><span class="p">)</span> <span class="o">==</span> <span class="o">-</span><span class="mi">1</span><span class="p">)</span>
      <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Far ranges file </span><span class="se">\&quot;</span><span class="s">&quot;</span><span class="o">&lt;&lt;</span><span class="n">far_ranges_filename</span><span class="o">&lt;&lt;</span><span class="s">&quot;</span><span class="se">\&quot;</span><span class="s"> does not exists.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
  <span class="p">}</span>
  <span class="k">else</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;</span><span class="se">\n</span><span class="s">No *.pcd file for scene given.</span><span class="se">\n\n</span><span class="s">&quot;</span><span class="p">;</span>
    <span class="n">printUsage</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
    <span class="k">return</span> <span class="mi">1</span><span class="p">;</span>
  <span class="p">}</span>
  
  <span class="c1">// -----------------------------------------------</span>
  <span class="c1">// -----Create RangeImage from the PointCloud-----</span>
  <span class="c1">// -----------------------------------------------</span>
  <span class="kt">float</span> <span class="n">noise_level</span> <span class="o">=</span> <span class="mf">0.0</span><span class="p">;</span>
  <span class="kt">float</span> <span class="n">min_range</span> <span class="o">=</span> <span class="mf">0.0f</span><span class="p">;</span>
  <span class="kt">int</span> <span class="n">border_size</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">RangeImage</span><span class="o">&gt;</span> <span class="n">range_image_ptr</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">RangeImage</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">RangeImage</span><span class="o">&amp;</span> <span class="n">range_image</span> <span class="o">=</span> <span class="o">*</span><span class="n">range_image_ptr</span><span class="p">;</span>   
  <span class="n">range_image</span><span class="p">.</span><span class="n">createFromPointCloud</span> <span class="p">(</span><span class="n">point_cloud</span><span class="p">,</span> <span class="n">angular_resolution</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">deg2rad</span> <span class="p">(</span><span class="mf">360.0f</span><span class="p">),</span> <span class="n">pcl</span><span class="o">::</span><span class="n">deg2rad</span> <span class="p">(</span><span class="mf">180.0f</span><span class="p">),</span>
                                   <span class="n">scene_sensor_pose</span><span class="p">,</span> <span class="n">coordinate_frame</span><span class="p">,</span> <span class="n">noise_level</span><span class="p">,</span> <span class="n">min_range</span><span class="p">,</span> <span class="n">border_size</span><span class="p">);</span>
  <span class="n">range_image</span><span class="p">.</span><span class="n">integrateFarRanges</span> <span class="p">(</span><span class="n">far_ranges</span><span class="p">);</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">setUnseenToMaxRange</span><span class="p">)</span>
    <span class="n">range_image</span><span class="p">.</span><span class="n">setUnseenToMaxRange</span> <span class="p">();</span>
  
  <span class="c1">// Extract NARF features:</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Now extracting NARFs in every image point.</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Narf</span><span class="o">*&gt;</span> <span class="o">&gt;</span> <span class="n">narfs</span><span class="p">;</span>
  <span class="n">narfs</span><span class="p">.</span><span class="n">resize</span> <span class="p">(</span><span class="n">range_image</span><span class="p">.</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">());</span>
  <span class="kt">int</span> <span class="n">last_percentage</span><span class="o">=-</span><span class="mi">1</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">y</span><span class="o">=</span><span class="mi">0</span><span class="p">;</span> <span class="n">y</span><span class="o">&lt;</span><span class="n">range_image</span><span class="p">.</span><span class="n">height</span><span class="p">;</span> <span class="o">++</span><span class="n">y</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="k">for</span> <span class="p">(</span><span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">x</span><span class="o">=</span><span class="mi">0</span><span class="p">;</span> <span class="n">x</span><span class="o">&lt;</span><span class="n">range_image</span><span class="p">.</span><span class="n">width</span><span class="p">;</span> <span class="o">++</span><span class="n">x</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="kt">int</span> <span class="n">index</span> <span class="o">=</span> <span class="n">y</span><span class="o">*</span><span class="n">range_image</span><span class="p">.</span><span class="n">width</span><span class="o">+</span><span class="n">x</span><span class="p">;</span>
      <span class="kt">int</span> <span class="n">percentage</span> <span class="o">=</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span> <span class="p">((</span><span class="mi">100</span><span class="o">*</span><span class="n">index</span><span class="p">)</span> <span class="o">/</span> <span class="n">range_image</span><span class="p">.</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">());</span>
      <span class="k">if</span> <span class="p">(</span><span class="n">percentage</span> <span class="o">&gt;</span> <span class="n">last_percentage</span><span class="p">)</span>
      <span class="p">{</span>
        <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="n">percentage</span><span class="o">&lt;&lt;</span><span class="s">&quot;% &quot;</span><span class="o">&lt;&lt;</span><span class="n">std</span><span class="o">::</span><span class="n">flush</span><span class="p">;</span>
        <span class="n">last_percentage</span> <span class="o">=</span> <span class="n">percentage</span><span class="p">;</span>
      <span class="p">}</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">Narf</span><span class="o">::</span><span class="n">extractFromRangeImageAndAddToList</span> <span class="p">(</span><span class="n">range_image</span><span class="p">,</span> <span class="n">x</span><span class="p">,</span> <span class="n">y</span><span class="p">,</span> <span class="n">descriptor_size</span><span class="p">,</span>
                                                    <span class="n">support_size</span><span class="p">,</span> <span class="n">rotation_invariant</span> <span class="o">!=</span> <span class="mi">0</span><span class="p">,</span> <span class="n">narfs</span><span class="p">[</span><span class="n">index</span><span class="p">]);</span>
      <span class="c1">//std::cout &lt;&lt; &quot;Extracted &quot;&lt;&lt;narfs[index].size ()&lt;&lt;&quot; features for pixel &quot;&lt;&lt;x&lt;&lt;&quot;,&quot;&lt;&lt;y&lt;&lt;&quot;.\n&quot;;</span>
    <span class="p">}</span>
  <span class="p">}</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;100%</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Done.</span><span class="se">\n\n</span><span class="s"> Now you can click on points in the image to visualize how the descriptor is &quot;</span>
       <span class="o">&lt;&lt;</span> <span class="s">&quot;extracted and see the descriptor distances to every other point..</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
  
  <span class="c1">//---------------------</span>
  <span class="c1">// -----Show image-----</span>
  <span class="c1">// --------------------</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">RangeImageVisualizer</span> <span class="n">range_image_widget</span> <span class="p">(</span><span class="s">&quot;Scene range image&quot;</span><span class="p">),</span>
                                           <span class="n">surface_patch_widget</span><span class="p">(</span><span class="s">&quot;Descriptor&#39;s surface patch&quot;</span><span class="p">),</span>
                                           <span class="n">descriptor_widget</span><span class="p">(</span><span class="s">&quot;Descriptor&quot;</span><span class="p">),</span>
                                           <span class="n">descriptor_distances_widget</span><span class="p">(</span><span class="s">&quot;descriptor distances&quot;</span><span class="p">);</span>
  <span class="n">range_image_widget</span><span class="p">.</span><span class="n">showRangeImage</span> <span class="p">(</span><span class="n">range_image</span><span class="p">);</span>
  <span class="c1">//range_image_widget.visualize_selected_point = true;</span>

  <span class="c1">//--------------------</span>
  <span class="c1">// -----Main loop-----</span>
  <span class="c1">//--------------------</span>
  <span class="k">while</span> <span class="p">(</span><span class="nb">true</span><span class="p">)</span> 
  <span class="p">{</span>
    <span class="n">range_image_widget</span><span class="p">.</span><span class="n">spinOnce</span> <span class="p">();</span>  <span class="c1">// process GUI events</span>
    <span class="n">surface_patch_widget</span><span class="p">.</span><span class="n">spinOnce</span> <span class="p">();</span>  <span class="c1">// process GUI events</span>
    <span class="n">descriptor_widget</span><span class="p">.</span><span class="n">spinOnce</span> <span class="p">();</span>  <span class="c1">// process GUI events</span>
    <span class="n">pcl_sleep</span><span class="p">(</span><span class="mf">0.01</span><span class="p">);</span>
    
    <span class="c1">//if (!range_image_widget.mouse_click_happened)</span>
      <span class="k">continue</span><span class="p">;</span>
    <span class="c1">//range_image_widget.mouse_click_happened = false;</span>
    <span class="c1">//float clicked_pixel_x_f = range_image_widget.last_clicked_point_x,</span>
          <span class="c1">//clicked_pixel_y_f = range_image_widget.last_clicked_point_y;</span>
    <span class="kt">int</span> <span class="n">clicked_pixel_x</span><span class="p">,</span> <span class="n">clicked_pixel_y</span><span class="p">;</span>
    <span class="c1">//range_image.real2DToInt2D (clicked_pixel_x_f, clicked_pixel_y_f, clicked_pixel_x, clicked_pixel_y);</span>
    <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">range_image</span><span class="p">.</span><span class="n">isValid</span> <span class="p">(</span><span class="n">clicked_pixel_x</span><span class="p">,</span> <span class="n">clicked_pixel_y</span><span class="p">))</span>
      <span class="k">continue</span><span class="p">;</span>
      <span class="c1">//Vector3f clicked_3d_point;</span>
      <span class="c1">//range_image.getPoint (clicked_pixel_x, clicked_pixel_y, clicked_3d_point);</span>
    
    <span class="c1">//surface_patch_widget.show (false);</span>
    <span class="c1">//descriptor_widget.show (false);&quot;</span>
    
    <span class="kt">int</span> <span class="n">selected_index</span> <span class="o">=</span> <span class="n">clicked_pixel_y</span><span class="o">*</span><span class="n">range_image</span><span class="p">.</span><span class="n">width</span> <span class="o">+</span> <span class="n">clicked_pixel_x</span><span class="p">;</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">Narf</span> <span class="n">narf</span><span class="p">;</span>
    <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">narf</span><span class="p">.</span><span class="n">extractFromRangeImage</span> <span class="p">(</span><span class="n">range_image</span><span class="p">,</span> <span class="n">clicked_pixel_x</span><span class="p">,</span> <span class="n">clicked_pixel_y</span><span class="p">,</span>
                                                                         <span class="n">descriptor_size</span><span class="p">,</span> <span class="n">support_size</span><span class="p">))</span>
    <span class="p">{</span>
      <span class="k">continue</span><span class="p">;</span>
    <span class="p">}</span>
    
    <span class="kt">int</span> <span class="n">surface_patch_pixel_size</span> <span class="o">=</span> <span class="n">narf</span><span class="p">.</span><span class="n">getSurfacePatchPixelSize</span> <span class="p">();</span>
    <span class="kt">float</span> <span class="n">surface_patch_world_size</span> <span class="o">=</span> <span class="n">narf</span><span class="p">.</span><span class="n">getSurfacePatchWorldSize</span> <span class="p">();</span>
    <span class="n">surface_patch_widget</span><span class="p">.</span><span class="n">showFloatImage</span> <span class="p">(</span><span class="n">narf</span><span class="p">.</span><span class="n">getSurfacePatch</span> <span class="p">(),</span> <span class="n">surface_patch_pixel_size</span><span class="p">,</span> <span class="n">surface_patch_pixel_size</span><span class="p">,</span>
                                         <span class="o">-</span><span class="mf">0.5f</span><span class="o">*</span><span class="n">surface_patch_world_size</span><span class="p">,</span> <span class="mf">0.5f</span><span class="o">*</span><span class="n">surface_patch_world_size</span><span class="p">,</span> <span class="nb">true</span><span class="p">);</span>
    <span class="kt">float</span> <span class="n">surface_patch_rotation</span> <span class="o">=</span> <span class="n">narf</span><span class="p">.</span><span class="n">getSurfacePatchRotation</span> <span class="p">();</span>
    <span class="kt">float</span> <span class="n">patch_middle</span> <span class="o">=</span> <span class="mf">0.5f</span><span class="o">*</span> <span class="p">(</span><span class="kt">float</span> <span class="p">(</span><span class="n">surface_patch_pixel_size</span><span class="o">-</span><span class="mi">1</span><span class="p">));</span>
    <span class="kt">float</span> <span class="n">angle_step_size</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">deg2rad</span> <span class="p">(</span><span class="mf">360.0f</span><span class="p">)</span><span class="o">/</span><span class="n">narf</span><span class="p">.</span><span class="n">getDescriptorSize</span> <span class="p">();</span>
    <span class="kt">float</span> <span class="n">cell_size</span> <span class="o">=</span> <span class="n">surface_patch_world_size</span><span class="o">/</span><span class="kt">float</span> <span class="p">(</span><span class="n">surface_patch_pixel_size</span><span class="p">),</span>
          <span class="n">cell_factor</span> <span class="o">=</span> <span class="mf">1.0f</span><span class="o">/</span><span class="n">cell_size</span><span class="p">,</span>
          <span class="n">max_dist</span> <span class="o">=</span> <span class="mf">0.5f</span><span class="o">*</span><span class="n">surface_patch_world_size</span><span class="p">,</span>
          <span class="n">line_length</span> <span class="o">=</span> <span class="n">cell_factor</span><span class="o">*</span> <span class="p">(</span><span class="n">max_dist</span><span class="o">-</span><span class="mf">0.5f</span><span class="o">*</span><span class="n">cell_size</span><span class="p">);</span>
    <span class="k">for</span> <span class="p">(</span><span class="kt">int</span> <span class="n">descriptor_value_idx</span><span class="o">=</span><span class="mi">0</span><span class="p">;</span> <span class="n">descriptor_value_idx</span><span class="o">&lt;</span><span class="n">narf</span><span class="p">.</span><span class="n">getDescriptorSize</span> <span class="p">();</span> <span class="o">++</span><span class="n">descriptor_value_idx</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="kt">float</span> <span class="n">angle</span> <span class="o">=</span> <span class="n">descriptor_value_idx</span><span class="o">*</span><span class="n">angle_step_size</span> <span class="o">+</span> <span class="n">surface_patch_rotation</span><span class="p">;</span>
      <span class="c1">//surface_patch_widget.markLine (patch_middle, patch_middle, patch_middle+line_length*sinf (angle),</span>
                                     <span class="c1">//patch_middle+line_length*-cosf (angle), pcl::visualization::Vector3ub (0,255,0));</span>
    <span class="p">}</span>
    <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="n">rotations</span><span class="p">,</span> <span class="n">strengths</span><span class="p">;</span>
    <span class="n">narf</span><span class="p">.</span><span class="n">getRotations</span> <span class="p">(</span><span class="n">rotations</span><span class="p">,</span> <span class="n">strengths</span><span class="p">);</span>
    <span class="kt">float</span> <span class="n">radius</span> <span class="o">=</span> <span class="mf">0.5f</span><span class="o">*</span><span class="n">surface_patch_pixel_size</span><span class="p">;</span>
    <span class="k">for</span> <span class="p">(</span><span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">i</span><span class="o">=</span><span class="mi">0</span><span class="p">;</span> <span class="n">i</span><span class="o">&lt;</span><span class="n">rotations</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="c1">//surface_patch_widget.markLine (radius-0.5, radius-0.5, radius-0.5f + 2.0f*radius*sinf (rotations[i]),</span>
                                                <span class="c1">//radius-0.5f - 2.0f*radius*cosf (rotations[i]), pcl::visualization::Vector3ub (255,0,0));</span>
    <span class="p">}</span>
    
    <span class="n">descriptor_widget</span><span class="p">.</span><span class="n">showFloatImage</span> <span class="p">(</span><span class="n">narf</span><span class="p">.</span><span class="n">getDescriptor</span> <span class="p">(),</span> <span class="n">narf</span><span class="p">.</span><span class="n">getDescriptorSize</span> <span class="p">(),</span> <span class="mi">1</span><span class="p">,</span> <span class="o">-</span><span class="mf">0.1f</span><span class="p">,</span> <span class="mf">0.3f</span><span class="p">,</span> <span class="nb">true</span><span class="p">);</span>

    <span class="c1">//===================================</span>
    <span class="c1">//=====Compare with all features=====</span>
    <span class="c1">//===================================</span>
    <span class="k">const</span> <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Narf</span><span class="o">*&gt;&amp;</span> <span class="n">narfs_of_selected_point</span> <span class="o">=</span> <span class="n">narfs</span><span class="p">[</span><span class="n">selected_index</span><span class="p">];</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">narfs_of_selected_point</span><span class="p">.</span><span class="n">empty</span> <span class="p">())</span>
      <span class="k">continue</span><span class="p">;</span>
    
    <span class="c1">//descriptor_distances_widget.show (false);</span>
    <span class="kt">float</span><span class="o">*</span> <span class="n">descriptor_distance_image</span> <span class="o">=</span> <span class="k">new</span> <span class="kt">float</span><span class="p">[</span><span class="n">range_image</span><span class="p">.</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">()];</span>
    <span class="k">for</span> <span class="p">(</span><span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">point_index</span><span class="o">=</span><span class="mi">0</span><span class="p">;</span> <span class="n">point_index</span><span class="o">&lt;</span><span class="n">range_image</span><span class="p">.</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">point_index</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="kt">float</span><span class="o">&amp;</span> <span class="n">descriptor_distance</span> <span class="o">=</span> <span class="n">descriptor_distance_image</span><span class="p">[</span><span class="n">point_index</span><span class="p">];</span>
      <span class="n">descriptor_distance</span> <span class="o">=</span> <span class="n">std</span><span class="o">::</span><span class="n">numeric_limits</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;::</span><span class="n">infinity</span> <span class="p">();</span>
      <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Narf</span><span class="o">*&gt;&amp;</span> <span class="n">narfs_of_current_point</span> <span class="o">=</span> <span class="n">narfs</span><span class="p">[</span><span class="n">point_index</span><span class="p">];</span>
      <span class="k">if</span> <span class="p">(</span><span class="n">narfs_of_current_point</span><span class="p">.</span><span class="n">empty</span> <span class="p">())</span>
        <span class="k">continue</span><span class="p">;</span>
      <span class="k">for</span> <span class="p">(</span><span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">i</span><span class="o">=</span><span class="mi">0</span><span class="p">;</span> <span class="n">i</span><span class="o">&lt;</span><span class="n">narfs_of_selected_point</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
      <span class="p">{</span>
        <span class="k">for</span> <span class="p">(</span><span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">j</span><span class="o">=</span><span class="mi">0</span><span class="p">;</span> <span class="n">j</span><span class="o">&lt;</span><span class="n">narfs_of_current_point</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">j</span><span class="p">)</span>
        <span class="p">{</span>
          <span class="n">descriptor_distance</span> <span class="o">=</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="n">min</span><span class="p">)(</span><span class="n">descriptor_distance</span><span class="p">,</span>
                                           <span class="n">narfs_of_selected_point</span><span class="p">[</span><span class="n">i</span><span class="p">]</span><span class="o">-&gt;</span><span class="n">getDescriptorDistance</span> <span class="p">(</span><span class="o">*</span><span class="n">narfs_of_current_point</span><span class="p">[</span><span class="n">j</span><span class="p">]));</span>
        <span class="p">}</span>
      <span class="p">}</span>
    <span class="p">}</span>
    <span class="n">descriptor_distances_widget</span><span class="p">.</span><span class="n">showFloatImage</span> <span class="p">(</span><span class="n">descriptor_distance_image</span><span class="p">,</span> <span class="n">range_image</span><span class="p">.</span><span class="n">width</span><span class="p">,</span> <span class="n">range_image</span><span class="p">.</span><span class="n">height</span><span class="p">,</span>
                                               <span class="o">-</span><span class="n">std</span><span class="o">::</span><span class="n">numeric_limits</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;::</span><span class="n">infinity</span> <span class="p">(),</span> <span class="n">std</span><span class="o">::</span><span class="n">numeric_limits</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;::</span><span class="n">infinity</span> <span class="p">(),</span> <span class="nb">true</span><span class="p">);</span>
    <span class="k">delete</span><span class="p">[]</span> <span class="n">descriptor_distance_image</span><span class="p">;</span>
  <span class="p">}</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
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

<span class="nb">project</span><span class="p">(</span><span class="s">narf_descriptor_visualization</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.3</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">narf_descriptor_visualization</span> <span class="s">narf_descriptor_visualization.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">narf_descriptor_visualization</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>You can now try it with a point cloud file from your hard drive:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./narf_descriptor_visualization &lt;point_cloud.pcd&gt;
</pre></div>
</div>
<p>It will take a few second, during which you will see the status in the
terminal. During this time, a NARF feature is extracted in every point of the
range image created from the given point cloud. When it is done, a widget
showing the range image pops up. Now click on a point in the range image. If it
is a valid image point, three additional widgets will pop up. One visualizing
the actual descriptor as a row of gray values, one showing a local range image
patch of the area on which you clicked, overlaid with a star shaped pattern.
Each beam corresponds to one of the cells in the descriptor. The one facing
upwards to the first cell and then going clockwise. The basic intuition is,
that the more the surface changes under the beam, the higher (brighter) the
value of the corresponding descriptor cell. There is also one or more red
beams, which mark the extracted dominant orientations of the image patch,
which, together with the normal, is used to create a unique orientation for the
feature coordinate frame. The last image visualizes the descriptor distances to
every other point in the scene. The darker the value, the more similar the
point is to the clicked image point.</p>
<p>The result should look similar to this:</p>
<img alt="_images/narf_descriptor_visualization.png" src="_images/narf_descriptor_visualization.png" />
<p>Also have a look at:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./narf_descriptor_visualization -h
</pre></div>
</div>
<p>for a list of parameters.</p>
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