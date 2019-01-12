<div class="payment-form-block">

   <form action='https://auth.robokassa.ru/Merchant/Index.aspx' method=POST>
   <input type=hidden name=MrchLogin value=<?php echo $data['paramArray'][0]['value'] ?>>
   <input type=hidden name=OutSum value=<?php echo $data['summ'] ?>>
   <input type=hidden name=InvId value=<?php echo $data['id'] ?>>
   <input type=hidden name=Desc value='Оплата заказа <?php echo $data['orderNumber'] ?>'>
   <input type=hidden name=SignatureValue value=<?php echo $data['paramArray']['sign'] ?>>
   <input type=hidden name=IncCurrLabel value="">
   <input type=hidden name=Culture value="ru">
   <input type=submit value='<?php echo lang('paymentPay'); ?>' style="padding: 10px 20px;">

</form>
<p>
 <em>
 <?php echo lang('paymentDiff1'); ?>"<a href="<?php echo SITE?>/personal"><?php echo lang('paymentDiff2'); ?></a>".
 <br/>
 <?php echo lang('paymentRobo1'); ?><b><span style="color:#0077C0" >Robokassa</span></b>,<b><?php echo $data['paramArray'][0]['value']?></b><?php echo lang('paymentRobo2'); ?>
 </em>
 </p>
</div>