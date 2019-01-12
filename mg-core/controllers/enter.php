<?php

/**
 * Контроллер: Enter
 * 
 * Класс Controllers_Enter обрабатывает действия пользователей на странице авторизации.
 * - Аутентифицирует пользовательские данные;
 * - Проверяет корректность ввода данных с формы авторизации;
 * - При успешной авторизации перенаправляет пользователя в личный кабинет;
 * - При необходимых настройках включает защиту от подбора паролей;
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Controller
 */
class Controllers_Enter extends BaseController {

  function __construct() {

    // Разлогиниваем пользователя.
    if (URL::getQueryParametr('logout')) {
      User::logout();
      header('Location: '.$_SERVER['HTTP_REFERER']);
    }
    
    // Пользователь уже авторизован, отправляем его в личный кабинет.
    if (User::isAuth()) {
      header('Location: '.SITE.'/personal');
    }

    $data = array(
      'meta_title' => 'Авторизация',
      'meta_keywords' => !empty($model->currentCategory['meta_keywords'])?$model->currentCategory['meta_keywords']:"Авторизация,вход, войти в личный кабинет",
      'meta_desc' => !empty($model->currentCategory['meta_desc'])?$model->currentCategory['meta_desc']:"Авторизуйтесь на сайте и вы получите дополнительные возможности, недоступные для обычных пользователей.",
    );

    // Если загрузка произведена по ссылке для отмены блокировки авторизации.
    if (URL::getQueryParametr('unlock')) {
      if (URL::getQueryParametr('unlock') == $_SESSION['unlockCode']) {
        unset($_SESSION['loginAttempt']);
        unset($_SESSION['blockTimeStart']);
        unset($_SESSION['unlockCode']);
      }
    }

    // Если пользователь не авторизован, проверяется  правильность ввода данных и количество неудачных попыток.
    if (!User::isAuth() && (isset($_POST['email']) || isset($_POST['pass']))) {

      $loginAttempt = (int) MG::getSetting('loginAttempt')?MG::getSetting('loginAttempt'):5;

      $capcha = (isset($_POST['capcha'])?$_POST['capcha']:false);
      unset($_POST['capcha']);

      if (!User::auth(URL::get('email'), URL::get('pass'), $capcha)) {
        if ($_SESSION['loginAttempt'] < 2) {
          // $data['msgError'] = '<span class="msgError">'.
          //   'Неправильная пара email-пароль! Авторизоваться не удалось.'.'</span>';
          $data['msgError'] = '<span class="msgError">'.MG::restoreMsg('msg__enter_failed').'</span>';
        } elseif ($_SESSION['loginAttempt'] < $loginAttempt) {
          // $data['msgError'] = '<span class="msgError">'.
          //   'Неправильно введен код с картинки! Авторизоваться не удалось.'.
          //   ' Количество оставшихся попыток - '.
          //   ($loginAttempt - $_SESSION['loginAttempt']).'</span>';
          $tmp = $loginAttempt - $_SESSION['loginAttempt'];
          if (MG::getSetting('useReCaptcha') == 'true' && MG::getSetting('reCaptchaSecret') && MG::getSetting('reCaptchaKey')) {
            $data['msgError'] = '<span class="msgError">'.MG::restoreMsg('msg__enter_recaptcha_failed',array('#COUNT#' => $tmp)).'</span>';
            $data['checkCapcha'] = MG::printReCaptcha();
          }
          else{
            $data['msgError'] = '<span class="msgError">'.MG::restoreMsg('msg__enter_captcha_failed',array('#COUNT#' => $tmp)).'</span>';
            $data['checkCapcha'] = '<div class="checkCapcha">
              <img style="margin-top: 5px; border: 1px solid gray; background: url("'.
              PATH_TEMPLATE.'/images/cap.png")" src = "captcha.html" width="140" height="36">
              <div>Введите текст с картинки:<span class="red-star">*</span> </div>
              <input type="text" name="capcha" class="captcha">';
          }
          
        } else {
          if (!isset($_SESSION['blockTimeStart'])) {  
            // Начало отсчета  времени блокировки на 15 мин.
            $_SESSION['blockTimeStart'] = time();
            $_SESSION['unlockCode'] = md5('mg'.time());
            $this->sendUnlockMail($_SESSION['unlockCode'],$_POST['email']);
          }
          // $data['msgError'] = '<span class="msgError">'.
          //   'В целях безопасности возможность авторизации '.
          //   'заблокирована на 15 мин. Отсчет времени от '.
          //   date("H:i:s", $_SESSION['blockTimeStart']).'</span>';
          $data['msgError'] = '<span class="msgError">'.MG::restoreMsg('msg__enter_blocked',array('#TIME#' => date("H:i:s", $_SESSION['blockTimeStart']))).'</span>';
        }
      } else {
        $this->successfulLogon();
      }
    }

    $this->data = $data;
  }

