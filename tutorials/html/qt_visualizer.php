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
    
    <title>Create a PCL visualizer in Qt with cmake</title>
    
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
            
  <div class="section" id="create-a-pcl-visualizer-in-qt-with-cmake">
<span id="qt-visualizer"></span><h1><a class="toc-backref" href="#id1">Create a PCL visualizer in Qt with cmake</a></h1>
<p>In this tutorial we will learn how to create a PCL + Qt project, we will use Cmake rather than Qmake.The program we are going to
write is a simple PCL visualizer which allow to change a randomly generated point cloud color.</p>
<div class="line-block">
<div class="line">The tutorial was tested on Linux Ubuntu 12.04 and 14.04. It also seems to be working fine on Windows 8.1 x64.</div>
<div class="line">Feel free to push modifications into the git repo to make this code/tutorial compatible with your platform !</div>
</div>
<div class="contents topic" id="contents">
<p class="topic-title first">Contents</p>
<ul class="simple">
<li><a class="reference internal" href="#create-a-pcl-visualizer-in-qt-with-cmake" id="id1">Create a PCL visualizer in Qt with cmake</a><ul>
<li><a class="reference internal" href="#the-project" id="id2">The project</a></li>
<li><a class="reference internal" href="#qt-configuration" id="id3">Qt configuration</a></li>
<li><a class="reference internal" href="#user-interface-ui" id="id4">User interface (UI)</a></li>
<li><a class="reference internal" href="#the-code" id="id5">The code</a><ul>
<li><a class="reference internal" href="#main-cpp" id="id6">main.cpp</a></li>
<li><a class="reference internal" href="#pclviewer-h" id="id7">pclviewer.h</a></li>
<li><a class="reference internal" href="#pclviewer-cpp" id="id8">pclviewer.cpp</a></li>
</ul>
</li>
<li><a class="reference internal" href="#compiling-and-running" id="id9">Compiling and running</a></li>
<li><a class="reference internal" href="#more-on-qt-and-pcl" id="id10">More on Qt and PCL</a></li>
</ul>
</li>
</ul>
</div>
<div class="section" id="the-project">
<h2><a class="toc-backref" href="#id2">The project</a></h2>
<p>For this project Qt is of course mandatory, make sure it is installed and PCL deals with it.
<a class="reference external" href="http://qt-project.org/doc/qt-4.8/qmake-manual.html">qmake</a> is a tool that helps simplify the build process for development project across different platforms,
we will use <a class="reference external" href="https://en.wikipedia.org/wiki/CMake">cmake</a> instead because most projects in PCL uses cmake and it is simpler in my opinion.</p>
<p>This is how I organized the project: the build folder contains all built files and the src folder holds all sources files</p>
<div class="highlight-python"><div class="highlight"><pre>.
├── build
└── src
    ├── CMakeLists.txt
    ├── main.cpp
    ├── pclviewer.cpp
    ├── pclviewer.h
    ├── pclviewer.ui
    ├── pcl_visualizer.pro
    └── pcl_visualizer.pro.user
