<style type="text/css">
  * 	{
    padding: 0;
    margin: 0;
    font-family: Arial, Verdana, sans-serif;
  }

  body {
    font-size: 11px;
  }

  table{
    border-collapse: collapse;
  }
</style>


<?php
$addDate = MG::dateConvert(date("d.m.Y"), true);
$arAddDate = explode(' ', $addDate);
$ndsPercent = is_numeric($data['propertyOrder']['nds']) ? $data['propertyOrder']['nds'] : 0;
$totalNds = 0;
$totalWithoutNds = 0;
$totalCount = 0;

if ($ndsPercent === 0) {
  $totalNds = '-';
}
?>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="1260" style="margin: 20px auto 0;">
  <tr>
    <td>
      <table align="center" border="0" cellpadding="0" cellspacing="0">
        <tr>
          <td width="1030">
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="1030">
              <tr>
                <td colspan="2" style="border-bottom: 1px solid #000000; padding: 1px 0;">
                  <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">
                    <?php echo $data['propertyOrder']['nameyur'].', '
                        .'ИНН '. $data['propertyOrder']['inn'].', '
                        .$data['propertyOrder']['adress'].', '
                        .'р/с '.$data['propertyOrder']['rs'].', '
                        .'в банке '.$data['propertyOrder']['bank'].', '
                        .'БИК '.$data['propertyOrder']['bik'].', '
                        .'к/с '.$data['propertyOrder']['ks'];?>
                  </span>
                </td>
              </tr>
              <tr>
                <td colspan="2" align="center" valign="top">
                  <span style="font-family: Arial, Verdana, sans-serif; font-size: 10px;">Грузоотправитель, адрес, номера телефона</span>
                </td>
              </tr>
              <tr>
                <td colspan="2" style="border-bottom: 1px solid #000000; padding: 1px 0;" height="5">
                  <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span>
                </td>
              </tr>
              <tr>
                <td colspan="2" align="center" valign="top" style="padding: 0 0 1px 0;">
                  <span style="font-family: Arial, Verdana, sans-serif; font-size: 10px;">(структурное подразделение)</span>
                </td>
              </tr>
              <tr>
                <td align="right" width="120" style="padding: 0 10px 0 0;">
                  <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">Грузополучатель</span>
                </td>
                <td style="border-bottom: 1px solid #000000; padding: 1px 0;">
                  <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">
                    <?php echo $data['customerInfo']?>
                  </span>
                </td>
              </tr>
              <tr>
                <td colspan="2" align="center" valign="top" style="padding: 0 0 1px 0;">
                  <span style="font-family: Arial, Verdana, sans-serif; font-size: 10px;">(организация, адрес, телефон, факс, банковские реквизиты)</span>
                </td>
              </tr>
              <tr>
                <td align="right" width="120" style="padding: 0 10px 0 0;">
                  <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">Адрес доставки</span>
                </td>
                <td style="border-bottom: 1px solid #000000; padding: 1px 0;">
                  <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">
                    <?php echo $data['order']['address']?>
                  </span>
                </td>
              </tr>
              <tr>
                <td colspan="2" align="center" valign="top" style="padding: 0 0 1px 0;">
                  <span style="font-family: Arial, Verdana, sans-serif; font-size: 10px;">адрес доставки</span>
                </td>
              </tr>
              <tr>
                <td align="right" width="120" style="padding: 0 10px 0 0;">
                  <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">Поставщик</span>
                </td>
                <td style="border-bottom: 1px solid #000000; padding: 1px 0;">
                  <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">
                    <?php echo $data['propertyOrder']['nameyur'].', '
                        .'ИНН '. $data['propertyOrder']['inn'].', '
                        .$data['propertyOrder']['adress'].', '
                        .'р/с '.$data['propertyOrder']['rs'].', '
                        .'в банке '.$data['propertyOrder']['bank'].', '
                        .'БИК '.$data['propertyOrder']['bik'].', '
                        .'к/с '.$data['propertyOrder']['ks'];?>
                    </span>
                </td>
              </tr>
              <tr>
                <td colspan="2" align="center" valign="top" style="padding: 0 0 1px 0;">
                  <span style="font-family: Arial, Verdana, sans-serif; font-size: 10px;">(организация, адрес, телефон, факс, банковские реквизиты)</span>
                </td>
              </tr>
              <tr>
                <td align="right" width="120" style="padding: 0 10px 0 0;">
                  <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">Плательщик</span>
                </td>
                <td style="border-bottom: 1px solid #000000; padding: 1px 0;">
                  <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">
                    <?php echo $data['customerInfo']?>
                  </span>
                </td>
              </tr>
              <tr>
                <td colspan="2" align="center" valign="top" style="padding: 0 0 1px 0;">
                  <span style="font-family: Arial, Verdana, sans-serif; font-size: 10px;">(организация, адрес, телефон, факс, банковские реквизиты)</span>
                </td>
              </tr>
              <tr>
                <td align="right" width="120" style="padding: 0 10px 0 0;">
                  <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">Основание</span>
                </td>
                <td style="border-bottom: 1px solid #000000; padding: 1px 0;">
                  <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">Заказ № <?php echo $data['order']['number'] ?></span>
                </td>
              </tr>
              <tr>
                <td colspan="2" align="center" valign="top" style="padding: 0 0 1px 0;">
                  <span style="font-family: Arial, Verdana, sans-serif; font-size: 10px;">(договор, заказ-наряд)</span>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 0 0 5px 0;">
        <tr>
          <td>
            <table align="center" border="0" cellpadding="0" cellspacing="0">
              <tr>
                <td valign="middle" style="padding: 0 20px 0 0;">
                  <span style="font-family: Arial, Verdana, sans-serif; font-size: 18px; font-weight: bold;">ТОВАРНАЯ НАКЛАДНАЯ</span>
                </td>
                <td>
                  <table align="center" border="0" cellpadding="0" cellspacing="0" style="border: 1px solid #000000; border-collapse: collapse;">
                    <tr>
                      <td style="padding: 1px;">
                        <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">Номер документа</span>
                      </td>
                      <td style="padding: 1px; border-left: 1px solid #000000;">
                        <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">Дата составления</span>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding: 1px; border: 2px solid #000000;" align="center">
                        <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"><?php echo $data['order']['id']; ?></span>
                      </td>
                      <td style="padding: 1px; border: 2px solid #000000;" align="center">
                        <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"><?php echo date("d.m.Y", strtotime($data['order']['add_date'])) ?></span>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
          <td>
            <div class="table-wrapper" style="margin-top: -260px;">
              <table align="right" border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse;" width="420">
                <tr>
                  <td colspan="3" align="right" style="padding: 0 0 1px 0;">
                    <span style="font-family: Arial, Verdana, sans-serif; font-size: 10px;">
                      Унифицированная форма № ТОРГ-12 <br/>
                      Утверждена постановлением Госкомстата России от 25.12.98 № 132
                    </span>
                  </td>
                </tr>
                <tr>
                  <td colspan="2">
                    &nbsp;
                  </td>
                  <td style="border: 1px solid #000000; padding: 2px;" width="107" height="5" align="center">
                    <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">Код</span>
                  </td>
                <tr>
                  <td colspan="2" align="right" style="padding: 0 1px 0 0;">
                     <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">Форма по ОКУД</span>
                  </td>
                  <td style="border: 2px solid #000000; border-bottom: 2px solid #000000; padding: 2px;" width="107" height="5" align="center">
                    <strong style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">0330212</strong>
                  </td>
                </tr>
                <tr>
                  <td colspan="2" align="right" style="padding: 0 1px 0 0;">
                     <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">по ОКПО</span>
                  </td>
                  <td style="border-left: 2px solid #000000;border-right: 2px solid #000000; border-bottom: 1px solid #000000; padding: 1px;" width="107" height="5" align="center">
                    <strong style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></strong>
                  </td>
                </tr>
                <tr>
                  <td colspan="2" align="right" style="padding: 0 1px 0 0;">
                    &nbsp;
                  </td>
                  <td style="border-left: 2px solid #000000;border-right: 2px solid #000000; border-bottom: 1px solid #000000; padding: 1px;" width="107" height="5" align="center">
                    <strong style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></strong>
                  </td>
                </tr>
                <tr>
                  <td colspan="2" align="right" style="padding: 0 1px 0 0;">
                     <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">Вид деятельности по ОКДП</span>
                  </td>
                  <td style="border-left: 2px solid #000000;border-right: 2px solid #000000; border-bottom: 1px solid #000000; padding: 1px;" width="107" height="5" align="center">
                    <strong style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></strong>
                  </td>
                </tr>
                <tr>
                  <td colspan="2" align="right" style="padding: 0 1px 0 0;">
                     <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">по ОКПО</span>
                  </td>
                  <td style="border-left: 2px solid #000000;border-right: 2px solid #000000; border-bottom: 1px solid #000000; padding: 1px;" width="107" height="5" align="center">
                    <strong style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></strong>
                  </td>
                </tr>
                <tr>
                  <td colspan="2" align="right" style="padding: 0 1px 0 0;">
                     <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">по ОКПО</span>
                  </td>
                  <td style="border-left: 2px solid #000000;border-right: 2px solid #000000; border-bottom: 1px solid #000000; padding: 1px;" width="107" height="5" align="center">
                    <strong style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></strong>
                  </td>
                </tr>
                <tr>
                  <td colspan="2" align="right" style="padding: 0 1px 0 0;">
                     <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">по ОКПО</span>
                  </td>
                  <td style="border-left: 2px solid #000000;border-right: 2px solid #000000; border-bottom: 1px solid #000000; padding: 1px;" width="107" height="5" align="center">
                    <strong style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></strong>
                  </td>
                </tr>
                <tr>
                  <td colspan="2" align="right" style="padding: 0 1px 0 0;">
                    &nbsp;
                  </td>
                  <td style="border-left: 2px solid #000000;border-right: 2px solid #000000; border-bottom: 1px solid #000000; padding: 1px;" width="107" height="5" align="center">
                    <strong style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></strong>
                  </td>
                </tr>
                <tr>
                  <td rowspan="5" style="padding: 0 10px 0 0;" valign="middle">
                    <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">Транспортная накладная</span>
                  </td>
                  <td style="border: 1px solid #000000; padding: 1px;" width="107" height="5" align="center">
                    <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">номер</span>
                  </td>
                  <td style="border-left: 2px solid #000000;border-right: 2px solid #000000; border-bottom: 1px solid #000000; padding: 1px;" width="107" height="5" align="center">
                    <strong style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></strong>
                  </td>
                </tr>
                <tr>
                  <td style="border: 1px solid #000000;padding: 2px;" width="107" height="5" align="center">
                    <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">дата</span>
                  </td>
                  <td style="border-left: 2px solid #000000;border-right: 2px solid #000000; border-bottom: 1px solid #000000; padding: 1px;" width="107" height="5" align="center">
                    <strong style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></strong>
                  </td>
                </tr>
                <tr>
                  <td style="border: 1px solid #000000;padding: 2px;" width="107" height="5" align="center">
                    <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">номер</span>
                  </td>
                  <td style="border-left: 2px solid #000000;border-right: 2px solid #000000; border-bottom: 1px solid #000000; padding: 1px;" width="107" height="5" align="center">
                    <strong style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></strong>
                  </td>
                </tr>
                <tr>
                  <td style="border: 1px solid #000000; padding: 2px;" width="107" height="5" align="center">
                    <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">дата</span>
                  </td>
                  <td style="border-left: 2px solid #000000;border-right: 2px solid #000000; border-bottom: 1px solid #000000; padding: 1px;" width="107" height="5" align="center">
                    <strong style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></strong>
                  </td>
                </tr>
                <tr>
                  <td style="border: 1px solid #000000; padding: 2px;" width="107" height="5" align="right">
                    <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">Вид операции</span>
                  </td>
                  <td style="border-left: 2px solid #000000;border-right: 2px solid #000000; border-bottom: 2px solid #000000; padding: 1px;" width="107" height="5" align="center">
                    <strong style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></strong>
                  </td>
                </tr>
              </table>
            </div>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 0 0 5px 0;">
        <tr>
          <td rowspan="2" style="padding: 1px; border: 1px solid #000000;" align="center">
            <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">
              Номер
              по
              порядку
            </span>
          </td>
          <td colspan="2" style="padding: 1px; border: 1px solid #000000;" align="center">
            <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">
            Товар
              </span>
          </td>
          <td colspan="2" style="padding: 1px; border: 1px solid #000000;" align="center">
            <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">
            Единица измерения
              </span>
          </td>
          <td rowspan="2" style="padding: 1px; border: 1px solid #000000;" align="center">
            <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">
            Вид
            упаков-
            ки
            </span>
          </td>
          <td colspan="2" style="padding: 1px; border: 1px solid #000000;" align="center">
            <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">
            Количество
            </span>
          </td>
          <td rowspan="2" style="padding: 1px; border: 1px solid #000000;" align="center">
            <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">
            Масса брутто
              </span>
          </td>
          <td rowspan="2" style="padding: 1px; border: 1px solid #000000;" width="15" align="center">
            <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">
            Количест-
            во
            (масса
            нетто)
              </span>
          </td>
          <td rowspan="2" style="padding: 1px; border: 1px solid #000000;" align="center">
            <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">
            Цена,
            руб.
            коп.
              </span>
          </td>
          <td rowspan="2" style="padding: 1px; border: 1px solid #000000;" align="center">
            <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">
            Сумма без учета
            НДС, руб. коп.
              </span>
          </td>
          <td colspan="2" style="padding: 1px; border: 1px solid #000000;" align="center">
            <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">
            НДС
              </span>
          </td>
          <td rowspan="2" style="padding: 1px; border: 1px solid #000000;" align="center">
            <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">
            Сумма с
            учетом
            НДС,
            руб. коп.
              </span>
          </td>
        </tr>
        <tr>
          <td style="padding: 1px; border: 1px solid #000000;" align="center">
            <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">наименование, характеристика, сорт,
            артикул товара
              </span>
          </td>
          <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">код</span></td>
          <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">наиме-нование</span></td>
          <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">код по ОКЕИ</span></td>
          <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">в одном месте</span></td>
          <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">мест, штук</span></td>
          <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">ставка, %</span></td>
          <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">сумма, руб. коп.</span></td>
        </tr>
        <tr>
          <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">1</span></td>
          <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">2</span></td>
          <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">3</span></td>
          <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">4</span></td>
          <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">5</span></td>
          <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">6</span></td>
          <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">7</span></td>
          <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">8</span></td>
          <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">9</span></td>
          <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">10</span></td>
          <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">11</span></td>
          <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">12</span></td>
          <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">13</span></td>
          <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">14</span></td>
          <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">15</span></td>
        </tr>
        <?php
        foreach ($data['perOrders'] as $cell => $item):
          $totalCount += $item['count'];
          $item['priceNds'] = $item['price'];

          if ($totalNds !== '-') {
            $marginNds = $item['priceNds'] * $ndsPercent / (100 + $ndsPercent);
            $item['priceNds'] -= $marginNds;
            $totalNds+=$item['count'] * $marginNds;
          }
          ?>
          <tr>
            <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"><?php echo ($cell + 1); ?></span></td>
            <td style="padding: 1px; border: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"><?php echo $item['name'] ?></span></td>
            <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"><?php echo $item['code'] ?></span></td>
            <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">шт.</span></td>
            <td style="padding: 1px; border: 1px solid #000000;" align="center"></td>
            <td style="padding: 1px; border: 1px solid #000000;" align="center"></td>
            <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">1</span></td>
            <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"><?php echo $item['count'] ?></span></td>
            <td style="padding: 1px; border: 1px solid #000000;" align="center"></td>
            <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"><?php echo $item['count'] ?></span></td>
            <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"><?php echo MG::numberFormat($item['priceNds'], '1 234,56') ?></span></td>
            <td style="padding: 1px; border: 1px solid #000000;" align="right"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"><?php echo MG::numberFormat(($item['priceNds'] * $item['count']), '1 234,56') ?></span></td>
            <td style="padding: 1px; border: 1px solid #000000;" align="center">
              <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">
  <?php echo (($data['propertyOrder']['nds'] >= 0 && is_numeric($data['propertyOrder']['nds'])) ? $data['propertyOrder']['nds'] . '%' : 'Без НДС') ?>
                </span>
            </td>
            <td style="padding: 1px; border: 1px solid #000000;" align="center"></td>
            <td style="padding: 1px; border: 1px solid #000000;" align="right"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"><?php echo MG::numberFormat(($item['price'] * $item['count']), '1 234,56') ?></span></td>
          </tr>
