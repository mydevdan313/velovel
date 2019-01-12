<?php

/**
 * Класс URL - предназначен для работы со ссылками, а также с адресной строкой.
 * Доступен из любой точки программы.
 *
 * Реализован в виде синглтона, что исключает его дублирование.
 * Имеет в себе реестр queryParams для хранения любых объектов.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */
class URL {

  static private $_instance = null;
  static private $cutPath = '';
  static private $route = 'index';
  static public $documentRoot = '';

  /**
   * Исключает XSS уязвимости для вех пользовательских данных.
   * Сохраняет все переданные параметры в реестр queryParams,
   * в дальнейшем доступный из любой точки программы.
   * Выявляет часть пути в ссылках, по $_SERVER['SCRIPT_NAME'],
   * которая не должна учитываться при выборе контролера.
   * Актуально когда файлы движка лежат не в корне сайта.
   */
  private function __construct() {
    self::$documentRoot = substr(SITE_DIR, 0, -1);
    self::$cutPath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
    $route = self::getLastSection();
    $route = $route ? $route : 'index';

    if ($route == 'resetmpcache') {
      MG::setOption('mpUpdate', 'clear');
    }

    if ((MG::getSetting('catalogIndex')=='true') && $route == 'index') {
      $route = 'catalog';
    }

    if ($route == 'mg-admin') {
      $route = 'mgadmin';
    }

    if ($route == 'catalog' && self::getCountSections() > 1) {
      $route = '404';
    }

    // Заполняем QUERY_STRING переменной route.
    $_SERVER['QUERY_STRING'] = 'route='.$route;
    $route = str_replace('.html', '', $route);

    // Конвертируем обращение к контролеру админки в подобающий вид.
    self::$route = $route;

    if (get_magic_quotes_gpc()) {
      $_REQUEST = MG::stripslashesArray($_REQUEST);
      $_POST = MG::stripslashesArray($_POST);
      $_GET = MG::stripslashesArray($_GET);
    }
    // Если данные пришли не из админки и не из плагинов а от пользователей,то проверяем их на XSS.
    // Также исключение действует на просмотрщик страниц,
    // он защищен от стороннего использования в контролере, поэтому исключает опасность.
    if ((strpos($route, 'mgadmin') === false && strpos($route, 'ajax') === false && strpos($route, 'previewer') === false) || strpos($route, 'ajaxrequest') !== false) {
      $emulMgOff = false;
      if (get_magic_quotes_gpc()) {

        $emulMgOff = true;
      }

      $_REQUEST = MG::defenderXss($_REQUEST, $emulMgOff);
      $_POST = MG::defenderXss($_POST, $emulMgOff);
      $_GET = MG::defenderXss($_GET, $emulMgOff);
    }

    $this->queryParams = $_REQUEST;
  }

  private function __clone() {
    
  }

  private function __wakeup() {
    
  }

