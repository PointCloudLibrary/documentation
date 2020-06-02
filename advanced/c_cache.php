<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation - Point Cloud Library (PCL)</title>
</head>

<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8" />
    <title>Using CCache to speed up compilation</title>
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
            
  <div class="section" id="using-ccache-to-speed-up-compilation">
<span id="c-cache"></span><h1>Using CCache to speed up compilation</h1>
<p><a class="reference external" href="http://ccache.samba.org/">CCache</a> is nothing more than a cache for your
compiler. ccache is usually very easy to install. Here’s an example for Ubuntu
systems:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">sudo</span> <span class="n">apt</span><span class="o">-</span><span class="n">get</span> <span class="n">install</span> <span class="n">ccache</span>
</pre></div>
</div>
<p><code class="docutils literal notranslate"><span class="pre">ccache</span></code> will cache previous compilations, detect when the same compilation
is being done again, and reuse its cache instead of recompiling the source code
again. This can speed up your compilation by many orders of magnitude,
especially in those situations where your file timestamps change, and <code class="docutils literal notranslate"><span class="pre">make</span></code>
is triggering a recompile.</p>
<p>To enable ccache, simply add ‘/usr/lib/ccache’ to the beginning of your PATH.
This directory contains symlinks to ccache, and ccache is smart enough to
look at the name of the calling executable to determine which real executable
to run. I.e. there is a symlink from ‘/usr/lib/ccache/g++’ to just ‘ccache’,
but it actually runs the equivalent of ‘ccache g++’.</p>
</div>
<div class="section" id="using-colorgcc-to-colorize-output">
<h1>Using colorgcc to colorize output</h1>
<p><a class="reference external" href="https://github.com/johannes/colorgcc">colorgcc</a> is a colorizer for the output
of GCC, and allows you to better interpret the compiler warnings/errors.</p>
<p>To enable both colorgcc and ccache, perform the following steps:</p>
<p>Install <code class="docutils literal notranslate"><span class="pre">colorgcc</span></code> on an Ubuntu system with</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">sudo</span> <span class="n">apt</span><span class="o">-</span><span class="n">get</span> <span class="n">install</span> <span class="n">colorgcc</span>
</pre></div>
</div>
<p>To enable colorgcc, perform the following steps:</p>
<div class="highlight-cmake notranslate"><div class="highlight"><pre><span></span>cp /etc/colorgcc/colorgccrc $HOME/.colorgccrc
</pre></div>
</div>
<ul class="simple">
<li><p>edit the $HOME/.colorgccrc file, search for the following lines:</p></li>
</ul>
<div class="highlight-cmake notranslate"><div class="highlight"><pre><span></span>g++: /usr/bin/g++
gcc: /usr/bin/gcc
c++: /usr/bin/g++
cc:  /usr/bin/gcc
g77: /usr/bin/g77
f77: /usr/bin/g77
gcj: /usr/bin/gcj
</pre></div>
</div>
<p>and replace them with:</p>
<div class="highlight-cmake notranslate"><div class="highlight"><pre><span></span>g++: ccache /usr/bin/g++
gcc: ccache /usr/bin/gcc
c++: ccache /usr/bin/g++
cc:  ccache /usr/bin/gcc
g77: ccache /usr/bin/g77
f77: ccache /usr/bin/g77
gcj: ccache /usr/bin/gcj
</pre></div>
</div>
<ul class="simple">
<li><p>create a $HOME/bin or $HOME/sbin directory, and create the following softlinks in it</p></li>
</ul>
<div class="highlight-cmake notranslate"><div class="highlight"><pre><span></span>ln -s /usr/bin/colorgcc c++
ln -s /usr/bin/colorgcc cc
ln -s /usr/bin/colorgcc g++
ln -s /usr/bin/colorgcc gcc
</pre></div>
</div>
<p>make sure that $HOME/bin or $HOME/sbin is the first directory in your $PATH, e.g.:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>export PATH=$HOME/bin:$PATH
</pre></div>
</div>
<p>or:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>export PATH=$HOME/sbin:$PATH
</pre></div>
</div>
<p>depending on where you stored the <code class="docutils literal notranslate"><span class="pre">colorgcc</span></code> softlinks, so that when
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