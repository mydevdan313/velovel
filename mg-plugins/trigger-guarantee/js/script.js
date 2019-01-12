/* 
 * Модуль подключается на странице настроек плагина.
 */

var triggerGuarantee = (function () {
  return {
    lang: [], // локаль плагина 
    trigger_html_content: null,
    init: function () {
      // установка локали плагина 
      admin.ajaxRequest({
        mguniqueurl: "action/seLocalesToPlug",
        pluginName: 'trigger-guarantee'
      },
      function (response) {
        triggerGuarantee.lang = response.data;
      });
      $('.admin-center').on('click', '.section-trigger-guarantee .add-new-button', function () {
        triggerGuarantee.showModal('add');
      });
      $('.admin-center').on('click', '.section-trigger-guarantee .choose-icon', function () {
        if ($(this).hasClass('opend')) {
          $('.section-trigger-guarantee .font-awesome-icons').toggle();
        } else {
          $(this).addClass('opend');
          triggerGuarantee.showIcons();
        }
      });
//      $('.admin-center').on('mouseover', '.section-trigger-guarantee .font-awesome-icons span .fa', function() {
//        $(this).addClass('fa-3x');
//      });
//      $('.admin-center').on('mouseleave', '.section-trigger-guarantee .font-awesome-icons span .fa', function() {
//        $(this).removeClass('fa-3x');
//      });
      $('.admin-center').on('click', '.section-trigger-guarantee .font-awesome-icons .fa', function () {
        var className = $(this).attr('class');
        $('.section-trigger-guarantee .add-trigger-element .img').html('<i class="' + className + ' fa-4x"></i>');
        $('.section-trigger-guarantee #add-plug-modal input[name="icon"] ').val(className);
        $('.section-trigger-guarantee .font-awesome-icons').slideUp();
      });
      $('.admin-center').on('click', '.section-trigger-guarantee #add-plug-modal .save-button', function () {
        var id = $(this).data('id');
        triggerGuarantee.saveField(id);
      });
      if ($('.section-trigger-guarantee .trigger-guarantee-elements tr').length > 0) {
        $('.section-trigger-guarantee .trigger-guarantee-save').show();
      }
      $('.admin-center').on('click', '.section-trigger-guarantee .save-button.trigger', function () {
        if ($('.section-trigger-guarantee table .trigger-guarantee-elements tr').length > 0) {
          var id = $(this).attr('id');
          var next_id = $(this).data('nextid');
          triggerGuarantee.saveTrigger(id, next_id);
        } else {
          admin.indication('error', 'Необходимо добавить элементы!');
          return false;
        }

      });

      // изменени внешнего вида элементов триггеров
      $('.admin-center').on('change', '.section-trigger-guarantee .list-option input, .list-option select', function () {
        triggerGuarantee.applySettings();
      })
      // Выбор картинки
      $('.admin-center').on('click', '.section-trigger-guarantee .browseImage', function () {
        admin.openUploader('triggerGuarantee.getImage');
      });
      $('.admin-center').on('click', '.section-trigger-guarantee  .open-trigger', function () {
        if ($(this).parents('li').hasClass('is-active')) {
          return true;
        } else {
          var id = $(this).attr('id');
          $(this).parents('ul').find('li').removeClass('is-active');
          $(this).parents('li').addClass('is-active');
          triggerGuarantee.clearTrigger();
          if (id) {
            admin.ajaxRequest({
              mguniqueurl: "action/getTrigger",
              pluginHandler: 'trigger-guarantee', // плагин для обработки запроса
              id: id,
            },
              function (response) {
                triggerGuarantee.fillFieldTrigger(response.data);
              }
            );
          }
        }
      });
      // Выводит модальное окно для редактирования
      $('.admin-center').on('click', '.section-trigger-guarantee .trigger-guarantee-elements .edit-row', function () {
        var id = $(this).data('id');
        triggerGuarantee.showModal('edit', id, 0);
      });
      // выбор элемента из существующих
      $('.admin-center').on('click', '.section-trigger-guarantee .add-exist-button', function () {
        $('.widget-table-wrapper .trigger-guarantee-all-elements').show();
        $('html, body').animate({scrollTop: $('.trigger-guarantee-all-elements').offset().top - 100}, 800);
      });
      // выбор закрыть таблицу с существующими элементами
      $('.admin-center').on('click', '.section-trigger-guarantee .close-trigger-table', function () {
        $('.widget-table-wrapper .trigger-guarantee-all-elements').slideUp(400);
      });
      // выбор элемента из таблицы
      $('.admin-center').on('click', '.section-trigger-guarantee .trigger-guarantee-tbody .trigger-item', function () {
        var id = $(this).parents('tr').data('id');
        var parent = $(this).parents('tr').find('.parent').data('parent');
        $('.widget-table-wrapper .trigger-guarantee-all-elements').hide();
        triggerGuarantee.showModal('edit', id, parent);
      })
      // удаление елемента из триггера
      $('.admin-center').on('click', '.section-trigger-guarantee .trigger-guarantee-elements .delete-row', function () {
        var id = $(this).data('id');
        if (id) {
          if (confirm('Удалить элемент триггера?')) {
            admin.ajaxRequest({
              mguniqueurl: "action/deleteElement", // действия для выполнения на сервере
              pluginHandler: 'trigger-guarantee', // плагин для обработки запроса
              id: id
            },
            function (response) {
              admin.indication(response.status, response.msg);
              if (response.status == 'success') {
                $('.section-trigger-guarantee .trigger-guarantee-elements tr[data-id=' + id + ']').remove();
                $('.section-trigger-guarantee .trigger-guarantee-table tr[data-id=' + id + ']').remove();
              }
            })
          }
        }

      });
      // удаление всего триггера
      $('.admin-center').on('click', '.section-trigger-guarantee .delete-trigger', function () {
        var id = $(this).data('id');
        if (id) {
          if (confirm('Удалить триггер и все его элементы?')) {
            admin.ajaxRequest({
              mguniqueurl: "action/deleteTrigger", // действия для выполнения на сервере
              pluginHandler: 'trigger-guarantee', // плагин для обработки запроса
              id: id
            },
            function (response) {
              admin.indication(response.status, response.msg);
              if (response.status == 'success') {
                admin.refreshPanel();
              }
            })
          }
        }

      });
    },
    /* открывает модальное окно 
     * @param {type} type -тип окна, для редактирования или для добавления
     * @param {type} id - номер записи, которая открыта на редактирование
     * @returns {undefined}
     */
    showModal: function (type, id, parent) {
      switch (type) {
        case 'add':
        {
          triggerGuarantee.clearField();
          break;
        }
        case 'edit':
        {
          
          triggerGuarantee.clearField();
          triggerGuarantee.fillField(id, parent);
          break;
        }
        default:
        {
          break;
        }
      }
      admin.openModal($('#add-plug-modal'));
      // $('textarea[name=trigger_html_content]').ckeditor();
    },
    /**
     * Очистка модального окна
     */
    clearField: function () {
      $('.section-trigger-guarantee #add-plug-modal .save-button').data('id', '');
      $('.section-trigger-guarantee textarea[name=trigger_html_content]').val('');
      $('.section-trigger-guarantee .add-trigger-element img').removeAttr('src');
      $('.section-trigger-guarantee .add-trigger-element .img').html('');
      $('.section-trigger-guarantee #add-plug-modal input[name="icon"] ').val('');

      if(CKEDITOR.instances.trigger_html_content) {
        CKEDITOR.instances.trigger_html_content.destroy();
      }
      triggerGuarantee.trigger_html_content = null;
    },
    /**
     * Заполнение модального окна данными из БД
     * @param {type} id
     * @returns {undefined}
     */
    fillField: function (id, parent) {
      var tmpText = '';
      admin.ajaxRequest({
        mguniqueurl: "action/getEntity", // действия для выполнения на сервере
        pluginHandler: 'trigger-guarantee', // плагин для обработки запроса
        id: id // id записи
      },
      function (response) {
        triggerGuarantee.trigger_html_content = admin.htmlspecialchars_decode(response.data.text);
        $('.section-trigger-guarantee #add-plug-modal textarea[name=trigger_html_content]').val(triggerGuarantee.trigger_html_content);
        if (parent == '0') {
          $('.section-trigger-guarantee #add-plug-modal .save-button').data('id', response.data.id);
        }
        $('.section-trigger-guarantee #add-plug-modal .img').html(response.data.icon);
        $('.section-trigger-guarantee #add-plug-modal  input[name="icon"] ').val(response.data.icon); 
        $('.section-trigger-guarantee #add-plug-modal textarea[name=trigger_html_content]').ckeditor(function () {
          this.setData(triggerGuarantee.trigger_html_content);
        });  
      }
      );
    },
    /**
     * загружает иконки из font-awesome
     */
    showIcons: function () {
      admin.ajaxRequest({
        mguniqueurl: "action/getIcons", // действия для выполнения на сервере
        pluginHandler: 'trigger-guarantee' // плагин для обработки запроса
      },
      function (response) {
        $('.section-trigger-guarantee .font-awesome-icons').append(response.data);
        $('.section-trigger-guarantee .font-awesome-icons').show();
      }
      );
    },
    /**
     * Сохранение данных из модального окна
     * @param {type} id
     * @returns {undefined}
     */
    saveField: function (id) {
      $('.section-trigger-guarantee  .add-trigger-element .img').attr('alt', 'tempAlt');
      var text = $('.section-trigger-guarantee  .add-trigger-element textarea[name=trigger_html_content]').val();
      textForAlt = $.trim(triggerGuarantee.strip_tags(text));
      text = admin.htmlspecialchars(text);
      var icon = $('.section-trigger-guarantee  .add-trigger-element .img').html().replace('alt="tempAlt"', 'alt="'+textForAlt+'"').replace("alt='tempAlt'", "alt='"+textForAlt+"'");
      var parent = $('.section-trigger-guarantee  .add-new-button').attr('id');

      admin.ajaxRequest({
        mguniqueurl: "action/saveEntity", // действия для выполнения на сервере
        pluginHandler: 'trigger-guarantee', // плагин для обработки запроса
        id: id,
        text: text,
        icon: icon,
        parent: parent ? parent : 0,
      },
        function (response) {
          admin.indication(response.status, response.msg);
          var elem = '<tr data-id=' + response.data.row.id + '>\
                <td >\
                <div class="trigger-item">\
                  <span class="trigger-icon">' + icon + '</span>\
                  <span class="trigger-text">' + admin.htmlspecialchars_decode(text) + '</span>\
                </div>\
                </td>\
                <td class="actions">\
                  <ul class="action-list">\
                    <li class="mover"\
                      data-id="' + response.data.row.id + '">\
                      <a href="javascript:void(0);"><i class="fa fa-arrows ui-sortable-handle"></i></a>\
                    </li>\
                    <li class="edit-row"\
                      data-id="' + response.data.row.id + '">\
                      <a class="tool-tip-bottom fa fa-pencil" href="javascript:void(0);"\
                        title="' + triggerGuarantee.lang['EDIT'] + '"></a>\
                    </li>\
                    <li class="delete-row"\
                      data-id="' + response.data.row.id + '">\
                      <a class="tool-tip-bottom fa fa-trash" href="javascript:void(0);"\
                      title="' + triggerGuarantee.lang['DELETE'] + '"></a>\
                      </li>\
                      </ul>\
              </td></tr>';
          var trigger = (parent ? 'Триггер №' + parent : '');
          var tr = '<tr data-id=' + response.data.row.id + '>\
                            <td>\
                             ' + response.data.row.id + '\
                            </td>\
                            <td>\
                                <div class="trigger-item">\
                                       <span class="trigger-icon">' + icon + '</span>\
                  <span class="trigger-text">' + admin.htmlspecialchars_decode(text) + '</span>\
                                    </div>\
                                      </td>\
                                <td class="parent" data-parent="' + parent + '">\
                                ' + trigger + '</td>\
                                </tr>';
          if (id && ($('.section-trigger-guarantee .trigger-guarantee-elements tr[data-id=' + id + ']')).length != 0) {
            $('.section-trigger-guarantee table .trigger-guarantee-elements tr[data-id=' + id + ']').replaceWith(elem);
          } else {
            $('.section-trigger-guarantee table .trigger-guarantee-elements').append(elem);
          }
          if (id && ($('.section-trigger-guarantee .trigger-guarantee-table tr[data-id=' + id + ']')).length != 0) {
            $('.section-trigger-guarantee .trigger-guarantee-table tr[data-id=' + id + ']').replaceWith(tr);
          } else {
            $('.section-trigger-guarantee .trigger-guarantee-table').append(tr);
          }
          admin.closeModal($('#add-plug-modal')); 
          triggerGuarantee.applySettings();
        }
      );
    },
    /**
     * функция для приема файла из аплоадера
     */
    getImage: function (file) {
      $('.section-trigger-guarantee #add-plug-modal  input[name="icon"]').val(file.url);
      if ($('.section-trigger-guarantee #add-plug-modal img').length != 0) {
        $('.section-trigger-guarantee #add-plug-modal  img').attr('src', file.url);
        $('.section-trigger-guarantee #add-plug-modal  img').attr('width', '170');
        $('.section-trigger-guarantee #add-plug-modal  img').attr('alt', 'tempAlt');
      } else {
        $('.section-trigger-guarantee #add-plug-modal  .img').html('<img class="img" width="170" alt="tempAlt" src="' + file.url + '">');
      }

    },
    /**
     * применение настроек к элементам триггера
     * 
     */
    applySettings: function () {
      var form = $('.section-trigger-guarantee .list-option select[name=form]').val();
      var radius = (form == 'circle' ? '50%' : 0);
      var float = $('.section-trigger-guarantee .list-option select[name=place]').val();
      var width = $('.section-trigger-guarantee .list-option input[name=width]').val();
      var unit = $('.section-trigger-guarantee .list-option select[name=unit]').val() == 1 ? '%' : 'px';
      width = width + unit;
      $('.section-trigger-guarantee .list-option .trigger-width').text(width);
      var height = $('.section-trigger-guarantee .list-option input[name=height]').val();
      $('.section-trigger-guarantee .list-option .trigger-height').text(height + 'px');
      if (float == 'left') {
        $('.section-trigger-guarantee table .trigger-guarantee-elements tr .trigger-icon').css('float', 'none');
      } else if (float == 'top') {
        $('.section-trigger-guarantee table .trigger-guarantee-elements tr .trigger-icon').css('display', 'block');
        $('.section-trigger-guarantee table .trigger-guarantee-elements tr .trigger-icon').css('float', 'none');
      }
      $('.section-trigger-guarantee table .trigger-guarantee-elements tr .trigger-icon img').css('border-radius', radius);
      $('.section-trigger-guarantee table .trigger-guarantee-elements tr .trigger-icon i').css('border-radius', radius);
      var color = $('.section-trigger-guarantee .list-option input[name="color_icon"]').val();
      $('.section-trigger-guarantee table .trigger-guarantee-elements tr .trigger-icon i').css('color', '#' + color);
      var colorBack = $('.section-trigger-guarantee .list-option input[name="background_icon"]').val();
      $('.section-trigger-guarantee table .trigger-guarantee-elements tr .trigger-icon i').css('background-color', '#' + colorBack);
      $('.section-trigger-guarantee table .trigger-guarantee-elements tr .trigger-icon img').css('background-color', '#' + colorBack);
      var background = $('.section-trigger-guarantee .list-option input[name="background"]').val();
      $('.section-trigger-guarantee table .trigger-guarantee-elements tr .trigger-item').css('background-color', '#' + background);
      $('.section-trigger-guarantee table .trigger-item').css('width', width);
      $('.section-trigger-guarantee table .trigger-item').css('height', height);
      var size = $('.section-trigger-guarantee .list-option input[name="fontSize"]').val();
      $('.section-trigger-guarantee table .trigger-icon i').css("font-size", size+"em");
    },
    /** 
     * сохраняем триггер в его элементами и настроками
     */
    saveTrigger: function (id, next_id) {
      var obj = '{';
      $('.section-trigger-guarantee .list-option input, .list-option select').each(function () {
        obj += '"' + $(this).attr('name') + '":"' + ($(this).val()) + '",';
      });
      obj += '}';
      //преобразуем полученные данные в JS объект для передачи на сервер
      var data = eval("(" + obj + ")");
      var title = $('.section-trigger-guarantee input[name="title-trigger"]').val();
      var elements = triggerGuarantee.createElementsList();
      admin.ajaxRequest({
        mguniqueurl: "action/saveTrigger",
        pluginHandler: 'trigger-guarantee', // плагин для обработки запроса
        id: id,
        settings: data,
        title: title,
        elements: elements,
        new_id: next_id,
      },
        function (response) {
          admin.indication(response.status, response.msg);
          if (!id) {
            admin.refreshPanel();
          }
        }
      );

    },
    // массив с id элементов триггера
    createElementsList: function () {
      var elem = [];
      $('.section-trigger-guarantee table .trigger-guarantee-elements tr').each(function () {
        elem.push($(this).data('id'));
      });
      return elem;
    },
    // 
    clearTrigger: function () {
      $('.section-trigger-guarantee .list-option select').each(function () {
        $(this).val('');
      });
      $('.section-trigger-guarantee .list-option input[name="color_icon"]').val('000');
      $('.section-trigger-guarantee .list-option input[name="background_icon"]').val('fff');
      $('.section-trigger-guarantee .list-option input[name="background"]').val('fff');
      $('.section-trigger-guarantee .list-option input[name="color_icon"]').css('border-color', '#000');
      $('.section-trigger-guarantee .list-option input[name="background"], .list-option input[name="background_icon"]').css('border-color', '#fff');
      $('.section-trigger-guarantee input[name="title-trigger"]').val('');
      $('.section-trigger-guarantee table .trigger-guarantee-elements tr').remove();
      var next_id = $('.section-trigger-guarantee .main-settings-trigger .short-code').data('id');
      $('.section-trigger-guarantee .main-settings-trigger .short-code').text(next_id);
      $('.section-trigger-guarantee .save-button.trigger').removeAttr('id');
      $('.section-trigger-guarantee .delete-trigger').data('id', '');
      $('.section-trigger-guarantee  .add-new-button').removeAttr('id');
      $('.section-trigger-guarantee .list-option select[name=unit]').val('1');
       $('.section-trigger-guarantee .list-option input[name=fontSize]').val('4');
    },
    fillFieldTrigger: function (data) {
      $('.section-trigger-guarantee .list-option select[name="form"]').val(data.settings.form);
      $('.section-trigger-guarantee .list-option select[name="place"]').val(data.settings.place);
      $('.section-trigger-guarantee .list-option input[name="width"]').val(data.settings.width);
      $('.section-trigger-guarantee .list-option input[name="height"]').val(data.settings.height);
      $('.section-trigger-guarantee .list-option select[name="layout"]').val(data.settings.layout ? data.settings.layout : 'vert');
      $('.section-trigger-guarantee .list-option input[name="color_icon"]').val(data.settings.color_icon);
      $('.section-trigger-guarantee .list-option input[name="background_icon"]').val(data.settings.background_icon);
      $('.section-trigger-guarantee .list-option input[name="background"]').val(data.settings.background);
      $('.section-trigger-guarantee .list-option input[name="color_icon"]').css('cssText', 'border-color: #' + data.settings.color_icon + ' !important');
      $('.section-trigger-guarantee .list-option input[name="background"]').css('cssText', 'border-color: #' + data.settings.background + ' !important');
      $('.section-trigger-guarantee .list-option input[name="background_icon"]').css('cssText', 'border-color: #' + data.settings.background_icon + ' !important');
      $('.section-trigger-guarantee input[name="title-trigger"]').val(data.title);
      $('.section-trigger-guarantee  .main-settings-trigger .short-code').text(data.id);
      $('.section-trigger-guarantee  .save-button.trigger').attr('id', data.id);
      $('.section-trigger-guarantee  .delete-trigger').data('id', data.id);
      $('.section-trigger-guarantee  .add-new-button').attr('id', data.id);
      $('.section-trigger-guarantee .list-option select[name=unit]').val(data.settings.unit? data.settings.unit : '1');
      $('.section-trigger-guarantee .list-option input[name="fontSize"]').val(data.settings.fontSize);
      function buildRow(el) {
        var row = '<tr data-id=' + el.id + '>\
                <td >\
                <div class="trigger-item">\
                  <span class="trigger-icon">' + el.icon + '</span>\
                  <span class="trigger-text">' + admin.htmlspecialchars_decode(el.text) + '</span>\
                </div>\
                </td>\
                <td class="actions">\
                  <ul class="action-list">\
                    <li class="mover"\
                      data-id="' + el.id + '">\
                      <a href="javascript:void(0);"><i class="fa fa-arrows ui-sortable-handle"></i></a>\
                    </li>\
                    <li class="edit-row"\
                      data-id="' + el.id + '">\
                      <a class="tool-tip-bottom fa fa-pencil" href="javascript:void(0);"\
                        title="' + triggerGuarantee.lang['EDIT'] + '"></a>\
                    </li>\
                    <li class="delete-row"\
                      data-id="' + el.id + '">\
                      <a class="tool-tip-bottom fa fa-trash" href="javascript:void(0);"\
                      title="' + triggerGuarantee.lang['DELETE'] + '"></a>\
                      </li>\
                      </ul>\
              </td></tr>';
        $('.section-trigger-guarantee table .trigger-guarantee-elements').append(row);
      }
      data.elements.forEach(buildRow);
      triggerGuarantee.applySettings();
    },

    strip_tags:function(str, allowed_tags) { 
        var key = '', allowed = false; 
        var matches = []; 
        var allowed_array = []; 
        var allowed_tag = ''; 
        var i = 0; 
        var k = ''; 
        var html = ''; 

        var replacer = function(search, replace, str) { 
            return str.split(search).join(replace); 
        }; 

        // Build allowes tags associative array 
        if (allowed_tags) { 
            allowed_array = allowed_tags.match(/([a-zA-Z]+)/gi); 
        } 

        str += ''; 

        // Match tags 
        matches = str.match(/(<\/?[\S][^>]*>)/gi); 

        // Go through all HTML tags 
        for (key in matches) { 
            if (isNaN(key)) { 
                // IE7 Hack 
                continue; 
            } 

            // Save HTML tag 
            html = matches[key].toString(); 

            // Is tag not in allowed list? Remove from str! 
            allowed = false; 

            // Go through all allowed tags 
            for (k in allowed_array) { 
                // Init 
                allowed_tag = allowed_array[k]; 
                i = -1; 

                if (i != 0) { i = html.toLowerCase().indexOf('<'+allowed_tag+'>');} 
                if (i != 0) { i = html.toLowerCase().indexOf('<'+allowed_tag+' ');} 
                if (i != 0) { i = html.toLowerCase().indexOf('</'+allowed_tag)   ;} 

                // Determine 
                if (i == 0) { 
                    allowed = true; 
                    break; 
                } 
            } 

            if (!allowed) { 
                str = replacer(html, "", str); // Custom replace. No regexing 
            } 
        } 

        return str; 
    }
  }
})();

triggerGuarantee.init();
