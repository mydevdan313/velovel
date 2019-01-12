<div class="c-catalog">
    <a class="c-button" href="#c-nav__catalog">
        <?php echo lang('menuCatalog'); ?>
        <svg class="icon icon--arrow-down"><use xlink:href="#icon--arrow-down"></use></svg>
    </a>
    <nav class="c-nav" id="c-nav__catalog">
        <div class="c-nav__menu">
            <ul class="c-nav__dropdown c-nav__dropdown--1 c-catalog__dropdown c-catalog__dropdown--1">

                <?php foreach ($data['categories'] as $category): ?>
                <?php if ($category['invisible'] == "1") { continue;} ?>
                <?php if (SITE.URL::getClearUri() === $category['link']) { $active = 'active'; } else { $active = ''; } ?>
                <?php if (isset($category['child'])): ?>
                <?php $slider = 'slider'; $noUl = 1; foreach($category['child'] as $categoryLevel1){$noUl *= $categoryLevel1['invisible']; } if($noUl){$slider='';}?>

                <li class="c-nav__level c-nav__level--1 c-catalog__level c-catalog__level--1 c-catalog__level--arrow">
                    <a class="c-nav__link c-nav__link--1 c-nav__link--arrow c-catalog__link c-catalog__link--1 c-catalog__link--arrow" href="<?php echo $category['link']; ?>">
 						<?php if(!empty($category['menu_icon'])): ?>
                            <div class="c-nav__img c-catalog__img"><img src="<?php echo SITE.$category['menu_icon'];?>" alt="<?php echo $category['title']; ?>"></div>
                        <?php endif; ?>                    	
                        <div class="c-nav__text c-catalog__text">
                            <?php echo MG::contextEditor('category', $category['menu_title'] ? $category['menu_title'] : $category['title'], $category["id"], "category"); ?>
                        </div>
                        <?php if (MG::getSetting('showCountInCat')=='true'):?>
                            <div class="c-nav__count c-catalog__count"><?php echo $category['insideProduct']?''.$category['insideProduct'].'':''; ?></div>
                        <?php endif;?>

                    </a>
                    <div class="c-nav__icon c-catalog__icon">
                        <svg class="icon icon--arrow-right"><use xlink:href="#icon--arrow-right"></use></svg>
                    </div>

                    <?php if($noUl){$slider=''; continue;} ?>

                    <ul class="c-nav__dropdown c-nav__dropdown--2 c-catalog__dropdown c-catalog__dropdown--2">

                        <?php foreach ($category['child'] as $categoryLevel1): ?> <?php if ($categoryLevel1['invisible'] == "1") { continue; } ?>
                        <?php if (SITE.URL::getClearUri() === $categoryLevel1['link']) { $active = 'active'; } else { $active = ''; } ?>
                        <?php if (isset($categoryLevel1['child'])): ?>
                        <?php $slider = 'slider'; $noUl = 1; foreach($categoryLevel1['child'] as $categoryLevel2){$noUl *= $categoryLevel2['invisible']; } if($noUl){$slider='';}?>

                        <li class="c-nav__level c-nav__level--2 c-catalog__level c-catalog__level--2 c-catalog__level--arrow">
                            <a class="c-nav__link c-nav__link--2 c-nav__link--arrow c-catalog__link c-catalog__link--2 c-catalog__link--arrow" href="<?php echo $categoryLevel1['link']; ?>">
                                 <?php if(!empty($categoryLevel1['menu_icon'])): ?>
                                        <div class="mg-cat-img">
                                        <img src="<?php echo SITE.$categoryLevel1['menu_icon'];?>"  alt="<?php echo $categoryLevel1['title']; ?>" title="<?php echo $categoryLevel1['title']; ?>">
                                        </div>
                                 <?php endif; ?>     
                                <div class="c-nav__text c-catalog__text">
                                    <?php echo MG::contextEditor('category', $categoryLevel1['menu_title'] ? $categoryLevel1['menu_title'] : $categoryLevel1['title'], $categoryLevel1["id"], "category"); ?>
                                </div>
                                <?php if (MG::getSetting('showCountInCat')=='true'):?>
                                    <div class="c-nav__count c-catalog__count"><?php echo $categoryLevel1['insideProduct']?''.$categoryLevel1['insideProduct'].'':''; ?></div>
                                <?php endif;?>
                                <div class="c-nav__icon c-catalog__icon">
                                    <svg class="icon icon--arrow-right"><use xlink:href="#icon--arrow-right"></use></svg>
                                </div>
                            </a>

                            <?php  if($noUl){$slider=''; continue;} ?>

                            <ul class="c-nav__dropdown c-nav__dropdown--3 c-catalog__dropdown c-catalog__dropdown--3">
                                <?php foreach ($categoryLevel1['child'] as $categoryLevel2): ?>
                                <?php if ($categoryLevel2['invisible'] == "1") { continue; } ?>
                                <?php if (SITE.URL::getClearUri() === $categoryLevel2['link']) { $active = 'active'; } else { $active = ''; } ?>
                                <?php if (isset($categoryLevel2['child'])): ?>
                                <?php $slider = 'slider'; $noUl = 1; foreach($categoryLevel2['child'] as $categoryLevel3){$noUl *= $categoryLevel3['invisible']; } if($noUl){$slider='';}?>

                                <li class="c-nav__level c-nav__level--3 c-catalog__level c-catalog__level--3">
                                    <a class="c-nav__link c-nav__link--3 c-catalog__link c-catalog__link--3" href="<?php echo $categoryLevel2['link']; ?>">
                                        <?php if(!empty($categoryLevel2['menu_icon'])): ?>
                                            <div class="mg-cat-img">
                                            <img src="<?php echo SITE.$categoryLevel2['menu_icon'];?>"  alt="<?php echo $categoryLevel2['title']; ?>" title="<?php echo $categoryLevel2['title']; ?>">
                                            </div>
                                        <?php endif; ?>      
                                        <div class="c-nav__text c-catalog__text">
                                            <?php echo MG::contextEditor('category', $categoryLevel2['menu_title'] ? $categoryLevel2['menu_title'] : $categoryLevel2['title'], $categoryLevel2["id"], "category"); ?>
                                        </div>
                                        <?php if (MG::getSetting('showCountInCat')=='true'):?>
                                            <div class="c-nav__count c-catalog__count"><?php echo $categoryLevel2['insideProduct']?''.$categoryLevel2['insideProduct'].'':''; ?></div>
                                        <?php endif;?>
                                    </a>

                                    <?php  if($noUl){$slider=''; continue;} ?>
                                </li>

                                <?php else: ?>

                                <li class="c-nav__level c-nav__level--3 c-catalog__level c-catalog__level--3">
                                    <a class="c-nav__link c-nav__link--3 c-catalog__link c-catalog__link--3" href="<?php echo $categoryLevel2['link']; ?>">
                                        <?php if(!empty($categoryLevel2['menu_icon'])): ?>
                                            <div class="mg-cat-img">
                                            <img src="<?php echo SITE.$categoryLevel2['menu_icon'];?>"  alt="<?php echo $categoryLevel2['title']; ?>" title="<?php echo $categoryLevel2['title']; ?>">
                                            </div>
                                        <?php endif; ?>                                            
                                        <div class="c-nav__text c-catalog__text">
                                            <?php echo MG::contextEditor('category', $categoryLevel2['menu_title'] ? $categoryLevel2['menu_title'] : $categoryLevel2['title'], $categoryLevel2["id"], "category"); ?>
                                        </div>
                                        <?php if (MG::getSetting('showCountInCat')=='true'):?>
                                            <div class="c-nav__count c-catalog__count"><?php echo $categoryLevel2['insideProduct']?''.$categoryLevel2['insideProduct'].'':''; ?></div>
                                        <?php endif;?>
                                    </a>
                                </li>

                                <?php endif; ?>
                                <?php endforeach; ?>  
                            </ul>
                        </li>

                        <?php else: ?>

                        <li class="c-nav__level c-nav__level--2 c-catalog__level c-catalog__level--2 c-catalog__level--no-arrow">
                            <a class="c-nav__link c-nav__link--2 c-catalog__link c-catalog__link--2" href="<?php echo $categoryLevel1['link']; ?>">
                                 <?php if(!empty($categoryLevel1['menu_icon'])): ?>
                                    <div class="mg-cat-img">
                                    <img src="<?php echo SITE.$categoryLevel1['menu_icon'];?>"  alt="<?php echo $categoryLevel1['title']; ?>" title="<?php echo $categoryLevel1['title']; ?>">
                                     </div>
                                 <?php endif; ?>     
                                <div class="c-nav__text c-catalog__text">
                                    <?php echo MG::contextEditor('category', $categoryLevel1['menu_title'] ? $categoryLevel1['menu_title'] : $categoryLevel1['title'], $categoryLevel1["id"], "category"); ?>
                                </div>
                                <?php if (MG::getSetting('showCountInCat')=='true'):?>
                                    <div class="c-nav__count c-catalog__count"><?php echo $categoryLevel1['insideProduct']?''.$categoryLevel1['insideProduct'].'':''; ?></div>
                                <?php endif; ?>
                            </a>
                        </li>

                        <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <?php else: ?>

                <li class="c-nav__level c-nav__level--1 c-catalog__level c-catalog__level--1 c-catalog__level--no-arrow">
                    <a class="c-nav__link c-nav__link--1 c-catalog__link c-catalog__link--1" href="<?php echo $category['link']; ?>">
						<?php if(!empty($category['menu_icon'])): ?>
                            <div class="c-nav__img c-catalog__img">
                                <img src="<?php echo SITE.$category['menu_icon'];?>" alt="<?php echo $category['title']; ?>" title="<?php echo $category['title']; ?>">
                            </div>
                        <?php endif; ?>                       
                        <div class="c-nav__text c-catalog__text">
                            <?php echo MG::contextEditor('category', $category['menu_title'] ? $category['menu_title'] : $category['title'], $category["id"], "category"); ?>
                        </div>
                        <?php if (MG::getSetting('showCountInCat')=='true'):?>
                            <div class="c-nav__count c-catalog__count"><?php echo $category['insideProduct']?''.$category['insideProduct'].'':''; ?></div>
                        <?php endif; ?>
                    </a>
                </li>

                <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>
</div>