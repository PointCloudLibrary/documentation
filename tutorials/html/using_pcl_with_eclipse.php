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
    
    <title>Using PCL with Eclipse</title>
    
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
            
  <div class="section" id="using-pcl-with-eclipse">
<span id="id1"></span><h1>Using PCL with Eclipse</h1>
<p>This tutorial explains how to use Eclipse as a PCL editor</p>
</div>
<div class="section" id="prerequisites">
<h1>Prerequisites</h1>
<p>We assume you have downloaded, compiled and installed PCL trunk (see Downloads, experimental) on your machine.</p>
</div>
<div class="section" id="creating-the-eclipse-project-files">
<h1>Creating the eclipse project files</h1>
<p>Open a terminal window and do:</p>
<div class="highlight-python"><div class="highlight"><pre>$ cd /PATH/TO/MY/TRUNK/ROOT
$  cmake -G&quot;Eclipse CDT4 - Unix Makefiles&quot; .
</pre></div>
</div>
<p>You will see something similar to:</p>
<div class="highlight-python"><div class="highlight"><pre>-- The C compiler identification is GNU
-- The CXX compiler identification is GNU
-- Could not determine Eclipse version, assuming at least 3.6 (Helios). Adjust CMAKE_ECLIPSE_VERSION if this is wrong.
-- Check for working C compiler: /home/u0062536/bin/gcc
-- Check for working C compiler: /home/u0062536/bin/gcc -- works
-- Detecting C compiler ABI info
-- Detecting C compiler ABI info - done
-- Check for working CXX compiler: /home/u0062536/bin/c++
-- Check for working CXX compiler: /home/u0062536/bin/c++ -- works
-- Detecting CXX compiler ABI info
-- Detecting CXX compiler ABI info - done
-- -- GCC &gt; 4.3 found, enabling -Wabi
-- Using CPU native flags for SSE optimization:  -march=native
-- Performing Test HAVE_MM_MALLOC
-- Performing Test HAVE_MM_MALLOC - Success
-- Performing Test HAVE_POSIX_MEMALIGN
-- Performing Test HAVE_POSIX_MEMALIGN - Success
-- Performing Test HAVE_SSE4_2_EXTENSIONS
-- Performing Test HAVE_SSE4_2_EXTENSIONS - Success
-- Performing Test HAVE_SSE4_1_EXTENSIONS
-- Performing Test HAVE_SSE4_1_EXTENSIONS - Success
-- Performing Test HAVE_SSE3_EXTENSIONS
-- Performing Test HAVE_SSE3_EXTENSIONS - Success
-- Performing Test HAVE_SSE2_EXTENSIONS
-- Performing Test HAVE_SSE2_EXTENSIONS - Success
-- Performing Test HAVE_SSE_EXTENSIONS
-- Performing Test HAVE_SSE_EXTENSIONS - Success
-- Found SSE4.2 extensions, using flags:  -march=native -msse4.2 -mfpmath=sse
-- Try OpenMP C flag = [-fopenmp]
-- Performing Test OpenMP_FLAG_DETECTED
-- Performing Test OpenMP_FLAG_DETECTED - Success
-- Try OpenMP CXX flag = [-fopenmp]
-- Performing Test OpenMP_FLAG_DETECTED
-- Performing Test OpenMP_FLAG_DETECTED - Success
-- Found OpenMP: -fopenmp
-- Found OpenMP
-- Boost version: 1.46.1
-- The following subsystems will be built:
--   common
--   kdtree
--   octree
--   io
--   search
--   sample_consensus
--   filters
--   2d
--   features
--   keypoints
--   geometry
--   ml
--   segmentation
--   visualization
--   outofcore
--   stereo
--   surface
--   tracking
--   registration
--   people
--   recognition
--   global_tests
--   tools
-- The following subsystems will not be built:
--   examples: Code examples are disabled by default.
--   simulation: Disabled by default.
--   apps: Disabled by default.
-- Configuring done
-- Generating done
-- Build files have been written to: /data/git/pcl
</pre></div>
</div>
</div>
<div class="section" id="importing-into-eclipse">
<h1>Importing into Eclipse</h1>
<p>Now you launch your Eclipse editor and you select File-&gt;Import...
Out of the list you select General-&gt;Existing Projects into Workspace and then next.
At the top you select Select root directory to be the root of your pcl trunk installation and press Finish.</p>
</div>
<div class="section" id="setting-the-pcl-code-style-in-eclipse">
<h1>Setting the PCL code style in Eclipse</h1>
<p>You can find a PCL code style file for Eclipse in trunk/doc/advanced/content/files/.
In Eclipse go to Project-&gt;Properties, then select Code Style in the left field and Enable project specific settings, then Import and select where your trunk/doc/advanced/content/files/PCL_eclipse_profile.xml file is.</p>
</div>
<div class="section" id="where-to-get-more-information">
<h1>Where to get more information</h1>
<p>You can get more information here: <a class="reference external" href="http://www.vtk.org/Wiki/Eclipse_CDT4_Generator">http://www.vtk.org/Wiki/Eclipse_CDT4_Generator</a></p>
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