<?php

/**
 * Файл metodadapter.php содержит набор функций, необходимых пользователям
 * для построения собственных скриптов. Все функции этого файла,
 * являются алиасами, для аналогичных функций из класса MG.
 * Целью использования данного файла является исключение из пользовательских
 * файлов сложного для понимания синтаксиса MG::
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Files
 */

/**
 * Метод addAction Добавляет обработчик для заданного хука.
 *
 * <code>
 *   mgAddAction('printHeader', 'userfunc', 2);
 * </code>
 * 
 * @param string $hookName имя хука, на который вешается обработчик.
 * @param string $userFunction пользовательская функции, которая сработает при объявлении хука.
 * @param int $countArg количество аргументов, которое ждет пользовательская функция.
 * @param int $priority приоритет выполнения пользовательская функция (чем больше число, тем позже выполняется функция).
 */
function mgAddAction($hookName, $userFunction, $countArg = 0, $priority = 10) {
  MG::addAction($hookName, $userFunction, $countArg, $priority);
}

/**
 * Метод mgAddCustomPriceAction Добавляет обработчик для изменения цены товара, до того как он попал в корзину.
 * <code>
 *   mgAddCustomPriceAction(array(__CLASS__, 'applyRate'));
 * </code>
 * @param string $userFunction название функции обработчика, или массив вида (Класс, Имя функции обработчика в классе)
 * @param int $priority приоритет выполнения функции
 */
function mgAddCustomPriceAction($userFunction, $priority = 10) {
  MG::addPriceCustomFunction($userFunction, $priority);
}

/**
 * Метод addAction Добавляет обработчик шорткода.
 * <code>
 *   mgAddShortcode('buy-click', array(__CLASS__, 'buyOneClick'));
 * </code>
 * @param string $shortcode название шорткода.
 * @param string $userFunction пользовательская функции, которая сработает при встрече [названия шорткода].
 */
function mgAddShortcode($shortcode, $userFunction) {
  MG::addShortcode($shortcode, $userFunction);
}

/**
 * Добавляет обработчик для страницы плагина.
 * Назначенная в качестве обработчика пользовательская функция
 * будет, отрисовывать страницу настроек плагина.
 * <code>
 *   mgPageThisPlugin(__FILE__, array(__CLASS__, 'pageSettingsPlugin'));
 * </code>
 * @param string $plugin название папки, в которой лежит плагин.
 * @param string $userFunction пользовательская функция, которая сработает при открытии страницы настроек данного плагина.
 */
function mgPageThisPlugin($plugin, $userFunction) {
  MG::addAction($plugin, $userFunction);
}

/**
 * Добавляет обработчик для активации плагина,
 * пользовательская функция будет срабатывать тогда когда
 * в панели администрирования будет активирован плагин.
 *
 * >Является необязательным атрибутом плагина, при отсутствии этого
 * обработчика плагин тоже будет работать.
 *
 * Функция обрабатывающая событие
 * не должна производить вывод (echo, print, print_r, var_dump), это нарушит
 * логику работы AJAX.
 *
 * <code>
 *   mgActivateThisPlugin(__FILE__, array(__CLASS__, 'activate'));
 * </code>
 * 
 * @param string $dirPlugin директория, в которой хранится плагин.
 * @param string $userFunction пользовательская функция, которая сработает при объявлении хука.
 */
function mgActivateThisPlugin($dirPlugin, $userFunction) {
  MG::activateThisPlugin($dirPlugin, $userFunction);
}

/**
 * Добавляет обработчик для дезактивации плагина,
 * пользовательская функция будет срабатывать тогда когда
 * в панели администрирования будет выключен  плагин.
 *
 * >Является необязательным атрибутом плагина, при отсутствии этого
 * обработчика плагин тоже будет работать.
 *
 * Функция обрабатывающая событие
 * не должна производить вывод (echo, print, print_r, var_dump), это нарушит
 * логику работы AJAX.
 *
 * <code>
 *   mgDeactivateThisPlugin(__FILE__, array(__CLASS__, 'deActivate'));
 * </code>
 * 
 * @param string $dirPlugin директория, в которой хранится плагин.
 * @param string $userFunction пользовательская функция, которая сработает при объявлении хука.
 */
