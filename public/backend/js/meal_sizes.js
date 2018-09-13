var MealSizes_grid;
var meal;
var validate_choices = true;

var MealSizes = function () {
    var init = function () {
        $.extend(lang, new_lang);
        $.extend(config, new_config);
        meal = config.meal;
        handleRecords();
        handleSubmit();
        My.readImageMulti('image');
     

    };


    var handleSubmit = function () {
        $.validator.addMethod("roles", function (value, elem, param) {
            var min = "input[name='choices[" + value + "][min]']";
            var max = "input[name='choices[" + value + "][max]']";
            if ($(this).is(':checked')) {
                alert('here');
                $(min).rules('add', {
                    required: true
                });
                $(max).rules('add', {
                    required: true
                });

            }
            return false;
        }, "You must select at least one!");
        $('#addEditMealSizesForm').validate({
            rules: {

//                title_ar: {
//                    required: true
//                },
//                title_en: {
//                    required: true
//                },
//                description_ar: {
//                    required: true,
//                },
//                description_en: {
//                    required: true
//                },
//                price: {
//                    required: true
//                },
//                this_order: {
//                    required: true
//                }
            },
            //messages: lang.messages,
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
        var choices = JSON.parse(config.choices);
//        for (var x = 0; x < choices.length; x++) {
//            var choice = choices[x];
//            var min = "input[name='choices[" + choice.id + "][min]']";
//            var max = "input[name='choices[" + choice.id + "][max]']";
//            var sub = "input[name='sub_choices[" + choice.id + "][]']";
//            //var sub = "input[name='sub_choices[3][]']";
//            $(min).rules('add', {
//                required: {
//                    depends: function (element) {
//                        if ($('#ch-' + $(element).data('choice')).is(':checked')) {
//                            return true;
//                        } else {
//                            return false;
//                        }
//                    }
//                }
//            });
//            $(max).rules('add', {
//                required: {
//                    depends: function (element) {
//                        if ($('#ch-' + $(element).data('choice')).is(':checked')) {
//                            return true;
//                        } else {
//                            return false;
//                        }
//                    }
//                }
//            });
//            if ($(sub).length > 0) {
//                $(sub).rules('add', {
//                    required: {
//                        depends: function (element) {
//                            if ($('#ch-' + $(element).data('choice')).is(':checked')) {
//                                return true;
//                            } else {
//                                return false;
//                            }
//                        }
//                    }
//                });
//            }
//
//        }
        $('#addEditMealSizesForm .submit-form').click(function () {
            // var my_validator = choices_rules();

            if ($('#addEditMealSizesForm').validate().form()) {
                //alert(my_validator);
                //if (my_validator) {
                $('#addEditMealSizesForm .submit-form').prop('disabled', true);
                $('#addEditMealSizesForm .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                setTimeout(function () {
                    $('#addEditMealSizesForm').submit();
                }, 1000);
                //}


            }
            return false;
        });
        $('#addEditMealSizesForm input').keypress(function (e) {
            if (e.which == 13) {
                //var my_validator = choices_rules();
                if ($('#addEditMealSizesForm').validate().form()) {
                    $('#addEditMealSizesForm .submit-form').prop('disabled', true);
                    $('#addEditMealSizesForm .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                    setTimeout(function () {
                        $('#addEditMealSizesForm').submit();
                    }, 1000);
                }
                return false;
            }
        });





        $('#addEditMealSizesForm').submit(function () {

            var id = $('#id').val();
            var formData = new FormData($(this)[0]);
            var action = config.admin_url + '/meal_sizes';

            if (id != 0) {
                formData.append('_method', 'PATCH');
                action = config.admin_url + '/meal_sizes/' + id;
            }
            formData.append('meal', meal);

            $.ajax({
                url: action,
                type: 'post',
                data: formData,
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    $('#addEditMealSizesForm .submit-form').prop('disabled', false);
                    $('#addEditMealSizesForm .submit-form').html(lang.save);

                    if (data.type == 'success')
                    {
                        toastr.options = {
                            "debug": false,
                            "positionClass": "toast-bottom-left",
                            "onclick": null,
                            "fadeIn": 300,
                            "fadeOut": 1000,
                            "timeOut": 5000,
                            "extendedTimeOut": 1000,
                            "showEasing": "swing",
                            "hideEasing": "linear",
                            "showMethod": "fadeIn",
                            "hideMethod": "fadeOut"
                        };
                        toastr.success(data.message, 'ﺔﻟﺎﺳﺭ');
                        if (id == 0) {
                            MealSizes.empty();
                        }
                    } else {
                        console.log(data)
                        if (typeof data.errors === 'object') {
                            for (i in data.errors)
                            {
                                var message=data.errors[i][0];
                                if (i.startsWith('choices')) {
                                    var key_arr = i.split('.');
                                    var key_text = key_arr[0] + '[' + key_arr[1] + ']'+ '[' + key_arr[2] + ']';
                                    i = key_text;
                                }
                                if (i.startsWith('sub_choices')) {
                                    var key_arr = i.split('.');
                                    var key_text = key_arr[0] + '[' + key_arr[1] + ']'+'[]';
                                    i = key_text;
                                    console.log(key_text);
                                }
                                $('[name="' + i + '"]')
                                        .closest('.form-group').addClass('has-error');
                                $('[name="' + i + '"]').closest('.form-group').find(".help-block").html(message).css('opacity', 1)
                            }
                        } else {
                            //alert('here');
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
                    $('#addEditMealSizesForm .submit-form').prop('disabled', false);
                    $('#addEditMealSizesForm .submit-form').html(lang.save);
                    My.ajax_error_message(xhr);
                },
                dataType: "json",
                type: "POST"
            });

            return false;

        })




    }

    var handleRecords = function () {
        MealSizes_grid = $('.dataTable').dataTable({
            //"processing": true,
            "serverSide": true,
            "ajax": {
                "url": config.admin_url + "/meal_sizes/data",
                "type": "POST",
                data: {meal: meal, _token: $('input[name="_token"]').val()},
            },
            "columns": [
                {"data": "title", "name": "title_" + config.lang_code},
                {"data": "this_order", "name": "this_order"},
                {"data": "active", "name": "active"},
                {"data": "options", orderable: false, searchable: false}
            ],
            "order": [
                [1, "desc"]
            ],
            "oLanguage": {"sUrl": config.url + '/datatable-lang-' + config.lang_code + '.json'}

        });
    }



    return{
        init: function () {
            init();
        },
        delete: function (t) {
            var id = $(t).attr("data-id");
            My.deleteForm({
                element: t,
                url: config.admin_url + '/meal_sizes/' + id,
                data: {_method: 'DELETE', _token: $('input[name="_token"]').val()},
                success: function (data)
                {

                    MealSizes_grid.api().ajax.reload();


                }
            });
        },
        error_message: function (message) {
            $.alert({
                title: lang.error,
                content: message,
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
        },
        empty: function () {
            //location.reload();
            $('#id').val(0);
            $('#active').find('option').eq(0).prop('selected', true);
            $('.image_box').html('<img src="' + config.url + '/no-image.png" class="image" width="150" height="80" />');
            $('.has-error').removeClass('has-error');
            $('input[type="checkbox"]').prop("checked", false).trigger("change");
            $('.has-success').removeClass('has-success');
            $('#sizes-table tbody').html('');
            $('#toppings-table tbody').html('');
            $('.help-block').html('');
            My.emptyForm();
        },
    };
}();
$(document).ready(function () {
    MealSizes.init();
});