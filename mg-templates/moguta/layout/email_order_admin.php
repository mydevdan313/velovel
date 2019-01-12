<table bgcolor="#FFFFFF" cellspacing="0" cellpadding="10" border="0" width="675">
  <tbody>
     <tr>
      <td>
        <h3 style="font-size:18px;font-weight:normal;margin:0;">
            На сайте <?php echo $data['siteName'] ?> магазина «<strong><?php echo $data['shopName'] ?></strong>» создан заказ №<?php echo $data['orderNumber'] ?>
          <small>(<?php echo date('d.m.Y H:i', strtotime($data['formatedDate'])) ?>)</small></h3>
      </td>
    </tr>
    <tr>
      <td>
           <span>Информация о заказе:</span>
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
				Тел: <span class="js-phone-number highlight-phone"><?php echo $data['phone'] ?></span>         <br>           
<?php if (!empty($data['yur_info']["nameyur"])&&!empty($data['yur_info']["inn"])) :?>       
        <br>
        <span>Юр. лицо: </span><?php echo ($data['yur_info']["nameyur"]) ?><br>
        <span>Юр. адрес: </span><?php echo ($data['yur_info']["adress"]) ?><br>
        <span>ИНН: </span><?php echo ($data['yur_info']["inn"]) ?><br>
        <span>КПП: </span><?php echo ($data['yur_info']["kpp"]) ?><br>          
        <span>Банк: </span><?php echo ($data['yur_info']["bank"]) ?><br>
        <span>БИК: </span><?php echo ($data['yur_info']["bik"]) ?><br>
        <span>К/Сч: </span><?php echo ($data['yur_info']["ks"]) ?><br>
        <span>P/Сч: </span><?php echo ($data['yur_info']["rs"]) ?><br>
<?php endif;?>      


              </td>
              <td>&nbsp;</td>
              <td valign="top" style="font-size:12px;padding:7px 9px 9px 9px;border-left:1px solid #EAEAEA;border-bottom:1px solid #EAEAEA;border-right:1px solid #EAEAEA;">
                <p><strong><?php echo $data['payment'] ?></strong></p>



              </td>
            </tr>
          </tbody>
        </table>
        <br>

        <table cellspacing="0" cellpadding="0" border="0" width="675">

          <tbody><tr>
              <th align="left" width="325" bgcolor="#EAEAEA" style="font-size:13px;padding:5px 9px 6px 9px;line-height:1em;">Адрес доставки:</th>
              <th width="10"></th>
              <th align="left" width="325" bgcolor="#EAEAEA" style="font-size:13px;padding:5px 9px 6px 9px;line-height:1em;">Способ доставки:</th>
            </tr>

          </tbody><tbody>
            <tr>
              <td valign="top" style="font-size:12px;padding:7px 9px 9px 9px;border-left:1px solid #EAEAEA;border-bottom:1px solid #EAEAEA;border-right:1px solid #EAEAEA;">            

                <?php echo $data['address'] ?><br>

                &nbsp;
              </td>
              <td>&nbsp;</td>
              <td valign="top" style="font-size:12px;padding:7px 9px 9px 9px;border-left:1px solid #EAEAEA;border-bottom:1px solid #EAEAEA;border-right:1px solid #EAEAEA;">
                <?php echo $data['delivery'] ?>
                &nbsp;
                <?php echo ($data['date_delivery'] ? '<br> Дата: '.$data['date_delivery'] : '') ?>
                &nbsp;
                <?php echo ($data['delivery_interval'] ? '<br> Время доставки: '.$data['delivery_interval'] : '') ?>
                &nbsp;
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
            <td colspan="3" align="right" style="padding:5px 9px 0 9px;font-size: 13px;">
              стоимость товаров                    </td>
            <td align="right" style="padding:5px 9px 0 9px;font-size: 13px;font-weight:bold;">
              <span><?php echo  MG::numberFormat($data['result']).' '.$data['currency'] ?></span>                    </td>
          </tr>
          <tr>
            <td colspan="3" align="right" style="padding:2px 9px;font-size: 13px;">
              доставка                    </td>
            <td align="right" style="padding:2px 9px;font-size: 13px;font-weight:bold;">
              <span><?php echo  MG::numberFormat($data['deliveryCost']).' '.$data['currency'] ?></span>                    </td>
          </tr>
          <tr>
            <td colspan="3" align="right" style="padding:2px 9px 5px 9px; font-size: 13px;font-weight:bold;">
              <strong>полная стоимость</strong>
            </td>
            <td align="right" style="padding:2px 9px 5px 9px; color: #BA0A0A;font-size: 13px;font-weight:bold;">
              <strong><span><?php echo  MG::numberFormat($data['total']).' '.$data['currency'] ?></span></strong>
            </td>
          </tr>
          <?php if ($data['orderWeight'] > 0) { ?>
          <tr>
            <td colspan="3" align="right" style="padding:2px 9px;font-size: 13px;">вес заказа</td>
            <td align="right" style="padding:2px 9px;font-size: 13px;font-weight:bold;">
              <span><?php echo $data['orderWeight']; ?> кг</span>
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
              foreach ($data['custom_fields'] as $key => $value) {
                echo '<tr><td style="font-size:13px;padding:5px 9px;"><b>'.$key.'</b>: '.$value.'</td></tr>';
              }
            ?>
          </tbody> 
        </table>      

            <p style="padding:2px 9px;font-size: 13px;"> <strong>Комментарий покупателя:</strong>
              <?php echo $data['userComment'];?>
            </p>

          

        <p style="font-size:12px;margin:0 0 10px 0">

        </p>
      </td>
    </tr>
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
  </tbody>
</table>

