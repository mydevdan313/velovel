<?php 
if(!empty($data['rate'])) { 
	$paymentRate = (abs($data['rate'])*100).'%';
	if((float)$data['rate'] > 0) {
		$paymentRate = '('.lang('priceMargin').' '.$paymentRate.')';
	} 
	elseif((float)$data['rate'] < 0) {
		$paymentRate = '('.lang('priceSale').' '.$paymentRate.')';
	}
}
?>
<li class="<?php if($data['active']) {echo 'active';} else{echo 'noneactive';} ?>">
	<label class="<?php if($data['active']) {echo 'active';} else{echo 'noneactive';} ?>">
		<input type="radio" name="payment" <?php if($data['active']) {echo 'checked';} else{echo 'rel';} ?> value="<?php echo $data['id']; ?>"><?php echo $data['name']; ?>
	</label>
	<span class="icon-payment-<?php echo $data['id']; ?>"></span>
	<span class="rate-payment"><?php echo $paymentRate; ?></span>
</li>