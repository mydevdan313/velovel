// список отложенных функций, выполняемых после фильтрации аяксом.
// Пример использования в сторонних JS:
// AJAX_CALLBACK_FILTER = [
//        {callback: 'settings.closeAllTab', param: null},
//        {callback: 'settings.openTab', param: ['tab-system']},
// ];
var AJAX_CALLBACK_FILTER = [];
var VIEW_ALL_FILTER = -1;

$(document).ready(function() {

  var ajaxUpdate = !$('.apply-filter-form').data('print-res');    
  
  if(!ajaxUpdate && location.search.indexOf("applyFilter") > 0){
    var destination = $('form.apply-filter-form').offset().top;    
    $('html, body').animate({ scrollTop: destination }, 500);
  }   

  function mgInitFilter() {
    $("#price-slider").slider({
      min: $("input#minCost").data("fact-min"),
      max: $("input#maxCost").data("fact-max"),
      values: [$("input#minCost").val(), $("input#maxCost").val()],
      step: 10,
      range: true,
      stop: function(event, ui) {
        $("input#minCost").val($("#price-slider").slider("values", 0));
        $("input#maxCost").val($("#price-slider").slider("values", 1));
        getFilteredItems($('.filter-form #maxCost'));
      },
      slide: function(event, ui) {
        $("input#minCost").val($("#price-slider").slider("values", 0));
        $("input#maxCost").val($("#price-slider").slider("values", 1));
      }
    });

    $("input#minCost").change(function() {
      var value1 = $("input#minCost").val();
      var value2 = $("input#maxCost").val();

      if (parseInt(value1) > parseInt(value2)) {
        value1 = value2;
        $("input#minCost").val(value1);
      }
      $("#price-slider").slider("values", 0, value1);
    });

    $("input#maxCost").change(function() {
      var value1 = $("input#minCost").val();
      var value2 = $("input#maxCost").val();

      if (parseInt(value1) > parseInt(value2)) {
        value2 = value1;
        $("input#maxCost").val(value2);
      }
      
      $("#price-slider").slider("values", 1, value2);
    });

    $("input#maxCost").change(function() {
      var value = $("input#maxCost").val();

      if(value == '') {
        $("input#maxCost").val($("input#maxCost").data("fact-max"));
      }
    });


    // Собираем слайдер с ползунками для всех характеристик #ДОБАВЛЕНО
    $(".mg-filter-item .mg-filter-prop-slider").each(function(i) {

      var min = parseInt($(this).data("min"));
      var max = parseInt($(this).data("max"));

      var fMin = (parseInt($(this).data("factmin"))) ? parseInt($(this).data("factmin")) : min;
      var fMax = (parseInt($(this).data("factmax"))) ? parseInt($(this).data("factmax")) : max;

      var sliderEl = $(this);
      var minInput = $("input#Prop" + $(this).data("id") + "-min");
      var maxInput = $("input#Prop" + $(this).data("id") + "-max");
      var step = max / 10;

      // Создаем ползунок
      $(this).slider({
        min: min,
        max: max,
        values: [fMin, fMax],
        step: 1,
        range: true,
        stop: function(event, ui) {
          minInput.val(sliderEl.slider("values", 0));
          maxInput.val(sliderEl.slider("values", 1));
          getFilteredItems(maxInput);
        },
        slide: function(event, ui) {
          minInput.val(sliderEl.slider("values", 0));
          maxInput.val(sliderEl.slider("values", 1));
        }
      });

      // Создаем крючок для ввода из полей
      minInput.change(function() {
        var value1 = minInput.val();
        var value2 = maxInput.val();

        // Если значение ускакало за пределы
        if (parseInt(value1) > parseInt(value2)) {
          value1 = value2;
          minInput.val(value1);
        }
        sliderEl.slider("values", 0, value1);
        getFilteredItems(maxInput);
      });

      maxInput.change(function() {
        var value1 = minInput.val();
        var value2 = maxInput.val();

        if (parseInt(value1) > parseInt(value2)) {
          value2 = value1;
          maxInput.val(value2);
        }
        sliderEl.slider("values", 1, value2);
        getFilteredItems(maxInput);
      });
    });

  }

  mgInitFilter();

  $('body').on('click', '.mg-filter-item .mg-viewfilter', function() {
    $(this).parents('ul').find('li').fadeIn();
    $(this).hide();
  });

  $('body').on('click', '.mg-viewfilter-all', function() {
    $(this).hide();
    $('.mg-filter-item').fadeIn();
    VIEW_ALL_FILTER = -1 * VIEW_ALL_FILTER;
  });



  $('body').on('click', '.mg-filter-item input[type=checkbox]', function() {

    getFilteredItems($(this));
  });

  $('body').on('change', '.mg-filter-item select', function() {
    getFilteredItems($(this));
  });


  $('body').on('change', '.filter-form #maxCost', function() {
    getFilteredItems($(this));
  });

  $('body').on('change', '.filter-form #minCost', function() {
    getFilteredItems($(this));
  });

  $('body').on('change', '.filter-form select[name=sorter]', function() {
    $('.filter-form').submit();
  });

  /**
   * 
   * @param {type} object - объект который инициировал новый поиск, нужен для расчета офсета
   * @param {type} page - страница
   * @returns {undefined}
   */
  function getFilteredItems(object, page, sort) {
    var uri = $('form.filter-form').attr('action');
    
    var printToLeft = true; // установить в false если нужно выводить внутри блока

    var offset = object.offset();

    var leftMargin = $('.mg-filter-head').css('width').slice(0, -2);
    var blockLeft = $('.mg-filter-head').offset().left;
    leftMargin = blockLeft + leftMargin * 1;

    if (!printToLeft)
      leftMargin = leftMargin - $('.mg-filter-head').css('width').slice(0, -2);

    $('.mg-filter-head .filter-preview').css('left', leftMargin + 'px');
    // 
    $('.mg-filter-head .filter-preview span').hide();
    $('.mg-filter-head .filter-preview .loader-search').fadeIn();
    $('.mg-filter-head .filter-preview').show();
    $('.mg-filter-head .filter-preview').css('top', offset.top + 'px');
    $('.mg-filter-head .filter-preview .loader-search').fadeOut();
    $('.mg-filter-head .filter-preview span').html(locale.productSearch).fadeIn();
    // 
    var packedData = $('.filter-form').serialize();
    var autoUpdate = $('.filter-form').data('print-res');
    if (!autoUpdate) {  
      history.replaceState(packedData, "", uri+'?'+packedData);      
      $.ajax({
        type: "GET",
        url: uri,
        data: packedData + '&filter=1',
        dataType: 'html',
        success: function(response) {
          // $('.mg-filter-head .filter-preview span').hide();
          // $('.mg-filter-head .filter-preview .loader-search').fadeIn();
          // $('.mg-filter-head .filter-preview').show();
          // $('.mg-filter-head .filter-preview').css('top', offset.top + 'px');

          // $('.mg-filter-head .filter-preview').fadeOut();
          var productContainer = $(response).find('.products-wrapper').html();
          $('.products-wrapper').fadeOut();
          if ($(response).find('.product-wrapper').length == 0) {
            $('.products-wrapper').html('<div class="mg-filter-empty"><span>'+locale.filterNone+'</span></div>').fadeIn();
          } else {
            $('.products-wrapper').html(productContainer).fadeIn();
          }

          var filterForm = $(response).find('.filter-form').html();
          $('.filter-form').fadeOut();
          $('.filter-form').html(filterForm).fadeIn();
          mgInitFilter();
          if (VIEW_ALL_FILTER == 1) {
            $('.mg-viewfilter-all').hide();
            $('.mg-filter-item').fadeIn();
          }

        },
        complete: function() {
          // выполнение стека отложенных функций после AJAX вызова       
          if (AJAX_CALLBACK_FILTER) {
            //debugger;
            AJAX_CALLBACK_FILTER.forEach(function(element, index, arr) {
              eval(element.callback).apply(this, element.param);
            });

          }

          $('.variants-table').each(function() {
            $(this).find('[type=radio]:eq(0)').click().trigger('change');
          });

          $('.color-block .color.active').click();
        }
      });
    } else {
      $.ajax({
        type: "GET",
        url: uri,
        data: packedData + '&filter=1&getcount=1',
        dataType: 'json',
        success: function(response) {
          state = $('.mg-viewfilter-all').is(':visible');
          if(response.htmlProp != 'false') {
            if($('.filterTmpDiv').html() == undefined) {
              $('body').append('<div class="filterTmpDiv" style="display:none;"></div>');
            }
            $('.filterTmpDiv').html(response.htmlProp);
            $('form[name=filter] .mg-filter:last').html($('.filterTmpDiv .mg-filter').html());
            mgInitFilter();
          }
          
          if(!state) $('.mg-viewfilter-all').click();

          // $('.mg-filter-head .filter-preview span').hide();
          // $('.mg-filter-head .filter-preview .loader-search').fadeIn();
          // $('.mg-filter-head .filter-preview').show();
          // $('.mg-filter-head .filter-preview').css('top', offset.top + 'px');
          var html = response.lang.product+': ' + response.count + ' '+response.lang.unit+' <a href="' + uri + '?' + packedData + '&filter=1">'+response.lang.show+'</a>';
          // $('.mg-filter-head .filter-preview .loader-search').fadeOut();
          $('.mg-filter-head .filter-preview span').html(html).fadeIn();
        }
      });
    }
  }
  
  $("body").on('click', 'a.removeFilter', function(){    
    onRemoveAplyFilterItem($(this));
  });
  
  function onRemoveAplyFilterItem(object){
    var parent = object.parents("li.apply-filter-item-value");
    if(!parent.html()){      
      parent = object.parents("li.apply-filter-item");
    }
    
    parent.remove();    
    
    var packedData = $('form.apply-filter-form').serialize();
    
    if (ajaxUpdate) {            
      var pathName = $('form.apply-filter-form').attr('action');
      
      if (location.origin) {
        var uri = location.origin + pathName;
      } else {
        var uri = location.protocol + '//' + location.hostname + pathName;
      }
      
      history.replaceState(packedData, "", uri+'?'+packedData);      
      $.ajax({
        type: "GET",
        url: uri,
        data: packedData + '&filter=1',
        dataType: 'html',
        success: function(response) {
          $('.mg-filter-head .filter-preview').fadeOut();
          var productContainer = $(response).find('.products-wrapper').html();
          $('.products-wrapper').fadeOut();
          if ($(response).find('.product-wrapper').length == 0) {
            $('.products-wrapper').html('<div class="mg-filter-empty"><span>'+locale.filterNone+'</span></div>').fadeIn();
          } else {
            $('.products-wrapper').html(productContainer).fadeIn();
          }

          var filterForm = $(response).find('.filter-form').html();
          $('.filter-form').fadeOut();
          $('.filter-form').html(filterForm).fadeIn();
          mgInitFilter();          
        },
        complete: function() {
          // выполнение стека отложенных функций после AJAX вызова       
          if (AJAX_CALLBACK_FILTER) {
            //debugger;
            AJAX_CALLBACK_FILTER.forEach(function(element, index, arr) {
              eval(element.callback).apply(this, element.param);
            });

          }
        }
      });
    } else {
      $('form.apply-filter-form').submit();
    }    
  }

  // клик вне блока с количеством найденных товаров
  $(document).mousedown(function(e) {
    var container = $('.mg-filter-head .filter-preview');
    if (container.has(e.target).length === 0) {
      container.hide();
    }
  });


  $(".price-slider-list input[type=text]").change(function() {
    if (isNaN(parseFloat($(this).val()))) {
      $(this).val('1');
    }
  });


}); 