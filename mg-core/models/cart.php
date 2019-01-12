<?php

/**
 * Модель: Cart
 *
 * Класс Models_Cart реализует логику взаимодействия с корзиной товаров.
 * - Добавляет товар в корзину;
 * - Получает список id продуктов из корзины;
 * - Расчитывает суммарную стоимость всех товаров в корзине;
 * - Очищает содержимое корзины.
 * - Обновляет содержимое корзины.
 * - Проверяет корзину на заполненность.
 * - Получает данные о всех продуктах в корзине.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Model
 */
class Models_Cart {

  /**
   * Добавляет товар в корзину.
   * <code>
   * $property = array(
   *     'property' => '<div class="prop-position"> <span class="prop-name">дополнительно: переходник</span> <span class="prop-val"> + 100 руб.</span></div>',
   *     'propertyReal' => '<div class="prop-position"> <span class="prop-name">дополнительно: переходник</span> <span class="prop-val"> переходник#100#</span></div>'
   * );
   * $cart = new Models_Cart();
   * $cart->addToCart(62, 2, $property, 1196);
   * </code>
   * @param int $id id товара.
   * @param int $count количество.
   * @param array $property характеристики товара.
   * @param int $variantId вариант товара (если есть).
   * @return bool
   */
  public function addToCart($id, $count = 1, $property = array('property' => '', 'propertyReal' => ''), $variantId = null) {
    $propertyReal = $property['propertyReal'];
    $property = $property['property'];
    if (empty($count) || !is_numeric($count)) {
      $count = 1;
    }
    $property = str_replace('%', '&#37;', $property);
    $property = str_replace('&', '&amp;', htmlspecialchars($property));

    // Если есть в корзине такой товар с этими характеристиками.
    $key = $this->alreadyInCart($id, $property, $variantId);

    if ($key !== null) {
      $product = new Models_Product();
      $tempProduct = $product->getProduct($id);
      $countMax = $tempProduct['count'];
    
      if ($variantId) {
        $tempProdVar = $product->getVariants($id);
        $countMax = $tempProdVar[$variantId]['count'];
      }
        if ($countMax == 0) {
        $args = func_get_args();
        $result = false;
        $args[1] = 0;
        return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
      }
      if (($count + $_SESSION['cart'][$key]['count']) > $countMax && $countMax > 0) {
        $_SESSION['cart'][$key]['count'] = $countMax;
      } else {
        // Увеличиваем счетчик.
        $_SESSION['cart'][$key]['count'] += $count;
      }
    } else {
      $_SESSION['propertySetArray'][] = $property;
      $lastKey = array_keys($_SESSION['propertySetArray']);
      $lastKey = end($lastKey);
      if ($variant) {
        $id = $variant;
      }
      if(count($_SESSION['cart']) < MAX_COUNT_CART) {

        $_SESSION['cart'][] = array(
          'id' => intval($id), 
          'count' => $count, 
          'property' => $property, 
          'propertyReal' => $propertyReal, 
          'propertySetId' => $lastKey, 
          'variantId' => intval($variantId)
        );
      
      }
    }

    $args = func_get_args();
    $result = true;
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Создает информацию для последующего сохранения свойства для товара положенного в корзину из входящего массива.
   * <code>
   * $arr = array(
   *   'calcPrice' => 1,          // рассчет цены
   *   'inCartProductId' => 102,  // id товара
   *   'amount_input' => 4,       // количество товара
   *   'variant' => 1240,         // id варианта товара
   *   '46#0' => '+100 руб.'      // где 46 - id характеристики, 0 - id варианта характеристики, 100 - наценка
   * );
   * $result = Models_Cart::createProperty($arr);
   * viewData($result);
   * </code>
   * @param array $arr
   * @return array
   */
  public function createProperty($arr) {

    $property = ''; // Фиктивная информация о характеристиках, выводимая в публичной части, в понятном пользователям виде.
    $propertyReal = ''; // Реальная защищенная информация о характеристиках, не выводимая в публичной части, хранящаяся в сессии в корзине.


    return array('property' => $property, 'propertyReal' => $propertyReal);
  }

  /**
   * Сравнивает добавляемый товар с товарами в корзине, если в корзине 
   * есть такой же товар с id и его свойства совпадают с 
   * текущим, то увеличиваем счетчик иначе просто добавляем новую
   * позицию продукта с выбранными параметрами.
   * <code>
   * var_dump(Models_Cart::alreadyInCart(
   *     62, 
   *     '<div class="prop-position"> <span class="prop-name">дополнительно: переходник</span> <span class="prop-val"> + 100 руб.</span></div>', 
   *     1196
   * ));
   * </code>
   * @param int $id id товара.
   * @param string $property характеристки товара.
   * @param int $variant id варианта товара.
   * @return int|null id элемента в корзине
   */
  public function alreadyInCart($id, $property, $variant = null) {
    $result = null;

    if (!empty($_SESSION['cart'])) {
      foreach ($_SESSION['cart'] as $key => $item) {
        if (empty($item['variantId'])) {
          if ($id == $item['id'] && $property == $item['property']) {
            $result = $key;
            break;
          }
        } else {
          if ($variant == $item['variantId'] && $property == $item['property']) {
            $result = $key;
            break;
          }
        }
      }
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Удаляет товар из корзины.
   * <code>
   * Models_Cart::delFromCart(
   *     62, 
   *     '<div class="prop-position"> <span class="prop-name">дополнительно: переходник</span> <span class="prop-val"> + 100 руб.</span></div>', 
   *     1196
   * );
   * </code>
   * @param int $id id товара.
   * @param string $property характеристки товара.
   * @param int $variantId id варианта.
   * @return bool
   */
  public function delFromCart($id, $property, $variantId) {

    $tmp = htmlspecialchars_decode(str_replace('&amp;', '&', $property));
    $arr1 = explode('<div class="prop-position">', $tmp);

    foreach ($arr1 as $key => $value) {
      $arr2 = explode('<span class="prop-val">', $value);
      $str = $arr2[1];
      $str = str_replace('</span></div>', '', $str);
      $oldBlock = str_replace(' + ', '', $str);
      $arr1[$key] = str_replace($oldBlock, '', $value);
    }
    $html = implode('<div class="prop-position">', $arr1);
    $property2 = htmlspecialchars($html);    

    if (!empty($_SESSION['cart'])) {
      foreach ($_SESSION['cart'] as $key => $item) {

        $tmp = htmlspecialchars_decode(str_replace('&amp;', '&', $item['property']));
        $arr1 = explode('<div class="prop-position">', $tmp);

        foreach ($arr1 as $keyz => $value) {
          $arr2 = explode('<span class="prop-val">', $value);
          $str = $arr2[1];
          $str = str_replace('</span></div>', '', $str);
          $oldBlock = str_replace(' + ', '', $str);
          $arr1[$keyz] = str_replace($oldBlock, '', $value);
        }
        $html = implode('<div class="prop-position">', $arr1);
        $item['property2'] = htmlspecialchars($html);

        if ($variantId > 0) {
          if (($property == $item['property'] || $property2 == $item['property2']) && $variantId == $item['variantId']) {
            $propertySetId = $_SESSION['cart'][$key]['propertySetId'];
            if (!empty($_SESSION['propertySetArray'][$propertySetId])) {
              unset($_SESSION['propertySetArray'][$propertySetId]);
            }
            unset($_SESSION['cart'][$key]);
            break;
          }
        } else {
          if ($id == $item['id'] && ($property == $item['property'] || $property2 == $item['property2'])) {
            $propertySetId = $_SESSION['cart'][$key]['propertySetId'];
            if (!empty($_SESSION['propertySetArray'][$propertySetId])) {
              unset($_SESSION['propertySetArray'][$propertySetId]);
            }
            unset($_SESSION['cart'][$key]);
            break;
          }
        }
      }
    }
  }           

  /**
   * Возвращает список id продуктов из корзины.
   * <code>
   * $result = Models_Cart::getListItemId();
   * viewData($result);
   * </code>
   * @return array список id.
   */
  protected function getListItemId() {
    $args = func_get_args();
    $result = null;

    if (!empty($_SESSION['cart'])) {
      foreach ($_SESSION['cart'] as $key => $item) {
        $result[] = $item['id'];
      }
    }

    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Возвращает суммарную стоимость всех товаров в корзине.
   * <code>
   * $cart = new Models_Cart();
   * echo $cart->getTotalSumm();
   * </code>
   * @return float
   */
  public function getTotalSumm() {

    // Создает модель для работы с продуктами.
    $itemPosition = new Models_Product();

    $currencyRate = MG::getSetting('currencyRate');   
    $currencyShopIso = MG::getSetting('currencyShopIso');
    
    if (!empty($_SESSION['cart'])) {
      $variants = array();
      $products = array();
      foreach ($_SESSION['cart'] as $key => $item) {  
        if($item['variantId']) {          
          $variants[$key] = intval($item['variantId']);                 
        } else {  
          $products[$key] = intval($item['id']);            
        }
      }
        if (!empty($variants)) {
          $ids = implode(',', $variants);
          $res_var = DB::query('
            SELECT  pv.id as id, pv.price_course as `price`, c.rate as rate,
            pv.currency_iso
            FROM `'.PREFIX.'product_variant` pv   
            LEFT JOIN `'.PREFIX.'product` as p ON 
            p.id = pv.product_id
            LEFT JOIN `'.PREFIX.'category` as c ON 
            c.id = p.cat_id       
            WHERE pv.id IN ('.DB::quote($ids, true).')
          ');  
          while($prod = DB::fetchArray($res_var)) {
            $rate = $prod['rate'] ? $prod['rate'] : 0;
            $prod['price_course'] = $prod['price']+$prod['price']*$rate;
            $prices_variant[$prod['id']] = $prod;
          }
        }
        if (!empty($products)) {
          $ids = implode(',', $products);
          $res_pr = DB::query('
            SELECT p.id as id, p.price_course as `price`, c.rate,
            p.`currency_iso` 
            FROM `'.PREFIX.'product` p
            LEFT JOIN `'.PREFIX.'category` c
            ON c.id = p.cat_id
            WHERE p.id IN  ('.DB::quote($ids, true).')');        
          while($prod = DB::fetchArray($res_pr)) {
            $rate = $prod['rate'] ? $prod['rate'] : 0;
            $prod['price_course'] = $prod['price']+$prod['price']*$rate;
            $prices_prod[$prod['id']] = $prod;
          }
        }      
      
      foreach ($_SESSION['cart'] as $key => $item) {
        if($item['variantId']) {
          $prod = $prices_variant[$variants[$key]];  
        } else {
          $prod = $prices_prod[$item['id']]; 
        }
               
        $prod['currency_iso'] = $prod['currency_iso']?$prod['currency_iso']:$currencyShopIso;   
        $prod['price'] = $prod['price_course']; 


        // если выбран формат без копеек, то округляем стоимость до ворматирования. 
        if(in_array(MG::getSetting('priceFormat'), array('1234','1 234','1,234'))) {
          $prod['price'] = round($prod['price']);
        }
        $prod['price'] = SmalCart::plusPropertyMargin($prod['price'], $item['propertyReal'], $currencyRate[$prod['currency_iso']]);
        $prod['property'] = $item['property'];
        $prod['keyInCart'] = $key;
        $productPositions[] = $prod;
      }
    }

    $totalSumm = 0;
    // Расчитывает сумму.
    if (!empty($productPositions)) {
      foreach ($productPositions as $key => $product) {
        // применение скидки по купону          
        $priceWithCoupon = $this->applyCoupon($_SESSION['couponCode'], $product['price'], $product);
        $priceWithDiscount = $this->applyDiscountSystem($product['price'], $product); 
        $product['price'] = $this->customPrice(array(
          'product' => $product,
          'priceWithCoupon' => $priceWithCoupon, 
          'priceWithDiscount' => $priceWithDiscount['price'],
        ));
        
        $totalSumm += $_SESSION['cart'][$product['keyInCart']]['count'] * $product['price'];
      }
    }
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $totalSumm, $args);
  }

  /**
   * Очищает содержимое корзины.
   * <code>
   * Models_Cart::clearCart();
   * </code>
   * @return void
   */
  public function clearCart() {
    unset($_SESSION['cart']);
    MG::createHook(__CLASS__."_".__FUNCTION__);
  }

  /**
   * Обновляет содержимое корзины.
   * <code>
   * $arr = Array()
   *   0 => Array(
   *     'id' => 102,
   *     'count' => 1,
   *     'property' => '&amp;lt;div class=&amp;quot;prop-position&amp;quot;&amp;gt; &amp;lt;span class=&amp;quot;prop-name&amp;quot;&amp;gt;дополнительно: переходник&amp;lt;/span&amp;gt; &amp;lt;span class=&amp;quot;prop-val&amp;quot;&amp;gt; + 100 руб.&amp;lt;/span&amp;gt;&amp;lt;/div&amp;gt;',
   *     'propertyReal' => '<div class="prop-position"> <span class="prop-name">дополнительно: переходник</span> <span class="prop-val"> переходник#100#</span></div>',
   *     'propertySetId' => 2,
   *     'variantId' => 1240,
   *     'price' => 6399,
   *     'priceWithDiscount' => 6399,
   *   )
   * );
   * </code>
   * @param array $arr массив продуктов в корзине.
   * @return bool
   */
  public function refreshCart($arr) {
    $_SESSION['cart'] = $arr;   
    $result = true;
    $args = func_get_args();
    MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Проверяет корзину на заполненность.
   * <code>
   * $result = Models_Cart::isEmptyCart();
   * var_dump($result);
   * </code>
   * @return bool
   */
  public function isEmptyCart() {
    $result = false;
    unset($_SESSION['cart']['']);
    if (!empty($_SESSION['cart'])) {
      $result = true;
    }
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Возвращает данные о всех продуктах в корзине.
   * <code>
   * $cart = new Models_Cart();
   * $result = $cart->getItemsCart();
   * viewData($result);
   * </code>
   * @return array
   */
  public function getItemsCart() {

    $productPositions = array();

    // Создает модель для работы с продуктами.
    $itemPosition = new Models_Product();
    $totalSumm = 0;
    if (!empty($_SESSION['cart'])) {

      $currencyRate = MG::getSetting('currencyRate');   
      $currencyShopIso = MG::getSetting('currencyShopIso');
      $variantsId = array();
      $productsId = array();
      foreach ($_SESSION['cart'] as $key => $item) {
        if (!empty($item['variantId'])) {
          $variantsId[] = intval($item['variantId']);
        }
        if (!empty($item['id'])) {
          $productsId[] = intval($item['id']);     
        }           
      }
      $products_all = array();
      $variants_all = array();
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
            $variants_all[$variant_row['id']] = $variant_row;
           }
        }
        if (!empty($productsId)) {
          $ids = implode(',', array_unique($productsId));
          $product_res = DB::query('
            SELECT  CONCAT(c.parent_url,c.url) as category_url,
            p.url as product_url, p.*, rate, (p.price_course + p.price_course * (IFNULL(rate,0))) as `price_course`, 
            p.`currency_iso` 
            FROM `'.PREFIX.'product` p
            LEFT JOIN `'.PREFIX.'category` c
            ON c.id = p.cat_id
            WHERE p.id IN ('.DB::quote($ids, true).')');
   
          if (!empty($product_res)) {
            while ($result = DB::fetchAssoc($product_res)) {
              $products_all[$result['id']] = $result;
            }          
          }
        }
      foreach ($_SESSION['cart'] as $key => $item) {
        $variant = '';
        if (!empty($item['variantId'])) {
          $variant = $variants_all[$item['variantId']];
        }
        // Заполняет массив информацией о каждом продукте по id из куков.
        // Если куки не актуальны, пропускает товар.
        $product = $products_all[$item['id']];
        if (!empty($product)) {
          $product['property'] = $_SESSION['cart'][$key]['propertySetId'];
          $product['property_html'] = htmlspecialchars_decode(str_replace('&amp;', '&', $_SESSION['cart'][$key]['property']));
          $product['propertySetId'] = $_SESSION['cart'][$key]['propertySetId'];

          MG::loadLocaleData($product['id'], LANG, 'product', $product);

          if (!empty($variant)) {
            MG::loadLocaleData($variant['id'], LANG, 'product_variant', $variant);
            
            $product['price'] = $variant['price'];
            $product['code'] = $variant['code'];
            $product['count'] = $variant['count'];
            $product['weight'] = $variant['weight'];        
            $product['image_url'] = $variant['image']?$variant['image']:$product['image_url'];
            $product['title'] .= " ".$variant['title_variant'];
            $product['variantId'] = $variant['id'];
          	$product['price_course']  = $variant['price_course'];
          }

          // если установлен формат без копеек то округлим стоимость.
          $priceFormat = MG::getSetting('priceFormat');
          
          if(in_array($priceFormat, array('1234','1 234','1,234',''))) {               
            $price = round($product['price_course']);          
          } else {
            $price = $product['price_course'];     
          }       
          
          if ($item['id'] == $product['id']) {
            $count = $item['count'];

            
            $price = SmalCart::plusPropertyMargin($price, $item['propertyReal'], $currencyRate[$product['currency_iso']]);
            $product['price'] = $price;
            // применение скидки по купону 

            $priceWithCoupon = $this->applyCoupon($_SESSION['couponCode'], $product['price'], $product);     
            $priceWithDiscount = $this->applyDiscountSystem($product['price'], $product);
            $product['price'] = $this->customPrice(array(
              'product' => $product,
              'priceWithCoupon' => $priceWithCoupon, 
              'priceWithDiscount' => $priceWithDiscount['price'],
            ));
            
            $product['priceInCart'] = MG::priceCourse($product['price'] * $count)." ".MG::getSetting('currency');          
            $arrayImages = explode("|", $product['image_url']);
            
            if (!empty($arrayImages)) {
              $product['image_url'] = $arrayImages[0];
            }
          }
          $product['category_url'] = (MG::getSetting('shortLink') == 'true' ? '' : $product['category_url'].'/');
          $product['category_url'] = ($product['category_url'] == '/' ? 'catalog/' : $product['category_url']);
          $product['link'] = (MG::getSetting('shortLink') == 'true' ? SITE.'/'.$product["product_url"] : SITE.'/'.(isset($product["category_url"])&&$product["category_url"]!='' ? $product["category_url"] : 'catalog/').$product["product_url"]);
          $product['countInCart'] = $item['count'];

          if ($product['countInCart'] > 0) {
            $productPositions[] = $product;
          }
          $totalSumm += $product['price'] * $item['count'];          
         
        }
      }
    }
   
    $totalSumm = MG::priceCourse($totalSumm);
    $result = array('items' => $productPositions, 'totalSumm' => $totalSumm);
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }
  
  /**
   * Проверяет целостность корзины.
   * Необходимо, когда был удален один из продуктов из БД, но у пользователя остался ID продукта
   * <code>
   * Models_Cart::repairCart();
   * </code>
   * @return void
   */
  public function repairCart() {
    foreach ($_SESSION['cart'] as $id => $count) {
      if ($id == '') {
        unset($_SESSION['cart']['']);
      }
    }
  }

  /**
   * Функция возвращает минимальную цену с учетом скидки по купону, объемной и накопительно скидок
   * или если находит пользовательскую функцию обработки цены, возвращает её результат.
   * <code>
   * $model = new Models_Product;
   * $product = $model->getProduct(62);
   * $arFields = array(
   *   'product' => $product,       // информация о продукте, включающая оригинальную цену.
   *   'priceWithCoupon' => 100,    // цена с примененной скидкой по купону.
   *   'priceWithDiscount' => 150   // цена с примененной накопительной или объемной скидкой.
   * );
   * $price = Models_Cart::customPrice($arFields);
   * echo $price;
   * </code>
   * @param $arFields  массив данных, которые можно использовать для формирования своей цены.
   * @return float
   */
  public function customPrice($arFields) {
    $result = $arFields['priceWithCoupon'] < $arFields['priceWithDiscount'] ? $arFields['priceWithCoupon'] : $arFields['priceWithDiscount'];    
    $product = $arFields['product'];
    $priceArgs = array(
      'priceWithCoupon' => $arFields['priceWithCoupon'], 
      'priceWithDiscount' => $arFields['priceWithDiscount'],
    );
    
    $priceCustomFunctionsList = MG::getInstance()->getPriceCustomFunctions();
    usort($priceCustomFunctionsList, array(__CLASS__, 'sortFunctionByPriority'));

    foreach($priceCustomFunctionsList as $function) {
      $priceArgs['product'] = $product;
      $args[0] = $priceArgs;
      
      if (function_exists($function['function_name']) && empty($function['class'])) {                
        $product['price'] = @call_user_func_array($function['function_name'], $args);
      } elseif ($function['class'] && class_exists($function['class'])) {
        $product['price'] = @call_user_func_array(array($function['class'], $function['function_name']), $args);
      }
      
      $result = $product['price'];
    }
    
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }
  
  /**
   * Сортирует записи по полю "priority"
   * @param array $a массив с полями: class, function_name, priority
   * @param array $b массив с полями: class, function_name, priority
   * @return int
   */
  private function sortFunctionByPriority($a, $b) {
    if($a['priority'] == $b['priority']) {
      return 0;
    }
    
    return $a['priority'] < $b['priority'] ? -1 : 1;
  }
  
  /**
   * Применяет скидку по купону
   * <code>
   * $model = new Models_Product;
   * $product = $model->getProduct(62);
   * $price = Models_Cart::applyCoupon($_SESSION['couponCode'], 150, $product);
   * echo $price;
   * </code>
   * @param string $code код купона товара.
   * @param string $price входящая стоимость.
   * @param string $product информация о продукте.
   * @return float возвращает новую стоимость товара
   */
  public function applyCoupon($code, $price, $product = null) {
    $result = $price;
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Применяет скидку по системе скидок - накопительная или объемная
   * <code>
   * $model = new Models_Product;
   * $product = $model->getProduct(62);
   * $result = Models_Cart::applyDiscountSystem(150, $product);
   * viewData($result);
   * </code>
   * @param string $price входящая стоимость.
   * @param array|null $product массив с товаром.
   * @return array возвращает новую стоимость товара, информация о том какая система скидок применена и скидочный купон
   */
  public function applyDiscountSystem($price, $product = null) {
    $result = array (
      'price' => $price, 
      'discounts' => '',
      'promo' => ''
      );
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

}