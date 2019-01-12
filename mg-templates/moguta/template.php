<?php /*
Template Name: Moguta
Author: Moguta
Version: 1.0.3
*/ ?>
<!DOCTYPE html>
<html lang="ru">
    <head>
        <!--[if lte IE 9]>
        <link  rel="stylesheet" type="text/css" href="<?php echo PATH_SITE_TEMPLATE ?>/css/reject/reject.css" />
        <link  rel="stylesheet" type="text/css" href="<?php echo PATH_SITE_TEMPLATE ?>/css/style-ie9.css" />
        <script  src="https://code.jquery.com/jquery-1.12.4.min.js"
        integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="
        crossorigin="anonymous"></script>
        <![endif]-->
      
		<?php mgMeta("meta","css","jquery"); ?>		
        <meta name="format-detection" content="telephone=no">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <?php mgAddMeta('<script src="'.PATH_SITE_TEMPLATE.'/js/owl.carousel.min.js"></script>'); ?>
        <?php mgAddMeta('<script src="'.PATH_SITE_TEMPLATE.'/js/jquery.hoverIntent.js"></script>'); ?>
        <?php mgAddMeta('<script src="'.PATH_SITE_TEMPLATE.'/js/script.js"></script>'); ?>

    </head>
    <body class="l-body <?php MG::addBodyClass('l-'); ?>" <?php backgroundSite(); ?>>
        <?php layout('icons'); ?> <!-- svg иконки -->		
		<?php layout('ie9'); ?>
        <header class="l-header">
            <div class="l-header__top">
                <div class="l-container">
                    <div class="l-row">
                        <div class="l-col min-0--3 min-1025--6">
                            <div class="l-header__block">
                                <?php layout('topmenu'); ?> <!-- меню страниц -->
                            </div>
                        </div>
                        <div class="lcg l-col min-0--9 min-1025--6">
                            <?php layout('language_select'); ?> <!-- блок выбора языка -->
                            <?php layout('currency_select'); ?> <!-- блок выбора валюты -->
                            <div class="l-header__block group">
                                <?php layout('group'); ?> <!-- "новинки", "хиты продаж", "акции" -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="l-header__middle">
                <div class="l-container">
                    <div class="l-row min-0--align-center">
                        <div class="l-col min-0--12 min-768--3">
                            <a  itemprop="logo" class="c-logo" href="<?php echo SITE ?>"><?php echo mgLogo(); ?></a> <!-- логотип -->
                        </div>
                        <div class="l-col min-0--12 min-768--9">
                            <div class="min-0--flex min-0--justify-center min-768--justify-end">
                                <div class="l-header__block">
                                    <?php layout('contacts'); ?> <!-- контакты -->
                                </div>

                                <?php if (MG::getSetting('printCompareButton') == 'true') { ?>
                                    <div class="l-header__block max-767--hide">
                                        <?php layout('compare'); ?> <!-- сравнение товаров -->
                                    </div>
                                <?php } ?>

                                <div class="l-header__block">
                                    <?php layout('auth'); ?> <!-- авторизация на сайте -->
                                </div>
                                <div class="l-header__block">
                                    <?php layout('cart'); ?> <!-- корзина -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="l-header__bottom">
                <div class="l-container">
                    <div class="l-row">
                        <div class="l-col min-0--5 min-768--3">
                            <div class="l-header__block">
                                <?php layout('leftmenu'); ?> <!-- меню каталога -->
                            </div>
                        </div>
                        <div class="l-col min-0--7 min-768--9">
                            <div class="l-header__block">
                                <?php layout('search'); ?> <!-- поиск -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>     
    <?php if (URL::isSection(null)): ?>    
    <?php if (class_exists('SliderAction')): ?>
            [slider-action]
    <?php endif; ?>
    <?php endif ?>
        <main class="l-main">
            <div class="l-container">
                <div class="l-row">
                    <div class="l-col min-12--hide min-1025--3 l-main__left">
                    <?php if (class_exists('dailyProduct')): ?>
                        <div class="daily-wrapper">
                                [daily-product]
                        </div>
                    <?php endif; ?>    
                        <div class="c-filter" id="c-filter" onClick="">
                            <div class="c-filter__content">
                                <?php filterCatalogMoguta(); ?> <!-- фильтр -->
                            </div>
                        </div>
                        <?php if (function_exists('sliderProducts')): ?>
                        <div class="mg-advise">
                                <div class="mg-advise__title"><?php echo lang('recommend'); ?></div>
                                    [slider-products countProduct="4" countPrint="1"]
                                 <!-- cлайдер товаров -->
                            </div>  
                        <?php endif ?>                     
                        <?php if (class_exists('PluginNews')): ?>
                        [news-anons count="3"]
                        <?php endif; ?>  
                    </div>
                    <div class="l-col min-0--12 min-1025--9 l-main__right">
                        <div class="l-row">
                            <div class="l-col min-0--12">
                                <?php if (class_exists('BreadCrumbs')&&MG::get('controller')=="controllers_catalog"): ?>[brcr]<?php endif; ?>

                                <!-- блок для главной страницы -->
                                <?php if(MG::get('controller') != 'controllers_index' && in_array(URL::getClearUri(), array('', '/'))) {  ?>
                                     <?php if (class_exists('trigger')): ?>
                                     [trigger-guarantee id="1"]
                                     <?php endif ?>
                                    <!--  blok editor start -->
                                    <?php if (class_exists('SiteBlockEditor')): ?>
                                     <div class="site-blocks l-col">
                                         [site-block id=1]
                                         [site-block id=2]
                                         [site-block id=3]
                                     </div>
                                    <?php endif ?>
                                    <!--  blok editor end -->    
                                <?php } ?>
                                <!-- конец блок для главной страницы -->

                                <?php layout('content'); ?> <!-- содержимое страниц -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="l-footer">
            <div class="l-container">
                <div class="l-row">
                    <div class="l-col min-0--12 min-768--5">
                        <div class="c-copyright"><?php echo date('Y').' '.lang('copyright'); ?></div> <!-- копирайт -->
                    </div>
                    <div class="l-col min-0--12 min-768--2 min-0--flex min-0--align-center min-0--justify-center max-767--order-end">
                        <div class="c-widget">
                            <?php layout('widget'); ?> <!-- счетчик -->
                        </div>
                    </div>
                    <div class="l-col min-0--12 min-768--5">
                        <div class="c-copyright c-copyright__moguta"><?php copyrightMoguta(); ?></div> <!-- копирайт -->
                    </div>
                </div>
            </div>
        </footer>
		
        <?php if (class_exists('BackRing')): ?>[back-ring]<?php endif; ?> <!-- обратный звонок -->		

		<?php mgMeta("js"); ?>

        <!-- избранное -->
        <?php 
            if (in_array(EDITION, array('market', 'gipermarket'))) {
                $showFavorite = $_COOKIE['favorites'] ? 'j-favourite--open' : ''; 
        ?>
        <a href="<?php echo SITE ?>/favorites" class="j-favourite favourite <?php echo $showFavorite ?>">
            <span class="favourite__text">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 47.94 47.94"><path d="M26.285 2.486l5.407 10.956c.376.762 1.103 1.29 1.944 1.412l12.091 1.757c2.118.308 2.963 2.91 1.431 4.403l-8.749 8.528c-.608.593-.886 1.448-.742 2.285l2.065 12.042c.362 2.109-1.852 3.717-3.746 2.722l-10.814-5.685c-.752-.395-1.651-.395-2.403 0l-10.814 5.685c-1.894.996-4.108-.613-3.746-2.722l2.065-12.042c.144-.837-.134-1.692-.742-2.285L.783 21.014c-1.532-1.494-.687-4.096 1.431-4.403l12.091-1.757c.841-.122 1.568-.65 1.944-1.412l5.407-10.956c.946-1.919 3.682-1.919 4.629 0z"/></svg>
                Избранное
                <span class="favourite__count">(<?php echo substr_count($_COOKIE['favorites'], ',')+1 ?>)</span>
            </span>
        </a>
        <?php } ?>
        <!-- / избранное -->
    </body>
</html>