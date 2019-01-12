<?php
/**
 * Контроллер: Payment
 *
 * Класс Controllers_Payment предназначен для приема и обработки платежей.
 * @package moguta.cms
 * @subpackage Controller
 */
class Controllers_Payment extends BaseController {

  public $msg = "";

  function __construct() {
    $this->msg = "";
    $paymentID = URL::getQueryParametr('id');
    $paymentStatus = URL::getQueryParametr('pay');
    $_POST['url'] = URL::getUrl();
    $modelOrder = new Models_Order();
    
     //MG::loger(print_r($_REQUEST,1));

    switch ($paymentID) {



    }

    $this->data = array(
      'payment' => $paymentID, //id способа оплаты
      'status' => $paymentStatus, //статус ответа платежной системы (result, success, fail)
      'message' => $msg, //статус ответа платежной системы (result, success, fail)
    );
  }

  /**
   * Действие при оплате заказа.
   * Обновляет статус заказа на Оплачен, отправляет письма оповещения, генерирует хук.
   * @param array $args массив с результатом оплаты
   * @return array
   */
  public function actionWhenPayment($args) {
    $result = true;
    ob_start();

    $order = new Models_Order();
    if(method_exists($order, 'updateOrder')) {
      $order->updateOrder(array('id' => $args['paymentOrderId'], 'status_id' => 2, 'paided' => 1));
    }
    if(method_exists($order, 'sendMailOfPayed')) {
      $order->sendMailOfPayed($args['paymentOrderId'], $args['paymentAmount'], $args['paymentID']);
    }
    if(method_exists($order, 'sendLinkForElectro')) {
      $order->sendLinkForElectro($args['paymentOrderId']);
    }

    $content = ob_get_contents();
    ob_end_clean();

    // если в ходе работы метода допущен вывод контента, то записать в лог ошибку.
    if(!empty($content)) {
      MG::loger('ERROR PAYMENT: ' . $content);
    }

    return MG::createHook(__CLASS__ . "_" . __FUNCTION__, $result, $args);
  }




    /**
     * Проверка платежа через PayKeeper.
     */
  public function paykeeper($paymentID, $paymentStatus) {
    $order = new Models_Order();
  
    /* $filename = $_SERVER['DOCUMENT_ROOT'].'/uploads/PayKeeper_log.txt';
    $text = print_r($_POST, true);
    file_put_contents($filename, $text, FILE_APPEND);
    $text = date("d.m.Y H:i:s");
    file_put_contents($filename, $text, FILE_APPEND); */
  
    if('success' == $paymentStatus) {
      
      if(!empty($_POST['clientid'])) {
        $orderInfo = $order->getOrder(" id = " . DB::quote(intval($_POST['clientid']), 1));
        $msg = 'Вы успешно оплатили заказ № ' . $orderInfo[$_POST['clientid']]['number'].'. Спасибо! Ожидайте звонка менеджера.'; 
      } else {
        $msg = 'Вы успешно оплатили заказ. Спасибо! Ожидайте звонка менеджера.';
      }  
      $msg .= $this->msg;
      
    } elseif('result' == $paymentStatus) {
      
      $id = $_POST['id'];
      $paymentAmount = $_POST['sum'];
      $paymentOrderId = $_POST['clientid'];
      $orderid = $_POST['orderid'];
      $key = $_POST['key'];
        
      //Проверка существование заказа и подлинности платежа
      if(!empty($paymentAmount) && !empty($paymentOrderId)) {
        $orderInfo = $order->getOrder(" id = " . DB::quote(intval($paymentOrderId), 1) . " and summ+delivery_cost = " . DB::quote($paymentAmount, 1));
        $paymentInfo = $order->getParamArray($paymentID, $orderInfo['id'], $orderInfo['summ']+$orderInfo['delivery_cost']);
        $secret_seed = trim($paymentInfo[2]['value']);
        
        
        if($key != md5 ($id . sprintf ("%.2lf", $paymentAmount).$paymentOrderId.$orderid.$secret_seed)) {
          echo "Error! Hash mismatch";
          exit();
        }
      }
      
      // предварительная проверка платежа
      if(empty($orderInfo)) {
        echo "ERR: НЕКОРРЕКТНЫЕ ДАННЫЕ ЗАКАЗА";
        exit();
      }
    
      $this->actionWhenPayment(
        array(
          'paymentOrderId' => $paymentOrderId,
          'paymentAmount' => $paymentAmount,
          'paymentID' => $paymentID
        )
      );
    
      // ОТДАЕМ PAYKEEPER ВСЕ OK
      echo "OK ".md5($id.$secret_seed);

    } else {
      $msg = 'Оплата не удалась';
    }
    

    return $msg;
  }

