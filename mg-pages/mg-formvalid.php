<?php
/*
 * Страница для обработки форм. При выводе формы необходимо добавить строку 
 *  <?php mgFormValid("name_form", "action");?>  
 *  name_form - название формы - необходимо указать атрибут name= name_form в теге <form> - уникальное имя формы на странице
 *  action - страница куда отправляются данные
 *  Если action="" то указывается адрес страницы с формой, например feedback - на странице с формой обратной связи.
 * 
 * При формировании страницы в сессию сохраняется имя формы и значение ключа - уникальное для этой формы.
 * В ответ на страницу возвращается вставка js-кода - в action формы подставляется значение страницы с проверкой, добавляется скрытое поле
 * Если форму отправляет человек - значение меняется и ajax запросом проверяется значение ключа из формы и из сессии.
 * Если значение есть и совпадают, данные из формы отправляются на страницу action 
 * Если значения в сессии нет или они разные, значит данные отправляются ботом. 
 * В таком случае данные из формы не отправляются. 
 * Если в config.ini прописана деректива LOG_USER_AGENT = 1 в корне сайта создается файл log_user_agent.txt,
 * где сохпраняются все подозрителные обращения к формам.
 * 
 */
/*
 * проверка наличия значения ключа в сессии и совпадение с переданным значением
 */
if(!empty($_POST['validformcheck'])&&$_POST['validformcheck']==1) {
  $response = array(
        'status' => 'error',
      );
  if(!empty($_SESSION['valid_form'][$_POST['validform']])&&$_SESSION['valid_form'][$_POST['validform']] == $_POST['value']) {
    $response = array(
        'status' => 'success',
      );     
  } elseif(LOG_USER_AGENT == 1) {
    logerUserAgent();
  }
  header('Content-Type: application/json');
  echo json_encode($response);
  exit();
}
/*
 * Функция в сормировании страницы с формой при наличии  <?php mgFormValid("name_form", "action");?>  
 * Добавляет в сессию в массив valid_form пару - имя формы - ключ
 * Возвращает js код для обработки формы - отправки на проверку, атем отправка на обработку формы.
 */

  function mgFormValid($nameForm, $action){
    if ($nameForm) {
      $key = time();
      $_SESSION['valid_form']= array($nameForm=> $key);    
      $html = '<script>'
        . '$("form[name='.$nameForm.']").attr("action", "mg-formvalid");'
        . '$("form[name='.$nameForm.']").attr("onsubmit", "return false");'
        . '$("form[name='.$nameForm.']").append("<input type=\'hidden\' name=\'validformcheck\' value=1>"); '
        . '$("form[name='.$nameForm.']").append("<input type=\'hidden\' name=\'validform\' value=\''.$action.'\'>");'
        . '$("body").on("click", "form[name='.$nameForm.'] input[type=\'submit\'] ", function(event){'
        . '$("input[type=\'submit\']").after("<div style=\"text-align: center;\"><img src=\''.PATH_SITE_TEMPLATE.'/images/loader.gif\'></div>");'
        . '$(this).hide();'
        . '$("form[name='.$nameForm.'] input[name=validformcheck]").remove();'
        . '$("form[name='.$nameForm.'] input[name=\'validform\']").val('.$key.'); 
           $("form[name='.$nameForm.']").attr("action", "");
              $("form[name='.$nameForm.'] input[type=submit]").attr("type", "text" );
                var spam = $(this).parents("form").find("input[name=validform]").val();
                $.ajax({
                  type: "POST",
                  url: mgBaseDir + "/mg-formvalid",
                  data: "validformcheck=1&validform='.$nameForm.'&value="+spam,
                  dataType: "json",
                  cache: false,
                  success: function(response){
                    if("success" == response.status){
                      $("form[name='.$nameForm.']").removeAttr("onsubmit");
                   
                     $("form[name='.$nameForm.']").submit();
                    }
                  }
                });
                return false;
        });'
        . '</script>';
    }
    echo $html;
}
/*
 * Запись обращения к форме от бота, если в config.ini прописана деректива LOG_USER_AGENT == 1
 * Создает log_user_agent.txt в корне сайта
 */
 function logerUserAgent() {
   $fileName = 'log_user_agent.txt';
   $string = '';
   if (!file_exists($fileName)) {
     $string = "Файл создан при обращении к классу обработки форм, при условии, \r\n"
       . "что проверка на антиспам не прошла, что означает о вероятности обращения робота.\r\n В файл записана информация по обращению:\r\n"
       . "ip адрес, дата, тип запроса, адрес страницы, user-agent, реферал. \r\n"
       . "В дальнейшем необходимо добавить в htaccess запрет на доступ user-agent из этого списка. Подробнее см. в документации: http://wiki.moguta.ru"."\r\n";
   }
    $text = $_SERVER['REMOTE_ADDR'].' "'.date(DATE_RFC2822).'" "'.$_SERVER['REQUEST_METHOD'].
      ' '.$_SERVER['SERVER_PROTOCOL'].'" "'.$_POST['validform'].'" "'.$_SERVER['HTTP_USER_AGENT'].'" '.$_SERVER['HTTP_REFERER'];
    $string .= $text."\r\n";
    $f = fopen($fileName, 'a+');
    fwrite($f, $string);
    fclose($f);
  }