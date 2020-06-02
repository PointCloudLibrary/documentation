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
    
    <title>Point Feature Histograms (PFH) descriptors</title>
    
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
            
  <div class="section" id="point-feature-histograms-pfh-descriptors">
<span id="pfh-estimation"></span><h1>Point Feature Histograms (PFH) descriptors</h1>
<p>As point feature representations go, surface normals and curvature estimates
are somewhat basic in their representations of the geometry around a specific
point. Though extremely fast and easy to compute, they cannot capture too much
detail, as they approximate the geometry of a point&#8217;s k-neighborhood with only
a few values. As a direct consequence, most scenes will contain many points
with the same or very similar feature values, thus reducing their informative
characteristics.</p>
<p>This tutorial introduces a family of 3D feature descriptors coined PFH (Point
Feature Histograms) for simplicity, presents their theoretical advantages and
discusses implementation details from PCL&#8217;s perspective. As a prerequisite,
please go ahead and read the <a class="reference internal" href="normal_estimation.php#normal-estimation"><em>Estimating Surface Normals in a PointCloud</em></a> tutorial first, as PFH
signatures rely on both xyz 3D data as well as surface normals.</p>
</div>
<div class="section" id="theoretical-primer">
<h1>Theoretical primer</h1>
<p>The goal of the PFH formulation is to encode a point&#8217;s k-neighborhood
geometrical properties by generalizing the mean curvature around the point
using a multi-dimensional histogram of values. This highly dimensional
hyperspace provides an informative signature for the feature representation, is
invariant to the 6D pose of the underlying surface, and copes very well with
different sampling densities or noise levels present in the neighborhood.</p>
<p>A Point Feature Histogram representation is based on the relationships between
the points in the k-neighborhood and their estimated surface normals. Simply
put, it attempts to capture as best as possible the sampled surface variations
by taking into account all the interactions between the directions of the
estimated normals. <strong>The resultant hyperspace is thus dependent on the quality of
the surface normal estimations at each point.</strong></p>
<p>The figure below presents an influence region diagram of the PFH computation
for a query point (<span class="math">p_q</span>), marked with red and placed in the middle of a
circle (sphere in 3D) with radius <strong>r</strong>, and all its <strong>k</strong> neighbors (points
with distances smaller than the radius <strong>r</strong>) are fully interconnected in a
mesh. The final PFH descriptor is computed as a histogram of relationships
between all pairs of points in the neighborhood, and thus has a computational
complexity of <span class="math">O(k^2)</span>.</p>
<img alt="_images/pfh_diagram.png" class="align-center" src="_images/pfh_diagram.png" />
<p>To compute the relative difference between two points <span class="math">p_i</span> and
<span class="math">p_j</span> and their associated normals <span class="math">n_i</span> and <span class="math">n_j</span>, we
define a fixed coordinate frame at one of the points (see the figure below).</p>
<div class="math">
<p><span class="math">{\mathsf u} =&amp; \boldsymbol{n}_s \\
{\mathsf v} =&amp;  {\mathsf u} \times \frac{(\boldsymbol{p}_t-\boldsymbol{p}_s)}{{\|\boldsymbol{p}_t-\boldsymbol{p}_s\|}_{2}} \\
{\mathsf w} =&amp; {\mathsf u} \times {\mathsf v}</span></p>
</div><img alt="_images/pfh_frame.png" class="align-center" src="_images/pfh_frame.png" />
<p>Using the above <strong>uvw</strong> frame, the difference between the two normals
<span class="math">n_s</span> and <span class="math">n_t</span> can be expressed as a set of angular features as
follows:</p>
<div class="math">
<p><span class="math">\alpha &amp;= {\mathsf v} \cdot \boldsymbol{n}_t \\
\phi   &amp;= {\mathsf u} \cdot \frac{(\boldsymbol{p}_t - \boldsymbol{p}_s)}{d}\\
\theta &amp;= \arctan ({\mathsf w} \cdot \boldsymbol{n}_t, {\mathsf u} \cdot \boldsymbol{n}_t) \\</span></p>
</div><p>where <strong>d</strong> is the Euclidean distance between the two points
<span class="math">\boldsymbol{p}_s</span> and <span class="math">\boldsymbol{p}_t</span>,
<span class="math">d={\|\boldsymbol{p}_t-\boldsymbol{p}_s\|}_2</span>.  The quadruplet
<span class="math">\langle\alpha, \phi, \theta, d\rangle</span> is computed for each pair of two
points in k-neighborhood, therefore reducing the 12 values (xyz and normal
information) of the two points and their normals to 4.</p>
<p>To estimate a PFH quadruplet for a pair of points, use:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="n">computePairFeatures</span> <span class="p">(</span><span class="k">const</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector4f</span> <span class="o">&amp;</span><span class="n">p1</span><span class="p">,</span> <span class="k">const</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector4f</span> <span class="o">&amp;</span><span class="n">n1</span><span class="p">,</span>
                     <span class="k">const</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector4f</span> <span class="o">&amp;</span><span class="n">p2</span><span class="p">,</span> <span class="k">const</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector4f</span> <span class="o">&amp;</span><span class="n">n2</span><span class="p">,</span>
                     <span class="kt">float</span> <span class="o">&amp;</span><span class="n">f1</span><span class="p">,</span> <span class="kt">float</span> <span class="o">&amp;</span><span class="n">f2</span><span class="p">,</span> <span class="kt">float</span> <span class="o">&amp;</span><span class="n">f3</span><span class="p">,</span> <span class="kt">float</span> <span class="o">&amp;</span><span class="n">f4</span><span class="p">);</span>
