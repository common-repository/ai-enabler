
jQuery(document).ready(async function ($) {
    const fbEditor = document.getElementById('edit-form-build-wrap')
    var formStructure = formData.data;
    var trash = $('#trash')
    var saveData = $('#save-data')
    var options = {
        formData: formStructure,
        dataType: 'json',
        disableFields: ['autocomplete', 'date', 'paragraph', 'starRating', 'number', 'hidden'],
        fieldRemoveWarn: true,
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
            }, textPrompt: function (fieldData) {
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

    formBuilder = await $(fbEditor).formBuilder(options);
    console.log('formData', formData);
    var inputs = $('#custom_form_style_element :input');
    if (formData.is_load_response_on_page_load == 'on') {
        $('#rgfb_is_load_response_on_page_load').prop('checked', true);
    }
    $('#rgfb_dalle_image_size_select').val(formData.dalle_image_size_select);
    if (formData.include_post_or_page_title_in_the_prompt == 'on') {
        $('#include_post_or_page_title_in_the_prompt').prop('checked', true);
    }
    if (formData.custom_style == 'on') {
        inputs.each(function () {
            $(this).removeAttr('disabled');
        });
        $('#rgfb_custom_form_style_chk').attr('checked', true);
        $('#rgfb_custom_form_color').val(formData.custom_form_bgcolor)
        $('#rgfb_custom_font_family').val(formData.custom_font_family);
        $('#rgfb_custom_heading_font_size').val(formData.custom_heading_font_size);
        $('#rgfb_custom_label_font_size').val(formData.custom_label_font_size);
        $('#rgfb_custom_output_font_size').val(formData.custom_output_font_size);
        $('#rgfb_custom_output_font_family').val(formData.custom_output_font_family);
        $('#rgfb_custom_form_width').val(formData.custom_form_width);

    } else {

        inputs.each(function () {
            $(this).attr('disabled', 'disabled');
        })
        $("input[type=radio][name=layout][value=" + formData.style + "]").prop("checked", true);
    }


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
        var edit_form_nonce = $('#edit_form_nonce').val();
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
            custom_label_font_size = $('#rgfb_custom_label_font_size').val();
            custom_output_font_size = $('#rgfb_custom_output_font_size').val();
            custom_output_font_family = $('#rgfb_custom_output_font_family').val();
            custom_form_width = $('#rgfb_custom_form_width').val();
        } else {
            custom_style = 'off';
            style = $('input[name="layout"]:checked').val();
        }
        var item = $('#rgfb_formbuilder_item').val();

        var custom_style_postData = `custom_style=${custom_style}&custom_form_color=${custom_form_color}&custom_font_family=${custom_font_family}&custom_heading_font_size=${custom_heading_font_size}&custom_label_font_size=${custom_label_font_size}&custom_output_font_size=${custom_output_font_size}&custom_output_font_family=${custom_output_font_family}&custom_form_width=${custom_form_width}`;
        var form_postData = `formname=${formName}&is_load_response_on_page_load=${is_load_response_on_page_load}&dalle_image_size_select=${dalle_image_size_select}&include_post_or_page_title_in_the_prompt=${include_post_or_page_title_in_the_prompt}&style=${style}&id=${item}&${custom_style_postData}&data=${JSON.stringify(formData)}`;
        $.ajax({
            type: 'POST',
            url: ajaxurl, // WordPress AJAX URL
            data: {
                action: 'update_rgfb_form_builder',
                formData: form_postData,
                nonce: edit_form_nonce,
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
    });

    $(document).on('click', '#rgfb_custom_form_style_chk', function () {
        if ($(this).is(':checked')) {
            $('#custom_form_style_element').removeClass('d-none');
        } else {
            $('#custom_form_style_element').addClass('d-none');
        }
    });
});

