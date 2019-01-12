/**
 * Модуль для работы со ссылками на страницы выборок фильтра
 */

var filterShortLink = (function() {  
  return {
    supportCkeditor: null, 
    supportCkeditorExtra: null,
    init: function(){   
      $('body').on('change', '#add-short-link-modal [name=url]', function() {
        $(this).val($(this).val().replace(mgBaseDir, ''));
      });   
      // смена языка
      $('.admin-center').on('change','#add-short-link-modal .select-lang', function() {
        filterShortLink.editRewrite($('#add-short-link-modal .save-button').attr('id'));     
      });
      // открыть модалку с привязками к категориям
      $('body').on('click', '#SEOMethod-settings .addShortLink', function(){
        filterShortLink.openModalWindow('add');
      });
      // редактирования строки свойства
      $('body').on('click', '.filterShortLinkTable li.edit-row', function(){ 
        filterShortLink.openModalWindow('edit', $(this).attr('id'));
      });
      // смена активности записи 
      $('body').on('click', '.filterShortLinkTable li.visible', function(){
        filterShortLink.setActivity($(this), $(this).attr('data-id'));
      });
      // удаление записи 
      $('body').on('click', '.filterShortLinkTable li.delete-row', function(){
        if(confirm('Удалить?')) {
          filterShortLink.deleteRewrite($(this), $(this).attr('id'));
        }
      });
      // показ длинного урла
      $('body').on('click', '.filterShortLinkTable .show-long-url', function(){
        $(this).hide();
        $(this).parents('td').find('.url-long').show();
      });
      // Сохранение в модальном окне.
      $('body').on('click', '#add-short-link-modal .save-button', function(){
        filterShortLink.saveRewrite($(this).attr('id'));
      });
      $('body').on('click', '.rewriteLinkPage', function (){                
        admin.ajaxRequest({
          mguniquetype: cookie("type"),
          mguniqueurl: admin.SECTION+'.php',
          seo_pager: 1,
          group: "STNG_SEO_GROUP_1",
          rewritePage: admin.getIdByPrefixClass($(this), 'page')
        }, function(response){          
          $('.filterShortLinkTable').empty();
          
          response.data.forEach(function(element, index, array){
            $('.filterShortLinkTable').append(filterShortLink.drawRow(element));
          });
          
          $('#urlRewritePager').html(response.pager);
        });
        return false;
      });
      // формирование meta title по введенному названию
      $('.admin-center').on('blur', '#add-short-link-modal input[name=titeCategory]', function(){
        var title = $(this).val().replace(/"/g,'');
        
        if (!$('#add-short-link-modal input[name=short_url]').val()){
          $('#add-short-link-modal input[name=short_url]').val(admin.urlLit(title));
        }
        
        if (!$('#add-short-link-modal input[name=meta_title]').val()){
          $('#add-short-link-modal input[name=meta_title]').val(title);
        }
        
        if (!$('#add-short-link-modal input[name=meta_keywords]').val()) {	           
          $('#add-short-link-modal input[name=meta_keywords]').val(title);
        }        
      });
      // автотранслит заголовка в URL. При клике, или табе, на поле URL, если оно пустое то будет автозаполнено транслитироированным заголовком
      $('.admin-center').on('click, focus', '#add-short-link-modal input[name=short_url]', function () {
        if ($('#add-short-link-modal input[name=short_url]').val() == '') {
          var text = $('#add-short-link-modal input[name=title]').val();
          if (text) {
            text.replace('%', '-');
            text = admin.urlLit(text, 1);
            $(this).val(text);
          }
        }
      });
      // при заполнении поля описание товара - первые 160 символов копируются в блок SEO - description
      CKEDITOR.on('instanceCreated', function(e) {
        if (e.editor.name === 'cat_desc') {
          e.editor.on('blur', function (event) {      
          var description = $('#add-short-link-modal textarea[name=meta_desc]').val();          
          if (!$.trim(description)) {            
            description = $('textarea[name=cat_desc]').val();
            var short_desc = description.replace(/<\/?[^>]+>/g, '');
            short_desc = admin.htmlspecialchars_decode(short_desc.replace(/\n/g, ' ').replace(/&nbsp;/g, '').replace(/\s\s*/g, ' ').replace(/"/g, ''));            
            if (short_desc.length > 150) {
              var point = short_desc.indexOf('.', 150);
              short_desc = short_desc.substr(0, (point > 0 ? point : short_desc.indexOf(' ',150)));   
            }                                        
            $('#add-short-link-modal textarea[name=meta_desc]').val($.trim(short_desc));
          } 
          });
        }
      }); 

      /*Инициализирует CKEditior и раскрывает поле для заполнения описания товара*/
      $('.admin-center').on('click', '.category-desc-wrapper .html-content-edit', function(){
        var link = $(this);
        $('textarea[name=cat_desc]').ckeditor(function() {  
          $('#html-content-wrapper').show();
          link.hide();
        });
      });
      /*Инициализирует CKEditior и раскрывает поле для заполнения seo описания товара*/
      $('.admin-center').on('click', '.category-desc-wrapper .html-content-edit-seo', function(){
        var link = $(this);
        $('textarea[name=seo_content]').ckeditor(function() {  
          $('#html-content-wrapper-seo').show();
          link.hide();
        });
      });
    },   
    deleteRewrite: function(obj, id){
      admin.ajaxRequest({
        mguniqueurl: "action/deleteRewrite",
        id: id,        
      },
      function (response) {
        if(response.status != "error"){
          obj.parents("tr.rewrite-line").remove();
        }
          
        admin.indication(response.status, response.msg);        
      });
    },
    setActivity: function(obj, id){
      var activity = 1;
      
      if(obj.find('a').hasClass('active')){
        activity = 0;
      }
      
      admin.ajaxRequest({
        mguniqueurl: "action/setRewriteActivity",
        id: id,
        activity: activity
      },
      function (response) {
        if(response.status != "error"){
          obj.find('a').toggleClass('active');
        }
          
        admin.indication(response.status, response.msg);        
      });
    },
    saveRewrite: function(id){
      // Пакет характеристик категории.
      var packedProperty = {
        mguniqueurl: "action/saveRewrite",
        id: id,
        activity: $('input[name=activity]').val(),
        titeCategory: $('input[name=titeCategory]').val(),
        url: $('input[name=url]').val(),
        short_url: $('input[name=short_url]').val(),
        cat_desc: $('textarea[name=cat_desc]').val(),
        meta_title: $('input[name=meta_title]').val(),
        meta_keywords: $('input[name=meta_keywords]').val(),
        meta_desc: $('textarea[name=meta_desc]').val(),  
        cat_desc_seo: $('textarea[name=seo_content]').val(),
        lang: $('#add-short-link-modal .select-lang').val()
      };
      
      // Отправка данных на сервер для сохранения.
      admin.ajaxRequest(packedProperty,
      function (response) {
        console.info(response);
          admin.indication(response.status, response.msg);
          
          var row = filterShortLink.drawRow(response.data);
          
          if(id){
            $('.filterShortLinkTable tr[id='+id+']').replaceWith(row);
          }else{
            $('.filterShortLinkTable').prepend(row);
          }

          // Закрываем окно.
          admin.closeModal('#add-short-link-modal');
          // admin.refreshPanel();
      });
    },
    drawRow: function(data){
      var activity = data.activity == 1?'active':'';         
      
      var row = '\
        <tr id="'+data.id+'" class="rewrite-line">\
          <td>'+data.titeCategory+'</td>\
          <td>'
              +admin.SITE+'/'+data.short_url+'\
              <a class="link-to-site tool-tip-bottom" href="'+admin.SITE+'/'+data.short_url+'" target="_blank">\
                <img src="'+admin.SITE+'/mg-admin/design/images/icons/link.png" alt="">\
              </a>\
          </td>\
          <td style="word-wrap: break-word;">\
            <span class="show-long-url">'+lang.SHOW+'</span>\
            <span style="display: none;" class="url-long">'+mgBaseDir+data.url+'</span>\
          </td>\
          <td class="actions text-right">\
            <ul class="action-list">\
              <li class="edit-row" id="'+data.id+'"><a href="javascript:void(0);" class="fa fa-pencil" title="'+lang.EDIT+'"></a></li>\
              <li class="visible tool-tip-bottom" data-id="'+data.id+'" title="'+lang.ACTIVITY+'" ><a href="javascript:void(0);" class="fa fa-eye '+activity+'"></a></li>\
              <li class="delete-order" id="'+data.id+'"><a href="javascript:void(0);" class="fa fa-trash" title="'+lang.DELETE+'"></a></li>\
            </ul>\
          </td>\
        </tr>';
      
      return row;
    },
    /**
     * Открывает модальное окно.
     * type - тип окна, либо для создания нового товара, либо для редактирования старого.
     * id - редактируемая категория, если это не создание новой
     */
    openModalWindow: function (type, id) {      
      // try {
      //   if (CKEDITOR.instances['cat_desc']) {
      //     CKEDITOR.instances['cat_desc'].destroy();
      //   }
      // } catch (e) {
      // } 
      // try {
      //   if (CKEDITOR.instances['seo_content']) {
      //     CKEDITOR.instances['seo_content'].destroy();
      //   }
      // } catch (e) {
      // } 

      switch (type) {
        case 'edit':
        {          
          filterShortLink.clearFileds();          
          filterShortLink.editRewrite(id);
          break;
        }
        case 'add':
        {                    
          filterShortLink.clearFileds();          
          break;
        }        
        default:
        {
          filterShortLink.clearFileds();
          break;
        }
      }

      $('textarea[name=cat_desc]').ckeditor(function () {
        this.setData(filterShortLink.supportCkeditor);
      });
      $('textarea[name=seo_content]').ckeditor(function () {
        this.setData(filterShortLink.supportCkeditorExtra);
      });

      // Вызов модального окна.
      admin.openModal('#add-short-link-modal');
    },
    editRewrite: function(id){  
      admin.ajaxRequest({
        mguniqueurl: "action/getRewriteData",
        id: id,
        lang: $('#add-short-link-modal .select-lang').val()
      },
      filterShortLink.fillFileds(),
      $('.add-product-form-wrapper .add-category-form')
      );
    },
    /**
     * Заполняет поля модального окна данными.
     */
    fillFileds: function () {
      return (function (response) {        
        filterShortLink.supportCkeditor = response.data.cat_desc;
        filterShortLink.supportCkeditorExtra = response.data.cat_desc_seo;
        $('input').removeClass('error-input');
        $('input[name=activity]').val(response.data.activity);
        $('input[name=titeCategory]').val(response.data.titeCategory);
        $('input[name=url]').val(response.data.url);
        $('input[name=short_url]').val(response.data.short_url);
        $('textarea[name=cat_desc]').val(response.data.cat_desc);
        $('input[name=meta_title]').val(response.data.meta_title);        
        $('input[name=meta_keywords]').val(response.data.meta_keywords);
        $('textarea[name=meta_desc]').val(response.data.meta_desc);
        $('.symbol-count').text($('textarea[name=meta_desc]').val().length);
        $('.save-button').attr('id', response.data.id);
        $('textarea[name=seo_content]').val(response.data.cat_desc_seo);
      })
    },
    /**
     * Чистит все поля модального окна.
     */
    clearFileds: function () {
      $('input[name=titeCategory]').val('');
      $('input[name=url]').val('');  
      $('input[name=short_url]').val('');  
      $('textarea[name=cat_desc], textarea[name=seo_content] ').val('');
      $('input[name=meta_title]').val('');
      $('input[name=meta_keywords]').val('');
      $('textarea[name=meta_desc]').val('');
      $('.symbol-count').text('0');
      $('.save-button').attr('id', '');
      $('.category-desc-wrapper .html-content-edit,.category-desc-wrapper .html-content-edit-seo').show();

      // Стираем все ошибки предыдущего окна если они были.
      $('.errorField').css('display', 'none');
      $('.error-input').removeClass('error-input');
      filterShortLink.supportCkeditor = "";
      filterShortLink.supportCkeditorExtra = "";
    },
  };
})();

// инициализация модуля при подключении
filterShortLink.init();