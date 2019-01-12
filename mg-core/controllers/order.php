<?php

/**
 * Контроллер: Order
 *
 * Класс Controllers_Order обрабатывает действия пользователей на 
 * странице оформления заказа.
 * - Производит проверку введенных данных в форму оформления заказа;
 * - Добавляет заказ в базу данных сайта;
 * - Для нового покупателя производится регистрация пользователя;
 * - Отправляет письмо с подтверждением заказа на указанный адрес покупателя 
 * и администратору сайта с составом заказа;
 * - Очищает корзину товаров, при успешном оформлении заказа;
 * - Перенаправляет на страницу с сообщеним об успешном оформлении заказа;
 * - Генерирует данные для страниц успешной и неудавшейся электронной оплаты 
 * товаров.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Controller
 */
class Controllers_Order extends BaseController {

  function __construct() {
    $lang = MG::get('lang');
    // Модель для работы заказом.
    $model = new Models_Order;  

    // для редиректа
    if(LANG != 'LANG' && LANG != 'default') {
      $lang = '/'.LANG;
    } else {
      $lang = '';
    }
    
    // Печать заказа в квитанцию.
    if (isset($_POST['printQittance'])) {
      $model->printQittance();
    }
    
    if ($orderId = URL::get('getOrderPdf')) {
      $model->getPdfOrder((int)$orderId);
    }

    // Запрос электронных товаров
    $fileToOrder = null;
    if (isset($_REQUEST['getFileToOrder'])) {
      $electroInfo = $model->getFileToOrder($_REQUEST['getFileToOrder']);

	  $orderInfo = $model->getOrder(' id = '.DB::quote(intval($_REQUEST['getFileToOrder'])));	
	  $orderNumber = $orderInfo[$_REQUEST['getFileToOrder']]['number'];
	  
      if ($electroInfo === false) {
        // $infoMsg = "Для просмотра страницы необходимо зайти на сайт под пользователем сделавшим заказ №".$orderNumber;
        $infoMsg = MG::restoreMsg('msg__order_denied',array('#NUMBER#' => $orderNumber));
      }

      if (is_array($electroInfo) && empty($electroInfo)) {
        // $infoMsg = "Заказ  не содержит электронных товаров или ожидает оплаты!";
        $infoMsg = MG::restoreMsg('msg__no_electro',array('#NUMBER#' => $orderNumber));
      };

      if (is_array($electroInfo) && !empty($electroInfo)) {
        // $infoMsg = "Скачать электронные товары для заказа №".$orderNumber."";
        $infoMsg = MG::restoreMsg('msg__electro_download',array('#NUMBER#' => $orderNumber));
      };
      $fileToOrder = array('infoMsg' => $infoMsg, 'electroInfo' => $electroInfo);
    }

    // пришел запрос на скачивание электронного товара
    if (isset($_REQUEST['link'])) {
      $model->getFileByMd5($_REQUEST['link']);
    }


    // Первый экран - Оформление заказа.
    $step = 1;
	
	// если до этого произошел редирект на этуже страницу на с параметром ?addOrderOk=1, то восстанавливаем массив $_POST и продолжаем работу скрипта как без редиректа
	 if (URL::get('addOrderOk')){
	   $_POST = $_SESSION['post'];
	 }

    // Если пришли данные с формы оформления заказа.
    if (isset($_POST['toOrder'])) {
      if (empty($_SESSION['cart'])) {
        MG::redirect($lang.'/cart');
      }

      // Если параметры введены корректно, то создается новый заказ.
      if ($error = $model->isValidData($_POST)) {
        $msg = $error;
      } else {
        // Второй экран - оплата заказа
        $step = 2;  
		
	    //сохраняем данные Post запроса и перенаправляем на страницу с Get параметром ?addOrderOk=1 для отслеживания конверсии цели в ЯндексМетрике
		if (URL::get('addOrderOk')) {	
		  $_SESSION['post'] = null;	
		}else{
		  $_SESSION['post'] = $_POST;
      if(LANG != 'LANG') {
        $lang = '/'.LANG;
      } else {
        $lang = '';
      }
		  MG::redirect($lang.'/order?addOrderOk=1');
		}	
		
        mgAddCustomPriceAction(array(__CLASS__, 'applyRate'));
        $orderArray = $model->addOrder();
        $orderId = $orderArray['id'];
        $orderNumber = $orderArray['orderNumber'];
        $summ = $model->summ + $model->delivery_cost;
        $pay = $model->payment;
        $paramArray = $model->getParamArray($pay, $orderId, $summ);
      }
    } else {
      $_SESSION['price_rate'] = 0;
    }        

    // Обработка действия при переходе по ссылке подтверждения заказа.
    if ($id = URL::getQueryParametr('id')) {
      $info = $this->confirmOrder($id);
      $msg = $info['msg'];
      $userEmail = $info['userEmail'];
      // Третий экран - подтверждение заказа по ссылке из письма.
      $step = 3;
    }
     // Обработка действия при переходе по ссылке получения информации о статусе заказа.
    if (URL::getQueryParametr('hash')) {
      $hash = URL::getQueryParametr('hash');
      // Информация о заказе по переданному id.
      $orderInfo = $model->getOrder('`'.PREFIX.'order`.hash = '.DB::quote($hash));  
      $id = (key($orderInfo));        
      if ($orderInfo) {
        if (USER::getUserInfoByEmail($orderInfo[$id]['user_email'])) {
          $orderNumber = !empty($orderInfo[$id]['number']) ? $orderInfo[$id]['number'] : $id;
          // $msg = 'Посмотреть статус заказа Вы можете в <a href="'.SITE.'/personal">личном кабинете</a>.';
          $msg = MG::restoreMsg('msg__view_status',array('#NUMBER#' => $orderNumber, '#LINK#' => SITE.'/personal'));
      } else {
        $lang = MG::get('lang');
        $orderNumber = $orderInfo[$id]['number'];  
        $orderId = $id;     
        if (class_exists('statusOrder')) {
          $dbQuery = DB::query('SELECT `status` FROM `'.PREFIX.'mg-status-order` '
            . 'WHERE `id_status`='.DB::quote($orderInfo[$id]['status_id']));
          if ($dbRes = DB::fetchArray($dbQuery)) {
            $orderInfo[$id]['string_status_id'] = $dbRes['status'];
          }
        }
        if (!$orderInfo[$id]['string_status_id']) {
          $status = $model->getOrderStatus($orderInfo[$id]['status_id']);
          $orderInfo[$id]['string_status_id'] = $status; 
        }        
        $paymentArray = $model->getPaymentMethod($orderInfo[$id]['payment_id']);
        $orderInfo[$id]['paymentName'] = $paymentArray['name'];
        $msg = '';
      }       
      } else {
        // $msg = 'Некорректная ссылка.<br> Заказ не найден<br>';
        $msg = MG::restoreMsg('msg__order_not_found');
    }  
      // пятый экран - инфо о статусе заказа
      $step = 5;
    }

    // Запрос оплаты из ЛК.
    if (URL::getQueryParametr('pay')) {
      // Четвертый экран - Запрос оплаты из ЛК.
      $step = 4;
      $pay = URL::getQueryParametr('paymentId');
      $orderId = URL::getQueryParametr('orderID');
      $order = $model->getOrder(' id = '.DB::quoteInt($orderId));
      $summ = URL::getQueryParametr('orderSumm');
      $summ = $order[$orderId]['summ'] * 1 + $order[$orderId]['delivery_cost'] * 1;
      $paramArray = $model->getParamArray($pay, $orderId, $summ);
    }

    // Если пользователь авторизован, то заполняем форму личными данными.
    if (User::isAuth()) {
      $userInfo = User::getThis();
      $_POST['email'] = $_POST['email'] ? $_POST['email'] : $userInfo->email;
      $_POST['phone'] = $_POST['phone'] ? $_POST['phone'] : $userInfo->phone;
      $_POST['fio'] = $_POST['fio'] ? $_POST['fio'] : $userInfo->name.' '.$userInfo->sname;
      $_POST['address'] = $_POST['address'] ? $_POST['address'] : $userInfo->address;
      if ($userInfo->inn) {
        $_POST['customer'] = 'yur';
      }
      $_POST['yur_info']['nameyur'] = $userInfo->nameyur;
      $_POST['yur_info']['adress'] = $userInfo->adress;
      $_POST['yur_info']['inn'] = $userInfo->inn;
      $_POST['yur_info']['kpp'] = $userInfo->kpp;
      $_POST['yur_info']['bank'] = $userInfo->bank;
      $_POST['yur_info']['bik'] = $userInfo->bik;
      $_POST['yur_info']['ks'] = $userInfo->ks;
      $_POST['yur_info']['rs'] = $userInfo->rs;

      $_POST['address_index'] = $userInfo->address_index;
      $_POST['address_country'] = $userInfo->address_country;
      $_POST['address_region'] = $userInfo->address_region;
      $_POST['address_city'] = $userInfo->address_city;
      $_POST['address_street'] = $userInfo->address_street;
      $_POST['address_house'] = $userInfo->address_house;
      $_POST['address_flat'] = $userInfo->address_flat;
    }

    // Обработка ajax запроса из шаблона.
    if ('getPaymentByDeliveryId' == URL::getQueryParametr('action')) {
      $this->getPaymentByDeliveryId();
    }
    
    // Обработка ajax запроса из шаблона.
    if ('setPaymentRate' == URL::getQueryParametr('action')) {
      $this->setPaymentRate();
    }

    // Обработка ajax запроса из шаблона.
    if ('getEssentialElements' == URL::getQueryParametr('action')) {
      $this->getEssentialElements();
    }
    
    //Обработка ajax запроса из редактирования заказа
    if('getDeliveryOrderOptions' == URL::getQueryParametr('action')) {           
      $this->getDeliveryOrderOptions();
    }

    $this->includeIconsPack();
    // Массив способов доставки.    
    $deliveryArray = $this->getDelivery();
    
    
    // Массив способов оплаты.
    $deliveryCount = count($deliveryArray);  
   
    // если из доступных способов доставки - только один, то сразу находим для него способы оплаты
    if($deliveryCount===1) {
      $keyDev = array_keys($deliveryArray);
      $_POST['delivery'] = $deliveryArray[$keyDev[0]]['id'];
    }
    
    $paymentTable = $this->getPaymentByDeliveryId($_POST['delivery'],$_POST['customer'],true,$deliveryCount);
   
    // если доставка не предусмотрена, то выводим все доступные активные метода оплаты
    if ($deliveryCount === 0) {
      $paymentTable = '';
      foreach ($this->getPayment() as $payment) {
        $paymentRate = '';
        
        $delivArray = json_decode($payment['deliveryMethod'], true);
        if ($_POST['customer'] == "yur" && $payment['id'] != "7") {
          continue;
        }

        if (!empty($payment['rate'])) {
          $paymentRate = (abs($payment['rate']) * 100) . '%';

          if ($payment['rate'] > 0) {
            $paymentRate = '(Комиссия ' . $paymentRate . ')';
          } else {
            $paymentRate = '(Скидка ' . $paymentRate . ')';
          }
        }

        $paymentTable .= '
         <li class="noneactive">
           <label>
           <input type="radio" name="payment" rel value=' . $payment['id'] . '>' .
                $payment['name'] .
                '</label>
           <span class="icon-payment-' . $payment['id'] . '"></span>
             <span class="rate-payment">'.$paymentRate.'</span>
         </li>';
      }
    }

    if($step == 1) {
      mgAddCustomPriceAction(array(__CLASS__, 'applyRate'));
    }
    
    $cart = new Models_Cart;
    $summOrder = $cart->getTotalSumm();       
    $summOrder = MG::numberFormat($summOrder);
    if ($step !=5 ) {
      $orderInfo = $model->getOrder('`'.PREFIX.'order`.id = '.DB::quote(intval($orderId)).'');
    }    
    $userInfo = USER::getUserInfoByEmail($orderInfo[$orderId]['user_email']);
    $settings = MG::get('settings');
    $orderNumber = $orderInfo[$orderId]['number'] != '' ? $orderInfo[$orderId]['number'] : $orderId;
    $linkToStatus = $orderInfo[$orderId]['hash'] ? $orderInfo[$orderId]['hash'] : '';
    
    $deliveryInfo = $model->getDeliveryMethod(false, $_POST['delivery']);
    if(!empty($deliveryInfo['cost'])) {
      $deliveryPrice = '+ доставка: <span class="order-delivery-summ">'.round($deliveryInfo['cost']).' '.MG::getSetting('currency').'</span>';
    }
    if(!empty($deliveryInfo['cost'])&&($deliveryInfo['free']<MG::numberDeFormat($summOrder))) {
      $deliveryPrice = '';   
    }   
    
    // Массив параметров для отображения в представлении.
    $this->data = array(
      'active' => !empty($userEmail) ? $userEmail : '', //состояние активации пользователя.
      'msg' => !empty($msg) ? $msg : '', //сообщение.
      'step' => !empty($step) ? $step : '', //стадия оформления заказа.
      'delivery' => !empty($deliveryArray) ? $deliveryArray : '', //массив способов доставки.
      'deliveryInfo' => $deliveryPrice,
      'paymentArray' => !empty($paymentTable) ? $paymentTable : '', //массив способов оплаты.
      'paramArray' => !empty($paramArray) ? $paramArray : '', //массив способов оплаты.
      'id' => !empty($orderId) ? $orderId : '', //id заказа.
      'orderNumber' => !empty($orderNumber) ? $orderNumber : $orderId, //id заказа.
      'summ' => !empty($summ) ? $summ : '', //сумма заказа.
      'pay' => !empty($pay) ? $pay : '', //
      'payMentView' => $this->getPaymentView($pay), //
      'currency' => $settings['currency'],
      'userInfo' => $userInfo,
      'orderInfo' => $orderInfo,
      'fileToOrder' => $fileToOrder,
      'meta_title' => 'Оформление заказа',
      'meta_keywords' => !empty($model->currentCategory['meta_keywords']) ? $model->currentCategory['meta_keywords'] : "заказы,заявки,оформить,оформление заказа",
      'meta_desc' => !empty($model->currentCategory['meta_desc']) ? $model->currentCategory['meta_desc'] : "Оформление заказа происходит в несколько этапов. 1 - ввод личных данных покупателя, 2 - оплата заказа.",
      'summOrder' => !empty($summOrder) ? $summOrder.' '.MG::getSetting('currency') : '', //сумма заказа без доставки
      'captcha' => (MG::getSetting('captchaOrder') == 'true' ? true : false),
      'recaptcha' => ((MG::getSetting('captchaOrder') == 'true' && MG::getSetting('useReCaptcha') == 'true' && MG::getSetting('reCaptchaSecret') && MG::getSetting('reCaptchaKey')) ? true : false),
      'linkToStatus' => $linkToStatus,
      );
  }    

