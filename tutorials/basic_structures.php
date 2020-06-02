<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation - Point Cloud Library (PCL)</title>
</head>

<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8" />
    <title>Getting Started / Basic Structures &#8212; PCL 0.0 documentation</title>
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
            
  <div class="section" id="getting-started-basic-structures">
<span id="basic-structures"></span><h1>Getting Started / Basic Structures</h1>
<p>The basic data type in PCL 1.x is a <a href="#id1"><span class="problematic" id="id2">:pcl:`PointCloud&lt;pcl::PointCloud&gt;`</span></a>. A
PointCloud is a C++ class which contains the following data fields:</p>
<blockquote>
<div><ul>
<li><p><a href="#id3"><span class="problematic" id="id4">:pcl:`width&lt;pcl::PointCloud::width&gt;`</span></a> (int)</p>
<p>Specifies the width of the point cloud dataset in the number of points. <em>width</em> has two meanings:</p>
<blockquote>
<div><ul class="simple">
<li><p>it can specify the total number of points in the cloud (equal with the number of elements in <strong>points</strong> – see below) for unorganized datasets;</p></li>
<li><p>it can specify the width (total number of points in a row) of an organized point cloud dataset.</p></li>
</ul>
</div></blockquote>
<div class="admonition note">
<p class="admonition-title">Note</p>
<p>An <strong>organized point cloud</strong> dataset is the name given to point clouds
that resemble an organized image (or matrix) like structure, where the
data is split into rows and columns. Examples of such point clouds
include data coming from stereo cameras or Time Of Flight cameras. The
advantages of an organized dataset is that by knowing the relationship
between adjacent points (e.g. pixels), nearest neighbor operations are
much more efficient, thus speeding up the computation and lowering the
costs of certain algorithms in PCL.</p>
</div>
<div class="admonition note">
<p class="admonition-title">Note</p>
<p>An <strong>projectable point cloud</strong> dataset is the name given to point clouds
that have a correlation according to a pinhole camera model between the (u,v) index
of a point in the organized point cloud and the actual 3D values. This correlation can be
expressed in it’s easiest form as: u = f*x/z and v = f*y/z</p>
</div>
<p>Examples:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">cloud</span><span class="o">.</span><span class="n">width</span> <span class="o">=</span> <span class="mi">640</span><span class="p">;</span> <span class="o">//</span> <span class="n">there</span> <span class="n">are</span> <span class="mi">640</span> <span class="n">points</span> <span class="n">per</span> <span class="n">line</span>
</pre></div>
</div>
</li>
<li><p><a href="#id5"><span class="problematic" id="id6">:pcl:`height&lt;pcl::PointCloud::height&gt;`</span></a> (int)</p>
<p>Specifies the height of the point cloud dataset in the number of points. <em>height</em> has two meanings:</p>
<blockquote>
<div><ul class="simple">
<li><p>it can specify the height (total number of rows) of an organized point cloud dataset;</p></li>
<li><p>it is set to <strong>1</strong> for unorganized datasets (<em>thus used to check whether a dataset is organized or not</em>).</p></li>
</ul>
<p>Example:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">cloud</span><span class="o">.</span><span class="n">width</span> <span class="o">=</span> <span class="mi">640</span><span class="p">;</span> <span class="o">//</span> <span class="n">Image</span><span class="o">-</span><span class="n">like</span> <span class="n">organized</span> <span class="n">structure</span><span class="p">,</span> <span class="k">with</span> <span class="mi">480</span> <span class="n">rows</span> <span class="ow">and</span> <span class="mi">640</span> <span class="n">columns</span><span class="p">,</span>
<span class="n">cloud</span><span class="o">.</span><span class="n">height</span> <span class="o">=</span> <span class="mi">480</span><span class="p">;</span> <span class="o">//</span> <span class="n">thus</span> <span class="mi">640</span><span class="o">*</span><span class="mi">480</span><span class="o">=</span><span class="mi">307200</span> <span class="n">points</span> <span class="n">total</span> <span class="ow">in</span> <span class="n">the</span> <span class="n">dataset</span>
</pre></div>
</div>
<p>Example:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">cloud</span><span class="o">.</span><span class="n">width</span> <span class="o">=</span> <span class="mi">307200</span><span class="p">;</span>
<span class="n">cloud</span><span class="o">.</span><span class="n">height</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span> <span class="o">//</span> <span class="n">unorganized</span> <span class="n">point</span> <span class="n">cloud</span> <span class="n">dataset</span> <span class="k">with</span> <span class="mi">307200</span> <span class="n">points</span>
</pre></div>
</div>
</div></blockquote>
</li>
<li><p><a href="#id7"><span class="problematic" id="id8">:pcl:`points&lt;pcl::PointCloud::points&gt;`</span></a> (std::vector&lt;PointT&gt;)</p>
<p>Contains the data array where all the points of type <strong>PointT</strong> are stored. For example, for a cloud containing XYZ data, <strong>points</strong> contains a vector of <em>pcl::PointXYZ</em> elements:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">pcl</span><span class="p">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="p">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">cloud</span><span class="p">;</span>
<span class="n">std</span><span class="p">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">pcl</span><span class="p">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">data</span> <span class="o">=</span> <span class="n">cloud</span><span class="o">.</span><span class="n">points</span><span class="p">;</span>
</pre></div>
</div>
</li>
<li><p><a href="#id9"><span class="problematic" id="id10">:pcl:`is_dense&lt;pcl::PointCloud::is_dense&gt;`</span></a> (bool)</p>
<p>Specifies if all the data in <strong>points</strong> is finite (true), or whether the XYZ values of certain points might contain Inf/NaN values (false).</p>
</li>
<li><p><a href="#id11"><span class="problematic" id="id12">:pcl:`sensor_origin_&lt;pcl::PointCloud::sensor_origin_&gt;`</span></a> (Eigen::Vector4f)</p>
<p>Specifies the sensor acquisition pose (origin/translation). This member is usually optional, and not used by the majority of the algorithms in PCL.</p>
</li>
<li><p><a href="#id13"><span class="problematic" id="id14">:pcl:`sensor_orientation_&lt;pcl::PointCloud::sensor_orientation_&gt;`</span></a> (Eigen::Quaternionf)</p>
<p>Specifies the sensor acquisition pose (orientation). This member is usually optional, and not used by the majority of the algorithms in PCL.</p>
</li>
</ul>
</div></blockquote>
<p>To simplify development, the <a href="#id15"><span class="problematic" id="id16">:pcl:`PointCloud&lt;pcl::PointCloud&gt;`</span></a> class contains
a number of helper member functions. For example, users don’t have to check if
<strong>height</strong> equals 1 or not in their code in order to see if a dataset is
organized or not, but instead use <a href="#id17"><span class="problematic" id="id18">:pcl:`PointCloud&lt;pcl::PointCloud::isOrganized&gt;`</span></a>:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>if (!cloud.isOrganized ())
  ...
</pre></div>
</div>
<p>The <strong>PointT</strong> type is the primary point data type and describes what each
individual element of <a href="#id19"><span class="problematic" id="id20">:pcl:`points&lt;pcl::PointCloud::points&gt;`</span></a> holds. PCL comes
with a large variety of different point types, most explained in the
<a class="reference internal" href="adding_custom_ptype.php#adding-custom-ptype"><span class="std std-ref">Adding your own custom PointT type</span></a> tutorial.</p>
</div>
<div class="section" id="compiling-your-first-code-example">
<h1>Compiling your first code example</h1>
<p>Until we find the right minimal code example, please take a look at the
<a class="reference internal" href="using_pcl_pcl_config.php#using-pcl-pcl-config"><span class="std std-ref">Using PCL in your own project</span></a> and <a class="reference internal" href="writing_new_classes.php#writing-new-classes"><span class="std std-ref">Writing a new PCL class</span></a> tutorials to see how
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