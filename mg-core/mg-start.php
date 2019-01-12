<?php

/**
 * Файл mg-start.php расположен в корне ядра, запускает движок и выводит на 
 * экран сгенерированную им страницу сайта.
 *
 * Инициализирует компоненты CMS, доступные из любой точки программы.
 * - DB - класс для работы с БД;
 * - Storage - класс для работы с кэшем;
 * - MG - класс содержащий функционал системы;
 * - URL - класс для работы со ссылками;
 * - PM - класс для работы с плагинами.
 * - User - класс для работы с профайлами пользователей;
 * - Mailer - класс для отправки писем.
 * 
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Files
 */

require_once (CORE_LIB.'encodeupdate.php');
ini_set ( 'session.serialize_handler', 'php');
MG::getConfigIni();

// Инициализация компонентов CMS.
DB::init();
Storage::init();
session_start();
MG::init();
URL::init();
User::init();
Mailer::init();
Urlrewrite::init();
Property::init();

if(file_exists('modificatoryInc.php')) {
	@include 'modificatoryInc.php';
	@unlink('modificatoryInc.php');
}

function mgShutdown() {
    Storage::close();
    session_write_close();
    DB::close();
}
register_shutdown_function('mgShutdown');

// Если сайт временно закрыт, то выводитя заглушка, хранящаяся в корне движка.  
if (MG::isDowntime()) {
  require_once 'downTime.html';
  exit;
}

// Запоминает откуда пришел пользователь.
MG::logReffererInfo();

// Подключить index.php всех плагинов.
PM::includePlugins();

// Хук выполняющийся до запуска движка.
MG::createHook('mg_start');

// Запуск движка.
$moguta = new Moguta;
$moguta = $moguta->run();

// Вывод результата на экран, предварительно обработав все возможные шорткоды.
echo URL::multiLangLink(PM::doShortcode(MG::printGui($moguta)));

// Хук выполняющийся после того как отработал движок.
MG::createHook('mg_end', true, $moguta);

// Ввывод консоли запросов к БД.
if (DEBUG_SQL) {
  echo DB::console();
}

// Завершение процесса кэширвания.

Storage::close();
session_write_close();

// echo microtime(true) - $_SERVER['REQUEST_TIME'];