  /**
   * Возвращает путь к странице с формой оплаты.
   * @param int $pay id способа оплаты.
   * @return string путь к странице с формой оплаты.
   */
  public function getPaymentView($pay) {
    switch ($pay) {
      case 1:
        $payMentView = 'webmoney.php';
        break;
      case 2:
        $payMentView = 'yandex.php';
        break;
      case 5:
        $payMentView = 'robokassa.php';
        break;
      case 6:
        $payMentView = 'qiwi.php';
        break;
      case 7:
        $payMentView = 'quittance.php';
        break;
      case 8:
        $payMentView = 'interkassa.php';
        break;
      case 9:
        $payMentView = 'payanyway.php';
        break;
      case 10:
        $payMentView = 'paymaster.php';
        break;
      case 11:
        $payMentView = 'alfabank.php';
        break;
      case 14:
        $payMentView = 'yandex-kassa.php';
        break;
      case 15:
        $payMentView = 'privat24.php';
        break;
      case 16:
        $payMentView = 'liqpay.php';
        break;
      case 17:
        $payMentView = 'sberbank.php';
        break;
      case 18:
        $payMentView = 'tinkoff.php';
        break;
      case 19:
        $payMentView = 'paypal.php';
        break;
      case 20:
        $payMentView = 'comepay.php';
        break;
      case 21:
        $payMentView = 'paykeeper.php';
        break;
      case 22:
        $payMentView = 'cloudpayments.php';
        break;
    }
    $dir = URL::getDocumentRoot();
    if (file_exists($dir.'mg-templates'.DS.MG::getSetting('templateName').DS.'layout'.DS.'payment_'.$payMentView)) {
      $payMentView2 = $dir.'mg-templates'.DS.MG::getSetting('templateName').DS.'layout'.DS.'payment_'.$payMentView;
    }
    elseif (file_exists($dir.'mg-core'.DS.'layout'.DS.'payment_'.$payMentView)) {
      $payMentView2 = $dir.'mg-core'.DS.'layout'.DS.'payment_'.$payMentView;
    }
    else{
      $payMentView2 = 'mg-pages/payment/'.$payMentView;
    }

    return $payMentView2;
  }