function mgDeactivateThisPlugin($dirPlugin, $userFunction) {
  MG::deactivateThisPlugin($dirPlugin, $userFunction);
}

/**
 * Создает hook - крючок, для  пользовательских функций и плагинов.
 * <code>
 *   mgCreateHook('hook');
 * </code>
 * @param string $hookName функция из плагина для выполнения
 */
function mgCreateHook($hookName) {
  MG::createHook($hookName);
}

/**
 * Добавляет переданную строку в секцию <head> </head>
 * <code>
 *   mgAddMeta('<link href="mg-plugins/news/css/style.css" rel="stylesheet" type="text/css">');
 * </code>
 * @param string $data строковая переменная, с данными.
 * @param string $onlyController подключать только для заданного контролера.
 * @return bool|void
 */
function mgAddMeta($data, $onlyController = 'all') {
  if (stristr($data,'mg-core/script/zoomsl-3.0.js')!==FALSE && MG::getSetting('connectZoom')=='false') {
    return false;
  }
  if (stristr($data,'mg-core/script/jquery.maskedinput.min.js')!==FALSE && MG::getSetting('usePhoneMask')=='false') {
    return false;
  }
  $register = MG::get('register')?MG::get('register'):array();

  // Если заголовок нужно подключить только в определенном контролере, 
  // то записываем его в  отдельный ключ массива.
  if($onlyController!='all') {
    $onlyController = 'controllers_'.$onlyController;
  }
  if(!empty($register[$onlyController])) {
    if(!in_array($data, $register[$onlyController])) {
      $register[$onlyController][] = $data;
    }
  }
  else{
    $register[$onlyController][] = $data;
  }

  MG::set('register', $register);
  MG::set('userMeta', MG::get('userMeta')."\n".$data);
}

/**
 * Исключает из реестра mgAddMeta , указанный статичный файл
 * <code>
 *   mgExcludeMeta('<link href="mg-plugins/news/css/style.css" rel="stylesheet" type="text/css">');
 * </code>
 * @param string|array $data строковая переменная с данными, либо массив.
 * @return void
 */
function mgExcludeMeta($data) {
  $exclude = MG::get('exclude_meta')?MG::get('exclude_meta'):array();
  
  if(is_array($data)){
	foreach($data as $v){
	 $exclude[]=$v;
	}
  }else{
    $exclude[]=$data;
  }
 
  MG::set('exclude_meta', $exclude);
}
/**
 * Устанавливает значение для опции (настройки).
 * <code>
 * $data = array(
 *   option => 'sitename'
 *   value  => 'moguta.ru'
 * )
 * setOption($data);
 * // или
 * setOption('sitename', 'moguta.ru');
 * </code>
 * @param array|string $option - значения для полей таблицы с настройками или название опции.
 * @param string $value - значение опции, указывается только если первый параметр строковой и означает название опции.
 * @return void
 */
function setOption($option, $value = '') {
  if(is_array($option)) {
    $data = $option;
  } else {
    $data = array('option' => $option, 'value' => $value);
  }
  MG::setOption($data);
}

/**
 * Возвращает значение для запрошенной опции (настройки).
 * Имеет два режима:
 * 1. getOption('optionName') - вернет только значение;
 * 2. getOption('optionName', true) - вернет всю информацию об опции в
 * виде массива.
 * <code>
 * echo getOption('sitename'); // 'moguta.ru'
 * </code>
 * <code>
 * $res = getOption('sitename', true);
 * viewData($res);
 * //$res == array(
 * //  'option' => 'sitename', // идентификатор опции
 * //  'value'  => 'moguta.ru', // значение опции
 * //  'active' => 'Y', // в будущем будет отвечать за автоподгрузку опций в кеш Y/N
 * //  'name' => 'Имя сайта', // метка для опции
 * //  'desc' => 'Настройка задает имя для сайта', // описание опции
 * //);
 * </code>
 * @param string $option название опции
 * @param bool $data если true, то вернет всю информацию об опции в виде массива
 * @return string|array
 */
