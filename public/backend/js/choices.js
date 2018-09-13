var Choices_grid;
var resturant;
var Choices = function () {

    var init = function () {

        $.extend(lang, new_lang);
        $.extend(config, new_config);
        resturant=config.resturant;
        handleRecords();
        handleSubmit();
     

    };


    var handleRecords = function () {
        Choices_grid = $('.dataTable').dataTable({
            //"processing": true,
            "serverSide": true,
            "ajax": {
                "url": config.admin_url + "/choices/data",
                "type": "POST",
                data: {resturant:resturant,_token: $('input[name="_token"]').val()},
            },
            "columns": [
//                    {"data": "user_input", orderable: false, "class": "text-center"},
                {"data": "title","name":"title_" + config.lang_code},
                {"data": "options", orderable: false,searchable: false}
            ],
            
            "oLanguage": {"sUrl": config.url + '/datatable-lang-' + config.lang_code + '.json'}

        });
    }


    var handleSubmit = function () {
        $('#addEditChoicesForm').validate({
            rules: {
                title_ar: {
                    required: true,
                },
                title_en: {
                    required: true,
                },
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
        $('#addEditChoices .submit-form').click(function () {

            if ($('#addEditChoicesForm').validate().form()) {
                $('#addEditChoices .submit-form').prop('disabled', true);
                $('#addEditChoices .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                setTimeout(function () {
                    $('#addEditChoicesForm').submit();
                }, 1000);
            }
            return false;
        });
        $('#addEditChoicesForm input').keypress(function (e) {
            if (e.which == 13) {
                if ($('#addEditChoicesForm').validate().form()) {
                    $('#addEditChoices .submit-form').prop('disabled', true);
                    $('#addEditChoices .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                    setTimeout(function () {
                        $('#addEditChoicesForm').submit();
                    }, 1000);
                }
                return false;
            }
        });



        $('#addEditChoicesForm').submit(function () {
            var id = $('#id').val();
            var action = config.admin_url + '/choices';
            var formData = new FormData($(this)[0]);
            if (id != 0) {
                formData.append('_method', 'PATCH');
                action = config.admin_url + '/choices/' + id;
            }
             formData.append('resturant', resturant);
            $.ajax({
                url: action,
                data: formData,
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    $('#addEditChoices .submit-form').prop('disabled', false);
                    $('#addEditChoices .submit-form').html(lang.save);

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
                        toastr.success(data.message, 'رسالة');
                        Choices_grid.api().ajax.reload();
                        if (id != 0) {
                            $('#addEditChoices').modal('hide');
                        } else {

                            Choices.empty();
                        }


                    } else {
                        if (typeof data.errors === 'object') {
                            for (i in data.errors)
                            {
                                $('[name="' + i + '"]')
                                        .closest('.form-group').addClass('has-error');
                                $('#' + i).parent().find(".help-block").html(data.errors[i]).css('opacity', 1)
                            }
                        } else {
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
                    $('#addEditChoices .submit-form').prop('disabled', false);
                    $('#addEditChoices .submit-form').html(lang.save);
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
        },
        edit: function (t) {
            var id = $(t).attr("data-id");
            My.editForm({
                element: t,
                url: config.admin_url + '/choices/' + id,
                success: function (data)
                {
                    console.log(data);

                    Choices.empty();
                    My.setModalTitle('#addEditChoices', lang.edit_country);

                    for (i in data.message)
                    {
                      $('#' + i).val(data.message[i]);
                    }
                    $('#addEditChoices').modal('show');
                }
            });

        },
        delete: function (t) {

            var id = $(t).attr("data-id");
            My.deleteForm({
                element: t,
                url: config.admin_url + '/choices/' + id,
                data: {_method: 'DELETE', _token: $('input[name="_token"]').val()},
                success: function (data)
                {
                    Choices_grid.api().ajax.reload();
                }
            });

        },
        add: function () {
            Choices.empty();
            My.setModalTitle('#addEditChoices', lang.add_country);
            $('#addEditChoices').modal('show');
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
            $('#id').val(0);
            $('#category_icon').val('');
            $('#active').find('option').eq(0).prop('selected', true);
            $('input[type="checkbox"]').prop('checked', false);
            $('.image_uploaded_box').html('<img src="' + config.url + '/no-image.png" class="image" width="150" height="80" />');
            $('.has-error').removeClass('has-error');
            $('.has-success').removeClass('has-success');
            $('.help-block').html('');
            My.emptyForm();
        }
    };

}();
jQuery(document).ready(function () {
    Choices.init();
});

