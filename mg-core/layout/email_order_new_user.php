<table bgcolor="#FFFFFF" cellspacing="0" cellpadding="10" border="0" width="675">
    <tbody>
    <tr>
        <td valign="top">
            <h1 style="margin: 0 0 10px 0; font-size: 16px;padding: 0;">
                Здравствуйте, <?php echo $data['fio'] ?>!
            </h1>
            <div style="font-size:12px;line-height:16px;margin:0;">
                <br>
                Мы создали для вас <a href="<?php echo SITE ?>/personal" style="color:#1E7EC8;" target="_blank">личный
                    кабинет</a>, чтобы вы могли следить за статусом заказа, а также скачивать оплаченные электронные
                товары.
                <br><br>
                <b>Ваш логин:</b> <?php echo $data['email']; ?>
                <br>
                <b>Ваш пароль:</b> <?php echo $data['pass']; ?>
            </div>
        </td>
    </tr>
    </tbody>
</table>