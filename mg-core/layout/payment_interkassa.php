<div class="payment-form-block">
<form id="payment" name="payment" method="post" action="https://sci.interkassa.com/" enctype="utf-8">
	<input type="hidden" name="ik_co_id" value="<?php echo $data['paramArray'][0]['value']?>" />
	<input type="hidden" name="ik_pm_no" value="<?php echo $data['id'] ?>" />
	<input type="hidden" name="ik_am" value="<?php echo $data['summ'] ?>" />
	<input type="hidden" name="ik_cur" value="<?php echo (MG::getSetting('currencyShopIso')=="RUR")?"RUB":MG::getSetting('currencyShopIso');?>" />
	<input type="hidden" name="ik_desc" value='Оплата заказа <?php echo $data['orderNumber'] ?>' />
	<input type="hidden" name="ik_act" value="payways" />
	<input type="hidden" name="ik_int" value="web" />
  <?php if ($data['paramArray'][2]['value']=='true') :?>
    <input type="hidden" name="ik_pw_via" value="test_interkassa_test_xts"> 
    <?php endif;?>
  <input type=submit value='<?php echo lang('paymentPay'); ?>' style="padding: 10px 20px;">
</form>

<p>
 <em>
 <?php echo lang('paymentDiff1'); ?>"<a href="<?php echo SITE?>/personal"><?php echo lang('paymentDiff2'); ?></a>".
 </em>
 </p>
</div>