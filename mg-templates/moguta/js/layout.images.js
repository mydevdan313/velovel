$(document).ready(function() {


    // big image
    // -----------------------------------------------------------
    $('.main-product-slide').bxSlider({
        pagerCustom: '.slides-inner',
        controls: false,
        mode: 'fade',
        useCSS: false
    });


    // change big image
    // ------------------------------------------------------------
    var $that = '';
    $(".mg-peview-foto").on('click', function() {
        var that = this;
        $(".main-product-slide").hide(0, function() {
            $(this).attr("src", $(that).attr("src")).attr("data-large", $(that).attr("data-large")).show(0);
        });
    });


    // slideset
    // -----------------------------------------------------------
    $('.c-carousel__images').owlCarousel({
        items: 3,
        nav: true,
        margin: 10,
        dots: false,
        mouseDrag: false,
        navText: [
            '<div class="c-carousel__arrow c-carousel__arrow--left"><svg class="icon icon--arrow-left"><use xlink:href="#icon--arrow-left"></use></svg></div>',
            '<div class="c-carousel__arrow c-carousel__arrow--right"><svg class="icon icon--arrow-right"><use xlink:href="#icon--arrow-right"></use></svg></div>'
        ]
    });


    // add active class in slideset
    // -----------------------------------------------------------
    $('.slides-inner a').click(function() {
        $(this).each(function() {
            $('.slides-inner a').removeClass('active');
            $(this).addClass('active');
        });
    });


    // magnify
    // ------------------------------------------------------------
    try {
        $('.main-product-slide .mg-product-image').each(function() {
            $(this).magnify({
                lensLeft: 310,
                lensTop: -5,                
                magnifiersize: [460,350],
            });
        });

    } catch (err) {}


    // fancybox
    // ------------------------------------------------------------
    $('.fancy-modal').fancybox({
        'overlayShow': false,
        tpl: {
            next: '<a title="'+locale.fancyNext+'" class="fancybox-nav fancybox-next" href="javascript:;"><span></span></a>',
            prev: '<a title="'+locale.fancyPrev+'" class="fancybox-nav fancybox-prev" href="javascript:;"><span></span></a>'
        }
    });


    // fancybox open
    // ------------------------------------------------------------
    $('body').on('click', '.tracker', function() {
        $('.product-details-image').each(function() {
            if ($(this).css('display') == 'block' || $(this).css('display') == 'list-item') {
                $(this).find('.fancy-modal').click();
            }
        });
    });


}); // end ready