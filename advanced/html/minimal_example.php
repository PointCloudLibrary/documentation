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
    
    <title>How to build a minimal example</title>
    
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
            
  <div class="section" id="how-to-build-a-minimal-example">
<span id="minimal-example"></span><h1>How to build a minimal example</h1>
<p>First of all make a backup of your current state or start a new project for the
minimal example. Than there are basically two ways: strip down your program or
start from scratch.</p>
<div class="section" id="method-1-strip-down-your-program">
<h2>Method 1: Strip down your program</h2>
<p>This method has the advantage that you start with your actual problem and you
can test all the time if you are on the right track. First make sure that the
program actually compiles without the problematic code by commenting it. Then
start removing unneeded code until the bare minimum and make sure that it&#8217;s
still showing the error by compiling it with and without the problematic line
(make sure it still emits the same error message).</p>
</div>
<div class="section" id="method-2-start-from-scratch">
<h2>Method 2: Start from scratch</h2>
<p>If your program is to big to strip it down, it&#8217;s maybe easier to start from
scratch by building a small project that only includes the problematic code.
Again make sure that it actually compiles without the erroneous code and emits
the same error with it.</p>
</div>
</div>
<div class="section" id="how-to-deal-with-input-data-e-g-point-clouds">
<h1>How to deal with input data (e.g. point clouds)</h1>
<p>If you fear that your problem is connected to the input data (either if you
have a problem with pcl/io or the error depends on the input data) you should
include the input with your minimal example. If the file is to big and a
stripped down version doesn&#8217;t work, you should upload it somewhere and only
provide a link to the data. If you can&#8217;t include the data or don&#8217;t know a way
to provide it, add a remark to your mail and we will contact you to find a
solution.</p>
<p>If the input data is not so important it is best to generate fake data:</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre>1
2</pre></div></td><td class="code"><div class="highlight"><pre><span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointCloudXYZ</span><span class="o">&gt;</span> <span class="n">cloud</span><span class="p">;</span>
<span class="n">cloud</span><span class="p">.</span><span class="n">insert</span> <span class="p">(</span><span class="n">cloud</span><span class="p">.</span><span class="n">end</span> <span class="p">(),</span> <span class="n">PointXYZ</span> <span class="p">(</span><span class="mi">1</span><span class="p">,</span> <span class="mi">1</span><span class="p">,</span> <span class="mi">1</span><span class="p">));</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="i-m-linking-against-other-libraries-what-to-do">
<h1>I&#8217;m linking against other libraries, what to do?</h1>
<p>Normally other libraries should not interfere, so try to build a minimal
example using PCL (and it&#8217;s dependencies) first. If your problems is gone
without the other library please make sure that it&#8217;s not actually a problem
with one of the other libraries and add a comment in your minimal example.</p>
</div>
<div class="section" id="final-make">
<h1>Final Make</h1>
<p>Please put only one error into the minimal example as well as include all
necessary files to build it.</p>
</div>
<div class="section" id="references">
<h1>References</h1>
<ul class="simple">
<li><a class="reference external" href="http://www.minimalbeispiel.de/mini-en.html">Latex minimal example</a></li>
<li><a class="reference external" href="http://www.chiark.greenend.org.uk/~sgtatham/bugs.html">How to Report Bugs Effectively</a></li>
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