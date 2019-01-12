<?php
/**
 * Файл index.php расположен в корне CMS, является единственной точкой инициализирующей работу системы.
 *
 * В этом файле:
 *  - настраивается вывод ошибок;
 *  - устанавливаются константы для работы движка;
 *  - массивом $includePath задаются пути для поиска библиотек при подключении файлов движка.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Files
 */

//Не выводить предупреждения и ошибки.
Error_Reporting(0);
// Error_Reporting(E_ERROR | E_PARSE);

// Установка кодировки для вывода контента.
header('Content-Type: text/html; charset=utf-8');
define('DS', DIRECTORY_SEPARATOR);

/**
 * Путь корневой директории сайта.
 */
define('SITE_DIR', dirname(__FILE__).DS);

/**
 *  Текущая редакция.
 */
# Витрина

/**
 *  Текущая версия.
 */
define('VER', 'v8.3.0');

/**
 * Путь к директории ядра.
 */
define('CORE_DIR', 'mg-core/');

/**
 * Путь к директории с библиотеками движка.
 */
define('CORE_LIB', CORE_DIR.'lib/');

/**
 * Путь к директории с JS скриптам.
 */
define('CORE_JS', CORE_DIR.'script/');

/**
 * Путь к директории админки.
 */
define('ADMIN_DIR', 'mg-admin/');

/**
 * Путь к директории плагинов.
 */
define('PLUGIN_DIR', 'mg-plugins/'); 

/**
 * Путь к директории пользовательских php страниц.
 */
define('PAGE_DIR', 'mg-pages/');

// Установка путей, для поиска подключаемых библиотек.
$includePath = array(CORE_DIR,CORE_LIB);
set_include_path('.'.PATH_SEPARATOR.implode(PATH_SEPARATOR, $includePath));

/**
 * Автоматически подгружает запрошенные классы.
 * @param type $className наименование класса.
 * @return void
 */
spl_autoload_register(function($className) {
  	$path = str_replace('_', '/', strtolower($className));
	return @include_once $path.'.php';
});

/**
 * Подключает движок и запускает CMS.
 */
require_once ('mg-start.php');
