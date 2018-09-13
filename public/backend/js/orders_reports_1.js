
var doc = new jsPDF();
var query;
var base64Img = null;


var margins = {
    top: 70,
    bottom: 40,
    left: 30,
    width: 550
};
var specialElementHandlers = {
    '#editor': function (element, renderer) {
        return true;
    }
};
var Orders = function () {

    var init = function () {

        handleReport();
        handleChangeResturant();
        imgToBase64(config.url + '/no-image.png', function (base64) {
            base64Img = base64;
        });
    };
    var handleChangeResturant = function () {
        $('#resturant').on('change', function () {
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
    var handleReport = function () {
        $('.btn-report').on('click', function () {
            var data = $("#orders-reports").serializeArray();


            var url = config.admin_url + "/orders_reports";
            var params = {};
            $.each(data, function (i, field) {
                var name = field.name;
                var value = field.value;
                if (value) {
                    if (name == "from" || name == "to") {
                        value = new Date(Date.parse(value));
                        value = getDate(value);
                    }
                    params[field.name] = field.value
                }

            });
            query = $.param(params);
            url += '?' + query;

            window.location.href = url;
            return false;
        })
    }

    var getDate = function (date) {
        var dd = date.getDate();
        var mm = date.getMonth() + 1; //January is 0!
        var yyyy = date.getFullYear();
        if (dd < 10) {
            dd = '0' + dd
        }
        if (mm < 10) {
            mm = '0' + mm
        }
        var edited_date = yyyy + '-' + mm + '-' + dd;
        return edited_date;
    }


    var generate3 = function ()
    {
        var pdf = new jsPDF('p', 'pt', 'a4');
        pdf.setFontSize(18);
        pdf.fromHTML(document.getElementById('html-2-pdfwrapper'),
                margins.left, // x coord
                margins.top,
                {
                    // y coord
                    width: margins.width// max width of content on PDF
                }, function (dispose) {
            headerFooterFormatting(pdf, pdf.internal.getNumberOfPages());
        },
                margins);

        var iframe = document.createElement('iframe');
        iframe.setAttribute('style', 'position:absolute;right:0; top:0; bottom:0; height:100%; width:650px; padding:20px;');
        document.body.appendChild(iframe);

        iframe.src = pdf.output('datauristring');
    }
    var headerFooterFormatting = function (doc, totalPages)
    {
        for (var i = totalPages; i >= 1; i--)
        {
            doc.setPage(i);
            //header
            header(doc);

            footer(doc, i, totalPages);
            doc.page++;
        }
    }
    ;

    var header = function (doc)
    {
        doc.setFontSize(30);
        doc.setTextColor(40);
        doc.setFontStyle('normal');

        if (base64Img) {
            doc.addImage(base64Img, 'JPEG', margins.left, 10, 40, 40);
        }

        doc.text("Report Header Template", margins.left + 50, 40);
        doc.setLineCap(2);
        doc.line(3, 70, margins.width + 43, 70); // horizontal line
    }
    ;


    var imgToBase64 = function (url, callback, imgVariable) {

        if (!window.FileReader) {
            callback(null);
            return;
        }
        var xhr = new XMLHttpRequest();
        xhr.responseType = 'blob';
        xhr.onload = function () {
            var reader = new FileReader();
            reader.onloadend = function () {
                imgVariable = reader.result.replace('text/xml', 'image/jpeg');
                callback(imgVariable);
            };
            reader.readAsDataURL(xhr.response);
        };
        xhr.open('GET', url);
        xhr.send();
    }


    var footer = function (doc, pageNumber, totalPages) {

        var str = "Page " + pageNumber + " of " + totalPages

        doc.setFontSize(10);
        doc.text(str, margins.left, doc.internal.pageSize.height - 20);

    }





    return {
        init: function () {
            init();
        },
        generate: function () {
            var pdf = new jsPDF('p', 'pt', 'a4');
            console.log(pdf);
            pdf.setFontSize(18);

            pdf.fromHTML(document.getElementById('content'),
                    margins.left, // x coord
                    margins.top,
                    {
                        // y coord
                        width: margins.width// max width of content on PDF
                    }, function (dispose) {
                headerFooterFormatting(pdf, pdf.internal.getNumberOfPages());
            },
                    margins);

            var iframe = document.createElement('iframe');
            iframe.setAttribute('style', 'position:absolute;right:0; top:0; bottom:0; height:100%; width:650px; padding:20px;');
            document.body.appendChild(iframe);

            iframe.src = pdf.output('datauristring');
        },
        generate2: function () {
            doc.fromHTML($('#content').html(), 15, 15, {
                'width': 170,
                'elementHandlers': specialElementHandlers
            });
            doc.save('sample-file.pdf');
        },
        download: function () {
            $.ajaxSetup({
                beforeSend: function (jqXHR, settings) {
                    if (settings.dataType === 'binary') {
                        settings.xhr().responseType = 'arraybuffer';
                        settings.processData = false;
                    }
                }
            })
            $.ajax({
                url: config.admin_url + '/orders_reports/download',
                data: query,
                async: false,
                xhrFields: {
                    responseType: 'blob'
                },
                success: function (data) {
                    console.log(data);
                },

                error: function (xhr, textStatus, errorThrown) {
                    console.log(xhr.responseText);
//                    $('#addEditProfileForm .submit-form').prop('disabled', false);
//                    $('#addEditProfileForm .submit-form').html(lang.save);
//                    My.ajax_error_message(xhr);
                },
                dataType: "blob",
                type: "Get"
            });
        }
    }

}();
jQuery(document).ready(function () {
    Orders.init();
});