  /**
   * Возвращает сообщение о статусе заказа "Подтвержден".
   * @param int $pay - id заказа.
   * @return array - сообщение и email пользователя.
   */
  public function confirmOrder($id) {
    // Модель для работы заказом.
    $model = new Models_Order;
    // Информация о заказе по переданному id.
    $orderInfo = $model->getOrder('`'.PREFIX.'order`.id = '.DB::quote(intval($id)));
    $hash = URL::getQueryParametr('sec');
    // Информация о пользователе, сделавший заказ .
    $orderUser = USER::getUserInfoByEmail($orderInfo[$id]['user_email']);
    $orderNumber = !empty($orderInfo[$id]['number']) ? $orderInfo[$id]['number'] : $id;
    // Если присланный хэш совпадает с хэшом из БД для соответствующего id.
    if ($orderInfo[$id]['confirmation'] == $hash) {
      if ($orderInfo[$id]['hash'] == '') {
          // $msg = 'Посмотреть статус заказа Вы можете в <a href="'.SITE.'/personal">личном кабинете</a>.';
          $msg = MG::restoreMsg('msg__view_status',array('#NUMBER#' => $orderNumber, '#LINK#' => SITE.'/personal'));
        } 
        else  {
          // $msg = 'Следить за статусом заказа Вы можете по ссылке <br> '
          //   . '<a href="'.SITE.'/order?hash='.$orderInfo[$id]['hash'].'">'.SITE.'/order?hash='.$orderInfo[$id]['hash'].'</a>';
          $msg = MG::restoreMsg('msg__view_order',array('#NUMBER#' => $orderNumber, '#LINK#' => SITE.'/order?hash='.$orderInfo[$id]['hash']));
        }
      // Если статус заказа "Не подтвержден".
      if (0 == $orderInfo[$id]['status_id']) {
        // Подтверждаем заказ.
        $orderStatus = 1;
        // если оплата выбрана наложенным платежём или наличными(курьеру), то статус заказа изменяем на "в доставке"
        if(in_array($orderInfo[$id]['payment_id'], array(3, 4))) {  
          $orderStatus = 6;
        }    
        
        $model->sendStatusToEmail($id, $orderStatus);
        $model->setOrderStatus($id, $orderStatus);
        
        $orderNumber = $orderInfo[$id]['number'];    
        $orderId = $id;
        // $msg = 'Ваш заказ №'.$orderNumber.' подтвержден и передан на обработку. <br>'.$msg;
        $msg = MG::restoreMsg('msg__order_confirmed',array('#NUMBER#' => $orderNumber)).$msg;
      } else {
        // $msg = 'Заказ уже подтвержден и находится в работе. <br> '.$msg;
        $msg = MG::restoreMsg('msg__order_processing',array('#NUMBER#' => $orderNumber)).$msg;
      }
      if (!$orderUser->activity) {
        $userEmail = $orderUser->email;
        $_SESSION['id'] = $orderUser->id;
      }
    } else {
      // $msg = 'Некорректная ссылка.<br> Заказ не подтвержден<br>';
      $msg = MG::restoreMsg('msg__order_not_confirmed',array('#NUMBER#' => $orderNumber));
    }

    $result = array(
      'msg' => $msg,
      'userEmail' => $userEmail,
    );
    return $result;
  }

