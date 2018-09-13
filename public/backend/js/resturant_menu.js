var ResturantMenu_grid;


var ResturantMenu = function () {
    var init = function () {
        handleRecords();
 
    };



    var handleRecords = function () {
        ResturantMenu_grid = $('.dataTable').dataTable({
            //"processing": true,
            "serverSide": true,
            "ajax": {
                "url": config.admin_url + "/resturant_menu/data",
                "type": "POST",
                data: {_token: $('input[name="_token"]').val()},
            },
            "columns": [
                {"data": "title", "name": "title_" + config.lang_code},
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
        }
    };
}();
$(document).ready(function () {
    ResturantMenu.init();
});