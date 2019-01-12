<?php mgSEO($data); $prodIds = array(); $propTable = array(); ?>

<div class="l-row">
    <div class="l-col min-0--12">
        <div class="c-title"><?php echo lang('compareProduct'); ?></div>
    </div>
    <div class="l-col min-0--12">
        <div class="c-alert c-alert--blue"><?php echo lang('compareProductEmpty'); ?></div>
    </div>
</div>

<!-- compare - start -->
<?php if(!empty($data['catalogItems'])){ ?>
<div class="c-compare mg-compare-products">

    <!-- top - start -->
    <div class="c-compare__top   mg-compare-left-side">
        <?php if(!empty($_SESSION['compareList'])){ ?>

        <div class="c-compare__top__select   mg-category-list-compare">
            <?php if(MG::getSetting('compareCategory')!='true'){ ?>
            <form class="c-form c-form--width">
                <select name="viewCategory" onChange="this.form.submit()">
                    <?php foreach($data['arrCategoryTitle'] as $id => $value): ?>
                    <option value ='<?php echo $id ?>' <?php
                        if($_GET['viewCategory']==$id){
                        echo "selected=selected";
                        }
                    ?> ><?php echo $value ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
            <?php } ?>
        </div>

        <div class="c-compare__top__buttons">
            <a class="c-button c-compare__clear   mg-clear-compared-products" href="<?php echo SITE ?>/compare?delCompare=1" >
                <svg class="icon icon--remove"><use xlink:href="#icon--remove"></use></svg>
                <?php echo lang('compareClean'); ?>
            </a>
            <a class="c-button" href="<?php echo SITE ?>">
                <svg class="icon icon--arrow-left"><use xlink:href="#icon--arrow-left"></use></svg>
                <?php echo lang('compareBack'); ?>
            </a>
        </div>

        <?php } ?>
    </div>
    <!-- top - end -->


    <!-- right block - start -->
    <div class="c-compare__right">

        <!-- items - start -->
        <div class="c-compare__wrapper mg-compare-product-wrapper">

            <?php if(!empty($data['catalogItems'])){ foreach($data['catalogItems'] as $item){ ?>
                <div class="c-goods__item c-compare__item mg-compare-product" itemscope itemtype="http://schema.org/Product">
                    <div class="c-goods__left">
                        <a class="c-compare__remove mp-remove-compared-product" href="<?php echo SITE ?>/compare?delCompareProductId=<?php echo $item['id'] ?>">
                            <svg class="icon icon--close"><use xlink:href="#icon--close"></use></svg>
                        </a>
                        <a class="c-goods__img c-compare__img" href="<?php echo $item['link'] ?>">
                            <?php echo mgImageProduct($item); ?>
                        </a>
                    </div>
                    <div class="c-goods__right">
                        <div class="c-goods__price mg-compare-product-list product-status-list">
                            <?php if($item["old_price"]!=""): ?>
                            <s class="c-goods__price--old old-price" <?php echo (!$item['old_price'])?'style="display:none"':'' ?>>
                                <?php echo str_replace(' ', '', trim($item['old_price']))." ".$item['currency']; ?>
                            </s>
                            <?php endif; ?>
                            <div class="c-goods__price--current price">
                               <?php echo $item['price'] ?> <?php echo $item['currency']; ?>
                            </div>
                        </div>
                        <a class="c-goods__title" href="<?php echo $item['link'] ?>" itemprop="name" content="<?php echo $item["title"] ?>">
                            <?php echo $item['title'] ?>
                        </a>
                        <div class="c-compare__property">
                            <?php echo $item['propertyForm'] ?>
                        </div>
                        <?php foreach($item['stringsProperties'] as $key => $val){ $propTable[$key][$item['id']] = $val; } ?>
                    </div>
                </div>

            <?php $prodIds[] = $item['id']; } } ?>
        </div>
        <!-- items - end -->

        <?php foreach($propTable as $key => $prop){ foreach($prodIds as $id){ if(empty($prop[$id])){ $propTable[$key][$id] = '-'; ksort($propTable[$key]); } } } ?>

        <!-- right table - start -->
        <div class="c-compare__table c-compare__table__right  mg-compare-fake-table-right">
            <?php foreach($propTable as $key => $prop){ ?>
            <div class="c-compare__row   mg-compare-fake-table-row">
                <?php foreach($prop as $prodId => $val){ ?>
                <div class="c-compare__column   mg-compare-fake-table-cell">
                    <?php echo $val ?>
                </div>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
        <!-- right table - start -->
    </div>
    <!-- right block - end -->


    <!-- left block - start -->
    <div class="c-compare__left">
        <!-- left table - start -->
        <div class="c-compare__table c-compare__table__left  mg-compare-fake-table">
            <div class="c-compare__row   mg-compare-fake-table-left <?php echo $data['moreThanThree'] ?>">
                <?php foreach($propTable as $key => $prop){ ?>
                <div class="c-compare__column   mg-compare-fake-table-cell <?php if(trim($data['property'][$key])!=='') : ?>with-tooltip<?php endif; ?>">
                    <?php if(trim($data['property'][$key])!=='') : ?>
                        <div class="mg-tooltip">?<div class="mg-tooltip-content" style="display:none;"><?php echo $data['property'][$key] ?></div></div>
                    <?php endif; ?>
                    <div class="compare-text" title="<?php echo $key ?>">
                        <?php echo $key ?>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
        <!-- left table - end -->
    </div>
    <!-- left block - end -->
</div>
<?php } ?>
<!-- compare - end -->