</pre></div>
</div>
<p>If you want to change this layout you will have to do minor modifications in the code, especially line 2 of <tt class="docutils literal"><span class="pre">pclviewer.cpp</span></tt>
Create the folder tree and download the sources files from <a class="reference external" href="https://github.com/PointCloudLibrary/pcl/tree/master/doc/tutorials/content/sources/qt_visualiser">github</a>.</p>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">File paths should not contain any special caracter or the compilation might fail with a <tt class="docutils literal"><span class="pre">moc:</span> <span class="pre">Cannot</span> <span class="pre">open</span> <span class="pre">options</span> <span class="pre">file</span> <span class="pre">specified</span> <span class="pre">with</span> <span class="pre">&#64;</span></tt> error message.</p>
</div>
</div>
<div class="section" id="qt-configuration">
<h2><a class="toc-backref" href="#id3">Qt configuration</a></h2>
<dl class="docutils">
<dt>First we will take a look at how Qt is configured to build this project. Simply open <tt class="docutils literal"><span class="pre">pcl_visualizer.pro</span></tt> with Qt (or double click on the file)</dt>
<dd>and go to the <strong>Projects</strong> tab</dd>
</dl>
<a class="reference internal image-reference" href="_images/qt_config.png"><img alt="_images/qt_config.png" src="_images/qt_config.png" style="height: 757px;" /></a>
<p>In this example note that I deleted the <strong>Debug</strong> configuration and only kept the <strong>Release</strong> config.
Use relative paths like this is better than absolute paths; this project should work wherever it has been put.</p>
<p>We specify in the general section that we want to build in the folder <tt class="docutils literal"><span class="pre">../build</span></tt> (this is a relative path from the <tt class="docutils literal"><span class="pre">.pro</span></tt> file).</p>
<p>The first step of the building is to call <tt class="docutils literal"><span class="pre">cmake</span></tt> (from the <tt class="docutils literal"><span class="pre">build</span></tt> folder) with argument <tt class="docutils literal"><span class="pre">../src</span></tt>; this is gonna create all files in the
<tt class="docutils literal"><span class="pre">build</span></tt> folder without modifying anything in the <tt class="docutils literal"><span class="pre">src</span></tt> foler; thus keeping it clean.</p>
<p>Then we just have to compile our program; the argument <tt class="docutils literal"><span class="pre">-j2</span></tt> allow to specify how many thread of your CPU you want to use for compilation. The more thread you use
the faster the compilation will be (especially on big projects); but if you take all threads from the CPU your OS will likely be unresponsive during
the compilation process.
See <a class="reference external" href="http://www.pointclouds.org/documentation/advanced/compiler_optimizations.php#compiler-optimizations">compiler optimizations</a> for more information.</p>
<p>If you don&#8217;t want to use Qt Creator but Eclipse instead; see <a class="reference external" href="http://www.pointclouds.org/documentation/tutorials/using_pcl_with_eclipse.php#using-pcl-with-eclipse">using PCL with Eclipse</a>.</p>
</div>
<div class="section" id="user-interface-ui">
<h2><a class="toc-backref" href="#id4">User interface (UI)</a></h2>
<p>The point of using Qt for your projects is that you can easily build cross-platform UIs. The UI is held in the <tt class="docutils literal"><span class="pre">.ui</span></tt> file
You can open it with a text editor or with Qt Creator, in this example the UI is very simple and it consists of :</p>
<blockquote>
<div><ul class="simple">
<li><a class="reference external" href="http://qt-project.org/doc/qt-4.8/qmainwindow.html">QMainWindow</a>, QWidget: the window (frame) of your application</li>
<li>qvtkWidget: The VTK widget which holds the PCLVisualizer</li>
<li><a class="reference external" href="http://qt-project.org/doc/qt-4.8/qlabel.html">QLabel</a>: Display text on the user interface</li>
<li><a class="reference external" href="http://qt-project.org/doc/qt-4.8/qslider.html">QSlider</a>: A slider to choose a value (here; an integer value)</li>
<li><a class="reference external" href="http://qt-project.org/doc/qt-4.8/qlcdnumber.html">QLCDNumber</a>: A digital display, 8 segment styled</li>
</ul>
</div></blockquote>
<a class="reference internal image-reference" href="_images/ui.png"><img alt="_images/ui.png" src="_images/ui.png" style="height: 518px;" /></a>
<p>If you click on Edit <a class="reference external" href="http://qt-project.org/doc/qt-4.8/signalsandslots.html">Signals/Slots</a> at the top of the Qt window if you will see the relationships
between some of the UI objects. In our example the sliderMoved(int) signal is connected to the display(int) slot; this means that everytime we move the slider
the digital display is updated accordingly to the slider value.</p>
</div>
<div class="section" id="the-code">
<h2><a class="toc-backref" href="#id5">The code</a></h2>
<p>Now, let&#8217;s break down the code piece by piece.</p>
<div class="section" id="main-cpp">
<h3><a class="toc-backref" href="#id6">main.cpp</a></h3>
<div class="highlight-cpp"><div class="highlight"><pre><span class="cp">#include &quot;pclviewer.h&quot;</span>
<span class="cp">#include &lt;QApplication&gt;</span>
<span class="cp">#include &lt;QMainWindow&gt;</span>

