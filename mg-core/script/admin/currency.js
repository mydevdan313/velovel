/**
 * Модуль работы с валютами
 */
var currency = (function() {

  var savedDataRow = {}; // данные редактируемой строки
  var cansel = false; // использовать возврат значений при отмене

  return {
    init: function() {

      admin.sortableMini('.currency-tbody', "currency.save('123')");

      // редактирование
      $('body').on('click', '#tab-currency-settings .edit-currency ', function() {
        var tr = $(this).parents('tr');
        $('.currency-tbody .actions').find('.save-row, .cancel-row').hide();
        $('.currency-tbody .actions').find('.edit-row').show();       
        $(this).parents('.actions').find('.save-row, .cancel-row').show();
        $(this).parent().hide();        
        currency.editRow(tr);
  
      });

      // сохранение
      $('body').on('click', '#tab-currency-settings .save-currency ', function() {
        $('.currency-field').hide();      
        currency.save();
      });
     
      // добавление новой валюты.
      $('.admin-center').on('click', '#tab-currency-settings .add-new-currency', function(){
        $('.currency-tbody .actions').find('.save-row, .cancel-row').hide();
        $('.currency-tbody .actions').find('.edit-row').show();  
        currency.addRow();
      });
      
       // удаление 
      $('body').on('click', '#tab-currency-settings .delete-row', function() {
        if (confirm(lang.DELETE_CURRENCY+' '+$(this).attr('id')+'?')) {
          $(this).parents('tr').remove();
          currency.save();
        }        
        
      });
       // отмена редактирования 
      $('body').on('click', '#tab-currency-settings .cancel-row', function() {
        admin.refreshPanel();   
      });
       // сохранение изменений  
      $('body').on('click', '#tab-currency-settings .save-row', function() {
        var tr = $(this).parents('tr');
        var iso = $(tr).data('iso');
        var error = false;
        $(tr).find('input').each(function(){
          if ($(this).val()=='') {
            $(this).addClass('error-input');            
            error = true;
          }
        });
        if (error) {
          admin.indication('error', lang.ERROR_EMPTY);
        } else {
          currency.save(iso);
        }
      });

      $('body').on('click', '#tab-currency-settings .visible', function() {
        $(this).find('a').toggleClass('active');
        var tr = $(this).parents('tr');
        var iso = $(tr).data('iso');
        var error = false;
        $(tr).find('input').each(function(){
          if ($(this).val()=='') {
            $(this).addClass('error-input');            
            error = true;
          }
        });
        if (error) {
          admin.indication('error', lang.ERROR_EMPTY);
        } else {
          currency.save(iso);
        }
      });

      // Сохранение настроек 
      $('body').on('click', '#tab-currency-settings #printCurrencySelector', function() {
        admin.ajaxRequest({
          mguniqueurl: "action/onOffCurrency", // действия для выполнения на сервере    
          data: $('#tab-currency-settings #printCurrencySelector').prop('checked')
        },      
        function(response) {
          admin.indication(response.status, response.msg);  
          admin.refreshPanel();
        });
      });

    },
            
    editRow: function(tr) {    
      $('.currency-tbody .currency-field').hide();
       $('.currency-tbody .view-value-curr').show();
      
      $(tr).find('.currency-field').show();
      $(tr).find('.view-value-curr').hide();
      if ($(tr).hasClass('none-edit')) {
        $(tr).find('.currency-field').hide();  
        $(tr).find('.view-value-curr').show();   
        $(tr).find('input[name=currency_short]').show();
        $(tr).find('input[name=currency_short]').parents('td').find('.view-value-curr').hide();
      }
    },
            
    addRow: function() {
      var activeLi = '';
      var row = '<tr data-iso="NEW">\
                  <td class="mover"></td>\
                  <td data-iso="">\
                    <input type="text" name="currency_iso" value="" class="currency-field" style="display:none;margin-bottom:0;" placeholder="USD">\
                  </td>'+
                  '<td class="currency-short">\
                    <input type="text" name="currency_short" value="" class="currency-field" style="display:none;margin-bottom:0;" placeholder="$">\
                  </td>\
                  <td class="actions">\
                    <ul class="action-list text-right">\
                      <li class="save-row" id="NEW"><a class="tool-tip-bottom fa fa-floppy-o" href="javascript:void(0);" title="'+lang.SAVE+'"></a></li>\
                      <li class="cancel-row" id=""><a class="tool-tip-bottom fa fa-times" href="javascript:void(0);" title="'+lang.CANCEL+'"></a></li>\
                      <li class="edit-row" style="display:none" id="NEW"><a href="javascript:void(0)" class="edit-currency tool-tip-bottom fa fa-pencil" title="Редактировать" ></a></li>\
                      '+activeLi+'\
                      <li class="delete-row" style="display:none" id=""><a class="tool-tip-bottom fa fa-trash" title="'+lang.DELETE+'" href="javascript:void(0);"></a></li>\
                    </ul>\
                  </td>\
                </tr>';
      $('.currency-tbody').prepend(row);
      var tr = $('.currency-tbody tr[data-iso="NEW"]');
      admin.initToolTip();
      currency.editRow(tr);     
    },
     
    // сохраняет все валюты и их соотношения
    save: function(iso) {
      var data = [];
      $('.currency-tbody tr').each(function(index, row) {
        if ($(this).data('iso')==iso) {
          var pack = {
            iso: $(row).find('input[name=currency_iso]').val(),
            rate: 1,
            short: $(row).find('input[name=currency_short]').val(),
            active: $(row).find('.visible').find('a').hasClass('active')
          };           
        } else {
          var pack = {
            iso: $(row).find('input[name=currency_iso]').data('value'),
            rate: 1,
            short: $(row).find('input[name=currency_short]').data('value'),
            active: $(row).find('.visible').find('a').hasClass('active')
          };
        }
        data.push(pack);
      });

      // получаем с сервера все доступные пользовательские параметры
      admin.ajaxRequest({
        mguniqueurl: "action/saveCurrency",
        data: data
      },
      function(response) {    
        admin.indication(response.status, response.msg);
        admin.refreshPanel();
      }
      );
    
    }

  };
})();

// инициализация модуля при подключении
currency.init();