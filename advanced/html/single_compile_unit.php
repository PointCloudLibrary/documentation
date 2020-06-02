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
    
    <title>Single compilation units</title>
    
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
    <link rel="next" title="PCL C++ Programming Style Guide" href="pcl_style_guide.php" />
    <link rel="prev" title="Compiler optimizations" href="compiler_optimizations.php" />
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
            
  <div class="section" id="single-compilation-units">
<span id="single-compile-unit"></span><h1>Single compilation units</h1>
<p>Even before reading <a class="footnote-reference" href="#id3" id="id1">[1]</a>, we noticed a great speed up in compile time for all
PCL libraries if instead of compiling N object files and linking them together,
we compile only one, and include all the sources of the N files in this main
source. If you peek at an older version of PCL, you might notice things along
the lines of:</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre>1
2
3
4
5
6</pre></div></td><td class="code"><div class="highlight"><pre> // Include the implementations instead of compiling them separately to speed up compile time
 #include &quot;extract_indices.cpp&quot;
 #include &quot;passthrough.cpp&quot;
 #include &quot;project_inliers.cpp&quot;
 #include &quot;statistical_outlier_removal.cpp&quot;
 #include &quot;voxel_grid.cpp&quot;
</pre></div>
</td></tr></table></div>
<p>and in CMakeLists.txt:</p>
<div class="highlight-cmake"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre>1
2
3
4
5
6
7
8
9</pre></div></td><td class="code"><div class="highlight"><pre> <span class="nb">rosbuild_add_library</span> <span class="p">(</span><span class="s">pcl_ros_filters</span>
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
<p>For more information on how/why this works, see <a class="footnote-reference" href="#id3" id="id2">[1]</a>.</p>
<table class="docutils footnote" frame="void" id="id3" rules="none">
<colgroup><col class="label" /><col /></colgroup>
<tbody valign="top">
<tr><td class="label">[1]</td><td><em>(<a class="fn-backref" href="#id1">1</a>, <a class="fn-backref" href="#id2">2</a>)</em> <a class="reference external" href="http://gamesfromwithin.com/physical-structure-and-c-part-2-build-times">http://gamesfromwithin.com/physical-structure-and-c-part-2-build-times</a></td></tr>
</tbody>
</table>
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