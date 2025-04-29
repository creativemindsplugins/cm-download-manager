jQuery(function ($) { // Report button

	$('.cmdm-btn-deselect-all > button.style-old').click(function(ev) {
		var btn = $(this);
		var container = btn.parent().next('.cmdm-single-download-checkbox-list');
		var attachments = container.find('.cmdm_att');
		ev.preventDefault();
		if (attachments.length > 0 ) {
			if ( btn.text() == "Deselect all") {
				$.each(attachments, (ind, el)=>{$(el).prop("checked", false);});
				btn.text("Select all");
			} else {
				$.each(attachments, (ind, el)=>{$(el).prop("checked", true);});
				btn.text("Deselect all");
			}
		}
	});
	
	
});


// ----------------------------------------------------------------------
// Support


jQuery(function ($) {

    var $addThreadForm = $('#addThreadForm');
    $('.CMDM .tabItemSupport .paging a').click(function (e) {
        e.preventDefault();
        var currentPageItem = $('.tabItemSupport .paging a.currentPage');
        var currentPage = parseInt(currentPageItem.data('page'));
        var selectedItem = $(this);
        if (selectedItem.hasClass('prev')) {
            showSupportPage(currentPage - 1);
        } else if (selectedItem.hasClass('next')) {
            showSupportPage(currentPage + 1);
        } else {
            showSupportPage(selectedItem.data('page'));
        }

    });

    function showSupportPage (pageNum, force) {
        force = typeof force !== 'undefined' ? force : false;
        var currentPageItem = $('.tabItemSupport .paging a.currentPage');
        var currentPage = parseInt(currentPageItem.data('page'));

        if (pageNum < 1)
            pageNum = 1;
        if (pageNum > totalSupportPages)
            pageNum = totalSupportPages;
        if (!force && pageNum == currentPage)
            return false;
        else {
            currentPageItem.removeClass('currentPage');
            $.ajax({
                url: location.href.replace(/\#.+$/, '') + 'topic/page/' + pageNum,
                dataType: 'html',
                beforeSend: function () {
                    $('#threadsContainer').append('<div class="CMDM_loadingOverlay"></div>');
                },
                success: function (data) {
                    $('#threadsContainer').html(data);
                    $('.tabItemSupport .paging a[data-page=' + pageNum + ']').addClass('currentPage');
                }
            });
        }
    }

    if ($addThreadForm.length)
    {
        $addThreadForm.ajaxForm({
            dataType: 'json',
            beforeSubmit: function (arr, $form) {
                $form.append('<div class="CMDM_loadingOverlay"></div>');
                $form.find('.CMDM_error').empty().hide();
            },
            success: function (data, status, xhr, $form) {

                if (data.success == 1) {
                    $form.find('.CMDM_loadingOverlay').remove();
                    $form.resetForm();
                    showSupportPage(1, true);
                    $form.find('.CMDM_success').append('<li>' + cmdm_supportSuccess + '</li>').show().delay(5000).fadeOut('slow');
                } else {
                    for(var i = 0; i < data.message.length; i++)
                        $form.find('.CMDM_error').append('<li>' + data.message[i] + '</li>').show().delay(5000).fadeOut('slow');
                    $form.find('.CMDM_loadingOverlay').remove();
                }
            }
        });
    }
    
    /*
    $('.CMDM #threadsContainer .topicLink').click(function() {
    	
    	var link = $(this);
    	$.ajax({
    		url: this.href,
    		success: function(response) {
    			var html = $(response);
    			html = html.find('.cmdm-support-topic').html();
    			link.parents('.tabItemSupport').html(html);
    		}
    	});
    	
    	return false;
    	
    });*/

    function showModeration () {

    }
});



// -----------------------------------------------------------------
// Single

(function ($) {
    
        var q = window.location.hash.substring(1);

        var sliderTime = 5000;
        var sliderTimeout;
        var recalculateZIndex = function() {
        	var itemsLength = $('.cmdm-screenshots .cmdm-screenshots-scrollable .items > div').length;
			$('.cmdm-screenshots .cmdm-screenshots-scrollable .items > div').each(function(index) {
				$(this).css('z-index', itemsLength - index + 10);
    		});
        };
        var setNextSlide = function() {
        	sliderTimeout = setTimeout(function() {
	    		nextScreenshot('next');
	    	}, sliderTime);
        };
        var nextScreenshot = function(dir) {
        	if (typeof dir == 'undefined') dir = 'next';
    		var width = $('.cmdm-screenshots .cmdm-screenshots-scrollable').width();
    		var current = $('.cmdm-screenshots .cmdm-screenshots-scrollable .items > div').first();
    		var next = current.next();
    		var last = $('.cmdm-screenshots .cmdm-screenshots-scrollable .items > div').last();
    		var left = -width;
    		if (dir == 'prev') {
    			left = width;
    			current.after(last);
    			recalculateZIndex();
    		}
    		current.animate({left: ""+left+"px"}, 'slow', 'linear', function() {
    			current.css('z-index', -9999);
    			current.css('left', 0);
    			if (dir == 'next') {
    				current.appendTo(current.parents('.items').first());
    			} else {
    				current.next().after(current);
    			}
    			recalculateZIndex();
    			if (slideshow_autoplay) {
    				setNextSlide();
    			}
    		});
    	};
    	
    	slideshow_autoplay = (typeof slideshow_autoplay !== 'undefined' && slideshow_autoplay);
    	var items = $('.cmdm-screenshots .cmdm-screenshots-scrollable .items > div');
    	if (items.length > 1) {
    		recalculateZIndex();
    		if (slideshow_autoplay) setNextSlide();
    	}
        
        $('.cmdm-screenshots-paging .browse.next').click(function() {
        	clearTimeout(sliderTimeout);
        	nextScreenshot('next');
        });
        $('.cmdm-screenshots-paging .browse.prev').click(function() {
        	clearTimeout(sliderTimeout);
        	nextScreenshot('prev');
        });
        
        

        if (q) {
//        	console.log('trigger click');
        	$('a[href="#' + q + '"]').trigger('click');
        }

        window.onhashchange = function () {
            var q = window.location.hash.substring(1);
            if (q) {
//            	console.log('trigger click');
            	$('a[href="#' + q + '"]').trigger('click');
            }
        };

        ////////////
        $(".cmdm-tab-nav li a").click(function () {
        	
            var tabIndex = $(this).parent("li").index();
            //console.log(tabIndex);
            $(".cmdm-tab-nav li").removeClass("on");
            $(this).parent("li").addClass("on");
            $(".tabItem").hide();
            $(".tabItem").eq(tabIndex).show();
        });
//        console.log('trigger click');
        $(".cmdm-tab-nav li a:first").trigger('click');
        
        
        // Open bbPress tab
        var bbPressTab = $('.cmdm-tab-nav .cmdm-bbpress-tab a');
        if (bbPressTab.length > 0 && location.href.indexOf('/page/') > -1) {
        	bbPressTab.click();
        }
		// selecting files for downloading in single download files list
		var container = $('.cmdm-single-download-checkbox-list');
		var attachments = container.find('.cmdm_att:checked');
		if (attachments.length == 0 && container.find('.cmdm_att').length != 0 ) {
			$('a.cmdm-download-button + input').prop( "disabled", true );
			$('a.cmdm-download-button').addClass('disabled');
		} else {
			$('a.cmdm-download-button + input').prop( "disabled", false );
			$('a.cmdm-download-button').removeClass('disabled');
		}
		$('.cmdm_att').click(function(ev) {
			var item = $(this);
			var container = item.parents('.cmdm-single-download-checkbox-list');
			var attachments = container.find('.cmdm_att:checked');
			if (attachments.length == 0 ) {
				$('a.cmdm-download-button + input').prop( "disabled", true );
				$('a.cmdm-download-button').addClass('disabled');
			} else {
				$('a.cmdm-download-button + input').prop( "disabled", false );
				$('a.cmdm-download-button').removeClass('disabled');
			}
		});
       	$(document).on('click', '.cmdm-single-download-list-item', function(ev) {
			if ( ev.target.className == "dashicons dashicons-external" ) return;
			var item = $(this);
			var checkbox = item.find('.cmdm_att');
			if ( checkbox.is(":checked") ) {
				item.removeClass("checked");
				checkbox.prop("checked", false);
			} else {
				checkbox.prop("checked", true);
				item.addClass("checked");
			}
			var container = item.parents('.cmdm-single-download-checkbox-list');
			var attachments = container.find('.cmdm_att:checked');
			if (attachments.length == 0 ) {
				$('a.cmdm-download-button + input').prop( "disabled", true );
				$('a.cmdm-download-button').addClass('disabled');

			} else {
				$('a.cmdm-download-button + input').prop( "disabled", false );
				$('a.cmdm-download-button').removeClass('disabled');
			}
		});
})(jQuery);

(function () {
    var po = document.createElement('script');
    po.type = 'text/javascript';
    po.async = true;
    po.src = 'https://apis.google.com/js/plusone.js';
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(po, s);
})();

(function (d, s, id) {
    window.fbAsyncInit = function () {
        // Don't init the FB as it needs API_ID just parse the likebox
        FB.XFBML.parse();
    };

    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id))
        return;
    js = d.createElement(s);
    js.id = id;
    js.src = "//connect.facebook.net/en_US/all.js";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));