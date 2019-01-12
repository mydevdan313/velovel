$(document).ready(function() {

    // add active link
    // ------------------------------------------------------------
    $('nav a').each(function() {
        var location = window.location.href;
        var link = this.href;
        if (location == link) {
            $(this).addClass('active');
        }
    });

 // c-nav (mobile menu)
    // ------------------------------------------------------------

    $("#c-nav__catalog .c-nav__menu").mouseover (function () {
        MenuOpenCloseTimer (
            function () {
                $('.c-catalog .c-button, .l-main, .c-catalog__dropdown--1').addClass('active');
                $('#c-nav__catalog').addClass('c-nav--open');
            }
        );
    });

    $(".l-header__block .c-catalog").mouseover (function (e) {
        if (e.target === this){
            MenuOpenCloseTimer (
                function () {
                    $('.c-catalog .c-button, .l-main, .c-catalog__dropdown--1').addClass('active');
                    $('#c-nav__catalog').addClass('c-nav--open');
                }
            );
        }
    });
     

    $("#c-nav__catalog .c-nav__menu>.c-nav__dropdown").mouseout (function () {
        MenuOpenCloseTimer (
            function () {
                $('.c-catalog .c-button, .l-main, .c-catalog__dropdown--1').removeClass('active');
                $('#c-nav__catalog').removeClass('c-nav--open');
            }
        );
    });

    $(".l-header__block .c-catalog, #c-nav__catalog .c-nav__menu>.c-nav__dropdown li").hover (function () {
        MenuOpenCloseTimer (
            function () {
                $('.c-catalog .c-button, .l-main, .c-catalog__dropdown--1').addClass('active');
                $('#c-nav__catalog').addClass('c-nav--open');
            }
        );
    },function () {
        MenuOpenCloseTimer (
            function () {
                $('.c-catalog .c-button, .l-main, .c-catalog__dropdown--1').removeClass('active');
                $('#c-nav__catalog').removeClass('c-nav--open');
            }
        );
    });

    $(".l-header__top .l-header__block .c-button, .l-header__top .l-header__block #c-nav__menu .c-nav__menu").hover (function () {
        MenuOpenCloseTimer (
            function () {
                $('.l-header__top .l-header__block #c-nav__menu').addClass('c-nav--open');
            }
        );
    },function () {
        MenuOpenCloseTimer (
            function () {
                $('.l-header__top .l-header__block #c-nav__menu').removeClass('c-nav--open');
            }
        );
    });

    function MenuOpenCloseTimer (funct){
        if (typeof this.delayTimer == "number"){
            clearTimeout (this.delayTimer);
            this.delayTimer = '';
        }
        this.delayTimer = setTimeout (function() {funct ();}, 200);
    }

    $('body').on('click', 'a[href^="#c-nav"]', function(a) {
        a.preventDefault();
        var b = $(this).attr('href');
        $(b).addClass('c-nav--open');

    }), $('body').on('click', '.c-nav', function() {
        $('.c-nav').removeClass('c-nav--open');

    }), $('body').on('click', '.c-nav__menu', function(a) {
        a.stopPropagation()
    });


    $('body').on('click', 'a[href^="#c-nav__menu"]', function(a) {
        a.preventDefault();
        var b = $(this).attr('href');
        $(b).addClass('c-nav--open');
        $('body').addClass('fixed__body')

    }), $('body').on('click', '.c-nav', function() {
        $('.c-nav').removeClass('c-nav--open');
        $('body').removeClass('fixed__body')

    }), $('body').on('click', '.c-nav__menu', function(a) {
        a.stopPropagation()
    });
 
  
  
  $(".c-menu").click(function() {
  $('.c-nav--open').toggle();
});
$(document).on('click', function(e) {
  if (!$(e.target).closest(".c-nav--open").length) {
    $('c-nav.c-nav--open').hide();
  }

  e.stopPropagation();
});  
  
  
 

    $('body').on('click', '.c-nav__level--1', function() {
        var a = $(this).siblings();

        if ($(window).width() < 1025) {
            a.find('.c-nav__dropdown--2').slideUp('fast');
            $(this).find('.c-nav__dropdown--2').slideToggle('fast');
        }
        a.find('.c-nav__icon').removeClass('rotate');
        $(this).find('.c-nav__icon').toggleClass('rotate');
    });


    // c-catalog
    // ------------------------------------------------------------
    $('.c-catalog .c-button').on('click', function() {
        if ($(window).width() < 1025) {
            $('.c-catalog .c-button, .l-main, .c-catalog__dropdown--1').toggleClass('active');
        }

    }), $('body').on('click', function() {
        if ($(window).width() < 1025) {
            $('.c-catalog .c-button, .l-main, .c-catalog__dropdown--1').removeClass('active');
        }

    }), $('body').on('click', '.c-catalog', function(a) {
        a.stopPropagation()
    });

    $('.c-catalog__level').hoverIntent({
        sensitivity: 3,
        interval: 100,
        timeout: 200,
        over: function() {
            $(this).find('> .c-catalog__dropdown').addClass('active');
        },
        out: function() {
            $(this).find('.c-catalog__dropdown').removeClass('active');
        }
    });


    // c-modal
    // ------------------------------------------------------------
    $('body').on('click', 'a[href^="#c-modal"]', function(a) {
        a.preventDefault();
        var b = $(this).attr('href');
        $(b).addClass('c-modal--open');
        if ($(document).height() > $(window).height()) {
            $('html').addClass('c-modal--scroll');
        }

    }), $('body').on('click', '.c-modal, .c-modal__close, .c-modal__cart', function() {
        $('.c-modal').removeClass('c-modal--open');
        $('html').removeClass('c-modal--scroll');

    }), $('body').on('click', '.c-modal__content', function(a) {
        a.stopPropagation()
    });


    // c-switcher
    // ------------------------------------------------------------
    function rememberView() {
        var className = localStorage['class'];
        //localStorage.clear();
        $('.c-switcher__item[data-type="' + className + '"]').addClass('c-switcher__item--active').siblings().removeClass('c-switcher__item--active');
        $('.c-goods').addClass(className);
        $('.c-switcher__item').on('click', function() {
            var currentView = $(this).data('type');
            var product = $('.c-goods');

            product.removeClass('c-goods--grid c-goods--list');
            product.addClass(currentView);

            $('.c-switcher__item').removeClass('c-switcher__item--active');
            $(this).addClass('c-switcher__item--active');

            localStorage.setItem('class', $(this).data('type'));
            return false;
        });
    }
    rememberView();


    // с-carousel
    // ------------------------------------------------------------
    $('.c-carousel__content').owlCarousel({
        nav: true,
        margin: 16,
        dots: false,
        mouseDrag: false,
        responsive: {
            0: {
                items: 1,
                margin: 10,
            },
            360: {
                items: 2
            },
            768: {
                items: 3
            },
            990: {
                items: 4
            }
        },
        navText: [
            '<div class="c-carousel__arrow c-carousel__arrow--left"><svg class="icon icon--arrow-left"><use xlink:href="#icon--arrow-left"></use></svg></div>',
            '<div class="c-carousel__arrow c-carousel__arrow--right"><svg class="icon icon--arrow-right"><use xlink:href="#icon--arrow-right"></use></svg></div>'
        ]
    });

    if ($('.c-carousel__content').hasClass('owl-loaded')) {
        $('.c-carousel').addClass('c-carousel--active');
    }
    // С этим товаром покупают
    $('.c-carousel__content--related').owlCarousel({
        nav: true,
        margin: 16,
        dots: false,
        mouseDrag: false,
        responsive: {
            0: {
                items: 2,
                margin: 10
            },
            768: {
                items: 3
            },
            990: {
                items: 4
            }
        },
        navText: [
            '<div class="c-carousel__arrow c-carousel__arrow--left"><svg class="icon icon--arrow-left"><use xlink:href="#icon--arrow-left"></use></svg></div>',
            '<div class="c-carousel__arrow c-carousel__arrow--right"><svg class="icon icon--arrow-right"><use xlink:href="#icon--arrow-right"></use></svg></div>'
        ]
    });

    if ($('.c-carousel__content--related').hasClass('owl-loaded')) {
        $('.c-carousel').addClass('c-carousel--active');
    }    

    $('.mg-brand-block').owlCarousel({
        loop: true,
        nav: true,
        responsive: {
            0: {
                items: 2,
                margin: 10
            },
            768: {
                items: 3
            },
            990: {
                items: 6
            }
        },
        navText: [
            '<div class="c-carousel__arrow c-carousel__arrow--left"><svg class="icon icon--arrow-left"><use xlink:href="#icon--arrow-left"></use></svg></div>',
            '<div class="c-carousel__arrow c-carousel__arrow--right"><svg class="icon icon--arrow-right"><use xlink:href="#icon--arrow-right"></use></svg></div>'
        ]        
    });

    // c-filter
    // ------------------------------------------------------------
   $('body').on('click', 'a[href^="#c-filter"]', function(a) {
        a.preventDefault();
        var b = $(this).attr('href');
        $(b).addClass('c-filter--active');
        $('body').addClass('fixed__body');

    }), $('body').on('click', '.c-filter', function() {
        $('.c-filter').removeClass('c-filter--active');
        $('body').removeClass('fixed__body');

    }), $('body').on('click', '.c-filter__content', function(a) {
        a.stopPropagation()
    });
 



    // c-tab
    // ------------------------------------------------------------
    $('body').on('click', 'a[href^="#c-tab"]', function(a) {
        a.preventDefault();
        var b = $(this).attr('href');
        $(b).addClass('c-tab__content--active');
        $(b).siblings().removeClass('c-tab__content--active');

        $(this).addClass('c-tab__link--active');
        $(this).siblings().removeClass('c-tab__link--active');
    });


    // c-compare
    // ------------------------------------------------------------
    if ($('.c-compare__item').length == 0) {
        $('.c-compare').hide();
    }


    // plugin "slider-action"
    // ------------------------------------------------------------
    $(document).ready(function () {
        $('.m-p-slider-wrapper').addClass('show');
    });


    // plugin "product-slider"
    // ------------------------------------------------------------
    $(document).ready(function () {
        $('.mg-advise').addClass('mg-advise--active');
    });


    // agreement
    // ------------------------------------------------------------
    $('.l-body').on('change', '.agreement-container [type="checkbox"]', function () {
        if ($(this).prop('checked')) {
           $(this).closest('label').removeClass('nonactive').addClass('active');
        } 
        else{
            $(this).closest('label').removeClass('active').addClass('nonactive');
        }
    });
    
    // order
    // ------------------------------------------------------------
    $('.c-order__checkbox label').on('click',function () {
        if ($(this).children('[type="checkbox"]').is(':checked')) {
           $(this).removeClass('nonactive').addClass('active');
        } else {
            $(this).removeClass('active').addClass('nonactive');
        }
    });
    $('.c-order__radiobutton label, .order-storage label').on('click',function () {
        if ($(this).children('[type="radio"]').is(':checked')) {
           $(this).removeClass('nonactive').addClass('active');
           $(this).siblings('label').removeClass('active');
        } 
    });

}); // end ready

$('input, textarea').each(function(){
          var $elem = $(this);
          if($elem.attr('placeholder') && !$elem[0].placeholder){
            var $label = $('<label class="placeholder"></label>').text($elem.attr('placeholder'));
            $elem.before($label);
            $elem.blur();
            if($elem.val() === ''){ $label.addClass('visible'); }
            $label.click(function(){
              $label.removeClass('visible'); $elem.focus();
            });
            $elem.focus(function(){
              if($elem.val() === ''){ $label.removeClass('visible'); }
            });
            $elem.blur(function(){
              if($elem.val() === ''){ $label.addClass('visible'); }
            });
          }
        });