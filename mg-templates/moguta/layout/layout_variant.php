<?php 
MG::setSizeMapToData($data);
$style = '';
if (MG::getSetting('printVariantsInMini') != 'true' && 
  (MG::get('controller')=="controllers_catalog" || MG::get('controller')=="controllers_index" || MG::get('controller')=="controllers_group")
  ) {
  $style = "style='display:none'";
}
?>
<?php if(!empty($data['blockVariants'])){?>
<div class="c-variant block-variants" <?php echo $style; ?>>

    <div class="c-variant__title">
        <?php if($data['sizeMap'] == '') { ?>
        <?php echo lang('variantTitle'); ?>
        <?php } ?>
    </div>
    <div class="c-variant__scroll">
       
    <?php if($data['sizeMap'] != '') { 
      echo '<div class="sizeMap-row">';
      $color = '';
      
      $countColor = 0;
      foreach ($data['sizeMap'] as $item) {
        MG::loadLocaleData($item['id'], LANG, 'property_data', $item);
        if($item['type'] == 'color') {
          $countColor++;
          if($item['img']) {
            $color .= '<div class="color" data-id="'.$item['id'].'" style="background:url('.SITE.'/'.$item['img'].');background-size:cover;" title="'.$item['name'].'"></div>';
          } else {
            $color .= '<div class="color" data-id="'.$item['id'].'" style="background-color:'.$item['color'].';" title="'.$item['name'].'"></div>';
          }
          $colorName = $item['pName'];
        }
      }

      if(($color != '')&&($countColor>1)) {
        $colorFull = '<div class="color-block"><span>'.$colorName.':</span>'.$color.'</div>';
      } else {
        $colorFull = '';
      }
      
      $size = '';
      foreach ($data['sizeMap'] as $item) {
        MG::loadLocaleData($item['id'], LANG, 'property_data', $item);
        if($item['type'] == 'size') {
          $size .= '<div class="size" data-id="'.$item['id'].'"><span>'.$item['name'].'</span></div>';
          $sizeName = $item['pName'];
        }
      }
      if($size != '') {
        $sizeFull = '<div class="size-block"><span>'.$sizeName.':</span>'.$size.'</div>';
      } else {
        $sizeFull = '';
      }

      if(MG::getSetting('sizeMapMod') == 'size') {
        echo $sizeFull;
        echo $colorFull;
      } else {
        echo $colorFull;
        echo $sizeFull;
      }

      echo '</div>';
    } ?>
         <table class="variants-table">
            <?php foreach ($data['blockVariants'] as $variant) : ?>
              <?php $count = $variant['count']; ?>
                <tr class="c-variant__row variant-tr <?php echo !$j++ ? 'active-var' : ''?>" 
                <?php if($data['sizeMap'] != '') echo "style='display:none;'" ?>
                data-color="<?php echo $variant['color'] ?>" data-size="<?php echo $variant['size'] ?>" 
                  data-count="<?php echo $count; ?>">

                    <td class="c-variant__column">
                        <label class="c-form <?php echo !$j++ ? 'active' : ''?>">
                            <input type="radio" id="variant-<?php echo $variant['id']; ?>" data-count="<?php echo $count; ?>" name="variant" value = "<?php echo $variant['id']; ?>" <?php echo !$i++ ? 'checked=checked' : ''?>>
                            <?php $src = mgImageProductPath($variant['image'], $variant['product_id'], 'small'); echo !empty($variant['image'])?'
                                <span class="c-variant__img"><img src="'.$src.'" alt="image"></span>
                            ':'' ?>
                            <span class="c-variant__value">
                                <span class="c-variant__name variantTitle">
                                    <?php echo $variant['title_variant'] ?>
                                </span>
                                <span class="c-variant__price <?php if($variant['activity'] === "0" || $variant['count'] == 0){echo 'c-variant__price--none';} ?>">
                                    <?php if($variant["old_price"]!=""): ?>
                                        <s class="c-variant__price--old" <?php echo (!$variant['old_price']) ?'style="display:none"':'' ?>><?php echo MG::priceCourse($variant['old_price']); ?> <?php echo MG::getSetting('currency') ?></s>
                                    <?php endif; ?>
                                    <span class="c-variant__price--current">
                                        <?php echo $variant['price'] ?> <?php echo MG::getSetting('currency')?>
                                    </span>
                                    <span class="c-variant__price--not-available"><?php echo lang('variantDepleted'); ?></span>
                                </span>
                            </span>
                        </label>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
<?php }?>