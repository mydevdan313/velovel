<?php
// нужно для прекращения работы скрипта при проверке на работоспособность модуля mod_rewrite
if(isset($_GET['test'])) {
  echo '1';
  exit;
}

error_reporting(1);	

define('SITE', (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off'  ? 'https://' : 'http://').$_SERVER['SERVER_NAME'].URL::getCutSection());
$prefix = empty($_REQUEST['prefix']) ? 'mg_' : $_REQUEST['prefix'];
$_SESSION = array();

if ($_REQUEST['siteName']) {
  $siteName = clearData($_REQUEST['siteName']);
} else {
  $siteName = $_SERVER['SERVER_NAME'];
}

$aLogin = 'Администратор';
$aPass = clearData($_REQUEST['pass']);
$adminEmail = clearData($_REQUEST['email']);
if ($_REQUEST['id']) {
  setcookie("installerMoguta", $_REQUEST['id'], time()+3600*24*30,'/');
  $idInstaller = $_REQUEST['id'];
}
if ($_REQUEST['step1']) {
  $step = 0;
  if ('ok'==$_REQUEST['agree']) {
    $step = 1;
  }
  
  if($checkLibs = libExists()){
    $libError = true;
	$msg .= '<div class="wrapper-error">';
    $msg .= '<div class="error-system-install">Установка системы невозможна!</div>';
    foreach ($checkLibs as $message){
        $msg .= '<span class="error-lib">'.$message.'</span><br>';
    }
    $msg .= '</div>';
  }
  
  if($_SERVER['HTTP_HOST']=='localhost'){
    $host = 'localhost';
    $user = 'root';
    $password = '';
    $nameDB = 'BASE_NAME';
  };
	
}

if ($_REQUEST['step2']) {
  
  if(!empty($_REQUEST['ajax'])){
    
    if(downloadDemoData($_REQUEST['part'])){
      return extractZip('uploads.zip');
    }
    
    return false;
  }
  
  $host = clearData($_REQUEST['host']);
  $user = clearData($_REQUEST['user']);
  $password = clearData($_REQUEST['password']);
  $nameDB = clearData($_REQUEST['nameDB']);
  $engineType = clearData($_REQUEST['engineType']);
  $step = 2;


  if(!empty($engineType)){
    $checkedTest = 'checked=checked'; //отметка "по умолчанию" типа магазина -тестовый
  }
  //Тестирование введенных пользователем параметров.
    try {
    $connection = mysqli_connect($host, $user, $password);
   
    if (!$connection) {
      throw new Exception('<span class="no-bd">Невозможно установить соединение.</span>');
    }

    if (!mysqli_select_db($connection,$nameDB)) {
      throw new Exception('<span class="error-db">Ошибка! Невозможно выбрать указанную базу.'. mysqli_error($connection).'</span>');
    }
  } catch (Exception $e) {
    //Выведет либо сообщение об ошибке подключения, либо об ошибке выбора.
    $msg = '<div class="msgError">'.$e->getMessage().'</div>';
	$step = 1;

  }
}

if ($_REQUEST['step3']) {
  $step = 3;
  
  $host = clearData($_REQUEST['host']);
  $user = clearData($_REQUEST['user']);
  $password = clearData($_REQUEST['password']);
  $nameDB = clearData($_REQUEST['nameDB']);
  $engineType = clearData($_REQUEST['engineType']);
  $consentData = clearData($_REQUEST['consentData']);
  if(!empty($engineType)){
    $checkedTest = 'checked=checked'; //отметка "по умолчанию" типа магазина -тестовый
  }

  if (!$_REQUEST['existDB']) {
     
    // Проверка адреса сайта.
    if (''==$siteName) {
      $msg .= '<div class="msgError">Ошибка!
        Не заполнено имя сайта</div>';
    }
    // Проверка электронного адреса.
    if (!preg_match(
        '/^[-._a-zA-Z0-9]+@(?:[a-zA-Z0-9][-a-zA-Z0-9]{0,61}+\.)+[a-zA-Z]{2,6}$/', $adminEmail)
    ) {
      $msg .= '<span class="error-email">Ошибка!
        Неверно заполнено email администратора</span>';
    }
      
    // Пароль должен быть больше 5-ти символов.
    if (strlen($aPass)<5) {
      $msg .= '<span class="error-pass-count">Ошибка!
        Пароль менее 5 символов</span>';
      // Иначе, если не отмечено что пароль видимый.
    } elseif (!$_REQUEST['showPass']) {
      $rePass = clearData($_REQUEST['rePass']);

      // Проверяем равенство введенных паролей.
      if ($rePass!=$aPass) {
        $msg .= '<span class="error-pass">Ошибка!
          Введенные пароли не совпадают</span>';
      }
    }
     
  
  
    // Если ошибок нет
    if (!$msg) {

	  //Тестирование введенных пользователем параметров.
	  try {
    $connection = mysqli_connect($host, $user, $password);
		if (!$connection) {
		  throw new Exception('<span class="no-bd">Невозможно установить соединение.</span>');
		}

		if (!mysqli_select_db($connection,$nameDB)) {
		  throw new Exception('<span class="error-db">Ошибка! Невозможно выбрать указанную базу.</span>');
		}
	  } catch (Exception $e) {
		//Выведет либо сообщение об ошибке подключения, либо об ошибке выбора.
		$msg = '<div class="msgError">'.$e->getMessage().'</div>';
	  }

      $mysqlVersion = mysqli_get_server_version($connection);
      $arVersion = array(
        'main' => round($mysqlVersion/10000),
        'minor' => ($mysqlVersion/100)%10,
        'sub' => $mysqlVersion%100,
      );

      if (file_exists('install/dbDump.php')) { //подгружаем основной дамп БД
      
        require_once ('install/dbDump.php');
   
        if(is_array($damp)){
          if (file_exists('install/dbDumpTestShop.php') && $checkedTest == 'checked=checked') { //если указано, что устанавливать тестовый магазин,
          //  то подгружаем дамп тестового магазина
            require_once ('install/dbDumpTestShop.php');
            
            if(is_array($dampTestShop)){
              $damp = array_merge($damp, $dampTestShop);
            }
          }

          foreach ($damp as $sql) {
            mysqli_query($connection,$sql) or die  ("Ошибка выполнения запроса:".mysqli_error($connection)."<br/>".$sql);
          }
          include 'mg-core/lib/category.php';
          Category::startIndexation();
        }
      }else{
	    echo "Внимание! Файл install/dbDump.php - не существует, не удалось установить движок! ";
	    exit();
	  }

      $cryptAPass = crypt($aPass);
      $sql = "
        INSERT INTO `".$prefix."user` 
          (`email`, `pass`,`name`,`role`,`activity`)
        VALUES ('".$adminEmail."','".$cryptAPass."', '".$aLogin."', 1, 1)
      ";
      mysqli_query($connection,$sql);
      $sql = "
        UPDATE `".$prefix."setting`
        SET `value` = '".$adminEmail."'
        WHERE `option` = 'adminEmail'
      ";
      mysqli_query($connection,$sql);
      $sql = "
        UPDATE `".$prefix."setting`
        SET `value` = '".$consentData."'
        WHERE `option` = 'consentData'
      ";
      mysqli_query($connection,$sql);
      $sql = '
        SELECT *
        FROM `'.$prefix.'user`
        WHERE `email` = "'.$adminEmail.'"';
    
      $res = mysqli_query($connection,$sql);    
      session_start();
      if ($row = mysqli_fetch_object($res)) {        
        $_SESSION['user'] = $row;
      }
  
    }else{
      $step = 2;
    }
  } else {

    $sql = '
    SELECT id
    FROM `'.$prefix.'user`
    WHERE `role` = 1';

    $res = mysqli_query($connection,$sql);

    if (!mysql_fetch_assoc($res))
      $msg .= '<div class="error-email">Ошибка! Недостаточно данных для установки системы. Не найден аккаунт с правами администратора</div>';
  }
  if (!$msg) {
    $step = 3;
    // Запись введенных данных в файл параметров config.ini
    $str = "[DB]\r\n";
    $str .="HOST = \"".$host."\"\r\n";
    $str .="USER = \"".$user."\"\r\n";
    $str .="PASSWORD = \"".$password."\"\r\n";
    $str .="NAME_BD = \"".$nameDB."\"\r\n";
    $str .="TABLE_PREFIX = \"".$prefix."\"\r\n";
    
    $str .="\r\n";
    $str .="[SETTINGS]\r\n";
    $str .=";Консоль выполненных sql запросов, для генерации страницы\r\n";
    $str .="DEBUG_SQL = 0\r\n";
    
    $str .="\r\n";
    $str .="; Протокол обмена данными с сайтом,(http или https)\r\n";
    $str .="PROTOCOL = \"".(!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off'  ? 'https' : 'http')."\"\r\n";
	
    $str .="\r\n";
    $str .="; Максимальное количество наименований товаров в одном заказе\r\n";
    $str .="MAX_COUNT_CART = 50\r\n";
    
    $str .="\r\n";
    $str .="; Позволяет использовать объемные запросы на хостинге\r\n";
    $str .="SQL_BIG_SELECTS = 0\r\n";  
    
    $str .="\r\n";
    $str .="; Включает дубли страниц заканчивающиеся на .html\r\n";
    $str .="OLDSCOOL_LINK = 0\r\n";
    
    $str .= "\r\n";
    $str .= "; Опция для создания папок с файлами .quarantine и .thums при работе в elfinder \r\n";
    $str .= "CREATE_TMB = 0 \r\n";

    $str .= "\r\n";
    $str .= "; Сервер обновлений плагинов и движка \r\n";
    $str .= "UPDATE_SERVER = 'http://updata.moguta.ru' \r\n";

    $str .= "LOG_USER_AGENT = 0\r\n";

    $str .= "\r\n";
    $str .= "; Включение режима редактирования в публичной части \r\n";
    $str .= "EDIT_MODE = 1 \r\n";

    $str .="\r\n";
    $str .="; Разрешить ли движку автоматическое обновление файлов, в случае изменения версии PHP на сервере\r\n";
    $str .="AUTO_UPDATE = 1\r\n";

    $str .="\r\n";
    $str .="; Максимальный размер файлов для добавления в резервные копии (в мегабайтах)\r\n";
    $str .="BACKUP_MAX_FILE_SIZE = 30\r\n";

    $str .="\r\n";
    $str .="; Кодировка для выгрузки каталога в Яндекс.Маркет по ссылке /getyml, значение none - кодировка не будет указана \r\n";
    $str .="ENCODE_YML_CATALOG = 'windows-1251'\r\n";

    $str .="\r\n";
    $str .="; Выгрузка на Яндекс.Маркет по ссылке /getyml всех товаров (=0) или только тех, которые есть в наличии (=1) \r\n";
    $str .="YML_ONLY_AVAILABLE = 0\r\n";

    $str .="\r\n";
    $str .="; Подставляет миниатюру 30_ вместо 70_ в миникарточку товара в шаблоне moguta \r\n";
    $str .="MODE_MINI_IMAGE = 1\r\n";

    $str .= "\r\n";
    $str .= "; Скрывает товар для посетителей по прямой ссылке, если товар не активен \r\n";
    $str .= "PRODUCT_404 = 0\r\n";

    $str .= "\r\n";
    $str .= "; Если установлен параметр \"1\", то будет производиться проверка целостности файла (в случае проблемы установите значение на \"0\") \r\n";
    $str .= "CSV_COLUMN_CHECK = 1\r\n";
	
    file_put_contents('config.ini', $str); 

    $robots ="User-agent: Yandex
Allow: /uploads/
Disallow: /install*
Disallow: /mg-admin*
Disallow: /personal*
Disallow: /enter*
Disallow: /forgotpass*
Disallow: /payment*
Disallow: /registration*
Disallow: /compare*
Disallow: /cart*
Disallow: /*?*lp*
Disallow: *applyFilter=*
Disallow: *?inCartProductId=*
Disallow: *?inCompareProductId=*
Host: ".$_SERVER['SERVER_NAME']."

User-agent: *
Allow: /uploads/
Disallow: /install*
Disallow: /mg-admin*
Disallow: /personal*
Disallow: /enter*
Disallow: /forgotpass*
Disallow: /payment*
Disallow: /registration*
Disallow: /compare*
Disallow: /cart*
Disallow: /*?*lp*
Disallow: *applyFilter=*
Disallow: *?inCartProductId=*
Disallow: *?inCompareProductId=*


Sitemap: http://".$_SERVER['SERVER_NAME']."/sitemap.xml";
    
    file_put_contents('robots.txt', $robots);
     
    $tables = array(
      'category',
      'category_user_property',
      'delivery',
      'order',
      'page',
      'payment',
      'plugins',
      'product',
      'product_user_property',
      'property',
      'setting',
      'user',
    );
    
       // отправка флага окончания установки
    $id = $idInstaller;
    if ($id) {

      $post = "&installer=".$id."&flag=install&edition=free";
      
      $url = "https://moguta.ru/checkinstaller";
      // Инициализация библиотеки curl.
      $ch = curl_init();
      // Устанавливает URL запроса.
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      // При значении true CURL включает в вывод заголовки.
      curl_setopt($ch, CURLOPT_HEADER, false);
      // Куда помещать результат выполнения запроса:
      //  false – в стандартный поток вывода,
      //  true – в виде возвращаемого значения функции curl_exec.
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      // Нужно явно указать, что будет POST запрос.
      curl_setopt($ch, CURLOPT_POST, true);
      // Здесь передаются значения переменных.
      curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
      // Максимальное время ожидания в секундах.
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
      // Выполнение запроса.
      $res = curl_exec($ch);
      curl_close($ch);
     }
     //копирование стандартного шаблона
     $templateDir = str_replace(DIRECTORY_SEPARATOR.'install', DIRECTORY_SEPARATOR.'mg-templates'.DIRECTORY_SEPARATOR, dirname(__FILE__));
     if (!is_file($templateDir.'moguta'.DIRECTORY_SEPARATOR.'template.php')) {
       copyDir($templateDir.'moguta-standard', $templateDir.'moguta');
     }
  }
}

/**
 * Фильтрует введенные пользователем данные
 *
 * @param string $str передаваемая строка
 * @param int $strong строгость проверки
 * @return string отфильтрованная строка
 *
 */
function clearData($str, $strong = 2) {

  switch ($strong) {
    case 1:
      return trim($str);
    case 2:
      return trim(strip_tags($str));
  }
}

function downloadDemoData($part){
  $imageZip = 'uploads.zip';
  $ch = curl_init('http://updata.moguta.ru/downloads/demofiles/uploads-8-'.$part.'.zip');
  $fp = fopen($imageZip, "w");
  curl_setopt($ch, CURLOPT_FILE, $fp);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_exec($ch);
  curl_close($ch);
  fclose($fp);

  if(file_exists($imageZip)) return true;

  return false;
}

/**
 * скачивание архива с изображениями для тестового магазина
 * @param string $imageFile путь к архиву на сервере
 * @return string|boolean в случае успеха путь к архиву в папке инсталлятора
 */
function downloadTestImage($imageFile){
    $imageZip = 'install/image.zip';
    $ch = curl_init($imageFile);
    $fp = fopen($imageZip, "w");
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);
    
    if(file_exists($imageZip)) return $imageZip;
    
    return false;
}

  /**
   * Распаковывает архив.
   * После распаковки удаляет заданный архив.
   *
   * @param $file - название архива, который нужно распаковать
   * @return bool
   */
  function extractZip($file) {
    $realDocumentRoot = str_replace(DIRECTORY_SEPARATOR.'install', '', dirname(__FILE__));
    $imageFolder = $realDocumentRoot.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR;
      
    if (file_exists($file)) {
      $zip = new ZipArchive;
      $res = $zip->open($file, ZIPARCHIVE::CREATE);

      if ($res === TRUE) {
        $zip->extractTo($imageFolder);
        $zip->close();
        unlink($file);

        return true;
      } else {
				unlink($file);
        return false;
      }
    }
    return false;
  }
    /**
   * Функция проверяет наличие установленных библиотек PHP
   * @return boolean|srting сообщение об отсутствии необходимого модуля
   */
  function libExists() {
        
      if(!function_exists('curl_init')){
        $res[] = 'Пакет libcurl не установлен! Библиотека cURL не подключена.';
      }
      
      if(!extension_loaded('zip')){
        $res[] = 'Пакет zip не установлен! Библиотека ZipArchive не подключена.';
      }
      
      file_put_contents('temp.txt', ' ');
      
      if(!file_exists('temp.txt')){
        $res[] = 'Нет прав на создание файла. Загрузка архива с обновлением невозможна';
      }else{
        unlink('temp.txt');
      }
      
     
      if(!filesize('.htaccess')){
        // создаем необходимый htaccess
        createHtAccess(getVersionHtaccess());
      }    
     
      
      return $res;
    }

  switch ($step) {
    case 0:
      require_once ('step0.php');
      break;
    case 1:
      require_once ('step1.php');
      break;
    case 2:
      require_once ('step2.php');
      break;
    case 3:
      require_once ('step3.php');
    break;
  }

// функция для определения нужного варпианта htaccess
function getVersionHtaccess() { 
  $verAc = 1;
    $result = false;
  if(!$result) {
    // создаем стандартный файл htaccess
    createHtAccess(1);
    // отправляем тестовый запрос для проверки перенаправления
    $ch = curl_init('http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'].'test?test=test');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    $info = curl_getinfo($ch);
    $result = $info['http_code'];
    curl_close($ch);
    $verAc = 1;
  }

  if($result != 200) {
    // создаем измененный файл htaccess
    createHtAccess(2);
    // отправляем тестовый запрос для проверки перенаправления
    $ch = curl_init('http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'].'test?test=test');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    $info = curl_getinfo($ch);
    $result = $info['http_code'];
    curl_close($ch);
    $verAc = 2;
  }

  if($result != 200) {
    // создаем измененный файл htaccess
    createHtAccess(3);
    // отправляем тестовый запрос для проверки перенаправления
    $ch = curl_init('http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'].'test?test=test');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    $info = curl_getinfo($ch);
    $result = $info['http_code'];
    curl_close($ch);
    $verAc = 3;
  }

  if($result != 200) {
    $verAc = 1;
  }

  @unlink('.htaccess');

  return $verAc;
}

// функция для создания файла htaccess
function createHtAccess($var = 1) {
if($var == 1) {
    $rewriteBase = '#RewriteBase /';
} else {
    $rewriteBase = 'RewriteBase /';
}

@unlink('.htaccess');

$htaccess = 'AddType image/x-icon .ico
AddDefaultCharset UTF-8

<IfModule mod_rewrite.c>
';
if($var != 3) {
    $htaccess .= 'Options +FollowSymlinks
Options -Indexes
';
}
$htaccess .= 'RewriteEngine on

'.$rewriteBase.'
#запрос к изображению напрямую без запуска движка 
RewriteCond %{REQUEST_URI} \.(png|gif|ico|swf|jpe?g|js|css|ttf|svg|eot|woff|yml|xml|zip|txt|doc)$
RewriteRule ^(.*) $1 [QSA,L]
RewriteCond %{REQUEST_FILENAME} !-f [OR]
RewriteCond %{REQUEST_URI} \.(ini|ph.*)$
RewriteRule ^(.*) index.php [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L,QSA]
</IfModule>
';
if($var != 3) {
    $htaccess .= '<IfModule mod_php5.c> 
php_flag magic_quotes_gpc Off
</IfModule>';
}

file_put_contents('.htaccess', $htaccess);
chmod('.htaccess', 0777);
} 

function copyDir($source, $dest) {
    mkdir($dest, 0755);
    if (!is_dir($source) || !is_dir($dest)) {return false;}
    foreach (
      $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST) as $item
        ) {
      if ($item->isDir()) {
        mkdir($dest.DIRECTORY_SEPARATOR.$iterator->getSubPathName());
      }
      else {
        copy($item, $dest.DIRECTORY_SEPARATOR.$iterator->getSubPathName());
      }
    }
  }