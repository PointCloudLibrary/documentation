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
    
    <title>Writing a new PCL class</title>
    
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
            
  <div class="section" id="writing-a-new-pcl-class">
<span id="writing-new-classes"></span><h1><a class="toc-backref" href="#id6">Writing a new PCL class</a></h1>
<p>Converting code to a PCL-like mentality/syntax for someone that comes in
contact for the first time with our infrastructure might appear difficult, or
raise certain questions.</p>
<p>This short guide is to serve as both a HowTo and a FAQ for writing new PCL
classes, either from scratch, or by adapting old code.</p>
<p>Besides converting your code, this guide also explains some of the advantages
of contributing your code to an already existing open source project. Here, we
advocate for PCL, but you can certainly apply the same ideology to other
similar projects.</p>
<div class="contents topic" id="contents">
<p class="topic-title first">Contents</p>
<ul class="simple">
<li><a class="reference internal" href="#writing-a-new-pcl-class" id="id6">Writing a new PCL class</a></li>
<li><a class="reference internal" href="#advantages-why-contribute" id="id7">Advantages: Why contribute?</a></li>
<li><a class="reference internal" href="#example-a-bilateral-filter" id="id8">Example: a bilateral filter</a></li>
<li><a class="reference internal" href="#setting-up-the-structure" id="id9">Setting up the structure</a><ul>
<li><a class="reference internal" href="#bilateral-h" id="id10">bilateral.h</a></li>
<li><a class="reference internal" href="#bilateral-hpp" id="id11">bilateral.hpp</a></li>
<li><a class="reference internal" href="#bilateral-cpp" id="id12">bilateral.cpp</a></li>
<li><a class="reference internal" href="#cmakelists-txt" id="id13">CMakeLists.txt</a></li>
</ul>
</li>
<li><a class="reference internal" href="#filling-in-the-class-structure" id="id14">Filling in the class structure</a><ul>
<li><a class="reference internal" href="#id3" id="id15">bilateral.cpp</a></li>
<li><a class="reference internal" href="#id4" id="id16">bilateral.h</a></li>
<li><a class="reference internal" href="#id5" id="id17">bilateral.hpp</a></li>
</ul>
</li>
<li><a class="reference internal" href="#taking-advantage-of-other-pcl-concepts" id="id18">Taking advantage of other PCL concepts</a><ul>
<li><a class="reference internal" href="#point-indices" id="id19">Point indices</a></li>
<li><a class="reference internal" href="#licenses" id="id20">Licenses</a></li>
<li><a class="reference internal" href="#proper-naming" id="id21">Proper naming</a></li>
<li><a class="reference internal" href="#code-comments" id="id22">Code comments</a></li>
</ul>
</li>
<li><a class="reference internal" href="#testing-the-new-class" id="id23">Testing the new class</a></li>
</ul>
</div>
</div>
<div class="section" id="advantages-why-contribute">
<h1><a class="toc-backref" href="#id7">Advantages: Why contribute?</a></h1>
<p>The first question that someone might ask and we would like to answer is:</p>
<p><em>Why contribute to PCL, as in what are its advantages?</em></p>
<p>This question assumes you&#8217;ve already identified that the set of tools and
libraries that PCL has to offer are useful for your project, so you have already
become an <em>user</em>.</p>
<p>Because open source projects are mostly voluntary efforts, usually with
developers geographically distributed around the world, it&#8217;s very common that
the development process has a certain <em>incremental</em>, and <em>iterative</em> flavor.
This means that:</p>
<blockquote>
<div><ul class="simple">
<li>it&#8217;s impossible for developers to think ahead of all the possible uses a new
piece of code they write might have, but also...</li>
<li>figuring out solutions for corner cases and applications where bugs might
occur is hard, and might not be desirable to tackle at the beginning, due to
limited resources (mostly a cost function of free time).</li>
</ul>
</div></blockquote>
<p>In both cases, everyone has definitely encountered situations where either an
algorithm/method that they need is missing, or an existing one is buggy.
Therefore the next natural step is obvious:</p>
<p><em>change the existing code to fit your application/problem</em>.</p>
<p>While we&#8217;re going to discuss how to do that in the next sections, we would
still like to provide an answer for the first question that we raised, namely
&#8220;why contribute?&#8221;.</p>
<p>In our opinion, there are many advantages. To quote Eric Raymond&#8217;s <em>Linus&#8217;s
Law</em>: <strong>&#8220;given enough eyeballs, all bugs are shallow&#8221;</strong>. What this means is
that by opening your code to the world, and allowing others to see it, the
chances of it getting fixed and optimized are higher, especially in the
presence of a dynamic community such as the one that PCL has.</p>
<p>In addition to the above, your contribution might enable, amongst many things:</p>
<blockquote>
<div><ul class="simple">
<li>others to create new work based on your code;</li>
<li>you to learn about new uses (e.g., thinks that you haven&#8217;t thought it could be used when you designed it);</li>
<li>worry-free maintainership (e.g., you can go away for some time, and then return and see your code still working. Others will take care of adapting it to the newest platforms, newest compilers, etc);</li>
<li>your reputation in the community to grow - everyone likes free stuff (!).</li>
</ul>
</div></blockquote>
<p>For most of us, all of the above apply. For others, only some (your mileage
might vary).</p>
</div>
<div class="section" id="example-a-bilateral-filter">
<span id="bilateral-filter-example"></span><h1><a class="toc-backref" href="#id8">Example: a bilateral filter</a></h1>
<p>To illustrate the code conversion process, we selected the following example:
apply a bilateral filter over intensity data from a given input point cloud,
and save the results to disk.</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
 2
 3
 4
 5
 6
 7
 8
 9
10
11
12
13
14
15
16
17
18
19
20
21
22
23
24
25
26
27
28
29
30
31
32
33
34
35
36
37
38
39
40
41
42
43
44
45
46
47
48
49
50
51
52
53
54
55
56
57
58
59
60
61
62
63
64
65
66</pre></div></td><td class="code"><div class="highlight"><pre> #include &lt;pcl/point_types.h&gt;
 #include &lt;pcl/io/pcd_io.h&gt;
 #include &lt;pcl/kdtree/kdtree_flann.h&gt;

 typedef pcl::PointXYZI PointT;

 float
 G (float x, float sigma)
 {
   return exp (- (x*x)/(2*sigma*sigma));
 }

 int
 main (int argc, char *argv[])
 {
   std::string incloudfile = argv[1];
   std::string outcloudfile = argv[2];
   float sigma_s = atof (argv[3]);
   float sigma_r = atof (argv[4]);

   // Load cloud
   pcl::PointCloud&lt;PointT&gt;::Ptr cloud (new pcl::PointCloud&lt;PointT&gt;);
   pcl::io::loadPCDFile (incloudfile.c_str (), *cloud);
   int pnumber = (int)cloud-&gt;size ();

   // Output Cloud = Input Cloud
   pcl::PointCloud&lt;PointT&gt; outcloud = *cloud;

   // Set up KDTree
   pcl::KdTreeFLANN&lt;PointT&gt;::Ptr tree (new pcl::KdTreeFLANN&lt;PointT&gt;);
   tree-&gt;setInputCloud (cloud);

   // Neighbors containers
   std::vector&lt;int&gt; k_indices;
   std::vector&lt;float&gt; k_distances;

   // Main Loop
   for (int point_id = 0; point_id &lt; pnumber; ++point_id)
   {
     float BF = 0;
     float W = 0;

     tree-&gt;radiusSearch (point_id, 2 * sigma_s, k_indices, k_distances);

     // For each neighbor
     for (size_t n_id = 0; n_id &lt; k_indices.size (); ++n_id)
     {
       float id = k_indices.at (n_id);
       float dist = sqrt (k_distances.at (n_id));
       float intensity_dist = abs (cloud-&gt;points[point_id].intensity - cloud-&gt;points[id].intensity);

       float w_a = G (dist, sigma_s);
       float w_b = G (intensity_dist, sigma_r);
       float weight = w_a * w_b;

       BF += weight * cloud-&gt;points[id].intensity;
       W += weight;
     }

     outcloud.points[point_id].intensity = BF / W;
   }

   // Save filtered output
   pcl::io::savePCDFile (outcloudfile.c_str (), outcloud);
   return (0);
 }
</pre></div>
</td></tr></table></div>
<dl class="docutils">
<dt>The presented code snippet contains:</dt>
<dd><ul class="first last simple">
<li>an I/O component: lines 21-27 (reading data from disk), and 64 (writing data to disk)</li>
<li>an initialization component: lines 29-35 (setting up a search method for nearest neighbors using a KdTree)</li>
<li>the actual algorithmic component: lines 7-11 and 37-61</li>
</ul>
</dd>
</dl>
<p>Our goal here is to convert the algorithm given into an useful PCL class so that it can be reused elsewhere.</p>
</div>
<div class="section" id="setting-up-the-structure">
<h1><a class="toc-backref" href="#id9">Setting up the structure</a></h1>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">If you&#8217;re not familiar with the PCL file structure already, please go ahead
and read the <a class="reference external" href="http://www.pointclouds.org/documentation/advanced/pcl_style_guide.php">PCL C++ Programming Style Guide</a> to
familiarize yourself with the concepts.</p>
</div>
<p>There&#8217;s two different ways we could set up the structure: i) set up the code
separately, as a standalone PCL class, but outside of the PCL code tree; or ii)
set up the files directly in the PCL code tree. Since our assumption is that
the end result will be contributed back to PCL, it&#8217;s best to concentrate on the
latter, also because it is a bit more complex (i.e., it involves a few
additional steps). You can obviously repeat these steps with the former case as
well, with the exception that you don&#8217;t need the files copied in the PCL tree,
nor you need the fancier <em>cmake</em> logic.</p>
<p>Assuming that we want the new algorithm to be part of the PCL Filtering library, we will begin by creating 3 different files under filters:</p>
<blockquote>
<div><ul class="simple">
<li><em>include/pcl/filters/bilateral.h</em> - will contain all definitions;</li>
<li><em>include/pcl/filters/impl/bilateral.hpp</em> - will contain the templated implementations;</li>
<li><em>src/bilateral.cpp</em> - will contain the explicit template instantiations <a class="footnote-reference" href="#id2" id="id1">[*]</a>.</li>
</ul>
</div></blockquote>
<p>We also need a name for our new class. Let&#8217;s call it <cite>BilateralFilter</cite>.</p>
<table class="docutils footnote" frame="void" id="id2" rules="none">
<colgroup><col class="label" /><col /></colgroup>
<tbody valign="top">
<tr><td class="label"><a class="fn-backref" href="#id1">[*]</a></td><td>The PCL Filtering API specifies that two definitions and implementations must be available for every algorithm: one operating on PointCloud&lt;T&gt; and another one operating on PCLPointCloud2. For the purpose of this tutorial, we will concentrate only on the former.</td></tr>
</tbody>
</table>
<div class="section" id="bilateral-h">
<h2><a class="toc-backref" href="#id10">bilateral.h</a></h2>
<p>As previously mentioned, the <em>bilateral.h</em> header file will contain all the
definitions pertinent to the <cite>BilateralFilter</cite> class. Here&#8217;s a minimal
skeleton:</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
 2
 3
 4
 5
 6
 7
 8
 9
