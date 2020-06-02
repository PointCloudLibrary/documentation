<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation - Point Cloud Library (PCL)</title>
</head>

<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8" />
    <title>PCL C++ Programming Style Guide</title>
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
            
  <div class="section" id="pcl-c-programming-style-guide">
<span id="pcl-style-guide"></span><h1>PCL C++ Programming Style Guide</h1>
<p>To make sure that all code in PCL is coherent and easily understood by other
developers and users, we follow a set of strict rules that everyone should
adopt. These rules are not to be broken unless there is a very good reason to
do so. Changes to these rules are always possible, but the person proposing and
changing a rule will have the unfortunate task to go and apply the rule change
to all the existing code.</p>
<div class="contents local topic" id="table-of-contents">
<p class="topic-title">Table of Contents</p>
<ul class="simple">
<li><p><a class="reference internal" href="#naming" id="id3">1. Naming</a></p>
<ul>
<li><p><a class="reference internal" href="#files" id="id4">1.1. Files</a></p></li>
<li><p><a class="reference internal" href="#directories" id="id5">1.2. Directories</a></p></li>
<li><p><a class="reference internal" href="#includes" id="id6">1.3. Includes</a></p></li>
<li><p><a class="reference internal" href="#defines-macros" id="id7">1.4. Defines &amp; Macros</a></p></li>
<li><p><a class="reference internal" href="#namespaces" id="id8">1.5. Namespaces</a></p></li>
<li><p><a class="reference internal" href="#classes-structs" id="id9">1.6. Classes / Structs</a></p></li>
<li><p><a class="reference internal" href="#functions-methods" id="id10">1.7. Functions / Methods</a></p></li>
<li><p><a class="reference internal" href="#variables" id="id11">1.8. Variables</a></p>
<ul>
<li><p><a class="reference internal" href="#iterators" id="id12">1.8.1. Iterators</a></p></li>
<li><p><a class="reference internal" href="#constants" id="id13">1.8.2. Constants</a></p></li>
<li><p><a class="reference internal" href="#member-variables" id="id14">1.8.3. Member variables</a></p></li>
</ul>
</li>
<li><p><a class="reference internal" href="#return-statements" id="id15">1.9. Return statements</a></p></li>
</ul>
</li>
<li><p><a class="reference internal" href="#indentation-and-formatting" id="id16">2. Indentation and Formatting</a></p>
<ul>
<li><p><a class="reference internal" href="#id1" id="id17">2.1. Namespaces</a></p></li>
<li><p><a class="reference internal" href="#classes" id="id18">2.2. Classes</a></p></li>
<li><p><a class="reference internal" href="#id2" id="id19">2.3. Functions / Methods</a></p></li>
<li><p><a class="reference internal" href="#braces" id="id20">2.4. Braces</a></p></li>
<li><p><a class="reference internal" href="#spacing" id="id21">2.5. Spacing</a></p></li>
<li><p><a class="reference internal" href="#automatic-code-formatting" id="id22">2.6. Automatic code formatting</a></p>
<ul>
<li><p><a class="reference internal" href="#script-usage" id="id23">2.6.1. Script usage</a></p></li>
</ul>
</li>
</ul>
</li>
<li><p><a class="reference internal" href="#structuring" id="id24">3. Structuring</a></p>
<ul>
<li><p><a class="reference internal" href="#classes-and-api" id="id25">3.1. Classes and API</a></p></li>
<li><p><a class="reference internal" href="#passing-arguments" id="id26">3.2. Passing arguments</a></p></li>
<li><p><a class="reference internal" href="#object-declaration" id="id27">3.3. Object declaration</a></p>
<ul>
<li><p><a class="reference internal" href="#use-of-auto" id="id28">3.3.1 Use of auto</a></p></li>
<li><p><a class="reference internal" href="#type-qualifiers-of-variables" id="id29">3.3.2 Type qualifiers of variables</a></p></li>
</ul>
</li>
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
<li><p>Header files have the extension <strong>.h</strong></p></li>
<li><p>Templated implementation files have the extension <strong>.hpp</strong></p></li>
<li><p>Source files have the extension <strong>.cpp</strong></p></li>
</ul>
</div>
<div class="section" id="directories">
<h3>1.2. Directories</h3>
<p>All directories and subdirectories should be <strong>under_scored</strong>.</p>
<ul class="simple">
<li><p>Header files should go under <strong>include/</strong></p></li>
<li><p>Templated implementation files should go under <strong>include/impl/</strong></p></li>
<li><p>Source files should go under <strong>src/</strong></p></li>
</ul>
</div>
<div class="section" id="includes">
<h3>1.3. Includes</h3>
<p>Include statements are made with <strong>“quotes”</strong> only if the file is in the
same directory, in any other case the include statement is made with
<strong>&lt;chevron_brackets&gt;</strong>, e.g.:</p>
<blockquote>
<div><div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="cp">#include</span> <span class="cpf">&lt;pcl/module_name/file_name.h&gt;</span><span class="cp"></span>
<span class="cp">#incluce &lt;pcl/module_name/impl/file_name.hpp&gt;</span>
</pre></div>
</div>
</div></blockquote>
</div>
<div class="section" id="defines-macros">
<h3>1.4. Defines &amp; Macros</h3>
<p>Macros should all be <strong>ALL_CAPITALS_AND_UNDERSCORED</strong>.</p>
<p>Include guards are not implemented with defines, instead <code class="docutils literal notranslate"><span class="pre">#pragma</span> <span class="pre">once</span></code> should be used.</p>
<blockquote>
<div><div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="c1">// the license</span>

