<div class="payment-form-block">

<form method="post" action="https://www.moneta.ru/assistant.htm">
<input type="hidden" name="MNT_ID" value="<?php echo $data['paramArray'][0]['value']?>">
<input type="hidden" name="MNT_TRANSACTION_ID" value="<?php echo $data['id'] ?>">
<input type="hidden" name="MNT_CURRENCY_CODE" value="<?php echo (MG::getSetting('currencyShopIso')=="RUR")?"RUB":MG::getSetting('currencyShopIso');?>">
<input type="hidden" name="MNT_AMOUNT" value="<?php echo sprintf("%01.2f", $data['summ']);?>">
<input type="hidden" name="MNT_SIGNATURE" value="<?php echo $data['paramArray']['sign'] ?>">
<?php if ($data['paramArray'][2]['value']=='true') :?>
  <input type="hidden" name="MNT_TEST_MODE" value="1"> 
 <?php endif;?>
<input type="submit" value="<?php echo lang('paymentPay'); ?>">
</form>

<p>
 <em>
 <?php echo lang('paymentDiff1'); ?>"<a href="<?php echo SITE?>/personal"><?php echo lang('paymentDiff2'); ?></a>".
 </em>
 </p>
</div>