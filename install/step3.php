<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta name="keywords" content="#"/>
    <meta name="description" content="#"/>
    <link href="<?php echo SITE ?>/install/css/style.css" rel="stylesheet" type="text/css"/>
    <link href="#" rel="shortcut icon" type="image/x-icon"/>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
    <title>Moguta.CMS™ | Установка</title>
</head>
<body>

<div class="install-body finish">
    <div class="install-logo">
        <img src="<?php echo SITE ?>/install/images/logo-black.svg" width="275" height="55" alt=""/>
    </div>

    <div class="center-wrapper step4">
        <div class="widget-table-title clearfix text-center">
            <h3><span class="arrow-icon"></span> Поздравляем! Установка Moguta.CMS™ успешно завершена</h3>
        </div>
        <div class="install-text success-install">
            <h2><img src="<?php echo SITE ?>/install/images/success.png" alt=""/>Вы успешно установили интернет-магазин.</h2>

            <div class="buttons-holder">
                <a class="dell-install green-btn"
                   href="<?php echo (!empty($_SERVER['HTTPS'])&& strtolower($_SERVER['HTTPS']) !== 'off'  ? 'https://' : 'http://').$_SERVER['SERVER_NAME'] . str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']) . '/mg-admin' ?>">
                    Перейти в панель администратора </a>
                <a class="dell-install blue-btn" href="<?php echo SITE ?>">Перейти на сайт</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>