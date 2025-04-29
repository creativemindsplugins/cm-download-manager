jQuery(document).ready(function($) {
    $('a:has(.cmseparator)').replaceWith(function() {
        return $(this).contents();
    });
	$('.cmdm-cleanup-button').trigger('click');
});