<?php

/**
 * Модель: Catalog
 *
 * Класс Models_Catalog реализует логику работы с каталогом товаров.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Model
 *
 */
class Models_Catalog {

  /**
   * @var array @var mixed Массив с категориями продуктов.
   */
  public $categoryId = array();

  /**
   * @var array @var mixed Массив текущей категории.
   */
  public $currentCategory = array();

  /**
   * @var array @var mixed Фильтр пользователя..
   */
  public $userFilter = array();

  /**
   * Записывает в переменную класса массив содержащий ссылку и название текущей, открытой категории товаров.
   * <code>
   * $catalog = new Models_Catalog;
   * $catalog->getCurrentCategory();
   * </code>
   * @return bool
   */
  public function getCurrentCategory() {
    $result = false;

    $sql = '
      SELECT *
      FROM `' . PREFIX . 'category`
      WHERE id = %d
    ';

    if (end($this->categoryId)) {
      $res = DB::query($sql, end($this->categoryId));
      if ($this->currentCategory = DB::fetchAssoc($res)) {
        $result = true;
      }

    } else {
      $this->currentCategory['url'] = 'catalog';
      $this->currentCategory['title'] = 'Каталог';
      $result = true;
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__ . "_" . __FUNCTION__, $result, $args);
  }

  /**
   * Возвращает список товаров и пейджер для постраничной навигации.
   * <code>
   * $catalog = new Models_Catalog;
   * $items = $catalog->getList(6, false, true);
   * viewData($items);
   * </code>
   * @param int $countRows количество возвращаемых записей для одной страницы.
   * @param bool $mgadmin откуда вызван метод, из публичной части или панели управления.
   * @param bool $onlyActive учитывать только активные продукты.
   * @return array
   */
  public function getList($countRows = 20, $mgadmin = false, $onlyActive = false) {
    // Если не удалось получить текущую категорию.
    if (!$this->getCurrentCategory()) {
      echo 'Ошибка получения данных!';
      exit;
    }

    // только для публичной части строим html для фильтров, а если уже пришел запрос с нее, то получаем результат
    if (!$mgadmin) {

      $onlyInCount = false; // ищем все товары
      if(MG::getSetting('printProdNullRem') == "true") {
        $onlyInCount = true; // ищем только среди тех которые есть в наличии
      }
      $filterProduct = $this->filterPublic(true, $onlyInCount);
      
      MG::set('catalogfilter',$filterProduct['filterBarHtml']);

      // return array('catalogItems'=>null, 'pager'=>null, 'filterBarHtml'=>$filter->getHtmlFilter(true), 'userFilter' => $userFilter);
      // если пришел запрос с фильтра со страницы каталога и не используется плагин фильтров
      if (isset($_REQUEST['applyFilter'])) {

        $result = array();
        if (!empty($filterProduct['userFilter'])) {
          // если при генерации фильтров был построен запрос
          // по входящим свойствам товара из  get запроса
          // то получим все товары  именно по данному запросу, учитывая фильтрацию по характеристикам

          $result = $this->getListByUserFilter($countRows, $filterProduct['userFilter']);

          $result['filterBarHtml'] = $filterProduct['filterBarHtml'];
          $result['htmlProp'] = $filterProduct['htmlProp'];
          $result['applyFilterList'] = $filterProduct['applyFilterList'];
        }

        $args = func_get_args();
        return MG::createHook(__CLASS__ . "_" . __FUNCTION__, $result, $args);
      }
    }

    // Страница.
    $page = URL::get("page");

    $parts = !empty($_SESSION['filters']) ? explode('|',$_SESSION['filters']) : explode('|',MG::getSetting('filterSort'));
    $PCS = false;
    if($parts[0] == 'price_course') {
      $priceCourseSort = ',IFNULL((SELECT pv.price_course FROM '.PREFIX.'product_variant AS pv WHERE pv.product_id = p.id AND count != 0 ORDER BY pv.price_course ASC LIMIT 1), p.price_course) AS `price_course_sort`';
      $PCS = true;
    }

    $sql .= 'SELECT p.id, CONCAT(c.parent_url,c.url) as category_url, c.unit as category_unit, p.unit as product_unit,
          p.url as product_url, p.*, pv.product_id as variant_exist, rate,
          (p.price_course + p.price_course * (IFNULL(rate,0))) as `price_course`,
          IF(p.count <0, 1000000, 
            IF(varcount, 
              IF(p.count<varcount, varcount, p.count), 
            p.count)
          ) AS  `count_sort`, p.currency_iso'.$priceCourseSort.'
        FROM `' . PREFIX . 'product` AS p
        LEFT JOIN `' . PREFIX . 'category` AS c
          ON c.id = p.cat_id
        LEFT JOIN `' . PREFIX . 'product_variant` AS pv
          ON p.id = pv.product_id
        LEFT JOIN (
          SELECT pv.product_id, SUM(IF(pv.count <0, 1000000, pv.count)) AS varcount
          FROM  `' . PREFIX . 'product_variant` AS pv
          GROUP BY pv.product_id
        ) AS temp ON p.id = temp.product_id';

      // FIND_IN_SET - учитывает товары, в настройках которых,
      // указано в каких категориях следует их показывать.
      $this->currentCategory['id'] = $this->currentCategory['id']?$this->currentCategory['id']:0;
      
        if (MG::getSetting('productInSubcat')=='true') {       
          $filter = '((p.cat_id IN (' .DB::quote( implode(',', $this->categoryId),1) . ') '
          . 'or FIND_IN_SET(' . DB::quote($this->currentCategory['id'],1) . ',p.`inside_cat`)))';
        } else {
          $filter = '((c.id IN (' . DB::quote($this->currentCategory['id'],1) . ') '
          . 'or FIND_IN_SET(' .  DB::quote($this->currentCategory['id'],1)  . ',p.`inside_cat`)))';
        }  
      
        if ($mgadmin) {           
          $filter = ' (p.cat_id IN (' .DB::quote( implode(',', $this->categoryId),1) . ') '
          . 'or FIND_IN_SET(' .  DB::quote($this->currentCategory['id'],1)  . ',p.`inside_cat`))';
          
          if($this->currentCategory['id'] == 0) {
            $filter = ' 1=1 ';
          }
        }  
      // Запрос вернет общее кол-во продуктов в выбранной категории.
      if ($onlyActive) {
        $filter .= ' AND p.activity = 1';
      }
      if (MG::getSetting('printProdNullRem') == "true" && !$mgadmin) {


          $filter .= " AND (temp.`varcount` > 0 OR temp.`varcount` < 0"
            . " OR p.count>0 OR p.count<0)";
      }
      $sql .=' WHERE  ' . $filter;
   
    $orderBy = ' ORDER BY `sort` DESC ';
    if(MG::getSetting('filterSort') && !$mgadmin ) {
      $parts = !empty($_SESSION['filters']) ? explode('|',$_SESSION['filters']) : explode('|',MG::getSetting('filterSort'));     
      if (!empty($_SESSION['filters'])) {
        $parts[1] = intval($parts[1]) > 0 ? "DESC" : "ASC"; 
      }      
      $parts[0] = $parts[0]=='count' ? 'count_sort' : $parts[0];
      $orderBy = ' ORDER BY `'.DB::quote($parts[0],1).'` '.DB::quote($parts[1],1);      
    }
    if($PCS) $orderBy = str_replace('price_course', 'price_course_sort', $orderBy);
    $sql .= ' GROUP BY p.id '.$orderBy;

    // в админке не используем кэш
    if (!$mgadmin) {
      $result = Storage::get('catalog-'.md5($sql.$page.LANG.$_SESSION['userCurrency']));
    }
    
    if ($result == null) {
      // узнаем количество товаров для построения навигатора
      $res = DB::query("SELECT count(distinct p.id) AS count
        FROM ".PREFIX."product p
        LEFT JOIN ".PREFIX."category c
          ON c.id = p.cat_id
        LEFT JOIN ".PREFIX."product_variant pv
          ON p.id = pv.product_id
        LEFT JOIN (
          SELECT pv.product_id, SUM(IF(pv.count <0, 1000000, pv.count)) AS varcount
          FROM  ".PREFIX."product_variant AS pv
          GROUP BY pv.product_id
        ) AS temp ON p.id = temp.product_id WHERE ". $filter);
      $maxCount = DB::fetchAssoc($res);
      //определяем класс  
      $navigator = new Navigator($sql, $page, $countRows, 6, false, 'page', $maxCount['count']); 
      
      $this->products = $navigator->getRowsSql();

      // добавим к полученным товарам их свойства
      $this->products = $this->addPropertyToProduct($this->products, $mgadmin);   
      
      foreach ($this->products as &$item) {
        MG::loadLocaleData($item['id'], LANG, 'product', $item);
        if (!isset($item['category_unit'])) {
          $item['category_unit'] = 'шт.';
        }
        if (isset($item['product_unit']) && $item['product_unit'] != null && strlen($item['product_unit']) > 0) {
          $item['category_unit'] = $item['product_unit'];
        }
      }

      if ($mgadmin) {
        $this->pager = $navigator->getPager('forAjax');
      } else {
        $this->pager = $navigator->getPager();
      }

      $result = array('catalogItems' => $this->products, 'pager' => $this->pager, 'totalCountItems' => $navigator->getNumRowsSql());
      // в админке не используем кэш
      if (!$mgadmin) {
        Storage::save('catalog-'.md5($sql.$page.LANG.$_SESSION['userCurrency']), array('catalogItems' => $this->products, 'pager' => $this->pager, 'totalCountItems' => $navigator->getNumRowsSql()));
      }
    }

    if (!empty($filterProduct['filterBarHtml'])) {
      $result['filterBarHtml'] = $filterProduct['filterBarHtml'];
    }

    // подгружаем цены для каталога
    $ids = NULL;
    $varIds = NULL;
    foreach ($result['catalogItems'] as $key => $value) {
      $ids[] = $value['id'];
      if($value['variants']) {
        foreach ($value['variants'] as $var) {
          $varIds[] = $var['id'];
        }
      }
    }
    $res = DB::query('SELECT p.id, p.price * (IFNULL(c.rate, 0) + 1) AS price, p.price_course * (IFNULL(c.rate, 0) + 1) AS price_course FROM '.PREFIX.'product AS p
      LEFT JOIN '.PREFIX.'category AS c ON c.id = p.cat_id
      WHERE p.id IN ('.DB::quoteIN($ids).')');
    while($row = DB::fetchAssoc($res)) {
      $prices[$row['id']]['price'] = MG::numberFormat(MG::convertPrice($row['price_course']));
      $prices[$row['id']]['price_course'] = MG::numberFormat($row['price']);
    }
    $res = DB::query('SELECT pv.id, pv.price * (IFNULL(c.rate, 0) + 1) AS price, pv.price_course * (IFNULL(c.rate, 0) + 1) AS price_course, pv.product_id 
      FROM '.PREFIX.'product_variant AS pv 
      LEFT JOIN '.PREFIX.'product AS p ON p.id = pv.product_id
      LEFT JOIN '.PREFIX.'category AS c ON c.id = p.cat_id 
      WHERE pv.id IN ('.DB::quoteIN($varIds).')');
    while($row = DB::fetchAssoc($res)) {
      $varPrices[$row['product_id']][$row['id']]['price'] = MG::convertPrice($row['price']);
      $varPrices[$row['product_id']][$row['id']]['price_course'] = MG::convertPrice($row['price_course']);
    }
    foreach ($result['catalogItems'] as $key => $value) {
      $result['catalogItems'][$key]['price'] = $prices[$value['id']]['price'];
      $result['catalogItems'][$key]['price_course'] = $prices[$value['id']]['price_course'];
      if($result['catalogItems'][$key]['variants']) {
        foreach ($result['catalogItems'][$key]['variants'] as $vKey => $var) {
          $result['catalogItems'][$key]['variants'][$vKey]['price'] = $varPrices[$value['id']][$var['id']]['price'];
          $result['catalogItems'][$key]['variants'][$vKey]['price_course'] = $varPrices[$value['id']][$var['id']]['price_course'];
        }
      }
    }

    $args = func_get_args();

    return MG::createHook(__CLASS__ . "_" . __FUNCTION__, $result, $args);
  }

  /**
   * Получает список продуктов в соответствии с выбранными параметрами фильтра.
   * <code>
   * $catalog = new Models_Catalog;
   * $result = $catalog->getListByUserFilter(20, ' p.cat_id IN  (1,2,3)');
   * viewData($result);
   * </code>
   * @param int $countRows количество записей.
   * @param string $userfilter пользовательская составляющая для запроса.
   * @param bool $mgadmin админка.
   * @param bool $noCache не использовать кэш.
   * @return array
   */
  public function getListByUserFilter($countRows = 20, $userfilter, $mgadmin = false, $noCache = false) {
    if(!MG::isAdmin() || $noCache) $cache = Storage::get('catalog-'.md5(URL::getUri()));
    if(!$cache) {
      // Вычисляет общее количество продуктов.
      $page = URL::get("page");
      // в запросе меняем условие по количеству товаров в таблице product
      // затем добавляем условие по количеству вариантов и товаров
      $having = '';
      if (stristr($userfilter, 'AND (p.count>0 OR p.count<0)')!==FALSE) {
        $userfilter = str_replace('AND (p.count>0 OR p.count<0)', ' ', $userfilter);

          $having = 'HAVING(SUM(IFNULL(ABS(pv.count), 0) + ABS(p.count)) > 0)';

      }  
      if($_REQUEST['sale']) {
        $userfilter = ' p.old_price != 0 AND '.$userfilter;
      }

      $parts = !empty($_SESSION['filters']) ? explode('|',$_SESSION['filters']) : explode('|',MG::getSetting('filterSort'));
      $PCS = false;
      if($parts[0] == 'price_course') {
        $priceCourseSort = ',IFNULL((SELECT pv.price_course FROM '.PREFIX.'product_variant AS pv WHERE pv.product_id = p.id AND count != 0 ORDER BY pv.price_course ASC LIMIT 1), p.price_course) AS `price_course_sort`';
        $PCS = true;
      }

      // Запрос вернет общее кол-во продуктов в выбранной категории.
      $sql = '
        SELECT DISTINCT p.id, CONCAT(c.parent_url,c.url) AS category_url, c.unit AS category_unit, p.unit AS product_unit,
          p.url AS product_url, p.*, pv.product_id AS variant_exist, rate,
          (p.price_course + p.price_course * (IFNULL(rate,0))) AS `price_course`,
          IF(p.count <0, 1000000, 
            IF(varcount, 
              IF(p.count<varcount, varcount, p.count), 
            p.count)
          ) AS  `count_sort`, p.currency_iso,
          IF(IFNULL(c.url, "") = "" AND p.cat_id <> 0, -10, p.cat_id) AS cat_id'.$priceCourseSort.'
        FROM `' . PREFIX . 'product` p
        LEFT JOIN `' . PREFIX . 'category` c
          ON c.id = p.cat_id
        LEFT JOIN `' . PREFIX . 'product_variant` pv
          ON p.id = pv.product_id
        LEFT JOIN (
          SELECT pv.product_id, SUM(IF(pv.count <0, 1000000, pv.count)) AS varcount
          FROM  `' . PREFIX . 'product_variant` AS pv
          GROUP BY pv.product_id
        ) AS temp ON p.id = temp.product_id 
       WHERE  '.(MG::enabledStorage() ? '1=1'.$having.' AND '.$userfilter : str_replace('ORDER BY', ' GROUP BY p.id '.$having.' ORDER BY', $userfilter));


      $sql = str_replace('ORDER BY `count`', 'ORDER BY `count_sort`', $sql);
      if($PCS) $sql = str_replace('ORDER BY `price_course`', 'ORDER BY `price_course_sort`', $sql);

      $userfilterCount = explode('ORDER BY', $userfilter);
      $userfilterCount = $userfilterCount[0];

      $res = DB::query('SELECT COUNT(DISTINCT p.id) AS count
        FROM `' . PREFIX . 'product` p
        LEFT JOIN `' . PREFIX . 'category` c
          ON c.id = p.cat_id
        LEFT JOIN `' . PREFIX . 'product_variant` pv
          ON p.id = pv.product_id
        LEFT JOIN (
          SELECT pv.product_id, SUM(IF(pv.count <0, 1000000, pv.count)) AS varcount
          FROM  `' . PREFIX . 'product_variant` AS pv
          GROUP BY pv.product_id
        ) AS temp ON p.id = temp.product_id 
       WHERE '.$userfilterCount.$having);

      $count = DB::fetchAssoc($res);

      $navigator = new Navigator($sql, $page, $countRows, 6, false, 'page', $count['count']); //определяем класс.
      $this->products = $navigator->getRowsSql();
      // 
      if ($mgadmin) {
        $this->pager = $navigator->getPager('forAjax');
      } else {
        $this->pager = $navigator->getPager();
      }
      // 
      // добавим к полученным товарам их свойства
      $this->products = $this->addPropertyToProduct($this->products, $mgadmin);

      foreach ($this->products as &$item) {
        MG::loadLocaleData($item['id'], LANG, 'product', $item);
        if (!isset($item['category_unit'])) {
          $item['category_unit'] = 'шт.';
        }
        if (isset($item['product_unit']) && $item['product_unit'] != null && strlen($item['product_unit']) > 0) {
          $item['category_unit'] = $item['product_unit'];
        }
      }
      // 
      $data['products'] = $this->products;
      $data['count'] = $productCount = $navigator->getNumRowsSql();
      $data['pager'] = $this->pager;
      if(!MG::isAdmin() && MG::get('controller')!="controllers_compare" && $noCache) Storage::save('catalog-'.md5(URL::getUri()), $data);
    } else {
      $this->products = $cache['products'];
      $productCount = $cache['count'];
      $this->pager = $cache['pager'];
    }  

    // добавляем к товарам со складов инфу, если надо
    $ids = array();
    foreach ($this->products as $value) {
      $ids[] = $value['id'];
    }
    if(MG::enabledStorage()) {
      $res = DB::query('SELECT SUM(count), product_id FROM '.PREFIX.'product_on_storage WHERE product_id IN ('.DB::quoteIN($ids).')');
      while($row = DB::fetchAssoc($res)) {
        $data[$row['product_id']] = $row['SUM(count)'];
      }
    } else {
      $res = DB::query('SELECT `id`, `count` FROM `'.PREFIX.'product` WHERE `id` IN ('.DB::quoteIN($ids).')');
      while($row = DB::fetchAssoc($res)) {
        $data[$row['id']] = $row['count'];
      }
    }
    foreach ($this->products as $key => $value) {
      $this->products[$key]['count'] = empty($data[$value['id']])?0:$data[$value['id']];
    }

    // подгружаем цены для каталога
    $res = DB::query('SELECT p.id, p.price * (IFNULL(c.rate, 0) + 1) AS price, p.price_course * (IFNULL(c.rate, 0) + 1) AS price_course FROM '.PREFIX.'product AS p
      LEFT JOIN '.PREFIX.'category AS c ON c.id = p.cat_id
      WHERE p.id IN ('.DB::quoteIN($ids).')');
    while($row = DB::fetchAssoc($res)) {
      $prices[$row['id']]['price'] = MG::numberFormat(MG::convertPrice($row['price_course']));
      $prices[$row['id']]['price_course'] = $row['price_course'];
    }
    // $res = DB::query('SELECT id, price, price_course FROM '.PREFIX.'product WHERE id IN ('.DB::quoteIN($ids).')');
    // while($row = DB::fetchAssoc($res)) {
    //   $prices[$row['id']]['price'] = $row['price'];
    //   $prices[$row['id']]['price_course'] = $row['price_course'];
    // }
    foreach ($this->products as $key => $value) {
      $this->products[$key]['price'] = $prices[$value['id']]['price'];
      $this->products[$key]['price_course'] = $prices[$value['id']]['price_course'];
    }


    $result = array('catalogItems' => $this->products, 'pager' => $this->pager, 'totalCountItems' => $productCount);

    $args = func_get_args();
    return MG::createHook(__CLASS__ . "_" . __FUNCTION__, $result, $args);
  }

  /**
   * Возвращает список найденных продуктов соответствующих поисковой фразе.
   * <code>
   * $catalog = new Models_Catalog();
   * $items = $catalog->getListProductByKeyWord('Nike', true, true);
   * viewData($items);
   * </code>
   * @param string $keyword поисковая фраза.
   * @param string $allRows получить сразу все записи.
   * @param string $onlyActive учитывать только активные продукты.
   * @param bool $adminPanel запрос из публичной части или админки.
   * @param bool $mode (не используеться)
   * @param bool|int $forcedPage номер страницы использующийся вместо url
   * @param int $searchCats поиск в категории (оставить пустым если не надо искать)
   * @return array
   */
  public function getListProductByKeyWord($keyword, $allRows = false, $onlyActive = false, $adminPanel = false, $mode = false, $forcedPage = false, $searchCats = -1) {

    $result = array(
      'catalogItems' => array(),
      'pager' => null,
      'numRows' => null
    );

    $keyword = htmlspecialchars($keyword);
    $keywordUnTrim = $keyword;
    $keyword = trim($keyword);

    //if (empty($keyword) || mb_strlen($keyword, 'UTF-8') <= 2) {
    //  return $result;
   // }
    $currencyRate = MG::getSetting('currencyRate');
    $currencyShopIso = MG::getSetting('currencyShopIso');
    // Поиск по точному соответствию.
    // Пример $keyword = " 'красный',   зеленый "
    // Убираем начальные пробелы и конечные.
    $keyword = trim($keyword); //$keyword = "'красный',   зеленый"

		
	if (MG::getSetting('searchType') == 'sphinx') {
		// подключаем библиотеку для поискового движка
		require_once ("sphinxapi.php");		
    $cl = new SphinxClient();
    $cl->SetServer( MG::getSetting('searchSphinxHost'), MG::getSetting('searchSphinxPort') );
    $cl->SetConnectTimeout(1); 
    $cl->SetMaxQueryTime(1000);
    $cl->SetMatchMode(SPH_MATCH_ALL);
    $sphinxLimit = MG::getSetting('sphinxLimit');
    $cl->_limit = $sphinxLimit?$sphinxLimit:20;

		$matches = array();
		// поиск по индексам товаров и вариантов
	    $resultSphinx = $cl->Query($keyword, 'product');
        $matches = isset($resultSphinx['matches'])?$resultSphinx['matches']:array();
		// поиск по индексам характеристик
		$resultSphinx2 = $cl->Query($keyword, 'property');
		$matches = isset($resultSphinx2['matches'])? ($matches+$resultSphinx2['matches']):$matches;

	    if ( $resultSphinx === false ) {
	     if( $cl->GetLastWarning() ) { 
	      echo 'WARNING: '.$cl->GetLastWarning();
	      exit;
	     }
	     exit('Невозможно установить соединение с поисковым движком Sphinx, пожалуйста, обратитесь к администратору.');
	    }

	    foreach ($matches AS $key => $row) {
        	$idsArr[] = intval($key);
    	}

        $idsProductSphinx = join(',', $idsArr);


	} else {

	    if (MG::getSetting('searchType') == 'fulltext') {
	      // Вырезаем спец символы из поисковой фразы.
	      $keyword = preg_replace('/[`~!#$%^*()=+\\\\|\\/\\[\\]{};:"\',<>?]+/', '', $keyword); //$keyword = "красный   зеленый"
	      // Замена повторяющихся пробелов на на один.
	      $keyword = preg_replace('/ +/', ' ', $keyword); //$keyword = "красный зеленый"
	      // Обрамляем каждое слово в звездочки, для расширенного поиска.
	      $keyword = str_replace(' ', '* +', $keyword); //$keyword = "красный* *зеленый"
	      // Добавляем по краям звездочки.
	      $keyword = '+' . $keyword . '*'; //$keyword = "*красный* *зеленый*"

	      $sql = " 
	      SELECT distinct p.code, CONCAT(c.parent_url,c.url) AS category_url, c.unit as category_unit, p.unit as product_unit,
	        p.url AS product_url, p.*, pv.product_id as variant_exist, pv.id as variant_id, rate,(p.price_course + p.price_course * (IFNULL(rate,0))) as `price_course`
	      FROM  `" . PREFIX . "product` AS p
	      LEFT JOIN  `" . PREFIX . "category` AS c ON c.id = p.cat_id
	      LEFT JOIN  `" . PREFIX . "product_variant` AS pv ON p.id = pv.product_id";

	      if (!$adminPanel) {
	        $sql .=" LEFT JOIN (
	        SELECT pv.product_id, SUM( pv.count ) AS varcount
	        FROM  `" . PREFIX . "product_variant` AS pv
	        GROUP BY pv.product_id
	      ) AS temp ON p.id = temp.product_id";
	      }

	      $prod = new Models_Product();
	      $fulltext = "";
	      $sql .= " WHERE ";
	      $match =
	      " MATCH (
	      p.`title` , p.`code`, p.`description` " . $fulltextInVar . " " . $fulltext . "
	      )
	      AGAINST (
	      '" . $keyword . "'
	      IN BOOLEAN
	      MODE
	      ) ";

	      DB::query("SELECT id FROM `" . PREFIX . "product_variant` LIMIT 1");

	      //Если есть варианты товаров то будет искать и в них.
	      if (DB::numRows(DB::query("SELECT id FROM `" . PREFIX . "product_variant` LIMIT 1"))) {
	        $fulltextInVar = ', pv.`title_variant`, pv.`code` ';

	      $match = "(".$match.
	        " OR MATCH (pv.`title_variant`, pv.`code`)
	        AGAINST (
	        '" . $keyword . "'
	        IN BOOLEAN
	        MODE
	        )) ";
	      }

	    $sql .= $match;
	      // Проверяем чтобы в вариантах была хотябы одна единица.
	      if (!$adminPanel) {
	      if (MG::getSetting('printProdNullRem') == "true") {
	          $sql .=" AND (temp.`varcount` > 0 OR temp.`varcount` < 0 OR p.count>0 OR p.count<0)";
	    }
	    if(MG::getSetting('showVariantNull')=='false') {
	        $sql .= ' AND (pv.`count` != 0 OR pv.`count` IS NULL) '; 
	      }
	      }

	      if ($onlyActive) {
	        $sql .= ' AND p.`activity` = 1';
	      }
        if ($searchCats > -1) {
          $sql .= ' AND c.`id` = '.DB::quoteInt($searchCats);
        }
	    } else {

	      $sql = "
	       SELECT distinct p.id, CONCAT(c.parent_url,c.url) AS category_url, c.unit as category_unit, p.unit as product_unit,
	         p.url AS product_url, p.*, pv.product_id as variant_exist, pv.id as variant_id, rate,(p.price_course + p.price_course * (IFNULL(rate,0))) as `price_course`, 
	         p.currency_iso
	       FROM  `" . PREFIX . "product` AS p
	       LEFT JOIN  `" . PREFIX . "category` AS c ON c.id = p.cat_id
	       LEFT JOIN  `" . PREFIX . "product_variant` AS pv ON p.id = pv.product_id";

	      if (!$adminPanel) {
	        $sql .=" LEFT JOIN (
	         SELECT pv.product_id, SUM( pv.count ) AS varcount
	         FROM  `" . PREFIX . "product_variant` AS pv
	         GROUP BY pv.product_id
	       ) AS temp ON p.id = temp.product_id";
	      }

	      $prod = new Models_Product();
	      $fulltext = "";

	      $keywords = explode(" ", $keyword);
	      // foreach($keywords as $key=>$s) {
	      //   if(strlen($s)<3) unset($keywords[$key]);
	      // }
	      $keyword = "%".implode('%%', $keywords)."%";

	      //Если есть варианты товаров то будеи искать и в них.
	      if (DB::numRows(DB::query("SELECT id FROM `" . PREFIX . "product_variant` LIMIT 1"))) {

	        $fulltextInVar = " OR
	             pv.`title_variant` LIKE '%" . DB::quote($keyword, true) . "%'
	           OR
	             pv.`code` LIKE '%" . DB::quote($keyword, true) . "%'";
	      }


	      $sql .=
	        " WHERE (
	             p.`title` LIKE '%" . DB::quote($keyword, true) . "%'
	           OR
	             p.`code` LIKE '%" . DB::quote($keyword, true) . "%'
	        " . $fulltextInVar .')';


	      // Проверяем чтобы в вариантах была хотябы одна единица.
	      if (!$adminPanel) {
  	      if (MG::getSetting('printProdNullRem') == "true") {
            if(MG::enabledStorage()) {
              $sql .= ' AND ((SELECT SUM(count) FROM '.PREFIX.'product_on_storage WHERE product_id = p.id) > 0)';
            } else {
              $sql .=" AND (temp.`varcount` > 0 OR temp.`varcount` < 0 OR p.count>0 OR p.count<0)";
            }
  	      }
  	      if(MG::getSetting('showVariantNull')=='false') {
  	        $sql .= ' AND (pv.`count` != 0 OR pv.`count` IS NULL)'; 
  	      }
	      }

	      if ($onlyActive) {
          $sql .= ' AND p.`activity` = 1';
        }

        if ($searchCats > -1) {
	        $sql .= ' AND c.`id` = '.DB::quoteInt($searchCats);
	      }

	    }

	}


	if(!empty($idsProductSphinx)) {
		$sql = "SELECT distinct p.id, CONCAT(c.parent_url,c.url) AS category_url,
	         p.url AS product_url, p.*, pv.product_id as variant_exist, pv.id as variant_id, rate,(p.price_course + p.price_course * (IFNULL(rate,0))) as `price_course`, 
	         p.currency_iso
	       FROM  `" . PREFIX . "product` AS p
	       LEFT JOIN  `" . PREFIX . "category` AS c ON c.id = p.cat_id
	       LEFT JOIN  `" . PREFIX . "product_variant` AS pv ON p.id = pv.product_id
	       WHERE p.id IN(".$idsProductSphinx.")";

	}
	if(empty($sql)) {return  $result;}

    $page = URL::get("page");
    $settings = MG::get('settings');

    if ($forcedPage) {
      $page = $forcedPage;
    }

    //if ($mode=='groupBy') {
      $sql .= ' GROUP BY p.id' ;
    //}
    if ($allRows) {
      $sql .= ' LIMIT 15' ;
    }

    if ($adminPanel) {
      // $allRows = true;
      $settings['countСatalogProduct'] = $settings['countPrintRowsProduct'];
    }

    if(!$settings['countСatalogProduct']) {
       $settings['countСatalogProduct'] = 10;
    }

    $navigator = new Navigator($sql, $page, $settings['countСatalogProduct'], $linkCount = 6, $allRows); // Определяем класс.

    $this->products = $navigator->getRowsSql();

    // добавим к полученым товарам их свойства
    $this->products = $this->addPropertyToProduct($this->products, $adminPanel, false);


    foreach ($this->products as &$pitem) {
      MG::loadLocaleData($pitem['id'], LANG, 'product', $pitem);

      if (!isset($pitem['category_unit'])) {
        $pitem['category_unit'] = 'шт.';
      }
      if (isset($pitem['product_unit']) && $pitem['product_unit'] != null && strlen($pitem['product_unit']) > 0) {
        $pitem['category_unit'] = $pitem['product_unit'];
      }

    }

    $this->pager = $navigator->getPager();
 
    $result = array(
      'catalogItems' => $this->products,
      'pager' => $this->pager,
      'numRows' => $navigator->getNumRowsSql()
    );

    if (count($result['catalogItems']) > 0) {

      // упорядочивание списка найденных  продуктов
      // первыми в списке будут стоять те товары, у которых полностью совпала поисковая фраза
      // затем будут слова в начале которых встретилось совпадение
      // в конце слова в середине которых встретилось совпадение
      $keyword = str_replace('*', '', $keyword);
      $resultTemp = $result['catalogItems'];
      $prioritet0 = array();
      $prioritet1 = array();
      $prioritet2 = array();
      foreach ($resultTemp as $key => $item) {
        $title = mb_convert_case($item['title'], MB_CASE_LOWER, "UTF-8");
        $keyword = mb_convert_case($keyword, MB_CASE_LOWER, "UTF-8");
        $item['image_url'] = mgImageProductPath($item["image_url"], $item['id']);
        
        if (trim($title) == $keyword) {
        $prioritet0[] = $item;
          continue;
        }

        if (strpos($title, $keyword) === 0) {
            $prioritet1[] = $item;
          } else {
            $prioritet2[] = $item;
          }
        }

      $result['catalogItems'] = array_merge($prioritet0,  $prioritet1,$prioritet2);
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__ . "_" . __FUNCTION__, $result, $args);
  }

  /**
   * Получает массив всех категорий магазина.
   * <code>
   * $catalog = new Models_Catalog();
   * $categoryArray = $catalog->getCategoryArray();
   * viewData($categoryArray);
   * </code>
   * @return array - ассоциативный массив id => категория.
   */
  public function getCategoryArray() {
    $res = DB::query('
      SELECT *
      FROM `' . PREFIX . 'category`');
    while ($row = DB::fetchAssoc($res)) {
      $result[$row['id']] = $row;
    }
    return $result;
  }

  /**
   * Получает минимальную цену из всех стоимостей товаров (варианты тоаров не учитываются).
   * <code>
   * echo Models_Catalog::getMinPrice();
   * </code>
   * @return float
   */
  public function getMinPrice() {
    $res = DB::query('SELECT MIN(`price_course`) as price FROM `' . PREFIX . 'product`');
    if ($row = DB::fetchObject($res)) {
      $result = $row->price;
    }
    return $result;
  }

  /**
   * Получает максимальную цену из всех стоимостей товаров (варианты тоаров не учитываются).
   * <code>
   * echo Models_Catalog::getMaxPrice();
   * </code>
   * @return float
   */
  public function getMaxPrice() {
    $res = DB::query('SELECT MAX(`price_course`) as price FROM `' . PREFIX . 'product`');
    if ($row = DB::fetchObject($res)) {
      $result = $row->price;
    }
    return $result;
  }


  /**
   * Метод для обработки фильтрации товаров в каталоге.
   * <code>
   * $catalog = new Models_Catalog();
   * $result = $catalog->filterPublic();
   * viewData($result);
   * </code>
   * @param bool $noneAjax построение HTML для использования AJAX запросов. 
   * @param bool $onlyInCount учитывать только товары в наличии, 
   * @param bool $onlyActive учитывать только активные товары, 
   * @param array $sortFields массив доступных сортировок товаров.
   * @param string $baseSort сортировка по умолчанию, 
   * @return array возвращает array('filterBarHtml' => $filter->getHtmlFilter($noneAjax), 'userFilter' => $userFilter, 'applyFilterList' => $applyFilterList);
   */
  public function filterPublic($noneAjax = true, $onlyInCount = false, $onlyActive=true, $sortFields = array(
      'price_course|-1'=>'цене, сначала недорогие',
      'price_course|1'=>'цене, сначала дорогие',
      'id|1'=>'новизне',
      'count_buy|1'=>'популярности',
      'recommend|1'=>'сначала рекомендуемые',
      'new|1'=>'сначала новинки',
      'old_price|1'=>'сначала распродажа',
      'sort|-1'=>'порядку',
      'count|1'=>'наличию',
      'count|-1' => 'возрастанию количества',    
      'title|-1' => 'наименованию А-Я',
      'title|1' => 'наименованию Я-А',
       ),$baseSort = 'sort|-1') {

    if (MG::enabledStorage()) {
      unset($sortFields['count|1']);
      unset($sortFields['count|-1']);
    }
      
    
    $orderBy = strtolower(MG::getSetting('filterSort'));   
    
    $compareArray = array(
      "sort|desc" => 'sort|1',
      "sort|asc" => 'sort|-1',
      "price_course|asc" => 'price_course|-1',
      "price_course|desc" => 'price_course|1',
      "id|desc" => 'id|1',
      "count_buy|desc" => 'count_buy|1',
      "recommend|desc" => 'recommend|1',
      "new|desc" => 'new|1',
      "old_price|desc" => 'old_price|1',
      "count|desc" =>'count|1'
    );

    if (MG::enabledStorage()) {
      unset($compareArray["count_buy|desc"]);
    }
    
    if(MG::isAdmin()) {
      $sortFieldsAdmin = array(
        'id|-1' => 'сначала старые',
        'count|-1' => 'по возрастанию количества',
        'cat_id|1' => 'Категория Я-А',
        'cat_id|-1' => 'Категория А-Я',
        'title|-1' => 'Название А-Я',
        'title|1' => 'Название Я-А',
        'activity|1' => 'Сначала активные',
        'activity|-1' => 'Сначала неактивные',
      );

      if (MG::enabledStorage()) {
        unset($sortFieldsAdmin['count|-1']);
      }

      $sortFields = array_merge($sortFields, $sortFieldsAdmin);

      $compareArrayAdmin = array(
        "id|asc" => 'id|-1', 
        "count|asc" => 'count|-1',
        "cat_id|asc" => 'cat_id|-1',
        "cat_id|desc" => 'cat_id|1',
        "title|asc" => 'title|-1',
        "title|desc" => 'title|1',
      );  

      if (MG::enabledStorage()) {
        unset($compareArrayAdmin["count|asc"]);
      }   
       
      $compareArray = array_merge($compareArray, $compareArrayAdmin);
    }
    
    $baseSort = $compareArray[$orderBy]?$compareArray[$orderBy]:'sort|1';
    
    $newSortFields[$baseSort] = $sortFields[$baseSort];
    unset($sortFields[$baseSort]);      
    $sortFields = array_merge($newSortFields,$sortFields);
    $lang = MG::get('lang');
    $model = new Models_Catalog;
    $catalog = array();

    foreach ($this->categoryId as $key => $value) {
      $this->categoryId[$key] = intval($value);
    }

    if(!empty($_REQUEST['insideCat']) && $_REQUEST['insideCat']==="false") {
      $this->categoryId = array(end($this->categoryId));
    }
    
    $currentCategoryId = $this->currentCategory['id'] ? $this->currentCategory['id'] : 0;
    $where = '';
    
    if(!URL::isSection('mg-admin')) {
      $where .= ' p.activity = 1 ';   
    
      if(MG::getSetting('printProdNullRem') == "true") {

          $where .= ' AND count != 0 ';
      }
    }
    $catIds = implode(',', $this->categoryId);
        
    if (!empty($catIds)||$catIds === 0) {             
      $where1 = ' (p.cat_id IN (' . DB::quote($catIds,1) . ') or FIND_IN_SET(' . DB::quote($currentCategoryId,1) . ',p.`inside_cat`))';
      $rule1 = ' (cat_id IN (' . DB::quote($catIds,1) . ') or FIND_IN_SET(' . DB::quote($currentCategoryId,1) . ',p.`inside_cat`)) ';
      if($currentCategoryId==0) {
        $where1 = ' 1=1 or FIND_IN_SET(' . DB::quote($currentCategoryId,1) . ',p.`inside_cat`)';
        $rule1 = ' 1=1 or FIND_IN_SET(' . DB::quote($currentCategoryId,1) . ',p.`inside_cat`) ';
      } 
    } else {
      $catIds = 0;
    }
    
    if(!empty($where) || !empty($where1)) {
      $where = 'WHERE '.$where;
      if(!empty($where1)) {
        $where .= (URL::isSection('mg-admin')) ? $where1 : ' AND '.$where1;
      }
    }
    
    $prices = DB::fetchAssoc(
        DB::query('
         SELECT
          CEILING(MAX((p.price_course + p.price_course * (IFNULL(c.rate,0))))) as `max_price`,
          FLOOR(MIN((p.price_course + p.price_course * (IFNULL(c.rate,0))))) as min_price
        FROM `' . PREFIX . 'product` as p
          LEFT JOIN `' . PREFIX . 'category` as c ON
          c.id = p.cat_id '. $where));    
    $where = str_replace('AND count != 0', 'AND (pv.count != 0 OR pv.count IS NULL)', $where);
    if(MG::getSetting('showVariantNull') == "false") {
        $where = str_replace('p.activity = 1', 'p.activity = 1 AND (pv.count != 0 OR pv.count IS NULL)', $where);
      }
    $pricesVariant = DB::fetchAssoc(
        DB::query('
         SELECT
          CEILING(MAX((pv.price_course + pv.price_course * (IFNULL(c.rate,0))))) as `max_price`, 
          FLOOR(MIN((pv.price_course + pv.price_course * (IFNULL(c.rate,0))))) as `min_price`
        FROM `' . PREFIX . 'product` as p
          LEFT JOIN `' . PREFIX . 'category` as c ON
          c.id = p.cat_id 
          LEFT JOIN `'.PREFIX.'product_variant` pv ON pv.`product_id`=p.id '.$where
    ));  
    $maxPrice = max($prices['max_price']||$prices['max_price']=="0" ? $prices['max_price'] : $pricesVariant['max_price'], $pricesVariant['max_price']||$pricesVariant['max_price']=="0" ? $pricesVariant['max_price'] : $prices['max_price']);
    $minPrice = min($prices['min_price']||$prices['min_price']=="0" ? $prices['min_price'] : $pricesVariant['min_price'], $pricesVariant['min_price']||$pricesVariant['min_price']=="0" ? $pricesVariant['min_price'] : $prices['min_price']);
    $property = array(
      'cat_id' => array(
        'type' => 'hidden',
        'value' => $_REQUEST['cat_id'],
      ),

      'sorter' => array(
        'type' => 'select', //текстовый инпут
        'label' => 'Сортировать по',
      'option' => $sortFields,
        'selected' => !empty($_REQUEST['sorter']) ? $_REQUEST['sorter'] : 'null', // Выбранный пункт (сравнивается по значению)
        'value' => !empty($_REQUEST['sorter'])?$_REQUEST['sorter']:null,
      ),

      'price_course' => array(
        'type' => 'beetwen', //Два текстовых инпута
        'label1' => $lang['PRICE_FROM'],
        'label2' => $lang['PRICE_TO'],
        'min' => !empty($_REQUEST['price_course'][0]) ? $_REQUEST['price_course'][0] : $minPrice,
        'max' => !empty($_REQUEST['price_course'][1]) ? $_REQUEST['price_course'][1] : $maxPrice,
        'factMin' => $minPrice,
        'factMax' => $maxPrice,
        'class' => 'price numericProtection'
      ),

      'applyFilter' => array(
        'type' => 'hidden', //текстовый инпут
        'label' => 'флаг примения фильтров',
        'value' => 1,
      )
    );
    
    if (URL::isSection('mg-admin')) {
      $property['title'] = array(
        'type' => 'text',
        'special' => 'like',
        'label' => $lang['NAME_PRODUCT'],
        'value' => !empty($_POST['title'][0]) ? $_POST['title'][0] : null,
        );
      $property['code'] = array(
        'type' => 'text',
        'special' => 'like',
        'label' => $lang['CODE_PRODUCT'],
        'value' => !empty($_POST['code'][0]) ? $_POST['code'][0] : null,
        );
    }

    $filter = new Filter($property);
          
    $arr = array(
      'dual_condition' => array (
           array(
                !empty($_REQUEST['price_course'][0]) ? $_REQUEST['price_course'][0] : $minPrice, 
                !empty($_REQUEST['price_course'][1]) ? $_REQUEST['price_course'][1] : $maxPrice,
                '(p.price_course + p.price_course * (IFNULL(rate,0)))'
              ),
              array(
                !empty($_REQUEST['price_course'][0]) ? $_REQUEST['price_course'][0] : $minPrice, 
                !empty($_REQUEST['price_course'][1]) ? $_REQUEST['price_course'][1] : $maxPrice,
                '(pv.price_course + pv.price_course * (IFNULL(rate,0)))'
              ),
          'operator' => 'OR'
        ),
      'p.new' => (isset($_REQUEST['new'])) ? $_REQUEST['new'] : 'null',
      'p.recommend' => (isset($_REQUEST['recommend'])) ? $_REQUEST['recommend'] : 'null',
      'rule1' => $rule1,

    );    
    if (URL::isSection('mg-admin')) {
      if (isset($_REQUEST['code'])&&!empty($_REQUEST['code'][0])) {
        $rule2 = 'p.`code` LIKE ("%'.DB::quote($_REQUEST['code'][0],1).'%") or pv.`code` LIKE ("%'.DB::quote($_REQUEST['code'][0],1).'%")  ';
        $arr['rule2'] = $rule2;
      }     
      if (isset($_REQUEST['title'])&&!empty($_REQUEST['title'][0])) {
        $rule3 = 'p.`title` LIKE ("%'.DB::quote($_REQUEST['title'][0],1).'%") or pv.`title_variant` LIKE ("%'.DB::quote($_REQUEST['title'][0],1).'%")  ';
        $arr['rule3'] = $rule3;
      }  
    }
    $userFilter = $filter->getFilterSql($arr, array(), $_REQUEST['insideCat']);

    // отсеивание фильтра ползунка, если его не настраивали
    foreach ($_REQUEST['prop'] as $id => $property) {
      if(in_array($property[0], array('slider|easy', 'slider|hard'))) {
        if($property[1] == '') {
          unset($_REQUEST['prop'][$id]);
          continue;
        }
        if($property[2] == '') {
          unset($_REQUEST['prop'][$id]);
          continue;
        }
        // проверка значений на дефолтность
        $type = explode('|', $property[0]);
        $type = $type[1];
        // if($type == 'easy') {
          unset($tmp);
          if(mg::getSetting('printProdNullRem') == 'true') {
            $checkCountP1 = '
              LEFT JOIN '.PREFIX.'product_user_property_data AS pupd ON pupd.prop_data_id = pd.id 
              LEFT JOIN '.PREFIX.'product AS p ON p.id = pupd.product_id';
            $checkCountP2 = ' AND (ABS(p.count) + IFNULL(0, (SELECT SUM(ABS(pv.count)) FROM '.PREFIX.'product_variant AS pv WHERE pv.product_id = p.id))) > 0';
          }
          $res = DB::query('SELECT DISTINCT pd.name FROM '.PREFIX.'property_data AS pd '.$checkCountP1 .' WHERE pd.prop_id = '.DB::quoteInt($id).$checkCountP2);
          while($row = DB::fetchAssoc($res)) {
            $t = str_replace(',', '.', $row['name']);
            if(is_numeric($t)) $tmp[] = $t;
          }
          if(($property[1] == min($tmp))&&($property[2] == max($tmp))) {
            unset($_REQUEST['prop'][$id]);
            continue;
          }
        // }
      }
    }

    // проерка значений фильтра на их наличие
    $propFilterCounter = 0;
    foreach ($_REQUEST['prop'] as $id => $property) {
      foreach ($property as $cnt=>$value) {
        if($value != '') {
          $propFilterCounter++;
        }
      }
    }

    if(!empty($_REQUEST['prop']) && ($propFilterCounter != 0)) {
      if (!empty($_REQUEST['insideCat'])&&$_REQUEST['insideCat']=='true') {
        $catIdsFilter = $this->categoryId;
      } else {
        $catIdsFilter = $this->currentCategory['id'];
      }
      $arrayIdsProd = $filter->getProductIdByFilter($_REQUEST['prop'], str_replace('AND count != 0', 'AND ABS(IFNULL( pv.`count` , 0 ) ) + ABS( p.`count` ) >0', $where)) ;
      $listIdsProd = implode(',',$arrayIdsProd);
      if($listIdsProd != '') {
        if(strlen($userFilter) > 0) {
          $userFilter .= ' AND ';
        }
        $userFilter .= ' p.id IN ('.$listIdsProd.') ';
      } else {
        // добавляем заведомо неверное  условие к запросу,
        // чтобы ничего не попало в выдачу, т.к. товаров отвечающих заданым характеристикам ненайдено
        $userFilter = ' 0 = 1 ';
      }
    }

    $keys = array_keys($sortFields);
    if(empty($_REQUEST['sorter'])) {
      $_REQUEST['sorter'] = $keys[0];
    } elseif(!URL::isSection('mg-admin') && !in_array($_REQUEST['sorter'], $keys)) {
      $_REQUEST['sorter'] = $keys[0];
    }

    if(!empty($_REQUEST['sorter']) && !empty($userFilter)) {
      $sorterData = explode('|', $_REQUEST['sorter']);
      $field = $sorterData[0];
      if ($sorterData[1] > 0) {
        $dir = 'desc';
      } else {
        $dir = 'asc';
      }

      if ($onlyInCount) {
        $userFilter .= ' AND (p.count>0 OR p.count<0)';
      }

      if ($onlyActive) {
        $userFilter .= ' AND p.`activity` = 1';
      }

      if(!empty($userFilter)) {
        $userFilter .= " ORDER BY `".DB::quote($field, true)."`  ".$dir;
      }
    }

    $applyFilterList = $filter->getApplyFilterList();
    if(MG::isAdmin()) {
      return array('filterBarHtml' => $filter->getHtmlFilterAdmin($noneAjax), 'userFilter' => $userFilter, 'applyFilterList' => $applyFilterList);
    } else {
      $result = array('filterBarHtml' => $filter->getHtmlFilter($noneAjax), 'userFilter' => $userFilter, 'applyFilterList' => $applyFilterList,
        'htmlProp' => $filter->getHtmlPropertyFilter());
      $args = func_get_args();
      return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
    }
  }



  /**
   * Метод добавляет к массиву продуктов информацию о характеристиках
   * для каждого продукта.
   * <code>
   * $catalog = new Models_Catalog;
   * $products = $catalog->addPropertyToProduct($products);
   * </code>
   * @param array $arrayProducts массив с продуктами
   * @param bool $mgadmin если из админки
   * @param bool $changePic заменять изображение
   * @return array
   */
  public function addPropertyToProduct($arrayProducts, $mgadmin = false, $changePic = true) {
    if(empty($arrayProducts)) {
      return $arrayProducts;
    }    
    
    $categoryIds = array();
    $whereCat = '';
    $idsProduct = array();
    $currency = MG::getSetting("currency");
    $currencyRate = MG::getSetting('currencyRate');
    $currencyShopIso = MG::getSetting('currencyShopIso');
    $prod = new Models_Product();
    $idsVariantProduct = array();
   
    foreach ($arrayProducts as $key => $product) {
      $change = true;
      $arrayProducts[$key]['category_url'] = (MG::getSetting('shortLink') == 'true'&&(!URL::isSection('mg-admin')&&!URL::isSection('mgadmin')) ? '' : $arrayProducts[$key]['category_url'].'/');
      $arrayProducts[$key]['category_url'] = ($arrayProducts[$key]['category_url'] == '/' ? '' : $arrayProducts[$key]['category_url']);
      $product['category_url'] = (MG::getSetting('shortLink') == 'true' ? '' : $product['category_url'].'/');
      $product['category_url'] = ($product['category_url'] == '/' ? '' : $product['category_url']);
      $idsProduct[$product['id']] = $key;
      $categoryIds[] = $product['cat_id'];
      // Назначаем для продукта пользовательские
      // характеристики по умолчанию, заданные категорией.
   

      $arrayProducts[$key]['currency'] = $currency;
      // Формируем ссылки подробнее и в корзину.		
      $arrayProducts[$key]['actionBuy'] = MG::layoutManager('layout_btn_buy', $product);	 
      $arrayProducts[$key]['actionView'] =  MG::layoutManager('layout_btn_more', $product);
	  
	  
      $arrayProducts[$key]['link'] = (MG::getSetting('shortLink') == 'true' ? SITE.'/'.$product["product_url"] : SITE.'/'.(isset($product["category_url"])&&($product["category_url"]!='') ? $product["category_url"] : 'catalog/').$product["product_url"]);
      if (empty($arrayProducts[$key]['currency_iso'])) {
        $arrayProducts[$key]['currency_iso'] = $currencyShopIso;
      }
	  
	  
      $arrayProducts[$key]['real_old_price'] = $arrayProducts[$key]['old_price'];


      // $arrayProducts[$key]['old_price'] = round($arrayProducts[$key]['old_price'],2);
      $arrayProducts[$key]['real_price'] = $arrayProducts[$key]['price'];


      $arrayProducts[$key]['price'] = MG::priceCourse($arrayProducts[$key]['price_course']);
      
      $imagesConctructions = $prod->imagesConctruction($arrayProducts[$key]['image_url'],$arrayProducts[$key]['image_title'],$arrayProducts[$key]['image_alt'], $product['id']);
      $arrayProducts[$key]['images_product'] = $imagesConctructions['images_product'];
      $arrayProducts[$key]['images_title'] = $imagesConctructions['images_title'];
      $arrayProducts[$key]['images_alt'] = $imagesConctructions['images_alt'];
      $arrayProducts[$key]['image_url'] = $imagesConctructions['image_url'];
      $arrayProducts[$key]['image_title'] = $imagesConctructions['image_title'];
      $arrayProducts[$key]['image_alt'] = $imagesConctructions['image_alt'];

      $imagesUrl = explode("|", $arrayProducts[$key]['image_url']);
      $arrayProducts[$key]["image_url"] = "";
      if (!empty($imagesUrl[0])) {
        $arrayProducts[$key]["image_url"] = $imagesUrl[0];
      }

    }

    $model = new Models_Product();
    $arrayVariants = $model->getBlocksVariantsToCatalog(array_keys($idsProduct), true, $mgadmin);

    foreach (array_keys($idsProduct) as $id) {
      $arrayProducts[$idsProduct[$id]]['variants'] = $arrayVariants[$id];
    }

    foreach ($arrayProducts as $key => $value) {
      if (!empty($arrayProducts[$key]['variant_exist'])) {
        $arrayProducts[$key]['real_old_price'] = $arrayProducts[$key]['old_price'];
        $arrayProducts[$key]['real_price'] = MG::priceCourse($arrayProducts[$key]['price_course']);
        if ($arrayProducts[$key]['count'] == 0) {
          $arrayProducts[$key]['actionBuy'] = $arrayProducts[$key]['actionView'];
        }
        if (!empty($value['variants'])) {
          foreach ($value['variants'] as $key2 => $value2) {
            

            $arrayProducts[$key]['variants'][$key2]['price'] = MG::priceCourse($arrayProducts[$key]['variants'][$key2]['price']);
          }
        }
      }    }

    // Собираем все ID продуктов в один запрос.
    if ($prodSet = trim(DB::quote(implode(',', array_keys($idsProduct))), "'")) {
      // Формируем список id продуктов, к которым нужно найти пользовательские характеристики.
      $where = ' IN (' . $prodSet . ') ';
    } else {
      $where = ' IN (0) ';
    }

    //Определяем id категории, в которой находимся
    $catCode = URL::getLastSection();


    return $arrayProducts;
  }

}