<?php if(EDITION == 'gipermarket' || EDITION == 'market') { ?>
<a href="<?php echo SITE.'/compare?inCompareProductId=' . $data["id"];?>" rel="nofollow" class="addToCompare" data-item-id="<?php echo $data["id"];?> "><?php echo lang('buttonCompare'); ?></a>
<?php } ?>