<div class="payment-form-block-n">

   <form action="<?php echo $data['paramArray'][1]['value'] ?>" method="POST" type="application/x-www-form-urlencoded" accept-charset="utf-8">
	<input type="hidden" name="sum" value="<?php echo $data['summ'] ?>"/>
	<input type="hidden" name="orderid" value="<?php echo $data['orderNumber'] ?>"/>
	<input type="hidden" name="clientid" value="<?php echo $data['id'] ?>"/>
	<input type="hidden" name="phone" value="<?php echo $data['phone'] ?>"/>
	<input name="gopay" type="submit" class="c-button" value="Перейти на страницу оплаты"/>
	</form>
	
<br/><br/><br/>
</div>