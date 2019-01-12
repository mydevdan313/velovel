<?php

/*
  Plugin Name: Редактор блоков сайта
  Description: Плагин определяет шорткод [site-block id=number]
  Author: Гайдис Михаил
  Version: 1.1.5
 */

new SiteBlockEditor;

class SiteBlockEditor {

  private static $pluginName = ''; // название плагина (соответствует названию папки)
  private static $path = ''; //путь до файлов плагина 

  public function __construct() {

    mgActivateThisPlugin(__FILE__, array(__CLASS__, 'activate')); //Инициализация  метода выполняющегося при активации  
    mgAddAction(__FILE__, array(__CLASS__, 'pageSettingsPlugin')); //Инициализация  метода выполняющегося при нажатии на кнопку настроект плагина  
    mgAddShortcode('site-block', array(__CLASS__, 'getCode')); // Инициализация шорткода [slider-action] - доступен в любом HTML коде движка.    

    self::$pluginName = PM::getFolderPlugin(__FILE__);
    self::$path = PLUGIN_DIR.self::$pluginName;

    if (!URL::isSection('mg-admin')) { // подключаем CSS плагина для всех страниц, кроме админки
      mgAddMeta('<link rel="stylesheet" href="'.SITE.'/'.self::$path.'/css/style.css" type="text/css" />');
    }
  }
  
  /**
   * Метод выполняющийся при активации палагина 
   */
  static function activate() {
    self::createDateBase();
  }
  
  /**
   * Метод выполняющийся перед генераццией страницы настроек плагина
   */
  static function preparePageSettings() {
    echo '   
      <link rel="stylesheet" href="'.SITE.'/'.self::$path.'/css/style.css" type="text/css" />
      <script type="text/javascript">
        includeJS("'.SITE.'/'.self::$path.'/js/script.js");  
      </script> 
    ';
  }
  
  /**
   * Создает таблицу плагина в БД
   */
  static function createDateBase() {
    DB::query("
     CREATE TABLE IF NOT EXISTS `".PREFIX.self::$pluginName."` (
      `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
      `comment` text NOT NULL,
      `type` varchar(255) NOT NULL,
	    `content` text NOT NULL,
      `width` text NOT NULL, 
      `height` text NOT NULL,      
      `alt` text NOT NULL,
      `title` text NOT NULL,
      `href` text NOT NULL,
      `class` text NOT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
  }

  /**
   * Выводит страницу настроек плагина в админке
   */
  static function pageSettingsPlugin() {
    $pluginName = self::$pluginName;
    $entity = self::getEntity();

    self::preparePageSettings();

    include('pageplugin.php');
  }
  
  /**
   * Получает из БД записи
   */
  static function getEntity() {
    $entity = array();
    $res = DB::query("
      SELECT * 
      FROM `".PREFIX.self::$pluginName."` 
        ORDER BY id ASC
    ");
    while ($row = DB::fetchAssoc($res)) {
      $entity[] = $row;
    }

    return $entity;
  }

  static function getCode($arg) {
    $id = $arg['id'];
    $res = DB::query('SELECT * FROM `'.PREFIX.self::$pluginName.'` WHERE id = '.DB::quote($id));
    while ($row = DB::fetchAssoc($res)) {
      $result[] = $row;
    }

    $html = '';

    foreach ($result as $item) {
      if($item['type'] == 'img') {
        if($item['height'] == '') $item['height'] = ''; else $item['height'] = 'height:'.$item['height'].'px;';
        if($item['width'] == '') $item['width'] = ''; else $item['width'] = 'width:'.$item['width'].'px;';
        $html .= '<img src="'.$item['content'].'" style="'.$item['height'].$item['width'].'" class="'.$item['class'].'" alt="'.$item['alt'].'" title="'.$item['title'].'">';

        if($item['href'] != '') {
          $html = '<a href="'.$item['href'].'">'.$html.'</a>';
        }
      } else {
        $html .= '<div>'.$item['content'].'</div>';
      }

      if(@$_SESSION['user']->enabledSiteEditor == 'true') {
        $html = '<div class="exist-admin-context site-block-editor" data-item="'.$item['id'].'">
                  '.$html.'
                  <div class="admin-context" style="display: none;">
                    <div class="modalOpenPlugin" style="width:120px;" data-section="site-block-editor" data-sectionJs="siteBlockEditorJs" data-param="[\'edit\','.$item['id'].']">Редактировать блок <span class="admin-edit-icon"></span></div>
                  </div>
                </div>';
      }
      return $html;
    }
  }

}