<script type="text/javascript">
includeJS('../mg-plugins/comments/js/comments.js');
</script>
<link rel="stylesheet" href="../mg-plugins/comments/css/style.css" type="text/css" />

<div class="section-comments">
	<!-- Верстка модального окна -->
    <div class="reveal-overlay" style="display:none;">
      <div class="reveal xssmall" id="comment-modal" style="display:block;"><!-- блок для контента модального окна -->
        <button class="close-button closeModal" type="button"><i class="fa fa-times-circle-o" aria-hidden="true"></i></button>  
        <div class="reveal-header"><!-- Заголовок модального окна -->
          <h4 class="pages-table-icon" id="modalTitle"><?php echo $lang['COMMENTS_MODAL_TITLE'];?></h4>
        </div>
        <div class="reveal-body"><!-- Содержимое окна, управляющие элементы -->      
          <div class="add-product-form-wrapper">
             <label><span class="custom-text">Страница:</span>
               <a style="margin-left:17px;" target="_blank" class='commentUrl' href="<?php echo SITE?>/" data-site="<?php echo SITE?>" title="Страница на которой оставлен комментарий"><?php echo SITE?>/</a></label>
         		<label><span class="custom-text">Имя:</span>
         		<input type="text" name="name" value="" class="product-name-input tool-tip-right" title="<?php echo $lang['T_TIP_COMMENTS_NAME'];?>"></label>
         		<label><span class="custom-text">Email:</span>
         		<input type="text" name="email" value="" class="product-name-input tool-tip-right" title="<?php echo $lang['T_TIP_COMMENTS_EMAIL'];?>"></label>
         		<label><span class="custom-text">Статус:</span>
         		<select class="last-items-dropdown tool-tip-right" title="<?php echo $lang['T_TIP_COMMENTS_STATUS'];?>">
         			<option value="0">Не одобрен</option>
         			<option value="1">Одобрен</option>
         		</select></label>
         		<label><span class="custom-text">Комментарий:</span>
         		<textarea name="comment" class="product-desc-field" title="<?php echo $lang['T_TIP_COMMENTS_COMMENT'];?>"></textarea></label>
            <label class="img-label"><span class="custom-text">Изображения:</span>
              <div class="img-container"></div>
            </label>
            </div>
          </div>
        <div class="reveal-footer clearfix">
              <button class="save-button tool-tip-bottom button success" title="<?php echo $lang['T_TIP_SAVE_COMMENT'];?>"><span><i class="fa fa-floppy-o"></i> <?php echo $lang['SAVE'];?></span></button>
            <div class="clear"></div>
          </div>
      </div>
    </div>

		<!-- Тут заканчивается Верстка модального окна -->


    <!-- Тут начинается  Верстка таблицы товаров -->

    <div class="widget-table-body">
      <div class="widget-table-action">
        <button class="base-setting-open button"><span><i class="fa fa-cogs"></i> Настройки</span></button>
      	<div class="filter fl-right" style="float:right;">
          <span class="last-items"><?php echo $lang['COMMENTS_COUNT'];?></span>
          <select class="last-items-dropdown countPrintRowsPage" style="width:60px;">
            <?php
            foreach(array(5, 10, 15, 20, 25, 30, 100, 150) as $value){
              $selected = '';
              if($value == $countPrintRowsComments){
                $selected = 'selected="selected"';
              }
              echo '<option value="'.$value.'" '.$selected.' >'.$value.'</option>';
            }
            ?>
          </select>
        </div>
        <div class="clear"></div>
      </div>

      <div class="widget-table-action base-settings" style="display:none;">
        <h3>Настройки загрузки изображений</h3>
        <div class="large-6 small-12 columns">
          <div class="row">
            <div class="large-6 columns">
              <span>Включить загрузку изображений:</span>
            </div>
            <div class="large-6 columns checkbox">
              <input type="checkbox" id="useFiles" <?php if ($options['useFiles'] == 'true') {echo 'checked';} ?>>
              <label for="useFiles"></label>
            </div>
          </div>
          <div class="row">
            <div class="large-6 columns">
              <span>Максимальная ширина изображений (px):</span>
            </div>
            <div class="large-6 columns">
              <input type="text" name="maxWidth" value="<?php echo $options['maxWidth']; ?>">
            </div>
          </div>
          <div class="row">
            <div class="large-6 columns">
              <span>Максимальная высота изображений (px):</span>
            </div>
            <div class="large-6 columns">
              <input type="text" name="maxHeight" value="<?php echo $options['maxHeight']; ?>">
            </div>
          </div>
          <div class="row">
            <div class="large-6 columns">
              <span>Максимальная ширина миниатюр (px):</span>
            </div>
            <div class="large-6 columns">
              <input type="text" name="maxWidthThumb" value="<?php echo $options['maxWidthThumb']; ?>">
            </div>
          </div>
          <div class="row">
            <div class="large-6 columns">
              <span>Максимальная высота миниатюр (px):</span>
            </div>
            <div class="large-6 columns">
              <input type="text" name="maxHeightThumb" value="<?php echo $options['maxHeightThumb']; ?>">
            </div>
          </div>
          <div class="row">
            <div class="large-6 columns">
            </div>
            <div class="large-6 columns">
              <button class="base-setting-save button success"><span><i class="fa fa-floppy-o"></i> Сохранить</span></button>
            </div>
          </div>
        </div>    
        <div class="clear"></div>
      </div>

      <div class="main-settings-container">
        <table class="widget-table product-table main-table" style="width:100%; " >
          <thead>
            <tr >
              <th class="c-name" style="width:20%; text-align:center;"><?php echo $lang['COMMENTS_NAME'];?></th>
              <th class="c-email" style="width:30%; text-align:center;"><?php echo $lang['COMMENTS_EMAIL'];?></th>              
              <th class="c-approved" style="text-align:center;"><?php echo $lang['COMMENTS_APPROVE'];?></th>
              <th class="actions" style="width:20%; text-align:center;"><?php echo $lang['COMMENTS_ACTIONS'];?></th>
            </tr>
          </thead>
          <tbody class="comments-tbody">
          	<?php if(!empty($comments)){ ?>
          	<?php foreach($comments as $comment): ?>
          	<tr id = "<?php echo $comment['id']; ?>">
	          	<td class="c-name"><?php echo $comment['name']; ?></td>
	          	<td class="c-email"><?php echo $comment['email']; ?></td>	          	
	          	<td class="c-approved"><?php echo $comment['approved'] ? '<span class="approved-comment">Одобрен</span>' : '<span class="n-approved-comment">Не одобрен</span>'; ?></td>
	          	<td class="actions">
	          		<ul class="action-list">
	          			<li class="edit-row" id="<?php echo $comment['id'] ?>"><a class="tool-tip-bottom fa fa-pencil" href="#" title="<?php echo $lang['EDIT'];?>"></a></li>
                  <li class="delete-order" id="<?php echo $comment['id'] ?>"><a class="tool-tip-bottom fa fa-trash" href="#"  title="<?php echo $lang['DELETE'];?>"></a></li>
	          		</ul>
	          	</td>
          	</tr>
          	<?php endforeach; ?>
          	<?php } else{ ?>
						<tr>
							<td colspan="5"><?php echo $lang['NEWS_NONE']; ?></td>
						</tr>
          	<?php } ?>
          </tbody>
        </table>
    	</div>
      <div class="table-pagination">
    	   <?php echo $pagination ?>
      </div>
      <div class="clear"></div>
		</div>
	</div>