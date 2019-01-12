<?php
/**
 *  Файл представления Catalog - выводит сгенерированную движком информацию на странице сайта с каталогом товаров.
 *  В этом  файле доступны следующие данные:
 *   <code>
 *    $data['items'] => Массив товаров,
 *    $data['titeCategory'] => Название открытой категории,
 *    $data['cat_desc'] => Описание открытой категории,
 *    $data['pager'] => html верстка  для навигации страниц,
 *    $data['searchData'] => Результат поисковой выдачи,
 *    $data['meta_title'] => Значение meta тега для страницы,
 *    $data['meta_keywords'] => Значение meta_keywords тега для страницы,
 *    $data['meta_desc'] => Значение meta_desc тега для страницы,
 *    $data['currency'] => Текущая валюта магазина,
 *    $data['actionButton'] => Тип кнопки в мини карточке товара,
 *    $data['cat_desc_seo'] => SEO описание каталога,
 *    $data['seo_alt'] => Алтернативное подпись изображение категории,
 *    $data['seo_title'] => Title изображения категории
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

<?php if (empty($data['searchData'])): ?>

    <!-- catalog - start -->
    <div class="l-row">
        <!-- c-title - start -->
        <div class="l-col min-0--12 c-title__categoty">
            <h1 class="c-title"><?php echo $data['titeCategory'] ?></h1>
        </div>
        <!-- c-title - end -->

        <!-- c-description - start -->
        <?php if ($cd = str_replace("&nbsp;", "", $data['cat_desc'])): ?>
            <div class="l-col min-0--12 c-description_category">
                <div class="c-description c-description__top">
                    <?php if ($data['cat_img']): ?>
                        <img class="c-description__img" src="<?php echo SITE . $data['cat_img'] ?>" alt="<?php echo $data['seo_alt'] ?>" title="<?php echo $data['seo_title'] ?>">
                    <?php endif; ?>
                    <?php if (URL::isSection('catalog')||(((MG::getSetting('catalogIndex')=='true') && (URL::isSection('index') || URL::isSection(''))))): ?>
                    <!-- Здесь можно добавить описание каталога - информация для пользователей (выводится только на странице каталог (не в категории)) -->
                        <div class="c-description__txt"><p><?php echo $data['cat_desc'] ?></p></div>
                    <?php else :?>
                        <div class="c-description__txt"><p><?php echo $data['cat_desc'] ?></p></div>
                    <?php endif;?>
                </div>
            </div>
        <?php endif; ?>
        <!-- c-description - end -->

        <!-- c-sub - start -->
        <?php if (MG::getSetting('picturesCategory') == 'true'): ?>
            <?php echo mgSubCategory($data['cat_id']); ?>
        <?php endif; ?>
        <!-- c-sub - end -->


        <!-- mobile filter - start -->
        <div class="l-col min-0--12 min-768--6 min-1025--hide ">
            <a href="#c-filter" class="c-button c-filter__button">
                <svg class="icon icon--filter"><use xlink:href="#icon--filter"></use></svg>
                <?php echo lang('mobileShowFilter'); ?>
            </a>
        </div>
        <!-- mobile filter - end -->

        <!-- c-switcher - start -->
        <div class="l-col min-0--hide min-768--6 min-1025--12 c-catalog__switchers">
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

        <!-- c-apply - start -->
        <?php layout("apply_filter", $data['applyFilter']); ?>
        <!-- c-switcher - end -->

        <!-- c-goods - start -->
        <div class="l-col min-0--12">
            <div class="c-goods products-wrapper catalog">
                <div class="l-row">
                    <?php foreach ($data['items'] as $item) {
                        $data['item'] = $item; ?>
                        <div class="l-col min-0--12 min-375--6 min-768--4 min-990--3 min-1025--4 c-goods__trigger">
                            <?php layout('mini_product', $data); ?>
                        </div>
                    <?php } ?>
                    
                    <?php if(count($data['items']) == 0 && $_GET['filter'] == 1) echo '<div class="l-col">'.lang('searchFail').'</div>'; ?>

                    <!-- pager - start -->
                    <div class="l-col min-0--12">
                        <div class="c-pagination">
                            <?php echo $data['pager']; ?>
                        </div>
                    </div>
                    <!-- pager - end -->

                </div>
            </div>
        </div>
        <!-- c-goods - end -->

        <!-- seo - start -->
        <?php if($data['cat_desc_seo']){ ?>
        <div class="l-col min-0--12">
            <div class="c-description c-description__bottom">
                <?php echo $data['cat_desc_seo'] ?>
            </div>
        </div>
        <?php } ?>
        <!-- seo - end -->

    </div>
    <!-- catalog - end -->


    <?php else: ?>


    <!-- search - start -->
    <div class="l-row">
        <style>
            .daily-wrapper{
                display: none;
            }
        </style>
        <!-- c-title - start -->
        <div class="l-col min-0--12">
            <h1 class="c-title"><?php echo lang('search1'); ?><b class="c-title__search">"<?php echo $data['searchData']['keyword'] ?>"</b><?php echo lang('search2'); ?><b class="c-title__search"><?php echo mgDeclensionNum($data['searchData']['count'], array(lang('search3-1'), lang('search3-2'), lang('search3-3'))); ?></b></h1>
        </div>
        <!-- c-title - end -->

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
    <!-- search - end -->

<?php endif;?>