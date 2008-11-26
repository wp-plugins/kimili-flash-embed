<?php

/*
Plugin Name: Kimili Flash Embed
Plugin URI: http://www.kimili.com/plugins/kml_flashembed
Description: Provides a full Wordpress interface for <a href="http://code.google.com/p/swfobject/">SWFObject</a> - the best way to embed Flash on a site.
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
			
			// Register editor button hooks
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
	
	public function doObStart()
	{
		# code...
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