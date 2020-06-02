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
    
    <title>PCL C++ Programming Style Guide</title>
    
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
    <link rel="next" title="How to write a good tutorial" href="how_to_write_a_tutorial.php" />
    <link rel="prev" title="Single compilation units" href="single_compile_unit.php" />
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
            
  <div class="section" id="pcl-c-programming-style-guide">
<span id="pcl-style-guide"></span><h1>PCL C++ Programming Style Guide</h1>
<p>To make sure that all code in PCL is coherent and easily understood by other
developers and users, we follow a set of strict rules that everyone should
adopt. These rules are not to be broken unless there is a very good reason to
do so. Changes to these rules are always possible, but the person proposing and
changing a rule will have the unfortunate task to go and apply the rule change
to all the existing code.</p>
<div class="contents local topic" id="table-of-contents">
<p class="topic-title first">Table of Contents</p>
<ul class="simple">
<li><a class="reference internal" href="#naming" id="id4">1. Naming</a><ul>
<li><a class="reference internal" href="#files" id="id5">1.1. Files</a></li>
<li><a class="reference internal" href="#directories" id="id6">1.2. Directories</a></li>
<li><a class="reference internal" href="#includes" id="id7">1.3. Includes</a></li>
<li><a class="reference internal" href="#defines-macros" id="id8">1.4. Defines &amp; Macros</a></li>
<li><a class="reference internal" href="#namespaces" id="id9">1.5. Namespaces</a></li>
<li><a class="reference internal" href="#classes-structs" id="id10">1.6. Classes / Structs</a></li>
<li><a class="reference internal" href="#functions-methods" id="id11">1.7. Functions / Methods</a></li>
<li><a class="reference internal" href="#variables" id="id12">1.8. Variables</a><ul>
<li><a class="reference internal" href="#iterators" id="id13">1.8.1. Iterators</a></li>
<li><a class="reference internal" href="#constants" id="id14">1.8.2. Constants</a></li>
<li><a class="reference internal" href="#member-variables" id="id15">1.8.3. Member variables</a></li>
</ul>
</li>
<li><a class="reference internal" href="#return-statements" id="id16">1.9. Return statements</a></li>
</ul>
</li>
<li><a class="reference internal" href="#indentation-and-formatting" id="id17">2. Indentation and Formatting</a><ul>
<li><a class="reference internal" href="#id1" id="id18">2.1. Namespaces</a></li>
<li><a class="reference internal" href="#classes" id="id19">2.2. Classes</a></li>
<li><a class="reference internal" href="#id2" id="id20">2.3. Functions / Methods</a></li>
<li><a class="reference internal" href="#braces" id="id21">2.4. Braces</a></li>
<li><a class="reference internal" href="#spacing" id="id22">2.5. Spacing</a></li>
<li><a class="reference internal" href="#automatic-code-formatting" id="id23">2.6. Automatic code formatting</a><ul>
<li><a class="reference internal" href="#emacs" id="id24">2.6.1. Emacs</a></li>
<li><a class="reference internal" href="#uncrustify" id="id25">2.6.2. Uncrustify</a></li>
<li><a class="reference internal" href="#eclipse" id="id26">2.6.3 Eclipse</a></li>
</ul>
</li>
</ul>
</li>
<li><a class="reference internal" href="#structuring" id="id27">3. Structuring</a><ul>
<li><a class="reference internal" href="#classes-and-api" id="id28">3.1. Classes and API</a></li>
<li><a class="reference internal" href="#passing-arguments" id="id29">3.2. Passing arguments</a></li>
</ul>
</li>
</ul>
</div>
<div class="section" id="naming">
<h2>1. Naming</h2>
<div class="section" id="files">
<h3>1.1. Files</h3>
<p>All files should be <strong>under_scored</strong>.</p>
<ul class="simple">
<li>Header files have the extension <strong>.h</strong></li>
<li>Templated implementation files have the extension <strong>.hpp</strong></li>
<li>Source files have the extension <strong>.cpp</strong></li>
</ul>
</div>
<div class="section" id="directories">
<h3>1.2. Directories</h3>
<p>All directories and subdirectories should be <strong>under_scored</strong>.</p>
<ul class="simple">
<li>Header files should go under <strong>include/</strong></li>
<li>Templated implementation files should go under <strong>include/impl/</strong></li>
<li>Source files should go under <strong>src/</strong></li>
</ul>
</div>
<div class="section" id="includes">
<h3>1.3. Includes</h3>
<p>Include statements are made with <strong>&#8220;quotes&#8221;</strong> only if the file is in the
same directory, in any other case the include statement is made with
<strong>&lt;chevron_brackets&gt;</strong>, e.g.:</p>
<blockquote>
<div><div class="highlight-cpp"><div class="highlight"><pre><span class="cp">#include &lt;pcl/module_name/file_name.h&gt;</span>
<span class="cp">#incluce &lt;pcl/module_name/impl/file_name.hpp&gt;</span>
</pre></div>
</div>
</div></blockquote>
</div>
<div class="section" id="defines-macros">
<h3>1.4. Defines &amp; Macros</h3>
<p>Macros should all be <strong>ALL_CAPITALS_AND_UNDERSCORED</strong>. Defines for header type
files also need a trailing underscore. Their naming should be mapped from their
include name: <tt class="docutils literal"><span class="pre">pcl/filters/bilateral.h</span></tt> becomes <tt class="docutils literal"><span class="pre">PCL_FILTERS_BILATERAL_H_</span></tt>.
The <tt class="docutils literal"><span class="pre">#ifndef</span></tt> and <tt class="docutils literal"><span class="pre">#define</span></tt> lines should be placed just past the BSD license.
The <tt class="docutils literal"><span class="pre">#endif</span></tt> goes all the way at the bottom and needs to have the define name in
its comment, e.g:</p>
<blockquote>
<div><div class="highlight-cpp"><div class="highlight"><pre><span class="c1">// the license</span>