<span class="kt">int</span> <span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span> <span class="o">*</span><span class="n">argv</span><span class="p">[])</span>
<span class="p">{</span>
  <span class="n">QApplication</span> <span class="n">a</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">);</span>
  <span class="n">PCLViewer</span> <span class="n">w</span><span class="p">;</span>
  <span class="n">w</span><span class="p">.</span><span class="n">show</span> <span class="p">();</span>

  <span class="k">return</span> <span class="n">a</span><span class="p">.</span><span class="n">exec</span> <span class="p">();</span>
<span class="p">}</span>
</pre></div>
</div>
<div class="line-block">
<div class="line">Here we include the headers for the class PCLViewer and the headers for QApplication and QMainWindow.</div>
<div class="line">Then the main functions consists of instanciating a QApplication <cite>a</cite> which manages the GUI application&#8217;s control flow and main settings.</div>
<div class="line">A <tt class="docutils literal"><span class="pre">PCLViewer</span></tt> object called <cite>w</cite> is instanciated and it&#8217;s method <tt class="docutils literal"><span class="pre">show()</span></tt> is called.</div>
<div class="line">Finally we return the state of our program exit through the QApplication <cite>a</cite>.</div>
</div>
</div>
<div class="section" id="pclviewer-h">
<h3><a class="toc-backref" href="#id7">pclviewer.h</a></h3>
<div class="highlight-cpp"><div class="highlight"><pre><span class="cp">#ifndef PCLVIEWER_H</span>
<span class="cp">#define PCLVIEWER_H</span>

<span class="cp">#include &lt;iostream&gt;</span>

<span class="c1">// Qt</span>
<span class="cp">#include &lt;QMainWindow&gt;</span>

<span class="c1">// Point Cloud Library</span>
<span class="cp">#include &lt;pcl/point_cloud.h&gt;</span>
<span class="cp">#include &lt;pcl/point_types.h&gt;</span>
<span class="cp">#include &lt;pcl/visualization/pcl_visualizer.h&gt;</span>

<span class="c1">// Visualization Toolkit (VTK)</span>
<span class="cp">#include &lt;vtkRenderWindow.h&gt;</span>

