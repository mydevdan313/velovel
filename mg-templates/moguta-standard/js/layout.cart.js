$(document).ready(function() {
    // Заполнение корзины аяксом
    $('body').on('click', '.addToCart', function() {

        var productId = $(this).data('item-id');
        transferEffect(productId, $(this), '.product-wrapper');

        if ($(this).parents('.property-form').length) {
            var request = $(this).parents('.property-form').formSerialize();
        } else {
            var request = 'inCartProductId=' + $(this).data('item-id') + "&amount_input=1";
        }


        $.ajax({
            type: "POST",
            url: mgBaseDir + "/cart",
            data: "updateCart=1&" + request,
            dataType: "json",
            cache: false,
            success: function(response) {

                $('.small-cart').show();

                if ($('#c-modal__cart').length > 0) {
                    $('#c-modal__cart').addClass('c-modal--open');
                    if ($(document).height() > $(window).height()) {
                        $('html').addClass('c-modal--scroll');
                    }
                }

                if ('success' == response.status) {
                    dataSmalCart = '';
                    response.data.dataCart.forEach(printSmalCartData);
                    $('.small-cart-table').html(dataSmalCart);
                    $('.total .total-sum span').text(response.data.cart_price_wc);
                    $('.pricesht').text(response.data.cart_price);
                    $('.countsht').text(response.data.cart_count);
                }
            }
        });

        return false;
    });

    // Удаление вещи из корзины аяксом
    $('body').on('click', '.deleteItemFromCart', function() {

        var $this = $(this);
        var itemId = $this.data('delete-item-id');
        var property = $this.data('property');
        var $vari = $this.data('variant');
        $.ajax({
            type: "POST",
            url: mgBaseDir + "/cart",
            data: {
                action: "cart", // название действия в пользовательском класса Ajaxuser
                delFromCart: 1,
                itemId: itemId,
                property: property,
                variantId: $vari
            },
            dataType: "json",
            cache: false,
            success: function(response) {
                if ('success' == response.status) {
                    if (response.deliv && response.curr) {
                        var i = 0;
                        response.deliv.forEach(function(element, index, arr) {
                          $('.delivery-details-list li:eq('+i+') .deliveryPrice').html('&nbsp;'+element);
                          if ($('.delivery-details-list input[type=radio]:eq('+i+')').is(':checked')) {
                            if (element == 0) {
                              $('.summ-info .delivery-summ').html('');
                            }
                            else{
                              $('.summ-info .delivery-summ').html(locale.delivery+' <span class="order-delivery-summ">'+element+' '+response.curr+'</span>');
                            }
                          }
                          i++;
                        });
                      }
                    var table = $('.deleteItemFromCart[data-property="' + property + '"][data-delete-item-id="' + itemId + '"][data-variant="' + $vari + '"]').parents('table');
                    $('.deleteItemFromCart[data-property="' + property + '"][data-delete-item-id="' + itemId + '"][data-variant="' + $vari + '"]').parents('tr').remove();
                    var i = 1;
                    table.find('.index').each(function() {
                        $(this).text(i++);
                    });
                    $('.total-sum strong,.total .total-sum span,.mg-desktop-cart .total-sum span,.mg-fake-cart .total-sum span').text(response.data.cart_price_wc);
                    response.data.cart_price = response.data.cart_price ? response.data.cart_price : 0;
                    response.data.cart_count = response.data.cart_count ? response.data.cart_count : 0;
                    $('.pricesht').text(response.data.cart_price);
                    $('.countsht').text(response.data.cart_count);
                    $('.cart-table .total-sum-cell strong').text(response.data.cart_price_wc);

                    if ($('.small-cart-table tr').length == 0) {

                        $('html').removeClass('c-modal--scroll');
                        $('#c-modal__cart').removeClass('c-modal--open');
                        $('.product-cart, .checkout-form-wrapper, .small-cart').hide();
                        $('.empty-cart-block').show();

                    }
                }
            }
        });

        return false;
    });

    // строит содержимое маленькой корзины в выпадащем блоке
    function printSmalCartData(element, index, array) {

        dataSmalCart += '<tr>\
                <td class="c-table__img     small-cart-img">\
                    <a href="' + mgBaseDir + '/' + ((element.category_url || element.category_url == '') ? element.category_url : 'catalog/') +
            element.product_url + '"><img src="' + element.image_url_new + '" alt="' +
            element.title + '" alt="" /></a>\
                </td>\
                <td class="c-table__name     small-cart-name">\
                    <ul class="small-cart-list">\
                        <li><a class="c-table__link" href="' + mgBaseDir + '/' + ((element.category_url || element.category_url == '') ? element.category_url : 'catalog/') +
            element.product_url + '">' + element.title + '</a><span class="property">' +
            element.property_html + '</span></li>\
                        <li class="c-table__quantity     qty">x' + element.countInCart + ' <span>' +
            element.priceInCart + '</span></li>\
                    </ul>\
                </td>\
                <td class="c-table__remove     small-cart-remove"><a href="#" class="deleteItemFromCart" title="'+locale.cartRemove+'" data-delete-item-id="' + element.id +
            '" data-property="' + element.property +
            '" data-variant="' + element.variantId +
            '">&nbsp;&nbsp;<div class="icon__cart-remove"><svg class="icon icon--remove"><use xlink:href="#icon--remove"></use></svg>&nbsp;&nbsp;</div></a></td>\
            </tr>';
    }

    if ($('.small-cart-table tr').length == 0) {

        $('.product-cart, .checkout-form-wrapper, .small-cart').hide();
        $('.empty-cart-block').show();

    };

});