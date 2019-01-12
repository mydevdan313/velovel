<!--
Доступны переменные:
  $pluginName - название плагина
  $lang - массив фраз для выбранной локали движка
  $options - набор данного плагина хранимый в записи таблиц mg_setting
  $entity - набор записей сущностей плагина из его таблицы
  $pagination - блок навигациицам 
-->

<div class="section-<?php echo $pluginName ?>"><!-- $pluginName - задает название секции для разграничения JS скрипта -->
    <!-- Тут начинается Верстка модального окна -->
        
         <div class="reveal-overlay" style="display:none;">
           <div class="reveal xssmall" id="brand-modal" style="display:block;"><!-- блок для контента модального окна -->
             <button class="close-button closeModal" type="button"><i class="fa fa-times-circle-o" aria-hidden="true"></i></button>  
             <div class="reveal-header"><!-- Заголовок модального окна -->
               <h4 class="pages-table-icon" id="modalTitle">
                           <?php echo $lang['HEADER_MODAL_ADD']; ?>
                       </h4><!-- Иконка + Заголовок модального окна -->
             </div>
             <div class="reveal-body">
                <h3>Описание бренда: </h3>
                <ul class="custom-form-wrapper mg-brand-info">        
                    <li>
                        <span style="float: left; width: 120px;">Бренд: </span>
                        <h2 class="brand-name activity-product-true" style="display:none"></h2>      
                        <input type="text" name="brand" value="" style="display:none"/>
                    </li>
                    <li>
                        <span>Описание: </span> <textarea name="desc" data-name="html_content"></textarea>              
                    </li>          
                    <li>
                        <span>Логотип: </span> <input type="hidden" name="logo" value=""/>
                        <img style="width:100px; height:100px;" class="logo-brand" src="" />
                        <div class="btn-holder">
                            <a href="javascript:void(0);" class="browseImage tool-tip-top link" title="Загрузить логотип">
                                <span>Загрузить логотип</span>
                            </a>
                        </div>
                    </li> 
                    <!-- SEO -->
                    <li></li>
                    <button class="custom-btn seo-gen-brand tool-tip-bottom" style="float:right; margin-top: -10px;"><a class="link"><span>Сгенерировать мета-теги</span></a></button>
                    <li><b>SEO настройки</b></li>
                    <li>
                      <span style="float: left; width: 120px;">Meta title: </span>
                      <input type="text" name="seo_title" id="seo_title" value="" style="width: calc(100% - 200px);">
                    </li>
                    <li>
                      <span style="float: left; width: 120px;">Meta keywords: </span>
                      <input type="text" name="seo_keywords" id="seo_keywords" value="" style="width: calc(100% - 200px);">
                    </li>
                    <li>
                      <span  style="float: left; width: 120px;">Meta description: </span>
                      <textarea class="product-meta-field tool-tip-bottom" name="seo_desc" id="seo_desc" title="" style="width: calc(100% - 200px)!important; margin-left: 3px;"></textarea>
                      <br><br><br>
                    </li>
                  <!-- end SEO -->                     		
                </ul>   

                
                <div class="clear"></div> 
            </div>
              <div class="reveal-footer clearfix">
                <button class="save-button tool-tip-bottom button success fa fa-save" data-id="" title="<?php echo $lang['SAVE_MODAL'] ?>"><!-- Кнопка действия -->
                    <span><?php echo $lang['SAVE_MODAL'] ?></span>
                </button>
                <div class="clear"></div>
              </div>
        </div>
    </div>
    <!-- Тут заканчивается Верстка модального окна -->
    <!-- Тут начинается верстка видимой части станицы настроек плагина-->
    <div class="widget-table-body">
        <div class="widget-table-action">
            <a href="javascript:void(0);" class="add-new-button tool-tip-top" title="<?php echo $lang['T_TIP_ADD_BRAND']; ?>"><button class="button primary" style="margin:10px;"><span><?php echo $lang['ADD_BRAND']; ?></span></button></a>
           <!--  <a href="javascript:void(0);" class="copy-old-characteristic custom-btn tool-tip-bottom" data-property="<?php echo $options['propertyId'] ?>"
              title="Если Вы добавили товары с новыми значениями брендов в строковой характеристике, Вы можете копировать эти значения 
              в характеристику 'Бренд'"><button class="button primary" style="margin:10px;"><span>Копировать значения из другой характеристики</span></button></a> -->
            <select class="changeBrand saveS" name="propertyId">
              <?php foreach ($propNames as $value) {
                viewData($id_prop);
                echo '<option value="'.$value['id'].'" '.($id_prop==$value['id']?'selected=selected':'').'>'.$value['name'].'</option>';
              } ?>
            </select>
            <a href="javascript:void(0);" class="button secondary syns" style="margin: 5px 10px;">Импортировать</a>
              
            <div class="filter">
                <span class="last-items"><?php echo $lang['SHOW_COUNT_BRAND']; ?></span>
                <select class="last-items-dropdown countPrintRowsEntity saveS">
                    <?php
                    foreach (array(10, 20, 30, 50, 100) as $value) {
                      $selected = '';
                      if ($value == $countPrintRows) {
                        $selected = 'selected="selected"';
                      }
                      echo '<option value="'.$value.'" '.$selected.' >'.$value.'</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="clear"></div>
        </div>         
        <?php if ($options['first'] == 'true') : ?>
          <div class="first-settings"> 
              <div class="link-result">
                  Внимание! Создана новая характеристика "Бренд". Если Вы уже используете
                  такую же или похожую характеристику, можете экспортировать значения и настройки
                  этой характеристики в новую. Старая характеристика будет неактивна для
                  вывода в фильтрах и в карточке товара, вместо нее будет выводится новая.
                  Все данные будут скопированы в новую характеристику. Экспортировать характеристику?
              </div>
              <div class="brand-buttons">
                  <a href="javascript:void(0);" class="no-old-characteristic custom-btn" data-property="<?php echo $options['propertyId'] ?>">
                      <button class="button primary"><span>Нет</span></button></a>
                  <a href="javascript:void(0);" class="export-old-characteristic custom-btn" data-property="<?php echo $options['propertyId'] ?>">
                      <button class="button primary"><span>Экспортировать</span></button></a>
              </div>
          </div>
        <?php endif; ?> 

        <div class="wrapper-entity-setting ">
            <?php if ($empty > 0) : ?>
              <div class="link-result"> Бренды без логотипов не выводятся в блоке на сайте! Добавьте логотип, чтобы бренд был выведен на сайте.</div>
            <?php endif; ?>
            <!-- Тут начинается верстка таблицы сущностей  -->
            <table class="widget-table mg-brand-table main-table">          
                <thead>
                    <tr>
                        <th style="width:40px;"></th>
                        <th>
                            Логотип
                        </th>
                        <th>
                            Название
                        </th>
                        <th>
                            Описание
                        </th>                                
                        <th class="actions text-right" style="width:15%"><?php echo $lang['ACTIONS']; ?>
                        </th>
                    </tr>
                </thead>
                <tbody class="entity-table-tbody mg-brand-tbody"> 
                    <?php if (empty($brand)): ?>
                      <tr class="no-results">
                          <td colspan="4" class="no-results" align="center"><?php echo $lang['ENTITY_NONE']; ?></td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($brand as $row): ?>
                        <tr data-id="<?php echo $row['id']; ?>" class="<?php echo $row['url'] == '' ? 'no-logo' : '' ?>">
                            <td class="mover"><i class="fa fa-arrows"></i></td>
                            <td class="logo">
                                <?php
                                $src = ($row['url'] ? $row['url'] : SITE.'/mg-admin/design/images/no-img.png');
                                ?>
                                <img class="uploads" src="<?php echo $src ?>"/>
                            </td>
                            <td class="brand"> 
                                <?php echo $row['brand'] ?>
                            </td>

                            <td class="desc">                                  
                                <?php echo $row['desc'] ?>                    
                            </td>  
                            <td class="actions text-right">
                                <ul class="action-list"><!-- Действия над записями плагина -->
                                    <li class="edit-row" 
                                        data-id="<?php echo $row['id'] ?>">
                                        <a class="fa fa-edit tool-tip-bottom" href="javascript:void(0);" 
                                           title="<?php echo $lang['EDIT']; ?>"></a>
                                    </li>                                           
                                    <li class="delete-row" 
                                        data-id="<?php echo $row['id'] ?>">
                                        <a class="fa fa-trash tool-tip-bottom" href="javascript:void(0);"  
                                           title="<?php echo $lang['DELETE']; ?>"></a>
                                    </li>
                                </ul>
                            </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="clear"></div>
    <div class="table-pagination clearfix" style="padding:10px;">
      <?php echo $pagination ?>  <!-- Вывод навигации -->
    </div>
    <div class="clear"></div>
</div>
<script>
  admin.sortable('.mg-brand-tbody', 'brand-logo');
</script>