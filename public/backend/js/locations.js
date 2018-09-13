var Locations_grid;
var parent_id = 0;
var level;
var nextLevel;
var Locations = function () {

    var init = function () {
        $.extend(lang, new_lang);
        nextLevel = 1;
        handleRecords();
        handleDatatables();
        handleSubmit();


    };


    var handleDatatables = function () {
        $(document).on('click', '.data-box', function () {
            parent_id = $(this).data('id');
            level = $(this).data('level');
            var where = $(this).data('where');
            var title = $(this).data('title');
            if (where == 'inTable') {
                nextLevel = level + 1;
            } else {
                nextLevel = level - 1;
            }
            var html = '<a class="panel-title data-box" data-where="outTable" data-id="' + parent_id + '" data-level="' + nextLevel + '"> / ' + title + '</a>';

            if (where == 'inTable') {
                $('.panel-heading').append(html);
            }
            if (where == 'outTable') {
                $(this).nextAll().remove();
            }
            if (typeof Locations_grid === 'undefined') {
                Locations_grid = $('.dataTable').dataTable({
                    //"processing": true,
                    "serverSide": true,
                    "ajax": {
                        "url": config.admin_url + "/locations/data",
                        "type": "POST",
                        data: {parent_id: parent_id, _token: $('input[name="_token"]').val()},
                    },
                    "columns": [
                        {"data": "title"},
                        {"data": "active"},
                        {"data": "this_order"},
                        {"data": "options", orderable: false}
                    ],
                    "order": [
                        [2, "ASC"]
                    ],
                    "oLanguage": {"sUrl": config.url + '/datatable-lang-' + config.lang_code + '.json'}

                });
            } else {
                Locations_grid.on('preXhr.dt', function (e, settings, data) {
                    data.parent_id = parent_id
                    data._token = $('input[name="_token"]').val()
                })
                Locations_grid.api().ajax.url(config.admin_url + "/locations/data").load();
            }


            return false;
        });
    }
    var handleRecords = function () {
        Locations_grid = $('.dataTable').dataTable({
            //"processing": true,
            "serverSide": true,
            "ajax": {
                "url": config.admin_url + "/locations/data",
                "type": "POST",
                data: {parent_id: parent_id, _token: $('input[name="_token"]').val()},
            },
            "columns": [
//                    {"data": "user_input", orderable: false, "class": "text-center"},
                 {"data": "title_"+config.lang_code},
//                {"data": "image"},
                {"data": "active"},
                {"data": "this_order"},
                {"data": "options", orderable: false,searchable:false}
            ],
            "order": [
                [2, "asc"]
            ],
            "oLanguage": {"sUrl": config.url + '/datatable-lang-' + config.lang_code + '.json'}

        });
    }


    var handleSubmit = function () {
        $('#addEditLocationsForm').validate({
            rules: {
                title_ar: {
                    required: true,
                },
                title_en: {
                    required: true,
                },
                dial_code: {
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
        $('#addEditLocations .submit-form').click(function () {

            if ($('#addEditLocationsForm').validate().form()) {
                $('#addEditLocations .submit-form').prop('disabled', true);
                $('#addEditLocations .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                setTimeout(function () {
                    $('#addEditLocationsForm').submit();
                }, 1000);
            }
            return false;
        });
        $('#addEditLocationsForm input').keypress(function (e) {
            if (e.which == 13) {
                if ($('#addEditLocationsForm').validate().form()) {
                    $('#addEditLocations .submit-form').prop('disabled', true);
                    $('#addEditLocations .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                    setTimeout(function () {
                        $('#addEditLocationsForm').submit();
                    }, 1000);
                }
                return false;
            }
        });



        $('#addEditLocationsForm').submit(function () {
            var id = $('#id').val();
            var action = config.admin_url + '/locations';
            var formData = new FormData($(this)[0]);
            if (id != 0) {
                formData.append('_method', 'PATCH');
                action = config.admin_url + '/locations/' + id;
            }
            formData.append('parent_id', parent_id);
            formData.append('level', nextLevel);
            $.ajax({
                url: action,
                data: formData,
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    $('#addEditLocations .submit-form').prop('disabled', false);
                    $('#addEditLocations .submit-form').html(lang.save);

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
                        Locations_grid.api().ajax.reload();
                        if (id != 0) {
                            $('#addEditLocations').modal('hide');
                        } else {

                            Locations.empty();
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
                    $('#addEditLocations .submit-form').prop('disabled', false);
                    $('#addEditLocations .submit-form').html(lang.save);
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
            if (parent_id > 0) {
                $('.for-country').hide();
            } else {
                $('.for-country').show();
            }
            var id = $(t).attr("data-id");
            My.editForm({
                element: t,
                url: config.admin_url + '/locations/' + id,
                success: function (data)
                {
                    console.log(data);

                    Locations.empty();
                    My.setModalTitle('#addEditLocations', lang.edit_location);

                    for (i in data.message)
                    {
                       $('#' + i).val(data.message[i]);
                    }
                    $('#addEditLocations').modal('show');
                }
            });

        },
        delete: function (t) {

            var id = $(t).attr("data-id");
            My.deleteForm({
                element: t,
                url: config.admin_url + '/locations/' + id,
                data: {_method: 'DELETE', _token: $('input[name="_token"]').val()},
                success: function (data)
                {
                    Locations_grid.api().ajax.reload();


                }
            });

        },
        add: function () {
            Locations.empty();
            if (parent_id > 0) {
                $('.for-country').hide();
            } else {
                $('.for-country').show();
            }

            My.setModalTitle('#addEditLocations', lang.add_location);
            $('#addEditLocations').modal('show');
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
            $('.image_uploaded_box').html('<img src="' + config.base_url + 'no-image.png" class="category_icon" width="150" height="80" />');
            $('.has-error').removeClass('has-error');
            $('.has-success').removeClass('has-success');
            $('.help-block').html('');
            My.emptyForm();
        }
    };

}();
jQuery(document).ready(function () {
    Locations.init();
});