  /**
   * Перенаправляет пользователя на страницу в личном кабинете.
   * <code>
   * $model = new Controllers_Enter;
   * $model->successfulLogon();
   * </code>
   * @return void
   */
  public function successfulLogon() {       
    
    if (empty($_REQUEST['location']) || 
          $_REQUEST['location'] == SITE.$_SERVER['REQUEST_URI'] || 
          $_REQUEST['location'] == $_SERVER['REQUEST_URI'] ||
          $_REQUEST['location'] == '/mg-admin') {    
 
      header('Location: '.$_SERVER['HTTP_REFERER']);
      exit;
    }
    
    header('Location: '.$_REQUEST['location']);
    exit;
  }

  /**
   * Проверяет корректность ввода данных с формы авторизации.
   * <code>
   * $model = new Controllers_Enter;
   * $res = $model->validForm();
   * var_dump($res);
   * </code>
   * @return bool
   */
  public function validForm() {
    $email = URL::getQueryParametr('email');
    $pass = URL::getQueryParametr('pass');

    if (!$email || !$pass) {
      // При первом показе, не выводить ошибку.
      if (strpos($_SERVER['HTTP_REFERER'], '/enter')) {
        $this->data = array(
          // 'msgError' => '<span class="msgError">'.'Одно из обязательных полей не заполнено!'.'</span>',
          'msgError' => '<span class="msgError">'.MG::restoreMsg('msg__enter_field_missing').'</span>',
          'meta_title' => 'Авторизация',
          'meta_keywords' => !empty($model->currentCategory['meta_keywords'])?$model->currentCategory['meta_keywords']:"Авторизация,вход, войти в личный кабинет",
          'meta_desc' => !empty($model->currentCategory['meta_desc'])?$model->currentCategory['meta_desc']:"Авторизуйтесь на сайте и вы получите дополнительные возможности, недоступные для обычных пользователей.",
        );
      }
      return false;
    }
    return true;
  }

  /**
   * Метод отправки письма администратору с ссылкой для отмены блокировки авторизации.
   * @param string $unlockCode код разблокировки
   * @param string $postEmail
   * @return bool 
   */
  private function sendUnlockMail($unlockCode,$postEmail) {
    $link = '<a href="'.SITE.'/enter?unlock='.$unlockCode.'" target="blank">'.SITE.'/enter?unlock='.$unlockCode.'</a>';
    $siteName = MG::getOption('sitename');
    
    $paramToMail = array(
      'siteName' => $siteName,
      'link' => $link,
      'lastEmail' => $postEmail,
    );
    
    $message = MG::layoutManager('email_unclockauth', $paramToMail);
    $emailData = array(
      'nameFrom' => $siteName,
      'emailFrom' => MG::getSetting('noReplyEmail'),
      'nameTo' => 'Администратору сайта '.$siteName,
      'emailTo' => MG::getSetting('adminEmail'),
      'subject' => 'Подбор паролей на сайте '.$siteName.' предотвращен!',
      'body' => $message,
      'html' => true
    );
    
    if (Mailer::sendMimeMail($emailData)) {
      return true;
    }
    
    return false;
  }

}