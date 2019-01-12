<div class="payment-form-block">
  <form action="https://api.privatbank.ua/p24api/ishop" method="POST" accept-charset="UTF-8">
    <input type="hidden" name="amt" value="<?php echo sprintf("%01.2f", $data['summ'])?>"/>
    <input type="hidden" name="ccy" value="UAH" />
    <input type="hidden" name="merchant" value="<?php echo $data['paramArray'][0]['value']?>" />
    <input type="hidden" name="order" value="<?php echo $data['id']?>" />
    <input type="hidden" name="details" value="<?php echo 'заказ на '.SITE?>" />
    <input type="hidden" name="ext_details" value="<?php echo $data['orderNumber']?>" />
    <input type="hidden" name="pay_way" value="privat24" />
    <input type="hidden" name="return_url" value="<?php echo SITE?>/payment?id=15&pay=result" />
    <input type="hidden" name="server_url" value="<?php echo SITE?>/payment?id=15&pay=result" />
    <input type="hidden" name="signature" value="<?php echo $data['paramArray']['sign']?>" />
    <input type="submit" value="Оплатить" />
  </form>
</div>