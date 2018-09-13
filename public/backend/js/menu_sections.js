var MenuSections_grid;
var resturant;

var MenuSections = function () {
    var init = function () {
        $.extend(lang, new_lang);
        $.extend(config, new_config);
        resturant = config.resturant;
        handleRecords();
        handleSubmit();
    };




    var handleSubmit = function () {

        $('#addEditMenuSectionsForm').validate({
            rules: {

                title_ar: {
                    required: true
                },
                title_en: {
                    required: true
                },
                this_order: {
                    required: true
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
        $('#addEditMenuSections .submit-form').click(function () {
            if ($('#addEditMenuSectionsForm').validate().form()) {
                $('#addEditMenuSections .submit-form').prop('disabled', true);
                $('#addEditMenuSections .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                setTimeout(function () {
                    $('#addEditMenuSectionsForm').submit();
                }, 1000);

            }
            return false;
        });
        $('#addEditMenuSectionsForm input').keypress(function (e) {
            if (e.which == 13) {
                if ($('#addEditMenuSectionsForm').validate().form()) {
                    $('#addEditMenuSections .submit-form').prop('disabled', true);
                    $('#addEditMenuSections .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                    setTimeout(function () {
                        $('#addEditMenuSectionsForm').submit();
                    }, 1000);
                }
                return false;
            }
        });



        $('#addEditMenuSectionsForm').submit(function () {
            var id = $('#id').val();
            var formData = new FormData($(this)[0]);
            var action = config.admin_url + '/menu_sections';

            if (id != 0) {
                formData.append('_method', 'PATCH');
                action = config.admin_url + '/menu_sections/' + id;
            }
            formData.append('resturant', resturant);
            $.ajax({
                url: action,
                type: 'post',
                data: formData,
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    $('#addEditMenuSections .submit-form').prop('disabled', false);
                    $('#addEditMenuSections .submit-form').html(lang.save);

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
                         toastr.success(data.message, 'رسالة');
                        MenuSections_grid.api().ajax.reload();
                        if (id != 0) {
                            $('#addEditMenuSections').modal('hide');
                        } else {

                            MenuSections.empty();
                        }
                    } else {
                        console.log(data)
                        if (typeof data.errors === 'object') {
                            for (i in data.errors)
                            {
                                $('[name="' + i + '"]')
                                        .closest('.form-group').addClass('has-error');
                                $('#' + i).closest('.form-group').find(".help-block").html(data.errors[i][0]).css('opacity', 1)
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
                    $('#addEditMenuSections .submit-form').prop('disabled', false);
                    $('#addEditMenuSections .submit-form').html(lang.save);
                    My.ajax_error_message(xhr);
                },
                dataType: "json",
                type: "POST"
            });

            return false;

        })




    }

    var handleRecords = function () {
        MenuSections_grid = $('.dataTable').dataTable({
            //"processing": true,
            "serverSide": true,
            "ajax": {
                "url": config.admin_url + "/menu_sections/data",
                "type": "POST",
                data: {resturant: resturant, _token: $('input[name="_token"]').val()},
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
        edit: function (t) {
            var id = $(t).attr("data-id");
            My.editForm({
                element: t,
                url: config.admin_url + '/menu_sections/' + id,
                success: function (data)
                {
                    console.log(data);

                    MenuSections.empty();
                    My.setModalTitle('#addEditMenuSections', lang.edit);

                    for (i in data.message)
                    {
                        $('#' + i).val(data.message[i]);
                    }
                    $('#addEditMenuSections').modal('show');
                }
            });

        },
        add: function () {
            MenuSections.empty();
            My.setModalTitle('#addEditMenuSections', lang.add);
            $('#addEditMenuSections').modal('show');
        },
        delete: function (t) {
            var id = $(t).attr("data-id");
            My.deleteForm({
                element: t,
                url: config.admin_url + '/menu_sections/' + id,
                data: {_method: 'DELETE', _token: $('input[name="_token"]').val()},
                success: function (data)
                {

                    MenuSections_grid.api().ajax.reload();


                }
            });
        },
        empty: function () {
            $('#id').val(0);
            $('#active').find('option').eq(0).prop('selected', true);
            $('.image_box').html('<img src="' + config.url + '/no-image.png" class="image" width="150" height="80" />');
            $('.has-error').removeClass('has-error');
            $('input[type="checkbox"]').prop("checked", false).trigger("change");
            $('.has-success').removeClass('has-success');
            $('#toppings-table').html('');
            $('.help-block').html('');
            My.emptyForm();
        },
    };
}();
$(document).ready(function () {
    MenuSections.init();
});