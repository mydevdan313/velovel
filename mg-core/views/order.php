<?php
/**
 *  Файл представления Index - выводит сгенерированную движком информацию на главной странице магазина.
 *  В этом файле доступны следующие данные:
 *   <code>
 *    $data['active'] => состояние активации пользователя.
 *    $data['msg'] => сообщение.
 *    $data['step'] => стадия оформления заказа.
 *    $data['delivery'] => массив способов доставки.
 *    $data['paymentArray'] => массив способов оплаты.
 *    $data['paramArray'] => массив способов оплаты.
 *    $data['id'] => id заказа.
 *    $data['orderNumber'] => номер заказа.
 *    $data['summ'] => сумма заказа.
 *    $data['pay'] => оплата.
 *    $data['payMentView'] => файл представления дляспособа оплаты.
 *    $data['currency'] => валюта магазина
 *    $data['userInfo'] => информация о пользователе,
 *    $data['orderInfo'] => информация о заказе,
 *    $data['meta_title'] => 'Значение meta тега для страницы order'
 *    $data['meta_keywords'] => 'Значение meta_keywords тега для страницы order'
 *    $data['meta_desc'] => 'Значение meta_desc тега для страницы order'
 *   </code>
 *
 *   Получить подробную информацию о каждом элементе массива $data, можно вставив следующую строку кода в верстку файла.
 *   <code>
 *    <?php viewData($data['saleProducts']); ?>
 *   </code>
 *
 *   Вывести содержание элементов массива $data, можно вставив следующую строку кода в верстку файла.
 *   <code>
 *    <?php echo $data['saleProducts']; ?>
 *   </code>
 *
 *   <b>Внимание!</b> Файл предназначен только для форматированного вывода данных на страницу магазина. Категорически не рекомендуется выполнять в нем запросы к БД сайта или реализовывать сложую программную логику.
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Views
 */
?>
<?php mgAddMeta('<script src="' . SCRIPT . 'jquery.maskedinput.min.js"></script>'); ?>
<?php mgAddMeta('<script src="' . SCRIPT . 'standard/js/order.js"></script>'); ?>
<?php mgAddMeta('<link href="' . SCRIPT . 'standard/css/datepicker.css" rel="stylesheet" type="text/css">'); ?>

