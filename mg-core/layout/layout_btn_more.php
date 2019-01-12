<a href="<?php echo SITE.'/'.((MG::getSetting('shortLink') != 'true')&&($data["category_url"]=='') ? 'catalog/' : $data["category_url"]) . $data["product_url"];?>" class="product-info <?php echo $data['classForButton'] ?>"><?php echo lang('buttonMore'); ?></a>
<?php if (!empty($data['variant_exist'])) :?>
<a style="display:none" href="<?php echo SITE . '/catalog?inCartProductId=' . $data["id"]; ?>" rel="nofollow" class="addToCart product-buy action_view_variant" data-item-id="<?php echo $data["id"] ?>"><?php echo lang('buttonBuy'); ?></a>
<?php endif;?>	  
	  