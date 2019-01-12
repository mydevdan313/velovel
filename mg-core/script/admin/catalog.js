
/**
 * Модуль для  раздела "Товары".
 */
var catalog = (function () {
  return {
    errorVariantField: false,
    memoryVal: null, // HTML редактор для   редактирования страниц
    supportCkeditor: null,
    deleteImage: '', // список картинок помеченных на удаление, при сохранении товара, данный список передается на сервер и картинки удаляются физически
    tmpImage2Del: '',
    dragActive: false,
    selectedStorage: 'all',
    modalUnit: 'шт.',
    /**
     * Инициализирует обработчики для кнопок и элементов раздела.
     */
    init: function() {
      includeJS(admin.SITE+'/mg-core/script/jquery.bxslider.min.js');

      $('body').on('change', '#productCategorySelect', function() {
        if($('#productCategorySelect').val() == 0) {
          $('.add-property-field').hide();
        } else {
          $('.add-property-field').show();
        }
        // $('.btn-selected-typeGroupVar').click();

        
      });

      $('body').on('change', '.tipo-radio input', function() {
        if($(this).prop('checked')) {
          $('.tipo-radio[data-group=salo] input').prop('checked', false);
          $(this).prop('checked', true);
        } else {
          $('.tipo-radio[data-group=salo] input').prop('checked', false);
        }
      });

      // для показа картинок вариантов
      $(document).on({
        mouseenter: function () {
          $(this).parents('tr').find('.img-this-variant').show();
        },
        mouseleave: function () {
          $(this).parents('tr').find('.img-this-variant').hide();
        }
      }, ".admin-center .del-img-variant"); //pass the element as an argument to .on

      $('body').on('click','#add-product-wrapper .expandModal', function() {
        if ($('#add-product-wrapper').hasClass('fullscreen')) {
          $('#add-product-wrapper').removeClass('fullscreen');
          $('#add-product-wrapper .expandModal i').addClass('fa-expand').removeClass('fa-compress');
        }
        else{
          $('#add-product-wrapper').addClass('fullscreen');
          $('#add-product-wrapper .expandModal i').removeClass('fa-expand').addClass('fa-compress');
        }
      });

      $('.admin-center').on('click','.section-catalog .showAllVariants', function() {
        $(this).parents('tr').find('.second-block-varians').show();
        $(this).parents('tr').find('.moreVariantsCount').removeClass('moreVariantsCount');
        $(this).detach();
      });

      $('body').on('click', '.btn-selected-typeGroupVar', function() {
        $('.select-typeGroupVar').show();
      });

      $('body').on('click', '.cancel-typeGroupVar', function() {
        $('.select-typeGroupVar').hide();
      });

      $('body').on('click', '.add-short-desc', function() {
        $('.shortDesc').css('display', 'inline-block');
        $('.add-short-desc').hide();
      });

      $('body').on('click', '.apply-typeGroupVar', function() {
        catalog.saveVarTable = $('.variant-table .variant-row, .variant-table .text-left').clone();
        catalog.saveTypeGroupVar = $('.select-typeGroupVar select').val();
        catalog.buildGroupVarTable();
        $('.select-typeGroupVar').hide();
      });

      // открытие выгрузок
      $('.admin-center').on('click','.catalog_uploads_container_wrapper .additional_catalog_uploads_container div', function() {
        if ($(this).attr('part') == 'Csv') {
          catalog.exportToCsv();
          return false;
        }
        else{
          admin.SECTION = 'integrations';
          cookie('setting-active-tab','#'+'tab-Integration');
          cookie("integrationPart", $(this).attr('part'));
          admin.show("integrations.php", "adminpage", cookie(admin.SECTION + "_getparam"));
        }
      });
      $('.admin-center').on('click','.catalog_uploads_container_wrapper a', function() {
        catalog.exportToCsv();
        return false;
      });

      // смена языка товара
      $('body').on('change','.section-catalog .select-lang', function() {
        if($('#add-product-wrapper .save-button').attr('id') == '') return false;
        $('#add-product-wrapper .related-block').html('');
        catalog.editProduct($('#add-product-wrapper .save-button').attr('id'));     
      });

      // смена единиц измерения товара - открытие окна
      $('body').on('click','#add-product-wrapper .btn-selected-unit', function() {
        $('#add-product-wrapper .input-unit-block').show();
      });

      // смена единиц измерения товара - сохранение
      $('body').on('click','#add-product-wrapper .input-unit-block .apply-unit', function() {
        $('#add-product-wrapper .input-unit-block').hide();
        var unit = $('#add-product-wrapper .input-unit-block .unit-input').val();
        $('#add-product-wrapper .btn-selected-unit').attr('realunit', unit);
        if (unit == '') {unit = catalog.realCatUnit}
        $('#add-product-wrapper .btn-selected-unit').text(unit);
      });

      // смена единиц измерения товара - отмена
      $('body').on('click','#add-product-wrapper .input-unit-block .cancel-unit', function() {
        $('#add-product-wrapper .input-unit-block').hide();
        var unit = $('#add-product-wrapper .btn-selected-unit').attr('realunit');
        $('#add-product-wrapper .input-unit-block .unit-input').val(unit).text(unit);
      });

      //вызов модального окна для редактирования полей таблицы
      $('.admin-center').on('click', '.section-catalog .catalog-col-config', function() {
        admin.openModal('#catalog-col-display-modal');
      });

      $('.admin-center').on('change', '[name=importScheme]', function() {
        if($('[name=importScheme]').val() != 'none') {
          $('.start-import').prop('disabled', false);
        } else {
          $('.start-import').prop('disabled', true);
        }
      });

      //сохранение модального окна для редактирования полей таблицы
      $('.admin-center').on('click', '.section-catalog #catalog-col-display-modal .save', function() {
        admin.ajaxRequest({
          mguniqueurl:"action/saveColsCatalog",
          catalogNumber: $("#catalog-col-display-modal #catalog-number").prop('checked'),
          catalogCategory: $("#catalog-col-display-modal #catalog-category").prop('checked'),
          catalogImg: $("#catalog-col-display-modal #catalog-img").prop('checked'),
          catalogPrice: $("#catalog-col-display-modal #catalog-price").prop('checked'),
          catalogCode: $("#catalog-col-display-modal #catalog-code").prop('checked'),
          catalogOrder: $("#catalog-col-display-modal #catalog-order").prop('checked'),
          catalogCount: $("#catalog-col-display-modal #catalog-count").prop('checked')
        },

        function(response) {
          admin.indication(response.status, 'Сохранено');
          admin.closeModal('#catalog-col-display-modal');
          admin.refreshPanel();
        });
      });

      // Вызов модального окна при нажатии на кнопку добавления товаров.
      $('.admin-center').on('click', '.section-catalog .add-new-button', function() {


        catalog.openModalWindow('add');
      });

      /*Инициализирует CKEditior*/
      $('body').on('click', '#add-product-wrapper .html-content-edit', function() {
        if (catalog.initSupportCkeditor) {
          $('textarea[name=html_content]').ckeditor(function () {
            this.setData(catalog.supportCkeditor);
          });
        }
        catalog.initSupportCkeditor = false;
        setTimeout(function () {
          CKEDITOR.instances['html_content'].config.filebrowserUploadUrl = admin.SITE + '/ajax?mguniqueurl=action/upload_tmp';
        }, 1500);
      });

      // Показывает панель с фильтрами.
      $('.admin-center').on('click', '.section-catalog .show-filters', function() {
        $('.import-container').slideUp();
        $('.filter-container').slideToggle(function() {
          $('.widget-table-action').toggleClass('no-radius');
        });
      });



      // Выделить все страницы
      $('.admin-center').on('click', '.section-catalog .check-all-page', function () {
        $('.product-tbody input[name=product-check]').prop('checked', 'checked');
        $('.product-tbody input[name=product-check]').val('true');
        $('.product-tbody tr').addClass('selected');

        $(this).addClass('uncheck-all-page');
        $(this).removeClass('check-all-page');
      });
      // Снять выделение со всех  страниц.
      $('.admin-center').on('click', '.section-catalog .uncheck-all-page', function () {
        $('.product-tbody input[name=product-check]').prop('checked', false);
        $('.product-tbody input[name=product-check]').val('false');
        $('.product-tbody tr').removeClass('selected');
        
        $(this).addClass('check-all-page');
        $(this).removeClass('uncheck-all-page');
      });

      // Применение выбранных фильтров
      $('.admin-center').on('click', '.section-catalog .filter-now', function() {
        catalog.getProductByFilter();
        return false;
      });

      // показывает все фильтры в заданной характеристике
      $('.admin-center').on('click', '.section-catalog .mg-filter-item .mg-viewfilter', function() {
        $(this).parents('ul').find('li').fadeIn();
        $(this).hide();
      });

       // показывает все группы фильтров
      $('.admin-center').on('click', '.section-catalog .mg-viewfilter-all', function() {
        $(this).hide();
        $('.mg-filter-item').fadeIn();
      });

      // Вызов модального окна при нажатии на кнопку изменения товаров.
      $('.admin-center').on('click', '.section-catalog .clone-row', function() {
        catalog.cloneProd($(this).attr('id'), $(this).parents('.product-row'));

      });

      // показывает настроки импорта csv
      $('.admin-center').on('click', '.section-catalog .import-csv', function() {
        $('.filter-container').slideUp();
        $('.import-container').slideToggle(function() {
          $('.widget-table-action').toggleClass('no-radius');
        });
      });

      // Обработчик для загрузки файла импорта из CSV
      $('body').on('change', '.section-catalog input[name="upload"]', function() {
        catalog.uploadCsvToImport();
      });

      // Обработчик для смены категории
      $('body').on('change', '.section-catalog .filter-container select[name="cat_id"]', function() {
        var cat_id= $('.section-catalog .filter-container select[name="cat_id"]').val();
        if(cat_id=="null") {
          cat_id = 0;
        }
        admin.show("catalog.php", cookie("type"), "page=0&cat_id=" + cat_id + '&displayFilter=1', catalog.callbackProduct);
      });
      // Обработчик для  переключения вывода товаров подкатегорий
      $('body').on('change', '.section-catalog .filter-container input[name="insideCat"]', function() {
        var cat_id= $('.section-catalog .filter-container select[name="cat_id"]').val();
        if(cat_id=="null") {
            cat_id = 0;
        }
        var request = $("form[name=filter]").formSerialize();
        var insideCat = $(this).prop('checked');
        admin.show("catalog.php", cookie("type"), request+"&page=0&insideCat="+insideCat+"&cat_id=" +cat_id + '&displayFilter=1', catalog.callbackProduct);
      });

      // Обработчик для загрузки файла импорта из CSV
      $('body').on('click', '.section-catalog .repeat-upload-csv', function() {
        $('.import-container input[name="upload"]').val('');
        $('.repeat-upload-file').hide();
        $('.upload-btn').show();
        $('.cancel-importing').hide();
        $('select[name=importScheme]').attr('disabled', 'disabled');
        $('select[name=identifyType]').attr('disabled', 'disabled');
        $('input[name=no-merge]').removeAttr("checked");
        $('input[name=no-merge]').val(false);
        $('input[name=no-merge]').attr('disabled', 'disabled');
        $('.message-importing').text('');
        catalog.STOP_IMPORT=false;
      });

      $('body').on('click', '.section-catalog .columnComplianceModal .closeModal', function() {
        $('.section-catalog input[name="upload"]').val('');
        $('.repeat-upload-file').hide();
        $('.block-upload-сsv, .upload-btn').show();
      });

      // Обработчик для загрузки изображения на сервер, сразу после выбора.
      $('body').on('click', '.section-catalog .start-import', function() {
        if(!confirm(lang.CATALOG_LOCALE_1)) {
          $('.section-catalog input[name="upload"]').val('');
          $('.repeat-upload-file').hide();
          $('.block-upload-сsv, .upload-btn').show();
          return false;
        } else {
          if(!catalog.startImport($('.block-importer .uploading-percent').text())) {
            admin.closeModal($('.columnComplianceModal'));
            $('.repat-upload-file').hide();
            $('.block-upload-сsv').hide();
            $('.cancel-import').show();
            $('.get-example-csv').hide();
          }
        }
      });

      // Останавливает процесс загрузки товаров.
      $('body').on('click', '.section-catalog .cancel-import', function() {
        catalog.canselImport();
      });

       // Открывает список  дополнительных категорий
      $('body').on('click', '#add-product-wrapper .add-category', function() {
        $(this).toggleClass('open');
        if($(this).hasClass('open')) {
          $('.inside-category').show();
        } else {
          $('.inside-category').hide();
        }
      });
      
      $('body').on('click', '.section-catalog .backToCsv', function() {
        $('.block-upload-images').hide();
        $('.block-upload-сsv').show();
        $('.csv-import-title').show();
        $('.img-import-title').hide();
      });

      // снимает выделение со всех дополнительных категорий
      $('body').on('click', '#add-product-wrapper .clear-select-cat', function() {
        $(this).parents('.inside-category').find('select option').prop('selected', false);
      });
        // снимает выделение со всех опций в характеристике 
      $('body').on('click', '#add-product-wrapper .clear-select-property', function() {
        $(this).parents('.price-settings').find('select option').prop('selected', false);
      });

      // разворачивает список всех дополнительных категорий
      $('body').on('click', '#add-product-wrapper .full-size-select-cat.closed-select-cat', function() {
        var name = $(this).parents('.inside-category').find('select').attr('name');
        $('select[name='+name+']').attr('size',$('select[name=inside_cat] option').length);
        $(this).removeClass('closed-select-cat').addClass('opened-select-cat');
        $(this).text(lang.PROD_CLOSE_CAT);
      });

      $('body').on('click', '.yml-title', function() {
        $(this).toggleClass('opened').toggleClass('closed');
        $('.yml-wrapper').slideToggle(300);
        if($(this).hasClass('opened')) {
          $(this).html(lang.CATALOG_LOCALE_2);
        }
        else {
          $(this).html(lang.CATALOG_LOCALE_3);
        }
      });

      // сворачивает список всех дополнительных категорий
      $('body').on('click', '#add-product-wrapper .full-size-select-cat.opened-select-cat', function() {
        var name = $(this).parents('.inside-category').find('select').attr('name');
        $('select[name='+name+']').attr('size',4);
        $(this).removeClass('opened-select-cat').addClass('closed-select-cat');
        $(this).text(lang.PROD_OPEN_CAT);
      });

      // для рекомендованных категорий
      // разворачивает список всех дополнительных категорий
      $('body').on('click', '#add-product-wrapper .full-size-select-cat.closed-select-cat', function() {
        $('select[name=related_cat]').attr('size',$('select[name=related_cat] option').length);
        $(this).removeClass('closed-select-cat').addClass('opened-select-cat');
        $(this).text(lang.PROD_CLOSE_CAT);
      });
      // сворачивает список всех дополнительных категорий
      $('body').on('click', '#add-product-wrapper .full-size-select-cat.opened-select-cat', function() {
        $('select[name=related_cat]').attr('size',4);
        $(this).removeClass('opened-select-cat').addClass('closed-select-cat');
        $(this).text(lang.PROD_OPEN_CAT);
      });
       // снимает выделение со всех дополнительных категорий
      $('body').on('click', '#add-product-wrapper .clear-select-cat-related', function() {
        $('select[name=related_cat] option').prop('selected', false);
      });


      // применение выбраной валюты
      $('body').on('click', '#add-product-wrapper .apply-currency', function() {
        catalog.changeIso();
      });

      // отмена выбора валюты
      $('body').on('click', '#add-product-wrapper .cancel-currency', function() {
        $('#add-product-wrapper .select-currency-block').hide();
      });


      // Вызов модального окна при нажатии на кнопку изменения товаров.
      $('.admin-center').on('click', '.section-catalog .edit-row', function() {


        catalog.openModalWindow('edit', $(this).attr('id'));
      });

      // Удаление товара.
      $('.admin-center').on('click', '.section-catalog .delete-order', function() {
        catalog.deleteProduct(
          $(this).attr('id'),
          $('tr[id='+$(this).attr('id')+'] .uploads').attr('src'),
          false,
          $(this)
        );
      });

      // Нажатие на кнопку - рекомендуемый товар
      $('.admin-center').on('click', '.section-catalog .recommend', function() {
        $(this).find('a').toggleClass('active');
        var id = $(this).data('id');

        if($(this).find('a').hasClass('active')) {
          catalog.recomendProduct(id, 1);
          $(this).find('a').attr('title', lang.PRINT_IN_RECOMEND);
        }
        else {
          catalog.recomendProduct(id, 0);
          $(this).find('a').attr('title', lang.PRINT_NOT_IN_RECOMEND);
        }
        $('#tiptip_holder').hide();
        admin.initToolTip();
      });

      // Нажатие на кнопку - активный товар
      $('.admin-center').on('click', '.section-catalog .visible', function() {
        $(this).find('a').toggleClass('active');
        var id = $(this).data('id');

        if($(this).find('a').hasClass('active')) {
          catalog.visibleProduct(id, 1);
          $(this).find('a').attr('title', lang.ACT_V_PROD);
        }
        else {
          catalog.visibleProduct(id, 0);
          $(this).find('a').attr('title', lang.ACT_UNV_PROD);
        }
        $('#tiptip_holder').hide();
        admin.initToolTip();
      });

       // Нажатие на кнопку - новый товар
      $('.admin-center').on('click', '.section-catalog .new', function() {
        $(this).find('a').toggleClass('active');
        var id = $(this).data('id');

        if($(this).find('a').hasClass('active')) {
          catalog.newProduct(id, 1);
          $(this).find('a').attr('title', lang.PRINT_IN_NEW);
        }
        else {
          catalog.newProduct(id, 0);
          $(this).find('a').attr('title', lang.PRINT_NOT_IN_NEW);
        }
        $('#tiptip_holder').hide();
        admin.initToolTip();
      });

      // Выделить все товары.
      $('.admin-center').on('click', '.section-catalog .checkbox-cell input[name=product-check]', function() {

        if($(this).val()!='true') {
          $('.product-tbody input[name=product-check]').prop('checked','checked');
          $('.product-tbody input[name=product-check]').val('true');
        } else {
          $('.product-tbody input[name=product-check]').prop('checked', false);
          $('.product-tbody input[name=product-check]').val('false');
        }
      });

      // Сброс фильтров.
      $('.admin-center').on('click', '.section-catalog .refreshFilter', function() {
        admin.clearGetParam();
        admin.show("catalog.php","adminpage","refreshFilter=1",admin.sliderPrice);
        return false;
      });

     // Обработка выбранной категории (перестраивает пользовательские характеристики).
      $('body').on('change', '#productCategorySelect', function() {
        //достаем id редактируемого продукта из кнопки "Сохранить"
        var product_id=$(this).parents('#add-product-wrapper').find('.save-button').attr('id');
        var category_id=$(this).val();
        catalog.generateUserProreprty(product_id, category_id);
        $('.size-map').hide();

      });

      // Обработчик для загрузки изображения на сервер, сразу после выбора.
      $('body').on('change', '.add-img-block input[name="photoimg"]', function() {
        var currentImg = '';
        var img_container = $(this).parents('.parent');

        if(!img_container.attr('class')) {
          img_container = $(this).parents('.variant-row');
        }

        if(img_container.find('img').length > 0) {
          currentImg = img_container.find('img').attr('alt');
        } else {
          currentImg = img_container.find('img').attr('filename');
        }

        //Пишем в поле deleteImage имена изображений, которые необходимо будет удалить при сохранении
        if(catalog.deleteImage) {
          catalog.deleteImage += '|'+currentImg;
        } else {
          catalog.deleteImage = currentImg;
        }
        if($(this).val()) {
          catalog.addImageToProduct(img_container);
        }
      });

      //открытие файлового менеджера
      $('body').on('click', '#add-product-wrapper .main-image .additional_uploads_container .from_file', function() {
        admin.openUploader('catalog.uploaderCallback');
      });

      //открытие файлового менеджера (варианты)
      $('body').on('click', '#add-product-wrapper .catalog_uploads_container_variants_wrapper .from_file', function() {
        catalog.lastVariant = $(this);
        admin.openUploader('catalog.uploaderCallbackVariant');
      });

      //открытие всплывалки для ввода ссылки
      $('body').on('click', '#add-product-wrapper .main-image .additional_uploads_container .from_url', function() {
        $('#add-product-wrapper .main-image .url-popup').show();
      });

      //открытие окна для выбора с компа (варианты)
      $('body').on('click', '#add-product-wrapper .catalog_uploads_container_variants_wrapper .from_pc', function() {
        $(this).parents('form').find('label').click();
      });

      //открытие всплывалки для ввода ссылки (варианты)
      $('body').on('click', '#add-product-wrapper .catalog_uploads_container_variants_wrapper .from_url', function() {
        $('#add-product-wrapper .catalog_uploads_container_variants_wrapper .url-popup').hide();
        $('#add-product-wrapper .catalog_uploads_container_variants_wrapper .existing-popup').html('').hide();
        $(this).parents('form').find('.url-popup').show();
      });

      //закрытие всплывалки для ввода ссылки
      $('body').on('click', '#add-product-wrapper .main-image .url-popup .cancel-url', function() {
        $('#add-product-wrapper .main-image .url-popup').hide();
      });

      //закрытие всплывалки для ввода ссылки (варианты)
      $('body').on('click', '#add-product-wrapper .catalog_uploads_container_variants_wrapper .url-popup .cancel-url', function() {
        $('#add-product-wrapper .catalog_uploads_container_variants_wrapper .url-popup').hide();
      });

      //применение всплывалки для ввода ссылки
      $('body').on('click', '#add-product-wrapper .main-image .url-popup .apply-url', function() {

        var imgUrl = $('#add-product-wrapper .main-image .url-popup input').val();

        admin.ajaxRequest({
          mguniqueurl:"action/addImageUrl",
          imgUrl: imgUrl,
          isCatalog: 'true'
        },

        function(response) {
          admin.indication(response.status, response.msg);
          var mainurl = $('.main-image').find('img').attr('src').substr(-12).toLowerCase();

          if (response.status == 'success') {
            if (mainurl.indexOf('no-img.') >= 0) {
              var src = admin.SITE+'/uploads/'+response.data;
              $('.main-image').find('img').attr('src',src);
              // if ($('#add-product-wrapper input[name="title"]').val().length) {
              //   $('.images-block img:last').attr('alt', $('#add-product-wrapper input[name="title"]').val());
              // }
              // else{
                $('.main-image').find('img').attr('alt',response.data);
              // }
            }
            if (mainurl.indexOf('no-img.') < 0) {
              var src = admin.SITE+'/uploads/'+response.data;
              var ttle = response.data.replace('prodtmpimg/', '');
              var row = catalog.drawControlImage(src, true,'','','');
              $('.sub-images').append(row);
              // if ($('#add-product-wrapper input[name="title"]').val().length) {
              //   $('.images-block img:last').attr('alt', $('#add-product-wrapper input[name="title"]').val());
              // }
              // else{
                $('.images-block img:last').attr('alt', response.data);
              // }
            }
            $('#add-product-wrapper .main-image .url-popup input').val('');
          }
        });

        $('#add-product-wrapper .main-image .url-popup').hide();
      });

      //применение всплывалки для ввода ссылки (варианты)
      $('body').on('click', '#add-product-wrapper .catalog_uploads_container_variants_wrapper .url-popup .apply-url', function() {

        var imgUrl = $(this).parents('form').find('.url-popup').find('input').val();
        var obje = $(this);

        admin.ajaxRequest({
          mguniqueurl:"action/addImageUrl",
          imgUrl: imgUrl,
          isCatalog: 'true'
        },

        function(response) {
          admin.indication(response.status, response.msg);

          if (response.status == 'success') {
            var src = admin.SITE+'/uploads/'+response.data;
            obje.parents('ul').find('.img-this-variant').find('img').attr('src', src).attr('alt', src).data('filename', src);
            $('#add-product-wrapper .catalog_uploads_container_variants_wrapper .url-popup').hide();
            obje.parents('tr').find('.img-button').hide();
            obje.parents('tr').find('.del-img-variant').show();
            catalog.updateImageVar();
          }
        });

        $('#add-product-wrapper .main-image .url-popup').hide();
      });

      //открытие всплывалки для выбора из старых картинок (варианты)
      $('body').on('click', '#add-product-wrapper .catalog_uploads_container_variants_wrapper .from_existing', function() {

        var srcs = [];
        var src = '';
        var html = '';
        $('#add-product-wrapper .catalog_uploads_container_variants_wrapper .url-popup').hide();
        $('#add-product-wrapper .catalog_uploads_container_variants_wrapper .existing-popup').html('').hide();
        $('#add-product-wrapper .add-img-block img').each(function(index,element) {

          src = $(this).attr('src');
          if (src.indexOf('no-img') >= 0) {src = '';}
          
          if (src != undefined && src != null && src != '') {
            html += '<div class="img-holder-variant"><img src="'+src+'"></div>';
          }
        });
        html += '<div class="row">\
                  <div class="large-12 columns">\
                    <a class="button fl-left cancel-existing" href="javascript:void(0);"><i class="fa fa-times"></i> '+lang.CANCEL+'</a>\
                  </div>\
                </div>';

        $(this).parents('form').find('.existing-popup').html(html).show();
        catalog.updateImageVar();
      });

      //закрытие всплывалки для выбора из старых картинок (варианты)
      $('body').on('click', '#add-product-wrapper .catalog_uploads_container_variants_wrapper .existing-popup .cancel-existing', function() {
        $('#add-product-wrapper .catalog_uploads_container_variants_wrapper .existing-popup').hide();
      });

      //применение всплывалки для выбора из старых картинок (варианты)
      $('body').on('click', '#add-product-wrapper .catalog_uploads_container_variants_wrapper .existing-popup .img-holder-variant', function() {
        var src = $(this).find('img').attr('src');
        $(this).parents('ul').find('.img-this-variant').find('img').attr('src', src).attr('alt', src).data('filename', src);
        $('#add-product-wrapper .catalog_uploads_container_variants_wrapper .existing-popup').hide();
        $(this).parents('tr').find('.img-button').hide();
        $(this).parents('tr').find('.del-img-variant').show();
        catalog.updateImageVar();
      });

      //появление дропзоны
      $(document).on('drag dragstart dragover dragenter', function(e) {
        $('.mg-admin-html #add-product-wrapper .main_img_input').addClass('dragover');
        // $('.mg-admin-html .main_img_input').parent().parent().parent().css('overflow', 'visible');
        $('.mg-admin-html #add-product-wrapper .img-dropzone').show();
        catalog.dragActive = true;
      });

      //исчезновение дропзоны
      $(document).on('dragend drop mouseleave mouseout', function(e) {
        $('.mg-admin-html #add-product-wrapper .main_img_input').delay(1000).removeClass('dragover');
        // $('.mg-admin-html .main_img_input').delay(1000).parent().parent().parent().css('overflow', 'hidden');
        catalog.dragActive = false;
        $('.mg-admin-html #add-product-wrapper .img-dropzone').delay(1000).hide();
      });

      //появление дропзоны
      $(document).on('mouseover', '#add-product-wrapper .main-image .img-holder', function() {
        if (catalog.dragActive == false) {
          $('.mg-admin-html #add-product-wrapper .img-dropzone').show();
        }
      });

      //исчезновение дропзоны
      $(document).on('mouseout mouseleave', '#add-product-wrapper .main-image .img-holder', function() {
        catalog.dragActive = false;
        $('.mg-admin-html #add-product-wrapper .img-dropzone').hide();
        $('.mg-admin-html #add-product-wrapper .main_img_input').delay(1000).removeClass('dragover');
        // $('.mg-admin-html .main_img_input').delay(1000).parent().parent().parent().css('overflow', 'hidden');
      });

      // Обработчик для загрузки изображений на сервер, сразу после выбора.
      $('body').on('change', '.add-img-block .main_img_input', function() {
        var mainurl = $('.main-image').find('img').attr('src').substr(-12).toLowerCase();
        var filesAmount = this.files.length;

        if (mainurl.indexOf('no-img.') >= 0 && filesAmount == 1) {
          var act = 'replace';
        }
        if (mainurl.indexOf('no-img.') < 0) {
          var act = 'add';
        }
        if (mainurl.indexOf('no-img.') >= 0 && filesAmount > 1) {
          var act = 'replace and add';
        }

        $(this).parents('.imageform').ajaxSubmit({
          type:"POST",
          url: "ajax",
          data: {
            mguniqueurl:"action/addImageMultiple"
          },
          cache: false,
          dataType: 'json',
          success: function(response) {
            admin.indication(response.status, response.msg);
            if(response.data.length) {
              var imgCount = response.data.length;

              if (act == 'replace' && imgCount > 0) {
                var src = admin.SITE+'/uploads/'+response.data[0];
                // catalog.tmpImage2Del += '|'+response.data.img;
                $('.main-image').find('img').attr('src',src);
                // if ($('#add-product-wrapper input[name="title"]').val().length) {
                //   $('.main-image').find('img').attr('alt', $('#add-product-wrapper input[name="title"]').val());
                // }
                // else{
                  $('.main-image').find('img').attr('alt',response.data[0]);
                // }
              }

              if (act == 'add' && imgCount > 0) {
                for (var i = 0; i < imgCount; i++) {
                  var src = admin.SITE+'/uploads/'+response.data[i];
                  var ttle = response.data[i].replace('prodtmpimg/', '');
                  var row = catalog.drawControlImage(src, true,'','','');
                  $('.sub-images').append(row);
                  // if ($('#add-product-wrapper input[name="title"]').val().length) {
                  //   $('.images-block img:last').attr('alt', $('#add-product-wrapper input[name="title"]').val());
                  // }
                  // else{
                    $('.images-block img:last').attr('alt', response.data[i]);
                  // }
                }
              }

              if (act == 'replace and add' && imgCount > 0) {
                var src = admin.SITE+'/uploads/'+response.data[0];
                // catalog.tmpImage2Del += '|'+response.data.img;
                $('.main-image').find('img').attr('src',src);
                // if ($('#add-product-wrapper input[name="title"]').val().length) {
                //   $('.main-image').find('img').attr('alt', $('#add-product-wrapper input[name="title"]').val());
                // }
                // else{
                  $('.main-image').find('img').attr('alt',response.data[0]);
                // }

                for (var i = 1; i < imgCount; i++) {
                  var src = admin.SITE+'/uploads/'+response.data[i];
                  var ttle = response.data[i].replace('prodtmpimg/', '');
                  var row = catalog.drawControlImage(src, true,'','','');
                  $('.sub-images').append(row);
                  // if ($('#add-product-wrapper input[name="title"]').val().length) {
                  //   $('.images-block img:last').attr('alt', $('#add-product-wrapper input[name="title"]').val());
                  // }
                  // else{
                    $('.images-block img:last').attr('alt', response.data[i]);
                  // }
                }
              }
            }
          }
        });

      });

      // Добавляет ссылку на электронный товар
      $('body').on('click', '.add-link-electro', function() {
         admin.openUploader('catalog.getFileElectro');
         $('#overlay:last').css('z-index', '100');
      });

      // Удаляет ссылку на электронный товар
      $('body').on('click', '.del-link-electro', function() {
         $('.section-catalog input[name="link_electro"]').val('');
         $('.del-link-electro').hide();
         $('.add-link-electro').show();
      });


      // Удаление изображения товара, как из БД таи физически с сервера.
      $('body').on('click', '.cancel-img-upload', function() {
        var img_container = $(this).parents('.parent');
        catalog.delImageProduct($(this).attr('id'),img_container);
      });

      // Сохранение продукта при на жатии на кнопку сохранить в модальном окне.
      $('body').on('click', '#add-product-wrapper .save-button', function() {
        catalog.saveProduct($(this).attr('id'));
      });

       // Нажатие ентера при вводе в строку поиска товара
      $('body').on('keypress', '.widget-panel input[name=search]', function(e) {
        if(e.keyCode==13) {
          catalog.getSearch($(this).val());
          $(this).blur();
        }
      });

      // Нажатие лупы при вводе в строку поиска товара
      $('body').on('click', '.widget-panel .search-block .fa-search', function() {
          catalog.getSearch($('.widget-panel input[name=search]').val());
          $('.widget-panel input[name=search]').blur();
      });

      // Нажатие пагинации при поиске товара
      $('body').on('click', '.mg-pager .linkPageCatalog', function() {
        var pageId = admin.getIdByPrefixClass($(this), 'page');
        catalog.getSearch($('.widget-panel input[name=search]').val(), pageId, 'nope');
      });

       // Добавить вариант товара
      $('body').on('click', '.variant-table-wrapper .add-position', function() {
        catalog.addVariant($('.variant-table'));

        $('.variant-table th:eq(0)').show();
        $('.variant-table tr').each(function() {
          $(this).find('td:eq(0)').show();
        });

        if(($('.storageToView').html() != undefined) && ($('.storageToView').val() == 'all')) {
          $('.variant-table [name=count]').prop('disabled', true);
        } else {
          $('.variant-table [name=count]').prop('disabled', false);
        }
      });

       // Удалить вариант товара
      $('body').on('click', '#add-product-wrapper .del-variant', function() {
          if(!confirm(lang.CATALOG_LOCALE_4)) return false;

        if($('.variant-table tr').length==2) {
          $('.variant-table .hide-content').hide();
          $('.variant-table').data('have-variant','0');
        } else {
          $(this).parents('tr').remove();
        }

        var imgFile = $(this).parents('tr').find('.img-this-variant img').attr('src');

        if(catalog.deleteImage) {
          catalog.deleteImage += '|'+imgFile;
        } else {
          catalog.deleteImage = imgFile;
        }

        return false;
        // admin.ajaxRequest({
        //   mguniqueurl:"action/deleteImageProduct",
        //   imgFile: imgFile,
        // },

        // function(response) {
        //   admin.indication(response.status, response.msg);
        // });
      });

       // при ховере на иконку картинки варианта  показывать  имеющееся изображение
       $('body').on('mouseover mouseout', '.product-table-wrapper .img-variant, .product-table-wrapper .del-img-variant',  function(event) {
        if (event.type == 'mouseover') {
          $(this).parents('td').find('.img-this-variant').show();
        } else {
          $(this).parents('td').find('.img-this-variant').hide();
        }
      });

      // При получении фокуса в поля для изменения значений, запоминаем каким было  исходное значение
      $('.admin-center').on('focus', '.section-catalog .fastsave', function() {
        catalog.memoryVal = $(this).val();
      });

      // сохранение параметров товара прямо из общей таблицы товаров при потере фокуса
      $('.admin-center').on('blur', '.section-catalog .fastsave', function() {
        //если введенное отличается от  исходного, то сохраняем.
        if(catalog.memoryVal!=$(this).val()) {
          catalog.fastSave($(this).data('packet'), $(this).val(),$(this));
        }
        catalog.memoryVal = null;
      });

      // сохранение параметров товара прямо из общей таблицы товаров при нажатии ентера
      $('.admin-center').on('keypress', '.section-catalog .fastsave', function(e) {
        if(e.keyCode==13) {
          $(this).blur();
        }
      });

      // показывает сроку поиска для связанных товаров
      $('body').on('click', '#add-product-wrapper .add-related-product', function() {
        $('.select-product-block').show();
      });

      // Удаляет связанный товар из списка связанных
      $('body').on('click', '#add-product-wrapper .add-related-product-block .remove-added-product', function() {
        $(this).parents('.product-unit').remove();
        catalog.widthRelatedUpdate();
        catalog.msgRelated();
      });
      // Удаляет связанную категорию товар из списка связанных
      $('body').on('click', '#add-product-wrapper .add-related-product-block .remove-added-category', function() {
        $(this).parents('.category-unit').remove();
        catalog.widthRelatedUpdate();
        catalog.msgRelated();
      });

      // Закрывает выпадающий блок выбора связанных товаров
      $('body').on('click', '#add-product-wrapper .add-related-product-block .cancel-add-related', function() {
        $('.select-product-block').hide();
      });

      // Поиск товара при создании связанного товара.
      // Обработка ввода поисковой фразы в поле поиска.
      $('body').on('keyup', '#add-product-wrapper .search-block input[name=searchcat]', function() {
        admin.searchProduct($(this).val(),'#add-product-wrapper .search-block .fastResult');
      });

      // подбор случайного товара
      $('body').on('click', '#add-product-wrapper .random-add-related', function() {
        admin.ajaxRequest({
          mguniqueurl:"action/getRandomProd"
        },
        function(response) {
          admin.indication(response.status, response.msg);
          if(response.status!='error') {
            catalog.addrelatedProduct(0, response.data.product);
          }
        },
        false,
        false,
        true
       );
      });

      // Подстановка товара из примера в строку поиска связанного товара.
      $('body').on('click', '#add-product-wrapper .search-block  .example-find', function() {
        $('.section-catalog .search-block input[name=searchcat]').val($(this).text());
        admin.searchProduct($(this).text(),'#add-product-wrapper .search-block .fastResult');
      });

     // Клик по найденым товарам поиска в форме добавления связанного товара.
      $('body').on('click', '#add-product-wrapper .fast-result-list a', function() {
        catalog.addrelatedProduct($(this).data('element-index'));
      });

      // Выполнение выбранной операции с товарами
      $('.admin-center').on('click', '.section-catalog .run-operation', function() {
        if ($('.product-operation').val() == 'fulldelete') {
          admin.openModal('#catalog-remove-modal');
        }
        else{
          catalog.runOperation($('.product-operation').val());
        }
      });
      //Проверка для массового удаления
      $('.admin-center').on('click', '#catalog-remove-modal .confirmDrop', function () {
        if ($('#catalog-remove-modal input').val() === $('#catalog-remove-modal input').attr('tpl')) {
          $('#catalog-remove-modal input').removeClass('error-input');
          admin.closeModal('#catalog-remove-modal');
          catalog.runOperation($('.product-operation').val(),true);
        }
        else{
          $('#catalog-remove-modal input').addClass('error-input');
        }
      });

      $('.admin-center').on('change', '.section-catalog select[name="operation"]', function() {
        if($(this).val() == 'move_to_category') {
          $('select#moveToCategorySelect').show(1);
        } else {
          $('select#moveToCategorySelect').hide(1);
        }
      });

      // Изменение типа каталога для импорта из CSV
      $('.admin-center').on('change', ".block-upload-сsv select[name=importType]", function() {
        $('.block-upload-сsv .example-csv').hide();
        $('input[name=upload]').val('');

        if ($(this).val() != 0) {
          $('input[name=upload]').removeAttr('disabled');
          $('.block-upload-сsv .view-'+$(this).val()).show();          
          $('select[name=importScheme]').attr('disabled', 'disabled');
          $('select[name=identifyType]').attr('disabled', 'disabled');
          $('.upload-csv-form').removeClass('disabled');
          $('input[name=no-merge]').attr('disabled', 'disabled');
          $('input[name=no-merge]').removeAttr("checked");
          $('input[name=no-merge]').val(false);
          $('.upload-btn').show();
          $('.repeat-upload-file').hide();
          $('.message-importing').text('');
        } else {
          $('input[name=upload]').attr('disabled', 'disabled');
          $('.upload-csv-form').addClass('disabled');
        }

        if($(this).val() === 'MogutaCMSUpdate') {
          $('.identifyType').hide();
          $(".delete-all-products-btn").hide();
        } else {
          $('.identifyType').show();
          $(".delete-all-products-btn").show();
        }
      });



     // Обработчик для загрузки изображения на сервер, сразу после выбора.
      $('body').on('change', '.catalog_uploads_container_variants_wrapper input[name="photoimg"]', function() {
        // отправка картинки на сервер
        var imgContainer = $(this).parents('td');

        $(this).parents('form').ajaxSubmit({
          type:"POST",
          url: "ajax",
          data: {
            mguniqueurl:"action/addImage"
          },
          cache: false,
          dataType: 'json',
          success: function(response) {
            admin.indication(response.status, response.msg);
            if(response.status != 'error') {
              var src=admin.SITE+'/uploads/'+response.data.img;
              imgContainer.find('img').attr('src',src).attr('filename', response.data.img);
              imgContainer.find('.del-img-variant').show();
              imgContainer.find('.img-button').hide();
              catalog.updateImageVar();
            } else {
              var src=admin.SITE+'/mg-admin/design/images/no-img.png';
              imgContainer.find('img').attr('src',src).attr('filename', 'no-img.png');
              catalog.updateImageVar();
            }
          }
        });
      });

      // Устанавливает количество выводимых записей в этом разделе.
      $('.admin-center').on('change', '.section-catalog .countPrintRowsProduct', function() {
        var count = $(this).val();
        admin.ajaxRequest({
          mguniqueurl: "action/setCountPrintRowsProduct",
          count: count
        },
        function(response) {
          admin.refreshPanel();
        }
        );

      });


      // Подобрать продукты по поиску
      $('.admin-center').on('click', '.section-catalog .searchProd', function() {
        var keyword =  $('input[name="search"]').val();
        catalog.getSearch(keyword);
      });


       //Добавить изображение для продукта
       $('body').on('click', '#add-product-wrapper .add-image', function() {
         var src=admin.SITE+'/mg-admin/design/images/no-img.png';
         var row = catalog.drawControlImage(src, true,'','','');
         $('.sub-images').append(row);
         admin.initToolTip();
       });

       // для главной картинки меняем классы сохраняем в буфер и удаляем

       //Сделать основной картинку продукта
       $('body').on('click', '.set-main-image', function() {
        var obj = $(this).parents('.parent');
        catalog.upMainImg(obj);
       });

       //Показать окно с настройками title и alt для картинки
       $('body').on('click', '#add-product-wrapper .seo-image', function(e) {
        var seoBlock = $(this).parents('.parent').find('.custom-popup:first');
        var main = false;
        if(seoBlock.is(':visible')==true) {
          seoBlock.hide();
        } else {
          seoBlock.show();
        }
        if(!main) {
          var obj = $(this).parents('.image-item'),
            objIndex = obj.index() + 1;

          if (objIndex % 2 == 0) {   //Если остаток деления на 2 равен 0, то четное
            seoBlock.css('margin-left','-100%');
          }
        }
       });

      //Спрятать  окно с настройками title и alt для картинки
      $('body').on('click', '#add-product-wrapper .apply-seo-image', function() {
        $(this).parents('.custom-popup').hide();
      });

      //Спрятать окно с настро title и alt для картинок, если параментры не были указаны
      $('body').on('click', '#add-product-wrapper .seo-image-block-close', function() {
        $(this).parents('.custom-popup').hide();
      });

       //Клик по кнопке Яндекс.Маркет
       $('body').on('click', '.get-yml-market', function() {
          admin.ajaxRequest({
             mguniqueurl:"action/existXmlwriter"
           },
           function(response) {
            admin.indication(response.status, response.msg);
            if(response.status!='error') {
              window.location=admin.SITE+'/mg-admin?yml=1';
            }
            admin.ajaxRequest({
              mguniqueurl:"action/createYmlLink",
            },
            function(response) {          
              admin.indication(response.status, response.msg);   
              if (response.status == 'success') {       
                admin.openModal($('.section-catalog .yml-link-was-formed'));            
                $('.section-catalog .yml-link-was-formed .yml-link').text(response.data);
                $('.section-catalog .yml-link-was-formed .yml-link').attr('href', response.data);
                $('.section-catalog .yml-link-was-formed .save-namelinkyml').addClass('save-button');
                $('.section-catalog .yml-link-was-formed').show();
              }
            })
           }, 
           $('.userField')
          );

       });
       $('body').on('click', '.section-catalog .yml-link-was-formed .edit-link', function() {
         $(this).parents('.product-table-wrapper').find('.link-name').show();
         $(this).parents('.product-table-wrapper').find('.link').hide();
       });
      // выводит путь родительских категорий при наведении мышкой
      $('.admin-center').on('mouseover', '.section-catalog tbody tr.product-row .cat_id', function() {
        if (!$(this).find('.parentCat').hasClass('categoryPath') && $(this).attr('id')!=0) {
          $(this).find('.parentCat').addClass('categoryPath');
          var cat_id = $(this).attr('id');
          var path = '';
          var parent = $('.section-catalog #add-product-wrapper select[name=cat_id] option[value='+cat_id+']').data('parent');
          if (parent) {
            while (parent != 0) {
              path = $('.section-catalog #add-product-wrapper select[name=cat_id] option[value='+parent+']').text()+ '/' + path ;
              parent = $('.section-catalog #add-product-wrapper select[name=cat_id] option[value='+parent+']').data('parent');
            }
            path = path.replace(/-/g,'');
            $(this).find('.parentCat').attr('title', '/'+path);
            $('#tiptip_holder').hide();
            admin.initToolTip();
          }
        }
      });
       // добавление класса на кнопку закрытия при изменении
      $('body').on('click', '#textarea-property-value .custom-textarea-value', function() {
        $('#textarea-property-value .proper-modal_close').addClass('edited');
      })
      // добавление "своего" артикула
      $('body').on('keyup', '.variant-table .default-code', function() {
        $(this).removeClass('default-code');
      });


      /*Инициализирует CKEditior и раскрывает поле для заполнения описания товара*/
      /*$('body').on('click', '.product-desc-wrapper .html-content-edit', function() {
        var link = $(this);
        $('textarea[name=html_content]').ckeditor(function() {
          $('#html-content-wrapper').show();
        });
      });*/

      /**
       * Дополнительный обработчик закрытия модального окна,
       * для удаления загруженных изображений.
       */
      // $('body').on('click', '.b-modal_close', function () {
      //   var imagesList = '';
      //   if($(this).attr('item-id')) {
      //     imagesList = catalog.tmpImage2Del;
      //     catalog.tmpImage2Del = '';
      //   } else {
      //     imagesList = catalog.createFieldImgUrl();

      //     $('.variant-table .variant-row').each(function() {
      //       var filename = $(this).find('img[filename]').attr('filename');

      //       if(!filename) {
      //         filename = $(this).find('img').data('filename');
      //       }

      //       if(filename) {
      //         imagesList += '|'+filename;
      //       }
      //     });

      //     imagesList += '|'+catalog.deleteImage;
      //     catalog.deleteImage = '';
      //   }

      //   admin.ajaxRequest({
      //     mguniqueurl:"action/deleteTmpImages",
      //     images: imagesList
      //   });
      //   // удаляем добавленные характеристики, если товар не был сохранен
      //   catalog.closeAddedProperty('close');
      // });


      // Удалить фотографию варианта товара
      $('body').on('click', '#add-product-wrapper .del-img-variant', function() {
        if (confirm(lang.DELETE_IMAGE+'?')) {
          var src = admin.SITE+'/mg-admin/design/images/no-img.png';
          var currentImg = $(this).parents('tr').find('.img-this-variant img').data('filename');
          if(!currentImg) {currentImg = $(this).parents('tr').find('.img-this-variant img').attr('filename');}
          if(!currentImg) {currentImg = $(this).parents('tr').find('.img-this-variant img').attr('src');}
          $(this).parents('tr').find('.img-this-variant img').attr('src',src).data('filename', '').attr('filename','');
          $(this).hide();
          $(this).parents('tr').find('.img-button').show();
            //Пишем в поле deleteImage имена изображений, которые необходимо будет удалить при сохранении
          if(catalog.deleteImage) {
            catalog.deleteImage += '|'+currentImg;
          } else {
            catalog.deleteImage = currentImg;
          }
        }
        catalog.updateImageVar();
        return false;
      });

      // переключение табов в попапе для добаления рекомендованного товарар или категории
      $('body').on('click', '#add-related-product-tabs .tabs-title', function() {
        $('#add-related-product-tabs .tabs-title').removeClass('is-active');
        $(this).addClass('is-active');

        $('#add-related-product-tabs-content .tabs-panel').removeClass('is-active');
        $('#add-related-product-tabs-content #'+$(this).data('target')).addClass('is-active');
      });

      $('body').on('click', '.section-catalog a.get-csv', function() {
        catalog.exportToCsv();

        return false;
      });

      $('.admin-center').on('change', '.section-catalog select[name=importScheme]', function() {
        switch($(this).val()) {
          case 'last':
            catalog.showSchemeSettings('last');
            break;
          case 'new':
            catalog.showSchemeSettings('auto');
            break;
          default:
            return false;
        }
      });

      // Сохраняет изменения в модальном окне
      $('.admin-center').on('click', '.section-catalog .columnComplianceModal .save-button', function() {
        var data = {};
        data['compliance'] = {};
        $('.section-catalog .columnComplianceModal select').each(function() {
          data['compliance'][$(this).attr('name')] = admin.htmlspecialchars($(this).val());
        });

        data['not_update'] = {};
        $('.section-catalog .columnComplianceModal input[type="checkbox"]').each(function() {
          if($(this).prop('checked')) {
            data['not_update'][$(this).attr('name')] = '1';
          }
        });

        admin.ajaxRequest({
          mguniqueurl: "action/setCsvCompliance", // действия для выполнения на сервере
          data: data,
          importType: $('.columnComplianceModal button.save-button').attr('importType')
        },
        function(response) {
          // admin.indication(response.status, response.msg);
          $('.start-import').click();
        });
      });

      $('.admin-center').on('click', '.section-catalog .columnComplianceModal .b-modal_close', function() {
        admin.closeModal($('.columnComplianceModal'));
      });

      //Пропустить шаг импорта товаров и перейти к загрузке изображений
      $('.admin-center').on('click', '.csv_skip_step', function() {
        $('.block-upload-сsv').hide();
        // $('.import-container h3.title').text(lang.BLOCK_UPLOAD_IMAGES_TITLE);
        $('.block-upload-images').show();
        $('.csv-import-title').hide();
        $('.img-import-title').show();
      });

      // Выбор ZIP архива на сервере
      $('.admin-center').on('click', '.section-catalog .block-upload-images .browseImage', function() {
        admin.openUploader('catalog.getFile');
         //catalog.printLog('Файлы архива распаковываются в tempimg/ !');
      });

      // Обработчик для загрузки архива с изображениями
      $('.admin-center').on('change', '.section-catalog .block-upload-images input[name="uploadImages"]', function() {
        catalog.uploadImagesArchive();
      });

      $('.admin-center').on('click', '.section-catalog .block-upload-images .startGenerationProcess', function() {
        $(this).hide();
        $('.message-importing').html(lang.CATALOG_LOCALE_6 + 0
                + '%<div class="progress-bar"><div class="progress-bar-inner" style="width:' + 0
                + '%;"></div></div>');
        $('.message-importing').show();
        catalog.startGenerationImageFunc();
      });

      $('.admin-center').on('click', 'a.gotoImageUpload' , function() {
        $('.message-importing').hide();
        $('.import-container h3.title').text(lang.BLOCK_UPLOAD_IMAGES_TITLE);
        $('.block-upload-images').show();
        return false;
      });
      $('body').on('click', '#overlay', function() {
        if ($('.section-catalog .yml-link-was-formed').is(":visible")) {
          $('.section-catalog .yml-link-was-formed .save-namelinkyml').removeClass('save-button');
          admin.closeModal($('.section-catalog .yml-link-was-formed'));
        }
      });
      // сохранение ссылки для yml - название файла надо переименовать после изменений.
      $('body').on('click', ".section-catalog .yml-link-was-formed .save-namelinkyml", function() {
        var name = $(this).parents('.yml-link-was-formed').find('input[name=getyml]').val();
        admin.ajaxRequest({
          mguniqueurl:"action/renameYmlLink",
          name: name
        },
        function(response) {          
          admin.indication(response.status, response.msg);   
          if (response.status == 'success') {       
            $('.section-catalog .yml-link-was-formed .yml-link').attr('href', admin.SITE+'/'+name);
            $('.section-catalog .yml-link-was-formed .yml-link').text(admin.SITE+'/'+name);
            $('.section-catalog .yml-link-was-formed .link').show();
            $('.section-catalog .yml-link-was-formed .link-name').hide();
          }
        })
      });
      // выбор рекомендуемых товаров или категорий
      $('body').on('click', '#add-product-wrapper .related-type li', function() {
        if ($(this).hasClass('ui-state-active') ) {
          return false;
        } 
        var type = $(this).data('type');
        $(this).parent().find('.ui-state-active').removeClass('ui-state-active');
        $(this).addClass('ui-state-active');
        $('#add-product-wrapper .search-block').hide();
        $('#add-product-wrapper .search-block.'+type).show();
      });
      // добавление выбранных категорий в список рекомендуемых товаров save-add-related
      $('body').on('click', '#add-product-wrapper .save-add-related', function() {
        var related = $('#add-product-wrapper select[name=related_cat]').val();
        if(related != null) {
          if (related.length > 0) {
          admin.ajaxRequest({
              mguniqueurl:"action/getRelatedCategory",
              cat:related
            },
            function(response) {
              if(response.status!='error') {
                catalog.addrelatedCategory(response.data);
              }
            })
          }
        }
      });


      $('body').on('change', '.columnComplianceModal .multiColumnParamInput', function() {
        if($(this).prop('checked')) {
          $('.multiColumnParam').show();
        } else {
          $('.multiColumnParam').hide();
        }
      });


      $('body').on('change', '.columnComplianceModal .widget-table-body .complianceHeaders tbody tr select', function() {
        if($(this).val() == 'none') {
          $(this).addClass('none-selected');
        } else {
          $(this).removeClass('none-selected');
        }
      });

      $('body').on('click', '.columnComplianceModal .setFullModCsv', function() {
        $(this).hide();
        $('.columnComplianceModal .setUpdateModCsv').show();
        $('.columnComplianceModal .delete-all-products-btn').show();

        $('.columnComplianceModal .widget-table-body .complianceHeaders tbody tr').each(function() {
          $(this).show();
        });
      });

      $('body').on('click', '.columnComplianceModal .setUpdateModCsv', function() {
        $(this).hide();
        $('.columnComplianceModal .setFullModCsv').show();
        $('.columnComplianceModal .delete-all-products-btn').hide();
        $('.columnComplianceModal .delete-all-products-btn input').prop('checked', false);

        requiredFields = ['Артикул','Цена','Старая цена','Количество','Оптовые цены','Склады', 'Валюта'];
        $('.columnComplianceModal .widget-table-body .complianceHeaders tbody tr').each(function() {
          if($.inArray($(this).find('td:eq(0) b').text(), requiredFields) === -1) {
            $(this).hide().find('select').val('none').addClass('none-selected');
            
          };
        });
        $('.columnComplianceModal .widget-table-body .complianceHeaders tbody tr').each(function() {
          if($(this).find('td:eq(0) b').text() == 'Оптовые цены') {
            $(this).find('select option').each(function() {
              if($(this).text().indexOf('[оптовая цена]') > -1) {
                $(this).parent().val($(this).val()).removeClass('none-selected');
                return false;
              }
            });
          }
          if($(this).find('td:eq(0) b').text() == 'Склады') {
            $(this).find('select option').each(function() {
              if($(this).text().indexOf('[склад') > -1) {
                $(this).parent().val($(this).val()).removeClass('none-selected');
                return false;
              }
            });
          }
        });
      });

      $('body').on('click', '.showGroupVar', function() {
        var type = $(this).parents('tr').data('type');
        var id = $(this).parents('tr').data('id');
        $(this).parents('tr').toggleClass('show');
        if($(this).parents('tr').hasClass('show')) {
          $('.variant-table tr').each(function() {
            if($(this).find('[name='+type+']').val() == id) $(this).show();
            if($(this).data('id') == id) {
              $(this).find('.tmplApply').show();
              $(this).find('.tmpl-code').hide();
              $(this).find('.tmplPreview').hide();
              $(this).find('.tmplEdit').show();
              $(this).find('.tmplApply').parent('td').attr('colspan', '2');
            }
          });
        } else {
          $('.variant-table tr').each(function() {
            if($(this).find('[name='+type+']').val() == id) $(this).hide();
            if($(this).data('id') == id) {
              $(this).find('.tmplApply').hide();
              $(this).find('.tmpl-code').show();
              $(this).find('.tmplPreview').show();
              $(this).find('.tmplEdit').hide();
              $(this).find('.tmplApply').parent('td').attr('colspan', '1');
            }
          });
        }
      });

      $('body').on('click', '.tmplApply', function() {
        var type = $(this).parents('tr').data('type');
        var id = $(this).parents('tr').data('id');
        var obj = $(this).parents('tr');
        var src = $(this).parents('tr').find('.action-list').parent().html();
        $('.variant-table tr').each(function() {
          if($(this).find('[name='+type+']').val() == id) {
            $(this).find('[name=price]').val(obj.find('.tmpl-price').val());
            $(this).find('[name=old_price]').val(obj.find('.tmpl-old_price').val());
            $(this).find('[name=weight]').val(obj.find('.tmpl-weight').val());
            if(!$(this).find('[name=count]').prop('disabled')) $(this).find('[name=count]').val(obj.find('.tmpl-count').val());

            $(this).find('.action-list').parent().html(src);
          }
        });
        $('.variant-table .variant-row .deleteGroupVar').addClass('del-variant').removeClass('deleteGroupVar');
      });

      $('body').on('keyup', '.group-row input', function() {
        if($(this).parents('tr').hasClass('show')) return false;
        var type = $(this).parents('tr').data('type');
        var id = $(this).parents('tr').data('id');
        var obj = $(this).parents('tr');
        $('.variant-table tr').each(function() {
          if($(this).find('[name='+type+']').val() == id) {
            $(this).find('[name=code]').val(obj.find('.tmpl-code').val());
            $(this).find('[name=price]').val(obj.find('.tmpl-price').val());
            $(this).find('[name=old_price]').val(obj.find('.tmpl-old_price').val());
            $(this).find('[name=weight]').val(obj.find('.tmpl-weight').val());
            if(!$(this).find('[name=count]').prop('disabled')) $(this).find('[name=count]').val(obj.find('.tmpl-count').val());
            return false;
          }
        });
      }); 
      $('body').on('change', '.group-row input', function() {
        $(this).parents('tr').find('.tmplApply').addClass('tmplChange');
      });

      $('body').on('click', '.group-row .deleteGroupVar', function() {
        if(!confirm(lang.DELETE_GROUP_VAR)) return false; 
        var type = $(this).parents('tr').data('type');
        var id = $(this).parents('tr').data('id');
        var obj = $(this).parents('tr');
        $('.variant-table tr').each(function() {
          if($(this).find('[name='+type+']').val() == id) $(this).detach();
        });
        $(this).parents('tr').detach();
      });

      $('body').on('click', '.back-category', function() {
        $('#add-product-wrapper [name=cat_id]').val(catalog.saveCategory).change();
      });

      $('body').on('click', '.drop-restart-variant', function() {
        userProperty.sizeMapCreatedProcess = true;
        $('.variant-row:not(:eq(0)) .del-variant').click();
        userProperty.sizeMapCreatedProcess = false;
        $('.variant-row:eq(0) [name=color], .variant-row:eq(0) [name=size]').val('');
        catalog.saveVarTable = $('.variant-table .variant-row, .variant-table .text-left').clone();
        $('#add-product-wrapper [name=cat_id]').change();
        catalog.saveCategory = $('#add-product-wrapper [name=cat_id]').val();
      });

     },

     updateImageVar: function() {
      $('.variant-table .group-row').each(function() {
        if($(this).hasClass('show')) return true;
        var type = $(this).data('type');
        var id = $(this).data('id');
        var src = $(this).find('.action-list').parent().html();
        $('.variant-table tr').each(function() {
          if($(this).find('[name='+type+']').val() == id) {
            $(this).find('.action-list').parent().html(src);
          }
        });
        $('.variant-table .variant-row .deleteGroupVar').addClass('del-variant').removeClass('deleteGroupVar');
      });
     },
     /**
      * Генерируем мета описание
      */
     generateMetaDesc: function(description) {
        if (!description) {return '';}
        return '';
     },
     /**
      * Генерируем ключевые слова для товара
      * @param string title
      */
     generateKeywords: function(title) {
     },
     /**
      * Запускаем генерацию метатегов по шаблонам из настроек
      */
     generateSeoFromTmpl: function(who) {
     },

    startGenerationImageFunc: function(nextItem, total_count, imgCount) {
      nextItem = typeof nextItem !== 'undefined' ? nextItem : 0;
      admin.ajaxRequest({
        mguniqueurl:"action/startGenerationImagePreview",
        nextItem: nextItem,
        total_count: total_count,
        imgCount: imgCount
      },
      function(response) {
        admin.indication(response.status, response.msg);

        if(response.data.percent<100) {
          $('.message-importing').html(lang.CATALOG_LOCALE_6
                  + response.data.percent
                  + '%<div class="progress-bar"><div class="progress-bar-inner" style="width:'
                  + response.data.percent + '%;"></div></div>');
          catalog.startGenerationImageFunc(response.data.nextItem, response.data.total_count, response.data.imgCount);
        } else {
          $('.message-importing').html(lang.CATALOG_LOCALE_6
                  + response.data.percent
                  + '%<div class="progress-bar"><div class="progress-bar-inner" style="width:'
                  + '100%;"></div></div>');
          if(catalog.startGenerationImage) {
            $('.loger').append(lang.CATALOG_LOCALE_10 +' \
               <a class="refresh-page custom-btn" href="'+mgBaseDir+'/mg-admin/">\n\
                 <span>'+lang.CATALOG_REFRESH+'</span>\
              </a>\n\
              <br><a href="'+admin.SITE+'/import_csv_log.txt" target="blank">'+lang.CATALOG_VIEW_LOG+'</a>');
          }
//          admin.refreshPanel();
        }
        $('.log').text($('.log').text()+response.data.log);
        $('.log').text($('.log').text()+response.msg);
        $('.loger').show();
      });
    },
    /**
     * Загружает Архив с изображениями на сервер для последующего импорта
     */
    uploadImagesArchive: function() {
      $('.section-comerceml input[name="upload"]').hide();
      // $('.mailLoader').before('<div class="view-action" style="margin-top:-2px;">' + lang.LOADING + '</div>');
      // отправка архива с изображениями на сервер
      // comerceMlModule.printLog('Идет передача файла на сервер. Подождите, пожалуйста...');    
      $('.upload-goods-image-form').ajaxForm({
        type: "POST",
        url: "ajax",
        cache: false,
        dataType: 'json',
        data: {
          mguniqueurl: "action/uploadImagesArchive",
        },
        error: function(q,w,r) {
          console.log(q);
          console.log(w);
          console.log(r);
         // comerceMlModule.printLog("Ошибка: Загружаемый вами файл превысил максимальный объем и не может быть передан на сервер из-за ограничения в настройках файла php.ini");
          admin.indication('error',lang.CATALOG_LOCALE_12);
          $('.section-comerceml input[name="upload"]').show();
          $('.view-action').remove();
        },
        success: function(response) {
          if(response.msg) admin.indication(response.status, response.msg);
          if (response.status == 'success') {
            $('.upload-images').hide();
            $('.start-generate').show();
          } else {
            $('.import-container input[name="upload"]').val('');
          }
          $('.view-action').remove();
        },
      }).submit();
    },
    /**
    * функция для приема файла из аплоадера
    */
    getFile: function(file) {
      $('.section-comerceml .b-modal input[name="src"]').val(file.url);
      $.ajax({
        type: "POST",
        url: "ajax",
        data: {
          mguniqueurl: "action/selectImagesArchive",
          data: {
          filename: file.url,
          }
        },
        dataType: 'json',
        success: function(response) {
          admin.indication(response.status, response.msg);
          if (response.status == 'success') {
            $('.upload-images').hide();
            $('.start-generate').show();
          }
        }
      });
    },
    /*
     * Открывает модальное окно для установки соответствия полей импорта
     * @param string scheme
     * @returns void
     */
    showSchemeSettings: function(scheme) {
      $('.columnComplianceModal .widget-table-body ul').empty();
      var importType = $('.section-catalog select[name="importType"]').val();
      admin.ajaxRequest({
        mguniqueurl: "action/getCsvCompliance", // действия для выполнения на сервере
        scheme: scheme,
        importType: importType
      },
      catalog.fillCsvCopliance(importType));
      $('.columnComplianceModal button.save-button').attr('importType', importType);

      // 
      setTimeout(function() {
        $('.columnComplianceModal .widget-table-body .complianceHeaders tbody tr').each(function() {
          if($(this).find('td:eq(0) b').text() == 'ID товара') {
            $(this).find('select option').each(function() {
              if($(this).text().indexOf('ID товара') > -1) {
                $(this).parent().val($(this).val()).removeClass('none-selected');
                return false;
              }
            });
          }
          if($(this).find('td:eq(0) b').text() == 'Оптовые цены') {
            $(this).find('select option').each(function() {
              if($(this).text().indexOf('[оптовая цена]') > -1) {
                $(this).parent().val($(this).val()).removeClass('none-selected');
                return false;
              }
            });
          }
          if($(this).find('td:eq(0) b').text() == 'Склады') {
            $(this).find('select option').each(function() {
              if($(this).text().indexOf('[склад') > -1) {
                $(this).parent().val($(this).val()).removeClass('none-selected');
                return false;
              }
            });
          }
        });
      }, 500);
      // 

      admin.openModal($('.columnComplianceModal'));
    },
    /*
     * Заполнение модального окна выбора соответствия полей данными
     * @returns {Function}
     */
    fillCsvCopliance: function(importType) {
      return function(response) {
        var titleList = '';
        var compList = '';
        var fieldContinue = -1;

        if(importType === 'MogutaCMS') {
          fieldContinue = 2;

          if($(".block-upload-сsv select[name=identifyType]").val() == 'article') {
            fieldContinue = 8;
          }
        }

        $('.columnComplianceModal .widget-table-body .complianceHeaders tbody').html('');

        response.data.titleList.forEach(function(item, i, arr) {
          titleList += '<option value="'+i+'">'+item+'</option>';
        });

        var typeWork = 'array';
        var count = response.data.maskArray.length;
        var keys = undefined;
        if(count == undefined) {
          count = Object.keys(response.data.maskArray).length;
          typeWork = 'object';
          keys = [];
          for (var key in response.data.maskArray) {
            keys.push(key);
          }
        }

        for(i = 0; i < count; i++) {

        // response.data.maskArray.forEach(function(item, i, arr) {
          var notUpdate = '';
          var disabled = '';

          if(i == fieldContinue) {
            disabled = 'disabled="disabled"';
          } else if(response.data.notUpdate[i] == 1) {
            notUpdate = 'checked="checked"';
          }

          if($.inArray(i, response.data.requiredFields) !== -1) {
            required = 'required';
          } else {
            required = '';
          }

          if(typeWork == 'array') {
            rowName = response.data.maskArray[i];
            dataFields = response.data.fieldsInfo[i];
          } else {
            rowName = response.data.maskArray[keys[i]];
            dataFields = response.data.fieldsInfo[keys[i]];
          }

          if(typeWork == 'array') {
            index = i;
          } else {
            index = keys[i];
          }

          compList = '\
            <tr>\
              <td>\
                <b>'+rowName+'</b>\
                <i class="fa fa-question-circle tip fl-right" style="cursor:pointer;" title="'+dataFields+'"></i>\
              </td>\
              <td style="padding-right:0;"><select name="colIndex'+index+'" style="margin:0;width:calc(100% + 1px);" '+required+'>\
                <option value="none">'+lang.NO_SELECT+'</option>\
                '+titleList+'\
              </select></td>\
            </tr>';
          $('.columnComplianceModal .widget-table-body .complianceHeaders tbody').append(compList);
          $('.columnComplianceModal .widget-table-body .complianceHeaders tbody select[name=colIndex'+i+'] option[value='+response.data.compliance[i]+']').attr('selected', 'selected');
        }

        $('.columnComplianceModal .widget-table-body .complianceHeaders tbody tr').each(function() {
          if($(this).find('select').val() == 'none') {
            $(this).find('select').addClass('none-selected');
          }
        });

        $('.csvPreview').html(response.data.csvPreview);
      }
    },

    exportToCsv: function(page, rowCount) {
      if(!page) {
        page = 1;
      }
      if(!rowCount) {
        rowCount = 0;
      }
      loader = $('.mailLoader');
      $.ajax({
        type: "POST",
        url: mgBaseDir + "/mgadmin",
        data: {
          csv: 1,
          page: page,
          rowCount: rowCount
        },
        dataType: "json",
        cache: false,
        beforeSend: function() {
          // флаг, говорит о том что начался процесс загрузки с сервера
          admin.WAIT_PROCESS = true;
          loader.hide();
          loader.before('<div class="view-action" style="display:none; margin-top:-2px;">' + lang.LOADING + '</div>');
          // через 300 msec отобразится лоадер.
          // Задержка нужна для того чтобы не мерцать лоадером на быстрых серверах.

          setTimeout(function () {
            if (admin.WAIT_PROCESS) {
              admin.waiting(true);
            }
          }, admin.WAIT_DELAY);
        },
        success: function(response) {
          admin.WAIT_PROCESS = false;
          admin.waiting(false);
          loader.show();
          $('.view-action').remove();

          if(!response.success) {
            admin.indication('success', lang.INDICATION_INFO_EXPORTED+' '+response.percent+'%');
            setTimeout(function() {
              catalog.exportToCsv(response.nextPage, response.rowCount);
            }, 2000);
          } else {
            admin.indication('success', lang.INDICATION_INFO_EXPORTED+' 100%');
            setTimeout(function() {
              if (confirm(lang.CATALOG_MESSAGE_1+response.file+lang.CATALOG_MESSAGE_2)) {
                location.href = mgBaseDir+'/'+response.file;
              }
            }, 100);
//            $('body').append('<iframe src="'+mgBaseDir+'/'+response.file+'" style="display: none;"></iframe>');
          }
        }
      });
    },

    /**
     * Открывает модальное окно.
     * type - тип окна, либо для создания нового товара, либо для редактирования старого.
     */
    openModalWindow: function(type, id) {

      try{
        if(CKEDITOR.instances['html_content']) {
          CKEDITOR.instances['html_content'].destroy();
        }
        if(CKEDITOR.instances['html_content-textarea']) {
          CKEDITOR.instances['html_content-textarea'].destroy();
        }
      } catch(e) { }

      switch (type) {
        case 'edit':{

          catalog.clearFields();
          $('.html-content-edit').show();
          $('.product-desc-wrapper #html-content-wrapper').hide();
          $('.add-product-table-icon').text(lang.CATALOG_PRODUCT_EDIT);
          catalog.editProduct(id);

          break;
        }
        case 'add':{
          $('.add-product-table-icon').text(lang.CATALOG_PRODUCT_ADD);
          catalog.clearFields();
          //$('textarea[name=html_content]').ckeditor();

          $('.related-block').html('');
          $('.related-block').hide();


          catalog.msgRelated();
          var src=admin.SITE+'/mg-admin/design/images/no-img.png';
          var row = catalog.drawControlImage(src, false,'','','');
          $('.main-image').html(row);
          // $('.main-img-prod .main-image').hide();

          var catId = $('.filter-container select[name=cat_id]').val();
          if(catId == 'null') {
            catId = 0;
          }
          // получаем набор общих характеристик и выводим их
          catalog.generateUserProreprty(0, catId);


          $('.add-position').show();

          $('.variant-table tr').each(function() {
            if($(this).find('[name=count]').val() == '') $(this).find('[name=count]').val('∞');
          });

          break;
        }
        default:{
          catalog.clearFields();
          break;
        }
      }

      if($('#productCategorySelect').val() == 0) {
        $('.add-property-field').hide();
      } else {
        $('.add-property-field').show();
      }

      // Вызов модального окна.
      admin.openModal('.product-desc-wrapper');

    },

    /**
     *  Изменяет список пользовательских свойств для выбранной категории в редактировании товара
     */
     generateUserProreprty: function(produtcId,categoryId) {

       admin.ajaxRequest({
          mguniqueurl:"action/getProdDataWithCat",
          produtcId: produtcId,
          categoryId: categoryId
        },
        function(response) {

          // admin.initToolTip();
        },
        $('.userField')
       );

     },

    /**
     *  Проверка заполненности полей, для каждого поля прописывается свое правило.
     */
    checkRulesForm: function() {
      $('.errorField').css('display','none');
      $('.product-text-inputs input').removeClass('error-input');
      var error = false;

      // наименование не должно иметь специальных символов.
      if(!$('.product-text-inputs input[name=title]').val()) {
        $('.product-text-inputs input[name=title]').parent("label").find('.errorField').css('display','block');
        $('.product-text-inputs input[name=title]').addClass('error-input');
        error = true;
      }

      // наименование не должно иметь специальных символов.
      if(!admin.regTest(2, $('.product-text-inputs input[name=url]').val()) || !$('.product-text-inputs input[name=url]').val()) {
        $('.product-text-inputs input[name=url]').parent("label").find('.errorField').css('display','block');
        $('.product-text-inputs input[name=url]').addClass('error-input');
        error = true;
      }

      // артикул обязательно надо заполнить.
      if(!$('.product-text-inputs input[name=code]').val()) {
        $('.product-text-inputs input[name=code]').parent("label").find('.errorField').css('display','block');
        $('.product-text-inputs input[name=code]').addClass('error-input');
        error = true;
      }

      // Проверка поля для стоимости, является ли текст в него введенный числом.
      if(isNaN(parseFloat($('.product-text-inputs input[name=price]').val()))) {
        $('.product-text-inputs input[name=price]').parent("label").find('.errorField').css('display','block');
        $('.product-text-inputs input[name=price]').addClass('error-input');
        error = true;
      }
      
      var url = $('.product-text-inputs input[name=url]').val();
      var reg = new RegExp('([^/-a-z\.\d])','i');
      
      if (reg.test(url)) {
        $('.product-text-inputs input[name=url]').parent("label").find('.errorField').css('display','block');
        $('.product-text-inputs input[name=url]').addClass('error-input');
        $('.product-text-inputs input[name=url]').val('');
        error = true;
      }

      // Проверка поля для старой стоимости, является ли текст в него введенный числом.
      $('.product-text-inputs input[name=old_price]').each(function() {
        var val = $(this).val();
        if(isNaN(parseFloat(val))&&val!="") {
          $(this).parent("label").find('.errorField').css('display','block');
          $(this).addClass('error-input');
          error = true;
        }
      });

      // Проверка поля количество, является ли текст в него введенный числом.
      $('.product-text-inputs input[name=count]').each(function() {
        var val = $(this).val();
        if(val=='\u221E'||val==''||parseFloat(val)<0) {val = "-1"; $(this).val('∞'); }
        if(isNaN(parseFloat(val))) {
          $(this).parent("label").find('.errorField').css('display','block');
          $(this).addClass('error-input');
          error = true;
        }
      });
      if(error == true) {
        var $container = $("#add-product-wrapper").parent();
        var $scrollTo = $('.error-input:first');

        $container.animate({scrollTop: $scrollTo.offset().top - $container.offset().top + $container.scrollTop()-25, scrollLeft: 0},300);
        return false;
      }

      return true;
    },


    /**
     * Сохранение изменений в модальном окне продукта.
     * Используется и для сохранения редактированных данных и для сохранения нового продукта.
     * id - идентификатор продукта, может отсутствовать если производится добавление нового товара.
     */
    saveProduct: function(id, closeModal) {
      closeModal = typeof closeModal !== 'undefined' ? closeModal : true;
      // Если поля неверно заполнены, то не отправляем запрос на сервер.
      if(!catalog.checkRulesForm()) {
        return false;
      }

      var recommend = $('.save-button').data('recommend');
      var activity =  $('.save-button').data('activity');
      var newprod =  $('.save-button').data('new');
      //определяем имеются ли варианты товара
      if(!id) $('.variant-row').data('id', '');
      var variants = catalog.getVariant();

      if(catalog.errorVariantField) {
        admin.indication('error', lang.ERROR_VARIANT);
        return false;
      }

      if(closeModal) {
        if($('textarea[name=html_content]').val()=='') {
          if(!confirm(lang.ACCEPT_EMPTY_DESC+'?')) {
            return false;
          }
        }
      }
      
      if ($('.addedProperty .new-added-prop').length > 0) {
        catalog.saveAddedProperties();
      }

      $('.userField .userfd').each(function() {
        if($(this).find('.price-body').html() != undefined) {
          var check = false;
          $('.userField .userfd .price-body .price-footer .setup-type').each(function() {
            if($(this).hasClass('selected')) {
              check = true;
            }
          });
          if(!check) {
            $(this).find('.price-body .price-footer .setup-type:eq(0)').click();
          }
        }
      });
      
      if (catalog.deleteImage != undefined && catalog.deleteImage != null && catalog.deleteImage != '') {
        var imgs = catalog.deleteImage.split('|');
        catalog.deleteImage = '';

        imgs.forEach(function (element, index, array) {
           var eleme = element.replace('thumbs/','');
           var splitIndex = eleme.lastIndexOf('/')+1;
           var fpart = eleme.slice(0, splitIndex);
           var spart = eleme.slice(splitIndex);
           var clearSpart = spart.replace('30_','');
           clearSpart = clearSpart.replace('70_','');
           var fullSrc = fpart+clearSpart;
           var miniSrc70 = fpart+'thumbs/70_'+clearSpart;
           var miniSrc30 = fpart+'thumbs/30_'+clearSpart;
            if(
              $('#add-product-wrapper .reveal-body>.collapse img[src="'+fullSrc+'"]').length ||
              $('#add-product-wrapper .reveal-body>.collapse img[src="'+miniSrc70+'"]').length ||
              $('#add-product-wrapper .reveal-body>.collapse img[src="'+miniSrc30+'"]').length ||
              spart.indexOf('no-img.') >= 0
              ) {}
            else{
              if(catalog.deleteImage) {
                catalog.deleteImage += '|'+fullSrc;
              } else {
                catalog.deleteImage = fullSrc;
              }
            }
        });
      }
      var imgs = '';

      
      if(!variants) {

        // Пакет характеристик товара.
        var packedProperty = {
          id: id,
          title: $('#add-product-wrapper .product-text-inputs input[name=title]').val(),
          link_electro: $('#add-product-wrapper .product-text-inputs input[name=link_electro]').val(),
          url: $('#add-product-wrapper .product-text-inputs input[name=url]').val(),
          code: $('#add-product-wrapper .product-text-inputs input[name=code]').val(),
          price: $('#add-product-wrapper .product-text-inputs input[name=price]').val(),
          old_price: $('#add-product-wrapper .product-text-inputs input[name=old_price]').val(),
          image_url: catalog.createFieldImgUrl(),
          image_title: catalog.createFieldImgTitle(),
          image_alt: catalog.createFieldImgAlt(),
          delete_image: catalog.deleteImage,
          count: $('#add-product-wrapper .product-text-inputs input[name=count]').val(),
          weight: $('#add-product-wrapper .product-text-inputs input[name=weight]').val(),
          cat_id: $('#add-product-wrapper .product-text-inputs select[name=cat_id]').val(),
          inside_cat: catalog.createInsideCat(),
          description: $('textarea[name=html_content]').val(),
          short_description: $('textarea[name=short_html_content]').val(),
          meta_title: $('#add-product-wrapper input[name=meta_title]').val(),
          meta_keywords: $('#add-product-wrapper input[name=meta_keywords]').val(),
          meta_desc: $('#add-product-wrapper textarea[name=meta_desc]').val(),
          currency_iso: $('#add-product-wrapper select[name=currency_iso]').val(),
          recommend: recommend,
          activity: activity,
          unit: $('#add-product-wrapper .btn-selected-unit').attr('realunit'),
          new:newprod,
          variants:null,
          yml_sales_notes: $('.yml-wrapper input[name=yml_sales_notes]').val(),
          related_cat: catalog.getRelatedCategory(),

        }
      } else {

        var packedProperty = {
          id: id,
          title: $('#add-product-wrapper .product-text-inputs input[name=title]').val(),
          link_electro: $('#add-product-wrapper .product-text-inputs input[name=link_electro]').val(),
          code: $('#add-product-wrapper .variant-table tr').eq(1).find('input[name=code]').val(),
          price: $('#add-product-wrapper .variant-table tr').eq(1).find('input[name=price]').val(),
          old_price: $('#add-product-wrapper .variant-table tr').eq(1).find('input[name=old_price]').val(),
          count: $('#add-product-wrapper .variant-table tr').eq(1).find('input[name=count]').val(),
          weight: $('#add-product-wrapper .variant-table tr').eq(1).find('input[name=weight]').val(),
          url: $('#add-product-wrapper .product-text-inputs input[name=url]').val(),
          image_url: catalog.createFieldImgUrl(),
          image_title: catalog.createFieldImgTitle(),
          image_alt: catalog.createFieldImgAlt(),
          delete_image: catalog.deleteImage,
          cat_id: $('#add-product-wrapper .product-text-inputs select[name=cat_id]').val(),
          inside_cat: catalog.createInsideCat(),
          description: $('#add-product-wrapper textarea[name=html_content]').val(),
          short_description: $('#add-product-wrapper textarea[name=short_html_content]').val(),
          meta_title: $('#add-product-wrapper input[name=meta_title]').val(),
          meta_keywords: $('#add-product-wrapper input[name=meta_keywords]').val(),
          meta_desc: $('#add-product-wrapper textarea[name=meta_desc]').val(),
          currency_iso: $('#add-product-wrapper select[name=currency_iso]').val(),
          recommend: recommend,
          activity: activity,
          unit: $('#add-product-wrapper .btn-selected-unit').attr('realunit'),
          new:newprod,
          variants:variants,
          yml_sales_notes: $('.yml-wrapper input[name=yml_sales_notes]').val(),
          related_cat: catalog.getRelatedCategory(),

        }

      }

      catalog.deleteImage = '';
      
      // отправка данных на сервер для сохранения
      admin.ajaxRequest({mguniqueurl:"action/saveProduct", data:JSON.stringify(packedProperty)},
        function(response) {
          admin.clearGetParam();
          if(closeModal) {
            admin.indication(response.status, response.msg);
          }
          if(response.status == 'error') return false;
          var row = catalog.drawRowProduct(response.data);

          // Вычисляем, по наличию характеристики 'id',
          // какая операция производится с продуктом, добавление или изменение.
          // Если id есть значит надо обновить запись в таблице.
          if(packedProperty.id) {
            $('.product-tbody tr[id='+packedProperty.id+']').replaceWith(row);
          } else {
            // Если id небыло значит добавляем новую строку в начало таблицы.
            if($('.product-tbody tr:first').length>0) {
              $('.product-tbody tr:first').before(row);
            } else{
              $('.product-tbody').append(row);
            }

            var newCount = $('.widget-table-title .produc-count strong').text()-0+1;
            if(response.status=='success') {
              $('.widget-table-title .produc-count strong').text(newCount);
            }

            $('.product-count strong').html(+$('.product-count strong').html() + 1);
          }

          $('.no-results').remove();

          // Закрываем окно
          if(closeModal) {
            admin.closeModal('#add-product-wrapper');
            admin.initToolTip();
          }

        }
      );
    },

    cloneProd: function(id, prod) {
     // получаем с сервера все доступные пользовательские параметры
      admin.ajaxRequest({
         mguniqueurl:"action/cloneProduct",
         id:id
       },
       function(response) {
        admin.indication(response.status, response.msg);
        if(response.status == 'error') return false;
        response.data.category_unit = response.data.unit;
        if (response.data.category_unit == undefined || response.data.category_unit == '' || response.data.category_unit == 'undefined') {response.data.category_unit = 'шт.';}
        var row = catalog.drawRowProduct(response.data);

        // Если id небыло значит добавляем новую строку в начало таблицы.
        if($('.product-tbody tr:first').length>0) {
          $('.product-tbody tr:first').before(row);
        } else{
          $('.product-tbody ').append(row);
        }

        for(i = 0; i < prod.find('.view-price').length; i++) {
          $('tr#'+response.data.id+' .view-price:eq('+i+')').html(prod.find('.view-price:eq('+i+')').html());
        }

        var newCount = $('.widget-table-title .produc-count strong').text()-0+1;
        if(response.status=='success') {
          $('.widget-table-title .produc-count strong').text(newCount);
        }

        $('.product-count strong').html(+$('.product-count strong').html() + 1);
      });
    },

    /**
     * изменяет строки в таблице товаров при редактировании изменении.
     */
    drawRowProduct: function(element) { 
        if(!element.real_price) {
          element.real_price = element.price;
        }
      // получаем название категории из списка в форме, чтобы внести в строку в таблице
          var cat_name = $('.product-text-inputs select[name=cat_id] option[value='+element.cat_id+']').text();
          if (cat_name.indexOf(' -- ') != -1) {
            cat_name = cat_name.replace(/ -- /g, '');
            cat_name = '<a class="parentCat " title="" style="cursor:pointer;">../</a>' + cat_name;
          }
          // получаем URL имеющейся картинки товара, если она была
          var src=$('tr[id='+element.id+'] .image_url .uploads').attr('src');

          if(element.image_url) {
            // если идет процесс обновления и картинка новая то обновляем путь к ней
            src=element.image_url;
          }else {
            src=admin.SITE+'/mg-admin/design/images/no-img.png'
          }

          if(element.image_url=='no-img.png') {
            src=admin.SITE+'/mg-admin/design/images/no-img.png'
          }

          // переменная для хранения класса для подсветки активности товара
          var classForTagActivity='activity-product-true';

          var recommend = element.recommend==='1'?'active':'';
          // var titleRecommend = element.recommend?lang.PRINT_IN_RECOMEND:lang.PRINT_NOT_IN_RECOMEND;
          var titleRecommend = lang.PRINT_IN_RECOMEND;

          var $new = element.new==='1'?'active':'';
          // var titleNew = element.new?lang.PRINT_IN_NEW:lang.PRINT_NOT_IN_NEW;
          var titleNew = lang.PRINT_IN_NEW;

          var activity = element.activity==='1'?'active':'';
          var titleActivity = element.activity?lang.ACT_V_PROD:lang.ACT_UNV_PROD;
          // var titleActivity = lang.ACT_V_PROD;

          var printPrice = false;

          // построение  ячейки с ценами
          var tdPrice ='<td  class="price">';
          tdPrice += '<div class="row"><table class="variant-row-table">';
          if(element.price_course && !element.variants) {
            if(admin.numberDeFormat(element.price_course)!=admin.numberDeFormat(element.real_price)) {
              printPrice = true;
              tdPrice +='<tr><td colspan="3" class="text-right" style="font-weight:bold;">';
              tdPrice +='<span class="view-price " style="color: '+((parseFloat(element.price_course)>parseFloat(element.real_price))?"#1C9221":"#B42020")+'" title="с учетом скидки/наценки">'+admin.numberFormat(element.price_course)+' '+admin.CURRENCY+'</span><div class="clear"></div>';
              tdPrice += '</td>';
              tdPrice += '</tr>';
            }
          }
          counter = 0
          if(element.variants) {
            element.variants.forEach(function (variant, index, array) {
              if(index > 2) {
                hide = 'second-block-varians';
                hideCss = 'display:none;';
              } else {
                hide = '';
                hideCss = '';
              }
              if(variant.price_course) {
                if(admin.numberDeFormat(variant.price_course)!=admin.numberDeFormat(variant.real_price)) {
                  if (admin.numberDeFormat(variant.price) != admin.numberDeFormat(variant.price_course)) {
                    printPrice = true;
                    tdPrice += '<tr class="'+hide+'" style="'+hideCss+'"><td colspan="3" class="text-right" style="font-weight:bold;">';
                    tdPrice += '<span class="view-price " style="color: '+((parseFloat(variant.price_course)>parseFloat(variant.price))?"#1C9221":"#B42020")+'" title="с учетом скидки/наценки">'+admin.numberFormat(variant.price_course)+' '+admin.CURRENCY+'</span><div class="clear"></div>';
                    tdPrice += '</td>';
                    tdPrice += '</tr>';
                  }
                }
              }
              tdPrice +='<tr class="variant-price-row '+hide+'" style="'+hideCss+'"><td>';
              tdPrice +='<span class="price-help">'+(element.codeshow  ? '['+variant.code+'] ': '')+variant.title_variant.replace(/"/g, "&quot;")+'</span></td><td><input class="variant-price fastsave small" type="text" value="'+variant.price+'" data-packet="{variant:1,id:'+variant.id+',field:\'price\'}"/></td><td>'+ catalog.getShortIso(element.currency_iso) +'<div class="clear"></div></td></tr>';
              counter++;
            });

          } else {
            tdPrice +='<tr class="variant-price-row"><td>';
            tdPrice += '</td><td><input type="text" value="'+element.real_price+'" class="fastsave small variant-price" data-packet="{variant:0,id:'+element.id+',field:\'price\'}"/></td><td> '+catalog.getShortIso(element.currency_iso)+'<div class="clear"></div></td></tr>';
          }

          tdPrice += '</table></div>';
          if(counter > 3) tdPrice += '<div class="text-right"><a href="javascript:void(0)" class="link showAllVariants">'+lang.CATALOG_SHOW_ALL+'</a></div>';
          tdPrice += '</td>';

         // построение  ячейки с остатками вариантов товара
          var tdCount ='<td class="count" style="padding-top:3px;">';
          var margin = '';
          if(printPrice) {
            margin = 23;
          } else {
            margin = 2;
          }
          if(element.variants) {
            element.variants.forEach(function (variant, index, array) {
              if(index > 2) {
                hide = 'second-block-varians';
                hideCss = 'display:none;';
              } else {
                hide = '';
                hideCss = '';
              }
              if(variant.count<0) {variant.count='∞'}


                  count = '<input class="variant-count fastsave tiny" type="text" value="'+variant.count+'" data-packet="{variant:1,id:'+variant.id+',field:\'count\'}"/>';
                  padding = '';   

              tdCount +='<div style="margin: '+margin+'px 0 4px 0;'+hideCss+padding+'" class="count '+hide+'">'+count+' '+element.category_unit+'</div>';
            });
          } else {
            if(element.count<0) {element.count='∞'}


                count = '<input type="text" value="'+element.count+'" class="fastsave tiny" data-packet="{variant:0,id:'+element.id+',field:\'count\'}"/>';
                padding = '';   


            tdCount += '<div style="margin: '+margin+'px 0 2px 0;'+padding+'" class="count">'+count+' '+element.category_unit+'</div>';
          }
          tdCount += '</td>';
          var tdSort = '';
          if (element.sortshow) {
            tdSort = '<td class="sort"><input type="text" value="'+element.sort+'" class="fastsave tiny"  data-packet="{variant:0,id:'+element.id+',field:\'sort\'}"/></td>';
          }
          var link = element.link ? element.link : mgBaseDir+'/'+(element.category_url ? element.category_url : "catalog")+'/'+element.product_url;
          // html верстка для  записи в таблице раздела
          var row='\
            <tr id="'+element.id+'" data-id="'+element.id+'" class="product-row">\
              <td class="check-align">\
                <div class="checkbox">\
                  <input type="checkbox" id="prod-'+element.id+'" name="product-check">\
                  <label for="prod-'+element.id+'"></label>\
                </div>\
              </td>';
          if ($("#catalog-col-display-modal #catalog-number").prop('checked')) {
            row += '<td class="id">'+element.id+'</td>';
          }
          
          if($('.pageSortOff').val() != 'true') {
            row += '<td class="mover"><i class="fa fa-arrows"></i></td>';
          }

          if ($("#catalog-col-display-modal #catalog-code").prop('checked')) {
            row += '<td class="code">'+element.code+'</td>';
          }

          if ($("#catalog-col-display-modal #catalog-category").prop('checked')) {
            row += '<td id="'+element.cat_id+'" class="cat_id">'+cat_name+'</td>';
          }

          if ($("#catalog-col-display-modal #catalog-img").prop('checked')) {
            row += '<td class="product-picture image_url"><img class="uploads" src="'+src+'"/></td>';
          }
          
          row += '<td class="name"><span class="product-name"><a class="name-link tip edit-row" id="'+element.id+'" href="javascript:void(0);">'+(element.codeshow &&!element.variants  ? '['+element.code+'] ' : '')+element.title+'</a><a class="fa fa-external-link tip" title="'+lang.PRODUCT_VIEW_SITE+'" href="'+link+'"  target="_blank"></a></span></td>';
          
          if ($("#catalog-col-display-modal #catalog-price").prop('checked')) {
            row += tdPrice;
          }

          if ($("#catalog-col-display-modal #catalog-count").prop('checked')) {
            row += tdCount;
          }

          if ($("#catalog-col-display-modal #catalog-order").prop('checked')) {
            row += tdSort;
          }
          row += '<td class="actions">\
                <ul class="action-list fl-right">\
                  <li class="edit-row" id="'+element.id+'"><a href="javascript:void(0);" class="fa fa-pencil" title="'+lang.EDIT+'"></a></li>\
                  <li class=" new" data-id="'+element.id+'" title="'+titleNew+'" ><a href="javascript:void(0);" class="fa fa-tag '+$new+'"></a></li>\
                  <li class=" recommend" data-id="'+element.id+'" title="'+titleRecommend+'" ><a href="javascript:void(0);" class="fa fa-star '+recommend+'"></a></li>\
                  <li class="clone-row" id="'+element.id+'"><a href="javascript:void(0);" class="fa fa-files-o" title="'+lang.CLONE+'"></a></li>\
                  <li class="visible " data-id="'+element.id+'" title="'+titleActivity+'"><a href="javascript:void(0);" class="fa fa-lightbulb-o '+activity+'"></a></li>\
                  <li class="delete-order" id="'+element.id+'"><a href="javascript:void(0);" class="fa fa-trash" title="'+lang.DELETE+'"></a></li>\
                </ul>\
              </td>\
           </tr>';

        return row;
    },

    /**
     * Получает данные о продукте с сервера и заполняет ими поля в окне.
     */
    editProduct: function(id) {
      $('#add-product-wrapper .product-text-inputs').hide();
      $('#add-product-wrapper .preloader').show();
      admin.ajaxRequest({
        mguniqueurl:"action/getProductData",
        id: id,
      },
      catalog.fillFields()
      );
    },

    /**
     * Удаляет продукт из БД сайта и таблицы в текущем разделе
     */
    deleteProduct: function(id,imgFile,massDel,obj) {
      var confirmed = false;
      if(!massDel) {
        if(confirm(lang.DELETE+'?')) {
          confirmed = true;
        }
      } else {
        confirmed = true;
      }
      if(confirmed) {
        admin.ajaxRequest({
          mguniqueurl:"action/deleteProduct",
          id: id,
          imgFile: imgFile,
          msgImg: true
        },
        function(response) {
          if(!massDel) {admin.indication(response.status, response.msg);}
          if(response.status == 'error') return false;
          $(obj).parents('tr').detach();
          $('.product-count strong').html($('.product-count strong').html() - 1);
          }
        );
      }

    },


    /**
     * Выполняет выбранную операцию со всеми отмеченными товарами
     * operation - тип операции.
     */
    runOperation: function(operation, skipConfirm) {
      if(typeof skipConfirm === "undefined" || skipConfirm === null){skipConfirm = false;}
      var products_id = [];
      $('.product-tbody tr').each(function() {
        if($(this).find('input[name=product-check]').prop('checked')) {
          products_id.push($(this).attr('id'));
        }
      });

      //Объект для передачи дополнительных данных, необходимых при выполнения действия
      var data = {};

      if($('select#moveToCategorySelect').is(':visible')) {
        data.category_id = $('select#moveToCategorySelect').val();
      }

      var notice = (operation.indexOf('changecur') != -1) ? lang.RUN_NOTICE : '';


      if (skipConfirm || confirm(lang.RUN_CONFIRM + notice)) {
        admin.ajaxRequest({
          mguniqueurl: "action/operationProduct",
          operation: operation,
          products_id: products_id,
          data: data
        },
        function(response) {
          if(response.data.clearfilter) {
            admin.show("catalog.php","adminpage","",admin.sliderPrice);
          } else {
           if(response.data.filecsv) {
            admin.indication(response.status, response.msg);
            setTimeout(function() {
              if (confirm(lang.CATALOG_MESSAGE_3+response.data.filecsv+lang.CATALOG_MESSAGE_2)) {
              location.href = mgBaseDir+'/'+response.data.filecsv;
            }}, 2000);
           }
           if(response.data.fileyml) {
            admin.indication(response.status, response.msg);
            setTimeout(function() {
              if (confirm(lang.CATALOG_MESSAGE_1+response.data.fileyml+lang.CATALOG_MESSAGE_2)) {
              location.href = mgBaseDir+'/mg-admin?yml=1&filename='+response.data.fileyml;
            }}, 2000);
           }
           admin.refreshPanel();
         }
        }
        );
      }


    },

    uploaderCallbackVariant:function(file) {

      admin.ajaxRequest({
        mguniqueurl:"action/addImageUploader",
        imgType: file.mime,
        imgSize: file.size,
        imgName: file.name,
        imgUrl: file.url
      },

      function(response) {
        admin.indication(response.status, response.msg);
        if (response.status == 'success') {
          var src = admin.SITE+'/uploads/'+response.data;
          catalog.lastVariant.parents('ul').find('.img-this-variant').find('img').attr('src', src).attr('alt', src).data('filename', src);
          catalog.lastVariant.parents('tr').find('.img-button').hide();
          catalog.lastVariant.parents('tr').find('.del-img-variant').show();
          catalog.updateImageVar();
        }
      });
    },

    uploaderCallback:function(file) {

      admin.ajaxRequest({
        mguniqueurl:"action/addImageUploader",
        imgType: file.mime,
        imgSize: file.size,
        imgName: file.name,
        imgUrl: file.url
      },

      function(response) {
        admin.indication(response.status, response.msg);
        var mainurl = $('.main-image').find('img').attr('src').substr(-12).toLowerCase();

        if (response.status == 'success') {
          if (mainurl.indexOf('no-img.') >= 0) {
            var src = admin.SITE+'/uploads/'+response.data;
            $('.main-image').find('img').attr('src',src);
            $('.main-image').find('img').attr('alt',response.data);
          }
          if (mainurl.indexOf('no-img.') < 0) {
            var src = admin.SITE+'/uploads/'+response.data;
            var ttle = response.data.replace('prodtmpimg/', '');
            var row = catalog.drawControlImage(src, true,'','','');
            $('.sub-images').append(row);
            $('.images-block img:last').attr('alt', response.data);
          }
        }
      });
    },

    /**
    * Формирует HTML для добавления и удаления картинки
    */
    drawControlImage:function(url,main,filename,title,alt) {
      var mainclass="main-img-prod";
      if(main==true) {
        mainclass='small-img';
      }

      if(!main) {
        return '<div class="img-holder" data-filename="'+filename+'">\
                  <div class="img-dropzone">'+lang.CATALOG_DRAG_IMG+'</div>\
                  <a class="icon tip seo-image" href="javascript:void(0);" title="SEO настройка"><i class="fa fa-cogs" aria-hidden="true"></i></a>\
                    <div class="popup-holder" >\
                      <div class="custom-popup right" style="display:none;">\
                        <div class="row">\
                          <div class="large-12 columns">\
                            <label>title:</label>\
                          </div>\
                        </div>\
                        <div class="row">\
                          <div class="large-12 columns">\
                            <input type="text" name="image_title" value="'+title+'">\
                          </div>\
                        </div>\
                        <div class="row">\
                          <div class="large-12 columns">\
                            <label>alt:</label>\
                          </div>\
                        </div>\
                        <div class="row">\
                          <div class="large-12 columns">\
                            <input type="text" name="image_alt" value="'+alt+'">\
                          </div>\
                        </div>\
                        <div class="row">\
                          <div class="large-12 columns">\
                            <a class="button fl-left seo-image-block-close" href="javascript:void(0);"><i class="fa fa-times" aria-hidden="true"></i> '+lang.CANCEL+'</a>\
                            <a class="button success fl-right apply-seo-image" href="javascript:void(0);"><i class="fa fa-check" aria-hidden="true"></i> '+lang.APPLY+'</a>\
                          </div>\
                        </div>\
                      </div>\
                    </div>\
                    <img class="main_product_image" src="'+url+'" alt="'+filename+'">\
                  </div>\
                  <div class="img-actions clearfix">\
                    <div class="upload-form fl-left">\
                      <form class="imageform" method="post" noengine="true" enctype="multipart/form-data">\
                        <label class="button tip" title="'+lang.UPLOAD_IMG+'">\
                          <i class="fa fa-picture-o" aria-hidden="true"></i> Загрузить\
                          <input class="main_img_input" id="main_img_input" type="file" name="photoimg_multiple[]" multiple>\
                        </label>\
                      </form>\
                      <div class="additional_uploads_container">\
                        <div><label for="main_img_input">'+lang.CATALOG_DOWNLOAD+'<label></div>\
                        <div class="from_url">'+lang.CATALOG_DOWNLOAD_LINK+'</div>\
                        <div class="from_file">'+lang.CATALOG_DOWNLOAD_SERVER+'</div>\
                      </div>\
                      <div class="custom-popup url-popup" style="display:none">\
                      <div class="row">\
                        <div class="large-12 columns">\
                          <label>'+lang.CATALOG_IMG_LINK+':</label>\
                        </div>\
                      </div>\
                      <div class="row">\
                        <div class="large-12 columns">\
                          <input type="text">\
                        </div>\
                      </div>\
                      <div class="row">\
                        <div class="large-12 columns">\
                          <a class="button fl-left cancel-url" href="javascript:void(0);"><i class="fa fa-times"></i> '+lang.CANCEL+'</a>\
                          <a class="button success fl-right apply-url" href="javascript:void(0);"><i class="fa fa-check"></i> '+lang.APPLY+'</a>\
                        </div>\
                      </div>\
                    </div>\
                    </div>\
                    <a class="button alert tip fl-right cancel-img-upload" href="javascript:void(0);" title="'+lang.DELETE_IMAGE+'">\
                    <i class="fa fa-trash" aria-hidden="true"></i> Удалить</a>\
                  </div>';
      } else {
        return '';
      }
    },

   /**
    * Заполняет поля модального окна данными
    */
    fillFields:function() {

      return function(response) {
        var iso = response.data.currency_iso?response.data.currency_iso:admin.CURRENCY_ISO;
        admin.isoHuman = catalog.getShortIso(iso);

        $('.size-map tbody').html('');
        catalog.modalUnit = response.data.category_unit;
        catalog.realCatUnit = response.data.real_category_unit;
        if (response.data.product_unit == undefined || response.data.product_unit == null) {response.data.product_unit = '';}
        catalog.realUnit = response.data.product_unit;
        if(catalog.modalUnit == null) {catalog.modalUnit = 'шт.';}
        var imageDir = Math.floor(response.data.id/100)+'00/'+response.data.id+'/';

      
        catalog.supportCkeditor = response.data.description;
        $('.product-desc-wrapper textarea[name=html_content]').text(response.data.description);
        $('.product-desc-wrapper textarea[name=short_html_content]').text(response.data.short_description);
        $('.product-text-inputs input').removeClass('error-input');
        $('.product-text-inputs input[name=title]').val(response.data.title);
        $('.product-text-inputs input[name=link_electro]').val(response.data.link_electro);
        
        if(response.data.link_electro) {
          $('.section-catalog .del-link-electro').text(response.data.link_electro.substr(0,50));
        }
        
        $('.section-catalog .del-link-electro').attr('title',response.data.link_electro);
        if(response.data.link_electro) {
          $('.section-catalog .del-link-electro').show();
          $('.section-catalog .add-link-electro').hide();
        }
        $('.product-text-inputs select[name=cat_id]').val(response.data.cat_id);
        $('.product-text-inputs input[name=url]').val(response.data.url);

        catalog.selectCategoryInside(response.data.inside_cat);
        catalog.cteateTableVariant(response.data.variants, imageDir);
        
        if(!response.data.variants) {
          $('.product-text-inputs input[name=code]').val(response.data.code);
          $('.product-text-inputs input[name=price]').val(response.data.price);
          $('.product-text-inputs input[name=old_price]').val(response.data.old_price);
          $('.product-text-inputs input[name=weight]').val(response.data.weight);
          //превращаем минусовое значение в знак бесконечности
          var val = response.data.count;
          if((val == '\u221E' || val === ''|| parseFloat(val)<0)) {val = '∞';}
          $('.product-text-inputs input[name=count]').val(val);
        }

        var rowMain = '';
        var rows = '';

        response.data.images_product.forEach(
          function (element, index, array) {
            var title = response.data.images_title[index]?response.data.images_title[index]:'';
            var alt = response.data.images_alt[index]?response.data.images_alt[index]:'';

            var src = admin.SITE+'/mg-admin/design/images/no-img.png';
            if(element) {
              var src = element;
            }

            if(index!=0) {
              rows += catalog.drawControlImage(src, true, element, title, alt);
            } else {
              rowMain = catalog.drawControlImage(src, false, element, title, alt);
            }

          }
        );

        $('.main-image').html(rowMain);
        $('.sub-images').html(rows);
        $('.main-img-prod .main-image').hide();
        $('textarea[name=html_content]').val(response.data.description);
        $('textarea[name=short_html_content]').val(response.data.short_description);
        $('#add-product-wrapper input[name=meta_title]').val(response.data.meta_title);
        $('#add-product-wrapper input[name=meta_keywords]').val(response.data.meta_keywords);
        $('#add-product-wrapper textarea[name=meta_desc]').val(response.data.meta_desc);
        $('.yml-wrapper input[name=yml_sales_notes]').val(response.data.yml_sales_notes);
        catalog.drawRelatedProduct(response.data.relatedArr);
        catalog.addrelatedCategory(response.data.relatedCat);
        $('.save-button').attr('id',response.data.id);
        $('.save-button').data('recommend',response.data.recommend);
        $('.save-button').data('activity',response.data.activity);
        $('.save-button').data('new',response.data.new);
        $('.b-modal_close').attr('item-id', response.data.id);
        $('.cancel-img-upload').attr('id',response.data.id);
        $('.userField').html('');

        $('.shortDesc').hide();
        $('.add-short-desc').show();

        try{
          $('.symbol-count').text($('#add-product-wrapper textarea[name=meta_desc]').val().length);
        }catch(e) {
          $('.symbol-count').text('0');
        }
        
        $('.userField tr td .value').each(function() {
          var value = $(this).text();
          if (value) {
            $(this).text(admin.htmlspecialchars(value));
          }
        });


        var iso = response.data.currency_iso?response.data.currency_iso:admin.CURRENCY_ISO;

        $('#add-product-wrapper .btn-selected-currency').text(catalog.getShortIso(iso));

        $('#add-product-wrapper select[name=currency_iso] option[value='+JSON.stringify(iso)+']').prop('selected','selected');

      
        // Проверка на наличии поля в возвращаемом результате, для вывода предупреждения,
        // если этот товар является комплектом товаров, созданным в плагине "Комплект товаров"
        if (response.data.plugin_message) {
          $('#add-product-wrapper .add-product-table-icon').append(response.data.plugin_message);
        }
        //$('textarea[name=html_content]').ckeditor(function() {});

        $('.sub-images').sortable({
          sort: function(e) {
            var Y = e.pageY; // положения по оси Y
            var X = e.pageX; // положения по оси Y
            $('.ui-sortable-helper').offset({ top: (Y - 50)});
            $('.ui-sortable-helper').offset({ left: (X - 60)});
            $(this).find('.img-action-hover').css('opacity', '0');
          },
          stop: function() {
            $(this).find('.img-action-hover').attr('style', '');
          }
        });

        $('.variant-table input').each(function() {
          $(this).attr('value', $(this).val());
        });
        catalog.saveVarTable = $('.variant-table').html();
        catalog.saveTypeGroupVar = 'color';

        if(response.data.variants && response.data.variants.length > 1 && $('.size-map table tbody').html() != '' && $('.size-map table tbody').html() != undefined) {

          $('.size-map tbody .checkbox input').each(function() {
            if($(this).prop('checked')) {
              if($(this).data('size') == 'none' || $(this).data('color') == 'none') {
                $('.select-typeGroupVar select').val('default');
                catalog.saveTypeGroupVar = 'default';
              }
            }
          });
          
          catalog.buildGroupVarTable();

          if(catalog.saveTypeGroupVar == 'default') {
            $('.variant-table th:eq(0)').hide();
            $('.variant-table tr').each(function() {
              $(this).find('td:eq(0)').hide();
            });
          } else {
            $('.variant-table th:eq(0)').show();
            $('.variant-table tr').each(function() {
              $(this).find('td:eq(0)').show();
            });
          }
        } else {
          $('.typeGroupVar').hide();
          $('.variant-table-wrapper .add-position').show();
          $('.variant-row .fa-arrows').show();
        }

        if($('.size-map table tbody').html() != '' && $('.size-map table tbody').html() != undefined) {
          $('.variant-table-wrapper .add-position').hide();
        } else {
          $('.variant-table-wrapper .add-position').show();
        }

        if(($('.storageToView').html() != undefined) && ($('.storageToView').val() == 'all')) {
          $('.variant-table [name=count]').prop('disabled', true);
        } else {
          $('.variant-table [name=count]').prop('disabled', false);
        }

        $('#add-product-wrapper .product-text-inputs').show();
        $('#add-product-wrapper .preloader').hide();

        if($('#productCategorySelect').val() == 0) {
          $('.add-property-field').hide();
        } else {
          $('.add-property-field').show();
        }
        $('#add-product-wrapper select[name=currency_iso]').val(iso);
      }
    },

    buildGroupVarTable: function() {
      if($('.size-map').data('type') == '') {
        $('.variant-table tr').show();
        $('.variant-table .group-row').detach();
        $('.variant-table th:eq(0)').hide();
        $('.variant-table tr').each(function() {
          $(this).find('td:eq(0)').hide();
        });

        $('.left-line').replaceWith('<i class="fa fa-arrows"></i>');
        $('.hor-line').detach();
      } else {
        if($('.variant-table .variant-row').length > 1) {
          $('.variant-table th:eq(0)').show();
          $('.variant-table tr').each(function() {
            $(this).find('td:eq(0)').show();
          });
        } else {
          $('.variant-table th:eq(0)').hide();
          $('.variant-table tr').each(function() {
            $(this).find('td:eq(0)').hide();
          });
        }
        
      }

      if($('.variant-row:eq(0) [name=color]').val() != '' && $('.variant-row:eq(0) [name=size]').val() != '') {
        if($('#sizeCheck-'+$('.variant-row:eq(0) [name=color]').val()+'-'+$('.variant-row:eq(0) [name=size]').val()).val() == undefined) {
          $('.variant-table').hide();
          $('.add-position, .set-size-map').hide();
          $('.category-change-alert-size-map').detach();
          $('.variant-table-wrapper').append('<div class="category-change-alert-size-map" style="text-align:center;">\
            В выбранной категории отсутствуют характеристики размерной сетки необходимые для существующих вариантов товара<br>\
            <a href="javascript:void(0);" class="link back-category">Вернуть категорию</a>&nbsp;&nbsp;&nbsp;\
            <a href="javascript:void(0);" class="link drop-restart-variant">Заполнить варианты заново</a></div>');
          return false;
        } else {
          $('.variant-table').show();
          $('.category-change-alert-size-map').detach();
          catalog.saveCategory = $('#add-product-wrapper [name=cat_id]').val();
        }
      }

      $('.variant-table').show();
      $('.category-change-alert-size-map').detach();

      if($('.variant-row').length <= 1) {
        catalog.saveTypeGroupVar = 'default';
      }

      var storage = $('#add-product-wrapper .storageToView').val();
      if($('.size-map').data('type') != 'only-all') {
        $('.typeGroupVar').hide();
        return false;
      }
      setTimeout(function() {
        switch($('.size-map').data('type')) {
          case 'only-all':
            $('.typeGroupVar').show();
            $('.typeGroupVar option').show();
            $('.variant-table-wrapper .add-position').hide();
            $('.variant-row .fa-arrows').hide();
            $('.variant-row .fa-arrows').replaceWith('<div class="left-line"></div><div class="hor-line"></div>');
            break;
          case 'only-size':
            $('.typeGroupVar').show();
            $('.typeGroupVar option').show();
            $('.typeGroupVar option[value=color]').hide();
            if(catalog.saveTypeGroupVar == 'color') catalog.saveTypeGroupVar = 'size';
            $('.variant-table-wrapper .add-position').hide();
            $('.variant-row .fa-arrows').hide();
            $('.variant-row .fa-arrows').replaceWith('<div class="left-line"></div><div class="hor-line"></div>');
            break;
          case 'only-color':
            $('.typeGroupVar').show();
            $('.typeGroupVar option').show();
            $('.typeGroupVar option[value=size]').hide();
            if(catalog.saveTypeGroupVar == 'size') catalog.saveTypeGroupVar = 'color';
            $('.variant-table-wrapper .add-position').hide();
            $('.variant-row .fa-arrows').hide();
            $('.variant-row .fa-arrows').replaceWith('<div class="left-line"></div><div class="hor-line"></div>');
            break;
          default:
            catalog.saveTypeGroupVar = 'default';
            $('.typeGroupVar').hide();
            $('.variant-table-wrapper .add-position').show();
            $('.variant-row .fa-arrows').show();
            break;
        }

        // для отключения логики
        vAll = true;
        $('.variant-row').each(function() {
          if($(this).find('[name=color]').val() == '' || $(this).find('[name=size]').val() == '') {
            vAll = false;
            return false;
          }
        });
        if(vAll) {
          $('.typeGroupVar').show();
        } else {
          $('.typeGroupVar').hide();
        }

        $('.select-typeGroupVar').hide();

      }, 0);

      // userProperty.createdSizeVariant();
      // userProperty.createdSizeVariant();

      $('.variant-table').html(catalog.saveVarTable);
      if(storage == 'all') {
        $('.variant-row [name=count]').prop('disabled', true);
      } else {
        $('.variant-row [name=count]').prop('disabled', false);
      }
      tmplType = catalog.saveTypeGroupVar;
      if(tmplType == 'default') {
        $('.left-line, .hor-line').hide();
      } else {
        $('.left-line, .hor-line').show();
      }
      $('.variant-table .variant-row').css('background', 'none');
      $('.variant-table .variant-row').show();    
      if(tmplType == 'default') return false; 
      var variantTableSort = {};
      var counter = 0;
      // группы
      $('.variant-table tr.variant-row').each(function() {
        if($(this).find('[name='+tmplType+']').val() != undefined) {
          counter = 0;
          for(var key in variantTableSort[$(this).find('[name='+tmplType+']').val()]) {
            counter++;
          }
          if(variantTableSort[$(this).find('[name='+tmplType+']').val()] == undefined) variantTableSort[$(this).find('[name='+tmplType+']').val()] = {};
          variantTableSort[$(this).find('[name='+tmplType+']').val()][counter+1] = $(this).clone();
        }
      });
      // console.log(variantTableSort);,
      $('.variant-table tr').not('.text-left').detach();
      var tmp = $('.variant-table .text-left').parent().html();
      if($('.variant-table tbody').html() == undefined) {
        appendTbody = true;
      } else {
        appendTbody = false;
      }
      $('.variant-table').html('');
      $('.variant-table').html(tmp);
      if(appendTbody) $('.variant-table').append('<tbody></tbody>');
      $('#add-product-wrapper .storageToView option[value='+storage+']').prop('selected', 'selected');

      if(tmplType == 'color') {
        var varTitle = lang.COLOR;
      } else {
        var varTitle = lang.SIZE;
      }

      for(var key in variantTableSort) {
        $('.variant-table tbody').append('\
          <tr data-id="'+key+'" data-type="'+tmplType+'" class="group-row">\
            <td><i class="fa fa-chevron-down showGroupVar"></td>\
            <td style="cursor:pointer;border-left:10px solid '+$('.size-map .color-'+key).val()+';vertical-align:middle;">\
              <span class="tmplPreview showGroupVar">'+varTitle+': '+$('.size-map .'+tmplType+'-'+key).parent().find('.'+tmplType).val()+'</span>\
              <button class="tmplApply button" title="'+lang.APPLY_TMPL_VAR_GROUP+'">'+lang.APPLY_TO_VAR_GROUP+'</button>\
            </td>\
            <td class="tmplPreview">\
              <input class="tmpl-code" type="text" value="'+variantTableSort[key][1].find('[name=code]').val()+'">\
            </td>\
            <td><input class="tmpl-price" type="text" value="'+variantTableSort[key][1].find('[name=price]').val()+'"></td>\
            <td><input class="tmpl-old_price" type="text" value="'+variantTableSort[key][1].find('[name=old_price]').val()+'"></td>\
            <td><input class="tmpl-weight" type="text" value="'+variantTableSort[key][1].find('[name=weight]').val()+'"></td>\
            <td><input class="tmpl-count" type="text" value="'+variantTableSort[key][1].find('[name=count]').val()+'"></td>\
            <td>\
              '+variantTableSort[key][1].find('.action-list').parent().html()+'\
            </td>\
          </tr>');
        // console.log(key);
        for(var keyIn in variantTableSort[key]) {
          // console.log(key+'/'+keyIn);
          $('.variant-table tbody').append(variantTableSort[key][keyIn]);
        }
      }
      // $('.variant-table .variant-row').css('background', '#fff');
      $('.variant-table .variant-row').hide();    
      $('.typeGroupVar select option[value='+tmplType+']').prop('selected', 'selected');
      $('.variant-table .group-row .del-variant').addClass('deleteGroupVar').removeClass('del-variant');

      $('.typeGroupVar').closest('th').css('width', '150px').css('position','relative');

    },

   /**
    * Чистит все поля модального окна
    */
    clearFields:function() {
      $("#related-draggable").sortable({
        revert: true,
        handle: ".move-handle"
      });
      $("#related-cat-draggable").sortable({
        revert: true,
        handle: ".move-handle"
      });
      catalog.initSupportCkeditor = true;
      catalog.modalUnit = lang.UNIT;
      $('select[name=landingSwitch]').val(-1);
      $('[name=ytpText]').val('');
      $('.remove-added-background').click();
      $('select[name=landingIndividualTemplate]').val('noLandingTemplate');
      $('select[name=landingIndividualTemplate]').trigger('change');

      $('.product-text-inputs input[name=title]').val('');
      $('.product-text-inputs input[name=link_electro]').val(''),
      $('.product-text-inputs input[name=url]').val('');
      $('.product-text-inputs input[name=code]').val('');
      $('.product-text-inputs input[name=price]').val('');
      $('.product-text-inputs input[name=old_price]').val('');
      $('.product-text-inputs input[name=count]').val('');
      $('#add-product-wrapper select[name=inside_cat]').val([]);

      catalog.selectedStorage = 'all';
      catalog.selectCategoryInside('');

      var catId = $('.filter-container select[name=cat_id]').val();
      if(catId == 'null') {
        catId = 0;
      }

      $('select[name=inside_cat]').attr('size',4);
      $('.full-size-select-cat').removeClass('opened-select-cat').addClass('closed-select-cat');
      $('.full-size-select-cat').text(lang.PROD_OPEN_CAT);


      $('.product-text-inputs select[name=cat_id]').val(catId);

      // $('.prod-gallery').html('<div class="small-img-wrapper"></div>');
      $('textarea[name=html_content]').val('');
      $('textarea[name=short_html_content]').val('');
      $('#add-product-wrapper input[name=meta_title]').val('');
      $('#add-product-wrapper input[name=meta_keywords]').val('');
      $('#add-product-wrapper textarea[name=meta_desc]').val('');
      $('.yml-wrapper input[name=yml_sales_notes]').val(''),
      $('.product-text-inputs .variant-table').html('');
      $('.added-related-product-block').html('');
      $('.added-related-product-block').css('width',"800px");
      $('.userField').html('');
      $('.symbol-count').text('0');
      $('.save-button').attr('id','');
      $('.save-button').data('recommend','0');
      $('.save-button').data('activity','1');
      $('.save-button').data('new','0');
      $('.select-product-block').hide();
      catalog.cteateTableVariant(null);
      catalog.deleteImage ='';

      $('.del-link-electro').hide();
      $('.add-link-electro').show();
      // Стираем все ошибки предыдущего окна если они были.
      $('.errorField').css('display','none');

      $('#add-product-wrapper .select-currency-block').hide();

      var short = catalog.getShortIso(admin.CURRENCY_ISO);
      $('#add-product-wrapper .btn-selected-currency').text(short);
      $('#add-product-wrapper select[name=currency_iso] option[value='+admin.CURRENCY_ISO+']').prop('selected','selected');
      $('.error-input').removeClass('error-input');

      catalog.supportCkeditor = '';
      $('.addedProperty').html('');

      $('#add-product-wrapper .custom-popup').css('display','none');
      $('#add-product-wrapper .product-desc-field').css('display','none');
      $('#add-product-wrapper .add-category').removeClass('open');

      $('.set-size-map').hide();
      $('.size-map').hide();

      $('.sub-images').html('');
    },


   /**
    * Добавляет изображение продукта
    */
    addImageToProduct:function(img_container) {
      var currentImg = '';
      img_container.find('.img-loader').show();

      if(img_container.find('.prev-img img').length > 0) {
        currentImg = img_container.find('.prev-img img').attr('alt');
      } else {
        currentImg = img_container.find('img').attr('data-filename');
      }

      //Пишем в поле deleteImage имена изображений, которые необходимо будет удалить при сохранении
      // if(catalog.deleteImage) {
      //   catalog.deleteImage += '|'+currentImg;
      // } else {
      //   catalog.deleteImage = currentImg;
      // }

      // отправка картинки на сервер
      img_container.find('.imageform').ajaxForm({
        type:"POST",
        url: "ajax",
        data: {
          mguniqueurl:"action/addImage"
        },
        cache: false,
        dataType: 'json',
        success: function(response) {
          admin.indication(response.status, response.msg);
          if(response.status != 'error') {
            var src=admin.SITE+'/uploads/'+response.data.img;
            catalog.tmpImage2Del += '|'+response.data.img;
            img_container.find('img').attr('src',src);
            img_container.find('img').attr('alt',response.data.img);
          } else {
            var src=admin.SITE+'/mg-admin/design/images/no-img.png';
            img_container.find('img').attr('src',src);
            img_container.find('img').attr('alt',response.data.img);
          }
         img_container.find('.img-loader').hide();
        }
      }).submit();
    },

    /**
     *  собирает названия файлов всех картинок чтобы сохранить их в БД в поле image_url
     */
    createFieldImgUrl: function() {
      var image_url = "";
      $('.images-block img').each(function() {
        if($(this).attr('alt') && $(this).attr('alt')!='undefined') {
          image_url += $(this).attr('alt')+'|';
        }
      });

      if(image_url) {
        image_url = image_url.slice(0,-1);
      }

      return image_url;
    },

    /**
     *  собирает все заголовки для картинок, чтобы сохранить их в БД в поле image_title
     */
    createFieldImgTitle: function() {
       var image_title = "";
       $('.images-block img').each(function() {
         if($(this).attr('alt') && $(this).attr('alt')!='undefined') {
           var title = $(this).parents('.parent').find('input[name=image_title]').val();
           title = title.replace('|','');
           image_title+=title+'|';
         }
       });

       if(image_title) {
         image_title = image_title.slice(0,-1);
       }

       return image_title;
    },

     /**
     *  собирает все описания для картинок, чтобы сохранить их в БД в поле image_alt
     */
    createFieldImgAlt: function() {
       var image_alt = "";
       $('.images-block img').each(function() {
         if($(this).attr('alt') && $(this).attr('alt')!='undefined') {
           var title = $(this).parents('.parent').find('input[name=image_alt]').val();
           title = title.replace('|','');
           image_alt+=title+'|';
         }
       });

       if(image_alt) {
         image_alt = image_alt.slice(0,-1);
       }

       return image_alt;
    },

   /**
     * Помещает  выбранную основной картинку в начало ленты
     * removemain = true - была удалена главная и требуется поднять из лены первую на место главной
     */
    upMainImg: function(obj, removemain) {
      if(obj.find('img').attr('src') == SITE+'/mg-admin/design/images/no-img.png') {
        return false;
      }
      var newMain = {
        src: obj.find('img').attr('src'),
        alt: obj.find('img').attr('alt'),
        imgTitle: obj.find('[name=image_title]').val(),
        imgAlt: obj.find('[name=image_alt]').val()
      };


      var main = $('.main-image');
      var sub = obj;

      sub.find('img').attr('src',main.find('img').attr('src')); 
      sub.find('img').attr('alt',main.find('img').attr('alt')); 
      sub.find('[name=image_title]').val(main.find('[name=image_title]').val()); 
      sub.find('[name=image_alt]').val(main.find('[name=image_alt]').val()); 

      main.find('img').attr('src',newMain.src); 
      main.find('img').attr('alt',newMain.alt); 
      main.find('[name=image_title]').val(newMain.imgTitle); 
      main.find('[name=image_alt]').val(newMain.imgAlt); 

      if(removemain) {
        obj.detach();
      }
    },

   /**
    * Удаляет изображение продукта
    */
    delImageProduct: function(id,img_container) {
      var imgFile = img_container.find('img').attr('src');

      if(confirm(lang.DELETE_IMAGE+'?')) {
        // catalog.deleteImage += "|"+imgFile;
        if(catalog.deleteImage) {
          catalog.deleteImage += '|'+imgFile;
        } 
        else {
          catalog.deleteImage = imgFile;
        }

        // удаляем текущий блок управления картинкой
        if($('.images-block img').length>1) {
          if(img_container.hasClass('main-image')) {
            catalog.upMainImg($('.sub-images .image-item:eq(0)'), true);
          } 
          else {
            img_container.remove();
          }
        } 
        else{
          // если блок единственный, то просто заменяем в нем картнку на заглушку
          var src = admin.SITE+'/mg-admin/design/images/no-img.png';
          img_container.find('img').attr('src',src).attr('alt','');
          img_container.data('filename','');
        }
      $('#tiptip_holder').hide();
      // admin.ajaxRequest({
      //   mguniqueurl:"action/deleteImageProduct",
      //   imgFile: imgFile,
      //   id: id,
      // },
      // function(response) {
      //   admin.indication(response.status, response.msg);
      // });
     }
    },

   /**
    * Поиск товаров
    */
    getSearch: function(keyword,forcedPage,showIndication) {
      if (forcedPage === undefined) {
        forcedPage = false;
      } 
      keyword = $.trim(keyword);
      if(keyword == lang.FIND+"...") {
        keyword = '';
      }
      if(!keyword) {
        admin.refreshPanel();
        admin.indication('error', lang.CATALOG_MESSAGE_4);
        return false
      };

      admin.ajaxRequest({
          mguniqueurl:"action/searchProduct",
          keyword:keyword,
          mode: 'groupBy',
          forcedPage: forcedPage,
      },
      function(response) {
        // console.log(response);
        if (showIndication === undefined) {
          admin.indication(response.status, response.msg);
        }
        $('.product-tbody tr').remove();
        response.data.catalogItems.forEach(
          function (element, index, array) {
             var row = catalog.drawRowProduct(element);
             $('.product-tbody').append(row);
          });
          // Если в результате поиска ничего не найдено
          if(response.data.catalogItems.length==0) {
            var row = "<tr><td class='no-results' colspan='"+$('.product-table th').length+"'>"+lang.SEARCH_PROD_NONE+"</td></tr>"
            $('.product-tbody').append(row);
          }
          // $('.mg-pager').hide();
          $('.mg-pager').replaceWith(response.data.pager);
          $('.mg-pager a').attr('href', 'javascript:void(0);');
          $('.mg-pager .linkPage').addClass('linkPageCatalog').removeClass('linkPage');
        }
      );
    },


    //  Получает данные из формы фильтров и перезагружает страницу
    getProductByFilter: function() {
       var request = $("form[name=filter]").formSerialize();
       var insideCat = $('input[name="insideCat"]').prop('checked');
       admin.show("catalog.php","adminpage",request+'&insideCat='+insideCat+'&applyFilter=1&displayFilter=1',catalog.callbackProduct);
       return false;
    },

    // Устанавливает статус продукта - рекомендуемый
     recomendProduct:function(id, val) {
      admin.ajaxRequest({
        mguniqueurl:"action/recomendProduct",
        id: id,
        recommend: val,
      },
      function(response) {
        admin.indication(response.status, response.msg);
      }
      );
    },

    // Устанавливает статус - видимый
     visibleProduct:function(id, val) {
      admin.ajaxRequest({
        mguniqueurl:"action/visibleProduct",
        id: id,
        activity: val,
      },
      function(response) {
        admin.indication(response.status, response.msg);
      }
      );
    },

    // вывод в новинках
    newProduct:function(id, val) {
      admin.ajaxRequest({
        mguniqueurl:"action/newProduct",
        id: id,
        new: val,
      },
      function(response) {
        admin.indication(response.status, response.msg);
      });
    },

     // Добавляет строку в таблицу вариантов
    cteateTableVariant:function(variants, imageDir) {

      admin.ajaxRequest({
        mguniqueurl:"action/nextIdProduct",
      },
      function(response) {
        if (!$('.product-text-inputs .variant-table .default-code').val()) {
          var id = response.data.id;
          var prefix = response.data.prefix_code ? response.data.prefix_code : 'CN';
          $('.product-text-inputs .variant-table .default-code').val(prefix + id);
        }
      });
      if (catalog.realUnit == undefined || catalog.realUnit == 'undefined') {catalog.realUnit = catalog.modalUnit;}

      var unitHtml = '\
          <div class="popup-holder"><a class="btn-selected-unit" realUnit="'+catalog.realUnit+'" href="javascript:void(0);">'+catalog.modalUnit+'</a>\
            <div class="custom-popup input-unit-block" style="display: none;">\
              <div class="row">\
                <div class="large-12 columns">\
                  <label>'+lang.CATALOG_SET_UNIT+'</label>\
                </div>\
              </div>\
              <div class="row">\
                <div class="large-12 columns">\
                  <input type="text" value="'+catalog.realUnit+'" class="unit-input">\
                </div>\
              </div>\
              <div class="row">\
                <div class="large-12 columns" style="margin-top: 15px;">\
                  <a class="button success apply-unit fl-right" href="javascript:void(0);">\
                    <i class="fa fa-check"></i> '+lang.APPLY+'\
                  </a>\
                  <a class="button cancel-unit" href="javascript:void(0);">\
                  <i class="fa fa-times"></i> '+lang.CANCEL+'\
                </a>\
                </div>\
              </div>\
            </div>\
          </div>';
      // строим первую строку заголовков
      $('.product-text-inputs .variant-table').html('');
      if(variants) {
        var position ='\
        <tr class="text-left">\
          <th></th>\
          <th style="width:150px;position:relative;" class="varTitle">'+lang.NAME_VARIANT+'\
            <span class="typeGroupVar">\
              (<a href="javascript:void(0);" class="btn-selected-typeGroupVar">'+lang.GROUP_VAR+'</a>)\
              <div class="custom-popup select-typeGroupVar" style="left:calc(100% - 18px);display:none;">\
                <div class="row">\
                  <div class="large-12 columns">\
                    <label>'+lang.SELECT_GROUP_VAR+':</label>\
                  </div>\
                </div>\
                <div class="row">\
                  <div class="large-12 columns">\
                    <select>\
                      <option value="default">'+lang.LOL_ALL+'</option>\
                      <option value="color">'+lang.COLOR+'</option>\
                      <option value="size">'+lang.SIZE+'</option>\
                    </select>\
                  </div>\
                </div>\
                <div class="row">\
                  <div class="large-12 columns text-right">\
                    <a class="button cancel-typeGroupVar fl-left" href="javascript:void(0);">\
                      <i class="fa fa-times" aria-hidden="true"></i> '+lang.CANCEL+'</a>\
                    <a class="button success apply-typeGroupVar" href="javascript:void(0);">\
                      <i class="fa fa-check" aria-hidden="true"></i> '+lang.APPLY+'</a>\
                  </div>\
                </div>\
              </div>\
            </span></th>\
          <th style="width:80px;">'+lang.CODE_PRODUCT+'</th>\
          <th>'+lang.PRICE_PRODUCT+' (<a href="javascript:void(0);" class="btn-selected-currency"></a>)</th>\
          <th>'+lang.OLD_PRICE_PRODUCT+'</th>\
          <th>'+lang.WEIGHT+'</th>\
          <th>'+unitHtml+'</th>\
          <th class="hide-content"></th>\
        </tr>\ ';
        $('.variant-table').append(position);
        // заполняем вариантами продукта
        variants.forEach(function(variant, index, array) {
          var src = admin.SITE+"/mg-admin/design/images/no-img.png";
          if(variant.image) {
            src = variant.image;
          }

          if(variant.count<0) {variant.count='∞'};
          var position ='\
          <tr data-id="'+variant.id+'"  class="variant-row">\
            <td><i class="fa fa-arrows"></i></td>\
            <td style="display:none;"><input name="color" value="'+variant.color+'"><input name="size" value="'+variant.size+'"></td>\
            <td>\
              <label for="title_variant"><input type="text" name="title_variant" value="'+variant.title_variant.replace(/"/g, "&quot;")+'" class="product-name-input "title="'+lang.NAME_PRODUCT+'" ><div class="errorField" style="display:none;">'+lang.NAME_PRODUCT+'</div></label>\
            </td>\
            <td style="width:100px;">\
              <label for="code"><input type="text" name="code" value="'+variant.code+'" class="product-name-input "title="'+lang.T_TIP_CODE_PROD+'" ><div class="errorField" style="display:none;">'+lang.ERROR_EMPTY+'</div></label>\
            </td>\
            <td>\
              <label for="price"><input type="text" name="price" value="'+variant.price+'" class="product-name-input  "title="'+lang.T_TIP_PRICE_PROD+'" ><div class="errorField" style="display:none;">'+lang.ERROR_NUMERIC+'</div></label>\
            </td>\
            <td>\
              <label for="old_price"><input type="text" name="old_price" value="'+variant.old_price+'" class="product-name-input  "title="'+lang.T_TIP_OLD_PRICE+'" ><div class="errorField" style="display:none;">'+lang.ERROR_NUMERIC+'</div></label>\
            </td>\
            <td>\
              <label for="weight"><input type="text" name="weight" value="'+variant.weight+'" class="product-name-input  "title="'+lang.T_TIP_WEIGHT_PROD+'" ><div class="errorField" style="display:none;">'+lang.ERROR_NUMERIC+'</div></label>\
            </td>\
            <td>\
              <label for="count"><input type="text" name="count" value="'+variant.count+'" class="product-name-input  " title="'+lang.T_TIP_COUNT_PROD+'" ><div class="errorField" style="display:none;">'+lang.ERROR_NUMERIC+'</div></label>\
            </td>\
            <td class="hide-content actions">\
            <div class="variant-dnd"></div>\
            <ul class="action-list">\
              <div class="img-this-variant" style="display:none; position: relative;">\
                <img src="'+src+'" style="width:50px; min-height:100%; position: absolute; bottom: 0;" data-filename="'+variant.image+'">\
              </div>\
              <li>\
                <form method="post" noengine="true" enctype="multipart/form-data" class="img-button catalog_uploads_container_variants_wrapper" style="display:'+(variant.image.indexOf('no-img')==-1 ? 'none': 'inline-block')+'">\
                  <span class="add-img-clone"></span>\
                  <label>\
                    <a class="fa fa-picture-o"></a>\
                    <input type="file" style="display:none;" name="photoimg" class="add-img-var img-variant " title="'+lang.UPLOAD_IMG_VARIANT+'">\
                  </label>\
                  <div class="additional_uploads_container_variants">\
                    <div class="from_pc">'+lang.CATALOG_DOWNLOAD+'</div>\
                    <div class="from_url">'+lang.CATALOG_DOWNLOAD_LINK+'</div>\
                    <div class="from_file">'+lang.CATALOG_DOWNLOAD_SERVER+'</div>\
                    <div class="from_existing">'+lang.CHOOSE_FROM_IMG+'</div>\
                  </div>\
                  <div class="custom-popup url-popup" style="display:none;">\
                    <div class="row">\
                      <div class="large-12 columns">\
                        <label>'+lang.CATALOG_IMG_LINK+':</label>\
                      </div>\
                    </div>\
                    <div class="row">\
                      <div class="large-12 columns">\
                        <input type="text" name="variant_url">\
                      </div>\
                    </div>\
                    <div class="row">\
                      <div class="large-12 columns">\
                        <a class="button fl-left cancel-url" href="javascript:void(0);"><i class="fa fa-times"></i> '+lang.CANCEL+'</a>\
                        <a class="button success fl-right apply-url" href="javascript:void(0);"><i class="fa fa-check"></i> '+lang.APPLY+'</a>\
                      </div>\
                    </div>\
                  </div>\
                  <div class="custom-popup existing-popup" style="display:none;">\
                  </div>\
                </form>\
                <a href="javascript:void(0);" class="del-img-variant fa fa-picture-o" title="'+lang.CATALOG_DELETE_IMG_VAR+'" style="display:'+(variant.image.indexOf('no-img')==-1 ? 'inline-block': 'none')+'"> </a>\
              </li>\
              <li>\
                <a href="javascript:void(0);" class="del-variant fa fa-trash"></a>\
              </li>\
            </ul>\
            </td>\
          </tr>\ ';
          $('.variant-table').append(position);
        });
        $('.variant-table').data('have-variant','1');
      } else {
        var position ='\
        <tr class="text-left">\
          <th style="display:none" class="hide-content"></th>\
          <th style="display:none;style="width:150px;position:relative;"" class="hide-content">'+lang.NAME_VARIANT+'\
            <span class="typeGroupVar">\
              (<a href="javascript:void(0);" class="btn-selected-typeGroupVar">'+lang.GROUP_VAR+'</a>)\
              <div class="custom-popup select-typeGroupVar" style="left:calc(100% - 18px);display:none;">\
                <div class="row">\
                  <div class="large-12 columns">\
                    <label>'+lang.SELECT_GROUP_VAR+':</label>\
                  </div>\
                </div>\
                <div class="row">\
                  <div class="large-12 columns">\
                    <select>\
                      <option value="default">'+lang.LOL_ALL+'</option>\
                      <option value="color">'+lang.COLOR+'</option>\
                      <option value="size">'+lang.SIZE+'</option>\
                    </select>\
                  </div>\
                </div>\
                <div class="row">\
                  <div class="large-12 columns text-right">\
                    <a class="button cancel-typeGroupVar fl-left" href="javascript:void(0);">\
                      <i class="fa fa-times" aria-hidden="true"></i> '+lang.CANCEL+'</a>\
                    <a class="button success apply-typeGroupVar" href="javascript:void(0);">\
                      <i class="fa fa-check" aria-hidden="true"></i> '+lang.APPLY+'</a>\
                  </div>\
                </div>\
              </div>\
            </span></th>\
          <th>'+lang.CODE_PRODUCT+'</th>\
          <th>'+lang.PRICE_PRODUCT+' (<a href="javascript:void(0);" class="btn-selected-currency"></a>)</th>\
          <th>'+lang.OLD_PRICE_PRODUCT+'</th>\
          <th>'+lang.WEIGHT+'</th>\
          <th>'+unitHtml+'</th>\
          <th style="display:none" class="hide-content"></th>\
        </tr>\ ';
        $('.variant-table').append(position);
        var position ='\
          <tr class="variant-row">\
            <td class="hide-content"><i class="fa fa-arrows"></i></td>\
            <td style="display:none;"><input name="color"><input name="size"></td>\
            <td class="hide-content">\
              <label for="title_variant"><input type="text" name="title_variant" value="" class="product-name-input " title="'+lang.NAME_PRODUCT+'" ><div class="errorField" style="display:none;">'+lang.NAME_PRODUCT+'</div></label>\
            </td>\
            <td style="width:100px;">\
              <label for="code"><input type="text" name="code" value="" class="product-name-input default-code" title="'+lang.T_TIP_CODE_PROD+'" ><div class="errorField" style="display:none;">'+lang.ERROR_EMPTY+'</div></label>\
            </td>\
            <td>\
              <label for="price"><input type="text" name="price" value="" class="product-name-input  " title="'+lang.T_TIP_PRICE_PROD+'" ><div class="errorField" style="display:none;">'+lang.ERROR_NUMERIC+'</div></label>\
            </td>\
            <td>\
              <label for="old_price"><input type="text" name="old_price" value="" class="product-name-input  " title="'+lang.T_TIP_OLD_PRICE+'" ><div class="errorField" style="display:none;">'+lang.ERROR_NUMERIC+'</div></label>\
            </td>\
            <td>\
              <label for="weight"><input type="text" name="weight" value="" class="product-name-input  " title="'+lang.T_TIP_WEIGHT_PROD+'" ><div class="errorField" style="display:none;">'+lang.ERROR_NUMERIC+'</div></label>\
            </td>\
            <td>\
              <label for="count"><input type="text" name="count" value="" class="product-name-input  " title="'+lang.T_TIP_COUNT_PROD+'" ><div class="errorField" style="display:none;">'+lang.ERROR_NUMERIC+'</div></label>\
            </td>\
            <td class="hide-content actions">\
            <div class="variant-dnd"></div>\
            <ul class="action-list" style="display:none;">\
              <div class="img-this-variant" style="display:none; position: relative;">\
                <img src="'+admin.SITE+'/mg-admin/design/images/no-img.png" style="width:50px; min-height:100%; position: absolute; bottom: 0;" data-filename="">\
              </div>\
              <li>\
                <form method="post" noengine="true" enctype="multipart/form-data" class="img-button catalog_uploads_container_variants_wrapper" style="display:inline-block">\
                  <span class="add-img-clone"></span>\
                  <label>\
                    <a class="fa fa-picture-o"></a>\
                    <input type="file" style="display:none;" name="photoimg" class="add-img-var img-variant " title="'+lang.UPLOAD_IMG_VARIANT+'">\
                  </label>\
                  <div class="additional_uploads_container_variants">\
                    <div class="from_pc">'+lang.CATALOG_DOWNLOAD+'</div>\
                    <div class="from_url">'+lang.CATALOG_DOWNLOAD_LINK+'</div>\
                    <div class="from_file">'+lang.CATALOG_DOWNLOAD_SERVER+'</div>\
                    <div class="from_existing">'+lang.CHOOSE_FROM_IMG+'</div>\
                  </div>\
                  <div class="custom-popup url-popup" style="display:none;">\
                    <div class="row">\
                      <div class="large-12 columns">\
                        <label>'+lang.CATALOG_IMG_LINK+':</label>\
                      </div>\
                    </div>\
                    <div class="row">\
                      <div class="large-12 columns">\
                        <input type="text" name="variant_url">\
                      </div>\
                    </div>\
                    <div class="row">\
                      <div class="large-12 columns">\
                        <a class="button fl-left cancel-url" href="javascript:void(0);"><i class="fa fa-times"></i> '+lang.CANCEL+'</a>\
                        <a class="button success fl-right apply-url" href="javascript:void(0);"><i class="fa fa-check"></i> '+lang.APPLY+'</a>\
                      </div>\
                    </div>\
                  </div>\
                  <div class="custom-popup existing-popup" style="display:none;">\
                  </div>\
                </form>\
                <a href="javascript:void(0);" class="del-img-variant fa fa-picture-o" title="'+lang.CATALOG_DELETE_IMG_VAR+'" style="display:none"> </a>\
              </li>\
              <li>\
                <a href="javascript:void(0);" class="del-variant fa fa-trash"></a>\
              </li>\
            </ul>\
            </td>\
          </tr>';
          $('.variant-table').append(position);
          $('.variant-table').data('have-variant','0');
          $('.variant-table').sortable({
            opacity: 0.6,
            axis: 'y',
            handle: '.fa-arrows',
            items: "tr+tr"
          });
        }


        $('.btn-selected-currency').replaceWith('\
          <div class="popup-holder"><a class="btn-selected-currency" href="javascript:void(0);"></a>\
            '+$('#for-curency').html()+'\
          </div>\
          ');

        if($('#add-product-wrapper .variant-row').length > 1) {
          $('.hide-content').css('display','');
        } else {
          $('.hide-content').css('display','none');
        }

      $('.variant-table input').each(function() {
        if($(this).val() == 'null') {
          if($(this).attr('name') == 'weight') {
            $(this).val(0);
          } else {
            $(this).val('');
          }
        }
      });

      admin.initToolTip();
    },


    // Добавляет строку в таблицу вариантов
    addVariant:function(table) {
      if($('.variant-table').data('have-variant')=="0") {
        $('.variant-table .hide-content').show();
        $('.variant-table').data('have-variant','1');
      }
      var code = $('.variant-table input[name="code"]:first').val();

      var position ='\
        <tr class="variant-row">\
          <td><i class="fa fa-arrows"></i></td>\
          <td style="display:none;"><input name="color"><input name="size"></td>\
          <td class="hide-content">\
            <label for="title_variant"><input type="text" name="title_variant" value="" class="product-name-input " title="'+lang.NAME_PRODUCT+'" ><div class="errorField" style="display:none;">'+lang.NAME_PRODUCT+'</div></label>\
          </td>\
          <td>\
            <label for="code"><input type="text" name="code" value="" class="product-name-input default-code" title="'+lang.T_TIP_CODE_PROD+'"><div class="errorField" style="display:none;">'+lang.ERROR_EMPTY+'</div></label>\
          </td>\
          <td>\
            <label for="price"><input type="text" name="price" value="" class="product-name-input  " title="'+lang.T_TIP_PRICE_PROD+'" ><div class="errorField" style="display:none;">'+lang.ERROR_NUMERIC+'</div></label>\
          </td>\
          <td>\
            <label for="old_price"><input type="text" name="old_price" value="" class="product-name-input  " title="'+lang.T_TIP_OLD_PRICE+'" ><div class="errorField" style="display:none;">'+lang.ERROR_NUMERIC+'</div></label>\
          </td>\
          <td>\
            <label for="weight"><input type="text" name="weight" value="" class="product-name-input  " title="'+lang.T_TIP_WEIGHT_PROD+'" ><div class="errorField" style="display:none;">'+lang.ERROR_NUMERIC+'</div></label>\
          </td>\
          <td>\
            <label for="count"><input type="text" name="count" value="" class="product-name-input  " title="'+lang.T_TIP_COUNT_PROD+'" ><div class="errorField" style="display:none;">'+lang.ERROR_NUMERIC+'</div></label>\
          </td>\
          <td class="hide-content actions">\
          <div class="variant-dnd"></div>\
          <ul class="action-list">\
            <div class="img-this-variant" style="display:none; position: relative;">\
              <img src="'+admin.SITE+'/mg-admin/design/images/no-img.png" style="width:50px; min-height:100%; position: absolute; bottom: 0;" data-filename="">\
            </div>\
            <li>\
              <form method="post" noengine="true" enctype="multipart/form-data" class="img-button catalog_uploads_container_variants_wrapper" style="display: inline-block;">\
                <span class="add-img-clone"></span>\
                <label>\
                  <a class="fa fa-picture-o"></a>\
                  <input type="file" style="display:none;" name="photoimg" class="add-img-var img-variant " title="'+lang.UPLOAD_IMG_VARIANT+'">\
                </label>\
                <div class="additional_uploads_container_variants">\
                  <div class="from_pc">'+lang.CATALOG_DOWNLOAD+'</div>\
                  <div class="from_url">'+lang.CATALOG_DOWNLOAD_LINK+'</div>\
                  <div class="from_file">'+lang.CATALOG_DOWNLOAD_SERVER+'</div>\
                  <div class="from_existing">'+lang.CHOOSE_FROM_IMG+'</div>\
                </div>\
                <div class="custom-popup url-popup" style="display:none;">\
                  <div class="row">\
                    <div class="large-12 columns">\
                      <label>'+lang.CATALOG_IMG_LINK+':</label>\
                    </div>\
                  </div>\
                  <div class="row">\
                    <div class="large-12 columns">\
                      <input type="text" name="variant_url">\
                    </div>\
                  </div>\
                  <div class="row">\
                    <div class="large-12 columns">\
                      <a class="button fl-left cancel-url" href="javascript:void(0);"><i class="fa fa-times"></i> '+lang.CANCEL+'</a>\
                      <a class="button success fl-right apply-url" href="javascript:void(0);"><i class="fa fa-check"></i> '+lang.APPLY+'</a>\
                    </div>\
                  </div>\
                </div>\
                <div class="custom-popup existing-popup" style="display:none;">\
                </div>\
              </form>\
              <a href="javascript:void(0);" class="del-img-variant fa fa-picture-o" title="'+lang.CATALOG_DELETE_IMG_VAR+'" style="display:none"> </a>\
            </li>\
            <li>\
              <a href="javascript:void(0);" class="del-variant fa fa-trash"></a>\
            </li>\
          </ul>\
          </td>\
        </tr>';
      table.append(position);

      $('.variant-table input[name="code"]:last').val(code+'-'+$('.variant-table input[name="code"]').length);

      $('.variant-row:eq(0) .action-list').css('display','');

      $('.typeGroupVar').hide();

      $('.variant-table tr').each(function() {
        if($(this).find('[name=count]').val() == '') $(this).find('[name=count]').val('∞');
      });

      $('.hide-content').show();

      admin.initToolTip();
    },


    // возвращает пакет  вариантов собранный из таблицы вариантов
    getVariant: function() {
      catalog.errorVariantField = false;
      $('.errorField').hide();

      if($('.variant-table tr').length == 2) {
        if($('.variant-row:eq(0) [name=color]').val() == "" && $('.variant-row:eq(0) [name=size]').val() == "" ) {
          $('.variant-table').data('have-variant','0');
        } else {
          $('.variant-table').data('have-variant','1');
        }
      }

      if($('.variant-table').data('have-variant')=="1") {
        var result = [];
        $('.variant-table .variant-row').each(function() {

          //собираем  все значения полей варианта для сохранения в БД

          var id =$(this).data('id');
          var currency_iso = $('#add-product-wrapper select[name=currency_iso] option:selected').val();
          var obj = {};
          $(this).find('input').removeClass('error-input');
          $(this).find('input').each(function() {

            if($(this).attr('name')!='photoimg') {
              var val = $(this).val();
              if((val=='\u221E'||val==''||parseFloat(val)<0)&&$(this).attr('name')=="count") {val = "-1";}
              if(val==""&&$(this).attr('name')=='weight') {val = "0";}
              if(val==""&&$(this).attr('name')!='old_price'&&$(this).attr('name')!='color'&&$(this).attr('name')!='size'&&$(this).attr('name')!='variant_url') {
                $(this).addClass('error-input');
                catalog.errorVariantField = true;
                $(this).parents('td').find('.errorField').show();
              }
              obj[$(this).attr('name')] = val;
            }
          });
          obj["activity"] = 1;
          obj["id"] = id;
          obj["currency_iso"] = currency_iso;

          var filename = $(this).find('img[filename]').attr('filename');
          if(!filename || filename == undefined || filename == '') {filename = $(this).find('img[filename]').attr('filename')}
          if(!filename || filename == undefined || filename == '') {filename = $(this).find('img').attr('src')}
          obj["image"] = filename;

          //преобразуем полученные данные в JS объект для передачи на сервер
          result.push(obj);
        });

        return result;
      }
      return null;
    },

    // возвращает список id связанных товаров с редактируемым
    getRelatedProducts: function() {
      var result = '';
      $('.add-related-product-block .product-unit').each(function() {
        result += $(this).data('code') + ',';
      });
      result = result.slice(0, -1);


      return result;
    },
    // возвращает список id связанных категорий с редактируемым
    getRelatedCategory: function() {
      var result = '';
      $('.add-related-product-block .category-unit').each(function() {
        result += $(this).data('id') + ',';
      });
      result = result.slice(0, -1);
      return result;
    },

    // сохраняет параметры товара прямо со страницы каталога в админке
    fastSave:function(data, val, input) {
      var obj = eval("(" + data + ")");
      // Проверка поля для стоимости, является ли текст в него введенный числом.

      // знак бесконечности
      if((val=='\u221E'||val==''||parseFloat(val)<0)&&obj.field=="count") {val = "-1"; input.val('∞'); }


      if(isNaN(parseFloat(val))) {
        admin.indication('error', lang.ENTER_NUM);
        input.addClass('error-input');
        return false;
      } else {
        input.removeClass('error-input');
      }
      var id = input.parents('.product-row').attr('id');
      // получаем с сервера все доступные пользовательские параметры
      admin.ajaxRequest({
        mguniqueurl:"action/fastSaveProduct",
        variant:obj.variant,
        id:obj.id,
        field:obj.field,
        value:val,
        product_id: id
      },
      function(response) {
        if (response.data) {
          $(".product-tbody tr#"+id+" .price").find(".view-price[data-productId="+obj.id+"]").text(response.data+' '+admin.CURRENCY);
        }
        admin.clearGetParam();
        admin.indication(response.status, response.msg);
      });

    },


    importFromCsv:function() {
      admin.ajaxRequest({
        mguniqueurl:"action/importFromCsv",
      },
      function(response) {
        admin.indication(response.status, response.msg);
      });
    },

    /**
     * Загружает CSV файл на сервер для последующего импорта
     */
    uploadCsvToImport:function() {
      // отправка файла CSV на сервер
      $('.repeat-upload-file .message').text(lang.MESSAGE_WAIT);
      $('.upload-csv-form').ajaxForm({
        type:"POST",
        url: "ajax",
        data: {
          mguniqueurl:"action/uploadCsvToImport"
        },
        cache: false,
        dataType: 'json',
        error: function() {alert(lang.CATALOG_MESSAGE_5);},
        success: function(response) {
          admin.indication(response.status, response.msg);
          if(response.status=='success') {
            $('.section-catalog select[name=importScheme]').removeAttr('disabled');
            $('.section-catalog select[name=identifyType]').removeAttr('disabled');
            $('input[name=no-merge]').removeAttr('disabled');
            $('.repeat-upload-file').show();
            $('.block-upload-сsv .upload-btn').hide();
            catalog.setCsvCompliance();
            $('.repeat-upload-file .message').text(lang.FILE_READY_IMPORT);
            catalog.showSchemeSettings('auto');
          } else {
            $('.message-importing').text('');
            $('.import-container input[name="upload"]').val('');
          }
        },

      }).submit();
    },

    /**
     * Устанавливает первоначальное соответствие полей для CSV по их заголовкам
     */
    setCsvCompliance: function() {
      var importType = $('.section-catalog select[name="importType"]').val();

      admin.ajaxRequest({
        mguniqueurl:"action/setCsvCompliance",
        importType: importType,
      },function(response) {});
    },

     /**
     * Контролирует процесс импорта, выводит индикатор в процентах обработки каталога.
     */
    startImport:function(rowId, percent, downloadLink, iteration) {
      iteration = typeof iteration !== 'undefined' ? iteration : 1;
      var typeCatalog = $(".block-upload-сsv select[name=importType]").val();
      var identifyType = $(".block-upload-сsv select[name=identifyType]").val();
      var schemeType = $('.section-catalog select[name=importScheme]').val();

      // needed = '';

      // $('.columnComplianceModal tr').each(function() {
      //   if($(this).find('select').prop('required') && $(this).find('select').val() == 'none') {
      //     needed += 'Укажите соотвествие для поля - '+$(this).find('td:eq(0) b').text()+'\n';
      //   }
      // });

      // if(needed != '') {
      //   alert(needed);
      //   return true;
      // }

      var delCatalog = null;
      var delImages = null;
      if(!rowId) {
        if(!$('.loading-line').length) {
          $('.process').append('<div class="loading-line"></div>');
        }
        rowId = 0;
        delCatalog = $('input[name=no-merge]').val();
        delImages = $('input[name=no-img]').val();
      }
      defaultActive = $('input[name=active-default]').val();
      if(!percent) {
        percent = 0;
      }

      if(!downloadLink) {
        downloadLink = false;
      }

      if(!catalog.STOP_IMPORT) {
        $('.message-importing').html(lang.IMPORT_PROGRESS+percent+'% <img src="'+admin.SITE+'/mg-admin/design/images/loader-small.gif"><div class="progress-bar"><div class="progress-bar-inner" style="width:'+percent+'%;"></div>\
          <div>'+lang.PROCESSED+': '+rowId+' '+lang.LOCALE_STRING+'</div></div>');
      } else {
        $('.loading-line').remove();
      }

      // отправка файла CSV на сервер
      admin.ajaxRequest({
        mguniqueurl:"action/startImport",
        rowId:rowId,
        iteration:iteration,
        delCatalog:delCatalog,
        typeCatalog: 'MogutaCMS',
        identifyType: identifyType,
        schemeType: schemeType,
        downloadLink: downloadLink,
        delImages: delImages,
        defaultActive: defaultActive,
      },
      function(response) {
        if(response.status == 'error') {
          admin.indication(response.status, response.msg);
        }

        if(response.data.percent < 100) {
          if(response.data.status == 'canseled') {
            $('.message-importing').html(lang.IMPORT_STOP+response.data.rowId+ lang.LOCALE_GOODS+'  '+'[<a href="javascript:void(0);" class="repeat-upload-csv">'+lang.UPLOAD_ANITHER+'</a>]' );
            $('.loading-line').remove();
          } else {
            if(response.data.iteration == 2) response.data.rowId--;
            setTimeout(function() {
              catalog.startImport(response.data.rowId,response.data.percent,response.data.downloadLink,response.data.iteration);
            }, 2000);
          }
        } else {
           $('.cancel-importing').hide();
           $('.message-importing').html(lang.IMPORT_FINISHED+' \
              <a class="refresh-page custom-btn" href="'+mgBaseDir+'/mg-admin/">\n\
                <span>'+lang.CATALOG_REFRESH+'</span>\n\
              </a> '+lang.LOCALE_OR+' <a href="javascript:void(0);" class="gotoImageUpload custom-btn"><span>'+lang.GO_DOWNLOAD_IMG+'</span></a><br>\
              <a href="'+admin.SITE+'/import_csv_log.txt" target="blank">'+lang.VIEW_IMPORT_LOG+'</a>');
           $('.block-upload-сsv').hide();
    
           if(response.data.startGenerationImage == true) {    
            $('.message-importing').hide();
            $('.import-container h3.title').text(lang.CREATE_THUMB_IMG);  
            $('.block-upload-images').show();    
            $('.block-upload-images .upload-images').hide();  
            catalog.startGenerationImage = true;     
            catalog.startGenerationImageFunc(); 
          }

          //startImport
          $('.loading-line').remove();
        }
      });
    },

     /**
     * Клик по найденным товарам поиске в форме добавления связанного товара.
     */
    addrelatedProduct: function(elementIndex, product) {
      $('.search-block .errorField').css('display', 'none');
      $('.search-block input.search-field').removeClass('error-input');
      if(!product) {
        var product = admin.searcharray[elementIndex];
      }

      if (product.category_url.charAt(product.category_url.length-1) == '/') {
        product.category_url = product.category_url.slice(0,-1);
      }

      var html = catalog.rowRelatedProduct(product);
      $('.added-related-product-block .product-unit[data-id='+product.id+']').remove();
      $('.related-wrapper .added-related-product-block').prepend(html);
      catalog.widthRelatedUpdate();
      catalog.msgRelated();
      $('input[name=searchcat]').val('');
      $('.select-product-block').hide();
      $('.fastResult').hide();
    },
      /**
     * Клик по выбранным связанным категориям 
     */
    addrelatedCategory: function(category) {
      var html = '';
      category.forEach(function(item, i, arr) {
        if(item.image_url == null) {
          image_url = '/uploads/no-img.jpg';
        } else {
          image_url = item.image_url;
        }
        html += '\
      <div class="category-unit" data-id='+ item.id +'>\
          <div class="product-img">\
              <a href="javascript:void(0);"><img src="' + mgBaseDir + image_url  + '"></a>\
          </div>\
          <a href="' + mgBaseDir + '/'+ item.parent_url + item.url + 
              '" data-url="' + item.url +'" class="product-name" target="_blank" title="' +
              item.title + '">' +
              item.title + '</a>\
          <a class="move-handle fa fa-arrows" href="javascript:void(0);"></a>\
          <a class="remove-added-category custom-btn fa fa-trash" href="javascript:void(0);"><span></span></a>\
      </div>\
      ';
        $('.added-related-category-block .category-unit[data-id='+item.id+']').remove();
      }) 
      $('.related-wrapper .added-related-category-block').prepend(html);
      catalog.widthRelatedUpdate();
      catalog.msgRelated();
      $('.search-block.category select[name=related_cat] option').prop('selected', false);
      $('.select-product-block').hide();
    },

     /**
     * формирует верстку связанного продукта.
     */
    rowRelatedProduct: function(product) {
      var price = (product.real_price) ? product.real_price : product.price;

      var html = '\
      <div class="product-unit ui-state-default" data-id='+ product.id +' data-code="'+ product.code +'">\
        <div class="product-img" style="text-align:center;height:50px;">\
          <a href="javascript:void(0);"><img src="' + product.image_url + '" style="height:50px;"></a>\
          <a class="move-handle fa fa-arrows" href="javascript:void(0);"></a>\
          <a class="remove-img fa fa-trash tip remove-added-product" href="javascript:void(0);" aria-hidden="true" data-hasqtip="88" oldtitle="'+lang.DELETE+'" title="" aria-describedby="qtip-88"></a>\
        </div>\
        <a href="' + mgBaseDir + '/' + product.category_url + "/" + product.product_url +
          '" data-url="' + product.category_url +
          "/" + product.product_url + '" class="product-name" target="_blank" title="' +
          product.title + '">' +
          product.title + '</a>\
        <span>' + price +' '+ admin.CURRENCY+'</span>\
      </div>\
      ';
      return html;
    },

    //выводит связанные товары
    //relatedProducts - массив с товарами
    drawRelatedProduct: function(relatedArr) {
      relatedArr.forEach(function (product, index, array) {
        var html = catalog.rowRelatedProduct(product);
        $('.related-wrapper .added-related-product-block').append(html);
        catalog.widthRelatedUpdate();
      });
      catalog.msgRelated();
    },

    //выводит ссылку в пустом блоке для добавления связанного товара
    msgRelated: function() {
      if($('.added-related-product-block .product-unit').length==0&&$('.added-related-category-block .category-unit').length==0) {
        if ($('a.add-related-product.in-block-message').length==0) {
        $('.related-wrapper .added-related-product-block').append('\
         <a class="add-related-product in-block-message" href="javascript:void(0);"><span>'+lang.RELATED_PROD+'</span></a>\
       ');
        }
        $('.added-related-product-block').width('800px');
      }else {
        $('.added-related-product-block .add-related-product').remove();
      };
      if ($('.added-related-category-block .category-unit').length==0) {
        $('.add-related-product-block .add-related-category.in-block-message').hide();
      } else {
        $('.add-related-product-block .add-related-category.in-block-message').show();
      }
    },

    //пересчитывает ширину блока с связанными товарами, для работы скрола.
    widthRelatedUpdate: function() {
      var prodWidth = $('.product-unit').length * ($('.product-unit').width() + 30);
      var catWidth = $('.category-unit').length * ($('.category-unit').width() + 30);
      if(prodWidth > catWidth) {
        $('.related-block').width(prodWidth);
      } else {
        $('.related-block').width(catWidth);
      }
      if($('.product-unit').length == 0) {
        $('.added-related-product-block').css('display','none');
      } else {
        $('.added-related-product-block').css('display','');
      }
      if($('.category-unit').length == 0) {
        $('.added-related-category-block').css('display','none');
      } else {
        $('.added-related-category-block').css('display','');
      }
    },

    /**
     * Останавливает процесс импорта в каталог товаров
     */
    canselImport:function() {
      $('.message-importing').text(lang.STOP_IMPORT);
      catalog.STOP_IMPORT=true;
      admin.ajaxRequest({
        mguniqueurl:"action/canselImport"
      },
      function(response) {
        admin.indication(response.status, response.msg);
      });
    },

    /**
     *Пакет выполняемых действий после загрузки раздела товаров
     */
    callbackProduct:function() {
      admin.sliderPrice();
      if (!$('.section-catalog table tbody').data('refresh')) {
        admin.AJAXCALLBACK = [
          {callback:'admin.sortable', param:['.product-table > tbody','product', true]},
        ];
      }  
      if($('#catalog-order').prop('checked')) {
        $('.product-table .fa-arrows').hide();
      } else {
        $('.product-table .fa-arrows').show();
      }
    },
    

    /**
     * Выделяет все категории в списке, в которых будет отображаться товар
     */
    selectCategoryInside:function(selectedCatIds) {
      if(!selectedCatIds) {
        $('.add-category').removeClass('opened-list');
        $('.inside-category').hide();
      } else {
        $('.add-category').addClass('opened-list');
        $('.inside-category').show();
      }
      if(selectedCatIds) {
      var htmlOptionsSelected = selectedCatIds.split(',');
      $('select[name=inside_cat] option').prop('selected', false);
      function buildOption(element, index, array) {
        $('.inside-category select[name="inside_cat"] [value="' + element + '"]').prop('selected', 'selected');
      }
      ;
      htmlOptionsSelected.forEach(buildOption);
      }
    },

    /**
     * Возвращает список выбранных категорий для товара
     */
    createInsideCat: function() {
      var category = '';
      $('select[name=inside_cat] option').each(function() {
        if ($(this).prop('selected')) {
          category += $(this).val() + ',';
        }
      });

      category = category.slice(0, -1);

      return category;
    },

    /**
     * Возвращает список выбранных категорий для товара
     */
    getFileElectro: function(file) {
      var dir = file.url;
      dir= dir.replace(mgBaseDir, '');
      $('.section-catalog input[name="link_electro"]').val(dir);
      $('.section-catalog .del-link-electro').text(dir.substr(0,50));
      $('.section-catalog .del-link-electro').attr('title',dir);
      $('.section-catalog .del-link-electro').show();
      $('.section-catalog .add-link-electro').hide();
    },

    /**
     * Смена валюты
     */
    changeIso: function() {
      var short = $('#add-product-wrapper select[name=currency_iso] option:selected').text();
      var rate = $('#add-product-wrapper select[name=currency_iso] option:selected').data('rate');
      $('#add-product-wrapper .btn-selected-currency').text(short);
      $('#add-product-wrapper .select-currency-block').hide();
    },

    /**
     * Возвращает сокращение, из списка допустимых валют
     * @param {type} iso
     * @returns {undefined}
     */
    getShortIso: function(iso) {
      iso = JSON.stringify(iso);
      var short = $('#for-curency select[name=currency_iso] option[value='+iso+']').text();
      return short;
    },

    closeAddedProperty: function(type) {
      if (type == 'close') {
        $('.addedProperty .new-added-prop').each(function() {
          var id = $(this).data('id');
          admin.ajaxRequest({
            mguniqueurl: "action/deleteUserProperty",
            id: id
          })
        });
      }
      $('#add-product-wrapper .new-added-properties').hide();
      $('#add-product-wrapper .new-added-properties input').val('');
      $('#add-product-wrapper .new-added-properties input').removeClass('error-input');
      $('.new-added-properties .errorField').hide();
    },

    // добавляет новую характеристику
    addNewProperty: function (name, value) {
      admin.ajaxRequest({
        mguniqueurl: "action/addUserProperty",
        type: 'string',
      },
        function (response) {
          var id = response.data.allProperty.id;
          var html = '<div class="new-added-prop" data-id="' + id + '">\
                        <div class="row">\
                          <div class="medium-5 small-12 columns">\
                            <label>' + name + ':</label>\
                          </div>\
                          <div class="medium-7 small-11 columns to-input-btn">\
                            <input class="property custom-input" type="text" value="' + value + '" data-id="temp-'+id+'" name="'+id+'">\
                            <a href="javascript:void(0);" class="remove-added-property fa fa-trash btn red"></a>\
                          </div>\
                        </div>\
                      </div>';
          $('#add-product-wrapper .addedProperty').prepend(html);
          admin.ajaxRequest({
            mguniqueurl: "action/saveUserProperty",
            id: id,
            name: name,
          })
          var category = $('.product-text-inputs select[name=cat_id]').val();
          admin.ajaxRequest({
            mguniqueurl: "action/saveUserPropWithCat",
            id: id,
            category: category
          })
        })
      catalog.closeAddedProperty();
    },

     //Добавляет новую характеристику
    saveAddedProperties: function () {
      $('.addedProperty .new-added-prop ').each(function () {
        var id = $(this).data('id');
        var category = $('.product-text-inputs select[name=cat_id]').val();
        admin.ajaxRequest({
          mguniqueurl: "action/saveUserPropWithCat",
          id: id,
          category: category
        })
      })
    },
  }
})();

// инициализация модуля при подключении
catalog.init();