  /**
   * Возвращает массив доступных способов доставки.
   * <code>
   * $result = Controllers_Order::getDelivery();
   * viewData($result);
   * </code>
   * @return array массив доступных способов доставки.
   */
  public function getDelivery() {
    $result = array();

    // Модель для работы с заказом.
    $model = new Models_Order;
    $cart = new Models_Cart;
    $cartSumm = $cart->getTotalSumm();

    foreach ($model->getDeliveryMethod() as $id => $delivery) {
      if ($delivery['free'] != 0 && $delivery['free'] <= $cartSumm) {
        $delivery['cost'] = 0;
      }

      if (!$delivery['activity']) {
        continue;
      }

      if (isset($_POST['delivery']) && $_POST['delivery'] == $id) {
        $delivery['checked'] = 1;
      }

      // Заполнение массива способов доставки.
      $result[$delivery['id']] = $delivery;
    }

    // Если доступен только один способ доставки, то он будет выделен.
    if (1 === count($result)) {
      $deliveryId = array_keys($result);
      $result[$deliveryId[0]]['checked'] = 1;
    }

    return $result;
  }

  /**
   * Возвращает массив доступных способов оплаты.
   * <code>
   * $result = Controllers_Order::getDelivery();
   * viewData($result);
   * </code>
   * @return array массив доступных способов оплаты.
   */
  public function getPayment() {
    $result = array();

    // Модель для работы с заказом.
    $model = new Models_Order;

    $i = 1;
    // Количество активных методов оплаты.
    $countPaymentMethod = 0;
    $allPayment = $model->getPaymentBlocksMethod();
    foreach ($allPayment as $payment) {
      $i++;
      if ($_POST['payment'] && !empty($deliveryArray)) {
        $delivArray = json_decode($payment['deliveryMethod'], true);
        if (!$delivArray[$_POST['delivery']])
          continue;
      }

      if (!$payment['activity']) {
        continue;
      }

      if ($_POST['payment'] == $payment['id']) {
        $payment['checked'] = 1;
      }

      // Заполнение массива способов оплаты.
      $result[$payment['id']] = $payment;
      $countPaymentMethod++;
    }
    return $result;
  }

