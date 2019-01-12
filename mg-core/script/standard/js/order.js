$(document).ready(function() {

  $(".ui-autocomplete").css('z-index', '1000');
  $.datepicker.regional['ru'] = {
    closeText: 'Закрыть',
    prevText: '&#x3c;Пред',
    nextText: 'След&#x3e;',
    currentText: 'Сегодня',
    monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
      'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
    monthNamesShort: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн',
      'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],
    dayNames: ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'],
    dayNamesShort: ['вск', 'пнд', 'втр', 'срд', 'чтв', 'птн', 'сбт'],
    dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
    dateFormat: 'dd.mm.yy',
    firstDay: 1,
    isRTL: false
  };
  $.datepicker.setDefaults($.datepicker.regional['ru']);
  $('.delivery-date input[name=date_delivery]').datepicker({dateFormat: "dd.mm.yy", minDate: 0});

  if ($('input[name=toOrder]').prop("disabled")) {
    disabledToOrderSubmit(true);
  }

  if ($('.delivery-details-list input[name=delivery]:checked').val()) {
    disabledToOrderSubmit(false);
  }

  if ($('.payment-details-list input[name=payment]:checked').val()) {
    disabledToOrderSubmit(false);
  }
  var dataDelivery = $('.delivery-details-list input[name=delivery]:checked').parent().attr('data-delivery-date');
  if (dataDelivery == '1') {
    $('.delivery-date').show();
  }
  var intervalDelivery = $('.delivery-details-list input[name=delivery]:checked').parent().attr('data-delivery-intervals');
  if (intervalDelivery) {
    $('.delivery-interval [name=delivery_interval] option').remove();
    if (!$.isArray(intervalDelivery)) {
        intervalDelivery = intervalDelivery.replace('["',"").replace('"]',"").split('","');
      }
    for (var i = 0; i < intervalDelivery.length; i++) {
      if (intervalDelivery[i] != '') {
        intervalDelivery[i] = intervalDelivery[i].replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
        $('.delivery-interval [name=delivery_interval]').append("<option value='"+intervalDelivery[i]+"'>"+intervalDelivery[i]+"</option>");
      }
    }
    $('.delivery-interval').show();
  }
  var addressParts = $('.delivery-details-list input[name=delivery]:checked').parent().attr('data-delivery-address');
  if (addressParts && $.type(addressParts) == 'string') {
    addressParts = $.parseJSON(addressParts);
  }
  if (addressParts) {
    $('[name="address"]').hide();
    $('.addressPartsContainer').hide().find('input').hide();
    for (var i = 0; i < addressParts.length; i++) {
      if (addressParts[i] != '') {
        $('.addressPartsContainer [name=address_'+addressParts[i]+']').show().closest('.addressPartsContainer').show();
      }
    }
    $('.addressPartsTitle').show();
  }

  var deliverySumm = 0;

  // действия при оформлении заказа
  $('body').on('change', '.delivery-details-list input', function() {
    if ($(this).attr('type') == 'text') {return false;}
    $("p#auxiliary").html('');
    $('.delivery-details-list input[name=delivery]').parent().addClass('noneactive');
    $('.delivery-details-list input[name=delivery]').parent().removeClass('active');

    $('.delivery-details-list input[name=delivery]:checked').parent().removeClass('noneactive');
    $('.delivery-details-list input[name=delivery]:checked').parent().addClass('active');
    if ($('.delivery-details-list li .active').data('delivery-date') == '1') {
      $('.delivery-date').show();
    }
    else {
      $('.delivery-date').hide();
    }
    $('.delivery-interval [name=delivery_interval] option').remove();
    var intervalDelivery = $('.delivery-details-list li .active').data('delivery-intervals');
    if (intervalDelivery) {
      if (!$.isArray(intervalDelivery)) {
        intervalDelivery = intervalDelivery.replace('["',"").replace('"]',"").split('","');
      }
      for (var i = 0; i < intervalDelivery.length; i++) {
        if (intervalDelivery[i] != '') {
          intervalDelivery[i] = intervalDelivery[i].replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
          $('.delivery-interval [name=delivery_interval]').append("<option value='"+intervalDelivery[i]+"'>"+intervalDelivery[i]+"</option>");
        }
      }
      $('.delivery-interval').show();
    }
    else{
      $('.delivery-interval').hide();
    }
    var addressParts = $('.delivery-details-list li .active').data('delivery-address');
    if (addressParts) {
      $('[name="address"]').hide();
      $('.addressPartsContainer').hide().find('input').hide();
      for (var i = 0; i < addressParts.length; i++) {
        if (addressParts[i] != '') {
          $('.addressPartsContainer [name=address_'+addressParts[i]+']').show().closest('.addressPartsContainer').show();
        }
      }
      $('.addressPartsTitle').show();
    }
    else{
       $('[name="address"]').show();
       $('.addressPartsContainer').hide().find('.address-area-part').hide();
       $('.addressPartsTitle').hide();
    }
    var deliveryId = $('.delivery-details-list input[name=delivery]:checked').val();

    $('.payment-details-list').before('<div class="loader"></div>');
    disabledToOrderSubmit(true);
    $('.summ-info .delivery-summ').html('');
    $.ajax({
      type: "POST",
      url: mgBaseDir + "/order",
      data: {
        action: "getPaymentByDeliveryId",
        deliveryId: deliveryId,
        customer: $('.form-list select[name="customer"]').val(),
        lang: langP
      },
      dataType: "json",
      cache: false,
      success: function(response) {
        var paymentTable = response.paymentTable;
        deliverySumm = response.summDelivery;
        
        if ('' == paymentTable || null == paymentTable) {
          paymentTable = locale.paymentNone;
          disabledToOrderSubmit(false);
        }
        
        if(response.summDelivery < 0) {
          paymentTable = response.error;
          disabledToOrderSubmit(false);
        }
          
        $('.payment-details-list').html(paymentTable);
        $('.loader').remove();
        $('.payment-details-list input[name=payment]').prop("checked", false);
        if ($('.payment-details-list input[name=payment]').length == 1) {
          disabledToOrderSubmit(false);
          $('.payment-details-list input[name=payment]').trigger('click');
        }
        if (!response.error) {
          if(response.summDelivery != 0) {
            $('.summ-info .delivery-summ').html(locale.delivery+' <span class="order-delivery-summ">' + response.summDelivery + ' </span> ');
          }          
        } else {
          $('ul.payment-details-list').empty();
          $('ul.payment-details-list').append('<li>'+ response.error +'</li>');
        }
      }
    });

  });
  
  $('body').on('click', '.payment-details-list input', function() {
    var paymentId = $(this).val();
    
    $.ajax({
      type: "POST",
      url: mgBaseDir + "/order",
      data: {
        action: "setPaymentRate",
        paymentId: paymentId        
      },
      dataType: "json",
      cache: false,
      success: function(response) {
        $('.summ-info .order-summ:first span').text(response.summ);
        if(deliverySumm) {
          if(response.enableDeliveryCur == 'true') {
            num = parseFloat(deliverySumm.replace(',', '.').replace(/[^0-9\.]/g,''));
            sm = +num * (+1 + +response.rate);
            $('.order-delivery-summ').html(roundPlus(sm, 2)+' '+response.cur);
          }
        }        
      }
    });
  });

  $('.form-list select[name="customer"]').change(function() {
    if ($(this).val() == 'fiz') {
      $('.form-list.yur-field').hide();
      $('.payment-details-list input[name=payment]').parents('li').show();
      $('.payment-details-list input[name=payment][value=7]').parents('li').hide();
    }
    if ($(this).val() == 'yur') {
      $('.form-list.yur-field').show();
      $('.payment-details-list input[name=payment]').parents('li').hide();
      $('.payment-details-list input[name=payment][value=7]').parents('li').show();
    }

    $('.delivery-details-list input[name=delivery]:checked').click();

  });


  $('body').on('click', '.payment-details-list input[name=payment]:checked', function() {
    $("p#auxiliary").html('');
    $('.payment-details-list input[name=payment]').parent().addClass('noneactive');
    $('.payment-details-list input[name=payment]').parent().removeClass('active');
    $('.payment-details-list input[name=payment]:checked').parent().removeClass('noneactive');
    $('.payment-details-list input[name=payment]:checked').parent().addClass('active');
    disabledToOrderSubmit(false);
  });

  function disabledToOrderSubmit(flag) {
    if (!flag) {
      $('input[name=toOrder]').prop("disabled", false);
      $('input[name=toOrder]').removeClass('disabled-btn');
    } else {
      $('input[name=toOrder]').prop("disabled", true);
      $('input[name=toOrder]').addClass('disabled-btn');
    }
  }

  if ($('.payment-details-list input[name=payment]').length == 1) {
    $('.payment-details-list input[name=payment]').trigger('click');
  }

  function roundPlus(x, n) { 
    if(isNaN(x) || isNaN(n)) return false;
    var m = Math.pow(10,n);
    return Math.round(x*m)/m;
  }

}); 