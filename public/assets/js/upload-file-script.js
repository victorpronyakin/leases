$(document).ready(function() {
    jQuery(this).on('change', '.upload-file-container__custom-upload', function (value) {
        var shortMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        var url = value.target.value;
        if (url) {
            var parent =  jQuery(this).parent();
            const parseUrl = url.split('\\');
            const date = new Date();
            const dateStr = date.getDate() + ' ' + shortMonths[date.getMonth()] + ' ' + date.getFullYear();
            parent.addClass('uploaded');
            const fileNameTag = parent.find('.upload-file-container__file-uploaded > .upload-file-container__file-name');
            fileNameTag.text(parseUrl[parseUrl.length - 1]);
            parent.find('.upload-file-container__file-uploaded > .upload-file-container__file-date')
                .text(dateStr);

            $(this).parent().find('.text-danger').hide();
        }
    });

    jQuery(document).on('click', '.upload-file-container__close', function () {
        var parent = jQuery(this).parent().parent();
        parent.find('.upload-file-container__custom-upload')[0].value = '';
        parent.find('.upload-file-container__fake-upload')[0].value = '';
        parent.removeClass('uploaded');
        parent.find('.text-danger').show();
    });

    jQuery(document).on('click', '.upload-file-container__upload-button', function (e) {
        e.preventDefault();
        jQuery(this).parent().find('.upload-file-container__custom-upload').trigger('click');
    })
});
