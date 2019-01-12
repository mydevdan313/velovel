<!--
Доступны переменные:
  $pluginName - название плагина
  $lang - массив фраз для выбранной локали движка
  $entity - набор записей сущностей плагина - составляющих элементов триггеров
  $pagination - блок навигациицам 
  $trigger - триггеры 
-->

<div class="section-<?php echo $pluginName ?>">
    <!-- Тут начинается Верстка модального окна -->

    <div class="reveal-overlay" style="display:none;">
        <div class="reveal xssmall add-trigger-element" id="add-plug-modal" style="display:block;">
            <button class="close-button closeModal" type="button"><i class="fa fa-times-circle-o" aria-hidden="true"></i></button>
            <div class="reveal-header">
                <h4><i class="fa fa-plus-circle" aria-hidden="true"></i> <span id="modalTitle"><?php echo $lang['HEADER_MODAL_ADD']; ?></span></h4>
            </div>
            <div class="reveal-body slide-editor">

                <span class="label-text">Иконка: </span> <input type="hidden" name="icon" value=""/>
                <div class="clear"></div>
                <div class="img">
                    <img style="width:100px; height:100px;" class="icon-trigger" src="" />
                </div>
                <div class="btn-holder">
                    <a href="javascript:void(0);" class="browseImage tool-tip-top custom-btn button success" title="Загрузить логотип">
                        <span><i class="fa fa-download"></i> Загрузить картинку</span>
                    </a>
                    <a href="javascript:void(0);" class="choose-icon tool-tip-top custom-btn button" title="Выбрать из существующих иконок">
                        <span><i class="fa fa-check"></i> Выбрать иконку</span>
                    </a>                    
                </div>
                <div class="font-awesome-icons 11">
                    <div class="link-result">
                        Для выобра иконки нажмите на нее
                    </div>
                </div>

                <div class="clear"></div>
                <span class="label-text">Описание: </span> <textarea name="trigger_html_content"></textarea>
                <div class="clear"></div>
            </div>

            <div class="reveal-footer clearfix">
                <a class="button success fl-right save-button tool-tip-bottom"  data-id="" title="<?php echo $lang['SAVE_MODAL'] ?>" href="javascript:void(0);"><i class="fa fa-floppy-o" aria-hidden="true"></i> <?php echo $lang['SAVE_MODAL'] ?></a>
            </div>
        </div>
    </div>


    <div class="widget-table-wrapper">
        <div id="trigger-guarantee-tabs">
            <ul class="tabs-list template-tabs-menu">
                <li class="is-active template-tabs button primary" >
                    <a href="javascript:void(0);" class="tool-tip-top open-trigger" title="<?php echo $lang['T_TIP_TAB_NEW']; ?>"><span><?php echo $lang['NEW_TRIGGER']; ?></span></a>
                </li>
                <?php foreach ($trigger as $trig): ?>
                  <li class="template-tabs button primary">
                      <a href="javascript:void(0);" class="tool-tip-top open-trigger" id="<?php echo $trig['id'] ?>" title="Открыть редактирование триггера '<?php echo $trig['title']; ?>'">
                          <span><?php echo ($trig['title'] ? $trig['title'] : 'Триггер №'.$trig['id']); ?></span></a>
                  </li>
                <?php endforeach; ?>
            </ul>
            <div class="clear"></div>
            <div class="tabs-content">
                <!--Раздел настроек -->
                <div class="main-settings-container" id="tab-new-form">
                    <div class="main-settings-trigger">
                        <ul>
                            <li>
                                <span class="label-text">Заголовок триггера <span class="help-text">(не обязательно)</span>:</span>
                                <input type="text" name="title-trigger">
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="add-new-button tool-tip-top button success" title="<?php echo $lang['T_TIP_NEW_ELEM']; ?>"><i class="fa fa-plus-circle"></i> <span><?php echo $lang['ADD_NEW_ELEM']; ?></span></a>
                                <a href="javascript:void(0);" class="button add-exist-button tool-tip-top" title="<?php echo $lang['T_TIP_EXIST_ELEM']; ?>"><i class="fa fa-files-o"></i> <span><?php echo $lang['ADD_EXIST_ELEM']; ?></span></a>
                            </li>
                            <li>
                                <div class="link-result">
                                    Шорт-код для вставки триггера:
                                    <span class="mg-code"><span>[</span>trigger-guarantee id="<span class="short-code" data-id="<?php echo $nextIdTrig['nextid'] ? $nextIdTrig['nextid'] : 1; ?>">
                                            <?php echo ($nextIdTrig['nextid'] ? $nextIdTrig['nextid'] : 1); ?></span>"<span>]</span>
                                    </span>
                                </div>
                            </li>
                        </ul>
                        <div class="table-trigger">
                            <table class="widget-table main-table">
                                <tbody class="trigger-guarantee-elements">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="base-setting">
                        <h3>Настройка триггера:</h3>
                        <ul class="list-option" data-propertyid="<?php echo ($nextIdTrig['nextid'] ? $nextIdTrig['nextid'] : 1) ?>">
                            <li>
                                <label>
                                    <span class="setting" >Форма иконки:</span>
                                    <select name="form">
                                        <option value="square" <?php echo ($settings['form'] == 'square' ? 'selected' : ''); ?>>Квадрат</option>
                                        <option value="circle" <?php echo ($settings['form'] == 'circle' ? 'selected' : ''); ?>>Круг</option>
                                    </select>
                                </label>
                            </li>
                            <li>
                                <label>
                                    <span class="setting">Расположение иконки:</span>
                                    <select name="place">
                                        <option value="left" <?php echo ($settings['place'] == 'left' ? 'selected' : ''); ?>>Слева</option>
                                        <option value="top" <?php echo ($settings['place'] == 'top' ? 'selected' : ''); ?>>Над текстом</option>
                                    </select>
                                </label>
                            </li>
                            <li>
                                <span class="setting">Выбрать цвет иконки:</span>
                                <div class="color-picker">
                                    #<input type="text" id="picker" name="color_icon" value="<?php echo ($settings['color_icon'] ? $settings['color_icon'] : "000"); ?>"
                                            style="border-color: <?php if ($settings['color_icon']) echo $settings['color_icon'];
                                            else echo "#000"; ?>">
                                </div>

                            </li>
                            <li>
                                <span class="setting">Выбрать фон иконки:</span>
                                <div class="color-picker">
                                    #<input type="text" id="picker" name="background_icon" value="<?php echo ($settings['background_icon'] ? $settings['background_icon'] : "fff"); ?>"
                                            style="border-color: <?php echo($settings['background_icon'] ? $settings['background_icon'] : "#fff"); ?>">
                                </div>
                            </li>
                            <li>
                                <span class="setting">Выбрать фон всего элемента:</span>
                                <div class="color-picker">
                                    #<input type="text" id="picker" name="background" value="<?php echo ($settings['background'] ? $settings['background'] : "fff"); ?>"
                                            style="border-color: <?php echo($settings['background'] ? $settings['background'] : "#fff"); ?>">
                                </div>
                            </li>
                            <li>
                                <span class="setting">Ширина:</span>
                                <span><input name="width" type="number" min="0" max="1000" step="1" value="31" ></span>
                                <select name="unit">
                                    <option value="1" selected>%</option>
                                    <option value="2">px</option>
                                </select>
                            </li>
                            <li>
                                <span class="setting">Высота в px:</span>
                                <span><input name="height" type="number" min="0" max="1000" step="1" value="100"> </span> <span class="trigger-height"> 260px</span>
                            </li>
                            <li>
                                <label>
                                    <span class="setting">Вывод триггера:</span>
                                    <select name="layout">
                                        <option value="vertleft">Вертикально слева</option>
                                        <option value="vertright">Вертикально справа</option>
                                        <option value="horfloat">Горизонтально в ряд с обтеканием</option>
                                        <option value="horiz">Горизонтально в ряд без обтекания</option>
                                        <option value="column">Две колонки</option>
                                    </select>
                                </label>
                            </li>
                            <li>
                            <span class="setting">Размер иконки:</span>
                              <input type="number" name="fontSize" step="0.25" value="4.5">
                            </li>
                        </ul>
                        <div class="clear"></div>
                    </div>
                    <div class="btn-holder">
                        <button class="save-button trigger tool-tip-bottom button success" data-nextid="<?php echo ($nextIdTrig['nextid'] ? $nextIdTrig['nextid'] : 1) ?>"
                                title="<?php echo $lang['SAVE_TRIGGER'] ?>">
                            <span><i class="fa fa-floppy-o"></i> <?php echo $lang['SAVE_TRIGGER'] ?></span>
                        </button>
                        <a href="javascript:void(0);" class="button secondary delete-trigger">
                            <span><i class="fa fa-times"></i> <?php echo $lang['DELETE'] ?></span>
                        </a>
                    </div>
                </div>
                <div style="padding:3px;"></div>
            </div>
        </div>
    </div>
    <div class="widget-table-wrapper">
        <!-- Тут начинается верстка таблицы всех элементов триггеров-->
        <!-- Тут начинается верстка таблицы сущностей  -->
        <div class="entity-settings-table-wrapper trigger-guarantee-all-elements"  style="display:none" >
            <span class="link-result">При нажатии на элемент, он будет скопирован и добавлен к триггеру.</span>
            <div class="button secondary close-trigger-table">
                <span><i class="fa fa-times"></i> <?php echo $lang['CLOSE'] ?></span>
            </div>
            <table class="widget-table trigger-guarantee-table main-table">
                <thead>
                    <tr>
                        <th>id</th>
                        <th>Элемент</th>
                        <th>Триггер</th>
                    </tr>
                </thead>
                <tbody class="entity-table-tbody trigger-guarantee-tbody ">
                    <?php if (empty($entity)): ?>
                      <tr class="no-results">
                          <td colspan="4" align="center"><?php echo $lang['ENTITY_NONE']; ?></td>
                      </tr>
                    <?php else: ?>
                        <?php foreach ($entity as $row): ?>
                            <tr data-id="<?php echo $row['id']; ?>">
                                <td>
                                    <?php echo $row['id'] ?>
                                </td>
                                <td style="width: initial;">
                                    <div class="trigger-item">
                                        <span class="trigger-icon"><?php echo $row['icon'] ?></span>
                                        <span class="trigger-text"><?php echo $row['text'] ?></span>
                                    </div>
                                </td>
                                <td class="parent" data-parent="<?php echo $row['parent'] ?>">
                                    <?php echo ($row['parent'] != '0' ? 'Триггер №'.$row['parent'] : '') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>



</div>
<script>
  $('.section-trigger-guarantee .color-picker #picker').colpick({
    layout: 'hex',
    submit: 0,
    colorScheme: 'dark',
    onChange: function (hsb, hex, rgb, el, bySetColor) {
      $(el).css('cssText', 'border-color: #' + hex + ' !important');
      $(el).val(hex);
      // Fill the text box just if the color was set using the picker, and not the colpickSetColor function.
      if (!bySetColor)
        $(el).val(hex);
      triggerGuarantee.applySettings();
    }
  }).keyup(function () {
    $(this).colpickSetColor(this.value);
  });
  admin.sortable('.trigger-guarantee-elements', 'trigger-guarantee-elements');
</script>



