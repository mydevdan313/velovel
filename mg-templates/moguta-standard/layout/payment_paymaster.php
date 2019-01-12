<div class="payment-form-block">


<?php 

	if (MG::getSetting('currencyShopIso') == 'RUR') {
		$currency = 'RUB';
	}
	else{
		$currency = MG::getSetting('currencyShopIso');
	}

	if ($data['paramArray'][3]['value'] == 'true' || $data['paramArray'][3]['value'] == true || $data['paramArray'][3]['value'] == 1) {


		$content = unserialize(stripslashes($data['orderInfo'][$data['id']]['order_content']));

		if ((count($content) < 11 && $data['orderInfo'][$data['id']]['delivery_cost'] == 0) || (count($content) < 11 && $data['orderInfo'][$data['id']]['delivery_cost'] > 0)) {

			switch ($data['paramArray'][4]['value']) {
				case 'без НДС':
					$tax = 'no_vat';
					break;
				case '0%':
					$tax = 'vat0';
					break;
				case '10%':
					$tax = 'vat110';
					break;
				
				default:
					$tax = 'vat118';
					break;
			}

			echo '<form id="pay" name="pay" method="POST" action="https://paymaster.ru/Payment/Init">';
			echo '<input type="hidden" name="LMI_MERCHANT_ID" value="'.$data['paramArray'][0]['value'].'">';
			echo '<input type="hidden" name="LMI_PAYMENT_AMOUNT" value="'.$data['summ'].'">';
			echo '<input type="hidden" name="LMI_CURRENCY" value="'.$currency.'">';
			echo '<input type="hidden" name="LMI_PAYMENT_NO" value="'.$data['id'].'">';
			echo '<input type="hidden" name="LMI_PAYMENT_DESC" value="Oplata zakaza # '.$data['orderNumber'].'">';

			$lastkey = 0;

			foreach ($content as $key => $value) {
				$lastkey++;
				$tmp = explode(PHP_EOL, $content[$key]['name']);

				echo '<input type="hidden" name="LMI_SHOPPINGCART.ITEM['.$key.'].NAME" value="'.MG::textMore($tmp[0], 125).'">';
				echo '<input type="hidden" name="LMI_SHOPPINGCART.ITEM['.$key.'].QTY" value="'.(float)round($content[$key]['count'], 3).'">';
				echo '<input type="hidden" name="LMI_SHOPPINGCART.ITEM['.$key.'].PRICE" value="'.(float)round($content[$key]['price'], 2).'">';
				echo '<input type="hidden" name="LMI_SHOPPINGCART.ITEM['.$key.'].TAX" value="'.$tax.'">';

				unset($item);
				unset($tmp);
			}
			
			if ($data['orderInfo'][$data['id']]['delivery_cost'] > 0) {
				echo '<input type="hidden" name="LMI_SHOPPINGCART.ITEM['.$lastkey.'].NAME" value="Dostavka">';
				echo '<input type="hidden" name="LMI_SHOPPINGCART.ITEM['.$lastkey.'].QTY" value="1">';
				echo '<input type="hidden" name="LMI_SHOPPINGCART.ITEM['.$lastkey.'].PRICE" value="'.(float)round($data['orderInfo'][$data['id']]['delivery_cost'], 2).'">';
				echo '<input type="hidden" name="LMI_SHOPPINGCART.ITEM['.$lastkey.'].TAX" value="'.$tax.'">';
			}

			echo '<input type="submit" class="btn" value="'.lang('paymentPay').'" style="padding: 10px 20px;">';
			echo '</form>';
			echo '<p>';
			echo '<em>';
			echo lang('paymentDiff1').'"<a href="'.SITE.'/personal">'.lang('paymentDiff2').'</a>".';
			echo '</em>';
			echo '</p>';

		}
		else{
			echo '<p>'.lang('paymentPaymasterFail1').'</p>';
			echo '<p>'.lang('paymentPaymasterFail2').'</p>';
		}
	}
	else{
		echo '<form id="pay" name="pay" method="POST" action="https://paymaster.ru/Payment/Init">';
		echo '<input type="hidden" name="LMI_MERCHANT_ID" value="'.$data['paramArray'][0]['value'].'">';
		echo '<input type="hidden" name="LMI_PAYMENT_AMOUNT" value="'.$data['summ'].'">';
		echo '<input type="hidden" name="LMI_CURRENCY" value="'.$currency.'">';
		echo '<input type="hidden" name="LMI_PAYMENT_NO" value="'.$data['id'].'">';
		echo '<input type="hidden" name="LMI_PAYMENT_DESC" value="Oplata zakaza # '.$data['orderNumber'].'">';
		echo '<input type="submit" class="btn" value="'.lang('paymentPay').'" style="padding: 10px 20px;">';
		echo '</form>';
		echo '<p>';
		echo '<em>';
		echo lang('paymentDiff1').'"<a href="'.SITE.'/personal">'.lang('paymentDiff2').'</a>".';
		echo '</em>';
		echo '</p>';
	}
?>

</div>