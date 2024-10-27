const BASE_URL = "https://paidapi.lezgo.ai";
// const BASE_URL = "http://127.0.0.1:8090";

jQuery(document).ready(function ($) {
    var fbRender = $('#rgfb-form-render');
    formStructure = formData.data;
    console.log(formStructure)

    formRenderOpts = {
        formData: formStructure,
        dataType: 'json',
        i18n: {
            locale: 'en-US',
            location: 'https://formbuilder.online/assets/lang/'
        }
    };

    $(fbRender).formRender(formRenderOpts);

    //Custom Style Apply
    if (formData.custom_style == 'on') {
        if (formData.custom_form_bgcolor != '') {
            $('#rgfb-form-render').css('background-color', formData.custom_form_bgcolor)
        }

        if (formData.custom_font_family != '') {
            $('#rgfb-form-render').css('font-family', formData.custom_font_family)
        }
        if (formData.custom_heading_font_size != '') {
            $('#rgfb-form-render h1').css('font-size', formData.custom_heading_font_size)
        }
        if (formData.custom_label_font_size != '') {
            console.log(formData.custom_label_font_size)
            $('#rgfb-form-render  .form-group label').css('font-size', formData.custom_label_font_size)
        }
        if (formData.custom_form_width != '') {
            $('#rgfb-form-div').css('width', formData.custom_form_width)
        }
    }

    // When User Submit the Form
    $('#rgfb-form-render').on('submit', function (e) {
        e.preventDefault();

        generateResponse();
    });


    function generateResponse() {
        $('#rgfb-form-render').find('button[type="submit"]').prop('disabled', true);
        $('#ai-output').remove();
        $('.ai_image_output').remove();
        $('#rgfb-form-render')
            .append(`
                <pre id="ai-output">
                    <div class="loader">
                        <div class="dot"></div>
                        <div class="dot"></div>
                        <div class="dot"></div>
                    </div>
                </pre>`);

        let dataArray = $('#rgfb-form-render').serializeArray();


        let prompt = '';
        let text_prompt = '';
        let image_prompt = '';
        let audio_prompt = '';
        $(dataArray).each(function (i, field) {

            let id = field.name;
            let value = field.value;

            if ($('[name="' + field.name + '"]').hasClass('text-prompt')) {
                text_prompt = $('[name="' + field.name + '"]').val();
            }

            if ($('[name="' + field.name + '"]').hasClass('image-prompt')) {
                image_prompt = $('[name="' + field.name + '"]').val();
            }

            if ($('[name="' + field.name + '"]').hasClass('audio-prompt')) {
                audio_prompt = $('[name="' + field.name + '"]').val();
            }

            if (!$('[name="' + field.name + '"]').hasClass('text-prompt') && !$('[name="' + field.name + '"]').hasClass('image-prompt') && !$('[name="' + field.name + '"]').hasClass('audio-prompt')) {
                let label = $("label[for='" + id + "']").text();
                prompt += ' ' + label + ': ' + value + '\n';
            }


        });

        console.log('Prompt', prompt);
        console.log('text_prompt', text_prompt);
        console.log('image_prompt', image_prompt);
        console.log('audio_prompt', audio_prompt);

        let include_post_or_page_title_in_the_prompt = $('#include_post_or_page_title_in_the_prompt').val();
        console.log('include_post_or_page_title_in_the_prompt', include_post_or_page_title_in_the_prompt);
        if (include_post_or_page_title_in_the_prompt=='on') {
            prompt += $('#rgfb_post_title').val();
        }

        let openai_api_key = $('#openai_api_key').val();
        let openai_model = $('#openai_model').val();
        let image_generation_model = $('#image_generation_model').val();
        let image_generation_strength = $('#image_generation_strength').val();
        let dalle_image_size = $('#dalle_image_size_select').val();
        let sizesArray = dalle_image_size.split('x').map(Number);
        let imgWidth = sizesArray[0];
        let imgHeight = sizesArray[1];

        let output_type = null;
        if ($('.text-prompt').length && !$('.image-prompt').length) {
            output_type = 'text';
        } else if (!$('.text-prompt').length && $('.image-prompt').length) {
            output_type = 'image';

            let limit_image_requests_by_ip = $('#limit_image_requests_by_ip').val();
            let number_of_image_requests_by_ip = $('#number_of_image_requests_by_ip').val();
            console.log('limit_image_requests_by_ip', limit_image_requests_by_ip);
            console.log('number_of_image_requests_by_ip', number_of_image_requests_by_ip);
            if (parseInt(number_of_image_requests_by_ip) >= parseInt(limit_image_requests_by_ip)) {
                const outputText = $("#ai-output");

                let answer = 'Your image generation limit is crossed, please subscribe for more generation.'

                console.log('Response', answer);

                function typeText(index) {
                    if (index < answer.length) {
                        outputText.text(answer.slice(0, index + 1));
                        setTimeout(function () {
                            typeText(index + 1);
                        }, 5);
                    }
                }

                typeText(0);

                $('#rgfb-form-render').find('button[type="submit"]').prop('disabled', false);
                return false;
            }
        } else if ($('.text-prompt').length && $('.image-prompt').length) {
            output_type = 'text_image';

            let limit_image_requests_by_ip = $('#limit_image_requests_by_ip').val();
            let number_of_image_requests_by_ip = $('#number_of_image_requests_by_ip').val();
            console.log('limit_image_requests_by_ip', limit_image_requests_by_ip);
            console.log('number_of_image_requests_by_ip', number_of_image_requests_by_ip);
            if (parseInt(number_of_image_requests_by_ip) >= parseInt(limit_image_requests_by_ip)) {
                const outputText = $("#ai-output");

                let answer = 'Your image generation limit is crossed, please subscribe for more generation.'

                console.log('Response', answer);

                function typeText(index) {
                    if (index < answer.length) {
                        outputText.text(answer.slice(0, index + 1));
                        setTimeout(function () {
                            typeText(index + 1);
                        }, 5);
                    }
                }

                typeText(0);

                $('#rgfb-form-render').find('button[type="submit"]').prop('disabled', false);
                return false;
            }
        }

        // Define the URL and POST data
        const url = BASE_URL + '/ask';



        // Create a FormData object and append the file and other data
        const formData = new FormData();
        formData.append('prompt', prompt);
        formData.append('text_prompt', text_prompt);
        formData.append('image_prompt', image_prompt);
        formData.append('openai_api_key', openai_api_key);
        formData.append('output_type', output_type);
        formData.append('openai_model', openai_model);
        formData.append('image_generation_model', image_generation_model);
        formData.append('image_generation_strength', image_generation_strength);
        formData.append('dalle_image_size', dalle_image_size);
        formData.append('audio_prompt', audio_prompt);
        // Get the file input element
        const fileInput = document.querySelector('input[type="file"]');

        if (fileInput) {
            const file = fileInput.files[0];  // Assuming you only want to handle one file
            formData.append('file', file);  // Append the file to the FormData
        }

        // Create a fetch request
        fetch(url, {
            method: 'POST',
            body: formData,
        }).then((response) => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        }).then((data) => {
            const outputText = $("#ai-output");
            let status = data?.status;
            let answer = data?.answer;
            let image_url = data?.image_url;
            let audio_url = data?.audio_url;
            let completion_tokens = data?.completion_tokens;
            let prompt_tokens = data?.prompt_tokens;
            let total_cost = data?.total_cost;
            let total_tokens = data?.total_tokens;

            if (!status) {
                answer = 'Something went wrong, please try again later.'
            }
            console.log('Response', answer);

            if (output_type == 'text') {

                function typeText(index) {
                    if (index < answer.length) {
                        outputText.text(answer.slice(0, index + 1));
                        setTimeout(function () {
                            typeText(index + 1);
                        }, 5); // Adjust the time interval for typing speed
                    } else {
                        $('#ai-output').html(answer);
                        makeTextH3();
                        makeTextBold();
                        makeTextStrong();
                    }
                }

                typeText(0);

                if (formData.custom_style == 'on') {
                    if (formData.custom_output_font_size != '') {
                        console.log(formData.custom_output_font_size)
                        $('#rgfb-form-render  #ai-output').css('font-size', formData.custom_output_font_size)
                    }
                    if (formData.custom_output_font_family != '') {
                        $('#rgfb-form-render #ai-output').css('font-family', formData.custom_output_font_family)
                    }
                }
            } else if (output_type == 'image') {
                var imgElement = $('<img class="ai_image_output mb-5" width="' + imgWidth + '" height="' + imgHeight + '">').attr('src', image_url);
                var divElement = $('<div class="text-center">').append(imgElement);
                $("#ai-output").html(divElement);
            } else if (output_type == 'text_image') {
                var imgElement = $('<img class="ai_image_output mb-5" width="' + imgWidth + '" height="' + imgHeight + '">').attr('src', image_url);
                var divElement = $('<div class="text-center">').append(imgElement);
                $("#ai-output").before(divElement);

                function typeText(index) {
                    if (index < answer.length) {
                        outputText.text(answer.slice(0, index + 1));
                        setTimeout(function () {
                            typeText(index + 1);
                        }, 10);
                    } else {
                        $('#ai-output').html(answer);
                        makeTextH3();
                        makeTextBold();
                        makeTextStrong();
                    }

                }

                typeText(0);


                if (formData.custom_style == 'on') {
                    if (formData.custom_output_font_size != '') {
                        console.log(formData.custom_output_font_size)
                        $('#rgfb-form-render  #ai-output').css('font-size', formData.custom_output_font_size)
                    }
                    if (formData.custom_output_font_family != '') {
                        $('#rgfb-form-render #ai-output').css('font-family', formData.custom_output_font_family)
                    }
                }
            }
            if (audio_prompt) {
                audio_html = `<audio controls autoplay>
                    <source src="${audio_url}" type="audio/mp3">
                    Your browser does not support the audio element.
                </audio>`;
                console.log(audio_html);
                $("#ai-output").after(audio_html);
            }

            $('#rgfb-form-render').find('button[type="submit"]').prop('disabled', false);

            var form_postData = `openai_api_key=${openai_api_key}&output_type=${output_type}&openai_model=${openai_model}&completion_tokens=${completion_tokens}&prompt_tokens=${prompt_tokens}&total_cost=${total_cost}&total_tokens=${total_tokens}&prompt=${prompt}&answer=${answer}&image_url=${encodeURIComponent(image_url)}`;
            var nonce = rgFormRenderAjax.nonce;
            $.ajax({
                type: 'POST',
                url: rgFormRenderAjax.ajaxurl,
                data: {
                    action: 'rgfb_save_llm_logs',
                    formData: form_postData,
                    nonce: nonce
                },
                dataType: 'JSON',
                success: function (response) {

                }
            });

        }).catch((error) => {
            console.error('Error:', error);
            const outputText = $("#ai-output");

            let answer = 'Something went wrong, please try again later.'

            console.log('Response', answer);

            function typeText(index) {
                if (index < answer.length) {
                    outputText.text(answer.slice(0, index + 1));
                    setTimeout(function () {
                        typeText(index + 1);
                    }, 5);
                }
            }

            typeText(0);

            $('#rgfb-form-render').find('button[type="submit"]').prop('disabled', false);
        });

    }

    if ($('#is_load_response_on_page_load').val() == 'on') {
        generateResponse();
    }
});

function makeTextBold() {
    var container = document.getElementById('ai-output');
    container.innerHTML = container.innerHTML.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
}

function makeTextH3() {
    var container = document.getElementById('ai-output');
    container.innerHTML = container.innerHTML.replace(/\*\*\*(.*?)\*\*\*/g, '<h3>$1</h3>');
}

function makeTextStrong() {
    var container = document.getElementById('ai-output');
    container.innerHTML = container.innerHTML.replace(/\*(.*?)\*/g, '<strong>$1</strong>');
}
