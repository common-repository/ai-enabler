jQuery(document).ready(function ($) {
    var trash = $('#trash')
    var saveData = $('#save-settings')
    var save_settings_nonce = $('#save_settings_nonce').val();
    saveData.on('click', function (e) {
        e.preventDefault();
        var formData = $('#rgfb_settings').serializeArray();

        $.ajax({
            type: 'POST',
            url: ajaxurl, // WordPress AJAX URL
            data: {
                action: 'rgfb_save_settings',
                formData: formData,
                nonce: save_settings_nonce,
            },
            dataType: 'JSON',
            success: function (response) {
                $.alert({
                    title: (response.status) ? 'Success' : 'Warning',
                    content: response.msg,
                });
            }
        });
    })

    trash.on('click', function () {
        formBuilder.actions.clearFields()
    })
});