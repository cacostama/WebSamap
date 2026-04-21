/* ==============
 ========= js documentation ==========================
 
 * template name: Medical
 * version: 1.0
 * description: Hospital Website Template
 * author: softivus
 * author url: https://www.templatemonster.com/authors/softivus/

    ==================================================

     01. wow init
     -------------------------------------------------
     02. magnificPopup
     -------------------------------------------------
     03. odometer counter
     -------------------------------------------------
     04. form validate
     -------------------------------------------------
     05. CircularProgressBar
     -------------------------------------------------
     06. team slider
     -------------------------------------------------
     07. team--cardiology slider
     -------------------------------------------------
     08. team--pulmonologist slider
     -------------------------------------------------
     09. testimonial slider
     -------------------------------------------------
     10. client-company slider
     -------------------------------------------------
     11. service slider
     -------------------------------------------------

    ==================================================
============== */

(function ($) {
    "use strict";

    jQuery(document).ready(function () {
        // wow init
        new WOW().init();

        // magnificPopup
        $('.popup_img').magnificPopup({
            type: 'image',
            gallery: {
                enabled: true
            }
        });
        // odometer counter
        $(".odometer").each(function () {
            $(this).isInViewport(function (status) {
                if (status === "entered") {
                    for (
                        var i = 0;
                        i < document.querySelectorAll(".odometer").length;
                        i++
                    ) {
                        var el = document.querySelectorAll(".odometer")[i];
                        el.innerHTML = el.getAttribute("data-odometer-final");
                    }
                }
            });
        });

        // form validate
        $("#frmContactus").validate({
            rules: {
                name: {
                    required: true,
                    minlength: 2
                },
                message: {
                    required: true,
                    minlength: 5
                },
                email: {
                    required: true,
                    email: true
                },
                phone: {
                    required: true
                }
            },
            messages: {
                name: {
                    minlength: "Name should be at least 2 characters"
                },
                message: {
                    number: "Offer should be at least 5 characters"
                }
            }
        });

        // CircularProgressBar
            const elements = [].slice.call(document.querySelectorAll(".pie"));
            const circle = new CircularProgressBar("pie");
    
            const config = {
                root: null,
                rootMargin: "0px",
                threshold: 0.75,
                
            };
    
            const ovserver = new IntersectionObserver((entries, observer) => {
                entries.map((entry) => {
                    if (entry.isIntersecting && entry.intersectionRatio > 0.75) {
                        circle.initial(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, config);
    
            elements.map((item) => {
                ovserver.observe(item);
            });
    

        // hero slider
        $('.slider-hero').slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: false,
            fade: true,
            asNavFor: '.slider-nav'
        });
        $('.slider-nav').slick({
            slidesToShow: 4,
            slidesToScroll: 1,
            vertical:true,
            asNavFor: '.slider-hero',
            arrows: false,
            focusOnSelect: true,
            verticalSwiping:true,
            responsive: [
            {
                breakpoint: 1199,
                settings: {
                  vertical: false,
                }
            },
            {
                breakpoint: 992,
                settings: {
                  vertical: false,
                }
            },
            {
              breakpoint: 768,
              settings: {
                slidesToShow: 3,
                vertical: false,
              }
            },
            {
              breakpoint: 576,
              settings: {
                vertical: false,
                slidesToShow: 3,
              }
            },
            {
              breakpoint: 380,
              settings: {
                vertical: false,
                slidesToShow: 2,
              }
            }
            ]
        });


        // team slider
        $(".team__slider")
            .not(".slick-initialized")
            .slick({
                infinite: false,
                autoplay: true,
                focusOnSelect: true,
                slidesToShow: 4,
                speed: 1000,
                slidesToScroll: 1,
                arrows: true,
                dots: true,
                prevArrow: $(".prev-team"),
                nextArrow: $(".next-team"),
                appendDots: $('.news__dots'),
                customPaging: function (slider, i) {
                    return '' + (i + 1);
                },
                dotsClass: 'news__dots-list',
                centerPadding: "100px",
                responsive: [
                    {
                        breakpoint: 992,
                        settings: {
                            slidesToShow: 3,
                        },
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 2,
                        },
                    },
                    {
                        breakpoint: 576,
                        settings: {
                            slidesToShow: 1,
                        },
                    },
                ],
            });

        // team--cardiology-slider
        $(".cardiology-slider")
            .not(".slick-initialized")
            .slick({
                infinite: false,
                // autoplay: true,
                focusOnSelect: true,
                slidesToShow: 4,
                speed: 1000,
                slidesToScroll: 1,
                arrows: true,
                dots: false,
                prevArrow: $(".prev-cardiology"),
                nextArrow: $(".next-cardiology"),
                responsive: [
                    {
                        breakpoint: 1200,
                        settings: {
                            slidesToShow: 3,
                        },
                    },
                    {
                        breakpoint: 992,
                        settings: {
                            slidesToShow: 2,
                        },
                    },
                    {
                        breakpoint: 576,
                        settings: {
                            slidesToShow: 1,
                        },
                    },
                ],
            });

        // team--pulmonologist-slider
        $(".pulmonologist-slider")
            .not(".slick-initialized")
            .slick({
                infinite: false,
                // autoplay: true,
                focusOnSelect: true,
                slidesToShow: 4,
                speed: 1000,
                slidesToScroll: 1,
                arrows: true,
                dots: false,
                prevArrow: $(".prev-pulmonologist"),
                nextArrow: $(".next-pulmonologist"),
                responsive: [
                    {
                        breakpoint: 1200,
                        settings: {
                            slidesToShow: 3,
                        },
                    },
                    {
                        breakpoint: 992,
                        settings: {
                            slidesToShow: 2,
                        },
                    },
                    {
                        breakpoint: 576,
                        settings: {
                            slidesToShow: 1,
                        },
                    },
                ],
            });


        // testimonial slider
        $(".testimonial__slider")
            .not(".slick-initialized")
            .slick({
                infinite: true,
                autoplay: true,
                // focusOnSelect: true,
                slidesToShow: 3,
                slidesToScroll: 1,
                speed: 1000,
                arrows: false,
                dots: true,
                centerPadding: "0",
                responsive: [
                    {
                        breakpoint: 992,
                        settings: {
                            slidesToShow: 2,
                        },
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 2,
                        },
                    },
                    {
                        breakpoint: 576,
                        settings: {
                            slidesToShow: 1,
                        },
                    },
                ],
            });

        // client-company slider
        $(".client-company__slider")
            .not(".slick-initialized")
            .slick({
                infinite: true,
                autoplay: true,
                slidesToShow: 4,
                slidesToScroll: 1,
                speed: 1000,
                arrows: false,
                dots: true,
                centerPadding: "0",
                responsive: [
                    {
                        breakpoint: 992,
                        settings: {
                            slidesToShow: 3,
                        },
                    },
                    {
                        breakpoint: 576,
                        settings: {
                            slidesToShow: 3,
                        },
                    },
                    {
                        breakpoint: 479,
                        settings: {
                            slidesToShow: 2,
                        },
                    },
                ],
            });

        // service slider
        $(".service__slider")
        .not(".slick-initialized")
        .slick({
            
            infinite: true,
            autoplay: true,
            slidesToShow: 4,
            slidesToScroll: 4,
            speed: 1000,
            arrows: false,
            responsive: [
                {
                    breakpoint: 992,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 3,
                        centerMode: false,
                    },
                },
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2,
                        centerMode: false,
                    },
                },
                {
                    breakpoint: 480,
                    settings: {
                        slidesToShow: 1,
                        centerMode: false,
                    },
                },
            ],
        });

    });
})(jQuery);
