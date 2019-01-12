/**
 * Модуль для  раздела "Плагины".
 */
var plugin = (function () {
  return {

    /**
     * Инициализирует обработчики для кнопок и элементов раздела.
     */
    init:function() {

      $('body').on('click', '.plugReadMe', function() {
        admin.openModal('#plugin-read-modal');
		$.ajax({
		  url: $(this).data('src'),
		}).done(function(responce) {
		 responce = responce.split('#SITE#').join(mgBaseDir);
		  $('#plugin-read-modal .reveal-body').html(responce);
		});
        $('#plugin-read-modal .pluginName').html($(this).data('title'));
      });

      // обрабатывает клик по кнопке настроек в таблице плагинов
      $('body').on('click', '.main-table .plugSettings', function () {
        var pluginName = $(this).parents('tr').attr('id');
        var pluginTitle = $('tr[id='+pluginName+'] .p-name').text();        
        $(".plugins-menu-wrapper").hide();
        plugin.openPagePlugin(pluginName, pluginTitle);        
        $('#tiptip_holder').hide();
        $('#tiptip_holder').css('left','230px');
      });

      // Клик по активным плагинам из выпадающего меню, открывает страницу настроек
      $('body').on('click','.plugins-dropdown-menu li a', function () {
        var pluginName = $(this).attr('class');
        if(pluginName != 'all-plugins-settings') {
          var pluginTitle = $(this).text();
          plugin.openPagePlugin(pluginName, pluginTitle, true);
        } else {
          $('a[id=plugins]').click();
        }
        
        $('.admin-top-menu-list > li > a').removeClass('active-item');
        $('a[id=plugins]').addClass('active-item');
        $(".plugins-menu-wrapper").hide();

      });

      // Обработчик для загрузки нового плагина
      $('body').on('change', '#addPlugin', function() {

        plugin.addNewPlugin();
      });

      // Удаление плагина
      $('.admin-center').on('click', '.section-plugins .delete-order', function() {
        if(!$(this).parents('tr').find('.switch-input').prop( "checked" )) {
          var id = $(this).parents('tr').attr('id');
          plugin.deletePlugin(id);
        } else {
          alert(lang.PLUG_NEED_DEACTIVE);
        }
      });
      
      //Обновление плагина
      $('.admin-center').on('click', '#checkPluginsUpdate', function() {
        plugin.checkPluginsUpdate();
      });
      
      //Обновление плагина
      $('.admin-center').on('click', '.main-table .update-plugin', function() {
        var id = $(this).parents('tr').attr('id');
        if(id.length > 0) {
          plugin.updatePlugin(id);
        }
      });

      //изменение активности плагина
      $('.admin-center').on('click', '.main-table .plugins-active', function() {
        var pluginName = $(this).parents('tr').attr('id');
        if($('#'+$(this).attr('for')).prop('checked')) {
          plugin.deactivatePlugin(pluginName);
        } else {
          plugin.activatePlugin(pluginName);
        }
      });

    },

    // открывает страницу настроек плагина, если она существует
    openPagePlugin: function(pluginName, pluginTitle, havePage) {
      if(havePage || $('tr[id='+pluginName+']').attr('class')=='plugin-settings-on') {
        admin.show(pluginName, "plugin",'&pluginTitle='+pluginTitle,function() {
          admin.CURENT_PLUG_TITLE = pluginTitle;
         // $('.widget-table-title h4').text('Настройки плагина "'+pluginTitle+'"');
        });
      } else {
        alert(lang.PLUGIN_NOT_HAVE_SETTING);
      }
    },

    // активирует плагин
    activatePlugin: function(pluginName) {
      var pluginTitle = $('tr[id='+pluginName+'] .p-name').text();
      admin.ajaxRequest({
        mguniqueurl:"action/activatePlugin",
        pluginFolder: pluginName,
        pluginTitle: pluginTitle
      },
      (function(response) {
        admin.indication(response.status, response.msg);

        if(response.data.havePage){
          if($('.plugins-list-menu .sub-list').html() == undefined) {
            $('.plugins-list-menu').append('<ul class="sub-list"></ul>');
            $('.plugins-list-menu').addClass('has-menu');
          }
          $('.plugins-list-menu .sub-list').html('<li><a href="#" class="'+pluginName+'">'+pluginTitle+'</li>'+$('.plugins-list-menu .sub-list').html());
        }

        $('.info-panel .button-list').html('');
        if(response.data.newInformer) {
          $('.info-panel .button-list').html(response.data.newInformer);
        }   
        admin.refreshPanel();
      })
      );
    },

    // деактивирует плагин
    deactivatePlugin: function(pluginName) {
       var pluginTitle = $('tr[id='+pluginName+'] .p-name').text();
      admin.ajaxRequest({
        mguniqueurl:"action/deactivatePlugin",
        pluginFolder: pluginName,
        pluginTitle: pluginTitle
      },
      (function(response) {
        admin.indication(response.status, response.msg)
        $('.plugins-list-menu .sub-list .'+pluginName).parent('li').remove();
        if($('.plugins-list-menu .sub-list').html() == '') {
          $('.plugins-list-menu .sub-list').detach();
          $('.plugins-list-menu').removeClass('has-menu');
        }
        admin.hideWhiteArrowDown();
        $('tr[id='+pluginName+'] .action-list .plugin-settings-large').remove();        
        $('.info-panel .button-list a[rel='+pluginName+']').parents('li').remove();     
        admin.refreshPanel();      
      })
      );
    },

    addNewPlugin:function() {
     $('.img-loader').show();

      // установка плагина
      $(".newPluginForm").ajaxForm({
        type:"POST",
        url: "ajax",
        data: {
          mguniqueurl:"action/addNewPlugin"
        },
        cache: false,
        dataType: 'json',
        success: function(response) {
          admin.indication(response.status, response.msg);
          admin.show("plugins.php","adminpage",'');
          $('.img-loader').hide();
        }
      }).submit();
    },

     /**
     * Удаляет плагин из системы
     */
    deletePlugin: function(id) {
      if(confirm(lang.DELETE+'?')) {
        admin.ajaxRequest({
          mguniqueurl:"action/deletePlugin",
          id: id
        },
        (function(response) {
          admin.indication(response.status, response.msg);
          if(response.status == 'error') return false;
          $('.main-table tr[id='+id+']').remove();
         })
        );
      }
    },
    
    /*
     * Проверяет наличие обновления для плагинов
     */
    checkPluginsUpdate: function() {
      admin.ajaxRequest({
        mguniqueurl:"action/checkPluginsUpdate",
      },function(response) {
        admin.indication(response.status, response.msg);
        $('a[id=plugins]').trigger('click');
      });
    },
    
    /*
     * Обновляет плагин
     */
    updatePlugin: function(id) {
      admin.ajaxRequest({
        mguniqueurl:"action/updatePlugin",
        pluginName: id
      },function(response) {
        if(!response.data['last_version'] && response.status != 'error') {
          plugin.updatePlugin(id);
        } else {
          admin.indication(response.status, response.msg);
          if(response.status != 'error') {
            $('a[id=plugins]').trigger('click');
          }
        }
      });
    },

    //Скролл для плагина
    scrollToPlug: function() {
      var plId = cookie("pluginScrollEl");
      var scrollTop = $(plId).offset().top -100;
      $(document).scrollTop(scrollTop);  
    }
  };
})();

plugin.init();