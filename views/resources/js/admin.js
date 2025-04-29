jQuery(function($) {
	
	$('select[name=CMDM_frontend_template_dir]').change(function() {
		var input = $(this);
		if ( (input.val() != 'frontend') && (input.val() != 'frontend-flat') ) {
			if (confirm('Chosen style will work properly only when using a page template provided by your theme.\n\nDo you want to set page template automaticaly to "page.php" for the index, download and dashboard pages?')) {
				$('select[name=CMDM_index_page_template]').val('page.php');
				$('select[name=CMDM_download_page_template]').val('page.php');
				$('select[name=CMDM_dashboard_page_template]').val('page.php');
			}
		}
	});

	$('.cmdm-protect-upload-dir .button-more').click(function() {
		var button = $(this);
		button.parents('div').first().find('.more').toggle();
		var text = button.text();
		button.text(button.data('altText'));
		button.data('altText', text);
		return false;
	});
	
	$('.cmdm-dismiss').click(function() {
		var container = $(this).parents('.cmdm-notice');
		$.post('admin-ajax.php', {action: "cmdm_notice_dismiss", notice: container.data('noticeId')}, function() {
			container.fadeOut('slow', function() {
				container.remove();
			});
		});
		return false;
	});
	
	$('.cmdm-category-icon-choose-btn').click(function(ev) {
		ev.stopPropagation();
		ev.preventDefault();
		var btn = $(this);
		var file_frame = wp.media.frames.file_frame = wp.media({
			title: 'Select featured image',
			selection: 'selection',
			button: {
				text: 'Select',
			},
			type : 'image',
			library : {
				type : 'image'
			},
			editing: false,
			multiple: false
		});
		file_frame.on( 'select', function() {
			var attachment = file_frame.state().get('selection').first().toJSON();
			console.log(attachment);
			if( attachment.type == 'image' ) {
				var image;
				if (typeof attachment.sizes.thumbnail != 'undefined') {
					image = attachment.sizes.thumbnail;
				} else {
					image = attachment.sizes.full;
				}
				btn.parents('.form-field').find('.cmdm-category-icon').html( $('<img>', {src: image.url}) );
				btn.parents('.form-field').find('input[name=cmdm_category_icon]').val( image.url );
			}
		});
		file_frame.open();
	});
	
	$('.cmdm-embed-shortcode textarea').on('click', function() {
		this.select();
	});
	
	$('div#cmdm_log_files_filter').keyup(function(ev) {
		var val = $(this).text();
		var tbl = $(this).closest('table');
		var rows = $(tbl).find('tr[class^="post-id"]');
		if ( val.length != 0 && val != undefined ) {
			$(rows).each(function() {
				var files_cell = $(this).find('.files_list')[0];
				if ( $(files_cell).text().toLowerCase().indexOf(val) != -1 ) {
					$(files_cell).closest('tr').show();
				} else {
					$(files_cell).closest('tr').hide();
				}
			});
		} else {
			$(rows).each(function() {
				$(this).show();
			});
		}
		ev.stopPropagation();
	});
	function groupsVisible(obj) {
		if (obj.val() == '1') {
			obj.parents('.form-field').find('.groups').show();
			obj.parents('.form-field').find('.category_upload_date_access').show();
		} else {
			obj.parents('.form-field').find('.groups').hide();
			obj.parents('.form-field').find('.category_upload_date_access').hide();
		}
	}

	groupsVisible($('input[name=cmdm_upload_users_groups_enable]:checked'));
	$('input[name=cmdm_upload_users_groups_enable]').change(function() {
		groupsVisible($(this));
	});
	$('#category_upload_date_access_from').datepicker({ dateFormat: 'dd-mm-yy'});
	$('#category_upload_date_access_to').datepicker({ dateFormat: 'dd-mm-yy'});

});


function cmdm_categories_custom_filter(s) {
	s = s.toLowerCase();
	var $ = jQuery;
	$('#tag-search-input').val(s);
	$('#the-list td.column-name').each(function() {
		var td = $(this);
		var tr = td.parents('tr').first();
		var name = td.find('strong a').text().toLowerCase();
		var levelMatch = name.replace(' ', '').match(/^—+/);
		tr.attr('data-level', levelMatch ? levelMatch[0].length : 0);
		if (name.indexOf(s) >= 0) {
			tr.attr('data-show', 1);
		} else {
			var prev = tr.prev();
			if (prev.data('show') == 1 && prev.data('level') <= tr.data('level')) {
				tr.attr('data-show', 1);
			} else {
				tr.hide();
			}
		}
	});
}