<?php endforeach; ?>
        <tr>
          <td colspan="7" align="right" style="padding: 2px;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">Итого</span></td>
          <td style="padding: 1px; border: 1px solid #000000;"></td>
          <td style="padding: 1px; border: 1px solid #000000;"></td>
          <td style="padding: 1px; border: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"><?php echo $totalCount ?></span></td>
          <td style="padding: 1px; border: 1px solid #000000;" align="right"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">X</span></td>
          <td style="padding: 1px; border: 1px solid #000000;" align="right"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"><?php echo MG::numberFormat(($data['order']['summ'] - $totalNds), '1 234,56') ?></span></td>
          <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">X</span></td>
          <td style="padding: 1px; border: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">X</span></td>
          <td style="padding: 1px; border: 1px solid #000000;" align="right"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"><?php echo MG::numberFormat($data['order']['summ'], '1 234,56') ?></span></td>
        </tr>
        <tr>
          <td colspan="7" align="right" style="padding: 2px;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">Всего по накладной</span></td>
          <td style="padding: 1px; border: 1px solid #000000;"></td>
          <td style="padding: 1px; border: 1px solid #000000;"></td>
          <td style="padding: 1px; border: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"><?php echo $totalCount ?></span></td>
          <td style="padding: 1px; border: 1px solid #000000;" align="right"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">X</span></td>
          <td style="padding: 1px; border: 1px solid #000000;" align="right"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"><?php echo MG::numberFormat(($data['order']['summ'] - $totalNds), '1 234,56') ?></span></td>
          <td style="padding: 1px; border: 1px solid #000000;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">X</span></td>
          <td style="padding: 1px; border: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">X</span></td>
          <td style="padding: 1px; border: 1px solid #000000;" align="right"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"><?php echo MG::numberFormat($data['order']['summ'], '1 234,56') ?></span></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table align="center" border="0" cellpadding="0" cellspacing="0">
        <tr>
          <td align="right" width="310" style="padding: 1px 10px 1px 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">Товарная накладная имеет приложение на</span></td>
          <td width="450" style="padding: 1px 10px 1px 0; border-bottom: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span></td>
          <td style="padding: 0 0 0 1px;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">листах</span></td>
        </tr>
        <tr>
          <td align="right" width="310" style="padding: 1px 10px 1px 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">и содержит</span></td>
          <td width="450" style="padding: 1px 10px 1px 0; border-bottom: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span></td>
          <td style="padding: 0 0 0 2px;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">порядковых номеров записей</span></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td width="450" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 10px;">(прописью)</span></td>
          <td>&nbsp;</td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table align="center" border="0" cellpadding="0" cellspacing="0" style="margin: 0 0 5px 0;">
        <tr>
          <td style="padding: 0 50px 0 0;" valign="bottom">
            <table align="center" border="0" cellpadding="0" cellspacing="0">
              <tr>
                <td align="right" width="310" style="padding: 1px 10px 1px 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">Всего мест</span></td>
                <td width="300" style="padding: 1px 10px 1px 0; border-bottom: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td width="300" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 10px;">(прописью)</span></td>
              </tr>
            </table>
          </td>
          <td>
            <table align="center" border="0" cellpadding="0" cellspacing="0">
              <tr>
                <td align="right" width="310" style="padding: 1px 10px 1px 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">Масса груза (нетто)</span></td>
                <td width="370" style="padding: 1px 10px 1px 0; border-bottom: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span></td>
                <td style="padding: 1px; border: 2px solid #000000;" height="5" width="130"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td width="300" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 10px;">(прописью)</span></td>
              </tr>
            </table>
            <table align="center" border="0" cellpadding="0" cellspacing="0" style="margin-top: -17px;">
              <tr>
                <td align="right" width="310" style="padding: 1px 10px 1px 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">Масса груза (брутто)</span></td>
                <td width="370" style="padding: 1px 10px 1px 0; border-bottom: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span></td>
                <td style="padding: 1px; border: 2px solid #000000;" height="5" width="130"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td width="300" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 10px;">(прописью)</span></td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table align="center" border="0" cellpadding="0" cellspacing="0" width="1260" style="margin: 0 0 5px 0;">
        <tr>
          <td style="padding: 0 10px 0 0;" width="620">
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td>
                  <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                      <td style="padding: 0 10px 0 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">Приложение (паспорта, сертификаты и т.п.) на</span></td>
                      <td width="230" style="padding: 1px 10px 1px 0; border-bottom: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span></td>
                      <td style="padding: 0 0 0 2px;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">листах</span></td>
                    </tr>
                    <tr>
                      <td style="padding: 0 10px 0 0;">&nbsp;</td>
                      <td width="230" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 10px;">(прописью)</span></td>
                      <td style="padding: 0 0 0 2px;">&nbsp;</td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td>
                  <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                      <td style="padding: 0 10px 0 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">Всего отпущено на сумму</span></td>
                      <td width="415" style="padding: 2px 10px 2px 0; border-bottom: 1px solid #000000;">
                        <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">
                          <?php
                          include('int2str.php');
                          $sumToWord = new int2str($data['order']['summ'], true);
                          echo $sumToWord->ucfirst($sumToWord->rub);
                          ?>
                        </span>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding: 0 10px 0 0;">&nbsp;</td>
                      <td width="415" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 10px;">(прописью)</span></td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td>
                  <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                      <td height="5" style="border-bottom: 1px solid #000000;"></td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td>
                  <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                      <td style="padding: 0 10px 0 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">Отпуск груза разрешил</span></td>
                      <td width="120" style="padding:1px; border-bottom: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span></td>
                      <td width="2">&nbsp;</td>
                      <td width="140" style="padding:1px; border-bottom: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span></td>
                      <td width="2">&nbsp;</td>
                      <td width="150" style="padding:1px; border-bottom: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span></td>
                    </tr>
                    <tr>
                      <td>&nbsp;</td>
                      <td width="120" align="center" style="padding: 0 0 1px 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 10px;">(должность)</span></td>
                      <td width="2" style="padding: 0 0 1px 0;">&nbsp;</td>
                      <td width="140" align="center" style="padding: 0 0 1px 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 10px;">(подпись)</span></td>
                      <td width="2" style="padding: 0 0 1px 0;">&nbsp;</td>
                      <td width="150" align="center" style="padding: 0 0 1px 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 10px;">(расшифровка подписи)</span></td>
                    </tr>
                    <tr>
                      <td style="padding: 0 10px 0 0;" colspan="2"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">Главный (старший) бухгалтер</span></td>
                      <td width="2">&nbsp;</td>
                      <td width="140" style="padding:1px; border-bottom: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span></td>
                      <td width="2">&nbsp;</td>
                      <td width="150" style="padding:1px; border-bottom: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span></td>
                    </tr>
                    <tr>
                      <td colspan="2" style="padding: 0 0 1px 0;">&nbsp;</td>
                      <td width="2" style="padding: 0 0 1px 0;">&nbsp;</td>
                      <td width="140" style="padding: 0 0 1px 0;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 10px;">(подпись)</span></td>
                      <td width="2" style="padding: 0 0 1px 0;">&nbsp;</td>
                      <td width="150" style="padding: 0 0 1px 0;" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 10px;">(расшифровка подписи)</span></td>
                    </tr>
                    <tr>
                      <td style="padding: 0 10px 0 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">Отпуск груза произвел</span></td>
                      <td width="120" style="padding:1px; border-bottom: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span></td>
                      <td width="2">&nbsp;</td>
                      <td width="140" style="padding:1px; border-bottom: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span></td>
                      <td width="2">&nbsp;</td>
                      <td width="150" style="padding:1px; border-bottom: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span></td>
                    </tr>
                    <tr>
                      <td>&nbsp;</td>
                      <td width="120" align="center" style="padding: 0 0 1px 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 10px;">(должность)</span></td>
                      <td width="2" style="padding: 0 0 1px 0;">&nbsp;</td>
                      <td width="140" align="center" style="padding: 0 0 1px 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 10px;">(подпись)</span></td>
                      <td width="2" style="padding: 0 0 1px 0;">&nbsp;</td>
                      <td width="150" align="center" style="padding: 0 0 1px 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 10px;">(расшифровка подписи)</span></td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td>
                  <table align="right" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                      <td style="padding: 0 1px 0 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">М.П.</span></td>
                      <td style="padding: 0 1px 0 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">«</span></td>
                      <td width="30" align="center" style="border-bottom: 1px solid #000000;">
                        <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"><?php echo $arAddDate[0] ?></span>
                      </td>
                      <td style="padding: 0 1px 0 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">»</span></td>
                      <td width="60" align="center" style="border-bottom: 1px solid #000000;">
                        <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"><?php echo $arAddDate[1] ?></span>
                      </td>
                      <td width="2">&nbsp;</td>
                      <td width="60" align="center" style="border-bottom: 1px solid #000000;">
                        <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"><?php echo $arAddDate[2] ?></span>
                      </td>
                      <td width="2">&nbsp;</td>
                      <td width="40" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">года</span></td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
          <td style="padding: 0 0 0 10px; border-left: 1px solid #000000;" width="620">
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 0 0 5px 0;">
              <tr>
                <td>
                  <table align="center" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                      <td style="padding: 0 10px 0 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">По доверенности №</span></td>
                      <td width="20">&nbsp;</td>
                      <td width="180" style="padding: 1px 10px 1px 0; border-bottom: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span></td>
                      <td width="20">&nbsp;</td>
                      <td style="padding: 1px 10px 1px 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">От</span></td>
                      <td style="padding: 0 1px 0 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">«</span></td>
                      <td width="30" align="center" style="border-bottom: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span></td>
                      <td style="padding: 0 1px 0 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">»</span></td>
                      <td width="80" style="padding: 1px 10px 1px 0; border-bottom: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span></td>
                      <td width="35" style="padding: 1px 10px 1px 0; border-bottom: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span></td>
                      <td width="40" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">года,</span></td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td>
                  <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                      <td style="padding: 0 10px 0 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">выданной</span></td>
                      <td width="520" height="5" style="padding: 1px 10px 1px 0; border-bottom: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span></td>
                    </tr>
                    <tr>
                      <td style="padding: 0 10px 0 0;">&nbsp;</td>
                      <td width="520" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 10px;">(кем, кому (организация, должность, фамилия, и., о.))</span></td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td>
                  <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                      <td height="5" style="border-bottom: 1px solid #000000;"></td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td>
                  <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                      <td height="5" style="border-bottom: 1px solid #000000;"></td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td>
                  <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                      <td colspan="6">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                          <tr>
                            <td style="padding: 0 10px 0 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">Груз принял</span></td>
                            <td width="95" style="padding:1px; border-bottom: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span></td>
                            <td width="2">&nbsp;</td>
                            <td width="110" style="padding:1px; border-bottom: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span></td>
                            <td width="2">&nbsp;</td>
                            <td width="270" style="padding:1px; border-bottom: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span></td>
                          </tr>
                          <tr>
                            <td>&nbsp;</td>
                            <td width="95" align="center" style="padding: 0 0 1px 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 10px;">(должность)</span></td>
                            <td width="2" style="padding: 0 0 1px 0;">&nbsp;</td>
                            <td width="110" align="center" style="padding: 0 0 1px 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 10px;">(подпись)</span></td>
                            <td width="2" style="padding: 0 0 1px 0;">&nbsp;</td>
                            <td width="270" align="center" style="padding: 0 0 1px 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 10px;">(расшифровка подписи)</span></td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding: 0 10px 0 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">Груз получил грузополучатель</span></td>
                      <td width="110" style="padding:1px; border-bottom: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span></td>
                      <td width="2">&nbsp;</td>
                      <td width="130" style="padding:1px; border-bottom: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span></td>
                      <td width="2">&nbsp;</td>
                      <td width="150" style="padding:1px; border-bottom: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span></td>
                    </tr>
                    <tr>
                      <td>&nbsp;</td>
                      <td width="110" align="center" style="padding: 0 0 1px 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 10px;">(должность)</span></td>
                      <td width="2" style="padding: 0 0 1px 0;">&nbsp;</td>
                      <td width="130" align="center" style="padding: 0 0 1px 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 10px;">(подпись)</span></td>
                      <td width="2" style="padding: 0 0 1px 0;">&nbsp;</td>
                      <td width="150" align="center" style="padding: 0 0 1px 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 10px;">(расшифровка подписи)</span></td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td>
                  <table align="right" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                      <td style="padding: 0 1px 0 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">М.П.</span></td>
                      <td style="padding: 0 1px 0 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">«</span></td>
                      <td width="30" align="center" style="border-bottom: 1px solid #000000;">
                        <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span>
                      </td>
                      <td style="padding: 0 1px 0 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">»</span></td>
                      <td width="100" align="center" style="border-bottom: 1px solid #000000;">
                        <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span>
                      </td>
                      <td width="2">&nbsp;</td>
                      <td width="60" align="center" style="border-bottom: 1px solid #000000;">
                        <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;"></span>
                      </td>
                      <td width="2">&nbsp;</td>
                      <td width="40" align="center"><span style="font-family: Arial, Verdana, sans-serif; font-size: 11px;">года</span></td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>