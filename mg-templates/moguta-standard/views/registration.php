<?php
/**
 *  Файл представления Registration - выводит сгенерированную движком информацию на странице регистрации нового пользователя.
 *  В этом файле доступны следующие данные:
 *   <code>
 *    $data['error'] => Сообщение об ошибке.
 *    $data['message'] => Информационное сообщение.
 *    $data['form'] =>  Отображение формы,
 *    $data['meta_title'] => 'Значение meta тега для страницы '
 *    $data['meta_keywords'] => 'Значение meta_keywords тега для страницы '
 *    $data['meta_desc'] => 'Значение meta_desc тега для страницы '
 *   </code>
 *
 *   Получить подробную информацию о каждом элементе массива $data, можно вставив следующую строку кода в верстку файла.
 *   <code>
 *    <?php viewData($data['message']); ?>
 *   </code>
 *
 *   Вывести содержание элементов массива $data, можно вставив следующую строку кода в верстку файла.
 *   <code>
 *    <?php echo $data['message']; ?>
 *   </code>
 *
 *   <b>Внимание!</b> Файл предназначен только для форматированного вывода данных на страницу магазина. Категорически не рекомендуется выполнять в нем запросы к БД сайта или реализовывать сложную программную логику логику.
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Views
 */
// Установка значений в метатеги title, keywords, description

mgSEO($data);
?>
<script src="<?php echo SITE ?>/mg-core/script/jquery.maskedinput.min.js"></script>

<?php if ($data['form']){?>
    <div class="l-row">
        <div class="l-col min-0--12">
            <div class="c-title"><?php echo lang('registrationTitle'); ?></div>
        </div>

        <?php if ($data['message']): ?>
            <div class="l-col min-0--12">
                <div class="c-alert c-alert--green mg-success"><?php echo $data['message'] ?></div>
            </div>
        <?php endif; ?>

        <?php if ($data['error']): ?>
            <div class="l-col min-0--12">
                <div class="c-alert c-alert--red msgError"><?php echo $data['error'] ?></div>
            </div>
        <?php endif; ?>

        <div class="l-col min-0--12">
            <form class="c-form c-form--width" action="<?php echo SITE ?>/registration" method="POST">
                <div class="c-form__row">
                    <input type="text" name="email" placeholder="Email" value="<?php echo $_POST['email'] ?>" required>
                </div>
                <div class="c-form__row">
                    <input type="password" placeholder="<?php echo lang('enterPass'); ?>" name="pass" required>
                </div>
                <div class="c-form__row">
                    <input type="password" placeholder="<?php echo lang('registrationConfirmPass'); ?>" name="pass2" required>
                </div>
                <div class="c-form__row">
                    <input type="text" name="name" placeholder="<?php echo lang('fname'); ?>" value="<?php echo $_POST['name'] ?>" required>
                </div>
                <div class="c-form__row">
                    <input type="hidden" name="ip" value="<?php echo $_SERVER['REMOTE_ADDR'] ?>" required>
                </div>
                <?php if (MG::getSetting('useCaptcha') == "true" && MG::getSetting('useReCaptcha') != 'true'):?>
                    <div class="c-form__row">
                        <b><?php echo lang('captcha'); ?></b>
                    </div>
                    <div class="c-form__row">
                        <img style="background: url('<?php echo PATH_TEMPLATE ?>/images/cap.png');" src="captcha.html" width="140" height="36">
                    </div>
                    <div class="c-form__row">
                        <input type="text" name="capcha" class="captcha" required>
                    </div>
                <?php endif; ?>
                <?php echo MG::printReCaptcha(); ?>
                <div class="c-form__row">
                    <button type="submit" class="c-button" name="registration"><?php echo lang('registrationButton'); ?></button>
                </div>
            </form>
        </div>
    </div>

    <?php } else { ?>

    <?php if ($data['message']): ?>
        <div class="l-col min-0--12">
            <div class="-alert c-alert--green mg-success"><?php echo $data['message'] ?></div>
        </div>
    <?php endif; ?>

 <?php } ?>
