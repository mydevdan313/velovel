<?php if(!empty($data)): ?>
    <div class="l-col min-0--12">
        <div class="l-row">
            <?php foreach($data as $category): ?>
                <div class="l-col min-0--4 min-768--3 min-990--2 min-1025--3">
                    <a class="c-sub" href="<?php echo SITE.'/'.$category['parent_url'].$category['url']; ?>">
                        <?php if(!empty($category['image_url'])): ?>
                        <div class="c-sub__img">
                            <img src="<?php echo SITE.$category['image_url']; ?>" alt="<?php echo $category['seo_alt'] ?>" title="<?php echo $category['seo_title'] ?>">
                        </div>
                        <?php else: ?>
                        <div class="c-sub__img">
                            <img src="<?php echo SITE.'/uploads/thumbs/70_no-img.jpg' ?>" alt="<?php echo $category['title']; ?>" title="<?php echo $category['title']; ?>">
                        </div>
                        <?php endif; ?>
                        <div class="c-sub__title"><?php echo $category['title']; ?></div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>