<?php

/**
 * Класс Filter - конструктор для фильтров. Создает фильтры по полям таблиц в базе. Используется преимущественно в панели управления. Также отвечает за вывод фильтра по цене и характеристикам в публичной части.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */
class Filter {

  // Массив категорий.
  private $categories;
  private $property;

  public function __construct($property) {
    $this->property = $property;
  }

  /**
   * Получает примерно такой массив.
   *  $data = array(
   *    'category' => '2',
   *    'price'=>array(10,100),
   *    'code'=> 'ABC',
   *    'rows'=> 20,
   *  );
   * @param array $data - массив параметров по фильтрам
   * @param array $sorter - массив содержащий поле, и направление сортировки
   *              $sorter = array('id', 'asc' );
   * по которому следует отсортировать выборку например ID и направление сортировки
   * 
   * @param bool $insideCat - если true, то учитывать вложенные категории
   * @return string - часть запроса  WHERE
   */

  public function getFilterSql($data, $sorter = array(), $insideCat = true) {
    
    // удаляем возможный мусор от движка
    unset($data['mguniqueurl']);
    unset($data['mguniquetype']);

    $where = "[START]";

    // начинаем формировать условие
    foreach ($data as $k => $v) {
	 
      // значение фильтра обязательно должно быть не пустым
      if((!empty($v) && $v != 'null') || $v === 0 || $v === '0') {

        // если значением элемента передана часть запроса
        // 'rule1' => 'sql код'
        // 'rule2' => 'sql код'
        if(substr(mb_strtolower($k),0,4) ==  'rule') {
          $where .= " AND (".$v.")";
          continue;
        }        
        
        if(is_array($v) && empty($v[0])) {
          continue;
        }
        
        $devide = ' = ';
        
        // если в special параметре указан оператор like
        if(is_array($v) && count($v) == 2 && $v[1]=='like') {
          $devide = ' like ';
          $v = DB::quote('%'.$v[0].'%');
        }  // если в значении передан массив двух значений, значит будет моделироваться оператор BETWEEN
        elseif(is_array($v) && count($v) >= 2) {    
          // для массива с параметром dual_condition строится условие с оператором BETWEEN с одинаковыми мин и макс занчениями,
          // и разными параметрами  - применяется для минимальной и максимальной цены для товаров и вариантов товаров.
          if(substr(mb_strtolower($k),0,14) ==  'dual_condition') {
            if((empty($v[0][0]) && $v[0][0] !==0 && $v[0][0] !== '0') || (empty($v[0][1])&& $v[0][1] !==0 && $v[0][1] !== '0' )||
             ( empty($v[1][0]) && $v[1][0] !=0 && $v[1][0] != '0') || (empty($v[1][1])&& $v[1][1] !=0 && $v[1][1] != '0') ) {
                continue;
              } 
            $v1 = DB::quote($v[0][0])." AND ".DB::quote($v[0][1]);
            $v2 = DB::quote($v[1][0])." AND ".DB::quote($v[1][1]);      
            $devide = ' BETWEEN ';
            $where.= " AND ( (".$v[0][2].$devide.$v1.")".$v['operator']." (".$v[1][2].$devide.$v2.") )";
              continue;
            } 
          //минимальное и максимальное значение обязательно должны быть заполнены
          if(empty($v[0]) && $v[0] !=0 && $v != '0' || empty($v[1])) {
            continue;
          }  
          $devide = ' BETWEEN ';
          if(!empty($v[2]) && $v[2] == 'date') {          
            $v = DB::quote(date('Y-m-d 00:00:00', strtotime($v[0])))." AND ".DB::quote(date('Y-m-d 23:59:59', strtotime($v[1])));
          } else {
            // экранируем данные
            $v = DB::quote($v[0])." AND ".DB::quote($v[1]+1);
          }
        } else {
          $v = DB::quote($v);
        }
     
        if($k != 'cat_id') {
		
          $where.=" AND ( ".DB::quote($k,1).$devide.$v.") ";
        }
   
      }
    }

    // удаляем первый AND
    $where = str_replace("[START] AND", " ", $where);
    if($where == "[START]") {
      $where = '';
    }

    //сортировка по полю
    if(!empty($sorter)) {
      if(!empty($sorter[0])) {
        if($sorter[1] > 0) {
          $sorter[1] = 'asc';
        } else {
          $sorter[1] = 'desc';
        }

        $incorrectParam = false;
        if(strpos($sorter[0], "'")===0 || strpos($sorter[0], '"')===0 || strpos($sorter[1], "'")===0 || strpos($sorter[1], '"')===0) {
          $incorrectParam = true;
        }
  
        if(empty($where)||$incorrectParam) {
          $where = " 1 = 1 ";
        }
        $where .= " ORDER BY ".DB::quote($sorter[0],1)." ".DB::quote($sorter[1],1);
      }
    }

    return $where;
  }

  /**
   * Возвращает HTML верстку блока с фильтрами по каталогу товаров. 
   * <code>
   *  $filter = new Filter();
   *  $res = $filter->getHtmlFilter();
   *  viewData($res);
   * </code>
   * @param array $submit флаг, для вывода кнопки отправки формы.
   * @return string - HTML верстка.
   */
  public function getHtmlFilter($submit = false) {
    $data['submit'] = $submit;
    $data['property'] = $this->property;


    return MG::layoutManager('layout_filter', $data);
  }