  /**
   * Конвертирует русскоязычный URL в транслит.
   * <code>
   *  $url = URL::createUrl('русский-домен.рф');
   * </code>
   * @param string $urlstr русскоязычный url.
   * @return string|bool
   */
  public static function createUrl($urlstr) {
    $result = false;
    if (preg_match('/[^A-Za-z0-9_\-]/', $urlstr)) {
      $urlstr = translitIt($urlstr);
      $urlstr = preg_replace('/[^A-Za-z0-9_\-]/', '', $urlstr);
      $result = $urlstr;
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Возвращает защищенный параметр из массива $_GET.
   * <code>
   *  $url = URL::get('id');
   * </code>
   * @param string $param нужный параметр
   * @return string
   */
  public static function get($param) {
    return self::getQueryParametr($param);
  }

  /**
   * Возвращает чистый URI, без строки с get параметрами.
   * <code>
   * echo URL::getClearUri();
   * </code>
   * @return string
   */
  public static function getClearUri() {
    $data = self::getDataUrl();
    //отрезаем только первую встретившуюся часть
    if (self::$cutPath) {
      $pos = strpos($data['path'], self::$cutPath);
      if ($pos !== false) {
        $res = substr_replace($data['path'], '', $pos, strlen(self::$cutPath));
      }
    } else {
      $res = $data['path'];
    }

    return $res;
  }

  /**
   * Чистит входящий URL и возвращает URI , аналогично методу  getClearUri() только для заданного URL.
   * <code>
   * echo URL::clearingUrl('http://site.ru/smartfony/apple-iphone-8');
   * </code>
   * @param string $url входящая ссылка
   * @return string
   */
  public static function clearingUrl($url) {
    $data = parse_url($url);
    //отрезаем только первую встретившуюся часть
    if (self::$cutPath) {
      $pos = strpos($data['path'], self::$cutPath);
      if ($pos !== false) {
        $res = substr_replace($data['path'], '', $pos, strlen(self::$cutPath));
      }
    } else {
      $res = $data['path'];
    }

    return $res;
  }

  /**
   * Возвращает часть пути, до папки с CMS.
   * Например если движок расположен по этому пути http://sitename.ru/shop/index.php,
   * то  метод вернет строку "/shop"
   * <code>
   * echo URL::getCutPath();
   * </code>
   * @return string
   */
  public static function getCutPath() {
    return self::$cutPath;
  }

  /**
   * Возвращает количество секций.
   * <code>
   * echo URL::getCountSections();
   * </code>
   * @return int
   */
  public static function getCountSections() {
    $sections = self::getSections();
    return count($sections) - 1;
  }

  /**
   * Возвращает массив составных частей ссылки.
   * <code>
   * $result = URL::getDataUrl('http://site.ru/smartfony/apple-iphone-8');
   * viewData($result);
   * </code>
   * @param string|bool ссылка для разбивки на составные части
   * @return array
   */
  public static function getDataUrl($url = false) {
    if (!$url) {
      $url = URL::getUrl();
    }
    return parse_url($url);
  }

  /**
   * Возвращает единственный экземпляр данного класса.
   * <code>
   * $obj = URL::getInstance();
   * </code>
   * @return object объект класса URL.
   */
  static public function getInstance() {
    if (is_null(self::$_instance)) {
      self::$_instance = new self;
    }
    return self::$_instance;
  }

  /**
   * Возвращает последнюю часть uri.
   * <code>
   * echo URL::getLastSection();
   * </code>
   * @return string
   */
  public static function getLastSection() {
    $sections = self::getSections();
    $lastSections = end($sections);
    
    if(OLDSCOOL_LINK!='OLDSCOOL_LINK' && OLDSCOOL_LINK!='0'){
      $lastSections = str_replace('.html','',$lastSections);
    }

    return $lastSections;
  }

  /**
   * Возвращает часть пути, до папки с CMS.
   * Например если движок расположен по этому пути http://sitename.ru/shop/index.php,
   * то  метод вернет строку "/shop"
   * <code>
   * echo URL::getCutSection();
   * </code>
   * @return string
   */
  public static function getCutSection() {
    return str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
  }

  /**
   * Возвращает запрошенный request параметр.
   * <code>
   * echo URL::getQueryParametr('http://site.ru/integration?int=ym&name=getyml', 'name'); // getyml
   * </code>
   * @param string $param ключ необхомиго параметра
   * @return string
   */
  public static function getQueryParametr($param) {
    $params = self::getInstance()->queryParams;
    $res = !empty($params[$param]) ? $params[$param] : null;
    return $res;
  }

  /**
   * Возвращает запрошенную строку параметров.
   * <code>
   * echo URL::getQueryString();
   * </code>
   * @return string
   */
  public static function getQueryString() {
    return $_SERVER['QUERY_STRING'];
  }

  /**
   * Возвращает массив секций URI.
   * <code>
   * $result = URL::getSections('http://site.ru/smartfony/apple-iphone-8');
   * viewData($result);
   * </code>
   * @param string|bool $path uri для разбивки
   * @return array
   */
  public static function getSections($path = false) {
    
    if (!$path) {
      $uri = self::getClearUri();
    } else {
      $uri = $path;
    }
    
    if(OLDSCOOL_LINK!='OLDSCOOL_LINK' && OLDSCOOL_LINK!='0'){
      $uri = str_replace('.html','',$uri);
    }
    
    $sections = explode('/', rtrim($uri, '/'));
    return $sections;
  }

  /**
   * Возвращает часть url являющуюся parent_url.
   * <code>
   * echo URL::parseParentUrl('http://site.ru/smartfony/apple-iphone-8');
   * </code>
   * @param string|bool $path url
   * @return string
   */
  public static function parseParentUrl($path = false) {
    if (!$path) {
      $uri = self::getSections();
    } else {
      $uri = self::getSections($path);
    }
    unset($uri[count($uri) - 1]);
    $parentUrl = trim(implode('/', $uri), '/').'/';
    return $parentUrl;
  }

  /**
   * Возвращает последнюю секцию URL.
   * <code>
   * echo URL::parsePageUrl('http://site.ru/smartfony/apple-iphone-8');
   * </code>
   * @param string|bool $path url
   * @return string
   */
  public static function parsePageUrl($path = false) {
    if (!$path) {
      $uri = self::getSections();
    } else {
      $uri = self::getSections($path);
    }
    $pageUrl = $uri[count($uri) - 1];
    return $pageUrl;
  }

  /**
   * Возвращает страницу с изменеными ссылками для мультиязычности.
   * @access private
   * @param string $data верстка страницы
   * @return string
   */
  public static function multiLangLink($data = '') {
    return $data;
  }

  /**
   * Возвращает  URI, с get параметров.
   * <code>
   * echo URL::getUri();
   * </code>
   * @return string
   */
  public static function getUri() {
    return $_SERVER['REQUEST_URI'];
  }

  /**
   * Возвращает ссылку с хостом и протоколом.
   * <code>
   * echo URL::getUrl();
   * </code>
   * @return string
   */
  public static function getUrl() {
    return PROTOCOL.'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
  }

  /**
   * Возвращает имя для роутера.
   * <code>
   * echo URL::getRoute();
   * </code>
   * @return string
   */
  public static function getRoute() {
    return self::$route;
  }

  /**
   * Инициализирует данный класс URL.
   * @access private
   * @return void
   */
  public static function init() {
    self::getInstance();
  }

  /**
   * Проверяет является ли полученное значение  - именем текущего раздела.
   * Пример:  isSection('catalog') вернет true если открыта страница каталога.
   * <code>
   * $result = URL::isSection('catalog');
   * var_dump($result);
   * </code>
   * @param string $section название секции.
   * @return bool
   */
  public static function isSection($section) {
    $sections = self::getSections();
    return (@$sections[1] == $section) ? true : false;
  }

  /**
   * Возвращает запрошенный параметр из $_POST массива.
   * <code>
   * $result = URL::post('id');
   * viewData($result);
   * </code>
   * @param string $param запрошенный параметр.
   * @return string
   */
  public static function post($param) {
    return self::getQueryParametr($param);
  }

  /**
   * Устанавливает параметр в реестр URL. Можно использовать как реестр переменных.
   * <code>
   * URL::setQueryParametr('param', 'paramval');
   * </code>
   * @param string $param наименование параметра.
   * @param string $param значение параметра.
   */
  public static function setQueryParametr($param, $value) {
    self::getInstance()->queryParams[$param] = $value;
  }

  /**
   * Добавляет, либо заменяет $_GET параметр в строке URL. Обычно нужен для пейджера навигации.
   * <code>
   * echo URL::add_get('http://site.ru/catalog?page=2', 'page', '4');
   * </code>
   * @param string $url ссылка
   * @param string $param параметр
   * @param string $pvalue значение параметра
   * @return string
   */
  public static function add_get($url, $param, $pvalue = '') {
    $res = $url;
    if (($p = strpos($res, '?')) !== false) {
      $paramsstr = substr($res, $p + 1);
      $params = explode('&', $paramsstr);
      $paramsarr = array();
      foreach ($params as $value) {
        $tmp = explode('=', $value);
        if (isset($paramsarr[$tmp[0]])) {

          if (is_array($paramsarr[$tmp[0]])) {
            $paramsarr[$tmp[0]][] = (string) $tmp[1];
          } else {
            $temp = $paramsarr[$tmp[0]];
            unset($paramsarr[$tmp[0]]);
            $paramsarr[$tmp[0]][] = $temp;
            $paramsarr[$tmp[0]][] = (string) $tmp[1];
          }
        } else {
          $paramsarr[$tmp[0]] = (string) $tmp[1];
        }
      }
      $paramsarr[$param] = $pvalue;
      $res = substr($res, 0, $p + 1);

      foreach ($paramsarr as $key => $value) {
        if (is_array($value)) {
          foreach ($value as $item) {
            $str = $key;
            if ($item !== '') {
              $str .= '='.$item;
            }
            $res .= $str.'&';
          }
        } else {
          $str = $key;
          if ($value !== '') {
            $str .= '='.$value;
          }
          $res .= $str.'&';
        }
      }
      $res = substr($res, 0, -1);
    } else {
      $str = $param;
      if ($pvalue) {
        $str .= '='.$pvalue;
      }
      $res .= '?'.$str;
    }
    return $res;
  }

  /**
   * Удаляет из URL все запрещенные спецсимволы, заменяет пробелы на тире.
   * <code>
   * echo URL::prepareUrl('http://site.ru/catalog ? <page>=2');
   * </code>
   * @param string $str строка для операции
   * @param bool $product для товаров
   * @param bool $toLower перевести в нижний регистр
   * @return string
   */
  public static function prepareUrl($str, $product = false, $toLower = true) {
    if ($toLower) {
      $str = strtolower($str);
    }
    
    $str = preg_replace('%\s%i', '-', $str);
    $str = str_replace('`', '', $str);
    $str = str_replace(array("\\","<",">"),"",$str);
    if($product){
      $pattern = '%[^/-a-zа-я#\d]%i';
    }else{
      $pattern = '%[^/-a-zа-я#\.\d]%i';
    }
    $str = preg_replace($pattern, '', $str);
    $str = substr($str, 0, 255);
    return $str;
  }
  
    
  /**
   * Вычисляет настоящее местоположение до файла на сервере.
   * Вместо этой функции можно использовать константу SITE_DIR
   * <code>
   * echo URL::getDocumentRoot();
   * </code>
   * @param bool $lastSep добавляет последний слеш в конец пути
   * @return string
   */
  public static function getDocumentRoot($lastSep=true) {     
    $documentroot = self::$documentRoot;
    $documentroot = $lastSep?$documentroot.DS:$documentroot;
    
    return $documentroot;
  }   
  
   /**
   * Получает ссылку для редиректа, если она была указана в настройках SEO для запрашиваемой страницы.
   * <code>
   * $result = URL::getUrlRedirect();
   * viewData($result);
   * </code>
   * @return array|bool ссылка для редиректа
   */
  public static function getUrlRedirect(){
    $redirect = false;    
    
    if(URL::getClearUri() == '/modificatoryInc' || MG::getSetting('useSeoRedirects') != 'true'){
      return $redirect;
    }
    
    $url = self::getUri();  

    
    if($_SERVER['HTTP_HOST']=='localhost'){      
      $url = ($_SERVER['REQUEST_SCHEME']?$_SERVER['REQUEST_SCHEME']:PROTOCOL).'://localhost'.$url;
    }else{
      $url = SITE.$url;
    }       

    
    $sql = "SELECT `url_new`, `code` "
         . "FROM `".PREFIX."url_redirect` "
         . "WHERE `url_old` = ".DB::quote($url)." AND `activity` = 1";        
    
    if($dbRes = DB::query($sql)){
      if($arRes = DB::fetchAssoc($dbRes)){        
        $redirect = array(
          'url' => str_replace('amp;', '&', $arRes['url_new']),
          'code' => $arRes['code'],
        );        
      }
    }
    
    return $redirect;
  }

  public static function clean($string) {
    $string = str_replace(' ', '-', $string);
    $string = preg_replace('/[^A-Za-z0-9\-\/]/', '', $string);
    return preg_replace('/-+/', '-', $string);
  }
}