10
11
12
13
14</pre></div></td><td class="code"><div class="highlight"><pre> #ifndef PCL_FILTERS_BILATERAL_H_
 #define PCL_FILTERS_BILATERAL_H_

 #include &lt;pcl/filters/filter.h&gt;

 namespace pcl
 {
   template&lt;typename PointT&gt;
   class BilateralFilter : public Filter&lt;PointT&gt;
   {
   };
 }

 #endif // PCL_FILTERS_BILATERAL_H_
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="bilateral-hpp">
<h2><a class="toc-backref" href="#id11">bilateral.hpp</a></h2>
<p>While we&#8217;re at it, let&#8217;s set up two skeleton <em>bilateral.hpp</em> and
<em>bilateral.cpp</em> files as well. First, <em>bilateral.hpp</em>:</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre>1
2
3
4
5
6</pre></div></td><td class="code"><div class="highlight"><pre> #ifndef PCL_FILTERS_BILATERAL_IMPL_H_
 #define PCL_FILTERS_BILATERAL_IMPL_H_

 #include &lt;pcl/filters/bilateral.h&gt;

 #endif // PCL_FILTERS_BILATERAL_H_
</pre></div>
</td></tr></table></div>
<p>This should be straightforward. We haven&#8217;t declared any methods for
<cite>BilateralFilter</cite> yet, therefore there is no implementation.</p>
</div>
<div class="section" id="bilateral-cpp">
<h2><a class="toc-backref" href="#id12">bilateral.cpp</a></h2>
<p>Let&#8217;s write <em>bilateral.cpp</em> too:</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre>1
2</pre></div></td><td class="code"><div class="highlight"><pre> #include &lt;pcl/filters/bilateral.h&gt;
 #include &lt;pcl/filters/impl/bilateral.hpp&gt;
</pre></div>
</td></tr></table></div>
<p>Because we are writing templated code in PCL (1.x) where the template parameter
is a point type (see <a class="reference internal" href="adding_custom_ptype.php#adding-custom-ptype"><em>Adding your own custom PointT type</em></a>), we want to explicitely
instantiate the most common use cases in <em>bilateral.cpp</em>, so that users don&#8217;t
have to spend extra cycles when compiling code that uses our
<cite>BilateralFilter</cite>. To do this, we need to access both the header
(<em>bilateral.h</em>) and the implementations (<em>bilateral.hpp</em>).</p>
</div>
<div class="section" id="cmakelists-txt">
<h2><a class="toc-backref" href="#id13">CMakeLists.txt</a></h2>
<p>Let&#8217;s add all the files to the PCL Filtering <em>CMakeLists.txt</em> file, so we can
enable the build.</p>
<div class="highlight-cmake"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
 2
 3
 4
 5
 6
 7
 8
 9
10
11
12
13
14
15
16
17
18
19
20</pre></div></td><td class="code"><div class="highlight"><pre> # Find &quot;set (srcs&quot;, and add a new entry there, e.g.,
 set (srcs
      src/conditional_removal.cpp
      # ...
      src/bilateral.cpp)
      )

 # Find &quot;set (incs&quot;, and add a new entry there, e.g.,
 set (incs
      include pcl/${SUBSYS_NAME}/conditional_removal.h
      # ...
      include pcl/${SUBSYS_NAME}/bilateral.h
      )

 # Find &quot;set (impl_incs&quot;, and add a new entry there, e.g.,
 set (impl_incs
      include/pcl/${SUBSYS_NAME}/impl/conditional_removal.hpp
      # ...
      include/pcl/${SUBSYS_NAME}/impl/bilateral.hpp
      )
</pre></div>
</td></tr></table></div>
</div>
</div>
<div class="section" id="filling-in-the-class-structure">
<span id="filling"></span><h1><a class="toc-backref" href="#id14">Filling in the class structure</a></h1>
<p>If you correctly edited all the files above, recompiling PCL using the new
filter classes in place should work without problems. In this section, we&#8217;ll
begin filling in the actual code in each file. Let&#8217;s start with the
<em>bilateral.cpp</em> file, as its content is the shortest.</p>
<div class="section" id="id3">
<h2><a class="toc-backref" href="#id15">bilateral.cpp</a></h2>
<p>As previously mentioned, we&#8217;re going to explicitely instantiate and
<em>precompile</em> a number of templated specializations for the <cite>BilateralFilter</cite>
class. While this might lead to an increased compilation time for the PCL
Filtering library, it will save users the pain of processing and compiling the
templates on their end, when they use the class in code they write. The
simplest possible way to do this would be to declare each instance that we want
to precompile by hand in the <em>bilateral.cpp</em> file as follows:</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre>1
2
3
4
5
6
7
8</pre></div></td><td class="code"><div class="highlight"><pre> #include &lt;pcl/point_types.h&gt;
 #include &lt;pcl/filters/bilateral.h&gt;
 #include &lt;pcl/filters/impl/bilateral.hpp&gt;

 template class PCL_EXPORTS pcl::BilateralFilter&lt;pcl::PointXYZ&gt;;
 template class PCL_EXPORTS pcl::BilateralFilter&lt;pcl::PointXYZI&gt;;
 template class PCL_EXPORTS pcl::BilateralFilter&lt;pcl::PointXYZRGB&gt;;
 // ...
