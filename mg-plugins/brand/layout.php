<div class="mg-brand-block">
    <?php if (!empty($brand)) : ?>
      <?php foreach ($brand as $value) : ?>
        <?php if ($value['url']) { ?>
          <div class="mg-brand-logo">
              <a href="<?php echo SITE.'/brand?brand='.$value['brand'] ?>">
                  <img src="<?php echo $value['url'] ?>" alt="<?php echo $value['brand']?>">
              </a>
          </div>
        <?php } ?>
      <?php endforeach; ?>

    <?php endif; ?>
</div>