function getOption($option, $data = false) {
  return MG::getOption($option, $data);
}

/**
 * Получить меню в HTML виде.
 * Ставится в месте вывода меню.
 * <code>
 *   mgMenu();
 * </code>
 * @return string
 */
function mgMenu() {
  echo MG::getMenu();
}

/**
 * Получить полное меню в HTML виде.
 * Ставится в месте вывода меню.
 * <code>
 *   mgMenuFull();
 * <code>
 * @param string $type тип меню
 * @return string
 */
function mgMenuFull($type = 'top') {
  echo MG::getMenu($type);
}

/**
 * Получить товары из корзины.
 * <code>
 *   $res = mgGetCart();
 *   viewData($res);
 * </code>
 * @return array данные по добавленным товарам в корзину.
 */
function mgGetCart() {
  return MG::getSmalCart();
}

mgAddShortcode('mg-meta', 'mgMetaInsertMode',1);
mgAddShortcode('mg-head', 'mgMetaInsert');
/**
 * Возвращает шорткод для подготовленных мета данных (seo, ссылки на css и js файлы).
 * <code>
 * <head>
 *   <?php mgMeta("meta","css","jquery"); ?>
 *   ............
 * </head>
 * <body>
 *   ............
 *   <?php mgMeta("js"); ?>
 * </body>
 * </code>
 * @param array $args массив с указанием, какие мета данные вернуть (meta(seo), css, js, jquery)
 * @return bool
 */
function mgMeta($args=null) { 

  $numargs = func_num_args();
    if(empty($numargs)) {
		echo '[mg-head]';
		return true;
	}
     
	$accessMode =array("meta","css","js","jquery");	
	$args = func_get_args();
	$shortcode='[mg-meta';
	foreach($args as $mode) {
	 if(in_array($mode, $accessMode)) {
	 $shortcode.=' '.$mode.'="true"';
	 }
	}
	$shortcode.=']';
	echo $shortcode;
	return true;
}
/**
 * Выводит подготовленные мета данные (seo, ссылки на css и js файлы).
 * @ignore
 * @param array массив с указанием, какие мета данные вывести (meta(seo), css, js, jquery)
 * @return string
 */
function mgMetaInsertMode($arg) {
  $tmp = MG::meta($arg);
  return $tmp;
}
/**
 * Выводит все подготовленные мета данные (seo, ссылки на css и js файлы).
 * @ignore
 * @return string
 */
function mgMetaInsert() {
  return MG::meta();
}
mgAddShortcode('prop', 'removePropCode');
/**
 * Удаляет шорткод характеристики
 * @return string
 * @ignore
 */
function removePropCode() {
  return '';
}
/**
 * Устанавливает SEO данные страницы: title, description, keywords.
 * <code>
 * $data = array(
 *   'meta_title' => 'Кроссовки Nike Flight Bonafide',
 *   'meta_keywords' => 'Кроссовки Nike Flight Bonafide купить, SKU319, Кроссовки, Nike, Flight, Bonafide',
 *   'meta_desc' => 'Описание Nike Flight Bonafide'
 * );
 * mgSEO($data);
 * </code>
 * @param array $data массив с SEO данными.
 * @return void
 */
function mgSEO($data) {
  MG::seoMeta($data);
}

/**
 * Задает заголовок страницы.
 * <code>
 *   mgTitle('Заголовок');
 * </code>
 * @param string|bool $title заголовок страницы.
 * @return void
 */
function mgTitle($title) {
  MG::titlePage($title);
}

/**
 * Выводит содержимое массива на страницу
 * <code>
 * $data = array(1, 2, 5, 'data');
 * viewData($data);
 * </code>
 * @param array $data
 */
function viewData($data) {
  echo "<pre>";
  echo htmlspecialchars(print_r($data, true));
  echo "</pre>";
}

/**
 * Склонение числительных.
 * <code>
 * $data['searchData']['count'] = 5;
 * echo 'Найдено '.mgDeclensionNum($data['searchData']['count'], array('товар', 'товара', 'товаров'));
 * </code>
 * @param int $number количество
 * @param array $titles массив для склонения, например: array('товар', 'товара', 'товаров')
 * @return string
 */
