<?php

/*
  Plugin Name: Логотипы брендов/производителей
  Description: При активации плагина создается новая характеристика Бренд, куда можно экспортировать уже существующие значения характеристики бренд или производитель. Добавьте шорт-код [brand] для вывода логотипов. При нажатии на логотип загружаются товары данного бренда. Можно копировать значения из других характеристик, они будут добавлены в характеристику "Бренд" и значения будут присвоены в карточке соответсвующих товаров.
  Author: Daria Churkina
  Version: 1.1.7
 */

new brand;

class brand {

  private static $options;
  private static $lang = array(); // массив с переводом плагина 
  private static $pluginName = ''; // название плагина (соответствует названию папки)
  private static $path = ''; //путь до файлов плагина 

  public function __construct() {

    mgActivateThisPlugin(__FILE__, array(__CLASS__, 'activate')); //Инициализация  метода выполняющегося при активации  
    mgAddAction(__FILE__, array(__CLASS__, 'pageSettingsPlugin')); //Инициализация  метода выполняющегося при нажатии на кнопку настроект плагина  
    mgAddShortcode('brand', array(__CLASS__, 'handleShortCode')); // Инициализация шорткода [brand] - доступен в любом HTML коде движка.    

    self::$pluginName = PM::getFolderPlugin(__FILE__);
    self::$lang = PM::plugLocales(self::$pluginName);
    self::$path = PLUGIN_DIR.self::$pluginName;
    $option = MG::getSetting('brand');
    $option = stripslashes($option);
    self::$options = unserialize($option);
    if (!URL::isSection('mg-admin')) { // подключаем CSS плагина для всех страниц, кроме админки
      mgAddMeta('<link rel="stylesheet" href="'.SITE.'/'.str_replace(DIRECTORY_SEPARATOR, '/', self::$path).'/css/style.css" type="text/css" />');
      //  mgAddMeta('<script src="'.SITE.'/'.self::$path.'/js/brand.js"></script>');
    } else {
      mgAddMeta('<script src="'.SITE.'/'.str_replace(DIRECTORY_SEPARATOR, '/', self::$path).'/js/script.js"></script>');
    }    
    $newfile = 'brand.php';
    if (!file_exists(PAGE_DIR.$newfile)) {
      $file = PLUGIN_DIR.self::$pluginName.'/brandviews.php';
      copy($file, PAGE_DIR.$newfile);
    }
    
  }

  /**
   * Метод выполняющийся при активации палагина 
   */
  static function activate() {
    self::createDateBase();
  }

