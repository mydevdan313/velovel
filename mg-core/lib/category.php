<?php

/**
 * Класс Category - совершает все операции с категориями товаров.
 * - Создает новую категорию;
 * - Удаляет категорию; 
 * - Редактирует категорию;
 * - Возвращает список id всех вложенных категорий;
 * - Возвращает древовидный список категорий, пригодный для использования в меню;
 * - Возвращает массив id категории и ее заголовок;
 * - Возвращает иерархический массив категорий;
 * - Возвращает отдельные пункты списка заголовков категорий.
 * - Генерирует UL список категорий для вывода в меню.
 * - Экземпляр класса категорий хранится в реестре класс MG
 * <code>
 * //пример вызова метода getCategoryListUl() из любого места в коде.
 * MG::get('category')->getCategoryListUl()
 * </code>
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */
class Category {

  // Массив категрорий.
  private $categories;
  private $listCategoryId;

  public function __construct() {
    // проверка целостности NESTED SETS
    $res = DB::query('SELECT COUNT(id), MIN(left_key), MAX(right_key) FROM '.PREFIX.'category', true);
    $resCheck = DB::fetchAssoc($res);
    if($res) {
      if(($resCheck['MIN(left_key)'] != 2) || ((($resCheck['COUNT(id)'] * 2) + 1) != $resCheck['MAX(right_key)'])) {
        self::startIndexation();
        Storage::clear();
      }
    }
    // получаем список категорий
    $this->categories = Storage::get('categoryInit-'.LANG);
    
	// проверка корректности ссылок в кэше
    if($this->categories != null) {
  		$arrkey = array_keys($this->categories);
  		$firsdId = $arrkey[0];
  		$normalLink = SITE.'/'.$this->categories[$firsdId]['parent_url'].$this->categories[$firsdId]['url'];
  		if($this->categories[$firsdId]['link'] != $normalLink) {
  			$this->categories = null;
  		}
  	}
    
    if($this->categories == null) {  

      $result = DB::query('SELECT * FROM `'.PREFIX.'category` ORDER BY sort');
      $listId = "";
      while ($cat = DB::fetchAssoc($result)) {
        $listId .= ','.$cat['id'];
        $link = SITE.'/'.$cat['parent_url'].$cat['url'];              
        $cat['link'] = $link;

        MG::loadLocaleData($cat['id'], LANG, 'category', $cat);

        $this->categories[$cat['id']] = $cat;     
        $this->categories[$cat['id']]['userProperty'] = array();
        $this->categories[$cat['id']]['propertyIds'] = array();
      }
      if($listId) {
        $listId = "in (".ltrim($listId,',').")";    
      }
      
      // старый кривовастый подсчет товаров для меню
      if(MG::getSetting('catalogPreCalcProduct') == 'old') {
        foreach ($this->categories as $key => $value) {
          $this->categories[$key]['countProduct'] = 0; 
        }

        $onlyInCount = '';
        
        if(MG::getSetting('printProdNullRem') == "true") {
            $onlyInCount = 'AND ABS(IFNULL( pv.`count` , 0 ) ) + ABS( p.`count` ) >0'; // ищем только среди тех которые есть в наличии
        }    

        // получаем строку с идентификаторами дополнительных категорий
        $res = DB::query("SELECT GROUP_CONCAT(REPLACE(`inside_cat`, `cat_id`, '')) AS insideCatRow FROM ".PREFIX."product WHERE `inside_cat` <> ''");
        while ($row = DB::fetchAssoc($res)) {  
          $idRow = ','.$row['insideCatRow'].',';  
        }

        // viewdatA($idRow);
        // viewdatA(1);

        // получаем количесво товаров для каждой категории
        $res = DB::query("SELECT cat_id, count(DISTINCT p.id) as count FROM `".PREFIX."product` p 
          LEFT JOIN `".PREFIX."product_variant` pv ON pv.`product_id`=p.`id` LEFT JOIN `".PREFIX."category` c ON c.`id`=p.`cat_id`"
          ." WHERE p.`activity` = 1 AND c.`id` IS NOT NULL ".$onlyInCount." GROUP BY cat_id");
        while ($row = DB::fetchAssoc($res)) {  
          $this->categories[$row['cat_id']]['countProduct'] = $row['count'];  
        }

        $res = DB::query('SELECT id FROM '.PREFIX.'category');
        while ($row = DB::fetchAssoc($res)) {
          @$this->categories[$row['id']]['countProduct'] += substr_count(','.$idRow.',', ','.$row['id'].',');   
        }
      }

      // viewdata($this->categories);

      // для каждой категории получаем массив пользовательских характеристик
      // $res = DB::query("
      //     SELECT p.*, c.category_id
      //     FROM `".PREFIX."category_user_property` AS c, `".PREFIX."property` AS p
      //     WHERE c.category_id ".$listId."
      //     AND (p.id = c.property_id OR p.all_category = 1)
      //     ORDER BY `sort` DESC"
      // );
      
      // while ($prop = DB::fetchAssoc($res)) {
      //   $prop['type_view'] = 'type_view';
      //   $this->categories[$prop['category_id']]['userProperty'][$prop['id']] = $prop;
      //   $this->categories[$prop['category_id']]['propertyIds'][] = $prop['id'];
      // }
      Storage::save('categoryInit-'.LANG, $this->categories);
    }
 
  }

  public function startIndexation() {
    $_categories = NULL;
    $_categories[-1][0] = array(
      'id' => 0,
      'sort' => -1,
      'parent' => -1);
    $result = DB::query('SELECT id, parent, sort FROM `'.PREFIX.'category` ORDER BY sort');
    while ($cat = DB::fetchAssoc($result)) {
      $_categories[$cat['parent']][$cat['id']] = $cat;     
    }
    self::indexation($_categories);
  }

  public function indexation($_categories, $parent = -1, $level = 0, $left_key = 0, $right_key = 0) {
    $catArray = array();
    if (!empty($_categories[$parent])) {
      foreach ($_categories[$parent] as $category) { 
        if ($parent == $category['parent']) {  
          $left_key += 2;
          $category['left_key'] = $left_key - 1;
          $category['right_key'] = $left_key;
          $child = self::indexation($_categories, $category['id'], $level+1, $category['left_key'], $category['right_key']);
          if (!empty($child)) {
            $chAr = end($child);
            $right_key = $chAr['right_key'] + 1;
            $left_key = $chAr['right_key'] + 1;
            $category['right_key'] = $right_key;
            $array = $category;
            $array['level'] = $level;  
            $array['child'] = $child;       
          } else {
            $array = $category;
            $array['level'] = $level;  
          }
          $catArray[] = $array;
          $toDb = $array;
          unset($toDb['child']);
          unset($toDb['sort']);
          unset($toDb['parent']);
          DB::query('UPDATE '.PREFIX.'category SET '.DB::buildPartQuery($toDb).' WHERE id = '.DB::quote($toDb['id']));
        }
      }
    }
    $result = $catArray;
    return $result;
  }
  
 /**
   * Возвращает полный url категории по ее id.
   * <code>
   *  $res = MG::get('category')->getParentUrl(12);
   *  viewData($res);
   * </code>
   * @param $parentId - id категории для которой нужно найти UR родителя.
   * @return string
   */
  public function getParentUrl($parentId) {
    $cat = $this->getCategoryById($parentId);
    $res = !empty($cat) ? $cat['parent_url'].$cat['url'] : '';
    return $res ? $res.'/' : '';
  }
  
