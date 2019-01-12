<?php

/**
 * Класс User - предназначен для работы с учетными записями пользователей системы.
 * Доступен из любой точки программы.
 * Реализован в виде синглтона, что исключает его дублирование.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */
class User {

  static private $_instance = null;
  private $auth = array();
  static $accessStatus = array(0 => 'Разрешен', 1 => 'Заблокирован');
  static $groupName = array(1 => 'Администратор', 2 => 'Пользователь', 3 => 'Менеджер', 4 => 'Модератор');

  private function __construct() {
     // Если пользователь был авторизован, то присваиваем сохраненные данные.
    if (isset($_SESSION['user']) && isset($_SESSION['userAuthDomain'])) {
      if ($_SESSION['userAuthDomain'] == $_SERVER['SERVER_NAME']) {
        if ((int)MG::getSetting('checkAdminIp') === 1) {
          if (!empty($_SESSION['user']->hash)&& ($_SESSION['user']->hash === md5($_SESSION['user']->email.$_SESSION['user']->date_add.$_SERVER['REMOTE_ADDR']) )) {
            $this->auth = $_SESSION['user'];
          } else {
            $this->auth = null;
            unset($_SESSION['user']);
            unset($_SESSION['loginAttempt']);
            unset($_SESSION['blockTimeStart']);
            //Удаляем данные о корзине.
         } 
        } else {
          $this->auth = $_SESSION['user'];
        }               
      }
    }
  }

  private function __clone() {
    
  }

  private function __wakeup() {
    
  }

  /**
   * Возвращает единственный экземпляр данного класса.
   * <code>
   * $obj = User::getInstance();
   * </code>
   * @return object объект класса User.
   */
  static public function getInstance() {
    if (is_null(self::$_instance)) {
      self::$_instance = new self;
    }
    return self::$_instance;
  }

  /**
   * Инициализирует объект данного класса User.
   * @access private
   * @return void
   */
  public static function init() {
    self::getInstance();
  }

  /**
   * Возвращает авторизированнго пользователя.
   * <code>
   * $result = User::getThis();
   * viweData($result);
   * </code>
   * @return array
   */
  public static function getThis() {
    return self::$_instance->auth;
  }

