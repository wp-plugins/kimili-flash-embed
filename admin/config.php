<?php

/* Finding the path to the wp-admin folder */
$iswin = preg_match('/:\\\/', dirname(__file__));
$slash = ($iswin) ? "\\" : "/";

$wp_path = preg_split('/(?=((\\\|\/)wp-content)).*/', dirname(__file__));
$wp_path = (isset($wp_path[0]) && $wp_path[0] != "") ? $wp_path[0] : $_SERVER["DOCUMENT_ROOT"];

/** Load WordPress Administration Bootstrap */
require_once($wp_path . $slash . 'wp-load.php');
require_once($wp_path . $slash . 'wp-admin' . $slash . 'admin.php');

$title = "Kimili Flash Embed";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
<head>
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
<title><?php bloginfo('name') ?> &rsaquo; <?php echo wp_specialchars( $title ); ?> &#8212; WordPress</title>
<?php

wp_admin_css( 'css/global' );
wp_admin_css();
wp_admin_css( 'css/colors' );
wp_admin_css( 'css/ie' );

$hook_suffix = '';
if ( isset($page_hook) )
	$hook_suffix = "$page_hook";
else if ( isset($plugin_page) )
	$hook_suffix = "$plugin_page";
else if ( isset($pagenow) )
	$hook_suffix = "$pagenow";

do_action("admin_print_styles-$hook_suffix");
do_action('admin_print_styles');
do_action("admin_print_scripts-$hook_suffix");
do_action('admin_print_scripts');
do_action("admin_head-$hook_suffix");
do_action('admin_head');


?>
<link rel="stylesheet" href="<?php echo plugins_url('/kimili-flash-embed/css/generator.css'); ?>?ver=<?php echo $KimiliFlashEmbed->version ?>" type="text/css" media="screen" title="no title" charset="utf-8" />
<script src="<?php echo plugins_url('/kimili-flash-embed/js/kfe.js'); ?>?ver=<?php echo $KimiliFlashEmbed->version ?>" type="text/javascript" charset="utf-8"></script>
<!--
	<?php echo wp_specialchars($title." Tag Generator" ); ?> is heavily based on
	SWFObject 2 HTML and JavaScript generator v1.2 <http://code.google.com/p/swfobject/>
	Copyright (c) 2007-2008 Geoff Stearns, Michael Williams, and Bobby van der Sluis
	This software is released under the MIT License <http://www.opensource.org/licenses/mit-license.php>
-->

