var Coupons_grid;
var Coupons = function () {

    var init = function () {
        //$.extend(lang, new_lang);
        nextLevel = 1;
        handleRecords();
        handleSubmit();
         $('.colorpicker-default').colorpicker({
            format: 'hex'
        });
        handleChangeResturant();
    };

    var handleChangeResturant = function () {
        $('#resturant_id').on('change', function () {
            var resturant = $(this).val();
            $('#branch').html("");
            $('#branch').html('<option selected value="">' + lang.choose + '</option>');

            if (resturant) {
                $.get('' + config.admin_url + '/getResturantBranches/' + resturant, function (data) {
                    $('#branch').append(data);


                });
            }
        })
    }

    var handleRecords = function () {
        Coupons_grid = $('.dataTable').dataTable({
            //"processing": true,
            "serverSide": true,
            "ajax": {
                "url": config.admin_url + "/coupons/data",
                "type": "POST",
                data: {_token: $('input[name="_token"]').val()},
            },
            "columns": [
               
                 {"data": "coupon","name":"coupons.coupon"},
                 {"data": "resturant","name":"resturantes.title_"+config.lang_code},
                 {"data": "resturant_branch","name":"resturant_branches.title_"+config.lang_code},
                 {"data": "available_until","name":"coupons.available_until"},
                 {"data": "discount","name":"coupons.discount"},
                 {"data": "options", orderable: false,searchable:false}
            ],
            "order": [
                [2, "asc"]
            ],
            "oLanguage": {"sUrl": config.url + '/datatable-lang-' + config.lang_code + '.json'}

        });
    }


    var handleSubmit = function () {
        $('#addCouponsForm').validate({
            rules: {
                coupon: {
                    required: true,
                },
                available_until: {
                    required: true,
                },
                discount: {
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
        $('#addCoupons .submit-form').click(function () {

            if ($('#addCouponsForm').validate().form()) {
                $('#addCoupons .submit-form').prop('disabled', true);
                $('#addCoupons .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                setTimeout(function () {
                    $('#addCouponsForm').submit();
                }, 1000);
            }
            return false;
        });
        $('#addCouponsForm input').keypress(function (e) {
            if (e.which == 13) {
                if ($('#addCouponsForm').validate().form()) {
                    $('#addCoupons .submit-form').prop('disabled', true);
                    $('#addCoupons .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                    setTimeout(function () {
                        $('#addCouponsForm').submit();
                    }, 1000);
                }
                return false;
            }
        });



        $('#addCouponsForm').submit(function () {
           
            var action = config.admin_url + '/coupons';
            var formData = new FormData($(this)[0]);
            $.ajax({
                url: action,
                data: formData,
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    $('#addCoupons .submit-form').prop('disabled', false);
                    $('#addCoupons .submit-form').html(lang.save);
                     console.log(data);
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
                        Coupons_grid.api().ajax.reload();
                        Coupons.empty();

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
                    $('#addCoupons .submit-form').prop('disabled', false);
                    $('#addCoupons .submit-form').html(lang.save);
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
        delete: function (t) {
            var id = $(t).attr("data-id");
            My.deleteForm({
                element: t,
                url: config.admin_url + '/coupons/' + id,
                data: {_method: 'DELETE', _token: $('input[name="_token"]').val()},
                success: function (data)
                {

                    Clients_grid.api().ajax.reload();


                }
            });
        },
        status: function(t) {
            var coupon_id = $(t).data("id"); 
            $(t).prop('disabled', true);
            $(t).html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');

            $.ajax({
                     url: config.admin_url+'/coupons/status/'+coupon_id,
                     success: function(data){
                        
                     $(t).prop('disabled', false);
            
                    if (data.status == true) {
                      $(t).addClass('btn-info').removeClass('btn-danger');
                      $(t).html(lang.active);
                    }
                    else
                    {
                      $(t).addClass('btn-danger').removeClass('btn-info');
                      $(t).html(lang.not_active);
                    }
                   
                  },
                   error: function (xhr, textStatus, errorThrown) {
                       My.ajax_error_message(xhr);
                   },
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
    Coupons.init();
});