  /**
   * Проверка платежа через CloudPayments.
   * @param int $paymentID ID способа оплаты
   * @param string $paymentStatus статус платежа
   * @return string
   */
  public function cloudpayments($paymentID, $paymentStatus) {
    $orderNumber = URL::getQueryParametr('orderNumber');

    // Редирект из виджета
    if($paymentStatus == 'success') {
      $msg = str_replace('{number}', $orderNumber, lang('paymentCloudPaymentsSuccess'));
      $msg .= $this->msg;
      return $msg;
    } elseif($paymentStatus == 'fail') {
      $msg = str_replace('{number}', $orderNumber, lang('paymentCloudPaymentsFail'));
      $msg .= $this->msg;
      return $msg;
    }

    // Обрабатываем уведомление от CloudPayments
    $response_codes = array(
      'SUCCESS' => 0,
      'ERROR_INVALID_ORDER' => 10,
      'ERROR_INVALID_COST' => 11,
      'ERROR_NOT_ACCEPTED' => 13,
      'ERROR_EXPIRED' => 20
    );

    $response = array(
      'code' => $response_codes['SUCCESS']
    );

    $order = new Models_Order();
    $paymentInfo = $order->getParamArray($paymentID, null, null);
    // Проверяем контрольную подпись
    $post_data    = file_get_contents('php://input');
    $check_sign   = base64_encode(hash_hmac('SHA256', $post_data, $paymentInfo[1]['value'], true));
    $request_sign = isset($_SERVER['HTTP_CONTENT_HMAC']) ? $_SERVER['HTTP_CONTENT_HMAC'] : '';


    if(false && $check_sign !== $request_sign) {
      $response['code'] = $response_codes['ERROR_NOT_ACCEPTED'];
      $response['msg'] = 'Invalid signature';
    } else {
      $action = URL::getQueryParametr('action');
      $orderId = null;
      if(isset($_POST['Data'])) {
        $data = json_decode(str_replace('&quot;', '"', $_POST['Data']), true);
        if(!empty($data['order_id'])) {
          $orderId = intval($data['order_id']);
        }
      }
      if(!empty($orderId)) {
        $orderInfo = $order->getOrder(" id = " . DB::quote($orderId, 1));
        $orderInfo = current($orderInfo);
      } else {
        $orderNumber = isset($_POST['InvoiceId']) ? $_POST['InvoiceId'] : '';
        $orderInfo = $order->getOrder(" number = " . DB::quote($orderNumber));
        $orderInfo = current($orderInfo);
        $orderId = isset($orderInfo['id']) ? $orderInfo['id'] : null;
      }
      if(empty($orderInfo)) {
        $response['code'] = $response_codes['ERROR_INVALID_ORDER'];
        $response['msg'] = 'Order not found';
      } else {
        // Запросы связанные с оплатой, для них проверяем статус заказа и сумму
        $is_payment_callback = in_array($action, array('check', 'pay'));
        $orderSum = floatval($orderInfo['summ']) + floatval($orderInfo['delivery_cost']);

        if($is_payment_callback && in_array($orderInfo['status_id'], array(2, 4, 5))) {
          // Нельзя оплатить уже оплаченный заказ
          $response['code'] = $response_codes['ERROR_NOT_ACCEPTED'];
          $response['msg'] = 'Order already payment or canceled';
        } elseif($is_payment_callback && floatval($_POST['Amount']) != $orderSum) {
          // Проверяем сумму заказа
          $response['code'] = $response_codes['ERROR_INVALID_COST'];
          $response['msg'] = 'Invalid order summ, should be ' . $orderSum;
        } elseif($action == 'pay') {
          $this->actionWhenPayment(
            array(
              'paymentOrderId' => $orderId,
              'paymentAmount' => $orderInfo['summ'],
              'paymentID' => $paymentID
            )
          );
        } elseif(in_array($action, array('fail', 'refund'))) {
          $order = new Models_Order();
          if(method_exists($order, 'updateOrder')) {
            $order->updateOrder(array(
              'id' => $orderId,
              'status_id' => 4
            ), true);
          }
        }
      }
    }

    header('Content-Type: application/json');
    echo json_encode($response, 256); //JSON_UNESCAPED_UNICODE для совместимости с PHP 5.3;
    exit;
  }
}
