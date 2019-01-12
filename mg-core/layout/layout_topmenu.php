<div class="c-menu">
    <a class="c-button" href="#c-nav__menu">
        <div class="c-hamburger">
            <div class="c-hamburger__row"></div>
            <div class="c-hamburger__row"></div>
            <div class="c-hamburger__row"></div>
        </div>
        <?php echo lang('menu'); ?>
    </a>

    <nav class="c-nav" id="c-nav__menu">
        <div class="c-nav__menu">
            <ul class="c-nav__dropdown c-nav__dropdown--1 c-menu__dropdown c-menu__dropdown--1">
                <?php foreach($data['pages'] as $page):?>
                <?php if($page['invisible']=="1"){continue;}?>
                <?php if(URL::getUrl()==$page['link']||URL::getUrl()==$page['link'].'/'){$active = 'active';} else {$active = '';}?>
                <?php if(isset($page['child'])):?>

                <?php $slider = 'slider'; $noUl = 1; foreach($page['child'] as $pageLevel1){$noUl *= $pageLevel1['invisible']; } if($noUl){$slider='';}?>
                <li class="c-nav__level c-nav__level--1 c-menu__level c-menu__level--1">
                    <a class="c-nav__link c-nav__link--1 c-nav__link--arrow c-menu__link c-menu__link--1 c-menu__link--arrow" href="<?php echo $page['link']; ?>">
                        <div class="c-nav__text c-menu__text">
                            <?php echo MG::contextEditor('page', $page['title'], $page["id"], "page"); ?>
                        </div>
                        <div class="c-nav__icon c-menu__icon">
                            <svg class="icon icon--arrow-down"><use xlink:href="#icon--arrow-down"></use></svg>
                        </div>
                    </a>

                    <?php  if($noUl){$slider=''; continue;} ?>

                    <ul class="c-nav__dropdown c-nav__dropdown--2 c-menu__dropdown c-menu__dropdown--2">
                        <?php foreach($page['child'] as $pageLevel1):?>
                        <?php if($pageLevel1['invisible']=="1"){continue;}?>
                        <?php if(isset($pageLevel1['child'])):?>
                        <?php $slider = 'slider'; $noUl = 1; foreach($pageLevel1['child'] as $pageLevel2){$noUl *= $pageLevel2['invisible']; } if($noUl){$slider='';}?>

                        <li class="c-nav__level c-nav__level--2 c-menu__level c-menu__level--2">
                            <a class="c-nav__link c-nav__link--2 c-nav__link--arrow c-menu__link c-menu__link--2  c-menu__link--arrow" href="<?php echo $pageLevel1['link']; ?>">
                                <div class="c-nav__text c-menu__text">
                                    <?php echo MG::contextEditor('page', $pageLevel1['title'], $pageLevel1["id"], "page"); ?>
                                </div>
                                <div class="c-nav__icon c-menu__icon">
                                    <svg class="icon icon--arrow-right"><use xlink:href="#icon--arrow-right"></use></svg>
                                </div>
                            </a>

                            <?php  if($noUl){$slider=''; continue;} ?>
                            <ul class="c-nav__dropdown c-nav__dropdown--3 c-menu__dropdown c-menu__dropdown--3">
                                <?php foreach($pageLevel1['child'] as $pageLevel2):?>
                                    <?php if($pageLevel2['invisible']=="1"){continue;}?>
                                    <li class="c-nav__level c-nav__level--3 c-menu__level c-menu__level--3">
                                        <a class="c-nav__link c-nav__link--3 c-menu__link c-menu__link--3" href="<?php echo $pageLevel2['link']; ?>">
                                            <div class="c-nav__text c-menu__text">
                                                <?php echo MG::contextEditor('page', $pageLevel2['title'], $pageLevel2["id"], "page");?>
                                            </div>
                                        </a>
                                    </li>
                                <?php endforeach;?>
                            </ul>
                        </li>

                        <?php else:?>

                        <li class="c-nav__level c-nav__level--2 c-menu__level c-menu__level--2">
                            <a class="c-nav__link c-nav__link--2 c-menu__link c-menu__link--2" href="<?php echo $pageLevel1['link']; ?>">
                                <div class="c-nav__text c-menu__text">
                                    <?php echo MG::contextEditor('page', $pageLevel1['title'], $pageLevel1["id"], "page");  ?>
                                </div>
                            </a>
                        </li>
                        <?php endif;?>

                      <?php endforeach;?>
                    </ul>
                </li>

                <?php else:?>
                <li class="c-nav__level c-nav__level--1 c-menu__level c-menu__level--1">
                    <a class="c-nav__link c-nav__link--1 c-menu__link c-menu__link--1" href="<?php echo $page['link']; ?>">
                        <div class="c-nav__text c-menu__text">
                            <?php echo MG::contextEditor('page', $page['title'], $page["id"], "page"); ?>
                        </div>
                    </a>
                </li>
                <?php endif;?>

                <?php endforeach;?>
            </ul>
        </div>
    </nav>
</div>