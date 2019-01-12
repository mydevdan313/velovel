<?php if($thisUser = $data['thisUser']): ?>

    <a class="c-login" href="<?php echo SITE?>/personal">
        <div class="c-login__icon">
            <svg class="icon icon--user"><use xlink:href="#icon--user"></use></svg>
        </div>
        <div class="c-login__text"><?php echo lang('authAccount'); ?></div>
    </a>

<?php else: ?>

    <a class="c-login" href="<?php echo SITE?>/enter">
        <div class="c-login__icon">
            <svg class="icon icon--user"><use xlink:href="#icon--user"></use></svg>
        </div>
        <div class="c-login__text"><?php echo lang('authAccount'); ?></div>
    </a>

<?php endif; ?>