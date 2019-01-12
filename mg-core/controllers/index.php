<?php

/**
 * Контроллер: Catalog
 *
 * Класс Controllers_Catalog обрабатывает действия пользователей в каталоге интернет магазина.
 * - Формирует список товаров для конкретной страницы;
 * - Добавляет товар в корзину.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Controller
 */
class Controllers_Index extends BaseController {

  function __construct() {
    $settings = MG::get('settings');
    // Если нажата кнопка купить.
    $_REQUEST['category_id'] = URL::getQueryParametr('category_id');
    $_REQUEST['inCartProductId'] = intval($_REQUEST['inCartProductId']);

    if (!empty($_REQUEST['inCartProductId'])) {
      $cart = new Models_Cart;
      $property = $cart->createProperty($_POST);
      $cart->addToCart($_REQUEST['inCartProductId'], $_REQUEST['amount_input'], $property);
      SmalCart::setCartData();
      MG::redirect('/cart');
    }

    $countСatalogProduct = $settings['countСatalogProduct'];
    // Показать первую страницу выбранного раздела.
    $page = 1;

    // Запрашиваемая страница.
    if (isset($_REQUEST['p'])) {
      $page = $_REQUEST['p'];
    }
    
    $model = new Models_Catalog;
    $product = new Models_Product;

    // Получаем список вложенных категорий, для вывода всех продуктов, на страницах текущей категории.
    $model->categoryId = MG::get('category')->getCategoryList($_REQUEST['category_id']);

    // В конец списка, добавляем корневую текущую категорию.
    $model->categoryId[] = $_REQUEST['category_id'];

    // Передаем номер требуемой страницы, и количество выводимых объектов.
    $countСatalogProduct = 100;
    if (MG::getSetting('mainPageIsCatalog') == 'true') {
    $printCompareButton = MG::getSetting('printCompareButton');
    $actionButton = MG::getSetting('actionInCatalog') === "true" ? 'actionBuy' : 'actionView';
    // $dataGroupProducts = Storage::get('indexGroup-'.md5('dataGroupProductsIndexConroller'.LANG.$_SESSION['userCurrency']));
  
    $currencyRate = MG::getSetting('currencyRate');      
    $currencyShopIso = MG::getSetting('currencyShopIso'); 
    $randomProdBlock = MG::getSetting('randomProdBlock')=="true"? true: false;  

    $maxCountRecommend = MG::getSetting('countRecomProduct')?MG::getSetting('countRecomProduct'):0;
    $maxCountNew = MG::getSetting('countNewProduct')?MG::getSetting('countNewProduct'):0;
    $maxCountSales = MG::getSetting('countSaleProduct')?MG::getSetting('countSaleProduct'):0;
    $allMaxCount = $maxCountRecommend + $maxCountNew + $maxCountSales;

    // достаем id товаров, которые должны будут показаны
    $sort = "p.sort";
    if($randomProdBlock) $sort = "RAND()";
    $ids = array();
    // достаем товары, которые должны быть в блоках
    $res = DB::query('SELECT DISTINCT p.id, p.sort FROM '.PREFIX.'product AS p LEFT JOIN '.PREFIX.'product_variant AS pv ON p.id = pv.product_id
      WHERE p.recommend = 1 and p.activity=1 ORDER BY '.$sort.' ASC LIMIT '.$maxCountRecommend);
    while ($row = DB::fetchAssoc($res)) {
      $ids[] = $row['id'];
    }
    $res = DB::query('SELECT DISTINCT p.id, p.sort FROM '.PREFIX.'product AS p LEFT JOIN '.PREFIX.'product_variant AS pv ON p.id = pv.product_id
      WHERE p.new = 1 and p.activity=1 ORDER BY '.($randomProdBlock?$sort.' ASC':'id DESC').' LIMIT '.$maxCountNew);
    while ($row = DB::fetchAssoc($res)) {
      $ids[] = $row['id'];
    }
    $res = DB::query('SELECT DISTINCT p.id, p.sort FROM '.PREFIX.'product AS p LEFT JOIN '.PREFIX.'product_variant AS pv ON p.id = pv.product_id
      WHERE (p.old_price>p.price_course || pv.old_price>pv.price_course) and p.activity=1 ORDER BY '.$sort.' ASC LIMIT '.$maxCountSales);
    while ($row = DB::fetchAssoc($res)) {
      $ids[] = $row['id'];
    }
    
    if ($dataGroupProducts == null) {
      $onlyInCount = '';
      
      DB::query('SELECT `system_set` FROM `'.PREFIX.'product`');
      // Формируем список товаров для блока рекомендуемой продукции.
      // $sort = $randomProdBlock ? "RAND()" : "p.sort";   
      

      // Формируем список товаров со старой ценой.
      $productsList = $model->getListByUserFilter($allMaxCount, ' p.id IN ('.DB::quoteIN($ids).')');
      $productsList['catalogItems'] = MG::loadWholeSalesToCatalog($productsList['catalogItems']);
      // дропаем товары, которых нет в наличии
      $productsList['catalogItems'] = MG::clearProductBlock($productsList['catalogItems']);
      
      // viewData($productsList['catalogItems']);

      // viewData(count($productsList['catalogItems']));
      $recommendCounter = 0;
      $newCounter = 0;
      $salesCounter = 0;

      // viewData($productsList['catalogItems']);

      foreach ($productsList['catalogItems'] as &$item) {
        // viewData($item);
        for($i = 0; $i < count($item['variants']); $i++) {
          if($item['variants'][$i]['count'] == 0) {
            $item['variants'][] = $item['variants'][$i];
            unset($item['variants'][$i]);
          }
        }

        $item['variants'] = array_values($item['variants']);
        
        $imagesUrl = explode("|", $item['image_url']);
        $item["image_url"] = "";
        if (!empty($imagesUrl[0])) {
          $item["image_url"] = $imagesUrl[0];
        }

        if(MG::getSetting('showMainImgVar') == 'true') {
          if($item['variants'][0]['image'] != '') {
            $img = explode('/', $item["image_url"]);
            $img = end($img);
            $item['images_product'][0] = str_replace($img, $item['variants'][0]['image'], $item['image_url']);
          }
        }

        if (!empty($item['variants'])) {
          $item["price"] = MG::numberFormat($item['variants'][0]["price_course"]);
          $item["old_price"] = $item['variants'][0]["old_price"];
          $item["count"] = $item['variants'][0]["count"];
          $item["code"] = $item['variants'][0]["code"];
          $item["weight"] = $item['variants'][0]["weight"];
          $item["price_course"] = $item['variants'][0]["price_course"];
          $item["variant_exist"] = $item['variants'][0]["id"];
        }
        else{
          $item["price_course"] = MG::convertPrice($item["price_course"]);
        }
        if (MG::numberDeFormat($item["price_course"]) > MG::numberDeFormat($item['old_price'])) {
          $item['old_price'] = 0;
        }

        $item['currency_iso'] = $item['currency_iso']?$item['currency_iso']:$currencyShopIso;
        $item['old_price'] = $item['old_price']? MG::priceCourse($item['old_price']):0;
        $item['price'] =  MG::priceCourse($item['price_course']); 
        if($printCompareButton!='true') {
          $item['actionCompare'] = '';         
        }    
        if($actionButton=='actionBuy' && $item['count']==0) {
          $item['actionBuy'] = $item['actionView'];         
        }

        // Легкая форма без характеристик.
        $blocksVariants = $product->getBlockVariants($item['id']);

        if (($item['count'] == 0&&empty($item['variants']))||
          (!empty($item['variants'])&&$item['variants'][0]['count'] == 0)||(MG::getSetting('actionInCatalog')=='false')) {
          $buyButton = MG::layoutManager('layout_btn_more', $item);
        } else {
          $buyButton = MG::layoutManager('layout_btn_buy', $item);
        }
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
          'blockVariants' => $blocksVariants,
          'buyButton' => $buyButton
        ));
        $item['buyButton']= $liteFormData['html'];

        if($item['recommend'] == 1) {
          if($recommendCounter < $maxCountRecommend) {
            $recommendProducts['catalogItems'][] = $item;
          }
          $recommendCounter++;
        }

        if($item['new'] == 1) {
          if($newCounter < $maxCountNew) {
            $newProducts['catalogItems'][] = $item;
          }
          $newCounter++;
        }

        if($item['old_price'] != 0) {
          if($salesCounter <= $maxCountSales) {
            $saleProducts['catalogItems'][] = $item;
          }
          $salesCounter++;
        }
      }

      if($randomProdBlock) {
        shuffle($recommendProducts['catalogItems']);
        shuffle($newProducts['catalogItems']);
        shuffle($saleProducts['catalogItems']);
      }
      else{
        usort($newProducts['catalogItems'], function ($a, $b) {
          return $a['id'] < $b['id'];
        });
      }

      $dataGroupProducts['recommendProducts'] = $recommendProducts;
      $dataGroupProducts['newProducts'] = $newProducts;
      $dataGroupProducts['saleProducts'] = $saleProducts;
      
      // Storage::save('indexGroup-'.md5('dataGroupProductsIndexConroller'.LANG.$_SESSION['userCurrency']), $dataGroupProducts);
    }

    // подгружаем независимое количество
    // $data = array();
    // if(MG::enabledStorage()) {
    //   $res = DB::query('SELECT SUM(count), product_id, variant_id FROM '.PREFIX.'product_on_storage WHERE product_id IN ('.((implode(',', $ids)!='')?(implode(',', $ids)):'\'\'').')
    //     GROUP BY product_id, storage, variant_id');
    //   while ($row = DB::fetchAssoc($res)) {
    //     $data[$row['product_id']][$row['variant_id']] = $row['SUM(count)']; 
    //   }
    // } else {

    // }   
    // foreach ($dataGroupProducts as $key => $value) {
    //   foreach ($value['catalogItems'] as $key2 => $value2) {
    //     if(!empty($value2['variants'])) {
    //       if(!empty($data[$value['catalogItems']['id']][$value2['id']])) {
    //         // $dataGroupProducts[$key]['catalogItems'][$key2]['count'] = $data[$value['catalogItems']['id']][$value2['id']];
    //       }
    //     }
    //   }

    // }
    
    $recommendProducts = $dataGroupProducts['recommendProducts'];
    $newProducts = $dataGroupProducts['newProducts'];
    $saleProducts = $dataGroupProducts['saleProducts'];
    }
    $html = MG::get('pages')->getPageByUrl('index');
    MG::loadLocaleData($html['id'], LANG, 'page', $html);
    
    if(!empty($html)) {
      $html['html_content'] = MG::inlineEditor(PREFIX.'page', "html_content", $html['id'], $html['html_content']);
    } else {
      $html['html_content'] = '';    
    }
    $this->data = array(
      'recommendProducts' => !empty($recommendProducts['catalogItems'])&&MG::getSetting('countRecomProduct') ? $recommendProducts['catalogItems'] : array(),
      'newProducts' => !empty($newProducts['catalogItems'])&&MG::getSetting('countNewProduct') ? $newProducts['catalogItems'] : array(),
      'saleProducts' => !empty($saleProducts['catalogItems'])&&MG::getSetting('countSaleProduct') ? $saleProducts['catalogItems'] : array(),
      'titeCategory' => $html['meta_title'],
      'cat_desc' => $html['html_content'],
      'meta_title' => $html['meta_title'],
      'meta_keywords' => $html['meta_keywords'],
      'meta_desc' => $html['meta_desc'],
      'currency' => $settings['currency'],
      'actionButton' => $actionButton
    );
  }

}