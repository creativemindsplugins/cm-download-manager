jQuery(function($) {
	
	$('.cmdm-screenshots-filelist').sortable({
		update: function(ev, ui) {
			var wrapper = $(ev.target);
			var items = wrapper.find('.progressWrapper img');
			var ids = [];
			for (var i=0; i<items.length; i++) {
				var item = $(items[i]);
				ids.push(item.data('id'));
			}
			$('#CMDM_AddDownloadForm_screenshots').val(ids.join(','));
		}
	});

	$('#CMDM_AddDownloadForm_screenshots_BrowseButton').click(function(ev) {
		ev.preventDefault();
		ev.stopPropagation();
		
		tb_show(CMDMScreenshots.title, CMDMScreenshots.url);
		
		var container = $(this).parents('.cmdm-plupload-queue').first();
		var filelist = container.find('.cmdm-screenshots-filelist');
		var fileInput = container.find('input[type=hidden]');
		
		window.send_to_editor = function(html) {
			console.log(html);
			var match = html.match(/<img.+src="([^"]+)"/);
//			console.log(match);
			if (match && typeof match[1] == 'string') {
				console.log(match[1]);
				var url = match[1];
				
				var wrapper = $('<div/>', {'class':'progressWrapper loader'});
				filelist.append(wrapper);
//				return;
				$.post(CMDM_UploadHelper.ajax_url, {action: 'cmdm_screenshot_from_wp', url: url}, function(jsonResponse) {
					console.log(jsonResponse);
					
					var progressImg = $('<div/>', {'class': 'progressImg'});
					$(progressImg)
	                    .append('<i class="progressCancel" data-id="' + jsonResponse.id + '">&times;</i>')
	                    .append('<img src="' + jsonResponse.imgSrc + '" data-id="'+ jsonResponse.id +'" />').fadeIn('slow');
					wrapper.removeClass('loader');
					wrapper.append(progressImg);
					
					var currentFiles = fileInput.val().split(',');
					if (!currentFiles || typeof currentFiles != 'object') currentFiles = [];
		            currentFiles.push(jsonResponse.id);
		            fileInput.val(currentFiles.join(','));
		            
		            progressImg.find('.progressCancel').click(removeImage);
		            
		            CMDM_Form_thumbnail_refresh_handlers();
		            
		            if (CMDMScreenshots.firstImageAsFeatured && filelist.find('.progressWrapper').length == 1) {
		            	progressImg.find('img').click();
		            }
		            
		            window.dispatchEvent(new CustomEvent('CMDM_screenshot_added', {detail: {
		            	mediaLibraryHTML: html,
		            	sourceUrl: url,
		            	response: jsonResponse,
		            	imageContainer: progressImg
		            }}));
					
				});
			}
			tb_remove();
		}
		
//		send_to_editor('<img src="http://local.cm.brainusers.net/wp-content/uploads/2015/05/gdansk-102.jpg">');
		
	});
	
	
	var removeImage = function() {
//		console.log('remove');
    	var cancelButton = $(this);
    	var fileInput = cancelButton.parents('td').first().find('input[type=hidden]');
    	var name = cancelButton.data('name');
	    if (!name) name = cancelButton.data('id');
	    var currentFiles = fileInput.val().split(',');
	    if (!currentFiles || typeof currentFiles != 'object') currentFiles = [];
	    var toRemove = -1;
	    for(var i = 0; i < currentFiles.length; i++){
	        if(currentFiles[i] == name){
	        	toRemove = i;
	        }
	    }
	    if(toRemove >= 0){
	        currentFiles.splice(toRemove, 1);
	    }
    	fileInput.val(currentFiles.join(','));
	    cancelButton.parents('.progressWrapperList, .progressWrapper').fadeOut('slow', function(){
	        $(this).remove();
	    });
    	
    };
    
    $('.cmdm-screenshots-filelist .progressCancel').click(removeImage);

});