function mgDeclensionNum($number, $titles) {
  return MG::declensionNum($number, $titles);
}

/**
 * Проверяет является ли страница статичной, созданной из панели администрирования
 * <code>
 *   $res = isStaticPage();
 *   var_dump($res);
 * </code>
 * @return bool
 */
function isStaticPage() {
  return MG::get('isStaticPage');
}

/**
 * Выводит блок стандартной маленькой корзины в HTML виде.
 * Использует 'layout_cart' текущего шаблона.
 * <code>
 * mgSmallCartBlock($data);
 * </code>
 * @param array $data массив с данными для корзины
 */
function mgSmallCartBlock($data) {
  echo MG::layoutManager('layout_cart', $data);
}

/**
 * Выводит блок стандартного поиска в HTML виде.
 * Использует 'layout_search' текущего шаблона.
 * <code>
 * mgSearchBlock();
 * </code>
 */
function mgSearchBlock() {
  echo MG::layoutManager('layout_search', null);
}

/**
 * Выводит блок с контактами в HTML виде.
 * Использует 'layout_cart' текущего шаблона.
 * <code>
 * mgContactBlock();
 * </code>
 */
function mgContactBlock() {
  echo MG::layoutManager('layout_contacts', null);
}

/**
 * Возвращает правильно сформированную картинку для продукта в HTML.
 * Со всеми параметрами, для эффекта перелета в корзину.
 * <code>
 *   echo mgImageProduct($data['item'],false,'MID',true);
 * </code>
 * @param array $data массив с параметрами товара
 * @param bool $origSize использовать оригинальное изображение
 * @param string|null $mode какую миниатюру использовать ('MIN', 'MID' или 'MAX'(оригинальное изображение))
 * @param bool $titleNoPrint не выводить аттрибут title у изображения
 * @return string
 */
function mgImageProduct($data, $origSize = false, $mode=null, $titleNoPrint=false) {

  if(!empty($data['images_product'])) $data["image_url"] = $data['images_product'][0];
  $product = new Models_Product();
  $data["image_url"] = mb_basename($data["image_url"]);
  $imagesData = $product->imagesConctruction($data["image_url"], $data["image_title"], $data["image_alt"], $data['id']);  
  $src = SITE."/uploads/no-img.jpg";
  $dir = floor($data["id"]/100).'00';
  $imagesData["image_url"] = mb_basename($imagesData["image_url"]);
	$srcLarge = mgImageProductPath($data["image_url"], $data["id"]);
  if(file_exists(URL::$documentRoot.DS.'uploads'.DS.'product'.DS.$dir.DS.$data["id"].DS.'thumbs'.DS.'70_'.$imagesData["image_url"])) {
    if(!$origSize){	
	    $src = SITE.'/uploads/product/'.$dir.'/'.$data['id'].'/thumbs/70_'.$imagesData["image_url"];
		
	    if($mode=='MIN'){
		 $src = SITE.'/uploads/product/'.$dir.'/'.$data['id'].'/thumbs/30_'.$imagesData["image_url"];
		}
		
		if($mode=='MID'){
		 $src = SITE.'/uploads/product/'.$dir.'/'.$data['id'].'/thumbs/70_'.$imagesData["image_url"];
		}
		
		if($mode=='MAX'){
		 $src = SITE.'/uploads/product/'.$dir.'/'.$data['id'].'/'.$imagesData["image_url"];
		}
		
	}else{
	  $src = SITE.'/uploads/product/'.$dir.'/'.$data['id'].'/'.$imagesData["image_url"];
	}
  }elseif(file_exists(URL::$documentRoot.DS.'uploads'.DS.'thumbs'.DS.'70_'.$imagesData["image_url"])) {
       if(!$origSize){
	    $src = SITE.'/uploads/thumbs/70_'.$imagesData["image_url"];
		
	    if($mode=='MIN'){
		  $src = SITE.'/uploads/thumbs/30_'.$imagesData["image_url"];
		}
		
		if($mode=='MID'){
		  $src = SITE.'/uploads/thumbs/70_'.$imagesData["image_url"];
		}
		
		if($mode=='MAX'){
		 $src = SITE.'/uploads/'.$imagesData["image_url"];
		}
		
	   }else{
	    $src = SITE.'/uploads/'.$imagesData["image_url"];
	  }
  }
  
  $imagesData["image_alt"]  = $imagesData["image_alt"]?$imagesData["image_alt"]:$data["title"];
  $imagesData["image_title"] = $imagesData["image_title"]?$imagesData["image_title"]:$data["title"];
  
  $titleAttr = 'title="'.$imagesData["image_title"].'"';
  if($titleNoPrint){
    $titleAttr = '';
  }
  
  $alt = 'alt="'.$imagesData["image_alt"].'"';
  
  if (@$_SESSION['user']->enabledSiteEditor == "true") {
    $alt = strip_tags($alt);
    $titleAttr = strip_tags($titleAttr);
  }
  
  return '<img class="mg-product-image" itemprop="image" data-transfer="true" data-product-id="'.$data["id"].'" src="'.$src.'" '.$alt.' '.$titleAttr.' data-magnify-src="'.$srcLarge.'">';
}


