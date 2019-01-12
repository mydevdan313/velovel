<div class="blanks-wrapper">
    <div class="table-blank">
        <table style="border-collapse: collapse; width: 100%; font-size: 12px; margin: 0;" cellpadding="0" cellspacing="0">
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
                <td style="border: 4px solid #000000; padding: 5px;" width="600" valign="top">
                    <div style="text-align: center;">
                        <?php echo $data['name'] ?><br/>
                        <span style="font-size: 12px; margin: 0;">(наименование получателя)</span>
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
                <td style="border: 4px solid #000000; padding: 5px;" width="600" valign="top">
                    <div style="text-align: center;">
                        <?php echo $data['name'] ?><br/>
                        <span style="font-size: 12px; margin: 0;">(наименование получателя)</span>
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