</pre></div>
</div>
<p>See the API documentation for additional details.</p>
<p>To create the final PFH representation for the query point, the set of all
quadruplets is binned into a histogram. The binning process divides each
features’s value range into <strong>b</strong> subdivisions, and counts the number of
occurrences in each subinterval. Since three out of the four features presented
above are measure of the angles between normals, their values can easily be
normalized to the same interval on the trigonometric circle. A binning example
is to divide each feature interval into the same number of equal parts, and
therefore create a histogram with <span class="math">b^4</span> bins in a fully correlated space.
In this space, a histogram bin increment corresponds to a point having certain
values for all its 4 features. The figure below presents examples of Point
Feature Histograms representations for different points in a cloud.</p>
<p>In some cases, the fourth feature, <strong>d</strong>, does not present an extreme
significance for 2.5D datasets, usually acquired in robotics, as the distance
between neighboring points increases from the viewpoint. Therefore, omitting
<strong>d</strong> for scans where the local point density influences this feature dimension
has proved to be beneficial.</p>
<img alt="_images/example_pfhs.jpg" class="align-center" src="_images/example_pfhs.jpg" />
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">For more information and mathematical derivations, including an analysis of PFH signatures for different surface geometries please see <a class="reference internal" href="how_features_work.php#rusudissertation" id="id1">[RusuDissertation]</a>.</p>
</div>
</div>
<div class="section" id="estimating-pfh-features">
<h1>Estimating PFH features</h1>
<p>Point Feature Histograms are implemented in PCL as part of the <a class="reference external" href="http://docs.pointclouds.org/trunk/a02944.html">pcl_features</a> library.</p>
<p>The default PFH implementation uses 5 binning subdivisions (e.g., each of the
four feature values will use this many bins from its value interval), and does
not include the distances (as explained above &#8211; although the
<strong>computePairFeatures</strong> method can be called by the user to obtain the
distances too, if desired) which results in a 125-byte array (<span class="math">5^3</span>) of
float values. These are stored in a <strong>pcl::PFHSignature125</strong> point type.</p>
<p>The following code snippet will estimate a set of PFH features for all the
points in the input dataset.</p>
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
34</pre></div></td><td class="code"><div class="highlight"><pre><span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/features/pfh.h&gt;</span>

