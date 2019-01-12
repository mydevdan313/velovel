<?php

function comepayNormalize($lines, $totalAmount)
{
  $totalAmount = round($totalAmount * 100);
  $realAmount  = 0;
  foreach ($lines as $key => $line) {
    $lines[$key]['Amount'] = round($line['Amount'] * 100);
    $realAmount            += $lines[$key]['Amount'];
  }

  if ($realAmount == $totalAmount) {
    foreach ($lines as $index => $line) {
      $lines[$index]['Amount'] = round($line['Amount'] / 100.00, 2);
    }

    return $lines;
  }

  $adjustment         = $totalAmount - $realAmount;
  $coef               = $adjustment / $realAmount;
  $realAmount         = 0;
  $indexForAdjustment = 0;
  $maxSubTotal        = 0;

  foreach ($lines as $index => $line) {
    $newAmount = round($lines[$index]['Amount'] + $lines[$index]['Amount'] * $coef);
    if ($newAmount <= 0) {
      $realAmount = $realAmount + $newAmount;
      continue;
    }
    $lines[$index]['Amount'] = $newAmount;
    $realAmount              = $realAmount + $newAmount;
    if ($lines[$index]['Amount'] > $maxSubTotal) {
      $maxSubTotal        = $lines[$index]['Amount'];
      $indexForAdjustment = $index;
    }
  }

  $diff = $totalAmount - $realAmount;
  if (abs($diff) >= 1) {
    $lines[$indexForAdjustment]['Amount'] += $diff;

  }

  foreach ($lines as $index => $line) {
    $lines[$index]['Amount'] = round($line['Amount'] / 100.00, 2);
  }

  return $lines;
}

function comepaySendRequest(
  $url,
  $method,
  $queryParams = '',
  $params = array(),
  $shopNumber,
  $shopPassword
) {
  if ( ! empty($queryParams)) {
    $url = $url . '?' . http_build_query($queryParams);
  }
  $curl = curl_init();

  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_HEADER, true);
  curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
  $headers = array('Authorization: Basic ' . base64_encode("{$shopNumber}:{$shopPassword}"));

  $httpBody = http_build_query($params);
  if ($method == 'PUT') {
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($curl, CURLOPT_POSTFIELDS, $httpBody);
    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
  }

  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

  curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
  curl_setopt($curl, CURLOPT_TIMEOUT, 60);

  $response       = curl_exec($curl);
  $httpHeaderSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
  $httpBody       = substr($response, $httpHeaderSize);
  $curlError      = curl_error($curl);
  $curlErrno      = curl_errno($curl);
  $responseInfo   = curl_getinfo($curl);
  if ($response === false) {
    MG::loger('COMEPAY ' . "Network error [errno $curlError]: $curlErrno");

    return array($curlError . ' ' . $curlErrno, '');
  }

  curl_close($curl);

  $logMessage = "Path {$url} method {$method}"
                . "\nRequestBody " . json_encode($params)
                . "\nResponseCode {$responseInfo['http_code']}"
                . "\nResponse\n{$response}";
  MG::loger('COMEPAY ' . $logMessage);

  $result = array(
    $httpBody,
    $responseInfo,
  );

  return $result;
}

/**
 * @var array $data
 */

/**
 * Уникальный идентификатор в системе ComePay (числовое значение)
 */
$shopId = $data['paramArray'][0]['value'];
/**
 * Для Basic-авторизации при выставлении счетов
 */
$shopNumber = $data['paramArray'][1]['value'];
/**
 * Для Basic-авторизации при выставлении счетов
 */
$shopPassword = $data['paramArray'][2]['value'];
/**
 * Для авторизации уведомлений платежной системы
 */
$callbackPassword = $data['paramArray'][3]['value'];
/**
 * Время действия счета (в часах) от момента создания. По истечении этого времени оплата невозможна
 */
$lifetimeHours = $data['paramArray'][4]['value'];
/**
 * В тестовом режиме счета выставляются на moneytest.comepay.ru
 */
$testMode = $data['paramArray'][5]['value'];
/**
 * Адрес api
 */
$host = $data['paramArray'][6]['value'];
/**
 * Тестовый адрес api
 */
$hostTest = $data['paramArray'][7]['value'];
/**
 * Отправлять информацию для печати чека через онлайн-кассу
 */
$sendReceipt = $data['paramArray'][8]['value'];
/**
 * 1 - НДС не облагается; 2 - НДС 10%; 3 - НДС 18%
 */
