<?php
/**
 *  Файл представления Forgotpass - выводит сгенерированную движком информацию на странице восстановления пароля.
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


// Установка значений в метатеги title, keywords, description.
mgSEO($data);
?>

<div class="l-row">

    <?php if ($data['message']): ?>
        <div class="l-col min-0--12">
            <div class="c-alert c-alert--green alert-info"><?php echo $data['message'] ?></div>
        </div>
    <?php endif; ?>

    <?php if ($data['error']): ?>
        <div class="l-col min-0--12">
            <div class="c-alert c-alert--red mg-error"><?php echo $data['error'] ?></div>
        </div>
    <?php endif; ?>

    <?php switch ($data['form']) { case 1: ?>

        <div class="l-col min-0--12">
            <div class="c-title"><?php echo lang('forgotTitle'); ?></div>
        </div>
        <div class="l-col min-0--12">
            <div class="c-alert c-alert--blue"><?php echo lang('forgotInstruction'); ?></div>
        </div>
        <div class="l-col min-0--12">
            <form class="c-form c-form--width" action="<?php echo SITE ?>/forgotpass" method="POST">
                <div class="c-form__row">
                    <input type="text" name="email" placeholder="Email" required>
                </div>
                <div class="c-form__row">
                    <input type="submit" class="c-button" name="forgotpass" value="<?php echo lang('send'); ?>">
                </div>
            </form>
        </div>

        <?php break; case 2: ?>

        <div class="l-col min-0--12">
            <form class="c-form c-form--width" action="<?php echo SITE ?>/forgotpass" method="POST">
                <div class="c-form__row">
                    <input type="password" name="newPass" placeholder="<?php echo lang('forgotPass1'); ?>" required>
                </div>
                <div class="c-form__row">
                    <input type="password" name="pass2" placeholder="<?php echo lang('forgotPass2'); ?>" required>
                </div>
                <div class="c-form__row">
                    <input type="submit" class="c-button" name="chengePass" value="<?php echo lang('save'); ?>">
                </div>
            </form>
        </div>
    <?php } ?>

</div>