/**
 * Функция возвращает сформированную ссылку на картинку
 * <code>
 *   $res = mgImageProductPath($value["image_url"], $value['id'], 'small');
 *   viewData($res);
 * </code>
 * @param string $image имя изображения
 * @param int $productId id товара
 * @param string $size размер: small/big/orig - не обязательный
 * @return string
 */
function mgImageProductPath($image, $productId, $size = 'orig') {

  $src = SITE.'/uploads/no-img.jpg';
  
  if(empty($image)) {
    return $src;
  }  

  $image = mb_basename($image);
  
  if(strpos($image, '30_') === 0 || strpos($image, '70_') === 0) {
    $image = str_replace(array('30_', '70_'), '', $image);
  }
	
  $dir = floor($productId/100).'00';
  $ds = DS;
  $prefix = '';
  
  if($size == 'small') {
    $prefix = '30_';
  }elseif($size == 'big') {
    $prefix = '70_';
  }
    
  if(empty($size) || $size == 'orig') {
    if(file_exists(URL::$documentRoot.$ds.'uploads'.$ds.'product'.$ds.$dir.$ds.$productId.$ds.$image)) {
      $src = SITE.'/uploads/product/'.$dir.'/'.$productId.'/'.$image;
    }elseif(file_exists(URL::$documentRoot.$ds.'uploads'.$ds.$image)) {
      $src = SITE.'/uploads/'.$image;
    }
	 
  }else{
    //Добавлено в 5.7.0. Через некоторое время можно удалить проверку наличия якартинки по старому образцу пути.
    if(file_exists(URL::$documentRoot.$ds.'uploads'.$ds.'product'.$ds.$dir.$ds.$productId.$ds.'thumbs'.$ds.$prefix.$image)) {
      $src = SITE.'/uploads/product/'.$dir.'/'.$productId.'/thumbs/'.$prefix.$image;
    }elseif(file_exists(URL::$documentRoot.$ds.'uploads'.$ds.'thumbs'.$ds.$prefix.$image)) {
      $src = SITE.'/uploads/thumbs/'.$prefix.$image;
    }
  }

  return $src;
}

/**
 * Возвращает список вложенных категорий в HTML виде.
 * Использует 'layout_subcategory' текущего шаблона.
 * <code>
 *   mgSubCategory(5);
 * </code>
 * @param int $catId id родительской категории.
 */
function mgSubCategory($catId) {
  $data = MG::get('category')->getHierarchyCategory($catId, true);
  echo MG::layoutManager('layout_subcategory', $data);
}

/**
 * Возвращает правильно верстку картинок товара в HTML.
 * Использует 'layout_images' текущего шаблона.
 * <code>
 *   mgGalleryProduct($data);
 * </code>
 * @param type $data параметры товара
 */
