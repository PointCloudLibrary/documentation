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
    
    <title>Getting Started / Basic Structures</title>
    
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
            
  <div class="section" id="getting-started-basic-structures">
<span id="basic-structures"></span><h1>Getting Started / Basic Structures</h1>
<p>The basic data type in PCL 1.x is a <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_point_cloud.html">PointCloud</a>. A
PointCloud is a C++ class which contains the following data fields:</p>
<blockquote>
<div><ul>
<li><p class="first"><a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_point_cloud.html#a2926818b0d2a18d8ca89897794ad68f0">width</a> (int)</p>
<p>Specifies the width of the point cloud dataset in the number of points. <em>width</em> has two meanings:</p>
<blockquote>
<div><ul class="simple">
<li>it can specify the total number of points in the cloud (equal with the number of elements in <strong>points</strong> &#8211; see below) for unorganized datasets;</li>
<li>it can specify the width (total number of points in a row) of an organized point cloud dataset.</li>
</ul>
</div></blockquote>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">An <strong>organized point cloud</strong> dataset is the name given to point clouds
that resemble an organized image (or matrix) like structure, where the
data is split into rows and columns. Examples of such point clouds
include data coming from stereo cameras or Time Of Flight cameras. The
advantages of a organized dataset is that by knowing the relationship
between adjacent points (e.g. pixels), nearest neighbor operations are
much more efficient, thus speeding up the computation and lowering the
costs of certain algorithms in PCL.</p>
</div>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">An <strong>projectable point cloud</strong> dataset is the name given to point clouds
that have a correlation according to a pinhole camera model between the (u,v) index
of a point in the organized point cloud and the actual 3D values. This correlation can be
expressed in it&#8217;s easiest form as: u = f*x/z and v = f*y/z</p>
</div>
<p>Examples:</p>
<div class="highlight-python"><div class="highlight"><pre>cloud.width = 640; // there are 640 points per line
</pre></div>
</div>
</li>
<li><p class="first"><a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_point_cloud.html#ab8958d773449d7927e72201e7534d32f">height</a> (int)</p>
<p>Specifies the height of the point cloud dataset in the number of points. <em>height</em> has two meanings:</p>
<blockquote>
<div><ul class="simple">
<li>it can specify the height (total number of rows) of an organized point cloud dataset;</li>
<li>it is set to <strong>1</strong> for unorganized datasets (<em>thus used to check whether a dataset is organized or not</em>).</li>
</ul>
<p>Example:</p>
<div class="highlight-python"><div class="highlight"><pre>cloud.width = 640; // Image-like organized structure, with 640 rows and 480 columns,
cloud.height = 480; // thus 640*480=307200 points total in the dataset
</pre></div>
</div>
<p>Example:</p>
<div class="highlight-python"><div class="highlight"><pre>cloud.width = 307200;
cloud.height = 1; // unorganized point cloud dataset with 307200 points
</pre></div>
</div>
</div></blockquote>
</li>
<li><p class="first"><a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_point_cloud.html#a26ff8d864157a2df538216f372405588">points</a> (std::vector&lt;PointT&gt;)</p>
<p>Contains the data array where all the points of type <strong>PointT</strong> are stored. For example, for a cloud containing XYZ data, <strong>points</strong> contains a vector of <em>pcl::PointXYZ</em> elements:</p>
<div class="highlight-python"><div class="highlight"><pre>pcl::PointCloud&lt;pcl::PointXYZ&gt; cloud;
std::vector&lt;pcl::PointXYZ&gt; data = cloud.points;
</pre></div>
</div>
</li>
<li><p class="first"><a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_point_cloud.html#a73140025f021b4e98109558a7dd39a21">is_dense</a> (bool)</p>
<p>Specifies if all the data in <strong>points</strong> is finite (true), or whether the XYZ values of certain points might contain Inf/NaN values (false).</p>
</li>
<li><p class="first"><a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_point_cloud.html#a73d13cc1d5faae4e57773b53d01844b7">sensor_origin_</a> (Eigen::Vector4f)</p>
<p>Specifies the sensor acquisition pose (origin/translation). This member is usually optional, and not used by the majority of the algorithms in PCL.</p>
</li>
<li><p class="first"><a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_point_cloud.html#a4cf725ae7670e05fbe0ec8270450b3dc">sensor_orientation_</a> (Eigen::Quaternionf)</p>
<p>Specifies the sensor acquisition pose (orientation). This member is usually optional, and not used by the majority of the algorithms in PCL.</p>
</li>
</ul>
</div></blockquote>
<p>To simplify development, the <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_point_cloud.html">PointCloud</a> class contains
a number of helper member functions. For example, users don&#8217;t have to check if
<strong>height</strong> equals 1 or not in their code in order to see if a dataset is
organized or not, but instead use <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_point_cloud.html#a148139a1b665a2158f2c163b7731a834">PointCloud()</a>:</p>
<div class="highlight-python"><div class="highlight"><pre>if (!cloud.isOrganized ())
  ...
</pre></div>
</div>
<p>The <strong>PointT</strong> type is the primary point data type and describes what each
individual element of <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_point_cloud.html#a26ff8d864157a2df538216f372405588">points</a> holds. PCL comes
with a large variety of different point types, most explained in the
<a class="reference internal" href="adding_custom_ptype.php#adding-custom-ptype"><em>Adding your own custom PointT type</em></a> tutorial.</p>
</div>
<div class="section" id="compiling-your-first-code-example">
<h1>Compiling your first code example</h1>
<p>Until we find the right minimal code example, please take a look at the
<a class="reference internal" href="using_pcl_pcl_config.php#using-pcl-pcl-config"><em>Using PCL in your own project</em></a> and <a class="reference internal" href="writing_new_classes.php#writing-new-classes"><em>Writing a new PCL class</em></a> tutorials to see how
to compile and write code for or using PCL.</p>
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