jQuery.noConflict();

jQuery(document).ready(function ($) {
    const fbTemplate = $('#build-wrap');
    var trash = $('#trash');
    var saveData = $('#save-data');

    var options = {
        disableFields: ['autocomplete', 'date', 'paragraph', 'number', 'hidden'],
        /* fieldRemoveWarn: true, */
        showActionButtons: false,
        i18n: {
            locale: 'en-US',
            location: 'https://formbuilder.online/assets/lang/'
        },
        fields: [{
            label: 'Image Prompt',
            attrs: {
                type: 'text',
                subtype: 'imagePrompt'
            },
            icon: '>>',
            className: 'image-prompt',
        }, {
            label: 'Text Prompt',
            attrs: {
                type: 'text',
                subtype: 'textPrompt'
            },
            icon: '>>',
            className: 'text-prompt',
        },
        {
            label: 'Audio Prompt',
            attrs: {
                type: 'text',
                subtype: 'audioPrompt'
            },
            icon: '>>',
            className: 'audio-prompt',
        }],
        templates: {
            imagePrompt: function (fieldData) {
                return {
                    field: '<span id="' + fieldData.name + '">',
                    onRender: function () {

                    }
                };
            },
            textPrompt: function (fieldData) {
                return {
                    field: '<span id="' + fieldData.name + '">',
                    onRender: function () {

                    }
                };
            },
            audioPrompt: function (fieldData) {
                return {
                    field: '<span id="' + fieldData.name + '">',
                    onRender: function () {

                    }
                };
            }
        },
        subtypes: {
            text: ['imagePrompt', 'textPrompt', 'audioPrompt']
        }
    };

    formBuilder = $(fbTemplate).formBuilder(options);

    saveData.on('click', function (e) {
        e.preventDefault();
        if ($('.text-prompt').length == 0 && $('.image-prompt').length == 0) {
            $.alert({
                title: 'Warning',
                content: 'Please add atleast one text or image prompt',
            });
            return false;
        } else if ($('button[type="submit"]').length == 0) {
            $.alert({
                title: 'Warning',
                content: 'Please add submit button',
            });
            return false;
        }
        var formData = formBuilder.actions.getData();
        var save_form_nonce = $('#save_form_nonce').val();
        var formName = $('#rg_frmbulder_form_name').val();
        var is_load_response_on_page_load = $('#rgfb_is_load_response_on_page_load:checked').val();
        var dalle_image_size_select = $('#rgfb_dalle_image_size_select').find('option:selected').val();
        var include_post_or_page_title_in_the_prompt = $('#include_post_or_page_title_in_the_prompt:checked').val();
        var style = '';
        var custom_style = $('#rgfb_custom_form_style_chk:checked').val();
        var custom_form_color = '';
        var custom_font_family = '';
        var custom_heading_font_size = '';
        var custom_label_font_size = '';
        var custom_output_font_size = '';
        var custom_output_font_family = '';
        var custom_form_width = '';
        if (custom_style == 'on') {
            style = '';
            custom_form_color = $('#rgfb_custom_form_color').val();
            custom_font_family = $('#rgfb_custom_font_family').val();
            custom_heading_font_size = $('#rgfb_custom_heading_font_size').val();
            custom_label_font_size = $('#rgfb_custom_label_font_size').val();
            custom_output_font_size = $('#rgfb_custom_output_font_size').val();
            custom_output_font_family = $('#rgfb_custom_output_font_family').val();
            custom_form_width = $('#rgfb_custom_form_width').val();
        } else {
            custom_style = 'off';
            style = $('input[name="layout"]:checked').val();
        }
        var custom_style_postData = `custom_style=${custom_style}&custom_form_color=${custom_form_color}&custom_font_family=${custom_font_family}&custom_heading_font_size=${custom_heading_font_size}&custom_label_font_size=${custom_label_font_size}&custom_output_font_size=${custom_output_font_size}&custom_output_font_family=${custom_output_font_family}&custom_form_width=${custom_form_width}`;
        var form_postData = `formname=${formName}&is_load_response_on_page_load=${is_load_response_on_page_load}&dalle_image_size_select=${dalle_image_size_select}&include_post_or_page_title_in_the_prompt=${include_post_or_page_title_in_the_prompt}&style=${style}&${custom_style_postData}&data=${JSON.stringify(formData)}`;

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'save_rgfb_form_builder',
                formData: form_postData,
                nonce: save_form_nonce
            },
            dataType: 'JSON',
            success: function (response) {
                $.alert({
                    title: (response.status) ? 'Success' : 'Warning',
                    content: response.msg,
                });
                if (response.redirect_url) {
                    window.location.href = response.redirect_url;
                }
            }
        });
    })

    trash.on('click', function () {
        formBuilder.actions.clearFields()
    })

    $(document).on('click', '#rgfb_custom_form_style_chk', function () {
        if ($(this).is(':checked')) {
            $('#custom_form_style_element').removeClass('d-none');
        } else {
            $('#custom_form_style_element').addClass('d-none');
        }
    });

});