  /**
   * Возвращает массив доступных способов оплаты с учетом количества способов доставки.
   * @deprecated
   * @param array массив способов доставки
   * @return array массив доступных способов оплаты.
   */
  public function getPaymentTable($deliveryArray) {
    $result = array();
    // Массив способов оплаты.
    $paymentArray = $this->getPayment();

    // Если доступен только один способ доставки.
    if (1 == count($deliveryArray)) {
      $deliveryId = array_keys($deliveryArray);
      foreach ($paymentArray as $payment) {
        $delivArray = json_decode($payment['deliveryMethod'], true);
        if (!$delivArray[$deliveryId[0]]) {
          continue;
        }
        $result[$payment['id']] = $payment;
      }
    } else {
      $result = $paymentArray;
    }

    // Если доступен только один способ оплаты, то он будет выделен.
    if (1 == count($result)) {
      $paymentId = array_keys($result);
      $result[$paymentId[0]]['checked'] = 1;
    }

    return $result;
  }

  /**
   * Используется при AJAX запросе, 
   * возвращает html список способов доставки в зависимости от 
   * выбранного способа доставки.
   * @param int ID заказа
   */
  public function getDeliveryOrderOptions($orderId=null) {
    $orderId = intval($_POST['order_id']); 
    $orderOptions = array();
    $model = new Models_Order();
    $delivery = $model->getDeliveryMethod(false, $_POST['deliveryId']);
    $orderOptions = array(
      'deliverySum' => $delivery['cost'],
    );        
    //Если указан id заказа
    if($orderId > 0) {      
      $orderInfo = $model->getOrder(' id = '.DB::quote($orderId));
      
      if(!empty($delivery['plugin'])) {
        if($orderInfo[$orderId]['delivery_id'] == $_POST['deliveryId']) {
          if(empty($_SESSION['deliveryAdmin'][$_POST['deliveryId']])) {
            $orderOptions = unserialize(stripslashes($orderInfo[$orderId]['delivery_options']));  
            $_SESSION['deliveryAdmin'][$_POST['deliveryId']] = $orderOptions;  
          }   
          
          $orderOptions['deliverySum'] = 0;
        } else {
          $orderOptions = $_SESSION['deliveryAdmin'][$_POST['deliveryId']];
          $orderOptions['deliverySum'] = 0;
        }        
      } else {
        if($orderInfo[$orderId]['delivery_id'] == $_POST['deliveryId']) {
          $orderOptions = array(
            'deliverySum' => $orderInfo[$orderId]['delivery_cost'],
          );
        }
      }          
    } else {          
      if(!empty($delivery['plugin'])) {
        $orderOptions = $_SESSION['deliveryAdmin'][$_POST['deliveryId']];
        $orderOptions['deliverySum'] = 0;
      }
    } 
    
    echo json_encode($orderOptions);
    exit();
  }
  
