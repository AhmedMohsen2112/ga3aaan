
var Settings = function () {

    var init = function () {
        //$.extend(lang, new_lang);
        handleSubmit();

    };

    var handleSubmit = function () {

        $('#editSettingsForm').validate({
            rules: {
                title_ar: {
                    required: true
                },
                title_en: {
                    required: true
                },
                phone: {
                    required: true
                },
                email: {
                    required: true
                },
                address_ar: {
                    required: true
                },
                address_en: {
                    required: true
                },
                about_us_ar: {
                    required: true
                },
                about_us_en: {
                    required: true
                },
                usage_conditions_ar: {
                    required: true
                },
                usage_conditions_en: {
                    required: true
                },
                terms_conditions_ar: {
                    required: true
                },
                terms_conditions_en: {
                    required: true
                },
 
            },
            messages: lang.messages,
            highlight: function (element) { // hightlight error inputs
                $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
            },
            unhighlight: function (element) {
                $(element).closest('.form-group').removeClass('has-error').addClass('has-success');
                $(element).closest('.form-group').find('.help-block').html('').css('opacity', 0);
            },
            errorPlacement: function (error, element) {
                $(element).closest('.form-group').find('.help-block').html($(error).html()).css('opacity', 1);
            }
        });
        $('#editSettingsForm .submit-form').click(function () {
            if ($('#editSettingsForm').validate().form()) {
                $('#editSettingsForm .submit-form').prop('disabled', true);
                $('#editSettingsForm .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                setTimeout(function () {
                    $('#editSettingsForm').submit();
                }, 500);
            }
            return false;
        });
        $('#editSettingsForm input').keypress(function (e) {
            if (e.which == 13) {
                if ($('#editSettingsForm').validate().form()) {
                    $('#editSettingsForm .submit-form').prop('disabled', true);
                    $('#editSettingsForm .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                    setTimeout(function () {
                        $('#editSettingsForm').submit();
                    }, 500);
                }
                return false;
            }
        });



        $('#editSettingsForm').submit(function () {
            var id = $('#id').val();
            var action = config.admin_url + '/settings/' + id;
            var formData = new FormData($(this)[0]);
            formData.append('_method', 'PATCH');
            $.ajax({
                url: action,
                data: formData,
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    $('#editSettingsForm .submit-form').prop('disabled', false);
                    $('#editSettingsForm .submit-form').html(lang.save);

                    if (data.type == 'success')
                    {

                        toastr.options = {
                            "debug": false,
                            "positionClass": "toast-bottom-left",
                            "onclick": null,
                            "fadeIn": 300,
                            "fadeOut": 1000,
                            "timeOut": 5000,
                            "extendedTimeOut": 1000
                        };
                        toastr.success(lang.updated_successfully, 'رسالة');

                    } else {
                        console.log(data)
                        if (typeof data.errors === 'object') {
                            for (i in data.errors)
                            {
                                $('[name="' + i + '"]')
                                        .closest('.form-group').addClass('has-error');
                                $('#' + i).closest('.form-group').find(".help-block").html(data.errors[i]).css('opacity', 1)
                            }
                        }
                        if (typeof data.message !== 'undefined') {
                            $.confirm({
                                title: lang.error,
                                content: data.message,
                                type: 'red',
                                typeAnimated: true,
                                buttons: {
                                    tryAgain: {
                                        text: lang.try_again,
                                        btnClass: 'btn-red',
                                        action: function () {
                                        }
                                    }
                                }
                            });
                        }
                    }
                },
                error: function (xhr, textStatus, errorThrown) {
                    $('#editSettingsForm .submit-form').prop('disabled', false);
                    $('#editSettingsForm .submit-form').html(lang.save);
                    My.ajax_error_message(xhr);
                },
                dataType: "json",
                type: "POST"
            });

            return false;

        })




    }

    return {
        init: function () {
            init();
        }
    };

}();
jQuery(document).ready(function () {
    Settings.init();
});
