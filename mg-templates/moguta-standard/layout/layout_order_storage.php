<div class="order-storage c-form">
<p class="c-order__title"><?php echo lang('storageShop'); ?>:</p>
<?php
	unset($_SESSION['forDeferCart']);
	foreach ($data as $item) {
		$_SESSION['forDeferCart'][] = $item;
		if((count($_SESSION['cart']) > count($item['data']))) {
			echo "<label><input value='".$item['id']."' type='radio' name='storage' disabled><span>".$item['name'].'</span></label>';
			echo '<p class="st-error">'.lang('storageDepleted').'</p>';
		} else {
		  	echo "<label><input value='".$item['id']."' type='radio' name='storage' required><span>".$item['name'].'</span></label>';
		}
	}
	
?>
</div>