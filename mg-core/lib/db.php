<?php

/**
 * Класс DB - предназначен для работы с базой данных.
 * Доступен из любой точки программы.
 * Реализован в виде синглтона, что исключает его дублирование.
 * Все запросы выполняемые в коде движка должны обязательно проходить через метод DB::query() данного класса, а параметры запроса  экранироваться методом DB::quote();
 * - Создает соединение с БД средствами mysqli;
 * - Защищает базу от SQL инъекций;
 * - Ведет логирование запросов если установленна данная опция;
 * 
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */
class DB {

  static private $_instance = null;
  static private $_debugMode = DEBUG_SQL;
  static private $log = null;
  static private $lastQuery = null;
  static public $connection = null;

  private function __construct() {
  
  	$hostAndPort = explode(':', HOST);
  	$port = null;
  	$host = HOST;
  	if(!empty($hostAndPort[1])){
  	  $port = $hostAndPort[1];
  	  $host = $hostAndPort[0];
  	}
		
    self::$connection = new mysqli($host, USER, PASSWORD, NAME_BD, $port);
		
    if (self::$connection->connect_error) {
      die('Ошибка подключения ('.self::$connection->connect_errno.') '
        .self::$connection->connect_error);
    }

    if(@SQL_MODE != 1) {
      $findMode = false;
      $res = DB::query('SELECT @@sql_mode');
      if($row = DB::fetchAssoc($res)) {
        $mode = explode(',', $row['@@sql_mode']);
      }
      foreach ($mode as $key => $value) {
        if(in_array($value, array('ONLY_FULL_GROUP_BY','STRICT_ALL_TABLES','STRICT_TRANS_TABLES'))) {
          unset($mode[$key]);
          $findMode = true;
        }
      }
      DB::query('SET @@sql_mode='.DB::quote(implode(',', $mode)));
      if(@SQL_MODE == 'SQL_MODE') {
        $str = "\r\n";
        $str .= "; Если у базы данных стоит строгий режим, то значение 0 исправит ситуацию, при значении 1 движок будет работать как раньше \r\n";
        if($findMode) $str .= "SQL_MODE = 0\r\n"; else $str .= "SQL_MODE = 1\r\n";
        file_put_contents('config.ini', $str, FILE_APPEND);
      } 
      // else {
      //   $config = file_get_contents('config.ini');
      //   if($findMode) $mode = 0; else $mode = 1;
      //   $config = str_replace('SQL_MODE = '.SQL_MODE, 'SQL_MODE = '.$mode, $config);
      //   file_put_contents('config.ini', $config);
      // }
    }

  }

  private function __clone() {
    
  }

  private function __wakeup() {
    
  }

  /**
   * Строит часть запроса, из полученного ассоциативного массива.
   * Обычно используется для оператора SET.
   * Пример:
   * <code>   
   * $array = (
   *   'login' => 'admin',
   *   'pass' => '1',
   * );
   * // преобразует массив в строку: "'login' = 'admin', 'pass' = '1'"
   * DB::buildPartQuery($array); 
   * </code>  
   * @param array $array ассоциативный массив полей с данными.
   * @param string $devide разделитель.
   *
   * @return string
   */
  public static function buildPartQuery($array, $devide = ',') {
    $partQuery = '';

    if (is_array($array)) {
      $partQuery = '';
      foreach ($array as $index => $value) {
        if(preg_match('~^\-?(^(\-?0(\.|$))|^\-?[1-9]+\d*\.?)(\d{0,})?$~',trim($value))){
          $partQuery .= ' `'.self::quote($index,true).'` = '.self::quote($value,true).''.$devide;
        }else{
          $partQuery .= ' `'.self::quote($index,true).'` = "'.self::quote($value,true).'"'.$devide;
        }
      }
      $partQuery = trim($partQuery, $devide);     
    }
    return $partQuery;
  }

  /**
   * Аналогичен методу buildPartQuery, но используется для целого запроса.
   * Как правило для WHERE.
   *
   * @param string SQL запрос.
   * @param array $array ассоциативный массив.
   * @param string $devide разделитель
   * @return obj|bool
   */
  public static function buildQuery($query, $array, $devide = ',') {

    if (is_array($array)) {
      $partQuery = '';

      foreach ($array as $index => $value) {   
  
        if(is_numeric($value)){
          if(preg_match('~^\-?(^(\-?0(\.|$))|^\-?[1-9]+\d*\.?)(\d{0,})?$~',trim($value))){
            $partQuery .= ' `'.self::quote($index,true).'` = '.self::quote($value,true).''.$devide;
          }else{
            $partQuery .= ' `'.self::quote($index,true).'` = "'.self::quote($value,true).'"'.$devide;
          }
        }else{
          $partQuery .= ' `'.self::quote($index,true).'` = "'.self::quote($value,true).'"'.$devide;
        }
      }

      $partQuery = trim($partQuery, $devide);
      $query .= $partQuery;

      return self::query($query);
    }
    return false;
  }