  /**
   * Сжимает изображение категории, по заданным в настройках параметрам.
   * <code>
   *  $imageUrl = 'uploads/image.png';
   *  $res = MG::get('category')->resizeCategoryImg($imageUrl);
   *  viewData($res);
   * </code>
   * @param string путь к файлу
   * @return string
   */
  private static function resizeCategoryImg($file, $id, $prefix) {
    $imgFolder = 'uploads'.DS.'category'.DS.$id;

    $categoryImgHeight = MG::getSetting('categoryImgHeight')?MG::getSetting('categoryImgHeight'):200;
    $categoryImgWidth = MG::getSetting('categoryImgWidth')?MG::getSetting('categoryImgWidth'):200;
    // $realDocumentRoot = str_replace(DS.'mg-core'.DS.'lib', '', $dirname = dirname(__FILE__)); 

    $file = urldecode($file);    
    $arName = URL::getSections($file);
    $name = array_pop($arName);
    $arNameExt = explode('.', $name);
    $ext = array_pop($arNameExt);
    $name = MG::translitIt(implode('.', $arNameExt));
    if (strpos($name, $prefix) === 0) {
      $prefix = '';
    }
    $pos = strpos($name, '_-_time_-_');
    if ($pos) {
      $name = substr($name, ($pos+10));
      if (MG::getSetting('addDateToImg') == 'true') {
        $name .= date("_Y-m-d_H-i-s");
      }
    }
    
    $name = $prefix.$name.'.'.$ext;
    if ('svg' == strtolower($ext) || 'gif' == strtolower($ext)) {
      @copy(SITE_DIR.$file, SITE_DIR.DS.$imgFolder.DS.$name);
      return DS.$imgFolder.DS.$name;
    }  
    
    $upload = new Upload(false);
    $upload->_reSizeImage($name, SITE_DIR.$file, $categoryImgWidth, $categoryImgHeight, "PROPORTIONAL", $imgFolder.DS, true);        
    
    return DS.$imgFolder.DS.$name;
  }

