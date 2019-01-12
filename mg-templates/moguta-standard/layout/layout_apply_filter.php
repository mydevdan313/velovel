<?php if (!empty($data)): ?>
    <div class="l-col min-0--12">
        <div class="c-apply apply-filter-line">
            <div class="c-apply__title">
                <?php echo lang('filterApplied'); ?>
            </div>
            <form class="c-apply__form apply-filter-form" data-print-res="<?php echo MG::getSetting('printFilterResult') ?>">
                <ul class="c-apply__tags filter-tags">
                    <?php foreach ($data as $property) {
                        $cellCount = 0;
                        ?>
                        <li class="c-apply__tags--item apply-filter-item">
                            <span class="c-apply__tags--name filter-property-name">
                                <?php echo $property['name'] . ": ";?>
                            </span>

                            <?php if(in_array($property['values'][0], array('slider|easy', 'slider|hard', 'slider'))) {
                                ?>
                                <span class="c-apply__tags--value filter-price-range">
                                    <?php echo lang('filterFrom')." " . $property['values'][1] . " ".lang('filterTo')." " . $property['values'][2]; ?>
                                    <a href="javascript:void(0);" class="c-apply__tags--remove removeFilter">
                                        <svg class="icon icon--close"><use xlink:href="#icon--close"></use></svg>
                                    </a>
                                </span>

                                <?php if ($property['code'] != "price_course"): ?>
                                    <input name="<?php echo $property['code'] . "[" . $cellCount . "]" ?>"
                                           value="<?php echo $property['values'][0] ?>" type="hidden"/>
                                    <?php $cellCount++;?>
                                <?php endif; ?>

                                <input name="<?php echo $property['code'] . "[" . $cellCount . "]" ?>"
                                       value="<?php echo $property['values'][1] ?>" type="hidden"/>
                                <input name="<?php echo $property['code'] . "[" . ($cellCount + 1) . "]" ?>"
                                       value="<?php echo $property['values'][2] ?>" type="hidden"/>
                            <?php } else { ?>
                                <ul class="c-apply__tags--values filter-values">
                                <?php foreach ($property['values'] as $cell => $value) {
                                    ?>
                                    <li class="c-apply__tags--value apply-filter-item-value">
                                         <?php echo $value['name']; ?>
                                        <a href="javascript:void(0);" class="c-apply__tags--remove removeFilter">
                                            <svg class="icon icon--close"><use xlink:href="#icon--close"></use></svg>
                                        </a>
                                          <input name="<?php echo $property['code'] . "[" . $cell . "]" ?>" value="<?php echo $property['values'][$cell]['val'] ?>" type="hidden"/>
                                    </li>
                                    <?php } ?>
                                </ul>
                            <?php } ?>

                        </li>
                    <?php } ?>
                </ul>
                <div class="c-apply__refresh">
                    <a href="<?php echo SITE.URL::getClearUri()?>" class="c-button refreshFilter"><?php echo lang('filterReset'); ?></a>
                </div>
                <input type="hidden" name="applyFilter" value="1"/>
            </form>
        </div>
    </div>
<?php endif; ?>