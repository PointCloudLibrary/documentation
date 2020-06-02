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
    
    <title>Clustering of Pointclouds into Supervoxels - Theoretical primer</title>
    
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
            
  <div class="section" id="clustering-of-pointclouds-into-supervoxels-theoretical-primer">
<span id="supervoxel-clustering"></span><h1>Clustering of Pointclouds into Supervoxels - Theoretical primer</h1>
<p>In this tutorial, we show how to divide a pointcloud into a number of supervoxel clusters using <tt class="docutils literal"><span class="pre">pcl::SupervoxelClustering</span></tt>, and then how to use and visualize the adjacency information and supervoxels themselves.</p>
<div class="figure align-center">
<a class="reference internal image-reference" href="_images/supervoxel_clustering_example.jpg"><img alt="_images/supervoxel_clustering_example.jpg" src="_images/supervoxel_clustering_example.jpg" /></a>
<p class="caption"><strong>An example of supervoxels and adjacency graph generated for a cloud</strong></p>
</div>
<p>Segmentation algorithms aim to group pixels in images into perceptually meaningful regions which conform to object boundaries. Graph-based approaches, such as Markov Random Field (MRF) and Conditional Random Field (CRF), have become popular, as they merge relational low-level context within the image with object level class knowledge. The cost of solving pixel-level graphs led to the development of mid-level inference schemes which do not use pixels directly, but rather use groupings of pixels, known as superpixels, as the base level for nodes. Superpixels are formed by over-segmenting the image into small regions based on local low-level features, reducing the number of nodes which must be considered for inference.</p>
<p>Due to their strong impact on the quality of the eventual segmentation, it is important that superpixels have certain characteristics. Of these, avoiding violating object boundaries is the most vital, as failing to do so will decrease the accuracy of classifiers used later - since they will be forced to consider pixels which belong to more than one class. Additionally, even if the classifier does manage a correct output, the final pixel level segmentation will necessarily contain errors. Another useful quality is regular distribution over the area being segmented, as this will produce a simpler graph for later steps.</p>
<p>Voxel Cloud Connectivity Segmentation (VCCS) is a recent &#8220;superpixel&#8221; method which generates volumetric over-segmentations of 3D point cloud data, known as supervoxels. Supervoxels adhere to object boundaries better than state-of-the-art 2D methods, while remaining efficient enough to use in online applications. VCCS uses a region growing variant of k-means clustering for generating its labeling of points directly within a voxel octree structure. Supervoxels have two important properties; they are evenly distributed across the 3D space, and they cannot cross boundaries unless the underlying voxels are spatial connected. The former is accomplished by seeding supervoxels directly in the cloud, rather than the projected plane, while the latter uses an octree structure which maintains adjacency information of leaves. Supervoxels maintain adjacency relations in voxelized 3D space; specifically, 26-adjacency- that is neighboring voxels are those that share a face, edge, or vertex, as seen below.</p>
<div class="figure align-center">
<a class="reference internal image-reference" href="_images/supervoxel_clustering_adjacency.jpg"><img alt="_images/supervoxel_clustering_adjacency.jpg" src="_images/supervoxel_clustering_adjacency.jpg" /></a>
<p class="caption"><strong>From right to left, 6 (faces), 18 (faces,egdes), and 26 (faces, edges, vertices) adjacency</strong></p>
</div>
<p>The adjacency graph of supervoxels (and the underlying voxels) is maintained efficiently within the octree by specifying that neighbors are voxels within R_voxel of one another, where R_voxel specifies the octree leaf resolution. This adjacency graph is used extensively for both the region growing used to generate the supervoxels, as well as determining adjacency of the resulting supervoxels themselves.</p>
<p>VCCS is a region growing method which incrementally expand supervoxels from a set of seed points distributed evenly in space on a grid with resolution R_seed. To maintain efficiency, VCCS does not search globally, but rather only considers points within R_seed of the seed center. Additionally, seeds which are isolated are filtered out by establishing a small search radius R_search around each seed and removing seeds which do not have sufficient neighbor voxels connected to them.</p>
<div class="figure align-center">
<a class="reference internal image-reference" href="_images/supervoxel_clustering_parameters.jpg"><img alt="_images/supervoxel_clustering_parameters.jpg" src="_images/supervoxel_clustering_parameters.jpg" /></a>
<p class="caption"><strong>The various sizing parameters which affect supervoxel clustering. R_seed and R_voxel must both be set by the user.</strong></p>
</div>
<p>Expansion from the seed points is governed by a distance measure calculated in a feature space consisting of spatial extent, color, and normals. The spatial distance D_s is normalized by the seeding resolution, color distance D_c is the euclidean distance in normalized RGB space, and normal distance D_n measures the angle between surface normal vectors.</p>
<div class="figure align-center">
<a class="reference internal image-reference" href="_images/supervoxel_clustering_distance_eqn.png"><img alt="_images/supervoxel_clustering_distance_eqn.png" src="_images/supervoxel_clustering_distance_eqn.png" /></a>
<p class="caption"><strong>Weighting equation used in supervoxel clustering. w_c, w_s, and w_n, the color, spatial, and normal weights, respectively, are user controlled parameters.</strong></p>
</div>
<p>Supervoxels are grown iteratively, using a local k-means clustering which considers connectivity and flow. The general process is as follows. Beginning at the voxel nearest the cluster center, we flow outward to adjacent voxels and compute the distance from each of these to the supervoxel center using the distance equation above. If the distance is the smallest this voxel has seen, its label is set, and using the adjacency graph, we add its neighbors which are further from the center to our search queue for this label. We then proceed to the next supervoxel, so that each level outwards from the center is considered at the same time for all supervoxels (a 2d version of this is seen in the figure below). We proceed iteratively outwards until we have reached the edge of the search volume for each supervoxel (or have no more neighbors to check).</p>
<div class="figure align-center">
<img alt="_images/supervoxel_clustering_search_order.jpg" src="_images/supervoxel_clustering_search_order.jpg" />
<p class="caption"><strong>Search order in the adjacency octree for supervoxel cluster expansion. Dotted edges in the adjacency graph are not searched, since they have already been considered earlier in the queue.</strong></p>
</div>
<p>Alright, let&#8217;s get to the code... but if you want further details on how supervoxels work (and if you use them in an academic work) please reference the following publication:</p>
<div class="highlight-python"><div class="highlight"><pre>@InProceedings{Papon13CVPR,
  author={Jeremie Papon and Alexey Abramov and Markus Schoeler and Florentin W\&quot;{o}rg\&quot;{o}tter},
  title={Voxel Cloud Connectivity Segmentation - Supervoxels for Point Clouds},
  booktitle={Computer Vision and Pattern Recognition (CVPR), 2013 IEEE Conference on},
  month     = {June 22-27},
  year      = {2013},
  address   = {Portland, Oregon},
}
</pre></div>
</div>
<p>Oh, and for a more complicated example which uses Supervoxels, see <tt class="docutils literal"><span class="pre">pcl/examples/segmentation/supervoxel_clustering.cpp</span></tt>.</p>
</div>
<div class="section" id="the-code">
<h1>The code</h1>
<p>First, grab a pcd file made from a kinect or similar device - here we shall use <tt class="docutils literal"><span class="pre">milk_cartoon_all_small_clorox.pcd</span></tt> which is available in the pcl git
<a class="reference external" href="https://github.com/PointCloudLibrary/data/blob/master/tutorials/correspondence_grouping/milk_cartoon_all_small_clorox.pcd?raw=true">here</a>).
Next, copy and paste the following code into your editor and save it as <tt class="docutils literal"><span class="pre">supervoxel_clustering.cpp</span></tt> (or download the source file <a class="reference download internal" href="_downloads/supervoxel_clustering.cpp"><tt class="xref download docutils literal"><span class="pre">here</span></tt></a>).</p>
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
165</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;pcl/console/parse.h&gt;</span>
<span class="cp">#include &lt;pcl/point_cloud.h&gt;</span>
<span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/io/pcd_io.h&gt;</span>
<span class="cp">#include &lt;pcl/visualization/pcl_visualizer.h&gt;</span>
<span class="cp">#include &lt;pcl/segmentation/supervoxel_clustering.h&gt;</span>

