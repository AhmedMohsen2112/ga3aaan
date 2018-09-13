
var Notifications = function () {

    var init = function () {
        //$.extend(lang, new_lang);
        handleSubmit();

    };

    var handleSubmit = function () {

        $('#notificationsForm').validate({
            rules: {
                title: {
                    required: true
                },
                message: {
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
        $('#notificationsForm .submit-form').click(function () {
            if ($('#notificationsForm').validate().form()) {
                $('#notificationsForm .submit-form').prop('disabled', true);
                $('#notificationsForm .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                setTimeout(function () {
                    $('#notificationsForm').submit();
                }, 500);
            }
            return false;
        });
        $('#notificationsForm input').keypress(function (e) {
            if (e.which == 13) {
                if ($('#notificationsForm').validate().form()) {
                    $('#notificationsForm .submit-form').prop('disabled', true);
                    $('#notificationsForm .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                    setTimeout(function () {
                        $('#notificationsForm').submit();
                    }, 500);
                }
                return false;
            }
        });



        $('#notificationsForm').submit(function () {
            var action = config.admin_url + '/notifications';
            var formData = new FormData($(this)[0]);
            $.ajax({
                url: action,
                data: formData,
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    $('#notificationsForm .submit-form').prop('disabled', false);
                    $('#notificationsForm .submit-form').html(lang.save);

                    if (data.type == 'success')
                    {

                       My.toast(data.message);
                       My.emptyForm();

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
                    $('#notificationsForm .submit-form').prop('disabled', false);
                    $('#notificationsForm .submit-form').html(lang.save);
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
    Notifications.init();
});
