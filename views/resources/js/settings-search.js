jQuery(function($) {
	
	var highlightTextInNode = function(node, text) {
//		console.log(node);
		if (!text || text.length === 0 || !node) return;
		if (node.parentNode.nodeName === 'SPAN' && node.parentNode.className === 'cmdm-hl' || node.parentNode.nodeName === 'TEXTAREA') {
			return;
		}
		else if (node.nodeType === document.ELEMENT_NODE) {
			if (node.nodeName === 'SPAN' && node.className === 'cmdm-hl') {
				// do nothing
			}
			else for (let i=0; i<node.childNodes.length; i++) {
				highlightTextInNode(node.childNodes[i], text);
			}
		}
		else if (node.nodeType === document.TEXT_NODE) {
//			console.log(node.textContent);
			var pos = node.textContent.toLowerCase().indexOf(text.toLowerCase());
			if (pos > -1) {
//				console.log(node.textContent);
				var html = node.textContent.substr(0, pos) + '<span class="cmdm-hl">'
					+ node.textContent.substr(pos, text.length) + '</span>' + node.textContent.substr(pos+text.length, node.textContent.length);
				$(node).replaceWith(html);
			}
		}
	};
	var clearHighlight = function(node) {
		$(node).find('.cmdm-hl').each(function() {
			let text = $(this).text();
			let outerHTML = $(this).parent().html();
//			console.log(outerHTML);
			if (outerHTML) {
				outerHTML = outerHTML.replace(/<span class="cmdm-hl">(.+)<\/span>/g, text);
				$(this).parent().html(outerHTML);
			}
//			$(this).replaceWith(this.textContent);
		});
	};
	
	let settingsSearchTimeout = null;
	$('#cmdm_settings_search').keyup(function() {
		if (this.lastValue === this.value) return;
		this.lastValue = this.value;
		let input = $(this);
		clearTimeout(settingsSearchTimeout);
		settingsSearchTimeout = setTimeout(function() {
			clearHighlight(document.getElementById('cm-downloads-settings-form'));
			highlightTextInNode(document.getElementById('cm-downloads-settings-form'), input.val());
			runSettingsSearch(input);
		}, 500);
	});

	let runSettingsSearch = function(input) {
		// Show or hide clear btn
			$('#cmdm_settings_search_clear').toggle((input.val().length !== 0));
		// Search in rows
		$('#cm-downloads-settings-form .cm-settings-row').each(function() {
			let row = $(this);
			if (input.val().length === 0 || this.textContent.toLowerCase().indexOf(input.val().toLowerCase()) > -1) {
				row.show();
			} else {
				row.hide();
			}
		});
		// Hide sections
		$('#cm-downloads-settings-form .cmdm-settings-section').each(function() {
			let section = $(this);
			if (input.val().length === 0 || this.textContent.toLowerCase().indexOf(input.val().toLowerCase()) > -1) {
				section.show();
				if (input.val().length > 0) {
					console.log('open');
					section.find('.cm-settings-collapse-container').show().toggleClass('cm-settings-collapse-close cm-settings-collapse-open');
					section.find('.cm-settings-collapse-btn .dashicons-arrow-right').toggleClass('dashicons-arrow-right dashicons-arrow-down');
				} else {
					console.log('close');
					section.find('.cm-settings-collapse-container').hide().toggleClass('cm-settings-collapse-open cm-settings-collapse-close');
					section.find('.cm-settings-collapse-btn.dashicons-arrow-down').toggleClass('dashicons-arrow-down dashicons-arrow-right');
				}
			} else {
				section.hide();
			}
		});
		// Hide tabs
		$('#cm-downloads-settings-form .cmdm-tab-content').each(function() {
			let tab = $('#cmdm-tab-menu a[href="#'+ this.id +'"]');
			if (input.val().length === 0 || this.textContent.toLowerCase().indexOf(input.val().toLowerCase()) > -1) {
				tab.show();
			} else {
				tab.hide();
			}
		});
		if ($('#cmdm-tab-menu .ui-state-active a:visible').length === 0) {
			$('#cmdm-tab-menu li a:visible').first().click();
		}
	};
	$('#cmdm_settings_search_clear').on('click', function() {
		$('#cmdm_settings_search').val('').trigger('keyup');
	});
	
	

	$('.cm-settings-collapse-btn').on('click', function() {
		let time = 300;
		let btn = $(this);
		let content = btn.next();
		if (content.hasClass('cm-settings-collapse-open')) {
			content.slideUp(time, function() {
				content.toggleClass('cm-settings-collapse-open cm-settings-collapse-close');
			});
		} else {
			content.slideDown(time, function() {
				content.toggleClass('cm-settings-collapse-open cm-settings-collapse-close');
			});
		}
		btn.toggleClass('dashicons-arrow-down dashicons-arrow-right');
	});
	
	
	$('.cm-settings-collapse-toggle').on('click', function() {
		let container = $(this).parents('.cmdm-tab-content');
		if (container.find('.cm-settings-collapse-open').length === 0) {
			container.find('.cm-settings-collapse-btn').click();
		} else {
			container.find('.cm-settings-collapse-open').parents('.cmdm-settings-section').find('.cm-settings-collapse-btn').click();
		}
	});
	
		
});
