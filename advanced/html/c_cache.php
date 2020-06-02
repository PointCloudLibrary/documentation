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
    
    <title>Using CCache to speed up compilation</title>
    
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
    <link rel="next" title="Using DistCC to speed up compilation" href="distcc.php" />
    <link rel="prev" title="Compiling PCL" href="index.php" />
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
            
  <div class="section" id="using-ccache-to-speed-up-compilation">
<span id="c-cache"></span><h1>Using CCache to speed up compilation</h1>
<p><a class="reference external" href="http://ccache.samba.org/">CCache</a> is nothing more than a cache for your
compiler. ccache is usually very easy to install. Here&#8217;s an example for Ubuntu
systems:</p>
<div class="highlight-python"><div class="highlight"><pre>sudo apt-get install ccache
</pre></div>
</div>
<p><tt class="docutils literal"><span class="pre">ccache</span></tt> will cache previous compilations, detect when the same compilation
is being done again, and reuse its cache instead of recompiling the source code
again. This can speed up your compilation by many orders of magnitude,
especially in those situations where your file timestamps change, and <tt class="docutils literal"><span class="pre">make</span></tt>
is triggering a recompile.</p>
<p>To enable ccache, simply add &#8216;/usr/lib/ccache&#8217; to the beginning of your PATH.
This directory contains symlinks to ccache, and ccache is smart enough to
look at the name of the calling executable to determine which real executable
to run. I.e. there is a symlink from &#8216;/usr/lib/ccache/g++&#8217; to just &#8216;ccache&#8217;,
but it actually runs the equivalent of &#8216;ccache g++&#8217;.</p>
</div>
<div class="section" id="using-colorgcc-to-colorize-output">
<h1>Using colorgcc to colorize output</h1>
<p><a href="#id1"><span class="problematic" id="id2">`colorgcc&lt;https://github.com/johannes/colorgcc&gt;`_</span></a> is a colorizer for the output
of GCC, and allows you to better interpret the compiler warnings/errors.</p>
<p>To enable both colorgcc and ccache, perform the following steps:</p>
<p>Install <tt class="docutils literal"><span class="pre">colorgcc</span></tt> on an Ubuntu system with</p>
<div class="highlight-python"><div class="highlight"><pre>sudo apt-get install colorgcc
</pre></div>
</div>
<p>To enable colorgcc, perform the following steps:</p>
<div class="highlight-cmake"><div class="highlight"><pre>cp /etc/colorgcc/colorgccrc $HOME/.colorgccrc
</pre></div>
</div>
<ul class="simple">
<li>edit the $HOME/.colorgccrc file, search for the following lines:</li>
</ul>
<div class="highlight-cmake"><div class="highlight"><pre>g++: /usr/bin/g++
gcc: /usr/bin/gcc
c++: /usr/bin/g++
cc:  /usr/bin/gcc
g77: /usr/bin/g77
f77: /usr/bin/g77
gcj: /usr/bin/gcj
</pre></div>
</div>
<p>and replace them with:</p>
<div class="highlight-cmake"><div class="highlight"><pre>g++: ccache /usr/bin/g++
gcc: ccache /usr/bin/gcc
c++: ccache /usr/bin/g++
cc:  ccache /usr/bin/gcc
g77: ccache /usr/bin/g77
f77: ccache /usr/bin/g77
gcj: ccache /usr/bin/gcj
</pre></div>
</div>
<ul class="simple">
<li>create a $HOME/bin or $HOME/sbin directory, and create the following softlinks in it</li>
</ul>
<div class="highlight-cmake"><div class="highlight"><pre>ln -s /usr/bin/colorgcc c++
ln -s /usr/bin/colorgcc cc
ln -s /usr/bin/colorgcc g++
ln -s /usr/bin/colorgcc gcc
</pre></div>
</div>
<p>make sure that $HOME/bin or $HOME/sbin is the first directory in your $PATH, e.g.:</p>
<div class="highlight-python"><div class="highlight"><pre>export PATH=$HOME/bin:$PATH
</pre></div>
</div>
<p>or:</p>
<div class="highlight-python"><div class="highlight"><pre>export PATH=$HOME/sbin:$PATH
</pre></div>
</div>
<p>depending on where you stored the <tt class="docutils literal"><span class="pre">colorgcc</span></tt> softlinks, so that when
cc/gcc/g++/c++ is invoked the freshly created softlinks get activated first and
not the global /usr/bin/{cc,gcc,g++,c++}.</p>
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