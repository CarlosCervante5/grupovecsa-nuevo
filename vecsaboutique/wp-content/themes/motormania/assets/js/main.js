(function($) {
    "use strict";

    //Preloader
    $(window).on('load', function() {
        $('#preloader').delay(350).fadeOut('slow');
    });

    // AJAX Search
    $(document).on('mouseup', function(e) {

        var searchresuls = $(".sidebar-search");

        // if the target of the click isn't the container nor a descendant of the container
        if (!searchresuls.is(e.target) && searchresuls.has(e.target).length === 0) {
            $("#datafetch").hide();
        }
    });

    // AJAX Search
    $('#keyword').on('keyup', function(event) {
        $.ajax({
            url: motormaniaAjaxUrlObj.ajaxurl,
            type: 'POST',
            data: {
                action: 'motormania_ajax_search',
                keyword: $('#keyword').val()
            },
            success: function(data) {
                if ($('#keyword').val().length !== 0) {
                    $('#datafetch').html(data);
                } else {
                    $('#datafetch').html('');
                }

            }
        });

        $("#datafetch").show();
    });

    // magnificPopup ajax view
    $('.ajax-quick-view-popup').on('click', function(event) {
        event.preventDefault();

        $('.ajax-quick-view-popup i').removeClass('fa-search-plus').addClass('fa-spinner fa-spin');

        $.ajax({
            url: motormaniaAjaxUrlObj.ajaxurl,
            type: 'POST',
            data: {
                action: 'motormania_ajax_quick_view',
                productid: $(this).attr('data-product-id')
            },
            success: function(data) {
                $.magnificPopup.open({
                    items: {
                        src: data,
                        type: 'inline'
                    },
                    closeMarkup: '<button title="%title%" type="button" class="mfp-close"></button>',
                });
                $('.ajax-quick-view-popup i').removeClass('fa-spinner fa-spin').addClass('fa-search-plus');
            }
        });

    });

    // Departments menu button
    $(function() {
        $('.departments-menu-button').on('click', function(event) {
            event.preventDefault();

            const dropdown = $(this).closest('.departments-container');

            if (dropdown.is('.open')) {
                dropdown.removeClass('open');
            } else {
                dropdown.addClass('open');
            }
        });

        $(document).on('click', function(event) {
            $('.departments-container')
                .not($(event.target).closest('.departments-container'))
                .removeClass('open');
        });
    });

    // Shopping Cart menu button
    $(function() {
        $('.shopping-cart-button').on('click', function(event) {
            event.preventDefault();

            const dropdown = $(this).closest('.shopping-cart-widget');

            if (dropdown.is('.open')) {
                dropdown.removeClass('open');
            } else {
                dropdown.addClass('open');
            }
        });

        $(document).on('click', function(event) {
            $('.shopping-cart-widget')
                .not($(event.target).closest('.shopping-cart-widget'))
                .removeClass('open');
        });
    });

    // My Account menu button
    $(function() {
        $('.my-account-button').on('click', function(event) {
            event.preventDefault();

            const dropdown = $(this).closest('.my-account-widget');

            if (dropdown.is('.open')) {
                dropdown.removeClass('open');
            } else {
                dropdown.addClass('open');
            }
        });

        $(document).on('click', function(event) {
            $('.my-account-widget')
                .not($(event.target).closest('.my-account-widget'))
                .removeClass('open');
        });
    });

    //Navbar Fixed
    $(window).on('scroll', function() {
        if ($(document).scrollTop() > 80) {
            $('.sticky-header,.off-canvas-menu-bar').addClass('fixed-top');
        } else {
            $('.sticky-header,.off-canvas-menu-bar').removeClass('fixed-top');
        }
    });

    // Newsletter Modal
    var storage = window.localStorage;
    $(window).on('load', function() {

        setTimeout(function() {
            if ( storage.getItem('modal_stop') !== 'true' ) {
                $('#newsletterModal').modal('show');
            }
        }, $('#newsletterModal').data('time'));

        $('#dont-show').on('click', function() {
            if ($(this).is(":checked")) {
                storage.setItem('modal_stop', true);
            }
        });

        $('#dont-show-hour').on('click', function() {
            storage.setItem('modal_stop', true);
        });

    });

    //Accordion
    $('.motormania-accordion-item:first-child').addClass('active');
    $('.motormania-accordion-item:first-child .collapse').addClass('show');
    $('.collapse').on('shown.bs.collapse', function() {
        $(this).parent().addClass('active');
    });

    $('.collapse').on('hidden.bs.collapse', function() {
        $(this).parent().removeClass('active');
    });

    //Mobile Nav Hide Show
    if ($('.off-canvas-menu').length) {

        var mobileMenuContent = $('.desktop-menu>ul').html();
        $('.off-canvas-menu .navigation').append(mobileMenuContent);

        //Menu Toggle Btn
        $('.mobile-nav-toggler').on('click', function() {
            $('body').addClass('off-canvas-menu-visible');
        });

        //Menu Toggle Btn
        $('.off-canvas-menu .menu-backdrop,.off-canvas-menu .close-btn').on('click', function() {
            $('body').removeClass('off-canvas-menu-visible');
        });
    }

    $('.off-canvas-menu li.menu-item-has-children').append('<div class="dropdown-btn"><span class="fa fa-angle-down"></span></div>');
    $('.off-canvas-menu li.menu-item-has-children .dropdown-btn').on('click', function() {
        $(this).prev('ul').slideToggle(500);
    });

    // Wishlist share icon update
    $('.yith-wcwl-share .share-button i.fa').addClass('fab').removeClass('fa');
    $('.single-product .yith-wcwl-icon.fa-heart-o').addClass('fa-heart').removeClass('fa-heart-o');

    //Back to top
    $(window).on('scroll', function() {
        if ($(this).scrollTop() >= 700) {
            $('#backtotop').fadeIn(500);
        } else {
            $('#backtotop').fadeOut(500);
        }
    });

    $('#backtotop').on('click', function() {
        $('body,html').animate({
            scrollTop: 0
        }, 500);
    });

})(jQuery);