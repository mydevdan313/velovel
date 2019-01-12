<?php
/**
 *  Файл представления Enter - выводит сгенерированную движком информацию на странице сайта авторизации пользователей.
 *  В этом файле доступны следующие данные:
 *   <code>
 *    $data['msgError'] => Сообщение об ошибке авторизации,
 *    $data['meta_title'] => 'Значение meta тега для страницы '
 *    $data['meta_keywords'] => 'Значение meta_keywords тега для страницы '
 *    $data['meta_desc'] => 'Значение meta_desc тега для страницы '
 *   </code>
 *
 *   Получить подробную информацию о каждом элементе массива $data, можно вставив следующую строку кода в верстку файла.
 *   <code>
 *    <?php viewData($data['msgError']); ?>
 *   </code>
 *
 *   Вывести содержание элементов массива $data, можно вставив следующую строку кода в верстку файла.
 *   <code>
 *    <?php echo $data['msgError']; ?>
 *   </code>
 *
 *   <b>Внимание!</b> Файл предназначен только для форматированного вывода данных на страницу магазина. Категорически не рекомендуется выполнять в нем запросы к БД сайта или реализовывать сложную программную логику логику.
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Views
 */
// Установка значений в метатеги title, keywords, description.
mgSEO($data);
?>

<div class="l-row">

    <div class="l-col min-0--12">
        <div class="c-title"><?php echo lang('enterTitle'); ?></div>
    </div>
    <?php echo !empty($data['msgError']) ? '<div class="l-col min-0--12"><div class="c-alert c-alert--red">'.$data['msgError']. '</div></div>' : '' ?>
    <div class="l-col min-0--12">
        <form class="c-form c-form--width" action="<?php echo SITE ?>/enter" method="POST">
            <div class="c-form__row">
                <input type="text" name="email" placeholder="Email" value="<?php echo !empty($_POST['email']) ? $_POST['email'] : '' ?>" required>
            </div>
            <div class="c-form__row">
                <input type="password" name="pass" placeholder="<?php echo lang('enterPass'); ?>" required>
            </div>

            <?php echo !empty($data['checkCapcha']) ? $data['checkCapcha'] : '' ?>
            <?php if (!empty($_REQUEST['location'])) : ?>
                <input type="hidden" name="location" value="<?php echo $_REQUEST['location']; ?>"/>
            <?php endif; ?>
            <div class="c-form__row">
                <button type="submit" class="c-button"><?php echo lang('enterEnter'); ?></button>
                <a class="c-button c-button--link" href="<?php echo SITE ?>/forgotpass"><?php echo lang('enterForgot'); ?></a>
            </div>
            <div class="c-form__row c-form__row--line">
                <a class="c-button c-button--border" href="<?php echo SITE ?>/registration"><?php echo lang('enterRegister'); ?></a>
            </div>
        </form>
    </div>
</div>