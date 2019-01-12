<style type="text/css">
  .form-wrapper table{border-collapse: collapse;width:100%;color:#000;}
  .form-wrapper small-table{border-collapse: separate;}
  .form-wrapper table tr th{padding: 10px;border: 1px solid #000;background:#FFFFE0;}
  .form-wrapper .who-pay tr td{padding: 5px;}
  .form-wrapper .who-pay tr td.name{width: 110px;}
  .form-wrapper .who-pay{margin: 10px 0 0 0;}
  .form-wrapper table tr td{padding: 5px;border: 1px solid #000;}
  .form-wrapper table tr td.bottom{border: none;text-align: right;}
  .form-wrapper .order-total{margin: 10px 0 0 0;color:#000;}
  .form-wrapper .title{text-align:center;font-size:24px;color:#000;}
  .form-wrapper .total-list{list-style:none;}
  .form-wrapper .no-border, .form-wrapper .who-pay tr td, .form-wrapper .small-table tr td{border:none;}
  .form-wrapper .colspan4{border:none;text-align:right;}
  .form-wrapper .rowspan2{vertical-align:bottom;}
  .form-wrapper .nowrap{white-space:nowrap;}
  .yur-table td {height:30px;}
  .form-table td {height:30px; vertical-align: baseline;}
  .p {height:30px; vertical-align: baseline;}
</style>
<div class="form-wrapper">
  <br />
  <strong><?php echo $data['propertyOrder']['nameyur']?></strong><br />
  <?php echo $data['propertyOrder']['adress']?>
  <br/>
  <br />
  <strong>Заказ покупателя № <?php echo $data['order']['number']?> от <?php echo $data['order']['add_date']?></strong><br />
  <strong>Товарный чек № <?php echo $data['order']['id']?> от <?php echo $data['order']['updata_date']?></strong><br />
  <?php
  if ($data['order']['address']) {
    echo '<strong>Адрес доставки: </strong>'.$data['order']['address'].'<br />';
  }
  if ($data['order']['date_delivery']) {
    echo '<strong>Дата доставки: </strong>'.$data['order']['date_delivery'].'<br />';
  }
  if ($data['order']['delivery_interval']) {
    echo '<strong>Время доставки: </strong>'.$data['order']['delivery_interval'].'<br />';
  }
  if ($data['order']['user_comment']) {
    echo '<strong>Комментарий пользователя: </strong>'.$data['order']['user_comment'].'<br />';
  }
  if ($data['order']['comment']) {
    echo '<strong>Комментарий менеджера: </strong>'.$data['order']['comment'].'<br />';
  }
  if (!empty($data['order']['optionalFields'])) {
    echo '<br /><strong>Дополнительно: </strong><br />';
    foreach ($data['order']['optionalFields'] as $field) {
      echo '<strong>'.$field['name'].': </strong>'.$field['value'].'<br />';
    }
  }
  ?>
  <br />
  <br/>  
  <table class="form-table">
    <tr>
      <th bgcolor="#FFFFE0" width="40">№</th>
      <th bgcolor="#FFFFE0" >Артикул</th>
      <th bgcolor="#FFFFE0" width="327">Товар</th>
      <th bgcolor="#FFFFE0" width="70">Кол-во</th>
      <th bgcolor="#FFFFE0" >Цена</th>            
      <th bgcolor="#FFFFE0" >Сумма</th>
    </tr>
    <?php 
    $i = 1;
    $totalPrice = 0;
    
    foreach ($data['perOrders'] as $perOrder) {
      $totalPrice += $perOrder['price'] * $perOrder['count'];
      ?>
      <tr>
        <td style="padding: 5px;"><?php echo $i++;?></td>
        <td ><?php echo $perOrder['code'] ?></td>
        <td cellpadding="5"><?php echo $perOrder['name'];?>
        <?php echo htmlspecialchars_decode(str_replace('&amp;', '&', $perOrder['property']));?>
        </td>        
        <td ><?php echo $perOrder['count'];?> шт.</td>
        <td ><?php echo MG::numberFormat($perOrder['price']).' '.$data['currency'];?></td>                
        <td ><?php echo MG::numberFormat($perOrder['price'] * $perOrder['count']).' '.$data['currency'];?></td>
      </tr>  
      <?php
    }
    ?>
  </table>
  <br />    
  <p><strong>Всего наименований <?php echo $i-1;?> шт., на сумму <?php echo MG::numberFormat($totalPrice).'  '.$data['currency'] ?></strong></p>
  <?php
  include('int2str.php');
  $sumToWord = new int2str($totalPrice, true);
  $sumToWord->ucfirst($sumToWord->rub);
  ?>
  <p><strong><?php echo $sumToWord->ucfirst($sumToWord->rub); ?></strong></p>
  <br/>
</div>

<table>  
  <tr>
    <td width="160">Отпуск разрешил</td>
    <td width="10"></td>        
    <td width="140" style="border-bottom: black solid 1px;"></td>    
    <td width="30"></td> 
    <td width="160" align="center">Получил</td>
    <td width="140" style="border-bottom: black solid 1px;"></td>    
    <td width="10"></td>     
  </tr>
  <tr><td colspan="5"></td></tr>
  <tr>
    <td width="160">Отпустил</td>
    <td width="10"></td>
    <td width="140" style="border-bottom: black solid 1px;"></td>  
    <td width="30"></td> 
    <td width="160"></td>
  </tr>  
</table>