var ResturantOrders_grid;
var type;


var ResturantOrders = function () {
    var init = function () {
        $.extend(lang, new_lang);
        $.extend(config, new_config);
        type = $('#type').val();
        handleRecords();
        handleSubmit();
        showOrHideRefuse();
       

    };

    var showOrHideRefuse = function(){
        $('#order_status').on('change',function(){
            if (this.value == 4) {
                $('#refusing').show();
            }
            else{
                $('#refusing').hide();
            }
        })
    }

     var handleSubmit = function () {

        $('#orderStatusForm').validate({
            rules: {
              
                order_status: {
                    required: true
                },
                order_id: {
                    required: true
                },
                refusing_reason:{
                    required: true
                }
               
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
        $('#orderStatusForm .submit-form').click(function () {
            if ($('#orderStatusForm').validate().form()) {
                $('#orderStatusForm .submit-form').prop('disabled', true);
                $('#orderStatusForm .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                setTimeout(function () {
                    $('#orderStatusForm').submit();
                }, 1000);

            }
            return false;
        });
        $('#orderStatusForm input').keypress(function (e) {
            if (e.which == 13) {
                if ($('#orderStatusForm').validate().form()) {
                    $('#orderStatusForm .submit-form').prop('disabled', true);
                    $('#orderStatusForm .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                    setTimeout(function () {
                        $('#orderStatusForm').submit();
                    }, 1000);
                }
                return false;
            }
        });



        $('#orderStatusForm').submit(function () {
            var formData = new FormData($(this)[0]);
            var action = config.admin_url + '/change_order_status';


            $.ajax({
                url: action,
                type: 'post',
                data: formData,
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    $('#orderStatusForm .submit-form').prop('disabled', false);
                    $('#orderStatusForm .submit-form').html(lang.save);

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
                        location.reload();
                       // console.log(data);
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
                    $('#orderStatusForm .submit-form').prop('disabled', false);
                    $('#orderStatusForm .submit-form').html(lang.save);
                    My.ajax_error_message(xhr);
                },
                dataType: "json",
                type: "POST"
            });

            return false;

        })




    }

    var handleRecords = function () {
        ResturantOrders_grid = $('.datatable').dataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": config.admin_url + "/resturant_orders/data",
                "type": "POST",
                data: { _token: $('input[name="_token"]').val(), order_type: type},
            },
            "columns": [
                {"data": "id"},
                {"data": "branch_title",name:"resturant_branches.title_"+config.lang_code},
                {"data": "name", orderable: false},
                {"data": "address", orderable: false,searchable: false},
                {"data": "mobile", "name": "users.mobile"},
                {"data": "status","name": "orders.status", searchable: false, orderable: false},
                {"data": "created_at","name": "orders.created_at", searchable: false, orderable: false},
                {"data": "options", orderable: false, searchable: false}
            ],
            
            "oLanguage": {"sUrl": config.url + '/datatable-lang-' + config.lang_code + '.json'}

        });
    }

  




    return{
        init: function () {
            init();
        }
    };
}();
$(document).ready(function () {
    ResturantOrders.init();
});