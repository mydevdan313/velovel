<?php

/**
 * Модель: Product
 *
 * Класс Models_Product реализует логику взаимодействия с товарами магазина.
 * - Добавляет товар в базу данных;
 * - Изменяет данные о товаре;
 * - Удаляет товар из базы данных;
 * - Получает информацию о запрашиваемом товаре;
 * - Получает продукт по его URL;
 * - Получает цену запрашиваемого товара по его id.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Model
 * 
 */
class Models_Product {

  public $storage = 'all';

  /**
   * Добавляет товар в базу данных. 
   * <code>
   * $array = array(
   *  'title' => 'title', // название товара
   *  'url' => 'link', // последняя часть ссылки на товар
   *  'code' => 'CN230', // артикул товара
   *  'price' => 100, // цена товара
   *  'old_price' => 200, // старая цена товара
   *  'image_url' => 1434653074061713.jpg, // последняя часть ссылки на изображение товара
   *  'image_title' => '', // title изображения товара
   *  'image_alt' => '', // alt изображения товара
   *  'count' => 77, // остаток товара
   *  'weight' => 5, // вес товара
   *  'cat_id' => 4, // ID основной категории товара
   *  'inside_cat' => '1,2', // дополнительные категории товаров
   *  'description' => 'descr', // описание товара
   *  'short_description' => 'short descr', // краткое описание товара
   *  'meta_title' => 'title', // seo название товара
   *  'meta_keywords' => 'title купить, CN230, title', // seo ключевые слова
   *  'meta_desc' => 'meta descr', // seo описание товара
   *  'currency_iso' => 'RUR', // код валюты товара
   *  'recommend' => 0, // выводить товар в блоке рекомендуемых
   *  'activity' => 1, // выводить товар
   *  'unit' => 'шт.', // единица измерения товара (если null, то используется единица измерения основной категории товара)
   *  'new' => 0, // выводить товар в блоке новинок
   *  'userProperty' => Array, // массив с характеристиками товара
   *  'related' => 'В-500-1', // артикулы связанных товаров
   *  'variants' => Array, // массив с вариантами товаров
   *  'related_cat' => null, // ID связанных категорий
   *  'lang' => 'default', // язык для сохранения
   *  'landingTemplate' => 'noLandingTemplate', // шаблон для лэндинга товара
   *  'ytp' => '', // строка с торговым предложением для лэндинга
   *  'landingImage' => 'no-img.jpg', // изображение для лэндинга
   *  'storage' => 'all' // склад товара
   * );
   * $model = new Models_Product();
   * $id = $model->addProduct($product);
   * echo $id;
   * </code>
   * @param array $array массив с данными о товаре.
   * @param bool $clone происходит ли клонирование или обычное добавление товара
   * @return int|bool в случае успеха возвращает id добавленного товара.
   */
  public function addProduct($array, $clone = false) {
    if(empty($array['title'])) {
      return false;
    }

    $userProperty = $array['userProperty'];
    $variants = !empty($array['variants']) ? $array['variants'] : array(); // варианты товара
    unset($array['userProperty']);
    unset($array['variants']);
    unset($array['count_sort']);
    unset($array['lang']);
    if(empty($array['id'])) {
      unset($array['id']);
    }

    if($array['code'] == '') {
      $res = DB::query('SELECT max(id) FROM '.PREFIX.'product');
      $id = DB::fetchAssoc($res);
      $array['code'] = MG::getSetting('prefixCode').($id['max(id)']+1);
    }

    $result = array();

    $array['url'] = empty($array['url']) ? MG::translitIt($array['title']) : $array['url'];

    $maskField = array('title','meta_title','meta_keywords','meta_desc','image_title','image_alt');

    foreach ($array as $k => $v) {
      if(in_array($k, $maskField)) {
        $v = htmlspecialchars_decode($v);
        $array[$k] = htmlspecialchars($v);       
      }
    }

    if (!empty($array['url'])) {
      $array['url'] = URL::prepareUrl($array['url']);
    }

    // Исключает дублирование.
    $dublicatUrl = false;
    $tempArray = $this->getProductByUrl($array['url']);
    if (!empty($tempArray)) {
      $dublicatUrl = true;
    }

    if($array['weight']) {
     $array['weight'] = (double)str_replace(array(',',' '), array('.',''), $array['weight']);
    }else {
      $array['weight'] = 0;
    }

    if($array['price']) {
      $array['price'] = (double)str_replace(array(',',' '), array('.',''), $array['price']);
    }

    $array['sort'] = 0;
    $array['system_set'] = 1;


    unset($array['landingTemplate']);
    unset($array['landingColor']);
    unset($array['ytp']);
    unset($array['landingImage']);
    unset($array['landingSwitch']);

    unset($array['storage']);

    unset($array['color']);
    unset($array['size']);

    if(!$array['currency_iso']) $array['currency_iso'] = MG::getSetting('currencyShopIso');

    if (DB::buildQuery('INSERT INTO `'.PREFIX.'product` SET ', $array)) {
      $id = DB::insertId();

      // Если url дублируется, то дописываем к нему id продукта.
      if ($dublicatUrl) {
        $url_explode = explode('_', $array['url']);
        if (count($url_explode) > 1) {
          $array['url'] = str_replace('_'.array_pop($url_explode), '', $array['url']);
        }
        $updateArray = array(
          'id' => $id, 
          'url' => $array['url'].'_'.$id, 
          'sort' => $id, 
          'description' => $array['description'], 
        );
        if ($clone) {
          $updateArray['code'] = MG::getSetting('prefixCode').$id;          
          $array['code'] = MG::getSetting('prefixCode').$id;
        }
        $this->updateProduct($updateArray);
      } else {
        $updateArray = array(
          'id' => $id, 
          'url' => $array['url'], 
          'sort' => $id, 
          'description' => $array['description'], 
        );
        if ($clone) {
          $updateArray['code'] = MG::getSetting('prefixCode').$id;
          $array['code'] = MG::getSetting('prefixCode').$id;
        }
        $this->updateProduct($updateArray);
      }
      unset($landArr);
      
      $array['id'] = $id;
      $array['sort'] = (int)$id;
      $array['userProperty'] = $userProperty;
      $userProp = array();


      // Обновляем и добавляем варианты продукта.      
      $this->saveVariants($variants, $id);
      $variants = $this->getVariants($id);
      foreach ($variants as $variant) {
        $array['variants'][] = $variant;
      }

      $tempProd = $this->getProduct($id);
      $array['category_url'] = $tempProd['category_url'];
      $array['product_url'] = $tempProd['product_url'];

      $result = $array;
    }
    
    $this->updatePriceCourse($currencyShopIso, array($result['id']));  

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Изменяет данные о товаре.
   * <code>
   * $array = array(
   *  'id' => 23, // ID товара
   *  'title' => 'title', // название товара
   *  'url' => 'link', // последняя часть ссылки на товар
   *  'code' => 'CN230', // артикул товара
   *  'price' => 100, // цена товара
   *  'old_price' => 200, // старая цена товара
   *  'image_url' => 1434653074061713.jpg, // последняя часть ссылки на изображение товара
   *  'image_title' => '', // title изображения товара
   *  'image_alt' => '', // alt изображения товара
   *  'count' => 77, // остаток товара
   *  'weight' => 5, // вес товара
   *  'cat_id' => 4, // ID основной категории товара
   *  'inside_cat' => '1,2', // дополнительные категории товаров
   *  'description' => 'descr', // описание товара
   *  'short_description' => 'short descr', // краткое описание товара
   *  'meta_title' => 'title', // seo название товара
   *  'meta_keywords' => 'title купить, CN230, title', // seo ключевые слова
   *  'meta_desc' => 'meta descr', // seo описание товара
   *  'currency_iso' => 'RUR', // код валюты товара
   *  'recommend' => 0, // выводить товар в блоке рекомендуемых
   *  'activity' => 1, // выводить товар
   *  'unit' => 'шт.', // единица измерения товара (если null, то используется единица измерения основной категории товара)
   *  'new' => 0, // выводить товар в блоке новинок
   *  'userProperty' => Array, // массив с характеристиками товара
   *  'related' => 'В-500-1', // артикулы связанных товаров
   *  'variants' => Array, // массив с вариантами товаров
   *  'related_cat' => null, // ID связанных категорий
   *  'lang' => 'default', // язык для сохранения
   *  'landingTemplate' => 'noLandingTemplate', // шаблон для лэндинга товара
   *  'ytp' => '', // строка с торговым предложением для лэндинга
   *  'landingImage' => 'no-img.jpg', // изображение для лэндинга
   *  'storage' => 'all' // склад товара
   * );
   * $model = new Models_Product();
   * $model->updateProduct($array);
   * </code>
   * @param array $array массив с данными о товаре.
   * @return bool
   */
  public function updateProduct($array) {
    $id = $array['id'];
    if ($array['description']) {
      $array['description'] = MG::moveCKimages($array['description'], 'product', $id, 'desc', 'product', 'description');
    }

    $userProperty = !empty($array['userProperty']) ? $array['userProperty'] : null; //свойства товара
    $variants = !empty($array['variants']) ? $array['variants'] : array(); // варианты товара
    $updateFromModal = !empty($array['updateFromModal']) ? true : false; // варианты товара

    unset($array['userProperty']);
    unset($array['variants']);
    unset($array['updateFromModal']);

    if (!empty($array['url'])) {
      $array['url'] = URL::prepareUrl($array['url']);
    }
    
    // перехватываем данные для записи, если выбран другой язык
    $lang = $array['lang'];
    define('LANG', $lang);
    unset($array['lang']);


    // фильтрация данных
    $maskField = array('title','meta_title','meta_keywords','meta_desc','image_title','image_alt');
    foreach ($array as $k => $v) {
      if(in_array($k, $maskField)) {
        $v = htmlspecialchars_decode($v);
        $array[$k] = htmlspecialchars($v);       
      }
    }
	
    $result = false;

    // Если происходит обновление параметров.
    if (!empty($id)) {
      unset($array['delete_image']);

      if($array['weight']) {
        $array['weight'] = (double)str_replace(array(',',' '), array('.',''), $array['weight']);
      }

      if($array['price']) {
        $array['price'] = (double)str_replace(array(',',' '), array('.',''), $array['price']);
      }
      if($array['price_course']) {
        $array['price_course'] = (double)str_replace(array(',',' '), array('.',''), $array['price_course']);
      }
      if(empty($array['price_course'])) {
        unset($array['price_course']);
      } 


      unset($array['landingTemplate']);
      unset($array['landingColor']);
      unset($array['ytp']);
      unset($array['landingImage']);
      unset($array['landingSwitch']);

      // фикс для размерной сетки, чтобы сюда не шло то, что не надо
      unset($array['color']);
      unset($array['size']);


      unset($array['storage']);

      foreach ($array as $key => $value) {
        if($key == '') unset($array[$key]);
      }

      // Обновляем стандартные  свойства продукта.
      if (DB::query('
          UPDATE `'.PREFIX.'product`
          SET '.DB::buildPartQuery($array).'
          WHERE id = '.DB::quote($id))) {


        // Обновляем пользовательские свойства продукта.
        if (!empty($userProperty)) {
          Property::saveUserProperty($userProperty, $id, $lang);
        }


        // Эта проверка нужна только для того, чтобы исключить удаление 
        //вариантов при обновлении продуктов не из карточки товара в админке, 
        //например по нажатию на "лампочку".
        if (!empty($variants) || $updateFromModal) {

          // обновляем и добавляем варианты продукта.
          if ($variants === null) {
            $variants = array();
          }

          // оключаем сохранение вариантов, когда выбран другой язык, чтобы все не поломать
          if(empty($localeDataVariants)) {
            $this->saveVariants($variants, $id);
          }
        }

        $result = true;
      }
    } else {
      $result = $this->addProduct($array);
    }
    
    $currencyShopIso = MG::getSetting('currencyShopIso');  
    
    $this->updatePriceCourse($currencyShopIso, array($id));   

    Storage::clear('product-'.$id, 'sizeMap-'.$id, 'catalog', 'prop');

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Обновляет поле в варианте и синхронизирует привязку первого варианта с продуктом.
   * <code>
   * $array = array(
   * 'price' => 200, // цена
   * 'count' => 50 // количество
   * );
   * $model = new Models_Product();
   * $model->fastUpdateProductVariant(5, $array, 2);
   * </code>
   * @param int $id id варианта.
   * @param array $array ассоциативный массив поле=>значение.
   * @param int $product_id id продукта.
   * @return bool
   */
  public function fastUpdateProductVariant($id, $array, $product_id) {
    if (!DB::query('
       UPDATE `'.PREFIX.'product_variant`
       SET '.DB::buildPartQuery($array).'
       WHERE id = '.DB::quote($id))) {
      return false;
    };
  
    // Следующие действия выполняются для синхронизации  значений первого 
    // варианта со значениями записи продукта из таблицы product.
    // Перезаписываем в $array новое значение от первого в списке варианта,
    // и получаем id продукта от этого варианта
    $variants = $this->getVariants($product_id);
   
    $field = array_keys($array);
    foreach ($variants as $key => $value) {
      $array[$field[0]] = $value[$field[0]];
      break;
    }

    // Обновляем продукт в соответствии с первым вариантом.
    $this->fastUpdateProduct($product_id, $array);
    return true;
  }

  /**
   * Аналогичная fastUpdateProductVariant функция, но с поправками для
   * процесса импорта вариантов.
   * <code>
   *   $model = new Models_Product();
   *   $model->importUpdateProductVariant(5, $array, 2);
   * </code>
   * @param int $id id варианта.
   * @param array $array массив поле = значение.
   * @param int $product_id id продукта.
   * @return bool
   */
  public function importUpdateProductVariant($id, $array, $product_id) {
    if($array['weight']) {
     $array['weight'] = (double)str_replace(array(',',' '), array('.',''), $array['weight']);
    }

    if($array['price']) {
      $array['price'] = (double)str_replace(array(',',' '), array('.',''), $array['price']);
    }

    if($array['price_course']) {
      $array['price_course'] = (double)str_replace(array(',',' '), array('.',''), $array['price_course']);
    }
    if(empty($array['price_course'])) {
      unset($array['price_course']);
    }
    
    if (!$id || !DB::query('
       UPDATE `'.PREFIX.'product_variant`
       SET '.DB::buildPartQuery($array).'
       WHERE id = %d
     ', $id)) {
      $res = DB::query('SELECT MAX(id) FROM '.PREFIX.'product_variant');
      while($row = DB::fetchAssoc($res)) {
        $array['sort'] = $row['MAX(id)']+1;
      }
      DB::query('
       INSERT INTO `'.PREFIX.'product_variant`
       SET '.DB::buildPartQuery($array)
      );
    };

    return true;
  }

  /**
   * Обновление заданного поля продукта.
   * <code>
   * $array = array(
   * 'price' => 200, // цена
   * 'sort' => 5, // номер сортировки
   * 'count' => 50 // количество
   * );
   * $model = new Models_Product();
   * $model->fastUpdateProduct(5, $array);
   * </code>
   * @param int $id - id продукта.
   * @param array $array - параметры для обновления.
   * @return bool
   */
  public function fastUpdateProduct($id, $array) {
    if($array['price']) {
      $array['price'] = (double)str_replace(array(',',' '), array('.',''), $array['price']);
    }
    if($array['sort']) {
      $array['sort'] = (int)str_replace(array(',',' '), array('.',''), $array['sort']);
    }
    if($array['count']) {
      $array['count'] = (int)str_replace(array(',',' '), array('.',''), $array['count']);
    }
    
    if (!DB::query('
      UPDATE `'.PREFIX.'product`
      SET '.DB::buildPartQuery($array).'
      WHERE id = %d
    ', $id)) {
      return false;
    };
    
    $currencyShopIso = MG::getSetting('currencyShopIso');  
    $this->updatePriceCourse($currencyShopIso, array($id));   
    
    return true;
  }

  /**
   * Сохраняет варианты товара.
   * <code>
   * $variants = Array(
   *  0 => Array(
   *     'color' => 19, // id цвета варианта
   *     'size' => 11, // id размера варианта
   *     'title_variant' => '22 Голубой', // название
   *     'code' => 'SKU241', // артикул
   *     'price' => 2599, // цена
   *     'old_price' => 3000, // старая цена
   *     'weight' => 1, // вес
   *     'count' => 50, // количество
   *     'activity' => 1, // активность
   *     'id' => 1249, // id варианта
   *     'currency_iso' => 'RUR', // код валюты
   *     'image' => '13140250299.jpg' // название картинки варианта
   *  )
   * );
   * $model = new Models_Product();
   * $model->saveVariants($variants, 51);
   * </code>
   * @param array $variants набор вариантов
   * @param int $id id товара
   * @return bool
   */
  public function saveVariants($variants = array(), $id) {
    $existsVariant = array();
    
    $dbRes = DB::query('SHOW COLUMNS FROM `'.PREFIX.'product` WHERE FIELD = \'system_set\'');
    if(!$row = DB::fetchArray($dbRes)) {
      return false;
    }
    
    $dbRes = DB::query("SELECT * FROM `".PREFIX."product_variant` WHERE product_id = ".DB::quote($id));
    
    while ($arRes = DB::fetchAssoc($dbRes)) {
      $existsVariant[$arRes['id']] = $arRes;
    }

    foreach ($variants as $item) {
      $res = DB::query('SELECT count FROM '.PREFIX.'product_variant WHERE id = '.DB::quoteInt($item['id']));
      while ($row = DB::fetchAssoc($res)) {
        $countArray[$item['id']] = $row['count'];
      }
    }
    
    // Удаляем все имеющиеся товары.
    $res = DB::query("DELETE FROM `".PREFIX."product_variant` WHERE product_id = ".DB::quote($id));


    // Если вариантов как минимум два.
   // if (count($variants) > 1) {
      // Сохраняем все отредактированные варианты.
    $i = 1;
    foreach ($variants as $variant) { 
      if (!empty($existsVariant[$variant['id']]['1c_id'])) {
        $variant['1c_id'] = $existsVariant[$variant['id']]['1c_id'];
      }
      if (empty($variant['code'])) {
        $variant['code'] = MG::getSetting('prefixCode').$id.'_'.$i;
      }
      $variant['sort'] = $i++;
      unset($variant['product_id']);
      unset($variant['rate']);
      unset($variant['count_sort']);
      if(!empty($variant['id'])) {

      }

      $varId = $variant['id'];
      if($this->clone) {
        unset($variant['id']);
      }
      DB::query(' 
        INSERT  INTO `'.PREFIX.'product_variant` 
        SET product_id= '.DB::quote($id).", ".DB::buildPartQuery($variant)
      );

      $newVarId = DB::insertId();

    }
   // }
  }

  /**
   * Клонирует товар.
   * <code>
   * $productId = 25;
   * $model = new Models_Product;
   * $model->cloneProduct($productId);
   * </code>
   * @param int $id id клонируемого товара.
   * @return array
   */
  public function cloneProduct($id) {
    $result = false;

    $arr = $this->getProduct($id, true, true);
    $arr['unit'] = $arr['product_unit'];
    $arr['title'] = htmlspecialchars_decode($arr['title']);
    $image_url = basename($arr['image_url']);         
    
    foreach ($arr['images_product'] as $k=>$image) {
      $arr['images_product'][$k] = basename($image);
    }   
    $arr['image_url'] = implode("|", $arr['images_product']);
    $imagesArray = $arr['images_product'];
    
    $userProperty = $arr['thisUserFields'];

    unset($arr['product_unit']);
    unset($arr['category_unit']);
    unset($arr['real_category_unit']);
    unset($arr['category_name']);
    unset($arr['thisUserFields']);
    unset($arr['category_url']);
    unset($arr['product_url']);
    unset($arr['images_product']);
    unset($arr['images_title']);
    unset($arr['images_alt']);
    unset($arr['rate']);    
    unset($arr['plugin_message']);
    unset($arr['id']);
    unset($arr['count_buy']);
    $arr['code'] = '';
    $arr['userProperty'] = $userProperty;
    $variants = $this->getVariants($id);
    
    foreach ($variants as &$item) {
      // unset($item['id']);
      unset($item['product_id']);
      unset($item['rate']); 
      $item['code'] = '';
      $imagesArray[] = $item['image'];
      $item['image'] = $item['image'];      
    }
    
    $arr['variants'] = $variants;   
    
    // перед клонированием создадим копии изображений, 
    // чтобы в будущем можно было без проблемно удалять их вместе с удалением продукта       
    $result = $this->addProduct($arr, true);
    
    $this->cloneImagesProduct($imagesArray, $id, $result['id']); 


    
    $result['image_url'] = $image_url;
    $result['currency'] = MG::getSetting('currency');

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }
  
   /**
     * Клонирует изображения продукта.
     * <code>
     *   $imagesArray = array(
     *     '40Untitled-1.jpg',
     *     '41Untitled-1.jpg',
     *     '42Untitled-1.jpg'
     *   );
     *   $oldId = 40;
     *   $newId = 130;
     *   $model = new Models_Product;
     *   $model->deleteProduct($imagesArray, $oldId, $newId);
     * </code>
     * @param array $imagesArray массив url изображений, которые надо клонировать.
     * @param int $oldId старый ID товара.
     * @param int $newId новый ID товара.
     * @return bool
     */
  public function cloneImagesProduct($imagesArray = array(), $oldId = 0, $newId = 0) { 
    if(!$oldId && !$newId) return false;
    $ds = DS;
    $documentroot = str_replace($ds.'mg-core'.$ds.'models','',dirname(__FILE__)).$ds;     
    $dir = floor($oldId/100).'00'.$ds.$oldId;        
    $this->movingProductImage($imagesArray, $newId, 'uploads'.$ds.'product'.$ds.$dir, false);
 
    return true;
  }

  /**
   * Удаляет товар, его свойства, варианты, локализации, оптовые цены из базы данных.
   * <code>
   * $productId = 25;
   * $model = new Models_Product;
   * $model->deleteProduct($productId);
   * </code>
   * @param int $id id удаляемого товара
   * @return bool
   */
  public function deleteProduct($id) {
    $result = false;
    $prodInfo = $this->getProduct($id);  
       
    // $this->deleteImagesProduct($prodInfo['images_product'], $id); 
    // $this->deleteImagesVariant($id); 
    // $this->deleteImagesFolder($id);
    $imgFolder = SITE_DIR.'uploads'.DS.'product'.DS.floor($id/100).'00'.DS.$id;
    MG::rrmdir($imgFolder);

    // Удаляем продукт из базы.
    DB::query('
      DELETE
      FROM `'.PREFIX.'product`
      WHERE id = %d
    ', $id);

    DB::query('
      DELETE
      FROM `'.PREFIX.'product_user_property_data`
      WHERE product_id = %d
    ', $id);

    // Удаляем все варианты данного продукта.
    DB::query('
      DELETE
      FROM `'.PREFIX.'product_variant`
      WHERE product_id = %d
    ', $id);


    $result = true;
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

   /**
   * Удаляет папки из структуры папок изображений относящиеся к заданному продукту.
   * <code>
   * $productId = 25;
   * $model = new Models_Product;
   * $model->deleteImagesFolder($productId);
   * </code>
   * @param int $id id товара.
   */
  public function deleteImagesFolder($id) {
    if(!empty($id)) {
      $ds = DS;
      $path = 'uploads'.$ds.'product'.$ds.floor($id/100).'00'.$ds.$id;
      if(file_exists($path)) {
        if(file_exists($path.$ds.'thumbs')) {
          rmdir($path.$ds.'thumbs');
        }
        rmdir($path);
      }
    }
  }
  /**
   * Удаляет все картинки привязанные к продукту.
   * <code>
   *   $array = array(
   *    'product/100/105/120.jpg',
   *    'product/100/105/122.jpg',
   *    'product/100/105/121.jpg'
   *  );
   *  $model = new Models_Product();
   *  $model->deleteImagesProduct($array);
   * </code>
   * @param array $arrayImages массив с названиями картинок
   * @param int $productId ID товара
   */
   public function deleteImagesProduct($arrayImages = array(), $productId = false) {
     if(empty($arrayImages)) {       
       return true;
     }     
     // удаление картинки с сервера
    $uploader = new Upload(false);   
    foreach ($arrayImages as $key => $imageName) {
      $pos = strpos($imageName, 'no-img');
      if(!$pos && $pos !== 0) {
        $uploader->deleteImageProduct($imageName, $productId);     
      }
    }
  }
  /**
   * Получает информацию о запрашиваемом товаре.
   * <code>
   * $where = '`cat_id` IN (5,6)';
   * $model = new Models_Product;
   * $result = $model->deleteImagesFolder($where);
   * viewData($result);
   * </code>
   * @param string $where необязательный параметр, формирующий условия поиска, например: id = 1
   * @return array массив товаров
   */
  public function getProductByUserFilter($where = '') {
    $result = array();

    if ($where) {
      $where = ' WHERE '.$where;
    }
    
    $res = DB::query('
     SELECT  CONCAT(c.parent_url,c.url) as category_url,
       p.url as product_url, p.*, rate,(p.price_course + p.price_course * (IFNULL(rate,0))) as `price_course`, 
       p.`currency_iso`
     FROM `'.PREFIX.'product` p
       LEFT JOIN `'.PREFIX.'category` c
       ON c.id = p.cat_id
     '.$where);
    
    while ($order = DB::fetchAssoc($res)) {
      $result[$order['id']] = $order;
    }
    return $result;
  }

  /**
   * Получает информацию о запрашиваемом товаре по его ID.
   * <code>
   * $productId = 25;
   * $model = new Models_Product;
   * $product = $model->getProduct($productId);
   * viewData($product);
   * </code>
   * @param int $id id запрашиваемого товара.
   * @param bool $getProps возвращать ли характеристики.
   * @param bool $disableCashe отключить ли кэш.
   * @return array массив с данными о товаре.
   */
  public function getProduct($id, $getProps = true, $disableCashe = false) {    
    if(!$disableCashe && $getProps) $prodCash = Storage::get('product-'.$id.'-'.LANG.'-'.MG::getSetting('currencyShopIso'));

    if(!$prodCash) {
      $id =  intval($id);
      $result = array();
      $res = DB::query('
        SELECT  CONCAT(c.parent_url,c.url) as category_url, c.title as category_name, c.unit as category_unit, p.unit as product_unit,
          p.url as product_url, p.*, rate, (p.price_course + p.price_course * (IFNULL(rate,0))) as `price_course`, 
          p.`currency_iso` 
        FROM `'.PREFIX.'product` p
          LEFT JOIN `'.PREFIX.'category` c
          ON c.id = p.cat_id
        WHERE p.id = '.DB::quote($id, true));
     
      if (!empty($res)) {
        if ($product = DB::fetchAssoc($res)) {
          $result = $product;

          

          $imagesConctructions = $this->imagesConctruction($result['image_url'],$result['image_title'],$result['image_alt'], $result['id']);
          $result['images_product'] = $imagesConctructions['images_product']; 
          $result['images_title'] = $imagesConctructions['images_title']; 
          $result['images_alt'] = $imagesConctructions['images_alt']; 
          $result['image_url'] = $imagesConctructions['image_url']; 
          $result['image_title'] = $imagesConctructions['image_title']; 
          $result['image_alt'] = $imagesConctructions['image_alt'];  


          $result['unit'] = $result['product_unit'];
        }
      }
      
      if (!isset($result['category_unit'])) {
        $result['category_unit'] = 'шт.';
      }

      $cat = array('unit'=>$result['category_unit']);
      MG::loadLocaleData($id, LANG, 'product', $result);
      MG::loadLocaleData($id, LANG, 'category', $cat);
      $result['product_unit'] = $result['unit'];
      $result['real_category_unit'] = $result['category_unit'];
      $result['real_category_unit'] = $cat['unit'];;
      if (isset($result['product_unit']) && $result['product_unit'] != null && strlen($result['product_unit']) > 0) {
        $result['category_unit'] = $result['product_unit'];
      }
      if ($getProps) {
        Storage::save('product-'.$id.'-'.LANG.'-'.MG::getSetting('currencyShopIso'), $result);
      }
    } else {
      $result = $prodCash;

      if(MG::enabledStorage()) {
      } else {
        $res = DB::query('SELECT `count`
          FROM '.PREFIX.'product 
          WHERE `id` = '.DB::quoteInt($id));
        while ($row = DB::fetchAssoc($res)) {
          $result['count'] = $row['count'];
        }
      }
    }

    // подгрузка цен без кэша
    if(!MG::isAdmin()) {
      $res = DB::query('SELECT p.id, p.price, p.price_course * (IFNULL(c.rate, 0) + 1) AS price_course FROM '.PREFIX.'product AS p
        LEFT JOIN '.PREFIX.'category AS c ON c.id = p.cat_id
        WHERE p.id = '.DB::quoteInt($id));
      while($row = DB::fetchAssoc($res)) {
        $result['price'] = MG::convertPrice($row['price']);
        $result['price_course'] = MG::convertPrice($row['price_course']);
      }  
    } 

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }
  
  /**
   * Создает массивы данных для картинок товара, возвращает три массива со ссылками, заголовками и альт, текстами.
   * <code>
   *   $model = new Models_Product();
   *   $imageUrl = '120.jpg|121.jpg';
   *   $imageTitle = 'Каритинка товара';
   *   $imageAlt = 'Альтернативная подпись картинки';
   *   $res = $model->imagesConctruction($imageUrl, $imageTitle, $imageAlt);
   *   viewData($res);
   * </code>
   * @param string $imageUrl строка с разделителями | между ссылок.
   * @param string $imageTitle строка с разделителями | между заголовков.
   * @param string $imageAlt строка с разделителями | между тестов.
   * @param string $id ID товара.
   * @return array
   */
  public function imagesConctruction($imageUrl, $imageTitle, $imageAlt, $id = 0) {
    $result = array(
      'images_product'=>array(),
      'images_title'=>array(),
      'images_alt'=>array()
    );
    
    // Получаем массив картинок для продукта, при этом первую в наборе делаем основной.
    $arrayImages = explode("|", $imageUrl);
    if (!empty($arrayImages)) {
      $arrayImages = array($arrayImages[0]);
    }
    
    foreach($arrayImages as $cell=>$image) {
      $arrayImages[$cell] = str_replace(SITE.'/uploads/', '', mgImageProductPath($image, $id));
    }
    
    if (!empty($arrayImages)) {
      $result['image_url'] = $arrayImages[0];
    }

    $result['images_product'] = $arrayImages;  
    // Получаем массив title для картинок продукта, при этом первый в наборе делаем основной.
    $arrayTitles = explode("|", $imageTitle);
    if (!empty($arrayTitles)) {
      $result['image_title'] = $arrayTitles[0];
    }

    $result['images_title'] = $arrayTitles;  

    // Получаем массив alt для картинок продукта, при этом первый в наборе делаем основной.
    $arrayAlt = explode("|", $imageAlt);
    if (!empty($arrayAlt)) {
      $result['image_alt'] = $arrayAlt[0];
    }

    $result['images_alt'] = $arrayAlt;  
    
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }
    
  /**
   * Обновляет остатки продукта, увеличивая их на заданное количество.
   * <code>
   * Models_Product::increaseCountProduct(37, 'SKU348', 2);
   * </code>
   * @param int $id номер продукта.
   * @param string $code артикул.
   * @param int $count прибавляемое значение к остатку.
   */
  public function increaseCountProduct($id, $code, $count) {

    $sql = "
      UPDATE `".PREFIX."product_variant` as pv 
      SET pv.`count`= pv.`count`+".DB::quote($count)." 
      WHERE pv.`product_id`=".DB::quote($id)." 
        AND pv.`code`=".DB::quote($code)." 
        AND pv.`count`>=0
    ";

    DB::query($sql);

    $sql = "
      UPDATE `".PREFIX."product` as p 
      SET p.`count`= p.`count`+".DB::quote($count)." 
      WHERE p.`id`=".DB::quote($id)." 
        AND p.`code`=".DB::quote($code)." 
        AND  p.`count`>=0
    ";

    DB::query($sql);
  }

  /**
   * Обновляет остатки продукта, уменьшая их количество,
   * при смене статуса заказа с "отменен" на любой другой.
   * <code>
   * Models_Product::decreaseCountProduct(37, 'SKU348', 2);
   * </code>
   * @param int $id ID продукта.
   * @param string $code Артикул.
   * @param int $count Прибавляемое значение к остатку.
   */
  public function decreaseCountProduct($id, $code, $count) {

    $product = $this->getProduct($id);
    $variants = $this->getVariants($product['id']);
    foreach ($variants as $idVar => $variant) {
      if ($variant['code'] == $code) {
        $variantCount = ($variant['count'] * 1 - $count * 1) >= 0 ? $variant['count'] - $count : 0;
        $sql = "
          UPDATE `".PREFIX."product_variant` as pv 
          SET pv.`count`= ".DB::quote($variantCount, true)." 
          WHERE pv.`id`=".DB::quote($idVar)." 
            AND pv.`code`=".DB::quote($code)." 
            AND  pv.`count`>0";
        DB::query($sql);
      }
    }

    $product['count'] = ($product['count'] * 1 - $count * 1) >= 0 ? $product['count'] - $count : 0;
    $sql = "
      UPDATE `".PREFIX."product` as p 
      SET p.`count`= ".DB::quote($product['count'], true)." 
      WHERE p.`id`=".DB::quote($id)." 
        AND p.`code`=".DB::quote($code)."
        AND  p.`count`>0";
    DB::query($sql);
  }

  /**
   * Удаляет все миниатюры и оригинал изображения товара из папки upload.
   * @param array $arrayDelImages массив с изображениями для удаления
   * @return bool
   * @deprecated
   */
  public function deleteImageProduct($arrayDelImages) {
    if (!empty($arrayDelImages)) {
      foreach ($arrayDelImages as $value) {
        if (!empty($value)) {
          // Удаление картинки с сервера.
          $documentroot = str_replace('mg-core'.$ds.'models', '', __DIR__);
          if (is_file($documentroot."uploads/".basename($value))) {
            unlink($documentroot."uploads/".basename($value));
            if (is_file($documentroot."uploads/thumbs/30_".basename($value))) {
              unlink($documentroot."uploads/thumbs/30_".basename($value));
            }
            if (is_file($documentroot."uploads/thumbs/70_".basename($value))) {
              unlink($documentroot."uploads/thumbs/70_".basename($value));
            }
          }
        }
      }
    }
    return true;
  }

  /**
   * Возвращает общее количество продуктов каталога.
   * <code>
   * $result = Models_Product::getProductsCount();
   * viewData($result);
   * </code>
   * @return int количество товаров.
   */
  public function getProductsCount() {
    $result = 0;
    $res = DB::query('
      SELECT count(id) as count
      FROM `'.PREFIX.'product`
    ');

    if ($product = DB::fetchAssoc($res)) {
      $result = $product['count'];
    }

    return $result;
  }

  /**
   * Получает продукт по его URL.
   * <code>
   * $url = 'nike-air-versitile_102';
   * $result = Models_Product::getProductByUrl($url);
   * viewData($result);
   * </code>
   * @param string $url запрашиваемого товара.
   * @param int $catId id-категории, т.к. в разных категориях могут быть одинаковые url.
   * @return array массив с данными о товаре.
   */
  public function getProductByUrl($url, $catId = false) {
    $result = array();
    if ($catId !== false) {
      $where = ' and cat_id='.DB::quote($catId);
    }

    $res = DB::query('
      SELECT *
      FROM `'.PREFIX.'product`
      WHERE url = '.DB::quote($url).' 
    '.$where);
   
    if (!empty($res)) {
      if ($product = DB::fetchAssoc($res)) {
        $result = $product;
      }
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Получает цену запрашиваемого товара по его id.
   * <code>
   * $result = Models_Product::getProductPrice(5);
   * viewData($result);
   * </code>
   * @param int $id id изменяемого товара.
   * @return bool|float $error в случаи ошибочного запроса.
   */
  public function getProductPrice($id) {
    $result = false;
    $res = DB::query('
      SELECT price
      FROM `'.PREFIX.'product`
      WHERE id = %d
    ', $id);

    if ($row = DB::fetchObject($res)) {
      $result = $row->price;
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Создает форму пользовательских характеристик для товара.
   * В качестве входящего параметра получает массив:
   * <code>
   * $param = array(
   *   'id' => null, // id товара.
   *   'maxCount' => null, // максимальное количество товара на складе.
   *   'productUserFields' => null, // массив пользовательских полей для данного продукта.
   *   'action' => "/catalog", // ссылка для метода формы.
   *   'method' => "POST", // тип отправки данных на сервер.
   *   'ajax' => true, // использовать ajax для пересчета стоимости товаров.
   *   'blockedProp' => array(), // массив из ID свойств, которые ненужно выводить в форме.
   *   'noneAmount' => false, // не выводить  input для количества.
   *   'titleBtn' => "В корзину", // название кнопки.
   *   'blockVariants' => '', // блок вариантов.
   *   'classForButton' => 'addToCart buy-product buy', // классы для кнопки.
   *   'noneButton' => false, // не выводить кнопку отправки.
   *   'addHtml' => '' // добавить HTML в содержимое формы.
   *   'currency_iso' => '', // обозначение валюты в которой сохранен товар
   *   'printStrProp' => 'true', // выводить строковые характеристики
   *   'printCompareButton' => 'true', // выводить кнопку сравнения
   *   'buyButton' => 'true', // показывать кнопку 'купить' в миникарточках (если false - показывается кнопка 'подробнее')
   *   'productData' => 'Array', // массив с данными о товаре
   *   'showCount' => 'true' // показывать блок с количеством
   * );
   * $model = new Models_Product;
   * $result = $model->getProduct($param);
   * echo $result;
   * </code>
   * @param array $param массив параметров.
   * @param string $adminOrder заказ для админки или нет (по умолчанию - нет).
   * @return string html форма.
   */
  public function createPropertyForm(
  $param = array(
    'id' => null,
    'maxCount' => null,
    'productUserFields' => null,
    'action' => "/catalog",
    'method' => "POST",
    'ajax' => true,
    'blockedProp' => array(),
    'noneAmount' => false,
    'titleBtn' => "В корзину",
    'blockVariants' => '',
    'classForButton' => 'addToCart buy-product buy',
    'noneButton' => false,
    'addHtml' => '',   
    'printStrProp' => null,
    'printCompareButton' => null,
    'buyButton' => '',
    'currency_iso' => '',
    'productData' => null,
    'showCount' => true,
  ), $adminOrder = 'nope'
  ) {
    extract($param);
    if (empty($classForButton)) {
      $classForButton = 'addToCart buy-product buy';
    }
    if ($id === null || $maxCount === null) {
      return "error param!";
    }
    if (empty($printStrProp)) {
      $printStrProp = MG::getSetting('printStrProp');    
    }
    if ($printCompareButton===null) {
      $printCompareButton = MG::getSetting('printCompareButton');    
    }
	
	if($this->groupProperty==null){
	  $this->groupProperty = Property::getPropertyGroup(true);
	}
	
    $catalogAction = MG::getSetting('actionInCatalog') === "true" ? 'actionBuy' : 'actionView';
    // если используется аяксовый метод выбора, то подключаем доп класс для работы с формой. 
    $marginPrice = 0; // добавочная цена, в зависимости от выбранных автоматом характеристик
    $secctionCartNoDummy = array(); //Не подставной массив характеристик, все характеристики с настоящими #ценами#
    //в сессию записать реальные значения, в паблик подмену, с привязкой в конце #№
    $html = '';
   //if ($ajax) {
    //  mgAddMeta("<script type=\"text/javascript\" src=\"".SITE."/mg-core/script/jquery.form.js\"></script>");
    //}

    $currencyRate = MG::getSetting('currencyRate');
    $currencyShort = MG::getSetting('currencyShort');
    $currencyRate = $currencyRate[$currency_iso];
    $currencyShort = $currencyShort[$currency_iso];
    $propPieces = array();
    $htmlProperty = '';


    $data = array(
     'maxCount' => $maxCount,
     'noneAmount' => $noneAmount,
     'noneButton' => $noneButton,
     'printCompareButton' => $printCompareButton,
     'ajax' => $ajax,
     'buyButton' => $buyButton,
     'classForButton' => $classForButton,
     'titleBtn' => $titleBtn,
     'id' => $id,
     'blockVariants' => $blockVariants,
     'addHtml' => $addHtml,
     'price' => ($productData ? $productData['price_course']: ''),
     'old_price' => ($productData ? $productData['old_price'] : ''),
     'activity' => $productData['activity'],
	   'parentData' => $param,
	   'htmlProperty' => $htmlProperty,
     'showCount' => $showCount,
     'action' => $action,
     'method' => $method,
     'catalogAction' => $catalogAction,
    );

    if ($adminOrder == 'yep') {
      // $adminOrderFile = str_replace('mg-core'.DS.'models', '', dirname(__FILE__)).'mg-admin'.DS.'section'.DS.'views'.DS.'layout'.DS.'adminOrder.php';
      $adminOrderFile = SITE_DIR.'mg-admin'.DS.'section'.DS.'views'.DS.'layout'.DS.'adminOrder.php';
      ob_start();
      include $adminOrderFile;
      $htmlLayout = ob_get_contents();
      ob_end_clean();
    }
    else{
      $htmlLayout = MG::layoutManager('layout_property', $data);
    }

    if (strpos($htmlLayout, '<form') === false ||
        strpos($htmlLayout, $action) === false ||
        strpos($htmlLayout, $method) === false ||
        strpos($htmlLayout, $catalogAction) === false ||
        strpos($htmlLayout, '</form>') === false
        ) {
      $htmlForm = '<form action="'.SITE.$action.'" method="'.$method.'" class="property-form '.$catalogAction.'" data-product-id='.$id.'>';
      $htmlForm .= $htmlLayout;
      $htmlForm .= '</form>';
    }
    else{
      $htmlForm = $htmlLayout;
    }

    $result = array(
        'html' => $htmlForm,    
        'marginPrice' => $marginPrice * $currencyRate, 
        'defaultSet' => $defaultSet,  // набор характеристик, которые были бы выбраны по умолчанию при открытии карточки товара.
        'propertyNodummy' => $secctionCartNoDummy, 
        'stringsProperties' => $stringsProperties
        );
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Формирует блок вариантов товара.
   * <code>
   * $model = new Models_Product;
   * $result = $model->getBlockVariants(5);
   * echo $result;
   * </code>
   * @param int $id id товара
   * @param int $cat_id id категории
   * @param string $adminOrder заказ из админки или нет (по умолчанию - нет)
   * @return string|array (array - для админки)
   */
  public function getBlockVariants($id, $cat_id = 0, $adminOrder = 'nope') {
    $arr = $this->getVariants($id, false, true);  

    foreach ($arr as $key => $value) {
      if($value['count'] == 0) {
        $tmp = $value;
        unset($arr[$key]);
        $arr[$tmp['id']] = $tmp;
      }
    }

    foreach ($arr as &$var) {
      $var['price'] = MG::priceCourse($var['price_course']);
    }
    if ($adminOrder == 'yep') {
      $html = $arr;
    }
    else{
      $html = MG::layoutManager('layout_variant', array('blockVariants'=>$arr, 'type'=>'product'));
    }
    return $html;
  }

  /**
   * Формирует массив блоков вариантов товаров на странице каталога.
   * Метод создан для сокращения количества запросов к БД.
   * <code>
   * $model = new Models_Product;
   * $result = $model->getBlocksVariantsToCatalog(array(2,3,4));
   * echo $result;
   * </code>
   * @param int $array массив id товаров
   * @param array $returnArray если true то вернет просто массив без html блоков
   * @param bool $mgadmin если true то вернет данные для админки
   * @return string|array
   */
  public function getBlocksVariantsToCatalog($array, $returnArray = false, $mgadmin = false) {
    if (!empty($array)) {
      $in = implode(',', $array);
    }
    $orderBy = 'ORDER BY sort, id';
    $where = '';
    if(MG::getSetting('filterSortVariant') && !$mgadmin) {
      $parts = explode('|',MG::getSetting('filterSortVariant'));
      $parts[0] = $parts[0] == 'count' ? 'count_sort' : $parts[0];
      $orderBy = ' ORDER BY `'.DB::quote($parts[0],1).'` '.DB::quote($parts[1],1).', id';      
    }
    if(MG::getSetting('showVariantNull')=='false' && !$mgadmin) {
      if(MG::enabledStorage()) {
        $orderBy = ' AND (SELECT SUM(ABS(count)) FROM '.PREFIX.'product_on_storage WHERE product_id = p.id AND variant_id = pv.id) > 0 '.$orderBy; 
      } else {
        $orderBy = ' AND (pv.`count` != 0 OR pv.`count` IS NULL) '.$orderBy; 
      }
      
    }
    if(MG::enabledStorage()) {
      $storageCheck = ',(SELECT SUM(ABS(count)) FROM '.PREFIX.'product_on_storage WHERE product_id = p.id AND variant_id = pv.id) AS count';
    }
    // Получаем все варианты для передранного массива продуктов.
    if ($in) {
      $res = DB::query('
       SELECT pv.*, c.rate,(pv.price_course + pv.price_course * (IFNULL(c.rate,0))) as `price_course`,
       IF( pv.count<0,  1000000, pv.count ) AS  `count_sort`
       '.$storageCheck.'
       FROM `'.PREFIX.'product_variant` pv    
         LEFT JOIN `'.PREFIX.'product` as p ON 
           p.id = pv.product_id
         LEFT JOIN `'.PREFIX.'category` as c ON 
           c.id = p.cat_id  
       WHERE pv.product_id  in ('.$in.')
       '.$orderBy);

      if (!empty($res)) {
        while ($variant = DB::fetchAssoc($res)) {     
          if (!$returnArray) {


            $variant['price'] = MG::priceCourse($variant['price_course']);
          }
          $results[$variant['product_id']][] = $variant;
        }
      }
    }
    $productCount = 0;

    if(!$mgadmin) {
      foreach ($results as &$blockVariants) {
        for($i = 0; $i < count($blockVariants); $i++) {
          $productCount += $blockVariants[$i]['count'];
          if($blockVariants[$i]['count'] == 0) {
            $blockVariants[] = $blockVariants[$i];
            unset($blockVariants[$i]);
          }
        }
        $blockVariants = array_values($blockVariants);
      }
    }
    if ($returnArray) {
      return $results;
    }

    sort($array);
    
    $cash = Storage::get('getBlocksVariantsToCatalog-'.md5(json_encode($array).$productCount.LANG));
    if(!$cash) {
      if (!empty($results)) {
        // Для каждого продукта создаем HTML верстку вариантов.
        foreach ($results as &$blockVariants) {       
          $html = MG::layoutManager('layout_variant', array('blockVariants'=>$blockVariants, 'type'=>'catalog'));
          $blockVariants = $html;
        }
      }
      Storage::save('getBlocksVariantsToCatalog-'.md5(json_encode($array).$productCount.LANG), $results);
      return $results;
    } else {
      return $cash;
    }
  }

  /**
   * Формирует добавочную строку к названию характеристики,
   * в зависимости от наличия наценки и стоимости.
   * <code>
   * $model = new Models_Product;
   * $result = $model->addMarginToProp(250);
   * echo $result;
   * </code>
   * @param float $margin наценка
   * @param float $rate множитель цены
   * @param string $currency валюта
   * @return string
   */
  public function addMarginToProp($margin, $rate = 1, $currency = false) {
    $currency = $currency ? $currency : MG::getSetting('currencyShopIso');
    $symbol = '+';
    if (!empty($margin)) {
      if ($margin < 0) {
        $symbol = '-';
        $margin = $margin * -1;
      }
    }
    return (!empty($margin) || $margin === 0) ? ' '.$symbol.' '.MG::numberFormat($margin * $rate).' '.MG::getSetting('currency') : '';
  }

  /**
   * Отделяет название характеристики от цены название_пункта#стоимость#.
   * Пример входящей строки: "Красный#300#"
   * <code>
   * $model = new Models_Product;
   * $result = $model->parseMarginToProp('Красный#300#');
   * echo $result;
   * </code>
   * @param string $value строка, которую надо распарсить
   * @return array $array массив с разделенными данными, название пункта и стоимость.
   */
  public function parseMarginToProp($value) {
    $array = array();
    $pattern = "/^(.*)#([\d\.\,-]*)#$/";
    preg_match($pattern, $value, $matches);
    if (isset($matches[1]) && isset($matches[2])) {
      $array = array('name' => $matches[1], 'margin' => $matches[2]);
    }
    return $array;
  }

  /**
   * Обновление состояния корзины.
   * Используеться для пересчета корзины и обновления цены в карточке товара ajax'ом
   * <code>
   *   $model = new Models_Product;
   *   $model->calcPrice();
   * </code>
   */
  public function calcPrice() {
    $product = $this->getProduct($_POST['inCartProductId']);
    $currencyRate = MG::getSetting('currencyRate');      
    $currencyShopIso = MG::getSetting('currencyShopIso'); 
    $variantId = 0;    
    if (isset($_POST['variant'])) {
      $variants = $this->getVariants($_POST['inCartProductId']);

      $variant = $variants[$_POST['variant']];
      $variantId = $_POST['variant'];
      $product['price'] = $variant['price'];           
      $product['code'] = $variant['code'];
      $product['count'] = $variant['count'];
      $product['old_price'] = $variant['old_price'];
      $product['weight'] = $variant['weight'];
      $product['price_course'] = $variant['price_course'];   
      $product['variant'] = $variant['id'];
    }

    $cart = new Models_Cart;
    $property = $cart->createProperty($_POST);
    $product['currency_iso'] = $product['currency_iso']?$product['currency_iso']:$currencyShopIso;
    $product['price'] = $product['price_course']; 

    $tmpPrice = $product['price'];

    
    $product['price'] = SmalCart::plusPropertyMargin($product['price'], $property['propertyReal'], $currencyRate[$product['currency_iso']]);

    $product['real_price'] = $product['price'];
    
    // $product['old_price'] *= $currencyRate[$product['currency_iso']];
    $product['remInfo'] = !empty($_POST['remInfo']) ? $_POST['remInfo'] : '';



    if (NULL_OLD_PRICE && $product['price'] > $product['old_price']) {
      $product['old_price'] = 0;
    }

    $response = array(
      'status' => 'success',
      'data' => array(
        'title' => $product['title'],
        'price' => MG::numberFormat($product['price']).' <span class="currency">'.MG::getSetting('currency').'</span>',
        'old_price' => MG::numberFormat($product['old_price']).' '.MG::getSetting('currency'),
        'code' => $product['code'],
        'count' => $product['count'],
        'price_wc' => $product['price'],
        'real_price' => $product['real_price'],
        'weight' => $product['weight'],
        'count_layout' => MG::layoutManager('layout_count_product', $product),
        'actionInCatalog' => MG::getSetting('actionInCatalog'),
      )
    );

    echo json_encode($response);
    exit;
  }

  /**
   * Возвращает набор вариантов товара.
   * <code>
   * $productId = 25;
   * $model = new Models_Product;
   * $variants = $model->getVariants($productId);
   * viewData($variants);
   * </code>
   * @param int $id id продукта для поиска его вариантов
   * @param string|bool $title_variants название варианта продукта для поиска его вариантов
   * @param bool $sort использовать ли сортировку результатов (из настройки 'filterSortVariant')
   * @return array $array массив с параметрами варианта.
   */
  public function getVariants($id, $title_variants = false, $sort = false) {
    $results = array();

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $results, $args);
  }

  /**
   * Возвращает массив id характеристик товара, которые ненужно выводить в карточке.
   * <code>
   * $result = Models_Product::noPrintProperty($productId);
   * viewData($result);
   * </code>
   * @return array $array - массив с id.
   */
  public function noPrintProperty() {
    $results = array();
   
    $res = DB::query('
      SELECT  `id`
      FROM `'.PREFIX.'property`     
      WHERE `activity` = 0');
    
    while ($row = DB::fetchAssoc($res)) {
      $results[] = $row['id'];
    }
 
    return $results;
  }
  
  /**
   * Возвращает HTML блок связанных товаров.
   * <code>
   * $args = array(
   *  'product' => 'CN182,В-500-1', // артикулы связанных товаров
   *  'category' => '2,4' // ID связанных категорий
   * );
   * $model = new Models_Product;
   * $result = $model->createRelatedForm($args);
   * echo $result;
   * </code>
   * @param array $args массив с данными о товарах
   * @param string $title заголовок блока
   * @param string $layout используемый лэйаут
   * @return string
   */
  public function createRelatedForm($args,$title='С этим товаром покупают', $layout = 'layout_related') {
    if($args) {
      $data['title'] = $title;
      
      $stringRelated = ' null';
      $sortRelated = array();
      if (!empty($args['product'])) {
        foreach (explode(',',$args['product']) as $item) {
          $stringRelated .= ','.DB::quote($item);
          $sortRelated[$item] = $item;
        }
        $stringRelated = substr($stringRelated, 1);
      }

      // выводить ли товар если его нет в наличии
      if(MG::getOption('printSameProdNullRem') == "true") {
        $forSameProdFilter = ' and count <> 0';
      } else {
        $forSameProdFilter = '';
      }

      $data['products'] = $this->getProductByUserFilter(' p.code IN ('.$stringRelated.') and p.activity = 1'.$forSameProdFilter);
      
      $datarelatedCat = array();
      if (!empty($args['category'])) {
        $stringRelatedCat = ' null';
        foreach (explode(',',$args['category']) as $item) {
          $stringRelatedCat .= ','.DB::quote($item);
        }
        $stringRelatedCat = substr($stringRelatedCat, 1);
        $relatedCat = $this->getProductByUserFilter(' p.`cat_id` IN ('.$stringRelatedCat.') and p.activity = 1'.$forSameProdFilter);
        shuffle($relatedCat);        
      }
      if (!empty($relatedCat)) {
        foreach ($relatedCat as $key => $prod) {
          if ($key > 10) {
            break;
          }
          $data['products'][] = $prod;
          $sortRelated[$prod['code']] = $prod;
        }
      }
      if(!empty($data['products'])) {
        $data['currency'] = MG::getSetting('currency');
        foreach ($data['products'] as $item) {            
          $img = explode('|',$item['image_url']);
          $item['img'] = $img[0];
          $item['category_url'] = (MG::getSetting('shortLink') == 'true' ? '' : $item['category_url'].'/');
          $item['category_url'] = ($item['category_url'] == '/' ? 'catalog/' : $item['category_url']);
          $item['url'] = (MG::getSetting('shortLink') == 'true' ? SITE .'/'.$item["product_url"] : SITE .'/'.(isset($item["category_url"])&&$item["category_url"]!='' ? $item["category_url"] : 'catalog/').$item["product_url"]);


          if (NULL_OLD_PRICE && $item['price'] > $item['old_price']) {
            $item['old_price'] = 0;
          }

          $item['price'] = MG::priceCourse($item['price_course']);

          $sortRelated[$item['code']] = $item;
        }
        $data['products'] = array();
        //сортируем связанные товары в том порядке, в котором они идут в строке артикулов
        foreach ($sortRelated as $item) {
          if(!empty($item['id']) && is_array($item)) {
            $data['products'][$item['id']] = $item;
          }
        }      
        $result = '';
      }
      
    };
    
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }
  
  
  /**
   * Конвертирование стоимости товаров по заданному курсу.
   * <code>
   * $model = new Models_Product;
   * $model->convertToIso('USD', array(2, 3, 4));
   * </code>
   * @param string $iso валюта в которую будет производиться конвертация.
   * @param array $productsId массив с id продуктов.
   */
  public function convertToIso($iso,$productsId=array()) {
    
    $productsId = implode(',', $productsId);
    if(empty($productsId)) {$productsId = 0;};
    
    // вычислим соотношение валют имеющихся в базе товаров к выбранной для замены
    // вычисление производится на основе имеющихся данных по отношению в  валюте магазина
    $currencyShort = MG::getSetting('currencyShort');     
    $currencyRate = MG::getSetting('currencyRate');
    $currencyShopIso = MG::getSetting('currencyShopIso'); 
       
    // если есть непривязанные к валютам товары, то  назначаем им текущую валюту магазина
    DB::query('
      UPDATE `'.PREFIX.'product` SET 
            `currency_iso` = '.DB::quote($currencyShopIso).'
      WHERE `currency_iso` =  "" AND `id` IN ('.DB::quote($productsId, true).')');
    DB::query('
      UPDATE `'.PREFIX.'product_variant` SET 
            `currency_iso` = '.DB::quote($currencyShopIso).'
      WHERE `currency_iso` =  "" AND `id` IN ('.DB::quote($productsId, true).')');

    // запоминаем базовое соотношение курсов к валюте магазина
    $rateBaseArray = $currencyRate;  
    $rateBase = $currencyRate[$iso];  
    // создаем новое соотношение валют по отношению в выбранной для конвертации
    foreach ($currencyRate as $key => $value) {     
        if(!empty($rateBase)) {    
          $currencyRate[$key] = $value / $rateBase;                 
        }        
    }
    $currencyRate[$iso] = 1;
  
    // пересчитываем цену, старую цену и цену по курсу для выбранных товаров
    foreach ($currencyRate as $key => $rate) { 
      DB::query('
      UPDATE `'.PREFIX.'product`
      SET `price`= ROUND(`price`*'.DB::quote($rate,TRUE).',2),
          `price_course`= ROUND(`price`*'.DB::quote(($rateBaseArray[$iso]?$rateBaseArray[$iso]:1),TRUE).',2)
      WHERE currency_iso = '.DB::quote($key).' AND `id` IN ('.DB::quote($productsId, true).')');
      
      // также и в вариантах
      DB::query('
      UPDATE `'.PREFIX.'product_variant`
       SET `price`= ROUND(`price`*'.DB::quote($rate,TRUE).',2),
          `price_course`= ROUND(`price`*'.DB::quote(($rateBaseArray[$iso]?$rateBaseArray[$iso]:1),TRUE).',2)
      WHERE currency_iso = '.DB::quote($key).' AND `product_id` IN ('.DB::quote($productsId, true).')');
    }
    
    // всем выбранным продуктам изменяем ISO
     DB::query('
      UPDATE `'.PREFIX.'product`
      SET `currency_iso` = '.DB::quote($iso).'
      WHERE `id` IN ('.DB::quote($productsId, true).')');
     
     DB::query('
      UPDATE `'.PREFIX.'product_variant`
      SET `currency_iso` = '.DB::quote($iso).'
      WHERE `product_id` IN ('.DB::quote($productsId, true).')');

  }

   /**
   * Обновления цены выдранных товаров в соответствии с курсом валюты.
   * <code>
   * $model = new Models_Product;
   * $model->updatePriceCourse('USD', array(2, 3, 4));
   * </code>
   * @param string $iso валюта в которую будет производиться конвертация.
   * @param array $listId массив с id продуктов.
   */
  public function updatePriceCourse($iso,$listId = array()) {
    
     if(empty($listId)) {$listId = 0;}
     else{
       foreach ($listId as $key => $value) {
         $listId[$key] = intval($value);
       }
       $listId = implode(',', $listId);     
     }
    
    // вычислим соотношение валют имеющихся в базе товаров к выбранной для замены
    // вычисление производится на основе имеющихся данных по отношению в  валюте магазина
    $currencyShort = MG::getSetting('currencyShort');     
    $currencyRate = unserialize(stripcslashes(MG::getOption('currencyRate')));
    $currencyShopIso = MG::getOption('currencyShopIso');

    $rate = $currencyRate[$iso];
    
    
    $where = '';
    if(!empty($listId)) {
      $where =' AND `id` IN ('.DB::quote($listId, true).')';
    }
    
    $whereVariant = '';
    if(!empty($listId)) {
      $whereVariant =' AND `product_id` IN ('.DB::quote($listId, true).')';
    }
    
    DB::query('
     UPDATE `'.PREFIX.'product` SET 
           `currency_iso` = '.DB::quote($currencyShopIso).'
     WHERE `currency_iso` = "" '.$where);
  
    
    $rate = $currencyRate[$iso];  
    foreach ($currencyRate as $key => $value) {     
        if(!empty($rate)) {
          $currencyRate[$key] = $value / $rate;                 
        }        
    }
    $currencyRate[$iso] = 1;

    foreach ($currencyRate as $key => $rate) {
   
      DB::query('
      UPDATE `'.PREFIX.'product` 
        SET `price_course`= ROUND(`price`*'.DB::quote((float)$rate,TRUE).',2)          
      WHERE currency_iso = '.DB::quote($key).' '.$where);
     
      DB::query('
      UPDATE `'.PREFIX.'product_variant` 
        SET `price_course`= ROUND(`price`*'.DB::quote((float)$rate,TRUE).',2)         
      WHERE currency_iso = '.DB::quote($key).' '.$whereVariant);
    }
    
  }
  
   /**
   * Удаляет картинки вариантов товара.
   * <code>
   * $model = new Models_Product;
   * $model->deleteImagesVariant(4);
   * </code>
   * @param int $productId ID товара
   * @return bool
   */
  public function deleteImagesVariant($productId) { 
    $imagesArray = array();
    // Удаляем картинки продукта из базы.
    $res = DB::query('
      SELECT image
      FROM `'.PREFIX.'product_variant` 
      WHERE product_id = '.DB::quote($productId) );
    while($row = DB::fetchAssoc($res)) {
      $imagesArray[] = $row['image'];
    }    
    $this->deleteImagesProduct($imagesArray, $productId); 
    return true;
  }
  
  /**
   * Подготавливает названия изображений товара.
   * <code>
   *   $model = new Models_Product;
   *   $res = $model->prepareImageName($product);
   *   viewData($res);
   * </code>
   * @param array $product массив с товаром
   * @return array
   */
  public function prepareImageName($product) {   
    $result = $product;

    $images = explode("|", $result['image_url']);
    foreach($images as $cell=>$image) {      
      $pos = strpos($image, 'no-img');
      if($pos || $pos === 0) {
        unset($images[$cell]);        
      } else {
        $images[$cell] = basename($image);
      }      
    }
    $result['image_url'] = implode('|', $images);
    
    foreach($result['variants'] as $cell=>$variant) {
      $images = array();
      if(empty($variant['image'])) {
        continue;
      }
      
      $pos = strpos($variant['image'], 'no-img');
      if($pos || $pos === 0) {
        unset($result['variants'][$cell]['image']);
      } else {
        if (strpos($variant['image'], DS.'thumbs'.DS)) {
          $variant['image'] = str_replace(array('thumbs'.DS.'30_', 'thumbs'.DS.'70_'), '', $variant['image']);
        }

        $images[] = basename($variant['image']);
      }
      $result['variants'][$cell]['image'] = implode('|', $images);
    }
    
    return $result;
  }
  
  /**
   * Копирует изображения товара в новую структуру хранения.
   * 
   * @param array $images - массив изображений
   * @param int $productId - id товара
   * @param string $path - папка в которой лежат исходные изображения
   * @param bool $removeOld - флаг удаления изображений из папки $path после копирования в новое место
   * @return void
   */
  public function movingProductImage($images, $productId, $path='uploads', $removeOld = true) {
    if(empty($images)) {
      return false;
    }
    
    $ds = DS;
    $dir = floor($productId/100).'00';
    @mkdir(SITE_DIR.'uploads'.$ds.'product', 0755);
    @mkdir(SITE_DIR.'uploads'.$ds.'product'.$ds.$dir, 0755);
    @mkdir(SITE_DIR.'uploads'.$ds.'product'.$ds.$dir.$ds.$productId, 0755);
    @mkdir(SITE_DIR.'uploads'.$ds.'product'.$ds.$dir.$ds.$productId.$ds.'thumbs', 0755);
    
    foreach($images as $cell=>$image) {
      $pos = strpos($image, '_-_time_-_');

      if ($pos) {
        if (MG::getSetting('addDateToImg') == 'true') {
          $tmp1 = explode('_-_time_-_', $image);
          $tmp2 = strrpos($tmp1[1], '.');
          $tmp1[0] = date("_Y-m-d_H-i-s", substr_replace($tmp1[0], '.', 10, 0));
          $imageClear = substr($tmp1[1], 0, $tmp2).$tmp1[0].substr($tmp1[1], $tmp2);
        }
        else{
          $imageClear = substr($image, ($pos+10));
        }
      }
      else{
        $imageClear = $image;
      }

      if(copy($path.$ds.$image, 'uploads'.$ds.'product'.$ds.$dir.$ds.$productId.$ds.$imageClear)) {
        
        if(copy($path.$ds.'thumbs'.$ds.'30_'.$image, 'uploads'.$ds.'product'.$ds.$dir.$ds.$productId.$ds.'thumbs'.$ds.'30_'.$imageClear) && $removeOld) {
          unlink($path.$ds.'thumbs'.$ds.'30_'.$image);
        }
        
        if(copy($path.$ds.'thumbs'.$ds.'70_'.$image, 'uploads'.$ds.'product'.$ds.$dir.$ds.$productId.$ds.'thumbs'.$ds.'70_'.$imageClear) && $removeOld) {
          unlink($path.$ds.'thumbs'.$ds.'70_'.$image);
        }
        
        if($removeOld) {
          unlink($path.$ds.$image);
        }
      }elseif(copy('uploads'.$ds.$image, 'uploads'.$ds.'product'.$ds.$dir.$ds.$productId.$ds.$imageClear)) {
        
        if(copy('uploads'.$ds.'thumbs'.$ds.'30_'.$image, 'uploads'.$ds.'product'.$ds.$dir.$ds.$productId.$ds.'thumbs'.$ds.'30_'.$imageClear) && $removeOld) {
          unlink('uploads'.$ds.'thumbs'.$ds.'30_'.$image);
        }
        
        if(copy('uploads'.$ds.'thumbs'.$ds.'70_'.$image, 'uploads'.$ds.'product'.$ds.$dir.$ds.$productId.$ds.'thumbs'.$ds.'70_'.$imageClear) && $removeOld) {
          unlink('uploads'.$ds.'thumbs'.$ds.'70_'.$image);
        }
        
        if($removeOld) {
          unlink('uploads'.$ds.$image);
        }
      }
    }
  }
  
}