jQuery(function($) {
	//////////////////Read more button start////////////////////////
	if($('.cmdm-list-item-desc .cmdm_read_more_btn').length>0){
		$('.cmdm-list-item-desc .cmdm_read_more_btn').click(function(e){
			
			var hidden_text = $(this).parent().find('span.cmdm_readmore_content');
			hidden_text_condition = hidden_text.css('display');
			if(hidden_text_condition=='none'){
				hidden_text.show();
			}else{
				hidden_text.hide();
			}
		});
	}
	//////////////////Read more button ends////////////////////////
	var performRequest = function(container, url, data) {
		var widgetContainer = container.parents('.cmdm-widget.ajax');
		if (url.indexOf('widgetCacheId') == -1) {
			data.widgetCacheId = widgetContainer.data('widgetCacheId');
		}
		container.addClass('cmdm-loading');
		container.append($('<div/>', {"class":"cmdm-loader"}));
		$.ajax({
			method: "GET",
			url: url,
			data: data,
			success: function(response) {
				var code = $('<div>' + response +'</div>');
				var newContainer = code.find('.cmdm-widget-content').first().clone();
				container.before(newContainer).remove();
				container = newContainer;
				initHandlers(container);
			}
		});
	};
	
	
	var initHandlers = function(container) {
		searchHandlerInit(container);
		categoryHandlerInit(container);
		if (typeof jQuery.selectbox != 'undefined') {
			container.find('select').selectbox('detach');
		}
	};
	
	
	
	var searchHandlerInit = function(container) {
		$('form.cmdm-search-form', container).submit(function() {
			var form = $(this);
			var data = {};
			form.find(':input[name]').each(function() {
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
	
	
	var categoryHandlerInit = function(container) {
		$('a.cmdm-category-link, .cmdm-widget-pagination a', container).click(function(e) {
			
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
	
});