<div class="l-row">
    <?php if (!empty($data['fileToOrder'])) { ?>

        <div class="l-col min-0--12">
            <div class="c-title"><?php echo $data['fileToOrder']['infoMsg'] ?></div>
        </div>

        <?php if (!empty($data['fileToOrder']['electroInfo'])) { ?>
            <div class="l-col min-0--12">
                <ul class="c-history__list">
                    <?php foreach ($data['fileToOrder']['electroInfo'] as $item) { ?>
                        <li class="c-history__list--item">
                            <a class="c-history__list--link" href="<?php echo $item['link'] ?>">
                                <b><?php echo lang('orderDownload'); ?></b>
                                <?php echo $item['title'] ?>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        <?php
        }
    } else {switch ($data['step']) {case 1:

    mgSEO($data);
    $model = new Models_Cart();
    $cartData = $model->getItemsCart();
    $data['isEmpty'] = $model->isEmptyCart();
    $data['productPositions'] = $cartData['items'];
    $data['totalSumm'] = $cartData['totalSumm'];
    ?>

    <div class="l-col min-0--12">
        <div class="c-title"><?php echo lang('orderCheckout'); ?></div>
    </div>

    <?php if (class_exists('MinOrder')): ?>
        <div class="l-col min-0--12">
            [min-order]
        </div>
    <?php endif; ?>

    <div class="l-col min-0--12">
        <div class="product-cart" style="display:<?php echo !$data['isEmpty'] ? 'none' : 'block'; ?>">
           <div class="c-form cart-wrapper">
                <form method="post" action="<?php echo SITE ?>/cart" class="cart-form">
                    <div class="c-table">
                        <table class="cart-table">
                           <?php $i = 1;
                           foreach ($data['productPositions'] as $product): ?>
                               <tr>
                                   <td class="c-table__img img-cell">
                                       <a href="<?php echo $product["link"] ?>" target="_blank" class="cart-img">
                                           <img src="<?php echo mgImageProductPath($product["image_url"], $product['id'], 'small') ?>" alt="image">
                                       </a>
                                   </td>
                                   <td class="c-table__name name-cell">
                                       <a class="c-table__link" href="<?php echo $product["link"] ?>" target="_blank">
                                           <?php echo $product['title'] ?>
                                       </a>
                                       <br/><?php echo $product['property_html'] ?>
                                   </td>
                                   <td class="c-table__count count-cell">
                                        <div class="cart_form">
                                            <div class="c-amount amount_change">
                                                <a href="#" class="c-amount__up up">
                                                    <svg class="icon icon--arrow-right"><use xlink:href="#icon--arrow-right"></use></svg>
                                                </a>
                                                <input type="text" name="item_<?php echo $product['id'] ?>[]"
                                                       class="amount_input zeroToo" data-max-count="<?php echo $data['maxCount'] ?>"
                                                       value="<?php echo $product['countInCart'] ?>"/>
                                                <a href="#" class="c-amount__down down">
                                                    <svg class="icon icon--arrow-left"><use xlink:href="#icon--arrow-left"></use></svg>
                                                </a>
                                            </div>
                                        </div>
                                       <input type="hidden" name="property_<?php echo $product['id'] ?>[]" value="<?php echo $product['property'] ?>"/>
                                   </td>
                                   <td class="c-table__price     price-cell">
                                       <?php echo MG::numberFormat($product['countInCart'] * $product['price']) ?> <?php echo $data['currency']; ?>
                                   </td>
                                   <td class="c-table__remove     remove-cell">
                                       <a class="deleteItemFromCart delete-btn" href="<?php echo SITE ?>/cart"
                                          data-delete-item-id="<?php echo $product['id'] ?>"
                                          data-property="<?php echo $product['property'] ?>"
                                          data-variant="<?php echo $product['variantId'] ?>" title="<?php echo lang('orderRemoveProduct'); ?>">
                                              &nbsp;&nbsp;<div class="icon__cart">
                                              <svg class="icon icon--remove"><use xlink:href="#icon--remove"></use></svg>
                                          </div>&nbsp;&nbsp;
                                          </a>
                                   </td>
                               </tr>
                           <?php endforeach; ?>
                        </table>
                    </div>
                </form>
                <?php if ((class_exists('OikDisountCoupon')) || (class_exists('PromoCode'))): ?>
                    <div class="c-promo-code">
                        [promo-code]
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="c-alert c-alert--blue empty-cart-block alert-info" style="display:<?php echo !$data['isEmpty'] ? 'block' : 'none'; ?>">
            <?php echo lang('cartIsEmpty'); ?>
        </div>
    </div>
</div>

<div class="l-row">
    <div class="c-order checkout-form-wrapper" style="display:<?php echo $data['isEmpty'] ? 'block' : 'none'; ?>">
        <?php if ($data['msg']): ?>
            <div class="l-col min-0--12">
                <div class="c-alert c-alert--red mg-error">
                    <?php echo $data['msg'] ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="l-col min-0--12">
            <div class="payment-option">

                <form class="c-form" action="<?php echo SITE ?>/order?creation=1" method="post">
                    <div class="l-row">
                        <div class="l-col min-0--12 min-990--6">
                            <div class="c-order__title">1. <?php echo lang('orderContactData'); ?></div>
                            <ul class="c-order__list form-list">
                                <li class="c-order__list--item"><input type="text" name="email" placeholder="Email" value="<?php echo $_POST['email'] ?>"></li>
                                <li class="c-order__list--item"><input type="text" name="phone" placeholder="<?php echo lang('phone'); ?>" value="<?php echo $_POST['phone'] ?>"></li>
                                <li class="c-order__list--item"><input type="text" name="fio" placeholder="<?php echo lang('fio'); ?>" value="<?php echo $_POST['fio'] ?>"></li>
                                <li class="c-order__list--item"><input type="text" class="address-area" placeholder="<?php echo lang('orderPhAdres'); ?>" name="address" value="<?php echo $_POST['address'] ?>"></li>
                                <li class="c-order__list--width"><textarea class="address-area" placeholder="<?php echo lang('orderPhComment'); ?>" name="info"><?php echo $_POST['info'] ?></textarea></li>                                
                                <?php layout('op_fields'); ?>
                                <li class="c-order__list--width c-order__title--small">
                                <?php echo lang('orderPayer'); ?></li>
                                <li class="c-order__list--width c-order__list--item">
                                    <select name="customer">
                                        <?php $selected = $_POST['customer'] == "yur" ? 'selected' : ''; ?>
                                        <option value="fiz"><?php echo lang('orderFiz'); ?></option>
                                        <option value="yur" <?php echo $selected ?>><?php echo lang('orderYur'); ?></option>
                                    </select>
                                </li>
                            </ul>
                            <?php if ($_POST['customer'] != "yur") {
                                $style = 'style="display:none"';
                            } ?>
                            <ul class="c-order__list form-list yur-field" <?php echo $style ?>>
                                <li class="c-order__list--item"><input type="text" name="yur_info[nameyur]" placeholder="<?php echo lang('orderPhNameyur'); ?>" value="<?php echo $_POST['yur_info']['nameyur'] ?>"></li>
                                <li class="c-order__list--item"><input type="text" name="yur_info[adress]" placeholder="<?php echo lang('orderPhAdress'); ?>" value="<?php echo $_POST['yur_info']['adress'] ?>"></li>
                                <li class="c-order__list--item"><input type="text" name="yur_info[inn]" placeholder="<?php echo lang('orderPhInn'); ?>" value="<?php echo $_POST['yur_info']['inn'] ?>"></li>
                                <li class="c-order__list--item"><input type="text" name="yur_info[kpp]" placeholder="<?php echo lang('orderPhKpp'); ?>" value="<?php echo $_POST['yur_info']['kpp'] ?>"></li>
                                <li class="c-order__list--item"><input type="text" name="yur_info[bank]" placeholder="<?php echo lang('orderPhBank'); ?>" value="<?php echo $_POST['yur_info']['bank'] ?>"></li>
                                <li class="c-order__list--item"><input type="text" name="yur_info[bik]" placeholder="<?php echo lang('orderPhBik'); ?>" value="<?php echo $_POST['yur_info']['bik'] ?>"></li>
                                <li class="c-order__list--item"><input type="text" name="yur_info[ks]" placeholder="<?php echo lang('orderPhKs'); ?>" value="<?php echo $_POST['yur_info']['ks'] ?>"></li>
                                <li class="c-order__list--item"><input type="text" name="yur_info[rs]" placeholder="<?php echo lang('orderPhRs'); ?>" value="<?php echo $_POST['yur_info']['rs'] ?>"></li>
                            </ul>

                            
                        </div>

                        <div class="l-col min-0--12 min-768--6 min-990--3">
                            <div class="c-order__title">2. <?php echo lang('orderDelivery'); ?></div>
                            <?php if ('' != $data['delivery']): ?>
                                <ul class="c-order__payment delivery-details-list">
                                    <?php foreach ($data['delivery'] as $delivery): ?>
                                        <li <?php echo ($delivery['checked']) ? 'class = "active"' : 'class = "noneactive"' ?>>
                                            <label data-delivery-date="<?php echo $delivery['date']; ?>" data-delivery-intervals='<?php echo $delivery["interval"]; ?>'>
                                                <input type="radio"
                                                       name="delivery" <?php if ($delivery['checked']) echo 'checked' ?>
                                                       value="<?php echo $delivery['id'] ?>">
                                                <span class="deliveryName"><?php echo $delivery['description'] ?></span>
                                                <?php 
                                                  if ($delivery['cost'] != 0 || DELIVERY_ZERO == 1) {
                                                    $deliveryCostShow = true;
                                                  } else {
                                                    $deliveryCostShow = false;
                                                  }
                                                ?>
                                                <span class="deliveryPrice" style="<?php echo $deliveryCostShow?'':'display:none;'; ?>">&nbsp;<?php echo MG::numberFormat($delivery['cost']); ?></span>
                                                <span class="deliveryCurrency" style="<?php echo $deliveryCostShow?'':'display:none;'; ?>"><?php echo '&nbsp;' . $data['currency']; ?></span>
                                            </label>
                                            <!--
                                            Для способов доставки с автоматическим расчетом стоимости, добавленных из плагинов.
                                            Проверяем наличие шорткода у способа доставки, и выводим его в специальный блок при наличии
                                            -->
                                            <?php if (!empty($delivery['plugin'])): ?>
                                                <?php echo '[' . $delivery['plugin'] . ']'; ?>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <div class="delivery-date" style="display:none;">
                                <div class="c-order__title--small"><?php echo lang('orderDeliveryDate'); ?>:</div>
                                <input type='text' name='date_delivery' placeholder='<?php echo lang('orderDeliveryDate'); ?>'
                                       value="<?php echo $_POST['date_delivery'] ?>">
                            </div>
                            <div class="delivery-interval" style="display:none;">
                                <div class="c-order__title--small"><?php echo lang('orderDeliveryInterval'); ?>:</div>
                                <select name="delivery_interval"></select>
                            </div>
                            <!-- склады -->
                            <?php MG::checkProductOnStorage(); ?>
                        </div>

                        <div class="l-col min-0--12 min-768--6 min-990--3">
                            <div class="c-order__title">3. <?php echo lang('orderPaymentMethod'); ?></div>
                            <ul class="c-order__payment payment-details-list">
                                <?php if (count($data['delivery']) > 1 && !$_POST['payment']): ?>
                                    <li>
                                        <div class="c-alert c-alert--blue">
                                            <?php echo lang('orderPaymentNoDeliv'); ?>
                                        </div>
                                    </li>
                                <?php elseif ('' != $data['paymentArray']): ?>
                                    <?php echo $data['paymentArray'] ?>
                                <?php else:
                                    ?>
                                    <li>
                                        <div class="c-alert c-alert--orange">
                                            <?php echo lang('orderPaymentNone'); ?>
                                        </div>
                                    </li>
                                <?php endif; ?>
                            </ul>
                            
                       </div>
                        <div class="l-col min-0--12">
                            <div class="total-price-block total">
                                <div class="c-order__title c-order__title--last">4. <?php echo lang('orderPaymentTotal'); ?></div>
                                <div class="c-order__total">
                                    <div class="c-order__total--row">
                                        <div class="c-order__total--amount summ-info">
                                            <span class="order-summ total-sum"><span><?php echo $data['summOrder'] ?></span></span>
                                            <span class="delivery-summ"><?php echo $data['deliveryInfo'] ?></span>
                                        </div>
                                    </div>
                                    <?php if ($data['captcha'] && !$data['recaptcha']) { ?>
                                        <div class="checkCapcha" style="display:inline-block">
                                            <img src="captcha.html" width="140" height="36">
                                            <div class="capcha-text"><?php echo lang('captcha'); ?><span class="red-star">*</span>
                                            </div>
                                            <input type="text" name="capcha" class="captcha">
                                        </div>
                                    <?php } ?>
                                    <?php if ($data['recaptcha']) { ?>
                                        <div class="checkCapcha" style="display:inline-block">
                                            <?php echo MG::printReCaptcha(); ?>
                                        </div>
                                    <?php } ?>
                                    <div class="c-order__total--row">
                                        <?php if (class_exists('PersonalData')): ?>
                                          <div class="PersonalData">
                                              [personal-data]
                                          </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php
                                       echo MG::addAgreementCheckbox(
                                         'checkout-btn', 
                                           array(
                                             'text' => 'Я даю согласие на обработку моих ', 
                                             'textLink' => 'персональных данных.'
                                           ) 
                                       );
                                  ?>
                                    <div class="c-order__total--row">
                                        <form action="<?php echo SITE ?>/order" method="post" class="checkout-form">
                                            <input type="submit" name="toOrder" class="checkout-btn default-btn success" value="<?php echo lang('checkout'); ?>" disabled>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="l-row">

    <?php break; case 2: $data['meta_title'] = lang('orderPayment'); mgSEO($data); if ($data['msg']): ?>
        <div class="l-col min-0--12">
            <div class="c-alert c-alert--red errorSend">
                <?php echo $data['msg'] ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="l-col min-0--12">
        <div class="c-title"><?php echo lang('orderPayment'); ?></div>
    </div>

    <?php if (!$data['pay'] && $data['payment'] == 'fail'): ?>
        <div class="l-col min-0--12">
            <div class="c-alert c-alert--red payment-form-block">
                <?php echo $data['message']; ?>
            </div>
        </div>
    <?php else: ?>

        <div class="payment-form-block">
            <div class="l-col min-0--12">
                <div class="c-alert c-alert--green">
                    <?php echo lang('orderPaymentForm1'); ?> <strong>№ <?php echo $data['orderNumber'] ?></strong> <?php echo lang('orderPaymentForm2'); ?>
                </div>
            </div>
            <div class="l-col min-0--12">
                <div class="c-alert c-alert--blue">
                    <p><?php echo lang('orderPaymentForm3'); ?></p>
                    <p><?php echo lang('orderPaymentForm4'); ?> <b>№ <?php echo $data['orderNumber'] ?></b> <?php echo lang('orderPaymentForm5'); ?> <b><?php echo MG::numberFormat($data['summ']) ?></b> <?php echo $data['currency']; ?></p>
                </div>
            </div>
        </div>

        <div class="l-col min-0--12">
            <?php endif;

            if ($data['payMentView']) {include($data['payMentView']); } elseif ($data['pay'] == 12 || $data['pay'] == 13) { ?>
                <div class="c-alert c-alert--blue">
                    <?php echo lang('orderPaymentView1'); ?> <b><?php echo $data['paramArray'][0]['name'] ?></b>: <b>
                    <?php echo $data['paramArray'][0]['value'] ?></b><?php echo lang('orderPaymentView2'); ?>
                 </div>
            <?php }

            break; case 3: $data['meta_title'] = lang('orderPaymentConfirmTitle'); mgSEO($data); if ($data['msg']): ?>
        </div>

        <div class="l-col min-0--12">
            <div class="c-alert c-alert--green text-success">
                <?php echo $data['msg'] ?>
            </div>
        </div>

        <?php endif;

        if ($data['id']): ?>
            <div class="l-col min-0--12">
                <div class="c-title"><?php echo lang('orderPaymentConfirmTitle'); ?></div>
            </div>
            <div class="l-col min-0--12">
                <div class="c-alert c-alert--green auth-text"><?php echo lang('orderPaymentConfirm1'); ?><?php echo $data['orderNumber'] ?> <?php echo lang('orderPaymentConfirm2'); ?></div>
            </div>
        <?php endif;

        //если пользователь не активизирован, то показываем форму задания пароля
        if ($data['active']): ?>

        <div class="l-col min-0--12">
            <div class="c-alert c-alert--green text-success">
                <?php echo lang('orderPaymentRegister1'); ?> <strong><?php echo SITE ?></strong> <?php echo lang('orderPaymentRegister2'); ?>
            </div>
        </div>
        <div class="l-col min-0--12">
            <div class="c-alert c-alert--blue get-login">
                <?php echo lang('orderPaymentRegister3'); ?> <strong><?php echo $data['active'] ?></strong>.
            </div>
        </div>
        <div class="user-login">
            <div class="l-col min-0--12">
                <div class="c-alert c-alert--blue custom-text"><?php echo lang('orderPaymentRegister4'); ?></div>
            </div>
            <div class="l-col min-0--12">
                <form class="c-form c-form--width" action="<?php echo SITE ?>/forgotpass" method="POST">
                    <div class="c-form__row">
                        <input type="password" name="newPass" placeholder="<?php echo lang('forgotPass1'); ?>" required>
                    </div>
                    <div class="c-form__row">
                        <input type="password" name="pass2" placeholder="<?php echo lang('forgotPass2'); ?>" required>
                    </div>
                    <div class="c-form__row">
                        <input type="submit" class="c-button" name="chengePass" value="<?php echo lang('save'); ?>">
                    </div>
                </form>
            </div>
        </div>

        <?php endif; break; case 4: ?>

        <div class="l-col min-0--12">
            <div class="c-alert c-alert--blue">
                <?php echo lang('orderPay1'); ?> <?php echo $data['orderNumber'] ?> <?php echo lang('orderPay2'); ?> <?php echo MG::numberFormat($data['summ']) ?> <?php echo $data['currency'] ?>
            </div>
        </div>

        <div class="l-col min-0--12">
            <?php if ($data['payMentView']) {include($data['payMentView']); } elseif ($data['pay'] == 12 || $data['pay'] == 13) { ?>
        </div>

        <div class="l-col min-0--12">
            <div class="c-alert c-alert--blue">
                <?php echo lang('orderPay3'); ?> <b><?php echo $data['paramArray'][0]['value'] ?></b><?php echo lang('orderPay4'); ?>
            </div>
        </div>

        <?php } else {?>

        <div class="l-col min-0--12">
            <div class="c-alert c-alert--blue">
                <?php echo lang('orderPay5'); ?>
                <br>
                <?php echo lang('orderPay6'); ?>
            </div>
        </div>

        <?php } break; case 5: ?>

        <div class="l-col min-0--12">
            <div class="c-title"><?php echo lang('orderStatus'); ?></div>
        </div>

        <?php if ($data['msg']) { ?>
            <div class="l-col min-0--12">
                <div class="c-alert c-alert--orange errorSend">
                    <?php echo $data['msg']; ?>
                </div>
            </div>
        <?php } else {$order = $data['orderInfo'][$data['id']]; ?>

        <div class="l-col min-0--12">
            <div class="c-history__item order-history" id="<?php echo $order['id'] ?>">
                <div class="c-history__header order-number">
                    <div class="c-history__header--left">
                        <strong><?php echo $order['number']!=''?$order['number']:$order['id'] ?></strong> от <?php echo date('d.m.Y', strtotime($order['add_date'])) ?>
                    </div>
                    <div class="c-history__header--right">
                        <span class="order-status">
                            <span class="c-history__status <?php echo $data['assocStatusClass'][$order['status_id']]?>"><?php echo $order['string_status_id'] ?></span>
                        </span>
                    </div>
                </div>
                <div class="c-history__content">
                    <div class="c-history__content--top">
                        <div class="c-table c-table--hover c-history__table">
                            <table class="status-table">
                                <?php
                                    $perOrder['currency_iso'] = $perOrder['currency_iso']?$perOrder['currency_iso']:$currencyShopIso;
                                    $perCurrencyShort = MG::getSetting('currency');
                                    $perOrders = unserialize(stripslashes($order['order_content']));
                                ?>
                                <?php if(!empty($perOrders)) foreach($perOrders as $perOrder): ?>
                                    <?php
                                    $perCurrencyShort = $currencyShort[$perOrder['currency_iso']]?$currencyShort[$perOrder['currency_iso']]:MG::getSetting('currency');
                                    $coupon = $perOrder['coupon'];
                                ?>
                                <tr>
                                    <td>
                                        <a class="c-history__table--title" href="<?php echo $perOrder['url'] ?>" target="_blank">
                                            <?php echo $perOrder['name'] ?>
                                            <?php echo htmlspecialchars_decode(str_replace('&amp;', '&', $perOrder['property'])) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="c-history__table--code">
                                            Код: <?php echo $perOrder['code'] ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="c-history__table--price">
                                            <?php echo MG::numberFormat(($perOrder['price'])).'  '.$perCurrencyShort; ?>/шт.
                                        </div>
                                    </td>
                                    <td>
                                        <div class="c-history__table--quantity">
                                            <?php echo $perOrder['count'] ?> шт.
                                        </div>
                                    </td>
                                    <td>
                                        <div class="c-history__table--total">
                                            <?php echo MG::numberFormat(($perOrder['price']*$perOrder['count'])).'  '.$perCurrencyShort; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    </div>
                    <div class="c-history__content--left">
                        <?php if($order['status_id']==2||$order['status_id']==5): ?>
                            <div class="c-history__row">
                                <a class="c-history__download download-link" href="<?php echo SITE.'/order?getFileToOrder='.$order['id'] ?>">
                                    <svg class="icon icon--download"><use xlink:href="#icon--download"></use></svg>
                                    <?php echo lang('orderDownloadDigital'); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php $yurInfo = unserialize(stripslashes($order['yur_info'])); if(!empty($yurInfo['inn'])): ?>
                            <div class="c-history__row">
                                <a class="c-history__download download-link" href="<?php echo SITE.'/order?getOrderPdf='.$order['id'] ?>">
                                    <svg class="icon icon--download"><use xlink:href="#icon--download"></use></svg>
                                    <?php echo lang('orderDownloadPdf'); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if($order['status_id']<2): ?>
                            <div class="order-settings">
                                <div class="c-history__row">
                                    <button class="c-button c-button--border close-order" id="<?php echo $order['id'] ?>" date="<?php echo date('d.m.Y', strtotime($order['add_date'])) ?>" data-number="<?php echo $order['number']!=''?$order['number']:$order['id']; ?>" href="#openModal">
                                        <?php echo lang('orderCancel'); ?>
                                    </button>
                                </div>
                                <div class="c-history__row">
                                    <button class="c-button c-button--border change-payment" id="<?php echo $order['id'] ?>" date="<?php echo date('d.m.Y', strtotime($order['add_date'])) ?>" data-number="<?php echo $order['number']!=''?$order['number']:$order['id']; ?>" href="#changePayment">
                                        <?php echo lang('orderChangePayment'); ?>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if(!empty($order['comment'])): ?>
                            <div class="c-history__row">
                                <div class="c-alert c-alert--blue">
                                    <?php echo $order['comment']; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="c-history__content--right">
                        <div class="order-total">
                            <ul class="c-history__list total-list">
<?php viewData($order) ?>
                                <?php if($coupon): ?>
                                <li class="c-history__list--item">
                                    <b><?php echo lang('orderFinalCoupon'); ?></b> <span title="<?php echo $coupon ?>"><?php echo MG::textMore($coupon, 20) ?></span>
                                </li>
                                <?php endif; ?>

                                <li class="c-history__list--item">
                                    <b><?php echo lang('orderFinalTotal'); ?>\</b> <span class="total-summ"><?php echo MG::numberFormat($order['summ']).'  '.$perCurrencyShort ?></span>
                                </li>

                                <?php if($order['description']): ?>
                                <li class="c-history__list--item">
                                    <b><?php echo lang('orderFinalDeliv'); ?></b> <span><?php echo $order['description'] ?></span>
                                </li>

                                <?php if($order['date_delivery']): ?>
                                <li class="c-history__list--item">
                                    <b><?php echo lang('orderFinalDelivDate'); ?></b> <span><?php echo date('d.m.Y', strtotime($order['date_delivery'])) ?></span>
                                </li>
                                <?php endif; ?>
                                <?php endif; ?>

                                <li class="c-history__list--item">
                                    <b><?php echo lang('orderFinalPayment'); ?></b> <span class="paymen-name-to-history"><?php echo $order['name'] ?></span>
                                </li>

                                <?php $totSumm = $order['summ']+$order['delivery_cost']; ?>
                                <?php if($order['delivery_cost']): ?>
                                    <li class="c-history__list--item">
                                        <b><?php echo lang('orderFinalDeliv'); ?></b> <span class="delivery-price"><?php echo MG::numberFormat($order['delivery_cost']).'  '.$perCurrencyShort; ?></span>
                                    </li>
                                <?php endif; ?>

                                <li class="c-history__list--item c-history__list--total">
                                    <b><?php echo lang('orderFinalPay'); ?></b> <span class="total-order-summ"><?php echo MG::numberFormat($totSumm).'  '.$perCurrencyShort; ?></span>
                                </li>

                                <?php if(2>$order['status_id']): ?>
                                <li class="c-history__list--item">
                                    <div class="order-settings">
                                        <form class="c-form" method="POST" action="<?php echo SITE ?>/order">
                                            <input type="hidden" name="orderID" value="<?php echo $order['id'] ?>">
                                            <input type="hidden" name="orderSumm" value="<?php echo $order['summ'] ?>">
                                            <input type="hidden" name="paymentId" value="<?php echo $order['payment_id'] ?>">
                                            <?php if($order['payment_id']!=3): ?>
                                            <button type="submit" class="c-button" name="pay" value="go"><?php echo lang('orderFinalButton'); ?></button>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } } } ?>
</div>