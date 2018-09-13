
$( document ).ready(function() {
	
$(window).scroll(function() {
if ($(this).scrollTop() > 1){  
$('.header').addClass("sticky");
}
else{
if ($(this).scrollTop() < 1){ 
$('.header').removeClass("sticky");
}}
});
	
(function($) {
$.fn.menumaker = function(options) {  
var cssmenu = $(this), settings = $.extend({
format: "dropdown",
sticky: false
}, options);
return this.each(function() {
$(this).find(".button").on('click', function(){
$(this).toggleClass('menu-opened');
var mainmenu = $(this).next('ul');
if (mainmenu.hasClass('open')) { 
mainmenu.slideToggle().removeClass('open');
}
else {
mainmenu.slideToggle().addClass('open');
if (settings.format === "dropdown") {
mainmenu.find('ul').show();
}
}
});
cssmenu.find('li ul').parent().addClass('has-sub');
multiTg = function() {
cssmenu.find(".has-sub").prepend('<span class="submenu-button"></span>');
cssmenu.find('.submenu-button').on('click', function() {
$(this).toggleClass('submenu-opened');
if ($(this).siblings('ul').hasClass('open')) {
$(this).siblings('ul').removeClass('open').slideToggle();
}
else {
$(this).siblings('ul').addClass('open').slideToggle();
}
});
};
if (settings.format === 'multitoggle') multiTg();
else cssmenu.addClass('dropdown');
if (settings.sticky === true) cssmenu.css('position', 'fixed');
resizeFix = function() {
var mediasize = 1199;
if ($( window ).width() > mediasize) {
cssmenu.find('ul').show();
}
if ($(window).width(1199) <= mediasize) {
cssmenu.find('ul').hide().removeClass('open');
}
};
resizeFix();
return $(window).on('resize', resizeFix);
});
};
})(jQuery);

(function($){
$(document).ready(function(){
$("#cssmenu").menumaker({
format: "multitoggle"
});
});
})(jQuery);

$('#cssmenu > ul > li > a').click(function(){
$('#cssmenu > ul > li > a').removeClass("active");
$(this).addClass("active");
});

$('.ordercont > a').click(function(){
$('.ordercont > a').removeClass("active");
$(this).addClass("active");
});
$('.ordercont2 > a').click(function(){
$('.ordercont2 > a').removeClass("active");
$(this).addClass("active");
});

$('.righton').click(function(){
//$('.righton').removeClass("active");
$(this).addClass("active");
});

$('.orderlast a').click(function(){
 $('.orderlast a').removeClass("active");
$(this).addClass("active");
});





wowReInitor(jQuery("#wowslider-container1"),{effect:"blur",prev:"",next:"",duration:19*100,delay:29*100,width:960,height:600,autoPlay:true,playPause:true,stopOnHover:false,loop:false,bullets:true,caption:true,captionEffect:"move",controls:true,onBeforeStep:0,images:0});


$(window).scroll(function(){
if ($(this).scrollTop() > 1000) {
$('.scrollToTop').fadeIn();
} else {
$('.scrollToTop').fadeOut();
}
});
//Click event to scroll to top
$('.scrollToTop').click(function(){
$('html, body').animate({scrollTop : 0},800);
return false;
});




 $('.your-studs').slick({
dots: true,
infinite: true,
speed: 2000,
slidesToShow:3,
slidesToScroll: 1,
autoplay: true,
autoplaySpeed: 3000,
responsive: 


[

 
{
breakpoint: 991,
settings: {
slidesToShow: 1,
slidesToScroll: 1
}
},

 
 
]
});



 $('.your-stud').slick({
dots: true,
infinite: true,
speed: 2000,
slidesToShow:1,
slidesToScroll: 1,
autoplay: true,
autoplaySpeed: 3000,
responsive: [
]
});


 








		  $("html").niceScroll({
	cursorcolor:'#ad2a3c',
	cursorwidth:'8px',
 	zindex:9999
  });
  
  
  
  function toggleIcon(e) {
$(e.target)
.prev('.panel-heading')
.find(".more-less")
.toggleClass('glyphicon-plus glyphicon-minus');
}
$('.panel-group').on('hidden.bs.collapse', toggleIcon);
$('.panel-group').on('shown.bs.collapse', toggleIcon);


$(".clicksho").click(function(){
    $(".advn-search").slideToggle();
});


 
$(function() {
    $('.type').change(function(){
        $('.row_dim')[ ($("option[value='parcel']").is(":checked"))? "show" : "hide" ]();  
    });
});

 


 //plugin bootstrap minus and plus
//http://jsfiddle.net/laelitenetwork/puJ6G/
$('.btn-number').click(function(e){
    e.preventDefault();
    
    fieldName = $(this).attr('data-field');
    type      = $(this).attr('data-type');
    var input = $("input[name='"+fieldName+"']");
    var currentVal = parseInt(input.val());
    if (!isNaN(currentVal)) {
        if(type == 'minus') {
            
            if(currentVal > input.attr('min')) {
                input.val(currentVal - 1).change();
            } 
            if(parseInt(input.val()) == input.attr('min')) {
                $(this).attr('disabled', true);
            }

        } else if(type == 'plus') {

            if(currentVal < input.attr('max')) {
                input.val(currentVal + 1).change();
            }
            if(parseInt(input.val()) == input.attr('max')) {
                $(this).attr('disabled', true);
            }

        }
    } else {
        input.val(0);
    }
});
$('.input-number').focusin(function(){
   $(this).data('oldValue', $(this).val());
});
$('.input-number').change(function() {
    
    minValue =  parseInt($(this).attr('min'));
    maxValue =  parseInt($(this).attr('max'));
    valueCurrent = parseInt($(this).val());
    
    name = $(this).attr('name');
    if(valueCurrent >= minValue) {
        $(".btn-number[data-type='minus'][data-field='"+name+"']").removeAttr('disabled')
    } else {
        alert('Sorry, the minimum value was reached');
        $(this).val($(this).data('oldValue'));
    }
    if(valueCurrent <= maxValue) {
        $(".btn-number[data-type='plus'][data-field='"+name+"']").removeAttr('disabled')
    } else {
        alert('Sorry, the maximum value was reached');
        $(this).val($(this).data('oldValue'));
    }
    
    
});


     
            var $inp = $('.rating-input');

            $inp.rating({
                min: 0,
                max: 5,
                step: 1,
                size: 'lg',
                showClear: false
            });

  
});


 $(function() {

// We can attach the `fileselect` event to all file inputs on the page
$(document).on('change', ':file', function() {
var input = $(this),
numFiles = input.get(0).files ? input.get(0).files.length : 1,
label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
input.trigger('fileselect', [numFiles, label]);
});

// We can watch for our custom `fileselect` event like this
$(document).ready( function() {
$(':file').on('fileselect', function(event, numFiles, label) {

var input = $(this).parents('.input-group').find(':text'),
log = numFiles > 1 ? numFiles + ' files selected' : label;

if( input.length ) {
input.val(log);
} else {
if( log ) alert(log);
}
});
});

});

 

var blank="http://upload.wikimedia.org/wikipedia/commons/c/c0/Blank.gif";
function readURL(input) {
if (input.files && input.files[0]) {
var reader = new FileReader();

reader.onload = function (e) {
$('.img_prev')
.attr('src', e.target.result)
.height(150);
};
reader.readAsDataURL(input.files[0]);
}
else {
var img = input.value;
$('.img_prev').attr('src',img).height(150);
}
}



$('.btn-number').click(function(e){
    e.preventDefault();
    
    fieldName = $(this).attr('data-field');
    type      = $(this).attr('data-type');
    var input = $("input[name='"+fieldName+"']");
    var currentVal = parseInt(input.val());
    if (!isNaN(currentVal)) {
        if(type == 'minus') {
            
            if(currentVal > input.attr('min')) {
                input.val(currentVal - 0).change();
            } 
            if(parseInt(input.val()) == input.attr('min')) {
                $(this).attr('disabled', true);
            }

        } else if(type == 'plus') {

            if(currentVal < input.attr('max')) {
                input.val(currentVal + 0).change();
            }
            if(parseInt(input.val()) == input.attr('max')) {
                $(this).attr('disabled', true);
            }

        }
    } else {
        input.val(0);
    }
});
 