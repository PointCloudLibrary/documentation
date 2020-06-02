<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation - Point Cloud Library (PCL)</title>
</head>

<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8" />
    <title>Exceptions in PCL</title>
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
            
  <div class="section" id="exceptions-in-pcl">
<span id="exceptions-guide"></span><h1>Exceptions in PCL</h1>
<p>There have been a multitude of discussions in the past regarding exceptions in
PCL (see <a class="reference external" href="http://www.pcl-developers.org/to-throw-or-not-to-throw-td4828759.html">http://www.pcl-developers.org/to-throw-or-not-to-throw-td4828759.html</a>
for an example). Herein, we discuss the major points with respect to writing
and using exceptions.</p>
<div class="section" id="adding-a-new-exception">
<h2>Adding a new Exception</h2>
<p>Any new exception should inherit from the <a href="#id1"><span class="problematic" id="id2">:pcl:`PCLException &lt;pcl::PCLException&gt;`</span></a> class in
<code class="docutils literal notranslate"><span class="pre">pcl/exceptions.h</span></code></p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="cm">/** \class MyException</span>
<span class="cm">  * \brief An exception that is thrown when I want it.</span>
<span class="cm">  */</span>

<span class="k">class</span> <span class="nc">PCL_EXPORTS</span> <span class="nl">MyException</span> <span class="p">:</span> <span class="k">public</span> <span class="n">PCLException</span>
<span class="p">{</span>
  <span class="k">public</span><span class="o">:</span>
    <span class="n">MyException</span> <span class="p">(</span><span class="k">const</span> <span class="n">std</span><span class="o">::</span><span class="n">string</span><span class="o">&amp;</span> <span class="n">error_description</span><span class="p">,</span>
                 <span class="k">const</span> <span class="kt">char</span><span class="o">*</span> <span class="n">file_name</span> <span class="o">=</span> <span class="nb">NULL</span><span class="p">,</span>
                 <span class="k">const</span> <span class="kt">char</span><span class="o">*</span> <span class="n">function_name</span> <span class="o">=</span> <span class="nb">NULL</span><span class="p">,</span>
                 <span class="kt">unsigned</span> <span class="n">line_number</span> <span class="o">=</span> <span class="mi">0</span><span class="p">)</span>
      <span class="o">:</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PCLException</span> <span class="p">(</span><span class="n">error_description</span><span class="p">,</span> <span class="n">file_name</span><span class="p">,</span> <span class="n">function_name</span><span class="p">,</span> <span class="n">line_number</span><span class="p">)</span> <span class="p">{</span> <span class="p">}</span>
<span class="p">};</span>
</pre></div>
</div>
</div>
<div class="section" id="using-exceptions">
<h2>Using Exceptions</h2>
<p>For ease of use we provide this macro</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="cp">#define PCL_THROW_EXCEPTION (ExceptionName, message)</span>
<span class="p">{</span>
  <span class="n">std</span><span class="o">::</span><span class="n">ostringstream</span> <span class="n">s</span><span class="p">;</span>
  <span class="n">s</span> <span class="o">&lt;&lt;</span> <span class="n">message</span><span class="p">;</span>
  <span class="k">throw</span> <span class="nf">ExceptionName</span> <span class="p">(</span><span class="n">s</span><span class="p">.</span><span class="n">str</span> <span class="p">(),</span> <span class="n">__FILE__</span><span class="p">,</span> <span class="n">BOOST_CURRENT_FUNCTION</span><span class="p">,</span> <span class="n">__LINE__</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</div>
<p>Then in your code, add:</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="k">if</span> <span class="p">(</span><span class="n">my_requirements</span> <span class="o">!=</span> <span class="n">the_parameters_used_</span><span class="p">)</span>
  <span class="n">PCL_THROW_EXCEPTION</span> <span class="p">(</span><span class="n">MyException</span><span class="p">,</span> <span class="s">&quot;my requirements are not met &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">the_parameters_used</span><span class="p">);</span>
</pre></div>
</div>
<p>This will set the file name and the line number thanks to the macro definition.
Take care of the message: it is the most important part of the exception. You
can profit of the std::ostringstream used in the macro, so you can append
variable names to variable values and so on to make it really explicit.  Also
something really important is when the method you are writing <code class="docutils literal notranslate"><span class="pre">can</span></code> throw an
exception, please add this to the the function documentation:</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="cm">/** Function that does cool stuff</span>
<span class="cm">  * \param nb number of points</span>
<span class="cm">  * \throws MyException</span>
<span class="cm">  */</span>
<span class="kt">void</span>
<span class="nf">myFunction</span> <span class="p">(</span><span class="kt">int</span> <span class="n">nb</span><span class="p">);</span>
</pre></div>
</div>
<p>This will be parsed by Doxygen and made available in the generated API
documentation so the person that would use your function knows that they have
to deal with an exception called <code class="docutils literal notranslate"><span class="pre">MyException</span></code>.</p>
</div>
<div class="section" id="exceptions-handling">
<h2>Exceptions handling</h2>
<p>To properly handle exceptions you need to use the <code class="docutils literal notranslate"><span class="pre">try</span></code>… <code class="docutils literal notranslate"><span class="pre">catch</span></code> block.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="c1">// Here we call myFunction which can throw MyException</span>
<span class="k">try</span>
<span class="p">{</span>
  <span class="n">myObject</span><span class="p">.</span><span class="n">myFunction</span> <span class="p">(</span><span class="n">some_number</span><span class="p">);</span>
  <span class="c1">// You can put more exceptions throwing instruction within same try block</span>
<span class="p">}</span>
<span class="c1">// We catch only MyException to be very specific</span>
<span class="k">catch</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">MyException</span><span class="o">&amp;</span> <span class="n">e</span><span class="p">)</span>
<span class="p">{</span>
  <span class="c1">// Code to deal with the exception maybe changing myObject.the_parameters_used_</span>
<span class="p">}</span>

<span class="c1">// Here we catch any exception</span>
<span class="cp">#if 0</span><span class="c"></span>
<span class="c">catch (exception&amp; e)</span>
<span class="c">{</span>
<span class="c">  // Code to deal with the exception maybe changing myObject.the_parameters_used_</span>
<span class="c">}</span>
<span class="cp">#endif</span>
</pre></div>
</div>
<p>Exceptions handling is really context dependent so there is no general
rule that can be applied but here are some of the most used guidelines:</p>
<blockquote>
<div><ul class="simple">
<li><p>exit with some error if the exception is critical</p></li>
<li><p>modify the parameters for the function that threw the exception and recall it again</p></li>
<li><p>throw an exception with a meaningful message saying that you encountred an exception</p></li>
<li><p>continue (really bad)</p></li>
</ul>
</div></blockquote>
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