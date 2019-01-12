<?php

/**
 * Модель: Forgotpass
 *
 * Класс Models_Forgotpass реализует логику восстановления пароля пользователей.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Model
 */
class Models_Forgotpass{

  /**
   * Генерация случайного хэша.
   * <code>
   * $email = 'admin@mail.mail';
   * $hash = Models_Forgotpass::getHash($email);
   * echo $hash;
   * </code>
   * @param string $string строка на основе которой готовится хэш.
   * @return string случайный хэш
   */
  public function getHash($string){
    $hash = htmlspecialchars(crypt($string));
    return $hash;
  }

  /**
   * Метод записывает хэш в таблицу пользователей.
   * <code>
   * $email = 'admin@mail.mail';
   * $hash = '$1$CcS6ghRe$QF1cM4JKQfnOZRKDVT63L.';
   * Models_Forgotpass::sendHashToDB($email, $hash);
   * </code>
   * @param string $email электронный адрес пользователя, для которого записываем хэш.
   * @param string $hash хэш.
   * @return bool результат выполнения операции.
   */
  public function sendHashToDB($email, $hash){
    if(DB::query('
        UPDATE `'.PREFIX.'user`
        SET `restore` = "%s"
        WHERE email = "%s"
      ', $hash, $email)){
      return true;
    }
    return false;
  }

  /**
   * Отправка письма со ссылкой на восстановление пароля.
   * <code>
   * $userId = 1;
   * $email = 'admin@mail.mail';
   * $hash = '$1$CcS6ghRe$QF1cM4JKQfnOZRKDVT63L.';
   * $siteName = MG::getOption('sitename');
   * 
   * $emailMessage = MG::layoutManager('email_forgot',
   *   array(          
   *     'siteName'=>$siteName,
   *     'email'=>$email,
   *     'hash'=> $hash,
   *     'userId'=> $userId,
   *     'link' => SITE.'/forgotpass?sec='.$hash.'&id='.$userId
   *   )
   * );   
   * 
   * $emailData = array(
   *   'nameFrom' => $siteName,
   *   'emailFrom' => MG::getSetting('noReplyEmail'),
   *   'nameTo' => 'Пользователю сайта '.$siteName,
   *   'emailTo' => $email,
   *   'subject' => 'Восстановление пароля на сайте '.$siteName,
   *   'body' => $emailMessage,
   *   'html' => true
   * );
   * 
   * Models_Forgotpass::sendUrlToEmail($emailData);
   * </code>
   * @param array $emailData массив с передаваемыми данными.
   * @return bool результат выполнения операции.
   */
  public function sendUrlToEmail($emailData){
    if(Mailer::sendMimeMail($emailData)){
      return true;
    }
    return false;
  }

  /**
   * Активация пользователя по переданному id.
   * <code>
   * $userId = 15;
   * Models_Forgotpass::activateUser($userId);
   * </code>
   * @param int $id
   */
  public function activateUser($id){
    $data = array(
      'activity' => 1,
    );
    USER::update($id, $data, 1);
  }

}