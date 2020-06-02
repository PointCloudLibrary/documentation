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
    
    <title>Installing on Mac OS X using Homebrew</title>
    
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
            
  <div class="section" id="installing-on-mac-os-x-using-homebrew">
<span id="installing-homebrew"></span><h1>Installing on Mac OS X using Homebrew</h1>
<p>This tutorial explains how to install the Point Cloud Library on Mac OS
X using Homebrew.</p>
<img alt="Mac OS X logo" class="align-right" src="_images/macosx_logo.png" />
</div>
<div class="section" id="prerequisites">
<span id="homebrew-preqs"></span><h1>Prerequisites</h1>
<p>You will need to have Homebrew installed. If you do not already have a Homebrew installation, see the
<a class="reference external" href="http://brew.sh/">Homebrew homepage</a> for installation instructions.</p>
</div>
<div class="section" id="using-the-formula">
<span id="homebrew-all"></span><h1>Using the formula</h1>
<p>The PCL formula is not in Homebrew official repositories yet, but it will be after 1.7.2.
For now it resides at <a class="reference external" href="https://github.com/fran6co/homebrew-cv">Fran6co&#8217;s repository</a> and can be tapped from there.
This will automatically install all necessary dependencies and provides options for controlling
which parts of PCL are installed.</p>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p>To prepare it, follow these steps:</p>
<ol class="last arabic simple">
<li>Install Homebrew. See the Homebrew website for instructions.</li>
<li>Execute <tt class="docutils literal"><span class="pre">brew</span> <span class="pre">update</span></tt>.</li>
<li>Execute <tt class="docutils literal"><span class="pre">brew</span> <span class="pre">tap</span> <span class="pre">homebrew/versions</span></tt>.</li>
<li>Execute <tt class="docutils literal"><span class="pre">brew</span> <span class="pre">tap</span> <span class="pre">homebrew/science</span></tt>.</li>
<li>Execute <tt class="docutils literal"><span class="pre">brew</span> <span class="pre">tap</span> <span class="pre">fran6co/cv</span></tt>.</li>
</ol>
</div>
<p>To install the latest version using the formula, execute the following command:</p>
<div class="highlight-python"><div class="highlight"><pre>$ brew install pcl --HEAD
</pre></div>
</div>
<p>You can specify options to control which parts of PCL are installed. For
example, to build just the libraries without extra dependencies, execute the following command:</p>
<div class="highlight-python"><div class="highlight"><pre>$ brew install pcl --HEAD --without-apps --without-tools --without-vtk --without-qvtk --without-qt
</pre></div>
</div>
<p>For a full list of the available options, see the formula&#8217;s help:</p>
<div class="highlight-python"><div class="highlight"><pre>$ brew options pcl
</pre></div>
</div>
<p>Once PCL is installed, you may wish to periodically upgrade it. Update
Homebrew and, if a PCL update is available, upgrade:</p>
<div class="highlight-python"><div class="highlight"><pre>$ brew update
$ brew upgrade pcl
</pre></div>
</div>
<div class="section" id="using-pcl">
<h2>Using PCL</h2>
<p>Now that PCL in installed, you can start using the library in your own
projects by following the <a class="reference internal" href="using_pcl_pcl_config.php#using-pcl-pcl-config"><em>Using PCL in your own project</em></a> tutorial.</p>
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