<span class="p">{</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">normals</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span> <span class="p">());</span>

  <span class="p">...</span> <span class="n">read</span><span class="p">,</span> <span class="n">pass</span> <span class="n">in</span> <span class="n">or</span> <span class="n">create</span> <span class="n">a</span> <span class="n">point</span> <span class="n">cloud</span> <span class="n">with</span> <span class="n">normals</span> <span class="p">...</span>
  <span class="p">...</span> <span class="p">(</span><span class="n">note</span><span class="o">:</span> <span class="n">you</span> <span class="n">can</span> <span class="n">create</span> <span class="n">a</span> <span class="n">single</span> <span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;</span> <span class="k">if</span> <span class="n">you</span> <span class="n">want</span><span class="p">)</span> <span class="p">...</span>

  <span class="c1">// Create the PFH estimation class, and pass the input dataset+normals to it</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PFHEstimation</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PFHSignature125</span><span class="o">&gt;</span> <span class="n">pfh</span><span class="p">;</span>
  <span class="n">pfh</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">pfh</span><span class="p">.</span><span class="n">setInputNormals</span> <span class="p">(</span><span class="n">normals</span><span class="p">);</span>
  <span class="c1">// alternatively, if cloud is of tpe PointNormal, do pfh.setInputNormals (cloud);</span>

  <span class="c1">// Create an empty kdtree representation, and pass it to the PFH estimation object.</span>
  <span class="c1">// Its content will be filled inside the object, based on the given input dataset (as no other search surface is given).</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">tree</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="c1">//pcl::KdTreeFLANN&lt;pcl::PointXYZ&gt;::Ptr tree (new pcl::KdTreeFLANN&lt;pcl::PointXYZ&gt; ()); -- older call for PCL 1.5-</span>
  <span class="n">pfh</span><span class="p">.</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">tree</span><span class="p">);</span>

  <span class="c1">// Output datasets</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PFHSignature125</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">pfhs</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PFHSignature125</span><span class="o">&gt;</span> <span class="p">());</span>

  <span class="c1">// Use all neighbors in a sphere of radius 5cm</span>
  <span class="c1">// IMPORTANT: the radius used here has to be larger than the radius used to estimate the surface normals!!!</span>
  <span class="n">pfh</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="mf">0.05</span><span class="p">);</span>

  <span class="c1">// Compute the features</span>
  <span class="n">pfh</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">pfhs</span><span class="p">);</span>

  <span class="c1">// pfhs-&gt;points.size () should have the same size as the input cloud-&gt;points.size ()*</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
<p>The actual <strong>compute</strong> call from the <strong>PFHEstimation</strong> class does nothing internally but:</p>
<div class="highlight-python"><div class="highlight"><pre>for each point p in cloud P

  1. get the nearest neighbors of p

  2. for each pair of neighbors, compute the three angular values

  3. bin all the results in an output histogram
</pre></div>
</div>
<p>To compute a single PFH representation from a k-neighborhood, use:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="n">computePointPFHSignature</span> <span class="p">(</span><span class="k">const</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointInT</span><span class="o">&gt;</span> <span class="o">&amp;</span><span class="n">cloud</span><span class="p">,</span>
                          <span class="k">const</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointNT</span><span class="o">&gt;</span> <span class="o">&amp;</span><span class="n">normals</span><span class="p">,</span>
                          <span class="k">const</span> <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="o">&amp;</span><span class="n">indices</span><span class="p">,</span>
                          <span class="kt">int</span> <span class="n">nr_split</span><span class="p">,</span>
                          <span class="n">Eigen</span><span class="o">::</span><span class="n">VectorXf</span> <span class="o">&amp;</span><span class="n">pfh_histogram</span><span class="p">);</span>
</pre></div>
</div>
<p>Where <em>cloud</em> is the input point cloud that contains the points, <em>normals</em> is
the input point cloud that contains the normals (could be equal to cloud if
<em>PointInT=PointNT=PointNormal</em>), <em>indices</em> represents the set of k-nearest
neighbors from <em>cloud</em>, <em>nr_split</em> is the number of subdivisions to use for the
binning process for each feature interval, and <em>pfh_histogram</em> is the output
resultant histogram as an array of float values.</p>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p>For efficiency reasons, the <strong>compute</strong> method in <strong>PFHEstimation</strong> does not check if the normals contains NaN or infinite values.
Passing such values to <strong>compute()</strong> will result in undefined output.
It is advisable to check the normals, at least during the design of the processing chain or when setting the parameters.
This can be done by inserting the following code before the call to <strong>compute()</strong>:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">for</span> <span class="p">(</span><span class="kt">int</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">normals</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span><span class="p">();</span> <span class="n">i</span><span class="o">++</span><span class="p">)</span>
<span class="p">{</span>
  <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">pcl</span><span class="o">::</span><span class="n">isFinite</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span><span class="p">(</span><span class="n">normals</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">]))</span>
  <span class="p">{</span>
    <span class="n">PCL_WARN</span><span class="p">(</span><span class="s">&quot;normals[%d] is not finite</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">i</span><span class="p">);</span>
  <span class="p">}</span>
<span class="p">}</span>
</pre></div>
</div>
<p class="last">In production code, preprocessing steps and parameters should be set so that normals are finite or raise an error.</p>
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