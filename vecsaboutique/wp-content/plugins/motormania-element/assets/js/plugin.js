(function($) {
    "use strict";

    // Mega menu button
    $(function() {
        $('.dropdown-mega-menu-toggle').on('hover', function(event) {
            event.preventDefault();

            const dropdown = $(this).closest('.menu-item');

            if (dropdown.is('.open')) {
                dropdown.removeClass('open');
            } else {
                dropdown.addClass('open');
            }
        });

        $('.mega-menu-content').on('mouseenter', function(event) {
            $(this).closest('.menu-item').addClass('open');
        });

        $('.mega-menu-content').on('mouseleave', function(event) {
            $(this).closest('.menu-item').removeClass('open');
        });
    });


    // AJAX Select Search One
    $('.motormania-select-search #select-one').on('change', function(event) {
        $('.motormania-select-search #select-two').prop("disabled", true);
        $.ajax({
            url: motormaniaPluginAjaxObj.ajaxurl,
            type: 'POST',
            data: {
                action: 'motormania_products_select_one_search',
                product_id: $(this).find(':selected').data('id')
            },
            success: function(data) {
                $('.motormania-select-search #select-two').html(data);
                $('.motormania-select-search #select-two').removeAttr("disabled");
            },
            error: function(error) {
                alert(error);
            }
        });

    });

    // AJAX Select Search Two
    $('.motormania-select-search #select-two').on('change', function(event) {
        $('.motormania-select-search #select-three').prop("disabled", true);
        $.ajax({
            url: motormaniaPluginAjaxObj.ajaxurl,
            type: 'POST',
            data: {
                action: 'motormania_products_select_two_search',
                product_id: $(this).find(':selected').data('id')
            },
            success: function(data) {
                $('.motormania-select-search #select-three').html(data);
                $('.motormania-select-search #select-three').removeAttr("disabled");
            },
            error: function(error) {
                alert(error);
            }
        });

    });

    // AJAX Select Search Three
    $('.motormania-select-search #select-three').on('change', function(event) {
        $('.motormania-select-search #select-four').prop("disabled", true);
        $.ajax({
            url: motormaniaPluginAjaxObj.ajaxurl,
            type: 'POST',
            data: {
                action: 'motormania_products_select_two_search',
                product_id: $(this).find(':selected').data('id')
            },
            success: function(data) {
                $('.motormania-select-search #select-four').html(data);
                $('.motormania-select-search #select-four').removeAttr("disabled");
            },
            error: function(error) {
                alert(error);
            }
        });

    });

    // Countdown
    $('[data-date]').each(function(i, obj) {

        var CounterID = '#' + this.id;
        // Countdown
        var countDownDate = new Date($(this).data('date')).getTime();

        // Update the count down every 1 second
        var x = setInterval(function() {

            // Get today's date and time
            var now = new Date().getTime();

            // Find the distance between now and the count down date
            var distance = countDownDate - now;

            // Time calculations for days, hours, minutes and seconds
            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            $(CounterID).html(`
            <div class="countdown-date">
                <span>${hours}</span> <b>:</b> <span>${minutes}</span> <b>:</b> <span>${seconds}</span>
            </div>
            `);

            // If the count down is finished, write some text
            if (distance < 0) {
                clearInterval(x);
                $(CounterID).html("EXPIRED");
            }
        }, 1000);
    });


    // Slick RTL Support
    function rtl_slick() {
        if ($('body').hasClass("rtl")) {
            return true;
        } else {
            return false;
        }
    }

    // product items
    $('.banner-slider').slick({
        arrows: true,
        infinite: true,
        autoplay: true,
        autoplaySpeed: 2000,
        infinite: true,
        speed: 2000,
        fade: true,
        rtl: rtl_slick(),
        slidesToShow: 1,
        slidesToScroll: 1,
        nextArrow: '<i class="fas fa-chevron-right"></i>',
        prevArrow: '<i class="fas fa-chevron-left"></i>',
        responsive: [
            {
                "breakpoint": 600,
                "settings": {
                    arrows: false
                }
            }
        ]
    });


    // product items
    $('.product-items').slick({
        arrows: true,
        infinite: true,
        rtl: rtl_slick(),
        slidesToShow: 3,
        slidesToScroll: 3,
        responsive: [{
                "breakpoint": 1200,
                "settings": {
                    "slidesToShow": 3,
                    "slidesToScroll": 3
                }
            },
            {
                "breakpoint": 1024,
                "settings": {
                    "slidesToShow": 2,
                    "slidesToScroll": 2
                }
            },
            {
                "breakpoint": 600,
                "settings": {
                    "slidesToShow": 1,
                    "slidesToScroll": 1
                }
            }
        ],
        nextArrow: '<i class="fas fa-chevron-right"></i>',
        prevArrow: '<i class="fas fa-chevron-left"></i>'
    });

    // product items tab
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        $('.product-items').slick('setPosition');
    });

    // testimonial
    $('.testimonials').slick({
        slidesToShow: 3,
        slidesToScroll: 3,
        infinite: true,
        arrows: true,
        rtl: rtl_slick(),
        responsive: [{
            "breakpoint": 1200,
            "settings": {
                "slidesToShow": 3,
                "slidesToScroll": 3
            }
        },
        {
            "breakpoint": 1024,
            "settings": {
                "slidesToShow": 2,
                "slidesToScroll": 2
            }
        },
        {
            "breakpoint": 600,
            "settings": {
                "slidesToShow": 1,
                "slidesToScroll": 1
            }
        }],
        nextArrow: '<i class="fas fa-chevron-right"></i>',
        prevArrow: '<i class="fas fa-chevron-left"></i>'
    });

    // Elementor front-end
    $(window).on('elementor/frontend/init', function() {

        elementorFrontend.hooks.addAction('frontend/element_ready/slider_banner.default', function($scope, $) {

            $scope.find('.banner-slider').not('.slick-initialized').slick({
                arrows: true,
                infinite: true,
                "slidesToShow": 1,
                "slidesToScroll": 1,
                nextArrow: '<i class="fas fa-chevron-right"></i>',
                prevArrow: '<i class="fas fa-chevron-left"></i>'
            });

        });


        elementorFrontend.hooks.addAction('frontend/element_ready/testimonials.default', function($scope, $) {

            $scope.find('.testimonials').not('.slick-initialized').slick({
                slidesToShow: 3,
                slidesToScroll: 3,
                infinite: true,
                arrows: true,
                rtl: rtl_slick(),
                responsive: [{
                    "breakpoint": 1200,
                    "settings": {
                        "slidesToShow": 3,
                        "slidesToScroll": 3
                    }
                },
                {
                    "breakpoint": 1024,
                    "settings": {
                        "slidesToShow": 2,
                        "slidesToScroll": 2
                    }
                },
                {
                    "breakpoint": 600,
                    "settings": {
                        "slidesToShow": 1,
                        "slidesToScroll": 1
                    }
                }],
                nextArrow: '<i class="fas fa-chevron-right"></i>',
                prevArrow: '<i class="fas fa-chevron-left"></i>'
            });

        });

        elementorFrontend.hooks.addAction('frontend/element_ready/products_carousel.default', function($scope, $) {

            $scope.find('.product-items').not('.slick-initialized').slick({
                arrows: true,
                infinite: true,
                rtl: rtl_slick(),
                nextArrow: '<i class="fas fa-chevron-right"></i>',
                prevArrow: '<i class="fas fa-chevron-left"></i>'
            });

        });

    });

})(jQuery);