<span class="c1">// Types</span>
<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span> <span class="n">PointT</span><span class="p">;</span>
<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">PointCloudT</span><span class="p">;</span>
<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointNormal</span> <span class="n">PointNT</span><span class="p">;</span>
<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointNT</span><span class="o">&gt;</span> <span class="n">PointNCloudT</span><span class="p">;</span>
<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZL</span> <span class="n">PointLT</span><span class="p">;</span>
<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointLT</span><span class="o">&gt;</span> <span class="n">PointLCloudT</span><span class="p">;</span>

<span class="kt">void</span> <span class="nf">addSupervoxelConnectionsToViewer</span> <span class="p">(</span><span class="n">PointT</span> <span class="o">&amp;</span><span class="n">supervoxel_center</span><span class="p">,</span>
                                       <span class="n">PointCloudT</span> <span class="o">&amp;</span><span class="n">adjacent_supervoxel_centers</span><span class="p">,</span>
                                       <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">supervoxel_name</span><span class="p">,</span>
                                       <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="o">&amp;</span> <span class="n">viewer</span><span class="p">);</span>


<span class="kt">int</span>
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span> <span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">argc</span> <span class="o">&lt;</span> <span class="mi">2</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_error</span> <span class="p">(</span><span class="s">&quot;Syntax is: %s &lt;pcd-file&gt; </span><span class="se">\n</span><span class="s"> &quot;</span>
                                <span class="s">&quot;--NT Dsables the single cloud transform </span><span class="se">\n</span><span class="s">&quot;</span>
                                <span class="s">&quot;-v &lt;voxel resolution&gt;</span><span class="se">\n</span><span class="s">-s &lt;seed resolution&gt;</span><span class="se">\n</span><span class="s">&quot;</span>
                                <span class="s">&quot;-c &lt;color weight&gt; </span><span class="se">\n</span><span class="s">-z &lt;spatial weight&gt; </span><span class="se">\n</span><span class="s">&quot;</span>
                                <span class="s">&quot;-n &lt;normal_weight&gt;</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
    <span class="k">return</span> <span class="p">(</span><span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>


  <span class="n">PointCloudT</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="o">=</span> <span class="n">boost</span><span class="o">::</span><span class="n">make_shared</span> <span class="o">&lt;</span><span class="n">PointCloudT</span><span class="o">&gt;</span> <span class="p">();</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_highlight</span> <span class="p">(</span><span class="s">&quot;Loading point cloud...</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">1</span><span class="p">],</span> <span class="o">*</span><span class="n">cloud</span><span class="p">))</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_error</span> <span class="p">(</span><span class="s">&quot;Error loading cloud file!</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
    <span class="k">return</span> <span class="p">(</span><span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>


  <span class="kt">bool</span> <span class="n">use_transform</span> <span class="o">=</span> <span class="o">!</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_switch</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--NT&quot;</span><span class="p">);</span>

  <span class="kt">float</span> <span class="n">voxel_resolution</span> <span class="o">=</span> <span class="mf">0.008f</span><span class="p">;</span>
  <span class="kt">bool</span> <span class="n">voxel_res_specified</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_switch</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-v&quot;</span><span class="p">);</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">voxel_res_specified</span><span class="p">)</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-v&quot;</span><span class="p">,</span> <span class="n">voxel_resolution</span><span class="p">);</span>

  <span class="kt">float</span> <span class="n">seed_resolution</span> <span class="o">=</span> <span class="mf">0.1f</span><span class="p">;</span>
  <span class="kt">bool</span> <span class="n">seed_res_specified</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_switch</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-s&quot;</span><span class="p">);</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">seed_res_specified</span><span class="p">)</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-s&quot;</span><span class="p">,</span> <span class="n">seed_resolution</span><span class="p">);</span>

  <span class="kt">float</span> <span class="n">color_importance</span> <span class="o">=</span> <span class="mf">0.2f</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_switch</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-c&quot;</span><span class="p">))</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-c&quot;</span><span class="p">,</span> <span class="n">color_importance</span><span class="p">);</span>

  <span class="kt">float</span> <span class="n">spatial_importance</span> <span class="o">=</span> <span class="mf">0.4f</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_switch</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-z&quot;</span><span class="p">))</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-z&quot;</span><span class="p">,</span> <span class="n">spatial_importance</span><span class="p">);</span>

  <span class="kt">float</span> <span class="n">normal_importance</span> <span class="o">=</span> <span class="mf">1.0f</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_switch</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-n&quot;</span><span class="p">))</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-n&quot;</span><span class="p">,</span> <span class="n">normal_importance</span><span class="p">);</span>

  <span class="c1">//////////////////////////////  //////////////////////////////</span>
  <span class="c1">////// This is how to use supervoxels</span>
  <span class="c1">//////////////////////////////  //////////////////////////////</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">SupervoxelClustering</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">super</span> <span class="p">(</span><span class="n">voxel_resolution</span><span class="p">,</span> <span class="n">seed_resolution</span><span class="p">,</span> <span class="n">use_transform</span><span class="p">);</span>
  <span class="n">super</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">super</span><span class="p">.</span><span class="n">setColorImportance</span> <span class="p">(</span><span class="n">color_importance</span><span class="p">);</span>
  <span class="n">super</span><span class="p">.</span><span class="n">setSpatialImportance</span> <span class="p">(</span><span class="n">spatial_importance</span><span class="p">);</span>
  <span class="n">super</span><span class="p">.</span><span class="n">setNormalImportance</span> <span class="p">(</span><span class="n">normal_importance</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">map</span> <span class="o">&lt;</span><span class="kt">uint32_t</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Supervoxel</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="o">&gt;</span> <span class="n">supervoxel_clusters</span><span class="p">;</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_highlight</span> <span class="p">(</span><span class="s">&quot;Extracting supervoxels!</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
  <span class="n">super</span><span class="p">.</span><span class="n">extract</span> <span class="p">(</span><span class="n">supervoxel_clusters</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_info</span> <span class="p">(</span><span class="s">&quot;Found %d supervoxels</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">supervoxel_clusters</span><span class="p">.</span><span class="n">size</span> <span class="p">());</span>

  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">viewer</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="p">(</span><span class="s">&quot;3D Viewer&quot;</span><span class="p">));</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setBackgroundColor</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>

  <span class="n">PointCloudT</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">voxel_centroid_cloud</span> <span class="o">=</span> <span class="n">super</span><span class="p">.</span><span class="n">getVoxelCentroidCloud</span> <span class="p">();</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">voxel_centroid_cloud</span><span class="p">,</span> <span class="s">&quot;voxel centroids&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span><span class="mf">2.0</span><span class="p">,</span> <span class="s">&quot;voxel centroids&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_OPACITY</span><span class="p">,</span><span class="mf">0.95</span><span class="p">,</span> <span class="s">&quot;voxel centroids&quot;</span><span class="p">);</span>

  <span class="n">PointCloudT</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">colored_voxel_cloud</span> <span class="o">=</span> <span class="n">super</span><span class="p">.</span><span class="n">getColoredVoxelCloud</span> <span class="p">();</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">colored_voxel_cloud</span><span class="p">,</span> <span class="s">&quot;colored voxels&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_OPACITY</span><span class="p">,</span><span class="mf">0.8</span><span class="p">,</span> <span class="s">&quot;colored voxels&quot;</span><span class="p">);</span>

  <span class="n">PointNCloudT</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">sv_normal_cloud</span> <span class="o">=</span> <span class="n">super</span><span class="p">.</span><span class="n">makeSupervoxelNormalCloud</span> <span class="p">(</span><span class="n">supervoxel_clusters</span><span class="p">);</span>
  <span class="c1">//We have this disabled so graph is easy to see, uncomment to see supervoxel normals</span>
  <span class="c1">//viewer-&gt;addPointCloudNormals&lt;PointNormal&gt; (sv_normal_cloud,1,0.05f, &quot;supervoxel_normals&quot;);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_highlight</span> <span class="p">(</span><span class="s">&quot;Getting supervoxel adjacency</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">multimap</span><span class="o">&lt;</span><span class="kt">uint32_t</span><span class="p">,</span> <span class="kt">uint32_t</span><span class="o">&gt;</span> <span class="n">supervoxel_adjacency</span><span class="p">;</span>
  <span class="n">super</span><span class="p">.</span><span class="n">getSupervoxelAdjacency</span> <span class="p">(</span><span class="n">supervoxel_adjacency</span><span class="p">);</span>
  <span class="c1">//To make a graph of the supervoxel adjacency, we need to iterate through the supervoxel adjacency multimap</span>
  <span class="n">std</span><span class="o">::</span><span class="n">multimap</span><span class="o">&lt;</span><span class="kt">uint32_t</span><span class="p">,</span><span class="kt">uint32_t</span><span class="o">&gt;::</span><span class="n">iterator</span> <span class="n">label_itr</span> <span class="o">=</span> <span class="n">supervoxel_adjacency</span><span class="p">.</span><span class="n">begin</span> <span class="p">();</span>
  <span class="k">for</span> <span class="p">(</span> <span class="p">;</span> <span class="n">label_itr</span> <span class="o">!=</span> <span class="n">supervoxel_adjacency</span><span class="p">.</span><span class="n">end</span> <span class="p">();</span> <span class="p">)</span>
  <span class="p">{</span>
    <span class="c1">//First get the label</span>
    <span class="kt">uint32_t</span> <span class="n">supervoxel_label</span> <span class="o">=</span> <span class="n">label_itr</span><span class="o">-&gt;</span><span class="n">first</span><span class="p">;</span>
    <span class="c1">//Now get the supervoxel corresponding to the label</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">Supervoxel</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">supervoxel</span> <span class="o">=</span> <span class="n">supervoxel_clusters</span><span class="p">.</span><span class="n">at</span> <span class="p">(</span><span class="n">supervoxel_label</span><span class="p">);</span>

    <span class="c1">//Now we need to iterate through the adjacent supervoxels and make a point cloud of them</span>
    <span class="n">PointCloudT</span> <span class="n">adjacent_supervoxel_centers</span><span class="p">;</span>
    <span class="n">std</span><span class="o">::</span><span class="n">multimap</span><span class="o">&lt;</span><span class="kt">uint32_t</span><span class="p">,</span><span class="kt">uint32_t</span><span class="o">&gt;::</span><span class="n">iterator</span> <span class="n">adjacent_itr</span> <span class="o">=</span> <span class="n">supervoxel_adjacency</span><span class="p">.</span><span class="n">equal_range</span> <span class="p">(</span><span class="n">supervoxel_label</span><span class="p">).</span><span class="n">first</span><span class="p">;</span>
    <span class="k">for</span> <span class="p">(</span> <span class="p">;</span> <span class="n">adjacent_itr</span><span class="o">!=</span><span class="n">supervoxel_adjacency</span><span class="p">.</span><span class="n">equal_range</span> <span class="p">(</span><span class="n">supervoxel_label</span><span class="p">).</span><span class="n">second</span><span class="p">;</span> <span class="o">++</span><span class="n">adjacent_itr</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">Supervoxel</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">neighbor_supervoxel</span> <span class="o">=</span> <span class="n">supervoxel_clusters</span><span class="p">.</span><span class="n">at</span> <span class="p">(</span><span class="n">adjacent_itr</span><span class="o">-&gt;</span><span class="n">second</span><span class="p">);</span>
      <span class="n">adjacent_supervoxel_centers</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">neighbor_supervoxel</span><span class="o">-&gt;</span><span class="n">centroid_</span><span class="p">);</span>
    <span class="p">}</span>
    <span class="c1">//Now we make a name for this polygon</span>
    <span class="n">std</span><span class="o">::</span><span class="n">stringstream</span> <span class="n">ss</span><span class="p">;</span>
    <span class="n">ss</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;supervoxel_&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">supervoxel_label</span><span class="p">;</span>
    <span class="c1">//This function is shown below, but is beyond the scope of this tutorial - basically it just generates a &quot;star&quot; polygon mesh from the points given</span>
    <span class="n">addSupervoxelConnectionsToViewer</span> <span class="p">(</span><span class="n">supervoxel</span><span class="o">-&gt;</span><span class="n">centroid_</span><span class="p">,</span> <span class="n">adjacent_supervoxel_centers</span><span class="p">,</span> <span class="n">ss</span><span class="p">.</span><span class="n">str</span> <span class="p">(),</span> <span class="n">viewer</span><span class="p">);</span>
    <span class="c1">//Move iterator forward to next label</span>
    <span class="n">label_itr</span> <span class="o">=</span> <span class="n">supervoxel_adjacency</span><span class="p">.</span><span class="n">upper_bound</span> <span class="p">(</span><span class="n">supervoxel_label</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">viewer</span><span class="o">-&gt;</span><span class="n">wasStopped</span> <span class="p">())</span>
  <span class="p">{</span>
    <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">spinOnce</span> <span class="p">(</span><span class="mi">100</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="k">return</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
<span class="p">}</span>

<span class="kt">void</span>
<span class="nf">addSupervoxelConnectionsToViewer</span> <span class="p">(</span><span class="n">PointT</span> <span class="o">&amp;</span><span class="n">supervoxel_center</span><span class="p">,</span>
                                  <span class="n">PointCloudT</span> <span class="o">&amp;</span><span class="n">adjacent_supervoxel_centers</span><span class="p">,</span>
                                  <span class="n">std</span><span class="o">::</span><span class="n">string</span> <span class="n">supervoxel_name</span><span class="p">,</span>
                                  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="o">&amp;</span> <span class="n">viewer</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">vtkSmartPointer</span><span class="o">&lt;</span><span class="n">vtkPoints</span><span class="o">&gt;</span> <span class="n">points</span> <span class="o">=</span> <span class="n">vtkSmartPointer</span><span class="o">&lt;</span><span class="n">vtkPoints</span><span class="o">&gt;::</span><span class="n">New</span> <span class="p">();</span>
  <span class="n">vtkSmartPointer</span><span class="o">&lt;</span><span class="n">vtkCellArray</span><span class="o">&gt;</span> <span class="n">cells</span> <span class="o">=</span> <span class="n">vtkSmartPointer</span><span class="o">&lt;</span><span class="n">vtkCellArray</span><span class="o">&gt;::</span><span class="n">New</span> <span class="p">();</span>
  <span class="n">vtkSmartPointer</span><span class="o">&lt;</span><span class="n">vtkPolyLine</span><span class="o">&gt;</span> <span class="n">polyLine</span> <span class="o">=</span> <span class="n">vtkSmartPointer</span><span class="o">&lt;</span><span class="n">vtkPolyLine</span><span class="o">&gt;::</span><span class="n">New</span> <span class="p">();</span>

  <span class="c1">//Iterate through all adjacent points, and add a center point to adjacent point pair</span>
  <span class="n">PointCloudT</span><span class="o">::</span><span class="n">iterator</span> <span class="n">adjacent_itr</span> <span class="o">=</span> <span class="n">adjacent_supervoxel_centers</span><span class="p">.</span><span class="n">begin</span> <span class="p">();</span>
  <span class="k">for</span> <span class="p">(</span> <span class="p">;</span> <span class="n">adjacent_itr</span> <span class="o">!=</span> <span class="n">adjacent_supervoxel_centers</span><span class="p">.</span><span class="n">end</span> <span class="p">();</span> <span class="o">++</span><span class="n">adjacent_itr</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">points</span><span class="o">-&gt;</span><span class="n">InsertNextPoint</span> <span class="p">(</span><span class="n">supervoxel_center</span><span class="p">.</span><span class="n">data</span><span class="p">);</span>
    <span class="n">points</span><span class="o">-&gt;</span><span class="n">InsertNextPoint</span> <span class="p">(</span><span class="n">adjacent_itr</span><span class="o">-&gt;</span><span class="n">data</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="c1">// Create a polydata to store everything in</span>
  <span class="n">vtkSmartPointer</span><span class="o">&lt;</span><span class="n">vtkPolyData</span><span class="o">&gt;</span> <span class="n">polyData</span> <span class="o">=</span> <span class="n">vtkSmartPointer</span><span class="o">&lt;</span><span class="n">vtkPolyData</span><span class="o">&gt;::</span><span class="n">New</span> <span class="p">();</span>
  <span class="c1">// Add the points to the dataset</span>
  <span class="n">polyData</span><span class="o">-&gt;</span><span class="n">SetPoints</span> <span class="p">(</span><span class="n">points</span><span class="p">);</span>
  <span class="n">polyLine</span><span class="o">-&gt;</span><span class="n">GetPointIds</span>  <span class="p">()</span><span class="o">-&gt;</span><span class="n">SetNumberOfIds</span><span class="p">(</span><span class="n">points</span><span class="o">-&gt;</span><span class="n">GetNumberOfPoints</span> <span class="p">());</span>
  <span class="k">for</span><span class="p">(</span><span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">points</span><span class="o">-&gt;</span><span class="n">GetNumberOfPoints</span> <span class="p">();</span> <span class="n">i</span><span class="o">++</span><span class="p">)</span>
    <span class="n">polyLine</span><span class="o">-&gt;</span><span class="n">GetPointIds</span> <span class="p">()</span><span class="o">-&gt;</span><span class="n">SetId</span> <span class="p">(</span><span class="n">i</span><span class="p">,</span><span class="n">i</span><span class="p">);</span>
  <span class="n">cells</span><span class="o">-&gt;</span><span class="n">InsertNextCell</span> <span class="p">(</span><span class="n">polyLine</span><span class="p">);</span>
  <span class="c1">// Add the lines to the dataset</span>
  <span class="n">polyData</span><span class="o">-&gt;</span><span class="n">SetLines</span> <span class="p">(</span><span class="n">cells</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addModelFromPolyData</span> <span class="p">(</span><span class="n">polyData</span><span class="p">,</span><span class="n">supervoxel_name</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>We start by defining convenience types in order not to clutter the code.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span> <span class="n">PointT</span><span class="p">;</span>
<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">PointCloudT</span><span class="p">;</span>
<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointNormal</span> <span class="n">PointNT</span><span class="p">;</span>
<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointNT</span><span class="o">&gt;</span> <span class="n">PointNCloudT</span><span class="p">;</span>
<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZL</span> <span class="n">PointLT</span><span class="p">;</span>
<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointLT</span><span class="o">&gt;</span> <span class="n">PointLCloudT</span><span class="p">;</span>
</pre></div>
</div>
<p>Then we load the input cloud based on the input argument</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">PointCloudT</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="o">=</span> <span class="n">boost</span><span class="o">::</span><span class="n">make_shared</span> <span class="o">&lt;</span><span class="n">PointCloudT</span><span class="o">&gt;</span> <span class="p">();</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_highlight</span> <span class="p">(</span><span class="s">&quot;Loading point cloud...</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">1</span><span class="p">],</span> <span class="o">*</span><span class="n">cloud</span><span class="p">))</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_error</span> <span class="p">(</span><span class="s">&quot;Error loading cloud file!</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
    <span class="k">return</span> <span class="p">(</span><span class="mi">1</span><span class="p">);</span>
  <span class="p">}</span>
</pre></div>
</div>
<p>Next we check the input arguments and set default values. You can play with the various parameters to see how they affect the supervoxels, but briefly:</p>
<ul class="simple">
<li><tt class="docutils literal"><span class="pre">--NT</span></tt> Disables the single-view transform (this is necessary if you are loading a cloud constructed from more than one viewpoint)</li>
<li><tt class="docutils literal"><span class="pre">-v</span></tt> Sets the voxel size, which determines the leaf size of the underlying octree structure (in meters)</li>
<li><tt class="docutils literal"><span class="pre">-s</span></tt> Sets the seeding size, which determines how big the supervoxels will be (in meters)</li>
<li><tt class="docutils literal"><span class="pre">-c</span></tt> Sets the weight for color - how much color will influence the shape of the supervoxels</li>
<li><tt class="docutils literal"><span class="pre">-z</span></tt> Sets the weight for spatial term - higher values will result in supervoxels with very regular shapes (lower will result in supervoxels which follow normals and/or colors, but are not very regular)</li>
<li><tt class="docutils literal"><span class="pre">-n</span></tt> Sets the weight for normal - how much surface normals will influence the shape of the supervoxels</li>
</ul>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="kt">bool</span> <span class="n">use_transform</span> <span class="o">=</span> <span class="o">!</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_switch</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;--NT&quot;</span><span class="p">);</span>

  <span class="kt">float</span> <span class="n">voxel_resolution</span> <span class="o">=</span> <span class="mf">0.008f</span><span class="p">;</span>
  <span class="kt">bool</span> <span class="n">voxel_res_specified</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_switch</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-v&quot;</span><span class="p">);</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">voxel_res_specified</span><span class="p">)</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-v&quot;</span><span class="p">,</span> <span class="n">voxel_resolution</span><span class="p">);</span>

  <span class="kt">float</span> <span class="n">seed_resolution</span> <span class="o">=</span> <span class="mf">0.1f</span><span class="p">;</span>
  <span class="kt">bool</span> <span class="n">seed_res_specified</span> <span class="o">=</span> <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_switch</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-s&quot;</span><span class="p">);</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">seed_res_specified</span><span class="p">)</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-s&quot;</span><span class="p">,</span> <span class="n">seed_resolution</span><span class="p">);</span>

  <span class="kt">float</span> <span class="n">color_importance</span> <span class="o">=</span> <span class="mf">0.2f</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_switch</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-c&quot;</span><span class="p">))</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-c&quot;</span><span class="p">,</span> <span class="n">color_importance</span><span class="p">);</span>

  <span class="kt">float</span> <span class="n">spatial_importance</span> <span class="o">=</span> <span class="mf">0.4f</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_switch</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-z&quot;</span><span class="p">))</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-z&quot;</span><span class="p">,</span> <span class="n">spatial_importance</span><span class="p">);</span>

  <span class="kt">float</span> <span class="n">normal_importance</span> <span class="o">=</span> <span class="mf">1.0f</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_switch</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-n&quot;</span><span class="p">))</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">parse</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-n&quot;</span><span class="p">,</span> <span class="n">normal_importance</span><span class="p">);</span>
