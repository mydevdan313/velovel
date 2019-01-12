<p style="font-size:12px;line-height:16px;margin:0;">
    Вы получили это письмо, так как произведена оплата заказа №<?php echo $data['number']?> 
     на сумму <?php echo $data['summ'].' '.MG::getSetting('currency')?>. 
     Оплата произведена при помощи <?php echo $data['payment']?> <br/>
     Статус заказа сменен на "<?php echo $data['status']?> "
</p>
  