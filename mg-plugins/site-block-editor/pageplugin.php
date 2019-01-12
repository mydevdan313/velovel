<div class="section-<?php echo $pluginName?>">

<!-- Тут начинается Верстка модального окна -->
<div class="reveal-overlay" style="display:none;">
  <div class="reveal xssmall modal-block-editor" style="display:block;">
    <button class="close-button closeModal" type="button"><i class="fa fa-times-circle-o" aria-hidden="true"></i></button>
    <div class="reveal-header">
      <h2><i class="fa fa-plus-circle" aria-hidden="true"></i> <span id="modalTitle">Редактирование блока &#91;site-block id=<span id="block-code"></span>&#93;</span></h2>
    </div>
    <div class="reveal-body">

           <ul class="text-list">
               <li>
                   <span style="width:80px;display:inline-block;">Тип блока:</span>
                   <select name="type" style="width:180px">
                       <option value="img">Изображение</option>
                       <option value="html">HTML код</option>
                   </select>
               </li>
           </ul>
            <div class="block-for-form" >
                <ul class="custom-form-wrapper type-img text-list">
                    <li><span style="width:80px;">src =</span> <input class="medium" type="text" name="src" value=""/> <a href="javascript:void(0);" class='browseImage link'>выбрать изображение</a> </li>
                    <li><span style="width:80px;">alt =</span> <input class="medium" type="text" name="alt" value=""/>
                        <a href="javascript:void(0);" class="desc-property fa fa-question-circle" title="Текст который будет выведен, если картинка не будет загружена"></a></li>
                    <li><span style="width:80px;">title =</span> <input class="medium" type="text" name="title" value=""/>
                        <a href="javascript:void(0);" class="desc-property fa fa-question-circle" title="Текст который будет выведен при наведении на картинку"></a></li>
                    <li><span style="width:80px;">href =</span> <input class="medium" type="text" name="href" value=""/>
                        <a href="javascript:void(0);" class="desc-property fa fa-question-circle" title="Ссылка, куда будет перенаправлен при нажатии на картинку. Если оставить поле пустным, перенаправлений не будет"></a></li>
                    <li><span style="width:80px;">высота =</span> <input class="medium" type="text" name="height" value=""/>
                        <a href="javascript:void(0);" class="desc-property fa fa-question-circle" title="Высота избражения, если оставить пустым, будем использована полная высота изображения"></a></li>
                    <li><span style="width:80px;">ширина =</span> <input class="medium" type="text" name="width" value=""/>
                        <a href="javascript:void(0);" class="desc-property fa fa-question-circle" title="Ширина избражения, если оставить пустым, будем использована полная ширина изображения"></a></li>
                    <li><span style="width:80px;">класс =</span> <input class="medium" type="text" name="class" value=""/>
                        <a href="javascript:void(0);" class="desc-property fa fa-question-circle" title="Класс, который будет применен к картинке, он позволит с помощью свойств css изменять параметры отображения блока"></a></li>
                </ul>
                <form class="type-html" style="margin: 0 -15px;">
                    <ul class="custom-form-wrapper text-list">
                        <li><textarea class="slide-html"> </textarea></li>
                    </ul>
                </form>
            </div>
            <br>
            <ul style="margin:0;padding:0;" class="text-list">
                <li>
                    <span class="custom-text" style="width:146px;">Комментарий к блоку:</span>
                    <input type="text" name="comment" value="" class="large"/>
                </li>
            </ul>
        </div>
    <div class="reveal-footer clearfix text-right">
      <button class="save-button tool-tip-bottom button success" data-id="" title="<?php echo $lang['SAVE_MODAL'] ?>" style="margin:0;"><!-- Кннопка действия -->
            <span>Сохранить</span>
        </button>
    </div>

  </div>
</div>
<!-- Тут заканчивается Верстка модального окна -->
<!-- Тут начинается  Верстка таблицы -->
<!-- <div class="widget-table-body"> -->
    <div class="wrapper-slider-setting">

        <div class="widget-table-action slide-settings">
            <div class="plugin-padding">
                <div class="add-new-button tool-tip-bottom button primary" title="Добавить новый шорткод">
                    <span>Добавить блок сайта</span>
                </div>
            </div>

            <div class="clear"></div>
            <div class="slide-settings-table-wrapper">
                <table class="main-table">
                    <thead>
                    <tr>
                        <th style="width:150px;">Шорткод блока</th>
                        <th>Комментарий к блоку</th>
                        <th style="min-width:100px; text-align: center;">Содержимое блока</th>
                        <th style="min-width:80px" class="text-right">Действия</th>
                    </tr>
                    </thead>
                    <tbody class="entity-table-tbody">
                    <?php if(empty($entity)): ?>
                        <tr class="no-results">
                            <td colspan="4" align="center">Шорткодов не обнаружено</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($entity as $row): ?>
                            <tr data-id="<?php echo $row['id']; ?>">
                                <td>[site-block id=<?php echo $row['id']; ?>]</td>
                                <td><?php echo $row['comment']; ?></td>
                                <td class="type">
                                  <?php
                                    if($row['type']=="img"){
                                      echo '<img height="50px" style="max-width:300px;" src="'.$row['content'].'">';
                                    }else{
                                      echo substr(strip_tags($row['content']),0,200);
                                    }
                                  ?>
                                </td>
                                <td class="actions text-right">
                                    <ul class="action-list"><!-- Действия над записями плагина -->
                                      <li class="edit-row" data-id="<?php echo $row['id'] ?>" data-type="<?php echo $row['type']; ?>"><a class="tool-tip-bottom fa fa-pencil" href="javascript:void(0);" title="Редактировать шорткод"></a></li>
                                      <li class="delete-row" data-id="<?php echo $row['id'] ?>"><a class="tool-tip-bottom fa fa-trash" href="javascript:void(0);"  title="Удалить шорткод"></a></li>
                                    </ul>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

</div>
