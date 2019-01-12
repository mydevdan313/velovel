<?php echo ('<link rel="stylesheet" href="'.SITE.'/'.self::$path.'/css/font-awesome.min.css"  type="text/css">')?>
<div class="mg-trigger-guarantee <?php echo $options['layout'] ? $options['layout'] : 'vertleft' ?>" <?php echo $styleTrigg?>>
    <?php echo $trigger['title'] ? "<h2>".$trigger['title']."</h2>" : "" ?>
    <?php foreach ($trigger['elements'] as $elem): ?>
      <div class="mg-trigger <?php if(!empty($elem['text'])): ?>mg-trigger--withtext<?php endif;?>" style="background-color: #<?php echo $options['background'] ?>;<?php if($options['height']) {echo ' height:'.$options['height'].'px;';} ?> <?php echo $widthTrig?>" >
          <span class="mg-trigger-icon" style="<?php echo $float ?>">
              <?php if (stristr($elem['icon'], 'style') === FALSE) : 
                $elem['icon'] = str_replace('>', ' style="'.$style.'>', $elem['icon']);
              else :
                 $elem['icon'] = str_replace('">', $style.'>', $elem['icon']);
              endif; ?>
              <?php echo $elem['icon'] ?>
          </span>
          <?php if(!empty($elem['text'])): ?>
              <span <?php if ($options['place'] == 'left' && $options['layout']=='horfloat') {echo 'style="display: table-cell;"';} ?> class="mg-trigger-text" >
                  <?php echo htmlspecialchars_decode($elem['text']); ?>
              </span>
          <?php endif;?>
      </div>
    <?php endforeach; ?>
</div>
<?php if ($options['layout'] == 'vertleft' || $options['layout'] == 'vertright' ) : ?>
<div class="clear"></div>
 <?php endif;?>