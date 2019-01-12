<div class="c-carousel">
    <div class="c-carousel__title">
        <span class="c-carousel__title--span">
            <?php echo lang('relatedAdd'); ?>
        </span>
    </div>
    <div class="c-carousel__content--related">        
        <?php foreach ($data['products'] as $item) {
                        $item['link'] = SITE.'/'.$item['category_url'].$item['product_url'];
                        $data['item'] = $item;
                        layout('mini_product', $data);
                    } ?>        
    </div>
</div>