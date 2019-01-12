/**
 * Модуль для  раздела "работы с загружаемыми файлами".
 */
var uploader = (function () {
  return { 


    CALLBACK: null, // отложенная функция которая будет вызвана после выбора файла из выплывающего окна менеджера
    PARAM1: null, // параметр для передачи в отложенную функцию
    
    
    /**
     * Инициализирует экземпляр файлового менеджера
     */
    init: function() {    
      var elf = $('#elfinder').elfinder({
        url : mgBaseDir+'/ajax?mguniqueurl=action/elfinder&dir=uploads',
        useBrowserHistory: false,
        lang: 'ru',
        getFileCallback : function(file) { // после выбора файла передаем его в отложенную функцию                
           eval(uploader.CALLBACK).call(null,file);
           admin.closeModal($('#modal-elfinder'));     //закрываем окно        
           $('.cke_dialog_background_cover').css('z-index', '96');  
        },      
        closeOnEditorCallback: function() { 
           admin.closeModal( $('#modal-elfinder'));     //закрываем окно        
           $('.cke_dialog_background_cover').css('z-index', '96');  
        },        
        resizable: false,
        defaultView: 'list',
      }).elfinder('instance');
       
      $('#elfinderTemplate').elfinder({
        url : mgBaseDir+'/ajax?mguniqueurl=action/elfinder&dir=template',
        useBrowserHistory: false,
        lang: 'ru',              
        closeOnEditorCallback: function() { 
           admin.closeModal($('#modal-elfinder'));     //закрываем окно        
           $('.cke_dialog_background_cover').css('z-index', '96');  
        },        
        resizable: false,
        defaultView: 'list',
      });
    },
    
   
       
    /*
     * этот метод отрабатывает при вызове файлового менеджера из CKEditor
     */
    getFileCallbackCKEDITOR: function(file) {        
      CKEDITOR.tools.callFunction(uploader.PARAM1, file.url);     
    },   
    
    /**
     * открывает окно менеджера файлов, сохраняет  параметры для вызова отложенной функции 
     * @param {type} callback
     * @param {type} param1
     * @returns {undefined}
     */        
    open: function(callback,param1) {  
      
      uploader.PARAM1 = param1;
      uploader.CALLBACK = callback;

      if($('#modal-elfinder').length==0){    
        var uploaderHtml =  '\
          <link href="'+mgBaseDir+'/mg-admin/design/css/jquery-ui.css" rel="stylesheet" type="text/css">\
          <link rel="stylesheet" type="text/css" media="screen" href="'+mgBaseDir+'/mg-core/script/elfinder/css/elfinder.min.css">\
          <link rel="stylesheet" type="text/css" media="screen" href="'+mgBaseDir+'/mg-core/script/elfinder/css/theme.css">\
          <link rel="stylesheet" type="text/css" media="screen" href="'+mgBaseDir+'/mg-core/script/elfinder/css/fixElfinderStyle.css">\
          <div class="reveal-overlay" style="display:none;">\
            <div class="reveal xssmall uploader-modal" id="modal-elfinder" style="height:430px; display:block;">\
              <div class="product-table-wrapper">\
                <div class="widget-table-title" style="padding: 10px 0 0 10px;">\
                  <h4 class="category-table-icon" id="modalTitle">'+lang.FILE_MANAGER+'</h4>\
                  <button class="close-button uploader-modal_close" data-close="" type="button" style="top:6px;right:10px;"><i class="fa fa-times-circle-o" aria-hidden="true"></i></button>\
                </div>\
                <div id="elfinder"></div>\
                <div id="elfinderTemplate"></div>\
              </div>\
            </div>\
          </div>';
        if(!admin.PULIC_MODE) {
          $('body').append(uploaderHtml);
        } else {
          $('.mg-admin-html').append(uploaderHtml);
        }
        
        uploader.init();

        $('#modal-elfinder').parent().css('z-index',11000);

        // удаление лишних кнопок интерфейса загрузчика
        $('.elfinder-button-icon-resize').parent().detach();
        $('.elfinder-button-icon-pixlr').parent().detach();
        $('.elfinder-button-icon-netmount').parent().parent().detach();
        $('.elfinder-button-icon-help').parent().parent().detach();
        $('.elfinder-button-icon-getfile').parent().parent().detach();

        $('.elfinder-buttonset:eq(3) .elfinder-toolbar-button-separator:eq(2)').detach();

        $('.elfinder-buttonset:eq(6) .elfinder-toolbar-button-separator:eq(2)').detach();
        $('.elfinder-buttonset:eq(6) .elfinder-toolbar-button-separator:eq(2)').detach();
        // конец удаления кнопок

        $( "#modal-elfinder").draggable({ handle: ".widget-table-title" });
        $('body').on('click', '.uploader-modal_close', function() {  
          $('.cke_dialog_background_cover').css('z-index', '96');  
        });
      }
      
      if(admin.DIR_FILEMANAGER=='template'){
        $('#elfinderTemplate').show();
        $('#elfinder').hide();  
      }
      if(admin.DIR_FILEMANAGER=='uploads'){
        $('#elfinderTemplate').hide();
        $('#elfinder').show();
      }
     
      admin.openModal($('#modal-elfinder'));
      // $('.cke_dialog ').css('z-index', '100'); 
      // $('.cke_dialog_background_cover').css('z-index', '150');  
      $('#modal-elfinder').css('z-index', '1200');   
    },            
    
            
    }
  
})();

// инициализация модуля при подключении
uploader.init();