<?php 

$remInfo =  false; $style = 'style="display:none;"';

if (MG::getSetting('printRemInfo') == "true") {
    $message = lang('countMsg1').' "'.str_replace("'", "&quot;", $data['title']).'" '.lang('countMsg2').' "'.$data['code'].'"'.lang('countMsg3');
    $message = urlencode($message);

if($data['count'] == '0'){
    $style = 'style="display:block;"';
}
    $remInfo = $data['remInfo'] !='false' ? true : false;
}?>

<span class="count">
    <?php if ($data['count'] === 0 || $data['count'] === '0') : ?>
        <span class="c-product__stock c-product__stock--out">
            <?php echo lang('countOutOfStock'); ?>
        </span>
    <?php elseif ((int)$data['count']>0): ?>
        <span class="c-product__stock c-product__stock--in">
            <?php echo lang('countInStock'); ?>:
            <span class="c-product__stock--span label-black count"><?php echo $data['count'].' '.$data['category_unit']; ?></span>
        </span>
    <?php else : ?>
        <span class="c-product__stock c-product__stock--in count" itemprop="availability">
            <?php echo lang('countInStock'); ?>
        </span>
    <?php endif;?>
</span>

<?php
if ($remInfo && MG::get('controller')=="controllers_product"): ?>
    <div class="c-product__message" <?php echo $style ?>>
        <a class="c-button"  rel='nofollow' href='<?php echo SITE."/feedback?message=".str_replace(' ', '&#32;', $message)?>'><?php echo lang('countMessage'); ?></a>
    </div>
<?php endif; 