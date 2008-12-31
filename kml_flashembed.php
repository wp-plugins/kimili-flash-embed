<?php

/*
Plugin Name: Kimili Flash Embed
Plugin URI: http://www.kimili.com/plugins/kml_flashembed
Description: Provides a full Wordpress interface for <a href="http://code.google.com/p/swfobject/">SWFObject</a> - the best way to embed Flash on your site.
Version: 2.0
Author: Michael Bester
Author URI: http://www.kimili.com
Update: http://www.kimili.com/plugins/kml_flashembed/wp
*/

/*
*
*	KIMILI FLASH EMBED
*
*	Copyright 2008 Michael Bester (http://www.kimili.com)
*	Released under the GNU General Public License (http://www.gnu.org/licenses/gpl.html)
*
*/

/**
* 
*/
class KimiliFlashEmbed
{
	
	var $version = '2.0rc1';
	var $staticSwfs = array();
	var $dynamicSwfs = array();
	
	function KimiliFlashEmbed()
	{
		// Register Hooks
		if (is_admin()) {
			
			// Default Options
			add_option('kml_flashembed_target_class', 'flashmovie');
			add_option('kml_flashembed_publish_method', '0');
			add_option('kml_flashembed_version_major', '8');
			add_option('kml_flashembed_version_minor', '0');
			add_option('kml_flashembed_version_revision', '0');
			add_option('kml_flashembed_alt_content', '<p><a href="http://adobe.com/go/getflashplayer"><img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" /></a></p>');
			add_option('kml_flashembed_reference_swfobject', '1');
			add_option('kml_flashembed_swfobject_source', '0');
			
			// Set up the options page
			add_action('admin_menu', array(&$this, 'options_menu'));
			
			// Register editor buttons
			add_filter( 'tiny_mce_version', array(&$this, 'tiny_mce_version') );
			add_filter( 'mce_external_plugins', array(&$this, 'mce_external_plugins') );
			add_action( 'edit_form_advanced', array(&$this, 'add_quicktags') );
			add_action( 'edit_page_form', array(&$this, 'add_quicktags') );
			add_filter( 'mce_buttons', array(&$this, 'mce_buttons') );
			add_action( 'admin_head', array(&$this, 'set_admin_js_vars'));
			
			// Queue Embed JS
			wp_enqueue_script( 'kimiliflashembed', plugins_url('/kimili-flash-embed/js/kfe.js'), array(), $this->version );
			
			
		} else {
			// Front-end
			add_action('wp_head', array(&$this, 'doObStart'));
			add_action('wp_head', array(&$this, 'addScriptPlaceholder'));
			add_action('wp_footer', array(&$this, 'doObEnd'));
			
		}
		
		// Queue SWFObject
		if ( get_option('kml_flashembed_reference_swfobject') == '1') {
			if ( get_option('kml_flashembed_swfobject_source') == '0' ) {
				wp_enqueue_script( 'swfobject', 'http://ajax.googleapis.com/ajax/libs/swfobject/2.1/swfobject.js', array(), '2.1' );
			} else {
				wp_enqueue_script( 'swfobject', plugins_url('/kimili-flash-embed/js/swfobject.js'), array(), '2.1' );
			}
		}
	}
	
	function parseShortcodes($content)
	{
		$pattern = '/(<p>[\s\n\r]*)?\[(kml_(flash|swf)embed)\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?([\s\n\r]*<\/p>)?/s';
		$temp 	= preg_replace_callback($pattern, array(&$this, 'processShortcode'), $content);
		$result = preg_replace_callback('/KML_FLASHEMBED_PROCESS_SCRIPT_CALLS/s', array(&$this, 'scriptSwfs'), $temp);
		return $result;
	}
	
