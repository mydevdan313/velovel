<?php
/**
 *  Файл представления Cart - выводит сгенерированную движком информацию на странице сайта с корзиной товаров.
 *  В этом  файле доступны следующие данные:
 *   <code>
 *    $data['isEmpty'] => 'Флаг наполненности корзины'
 *    $data['productPositions'] => 'Набор продуктов в корзине'
 *    $data['totalSumm'] => 'Общая стоимость товаров в корзине'
 *    $data['meta_title'] => 'Значение meta тега для страницы '
 *    $data['meta_keywords'] => 'Значение meta_keywords тега для страницы '
 *    $data['meta_desc'] => 'Значение meta_desc тега для страницы '
 *    $data['currency'] => 'Текущая валюта магазина',
 *    $data['related'] => 'Товары с которыми покупают данные товары',
 *   </code>
 *
 *   Получить подробную информацию о каждом элементе массива $data, можно вставив следующую строку кода в верстку файла.
 *   <code>
 *    <?php viewData($data['productPositions']); ?>
 *   </code>
 *
 *   Вывести содержание элементов массива $data, можно вставив следующую строку кода в верстку файла.
 *   <code>
 *    <?php echo $data['productPositions']; ?>
 *   </code>
 *
 *   <b>Внимание!</b> Файл предназначен только для форматированного вывода данных на страницу магазина. Категорически не рекомендуется выполнять в нем запросы к БД сайта или реализовывать сложную программную логику логику.
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Views
 */
// Установка значений в метатеги title, keywords, description.
mgSEO($data);
?>

<?php mgTitle(lang('cart')); ?>

<div class="l-row">
    <div class="l-col min-0--12">
        <div class="c-title"><?php echo lang('productCart'); ?></div>
    </div>

    <?php if (class_exists('MinOrder')): ?>
        <div class="l-col min-0--12">
            [min-order]
        </div>
    <?php endif; ?>

    <div class="l-col min-0--12">
        <div class="product-cart" style="display:<?php echo $data['isEmpty'] ? 'block' : 'none'; ?>">
           <div class="c-form cart-wrapper">
               <form class="cart-form" method="post" action="<?php echo SITE ?>/cart">
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
                                        <br>
                                        <?php echo $product['property_html'] ?>
                                   </td>
                                   <td class="c-table__count count-cell">
                                       <div class="cart_form">
                                            <div class="c-amount amount_change">
                                                <a href="#" class="c-amount__up up">
                                                    <svg class="icon icon--arrow-right"><use xlink:href="#icon--arrow-right"></use></svg>
                                                </a>
                                                <input type="text" name="item_<?php echo $product['id'] ?>[]"
                                                       class="amount_input zeroToo"
                                                       data-max-count="<?php echo $data['maxCount'] ?>"
                                                       value="<?php echo $product['countInCart'] ?>"/>
                                                <a href="#" class="c-amount__down down">
                                                    <svg class="icon icon--arrow-left"><use xlink:href="#icon--arrow-left"></use></svg>
                                                </a>
                                            </div>
                                        </div>
                                       <input type="hidden" name="property_<?php echo $product['id'] ?>[]" value="<?php echo $product['property'] ?>"/>
                                   </td>
                                   <td class="c-table__price price-cell">
                                       <?php echo MG::numberFormat($product['countInCart'] * $product['price']) ?> <?php echo $data['currency']; ?>
                                   </td>
                                   <td class="c-table__remove remove-cell">
                                       <a class="deleteItemFromCart delete-btn" href="<?php echo SITE ?>/cart"
                                          data-delete-item-id="<?php echo $product['id'] ?>"
                                          data-property="<?php echo $product['property'] ?>"
                                          data-variant="<?php echo $product['variantId'] ?>" title="<?php echo lang('deleteProduct'); ?>">
                                          <div class="icon__cart">
                                              <svg class="icon icon--remove"><use xlink:href="#icon--remove"></use></svg>
                                          </div>
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

               <div class="c-table__footer total-price-block">
                   <div class="c-table__total">
                       <span class="title"><?php echo lang('toPayment'); ?>:</span>
                       <span class="total-sum">
                           <strong> <?php echo priceFormat($data['totalSumm']) ?>&nbsp;<?php echo $data['currency']; ?></strong>
                       </span>
                   </div>
                   <form action="<?php echo SITE ?>/order" method="post" class="checkout-form">
                       <button type="submit" class="checkout-btn default-btn success" name="order" value="<?php echo lang('checkout'); ?>"><?php echo lang('checkout'); ?></button>
                   </form>
               </div>
           </div>
            <?php echo $data['related'] ?>
        </div>
        <div class="c-alert c-alert--blue empty-cart-block alert-info" style="display:<?php echo !$data['isEmpty'] ? 'block' : 'none'; ?>">
            <?php echo lang('cartIsEmpty'); ?>
        </div>
    </div>
</div>