  /**
   * Используется при AJAX запросе, 
   * возвращает html список способов оплаты в зависимости от 
   * выбранного способа доставки.
   * @param int ID заказа
   * @param string тип покупателя
   * @param bool возвращать верстку или ajax ответ
   * @param int количество доставок
   * @return string html верстка
   */
  public function getPaymentByDeliveryId($deliveryId=null,$customer=null,$nojson=false, $countDeliv=null) {
    
    if(!$deliveryId) {
      $deliveryId = $_POST['deliveryId'];
    }
    if(!$customer) {
      $customer = $_POST['customer'];
    }    
    if($countDeliv===1) {
      $seletFirst = true;
    }    
   
    $countPaymentMethod = 0; //количество активных методов оплаты

    $paymentTable = '';
    foreach ($this->getPayment() as $payment) {   
      $delivArray = json_decode($payment['deliveryMethod'], true);
      if($customer=="yur" && $payment['permission'] == "fiz") {
        continue;
      }

      if($customer=="fiz" && $payment['permission'] == "yur") {
        continue;
      }
      
      if (!$delivArray[$deliveryId] || !$payment['activity']) {
        continue;
      } 

      $countPaymentMethod++;
    }

    foreach ($this->getPayment() as $payment) {

      $delivArray = json_decode($payment['deliveryMethod'], true);      
      $paymentRate = '';
     
      if($customer=="yur" && $payment['permission'] == "fiz") {
        continue;
      }

      if($customer=="fiz" && $payment['permission'] == "yur") {
        continue;
      }
      
      if (!$delivArray[$deliveryId] || !$payment['activity']) {
        continue;
      }

      MG::loadLocaleData($payment['id'], $_POST['lang'], 'payment', $payment);

      $payActive = false;

      if ($payment['id']===$_POST['payment'] || 1 == $countPaymentMethod) {
        $payActive = true;
      }

      $paymentTable .= MG::layoutManager('layout_payment', array('id' => $payment['id'], 'name' => $payment['name'], 'rate' => $payment['rate'], 'active' => $payActive));
    }

    if($nojson) {
      return $paymentTable;
    }
    
    $summDelivery = 0;                             
    $deliveryArray = $this->getDelivery();
    foreach($deliveryArray as $delivery) {
      if ($delivery['id'] == $deliveryId && $delivery['cost'] != 0 ) {
        $summDelivery = MG::numberFormat($delivery['cost']).' '.MG::getSetting('currency');
      }
    }    
    
    $result = array(
      'status' => true,
      'paymentTable' => $paymentTable,
      'summDelivery' => $summDelivery,
    );
    
    $args = func_get_args();
    
    if(empty($args)) {
      $args = array($deliveryId);
    }
    
    $result = MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
    echo json_encode($result);
    MG::disableTemplate();    
    exit;
  }
  /**
   * Устанавливает наценку от способа оплаты 
   * <code>
   * $_POST['paymentId'] = 1;
   * $_SESSION['price_rate'] = 0.5;
   * $model = new Controllers_Order();
   * $model->setPaymentRate();
   * </code>
   */
  public function setPaymentRate() {
    if(!empty($_POST['paymentId'])) {
      $order = new Models_Order();
      $payment = $order->getPaymentMethod($_POST['paymentId']);
      
      if(!empty($payment['rate'])) {
        $_SESSION['price_rate'] = $payment['rate'];
        mgAddCustomPriceAction(array(__CLASS__, 'applyRate'));        
      } else {
        $_SESSION['price_rate'] = 0;
      }
      
      $cart = new Models_Cart;
      $summOrder = $cart->getTotalSumm();       
      $res = array(
        'summ' => MG::numberFormat($summOrder).' '.htmlspecialchars_decode(MG::getSetting('currency')), 
        'rate' => $_SESSION['price_rate'], 
        'cur' => htmlspecialchars_decode(MG::getSetting('currency')),
        'enableDeliveryCur' => MG::getSetting('enableDeliveryCur')); 
      echo json_encode($res);
      exit;
    }        
  }
  /**
   * Добавляет к заказу наценку от способа оплаты 
   * <code>
   * $_SESSION['price_rate'] = 0.5;
   * $product = Array(
   *   'priceWithCoupon' => 19499,
   *   'priceWithDiscount' => 19499
   * );
   * $model = new Controllers_Order();
   * $res = $model->applyRate($product);
   * viewData($res);
   * </code>
   * @param array массив параметров заказа
   * @return float
   */
  function applyRate($args) {
    $price = $args['priceWithCoupon'] < $args['priceWithDiscount'] ? $args['priceWithCoupon'] : $args['priceWithDiscount'];
    if(!empty($_SESSION['price_rate'])) {
      $price += $price * $_SESSION['price_rate'];  
    }
    return round($price, 2);   
  }
  
  /**
   * Используется при AJAX запросе.
   * <code>
   * $_POST['paymentId'] = 1;
   * $model = new Controllers_Order();
   * $model->getEssentialElements();
   * </code>
   */
  public function getEssentialElements() {
    $paymentId = $_POST['paymentId'];
    $paramArray = $model->getParamArray($paymentId, $orderId, $summ);
    $result = array(
      'name' => $paramArray[0]['name'],
      'value' => $paramArray[0]['value']
    );
    echo json_encode($result);
    MG::disableTemplate();
    exit;
  }

  /**
   * Подключает набор иконок для способов оплаты.
   * <code>
   * $model = new Controllers_Order();
   * $model->includeIconsPack();
   * </code>
   */
  public function includeIconsPack() {
    /* Иконки оплаты для сайта */
    mgAddMeta('<link type="text/css" href="'.SCRIPT.'standard/css/layout.order.css" rel="stylesheet"/>');
  }

}