</pre></div>
</div>
<p>We are now ready to setup the supervoxel clustering. We use the class <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_supervoxel_clustering.html">SupervoxelClustering</a>, which implements the clustering process and give it the parameters.</p>
<div class="admonition important">
<p class="first admonition-title">Important</p>
<p class="last">You MUST set use_transform to false if you are using a cloud which doesn&#8217;t have the camera at (0,0,0). The transform is specifically designed to help improve Kinect data by increasing voxel bin size as distance from the camera increases. If your data is artificial, made from combining multiple clouds from cameras at different viewpoints, or doesn&#8217;t have the camera at (0,0,0), the transform MUST be set to false.</p>
</div>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">SupervoxelClustering</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">super</span> <span class="p">(</span><span class="n">voxel_resolution</span><span class="p">,</span> <span class="n">seed_resolution</span><span class="p">,</span> <span class="n">use_transform</span><span class="p">);</span>
  <span class="n">super</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">super</span><span class="p">.</span><span class="n">setColorImportance</span> <span class="p">(</span><span class="n">color_importance</span><span class="p">);</span>
  <span class="n">super</span><span class="p">.</span><span class="n">setSpatialImportance</span> <span class="p">(</span><span class="n">spatial_importance</span><span class="p">);</span>
  <span class="n">super</span><span class="p">.</span><span class="n">setNormalImportance</span> <span class="p">(</span><span class="n">normal_importance</span><span class="p">);</span>