<span class="cp">#pragma once</span>

<span class="c1">// the code</span>
</pre></div>
</div>
</div></blockquote>
</div>
<div class="section" id="namespaces">
<h3>1.5. Namespaces</h3>
<p>Namespaces should be <strong>under_scored</strong>, e.g.:</p>
<blockquote>
<div><div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="k">namespace</span> <span class="n">pcl_io</span>
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
<div><div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="k">class</span> <span class="nc">ExampleClass</span><span class="p">;</span>
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
<div><div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="kt">int</span>
<span class="nf">applyExample</span> <span class="p">(</span><span class="kt">int</span> <span class="n">example_arg</span><span class="p">);</span>
</pre></div>
</div>
</div></blockquote>
</div>
<div class="section" id="variables">
<h3>1.8. Variables</h3>
<p>Variable names should be <strong>under_scored</strong>.</p>
<blockquote>
<div><div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="kt">int</span> <span class="n">my_variable</span><span class="p">;</span>
</pre></div>
</div>
</div></blockquote>
<div class="section" id="iterators">
<h4>1.8.1. Iterators</h4>
<p>Iterator variables should indicate what they’re iterating over, e.g.:</p>
<blockquote>
<div><div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="n">std</span><span class="o">::</span><span class="n">list</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">pid_list</span><span class="p">;</span>
<span class="n">std</span><span class="o">::</span><span class="n">list</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;::</span><span class="n">iterator</span> <span class="n">pid_it</span><span class="p">;</span>
</pre></div>
</div>
</div></blockquote>
</div>
<div class="section" id="constants">
<h4>1.8.2. Constants</h4>
<p>Constants should be <strong>ALL_CAPITALS</strong>, e.g.:</p>
<blockquote>
<div><div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="k">const</span> <span class="k">static</span> <span class="kt">int</span> <span class="n">MY_CONSTANT</span> <span class="o">=</span> <span class="mi">1000</span><span class="p">;</span>
</pre></div>
</div>
</div></blockquote>
</div>
<div class="section" id="member-variables">
<h4>1.8.3. Member variables</h4>
<p>Variables that are members of a class are <strong>under_scored_</strong>, with a trailing
underscore added, e.g.:</p>
<blockquote>
<div><div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="kt">int</span> <span class="n">example_int_</span><span class="p">;</span>
</pre></div>
</div>
</div></blockquote>
</div>
</div>
<div class="section" id="return-statements">
<h3>1.9. Return statements</h3>
<p>Return statements should have their values in parentheses, e.g.:</p>
<blockquote>
<div><div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="kt">int</span>
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
<p>In both header and implementation files, namespaces are to be explicitly
declared, and their contents should not be indented, like clang-format
enforces in the Formatting CI job, e.g.:</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="k">namespace</span> <span class="n">pcl</span>
<span class="p">{</span>

<span class="k">class</span> <span class="nc">Foo</span>
<span class="p">{</span>
  <span class="p">...</span>
<span class="p">};</span>

<span class="p">}</span>
</pre></div>
</div>
</div>
<div class="section" id="classes">
<h3>2.2. Classes</h3>
<p>The template parameters of a class should be declared on a different line,
e.g.:</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="k">template</span> <span class="o">&lt;</span><span class="k">typename</span> <span class="n">T</span><span class="o">&gt;</span>
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
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="kt">void</span>
<span class="nf">bar</span> <span class="p">();</span>
</pre></div>
</div>
<p>Same for the implementation/definition, e.g.:</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="kt">void</span>
<span class="nf">bar</span> <span class="p">()</span>
<span class="p">{</span>
  <span class="p">...</span>
<span class="p">}</span>
</pre></div>
</div>
<p>or</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="kt">void</span>
<span class="n">Foo</span><span class="o">::</span><span class="n">bar</span> <span class="p">()</span>
<span class="p">{</span>
  <span class="p">...</span>
<span class="p">}</span>
</pre></div>
</div>
<p>or</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="k">template</span> <span class="o">&lt;</span><span class="k">typename</span> <span class="n">T</span><span class="o">&gt;</span> <span class="kt">void</span>
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
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="k">if</span> <span class="p">(</span><span class="n">a</span> <span class="o">&lt;</span> <span class="n">b</span><span class="p">)</span>
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
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="k">if</span> <span class="p">(</span><span class="n">a</span> <span class="o">&lt;</span> <span class="n">b</span><span class="p">)</span>
  <span class="n">x</span> <span class="o">=</span> <span class="mi">2</span> <span class="o">*</span> <span class="n">a</span><span class="p">;</span>