$vatId = $data['paramArray'][9]['value'];
/**
 * 1 - НДС не облагается; 2 - НДС 10%; 3 - НДС 18%
 */
$vatShippingId = $data['paramArray'][10]['value'];
/**
 * Признак способа расчёта (см. табл. 1)
 */
$payattribute = $data['paramArray'][11]['value'];
/**
 * Url страницы куда направить пользователя после успешной оплаты покупки.
 */
$successUrl = SITE . '/payment?id=20&pay=success';
/**
 * Url страницы куда направить пользователя в случае отказа от оплаты покупки.
 */
$failureUrl = SITE . '/payment?id=20&pay=fail';


$currency = (MG::getSetting('currencyShopIso') == "RUR") ? "RUB" : MG::getSetting('currencyShopIso');

if ($testMode === 'true' || $testMode === 1 || $testMode === true) {
  $host = $hostTest;
}

if ( ! empty($lifetimeHours)) {
  $lifetime = new DateTime();
  $lifetime->add(new DateInterval("PT{$lifetimeHours}H"));
  $lifetime        = strval($lifetime->format('Y-m-d\TH:i:s'));
  $customerContact = null;

  if (isset($data['orderInfo'][$data['id']]['user_email'])) {
    $customerContact = $data['orderInfo'][$data['id']]['user_email'];
  }

  $amountTotal = number_format($data['summ'], 2, '.', '') * 1.00;


  $params = array(
    'user'     => 'bankcard',
    'amount'   => $amountTotal,
    'lifetime' => $lifetime,
    'ccy'      => 'RUB',
    'comment'  => "Оплата заказа " . $data['orderNumber'],

  );


  $receipt      = null;
  $payattribute = 4;

  if ($sendReceipt === 'true' || $sendReceipt === 1 || $sendReceipt === true) {
    $receipt = array();
    $content = unserialize(stripslashes($data['orderInfo'][$data['id']]['order_content']));
    foreach ($content as $key => $value) {
      $productFullName = explode(PHP_EOL, $content[$key]['name']);
      $qty             = round($content[$key]['count'] * 1000);
      $cost            = $content[$key]['price'];
      $subTotalAmount  = number_format($cost * $content[$key]['count'], 2, '.', '') * 1.00;
      $desc            = MG::textMore($productFullName[0], 125);
      $receipt[]       = array(
        'Description'    => $desc,
        'Count'          => $qty,
        'Amount'         => $subTotalAmount,
        'Vat'            => $vatId,
        'CalcBySubTotal' => true,
      );
    }

    if ($data['orderInfo'][$data['id']]['delivery_cost'] > 0) {
      $qty            = 1000;
      $subTotalAmount = number_format($data['orderInfo'][$data['id']]['delivery_cost'], 2, '.', '') * 1.00;
      $desc           = 'Доставка';
      $receipt[]      = array(
        'Description'    => $desc,
        'Count'          => $qty,
        'Amount'         => $subTotalAmount,
        'Vat'            => $vatShippingId,
        'CalcBySubTotal' => true,
      );
    }
    $infos                  = comepayNormalize($receipt, $amountTotal);
    $params['infos']        = json_encode($infos);
    $params['payattribute'] = $payattribute;
  }

  /**
   * true
   */
  $transactionId = $data['id'] .= '-' . time();

  $url = "{$host}/api/prv/{$shopId}/bills/{$transactionId}";

  /**
   * Request
   */
  list($response, $responseInfo) = comepaySendRequest(
    $url,
    'PUT',
    '',
    $params,
    $shopNumber,
    $shopPassword
  );

  $result = json_decode($response, true);

  if (in_array($responseInfo['http_code'], array(200, 201))
      && $result['response']['result_code'] === 0
  ) {
    $param = http_build_query(array(
      'shop'        => $shopId,
      'transaction' => $transactionId,
      'successUrl'  => $successUrl,
      'failUrl'     => $failureUrl,
    ));

    $paymentUrl = "{$host}/Order/Accept?{$param}";
  }
}
?>
<?php if (isset($paymentUrl)) { ?>
    <div class="payment-form-block">
        <table>
            <tr>
                <td></td>
                <td valign="middle">
                    <a style="color: #fff;" class="default-btn" href="<?php echo $paymentUrl ?>"><?php echo lang('paymentPay'); ?></a>
                </td>
            </tr>
        </table>
    </div>
<?php } else {
  echo lang('paymentComepayError');
} ?>