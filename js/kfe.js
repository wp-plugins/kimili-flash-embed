var Kimili = window.Kimili || {};

(Kimili.Flash = function() {
	
	return {
	
		embed : function() {
			
			if (typeof this.configUrl !== 'string' || typeof tb_show !== 'function') {
				return;
			}
			
			var url = this.configUrl + ((this.configUrl.match(/\?/)) ? "&" : "?") + "TB_iframe=true";
			
			tb_show('Kimili Flash Embed', url , false);
		}
		
	};
	
}());

/*
	Generator specific script
*/

(Kimili.Flash.Generator = function(){
	
	var toggleSection = function(toggle, $toggleable) {
		console.log($toggleable.css('display'));
		if ($toggleable.css('display') === "" || $toggleable.css('display') === "block") {
			$toggleable.css('display', 'none');
			toggle.firstChild.nodeValue = "+";
		}
		else {
			$toggleable.css('display', 'block');
			toggle.firstChild.nodeValue = "-";
		}
	};
	
	var toggleAttsParamsContainer = function() {
		var $tb = jQuery("#toggleAttsParams");
		var $tc = jQuery("#toggleAttsParamsContainer");
		if ($tc.css('display') === "" || $tc.css('display') === "none") {
			$tc.css('display','block');
			$tb.get(0).firstChild.nodeValue = "less";
		}
		else {
			$tc.css('display','none');
			$tb.get(0).firstChild.nodeValue = "more";
		}
	};
	
	var insertTag = function() {
		
		var tag = "KFE test";//buildTag();
		var win = window.parent || window;
				
		if ( typeof win.tinyMCE !== 'undefined' && ( win.ed = win.tinyMCE.activeEditor ) && !win.ed.isHidden() ) {
			win.ed.focus();
			if (win.tinymce.isIE)
				win.ed.selection.moveToBookmark(win.tinymce.EditorManager.activeEditor.windowManager.bookmark);

			win.ed.execCommand('mceInsertContent', false, tag);
		} else {
			win.edInsertContent(win.edCanvas, tag);
		}
		
		// Close Lightbox
		win.tb_remove();
		
	};
	
	return {
		
		initialize : function() {
			
			if (typeof jQuery === 'undefined') {
				return;
			}
			
			//checkRequired();
			jQuery("#publishingMethod").change(function(e) {
				checkRequired();
			});
			//checkAltContent();
			jQuery("#htmlTemplate").change(function(e) {
				checkAltContent();
			});
			
			jQuery("#togglePublishingMethodHelp").click(function(e) {
				e.preventDefault();
				var el = jQuery("#publishingMethodHelp");
				el.css('display', (el.css('display') === "block" ? "none" : "block"));
			});
			
			jQuery("#toggleAlternativeContentHelp").click(function(e) {
				e.preventDefault();
				var el = jQuery("#alternativeContentHelp");
				el.css('display', (el.css('display') === "block" ? "none" : "block"));
			});
			jQuery("#toggleReplaceIdHelp").click(function(e) {
				e.preventDefault();
				var el = jQuery("#replaceIdHelp");
				el.css('display', (el.css('display') === "block" ? "none" : "block"));
			});
			jQuery("#toggle1").click(function(e) {
				e.preventDefault();
				toggleSection(this, jQuery("#toggleable1"));
			});
			jQuery("#toggle2").click(function(e) {
				e.preventDefault();
				toggleSection(this, jQuery("#toggleable2"));
			});
			jQuery("#toggle3").click(function(e) {
				e.preventDefault();
				toggleSection(this, jQuery("#toggleable3"));
			});
			jQuery("#toggle4").click(function(e) {
				e.preventDefault();
				toggleSection(this, jQuery("#toggleable4"));
			});
			
			jQuery("#toggleAttsParams").click(function(e) {
				e.preventDefault();
				toggleAttsParamsContainer();
			});
			jQuery("#addFlashvar").onclick = function() {
				flashvarsTotal++;
				addFlashvar();
				return false;
			};
			jQuery("#generate").click(function(e) {
				e.preventDefault();
				insertTag();
			});
			jQuery("#clear").click(function(e) {
				e.preventDefault();
				var win = window.parent || window;
				win.tb_remove();
			});
			
		}
		
	};
	
}());