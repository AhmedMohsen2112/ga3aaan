var Login = function () {

    var init = function () {
        handle_login();
        handle_register();
        handle_forgot_password();
        handle_change_password();
        handle_activation_code();
        handle_edit_phone();


    }
    var handle_login = function () {
        $("#login-form").validate({
            rules: {
                username: {
                    required: true,
                },
                password: {
                    required: true,
                }
            },

            highlight: function (element) { // hightlight error inputs
                $(element).closest('.form-group').removeClass('has-success').addClass('has-error');

            },
            unhighlight: function (element) {
                $(element).closest('.form-group').removeClass('has-error').addClass('has-success');
                $(element).closest('.form-group').find('.help-block').html('');

            },
            errorPlacement: function (error, element) {
                errorElements1.push(element);
                $(element).closest('.form-group').find('.help-block').html($(error).html());
            }

        });
        $('#login-form .submit-form').click(function () {
            var validate_2 = $('#login-form').validate().form();
            errorElements = errorElements1.concat(errorElements2);
            if (validate_2) {
                $('#login-form .submit-form').prop('disabled', true);
                $('#login-form .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                setTimeout(function () {
                    $('#login-form').submit();
                }, 1000);

            }
            if (errorElements.length > 0) {
                App.scrollToTopWhenFormHasError($('#login-form'));
            }

            return false;
        });

        $('#login-form input').keypress(function (e) {
            if (e.which == 13) {
                var validate_2 = $('#login-form').validate().form();
                errorElements = errorElements1.concat(errorElements2);
                if (validate_2) {
                    $('#login-form .submit-form').prop('disabled', true);
                    $('#login-form .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                    setTimeout(function () {
                        $('#login-form').submit();
                    }, 1000);

                }
                if (errorElements.length > 0) {
                    App.scrollToTopWhenFormHasError($('#login-form'));
                }

                return false;
            }
        });
        $('#login-form').submit(function () {
            var formData = new FormData($(this)[0]);
            var return_url = App.getParameterByName('return', window.location.href);
            if (return_url !== null) {
                formData.append('return', return_url);
            }
            $.ajax({
                url: config.url + "/login",
                type: 'POST',
                dataType: 'json',
                data: formData,
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data)
                {
                    console.log(data);
                    if (data.type == 'success') {
                        setTimeout(function () {
                            window.location.href = data.message;
                        }, 1000);


                    } else {
                        $('#login-form .submit-form').prop('disabled', false);
                        $('#login-form .submit-form').html(lang.login);
                        if (typeof data.errors === 'object') {
                            for (i in data.errors)
                            {
                                $('[name="' + i + '"]')
                                        .closest('.form-group').addClass('has-error').removeClass("has-info");
                                $('#' + i).closest('.form-group').find(".help-block").html(data.errors[i])
                            }
                        } else {
                            $('#alert-message').removeClass('alert-success').addClass('alert-danger').fadeIn(500).delay(3000).fadeOut(2000);
                            var message = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <span>' + data.message + '</span> ';
                            $('#alert-message').show().html(message);
                        }
                    }


                },
                error: function (xhr, textStatus, errorThrown) {
                    $('#login-form .submit-form').prop('disabled', false);
                    $('#login-form .submit-form').html(lang.login);
                    App.ajax_error_message(xhr);
                },
            });

            return false;
        });

    }
    var handle_forgot_password = function () {
        $("#forgotPasswordForm").validate({
            rules: {
                email: {
                    required: true,
                    email: true
                },
            },
            messages: {
                email: {
                    required: lang.required,
                    email: lang.email_not_valid
                },
            },
            highlight: function (element) { // hightlight error inputs
                $(element).closest('.form-group').removeClass('has-success').addClass('has-error');

            },
            unhighlight: function (element) {
                $(element).closest('.form-group').removeClass('has-error').addClass('has-success');
                $(element).closest('.form-group').find('.help-block').html('');

            },
            errorPlacement: function (error, element) {
                $(element).closest('.form-group').find('.help-block').html($(error).html());
            }

        });
        $('#forgotPasswordForm .submit-form').click(function () {
            if ($('#forgotPasswordForm').validate().form()) {
                $('#forgotPasswordForm.submit-form').prop('disabled', true);
                $('#forgotPasswordForm .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                setTimeout(function () {
                    $('#forgotPasswordForm').submit();
                }, 1000);
            }
            return false;
        });

        $('#forgotPasswordForm input').keypress(function (e) {
            if (e.which == 13) {
                if ($('#forgotPasswordForm').validate().form()) {
                    $('#forgotPasswordForm .submit-form').prop('disabled', true);
                    $('#forgotPasswordForm .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                    setTimeout(function () {
                        $('#forgotPasswordForm').submit();
                    }, 1000);
                }
                return false;
            }
        });
        $('#forgotPasswordForm').submit(function () {
            var formData = new FormData($(this)[0]);
            $.ajax({
                url: config.url + "/password/email",
                type: 'POST',
                dataType: 'json',
                data: formData,
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data)
                {
                    console.log(data);


                    if (data.type == 'success') {

                        $('#forgotPasswordForm .submit-form').html(lang.send_request);
                        $('#alert-message').removeClass('alert-danger').addClass('alert-success').fadeIn(500).delay(3000).fadeOut(2000);
                        var message = '<i class="fa fa-check" aria-hidden="true"></i> <span>' + data.message + '</span> ';
                        $('#alert-message').html(message);


                    } else {
                        $('#forgotPasswordForm .submit-form').prop('disabled', false);
                        $('#forgotPasswordForm .submit-form').html(lang.send_request);
                        if (typeof data.errors === 'object') {
                            for (i in data.errors)
                            {
                                $('[name="' + i + '"]')
                                        .closest('.form-group').addClass('has-error').removeClass("has-info");
                                $('#' + i).closest('.form-group').find(".help-block").html(data.errors[i])
                            }
                        } else {
                            $('#alert-message').removeClass('alert-success').addClass('alert-danger').fadeIn(500).delay(3000).fadeOut(2000);
                            var message = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <span>' + data.message + '</span> ';
                            $('#alert-message').html(message);
                        }
                    }


                },
                error: function (xhr, textStatus, errorThrown) {
                    $('#forgotPasswordForm .submit-form').prop('disabled', false);
                    $('#forgotPasswordForm .submit-form').html(lang.send_request);
                    App.ajax_error_message(xhr);

                },
            });

            return false;
        });

    }






    var handle_change_password = function () {
        $("#changePasswordForm").validate({
            rules: {
                email: {
                    required: true,
                    email: true
                },
                password: {
                    required: true
                },
                password_confirmation: {
                    required: true,
                    equalTo: "#password"
                },
            },
            messages: {
                password: {
                    required: lang.required
                },
                password_confirmation: {
                    required: lang.required,
                    equalTo: lang.please_enter_the_same_value_again,
                },
            },
            highlight: function (element) { // hightlight error inputs
                $(element).closest('.form-group').removeClass('has-success').addClass('has-error');

            },
            unhighlight: function (element) {
                $(element).closest('.form-group').removeClass('has-error').addClass('has-success');
                $(element).closest('.form-group').find('.help-block').html('');

            },
            errorPlacement: function (error, element) {
                $(element).closest('.form-group').find('.help-block').html($(error).html());
            }

        });
        $('#changePasswordForm .submit-form').click(function () {
            if ($('#changePasswordForm').validate().form()) {
                $('#changePasswordForm.submit-form').prop('disabled', true);
                $('#changePasswordForm .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                setTimeout(function () {
                    $('#changePasswordForm').submit();
                }, 1000);
            }
            return false;
        });

        $('#changePasswordForm input').keypress(function (e) {
            if (e.which == 13) {
                if ($('#changePasswordForm').validate().form()) {
                    $('#changePasswordForm .submit-form').prop('disabled', true);
                    $('#changePasswordForm .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                    setTimeout(function () {
                        $('#changePasswordForm').submit();
                    }, 1000);
                }
                return false;
            }
        });
        $('#changePasswordForm').submit(function () {
            var formData = new FormData($(this)[0]);
            $.ajax({
                url: config.url + "/password/reset",
                type: 'POST',
                dataType: 'json',
                data: formData,
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data)
                {
                    console.log(data);
                    $('#changePasswordForm .submit-form').prop('disabled', false);
                    $('#changePasswordForm .submit-form').html(lang.send);
                    if (data.type == 'success') {
                        window.location.href = config.url + '/login';


                    } else {

                        if (typeof data.errors === 'object') {
                            for (i in data.errors)
                            {
                                $('[name="' + i + '"]')
                                        .closest('.form-group').addClass('has-error').removeClass("has-info");
                                $('#' + i).parent().find(".help-block").html(data.errors[i])
                            }
                        } else {
                            $('#alert-message').removeClass('alert-success').addClass('alert-danger').fadeIn(500).delay(3000).fadeOut(2000);
                            var message = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <span>' + data.message + '</span> ';
                            $('#alert-message').html(message);
                        }
                    }


                },
                error: function (xhr, textStatus, errorThrown) {
                    $('#changePasswordForm .submit-form').prop('disabled', false);
                    $('#changePasswordForm .submit-form').html(lang.send);
                    App.ajax_error_message(xhr);
                },
            });

            return false;
        });

    }
    var handle_register = function () {
        $("#register-form").validate({
            rules: {
                first_name: {
                    required: true
                },
                last_name: {
                    required: true
                },
                mobile: {
                    required: true
                },
                email: {
                    required: true
                },
                password: {
                    required: true
                },
                confirm_password: {
                    required: true,
                    equalTo: "#password"
                },
                conditions: {
                    required: true
                }
            },

            highlight: function (element) { // hightlight error inputs
                $(element).closest('.form-group').removeClass('has-success').addClass('has-error');

            },
            unhighlight: function (element) {
                $(element).closest('.form-group').removeClass('has-error').addClass('has-success');
                $(element).closest('.form-group').find('.help-block').html('');

            },
            errorPlacement: function (error, element) {
                errorElements1.push(element);
                $(element).closest('.form-group').find('.help-block').html($(error).html());
            }

        });
        $('#register-form .submit-form').click(function () {
            var validate_2 = $('#register-form').validate().form();
            errorElements = errorElements1.concat(errorElements2);
            if (validate_2) {
                $('#register-form .submit-form').prop('disabled', true);
                $('#register-form .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                setTimeout(function () {
                    $('#register-form').submit();
                }, 1000);

            }
            if (errorElements.length > 0) {
                App.scrollToTopWhenFormHasError($('#register-form'));
            }

            return false;

        });
        $('#register-form input').keypress(function (e) {
            if (e.which == 13) {
                var validate_2 = $('#register-form').validate().form();
                errorElements = errorElements1.concat(errorElements2);
                if (validate_2) {
                    $('#register-form .submit-form').prop('disabled', true);
                    $('#register-form .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                    setTimeout(function () {
                        $('#register-form').submit();
                    }, 1000);

                }
                if (errorElements.length > 0) {
                    App.scrollToTopWhenFormHasError($('#register-form'));
                }

                return false;
            }
        });
        $('#register-form').submit(function () {
            var formData = new FormData($(this)[0]);
            $.ajax({
                url: config.url + "/register",
                type: 'POST',
                dataType: 'json',
                data: formData,
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data)
                {

                    $('#register-form .submit-form').prop('disabled', false);
                    $('#register-form .submit-form').html(lang.register_now);
                    if (data.type == 'success') {

                        //$('#r-alert-message').removeClass('alert-danger').addClass('alert-success').fadeIn(500);
                        //var message = '<i class="fa fa-check" aria-hidden="true"></i> <span>' + data.message + '</span> ';
                        //$('#r-alert-message').html(message);
                        setTimeout(function () {
                            window.location.href = data.message;
                        }, 2500);

                    } else {
                        $('#register-form .submit-form').prop('disabled', false);
                        $('#register-form .submit-form').html(lang.register);
                        if (typeof data.errors === 'object') {
                            for (i in data.errors)
                            {
                                $('[name="' + i + '"]')
                                        .closest('.form-group').addClass('has-error').removeClass("has-info");
                                $('#' + i).closest('.form-group').find(".help-block").html(data.errors[i])
                            }
                        } else {
                            $('#r-alert-message').html(data.message).fadeIn(500).delay(3000).fadeOut(2000);
                        }
                    }


                },
                error: function (xhr, textStatus, errorThrown) {
                    $('#register-form .submit-form').prop('disabled', false);
                    $('#register-form .submit-form').html(lang.register);
                    App.ajax_error_message(xhr);

                },
            });

            return false;
        });

    }



    var handle_activation_code = function () {
        $("#activationForm").validate({
            rules: {
                'activation[]': {
                    required: true,
                },
            },
            messages: {
                'activation[]': {
                    required: lang.required,
                },
            },
            highlight: function (element) { // hightlight error inputs
                $(element).closest('.form-group').removeClass('has-success').addClass('has-error');

            },
            unhighlight: function (element) {
                $(element).closest('.form-group').removeClass('has-error').addClass('has-success');
                $(element).closest('.form-group').find('.help-block').html('');

            },
            errorPlacement: function (error, element) {
                $(element).closest('.form-group').find('.help-block').html($(error).html());
            }

        });
        $('#activationForm .submit-form').click(function () {
            if ($('#activationForm').validate().form()) {
                $('#activationForm.submit-form').prop('disabled', true);
                $('#activationForm .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                setTimeout(function () {
                    $('#activationForm').submit();
                }, 1000);
            }
            return false;
        });

        $('#activationForm input').keypress(function (e) {
            if (e.which == 13) {
                if ($('#activationForm').validate().form()) {
                    $('#activationForm .submit-form').prop('disabled', true);
                    $('#activationForm .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                    setTimeout(function () {
                        $('#activationForm').submit();
                    }, 1000);
                }
                return false;
            }
        });
        $('#activationForm').submit(function () {
            var form_data = new FormData($(this)[0]);
            $.ajax({
                url: config.url + "/activateuser",
                type: 'POST',
                dataType: 'json',
                data: form_data,
                processData: false,
                contentType: false,
                success: function (data)
                {
                    console.log(data);


                    if (data.type == 'success') {

                        setTimeout(function () {
                            window.location.href = data.message;
                        }, 2500);


                    } else {
                        $('#activationForm .submit-form').prop('disabled', false);
                        $('#activationForm .submit-form').html(lang.confirm);
                        if (typeof data.errors === 'object') {
                            for (i in data.errors)
                            {
                                $('[name="' + i + '"]')
                                        .closest('.form-group').addClass('has-error').removeClass("has-info");
                                $('#' + i).closest('.form-group').find(".help-block").html(data.errors[i])
                            }
                        } else {
                            $('#alert-message').removeClass('alert-success').addClass('alert-danger').fadeIn(500).delay(3000).fadeOut(2000);
                            var message = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <span>' + data.message + '</span> ';
                            $('#alert-message').html(message);
                        }
                    }


                },
                error: function (xhr, textStatus, errorThrown) {
                    $('#activationForm .submit-form').prop('disabled', false);
                    $('#activationForm .submit-form').html(lang.confirm);
                    bootbox.dialog({
                        message: xhr.responseText,
                        title: 'error',
                        buttons: {
                            danger: {
                                label: 'error',
                                className: "red"
                            }
                        }
                    });
                },
            });

            return false;
        });

    }


    var handle_edit_phone = function () {
        $("#editMobileForm").validate({
            rules: {
                'mobile': {
                    required: true,
                },
            },
            messages: {
                'mobile': {
                    required: lang.required,
                },
            },
            highlight: function (element) { // hightlight error inputs
                $(element).closest('.form-group').removeClass('has-success').addClass('has-error');

            },
            unhighlight: function (element) {
                $(element).closest('.form-group').removeClass('has-error').addClass('has-success');
                $(element).closest('.form-group').find('.help-block').html('');

            },
            errorPlacement: function (error, element) {
                $(element).closest('.form-group').find('.help-block').html($(error).html());
            }

        });
        $('#editMobileForm .submit-form').click(function () {
            if ($('#editMobileForm').validate().form()) {
                $('#editMobileForm.submit-form').prop('disabled', true);
                $('#editMobileForm .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                setTimeout(function () {
                    $('#editMobileForm').submit();
                }, 1000);
            }
            return false;
        });

        $('#editMobileForm input').keypress(function (e) {
            if (e.which == 13) {
                if ($('#editMobileForm').validate().form()) {
                    $('#editMobileForm .submit-form').prop('disabled', true);
                    $('#editMobileForm .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                    setTimeout(function () {
                        $('#editMobileForm').submit();
                    }, 1000);
                }
                return false;
            }
        });
        $('#editMobileForm').submit(function () {
            var form_data = new FormData($(this)[0]);
            $.ajax({
                url: config.url + "/edituserphone",
                type: 'POST',
                dataType: 'json',
                data: form_data,
                processData: false,
                contentType: false,
                success: function (data)
                {
                    console.log(data);


                    if (data.type == 'success') {

                        setTimeout(function () {
                            window.location.href = data.message;
                        }, 2500);


                    } else {
                        $('#editMobileForm .submit-form').prop('disabled', false);
                        $('#editMobileForm .submit-form').html(lang.confirm);
                        if (typeof data.errors === 'object') {
                            for (i in data.errors)
                            {
                                $('[name="' + i + '"]')
                                        .closest('.form-group').addClass('has-error').removeClass("has-info");
                                $('#' + i).closest('.form-group').find(".help-block").html(data.errors[i])
                            }
                        } else {
                            $('#alert-message').removeClass('alert-success').addClass('alert-danger').fadeIn(500).delay(3000).fadeOut(2000);
                            var message = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <span>' + data.message + '</span> ';
                            $('#alert-message').html(message);
                        }
                    }


                },
                error: function (xhr, textStatus, errorThrown) {
                    $('#editMobileForm .submit-form').prop('disabled', false);
                    $('#editMobileForm .submit-form').html(lang.confirm);
                    bootbox.dialog({
                        message: xhr.responseText,
                        title: 'error',
                        buttons: {
                            danger: {
                                label: 'close',
                                className: "red"
                            }
                        }
                    });
                },
            });

            return false;
        });

    }

    return {
        init: function () {
            init();
        },
        empty: function () {
            $('.has-error').removeClass('has-error');
            $('.has-success').removeClass('has-success');
            $('.help-block').html('');

            App.emptyForm();
        },
    }

}();

jQuery(document).ready(function () {
    Login.init();
});


