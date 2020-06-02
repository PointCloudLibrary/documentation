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
    
    <title>PCL 2.x API consideration guide</title>
    
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
            
  <div class="section" id="pcl-2-x-api-consideration-guide">
<span id="pcl2"></span><h1>PCL 2.x API consideration guide</h1>
<p>With the PCL 1.x API locked and a few releases already underway, it&#8217;s time to
consider what the next generation of libraries should look like. This document
discusses a series of changes to the current API, from base classes to higher
level algorithms.</p>
<div class="section" id="major-changes">
<h2>Major changes</h2>
<div class="section" id="pcl-pointcloud">
<h3>1.1 pcl::PointCloud</h3>
<p>The <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_point_cloud.html">PointCloud</a> class represents the base class in PCL
for holding <strong>nD</strong> (n dimensional) data.</p>
<dl class="docutils">
<dt>The 1.x API includes the following data members:</dt>
<dd><ul class="first last simple">
<li><a class="reference external" href="http://docs.pointclouds.org/trunk/structpcl_1_1_p_c_l_header.html">PCLHeader</a> (coming from ROS)<ul>
<li><strong>uint32_t</strong> <a class="reference external" href="http://docs.pointclouds.org/trunk/structpcl_1_1_p_c_l_header.html#a00fe11308c4e18133b1d85c7f05fe432">seq</a> - a sequence number</li>
<li><strong>uint64_t</strong> <a class="reference external" href="http://docs.pointclouds.org/trunk/structpcl_1_1_p_c_l_header.html#a7ccf28ecce53332cd572d1ba982a4579">stamp</a> - a timestamp associated with the time when the data was acquired</li>
<li><strong>std::string</strong> <a class="reference external" href="http://docs.pointclouds.org/trunk/structpcl_1_1_p_c_l_header.html#a21ef5399c3f81709f3cf48989607e698">frame_id</a> - a TF frame ID</li>
</ul>
</li>
<li><strong>std::vector&lt;T&gt;</strong> <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_point_cloud.html#a26ff8d864157a2df538216f372405588">points</a> - a std C++ vector of T data. T can be a structure of any of the types defined in <cite>point_types.h</cite>.</li>
<li><strong>uint32_t</strong> <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_point_cloud.html#a2926818b0d2a18d8ca89897794ad68f0">width</a> - the width (for organized datasets) of the data. Set to the number of points for unorganized data.</li>
<li><strong>uint32_t</strong> <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_point_cloud.html#ab8958d773449d7927e72201e7534d32f">height</a> - the height (for organized datasets) of the data. Set to 1 for unorganized data.</li>
<li><strong>bool</strong> <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_point_cloud.html#a73140025f021b4e98109558a7dd39a21">is_dense</a> - true if the data contains only valid numbers (e.g., no NaN or -/+Inf, etc). False otherwise.</li>
<li><strong>Eigen::Vector4f</strong> <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_point_cloud.html#a73d13cc1d5faae4e57773b53d01844b7">sensor_origin_</a> - the origin (pose) of the acquisition sensor in the current data coordinate system.</li>
<li><strong>Eigen::Quaternionf</strong> <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_point_cloud.html#a4cf725ae7670e05fbe0ec8270450b3dc">sensor_orientation_</a> - the origin (orientation) of hte acquisition sensor in the current data coordinate system.</li>
</ul>
</dd>
</dl>
<p>Proposals for the 2.x API:</p>
<blockquote>
<div><ul>
<li><p class="first">drop templating on point types, thus making <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_point_cloud.html">PointCloud</a> template free</p>
</li>
<li><p class="first">drop the <a class="reference external" href="http://docs.pointclouds.org/trunk/structpcl_1_1_p_c_l_header.html">PCLHeader</a> structure, or consolidate all the above information (width, height, is_dense, sensor_origin, sensor_orientation) into a single struct</p>
</li>
<li><p class="first">make sure we can access a slice of the data as a <em>2D image</em>, thus allowing fast 2D displaying, [u, v] operations, etc</p>
</li>
<li><p class="first">make sure we can access a slice of the data as a subpoint cloud: only certain points are chosen from the main point cloud</p>
</li>
<li><p class="first">implement channels (of a single type!) as data holders, e.g.:</p>
<ul class="simple">
<li>cloud[&#8220;xyz&#8221;] =&gt; gets all 3D x,y,z data</li>
<li>cloud[&#8220;normals&#8221;] =&gt; gets all surface normal data</li>
<li>etc</li>
</ul>
</li>
<li><p class="first">internals should be hidden : only accessors (begin, end ...) are public, this facilitating the change of the underlying structure</p>
</li>
<li><p class="first">Capability to construct point cloud types containing the necessary channels
<em>at runtime</em>. This will be particularly useful for run-time configuration of
input sensors and for reading point clouds from files, which may contain a
variety of point cloud layouts not known until the file is opened.</p>
</li>
<li><p class="first">Complete traits system to identify what data/channels a cloud stores at
runtime, facilitating decision making in software that uses PCL. (e.g.
generic component wrappers.)</p>
</li>
<li><p class="first">Stream-based IO sub-system to allow developers to load a stream of point
clouds and &#8220;play&#8221; them through their algorithm(s), as well as easily capture
a stream of point clouds (e.g. from a Kinect). Perhaps based on
Boost::Iostreams.</p>
</li>
<li><p class="first">Given the experience on <a class="reference external" href="https://github.com/ethz-asl/libpointmatcher">libpointmatcher</a>,
we (François Pomerleau and Stéphane Magnenat) propose the following data structures:</p>
<div class="highlight-python"><div class="highlight"><pre>cloud = map&lt;space_identifier, space&gt;
space = tuple&lt;type, components_identifiers, data_matrix&gt;
components_identifiers = vector&lt;component_identifier&gt;
data_matrix = Eigen matrix
space_identifier = string with standardised naming (pos, normals, color, etc.)
component_identifier = string with standardised naming (x, y, r, g, b, etc.)
type = type of space, underlying scalar type + distance definition (float with euclidean 2-norm distance, float representing gaussians with Mahalanobis distance, binary with manhattan distance, float with euclidean infinity norm distance, etc.)
</pre></div>
</div>
<dl class="docutils">
<dt>For instance, a simple point + color scenario could be::</dt>
<dd><p class="first last">cloud = { &#8220;pos&#8221; =&gt; pos_space, &#8220;color&#8221; =&gt; color_space }
pos_space = ( &#8220;float with euclidean 2-norm distance&#8221;, { &#8220;x&#8221;, &#8220;y&#8221;, &#8220;z&#8221; }, [[(0.3,0,1.3) , ... , (1.2,3.1,2)], ... , [(1,0.3,1) , ... , (2,0,3.5)] )
color_space = ( &#8220;uint8 with rgb distance&#8221;, { &#8220;r&#8221;, &#8220;g&#8221;, &#8220;b&#8221; }, [[(0,255,0), ... , (128,255,32)] ... [(12,54,31) ... (255,0,192)]] )</p>
</dd>
</dl>
</li>
</ul>
</div></blockquote>
</div>
<div class="section" id="pointtypes">
<h3>1.2 PointTypes</h3>
<blockquote>
<div><ol class="arabic simple">
<li>Eigen::Vector4f or Eigen::Vector3f ??</li>
<li>Large points cause significant perfomance penalty for GPU. Let&#8217;s assume that point sizes up to 16 bytes are suitable. This is some compromise between SOA and AOS. Structures like pcl::Normal (size = 32) is not desirable. SOA is better in this case.</li>
</ol>
</div></blockquote>
</div>
<div class="section" id="gpu-support">
<h3>1.3 GPU support</h3>
<blockquote>
<div><ol class="arabic">
<li><p class="first">Containers for GPU memory. pcl::gpu::DeviceMemory/DeviceMemory2D/DeviceArray&lt;T&gt;/DeviceArray2D&lt;T&gt; (Thrust containers are incinvinient).</p>
<blockquote>
<div><ul class="simple">
<li>DeviceArray2D&lt;T&gt; is container for organized point cloud data (supports row alignment)</li>
</ul>
</div></blockquote>
</li>
<li><p class="first">PointCloud Channels for GPU memory. Say, with &#8220;_gpu&#8221; postfix.</p>
<blockquote>
<div><ul class="simple">
<li>cloud[&#8220;xyz_gpu&#8221;] =&gt; gets channel with 3D x,y,z data allocated on GPU.</li>
<li>GPU functions (ex. gpu::computeNormals) create new channel in cloud (ex. &#8220;normals_gpu&#8221;) and write there. Users can preallocate the channel and data inside it in order to save time on allocations.</li>
<li>Users must manually invoke uploading/downloading data to/from GPU. This provides better understanding how much each operation costs.</li>
</ul>
</div></blockquote>
</li>
<li><p class="first">Two layers in GPU part:  host layer(nvcc-independent interface) and device(for advanced use, for sharing code compiled by nvcc):</p>
<blockquote>
<div><ul class="simple">
<li>namespace pcl::cuda (can depend on CUDA headers) or pcl::gpu (completely independent from CUDA, OpenCL support in future?).</li>
<li>namespace pcl::device for device layer, only headers.</li>
</ul>
</div></blockquote>
</li>
<li><p class="first">Async operation support???</p>
</li>
</ol>
</div></blockquote>
</div>
<div class="section" id="keypoints-and-features">
<h3>1.4 Keypoints and features</h3>
<blockquote>
<div><ol class="arabic">
<li><p class="first">The name Feature is a bit misleading, since it has tons of meanings. Alternatives are Descriptor or FeatureDescription.</p>
</li>
<li><p class="first">In the feature description, there is no need in separate FeatureFromNormals class and setNormals() method, since all the required channels are contained in one input. We still need separate setSearchSurface() though.</p>
</li>
<li><p class="first">There exist different types of keypoints (corners, blobs, regions), so keypoint detector might return some meta-information besides the keypoint locations (scale, orientation etc.). Some channels of that meta-information are required by some descriptors. There are options how to deliver that information from keypoints to descriptor, but it should be easy to pass it if a user doesn&#8217;t change anything. This interface should be uniform to allow for switching implementations and automated benchmarking. Still one might want to set, say, custom orientations, different from what detector returned.</p>
<blockquote>
<div><p>to be continued...</p>
</div></blockquote>
</li>
</ol>
</div></blockquote>
</div>
<div class="section" id="data-slices">
<h3>1.5 Data slices</h3>
<p>Anything involving a slice of data should use size_t for indices and not int. E.g the indices of the inliers in RANSAC, the focused points in RANSAC ...</p>
</div>
<div class="section" id="ransac">
<h3>1.6 RANSAC</h3>
<blockquote>
<div><ul class="simple">
<li>Renaming the functions and internal variables: everything should be named with _src and _tgt: we have confusing names like <a href="#id1"><span class="problematic" id="id2">indices_</span></a> and <a href="#id3"><span class="problematic" id="id4">indices_tgt_</span></a> (and no <a href="#id5"><span class="problematic" id="id6">indices_src_</span></a>), setInputCloud and setInputTarget (duuh, everything is an input, it should be setTarget, setSource), in the code, a sample is named: selection, <a href="#id7"><span class="problematic" id="id8">model_</span></a> and samples. getModelCoefficients is confusing with getModel (this one should be getBestSample).</li>
<li>no const-correctness all over, it&#8217;s pretty scary: all the get should be const, selectWithinDistance and so on too.</li>
<li>the getModel, getInliers function should not force you to fill a vector: you should just return a const reference to the internal vector: that could allow you to save a useless copy</li>
<li>some private members should be made protected in the sub sac models (like sac_model_registration) so that we can inherit from them.</li>
<li>the SampleConsensusModel should be independent from point clouds so that we can create our own model for whatever library. Then, the one used in the specialize models (like sac_model_registration and so on) should inherit from it and have constructors based on PointClouds like now. Maybe we should name those PclSampleConsensusModel or something (or have SampleConsensusModelBase and keep the naming for SampleConsensusModel).</li>
</ul>
</div></blockquote>
</div>
</div>
<div class="section" id="minor-changes">
<h2>Minor changes</h2>
</div>
<div class="section" id="concepts">
<h2>Concepts</h2>
<p>See <a class="reference external" href="http://dev.pointclouds.org/issues/567">http://dev.pointclouds.org/issues/567</a>.</p>
</div>
</div>
<div class="section" id="references">
<h1>References</h1>
<ul class="simple">
<li><a class="reference external" href="www4.in.tum.de/~blanchet/api-design.pdf">The Little Manual of API Design</a></li>
</ul>
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