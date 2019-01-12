<?php

/**
 * Класс SmalCart - моделирует данные для маленькой корзины.
 *  - Предоставляет массив с количеством товаров и их общей стоимостью.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */
class SmalCart {

  /**
   * Записывает в cookie текущее состояние
   * корзины в сериализованном виде.
   * @access private
   * @return void
   */
  public static function setCartData() {
    // Сериализует  данные корзины из сессии в строку.
    // $cartContent = serialize($_SESSION['cart']);
    // Записывает сериализованную строку в куки, хранит 1 год.
    // SetCookie('cart', $cartContent, time()+3600*24*365);
    // MG::createHook(__CLASS__."_".__FUNCTION__, $cartContent);
  }

  /**
   * Получает данные из куков назад в сессию.
   * @access private
   * @return array
   */
  public static function getCokieCart() {
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $_SESSION['cart'], $args);
  }

  /**
   * Вычисляет общую стоимость содержимого, а также количество.
   * <code>
   * $res = Smalcart::getCartData();
   * viewData($res);
   * </code>
   * @return array массив с данными о количестве и цене.
   */
  public static function getCartData() {
    $modelCart = new Models_Cart();
    // Количество вещей в корзине.
    $res['cart_count'] = 0;

    // Общая стоимость.
    $res['cart_price'] = 0;

    // Если удалось получить данные из куков и они успешно десериализованны в $_SESSION['cart'].
    //self::getCokieCart() &&
    if (!empty($_SESSION['cart'])) {
      $settings = MG::get('settings');
      $totalPrice = 0;
      $totalCount = 0;


      if (!empty($_SESSION['cart'])) {
        $itemIds = array();
        $variantsId = array();
        foreach ($_SESSION['cart'] as $key => $item) {
          if (!empty($item['id'])) {            
            if (!empty($item['variantId'])) {
              $variantsId[] = intval($item['variantId']);
            } 
            $itemIds[] = intval($item['id']); 
          }
        }
        
        if (!empty($itemIds)) {
          // Пробегаем по содержимому.
          $idsPr = implode(',', array_unique($itemIds));
          $where = ' IN ('.trim(DB::quote($idsPr), "'").')';
        }
      } else {
        $where = ' IN (0)';
      }
      // Пробегаем по содержимому.
      //   $where = ' IN ('.trim(DB::quote(implode(',',$itemIds)),"'").')';
      $result = DB::query('
          SELECT CONCAT(c.parent_url,c.url) AS category_url, p.url AS product_url, p.*, rate,
          (p.price_course + p.price_course * (IFNULL(rate,0))) as `price_course`, p.currency_iso
          FROM `'.PREFIX.'product` AS p
          LEFT JOIN `'.PREFIX.'category` AS c ON c.id = p.cat_id
          WHERE p.id '.$where);
      $array_variants = array();
      if (!empty($variantsId)) {
          $ids = implode(',', $variantsId);
          $variants_res = DB::query('SELECT  pv.*, c.rate,(pv.price_course + pv.price_course *(IFNULL(c.rate,0))) as `price_course`,
          p.currency_iso
          FROM `'.PREFIX.'product_variant` pv   
          LEFT JOIN `'.PREFIX.'product` as p ON 
            p.id = pv.product_id
          LEFT JOIN `'.PREFIX.'category` as c ON 
            c.id = p.cat_id       
          WHERE pv.id IN ('.trim(DB::quote($ids, true)).')');
          while ($variant_row = DB::fetchAssoc($variants_res)) {
            $array_variants[$variant_row['id']] = $variant_row;
           }
        }

      $currencyRate = MG::getSetting('currencyRate');   
      $currencyShopIso = MG::getSetting('currencyShopIso');
      $products_row = array();
      while ($prod = DB::fetchAssoc($result)) {        
        $products_row[$prod['id']] = $prod;
      }
      foreach ($_SESSION['cart'] as $key => $item) {
        $variant = null;
        $row = $products_row[$item['id']];
        $arrayImages = explode("|", $row['image_url']);

        if (!empty($arrayImages)) {
          $row['image_url'] = $arrayImages[0];
          $row['image_url_new'] = mgImageProductPath($arrayImages[0], $item['id'], 'small');
        }

        MG::loadLocaleData($row['id'], LANG, 'product', $row);

        if (!empty($item['variantId']) && !empty($array_variants[$item['variantId']])) {
          $variant = $array_variants[$item['variantId']];
          $image = ($variant['image']) ? $variant['image'] : $arrayImages[0];

          MG::loadLocaleData($variant['id'], LANG, 'product_variant', $variant);
          
          $row['price'] = $variant['price'];
          $row['code'] = $variant['code'];
          $row['count'] = $variant['count'];
          $row['image_url'] = $variant['image']?$variant['image']:$row['image_url'];
          $row['image_url_new'] = mgImageProductPath($image, $item['id'], 'small');
          $row['weight'] = $variant['weight'];
          $row['title'] = $row['title']." ".$variant['title_variant'];
          $row['variantId'] = $variant['id'];
          $row['price_course'] = $variant['price_course'];
        }

        $price = $row['price_course'];


        $_SESSION['cart'][$key]['price'] = $price;
        if ($item['id'] == $row['id']) {
          $count = $item['count'];
          $row['countInCart'] = $count;
          $row['property_html'] = htmlspecialchars_decode(str_replace('&amp;', '&', $item['property']));
          $price = self::plusPropertyMargin($price, $item['propertyReal'], $currencyRate[$row['currency_iso']]);
          $_SESSION['cart'][$key]['price'] = $price;
          $row['property'] = $item['propertySetId'];
          @$priceWithCoupon = $modelCart->applyCoupon($_SESSION['couponCode'], $price, $row);
          $priceWithDiscount = $modelCart->applyDiscountSystem($price, $row); 

          $price = $modelCart->customPrice(array(
            'product' => $row,
            'priceWithCoupon' => $priceWithCoupon, 
            'priceWithDiscount' => $priceWithDiscount['price'],
          ));

          // если выбран формат без копеек, то округляем стоимость до ворматирования. 
          if(in_array(MG::getSetting('priceFormat'), array('1234','1 234','1,234'))){
            $price = round($price);
          }
          $row['priceInCart'] = MG::priceCourse($price * $count)." ".$settings['currency'];
          $_SESSION['cart'][$key]['priceWithDiscount'] = $price;
          $row['category_url'] = (MG::getSetting('shortLink') == 'true' ? '' : $row['category_url'].'/');
          $row['category_url'] = ($row['category_url'] == '/' ? 'catalog/' : $row['category_url']);
          $row['price'] = $price;
          $res['dataCart'][] = $row;
                
          $totalPrice += $price * $count;
          $totalCount += $count;
          $itemIds[] = $item['id'];
        }
      }
      
      $res['cart_price_wc'] = MG::priceCourse($totalPrice)." ".htmlspecialchars_decode($settings['currency']);
      $res['cart_count'] = $totalCount;
      $res['cart_price'] = MG::priceCourse($totalPrice);
    }


    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $res, $args);
  }

  /**
   * Прибавляет к стоимости товара дополнительные цены от выбранных характеристик.
   * <code>
   * $price = 6299;
   * $propertyHtml = '<div class="prop-position"> <span class="prop-name">Дополнительно: переходник</span> <span class="prop-val"> переходник#100#</span></div>';
   * $rate = 1;
   * $res = Smalcart::plusPropertyMargin($price, $propertyHtml, $rate);
   * viewData($res);
   * </code>
   * @param float $price базовая цена товара.
   * @param string $propertyHtml строка с информацией о выбранных характеристиках.
   * @param float $rate наценка.
   * @return float
   */
  public static function plusPropertyMargin($price, $propertyHtml, $rate) {
    return $price;
  }

}