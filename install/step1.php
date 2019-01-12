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

<div class="install-body">
    <div class="install-logo">
        <img src="<?php echo SITE ?>/install/images/logo-black.svg" width="275" height="55" alt=""/>
    </div>

    <div class="center-wrapper step2">

        <div class="widget-table-title clearfix">
            <h3><span class="arrow-icon"></span> Установка Moguta.CMS™</h3>
            <ul class="step-list">
                <li class="step-number passed">1</li>
                <li class="step-number active">шаг 2</li>
                <li class="step-number ">3</li>
                <li class="step-number last">4</li>
            </ul>
        </div>
        <?php if ($msg) echo $msg; ?>
        <?php if (!$libError):?>
            <div class="install-text">
                <form class="add-img-form" method="post" enctype="multipart/form-data" action=""
                      encoding="multipart/form-data" disable>
                    <h3 class="bd-title">Настройки подключения к базе данных</h3>

                    <div class="help-text">Параметры подключения вы можете уточнить у вашего
                        хостинг-провайдера. Обычно все настройки приходят в письме от хостинга.</div>

                    <table class="form-table">
                        <tr>
                            <td>
                                <span class="custom-text">Имя сервера базы<span class="red-star">*</span>:</span>
                            </td>
                            <td>
                                <input type="text" name="host" class="product-name-input" value="localhost">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="custom-text">Пользователь базы<span class="red-star">*</span>:</span>
                            </td>
                            <td>
                                <input type="text" name="user" class="product-name-input" value="<?php echo $user ?>"
                                       placeholder="">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="custom-text">Пароль к базе</span>
                            </td>
                            <td>
                                <input type="password" name="password" class="product-name-input"
                                       value="<?php echo $password ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="custom-text">Название базы<span class="red-star">*</span>:</span>
                            </td>
                            <td>
                                <input type="text" name="nameDB" class="product-name-input"
                                       value="<?php echo $nameDB ?>">
                            </td>
                        </tr>
                         <tr>
                            <td colspan="2" style="text-align: right;">
                                <span class="custom-text"><a href="javascript:void(0);" onclick="$('.display-none').show(); $(this).parents('tr').hide();" 
                                style="border: 1px dashed;border-width: 0 0 1px 0;text-decoration:none;">Задать префикc таблиц</a></span>
                                <div style="clear:both; font-size:10px; color:#3E3E3E; font-style: italic; margin-top:4px;" >(не обязательно)</div>
                            </td>
                           
                        </tr>
                        <tr class="display-none" style="display:none">
                            <td>
                                <span class="custom-text">Префикc таблиц:</span>
                            </td>
                            <td>
                                <input type="text" name="prefix" class="product-name-input"
                                       value="<?php echo $prefix ?>">
                            </td>
                        </tr>
                    </table>

                    <button class="save-settings" type="submit" name="step2" value="go"><span>Далее</span></button>
                    <div class="clear"></div>
                </form>
            </div>

        <?php endif ?>
    </div>
</div>


</body>
</html>