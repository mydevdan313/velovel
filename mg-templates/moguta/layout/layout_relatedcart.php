<div class="c-carousel">
    <div class="c-carousel__title">
        <span class="c-carousel__title--span">
            <?php echo lang('relatedAddCart'); ?>
        </span>
    </div>
    <div class="c-carousel__content">
        <?php foreach ($data['products'] as $item) {
                        $item['link'] = SITE.'/'.$item['category_url'].$item['product_url'];
                        $data['item'] = $item;
                        layout('mini_product', $data);
                    } ?>   
    </div>
</div>