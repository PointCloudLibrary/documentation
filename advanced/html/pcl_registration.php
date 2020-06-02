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
    
    <title>PCL/registration</title>
    
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
            
  <div class="section" id="pcl-registration">
<h1>PCL/registration</h1>
<div class="section" id="participants">
<h2>Participants</h2>
<ul class="simple">
<li>Michael Dixon</li>
<li>Radu Rusu</li>
<li>Nicola Fioraio</li>
<li>Jochen Sprickerhof</li>
</ul>
</div>
<div class="section" id="existing-frameworks">
<h2>Existing Frameworks</h2>
<ul class="simple">
<li>SLAM6D</li>
<li>Toro</li>
<li>Hogman</li>
<li>G2O</li>
<li>MegaSLAM/MegaICP</li>
</ul>
</div>
<div class="section" id="mission">
<h2>Mission</h2>
<p>Provide a common interface/architecture for all of these and future SLAM ideas.</p>
</div>
<div class="section" id="ideas">
<h2>Ideas</h2>
<ul class="simple">
<li>Separate algorithms from data structures.</li>
<li>strip down everything to it&#8217;s basics and define an interface.</li>
<li>modify data structure in algorithms (you can copy them before if you need to).</li>
<li>point clouds are not transformed, only the translation and rotation is updated.</li>
</ul>
</div>
<div class="section" id="data-structures">
<h2>Data structures</h2>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">These ideas are independent of actual data structures in the PCL for now. We can see later how to integrate them best.</p>
</div>
<div class="section" id="pose">
<h3>Pose</h3>
<div class="highlight-c++"><div class="highlight"><pre><span class="k">struct</span> <span class="n">Pose</span>
<span class="p">{</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector3</span> <span class="n">translation</span><span class="p">;</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Quaternion</span> <span class="n">rotation</span><span class="p">;</span>
<span class="p">}</span>
</pre></div>
</div>
</div>
<div class="section" id="pointcloud">
<h3>PointCloud</h3>
<div class="highlight-c++"><div class="highlight"><pre><span class="k">typedef</span> <span class="n">vector</span><span class="o">&lt;</span><span class="n">vector</span> <span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="o">&gt;</span> <span class="n">Points</span><span class="p">;</span>
</pre></div>
</div>
</div>
<div class="section" id="posedpointcloud">
<h3>PosedPointCloud</h3>
<div class="highlight-c++"><div class="highlight"><pre><span class="k">typedef</span> <span class="n">pair</span><span class="o">&lt;</span><span class="n">Pose</span><span class="o">*</span><span class="p">,</span> <span class="n">PointCloud</span><span class="o">*&gt;</span> <span class="n">PosedPointCloud</span><span class="p">;</span>
</pre></div>
</div>
<p>PointCloud* can be 0.</p>
</div>
<div class="section" id="graph">
<h3>Graph</h3>
<p>This should hold the SLAM graph. I would propose to use Boost::Graph for it, as it allows us to access a lot of algorithms.</p>
</div>
<div class="section" id="covariancematrix">
<h3>CovarianceMatrix</h3>
<div class="highlight-c++"><div class="highlight"><pre><span class="k">typedef</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix4f</span> <span class="n">CovarianceMatrix</span><span class="p">;</span>
</pre></div>
</div>
</div>
<div class="section" id="measurement">
<h3>Measurement</h3>
<div class="highlight-c++"><div class="highlight"><pre><span class="k">struct</span> <span class="n">Measurement</span>
<span class="p">{</span>
  <span class="n">Pose</span> <span class="n">pose</span><span class="p">;</span>
  <span class="n">CovarianceMatrix</span> <span class="n">covariance</span><span class="p">;</span>
<span class="p">}</span>
</pre></div>
</div>
<p>Idea: change the CovarianceMatrix into a function pointer.</p>
</div>
</div>
<div class="section" id="interfaces">
<h2>Interfaces</h2>
<div class="section" id="globalregistration">
<h3>GlobalRegistration</h3>
<div class="highlight-c++"><div class="highlight"><pre><span class="k">class</span> <span class="nc">GlobalRegistration</span>
<span class="p">{</span>
  <span class="nl">public:</span>
    <span class="cm">/**</span>
<span class="cm">      * \param history how many poses should be cached (0 means all)</span>
<span class="cm">      */</span>
    <span class="n">GlobalRegistration</span> <span class="p">(</span><span class="kt">int</span> <span class="n">history</span> <span class="o">=</span> <span class="mi">0</span><span class="p">)</span> <span class="o">:</span> <span class="n">history_</span><span class="p">(</span><span class="n">history</span><span class="p">)</span> <span class="p">{}</span>

    <span class="cm">/**</span>
<span class="cm">      * \param pc a new point cloud for GlobalRegistration</span>
<span class="cm">      * \param pose the initial pose of the pc, could be 0 (unknown)</span>
<span class="cm">      */</span>
    <span class="kt">void</span> <span class="n">addPointCloud</span> <span class="p">(</span><span class="n">PointCloud</span> <span class="o">&amp;</span><span class="n">pc</span><span class="p">,</span> <span class="n">Pose</span> <span class="o">&amp;</span><span class="n">pose</span> <span class="o">=</span> <span class="mi">0</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">new_clouds_</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">make_pair</span> <span class="p">(</span><span class="n">pc</span><span class="p">,</span> <span class="n">pose</span><span class="p">));</span>
    <span class="p">}</span>

    <span class="cm">/**</span>
<span class="cm">      * returns the current estimate of the transformation from point cloud from to point cloud to</span>
<span class="cm">        throws an exception if the transformation is unknown</span>
<span class="cm">      */</span>
    <span class="n">Pose</span> <span class="n">getTF</span> <span class="p">(</span><span class="n">PointCloud</span> <span class="o">&amp;</span><span class="n">from</span><span class="p">,</span> <span class="n">PointCloud</span> <span class="o">&amp;</span><span class="n">to</span><span class="p">);</span>

    <span class="cm">/**</span>
<span class="cm">      * run the optimization process</span>
<span class="cm">      * \param lod the level of detail (optional). Roughly how long it should run (TODO: better name/parametrization?)</span>
<span class="cm">      */</span>
    <span class="k">virtual</span> <span class="kt">void</span> <span class="nf">compute</span> <span class="p">(</span><span class="kt">int</span> <span class="n">lod</span> <span class="o">=</span> <span class="mi">0</span><span class="p">)</span> <span class="p">{}</span>

  <span class="nl">private:</span>
    <span class="kt">int</span> <span class="n">history_</span><span class="p">;</span>
    <span class="n">map</span><span class="o">&lt;</span><span class="n">PointCloud</span><span class="o">*</span><span class="p">,</span> <span class="n">Pose</span><span class="o">*&gt;</span> <span class="n">poses_</span><span class="p">;</span>
    <span class="n">PosedPointCloud</span> <span class="n">new_clouds_</span><span class="p">;</span>
