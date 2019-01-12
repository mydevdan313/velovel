<?php

/**
 * Модель: Feedback
 *
 * Класс Models_Feedback реализует логику взаимодействия с формой обратной связи.
 * - Проверяет корректность ввода данных;
 * - Отправляет сообщения на электронные адреса пользователя и администраторов.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Model
 */
class Models_Feedback {

  // Электронный адрес пользователя.
  private $email;
  // Фамилия имя пользователя.
  private $fio;
  // Сообщение пользователя.
  private $message;

  /**
   * Проверяет корректность ввода данных.
   * <code>
   * $feedback = new Models_Feedback();
   * $arrayData = array(
   *   'fio' => 'Username',
   *   'email' => 'admin@mail.mail',
   *   'message' => 'Сообщение'
   * );
   * $result = $feedback->isValidData($arrayData);
   * var_dump($result);
   * </code>
   * @param array $arrayData массив с данными введенными пользователем.
   * @return bool|string $error сообщение с ошибкой в случае некорректных данных.
   */
  public function isValidData($arrayData) {
   
    $result = false;
    if (!preg_match('/^[-._a-zA-Z0-9]+@(?:[a-zA-Z0-9][-a-zA-Z0-9]{0,61}+\.)+[a-zA-Z]{2,6}$/', $arrayData['email'])) {
      // $error = '<span class="error-email">E-mail не существует!</span>';
      $error = '<span class="error-email">'.MG::restoreMsg('msg__feedback_wrong_email').'</span>';
    } elseif (!trim($arrayData['message'])) {
      // $error = 'Введите текст сообщения!';
      $error = MG::restoreMsg('msg__feedback_no_text');
    }

    if(MG::getSetting('useCaptcha')=="true" && MG::getSetting('useReCaptcha') != 'true'){
      if (strtolower($arrayData['capcha']) != strtolower($_SESSION['capcha'])) {
        // $error .= "<span class='error-captcha-text'>Текст с картинки введен неверно!</span>";
        $error .= "<span class='error-captcha-text'>".MG::restoreMsg('msg__captcha_incorrect')."</span>";
      }    
    }
    if (!MG::checkReCaptcha()) {
      $error .= "<span class='error-captcha-text'>".MG::restoreMsg('msg__recaptcha_incorrect')."</span>";
    }

    // Если нет ошибок, то заносит информацию в поля класса.
    if ($error) {
      $result = $error;
    } else {

      $this->fio = trim($arrayData['fio']);
      $this->email = trim($arrayData['email']);
      $this->message = trim($arrayData['message']);
      $result = false;
    }

    $args = func_get_args();

    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }
  
  /**
   * Получает сообщение из закрытых полей класса.
   * <code>
   * $feedback = new Models_Feedback();
   * $arrayData = array(
   *   'fio' => 'Username',
   *   'email' => 'admin@mail.mail',
   *   'message' => 'Сообщение'
   * );
   * $feedback->isValidData($arrayData);
   * echo $feedback->getMessage(); // 'Сообщение'
   * </code>
   * @return string
   */
  public function getMessage() {
    return $this->fio.": ".$this->message;
  }

   /**
   * Получает email из закрытых полей класса.
   * <code>
   * $feedback = new Models_Feedback();
   * $arrayData = array(
   *   'fio' => 'Username',
   *   'email' => 'admin@mail.mail',
   *   'message' => 'Сообщение'
   * );
   * $feedback->isValidData($arrayData);
   * echo $feedback->getEmail(); // 'admin@mail.mail'
   * @return string
   */
  public function getEmail() {
    return $this->email;
  }

  /**
   * Получает имя отправителя из закрытых полей класса.
   * <code>
   * $feedback = new Models_Feedback();
   * $arrayData = array(
   *   'fio' => 'Username',
   *   'email' => 'admin@mail.mail',
   *   'message' => 'Сообщение'
   * );
   * $feedback->isValidData($arrayData);
   * echo $feedback->getFio(); // 'Username'
   * @return string
   */
  public function getFio() {
    return $this->fio;
  }

}