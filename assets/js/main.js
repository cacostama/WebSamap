/* ==============
 ========= js documentation ==========================

 * template name: Medical
 * version: 1.0
 * description: Hospital Website Template
 * author: softivus
 * author url: https://www.templatemonster.com/authors/softivus/

    ==================================================

     01. preloader
     -------------------------------------------------
     02. on scroll actions
     -------------------------------------------------
     03. scroll top
     -------------------------------------------------
     04. data background
     -------------------------------------------------
     05. reply
     -------------------------------------------------
     06. contact form
     -------------------------------------------------
     07. timer countdown
     -------------------------------------------------
     08. copyright year
     -------------------------------------------------
     
    ==================================================
============== */

(function ($) {
    "use strict";

    jQuery(document).ready(function () {
        
        // pre_loader
        $("#pre_loader").delay(300).animate({
            "opacity": "0"
        }, 500, function () {
            $("#pre_loader").css("display", "none");
        });

        // on scroll actions
        var scroll_offset = 120;
        $(window).scroll(function(){
            var $this = $(window);
            if( $('.header_area').length ) {
                if( $this.scrollTop() > scroll_offset ) { 
                    $('.header_area').addClass('header-active');
                    $('.navbar').removeClass('nav-shadow');
                } else {
                    $('.header_area').removeClass('header-active');
                    $('.navbar').addClass('nav-shadow');
                }	
            }
        });


        // scroll top
        $(window).scroll(function(){
            if($(window).scrollTop() > 500 ){
                $('.scrollToTop').addClass('topActive');
            }
            else{
                $('.scrollToTop').removeClass('topActive');
            }
        });

        // data background
        $("[data-background]").each(function () {
            $(this).css(
                "background-image",
                "url(" + $(this).attr("data-background") + ")"
            );
        });

        // reply
        $(".reply").on("click", function () {
            $(this).toggleClass("reply-active");
            $(this).parent().next(".blog-comment-reply").slideToggle();
        });

        // contact form
        // ajax
        jQuery('#frmContactus').on('submit',function(e){
            jQuery('#msg').html('');
            jQuery('#submit').html('Please wait....');
            jQuery('#submit').attr('disabled',true);
            jQuery.ajax({
                url:'mail.php',
                type:'POST',
                data:jQuery('#frmContactus').serialize(),
                success:function(result){
                    jQuery('#msg').html(result);
                    jQuery('#submit').html('Send Message');
                    jQuery('#submit').attr('disabled',false);
                    jQuery('#frmContactus')[0].reset();

                    setTimeout(function () {
                        $('.alert-dismissible').fadeOut('slow', function(){
                            $(this).remove();
                        });
                    }, 3000);
                }
            });
            e.preventDefault();
        });
        
        // Email Subscribe
        jQuery('#frmSubscribe').on('submit',function(e){
            var emailSubscribe = jQuery("input[name='sMail']").val();
            jQuery('#subscribeMsg').html('');
            jQuery('#emailSubscribe').html('Please wait....');
            jQuery('#emailSubscribe').attr('disabled',true);
            jQuery.ajax({
                url:'mail.php',
                type:'POST',
                data: {
                    'subscribes': '',
                    'emailSubscribe': emailSubscribe
                },
                success:function(result){
                    jQuery('#subscribeMsg').html(result);
                    jQuery('#emailSubscribe').html('Subscribe');
                    jQuery('#emailSubscribe').attr('disabled',false);
                    jQuery('#frmSubscribe')[0].reset();

                    setTimeout(function () {
                        $('.alert-dismissible').fadeOut('slow', function(){
                            $(this).remove();
                        });
                    }, 3000);
                }
            });
            e.preventDefault();
        });

        // timer countdown
        let custom_countdown = document.querySelector(".custom_countdown");
        if(custom_countdown){
            const newDate = new Date('sep 12 23 23:59:59').getTime()
            const countdown = setInterval(() =>{
            
                const date = new Date().getTime()
                const diff = newDate - date
    
                const month =  Math.floor((diff % (1000 * 60 * 60 * 24 * (365.25 / 12) * 365)) / (1000 * 60 * 60 * 24 * (365.25 / 12)))
                const days = Math.floor(diff % (1000 * 60 * 60 * 24 * (365.25 / 12)) / (1000 * 60 * 60 * 24))
                const hours =  Math.floor(diff % (1000 * 60 * 60 * 24) / (1000 * 60 * 60))
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
                const seconds = Math.floor((diff % (1000 * 60)) / 1000)
    
                let secondsPara = document.querySelector(".seconds").innerHTML = seconds < 10 ? '0' + seconds : seconds
                let minutesPara = document.querySelector(".minutes").innerHTML = minutes < 10 ? '0' + minutes :minutes
                let hoursPara = document.querySelector(".hours").innerHTML = hours < 10 ? '0' + hours : hours
                let daysPara = document.querySelector(".days").innerHTML = days < 10 ? '0' + days : days
                let monthsPara = document.querySelector(".months").innerHTML = month < 10 ? '0' + month : month
    
                $(".secondsV1").html(secondsPara.toString().split('')[0]);
                $(".secondsV2").html(secondsPara.toString().split('')[1]);
                $(".minutesV1").html(minutesPara.toString().split('')[0]);
                $(".minutesV2").html(minutesPara.toString().split('')[1]);
                $(".hoursV1").html(hoursPara.toString().split('')[0]);
                $(".hoursV2").html(hoursPara.toString().split('')[1]);
                $(".daysV1").html(daysPara.toString().split('')[0]);
                $(".daysV2").html(daysPara.toString().split('')[1]);
                $(".monthsV1").html(monthsPara.toString().split('')[0]);
                $(".monthsV2").html(monthsPara.toString().split('')[1]);
    
                if(diff === 0){
                    clearInterval(countdown)
                    document.querySelector(".countdown").innerHTML = 'Time is up'
                }
    
            }, 1000)
        }


        // copyright year
        $("#copyYear").text(new Date().getFullYear());

    });
})(jQuery);
