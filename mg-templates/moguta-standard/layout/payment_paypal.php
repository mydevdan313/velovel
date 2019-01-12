<div class="payment-form-block">
<?php 

if ($data['paramArray'][2]['value'] === 'true' || $data['paramArray'][2]['value'] === true || $data['paramArray'][2]['value'] === 1) {
	$link = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
}
else{
	$link = 'https://www.paypal.com/cgi-bin/webscr';
}

$currency = MG::getSetting('currencyShopIso');

if ($currency == 'RUR') {
	$currency = 'RUB';
}

$content = unserialize(stripslashes($data['orderInfo'][$data['id']]['order_content']));

echo '<form method="POST" action="'.$link.'">';
echo '<input type="hidden" name="charset" value="utf-8">';
echo '<input type="hidden" name="currency_code" value="'.$currency.'">';
echo '<input type="hidden" name="business" value="'.$data['paramArray'][1]['value'].'">';
echo '<input type="hidden" name="cmd" value="_cart">';
echo '<input type="hidden" name="upload" value="1">';
echo '<input type="hidden" name="custom" value="'.$data['orderInfo'][$data['id']]['id'].'">';

foreach ($content as $key => $value) {

    $tmp = explode(PHP_EOL, $content[$key]['name']);

    echo '<input type="hidden" name="item_name_'.($key+1).'" value="'.MG::textMore($tmp[0], 125).'">';
    echo '<input type="hidden" name="amount_'.($key+1).'" value="'.(float)round($content[$key]['price'], 2).'">';
    echo '<input type="hidden" name="quantity_'.($key+1).'" value="'.$content[$key]['count'].'">';
    unset($tmp);

    if ($data['orderInfo'][$data['id']]['delivery_cost'] > 0 && $key == 0) {
		echo '<input type="hidden" name="shipping_1" value="'.$data['orderInfo'][$data['id']]['delivery_cost'].'">';
	}
}
?>
		<input type="submit" name="submit-button" value="<?php echo lang('paymentPay'); ?>" class="btn" style="padding: 10px 20px;">
	</form>
</div>