	// Thanks to WP shortcode API Code
	function processShortcode($code)
	{
		$r	= "";

		$atts = $this->parseAtts($code[4]);
		$altContent = isset($code[6]) ? $code[6] : '';

		$attpairs	= preg_split('/\|/', $elements, -1, PREG_SPLIT_NO_EMPTY);

		if (isset($atts['movie']) && isset($atts['height']) && isset($atts['width'])) {

			$atts['fversion']			= (isset($atts['fversion'])) ? $atts['fversion'] : 8;
			$atts['targetclass']		= (isset($atts['targetclass'])) ? $atts['targetclass'] : 'flashmovie';
			$atts['publishmethod']		= (isset($atts['publishmethod'])) ? $atts['publishmethod'] : 'static';
			$atts['useexpressinstall']	= (isset($atts['useexpressinstall'])) ? $atts['useexpressinstall'] : 'false';
			$atts['xiswf']				= plugins_url('/kimili-flash-embed/lib/expressInstall.swf');
			
			$rand	= mt_rand();  // For making sure this instance is unique

			// Extract the filename minus the extension...
			$swfname	= (strrpos($atts['movie'], "/") === false) ?
									$atts['movie'] :
									substr($atts['movie'], strrpos($atts['movie'], "/") + 1, strlen($atts['movie']));
			$swfname	= (strrpos($swfname, ".") === false) ?
									$swfname :
									substr($swfname, 0, strrpos($swfname, "."));
									
			// set an ID for the movie if necessary
			if (!isset($atts['fid'])) {
				// ... to use as a default ID if an ID is not defined.
				$atts['fid']	= "fm_" . $swfname . "_" . $rand;
			}
			
			if (!isset($atts['target'])) {
				// ... and a target ID if need be for the dynamic publishing method
				$atts['target']	= "so_targ_" . $swfname . "_" . $rand;
			}

			// Parse out the fvars
			if (isset($atts['fvars'])) {
				$fvarpair_regex		= "/(?<!([$|\?]\{))\s*;\s*(?!\})/";
				// Untexturize ampersands.
				$atts['fvars']		= preg_replace('/&amp;/', '&', $atts['fvars']);
				$atts['fvars']		= preg_split($fvarpair_regex, $atts['fvars'], -1, PREG_SPLIT_NO_EMPTY);
			}

			// Convert any quasi-HTML in alttext back into tags
			$atts['alttext']		= (isset($atts['alttext'])) ? preg_replace("/{(.*?)}/i", "<$1>", $atts['alttext']) : $altContent;
			
			// Strip leading </p> and trailing <p> - detritius from the way the tags are parsed out of the RTE
			$patterns = array(
				"/^[\s\n\r]*<\/p>/i",
				"/<p>[\s\n\r]*$/i"
			);
			$atts['alttext'] = preg_replace($patterns, "", $atts['alttext']);

			// If we're not serving up a feed, generate the script tags
			if (is_feed()) {
				$r	= $this->buildObjectTag($atts);
			} else {
				if ($atts['publishmethod'] == 'static') {
					$r = $this->publishStatic($atts);
				} else {
					$r = $this->publishDynamic($atts);
				}
			}
		}
		
	 	return $r;
	}
	
