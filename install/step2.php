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

    <div class="center-wrapper step3">

        <div class="widget-table-title clearfix">
            <h3><span class="arrow-icon"></span> Установка Moguta.CMS™</h3>
            <ul class="step-list">
                <li class="step-number passed">1</li>
                <li class="step-number passed">2</li>
                <li class="step-number active">шаг 3</li>
                <li class="step-number last">4</li>
            </ul>
        </div>
        <?php if ($msg) echo $msg;?>
        <?php if (!$libError):?>
            <div class="install-text">
                <form class="add-img-form" method="post" enctype="multipart/form-data" action=""
                      encoding="multipart/form-data" disable>
                    <h3 class="bd-title">Параметры для входа в панель управления интернет-магазина</h3>

                    <div id="siteParam">
                        <table class="form-table">
                            <tr>
                                <td>
                                    <span class="custom-text">Введите свой электронный адрес<span
                                            class="red-star">*</span>:</span>
                                </td>
                                <td>
                                    <input type="text" name="email" value="<?php echo $adminEmail ?>"
                                           class="product-name-input">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="custom-text">Придумайте пароль<span class="red-star">*</span>:</span>
                                    <i>Не менее 5 символов</i>
                                </td>
                                <td>
                                    <input type="password" name="pass" class="product-name-input">
                                </td>
                            </tr>
                            <tr id="rePass">
                                <td>
                                    <span class="custom-text">Введите пароль еще раз<span
                                            class="red-star">*</span>:</span>
                                </td>
                                <td>
                                    <input type="password" name="rePass" class="product-name-input">
                                </td>
                            </tr>
                        </table>
                        <div class="radiobotton-text">
                            <label>
                                <input type="checkbox" name="engineType" value="test"
                                       class="checkit" <?php echo $checkedTest ?>>
                                Заполнить сайт демонстрационными данными
                            </label>
                        </div>

                        <div class="radiobotton-text">
                            <label>
                                <input type="checkbox" name="consentData" value="true" class="checkit" checked>
                                <span class="custom-text">Согласие на сбор данных для статистики</span>
                            </label>
                        </div>
                    </div>
                    <div id="progress-bar" style="margin-bottom: 20px;display: none;">
                        <h3>Пожалуйста, подождите пока завершится загрузка данных</h3>

                        <div id="controlButton" href="#" class="progress-button">
                            <div style="width: 815px;text-align:center;">Загрузка демонстрационных данных ...</div>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div id="progress-bar" style="margin-bottom: 20px;display: none;">
                        <div id="controlButton" href="#" class="progress-button">
                            <div style="width: 815px;text-align:center;">Загрузка демонстрационных данных ...</div>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <button class="save-settings" type="submit" name="step3" value="go"><span>Установить</span></button>
                    <div class="clear"></div>

                    <input type="hidden" name="step3" class="product-name-input" value="go">
                    <input type="hidden" name="host" class="product-name-input" value="<?php echo $host ?>">
                    <input type="hidden" name="user" class="product-name-input" value="<?php echo $user ?>">
                    <input type="hidden" name="password" class="product-name-input" value="<?php echo $password ?>">
                    <input type="hidden" name="nameDB" class="product-name-input" value="<?php echo $nameDB ?>">
                    <input type="hidden" name="prefix" class="product-name-input" value="<?php echo $prefix ?>">
                </form>
            </div>
        <?php endif ?>
    </div>
</div>

