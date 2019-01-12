<?php
/**
 *  Файл представления Group - выводит сгенерированную движком информацию на странице сайта с новинками, рекомендуемыми и товарами распродажи.
 *  В этом  файле доступны следующие данные:
 *   <code>
 * 'items' => $items['catalogItems'],
 *    $data['items'] => Массив товаров,
 *    $data['titeCategory'] => Название открытой категории,
 *    $data['pager'] => html верстка  для навигации страниц,
 *    $data['meta_title'] => Значение meta тега для страницы,
 *    $data['meta_keywords'] => Значение meta_keywords тега для страницы,
 *    $data['meta_desc'] => Значение meta_desc тега для страницы,
 *    $data['currency'] => Текущая валюта магазина,
 *    $data['actionButton'] => тип кнопки в мини карточке товара
 *   </code>
 *
 *   Получить подробную информацию о каждом элементе массива $data, можно вставив следующую строку кода в верстку файла.
 *   <code>
 *    <?php viewData($data['items']); ?>
 *   </code>
 *
 *   Вывести содержание элементов массива $data, можно вставив следующую строку кода в верстку файла.
 *   <code>
 *    <?php echo $data['items']; ?>
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
 <!-- catalog - start -->
<div class="l-row">

    <!-- c-title - start -->
    <div class="l-col min-0--12">
        <h1 class="c-title"><?php echo $data['titeCategory'] ?></h1>
    </div>
    <!-- c-title - end -->

    <!-- c-switcher - start -->
    <div class="l-col min-0--12">
        <div class="c-switcher">
            <div class="c-switcher__item c-switcher__item--active" data-type="c-goods--grid" title="<?php echo lang('viewNet'); ?>">
                <svg class="icon icon--grid"><use xlink:href="#icon--grid"></use></svg>
            </div>
            <div class="c-switcher__item" data-type="c-goods--list" title="<?php echo lang('viewList'); ?>">
                <svg class="icon icon--list"><use xlink:href="#icon--list"></use></svg>
            </div>
        </div>
    </div>
    <!-- c-switcher - end -->

    <!-- c-goods - start -->
    <div class="l-col min-0--12">
        <div class="c-goods products-wrapper catalog">
            <div class="l-row">
                <?php foreach ($data['items'] as $item) {
                    $data['item'] = $item; ?>
                    <div class="l-col min-0--6 min-768--4 min-990--3 min-1025--4 c-goods__trigger">
                        <?php layout('mini_product', $data); ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <!-- c-goods - end -->

    <!-- pager - start -->
    <div class="l-col min-0--12">
        <div class="c-pagination">
            <?php echo $data['pager']; ?>
        </div>
    </div>
    <!-- pager - end -->

</div>
<!-- catalog - end -->

<script>
    $(document).ready(function() {

        if($('.c-pagination li').length == 1) {
            $('.c-pagination').hide();
        }

    });
</script>