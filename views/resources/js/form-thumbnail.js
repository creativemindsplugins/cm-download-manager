function CMDM_Form_thumbnail_refresh_handlers() {
	var $ = jQuery;
	$('#CMDM_AddDownloadForm_screenshots_filelist img:not(.cmdm-thumb-handler)').each(function() {
		var img = $(this);
		img.addClass('cmdm-thumb-handler');
		img.css('cursor', 'pointer');
		img.attr('title', cmdm_thumbnail_data.img_title);
		img.click(function() {
			
			var id = img.data('id');
			var wrapper = img.parents('.progressWrapper');
			var thumbWrapper = $('.cmdm-form-thumbnail');
			var thumb = $('.cmdm-form-thumbnail .thumb');
            thumb.find('img').remove();
            newImage = img.clone();
            newImage.removeAttr('title');
            newImage.removeAttr('style');
            thumb.append($('<div>').append(newImage).html());
			thumb.parents('td').find('input').val(id);
			thumbWrapper.removeClass('empty');
			
			CMDM_Form_thumbnail_refresh_handlers();
			
		});
	});
	
	$('.cmdm-form-thumbnail .thumb a:not(.cmdm-thumb-handler)').click(function() {
		var link = $(this);
		link.addClass('cmdm-thumb-handler');
		link.parents('td').find('input[type=hidden]').val('');
		link.parents('.thumb').find('img').remove();
		link.parents('.cmdm-form-thumbnail').addClass('empty');
		return false;
	});
};

jQuery(function($) {
	
	CMDM_Form_thumbnail_refresh_handlers();
	
	
});