function mgGalleryProduct($data) {
  if (@$_SESSION['user']->enabledSiteEditor == "true") {$data['title'] = strip_tags($data['title']);}
  echo MG::layoutManager('layout_images', $data);
}

/**
 * Возвращает картинку логотипа магазина, установленную в настройках.
 * <code>
 *   echo mgLogo();
 * </code>
 * @param string $alt параметр alt
 * @param string $title параметр title
 * @param string $style дополнительные стили
 * @return string
 */
function mgLogo($alt = '', $title = '', $style = '') {
  if(!$title&&!$alt) {
    $title = MG::getSetting('shopName');
    $alt = $title;
  }
  $logo = (MG::getSetting('shopLogo')!='')?MG::getSetting('shopLogo'):"/mg-templates/".MG::getSetting('templateName')."/images/logo.png";

  if ($style == '') {
    $ext = explode('.', $logo);
    $ext = end($ext);
    if (strtolower($ext) === 'svg') {
      $style = 'style="max-height: 100px;"';
    }
  }

  return '<img src='.SITE.$logo.' alt="'.htmlspecialchars($alt).
    '" title="'.htmlspecialchars($title).'" '.$style.'>';
}

/**
 * Выводит верстку содержащуюся в заданном layout. 
 * <code>
 *   layout('cart', $data);
 * </code>
 * @param $layout название верстки  в папке шаблона layout, без префикса 'layout_'
 * @param $data массив данных переданных в layout'
 * @return bool
 */
function layout($layout, $data = null) {
  if(in_array($layout, array('cart', 'auth', 'contacts', 'search'))) {
    $data = MG::get('templateData');
  }

  if($layout=='topmenu') {
    echo Menu::getMenuFull('top');
    return true;
  }

  if($layout=='leftmenu') {
    echo MG::get('category')->getCategoriesHTML();
    return true;
  }

  if($layout=='horizontmenu') {
    echo MG::get('category')->getCategoriesHorHTML();
    return true;
  }

  if($layout=='content') {
    $data = MG::get('templateData');
    echo $data['content'];
    return true;
  }

  if($layout=='widget') {
    echo MG::getSetting('widgetCode');
    return true;
  }

  if($layout=='logo') {
    $logo = (MG::getSetting('shopLogo')!='')?MG::getSetting('shopLogo'):"/mg-templates/".MG::getSetting('templateName')."/images/logo.png";
    echo '<img src="'.SITE.$logo.'" alt="">';
    return true;
  }
  
  if($layout=='property') {
    echo Property::sortPropertyToGroup($data);   
    return true;
  }

  echo MG::layoutManager('layout_'.$layout, $data);
  return true;
}

/**
 * Возвращает цену в отформатированном виде.
 * @ignore
 */
function priceFormat($number) {
  return $number;
}

/**
 * Возвращает html код фильтров магазина.
 * Работает только для разделов каталога.
 * <code>
 *   <?php filterCatalog(); ?>
 * </code>
 * @param bool $userStyle отключает стандартные стили, позволяет задать пользовательские
 */
function filterCatalog($userStyle = false) {
  if(!$userStyle) {
    if(MG::get('controller')=='controllers_catalog') {
      mgAddMeta('<link type="text/css" href="'.SCRIPT.'standard/css/jquery.ui.slider.css" rel="stylesheet"/>');
      mgAddMeta('<link type="text/css" href="'.SCRIPT.'standard/css/filter.css" rel="stylesheet"/>');
      mgAddMeta('<script src="'.SCRIPT.'standard/js/filter.js"></script>');
    }
  }
  echo MG::get('catalogfilter');
}

/**
 * Возвращает html код копирайта Moguta.CMS в футере сайта
 * <code>
 * copyrightMoguta();
 * </code>
 */
function copyrightMoguta() {
  $html = '';
  if (MG::getSetting('copyrightMoguta')=='true') { 
    $html = '<div class="powered"> Сайт работает на движке: 
      <a href="https://moguta.ru" target="_blank">
      Moguta<span class="red">CMS</span></a></div>';
  }
  echo $html;
}
/**
 * Добавляет фоновое изображение, если выбрано в настройках
 * <code>
 *   <body <?php backgroundSite(); ?>>
 * </code>
 */
