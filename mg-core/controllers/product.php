<?php
/**
 * Контроллер Product
 *
 * Класс Controllers_Product обрабатывает действия пользователей на странице товара.
 * - Пересчитывает стоимость товара.
 * - Подготавливает форму для вариантов товара.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Controller
 */
class Controllers_Product extends BaseController {

  function __construct() {
   
    $model = new Models_Product;
    
    $id = URL::getQueryParametr('id');

    // для редиректа
    if(LANG != 'LANG' && LANG != 'default') {
      $lang = '/'.LANG;
    } else {
      $lang = '';
    }
    
    if(empty($id) && empty($_REQUEST['calcPrice'])) {
      MG::redirect($lang.'/404');
      exit;
    }

    // Требуется только пересчет цены товара.
    if (!empty($_REQUEST['calcPrice'])) {
      $model->calcPrice();
      exit;
    }
    
    if ($product == null) {
      $settings = MG::get('settings');


      $product = $model->getProduct($id);
      
      if (empty($product)) {
        MG::redirect($lang.'/404');
        exit;
      } 

      // проверка на то, нужно ли показывать товар при неактивности
      if((USER::access('admin_zone') != 1) && ($product['activity'] != 1) && (PRODUCT_404 == 1)) {
        MG::redirect($lang.'/404');
        exit;
      }
            
      $product['currency'] = $settings['currency'];
      $blockVariants = $model->getBlockVariants($product['id'], $product['cat_id']);
      if ($blockVariants) {
        $variants = $model->getVariants($id, false, true);
        // оптовые цены грузим
        $product['variants'] = $variants;
        $product = MG::loadWholeSalesToCatalog(array($product));
        $product = $product[0];
        $variants = $product['variants'];
        
        if (!empty($variants)) {
          $variants = array_values($variants);
          foreach ($variants as $key => $value) {
            if($value['count'] == 0) {
              $tmp = $value;
              unset($variants[$key]);
              $variants[] = $tmp;
            }
          }
          $variants = array_values($variants);

          $firstVariant = array_shift($variants);
          $product['price'] = $firstVariant['price'];
          $product['old_price'] = $firstVariant['old_price'];
          $product['code'] = $firstVariant['code'];
          $product['count'] = $firstVariant['count'];
          $product['variant'] = $firstVariant['id'];

          if(MG::getSetting('showMainImgVar') == 'true') {
            if($firstVariant['image'] != '') {
              $img = explode('/', $product['images_product'][0]);
              $img = end($img);
              $product['images_product'][0] = str_replace($img, $firstVariant['image'], $product['images_product'][0]);
            }
          }

          $product['weight'] = $firstVariant['weight'];
          $product['price_course'] = $firstVariant['price_course'];
        }
      } else {
        $product = MG::loadWholeSalesToCatalog(array($product));
        $product = $product[0];
      }

      $blockedProp = $model->noPrintProperty();      
      $propertyFormData = $model->createPropertyForm($param = array(
        'id' => $product['id'],
        'maxCount' => $product['count'],
        'productUserFields' => $product['thisUserFields'],
        'action' => "/catalog",
        'method' => "POST",
        'ajax' => true,
        'blockedProp' => $blockedProp,
        'noneAmount' => false,
        // 'noneButton' => $product['count']!=0?false:true,
        'titleBtn' => MG::getSetting('buttonBuyName'),
        'blockVariants' => $blockVariants,
        'currency_iso' => $product['currency_iso'],
        'productData' => $product,
      ));      

      // Легкая форма без характеристик.   
      $liteFormData = $model->createPropertyForm($param = array(
        'id' => $product['id'],
        'maxCount' => $product['count'],
        'productUserFields' => null,
        'action' => "/catalog",
        'method' => "POST",
        'ajax' => true,
        'blockedProp' => $blockedProp,
        'noneAmount' => false,
        'noneButton' => $product['count']?false:true,
        'titleBtn' => MG::getSetting('buttonBuyName'),
        'blockVariants' => $blockVariants,
      ));

      $product['price_course']+=$propertyFormData['marginPrice'];
      $currencyRate = MG::getSetting('currencyRate');      
      $currencyShopIso = MG::getSetting('currencyShopIso');      
      $product['currency_iso'] = $product['currency_iso']?$product['currency_iso']:$currencyShopIso;
      $product['old_price'] = $product['old_price']? $product['old_price']:0;
      if (NULL_OLD_PRICE && $product['price_course'] > $product['old_price']) {
        $product['old_price'] = 0;
      }
      $product['price'] = MG::priceCourse($product['price_course']); 
      
      $product['propertyForm'] = $propertyFormData['html'];
      $product['propertyNodummy'] = $propertyFormData['propertyNodummy'];
      $product['stringsProperties'] = $propertyFormData['stringsProperties'];   
      $product['liteFormData'] = $liteFormData['html'];
      $product['description'] = MG::inlineEditor(PREFIX.'product', "description", $product['id'], $product['description']);
      $product['product_title'] = $product['title'];
      $product['title'] = MG::modalEditor('catalog', $product['title'], 'edit', $product["id"]);
      // Информация об отсутствии товара на складе.
      if (MG::getSetting('printRemInfo') == "true") {
        // $message = 'Здравствуйте, меня интересует товар "'.str_replace("'", "&quot;", $product['title']).'" с артикулом "'.$product['code'].'", но его нет в наличии.
        // Сообщите, пожалуйста, о поступлении этого товара на склад. '; 
        $message = MG::restoreMsg('msg__product_nonavaiable2',array('#PRODUCT#' => str_replace("'", "&quot;", $product['title']), '#CODE#' => $product['code']));
        if($product['count']!=0) {
          $style = 'style="display:none;"';        
        }
        // $product['remInfo'] = "<noindex><span class='rem-info' ".$style.">Товара временно нет на складе!<br/><a rel='nofollow' href='".SITE."/feedback?message=".$message."'>Сообщить когда будет в наличии.</a></span></noindex>";
        $product['remInfo'] = "<noindex><span class='rem-info' ".$style.">".MG::restoreMsg('msg__product_nonavaiable1',array('#LINK#' => SITE."/feedback?message=".$message))."</span></noindex>";
      }
      
      if ($product['count'] < 0) {
        $product['count'] = "много";
      };
      $product['related'] = $model->createRelatedForm(array('product'=>$product['related'], 'category'=>$product['related_cat']));
    }
    
    if($seoData = Seo::getMetaByTemplate('product', $product)) {            
      foreach ($seoData as $key => $value) {
        if(!empty($value)) {
          $product[$key] = empty($product[$key]) ? preg_replace('!\s+!', ' ', htmlspecialchars($value)) : $product[$key];
        }
      }      
    }


    $product['meta_title'] = $product['meta_title'] ? $product['meta_title'] : $product['title'];


    // для подстановки артикулов в верстку таблицу вариантов
    $tmp1 = array();
    $tmp2 = array();
    foreach ($product['variants'] as $key => $value) {
      $tmp1[] = 'id="variant-'.$value['id'].'"';
      $tmp2[] = 'id="variant-'.$value['id'].'" data-code="'.$value['code'].'"';
    }
    $product['propertyForm'] = str_replace($tmp1, $tmp2, $product['propertyForm']);

    $this->data = $product;

  }

}
