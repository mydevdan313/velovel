<table bgcolor="#FFFFFF" cellspacing="0" cellpadding="10" border="0" width="675">
  <tbody>
  <tr>
      <td valign="top">
      <h1 style="margin: 0 0 10px 0; font-size: 16px;padding: 0;">
          Здравствуйте, <?php echo $data['fio'] ?>!
        </h1>

<?php // echo print_r($data,1); ?>

        <div style="font-size:12px;line-height:16px;margin:0;">
          Ваш заказ <b>№<?php echo $data['orderNumber'] ?></b> успешно оформлен.
      <p class="confirm-info" style="font-size:12px;margin:0 0 10px 0">
      <br>

     <?php /* if(!USER::getUserInfoByEmail($data['email'])->activity) { ?>
      Перейдите по <?php echo $data['confirmLink'] ?> для подтверждения заказа и создания личного кабинета
       <br>
           <?php }*/?>


           <?php
           // если пользователь впервые оформил заказ на сайте, и не имел ранее учетной записи
            if(!empty($data['pass'])) { ?>
            <br>Мы создали для вас  <a href="<?php echo SITE ?>/personal" style="color:#1E7EC8;" target="_blank">личный кабинет</a>, чтобы вы могли следить за статусом заказа, а также скачивать оплаченные электронные товары.
            <br><br> <b>Ваш логин:</b> <?php echo $data['email'];?>
            <br><b>Ваш пароль:</b> <?php echo $data['pass'];?>
           <?php }?>
      </p>
      <?php if(!in_array($data['paymentId'], array(3, 4))) { ?>
      <p class="confirm-info" style="font-size:12px;margin:0 0 10px 0; text-align: center;">
        <a style="text-transform: uppercase; height: 48px; padding: 0 35px; font-size: 18px; line-height: 46px;border: 1px solid #44B926;color: #fff;background: #44B926;display: inline-block;transition: all 0.2s ease-in-out;   text-decoration: none;  outline: none; box-sizing: border-box;" href="<?php echo SITE.'/order?orderID='.$data['id'].'&orderSumm='.$data['total'].'&paymentId='.$data['paymentId'].'&pay=go'; ?>" >Оплатить заказ сейчас</a>
      </p>
      <?php } ?>
       <?php if ($data['toKnowStatus'] == '') {?>
         Вы можете в любой момент узнать статус заказа в Вашем
          <a href="<?php echo SITE ?>/personal" style="color:#1E7EC8;" target="_blank">личном кабинете</a>.
          <?php } else {?>
          <span>После подтверждения заказа, Вы можете в любой момент самостоятельно узнать статус заказа
              по ссылке:<br> <?php echo $data['toKnowStatus'] ?> </span>
          <?php }; ?>
          <br>
          Если у Вас возникнут вопросы — их можно задать по почте:
          <a href="mailto:<?php echo MG::getOption('adminEmail'); ?>" style="color:#1E7EC8;" target="_blank"><?php echo MG::getOption('adminEmail'); ?></a>
          или по телефону
      <span>
            <span class="js-phone-number highlight-phone"><?php echo $data['shopPhone'] ?></span>
          </span>
        </div>
      </td>
    </tr>
    <tr>
      <td>
          <h2 style="font-size:18px;font-weight:normal;margin:0;">Ваш заказ №<?php echo $data['orderNumber'] ?> <small> (<?php echo date('d.m.Y H:i', strtotime($data['formatedDate'])) ?>)</small></h2>
      </td>
    </tr>
    <tr>
      <td>
        <table cellspacing="0" cellpadding="0" border="0" width="675">

          <tbody><tr>
              <th align="left" width="325" bgcolor="#EAEAEA" style="font-size:13px;padding:5px 9px 6px 9px;line-height:1em;">Плательщик:</th>
              <th width="10"></th>
              <th align="left" width="325" bgcolor="#EAEAEA" style="font-size:13px;padding:5px 9px 6px 9px;line-height:1em;">Способ оплаты:</th>
            </tr>

          </tbody><tbody>
            <tr>
              <td valign="top" style="font-size:12px;padding:7px 9px 9px 9px;border-left:1px solid #EAEAEA;border-bottom:1px solid #EAEAEA;border-right:1px solid #EAEAEA;">
                <?php echo $data['fio'] ?><br>
                <br>
        Тел: <span class="js-phone-number highlight-phone"><?php echo $data['phone'] ?></span>


              </td>
              <td>&nbsp;</td>
              <td valign="top" style="font-size:12px;padding:7px 9px 9px 9px;border-left:1px solid #EAEAEA;border-bottom:1px solid #EAEAEA;border-right:1px solid #EAEAEA;">
                <p><strong><?php echo $data['payment'] ?></strong></p>



              </td>
            </tr>
          </tbody>
        </table>
        <br>
        <table cellspacing="0" cellpadding="0" border="0" width="675" style="border:1px solid #EAEAEA;">

          <thead>
            <tr>
              <th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Товар</th>
              <th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Артикул</th>
              <th align="center" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Количество</th>
              <th align="right" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">стоимость товаров</th>
            </tr>
          </thead>
          <tbody bgcolor="#F6F6F6">
            <?php if (!empty($data['productPositions']) || $data['adminOrder']) : ?>
              <?php foreach ($data['productPositions'] as $product) : ?>
                <?php $product['property'] = htmlspecialchars_decode(str_replace('&amp;', '&', $product['property'])); ?>
                <tr>
                  <td style="font-size:13px;padding:5px 9px;"><?php echo $product['name'].$product['property'] ?></td>
                  <td style="font-size:13px;padding:5px 9px;"><?php echo $product['code'] ?></td>
                  <td style="font-size:13px;padding:5px 9px;" align="center"><?php echo $product['count'] ?> шт.</td>
                  <td style="font-size:13px;padding:5px 9px;" align="right"><?php echo MG::numberFormat($product['price']).' '.$data['currency'] ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>

          <tr>
            <td colspan="2" align="right" style="padding:2px 9px 5px 9px; font-size: 13px;font-weight:bold;">
              <strong>полная стоимость</strong>
            </td>
            <td align="right" style="padding:2px 9px 5px 9px; color: #BA0A0A;font-size: 13px;font-weight:bold;">
              <strong><span><?php echo  MG::numberFormat($data['total']).' '.$data['currency'] ?></span></strong>
            </td>
          </tr>
          <?php if ($data['orderWeight'] > 0) { ?>
          <tr>
            <td colspan="2" align="right" style="padding:2px 9px 5px 9px; font-size: 13px;font-weight:bold;">
              <strong>вес заказа</strong>
            </td>
            <td align="right" style="padding:2px 9px 5px 9px; color: #BA0A0A;font-size: 13px;font-weight:bold;">
              <strong><span><?php echo $data['orderWeight']; ?> кг</span></strong>
            </td>
          </tr>
          <?php } ?>
        </table>

        <br>
        <table cellspacing="0" cellpadding="0" border="0" width="675" style="border:1px solid #EAEAEA;">

          <thead>
            <tr>
              <th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Дополнительная информация:</th>
            </tr>
          </thead>
          <tbody>
            <tr><td style="font-size:13px;padding:5px 9px;"><b>Адрес доставки</b>: <?php echo $data['address']; ?></td></tr>
            <?php 
              if ($data['date_delivery']) {
                echo '<tr><td style="font-size:13px;padding:5px 9px;"><b>Дата доставки</b>: '.$data['date_delivery'].'</td></tr>';
              }
              if ($data['delivery_interval']) {
                echo '<tr><td style="font-size:13px;padding:5px 9px;"><b>Время доставки</b>: '.$data['delivery_interval'].'</td></tr>';
              }
              if ($data['orderWeight'] > 0) {
                echo '<tr><td style="font-size:13px;padding:5px 9px;"><b>Вес заказа</b>: '.$data['orderWeight'].' кг</td></tr>';
              }
              foreach ($data['custom_fields'] as $key => $value) {
                echo '<tr><td style="font-size:13px;padding:5px 9px;"><b>'.$key.'</b>: '.$value.'</td></tr>';
              }
            ?>
          </tbody> 
        </table>      

        <p style="font-size:12px;margin:0 0 10px 0">

        </p>
      </td>
    </tr>
    <tr>
      <td bgcolor="#EAEAEA" align="center" style="background:#EAEAEA;text-align:center;">

  
      

  <center>
    <p style="font-size:12px;margin:0;">
      Спасибо за покупку! Следите за новостями на нашем <a href="<?php echo SITE ?>">сайте</a>!
    </p>
  </center>
</td>
</tr>

<?php if(!empty($data['adminMail'])):?>
  <tr>
   <td bgcolor="" align="left" style="background:#F5F3C6;">
      <p style="font-size:11px;margin:0;">
        ip пользователя: <b><?php echo $data['ip']?></b><br/>
        Покупатель сделал этот заказ после перехода из:  <b><?php echo $data['lastvisit']?> </b><br/>
        Покупатель впервые пришел к нам на сайт из:  <b><?php echo $data['firstvisit']?> </b><br/>
        Покупатель использовал купон: <b><?php echo $data['couponCode']?> </b><br/>
      </p>
  </td>
  </tr>
<?php endif;?>

</tbody></table>