<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGBA</span> <span class="n">PointT</span><span class="p">;</span>
<span class="k">typedef</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span> <span class="n">PointCloudT</span><span class="p">;</span>
</pre></div>
</div>
<p>This file is the header for the class PCLViewer; we include <tt class="docutils literal"><span class="pre">QMainWindow</span></tt> because this class contains UI elements, we include the PCL headers we will
be using and the VTK header for the <tt class="docutils literal"><span class="pre">qvtkWidget</span></tt>. We also define typedefs of the point types and point clouds, this improves readabily.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">namespace</span> <span class="n">Ui</span>
<span class="p">{</span>
  <span class="k">class</span> <span class="nc">PCLViewer</span><span class="p">;</span>
<span class="p">}</span>
</pre></div>
</div>
<p>We declare the namespace <tt class="docutils literal"><span class="pre">Ui</span></tt> and the class PCLViewer inside it.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">class</span> <span class="nc">PCLViewer</span> <span class="o">:</span> <span class="k">public</span> <span class="n">QMainWindow</span>
<span class="p">{</span>
  <span class="n">Q_OBJECT</span>
</pre></div>
</div>
<p>This is the definition of the PCLViewer class; the macro <tt class="docutils literal"><span class="pre">Q_OBJECT</span></tt> tells the compiler that this object contains UI elements;
this imply that this file will be processed through <a class="reference external" href="http://qt-project.org/doc/qt-4.8/moc.html">the Meta-Object Compiler (moc)</a>.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="nl">public:</span>
  <span class="k">explicit</span> <span class="nf">PCLViewer</span> <span class="p">(</span><span class="n">QWidget</span> <span class="o">*</span><span class="n">parent</span> <span class="o">=</span> <span class="mi">0</span><span class="p">);</span>
  <span class="o">~</span><span class="n">PCLViewer</span> <span class="p">();</span>
</pre></div>
</div>
<p>The constructor and destructor of the PCLViewer class.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">public</span> <span class="n">slots</span><span class="o">:</span>
  <span class="kt">void</span>
  <span class="n">randomButtonPressed</span> <span class="p">();</span>

  <span class="kt">void</span>
  <span class="nf">RGBsliderReleased</span> <span class="p">();</span>

  <span class="kt">void</span>
  <span class="nf">pSliderValueChanged</span> <span class="p">(</span><span class="kt">int</span> <span class="n">value</span><span class="p">);</span>

  <span class="kt">void</span>
  <span class="nf">redSliderValueChanged</span> <span class="p">(</span><span class="kt">int</span> <span class="n">value</span><span class="p">);</span>

  <span class="kt">void</span>
  <span class="nf">greenSliderValueChanged</span> <span class="p">(</span><span class="kt">int</span> <span class="n">value</span><span class="p">);</span>

  <span class="kt">void</span>
  <span class="nf">blueSliderValueChanged</span> <span class="p">(</span><span class="kt">int</span> <span class="n">value</span><span class="p">);</span>
</pre></div>
</div>
<p>These are the public slots; these functions will be linked with UI elements actions.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="nl">protected:</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">viewer</span><span class="p">;</span>
  <span class="n">PointCloudT</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">cloud</span><span class="p">;</span>

  <span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">red</span><span class="p">;</span>
  <span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">green</span><span class="p">;</span>
  <span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">blue</span><span class="p">;</span>
</pre></div>
</div>
<div class="line-block">
<div class="line">A boost shared pointer to a PCLVisualier and a pointer to a point cloud are defined here.</div>
<div class="line">The integers <tt class="docutils literal"><span class="pre">red</span></tt>, <tt class="docutils literal"><span class="pre">green</span></tt>, <tt class="docutils literal"><span class="pre">blue</span></tt> will help us store the value of the sliders.</div>
</div>
</div>
<div class="section" id="pclviewer-cpp">
<h3><a class="toc-backref" href="#id8">pclviewer.cpp</a></h3>
<div class="highlight-cpp"><div class="highlight"><pre><span class="cp">#include &quot;pclviewer.h&quot;</span>
<span class="cp">#include &quot;../build/ui_pclviewer.h&quot;</span>

<span class="n">PCLViewer</span><span class="o">::</span><span class="n">PCLViewer</span> <span class="p">(</span><span class="n">QWidget</span> <span class="o">*</span><span class="n">parent</span><span class="p">)</span> <span class="o">:</span>
  <span class="n">QMainWindow</span> <span class="p">(</span><span class="n">parent</span><span class="p">),</span>
  <span class="n">ui</span> <span class="p">(</span><span class="k">new</span> <span class="n">Ui</span><span class="o">::</span><span class="n">PCLViewer</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">ui</span><span class="o">-&gt;</span><span class="n">setupUi</span> <span class="p">(</span><span class="k">this</span><span class="p">);</span>
  <span class="k">this</span><span class="o">-&gt;</span><span class="n">setWindowTitle</span> <span class="p">(</span><span class="s">&quot;PCL viewer&quot;</span><span class="p">);</span>

  <span class="c1">// Setup the cloud pointer</span>
  <span class="n">cloud</span><span class="p">.</span><span class="n">reset</span> <span class="p">(</span><span class="k">new</span> <span class="n">PointCloudT</span><span class="p">);</span>
  <span class="c1">// The number of points in the cloud</span>
  <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">resize</span> <span class="p">(</span><span class="mi">200</span><span class="p">);</span>
</pre></div>
</div>
<p>We include the class header and the header for the UI object; note that this file is generated by the moc and it&#8217;s path depend on
where you call cmake !</p>
<p>After that is the constructor implementation; we setup the ui and the window title name.
| Then we initialize the cloud pointer member of the class at a newly allocated point cloud pointer.
| The cloud is resized to be able to hold 200 points.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// The default color</span>
  <span class="n">red</span>   <span class="o">=</span> <span class="mi">128</span><span class="p">;</span>
  <span class="n">green</span> <span class="o">=</span> <span class="mi">128</span><span class="p">;</span>
  <span class="n">blue</span>  <span class="o">=</span> <span class="mi">128</span><span class="p">;</span>

  <span class="c1">// Fill the cloud with some points</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">x</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">y</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">z</span> <span class="o">=</span> <span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">);</span>

    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">r</span> <span class="o">=</span> <span class="n">red</span><span class="p">;</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">g</span> <span class="o">=</span> <span class="n">green</span><span class="p">;</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">b</span> <span class="o">=</span> <span class="n">blue</span><span class="p">;</span>
  <span class="p">}</span>
</pre></div>
</div>
<div class="line-block">
<div class="line"><tt class="docutils literal"><span class="pre">red</span></tt> <tt class="docutils literal"><span class="pre">green</span></tt> and <tt class="docutils literal"><span class="pre">blue</span></tt> protected members are initialized to their default values.</div>
<div class="line">The cloud is filled with random points (in a cube) and accordingly to <tt class="docutils literal"><span class="pre">red</span></tt> <tt class="docutils literal"><span class="pre">green</span></tt> and <tt class="docutils literal"><span class="pre">blue</span></tt> colors.</div>
</div>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Set up the QVTK window</span>
  <span class="n">viewer</span><span class="p">.</span><span class="n">reset</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="p">(</span><span class="s">&quot;viewer&quot;</span><span class="p">,</span> <span class="nb">false</span><span class="p">));</span>
  <span class="n">ui</span><span class="o">-&gt;</span><span class="n">qvtkWidget</span><span class="o">-&gt;</span><span class="n">SetRenderWindow</span> <span class="p">(</span><span class="n">viewer</span><span class="o">-&gt;</span><span class="n">getRenderWindow</span> <span class="p">());</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setupInteractor</span> <span class="p">(</span><span class="n">ui</span><span class="o">-&gt;</span><span class="n">qvtkWidget</span><span class="o">-&gt;</span><span class="n">GetInteractor</span> <span class="p">(),</span> <span class="n">ui</span><span class="o">-&gt;</span><span class="n">qvtkWidget</span><span class="o">-&gt;</span><span class="n">GetRenderWindow</span> <span class="p">());</span>
  <span class="n">ui</span><span class="o">-&gt;</span><span class="n">qvtkWidget</span><span class="o">-&gt;</span><span class="n">update</span> <span class="p">();</span>