  /**
   * Возвращает HTML верстку блока с фильтрами по каталогу товаров (для панели администратора).
   * <code>
   *  $filter = new Filter();
   *  $res = $filter->getHtmlFilterAdmin();
   *  viewData($res);
   * </code>
   * @param array $submit флаг, для вывода кнопки отправки формы.
   * @return string - HTML верстка.
   */
    public function getHtmlFilterAdmin($submit = false) {
      $html = '<div class="row">'; 
      $lang = MG::get('lang');      

      // если это секциями с товарами, то начало формы выводиться в верстке страницы
      if($section != 'catalog') {
        $arReuestUrl = parse_url($_SERVER['REQUEST_URI']);
        $formStart = '<form name="filter" class="filter-form" action="'.$arReuestUrl['path'].'" data-print-res="'.MG::getSetting('printFilterResult').'">';
      }
      
      $countProp = -1;

      // перебор характеристик и в зависимости от типа строится соответсвующий html код
      foreach ($this->property as $name => $prop) {
        if(MG::get('controller')!='controllers_catalog' || $_REQUEST['mguniqueurl'] != 'catalog.php') {
          $countProp++;
          if($countProp > 1) {
            $countProp = 0;
            $html .= '</div><div class="row">';
          }
        }

        switch ($prop['type']) {
          case 'select': {
              if(!URL::isSection("mg-admin") && $name == 'sorter' && !empty($_SESSION['filters'])) {
                $prop['selected'] = $_SESSION['filters'];
                $prop['value'] = $_SESSION['filters'];
              }
              $html .= '<div class="large-6 columns">
                          <div class="row">
                            <div class="small-4 medium-5 columns">
                              <label class="middle dashed">'.$prop['label'].':</label>
                            </div>
                            <div class="small-8 medium-7 columns">
                              <select class="no-search" name="'.$name.'">';         
              foreach ($prop['option'] as $value => $text) {
                $selected = ($prop['selected'] === $value."") ? 'selected="selected"' : '';
                $html .= '<option value="'.$value.'" '.$selected.'>'.$text.'</option>';
              }
              $html .= '</select></div>';
              if($name =! 'cat_id') {
                $checked = '';
                if($_POST['insideCat']) {
                  $checked = 'checked=checked';
                }
                $html .= '<div class="checkbox">'.$lang['FILTR_PRICE7'].'<input type="checkbox"  name="insideCat" '.$checked.' /></div>';
              }
              $html .= '</div></div>';
              
              break;
            }

          case 'beetwen': {
              if($prop['special'] == 'date') {
                $html .= '
                        <div class="large-6 columns">    
                          <div class="row">
                            <div class="small-4 large-5 medium-5 columns">
                              <div class="wrapper-field range-field">
                                <div class="price-slider-wrapper input-range dashed">
                                  <div class="text-side"><span class="text">'.$prop['label0'].' '.$prop['label1'].':</span></div>
                                </div>
                              </div>
                            </div>
                            <div class="small-8 large-7 medium-7 columns">
                              <div class="input-side">
                                <div class="input-line input-group">
                                  <input class="input-group-field from-'.$prop['class'].'" type="text" name="'.$name.'[]" value="'.date('d.m.Y', strtotime($prop['min'])).'">
                                  <span class="text input-group-label">'.$prop['label2'].'</span>
                                  <input class="input-group-field to-'.$prop['class'].'" type="text" name="'.$name.'[]" value="'.date('d.m.Y', strtotime($prop['max'])).'">
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>';
                } else {
                  $html .= '
                          <div class="large-6 columns">    
                            <div class="row">
                              <div class="small-4 large-5 medium-5 columns">
                                <div class="wrapper-field range-field">
                                  <div class="price-slider-wrapper input-range dashed">
                                    <div class="text-side" style="width:100%;"><span class="text">'.$prop['label1'].'</span></div>
                                  </div>
                                </div>
                              </div>
                              <div class="small-8 large-7 medium-7 columns">
                                <div class="input-side">
                                  <div class="input-line input-group">
                                    <input type="text" id="minCost" class="input-group-field price-input start-'.$prop['class'].'  price-input" data-fact-min="'.$prop['factMin'].'" name="'.$name.'[]" value="'.$prop['min'].'" />
                                    <span class="text input-group-label">'.$prop['label2'].'</span>
                                    <input type="text" id="maxCost" class="input-group-field price-input end-'.$prop['class'].'  price-input" data-fact-max="'.$prop['factMax'].'" name="'.$name.'[]" value="'.$prop['max'].'" />
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>';

                if(!empty($prop['special'])) {
                  $html .= '<input type="hidden"  name="'.$name.'[]" value="'.$prop['special'].'" />';
                }
              }
              break;
            }

          case 'hidden': {
              $html .= ' <input type="hidden" name="'.$name.'" value="'.$prop['value'].'" class="price-input"/>';
              $countProp--;
              break;
            }

          case 'text': {
                if(!empty($prop['special'])) {
                  $html .= '
                          <div class="large-6 columns">
                            <div class="row">
                              <div class="small-4 medium-5 columns">
                                <label class="middle dashed">'.$prop['label'].':</label>
                              </div>
                              <div class="small-8 medium-7 columns">
                                <input type="text" name="'.$name.'[]" value="'.$prop['value'].'" class="price-input" style="width:100%"/>
                                <input type="hidden"  name="'.$name.'[]" value="'.$prop['special'].'" />
                              </div>
                            </div>
                          </div>';
                } else {
                  $html .= '
                          <div class="large-6 columns">
                            <div class="row">
                              <div class="small-4 medium-5 columns">
                                <label class="middle dashed">'.$prop['label'].':</label>
                              </div>
                              <div class="small-8 medium-7 columns">
                                <input type="text" name="'.$name.'" value="'.$prop['value'].'" class="price-input"/>
                              </div>
                            </div>
                          </div>';
                }
              
              break;
            }

          default:
            break;
        }
      }
      // фикс улетания столбца в правую часть
      if(MG::get('controller')!='controllers_catalog' || $_REQUEST['mguniqueurl'] != 'catalog.php') {
        $html .= '<div class="large-'.((2-$countProp)*6).' columns"></div>';
      }
      $html .= '</div>';
      
      if(MG::get('controller')=='controllers_catalog' || $_REQUEST['mguniqueurl'] == 'catalog.php') {
        $html .= '<div class="mg-filter-body"><div class="row" style="margin:20px 20px 0 20px;">';
          

        $html .= '</div></div>';
      }
      if(MG::get('controller')=='controllers_users' || $_REQUEST['mguniqueurl'] == 'users.php') {
        $html .= '<div class="mg-filter-body">';
       
        $html .= '</div>';
      }
     
      $html .= '<div class="actions-panel">
                  <div class="actions text-right">';
    if($submit) {
      $html .= '<input type="submit" value="'.$lang['FILTR_PRICE8'].'" class="filter-btn">';
      $html .= '<a href="'.SITE.URL::getClearUri().'" class="refreshFilter"><span>'.$lang['CLEAR'].'</span></a>'; 
    } else {
      $html .= '<a class="button filter-now" href="javascript:void(0);"><i class="fa fa-filter" aria-hidden="true"></i> '.$lang['FILTR_PRICE8'].'</a>
                <a class="button secondary refreshFilter" href="javascript:void(0);"><i class="fa fa-times" aria-hidden="true"></i> '.$lang['CLEAR'].'</a>
                <a class="button secondary csvExport" href="javascript:void(0);"><i class="fa fa-upload"></i>Выгрузить в CSV по фильтру</a>';
    }
      
    $html .= '</div></div>';
    
    $arReuestUrl = parse_url($_SERVER['REQUEST_URI']);
    
    return $formStart.str_replace(array('[', ']'), array('&#91;', '&#93;'), $html).'</form>';
  }


