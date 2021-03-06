
var Users_grid;
var Users = function () {
    var init = function () {

        $.extend(lang, new_lang);
        handleRecords();
    };
    
   
    var handleRecords = function () {

        Users_grid = $('.dataTable').dataTable({
            //"processing": true,
            "serverSide": true,
            "ajax": {
                "url": config.admin_url + "/users/data",
                "type": "POST",
                data: {_token: $('input[name="_token"]').val()},
            },
            "columns": [       
                {"data": "user_image",ordable:false},         
                {"data": "first_name", "name": "first_name"},
                {"data": "last_name", "name": "last_name"},
                {"data": "email", "name": "email"},
                {"data": "mobile", "name": "mobile"},
                {"data": "active", "name": "active"},
                {"data": "options",ordable:false,searchable:false},
            ],
            "order": [
                [1, "desc"]
            ],
            "oLanguage": {"sUrl": config.url + '/datatable-lang-' + config.lang_code + '.json'}

        });
    }
    var handleSubmit = function () {

        $('#addEditUsersForm').validate({
            rules: {
                first_name: {
                    required: true
                },
                last_name: {
                    required: true
                },
               
                mobile: {
                    required: true
                },
                user_image: {
                    required: true
                },
                email: {
                    required: true,
                    email: true,
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
        $('#addEditUsers .submit-form').click(function () {
            if ($('#addEditUsersForm').validate().form()) {
                $('#addEditUsers .submit-form').prop('disabled', true);
                $('#addEditUsers .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                setTimeout(function () {
                    $('#addEditUsersForm').submit();
                }, 1000);

            }
            return false;
        });
        $('#addEditUsersForm input').keypress(function (e) {
            if (e.which == 13) {
                if ($('#addEditUsersForm').validate().form()) {
                    $('#addEditUsers .submit-form').prop('disabled', true);
                    $('#addEditUsers .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                    setTimeout(function () {
                        $('#addEditUsersForm').submit();
                    }, 1000);
                }
                return false;
            }
        });



        $('#addEditUsersForm').submit(function () {
            var id = $('#id').val();
            var formData = new FormData($(this)[0]);
            var action = config.admin_url + '/users';
            if (id != 0) {
                formData.append('_method', 'PATCH');
                action = config.admin_url + '/users/' + id;
            }


            $.ajax({
                url: action,
                data: formData,
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    $('#addEditUsers .submit-form').prop('disabled', false);
                    $('#addEditUsers .submit-form').html(lang.save);

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
                        Users_grid.api().ajax.reload();

                        if (id != 0) {
                            $('#addEditUsers').modal('hide');
                        } else {
                            Users.empty();
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
                    $('#addEditUsers .submit-form').prop('disabled', false);
                    $('#addEditUsers .submit-form').html(lang.save);
                    My.ajax_error_message(xhr);
                },
                dataType: "json",
                type: "POST"
            });

            return false;

        })




    }



    return{
        init: function () {
            init();
        },
        edit: function (t) {
            var id = $(t).attr("data-id");
            My.editForm({
                element: t,
                url: config.admin_url + '/users/' + id,
                success: function (data)
                {
                    console.log(data);

                    Users.empty();
                    My.setModalTitle('#addEditUsers', lang.edit_user);

                    for (i in data.message)
                    {
                        if (i == 'password') {
                            continue;
                        } else if (i == 'image') {
                            $('.user_image_box').html('<img style="height:80px;width:150px;" class="user_image"  src="' + config.public_path + '/uploads/workers/' + data.message[i] + '" alt="your image" />');
                        }
                        else if(i == 'user'){
                            for (x in data.message.user) {
                                
                                $('#' + x).val(data.message.user[x]);
                            }
                            
                        }
                         else {
                            $('#' + i).val(data.message[i]);
                        }


                    }
                    $('#addEditUsers').modal('show');
                }
            });

        },
        delete: function (t) {
            var id = $(t).attr("data-id");
            My.deleteForm({
                element: t,
                url: config.admin_url + '/users/' + id,
                data: {_method: 'DELETE', _token: $('input[name="_token"]').val()},
                success: function (data)
                {
                    Users_grid.api().ajax.reload();
                }
            });
        },
        add: function () {
            Users.empty();
            My.setModalTitle('#addEditUsers', lang.add_user);
            $('#addEditUsers').modal('show');
        },
        empty: function () {
            $('#id').val(0);
            $('#active').find('option').eq(0).prop('selected', true);
            $('.has-error').removeClass('has-error');
            $('.has-success').removeClass('has-success');
            $('.help-block').html('');
            My.emptyForm();
        },

         status: function(t) {
            var user_id = $(t).data("id"); 
            $(t).prop('disabled', true);
            $(t).html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');

            $.ajax({
                    url: config.admin_url+'/users/status/'+user_id,
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
    Users.init();
});