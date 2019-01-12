/**
 * Подключается в карточке товара
 */
$(document).ready(function() {
  //Выбирает текущий тумбнейл
  $('.slides-inner a').click(function() {
    $(this).each(function() {
      $('.slides-inner a').removeClass('active-item');
      $(this).addClass('active-item');
    });
  });

  //Инициализация fancybox
  $(".close-order, a.fancy-modal").fancybox({
    'overlayShow': false,
    hideScrollbar: false,
    tpl: {
      next: '<a title="'+locale.fancyNext+'" class="fancybox-nav fancybox-next" href="javascript:;"><span></span></a>',
      prev: '<a title="'+locale.fancyPrev+'" class="fancybox-nav fancybox-prev" href="javascript:;"><span></span></a>'
    }
  });

  //Слайдер картинок в карточке товаров
  $('.main-product-slide').bxSlider({
    pagerCustom: '.slides-inner',
    controls: false,
    mode: 'fade',
    useCSS: false
  });

  //Слайдер тумбнейлов
  $('.slides-inner').bxSlider({
    minSlides: 3,
    maxSlides: 3,
    slideWidth: 75,
    pager: false,
    slideMargin: 10,
    useCSS: false
  });

	try {
    $('.main-product-slide .mg-product-image').each(function(){
      $(this).magnify({
        lensLeft: 310,
        lensTop: -5,     
    });});
	}
	catch(err) { }
	
  
  //клик по превью-картинке
  var $that = '';
  $(".mg-peview-foto").click(function() {
    var that = this;
    //копируем атрибуты из превью-картинки в контейнер-картинку
    $(".main-product-slide").fadeOut(600, function() {
      $(this).attr("src", $(that).attr("src")).attr("data-large", $(that).attr("data-large")).fadeIn(1000);
    });
  });
  // открытие фенсимодал
  $('body').on('click', '.tracker', function() {
    $('.product-details-image').each(function() {
      if ($(this).css('display') == 'block' || $(this).css('display') == 'list-item') {
        $(this).find('.fancy-modal').click();
      }
    });
  });

});  