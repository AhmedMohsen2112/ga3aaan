var Categories_grid;

var Categories = function () {

    var init = function () {

        $.extend(lang, new_lang);
        nextLevel = 1;
        handleRecords();
        handleSubmit();
        $('.colorpicker-default').colorpicker({
            format: 'hex'
        });

    };


    var handleRecords = function () {
        Categories_grid = $('.dataTable').dataTable({
            //"processing": true,
            "serverSide": true,
            "ajax": {
                "url": config.admin_url + "/categories/data",
                "type": "POST",
                data: {_token: $('input[name="_token"]').val()},
            },
            "columns": [
//                    {"data": "user_input", orderable: false, "class": "text-center"},
                {"data": "title","name":"title_" + config.lang_code},
                {"data": "active"},
                {"data": "this_order"},
                {"data": "options", orderable: false,searchable: false}
            ],
           
            "oLanguage": {"sUrl": config.url + '/datatable-lang-' + config.lang_code + '.json'}

        });
    }


    var handleSubmit = function () {
        $('#addEditCategoriesForm').validate({
            rules: {
                title_ar: {
                    required: true,
                },
                title_en: {
                    required: true,
                },
                this_order: {
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
        $('#addEditCategories .submit-form').click(function () {

            if ($('#addEditCategoriesForm').validate().form()) {
                $('#addEditCategories .submit-form').prop('disabled', true);
                $('#addEditCategories .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                setTimeout(function () {
                    $('#addEditCategoriesForm').submit();
                }, 1000);
            }
            return false;
        });
        $('#addEditCategoriesForm input').keypress(function (e) {
            if (e.which == 13) {
                if ($('#addEditCategoriesForm').validate().form()) {
                    $('#addEditCategories .submit-form').prop('disabled', true);
                    $('#addEditCategories .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                    setTimeout(function () {
                        $('#addEditCategoriesForm').submit();
                    }, 1000);
                }
                return false;
            }
        });



        $('#addEditCategoriesForm').submit(function () {
            var id = $('#id').val();
            var action = config.admin_url + '/categories';
            var formData = new FormData($(this)[0]);
            if (id != 0) {
                formData.append('_method', 'PATCH');
                action = config.admin_url + '/categories/' + id;
            }
            $.ajax({
                url: action,
                data: formData,
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    $('#addEditCategories .submit-form').prop('disabled', false);
                    $('#addEditCategories .submit-form').html(lang.save);

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
                        Categories_grid.api().ajax.reload();
                        if (id != 0) {
                            $('#addEditCategories').modal('hide');
                        } else {

                            Categories.empty();
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
                    $('#addEditCategories .submit-form').prop('disabled', false);
                    $('#addEditCategories .submit-form').html(lang.save);
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
                url: config.admin_url + '/categories/' + id,
                success: function (data)
                {
                    console.log(data);

                    Categories.empty();
                    My.setModalTitle('#addEditCategories', lang.edit_country);

                    for (i in data.message)
                    {
                      $('#' + i).val(data.message[i]);
                    }
                    $('#addEditCategories').modal('show');
                }
            });

        },
        delete: function (t) {

            var id = $(t).attr("data-id");
            My.deleteForm({
                element: t,
                url: config.admin_url + '/categories/' + id,
                data: {_method: 'DELETE', _token: $('input[name="_token"]').val()},
                success: function (data)
                {
                    Categories_grid.api().ajax.reload();
                }
            });

        },
        add: function () {
            Categories.empty();
            My.setModalTitle('#addEditCategories', lang.add_country);
            $('#addEditCategories').modal('show');
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
    Categories.init();
});