  /**
   * Создает новую категорию.
   * <code>
   *  $array = array(
   *    'id' => ,              // id
   *    'unit' => 'шт.',       // единица измерения товаров
   *    'title' => 123,        // название категории
   *    'url' => 123,          // url последней секции категории
   *    'parent' => 0,         // id родительской категории
   *    'html_content' => ,    // описание категории
   *    'meta_title' => ,      // заголовок страницы
   *    'meta_keywords' => ,   // ключевые слова
   *    'meta_desc' => ,       // мета описание
   *    'image_url' => ,       // ссылка на изображение
   *    'menu_icon' => ,       // ссылка на иконку меню
   *    'invisible' => 0,      // параметр видимости
   *    'rate' => 0,           // наценка
   *    'seo_content' => ,     // seo контент
   *    'seo_alt' => ,         // seo 
   *    'seo_title' => ,       // seo
   *    'parent_url' => ,      // url родительской категории
   *  );
   *  $res = MG::get('category')->addCategory($array);
   *  viewData($res);
   * </code>
   * @param array $array массив с данными о категории.
   * @return bool|int в случае успеха возвращает id добавленной категории.
   */
  public function addCategory($array) {    
//    unset($array['id']);
    $result = array();
    if(!empty($array['url'])) {
      $array['url'] = URL::prepareUrl($array['url']); 
    }
    
    $maskField = array('title','meta_title','meta_keywords','meta_desc','image_title','image_alt');

    foreach ($array as $k => $v) {
       if(in_array($k, $maskField)) {
        $v = htmlspecialchars_decode($v);
        $array[$k] = htmlspecialchars($v);       
       }
    }

    // if(!empty($array['image_url'])) {
    //   $array['image_url'] = self::resizeCategoryImg($array['image_url']);
    // }

    // if(!empty($array['menu_icon'])) {
    //   $array['menu_icon'] = self::resizeCategoryImg($array['menu_icon']);
    // }
    
    // Исключает дублирование.
    $dublicatUrl = false;
    
    $tempArray = $this->getCategoryByUrl($array['url'],$array['parent_url']);
    if (!empty($tempArray)) {
      $dublicatUrl = true;
    }
    $array['sort'] = (int)$array['id'];
    $res = DB::query('SELECT MAX(id) FROM '.PREFIX.'category');
    while($row = DB::fetchAssoc($res)) {
      $array['sort'] = $row['MAX(id)']+1;
    }
    
    $id = $array['id'];
    unset($array['id']);
    if (!empty($array['csv'])) {
      $array['id'] = $id;
      unset($array['csv']);
    }    

    if (DB::buildQuery('INSERT INTO `'.PREFIX.'category` SET ', $array)) {
      $id = DB::insertId();

      // Если url дублируется, то дописываем к нему id продукта.
      if ($dublicatUrl) {
        $arr = array('id' => $id,'sort'=>$id, 'url' => $array['url'].'_'.$id, 'image_url' => $array['image_url'], 'menu_icon' => $array['menu_icon']);
      } else{
        $arr = array('id' => $id,'sort'=>$id, 'url' => $array['url'], 'image_url' => $array['image_url'], 'menu_icon' => $array['menu_icon']);    
      }
      $this->listCategoryId[] = $id;
      $this->updateCategory($arr);     
      $array['id'] = $id;
      $result = $array;
    }

    //очищам кэш категорий
    Storage::clear('category');
    
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Изменяет данные о категории.
   * <code>
   *  $array = array(
   *    'id' => ,              // id
   *    'unit' => 'шт.',       // единица измерения товаров
   *    'title' => 123,        // название категории
   *    'url' => 123,          // url последней секции категории
   *    'parent' => 0,         // id родительской категории
   *    'html_content' => ,    // описание категории
   *    'meta_title' => ,      // заголовок страницы
   *    'meta_keywords' => ,   // ключевые слова
   *    'meta_desc' => ,       // мета описание
   *    'image_url' => ,       // ссылка на изображение
   *    'menu_icon' => ,       // ссылка на иконку меню
   *    'invisible' => 0,      // параметр видимости
   *    'rate' => 0,           // наценка
   *    'seo_content' => ,     // seo контент
   *    'seo_alt' => ,         // seo 
   *    'seo_title' => ,       // seo
   *    'parent_url' => ,      // url родительской категории
   *  );
   *  $res = MG::get('category')->updateCategory($array);
   *  viewData($res);
   * </code>
   * @param array $array массив с данными о категории.
   * @return bool|int в случае добавления возвращает id добавленной категории.
   */
  public function updateCategory($array) {
    $id = $array['id'];

    if (!empty($array['image_url']) || !empty($array['menu_icon']) || strpos($array['html_content'], 'src="'.SITE.'/uploads') || strpos($array['seo_content'], 'src="'.SITE.'/uploads')) {
      @mkdir(SITE_DIR.'uploads'.DS.'category', 0755);
      @mkdir(SITE_DIR.'uploads'.DS.'category'.DS.$id, 0755);
    }
    if ($array['html_content']) {
      $array['html_content'] = MG::moveCKimages($array['html_content'], 'category', $id, 'desc', 'category', 'html_content');
    }
    if ($array['seo_content']) {
      $array['seo_content'] = MG::moveCKimages($array['seo_content'], 'category', $id, 'seo', 'category', 'seo_content');
    }

    $result = false;
    if(!empty($array['url'])) {
     $array['url'] = URL::prepareUrl($array['url']);     
    }elseif(!empty($array['title'])) {
      $array['url'] = MG::translitIt($array['title']);
      $array['url'] = URL::prepareUrl($array['url']);
    }

    unset($array['rate']);
    
    // перехватываем данные для записи, если выбран другой язык
    $lang = $array['lang'];
    unset($array['lang']);

    
    $maskField = array('title','meta_title','meta_keywords','meta_desc','image_title','image_alt','seo_title','seo_alt','menu_title');

    foreach ($array as $k => $v) {
      if(in_array($k, $maskField)) {
        $v = htmlspecialchars_decode($v);
        $array[$k] = htmlspecialchars($v);       
      }
    }
 
    // Если назначаемая категория, является тойже.
    if ($array['parent']===$id) {
      $this->messageError = 'Нельзя назначить выбраную категорию родительской!';
      return false;
    }

    if ($id || $id===0) {
      $childsCaterory = $this->getCategoryList($id);
    }

    // Если есть вложенные, и одна из них назначена родительской.
    if (!empty($childsCaterory)) {
      foreach ($childsCaterory as $cateroryId) {
        if ($array['parent']==$cateroryId) {
          $this->messageError = 'Нельзя назначить выбраную категорию родительской!';         
          return false;
        }
      }
    }

    if ($_POST['parent']===$id && !isset($array['parent'])) {
      $this->messageError = 'Нельзя назначить выбраную категорию родительской!';
      return false;
    }
    
    if(!empty($array['image_url'])) {
      $array['image_url'] = self::resizeCategoryImg($array['image_url'], $id, '');
    }

    if(!empty($array['menu_icon'])) {
      $array['menu_icon'] = self::resizeCategoryImg($array['menu_icon'], $id, 'menu_');
    }
    
    $catIds = array_keys($this->categories);
    
    if (!empty($id) && (in_array($id, $this->listCategoryId) || in_array($id, $catIds))) {
      // обновляем выбраную категорию
      unset($array['csv']);
      if (DB::query('
        UPDATE `'.PREFIX.'category`
        SET '.DB::buildPartQuery($array).'
        WHERE id = '.DB::quote(intval($id), true))) {        
        $result = true;
      }
      

      // находим список всех вложенных в нее категорий
      $arrayChildCat = $this->getCategoryList($array['parent']);
      if (!empty($arrayChildCat)) {
        // обновляем parent_url у всех вложенных категорий, т.к. корень поменялся
        foreach ($arrayChildCat as $childCat) {
         
          $childCat = $this->getCategoryById($childCat);
          $upParentUrl = $this->getParentUrl($childCat['parent']);
          if(!empty($childCat['id'])) {
            if (DB::query('
                UPDATE `'.PREFIX.'category`
                SET parent_url='.DB::quote($upParentUrl).'
            WHERE id = '.DB::quote($childCat['id'], true)));
          }
       
        }
      }
    } else {
      $result = $this->addCategory($array);      
    }

    // обновляем ключи
    $this->startIndexation();

    //очищам кэш категорий
    Storage::clear('category');
    
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Удаляет категорию.
   * <code>
   *  $res = MG::get('category')->delCategory(12);
   *  viewData($res);
   * </code>
   * @param int $id id удаляемой категории.
   * @return bool
   */
  public function delCategory($id) {
    $categories = $this->getCategoryList($id);
    $categories[] = $id;
    foreach ($categories as $categoryID) {
      DB::query('
        DELETE FROM `'.PREFIX.'category`
        WHERE id = %d
      ', $categoryID);
      MG::rrmdir(SITE_DIR.'uploads/category/'.$categoryID);
    }

    //очищам кэш категорий
    Storage::clear('category');
    
    $args = func_get_args();
    $result = true;
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

   /**
    * Возвращает закэшированное левое меню категорий.
    * <code>
    *  $res = MG::get('category')->getCategoriesHTML();
    *  viewData($res);
    * </code>
    * @return string
    */
   public function getCategoriesHTML() {  
      $result = Storage::get('getCategoriesHTML-'.LANG);     

      if($result == null) {        
                
        $category = $this->getHierarchyCategory();
        $result =  MG::layoutManager('layout_leftmenu', array('categories'=>$category));
      
        if (!empty($_SESSION['user']->enabledSiteEditor) && $_SESSION['user']->enabledSiteEditor == "false") {
          Storage::save('getCategoriesHTML-'.LANG, $result);        
        }
      }
     
      $args = func_get_args();
      return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
   }
   
    /**
    * Возвращает закэшированное горизонтальное меню категорий.
    * <code>
    *  $res = MG::get('category')->getCategoriesHorHTML();
    *  viewData($res);
    * </code>
    * @return string
    */
   public function getCategoriesHorHTML() {  
      $result = Storage::get('getCategoriesHorHTML-'.LANG);     

      if($result == null) {        
                
        $category = $this->getHierarchyCategory();
        $result =  MG::layoutManager('layout_horizontmenu', array('categories'=>$category));
      
        if ($_SESSION['user']->enabledSiteEditor == "false") {
          Storage::save('getCategoriesHorHTML-'.LANG, $result);        
        }
      }
     
      $args = func_get_args();
      return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
   }
   
  /**
   * Возвращает древовидный список категорий, пригодный для использования в меню.
   * <code>
   *  $res = MG::get('category')->getCategoryListUl();
   *  viewData($res);
   * </code>
   * @param int $parent id категории, для которой надо вернуть список.
   * @param int $type тип списка (для публичной части, либо для админки).
   * @param bool $recursion использовать рекурсию.
   * @return string
   */
  public function getCategoryListUl($parent = 0, $type = 'public', $recursion=true, $sql = true, $categories = array()) {
    // получаем данные об открытых категориях из куков  
    if(empty($this->openedCategory)) {
      if($type == 'admin') {
        if(!empty($_COOKIE['openedCategoryAdmin'])) {
          $this->openedCategory = json_decode($_COOKIE['openedCategoryAdmin']);    
        }
      } else {
        if(!empty($_COOKIE['openedCategory'])) {
          $this->openedCategory = json_decode($_COOKIE['openedCategory']);  
        }
      }
      if(empty($this->openedCategory)) {
        $this->openedCategory = array();
      }    
    }

    if($sql) $categories = self::getCategoryFromBdNestedH($parent);
    $print = '';
    if (empty($categories)) {
      $print = '';
    } else {
      $lang = MG::get('lang');
      $categoryArr = $categories[$parent];
      
      //для публичной части убираем из меню закрытые категории
      if($type == 'public') {
        foreach ($categoryArr as $key => $val) {
           if($val['invisible'] == 1) {
             unset($categoryArr[$key]);
           } 
        }
      }
      
      foreach ($categoryArr as $category) {
        if(!isset($category['id'])) break; //если категории неceotcndetn
        if ($parent == $category['parent']) {

          $flag = false;
          
          $mover = '';

          if ('admin' == $type) {
            $class = 'active';
            $title = $lang['ACT_EXPORT_CAT'];
            
            if($category['export'] == 0) {
              $class = '';
              $title = $lang['ACT_NOT_EXPORT_CAT'];
            }
            
            $export = '<div class="export tool-tip-bottom ' . $class . '" title="' . $title . '" data-category-id="' . $category['id'] . '"></div>';
            
            $class = 'active';
            $title = $lang['ACT_V_CAT'];
            
            if ($category['invisible'] == 1) {
              $class = '';
              $title = $lang['ACT_UNV_CAT'];
            }
            $classAct = 'active';
            $titleAct = $lang['ACT_V_CAT_ACT'];
            
            if ($category['activity'] == 0) {
              $classAct = '';
              $titleAct = $lang['ACT_UNV_CAT_ACT'];
            }

            $checkbox = '<input type="checkbox" name="category-check">';
            $mover .= $checkbox . '<div class="mover"></div>'
              . '<div class="link-to-site tool-tip-bottom" title="' . $lang['MOVED_TO_CAT'] . '"  data-href="' . SITE . '/' . $category['parent_url'] . $category['url'] . '"></div>'.
              $export.'<div class="visible tool-tip-bottom ' . $class . '" title="' . $title . '" data-category-id="' . $category['id'] . '"></div>'.
              '<div class="activity tool-tip-bottom ' . $classAct . '" title="' . $titleAct . '" data-category-id="' . $category['id'] . '"></div>';
          }

          $slider = '>'.$mover;

          foreach ($categories as $sub_category) {             
            if (isset($sub_category['parent']) && ($category['id'] == $sub_category['parent'])) {
              $slider = ' class="slider">'.$mover.'<div class="slider_btn"></div>';
              $style = "";
              $opened = "";

              if(in_array( $category['id'],$this->openedCategory)) {
                $opened = " opened ";
                $style=' style="background-position: 0 0"';
              }
              
              $slider = ' class="slider">'.$mover.'<div class="slider_btn '.$opened.'" '.$style.'></div>';
              $flag = true;
              break;
            }
          }
            
          $rate = '';
          if ($category['rate']>0) {
            $rate = '<div class="sticker-menu discount-rate-up" data-cat-id="'.$category['id'].'"> '.$lang['DISCOUNT_UP'].' +'.($category['rate']*100).'% <div class="discount-mini-control"><span class="discount-apply-follow tool-tip-bottom"  title="Применить ко всем вложенным категориям" >&darr;&darr;</span> <span class="discount-cansel tool-tip-bottom" title="Отменить">x</span></div></div>';
          }
          if ($category['rate']<0) {
            $rate = '<div class="sticker-menu discount-rate-down" data-cat-id="'.$category['id'].'"> '.$lang['DISCOUNT_DOWN'].' '.($category['rate']*100).'% <div class="discount-mini-control"><span class="discount-apply-follow tool-tip-bottom"  title="Применить ко всем вложенным категориям">&darr;&darr;</span> <span class="discount-cansel tool-tip-bottom" title="Отменить">x</span></div></div>';
          }
          if ('admin'==$type) {
            $print.= '<li'.$slider.'<a href="javascript:void(0);" onclick="return false;" class="CategoryTree" rel="CategoryTree" id="'.$category['id'].'" parent_id="'.$category["parent"].'">'.$category['title'].'</a>
              '.$rate;
          } else {
            if ($category['invisible']!=1) {             
              $active = '';     
              if(URL::isSection($category['parent_url'].$category['url'])) {
                $active = 'class="active"';              
              }
              $category['title'] = MG::contextEditor('category', $category['title'], $category["id"],"category");              
              $print.= '<li'.$slider.'<a href="'.SITE.'/'.$category['parent_url'].$category['url'].'"><span '.$active.'>'.$category['title'].'</span></a>';
            }
          }

          if ($flag) {
            $display = "display:none";
            if(in_array( $category['id'],$this->openedCategory)) {
              $display = "display:block";
            }
            
      
            
            // если нужно выводить подкатегории то делаем рекурсию
            if ((($category['right_key'] - $category['left_key']) > 1) && $recursion) {  
              $sub_menu = '
              <ul class="sub_menu" style="'.$display.'">
                [li]
              </ul>';   
              $li = $this->getCategoryListUl($category['id'], $type, $recursion, false, $categories);         
              $print .= strlen($li)>0 ? str_replace('[li]', $li, $sub_menu) : "";
            }
           $print .= '</li>'; 
        
          } else {            
            $print .= '</li>';
          }
        }
      }
    }

    $args = func_get_args();
    $result = $print;
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Возвращает массив вложенных категорий первого уровня.
   * <code>
   *  $parentId = 5; // id родительской категории
   *  $res = MG::get('category')->getChildCategoryIds($parentId);
   *  viewData($res);
   * </code>
   * @param int $parent id родительской категории.
   * @return string.
   */
  public function getChildCategoryIds($parentId = 0) {
    $result = array();

    $res = DB::query('
      SELECT id
      FROM `'.PREFIX.'category`
      WHERE parent = %d
      ORDER BY id
    ', $parentId);

    while ($row = DB::fetchArray($res)) {
      $result[] = $row['id'];
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Возвращает список только id всех вложеных категорий.
   * <code>
   *  $parentId = 5; // id родительской категории
   *  $res = MG::get('category')->getCategoryList($parentId);
   *  viewData($res);
   * </code>
   * @param int $parent id родительской категории
   * @return array
   */
  public function getCategoryList($parent = 0, $sql = true, $categories = array()) {
    if(!MG::isAdmin() && $parent == 0) $result = Storage::get('getCategoryList');
    if(!$result) {
      if($sql) $categories = self::getCategoryFromBdNestedH($parent, true);
      $this->listCategoryId[] = array();
      
      if (!empty($categories))
        foreach ($categories[$parent] as $category) {
        
          if(!isset($category['id'])) {break;}//если категории неceotcndetn
          
          if ($parent==$category['parent']) {
            $this->listCategoryId[] = $category['id'];      
            if(($category['right_key'] - $category['left_key']) > 1) {    
              $this->getCategoryList($category['id'], false, $categories);
            }
          }
        }
      $args = func_get_args();
      if (!empty($this->listCategoryId)) {
        $this->listCategoryId = array_flip(array_flip($this->listCategoryId)); //удаление дублей
      }
      $result = $this->listCategoryId;
      if(!MG::isAdmin() && $parent == 0) Storage::save('getCategoryList', $result);
    }
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Возвращает массив id категории и ее заголовок.
   * <code>
   *  $res = MG::get('category')->getCategoryTitleList();
   *  viewData($res);
   * </code>
   * @return array
   */
  public function getCategoryTitleList() {
    $titleList[0] = 'Корень каталога';
    if (!empty($this->categories))
      foreach ($this->categories as $category) {
        $titleList[$category['id']] = $category['title'];
      }

    $args = func_get_args();
    $result = $titleList;
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  public function getCategoryFromBdNestedH($id = 0, $lite = false) {
    if(!URL::isSection('mg-admin') && $id == 0) $data = Storage::get('getCategoryFromBdNestedH-'.LANG);
    if(!$data) {
      // достаем ключи для выборки
      if($id == 0) {
        $leftKey = 1;
        $rightKey = 999999999;
      } else {
        $res = DB::query('SELECT left_key, right_key FROM '.PREFIX.'category WHERE id = '.DB::quoteInt($id));
        while($row = DB::fetchAssoc($res)) {
          $leftKey = $row['left_key'];
          $rightKey = $row['right_key'];
        }
      }
      // достаем категории для построения
      $res = DB::query('SELECT * FROM '.PREFIX.'category WHERE left_key > '.DB::quoteInt($leftKey).' AND right_key < '.DB::quoteInt($rightKey).' ORDER BY `sort` ASC');
      while($row = DB::fetchAssoc($res)) {
        $link = SITE.'/'.$row['parent_url'].$row['url'];              
        $row['link'] = $link;

        MG::loadLocaleData($row['id'], LANG, 'category', $row);

        $data[$row['parent']][$row['id']] = $row;     
        $data[$row['parent']][$row['id']]['userProperty'] = array();
        $data[$row['parent']][$row['id']]['propertyIds'] = array();
      }

      if(!URL::isSection('mg-admin') && $id == 0) Storage::save('getCategoryFromBdNestedH-'.LANG, $data);
    }
    return $data;
  }
  
  /**
   * Возвращает иерархический массив категорий.
   * <code>
   *  $res = MG::get('category')->getHierarchyCategory();
   *  viewData($res);
   * </code>
   * @param int $parent id родительской категории.
   * @param bool $onlyActive возвращать только активные категории.
   * @return array
   */
  public function getHierarchyCategory($parent = 0, $onlyActive = false, $sql = true, $categories = array()) {
    if(!MG::isAdmin() && !$onlyActive && $parent == 0) $result = Storage::get('getHierarchyCategory'.'-'.LANG);
    if(empty($result)) {
      if($sql) $categories = self::getCategoryFromBdNestedH($parent);
      $catArray = array();
      // viewdata($categories);  
      // viewdata(count($categories));
      if (!empty($categories)) {

        foreach ($categories[$parent] as $category) {     
          unset($child);
          if(!isset($category['id'])) {break;}//если категории неceotcndetn
            if ($onlyActive && $category['invisible']==="1") {
               continue;
            }
           
            if ($parent == $category['parent']) {
              // проверка на то, есть ли дочерние элементы
              if(($category['right_key'] - $category['left_key']) > 1) {
                $child = $this->getHierarchyCategory($category['id'], $onlyActive, false, $categories);
              }

              if (!empty($child)) {
                $array = $category;
                if (!array_key_exists('insideProduct', $array)) {
                  $array['insideProduct'] = '';
                }
                usort($child, array(__CLASS__, "sort"));        
                $array['child'] = $child; 
                
                $data = 0;
                if(MG::getSetting('catalogPreCalcProduct') == 'old') {
                  foreach($child as $item) {
                    $data += $item['countProduct'];
                  }
                  $array['insideProduct'] = $this->categories[$category['id']]['countProduct'] + $data;
                } else {
                  $array['insideProduct'] += $this->categories[$category['id']]['countProduct'];
                }            
              } else {
                $array = $category;
                if(empty($array['insideProduct'])) $array['insideProduct'] = 0;
                $array['insideProduct'] += $this->categories[$category['id']]['countProduct'];
              }
              $array['countProduct'] = $array['insideProduct'];
              $catArray[] = $array;
            }
          
        }
      }
      $result = $catArray;
      if(!MG::isAdmin() && !$onlyActive && $parent == 0) Storage::save('getHierarchyCategory'.'-'.LANG, $result);
    }
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }


  
  /**
   * Возвращает отдельные пункты списка заголовков категорий.
   * <code>
   *  $arrayCategories = MG::get('category')->getArrayCategory();
   *  $res = MG::get('category')->getTitleCategory($arrayCategories);
   *  viewData($res);
   * </code>
   * @param array $arrayCategories массив с категориями.
   * @param int $selectCategory id выбранной категории.
   * @param bool $modeArray - если установлен этот флаг, то  результат вернет массив а не HTML список
   * @param string $prefix префикс для подкатегорий
   * @return string
   */
  public function getTitleCategory($arrayCategories, $selectCategory = 0, $modeArray = false, $prefix = '  --  ') {
    // MG::LOGER($arrayCategories);
    if($modeArray) {
      global $catArr;
    }
    global $lvl;
    $option = '';
    $level = 0;
    foreach ($arrayCategories as $category) {
      $select = '';
      if ($selectCategory==$category['id']) {
        $select = 'selected = "selected"';
      }
      $option .= '<option data-parent='.$category['parent'].' value='.$category['id'].' '.$select.' >';
      $option .= str_repeat($prefix, $lvl);
      $option .= $category['title'];
      $option .= '</option>';
      $catArr[$category['id']] = str_repeat($prefix, $lvl).$category['title'];
      if (isset($category['child'])) {
        $lvl++;       
        $option .= $this->getTitleCategory($category['child'],$selectCategory,$modeArray,$prefix);
        $lvl--;
      }
    }
    $args = func_get_args();
    
    $result = $option;  
    if($modeArray) {
      $result = $catArr;      
    }
  
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Получает параметры категори по его URL.
   * <code>
   *  $url = 'chasy-sekundomery-shagomery';
   *  $parentUrl = 'aksessuary';
   *  $res = MG::get('category')->getCategoryByUrl($url, $parentUrl);
   *  viewData($res);
   * </code>
   * @param string $url запрашиваемой категории.
   * @param string $parentUrl родительской категории.
   * @return array массив с данными о категории.
   */
  public function getCategoryByUrl($url, $parentUrl="") {
    $result = array();

    $res = DB::query('
      SELECT *
      FROM `'.PREFIX.'category`
      WHERE url = '.DB::quote($url).' AND parent_url = '.DB::quote($parentUrl).'
    ');

    if (!empty($res)) {
      if ($cat = DB::fetchAssoc($res)) {
        $result = $cat;
      }
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Получает параметры категори по его Id.
   * <code>
   *  $res = MG::get('category')->getCategoryById(12);
   *  viewData($res);
   * </code>
   * @param string $id запрашиваемой  категории.
   * @return array массив с данными о категории.
   */
  public function getCategoryById($id) {
    $result = array();
    $res = DB::query('
      SELECT *
      FROM `'.PREFIX.'category`
      WHERE id = '.DB::quote($id));

    if (!empty($res)) {
      if ($cat = DB::fetchArray($res)) {
        $result = $cat;
      }
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Возвращает массив пользовательских характеристик для заданной категории.
   * <code>
   *  $res = MG::get('category')->getUserPropertyCategoryById(12);
   *  viewData($res);
   * </code>
   * @param string $id запрашиваемой  категории.
   * @return array
   */
  public function getUserPropertyCategoryById($id) {
    return $this->categories[$id]['userProperty'];
  }
  
   /**
   * Возвращает массив id всех характеристик для заданной категории.
   * <code>
   *  $res = MG::get('category')->getPropertyForCategoryById(12);
   *  viewData($res);
   * </code>
   * @param string $id запрашиваемой категории.
   * @return array 
   */  
  public function getPropertyForCategoryById($id) {
    return $this->categories[$id]['propertyIds'];
  }
  
   /**
   * Возвращает массив всех категорий каталога.
   * <code>
   *  $res = MG::get('category')->getArrayCategory();
   *  viewData($res);
   * </code>
   * @return array
   */
  public function getArrayCategory() {
    return $this->categories;
  }

  /**
   * Получает описание категории.
   * <code>
   *  $res = MG::get('category')->getDesctiption(12);
   *  viewData($res);
   * </code>
   * @param int $id id категории
   * @return array
   */
  public function getDesctiption($id) {
    $result = null;
    $res = DB::query('
      SELECT `html_content`, `seo_content`
      FROM `'.PREFIX.'category`
      WHERE id = "%d"
    ', intval($id));

    if (!empty($res)) {
      if ($cat = DB::fetchArray($res)) {
        MG::loadLocaleData($id, LANG, 'category', $cat);
        $result = array('html_content' => $cat['html_content'],
          'seo_content' => $cat['seo_content']);
      }
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Получает изображение категории.
   * <code>
   *  $res = MG::get('category')->getImageCategory(12);
   *  viewData($res);
   * </code>
   * @param int $id id категории
   * @return string
   */
  public function getImageCategory($id) {   
    return $this->categories[$id]['image_url'];
  }
  
  /** 
   *  Упорядочивает категорию по сортировке.
   *  @param array $a первая категория
   *  @param array $b вторая категория
   *  @return int
   */
  public function sort($a, $b) {
    return $a['sort'] - $b['sort'];
  }
  
  
  /** 
   * Меняет местами параметры сортировки двух категории.
   * @param int $oneId - id первой категории.
   * @param int $twoId - id второй категории.
   * @return bool
   */
  public function changeSortCat($oneId, $twoId) {
    $cat1 = $this->getCategoryById($oneId); 
    $cat2 = $this->getCategoryById($twoId); 
    if(!empty($cat1)&&!empty($cat2)) {
      
     $res = DB::query('
       UPDATE `'.PREFIX.'category` 
       SET  `sort` = '.DB::quote($cat1['sort']).'  
       WHERE  `id` ='.DB::quote($cat2['id']).'
     ');
     
     $res = DB::query('
       UPDATE `'.PREFIX.'category` 
       SET  `sort` = '.DB::quote($cat2['sort']).'  
       WHERE  `id` ='.DB::quote($cat1['id']).'
     ');  
     //очищаем кэш категорий
      Storage::clear('category');
      return true;
    }
    return false;
  }
  
  
  
  /**
   * Отменяет скидки и наценки для выбранной категории.
   * <code>
   *  $res = MG::get('category')->clearCategoryRate(12);
   *  viewData($res);
   * </code>
   * @param int $id id категории
   * @return bool Делает все категории видимыми в меню.
   */
  public function  clearCategoryRate($id) {
     $res = DB::query('
       UPDATE `'.PREFIX.'category` 
       SET `rate` = 0  
       WHERE `id` = '.DB::quote($id)
     ); 
     Storage::clear('category');
     return true;
  }
  
   /**
   * Применяет скидку/наценку ко всем вложенным подкатегориям.
   * <code>
   *  $res = MG::get('category')->applyRateToSubCategory(12);
   *  viewData($res);
   * </code>
   * @param id - id текущей категории
   * @return bool 
   */
  public function  applyRateToSubCategory($id) {
    $childsCaterory = $this->getCategoryList($id);    
    // Если есть вложенные
    if (!empty($childsCaterory)) {
      $caterory = $this->getCategoryById($id);
      foreach ($childsCaterory as $cateroryId) {
        $res = DB::query('
          UPDATE `'.PREFIX.'category` 
          SET  `rate` = '.$caterory['rate'].'
          WHERE `id` = '.DB::quote($cateroryId)
        ); 
      }
    }
    Storage::clear('category');
    return true;
  }
  
   /**
   * Возвращает общее количество категорий каталога.
   * <code>
   *  $res = MG::get('category')->getCategoryCount();
   *  viewData($res);
   * </code>
   * @return int
   */
  public function getCategoryCount() {
    $result = 0;
    $res = DB::query('
      SELECT count(id) as count
      FROM `'.PREFIX.'category`
    ');

    if ($product = DB::fetchAssoc($res)) {
      $result = $product['count'];
    }

    return $result;
  }
  
  /**
   * Сортировка по алфавиту.
   * <code>
   *  MG::get('category')->sortToAlphabet();
   * </code>
   */
  public function sortToAlphabet() {
   $result = DB::query('SELECT id, title FROM `'.PREFIX.'category` ORDER BY title');
   $sort = 1;
   while ($row = DB::fetchAssoc($result)) {    
     DB::query('SELECT id, title FROM `'.PREFIX.'category` ORDER BY title');     
     $res = DB::query('
        UPDATE `'.PREFIX.'category` 
        SET  `sort` = '.DB::quote($sort).'
        WHERE `id` = '.DB::quote($row['id'])
     ); 
     $sort++;
   }
   Storage::clear('category');  
  }
  
  /**
   * Сортировка по порядку добавления категорий на сайт.
   * <code>
   *  MG::get('category')->sortToAdd();
   * </code>
   */
  public function sortToAdd() { 
     $res = DB::query('
        UPDATE `'.PREFIX.'category` 
        SET  `sort` = `id`'      
     );   
     Storage::clear('category');
   }     
  
  /**
   * Выгрузка категории в CSV.
   * <code>
   *  MG::get('category')->exportToCsv();
   * </code>
   */
  public function exportToCsv() {
    $categories = $this->getCategoryList();
    
    if(@set_time_limit(100)) {
      $maxExecTime = 90;
    } else {
      $maxExecTime = min(30, @ini_get("max_execution_time"));      
    }     
        
    $startTime = microtime(true);
    $timeMargin = 5;
    $rowCount = (URL::getQueryParametr('rowCount')) ? URL::getQueryParametr('rowCount') : 0;
    $csvText = '';
    
    if($rowCount == 0) {
      $csvText = array("Название категории","URL категории","id родительской категории","URL родительской категории","Описание категории","Изображение","Заголовок [SEO]","Ключевые слова [SEO]","Описание [SEO]","SEO Описание","Наценка","Не выводить в меню","Активность","Не выгружать в YML","Сортировка","Внешний идентификатор","ID категории","title изображенния","alt изображения");
      $this->rowCsvPrintToFile($csvText, true);
    }
    
    foreach ($categories as $cell=>$catId) {
      if ($cell < $rowCount) {
        continue;
      }
      
      $category = $this->getCategoryById($catId);

      $row = array(
        'title' => $category['title'],
        'url' => $category['url'],
        'parent' => $category['parent'],
        'parent_url' => $category['parent_url'],
        'html_content' => str_replace(array("\r\n", "\r", "\n"), "", $category['html_content']),
        'image_url' => $category['image_url'],
        'meta_title' => $category['meta_title'],
        'meta_keywords' => $category['meta_keywords'],
        'meta_desc' => $category['meta_desc'],
        'seo_content' => str_replace(array("\r\n", "\r", "\n"), "", $category['seo_content']),
        'rate' => $category['rate'],
        'invisible' => $category['invisible'],
        'activity' => $category['activity'],
        'export' => $category['export'],
        'sort' => $category['sort'],
        '1c_id' => $category['1c_id'],
        'id' => $category['id'],
        'seo_title' => $category['seo_title'],
        'seo_alt' => $category['seo_alt'], 
      );
      
      $csvLine = $this->addToCsvLine($row);
      $this->rowCsvPrintToFile($csvLine, false);
      $rowCount++;
      
      $execTime = microtime(true) - $startTime;        

      if($execTime+$timeMargin >= $maxExecTime) {                  
        $data = array(
          'success' => false,          
          'rowCount' =>$rowCount,
          'percent' => round(($rowCount / count($categories)) * 100)
        );
        echo json_encode($data);
        exit();
      }
    }
    
    $date = date('m_d_Y');
    
    $data = array(
      'success' => true,
      'file' => 'data_csv_'.$date.'.csv'
    );
    echo json_encode($data);
    exit();
  }
  
   /**
   * По входящим данным формирует новую строку CSV файла, в требуемом формате.
   * <code>
   *  $array = array(
   *    'title' => 'Смартфоны',                  // название категории
   *    'url' => 'smartfony',                    // url
   *    'parent' => 0,                           // id родительской категори
   *    'parent_url' => ,                        // родительский url
   *    'html_content' => ,                      // содеражние страницы
   *    'image_url' => '/uploads/cat_smart.png', // ссылка на изображение
   *    'meta_title' => ,                        // заголовок страницы
   *    'meta_keywords' => ,                     // ключевые слова
   *    'meta_desc' => ,                         // мета описание
   *    'seo_content' => ,                       // seo контент
   *    'rate' => 0,                             // наценка
   *    'invisible' => 0,                        // параметр видимости
   *    'activity' => 1,                         // параметр активности
   *    'export' => 1,                           // 
   *    'sort' => 1,                             // порядок сортировки
   *    '1c_id' => ,                             // идентификатор в 1с
   *    'id' => 1,                               // id
   *    'seo_title' => ,                         // seo title
   *    'seo_alt' => ,                           // seo alt
   *  );
   *  $res = MG::get('category')->addToCsvLine($array);
   *  viewData($res);
   * </code>
   * @param array $row массив со всеми данными о категории.
   * @return void
   */
  function addToCsvLine($row) {
    $row['title'] = str_replace("\"", "\"\"", htmlspecialchars_decode($row['title']));
    $row['url'] = str_replace("\"", "\"\"", htmlspecialchars_decode($row['url']));
    $row['parent_url'] = str_replace("\"", "\"\"", htmlspecialchars_decode($row['parent_url']));
    $row['html_content'] = str_replace("\"", "\"\"", $row['html_content']);
    $row['html_content'] = str_replace("\r", "", $row['html_content']);
    $row['html_content'] = str_replace("\n", "", $row['html_content']);
    $row['activity'] = str_replace("\"", "\"\"", $row['activity']);
    $row['meta_title'] = str_replace("\"", "\"\"", htmlspecialchars_decode($row['meta_title']));
    $row['meta_keywords'] = str_replace("\"", "\"\"", htmlspecialchars_decode($row['meta_keywords']));
    $row['meta_desc'] = str_replace("\"", "\"\"", htmlspecialchars_decode($row['meta_desc']));
    $row['meta_desc'] = str_replace("\r", "", $row['meta_desc']);
    $row['meta_desc'] = str_replace("\n", "", $row['meta_desc']);
    $row['sort'] = str_replace("\"", "\"\"", $row['sort']);
    $row['image_url'] = $row['image_url'];
    $row['seo_title'] = str_replace("\"", "\"\"", htmlspecialchars_decode($row['seo_title']));
    $row['seo_alt'] = str_replace("\"", "\"\"", htmlspecialchars_decode($row['seo_alt']));
    
    $csvText = array(
      $row['title'],
      $row['url'],      
      $row['parent'],
      $row['parent_url'],
      $row['html_content'],
      $row['image_url'],
      $row['meta_title'],
      $row['meta_keywords'],
      $row['meta_desc'],
      $row['seo_content'],
      $row['rate'],
      $row['invisible'],
      $row['activity'],
      $row['export'],
      $row['sort'],
      $row['1c_id'],
      $row['id'],
      $row['seo_title'],
      $row['seo_alt'],
    );

    return $csvText;
  }
  
   /**
   * Записывает построчно CSV выгрузку в файл data_csv_m_d_Y.csv в корневую папку сайта.
   * <code>
   *  $csvText = MG::get('category')->addToCsvLine($array);
   *  MG::get('category')->rowCsvPrintToFile($csvText);
   *  viewData($res);
   * </code>
   * @param string $csvText csv строка.
   * @param bool $new записывать в конец файла.
   * @return void
   */
  public function rowCsvPrintToFile($csvText, $new = false) {
    foreach ($csvText as &$item) {
      $item = mb_convert_encoding($item, "WINDOWS-1251", "UTF-8");
    }

    $date = date('m_d_Y');

    if($new) {      
      $fp = fopen('data_csv_'.$date.'.csv', 'w');
    } else {      
      $fp = fopen('data_csv_'.$date.'.csv', 'a');
    }

    fputcsv($fp, $csvText, ';');
    fclose($fp);
  }

  /**
   * Возвращает строки для таблицы с категориями в админке.
   * @param array $pagesArray массив с информацие о категориях
   * @param int $parentLevel уровень вложенности родительской страницы
   * @param int $parent id родительской характеристики
   * @return string html
   */
  public function getPages($pagesArray, $parentLevel, $parent) {
    foreach($pagesArray as $page) { 
      $pages .= self::getHtmlPageRow($pagesArray, $page['id'], $parentLevel);
    } 

    return $pages;
  }

  /**
   * возвращает строки для таблицы с категориями (упрощенный).
   * @param array $pagesArray массив с информацие о категориях
   * @param int $parentLevel уровень вложенности родительской страницы
   * @param int $parent id родительской характеристики
   * @return string html
   */
  public function getPagesSimple($pagesArray, $parentLevel, $parent) {
    foreach($pagesArray as $page) { 
      $pages .= self::getHtmlPageRowSimple($pagesArray, $page['id'], $parentLevel);
    } 

    return $pages;
  }

  /**
   * возвращает html верстку строк для таблицы с категориями (упрощенный).
   * @param array $pages массив с информацие о категориях
   * @param int $id id категории
   * @param int $level уровень вложенности
   * @return string html
   */
  public function getHtmlPageRowSimple($pages, $id, $level) {
    $categoryCount = $_SESSION['categoryCountToAdmin'];
    foreach($pages as $page) { 
      if($page['id'] == $id) {
        // группировка для сортировки
        if($level == 0) {
          $group = 'main';
        } else {
          $group = 'group-'.$page['parent'];
        }
        // отображать ли кнопку для выпадающего списка
        $res = DB::query('SELECT id FROM '.PREFIX.'category WHERE parent = '.DB::quote($page['id']).' GROUP BY sort ASC LIMIT 1');
        while($row = DB::fetchAssoc($res)) {
          $result = $row['id'];
        }
        if($result != "") {
          $circlePlus = '<a class="fa fa-plus-circle tip show_sub_menu" href="javascript:void(0);" id="toHide-'.$page['id'].'" data-id="'.$page['id'].'" aria-hidden="true" title="Показать/скрыть вложенные категории"></a> ';
        } else {
          $circlePlus = '';
        }
        // отображать ли иконку вложенности
        $levelArrow = '';
        for($i = 0; $i < $level; $i++) {
          $levelArrow .= '<i class="fa fa-long-arrow-right" aria-hidden="true"></i>';
        }

        return '
              <tr class="level-'.($level+1).' '.$group.'" '.$hide.' data-group="'.$group.'" data-level="'.($level+1).'" data-id="'.$page['id'].'" data-sort="'.$categoryCount.'">
        
                <td class="name">'.$circlePlus.$levelArrow.'<span class="product-name"><a class="name-link tip edit-sub-cat" href="javascript:void(0);">'.$page['title'].'</a><a class="fa fa-external-link tip" href="'.SITE.'/'.$page['parent_url'].$page['url'].'" aria-hidden="true" title="Открыть категорию на сайте" target="_blank"></a></span></td>

                <td class="uploadCat">
                  <div class="sticker-menu discount-rate-down badge alert">
                    <span class="upload-cat-text" title="Изменить привязку к категории выгрузки" upload-cat-name="0" data-cat-id="'.$page['id'].'" upload-cat-name="">Привязать категорию</span>
                    <div class="discount-mini-control">
                      <span class="cat-apply-follow tool-tip-bottom"  title="Применить ко всем вложенным категориям">&darr;&darr;</span> 
                      <span class="cat-cansel tool-tip-bottom" title="Убрать привязку к категории выгрузки">x</span>
                    </div>
                  </div>
                </td>
              </tr>
              ';
      }
    }
  }

  /**
   * возвращает html верстку строк для таблицы с категориями.
   * @param array $pages массив с информацие о категориях
   * @param int $id id категории
   * @param int $level уровень вложенности
   * @return string html
   */
  public function getHtmlPageRow($pages, $id, $level) {
    $lang = MG::get('lang');
    $categoryCount = $_SESSION['categoryCountToAdmin'];

    foreach($pages as $page) { 
      if($page['id'] == $id) {
        // группировка для сортировки
        if($level == 0) {
          $group = 'main';
        } else {
          $group = 'group-'.$page['parent'];
        }
        // отображать ли кнопку для выпадающего списка
        $res = DB::query('SELECT id FROM '.PREFIX.'category WHERE parent = '.DB::quote($page['id']).' ORDER BY sort ASC LIMIT 1');
        while($row = DB::fetchAssoc($res)) {
          $result = $row['id'];
        }
        if($result != "") {
          $circlePlus = '<a class="fa fa-plus-circle tip show_sub_menu" href="javascript:void(0);" id="toHide-'.$page['id'].'" data-id="'.$page['id'].'" aria-hidden="true" title="Показать/скрыть вложенные категории"></a> ';
        } else {
          $circlePlus = '';
        }
        // отображать ли иконку вложенности
        $levelArrow = '';
        for($i = 0; $i < $level; $i++) {
          $levelArrow .= '<i class="fa fa-long-arrow-right" aria-hidden="true"></i>';
        }
        // отмечен ли чекбокс показа
        if($page['activity'] == '1') {
          $checkbox = 'active';
        } else {
          $checkbox = '';
        } 
        // отмечен ли чекбокс показа
        if($page['invisible'] == '0') {
          $invisible = 'active';
        } else {
          $invisible = '';
        } 
        // отмечен ли чекбокс export
        if($page['export'] == '1') {
          $export = 'active';
        } else {
          $export = '';
        } 

        if ($page['rate']>0) {
          $rate = '<div class="sticker-menu discount-rate-up badge success" data-cat-id="'.$page['id'].'"> '.$lang['DISCOUNT_UP'].' +'.($page['rate']*100).'% <div class="discount-mini-control"><span class="discount-apply-follow tool-tip-bottom"  title="Применить ко всем вложенным категориям" >&darr;&darr;</span> <span class="discount-cansel tool-tip-bottom" title="Отменить">x</span></div></div>';
        }
        if ($page['rate']<0) {
          $rate = '<div class="sticker-menu discount-rate-down badge alert" data-cat-id="'.$page['id'].'"> '.$lang['DISCOUNT_DOWN'].' '.($page['rate']*100).'% <div class="discount-mini-control"><span class="discount-apply-follow tool-tip-bottom"  title="Применить ко всем вложенным категориям">&darr;&darr;</span> <span class="discount-cansel tool-tip-bottom" title="Отменить">x</span></div></div>';
        }

        if(USER::access('category') > 1) {
          $actions = '<li><a class="fa fa-plus-circle tip add-sub-cat" href="javascript:void(0);" aria-hidden="true" title="Добавить вложенную категорию"></a></li>
                    <li><a class="fa fa-lightbulb-o tip '.$checkbox.' activity" href="javascript:void(0);" aria-hidden="true" title="'.($checkbox == "" ? $lang["ACT_UNV_CAT_ACT"] : $lang["ACT_V_CAT_ACT"]).'"></a></li>
                    <li><a class="fa fa-list tip visible '.$invisible.'" href="javascript:void(0);" aria-hidden="true" title="'.($invisible == "" ? $lang["ACT_UNV_CAT"] : $lang["ACT_V_CAT"]).'"></a></li>
                    <li><a class="fa fa-shopping-cart tip export '.$export.'" href="javascript:void(0);" aria-hidden="true" title="Включить в выгрузку на Яндекс.Маркет"></a></li>';
          $actions2 = '<li><a class="fa fa-trash tip delete-sub-cat" href="javascript:void(0);" aria-hidden="true" title="Удалить"></a></li>';
        } else {
          $actions = '';
          $actions2 = '';
        }

        return '
              <tr class="level-'.($level+1).' '.$group.'" '.$hide.' data-group="'.$group.'" data-level="'.($level+1).'" data-id="'.$page['id'].'" data-sort="'.$categoryCount.'">
                <td class="checkbox">
                  <div class="checkbox">
                    <input type="checkbox" id="c'.$page['id'].'" name="category-check">
                    <label class="select-row" for="c'.$page['id'].'"></label>
                  </div>
                </td>
                <td class="sort"><a class="fa fa-arrows tip mover" href="javascript:void(0);" aria-hidden="true" title="Сортировать"></a></td>
                <td class="number">'.$page['id'].'</td>
                <td class="name">'.$circlePlus.$levelArrow.'<span class="product-name"><a class="name-link tip edit-sub-cat" href="javascript:void(0);" title="Редактировать категорию">'.$page['title'].'</a><a class="fa fa-external-link tip" href="'.SITE.'/'.$page['parent_url'].$page['url'].'" aria-hidden="true" title="Открыть категорию на сайте" target="_blank"></a></span></td>
                <td>'.$rate.'</td>
                <td><a class="tip" href="'.SITE.'/'.$page['parent_url'].$page['url'].'" target="blank" title="Перейти в категорию">/'.$page['parent_url'].$page['url'].'</a></td>
                <td class="text-right actions">
                  <ul class="action-list">
                    <li><a class="fa fa-pencil tip edit-sub-cat" href="javascript:void(0);" tabindex="0" title="Редактировать"></a></li>
                    '.$actions.'
                    <li><a class="fa fa-search tip prod-sub-cat" href="javascript:void(0);" aria-hidden="true" title="Просмотреть товары категории"></a></li>
                    '.$actions2.'
                  </ul>
                </td>
              </tr>
              ';
      }
    }
  }
}