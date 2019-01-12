/**
 * Модуль для  раздела "Заказы".
 */
$(".ui-autocomplete").css('z-index', '1000');
$.datepicker.regional['ru'] = {
  closeText: lang.CLOSE,
  prevText: lang.PREV,
  nextText: lang.NEXT,
  currentText: lang.TODAY,
  monthNames: [lang.MONTH_1 , lang.MONTH_2 , lang.MONTH_3 , lang.MONTH_4 , lang.MONTH_5 , lang.MONTH_6 , lang.MONTH_7 , lang.MONTH_8 , lang.MONTH_9 , lang.MONTH_10 , lang.MONTH_11 , lang.MONTH_12],
  monthNamesShort: [lang.MONTH_SHORT_1 , lang.MONTH_SHORT_2 , lang.MONTH_SHORT_3 , lang.MONTH_SHORT_4 , lang.MONTH_SHORT_5 , lang.MONTH_SHORT_6 , lang.MONTH_SHORT_7 , lang.MONTH_SHORT_8 , lang.MONTH_SHORT_9 , lang.MONTH_SHORT_10 , lang.MONTH_SHORT_11 , lang.MONTH_SHORT_12],
  dayNames: [lang.DAY_1 , lang.DAY_2 , lang.DAY_3 , lang.DAY_4 , lang.DAY_5 , lang.DAY_6 , lang.DAY_7],
  dayNamesShort: [lang.DAY_SHORT_1 , lang.DAY_SHORT_2 , lang.DAY_SHORT_3 , lang.DAY_SHORT_4 , lang.DAY_SHORT_5 , lang.DAY_SHORT_6 , lang.DAY_SHORT_7],
  dayNamesMin: [lang.DAY_MIN_1 , lang.DAY_MIN_2 , lang.DAY_MIN_3 , lang.DAY_MIN_4 , lang.DAY_MIN_5 , lang.DAY_MIN_6 , lang.DAY_MIN_7],
  dateFormat: 'dd.mm.yy',
  firstDay: 1,
  isRTL: false
};
$.datepicker.setDefaults($.datepicker.regional['ru']);

