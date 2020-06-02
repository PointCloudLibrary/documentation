<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation - Point Cloud Library (PCL)</title>
</head>

<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8" />
    <title>Single compilation units</title>
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
            
  <div class="section" id="single-compilation-units">
<span id="single-compile-unit"></span><h1>Single compilation units</h1>
<p>Even before reading <a class="footnote-reference brackets" href="#id3" id="id1">1</a>, we noticed a great speed up in compile time for all
PCL libraries if instead of compiling N object files and linking them together,
we compile only one, and include all the sources of the N files in this main
source. If you peek at an older version of PCL, you might notice things along
the lines of:</p>
<div class="highlight-cpp notranslate"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre>1
2
3
4
5
6</pre></div></td><td class="code"><div class="highlight"><pre><span></span> <span class="c1">// Include the implementations instead of compiling them separately to speed up compile time</span>
 <span class="cp">#include</span> <span class="cpf">&quot;extract_indices.cpp&quot;</span><span class="cp"></span>
 <span class="cp">#include</span> <span class="cpf">&quot;passthrough.cpp&quot;</span><span class="cp"></span>
 <span class="cp">#include</span> <span class="cpf">&quot;project_inliers.cpp&quot;</span><span class="cp"></span>
 <span class="cp">#include</span> <span class="cpf">&quot;statistical_outlier_removal.cpp&quot;</span><span class="cp"></span>
 <span class="cp">#include</span> <span class="cpf">&quot;voxel_grid.cpp&quot;</span><span class="cp"></span>
</pre></div>
</td></tr></table></div>
<p>and in CMakeLists.txt:</p>
<div class="highlight-cmake notranslate"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre>1
2
3
4
5
6
7
8
9</pre></div></td><td class="code"><div class="highlight"><pre><span></span> <span class="nb">rosbuild_add_library</span> <span class="p">(</span><span class="s">pcl_ros_filters</span>
                       <span class="s">src/pcl_ros/filters/filter.cpp</span>
                       <span class="c"># Compilation is much faster if we include all the following CPP files in filters.cpp</span>
                       <span class="c">#src/pcl_ros/filters/passthrough.cpp</span>
                       <span class="c">#src/pcl_ros/filters/project_inliers.cpp</span>
                       <span class="c">#src/pcl_ros/filters/extract_indices.cpp</span>
                       <span class="c">#src/pcl_ros/filters/statistical_outlier_removal.cpp</span>
                       <span class="c">#src/pcl_ros/filters/voxel_grid.cpp</span>
                      <span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>For more information on how/why this works, see <a class="footnote-reference brackets" href="#id3" id="id2">1</a>.</p>
<dl class="footnote brackets">
<dt class="label" id="id3"><span class="brackets">1</span><span class="fn-backref">(<a href="#id1">1</a>,<a href="#id2">2</a>)</span></dt>
<dd><p><a class="reference external" href="http://gamesfromwithin.com/physical-structure-and-c-part-2-build-times">http://gamesfromwithin.com/physical-structure-and-c-part-2-build-times</a></p>
</dd>
</dl>
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