
<style type="text/css">
  * 	{
    padding: 0;
    margin: 0;
    font-family: Arial, Verdana, sans-serif;
  }

  body {
    font-size: 12px;
  }

  table{
    border-collapse: collapse;
  }
</style>
<?php
$addDate = MG::dateConvert($data['order']['add_date'], true);
$arAddDate = explode(' ', $addDate);
$ndsPercent = is_numeric($data['propertyOrder']['nds']) ? $data['propertyOrder']['nds'] : 0;
$totalNds = 0;
$totalWithoutNds = 0;

if ($ndsPercent === 0) {
  $totalNds = '-';
}
?>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 20px auto 0;">
  <tr>
    <td>
      <span style="font-family: Arial, Verdana, sans-serif; font-size: 26px; line-height: 26px; font-weight: bold;">Счет-фактура № <?php echo $data['order']['id'] ?> от <?php echo date("d.m.Y", strtotime($data['order']['add_date'])) ?></span>
      <br/>
      <br/>
      <br/>
    </td>
  </tr>
  <tr>
    <td style="padding: 0 0 7px 0;">
      <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
        Продавец: <?php echo $data['propertyOrder']['nameyur'] ?>
      </span>
    </td>
  </tr>
  <tr>
    <td style="padding: 0 0 7px 0;">
      <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
        Адрес: <?php echo $data['propertyOrder']['adress'] ?>
      </span>
    </td>
  </tr>
  <tr>
    <td style="padding: 0 0 7px 0;">
      <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
        ИНН: <?php echo $data['propertyOrder']['inn'] ?>
      </span>
    </td>
  </tr>
  <tr>
    <td style="padding: 0 0 7px 0;">
      <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
        Грузотправитель и его адрес: --
      </span>
    </td>
  </tr>
  <tr>
    <td style="padding: 0 0 7px 0;">
      <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
        Грузополучатель и его адрес: --
      </span>
    </td>
  </tr>
  <tr>
    <td style="padding: 0 0 7px 0;">
      <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
        К платежно-расчетному документу № <?php echo $data['order']['number'] ?> от <?php echo date("d.m.Y", strtotime($data['order']['add_date'])) ?>
      </span>
    </td>
  </tr>
  <tr>
    <td style="padding: 0 0 7px 0;">
      <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
        Покупатель: <?php echo $data['order']['name_buyer'] ?>
      </span>
    </td>
  </tr>
   <tr>
    <td style="padding: 0 0 7px 0;">
      <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
        Покупатель: <?php echo $data['customerInfo'] ?>
      </span>
    </td>
  </tr>
  <tr>
    <td style="padding: 0 0 7px 0;">
      <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
        Адрес: <?php echo $data['order']['address'] ?>
      </span>
    </td>
  </tr>
  <tr>
    <td style="padding: 0 0 7px 0;">
      <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
        Валюта: наименование, код Российский рубль 643
      </span>
    </td>
  </tr>
  <tr>
    <td>
      <br/>
      <table align="center" border="0" cellpadding="0" cellspacing="0" width="1210" style="border: 1px solid #000000; border-collapse: collapse;">
        <tr>
          <td rowspan="2" style="padding: 5px; border: 1px solid #000000;">
            <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
              Наименование
              товара
              (описание
              выполненных
              работ,
              оказанных
              услуг),
              имущественного
              права
            </span>
          </td>
          <td colspan="2" style="padding: 5px; border: 1px solid #000000;">
            <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
              Единицы измерения
            </span>
          </td>
          <td rowspan="2" style="padding: 5px; border: 1px solid #000000;">
            <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
              Количество объем
            </span>
          </td>
          <td rowspan="2" style="padding: 5px; border: 1px solid #000000;">
            <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
              Цена
              (тариф)
              за
              единицу
              измерения
            </span>
          </td>
          <td rowspan="2" style="padding: 5px; border: 1px solid #000000;">
            <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
              Стоимость
              товаров (работ,
              услуг)
              имущественных
              прав, без
              налога —
            </span>
          </td>
          <td rowspan="2" style="padding: 5px; border: 1px solid #000000;">
            <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
              В том
              числе
              сумма
              акциза
            </span>
          </td>
          <td rowspan="2" style="padding: 5px; border: 1px solid #000000;">
            <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
              Налоговая ставка
            </span>
          </td>
          <td rowspan="2" style="padding: 5px; border: 1px solid #000000;">
            <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
              Сумма налога
              предъявляемая
              полкупателю
            </span>
          </td>
          <td rowspan="2" style="padding: 5px; border: 1px solid #000000;">
            <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
              Стоимость
              товаров (работ,
              услуг)
              имущественных
              прав с налогом
              — всего
            </span>
          </td>
          <td colspan="2" style="padding: 5px; border: 1px solid #000000;">
            <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
              Страна происхождения товара
            </span>
          </td>
          <td rowspan="2" style="padding: 5px; border: 1px solid #000000;">
            <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
              Номер
              таможенной
              декларации
            </span>
          </td>
        </tr>
        <tr>
          <td style="padding: 5px; border: 1px solid #000000;">
            <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
              Код
            </span>
          </td>
          <td style="padding: 5px; border: 1px solid #000000;">
            <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
              Условное
              обозначение
              (национальное)
            </span>
          </td>
          <td style="padding: 5px; border: 1px solid #000000;">
            <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
              Цифровой код
            </span>
          </td>
          <td style="padding: 5px; border: 1px solid #000000;">
            <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
              Краткое наименование
            </span>
          </td>
        </tr>
        <tr>
          <td style="padding: 5px; border: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">1</span></td>
          <td style="padding: 5px; border: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">2</span></td>
          <td style="padding: 5px; border: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">2a</span></td>
          <td style="padding: 5px; border: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">3</span></td>
          <td style="padding: 5px; border: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">4</span></td>
          <td style="padding: 5px; border: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">5</span></td>
          <td style="padding: 5px; border: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">6</span></td>
          <td style="padding: 5px; border: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">7</span></td>
          <td style="padding: 5px; border: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">8</span></td>
          <td style="padding: 5px; border: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">9</span></td>
          <td style="padding: 5px; border: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">10</span></td>
          <td style="padding: 5px; border: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">10a</span></td>
          <td style="padding: 5px; border: 1px solid #000000;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">11</span></td>
        </tr>
        <?php
        foreach ($data['perOrders'] as $item):
          $item['priceNds'] = $item['price'];

          if ($totalNds !== '-') {
            $marginNds = $item['priceNds'] * $ndsPercent / (100 + $ndsPercent);
            $item['priceNds'] -= $marginNds;
            $totalNds+=$item['count'] * $marginNds;
          }
          ?>
          <tr>
            <td style="padding: 5px; border: 1px solid #000000;">
              <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
                <?php echo $item['name'] ?>
              </span>
            </td>
            <td style="padding: 5px; border: 1px solid #000000;"></td>
            <td style="padding: 5px; border: 1px solid #000000;">шт.</td>
            <td style="padding: 5px; border: 1px solid #000000;">
              <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
                <?php echo $item['count'] ?>
              </span>
            </td>
            <td style="padding: 5px; border: 1px solid #000000;">
              <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
                <?php echo MG::numberFormat($item['priceNds'], '1 234,56') ?>
              </span>
            </td>
            <td style="padding: 5px; border: 1px solid #000000;">
              <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
                <?php echo MG::numberFormat(($item['priceNds'] * $item['count']), '1 234,56') ?>
              </span>
            </td>
            <td style="padding: 5px; border: 1px solid #000000;">без акциза</td>
            <td style="padding: 5px; border: 1px solid #000000;">
              <?php echo (($data['propertyOrder']['nds'] >= 0 && is_numeric($data['propertyOrder']['nds'])) ? $data['propertyOrder']['nds'] . '%' : 'Без НДС') ?>
            </td>
            <td style="padding: 5px; border: 1px solid #000000;"></td>
            <td style="padding: 5px; border: 1px solid #000000;">
              <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
                <?php echo MG::numberFormat(($item['price'] * $item['count']), '1 234,56') ?>
              </span>
            </td>
            <td style="padding: 5px; border: 1px solid #000000;"></td>
            <td style="padding: 5px; border: 1px solid #000000;">
              <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;"></span>
            </td>
            <td style="padding: 5px; border: 1px solid #000000;"></td>
          </tr>
        <?php endforeach; ?>
        <tr>
          <td style="padding: 5px; border: 1px solid #000000;" colspan="5">
            <strong style="font-family: Arial, Verdana, sans-serif; font-size: 18px; line-height: 18px;">К оплате:</strong>
          </td>
          <td style="padding: 5px; border: 1px solid #000000;">
            <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
              <?php echo MG::numberFormat(($data['order']['summ'] - $totalNds), '1 234,56') ?>
            </span>
          </td>
          <td style="padding: 5px; border: 1px solid #000000;" colspan="2"><span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">X</span></td>
          <td style="padding: 5px; border: 1px solid #000000;"></td>
          <td style="padding: 5px; border: 1px solid #000000;">
            <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;">
              <?php echo MG::numberFormat($data['order']['summ'], '1 234,56') ?>
            </span>
          </td>
          <td style="padding: 5px; border: 1px solid #000000;" colspan="3"></td>
        </tr>
      </table>
      <br/>
    </td>
  </tr>
  <tr>
    <td>
      <table align="center" border="0" cellpadding="0" cellspacing="0">
        <tr>
          <td width="50%">
            <table align="center" border="0" cellpadding="0" cellspacing="0">
              <tr>
                <td>
                  <table align="center" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                      <td style="padding: 0 20px 0 0;">
                        <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 20px;">Руководитель организации или иное уполнопоченное лицо:</span>
                      </td>
                      <td width="130" style="border-bottom: 1px solid #000000;">

                      </td>
                      <td style="padding: 0 10px 0 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;"></span></td>
                      <td width="230" style="border-bottom: 1px solid #000000;">

                      </td>
                    </tr>
                    <tr>
                      <td style="padding: 0 20px 0 0;">
                        <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 20px;">&nbsp;</span>
                      </td>
                      <td width="130" align="center">
                        <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px; line-height: 12px;">(подпись)</span>
                      </td>
                      <td style="padding: 0 10px 0 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;"></span></td>
                      <td width="230" align="center">
                        <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px; line-height: 12px;">(Ф.И.О.)</span>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="4" height="15">
                        &nbsp;
                      </td>
                    </tr>
                    <tr>
                      <td style="padding: 0 20px 0 0;">
                        <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 20px;">Индивидуальный предприниматель:</span>
                      </td>
                      <td width="130" style="border-bottom: 1px solid #000000;">

                      </td>
                      <td style="padding: 0 10px 0 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;"></span></td>
                      <td width="230" style="border-bottom: 1px solid #000000;">

                      </td>
                    </tr>
                    <tr>
                      <td style="padding: 0 20px 0 0;">
                        <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 20px;">&nbsp;</span>
                      </td>
                      <td width="130" align="center">
                        <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px; line-height: 12px;">(подпись)</span>
                      </td>
                      <td style="padding: 0 10px 0 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;"></span></td>
                      <td width="230" align="center">
                        <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px; line-height: 12px;">(Ф.И.О.)</span>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
          <td style="padding: 0 0 0 10px;">
            <table align="center" border="0" cellpadding="0" cellspacing="0">
              <tr>
                <td>
                  <table align="center" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                      <td style="padding: 0 20px 0 0;">
                        <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 20px;">Главный бухгалтер или иное уполнопоченное лицо:</span>
                      </td>
                      <td width="130" style="border-bottom: 1px solid #000000;">

                      </td>
                      <td style="padding: 0 10px 0 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;"></span></td>
                      <td width="230" style="border-bottom: 1px solid #000000;">

                      </td>
                    </tr>
                    <tr>
                      <td style="padding: 0 20px 0 0;">
                        <span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 20px;">&nbsp;</span>
                      </td>
                      <td width="130" align="center">
                        <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px; line-height: 12px;">(подпись)</span>
                      </td>
                      <td style="padding: 0 10px 0 0;"><span style="font-family: Arial, Verdana, sans-serif; font-size: 12px; line-height: 16px;"></span></td>
                      <td width="230" align="center">
                        <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px; line-height: 12px;">(Ф.И.О.)</span>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="4" height="15">
                        &nbsp;
                      </td>
                    </tr>
                    <tr>
                      <td colspan="4" style="border-bottom: 1px solid #000000;" height="20">
                        &nbsp;
                      </td>
                    </tr>
                    <tr>
                      <td colspan="4" align="center">
                        <span style="font-family: Arial, Verdana, sans-serif; font-size: 11px; line-height: 12px;">(реквизиты свидетельства о государственной <br/> регистрации индивидуального предпринимателя)</span>
                      </td>
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