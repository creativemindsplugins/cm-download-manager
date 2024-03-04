(function($){

    $(document).ready(function($){

        $(".screenshots-list").slick({
            dots: true,
            infinite: true,
            slidesToShow: 1,
            slidesToScroll: 1,
            lazyLoad: 'ondemand',
            adaptiveHeight: true,
        });

        var q = window.location.hash.substring(1) ? window.location.hash.substring(1) : "description";
        tabHandler($('a[href="#' + q + '"]'));

        window.onhashchange = function(){
            var q = window.location.hash.substring(1);
            $('a[href="#' + q + '"]').trigger('click');
        };

        //
        $(".tabNav li a").click(function(){
            tabHandler($(this));
        });
    })

    /**
     * event handlers
     */
    // adding threads (question)
    .on('submit', 'form#addThreadForm', function(e){
        e.preventDefault();
        var form = $(this)[0];
        var form_data = new FormData(form); 
        form_data.set('action', 'addthread');

        $.ajax({
            type: "POST",
            url: form_data.get('cmdm_ajaxurl'),
            processData: false,
            contentType: false,
            data: form_data,
            dataType: 'json',
            beforeSend: function () {
                $(form).append('<div class="CMDM_loadingOverlay"></div>');
                $(form).find('.CMDM_error').empty().hide();
            },
            success: function(responce, status, xhr){
                // console.log('responce', responce);
                if (responce.success == 1) {
                    $(form).find('.CMDM_loadingOverlay').remove();
                    form.reset();
                    displaySupportPage(1, true);
                } else {
                    for(var i = 0; i < responce.message.length; i++)
                        $(form).find('.CMDM_error').append('<li>' + responce.message[i] + '</li>').show().delay(5000).fadeOut('slow');
                    $(form).find('.CMDM_loadingOverlay').remove();
                }
            },
            error: function (jqXHR, exception) {
                console.warn('Error in adding thread by ajax request');
            },
        });
    })
    
    // threads (questions) paging
    .on('click', '.tabItemSupport .paging a', function (e) {
        e.preventDefault();
        var currentPageItem = $('.tabItemSupport .paging a.currentPage');
        var currentPage = parseInt(currentPageItem.data('page'));
        var selectedItem = $(this);
        if (selectedItem.hasClass('prev')) {
            displaySupportPage(currentPage - 1, true);
        } else if (selectedItem.hasClass('next')) {
            displaySupportPage(currentPage + 1, true);
        } else {
            displaySupportPage(selectedItem.data('page'), true);
        }
    })

    // adding comments to threads (question)
    .on('submit', 'form#addCommentForm', function(e){
        e.preventDefault();
        var form = $(this)[0];
        var form_data = new FormData(form); 
        form_data.set('action', 'addcomment');
        
        $.ajax({
            type: "POST",
            url: form_data.get('cmdm_ajaxurl'),
            processData: false,
            contentType: false,
            data: form_data,
            dataType: 'json',
            beforeSend: function () {
                $(form).append('<div class="CMDM_loadingOverlay"></div>');
                $(form).find('.CMDM_error').empty().hide();
            },
            success: function(responce, status, xhr){
                // console.log('responce', responce);
                if (responce.success == 1) {
                    $(form).find('.CMDM_loadingOverlay').remove();
                    form.reset();
                    displayCommentsTable(responce.target_url);
                } else {
                    for(var i = 0; i < responce.message.length; i++)
                        $(form).find('.CMDM_error').append('<li>' + responce.message[i] + '</li>').show().delay(5000).fadeOut('slow');
                    $(form).find('.CMDM_loadingOverlay').remove();
                }
            },
            error: function (jqXHR, exception) {
                console.warn('Error in adding comment to thread by ajax request');
            },
        });
    })
    
    ;

    

    /**
     * functions
     * 
     */

    //
    function tabHandler(el){
        var tabIndex = $(el).parent("li").index();
        $(".tabNav li").removeClass("on");
        $(el).parent("li").addClass("on");
        $(".tabItem").hide();
        $(".tabItem").eq(tabIndex).show();
    }

    //
    function displaySupportPage (pageNum, force) {
        force = typeof force !== 'undefined' ? force : false;
        var currentPageItem = $('.tabItemSupport .paging a.currentPage');
        var totalPages = parseInt($('.tabItemSupport .paging').data('pages'));
        var permalink = $('.tabItemSupport .paging').data('permalink');
        var currentPage = parseInt(currentPageItem.data('page'));
        if (pageNum < 1)
            pageNum = 1;
        if (pageNum > totalPages)
            pageNum = totalPages;
        if (!force && pageNum == currentPage)
            return false;
        else {
            currentPageItem.removeClass('currentPage');
            $.ajax({
                url: permalink + '/topic/page/' + pageNum,
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

    //
    function displayCommentsTable (_url) {
        $.ajax({
            url: _url,
            dataType: 'html',
            beforeSend: function () {
                $('#commentsTableContainer').append('<div class="CMDM_loadingOverlay"></div>');
            },
            success: function (responce) {
                var commentsHtml = $(responce).find('#commentsTableContainer');
                $('#commentsTable').html(commentsHtml);
            }
        });
    }

})(jQuery);