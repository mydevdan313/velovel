<?php mgAddMeta('<link type="text/css" href="'.SCRIPT.'standard/css/jquery.fancybox.css" rel="stylesheet"/>'); ?>
<?php mgAddMeta('<script src="'.SCRIPT.'jquery.fancybox.pack.js"></script>'); ?>
<?php mgAddMeta('<script src="'.SCRIPT.'jquery.bxslider.min.js"></script>'); ?>
<?php mgAddMeta('<script src="' . PATH_SITE_TEMPLATE . '/js/layout.images.js"></script>'); ?>
<?php mgAddMeta('<script src="'.SCRIPT.'zoomsl-3.0.js"></script>'); ?>

<div class="c-images mg-product-slides">
    <!-- Избранное -->
    <?php
    if (in_array(EDITION, array('market', 'gipermarket'))) {
        $favorites = explode(',', $_COOKIE['favorites']);
        if(in_array($data['id'], $favorites)) {
            $_fav_style_add = 'display:none;';
            $_fav_style_remove = '';
        } else {
            $_fav_style_add = '';
            $_fav_style_remove = 'display:none;';
        }
        ?>
        <a href="javascript:void(0);" data-item-id="<?php echo $data['id']; ?>" class="mg-remove-to-favorites mg-remove-to-favorites--product" style="<?php echo $_fav_style_remove ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 47.94 47.94"><path d="M26.285 2.486l5.407 10.956c.376.762 1.103 1.29 1.944 1.412l12.091 1.757c2.118.308 2.963 2.91 1.431 4.403l-8.749 8.528c-.608.593-.886 1.448-.742 2.285l2.065 12.042c.362 2.109-1.852 3.717-3.746 2.722l-10.814-5.685c-.752-.395-1.651-.395-2.403 0l-10.814 5.685c-1.894.996-4.108-.613-3.746-2.722l2.065-12.042c.144-.837-.134-1.692-.742-2.285L.783 21.014c-1.532-1.494-.687-4.096 1.431-4.403l12.091-1.757c.841-.122 1.568-.65 1.944-1.412l5.407-10.956c.946-1.919 3.682-1.919 4.629 0z"/></svg>
            <span class="remove__text">В избранном</span>
            <span class="remove__hover">Убрать</span>
        </a>
        <a href="javascript:void(0);" data-item-id="<?php echo $data['id']; ?>" class="mg-add-to-favorites mg-add-to-favorites--product" style="<?php echo $_fav_style_add ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 47.94 47.94"><path d="M26.285 2.486l5.407 10.956c.376.762 1.103 1.29 1.944 1.412l12.091 1.757c2.118.308 2.963 2.91 1.431 4.403l-8.749 8.528c-.608.593-.886 1.448-.742 2.285l2.065 12.042c.362 2.109-1.852 3.717-3.746 2.722l-10.814-5.685c-.752-.395-1.651-.395-2.403 0l-10.814 5.685c-1.894.996-4.108-.613-3.746-2.722l2.065-12.042c.144-.837-.134-1.692-.742-2.285L.783 21.014c-1.532-1.494-.687-4.096 1.431-4.403l12.091-1.757c.841-.122 1.568-.65 1.944-1.412l5.407-10.956c.946-1.919 3.682-1.919 4.629 0z"/></svg>
            В избранное
        </a>
    <?php } ?>
    <!-- / Избранное -->
    <ul class="main-product-slide">
        <?php foreach ($data["images_product"] as $key=>$image){?>
            <li class="c-images__big product-details-image">
                <a class="fancy-modal" href="<?php echo mgImageProductPath($image, $data["id"]) ?>" data-fancybox="mainProduct">
                    <?php
                    $item["image_url"] = $image;
                    $item["id"] = $data["id"];
                    $item["title"] = $data["title"];
                    $item["image_alt"] = $data["images_alt"][$key];
                    $item["image_title"] = $data["images_title"][$key];
                    echo mgImageProduct($item,false,'MID',true);
                    ?>
                </a>
            </li>
        <?php }?>
    </ul>

    <?php if(count($data["images_product"])>1){?>

        <div class="c-carousel slides-slider">
            <div class="c-carousel__images slides-inner">
                <?php foreach ($data["images_product"] as $key=>$image){
                    $src = mgImageProductPath($image, $data["id"], 'small');
                    $data["images_alt"][$key] = $imagesData["image_alt"]?$imagesData["image_alt"]:$data["title"].'_'.$key;
                    $data["images_title"][$key] = $imagesData["images_title"]?$imagesData["images_title"]:$data["title"].'_'.$key;
                    ?>
                    <a class="c-images__slider__item  slides-item" data-slide-index="<?php echo $key?>">
                        <img class="c-images__slider__img   mg-peview-foto"  src="<?php echo $src ?>" alt="<?php echo $data["images_alt"][$key];?>"/>
                    </a>
                <?php }?>
            </div>
        </div>
    <?php }?>
</div>