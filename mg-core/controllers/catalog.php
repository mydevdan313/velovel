<?php

/**
 * Контроллер: Catalog
 *
 * Класс Controllers_Catalog обрабатывает действия пользователей в каталоге интернет-магазина.
 * - Формирует список товаров для конкретной страницы;
 * - Добавляет товар в корзину.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Controller
 */
class Controllers_Catalog extends BaseController {

  function __construct() {
    $settings = MG::get('settings');
    $lang = MG::get('lang');
    // Если нажата кнопка купить.
    $_REQUEST['category_id'] = URL::getQueryParametr('category_id');
    $_REQUEST['inCartProductId'] = intval($_REQUEST['inCartProductId']);

    if (!empty($_REQUEST['inCartProductId'])) {
      // для редиректа
      if(LANG != 'LANG' && LANG != 'default') {
        $lang = '/'.LANG;
      } else {
        $lang = '';
      }

      $cart = new Models_Cart;
      // Если параметров  товара не передано     
      // возможно была нажата кнопка купить из мини карточки, 
      // в этом случае самостоятельно вычисляем набор
      // параметров, которые были бы указаны при открытии карточки товара.
      if (empty($_POST) || (isset($_POST['updateCart']) && isset($_POST['inCartProductId']) && count($_POST) == 2)) {

        $modelProduct = new Models_Product;
        $modelProduct->storage = 'all';
        $product = $modelProduct->getProduct($_REQUEST['inCartProductId']);

        if (empty($product)) {
          MG::redirect($lang.'/404');
          exit;
        }

        $blockVariants = $modelProduct->getBlockVariants($product['id']);

        $blockedProp = $modelProduct->noPrintProperty();

        $propertyFormData = $modelProduct->createPropertyForm($param = array(
          'id' => $product['id'],
          'maxCount' => $product['count'],
          'productUserFields' => $product['thisUserFields'],
          'action' => "/catalog",
          'method' => "POST",
          'ajax' => true,
          'blockedProp' => $blockedProp,
          'noneAmount' => false,
          'titleBtn' => MG::getSetting('buttonBuyName'),
          'blockVariants' => $blockVariants,
          'currency_iso' => $product['currency_iso'],
        ));

        $_POST = $propertyFormData['defaultSet'];
        $_POST['inCartProductId'] = $product['id'];
      }

      $property = $cart->createProperty($_POST);
      
      if (!empty($_REQUEST['isLanding'])) {
        $cart->addToCart($_REQUEST['inCartProductId'], $_REQUEST['amount_input'], $property, $_REQUEST['variant']);
        SmalCart::setCartData();
        MG::redirect($lang.'/order');
      } else {
        $cart->addToCart($_REQUEST['inCartProductId'], $_REQUEST['amount_input'], $property);
        SmalCart::setCartData();

        MG::redirect($lang.'/cart');
      }
    }

    if (!empty($_REQUEST['fastsearch'])) {
      $this->getSearchData();
    }

    $countСatalogProduct = $settings['countСatalogProduct'];
    // Показать первую страницу выбранного раздела.
    $page = 1;

    // Запрашиваемая страница.
    if (isset($_REQUEST['p'])) {
      $page = $_REQUEST['p'];
    }

    $model = new Models_Catalog;

    $number = URL::getQueryParametr('cnt');

    if(!$number) {
    	if (isset($_SESSION["cnt"])) {
    		$number = $_SESSION["cnt"];
    	} else {
    		$number = $settings['countСatalogProduct'];
    		$_SESSION["cnt"] = $number;
    	}
    } else {
    	$_SESSION["cnt"] = $number;
    }

    if($number=="all") $number = 10000000;

    $filters = URL::getQueryParametr('filters');

    if(empty($filters) && !isset($_REQUEST["sorter"])) {
      $filter = explode("|", MG::getSetting('filterSort'));
      $filters = $filter[0]."|";
      
      if ($filter[1] == "asc") {
        $filters .= "-1";
      } else {
        $filters .= "1";
      }
    }

    if(!$filters) {
      if (isset($_SESSION["filters"]) && !isset($_REQUEST["sorter"])) {
        $filters = $_SESSION["filters"];
      }
      elseif(isset($_REQUEST["sorter"])) {
        $filters = $_REQUEST["sorter"];
        $_SESSION["filters"] = $filters;
      }
    } else {
      $_SESSION["filters"] = $filters;
    }

    // Если происходит поиск по ключевым словам.
    $keyword = MG::defenderXss_decode(urldecode(URL::getQueryParametr('search')));

    if (!empty($keyword)) {
      $keyword = $this->convertLang($keyword);
      $items = $model->getListProductByKeyWord($keyword, false, true, false, 'groupBy');
      $searchData = array('keyword' => URL::getQueryParametr('search'), 'count' => $items['numRows']);
    } else {
      $_REQUEST['category_id'] = intval($_REQUEST['category_id']);
      // Получаем список вложенных категорий, 
      // для вывода всех продуктов, на страницах текущей категории.           
      if (empty($_REQUEST['category_id'])) {
        $_REQUEST['category_id'] = 0;
      }

      $model->categoryId = MG::get('category')->getCategoryList($_REQUEST['category_id']);

      // В конец списка, добавляем корневую текущую категорию.
      $model->categoryId[] = $_REQUEST['category_id'];

      // Записываем в глобальную переменную список всех вложенных категорий, 
      // чтобы использовать в других местах кода, например в фильтре по характеристикам
      $_REQUEST['category_ids'] = $model->categoryId;
      // Передаем номер требуемой страницы, и количество выводимых объектов.
      if(!$number) {
        $countСatalogProduct = $settings['countСatalogProduct'];
        $number = $countСatalogProduct;
      }
      $items = $model->getList($settings['countСatalogProduct'], false, true);
      // viewdata($items);
    }
    // Если с фильтра пришел запрос только на количество позиций.
    if (!empty($_REQUEST['getcount']) && !empty($_REQUEST['filter'])) {
      $result['count'] = $items['totalCountItems'] ? $items['totalCountItems'] : 0;
      $result['lang']['product'] = lang('filterProductResult');
      $result['lang']['unit'] = lang('filterUnitResult');
      $result['lang']['show'] = lang('filterShowResult');
      if(MG::getSetting('disabledPropFilter') != 'false') {
        $result['htmlProp'] = $items['htmlProp'];
      } else {
        $result['htmlProp'] = 'false';
      }
      
      echo json_encode($result);
      exit();
    }
    
    $settings = MG::get('settings');
    if (empty($items['catalogItems'])) {
      $items['catalogItems'] = array();
    } else {
      foreach ($items['catalogItems'] as $item) {
        if ($item['id']) {
          $productIds[] = $item['id'];
        }
      }


      $product = new Models_Product;
      $blocksVariants = empty($productIds) ? null : $product->getBlocksVariantsToCatalog($productIds);

      $blockedProp = $product->noPrintProperty();
      $actionButton = MG::getSetting('actionInCatalog') === "true" ? 'actionBuy' : 'actionView';
      
      foreach ($items['catalogItems'] as $k => $item) {
        for($i = 0; $i < count($item['variants']); $i++) {
          if($item['variants'][$i]['count'] == 0) {
            $item['variants'][] = $item['variants'][$i];
            unset($item['variants'][$i]);
          }
        }
        $items['catalogItems'][$k]['variants'] = array_values($item['variants']);

        $imagesUrl = explode("|", $item['image_url']);
        $items['catalogItems'][$k]["image_url"] = "";

        if (!empty($imagesUrl[0])) {
          $items['catalogItems'][$k]["image_url"] = $imagesUrl[0];
        }

        $items['catalogItems'][$k]['title'] = MG::modalEditor('catalog', $item['title'], 'edit', $item["id"]);
        if (($items['catalogItems'][$k]['count'] == 0&&empty($items['catalogItems'][$k]['variants']))||
          (!empty($items['catalogItems'][$k]['variants'])&&$items['catalogItems'][$k]['variants'][0]['count'] == 0)||
          (MG::getSetting('actionInCatalog')=='false')) {
          $buyButton = MG::layoutManager('layout_btn_more', $items['catalogItems'][$k]);
        } else {
          $buyButton = MG::layoutManager('layout_btn_buy', $items['catalogItems'][$k]);
        }

        if(MG::getSetting('showMainImgVar') == 'true') {
          if($item['variants'][0]['image'] != '') {
            $img = explode('/', $items['catalogItems'][$k]['images_product'][0]);
            $img = end($img);
            $items['catalogItems'][$k]['images_product'][0] = str_replace($img, $item['variants'][0]['image'], $items['catalogItems'][$k]['images_product'][0]);
          }
        }

        // Легкая форма без характеристик.
        $liteFormData = $product->createPropertyForm($param = array(
          'id' => $item['id'],
          'maxCount' => $item['count'],
          'productUserFields' => null,
          'action' => "/catalog",
          'method' => "POST",
          'ajax' => true,
          'blockedProp' => $blockedProp,
          'noneAmount' => true,
          'titleBtn' => "В корзину",
          'blockVariants' => $blocksVariants[$item['id']],
          'buyButton' => $buyButton
        ));

        $items['catalogItems'][$k]['liteFormData'] = $liteFormData['html'];
        $buyButton = $items['catalogItems'][$k]['liteFormData'];
        $items['catalogItems'][$k]['buyButton'] = $buyButton;

        // viewData($item['variants']);
      }
    }

    $categoryDescRes = MG::get('category')->getDesctiption($_REQUEST['category_id']);
    
    if ($_REQUEST['category_id']) {
      $categoryDesc = MG::inlineEditor(PREFIX.'category', "html_content", $_REQUEST['category_id'], $categoryDescRes['html_content']);
      $categoryDescSeo = MG::inlineEditor(PREFIX.'category', "seo_content", $_REQUEST['category_id'],  $categoryDescRes['seo_content']);
    }

    if ($categoryDescSeo == '&nbsp;') {$categoryDescSeo = '';}
   
    $catImg = MG::get('category')->getImageCategory($_REQUEST['category_id']);

    $pageCat = URL::get("page") ? URL::get("page") : 1;

    MG::loadLocaleData($model->currentCategory['id'], LANG, 'category', $model->currentCategory);

    $catTitle = $model->currentCategory['title'];
    
    $data = array(
      'items' => $items['catalogItems'],
      'titeCategory' => $catTitle,//$model->currentCategory['title'],
      'cat_desc' => $pageCat > 1 && (MG::getSetting('duplicateDesc')=='false') ? '': $categoryDesc,
      'cat_img' => $pageCat > 1 && (MG::getSetting('duplicateDesc')=='false') ? '': $catImg,
      'cat_id' => intval($_REQUEST['category_id']) ? intval($_REQUEST['category_id']) : 0,
      'filterBar' => $items['filterBarHtml'],
      'applyFilter' => $items['applyFilterList'],
      'totalCountItems' => $items['totalCountItems'],
      'pager' => $items['pager'],
      'searchData' => empty($searchData) ? '' : $searchData,
      'meta_title' => $model->currentCategory['meta_title'],
      'meta_keywords' =>$model->currentCategory['meta_keywords'],
      'meta_desc' => $model->currentCategory['meta_desc'],
      'currency' => $settings['currency'],
      'actionButton' => $actionButton,
      'cat_desc_seo' => $pageCat > 1 && (MG::getSetting('duplicateDesc')=='false') ? '': $categoryDescSeo,
      'seo_alt' => $model->currentCategory['seo_alt'],
      'seo_title' => $model->currentCategory['seo_title'],
    ); 
    if (URL::isSection('catalog')||(((MG::getSetting('catalogIndex')=='true') && (URL::isSection('index') || URL::isSection(''))))) {
      $html = MG::get('pages')->getPageByUrl('catalog');
      $html['html_content'] = MG::inlineEditor(PREFIX.'page', "html_content", $html['id'], $html['html_content']);
      $data['meta_title'] = $html['meta_title'] ? $html['meta_title'] : $html['title'];
      $data['meta_title'] = $data['meta_title'] ? $data['meta_title'] : $model->currentCategory['title'];
      $data['meta_keywords'] = $html['meta_keywords'];
      $data['meta_desc'] = $html['meta_desc'];
      $data['cat_desc'] = $html['html_content'];
      $data['cat_desc_seo'] = $html['seo_content'];
      $data['titeCategory'] = $html['title'];      
    }
    if ($keyword) {
      $data['meta_title'] = 'Поиск по фразе: '.URL::getQueryParametr('search');
    }    
    
    $seoTmpl = Seo::getMetaByTemplate('catalog', $data);
    $seoData = Urlrewrite::getSeoDataFotUrl();

    if(!empty($seoTmpl) || !empty($seoData)){

      if(empty($seoData)){
        $seoData = $seoTmpl;
      } else {
        $seoUrlShort = true;
      }   
      foreach ($seoData as $key => $value) {
        if(!$seoUrlShort) {
		      if($model->currentCategory['id']==0) continue;
        }
        if(!empty($value)){
          $data[$key] = empty($model->currentCategory[$key]) ? $value : $model->currentCategory[$key];
          if ($seoUrlShort) {
            switch ($key) {
              case 'meta_title': $data[$key] = $value; break;
              case 'meta_keywords':$data[$key] = $value; break;
              case 'meta_desc': 
                $desc = strip_tags($value); 
                $data[$key] = mb_substr($desc, 0,160); 
                break;          
            }
          }
        } elseif(empty($model->currentCategory[$key])) {
          switch ($key) {
            case 'meta_title':$data[$key] = $model->currentCategory['title']; break;
            case 'meta_keywords':$data[$key] = $model->currentCategory['title'].",".$lang['META_BUY']; break;
            case 'meta_desc': 
              $desc = strip_tags($data['cat_desc']); 
              $data[$key] = mb_substr($desc, 0,160); 
              break;          
          }
        }
      }  
    }  
    $currencyRate = MG::getSetting('currencyRate');  
    foreach ($data['items'] as $key => $product) {
      if (!empty($product['variants'])) {
        $data['items'][$key]["price"] = MG::numberFormat($product['variants'][0]["price_course"]);
        $data['items'][$key]["old_price"] = $product['variants'][0]["old_price"];
        $data['items'][$key]["count"] = $product['variants'][0]["count"];
        $data['items'][$key]["code"] = $product['variants'][0]["code"];
        $data['items'][$key]["weight"] = $product['variants'][0]["weight"];
        $data['items'][$key]["price_course"] = $product['variants'][0]["price_course"];
        $data['items'][$key]["variant_exist"] = $product['variants'][0]["id"];
      }
      if (MG::numberDeFormat($data['items'][$key]["price"]) > MG::numberDeFormat($data['items'][$key]["old_price"])) {
        $data['items'][$key]["old_price"] = 0;
      }
    }

    // загружаем локализацию для корневой каталога
    if($data['id'] == 0) {
      $tmpArr['title'] = $data['titeCategory'];
      $tmpArr['html_content'] = $data['cat_desc'];
      // получаем id страницы
      $res = DB::query('SELECT id FROM '.PREFIX.'page WHERE url = '.DB::quote(URL::getLastSection()));
      while($row = DB::fetchAssoc($res)) {
        $id = $row['id'];
      }
      $data['titeCategory'] = $tmpArr['title'];
      $data['cat_desc'] = $tmpArr['html_content'];
    }

    $this->data = $data;
  }