<span class="cp">#ifndef PCL_MODULE_NAME_IMPL_FILE_NAME_HPP_</span>
<span class="cp">#define PCL_MODULE_NAME_IMPL_FILE_NAME_HPP_</span>

<span class="c1">// the code</span>

<span class="cp">#endif </span><span class="c1">// PCL_MODULE_NAME_IMPL_FILE_NAME_HPP_</span>
</pre></div>
</div>
</div></blockquote>
</div>
<div class="section" id="namespaces">
<h3>1.5. Namespaces</h3>
<p>Namespaces should be <strong>under_scored</strong>, e.g.:</p>
<blockquote>
<div><div class="highlight-cpp"><div class="highlight"><pre><span class="k">namespace</span> <span class="n">pcl_io</span>
<span class="p">{</span>
  <span class="p">...</span>
<span class="p">}</span>
</pre></div>
</div>
</div></blockquote>
</div>
<div class="section" id="classes-structs">
<h3>1.6. Classes / Structs</h3>
<p>Class names (and other type names) should be <strong>CamelCased</strong>.
Exception: if the class name contains a short acronym, the acronym itself
should be all capitals. Class and struct names are preferably <strong>nouns</strong>:
PFHEstimation instead of EstimatePFH.</p>
<p>Correct examples:</p>
<blockquote>
<div><div class="highlight-cpp"><div class="highlight"><pre><span class="k">class</span> <span class="nc">ExampleClass</span><span class="p">;</span>
<span class="k">class</span> <span class="nc">PFHEstimation</span><span class="p">;</span>
</pre></div>
</div>
</div></blockquote>
</div>
<div class="section" id="functions-methods">
<h3>1.7. Functions / Methods</h3>
<p>Functions and class method names should be <strong>camelCased</strong>, and arguments are
<strong>under_scored</strong>. Function and method names are preferably <strong>verbs</strong>, and the name
should make clear what it does: checkForErrors() instead of errorCheck(),
dumpDataToFile() instead of dataFile().</p>
<p>Correct usage:</p>
<blockquote>
<div><div class="highlight-cpp"><div class="highlight"><pre><span class="kt">int</span>
<span class="nf">applyExample</span> <span class="p">(</span><span class="kt">int</span> <span class="n">example_arg</span><span class="p">);</span>
</pre></div>
</div>
</div></blockquote>
</div>
<div class="section" id="variables">
<h3>1.8. Variables</h3>
<p>Variable names should be <strong>under_scored</strong>.</p>
<blockquote>
<div><div class="highlight-cpp"><div class="highlight"><pre><span class="kt">int</span> <span class="n">my_variable</span><span class="p">;</span>
</pre></div>
</div>
</div></blockquote>
<div class="section" id="iterators">
<h4>1.8.1. Iterators</h4>
<p>Iterator variables should indicate what they&#8217;re iterating over, e.g.:</p>
<blockquote>
<div><div class="highlight-cpp"><div class="highlight"><pre><span class="n">std</span><span class="o">::</span><span class="n">list</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">pid_list</span><span class="p">;</span>
<span class="n">std</span><span class="o">::</span><span class="n">list</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;::</span><span class="n">iterator</span> <span class="n">pid_it</span><span class="p">;</span>
</pre></div>
</div>
</div></blockquote>
</div>
<div class="section" id="constants">
<h4>1.8.2. Constants</h4>
<p>Constants should be <strong>ALL_CAPITALS</strong>, e.g.:</p>
<blockquote>
<div><div class="highlight-cpp"><div class="highlight"><pre><span class="k">const</span> <span class="k">static</span> <span class="kt">int</span> <span class="n">MY_CONSTANT</span> <span class="o">=</span> <span class="mi">1000</span><span class="p">;</span>
</pre></div>
</div>
</div></blockquote>
</div>
<div class="section" id="member-variables">
<h4>1.8.3. Member variables</h4>
<p>Variables that are members of a class are <strong>under_scored_</strong>, with a trailing
underscore added, e.g.:</p>
<blockquote>
<div><div class="highlight-cpp"><div class="highlight"><pre><span class="kt">int</span> <span class="n">example_int_</span><span class="p">;</span>
</pre></div>
</div>
</div></blockquote>
</div>
</div>
<div class="section" id="return-statements">
<h3>1.9. Return statements</h3>
<p>Return statements should have their values in parentheses, e.g.:</p>
<blockquote>
<div><div class="highlight-cpp"><div class="highlight"><pre><span class="kt">int</span>
<span class="nf">main</span> <span class="p">()</span>
<span class="p">{</span>
  <span class="k">return</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</div>
</div></blockquote>
<div class="line-block">
<div class="line"><br /></div>
</div>
</div>
</div>
<div class="section" id="indentation-and-formatting">
<h2>2. Indentation and Formatting</h2>
<p>The standard indentation for each block in PCL is <strong>2 spaces</strong>. Under no
circumstances, tabs or other spacing measures should be used. PCL uses a
variant of the GNU style formatting.</p>
<div class="section" id="id1">
<h3>2.1. Namespaces</h3>
<p>In a header file, the contets of a namespace should be indented, e.g.:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">namespace</span> <span class="n">pcl</span>
<span class="p">{</span>
  <span class="k">class</span> <span class="nc">Foo</span>
  <span class="p">{</span>
    <span class="p">...</span>
  <span class="p">};</span>
<span class="p">}</span>
</pre></div>
</div>
<p>In an implementation file, the namespace must be added to each individual
method or function definition, e.g.:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">void</span>
<span class="n">pcl</span><span class="o">::</span><span class="n">Foo</span><span class="o">::</span><span class="n">bar</span> <span class="p">()</span>
<span class="p">{</span>
  <span class="p">...</span>
<span class="p">}</span>
</pre></div>
</div>
</div>
<div class="section" id="classes">
<h3>2.2. Classes</h3>
<p>The template parameters of a class should be declared on a different line,
e.g.:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">template</span> <span class="o">&lt;</span><span class="k">typename</span> <span class="n">T</span><span class="o">&gt;</span>
<span class="k">class</span> <span class="nc">Foo</span>
<span class="p">{</span>
  <span class="p">...</span>
<span class="p">}</span>
</pre></div>
</div>
</div>
<div class="section" id="id2">
<h3>2.3. Functions / Methods</h3>
<p>The return type of each function declaration must be placed on a different
line, e.g.:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">void</span>
<span class="nf">bar</span> <span class="p">();</span>
</pre></div>
</div>
<p>Same for the implementation/definition, e.g.:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">void</span>
<span class="nf">bar</span> <span class="p">()</span>
<span class="p">{</span>
  <span class="p">...</span>
<span class="p">}</span>
</pre></div>
</div>
<p>or</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">void</span>
<span class="n">Foo</span><span class="o">::</span><span class="n">bar</span> <span class="p">()</span>
<span class="p">{</span>
  <span class="p">...</span>
<span class="p">}</span>
</pre></div>
</div>
<p>or</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">template</span> <span class="o">&lt;</span><span class="k">typename</span> <span class="n">T</span><span class="o">&gt;</span> <span class="kt">void</span>
<span class="n">Foo</span><span class="o">&lt;</span><span class="n">T</span><span class="o">&gt;::</span><span class="n">bar</span> <span class="p">()</span>
<span class="p">{</span>
  <span class="p">...</span>
<span class="p">}</span>
</pre></div>
</div>
</div>
<div class="section" id="braces">
<h3>2.4. Braces</h3>
<p>Braces, both open and close, go on their own lines, e.g.:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">if</span> <span class="p">(</span><span class="n">a</span> <span class="o">&lt;</span> <span class="n">b</span><span class="p">)</span>
<span class="p">{</span>
  <span class="p">...</span>
<span class="p">}</span>
<span class="k">else</span>
<span class="p">{</span>
  <span class="p">...</span>
<span class="p">}</span>
</pre></div>
</div>
<p>Braces can be omitted if the enclosed block is a single-line statement, e.g.:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">if</span> <span class="p">(</span><span class="n">a</span> <span class="o">&lt;</span> <span class="n">b</span><span class="p">)</span>
  <span class="n">x</span> <span class="o">=</span> <span class="mi">2</span> <span class="o">*</span> <span class="n">a</span><span class="p">;</span>
</pre></div>
</div>
</div>
<div class="section" id="spacing">
<h3>2.5. Spacing</h3>
<p>We&#8217;ll say it again: the standard indentation for each block in PCL is <strong>2
spaces</strong>. We also include a space before the bracketed list of arguments to a
function/method, e.g.:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">int</span>
<span class="nf">exampleMethod</span> <span class="p">(</span><span class="kt">int</span> <span class="n">example_arg</span><span class="p">);</span>
</pre></div>
</div>
<p>If multiple namespaces are declared within header files, always use <strong>2
spaces</strong> to indent them, e.g.:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">namespace</span> <span class="n">foo</span>
<span class="p">{</span>
  <span class="k">namespace</span> <span class="n">bar</span>
  <span class="p">{</span>
     <span class="kt">void</span>
     <span class="n">method</span> <span class="p">(</span><span class="kt">int</span> <span class="n">my_var</span><span class="p">);</span>
   <span class="p">}</span>
<span class="p">}</span>
</pre></div>
</div>
<p>Class and struct members are indented by <strong>2 spaces</strong>. Access qualifiers (public, private and protected) are put at the
indentation level of the class body and members affected by these qualifiers are indented by one more level, i.e. 2 spaces. E.g.:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="k">namespace</span> <span class="n">foo</span>
<span class="p">{</span>
  <span class="k">class</span> <span class="nc">Bar</span>
  <span class="p">{</span>
    <span class="kt">int</span> <span class="n">i</span><span class="p">;</span>
    <span class="nl">public:</span>
      <span class="kt">int</span> <span class="n">j</span><span class="p">;</span>
    <span class="nl">protected:</span>
      <span class="kt">void</span>
      <span class="nf">baz</span> <span class="p">();</span>
  <span class="p">}</span>
<span class="p">}</span>
</pre></div>
</div>
</div>
<div class="section" id="automatic-code-formatting">
<h3>2.6. Automatic code formatting</h3>
<p>The following set of rules can be automatically used by various different IDEs,
editors, etc.</p>
<div class="section" id="emacs">
<h4>2.6.1. Emacs</h4>
<p>You can use the following <a class="reference external" href="http://dev.pointclouds.org/attachments/download/748/pcl-c-style.el">PCL C/C++ style file</a>,
download it to some known location and then:</p>
<ul class="simple">
<li>open .emacs</li>
<li>add the following before any C/C++ custom hooks</li>
</ul>
<div class="highlight-lisp"><div class="highlight"><pre>(load-file &quot;/location/to/pcl-c-style.el&quot;)
(add-hook &#39;c-mode-common-hook &#39;pcl-set-c-style)
</pre></div>
</div>
</div>
<div class="section" id="uncrustify">
<h4>2.6.2. Uncrustify</h4>
<p>You can find a semi-finished config for <a class="reference external" href="http://uncrustify.sourceforge.net/">Uncrustify</a> <a class="reference external" href="http://dev.pointclouds.org/attachments/download/537/uncrustify.cfg">here</a></p>
</div>
<div class="section" id="eclipse">
<h4>2.6.3 Eclipse</h4>
<div class="line-block">
<div class="line">You can find a PCL code style file for Eclipse <a class="reference external" href="https://github.com/PointCloudLibrary/pcl/tree/master/doc/advanced/content/files">on GitHub</a>.</div>
<div class="line">To add the new formatting style go to: Windows &gt; Preferences &gt; C/C++ &gt; Code Style &gt; Formatter</div>
</div>
<div class="line-block">
<div class="line">To format portion of codes, select the code and press Ctrl + Shift + F.</div>
<div class="line">If you want to format the whole code in your project go to the tree and right click on the project: Source &gt; Format.</div>
</div>
<p>Note that the Eclipse formatter style is configured to wrap all arguments in a function, feel free to re-arange the arguments if you feel the need; for example,
this improves readability:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="kt">int</span>
<span class="nf">displayPoint</span> <span class="p">(</span><span class="kt">float</span> <span class="n">x</span><span class="p">,</span> <span class="kt">float</span> <span class="n">y</span><span class="p">,</span> <span class="kt">float</span> <span class="n">z</span><span class="p">,</span>
              <span class="kt">float</span> <span class="n">r</span><span class="p">,</span> <span class="kt">float</span> <span class="n">g</span><span class="p">,</span> <span class="kt">float</span> <span class="n">b</span>
             <span class="p">);</span>
</pre></div>
</div>
<p>This eclipse formatter fails to add a space before brackets when using PCL macros:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="n">PCL_ERROR</span><span class="p">(</span><span class="s">&quot;Text</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
</pre></div>
</div>
<p>should be</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="n">PCL_ERROR</span> <span class="p">(</span><span class="s">&quot;Text</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">);</span>
</pre></div>
</div>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">This style sheet is not perfect, please mention errors on the user mailing list and feel free to patch!</p>
</div>
</div>
</div>
</div>
<div class="section" id="structuring">
<h2>3. Structuring</h2>
<div class="section" id="classes-and-api">
<h3>3.1. Classes and API</h3>
<p>For most classes in PCL, it is preferred that the interface (all public
members) does not contain variables and only two types of methods:</p>
<ul class="simple">
<li>The first method type is the get/set type that allows to manipulate the
parameters and input data used by the class.</li>
<li>The second type of methods is actually performing the class functionality
and produces output, e.g. compute, filter, segment.</li>
</ul>
</div>
<div class="section" id="passing-arguments">
<h3>3.2. Passing arguments</h3>
<p>For get/set type methods the following rules apply:</p>
<ul class="simple">
<li>If large amounts of data needs to be set (usually the case with input data
in PCL) it is preferred to pass a boost shared pointer instead of the actual
data.</li>
<li>Getters always need to pass exactly the same types as their repsective setters
and vice versa.</li>
<li>For getters, if only one argument needs to be passed this will be done via
the return keyword. If two or more arguments need to be passed they will
all be passed by reference instead.</li>
</ul>
<p>For the compute, filter, segment, etc. type methods the following rules apply:</p>
<ul class="simple">
<li>The output arguments are preferably non-pointer type, regardless of data
size.</li>
<li>The output arguments will always be passed by reference.</li>
</ul>
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