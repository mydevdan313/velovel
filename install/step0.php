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
<script type="text/javascript">
    $(document).ready(function () {
        var goButton = $('.agree-button').prop('disabled', 'disabled');
        $('#agree').removeAttr('checked');
        $('.agree-button').addClass('opacity');
        $('#agree').change(function () {
            var checkBox = $(this).prop('checked');
            if (checkBox) {
                $('.agree-button').removeClass('opacity');
            }
            else {
                $('.agree-button').addClass('opacity');
            }
        });
        $('#agree').change(function () {
            $('.agree-button').prop('disabled', function (i, val) {
                return !val;
            })
        });
    });
</script>
<?php $_SESSION = array(); ?>
<div class="install-body">
    <div class="install-logo">
        <img src="<?php echo SITE ?>/install/images/logo-black.svg" width="275" height="55" alt=""/>
    </div>

    <div class="center-wrapper step1">
        <div class="widget-table-title clearfix">
            <h3><span class="arrow-icon"></span> Добро пожаловать в мастер установки Moguta.CMS™</h3>
            <ul class="step-list">
                <li class="step-number active">шаг 1</li>
                <li class="step-number">2</li>
                <li class="step-number ">3</li>
                <li class="step-number last">4</li>
            </ul>
        </div>
        <div class="install-text">
            <p>Сейчас будет произведена установка Вашего интернет-магазина.</p>

            <p>Вам необходимо иметь базу данных на Вашем хостинге и знать параметры для подключения к ней.
                Если в процессе установки у Вас возникнут вопросы, Вы можете найти ответы в <a
                    href="http://wiki.moguta.ru/ustanovka-sistemy" target="_blank">документации</a>
                или на <a href="http://forum.moguta.ru" target="_blank">форуме Moguta.CMS</a>™</p>
            <br/>

            <p>Перед началом установки необходимо ознакомиться с <a href="https://moguta.ru/license" target="_blank">"Лицензионным
                    соглашением и условиями использования"</a>.</p>



            <div class="clear"></div>
        </div>
        <div class="install-footer">
            <form action="" method="post">
                <div class="agree-blok">
                    <label>Я прочитал <a href="https://moguta.ru/license" target="_blank">"Условия использования"</a> и
                        согласен с ними <input type="checkbox" name="agree" value="ok" id="agree"></label>
                    <button class="agree-button opacity" type="submit" name="step1" value="go" disabled="disabled">
                        <span>Продолжить</span></button>
                </div>
            </form>
        </div>
    </div>
</div>


</body>
</html>