
var Main = function () {


    var init = function () {
        handleChangeLang();
        handlePusher();
    }





    var handleChangeLang = function () {
        $(document).on('change', '#change-lang', function () {
            var lang_code = $(this).val();
            var action = config.admin_url + '/change_lang';
            $.ajax({
                url: action,
                data: {lang_code: lang_code},
                async: false,
                success: function (data) {
                    console.log(data);
                    if (data.type == 'success') {

                        window.location.reload()

                    }


                },
                error: function (xhr, textStatus, errorThrown) {
                    My.ajax_error_message(xhr);
                },
                dataType: "JSON",
                type: "GET"
            });

            return false;
        });
    }
    var handlePusher = function () {
        Pusher.logToConsole = true;
        var pusher_app_id = config.pusher_app_id;
        var pusher_cluster = config.pusher_cluster;
        var pusher_encrypted = config.pusher_encrypted;
        var resturant = config.resturant;
        var pusher = new Pusher(pusher_app_id, {
            cluster: pusher_cluster,
            encrypted: pusher_encrypted
        });

        var new_order = pusher.subscribe('new_order');

        new_order.bind('App\\Events\\updateOrderStatus', function (data) {
            console.log(data);
            if (!resturant || data.resturant == resturant) {
                handleNotiSound();
                My.toast(data.message);
                if (typeof ResturantOrders_grid !== 'undefined') {
                    ResturantOrders_grid.api().ajax.reload();
                }
            }

        });
        pusher.connection.bind('connected', function () {
            socketId = pusher.connection.socket_id;
            console.log(socketId);
        });
    }
    var handleNotiSound = function () {
        var sound = '<audio style="display:none;" id="noti-sound" controls>' +
                '<source src="' + config.url + '/public/front/tones/consequence.ogg" type="audio/mpeg">Your browser does not support the audio element.' +
                '</audio>';
        $('html').append(sound);
        var ele = $(document).find('#noti-sound');
        ele[0].play();

        //$('#reserve-count').html(data.reserve_count);
        setTimeout(function () {
            ele.remove()
        }, 1000);
    }




    return {
        init: function () {
            init();
        },

    }

}();

jQuery(document).ready(function () {
    Main.init();
});