</pre></div>
</div>
<div class="line-block">
<div class="line">Here we create a PCL Visualizer name <tt class="docutils literal"><span class="pre">viewer</span></tt> and we also specify that we don&#8217;t want an interactor to be created.</div>
<div class="line">We don&#8217;t want an interactor to be created because our <tt class="docutils literal"><span class="pre">qvtkWidget</span></tt> is already an interactor and it&#8217;s the one we want to use.</div>
<div class="line">So the next step is to configure our newly created PCL Visualiser interactor to use the <tt class="docutils literal"><span class="pre">qvtkWidget</span></tt>.</div>
</div>
<p>The <tt class="docutils literal"><span class="pre">update()</span></tt> method of the <tt class="docutils literal"><span class="pre">qvtkWidget</span></tt> should be called each time you modify the PCL visualizer; if you don&#8217;t call it you don&#8217;t know if the
visualizer will be updated before the user try to pan/spin/zoom.</p>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="c1">// Connect &quot;random&quot; button and the function</span>
  <span class="n">connect</span> <span class="p">(</span><span class="n">ui</span><span class="o">-&gt;</span><span class="n">pushButton_random</span><span class="p">,</span>  <span class="n">SIGNAL</span> <span class="p">(</span><span class="n">clicked</span> <span class="p">()),</span> <span class="k">this</span><span class="p">,</span> <span class="n">SLOT</span> <span class="p">(</span><span class="n">randomButtonPressed</span> <span class="p">()));</span>

  <span class="c1">// Connect R,G,B sliders and their functions</span>
  <span class="n">connect</span> <span class="p">(</span><span class="n">ui</span><span class="o">-&gt;</span><span class="n">horizontalSlider_R</span><span class="p">,</span> <span class="n">SIGNAL</span> <span class="p">(</span><span class="n">valueChanged</span> <span class="p">(</span><span class="kt">int</span><span class="p">)),</span> <span class="k">this</span><span class="p">,</span> <span class="n">SLOT</span> <span class="p">(</span><span class="n">redSliderValueChanged</span> <span class="p">(</span><span class="kt">int</span><span class="p">)));</span>
  <span class="n">connect</span> <span class="p">(</span><span class="n">ui</span><span class="o">-&gt;</span><span class="n">horizontalSlider_G</span><span class="p">,</span> <span class="n">SIGNAL</span> <span class="p">(</span><span class="n">valueChanged</span> <span class="p">(</span><span class="kt">int</span><span class="p">)),</span> <span class="k">this</span><span class="p">,</span> <span class="n">SLOT</span> <span class="p">(</span><span class="n">greenSliderValueChanged</span> <span class="p">(</span><span class="kt">int</span><span class="p">)));</span>
  <span class="n">connect</span> <span class="p">(</span><span class="n">ui</span><span class="o">-&gt;</span><span class="n">horizontalSlider_B</span><span class="p">,</span> <span class="n">SIGNAL</span> <span class="p">(</span><span class="n">valueChanged</span> <span class="p">(</span><span class="kt">int</span><span class="p">)),</span> <span class="k">this</span><span class="p">,</span> <span class="n">SLOT</span> <span class="p">(</span><span class="n">blueSliderValueChanged</span> <span class="p">(</span><span class="kt">int</span><span class="p">)));</span>
  <span class="n">connect</span> <span class="p">(</span><span class="n">ui</span><span class="o">-&gt;</span><span class="n">horizontalSlider_R</span><span class="p">,</span> <span class="n">SIGNAL</span> <span class="p">(</span><span class="n">sliderReleased</span> <span class="p">()),</span> <span class="k">this</span><span class="p">,</span> <span class="n">SLOT</span> <span class="p">(</span><span class="n">RGBsliderReleased</span> <span class="p">()));</span>
  <span class="n">connect</span> <span class="p">(</span><span class="n">ui</span><span class="o">-&gt;</span><span class="n">horizontalSlider_G</span><span class="p">,</span> <span class="n">SIGNAL</span> <span class="p">(</span><span class="n">sliderReleased</span> <span class="p">()),</span> <span class="k">this</span><span class="p">,</span> <span class="n">SLOT</span> <span class="p">(</span><span class="n">RGBsliderReleased</span> <span class="p">()));</span>
  <span class="n">connect</span> <span class="p">(</span><span class="n">ui</span><span class="o">-&gt;</span><span class="n">horizontalSlider_B</span><span class="p">,</span> <span class="n">SIGNAL</span> <span class="p">(</span><span class="n">sliderReleased</span> <span class="p">()),</span> <span class="k">this</span><span class="p">,</span> <span class="n">SLOT</span> <span class="p">(</span><span class="n">RGBsliderReleased</span> <span class="p">()));</span>

  <span class="c1">// Connect point size slider</span>
  <span class="n">connect</span> <span class="p">(</span><span class="n">ui</span><span class="o">-&gt;</span><span class="n">horizontalSlider_p</span><span class="p">,</span> <span class="n">SIGNAL</span> <span class="p">(</span><span class="n">valueChanged</span> <span class="p">(</span><span class="kt">int</span><span class="p">)),</span> <span class="k">this</span><span class="p">,</span> <span class="n">SLOT</span> <span class="p">(</span><span class="n">pSliderValueChanged</span> <span class="p">(</span><span class="kt">int</span><span class="p">)));</span>
