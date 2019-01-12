<?php mgAddMeta('<script src="' . PATH_SITE_TEMPLATE . '/js/layout.compare.js"></script>'); ?>

<a class="c-compare__link mg-product-to-compare" href="<?php echo SITE ?>/compare" title="<?php echo lang('compareToList'); ?>">
    <div class="c-compare__link--icon">
        <svg class="icon icon--compare"><use xlink:href="#icon--compare"></use></svg>
    </div>
    <div class="c-compare__link--count mg-compare-count" style="<?php echo ($_SESSION['compareCount']) ? 'display:block;' : 'display:none;'; ?>">
        <div class="c-compare__link--number">
            <?php if (isset($_SESSION['compareCount'])) {echo $_SESSION['compareCount'];} else{echo 0;}?>
        </div>
    </div>
    <div class="c-compare__link--text"><?php echo lang('compareCompare'); ?></div>
</a>

<div class="c-compare__informer">
    <div class="c-compare__informer--content">
        <svg class="icon icon--check"><use xlink:href="#icon--check"></use></svg>
        <?php echo lang('compareAdded'); ?>
    </div>
</div>