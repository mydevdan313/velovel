/**
 * Модуль для смены языка админки
 */
var multiLang = (function () {
  return {

    init: function() {

      if (typeof admin.sortableMini !== 'undefined' && $.isFunction(admin.sortableMini)) {
        admin.sortableMini('.language-tbody', 'multiLang.save()');
      }

      $('body').on('click', '.section-settings #tab-language-settings .save-button', function() {
        $('.section-settings #tab-language-settings [name=short]').each(function(index) {
          $(this).val($(this).val().replace(/(^\s+|[^a-zA-Z0-9 -]+|\s+$)/g,"").replace(/\s+/g, "-"));
        });
        multiLang.save();
      }); 

      $('body').on('click', '.section-settings #tab-language-settings .delete-lang', function() {
        if(!confirm('Удалить? Будут удалены все переводы!')) return;
        $(this).parents('tr').detach();
        // multiLang.save();
      }); 

      $('body').on('click', '.section-settings #tab-language-settings .add-new-lang', function() {
        $('.toDel').detach();
        var html = '';
        html += '<tr>';
        html += '<td class="mover"><i style="display:none;" class="fa fa-arrows ui-sortable-handle"></i></td>';
        html += '<td><input type="text" style="margin:0;" class="lang" name="short"></td>';
        html += '<td><input type="text" style="margin:0;" class="lang" name="full"></td>';
        html += '<td>'+$('.selecttemplate').html()+'</td>';
        html += '<td class="text-right actions">';
        html += '<ul class="action-list text-right">';
        html += '<li><a href="javascript:void(0)" class="fa fa-external-link" target="_blank" style="display:none;"></a></li>';
        html += '<input type="text" style="display:none;" class="lang" name="active" value="false">';
        html += '<li><a style="display:none;" href="javascript:void(0);" class="fa fa-lightbulb-o "></a></li>';
        html += '<li><a href="javascript:void(0)" class="fa fa-trash delete-lang" ></a></li>';
        html += '</ul>';
        html += '</td>';
        html += '</tr>';
        $('.section-settings #tab-language-settings tbody').append(html);
      });

      $('body').on('click', '.section-settings #tab-language-settings .fa-lightbulb-o', function() {
        if ($(this).hasClass('active')) {
          $(this).removeClass('active');
          $(this).parents('tr').find('[name=active]').val('false');
        }
        else{
          $(this).addClass('active');
          $(this).parents('tr').find('[name=active]').val('true');
        }
        multiLang.save();
      }); 

    },

    save: function() {
      error = false;
      var data = [];
      for(i = 0; i < $('.section-settings #tab-language-settings tbody tr').length; i++) {
        if($('.section-settings #tab-language-settings tbody tr:eq('+i+') [name=short]').val() == '') {
          $('.section-settings #tab-language-settings tbody tr:eq('+i+') [name=short]').addClass('error');
          error = true;
          continue;
        } else {
          $('.section-settings #tab-language-settings tbody tr:eq('+i+') [name=short]').removeClass('error');
        }
        data[i] = admin.createObject('.section-settings #tab-language-settings tbody tr:eq('+i+') .lang');
      }
      if(error) return false;
      admin.ajaxRequest({
        mguniqueurl: "action/saveLang",
        data: data
      },
      function(response) {
        $('.section-settings #tab-language-settings .language-tbody .fa-external-link').each(function(index) {
          $(this).attr('href', $('.selecttemplate').attr('site')+'/'+$(this).parents('tr').find('[name=short]').val()).show();
        });
        $('.section-settings #tab-language-settings .language-tbody .mover i').show();
        $('.section-settings #tab-language-settings .language-tbody .fa-lightbulb-o').show();
        admin.indication(response.status, response.msg);        
      });
    },
  };
})();

// инициализация модуля при подключении
multiLang.init();