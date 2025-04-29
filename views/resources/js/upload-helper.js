// Convert divs to queue widgets when the DOM is ready
function cmdm_plupload_helper() {

	var $ = jQuery;

	var fileInput = $(this);

	// console.log("fileInput", fileInput);

	var extensions = fileInput.data('fileTypes');
	if (extensions.match('zip')) {
		extensions = '*';
	}

	var uploader = new plupload.Uploader({
	    // General settings
	    runtimes: 'html5,gears,silverlight,browserplus,flash',
	    url: fileInput.data('uploadUrl'),
	    max_file_size: fileInput.data('sizeLimit'),
	    browse_button: this.id + '_BrowseButton',
	    container: this.id + '_container',
	    chunk_size: '99999mb',
	    unique_names: fileInput.data('uniqueNames'),
	    multipart: true,
	    multi_selection: (CMDM_UploadHelper.limitSingleFile != '1'),
	    multiple_queues: (CMDM_UploadHelper.limitSingleFile != '1'),
	    file_data_name: 'upload',
	    // Resize images on clientside if we can
	    //    resize: {width: 720, height: 220, quality: 90},
	    // Specify what files to browse for
	    filters: [
			{title: fileInput.data('fileTypesDescription'), extensions: extensions},
		],
	    // Flash settings
	    flash_swf_url: fileInput.data('flashUrl'),
	    // Silverlight settings
	    silverlight_xap_url: fileInput.data('silverlightUrl')
	});
	$('#' + this.id + '_filelist').on('click', '.progressCancel', function(e) {
//		console.log('progressCancel');
	    e.preventDefault();
	    var cancelButton = $(e.target);
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
	        if (CMDM_UploadHelper.limitSingleFile == '1') {
		    	$('#CMDM_AddDownloadForm_package_container input[type=button]').show();
		    }
	    });
	});

	uploader.bind('BeforeUpload', function (up, files) {
		up.settings.multipart_params = {nonce: fileInput.data('nonce')}
	});

	uploader.bind('FilesAdded', function(up, files){
		// console.log('FilesAdded');
		$('#CMDM_AddDownloadForm_submit').hide();
		if (CMDM_UploadHelper.limitSingleFile == '1') {
			$('#CMDM_AddDownloadForm_package_container input[type=button]').hide();
		}
		$('#' + this.id + '_filelist .upload-error').remove();
	    $.each(files, function(i, file){
    		$('#' + fileInput.attr('id') + '_filelist').append('<div class="progressWrapper'+ (fileInput.data('screenshot') ? '' : 'List') +'" id="' + file.id + '">'
    				+ file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' + '</div>');
	    });
	    up.refresh(); // Reposition Flash/Silverlight

	    setTimeout(function(){
	        up.start();
	    }, 500);
	});
	uploader.bind('UploadProgress', function(up, file){
//		console.log('UploadProgress', file.percent);
		var loader = '<div class="cmdm-loader-circle"></div>';
	    $('#' + file.id + " b").html(loader + ' ' + file.percent + "%");
	});
	uploader.bind('Error', function(up, err){
		console.log('error');
	    $('#' + fileInput.attr('id') + '_filelist').append('<div class="upload-error">' + err.message + "</div>");
	    up.refresh(); // Reposition Flash/Silverlight
	    $('#CMDM_AddDownloadForm_submit').show();
	    if (CMDM_UploadHelper.limitSingleFile == '1') {
	    	$('#CMDM_AddDownloadForm_package_container input[type=button]').show();
	    }
	});
	uploader.bind('FileUploaded', function(up, file, info){
//		console.log('FileUploaded');
		$('#CMDM_AddDownloadForm_submit').show();
	    var jsonResponse = $.parseJSON(info.response);
	    var container = $('#' + file.id);
	    var currentFiles = fileInput.val().split(',');
        if (!currentFiles || typeof currentFiles != 'object') currentFiles = [];

	    if (fileInput.data('screenshot')) {

	    	var progressImg = $('<div/>', {
                'class': 'progressImg',
                'style': 'display:none'
            });
            $(progressImg)
                    .append('<i class="progressCancel" data-id="' + jsonResponse.id + '">&times;</i>')
                    .append('<img src="' + jsonResponse.imgSrc + '" data-id="'+ jsonResponse.id +'" />').fadeIn('slow');
            container.html(progressImg);
            currentFiles.push(jsonResponse.id);
            fileInput.val(currentFiles.join(','));

            CMDM_Form_thumbnail_refresh_handlers();
            var wrapper = container.parents('.plupload').first();
            if (wrapper.data('firstSetThumb') == 1 && wrapper.find('.progressWrapper').length == 1) {
            	progressImg.find('img').click();
            }

	    } else {
		    if (jsonResponse.error) {
		    	container.remove();
		    	var error = $('<div class="upload-error">' + jsonResponse.error + "</div>");
		    	$('#' + fileInput.attr('id') + '_filelist').append(error);
		    	setTimeout(function() {
		    		error.fadeOut();
		    	}, 5000);
		    	if (CMDM_UploadHelper.limitSingleFile == '1') {
			    	$('#CMDM_AddDownloadForm_package_container input[type=button]').show();
			    }
		    }
		    if(jsonResponse.fileName)
		    {

		        var newItem = container.parent().find('.progressWrapperList.template').clone();
		        newItem.removeClass('template');
		        newItem.find('input').attr('name', 'attachmentName['+ jsonResponse.id +']').val(file.name);
		        newItem.find('i').data('name', jsonResponse.id);
		        newItem.hide();
		        container.parent().append(newItem);
		        container.remove();
		        newItem.fadeIn('slow');
		        currentFiles.push(jsonResponse.id);
		        fileInput.val(currentFiles.join(','));
		    }
	    }
	});
	uploader.init();


	         // Uploading files
	var file_frame;

	  jQuery('.upload_image_button').click(function( event ){

	    event.preventDefault();

	    console.log('upload_image_button click');

	    // If the media frame already exists, reopen it.
	    if ( file_frame ) {
	      file_frame.open();
	      return;
	    }

	    // Create the media frame.
	    file_frame = wp.media.frames.file_frame = wp.media({
	      title: jQuery( this ).data( 'uploader_title' ),
	      button: {
	        text: jQuery( this ).data( 'uploader_button_text' ),
	      },
	      multiple: false  // Set to true to allow multiple files to be selected
	    });

	    // When an image is selected, run a callback.
	    file_frame.on( 'select', function() {
	      // We set multiple to false so only get one image from the uploader
	      attachment = file_frame.state().get('selection').first().toJSON();

	      // Do something with attachment.id and/or attachment.url here
	    });

	    // Finally, open the modal
	    file_frame.open();
	  });

}