<span class="p">};</span>
</pre></div>
</div>
<p>This will be the base class interface for every SLAM algorithm. At any point you can add point clouds and they will be processed.
The poses can be either in a global or in a local coordinate system (meaning that they are incremental regarding the last one).
Idea: Do we need the compute? Could it be included into the addPointCloud or getTF?</p>
</div>
<div class="section" id="graphoptimizer">
<h3>GraphOptimizer</h3>
<div class="highlight-c++"><div class="highlight"><pre><span class="k">class</span> <span class="nc">GraphOptimizer</span>
<span class="p">{</span>
  <span class="nl">public:</span>
    <span class="k">virtual</span> <span class="kt">void</span> <span class="n">optimize</span> <span class="p">(</span><span class="n">Graph</span> <span class="o">&amp;</span><span class="n">gr</span><span class="p">)</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
<span class="p">}</span>
</pre></div>
</div>
</div>
<div class="section" id="loopdetection">
<h3>LoopDetection</h3>
<div class="highlight-c++"><div class="highlight"><pre><span class="k">class</span> <span class="nc">LoopDetection</span>
<span class="p">{</span>
  <span class="nl">public:</span>
    <span class="k">virtual</span> <span class="o">~</span><span class="n">LoopDetection</span> <span class="p">()</span> <span class="p">{}</span>
    <span class="k">virtual</span> <span class="n">list</span><span class="o">&lt;</span><span class="n">pair</span><span class="o">&lt;</span><span class="n">PointCloud</span><span class="o">*</span><span class="p">,</span> <span class="n">PointCloud</span><span class="o">*&gt;</span> <span class="o">&gt;</span> <span class="n">detectLoop</span><span class="p">(</span><span class="n">list</span><span class="o">&lt;</span><span class="n">PosedPointCloud</span><span class="o">*&gt;</span> <span class="n">poses</span><span class="p">,</span> <span class="n">list</span><span class="o">&lt;</span><span class="n">PosedPointCloud</span><span class="o">*&gt;</span> <span class="n">query</span><span class="p">)</span> <span class="p">{}</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
<span class="p">}</span>
</pre></div>
</div>
</div>
<div class="section" id="graphhandler">
<h3>GraphHandler</h3>
<div class="highlight-c++"><div class="highlight"><pre><span class="k">class</span> <span class="nc">GraphHandler</span>
<span class="p">{</span>
  <span class="kt">void</span> <span class="n">addPose</span> <span class="p">(</span><span class="n">Graph</span> <span class="o">&amp;</span><span class="n">gr</span><span class="p">,</span> <span class="n">PointCloud</span> <span class="o">&amp;</span><span class="n">pc</span><span class="p">);</span>
  <span class="kt">void</span> <span class="nf">addConstraint</span> <span class="p">(</span><span class="n">Graph</span> <span class="o">&amp;</span><span class="n">gr</span><span class="p">,</span> <span class="n">PointCloud</span> <span class="o">&amp;</span><span class="n">from</span><span class="p">,</span> <span class="n">PointCloud</span> <span class="o">&amp;</span><span class="n">to</span><span class="p">,</span> <span class="n">Pose</span> <span class="o">&amp;</span><span class="n">pose</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</div>
</div>
</div>
<div class="section" id="example-implementations">
<h2>Example Implementations</h2>
<div class="section" id="pairwiseglobalregistration">
<h3>PairwiseGlobalRegistration</h3>
<div class="highlight-c++"><div class="highlight"><pre><span class="k">class</span> <span class="nc">PairwiseGlobalRegistration</span> <span class="o">:</span> <span class="k">public</span> <span class="n">GlobalRegistration</span>
<span class="p">{</span>
  <span class="nl">public:</span>
    <span class="n">PairwiseGlobalRegistration</span><span class="p">(</span><span class="n">Registration</span> <span class="o">&amp;</span><span class="n">reg</span><span class="p">)</span> <span class="o">:</span> <span class="n">reg_</span><span class="p">(</span><span class="n">reg</span><span class="p">)</span> <span class="p">{}</span>
    <span class="k">virtual</span> <span class="kt">void</span> <span class="n">compute</span> <span class="p">(</span><span class="kt">int</span> <span class="n">lod</span> <span class="o">=</span> <span class="mi">0</span><span class="p">)</span> <span class="p">{}</span>
    <span class="p">{</span>
      <span class="n">list</span><span class="o">&lt;</span><span class="n">PosedPointCloud</span> <span class="o">&gt;::</span><span class="n">iterator</span> <span class="n">cloud_it</span><span class="p">;</span>
      <span class="k">for</span> <span class="p">(</span><span class="n">cloud_it</span> <span class="o">=</span> <span class="n">new_clouds_</span><span class="p">.</span><span class="n">begin</span><span class="p">();</span> <span class="n">cloud_it</span> <span class="o">!=</span> <span class="n">new_clouds_</span><span class="p">.</span><span class="n">end</span><span class="p">();</span> <span class="n">cloud_it</span><span class="o">++</span><span class="p">)</span>
      <span class="p">{</span>
        <span class="k">if</span><span class="p">(</span><span class="o">!</span><span class="n">old_</span><span class="p">)</span> <span class="p">{</span>
          <span class="n">old</span> <span class="o">=</span> <span class="o">*</span><span class="n">cloud_it</span><span class="p">;</span>
          <span class="k">continue</span><span class="p">;</span>
        <span class="p">}</span>
        <span class="n">reg_</span><span class="p">.</span><span class="n">align</span><span class="p">(</span><span class="n">old_</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud_it</span><span class="p">,</span> <span class="n">transformation</span><span class="p">);</span>
        <span class="n">poses</span><span class="p">[</span><span class="o">*</span><span class="n">cloud_it</span><span class="p">]</span> <span class="o">=</span> <span class="n">transformation</span><span class="p">;</span>
        <span class="n">old_</span> <span class="o">=</span> <span class="o">*</span><span class="n">cloud_it</span><span class="p">;</span>
      <span class="p">}</span>
      <span class="n">new_clouds_</span><span class="p">.</span><span class="n">clear</span><span class="p">();</span>
    <span class="p">}</span>

  <span class="nl">private:</span>
    <span class="n">Registration</span> <span class="o">&amp;</span><span class="n">reg_</span><span class="p">;</span>
    <span class="n">PointCloud</span> <span class="o">&amp;</span><span class="n">old_</span><span class="p">;</span>
<span class="p">}</span>
</pre></div>
</div>
</div>
<div class="section" id="distanceloopdetection">
<h3>DistanceLoopDetection</h3>
<div class="highlight-c++"><div class="highlight"><pre><span class="k">class</span> <span class="nc">DistanceLoopDetection</span> <span class="o">:</span> <span class="n">LoopDetection</span>
<span class="p">{</span>
  <span class="nl">public:</span>
    <span class="k">virtual</span> <span class="n">list</span><span class="o">&lt;</span><span class="n">pair</span><span class="o">&lt;</span><span class="n">PointCloud</span><span class="o">*</span><span class="p">,</span> <span class="n">PointCloud</span><span class="o">*&gt;</span> <span class="o">&gt;</span> <span class="n">detectLoop</span><span class="p">(</span><span class="n">list</span><span class="o">&lt;</span><span class="n">PosedPointCloud</span><span class="o">*&gt;</span> <span class="n">poses</span><span class="p">,</span> <span class="n">list</span><span class="o">&lt;</span><span class="n">PosedPointCloud</span><span class="o">*&gt;</span> <span class="n">query</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="c1">//I want a map reduce here ;)</span>
      <span class="n">list</span><span class="o">&lt;</span><span class="n">PosedPointCloud</span> <span class="o">&gt;::</span><span class="n">iterator</span> <span class="n">poses_it</span><span class="p">;</span>
      <span class="k">for</span> <span class="p">(</span><span class="n">poses_it</span> <span class="o">=</span> <span class="n">poses</span><span class="p">.</span><span class="n">begin</span><span class="p">();</span> <span class="n">poses_it</span> <span class="o">!=</span> <span class="n">poses</span><span class="p">.</span><span class="n">end</span><span class="p">();</span> <span class="n">poses_it</span><span class="o">++</span><span class="p">)</span>
      <span class="p">{</span>
        <span class="n">list</span><span class="o">&lt;</span><span class="n">PosedPointCloud</span> <span class="o">&gt;::</span><span class="n">iterator</span> <span class="n">query_it</span><span class="p">;</span>
        <span class="k">for</span> <span class="p">(</span><span class="n">query_it</span> <span class="o">=</span> <span class="n">query</span><span class="p">.</span><span class="n">begin</span><span class="p">();</span> <span class="n">query_it</span> <span class="o">!=</span> <span class="n">query</span><span class="p">.</span><span class="n">end</span><span class="p">();</span> <span class="n">query_it</span><span class="o">++</span><span class="p">)</span>
        <span class="p">{</span>
          <span class="k">if</span> <span class="p">(</span><span class="n">dist</span> <span class="p">(</span><span class="o">*</span><span class="n">poses_it</span><span class="p">,</span> <span class="o">*</span><span class="n">query_it</span><span class="p">)</span> <span class="o">&lt;</span> <span class="n">min_dist_</span><span class="p">)</span>
          <span class="p">{</span>
            <span class="c1">//..</span>
          <span class="p">}</span>
      <span class="p">}</span>

    <span class="p">}</span>

<span class="p">}</span>
</pre></div>
</div>
</div>
<div class="section" id="elch">
<h3>ELCH</h3>
<div class="highlight-c++"><div class="highlight"><pre><span class="k">class</span> <span class="nc">ELCH</span> <span class="o">:</span> <span class="k">public</span> <span class="n">GlobalRegistration</span>
<span class="p">{</span>
  <span class="nl">public:</span>
    <span class="n">ELCH</span><span class="p">(</span><span class="n">GlobalRegistration</span> <span class="o">&amp;</span><span class="n">initial_optimizer</span> <span class="o">=</span> <span class="n">PairwiseGlobalRegistration</span><span class="p">(),</span> <span class="n">LoopDetection</span> <span class="o">&amp;</span><span class="n">loop_detection</span><span class="p">,</span> <span class="n">GraphOptimizer</span> <span class="o">&amp;</span><span class="n">loop_optimizer</span><span class="p">,</span> <span class="n">GraphOptimizer</span> <span class="o">&amp;</span><span class="n">graph_optimizer</span> <span class="o">=</span> <span class="n">LUM</span><span class="p">())</span>
<span class="p">}</span>
</pre></div>
</div>
</div>
<div class="section" id="lum">
<h3>LUM</h3>
<div class="highlight-c++"><div class="highlight"><pre><span class="k">class</span> <span class="nc">ELCH</span> <span class="o">:</span> <span class="k">public</span> <span class="n">GlobalRegistration</span>
<span class="p">{</span>
  <span class="nl">public:</span>
    <span class="n">ELCH</span><span class="p">(</span><span class="n">GlobalRegistration</span> <span class="o">&amp;</span><span class="n">initial_optimizer</span> <span class="o">=</span> <span class="n">PairwiseGlobalRegistration</span><span class="p">(),</span> <span class="n">LoopDetection</span> <span class="o">&amp;</span><span class="n">loop_detection</span><span class="p">,</span> <span class="n">GraphOptimizer</span> <span class="o">&amp;</span><span class="n">loop_optimizer</span><span class="p">,</span> <span class="n">GraphOptimizer</span> <span class="o">&amp;</span><span class="n">graph_optimizer</span><span class="p">)</span>
<span class="p">}</span>
</pre></div>
</div>
<p>Lu and Milios style scan matching (as in SLAM6D)</p>
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