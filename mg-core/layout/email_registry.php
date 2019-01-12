<h1 style="margin: 0 0 10px 0; font-size: 16px;padding: 0;">Здравствуйте!</h1>
<p style="padding: 0;margin: 10px 0;font-size: 12px;">
    Вы получили данное письмо так как зарегистрировались на сайте
    <strong><?php echo $data['siteName']?> </strong> с логином <strong><?php echo $data['userEmail']?></strong>
</p>
<?php if (MG::getSetting('confirmRegistration') == 'true') :?> 
<p style="padding: 0;margin: 10px 0;font-size: 12px;">
    Для активации пользователя и возможности пользоваться личным кабинетом пройдите по ссылке:
</p>
<div style="margin: 0;padding: 10px;background: #FFF5B5; font-weight: bold; text-align: center;">
    <?php echo $data['link']?>
</div>
<?php else: ?>
<p style="padding: 0;margin: 10px 0;font-size: 12px;">
    Для авторизации на сайте пройдите по ссылке: <a href="<?php  echo SITE ?>/enter">Вход в личный кабинет</a>
</p>
<?php endif;?>
<p style="padding: 0;margin: 10px 0;font-size: 10px; color: #555; font-weight: bold;">
    Отвечать на данное сообщение не нужно.
</p>