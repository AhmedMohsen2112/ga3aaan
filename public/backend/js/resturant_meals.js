var ResturantMeals_grid;
var menu_section;

var ResturantMeals = function () {
    var init = function () {
        $.extend(lang, new_lang);
        $.extend(config, new_config);
        menu_section=config.menu_section;
        handleRecords();
    };

  

    var handleRecords = function () {
        ResturantMeals_grid = $('.dataTable').dataTable({
            //"processing": true,
            "serverSide": true,
            "ajax": {
                "url": config.admin_url + "/resturant_meals/data",
                "type": "POST",
                data: {menu_section: menu_section, _token: $('input[name="_token"]').val()},
            },
            "columns": [
                {"data": "title", "name": "title_" + config.lang_code},
                {"data": "image", orderable: false, searchable: false},
                {"data": "active", orderable: false, searchable: false},
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
        status: function(t) {
            var meal_id = $(t).data("id"); 
            $(t).prop('disabled', true);
            $(t).html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');

            $.ajax({
                    url: config.admin_url+'/resturant_meals/status/'+meal_id,
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
    };
}();
$(document).ready(function () {
    ResturantMeals.init();
});