function backgroundSite() {
  $backgr = (MG::getSetting('backgroundSite')!='')? SITE.MG::getSetting('backgroundSite'): '';
  if ($backgr) {
    $html = 'style="background: url('.SITE.(MG::getSetting('backgroundSite')).') no-repeat fixed center center /100% auto #fff;" ';
    echo $html;
  }
}

/**
 * Возвращает true, если открыта главная страница
 * <code>
 * $result = isIndex();
 * var_dump($result);
 * </code>
 * @return bool
 */
function isIndex() {
  return (MG::get('controller') == 'controllers_index') ? true: false;
}

/**
 * Возвращает true, если открыта страница каталога
 * <code>
 * $result = isCatalog();
 * var_dump($result);
 * </code>
 * @return bool
 */
function isCatalog() {
  return (MG::get('controller') == 'controllers_catalog') ? true: false;
}

/**
 * Возвращает true, если открыта страница корзины
 * <code>
 * $result = isCart();
 * var_dump($result);
 * </code>
 * @return bool
 */
function isCart() {
  return (MG::get('controller') == 'controllers_cart') ? true: false;
}

/**
 * Возвращает true, если открыта страница заказа
 * <code>
 * $result = isOrder();
 * var_dump($result);
 * </code>
 * @return bool
 */
function isOrder() {
  return (MG::get('controller') == 'controllers_order') ? true: false;
}

/**
 * Возвращает true, если открыта страница поиска
 * <code>
 * $result = isSearch();
 * var_dump($result);
 * </code>
 * @return bool
 */
function isSearch() {
  return !empty($_GET['search']) ? true: false;
}
/**
 * Возвращает верстку горизонтального меню, если оно подключено в настройках
 * Использует 'layout_horizontmenu' текущего шаблона.
 * <code>
 * echo horizontMenu();
 * </code>
 * @return string|bool
 */
function horizontMenu() {
  if (MG::getSetting('horizontMenu') == "true") {
    return layout('horizontmenu');
  }
  return false;
}
/**
 * Возвращает false, если горизонтальное меню подключено в настройках
 * <code>
 * $result = horizontMenuDisable();
 * var_dump($result);
 * </code>
 * @return bool
 */
function horizontMenuDisable() {
  if (MG::getSetting('horizontMenu') == "false") {
    return true;
  }
  return false;
}

/**
 * Возвращает значение флага, для опции вывода каталога на главной
 * <code>
 * $result = catalogToIndex();
 * var_dump($result);
 * </code>
 * @return bool
 */
function catalogToIndex() {
  if (MG::getSetting('catalogIndex') == 'true') {
    return true;
  }
  return false;
}
/**
 * Возвращает приписку с наценкой для способа оплаты
 * <code>
 *   echo mgGetPaymentRateTitle(1.1);
 * </code>
 * @param float $rate коэффициент наценки
 * @return string
 */
function mgGetPaymentRateTitle($rate) {
  $rateTitle = '';
  
  if(!empty($rate)) {
    $paymentRate = (abs($rate)*100).'%';

    if($rate > 0) {
      $rateTitle .= ' (Наценка '.$paymentRate.')';
    }else{
      $rateTitle .= ' (Скидка '.$paymentRate.')';
    }        
  }
  
  return $rateTitle;
}

/**
 * Возвращает части локализации шаблона.
 * Подгружает локализации из файла в папке locales, в соответствии с выбранным языком.
 * В скобках указывается соответствие из левой части 'save' => 'Сохранить'
 * <code>
 *   echo lang('save');
 * </code>
 * @param string $phrase ключ массива локализации
 * @return string
 */
function lang($phrase) {
  return $GLOBALS['templateLocale'][$phrase];
}

/**
 * Тоже самое, что и basename, но позволяет работать с кириллическими именами файлов.
 * <code>
 *   echo mb_basename('кириллица.jpg');
 * </code>
 * @param string $filename путь к файлу
 * @return string
 */
function mb_basename($filename) {
  return @array_pop(explode('/', $filename));
}