  /**
   * Возвращает запись в виде массива.
   * @param obj $object
   * @return array
   */
  public static function fetchArray($object) {
    return @mysqli_fetch_array($object);
  }

  /**
   * Возвращает ряд результата запроса в качестве ассоциативного массива.
   * @param obj $object
   * @return array
   */
  public static function fetchAssoc($object) {
    return @mysqli_fetch_assoc($object);
  }

  /**
   * Возвращает запись в виде объекта.
   * @param obj $object
   * @return obj
   */
  public static function fetchObject($object) {
    return @mysqli_fetch_object($object);
  }
  
   /**
   * Get a result row as an enumerated array.
   * @param obj $object
   * @return obj
   */
  public static function fetchRow($object) {
    return @mysqli_fetch_row($object);
  }
  

  /**
   * Возвращает единственный экземпляр данного класса.
   * @return obj
   */
  static public function getInstance() {
    if (is_null(self::$_instance)) {
      self::$_instance = new self;
    }
    return self::$_instance;
  }

  /**
   * Инициализирует единственный объект данного класса, устанавливает кодировку БД utf8.
   * @return void
   */
  public static function init() {
    self::getInstance();
    DB::query('SET names utf8');
    if (SQL_BIG_SELECTS) {
      DB::query('SET SQL_BIG_SELECTS = 1');
    }
  }

  /**
   * Возвращает сгенерированный колонкой с AUTO_INCREMENT
   * последним запросом INSERT к серверу.
   * @return int
   */
  public static function insertId() {
    return @mysqli_insert_id(self::$connection);
  }

  /**
   * Возвращает количество рядов результата запроса.
   * @param obj $object
   * @return int
   */
  public static function numRows($object) {
    return @mysqli_num_rows($object);
  }
  
  /**
   * Получает число строк, затронутых предыдущей операцией MySQL.
   * @return int
   */
  public static function affectedRows() {
    return @mysqli_affected_rows(self::$connection);
  }
  
  /**
   * Закрывает соединение с БД.
   */
  public static function close() {
    return @mysqli_close(self::$connection);
  }