</pre></div>
</div>
<dl class="docutils">
<dt>Here we connect slots and signals, this links UI actions to functions. Here is a summary of what we have linked :</dt>
<dd><ul class="first last">
<li><dl class="first docutils">
<dt><tt class="docutils literal"><span class="pre">pushButton_random</span></tt>:</dt>
<dd><div class="first last line-block">
<div class="line">if button is pressed call <tt class="docutils literal"><span class="pre">randomButtonPressed</span> <span class="pre">()</span></tt></div>
</div>
</dd>
</dl>
</li>
<li><dl class="first docutils">
<dt><tt class="docutils literal"><span class="pre">horizontalSlider_R</span></tt>:</dt>
<dd><div class="first last line-block">
<div class="line">if slider value is changed call <tt class="docutils literal"><span class="pre">redSliderValueChanged(int)</span></tt> with the new value as argument</div>
<div class="line">if slider is released call <tt class="docutils literal"><span class="pre">RGBsliderReleased()</span></tt></div>
</div>
</dd>
</dl>
</li>
<li><dl class="first docutils">
<dt><tt class="docutils literal"><span class="pre">horizontalSlider_G</span></tt>:</dt>
<dd><div class="first last line-block">
<div class="line">if slider value is changed call <tt class="docutils literal"><span class="pre">redSliderValueChanged(int)</span></tt> with the new value as argument</div>
<div class="line">if slider is released call <tt class="docutils literal"><span class="pre">RGBsliderReleased()</span></tt></div>
</div>
</dd>
</dl>
</li>
<li><dl class="first docutils">
<dt><tt class="docutils literal"><span class="pre">horizontalSlider_B</span></tt>:</dt>
<dd><div class="first last line-block">
<div class="line">if slider value is changed call <tt class="docutils literal"><span class="pre">redSliderValueChanged(int)</span></tt> with the new value as argument</div>
<div class="line">if slider is released call <tt class="docutils literal"><span class="pre">RGBsliderReleased()</span></tt></div>
</div>
</dd>
</dl>
</li>
</ul>
</dd>
</dl>
<div class="highlight-cpp"><div class="highlight"><pre>  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addPointCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="s">&quot;cloud&quot;</span><span class="p">);</span>
  <span class="n">pSliderValueChanged</span> <span class="p">(</span><span class="mi">2</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">resetCamera</span> <span class="p">();</span>
  <span class="n">ui</span><span class="o">-&gt;</span><span class="n">qvtkWidget</span><span class="o">-&gt;</span><span class="n">update</span> <span class="p">();</span>
<span class="p">}</span>
</pre></div>
</div>
<div class="line-block">
<div class="line">This is the last part of our constructor; we add the point cloud to the visualizer, call the method <tt class="docutils literal"><span class="pre">pSliderValueChanged</span></tt> to change the point size to 2.</div>
</div>
<p>We finaly reset the camera within the PCL Visualizer not avoid the user having to zoom out and update the qvtkwidget to be
sure the modifications will be displayed.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">void</span>
<span class="n">PCLViewer</span><span class="o">::</span><span class="n">randomButtonPressed</span> <span class="p">()</span>
<span class="p">{</span>
  <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;Random button was pressed</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>

  <span class="c1">// Set the new color</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">size</span><span class="p">();</span> <span class="n">i</span><span class="o">++</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">r</span> <span class="o">=</span> <span class="mi">255</span> <span class="o">*</span><span class="p">(</span><span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">));</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">g</span> <span class="o">=</span> <span class="mi">255</span> <span class="o">*</span><span class="p">(</span><span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">));</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">b</span> <span class="o">=</span> <span class="mi">255</span> <span class="o">*</span><span class="p">(</span><span class="mi">1024</span> <span class="o">*</span> <span class="n">rand</span> <span class="p">()</span> <span class="o">/</span> <span class="p">(</span><span class="n">RAND_MAX</span> <span class="o">+</span> <span class="mf">1.0f</span><span class="p">));</span>
  <span class="p">}</span>

  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">updatePointCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="s">&quot;cloud&quot;</span><span class="p">);</span>
  <span class="n">ui</span><span class="o">-&gt;</span><span class="n">qvtkWidget</span><span class="o">-&gt;</span><span class="n">update</span> <span class="p">();</span>
