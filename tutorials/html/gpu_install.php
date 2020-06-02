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
    
    <title>Configuring your PC to use your Nvidia GPU with PCL</title>
    
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
            
  <div class="section" id="configuring-your-pc-to-use-your-nvidia-gpu-with-pcl">
<span id="gpu-install"></span><h1>Configuring your PC to use your Nvidia GPU with PCL</h1>
<p>In this tutorial we will learn how to check if your PC is
suitable for use with the GPU methods provided within PCL.
This tutorial has been tested on Ubuntu 11.04 and 12.04, let
us know on the user mailing list if you have tested this on other
distributions.</p>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<p>In order to run the code you&#8217;ll need a decent Nvidia GPU with Fermi or Kepler architecture you can check this by:</p>
<div class="highlight-python"><div class="highlight"><pre>$ lspci | grep nVidia
</pre></div>
</div>
<p>This should indicate which GPU you have on your system, if you don&#8217;t have an Nvidia GPU, we&#8217;re sorry, but you
won&#8217;t be able to use PCL GPU.
The output of this you can compare with <a class="reference external" href="http://www.nvidia.co.uk/object/cuda-parallel-computing-uk.html">this link</a>
on the Nvidia website, your card should mention compute capability of 2.x (Fermi) or 3.x (Kepler) or higher.
If you want to run with a GUI, you can also run:</p>
<div class="highlight-python"><div class="highlight"><pre>$ nvidia-settings
</pre></div>
</div>
<p>From either CLI or from your system settings menu. This should mention the same information.</p>
<p>First you need to test if your CPU is 32 or 64 bit, if it is 64-bit, it is preferred to work in this mode.
For this you can run:</p>
<div class="highlight-python"><div class="highlight"><pre>$ lscpu | grep op-mode
</pre></div>
</div>
<p>As a next step you will need a up to date version of the Cuda Toolkit. You can get this
<a class="reference external" href="http://developer.nvidia.com/cuda/cuda-downloads">here</a>, at the time of writing the
latest version was 4.2 and the beta release of version 5 was available as well.</p>
<p>First you will need to install the latest video drivers, download the correct one from the site
and run the install file, after this, download the toolkit and install it.
At the moment of writing this was version 295.41, please choose the most up to date one:</p>
<div class="highlight-python"><div class="highlight"><pre>$ wget http://developer.download.nvidia.com/compute/cuda/4_2/rel/drivers/devdriver_4.2_linux_64_295.41.run
</pre></div>
</div>
<p>Make the driver executable:</p>
<div class="highlight-python"><div class="highlight"><pre>$ chmod +x devdriver_4.2_linux_64_295.41.run
</pre></div>
</div>
<p>Run the driver:</p>
<div class="highlight-python"><div class="highlight"><pre>$ sudo ./devdriver_4.2_linux_64_295.41.run
</pre></div>
</div>
<p>You need to run this script without your X-server running. You can shut your X-server down as follows:
Go to a terminal by pressing Ctrl-Alt-F1 and typing:</p>
<div class="highlight-python"><div class="highlight"><pre>$ sudo service gdm stop
</pre></div>
</div>
<p>Once you have installed you GPU device driver you will also need to install the CUDA Toolkit:</p>
<div class="highlight-python"><div class="highlight"><pre>$ wget http://developer.download.nvidia.com/compute/cuda/4_2/rel/toolkit/cudatoolkit_4.2.9_linux_64_ubuntu11.04.run
$ chmod +x cudatoolkit_4.2.9_linux_64_ubuntu11.04.run
$ sudo ./cudatoolkit_4.2.9_linux_64_ubuntu11.04.run
</pre></div>
</div>
<p>You can get the SDK, but for PCL this is not needed, this provides you with general CUDA examples
and some scripts to test the performance of your CPU as well as your hardware specifications.</p>
<p>CUDA only compiles with gcc 4.4 and lower, so if your default installed gcc is 4.5 or higher you&#8217;ll need to install gcc 4.4:</p>
<blockquote>
<div>$ sudo apt-get install gcc-4.4</div></blockquote>
<p>Now you need to force your gcc to use this version, you can remove the older version, the other option is to create a symlink in your home folder and include that in the beginning of your path:</p>
<blockquote>
<div>$ cd
$ mkdir bin</div></blockquote>
<p>Add &#8216;export PATH=$HOME/bin:$PATH as the last line to your ~/.bashrc file.
Now create the symlinks in your bin folder:</p>
<blockquote>
<div>$ cd ~/bin
$ ln -s &lt;your_gcc_installation&gt; c++
$ ln -s &lt;your_gcc_installation&gt; cc
$ ln -s &lt;your_gcc_installation&gt; g++
$ ln -s &lt;your_gcc_installation&gt; gcc</div></blockquote>
<p>If you use colorgcc these links all need to point to /usr/bin/colorgcc.</p>
<p>Now you can get the latest git master (or another one) of PCL and configure your
installation to use the CUDA functions.</p>
<p>Go to your PCL root folder and do:</p>
<div class="highlight-python"><div class="highlight"><pre>$ mkdir build; cd build
$ ccmake ..
</pre></div>
</div>
<p>Press c to configure ccmake, press t to toggle to the advanced mode as a number of options
only appear in advanced mode. The latest CUDA algorithms are beeing kept in the GPU project, for
this the BUILD_GPU option needs to be on and the BUILD_gpu_&lt;X&gt; indicate the different
GPU subprojects.</p>
<a class="reference internal image-reference" href="_images/gpu_ccmake.png"><img alt="_images/gpu_ccmake.png" src="_images/gpu_ccmake.png" style="width: 400pt;" /></a>
<p>Press c again to configure for you options, press g to generate the makefiles and to exit. Now
the makefiles have been generated successfully and can be executed by doing:</p>
<div class="highlight-python"><div class="highlight"><pre>$ make
</pre></div>
</div>
<p>If you want to install your PCL installation for everybody to use:</p>
<div class="highlight-python"><div class="highlight"><pre>$ make install
</pre></div>
</div>
<p>Now your installation is finished!</p>
</div>
<div class="section" id="tested-hardware">
<h1>Tested Hardware</h1>
<p>Please report us the hardware you have tested the following methods with.</p>
<table border="1" class="docutils">
<colgroup>
<col width="21%" />
<col width="64%" />
<col width="15%" />
</colgroup>
<thead valign="bottom">
<tr class="row-odd"><th class="head">Method</th>
<th class="head">Hardware</th>
<th class="head">Reported FPS</th>
</tr>
</thead>
<tbody valign="top">
<tr class="row-even"><td>Kinfu</td>
<td>GTX680, Intel Xeon CPU E5620 &#64; 2.40Ghz x 8, 24Gb RAM</td>
<td>20-27</td>
</tr>
<tr class="row-odd"><td>&nbsp;</td>
<td>GTX480, Intel Xeon CPU W3550 &#64; 3.07GHz × 4, 7.8Gb RAM</td>
<td>40</td>
</tr>
<tr class="row-even"><td>&nbsp;</td>
<td>C2070, Intel Xeon CPU E5620 &#64; 2.40Ghz x 8, 24Gb RAM</td>
<td>29</td>
</tr>
<tr class="row-odd"><td>People Pose Detection</td>
<td>GTX680, Intel Xeon CPU E5620 &#64; 2.40Ghz x 8, 24Gb RAM</td>
<td>20-23</td>
</tr>
<tr class="row-even"><td>&nbsp;</td>
<td>C2070, Intel Xeon CPU E5620 &#64; 2.40Ghz x 8, 24Gb RAM</td>
<td>10-20</td>
</tr>
</tbody>
</table>
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