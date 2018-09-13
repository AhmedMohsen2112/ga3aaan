

var Resturantes = function () {

    var init = function () {

        handleReport();
        handleSearch();
        handle_suggestions_submit();
    };
    var handle_suggestions_submit = function () {
        $("#suggestions-form").validate({
            rules: {
                resturant_name: {
                    required: true,
                },
                resturant_region: {
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
        $('#suggestions-form .submit-form').click(function () {
            var validate_2 = $('#suggestions-form').validate().form();
            errorElements = errorElements1.concat(errorElements2);
            if (validate_2) {
                $('#suggestions-form .submit-form').prop('disabled', true);
                $('#suggestions-form .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                setTimeout(function () {
                    $('#suggestions-form').submit();
                }, 1000);

            }
            if (errorElements.length > 0) {
                App.scrollToTopWhenFormHasError($('#suggestions-form'));
            }

            return false;
        });

        $('#suggestions-form input').keypress(function (e) {
            if (e.which == 13) {
                var validate_2 = $('#suggestions-form').validate().form();
                errorElements = errorElements1.concat(errorElements2);
                if (validate_2) {
                    $('#suggestions-form .submit-form').prop('disabled', true);
                    $('#suggestions-form .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                    setTimeout(function () {
                        $('#suggestions-form').submit();
                    }, 1000);

                }
                if (errorElements.length > 0) {
                    App.scrollToTopWhenFormHasError($('#suggestions-form'));
                }

                return false;
            }
        });
        $('#suggestions-form').submit(function () {
            var formData = new FormData($(this)[0]);
            $.ajax({
                url: config.url + "/resturantes/suggest",
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
                    $('#suggestions-form .submit-form').prop('disabled', false);
                    $('#suggestions-form .submit-form').html(lang.save);
                    if (data.type == 'success') {
                        $('.alert-danger').hide();
                        $('.alert-success').show().find('.message').html(data.message);

                    } else {

                        if (typeof data.errors === 'object') {
                            for (i in data.errors)
                            {
                                var message = data.errors[i][0];
                                $('[name="' + i + '"]')
                                        .closest('.form-group').addClass('has-error').removeClass("has-info");
                                $('[name="' + i + '"]').closest('.form-group').find(".help-block").html(message)


                            }
                        } else {
                            $('.alert-success').hide();
                            $('.alert-danger').show().find('.message').html(data.message);
                        }
                    }



                },
                error: function (xhr, textStatus, errorThrown) {
                    $('#suggestions-form .submit-form').prop('disabled', false);
                    $('#suggestions-form .submit-form').html(lang.add);
                    App.ajax_error_message(xhr);
                },
            });

            return false;
        });

    }
    var handleSearch = function () {
//        $('#query').on('change', function () {
//            var thisVal = $(this).val();
//            if (!thisVal || thisVal.replace(/ /g, '') === '') {
//                $(this).prop('disabled', true);
//            } else {
//                $(this).prop('disabled', false);
//            }
//        });
        $('.search-btn').on('click', function () {
            var query = $('#query').val();
            var edited_query = query.replace(" ", "+");
            if (query && query.replace(/ /g, '') !== '') {
                var url = config.url + '/resturantes?' + 'q=' + edited_query;
            } else {
                var url = config.url + '/resturantes';
            }
            window.location.href = url;
            return false;
        })
    }
    var handleReport = function () {
        $('#filter-form .submit-form').on('click', function () {
            var data = $("#filter-form").serializeArray();


            var url = config.url + "/resturantes";
            var params = {};
            var cuisines = [];
            $.each(data, function (i, field) {
                var name = field.name;
                var value = field.value;

                if (value) {
                    if (name == "from" || name == "to") {
                        value = new Date(Date.parse(value));
                        value = getDate(value);
                    }
                    if (name == 'cuisines[]') {
                        cuisines.push(value);
                    } else {
                        params[name] = value;
                    }

                }

            });
            params['cuisines'] = cuisines;
//            console.log(params);
//            return false;
            var query = $.param(params);
            url += '?' + query;

            window.location.href = url;
            return false;
        })
    }

    var getDate = function (date) {
        var dd = date.getDate();
        var mm = date.getMonth() + 1; //January is 0!
        var yyyy = date.getFullYear();
        if (dd < 10) {
            dd = '0' + dd
        }
        if (mm < 10) {
            mm = '0' + mm
        }
        var edited_date = yyyy + '-' + mm + '-' + dd;
        return edited_date;
    }



    return {
        init: function () {
            init();
        },
    };

}();
jQuery(document).ready(function () {
    Resturantes.init();
});