	// Thanks to WP shortcode API Code
	function parseAtts($text)
	{
		$atts = array();
		$pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
		$text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);
		if ( preg_match_all($pattern, $text, $match, PREG_SET_ORDER) ) {
			foreach ($match as $m) {
				if (!empty($m[1]))
					$atts[strtolower($m[1])] = stripcslashes($m[2]);
				elseif (!empty($m[3]))
					$atts[strtolower($m[3])] = stripcslashes($m[4]);
				elseif (!empty($m[5]))
					$atts[strtolower($m[5])] = stripcslashes($m[6]);
				elseif (isset($m[7]) and strlen($m[7]))
					$atts[] = stripcslashes($m[7]);
				elseif (isset($m[8]))
					$atts[] = stripcslashes($m[8]);
			}
		} else {
			$atts = ltrim($text);
		}
		return $atts;
	}
	
	function publishStatic($atts)
	{
		if (is_array($atts)) {
			extract($atts);
		}
		
		$this->staticSwfs[] = array(
			'id'					=> $fid,
			'version'				=> $fversion,
			'useexpressinstall'		=> $useexpressinstall,
			'xiswf'					=> $xiswf
		);
		
		return $this->buildObjectTag($atts);
	}
	
	function publishDynamic($atts)
	{
		if (is_array($atts)) {
			extract($atts);
		}
		
		$this->dynamicSwfs[] = $atts;
		
		$out = array();
		
		$out[]		= '<div id="' . $target . '" class="' . $targetclass . '">'.$alttext.'</div>';
		
		return join("\n", $out);
	}
	
	function addScriptPlaceholder()
	{
		echo 'KML_FLASHEMBED_PROCESS_SCRIPT_CALLS';
	}
	
	function scriptSwfs()
	{
		// If we don't have any swfs on the page, drop out.
		if (count($this->staticSwfs) == 0 && count($this->dynamicSwfs) == 0) {
			return '';
		}
		
		// Otherwise build out the script.
		$out = array();	
		
		$out[]		= '';
		$out[]		= '<script type="text/javascript" charset="utf-8">';
		$out[]		= '';
		$out[]		= '	/**';
		$out[]		= '	 * Courtesy of Kimili Flash Embed - Version ' . $this->version;
		$out[]		= '	 * by Michael Bester - http://kimili.com';
		$out[]		= '	 */';
		$out[]		= '';
		$out[]		= '	(function(){';
		$out[]		= '		try {';
		if (count($this->staticSwfs) > 0) {
			$out[]	= '			// Registering Statically Published SWFs';
		}
		
		for ($i = 0; $i < count($this->staticSwfs); $i++) {
			$curr	= $this->staticSwfs[$i];
			$out[]	= '			swfobject.registerObject("' . $curr['id'] . '","' . $curr['version'] . '"'.(($curr['useexpressinstall'] == 'true') ? ',"'.$curr['xiswf'].'"' : '') . ');';
		}
		
		if (count($this->dynamicSwfs) > 0) {
			$out[]		= '';
			$out[]	= '			// Registering Dynamically Published SWFs';
		}
		for ($i = 0; $i < count($this->dynamicSwfs); $i++) {
			
			$curr		= $this->dynamicSwfs[$i];
			
			// Flashvars
			$flashvars	= $this->parseFvars($curr['fvars'],'object');
			
			// Parameters
			$params = array();			
			if (isset($curr['play']))				$params[] = '"play" : "' . $curr['play'] . '"';
			if (isset($curr['loop']))				$params[] = '"loop" : "' . $curr['loop'] . '"';
			if (isset($curr['menu'])) 				$params[] = '"menu" : "' . $curr['menu'] . '"';
			if (isset($curr['quality']))			$params[] = '"quality" : "' . $curr['quality'] . '"';
			if (isset($curr['scale'])) 				$params[] = '"scale" : "' . $curr['scale'] . '"';
			if (isset($curr['salign'])) 			$params[] = '"salign" : "' . $curr['salign'] . '"';
			if (isset($curr['wmode'])) 				$params[] = '"wmode" : "' . $curr['wmode'] . '"';
			if (isset($curr['bgcolor'])) 			$params[] = '"bgcolor" : "' . $curr['bgcolor'] . '"';
			if (isset($curr['base'])) 	   		 	$params[] = '"base" : "' . $curr['base'] . '"';
			if (isset($curr['swliveconnect']))		$params[] = '"swliveconnect" : "' . $curr['swliveconnect'] . '"';
			if (isset($curr['devicefont']))			$params[] = '"devicefont" : "' . $curr['devicefont'] . '"';
			if (isset($curr['allowscriptaccess']))	$params[] = '"allowscriptaccess" : "' . $curr['allowscriptaccess'] . '"';
			if (isset($curr['seamlesstabbing']))	$params[] = '"seamlesstabbing" : "' . $curr['seamlesstabbing'] . '"';
			if (isset($curr['allowfullscreen']))	$params[] = '"allowfullscreen" : "' . $curr['allowfullscreen'] . '"';
			if (isset($curr['allownetworking']))	$params[] = '"allownetworking" : "' . $curr['allownetworking'] . '"';
			
			// Attributes
			$attributes = array();
			if (isset($curr['align'])) 			$attributes[] = '"align" : "' . $curr['align'] . '"';  
			if (isset($curr['fid'])) 			$attributes[] = '"id" : "' . $curr['fid'] . '"';  
			if (isset($curr['fid'])) 	   		$attributes[] = '"name" : "' . $curr['fid'] . '"';
			if (isset($curr['targetclass']))	$attributes[] = '"styleclass" : "' . $curr['targetclass'] . '"';
			
			$out[]		= '			swfobject.embedSWF("'.$curr['movie'].'","'.$curr['target'].'","'.$curr['width'].'","'.$curr['height'].'","'.$curr['fversion'].'","'.(($curr['useexpressinstall'] == 'true') ? $curr['xiswf'] : '').'",{';
			for ($j = 0; $j < count($flashvars); $j++) {
				$out[]	= '				'.$flashvars[$j].(($j < count($flashvars) - 1) ? ',' : '');
			}
			$out[]	= '			},{';
			for ($j = 0; $j < count($params); $j++) {
				$out[]	= '				'.$params[$j].(($j < count($params) - 1) ? ',' : '');
			}
			$out[] = '			},{';
			for ($j = 0; $j < count($attributes); $j++) {
				$out[]	= '				'.$attributes[$j].(($j < count($attributes) - 1) ? ',' : '');
			}
			$out[] = '			});';
		}
		
		$out[]		= '		} catch(e) {}';
		$out[]		= '	}())';
		$out[]		= '</script>';
		$out[]		= '';
		
		return join("\n", $out);
	}
	
	function buildObjectTag($atts)
	{
		$out	= array();	
		if (is_array($atts)) {
			extract($atts);
		}

		// Build a query string based on the $fvars attribute
		$querystring = join("&", $this->parseFvars($fvars));
		
										$out[] = '';    
										$out[] = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"';
		if (isset($fid))				$out[] = '			id="'.$fid.'"';
		if (isset($align)) 				$out[] = '			align="'.$align.'"';
										$out[] = '			class="'.$targetclass.'"';
										$out[] = '			width="'.$width.'"';
										$out[] = '			height="'.$height.'">';
										$out[] = '	<param name="movie" value="' . $movie . '" />';
		if (count($fvars) > 0)			$out[] = '	<param name="flashvars" value="' . $querystring . '" />';
		if (isset($play))				$out[] = '	<param name="play" value="' . $play . '" />';
		if (isset($loop))				$out[] = '	<param name="loop" value="' . $loop . '" />';
		if (isset($menu)) 				$out[] = '	<param name="menu" value="' . $menu . '" />';
		if (isset($quality))			$out[] = '	<param name="quality" value="' . $quality . '" />';
		if (isset($scale)) 				$out[] = '	<param name="scale" value="' . $scale . '" />';
		if (isset($salign)) 			$out[] = '	<param name="salign" value="' . $salign . '" />';
		if (isset($wmode)) 				$out[] = '	<param name="wmode" value="' . $wmode . '" />';
		if (isset($bgcolor)) 			$out[] = '	<param name="bgcolor" value="' . $bgcolor . '" />';
		if (isset($base)) 	   		 	$out[] = '	<param name="base" value="' . $base . '" />';
		if (isset($swliveconnect))		$out[] = '	<param name="swliveconnect" value="' . $swliveconnect . '" />';
		if (isset($devicefont))			$out[] = '	<param name="devicefont" value="' . $devicefont . '" />';
		if (isset($allowscriptaccess))	$out[] = '	<param name="allowscriptaccess" value="' . $allowscriptaccess . '" />';
		if (isset($seamlesstabbing))	$out[] = '	<param name="seamlesstabbing" value="' . $seamlesstabbing . '" />';
		if (isset($allowfullscreen))	$out[] = '	<param name="allowfullscreen" value="' . $allowfullscreen . '" />';
		if (isset($allownetworking))	$out[] = '	<param name="allownetworking" value="' . $allownetworking . '" />';
										$out[] = '	<!--[if !IE]>-->';
										$out[] = '	<object	type="application/x-shockwave-flash"';
										$out[] = '			data="'.$movie.'"'; 
		if (isset($fid))				$out[] = '			name="'.$fid.'"';
		if (isset($align)) 				$out[] = '			align="'.$align.'"';
										$out[] = '			width="'.$width.'"';
										$out[] = '			height="'.$height.'">';
		if (count($fvars) > 0)			$out[] = '		<param name="flashvars" value="' . $querystring . '" />';
		if (isset($play))				$out[] = '		<param name="play" value="' . $play . '" />';
		if (isset($loop))				$out[] = '		<param name="loop" value="' . $loop . '" />';
		if (isset($menu)) 				$out[] = '		<param name="menu" value="' . $menu . '" />';
		if (isset($quality))			$out[] = '		<param name="quality" value="' . $quality . '" />';
		if (isset($scale)) 				$out[] = '		<param name="scale" value="' . $scale . '" />';
		if (isset($salign)) 			$out[] = '		<param name="salign" value="' . $salign . '" />';
		if (isset($wmode)) 				$out[] = '		<param name="wmode" value="' . $wmode . '" />';
		if (isset($bgcolor)) 			$out[] = '		<param name="bgcolor" value="' . $bgcolor . '" />';
		if (isset($base)) 	   		 	$out[] = '		<param name="base" value="' . $base . '" />';
		if (isset($swliveconnect))		$out[] = '		<param name="swliveconnect" value="' . $swliveconnect . '" />';
		if (isset($devicefont))			$out[] = '		<param name="devicefont" value="' . $devicefont . '" />';
		if (isset($allowscriptaccess))	$out[] = '		<param name="allowscriptaccess" value="' . $allowscriptaccess . '" />';
		if (isset($seamlesstabbing))	$out[] = '		<param name="seamlesstabbing" value="' . $seamlesstabbing . '" />';
		if (isset($allowfullscreen))	$out[] = '		<param name="allowfullscreen" value="' . $allowfullscreen . '" />';
		if (isset($allownetworking))	$out[] = '		<param name="allownetworking" value="' . $allownetworking . '" />';
										$out[] = '	<!--<![endif]-->';
		if (isset($alttext))			$out[] = '		'.$alttext;
										$out[] = '	<!--[if !IE]>-->';
							  	  		$out[] = '	</object>';
										$out[] = '	<!--<![endif]-->';
		 								$out[] = '</object>';     

		$ret .= join("\n", $out);
		return $ret;
	}
	
	function parseFvars($fvars, $format='string')
	{
		$ret = array();
		
		for ($i = 0; $i < count($fvars); $i++) {
			$thispair	= trim($fvars[$i]);
			$nvpair		= explode("=",$thispair);
			$name		= trim($nvpair[0]);
			$value		= "";
			for ($j = 1; $j < count($nvpair); $j++) {			// In case someone passes in a fvars with additional "="
				$value		.= trim($nvpair[$j]);
				$value		= preg_replace('/&#038;/', '&', $value);
				if ((count($nvpair) - 1) != $j) {
					$value	.= "=";
				}
			}
			// Prune out JS or PHP values
			if (preg_match("/^\\$\\{.*\\}/i", $value)) { 		// JS
				$endtrim 	= strlen($value) - 3;
				$value		= substr($value, 2, $endtrim);
				$value		= str_replace(';', '', $value);
			} else if (preg_match("/^\\?\\{.*\\}/i", $value)) {	// PHP
				$endtrim 	= strlen($value) - 3;
				$value 		= substr($value, 2, $endtrim);
				$value 		= eval("return " . $value);
			}
			
			if ($format == 'string') {
				$ret[] = $name . '=' . $value;
			} else {
				$ret[] = $name . ' : "' . $value . '"';
			}
		}

		return $ret;
		
	}
	
	function doObStart()
	{
		ob_start(array(&$this, 'parseShortcodes'));
	}
	
	function doObEnd()
	{
		// Check the output buffer
		if (function_exists('ob_list_handlers')) {
			$active_handlers = ob_list_handlers();
		} else {
			$active_handlers = array();
		}
		if (sizeof($active_handlers) > 0 &&
			strtolower($active_handlers[sizeof($active_handlers) - 1]) ==
			strtolower('KimiliFlashEmbed::parseShortcodes')) {
			ob_end_flush();
		}
	}
		
	// Break the browser cache of TinyMCE
	function tiny_mce_version( $version )
	{
		return $version . '-kfe' . $this->version;
	}
	
	// Load the custom TinyMCE plugin
	function mce_external_plugins( $plugins )
	{
		$plugins['kimiliflashembed'] = plugins_url('/kimili-flash-embed/lib/tinymce3/editor_plugin.js');
		return $plugins;
	}

	// Add the custom TinyMCE buttons
	function mce_buttons( $buttons )
	{
		array_push( $buttons, 'kimiliFlashEmbed' );
		return $buttons;
	}
	
	function set_admin_js_vars()
	{
?>
<script type="text/javascript" charset="utf-8">
// <![CDATA[
	if (typeof Kimili !== 'undefined' && typeof Kimili.Flash !== 'undefined') {
		Kimili.Flash.configUrl = "<?php echo plugins_url('/kimili-flash-embed/admin/config.php'); ?>";
	}
// ]]>	
</script>
<?php
	}
	
	// Add a button to the quicktag view
	function add_quicktags()
	{
		$buttonshtml = '<input type="button" class="ed_button" onclick="Kimili.Flash.embed.apply(Kimili.Flash); return false;" title="Embed a Flash Movie in your post" value="Kimili Flash Embed" />';
?>
<script type="text/javascript" charset="utf-8">
// <![CDATA[
	(function(){
		
		if (typeof jQuery === 'undefined') {
			return;
		}
		
		jQuery(document).ready(function(){
			// Add the buttons to the HTML view
			jQuery("#ed_toolbar").append('<?php echo $buttonshtml; ?>');
		});
	}());
// ]]>
</script>
<?php	
	}
	
	// Set up the Plugin Options Page
	function options_menu() {
		add_options_page('Kimili Flash Embed Options', 'Kimili Flash Embed', 8, __FILE__, array(&$this, 'settings_page'));
	}
	
	// Render the settings page
	function settings_page() {
		
		$message = null;
		$message_updated = __("Kimili Flash Embed Options Updated.", 'kimili_flash_embed');

		// update options
		if ($_POST['action'] && $_POST['action'] == 'kml_flashembed_update') {
						
			$target_class 			= preg_replace("/(^|&\S+;)|(<[^>]*>)/U", '', strip_tags($_POST['target_class']));
			
			$alt_content			= $_POST['alt_content'];
			
			$version_major 			= preg_replace("/\D/s", '', $_POST['version_major']);
			$version_minor 			= preg_replace("/\D/s", '', $_POST['version_minor']);
			$version_revision 		= preg_replace("/\D/s", '', $_POST['version_revision']);
			
			if (empty($version_major)) {
				$version_major = '8';
			}
						
			if (empty($version_minor)) {
				$version_minor = '0';
			}
			
			if (empty($version_revision)) {
				$version_revision = '0';
			}			
			
			$publish_method			= ($_POST['publish_method'] == '1') ? $_POST['publish_method'] : '0';
			$reference_swfobject 	= ($_POST['reference_swfobject'] == '0') ? $_POST['reference_swfobject'] : '1';
			$swfobject_source		= ($_POST['swfobject_source'] == '1') ? $_POST['swfobject_source'] : '0';
			
			$message = $message_updated;
			update_option('kml_flashembed_target_class', $target_class);
			update_option('kml_flashembed_publish_method', $publish_method);
			update_option('kml_flashembed_version_major', $version_major);
			update_option('kml_flashembed_version_minor', $version_minor);
			update_option('kml_flashembed_version_revision', $version_revision);
			update_option('kml_flashembed_alt_content', $alt_content);
			update_option('kml_flashembed_reference_swfobject', $reference_swfobject);
			update_option('kml_flashembed_swfobject_source', $swfobject_source);
			
			if (function_exists('wp_cache_flush')) {
				wp_cache_flush();
			}
		
		}
			
	?>
	
<?php if ($message) : ?>
<div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
<?php endif; ?>
	
<form action="" method="post" accept-charset="utf-8">
	<div class="wrap">
		<h2>Kimili Flash Embed Preferences</h2>
		

		<h3>KFE Tag Defaults</h3> 
		
		<table class="form-table">
			<tr>
				<th scope="row" style="text-align:right; vertical-align:top;">Element Class Name</th>
				<td><input type="text" name="target_class" value="<?php echo get_option('kml_flashembed_target_class'); ?>" /></td>
			</tr>
			<tr>
				<th scope="row" style="text-align:right; vertical-align:top;">Publish Method</th>
				<td>
					<input type="radio" id="publish_method-0" name="publish_method" value="0" class="radio" <?php if (!get_option('kml_flashembed_publish_method')) echo "checked=\"checked\""; ?> /><label for="publish_method-0">Static Publishing</label>
					<input type="radio" id="publish_method-1" name="publish_method" value="1" class="radio" <?php if (get_option('kml_flashembed_publish_method')) echo "checked=\"checked\""; ?> /><label for="publish_method-1">Dynamic Publishing</label>
				</td>
			</tr>
			<tr>
				<th scope="row" style="text-align:right; vertical-align:top;">Flash Version</th>
				<td>
					<input type="text" name="version_major" value="<?php echo get_option('kml_flashembed_version_major'); ?>" size="2" title="Major Version" />.
					<input type="text" name="version_minor" value="<?php echo get_option('kml_flashembed_version_minor'); ?>" size="2" title="Minor Version" />.
					<input type="text" name="version_revision" value="<?php echo get_option('kml_flashembed_version_revision'); ?>" size="3" title="Version Revision Number" />
				</td>
			</tr>
			<tr>
				<th scope="row" style="text-align:right; vertical-align:top;">Alternate Content</th>
				<td><textarea name="alt_content" cols="50" rows="4"><?php echo stripcslashes(get_option('kml_flashembed_alt_content')); ?></textarea></td>
			</tr>
		</table>

		<h3>Javascript Options</h3> 
		
		<table class="form-table">
			<tr>
				<th scope="row" style="text-align:right; vertical-align:top;">Create a reference to SWFObject.js?</th>
				<td>
					<input type="radio" id="reference_swfobject-0" name="reference_swfobject" value="0" class="radio" <?php if (!get_option('kml_flashembed_reference_swfobject')) echo "checked=\"checked\""; ?> /><label for="reference_swfobject-0">No</label>
					<input type="radio" id="reference_swfobject-1" name="reference_swfobject" value="1" class="radio" <?php if (get_option('kml_flashembed_reference_swfobject')) echo "checked=\"checked\""; ?> /><label for="reference_swfobject-1">Yes</label>
				</td>
			</tr>
			<tr>
				<th scope="row" style="text-align:right; vertical-align:top;">Where do you want to reference SWFObject.js from?</th>
				<td>
					<input type="radio" id="swfobject_source-0" name="swfobject_source" value="0" class="radio" <?php if (!get_option('kml_flashembed_swfobject_source')) echo "checked=\"checked\""; ?> /><label for="swfobject_source-0">Google Ajax Library</label>
					<input type="radio" id="swfobject_source-1" name="swfobject_source" value="1" class="radio" <?php if (get_option('kml_flashembed_swfobject_source')) echo "checked=\"checked\""; ?> /><label for="swfobject_source-1">Internal</label>
				</td>
			</tr>
		</table>
		
		<p class="submit">
			<input type="hidden" name="action" value="kml_flashembed_update" /> 
			<input type="submit" name="Submit" value="Update Options &raquo;" /> 
		</p>

	</div>
	
</form>
	<?php
		
	}
	
}

// Start this plugin once all other plugins are fully loaded
add_action( 'plugins_loaded', 'KimiliFlashEmbed' );
function KimiliFlashEmbed() {
	global $KimiliFlashEmbed;
	$KimiliFlashEmbed = new KimiliFlashEmbed();
}

?>