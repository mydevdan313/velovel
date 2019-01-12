var comments = (function(){
	return {
		init: function(){
			//сохранение настроек
			$('.admin-center').on('click', '.section-comments .base-setting-save', function() {

				admin.ajaxRequest({
		          pluginHandler: 'comments', // имя папки в которой лежит данный плагин
		          actionerClass: "Comments", // класс Comments в comments.php - в папке плагина
		          action: "saveSettings", // название действия в пользовательском  классе Comments
		          maxHeight: $('input[name=maxHeight]').val(),
		          maxWidth: $('input[name=maxWidth]').val(),
		          maxHeightThumb: $('input[name=maxHeightThumb]').val(),
		          maxWidthThumb: $('input[name=maxWidthThumb]').val(),
		          useFiles: $('#useFiles').prop('checked'),

		        },
		        function(response) {
		          admin.indication(response.status, "Сохранено");
		        });
			});

			// $(".comments .fancybox").fancybox();
			// $.fancybox($('.fancybox'));

			if(typeof $.fancybox != 'function') {
				$(".comments .fancybox").fancybox({
				    'overlayShow': false,
				});
				$(".admin-center .fancybox").fancybox({
				    'overlayShow': false,
				});
			}

			// Показывает панель с настройками.
			$('.admin-center').on('click', '.section-comments .base-setting-open', function() {
				$('.base-settings').slideToggle();
			});


			// показ дропзоны
			$('.comments .showImgComments').on('click', function(){
				$(this).hide();
				$(this).parents('.comments').find('.comments-dropZone').show();
			})

			// Отправка комментария на сервер для обработки
			$('.comments .sendComment').on('click', function(e){
				comments.sendComment(e);   
				return false;
			})	

			//превью изображений
			$('.comments .comments_file_input').on('change', function(e){
				comments.previewImages(this);
			})

			// Открытие окна изменение комментария
			$('.admin-center').on('click', '.section-comments .edit-row', function(e){				
				comments.openModalWindow($(this).attr('id'));
				return false;
			})

			// Обработка нажатия кнопки удаления комментария
			$('.admin-center').on('click', '.section-comments .delete-order', function(e){
				e.preventDefault();
				var res = confirm("Удалить комментарий?");
				if(res){
					comments.deleteComment($(this).attr('id'));
				}
			})

			// Обработка нажатия кнопки сохранения комментария
			$('.admin-center').on('click', '.section-comments .save-button', function(e){
		//		$('#overlay').remove();
				comments.editComment();
			})

			// Очищаем поля при закрытии модального окна
			$('.admin-center').on('click', 'section-comments #comment-modal_close', function(){
				comments.clearFields();
			})

			// Устанавливает количиство выводимых записей в этом разделе.
			$('.admin-center').on('change', '.section-comments .countPrintRowsPage', function(){

	        var count = $(this).val();
	        admin.ajaxRequest({
	          pluginHandler: 'comments', // имя папки в которой лежит данный плагин
	          actionerClass: "Comments", // класс Comments в comments.php - в папке плагина
	          action: "setCountPrintRowsComments", // название действия в пользовательском  классе Comments
	          count: count
	        },
	        function(response) {
	          admin.refreshPanel();
	        }
	        );

	      });

		},

		// Открытие модального окна и заполнение полей из БД
		openModalWindow: function(id){
			comments.clearFields();
			admin.openModal($('#comment-modal'));      
			comments.fillFields(id);
		},

		previewImages: function(input){
			var $preview = $('.comments_preview').empty();
			if (input.files && input.files[0]) {
				var filesAmount = input.files.length;
				$('.comments_preview').show();
				for (i = 0; i < filesAmount; i++) {

					if (!/\.(jpe?g|png|gif)$/i.test(input.files[i].name)){
						$('.comments_preview').hide();
						var txt = "Формат файла "+input.files[i].name +" не поддерживается (поддерживаются только jpeg, jpg, png, gif)";
						$('.comments .comments_file_input').val('');
						$('.comments_preview').empty();
						return alert(txt);
					}
	                var reader = new FileReader();

	                reader.onload = function(event) {
	                    $($.parseHTML('<img class="comments_img">')).attr('src', event.target.result).appendTo($preview);
	                }

	                reader.readAsDataURL(input.files[i]);
	            }
			}
		},

		// Функция заполнения полей из БД
		fillFields: function(id){
			admin.ajaxRequest({
					pluginHandler: 'comments', // имя папки в которой лежит данный плагин
          actionerClass: "Comments", // класс Comments в comments.php - в папке плагина
          action: "getCommentById", // название действия в пользовательском  классе Comments
          id: id
        },
        function(response) {
        	console.log(response);
        	$('#comment-modal input[name=name]').val(response.data.name);
        	$('#comment-modal input[name=email]').val(response.data.email);
        	$('#comment-modal select option[value=' + response.data.approved + ']').prop('selected', 'selected');
        	$('#comment-modal textarea').val(response.data.comment);
          
			var commentUrl=$('#comment-modal .commentUrl');
			var link = commentUrl.data('site')+response.data.uri;
			commentUrl.attr('href', link);
			commentUrl.text(link);
         
        	$('#comment-modal button.save-button').attr('id', id);

        	if (response.data.img.length > 1) {
        		$('#comment-modal .img-label').show();
        		var imgArr = response.data.img.split('|');
        		for (var i = 0; i < imgArr.length; i++) {
					$('<a class="fancybox" href="'+mgBaseDir+'/uploads/comments/'+imgArr[i]+'"><img src="'+mgBaseDir+'/uploads/comments/thumbs/'+imgArr[i]+'"/></a>').appendTo('#comment-modal .img-container');
        		}
        	}
        },
        
        $('.add-product-form-wrapper')
        );
		},

		// Функция отправляет запрос для изменения комментария
		editComment: function(){
      
      var id = $('#comment-modal button.save-button').attr('id');
      var name = $('#comment-modal input[name=name]').val();
      var email = $('#comment-modal input[name=email]').val();
      var comment = $('#comment-modal textarea').val();
      var approved = $('#comment-modal select').val();

			var data = {
				pluginHandler: 'comments', // имя папки в которой лежит данный плагин
        actionerClass: "Comments", // класс Comments в comments.php - в папке плагина
        action: "saveComment", // название действия в пользовательском  классе Comments
				id: id,
				name: name,
				email: email,
				comment: comment,
				approved: approved
			}

			admin.ajaxRequest(
				data,
			function(response){  
        
        comments.indicatorCount(response.data.count);
        $('.comments-tbody tr[id='+id+']').replaceWith(        
            '<tr id="'+id+'">\
	          	<td class="c-name">'+name+'</td>\
	          	<td class="c-email">'+email+'</td>\
	          	<td class="c-approved"><span class="'+((approved==1)?'approved-comment':'n-approved-comment')+'">'+((approved==1)?'Одобрен':'Не одобрен')+'</span></td>\
	          	<td class="actions">\
	          		<ul class="action-list">\
	          			<li class="edit-row" id="'+id+'"><a class="tool-tip-bottom fa fa-pencil" href="#" title=""></a></li>\
                  <li class="delete-order" id="'+id+'"><a class="tool-tip-bottom fa fa-trash" href="#" title=""></a></li>\
	          		</ul>\
	          	</td>\
          	</tr>');
    
        admin.indication(response.status, response.msg);
        admin.closeModal($('#comment-modal'));
				//comments.clearFields();
			});
		},

    // меняеn индикатор количества новых комментариев
    indicatorCount: function(count) {       
        if(count==0){
          $('.button-list a[rel=comments]').parents('li').find('.comment-wrap').hide();
        } else {
          $('.button-list a[rel=comments]').parents('li').find('.comment-wrap').show();
           $('.button-list a[rel=comments]').parents('li').find('.comment-wrap').text(count);
        }
    },
            
		// Функция отправляет запрос на удаления комментария
		deleteComment: function(id){
			admin.ajaxRequest({
				pluginHandler: 'comments', // имя папки в которой лежит данный плагин
        actionerClass: "Comments", // класс Comments в comments.php - в папке плагина
        action: "deleteComment", // название действия в пользовательском  классе Comments
				id: id
			},
			function(response){
        admin.indication(response.status, response.msg);
        comments.indicatorCount(response.data.count);
			//	$('li.edit-row#'+id).parent().parent().parent().remove();
        $('.comments-tbody tr[id='+id+']').remove();          
    
			})
		},

		// Функция очищает поля формы правки комментария
		clearFields: function(){
			$('#comment-modal .input[type=text]').val('');
			$('#comment-modal textarea').val('');
			$('#comment-modal select option[value=0]').removeAttr('selected');
			$('#comment-modal select option[value=1]').removeAttr('selected');
      		$('#comment-modal .commentUrl').text('');
      		$('#comment-modal .img-label').hide();
      		$('#comment-modal .img-container').html('');
		},

		// Функция отправляет комментарий из формы отправки на странице сайта
		sendComment: function(e){
			e.stopPropagation();
			e.preventDefault();
			$('.comments .comments-msg').html('Подождите, идет отправка комментария...');

			$('.comments form').ajaxSubmit({
				type: "POST",
				url: mgBaseDir+"/ajaxrequest",
				data: {
					pluginHandler: 'comments', // имя папки в которой лежит данный плагин
					actionerClass: 'Comments', // класс Comments в Comments.php - в папке плагина
					// action: "addComment", // название действия в пользовательском  классе        
				},
				dataType: "json",
				cache: false,
				success: function(response){     
					if(response.status!="error"){
						$('.comments input[type=text]').val('');
						$('.comments textarea').val('');
						$('.comments .comments_file_input').val('');
						$('.comments_preview').empty();
					}
					$('.comments .comments-msg').html(response.msg);
				}
			});
		}		
	}
})();


$(document).ready(function(){
	comments.init();
});