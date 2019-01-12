<?php

/**
 * Файл может содержать ряд пользовательских фунций влияющих на работу движка. 
 * В данном файле можно использовать собственные обработчики 
 * перехватывая функции движка, аналогично работе плагинов.
 * 
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage File
 */
function seoMeta($args) {
  $settings = MG::get('settings');
  $args[0]['title'] = !empty($args[0]['title']) ? $args[0]['title'] : '';
  $title = !empty($args[0]['meta_title']) ? $args[0]['meta_title'] : $args[0]['title'];
  MG::set('metaTitle', $title.' | '.$settings['sitename']);
}

function filterCatalogMoguta(){
	if(MG::get('controller')=='controllers_catalog'){
		mgAddMeta('<link type="text/css" href="'.SCRIPT.'standard/css/jquery.ui.slider.css" rel="stylesheet"/>');
		mgAddMeta('<script src="'.SCRIPT.'standard/js/filter.js"></script>');
	}
	echo MG::get('catalogfilter');
}

mgAddAction('mg_seometa', 'seoMeta', 1);

/*
Этой функцией можно отключать ненужные css и js подключаемые плагинами и движком
mgExcludeMeta(
  array(
   '/mg-plugins/rating/css/rateit.css',
   '/mg-plugins/rating/js/rating.js',
   '/mg-core/script/standard/css/layout.agreement.css'
 )
);
*/




