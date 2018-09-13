
var address = 0;
var Orders = function () {

    var init = function () {
        $.extend(config, new_config);
        handleCountDown();
        handleUpdateQuantity();
        handleRemoveFromOrder();
    }

    var handleCountDown = function () {
        if (config.minutes !== 'undefined') {
            var minutes = parseInt(config.minutes);
            var order_minutes_limit = parseInt(config.order_minutes_limit);
            if (minutes < order_minutes_limit) {
                var limit = order_minutes_limit - minutes;

                countDownMinute(limit);
            }
        }


    }
     var countDownMinute=  function (minutes) {
            var timer2 = minutes + ":00";
            var interval = setInterval(function () {


                var timer = timer2.split(':');
                //by parsing integer, I avoid all extra string processing
                var minutes = parseInt(timer[0], 10);
                var seconds = parseInt(timer[1], 10);
                --seconds;
                minutes = (seconds < 0) ? --minutes : minutes;
                if (minutes < 0) {
                    $('#countdown').hide().remove();
                    clearInterval(interval);
                } else {
                    seconds = (seconds < 0) ? 59 : seconds;
                    seconds = (seconds < 10) ? '0' + seconds : seconds;
                    //minutes = (minutes < 10) ?  minutes : minutes;
                    $('.countdown').html(minutes + ':' + seconds);
                    timer2 = minutes + ':' + seconds;
                }


            }, 1000);
        }
    var handleUpdateQuantity = function () {
        $('.btn-qty-plus,.btn-qty-minus').on('click', function () {
            var id = $(this).data('id');
            var quantity = parseInt($('input[name="qty[' + id + ']"]').val());
            var url = config.url + "/order-meal/update-quantity?id=" + id + '&qty=' + quantity;

            $('#loader').show();
            $('#cart-content').addClass('loading');
            setTimeout(function () {
                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function (data)
                    {
                        console.log(data);
                        $('#loader').hide();
                        $('#cart-content').removeClass('loading');
                        if (data.type == 'success') {
                            var PriceList = data.data;
                            for (var i in PriceList) {
                                $('#' + i).html(PriceList[i] + ' ' + lang.currency_sign)
                            }

                        } else {

                            $('.alert-success').hide();
                            $('.alert-danger').show().find('.message').html(data.message);

                        }


                    },
                    error: function (xhr, textStatus, errorThrown) {
                        $('#addToCartForm .submit-form').prop('disabled', false);
                        $('#addToCartForm .submit-form').html(lang.save);
                        App.ajax_error_message(xhr);
                    },
                });

            }, 1000);




        });
    }
    var handleRemoveFromOrder = function () {
        $('.remove-cart').on('click', function () {
            var ele = $(this);
            var id = $(this).data('id');
            var url = config.url + "/order-meal/remove?id=" + id;

            $('#loader').show();
            $('#cart-content').addClass('loading');
            setTimeout(function () {

                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function (data)
                    {
                        $('#loader').hide();
                        $('#cart-content').removeClass('loading');
                        if (data.type == 'success') {
                            if (typeof data.message !== 'undefined') {
                                $('#newMessageModal .message-content').html(lang.order_is_deleted);
                                $('#newMessageModal').modal('show');
                                $('#newMessageModal').on('hidden.bs.modal', function () {
                                    window.location.href = data.message;
                                })

                            }
                            if (typeof data.data !== 'undefined') {
                                ele.closest('.item-row').remove();
                                var PriceList = data.data;
                                for (var i in PriceList) {
                                    $('#' + i).html(PriceList[i] + ' ' + lang.currency_sign)
                                }
                            }

                        } else {
                            $('.alert-success').hide();
                            $('.alert-danger').show().find('.message').html(data.message);


                        }


                    },
                    error: function (xhr, textStatus, errorThrown) {
                        $('#addToCartForm .submit-form').prop('disabled', false);
                        $('#addToCartForm .submit-form').html(lang.save);
                        App.ajax_error_message(xhr);
                    },
                });

            }, 1000);




        });
    }
    var handleRemoveOrder = function () {
        $('.remove-order').on('click', function () {
            var ele = $(this);
            var id = $(this).data('id');
            var url = config.url + "/orders/remove?id=" + id;

            $('#loader').show();
            $('#cart-content').addClass('loading');
            setTimeout(function () {

                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function (data)
                    {
                        $('#loader').hide();
                        $('#cart-content').removeClass('loading');
                        if (data.type == 'success') {
                            if (typeof data.message !== 'undefined') {
                                $('#newMessageModal .message-content').html(lang.order_is_deleted);
                                $('#newMessageModal').modal('show');
                                $('#newMessageModal').on('hidden.bs.modal', function () {
                                    window.location.href = data.message;
                                })

                            }
                            if (typeof data.data !== 'undefined') {
                                ele.closest('.item-row').remove();
                                var PriceList = data.data;
                                for (var i in PriceList) {
                                    $('#' + i).html(PriceList[i] + ' ' + lang.currency_sign)
                                }
                            }

                        } else {
                            $('.alert-success').hide();
                            $('.alert-danger').show().find('.message').html(data.message);


                        }


                    },
                    error: function (xhr, textStatus, errorThrown) {
                        $('#addToCartForm .submit-form').prop('disabled', false);
                        $('#addToCartForm .submit-form').html(lang.save);
                        App.ajax_error_message(xhr);
                    },
                });

            }, 1000);



            return false;
        });
    }




    return {
        init: function () {
            init();
        },
        delete: function (t) {
            $(t).prop('disabled', true);
            $(t).html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
            var id = $(t).attr("data-id");
            //console.log(id);
            App.deleteForm({
                element: t,
                url: config.url + '/user-orders/' + id,
                data: {_method: 'DELETE', _token: $('input[name="_token"]').val()},
                success: function (data)
                {
                    window.location.href=data.message
                }
            });

        },
    }


}();

$(document).ready(function () {
    Orders.init();
});