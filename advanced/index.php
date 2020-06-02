<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation - Point Cloud Library (PCL)</title>
</head>

<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8" />
    <title>Compiling PCL</title>
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
            
  <div class="toctree-wrapper compound">
</div>
<p>The following presents a set of advanced topics regarding PCL.</p>
<div class="section" id="compiling-pcl">
<h1>Compiling PCL</h1>
<p>PCL uses modern C++ template programming in order to achieve maximum generality
and reusability of its components. Due to intricate details of the current
generation of C++ compilers however, the usage of templated code introduces
additional compile-time delays. We present a series of tricks that, if used
appropriately, will save you a lot of headaches and will speed up the
compilation of your project.</p>
<ul>
<li><p><a class="reference internal" href="c_cache.php#c-cache"><span class="std std-ref">Using CCache to speed up compilation</span></a></p>
<p><cite>CCache</cite> is a compiler cache. It speeds up recompilation by caching previous
compilations and detecting when the same compilation is being done again.
Supported languages are C, C++, Objective-C and Objective-C++.</p>
<a class="reference internal image-reference" href="_images/ccache.png"><img alt="_images/ccache.png" src="_images/ccache.png" style="height: 75px;" /></a>
</li>
<li><p><a class="reference internal" href="distcc.php#distc"><span class="std std-ref">Using DistCC to speed up compilation</span></a></p>
<p><cite>distcc</cite> is a program to distribute builds of C, C++, Objective C or
Objective C++ code across several machines on a network. distcc should always
generate the same results as a local build, is simple to install and use, and
is usually much faster than a local compile.</p>
<a class="reference internal image-reference" href="_images/distcc.png"><img alt="_images/distcc.png" src="_images/distcc.png" style="height: 75px;" /></a>
</li>
<li><p><a class="reference internal" href="compiler_optimizations.php#compiler-optimizations"><span class="std std-ref">Compiler optimizations</span></a></p>
<p>Depending on what compiler optimizations you use, your code might behave
differently, both at compile time and at run time.</p>
<a class="reference internal image-reference" href="_images/optimize.png"><img alt="_images/optimize.png" src="_images/optimize.png" style="height: 75px;" /></a>
</li>
<li><p><a class="reference internal" href="single_compile_unit.php#single-compile-unit"><span class="std std-ref">Single compilation units</span></a></p>
<p>In certain cases, it’s better to concatenate source files into single
compilation units to speed up compiling.</p>
<a class="reference internal image-reference" href="_images/unitybuild.jpg"><img alt="_images/unitybuild.jpg" src="_images/unitybuild.jpg" style="height: 75px;" /></a>
</li>
</ul>
</div>
<div class="section" id="developing-pcl-code">
<h1>Developing PCL code</h1>
<p>To make our lives easier, and to be able to read and integrate code from each
other without causing ourselves headaches, we assembled a set of rules for PCL
development that everyone should follow:</p>
<div class="topic">
<p class="topic-title">Rules</p>
<ul class="simple">
<li><p>if you make important commits, please <strong>_add the commit log_</strong> or something similar <strong>_to
the changelist page_</strong>
(<a class="reference external" href="https://github.com/PointCloudLibrary/pcl/blob/master/CHANGES.md">https://github.com/PointCloudLibrary/pcl/blob/master/CHANGES.md</a>);</p></li>
<li><p>if you change anything in an existing algorithm, <strong>_make sure that there are
unit tests_</strong> for it and <strong>_make sure that they pass before you commit_</strong> the code;</p></li>
<li><p>if you add a new algorithm or method, please <strong>_document the code in a similar
manner to the existing PCL code_</strong> (or better!), and <strong>_add some minimal unit
tests_</strong> before you commit it;</p></li>
<li><p>method definitions go into (include/.h), templated implementations go into
(include/impl/.hpp), non-templated implementations go into (src/.cpp), and
unit tests go in (test/.cpp);</p></li>
<li><p>last but not least, please <strong>_respect the same naming and indentation
guidelines_</strong> as you see in the <a class="reference internal" href="pcl_style_guide.php#pcl-style-guide"><span class="std std-ref">PCL C++ Programming Style Guide</span></a>.</p></li>
</ul>
</div>
<ul>
<li><p><a class="reference internal" href="pcl_style_guide.php#pcl-style-guide"><span class="std std-ref">PCL C++ Programming Style Guide</span></a></p>
<p>Please follow the following naming and indentation rules when developing code for PCL.</p>
</li>
<li><p><a class="reference internal" href="exceptions_guide.php#exceptions-guide"><span class="std std-ref">Exceptions in PCL</span></a></p>
<p>Short documentation on how to add new, throw and handle exceptions in PCL.</p>
</li>
<li><p><a class="reference internal" href="pcl2.php#pcl2"><span class="std std-ref">PCL 2.x API consideration guide</span></a></p>
<p>An in-depth discussion about the PCL 2.x API can be found here.</p>
</li>
</ul>
</div>
<div class="section" id="committing-changes-to-the-git-master">
<h1>Committing changes to the git master</h1>
<p>In order to oversee the commit messages more easier and that the changelist looks homogenous please keep the following format:</p>
<p>“* &lt;fixed|bugfix|changed|new&gt; X in &#64;&lt;classname&gt;&#64; (#&lt;bug number&gt;)”</p>
</div>
<div class="section" id="improving-the-pcl-documentation">
<h1>Improving the PCL documentation</h1>
<ul>
<li><p><a class="reference internal" href="how_to_write_a_tutorial.php#how-to-write-a-tutorial"><span class="std std-ref">How to write a good tutorial</span></a></p>
<p>In case you want to contribute/help PCL by improving the existing
documentation and tutorials/examples, please read our short guide on how to
start.</p>
</li>
</ul>
</div>
<div class="section" id="how-to-build-a-minimal-example">
<h1>How to build a minimal example</h1>
<ul>
<li><p><a class="reference internal" href="minimal_example.php#minimal-example"><span class="std std-ref">How to build a minimal example</span></a></p>
<p>In case you need help to debug your code, please follow this guidelines to write a minimal example.</p>
</li>
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