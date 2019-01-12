/**
 /* 
 * Модуль  sliderActionModule, подключается на странице настроек плагина.
 */

var sliderActionModule = (function() {
  
  return { 
    lang: [], // локаль плагина 
    init: function() {     
      // установка локали плагина 
      admin.ajaxRequest({
          mguniqueurl: "action/seLocalesToPlug",
          pluginName: 'slider-action'
        },
        function(response) {
          sliderActionModule.lang = response.data;        
        }
      );        
        
      // Выводит модальное окно для добавления
      $('.admin-center').on('click', '.section-slider-action .add-new-button', function() {    
        sliderActionModule.showModal('add');
        sliderActionModule.changeType('img');
      });
      
      // Выводит модальное окно для редактирования
      $('.admin-center').on('click', '.section-slider-action .edit-row', function() {       
        var id = $(this).data('id');
        sliderActionModule.showModal('edit', id);
        sliderActionModule.changeType($(this).data('type'));        
      });
      
       // Сохраняет изменения в модальном окне
      $('.admin-center').on('click', '.section-slider-action #add-plug-modal .save-button', function() {
        var id = $(this).data('id');    
        sliderActionModule.saveField(id);        
      });
      
     // Нажатие на кнопку - активности
      $('.admin-center').on('click', '.section-slider-action .visible', function(){    
        $(this).toggleClass('active');  
        $(this).find('a').toggleClass('active');  
        var id = $(this).data('id');
        if($(this).hasClass('active')) { 
          sliderActionModule.visibleEntity(id, 1); 
          $(this).attr('title', lang.ACT_V_ENTITY);
        }
        else {
          sliderActionModule.visibleEntity(id, 0);
          $(this).attr('title', lang.ACT_UNV_ENTITY);
        }
        $('#tiptip_holder').hide();
        admin.initToolTip();
      });
      
      // Удаляет запись
      $('.admin-center').on('click', '.section-slider-action .delete-row', function() {
        var id = $(this).data('id');
        sliderActionModule.deleteEntity(id);
      });
      
       // Сохраняет базовые настроки запись
      $('.admin-center').on('click', '.section-slider-action .base-setting-save', function() {
   
     
      var obj = '{';
      $('.list-option input, .list-option select').each(function() {     
        obj += '"' + $(this).attr('name') + '":"' + $(this).val() + '",';
      });
      obj += '}';    
    

      //преобразуем полученные данные в JS объект для передачи на сервер
      var data =  eval("(" + obj + ")");
      data.html_content_before = $('textarea[name=html_content_before]').val();
      data.html_content_after = $('textarea[name=html_content_after]').val();	 	 
	    data.nameaction = $(".base-settings input[name=nameaction]").val();

      admin.ajaxRequest({
        mguniqueurl: "action/saveBaseOption", // действия для выполнения на сервере
        pluginHandler: 'slider-action', // плагин для обработки запроса
        data: data // id записи
      },
      
      function(response) {
        admin.indication(response.status, response.msg);
        sliderActionModule.reloadSlider(response.data);        
      }
              
      );
        
      });
      
      
      // Выбор картинки слайдера
      $('.admin-center').on('click', '.section-slider-action .browseImage', function() {
        sliderSaveObject = this;
        admin.openUploader('sliderActionModule.getFile');
      });

      
      // Смена типа слайда
      $('.admin-center').on('change', '.section-slider-action .slide-editor select[name=type]', function() {
        sliderActionModule.changeType($(this).val());
      });
      
      
      // перезагрузка слайдера
      $('.admin-center').on('click', '.section-slider-action .reload-slider', function() {
         admin.ajaxRequest({
            mguniqueurl: "action/reloadSlider", // действия для выполнения на сервере
            pluginHandler: 'slider-action', // плагин для обработки запроса       
         },

         function(response) {  
            sliderActionModule.reloadSlider(response.data);      
         }

         );
      });   

      $('.admin-center').on('click', '.section-slider-action #add-plug-modal .additionalImage', function() {
        $('#srcsetli').show();
        $('<li class="additionalImageContainer"><input type="number" class="additionalImageWidth" value=""/><p>px и меньше</p><input type="text" class="additionalImageSrc imgSrc" placeholder="ссылка на изображение" value=""/> <a href="javascript:void(0);" class="browseImage">выбрать изображение</a> </li>').insertAfter('.section-slider-action #add-plug-modal #srcsetli');
      });    
      
    },
    
    // открытие модального окна
    showModal: function(type, id) {
      switch (type) {
        case 'add':
          {
            sliderActionModule.clearField();           
            break;
          }
        case 'edit':
          {
            sliderActionModule.clearField();
            sliderActionModule.fillField(id);
            break;
          }
        default:
          {
            break;
          }
      }

      admin.openModal($('#add-plug-modal'));      
      $('.section-slider-action #add-plug-modal textarea').ckeditor();  
    },
                 
   /**
    * функция для приема файла из аплоадера
    */         
    getFile: function(file) {
      $(sliderSaveObject).parents('li').find('.imgSrc').val(file.url);
      if (file.width && !$(sliderSaveObject).parents('li').find('.additionalImageWidth').val()) {
        $(sliderSaveObject).parents('li').find('.additionalImageWidth').val(file.width);
      }
    },      
            
   /**
    * Очистка модального окна
    */
    clearField: function() {
      $('.section-slider-action #add-plug-modal input').val('');
      $('.section-slider-action #add-plug-modal textarea').text('');
      $('.section-slider-action #add-plug-modal .id-entity').text('');
      $('.section-slider-action #add-plug-modal .save-button').data('id','');
      $('.section-slider-action #add-plug-modal .additionalImageContainer').remove();
      $('#srcsetli').hide();
    },
            
    /**
     * Заполнение модального окна данными из БД
     * @param {type} id
     * @returns {undefined}
     */        
    fillField: function(id) {

      admin.ajaxRequest({
        mguniqueurl: "action/getEntity", // действия для выполнения на сервере
        pluginHandler: 'slider-action', // плагин для обработки запроса
        id: id // id записи
      },
      
      function(response) {
        var content = response.data.value;
        var srcSet = $(content).attr('srcset');
        var src = $(content).attr('src');
        var alt = $(content).attr('alt');
        var title = $(content).attr('title');
     	   
	      $('.section-slider-action #add-plug-modal  input[name="nameaction"]').val(response.data.nameaction);	   
        $('.section-slider-action #add-plug-modal  input[name="src"]').val(src);
        $('.section-slider-action #add-plug-modal  input[name="alt"]').val(alt);
        $('.section-slider-action #add-plug-modal  input[name="title"]').val(title);
        $('.section-slider-action #add-plug-modal  input[name="href"]').val(response.data.href);
        $('.section-slider-action #add-plug-modal  textarea').val(content);          
         
        $('.section-slider-action #add-plug-modal .save-button').data('id',response.data.id);
        if (undefined != srcSet) {
          if (srcSet.length > 1) {
            var srcSetArr = srcSet.split(", ");
            srcSetArr.forEach(function (index, value) {
              var srcSetPiece = index.split(" ");
              // console.log(srcSetPiece);
              $('#srcsetli').show();
              $('<li class="additionalImageContainer"><input type="number" class="additionalImageWidth" value=""/><p>px и меньше</p><input type="text" class="additionalImageSrc imgSrc" placeholder="ссылка на изображение" value=""/> <a href="javascript:void(0);" class="browseImage">выбрать изображение</a> </li>').appendTo('.type-img');
              $('.additionalImageContainer:last .additionalImageSrc').val(srcSetPiece[0]);
              $('.additionalImageContainer:last .additionalImageWidth').val(srcSetPiece[1].slice(0,-1));
            });
          }
        }
        
      },
              
      $('#add-plug-modal .widget-table-body') // вывод лоадера в контейнер окна, пока идет загрузка данных
      
      );

    },
    
    /**
     * Сохранение данных из модального окна
     * @param {type} id
     * @returns {undefined}
     */        
    saveField: function(id) {
	    var nameaction = $('.section-slider-action .slide-editor input[name=nameaction]').val();
      var type = $('.section-slider-action .slide-editor select[name=type]').val();     
      var src = $('.section-slider-action #add-plug-modal input[name="src"]').val();
      var alt = $('.section-slider-action #add-plug-modal input[name="alt"]').val();
      var title = $('.section-slider-action #add-plug-modal input[name="title"]').val();
      var href = $('.section-slider-action #add-plug-modal input[name="href"]').val();
      var content = $('.section-slider-action #add-plug-modal textarea').val();
            
      if(type=='img'){
        if ($('.section-slider-action .additionalImageContainer').length > 0) {
          var srcSet = '';
          $('.section-slider-action .additionalImageContainer').each(function(index,element) {
            var srcSetWidth = $(this).find('.additionalImageWidth').val();
            var srcSetLink = $(this).find('.additionalImageSrc').val();
            if (srcSetWidth.length > 0 && srcSetLink.length > 0) {
              srcSet += srcSetLink+' '+srcSetWidth+'w, ';
            }
          });
          srcSet = srcSet.slice(0,-2);
          var value = "<img srcset='"+srcSet+"' src='"+src+"' alt='"+alt+"' title='"+title+"'>";
        }
        else{
          var value = "<img src='"+src+"' alt='"+alt+"' title='"+title+"'>";
        }
      } else {
        var value = content;
      }   
 
      admin.ajaxRequest({
        mguniqueurl: "action/saveEntity", // действия для выполнения на сервере
        pluginHandler: 'slider-action', // плагин для обработки запроса
        id: id,
        value: value,
        type: type,
		    nameaction: nameaction,
        href: href,     
      },
      
      function(response) {
        admin.indication(response.status, response.msg);
        if(id){
          var replaceTr = $('.entity-table-tbody tr[data-id='+id+']');
          sliderActionModule.drawRow(response.data.row,replaceTr); // перерисовка строки новыми данными
        } else{
          sliderActionModule.drawRow(response.data.row); // добавление новой записи         
        }        
        sliderActionModule.reloadSlider(response.data.slider);       
        admin.closeModal($('#add-plug-modal'));        
        sliderActionModule.clearField();
      },
              
      $('#add-plug-modal .widget-table-body') // на месте кнопки
      
      );

    },
    
    
    /**    
     * Отрисовывает  строку сущности в главной таблице
     * @param {type} data - данные для вывода в строке таблицы
     */        
    drawRow: function(data, replaceTr) {
      var invisible = data.invisible==='1'?'active':'';        
      var titleInvisible = data.invisible?lang.ACT_V_ENTITY:lang.ACT_UNV_ENTITY;  
     
      if(data.type=="img"){ 
        var type = data.value;
      } else{                
        var type = data.type;  
      }
      
      var tr = '\
       <tr data-id="'+data.id+'">\
        <td>'+data.id+'</td>\
        <td class="mover" style="width:10px; padding: 0"><i class="fa fa-arrows ui-sortable-handle"></i></td>\
        <td class="type">'+type+'</td>\
         <td class="actions">\
           <ul class="action-list">\
             <li class="edit-row" data-id="'+data.id+'" data-type="'+data.type+'"><a class="tool-tip-bottom fa fa-pencil" href="javascript:void(0);" title="'+lang.EDIT+'"></a></li>\
             <li class="visible tool-tip-bottom '+invisible+'" data-id="'+data.id+'" title="'+titleInvisible+'"><a class="fa fa-lightbulb-o '+invisible+'" href="javascript:void(0);"></a></li>\
             <li class="delete-row" data-id="'+data.id+'"><a class="tool-tip-bottom fa fa-trash" href="javascript:void(0);"  title="'+lang.DELETE+'"></a></li>\
           </ul>\
         </td>\
      </tr>';
 
      if(!replaceTr) {
        $('.entity-table-tbody').append(tr);
        $('.entity-table-tbody .no-results').remove();
         
      } else {
        replaceTr.replaceWith(tr);
      }
    },
       
       
    /**    
     * Удаляет  строку сущности в главной таблице
     * @param {type} data - данные для вывода в строке таблицы
     */           
    deleteEntity: function(id) {
      if(!confirm(lang.DELETE+'?')){
        return false;
      }
      
      admin.ajaxRequest({
        mguniqueurl: "action/deleteEntity", // действия для выполнения на сервере
        pluginHandler: 'slider-action', // плагин для обработки запроса
        id: id               
      },
      
      function(response) {
        admin.indication(response.status, response.msg);
        $('.entity-table-tbody tr[data-id='+id+']').remove();
        if($(".entity-table-tbody tr").length==0){
          var html ='<tr class="no-results">\
            <td colspan="3" align="center">'+sliderActionModule.lang['ENTITY_NONE']+'</td>\
          </tr>';
          $(".entity-table-tbody").append(html);
        };
      }
      
      );
    },
    
    
    /**
    * Смена типа слайда
    */         
    changeType: function(type) {
       switch (type) {
        case 'img':
          {
            $('.type-img').show();
            $('.type-html').hide(); 
            $('.section-slider-action .slide-editor select[name=type] option[value=img]').prop('selected','selected');
            break;
          }
        case 'html':
          {
            $('.type-img').hide();
            $('.type-html').show(); 
            $('.section-slider-action .slide-editor select[name=type] option[value=html]').prop('selected','selected');
           
            break;
          }
        default:
          {
            break;
          }
      }
    },
   
    /*
     * Перезагрузка слайдера
     */        
    reloadSlider: function(newSlider) {
      $('.m-p-slider-wrapper').remove();     
      $('.before-slider-content').remove();
      $('.after-slider-content').remove();
      $('.reload-slider').parent().append(newSlider);
    },
    
    /*
     * Переключатель слайдера
     */
     visibleEntity:function(id, val) {
      admin.ajaxRequest({
        mguniqueurl:"action/visibleEntity",
        pluginHandler: 'slider-action', // плагин для обработки запроса
        id: id,
        invisible: val,
      },
      function(response) {
        admin.indication(response.status, response.msg);
      } 
      );
    },
    
  }
})();

sliderActionModule.init();
var sliderSaveObject = ''; 