<span class="p">}</span>
</pre></div>
</div>
<div class="line-block">
<div class="line">This is the public slot function member called when the push button &#8220;Random&#8221; is pressed.</div>
<div class="line">The <tt class="docutils literal"><span class="pre">for</span></tt> loop iterates through the point cloud and changes point cloud color to a random number (between 0 and 255).</div>
<div class="line">The point cloud is then updated and so the <tt class="docutils literal"><span class="pre">qtvtkwidget</span></tt> is.</div>
</div>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">void</span>
<span class="n">PCLViewer</span><span class="o">::</span><span class="n">RGBsliderReleased</span> <span class="p">()</span>
<span class="p">{</span>
  <span class="c1">// Set the new color</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">size</span> <span class="p">();</span> <span class="n">i</span><span class="o">++</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">r</span> <span class="o">=</span> <span class="n">red</span><span class="p">;</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">g</span> <span class="o">=</span> <span class="n">green</span><span class="p">;</span>
    <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">].</span><span class="n">b</span> <span class="o">=</span> <span class="n">blue</span><span class="p">;</span>
  <span class="p">}</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">updatePointCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="s">&quot;cloud&quot;</span><span class="p">);</span>
  <span class="n">ui</span><span class="o">-&gt;</span><span class="n">qvtkWidget</span><span class="o">-&gt;</span><span class="n">update</span> <span class="p">();</span>
<span class="p">}</span>
</pre></div>
</div>
<div class="line-block">
<div class="line">This is the public slot function member called whenever the red, green or blue slider is released</div>
<div class="line">The <tt class="docutils literal"><span class="pre">for</span></tt> loop iterates through the point cloud and changes point cloud color to <tt class="docutils literal"><span class="pre">red</span></tt>, <tt class="docutils literal"><span class="pre">green</span></tt> and <tt class="docutils literal"><span class="pre">blue</span></tt> member values.</div>
<div class="line">The point cloud is then updated and so the <tt class="docutils literal"><span class="pre">qtvtkwidget</span></tt> is.</div>
</div>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">void</span>
<span class="n">PCLViewer</span><span class="o">::</span><span class="n">redSliderValueChanged</span> <span class="p">(</span><span class="kt">int</span> <span class="n">value</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">red</span> <span class="o">=</span> <span class="n">value</span><span class="p">;</span>
  <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;redSliderValueChanged: [%d|%d|%d]</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">red</span><span class="p">,</span> <span class="n">green</span><span class="p">,</span> <span class="n">blue</span><span class="p">);</span>
<span class="p">}</span>

