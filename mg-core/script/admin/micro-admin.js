var javascripts = [];

function includeJS(path) {

  for (var i = 0; i < javascripts.length; i++) {
    if (path == javascripts[i]) {
      // alert('JavaScript: ['+path+'] уже был подключен ранее!');
      return false;
    }
  }
  javascripts.push(path);
  $.ajax({
    url: path,
    dataType: "script", // при типе script JS сам инклюдится и воспроизводится без eval
    async: false
  });
}

var admin = (function () {

  return {

  	init: function() {

      $(function() {
        $.when(
          admin.getSettingFromDB('sessionToDB'),
          admin.getSettingFromDB('sessionAutoUpdate')
        ).then(function(sessionToDBResp, sessionAutoUpdateResp, sessionLifeTimeResp) {
          var sessionToDB = sessionToDBResp[0].data.sessionToDB;
          var sessionAutoUpdate = sessionAutoUpdateResp[0].data.sessionAutoUpdate;

          admin.ajaxRequest({
            mguniqueurl: "action/getSessionLifeTime"
          }, function (response) {
            admin.SESSION_LIFE_TIME = response.data.sessionLifeTime;
            
            if (sessionAutoUpdate != 'false') {
              setInterval(function() {
                admin.ajaxRequest({
                  mguniqueurl: "action/updateSession",
                },
                function (response) {
                });
              }, (admin.SESSION_LIFE_TIME/2*1000));
            } else {
              admin.startCountDown();
            }
          });
        });
      });

  		// обработка клика по кнопки - сбросить кэш
  		$('body').on('click', '.clear-cache', function () {
  		  admin.ajaxRequest({
  		    mguniqueurl: "action/clearСache",
  		  },
  		    function (response) {
  		      location.reload();
  		    }
  		  );
  		});

      $('body').on('click', '.closeClass', function() {
        $(this).parent().parent().detach();
      });

  	},


		ajaxRequest: function (data, callBack, loader, dataType, noAlign) {
		  if (!dataType)
		    dataType = 'json';
		  $.ajax({
		    type: "POST",
		    url: "ajax",
		    data: data,
		    cache: false,
		    dataType: dataType,
		    success: callBack,
		    beforeSend: function () {},
		    complete: function () {
		      // выполнение стека отложенных функций после AJAX вызова    
		      if (admin.AJAXCALLBACK) {
		        //debugger;
		        admin.AJAXCALLBACK.forEach(function (element, index, arr) {
		          eval(element.callback).apply(this, element.param);
		        });
		        admin.AJAXCALLBACK = null;
		      }
		    },
		    error: function (request, status, error) {}
		  });
		},

    startCountDown: function() {
      admin.SESSION_CHECK_INTERVAL = setInterval(function() {
        admin.ajaxRequest({
          mguniqueurl: "action/getSessionLifeTime",
          a: 'ping'
        }, function (response) {
          admin.TIME_WITHOUT_USER = response.data.timeWithoutUser;

          if (response.data.sessionLifeTime <= 300) {
            var alert = 'alert';
            var sessInfo = $('div.session-info .sess-info-body');
            var sessInfoText = 
                "<div>Вы не совершали никаких действий в течение <strong>" 
                + Math.floor(admin.TIME_WITHOUT_USER/60) + "</strong> мин.<br /> До конца сессии осталось <strong>"
                + Math.ceil(response.data.sessionLifeTime/60)+"</strong> мин.</div>";

            var closeClass = 'canClose';
            if (response.data.sessionLifeTime <= 0) {
              var closeClass = '';
              alert = '';
              sessInfoText = "<div class='help-text'>Вы не совершали никаких действий в течение "
                +"<strong>" + Math.round(admin.SESSION_LIFE_TIME/60) + "</strong> мин. <br />"
                +"Ваша сессия была закрыта.</div>"
                +"<form method='POST' action='" + $('.site').val() + "/enter'>"
                +"<ul class='form-list' style='width:100%;'><li><span>Email:</span>"
                +"<input type='text' name='email'></li>"
                +"<li><span>Пароль:</span>"
                +"<input type='password' name='pass'></li></ul>"
                +"<button type='submit' class='button success'>Войти</button></form>";
              sessInfo.html(sessInfoText);
              sessInfo.parent('.session-info').removeClass('alert');
              clearInterval(admin.SESSION_CHECK_INTERVAL);
            }

            if (sessInfo.length) {
              sessInfo.html(sessInfoText);
            } else {
              $('body').append("<div class='mg-admin-html'><div class='reveal-overlay' style='display:block;'>"
                  +"<div class='reveal xssmall closeClass' id='session-info-overlay' style='display:block;width:400px;'>"
                  +"<div class='session-info-overlay'>"
                  +"</div><div class='session-info "+alert+"'>"
                  +'<div class="reveal-header">'
                  +'<h2><span id="modalTitle">Moguta.CMS</span></h2>'
                  +'</div><div class="reveal-body">'
                  +"<div class='sess-info-body'>" + sessInfoText + "</div>"
                  +"</div></div></div></div></div>");
            }
          }
        });

        admin.TIME_WITHOUT_USER+=60;
      }, 60000);
    },

    getSettingFromDB: function (setting) {
      return $.ajax({
        type: "POST",
        url: mgBaseDir+"/ajaxrequest",
        data:{
          actionerClass: 'Ajaxuser', // класс Pactioner в Pactioner.php - в папке плагина
          action: 'getSetting',
          setting: setting,
        },
        cache: false,
        dataType: 'json',
      });
    },

	};
})();

admin.init();