</pre></div>
</div>
<p>Then we initialize the data structure which will be used to extract the supervoxels, and run the algorithm. The data structure is a map from labels to shared pointers of <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_supervoxel.html">Supervoxel</a> templated on the input point type. Supervoxels have the following fields:</p>
<ul class="simple">
<li><tt class="docutils literal"><span class="pre">normal_</span></tt> The normal calculated for the voxels contained in the supervoxel</li>
<li><tt class="docutils literal"><span class="pre">centroid_</span></tt> The centroid of the supervoxel - average voxel</li>
<li><tt class="docutils literal"><span class="pre">voxels_</span></tt> A Pointcloud of the voxels in the supervoxel</li>
<li><tt class="docutils literal"><span class="pre">normals_</span></tt> A Pointcloud of the normals for the points in the supervoxel</li>
</ul>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">std</span><span class="o">::</span><span class="n">map</span> <span class="o">&lt;</span><span class="kt">uint32_t</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Supervoxel</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="o">&gt;</span> <span class="n">supervoxel_clusters</span><span class="p">;</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_highlight</span> <span class="p">(</span><span class="s">&quot;Extracting supervoxels!</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
  <span class="n">super</span><span class="p">.</span><span class="n">extract</span> <span class="p">(</span><span class="n">supervoxel_clusters</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_info</span> <span class="p">(</span><span class="s">&quot;Found %d supervoxels</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">supervoxel_clusters</span><span class="p">.</span><span class="n">size</span> <span class="p">());</span>
</pre></div>
</div>
<p>We then load a viewer and use some of the getter functions of <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_supervoxel_clustering.html">SupervoxelClustering</a> to pull out clouds to display. <tt class="docutils literal"><span class="pre">voxel_centroid_cloud</span></tt> contains the voxel centroids coming out of the octree (basically the downsampled original cloud), and <tt class="docutils literal"><span class="pre">colored_voxel_cloud</span></tt> are the voxels colored according to their supervoxel labels (random colors). <tt class="docutils literal"><span class="pre">sv_normal_cloud</span></tt> contains a cloud of the supervoxel normals, but we don&#8217;t display it here so that the graph is visible.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">viewer</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="p">(</span><span class="s">&quot;3D Viewer&quot;</span><span class="p">));</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setBackgroundColor</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>

  <span class="n">PointCloudT</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">voxel_centroid_cloud</span> <span class="o">=</span> <span class="n">super</span><span class="p">.</span><span class="n">getVoxelCentroidCloud</span> <span class="p">();</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">voxel_centroid_cloud</span><span class="p">,</span> <span class="s">&quot;voxel centroids&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span><span class="mf">2.0</span><span class="p">,</span> <span class="s">&quot;voxel centroids&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_OPACITY</span><span class="p">,</span><span class="mf">0.95</span><span class="p">,</span> <span class="s">&quot;voxel centroids&quot;</span><span class="p">);</span>

  <span class="n">PointCloudT</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">colored_voxel_cloud</span> <span class="o">=</span> <span class="n">super</span><span class="p">.</span><span class="n">getColoredVoxelCloud</span> <span class="p">();</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">colored_voxel_cloud</span><span class="p">,</span> <span class="s">&quot;colored voxels&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_OPACITY</span><span class="p">,</span><span class="mf">0.8</span><span class="p">,</span> <span class="s">&quot;colored voxels&quot;</span><span class="p">);</span>

  <span class="n">PointNCloudT</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">sv_normal_cloud</span> <span class="o">=</span> <span class="n">super</span><span class="p">.</span><span class="n">makeSupervoxelNormalCloud</span> <span class="p">(</span><span class="n">supervoxel_clusters</span><span class="p">);</span>
  <span class="c1">//We have this disabled so graph is easy to see, uncomment to see supervoxel normals</span>
  <span class="c1">//viewer-&gt;addPointCloudNormals&lt;PointNormal&gt; (sv_normal_cloud,1,0.05f, &quot;supervoxel_normals&quot;);</span>
