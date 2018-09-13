var Offers_grid;

var Offers = function () {
    var init = function () {
        $.extend(lang);
        handleRecords();
        handleShowOrHideMenuSections();
        handleShowOrHideDiscount();
        handleSubmit();
        My.readImageMulti('image');
    };

    var handleShowOrHideMenuSections = function () {
        $('#resturnantes').on('change', function () {
            var resturant = $(this).val();
            if ($('input[name=type]:checked').val() == '2' || $('input[name=type]:checked').val() == '3') {
                $("#menu_sections").show();
                if (resturant) {
                    getMenuSectionsByResturant(resturant);

                } else {
                    $('#menu_section').html(lang.no_results);
                }
            }


        });
    }

    var getMenuSectionsByResturant = function (resturant) {
        $.get('' + config.admin_url + '/getMenueSectionsByResturant/' + resturant, function (data) {
            var html = '';
            if (data.data.length != 0)
            {
                $.each(data.data, function (index, Obj) {
                    html += '<div class="md-checkbox has-success">' +
                            '<input type="checkbox" id="' + Obj.id + '-' + Obj.title + '" name="menu_sections[]" value="' + Obj.id + '" class="md-check">' +
                            '<label for="' + Obj.id + '-' + Obj.title + '">' +
                            '<span class="inc"></span>' +
                            '<span class="check"></span>' +
                            '<span class="box"></span>' + Obj.title + '</label>' +
                            '</div>';
                });

            }
            $('#menu_section').html(html);

        }, "json");
    }

    var handleShowOrHideDiscount = function () {
        $('input[name=type]').change(function () {
            var value = $(this).val();
            if (value == '4') {
                $("#menu_sections").hide();
                $("#discount_v").hide();
            } else {
                if (value != '1') {
                    var resturant = $('#resturnantes').val();
                    if (resturant && $('#menu_section').html() == '') {
               
                        $("#menu_sections").show();
                        $("#menu_section").html('');
                        getMenuSectionsByResturant(resturant);
                    }
                } else {
                    $("#menu_sections").hide()
                    $("#menu_section").html('');
                }
                $("#discount_v").show();
            }
        });
    }


    var handleSubmit = function () {

        $('#addEditOffersForm').validate({
            rules: {

                resturant: {
                    required: true
                },
                discount: {
                    required: true
                },
                available_until: {
                    required: true,
                },
                this_order: {
                    required: true
                },
                active: {
                    required: true
                },
                'offers[]': {
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
        $('#addEditOffersForm .submit-form').click(function () {
            if ($('#addEditOffersForm').validate().form()) {
                $('#addEditOffersForm .submit-form').prop('disabled', true);
                $('#addEditOffersForm .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                setTimeout(function () {
                    $('#addEditOffersForm').submit();
                }, 1000);

            }
            return false;
        });
        $('#addEditOffersForm input').keypress(function (e) {
            if (e.which == 13) {
                if ($('#addEditOffersForm').validate().form()) {
                    $('#addEditOffersForm .submit-form').prop('disabled', true);
                    $('#addEditOffersForm .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                    setTimeout(function () {
                        $('#addEditOffersForm').submit();
                    }, 1000);
                }
                return false;
            }
        });



        $('#addEditOffersForm').submit(function () {
            var id = $('#id').val();
            var formData = new FormData($(this)[0]);
            var action = config.admin_url + '/offers';

            if (id != 0) {
                formData.append('_method', 'PATCH');
                action = config.admin_url + '/offers/' + id;
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
                    $('#addEditOffersForm .submit-form').prop('disabled', false);
                    $('#addEditOffersForm .submit-form').html(lang.save);

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
                            Offers.empty();
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
                    $('#addEditOffersForm .submit-form').prop('disabled', false);
                    $('#addEditOffersForm .submit-form').html(lang.save);
                    My.ajax_error_message(xhr);
                },
                dataType: "json",
                type: "POST"
            });

            return false;

        })




    }

    var handleRecords = function () {
        Offers_grid = $('.dataTable').dataTable({
            //"processing": true,
            "serverSide": true,
            "ajax": {
                "url": config.admin_url + "/offers/data",
                "type": "POST",
                data: {_token: $('input[name="_token"]').val()},
            },
            "columns": [
                {"data": "resturant", "name": "resturantes.title_" + config.lang_code},
                {"data": "image", orderable: false, searchable: false},
                {"data": "discount", "name": "offers.this_order"},
                {"data": "available_until", "name": "offers.available_until"},
                {"data": "this_order", "name": "offers.this_order"},
                {"data": "active", "name": "offers.active"},
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
                url: config.admin_url + '/offers/' + id,
                data: {_method: 'DELETE', _token: $('input[name="_token"]').val()},
                success: function (data)
                {

                    Offers_grid.api().ajax.reload();


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
            $('#menu_sections').html('');
            $('.help-block').html('');
            My.emptyForm();
        },
    };
}();
$(document).ready(function () {
    Offers.init();
});