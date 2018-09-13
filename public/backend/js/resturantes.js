var Resturantes_grid;
var Resturantes = function () {
    var init = function () {
        $.extend(lang);
        handleRecords();
        handleSubmit();
        My.readImageMulti('image');
    };




    var handleSubmit = function () {

        $('#addEditResturantesForm').validate({
            rules: {

//                username: {
//                    required: true
//                },
//                phone: {
//                    required: true
//                },
//                email: {
//                    required: true,
//                    email: true,
//                },
//                title_ar: {
//                    required: true
//                },
//                title_en: {
//                    required: true
//                },
//                image: {
//                    required: true
//                },
//                delivery_time: {
//                    required: true
//                },
//                minimum_charge: {
//                    required: true
//                },
//                'payment_methods[]': {
//                    required: true
//                },
//                service_charge: {
//                    required: true
//                },
//                vat: {
//                    required: true
//                },
//                category: {
//                    required: true
//                },
//                'cuisines[]': {
//                    required: true
//                },
//                commission: {
//                    required: true
//                },
//                'working_hours[Sat][from]': {
//                    required: true
//                },
//                'working_hours[Sat][to]': {
//                    required: true
//                },
//                'working_hours[Sun][from]': {
//                    required: true
//                },
//                'working_hours[Sun][to]': {
//                    required: true
//                },
//                'working_hours[Mon][from]': {
//                    required: true
//                },
//                'working_hours[Mon][to]': {
//                    required: true
//                },
//                'working_hours[Tue][from]': {
//                    required: true
//                },
//                'working_hours[Tue][to]': {
//                    required: true
//                },
//                'working_hours[Wed][from]': {
//                    required: true
//                },
//                'working_hours[Wed][to]': {
//                    required: true
//                },
//                'working_hours[Thu][from]': {
//                    required: true
//                },
//                'working_hours[Thu][to]': {
//                    required: true
//                },
//                'working_hours[Fri][from]': {
//                    required: true
//                },
//                'working_hours[Fri][to]': {
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
        $('#addEditResturantesForm .submit-form').click(function () {
            if ($('#addEditResturantesForm').validate().form()) {
                $('#addEditResturantesForm .submit-form').prop('disabled', true);
                $('#addEditResturantesForm .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                setTimeout(function () {
                    $('#addEditResturantesForm').submit();
                }, 1000);

            }
            return false;
        });
        $('#addEditResturantesForm input').keypress(function (e) {
            if (e.which == 13) {
                if ($('#addEditResturantesForm').validate().form()) {
                    $('#addEditResturantesForm .submit-form').prop('disabled', true);
                    $('#addEditResturantesForm .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                    setTimeout(function () {
                        $('#addEditResturantesForm').submit();
                    }, 1000);
                }
                return false;
            }
        });



        $('#addEditResturantesForm').submit(function () {
            var id = $('#id').val();
            var formData = new FormData($(this)[0]);
            var action = config.admin_url + '/resturantes';

            if (id != 0) {
                formData.append('_method', 'PATCH');
                action = config.admin_url + '/resturantes/' + id;
            }

            $.ajax({
                url: action,
                type: 'post',
                data: formData,
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    $('#addEditResturantesForm .submit-form').prop('disabled', false);
                    $('#addEditResturantesForm .submit-form').html(lang.save);

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
                        if (id == 0) {
                            Resturantes.empty();
                        }
                    } else {
                        console.log(data)
                        if (typeof data.errors === 'object') {
                            for (i in data.errors)
                            {
                                var message = data.errors[i][0];
                                if (i.startsWith('working_hours')) {
                                    var key_arr = i.split('.');
                                    var key_text = key_arr[0] + '[' + key_arr[1] + ']' + '[' + key_arr[2] + ']';
                                    i = key_text;
                                }
                                if (i.startsWith('cuisines')) {
                                    i = 'cuisines[]';
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
                    $('#addEditResturantesForm .submit-form').prop('disabled', false);
                    $('#addEditResturantesForm .submit-form').html(lang.save);
                    My.ajax_error_message(xhr);
                },
                dataType: "json",
                type: "POST"
            });

            return false;

        })




    }

    var handleRecords = function () {

        Resturantes_grid = $('.dataTable').dataTable({
            //"processing": true,
            "serverSide": true,
            "ajax": {
                "url": config.admin_url + "/resturantes/data",
                "type": "POST",
                data: {_token: $('input[name="_token"]').val()},
            },
            "columns": [
                {"data": "resturant", "name": "resturant"},
                {"data": "image", "name": "image"},
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
                url: config.admin_url + '/resturantes/' + id,
                data: {_method: 'DELETE', _token: $('input[name="_token"]').val()},
                success: function (data)
                {

                    Clients_grid.api().ajax.reload();


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
            $('.help-block').html('');
            My.emptyForm();
        },
    };
}();
$(document).ready(function () {
    Resturantes.init();
});