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
    
    <title>How to write a good tutorial</title>
    
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
    <link rel="prev" title="PCL C++ Programming Style Guide" href="pcl_style_guide.php" />
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
            
  <div class="section" id="how-to-write-a-good-tutorial">
<span id="how-to-write-a-tutorial"></span><h1>How to write a good tutorial</h1>
<p>No matter how many tutorials we create and upload at
www.pointclouds.org/documentation/tutorials, there are never going to be
enough. :) As our code base and user base are growing, so is the demand for
detailed explanations or step-by-step/how-to documentation increasing. This
short guide will help you understand how <strong>you</strong> can contribute documentation
and help improve the project.</p>
<p>The Point Cloud Library (PCL) documentation infrastructure has two distinct
parts:</p>
<p>1. <a class="reference external" href="http://docs.pointclouds.org/">API documentation</a> &#8211; we are using
<a class="reference external" href="http://www.doxygen.org/">Doxygen</a> to automatically generate the best
possible API documentation, directly from our source files;</p>
<p>2. <a class="reference external" href="http://www.pointclouds.org/documentation">Tutorials and HowTo documents</a>
&#8211; we are using <a class="reference external" href="http://docutils.sourceforge.net/rst.html">Restructured Text</a>
via <a class="reference external" href="http://sphinx.pocoo.org">Sphinx</a> to transform simple <strong>reST</strong> files into
beautiful HTML documents.</p>
<p>Both documentation sources are stored in our <a class="reference external" href="https://github.com/PointCloudLibrary/pcl">Source repository</a> and the web pages are generated
hourly by our server via <cite>crontab</cite> jobs.</p>
<p>In the next two sections we will address both of the above, and present a small
example for each. We&#8217;ll begin with the easiest of the two: adding a new
tutorial.</p>
</div>
<div class="section" id="creating-a-new-tutorial">
<h1>Creating a new tutorial</h1>
<p>As already mentioned, we make use of Sphinx to generate HTML files from reST
(restructured text) documents. If you want to add a new tutorial, we suggest
you read the following resources:</p>
<blockquote>
<div><ul class="simple">
<li><a class="reference external" href="http://sphinx.pocoo.org/rest.html">http://sphinx.pocoo.org/rest.html</a> - official Sphinx documentation</li>
<li><a class="reference external" href="http://docutils.sourceforge.net/rst.html">http://docutils.sourceforge.net/rst.html</a> - official RST documentation</li>
<li><a class="reference external" href="http://www.siafoo.net/help/reST">http://www.siafoo.net/help/reST</a> - has a nice tutorial/set of examples</li>
</ul>
</div></blockquote>
<p>Once you understand how reST works, look over our current set of tutorials for
examples at <a class="reference external" href="https://github.com/PointCloudLibrary/pcl/tree/master/doc/tutorials/content">https://github.com/PointCloudLibrary/pcl/tree/master/doc/tutorials/content</a>.</p>
<p>To add a new tutorial, simply create a new file, and send it to us together
with the images/videos that you want included in the tutorial. The best way to
do this is to login to <a class="reference external" href="https://github.com/PointCloudLibrary/pcl">https://github.com/PointCloudLibrary/pcl</a> and send it as
a pull request.</p>
</div>
<div class="section" id="improving-the-api-documentation">
<h1>Improving the API documentation</h1>
<p>Providing a good API documentation is not easy &#8211; as finding a balance between
the amount of information that you present for each function, versus keeping it
clean and simple is ermmm, a challenge in itself. Differently said, it&#8217;s hard
to know what sort of people will look at the API: hardcore developers or first
time users.</p>
<p>Our solution is to document the API as best as possible, but leave certain more
complex details such as application examples for the tutorials. However, while
this is a nice goal, it&#8217;s very improbable that our documentation is perfect.</p>
<p>To help us improve the API documentation, all that you need to do is simply
check out the source code of PCL (we recommend trunk if you&#8217;re going to start
editing the sources), like:</p>
<div class="highlight-python"><div class="highlight"><pre>git clone https://github.com/PointCloudLibrary/pcl
</pre></div>
</div>
<p>Then, edit the file containing the function/class that you want to improve the
documentation for, say <em>common/include/pcl/point_cloud.h</em>, and go to the
element that you want to improve. Let&#8217;s take <em>points</em> for example:</p>
<div class="highlight-python"><div class="highlight"><pre>/** \brief The point data. */
std::vector&lt;PointT, Eigen::aligned_allocator&lt;PointT&gt; &gt; points;
</pre></div>
</div>
<p>What you have to modify is the Doxygen-style comment starting with /** and
ending with */. See <a class="reference external" href="http://www.doxygen.org">http://www.doxygen.org</a> for more information.</p>
<p>To send us the modification, please send a pull request through Github.</p>
</div>
<div class="section" id="testing-the-modified-api-documentation">
<h1>Testing the modified API documentation</h1>
<p>If you want to test it locally on your machine, make sure you have Doxygen
installed, and go into the build system (here we assume that you followed the
source installation instructions from
<a class="reference external" href="http://www.pointclouds.org/downloads">http://www.pointclouds.org/downloads</a>) and run:</p>
<div class="highlight-python"><div class="highlight"><pre>make doc
</pre></div>
</div>
<p>This will create a set of html files containing the API documentation for PCL,
in <strong>build/html/</strong></p>
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