jQuery(function($){

	const pageBody = $('body');

	$('.CMDM-edit-form .plupload input[type=hidden]').each(cmdm_plupload_helper);

	$('#CMDM_AddDownloadForm_package_filelist').sortable({
		update: function(event, ui) {
			var items = $('#CMDM_AddDownloadForm_package_filelist .progressWrapperList:not(.template)');
			var ids = [];
			for (var i=0; i<items.length; i++) {
				var item = $(items[i]);
				ids.push(item.find('i').data('name'));
			}
			$('#CMDM_AddDownloadForm_package').val(ids.join(','));
		}
	});

	function hidePackageForm() {
		$('.cmdm-package-file, .cmdm-package-url, .cmdm-package-shortcode').parents('tr').hide();
	}

	pageBody.on('submit', '.CMDM-form', function(ev) {
    	let form = $(this);
    	if (form.data('addTagFlag') == 1) return;
    	form.hide();
    	$('.CMDM_error').hide();
    	scrollTo(0, $('.CMDM-form-loader').show().offset().top-200);
    });

	pageBody.on('change', '.CMDM-edit-form .package-type', function() {
        hidePackageForm();
        switch ($(this).val()) {
	        case 'file':
		        $('.cmdm-package-file').parents('tr').show();
		        break;
	        case 'url':
		        $('.cmdm-package-url').parents('tr').show();
		        break;
	        case 'shortcode':
		        $('.cmdm-package-shortcode').parents('tr').show();
		        break;
        }
	});

	$('.CMDM-edit-form .package-type').trigger('change');


	$('.CMDM-edit-form .cmdm-download-visibility, .CMDM-edit-form .cmdm-download-permission').each(function() {

		var select = $(this);
		var form = select.parents('form').first();
		var className = select.attr('class').match(/cmdm\-download\-\w+/);
		if (className && className.length > 0) className = className[0];

		var hideAll = function() {
			form.find('*[class*="' + className +'-"]').parents('tr').hide();
		};

		select.change(function() {
			var value = select.val().replace('_', '-');
			hideAll();
			var selector = '.' + className +'-'+ value;
//			console.log(selector);
			form.find(selector).parents('tr').show();
		});

		// Initial value check
		select.trigger('change');
		return;


	});

	// PassworddSwitchable
	$(".password-switch").parents("td").first().find("input[type=checkbox]").change(function() {
		var obj = $(this).parents("td").first().find(".password-switch");
    	if (this.checked) obj.show();
    	else {
    		obj.hide();
    		obj.find("input[type=password]").val("");
    	}
	});


	$(document).ready(function () {
		$('.cmdm-categories-show').click(function (){
			$(this).siblings('.cmdm-categories-children').slideToggle();
		});
	});

});