</pre></div>
</div>
</div>
<div class="section" id="spacing">
<h3>2.5. Spacing</h3>
<p>We’ll say it again: the standard indentation for each block in PCL is <strong>2
spaces</strong>. We also include a space before the bracketed list of arguments to a
function/method, e.g.:</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="kt">int</span>
<span class="nf">exampleMethod</span> <span class="p">(</span><span class="kt">int</span> <span class="n">example_arg</span><span class="p">);</span>
</pre></div>
</div>
<p>Class and struct members are indented by <strong>2 spaces</strong>. Access qualifiers (public, private and protected) are put at the
indentation level of the class body and members affected by these qualifiers are indented by one more level, i.e. 2 spaces. E.g.:</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="k">namespace</span> <span class="n">foo</span>
<span class="p">{</span>

<span class="k">class</span> <span class="nc">Bar</span>
<span class="p">{</span>
  <span class="kt">int</span> <span class="n">i</span><span class="p">;</span>
  <span class="k">public</span><span class="o">:</span>
    <span class="kt">int</span> <span class="n">j</span><span class="p">;</span>
  <span class="k">protected</span><span class="o">:</span>
    <span class="kt">void</span>
    <span class="n">baz</span> <span class="p">();</span>
<span class="p">};</span>
<span class="p">}</span>
</pre></div>
</div>
</div>
<div class="section" id="automatic-code-formatting">
<h3>2.6. Automatic code formatting</h3>
<p>We currently use clang-format-10 as the tool for auto-formatting our C++ code.
Please note that different versions of clang-format can result in slightly different outputs.</p>
<p>The style rules mentioned in this document are enforced via <a class="reference external" href="https://github.com/PointCloudLibrary/pcl/blob/master/.clang-format">PCL’s .clang-format file</a>.
The style files which were previously distributed should now be considered deprecated.</p>
<p>For the integration of clang-format with various text editors and IDE’s, refer to this <a class="reference external" href="https://clang.llvm.org/docs/ClangFormat.html">page</a>.</p>
<p>Details about the style options used can be found <a class="reference external" href="https://clang.llvm.org/docs/ClangFormatStyleOptions.html">here</a>.</p>
<div class="section" id="script-usage">
<h4>2.6.1. Script usage</h4>
<p>PCL also creates a build target ‘format’ to format the whitelisted directories using clang-format.</p>
<p>Command line usage:</p>
<div class="highlight-shell notranslate"><div class="highlight"><pre><span></span>$ make format
</pre></div>
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
<li><p>The first method type is the get/set type that allows to manipulate the
parameters and input data used by the class.</p></li>
<li><p>The second type of methods is actually performing the class functionality
and produces output, e.g. compute, filter, segment.</p></li>
</ul>
</div>
<div class="section" id="passing-arguments">
<h3>3.2. Passing arguments</h3>
<p>For get/set type methods the following rules apply:</p>
<ul class="simple">
<li><p>If large amounts of data needs to be set (usually the case with input data
in PCL) it is preferred to pass a boost shared pointer instead of the actual
data.</p></li>
<li><p>Getters always need to pass exactly the same types as their repsective setters
and vice versa.</p></li>
<li><p>For getters, if only one argument needs to be passed this will be done via
the return keyword. If two or more arguments need to be passed they will
all be passed by reference instead.</p></li>
</ul>
<p>For the compute, filter, segment, etc. type methods the following rules apply:</p>
<ul class="simple">
<li><p>The output arguments are preferably non-pointer type, regardless of data
size.</p></li>
<li><p>The output arguments will always be passed by reference.</p></li>
</ul>
</div>
<div class="section" id="object-declaration">
<h3>3.3. Object declaration</h3>
<div class="section" id="use-of-auto">
<h4>3.3.1 Use of auto</h4>
<ul class="simple">
<li><p>For Iterators auto must be used as much as possible</p></li>
<li><p>In all the other cases auto can be used at the author’s discretion</p></li>
<li><p>Use const auto references by default in range loops. Drop the const if the item needs to be modified.</p></li>
</ul>
</div>
<div class="section" id="type-qualifiers-of-variables">
<h4>3.3.2 Type qualifiers of variables</h4>
<ul class="simple">
<li><p>Declare variables const when they don’t need to be modified.</p></li>
<li><p>Use const references whenever you don’t need a copy of the variable.</p></li>
<li><p>Use of unsigned variables if the value is sure to not go negative by
use and by definition of the variable</p></li>
</ul>
</div>
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