  /**
   * Строит HTML верстку для фильтра по характеристикам.
   * <code>
   *  $_REQUEST['category_id'] = 1;
   *  $filter = new Filter();
   *  $res = $filter->getHtmlPropertyFilter();
   *  viewData($res);
   * </code>
   * @return string html верстка чекбоксов характеристик.
   */
  public function getHtmlPropertyFilter() { 
    $times = microtime(true);
    $property = array();   
    $_REQUEST['category_id'] = intval($_REQUEST['category_id'])?intval($_REQUEST['category_id']):intval($_REQUEST['cat_id']);
    // каталог фильтры корень опционально
    if(MG::getSetting('filterCatalogMain') != "true") if($_REQUEST['category_id'] === 0) return false;
    $cacheRowName = 'filterProperty'.$_REQUEST['category_id'];
      
    if(MG::isAdmin()) {
      $cacheRowName = 'mgadmin_'.$cacheRowName;
    }
     
    $property = Storage::get('propFilter-'.md5($cacheRowName));   
    
    if($property == null) {  
      $property = $this->getPropertyData(true);
      Storage::save('propFilter-'.md5($cacheRowName),$property);
    }

    // MG::loger($_REQUEST['category_id']);
    // MG::loger('propPieces-'.md5('propPieces'.$_REQUEST['category_id'].LANG.json_encode($_REQUEST['prop']))); 
    if(!MG::isAdmin()) $propsPieces = Storage::get('propPieces-'.md5('propPieces'.$_REQUEST['category_id'].LANG.json_encode($_REQUEST['prop'])));
    // $propsPieces = Storage::get('propPieces-'.md5('propPieces'.json_encode($_REQUEST).LANG.json_encode($_REQUEST['prop'])));

    $categorieIdsAll = $_REQUEST['category_ids'];

    if(!$propsPieces) {
      $html = "";
      $allFilter = "";
      $propsPieces = array();
      $propCount = 0;
      // приводим к одному виду все значения характеристик в выбранных фильтрах заменяем 
      // HTML сущности на мнемоники, для последующего сравнения.
      // этот цикл является костылем, т.к. данные в паблике и админке отличаются. 
      // Если его убрать фильтр будет корректно работать только в паблике
      foreach ($_REQUEST['prop'] as $idProp => $prop) {
        foreach ($_REQUEST['prop'][$idProp] as $key => $val) {
          $valDecode = htmlspecialchars_decode($val);
          $valEncode = htmlspecialchars($valDecode);
          $_REQUEST['prop'][$idProp][$key] = $valEncode;
        }
      }

      // получаем категории
      if(MG::getSetting('filterSubcategory') == 'true') {
        $categories = MG::get('category')->getCategoryList($_REQUEST['category_id']);
        $categories[] = $_REQUEST['category_id'];
        $categories = implode(',', $categories);
      } else {
        $categories = $_REQUEST['category_id'];
      }
// mg::loger($_REQUEST);
// mg::loger(MG::get('productFindedByFilter'));
      // id товаров, которые были найдены по фильтрам
      if(!$prductIds = implode(',', MG::get('productFindedByFilter'))) {
        $prductIds = '';
      }
      // viewdata(microtime(true) - $times);
      // mg::loger($property);
      foreach ($property as $idProp => $prop) {
        $tmp = explode('[', $prop['name']);
        $prop['name'] = $tmp[0];
        $propPieces = array();
        $prop['data'] = array();
        if(MG::getSetting('printProdNullRem') == 'true') {
          if(MG::enabledStorage()) {
            $storageCheck = ' AND (SELECT SUM(ABS(count)) FROM '.PREFIX.'product_on_storage WHERE product_id = p.id) > 0';
          } else {
            $storageCheck = ' AND (SELECT IF(SUM(ipv.count) != 0, ip.count + SUM(ipv.count), ip.count) FROM '.PREFIX.'product AS ip 
              LEFT JOIN '.PREFIX.'product_variant AS ipv ON ip.id = ipv.product_id WHERE ip.id = p.id) != 0';
          }
        }
        if(MG::getSetting('disabledPropFilter') != 'false') {
          if($prductIds != '') {
            $ifSelect = ", IF(p.id IN (".DB::quoteIN($prductIds)."), '0', '1') AS disabled";
            $disabledPart = ', disabled DESC';
          } else {
            $ifSelect = '';
            $disabledPart = '';
          }
        }
        if($prop['type'] != 'string') {
          if(MG::getSetting('printProdNullRem') == 'true') {
            if(MG::enabledStorage()) {
              $storageCheck = ' AND (SELECT SUM(ABS(count)) FROM '.PREFIX.'product_on_storage WHERE product_id = p.id) > 0';
            } else {
              $storageCheck = ' AND (SELECT IF(SUM(ipv.count) != 0, ip.count + SUM(ipv.count), ip.count) FROM '.PREFIX.'product AS ip 
                LEFT JOIN '.PREFIX.'product_variant AS ipv ON ip.id = ipv.product_id WHERE ip.id = p.id) != 0';
            }
          }
          $res = DB::query("SELECT pd.* ".$ifSelect."
            FROM ".PREFIX."property_data AS pd 
            LEFT JOIN ".PREFIX."product_user_property_data AS pupd ON pd.id = pupd.prop_data_id
            LEFT JOIN ".PREFIX."product AS p ON p.id = pupd.product_id
            WHERE pd.`prop_id` = ".DB::quote($idProp).' AND pupd.active = 1  AND product_id IN 
                (SELECT id FROM '.PREFIX.'product WHERE cat_id IN ('.DB::quoteIN($categorieIdsAll).') 
                OR FIND_IN_SET('.$_REQUEST['category_id'].', inside_cat))
                '.$storageCheck.$storageCheckVar.'
            ORDER BY sort ASC'.$disabledPart);
          while($userFieldsData = DB::fetchAssoc($res)) {
            // проверка вариантов товаров на наличие
            if(MG::getSetting('showVariantNull') != 'true') {
              if(MG::enabledStorage()) {
                if($prop['type'] == 'color') {
                  $resIn = DB::query('SELECT SUM(ABS(pos.count)) AS pcount
                    FROM '.PREFIX.'product_on_storage AS pos
                    LEFT JOIN '.PREFIX.'product_variant AS pv ON pos.variant_id = pv.id 
                    LEFT JOIN '.PREFIX.'product AS p ON p.id = pv.product_id
                    WHERE pv.color = '.DB::quoteInt($userFieldsData['id']).'
                    AND (p.cat_id IN ('.DB::quoteIN($_REQUEST['category_id']).') OR FIND_IN_SET('.DB::quoteInt($_REQUEST['category_id']).', p.inside_cat))');
                  $rowIn = DB::fetchAssoc($resIn);
                  if($rowIn['pcount'] == 0) continue;
                }
                if($prop['type'] == 'size') {
                  $resIn = DB::query('SELECT SUM(ABS(pos.count)) AS pcount 
                    FROM '.PREFIX.'product_on_storage AS pos
                    LEFT JOIN '.PREFIX.'product_variant AS pv ON pos.variant_id = pv.id 
                    LEFT JOIN '.PREFIX.'product AS p ON p.id = pv.product_id
                    WHERE pv.size = '.DB::quoteInt($userFieldsData['id']).'
                    AND (p.cat_id IN ('.DB::quoteIN($_REQUEST['category_id']).') OR FIND_IN_SET('.DB::quoteInt($_REQUEST['category_id']).', p.inside_cat))');
                  $rowIn = DB::fetchAssoc($resIn);
                  if($rowIn['pcount'] == 0) continue;
                }
              } else {
                if($prop['type'] == 'color') {
                  $resIn = DB::query('SELECT SUM(ABS(pv.count)) AS pcount
                    FROM '.PREFIX.'product_variant AS pv 
                    LEFT JOIN '.PREFIX.'product AS p ON p.id = pv.product_id
                    WHERE pv.color = '.DB::quoteInt($userFieldsData['id']).'
                    AND (p.cat_id IN ('.DB::quoteIN($_REQUEST['category_id']).') OR FIND_IN_SET('.DB::quoteInt($_REQUEST['category_id']).', p.inside_cat))');
                  $rowIn = DB::fetchAssoc($resIn);
                  if($rowIn['pcount'] == 0) continue;
                }
                if($prop['type'] == 'size') {
                  $resIn = DB::query('SELECT SUM(ABS(pv.count)) AS pcount 
                    FROM '.PREFIX.'product_variant AS pv
                    LEFT JOIN '.PREFIX.'product AS p ON p.id = pv.product_id
                     WHERE pv.size = '.DB::quoteInt($userFieldsData['id']).'
                     AND (p.cat_id IN ('.DB::quoteIN($_REQUEST['category_id']).') OR FIND_IN_SET('.DB::quoteInt($_REQUEST['category_id']).', p.inside_cat))');
                  $rowIn = DB::fetchAssoc($resIn);
                  if($rowIn['pcount'] == 0) continue;
                }
              }
            }

            if(MG::getSetting('disabledPropFilter') == 'false') {
              $userFieldsData['disabled'] = 0;
            }
            $prop['data'][$userFieldsData['name']] = $userFieldsData;

          }
        }
        
        foreach ($prop['data'] as $key => $value) {
          MG::loadLocaleData($value['id'], LANG, 'property_data', $value);
          $prop['data'][$key] = $value;
        }
        unset($tmp);

        MG::loadLocaleData($prop['id'], LANG, 'property', $prop);
        if(MG::getSetting('printProdNullRem') == 'true') {
          if(MG::enabledStorage()) {
            $storageCheck = ' AND (SELECT SUM(ABS(count)) FROM '.PREFIX.'product_on_storage WHERE product_id = pupd.product_id) > 0';
          } else {
            $storageCheck = ' AND (SELECT IF(SUM(ipv.count) != 0, ip.count + SUM(ipv.count), ip.count) FROM '.PREFIX.'product AS ip 
              LEFT JOIN '.PREFIX.'product_variant AS ipv ON ip.id = ipv.product_id WHERE ip.id = pupd.product_id) != 0';
          }
        }
        // viewdata(microtime(true) - $times);
        // если пусто, подгружаем параметры товаров
        if(empty($prop['data'])) { 
          $storageCheck = '';
          if(MG::getSetting('printProdNullRem') == 'true') {
            if(MG::enabledStorage()) {
              $storageCheck = ' AND (SELECT SUM(ABS(count)) FROM '.PREFIX.'product_on_storage WHERE product_id = pupd.product_id) > 0';
            } else {
              $storageCheck = ' AND (SELECT IF(SUM(ipv.count) != 0, ip.count + SUM(ipv.count), ip.count) FROM '.PREFIX.'product AS ip 
                LEFT JOIN '.PREFIX.'product_variant AS ipv ON ip.id = ipv.product_id WHERE ip.id = pupd.product_id) != 0';
            }
          }
          if(MG::getSetting('disabledPropFilter') != 'false') {
            if($prductIds != '') {
              $ifSelect = ", IF(pupd.product_id IN (".DB::quoteIN($prductIds)."), '0', '1') AS disabled";
              $disabledPart = ', disabled DESC';
            } else {
              $ifSelect = '';
              $disabledPart = '';
            }
          }
          if(MG::getSetting('filterSubcategory') == 'true') {
            $res = DB::query("SELECT pupd.prop_data_id, (pupd.name), pupd.id, pupd.prop_id".$ifSelect." FROM ".PREFIX."product_user_property_data AS pupd
              WHERE pupd.prop_id = ".DB::quote($idProp).' AND product_id IN 
              (SELECT id FROM '.PREFIX.'product WHERE cat_id IN ('.DB::quoteIN($categorieIdsAll).') 
              OR FIND_IN_SET('.$_REQUEST['category_id'].', inside_cat)) AND pupd.name <> \'\' '.$storageCheck.' ORDER BY pupd.name ASC'.$disabledPart);
          } else {
            $res = DB::query("SELECT pupd.prop_data_id, (pupd.name), pupd.id, pupd.prop_id".$ifSelect." FROM ".PREFIX."product_user_property_data AS pupd
              WHERE pupd.prop_id = ".DB::quote($idProp).' AND product_id IN 
              (SELECT id FROM '.PREFIX.'product WHERE cat_id IN ('.DB::quoteIN($categorieIdsAll).') 
              OR FIND_IN_SET('.$_REQUEST['category_id'].', inside_cat)) AND pupd.name <> \'\' '.$storageCheck.' ORDER BY pupd.name ASC'.$disabledPart);
          }
          // viewData("SELECT pupd.prop_data_id, (pupd.name), pupd.id, pupd.prop_id".$ifSelect." FROM ".PREFIX."product_user_property_data AS pupd
          //     WHERE pupd.prop_id = ".DB::quote($idProp).' AND product_id IN 
          //     (SELECT id FROM '.PREFIX.'product WHERE cat_id IN ('.DB::quoteIN($categorieIdsAll).') 
          //     OR FIND_IN_SET('.$_REQUEST['category_id'].', inside_cat)) AND pupd.name <> \'\' '.$storageCheck.' ORDER BY pupd.name ASC, disabled DESC');
          unset($temp);
          while($userFieldsData = DB::fetchAssoc($res)) {
            $temp[$userFieldsData['name']]['name'] = $userFieldsData['name'];
            if(empty($temp[$userFieldsData['name']]['id'])) {
              $temp[$userFieldsData['name']]['id'] = $userFieldsData['prop_data_id'];
            } else {
              $temp[$userFieldsData['name']]['id'] = $userFieldsData['prop_data_id'];
            }
            if(MG::getSetting('disabledPropFilter') == 'false') {
              $temp[$userFieldsData['name']]['disabled'] = 0;
            } else {
              $temp[$userFieldsData['name']]['disabled'] = $userFieldsData['disabled'];
            }
          }
          foreach ($temp as $item) {
            $prop['data'][$item['name']] = $item;
          }
          // viewdata($prop['data'][$item['name']]);

          // числовая сортировка идет отдельно
          $intB = true;
          foreach ($prop['data'] as $value) {
            if(!is_numeric(str_replace(',', '.', $value['name']))) $intB = false;
          }
          if($intB) {
            $sort = NULL;
            foreach($prop['data'] as $key => $arr){
              $sort[$key] = $arr['name'];
            }
            array_multisort($sort, SORT_NUMERIC, $prop['data']);
          }
        }
        
        MG::set('prop-'.$prop['name'], $prop['data']);

        if(!empty($prop['data'])) { 
        $propCount++;
        $style = "";   
        $maxCountProp = MG::getSetting('filterCountProp');
        if($propCount > $maxCountProp) {
          $style = "display:none";   
          $allFilter = '<a href="javascript:void(0);" class="mg-viewfilter-all">'.lang('filterShowAll').'</a>';
        }

        $propPieces['style'] = $style;
        $propPieces['name'] = $prop['name'];
        $propPieces['description'] = $prop['description'];
        $propPieces['type'] = $prop['type_filter'];
        $propPieces['idProp'] = $idProp;
        $propsPieces['allFilter'] = $allFilter;

        if(!empty($prop['data'])) {
          $propPieces['data'] = array();

          #тип вывода характеристики (слайдер)
          if($prop['type_filter']!='checkbox' && $prop['type_filter']!='select' && $prop['type_filter']!='slider') {
            $prop['type_filter']='checkbox';
          }
          
        }  
        }
        $propsPieces['props'][] = $propPieces;
      }
      // viewdata($propsPieces);
      if(!MG::isAdmin()) Storage::save('propPieces-'.md5('propPieces'.$_REQUEST['category_id'].LANG.json_encode($_REQUEST['prop'])), $propsPieces);
      // Storage::save('propPieces-'.md5('propPieces'.json_encode($_REQUEST).LANG.json_encode($_REQUEST['prop'])), $propsPieces);
    }
    // viewdata(microtime(true) - $times);
    // viewdata('--------------');
    return MG::layoutManager('layout_prop_filter', $propsPieces);
     // return '<div class="mg-filter">'.$html.$allFilter.'</div>';
  }

  /**
   * Строит HTML верстку для фильтра по характеристикам в админке.
   * <code>
   *  $property = array(
   *    'phone' => array(
   *        'type' => 'text',
   *        'special' => 'like',
   *        'label' => '1',
   *        'value' => null,
   *    )); 
   *  $filter = new Filter($property);
   *  $res = $filter->getHtmlPropertyFilterAdmin();
   *  viewData($res);
   * </code>
   * @return string html верстка чекбоксов характеристик.
   */
  public function getHtmlPropertyFilterAdmin() { 
    $property = array();   
    $_REQUEST['category_id'] = intval($_REQUEST['category_id'])?intval($_REQUEST['category_id']):intval($_REQUEST['cat_id']);
    $cacheRowName = 'filterProperty'.$_REQUEST['category_id'];
      
    // if(URL::isSection('mg-admin')) {
    //   $cacheRowName = 'mgadmin_'.$cacheRowName;
    // }
     
    // $property = Storage::get(md5($cacheRowName));   
    
    // if($property == null) {  
      $property = $this->getPropertyData();
      // Storage::save(md5($cacheRowName),$property);
    // }
    
    $html = "";
    $allFilter = "";

    $propCount = 0;
    // приводим к одному виду все значения характеристик в выбранных фильтрах заменяем 
    // HTML сущности на мнемоники, для последующего сравнения.
    // этот цикл является костылем, т.к. данные в паблике и админке отличаются. 
    // Если его убрать фильтр будет корректно работать только в паблике
    // foreach ($_REQUEST['prop'] as $idProp => $prop) {
    //     foreach ($_REQUEST['prop'][$idProp] as $key => $val) {
    //       $valDecode = htmlspecialchars_decode($val);
    //       $valEncode = htmlspecialchars($valDecode);
    //       $_REQUEST['prop'][$idProp][$key] = $valEncode;
    //     }
    // }

    foreach ($property as $idProp => $prop) {
      ksort($prop['allValue']);
      $prop['name'] = str_replace(array('prop attr=', '[', '  '), array('', ' [', ' '), $prop['name']);
      // 
      if(!empty($prop['allValue'])) { 
      $propCount++;
      $style = "";
      $maxCountProp = MG::getSetting('filterCountProp');
      if($propCount>$maxCountProp) {
        $style = "display:none";   
        $allFilter = '<a href="javascript:void(0);" class="mg-viewfilter-all">'.lang('filterShowAll').'</a>';
      }
      // $values = explode('|',trim($prop['allValue']));   
      $values = $prop['allValue'];
   
      $html .= '<div class="large-3 columns"><div class="mg-filter-item" style="'.$style.';margin-bottom:20px;">';     
      $html .= '<h4>'.$prop['name'];
      $html .= '</h4>';
      
      if(!empty($values)) {
        $values = array_unique($values);
        // if(empty($prop['data'])) {
        //    natcasesort($values);  
        // } else {
        //   $values_sort = explode('|',$prop['data']);
        //   $arr = array();
        //   foreach ($values_sort as $val) {
        //     $arr_val = explode('#',$val);
        //     $arr[] = $arr_val[0];
        //   }          
        //   $values = array_intersect($arr, $values);
        // }
       
        #тип вывода характеристики (слайдер)
        if($prop['type_filter']!='checkbox' && $prop['type_filter']!='select' && $prop['type_filter']!='slider') {
          $prop['type_filter']='checkbox';
        }

       
        if($prop['type_filter']=='slider') {
          if($prop['type'] == 'string') {
            $type = 'slider|easy';
          } else {
            $type = 'slider|hard';
          }

          $values = array();
          if($type == 'slider|easy') {
            $res = DB::query('SELECT DISTINCT name FROM '.PREFIX.'product_user_property_data WHERE prop_id = '.DB::quoteInt($prop['id']));
            while ($row = DB::fetchAssoc($res)) {
              $values[] = (float)$row['name'];
            }
          } else {
            $res = DB::query('SELECT DISTINCT name FROM '.PREFIX.'property_data WHERE prop_id = '.DB::quoteInt($prop['id']));
            while ($row = DB::fetchAssoc($res)) {
              $values[] = (float)$row['name'];
            }
          }

          $max = max($values);
          $min = min($values);
          $fMin = ($_REQUEST['prop'][$prop['id']][1])?(float)$_REQUEST['prop'][$prop['id']][1]:$min;
          $fMax = ($_REQUEST['prop'][$prop['id']][2])?(float)$_REQUEST['prop'][$prop['id']][2]:$max;

          // Если рассмотренных значений меньше 2, нет смысла выводить слайдер    
            $html .= '
              <input type="hidden" name="prop['.$prop['id'].'][0]" value="'.$type.'" />
                <div class="input-line input-group">
                   <span class="text input-group-label">от</span><input type="text" id="Prop'.$prop['id'].'-min" class="price-input start-price numericProtection  price-input" data-fact-min="'.$min.'" name="prop['.$prop['id'].'][]" value="'.$fMin.'">
                   <span class="text input-group-label">до</span><input type="text" id="Prop'.$prop['id'].'-max" class="price-input end-price numericProtection  price-input" data-fact-max="'.$max.'" name="prop['.$prop['id'].'][]" value="'.$fMax.'">
                   <span class="text input-group-label">'.$prop['unit'].'</span>
                </div>
              <div name="prop['.$prop['id'].'][] " class="mg-filter-prop-slider" data-id="'.$prop['id'].'" data-min="'.$min.'" data-max="'.$max.'" data-factmin="'.$fMin.'" data-factmax="'.$fMax.'"></div>';
        }
                
      }
      $html .= '</div></div>';      
      }
    }
    return '<div class="mg-filter">'.$html.$allFilter.'</div>';
  }
  
  /**
   * Выбирает данные о характеристиках для построения фильтра.
   * @return array массив данных о характеристиках
   */
  private function getPropertyData($public = false) {
    if(MG::getSetting('filterSubcategory') == 'true') {
      $categoryIds = implode(',',$_REQUEST['category_ids']);
    } else {
      $categoryIds = end($_REQUEST['category_ids']);       
    }
    
    if(empty($categoryIds)) {
	    $categoryIds = $_REQUEST['category_id']?intval($_REQUEST['category_id']):"0";
	  }
    
    $categoryIdsCurr = $categoryIds;
    $categorieIdsAll = $_REQUEST['category_ids'];
    // формируется условия для запроса. Выборка категорий товаров, 
    // которые активны и есть в наличии, 
    // если это публичная часть и включены соответсвующие опции.
    // Выборка категорий которым принадлежат товары, которые 
    // выводятся в текущей категории как дополнительные
    $currentCategoryId = $_REQUEST['category_id'];

    if($currentCategoryId) {
      $where = '';
      $categoryIdsExtra = array();
      if(!URL::isSection('mg-admin')) {
        $where .= ' p.activity = 1 AND';
        if(MG::getSetting('printProdNullRem') == "true") {
          $where .= ' count != 0 AND';
        }
      }
      $where .= ' FIND_IN_SET('.$currentCategoryId.',p.`inside_cat`)';

      $sql = "SELECT `cat_id` FROM `".PREFIX."product` p WHERE ".$where;
      $res = DB::query($sql);

      while($row = DB::fetchArray($res)) {
        $categoryIdsExtra[] = $row['cat_id'];
      }

      $categoryIdsExtra = array_unique($categoryIdsExtra);
      if(!empty($categoryIdsExtra)) {
        $categoryIds .= ','.implode(',', $categoryIdsExtra);
      }
    }

    // получаем все характеристики для текущей категории и вложенных в нее
    // а также характеристики выводимые для всех категорий
    $sql = "
      SELECT * FROM `".PREFIX."property` as pp
      LEFT JOIN `".PREFIX."category_user_property` as cp
         ON  pp.id = cp.property_id
      WHERE cp.category_id IN (".DB::quote($categoryIds,true).") and pp.filter = 1 and pp.type != 'textarea'
        ORDER BY pp.sort DESC
    ";
    
    $res = DB::query($sql);
    while($row = DB::fetchAssoc($res)) {    
      $property[$row['id']] = $row;    
      $row['default'] = preg_replace("/#(-?\d+)#/i", "", $row['default']);
      $property[$row['id']]['allValue'] = $row['default'];     
    }    
    $regexp = '';
    if(!$public) {
      $sql = "
         SELECT distinct pr.id, pp.name AS pName, pr.name, pr.activity, pr.type FROM `".PREFIX."product_user_property_data` as pp  
         LEFT JOIN `".PREFIX."product` as p
           ON pp.product_id = p.id
         LEFT JOIN `".PREFIX."property` as pr
           ON pp.prop_id = pr.id
         LEFT JOIN `".PREFIX."product_variant` as pv
           ON pv.product_id = p.id
         WHERE p.cat_id IN (".DB::quoteIN($categorieIdsAll).") and pr.filter = 1 and pp.name <> '' and p.activity = 1     
      ";     
    
      if(MG::getSetting('printProdNullRem') == "true" && !URL::isSection('mg-admin')) {
        $sql .=' AND ABS(IFNULL( pv.`count` , 0 ) ) + ABS( p.`count` ) >0';
      }

      $res = DB::query($sql);
       
      while($row = DB::fetchAssoc($res)) {
        if(empty($property[$row['id']])) {
          continue;
        }
        
        $row['pName'] = preg_replace("/#(-?\d+)#/i", "", $row['pName']);
        $property[$row['id']]['name'] = $row['name']; 
        $property[$row['id']]['type'] = $row['type']; 
      }
      
      foreach ($property as $key => $value) {
        // viewdata($value);
        if($value['type'] != 'string') {
          $res = DB::query('SELECT DISTINCT pd.id, pd.name AS name, prop_data_id FROM '.PREFIX.'product_user_property_data AS pupd
            LEFT JOIN '.PREFIX.'property_data AS pd ON pd.id = pupd.prop_data_id
            WHERE pupd.prop_id = '.DB::quoteInt($key).' 
            AND product_id IN (SELECT id FROM '.PREFIX.'product WHERE cat_id IN ('.DB::quoteIN($categorieIdsAll).')) AND pupd.active = 1');
          while($row = DB::fetchAssoc($res)) {
            $data[$key][$row['name']] = $row['prop_data_id'];
          }
          $property[$key]['allValue'] = array();
          foreach ($data[$key] as $keyS => $value) {
            $property[$key]['allValue'][$keyS] = $value;
          }
        } else {
          $res = DB::query('SELECT id, name, prop_data_id FROM '.PREFIX.'product_user_property_data
            WHERE prop_id = '.DB::quoteInt($key).' 
            AND product_id IN (SELECT id FROM '.PREFIX.'product WHERE cat_id IN ('.DB::quoteIN($categorieIdsAll).'))');
          while($row = DB::fetchAssoc($res)) {
            $data[$key][$row['name']] = $row['prop_data_id'];
          }
          $property[$key]['allValue'] = array();
          foreach ($data[$key] as $keyS => $value) {
            $property[$key]['allValue'][$keyS] = $value;
          }
        }
      }
    }
    

    return $property;
  }
  
  
   /**
   * Возвращает id всех товаров удовлетворяющих фильтру по характеристикам.
   * <code>
   *   $filter = new Filter();
   *   $array = Array(       // массив с параметрами от фильтра
   *     20 => Array(        // id характеристики
   *         0 => '2859'  // id значения характеристики | тип характеристики (pp - простые характеристики, mp - сложные характеристики)
   *     ));
   *   $res = $filter->getProductIdByFilter($array);
   *   viewData($res);
   * </code>
   * @param array $properties  массив с ключами переданных массивов с характеристиками
   * @return array массив id товаров.
   */
  public function getProductIdByFilter($properties) {
    $result = array();
    
    // проверка наличия товара если надо
    if(!MG::isAdmin()) {
      if(MG::getSetting('printProdNullRem') == 'true') {
        if(MG::enabledStorage()) {
          $checkCount = ' AND (SELECT SUM(ABS(count)) FROM '.PREFIX.'product_on_storage WHERE product_id = p.id) > 0';
        } else {
          // $checkCount = ' AND ABS(p.`count`) > 0';
          $checkCount = ' AND ABS(IFNULL((SELECT SUM(ABS(count)) FROM '.PREFIX.'product_variant WHERE product_id = p.id), 0)) + ABS(p.`count`) > 0';
        }
      }
    }

    // для слайдера исключение
    foreach ($properties as $id => $property) {
      if(in_array($property[0], array('slider|easy', 'slider|hard'))) {
        $slider[$id] = $property;
        unset($properties[$id]);
      }
    }

    foreach ($slider as $id => $item) {
      $type = explode('|', $item[0]);
      $type = $type[1];
      if($type == 'easy') {
        $res = DB::query('SELECT product_id, prop_id FROM `'.PREFIX.'product_user_property_data` WHERE 
          0+name >= '.DB::quote($item[1]).' AND 0+name <= '.DB::quote($item[2]).' AND prop_id = '.DB::quoteInt($id));
        while ($row = DB::fetchAssoc($res)) {
          $idProd[$row['prop_id']][] = $row['product_id'];
        }
      } else {
        $res = DB::query('SELECT pupd.product_id, pd.prop_id FROM `'.PREFIX.'product_user_property_data` AS pupd
          LEFT JOIN `'.PREFIX.'property_data` AS pd ON pd.id = pupd.prop_data_id WHERE pupd.active = 1 AND
          pd.name >= '.DB::quote($item[1]).' AND pd.name <= '.DB::quote($item[2]).' AND pd.prop_id = '.DB::quoteInt($id).' GROUP BY pupd.product_id');
        while ($row = DB::fetchAssoc($res)) {
          $idProd[$row['prop_id']][] = $row['product_id'];
        }
      }
    }

    foreach ($idProd as $key => $value) {
      $idProd[$key] = array_unique($value);
    }
    $sliderResult = current($idProd);
    foreach ($idProd as $value) {
      $sliderResult = array_intersect($sliderResult, $value);
    }

    if(count($sliderResult) == 0) {
      $sliderResultQ = '';
    } else {
      $sliderResultQ = ' AND product_id IN ('.DB::quoteIN(implode(',', $sliderResult)).')';
    }
    
    // подготовка значений для поиска
    foreach ($properties as $id => $property) {
      foreach ($property as $cnt => $value) {
        if($value != '') {
          $temp = explode('|', $value);
          $dataProperty[$id][] = $temp[0];
        }
      }
    }

    if((count($dataProperty) + count($productsPropertyId)) == 0) {
      MG::set('productFindedByFilter', $sliderResult);
      return $sliderResult;
    }

    // достаем все возможные товары для каждой выбранной характеристики
    unset($idProd);
    foreach ($dataProperty as $key => $item) {
      $countVarCheck = '';
      if(MG::getSetting('showVariantNull') != 'true') {
        $res = DB::query('SELECT p.type FROM '.PREFIX.'property AS p LEFT JOIN '.PREFIX.'product_user_property_data AS pupd ON p.id = pupd.prop_id 
          WHERE pupd.prop_data_id IN ('.DB::quoteIN($item).')');
        while($row = DB::fetchAssoc($res)) {
          $type = $row['type'];
        }
        
        if(MG::enabledStorage()) {
          switch ($type) {
            case 'size':
              $countVarCheck = '';//' AND (SELECT SUM(ABS(count)) FROM `'.PREFIX.'product_variant` AS ipv WHERE ipv.size IN ('.DB::quoteIN($item).') AND ipv.product_id = pupd.product_id) != 0';
              break;
            case 'color':
              $countVarCheck = '';//' AND (SELECT SUM(ABS(count)) FROM `'.PREFIX.'product_variant` AS ipv WHERE ipv.color IN ('.DB::quoteIN($item).') AND ipv.product_id = pupd.product_id) != 0';
              break;
            default:
              $countVarCheck = '';
              break;
          }
        } else {
          switch ($type) {
            case 'size':
              $countVarCheck = ' AND (SELECT SUM(ABS(count)) FROM `'.PREFIX.'product_variant` AS ipv WHERE ipv.size IN ('.DB::quoteIN($item).') AND ipv.product_id = pupd.product_id) != 0';
              break;
            case 'color':
              $countVarCheck = ' AND (SELECT SUM(ABS(count)) FROM `'.PREFIX.'product_variant` AS ipv WHERE ipv.color IN ('.DB::quoteIN($item).') AND ipv.product_id = pupd.product_id) != 0';
              break;
            default:
              $countVarCheck = '';
              break;
          }
        }
      }
      $res = DB::query('SELECT product_id, prop_id FROM `'.PREFIX.'product_user_property_data` AS pupd
        LEFT JOIN '.PREFIX.'product AS p ON p.id = pupd.product_id
        WHERE prop_data_id IN ('.DB::quoteIN($item).') 
        AND active = 1 AND prop_id = '.DB::quoteInt($key).$sliderResultQ.$countVarCheck.$checkCount);

      while($row = DB::fetchAssoc($res)) {
        $idProd[$row['prop_id']][] = $row['product_id'];
      }
    }

    foreach ($idProd as $key => $value) {
      $idProd[$key] = array_unique($value);
    }
    
    $all = current($idProd);
    foreach ($idProd as $value) {
      $all = array_intersect($all, $value);
    }
    $result = $all;

    MG::set('productFindedByFilter', $result);
    return $result;
  }   

  /**
   * Возвращает список доступных характеристик выбранной категории, для дальнейшего построения блока фильтров.
   * <code>
   *   $filter = new Filter();
   *   $res = $filter->getApplyFilterList();
   *   viewData($res);
   * </code>
   * @return array - массив с характеристиками - название, id и выбранные в фильтре значения
   */
  public function getApplyFilterList() {
    $filterList = array();                
    
    if(!empty($_GET['applyFilter'])) {
      if(!empty($_GET['price_course'])) {
        $filterList[] = array(
          'name' => lang('applyFilterPrice'),
          'code' => 'price_course',
          'values' => array_merge(array('slider'), $_GET['price_course']),
        );
      }   
      
      $propIds = array_keys($_GET['prop']);
      
      if(!empty($propIds)) {
        $propIds = implode(",", $propIds);
      } else {
        $propIds = 0;
      }
      
      $sql = "
        SELECT `id`, `name` 
        FROM `".PREFIX."property` 
        WHERE `id` IN (".$propIds.")";
      $dbRes = DB::query($sql);
      
      while($arRes = DB::fetchAssoc($dbRes)) {
        MG::loadLocaleData($arRes['id'], LANG, 'property', $arRes);
        $propNames[$arRes['id']] = $arRes['name'];
      }        

      // для слайдера
      foreach ($_GET['prop'] as $id => $item) {
        if(in_array($item[0], array('slider|easy', 'slider|hard'))) {
          if(empty($_REQUEST['prop'][$id])) {
            unset($_GET['prop'][$id]);
            continue;
          }
          $filterList[] = array(
            'name' => $propNames[$id],
            'code' => 'prop['.$id.']',
            'values' => $item,
          );
          unset($_GET['prop'][$id]);
        }
      }

      foreach($_GET['prop'] as $id => $property) {
        if(empty($_GET['prop'][$id][0])) {
          continue;
        }

        $value = array();

        // разбиваем характеристику
        $neProp = null;
        foreach ($property as $item) {
          $propertyS = explode('|', $item);
          $type = $propertyS[1];
          $ids = $propertyS[0];

          $res = DB::query('SELECT DISTINCT(name) AS name, id FROM '.PREFIX.'property_data WHERE id IN ('.DB::quoteIN($ids).') GROUP BY name');
          while($row = DB::fetchAssoc($res)) {
            MG::loadLocaleData($row['id'], LANG, 'property_data', $row);
            $data['val'] = $item;
            $data['name'] = $row['name'];
            $neProp[] = $data;
          }
        }
        
        $filterList[] = array(
          'name' => $propNames[$id],
          'code' => 'prop['.$id.']',
          'values' => $neProp,
        );
      }      
    }

    return $filterList;
  }
}