// toaster message

(function($) {
	var settings = {
				inEffect: 			{opacity: 'show'},	// in effect
				inEffectDuration: 	600,				// in effect duration in miliseconds
				stayTime: 			3000,				// time in miliseconds before the item has to disappear
				text: 				'',					// content of the item. Might be a string or a jQuery object. Be aware that any jQuery object which is acting as a message will be deleted when the toast is fading away.
				sticky: 			false,				// should the toast item sticky or not?
				type: 				'notice', 			// notice, warning, error, success
                position:           'middle-center',        // top-left, top-center, top-right, middle-left, middle-center, middle-right ... Position of the toast container holding different toast. Position can be set only once at the very first call, changing the position after the first call does nothing
                closeText:          '',                 // text which will be shown as close button, set to '' when you want to introduce an image via css
                close:              null                // callback function when the toastmessage is closed
            };

    var methods = {
        init : function(options) {
			if (options) {
                $.extend( settings, options );
            }
		},
        showToast : function(options) {
			var localSettings = {};
            $.extend(localSettings, settings, options);
            var toastWrapAll, toastItemOuter, toastItemInner, toastItemClose, toastItemImage;
			toastWrapAll	= (!$('.toast-container').length) ? $('<div></div>').addClass('toast-container').addClass('toast-position-' + localSettings.position).appendTo('body') : $('.toast-container');
			toastItemOuter	= $('<div></div>').addClass('toast-item-wrapper');
			toastItemInner	= $('<div></div>').hide().addClass('toast-item toast-type-' + localSettings.type).appendTo(toastWrapAll).html($('<p>').append (localSettings.text)).animate(localSettings.inEffect, localSettings.inEffectDuration).wrap(toastItemOuter);
			toastItemClose	= $('<div></div>').addClass('toast-item-close').prependTo(toastItemInner).html(localSettings.closeText).click(function() { $().toastmessage('removeToast',toastItemInner, localSettings) });
			toastItemImage  = $('<div></div>').addClass('toast-item-image').addClass('toast-item-image-' + localSettings.type).prependTo(toastItemInner);
            if(navigator.userAgent.match(/MSIE 6/i)) {
		    	toastWrapAll.css({top: document.documentElement.scrollTop});
		    }
			if(!localSettings.sticky) {
				setTimeout(function() {
					$().toastmessage('removeToast', toastItemInner, localSettings);
				},
				localSettings.stayTime);
			}
            return toastItemInner;
		},
        showNoticeToast : function (message) {
            var options = {text : message, type : 'notice'};
            return $().toastmessage('showToast', options);
        },
        showSuccessToast : function (message) {
            var options = {text : message, type : 'success'};
            return $().toastmessage('showToast', options);
        },
        showErrorToast : function (message) {
            var options = {text : message, type : 'error'};
            return $().toastmessage('showToast', options);
        },
        showWarningToast : function (message) {
            var options = {text : message, type : 'warning'};
            return $().toastmessage('showToast', options);
        },
		removeToast: function(obj, options) {
			obj.animate({opacity: '0'}, 600, function() {
				obj.parent().animate({height: '0px'}, 300, function() {
					obj.parent().remove();
				});
			});
            if (options && options.close !== null) {
                options.close();
            }
		}
	};
    $.fn.toastmessage = function( method ) {
        if ( methods[method] ) {
          return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
          return methods.init.apply( this, arguments );
        } else {
          $.error( 'Method ' +  method + ' does not exist on jQuery.toastmessage' );
        }
    };
})(jQuery);
