jQuery(function($) {

	jQuery('#upload_default_screenshot_button').click(function() {
	 formfield = jQuery('#upload_default_screenshot').attr('name');
	 tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
	 return false;
	});
	
	window.send_to_editor = function(html) {
		var match = html.match(/<img.+src="([^"]+)"/);
		if (match && typeof match[1] == 'string') {
			imgurl = match[1];
			// var imgurl = jQuery('img',html).attr('src');
			jQuery('#upload_default_screenshot').val(imgurl);
			jQuery('#upload_default_screenshot_img').attr('src', imgurl);
			tb_remove();
		}
	}
	
	
	var permissionFieldsPrefixes = ['CMDM_downloading', 'CMDM_adding', 'CMDM_viewing', 'CMDM_approving_new_uploads'];
	var hideViewPermissionOtherFields = function(prefix) {
//		console.log('hide', prefix)
		$('*[name^='+ prefix +'_]').each(function() {
			var obj = $(this);
			if (!obj.attr('name').match(/_permissions$/)) {
				obj.parents('.cm-settings-row').hide();
			}
		});
	};
	for (var i=0; i<permissionFieldsPrefixes.length; i++) {
		var prefix = permissionFieldsPrefixes[i];
		(function(prefix) {
			var permissionField = $('select[name='+ prefix +'_permissions]');
			permissionField.change(function() {
				hideViewPermissionOtherFields(prefix);
				var value = permissionField.val();
				if (true || value == 'roles') {
					var name = prefix +'_'+ permissionField.val();
					var selector = '*[name="'+ name +'"], *[name="'+ name +'[]"]';
					$(selector).parents('.cm-settings-row').show();
				}
			});
			permissionField.trigger('change');
		})(prefix);
	}
	
	// Position of admin panel tabs row
	function position_settings_top_bar() {
		var top_edge = parseInt($('#wpadminbar').height());
		$('#cmdm-tab-menu').css({top: top_edge + 'px'});
		var top_of_settings_group_header = parseInt($('#cmdm-tab-menu').height()) + parseInt(top_edge);
		$('#cm-downloads-settings-form h3').css({top: top_of_settings_group_header + 'px'});
	};
	$(document).ready(function(){
		position_settings_top_bar();
	});
	$(window).resize(function() {
		position_settings_top_bar();
	});
	
}(jQuery));
