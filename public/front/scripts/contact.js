var Contact = function(){

	var init = function(){
		validateform();
	}

	var validateform = function()
	{
		
		$("#contactus_form").validate({
			ignore: "",
			rules: {
				subject:{
					required : true,
				},
				email:{
					required : true,
					email: true,
				},
				type:{
					required : true,
				},
				message:{
					required : true,
				}
			},


			highlight: function(element) {
				$(element).closest('.form-group').addClass('has-error');
			},

			unhighlight: function(element) {
				$(element).closest('.form-group').removeClass('has-error').addClass('has-success');
				$(element).closest('.form-group').find('.help-block').html('');
			},
			errorPlacement: function (error, element) {
				$(element).closest('.form-group').find('.help-block').html($(error).html());
			}

		});

		$('#contactus_form .submit-form').click(function () {

			if ($('#contactus_form').validate().form()) {
				$('#contactus_form .submit-form').prop('disabled', true);
				$('#contactus_form .submit-form').html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>');
				setTimeout(function () {
					$('#contactus_form').submit();
				}, 1000);
			}
			return false;
		});


		$('#contactus_form').submit(function(e){

			var form = $(this);
			e.preventDefault(); 
			var form_data = new FormData($(this)[0]);
			var method = "POST";
        	var url = form.attr('action');

			$.ajax({
				type : method,
				url : url,
				dataType : "JSON",
				data:form_data,
				processData: false,
				contentType: false,
	             success: function (data){
	        	if (data.type == 'success') 
	        	{
	        		$('#contactus_form')[0].reset();
                    $('#contactus_form').find('div').removeClass('has-success');
	        		$('#contactus_form .submit-form').prop('disabled', false);
	        		$('#contactus_form .submit-form').html(lang.send);

	        	    $('#alert-message').fadeIn(2000).delay(3000).fadeOut(2000);
                    var message = '<i class="fa fa-check" aria-hidden="true"></i> <span>' + data.message + '</span> ';
                    $('#alert-message').html(message);

	        	} else {
	        		$('#contactus_form .submit-form').prop('disabled', false);
	        		$('#contactus_form .submit-form').html(lang.send);
	        		if (typeof data.errors === 'object') {
	        			console.log(data.errors);
	        			associate_errors(data.errors);
	        		} 
	        	}


	        },
	        error: function (xhr, textStatus, errorThrown) {
	        	$('#contactus_form .submit-form').prop('disabled', false);
	            $('#contactus_form .submit-form').html(lang.send);
	        	App.ajax_error_message(xhr);
	        },
	    }); 
		});


	}

	var associate_errors = function(errors, form)
	{
		$('.help-block').html('');
		$.each(errors,function(index, value)
		{
			var element = 'input[name='+index+']';
			$(element).closest('.form-group').addClass('has-error');
			$(element).closest('.form-group').find('.help-block').html(value);


		}
		);
	}
	
	
	

	

	return{

		init:function(){
			init();
		},
		
	}


}();

$(document).ready(function() {
	Contact.init();
});





