<?php

/**
 * Модель: Order
 *
 * Класс Models_Order реализует логику взаимодействия с заказами покупателей.
 * - Проверяет корректность ввода данных в форме оформления заказа;
 * - Добавляет заказ в базу данных.
 * - Отправляет сообщения на электронные адреса пользователя и администраторов, при успешном оформлении заказа.
 * - Удаляет заказ из базы данных.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>

 * @package moguta.cms
 * @subpackage Model
 */
class Models_Order {

  // ФИО покупателя.
  public $fio;
  // Электронный адрес покупателя.
  public $email;
  // Телефон покупателя.
  public $phone;
  // Адрес покупателя.
  public $address;
  // Флаг нового пользователя.
  public $newUser = false;
  // Комментарий покупателя.
  public $info;
  // Автоматически созданный пароль для нового пользователя при оформлении первого заказа, можно вывести в email_order_layout
  public $passNewUser = '';  
  // Дата доставки.
  public $dateDelivery;
  // Массив способов оплаты.
  public $_paymentArray = array();
  // ip пользователя при заказе
  public $ip;
  // Статичный массив статусов.
  static $status = array(
    0 => 'NOT_CONFIRMED',
    1 => 'EXPECTS_PAYMENT',
    2 => 'PAID',
    3 => 'IN_DELIVERY',
    4 => 'CANSELED',
    5 => 'EXECUTED',
    6 => 'PROCESSING',
  );
  static $statusUser = array();
  // кастомные поля для заказов
  static $optFields = array();
  static $storage = '';

  function __construct() {
    $res = DB::query('SELECT  *  FROM `'.PREFIX.'payment` ORDER BY `sort`');
    $i = 0;
    while ($row = DB::fetchAssoc($res)) {
      $newparam = array();
      $param = json_decode($row['paramArray']);
      foreach ($param as $key=>$value) {
        if ($value != '') {
          $value = CRYPT::mgDecrypt($value);
        }
        $newparam[$key] = $value;
      }

      $row['paramArray'] = CRYPT::json_encode_cyr($newparam);
      $this->_paymentArray[$row['id']] = $row;
    };
  }

