/**
 * Модуль для  раздела "Настройки".
 */

var settings = (function () {
  return {
    codeEditor: null,
    codeEditorWidgetCode: null,
    allowTemplateAutoClicks: true,
         
    /**
     * Инициализирует обработчики для кнопок и элементов раздела.
    */
    init: function() {


      $('.admin-center').on('change', '.section-settings #qqvariantToSize1c', function() { 
        // для 1с
        if($('#qqvariantToSize1c').prop('checked')) {
          $('input[name="colorName1c"]').closest('.row').show();
          $('input[name="sizeName1c"]').closest('.row').show();
        } else {
          $('input[name="colorName1c"]').closest('.row').hide();
          $('input[name="sizeName1c"]').closest('.row').hide();
        }
      });

      $('.admin-center').on('click', '.section-settings #useSeoRewrites', function() { 
        if($(this).prop('checked')) {
          $('.addShortLink-table').show();
        } else {
          $('.addShortLink-table').hide();
        }
      });

      // переключение вкладок
      $('.admin-center').on('click', '.section-settings .tabs-title-settings', function() { 
        $('.tabs-title-settings').removeClass('is-active');
        $(this).addClass('is-active');

        $('.tabs-content-settings .tabs-panel').hide();
        $($(this).find('a').data('target')).show();
        admin.showSideArrow();
      });

      $('.admin-center').on('click', '.section-settings .viewAgreement', function() { 
        settings.allowTemplateAutoClicks = false;
        $('.section-settings #tab-template a').click();
        $('.section-settings .template-tabs-menu a[data-target="#ttab3"]').click();
        $('.section-settings .template-tabs-content #ttab3 a:contains("layout_agreement.php")').click();
        settings.allowTemplateAutoClicks = true;
      });

      // делаем клик по первой вкладке редактирования шаблона, для активации редактора и прочего
      $('.admin-center').on('click', '.section-settings #tab-template', function() {
        if (settings.allowTemplateAutoClicks) {
          $('.admin-center .section-settings #tab-template-settings .template-tabs:eq(0)').click();
        } 
      });

      // переключение вкладок шаблона
      $('.admin-center').on('click', '.section-settings .template-tabs', function() {  
        $('.template-tabs').removeClass('is-active');
        $(this).addClass('is-active');

        $('.template-tabs-content  .tabs-panel').hide();
        $($(this).find('a').data('target')).show();

        var height = $($('.template-tabs-menu').find('.is-active a').data('target')).height();
        if(height < 550) {
          height = 550;
        }
        if (settings.allowTemplateAutoClicks) {
          $('.CodeMirror').height(height-5);
          $('.CodeMirror-scroll').scrollTop(2);
          if ($('.file-template:visible:first').length) {
            $('.file-template:visible:first').click();
          }
          else{
            $('.CodeMirror').remove();
            $('.save-file-template').hide();  
          }
        }
      });

      $('body').on('click', '.tabs-content-settings #tab-shop-settings .accordion .accordion-item:nth-child(1) .accordion-title', function() {
        $('.CodeMirror').remove();
        codeEditorWidgetCode = CodeMirror.fromTextArea(document.getElementById("widgetCode"), {
          lineNumbers: true,           
          mode: "text/javascript",
          extraKeys: {"Ctrl-F": "findPersistent"},
          showMatchesOnScrollbar:true ,
          lineWrapping:true
        });

        $('.CodeMirror').height(200);
        $('.CodeMirror-scroll').scrollTop(2);
      });

      //скрывалка полей  при загрузке
      $('body').on('click', '.tabs-content-settings .accordion .accordion-item .accordion-title', function() {
        $('[name=cacheMode]').trigger('change');
        $('[name=printAgreement]').trigger('change');
      });

      //скрывалка полей при клике
      $('body').on('change', '#tuseCaptcha, #tcaptchaOrder, #tuseReCaptcha, [name=searchType], #tusePhoneMask, #tmainPageIsCatalog, #tprintAgreement', function() {
        if ($('#tuseCaptcha').prop('checked') || $('#tcaptchaOrder').prop('checked')) {
          $('[name=useReCaptcha]').parent().parent().parent().parent().show();
        }
        else{
          $('#tuseReCaptcha').prop('checked',false);
          $('[name=useReCaptcha]').parent().parent().parent().parent().hide();
          $('[name=reCaptchaKey]').parent().parent().parent().parent().hide();
          $('[name=reCaptchaSecret]').parent().parent().parent().parent().hide();
        }
        if ($('#tuseReCaptcha').prop('checked')) {
          $('[name=reCaptchaKey]').parent().parent().parent().parent().show();
          $('[name=reCaptchaSecret]').parent().parent().parent().parent().show();
        }
        else{
          $('[name=reCaptchaKey]').parent().parent().parent().parent().hide();
          $('[name=reCaptchaSecret]').parent().parent().parent().parent().hide();
        }
        if ($('[name=searchType]').val() == 'sphinx') {
          $('[name=searchSphinxHost]').parent().parent().parent().parent().show();
          $('[name=searchSphinxPort]').parent().parent().parent().parent().show();
          $('[name=sphinxLimit]').parent().parent().parent().parent().show();
        }
        else{
          $('[name=searchSphinxHost]').parent().parent().parent().parent().hide();
          $('[name=searchSphinxPort]').parent().parent().parent().parent().hide();
          $('[name=sphinxLimit]').parent().parent().parent().parent().hide();
        }
        if ($('#tusePhoneMask').prop('checked')) {
          $('[name=phoneMask]').parent().parent().parent().parent().show();
        }
        else{
          $('[name=phoneMask]').parent().parent().parent().parent().hide();
        }
        if ($('#tprintAgreement').prop('checked')) {
          $('.viewAgreement').show();
        }
        else{
          $('.viewAgreement').hide();
        }
        if ($('#tmainPageIsCatalog').prop('checked')) {
          $('[name=randomProdBlock]').parent().parent().parent().parent().show();
          $('[name=countNewProduct]').parent().parent().parent().parent().show();
          $('[name=countRecomProduct]').parent().parent().parent().parent().show();
          $('[name=countSaleProduct]').parent().parent().parent().parent().show();
        }
        else{
          $('[name=randomProdBlock]').parent().parent().parent().parent().hide();
          $('[name=countNewProduct]').parent().parent().parent().parent().hide();
          $('[name=countRecomProduct]').parent().parent().parent().parent().hide();
          $('[name=countSaleProduct]').parent().parent().parent().parent().hide();
        }
      });

      // открытие модалки с уведомлениями
      $('.admin-center').on('click', '.section-settings #tab-template-settings .browseMessages', function() {    
        settings.updateMessageModal();
      });

      // смена языка в модалке с уведомлениями
      $('body').on('change', '#messages-modal .select-lang', function() {
        settings.updateMessageModal();
      });

      // сброс разделов в модалке с уведомлениями
      $('body').on('click', '#messages-modal .clearfix a', function() {
        admin.ajaxRequest({
          mguniqueurl: "action/resetMsgs",   
          type: $(this).attr('part'),       
        },
        function(response) {       
          $.each(response.data, function (index) {
            $('#messages-modal .clearfix [name='+index+']').val(response.data[index]);
          });
        });
      });

      $('body').on('click', '#messages-modal .save-button', function() {
        var fields = [];

        $('#messages-modal .clearfix input').each(function(index,element) {          
          fields.push({'id':$(this).attr('ajdi'), 'name':$(this).attr('name'), 'val':$(this).val()});
        });

        admin.ajaxRequest({
          mguniqueurl: "action/saveMsgs",   
          lang: $('#messages-modal .select-lang').val(),      
          fields: fields    
        },
        function(response) {
          admin.indication(response.status, 'Сохранено');
          admin.closeModal('#messages-modal');
        });
      });

      // переход во вкладку "Магазин"
      $('.admin-center').on('click', '.section-settings #tab-shop', function() {
        includeJS(mgBaseDir+'/mg-core/script/codemirror/lib/codemirror.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/mode/php/php.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/mode/xml/xml.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/mode/javascript/javascript.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/addon/search/search.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/addon/search/searchcursor.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/addon/search/jump-to-line.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/addon/search/match-highlighter.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/addon/search/matchesonscrollbar.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/addon/dialog/dialog.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/addon/scroll/annotatescrollbar.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/addon/scroll/scrollpastend.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/addon/scroll/simplescrollbars.js');

        if ($('[name=sitename]').is(':visible')) {
          $('.CodeMirror').remove();
          codeEditorWidgetCode = CodeMirror.fromTextArea(document.getElementById("widgetCode"), {
            lineNumbers: true,           
            mode: "application/x-httpd-php",
            extraKeys: {"Ctrl-F": "findPersistent"},
            showMatchesOnScrollbar:true ,
            lineWrapping:true
          });

          $('.CodeMirror').height(200);
          $('.CodeMirror-scroll').scrollTop(2);
        }

        settings.openTab($(this).attr('id'));
      });

      // переход во вкладку "Интеграции"
      $('.admin-center').on('click', '.section-settings #tab-Integration', function() {    
        settings.openTab($(this).attr('id'));
      });

      // переход во вкладку "Мультиязычность"
      $('.admin-center').on('click', '.section-settings #tab-language', function() {    
        settings.openTab($(this).attr('id'));
      });

      // переход во вкладку "API"
      $('.admin-center').on('click', '.section-settings #tab-api', function() {  
        settings.openTab($(this).attr('id'));
      });

      // переход во вкладку "оптовые скидки"
      $('.admin-center').on('click', '.section-settings #tab-wholesale', function() {   
        settings.openTab($(this).attr('id'));
      });

      // переход во вкладку "Склады"
      $('.admin-center').on('click', '.section-settings #tab-storage', function() {
        settings.openTab($(this).attr('id'));
      });
      
      // переход во вкладку "Система"
      $('.admin-center').on('click', '.section-settings #tab-system', function() {  
        includeJS(mgBaseDir + '/mg-core/script/admin/backup.js'); 
        settings.openTab($(this).attr('id'));
      });

      // открытие вкладки шаблона
      $('.admin-center').on('click', '.section-settings .openTemplate', function() {        
        $('.templateContainer').slideToggle();
        $('.landingContainer').slideUp();
        $('.resetContainer').slideUp();
      });


      // открытие обновления шаблонов
      $('.admin-center').on('click', '.section-settings .openReset', function() {        
        
        if ($('.resetContainer select').length) {
          $('.templateContainer').slideUp();
          $('.landingContainer').slideUp();
          $('.resetContainer').slideToggle();
        }
        else{
          admin.ajaxRequest({
            mguniqueurl: "action/mpGetResetSelect",
          },
          
          function(response) {
            if (response.status == 'success') {
              $('.resetContainer').html(response.data);
              $('.templateContainer').slideUp();
              $('.landingContainer').slideUp();
              $('.resetContainer').slideToggle();
            }
            else{
              admin.indication(response.status, 'Ошибка получения данных');
            }
          });
        }
      });

      $('.mg-admin-html').on('click', '.section-settings .resetContainer button', function() {
        if (!confirm('Все пользовательские изменения в шаблоне будут утрачены. Обновить?')) {return false;}
        admin.ajaxRequest({
          mguniqueurl:"action/mpUpdateTemplate",
          code: $('.section-settings .resetContainer select').val()
        },
        function(response) {
          if (response.status == 'success') {
            admin.indication(response.status, 'Шаблон обновлен');
            admin.refreshPanel();
          }
          else{
            admin.indication(response.status, 'При обновлении произошла ошибка');
          }
        });
      });

      // Выделить все страницы
      $('.admin-center').on('click', '.section-settings .check-all-page', function () {
        $('.section-settings #tab-userField-settings .main-table tbody input[name=property-check]').prop('checked', 'checked');
        $('.section-settings #tab-userField-settings .main-table tbody input[name=property-check]').val('true');
        $('.section-settings #tab-userField-settings .main-table tbody tr').addClass('selected');

        $(this).addClass('uncheck-all-page');
        $(this).removeClass('check-all-page');
      });
      // Снять выделение со всех  страниц.
      $('.admin-center').on('click', '.section-settings .uncheck-all-page', function () {
        $('.section-settings #tab-userField-settings .main-table tbody input[name=property-check]').prop('checked', false);
        $('.section-settings #tab-userField-settings .main-table tbody input[name=property-check]').val('false');
        $('.section-settings #tab-userField-settings .main-table tbody tr').removeClass('selected');
        
        $(this).addClass('check-all-page');
        $(this).removeClass('uncheck-all-page');
      });

      // переход во вкладку "шаблон"
      $('.admin-center').on('click', '.section-settings #tab-template', function() {        

        includeJS(mgBaseDir+'/mg-core/script/codemirror/lib/codemirror.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/mode/javascript/javascript.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/mode/xml/xml.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/mode/php/php.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/mode/css/css.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/mode/clike/clike.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/addon/search/search.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/addon/search/searchcursor.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/addon/search/jump-to-line.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/addon/search/match-highlighter.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/addon/search/matchesonscrollbar.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/addon/dialog/dialog.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/addon/scroll/annotatescrollbar.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/addon/scroll/scrollpastend.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/addon/scroll/simplescrollbars.js');


        // выбираем первый в наборе файл 
        $('#tab-template-settings .file-template').eq(0).click();    
        settings.openTab($(this).attr('id'));        
      });
      
       // Выбор картинки
      $('.admin-center').on('click', '.section-settings .browseImage', function() {
        admin.openUploader(null,null,'template');
      }); 
      
      // установка мета тегов для всех сущностей одной категории
      $('.admin-center').on('click', '.section-settings #setCatalogSeoForTemplate', function() {
        if($(this).attr('status') == 'active') {
          settings.setSeoForGroup('catalog');
        }
      }); 
      $('.admin-center').on('click', '.section-settings #setProductSeoForTemplate', function() {
        if($(this).attr('status') == 'active') {
          settings.setSeoForGroup('product');
        }
      }); 
      $('.admin-center').on('click', '.section-settings #setPageSeoForTemplate', function() {
        if($(this).attr('status') == 'active') {
          settings.setSeoForGroup('page');
        }
      });

      // установка активности для способов доставки
      $('.admin-center').on('click', '.section-settings #tab-deliveryMethod-settings .activity', function() {    
        $(this).find('a').toggleClass('active');
        if($(this).attr('status') == 1) $(this).attr('status', 0); else $(this).attr('status', 1);
        settings.changeActivityDP('delivery', $(this).attr('id'), $(this).find('a').hasClass('active'));
      });

      // установка активности для способов оплаты
      $('.admin-center').on('click', '.section-settings #tab-paymentMethod-settings .activity', function() {    
        $(this).find('a').toggleClass('active');
        if($(this).attr('status') == 1) $(this).attr('status', 0); else $(this).attr('status', 1);
        settings.changeActivityDP('payment', $(this).attr('id'), $(this).find('a').hasClass('active'));
      });

      // 
      $('.admin-center').on('keyup', '.section-settings .catalog-seo input, .section-settings .catalog-seo textarea', function() {
        $('.section-settings #setCatalogSeoForTemplate').attr('status','disable');
        $('.section-settings #setCatalogSeoForTemplate').css('color','red!important');
        $('.section-settings #setCatalogSeoForTemplate').text(lang.SEO_TEMPLATE_ALERT);
      });
      $('.admin-center').on('keyup', '.section-settings .product-seo input, .section-settings .product-seo textarea', function() {
        $('.section-settings #setProductSeoForTemplate').attr('status','disable');
        $('.section-settings #setProductSeoForTemplate').css('color','red!important');
        $('.section-settings #setProductSeoForTemplate').text(lang.SEO_TEMPLATE_ALERT);
      });
      $('.admin-center').on('keyup', '.section-settings .page-seo input, .section-settings .page-seo textarea', function() {
        $('.section-settings #setPageSeoForTemplate').attr('status','disable');
        $('.section-settings #setPageSeoForTemplate').css('color','red!important');
        $('.section-settings #setPageSeoForTemplate').text(lang.SEO_TEMPLATE_ALERT);
      });
      
      // сворачиваем все вкладки с настройками     
     
      // клик по заголовкам настроек в первой вкладке
      $('.admin-center').on('click', '.section-settings .group-property h3', function() {    
      
         if($(this).parent().hasClass("open")) {
             $(this).parent().removeClass("open");
             $(this).next().slideUp("fast");
         }
         else{
             $('.group-property .group-property-list').slideUp("fast");
             $('.group-property .group-property-list').parent().removeClass("open");
             $(this).next().slideDown("fast");
             $(this).parent().addClass("open");
         }
       });

      // клик по кнопкам отсутствующих файлов шаблона, копирует файл в папку шаблона
      $('.admin-center').on('click', '.section-settings .file-template-missing', function() {
        var path = $(this).data('path');
        var type = $(this).data('type');
        var file = $(this).data('file');
        var obj = $(this);

        admin.ajaxRequest({
          mguniqueurl: "action/copyTemplateFile",   
          path: path,
          type: type,       
          file: file,       
        },
        
        function(response) {
          if (response.status == 'success') {
            obj.removeClass('file-template-missing').addClass('file-template').addClass('tool-tip-bottom').click();
          }
          else{
            admin.indication(response.status, lang.TEMPLATE_COPY_FAIL);
          }
        });
      });

      // клик по кнопкам файлов шаблона, загружает  содержание файла с сервера
      $('.admin-center').on('click', '.section-settings .file-template', function() {
        $('.save-file-template').data('editfilename', $(this).data('path'));
        $('.file-template').removeClass('editing-file');
        $(this).addClass('editing-file');
        var path = $(this).data('path');
        var type = $('.template-tabs-menu').find('.is-active').find('a').attr('data-target');

        admin.ajaxRequest({
          mguniqueurl: "action/getTemplateFile",   
          path: path,
          type: type,       
        },
        
        function(response) {         
          $('.CodeMirror').remove();
          $('.save-file-template').hide();     
          // каждому файлу свою схему
          if(response.status!="error") { 
            var mode = "application/x-httpd-php";
            if(path.indexOf(".js") > 0) {
              mode = "text/javascript";
            }
            if(path.indexOf(".css") > 0) {
              mode = "text/css";
            }
            $('#codefile').val(response.data.filecontent);            
            codeEditor = CodeMirror.fromTextArea(document.getElementById("codefile"), {
              lineNumbers: true,           
              mode: mode,
              extraKeys: {"Ctrl-F": "findPersistent"},
              showMatchesOnScrollbar:true 
            }); 
                  
            $('.error-not-tpl').hide();
            $('.save-file-template').show();
          } else {
            $('.error-not-tpl').show();
          }

          var height = $($('.template-tabs-menu').find('.is-active a').data('target')).height();
          if(height < 550) {
            height = 550;
          }
          $('.CodeMirror').height(height-5);
          $('.CodeMirror-scroll').scrollTop(2);
        }
        
        );
      });      
      
      $('.admin-center').on('click', '.section-settings #tab-currency', function() {
        includeJS(mgBaseDir+'/mg-core/script/admin/currency.js');
        settings.openTab($(this).attr('id'));    
      });
      
      $('.admin-center').on('click', '.section-settings #interface', function() {
        settings.openTab($(this).attr('id'));
      });

      $('.admin-center').on('click', '.section-settings #tab-userField', function() {
        settings.openTab($(this).attr('id'));
      });
      
      // при выборе категории переформировать талицу характеристик
      $('.admin-center').on('change', '.section-settings #tab-userField-settings select[name=cat_id]', function() {      
        var cat_id = $(this).val(); 
        cookie("userPropertyType", $('#tab-userField-settings form[name="filter"] select[name="type"]').val());
        userProperty.print(cat_id, true, 0);
      });
      // при выборе страницы в таблице характеристик
      $('body').on('click', '.section-settings #tab-userField-settings .propLinkPage', function() {
        var page = admin.getIdByPrefixClass($(this), 'page'); 
        var cat_id = $('.section-settings #tab-userField-settings select[name=cat_id]').val();
        cookie("userPropertyCat", cat_id);
        cookie("userPropertyPage", page);
        userProperty.print(cat_id, true, page);
      });
      // при выборе страницы в таблице характеристик
      $('body').on('click', '.section-settings #tab-userField-settings .filter-now', function() { 
        var cat_id = $('.section-settings #tab-userField-settings select[name=cat_id]').val();
        cookie("userPropertyType", $('#tab-userField-settings form[name="filter"] select[name="type"]').val());
        userProperty.print(cat_id, true, true);        
      });
      
      $('.admin-center').on('click', '.section-settings #tab-deliveryMethod', function() {
        settings.openTab($(this).attr('id'));
        // settings.getDeliveryArray();
        settings.updataTabs();
        admin.sortable('.deliveryMethod-tbody','delivery');
      });

      $('.admin-center').on('click', '.section-settings #tab-paymentMethod', function() {
        settings.openTab($(this).attr('id'));
        // settings.getPaymentTable();
        settings.updataTabs();
        admin.sortable('.paymentMethod-tbody','payment');
      });
      
      $('.admin-center').on('click', '.section-settings #tab-SEOMethod', function() {        
        settings.openTab($(this).attr('id'));
      });
      
      $('.admin-center').on('click', '.section-settings #tab-1C', function() {        
        settings.openTab($(this).attr('id'));
      });

      $('.admin-center').on('change', '.section-settings  input[name="staticMenu"]', function() {
        admin.fixedMenu($(this).val()=='false'?'true':'false');
      });
      
      $('.admin-center').on('click', '.section-settings .edit-key', function() {
        $('.section-settings input[name="licenceKey"]').fadeIn();
        $('.section-settings .save-settings-system').fadeIn();        
        $(this).hide();       
      });

      // для клика по чекбоксу - закрытия сайта от посетителей, особый обрабочик
      $('.admin-center').on('click', '.section-settings .downtime-check', function() {        
        var tabName = $(this).parents('.main-settings-container').attr('id');      
        
        var obj ={downtime: "false", consentData: "false"};
        if($('[name=downtime]').prop('checked')) {
          obj.downtime = "true";
        }
        if($('[name=consentData]').prop('checked')) {
          obj.consentData = "true";
        }
           
        admin.ajaxRequest({
          mguniqueurl: "action/editSettings",
          options: obj
        },
        function(response) {
          admin.indication(response.status, response.msg);        
          $('.tabs-content').animate({opacity: "hide"}, 1000);
          $('.tabs-content').animate({opacity: "show"}, "slow");
          admin.refreshPanel();
        }
       );
      });
      
      $('.admin-center').on('change', '.section-settings  select[name="cacheMode"]', function() {
        $('.memcache-conection').hide();	
        $('input[name="cacheHost"]').parent().parent().hide();
        $('input[name="cachePort"]').parent().parent().hide();
        $('input[name="cachePrefix"]').parent().parent().hide();
        if($(this).val()=="MEMCACHE") {
          $('.memcache-conection').show();	
          $('input[name="cacheHost"]').parent().parent().show();	
          $('input[name="cachePort"]').parent().parent().show();		
          $('input[name="cachePrefix"]').parent().parent().show();
        }
      });
      
       $('.admin-center').on('change', '.section-settings input[name=cacheCssJs]', function() {        
        if($(this).prop('checked')) {
          $('.create-images-for-css-cache').show(); 
          $('.warning-create-images').show();
        } else {
          $('.create-images-for-css-cache').hide();	
          $('.warning-create-images').hide();
        }
      });
      
      $('.admin-center').on('change', '.section-settings select[name="templateName"]', function() {
        settings.drawColorShemes($(this).find('option:selected').data('schemes'));
        
        if ($(this).val() == 'moguta-standard' || $(this).val() == $(this).data('current')) {
          $('.section-settings .wrapp-templ .dropTemplate').hide();
        }
        else{
          $('.section-settings .wrapp-templ .dropTemplate').show();
        }
      });

      $('.admin-center').on('click', '.section-settings .wrapp-templ .dropTemplate', function() {
        if (!confirm(lang.dropTemplate)) {return false;}
        admin.ajaxRequest({
          mguniqueurl: "action/dropTemplate",
          template: $('.section-settings .wrapp-templ select[name="templateName"]').val()
        },
        function(response) {
          admin.indication(response.status, response.msg);
          admin.refreshPanel();
        });
      });


      $('.section-settings  select[name="templateName"]').trigger('change');

      $('.admin-center').on('click', '.section-settings #tab-shop-settings .addShopPhone', function() {
        if ($('#tab-shop-settings [name=shopPhone]').length > 1) {
          $('#tab-shop-settings [name=shopPhone]:first').clone().insertAfter($('#tab-shop-settings [name=shopPhone]:last'));
        }
        else{
          $('#tab-shop-settings [name=shopPhone]:first').clone().insertAfter($('#tab-shop-settings .addShopPhone'));
        }
        $('#tab-shop-settings [name=shopPhone]:last').val('');
      });
	 
      $('.admin-center').on('click', '.section-settings .save-settings', function() {
        var tabName = $(this).parents('.main-settings-container').attr('id'); 
        var shopPhone = '';
        if (tabName == 'tab-shop-settings') {
          if (typeof codeEditorWidgetCode !== "undefined" && null !== codeEditorWidgetCode) {
            $('#widgetCode').val(codeEditorWidgetCode.getValue());
          }
          var prefix = $('.section-settings #tab-shop-settings input[name=cachePrefix]').val();
          if((!(/^[-0-9a-zA-Z]+$/.test(prefix)) || prefix.length > 5) && prefix != '') {
            $('.section-settings #tab-shop-settings input[name=cachePrefix]').addClass('error-input');
            admin.indication('error', lang.ERROR_CACHE_PREFIX);
            return false;
          }
          $('#tab-shop-settings [name=shopPhone]').each(function(index,element) {
            if (shopPhone == '') {
              shopPhone = $(this).val();
            }
            else{
              if ($(this).val() != '') {
                shopPhone = shopPhone + ', ' + $(this).val();
              }
            }
          });
        }

        var settingsArr = settings.getAllSetting(tabName);

        if (shopPhone != '') {
          settingsArr.shopPhone = shopPhone;
        }

        admin.ajaxRequest({
          mguniqueurl: "action/editSettings",
          options: settingsArr
        },
        function(response) {
          admin.indication(response.status, response.msg);
          settings.checkValidKey();
          $('.tabs-content').animate({opacity: "hide"}, 1000);
          $('.tabs-content').animate({opacity: "show"}, "slow");
          if ($('.admin-center .licenceKey[name=licenceKey]').is(':visible')) {
            window.location.reload(true);
          }
          else{
            admin.refreshPanel();
          }
        }
       );
      });
     
        // Выбор цветовой схемы шаблона
      $('.admin-center').on('click', '.section-settings .color-scheme', function() { 
        $(this).parents('ul').find('.color-scheme').removeClass('active');
        $(this).addClass('active');
      });

      
      // установка сединения с memcache
      $('.admin-center').on('click', '.section-settings .memcache-conection', function() {           
	     admin.ajaxRequest({
          mguniqueurl: "action/testMemcacheConection", 
          host: $('input[name=cacheHost]').val(),
          port: $('input[name=cachePort]').val()
        },
        function(response) {
          admin.indication(response.status, response.msg);        
        });
      });
      
      // Выбор картинки для логотипа сайта
      $('.admin-center').on('click', '.section-settings  .browseImageLogo', function() {
        admin.openUploader('settings.getFile');  
        $('.logo-img').show();     
      });
      // Выбор картинки для фона сайта
      $('.admin-center').on('click', '.section-settings  .browseBackgroundSite', function() {
        admin.openUploader('settings.getBackground'); 
        $('.background-img').show();      
      });
       // Выбор фавиконки для  сайта
      $('body').on('change', '.section-settings input[name="favicon"]', function() {
        var img_container = $(this).parents('.upload-img-block');
                
        if($(this).val()) {          
          settings.addFavicon(img_container);
        }
      });

      // Обработчик для загрузки нового шаблона
      $('body').on('change', '#addTempl', function() {
        settings.addNewTemplate('newTemplateForm');
      });


      // сохранение файла шаблона
      $('.admin-center').on('click', '.section-settings .save-file-template', function() {
        var filename = $(this).data('editfilename');
        content = codeEditor.getValue();
        var type = $('.template-tabs-menu').find('.is-active').find('a').attr('data-target');
        
        admin.ajaxRequest({
          mguniqueurl: "action/saveTemplateFile", 
          content: content,
          filename: filename,
          type: type,
        },
        function(response) {
          admin.indication(response.status, response.msg);        
        });
      });    

      //Обработка  нажатия кнопки проверить версию
      $('.admin-center').on('click', '.clearLastUpdate', function() {         
        admin.ajaxRequest({
          mguniqueurl: "action/clearLastUpdate",         
        },
        function(response) {
          admin.indication(response.status, response.msg);
          if(response.status == 'success') {
            window.location = mgBaseDir+"/mg-admin/";
          }
        });
      });

      
    //Обработка  нажатия кнопки Приступить к обновлению
      $('.admin-center').on('click', '.update-now', function() {
       $(".loading-update-step-1").show();       
       $(".step-1-info").hide();
   
       var buttonDownload = $(this);
       buttonDownload.hide();
        
       $('.start-update').hide();
       $(".step-process-info").show();
       $(".step-process-info").text(lang.WHAITING_UPDATE);
       $('.step-eror-info').hide();
        var version = $("#lVer").text();
        
        admin.ajaxRequest({
          mguniqueurl: "action/preDownload",
          version: version
        },
        
        function(response) {
   
         // response.status = "success";
          $(".loading-update-step-1").hide();
          $(".step-process-info").hide();     
          $(".step-1-info").show();
          
          if('error'==response.status) {
            admin.indication(response.status, response.msg);            
    
            $('.step-eror-info').show();
            $('.step-eror-info').text(response.msg);
            buttonDownload.show();  
            $('.start-update').show();
            
          } else {
              
            admin.indication(response.status, response.msg);
            
            $('.step-update-li-1').addClass('current');
            $('.step-update-li-1').addClass('completed');
            $('.step-update-li-2').removeClass('current');              
            $('.update-archive').show();           
            $("#lVer").html(version);
            $(".step-block .step1").hide();
            $(".step-block .step2").show();
            
          }  
          admin.initToolTip();
        });
          
      });

      //Обработка  нажатия кнопки Установить обновление
      $('.admin-center').on('click', '.update-archive', function() {
 
       var version = $("#lVer").text();
       $(".loading-update-step-2").show();
       $(".step-2-info").hide();
       var buttonArchive = $(this);
       buttonArchive.hide();  
       $(".step-process-info").show();       
       $(".step-process-info").text(lang.CHANGES_IN_PROGRESS);
    
        admin.ajaxRequest({
          mguniqueurl: "action/postDownload",
          version: version
        },
        function(response) {
          admin.indication(response.status, response.msg);   
          $(".loading-update-step-2").hide();
           if('error'==response.status) {
            admin.indication(response.status, response.msg);            
            $('.error-update').remove();           
            $('.step-eror-info').html(response.msg+ '  <a href="'+admin.SITE+'/mg-admin'+'">'+lang.START_UPDATE+'</a>');     
            
          } else {              
            admin.indication(response.status, response.msg);            
            $('.step-update-li-2').addClass('current');
            // $('.step-update-li-2').addClass('completed');
            $('.step-update-li-3').removeClass('current');  
            $(".step-info").hide();     
            $(".step-process-info").text(lang.ENDING_UPDATE);
            $(".loading-update-step-2").show();
            setTimeout(function() { 
              window.location = mgBaseDir+"/mg-admin/"; 
              // $('.clearLastUpdate').click();
            }, 3000);
          }  
        });
        return false;
      }); 

      // смена языка
      $('.admin-center').on('change','.section-settings #tab-deliveryMethod-settings .select-lang', function() {
        id = $('.section-settings #tab-deliveryMethod-settings .save-button').attr('id');
        if(id < 1) {
          type = 'add';
        } else {
          type = 'edit';
        }
        settings.openDeliveryModalWindow(type, id);
      });
      
      // Вызов модального окна при нажатии на кнопку добавления способа доставки.
      $('.admin-center').on('click', '#tab-deliveryMethod-settings .add-new-button', function() {
        settings.openDeliveryModalWindow('add');
      });
          
      // Вызов модального окна при нажатии на кнопку изменения способа доставки.
      $('.admin-center').on('click', '#tab-deliveryMethod-settings .edit-row', function() {
        settings.openDeliveryModalWindow('edit', $(this).attr('id'));
      });
      
      // Сохранение при нажатии на кнопку сохранить в модальном окне способа доставки.
      $('body').on('click', '#tab-deliveryMethod-settings .save-button', function() {
        settings.saveDeliveryMethod($(this).attr('id'));
      });
      
      // Удаление способа доставки.
      $('.admin-center').on('click', '#tab-deliveryMethod-settings .delete-row', function() {
        settings.deleteDelivery($(this).attr('id'));
      });
            
      // Вызов модального окна при нажатии на кнопку изменения способа оплаты.
      $('.admin-center').on('click', '#tab-paymentMethod-settings .edit-row', function() {
        admin.ajaxRequest({
          mguniqueurl: "action/loadPayment",
          id: $(this).parents('tr').data('id'),
          lang: $('#tab-paymentMethod-settings .select-lang').val()
        },
        function (response) {
          settings.openPaymentModalWindow(response.data);
        });
      });

      $('body').on('change', '#tab-paymentMethod-settings .select-lang', function() {
        admin.ajaxRequest({
          mguniqueurl: "action/loadPayment",
          id: $('#tab-paymentMethod-settings .save-button').attr('id'),
          lang: $('#tab-paymentMethod-settings .select-lang').val()
        },
        function (response) {
          settings.openPaymentModalWindow(response.data);
        });
      });

      $('body').on('change', '#tab-shop-settings .watermarkform', function() {        
        settings.addWatermark();
      });     
      
      // Сохранение при нажатии на кнопку сохранить в модальном окне способа оплаты
      $('body').on('click', '#tab-paymentMethod-settings .save-button', function() {
        settings.savePaymentMethod($(this).attr('id'));
      });

      // удаление фонового рисунка сайта
      $('.admin-center').on('click', '.section-settings .remove-added-background', function() {
        $(this).hide();
        $('.section-settings input[name="backgroundSite"]').val('');    
        $('.section-settings .background-img img').removeAttr().hide();  
        $('.section-settings .background-img').hide();  
      });
       // удаление логотипа сайта
      $('.admin-center').on('click', '.section-settings .remove-added-logo', function() {
        $(this).hide();
        $('.section-settings input[name="shopLogo"]').val('');    
        $('.section-settings .logo-img img').removeAttr().hide();  
        $('.section-settings .logo-img').hide();  
      });
       //Клик по ссылке для установки скидки/наценки способа оплаты
      $('body').on('click', '.section-settings #add-paymentMethod-wrapper .discount-setup-rate', function() {
        $(this).hide();
        $('.discount-rate-control').show();
      }); 
       //Клик по отмене скидки/наценки 
      $('body').on('click', '#add-paymentMethod-wrapper .cancel-rate', function() {
        $('.discount-setup-rate').show();
        $('.discount-rate-control').hide();
        $('.discount-rate-control input[name=rate]').val(0);        
      });           
       // Клик по кнопке для смены скидки/наценки
      $('body').on('click', '#add-paymentMethod-wrapper .discount-change-rate', function() {
        $('.select-rate-block').show();
      }); 
      // Клик по кнопке для  отмены модалки смены скидки/наценки
      $('body').on('click', '#add-paymentMethod-wrapper .cancel-rate-dir', function() {
        $('.select-rate-block').hide();  
        if($('.rate-dir').text()=="+") {
          $('.select-rate-block select[name=change_rate_dir] option[value=up]').prop('selected','selected');
        }
        if($('.rate-dir').text()=="-") {
          $('.select-rate-block select[name=change_rate_dir] option[value=down]').prop('selected','selected');
        }        
      });        
      // Клик по кнопке для применения скидки/наценки
      $('body').on('click', '#add-paymentMethod-wrapper .apply-rate-dir', function() {
        $('.select-rate-block').hide();        
        if($('.select-rate-block select[name=change_rate_dir]').val()=='up') {
          settings.setupDirRate(1);    
        } else {
          settings.setupDirRate(-1);    
        }
      });   

      // Выводит модальное окно для добавления
      $('body').on('click', '#SEOMethod-settings .createSitemap', function() {    
        settings.createMap();
      });

      // проверка на ограничение количества выводимых товаров
      $('body').on('keyup', '.main-settings-list input[name=countRecomProduct],.main-settings-list input[name=countNewProduct],.main-settings-list input[name=countSaleProduct]', function() {    
        if ($(this).val()>30) {
          $(this).val(30);
          admin.indication('error',lang.MAX_SHOWN_PROD);
        }
      });
      // скрытие настроек параметров smtp если опция использования отключена
      $('.admin-center').on('change', '.section-settings  input[name="smtp"]', function() {        
        if($(this).val()=="true") {
          $('.section-settings input[name=smtpSsl]').parent().parent().parent().show();
          $('.section-settings input[name=smtpHost]').parent().parent().show();
          $('.section-settings input[name=smtpLogin]').parent().parent().show();
          $('.section-settings input[name=smtpPass]').parent().parent().show();
          $('.section-settings input[name=smtpPort]').parent().parent().show();
        } else {
          $('.section-settings input[name=smtpSsl]').parent().parent().parent().hide();
          $('.section-settings input[name=smtpHost]').parent().parent().hide();
          $('.section-settings input[name=smtpLogin]').parent().parent().hide();
          $('.section-settings input[name=smtpPass]').parent().parent().hide();
          $('.section-settings input[name=smtpPort]').parent().parent().hide();
        }	
      });
      // отправка тестового письма
      $('.admin-center').on('click', '.section-settings .email-conection', function() {           
	     admin.ajaxRequest({
          mguniqueurl: "action/testEmailSend", 
        },
        function(response) {
          admin.indication(response.status, response.msg);        
        });
      });
       // проверка префикса для мемкэш - длина не более 5 символов, и отсутствие спецсимволов и русских букв
       $('.admin-center').on('keyup', '.section-settings #tab-shop-settings input[name=cachePrefix]', function() {
           // наименование не должно иметь специальных символов.
          if((!(/^[-0-9a-zA-Z]+$/.test($(this).val())) || $(this).val().length > 5) && $(this).val() != '') {
            $('.section-settings #tab-shop-settings input[name=cachePrefix]').addClass('error-input');
            admin.indication('error', lang.ERROR_CACHE_PREFIX);
          } else {
            $('.section-settings #tab-shop-settings input[name=cachePrefix]').removeClass('error-input');
          }
       });
       
       //Показываем поле ввода времени жизни сесии если выбрано сохранение в БД.
       //Иначе скрываем
       $('.admin-center').on('change', '.section-settings #tab-shop-settings input[name=sessionToDB]', function() {
         if ($(this).val() == 'true') {
           $('.section-settings #tab-shop-settings input[name=sessionLifeTime]').parent().parent().parent().show();
         } else {
           $('.section-settings #tab-shop-settings input[name=sessionLifeTime]').parent().parent().parent().hide();
         }
       });

       $('.admin-center').on('change', '.section-settings #add-deliveryMethod-wrapper input[name=useIntervals]', function() {
         if ($(this).prop('checked')) {
           $('.section-settings #add-deliveryMethod-wrapper .deliveryIntervals').show();
         } 
         else {
           $('.section-settings #add-deliveryMethod-wrapper .deliveryIntervals').hide();
         }
       });

       $('.admin-center').on('click', '.section-settings #add-deliveryMethod-wrapper .useWeight', function() {
        if ($(this).data('active') == 'fals') {
          $(this).data('active','tru').text(lang.DELIVERY_WEIGHT_INACTIVE);
          $('.section-settings #add-deliveryMethod-wrapper .deliveryWeight').show();
        }
        else{
          $(this).data('active','fals').text(lang.DELIVERY_WEIGHT_ACTIVE);
          $('.section-settings #add-deliveryMethod-wrapper .deliveryWeight').hide();
        }
       });

       $('.admin-center').on('click', '.section-settings #add-deliveryMethod-wrapper .addDeliveryWeight', function() {
         settings.addDeliveryWeightRow();
       });

       $('.admin-center').on('click', '.section-settings #add-deliveryMethod-wrapper .deliveryWeight .accordion-title', function() {
         if (!$('.section-settings #add-deliveryMethod-wrapper .deliveryWeight [name=deliveryWeight]').length) {
           settings.addDeliveryWeightRow();
         }
       });

       $('.admin-center').on('click', '.section-settings #add-deliveryMethod-wrapper .addDeliveryInterval', function() {
         settings.addDeliveryIntervalRow();
       });

       $('.admin-center').on('click', '.section-settings #add-deliveryMethod-wrapper .deliveryIntervals .accordion-title', function() {
         if (!$('.section-settings #add-deliveryMethod-wrapper .deliveryIntervals [name=deliveryInterval]').length) {
           settings.addDeliveryIntervalRow();
         }
       });

       $('.admin-center').on('click', '.createNewPaymentMethod', function() {
        settings.createPaymentModalOpen();
       });

       $('body').on('click', '.deletePayment', function() {
        if(!confirm('Удалить способ оплаты?')) return false;
        settings.deletePayment($(this).data('id'));
       });
    },

    deletePayment: function(id) {
      $.ajax({
        type: "POST",
        url: mgBaseDir + "/ajax",
        data: {
          mguniqueurl: "action/deletePayment",
          id: id,
        },
        cache: false,
        // async: false,
        dataType: "json",
        success: function (response) {
          admin.refreshPanel();
        }
      });
    },

    addDeliveryWeightRow: function() {
      var html = '<div class="row weights">\
          <div class="small-12 medium-6 columns">\
            <input placeholder="'+lang.DELIVERY_WEIGHT_W+'" type="text" name="deliveryWeight">\
          </div>\
          <div class="small-12 medium-6 columns">\
            <input placeholder="'+lang.DELIVERY_WEIGHT_P+'" type="text" name="deliveryWeightPrice">\
          </div>\
        </div>';
      $(html).insertBefore($('#add-deliveryMethod-wrapper .addDeliveryWeight').closest('.row'));
    },
    addDeliveryIntervalRow: function() {
      var html = '<div class="row intervals">\
          <div class="small-12 medium-12 columns">\
            <input placeholder="'+lang.DELIVERY_INTERVAL+'" type="text" name="deliveryInterval">\
          </div>\
        </div>';
      $(html).insertBefore($('#add-deliveryMethod-wrapper .addDeliveryInterval').closest('.row'));
    },


    openSystemTab: function() {
      $('#tab-system').click();
    },

    setupDirRate: function(rate) {         
      if(rate>=0) {
        $('#add-paymentMethod-wrapper select[name=change_rate_dir] option[value=up]').prop('selected','selected');
        $('#add-paymentMethod-wrapper .discount-rate').removeClass('color-down').addClass('color-up');
        $('.rate-dir').text('+');
        $('.rate-dir-name').text(lang.DISCOUNT_UP);
        $('.discount-rate-control input[name=rate]').val(Math.abs($('.discount-rate-control input[name=rate]').val()));
      } else {
        $('#add-paymentMethod-wrapper select[name=change_rate_dir] option[value=down]').prop('selected','selected');  
        $('.rate-dir-name').text(lang.DISCOUNT_DOWN);
        $('#add-paymentMethod-wrapper .discount-rate').removeClass('color-up').addClass('color-down');
        $('.rate-dir').text('-');
        $('.discount-rate-control input[name=rate]').val(Math.abs($('.discount-rate-control input[name=rate]').val()));
      }
    }, 
    /**
     * Закрывает все табы
    */
    closeAllTab: function() {
     $('.tabs-list li').removeClass('ui-state-active');
     $('.main-settings-container').css('display', 'none');
    },

    /**
     * Открывает все табы
     */
    openTab: function(tab) {
      cookie('setting-active-tab','#'+tab);
      if (tab != 'tab-userField') {
        cookie("userPropertyPage", '');
        cookie("userPropertyCat", '');
        cookie("userPropertyType", '');
      }
      // $('#'+tab).click();
    },
            
     /**
     * отложенное открытие таба, применяется при перезагрузке
     */
    calbackOpenTab: function() {   
      $(cookie('setting-active-tab')).click();
      if (cookie('setting-active-tab') == '#tab-userField') {
        var page = cookie("userPropertyPage");
        var cat = cookie("userPropertyCat");
        if (cat && page) {
          var type = cookie("userPropertyType");
          userProperty.print(cat, true, page);
          $('.tabs-content-settings .filter-form select[name=cat_id]').val(cat); 
          if (type) {$('.tabs-content-settings .filter-form select[name=type]').val(type);}
        }
      }
    },

    /**
     * Получает значение всех настроек в выбраном табе
     */
    getAllSetting: function(tab) {
      //собираем из таблицы все инпуты с данными
      var obj = admin.createObject('#'+tab+' .option');
      
      if(tab == "tab-shop-settings" ) {
        //теперь присваиваем текстовое значение объекту
        obj.shopName = $('input[name=shopName]').val();
        // obj.colorScheme = $('.color-scheme.active').data('scheme');
        // obj.colorSchemeLanding = $('.color-scheme-landing.active').data('scheme');
      }  
      if(tab == "tab-template-settings" ) {
        //теперь присваиваем текстовое значение объекту
        obj.colorScheme = $('.color-scheme.active').data('scheme');

      }     
      if(tab == "SEOMethod-settings" ) {
        //теперь присваиваем текстовое значение объекту
        obj.excludeUrl = admin.htmlspecialchars($('textarea[name=excludeUrl]').val().replace(/\n/g,"\n;"));
        obj.robots = $('textarea[name=robots]').val();
      }  
     
      return obj;
    },

    changeActivityDP: function(tab, id, status) {
      if(status) status = 1; else status = 0;
      admin.ajaxRequest({
        mguniqueurl: "action/changeActivityDP",
        tab: tab,
        id: id,
        status: status
      },
      function(response) {
      });
    },

    checkValidKey:function() {
      if ($('.licenceKey').val()) {
        if(32 == $('.licenceKey').val().length) {
          $('.update-now').removeClass('opacity');
          $('.update-now').prop('disabled', false);
          $('.error-key').hide();
        } else {
          $('.update-now').addClass('opacity');
          $('.update-now').prop('disabled', true);
          $('.error-key').show();
        }
      }
    },

    /**
     * Открывает модальное окно способа доставки.
     * type - тип окна, либо для создания нового, либо для редактирования старого.
     */
    openDeliveryModalWindow: function(type, id) {
      settings.clearFileds();   
      switch (type) {
        case 'edit': {          
          $('.deliveryMethod-table-wrapper .delivery-table-icon').text(lang.TITLE_EDIT_DELIVERY);
          $('#add-deliveryMethod-wrapper .save-button').attr("id", id);
          var paymentMethod = $.parseJSON($('tr[id=delivery_'+id+'] td#paymentHideMethod').text());
          weights = '';
          if ($('tr[id=delivery_'+id+'] td#paymentHideMethod').data('weight')) {
            weights = $.parseJSON($('tr[id=delivery_'+id+'] td#paymentHideMethod').data('weight'));
          }
          intervals = '';
          if ($('tr[id=delivery_'+id+'] td#paymentHideMethod').data('interval')) {
            intervals = $('tr[id=delivery_'+id+'] td#paymentHideMethod').data('interval');
          }
          $('input[name=deliveryName]').val($('tr[id=delivery_'+id+'] td#deliveryName').text());
          $('input[name=deliveryCost]').val($('tr[id=delivery_'+id+'] td#deliveryCost span.costValue').text());
          $('input[name=deliveryDescription]').val($('tr[id=delivery_'+id+'] td#deliveryDescription').text());
          $('input[name=free]').val($('tr[id=delivery_'+id+'] td.free .costFree').text());

          if (weights) {
            $('.section-settings #add-deliveryMethod-wrapper .useWeight').click();
            for (var i = 0; i < weights.length; i++) {
              settings.addDeliveryWeightRow();
              $('.section-settings #add-deliveryMethod-wrapper input[name=deliveryWeight]:last').val(weights[i].w);
              $('.section-settings #add-deliveryMethod-wrapper input[name=deliveryWeightPrice]:last').val(weights[i].p);
            }
          }

          if (intervals) {
            if (!$.isArray(intervals)) {
              intervals = intervals.replace('["',"").replace('"]',"").split('","');
            }
            $('.section-settings #add-deliveryMethod-wrapper [name=useIntervals]').click();
            for (var i = 0; i < intervals.length; i++) {
              if (intervals[i] != '') {
                settings.addDeliveryIntervalRow();
                $('.section-settings #add-deliveryMethod-wrapper input[name=deliveryInterval]:last').val(admin.htmlspecialchars_decode(intervals[i]));
              }
            }
          }
          
          if(1 == $('tr[id=delivery_'+id+'] td .activity').attr('status')) {
            $('input[name=deliveryActivity]').prop('checked', true);
          }
          if(1 == $('tr[id=delivery_'+id+'] td .activity').data('delivery-date')) {
            $('input[name=deliveryDate]').prop('checked', true);
          }
          if($('tr[id=delivery_'+id+'] td .activity').attr('data-delivery-ymarket') === '1') {
            $('input[name=deliveryYmarket]').prop('checked', true);
          }
          if('' != $('tr[id=delivery_'+id+'] td#paymentHideMethod').data('address_parts')) {
            $('input[name=useAddressParts]').prop('checked', true);
          }         
          //выбор способов оплаты применительно к данному способу доставки
          $.each(paymentMethod, function(paymentId, active) {
            if(1 == active) {
              $('#add-deliveryMethod-wrapper #paymentCheckbox input[name='+paymentId+']').prop('checked', true);
            } else {
              $('#add-deliveryMethod-wrapper #paymentCheckbox input[name='+paymentId+']').prop('checked', false);
            }
          });
          break;
        }
        case 'add': {
          $('.deliveryMethod-table-wrapper .delivery-table-icon').text(lang.TITLE_NEW_DELIVERY);
          break;
        }
        default: {
          user.clearFileds();
          break;
        }
      }

      // Вызов модального окна.
      admin.openModal($('#add-deliveryMethod-wrapper'));


    },

    createPaymentModalOpen: function() {
      $('#add-paymentMethod-wrapper span#paymentName').html('<span class="default_icon">'+'<input class="name-payment" name="name" type="text" value="">'+'</span>');
      settings.clearFileds();
      $('#add-paymentMethod-wrapper .payment-table-icon').text(lang.TITLE_NEW_PAYMENT);
      $('#paymentParam').replaceWith('<div id="paymentParam"><div class="row"><div class="small-5 columns"><label class="middle">Примечание</label></div><div class="small-7 columns"><input type="text" name="Примечание" class="product-name-input" value=""></div></div><div class="row"><div class="small-5 columns"><label class="middle">Способ оплаты доступен для:</label></div><div class="small-7 columns"><select class="medium permission"><option value="all">всех</option><option value="fiz">физических лиц</option><option value="yur">юридических лиц</option></select></div></div></div>');
      // Вызов модального окна.
      admin.openModal('#add-paymentMethod-wrapper');
    },

    /**
     * Открывает модальное окно способа оплаты.
     */
    openPaymentModalWindow: function(data) {  
     var paramArray = JSON.parse($('tr[id=payment_'+data.id+'] td#paramHideArray').html());     
      //проверка ниличия сопособов доставки для данного метода      
      if('' != $('tr[id=payment_'+data.id+'] td#deliveryHideMethod').text()) {
        var deliveryMethod = $.parseJSON($('tr[id=payment_'+data.id+'] td#deliveryHideMethod').text());
      }

      settings.clearFileds();
      $('#add-paymentMethod-wrapper .payment-table-icon').text(lang.TITLE_EDIT_PAYMENT);
      $('#add-paymentMethod-wrapper .save-button').attr("id", data.id);
      //подстановка классов иконок
      switch (data.id) {
        case "1":
          var iconClass = 'wm_icon';
          break;
        case "2":
          var iconClass = 'ym_icon';
          break;
        case "5":
          var iconClass = 'robo_icon';
          break;
        case "6":
          var iconClass = 'qiwi_icon';
          break;
        case "8":
          var iconClass = 'sci_icon';
          break;
        case "9":
          var iconClass = 'payanyway_icon';
          break;
        case "10":
          var iconClass = 'paymenmaster_icon';
          break;
        case "11":
          var iconClass = 'alfabank_icon';
          break;      
        default:
          var iconClass = 'default_icon';
      }
      $('#add-paymentMethod-wrapper span#paymentName').html('<span class="'+iconClass+'">'+'<input class="name-payment" name="name" type="text" value="'+data.name+'">'+'</span>');
      
      if('' != $('tr[id=payment_'+data.id+'] td#urlArray').text()) {
        var urlArray = $.parseJSON($('tr[id=payment_'+data.id+'] td#urlArray').text());
        var urlParam = '<div class="custom-text links-text" style="margin-bottom:7px;"><strong>'+lang.LINKS_SERVICE+''+$('tr[id=payment_'+data.id+'] td#paymentName').text()+':</strong></div>';
        var k=1;
        $.each(urlArray, function(name, val) {
          if(k==1) {urlParam += '<p class="alert-block warning">'}
          if(k==2) {urlParam += '<p class="alert-block success">'}
          if(k==3) {urlParam += '<p class="alert-block alert">'}
          if(k==4) {urlParam += '<p class="alert-block refund">'}
          urlParam += '<span>'+name+'</span>\
                      '+admin.SITE+val+'\
                    </p>';
          k++;
        });
        $('#add-paymentMethod-wrapper #urlParam').html(urlParam);
      }
      //создание списка изменения параметров для данного способа оплаты
      var input = '';
      var algorithm = new Array('md5', 'sha256', 'sha1');
        $('#add-paymentMethod-wrapper #paymentParam').html('');
      var yandexNDS = new Array('без НДС', '0%', '10%','18%');

      var comepayPayattributs = [];
        comepayPayattributs[1] ='Полная предварительная оплата до момента передачи предмета расчёта';
        comepayPayattributs[2] ='Частичная предварительная оплата до момента передачи предмета расчёта';
        comepayPayattributs[3] ='Аванс';
        comepayPayattributs[4] ='Полная оплата, в том числе с учётом аванса (предварительной оплаты) в момент передачи предмета расчёта';
        comepayPayattributs[5] ='Частичная оплата предмета расчёта в момент его передачи с последующей оплатой в кредит';
        comepayPayattributs[6] ='Передача предмета расчёта без его оплаты в момент его передачи с последующей оплатой в кредит';
        comepayPayattributs[7] ='Оплата предмета расчёта после его передачи с оплатой в кредит (оплата кредита). Этот признак должен быть единственным в документе и документ с этим признаком может содержать только одну строку';

      var comepayVats = [];
        comepayVats[1] = 'НДС не облагается';
        comepayVats[2] = 'НДС 10%';
        comepayVats[3] = 'НДС 18%';

      var cloudpaymentsTS = {
        'ts_0': 'Общая система налогообложения',
        'ts_1': 'Упрощенная система налогообложения (Доход)',
        'ts_2': 'Упрощенная система налогообложения (Доход минус Расход)',
        'ts_3': 'Единый налог на вмененный доход',
        'ts_4': 'Единый сельскохозяйственный налог',
        'ts_5': 'Патентная система налогообложения',
      };

      var cloudpaymentsVat = {
        'vat_none': 'НДС не облагается',
        'vat_0': 'НДС 0%',
        'vat_10': 'НДС 10%',
        'vat_18': 'НДС 18%',
        'vat_110': 'Расчетный НДС 10/110',
        'vat_118': 'Расчетный НДС 18/118',
      };

      var cloudpaymentsLang = {
        'ru-RU': 'Русский (MSK)',
        'en-US': 'Английский (CET)',
        'lv': 'Латышский (CET)',
        'az': 'Азербайджанский (AZT)',
        'kk': 'Русский (ALMT)',
        'kk-KZ': 'Казахский (ALMT)',
        'uk': 'Украинский (EET)',
        'pl': 'Польский (CET)',
        'pt': 'Португальский (CET)'
      };

      var numberOfCheckbox = 0;
     
      $.each(paramArray, function(name, val) {  
        var inpType = "text";
        if(name.indexOf('ароль') + 1) {
          inpType = "password";
        }
        if(name.indexOf('екретн') + 1) {
          inpType = "password";
        }
        if(name.indexOf('од проверки ') + 1) {
          inpType = "password";
        }
        if(name.indexOf('естовый') + 1) {
           inpType = "checkbox";
        }
        if(name.indexOf('пользовать онлайн кас') + 1) {
           inpType = "checkbox";
        }

        /*COMEPAY*/
        if('Callback Password' === name) {
            inpType = "password";
        }

        if ('Разрешить печать чеков в ККТ' === name) {
            inpType = "checkbox";
        }

        if ('НДС на товары' === name || 'НДС на доставку' === name ) {
            var options = '';
            comepayVats.forEach(function(arr, i, e) {
                options += '<option value="'+i+'">'+arr+'</option>';
            });
            $('#add-paymentMethod-wrapper #paymentParam').append(
                '<div class="row">\
                  <div class="small-5 columns">\
                    <label class="middle">'+name+'</label>\
              </div>\
              <div class="small-7 columns">\
              <div class="select medium">\
                <select name="'+name+'">'+options+'</select>\
              </div>\
            </div>');
            val = admin.htmlspecialchars_decode(val);
            $('#add-paymentMethod-wrapper #paymentParam select[name="'+name+'"]').val(val);
            return;
        }

        if ('Признак способа расчёта' === name) {
            var options = '';
            comepayPayattributs.forEach(function(arr, i, e) {
                options += '<option value="'+i+'">'+arr+'</option>';
            });
            $('#add-paymentMethod-wrapper #paymentParam').append(
                '<div class="row">\
                  <div class="small-5 columns">\
                    <label class="middle">'+name+'</label>\
            </div>\
            <div class="small-7 columns">\
            <div class="select medium">\
              <select name="'+name+'">'+options+'</select>\
            </div>\
          </div>');
            val = admin.htmlspecialchars_decode(val);
            $('#add-paymentMethod-wrapper #paymentParam select[name="'+name+'"]').val(val);
            return;
        }
        /* END COMEPAY*/

        if(name.indexOf('С, включенный в це') + 1) {
          var options = '';
          yandexNDS.forEach(function(arr, i, e) {
            options += '<option value="'+arr+'">'+arr+'</option>';
          });
          $('#add-paymentMethod-wrapper #paymentParam').append(
            '<div class="row">\
              <div class="small-5 columns">\
                <label class="middle">'+name+'</label>\
              </div>\
              <div class="small-7 columns">\
              <div class="select medium">\
                <select name="'+name+'">'+options+'</select>\
              </div>\
            </div>');
          val = admin.htmlspecialchars_decode(val);
          $('#add-paymentMethod-wrapper #paymentParam select[name="'+name+'"]').val(val);
          return; 
        }
        if(name.indexOf(lang.CRIPT_METHOD) + 1) {
          var options = '<option value="0">'+lang.CHOOSE+':</option>';
          algorithm.forEach(function(arr, i, e) {
            options += '<option value="'+arr+'">'+arr+'</option>';
          });
          $('#add-paymentMethod-wrapper #paymentParam').append(
            '<div class="row">\
              <div class="small-5 columns">\
                <label class="middle">'+name+'</label>\
              </div>\
              <div class="small-7 columns">\
              <div class="select medium">\
                <select name="'+name+'">'+options+'</select>\
              </div>\
            </div>');
          val = admin.htmlspecialchars_decode(val);
          $('#add-paymentMethod-wrapper #paymentParam select[name="'+name+'"]').val(val);
          return; 
        }

        // cloudPayment
        if(name.indexOf('С, включенный в це') + 1) {
          var options = '';
          yandexNDS.forEach(function(arr, i, e) {
            options += '<option value="'+arr+'">'+arr+'</option>';
          });
          $('#add-paymentMethod-wrapper #paymentParam').append(
            '<div class="row">\
              <div class="small-5 columns">\
                <label class="middle">'+name+'</label>\
              </div>\
              <div class="small-7 columns">\
              <div class="select medium">\
                <select name="'+name+'">'+options+'</select>\
              </div>\
            </div>');
          val = admin.htmlspecialchars_decode(val);
          $('#add-paymentMethod-wrapper #paymentParam select[name="'+name+'"]').val(val);
          return; 
        }
        if(name.indexOf(lang.CRIPT_METHOD) + 1) {
          var options = '<option value="0">'+lang.CHOOSE+':</option>';
          algorithm.forEach(function(arr, i, e) {
            options += '<option value="'+arr+'">'+arr+'</option>';
          });
          $('#add-paymentMethod-wrapper #paymentParam').append(
            '<div class="row">\
              <div class="small-5 columns">\
                <label class="middle">'+name+'</label>\
              </div>\
              <div class="small-7 columns">\
              <div class="select medium">\
                <select name="'+name+'">'+options+'</select>\
              </div>\
            </div>');
          val = admin.htmlspecialchars_decode(val);
          $('#add-paymentMethod-wrapper #paymentParam select[name="'+name+'"]').val(val);
          return; 
        }
        if(name.indexOf('истема налогообложения') + 1) {
            var options = '';
            $.each(cloudpaymentsTS, function(key, val) {
                options += '<option value="'+key+'">'+val+'</option>';
            });
            $('#add-paymentMethod-wrapper #paymentParam').append(
                '<div class="row">\
                  <div class="small-5 columns">\
                    <label class="middle">'+name+'</label>\
            </div>\
            <div class="small-7 columns">\
            <div class="select medium">\
              <select name="'+name+'">'+options+'</select>\
            </div>\
          </div>');
            val = admin.htmlspecialchars_decode(val);
            $('#add-paymentMethod-wrapper #paymentParam select[name="'+name+'"]').val(val);
            return;
        }
        if(name.indexOf('тавка НДС') + 1) {
          var options = '';
          $.each(cloudpaymentsVat, function(key, val) {
            options += '<option value="'+key+'">'+val+'</option>';
          });
          // console.log(cloudpaymentsVat);
          // console.log(options);
          $('#add-paymentMethod-wrapper #paymentParam').append(
            '<div class="row">\
              <div class="small-5 columns">\
                <label class="middle">'+name+'</label>\
            </div>\
            <div class="small-7 columns">\
            <div class="select medium">\
              <select name="'+name+'">'+options+'</select>\
            </div>\
          </div>');
          val = admin.htmlspecialchars_decode(val);
          $('#add-paymentMethod-wrapper #paymentParam select[name="'+name+'"]').val(val);
          return;
        }
        if(name.indexOf('зык виджета') + 1) {
          var options = '';
          $.each(cloudpaymentsLang, function(key, val) {
            options += '<option value="'+key+'">'+val+'</option>';
          });
          $('#add-paymentMethod-wrapper #paymentParam').append(
            '<div class="row">\
              <div class="small-5 columns">\
                <label class="middle">'+name+'</label>\
            </div>\
            <div class="small-7 columns">\
            <div class="select medium">\
              <select name="'+name+'">'+options+'</select>\
            </div>\
          </div>');
          val = admin.htmlspecialchars_decode(val);
          $('#add-paymentMethod-wrapper #paymentParam select[name="'+name+'"]').val(val);
          return;
        }
        // cloudPayment end
        
        switch(inpType) {
          case 'checkbox':
            numberOfCheckbox++;
            input = '<div class="checkbox margin">\
                      <input id="cr' + numberOfCheckbox + '" type="checkbox" name="'+name+'">\
                      <label for="cr' + numberOfCheckbox + '"></label>\
                    </div>';
            break;
          default:
            input = '<input type="'+inpType+'" name="'+name+'" class="product-name-input" value="">';
            break;
        }
         
        $('#add-paymentMethod-wrapper #paymentParam').append(
          '<div class="row">\
            <div class="small-5 columns">\
              <label class="middle">'+name+'</label>\
            </div>\
            <div class="small-7 columns">\
              '+input+'\
            </div>\
          </div>'
        );
        val = admin.htmlspecialchars_decode(val);
        $('#add-paymentMethod-wrapper #paymentParam input[name="'+name+'"]').val(val);
        if (inpType=='checkbox'&&val=='true') {
          $(this).attr('checked', 'checked');
          $('#add-paymentMethod-wrapper #paymentParam input[name="'+name+'"]').attr('checked', 'checked');
        }
      });
      
      // вешаем текстовый редактор на поле в реквизитах
      $('textarea[class=product-name-input]').ckeditor();  
      //ниличие сопобов доставки для данного метода
      if(!$.isEmptyObject(deliveryMethod)) {
        //выбор способов доставки применительно к данному способу оплаты
        $.each(deliveryMethod, function(deliveryId, active) {
          if(1 == active) {
            $('#add-paymentMethod-wrapper #deliveryCheckbox input[name='+deliveryId+']').prop('checked', true);
          } else {
            $('#add-paymentMethod-wrapper #deliveryCheckbox input[name='+deliveryId+']').prop('checked', false);
          }
        });
      } else {
        // $('#add-paymentMethod-wrapper #deliveryArray').html(lang.NONE_DELIVERY);
      }
      //выбор активности данного способа оплаты
      if(1 == $('tr[id=payment_'+data.id+'] td .activity').attr('status')) {
        $('input[name=paymentActivity]').prop('checked', true);
      }
      
      var rate = $('tr[id=payment_'+data.id+'] td#paymentRate').text();      
      $('.discount-rate-control input[name=rate]').val(rate*100);
      if(rate != 0) {
        $('.discount-setup-rate').hide();
        $('.discount-rate-control').show();
        settings.setupDirRate(rate);  
      }

      $('#add-paymentMethod-wrapper #paymentParam').append(
        '<div class="row">\
          <div class="small-5 columns">\
            <label class="middle">'+lang.PAYMENT_PERMISSION+':</label>\
          </div>\
          <div class="small-7 columns">\
            <select class="medium permission">\
              <option value="all">'+lang.PAYMENT_ALL+'</option>\
              <option value="fiz">'+lang.PAYMENT_FIZ+'</option>\
              <option value="yur">'+lang.PAYMENT_YUR+'</option>\
            </select>\
          </div>\
        </div>'
      );

      $('#add-paymentMethod-wrapper .permission').val(data.permission);

      // Вызов модального окна.
      admin.openModal('#add-paymentMethod-wrapper');

    },

   /**
    * Чистит все поля модального окна
    */
    clearFileds:function() {
      $('.section-settings #add-deliveryMethod-wrapper input[name=deliveryWeight]').remove();
      $('.section-settings #add-deliveryMethod-wrapper input[name=deliveryWeightPrice]').remove();
      $('.section-settings #add-deliveryMethod-wrapper input[name=deliveryInterval]').remove();
      $('.section-settings #add-deliveryMethod-wrapper .useWeight').data('active','fals').text(lang.DELIVERY_WEIGHT_ACTIVE);
      $('.deliveryWeight').hide();
      $('.deliveryIntervals').hide();
      $('input').removeClass('error-input');
      $('input[name=deliveryName]').val('');
      $('input[name=deliveryCost]').val('');
      $('input[name=free]').val('');
      $('input[name=deliveryDescription]').val('');
      $('.section-settings #add-deliveryMethod-wrapper input[name=useAddressParts]').prop('checked', false);
      $('.section-settings #add-deliveryMethod-wrapper [name=useIntervals]').prop('checked', false);
      $('input[name=deliveryActivity]').prop('checked', false);
      $('input[name=deliveryDate]').prop('checked', false);
      $('input[name=deliveryYmarket]').prop('checked', false);
      $('input[name=paymentActivity]').prop('checked', false);
      $('.deliveryMethod').prop('checked', false);
      $('#add-paymentMethod-wrapper #urlParam').html('');
      $('.paymentMethod').prop('checked', false);
      $('.discount-setup-rate').show();
      $('.discount-rate-control input[name=rate]').val(0);
      $('.discount-rate-control').hide();
      $('#add-paymentMethod-wrapper .discount-rate').removeClass('color-down').addClass('color-up');
      $('.save-button').attr('id','');
      // Стираем все ошибки предыдущего окна если они были.
      $('.errorField').css('display','none');
    },
   /**
    * сохранение способа доставки
    */
    saveDeliveryMethod:function(id) {
      
      // Если поля не верно заполнены, то не отправляем запрос на сервер.
      if(!settings.validForm()) {
        return false;
      }
      
      $('.img-loader').show();
      var status="createDelivery";
      //обрабатываем доступные методы оплаты для данного метода доставки
      var paymentMethod='{';
      
      $('#paymentCheckbox input').each(function() {
        
        if($(this).prop('checked')) {
          paymentMethod += '"'+$(this).attr('name')+'":1,';
        } else {
          paymentMethod += '"'+$(this).attr('name')+'":0,';
        }
      });
      
      paymentMethod = paymentMethod.substr(0, paymentMethod.length-1); //удаляем последнюю запятую в конце списка
      paymentMethod +='}';
      
      if(id) {
        status="editDelivery";
      }
      
      var deliveryName = $('input[name=deliveryName]').val();
      var deliveryCost = $('input[name=deliveryCost]').val();
      var deliveryDescription = $('input[name=deliveryDescription]').val();
      var free = $('input[name=free]').val();
      var deliveryActivity = 0;
      var deliveryDate = 0;
      var useAddressParts = 0;
      var weights = [];
      var intervals = [];
      if($('input[name=deliveryActivity]').prop('checked')) {
        deliveryActivity = 1;
      }      
      if($('input[name=deliveryDate]').prop('checked')) {
        deliveryDate = 1;
      } 
      if($('input[name=deliveryYmarket]').prop('checked')) {
        deliveryYmarket= 1;
      }
      if($('input[name=useAddressParts]').prop('checked')) {
        useAddressParts= 1;
      }

      if ($('.section-settings #add-deliveryMethod-wrapper .useWeight').data('active') == 'tru') {
        $('#add-deliveryMethod-wrapper .weights').each(function(index,element) {
          var weight = $(this).find('input[name=deliveryWeight]').val();
          var weightPrice = $(this).find('input[name=deliveryWeightPrice]').val();
          if (weight != '' && weightPrice != ''){
            weights.push({'w':weight,'p':weightPrice});
          }
        });
      }

      if ($('.section-settings #add-deliveryMethod-wrapper [name=useIntervals]').prop('checked')) {
        $('#add-deliveryMethod-wrapper .intervals').each(function(index,element) {
          var interval = $(this).find('input[name=deliveryInterval]').val();
          if (interval != ''){
            intervals.push(admin.htmlspecialchars(interval));
          }
        });
      }
      admin.ajaxRequest({
        mguniqueurl: "action/saveDeliveryMethod",
        status: status,
        deliveryName: deliveryName,
        deliveryCost: deliveryCost,
        deliveryDescription: deliveryDescription,
        deliveryActivity: deliveryActivity,
        deliveryDate: deliveryDate,
        useAddressParts: useAddressParts,
        paymentMethod: paymentMethod,
        deliveryId: id,
        free: free,
        weight: weights,
        intervals: intervals,
        lang: $('.section-settings #tab-deliveryMethod-settings .select-lang').val()
      },
      function(response) {
        $('.img-loader').hide();
  
        admin.indication(response.status, response.msg);
        if('success' == response.status) {
          admin.refreshPanel();
          admin.closeModal($('#add-deliveryMethod-wrapper'));
        } 
      });
    },
    
   /**
    * сохранение способа оплаты
    */
    savePaymentMethod:function(id) {      
      $('.img-loader').show();
     
      //обрабатываем параметры методов оплаты
      var name = admin.htmlspecialchars($('.name-payment').val());   
      
      
      //обрабатываем параметры методов оплаты
      var paymentParam ='{';
      $('#paymentParam input,#paymentParam select').each(function() {
          if(!$(this).hasClass('name-payment') && !$(this).hasClass('permission')) {
           // paymentParam+='"'+$(this).attr('name')+'":"'+$(this).val().replace(/\\/g, '\\\\\\\\').replace(/"/g, '\\\\$&')+'",';
           paymentParam+='"'+$(this).attr('name')+'":"'+admin.htmlspecialchars($(this).val().replace(/'/g, '"'))+'",';
          }
      });   
      
      paymentParam = paymentParam.substr(0, paymentParam.length-1); //удаляем последнюю запятую в конце списка
      paymentParam+='}';
      
      var deliveryMethod='';
      if(0 != $('#deliveryCheckbox #deliveryArray').find('input').length) {
        //обрабатываем доступные методы доставки для данного метода оплаты

        deliveryMethod='{';
        $('#deliveryCheckbox input').each(function() {

          if($(this).prop('checked')) {
            deliveryMethod += '"'+admin.htmlspecialchars($(this).attr('name'))+'":1,';
          } else {
            deliveryMethod += '"'+admin.htmlspecialchars($(this).attr('name'))+'":0,';
          }
        });

        deliveryMethod = deliveryMethod.substr(0, deliveryMethod.length-1); //удаляем последнюю запятую в конце списка
        deliveryMethod +='}';
      }            
      
      var rate = $('.discount-rate-control input[name=rate]').val();
      
      if(rate!=0) {
        rate = rate/100;
      }
      
      if($('.rate-dir').text()!='+') {
        rate = -1*rate;
      }
      
      //активность метода оплаты
      var paymentActivity = 0;
      if($('input[name=paymentActivity]').prop('checked')) {
        paymentActivity = 1;
      }
      
      admin.ajaxRequest({
        mguniqueurl: "action/savePaymentMethod",
        paymentParam: paymentParam,
        deliveryMethod: deliveryMethod,
        paymentActivity: paymentActivity,
        name: name,
        rate: rate,
        paymentId: id,
        permission: $('#tab-paymentMethod-settings .permission').val(),
        lang: $('#tab-paymentMethod-settings .select-lang').val()
      },
      function(response) {
        $('.img-loader').hide();
        admin.indication(response.status, response.msg);
        if('success' == response.status) {
          admin.closeModal('#add-paymentMethod-wrapper');
          admin.refreshPanel();
        }
      }
      );
    },
    
    /**
     * Удаляет способ доставки из БД сайта и таблицы в текущем разделе
     */
    deleteDelivery: function(id) {
      if(confirm(lang.DELETE+'?')) {
        admin.ajaxRequest({
          mguniqueurl:"action/deleteDeliveryMethod",
          id: id
        },
        (function(response) {
          admin.indication(response.status, response.msg);
          admin.refreshPanel();          
         })
        );
      }
    },

    updateMessageModal: function() {
      admin.ajaxRequest({
          mguniqueurl: "action/getEngineMessages",   
          lang: $('#messages-modal .select-lang').val()
        },
        function(response) {
          msghtml = '<ul class="accordion" data-accordion="" data-multi-expand="true" data-allow-all-closed="true">';
          $.each(response.data, function (gindex) {
            var grp = gindex;
            switch(grp) {
              case 'order':
                  msghtml += '\
                    <li class="accordion-item" data-accordion-item=""><a class="accordion-title" href="javascript:void(0);">Заказы</a>\
                    <a part="order"><i class="fa fa-refresh"></i>'+lang.RETURN_DEFAULT+'</a>\
                      <div class="accordion-content" data-tab-content="">';
                    // msghtml += '\
                    // <div class="row">\
                    //   <div class="large-12 columns">\
                    //     <div class="widget-panel msgs">\
                    //       <span>Заказы</span>\
                    //       <a part="order"><i class="fa fa-refresh"></i>'+lang.RETURN_DEFAULT+'</a>\
                    //     </div>\
                    //   </div>\
                    // </div>';
                  break;
              case 'product':
                  msghtml += '\
                    </div>\
                  </li>\
                  <li class="accordion-item" data-accordion-item=""><a class="accordion-title" href="javascript:void(0);">Товары</a>\
                    <a part="product"><i class="fa fa-refresh"></i>'+lang.RETURN_DEFAULT+'</a>\
                      <div class="accordion-content" data-tab-content="">';

                  // msghtml += '\
                  //   <div class="row">\
                  //     <div class="large-12 columns">\
                  //       <div class="widget-panel msgs">\
                  //         <span>Товары</span>\
                  //         <a part="product"><i class="fa fa-refresh"></i>'+lang.RETURN_DEFAULT+'</a>\
                  //       </div>\
                  //     </div>\
                  //   </div>';
                  break;
              case 'register':

                  msghtml += '\
                    </div>\
                  </li>\
                  <li class="accordion-item" data-accordion-item=""><a class="accordion-title" href="javascript:void(0);">Регистрация и авторизация</a>\
                    <a part="register"><i class="fa fa-refresh"></i>'+lang.RETURN_DEFAULT+'</a>\
                      <div class="accordion-content" data-tab-content="">';

                  // msghtml += '\
                  //   <div class="row">\
                  //     <div class="large-12 columns">\
                  //       <div class="widget-panel msgs">\
                  //         <span>Регистрация и авторизация</span>\
                  //         <a part="register"><i class="fa fa-refresh"></i>'+lang.RETURN_DEFAULT+'</a>\
                  //       </div>\
                  //     </div>\
                  //   </div>';
                  break;
              case 'feedback':

                  msghtml += '\
                    </div>\
                  </li>\
                  <li class="accordion-item" data-accordion-item=""><a class="accordion-title" href="javascript:void(0);">Обратная связь</a>\
                    <a part="feedback"><i class="fa fa-refresh"></i>'+lang.RETURN_DEFAULT+'</a>\
                      <div class="accordion-content" data-tab-content="">';

                  // msghtml += '\
                  //   <div class="row">\
                  //     <div class="large-12 columns">\
                  //       <div class="widget-panel msgs">\
                  //         <span>Обратная связь</span>\
                  //         <a part="feedback"><i class="fa fa-refresh"></i>'+lang.RETURN_DEFAULT+'</a>\
                  //       </div>\
                  //     </div>\
                  //   </div>';
                  break;
              case 'status':

                  msghtml += '\
                    </div>\
                  </li>\
                  <li class="accordion-item" data-accordion-item=""><a class="accordion-title" href="javascript:void(0);">Статусы заказов</a>\
                    <a part="status"><i class="fa fa-refresh"></i>'+lang.RETURN_DEFAULT+'</a>\
                      <div class="accordion-content" data-tab-content="">';

                  // msghtml += '\
                  //   <div class="row">\
                  //     <div class="large-12 columns">\
                  //       <div class="widget-panel msgs">\
                  //         <span>Статусы заказов</span>\
                  //         <a part="status"><i class="fa fa-refresh"></i>'+lang.RETURN_DEFAULT+'</a>\
                  //       </div>\
                  //     </div>\
                  //   </div>';
                  break;
              
              default:
                break;
            }
            $.each(response.data[grp], function (index) {
              msghtml += '\
                <div class="row">\
                  <div class="small-12 medium-4 columns dashed">\
                    <label>'+response.data[grp][index].title+'<i class="fa fa-question-circle tip fl-right" aria-hidden="true" title="'+response.data[grp][index].tip+'"></i></label>\
                  </div>\
                  <div class="small-12 medium-8 columns">\
                    <div class="input-with-text">\
                      <input type="text" ajdi="'+response.data[grp][index].id+'" name="'+index+'" class="messages" value=\''+response.data[grp][index].text+'\'>\
                    </div>\
                  </div>\
                </div>';
            });
          });

          msghtml += '\
              </div>\
            </li>\
          </ul>';

          $('#messages-modal .reveal-body .clearfix').html(msghtml);
          admin.openModal($('#messages-modal'));
        })
    },
    
    /**
     * Обновляет необходимые поля при переходе по табам
     */
    updataTabs: function() {
      $('.add-new-button').hide();
      admin.ajaxRequest({
        mguniqueurl:"action/getMethodArray"
      },
      function(response) {
        var deliveryArray ='';
        //массив способов доставки         
        $('.add-new-button').show();
        $.each(response.data.deliveryArray, function(i, delivery) {
          var paymentMethod = delivery.paymentMethod ? delivery.paymentMethod : '{"0":0}';
          //к каждому способу доставки добавляем "привязаные" способы оплаты
          $('tr#delivery_'+delivery.id+' td#paymentHideMethod').text(paymentMethod).data('weight', delivery.weight).data('interval', delivery.interval).data('address_parts', delivery.address_parts);
          //формируем список чекбоксов для вставки в модальное окно способов оплаты
          deliveryArray +='\
            <div class="row">\
              <div class="small-5 columns">\
                <label for="cryt-'+delivery.id+'" class="middle">'+delivery.name+'</label>\
              </div>\
              <div class="small-7 columns">\
                <div class="checkbox margin">\
                  <input id="cryt-'+delivery.id+'" type="checkbox" name="'+delivery.id+'" class="deliveryMethod">\
                  <label for="cryt-'+delivery.id+'"></label>\
                </div>\
              </div>\
            </div>\
          ';
        });
        
        $('#add-paymentMethod-wrapper #deliveryArray').html(deliveryArray);
        
        var paymentArray ='';
        //массив способов оплаты
    
        $.each(response.data.paymentArray, function(i, payment) {
          
          var deliveryMethod = payment.deliveryMethod ? payment.deliveryMethod : '';
          $('tr#payment_'+payment.id+' td#deliveryHideMethod').text(deliveryMethod);
          
          paymentArray +='\
            <label class="payment-'+payment.id+'">\
              <span class="custom-text">'+payment.name+'</span>\
              <input type="checkbox" name="'+payment.id+'" class="paymentMethod">\
            </label>\
          ';
        });
        
        $('#add-deliveryMethod-wrapper #paymentArray').html(paymentArray);

       
      },
      $('.main-settings-list')
      );
    },
    
    validForm : function() {
      $('.errorField').css('display','none');
      $('input').removeClass('error-input');
      var error;
      var cost = $('input[name=deliveryCost]').val();
      cost = admin.numberDeFormat(cost); //отменяем форматирование цены
      $('input[name=deliveryCost]').val(cost); //помещаем новое значение в поле ввода перед отправкой
      
      if('' == $('input[name=deliveryName]').val()) {
        $('input[name=deliveryName]').addClass('error-input');
        $('input[name=deliveryName]').parent("label").find('.errorField').css('display','block');
        error = true;
      }
      
      if('' == cost || 0 != cost*0) {
        $('input[name=deliveryCost]').addClass('error-input');
        $('input[name=deliveryCost]').parent("label").find('.errorField').css('display','block');
        error = true;
      }
      
      if('' == $('input[name=deliveryDescription]').val()) {
        $('input[name=deliveryDescription]').addClass('error-input');
        $('input[name=deliveryDescription]').parent("label").find('.errorField').css('display','block');
        error = true;
      }
           
      // Проверка поля для бесплатной доставки, является ли текст в него введенный числом.    
      if(isNaN(parseFloat($('input[name=free]').val()))) {
        $('input[name=free]').addClass('error-input');
        $('input[name=free]').parent("label").find('.errorField').css('display','block');
        error = true;
      }
      
      if(error == true) {
        return false;
      }

      return true;
    },
    
    /**
     * Загружает водяной знак
     */
    addWatermark: function() {
      $('.watermarkform').ajaxForm({
        type:"POST",
        url: "ajax",
        data: {
          mguniqueurl:"action/updateWaterMark"
        },
        cache: false,
        dataType: 'json',
        success: function(response) {
          admin.indication(response.status, response.msg);         
          $('.watermark-img').html("");   
          $('.watermark-img').html("<img src='"+admin.SITE+'/uploads/watermark/watermark.png?='+parseInt(new Date().getTime()/1000)+"'/>");
        }
      }).submit();
    },  
  
    addNewTemplate:function(type) {      
       // установка шаблона


        
       $("."+type).ajaxForm({
         type:"POST",
         url: "ajax",
         data: {
           mguniqueurl:"action/addNewTemplate"
         },
         cache: false,
         dataType: 'json',
         success: function(response){
         
           if(response.status == 'error'){
             admin.indication(response.status, response.msg);  
           }else{
             admin.indication(response.status, response.msg);  
             admin.refreshPanel();
           }
        
         }
       }).submit();
     },
     
    /**
    * функция для приема файла из аплоадера, для сохранения в путь логотипа сайта
    */         
    getFile: function(file) {               
      var dir = file.url;                     
      $('.section-settings input[name="shopLogo"]').val(dir);    
      $('.section-settings .logo-img img').attr('src',dir).show(); 
      $('.section-settings .logo-img .remove-added-logo').show();
    },     
    /**
    * функция для приема фонового изобрадения для сайта
    */         
    getBackground: function(file) {               
      var dir = file.url;              
      $('.section-settings input[name="backgroundSite"]').val(dir);    
      $('.section-settings .background-img img').attr('src',dir).show();  
      $('.section-settings .background-img .remove-added-background').show();
    },   
        
          
    /**
    * функция строит список цветовых схем
    */         
    drawColorShemes: function(arrayColor) { 
    
      if(arrayColor.length == 0) {
        $('.template-schemes').hide();
        $('.template-schemes .color-scheme').remove();
        return false;
      }
     
      var html = '<ul class="color-list">';
      var active = 'active';
      arrayColor.forEach(function(element) {          
         html +='<li class="color-scheme '+active+'" data-scheme="'+element+'" style="background:#'+element+';"></li>';
         active = '';
      }); 
      html += '</ul>';
      
      $('.template-schemes .color-list').replaceWith(html);
      $('.template-schemes').show();
      
      return html;
    },  


    /**
    * функция замены фавикона
    */         
    addFavicon: function(img_container) { 
      // отправка картинки на сервер
      img_container.find('.imageform').ajaxForm({
        type:"POST",
        url: "ajax",
        data: {
          mguniqueurl:"action/updateFavicon"
        },
        cache: false,
        dataType: 'json',
        success: function(response) {
          if (response.status=='error') {
            admin.indication(response.status, response.msg);
          } else {
            img_container.find('#favicon-image').attr('src', admin.SITE+'/'+response.data.img+'?t='+Math.random());
            img_container.find('.option').val('favicon.ico');
          }
         }
      }).submit();
    },

      /**    
     * Запускает процесс создания карты в корне сайта
     * @param {type} data - данные для вывода в строке таблицы
     */           
    createMap: function() {       
      admin.ajaxRequest({
        mguniqueurl: "action/generateSitemap", // действия для выполнения на сервере             
      },      
      function(response) {
        admin.indication(response.status, response.msg);  
        if (response.status!='error') {
          $('#SEOMethod-settings .sitemap-msg').html('<div class="alert-block success text-center">'+response.data.msg+'</div>');
        }        
      }      
      );
    }, 

    setSeoForGroup: function(type) {  
      isChange = confirm(lang.MESSAGE_CONFIRM);   
      if(isChange) {  
        admin.ajaxRequest({
          mguniqueurl: "action/setSeoForGroup", // действия для выполнения на сервере    
          data: type         
        },      
        function(response) {
          admin.indication(response.status, response.msg);       
        });
      }
    }, 
     
    repeatInit: function() {
      settings.calbackOpenTab();


      admin.sortable('#user-property-edit .main-table tbody', 'property_data');

      if(($('.option[name=templateName]').val()=='default')||($('.option[name=templateName]').val()=='mg-default')) {
        $('.default-info').show();            
      }

      // проверка и скрытие полей smtp
      if(!$('.section-settings input[name=smtp]').prop('checked')) {
        $('.section-settings input[name=smtpSsl]').parent().parent().parent().hide();
        $('.section-settings input[name=smtpHost]').parent().parent().hide();
        $('.section-settings input[name=smtpLogin]').parent().parent().hide();
        $('.section-settings input[name=smtpPass]').parent().parent().hide();
        $('.section-settings input[name=smtpPort]').parent().parent().hide();
      } 

      // проверка и скрытие полей в мемкэша
      $('.memcache-conection').hide();  
      $('input[name="cacheHost"]').parent().parent().hide();
      $('input[name="cachePort"]').parent().parent().hide();
      $('input[name="cachePrefix"]').parent().parent().hide();
      if($('input[name=cacheMode]').val()=="MEMCACHE") {
        $('.memcache-conection').show();  
        $('input[name="cacheHost"]').parent().parent().show();  
        $('input[name="cachePort"]').parent().parent().show();    
        $('input[name="cachePrefix"]').parent().parent().show();
      }

      if($('input[name=sessionToDB]').prop('checked')) {
        $('.section-settings #tab-shop-settings input[name=sessionLifeTime]').parent().parent().parent().show();
      } else {
        $('.section-settings #tab-shop-settings input[name=sessionLifeTime]').parent().parent().parent().hide();
      }

      // для 1с
      if($('#qqvariantToSize1c').prop('checked')) {
        $('input[name="colorName1c"]').closest('.row').show();
        $('input[name="sizeName1c"]').closest('.row').show();
      } else {
        $('input[name="colorName1c"]').closest('.row').hide();
        $('input[name="sizeName1c"]').closest('.row').hide();
      }
    },
  }
})();

settings.init();