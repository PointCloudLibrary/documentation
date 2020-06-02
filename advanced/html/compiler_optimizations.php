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
    
    <title>Compiler optimizations</title>
    
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
    <link rel="next" title="Single compilation units" href="single_compile_unit.php" />
    <link rel="prev" title="Using DistCC to speed up compilation" href="distcc.php" />
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
            
  <div class="section" id="compiler-optimizations">
<span id="id1"></span><h1>Compiler optimizations</h1>
<p>Using excessive compiler optimizations can really hurt your compile-time
performance, and there&#8217;s a question whether you really need these optimizations
everytime you recompile to prototype something new, or whether you can live
with a less optimal binary for testing things. Obviously once your tests
succeed and you want to deploy your project, you can simply re-enable the
compiler optimizations. Here&#8217;s a few tests that we did a while back with
<a class="reference external" href="http://pcl.ros.org/">pcl_ros</a>:</p>
<div class="highlight-python"><div class="highlight"><pre>-j1, RelWithDebInfo + O3 : 3m20.376s -j1, RelWithDebInfo : 2m48.064s
-j1, Debug : 2m0.452s
-j2, Debug : 1m8.151s
-j4, Debug : 0m42.846s
</pre></div>
</div>
<p>In general, we got used to enable all compiler optimizations possible. In PCL
pre-0.4, this is how the CMakeLists.txt file looked like:</p>
<div class="highlight-python"><div class="highlight"><pre>add_definitions(-Wall -O3 -DNDEBUG -pipe -ffast-math -funroll-loops -ftree-vectorize -fomit-frame-pointer -pipe -mfpmath=sse -mmmx -msse -mtune=core2 -march=core2 -msse2 -msse3 -mssse3 -msse4)
#add_definitions(-momit-leaf-frame-pointer -fomit-frame-pointer -floop-block -ftree-loop-distribution -ftree-loop-linear -floop-interchange -floop-strip-mine -fgcse-lm -fgcse-sm -fsched-spec-load)
add_definitions (-Wall -O3 -Winvalid-pch -pipe -funroll-loops -fno-strict-aliasing)
</pre></div>
</div>
<p>Obviously, not all those flags were enabled by default, but we were definitely
playing around with them, and sometimes committing them to the repository,
which led to increase compilation times for some of the projects that needed to
precompile/use PCL.</p>
<p>In general there is no good rule of thumb here, but we decided to disable these
excessive optimizations by default, and rely on CMake&#8217;s <em>RelWithDebInfo</em> by
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