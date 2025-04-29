(function ($) {

    $(document).ready(function () {
        const pageBody = $('body');
        pageBody.on('click', 'form.CMDM-downloadForm .downloadBut', function(e) {
            e.preventDefault();
            $(this).closest('form').find('input[type=submit]').click();
        });

        pageBody.on('change', '.cmdm-download-files-list', function() {
            var obj = $(this);
            var selected = obj.find('option:selected');
            obj.parents('form').attr('action', selected.data('url'));
        });

        pageBody.on('click', '.dashicons-lock', function(){
            const lockBtn = $(this);
            const parentContainer = lockBtn.closest('form');
            const passwordBlock = parentContainer.find('.cmdm-password-label-wrapper');
            passwordBlock.toggle();
        });


        pageBody.on('click', '.for_show_password', function(){
            const eyeToShowPassword = $(this);
            const parentContainer = eyeToShowPassword.closest('label');
            const inputPassword = parentContainer.find('input');
            inputPassword.attr('type', 'text');
            eyeToShowPassword.removeClass('dashicons-visibility').addClass('dashicons-hidden')
        });

        pageBody.on('click', '.dashicons-hidden', function(){
            const eyeToShowPassword = $(this);
            const parentContainer = eyeToShowPassword.closest('label');
            const inputPassword = parentContainer.find('input');
            inputPassword.attr('type', 'password');
            eyeToShowPassword.removeClass('dashicons-hidden').addClass('dashicons-visibility')
        });
    });

})(jQuery);