</pre></div>
</td></tr></table></div>
<p>However, this becomes cumbersome really fast, as the number of point types PCL
supports grows. Maintaining this list up to date in multiple files in PCL is
also painful. Therefore, we are going to use a special macro called
<cite>PCL_INSTANTIATE</cite> and change the above code as follows:</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre>1
2
3
4
5
6</pre></div></td><td class="code"><div class="highlight"><pre> #include &lt;pcl/point_types.h&gt;
 #include &lt;pcl/impl/instantiate.hpp&gt;
 #include &lt;pcl/filters/bilateral.h&gt;
 #include &lt;pcl/filters/impl/bilateral.hpp&gt;

 PCL_INSTANTIATE(BilateralFilter, PCL_XYZ_POINT_TYPES);
</pre></div>
</td></tr></table></div>
<p>This example, will instantiate a <cite>BilateralFilter</cite> for all XYZ point types
defined in the <em>point_types.h</em> file (see
<span>PCL_XYZ_POINT_TYPES</span> for more information).</p>
<p>By looking closer at the code presented in <a class="reference internal" href="#bilateral-filter-example"><em>Example: a bilateral filter</em></a>, we
notice constructs such as <cite>cloud-&gt;points[point_id].intensity</cite>. This indicates
that our filter expects the presence of an <strong>intensity</strong> field in the point
type. Because of this, using <strong>PCL_XYZ_POINT_TYPES</strong> won&#8217;t work, as not all the
types defined there have intensity data present. In fact, it&#8217;s easy to notice
that only two of the types contain intensity, namely:
<a class="reference external" href="http://docs.pointclouds.org/trunk/structpcl_1_1_point_x_y_z_i.html">PointXYZI</a> and
<a class="reference external" href="http://docs.pointclouds.org/trunk/structpcl_1_1_point_x_y_z_i_normal.html">PointXYZINormal</a>. We therefore replace
<strong>PCL_XYZ_POINT_TYPES</strong> and the final <em>bilateral.cpp</em> file becomes:</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre>1
2
3
4
5
6</pre></div></td><td class="code"><div class="highlight"><pre> #include &lt;pcl/point_types.h&gt;
 #include &lt;pcl/impl/instantiate.hpp&gt;
 #include &lt;pcl/filters/bilateral.h&gt;
 #include &lt;pcl/filters/impl/bilateral.hpp&gt;

 PCL_INSTANTIATE(BilateralFilter, (pcl::PointXYZI)(pcl::PointXYZINormal));
