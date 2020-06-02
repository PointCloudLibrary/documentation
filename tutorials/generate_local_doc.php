<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation - Point Cloud Library (PCL)</title>
</head>

<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8" />
    <title>Generate a local documentation for PCL &#8212; PCL 0.0 documentation</title>
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
            
  <div class="section" id="generate-a-local-documentation-for-pcl">
<span id="generate-local-doc"></span><h1>Generate a local documentation for PCL</h1>
<p>For practical reasons you might want to have a local documentation which corresponds to your
PCL version. In this tutorial you will learn how to generate it and how to set up Apache so that
the search bar works.</p>
<p>This tutorial was written for Ubuntu 12.04 and 14.04, feel free to edit it on GitHub to add your platform.</p>
<div class="section" id="dependencies">
<h2>Dependencies</h2>
<p>You need to install a few dependencies in order to be able to generate the documentation:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>$ sudo apt-get install doxygen graphviz sphinx3 python-pip
$ sudo pip install sphinxcontrib-doxylink
</pre></div>
</div>
</div>
<div class="section" id="generate-the-documentation">
<h2>Generate the documentation</h2>
<p>Go into the build folder of PCL where you’ve configured it (<a class="reference external" href="http://www.pointclouds.org/downloads/source.html">see tutorial</a>) and enter:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>$ make doc
</pre></div>
</div>
<p>Then you can open the documentation with your browser, for example:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>$ firefox doc/doxygen/html/index.html
</pre></div>
</div>
<p>The documentation has been generated in your PCL build directory but it is not installed; if you wish to install it just do:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>$ sudo make install
</pre></div>
</div>
<p>The default PCL <code class="docutils literal notranslate"><span class="pre">CMAKE_INSTALL_PREFIX</span></code> is <code class="docutils literal notranslate"><span class="pre">/usr/local</span></code>, this means the documentation will be located in <code class="docutils literal notranslate"><span class="pre">/usr/local/share/doc/pcl-1.7/html/index.html</span></code></p>
<div class="admonition note">
<p class="admonition-title">Note</p>
<p>You will quickly notice that the search bar doesn’t work! (searching opens “search.php” instead of searching)</p>
</div>
</div>
<div class="section" id="installing-and-configuring-apache">
<h2>Installing and configuring Apache</h2>
<p>Apache (<a class="reference external" href="https://en.wikipedia.org/wiki/Apache_HTTP_Server">The Apache HTTP Server</a>) is a web server application, in this section you will
learn how to configure Apache in order to be able to use the search feature within your offline documentation.</p>
<p>First you need to install Apache and php:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>$ sudo apt-get install apache2 php5 libapache2-mod-php5
</pre></div>
</div>
<p>Then you need to edit the default website location:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>$ sudo gedit /etc/apache2/sites-available/000-default.conf
</pre></div>
</div>
<p>Change <code class="docutils literal notranslate"><span class="pre">DocumentRoot</span></code> (default = <code class="docutils literal notranslate"><span class="pre">/var/www/html</span></code>) to <code class="docutils literal notranslate"><span class="pre">/usr/local/share/doc/pcl-1.7/html/</span></code> (or your local PCL doc build path)</p>
<p>After that change the Apache directory options:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>$ sudo gedit +153 /etc/apache2/apache2.conf
</pre></div>
</div>
<p>Replace the paragraph at line 153 with:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="o">&lt;</span><span class="n">Directory</span> <span class="o">/&gt;</span>
    <span class="c1">#Options FollowSymLinks</span>
    <span class="n">Options</span> <span class="n">Indexes</span> <span class="n">FollowSymLinks</span> <span class="n">Includes</span> <span class="n">ExecCGI</span>
    <span class="n">AllowOverride</span> <span class="n">All</span>
    <span class="n">Order</span> <span class="n">deny</span><span class="p">,</span><span class="n">allow</span>
    <span class="n">Allow</span> <span class="kn">from</span> <span class="nn">all</span>
<span class="o">&lt;/</span><span class="n">Directory</span><span class="o">&gt;</span>
</pre></div>
</div>
<p>Restart Apache and the search bar will now work if you open <code class="docutils literal notranslate"><span class="pre">localhost</span></code>:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>$ sudo /etc/init.d/apache2 restart
$ firefox localhost
</pre></div>
</div>
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