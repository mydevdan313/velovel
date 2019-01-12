<html xmlns="http://www.w3.org/1999/xhtml">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Квитанция Сбербанка</title>
    <style type="text/css">
        * {
            padding: 0;
            margin: 0;
        }

        body {
            font-size: 12px;
        }

        .clear {
            clear: both;
        }

        #blank {
            width: 792px;
            border: 4px solid #000;
            margin: 0 auto;
        }

        .blanks-wrapper {
            width: 800px;
            margin: 0 auto;
            padding: 20px 0;
        }

        #control-panel {
            height: 40px;
        }

        #control-panel a span {
            display: inline-block;
        }

        #control-panel a.btn-personal span {
            padding: 4px 10px 4px 27px;
            background: url(<?php echo SITE ?>/mg-admin/design/images/icons/go-back-icon.png) 6px 4px no-repeat;
        }

        #control-panel a.btn-print span {
            padding: 4px 10px 4px 27px;
            background: url(<?php echo SITE ?>/mg-admin/design/images/icons/print-icon.png) 6px 4px no-repeat;
        }

        #control-panel a {
            display: block;
            background: #FCFCFC; /* Old browsers */
            background: -moz-linear-gradient(top, #FCFCFC 0%, #E8E8E8 100%); /* FF3.6+ */
            background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #FCFCFC), color-stop(100%, #E8E8E8)); /* Chrome,Safari4+ */
            background: -webkit-linear-gradient(top, #FCFCFC 0%, #E8E8E8 100%); /* Chrome10+,Safari5.1+ */
            background: -o-linear-gradient(top, #FCFCFC 0%, #E8E8E8 100%); /* Opera11.10+ */
            background: -ms-linear-gradient(top, #FCFCFC 0%, #E8E8E8 100%); /* IE10+ */
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#FCFCFC', endColorstr='#E8E8E8', GradientType=0); /* IE6-9 */
            background: linear-gradient(top, #FCFCFC 0%, #E8E8E8 100%); /* W3C */
            border: 1px solid #D3D3D3;
            font-family: Tahoma, Verdana, sans-serif;
            font-size: 12px;
            border-radius: 5px;
            -moz-border-radius: 5px;
            -webkit-border-radius: 5px;
            color: #333;
            text-decoration: none;
        }

        #control-panel a:hover {
            background: #eeeeee; /* Old browsers */
            background: -moz-linear-gradient(top, #eeeeee 0%, #eeeeee 100%); /* FF3.6+ */
            background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #eeeeee), color-stop(100%, #eeeeee)); /* Chrome,Safari4+ */
            background: -webkit-linear-gradient(top, #eeeeee 0%, #eeeeee 100%); /* Chrome10+,Safari5.1+ */
            background: -o-linear-gradient(top, #eeeeee 0%, #eeeeee 100%); /* Opera11.10+ */
            background: -ms-linear-gradient(top, #eeeeee 0%, #eeeeee 100%); /* IE10+ */
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#eeeeee', endColorstr='#eeeeee', GradientType=0); /* IE6-9 */
            background: linear-gradient(top, #eeeeee 0%, #eeeeee 100%);
        }

        #control-panel a:active {
            -moz-box-shadow: 0 0 4px 2px rgba(0, 0, 0, .3) inset;
            -webkit-box-shadow: 0 0 4px 2px rgba(0, 0, 0, .3) inset;
            box-shadow: 0 0 4px 2px #CFCFCF inset;
            outline: none;
        }

        #control-panel .btn-print {
            float: left;
        }

        #control-panel .btn-personal {
            float: right;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
<div class="blanks-wrapper">
    <div id="control-panel" class="no-print">
        <a href="javascript:vodi(0);" onclick="window.print();" class="no-print btn-print"><span>Распечатать</span></a>
        <a href="<?php echo SITE ?>/personal" class="no-print btn-personal"><span>Вернуться в личный кабинет</span></a>
    </div>
    <div class="table-blank">
        <table style="border-collapse: collapse; width: 100%; font-size: 12px;" cellpadding="0" cellspacing="0">
            <tr>
                <td style="border: 4px solid #000000; text-align: center; padding: 0 0 10px 0;" width="190" valign="top">
                    <br/>
                    <br/>
                    <strong>Извещение</strong>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <strong>Кассир</strong>
                    <br/>
                </td>
                <td style="border: 4px solid #000000; padding: 10px;" width="600" valign="top">
                    <div style="text-align: center;">
                        <?php echo $data['name'] ?><br/>
                        <span style="font-size: 12px; margin: 0;">(наименование получателя)</span>
                        <br/>
                    </div>
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="width: 50%; text-align: center; padding: 5px 10px;" valign="top">
                                <?php echo $data['inn']; ?> <br/>
                                <span style="font-size: 12px; margin: 0;">(ИНН получателя платежа)</span>
                                <br/>
                            </td>
                            <td style="width: 50%; text-align: center; padding: 5px 10px;" valign="top">
                                <?php echo $data['nsp']; ?><br/>
                                <span style="font-size: 12px; margin: 0;">(номер счета получателя платежа)</span>
                                <br/>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 50%; text-align: center; padding: 5px 10px;" valign="top">
                                <?php echo $data['bank']; ?><br/>
                                <span style="font-size: 12px; margin: 0;">(наименование банка получателя)</span>
                                <br/>
                            </td>
                            <td style="width: 50%; text-align: center; padding: 5px 10px;" valign="top">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td width="20%">БИК</td>
                                        <td width="80%"><?php echo $data['bik']; ?></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 50%; text-align: left; padding: 5px 10px;" valign="top">
                                Номер кор./сч банка получателя платежа
                            </td>
                            <td style="width: 50%; text-align: center; padding: 5px 10px;" valign="top">
                                <?php echo $data['ncsp']; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 50%; text-align: center; padding: 5px 10px;" valign="top">
                                <?php echo $data['appointment']; ?> <br/>
                                <span style="font-size: 12px; margin: 0;">(наименование платежа)</span>
                                <br/>
                            </td>
                            <td style="width: 50%; text-align: center; padding: 5px 10px;" valign="top">
                                <?php echo $data['nls']; ?> <br/>
                                <span style="font-size: 12px; margin: 0;">(номер лицевого счета (код) плательщика)</span>
                                <br/>
                            </td>
                        </tr>
                        <tr>
                            <td style=" padding: 5px 10px;" valign="top" colspan="2">
                                <table cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding: 0 10px 0 0;">Ф.И.О. плательщика:</td>
                                        <td><?php echo $data['payer']; ?></td>
                                    </tr>
                                </table>
                                <br/>
                            </td>
                        </tr>
                        <tr>
                            <td style=" padding: 5px 10px;" valign="top" colspan="2">
                                <table cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding: 0 10px 0 0;">Адрес плательщика:</td>
                                        <td style="width: 400px;"><?php echo $data['addrPayer']; ?></td>
                                    </tr>
                                </table>
                                <br/>
                            </td>
                        </tr>
                        <tr>
                            <td style=" padding: 5px 10px;" valign="top" colspan="2">
                                 <table cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding: 0 10px 0 0;">
                                            <span style="font-size: 0;"><?php echo $currency = MG::getSetting('currency'); ?></span>
                                            Сумма платежа:
                                        </td>
                                        <td style="width: 400px;"><?php echo $data['sRub']; ?> <?php echo $currency; ?> <?php echo $data['sKop']; ?>
                                            коп.</td>
                                    </tr>
                                </table>
                                <br/>
                            </td>
                        </tr>
                        <tr>
                            <td style=" padding: 5px 10px;" valign="top" colspan="2">
                                 <table cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding: 0 10px 0 0;">
                                            Сумма платы за
                                            услуги
                                        </td>
                                        <td style="width: 350px;"><?php echo $data['uRub']; ?> <?php echo $currency; ?> <?php echo $data['uKop'] ?>
                                            коп.</td>
                                    </tr>
                                </table>
                                <br/>
                            </td>
                        </tr>
                        <tr>
                            <td style=" padding: 5px 10px;" valign="top" colspan="2">
                                Итого: <?php echo $data['rub']; ?> <?php echo $currency; ?> <?php echo $data['kop']; ?> коп.
                                <?php echo $data['day']; ?>.<?php echo $data['month']; ?>.<?php echo date('Y'); ?>
                                г.
                                <br/>
                            </td>
                        </tr>
                        <tr>
                            <td style=" padding: 5px 10px;" valign="top" colspan="2">
                                С условиями приема указанной в платежном документе суммы, в т.ч. с суммой взимаемой платы
                                банка ознакомлен и согласен.
                                <br/>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <table style="border-collapse: collapse; width: 100%; font-size: 12px; margin: -4px 0 0 0;" cellpadding="0" cellspacing="0">
            <tr>
                <td style="border: 4px solid #000000; text-align: center; padding: 0 0 10px 0;" width="190" valign="top">
                    <br/>
                    <br/>
                    <strong>Квитанция
                    </strong>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                    <strong>Кассир</strong>
                    <br/>
                </td>
                <td style="border: 4px solid #000000; padding: 10px;" width="600" valign="top">
                    <div style="text-align: center;">
                        <?php echo $data['name'] ?><br/>
                        <span style="font-size: 12px; margin: 0;">(наименование получателя)</span>
                        <br/>
                    </div>
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="width: 50%; text-align: center; padding: 5px 10px;" valign="top">
                                <?php echo $data['inn']; ?> <br/>
                                <span style="font-size: 12px; margin: 0;">(ИНН получателя платежа)</span>
                                <br/>
                            </td>
                            <td style="width: 50%; text-align: center; padding: 5px 10px;" valign="top">
                                <?php echo $data['nsp']; ?><br/>
                                <span style="font-size: 12px; margin: 0;">(номер счета получателя платежа)</span>
                                <br/>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 50%; text-align: center; padding: 5px 10px;" valign="top">
                                <?php echo $data['bank']; ?><br/>
                                <span style="font-size: 12px; margin: 0;">(наименование банка получателя)</span>
                                <br/>
                            </td>
                            <td style="width: 50%; text-align: center; padding: 5px 10px;" valign="top">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td width="20%">БИК</td>
                                        <td width="80%"><?php echo $data['bik']; ?></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 50%; text-align: left; padding: 5px 10px;" valign="top">
                                Номер кор./сч банка получателя платежа
                            </td>
                            <td style="width: 50%; text-align: center; padding: 5px 10px;" valign="top">
                                <?php echo $data['ncsp']; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 50%; text-align: center; padding: 5px 10px;" valign="top">
                                <?php echo $data['appointment']; ?> <br/>
                                <span style="font-size: 12px; margin: 0;">(наименование платежа)</span>
                                <br/>
                            </td>
                            <td style="width: 50%; text-align: center; padding: 5px 10px;" valign="top">
                                <?php echo $data['nls']; ?> <br/>
                                <span style="font-size: 12px; margin: 0;">(номер лицевого счета (код) плательщика)</span>
                                <br/>
                            </td>
                        </tr>
                        <tr>
                            <td style=" padding: 5px 10px;" valign="top" colspan="2">
                                <table cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding: 0 10px 0 0;">Ф.И.О. плательщика:</td>
                                        <td><?php echo $data['payer']; ?></td>
                                    </tr>
                                </table>
                                <br/>
                            </td>
                        </tr>
                        <tr>
                            <td style=" padding: 5px 10px;" valign="top" colspan="2">
                                <table cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding: 0 10px 0 0;">Адрес плательщика:</td>
                                        <td style="width: 400px;"><?php echo $data['addrPayer']; ?></td>
                                    </tr>
                                </table>
                                <br/>
                            </td>
                        </tr>
                        <tr>
                            <td style=" padding: 5px 10px;" valign="top" colspan="2">
                                 <table cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding: 0 10px 0 0;">
                                            <span style="font-size: 0;"><?php echo $currency = MG::getSetting('currency'); ?></span>
                                            Сумма платежа:
                                        </td>
                                        <td style="width: 400px;"><?php echo $data['sRub']; ?> <?php echo $currency; ?> <?php echo $data['sKop']; ?>
                                            коп.</td>
                                    </tr>
                                </table>
                                <br/>
                            </td>
                        </tr>
                        <tr>
                            <td style=" padding: 5px 10px;" valign="top" colspan="2">
                                 <table cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding: 0 10px 0 0;">
                                            Сумма платы за
                                            услуги
                                        </td>
                                        <td style="width: 350px;"><?php echo $data['uRub']; ?> <?php echo $currency; ?> <?php echo $data['uKop'] ?>
                                            коп.</td>
                                    </tr>
                                </table>
                                <br/>
                            </td>
                        </tr>
                        <tr>
                            <td style=" padding: 5px 10px;" valign="top" colspan="2">
                                Итого: <?php echo $data['rub']; ?> <?php echo $currency; ?> <?php echo $data['kop']; ?> коп.
                                <?php echo $data['day']; ?>.<?php echo $data['month']; ?>.<?php echo date('Y'); ?>
                                г.
                                <br/>
                            </td>
                        </tr>
                        <tr>
                            <td style=" padding: 5px 10px;" valign="top" colspan="2">
                                С условиями приема указанной в платежном документе суммы, в т.ч. с суммой взимаемой платы
                                банка ознакомлен и согласен.
                                <br/>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</div>
</body>
</html>