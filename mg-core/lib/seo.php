<?php
/**
 * Класс Seo - предназначен для работы с функционалом системы, относящимся к 
 * seo-оптимизации контента.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */
class Seo {

  /**
   * Возвращает набор шаблонов, для формарования сео тегов, для переданного типа страницы.
   * <code>
   *   $res = Seo::getTemplateForMeta('catalog');
   *   viewData($res);
   * </code>
   * @param string $type тип страницы
   * @return array
   */
  public static function getTemplateForMeta($type) {
    switch($type) {
      case 'catalog':
        $templates = array(
          'meta_title' => MG::getSetting('catalog_meta_title'),
          'meta_desc' => MG::getSetting('catalog_meta_description'),
          'meta_keywords' => MG::getSetting('catalog_meta_keywords'),
        );
        break;
      case 'product':
        $templates = array(
          'meta_title' => MG::getSetting('product_meta_title'),
          'meta_desc' => MG::getSetting('product_meta_description'),
          'meta_keywords' => MG::getSetting('product_meta_keywords'),
        );
        break;
      case 'page':
        $templates = array(
          'meta_title' => MG::getSetting('page_meta_title'),
          'meta_desc' => MG::getSetting('page_meta_description'),
          'meta_keywords' => MG::getSetting('page_meta_keywords'),
        );
        break;
    }    
    
    return $templates;
  }
  
  /**
   * Возвращает массив со значениями метатегов, сформированных по шаблонам, 
   * заданным в настройках системы.
   * <code>
   *   $res = Seo::getMetaByTemplate('catalog', $data);
   *   viewData($res);
   * </code>
   * @param string $type - тип страницы(каталог/товар/страница)
   * @param array $data - массив данных, используемых в шаблоне
   * @return array
   */
  public static function getMetaByTemplate($type, $data) {
    return $return;
  }

