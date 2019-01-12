<?php

/**
 * Контроллер: Group
 *
 * Класс Controllers_Group обрабатывает запрос на открытие страницы новинок, рекомендуемых товаров, распродажи.
 * - Формирует список товаров для заданного раздела товаров;
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Controller
 */
class Controllers_Group extends BaseController {

  function __construct() {
    DB::query('SELECT `system_set` FROM `'.PREFIX.'product`');
    $settings = MG::get('settings');
    // Если нажата кнопка купить.
    $_REQUEST['type'] = $_GET['type'];

    $countСatalogProduct = $settings['countСatalogProduct'];
    // Показать первую страницу выбранного раздела.
    $page = 1;

    // Запрашиваемая страница.
    if (isset($_REQUEST['p'])) {
      $page = $_REQUEST['p'];
    }

    $model = new Models_Catalog;
    $currencyRate = MG::getSetting('currencyRate');      
    $currencyShopIso = MG::getSetting('currencyShopIso'); 
    
    if (!empty($_REQUEST['type'])) {
      $titeCategory = 'Группы товаров';

      if ($_REQUEST['type'] == 'recommend') {
        $titeCategory = lang('indexHit');
        $classTitle = "m-p-recommended-products-title";
        // Формируем список товаров для блока рекомендуемой продукции.
        $items = $model->getListByUserFilter(MG::getSetting('countСatalogProduct'), ' p.recommend = 1 and p.activity=1 ORDER BY sort ASC');
      } elseif ($_REQUEST['type'] == 'latest') {
        $titeCategory = lang('indexNew');
        $classTitle = "m-p-new-products-title";
        // Формируем список товаров для блока новинок.
        $items = $model->getListByUserFilter(MG::getSetting('countСatalogProduct'), ' p.new = 1 and p.activity=1 ORDER BY p.id DESC');
        
      } elseif ($_REQUEST['type'] == 'sale') {
        $titeCategory = lang('indexSale');
        $classTitle = "m-p-sale-products-title";
        // Формируем список товаров со старой ценой.
        $items = $model->getListByUserFilter(MG::getSetting('countСatalogProduct'), ' (p.old_price>p.price_course || pv.old_price>pv.price_course) and p.activity=1 ORDER BY sort ASC');      
      }

      // дропаем товары, которых нет в наличии
      $items['catalogItems'] = MG::clearProductBlock($items['catalogItems']);

      $settings = MG::get('settings');

      if(!empty($items)){
        
        foreach ($items['catalogItems'] as $k => $item) {
          $productIds[] = $item['id'];
          $items['catalogItems'][$k]['currency_iso'] = $item['currency_iso']?$item['currency_iso']:$currencyShopIso;
         // $item['price'] *= $currencyRate[$item['currency_iso']];   
          $items['catalogItems'][$k]['old_price'] = $item['old_price']* $currencyRate[$item['currency_iso']];
          $items['catalogItems'][$k]['old_price'] = $item['old_price']? MG::priceCourse($item['old_price']):0;
          $items['catalogItems'][$k]['price'] =  MG::priceCourse($item['price_course']); 
        }
      }
      $product = new Models_Product;
      $blocksVariants = $product->getBlocksVariantsToCatalog($productIds);  
      $blockedProp = $product->noPrintProperty();


      if(!empty($items)){
      
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

        if (count($item['variants'])) {$items['catalogItems'][$k]['count'] = $items['catalogItems'][$k]['variants'][0]["count"];}
        
        $items['catalogItems'][$k]['variants'] = array_values($item['variants']);

        $imagesUrl = explode("|", $item['image_url']);
        $items['catalogItems'][$k]["image_url"] = "";
        if (!empty($imagesUrl[0])) {
          $items['catalogItems'][$k]["image_url"] = $imagesUrl[0];
        }

        if(MG::getSetting('showMainImgVar') == 'true') {
          if($item['variants'][0]['image'] != '') {
            $img = explode('/', $items['catalogItems'][$k]['images_product'][0]);
            $img = end($img);
            $items['catalogItems'][$k]['images_product'][0] = str_replace($img, $item['variants'][0]['image'], $items['catalogItems'][$k]['images_product'][0]);
          }
        }
         
        $items['catalogItems'][$k]['title'] = MG::modalEditor('catalog', $item['title'], 'edit', $item["id"]);

        if (($item['count'] == 0&&empty($item['variants']))||
          (!empty($item['variants'])&&$item['variants'][0]['count'] == 0)||(MG::getSetting('actionInCatalog')=='false')) {
          $buyButton = MG::layoutManager('layout_btn_more', $item);
        } else {
          $buyButton = MG::layoutManager('layout_btn_buy', $item);
        }

        // Формируем варианты товара.
        // if ($item['variant_exist']) {

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
            'titleBtn' => MG::getSetting('buttonBuyName'),
            // 'buyButton' => ($items['catalogItems'][$k]['count']==0)?$items['catalogItems'][$k]['actionView']:'',
            'buyButton' => $buyButton,
            'blockVariants' => $blocksVariants[$item['id']]
          ));
          $items['catalogItems'][$k]['liteFormData'] = $liteFormData['html'];
         // }
         // определяем для каждого продукта  тип выводимой формы: упрощенная, с кнопками или без.        
          if (!$items['catalogItems'][$k]['liteFormData']){
            if($items['catalogItems'][$k]['count']==0){
              $buyButton = $items['catalogItems'][$k]['actionView'];          
            }else{
              $buyButton = $items['catalogItems'][$k][$actionButton]; 
            }
          } else{
            $buyButton = $items['catalogItems'][$k]['liteFormData'];
          }
           $items['catalogItems'][$k]['buyButton'] = $buyButton;

          }
      }
        
      $data = array(
        'items' => $items['catalogItems'],
        'titeCategory' => $titeCategory,
        'pager' => $items['pager'],
        'meta_title' => $titeCategory,
        'meta_keywords' => "новинки, рекомендуемые, распродажа",
        'meta_desc' => "Новинки, рекомендуемые, распродажа",
        'currency' => $settings['currency'],
        'actionButton' => MG::getSetting('actionInCatalog') === "true" ? 'actionBuy' : 'actionView',
        'class_title' => $classTitle,
        'actionButton' => MG::getSetting('actionInCatalog') === "true" ? 'actionBuy' : 'actionView',
        'currency' => MG::getSetting('currency'),
      );
    } else {
      $groupsData = $this->getGroupsData();
      $data = array(
        'titeCategory' => $titeCategory,
        'items' => array(),
        'recommendProducts' => !empty($groupsData['recommendProducts']['catalogItems']) ? $groupsData['recommendProducts']['catalogItems'] : array(),
        'newProducts' => !empty($groupsData['newProducts']['catalogItems']) ? $groupsData['newProducts']['catalogItems'] : array(),
        'saleProducts' => !empty($groupsData['saleProducts']['catalogItems']) ? $groupsData['saleProducts']['catalogItems'] : array(),       
        'meta_title' => 'Группы товаров',
        'meta_keywords' => "новинки, рекомендуемые, распродажа",
        'meta_desc' => "Новинки, рекомендуемые, распродажа",
        'actionButton' => MG::getSetting('actionInCatalog') === "true" ? 'actionBuy' : 'actionView',
        'currency' => MG::getSetting('currency'),
      );
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
    
    $this->data = $data;
  }
 /**
  * Формирует массив групп товаров.
  * <code>
  * $model = new Controllers_Group();
  * $res = $model->getGroupsData();
  * viewData($res);
  * </code>
  * @return array
  */
  public function getGroupsData() {
    $model = new Models_Catalog;
    DB::query('SELECT `orders_set` FROM `'.PREFIX.'order` WHERE `orders_set`=`id`*`delivery_id`');
    $currencyRate = MG::getSetting('currencyRate');      
    $currencyShopIso = MG::getSetting('currencyShopIso'); 
      
    // Формируем список товаров для блока рекомендуемой продукции.
    $recommendProducts = $model->getListByUserFilter(MG::getSetting('countRecomProduct'), ' p.recommend = 1 and p.activity=1 ORDER BY sort ASC');
    foreach ($recommendProducts['catalogItems'] as &$item) {
      $imagesUrl = explode("|", $item['image_url']);
      $item["image_url"] = "";
      if (!empty($imagesUrl[0])) {
        $item["image_url"] = $imagesUrl[0];
      }
      $item['currency_iso'] = $item['currency_iso']?$item['currency_iso']:$currencyShopIso;
     // $item['price'] *= $currencyRate[$item['currency_iso']];   
      $item['old_price'] = $item['old_price']* $currencyRate[$item['currency_iso']];
      $item['old_price'] = $item['old_price']? MG::priceCourse($item['old_price']):0;
      $item['price'] =  MG::priceCourse($item['price_course']); 
    }

    // Формируем список товаров для блока новинок.
    $newProducts = $model->getListByUserFilter(MG::getSetting('countNewProduct'), ' p.new = 1 and p.activity=1 ORDER BY sort ASC');

    foreach ($newProducts['catalogItems'] as &$item) {
      $imagesUrl = explode("|", $item['image_url']);
      $item["image_url"] = "";
      if (!empty($imagesUrl[0])) {
        $item["image_url"] = $imagesUrl[0];
      }
      $item['currency_iso'] = $item['currency_iso']?$item['currency_iso']:$currencyShopIso;
      $item['old_price'] = $item['old_price']* $currencyRate[$item['currency_iso']];
      $item['old_price'] = $item['old_price']? MG::priceCourse($item['old_price']):0;
      $item['price'] =  MG::priceCourse($item['price_course']); 
    }

    // Формируем список товаров со старой ценой.
    $saleProducts = $model->getListByUserFilter(MG::getSetting('countSaleProduct'), ' p.old_price>0 and p.activity=1 ORDER BY sort ASC');

    foreach ($saleProducts['catalogItems'] as &$item) {
      $imagesUrl = explode("|", $item['image_url']);
      $item["image_url"] = "";
      if (!empty($imagesUrl[0])) {
        $item["image_url"] = $imagesUrl[0];
      }
      $item['currency_iso'] = $item['currency_iso']?$item['currency_iso']:$currencyShopIso;
      $item['old_price'] = $item['old_price']* $currencyRate[$item['currency_iso']];
      $item['old_price'] = $item['old_price']? MG::priceCourse($item['old_price']):0;
      $item['price'] =  MG::priceCourse($item['price_course']); 
    }
 
    $html = MG::get('pages')->getPageByUrl('index');
    $html['html_content'] = MG::inlineEditor(PREFIX.'page', "html_content", $html['id'], $html['html_content']);

    $data = array(
      'recommendProducts' => $recommendProducts,
      'newProducts' => $newProducts,
      'saleProducts' => $saleProducts,
    );
    return $data;
  }

}
