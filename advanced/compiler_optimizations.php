<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation - Point Cloud Library (PCL)</title>
</head>

<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8" />
    <title>Compiler optimizations</title>
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
            
  <div class="section" id="compiler-optimizations">
<span id="id1"></span><h1>Compiler optimizations</h1>
<p>Using excessive compiler optimizations can really hurt your compile-time
performance, and there’s a question whether you really need these optimizations
everytime you recompile to prototype something new, or whether you can live
with a less optimal binary for testing things. Obviously once your tests
succeed and you want to deploy your project, you can simply re-enable the
compiler optimizations. Here’s a few tests that we did a while back with
<a class="reference external" href="http://pcl.ros.org/">pcl_ros</a>:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="o">-</span><span class="n">j1</span><span class="p">,</span> <span class="n">RelWithDebInfo</span> <span class="o">+</span> <span class="n">O3</span> <span class="p">:</span> <span class="mi">3</span><span class="n">m20</span><span class="o">.</span><span class="mi">376</span><span class="n">s</span> <span class="o">-</span><span class="n">j1</span><span class="p">,</span> <span class="n">RelWithDebInfo</span> <span class="p">:</span> <span class="mi">2</span><span class="n">m48</span><span class="o">.</span><span class="mi">064</span><span class="n">s</span>
<span class="o">-</span><span class="n">j1</span><span class="p">,</span> <span class="n">Debug</span> <span class="p">:</span> <span class="mi">2</span><span class="n">m0</span><span class="o">.</span><span class="mi">452</span><span class="n">s</span>
<span class="o">-</span><span class="n">j2</span><span class="p">,</span> <span class="n">Debug</span> <span class="p">:</span> <span class="mi">1</span><span class="n">m8</span><span class="o">.</span><span class="mi">151</span><span class="n">s</span>
<span class="o">-</span><span class="n">j4</span><span class="p">,</span> <span class="n">Debug</span> <span class="p">:</span> <span class="mi">0</span><span class="n">m42</span><span class="o">.</span><span class="mi">846</span><span class="n">s</span>
</pre></div>
</div>
<p>In general, we got used to enable all compiler optimizations possible. In PCL
pre-0.4, this is how the CMakeLists.txt file looked like:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">add_definitions</span><span class="p">(</span><span class="o">-</span><span class="n">Wall</span> <span class="o">-</span><span class="n">O3</span> <span class="o">-</span><span class="n">DNDEBUG</span> <span class="o">-</span><span class="n">pipe</span> <span class="o">-</span><span class="n">ffast</span><span class="o">-</span><span class="n">math</span> <span class="o">-</span><span class="n">funroll</span><span class="o">-</span><span class="n">loops</span> <span class="o">-</span><span class="n">ftree</span><span class="o">-</span><span class="n">vectorize</span> <span class="o">-</span><span class="n">fomit</span><span class="o">-</span><span class="n">frame</span><span class="o">-</span><span class="n">pointer</span> <span class="o">-</span><span class="n">pipe</span> <span class="o">-</span><span class="n">mfpmath</span><span class="o">=</span><span class="n">sse</span> <span class="o">-</span><span class="n">mmmx</span> <span class="o">-</span><span class="n">msse</span> <span class="o">-</span><span class="n">mtune</span><span class="o">=</span><span class="n">core2</span> <span class="o">-</span><span class="n">march</span><span class="o">=</span><span class="n">core2</span> <span class="o">-</span><span class="n">msse2</span> <span class="o">-</span><span class="n">msse3</span> <span class="o">-</span><span class="n">mssse3</span> <span class="o">-</span><span class="n">msse4</span><span class="p">)</span>
<span class="c1">#add_definitions(-momit-leaf-frame-pointer -fomit-frame-pointer -floop-block -ftree-loop-distribution -ftree-loop-linear -floop-interchange -floop-strip-mine -fgcse-lm -fgcse-sm -fsched-spec-load)</span>
<span class="n">add_definitions</span> <span class="p">(</span><span class="o">-</span><span class="n">Wall</span> <span class="o">-</span><span class="n">O3</span> <span class="o">-</span><span class="n">Winvalid</span><span class="o">-</span><span class="n">pch</span> <span class="o">-</span><span class="n">pipe</span> <span class="o">-</span><span class="n">funroll</span><span class="o">-</span><span class="n">loops</span> <span class="o">-</span><span class="n">fno</span><span class="o">-</span><span class="n">strict</span><span class="o">-</span><span class="n">aliasing</span><span class="p">)</span>
</pre></div>
</div>
<p>Obviously, not all those flags were enabled by default, but we were definitely
playing around with them, and sometimes committing them to the repository,
which led to increase compilation times for some of the projects that needed to
precompile/use PCL.</p>
<p>In general there is no good rule of thumb here, but we decided to disable these
excessive optimizations by default, and rely on CMake’s <em>RelWithDebInfo</em> by
default. You should do the same too when you prototype.</p>
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