  /**
   * Конвертирует текст в поиске в правильную раскладку.
   * @param string $text - текст который необходимо конвертировать.
   * @return string
   */
  public function convertLang($text) {

    $php = explode('.', phpversion());

    if ($php[0] < 5) {
      return $text;
    }
    if ($php[1] < 3) {
      return $text;
    }

    require_once (CORE_JS.'langcorrect/ReflectionTypeHint.php');
    require_once (CORE_JS.'langcorrect/UTF8.php');
    require_once (CORE_JS.'langcorrect/Text/LangCorrect.php');

    $corrector = new Text_LangCorrect();
    $text = $corrector->parse($text, 2);
    return $text;
  }

  /**
   * Получает список продуктов при вводе в поле поиска.
   */
  public function getSearchData() {
    $keyword = MG::defenderXss_decode(URL::getQueryParametr('text'));
    if (!empty($keyword)) {
      $keyword = $this->convertLang($keyword);

      $catalog = new Models_Catalog;
      $items = $catalog->getListProductByKeyWord($keyword, true, true, false, 'groupBy');

      foreach ($items['catalogItems'] as $key => $value) {
        $items['catalogItems'][$key]['image_url'] = mgImageProductPath($value["image_url"], $value['id'], 'small');
      }

      $html = MG::layoutManager('layout_fast_search', array('items' => $items['catalogItems'], 'keyword' => trim($keyword), 'count' => $items['numRows'], 'currency' => MG::getSetting('currency')));

      $searchData = array(
        'status' => 'success',
        'html' => $html,
        'item' => array(
          'keyword' => URL::getQueryParametr('text'),
          'count' => $items['numRows'],
          'items' => $items,
        ),
        'currency' => MG::getSetting('currency')
      );
    }

    echo json_encode($searchData);
    exit;
  }

}