<script>
    var buttonMax = 25;
    $(document).ready(function () {
        $('.progress-button').progressInitialize();
        var controlButton = $('#controlButton');

        // Реакция на чекбокс Заполнить сайт демо данными
        $('button[name=step3]').live("click", function () {
            $('.error-pass, .error-pass-count').remove();

            if ($('input[name=engineType]').attr("checked") == "checked") {

                if ($('input[name=pass]').val().length < 5) {
                    $('.install-text').append('<span class="error-pass-count">Ошибка! Пароль менее 5 символов</span>');
                    return false;

                } else if ($('input[name=showPass]').attr("checked") != "checked") {

                    if ($('input[name=pass]').val() != $('input[name=rePass]').val()) {
                        $('.install-text').append('<span class="error-pass">Ошибка! Введенные пароли не совпадают</span>');
                        return false;
                    }
                }

                controlButton.progressStart();
                uploadDemoFiles(1);
                return false;
            }
        });

        function uploadDemoFiles(part) {
            $('div#siteParam').hide();
            $('div h3.bd-title').hide();
            $('button[name=step3]').hide();
            $('#progress-bar').show();
            $.ajax({
                type: 'POST',
                url: 'index.php?step2=go&ajax=y&part=' + part,
                dataType: 'json',
                success: function (response) {
                    if (part < 9) {
                        controlButton.progressSet((part) * (99 / 9));
                        uploadDemoFiles(part + 1);
                        buttonMax = (part + 1)*(99 / 9);
                        if (buttonMax >= 100) {
                            buttonMax = 99;
                        }
                    } else {
                        //$('button[name=step3]').show();
                        controlButton.progressFinish();
                        $('button[name=step3]').parents('form').submit();
                    }
                }
            });
        }

    });
    (function ($) {
        var buttonCurrent = 0;
        
        // Creating a number of jQuery plugins that you can use to
        // initialize and control the progress meters.

        $.fn.progressInitialize = function () {

            // This function creates the necessary markup for the progress meter
            // and sets up a few event listeners.


            // Loop through all the buttons:

            return this.each(function () {

                var button = $(this),
                    progress = 0;

                // Extract the data attributes into the options object.
                // If they are missing, they will receive default values.

                var options = $.extend({
                    type: 'background-horizontal',
                    loading: 'Загрузка демонстрационных данных ...',
                    finished: 'Загрузка завершена!'
                }, button.data());

                // Add the data attributes if they are missing from the element.
                // They are used by our CSS code to show the messages
                button.attr({'data-loading': options.loading, 'data-finished': options.finished});

                // Add the needed markup for the progress bar to the button
                var bar = $('<span class="tz-bar ' + options.type + '">').appendTo(button);


                // The progress event tells the button to update the progress bar
                button.on('progress', function (e, val, absolute, finish) {

                    if (!button.hasClass('in-progress')) {

                        // This is the first progress event for the button (or the
                        // first after it has finished in a previous run). Re-initialize
                        // the progress and remove some classes that may be left.

                        bar.show();
                        progress = 0;
                        button.removeClass('finished').addClass('in-progress')
                    }

                    // val, absolute and finish are event data passed by the progressIncrement
                    // and progressSet methods that you can see near the end of this file.

                    if (absolute) {
                        progress = val;
                    }
                    else {
                        progress += val;
                    }

                    if (progress >= 100) {
                        progress = 100;
                    }
                    buttonCurrent = progress;
                    if (finish) {

                        button.removeClass('in-progress').addClass('finished');

                        bar.delay(500).fadeOut(function () {

                            // Trigger the custom progress-finish event
                            //button.trigger('progress-finish');
                            //setProgress(0);
                            return true;
                        });

                    }

                    setProgress(progress);
                });

                function setProgress(percentage) {
                    bar.filter('.background-horizontal,.background-bar').width(percentage + '%');
                    bar.filter('.background-vertical').height(percentage + '%');
                }

            });

        };

        // progressStart simulates activity on the progress meter. Call it first,
        // if the progress is going to take a long time to finish.

        $.fn.progressStart = function () {

            var button = this.first(),
                last_progress = new Date().getTime();

            if (button.hasClass('in-progress')) {
                // Don't start it a second time!
                return this;
            }

            button.on('progress', function () {
                last_progress = new Date().getTime();
            });

            // Every half a second check whether the progress
            // has been incremented in the last two seconds

            var interval = window.setInterval(function () {

                if (new Date().getTime() > 200 + last_progress) {

                    // There has been no activity for two seconds. Increment the progress
                    // bar a little bit to show that something is happening

                    button.progressIncrement(1);
                }

            }, 200);

            button.on('progress-finish', function () {
                window.clearInterval(interval);
            });

            return button.progressIncrement(1);
        };

        $.fn.progressFinish = function () {
            //return this.first().progressSet(100);
            var button = this.first();
            button.trigger('progress-finish');
            button.removeClass('in-progress').addClass('finished');
            return true;
        };

        $.fn.progressIncrement = function (val) {

            val = val || 1;

            var button = this.first();

            if (buttonCurrent < buttonMax) {
                button.trigger('progress', [val]);
            }

            return this;
        };

        $.fn.progressSet = function (val) {
            val = val || 1;

            var finish = false;
            if (val >= 100) {
                finish = true;
            }

            return this.first().trigger('progress', [val, true, finish]);
        };

        // This function creates a progress meter that
        // finishes in a specified amount of time.

        $.fn.progressTimed = function (seconds, cb) {

            var button = this.first(),
                bar = button.find('.tz-bar');

            if (button.is('.in-progress')) {
                return this;
            }

            // Set a transition declaration for the duration of the meter.
            // CSS will do the job of animating the progress bar for us.

            bar.css('transition', seconds + 's linear');
            button.progressSet(99);

            window.setTimeout(function () {
                bar.css('transition', '');
                button.progressFinish();

                if ($.isFunction(cb)) {
                    cb();
                }

            }, seconds * 1000);
        };

    })(jQuery);
</script>
</body>
</html>