  /**
   * Создает в корневой папке сайта карту в формате XML.
   * <code>
   *   $res = Seo::autoGenerateSitemap();
   *   viewData($res);
   * </code>
   * @return int возвращает количество записанных в файл страниц
   */
  public function autoGenerateSitemap() {
    $urls = array();
    $tmpLangs = unserialize(stripslashes(MG::getSetting('multiLang')));
    foreach ($tmpLangs as $key => $value) {
      if($value['active'] == 'true') {
        $langs[] = $value['short'];
      }
    }
    // категории каталога     
    $result = DB::query('
      SELECT  url,  parent_url 
      FROM `'.PREFIX.'category` WHERE `invisible`=0 AND `activity`=1');
    while ($row = DB::fetchAssoc($result)) {
      $urls[] = $row['parent_url'].$row['url'];
      foreach ($langs as $key => $value) {
        $urls[] = $value.'/'.$row['parent_url'].$row['url'];
      }
    }
    // страницы товаров, с учетом флага коротких ссылок,
    if (SHORT_LINK == 1 || MG::getSetting('shortLink') == 'true') {
      $result = DB::query('   
      SELECT url
      FROM `'.PREFIX.'product` WHERE `activity`=1');
    } else {
      $result = DB::query('   
      SELECT CONCAT(c.parent_url,c.url,"/",p.url) as url
      FROM `'.PREFIX.'product` as p
      LEFT JOIN `'.PREFIX.'category` as c
      ON p.cat_id = c.id WHERE p.`activity`=1');
    }
    while ($row = DB::fetchAssoc($result)) {
      $urls[] = $row['url'];
      foreach ($langs as $key => $value) {
        $urls[] = $value.'/'.$row['url'];
      }
    }
    // статические страницы сайта
    $result = DB::query('
      SELECT  parent_url, url
      FROM `'.PREFIX.'page` WHERE invisible = 0');
    while ($row = DB::fetchAssoc($result)) {
      if ($row['url'] != 'index') {
        $pattern = "/^(http|https):\/\/([a-z0-9\.-]+)\.([a-z\.]{2,6})(.*)$/";
        $matches = array();
        preg_match($pattern, $row['url'], $matches);
        if (!empty($matches)) {
          if (trim($row['parent_url'], '/') == trim($matches[count($matches) - 1], '/')) {
            continue;
          }
          $urls[] = trim($matches[count($matches) - 1], '/');
          foreach ($langs as $key => $value) {
            $urls[] = $value.'/'.trim($matches[count($matches) - 1], '/');
          }
          continue;
        }
        $urls[] = $row['parent_url'].$row['url'];
        foreach ($langs as $key => $value) {
          $urls[] = $value.'/'.$row['parent_url'].$row['url'];
        }
      }
    }
    $res = DB::query("SELECT *  FROM ".PREFIX."plugins WHERE folderName = 'news' and active = '1'");
    if (DB::numRows($res)) {
      // страницы новостей  // 
      $result = DB::query('
       SELECT  url
       FROM `mpl_news`');
      while ($row = DB::fetchAssoc($result)) {
        $urls[] = 'news/'.$row['url'];
      }
    }
    $res = DB::query("SELECT *  FROM ".PREFIX."plugins WHERE folderName = 'blog' and active = '1'");
    if (DB::numRows($res)) {
      $result = DB::query("
       SELECT CONCAT(IFNULL(bc.url,''),'/',bi.url) as url
       FROM  ".PREFIX."blog_items as bi
	   LEFT JOIN  `".PREFIX."blog_item2category` as b2c ON b2c.`item_id` = bi.`id`
	   LEFT JOIN  `".PREFIX."blog_categories` as bc ON bc.`id` = b2c.`category_id`

	  ");
      while ($row = DB::fetchAssoc($result)) {
        $urls[] = str_replace('//', '/', 'blog/'.$row['url']);
      }
    }
    
    if (MG::getSetting('useSeoRewrites') == 'true') {
      $dbRes = DB::query("SELECT `short_url` FROM `".PREFIX."url_rewrite` WHERE `activity` = 1");
      if (DB::numRows($dbRes)) {
        while ($row = DB::fetchAssoc($dbRes)) {
          $urls[] = $row['short_url'];
        }
      }
    }
    
    // страницы из папки mg-pages  
    $files = scandir(PAGE_DIR);
    foreach ($files as $item) {
      $pathInfo = pathinfo($item);
      if ($pathInfo['extension'] == 'php' || $pathInfo['extension'] == 'html') {
        if ($pathInfo['filename'] != 'captcha') {
          $urls[] = $pathInfo['filename'];
        }
      }
    }
    // страницы с применеными фильтрами
    $res = DB::query('SELECT short_url FROM '.PREFIX.'url_rewrite');
    while($row = DB::fetchAssoc($res)) {
      $urls[] = $row['short_url'];
      foreach ($langs as $key => $value) {
        $urls[] = $value.'/'.$row['short_url'];
      }
    }

    $urls = array_unique($urls);    
    $exl = explode(';', MG::getSetting('excludeUrl'));
    foreach ($exl as &$url) {
      $url = str_replace(SITE.'/', '', trim($url));
    }
    $urls = array_diff($urls, $exl);
    $xmlSitemap = self::getXmlView(array_diff($urls, $exl));
    $string = $xmlSitemap;
    $f = fopen('sitemap.xml', 'w');
    $result = fwrite($f, $string);
    fclose($f);
    if ($result) {
      return count($urls);
    } else {
      return false;
    }
    
  }

  /**
   * Функция создания sitemap.xml.
   * <code>
   *   $urls = array(
   *     'http://test/1',
   *     'http://test/2',
   *   );
   *   $res = Seo::getXmlView();
   *   viewData($res);
   * </code>
   * @param array $urls массив ссылок на страницы
   * @return string xml данные для карты сайта
   */
  public function getXmlView($urls) {
    $nXML = '<?xml version="1.0" encoding="UTF-8"?>
      <urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
      ';
    $xml = new XMLWriter();
    $xml->openMemory();
    $xml->setIndent(true);
    $date = date("Y-m-d");
    foreach ($urls as $url) {
      $xml->startElement("url");
      $xml->writeElement("loc", SITE.'/'.$url);
      $xml->writeElement("lastmod", $date);
      $partsUrl = URL::getSections($url);
      $priority = count($partsUrl);
      if ($priority >= 3) {
        $priority = '0.5';
        // исключение для главной страницы
        if ($partsUrl[2] == 'ajax') {
          $priority = '1.0';
        }
      }
      if ($priority == 2) {
        $priority = '0.8';
      }
      if ($priority == 1) {
        $priority = '1.0';
      }
      $xml->writeElement("priority", $priority);
      $xml->endElement();
    }
    $nXML .= $xml->outputMemory();
    $nXML .= '</urlset>';
    return mb_convert_encoding($nXML, "WINDOWS-1251", "UTF-8");
  }

  /**
   * Применения SEO настроек сразу ко всем сущностям одного типа.
   * Установка мета тегов по шаблону.
   * <code>
   *   $res = Seo::getMetaByTemplateForAll('catalog');
   *   viewData($res);
   * </code>
   * @param string $type тип страницы
   * @return bool
   */
  public function getMetaByTemplateForAll($type) {
    // создание процедуры для обработки html тегов (удаление)
    $res = mysqli_query(DB::$connection, "DROP FUNCTION IF EXISTS strip_tags");
    
    if(!$res) {
      return false;
    }
    
    DB::query("
      CREATE FUNCTION strip_tags( x longtext CHARSET utf8 ) RETURNS longtext CHARSET utf8 
      LANGUAGE SQL NOT DETERMINISTIC READS SQL DATA
      BEGIN
      DECLARE sstart INT UNSIGNED;
      DECLARE ends INT UNSIGNED;
      IF ((x REGEXP '<') = 1) THEN
      SET sstart = LOCATE('<', x, 1);
      REPEAT
      SET ends = LOCATE('>', x, sstart);
      SET x = CONCAT(SUBSTRING( x, 1 ,sstart -1) ,SUBSTRING(x, ends +1 ));
      SET sstart = LOCATE('<', x, 1);
      UNTIL sstart < 1 END REPEAT;
      END IF;
      return x;
      END;");
    // составления соответсвия 
    switch ($type) {
      // товары
      case 'product':
        $templates = self::getTemplateForMeta('product');

        foreach($templates as $key => $template) {
          $templates[$key] = addslashes($template);
        }

        $dbRes = DB::query("SELECT `id`, `name` FROM `".PREFIX."property` WHERE type=\"string\"");

        while ($row = DB::fetchAssoc($dbRes)) {
          $data[$row['name']] = $row['id'];
        }

        // определение данных в атрибутах
        foreach($templates as $field=>$template) {
          $matches = array();
          preg_match_all("/{[\pL\s\d():_'\",]+}/u", $template, $matches);

          foreach($matches[0] as $cell=>$match) {
            $keys = mb_substr($match, 1, -1);
            
            if (mb_strpos($match, ":")) {
              $keys = explode(":", $keys);
              $arrayMatchProp['{stringsProperties:'.$keys[1].'}'] = '",(SELECT DISTINCT `name` FROM '.PREFIX.'product_user_property_data WHERE product_id = tProd.id AND prop_id = '.DB::quote($keys[1]).'),"';
            }
            if (mb_strpos($match, ",")) {
              $keys = explode(",", $keys);
              $desc = MG::nl2br($data[$keys[0]]);
              $desc = strip_tags($desc);
              $length = ($keys[1] > 160) ? 160 : $keys[1];
              $arrayMatchDesc['{description,'.$length.'}'] = '",(SUBSTRING(strip_tags(description),1,'.DB::quote($length, true).')),"';
            }
          }      
        }

        $arrayMatch = array(
          '{title}' => '",title,"',
          '{category_name}' => '",(SELECT title FROM `'.PREFIX.'category` AS tCat WHERE tCat.id = tProd.cat_id),"',
          '{code}' => '",code,"',
          '{meta_title}' => '",meta_title,"',
          '{meta_keywords}' => '",meta_keywords,"',
          '{meta_desc}' => '",meta_desc,"',
          '{price}' => '",price,"',
        );
        $ca = '",CASE ';
        foreach (MG::getSetting('currencyShort') as $key => $value) {
          $ca .= 'WHEN currency_iso = '.DB::quote($key).' THEN '.DB::quote($value).' ';
        }
        $ca .= 'END,"';
        $arrayMatch['{currency}'] = $ca;

        $templates['meta_title'] = addslashes($templates['meta_title']);
        $templates['meta_keywords'] = addslashes($templates['meta_keywords']);
        $templates['meta_desc'] = addslashes($templates['meta_desc']);

        $title = strtr($templates['meta_title'], $arrayMatch);
        if(!empty($arrayMatchDesc)) $title = strtr($title, $arrayMatchDesc);
        if(!empty($arrayMatchProp)) $title = strtr($title, $arrayMatchProp);

        $keywords = strtr($templates['meta_keywords'], $arrayMatch);
        if(!empty($arrayMatchDesc)) $keywords = strtr($keywords, $arrayMatchDesc);
        if(!empty($arrayMatchProp)) $keywords = strtr($keywords, $arrayMatchProp);

        $desc = strtr($templates['meta_desc'], $arrayMatch);
        if(!empty($arrayMatchDesc)) $desc = strtr($desc, $arrayMatchDesc);
        if(!empty($arrayMatchProp)) $desc = strtr($desc, $arrayMatchProp);
        DB::query('UPDATE '.PREFIX.'product AS tProd SET meta_title = concat("'.$title.'"), meta_keywords = concat("'.$keywords.'"), meta_desc = concat("'.$desc.'")');
        // 
        break;
      // категории
      case 'catalog':
        $templates = self::getTemplateForMeta('catalog');

        foreach($templates as $key => $template) {
          $templates[$key] = addslashes($template);
        }

        $dbRes = DB::query("SELECT `id`, `name` FROM `".PREFIX."property` WHERE type=\"string\"");

        while ($row = DB::fetchAssoc($dbRes)) {
          $data[$row['name']] = $row['id'];
        }

        // определение данных в атрибутах
        foreach($templates as $field=>$template) {
          $matches = array();
          preg_match_all("/{[\pL\s\d():_'\",]+}/u", $template, $matches);

          foreach($matches[0] as $cell=>$match) {
            $keys = mb_substr($match, 1, -1);
            
            if (mb_strpos($match, ",")) {
              $keys = explode(",", $keys);
              $desc = MG::nl2br($data[$keys[0]]);
              $desc = strip_tags($desc);
              $length = ($keys[1] > 160) ? 160 : $keys[1];
              $arrayMatchDesc['{cat_desc,'.$length.'}'] = '",SUBSTRING(strip_tags(html_content),1,'.$length.'),"';
            }
          }      
        }

        $arrayMatch = array(
          '{titeCategory}' => '",title,"',
          '{meta_title}' => '",meta_title,"',
          '{meta_keywords}' => '",meta_keywords,"',
          '{meta_desc}' => '",meta_desc,"'
          );

        $title = strtr($templates['meta_title'], $arrayMatch);
        if(!empty($arrayMatchDesc)) $title = strtr($title, $arrayMatchDesc);

        $keywords = strtr($templates['meta_keywords'], $arrayMatch);
        if(!empty($arrayMatchDesc)) $keywords = strtr($keywords, $arrayMatchDesc);

        $desc = strtr($templates['meta_desc'], $arrayMatch);
        if(!empty($arrayMatchDesc)) $desc = strtr($desc, $arrayMatchDesc);

        DB::query('UPDATE '.PREFIX.'category SET meta_title = concat("'.$title.'"), meta_keywords = concat("'.$keywords.'"), meta_desc = concat("'.$desc.'")');
        // 
        break;
      // страницы
      case 'page':
        $templates = self::getTemplateForMeta('page');

        foreach($templates as $key => $template) {
          $templates[$key] = addslashes($template);
        }

        $dbRes = DB::query("SELECT `id`, `name` FROM `".PREFIX."property` WHERE type=\"string\"");

        while ($row = DB::fetchAssoc($dbRes)) {
          $data[$row['name']] = $row['id'];
        }

        // определение данных в атрибутах
        foreach($templates as $field=>$template) {
          $matches = array();
          preg_match_all("/{[\pL\s\d():_'\",]+}/u", $template, $matches);

          foreach($matches[0] as $cell=>$match) {
            $keys = mb_substr($match, 1, -1);
            
            if (mb_strpos($match, ",")) {
              $keys = explode(",", $keys);
              $desc = MG::nl2br($data[$keys[0]]);
              $desc = strip_tags($desc);
              $length = ($keys[1] > 160) ? 160 : $keys[1];
              $arrayMatchDesc['{html_content,'.$length.'}'] = '",SUBSTRING(strip_tags(html_content),1,'.$length.'),"';
            }
          }      
        }

        $arrayMatch = array(
          '{title}' => '",title,"',
          '{meta_title}' => '",meta_title,"',
          '{meta_keywords}' => '",meta_keywords,"',
          '{meta_desc}' => '",meta_desc,"'
          );

        $title = strtr($templates['meta_title'], $arrayMatch);
        if(!empty($arrayMatchDesc)) $title = strtr($title, $arrayMatchDesc);

        $keywords = strtr($templates['meta_keywords'], $arrayMatch);
        if(!empty($arrayMatchDesc)) $keywords = strtr($keywords, $arrayMatchDesc);

        $desc = strtr($templates['meta_desc'], $arrayMatch);
        if(!empty($arrayMatchDesc)) $desc = strtr($desc, $arrayMatchDesc);

        DB::query('UPDATE '.PREFIX.'page SET meta_title = concat("'.$title.'"), meta_keywords = concat("'.$keywords.'"), meta_desc = concat("'.$desc.'")');
        // 
        break;
    }

    return true;
  }
}