  /**
   * Функция для создания индекосов в таблицах с проверкой на их существование.
   * @param string $table целевая таблица
   * @param string $index столбец для индекса
   */
  public static function createIndexIfNotExist($table, $index) {
    $res = DB::query("SELECT COUNT(1) indexExists FROM INFORMATION_SCHEMA.STATISTICS
        WHERE table_schema=DATABASE() AND table_name='".PREFIX.DB::quote($table, true)."' AND index_name='".DB::quote($index, true)."';");
    $res = DB::fetchAssoc($res);
    if($res['indexExists'] == 0) {
      DB::query('CREATE INDEX '.DB::quote($index, true).' ON '.PREFIX.DB::quote($table, true).'('.DB::quote($index, true).')', true);
    }
  }

  /**
   * Выполняет запрос к БД.
   * @param srting $sql запрос.( Может содержать дополнительные аргументы.)
   * @param bool $noError не выводить SQL ошибку
   * @return obj|bool
   */
  public static function query($sql, $noError = false) {

    if (($num_args = func_num_args()) > 1) {
      $arg = func_get_args();
      unset($arg[0]);

      // Экранируем кавычки для всех входных параметров.
      foreach ($arg as $argument => $value) {
        $arg[$argument] = mysqli_real_escape_string(self::$connection, $value);
      }
      $sql = vsprintf($sql, $arg);
    }
    $obj = self::$_instance;

    if (isset(self::$connection)) {
      @$obj->count_sql++;

      $startTimeSql = microtime(true);

      if($noError) {
        $result = mysqli_query(self::$connection, $sql);
      } else {
        $result = mysqli_query(self::$connection, $sql) 
          or die(self::console('<br/><span style="color:red">Ошибка в SQL запросе: '
            . '</span><span style="color:blue">'.$sql.'</span> <br/> '
            . '<span style="color:red">'.mysqli_error(self::$connection).'</span>'));
      }

      $timeSql = microtime(true) - $startTimeSql;
      @$obj->timeout += $timeSql;
      self::$lastQuery = $sql;
      if (self::$_debugMode) {    
        self::$log .= "<p style='margin:5px; font-size:10px;'><span style='color:blue'> <span style='color:green'># Запрос номер ".$obj->count_sql.": </span>".$sql."</span> <span style='color:green'>(".round($timeSql, 4)." msec )</span>";
        $stack = debug_backtrace();
        self::$log .= " <span style='color:#c71585'>".$stack[0]['file'].' (line '.$stack[0]['line'].")</span></p>";
      }

      return $result;
    }
    return false;
  }

  /**
   * Экранирует кавычки для части запроса.
   * @param srting $string часть запроса.
   * @param srting $noQuote - если true, то не будет выводить кавычки вокруг строки.
   * @return string
   */
  public static function quote($string, $noQuote = false) {
    return (!$noQuote) ? "'".mysqli_real_escape_string(self::$connection, $string)."'" : mysqli_real_escape_string(self::$connection, $string);
  }
  /**
   * Экранирует кавычки для части запроса и преобразует экранируемую часть запроса в тип integer 
   * @param srting $string часть запроса.
   * @param srting $noQuote - если true, то не будет выводить кавычки вокруг строки.
   * @return int
   */
  public static function quoteInt($string, $noQuote = false) {
    return (!$noQuote) ? "'".mysqli_real_escape_string(self::$connection, intval($string))."'" : mysqli_real_escape_string(self::$connection, intval($string));
  }
  /**
   * Экранирует кавычки для части запроса, заменяет запятую на точку и преобразует экранируемую часть запроса в тип float 
   * @param srting $string часть запроса.
   * @param srting $noQuote - если true, то не будет выводить кавычки вокруг строки.
   * @return int
   */
  public static function quoteFloat($string, $noQuote = false) {
    $string = str_replace(',', '.', $string);
    return (!$noQuote) ? "'".mysqli_real_escape_string(self::$connection, floatval($string))."'" : mysqli_real_escape_string(self::$connection, floatval($string));
  }
  /**
   * Экранирует кавычки для части запроса и преобразует экранируемую часть запроса в пригодный вид для условий типа IN
   * @param srting $string часть запроса.
   * @return string
   */
  public static function quoteIN($string) {
    if(empty($string)) return "''";
    if(is_array($string)) {
      $string = implode(',', $string);
    }
    $tmp = explode(',', $string);
    foreach ($tmp as $key => $value) {
      $tmp[$key] = self::quote(trim($value));
    }
    return implode(',', $tmp);
  }

  /**
   * Выводит консоль запросов и ошибок.
   * @param srting $text - данные лога.
   * @return string
   */
  public static function console($text = '') {

    $stack = debug_backtrace();
  
    unset($stack[0]);
    $obj = self::$_instance;
    $html = '<script>var consoleCount = $(".wrap-mg-console").length; if(consoleCount>1){$(".wrap-mg-console").hide();}</script>
      <div class="wrap-mg-console '.time().'" style="height: 200px; width:100%; position:fixed;z-index:66;bottom:0;left:0;right:0;background:#fff;">
      <div class="mg-bar-console" style="background:#dfdfdf; height: 30px; line-height: 30px; padding: 0 0 0 10px; width:100%; border-top: 2px solid #a3a3a3; border-bottom: 2px solid #a3a3a3;">
      Всего выполнено запросов: '.$obj->count_sql.' шт. за '.round($obj->timeout, 4).' сек.
      <a style="float:right; margin-right:30px;" href="javascript:void(0);" onclick=\'$(".wrap-mg-console").hide()\'>Закрыть</a>
      </div>
      <div class="mg-console" style="background:#f4f4f4; height: 200px; overflow:auto;">
      <script>$(".'.time().'").show();</script>     
      ';
    $logStack = '';
    foreach ($stack as $item) {
      $logStack .= '<p style="margin:5px; font-size:10px;"><br/><span style="color:#c71585">'.$item['file'].' (line '.$item['line'].")</span></p>";
    }
    $html.= self::$log.'<br/>'.$text.$logStack;
    $html.='</div>
    </div>';
    return $html;
  }

  /**
   * Выводит последний выполненный SQL запрос.
   * @return string
   */
  public static function lastQuery() {
    return self::$lastQuery;
  }

}