</pre></div>
</div>
<p>Finally, we extract the supervoxel adjacency list (in the form of a multimap of label adjacencies).</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">print_highlight</span> <span class="p">(</span><span class="s">&quot;Getting supervoxel adjacency</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">multimap</span><span class="o">&lt;</span><span class="kt">uint32_t</span><span class="p">,</span> <span class="kt">uint32_t</span><span class="o">&gt;</span> <span class="n">supervoxel_adjacency</span><span class="p">;</span>
  <span class="n">super</span><span class="p">.</span><span class="n">getSupervoxelAdjacency</span> <span class="p">(</span><span class="n">supervoxel_adjacency</span><span class="p">);</span>
</pre></div>
</div>
<p>Then we iterate through the multimap, creating a point cloud of the centroids of each supervoxel&#8217;s neighbors.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">std</span><span class="o">::</span><span class="n">multimap</span><span class="o">&lt;</span><span class="kt">uint32_t</span><span class="p">,</span><span class="kt">uint32_t</span><span class="o">&gt;::</span><span class="n">iterator</span> <span class="n">label_itr</span> <span class="o">=</span> <span class="n">supervoxel_adjacency</span><span class="p">.</span><span class="n">begin</span> <span class="p">();</span>
  <span class="k">for</span> <span class="p">(</span> <span class="p">;</span> <span class="n">label_itr</span> <span class="o">!=</span> <span class="n">supervoxel_adjacency</span><span class="p">.</span><span class="n">end</span> <span class="p">();</span> <span class="p">)</span>
  <span class="p">{</span>
    <span class="c1">//First get the label</span>
    <span class="kt">uint32_t</span> <span class="n">supervoxel_label</span> <span class="o">=</span> <span class="n">label_itr</span><span class="o">-&gt;</span><span class="n">first</span><span class="p">;</span>
    <span class="c1">//Now get the supervoxel corresponding to the label</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">Supervoxel</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">supervoxel</span> <span class="o">=</span> <span class="n">supervoxel_clusters</span><span class="p">.</span><span class="n">at</span> <span class="p">(</span><span class="n">supervoxel_label</span><span class="p">);</span>

    <span class="c1">//Now we need to iterate through the adjacent supervoxels and make a point cloud of them</span>
    <span class="n">PointCloudT</span> <span class="n">adjacent_supervoxel_centers</span><span class="p">;</span>
    <span class="n">std</span><span class="o">::</span><span class="n">multimap</span><span class="o">&lt;</span><span class="kt">uint32_t</span><span class="p">,</span><span class="kt">uint32_t</span><span class="o">&gt;::</span><span class="n">iterator</span> <span class="n">adjacent_itr</span> <span class="o">=</span> <span class="n">supervoxel_adjacency</span><span class="p">.</span><span class="n">equal_range</span> <span class="p">(</span><span class="n">supervoxel_label</span><span class="p">).</span><span class="n">first</span><span class="p">;</span>
    <span class="k">for</span> <span class="p">(</span> <span class="p">;</span> <span class="n">adjacent_itr</span><span class="o">!=</span><span class="n">supervoxel_adjacency</span><span class="p">.</span><span class="n">equal_range</span> <span class="p">(</span><span class="n">supervoxel_label</span><span class="p">).</span><span class="n">second</span><span class="p">;</span> <span class="o">++</span><span class="n">adjacent_itr</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">Supervoxel</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">neighbor_supervoxel</span> <span class="o">=</span> <span class="n">supervoxel_clusters</span><span class="p">.</span><span class="n">at</span> <span class="p">(</span><span class="n">adjacent_itr</span><span class="o">-&gt;</span><span class="n">second</span><span class="p">);</span>
      <span class="n">adjacent_supervoxel_centers</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">neighbor_supervoxel</span><span class="o">-&gt;</span><span class="n">centroid_</span><span class="p">);</span>
    <span class="p">}</span>
</pre></div>
</div>
<p>Then we create a string label for the supervoxel graph we will draw and call <tt class="docutils literal"><span class="pre">addSupervoxelConnectionsToViewer</span></tt>, a drawing helper function implemented later in the tutorial code. The details of <tt class="docutils literal"><span class="pre">addSupervoxelConnectionsToViewer</span></tt> are beyond the scope of this tutorial, but all it does is draw a star polygon mesh of the supervoxel centroid to all of its neighbors centroids. We need to do this like this because adding individual lines using the <tt class="docutils literal"><span class="pre">addLine</span></tt> functionality of <tt class="docutils literal"><span class="pre">pcl_visualizer</span></tt> is too slow for large numbers of lines.</p>
<div class="highlight-cpp"><div class="highlight"><pre>    <span class="c1">//Now we make a name for this polygon</span>
    <span class="n">std</span><span class="o">::</span><span class="n">stringstream</span> <span class="n">ss</span><span class="p">;</span>
    <span class="n">ss</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;supervoxel_&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">supervoxel_label</span><span class="p">;</span>
    <span class="c1">//This function is shown below, but is beyond the scope of this tutorial - basically it just generates a &quot;star&quot; polygon mesh from the points given</span>
    <span class="n">addSupervoxelConnectionsToViewer</span> <span class="p">(</span><span class="n">supervoxel</span><span class="o">-&gt;</span><span class="n">centroid_</span><span class="p">,</span> <span class="n">adjacent_supervoxel_centers</span><span class="p">,</span> <span class="n">ss</span><span class="p">.</span><span class="n">str</span> <span class="p">(),</span> <span class="n">viewer</span><span class="p">);</span>
    <span class="c1">//Move iterator forward to next label</span>
    <span class="n">label_itr</span> <span class="o">=</span> <span class="n">supervoxel_adjacency</span><span class="p">.</span><span class="n">upper_bound</span> <span class="p">(</span><span class="n">supervoxel_label</span><span class="p">);</span>
</pre></div>
</div>
<p>This results in a supervoxel graph that looks like this for seed size of 0.1m (top) and 0.05m (middle). The bottom is the original cloud, given for reference.:</p>
<img alt="_images/supervoxel_clustering_results.jpg" class="align-center" src="_images/supervoxel_clustering_results.jpg" />
</div>
<div class="section" id="compiling-and-running-the-program">
<h1>Compiling and running the program</h1>
<p>Create a <tt class="docutils literal"><span class="pre">CMakeLists.txt</span></tt> file with the following content (or download it <a class="reference download internal" href="_downloads/CMakeLists2.txt"><tt class="xref download docutils literal"><span class="pre">here</span></tt></a>):</p>
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

<span class="nb">project</span><span class="p">(</span><span class="s">supervoxel_clustering</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.7</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">supervoxel_clustering</span> <span class="s">supervoxel_clustering.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">supervoxel_clustering</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span> 
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it like so, assuming the pcd file is in the same folder as the executable:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./supervoxel_clustering milk_cartoon_all_small_clorox.pcd
</pre></div>
</div>
<p>Don&#8217;t be afraid to play around with the parameters (especially the seed size, -s) to see what happens. The pcd file name should always be the first parameter!</p>
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