</pre></div>
</td></tr></table></div>
<p>Note that at this point we haven&#8217;t declared the PCL_INSTANTIATE template for
<cite>BilateralFilter</cite>, nor did we actually implement the pure virtual functions in
the abstract class <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_filter.html">pcl::Filter</a> so attemping to compile the
code will result in errors like:</p>
<div class="highlight-python"><div class="highlight"><pre>filters/src/bilateral.cpp:6:32: error: expected constructor, destructor, or type conversion before ‘(’ token
</pre></div>
</div>
</div>
<div class="section" id="id4">
<h2><a class="toc-backref" href="#id16">bilateral.h</a></h2>
<p>We begin filling the <cite>BilateralFilter</cite> class by first declaring the
constructor, and its member variables. Because the bilateral filtering
algorithm has two parameters, we will store these as class members, and
implement setters and getters for them, to be compatible with the PCL 1.x API
paradigms.</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
 2
 3
 4
 5
 6
 7
 8
 9
10
11
12
13
14
15
16
17
18
19
20
21
22
23
24
25
26
27
28
29
30
31
32
33
34
35
36
37
38
39
40
41
42
43</pre></div></td><td class="code"><div class="highlight"><pre> ...
 namespace pcl
 {
   template&lt;typename PointT&gt;
   class BilateralFilter : public Filter&lt;PointT&gt;
   {
     public:
       BilateralFilter () : sigma_s_ (0),
                            sigma_r_ (std::numeric_limits&lt;double&gt;::max ())
       {
       }

       void
       setSigmaS (const double sigma_s)
       {
         sigma_s_ = sigma_s;
       }

       double
       getSigmaS ()
       {
         return (sigma_s_);
       }

       void
       setSigmaR (const double sigma_r)
       {
         sigma_r_ = sigma_r;
       }

       double
       getSigmaR ()
       {
         return (sigma_r_);
       }

     private:
       double sigma_s_;
       double sigma_r_;
   };
 }

 #endif // PCL_FILTERS_BILATERAL_H_
</pre></div>
</td></tr></table></div>
<p>Nothing out of the ordinary so far, except maybe lines 8-9, where we gave some
default values to the two parameters. Because our class inherits from
<a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_filter.html">pcl::Filter</a>, and that inherits from
<a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_p_c_l_base.html">pcl::PCLBase</a>, we can make use of the
<span>setInputCloud</span> method to pass the input data
to our algorithm (stored as <span>input_</span>). We therefore
add an <cite>using</cite> declaration as follows:</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre>1
2
3
4
5
6
7
8</pre></div></td><td class="code"><div class="highlight"><pre> <span class="p">...</span>
   <span class="k">template</span><span class="o">&lt;</span><span class="k">typename</span> <span class="n">PointT</span><span class="o">&gt;</span>
   <span class="k">class</span> <span class="nc">BilateralFilter</span> <span class="o">:</span> <span class="k">public</span> <span class="n">Filter</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span>
   <span class="p">{</span>
     <span class="k">using</span> <span class="n">Filter</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;::</span><span class="n">input_</span><span class="p">;</span>
     <span class="nl">public:</span>
       <span class="n">BilateralFilter</span> <span class="p">()</span> <span class="o">:</span> <span class="n">sigma_s_</span> <span class="p">(</span><span class="mi">0</span><span class="p">),</span>
 <span class="p">...</span>
</pre></div>
</td></tr></table></div>
<p>This will make sure that our class has access to the member variable <cite>input_</cite>
without typing the entire construct. Next, we observe that each class that
inherits from <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_filter.html">pcl::Filter</a> must inherit a
<span>applyFilter</span> method. We therefore define:</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
 2
 3
 4
 5
 6
 7
 8
 9
10
11
12
13</pre></div></td><td class="code"><div class="highlight"><pre> <span class="p">...</span>
     <span class="k">using</span> <span class="n">Filter</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;::</span><span class="n">input_</span><span class="p">;</span>
     <span class="k">typedef</span> <span class="k">typename</span> <span class="n">Filter</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;::</span><span class="n">PointCloud</span> <span class="n">PointCloud</span><span class="p">;</span>

     <span class="nl">public:</span>
       <span class="n">BilateralFilter</span> <span class="p">()</span> <span class="o">:</span> <span class="n">sigma_s_</span> <span class="p">(</span><span class="mi">0</span><span class="p">),</span>
                            <span class="n">sigma_r_</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="n">numeric_limits</span><span class="o">&lt;</span><span class="kt">double</span><span class="o">&gt;::</span><span class="n">max</span> <span class="p">())</span>
       <span class="p">{</span>
       <span class="p">}</span>

       <span class="kt">void</span>
       <span class="n">applyFilter</span> <span class="p">(</span><span class="n">PointCloud</span> <span class="o">&amp;</span><span class="n">output</span><span class="p">);</span>
 <span class="p">...</span>
</pre></div>
</td></tr></table></div>
<p>The implementation of <cite>applyFilter</cite> will be given in the <em>bilateral.hpp</em> file
later. Line 3 constructs a typedef so that we can use the type <cite>PointCloud</cite>
without typing the entire construct.</p>
<p>Looking at the original code from section <a class="reference internal" href="#bilateral-filter-example"><em>Example: a bilateral filter</em></a>, we
notice that the algorithm consists of applying the same operation to every
point in the cloud. To keep the <cite>applyFilter</cite> call clean, we therefore define
method called <cite>computePointWeight</cite> whose implementation will contain the corpus
defined in between lines 45-58:</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre>1
2
3
4
5
6
7</pre></div></td><td class="code"><div class="highlight"><pre> <span class="p">...</span>
       <span class="kt">void</span>
       <span class="n">applyFilter</span> <span class="p">(</span><span class="n">PointCloud</span> <span class="o">&amp;</span><span class="n">output</span><span class="p">);</span>

       <span class="kt">double</span>
       <span class="nf">computePointWeight</span> <span class="p">(</span><span class="k">const</span> <span class="kt">int</span> <span class="n">pid</span><span class="p">,</span> <span class="k">const</span> <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="o">&amp;</span><span class="n">indices</span><span class="p">,</span> <span class="k">const</span> <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="o">&amp;</span><span class="n">distances</span><span class="p">);</span>
 <span class="p">...</span>
</pre></div>
</td></tr></table></div>
<p>In addition, we notice that lines 29-31 and 43 from section
<a class="reference internal" href="#bilateral-filter-example"><em>Example: a bilateral filter</em></a> construct a <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_kd_tree.html">KdTree</a>
structure for obtaining the nearest neighbors for a given point. We therefore
add:</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
 2
 3
 4
 5
 6
 7
 8
 9
10
11
12
13
14
15
16
17
18
19</pre></div></td><td class="code"><div class="highlight"><pre> #include &lt;pcl/kdtree/kdtree.h&gt;
 ...
     using Filter&lt;PointT&gt;::input_;
     typedef typename Filter&lt;PointT&gt;::PointCloud PointCloud;
     typedef typename pcl::KdTree&lt;PointT&gt;::Ptr KdTreePtr;

   public:
 ...

     void
     setSearchMethod (const KdTreePtr &amp;tree)
     {
       tree_ = tree;
     }

   private:
 ...
     KdTreePtr tree_;
 ...
</pre></div>
</td></tr></table></div>
<p>Finally, we would like to add the kernel method (<cite>G (float x, float sigma)</cite>)
inline so that we speed up the computation of the filter. Because the method is
only useful within the context of the algorithm, we will make it private. The
header file becomes:</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
 2
 3
 4
 5
 6
 7
 8
 9
10
11
12
13
14
15
16
17
18
19
20
21
22
23
24
25
26
27
28
29
30
31
32
33
34
35
36
37
38
39
40
41
42
43
44
45
46
47
48
49
50
51
52
53
54
55
56
57
58
59
60
61
62
63
64
65
66
67
68
69
70
71
72
73
74</pre></div></td><td class="code"><div class="highlight"><pre> #ifndef PCL_FILTERS_BILATERAL_H_
 #define PCL_FILTERS_BILATERAL_H_

 #include &lt;pcl/filters/filter.h&gt;
 #include &lt;pcl/kdtree/kdtree.h&gt;

 namespace pcl
 {
   template&lt;typename PointT&gt;
   class BilateralFilter : public Filter&lt;PointT&gt;
   {
     using Filter&lt;PointT&gt;::input_;
     typedef typename Filter&lt;PointT&gt;::PointCloud PointCloud;
     typedef typename pcl::KdTree&lt;PointT&gt;::Ptr KdTreePtr;

     public:
       BilateralFilter () : sigma_s_ (0),
                            sigma_r_ (std::numeric_limits&lt;double&gt;::max ())
       {
       }


       void
       applyFilter (PointCloud &amp;output);

       double
       computePointWeight (const int pid, const std::vector&lt;int&gt; &amp;indices, const std::vector&lt;float&gt; &amp;distances);

       void
       setSigmaS (const double sigma_s)
       {
         sigma_s_ = sigma_s;
       }

       double
       getSigmaS ()
       {
         return (sigma_s_);
       }

       void
       setSigmaR (const double sigma_r)
       {
         sigma_r_ = sigma_r;
       }

       double
       getSigmaR ()
       {
         return (sigma_r_);
       }

       void
       setSearchMethod (const KdTreePtr &amp;tree)
       {
         tree_ = tree;
       }


     private:

       inline double
       kernel (double x, double sigma)
       {
         return (exp (- (x*x)/(2*sigma*sigma)));
       }

       double sigma_s_;
       double sigma_r_;
       KdTreePtr tree_;
   };
 }

 #endif // PCL_FILTERS_BILATERAL_H_
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="id5">
<h2><a class="toc-backref" href="#id17">bilateral.hpp</a></h2>
<p>There&#8217;s two methods that we need to implement here, namely <cite>applyFilter</cite> and
<cite>computePointWeight</cite>.</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
 2
 3
 4
 5
 6
 7
 8
 9
10
11
12
13
14
15
16
17
18
19
20
21
22
23
24
25
26
27
28
29
30
31
32
33
34
35
36
37
38
39
40</pre></div></td><td class="code"><div class="highlight"><pre> <span class="k">template</span> <span class="o">&lt;</span><span class="k">typename</span> <span class="n">PointT</span><span class="o">&gt;</span> <span class="kt">double</span>
 <span class="n">pcl</span><span class="o">::</span><span class="n">BilateralFilter</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;::</span><span class="n">computePointWeight</span> <span class="p">(</span><span class="k">const</span> <span class="kt">int</span> <span class="n">pid</span><span class="p">,</span>
                                                   <span class="k">const</span> <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="o">&amp;</span><span class="n">indices</span><span class="p">,</span>
                                                   <span class="k">const</span> <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="o">&amp;</span><span class="n">distances</span><span class="p">)</span>
 <span class="p">{</span>
   <span class="kt">double</span> <span class="n">BF</span> <span class="o">=</span> <span class="mi">0</span><span class="p">,</span> <span class="n">W</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>

   <span class="c1">// For each neighbor</span>
   <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">n_id</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">n_id</span> <span class="o">&lt;</span> <span class="n">indices</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">n_id</span><span class="p">)</span>
   <span class="p">{</span>
     <span class="kt">double</span> <span class="n">id</span> <span class="o">=</span> <span class="n">indices</span><span class="p">[</span><span class="n">n_id</span><span class="p">];</span>
     <span class="kt">double</span> <span class="n">dist</span> <span class="o">=</span> <span class="n">std</span><span class="o">::</span><span class="n">sqrt</span> <span class="p">(</span><span class="n">distances</span><span class="p">[</span><span class="n">n_id</span><span class="p">]);</span>
     <span class="kt">double</span> <span class="n">intensity_dist</span> <span class="o">=</span> <span class="n">abs</span> <span class="p">(</span><span class="n">input_</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">pid</span><span class="p">].</span><span class="n">intensity</span> <span class="o">-</span> <span class="n">input_</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">id</span><span class="p">].</span><span class="n">intensity</span><span class="p">);</span>

     <span class="kt">double</span> <span class="n">weight</span> <span class="o">=</span> <span class="n">kernel</span> <span class="p">(</span><span class="n">dist</span><span class="p">,</span> <span class="n">sigma_s_</span><span class="p">)</span> <span class="o">*</span> <span class="n">kernel</span> <span class="p">(</span><span class="n">intensity_dist</span><span class="p">,</span> <span class="n">sigma_r_</span><span class="p">);</span>

     <span class="n">BF</span> <span class="o">+=</span> <span class="n">weight</span> <span class="o">*</span> <span class="n">input_</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">id</span><span class="p">].</span><span class="n">intensity</span><span class="p">;</span>
     <span class="n">W</span> <span class="o">+=</span> <span class="n">weight</span><span class="p">;</span>
   <span class="p">}</span>
   <span class="k">return</span> <span class="p">(</span><span class="n">BF</span> <span class="o">/</span> <span class="n">W</span><span class="p">);</span>
 <span class="p">}</span>

 <span class="k">template</span> <span class="o">&lt;</span><span class="k">typename</span> <span class="n">PointT</span><span class="o">&gt;</span> <span class="kt">void</span>
 <span class="n">pcl</span><span class="o">::</span><span class="n">BilateralFilter</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;::</span><span class="n">applyFilter</span> <span class="p">(</span><span class="n">PointCloud</span> <span class="o">&amp;</span><span class="n">output</span><span class="p">)</span>
 <span class="p">{</span>
   <span class="n">tree_</span><span class="o">-&gt;</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">input_</span><span class="p">);</span>

   <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="n">k_indices</span><span class="p">;</span>
   <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;</span> <span class="n">k_distances</span><span class="p">;</span>

   <span class="n">output</span> <span class="o">=</span> <span class="o">*</span><span class="n">input_</span><span class="p">;</span>

   <span class="k">for</span> <span class="p">(</span><span class="kt">size_t</span> <span class="n">point_id</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">point_id</span> <span class="o">&lt;</span> <span class="n">input_</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span> <span class="o">++</span><span class="n">point_id</span><span class="p">)</span>
   <span class="p">{</span>
     <span class="n">tree_</span><span class="o">-&gt;</span><span class="n">radiusSearch</span> <span class="p">(</span><span class="n">point_id</span><span class="p">,</span> <span class="n">sigma_s_</span> <span class="o">*</span> <span class="mi">2</span><span class="p">,</span> <span class="n">k_indices</span><span class="p">,</span> <span class="n">k_distances</span><span class="p">);</span>

     <span class="n">output</span><span class="p">.</span><span class="n">points</span><span class="p">[</span><span class="n">point_id</span><span class="p">].</span><span class="n">intensity</span> <span class="o">=</span> <span class="n">computePointWeight</span> <span class="p">(</span><span class="n">point_id</span><span class="p">,</span> <span class="n">k_indices</span><span class="p">,</span> <span class="n">k_distances</span><span class="p">);</span>
   <span class="p">}</span>

 <span class="p">}</span>
</pre></div>
</td></tr></table></div>
<p>The <cite>computePointWeight</cite> method should be straightforward as it&#8217;s <em>almost
identical</em> to lines 45-58 from section <a class="reference internal" href="#bilateral-filter-example"><em>Example: a bilateral filter</em></a>. We
basically pass in a point index that we want to compute the intensity weight
for, and a set of neighboring points with distances.</p>
<p>In <cite>applyFilter</cite>, we first set the input data in the tree, copy all the input
data into the output, and then proceed at computing the new weighted point
intensities.</p>
<p>Looking back at <a class="reference internal" href="#filling"><em>Filling in the class structure</em></a>, it&#8217;s now time to declare the <cite>PCL_INSTANTIATE</cite>
entry for the class:</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
 2
 3
 4
 5
 6
 7
 8
 9
10</pre></div></td><td class="code"><div class="highlight"><pre> #ifndef PCL_FILTERS_BILATERAL_IMPL_H_
 #define PCL_FILTERS_BILATERAL_IMPL_H_

 #include &lt;pcl/filters/bilateral.h&gt;

 ...

 #define PCL_INSTANTIATE_BilateralFilter(T) template class PCL_EXPORTS pcl::BilateralFilter&lt;T&gt;;

 #endif // PCL_FILTERS_BILATERAL_H_
</pre></div>
</td></tr></table></div>
<p>One additional thing that we can do is error checking on:</p>
<blockquote>
<div><ul class="simple">
<li>whether the two <cite>sigma_s_</cite> and <cite>sigma_r_</cite> parameters have been given;</li>
<li>whether the search method object (i.e., <cite>tree_</cite>) has been set.</li>
</ul>
</div></blockquote>
<p>For the former, we&#8217;re going to check the value of <cite>sigma_s_</cite>, which was set to
a default of 0, and has a critical importance for the behavior of the algorithm
(it basically defines the size of the support region). Therefore, if at the
execution of the code, its value is still 0, we will print an error using the
<span>PCL_ERROR</span> macro, and return.</p>
<p>In the case of the search method, we can either do the same, or be clever and
provide a default option for the user. The best default options are:</p>
<blockquote>
<div><ul class="simple">
<li>use an organized search method via <span>pcl::OrganizedDataIndex</span> if the point cloud is organized;</li>
<li>use a general purpose kdtree via <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_kd_tree_f_l_a_n_n.html">pcl::KdTreeFLANN</a> if the point cloud is unorganized.</li>
</ul>
</div></blockquote>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
 2
 3
 4
 5
 6
 7
 8
 9
10
11
12
13
14
15
16
17
18
19
20
21</pre></div></td><td class="code"><div class="highlight"><pre> #include &lt;pcl/kdtree/kdtree_flann.h&gt;
 #include &lt;pcl/kdtree/organized_data.h&gt;

 ...
 template &lt;typename PointT&gt; void
 pcl::BilateralFilter&lt;PointT&gt;::applyFilter (PointCloud &amp;output)
 {
   if (sigma_s_ == 0)
   {
     PCL_ERROR (&quot;[pcl::BilateralFilter::applyFilter] Need a sigma_s value given before continuing.\n&quot;);
     return;
   }
   if (!tree_)
   {
     if (input_-&gt;isOrganized ())
       tree_.reset (new pcl::OrganizedDataIndex&lt;PointT&gt; ());
     else
       tree_.reset (new pcl::KdTreeFLANN&lt;PointT&gt; (false));
   }
   tree_-&gt;setInputCloud (input_);
 ...
</pre></div>
</td></tr></table></div>
<p>The implementation file header thus becomes:</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
 2
 3
 4
 5
 6
 7
 8
 9
10
11
12
13
14
15
16
17
18
19
20
21
22
23
24
25
26
27
28
29
30
31
32
33
34
35
36
37
38
39
40
41
42
43
44
45
46
47
48
49
50
51
52
53
54
55
56
57
58
59
60
61
62</pre></div></td><td class="code"><div class="highlight"><pre> #ifndef PCL_FILTERS_BILATERAL_IMPL_H_
 #define PCL_FILTERS_BILATERAL_IMPL_H_

 #include &lt;pcl/filters/bilateral.h&gt;
 #include &lt;pcl/kdtree/kdtree_flann.h&gt;
 #include &lt;pcl/kdtree/organized_data.h&gt;

 template &lt;typename PointT&gt; double
 pcl::BilateralFilter&lt;PointT&gt;::computePointWeight (const int pid,
                                                   const std::vector&lt;int&gt; &amp;indices,
                                                   const std::vector&lt;float&gt; &amp;distances)
 {
   double BF = 0, W = 0;

   // For each neighbor
   for (size_t n_id = 0; n_id &lt; indices.size (); ++n_id)
   {
     double id = indices[n_id];
     double dist = std::sqrt (distances[n_id]);
     double intensity_dist = abs (input_-&gt;points[pid].intensity - input_-&gt;points[id].intensity);

     double weight = kernel (dist, sigma_s_) * kernel (intensity_dist, sigma_r_);

     BF += weight * input_-&gt;points[id].intensity;
     W += weight;
   }
   return (BF / W);
 }

 template &lt;typename PointT&gt; void
 pcl::BilateralFilter&lt;PointT&gt;::applyFilter (PointCloud &amp;output)
 {
   if (sigma_s_ == 0)
   {
     PCL_ERROR (&quot;[pcl::BilateralFilter::applyFilter] Need a sigma_s value given before continuing.\n&quot;);
     return;
   }
   if (!tree_)
   {
     if (input_-&gt;isOrganized ())
       tree_.reset (new pcl::OrganizedDataIndex&lt;PointT&gt; ());
     else
       tree_.reset (new pcl::KdTreeFLANN&lt;PointT&gt; (false));
   }
   tree_-&gt;setInputCloud (input_);

   std::vector&lt;int&gt; k_indices;
   std::vector&lt;float&gt; k_distances;

   output = *input_;

   for (size_t point_id = 0; point_id &lt; input_-&gt;points.size (); ++point_id)
   {
     tree_-&gt;radiusSearch (point_id, sigma_s_ * 2, k_indices, k_distances);

     output.points[point_id].intensity = computePointWeight (point_id, k_indices, k_distances);
   }
 }

 #define PCL_INSTANTIATE_BilateralFilter(T) template class PCL_EXPORTS pcl::BilateralFilter&lt;T&gt;;

 #endif // PCL_FILTERS_BILATERAL_H_
</pre></div>
</td></tr></table></div>
</div>
</div>
<div class="section" id="taking-advantage-of-other-pcl-concepts">
<h1><a class="toc-backref" href="#id18">Taking advantage of other PCL concepts</a></h1>
<div class="section" id="point-indices">
<h2><a class="toc-backref" href="#id19">Point indices</a></h2>
<p>The standard way of passing point cloud data into PCL algorithms is via
<span>setInputCloud</span> calls. In addition, PCL also
defines a way to define a region of interest / <em>list of point indices</em> that the
algorithm should operate on, rather than the entire cloud, via
<span>setIndices</span>.</p>
<p>All classes inheriting from <a class="reference external" href="http://docs.pointclouds.org/trunk/classpcl_1_1_p_c_l_base.html">PCLBase</a> exhbit the following
behavior: in case no set of indices is given by the user, a fake one is created
once and used for the duration of the algorithm. This means that we could
easily change the implementation code above to operate on a <em>&lt;cloud, indices&gt;</em>
tuple, which has the added advantage that if the user does pass a set of
indices, only those will be used, and if not, the entire cloud will be used.</p>
<p>The new <em>bilateral.hpp</em> class thus becomes:</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
 2
 3
 4
 5
 6
 7
 8
 9
10
11
12
13
14
15
16
17
18
19
20
21</pre></div></td><td class="code"><div class="highlight"><pre> #include &lt;pcl/kdtree/kdtree_flann.h&gt;
 #include &lt;pcl/kdtree/organized_data.h&gt;

 ...
 template &lt;typename PointT&gt; void
 pcl::BilateralFilter&lt;PointT&gt;::applyFilter (PointCloud &amp;output)
 {
   if (sigma_s_ == 0)
   {
     PCL_ERROR (&quot;[pcl::BilateralFilter::applyFilter] Need a sigma_s value given before continuing.\n&quot;);
     return;
   }
   if (!tree_)
   {
     if (input_-&gt;isOrganized ())
       tree_.reset (new pcl::OrganizedDataIndex&lt;PointT&gt; ());
     else
       tree_.reset (new pcl::KdTreeFLANN&lt;PointT&gt; (false));
   }
   tree_-&gt;setInputCloud (input_);
 ...
</pre></div>
</td></tr></table></div>
<p>The implementation file header thus becomes:</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
 2
 3
 4
 5
 6
 7
 8
 9
10
11
12
13
14
15
16
17
18
19
20
21
22
23
24
25
26
27
28
29
30
31
32
33
34
35
36
37
38
39
40
41
42
43
44
45
46
47
48
49
50
51
52
53
54
55
56
57
58
59
60
61
62</pre></div></td><td class="code"><div class="highlight"><pre> #ifndef PCL_FILTERS_BILATERAL_IMPL_H_
 #define PCL_FILTERS_BILATERAL_IMPL_H_

 #include &lt;pcl/filters/bilateral.h&gt;
 #include &lt;pcl/kdtree/kdtree_flann.h&gt;
 #include &lt;pcl/kdtree/organized_data.h&gt;

 template &lt;typename PointT&gt; double
 pcl::BilateralFilter&lt;PointT&gt;::computePointWeight (const int pid,
                                                   const std::vector&lt;int&gt; &amp;indices,
                                                   const std::vector&lt;float&gt; &amp;distances)
 {
   double BF = 0, W = 0;

   // For each neighbor
   for (size_t n_id = 0; n_id &lt; indices.size (); ++n_id)
   {
     double id = indices[n_id];
     double dist = std::sqrt (distances[n_id]);
     double intensity_dist = abs (input_-&gt;points[pid].intensity - input_-&gt;points[id].intensity);

     double weight = kernel (dist, sigma_s_) * kernel (intensity_dist, sigma_r_);

     BF += weight * input_-&gt;points[id].intensity;
     W += weight;
   }
   return (BF / W);
 }

 template &lt;typename PointT&gt; void
 pcl::BilateralFilter&lt;PointT&gt;::applyFilter (PointCloud &amp;output)
 {
   if (sigma_s_ == 0)
   {
     PCL_ERROR (&quot;[pcl::BilateralFilter::applyFilter] Need a sigma_s value given before continuing.\n&quot;);
     return;
   }
   if (!tree_)
   {
     if (input_-&gt;isOrganized ())
       tree_.reset (new pcl::OrganizedDataIndex&lt;PointT&gt; ());
     else
       tree_.reset (new pcl::KdTreeFLANN&lt;PointT&gt; (false));
   }
   tree_-&gt;setInputCloud (input_);

   std::vector&lt;int&gt; k_indices;
   std::vector&lt;float&gt; k_distances;

   output = *input_;

   for (size_t i = 0; i &lt; indices_-&gt;size (); ++i)
   {
     tree_-&gt;radiusSearch ((*indices_)[i], sigma_s_ * 2, k_indices, k_distances);

     output.points[(*indices_)[i]].intensity = computePointWeight ((*indices_)[i], k_indices, k_distances);
   }
 }

 #define PCL_INSTANTIATE_BilateralFilter(T) template class PCL_EXPORTS pcl::BilateralFilter&lt;T&gt;;

 #endif // PCL_FILTERS_BILATERAL_H_
</pre></div>
</td></tr></table></div>
<p>To make <span>indices_</span> work without typing the full
construct, we need to add a new line to <em>bilateral.h</em> that specifies the class
where <cite>indices_</cite> is declared:</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre>1
2
3
4
5
6
7
8
9</pre></div></td><td class="code"><div class="highlight"><pre> <span class="p">...</span>
   <span class="k">template</span><span class="o">&lt;</span><span class="k">typename</span> <span class="n">PointT</span><span class="o">&gt;</span>
   <span class="k">class</span> <span class="nc">BilateralFilter</span> <span class="o">:</span> <span class="k">public</span> <span class="n">Filter</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;</span>
   <span class="p">{</span>
     <span class="k">using</span> <span class="n">Filter</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;::</span><span class="n">input_</span><span class="p">;</span>
     <span class="k">using</span> <span class="n">Filter</span><span class="o">&lt;</span><span class="n">PointT</span><span class="o">&gt;::</span><span class="n">indices_</span><span class="p">;</span>
     <span class="nl">public:</span>
       <span class="n">BilateralFilter</span> <span class="p">()</span> <span class="o">:</span> <span class="n">sigma_s_</span> <span class="p">(</span><span class="mi">0</span><span class="p">),</span>
 <span class="p">...</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="licenses">
<h2><a class="toc-backref" href="#id20">Licenses</a></h2>
<p>It is advised that each file contains a license that describes the author of
the code. This is very useful for our users that need to understand what sort
of restrictions are they bound to when using the code. PCL is 100% <strong>BSD
licensed</strong>, and we insert the corpus of the license as a C++ comment in the
file, as follows:</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
 2
 3
 4
 5
 6
 7
 8
 9
10
11
12
13
14
15
16
17
18
19
20
21
22
23
24
25
26
27
28
29
30
31
32
33
34
35
36</pre></div></td><td class="code"><div class="highlight"><pre> <span class="cm">/*</span>
<span class="cm">  * Software License Agreement (BSD License)</span>
<span class="cm">  *</span>
<span class="cm">  *  Point Cloud Library (PCL) - www.pointclouds.org</span>
<span class="cm">  *  Copyright (c) 2010-2011, Willow Garage, Inc.</span>
<span class="cm">  *</span>
<span class="cm">  *  All rights reserved.</span>
<span class="cm">  *</span>
<span class="cm">  *  Redistribution and use in source and binary forms, with or without</span>
<span class="cm">  *  modification, are permitted provided that the following conditions</span>
<span class="cm">  *  are met:</span>
<span class="cm">  *</span>
<span class="cm">  *   * Redistributions of source code must retain the above copyright</span>
<span class="cm">  *     notice, this list of conditions and the following disclaimer.</span>
<span class="cm">  *   * Redistributions in binary form must reproduce the above</span>
<span class="cm">  *     copyright notice, this list of conditions and the following</span>
<span class="cm">  *     disclaimer in the documentation and/or other materials provided</span>
<span class="cm">  *     with the distribution.</span>
<span class="cm">  *   * Neither the name of Willow Garage, Inc. nor the names of its</span>
<span class="cm">  *     contributors may be used to endorse or promote products derived</span>
<span class="cm">  *     from this software without specific prior written permission.</span>
<span class="cm">  *</span>
<span class="cm">  *  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS</span>
<span class="cm">  *  &quot;AS IS&quot; AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT</span>
<span class="cm">  *  LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS</span>
<span class="cm">  *  FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE</span>
<span class="cm">  *  COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,</span>
<span class="cm">  *  INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,</span>
<span class="cm">  *  BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;</span>
<span class="cm">  *  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER</span>
<span class="cm">  *  CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT</span>
<span class="cm">  *  LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN</span>
<span class="cm">  *  ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE</span>
<span class="cm">  *  POSSIBILITY OF SUCH DAMAGE.</span>
<span class="cm">  *</span>
<span class="cm">  */</span>
</pre></div>
</td></tr></table></div>
<p>An additional like can be inserted if additional copyright is needed (or the
original copyright can be changed):</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre>1</pre></div></td><td class="code"><div class="highlight"><pre> <span class="o">*</span> <span class="n">Copyright</span> <span class="p">(</span><span class="n">c</span><span class="p">)</span> <span class="n">XXX</span><span class="p">,</span> <span class="n">respective</span> <span class="n">authors</span><span class="p">.</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="proper-naming">
<h2><a class="toc-backref" href="#id21">Proper naming</a></h2>
<p>We wrote the tutorial so far by using <em>silly named</em> setters and getters in our
example, like <cite>setSigmaS</cite> or <cite>setSigmaR</cite>. In reality, we would like to use a
better naming scheme, that actually represents what the parameter is doing. In
a final version of the code we could therefore rename the setters and getters
to <cite>set/getHalfSize</cite> and <cite>set/getStdDev</cite> or something similar.</p>
</div>
<div class="section" id="code-comments">
<h2><a class="toc-backref" href="#id22">Code comments</a></h2>
<p>PCL is trying to maintain a <em>high standard</em> with respect to user and API
documentation. This sort of Doxygen documentation has been stripped from the
examples shown above. In reality, we would have had the <em>bilateral.h</em> header
class look like:</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre>  1
  2
  3
  4
  5
  6
  7
  8
  9
 10
 11
 12
 13
 14
 15
 16
 17
 18
 19
 20
 21
 22
 23
 24
 25
 26
 27
 28
 29
 30
 31
 32
 33
 34
 35
 36
 37
 38
 39
 40
 41
 42
 43
 44
 45
 46
 47
 48
 49
 50
 51
 52
 53
 54
 55
 56
 57
 58
 59
 60
 61
 62
 63
 64
 65
 66
 67
 68
 69
 70
 71
 72
 73
 74
 75
 76
 77
 78
 79
 80
 81
 82
 83
 84
 85
 86
 87
 88
 89
 90
 91
 92
 93
 94
 95
 96
 97
 98
 99
100
101
102
103
104
105
106
107
108
109
110
111
112
113
114
115
116
117
118
119
120
121
122
123
124
125
126
127
128
129
130
131
132
133
134
135
136
137
138
139
140
141
142
143
144
145
146
147
148
149</pre></div></td><td class="code"><div class="highlight"><pre> /*
  * Software License Agreement (BSD License)
  *
  *  Point Cloud Library (PCL) - www.pointclouds.org
  *  Copyright (c) 2010-2011, Willow Garage, Inc.
  *
  *  All rights reserved.
  *
  *  Redistribution and use in source and binary forms, with or without
  *  modification, are permitted provided that the following conditions
  *  are met:
  *
  *   * Redistributions of source code must retain the above copyright
  *     notice, this list of conditions and the following disclaimer.
  *   * Redistributions in binary form must reproduce the above
  *     copyright notice, this list of conditions and the following
  *     disclaimer in the documentation and/or other materials provided
  *     with the distribution.
  *   * Neither the name of Willow Garage, Inc. nor the names of its
  *     contributors may be used to endorse or promote products derived
  *     from this software without specific prior written permission.
  *
  *  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
  *  &quot;AS IS&quot; AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
  *  LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
  *  FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
  *  COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
  *  INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
  *  BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
  *  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
  *  CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
  *  LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
  *  ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
  *  POSSIBILITY OF SUCH DAMAGE.
  *
  */

 #ifndef PCL_FILTERS_BILATERAL_H_
 #define PCL_FILTERS_BILATERAL_H_

 #include &lt;pcl/filters/filter.h&gt;
 #include &lt;pcl/kdtree/kdtree.h&gt;

 namespace pcl
 {
   /** \brief A bilateral filter implementation for point cloud data. Uses the intensity data channel.
     * \note For more information please see
     * &lt;b&gt;C. Tomasi and R. Manduchi. Bilateral Filtering for Gray and Color Images.
     * In Proceedings of the IEEE International Conference on Computer Vision,
     * 1998.&lt;/b&gt;
     * \author Luca Penasa
     */
   template&lt;typename PointT&gt;
   class BilateralFilter : public Filter&lt;PointT&gt;
   {
     using Filter&lt;PointT&gt;::input_;
     using Filter&lt;PointT&gt;::indices_;
     typedef typename Filter&lt;PointT&gt;::PointCloud PointCloud;
     typedef typename pcl::KdTree&lt;PointT&gt;::Ptr KdTreePtr;

     public:
       /** \brief Constructor.
         * Sets \ref sigma_s_ to 0 and \ref sigma_r_ to MAXDBL
         */
       BilateralFilter () : sigma_s_ (0),
                            sigma_r_ (std::numeric_limits&lt;double&gt;::max ())
       {
       }


       /** \brief Filter the input data and store the results into output
         * \param[out] output the resultant point cloud message
         */
       void
       applyFilter (PointCloud &amp;output);

       /** \brief Compute the intensity average for a single point
         * \param[in] pid the point index to compute the weight for
         * \param[in] indices the set of nearest neighor indices
         * \param[in] distances the set of nearest neighbor distances
         * \return the intensity average at a given point index
         */
       double
       computePointWeight (const int pid, const std::vector&lt;int&gt; &amp;indices, const std::vector&lt;float&gt; &amp;distances);

       /** \brief Set the half size of the Gaussian bilateral filter window.
         * \param[in] sigma_s the half size of the Gaussian bilateral filter window to use
         */
       inline void
       setHalfSize (const double sigma_s)
       {
         sigma_s_ = sigma_s;
       }

       /** \brief Get the half size of the Gaussian bilateral filter window as set by the user. */
       double
       getHalfSize ()
       {
         return (sigma_s_);
       }

       /** \brief Set the standard deviation parameter
         * \param[in] sigma_r the new standard deviation parameter
         */
       void
       setStdDev (const double sigma_r)
       {
         sigma_r_ = sigma_r;
       }

       /** \brief Get the value of the current standard deviation parameter of the bilateral filter. */
       double
       getStdDev ()
       {
         return (sigma_r_);
       }

       /** \brief Provide a pointer to the search object.
         * \param[in] tree a pointer to the spatial search object.
         */
       void
       setSearchMethod (const KdTreePtr &amp;tree)
       {
         tree_ = tree;
       }

     private:

       /** \brief The bilateral filter Gaussian distance kernel.
         * \param[in] x the spatial distance (distance or intensity)
         * \param[in] sigma standard deviation
         */
       inline double
       kernel (double x, double sigma)
       {
         return (exp (- (x*x)/(2*sigma*sigma)));
       }

       /** \brief The half size of the Gaussian bilateral filter window (e.g., spatial extents in Euclidean). */
       double sigma_s_;
       /** \brief The standard deviation of the bilateral filter (e.g., standard deviation in intensity). */
       double sigma_r_;

       /** \brief A pointer to the spatial search object. */
       KdTreePtr tree_;
   };
 }

 #endif // PCL_FILTERS_BILATERAL_H_
</pre></div>
</td></tr></table></div>
<p>And the <em>bilateral.hpp</em> like:</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre>  1
  2
  3
  4
  5
  6
  7
  8
  9
 10
 11
 12
 13
 14
 15
 16
 17
 18
 19
 20
 21
 22
 23
 24
 25
 26
 27
 28
 29
 30
 31
 32
 33
 34
 35
 36
 37
 38
 39
 40
 41
 42
 43
 44
 45
 46
 47
 48
 49
 50
 51
 52
 53
 54
 55
 56
 57
 58
 59
 60
 61
 62
 63
 64
 65
 66
 67
 68
 69
 70
 71
 72
 73
 74
 75
 76
 77
 78
 79
 80
 81
 82
 83
 84
 85
 86
 87
 88
 89
 90
 91
 92
 93
 94
 95
 96
 97
 98
 99
100
101
102
103
104
105
106
107
108
109
110
111
112</pre></div></td><td class="code"><div class="highlight"><pre> /*
  * Software License Agreement (BSD License)
  *
  *  Point Cloud Library (PCL) - www.pointclouds.org
  *  Copyright (c) 2010-2011, Willow Garage, Inc.
  *
  *  All rights reserved.
  *
  *  Redistribution and use in source and binary forms, with or without
  *  modification, are permitted provided that the following conditions
  *  are met:
  *
  *   * Redistributions of source code must retain the above copyright
  *     notice, this list of conditions and the following disclaimer.
  *   * Redistributions in binary form must reproduce the above
  *     copyright notice, this list of conditions and the following
  *     disclaimer in the documentation and/or other materials provided
  *     with the distribution.
  *   * Neither the name of Willow Garage, Inc. nor the names of its
  *     contributors may be used to endorse or promote products derived
  *     from this software without specific prior written permission.
  *
  *  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
  *  &quot;AS IS&quot; AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
  *  LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
  *  FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
  *  COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
  *  INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
  *  BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
  *  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
  *  CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
  *  LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
  *  ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
  *  POSSIBILITY OF SUCH DAMAGE.
  *
  */

 #ifndef PCL_FILTERS_BILATERAL_IMPL_H_
 #define PCL_FILTERS_BILATERAL_IMPL_H_

 #include &lt;pcl/filters/bilateral.h&gt;
 #include &lt;pcl/kdtree/kdtree_flann.h&gt;
 #include &lt;pcl/kdtree/organized_data.h&gt;

 //////////////////////////////////////////////////////////////////////////////////////////////
 template &lt;typename PointT&gt; double
 pcl::BilateralFilter&lt;PointT&gt;::computePointWeight (const int pid,
                                                   const std::vector&lt;int&gt; &amp;indices,
                                                   const std::vector&lt;float&gt; &amp;distances)
 {
   double BF = 0, W = 0;

   // For each neighbor
   for (size_t n_id = 0; n_id &lt; indices.size (); ++n_id)
   {
     double id = indices[n_id];
     // Compute the difference in intensity
     double intensity_dist = abs (input_-&gt;points[pid].intensity - input_-&gt;points[id].intensity);

     // Compute the Gaussian intensity weights both in Euclidean and in intensity space
     double dist = std::sqrt (distances[n_id]);
     double weight = kernel (dist, sigma_s_) * kernel (intensity_dist, sigma_r_);

     // Calculate the bilateral filter response
     BF += weight * input_-&gt;points[id].intensity;
     W += weight;
   }
   return (BF / W);
 }

 //////////////////////////////////////////////////////////////////////////////////////////////
 template &lt;typename PointT&gt; void
 pcl::BilateralFilter&lt;PointT&gt;::applyFilter (PointCloud &amp;output)
 {
   // Check if sigma_s has been given by the user
   if (sigma_s_ == 0)
   {
     PCL_ERROR (&quot;[pcl::BilateralFilter::applyFilter] Need a sigma_s value given before continuing.\n&quot;);
     return;
   }
   // In case a search method has not been given, initialize it using some defaults
   if (!tree_)
   {
     // For organized datasets, use an OrganizedDataIndex
     if (input_-&gt;isOrganized ())
       tree_.reset (new pcl::OrganizedDataIndex&lt;PointT&gt; ());
     // For unorganized data, use a FLANN kdtree
     else
       tree_.reset (new pcl::KdTreeFLANN&lt;PointT&gt; (false));
   }
   tree_-&gt;setInputCloud (input_);

   std::vector&lt;int&gt; k_indices;
   std::vector&lt;float&gt; k_distances;

   // Copy the input data into the output
   output = *input_;

   // For all the indices given (equal to the entire cloud if none given)
   for (size_t i = 0; i &lt; indices_-&gt;size (); ++i)
   {
     // Perform a radius search to find the nearest neighbors
     tree_-&gt;radiusSearch ((*indices_)[i], sigma_s_ * 2, k_indices, k_distances);

     // Overwrite the intensity value with the computed average
     output.points[(*indices_)[i]].intensity = computePointWeight ((*indices_)[i], k_indices, k_distances);
   }
 }

 #define PCL_INSTANTIATE_BilateralFilter(T) template class PCL_EXPORTS pcl::BilateralFilter&lt;T&gt;;

 #endif // PCL_FILTERS_BILATERAL_H_
</pre></div>
</td></tr></table></div>
</div>
</div>
<div class="section" id="testing-the-new-class">
<h1><a class="toc-backref" href="#id23">Testing the new class</a></h1>
<p>Testing the new class is easy. We&#8217;ll take the first code snippet example as
shown above, strip the algorithm, and make it use the <cite>pcl::BilateralFilter</cite>
class instead:</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
 2
 3
 4
 5
 6
 7
 8
 9
10
11
12
13
14
15
16
17
18
19
20
21
22
23
24
25
26
27
28
29
30
31
32
33
34
35</pre></div></td><td class="code"><div class="highlight"><pre> #include &lt;pcl/point_types.h&gt;
 #include &lt;pcl/io/pcd_io.h&gt;
 #include &lt;pcl/kdtree/kdtree_flann.h&gt;
 #include &lt;pcl/filters/bilateral.h&gt;

 typedef pcl::PointXYZI PointT;

 int
 main (int argc, char *argv[])
 {
   std::string incloudfile = argv[1];
   std::string outcloudfile = argv[2];
   float sigma_s = atof (argv[3]);
   float sigma_r = atof (argv[4]);

   // Load cloud
   pcl::PointCloud&lt;PointT&gt;::Ptr cloud (new pcl::PointCloud&lt;PointT&gt;);
   pcl::io::loadPCDFile (incloudfile.c_str (), *cloud);

   pcl::PointCloud&lt;PointT&gt; outcloud;

   // Set up KDTree
   pcl::KdTreeFLANN&lt;PointT&gt;::Ptr tree (new pcl::KdTreeFLANN&lt;PointT&gt;);

   pcl::BilateralFilter&lt;PointT&gt; bf;
   bf.setInputCloud (cloud);
   bf.setSearchMethod (tree);
   bf.setHalfSize (sigma_s);
   bf.setStdDev (sigma_r);
   bf.filter (outcloud);

   // Save filtered output
   pcl::io::savePCDFile (outcloudfile.c_str (), outcloud);
   return (0);
 }
</pre></div>
</td></tr></table></div>
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