var order = (function () {
  return {
    comment: null,
    firstCall: true,
    deliveryCost: 0,
    searchUnit: 'шт.',
    orderItems: [],
    currencyShort: [],
    initialStatus: -1,
    /**
     * Инициализирует обработчики для кнопок и элементов раздела.
     */
    init: function () {

      admin.ajaxRequest({
        mguniqueurl: "action/getCurrencyShort"
      },
      function (response) {
        order.currencyShort = response.data;
      });

      $('.admin-center').on('click', '.changeSortDateTipe', function () {
        if($('[name=dateSortType]').val() == 'create') {
          $('[name=dateSortType]').val('pay');
          $(this).text(lang.PAY);
        } else {
          $('[name=dateSortType]').val('create');
          $(this).text(lang.CREATE);
        }
      });

      // ================================
      //            для PDF
      // ================================
      var showPR = false;
      var showPD = false;
      // для наведения на кнопку
      $(document).on({
          mouseenter: function () {
            showPD = true;
            $('.pdf-docs-list li a').data('id', $(this).parents('tr').attr('order_id'));
            $('.pdf-docs-list').show().css('opacity', 1).css('visibility', 'visible');
            offset = $(this).offset();
            if($(window).height() - offset.top - $(this).outerHeight() - $('.pdf-docs-list').outerHeight() <= 0) {
              $('.pdf-docs-list').offset({top:offset.top - $('.pdf-docs-list').outerHeight() + 5, left:offset.left - $('.pdf-docs-list').outerWidth() + ($(this).outerWidth() * 1.3)});
            } else {
              $('.pdf-docs-list').offset({top:offset.top + $(this).outerHeight(), left:offset.left - $('.pdf-docs-list').outerWidth() + ($(this).outerWidth() * 1.3)});
            }
            showPR = false;
            $('.print-docs-list').hide().css('opacity', 0).css('visibility', 'hidden');
          },
          mouseleave: function () {
            setTimeout(function() {
              if(!showPD) {
                $('.pdf-docs-list').hide().css('opacity', 0).css('visibility', 'hidden');
              }
            }, 1000);
            showPD = false;
          }
      }, ".order-to-pdf");
      // для наведения на меню
      $(document).on({
          mouseenter: function () {
            if($(this).hasClass('print-docs-list')) showPR = true;
            if($(this).hasClass('pdf-docs-list')) showPD = true;
            $(this).show().css('opacity', 1).css('visibility', 'visible');
          },
          mouseleave: function () {
            setTimeout(function() {
              if(!showPR) {
                $('.print-docs-list').hide().css('opacity', 0).css('visibility', 'hidden');
              }
              if(!showPD) {
                $('.pdf-docs-list').hide().css('opacity', 0).css('visibility', 'hidden');
              }
            }, 1000);
            showPR = false;
            showPD = false;
          }
      }, ".pdf-docs-list, .print-docs-list");
      // ================================
      //            для печати
      // ================================
      // для наведения на кнопку
      $(document).on({
          mouseenter: function () {
            showPR = true;
            $('.print-docs-list li a').data('id', $(this).parents('tr').attr('order_id'));
            $('.print-docs-list').show().css('opacity', 1).css('visibility', 'visible');
            offset = $(this).offset();
            if($(window).height() - offset.top - $(this).outerHeight() - $('.pdf-docs-list').outerHeight() <= 0) {
              $('.print-docs-list').offset({top:offset.top - $('.print-docs-list').outerHeight() + 5, left:offset.left - $('.print-docs-list').outerWidth() + ($(this).outerWidth() * 1.3)});
            } else {
              $('.print-docs-list').offset({top:offset.top + $(this).outerHeight(), left:offset.left - $('.print-docs-list').outerWidth() + ($(this).outerWidth() * 1.3)});
            }
            showPD = false;
            $('.pdf-docs-list').hide().css('opacity', 0).css('visibility', 'hidden');
          },
          mouseleave: function () {
            setTimeout(function() {
              if(!showPR) {
                $('.pdf-docs-list').hide().css('opacity', 0).css('visibility', 'hidden');
                $('.print-docs-list').hide().css('opacity', 0).css('visibility', 'hidden');
              }
            }, 1000);
            showPR = false;
          }
      }, ".order-to-print");
      // ================================
      //              КОНЕЦ
      // ================================
      
      // убирает подсказку для поиска товаров
      $('.admin-center').on('change', '.add-order .search-field', function () {
        $('.example-line').hide();
      });

      $('.admin-center').on('click', '.section-order .addProductToOrder', function() {  
        $('.top-block').slideToggle('fast');
      });

      $('.admin-center').on('change', '.section-order #add-order-wrapper #orderStatus', function() {
        if (order.initialStatus > -1 && order.initialStatus != $('#orderStatus').val()) {
          $('.section-order #add-order-wrapper .mailUser').show().find('input').trigger('change');
        }
        else{
          $('.section-order #add-order-wrapper .mailUser').hide();
          $('.section-order #add-order-wrapper .mailUserText').hide();
        }
      });

      $('.admin-center').on('change', '.section-order #add-order-wrapper #mailUser', function() {
        if (this.checked) {
          $('.section-order #add-order-wrapper .mailUserText').show();
        }
        else{
          $('.section-order #add-order-wrapper .mailUserText').hide();
        }
      });

      //вызов модального окна для редактирования полей таблицы
      $('.admin-center').on('click', '.section-order .order-col-config', function() {
        admin.openModal('#order-col-display-modal');
      });

      //раскрытие комментария
      $('.admin-center').on('click', '.section-order .order-tbody .showMoar', function() {
        $(this).parents('td').html($(this).attr('content'));
      });

      $('.admin-center').on('change', '#add-order-wrapper #user_email_needed', function() {
        if(this.checked) {
          $('#add-order-wrapper input[name=user_email]').prop("disabled", true);
        }
        else{
          $('#add-order-wrapper input[name=user_email]').prop("disabled", false);
        }
      });

      //сохранение модального окна для редактирования полей таблицы
      $('.admin-center').on('click', '.section-order #order-col-display-modal .save', function() {
        var additionalFields = [];
        $('.section-order #order-col-display-modal .additionalColumns input[type=checkbox]').each(function(index,element) {

        	if ($(this).prop('checked') == true) {
        		additionalFields.push($(this).attr('fieldname'));//.push({'name':customTagName, 'type':customTagType});
        	}
        });

        admin.ajaxRequest({
          mguniqueurl:"action/saveColsOrder",
          additional: additionalFields,
          orderId: $("#order-col-display-modal #order-id").prop('checked'),
          orderDate: $("#order-col-display-modal #order-date").prop('checked'),
          orderFio: $("#order-col-display-modal #order-fio").prop('checked'),
          orderEmail: $("#order-col-display-modal #order-email").prop('checked'),
          orderPhone: $("#order-col-display-modal #order-phone").prop('checked'),
          orderYur: $("#order-col-display-modal #order-yur").prop('checked'),
          orderSumm: $("#order-col-display-modal #order-summ").prop('checked'),
          orderDeliv: $("#order-col-display-modal #order-deliv").prop('checked'),
          orderDelivDate: $("#order-col-display-modal #order-delivDate").prop('checked'),
          orderDelivAddress: $("#order-col-display-modal #order-address").prop('checked'),
          orderPayment: $("#order-col-display-modal #order-payment").prop('checked'),
          orderStatus: $("#order-col-display-modal #order-status").prop('checked'),
          commUzer: $("#order-col-display-modal #comm-uzer").prop('checked'),
          commManager: $("#order-col-display-modal #comm-manager").prop('checked'),
          owner: $("#order-col-display-modal #owner-order").prop('checked'),
        },

        function(response) {
          admin.indication(response.status, lang.SUCCESS_SAVE);
          admin.closeModal('#order-col-display-modal');
          admin.refreshPanel();
        });
      });

      // Выделить все заказы
      $('.admin-center').on('click', '.section-order .check-all-order', function () {
        $('.order-tbody input[name=order-check]').prop('checked', 'checked');
        $('.order-tbody input[name=order-check]').val('true');
        $('.order-tbody tr').addClass('selected');

        $(this).addClass('uncheck-all-order');
        $(this).removeClass('check-all-order');
      });
      // Снять выделение со всех заказы.
      $('.admin-center').on('click', '.section-order .uncheck-all-order', function () {
        $('.order-tbody input[name=order-check]').prop('checked', false);
        $('.order-tbody input[name=order-check]').val('false');
        $('.order-tbody tr').removeClass('selected');
        
        $(this).addClass('check-all-order');
        $(this).removeClass('uncheck-all-order');
      });

      // клик на мегафон (уведомление пользователя о смене заказа)
      $('.admin-center').on('click', '.section-order .fa-bullhorn', function () {
        $(this).toggleClass('active');
      });

      // Вызов модального окна при нажатии на кнопку добавления заказа.
      $('.admin-center').on('click', '.section-order .add-new-button', function () {
        order.openModalWindow('add');
      });

      // Вызов модального окна при нажатии на кнопку изменения товаров.
      $('.admin-center').on('click', '.section-order .see-order', function () {
        order.openModalWindow('edit',$(this).attr('id'), $(this).attr('data-number'));
      });

      // Клонирование заказа
      $('.admin-center').on('click', '.section-order .clone-row', function () {
        order.cloneOrder($(this).attr('id'));
      });

      // Удаление товара.
      $('.admin-center').on('click', '.section-order .delete-order', function () {
        order.deleteOrder($(this).attr('id'));
      });

      // Показывает панель с фильтрами.
      $('.admin-center').on('click', '.section-order .show-filters', function () {
        $('.filter-container').slideToggle(function () {
          $('.property-order-container').slideUp();
          $('.widget-table-action').toggleClass('no-radius');
        });
      });

      // Показывает панель с фильтрами.
      $('.admin-center').on('click', '.section-order .show-property-order', function () {
        $('.property-order-container').slideToggle(function () {
          $('.filter-container').slideUp();
          $('.widget-table-action').toggleClass('no-radius');
        });
      });


      // Сброс фильтров.
      $('.admin-center').on('click', '.section-order .refreshFilter', function () {
        admin.clearGetParam();
        admin.show("orders.php", "adminpage", "refreshFilter=1", admin.sliderPrice);
        return false;
      });

      // Применение выбранных фильтров
      $('.admin-center').on('click', '.section-order .filter-now', function () {
        order.getProductByFilter();
        return false;
      });

      $('.admin-center').on('click', '#add-order-wrapper .product-block .variants-table [type=radio]', function () {
        var img = $(this).parents('tr').find('img');
        if (img != undefined) {
          var src = img.attr('src');
          if (src != undefined && src.length) {
            $('#add-order-wrapper .product-block .image-sp img').attr('src', src);
          }
        }
      });

      // Открывает панель настроек заказа
      $('.admin-center').on('click', '.section-order .property-order-container .save-property-order', function () {
        order.savePropertyOrder();
        return false;
      });

      // Выбор картинки
      $('.admin-center').on('click', '.section-order .property-order-container .upload-sign', function () {
        admin.openUploader('order.getSignFile');

      });

      // Выбор картинки
      $('.admin-center').on('click', '.section-order .property-order-container .upload-stamp', function () {
        admin.openUploader('order.getStampFile');

      });

      // Сохранение  при нажатии на кнопку сохранить в модальном окне.
      $('body').on('click', '#add-order-wrapper .save-button', function () {
        order.saveOrder($(this).attr('id'), $(this).parents('.orders-table-wrapper'), $(this).attr('data-number'));
      });

      // Распечатка заказа  
      $('body').on('click', '#add-order-wrapper .print-button, .print-docs-list a', function () {
        var layout = '';
        
        if($(this).data('template')) {
          layout = $(this).data('template');
        }                
        
        order.printOrder($(this).data('id'), layout);
      });

      // Сохранить в PDF   
      $('body').on('click', '#add-order-wrapper .get-pdf-button, .pdf-docs-list a', function () {
        var layout = '';
        
        if($(this).data('template')) {
          layout = '&layout=' + $(this).data('template');
        }
        
        window.location.href = mgBaseDir + '/mg-admin?getOrderPdf=' + $(this).data('id') + layout;
      });

      // Получить выгрузку счета в CSV   
      // $('body').on('click', '#add-order-wrapper .csv-button, .order-to-csv a', function () {
        
      //   window.location.href = mgBaseDir + '/mg-admin?getExportCSV=' + $(this).data('id');
      // });

      // Разблокировать поля для редактирования заказа.
      $('body').on('click', '#add-order-wrapper .editor-order', function () {
        order.enableEditor();

        var id = $("#add-order-wrapper .save-button").attr('id');
        var deliveryId = $("#delivery :selected").attr('name');
        var plugin = $("#delivery :selected").data('plugin');

        if(plugin && plugin.length > 0) {  
          $.ajax({
            type: "POST",
            url: mgBaseDir+"/ajaxrequest",
            data: {
              pluginHandler: plugin, // имя папки в которой лежит данный плагин
              actionerClass: 'Pactioner', // класс Pactioner в Pactioner.php - в папке плагина
              action: "getAdminDeliveryForm", // название действия в пользовательском  классе 
              deliveryId: deliveryId,
              firstCall: order.firstCall,
              orderItems: order.orderItems,
              orderId: id
            },
            cache: false,
            dataType: 'json',
            success: function(response) { 
              order.firstCall = false;
              $('#delivery').parents('span').append('<span class="add-delivery-info">'+response.data.form+'</span>');
              $('input#deliveryCost').prop("disabled",true);
            }            
          });
        }
      });

      $('body').find('#add-order-wrapper .delivery-date input[name=date_delivery]').datepicker({dateFormat: "dd.mm.yy", minDate: 0});
      $('body').on('mousedown', '#add-order-wrapper .delivery-date input[name=date_delivery]', function () {
        $(this).datepicker({dateFormat: "dd.mm.yy", minDate: 0});
      });

      // Удаляет выбранный продукт из поля для добавления в заказ.
      $('body').on('click', '#add-order-wrapper .clear-product', function () {
        $('[name=searchcat]').val('');
        $(".product-block").html('');
        $('.example-line').show();
      });

      // Применить купон в редактировании заказа.
      $('body').on('change', '#add-order-wrapper select[name=promocode]', function () {
        order.calculateOrder();
      });
      // Применить скидку в редактировании заказа.
      $('body').on('change', '#add-order-wrapper .discount-system input', function () {
        if ($(this).is( ":checked")) {
          $(this).val('true');
        } else {
          $(this).val('false');
        }
        order.calculateOrder();            
      });
      // при изменении email покупателя - пересчет накопительной скидки
      $('body').on('blur', '#order-data input[name=user_email]', function () {
        if ($('.order-payment-sum .discount-system input[name=cumulative]').val()=='true') {
          order.calculateOrder();          
        }        
      });

      // Подстановка значения стоимости при выборе способа доставки в добавлении заказа.
      $('body').on('change', '#delivery', function () {
        $('#delivery').parent().find('.errorField').css('display', 'none');
        $('#delivery').removeClass('error-input');
        
        if(!$("#delivery :selected").data('plugin')) {
          var deliveryCost = $('#delivery option:selected').val();
          var deliveryId = $('#delivery option:selected').attr('name');
          order.getDeliveryOrderOptions(deliveryId, true);                                                           
        }else{          
          order.calculateOrder();
        }               
      });
      
      //
      $(window).on("delivery:change", function() {
        $('#deliveryCost').val(order.deliveryCost);          
        order.calculateOrder();
      });
      
       // Изменнение стоимости доставки
      $('body').on('change', '#deliveryCost', function () {
        if ($(this).val()< 0 || !$.isNumeric($(this).val())) {         
          $(this).val('0');
        }
        order.calculateOrder();
      });

      // Смена плательщика.
      $('body').on('change', '#customer', function () {
        $(this).val() == 'fiz' ? $('.yur-list-editor').hide() : $('.yur-list-editor').show();
      });

      // Действия при выборе способа оплаты.
      $('body').on('change', 'select#payment', function () {
        $('.main-settings-list select#payment').parent().find('.errorField').css('display', 'none');
        $('.main-settings-list select#payment').removeClass('error-input');
        order.calculateOrder();        
      });

      // Устанавливает количиство выводимых записей в этом разделе.
      $('.admin-center').on('change', '.section-order .countPrintRowsOrder', function () {
        var count = $(this).val();
        admin.ajaxRequest({
          mguniqueurl: "action/setCountPrintRowsOrder",
          count: count
        },
        function (response) {
          admin.refreshPanel();
        }
        );
      });

      // Поиск товара при создании нового заказа.
      // Обработка ввода поисковой фразы в поле поиска.
      $('.admin-center').on('keyup', '#order-data input[name=searchcat]', function () {
        admin.searchProduct($(this).val(), '#order-data .fastResult', $('#add-order-wrapper [name=searchCats]').val(), 'yep');
      });

      $('.admin-center').on('change', '#add-order-wrapper [name=searchCats]', function () {
        admin.searchProduct($('#order-data input[name=searchcat]').val(), '#order-data .fastResult', $('#add-order-wrapper [name=searchCats]').val(), 'yep');
      });

      // Подстановка товара из примера в строку поиска.
      $('.admin-center').on('click', '#order-data .example-find', function () {
        $('#order-data input[name=searchcat]').val($(this).text());
        admin.searchProduct($(this).text(), '#order-data .fastResult', -1, 'yep');
      });

      // Клик вне поиска.
      $(document).mousedown(function (e) {
        var container = $(".fastResult");
        if (container.has(e.target).length === 0 &&
          $(".search-block").has(e.target).length === 0) {
          container.hide();
        }
      });

      // Пересчет цены товара аяксом в форме добавления заказа.
      $('.admin-center').on('change', '.orders-table-wrapper .property-form input, .orders-table-wrapper .property-form select',
        function () {
          if ($(this).parents('p').find('input[type=radio]').length) {
            $(this).parents('p').find('input[type=radio]').prop('checked', false);
            $(this).prop('checked', true);
          }
          order.refreshPriceProduct();
          return false;
        });

      $('.admin-center').on('change', '#add-order-wrapper .currSpan [name=userCurrency]', function () {
        $.ajax({
          type: "POST",
          url: mgBaseDir + "/ajax",
          data: {
            mguniqueurl: "action/setAdminCurrency",
            userCustomCurrency: $('#add-order-wrapper .currSpan [name=userCurrency]').val()
          },
          cache: false,
          // async: false,
          dataType: "json",
          success: function (response) {
            $('#add-order-wrapper .changeCurrency').text(response.data.curr);

            $('#add-order-wrapper .price-val').each(function(index,element) {
              var current = $(this).val();
              current = (current*response.data.multiplier).toFixed(2);
              $(this).val(current);
            });
            var current = $('#add-order-wrapper #deliveryCost').val();
            current = (current*response.data.multiplier).toFixed(2);
            $('#add-order-wrapper #deliveryCost').val(current);
            $('#add-order-wrapper .price-val:first').keyup();
            $('#add-order-wrapper .clear-product').click();
            $('#add-order-wrapper #orderContent .property .prop-val').each(function(index,element) {
              var current = $(this).text();
              current = current.replace(",", ".");
              current = current.replace(/[^0-9.]/g, '');
              while (current[current.length -1] == '.') {
                  current = current.slice(0,-1);
              }
              current = (current*response.data.multiplier).toFixed(2);
              $(this).text(' + '+current+' '+response.data.curr);
            });
          }
        });
      });

      // Клик по найденным товарам поиске в форме добавления заказа
      $('.admin-center').on('click', '.section-order .fast-result-list a', function () {
        order.viewProduct($(this).data('element-index'));
      });

      // Вставка продукта из списка поиска в строку заказа.
      $('.admin-center').on('click', '.orders-table-wrapper .property-form .addToCart', function () {
        order.addToOrder($(this));
        return false;
      });

      // Удаление позиции из заказа.
      $('body').on('click', '.order-history a[rel=delItem]', function () {
        var itemLine = $(this).parents('tr');
        var itemId = itemLine.attr('data-id');
        order.orderItems.forEach(function(item, i) {                      
          if(item.id == itemId) {                                     
            order.orderItems.splice(i, 1);              
          }            
        });
        itemLine.remove();
        order.calculateOrder();
      });

      // Обработка выбора  способа доставки при добавлении нового заказа.
      $('body').on('change', 'select #delivery', function () {
        $('select #delivery option[name=null]').remove();
      });

      // Обработка выбора  способа оплаты при добавлении нового заказа.
      $('body').on('change', 'select#payment', function () {
        $('select#payment option[name=null]').remove();
      });

      // Перерасчет стоимости при смене количества товара.
      $('body').on('keyup', '#orderContent input', function () {
        var error = false;
        $(this).removeClass('error-input');
        $(this).val($(this).val().replace(new RegExp(',','g'),'.'));
        if ((parseInt($(this).val()) !== 0 || $(this).val().length > 1) && (1 > $(this).val() || !$.isNumeric($(this).val()))) {
          $(this).addClass('error-input');
          error = true;
          admin.indication('error', lang.ERROR_FORMAT_COUNT);
        }
        if ($(this).hasClass('count') && ($(this).data('max') >= 0)) {
          var max = parseInt($(this).data('max')) + parseInt($(this).attr('count-old'));
          if ($(this).val() > max) {
             $(this).val(max);
          }          
        }
        
        if($(this).hasClass('count')) {
          var itemId = $(this).parents('tr').attr('data-id');
          var count = $(this);
          order.orderItems.forEach(function(item, i) {                      
            if(item.id == itemId) {              
              item.count = count.val();              
              order.orderItems[i] = item;              
            }            
          });          
        }                
        if (!error) {
          order.calculateOrder();
        }
        
      });
      
      $('body').on('focus', '#orderContent input.count', function () {
        if (!$(this).attr('count-old')) {
          $(this).attr('count-old', $(this).val());
        }
      });

      // Обработка ввода адреса доставки 
      $('body').on('keyup', '#order-data input[name=address]', function () {
        $('.map-btn').attr('href', 'http://maps.yandex.ru/?text=' + encodeURIComponent($(this).val()));
      });

      // Выделить все заказы.
      $('.admin-center').on('click', '.section-order .checkbox-cell input[name=order-check]', function () {
        if ($(this).val() != 'true') {
          $('.order-tbody input[name=order-check]').prop('checked', 'checked');
          $('.order-tbody input[name=order-check]').val('true');
        } else {
          $('.order-tbody input[name=order-check]').prop('checked', false);
          $('.order-tbody input[name=order-check]').val('false');
        }
      });

      $('.admin-center').on('click', '#order-data .template-tabs-menu li', function () {
        $(this).parents('.template-tabs-menu').find('li').removeClass('is-active');
        $(this).addClass('is-active');
        if ($(this).attr('part') == 'descr') {
          $('#order-data .descrip').show();
          $('#order-data .propsFrom').hide();
        } 
        if ($(this).attr('part') == 'props') {
          $('#order-data .descrip').hide();
          $('#order-data .propsFrom').show();
        }
      });

      // Выполнение выбранной операции с заказами
      $('.admin-center').on('click', '.section-order .run-operation', function () {
        if ($('.order-operation').val() == 'fulldelete') {
          admin.openModal('#order-remove-modal');
        }
        else{
          var operation = $('.order-operation').val();
          if (operation == 'massPdf' || operation == 'massPdfSingle' || operation == 'massPrint') {
            order.runOperation(operation, true);
          } else {
            order.runOperation(operation);
          }
        }
      });

      //Проверка для массового удаления
      $('.admin-center').on('click', '#order-remove-modal .confirmDrop', function () {
        if ($('#order-remove-modal input').val() === $('#order-remove-modal input').attr('tpl')) {
          $('#order-remove-modal input').removeClass('error-input');
          admin.closeModal('#order-remove-modal');
          order.runOperation($('.order-operation').val(),true);
        }
        else{
          $('#order-remove-modal input').addClass('error-input');
        }
      });

      $('.admin-center').find('#delivery').attr('selected');

      
      $('.admin-center').on('click', '.section-order .addField', function () {
        order.printFieldsRow();
      });

      $('.admin-center').on('click', '.section-order .openPopup', function () {
        $('.field-variant-popup').hide();
        $(this).parents('.field-item').find('.field-variant-popup').show();
      });

      $('.admin-center').on('click', '.section-order .apply-popup', function () {
        $(this).parents('.field-variant-popup').hide();
      });

      $('.admin-center').on('click', '.section-order .fa-exclamation-triangle', function () {
        $(this).toggleClass('active');
      });

      $('.admin-center').on('click', '.section-order .fa-eye', function () {
        $(this).toggleClass('active');
      });

      $('.admin-center').on('click', '.section-order .fa-trash', function () {
        if(confirm('Удалить поле?')) {
          $(this).parents('.field-item').detach();
        }
      });

      $('.admin-center').on('change', '.section-order [name=type]', function () {
        $('.field-variant-popup').hide();
        order.showCog($(this).parents('.field-item'));
      });

      $('.admin-center').on('click', '.section-order .add-popup-field', function () {
        order.addPopupField($(this).parents('.field-item'));
      });

      $('.admin-center').on('click', '.section-order .field-variant .fa-times', function () {
        $(this).parent().detach();
      });

      $('.admin-center').on('click', '.section-order .save-optional-field', function () {
        order.saveOptionalFields();
        admin.refreshPanel();
      });

      $('.admin-center').on('change', '.section-order .order-operation', function () {
        $('.orderOperationParam').hide();
        var operation = $(this).val();
        if (operation == 'massPdfSingle') {operation = 'massPdf';}
        $(this).closest('.label-select').find('.orderOperationParam#'+operation).show();
      });

    },


    addPopupField: function(object, val) {
      val = typeof val !== 'undefined' ? val : '';
      object.find('.field-variant').append('<p class="field"><input type="text" value="'+val+'"><i class="fa fa-times"></p>');
    },
    

    loadFieldsRow: function(data) {
      if(data == null || !data || data == '') {
        $('.fields-list').html('<tr><td colspan="5" class="text-center toDel">'+lang.NO_ADDITIONAL_FIELDS+'</td></tr>');
      } else {
        $('.fields-list').html('');
        data.forEach(function(item, i, data) {
          order.printFieldsRow(item);
        }); 
      }
      $('.fields-list').sortable({
        opacity: 0.8,
        // axis: 'y',
        handle: '.fa-arrows',
        // items: "tr+tr"
      });
    },

    printFieldsRow: function(data) {
      $('.toDel').detach();
      if(data == undefined || typeof data === 'undefined') {
        data = {};
        data.name = "";
        data.type = "input";
        data.variants = undefined;
      }
      $('.toObja').removeClass('toObja');
      $('.fields-list').append('\
        <tr class="field-item toObja">\
          <td><i class="fa fa-arrows"></i></td>\
          <td><input type="text" name="name" value="'+data.name+'"></td>\
          <td>\
            <select style="margin: 0;" name="type">\
              <option value="input">input</option>\
              <option value="select">select</option>\
              <option value="checkbox">checkbox</option>\
              <option value="radiobutton">radiobutton</option>\
              <option value="textarea">textarea</option>\
            </select>\
          </td>\
          <td style="position: relative; width:40px;">\
            <button class="button secondary openPopup" style="margin: 0;">\
              <span class="fa fa-cog"></span>\
            </button>\
            <div class="custom-popup field-variant-popup" style="display:none;top:7px;">\
              <p>Варианты выбора</p>\
              <div class="field-variant"></div>\
              <div class="row">\
                <div class="large-12 columns">\
                  <a class="button primary add-popup-field" href="javascript:void(0);"><i class="fa fa-plus" aria-hidden="true"></i>'+lang.ADD+'</a>\
                  <a class="button success fl-right apply-popup" href="javascript:void(0);"><i class="fa fa-check" aria-hidden="true"></i> '+lang.APPLY+'</a>\
                </div>\
              </div>\
            </div>\
          </td>\
          <td class="text-right action-list">\
            <i class="fa fa-exclamation-triangle" title="'+lang.OP_REQUIRED_FIELD+'"></i>\
            <i class="fa fa-eye" title="'+lang.OP_SHOW+'"></i>\
            <i class="fa fa-trash" title="'+lang.DELETE+'"></i>\
          </td>\
        </tr>');
      object = $('.toObja');
      if(data.active == 1) {
        object.find('.action-list .fa-eye').addClass('active');
      } else {
        object.find('.action-list .fa-eye').removeClass('active');
      }
      if(data.required == 1) {
        object.find('.action-list .fa-exclamation-triangle').addClass('active');
      } else {
        object.find('.action-list .fa-exclamation-triangle').removeClass('active');
      }
      object.find('.field-variant').html('');
      if(data.variants != undefined) {
        data.variants.forEach(function(item) {
          order.addPopupField(object, item);
        });
      }
      object.find('[name=type] option[value='+data.type+']').prop('selected', 'selected');

      order.showCog(object);
    },

    showCog: function(object) {
      switch (object.find('[name=type]').val()) {
        case 'input':
        case 'textarea':
        case 'checkbox':
          object.find('.openPopup').hide();
          break;

        default:
          object.find('.openPopup').show();
      }
    },

    /**
     * Создает строку в таблице заказов
     * @param {type} position - параметры позиции
     * @param {type} type - тип формирования, для имеющегося состава или новой позиции
     * @returns {String}
     */
    createPositionRow: function (position, type) {
      if (position.currency_iso == null) {
        var currency = admin.CURRENCY;
      }
      else{
        var currency = position.currency_iso;
        currency = order.currencyShort[currency];
      }
      
      var row = '\
          <tr data-id=' + position.id + ' data-variant=' + (position.variant ? position.variant : 0) + '>\
          <td class="image"><img src="' + position.image_url + '" style="width:50px;"></td>\
          <td class="title" style="width:250px">' + position.title + '</td>\
          <td class="code" data-code="' + position.code + '">' + position.code + '</td>\
          <td class="weight" data-weight="' + position.weight + '">' + ((position.weight == "undefined" || !position.weight) ? 0 : position.weight) + '</td>\
          <td class="fullPrice">'+
           ((type == "view") ? '<span class="value order-edit-visible">' + admin.numberFormat(position.fulPrice) + '</span>' : 
                               '<span class="value" style="display:none;">' + admin.numberFormat(position.fulPrice) + '</span>')
          +'<input class="small price-val '+((type == "view") ? 'order-edit-display' : 'inline-block')+'" type="text" value="' + position.fulPrice + '"> <span class="changeCurrency">' + currency + '</span></td>\
          <td class="discount"><span>' + position.discount + '</span>%</td>\
          <td class="price">\
            <span class="value">' + admin.numberFormat(position.price)+ '</span>\
            <input class="small" style="display: none;" type="text" value="' + position.price + '">\
            <span class="changeCurrency"> '+ currency +'</span>\
          </td>\
          <td class="count">' +
        ((type == "view") ? '<span class="value order-edit-visible">' + position.count + '</span>' : '') +
        (position.notSet ? 
        '<input order_id="' + position.order_id + '"  type="text" data-max="' + position.maxCount + '" count-old =' + position.count + ' value="' + position.count + '" class="tiny count ' +
        ((type == "view") ? 'order-edit-display' : 'inline-block')
        + '"> ':
        '<input disabled order_id="' + position.order_id + '"  type="text" data-max="' + position.maxCount + '" count-old =' + position.count + ' value="' + position.count + '" class="tiny tool-tip-bottom count ' +
        ((type == "view") ? 'order-edit-display' : '')
        + '" title="'+lang.ERROR_NO_EDIT+'"> '
        ) + position.category_unit + '</td>\
          <td class="summ" data-summ="' + position.summ + '"><span class="value">' + admin.numberFormat(position.summ) + '</span> <div style="display:inline-block" class="changeCurrency">' + currency + '</div></td>\
          <td class="prod-remove"><span class="' + ((type == "view") ? 'order-edit-display' : '') + '"><a style="font-size:16px;padding-right:20px;" class="tool-tip-bottom dell-btn fa fa-trash txt-red ' +
        ((type == "view") ? 'order-edit-display' : '')
        + '" order_id="' + position.order_id + '" href="javascript:void(0);" rel="delItem"></a></span></td>\
        </tr>';
      return row;
    },
    /*
     * Получает все выбранные свойства товара перед добавлением в строку заказа  
     * @returns {String}
     */
    getPropPosition: function () {
      var prop = '';
      $('.property-form select, .property-form input[type=checkbox],.property-form input[type=radio]').each(function () {
        if ($(this).attr('name') != 'variant') {
          var val = "";
          var val = $(this).find('option:selected').text();

          if ($(this).val() == "true") {
            val = $(this).next("span").text();
          }

          if ($(this).prop('checked') === true) {
            val = $(this).next("span").text();
          }

          if (val) {
            // var propertyTitle = $(this).parents('p').find('.property-title').text() + ': ';
            var propertyTitle = $(this).parents('p').find('.property-title').text();

            var marg = admin.trim(val.replace(eval('/(.*)([-+]\\s[0-9]+' + $('#order-data .currency-sp').text() + ')/gi'), '$2'));
            var val = admin.trim(val.replace(eval('/(.*)([-+]\\s[0-9]+' + $('#order-data .currency-sp').text() + ')/gi'), '$1'));
            if (marg == val) {
              marg = '';
            }
            
            var wrap = '<div class="prop-position"> <span class="prop-name">' + propertyTitle + ': ' + val + '</span> <span class="prop-val"> ' + marg + '</span></div>';
            prop += wrap;
          }

        }
      });
      
      return prop;
    },

    /**
     * Открывает модальное окно.
     * type - тип окна, либо для создания нового товара, либо для редактирования старого.
     */
    openModalWindow: function (type, id, number) {
      $('.product-block').html('');
      switch (type) {
        case 'add': {
          $(".save-button").attr('id', '');
          $('.add-order-table-icon').text(lang.TITLE_NEW_ORDER);
          order.newOrder();
          break;
        }
        case 'edit': {
          $('.add-order-table-icon').text(lang.TITLE_ORDER_VIEW + ' №' + number + ' от ' + $('tr[order_id=' + id + '] .add_date').text());
          order.editOrder(id);
          break;
        }
      }

      // Вызов модального окна.
      admin.openModal('#add-order-wrapper');
      admin.initToolTip();
    },

    //скачивание пачки pdf файлов
    downloadMultipleOrderPdfs: function(i, template, orders_id) {
        if (i >= orders_id.length) {
          return;
        }
        var a = document.createElement('a');

        a.href = mgBaseDir + '/mg-admin?getOrderPdf='+orders_id[i]+'&layout='+template;
        a.target = '_parent';
        // Use a.download if available, it prevents plugins from opening.
        if ('download' in a) {
          a.download = 'order_'+orders_id[i];
        }
        // Add a to the doc for click to work.
        (document.body || document.documentElement).appendChild(a);
        if (a.click) {
          a.click(); // The click method is supported by most browsers.
        } else {
          $(a).click(); // Backup using jquery
        }
        // Delete the temporary link.
        a.parentNode.removeChild(a);
        // Download the next file with a small timeout. The timeout is necessary
        // for IE, which will otherwise only download the first file.
        setTimeout(function() {
          order.downloadMultipleOrderPdfs(i + 1, template, orders_id);
        }, 500);
      },

    /**
     * Выполняет выбранную операцию со всеми отмеченными заказами
     * operation - тип операции.
     */
    runOperation: function (operation, skipConfirm) {
      if(typeof skipConfirm === "undefined" || skipConfirm === null) { skipConfirm = false; }
      

      var param;
      if($('.orderOperationParam:visible').length) {
        param = $('.orderOperationParam:visible').val();
      }

      var orders_id = [];
      $('.order-tbody tr').each(function () {
        if ($(this).find('input[name=order-check]').prop('checked')) {
          orders_id.push($(this).attr('order_id'));
        }
      });

      if (!orders_id.length) {
        alert('Заказы не выбраны.');
        return false;
      }

      if (operation == 'massPdf') {
        order.downloadMultipleOrderPdfs(0, param, orders_id);
        return false;
      }

      if (operation == 'massPdfSingle') {
        window.location.href = mgBaseDir + '/mg-admin?getMassPdfOrders='+JSON.stringify(orders_id)+'&layout='+param;
        return false;
      }

      if (skipConfirm || confirm(lang.RUN_CONFIRM)) {
        admin.ajaxRequest({
          mguniqueurl: "action/operationOrder",
          operation: operation,
          orders_id: orders_id,
          param: param
        },
        function (response) {
          if (operation == 'massPrint') {
            $('.block-print').html(response.data.html);
            $('#tiptip_holder').hide();
            setTimeout("window.print();", 500);
          } else {
            if(response.data.filecsv) {
              admin.indication(response.status, response.msg);
              setTimeout(function() {
                if (confirm(lang.CATALOG_MESSAGE_3+response.data.filecsv+lang.CATALOG_MESSAGE_2)) {
                location.href = mgBaseDir+'/'+response.data.filecsv;
              }}, 2000);            
            }
            response.data.count = response.data.count ? response.data.count : '';
            $('.button-list a[rel=orders]').parent().find('span').eq(0).text(response.data.count);
            admin.refreshPanel();
          }
        });
      }
    },

    /**
     *  Проверка заполненности полей, для каждого поля прописывается свое правило.
     */
    checkRulesForm: function () {
      $('.errorField').css('display', 'none');
      $('#order-data input, select').removeClass('error-input');
      var error = false;

      // покупателю обязательно надо заполнить телефон или email.
      var phone = $('#order-data input[name=phone]').val();
      var email = $('#order-data input[name=user_email]').val();
      
      // email или телефон обязательно надо заполнить.
      if ((!/^[-._a-zA-Z0-9]+@(?:[a-zA-Z0-9][-a-zA-Z0-9]{0,61}\.)+[a-zA-Z]{2,6}$/.test(email) || !email) && !$('#user_email_needed').is(":checked")) {
        $('#order-data input[name=user_email]').parent().find('.errorField').css('display', 'block');
        $('#order-data input[name=user_email]').addClass('error-input');
        admin.indication('error', lang.ERROR_EMPTY_BUYER_ORD);
        error = true;
      }
      // проверка валидности емэйла
      if(!admin.regTest(5,email) && !$('#user_email_needed').is(":checked")) {
        $('#order-data input[name=user_email]').parent().find('.errorField').css('display', 'block');
        $('#order-data input[name=user_email]').addClass('error-input');
        admin.indication('error', lang.ERROR_EMPTY_BUYER_ORD);
        error = true;
      }

      // товар обязательно надо добавить
      if ($("#totalPrice").text() == "0" && $('#add-order-wrapper #orderContent .titleProd').length == 0) {
        $('.search-block .errorField').css('display', 'block');
        $('.search-block input.search-field').addClass('error-input');
        $('.top-block').show();
        error = true;
      }

      // проверка реквизитов юр. лица
      if ($('#customer').val() == 'yur') {
        //var filds = ['nameyur', 'adress', 'inn', 'kpp', 'bank', 'bik', 'ks', 'rs'];
        var filds = ['inn'];
        filds.forEach(function (element, index, array) {
          if (!$('#order-data input[name=' + element + ']').val()) {
            $('#order-data input[name=' + element + ']').parent().find('.errorField').css('display', 'block');
            $('#order-data input[name=' + element + ']').addClass('error-input');
            error = true;
          }
        });
      }

      if (error == true) {
        return false;

      }
      return true;
    },
    /**
     * Собираем состав заказа из таблицы   
     * @returns {string}
     */
    getOrderContent: function () {
      var discountCum = $('.discount-system input[name=cumulative]').val()=='true' ? "true" : "false" ;
      var discountVol = $('.discount-system input[name=volume]').val()=='true' ? "true" : "false" ;

      obj = [];
      $('#order-data .order-history tbody#orderContent tr').each(function (index) {
        if ($(this).data('id')) {
          obj[index] = {};
          obj[index]['id'] = +$(this).data('id');
          obj[index]['variant'] = +$(this).data('variant');
          obj[index]['title'] = $(this).find('.titleProd').text();
          obj[index]['name'] = $(this).find('.titleProd').text();
          obj[index]['property'] = $(this).find('.property').html();
          obj[index]['price'] = admin.numberDeFormat($(this).find('.price input').val());
          obj[index]['fulPrice'] = $(this).find('.fullPrice input').val();
          obj[index]['code'] = $(this).find('.code').text();
          obj[index]['weight'] = $(this).find('.weight').text();
          obj[index]['currency_iso'] = $('#add-order-wrapper .currSpan [name=userCurrency]').val();
          obj[index]['count'] = $(this).find('input.count').val();
          obj[index]['coupon'] = $("select[name=promocode]").val();
          obj[index]['info'] = $(".user-info-order").text();
          obj[index]['url'] = $(this).find(".href-to-prod").data('url');
          obj[index]['discount'] = $('.discount span:first').text();
          obj[index]['discSyst'] = (discountCum+'/'+discountVol);
        }

      });

      return obj;
    },
    /**
     * Сохранение изменений в модальном окне заказа.
     * Используется и для сохранения редактированных данных и для сохранения нового заказа.
     * id - идентификатор продукта, может отсутствовать если производится добавление нового заказа.
     */
    saveOrder: function (id, container, number) {
      var orderContent = order.getOrderContent();

      if (!order.checkRulesForm()) {
        return false;
      }

      var yur = $('#customer').val() == 'yur' ? true : false;
      // Пакет характеристик заказа.
      var packedProperty = {
        mguniqueurl: "action/saveOrder",
        orderPositionCount: orderContent.length,
        //address: $('input[name=address]').val(),
        date_delivery: $('input[name=date_delivery]').val(),
        delivery_interval: $('.delivery-interval select[name=interval]').val() ? $('.delivery-interval select[name=interval]').val() : $('.itervalInitialVal').data('interval'),
        comment: $('textarea[name=comment]').val(),
        delivery_cost: $('#deliveryCost').val(),
        delivery_id: $('select#delivery :selected').attr('name'),
        id: id,
        number: number,
        name_buyer: $('input[name=name_buyer]').val(),
        payment_id: $('select#payment :selected').val(),
        phone: $('input[name=phone]').val(),
        status_id: $('select[name=status_id] :selected').val(),
        inform_user: $('#mailUser').val(),
        inform_user_text: $('.mailUserText').val(),
        summ: admin.numberDeFormat($('#totalPrice').text()),
        currency_iso: $('#add-order-wrapper .currSpan [name=userCurrency]').val(),
        user_email: $('input[name=user_email]').val(),
        nameyur: (yur ? container.find('.yur-list-editor input[name=nameyur]').val() : ''),
        adress: (yur ? container.find('.yur-list-editor input[name=adress]').val() : ''),
        inn: (yur ? container.find('.yur-list-editor input[name=inn]').val() : ''),
        kpp: (yur ? container.find('.yur-list-editor input[name=kpp]').val() : ''),
        ogrn: (yur ? container.find('.yur-list-editor input[name=ogrn]').val() : ''),
        bank: (yur ? container.find('.yur-list-editor input[name=bank]').val() : ''),
        bik: (yur ? container.find('.yur-list-editor input[name=bik]').val() : ''),
        ks: (yur ? container.find('.yur-list-editor input[name=ks]').val() : ''),
        rs: (yur ? container.find('.yur-list-editor input[name=rs]').val() : ''),
        order_content: JSON.stringify(orderContent),
      }

      var address_parts = $("#delivery :selected").data('address-parts');
      if (address_parts) {
        var parts = {};
        for (var i = 0; i < address_parts.length; i++) {
          parts[address_parts[i]] = admin.htmlspecialchars(admin.htmlspecialchars_decode($('.address_part input[name=address_'+address_parts[i]+']').val()));
        }
        packedProperty.address_parts = JSON.stringify(parts);
      }
      else{
        packedProperty.address = $('input[name=address]').val();
      }
      
      // отправка данных на сервер для сохранения
      admin.ajaxRequest(packedProperty,
        function (response) {
          // admin.clearGetParam();
          admin.indication(response.status, response.msg);
          order.indicatorCount(response.data.count);
          admin.closeModal('#add-order-wrapper');
          admin.refreshPanel();
        }
      );
    },
    // меняет индикатор количества новых заказов
    indicatorCount: function (count) {
      if (count == 0) {
        $('.button-list a[rel=orders]').parents('li').find('.message-wrap').hide();
      } else {
        $('.button-list a[rel=orders]').parents('li').find('.message-wrap').show();
        $('.button-list a[rel=orders]').parents('li').find('.message-wrap').text(count);
      }
    },
    /**
     * Удаляет запись из БД сайта и таблицы в текущем разделе
     */
    deleteOrder: function (id) {
      if (confirm(lang.DELETE + '?')) {
        admin.ajaxRequest({
          mguniqueurl: "action/deleteOrder",
          id: id
        },
        function (response) {
          admin.indication(response.status, response.msg);
          if(response.status == 'error') return false;
          order.indicatorCount(response.data.count - 1);
          $('tr[order_id=' + id + ']').remove();
          var newCount = ($('.widget-table-title .produc-count strong').text() - 1);
          if (newCount >= 0) {
            $('.widget-table-title .produc-count strong').text(newCount);
          }

          if ($('.product-table tr').length == 1) {
            var row = "<tr><td colspan=" + $('.product-table th').length + " class='noneOrders'>" + lang.ORDER_NONE + "</td></tr>"
            $('.order-tbody').append(row);
          }
          $('.product-count strong').html($('.product-count strong').html() - 1);
        }
        );
      }
    },

    /**
     * Редактирует заказ
     * @param {type} id
     * @returns {undefined}
     */
    editOrder: function (id) {
      admin.ajaxRequest({
        mguniqueurl: "action/getOrderData",
        id: id,
      },
      order.fillFields(),
        $('#add-order-wrapper')
        );
    },
    newOrder: function (id) {
      order.orderItems = [];
      admin.ajaxRequest({
        mguniqueurl: "action/getOrderData",
        id: null
      },
      order.fillFields('newOrder'),
        $('#add-order-wrapper')
        );
    },

    /**
     * Заполняет поля модального окна данными.
     */
    fillFields: function (type) {
      return function (response) {
        // для работы ссылки в заказе на скачивание CSV
        if(response.data.order != undefined) {
          csvLink = $('.csv-button a').attr('href');
          if(csvLink != undefined) {
            csvLink = csvLink.split('=');
            newCsvLink = [];
            for(i = 0; i < csvLink.length; i++) {
              if(csvLink[i] == '1&id') {
                newCsvLink.push(csvLink[i]);
                break;
              }
              if(csvLink[i] != '') newCsvLink.push(csvLink[i]);
            }
            newCsvLink.push(response.data.order.id);
            $('.csv-button a').attr('href', newCsvLink.join('='));
          }
        }

        if (type == 'newOrder') {
          $('#add-order-wrapper .currSpan').show();
          order.initialStatus = -1;
        } 
        else{
          $('#add-order-wrapper .currSpan').hide();
          order.initialStatus = parseInt(response.data.order.status_id);
        }

        $('.custom-order-fields').html(response.data.customFields);

        $('.order-edit-display').hide();
        $('.order-edit-visible').show();
        $("#orderStatus").removeClass('edit-layout');
        /* заполнение выпадающих списков */
        $('#add-order-wrapper .save-button').attr('id', response.data.order.id);
        $('#add-order-wrapper .save-button').attr('data-number', response.data.order.number);
        $('#add-order-wrapper .print-button').data('id', response.data.order.id);
        $('#add-order-wrapper .get-pdf-button').data('id', response.data.order.id);
        $('#add-order-wrapper .csv-button').data('id', response.data.order.id);
        $('#orderStatus').val(response.data.order.status_id ? response.data.order.status_id : '0');
        $('.mailUser').hide().find('input').val('false').removeAttr('checked');
        $('.mailUserText').hide();
        $('input[name=inform-user]').removeAttr('checked');
        var deliveryCurrentName = '';
        var deliveryDatePossible;
        //список способов доставки
        var deliveryList = '<select id="delivery">';
        var selected = '';
        if(typeof(response.data.deliveryArray) != "undefined") {
          $.each(response.data.deliveryArray, function (i, delivery) {
            selected = '';

            if (delivery.activity == 1) {
              if (delivery.id == response.data.order.delivery_id) {
                deliveryCurrentName = delivery.name;
                deliveryDatePossible = delivery.date;
                selected = 'selected';
              }
              deliveryList += '<option value="' + delivery.cost + 
              '" data-interval='+"'"+delivery.interval+"'"+
              '" data-address-parts='+"'"+delivery.address_parts+"'"+
              ' data-free="' + delivery.free + 
              '" data-plugin="' + delivery.plugin + 
              '" data-date="' + delivery.date + 
              '" name="' + delivery.id + '" ' + selected + 
              '>' + delivery.name + '</option>';
            }
          });
        }
        
        deliveryList += '</select>';

        var paymentCurrentName = '';
        //список способов оплаты
        var paymentList = '<select id="payment">';
        $.each(response.data.paymentArray, function (i, payment) {
          if(payment.activity != 0 && payment.id != undefined) {
            selected = '';
            if (payment.id == response.data.order.payment_id) {
              paymentCurrentName = payment.name;
              selected = 'selected';
            }

            paymentList += '<option value="' + payment.id + '" ' + selected + '>' + payment.name + '</option>';
          }
        });
        paymentList += '</select>';
        var coupon = '';
        var info = '';
        var orderContentTable = '';
        var discounts = '';
        if (response.data.order.currency_iso) {
          curr = response.data.order.currency_iso;
        }
        else{
          curr = admin.CURRENCY_ISO;
        }
        $('#add-order-wrapper .currSpan [name=userCurrency]').val(curr);
        if (response.data.order.order_content) {
          order.orderItems = [];

          $.each(response.data.order.order_content, function (i, element) {
            coupon = element.coupon ? element.coupon : '';
            info = element.info ? element.info : '';
            discounts = element.discSyst ? element.discSyst : '';
            // если товар находится в корне каталога, то приписываем категорию catalog           
            if (element.url) {

              var sections = admin.trim(element.url, '/').split('/');

              if (sections.length == 1) {
                element.url = 'catalog' + element.url;
              }
            }

            var position = {
              order_id: response.data.order.id,
              id: element.id,
              title: '<a href="' + mgBaseDir + '/' + element.url + '" data-url="' + element.url + '" class="href-to-prod"><span class="titleProd">' + element.name + '</span></a>' + '<span class="property">' + element.property + '</span>',
              prop: element.property,
              code: element.code,
              weight: element.weight,
              price: element.price,
              count: element.count,
              summ: (element.count * (element.price * 100)) / 100,
              image_url: element.image_url,
              fulPrice: element.fulPrice,
              discount: element.discount,
              maxCount: element.maxCount,
              variant: element.variant,
              notSet: element.notSet,
              currency_iso: curr,
              category_unit: element.category_unit
            };
            
            var url = element.url;
            var urls = url.split('/');  
            var orderItem = {
              id: position.id,
              title: element.name,
              price: position.price,
              weight: position.weight,
              count: position.count,
              url: urls.pop()
            };     
            
            order.orderItems.push(orderItem);
            orderContentTable += order.createPositionRow(position, 'view');

          });
        }
        
        if(info == '') {
          info = response.data.order.user_comment;
        }

        var data = {
          paymentList: paymentList,
          deliveryList: deliveryList,
          coupon: coupon,
          info: info,
          discounts: discounts,
          orderContentTable: orderContentTable,
          paymentCurrentName: paymentCurrentName,
          deliveryCurrentName: deliveryCurrentName,
          deliveryDatePossible: deliveryDatePossible,
        }

        $('.order-history').html(order.drawOrder(response, data));
        
        $("#add-order-wrapper input[name=user_email]").autocomplete({
          appendTo: ".autocomplete-holder",
          source: function (request, response) {
            var term = request.term;
              $.ajax({
                url: mgBaseDir + "/ajax",
                type: "POST",
                data: {
                  mguniqueurl: "action/getBuyerEmail",
                  email: term
                },
                dataType: "json",
                cache: false,
                // обработка успешного выполнения запроса
                success: function (resp) {
                  response(resp.data);
                }
              });
          },
          select: function (event, ui) {
            $.ajax({
              url: mgBaseDir + "/ajax",
              type: "POST",
              data: {
                mguniqueurl: "action/getInfoBuyerEmail",
                email: ui.item.value
              },
              dataType: "json",
              cache: false,
              // обработка успешного выполнения запроса
              success: function (response) {
                var user = response.data;
                
                $('#add-order-wrapper .editor-block input[name=name_buyer]').val(user.name+' '+ (user.sname ? user.sname : '' ));
                $('#add-order-wrapper .editor-block input[name=phone]').val(user.phone);
                $('#add-order-wrapper .editor-block input[name=address]').val(user.address);
                $('#add-order-wrapper .editor-block input[name=address_index]').val(user.address_index);
                $('#add-order-wrapper .editor-block input[name=address_country]').val(user.address_country);
                $('#add-order-wrapper .editor-block input[name=address_region]').val(user.address_region);
                $('#add-order-wrapper .editor-block input[name=address_city]').val(user.address_city);
                $('#add-order-wrapper .editor-block input[name=address_street]').val(user.address_street);
                $('#add-order-wrapper .editor-block input[name=address_house]').val(user.address_house);
                $('#add-order-wrapper .editor-block input[name=address_flat]').val(user.address_flat);

                if (user.inn) {
                  $('.yur-list-editor').show();
                  $('#add-order-wrapper .editor-block select[name=customer]').val('yur');
                  $('#add-order-wrapper .editor-block input[name=nameyur]').val(user.nameyur);
                  $('#add-order-wrapper .editor-block input[name=adress]').val(user.adress);
                  $('#add-order-wrapper .editor-block input[name=kpp]').val(user.kpp);
                  $('#add-order-wrapper .editor-block input[name=inn]').val(user.inn);
                  $('#add-order-wrapper .editor-block input[name=bank]').val(user.bank);
                  $('#add-order-wrapper .editor-block input[name=bik]').val(user.bik);
                  $('#add-order-wrapper .editor-block input[name=ks]').val(user.ks);
                  $('#add-order-wrapper .editor-block input[name=rs]').val(user.rs);
                } else {
                  $('.yur-list-editor').hide();
                  $('#add-order-wrapper .editor-block select[name=customer]').val('fiz');
                }                
              }
            });
          }, 
          minLength: 2
        });
        $(".ui-autocomplete").css('z-index', '1000');
        if (response.data.order.user_email == undefined || response.data.order.user_email.length>0) {
          $('#add-order-wrapper input[name=user_email]').prop("disabled", false);
          $('#add-order-wrapper #user_email_needed').prop("checked", false);
        }
        else{
          $('#add-order-wrapper input[name=user_email]').prop("disabled", true);
          $('#add-order-wrapper #user_email_needed').prop("checked", true);
        }
        // Если открыта модалка добавления нового заказа.
        if (type == 'newOrder') {
          $('#add-order-wrapper input[name=user_email]').prop("disabled", false);
          $('#add-order-wrapper #user_email_needed').prop("checked", false);
          $('.order-history input').val('');
          $('.order-history #orderContent').html('');
          order.enableEditor();
          $('#delivery option:first-of-type').prop('selected', 'selected');
          order.calculateOrder();
          $('#add-order-wrapper .save-button').attr('id', "");
          $('#add-order-wrapper .save-button').attr('data-number', "");
          $('.delivery-date').hide();
          $('#add-order-wrapper .order-edit-display #delivery').trigger('change');
        }
      }
    },

    /**
     * Создает верстку для модального окна, редактирования и добавления заказа
     * @param {type} id
     * @returns {undefined}
     */
    drawOrder: function (response, data) { 
      var dateDelivery = '';
      if (response.data.order.currency_iso) {
        var currency = response.data.order.currency_iso;
        $('#add-order-wrapper .currSpan [name=userCurrency]').val(currency);
        currency = order.currencyShort[currency];
      }
      else{
        var currency = admin.CURRENCY;
        $('#add-order-wrapper .currSpan [name=userCurrency]').val(admin.CURRENCY_ISO);
      }

      $.ajax({
        type: "POST",
        url: mgBaseDir + "/ajax",
        data: {
          mguniqueurl: "action/setAdminCurrency",
          userCustomCurrency: $('#add-order-wrapper .currSpan [name=userCurrency]').val()
        },
        cache: false,
        async: false,
        success: function (response) {}
      });
      var weight = 0;
      $.each(response.data.order.order_content, function (i, element) {
        weight += element.count*element.weight;
      });
      if (weight > 0) {
        var weightBlock = '<div class="row"><div class="small-6 large-10 columns text-right"><span>'+lang.ORDER_WEIGHT+':</span></div><div class="small-6 large-2 columns"><strong> <span class="order-weight">'+weight+'</span> '+lang.KG+'.</strong></div></div>';
      }
      else{
        var weightBlock = '<div class="row" style="display:none;"><div class="small-6 large-10 columns text-right"><span>'+lang.ORDER_WEIGHT+':</span></div><div class="small-6 large-2 columns"><strong> <span class="order-weight">'+weight+'</span> '+lang.KG+'.</strong></div></div>';;
      }
      order.address_parts_val = false;
      if (response.data.order.address_parts) {
        order.address_parts_val = response.data.order.address_parts;
      }
      if (response.data.order.address_imploded) {
        response.data.order.address = response.data.order.address_imploded;
      }
      /* заполнение состава заказа  */
      var editorBlock = '\
        <div class="row" style="padding:10px 20px;border-top:1px solid #e6e6e6;"><div class="large-12 small-12 columns">\
          <div class="order-edit-display fl-left editor-block" style="width:100%;">\
            <div class="row"><div class="large-6 small-12 columns">\
            <div class="row">\
              <div class="large-4 small-12 columns"><span>' + lang.ORDER_BUYER + ':</span></div>\
              <div class="large-8 small-12 columns"><input type="text" name="name_buyer" value="' + admin.htmlspecialchars(response.data.order.name_buyer) + '" ></div>\
            </div>\
            <div class="row">\
              <div class="large-4 small-12 columns"><span>' + lang.ORDER_ADDRESS + ':</span></div>\
              <div class="large-8 small-12 columns" style="position:relative;"><input type="text" name="address" value="' + admin.htmlspecialchars(response.data.order.address) + '" >\
                <a target="_blank" class="map-btn fa fa-map-marker" title="Посмотреть на карте" href="http://maps.yandex.ru/?text=' + encodeURIComponent(response.data.order.address) + '" ></a></strong></div>\
            </div>\
            <div class="row address_part">\
              <div class="large-4 small-12 columns"><span>' + lang.ORDER_ADDRESS + ':</span></div>\
              <div class="large-8 small-12 columns"><input type="text" name="address_index" placeholder="'+lang.ORDER_PH_ADDRESS_INDEX+'" value="'+(order.address_parts_val.index?order.address_parts_val.index:'')+'"></div>\
            </div>\
            <div class="row address_part">\
              <div class="large-4 small-12 columns"></div>\
              <div class="large-8 small-12 columns"><input type="text" name="address_country" placeholder="'+lang.ORDER_PH_ADDRESS_COUNTRY+'" value="'+(order.address_parts_val.country?order.address_parts_val.country:'')+'"></div>\
            </div>\
            <div class="row address_part">\
              <div class="large-4 small-12 columns"></div>\
              <div class="large-8 small-12 columns"><input type="text" name="address_region" placeholder="'+lang.ORDER_PH_ADDRESS_REGION+'" value="'+(order.address_parts_val.region?order.address_parts_val.region:'')+'"></div>\
            </div>\
            <div class="row address_part">\
              <div class="large-4 small-12 columns"></div>\
              <div class="large-8 small-12 columns"><input type="text" name="address_city" placeholder="'+lang.ORDER_PH_ADDRESS_CITY+'" value="'+(order.address_parts_val.city?order.address_parts_val.city:'')+'"></div>\
            </div>\
            <div class="row address_part">\
              <div class="large-4 small-12 columns"></div>\
              <div class="large-8 small-12 columns"><input type="text" name="address_street" placeholder="'+lang.ORDER_PH_ADDRESS_STREET+'" value="'+(order.address_parts_val.street?order.address_parts_val.street:'')+'"></div>\
            </div>\
            <div class="row address_part">\
              <div class="large-4 small-12 columns"></div>\
              <div class="large-8 small-12 columns"><input type="text" name="address_house" placeholder="'+lang.ORDER_PH_ADDRESS_HOUSE+'" value="'+(order.address_parts_val.house?order.address_parts_val.house:'')+'"></div>\
            </div>\
            <div class="row address_part">\
              <div class="large-4 small-12 columns"></div>\
              <div class="large-8 small-12 columns"><input type="text" name="address_flat" placeholder="'+lang.ORDER_PH_ADDRESS_FLAT+'" value="'+(order.address_parts_val.flat?order.address_parts_val.flat:'')+'"></div>\
            </div>\
            <div class="row">\
              <div class="delivery-date" style="display:none">\
                <div class="large-4 small-12 columns"><span >' + lang.DELIVERY_DATE + ':</span></div>\
                <div class="large-8 small-12 columns"><input type="text" name="date_delivery" value="' + (response.data.order.date_delivery ? response.data.order.date_delivery : '') + '" ></div>\
              </div>\
             </div>\
            <div class="row">\
              <div class="delivery-interval" style="display:none">\
                <div class="large-4 small-12 columns"><span >' + lang.DELIVERY_INTERVAL_ORDER + ':</span></div>\
                <div class="large-8 small-12 columns"><select name="interval"></select></div>\
              </div>\
             </div>\
            <div class="row">\
              <div class="large-4 small-12 columns"><span>' + lang.ORDER_PAYMENT + ':</span></div>\
              <div class="large-8 small-12 columns">' + data.paymentList + '</div>\
            </div>\
            <div class="row">\
              <div class="large-4 small-12 columns"><span>' + lang.ORDER_EMAIL + '</span></div>\
              <div class="large-8 small-12 columns"><span class="autocomplete-holder">\
                <input type="text" name="user_email" value="' + admin.htmlspecialchars(response.data.order.user_email) + '">\
              </span></div>\
            </div>\
            <div class="row">\
              <div class="large-4 small-12 columns"><span><label for="user_email_needed">' + lang.ORDER_EMAIL_NEEDED + '</label></span></div>\
              <div class="large-8 small-12 columns"><span>\
                  <div class="checkbox" style="margin: 0 0 10px;">\
                    <input type="checkbox" id="user_email_needed" name="user_email_needed" '+($('.user-email-needed-control').val() == 'true'?'checked="checked"':'')+'>\
                    <label for="user_email_needed"></label>\
                  </div>\
              </span></div>\
            </div>\
            <div class="row">\
              <div class="large-4 small-12 columns"><span>' + lang.ORDER_PHONE + '</span></div>\
              <div class="large-8 small-12 columns"><input type="text" name="phone" value="' + admin.htmlspecialchars(response.data.order.phone) + '"></div>\
            </div>\
            <div class="row">\
              <div class="large-4 small-12 columns"><span>' + lang.EDIT_ORDER_1 + ':</span></div>\
              <div class="large-8 small-12 columns"><select id="customer" name="customer">\
                <option value="fiz">' + lang.EDIT_ORDER_2 + '</option>\
                <option value="yur" ' + (response.data.order.yur_info.inn ? 'selected' : '') + '>' + lang.EDIT_ORDER_3 + '</option>\
              </select></div>\
            </div></div>\
            ';

      editorBlock += '\
          <div class="large-6 small-12 columns">\
            <div class="yur-list-editor">\
              <div class="row">\
                <div class="large-3 large-offset-1 small-12 columns">\
                  <span>' + lang.OREDER_LOCALE_9 + ':</span>\
                </div>\
                <div class="large-8 small-12 columns">\
                  <input type="text" name="nameyur" value="' + admin.htmlspecialchars((response.data.order.yur_info.nameyur ? admin.htmlspecialchars_decode(response.data.order.yur_info.nameyur) : '')) + '">\
                </div>\
              </div>\
              <div class="row">\
                <div class="large-3 large-offset-1 small-12 columns">\
                  <span>' + lang.OREDER_LOCALE_15 + ':</span>\
                </div>\
                <div class="large-8 small-12 columns">\
                  <input type="text" name="adress" value="' + admin.htmlspecialchars((response.data.order.yur_info.adress ? admin.htmlspecialchars_decode(response.data.order.yur_info.adress) : '')) + '" style="padding-right:25px;">\
                </div>\
              </div>\
              <div class="row">\
                <div class="large-3 large-offset-1 small-12 columns">\
                  <span>' + lang.OREDER_LOCALE_16 + ':</span>\
                </div>\
                <div class="large-8 small-12 columns">\
                  <input type="text" name="inn" value="' + admin.htmlspecialchars((response.data.order.yur_info.inn ? admin.htmlspecialchars_decode(response.data.order.yur_info.inn) : '')) + '">\
                </div>\
              </div>\
              <div class="row">\
                <div class="large-3 large-offset-1 small-12 columns">\
                  <span>' + lang.OREDER_LOCALE_17 + ':</span>\
                </div>\
                <div class="large-8 small-12 columns">\
                  <input type="text" name="kpp" value="' + admin.htmlspecialchars((response.data.order.yur_info.kpp ? admin.htmlspecialchars_decode(response.data.order.yur_info.kpp) : '')) + '">\
                </div>\
              </div>\
              <div class="row">\
                <div class="large-3 large-offset-1 small-12 columns">\
                  <span>' + lang.OREDER_LOCALE_18 + ':</span>\
                </div>\
                <div class="large-8 small-12 columns">\
                  <input type="text" name="bank" value="' + admin.htmlspecialchars((response.data.order.yur_info.bank ? admin.htmlspecialchars_decode(response.data.order.yur_info.bank) : '')) + '">\
                </div>\
              </div>\
              <div class="row">\
                <div class="large-3 large-offset-1 small-12 columns">\
                  <span>' + lang.OREDER_LOCALE_19 + ':</span>\
                </div>\
                <div class="large-8 small-12 columns">\
                  <input type="text" name="bik" value="' + admin.htmlspecialchars((response.data.order.yur_info.bik ? admin.htmlspecialchars_decode(response.data.order.yur_info.bik) : '')) + '">\
                </div>\
              </div>\
              <div class="row">\
                <div class="large-3 large-offset-1 small-12 columns">\
                  <span>' + lang.OREDER_LOCALE_20 + ':</span>\
                </div>\
                <div class="large-8 small-12 columns">\
                  <input type="text" name="ks" value="' + admin.htmlspecialchars((response.data.order.yur_info.ks ? admin.htmlspecialchars_decode(response.data.order.yur_info.ks) : '')) + '">\
                </div>\
              </div>\
              <div class="row">\
                <div class="large-3 large-offset-1 small-12 columns">\
                  <span>' + lang.OREDER_LOCALE_21 + ':</span>\
                </div>\
                <div class="large-8 small-12 columns">\
                  <input type="text" name="rs" value="' + admin.htmlspecialchars((response.data.order.yur_info.rs ? admin.htmlspecialchars_decode(response.data.order.yur_info.rs) : '')) + '">\
                </div>\
              </div>\
          </div>\
        </div>';

      editorBlock += '</div></div></div></div>';
      var disabled = '';
      
      var selectPromocode = '<select class="tool-tip-bottom" data-discount=0 name="promocode" '+disabled+'>';
      selectPromocode += '<option value=0>' + lang.EDIT_ORDER_4 + '</option>';
      $.each(response.data.order.promoCodes, function (i, element) {
        selectPromocode += '<option ' + (element == data.coupon ? 'selected' : '') + '>' + element + '</option>';
      });
      selectPromocode += '</select>';
      var discounts = '';

      if (response.data.order.discountsSystem || (0+data.discounts) > 0) {  
        var cumulative = false; 
        var volume = false;        
        if (data.discounts != '') {
          var discount = data.discounts.split('/');
          cumulative = discount[0]; 
          volume = discount[1];
          disabled = 'disabled title="'+lang.T_TIP_EDIT_ORDER_11+'"';
        }    
        if(cumulative) {
          cumul = '\
          <div class="checkbox">\
            <input type="checkbox" id="dis-1" class="tool-tip-bottom" name="cumulative" value='+cumulative+' '+(cumulative == 'true' ? 'checked' : '')+ ' '+ disabled+ '>\
            <label for="dis-1"></label>\
          </div>'
        } else {
          cumul = 'Отсутствует';
        }
        discounts = '<span>'+lang.ACCUMULATIVE_DISCOUNT+'</span></div>\
        <div class="large-2 small-6 columns">'+cumul+'</div>\
        </div><div class="row discount-system">\
        <div class="large-10 small-6 columns text-right"><span>'+lang.VOLUME_DISCOUNT+'</span></div>\
        <div class="large-2 small-6 columns">\
          <div class="checkbox">\
            <input type="checkbox" id="dis-2" name="volume" value='+volume+' '+(volume == 'true' ? 'checked' : '')+' '+ disabled+ '>\
            <label for="dis-2"></label>\
          </div>';
      }

      // deliveryDatePossible - 1 или 0 - возможность добавления даты доставки в заказ, значение выбранного метода доставки
      if (data.deliveryDatePossible == 1) {
        dateDelivery = '<div class="row">\
                          <div class="large-4 small-12 columns"><span>' + lang.DELIVERY_DATE + ':</span></div>\
                          <div class="large-8 small-12 columns"><strong>' + (response.data.order.date_delivery ? response.data.order.date_delivery : lang.NO_DATE) + '</strong></div>\
                        </div>\ ';
      }
      if (response.data.order.delivery_interval) {
        dateDelivery += '<div class="row">\
                          <div class="large-4 small-12 columns"><span>' + lang.DELIVERY_INTERVAL_ORDER + ':</span></div>\
                          <div class="large-8 small-12 columns"><strong class="itervalInitialVal" data-interval=\''+admin.htmlspecialchars(admin.htmlspecialchars_decode(response.data.order.delivery_interval))+'\'>' + admin.htmlspecialchars(admin.htmlspecialchars_decode(response.data.order.delivery_interval)) + '</strong></div>\
                        </div>\ ';
      }
      
      var orderHtml = '<div style="overflow:auto;">\
                     <table class="status-table main-table small-table">\
                       <thead>\
                        <tr>\
                          <th></th>\
                          <th class="prod-name">' + lang.ORDER_PROD + '</th>\
                          <th>' + lang.ORDER_CODE + '</th>\
                          <th>' + lang.WEIGHT + '</th>\
                          <th class="prod-price">' + lang.ORDER_PRICE + '</th>\
                          <th class="prod-price">' + lang.ORDER_DISCOUNT + '</th>\
                          <th class="prod-price">' + lang.ORDER_DISCOUNT_PRICE + '</th>\
                          <th>' + lang.ORDER_COUNT + '</th>\
                          <th>' + lang.ORDER_SUMM + '</th>\
                          <th class="prod-remove"></th>\
                        </tr>\
                      </thead>\
                      <tbody id="orderContent">' + data.orderContentTable + '</tbody>\
                     </table></div>\
                     <div style="border-top:1px solid #eee;"></div>\
                      <div class="row"><div class="small-12 large-12 columns">\
                       <div class="order-payment-sum" style="margin:10px 20px;">\
                          <div class="row">\
                            <div class="small-6 large-10 columns text-right"><span>' + lang.ORDER_TOTAL_PRICE + ':</span></div>\
                            <div class="small-6 large-2 columns"><span><strong>' + '<span id="totalPrice">' + admin.numberFormat(response.data.order.summ * 1) + '</span>' + " <span class='changeCurrency'>" + currency + '</span></span></strong></div>\
                          </div>\
                          <div class="row promocode-order">\
                            <div class="small-6 large-10 columns text-right"><span>' + lang.EDIT_ORDER_11 + ': </span></div>\
                            <div class="small-6 large-2 columns"><span class="order-edit-visible"><strong>' + (data.coupon !='0'  ? data.coupon : lang.EDIT_ORDER_4 )  + '</strong></span>\
                            <span class="order-edit-display code-block">' + selectPromocode + '</span></div>\
                          </div>\
                          <div class="row discount-system">\
                            <div class="small-6 large-10 columns text-right">'+ discounts +
                          '</div></div>\
                          <div class="row">\
                              <div class="small-6 large-10 columns text-right"><span>' + lang.ORDER_DELIVERY + ':</span></div>\
                              <div class="small-6 large-2 columns"><span class="order-edit-visible"><strong>' + data.deliveryCurrentName + '</strong></span>\
                              <span class="order-edit-display">' + data.deliveryList + '</span></div>\
                          </div class="row">' + 
                          '<div class="row">\
                              <div class="small-6 large-10 columns text-right"><span>' + lang.EDIT_ORDER_6 + ':</span></div>\
                              <div class="small-6 large-2 columns"><strong><span class="order-edit-visible">' + admin.numberFormat(response.data.order.delivery_cost) + " <span class='changeCurrency'>" + currency + '</span></span></strong>\
                              <span class="order-edit-display">' + '<input class="small" style="display:inline-block" type="text" id="deliveryCost" value="' +response.data.order.delivery_cost + '">' + " <span class='changeCurrency'>" + currency + '</span></span></div>\
                          </div>\
                          <div class="row">\
                              <div class="small-6 large-10 columns text-right"><span>' + lang.ORDER_SUMM + ':</span></div>\
                              <div class="small-6 large-2 columns"><strong><span class="total-price">' + '<span id="fullCost">' + admin.numberFormat((response.data.order.summ * 1 + response.data.order.delivery_cost * 1)) + '</span>' + " <span class='changeCurrency'>" + currency + '</span></span></strong></div>\
                          </div>\
                          '+weightBlock+'\
                        </div></div>\
                     </div>\
                        </div>'
        + editorBlock +
                    '<div class="row" style="margin: 0 20px 10px 20px;"><div class="small-12 large-6 columns">'+
                      '<div class="order-other-info order-edit-visible">\
                        <div class="row">\
                            <div class="large-4 small-12 columns"><span>' + lang.ORDER_BUYER + ':</span></div>\
                            <div class="large-8 small-12 columns"><strong>' + response.data.order.name_buyer + '</strong></div>\
                        </div>\
                        <div class="row">\
                            <div class="large-4 small-12 columns"><span>' + lang.ORDER_ADDRESS + ':</span></div>\
                            <div class="large-8 small-12 columns"><strong><a target="_blank" href="http://maps.yandex.ru/?text=' + encodeURIComponent(response.data.order.address) + '">' + response.data.order.address + '</a></strong></div>\
                        </div>\ '
                        + dateDelivery +
                        '<div class="row">\
                            <div class="large-4 small-12 columns"><span>' + lang.ORDER_PAYMENT + ':</span></div>\
                            <div class="large-8 small-12 columns"><strong><span class="icon-payment-' + response.data.order.payment_id + '"></span>' + data.paymentCurrentName + '</strong></div>\
                        </div>\
                        <div class="row">\
                            <div class="large-4 small-12 columns"><span>' + lang.ORDER_EMAIL + ':</span></div>\
                            <div class="large-8 small-12 columns"><a><a href="mailto:' + response.data.order.user_email + '">' + response.data.order.user_email + '</a></strong></div>\
                        </div>\
                        <div class="row">\
                            <div class="large-4 small-12 columns"><span>' + lang.ORDER_PHONE + ':</span></div>\
                            <div class="large-8 small-12 columns"><strong><a href="tel:' + response.data.order.phone + '">' + response.data.order.phone + '</a></strong></div>\
                        </div>\
                        <div class="row">\
                            <div class="large-4 small-12 columns"><span>' + lang.ORDER_IP + ':</span></div>\
                            <div class="large-8 small-12 columns"><strong>' + response.data.order.ip+ '</strong></div>\
                        </div>'+
                      '</div></div>';
      
      if (response.data.order.yur_info.inn) {
        orderHtml += '\
          <div class="small-12 large-6 columns">\
            <ul class="order-edit-visible" style="margin:0;">\
              <div class="row"><div class="large-3 large-offset-1 columns">\
                  <span>'+ lang.OREDER_LOCALE_9 +':</span>\
                </div>\
                <div class="large-8 small-12 columns">\
                  <strong>' + (response.data.order.yur_info.nameyur ? response.data.order.yur_info.nameyur : '') + '</strong>\
                </div>\
              </div>\
              <div class="row"><div class="large-3 large-offset-1 columns">\
                  <span>'+ lang.OREDER_LOCALE_15 +':</span>\
                </div>\
                <div class="large-8 small-12 columns">\
                  <strong>' + (response.data.order.yur_info.adress ? response.data.order.yur_info.adress : '') + '</strong>\
                </div>\
              </div>\
              <div class="row"><div class="large-3 large-offset-1 columns">\
                  <span>'+ lang.OREDER_LOCALE_16 +':</span>\
                </div>\
                <div class="large-8 small-12 columns">\
                  <strong>' + (response.data.order.yur_info.inn ? response.data.order.yur_info.inn : '') + '</strong>\
                </div>\
              </div>\
              <div class="row"><div class="large-3 large-offset-1 columns">\
                  <span>'+ lang.OREDER_LOCALE_17 +':</span>\
                </div>\
                <div class="large-8 small-12 columns">\
                  <strong>' + (response.data.order.yur_info.kpp ? response.data.order.yur_info.kpp : '') + '</strong>\
                </div>\
              </div>\
              <div class="row"><div class="large-3 large-offset-1 columns">\
                  <span>'+ lang.OREDER_LOCALE_18 +':</span>\
                </div>\
                <div class="large-8 small-12 columns">\
                  <strong>' + (response.data.order.yur_info.bank ? response.data.order.yur_info.bank : '') + '</strong>\
                </div>\
              </div>\
              <div class="row"><div class="large-3 large-offset-1 columns">\
                  <span>'+ lang.OREDER_LOCALE_19 +':</span>\
                </div>\
                <div class="large-8 small-12 columns">\
                  <strong>' + (response.data.order.yur_info.bik ? response.data.order.yur_info.bik : '') + '</strong>\
                </div>\
              </div>\
              <div class="row"><div class="large-3 large-offset-1 columns">\
                  <span>'+ lang.OREDER_LOCALE_20 +':</span>\
                </div>\
                <div class="large-8 small-12 columns">\
                  <strong>' + (response.data.order.yur_info.ks ? response.data.order.yur_info.ks : '') + '</strong>\
                </div>\
              </div>\
              <div class="row"><div class="large-3 large-offset-1 columns">\
                  <span>'+ lang.OREDER_LOCALE_21 +':</span>\
                </div>\
                <div class="large-8 small-12 columns">\
                  <strong>' + (response.data.order.yur_info.rs ? response.data.order.yur_info.rs : '') + '</strong>\
                </div>\
              </div>\
          </ul>\
        </div>';
      }
      orderHtml += '</div></div>';
      if (data.info) {
        orderHtml += '<div style="margin: 0 30px 10px;" class="order-comment-block added-comment" >\
                  <span>' + lang.EDIT_ORDER_7 + ':</span>\
                  <div class="user-info-order">' + data.info + '</div></div>';
      }
      orderHtml += '<div style="margin: 0 30px 10px;" class="order-comment-block ' + (response.data.order.comment ? 'added-comment' : 'order-edit-display') + '" >\
                  <span>' + lang.EDIT_ORDER_8 + ':</span>\
                  <div class="order-edit-visible">' + (response.data.order.comment ? response.data.order.comment : ' ') + '</div>\
                  <textarea name="comment" class="cancel-order-reason order-edit-display">' + (response.data.order.comment ? response.data.order.comment : '') + '</textarea>\
                </div>\
               ';


      return orderHtml;
    },

    /**
     * Сохраняет настройки к заказам.
     */
    savePropertyOrder: function () {
      var request = "mguniqueurl=action/savePropertyOrder&" + $("form[name=requisites]").formSerialize();

      admin.ajaxRequest(
        request,
        function (response) {
          admin.indication(response.status, response.msg);
          $('.property-order-container').slideToggle(function () {
            $('.widget-table-action').toggleClass('no-radius');
          });
        }
      );

      return false;
    },

    /**
     * Просчитывает стоимость заказа, обновляет поля.
     */
    calculateOrder: function () {
      if ($("#deliveryCost").val() === '') {$("#deliveryCost").val(0);}
      var totalFullSumm = 0;
      var totalWeight = 0;
      var format = admin.PRICE_FORMAT;  
      var cent = format.substring(format.length-3, format.length-2);
      
      $('tbody#orderContent tr').each(function (i, element) { 
        var fullPrice = $(this).find('td.fullPrice input').val();
        var count = $(this).find('td.count input').val();        

        //Округляем цену, если задан формат цен без десятичных знаков
        if(cent != '.' && cent != ',') {
          fullPrice = Math.round(fullPrice);
        }
        
        var fullSumm = count * (Math.round(fullPrice * 100));        
        fullSumm = fullSumm *100;   
        totalFullSumm += Math.ceil(fullSumm)/100;        
      });
      totalFullSumm = totalFullSumm / 100;
      $('#totalPrice').attr('data-fullsum', totalFullSumm);
      $.ajax({
        type: "POST",
        url: mgBaseDir + "/ajax",
        data: {
          mguniqueurl: "action/getDiscount",
          summ: totalFullSumm,
          email: $('#order-data input[name="user_email"]').val(),
          promocode: $("#order-data select[name=promocode]").val(),
          cumulative: $('#order-data .discount-system input[name=cumulative]').val(),
          volume: $('#order-data .discount-system input[name=volume]').val(),
          paymentId: $('select#payment option:selected').val(),
          orderItems: order.orderItems
        },
        dataType: "json",
        cache: false,
        success: function (response) {
//          $(".promocode-percent span").text(response.data.percent);
          var totalSumm = 0;
          
          $('tbody#orderContent tr').each(function (i, element) {
            var id = $(this).attr('data-id');
            var percent = 0;
            
            response.data.productDiscount.forEach(function(item, i, arr) {
              if (id == item.id) {
                percent = parseFloat(item.discount);
              }
            });
            
            var price = $(this).find('td.fullPrice input.price-val').val();
            var priceDiscount = (price - (price * percent / 100));
            priceDiscount = (priceDiscount*100).toFixed();
            priceDiscount = Math.ceil(priceDiscount)/100;
            price = priceDiscount;
            if(cent != '.' && cent != ',') {
              price = Math.round(price);
            }
            
            $(this).find('td.discount span').text(percent);
            
            $(this).find('td.price span.value').text(admin.numberFormat(price)).show();
            $(this).find('td.price input').val(admin.numberFormat(price));                                    
            
            var count = $(this).find('td.count input').val();
            var summ = count * (Math.round(price * 100));
            summ = summ / 100;                                   

            $(this).find('td.summ').data('summ', summ);
            $(this).find('td.summ span').text(admin.numberFormat(summ));
            totalSumm += Math.round(summ * 100);
            totalWeight += count*$(this).find('.weight').data('weight');
          });
          totalSumm = totalSumm / 100;
          
          var deliveryCost = $('#deliveryCost').val();          
          var plugin = $("#delivery :selected").data('plugin');
          var orderId = $('button.save-button').attr('id');
          
          if(plugin && (!order.firstCall || !orderId)) {
            deliveryCost = order.getDeliveryCost(plugin);            
          }          

          if (totalSumm >= $('#delivery option:selected').data('free') && $('#delivery option:selected').data('free') > 0 || deliveryCost == undefined) {
            deliveryCost = 0;
          }
          
          var fullCost = totalSumm * 100 + parseFloat(deliveryCost) * 100;
          fullCost = fullCost / 100;
          $('#deliveryCost').val(deliveryCost);
          $('#totalPrice').text(admin.numberFormat(totalSumm));
          $('#fullCost').text(admin.numberFormat(fullCost ? fullCost : 0));
          if (totalWeight > 0) {
            $('.order-weight').text(totalWeight).closest('.row').show();
          }
          else{
            $('.order-weight').closest('.row').hide();
          }
        }
      });

      return false;
    },    

    /**
     * 
     * @param string plugin
     * @returns {undefined}
     */
    getDeliveryCost: function(plugin) {      
      var deliveryId = $('#delivery option:selected').attr('name');
      order.deliveryCost = 0;
      order.getDeliveryOrderOptions(deliveryId);      
      loader = $('.mailLoader');
      
      if(order.deliveryCost > 0 || order.orderItems.length == 0) {
        return order.deliveryCost;
      }
      // флаг, говорит о том что начался процесс загрузки с сервера
      admin.WAIT_PROCESS = true;
      loader.hide();
      loader.before('<div class="view-action" style="display:none; margin-top:-2px;">' + lang.LOADING + '</div>');
      admin.waiting(true);      
      //Запрашиваем расчет стоимости доставки у плагина
      $.ajax({
        type: "POST",
        url: mgBaseDir+"/ajaxrequest",
        async: false,
        data: {
          pluginHandler: plugin, // имя папки в которой лежит данный плагин
          actionerClass: 'Pactioner', // класс Pactioner в Pactioner.php - в папке плагина
          action: "getPriceForParams", // название действия в пользовательском  классе 
          deliveryId: deliveryId,
          orderItems: order.orderItems
        },
        cache: false,
        dataType: 'json',        
        success: function(response) {        
          if(response.data.deliverySum >= 0) {
            order.deliveryCost = response.data.deliverySum;
            $(window).trigger('getDeliveryCost:finish');
          }else{
            alert(response.data.error);
          }     
          // завершился процесс
          admin.WAIT_PROCESS = false;
          //прячим лоадер если он успел появиться
          admin.waiting(false);
          loader.show();
          $('.view-action').remove();
        }            
      });
      return order.deliveryCost;
    },
    /**
     * 
     * @param int deliveryId
     * @returns {undefined}
     */
    getDeliveryOrderOptions: function(deliveryId, static) {      
      var orderId = $('button.save-button').attr('id');      
      
      if(!orderId) {
        orderId = 0;
      }
      
      $.ajax({
        type: "POST",
        url: mgBaseDir+"/order",
        data: {          
          action: "getDeliveryOrderOptions",
          order_id: orderId,
          deliveryId: deliveryId,
          firstCall: order.firstCall
        },
        dataType: "json",
        cache: false,        
        success: function(response) { 
          if(response != null) {
            order.deliveryCost = response.deliverySum;   
            
            if(static) {
              $(window).trigger("delivery:change");
            }
          }                   
        }, 
        error: function(a,b,c) {
          console.info(a);
          console.info(b);
          console.info(c);
        }
      });      
    },
    /**
     * Получает данные из формы фильтров и перезагружает страницу
     */
    getProductByFilter: function () {
      var request = $("form[name=filter]").formSerialize();
      admin.show("orders.php", "adminpage", request + '&applyFilter=1', order.callbackOrders);
      return false;
    },

    /**
     * изменяет строки в таблице товаров при редактировании изменении.                    
     */
    drawRowOrder: function (element, assocStatus) {
      if (element.currency_iso == null) {
        var currency = admin.CURRENCY;
      }
      else{
        var currency = element.currency_iso;
        currency = order.currencyShort[currency];
      }

      var deliveryText = $('#add-order-wrapper #delivery option[name=' + element.delivery_id + ']').text();
      var paymentText = $('#add-order-wrapper #payment option[value=' + element.payment_id + ']').text();
      var statusName = $('#add-order-wrapper #orderStatus option:selected').text();
      var orderSumm = parseFloat(element.summ) + parseFloat(element.delivery_cost);
       // html верстка для  записи в таблице раздела  

      var row = '\
       <tr class="" order_id="' + element.id + '">\
       <td class="check-align">\
        <div class="checkbox">\
          <input type="checkbox" id="c2-' + element.id + '" name="order-check">\
          <label for="c2-' + element.id + '"></label>\
        </div>\
       <td> ' + element.id + '</td>\
       <td> ' + element.number + '</td>\
       <td class="add_date"> ' + element.date + '</td>\
       <td> ' + element.name_buyer + '</td>\
       <td> ' + element.user_email + '</td>\
       <td> ' + deliveryText + '</td>\
       <td> <span class="icon-payment-' + element.payment_id + '"></span>' + paymentText + '</td>\
       <td><strong> ' + admin.numberFormat(orderSumm) + ' ' + currency + '</strong></td>\
       <td class="statusId id_' + element.status_id + '">\
       <span class="badge ' + (assocStatus[element.status_id] ?assocStatus[element.status_id] : 'get-paid' ) + '">' + statusName + '</span>\
       </td>\
       <td class="actions">\
       <ul class="action-list">\
       <li class="see-order" id="' + element.id + '"  data-number="'+ element.number +'">\
       <a class="tool-tip-bottom fa fa-pencil" href="javascript:void(0);" title="' + lang.SEE + '"></a>\
       </li>\
       <li class="order-to-csv"><a  data-id="' + element.id + '" class="tool-tip-bottom fa fa-download" href="javascript:void(0);" title="'+lang.OREDER_LOCALE_1+'"></a></li>\
       <li class="order-to-pdf">\
        <a data-id="' + element.id + '" class="tool-tip-bottom fa fa-file-pdf-o" href="javascript:void(0);" title="'+lang.PRINT_ORDER_PDF+'"></a>\
       </li>\
       <li class="order-to-print">\
        <a  data-id="' + element.id + '" class="tool-tip-bottom fa fa-print" href="javascript:void(0);" title="'+lang.PRINT_ORDER+'"></a>\
       </li>\
       <li class="clone-row" id="' + element.id + '"><a title="'+lang.CLONE_ORDER+'" class="tool-tip-bottom fa fa-files-o" href="javascript:void(0);"></a></li>\
       <li class="delete-order" id="' + element.id + '"><a class="tool-tip-bottom fa fa-trash" href="javascript:void(0);" title="' + lang.DELETE + '"></a>\
       </li>\
       </ul>\
       </tr>';

      return row;

    },
    /**
     * функция для приема подписи из аплоадера
     */
    getSignFile: function (file) {
      var src = file.url;
      src = 'uploads' + src.replace(/(.*)uploads/g, '');
      $('.section-order .property-order-container input[name="sing"]').val(src);
      $('.section-order .property-order-container .singPreview').attr("src", file.url);
    },
    /**
     * функция для приема печати из аплоадера
     */
    getStampFile: function (file) {
      var src = file.url;
      src = 'uploads' + src.replace(/(.*)uploads/g, '');
      $('.section-order .property-order-container input[name="stamp"]').val(src);
      $('.section-order .property-order-container .stampPreview').attr("src", file.url);
    },
    
    /**
     * Печать заказа
     */
    printOrder: function (id, template) {      
      admin.ajaxRequest({
        mguniqueurl: "action/printOrder",
        id: id,
        template: template
      },
      function (response) {
        //admin.indication(response.status, response.msg);     
        $('.block-print').html(response.data.html);
        $('#tiptip_holder').hide();
        setTimeout("window.print();", 500);
      }
      );
    },
    
    /**
     * Включает режим редактирования заказа
     */
    enableEditor: function () {
      $('#add-order-wrapper .currSpan').show();
      var id = $("#add-order-wrapper .save-button").attr('id');
      var number = $("#add-order-wrapper .save-button").attr('data-number');
      if (id) {
        $('.add-order-table-icon').text(lang.EDIT_ORDER_9 + ' №' + number + ' от ' + $('tr[order_id=' + id + '] .add_date').text());
      } else {
        $('.add-order-table-icon').text(lang.EDIT_ORDER_10);
      }
      $(".discount-system input").prop("disabled", false);
      $(".order-edit-display").show();
      $(".order-edit-visible").hide();
      $("#orderStatus").addClass('edit-layout');
      var date = $("#delivery :selected").data('date');
      if (date == 1) {
        $('.delivery-date').show();
      } else {
        $('.delivery-date').hide();
      }
      $("#customer").change();
      var interval = $("#delivery :selected").data('interval');
      if (interval) {
        if (!$.isArray(interval)) {
          interval = interval.replace('["',"").replace('"]',"").split('","');
        }
        $('.delivery-interval [name=interval] option').remove();
        for (var i = 0; i < interval.length; i++) {
          if (interval[i] != '') {
            $('.delivery-interval [name=interval]').append("<option value='"+admin.htmlspecialchars(admin.htmlspecialchars_decode(interval[i]))+"'>"+admin.htmlspecialchars(admin.htmlspecialchars_decode(interval[i]))+"</option>");
          }
        }
        $('.delivery-interval').show();
        interval = $('strong.itervalInitialVal').data('interval');
        if (interval) {
          $('.delivery-interval [name=interval]').val(interval);
        }
      }
      else{
        $('.delivery-interval').hide();
      }

      var address_parts = $("#delivery :selected").data('address-parts');
      $('.address_part').hide().find('input').val('');
      if (address_parts) {
        for (var i = 0; i < address_parts.length; i++) {

          $('.address_part input[name=address_'+address_parts[i]+']').val(admin.htmlspecialchars_decode(order.address_parts_val[address_parts[i]])).closest('.row').show();
        }
        $('input[name=address]').closest('.row').hide();
      }
      else{
        $('input[name=address]').closest('.row').show();
      }

      //$("input[name=phone]").mask("+7 (999) 999-99-99");
      //$("input[name=phone]").mask("+38 (999) 999-99-99");
      $('#delivery').on('change', function () {
        $('.delivery-date').hide(); 
        $('span.add-delivery-info').remove();
        
        if($("#delivery :selected").data('date') == 1) {
          $('.delivery-date').show();
        }

        var interval = $("#delivery :selected").data('interval');
        if (interval) {
          $('.delivery-interval [name=interval] option').remove();
          for (var i = 0; i < interval.length; i++) {
            if (interval[i] != '') {
              $('.delivery-interval [name=interval]').append('<option value="'+admin.htmlspecialchars(admin.htmlspecialchars_decode(interval[i]))+'">'+admin.htmlspecialchars(admin.htmlspecialchars_decode(interval[i]))+'</option>');
            }
          }
          $('.delivery-interval').show();
        }
        else{
          $('.delivery-interval').hide();
        }

        var address_parts = $("#delivery :selected").data('address-parts');
        $('.address_part').hide();
        if (address_parts) {
          for (var i = 0; i < address_parts.length; i++) {
            $('.address_part input[name=address_'+address_parts[i]+']').closest('.row').show();
          }
          $('input[name=address]').closest('.row').hide();
        }
        else{
          $('input[name=address]').closest('.row').show();
        }
       
        var select = $(this);
        var deliveryId = $("#delivery :selected").attr('name');
        
        var plugin = $("#delivery :selected").data('plugin');
        if(plugin && plugin.length > 0) {  
          $.ajax({
            type: "POST",
            url: mgBaseDir+"/ajaxrequest",
            data: {
              pluginHandler: plugin, // имя папки в которой лежит данный плагин
              actionerClass: 'Pactioner', // класс Pactioner в Pactioner.php - в папке плагина
              action: "getAdminDeliveryForm", // название действия в пользовательском  классе 
              deliveryId: deliveryId,
              firstCall: order.firstCall,
              orderItems: order.orderItems,
              orderId: id
            },
            cache: false,
            dataType: 'json',
            success: function(response) { 
              order.firstCall = false;
              select.parents('span').append('<span class="add-delivery-info">'+response.data.form+'</span>');
              $('input#deliveryCost').prop("disabled",true);
              // $('#delivery').trigger('change');
            }            
          });
        }else{
          $('span.add-delivery-info').remove();
          $('input#deliveryCost').prop("disabled",false);
        }
      });
    },
    /**
     * Пересчет цены товара аяксом в форме добавления заказа.
     */
    refreshPriceProduct: function () {
      var request = $('.property-form').formSerialize();
      //$('.orders-table-wrapper .property-form .addToCart').css('visibility', 'hidden');
      // Пересчет цены.        
      $.ajax({
        type: "POST",
        url: mgBaseDir + "/product/",
        data: "calcPrice=1&" + request,
        dataType: "json",
        cache: false,
        success: function (response) {
          if ('success' == response.status) {
            //$('#order-data .product-block .price-sp').text(response.data.price_wc);
            $('#order-data .product-block .price-sp').text(Math.ceil(response.data.real_price*100)/100);            
            $('#order-data .product-block .code-sp').text(response.data.code);
            $('#order-data .product-block .weight-sp').text(response.data.weight);
            $('#order-data .product-block .count-sp').text(response.data.count=='-1' ? lang.AVAILIBLE_NOW :  lang.REMAIN +' '+response.data.count);
            $('#order-data .product-block .count-sp').data('count', response.data.count);
            $('.orders-table-wrapper .property-form .addToCart').css('visibility', 'visible');
          }
        }
      });
    },
    /**
     * Клик по найденным товарам поиске в форме добавления заказа
     */
    viewProduct: function (elementIndex) {
      $('.search-block .errorField').css('display', 'none');
      $('.search-block input.search-field').removeClass('error-input');
      var product = admin.searcharray[elementIndex];

      if (product.category_unit == null) {product.category_unit = 'шт.'}
      order.searchUnit = product.category_unit;
      if (!product.category_url) {
        product.category_url = 'catalog';
      }
      if (product.category_url.charAt(product.category_url.length-1) == '/') {
        product.category_url = product.category_url.slice(0,-1);
      }
      
      var html = '<div class="row" style="margin: 0 7px;"><div class="large-6 small-12 columns"><div class="image-sp fl-right"><img src="' + product.image_url + '" style="max-width:50px;"></div>';
      html +=
        '<div class="product-info"><div class="title-sp">' +
        '<a href="' + mgBaseDir + '/' + product.category_url + '/' + product.url +
        '" data-url="' + product.category_url +
        "/" + product.url + '" class="url-sp" target="_blank">' +
        product.title + '</a>' +
        '</div>';
      html += '<div class="id-sp" style="display:none" data-set='+product.notSet+'>' + product.id + '</div>';
      html += '<div class="price-line">' + lang.PRICE_PRODUCT + ': <span class="price-sp">' + Math.round(product.price_course*100)/100 + '</span>';
      html += '<span class="currency-sp"> ' + product.currency + '</span></div>';
      html += '<div class="code-line">' + lang.CODE_PRODUCT + ': <span class="code-sp">' + product.code + '</span></div>';
      html += '<div class="weight-line"> <span class="count-sp" data-count="'+product.count+'">' + (product.count =='-1' ? lang.AVAILIBLE_NOW : lang.REMAIN + ' '+ product.count) + '</span></div>';
      html += '<div class="weight-line">' + lang.WEIGHT + ': <span class="weight-sp">' + product.weight + '</span></div>';
      html += '<div class="form-sp">'+product.propertyForm+'</div>';
      html += '</div></div><div class="large-6 small-12 columns">';
      html += '<div class="desc-sp">\
              <ul class="template-tabs-menu inline-list" style="margin-top:0">\
                <li class="is-active template-tabs button primary" part="descr">\
                  <a href="javascript:void(0);"><span>'+lang.PLUG_DESC+'</span></a>\
                </li>'+
              '</ul><span class="descrip">' + product.description + '</span><span class="propsTo"></span></div></div>';
      html += '<div class="clear"></div>';
      $('#order-data .product-block').html(html);
      $('#order-data .product-block .orderUnit').text(' '+product.category_unit);
      $('#order-data .product-block .propsFrom').detach().appendTo('#order-data .product-block .propsTo').hide();
      $('.addToCart').wrap('<span class="button success btn-a-white"></span>');
      if (!$('#order-data .addiProps .property-title').length) {$('#order-data .addiProps').hide()}
      $('input[name=searchcat]').val('');
      $('.fastResult').hide();
      $('.addToCart').attr('href', 'javascript:void(0);');
      $('#order-data .product-block .amount_input').trigger('change');
    },

    /**
     * Добавляет товар в заказ
     */
    addToOrder: function (obj) {
      if ($('#add-order-wrapper .save-button').attr('id')&& !$('#order-data .id-sp').data('set')) {
        admin.indication('error', lang.ERROR_MESSAGE_20);
        return false;
      }
      $('.search-block .errorField').css('display', 'none');
      $('.search-block input.search-field').removeClass('error-input');

      var max_count_in_order = $('#max-count-cart').text();
      var count_in_order = $('#orderContent tr').length + 1;
      if (count_in_order > max_count_in_order) {
        admin.indication('error', lang.LIMIT_EXCEEDED + ' [max =' + max_count_in_order + ']');
        return false;
      }
      var count = $('#order-data .count-sp').data('count');
      if (count == '0') {
        admin.indication('error', lang.NON_AVAILIBLE);
        return false;
      }
      var maxCount = (count == '-1'|| count == '∞') ? -1 : parseInt(count) - 1;
      
      
      // Собираем все выбранные характеристики для записи в заказ.
      var prop = order.getPropPosition(obj);
   
      var variant = $('.block-variants tr td input:checked').val();
      variant  = variant ? variant : 0; 
      if ($('#add-order-wrapper .variants-table .c-variant__name').length) {
        var itemName = $('#order-data .title-sp').text() + ' ' + admin.trim($('.property-form input[name=variant]:checked').parents('tr').find(".c-variant__name").text());
      }
      else{
        var itemName = $('#order-data .title-sp').text() + ' ' + admin.trim($('.property-form input[name=variant]:checked').parents('tr').find("label").text());
      }
      var position = {
        order_id: $('#order-data .id-sp').text(),
        id: $('#order-data .id-sp').text(),
        title: '<a href="' + mgBaseDir + '/' + $('#order-data .url-sp').data('url') + '" data-url="' + $('#order-data .url-sp').data('url') + '" class="href-to-prod"><span class="titleProd">' + itemName + '</span></a>' + '<span class="property">' + prop + '</span>',
        prop: prop,
        code: $('#order-data .code-sp').text(),
        weight: $('#order-data .weight-sp').text(),
        price: $('#order-data .price-sp').text(),
        count: $('.product-block .amount_input').val(),
        summ: $('#order-data .price-sp').text().replace(/,/, '.').replace(/\s/, ''),
        url: $('#order-data .url-sp').data('url'),
        image_url: $('#order-data .image-sp img').attr('src'),
        fulPrice: $('#order-data .price-sp').text().replace(/,/, '.').replace(/\s/, ''),
        variant: variant,
        maxCount: maxCount, 
        notSet: $('#add-order-wrapper .save-button').attr('id') ? $('#order-data .id-sp').data('set') : true,
        category_unit: order.searchUnit,
        currency_iso: $('#add-order-wrapper .currSpan [name=userCurrency]').val(),
      };

      var row = order.createPositionRow(position);
      var update = false;

      // сравним добавляемую строку с уже имеющимися, возможно нужно только увеличить количество
      $('.status-table tbody#orderContent tr').each(function (i, element) {
        var title1 = $(this).find('.title').html().replace("<br>", "").replace(/\s/gi, "");
        var title2 = position.title.replace("<br>", "").replace(/\s/gi, "");

        if ($(this).data('id') == position.id && title1 == title2) {
          var count = $(this).find('.count input').val();
          $(this).find('.count input').val(count * 1 + 1);
          var max = parseInt ($(this).find('.count input').data('max'));
          if ((count * 1 + 1) > max + 1 && (max > 0)) {
            $(this).find('.count input').val(max + 1);
          }
          update = true;
        }
      });

      // если не обновляем, то добавляем новую строку
      if (!update) {
        $('.status-table tbody#orderContent').append(row);
      }
      
      var url = $('#order-data .url-sp').data('url');
      var urls = url.split('/');
      var orderItem = {
        id: position.id,
        title: itemName,
        price: position.price,
        weight: position.weight,
        count: position.count,
        url: urls.pop()
      };      
      
      order.orderItems.push(orderItem);
      order.calculateOrder();
      $('.fastResult').hide();
      $('input[name=searchcat]').val('');
    },
    //Клонирование заказа
    cloneOrder: function (id) {
      // получаем с сервера все доступные пользовательские параметры
      admin.ajaxRequest({
        mguniqueurl: "action/cloneOrder",
        id: id
      },
      function (response) {
        admin.indication(response.status, response.msg);
        admin.refreshPanel();
      }
      );
    },
    /**
     *Пакет выполняемых действий после загрузки раздела товаров
     */
    callbackOrders:function() {
      admin.sliderPrice();
      $('.section-order .to-date').datepicker({dateFormat: "dd.mm.yy"});
      $('.section-order .from-date').datepicker({dateFormat: "dd.mm.yy"});         
    },
  }
})();

// инициализация модуля при подключении
order.init();