<span class="kt">void</span>
<span class="n">PCLViewer</span><span class="o">::</span><span class="n">greenSliderValueChanged</span> <span class="p">(</span><span class="kt">int</span> <span class="n">value</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">green</span> <span class="o">=</span> <span class="n">value</span><span class="p">;</span>
  <span class="n">printf</span> <span class="p">(</span><span class="s">&quot;greenSliderValueChanged: [%d|%d|%d]</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">red</span><span class="p">,</span> <span class="n">green</span><span class="p">,</span> <span class="n">blue</span><span class="p">);</span>
<span class="p">}</span>

<span class="kt">void</span>
<span class="n">PCLViewer</span><span class="o">::</span><span class="n">blueSliderValueChanged</span> <span class="p">(</span><span class="kt">int</span> <span class="n">value</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">blue</span> <span class="o">=</span> <span class="n">value</span><span class="p">;</span>
  <span class="n">printf</span><span class="p">(</span><span class="s">&quot;blueSliderValueChanged: [%d|%d|%d]</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">red</span><span class="p">,</span> <span class="n">green</span><span class="p">,</span> <span class="n">blue</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</div>
<div class="line-block">
<div class="line">These are the public slot function member called whenever the red, green or blue slider value is changed</div>
<div class="line">These functions just changes the member value accordingly to the slider value.</div>
<div class="line">Here the point cloud is not updated; so until you release the slider you won&#8217;t see any change in the visualizer.</div>
</div>
<div class="highlight-cpp"><div class="highlight"><pre><span class="n">PCLViewer</span><span class="o">::~</span><span class="n">PCLViewer</span> <span class="p">()</span>
<span class="p">{</span>
  <span class="k">delete</span> <span class="n">ui</span><span class="p">;</span>
<span class="p">}</span>
</pre></div>
</div>
<p>The destructor.</p>
</div>
</div>
<div class="section" id="compiling-and-running">
<h2><a class="toc-backref" href="#id9">Compiling and running</a></h2>
<dl class="docutils">
<dt>There are two options here :</dt>
<dd><ul class="first last simple">
<li>You have configured the Qt project and you can compile/run just by clicking on the bottom left &#8220;Play&#8221; button.</li>
<li>You didn&#8217;t configure the Qt project; just go to the build folder an run <tt class="docutils literal"><span class="pre">cmake</span> <span class="pre">../src</span> <span class="pre">&amp;&amp;</span> <span class="pre">make</span> <span class="pre">-j2</span> <span class="pre">&amp;&amp;</span> <span class="pre">./pcl_visualizer</span></tt></li>
</ul>
</dd>
</dl>
<div class="line-block">
<div class="line">Notice that when changing the slider color, the cloud is not updated until you release the slider (<tt class="docutils literal"><span class="pre">sliderReleased</span> <span class="pre">()</span></tt> slot).</div>
</div>
<p>If you wanted to update the point cloud when the slider value is changed you could just call the <tt class="docutils literal"><span class="pre">RGBsliderReleased</span> <span class="pre">()</span></tt> function inside the
<tt class="docutils literal"><span class="pre">*sliderValueChanged</span> <span class="pre">(int)</span></tt> functions. The connect between  <tt class="docutils literal"><span class="pre">sliderReleased</span> <span class="pre">()</span></tt> / <tt class="docutils literal"><span class="pre">RGBsliderReleased</span> <span class="pre">()</span></tt> would become useless then.</p>
<div class="line-block">
<div class="line">When using the slider for the point size; the size of the point is updated without having to release the slider.</div>
</div>
<a class="reference internal image-reference" href="_images/pcl_visualizer.gif"><img alt="_images/pcl_visualizer.gif" src="_images/pcl_visualizer.gif" style="height: 527px;" /></a>
</div>
<div class="section" id="more-on-qt-and-pcl">
<h2><a class="toc-backref" href="#id10">More on Qt and PCL</a></h2>
<p>If you want to know more about Qt and PCL go take a look at <a class="reference external" href="https://github.com/PointCloudLibrary/pcl/tree/master/apps">PCL apps</a> like
<a class="reference external" href="https://github.com/PointCloudLibrary/pcl/tree/master/apps/src/pcd_video_player">PCD video player</a>
or <a class="reference external" href="https://github.com/PointCloudLibrary/pcl/tree/master/apps/src/manual_registration">manual registration</a>.</p>
<p>Re-use the <a class="reference download internal" href="_downloads/CMakeLists1.txt"><tt class="xref download docutils literal"><span class="pre">CMakeLists.txt</span></tt></a> from this tutorial if you want to compile the application outside of PCL.</p>
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