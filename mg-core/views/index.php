<?php
/**
 *  Файл представления Index - выводит сгенерированную движком информацию на главной странице магазина.
 *  В этом файле доступны следующие данные:
 *   <code>
 *    $data['recommendProducts'] => Массив рекомендуемых товаров
 *    $data['newProducts'] => Массив товаров новинок
 *    $data['saleProducts'] => Массив товаров распродажи
 *    $data['titeCategory'] => Название категории
 *    $data['cat_desc'] => Описание категории
 *    $data['meta_title'] => Значение meta тега для страницы
 *    $data['meta_keywords'] => Значение meta_keywords тега для страницы
 *    $data['meta_desc'] => Значение meta_desc тега для страницы
 *    $data['currency'] => Текущая валюта магазина
 *    $data['actionButton'] => тип кнопки в мини карточке товара
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
 *   <b>Внимание!</b> Файл предназначен только для форматированного вывода данных на страницу магазина. Категорически не рекомендуется выполнять в нем запросы к БД сайта или реализовывать сложную программную логику логику.
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Views
 */
// Установка значений в метатеги title, keywords, description.
mgSEO($data);
//viewData($data['newProducts']);
?>

<div class="l-row">
    <?php if (class_exists('trigger')): ?>
    [trigger-guarantee id="1"]
    <?php endif ?>
    <!-- new - start -->
    <?php if (!empty($data['newProducts'])): ?>
        <div class="l-col min-0--12">
            <div class="c-carousel c-carousel--first">
                <div class="c-carousel__title">
                    <a href="<?php echo SITE; ?>/group?type=latest">
                        <span class="c-carousel__title--span">
                            <?php echo lang('indexNew'); ?> <span class="c-carousel__title--more"><?php echo lang('indexViewAll'); ?></span>
                        </span>
                    </a>
                </div>
                <div class="<?php echo count($data['newProducts']) > 0 ? "c-carousel__content" : "" ?>">
                    <?php foreach ($data['newProducts'] as $item) {
                        $data['item'] = $item;
                        layout('mini_product', $data);
                    } ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <!-- new - end -->

   <!--  blok editor start -->
   <?php if (class_exists('SiteBlockEditor')): ?>
    <div class="site-blocks l-col">
        [site-block id=1]
        [site-block id=2]
        [site-block id=3]
    </div>
   <?php endif ?>
   <!--  blok editor end -->    
    <!-- hit - start -->
    <?php if (!empty($data['recommendProducts'])): ?>
        <div class="l-col min-0--12">
            <div class="c-carousel">
                <div class="c-carousel__title">
                    <a href="<?php echo SITE; ?>/group?type=recommend">
                        <span class="c-carousel__title--span">
                            <?php echo lang('indexHit'); ?> <span class="c-carousel__title--more"><?php echo lang('indexViewAll'); ?></span>
                        </span>
                    </a>
                </div>
                <div class="<?php echo count($data['recommendProducts']) > 0 ? "c-carousel__content" : "" ?>">
                    <?php foreach ($data['recommendProducts'] as $item) {
                        $data['item'] = $item;
                        layout('mini_product', $data);
                    } ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <!-- hit - end -->
    <!-- sales - start -->
    <?php if (!empty($data['saleProducts'])): ?>
        <div class="l-col min-0--12">
            <div class="c-carousel">
                <div class="c-carousel__title">
                    <a href="<?php echo SITE; ?>/group?type=sale">
                        <span class="c-carousel__title--span">
                            <?php echo lang('indexSale'); ?> <span class="c-carousel__title--more"><?php echo lang('indexViewAll'); ?></span>
                        </span>
                    </a>
                </div>
                <div class="<?php echo count($data['saleProducts']) > 0 ? "c-carousel__content" : "" ?>">
                    <?php foreach ($data['saleProducts'] as $item) {
                        $data['item'] = $item;
                        layout('mini_product', $data);
                    } ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <!-- sales - end -->
    <!-- brand - start -->
    <?php if (class_exists('brand')): ?>
        <div class="l-col min-0--12 max-767--hide">
            <div class="mg-brand c-carousel ">
                <div class="c-carousel__title">
                    <span class="c-carousel__title--span"><?php echo lang('indexBrand'); ?></span>
                </div>
                [brand]
            </div>
        </div>
    <?php endif; ?>
    <!-- brand - end -->
    <!-- seo - start -->
        <div class="l-col min-0--12">
            <div class="c-description c-description__bottom">
                <?php echo $data['cat_desc'] ?>
            </div>
        </div>
    <!-- seo - end -->

</div>