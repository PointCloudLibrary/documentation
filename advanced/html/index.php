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
    
    <title>Compiling PCL</title>
    
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
    <link rel="top" title="None" href="#" />
    <link rel="next" title="Using CCache to speed up compilation" href="c_cache.php" />
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
            
  <div class="toctree-wrapper compound">
<ul class="simple">
</ul>
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
<li><p class="first"><a class="reference internal" href="c_cache.php#c-cache"><em>Using CCache to speed up compilation</em></a></p>
<p><cite>CCache</cite> is a compiler cache. It speeds up recompilation by caching previous
compilations and detecting when the same compilation is being done again.
Supported languages are C, C++, Objective-C and Objective-C++.</p>
<a class="reference internal image-reference" href="_images/ccache.png"><img alt="_images/ccache.png" src="_images/ccache.png" style="height: 75px;" /></a>
</li>
<li><p class="first"><a class="reference internal" href="distcc.php#distc"><em>Using DistCC to speed up compilation</em></a></p>
<p><cite>distcc</cite> is a program to distribute builds of C, C++, Objective C or
Objective C++ code across several machines on a network. distcc should always
generate the same results as a local build, is simple to install and use, and
is usually much faster than a local compile.</p>
<a class="reference internal image-reference" href="_images/distcc.png"><img alt="_images/distcc.png" src="_images/distcc.png" style="height: 75px;" /></a>
</li>
<li><p class="first"><a class="reference internal" href="compiler_optimizations.php#compiler-optimizations"><em>Compiler optimizations</em></a></p>
<p>Depending on what compiler optimizations you use, your code might behave
differently, both at compile time and at run time.</p>
<a class="reference internal image-reference" href="_images/optimize.png"><img alt="_images/optimize.png" src="_images/optimize.png" style="height: 75px;" /></a>
</li>
<li><p class="first"><a class="reference internal" href="single_compile_unit.php#single-compile-unit"><em>Single compilation units</em></a></p>
<p>In certain cases, it&#8217;s better to concatenate source files into single
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
<p class="topic-title first">Rules</p>
<ul class="simple">
<li>if you make important commits, please <strong>_add the commit log_</strong> or something similar <strong>_to
the changelist page_</strong>
(<a class="reference external" href="https://github.com/PointCloudLibrary/pcl/blob/master/CHANGES.md">https://github.com/PointCloudLibrary/pcl/blob/master/CHANGES.md</a>);</li>
<li>if you change anything in an existing algorithm, <strong>_make sure that there are
unit tests_</strong> for it and <strong>_make sure that they pass before you commit_</strong> the code;</li>
<li>if you add a new algorithm or method, please <strong>_document the code in a similar
manner to the existing PCL code_</strong> (or better!), and <strong>_add some minimal unit
tests_</strong> before you commit it;</li>
<li>method definitions go into (include/.h), templated implementations go into
(include/impl/.hpp), non-templated implementations go into (src/.cpp), and
unit tests go in (test/.cpp);</li>
<li>last but not least, please <strong>_respect the same naming and indentation
guidelines_</strong> as you see in the <a class="reference internal" href="pcl_style_guide.php#pcl-style-guide"><em>PCL C++ Programming Style Guide</em></a>.</li>
</ul>
</div>
<ul>
<li><p class="first"><a class="reference internal" href="pcl_style_guide.php#pcl-style-guide"><em>PCL C++ Programming Style Guide</em></a></p>
<p>Please follow the following naming and indentation rules when developing code for PCL.</p>
</li>
<li><p class="first"><a class="reference internal" href="exceptions_guide.php#exceptions-guide"><em>Exceptions in PCL</em></a></p>
<p>Short documentation on how to add new, throw and handle exceptions in PCL.</p>
</li>
<li><p class="first"><a class="reference internal" href="pcl2.php#pcl2"><em>PCL 2.x API consideration guide</em></a></p>
<p>An in-depth discussion about the PCL 2.x API can be found here.</p>
</li>
</ul>
</div>
<div class="section" id="commiting-changes-to-the-git-master">
<h1>Commiting changes to the git master</h1>
<p>In order to oversee the commit messages more easier and that the changelist looks homogenous please keep the following format:</p>
<p>&#8220;* &lt;fixed|bugfix|changed|new&gt; X in &#64;&lt;classname&gt;&#64; (#&lt;bug number&gt;)&#8221;</p>
</div>
<div class="section" id="improving-the-pcl-documentation">
<h1>Improving the PCL documentation</h1>
<ul>
<li><p class="first"><a class="reference internal" href="how_to_write_a_tutorial.php#how-to-write-a-tutorial"><em>How to write a good tutorial</em></a></p>
<p>In case you want to contribute/help PCL by improving the existing
documentation and tutorials/examples, please read our short guide on how to
start.</p>
</li>
</ul>
</div>
<div class="section" id="contents">
<h1>Contents</h1>
<div class="toctree-wrapper compound">
<ul>
<li class="toctree-l1"><a class="reference internal" href="c_cache.php">Using CCache to speed up compilation</a></li>
<li class="toctree-l1"><a class="reference internal" href="c_cache.php#using-colorgcc-to-colorize-output">Using colorgcc to colorize output</a></li>
<li class="toctree-l1"><a class="reference internal" href="distcc.php">Using DistCC to speed up compilation</a></li>
<li class="toctree-l1"><a class="reference internal" href="compiler_optimizations.php">Compiler optimizations</a></li>
<li class="toctree-l1"><a class="reference internal" href="single_compile_unit.php">Single compilation units</a></li>
<li class="toctree-l1"><a class="reference internal" href="pcl_style_guide.php">PCL C++ Programming Style Guide</a><ul>
<li class="toctree-l2"><a class="reference internal" href="pcl_style_guide.php#naming">1. Naming</a><ul>
<li class="toctree-l3"><a class="reference internal" href="pcl_style_guide.php#files">1.1. Files</a></li>
<li class="toctree-l3"><a class="reference internal" href="pcl_style_guide.php#directories">1.2. Directories</a></li>
<li class="toctree-l3"><a class="reference internal" href="pcl_style_guide.php#includes">1.3. Includes</a></li>
<li class="toctree-l3"><a class="reference internal" href="pcl_style_guide.php#defines-macros">1.4. Defines &amp; Macros</a></li>
<li class="toctree-l3"><a class="reference internal" href="pcl_style_guide.php#namespaces">1.5. Namespaces</a></li>
<li class="toctree-l3"><a class="reference internal" href="pcl_style_guide.php#classes-structs">1.6. Classes / Structs</a></li>
<li class="toctree-l3"><a class="reference internal" href="pcl_style_guide.php#functions-methods">1.7. Functions / Methods</a></li>
<li class="toctree-l3"><a class="reference internal" href="pcl_style_guide.php#variables">1.8. Variables</a><ul>
<li class="toctree-l4"><a class="reference internal" href="pcl_style_guide.php#iterators">1.8.1. Iterators</a></li>
<li class="toctree-l4"><a class="reference internal" href="pcl_style_guide.php#constants">1.8.2. Constants</a></li>
<li class="toctree-l4"><a class="reference internal" href="pcl_style_guide.php#member-variables">1.8.3. Member variables</a></li>
</ul>
</li>
<li class="toctree-l3"><a class="reference internal" href="pcl_style_guide.php#return-statements">1.9. Return statements</a></li>
</ul>
</li>
<li class="toctree-l2"><a class="reference internal" href="pcl_style_guide.php#indentation-and-formatting">2. Indentation and Formatting</a><ul>
<li class="toctree-l3"><a class="reference internal" href="pcl_style_guide.php#id1">2.1. Namespaces</a></li>
<li class="toctree-l3"><a class="reference internal" href="pcl_style_guide.php#classes">2.2. Classes</a></li>
<li class="toctree-l3"><a class="reference internal" href="pcl_style_guide.php#id2">2.3. Functions / Methods</a></li>
<li class="toctree-l3"><a class="reference internal" href="pcl_style_guide.php#braces">2.4. Braces</a></li>
<li class="toctree-l3"><a class="reference internal" href="pcl_style_guide.php#spacing">2.5. Spacing</a></li>
<li class="toctree-l3"><a class="reference internal" href="pcl_style_guide.php#automatic-code-formatting">2.6. Automatic code formatting</a><ul>
<li class="toctree-l4"><a class="reference internal" href="pcl_style_guide.php#emacs">2.6.1. Emacs</a></li>
<li class="toctree-l4"><a class="reference internal" href="pcl_style_guide.php#uncrustify">2.6.2. Uncrustify</a></li>
<li class="toctree-l4"><a class="reference internal" href="pcl_style_guide.php#eclipse">2.6.3 Eclipse</a></li>
</ul>
</li>
</ul>
</li>
<li class="toctree-l2"><a class="reference internal" href="pcl_style_guide.php#structuring">3. Structuring</a><ul>
<li class="toctree-l3"><a class="reference internal" href="pcl_style_guide.php#classes-and-api">3.1. Classes and API</a></li>
<li class="toctree-l3"><a class="reference internal" href="pcl_style_guide.php#passing-arguments">3.2. Passing arguments</a></li>
</ul>
</li>
</ul>
</li>
<li class="toctree-l1"><a class="reference internal" href="how_to_write_a_tutorial.php">How to write a good tutorial</a></li>
<li class="toctree-l1"><a class="reference internal" href="how_to_write_a_tutorial.php#creating-a-new-tutorial">Creating a new tutorial</a></li>
<li class="toctree-l1"><a class="reference internal" href="how_to_write_a_tutorial.php#improving-the-api-documentation">Improving the API documentation</a></li>
<li class="toctree-l1"><a class="reference internal" href="how_to_write_a_tutorial.php#testing-the-modified-api-documentation">Testing the modified API documentation</a></li>
</ul>
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