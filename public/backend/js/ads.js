var Ads_grid;
var Ads = function () {
    var init = function () {
        //alert('heree');
        $.extend(lang, new_lang);
        //console.log(lang);
        handleRecords();
        handleSubmit();
        My.readImageMulti('ad_image');

    };

    var handleRecords = function () {

        Ads_grid = $('.dataTable').dataTable({
            //"processing": true,
            "serverSide": true,
            "ajax": {
                "url": config.admin_url + "/ads/data",
                "type": "POST",
                data: {_token: $('input[name="_token"]').val()},
            },
            "columns": [
//                    {"data": "user_input", orderable: false, "class": "text-center"},
      
                {"data": "ad_image"},
                {"data": "active"},
                {"data": "this_order"},
                {"data": "options", orderable: false, searchable: false}
            ],
            "order": [
                [1, "desc"]
            ],
            "oLanguage": {"sUrl": config.url + '/datatable-lang-' + config.lang_code + '.json'}

        });
    }
    var handleSubmit = function () {

        $('#addEditAdsForm').validate({
            rules: {
                url: {
                    required: true

                },
                this_order: {
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
        $('#addEditAds .submit-form').click(function () {
            if ($('#addEditAdsForm').validate().form()) {
                $('#addEditAds .submit-form').prop('disabled', true);
                $('#addEditAds .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                setTimeout(function () {
                    $('#addEditAdsForm').submit();
                }, 1000);

            }
            return false;
        });
        $('#addEditAdsForm input').keypress(function (e) {
            if (e.which == 13) {
                if ($('#addEditAdsForm').validate().form()) {
                    $('#addEditAds .submit-form').prop('disabled', true);
                    $('#addEditAds .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
                    setTimeout(function () {
                        $('#addEditAdsForm').submit();
                    }, 1000);

                }
                return false;
            }
        });



        $('#addEditAdsForm').submit(function () {
            var id = $('#id').val();
            var action = config.admin_url + '/ads';
            var formData = new FormData($(this)[0]);
            if (id != 0) {
                formData.append('_method', 'PATCH');
                action = config.admin_url + '/ads/' + id;
            }



            $.ajax({
                url: action,
                data: formData,
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    console.log(data);
                    $('#addEditAds .submit-form').prop('disabled', false);
                    $('#addEditAds .submit-form').html(lang.save);

                    if (data.type == 'success')
                    {
                        My.toast(data.message);
                        Ads_grid.api().ajax.reload();

                        if (id != 0) {
                            $('#addEditAds').modal('hide');
                        } else {
                            Ads.empty();
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
                    $('#addEditAds .submit-form').prop('disabled', false);
                    $('#addEditAds .submit-form').html(lang.save);
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
                url: config.admin_url + '/ads/' + id,
                success: function (data)
                {
                    console.log(data);
                    Ads.empty();
                    My.setModalTitle('#addEditAds', lang.edit_consultation_group);

                    for (i in data.message)
                    {
                        if (i == 'ad_image') {
                            $('.ad_image_box').html('<img style="height:80px;width:150px;" class="ad_image plate_img"  src="' + config.public_path + '/uploads/ads/' + data.message[i] + '" alt="your image" />');
                        }  else {
                            $('#' + i).val(data.message[i]);
                        }

                    }
                    $('#addEditAds').modal('show');
                }
            });

        },
        delete: function (t) {
            var id = $(t).attr("data-id");
            My.deleteForm({
                element: t,
                url: config.admin_url + '/ads/' + id,
                data: {_method: 'DELETE', _token: $('input[name="_token"]').val()},
                success: function (data)
                {

                    Ads_grid.api().ajax.reload();


                }
            });
        },
        add: function () {
            Ads.empty();
            My.setModalTitle('#addEditAds', lang.add_consultation_group);
            $('#addEditAds').modal('show');
        },
        empty: function () {
            $('#id').val(0);
            $('#active').find('option').eq(0).prop('selected', true);
            $('.has-error').removeClass('has-error');
            $('.has-success').removeClass('has-success');
            $('.help-block').html('');
            $('input[type="checkbox"]').prop("checked", false).trigger("change");
            My.emptyForm();
        },
    };
}();
$(document).ready(function () {
    Ads.init();
});