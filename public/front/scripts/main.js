var main = function () {

    var init = function () {

        handleChangeCity();
        handleSearch();
       


    }
    var handleChangeCity = function () {

        $('#city').on('change.bs.select', function () {

            var city = $(this).val();
            if (city) {
                $.get('' + config.url + '/getRegionByCity/' + city, function (data) {
                    $('#region').html('<option selected value="">' + lang.region + '</option>')
                    if (data.data.length != 0)
                    {

                        $.each(data.data, function (index, Obj) {

                            $('#region').append($('<option>', {
                                value: Obj.id,
                                text: Obj.title
                            }));
                        });

                    }


                }, "json");
            }
        })
    }



    var handleSearch = function () {
        $("#search-form").validate({
            rules: {
                city: {
                    required: true,
                },
                region: {
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
                $(element).closest('.form-group').find('.help-block').html($(error).html());
            }

        });
        $('#search-form .submit-form').click(function () {
            var validate_2 = $('#search-form').validate().form();
            if (validate_2) {
                $('#search-form .submit-form').prop('disabled', true);
                $('#search-form .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                setTimeout(function () {
                    $('#search-form').submit();
                }, 1000);

            }
            if (errorElements.length > 0) {
                App.scrollToTopWhenFormHasError($('#search-form'));
            }

            return false;
        });

        $('#search-form input').keypress(function (e) {
            if (e.which == 13) {
                var validate_2 = $('#search-form').validate().form();

                if (validate_2) {
                    $('#search-form .submit-form').prop('disabled', true);
                    $('#search-form .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                    setTimeout(function () {
                        $('#search-form').submit();
                    }, 500);

                }

                return false;
            }
        });
        $('#search-form').submit(function () {
            $.ajax({
                url: config.url + "/location-suggestions",
                type: 'POST',
                dataType: 'json',
                data: $(this).serialize(),
                async: false,

                success: function (data)
                {
                    console.log(data);
                    if (data.type == 'success') {
                        setTimeout(function () {
                            window.location.href = data.message;
                        }, 1000);


                    } else {
                        $('#search-form .submit-form').prop('disabled', false);
                        $('#search-form .submit-form').html(lang.view_resturantes);
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
                    $('#search-form .submit-form').prop('disabled', false);
                    $('#search-form .submit-form').html(lang.view_resturantes);
                    App.ajax_error_message(xhr);
                },
            });

            return false;
        });

    }
    return {
        init: function () {
            init();
        },

        handleFavourites: function (t) {

            var _config = $(t).data("config");
            $(t).prop('disabled', true);
            $(t).html('<i class="fa fa-spinner fa-spin fa-fw"></i><span class="sr-only">Loading...</span>');
//            console.log(config.url + '/add-favourite?meal=' + _config.meal_id+'&branch='+_config.resturant_branch_id);
//            return false;
            $.ajax({
                dataType: 'json',
                url: config.url + '/add-favourite?meal=' + _config.meal_id + '&branch=' + _config.resturant_branch_id,
                success: function (data) {
                    console.log(data);
                    $(t).prop('disabled', false);
                    if (data.type == 'success') {
                        if (data.message == true) {
                            $(t).addClass('active');
                        } else {
                            $(t).removeClass('active');
                        }

                        $(t).html('<i class="fa fa-heart-o" aria-hidden="true"></i>');
                    } else {
                        
                    }

                },
                error: function (xhr, textStatus, errorThrown) {
                    App.ajax_error_message(xhr);
                    $(t).html('<i class="fa fa-heart-o" aria-hidden="true"></i>');
                },
            });

        },
        changelang: function () {

            $.get('' + config.base_url + '/ajax/changelang', function (data) {
            }).done(function (data) {
                window.location.reload();
            });

        },
    }


}();

$(document).ready(function () {
    main.init();
});