  /**
   * Проверяет корректность ввода данных в форму заказа и регистрацию в системе покупателя.
   * <code>
   * $arrayData = array(
   *  'email' => 'admin@admin.ru', // почта пользователя
   *  'phone' => '+7 (111) 111-11-11', // телефон пользователя
   *  'fio' => 'Администратор', // имя покупателя
   *  'address' => 'addr', // адрес доставки
   *  'info' => 'comment', // комментарий покупателя
   *  'customer' => 'fiz', // плательщик (fiz - физическое лицо, yur - юридическое)
   *  'yur_info' => Array(
   *         'nameyur' => null, // название юр лица
   *         'adress' => null, // адрес юр лица
   *         'inn' => null, // инн юр лица
   *         'kpp' => null, // кпп юр лица
   *         'bank' => null, // банк юр лица
   *         'bik' => null, // бик юр лица
   *         'ks' => null, // К/Сч юр лица
   *         'rs' => null, // Р/Сч юр лица
   *     ),
   *  'delivery' => 1, // ID доставки
   *  'date_delivery' => '08.03.2018', // дата доставки
   *  'payment' => 2 // ID оплаты
   * );
   * $order = new Models_Order;
   * $order->isValidData($arrayData);
   * </code>
   * @param array $arrayData  массив с ведёнными пользователем данными.
   * @param array $require обязательные поля к заполнению
   * @param bool $createUser создавать ли нового пользователя, если нет такого
   * @param string $error ошибка
   * @return bool|string $error сообщение с ошибкой в случае некорректных данных.
   */
  public function isValidData($arrayData, $require = array('email','phone','payment'),$createUser = true , $error = null) {
    $result = null;
    $this->newUser = false;
    if($createUser) {
      // Если электронный адрес зарегистрирован в системе.
      $currenUser = USER::getThis();
      if ($currenUser->email != trim($arrayData['email'])) {
        if (USER::getUserInfoByEmail($arrayData['email'])) {
          // $error = "<span class='user-exist'>Пользователь с таким email существует. 
          //   Пожалуйста, <a href='".SITE."/enter?location=".SITE.$_SERVER['REQUEST_URI']."'>войдите в систему</a> используя 
          //   свой электронный адрес и пароль!</span>";
          // $error = "<span class='user-exist'>".MG::restoreMsg('msg__email_in_use',array('#LINK#' => SITE."/enter?location=".SITE.$_SERVER['REQUEST_URI']))."</span>";
          // Иначе новый пользователь.
        } else {
          $this->newUser = true;
        }
      }
    }
    
    if(in_array('email', $require)&&(MG::getSetting('requiredFields')=='true')) {
      // Корректность емайл.
      if (!preg_match('/^[-._a-zA-Z0-9]+@(?:[a-zA-Z0-9][-a-zA-Z0-9]{0,61}+\.)+[a-zA-Z]{2,6}$/', $arrayData['email'])) {
        // $error = "<span class='order-error-email'>E-mail введен некорректно!</span>";
        $error = "<span class='order-error-email'>".MG::restoreMsg('msg__email_incorrect')."</span>";
      }
    }
    
    if(in_array('phone', $require)&&(MG::getSetting('requiredFields')=='true')) {
      // Наличие телефона.
      if (empty($arrayData['phone'])) {
        // $error = "<span class='no-phone'>Введите верный номер телефона!</span>";
        $error = "<span class='no-phone'>".MG::restoreMsg('msg__phone_incorrect')."</span>";
      }
    }

    if ($arrayData['customer'] == "yur" && !(int)$arrayData['yur_info']['inn']) {
        $error = "<span class='no-phone'>".MG::restoreMsg('msg__payment_inn')."</span>";
    }  
    
    if(in_array('payment', $require)) {
      // Неуказан способ оплаты.
      if (empty($arrayData['payment'])) {
        // $error = "<span class='no-phone'>Выберите способ оплаты!</span>";
        $error = "<span class='no-phone'>".MG::restoreMsg('msg__payment_incorrect')."</span>";
      }
    }
    if (MG::getSetting('captchaOrder') == 'true') {// если при оформлении капча.
      if (MG::getSetting('useReCaptcha') == 'true' && MG::getSetting('reCaptchaSecret') && MG::getSetting('reCaptchaKey')) {
        if (!MG::checkReCaptcha() && !URL::get('addOrderOk')) {
          $error = "<span class='no-phone'>".MG::restoreMsg('msg__recaptcha_incorrect')."</span>";
        }
      }
      else{
        if (empty($arrayData['capcha']) || (strtolower($arrayData['capcha']) != strtolower($_SESSION['capcha']))) {
          // $error = "<span class='no-phone'>Неверно введен код с картинки!</span>";
          $error = "<span class='no-phone'>".MG::restoreMsg('msg__captcha_incorrect')."</span>";
        }
      }
    } 
       // проверка  наличия товара товаров, пока человек оформляет заказ -товар может купить другой пользователь
    if (!empty($_SESSION['cart']) 
      ) {
      foreach ($_SESSION['cart'] as $key => $item) {  
        if ($item['variantId']) {         
          $res_var = DB::query('
            SELECT  pv.id, p.`title`, pv.`title_variant`, pv.count
            FROM `'.PREFIX.'product_variant` pv   
            LEFT JOIN `'.PREFIX.'product` as p ON 
            p.id = pv.product_id   
            WHERE pv.id ='.DB::quote($item['variantId'])); 
          if ($prod = DB::fetchArray($res_var)) {
            if ($prod['count'] >=0 && $prod['count'] < $_SESSION['cart'][$key]['count']) {
              if ($prod['count'] == 0) {
                // $error .= "<p>Товара ".$prod['title'].' '.$prod['title_variant']." уже нет в наличии. Для оформления заказа его необходимо удалить из корзины.</p>";
                $error .= "<p>".MG::restoreMsg('msg__product_ended',array('#PRODUCT#' => $prod['title'].' '.$prod['title_variant']))."</p>";
              } else {
                // $error .= "<p>Товар ".$prod['title'].' '.$prod['title_variant']." доступен в количестве ".$prod['count']." шт. Для оформления заказа измените количество в корзине.</p>";
                $error .= "<p>".MG::restoreMsg('msg__product_ending',array('#PRODUCT#' => $prod['title'].' '.$prod['title_variant'], '#COUNT#' => $prod['count']))."</p>";
              }              
            }            
          }          
        } else {  
          $res_pr = DB::query('
            SELECT id, title, count
            FROM `'.PREFIX.'product` p 
            WHERE id ='.DB::quote($item['id']));      
          if ($prod = DB::fetchArray($res_pr)) {
            if ($prod['count'] >=0 && $prod['count'] < $_SESSION['cart'][$key]['count']) {
              if ($prod['count'] == 0) {
                // $error .= "<p>Товара ".$prod['title']." уже нет в наличии. Для оформления заказа его необходимо удалить из корзины.</p>";
                $error .= "<p>".MG::restoreMsg('msg__product_ended',array('#PRODUCT#' => $prod['title']))."</p>";
              } else {
                // $error .= "<p>Товар ".$prod['title']." доступен в количестве ".$prod['count']." шт. Для оформления заказа измените количество в корзине.</p>";
                $error .= "<p>".MG::restoreMsg('msg__product_ending',array('#PRODUCT#' => $prod['title'], '#COUNT#' => $prod['count']))."</p>";
              }   
            }            
          }   
        }  
      }
    }



    
    // Если нет ошибок, то заносит информацию в поля класса.
    if (!empty($error)) {
      $result = $error;
    } else {


      $cart = new Models_Cart();
      $summ = $cart->getTotalSumm();      


      $this->fio = trim($arrayData['fio']);
      $this->email = trim($arrayData['email']);
      $this->phone = trim($arrayData['phone']);
      $this->info = trim($arrayData['info']);
      $this->delivery = $arrayData['delivery'];
      $this->dateDelivery = $arrayData['date_delivery'];
      $this->interval = trim($arrayData['delivery_interval']);
      $deliv = new Delivery();
      $tmp = $deliv->getCostDelivery($arrayData['delivery']);
      $deliveryArrdessParts = $deliv->getDeliveryAddressParts($arrayData['delivery']);

      if ($deliveryArrdessParts == '1') {
        $deliveryArrdessParts = array(
          'index' => $arrayData['address_index'],
          'country' => $arrayData['address_country'],
          'region' => $arrayData['address_region'],
          'city' => $arrayData['address_city'],
          'street' => $arrayData['address_street'],
          'house' => $arrayData['address_house'],
          'flat' => $arrayData['address_flat']
        );
        $this->address_parts = addslashes(serialize($deliveryArrdessParts));
        $this->address = '';
        $useOrderParts = true;
      }
      else{
        $this->address = trim($arrayData['address']);
        $useOrderParts = false;
      }
      
      $res = DB::query('SELECT rate FROM '.PREFIX.'payment WHERE id = '.DB::quoteInt($arrayData['payment']));
      while($row = DB::fetchAssoc($res)) {
        $rate = $row['rate'];
      }


      if(MG::getSetting('enableDeliveryCur') == 'true') {
        $this->delivery_cost = MG::numberDeFormat(MG::numberFormat($tmp * (1+$rate)));
      } else {
        $this->delivery_cost = $tmp;
      }
      $this->payment = $arrayData['payment'];
      $this->summ = $summ;
      $this->ip = $_SERVER['REMOTE_ADDR'];
      $result = false;
      // если существуют данные сохраненного пост запроса, значит был редирект на страницу с ?addOrderOk=1 для отслеживания цели в метрике яндекса
	  // значит теперь можно создать пользователя, во второй итерации данного метода
	  if(!empty($_SESSION['post'])){
	    $this->addNewUser($useOrderParts);  
	  }    
    }
    
    $args = func_get_args();
    $args['this'] = &$this;
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Если заказ оформляется впервые на нового покупателя, то создает новую запись в таблице пользователей.
   * <code>
   * $model = new Models_Order();
   * $model->newUser = true;
   * $model->email = 'user@mail.mail';
   * $model->fio = 'username';
   * $model->address = 'адрес';
   * $model->phone = '8 (555) 555-55-55';
   * $model->ip = '127.0.0.1';
   *
   * $model->addNewUser();
   * </code>
   */
  public function addNewUser($useOrderParts = false) {
    // Если заказ производит новый пользователь, то регистрируем его
    if (MG::getSetting('autoRegister') == "true") {
      $activity = 1;
      if ($this->newUser) {

        $this->passNewUser = MG::genRandomWord(10);

        $userArr = array(
            'email' => $this->email,
            'role' => 2,
            'name' => $this->fio ? $this->fio : 'Пользователь',
            'pass' => $this->passNewUser,
            'address' => $this->address,
            'phone' => $this->phone,
            'ip' => $this->ip,
            'nameyur' => $_POST['yur_info']['nameyur'],
            'adress' => $_POST['yur_info']['adress'],
            'inn' => $_POST['yur_info']['inn'],
            'kpp' => $_POST['yur_info']['kpp'],
            'bank' => $_POST['yur_info']['bank'],
            'bik' => $_POST['yur_info']['bik'],
            'ks' => $_POST['yur_info']['ks'],
            'rs' => $_POST['yur_info']['rs'],
            'activity' => $activity
          );

        if ($useOrderParts) {
          $tmp = unserialize(stripcslashes($this->address_parts));
          foreach ($tmp as $key => $value) {
            $userArr['address_'.$key] = $value;
          }
        }

        USER::add($userArr);
      }
    }
  }

  /**
   * Сохраняет заказ в базу сайта.
   * Добавляет в массив корзины третий параметр 'цена товара', для сохранения в заказ.
   * Это нужно для того, чтобы в последствии вывести детальную информацию о заказе.
   * Если оставить только id то информация может оказаться неверной, так как цены меняются.
   * @see Models_Order::isValidData() входящий массив
   * <code>
   * $model = new Models_Order();
   * $model->isValidData($arrayData);
   * $orderId = $model->addOrder();
   * echo $orderId;
   * </code>
   * @param bool $adminOrder пришел ли заказ из админки
   * @return int $id номер заказа.
   */
  public function addOrder($adminOrder = false) {
    $itemPosition = new Models_Product();
    $cart = new Models_Cart();
    $catalog = new Models_Catalog();
    $categoryArray = $catalog->getCategoryArray();
    $this->summ = 0;
    $currencyRate = MG::getSetting('currencyRate');   
    $currencyShopIso = MG::getSetting('currencyShopIso');

    // Массив запросов на обновление количества товаров.
    $updateCountProd = array();
    $variant_update = array();
    $product_update = array();
    // Добавляем в массив корзины параметр 'цена товара'.
    if ($adminOrder) {

      $this->email = $adminOrder['user_email'];
      $this->phone = $adminOrder['phone'];
      $this->address = $adminOrder['address'];
      $this->delivery = $adminOrder['delivery_id'];
      $this->dateDelivery = $adminOrder['date_delivery'];
      $this->delivery_cost = $adminOrder['delivery_cost'];
      $this->payment = $adminOrder['payment_id'];
      $this->fio = $adminOrder['name_buyer'];
      $formatedDate = date('Y-m-d H:i:s'); // Форматированная дата ГГГГ-ММ-ДД ЧЧ:ММ:СС.

      foreach ($adminOrder['order_content'] as $item) {
               
        $product = $itemPosition->getProduct($item['id']);
        $_SESSION['couponCode'] = $item['coupon'];
        $product['category_url'] = $product['category_url'] ? $product['category_url'] : 'catalog';
        $productUrl = $product['category_url'].'/'.$product['url'];
        $itemCount = $item['count'];
        if (!empty($product)) {
          $fulPrice = $item['fulPrice']; // полная стоимость без скидки
          $product['price'] = $item['price'];
          // если выбран формат без копеек, то округляем стоимость до ворматирования. 
          if(in_array(MG::getSetting('priceFormat'), array('1234','1 234','1,234'))) {
            $product['price'] = round($item['price']);
          }
          $discount = 0;
          if (!empty($item['price'])&&(!empty($item['coupon'])||(stristr($item['discSyst'], 'true')!==false))) {
            $discount = 100 - ($product['price'] * 100) / $fulPrice;
          }

          $productPositions[] = array(
            'id' => $product['id'],
            'name' => $item['title'],
            'url' => $productUrl,
            'code' => $item['code'],
            'price' => $product['price'],
            'count' => $itemCount,
            'property' => $item['property'],
            'coupon' => $_SESSION['couponCode'],
            'discount' => round($discount, 2),
            'fulPrice' => $fulPrice,
            'weight' => $product['weight'],
            'currency_iso' => $currencyShopIso,
            'discSyst' => !empty($item['discSyst'])?$item['discSyst']:'',
          );


          $this->summ += $product['price'] * $itemCount;

          // По ходу формируем массив запросов на обновление количества товаров.
          if ($item['variant'] == 0) {
            $product['count'] = ($product['count'] - $itemCount) >= 0 ? $product['count'] - $itemCount : 0;
            if (!empty($product_update[$product['id']])) {
              $product_update[$product['id']] = ($product_update[$product['id']] - $itemCount) >= 0 ? $product_update[$product['id']] - $itemCount : 0;     
            } else {
              $product_update[$product['id']] = $product['count'];
            }
          } else {

            $count = DB::query('
              SELECT count
              FROM `'.PREFIX.'product_variant`
              WHERE id = '.DB::quote($item['variant']));
            $count = DB::fetchAssoc($count);

            $product['count'] = ($count['count'] - $itemCount) >= 0 ? $count['count'] - $itemCount : 0;            
            if (!empty($variant_update[$item['variant']])) {
              $variant_update[$item['variant']] = ($variant_update[$item['variant']] - $itemCount) >= 0 ? $variant_update[$item['variant']] - $itemCount : 0;     
            } else {
              $variant_update[$item['variant']] = $product['count'];
            }            
            $variants = $itemPosition->getVariants($product['id']);
            $firstVariant = reset($variants);
            if ($firstVariant['id'] == $item['variant']) {
              // если приобретен вариант товара, то выясним является ли он первым в наборе, если да то обновим информацию в mg_product
              if (!empty($product_update[$product['id']])) {
                $product_update[$product['id']] = ($product_update[$product['id']] - $itemCount) >= 0 ? $product_update[$product['id']] - $itemCount : 0;     
              } else {
                $product_update[$product['id']] = $product['count'];
              }              
            }
          }
          $updateCountProd[] = "UPDATE `".PREFIX."product` SET `count_buy`= `count_buy` + 1 WHERE `id`=".DB::quote($product['id']);         
        }
      }
    } elseif (!empty($_SESSION['cart'])) {
      foreach ($_SESSION['cart'] as $item) {
        $product = $itemPosition->getProduct($item['id']);
        $product['price_course'] = $product['price'] = MG::setWholePrice($product['price_course'], $item['id'], $item['count'], $item['variantId']);


        $variant = null;
        $discount = null;
        $promocode = null;
        if (!empty($item['variantId']) && $item['id'] == $product['id']) {
          $variants = $itemPosition->getVariants($product['id']);
          $variant = $variants[$item['variantId']];
          $tmp = $variant['price_course'];
          $tmp2 = MG::setWholePrice($variant['price_course'], $item['id'], $item['count'], $item['variantId']);
          if ($tmp != $tmp2) {
            $variant['price_course'] = MG::convertPrice($tmp2);
          }
          $variant['price'] = $variant['price_course'];
          $fulPrice = $product['price'];
          $priceWithCoupon = $cart->applyCoupon($_SESSION['couponCode'], $product['price'], $product);
          $priceWithDiscount = $cart->applyDiscountSystem($product['price'], $product); 
          $product['price'] = $cart->customPrice(array(
            'product' => $product,
            'priceWithCoupon' => $priceWithCoupon, 
            'priceWithDiscount' => $priceWithDiscount['price'],
          ));
          $product['price'] = round($product['price'], 2);
          $product['variant_id'] = $item['variantId'];
          $product['code'] = $variant['code'];
          $product['count'] = $variant['count'];
          $product['weight'] = $variant['weight'];
          $product['title'] .= " ".$variant['title_variant'];
          $discountSystem = $priceWithDiscount['discounts']; 
          $promocode = $priceWithDiscount['discounts'] !='' ? $priceWithDiscount['promo'] : $_SESSION['couponCode'];
          //По ходу формируем массив запросов на обновление количества товаров
          $resCount = $variant['code'];
          $resCount = ($variant['count'] - $item['count']) >= 0 ? $variant['count'] - $item['count'] : 0;
           if (!empty($variant_update[$item['variantId']])) {
              $variant_update[$item['variantId']] = ($variant_update[$item['variantId']] - $item['count']) >= 0 ? $variant_update[$item['variantId']] - $item['count'] : 0;     
            } else {
              $variant_update[$item['variantId']] = $resCount;
            } 
        }
        $product['category_url'] = $product['category_url'] ? $product['category_url'] : 'catalog';
        $productUrl = $product['category_url'].'/'.$product['url'];

        // Если куки не актуальны исключает попадание несуществующего продукта в заказ
        if (!empty($product)) {
          if (!$variant) {
            $product['price'] = $product['price_course'];
            // если выбран формат без копеек, то округляем стоимость до ворматирования. 
            if(in_array(MG::getSetting('priceFormat'), array('1234','1 234','1,234'))) {
              $product['price'] = round($product['price_course']);
            }
            $fulPrice = $product['price'];
          }
          $product['price'] = SmalCart::plusPropertyMargin($fulPrice, $product['property_html'], $currencyRate[$product['currency_iso']]);
          $fulPrice = $product['price'];
          $tempPrice = $product['price'];
          $priceWithCoupon = $cart->applyCoupon($_SESSION['couponCode'], $product['price'], $product);
          $priceWithDiscount = $cart-> applyDiscountSystem($product['price'], $product); 
          $product['price'] = $cart->customPrice(array(
            'product' => $product,
            'priceWithCoupon' => $priceWithCoupon, 
            'priceWithDiscount' => $priceWithDiscount['price'],
          ));                 
          $discountSystem = $priceWithDiscount['discounts']; 
          $promocode = $priceWithDiscount['discounts'] !='' ? $priceWithDiscount['promo'] : $_SESSION['couponCode'];

          $discount = 0;
          if (!empty($tempPrice)) {
            $discount = 100 - ($product['price'] * 100) / $tempPrice;
          }
          $tempPriceCeil = (string) ($product['price']*100);
          $product['price'] = ceil($tempPriceCeil)/100;          

          $productPositions[] = array(
            'id' => $product['id'],
            'variant_id' => $product['variant_id'],
            'name' => $product['title'],
            'url' => $productUrl,
            'code' => $product['code'],
            'price' => $product['price'],
            'count' => $item['count'],
            'property' => $item['property'],
            'coupon' => $promocode,
            'discount' => round($discount, 2),
            'fulPrice' => $fulPrice,
            'weight' => $product['weight'],
            'currency_iso' => $currencyShopIso,
            'discSyst' => $discountSystem ? $discountSystem : '',
          );


          $this->summ += $product['price'] * $item['count'];

          if (!$resCount) {
            $resCount = ($product['count'] - $item['count']) >= 0 ? $product['count'] - $item['count'] : 0;
          }

          //По ходу формируем массив запросов на обновление количества товаров
          if (!$variant) {  
            if (!empty($product_update[$product['id']])) {
                $product_update[$product['id']] = ($product_update[$product['id']] - $item['count']) >= 0 ? $product_update[$product['id']] - $item['count'] : 0;     
              } else {
                $product_update[$product['id']] = $resCount;
              } 
          } else {                      
            $firstVariant = reset($variants);     
            if($firstVariant['id']==$item['variantId']) {            
              // если приобретен вариант товара, то выясним является ли он первым в наборе, если да то обновим информацию в mg_product              
              if (!empty($product_update[$product['id']])) {
                $product_update[$product['id']] = ($product_update[$product['id']] - $item['count']) >= 0 ? $product_update[$product['id']] - $item['count'] : 0;     
              } else {
                $product_update[$product['id']] = $resCount;
              } 
            }    
          };
          $updateCountProd[] = "UPDATE `".PREFIX."product` SET `count_buy`= `count_buy` + 1 WHERE `id`=".DB::quote($product['id']);
          $resCount = null;    
        }
      }
    }

    // Сериализует данные в строку для записи в бд.
    $orderContent = addslashes(serialize($productPositions));

    // Сериализует данные в строку для записи в бд информации об юридическом лице.
    $yurInfo = '';
    if (!empty($adminOrder['yur_info'])) {
      $yurInfo = addslashes(serialize($adminOrder['yur_info']));
    }
    if (!empty($_POST['yur_info'])) {
      $yurInfo = addslashes(serialize($_POST['yur_info']));
    }
    
    // Создает новую модель корзины, чтобы узнать сумму заказа.
    $cart = new Models_Cart();

    // Генерируем уникальный хэш для подтверждения заказа.
    $hash = $this->_getHash($this->email);
    
    //Достаем настройки заказов, чтобы установить статус для нового заказа.
    $propertyOrder = MG::getOption('propertyOrder');
    $propertyOrder = stripslashes($propertyOrder);
    $propertyOrder = unserialize($propertyOrder);
    //Если установлен статус для новых заказов по умолчанию "ожидает оплаты", 
    //для способов оплаты "наличными" или "наложенным" меняем на "в доставке"
    $order_status_id = (in_array($this->payment, array(3, 4)) && $propertyOrder['order_status'] == 1) ? 3 : $propertyOrder['order_status'];

    $summ = (float)number_format($this->summ, 2, '.', '');
    $deliv = MG::numberDeFormat(MG::numberFormat($this->delivery_cost));
    $shopCurr = MG::getOption('currencyShopIso');
    $newCurr = MG::getSetting('currencyShopIso');

    if ($newCurr == $shopCurr) {
      $summ_shop_curr = $summ;
      $delivery_shop_curr = $deliv;
    }
    else{
      $rates = MG::getSetting('currencyRate');
      $summ_shop_curr = (float)round($summ/$rates[$shopCurr],2);
      $delivery_shop_curr = (float)round($deliv/$rates[$shopCurr],2);
    }

    // для получения права на заказ
    if($_SESSION['user']->role != 1 && User::access('admin_zone') == 1) {
      $owner = $_SESSION['user']->id;
    } else {
      $owner = 0;
    }
    
    // Формируем массив параметров для SQL запроса.
    $array = array(
      'owner' => $owner,
      'user_email' => $this->email,
      'summ' => number_format($this->summ, 2, '.', ''),
      'summ_shop_curr' => number_format($summ_shop_curr, 2, '.', ''),
      'currency_iso' => $newCurr,
      'order_content' => $orderContent,
      'phone' => $this->phone,
      'delivery_id' => $this->delivery,
      'delivery_cost' => MG::numberDeFormat(MG::numberFormat($this->delivery_cost)),
      'delivery_shop_curr' => $delivery_shop_curr,
      'payment_id' => $this->payment,
      'paided' => '0',
      'status_id' => (int)$order_status_id,
      'confirmation' => $hash,
      'yur_info' => $yurInfo,
      'name_buyer' => $this->fio,
      'date_delivery' => $this->dateDelivery,
      'delivery_interval' => $this->interval,
      'user_comment' => $this->info,
      'ip'=> $_SERVER['REMOTE_ADDR'],

    );

    if ($this->address_parts) {
      $array['address_parts'] = $this->address_parts;
    }
    else{
      $array['address'] = $this->address;
    }

    // Если заказ оформляется через админку.
    if ($adminOrder) {
      $array['comment'] = $adminOrder['comment'];
      $array['status_id'] = $adminOrder['status_id'];
      $array['date_delivery'] = $adminOrder['date_delivery'];
      DB::buildQuery("INSERT INTO `".PREFIX."order` SET add_date = now(), ", $array);
    } else {
      // Отдает на обработку  родительской функции buildQuery.
      DB::buildQuery("INSERT INTO `".PREFIX."order` SET add_date = now(), ", $array);
    }

    // Заказ номер id добавлен в базу.
    $id = null;
    $id = DB::insertId();
    $_SESSION['usedCouponCode'] = $_SESSION['couponCode'];
    unset($_SESSION['couponCode']);
    

    $orderNumber = $this->getOrderNumber($id);
    $hashStatus = '';
    $linkToStatus = '';
    if (MG::getSetting('autoRegister') == "false" && !USER::isAuth()) {
      $hashStatus = md5($id.$this->email.rand(9999));
      $linkToStatus = '<a href="'.SITE.'/order?hash='.$hashStatus.'" target="blank">'.SITE.'/order?hash='.$hashStatus.'</a>';
    }
    DB::query("UPDATE `".PREFIX."order` SET `number`= ".DB::quote($orderNumber).", `hash`=".DB::quote($hashStatus)." WHERE `id`=".DB::quote($id)."");
    
    // Ссылка для подтверждения заказа
    $link = 'ссылке <a href="'.SITE.'/order?sec='.$hash.'&id='.$id.'" target="blank">'.SITE.'/order?sec='.$hash.'&id='.$id.'</a>';
    $table = "";

    // Формирование тела письма.
    if ($id) {
      // Уменьшаем количество купленных товаров
      if (!empty($updateCountProd)) {
        foreach ($updateCountProd as $sql) {
          DB::query($sql);
        }
      }
      if (!empty($product_update) 
        ) {
        foreach ($product_update as $id_upd => $count_upd) {
          DB::query("UPDATE `".PREFIX."product` SET `count`= ".DB::quote($count_upd)." WHERE `id`=".DB::quote($id_upd)." AND `count`>0");
        }
      }
      if (!empty($variant_update) 
        ) {
        foreach ($variant_update as $id_upd => $count_upd) {
          DB::query("UPDATE `".PREFIX."product_variant` SET `count`= ".DB::quote($count_upd)." WHERE `id`=".DB::quote($id_upd)." AND `count`>0");
        }
      }


      // Если заказ создался, то уменьшаем количество товаров на складе.
      $settings = MG::get('settings');
      $delivery = $this->getDeliveryMethod(false, $this->delivery);
      $sitename = $settings['sitename'];
      $currency = MG::getSetting('currency');
      $paymentArray = $this->getPaymentMethod($this->payment);
      $subj = 'Оформлена заявка №'.($orderNumber != "" ? $orderNumber : $id).' на сайте '.$sitename;
      $orderWeight = 0;


      foreach ($productPositions as &$item) {
        $orderWeight += $item['count']*$item['weight'];
        foreach ($item as &$v) {
          $v = rawurldecode($v);
        }
      }
      
      $opF = unserialize(stripcslashes(MG::getSetting('optionalFields'))); 
      foreach ($opF as $field) {
        if($field['active'] != 1) continue;
        if($field['type'] == 'checkbox') {
          if($this->optFields[MG::translitIt($field['name'])] != '') {
            $OFM[$field['name']] = 'Да';
          } else {
            $OFM[$field['name']] = 'Нет';
          }
        } else {
          $OFM[$field['name']] = $this->optFields[MG::translitIt($field['name'])];
        }
      }

      $phones = explode(', ', MG::getSetting('shopPhone'));

      $paramToMail = array(
        'id' => $id,
        'orderNumber' => $orderNumber,
        'siteName' => MG::getSetting('sitename'),
        'delivery' => $delivery['description'],
        'delivery_interval' => $this->interval,
        'currency' => MG::getSetting('currency'),
        'fio' => $this->fio,
        'email' => $this->email,
        'phone' => $this->phone,
        'address' => $this->address,
        'payment' => $paymentArray['name'],
        'deliveryId' => $this->delivery,
        'paymentId' => $this->payment,
        'adminOrder' => $adminOrder,
        'result' => $this->summ,
        'deliveryCost' => $this->delivery_cost,
        'date_delivery' => $this->dateDelivery,
        'total' => $this->delivery_cost + $this->summ,
        'confirmLink' => $link,
        'ip' => $this->ip,
        'lastvisit' => $_SESSION['lastvisit'],
        'firstvisit' => $_SESSION['firstvisit'],
        'supportEmail' => MG::getSetting('noReplyEmail'),
        'shopName' => MG::getSetting('shopName'),
        'shopPhone' => $phones[0],
        'formatedDate' => date('Y-m-d H:i:s'),
        'productPositions' => $productPositions,
        'couponCode' => $_SESSION['usedCouponCode'],
        'toKnowStatus' => $linkToStatus,
        'userComment' => $this->info,
        'yur_info' => unserialize(stripcslashes($yurInfo)),
        'custom_fields' => $OFM,
        'orderWeight' => $orderWeight,
      );

      if ($this->address_parts) {
        $tmp = array_filter(unserialize(stripcslashes($this->address_parts)));
        foreach ($tmp as $ke => $va) {
          $tmp[$ke] = htmlspecialchars_decode($va);
        }
        $paramToMail['address'] = implode(', ', $tmp);
      }
      
      $emailToUser = MG::layoutManager('email_order', $paramToMail);

      $paramToMail['adminMail'] = true;
      $emailToAdmin = MG::layoutManager('email_order_admin', $paramToMail);

      $mails = explode(',', MG::getSetting('adminEmail'));

      $fromEmail = $this->fio;
      if (strlen($fromEmail) < 2) {
        $fromEmail = $this->email;
      }
      if (strlen($fromEmail) < 2) {
        $fromEmail = MG::getSetting('shopName');
      }
      
      foreach ($mails as $mail) {
        if (preg_match('/^[-._a-zA-Z0-9]+@(?:[a-zA-Z0-9][-a-zA-Z0-9]+\.)+[a-zA-Z]{2,6}$/', $mail)) {
          Mailer::addHeaders(array("Reply-to" => $this->email));
          Mailer::sendMimeMail(array(
            'nameFrom' => $fromEmail,
            'emailFrom' => MG::getSetting('noReplyEmail'),
            'nameTo' => $sitename,
            'emailTo' => $mail,
            'subject' => $subj,
            'body' => $emailToAdmin,
            'html' => true
          ));
        }
      }

      // Отправка заявки пользователю.
      Mailer::sendMimeMail(array(
        'nameFrom' => MG::getSetting('shopName'),
        'emailFrom' => MG::getSetting('noReplyEmail'),
        'nameTo' => $this->fio,
        'emailTo' => $this->email,
          'subject' => 'Регистрация на '.$sitename,
          'body' => $emailToUser,
          'html' => true
      ));

        $pass = $this->passNewUser;

        if ($pass) {
            $emailToUser = MG::layoutManager('email_order_new_user', array('fio' => $this->fio, 'email' => $this->email, 'pass' => $pass));

            // Отправка данных для входа новому пользователю.
            Mailer::sendMimeMail(array(
                'nameFrom' => MG::getSetting('shopName'),
                'emailFrom' => MG::getSetting('noReplyEmail'),
                'nameTo' => $this->fio,
                'emailTo' => $this->email,
                'subject' => $subj,
                'body' => $emailToUser,
                'html' => true
            ));
        }

      // Если заказ успешно записан, то очищает корзину.
      if (!$adminOrder) {
        $cart->clearCart();
      }
    }
    
    $result =array('id'=>$id, 'orderNumber' => $orderNumber);
    // Возвращаем номер созданного заказа.
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Отправляет сообщение о смене статуса заказа его владельцу.
   * <code>
   * $model = new Models_Order;
   * $model->sendStatusToEmail(5, 3, 'Ваш заказ передан в службу доставки');
   * </code>
   * @param int $id номер заказа.
   * @param int $statusId новый статус.
   * @param string $text текст письма.
   */
  public function sendStatusToEmail($id, $statusId, $text = '') {
    $order = $this->getOrder('id = '.DB::quote(intval($id)));
    $lang = MG::get('lang');
    $statusArray = self::$status;
    if (class_exists('statusOrder')) {
      $dbQuery = DB::query('SELECT `id_status`, `status` FROM `'.PREFIX.'mg-status-order` ');
      while ($dbRes = DB::fetchArray($dbQuery)) {
        self::$statusUser[$dbRes['id_status']] = $dbRes['status'];
      }
    }
    $statusName = self::$statusUser[$statusId] ? self::$statusUser[$statusId] : $lang[$statusArray[$statusId]];
    $statusOldName = self::$statusUser[$order[$id]['status_id']] ? self::$statusUser[$order[$id]['status_id']] : $lang[$statusArray[$order[$id]['status_id']]];
     
     $paramToMail = array(
      'orderInfo' => $order[$id],
      'statusId' => $statusId,
      'statusName' => $statusName,
      'statusOldName' => $statusOldName,
      'comment' => $text
    );
    if ($statusName !== $statusOldName) {

      $emailToUser = MG::layoutManager('email_order_change_status', $paramToMail);

      Mailer::addHeaders(array("Reply-to" => MG::getSetting('noReplyEmail')));
      Mailer::sendMimeMail(array(
        'nameFrom' => MG::getSetting('shopName'),
        'emailFrom' => MG::getSetting('noReplyEmail'),
        'nameTo' => $order[$id]['user_email'],
        'emailTo' => $order[$id]['user_email'],
        'subject' => "Заказ №".$order[$id]['number']." ".$statusName,
        'body' => $emailToUser,
        'html' => true
      ));
      $result = $paramToMail;
    } else {
      $result = false;
    }
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Изменяет данные о заказе
   * <code>
   * $array = array(
   *  'address' => 'addr', // адрес доставки
   *  'date_delivery' => '08.03.2018', // дата доставки
   *  'comment' => 'comment', // комментарий менеджера
   *  'delivery_cost' => 700, // стоимость доставки
   *  'delivery_id' => 1, // ID доставки
   *  'id' => 3, // ID заказа
   *  'number' => 'M-0105268947551', // код заказа
   *  'name_buyer' => 'Администратор', // имя покупателя
   *  'payment_id' => 1, // ID оплаты
   *  'phone' => '+7 (111) 111-11-11', // телефон пользователя
   *  'status_id' => 0, // ID статуса заказа
   *  'summ' => 100, // сумма заказа без доставки
   *  'currency_iso' => 'RUR', // код валюты заказа
   *  'user_email' => 'admin@admin.ru', // почта пользователя
   *  'order_content' => 'string', // сериализованный массив состава заказа
   *  'storage' => 'default', // склад для заказа
   *  'summ_shop_curr' => 100, // сумма заказа без доставки в основной валюте магазина
   *  'delivery_shop_curr' => 700, // стоимость доставки в основной валюте магазина
   *  'yur_info' => 'string' // сериализованный массив юридических данных
   * )
   * $model = new Models_Order;
   * $model->updateOrder($array , true, 'Ваш заказ успешно обновлен');
   * </code>
   * @param array $array массив с данными о заказе.
   * @param bool $informUser информировать ли пользователя об изменении заказа.
   * @param string $text комментарий к заказу.
   * @return bool
   */
  public function updateOrder($array, $informUser = false, $text = '') {
    $id = $array['id'];
    unset($array['id']);

    $this->refreshCountProducts($id, $array['status_id']);

    if (!empty($array['status_id']) && $informUser == 'true') {
      $this->sendStatusToEmail($id, $array['status_id'], $text);
    }

    $result = false;
    if (!empty($id)) {  
      if (DB::query('
        UPDATE `'.PREFIX.'order`
        SET '.DB::buildPartQuery($array).'
        WHERE id = '.DB::quote($id))) {
        $result = true;
      }
      if($array['status_id'] == 2) {
        DB::query('UPDATE '.PREFIX.'order SET pay_date = NOW() WHERE id = '.DB::quote($id));
      }
    }
    $array['id'] = $id;
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Пересчитывает количество остатков продуктов при отмене заказа.
   * <code>
   * $model = new Models_Order;
   * $model->refreshCountProducts(5, 4);
   * </code>
   * @param int $orderId id заказа.
   * @param int $status_id новый статус заказа.
   * @return bool
   */
  public function refreshCountProducts($orderId, $status_id) {
    // Если статус меняется на "Отменен", то пересчитываем остатки продуктов из заказа.
    $order = $this->getOrder(' id = '.DB::quote(intval($orderId)));
    // Увеличиваем колличество товаров. 
    if ($status_id == 4) {
      if (($order[$orderId]['status_id'] != 4)&&($order[$orderId]['status_id'] != 5)) {
        $order_content = unserialize(stripslashes($order[$orderId]['order_content']));
        $product = new Models_Product();
        foreach ($order_content as $item) {
            $product->increaseCountProduct($item['id'], $item['code'], $item['count']);
        }
      }
    } else {
      // Уменьшаем колличество товаров. 
      if ($order[$orderId]['status_id'] == 4) {
        $order_content = unserialize(stripslashes($order[$orderId]['order_content']));
        $product = new Models_Product();
        foreach ($order_content as $item) {
            $product->decreaseCountProduct($item['id'], $item['code'], $item['count']);
        }
      }
    }    
    $result = true;
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Удаляет заказ из базы данных.
   * <code>
   * $model = new Models_Order;
   * $model->deleteOrder(false, array(1,2,3,4,5));
   * </code>
   * @param int $id id удаляемого заказа
   * @param array|null $arrayId массив id заказов, которые требуется удалить
   * @return bool
   */
  public function deleteOrder($id, $arrayId = null) {
    $result = false;
    if (empty($arrayId)) {
      if (DB::query('
        DELETE
        FROM `'.PREFIX.'order`
        WHERE id = %d
      ', $id)) {
        $result = true;
      }
    } else {
      $where = '('.implode(',', $arrayId).')';
      if (DB::query('
        DELETE
        FROM `'.PREFIX.'order`
        WHERE id in %s
      ', $where)) {
        $result = true;
      }
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Возвращает массив заказов подцепляя данные о способе доставки.
   * <code>
   * $model = new Models_Order;
   * $orders = $model->getOrder('id IN (1,2,3,4,5)');
   * viewData($orders);
   * </code>
   * @param string $where необязательный параметр формирующий условия поиска заказа, например: id = 1
   * @return array массив заказов
   */
  public function getOrder($where = '') {

    if ($where) {
      $where = 'WHERE '.$where;
    }

    $result = DB::query('
      SELECT  *
      FROM `'.PREFIX.'order`'.$where.' ORDER BY id desc');

    while ($order = DB::fetchAssoc($result)) {

      $delivery = $this->getDeliveryMethod(false, $order['delivery_id']);
      $order['description'] = $delivery['description'];
      $order['cost'] = $delivery['cost'];
      // декодируем параметры заказа
      $order['order_content'] = unserialize(stripslashes($order['order_content']));
      foreach ($order['order_content'] as &$item) {
        foreach ($item as &$v) {
          $v = rawurldecode($v);
        }
      }
      $order['order_content'] = addslashes(serialize($order['order_content']));

      $orderArray[$order['id']] = $order;
    }
    return $orderArray;
  }

  /**
   * Устанавливает переданный статус заказа.
   * <code>
   * $result = Models_Order::setOrderStatus(5, 4);
   * var_dump($result);
   * </code>
   * @param int $id номер заказа.
   * @param int $statusId статус заказа.
   * @return bool результат выполнения метода.
   */
  public function setOrderStatus($id, $statusId) {
    $res = DB::query('
      UPDATE `'.PREFIX.'order`
      SET status_id = %d
      WHERE id = %d', $statusId, $id);

    if ($res) {
      $result = true;
    }
    else{
      $result = false;
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Генерация случайного хэша.
   * <code>
   * $email = 'admin@mail.mail';
   * $hash = Models_Order::_getHash($email);
   * echo $hash;
   * </code>
   * @param string $string - строка, на основе которой готовится хэш.
   * @return string случайный хэш
   */
  public function _getHash($string) {
    $hash = htmlspecialchars(crypt($string));
    return $hash;
  }

  /**
   * Получение данных о способах доставки.
   * <code>
   * $order = new Models_Order();
   * $result = $order->getDeliveryMethod();
   * viewData($result);
   * </code>
   * @param bool $returnArray возвращать несколько способов доставки
   * @param int $id способа доставки
   * @return array массив содержащий способы доставки.
   */
  public function getDeliveryMethod($returnArray = true, $id = -1) {

    if(!empty($_POST['orderItems'])){
      $itemsCart['items'] = $_POST['orderItems'];

    }else{
      $cart = new Models_Cart();
      $itemsCart = $cart->getItemsCart();
    }    

    $sumWeight = 0;

    for($i=0; $i<count($itemsCart['items']); $i++){
      $sumWeight += $itemsCart['items'][$i]['weight']*$itemsCart['items'][$i]['countInCart'];
    }

    if ($returnArray) {

      $deliveryArray = array();
      $result = DB::query('SELECT  *  FROM `'.PREFIX.'delivery` ORDER BY `sort`');
      while ($delivery = DB::fetchAssoc($result)) {
        $deliveryArray[$delivery['id']] = $delivery;
        $deliveryIds[] = $delivery['id'];
      }

      if (!empty($deliveryIds)) {
        $in = 'in('.implode(',', $deliveryIds).')';
        $deliveryCompareArray = array();
        $res = DB::query('
          SELECT  *  
          FROM `'.PREFIX.'delivery_payment_compare` 
          WHERE `delivery_id` '.$in);
        while ($row = DB::fetchAssoc($res)) {
          $deliveryCompareArray[$row['delivery_id']][] = $row;
        }
      }

      foreach ($deliveryArray as &$item) {
        // Получаем доступные методы оплаты $delivery['paymentMethod'] для данного способа доставки.

        $jsonStr = '{';
        if (!empty($deliveryCompareArray[$item['id']])) {
          foreach ($deliveryCompareArray[$item['id']] as $compareMethod) {
            $jsonStr .= '"'.$compareMethod['payment_id'].'":'.$compareMethod['compare'].',';
          }
          $jsonStr = substr($jsonStr, 0, strlen($jsonStr) - 1);
        }
        $jsonStr .= '}';
        $item['paymentMethod'] = $jsonStr;

        if (!MG::isAdmin() && $item['weight']) {
          $weights = json_decode($item['weight'],1);
          foreach ($weights as $key => $value) {
            if ($sumWeight >= $value['w']) {
              $item['cost'] = $value['p'];
            }
          }
        }

        if ($item['address_parts'] == '1') {
          $item['address_parts'] = '["index","country","region","city","street","house","flat"]';
        }
        else{
          $item['address_parts'] = '';
        }

      }

      return $deliveryArray;
    } elseif ($id >= 0) {
      $result = DB::query('
        SELECT `description`, `cost`, `free`, `plugin`, `weight`
        FROM `'.PREFIX.'delivery`
        WHERE id = %d', $id);
      $tmp = DB::fetchAssoc($result);

      if (!MG::isAdmin() && $item['weight']) {
        $weights = json_decode($item['weight'],1);
        foreach ($weights as $key => $value) {
          if ($sumWeight >= $value['w']) {
            $item['cost'] = $value['p'];
          }
        }
      }

      
      return $tmp;
    }
  }

  /**
   * Проверяет, существуют ли способы доставки.
   * <code>
   * var_dump(Models_Order::DeliveryExist());
   * </code>
   * @return bool
   */
  public function DeliveryExist() {
    if (DB::numRows(DB::query('SELECT  *  FROM `'.PREFIX.'delivery` ORDER BY id'))) {
      return true;
    }
    return false;
  }

  /**
   * Расшифровка по id статуса заказа.
   * <code>
   * echo Models_Order::getOrderStatus(4);
   * </code>
   * @param int $statusId id статуса заказа.
   * @return string
   */
  public function getOrderStatus($statusId) {

    switch ($statusId['status_id']) {
      case 0:
        $msg = 'msg__status_not_confirmed';
        break;
      case 1:
        $msg = 'msg__status_expects_payment';
        break;
      case 2:
        $msg = 'msg__status_paid';
        break;
      case 3:
        $msg = 'msg__status_in_delivery';
        break;
      case 4:
        $msg = 'msg__status_canceled';
        break;
      case 5:
        $msg = 'msg__status_executed';
        break;
      case 6:
        $msg = 'msg__status_processing';
        break;
    }
    $res = DB::query("SELECT `id`, `text` FROM `".PREFIX."messages` WHERE `name` = '".$msg."'");
    $row = DB::fetchAssoc($res);
    return $row['text'];
  }

  /**
   * Расшифровка по id методов оплаты.
   * <code>
   * $order = new Models_Order();
   * $result = $order->getPaymentMethod(14);
   * viewData($result);
   * </code>
   * @param int $paymentId
   * @return array
   */
  public function getPaymentMethod($paymentId) {

    if (count($this->_paymentArray) < $paymentId) {
      return false;
    }

    //получаем доступные методы доставки $this->_paymentArray[$paymentId]['deliveryMethod'] для данного способа оплаты
    //массив соответствия доставки к данному методу.
    $compareArray = $this->getCompareMethod('payment_id', $paymentId);

    if (count($compareArray)) {
      $jsonStr = '{';

      foreach ($compareArray as $compareMethod) {
        $jsonStr .= '"'.$compareMethod['delivery_id'].'":'.$compareMethod['compare'].',';
      }

      $jsonStr = substr($jsonStr, 0, strlen($jsonStr) - 1);
      $jsonStr .= '}';

      $this->_paymentArray[$paymentId]['deliveryMethod'] = $jsonStr;
    } else {
	  $this->_paymentArray[$paymentId]['deliveryMethod'] = '{}';
	}
      return $this->_paymentArray[$paymentId];
  }

  /**
   * Получает набор всех способов доставки.
   * <code>
   * $order = new Models_Order();
   * $result = $order->getPaymentBlocksMethod();
   * viewData($result);
   * </code>
   * @return array
   */
  public function getPaymentBlocksMethod() {

    $paymentArray = array();
    foreach ($this->_paymentArray as $payment) {
      $paymentArray[$payment['id']] = $payment;
      $paymentIds[] = intval($payment['id']);
    }
    $compareArray = array();
    if (!empty($paymentIds)) {
      $in = 'in('.implode(',', $paymentIds).')';
      $res = DB::query('
          SELECT  *  
          FROM `'.PREFIX.'delivery_payment_compare` 
          WHERE `payment_id` '.$in);
      while ($row = DB::fetchAssoc($res)) {
        $compareArray[$row['payment_id']][] = $row;
      }
    }

    foreach ($paymentArray as &$item) {

      // Получаем доступные методы оплаты $delivery['paymentMethod'] для данного способа доставки.
      $jsonStr = '{';
      if (empty($compareArray[$item['id']])) {
        continue;
      }

      foreach ($compareArray[$item['id']] as $compareMethod) {
        $jsonStr .= '"'.$compareMethod['delivery_id'].'":'.$compareMethod['compare'].',';
      }
      $jsonStr = substr($jsonStr, 0, strlen($jsonStr) - 1);
      $jsonStr .= '}';
      $item['deliveryMethod'] = $jsonStr;
    }

    return $paymentArray;
  }

  /**
   * Возвращает весь список способов оплаты в ассоциативном массиве с индексами.
   * <code>
   * $result = Models_Order::getListPayment();
   * viewData($result);
   * </code>
   * @return array
   */
  public function getListPayment() {
    $result = array();
    $res = DB::query('SELECT  *  FROM `'.PREFIX.'payment`');

    while ($row = DB::fetchAssoc($res)) {
      $result[$row['id']] = $row['name'];
    }
    return $result;
  }

  /**
   * Возвращает максимальную сумму заказа.
   * <code>
   * echo Models_Order::getMaxPrice();
   * </code>
   * @return string
   */
  public function getMaxPrice() {
    $res = DB::query('
      SELECT MAX(`summ_shop_curr`+`delivery_shop_curr`) as summ 
      FROM `'.PREFIX.'order`');

    if ($row = DB::fetchObject($res)) {
      $result = $row->summ;
    }

    return $result;
  }

  /**
   * Возвращает минимальную сумму заказа.
   * <code>
   * echo Models_Order::getMinPrice();
   * </code>
   * @return string
   */
  public function getMinPrice() {
    $res = DB::query('
      SELECT MIN(`summ_shop_curr`+`delivery_shop_curr`) as summ 
      FROM `'.PREFIX.'order`'
    );
    if ($row = DB::fetchObject($res)) {
      $result = $row->summ;
    }
    return $result;
  }

  /**
   * Возвращает дату последнего заказа.
   * <code>
   * echo Models_Order::getMaxDate();
   * </code>
   * @return string
   */
  public function getMaxDate() {
    $res = DB::query('
      SELECT MAX(add_date) as res 
      FROM `'.PREFIX.'order`');

    if ($row = DB::fetchObject($res)) {
      $result = $row->res;
    }

    return $result;
  }

  /**
   * Возвращает дату первого заказа.
   * <code>
   * echo Models_Order::getMinDate();
   * </code>
   * @return array
   */
  public function getMinDate() {
    $res = DB::query('
      SELECT MIN(add_date) as res 
      FROM `'.PREFIX.'order`'
    );
    if ($row = DB::fetchObject($res)) {
      $result = $row->res;
    }
    return $result;
  }

  /**
   * Возвращает весь список способов доставки в ассоциативном массиве с индексами.
   * <code>
   * $result = Models_Order::getListDelivery();
   * viewData($result);
   * </code>
   * @return array
   */
  public function getListDelivery() {
    $result = array();
    $res = DB::query('SELECT * FROM `'.PREFIX.'delivery`');
    while ($row = DB::fetchAssoc($res)) {
      $result[$row['id']] = $row['name'];
    }
    return $result;
  }

  /**
   * Получение статуса оплаты.
   * <code>
   * echo Models_Order::getPaidedStatus(5);
   * </code>
   * @param array $paidedId массив с заказом
   * @return string
   */
  public function getPaidedStatus($paidedId) {
    if (1 == $paidedId['paided']) {
      return 'оплачен';
    } else {
      return 'не оплачен';
    }
  }

  /**
   * Возвращает общее количество заказов.
   * <code>
   * echo Models_Order::getOrderCount('WHERE status_id = 5');
   * <\code>
   * @param string $where условие выбора
   * @return string
   */
  public function getOrderCount($where = '') {
    $result = 0;
    $res = DB::query('
      SELECT count(id) as count
      FROM `'.PREFIX.'order`
    '.$where);

    if ($order = DB::fetchAssoc($res)) {
      $result = $order['count'];
    }

    return $result;
  }

  /**
   * Возвращает информацию о соответствии методов оплаты к методам доставки.
   * <code>
   * $result = Models_Order::getCompareMethod('payment_id', 19);
   * viewData($result);
   * </code>
   * @param string $methodSearch - название поля в базе данных
   * @param int $id - значение поля в базе данных
   * @return array 
   */
  private function getCompareMethod($methodSearch, $id) {
    $result = array();
    $res = DB::query('
      SELECT  *  
      FROM `'.PREFIX.'delivery_payment_compare` 
      WHERE `%s` = %d', $methodSearch, $id);
    while ($row = DB::fetchAssoc($res)) {
      $result[] = $row;
    }
    return $result;
  }

  /**
   * Отправляет сообщение  об оплате заказа.
   * <code>
   * $model = new Models_Order;
   * $model->sendMailOfPayed(5, 1500, 19);
   * </code>
   * @param string $orderNamber id заказа.
   * @param string $paySumm сумма заказа.
   * @param string $pamentId id способа оплаты.
   */
  public function sendMailOfPayed($orderNamber, $paySumm, $pamentId) {
    $pamentArray = $this->_paymentArray[$pamentId];
    $siteName = MG::getSetting('sitename');
    $adminEmail = MG::getSetting('adminEmail');
    $subj = 'Оплата заказа '.$orderNamber.' на сайте '.$siteName;
    if (class_exists('statusOrder')) {
      $dbQuery = DB::query('SELECT `status` FROM `'.PREFIX.'mg-status-order` '
        . 'WHERE `id_status`=2');
      if ($dbRes = DB::fetchArray($dbQuery)) {
        $status = $dbRes['status'];
      }
    } 
    if (!$status) {
      $lang = MG::get('lang');
      $status = $lang['PAID'];
    }

      $res = DB::query("SELECT `summ_shop_curr`, `delivery_shop_curr` FROM `".PREFIX."order` WHERE `id` = ".DB::quoteInt($orderNamber));
      if ($row = DB::fetchAssoc($res)) {
          $paySumm = $row['summ_shop_curr'] + $row['delivery_shop_curr'];
    }

    $paramToMail = array(
        'number' => $orderNamber,
        'summ' => $paySumm,
      'payment'=> $pamentArray['name'],
      'status'=> $status);
    
    $emailToUser = MG::layoutManager('email_order_paid', $paramToMail);
    $mails = explode(',', MG::getSetting('adminEmail'));

    foreach ($mails as $mail) {
      if (preg_match('/^[-._a-zA-Z0-9]+@(?:[a-zA-Z0-9][-a-zA-Z0-9]+\.)+[a-zA-Z]{2,6}$/', $mail)) {
        Mailer::sendMimeMail(array(
          'nameFrom' => MG::getSetting('shopName'),
          'emailFrom' => MG::getSetting('noReplyEmail'),
          'nameTo' => $sitename,
          'emailTo' => $mail,
          'subject' => $subj,
          'body' => $emailToUser,
          'html' => true
        ));
      }
    }
    $result = true;
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Возвращает ссылки на скачивания электронных товаров.
   * <code>
   * $model = new Models_Order;
   * $result = $model->sendMailOfPayed(5);
   * viewData($result);
   * </code>
   * @param int $orderId id заказа
   * @return array
   */
  public function getFileToOrder($orderId) {
    $linksElectro = array();
    $orderId = (int) $orderId;
    $userInfo = USER::getThis();

    if (empty($userInfo)) {
      return false;
    }

    $orderInfo = $this->getOrder('
      id = '.DB::quote($orderId, true).' AND 
      user_email = '.DB::quote($userInfo->email).' AND
      (status_id = 2 OR status_id = 5)'
    );

    $orderInfo[$orderId]['order_content'] = unserialize(stripslashes($orderInfo[$orderId]['order_content']));
    $product = new Models_Product();
    if (!empty($orderInfo[$orderId]['order_content'])) {
      foreach ($orderInfo[$orderId]['order_content'] as $item) {
        $productInfo = $product->getProduct($item['id']);
        if ($productInfo['link_electro']) {
          $linksElectro[] = array(
            'link' => SITE.'/order?link='.md5($userInfo->email.$productInfo['link_electro']),
            'title' => $productInfo['title'],
            'product' => $productInfo,
          );
        }
      }
    }

    return $linksElectro;
  }

  /**
   * Возвращает файл по хэшу.
   * <code>
   * Models_Order::getFileByMd5('$1$.z8cFb7V$zt15YCRQ3442XaOU8mkWh1');
   * </code>
   * @param string $md5
   * @return bool
   */
  public function getFileByMd5($md5) {
    $linksElectro = array();

    $userInfo = USER::getThis();

    if (empty($userInfo)) {
      return false;
    }

    $res = DB::query('
      SELECT `link_electro`
      FROM `'.PREFIX.'product`
      WHERE MD5(concat('.DB::quote($userInfo->email).',`link_electro`)) = '.DB::quote($md5).' 
    ');

    if ($row = DB::fetchAssoc($res)) {
      $realLink = $row['link_electro'];
    }

    $realLink = str_replace('/', DS, trim($realLink, DS));
    $realLink = URL::getDocumentRoot().urldecode($realLink);
    
    if ($realLink) {
      header("Content-Length: ".filesize($realLink));
      header("Content-type: application/octed-stream");
      header('Content-Disposition: attachment; filename="'.basename($realLink).'"');
      readfile($realLink);
      exit();
    }
  }

  /**
   * Отправляет письмо со ссылками на приобретенные электронные товары
   * <code>
   * $model = new Models_Order;
   * $model->sendLinkForElectro(5);
   * </code>
   * @param string $orderId номер заказа.
   */
  public function sendLinkForElectro($orderId) {
    $linksElectro = array();
    $orderInfo = $this->getOrder(' id = '.DB::quote(intval($orderId), true));
    $orderInfo[$orderId]['order_content'] = unserialize(stripslashes($orderInfo[$orderId]['order_content']));
    $product = new Models_Product();
    foreach ($orderInfo[$orderId]['order_content'] as $item) {
      $productInfo = $product->getProduct($item['id']);
      if ($productInfo['link_electro']) {
        $linksElectro[] = $productInfo['link_electro'];
      }
    }
    // если нет электронных товаров в заказе, то не высылаем письмо
    if (empty($linksElectro)) {
      return false;
    }

    $siteName = MG::getSetting('sitename');
    $adminEmail = MG::getSetting('adminEmail');
    $userEmail = $orderInfo[$orderId]['user_email'];
    $orderNumber = $orderInfo['orderNumber'] != '' ? $orderInfo['orderNumber'] : $orderId;
    $subj = 'Ссылка для скачивания по заказу'.$orderNamber.' на сайте '.$siteName;

    $paramToMail = array(
      'orderNumber' => $orderNumber,
      'getElectro' => SITE.'/order?getFileToOrder='.$orderId
    );

    $emailToUser = MG::layoutManager('email_order_electro', $paramToMail);

    if (preg_match('/^[-._a-zA-Z0-9]+@(?:[a-zA-Z0-9][-a-zA-Z0-9]+\.)+[a-zA-Z]{2,6}$/', $userEmail)) {
      Mailer::sendMimeMail(array(
        'nameFrom' => MG::getSetting('shopName'),
        'emailFrom' => MG::getSetting('noReplyEmail'),
        'nameTo' => $userEmail,
        'emailTo' => $userEmail,
        'subject' => $subj,
        'body' => $emailToUser,
        'html' => true
      ));
    }

    $mails = explode(',', MG::getSetting('adminEmail'));

    foreach ($mails as $mail) {
      if (preg_match('/^[-._a-zA-Z0-9]+@(?:[a-zA-Z0-9][-a-zA-Z0-9]+\.)+[a-zA-Z]{2,6}$/', $mail)) {
        Mailer::sendMimeMail(array(
          'nameFrom' => MG::getSetting('shopName'),
          'emailFrom' => MG::getSetting('noReplyEmail'),
          'nameTo' => $sitename,
          'emailTo' => $mail,
          'subject' => $subj,
          'body' => 'Пользователю '.$userEmail.' выслана ссылка на электронные товары',
          'html' => true
        ));
      }
    }
  }

  /**
   * Уведомляет админов о смене статуса заказа пользователем, высылая им письма.
   * <code>
   * $model = new Models_Order;
   * $model->sendMailOfUpdateOrder(5);
   * </code>
   * @param int $orderId id заказа.
   */
  public function sendMailOfUpdateOrder($orderId, $comment = null) {
    $order = $this->getOrder('id = '.DB::quote(intval($orderId)));
    $orderNumber = $order[$orderId]['number'];
    $siteName = MG::getSetting('sitename');
    $adminEmail = MG::getSetting('adminEmail');
    if (class_exists('statusOrder')) {
      $dbQuery = DB::query('SELECT `status` FROM `'.PREFIX.'mg-status-order` '
        . 'WHERE `id_status`=4');
      if ($dbRes = DB::fetchArray($dbQuery)) {
        $status = $dbRes['status'];
      }
    }
    if (!$status) {
      $lang = MG::get('lang');
      $status = $lang['CANSELED'];
    }
    $subj = 'Пользователь отменил заказ №'.$orderNumber.' на сайте '.$siteName;
    $msg = '
      Вы получили это письмо, так как произведена смена статуса заказа.
     <br/>Статус заказа #'.$orderNumber.' сменен на "'.$status.'".';
     if ($comment) {
       $msg .= '<br/>По причине: '.$comment;
     }

    $mails = explode(',', MG::getSetting('adminEmail'));

    foreach ($mails as $mail) {
      if (preg_match('/^[-._a-zA-Z0-9]+@(?:[a-zA-Z0-9][-a-zA-Z0-9]+\.)+[a-zA-Z]{2,6}$/', $mail)) {
        Mailer::sendMimeMail(array(
          'nameFrom' => MG::getSetting('shopName'),
          'emailFrom' => MG::getSetting('noReplyEmail'),
          'nameTo' => $sitename,
          'emailTo' => $mail,
          'subject' => $subj,
          'body' => $msg,
          'html' => true
        ));
      }
    }
  }

  /**
   * Возвращает массив параметров оплаты.
   * <code>
   * $order = new Models_Order;
   * $paymentInfo = $order->getParamArray(15, 5, 1500);
   * viewData($paymentInfo);
   * </code>
   * @param int $pay id способа оплаты.
   * @param int $orderId id заказа.
   * @param float $summ сумма заказа.
   * @return array параметры оплаты.
   */
  public function getParamArray($pay, $orderId, $summ) {
    $paramArray = array();
    $jsonPaymentArray = json_decode(MG::nl2br($this->_paymentArray[$pay]['paramArray']), true);
    if (!empty($jsonPaymentArray)) {
      foreach ($jsonPaymentArray as $paramName => $paramValue) {
        $paramArray[] = array('name' => $paramName, 'value' => $paramValue);
      }
      if (5 == $pay) { // Для robokassa добавляем сигнатуру.
        $alg = $paramArray[3]['value'];
        $login = trim($paramArray[0]['value']);
        $pass1 = trim($paramArray[1]['value']);
        $paramArray['sign'] = hash($alg,$login.":".$summ.":".$orderId.":".$pass1);
      }
      if (9 == $pay) { // Для payanyway добавляем сигнатуру.	    
        $summ = sprintf("%01.2f", $summ);
        $currency = (MG::getSetting('currencyShopIso') == "RUR") ? "RUB" : MG::getSetting('currencyShopIso');
        $testmode = 0;
        
        if ($paramArray[2]['value'] == 'true') {
          $testmode = 1;
        }

        $alg = $paramArray[3]['value'];
        $account = trim($paramArray[0]['value']);
        $securityCode = trim($paramArray[1]['value']);
        $paramArray['sign'] = hash($alg, $account.$orderId.$summ.$currency.$testmode.$securityCode);
      }
      if(15 == $pay) {
        $model = new Models_Order;
        $summ = sprintf("%01.2f", $summ);
        $order = $model->getOrder(' id = '.DB::quote(intval($orderId), true));
        $payment = 'amt='.$summ.'&ccy=UAH&details=заказ на '.SITE.'&ext_details='.$order[$orderId]['number']
          .'&pay_way=privat24&order='.$orderId.'&merchant='.trim($paramArray[0]['value']);
        $pass = trim($paramArray[1]['value']);
        $paramArray['sign'] = sha1(md5($payment.$pass));
      }
      if(16 == $pay) {
        $model = new Models_Order;        
        $order = $model->getOrder(' id = '.DB::quote(intval($orderId), true));                
        $amount = sprintf("%01.2f", $summ);        
        $currency = MG::getSetting('currencyShopIso');
        
        if($currency == 'RUR') {
          $currency = 'RUB';
        }
        
        $params = array(
          'version'     => 3,
          'public_key'  => trim($paramArray[0]['value']),
          'action'      => 'pay',
          'amount'      => $amount,
          'currency'    => $currency,
          'description' => 'Оплата заказа № '.$order[$orderId]['number'],
          'order_id'    => $orderId,
          'server_url'  => SITE.'/payment?id=16&pay=result',
          'result_url'  => SITE.'/payment?id=16&order_id='.$orderId,
        );                

        //Для проведения тестовых платежей
        if(!empty($paramArray[2]['value']) && $paramArray[2]['value'] != "false") {
          $params['sandbox'] = 1;
        }                
        
        $paramArray['data'] = base64_encode(json_encode($params));
        $privateKey = trim($paramArray[1]['value']);
        $paramArray['signature'] = base64_encode(sha1($privateKey.$paramArray['data'].$privateKey, 1));
      }
    }
    
    return $paramArray;
  }

  /**
   * Создает дубль заказа
   * <code>
   * $order = new Models_Order;
   * $order->cloneOrder(5);
   * </code>
   * @param $id - id копируемого заказа
   * @return bool
   */
  public function cloneOrder($id) {
    // учет остатков товаров в заказе
    $res = DB::query('SELECT `order_content`, `storage` FROM `'.PREFIX.'order` WHERE `id`= '.DB::quote($id));
    if ($row = DB::fetchArray($res)) {
      $content = unserialize(stripslashes($row['order_content']));
    }
    $allAvailable = true;
    foreach ($content as $item) {
      if ( $this->notSetGoods($item['id'])==false) {
        return false;
      }
      $res = DB::query('SELECT p.`count`, pv.`count` AS  `var_count`, pv.`code` 
        FROM `'.PREFIX.'product` p LEFT JOIN 
        `'.PREFIX.'product_variant` pv ON p.id = pv.product_id WHERE p.id='.DB::quote($item['id']));
      while($row = DB::fetchArray($res)) {
        if (!empty($row['code'])&& $row['code'] == $item['code']) {
          $count = $row['var_count'];
        } elseif(empty($row['code'])) {
          $count = $row['count'];
        }
      }
      if ($count >= 0 && $count < $item['count']) {
         $allAvailable = false;
      }
    }
    if ($allAvailable == false ) {
      return false;
    }
    $product = new Models_Product();
    foreach ($content as $item) {
        $product->decreaseCountProduct($item['id'], $item['code'], $item['count']);
    }
    $sql = " INSERT INTO  
      `".PREFIX."order`
        ( 
          `updata_date`, 
          `add_date`, 
          `close_date`, 
          `user_email`, 
          `phone`, 
          `address`, 
          `summ`, 
          `order_content`, 
          `delivery_id`, 
          `delivery_cost`, 
          `payment_id`, 
          `paided`, 
          `status_id`, 
          `comment`, 
          `confirmation`, 
          `yur_info`, 
          `name_buyer`,
          `storage`,
          `summ_shop_curr`,
          `delivery_shop_curr`,
          `currency_iso`
        ) 
      SELECT 
        `updata_date`, 
         now() as `add_date`,
        `close_date`, 
        `user_email`, 
        `phone`, 
        `address`, 
        `summ`,
        `order_content`,
        `delivery_id`,
        `delivery_cost`,
        `payment_id`,
        `paided`,
        `status_id`,
        `comment`,
        `confirmation`,
        `yur_info`,
        `name_buyer`,
        `storage`,
        `summ_shop_curr`,
        `delivery_shop_curr`,
        `currency_iso`
      FROM ".PREFIX."order
      WHERE `id`= ".DB::quote($id);
    $res = DB::query($sql);
    $id = DB::insertId();
    $orderNumber = $this->getOrderNumber($id);
    DB::query("UPDATE `".PREFIX."order` SET `number`= ".DB::quote($orderNumber)." WHERE `id`=".DB::quote($id)."");
    return true;
  }

  /**
   * Возвращает общее количествo невыполненных заказов.
   * <code>
   * echo Models_Order::getNewOrdersCount();
   * </code>
   * @return int - количество заказов
   */
  public function getNewOrdersCount() {
    if (MG::getSetting('orderOwners') == 'true' && $_SESSION['user']->role != 1) {
      $owners = ' AND owner = '.DB::quoteInt($_SESSION['user']->id);
    }
    $sql = "
  		SELECT `id`
      FROM `".PREFIX."order`
      WHERE `status_id`!=5 AND `status_id`!=4".$owners;

    $res = DB::query($sql);
    $count = DB::numRows($res);
    return $count ? $count : 0;
  }

  /**
   * Возвращает статистику заказов за каждый день начиная с открытия магазина.
   * <code>
   * $result = Models_Order::getOrderStat();
   * viewData($result);
   * </code>
   * @return array - [время, значение]
   */
  public function getOrderStat() {
    $result = array();
    $res = DB::query('    
      SELECT (UNIX_TIMESTAMP( CAST( o.add_date AS DATE ) ) * 1000) as "date" , COUNT( add_date ) as "count"
      FROM `'.PREFIX.'order` AS o
      GROUP BY CAST( o.add_date AS DATE )
    ');

    while ($order = DB::fetchAssoc($res)) {
      $result[] = array($order['date'] * 1, $order['count'] * 1);
    }
    return $result;
  }

  /**
   * Возвращает статистику заказов за выбранный период. 
   * <code>
   * $result = Models_Order::getStatisticPeriod('01.01.2017','01.01.2018');
   * viewData($result);
   * </code>
   * @param string $dateFrom дата "Oт".
   * @param string $dateTo дата "До".
   * @return array
   */
  public function getStatisticPeriod($dateFrom, $dateTo) {
    $dateFromRes = $dateFrom;
    $dateToRes = $dateTo;
    $dateFrom = date('Y-m-d', strtotime($dateFrom));
    $dateTo = date('Y-m-d', strtotime($dateTo));
    $period = "AND `add_date` >= ".DB::quote($dateFrom)."
       AND `add_date` <= ".DB::quote($dateTo);

    // Количество закрытых заказов всего.
    $ordersCount = $this->getOrderCount('WHERE status_id = 5 '.$period);

    $noclosed = $this->getOrderCount('WHERE status_id <> 5 '.$period);

    // Сумма заработанная за все время работы магазина.
    $res = DB::query("
      SELECT sum(summ) as 'summ'  FROM `".PREFIX."order`
      WHERE status_id = 5 ".$period
    );

    if ($row = DB::fetchAssoc($res)) {
      $summ = $row['summ'];
    }

    $product = new Models_Product;
    $productsCount = $product->getProductsCount();
    $res = DB::query("SELECT id  FROM `".PREFIX."user`");
    $usersCount = DB::numRows($res);

    $result = array(
      'from_date_stat' => $dateFromRes,
      'to_date_stat' => $dateToRes,
      "orders" => $ordersCount ? $ordersCount : "0",
      "noclosed" => $noclosed ? $noclosed : "0",
      "summ" => $summ ? $summ : "0",
      "users" => $usersCount ? $usersCount : "0",
      "products" => $productsCount ? $productsCount : "0",
    );

    return $result;
  }

  /**
   * Выводит на экран печатную форму для печати заказа в админке.
   * <code>
   * $model = new Models_Order;
   * echo $model->printOrder(5);
   * </code>
   * @param int $id id заказа.
   * @param bool $sign использовать ли подпись.
   * @param string $type тип документа
   * @return string 
   */
  public function printOrder($id, $sign = true, $type="order") {    
    $orderInfo = $this->getOrder('id='.DB::quote(intval($id), true));
    if ($type == 'qittance') {
      $summ = $orderInfo[$id]['summ']+$orderInfo[$id]['delivery_cost'];
      $paramArray = $this->getParamArray('7', $id, $summ);
      $data['paramArray'] = $paramArray;
      foreach($data['paramArray'] as $k=>$field) {
        $data['paramArray'][$k]['value'] = htmlentities($data['paramArray'][$k]['value'], ENT_QUOTES, "UTF-8");
      }
      $line = "<p class='line'></p>";
      $data['line'] = "<p class='line'></p>";
      $data['line2'] = "<p class='line2'></p>";
      $data['name'] = (!empty($data['paramArray'][0]['value'])) ? $data['paramArray'][0]['value'] : $line;
      $data['inn'] = (!empty($data['paramArray'][1]['value'])) ? $data['paramArray'][1]['value'] : $line;
      $data['nsp'] = (!empty($data['paramArray'][6]['value'])) ? $data['paramArray'][6]['value'] : $line;
      $data['ncsp'] = (!empty($data['paramArray'][7]['value'])) ? $data['paramArray'][7]['value'] : $data['line2'];
      $data['bank'] = (!empty($data['paramArray'][4]['value'])) ? $data['paramArray'][4]['value'] : $line;
      $data['bik'] = (!empty($data['paramArray'][5]['value'])) ? $data['paramArray'][5]['value'] : $data['line2'];
      $data['appointment'] = "Оплата по счету №".($orderInfo[$id]['number']!=''?$orderInfo[$id]['number']:$id);
      $data['nls'] = $line;
      $data['payer'] = $orderInfo[$id]['name_buyer'];
      $data['addrPayer'] = $orderInfo[$id]['address'];
      $data['sRub'] = $orderInfo[$id]['summ']+$orderInfo[$id]['delivery_cost'] ? $orderInfo[$id]['summ']+$orderInfo[$id]['delivery_cost'] : '_______';
      $data['sKop'] = 0;
      $data['uRub'] = '_______';
      $data['uKop'] = 0;
      $data['day'] = date('d');
      $data['month'] = date('m');
      $data['rub'] = '_______';
      $data['kop'] = '_______';
      $html = MG::layoutManager('print_'.$type, $data);
      return $html;
    }
    $order = $orderInfo[$id];
    $lang = MG::get('lang');

    $perOrders = unserialize(stripslashes($order['order_content']));
    $prodPicIds = $varPicIds = $prodPics = $varPics = array();
    foreach ($perOrders as $prod) {
      if ($prod['variant']) {
        $varPicIds[] = $prod['variant'];
      } 
      $prodPicIds[] = $prod['id'];
    }

    if (!empty($prodPicIds)) {
      $res = DB::query("SELECT `id`, `image_url` FROM `".PREFIX."product` WHERE `id` IN (".DB::quoteIn($prodPicIds).")");
      while ($row = DB::fetchAssoc($res)) {
        $folder = floor($row['id']/100).'00';
        $images = explode('|', $row['image_url']);
        if ($images[0] && is_file(SITE_DIR.'uploads'.DS.'product'.DS.$folder.DS.$row['id'].DS.$images[0])) {
          $prodPics[$row['id']] = SITE.'/uploads/product/'.$folder.'/'.$row['id'].'/'.$images[0];
        }
      }
    }
    if (!empty($varPicIds)) {
      $res = DB::query("SELECT `id`, `product_id`, `image` FROM `".PREFIX."product_variant` WHERE `id` IN (".DB::quoteIn($varPicIds).")");
      while ($row = DB::fetchAssoc($res)) {
        $folder = floor($row['product_id']/100).'00';
        $images = explode('|', $row['image_url']);
        if ($images[0] && is_file(SITE_DIR.'uploads'.DS.'product'.DS.$folder.DS.$row['product_id'].DS.$images[0])) {
          $varPics[$row['id']] = SITE.'/uploads/product/'.$folder.'/'.$row['product_id'].'/'.$images[0];
        }
      }
    }
    foreach ($perOrders as $key => $prod) {
      if ($prod['variant']) {
        if ($varPics[$prod['variant']]) {
          $perOrders[$key]['image'] = $varPics[$prod['variant']];
          continue;
        }
      }
      if ($prodPics[$prod['id']]) {
        $perOrders[$key]['image'] = $prodPics[$prod['id']];
        continue;
      }
      if (is_file(SITE_DIR.'uploads'.DS.'no-img.jpg')) {
        $perOrders[$key]['image'] = SITE.'/uploads/no-img.jpg';
      }
    }

    $currency = MG::getSetting('currency');
    $totSumm = $order['summ'] + $order['cost'];
    $paymentArray = $this->getPaymentMethod($order['payment_id']);
    $order['name'] = $paymentArray['name'];

    $propertyOrder = MG::getOption('propertyOrder');
    $propertyOrder = stripslashes($propertyOrder);
    $propertyOrder = unserialize($propertyOrder);

    $paramArray = $this->getParamArray(7, $order['id'], $order['summ']);
    foreach ($paramArray as $k => $field) {
      $paramArray[$k]['value'] = htmlentities($paramArray[$k]['value'], ENT_QUOTES, "UTF-8");
    }

    $customer = unserialize(stripslashes($order['yur_info']));

    if ($type == 'packing-list') {
      $customerInfo = $customer['nameyur'].', '
          .'ИНН '. $customer['inn'].', '
          .$customer['adress'].', '
          .'р/с '.$customer['rs'].', '
          .'в банке '.$customer['bank'].', '
          .'БИК '.$customer['bik'].', '
          .'к/с '.$customer['ks'];
    } else {
      $customerInfo = $lang['OREDER_LOCALE_16'].': &nbsp;'. $customer['inn'].'<br/>'.$lang['OREDER_LOCALE_17'].': &nbsp;'.$customer['kpp'].'<br/>'.$lang['OREDER_LOCALE_9'].': &nbsp;'.
        $customer['nameyur'].'<br/>'.$lang['OREDER_LOCALE_15'].': &nbsp;'.$customer['adress'].'<br/>'.$lang['OREDER_LOCALE_18'].': &nbsp;'.
        $customer['bank'].'<br/>'.$lang['OREDER_LOCALE_19'].': &nbsp;'.$customer['bik'].'<br/>'.$lang['OREDER_LOCALE_20'].': &nbsp;'.$customer['ks'].'<br/>'.$lang['OREDER_LOCALE_21'].': &nbsp;'.
        $customer['rs'];
    }

    $ylico = false;
    if (empty($customer['inn']) || empty($customer['bik'])) {
      $fizlico = true;
      $userInfo = USER::getUserInfoByEmail($order['user_email']);
      
      if ($type == 'packing-list') {
        $customerInfo = $order['name_buyer'].', '.$order['address'].', '.$order['phone'];
      } elseif($type == 'order') {
        $customerInfo = $lang['ORDER_BUYER'].': &nbsp;'.$order['name_buyer'].'<br/>'.$lang['ORDER_PHONE'].': &nbsp;'.
          $order['phone'].' <br/>'.$lang['ORDER_EMAIL'].': &nbsp;'.$order['user_email'].'<br/>'.$lang['ORDER_ADDRESS'].': &nbsp;'.
          $order['address'];
      } else {
        $customerInfo = $lang['ORDER_BUYER'].': &nbsp;'.$order['name_buyer'].'<br/>'.$lang['ORDER_ADDRESS'].': &nbsp;'.
          $order['address'].'<br/> '.$lang['ORDER_PHONE'].': &nbsp;'.
          $order['phone'].' <br/>'.$lang['ORDER_EMAIL'].': &nbsp;'.$order['user_email'];
      }
    }
    
    if ($type == "invoice" && !empty($customer['nameyur']) && !empty($customer['adress'])) {
      $order['name_buyer'] = $customer['nameyur'];
      $order['address'] = $customer['adress'];
    }

    $order['optionalFields'] = Models_Order::getOPfields($id);

    $customerInfo = htmlspecialchars($customerInfo);
    $propertyOrder['sing'] = $propertyOrder['sing'] ? $propertyOrder['sing'] : 'uploads/sing.jpg';
    $propertyOrder['stamp'] = $propertyOrder['stamp'] ? $propertyOrder['stamp'] : 'uploads/stamp.jpg';
    $data['propertyOrder'] = $propertyOrder;
    $data['order'] = $order;
    $data['customerInfo'] = $customerInfo;
    $data['perOrders'] = $perOrders;
    $data['currency'] = $currency;
    $data['customerInfo'] = htmlspecialchars_decode($data['customerInfo']);

    if(empty($type)) {
      $type="order";
    }
    
    $html = MG::layoutManager('print_'.$type, $data);

    return $html;
  }

  public function getOPfields($orderId) {
    $optionalFields = unserialize(stripcslashes(MG::getSetting('optionalFields')));

    if (empty($optionalFields)) {return array();}

    $res = DB::query("SELECT `field`, `value` FROM `".PREFIX."custom_order_fields` WHERE `id_order` = ".DB::quoteInt($orderId));
    $orderOP = array();
    while ($row = DB::fetchAssoc($res)) {
      $orderOP[$row['field']] = $row['value'];
    }

    $result = array();
    foreach ($optionalFields as $field) {
      $name = MG::translitIt($field['name']);
      if ($field['type'] == 'checkbox' && (!array_key_exists(MG::translitIt($field['name']), $orderOP)) || !$orderOP[MG::translitIt($field['name'])]) {
        $result[] = array('name' => $field['name'], 'value' => 'нет');
        continue;
      }
      if ($field['type'] == 'checkbox' && array_key_exists($name, $orderOP)) {
        $result[] = array('name' => $field['name'], 'value' => 'да');
        continue;
      }
      if ($field['type'] == 'radiobutton' && (!array_key_exists($name, $orderOP) || !$orderOP[$name])) {
        $result[] = array('name' => $field['name'], 'value' => 'не отмечено');
        continue;
      }
      $result[] = array('name' => $field['name'], 'value' => $orderOP[$name]);
    }
    return $result;
  }

  /**
   * Отдает pdf файл на скачивание.
   * <code>
   * $model = new Models_Order;
   * $model->getPdfOrder(5);
   * </code>
   * @param int $orderId номер заказа id.
   * @param string $type тип запрашиваемого результата.
   * @return array 
   */
  public function getPdfOrder($orderId, $type="order") {  
    if(empty($type)) {
      $type="order";
    }
    // Подключаем библиотеку tcpdf.php    
    require_once('mg-core/script/tcpdf/tcpdf.php');
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->setImageScale(1.53);
    $pdf->SetFont('arial', '', 10);
    $pdf->AddPage();

    $orderInfo = $this->getOrder('id='.DB::quote(intval($orderId), true));

    $access = false;
    if (USER::getThis()->email && (USER::getThis()->email == $orderInfo[$orderId]['user_email'] || USER::getThis()->role != 2)) {
      $access = true;
    }
    if (MG::getSetting('autoRegister') == "false") {
       $access = true;
    }
    if (!$access) {
      MG::redirect('/404');
      return false;
    }

    $html = $this->printOrder($orderId, true, $type);

    $pdf->writeHTML($html, true, false, true, false, '');

    $pdf->Output('Order '.$orderInfo[$orderId]['number'].'.pdf', 'D');
    exit;
  }

  public function getMassPdfOrders($orderIds, $layout) {

    $orderIds = json_decode($orderIds, 1);
    $html = array();
    foreach ($orderIds as $orderId) {
      $html[] = $this->printOrder($orderId, true, $layout);
    }
    $html = implode('<br pagebreak="true"/>', $html);

    require_once('mg-core/script/tcpdf/tcpdf.php');
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->setImageScale(1.53);
    $pdf->SetFont('arial', '', 10);
    $pdf->AddPage();
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('Orders_'.$layout.'_'.date("d.m.Y").'.pdf', 'D');
    die;
  }

  /**
   * Выводит на экран печатную форму для печати квитанции на оплату заказа.
   * <code>
   * $model = new Models_Order;
   * $model->printQittance();
   * </code>
   * @param bool вывод на печать в публичной части, либо в админке.
   * @return void|string 
   */
  public function printQittance($public = true) {
    MG::disableTemplate();

    $data['line'] = "<p class='line'></p>";
    $data['line2'] = "<p class='line2'></p>";
    $data['name'] = (!empty($_POST['name'])) ? $_POST['name'] : $line;
    $data['inn'] = (!empty($_POST['inn'])) ? $_POST['inn'] : $line;
    $data['nsp'] = (!empty($_POST['nsp'])) ? $_POST['nsp'] : $line;
    $data['ncsp'] = (!empty($_POST['ncsp'])) ? $_POST['ncsp'] : $data['line2'];
    $data['bank'] = (!empty($_POST['bank'])) ? $_POST['bank'] : $line;
    $data['bik'] = (!empty($_POST['bik'])) ? $_POST['bik'] : $data['line2'];
    $data['appointment'] = (!empty($_POST['appointment'])) ? $_POST['appointment'] : $line;
    $data['nls'] = (!empty($_POST['nls'])) ? $_POST['nls'] : $line;
    $data['payer'] = (!empty($_POST['payer'])) ? $_POST['payer'] : $data['line2'];
    $data['addrPayer'] = (!empty($_POST['addrPayer'])) ? $_POST['addrPayer'] : $data['line2'];
    $data['sRub'] = (!empty($_POST['sRub'])) ? $_POST['sRub'] : '_______';
    $data['sKop'] = (!empty($_POST['sKop'])) ? $_POST['sKop'] : 0;
    $data['uRub'] = (!empty($_POST['uRub'])) ? $_POST['uRub'] : '_______';
    $data['uKop'] = (!empty($_POST['uKop'])) ? $_POST['uKop'] : 0;
    $data['day'] = (!isset($_POST['day']) || $_POST['day'] == '_') ? '____' : $_POST['day'];
    $data['month'] = (!isset($_POST['month']) || $_POST['month'] == '_') ?
      '___________________' : $_POST['month'];

    if (!isset($_POST['sKop'])) {
      $sKop = '___';
    }
    if (!isset($_POST['uKop'])) {
      $uKop = '___';
    }
    $sResult = (!empty($sKop)) ? $sResult = "$sRub.$sKop" : $sRub;
    $uResult = (!empty($uKop)) ? $uResult = "$uRub.$uKop" : $uRub;

    $rubResult = $sResult + $uResult;

    if (empty($rubResult)) {
      settype($rubResult, 'null');
    }

    if (is_double($rubResult)) {
      list($rub, $kop) = explode('.', $rubResult);
    } else if (is_int($rubResult)) {
      $rub = $rubResult;
      $kop = "0";
    }

    if (empty($rub))
      $rub = '_______';
    if (!isset($kop))
      $kop = '___';

    $data['rub'] = $rub;
    $data['kop'] = $kop;
    $data['uKop'] = $uKop;
    $data['sKop'] = $sKop;
    $html = MG::layoutManager('print_qittance', $data);
    if ($public) {
      echo $html;
      exit();
    }
    return $html;
  }

  /**
   * Экспортирует параметры конкретного заказа в CSV файл.
   * <code>
   * $order = new Models_Order;
   * $order->getExportCSV(5);
   * </code>
   * @param $orderId - id заказа.
   * @return void
   */
  public function getExportCSV($orderId) {

    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream;");
    header("Content-Type: application/download");
    header("Content-Disposition: attachment;filename=data.csv");
    header("Content-Transfer-Encoding: binary ");

    $orderInfo = $this->getOrder('id='.DB::quote(intval($orderId), true));

    $order = $orderInfo[$orderId];

    $order_content =stripslashes($order["order_content"]);
    $order_content = unserialize($order_content);
  
    foreach ($order_content as $item) {
      $csvText .= "\"".$item["id"]."\";\"".
        $order["add_date"]."\";\"".
        $order["name_buyer"]."\";\"".
        $order["user_email"]."\";\"".
        $order["phone"]."\";\"".
        $order["address"]."\";\"".
        $order["comment"]."\";\"".
        $item["name"]."\";\"".
        $item["code"]."\";\"".
        number_format($item["price"]*$item["count"], 2, ',', '')."\";\"".
        $item["count"]."\";\"".
        $item["coupon"]."\"\n";
    }

    echo mb_convert_encoding($csvText, "WINDOWS-1251", "UTF-8");
    exit;
  }
  
  /**
   * Возвращаем номер или префикс заказа.
   * <code>
   * echo Models_Order::getOrderNumber(5);
   * </code>
   * @param int $id id заказа.
   * @return string номер заказа
   */
  private function getOrderNumber($id) {
    $orderNum = MG::getSetting('orderNumber');
    $prefix = '';
    if(MG::getSetting('prefixOrder')) $prefix = MG::getSetting('prefixOrder');
    if($orderNum =='false') {
      $result = $prefix.$id;
    } else {
      $str = mt_rand(10000, 9999999999);
      $str = $str?$str:rand(10000, 999999);
	  
      $result = str_pad((string)$str, 10, '0', STR_PAD_LEFT);
      $result = $prefix.$result;
    }
    // Возвращаем номер или префикс заказа.
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }
  
   /**
   * Пересчитывает количество остатков продуктов при редактировании заказа.
   * <code>
   * $orderId = 5;
   * $content = 'a:1:{i:0;a:16:{s:2:\"id\";s:2:\"40\";s:7:\"variant\";s:4:\"1099\";s:5:\"title\";s:72:\"Чехол на руку для смартфона Demix+ Зелёный\";s:4:\"name\";s:72:\"Чехол на руку для смартфона Demix+ Зелёный\";s:8:\"property\";s:0:\"\";s:5:\"price\";s:3:\"499\";s:8:\"fulPrice\";s:3:\"499\";s:4:\"code\";s:6:\"SKU343\";s:6:\"weight\";s:1:\"0\";s:12:\"currency_iso\";s:3:\"RUR\";s:5:\"count\";s:1:\"2\";s:6:\"coupon\";s:1:\"0\";s:4:\"info\";s:0:\"\";s:3:\"url\";s:69:\"aksessuary/chehly-dlya-smartfonov/chehol-na-ruku-dlya-smartfona-demix\";s:8:\"discount\";s:1:\"0\";s:8:\"discSyst\";s:11:\"false/false\";}}';
   * $order = new Models_Order;
   * $result = $order->refreshCountAfterEdit($orderId, $content);
   * var_dump($result);
   * </code>
   * @param int $orderId id заказа.
   * @param string $content новое содержимое содержимое заказа (сериализованный массив)
   * @return bool
   */
  public function refreshCountAfterEdit($orderId, $content) {
    // Если количество товара меняется, то пересчитываем остатки продуктов из заказа.
    $order = $this->getOrder(' id = '.DB::quote(intval($orderId), true));

    $order_content_old = unserialize(stripslashes($order[$orderId]['order_content']));
    $order_content_new = unserialize(stripslashes($content));
    $product = new Models_Product();
    $codes = array();
    foreach ($order_content_old as $item_old) {
      if (!empty($codes[$item_old['id'].'|'.$item_old['code']])) {
        $codes[$item_old['id'].'|'.$item_old['code']]['count'] += $item_old['count'];
      } else {
        $codes[$item_old['id'].'|'.$item_old['code']] = array(
          'id' => $item_old['id'],
          'code' => $item_old['code'],
          'count' => $item_old['count'],
          'variant_id' => $item_new['variant']);
      }      
    }
       
    foreach ($order_content_new as $item_new) {
      $flag = 0;
      foreach ($codes as $key => $info) {
        if (in_array($item_new['code'], $info)&&$item_new['id']==$info['id']) {
          $codes[$key] = array(
            'id' => $item_new['id'],
            'code' => $item_new['code'],
            'count' => $info['count'] - $item_new['count'],
            'variant_id' => $item_new['variant']);
          $flag = 1;
        }
      }
      if ($flag === 0) {
        $codes[] = array(
          'id' => $item_new['id'],
          'code' => $item_new['code'],
          'count' => $item_new['count'] * (-1),
          'variant_id' => $item_new['variant']);
      }
    }

    foreach ($codes as $prod) {
      if ($prod['count'] > 0) {
          $product->increaseCountProduct($prod['id'], $prod['code'], $prod['count']);
      } elseif ($prod['count'] < 0) {
          $product->decreaseCountProduct($prod['id'], $prod['code'], abs($prod['count']));
      }
    }

    $result = $flag;
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Проверяет есть в заказе комплект или нет при копировании заказа
   * <code>
   * $result = Models_Order::notSetGoods(3);
   * var_dump($result);
   * </code>
   * @param int $id id товара
   * @return bool
   */
  public function notSetGoods($id) {
    $result = true;
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Выгружает список заказов в CSV файл.
   * <code>
   * $order = new Models_Order;
   * echo $order->exportToCsvOrder();
   * </code>
   * @param array $listOrderId выгрузка выбранных заказов
   * @param bool $full полная выгрузка
   * @return array
   */
  public function exportToCsvOrder($listOrderId=array(), $full = false, $exportToCSV = false) {
  
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream;");
    header("Content-Type: application/download");
    header("Content-Disposition: attachment;filename=data.csv");
    header("Content-Transfer-Encoding: binary ");

    $csvText = '';
    $csvText .= '"id";"Номер";"Дата создания";"Email";"Имя пользователя";"Телефон";"Адрес";"Сумма";"Купон";"Скидка";"Статус заказа";"Способ доставки";"Стоимость доставки";"Оплата";'."\n";
    if ($full) {
      $csvText = '"id заказа";"Номер";"Дата создания";"Сумма заказа";"id товара";"Артикул";"Наименование";"Количество";"Полная цена";"Стоимость";"Вес";"Купон";"Скидка";"Email";"Имя пользователя";"Телефон";"Адрес";"Статус заказа";"Способ доставки";"Стоимость доставки";"Оплата";"Комментарий менеджера";"Комментарий клиента"'."\n";
    }
    Storage::$noCache = true;
    $page = 1;
    // получаем максимальное количество заказов, если выгрузка всего ассортимента
    if(empty($listOrderId)) {
      $res = DB::query('
        SELECT count(id) as count
        FROM `'.PREFIX.'order`
        ');
      if ($order = DB::fetchAssoc($res)) {
        $count = $order['count'];
      }
      $maxCountPage = ceil($count / 500);
    } else {
      $maxCountPage = ceil(count($listOrderId) / 500);
      $listId = implode(',', $listOrderId);
    }    
    for ($page = 1; $page <= $maxCountPage; $page++) {      
      URL::setQueryParametr("page", $page);
      $sql = 'SELECT * FROM `'.PREFIX.'order` ';
      if(!empty($listOrderId)) { 
        $sql .= ' WHERE `id` IN ('.DB::quote($listId,1).')';     
      }
      $sql .= ' ORDER BY `add_date`'; 
      $navigator = new Navigator($sql, $page, 500); //определяем класс  
      $orders = $navigator->getRowsSql();
      if (class_exists('statusOrder')) {
        $dbQuery = DB::query('SELECT `id_status`, `status` FROM `'.PREFIX.'mg-status-order` ');
        while ($dbRes = DB::fetchArray($dbQuery)) {
          self::$statusUser[$dbRes['id_status']] = $dbRes['status'];
        }
      }
      foreach ($orders as $row) {
        $csvText .= self::addOrderToCsvLine($row, $full);
        }
      }
    
    $csvText = substr($csvText, 0, -2); // удаляем последний символ '\n'
        
    $csvText = mb_convert_encoding($csvText, "WINDOWS-1251", "UTF-8");
    if(empty($listOrderId) || $exportToCSV) {
      echo $csvText;
      exit;
    } else{
      $date = date('m_d_Y_h_i_s');
      file_put_contents('data_csv_'.$date.'.csv', $csvText);
      $msg = 'data_csv_'.$date.'.csv';
    }
    return $msg;
  }

  /**
   * Добавляет пользователя в CSV выгрузку.
   * @param array $row - запись о пользователе.
   * @param bool $full - полная выгрузка
   * @return string
   */
  private function addOrderToCsvLine($row, $full=false) {
    // mg::loger($row);
    $csvText = '';
    $row['user_email'] = '"' . str_replace("\"", "\"\"", $row['user_email']) . '"';
    $row['id'] = '"' . str_replace("\"", "\"\"", $row['id']) . '"';
    $row['number'] = '"' . str_replace("\"", "\"\"", $row['number']) . '"';
    $row['address'] = '"' . str_replace("\"", "\"\"", $row['address']) . '"';
    $row['phone'] = '"' . str_replace("\"", "\"\"", $row['phone']) . '"';
    $delivery = $this->getDeliveryMethod(false, $row['delivery_id']);
    $row['delivery_id'] = '"' . str_replace("\"", "\"\"", $delivery['description']) . '"';
    $row['delivery_cost'] = '"' . str_replace("\"", "\"\"", $row['delivery_cost']) . '"';
    $row['payment_id'] = '"' . str_replace("\"", "\"\"", $this->_paymentArray[$row['payment_id']]['name']) . '"';
    $statusOrder = '';
    if (!empty(self::$statusUser)) {
      $statusOrder = self::$statusUser[$row['status_id']];
    }
    if (!$statusOrder) {
      $statusArray = self::$status;
      $lang = MG::get('lang');
      $statusOrder = $lang[$statusArray[$row['status_id']]];
    }   
    $row['status_id'] = '"' . str_replace("\"", "\"\"", $statusOrder) . '"';
    $row['name_buyer'] = '"' . str_replace("\"", "\"\"", $row['name_buyer']) . '"';
    $row['add_date'] = '"' . str_replace("\"", "\"\"", date('d.m.Y', strtotime($row['add_date']))).'"';
    $row['summ'] = '"' . str_replace("\"", "\"\"",  str_replace('.', ',',$row['summ'])) . '"';
    $row['comment'] = '"' . str_replace("\"", "\"\"", $row['comment']) . '"';
    $row['user_comment'] = '"' . str_replace("\"", "\"\"", $row['user_comment']) . '"';
    $content = unserialize(stripslashes($row['order_content'])); 
    foreach ($content as $order) {
      $coupon= '"' . str_replace("\"", "\"\"", (!empty($order['coupon'])&&$order['coupon']!="Не указан"? urldecode($order['coupon']):'')) . '"';
      $discount= '"' . str_replace("\"", "\"\"", (!empty($order['discount'])? $order['discount']:'')) . '"';
      if ($full) {
        $code = '"' . str_replace("\"", "\"\"", $order['code']) . '"';
        $id = '"' . str_replace("\"", "\"\"", $order['id']) . '"';
        $count = '"' . str_replace("\"", "\"\"", $order['count']).'"';
        $name = '"' . str_replace("\"", "\"\"", urldecode($order['name'])).'"';
        $price = '"' . str_replace("\"", "\"\"", str_replace('.', ',',$order['price'])) . '"';
        $priceFull = '"' . str_replace("\"", "\"\"", str_replace('.', ',',$order['fulPrice'])) . '"';
        $weight = '"' . str_replace('.', ',', str_replace("\"", "\"\"", floatval($order['weight'])*floatval($order['count']))) . '"';        

        $csvText .= $row['id'].";".$row['number'].";". $row['add_date'].";".
          $row['summ'].";".$id.";".$code.";".$name.";".$count.";".
          $priceFull.";".$price.";".$weight.";".$coupon.";".$discount.";".
          $row['user_email'].";".$row['name_buyer'].";".$row['phone'].";".
          $row['address'].";".$row['status_id'].";".
          $row['delivery_id'] . ";" .
          $row['delivery_cost'] . ";" .
          $row['payment_id'] . ";". 
          $row['comment'] . ";". 
          $row['user_comment'] . ";". "\n";
      }      
    }      
    if (!$full) {
      $csvText = $row['id'] . ";" .
        $row['number'] . ";" .
        $row['add_date'] . ";" .
        $row['user_email'] . ";" .     
        $row['name_buyer'] . ";" .
        $row['phone'] . ";" .
        $row['address'] . ";" .
        $row['summ'] . ";" .
        $coupon . ";" .
        $discount . ";".
        $row['status_id'] . ";" .
        $row['delivery_id'] . ";" .
        $row['delivery_cost'] . ";" .
        $row['payment_id'] . ";". "\n";
    }
    return $csvText;
  }
  
  /**
   * Поиск скидки, применяемой к заказу по промокоду или в рамках 
   * накопительной/объемной скидки.
   * <code>
   * $params = array(
   *    'summ' => 1000, // сумма заказа 
   *    'email' => 'admin@admin.ru', // почта покупателя
   *    'promocode' => 'DEFAULT-DISCONT', // код купона скидки
   *    'cumulative' => 'true', // накопительная скидка
   *    'volume' => 'true', // объемная скидка
   *    'paymentId' => 5, // ID способа 
   *    'orderItems' => 'array' // массив с товарами заказа
   * );
   * $order = new Models_Order;
   * $order->getOrderDiscount($params);
   * </code>
   * @param array $params параметры заказа
   * @return array
   */
  public function getOrderDiscount($params) {
    $percent = 0;

    if ($params['promocode']) {
      $result = DB::query('SHOW TABLES LIKE "'.PREFIX.'promo-code"');
      
      if (DB::numRows($result)) {
        $sql = 'SELECT * FROM `'.PREFIX.'promo-code` 
          WHERE `code` ='.DB::quote($params['promocode']).'
           AND `invisible` = 1 
           AND now() >= `from_datetime`
           AND now() <= `to_datetime`';
        $res = DB::query($sql);

          if ($code = DB::fetchAssoc($res)) {
            $percent = $code['percent'] ? $code['percent'] : 0;
            
        };
      }        
    }
    $percent = (float) $percent;

    $individualPercent = false;

    if ($params['promocode']) {
      $result = DB::query('SHOW TABLES LIKE "'.PREFIX.'oik-discount-coupon"');
      
      if (DB::numRows($result)) {
        $excludedProductTmp = explode(',', unserialize(stripslashes(MG::getSetting('discount-coupon-exclude'))));
        $sql = 'SELECT * FROM `'.PREFIX.'oik-discount-coupon` 
          WHERE `code` ='.DB::quote($params['promocode']).'
          AND `activity` = 1 
          AND now() >= `date_active_from`
          AND now() <= `date_active_to`';
        $res = DB::query($sql);

        if ($code = DB::fetchAssoc($res)) {
          $excludedProduct = array_merge($excludedProductTmp, explode(',', unserialize(stripslashes($row['products']))));
          $individualPercent = true;
          $percentInd = $code['value'] ? $code['value'] : 0;

          $productId = '';
          foreach ($params['orderItems'] as $orderItem) {
            $productId .= $orderItem['id'].',';
          }
          $productId = chop($productId, ',');

          $disountProduct = 'SELECT GROUP_CONCAT(id) AS id FROM `'.PREFIX.'product` WHERE `cat_id` in ('.DB::quoteIN($code['category'],true).') AND id IN ('.DB::quoteIN($productId,true).')';

          $res = DB::query($disountProduct);
          while ($row = DB::fetchArray($res)) {
            $idForDis = ','.$row['id'].',';
          }
        };
      }        
    }
    
    if ($params['cumulative'] == 'true' || $params['volume'] == 'true') {
      $result = DB::query('SHOW TABLES LIKE "'.PREFIX.'discount-system%"');
      if (DB::numRows($result)) {
        
        if ($params['cumulative'] == 'true' && $params['email']) {
          $sql = "SELECT SUM(`summ`) as summ FROM  `".PREFIX."order`       
            WHERE  `user_email` =  ".DB::quote($params['email'])." 
            AND ( `status_id` =  '2'
            OR  `status_id` =  '5')";
          $res = DB::query($sql);
          
          if ($count = DB::fetchAssoc($res)) {
            $sql = "SELECT * FROM `".PREFIX."discount-system-cumulative` 
              WHERE `summ` <= ".DB::quote($count['summ'])." ORDER BY `summ` DESC LIMIT 1";
            $res = DB::query($sql);
            
            if ($discount = DB::fetchAssoc($res)) {
              $percent += (float) $discount['percent'];
            }
          }
        } 
        
        if ($params['volume'] == 'true' && ($params['summ'] > 0)) {
          $sql = 'SELECT * FROM `'.PREFIX.'discount-system-volume` 
            WHERE `summ` <= '.DB::quote($params['summ']).' ORDER BY `summ` DESC LIMIT 1';
          $rez = DB::query($sql);
          
          if ($discount = DB::fetchArray($rez)) {
            $percent += (float) $discount['percent'];
          }
        }
      }
    }
    
    if(!empty($params['paymentId'])) {
      $payment = $this->getPaymentMethod($params['paymentId']);
      
      if(!empty($payment['rate'])) {  
        $summ = floatval($params['summ']);
        $summDiscount = $summ - ($summ * $percent / 100);
        $percent1 = (float) -1*($payment['rate']*100);
        $summDiscountPay = ($summDiscount * $percent1 / 100);
        $per = $summDiscountPay / $summ * 100;
        $percent += $per;
      }
    }
    
    $productDiscount = array();
    
    foreach ($params['orderItems'] as $orderItem) {
      if(!$individualPercent) {
        $productDiscount[] = array(
          'id' => $orderItem['id'],
          'discount' => $percent,
        );
      } else {
        if(substr_count($idForDis,','.$orderItem['id'].',') == 1) {
          $productDiscount[] = array(
            'id' => $orderItem['id'],
            'discount' => $percentInd + $percent,
          );
        } else {
          $productDiscount[] = array(
            'id' => $orderItem['id'],
            'discount' => $percent,
          );
        }
      }
    }
    
    if(!empty($excludedProduct)) {
      $res = DB::query('SELECT id FROM '.PREFIX.'product WHERE code IN ('.DB::quoteIN($excludedProduct).')');
      while($row = DB::fetchAssoc($res)) {

        foreach ($productDiscount as $key => $product) {
          if($row['id'] == $product['id']) $productDiscount[$key]['discount'] = 0;        
        }
      }
    }
    
    $result = array(
      'percent' => $percent,
      'productDiscount' => $productDiscount,
    );
    
    $args = func_get_args();
    
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

}
