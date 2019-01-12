<div class="payment-form-block">

    <form id="cp_payment" method="POST">
        <input class="green-btn big-btn" type='submit' value='<?php echo lang('paymentPay'); ?>' style="padding: 10px 20px;">
    </form>

    <p>
        <em>
          <?php echo lang('paymentDiff1'); ?>"<a href="<?php echo SITE?>/personal"><?php echo lang('paymentDiff2'); ?></a>".
        </em>
    </p>
</div>
<?php
$order_currency = $data['orderInfo'][$data['id']]['currency_iso'];
if ($order_currency == 'RUR') {
  $order_currency = 'RUB';
}
$data['orderInfo'][$data['id']]['phone'] = preg_replace("/[^0-9+]/", '', $data['orderInfo'][$data['id']]['phone']);

$params = array(
  'publicId'    => $data['paramArray'][0]['value'],  //id из личного кабинета
  'description' => lang('paymentCloudPaymentsDescription') . $data['orderNumber'], //назначение
  'amount'      => floatval($data['summ']), //сумма
  'currency'    => $order_currency, //валюта
  'invoiceId'   => $data['orderNumber'], //номер заказа  (необязательно)
  'accountId'   => $data['orderInfo'][$data['id']]['user_email'], //идентификатор плательщика (необязательно)
  'email'       => $data['orderInfo'][$data['id']]['user_email'],
  'data'        => array(
    'order_id'      => $data['id'],
    'name'          => $data['orderInfo'][$data['id']]['name_buyer'],
    'phone'         => $data['orderInfo'][$data['id']]['phone'],
    'cloudPayments' => array()
  )
);
if (intval($data['paramArray'][2]['value']) || $data['paramArray'][2]['value'] == 'true') {
  $ts = substr($data['paramArray'][3]['value'], 3); //Удаляем ts_
  $vat = substr($data['paramArray'][4]['value'], 4); //Удаляем vat_
  if ($vat == 'none') {
    $vat = '';
  }
  $vat_delivery = substr($data['paramArray'][5]['value'], 4); //Удаляем vat_
  if ($vat_delivery == 'none') {
    $vat_delivery = '';
  }

  $receipt = array(
    'Items'          => array(),
    'taxationSystem' => $ts,
    'email'          => $data['orderInfo'][$data['id']]['user_email'],
    'phone'          => $data['orderInfo'][$data['id']]['phone']
  );

  $content = unserialize(stripslashes($data['orderInfo'][$data['id']]['order_content']));
  foreach ($content as $key => $value) {

    $tmp = explode(PHP_EOL, $content[$key]['name']);

    $item = array(
      'label'    => MG::textMore($tmp[0], 125),
      'price'    => floatval($content[$key]['price']),
      'quantity' => floatval($content[$key]['count']),
      'amount'   => floatval($content[$key]['price']) * floatval($content[$key]['count']),
      'vat'      => $vat
    );

    $receipt['Items'][] = $item;
  }
  if ($data['orderInfo'][$data['id']]['delivery_cost'] > 0) {

    $item = array(
      'label'    => 'Доставка',
      'price'    => floatval($data['orderInfo'][$data['id']]['delivery_cost']),
      'quantity' => 1,
      'amount'   => floatval($data['orderInfo'][$data['id']]['delivery_cost']),
      'vat'      => $vat_delivery
    );
    $receipt['Items'][] = $item;
  }

  $params['data']['cloudPayments']['customerReceipt'] = $receipt;
}

$lang   = $data['paramArray'][6]['value'];
$params = json_encode($params);
$base_url = SITE;
if(defined('LANG') && LANG != 'LANG' && LANG != 'default') {
  $base_url = '/'.LANG;
}

$success_url = $base_url . '/payment?id=20&pay=success&orderNumber=' . urlencode($data['orderNumber']);
$fail_url = $base_url . '/payment?id=20&pay=fail&orderNumber=' . urlencode($data['orderNumber']);
?>

<script src="https://widget.cloudpayments.ru/bundles/cloudpayments"></script>
<script>
  (function(show_widget_callback) {
    var form = document.getElementById('cp_payment');
    if (form.addEventListener) {
      form.addEventListener('click', show_widget_callback, false);
    } else {
      form.attachEvent('onclick', show_widget_callback);
    }
  })(function(e) {
    var evt = e || window.event; // Совместимость с IE8
    if (evt.preventDefault) {
      evt.preventDefault();
    } else {
      evt.returnValue = false;
      evt.cancelBubble = true;
    }
    var widget = new cp.CloudPayments({language: '<?php echo $lang; ?>'});
    widget.charge(<?php echo $params; ?>, '<?php echo $success_url; ?>', '<?php echo $fail_url; ?>');
  });
</script>