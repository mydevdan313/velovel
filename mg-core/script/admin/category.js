
/**
 * Модуль для  раздела "Категории".
 */
var category = (function() {
  return {
    wysiwyg: null, // HTML редактор для   редактирования страниц
    supportCkeditor: null, 
    supportCkeditorSeo: null, 
    clickedId: [],
    //openedCategoryAdmin: {}[], //массив открытых категорий
    /**
     * Инициализирует обработчики для кнопок и элементов раздела.
     */
    init: function() {
      // восстанавливаем массив открытых значение из куков
      category.openedCategoryAdmin = eval(cookie("openedCategoryAdmin"));

      // смена языка
      $('.admin-center').on('change','.section-category .select-lang', function() {
        category.editCategory($('#add-category-modal .save-button').attr('id'));     
      });

      $('body').on('click', '.set-seo', function() {
        $('#seoImgCat').show();
      });

      $('body').on('click', '.seo-image-block-close, .apply-seo-image', function() {
        $('#seoImgCat').hide();
      });

      /*Инициализирует CKEditior*/
      $('body').on('click', '#add-category-modal .html-content-edit', function() {
        $('#add-category-modal textarea[name=html_content]').ckeditor(function() {});
        CKEDITOR.instances['html_content'].config.filebrowserUploadUrl = admin.SITE + '/ajax?mguniqueurl=action/upload_tmp';
      });

      $('body').on('click', '#add-category-modal .seo-content-edit', function() {
        $('#add-category-modal textarea[name=html_content-seo]').ckeditor(function() {});
        CKEDITOR.instances['html_content-seo'].config.filebrowserUploadUrl = admin.SITE + '/ajax?mguniqueurl=action/upload_tmp';
      }); 

      // Вызов модального окна при нажатии на кнопку добавления категории.      
      $('.admin-center').on('click', '.section-category .add-new-button', function() {
        category.openModalWindow('add');
      });

      // Вызов модального окна при нажатии на пункт изменения категории.
      $('.admin-center').on('click', '.section-category .edit-sub-cat', function() {
        category.openModalWindow('edit', $(this).parents('tr').data('id'));
      });

      // Вызов модального окна при нажатии на пункт добавления подкатегории.
      $('.admin-center').on('click', '.section-category .add-sub-cat', function() {
        category.openModalWindow('addSubCategory', $(this).parents('tr').data('id'));
      });

      // Удаление категории.
      $('.admin-center').on('click', '.section-category .delete-sub-cat', function() {
        category.deleteCategory($(this).parents('tr').data('id'));
      });

      // Закрыть контекстное меню.
      $('.admin-center').on('click', '.section-category .cancel-sub-cat', function() {
        category.closeContextMenu();
      });

      // Сохранение продукта при нажатии на кнопку сохранить в модальном окне.
      $('body').on('click', '#add-category-modal .save-button', function() {
        category.saveCategory($(this).attr('id'));
      });      
      
      // Выбор картинки категории
      $('body').on('click', '#add-category-modal .imgPrimary .add-image-to-category, #add-category-modal .imgPrimary .additional_uploads_container label', function() {
        admin.openUploader('category.getFile');

      });  
      
      // Удаление картинки категории
      $('body').on('click', '#add-category-modal .imgPrimary .del-image-to-category', function() {
        category.delImgs.push($('#add-category-modal .imgPrimary input[name=image_url]').val());
        $('#add-category-modal .imgPrimary input[name=image_url]').val('');
        $('#add-category-modal .imgPrimary .category-img-block').hide();
        $('#add-category-modal .imgPrimary .add-image-to-category').show();
        $('#add-category-modal .imgPrimary .del-image-to-category').hide();       
      });  

      //открытие всплывалки для ввода ссылки
      $('.admin-center').on('click', '#add-category-modal .imgPrimary .additional_uploads_container .from_url', function() {
        $('#add-category-modal .upload-form .custom-popup').hide();
        $('#add-category-modal .imgPrimary .upload-form .custom-popup').show();
      });

      //закрытие всплывалки для ввода ссылки
      $('.admin-center').on('click', '#add-category-modal .upload-form .custom-popup .cancel-url', function() {
        $('#add-category-modal .upload-form .custom-popup').hide();
      });

      //применение всплывалки для ввода ссылки
      $('.admin-center').on('click', '#add-category-modal .imgPrimary .upload-form .custom-popup .apply-url', function() {

        var imgUrl = $('#add-category-modal .imgPrimary .upload-form .custom-popup input').val();

        admin.ajaxRequest({
          mguniqueurl:"action/addImageUrl",
          imgUrl: imgUrl,
          isCatalog: 'false'
        },

        function(response) {
          admin.indication(response.status, response.msg);

          if (response.status == 'success') {
            $('#add-category-modal .imgPrimary  input[name="image_url"]').val(admin.SITE+'/uploads/'+response.data);
            $('#add-category-modal .imgPrimary .category-image').attr('src', admin.SITE+'/uploads/'+response.data);
            $('#add-category-modal .imgPrimary .category-img-block').show();
            $('#add-category-modal .imgPrimary .category-image').show();
            $('#add-category-modal .imgPrimary .add-image-to-category').hide();
            $('#add-category-modal .imgPrimary .del-image-to-category').show(); 
          }
        });

        $('#add-category-modal .imgPrimary .upload-form .custom-popup').hide();
      });

      // Выбор картинки категории
      $('body').on('click', '#add-category-modal .imgSecondary .add-image-to-category, #add-category-modal .imgSecondary .additional_uploads_container label', function() {
        admin.openUploader('category.getFileSecondary');

      });  
      
      // Удаление картинки категории
      $('body').on('click', '#add-category-modal .imgSecondary .del-image-to-category', function() {
        category.delImgs.push($('#add-category-modal .imgSecondary input[name=image_url]').val());
        $('#add-category-modal .imgSecondary input[name=image_url]').val('');
        $('#add-category-modal .imgSecondary .category-img-block').hide();
        $('#add-category-modal .imgSecondary .add-image-to-category').show();
        $('#add-category-modal .imgSecondary .del-image-to-category').hide();       
      });  

      //открытие всплывалки для ввода ссылки
      $('.admin-center').on('click', '#add-category-modal .imgSecondary .additional_uploads_container .from_url', function() {
        $('#add-category-modal .upload-form .custom-popup').hide();
        $('#add-category-modal .imgSecondary .upload-form .custom-popup').show();
      });

      //применение всплывалки для ввода ссылки
      $('.admin-center').on('click', '#add-category-modal .imgSecondary .upload-form .custom-popup .apply-url', function() {

        var imgUrl = $('#add-category-modal .imgSecondary .upload-form .custom-popup input').val();

        admin.ajaxRequest({
          mguniqueurl:"action/addImageUrl",
          imgUrl: imgUrl,
          isCatalog: 'false'
        },

        function(response) {
          admin.indication(response.status, response.msg);

          if (response.status == 'success') {
            $('#add-category-modal .imgSecondary  input[name="image_url"]').val(admin.SITE+'/uploads/'+response.data);
            $('#add-category-modal .imgSecondary .category-image').attr('src', admin.SITE+'/uploads/'+response.data);
            $('#add-category-modal .imgSecondary .category-img-block').show();
            $('#add-category-modal .imgSecondary .category-image').show();
            $('#add-category-modal .imgSecondary .add-image-to-category').hide();
            $('#add-category-modal .imgSecondary .del-image-to-category').show(); 
          }
        });

        $('#add-category-modal .imgSecondary .upload-form .custom-popup').hide();
      });

     // Выделить все категории.
      $('.admin-center').on('click', '.section-category .check-all-cat', function() {       
        $('.category-tree input[name=category-check]').prop('checked', 'checked');
        $('.category-tree input[name=category-check]').val('true');
        $('.category-tree tr').addClass('selected');

        $(this).addClass('uncheck-all-cat');
        $(this).removeClass('check-all-cat');
      });
            
       // Сортировать все категории по алфавиту
      $('.admin-center').on('click', '.section-category .sort-all-cat', function() {
        category.sortToAlphabet();
      });
      
      // Снять выделение со всех  категорий.
      $('.admin-center').on('click', '.section-category .uncheck-all-cat', function() {        
        $('.category-tree input[name=category-check]').prop('checked', false);
        $('.category-tree input[name=category-check]').val('false');
        $('.category-tree tr').removeClass('selected');
        
        $(this).addClass('check-all-cat');
        $(this).removeClass('uncheck-all-cat');
      });
      
      // Выполнение выбранной операции с категориями
      $('.admin-center').on('click', '.section-category .run-operation', function() {
        if ($('.category-operation').val() == 'fulldelete') {
          admin.openModal('#category-remove-modal');
        }
        else{
          category.runOperation($('.category-operation').val());
        }
      });
      //Проверка для массового удаления
      $('.admin-center').on('click', '#category-remove-modal .confirmDrop', function () {
        if ($('#category-remove-modal input').val() === $('#category-remove-modal input').attr('tpl')) {
          $('#category-remove-modal input').removeClass('error-input');
          admin.closeModal('#category-remove-modal');
          category.runOperation($('.category-operation').val(),true);
        }
        else{
          $('#category-remove-modal input').addClass('error-input');
        }
      });

      // Сохранение категории при нажатии на кнопку сохранить в модальном окне.
      $('body').on('click', '.section-category .prod-sub-cat', function() {
        includeJS(admin.SITE + '/mg-core/script/admin/catalog.js');
        admin.SECTION = 'catalog';
        admin.show("catalog.php", cookie("type"), "page=0&insideCat=true&applyFilter=1&displayFilter=1&cat_id=" + $(this).parents('tr').data('id'), catalog.callbackProduct);
        $('#category').removeClass('active');
        $('#catalog').addClass('active');
      });

      // Сохранение категории при на жатии на кнопку сохранить в модальном окне.
      $('body').on('click', '.section-category .link-to-site', function() {
        window.open($(this).data('href'));
      });

      // для коректной работы функции для открытия вложенных пунктов
      $(document).on({
        mouseenter: function () {
          category.humanClick = true;
          console.clear();
          console.log('human - '+category.humanClick);
        },
        mouseleave: function () {
          category.humanClick = false;
          console.clear();
          console.log('human - '+category.humanClick);
        }
      }, '.section-category .show_sub_menu');

      // Разворачивание подпунктов по клику
      $('.admin-center').on('click', '.section-category .show_sub_menu', function() {
        var object = $(this).parents('tr');
        var id = $(this).parents('tr').data('id');
        var level = $(this).parents('tr').data('level');
        var group = 'group-'+$(this).parents('tr').data('id');
        level++;

        var saveHuman = category.humanClick;

        // thisSortNumber = $(this).parents('tr').data('sort');
        thisSortNumber = 0;
        isFindeSorte = false;
        $('.section-category .main-table tbody tr').each(function() {
          if($(this).data('id') == id) {
            isFindeSorte = true;
          }
          if(!isFindeSorte) {
            thisSortNumber++;
          }
        });

        if ($(this).hasClass('opened')) {
          category.delCategoryToOpenArr($(this).data('id'), level);

          category.group = $(this).parents('tr').data('group');

          var trCount = $('.section-category .main-table tbody tr').length;

          var startDel = false;
          $('.section-category .main-table tbody tr').each(function() {
            if($(this).data('level') >= level) {
              if($(this).data('group') == group) {
                startDel = true;
              }
            }
            if(startDel) {
              if($(this).data('level') >= level) {
                category.delCategoryToOpenArr($(this).data('id'), $(this).data('level')+1);
                $(this).detach();
              } else {
                startDel = false;
              }
            }
          });

          $(this).removeClass('opened');
        } else {
          category.addCategoryToOpenArr(id, level);
          object.after('\
            <tr id="loader-'+id+'">\
              <td><div class="checkbox"><input type="checkbox" name="category-check"><label class="select-row"></label></div></td>\
              <td class="sort"><a class="fa fa-arrows tip mover" href="javascript:void(0);" aria-hidden="true" title="Сортировать"></a></td>\
              <td class="number"></td>\
              <td style="padding-left:40px;"><img src="'+admin.SITE+'/mg-admin/design/images/loader-small.gif"></td>\
              <td colspan="2"></td>\
              <td class="text-right actions">\
                <ul class="action-list">\
                  <li><a class="fa fa-pencil tip edit-sub-cat" href="javascript:void(0);" tabindex="0" title="'+lang.EDIT+'"></a></li>\
                  <li><a class="fa fa-plus-circle tip add-sub-cat" href="javascript:void(0);" aria-hidden="true" title="'+lang.ADD_SUBCATEGORY+'"></a></li>\
                  <li><a class="fa fa-lightbulb-o tip activity" href="javascript:void(0);" aria-hidden="true" title="'+lang.DISPLAY+'"></a></li>\
                  <li><a class="fa fa-list tip visible" href="javascript:void(0);" aria-hidden="true" title="'+lang.SHOW_PRODUCT+'"></a></li>\
                  <li><a class="fa fa-shopping-cart tip export" href="javascript:void(0);" aria-hidden="true" title="'+lang.ACTIVATE_IMPORT_YANDEX+'"></a></li>\
                  <li><a class="fa fa-search tip prod-sub-cat" href="javascript:void(0);" aria-hidden="true" title="'+lang.LOOK_AT_PROD_IN_CAT+'"></a></li>\
                  <li><a class="fa fa-trash tip delete-sub-cat" href="javascript:void(0);" aria-hidden="true" title="'+lang.DELETE+'"></a></li>\
                </ul>\
              </td>\
            </tr>');
          admin.ajaxRequest({
            mguniqueurl: "action/showSubCategory",
            id: id,
            level: level
          },
          function(response) {      
            $('#loader-'+id).detach();
            object.after(response.data);
            category.sortableInit();
            if(!saveHuman) {
              category.hidePageRows();
            }
          });

          $(this).addClass('opened');
        }
      });

      // Клик на иконку меню, делает невидимой категорию в меню      
      $('.admin-center').on('click', '.section-category .visible', function() {
        var id = $(this).parents('tr').data('id');

        if ($(this).hasClass('active')) {
          category.invisibleCat(id, 1);
          $(this).attr('title', lang.ACT_V_CAT);
        }
        else {
          category.invisibleCat(id, 0);
          $(this).attr('title', lang.ACT_UNV_CAT);
        }
        admin.initToolTip();
      });
         // Клик на иконку лампочки, делает неактивной категорию и товары этой категории     
      $('.admin-center').on('click', '.section-category .activity', function() {
        var id = $(this).parents('tr').data('id');

        if (!$(this).hasClass('active')) {
          category.activityCat(id, 1);
          $(this).addClass('active');
          $(this).attr('title', lang.ACT_V_CAT_ACT);
        } else {
          category.activityCat(id, 0);
          $(this).removeClass('active');
          $(this).attr('title', lang.ACT_UNV_CAT_ACT);
        }
        admin.initToolTip();
      });
      
      // Клик на иконку экспорта, включает или исключает категорию из выгрузки      
      $('.admin-center').on('click', '.section-category .export', function() {
        $(this).toggleClass('active');
        var id = $(this).parents('tr').data('id');

        if ($(this).hasClass('active')) {
          $(this).parent('li').find('ul div.export').addClass('active').attr('title', lang.ACT_EXPORT_CAT);
          category.exportCatStatus(id, 1);
          $(this).attr('title', lang.ACT_EXPORT_CAT);
        } else {
          $(this).parent('li').find('ul div.export').removeClass('active').attr('title', lang.ACT_NOT_EXPORT_CAT);
          category.exportCatStatus(id, 0);
          $(this).attr('title', lang.ACT_NOT_EXPORT_CAT);
        }
        admin.initToolTip();

      });

      // применение класса selected для строки, которой ставят галочку выделения
      $('body').on('click' ,'.section-category .select-row', function() {
        var id = $(this).parents('tr').data('id');
        if($('#c'+id).prop('checked')) {
          $(this).parents('tr').removeClass('selected');
        } else {
          $(this).parents('tr').addClass('selected');
        }
      });

      // контекстное меню для работы с категориями.
      $('.admin-center').on('click', '.category-tree li a[class=CategoryTree]', function() {
        $(".cat-li .cat-title").text($(this).text());
        $(".cat-li .cat-id").text('id = ' + $(this).attr('id') + ' ');
        category.openContextMenu($(this).attr('id'), $(this).offset());
      });


      // клик вне поиска
      $(document).mousedown(function(e) {
        var container = $(".edit-category-list");
        if (container.has(e.target).length === 0 && $(".edit-category-list").has(e.target).length === 0) {
          category.closeContextMenu();
        }
      });
      
      //Инициализирует CKEditior и раскрывает поле для заполнения описания товара
      // $('.admin-center').on('click', '.category-desc-wrapper .html-content-edit', function() {
      //   var link = $(this);
      //   $('#add-category-modal textarea[name=html_content]').ckeditor(function() {  
      //     $('#html-content-wrapper').show();
      //     link.hide();
      //   });
      // });
      //Инициализирует CKEditior и раскрывает поле для заполнения seo описания категории
      $('.admin-center').on('click', '.category-desc-wrapper-seo .html-content-edit-seo', function() {
        var link = $(this);
        if (!link.hasClass('init')) {
          //   $('#add-category-modal textarea[name=html_content-seo]').ckeditor(function() {  
          //   $('#html-content-wrapper-seo').show();
          //   link.addClass('init');
          // });
            $('#html-content-wrapper-seo').show();
        } else {
          $('#html-content-wrapper-seo').slideToggle();
        }        
      });

      
      $('body').on('focus', '.discount-rate input[name=rate]', function() {
        $('.discount_apply_follow').show();
      }); 
      
      //Обработка клика по кнопке "Загрузить из CSV"
      $('.admin-center').on('click', '.section-category .import-csv', function() {
        $('.import-container').slideToggle(function() {
          $('.widget-table-action').toggleClass('no-radius');
        });
      });
      
      // Обработчик для загрузки файла импорта
      $('body').on('change', '.section-category input[name="upload"]', function() {

        category.uploadCsvToImport();
      });
      
      // Обработчик для загрузки файла импорта из CSV
      $('body').on('click', '.section-category .repeat-upload-csv', function() {
        $('.import-container input[name="upload"]').val('');
        $('.import-container .block-upload-csv').show();
        $('.block-importer').hide();
        $('.repat-upload-file').show();
        $('.cancel-importing').hide();
        $('.message-importing').text('');
        category.STOP_IMPORT=false;

      });

      // Обработчик для загрузки изображения на сервер, сразу после выбора.
      $('body').on('click', '.section-category .start-import', function() {
        if(!confirm(lang.CATALOG_LOCALE_1)) {
          return false;
        }
        $('.repat-upload-file').hide();
        $('.block-importer').hide();
        $('.cancel-importing').show();
        category.startImport($('.block-importer .uploading-percent').text());

      });

      // Останавливает процесс загрузки товаров.
      $('body').on('click', '.section-catalog .cancel-import', function() {
        category.canselImport();
      });
      
      $('.admin-center').on('click', '.section-category a.get-csv', function() {
        category.exportToCsv(0);
        return false;
      });
      
      $('body').on('click', '#add-category-modal .generate-tags-btn', function() {
        category.generateSeoFromTmpl();
      });

      $(".section-page .main-table tbody tr").hover( 
        function() {
          group = $(this).data('group');

          var trCount = $('.section-page .main-table tbody tr').length;
          for(i = 0; i < trCount; i++) {
            if($('.section-page .main-table tbody tr:eq('+i+')').hasClass(group)) {
              $('.section-page .main-table tbody tr:eq('+i+')').removeClass('disableSort');
            } else {
              $('.section-page .main-table tbody tr:eq('+i+')').addClass('disableSort');
            }
          }
        }
      );

      $('body').on('click', '.calcCountProd', function() {
        $('.catsCountInDoNothing').hide();
        $('.catsCountInProgress').show();
        category.calcCount();
      });
    },
    calcCount: function() {
      admin.ajaxRequest({
        mguniqueurl:"action/calcCountProdCat",
      },
      function(response) {
        if(response.data < 100) {
          $('.catsCountInDoNothing').hide();
          $('.catsCountInProgress').show();
          $('.catsCountPerc').html(response.data);
          setTimeout(function() {
            category.calcCount();
          }, 2000);
        } else {
          $('.catsCountInDoNothing').show();
          $('.catsCountInProgress').hide();
          admin.indication(response.status, response.msg);
        }
      });
    },
    /**
      * Генерируем мета описание
      */
    generateMetaDesc: function(description) {
      if (!description) {return '';}
      var short_desc = description.replace(/<\/?[^>]+>/g, '');
      short_desc = admin.htmlspecialchars_decode(short_desc.replace(/\n/g, ' ').replace(/&nbsp;/g, '').replace(/\s\s*/g, ' ').replace(/"/g, ''));

      if (short_desc.length > 150) {
        var point = short_desc.indexOf('.', 150);
        short_desc = short_desc.substr(0, (point > 0 ? point : short_desc.indexOf(' ',150)));
      }

      return short_desc;
    },
    /**
    * Генерируем ключевые слова для категории
    * @param string title
    */
    generateKeywords: function(title) {
    },
    /**
    * Запускаем генерацию метатегов по шаблонам из настроек
    */
    generateSeoFromTmpl: function() {
    },
    
    /**
     * Загружает CSV файл на сервер для последующего импорта
     */
    uploadCsvToImport:function() {

    },
    
    /**
     * Контролирует процесс импорта, выводит индикатор в процентах обработки каталога.
     */
    startImport:function(rowId, percent) {
      var delCatalog=null;
      
      if(!rowId) {
        if(!$('.loading-line').length) {
          $('.process').append('<div class="loading-line"></div>');
        }
        
        rowId = 0;
        delCatalog = $('input[name=no-merge]').val();
      }
      
      if(!percent) {
        percent = 0;
      }
      
      if(!catalog.STOP_IMPORT) {
        $('.message-importing').html(lang.IMPORT_CATEGORY_PROGRESS+percent+'%<div class="progress-bar"><div class="progress-bar-inner" style="width:'+percent+'%;"></div></div>');
      } else {
        $('.loading-line').remove();
      }
      
      // отправка файла CSV на сервер
      admin.ajaxRequest({
        mguniqueurl:"action/startImportCategory",
        rowId:rowId,
        delCatalog:delCatalog,
      },
      function(response) {
        if(response.status=='error') {
          admin.indication(response.status, response.msg);
        }

        if(response.data.percent < 100) {
          if(response.data.status == 'canseled') {
            $('.message-importing').html(lang.IMPORT_STOP+response.data.rowId+' '+lang.LOCALE_GOODS+' '+'[<a href="javascript:void(0);" class="repeat-upload-csv">'+lang.UPLOAD_ANITHER+'</a>]' );
            $('.block-importer').hide();
            $('.loading-line').remove();
          } else {
            category.startImport(response.data.rowId,response.data.percent);
          }
        } else {
          $('.message-importing').html(lang.IMPORT_CATEGORY_FINISHED+'\
            <a class="refresh-page custom-btn" href="'+mgBaseDir+'/mg-admin/">\n\
              <span>'+lang.CATALOG_REFRESH+'</span>\n\
            </a>');
          $('.block-importer').hide();
          $('.loading-line').remove();
        }

      });
    },
    
    exportToCsv: function(rowCount) {   
    },
    
    /** 
     * меняет местами две категории oneId и twoId
     * oneId - идентификатор первой категории
     * twoId - идентификатор второй категории
     */
    changeSortCat: function(oneId, twoId) {
      admin.ajaxRequest({
        mguniqueurl: "action/changeSortCat",
        oneId: oneId,
        twoId: twoId
      },
      function(response) {
        admin.indication(response.status, response.msg)
      });
    },
    /** 
     * Делает категорию  видимой/невидимой в меню
     * oneId - идентификатор первой категории
     * twoId - идентификатор второй категории
     */
    invisibleCat: function(id, invisible) {
      loader = $('.mailLoader');
      loader.before('<div class="view-action" style="display:block; margin-top:-2px;">' + lang.LOADING + '</div>');
      $.ajax({
        type: "POST",
        url: mgBaseDir + "/ajax",
        data: {
          mguniqueurl: "action/invisibleCat",
          id: id,
          invisible: invisible
        },
        dataType: "json",
        cache: false,
        success: function(response) {
          admin.indication(response.status, response.msg);
          admin.refreshPanel();
        }
      });
    },
    /** 
     * Делает категорию  активной/неактивной в меню
     * oneId - идентификатор первой категории
     * twoId - идентификатор второй категории
     */
    activityCat: function(id, activity) {
      admin.ajaxRequest({
        mguniqueurl: "action/activityCat",
        id: id,
        activity: activity
      },
      function(response) {
        admin.indication(response.status, response.msg)
      });
    },
    /** 
     * Устанавливает флаг выгрузки для категории
     * oneId - идентификатор первой категории
     * exportCat - значение флага выгружать/не выгружать(1/0)
     */
    exportCatStatus: function(id, exportCat) {
      admin.ajaxRequest({
        mguniqueurl: "action/exportCatStatus",
        id: id,
        export: exportCat
      },
      function(response) {
        admin.indication(response.status, response.msg);
      });
    },
    /**
     * открывает контекстное меню
     * id - идентификатор выбранной категории
     * offset - положение элемента на странице, для вычисления позиции контекстного меню
     */
    openContextMenu: function(id, offset) {

      $('.edit-category-list').css('position', 'absolute');
      $('.edit-category-list').css('display', 'block');
      $('.edit-category-list').css('z-index', '1');
      $('.edit-category-list').offset(offset);
      var top = $('.edit-category-list').css('top').slice(0, -2);
      var left = $('.edit-category-list').css('left').slice(0, -2);
      top = parseInt(top) - 2;
      left = parseInt(left) + 92;
      $('.edit-category-list').css({top: top + 'px', left: left + 'px', });

      $('.edit-sub-cat').attr('id', id);
      $('.add-sub-cat').attr('id', id);
      $('.delete-sub-cat').attr('id', id);
      $('.prod-sub-cat').data('id', id);

    },
    // закрывает контекстное меню для работы с категориями.
    closeContextMenu: function() {
      $('.edit-category-list').css('display', 'none');
    },
    // добавляет ID открытой категории в массив, записывает в куки для сохранения статуса дерева
    addCategoryToOpenArr: function(id, level) {
      level = typeof level !== 'undefined' ? level : 0;
      if(category.openedCategoryAdmin == undefined) 
        category.openedCategoryAdmin = [];

      var addId = true;
      category.openedCategoryAdmin.forEach(function(item) {
        if (item == id) {
          addId = false;
        }
      });

      if (addId) {
        category.openedCategoryAdmin.push(id);
      }

      cookie("openedCategoryAdmin", JSON.stringify(category.openedCategoryAdmin));
    },
    // удаляет ID закрытой категории из массива, записывает в куки для сохранения статуса дерева
    delCategoryToOpenArr: function(id, level) {
      level = typeof level !== 'undefined' ? level : 0;
      if(category.openedCategoryAdmin == undefined) 
        category.openedCategoryAdmin = [];

      var dell = false;
      var i = 0;
      var spliceIndex = 0;
      category.openedCategoryAdmin.forEach(function(item) {
        if (item == id) {
          dell = true;
          spliceIndex = i;
        }
        i++;
      });

      if (dell) {
        category.openedCategoryAdmin.splice(spliceIndex, 1);
      }

      cookie("openedCategoryAdmin", JSON.stringify(category.openedCategoryAdmin));
    },
    /**
     * Открывает модальное окно.
     * type - тип окна, либо для создания нового товара, либо для редактирования старого.
     * id - редактируемая категория, если это не создание новой
     */
    openModalWindow: function(type, id) {
      category.delImgs = [];
     try{        
        if(CKEDITOR.instances['html_content']) {
          CKEDITOR.instances['html_content'].destroy();
        }      
      } catch(e) { }   
      try{        
        if(CKEDITOR.instances['html_content-seo']) {
          CKEDITOR.instances['html_content-seo'].destroy();
        }      
      } catch(e) { } 
      switch (type) {
        case 'edit':
          {
            category.clearFileds();
            $('.html-content-edit').show();
            $('.category-desc-wrapper #html-content-wrapper').hide();
            $('.category-desc-wrapper-seo #html-content-wrapper-seo').hide();
            $('#modalTitle').text(lang.EDIT_CAT);
            category.editCategory(id);
            break;
          }
        case 'add':
          {
            $('#modalTitle').text(lang.ADD_CATEGORY);
            category.clearFileds();
            // $('.html-content-edit').hide();
            $('.category-desc-wrapper #html-content-wrapper').show();
            $('.category-desc-wrapper-seo #html-content-wrapper-seo').hide();
            break;
          }
        case 'addSubCategory':
          {
            $('#modalTitle').text(lang.ADD_SUBCATEGORY);
            category.clearFileds();
            $('.html-content-edit').show();
            $('.category-desc-wrapper #html-content-wrapper').hide();
            $('.category-desc-wrapper-seo #html-content-wrapper-seo').hide();
            $('select[name=parent] option[value="' + id + '"]').prop("selected", "selected");
            break;
          }
        default:
          {
            category.clearFileds();
            break;
          }
      }

      // инициализация ckeditor
      // $('#add-category-modal textarea[name=html_content]').ckeditor();
      // $('#add-category-modal textarea[name=html_content-seo]').ckeditor();

      // закрытие контекстного меню
      category.closeContextMenu();

      // Вызов модального окна.
      admin.openModal('#add-category-modal');
    },
    /**
     *  Проверка заполненности полей, для каждого поля прописывается свое правило.
     */
    checkRulesForm: function() {
      $('.errorField').css('display', 'none');
      $('input').removeClass('error-input');

      var error = false;
      // наименование не должно иметь специальных символов.
      if (!$('input[name=title]').val()) {
        $('input[name=title]').parent("div").find('.errorField').css('display', 'block');
        $('input[name=title]').addClass('error-input');
        error = true;
      }

      // артикул обязательно надо заполнить.
      if (!admin.regTest(1, $('input[name=url]').val()) || !$('input[name=url]').val()) {
        $('input[name=url]').parent("div").find('.errorField').css('display', 'block');
        $('input[name=url]').addClass('error-input');
        error = true;
      }
      
      var url = $('input[name=url]').val();
      var reg = new RegExp('([^/-a-z\.\d])','i');
      
      if (reg.test(url)) {
        $('input[name=url]').parent("div").find('.errorField').css('display','block');
        $('input[name=url]').addClass('error-input');
        $('input[name=url]').val('');
        error = true;
      }


      if (error == true) {
        return false;
      }

      return true;
    },
    /**
     * Сохранение изменений в модальном окне категории.
     * Используется и для сохранения редактированных данных и для сохранения нового продукта.
     * id - идентификатор продукта, может отсутствовать если производится добавление нового товара.
     */
    saveCategory: function(id) {
      // Если поля неверно заполнены, то не отправляем запрос на сервер.
      if (!category.checkRulesForm()) {
        return false;
      }

      if($('#add-category-modal textarea[name=html_content]').val()=='') {
        if(!confirm(lang.ACCEPT_EMPTY_DESC+'?')) {
          return false;
        }
      }

      var validFormats = ['jpeg', 'jpg', 'png', 'gif', 'svg'];
      var ext = '';
      if ($('#add-category-modal .imgPrimary input[name="image_url"]').val()) {
        ext = $('#add-category-modal .imgPrimary input[name="image_url"]').val();
        ext = ext.split('.');
        ext = ext.pop();
        ext = ext.toLowerCase();
        if (jQuery.inArray(ext, validFormats) < 0) {
          admin.indication('error', lang.ACT_IMG_NOT_UPLOAD2);
          return false;
        }
      }
      
      if ($('#add-category-modal .imgSecondary input[name="image_url"]').val()) {
        ext = $('#add-category-modal .imgSecondary input[name="image_url"]').val();
        ext = ext.split('.');
        ext = ext.pop();
        ext = ext.toLowerCase();
        if (jQuery.inArray(ext, validFormats) < 0) {
          admin.indication('error', lang.ACT_IMG_NOT_UPLOAD2);
          return false;
        }
      }


      // Пакет характеристик категории.
      var packedProperty = {
        mguniqueurl: "action/saveCategory",
        id: id,
        unit: $('#add-category-modal input[name=unit]').val(),
        title: $('#add-category-modal input[name=title]').val(),
        menu_title: $('#add-category-modal input[name=menu_title]').val(),
        url: $('#add-category-modal input[name=url]').val(),
        parent: $('#add-category-modal select[name=parent]').val(),
        html_content: $('#add-category-modal textarea[name=html_content]').val(),
        meta_title: $('#add-category-modal input[name=meta_title]').val(),
        meta_keywords: $('#add-category-modal input[name=meta_keywords]').val(),
        meta_desc: $('#add-category-modal textarea[name=meta_desc]').val(),
        image_url: $('#add-category-modal .imgPrimary input[name="image_url"]').val(),
        menu_icon: $('#add-category-modal .imgSecondary input[name="image_url"]').val(),
        invisible: $('#add-category-modal input[name=invisible]').prop('checked') ? 1 : 0,
        seo_content: $('#add-category-modal textarea[name=html_content-seo]').val(),
        lang: $('.select-lang').val(),
        seo_alt: $('#add-category-modal [name=image_alt]').val(),
        seo_title: $('#add-category-modal [name=image_title]').val(),
        delImgs: category.delImgs,
      };

      // Отправка данных на сервер для сохранения.
      admin.ajaxRequest(packedProperty,
        function(response) {
          admin.indication(response.status, response.msg);                
          if ($('input[name=discount_apply_follow]').val()== 'true') {
            admin.ajaxRequest({
            mguniqueurl: "action/applyRateToSubCategory",
            id: id
            },
              function(response) {  
                admin.closeModal($('#add-category-modal'));
                admin.refreshPanel();
              });
          } else {
            // Закрываем окно.
            admin.closeModal($('#add-category-modal'));
            admin.refreshPanel();
          }                
        }
      );
    },
    /**
     * Получает данные о категории с сервера и заполняет ими поля в окне.
     */
    editCategory: function(id) {
      admin.ajaxRequest({
        mguniqueurl: "action/getCategoryData",
        id: id,
        lang: $('.select-lang').val()
      },
      category.fillFields(),
              $('.add-product-form-wrapper .add-category-form')
              );
    },
    /**
     * Удаляет категорию из БД сайта
     */
    deleteCategory: function(id) {
      if (!confirm(lang.SUB_CATEGORY_DELETE + '?')) {return false;}
      var dropProducts = 'false';
      if (confirm(lang.SUB_CATEGORY_DELETE_PROD)) {dropProducts = 'true';}

        admin.ajaxRequest({
          mguniqueurl: "action/deleteCategory",
          id: id,
          dropProducts: dropProducts
        },
        function(response) {
          admin.indication(response.status, response.msg);
          admin.refreshPanel();
        });
      
    },
    /**
     * Заполняет поля модального окна данными.
     */
    fillFields: function() {
      return (function(response) {

        $('.accordion-item').removeClass('is-active');
        $('.accordion-content').hide();
        
        $('input').removeClass('error-input');
        $('input[name=unit]').val(response.data.unit);
        $('input[name=title]').val(response.data.title);
        $('input[name=menu_title]').val(response.data.menu_title);
        $('input[name=url]').val(response.data.url);
        $('select[name=parent]').val(response.data.parent);
        $('select[name=parent]').val(response.data.parent);
        $('input[name=invisible]').prop('checked', false);
        $('input[name=invisible]').val('false');
        if (response.data.invisible == 1) {
          $('input[name=invisible]').prop('checked', true);
          $('input[name=invisible]').val('true');
        }
        $('input[name=meta_title]').val(response.data.meta_title);  
        category.supportCkeditor = response.data.html_content;  
        category.supportCkeditorSeo = response.data.seo_content;  
        $('#add-category-modal textarea[name=html_content]').val(response.data.html_content);
        $('#add-category-modal textarea[name=html_content-seo]').val(response.data.seo_content);
        $('input[name=meta_keywords]').val(response.data.meta_keywords);
        $('textarea[name=meta_desc]').val(response.data.meta_desc);
        $('.symbol-count').text($('textarea[name=meta_desc]').val().length);

        $('#add-category-modal [name=image_title]').val('');
        $('#add-category-modal [name=image_alt]').val('');
        $('#add-category-modal [name=image_title]').val(response.data.seo_title);
        $('#add-category-modal [name=image_alt]').val(response.data.seo_alt);
                
        if(response.data.image_url) {
          $('#add-category-modal .imgPrimary .category-image').attr('src', admin.SITE+response.data.image_url);
          $('#add-category-modal .imgPrimary .category-image').show();
          $('#add-category-modal .imgPrimary .category-img-block').show();          
          $('#add-category-modal .imgPrimary .del-image-to-category').show();  
          $('#add-category-modal .imgPrimary .add-image-to-category').hide();
          $('#add-category-modal .imgPrimary  input[name="image_url"]').val(admin.SITE+response.data.image_url);
        } else{
          $('#add-category-modal .imgPrimary  input[name="image_url"]').val('');
          $('#add-category-modal .imgPrimary .category-image').hide();
          $('#add-category-modal .imgPrimary .category-img-block').hide();     
          $('#add-category-modal .imgPrimary .del-image-to-category').hide();  
          $('#add-category-modal .imgPrimary .add-image-to-category').show();
        }  

        if(response.data.menu_icon) {
          $('#add-category-modal .imgSecondary .category-image').attr('src', admin.SITE+response.data.menu_icon);
          $('#add-category-modal .imgSecondary .category-image').show();
          $('#add-category-modal .imgSecondary .category-img-block').show();          
          $('#add-category-modal .imgSecondary .del-image-to-category').show();  
          $('#add-category-modal .imgSecondary .add-image-to-category').hide();
          $('#add-category-modal .imgSecondary  input[name="image_url"]').val(admin.SITE+response.data.menu_icon);
        } else{
          $('#add-category-modal .imgSecondary  input[name="image_url"]').val('');
          $('#add-category-modal .imgSecondary .category-image').hide();
          $('#add-category-modal .imgSecondary .category-img-block').hide();     
          $('#add-category-modal .imgSecondary .del-image-to-category').hide();  
          $('#add-category-modal .imgSecondary .add-image-to-category').show();
        }   
        
  
        $('.discount-rate-control input[name=rate]').val(response.data.rate == 0 ? '0' : ((response.data.rate)*100).toFixed(4));
        if(response.data.rate!=0) {
          $('.discount-setup-rate').hide();
          $('.discount-rate-control').show();
          category.setupDirRate(response.data.rate);  
        }
        
        $('.save-button').attr('id', response.data.id);
        //$('#add-category-modal textarea[name=html_content]').ckeditor(function() {});
      })
    },
    /**
     * Чистит все поля модального окна.
     */
    clearFileds: function() {

      $('#add-category-modal .category-img-block').hide();
      $('#add-category-modal .category-image').hide();
      $('#add-category-modal .add-image-to-category').show();
      $('#add-category-modal .del-image-to-category').hide(); 
      $('#add-category-modal .category-img-block img').attr('src', 'http://placehold.it/100x100');
      
      $('input[name=unit]').val('шт.');
      $('input[name=title]').val('');
      $('input[name=url]').val('');
      $('select[name=parent]').val('0');
      $('input[name=invisible]').prop('checked', false);
      $('input[name=invisible]').val('false');
      $('textarea').val('');
      $('input[name=meta_title]').val('');
      $('input[name=meta_keywords]').val('');
      $('textarea[name=meta_desc]').val('');
      $('.symbol-count').text('0');
      $('#add-category-modal  input[name="image_url"]').val('');
      $('#add-category-modal .category-image').hide();
      $('#add-category-modal .category-img-block').hide();     
      $('#add-category-wrapper .del-image-to-category').hide();  
      $('#add-category-wrapper .add-image-to-category').show();
      $('.save-button').attr('id', '');
      // Стираем все ошибки предыдущего окна если они были.
      $('.errorField').css('display', 'none');
      $('.error-input').removeClass('error-input');
      $('.discount-setup-rate').show();
      $('.discount-rate-control input[name=rate]').val(0);
      $('.discount-rate-control').hide();
      $('#add-category-wrapper .discount-rate').removeClass('color-down').addClass('color-up');
      category.setupDirRate(0);
      $('.category-desc-wrapper-seo .html-content-edit-seo').removeClass('init');
      $('input[name=discount_apply_follow]').prop('checked', false);
      $('input[name=discount_apply_follow]').val('false');
      $('.discount_apply_follow').hide();
      category.supportCkeditor = "";
      category.supportCkeditorSeo = "";
    },
            
     setupDirRate: function(rate) {    
      if(rate>=0) {
        $('#add-category-modal select[name=change_rate_dir] option[value=up]').prop('selected','selected');
        // $('#add-category-modal .discount-rate').removeClass('color-down').addClass('color-up');
        $('#add-category-modal .rate-dir').text('+');
        $('#add-category-modal .rate-dir-name').text(lang.DISCOUNT_UP);
        $('#add-category-modal .discount-rate-control input[name=rate]').val(Math.abs($('#add-category-modal .discount-rate-control input[name=rate]').val()));
      } else {
        $('#add-category-modal select[name=change_rate_dir] option[value=down]').prop('selected','selected');  
        $('#add-category-modal .rate-dir-name').text(lang.DISCOUNT_DOWN);
        // $('#add-category-modal .discount-rate').removeClass('color-up').addClass('color-down');
        $('#add-category-modal .rate-dir').text('-');
        $('#add-category-modal .discount-rate-control input[name=rate]').val(Math.abs($('#add-category-modal .discount-rate-control input[name=rate]').val()));
      }
     },        
    /**
     * устанавливает для каждой категории в списке возможность перемещения
     */
    draggableCat: function() {

      var listIdStart = [];
      var listIdEnd = [];

      $('.category-tree li').each(function() {

        $(this).addClass('ui-draggable');

        $(this).draggable({
          scroll: true,
          cursor: "move",
          handle: "div[class=mover]",
          snapMode: 'outer',
          snapTolerance: 0,
          start: function(event, ui) {
            $(this).css('width', '50%');
            $(this).parent('UL').addClass('editingCat');
            $(this).css('opacity', '0.5');
            $(this).css('height', '1px');
            var li = $(this).parent('UL').find('li');

            // составляем список ID категорий в текущем UL.
            listIdStart = [];
            var $thisId = $(this).find('a').attr('id');
            li.each(function(i) {
              if ($(this).parent('ul').hasClass('editingCat')) {
                var id = $(this).find('a').attr('id');
                if ($thisId == id) {
                  listIdStart.push('start');
                } else {
                  listIdStart.push($(this).find('a').attr('id'));
                }
              }
            });

            $(this).before('<li class="pos-element" style="display:none;"></li>'); // чтобы можно было вернуть на тоже место           
            $(this).parent('UL').append('<li class="end-pos-element"></li>'); // чтобы можно было вставить в конец списка  

          },
          stop: function(event, ui) {

            // найдем выделенный объект поместим перед ним тот который перетаскивался
            $(this).attr('style', 'style=""');
            $('.afterCat').before($(this));


            var li = $(this).parent('UL').find('li');

            // составляем список ID категорий в текущем UL.
            listIdEnd = [];
            var $thisId = $(this).find('a').attr('id');
            li.each(function(i) {
              if ($(this).parent('ul').hasClass('editingCat')) {
                var id = $(this).find('a').attr('id');
                if (id) {
                  if ($thisId == id) {
                    listIdEnd.push('end');
                  } else {
                    listIdEnd.push($(this).find('a').attr('id'));
                  }
                }
              }
            });


            $(this).parent('UL').removeClass('editingCat');
            $(this).parent('UL').find('li').removeClass('afterCat');
            $('.pos-element').remove();
            $('.end-pos-element').remove();


            var sequence = category.getSequenceSort(listIdStart, listIdEnd, $(this).find('a').attr('id'));
            if (sequence.length > 0) {
              sequence = sequence.join();
              admin.ajaxRequest({
                mguniqueurl: "action/changeSortCat",
                switchId: $thisId,
                sequence: sequence
              },
              function(response) {
                admin.indication(response.status, response.msg)
              }
              );
            }

          },
          drag: function(event, ui) {
            var dragElementTop = $(this).offset().top;
            var li = $(this).parent('UL').find('li');
            li.removeClass('afterCat');

            // проверяем, существуют ли LI ниже  перетаскиваемого.
            li.each(function(i) {
              $('.end-pos-element').removeClass('afterCat');
              if ($(this).offset().top > dragElementTop
                      && !$(this).hasClass('pos-element')
                      && $(this).parent('ul').hasClass('editingCat')
                      ) {
                $(this).addClass('afterCat');
                return false;
              } else {
                $('.end-pos-element').addClass('afterCat');
              }
            });
          }

        });
      });
    },
    /**
     * Вычисляет последовательность замены порядковых индексов 
     * Получает  для массива
     * ["1", "start", "9", "2", "10"]
     * ["1", "9", "2", "end", "10"]
     * и ID перемещенной категории
     */
    getSequenceSort: function(arr1, arr2, id) {
      var startPos = '';
      var endPos = '';

      // вычисляем стартовую позицию элемента
      arr1.forEach(function(element, index, array) {
        if (element == "start") {
          startPos = index;
          arr1[index] = id;
          return false;
        }
      });

      // вычисляем конечную позицию элемента      
      arr2.forEach(function(element, index, array) {
        if (element == "end") {
          endPos = index;
          arr2[index] = id;
          return false;
        }
      });

      // вычисляем индексы категорий с которым и надо поменяться пместами     
      var result = [];

      // направление переноса, сверху вниз
      if (endPos > startPos) {
        arr1.forEach(function(element, index, array) {
          if (index > startPos && index <= endPos) {
            result.push(element);
          }
        });
      }

      // направление переноса, снизу вверх
      if (endPos < startPos) {
        arr2.forEach(function(element, index, array) {
          if (index > endPos && index <= startPos) {
            result.unshift(element);
          }
        });
      }

      return result;
    },
  
    /**
    * функция для приема файла из аплоадера
    */         
    getFile: function(file) {      
      $('#add-category-modal .imgPrimary input[name="image_url"]').val(file.url);
      $('#add-category-modal .imgPrimary .category-image').attr('src',file.url);
      $('#add-category-modal .imgPrimary .category-img-block').show();
      $('#add-category-modal .imgPrimary .category-image').show();
      $('#add-category-modal .imgPrimary .add-image-to-category').hide();
      $('#add-category-modal .imgPrimary .del-image-to-category').show();  
    }, 

    /**
    * функция для приема файла из аплоадера
    */         
    getFileSecondary: function(file) {      
      $('#add-category-modal .imgSecondary  input[name="image_url"]').val(file.url);
      $('#add-category-modal .imgSecondary .category-image').attr('src',file.url);
      $('#add-category-modal .imgSecondary .category-img-block').show();
      $('#add-category-modal .imgSecondary .category-image').show();
      $('#add-category-modal .imgSecondary .add-image-to-category').hide();
      $('#add-category-modal .imgSecondary .del-image-to-category').show();  
    }, 
    
    /**
     * Выполняет выбранную операцию со всеми отмеченными категориями
     * operation - тип операции.
     */
    runOperation: function(operation, skipConfirm) { 
      if(typeof skipConfirm === "undefined" || skipConfirm === null){skipConfirm = false;}
      var category_id = [];
      $('.category-tree input[name=category-check]').each(function() {              
        if($(this).prop('checked')) {  
          category_id.push($(this).parents('tr').data('id'));
        }
      });  
      if (skipConfirm || confirm(lang.RUN_CONFIRM)) {
        var dropProducts = 'false';
        if ((operation == 'delete') && confirm(lang.SUB_CATEGORY_DELETE_PROD)) {dropProducts = 'true';}
        admin.ajaxRequest({
          mguniqueurl: "action/operationCategory",
          operation: operation,
          category_id: category_id,
          dropProducts: dropProducts
        },
        function(response) { 
          admin.refreshPanel();  
        });
      }
    }, 
    
    /**
     * 
     * Упорядочивает всё дерево категорий по алфавиту 
     */
    sortToAlphabet: function() { 
          
      if (confirm(lang.ARRANGE_TREE_QUEST)) {        
        admin.ajaxRequest({
          mguniqueurl: "action/sortToAlphabet",      
        },
        function(response) {         
          admin.refreshPanel();  
        }
        );
      }
       

    },
    sortableInit: function() {
      $(".section-category .main-table tbody tr").hover( 
        function() {
          group = $(this).data('group');

          var trCount = $('.section-category .main-table tbody tr').length;
          for(i = 0; i < trCount; i++) {
            if($('.section-category .main-table tbody tr:eq('+i+')').hasClass(group)) {
              $('.section-category .main-table tbody tr:eq('+i+')').removeClass('disableSort');
            } else {
              $('.section-category .main-table tbody tr:eq('+i+')').addClass('disableSort');
            }
          }
        }
      );

      $(".section-category .main-table tbody").sortable({
        handle: '.mover',
        start: function(event, ui) {
          group = $(ui.item).data('group');

          var trCount = $('.section-category .main-table tbody tr').length;
          for(i = 0; i < trCount; i++) {
            if(!$('.section-category .main-table tbody tr:eq('+i+')').hasClass(group)) {
              $('.section-category .main-table tbody tr:eq('+i+')').addClass('disabled');
            } else {
              $('.section-category .main-table tbody tr:eq('+i+')').removeClass('disabled');
            }
          }
        },
        sort: function(e) {
          var Y = e.pageY; // положения по оси Y
          $('.ui-sortable-helper').offset({ top: (Y-10)});
        },
        items: 'tr:not(.disableSort)',
        helper: fixHelperCategory,
        stop: function() {
          category.saveSort();
        }
      }).disableSelection();
    },
    // сохранение порядка сортировки
    saveSort: function() {
      data = [];   

      // составление массива строк с индефикаторами, для отправки на сервер, для сохранения позиций
      $.each( $('.section-category .main-table tbody tr'), function() {
        data.push($(this).data('id'));
      });         

      admin.ajaxRequest({
        mguniqueurl: "action/saveSortableTable",
        data: data,
        type: 'category'
      },
      function (response) {
        admin.indication(response.status, response.msg);
        admin.refreshPanel();
      });
    },
    // скрывает спрятанные пункты
    hidePageRows: function() {
      if(category.openedCategoryAdmin == undefined) {
        category.openedCategoryAdmin = [];
      }

      for(var i = 0; i < category.openedCategoryAdmin.length; i++) {
        if(category.openedCategoryAdmin[i] != undefined) {
          if($('#toHide-'+category.openedCategoryAdmin[i]).html() != undefined) {
            if(category.clickedId.indexOf(category.openedCategoryAdmin[i]) == -1) {
              $('#toHide-'+category.openedCategoryAdmin[i]).click();
              category.clickedId.push(category.openedCategoryAdmin[i]);
            }
          }
        }
      }
    },
    // для повторной инициализации всех необходимых скриптов
    repeatInit: function() {
      category.sortableInit();
      category.clickedId = [];
      category.humanClick = false;
      category.hidePageRows();
    },
  }
})();

// инициализациямодуля при подключении
category.init();

var fixHelperCategory = function(e, ui) {
  trStyle = "color:#1585cf!important;background-color:#fff!important;";

  // берем id текущей строки
  var id = $(ui).data('id');
  // достаем уровень вложенности данной строки
  var level = $(ui).data('level');
  level++;

  // берем порядковый номер текущей строки
  // thisSortNumber = $(ui).data('sort');
  $('.section-category .main-table tbody tr').each(function(index) {
    if($(this).data('id') == id) {
      thisSortNumber = index;
      return false;
    }
  }); 

  // фикс скрола
  $('.section-category .table-wrapper').css('overflow', 'visible');

  // поиск ширины для жесткой записи, чтобы не разебывалось
  width = $('.section-category .main-table').width();
  width *= 0.9;

  uiq = '<div style="width:'+width+'px;position:fixed;"><table style="width:100%;"><tr style="'+trStyle+'">'+$(ui).html()+'</tr>';

  group = $(ui).data('group');

  var trCount = $('.section-category .main-table tbody tr').length;
  for(i = thisSortNumber+1; i < trCount; i++) {
    if(($('.section-category .main-table tbody tr:eq('+i+')').hasClass(group)) || (($('.section-category .main-table tbody tr:eq('+i+')').data('level') < level))) {
      break;
    } else {
      if(($('.section-category .main-table tbody tr:eq('+i+')').data('level') >= level)) {
        uiq += '<tr style="'+trStyle+'display:'+$('.section-category .main-table tbody tr:eq('+i+')').css('display')+'">'+$('.section-category .main-table tbody tr:eq('+i+')').html()+'</tr>';
        $('.section-category .main-table tbody tr:eq('+i+')').css('display','none');
      }
    }
  }

  uiq += '</table></div>';

  return uiq;
};