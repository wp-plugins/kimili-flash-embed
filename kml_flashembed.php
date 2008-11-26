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
	
	var $version = '2.0';
	
	function __construct()
	{
		// Register Hooks
		if (is_admin()) {
			
			// Register editor buttons
			add_filter( 'tiny_mce_version', array(&$this, 'tiny_mce_version') );
			add_filter( 'mce_external_plugins', array(&$this, 'mce_external_plugins') );
			add_action( 'edit_form_advanced', array(&$this, 'add_quicktags') );
			add_action( 'edit_page_form', array(&$this, 'add_quicktags') );
			add_filter( 'mce_buttons', array(&$this, 'mce_buttons') );
			
			// Queue Embed JS
			wp_enqueue_script( 'kimiliflashembed', plugins_url('/kimili-flash-embed/js/kfe.js'), array(), $this->version );
			
			
		} else {
			// Front-end
			add_action('template_redirect', array(&$this, 'doObStart'));
		}
		
		// Queue SWFObject
		wp_enqueue_script( 'swfobject', plugins_url('/kimili-flash-embed/js/swfobject.js'), array(), '2.1' );
	}
	
	public function parseShortcodes($content)
	{
		$pattern = '/(<p>[\s\n\r]*)?\[(kml_(flash|swf)embed)\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?([\s\n\r]*<\/p>)?/s';
		$result = preg_replace_callback($pattern, array(&$this, 'processShortcode'), $content);
		return $result;
	}
	
	// Thanks to WP shortcode API Code
	public function processShortcode($code)
	{
		$r	= "";

		$atts = $this->parseAtts($code[4]);
		$altContent = isset($code[6]) ? $code[6] : '';

		$attpairs	= preg_split('/\|/', $elements, -1, PREG_SPLIT_NO_EMPTY);

		if (isset($atts['movie']) && isset($atts['height']) && isset($atts['width'])) {

			$atts['fversion']	= (isset($atts['fversion'])) ? $atts['fversion'] : 6;

			if (isset($atts['fvars'])) {
				$fvarpair_regex		= "/(?<!([$|\?]\{))\s+;\s+(?!\})/";
				$atts['fvars']		= preg_split($fvarpair_regex, $atts['fvars'], -1, PREG_SPLIT_NO_EMPTY);
			}

			// Convert any quasi-HTML in alttext back into tags
			$atts['alttext']		= (isset($atts['alttext'])) ? preg_replace("/{(.*?)}/i", "<$1>", $atts['alttext']) : $altContent;

			// If we're not serving up a feed, generate the script tags
			if (is_feed()) {
				$r	= $this->buildObjectTag($atts);
			} else {
				$r	= $this->buildObjectTag($atts);
			}
		}
	 	return $r;
	}
	
	// Thanks to WP shortcode API Code
	public function parseAtts($text)
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
	
	public function buildObjectTag($atts)
	{
		$out	= array();	
		if (is_array($atts)) extract($atts);

		// Build a query string based on the $fvars attribute
		$querystring = (count($fvars) > 0) ? "?" : "";
		for ($i = 0; $i < count($fvars); $i++) {
			$thispair	= trim($fvars[$i]);
			$nvpair		= explode("=",$thispair);
			$name		= trim($nvpair[0]);
			$value		= "";
			for ($j = 1; $j < count($nvpair); $j++) {			// In case someone passes in a fvars with additional "="
				$value		.= trim($nvpair[$j]);
				$value		= preg_replace('/&#038;/', '&', $value);
				if ((count($nvpair) - 1)  != $j) {
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
			// else {
			//	$value = '"'.$value.'"';
			//}
			$querystring .= $name . '=' . $value;
			if ($i < count($fvars) - 1) {
				$querystring .= "&";
			}
		}

										$out[] = '';    
										$out[] = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"';
										$out[] = '			width="'.$width.'"';
										$out[] = '			height="'.$height.'">';										
										$out[] = '	<param name="movie" value="' . $movie.$querystring . '" />';
		if (isset($play))				$out[] = '	<param name="play" value="' . $play . '" />';
		if (isset($loop))				$out[] = '	<param name="loop" value="' . $loop . '" />';
		if (isset($menu)) 				$out[] = '	<param name="menu" value="' . $menu . '" />';
		if (isset($scale)) 				$out[] = '	<param name="scale" value="' . $scale . '" />';
		if (isset($wmode)) 				$out[] = '	<param name="wmode" value="' . $wmode . '" />';
		if (isset($align)) 				$out[] = '	<param name="align" value="' . $align . '" />';
		if (isset($salign)) 			$out[] = '	<param name="salign" value="' . $salign . '" />';    
		if (isset($base)) 	   		 	$out[] = '	<param name="base" value="' . $base . '" />';
		if (isset($allowscriptaccess))	$out[] = '	<param name="allowScriptAccess" value="' . $allowscriptaccess . '" />';
		if (isset($allowfullscreen))	$out[] = '	<param name="allowFullScreen" value="' . $allowfullscreen . '" />';
										$out[] = '	<!--[if !IE]>-->';
							  	  		$out[] = '	<object	type="application/x-shockwave-flash"';
										$out[] = '			data="'.$movie.$querystring.'"'; 
		if (isset($base)) 	   		 	$out[] = '			base="'.$base.'"';
										$out[] = '			width="'.$width.'"';
										$out[] = '			height="'.$height.'">';
										$out[] = '	<!--[endif]>-->';
		if (isset($alttext))			$out[] = '		'.$alttext;
										$out[] = '	<!--[if !IE]>-->';
							  	  		$out[] = '	</object>';
										$out[] = '	<!--[endif]>-->';
		 								$out[] = '</object>';     

		$ret .= join("\n", $out);
		return $ret;
	}
	
	public function doObStart()
	{
		ob_start(array(&$this, 'parseShortcodes'));
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
	
	// Add a button to the quicktag view
	function add_quicktags()
	{
		$buttonshtml = '<input type="button" class="ed_button" onclick="Kimili.Flash.embed(); return false;" title="Embed a Flash Movie in your post" value="Kimili Flash Embed" />';
?>
<script type="text/javascript" charset="utf-8">
	(function(){
		
		if (typeof jQuery === 'undefined') {
			return;
		}
		
		jQuery(document).ready(function(){
			// Add the buttons to the HTML view
			jQuery("#ed_toolbar").append('<?php echo $buttonshtml; ?>');
		});
	}());
</script>
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