  /**
   * Добавляет новую учетную запись пользователя в базу сайта.
   * <code>
   * $userInfo = array(
   *  'id' => null,                   // id пользователя, при добавлении null
   *  'email' => 'mail@email.com',    // почта пользователя
   *  'pass' => '123456',             // пароль
   *  'name' => 'username',           // имя пользователя
   *  'birthday' => '01.03.2018',     // день рождения пользователя
   *  'sname' => 'usersname',         // фамилия 
   *  'address' => 'adr',             // адрес
   *  'phone' => '+7 (111) 111-11-11',// телефон
   *  'blocked' => 0,                 // флаг блокировки пользователя (1 = заблокирован)
   *  'activity' => 1,                // флаг активности пользователя (0 = не активен)
   *  'role' => 2                     // группа пользователя (1 - администратор, 2 - пользователь, 3 - менеджер, 4 - модератор)
   * );
   * User::add($userInfo);
   * </code>
   * @param array $userInfo массив значений для вставки в БД [Поле => Значение].
   * @return bool
   */
  public static function add($userInfo) {
    $result = false;
    
    // Если пользователя с таким емайлом еще нет.
    if (!self::getUserInfoByEmail($userInfo['email'])) {
      $userInfo['pass'] = crypt($userInfo['pass']);      
             
      foreach ($array as $k => $v) {
         if($k!=='pass') {
          $array[$k] = htmlspecialchars_decode($v);
          $array[$k] = htmlspecialchars($v);       
         }
      }
      if (!isset($userInfo['activity'])) {
        $userInfo['activity'] = 0;
        if (MG::getSetting('confirmRegistration') == 'false') {
          $userInfo['activity'] = 1;
        }
      }
      $userInfo['date_add'] = date('Y-m-d H:i:s');
      unset($userInfo['id']);
      if (DB::buildQuery('INSERT INTO  `'.PREFIX.'user` SET ', $userInfo)) {
        $id = DB::insertId();
        $result = $id;     
      }
    } else {
      
      $result = false;
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Удаляет учетную запись пользователя из базы.
   * <code>
   * User::delete(3);
   * </code>
   * @param int $id id пользователя, чью запись следует удалить.
   * @return bool
   */
  public static function delete($id) {
    $res = DB::query('SELECT `role` FROM `'.PREFIX.'user` WHERE id = '.DB::quote($id));
    $role = DB::fetchArray($res);
    
    // Нельзя удалить первого пользователя, поскольку он является админом
    if ($role['role'] == 1 ) {
      $res = DB::query('SELECT `id` FROM `'.PREFIX.'user` WHERE `role` = 1');
      if (DB::numRows($res) == 1 || $_SESSION['user']->id == $id) {
        return false;
      }
    }
    DB::query('DELETE FROM `'.PREFIX.'user` WHERE id = '.DB::quote($id));
    return true;
  }

  /**
   * Обновляет учетную запись пользователя.
   * <code>
   * $data = array(
   *  'id' => 14,                     // id пользователя
   *  'email' => 'mail@email.com',    // почта пользователя
   *  'pass' => '123456',             // пароль
   *  'name' => 'username',           // имя пользователя
   *  'birthday' => '01.03.2018',     // день рождения пользователя
   *  'sname' => 'usersname',         // фамилия 
   *  'address' => 'adr',             // адрес
   *  'phone' => '+7 (111) 111-11-11',// телефон
   *  'blocked' => 0,                 // флаг блокировки пользователя (1 = заблокирован)
   *  'activity' => 1,                // флаг активности пользователя (0 = не активен)
   *  'role' => 2                     // группа пользователя (1 - администратор, 2 - пользователь, 3 - менеджер, 4 - модератор)
   * );
   * User::update(14, $data);
   * </code>
   * @param int $id id пользователя.
   * @param array $data массив значений для вставки в БД 
   * @param bool $authRewrite false = перезапишет данные в сессии детущего пользователя, на полученные у $data.
   * @return bool
   */
  public static function update($id, $data, $authRewrite = false) {
    $userInfo = USER::getUserById($id);

    foreach ($data as $k => $v) {
      if($k!=='pass') {
       $v = htmlspecialchars_decode($v);    
       $data[$k] = htmlspecialchars($v);
        
      }
    }   

      
    // только если пытаемся разжаловать админа, проверяем,
    // не является ли он последним админом
    // Без админов никак нельзя!
    if ($userInfo->role == '1' && $data['role'] != '1') {
      $countAdmin = DB::query('
     SELECT count(id) as "count"
      FROM `'.PREFIX.'user`    
      WHERE role = 1
    ');
      if ($row = DB::fetchAssoc($countAdmin)) {
        if ($row['count'] == 1) {// остался один админ    
          $data['role'] = 1; // не даем разжаловать админа, уж лучше плохой чем никакого :-)
        }
      }
    }

    DB::query('
     UPDATE `'.PREFIX.'user`
     SET '.DB::buildPartQuery($data).'
     WHERE id = '.DB::quote($id));

    if (!$authRewrite) {
      foreach ($data as $k => $v) {
        self::$_instance->auth->$k = $v;
      }
      $_SESSION['user'] = self::$_instance->auth;
    }

    return true;
  }

  /**
   * Разлогинивает авторизованного пользователя.
   * <code>
   * User::logout();
   * </code>
   */
  public static function logout() {
    self::getInstance()->auth = null;
    unset($_SESSION['user']);
    unset($_SESSION['cart']);
    unset($_SESSION['loginAttempt']);
    unset($_SESSION['blockTimeStart']);
    //Удаляем данные о корзине.
    SetCookie('cart', '', time());
    setcookie (session_id(), "", time() - 3600);
    session_destroy();
    session_write_close();
    header('Location: '.SITE.'/enter');
  }

  /**
   * Очищает внутренний массив с данными пользователя
   * @access private
   */
  public static function clearAuth() {
    self::getInstance()->auth = array();
  }

  /**
   * Аутентифицирует данные, с помощью криптографического алгоритма.
   * <code>
   * User::auth('mail@email.com', '123456');
   * </code>
   * @param string $email емайл.
   * @param string $pass пароль.
   * @param string|null $cap captcha
   * @return bool
   */
  public static function auth($email, $pass, $cap=null) {
    // проверка заблокирована ли авторизация,
    if (isset($_SESSION['blockTimeStart'])) {
      $period = time() - $_SESSION['blockTimeStart'];
      if ($period < 15 * 60) {
        return false;
      } else {
        unset($_SESSION['loginAttempt']);
        unset($_SESSION['blockTimeStart']);
      }
    }

    $result = DB::query('
      SELECT *
      FROM `'.PREFIX.'user`
      WHERE email ='.DB::quote($email));

    // если был введен код капчи, 

    if (MG::getSetting('useReCaptcha') == 'true' && MG::getSetting('reCaptchaSecret') && MG::getSetting('reCaptchaKey')) {
      if (MG::checkReCaptcha()) {
        $cap = $_SESSION['capcha'];
      }
    }

    $loginAttempt = (int) MG::getSetting('loginAttempt')?MG::getSetting('loginAttempt'):5;
    if (($_SESSION['loginAttempt']>=2 && $_SESSION['loginAttempt'] < $loginAttempt) && (strtolower($cap) != strtolower($_SESSION['capcha']))) {
      $_SESSION['loginAttempt'] += 1;
      return false;
    }

    if ($row = DB::fetchObject($result)) {
      if ($row->pass == crypt($pass, $row->pass)) {
        if ((int)MG::getSetting('checkAdminIp')  === 1) {
          $row->hash = md5($row->email.$row->date_add.$_SERVER['REMOTE_ADDR']);
        }        
        self::$_instance->auth = $row;
        $_SESSION['userAuthDomain'] = $_SERVER['SERVER_NAME'];
        $_SESSION['user'] = self::$_instance->auth;
       // $_SESSION['loginAttempt']='';
        return true;
      }
    }
    // если в настройках блокировка отменена, то количество попыток не суммируется.
    $lockAuth = MG::getOption('lockAuthorization') == 'false' ? false : true;
    if ($lockAuth) {
      if (!isset($_SESSION['loginAttempt'])) {
        $_SESSION['loginAttempt'] = 0;
      }
      $_SESSION['loginAttempt'] += 1;
    }
    return false;
  }

  /**
   * Получает все данные пользователя из БД по ID.
   * <code>
   * $result = User::getUserById(14);
   * viewData($result);
   * </code>
   * @param int $id id пользователя.
   * @return array
   */
  public static function getUserById($id) {
    $result = false;
    $res = DB::query('
      SELECT *
      FROM `'.PREFIX.'user`
      WHERE id = "%s"
    ', $id);

    if ($row = DB::fetchObject($res)) {
      $result = $row;
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Получает все данные пользователя из БД по email.
   * <code>
   * $result = User::getUserById('mail@email.com');
   * viewData($result);
   * </code>
   * @param string $email почта пользователя.
   * @return array
   */
  public static function getUserInfoByEmail($email) {
    $result = false;
    $res = DB::query('
      SELECT *
      FROM `'.PREFIX.'user`
      WHERE email = "%s"
    ', $email);

    if ($row = DB::fetchObject($res)) {
      $result = $row;
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Проверяет, авторизован ли текущий пользователь.
   * <code>
   * $result = User::isAuth();
   * var_dump($result);
   * </code>
   * @return bool
   */
  public static function isAuth() {
    if (self::getThis()) {
      return true;
    }
    return false;
  }

  /**
   * Получает список пользователей.
   * <code>
   * $result = User::getListUser();
   * viewData($result);
   * </code>
   * @return array
   */
  public static function getListUser() {
    $result = false;
    $res = DB::query('
      SELECT *
      FROM `'.PREFIX.'user`
    ', $id);

    while ($row = DB::fetchObject($res)) {
      $result[] = $row;
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }
  
    /**
   * Проверяет права пользователя на выполнение ajax запроса.
   * <code>
   * USER::AccessOnly('1,4','exit()');
   * </code>
   * @param string $roleMask - строка с перечисленными ролями, которые имеют доступ,
   *   если параметр не передается, то доступ открыт для всех.
   *  1 - администратор,
   *  2 - пользователь,
   *  3 - менеджер,
   *  4 - модератор
   * @param bool нужно ли прерывать движок
   * @return bool or exit;
   * @deprecated
   */
  public static function AccessOnly($roleMask="1,2,3,4",$exit=null) {
    $thisRole = empty(self::getThis()->role)?'2':self::getThis()->role;
    
    if(strpos($roleMask,(string)$thisRole)!==false) {
      return true;
  	}
  	// мод для аяксовых запросов.
  	if($exit) {
  	  exit();
  	}
    return false;
  }
  
  /**
   * Возвращает дату последней регистрации пользователя.
   * <code>
   * $result = User::getMaxDate();
   * viewData($result);
   * </code>
   * @return array
   */
  public function getMaxDate() {
    $res = DB::query('
      SELECT MAX(date_add) as res 
      FROM `'.PREFIX.'user`');

    if ($row = DB::fetchObject($res)) {
      $result = $row->res;
    }

    return $result;
  }

  /**
   * Возвращает дату первой регистрации пользователя.
   * <code>
   * $result = User::getMinDate();
   * viewData($result);
   * </code>
   * @return array
   */
  public function getMinDate() {
    $res = DB::query('
      SELECT MIN(date_add) as res 
      FROM `'.PREFIX.'user`'
    );
    if ($row = DB::fetchObject($res)) {
      $result = $row->res;
    }
    return $result;
  }
  
  /**
   * Получает все email пользователя из БД.
   * <code>
   * $result = User::searchEmail('mail@email.com');
   * viewData($result);
   * </code>
   * @param string $email почтовый адрес пользователя.
   * @return array
   */
  public static function searchEmail($email) {
    $result = false;
    $res = DB::query('
      SELECT `email`
      FROM `'.PREFIX.'user`
      WHERE email LIKE '.DB::quote($email,1).'%');

    if ($row = DB::fetchObject($res)) {
      $result = $row;
    }
    return $result;    
    }
  /**
   * Выгружает список пользователей в CSV файл.
   * <code>
   * $listUserId = array(1, 5, 9, 15);
   * $result = User::exportToCsvUser($listUserId);
   * viewData($result);
   * </code>
   * @param array $listUserId массив с id пользователей для выгрузки (необязаьельно)
   * @return string
   */
  public function exportToCsvUser($listUserId=array()) {
  
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream;");
    header("Content-Type: application/download");
    header("Content-Disposition: attachment;filename=data.csv");
    header("Content-Transfer-Encoding: binary ");

    $csvText = '';
    $csvText .= '"email";"Имя";"Фамилия";"Адрес";"Телефон";"День рождения";"Статус";"Группа";"Дата регистрации";"Доступ к кабинету";"Юр.лицо";"Юр.адрес";"ИНН";"КПП";"Банк";"БИК";"К/Сч";"Р/Сч";"IP";'."\n";
    
    Storage::$noCache = true;
    $page = 1;
    // получаем максимальное количество заказов, если выгрузка всего ассортимента
    if(empty($listUserId)) {
      $res = DB::query('
        SELECT count(id) as count
        FROM `'.PREFIX.'user` WHERE id > 0');
      if ($user = DB::fetchAssoc($res)) {
        $count = $user['count'];
      }
      $maxCountPage = ceil($count / 500);
    } else {
      $maxCountPage = ceil(count($listUserId) / 500);
    }
    $listId = implode(',', $listUserId);
    for ($page = 1; $page <= $maxCountPage; $page++) {      
      URL::setQueryParametr("page", $page);
      $sql = 'SELECT * FROM `'.PREFIX.'user` WHERE id > 0';
      if(!empty($listUserId)) {  
        $sql .= ' WHERE `id` IN ('.DB::quote($listId,1).')';     
      }
      $navigator = new Navigator($sql, $page, 500); //определяем класс  
      $users = $navigator->getRowsSql();
      foreach ($users as $row) {
        $csvText .= self::addUserToCsvLine($row);
        }
      }
    
    $csvText = substr($csvText, 0, -2); // удаляем последний символ '\n'
        
    $csvText = mb_convert_encoding($csvText, "WINDOWS-1251", "UTF-8");
    if(empty($listUserId)) {
      echo $csvText;
      exit;
    } else{
      $date = date('m_d_Y_h_i_s');
      file_put_contents('data_csv_'.$date.'.csv', $csvText);
      $msg = 'data_csv_'.$date.'.csv';
    }
    return $msg;
  }

  /**
   * Добавляет пользователя в CSV выгрузку.
   * @access private
   * @param array $row запись о пользователе.
   * @return string
   */
  public function addUserToCsvLine($row) {
    $row['address'] = '"' . str_replace("\"", "\"\"", $row['address']) . '"';
    $row['adress'] = '"' . str_replace("\"", "\"\"", $row['adress']) . '"';
   
    $row['email'] = '"' . str_replace("\"", "\"\"", $row['email']) . '"';
    $row['role'] = '"' . str_replace("\"", "\"\"", self::$groupName[$row['role']]) . '"';
    $row['name'] = '"' . str_replace("\"", "\"\"", $row['name']) . '"';
    $row['sname'] = '"' . str_replace("\"", "\"\"", $row['sname']).'"';
    $row['phone'] = '"' . str_replace("\"", "\"\"", $row['phone']) . '"';
    $row['date_add'] = '"' . str_replace("\"", "\"\"", date('d.m.Y', strtotime($row['date_add']))).'"';
    $row['blocked'] = '"' . str_replace("\"", "\"\"",  self::$accessStatus[$row['blocked']]).'"';
    $activity = $row['activity'] == 1 ? 'Подтвердил регистрацию' : 'Не подтвердил регистрацию';
    $row['activity'] = '"' . str_replace("\"", "\"\"", $activity) . '"';
    $row['inn'] = '"' . str_replace("\"", "\"\"", $row['inn']) . '"';
    $row['kpp'] = '"' . str_replace("\"", "\"\"", $row['kpp']) . '"';
    $row['nameyur'] = '"' . str_replace("\"", "\"\"", $row['sort']) . '"';
    $row['bank'] = '"' . str_replace("\"", "\"\"", $row['bank']) . '"';
    $row['bik'] = '"' . str_replace("\"", "\"\"", $row['bik']) . '"';
    $row['ks'] = '"' . str_replace("\"", "\"\"", $row['ks']) . '"';
    $row['rs'] = '"' . str_replace("\"", "\"\"", $row['rs']) . '"';
    $row['birthday'] = '"' . str_replace("\"", "\"\"", strtotime($row['birthday']) ? date('d.m.Y', strtotime($row['birthday'])) : '') . '"';
    $row['ip'] = '"' . str_replace("\"", "\"\"", $row['ip']) . '"';
    $csvText = $row['email'] . ";" .
      $row['name'] . ";" .
      $row['sname'] . ";" .
      $row['address'] . ";" .
      $row['phone'] . ";" .
      $row['birthday'] . ";" .
      $row['activity'] . ";" .
      $row['role'] . ";" .
      $row['date_add'] . ";" .
      $row['blocked'] . ";" .
      $row['nameyur'] . ";" .
      $row['adress'] . ";" .
      $row['inn'] . ";" .
      $row['kpp'] . ";" .
      $row['bank'] . ";" .
      $row['bik'] . ";" .
      $row['ks'] . ";" .
      $row['rs'] . ";" .
      $row['ip'] . ";" . "\n";

    return $csvText;
  }

  /**
   * Получает права доуступа пользователей сайта к различным разделам системы.
   * <code>
   * $result = USER::access('product');
   * viewData($result);
   * </code>
   * @param string $zone название зоны доступа 
   *   1 admin_zone - админка,
   *   2 product - товары,
   *   3 page - страницы,
   *   4 category - категории,
   *   5 order - заказы,
   *   6 user - покупатели,
   *   7 plugin - плагины,
   *   8 setting - настройки,
   *   9 wholesales - оптовые цены
   * @return int число показывающее уровень доступа (0 - нет доступа; 1 - только просмотр; 2 - просмотр и редактирование(кроме admin_zone и wholesales))
   */
  public static function access($zone = '') {

    // для старого алгоритма
    $thisRole = empty(self::getThis()->role)?'2':self::getThis()->role;

    // распределяем цифры как в новом алгоритме, но на базе старых правил
    switch ($thisRole) {
      // админ
      case '1':
        switch ($zone) {
          case 'admin_zone':
            return 1;
            break;
          default:
            return 2;
            break;
        }
        break;
      // пользователь
      case '2':
        return 0;
        break;
      // менеджер
      case '3':
        switch ($zone) {
          case 'admin_zone':
            return 1;
            break;
          case 'order':
          case 'plugin':
            return 2;
            break;
          default:
            return 0;
            break;
        }
        break;
      // модератор
      case '4':
        switch ($zone) {
          case 'admin_zone':
            return 1;
            break;
          case 'product':
          case 'category':
          case 'page':
          case 'order':
          case 'user':
          case 'plugin':
            return 2;
            break;
          default:
            return 0;
            break;
        }
        break;
    }

  }

  public function getUserOrderContent($email) {
    // сумма оплаченных заказов
    $res = DB::query('SELECT SUM(summ_shop_curr) AS summ FROM '.PREFIX.'order WHERE user_email = '.DB::quote($email).' AND status_id IN (2, 5)');
    if($row = DB::fetchAssoc($res)) {
      $orderSumm = $row['summ'];
    }
    // содержимое заказов
    $res = DB::query('SELECT order_content, `number`, add_date FROM '.PREFIX.'order WHERE user_email = '.DB::quote($email).' AND status_id IN (2, 5)');
    while($row = DB::fetchAssoc($res)) {
      $tmp = unserialize(stripcslashes($row['order_content']));
      foreach ($tmp as $value) {
        $orderContent[$value['code']]['name'] = $value['name'];
        $orderContent[$value['code']]['count'] += $value['count'];
        $orderContent[$value['code']]['price'] += $value['price'] * $value['count'];
        $orderContent[$value['code']]['number'] = $row['number'];
        $orderContent[$value['code']]['add_date'] = $row['add_date'];
        $orderContent[$value['code']]['code'] = $value['code'];
      }
    }
    // сортировка содержимого заказов
    $sort = NULL;
    foreach($orderContent as $key => $arr){
      $sort[$key] = $arr['add_date'];
    }
    array_multisort($sort, SORT_NUMERIC, $orderContent);
    $data['summ'] = $orderSumm;
    $data['products'] = $orderContent;
    return $data;
  }

  public function getOwners($type, $filter = false) {
    $roles = array(3,4);
    $lang = MG::get('lang');
    if($filter) $owners[0] = $lang['LAYOUT_CATALOG_66'];
    $res = DB::query('SELECT id, name, email FROM '.PREFIX.'user WHERE role IN ('.DB::quoteIN($roles).')');
    while($row = DB::fetchAssoc($res)) {
      $owners[$row['id']] = $row['name'].' ('.$row['email'].')';
    }
    return $owners;
  }

}