jQuery(function ($) {

    const $pageBody = $('body');

    //////////////////Read more button start////////////////////////

    function setReadmoreHandler() {
        if ($('.cmdm-file-description .cmdm_read_more_btn').length > 0) {
            $('.cmdm-file-description .cmdm_read_more_btn').click(function (e) {

                var hidden_text = $(this).parent().find('span.cmdm_readmore_content');
                hidden_text_condition = hidden_text.css('display');
                if (hidden_text_condition == 'none') {
                    hidden_text.show();
                } else {
                    hidden_text.hide();
                }

            });
        }
    }

    //////////////////Read more button ends////////////////////////

    setReadmoreHandler();

    var filterSearchTest = function (item, words) {
        if (words.length == 0) return true;
        var count = $('.cmdm-file-name, .cmdm-file-description, .cmdm-list-item-desc, .cmdm-file-attachment-name', item).filter(function () {
            var obj = $(this);
            for (var i = 0; i < words.length; i++) {
                var word = words[i];
                if (obj.text().toLowerCase().indexOf(word) > -1) {
                    return true;
                }
            }
            return false;
        }).length;
        return (count > 0);
    };

    var filterCategoryTest = function (item, categoryId) {
        if (!categoryId || categoryId == 0) return true;
        var categories = item.data('categories').toString().split(',');
        for (var i = 0; i < categories.length; i++) {
            if (categories[i] == categoryId) {
                return true;
            }
        }
        return false;
    }

    var filterGetWords = function (container) {
        var input = $('input', container);
        return input.val().toLowerCase().split(' ').filter(function (word) {
            return (word.replace(/ /g, '').length > 0);
        });
    };

    var filter = function (container) {
        var categoryId = $('select', container).val();
        var words = filterGetWords(container);
        $('.cmdm-files-list-item', container).each(function () {
            var item = $(this);
            if (filterSearchTest(item, words) && filterCategoryTest(item, categoryId)) {
                item.show();
            } else {
                item.hide();
            }
        });
    };

    var initHandlers = function (container) {

        $pageBody.on('click', '.cmdm-files-list-widget[data-toggle-tree="1"] h2, .cmdm-files-list-toggle-tree', function (ev) {
            var categoryContainer = $(this).parents('.cmdm-files-list-category').first();
            var contentContainer = categoryContainer.parents('.cmdm-files-list-content');

            var getAllDescendants = function (categoryId) {
                var children = $('.cmdm-files-list-category[data-parent-category-id=' + categoryId + ']', contentContainer);
                var result = $('.cmdm-files-list-category[data-parent-category-id=' + categoryId + ']', contentContainer);
                for (var i = 0; i < children.length; i++) {
                    childId = $(children[i]).data('categoryId');
                    result = result.add(getAllDescendants(childId));
                }
                return result;
            };

            var categoryId = categoryContainer.data('categoryId');
            var isVisible = $('.cmdm-files-list-items-wrapper', categoryContainer).is(":visible");
            $('.cmdm-files-list-items-wrapper', categoryContainer).slideToggle(isVisible);


            var toggleControls = categoryContainer.find('.cmdm-files-list-toggle-tree');
            var state = toggleControls.attr('data-state');
            toggleControls.attr('data-state', (state == 'open' ? 'closed' : 'open'));
        });

        $('.cmdm-files-list-item .cmdm-details-open, .cmdm-files-list-item .cmdm-details-close a', container).click(function (e) {
            e.preventDefault();
            var obj = $(this);
            var item = obj.parents('.cmdm-files-list-item').first();
            item.find('.cmdm-files-list-details').slideToggle('fast');
            return false;
        });

        $('.cmdm-files-list-filter select', container).each(function () {
            // Avoid selection when window.history.go
//			$(this).val($(this).find('option:first-child').val());
        });


        $('.cmdm-files-list-filter input.cmdm-filter', container).keyup(function () { // Search filter
            filter($(this).parents('.cmdm-files-list-widget').first());
        });


        $('.cmrm-files-list-filter-category select:not(.cmdm-category-filter-handler)', container).change(function () { // Category filter
            var obj = $(this);
            if (!$('.cmdm-filter-cat-item.active').length) {
                obj.parents('form').first().submit();
            }
        }).addClass('cmdm-category-filter-handler');

        $('.cmrm-files-list-filter-date', container).change(function () { // Category filter
            var obj = $(this);
            obj.parents('form').first().submit();
        });

        $(document).on('click', '.cmdm-filter-cat-item', function (e) { // Category filter
            e.stopPropagation();
            e.preventDefault();
            var obj = $(e.target);
            if (obj.attr('data-value') == '0') {
                $('.cmdm-filter-cat-item').each((ind, el) => {
                    $(el).removeClass('active');
                });
            }
            console.log(obj.attr('data-value'));
            obj.toggleClass('active').parents('form').first().submit();
        });

        var tagsFilterApply = function (ev) {
            if (ev) {
                ev.stopPropagation();
                ev.preventDefault();
            }
            const wrapper = $(this).parents('.cmdm-tags-filter');
            wrapper.find('.cmdm-tags-filter-list').slideToggle();

            if (parseInt(wrapper.data('changed')) === 1) {
                wrapper.data('changed', 0);
                let list = wrapper.find('.cmdm-tags-filter-current-list');
                list.text('');
                wrapper.find('.cmdm-tags-filter-list input:checkbox:checked').each(function () {
                    const $input = $(this);
                    if ($input.val() !== 'all') {
                        let text = list.text();
                        if (text.length > 0) {
                            text += ', ';
                        }
                        let label = $input.parents('label').find('span.name').text();
                        list.text(text + label);
                    }
                });
                $(this).parents('form').first().submit();
            }
        };
        $('.cmdm-tags-filter-choose:not(.cmdm-choose-handler)').click(function (ev) {
            ev.stopPropagation();
            ev.preventDefault();
            var wrapper = $(this).parents('.cmdm-tags-filter');
            var list = wrapper.find('.cmdm-tags-filter-list');
            if (list.is(':visible')) {
                tagsFilterApply.apply(this);
            } else {
                wrapper.find('.cmdm-tags-filter-list').slideToggle();
            }
        }).addClass('cmdm-choose-handler');
        $('.cmdm-tags-filter-list-btn:not(.cmdm-apply-handler)').click(tagsFilterApply).addClass('cmdm-apply-handler');
        $('.cmdm-tags-filter-list input:checkbox:not(.cmdm-change-handler)').change(function () {
            $(this).parents('.cmdm-tags-filter').data('changed', 1);
        }).addClass('cmdm-change-handler');
        $('.cmdm-tags-filter[data-type=select] select').change(function () {
            $(this).parents('form').first().submit();
        });

        $('.cmdm-files-list-filter-author select').change(function () {
            $(this).parents('form').first().submit();
        });

        let requestCallXHR = null;
        var requestCall = function (url, data, container, responseSelector, localSelector) {

            var widget = container.parents('.cmdm-files-list-widget').first();
            var loader = $('<div />', {"class": "cmdm-loader"});
            if (typeof data != 'object') data = {};
            if (typeof data.action == "undefined") data.action = 'cmdm_files_list';
            if (typeof data.widgetCacheId == "undefined") data.widgetCacheId = widget.data('widgetCacheId');
            if (typeof responseSelector == "undefined") responseSelector = '.cmdm-files-list-content';

            if (!container.hasClass('cmdm-loading')) {
                container.addClass('cmdm-loading');
                container.append(loader);
            }

            if (requestCallXHR) {
                requestCallXHR.abort();
                requestCallXHR = null;
            }

            requestCallXHR = $.post(url, data, function (response) {
                var responseObj = $(response);
                var localObj;
                if (responseSelector) {
                    responseObj = responseObj.find(responseSelector);
                }
                if (localSelector) {
                    localObj = container.find(localSelector);
                } else {
                    localObj = container;
                }

                loader.remove();
                widget.removeClass('cmdm-loading');
                if (typeof (responseObj.html()) !== 'undefined') {
                    localObj.html(responseObj.html());
                } else {
                    localObj.html('');
                }
                localObj.find('.cmdm-files-list-items-wrapper').show();

                // Refresh handlers
                initHandlers(localObj);

                container.removeClass('cmdm-loading');
                if (widget.data('scroll') == '1') {
                    $('html, body').animate({
                        scrollTop: widget.offset().top
                    }, 1000);
                }

                setReadmoreHandler();

            }).fail(function () {
                console.log("error");
            });

        };

        var toggleBulkDownloadButton = function (data) {
            let blk_btn = $('.btn_user_cat_bulk_download');
            if (blk_btn.length != 0 &&
                !data['categoryId'].match(/\D/gm) &&
                data['categoryId'] !== '0' &&
                data['author'] !== '0') {
                $(blk_btn).removeClass('cmdm-hidden')
                    .off('click')
                    .on('click', function () {
                        data.action = 'cmdm_bulk_download';
                        $.ajax({
                            url: CMDM_Files_List_Settings.ajaxurl,
                            type: 'POST',
                            data: data,
                            xhrFields: {
                                responseType: 'blob'
                            },
                            success: function (response, status, xhr) {
                                var anchor = document.createElement("a");
                                var url = window.URL || window.webkitURL;
                                anchor.href = url.createObjectURL(response);
                                var filename = "";
                                var disposition = xhr.getResponseHeader('Content-Disposition');
                                if (disposition && disposition.indexOf('attachment') !== -1) {
                                    var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                                    var matches = filenameRegex.exec(disposition);
                                    if (matches != null && matches[1]) filename = matches[1].replace(/['"]/g, '');
                                }
                                anchor.download = filename;
                                document.body.append(anchor);
                                anchor.click();
                                setTimeout(function () {
                                    document.body.removeChild(anchor);
                                    url.revokeObjectURL(anchor.href);
                                }, 1);
                            },
                            error: function () {
                                console.log("error");
                            }
                        });
                    });
            } else {
                $(blk_btn).addClass('cmdm-hidden').off('click');
            }
        }

        var getCategory = function (form) {
            var categoryId = '';
            var categoryFilter = form.find('.cmrm-files-list-filter-category select');
            if (categoryFilter.length > 0) {
                for (var i = categoryFilter.length - 1; i >= 0; i--) {
                    if (categoryFilter[i].value) {
                        categoryId = categoryFilter[i].value;
                        break;
                    }
                }
            } else if (typeof $('.cmrm-files-list-filter-category .cmdm-filter-cat-flex') != 'undefined') {
                if ($('.cmdm-filter-cat-item.active').length) {
                    categoryFilter = [];
                    $('.cmdm-filter-cat-item.active').each((ind, el) => {
                        categoryFilter.push($(el).attr('data-value'));
                    });
                    categoryId = categoryFilter.join(',');
                }
            }
            if (isNaN(parseInt(categoryId))) categoryId = form.parents('.cmdm-files-list-widget').first().data('categoryId');
            return categoryId;
        };

        var getAuthor = function (form) {
            var input = $('.cmdm-files-list-filter-author select', form);
            if (input.length == 1) return input.val();
        };

        var getMonth = function (form) {
            var input = $('.cmrm-files-list-month-filter-handler', form);
            if (input.length == 1) return input.val();
        };

        var getYear = function (form) {
            var input = $('.cmrm-files-list-year-filter-handler', form);
            if (input.length == 1) return input.val();
        };

        var searchTimeout;

        $('.cmdm-files-list-search-form', container).on("submit", function (ev) {

            ev.stopPropagation();
            ev.preventDefault();
            var form = $(this);
            var input = form.find('.cmdm-search');
            input.data('lastValue', input.val());
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function () {

                var widget = form.parents('.cmdm-files-list-widget').first();
                var contentContainer = widget.find('.cmdm-files-list-content');
                var val = input.val();
                if (!val) val = '';
                var widgetCacheId = widget.data('widgetCacheId');
                if (!form.data('prevWidgetCacheId')) {
                    form.data('prevWidgetCacheId', widgetCacheId);
                }
                if (val.length == 0) {
                    widgetCacheId = form.data('prevWidgetCacheId');
                }

                var data = {
                    widgetCacheId: widgetCacheId,
                    query: val,
                    categoryId: getCategory(form),
                    author: getAuthor(form),
                    month: getMonth(form),
                    year: getYear(form)
                };

                $(document).trigger("extendFileListData", data);

                requestCall(CMDM_Files_List_Settings.ajaxurl, data, contentContainer);
                toggleBulkDownloadButton(data);
            }, 500);
        });
        var search_field = $('.cmdm-files-list-search-form input.cmdm_search');
        if (search_field.length && search_field.value != '') {
            $('.cmdm-files-list-search-form').submit();
        }

        $pageBody.on('click', '.cmdm-files-list-content .cmdm-pagination a', function (ev) {
            ev.stopPropagation();
            ev.preventDefault();

            let link = $(this);

            var widget = link.parents('.cmdm-files-list-widget').first();
            var categoryContainer = link.parents('.cmdm-files-list-category').first();

            var data = {page: link.data('page')};

            var container, responseSelector;

            if (categoryContainer.length) {
                data.categoryId = categoryContainer.data('categoryId');
                container = categoryContainer;
                responseSelector = '.cmdm-files-list-category';
            } else {
                container = widget.find('.cmdm-files-list-content');
            }

            requestCall(link.attr('href'), data, container, responseSelector);
        });

        $pageBody.on('change', '.js-files-list-filter-tags__check-all', function (e) {
            e.preventDefault();

            const $checkbox = $(this);
            const $scope = $checkbox.closest('.cmdm-tags-filter-list');
            if ($checkbox.prop('checked')) {
                $scope.find('input').prop('checked', true);
            } else {
                $scope.find('input').prop('checked', false);
            }

            return false;
        });

    };

    // console.log(CMDM_Files_List_Settings.uploadPage);
    if ((typeof CMDM_Files_List_Settings !== "undefined") && CMDM_Files_List_Settings.uploadPage == 0) {
        $('.cmdm-files-list-upload-btn').click(function (ev) {

            ev.stopPropagation();
            ev.preventDefault();

            var obj = $(this);
            var widget = obj.parents('.cmdm-files-list-widget').first();
            var content = widget.find('.cmdm-files-list-content');

            if (!content.hasClass('cmdm-loading')) {
                widget.find('.cmdm-files-list-content > *').remove();
                var loader = $('<div />', {"class": "cmdm-loader"});
                content.addClass('cmdm-loading');
                content.append(loader);
            }

            content.show();

            var iframe = $('<iframe/>', {src: CMDM_Files_List_Settings.uploadUrl, "class": "cmdm-files-list-upload"});
            iframe.on('load', function () {
                loader.remove();
                content.removeClass('cmdm-loading');
            });
            widget.find('.cmdm-files-list-content').append(iframe);

        });
    }

    // Initialize handlers
    initHandlers($('.cmdm-files-list-widget'));

    //////////////////Read more button start////////////////////////
    $(document).on('click', '.cmdm-list-item-desc .cmdm_read_more_btn', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        var hidden_text = $(this).parent().find('span.cmdm_readmore_content');
        hidden_text_condition = hidden_text.css('display');
        if (hidden_text_condition == 'none') {
            hidden_text.show();
            $(this).html('&#9650;');
        } else {
            hidden_text.hide();
            $(this).html('&#9660;');
        }
        $('.CMDM-tiles-view .cmdm-list-view').waterfall({refresh: 0});

    });
    //////////////////Read more button ends////////////////////////

    var performRequest = function (container, url, data) {
        var widgetContainer = container.parents('.cmdm-widget.ajax');
        if (url.indexOf('widgetCacheId') == -1) {
            data.widgetCacheId = widgetContainer.data('widgetCacheId');
        }
        container.addClass('cmdm-loading');
        container.append($('<div/>', {"class": "cmdm-loader"}));
        $.ajax({
            method: "GET",
            url: url,
            data: data,
            success: function (response) {
                var code = $('<div>' + response + '</div>');
                var newContainer = code.find('.cmdm-widget-content').first().clone();
                container.before(newContainer).remove();
                container = newContainer;
                initHandlers(container);
            }
        });
    };


    var initHandlers = function (container) {
        searchHandlerInit(container);
        categoryHandlerInit(container);
        if (typeof jQuery.selectbox != 'undefined') {
            container.find('select').selectbox('detach');
        }
    };


    var searchHandlerInit = function (container) {
        $('form.cmdm-search-form', container).submit(function () {
            var form = $(this);
            var data = {};
            form.find(':input[name]').each(function () {
                data[this.name] = this.value;
            });
            performRequest(
                form.parents('.cmdm-widget.ajax').find('.cmdm-widget-content'),
                form.attr('action'),
                data
            );
            return false;
        });
    };


    var categoryHandlerInit = function (container) {
        $('a.cmdm-category-link, .cmdm-widget-pagination a', container).click(function (e) {

            // Allow to use middle-button to open link in a new tab:
            if (e.which > 1 || e.shiftKey || e.altKey || e.metaKey || e.ctrlKey) return;

            e.preventDefault();
            e.stopPropagation();

            var link = $(this);
            var widgetContainer = link.parents('.cmdm-widget.ajax');
            var data = {widgetCacheId: widgetContainer.data('widgetCacheId')};
            performRequest(
                widgetContainer.find('.cmdm-widget-content'),
                this.href,
                data
            );

            return false;

        });
    };

    initHandlers($('.cmdm-widget.ajax'));

    if (typeof jQuery.selectbox != 'undefined') {
        $('.cmdm-widget select').selectbox('detach');
    }

    $('.cmdm-files-list-item').find('form').on('submit', function (e) {
        var enteredEmail = $(this).find('input[name=download_user_email]').val();
        if (enteredEmail && enteredEmail.length < 5) {
            e.preventDefault();
            alert('Invalid Email');
        }
    });

    $(document).on('click','.cmdm-archive-items ul li', function(e){
        if(e.target.tagName.toLowerCase() != 'a'){
            let href = $(this).find('a.download').attr('href');
            if (href) {
                window.location.href = href; // Redirect to the link
            }
        }
    });

});