</head>
<body class="<?php echo apply_filters( 'admin_body_class', '' ); ?>">

	<div class="wrap" id="KFE_Generator">
	
		<h2><?php echo wp_specialchars($title." Tag Generator" ); ?></h2> 

		<div class="note">Asterisk (<span class="req">*</span>) indicates required field</div> 
		<fieldset> 
			<legend>SWFObject configuration [ <a id="toggle1" href="#">-</a> ]</legend> 
			<div id="toggleable1">
				<div class="col1"> 
					<label for="publishingMethod">Publish method:</label> <span class="req">*</span> 
				</div> 
				<div class="col2"> 
					<select id="publishingMethod" name="publishmethod"> 
		  				<option value="static" <?php if (!get_option('kml_flashembed_publish_method')) echo "selected=\"selected\""; ?>>Static publishing</option> 
						<option value="dynamic" <?php if (get_option('kml_flashembed_publish_method')) echo "selected=\"selected\""; ?>>Dynamic publishing</option> 
					</select> 
					<a id="togglePublishingMethodHelp" href="#">what is this?</a> 
				</div> 
				<div class="clear">&nbsp;</div> 
				<div id="publishingMethodHelp" class="help"> 
					<h2>Static publishing</h2> 
					<h3>Description</h3> 
					<p>Embed Flash content and alternative content using standards compliant markup, and use unobtrusive JavaScript to resolve the issues that markup alone cannot solve.</p> 
					<h3>Pros</h3> 
					<p>The embedding of Flash content does not rely on JavaScript and the actual authoring of standards compliant markup is promoted.</p> 
					<h3>Cons</h3> 
					<p>Does not solve 'click-to-activate' mechanisms in Internet Explorer 6+ and Opera 9+.</p> 
					<h2>Dynamic publishing</h2> 
					<h3>Description</h3> 
					<p>Create alternative content using standards compliant markup and embed Flash content with unobtrusive JavaScript.</p> 
					<h3>Pros</h3> 
					<p>Avoids 'click-to-activate' mechanisms in Internet Explorer 6+ and Opera 9+.</p> 
					<h3>Cons</h3> 
					<p>The embedding of Flash content relies on JavaScript, so if you have the Flash plug-in installed, but have JavaScript disabled or use a browser that doesn't support JavaScript, you will not be able to see your Flash content, however you will see alternative content instead. Flash content will also not be shown on a device like Sony PSP, which has very poor JavaScript support, and automated tools like RSS readers are not able to pick up Flash content.</p> 
				</div> 
				<div class="col1"> 
					<label title="Flash version consists of major, minor and release version" class="info">Flash version:</label> <span class="req">*</span> 
				</div> 
				<div class="col2"> 
					<input type="text" id="major" name="major" value="<?php echo get_option('kml_flashembed_version_major'); ?>" size="4" maxlength="2" /> 
					.
					<input type="text" id="minor" name="minor" value="<?php echo get_option('kml_flashembed_version_minor'); ?>" size="4" maxlength="4" /> 
					.
					<input type="text" id="release" name="release" value="<?php echo get_option('kml_flashembed_version_revision'); ?>" size="4" maxlength="4" /> 
				</div> 
				<div class="clear">&nbsp;</div> 
				<div class="col1"> 
					<label for="expressInstall" title="Check checkbox to activate express install functionality on your swf." class="info">Adobe Express Install:</label> 
				</div> 
				<div class="col2"> 
					<input type="checkbox" id="expressInstall" name="useexpressinstall" value="true" />
				</div> 
				<div class="clear">&nbsp;</div> 
				<div id="toggleReplaceId"> 
					<div class="col1"> 
						<label for="replaceId">HTML container id:</label> <span class="req">*</span> 
					</div> 
					<div class="col2"> 
						<input type="text" id="replaceId" name="replaceId" value="" size="20" /> 
						<a id="toggleReplaceIdHelp" href="#">what is this?</a> 
					</div> 
					<div id="replaceIdHelp" class="help"> 
						<p>Specifies the id attribute of the HTML container element that will be replaced with Flash content if enough JavaScript and Flash support is available.</p> 
						<p>This HTML container will be generated automatically and will embed your alternative HTML content as defined in the HTML section.</p> 
					</div> 
					<div class="clear">&nbsp;</div> 
				</div> 
			</div> 
		</fieldset> 
		<fieldset> 
			<legend>SWF definition [ <a id="toggle2" href="#">-</a> ]</legend> 
			<div id="toggleable2"> 
				<div class="col1"> 
					<label for="swf" title="The relative or absolute path to your Flash content .swf file" class="info">Flash (.swf):</label> <span class="req">*</span> 
				</div> 
				<div class="col2"> 
					<input type="text" id="swf" name="movie" value="<?php echo get_option('kml_flashembed_filename'); ?>" size="20" /> 
				</div> 
				<div class="clear">&nbsp;</div> 
				<div class="col1"> 
					<label title="Width &times; height (unit)" class="info">Dimensions:</label> <span class="req">*</span> 
				</div> 
				<div class="col2"> 
					<input type="text" id="width" name="width" value="<?php echo get_option('kml_flashembed_width'); ?>" size="5" maxlength="5" /> 
					&times;
					<input type="text" id="height" name="height" value="<?php echo get_option('kml_flashembed_height'); ?>" size="5" maxlength="5" /> 
					<select id="unit" name="unit"> 
		  				<option value="pixels">pixels</option> 
						<option value="percentage">percentage</option> 
					</select> 
				</div> 
				<div class="clear">&nbsp;</div> 
				<div id="toggleAttsParamsContainer">			
					<div class="col1"><label class="info" title="HTML object element attributes">Attributes:</label></div>
					<div class="col3">	
						<label for="attId" class="info" title="Uniquely identifies the Flash movie so that it can be referenced using a scripting language or by CSS">Flash content id</label>
					</div> 
					<div class="col4"> 
						<input type="text" id="attId" name="fid" value="" size="15" /> 
					</div> 
					<div class="clear">&nbsp;</div>
					<div class="col1">&nbsp;</div>
					<div class="col3"> 
						<label for="attClass" class="info" title="Classifies the Flash movie so that it can be referenced using a scripting language or by CSS">class</label> 
					</div> 
					<div class="col4"> 
						<input type="text" id="attClass" name="targetclass" value="<?php echo get_option('kml_flashembed_target_class'); ?>" size="15" /> 
					</div> 
					<div class="clear">&nbsp;</div> 
					<div class="col1">&nbsp;</div> 
					<div class="col3"> 
						<label for="align" class="info" title="HTML alignment of the object element. If this attribute is omitted, it by default centers the movie and crops edges if the browser window is smaller than the movie. NOTE: Using this attribute is not valid in XHTML 1.0 Strict.">align</label> 
					</div> 
					<div class="col4"> 
						<select id="align" name="align"> 
							<option value="">Choose...</option>
			  				<option value="left">left</option> 
							<option value="right">right</option> 
							<option value="top">top</option> 
							<option value="bottom">bottom</option> 
						</select> 
					</div> 
					<div class="clear">&nbsp;</div> 
					<div class="col1"> 
						<label class="info" title="HTML object element nested param elements">Parameters:</label> 
					</div> 
					<div class="col3"> 
						<label for="play" class="info" title="Specifies whether the movie begins playing immediately on loading in the browser. The default value is true if this attribute is omitted.">play</label> 
					</div> 
					<div class="col4"> 
						<select id="play" name="play"> 
							<option value="">Choose...</option> 
							<option value="true">true</option> 
			  				<option value="false">false</option> 
						</select> 
					</div> 
					<div class="col3"> 
						<label for="loop" class="info" title="Specifies whether the movie repeats indefinitely or stops when it reaches the last frame. The default value is true if this attribute is omitted.">loop</label> 
					</div> 
					<div class="col4"> 
						<select id="loop" name="loop"> 
							<option value="">Choose...</option> 
							<option value="true">true</option> 
			  				<option value="false">false</option> 
						</select> 
					</div> 
					<div class="clear">&nbsp;</div> 
					<div class="col1">&nbsp;</div> 
					<div class="col3"> 
						<label for="menu" class="info" title="Shows a shortcut menu when users right-click (Windows) or control-click (Macintosh) the SWF file. To show only About Flash in the shortcut menu, deselect this option. By default, this option is set to true.">menu</label> 
					</div> 
					<div class="col4"> 
						<select id="menu" name="menu"> 
							<option value="">Choose...</option> 
							<option value="true">true</option> 
			  				<option value="false">false</option> 
						</select> 
					</div> 
					<div class="col3"> 
						<label for="quality" class="info" title="Specifies the trade-off between processing time and appearance. The default value is 'high' if this attribute is omitted.">quality</label> 
					</div> 
					<div class="col4"> 
						<select id="quality" name="quality"> 
							<option value="">Choose...</option> 
							<option value="best">best</option> 
			  				<option value="high">high</option> 
							<option value="medium">medium</option> 
							<option value="autohigh">autohigh</option> 
							<option value="autolow">autolow</option> 
							<option value="low">low</option> 
						</select> 
					</div> 
					<div class="clear">&nbsp;</div> 
					<div class="col1">&nbsp;</div> 
					<div class="col3"> 
						<label for="scale" class="info" title="Specifies scaling, aspect ratio, borders, distortion and cropping for if you have changed the document's original width and height.">scale</label> 
					</div> 
					<div class="col4"> 
						<select id="scale" name="scale"> 
							<option value="">Choose...</option> 
							<option value="showall">showall</option> 
				  			<option value="noborder">noborder</option> 
							<option value="exactfit">exactfit</option> 
				  			<option value="noscale">noscale</option> 
						</select> 
					</div> 
					<div class="col3"> 
						<label for="salign" class="info" title="Specifies where the content is placed within the application window and how it is cropped.">salign</label> 
					</div> 
					<div class="col4"> 
						<select id="salign" name="salign"> 
							<option value="">Choose...</option> 
							<option value="tl">tl</option> 
				  			<option value="tr">tr</option> 
							<option value="bl">bl</option> 
				  			<option value="br">br</option> 
							<option value="l">l</option> 
				  			<option value="t">t</option> 
							<option value="r">r</option> 
				  			<option value="b">b</option> 
						</select> 
					</div> 
					<div class="clear">&nbsp;</div> 
					<div class="col1">&nbsp;</div> 
					<div class="col3"> 
						<label for="wmode" class="info" title="Sets the Window Mode property of the Flash movie for transparency, layering, and positioning in the browser. The default value is 'window' if this attribute is omitted.">wmode</label> 
					</div> 
					<div class="col4"> 
						<select id="wmode" name="wmode"> 
							<option value="">Choose...</option> 
							<option value="window">window</option> 
				  			<option value="opaque">opaque</option> 
							<option value="transparent">transparent</option> 
							<option value="direct">direct</option> 
							<option value="gpu">gpu</option> 
						</select> 
					</div> 
					<div class="col3"> 
						<label for="bgcolor" class="info" title="Hexadecimal RGB value in the format #RRGGBB, which specifies the background color of the movie, which will override the background color setting specified in the Flash file.">bgcolor</label> 
					</div> 
					<div class="col4"> 
						<input type="text" id="bgcolor" name="bgcolor" value="" size="15" maxlength="7" /> 
					</div> 
					<div class="clear">&nbsp;</div> 
					<div class="col1">&nbsp;</div> 
					<div class="col3"> 
						<label for="devicefont" class="info" title="Specifies whether static text objects that the Device Font option has not been selected for will be drawn using device fonts anyway, if the necessary fonts are available from the operating system.">devicefont</label> 
					</div> 
					<div class="col4"> 
						<select id="devicefont" name="devicefont"> 
							<option value="">Choose...</option> 
							<option value="true">true</option> 
			  				<option value="false">false</option> 
						</select> 
					</div> 
					<div class="col3"> 
						<label for="seamlesstabbing" class="info" title="Specifies whether users are allowed to use the Tab key to move keyboard focus out of a Flash movie and into the surrounding HTML (or the browser, if there is nothing focusable in the HTML following the Flash movie). The default value is true if this attribute is omitted.">seamlesstabbing</label> 
					</div> 
					<div class="col4"> 
						<select id="seamlesstabbing" name="seamlesstabbing"> 
							<option value="">Choose...</option> 
							<option value="true">true</option> 
			  				<option value="false">false</option> 
						</select> 
					</div> 
					<div class="clear">&nbsp;</div> 
					<div class="col1">&nbsp;</div> 
					<div class="col3"> 
						<label for="swliveconnect" class="info" title="Specifies whether the browser should start Java when loading the Flash Player for the first time. The default value is false if this attribute is omitted. If you use JavaScript and Flash on the same page, Java must be running for the FSCommand to work.">swliveconnect</label> 
					</div> 
					<div class="col4"> 
						<select id="swliveconnect" name="swliveconnect"> 
							<option value="">Choose...</option> 
							<option value="true">true</option> 
			  				<option value="false">false</option> 
						</select> 
					</div> 
					<div class="col3"> 
						<label for="allowfullscreen" class="info" title="Enables full-screen mode. The default value is false if this attribute is omitted. You must have version 9,0,28,0 or greater of Flash Player installed to use full-screen mode.">allowfullscreen</label> 
					</div> 
					<div class="col4"> 
						<select id="allowfullscreen" name="allowfullscreen"> 
							<option value="">Choose...</option> 
							<option value="true">true</option> 
			  				<option value="false">false</option> 
						</select> 
					</div> 
					<div class="clear">&nbsp;</div> 
					<div class="col1">&nbsp;</div> 
					<div class="col3"> 
						<label for="allowscriptaccess" class="info" title="Controls the ability to perform outbound scripting from within a Flash SWF. The default value is 'always' if this attribute is omitted.">allowscriptaccess</label> 
					</div> 
					<div class="col4"> 
						<select id="allowscriptaccess" name="allowscriptaccess"> 
							<option value="">Choose...</option> 
							<option value="always">always</option> 
							<option value="sameDomain">sameDomain</option> 
			  				<option value="never">never</option> 
						</select> 
					</div> 
					<div class="col3"> 
						<label for="allownetworking" class="info" title="Controls a SWF file's access to network functionality. The default value is 'all' if this attribute is omitted.">allownetworking</label> 
					</div> 
					<div class="col4"> 
						<select id="allownetworking" name="allownetworking"> 
							<option value="">Choose...</option> 
							<option value="all">all</option> 
			  				<option value="internal">internal</option> 
							<option value="none">none</option> 
						</select> 
					</div> 
					<div class="clear">&nbsp;</div> 
					<div class="col1">&nbsp;</div> 
					<div class="col3"> 
						<label for="base" class="info" title="Specifies the base directory or URL used to resolve all relative path statements in the Flash Player movie. This attribute is helpful when your Flash Player movies are kept in a different directory from your other files.">base</label> 
					</div> 
					<div class="col5"> 
						<input type="text" id="base" name="base" value="" size="15" /> 
					</div> 
					<div class="clear">&nbsp;</div> 
					<div class="col1"> 
						<label class="info" title="Method to pass variables to a Flash movie. You need to separate individual name/variable pairs with a semicolon (i.e. name=John Doe ; count=3).">fvars:</label>
					</div> 
					<div class="col2"> 
						<textarea name="fvars" id="fvars" rows="4" cols="40"></textarea>
					</div> 
					
				</div>				
				<div class="clear">&nbsp;</div> 
				<div class="col1"><a id="toggleAttsParams" href="#">more</a></div> 
				<div class="clear">&nbsp;</div> 
			</div> 
		</fieldset> 
		<fieldset>
			<legend>Alternative Content [ <a id="toggle3" href="#">-</a> ]</legend>
			<div id="toggleable3">
				<div class="col1">
					<label for="alternativeContent">Alternative content:</label>
				</div>
				<div class="col2">
					<a id="toggleAlternativeContentHelp" href="#alternativeContentHelp">what is this?</a>
				</div>
				<div id="alternativeContentHelp" class="help">
					<p>
						The object element allows you to nest alternative HTML content inside of it, which will be displayed if Flash is not installed or supported. 
						This content will also be picked up by search engines, making it a great tool for creating search-engine-friendly content.
					</p>
					<p>Summarized, you should use alternative content for the following:</p>
					<ul>
						<li>When you like to create content that is accessible for people who browse the Web without plugins</li>
						<li>When you like to create search-engine-friendly content</li>
						<li>To tell visitors that they can have a richer user experience by downloading the Flash plugin</li>
					</ul>
				</div>
				<div class="clear"> </div>
				<div class="col2">
					<textarea id="alternativeContent" name="alternativeContent" rows="6" cols="10"><?php echo stripcslashes(get_option('kml_flashembed_alt_content')); ?></textarea>
				</div>
				<div class="clear"> </div>
			</div>
		</fieldset>
		<div class="col1"> 
			<input type="button" class="button" id="generate" name="generate" value="Generate" />
		</div> 
		
	</div>
	
	<script type="text/javascript" charset="utf-8">
		// <![CDATA[
		jQuery(document).ready(function(){
			try {
				Kimili.Flash.Generator.initialize();
			} catch (e) {
				throw "Kimili is not defined. This generator isn't going to put a KFE tag in your code.";
			}
		});
		// ]]>
	</script>

</body>
</html>