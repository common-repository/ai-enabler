jQuery(document).ready(function($) {
    $('.autoplay').slick({
        slidesToShow: 7,
        slidesToScroll: 1,
        autoplay: false,
        autoplaySpeed: 2000,
        responsive: [
            {
              breakpoint: 1024,
              settings: {
                slidesToShow: 4,
                slidesToScroll: 1,
                dots: false
              }
            },
            {
              breakpoint: 600,
              settings: {
                slidesToShow: 3,
                slidesToScroll: 1
              }
            },
            {
              breakpoint: 480,
              settings: {
                slidesToShow: 2,
                slidesToScroll: 1
              }
            }
          ]
    });

    var inputs = $('#custom_form_style_element :input');
    inputs.each(function(){
        $(this).attr('disabled','disabled');
    })
    $('#rgfb_custom_form_style_chk').on('click',function(){
        if ($(this).is(":checked")){
            inputs.each(function(){
                $(this).removeAttr('disabled');
            })
        } else {
            inputs.each(function(){
                $(this).attr('disabled','disabled');
            })
        }
        
    })

    //Apply Button in form list
    $('#doaction, #doaction2').on('click', function() {
        var action = $('#bulk-action-selector-top, #bulk-action-selector-bottom').val();
        if (action === 'delete') {
            var selectedItems=[];
            var checkboxes = document.querySelectorAll('input[type=checkbox]:checked')
            for (var i = 0; i < checkboxes.length; i++) {
                selectedItems.push(checkboxes[i].value)
            }

            if (selectedItems.length==0) {
                $.alert({
                    title: 'Alert!',
                    content: 'No item selected.',
                });
                
            } else {
                $.confirm({
                    title: 'Confirm!',
                    content: 'Do you want to delete the records?',
                    buttons: {
                        confirm: function () {
                            // Execute your custom AJAX logic here
                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'bulk_action',
                                    action_type: action,
                                    ids: selectedItems,
                                    // Add any additional data you need to send
                                },
                                success: function(response) {
                                    $.alert({
                                        title: (response.status) ? 'Success' : 'Warning',
                                        content: response.msg,
                                    });
                                }
                            });
                        },
                        cancel: function () {
                            
                        },
                    }
                });
            }
            
        }
    });
});