  /**
   * Создает таблицу плагина в БД
   */
  static function createDateBase() {
    // Запрос для проверки, был ли плагин установлен ранее.
    $exist = false;
    $brandExist = false;
    $result = DB::query('SHOW TABLES LIKE "'.PREFIX.self::$pluginName.'-logo"');
    if (DB::numRows($result)) {
      $exist = true;
    }

    DB::query("
     CREATE TABLE IF NOT EXISTS `".PREFIX.self::$pluginName."-logo` (     
      `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT COMMENT 'Порядковый номер записи',     
      `brand` text NOT NULL COMMENT 'Бренд',
      `url` text NOT NULL COMMENT 'Логотип',    
      `desc` text NOT NULL COMMENT 'Описание',    
      `sort` int(11) NOT NULL COMMENT 'Порядок',
      `seo_title` text NOT NULL,
      `seo_keywords` text NOT NULL,
      `seo_desc` text NOT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

    $res = DB::query('SELECT name FROM '.PREFIX.'property WHERE name = \'Бренд\'');
    if(!DB::fetchAssoc($res)) {
      DB::query('INSERT INTO '.PREFIX.'property (type, name) VALUES (\'assortmentCheckBox\', \'Бренд\')');
      $array = Array(
        'propertyId' => DB::insertId(),
        'first' => 'false',
      );
      MG::setOption(array('option' => 'brand', 'value' => addslashes(serialize($array))));
    }
  }

  /**
   * Выводит страницу настроек плагина в админке
   */
  static function pageSettingsPlugin() {
    $id_prop = self::$options['propertyId'];
    // self::compareProp($id_prop);    
    $lang = self::$lang;
    $pluginName = self::$pluginName;
    $countPrintRows = MG::getSetting('countPrintRowsBrand');
    $options = self::$options;
    $result = array();
    $sql = "SELECT * FROM `".PREFIX.self::$pluginName."-logo` ORDER BY `sort`";
    $page = 1;
    if ($_POST["page"]) {
      $page = $_POST["page"]; //если был произведен запрос другой страницы, то присваиваем переменной новый индекс
    }
    $navigator = new Navigator($sql, $page, $countPrintRows); //определяем класс
    $brand = $navigator->getRowsSql();
    $pagination = $navigator->getPager('forAjax');
    $res = DB::query("SELECT * FROM `".PREFIX.self::$pluginName."-logo` WHERE `url`=''");
    $empty = DB::numRows($res);    
    $res = DB::query('SELECT id, name FROM '.PREFIX.'property');
    while($row = DB::fetchAssoc($res)) {
      $propNames[] = $row;
    }
    self::preparePageSettings();
    include('pageplugin.php');
  }

  /**
   * Метод выполняющийся перед генерацией страницы настроек плагина
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
   * выводит логотипы брендов по шорткоду [brand]
   */
  static function handleShortCode() {       
      $options = self::$options;
      $brand = array();
      $res = DB::query('SELECT `url`, `brand` FROM `'.PREFIX.self::$pluginName.'-logo` order by `sort`');
      if ($res) {
        while ($row = DB::fetchArray($res)) {
          $brand[] = $row;
        }      
      ob_start();
      include ('layout.php');
      $html = ob_get_contents();
      ob_clean();
      return $html;
      }
  }

  /**
   * Возвращает массив продуктов  по запрошенному бренду и информацию о бренде
   * @param $brand - название тега
   */
  static function getProductsByBrand($brand) {
    if (empty(self::$options['propertyId'])) {
      $option = MG::getSetting('brand');
      $option = stripslashes($option);
      self::$options = unserialize($option);
    }
    // Показать первую страницу выбранного раздела.
    $page = 1;
    // Запрашиваемая страница.
    if (isset($_REQUEST['page'])) {
      $page = $_REQUEST['page'];
    }

    $catalog = new Models_Catalog;
    $currencyRate = MG::getSetting('currencyRate');      
    $currencyShopIso = MG::getSetting('currencyShopIso'); 
    
    if (!empty($brand)) {
    $sql = '
      SELECT distinct p.id,
        CONCAT(c.parent_url,c.url) as category_url,
        p.url as product_url,
        p.*,pv.product_id as variant_exist,
        rate,(p.price_course + p.price_course * (IFNULL(rate,0))) as `price_course`,
        p.currency_iso
      FROM `'.PREFIX.'product` AS p
      LEFT JOIN `'.PREFIX.'category` AS c
        ON c.id = p.cat_id
      LEFT JOIN `'.PREFIX.'product_variant` AS pv
        ON p.id = pv.product_id
      LEFT JOIN  `'.PREFIX.'product_user_property_data` AS up ON up.`product_id` = p.id
      LEFT JOIN '.PREFIX.'property_data AS pd ON up.prop_data_id = pd.id
      WHERE pd.`name`='.DB::quote($brand).' AND p.activity = 1 AND up.active = 1';

    $navigator = new Navigator($sql, $page, MG::getSetting('countСatalogProduct')); //определяем класс.
    $products = $navigator->getRowsSql();
    $pager = $navigator->getPager();
    // добавим к полученым товарам их свойства    
    $products = $catalog->addPropertyToProduct($products);   
    $product = new Models_Product;    
    if(!empty($products)){
      foreach ($products as $item) {
        $productIds[] = $item['id'];
      }
      $blocksVariants = $product->getBlocksVariantsToCatalog($productIds);  
      foreach ($products as $k => $item) {
        for($i = 0; $i < count($item['variants']); $i++) {
          if($item['variants'][$i]['count'] == 0) {
            $item['variants'][] = $item['variants'][$i];
            unset($item['variants'][$i]);
          }
        }

        $imagesUrl = explode("|", $item['image_url']);
        $products[$k]["image_url"] = "";
        if (!empty($imagesUrl[0])) {
          $products[$k]["image_url"] = $imagesUrl[0];
        }
        
        $item['currency_iso'] = $item['currency_iso']?$item['currency_iso']:$currencyShopIso;
        //$item['price'] *= $currencyRate[$item['currency_iso']];   
        
        $item['old_price'] = $item['old_price']* $currencyRate[$item['currency_iso']];
        $item['old_price'] = $item['old_price']? MG::priceCourse($item['old_price']):0;
        $item['price'] =  MG::priceCourse($item['price_course']); 
          
        $products[$k]['title'] = MG::modalEditor('catalog', $item['title'], 'edit', $item["id"]);

        if (($products[$k]['count'] == 0&&empty($products[$k]['variants']))||
          (!empty($products[$k]['variants'])&&$products[$k]['variants'][0]['count'] == 0)||
          (MG::getSetting('actionInCatalog')=='false')) {
          $buyButton = MG::layoutManager('layout_btn_more', $products[$k]);
        } else {
          $buyButton = MG::layoutManager('layout_btn_buy', $products[$k]);
        }

        // Формируем варианты товара.
        // if ($item['variant_exist']) {

          // Легкая форма без характеристик.
          $liteFormData = $product->createPropertyForm($param = array(
            'id' => $item['id'],
            'maxCount' => $item['count'],
            'productUserFields' => null,
            'action' => "/catalog",
            'method' => "POST",
            'ajax' => true,
            'blockedProp' => $blockedProp,
            'noneAmount' => true,
            'titleBtn' => MG::getSetting('buttonBuyName'),
            'buyButton' => $buyButton,//($products[$k]['count']==0 ||MG::getSetting('actionInCatalog')=='false')?$products[$k]['actionView']:'',
            'blockVariants' => $blocksVariants[$item['id']]
          ));
          $products[$k]['liteFormData'] = $liteFormData['html'];
         // }
         // опледеляем для каждого продукта  тип выводимой формы: упрощенная, с кнопками или без.        
          if (!$products[$k]['liteFormData']){
            if($products[$k]['count']==0||MG::getSetting('actionInCatalog')=='false'){
              $buyButton = $products[$k]['actionView'];          
            }else{
              $buyButton = $products[$k]['actionButton']; 
            }
          } else{
            $buyButton = $products[$k]['liteFormData'];
          }
           $products[$k]['buyButton'] = $buyButton;

          }
      }

      foreach ($products as $key => $product) {
        if (!empty($product['variants'])) {
          $products[$key]["price"] = MG::numberFormat($product['variants'][0]["price_course"]);
          $products[$key]["old_price"] = $product['variants'][0]["old_price"];
          $products[$key]["count"] = $product['variants'][0]["count"];
          $products[$key]["code"] = $product['variants'][0]["code"];
          $products[$key]["weight"] = $product['variants'][0]["weight"];
          $products[$key]["price_course"] = $product['variants'][0]["price_course"];
          $products[$key]["variant_exist"] = $product['variants'][0]["id"];
        }
      }

      // viewdata($products);
    
    $brandInfo = array();
    $res = DB::query('SELECT * FROM `'.PREFIX.'brand-logo` WHERE `brand`='.DB::quote($brand));

    if ($row = DB::fetchArray($res)) {
      $brandInfo = $row;
    }
    $result = array(
      'items' => $products,
      'brand' => $brandInfo,
      'pager' => $pager);
    }
    return $result;
  }

  static function compareProp($id) {
    $prop = DB::query('SELECT `data` FROM `'.PREFIX.'property` WHERE `id`='.DB::quote($id));
    if ($res = DB::fetchArray($prop)) {
      $value = $res['data'] ? $res['data'] : '';
      $data = explode('|', $res['data']);
      $res = DB::query("SELECT `id`, `brand` FROM `".PREFIX.self::$pluginName."-logo` ");
      $brand = array();
      while ($row = DB::fetchArray($res)) {
        $brand[$row['id']] = $row['brand'];
      }
      $diff = array_diff($data, $brand);
      if (!empty($diff)) {
        foreach ($diff as $newBrand) {
          if ($newBrand != '') {
            $res = DB::query("INSERT INTO `".PREFIX.self::$pluginName."-logo` (`brand`) VALUES (".DB::quote($newBrand).")");
            $brandId = DB::insertId();
            DB::query("UPDATE `".PREFIX.self::$pluginName."-logo` SET `sort`=".DB::quote($brandId)." WHERE `id`= ".DB::quote($brandId));
          }
        }
      }
    } 
  }

}
