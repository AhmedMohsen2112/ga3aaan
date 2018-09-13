var ResturantBranches_grid;
var resturant = 0;
var regions;

var delivery_places_count;
var ResturantBranches = function () {

    var init = function () {

        
        $.extend(config, new_config);
        nextLevel = 1;
        resturant =  $('#res').val();
        handleRecords();
        handleChangeCity();
        handleSubmit();
        handleAddOrRemoveItem();
        $('.colorpicker-default').colorpicker({
            format: 'hex'
        });
        delivery_places_count = $('.delivery-one').length;
        Map.initMap(true,true,true,false);


    };

     var handleAddOrRemoveItem = function () {
        $(document).on('click', '.remove-delivery-place', function () {

            var index = $('.delivery-one').index($(this).closest('tr'));
            $(this).closest('tr').remove();
            delivery_places_count--;

        });
        $('.add-delivery-place').on('click', function () {

            if ($('#city').val() == "") {
               ResturantBranches.error_message('please choose city first');
               return false;
            }
            
            if ($('#id').val() != 0 && $('#city').val() == config.city) {
              regions = "";
              var branch_regions = JSON.parse(config.regions);
              for (var x = 0; x < branch_regions.length; x++) {
                    var item = branch_regions[x];
                    regions += '<option value="' + item.id + '">' + item.title + '</option>';
               }
            }
            
            var html = '<tr class="delivery-one">' +
                    '<td>' +
                    '<select class="form-control edited" name="delivery_places[' + delivery_places_count + '][region_id]">';
          
            html += regions;
           
            html += '</select>' +
                    '</td>' +
                    '<td><input type="text" class="form-control form-filter input-sm" style="width:25%;" name="delivery_places[' + delivery_places_count + '][delivery_cost]" value=""></td>' +
                    '<td><a class="btn btn-danger remove-delivery-place">' + lang.remove + '</a></td>' +
                    '</tr>';


            $('#delivery-places-table tbody').append(html);
            delivery_places_count++;
        });
    }

    var handleChangeCity = function () {
        $('#city').on('change', function () {
            var city = $(this).val();
            $('#delivery-places-table tbody').html("");
            $('#region').html("");
            $('#region').html('<option selected value="">' + lang.choose + '</option>');
            regions = "";
            if (city) {
                $.get('' + config.admin_url + '/getRegionByCity/' + city, function (data) {
                    if (data.data.length != 0)
                    {
                        for (var x = 0; x < data.data.length; x++) {
                            var item = data.data[x];
                            regions += '<option value="' + item.id + '">' + item.title + '</option>';
                        }
                        $('#region').append(regions);
                    }


                }, "json");
            }
        })
    }


    var handleRecords = function () {
        ResturantBranches_grid = $('.dataTable').dataTable({
            //"processing": true,
            "serverSide": true,
            "ajax": {
                "url": config.admin_url + "/resturant_branches/data",
                "type": "POST",
                data: {resturant : resturant,_token: $('input[name="_token"]').val()},
            },
            "columns": [
//                    {"data": "user_input", orderable: false, "class": "text-center"},
                {"data": "city","name":"city.title_" + config.lang_code},
                {"data": "region","name":"region.title_" + config.lang_code},
                {"data": "active","name":"resturant_branches.active"},
                {"data": "options", orderable: false,searchable: false}
            ],
            
            "oLanguage": {"sUrl": config.url + '/datatable-lang-' + config.lang_code + '.json'}

        });
    }


    var handleSubmit = function () {
        $('#addEditResturantBranchesForm').validate({
            rules: {
                title_ar: {
                    required: true,
                },
                title_en: {
                    required: true,
                },
                city: {
                    required: true,
                },
                region: {
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
        $('#addEditResturantBranchesForm .submit-form').click(function () {

            if ($('#addEditResturantBranchesForm').validate().form()) {
                $('#addEditResturantBranchesForm .submit-form').prop('disabled', true);
                $('#addEditResturantBranchesForm .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                setTimeout(function () {
                    $('#addEditResturantBranchesForm').submit();
                }, 1000);
            }
            return false;
        });
        $('#addEditResturantBranchesForm input').keypress(function (e) {
            if (e.which == 13) {
                if ($('#addEditResturantBranchesForm').validate().form()) {
                    $('#addEditResturantBranchesForm .submit-form').prop('disabled', true);
                    $('#addEditResturantBranchesForm .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                    setTimeout(function () {
                        $('#addEditResturantBranchesForm').submit();
                    }, 1000);
                }
                return false;
            }
        });



        $('#addEditResturantBranchesForm').submit(function () {

            if($('.delivery-one').length == 0){
                ResturantBranches.error_message('please add branch delivery places first');
                $('#addEditResturantBranchesForm .submit-form').prop('disabled', false);
                $('#addEditResturantBranchesForm .submit-form').html(lang.save);
                return false;
            }
            var id = $('#id').val();
            var action = config.admin_url + '/resturant_branches';
            var formData = new FormData($(this)[0]);
            if (id != 0) {
                formData.append('_method', 'PATCH');
                action = config.admin_url + '/resturant_branches/' + id;
            }
            $.ajax({
                url: action,
                data: formData,
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    $('#addEditResturantBranchesForm .submit-form').prop('disabled', false);
                    $('#addEditResturantBranchesForm .submit-form').html(lang.save);

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
                        if (id == 0) {
                            ResturantBranches.empty();
                        }
                    } else {
                        if (typeof data.errors === 'object') {
                            console.log(data.errors);
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
                    $('#addEditResturantBranchesForm .submit-form').prop('disabled', false);
                    $('#addEditResturantBranchesForm .submit-form').html(lang.save);
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
            var id = $(t).attr("data-id");
            My.editForm({
                element: t,
                url: config.admin_url + '/resturant_branches/' + id,
                success: function (data)
                {
                    console.log(data);

                    ResturantBranches.empty();
                    My.setModalTitle('#addEditResturantBranches', lang.edit_branch);

                    for (i in data.message)
                    {
                       if (i == 'city_id') {
                           $('#city').val(data.message[i]);
                           city = data.message[i];
                        }
                        if (i == 'region_id') {
                           region = data.message[i];
                        }
                      $('#' + i).val(data.message[i]);
                    }

            
                    $.get('' + config.admin_url + '/getRegionByCity/' + city, function (data) {
                    $('#region').html('<option selected value="">' + lang.choose + '</option>')
                    if (data.data.length != 0)
                    {
                        $.each(data.data, function (index, Obj) {
                            if (region == Obj.id) {
                                $('#region').append($('<option>', {
                                value: Obj.id,
                                text: Obj.title,
                                selected : true
                              }));
                            }
                            else{
                               $('#region').append($('<option>', {
                                value: Obj.id,
                                text: Obj.title
                            }));
                            }
                           
                        });

                    }


                }, "json");
       
                    $('#addEditResturantBranches').modal('show');
                    $('#addEditResturantBranches').on('shown.bs.modal', function() {
                       Map.initMap(true,true,true,false);
                       google.maps.event.trigger(map, 'resize');
                       
                    });
                }
            });

        },
        delete: function (t) {

            var id = $(t).attr("data-id");
            My.deleteForm({
                element: t,
                url: config.admin_url + '/resturant_branches/' + id,
                data: {_method: 'DELETE', _token: $('input[name="_token"]').val()},
                success: function (data)
                {
                    ResturantBranches_grid.api().ajax.reload();
                }
            });

        },
        add: function () {
            ResturantBranches.empty();
            My.setModalTitle('#addEditResturantBranches', lang.add_branch);
            $('#addEditResturantBranches').modal('show');
            $('#addEditResturantBranches').on('shown.bs.modal', function() {
               Map.initMap(true,true,true,false);
                       google.maps.event.trigger(map, 'resize');
               
            })
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
            //$('#id').val(0);
            $('#category_icon').val('');
            $('#city').find('option').eq(0).prop('selected', true);
            $('#region').find('option').eq(0).prop('selected', true);
            $('#active').find('option').eq(0).prop('selected', true);
            $('input[type="checkbox"]').prop('checked', false);
            $('.image_uploaded_box').html('<img src="' + config.url + '/no-image.png" class="image" width="150" height="80" />');
            $('.has-error').removeClass('has-error');
            $('.has-success').removeClass('has-success');
            $('.help-block').html('');
            $('#delivery-places-table tbody').html("");
            regions = "";
            My.emptyForm();
        }
    };

}();
jQuery(document).ready(function () {
    ResturantBranches.init();
});

