<?php
/**
 * Класс Navigator - генерирует пейджер для постраничной навигации.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */
class Navigator{

  //Блок переменных доступных только внутри класса .

  private $countRecord; //Количество выводимых записей на странице.
  private $sql; // Исходный sql запрос.
  private $maxAcceptedCount; //Количество записей вернувшихся по запросу
  private $numberPage; //Номер текущей страницы.
  private $viewAll; //Флаг - показать все страницы.
  private $paramName; //Имя параметра в GET запросе например "page".
  private $linkCount; //количество выводимых ссылок на страницы в пейджере.
  private $returnData = array();
  public $linkPieces = array();
  public  $allPages = true;
  public $poinBetwenIntervar = false;

  /**
   * Производит  sql запрос и устанавливает параметры.
   * @param string $sql - запрос к базе
   * @param int $numberPage - номер запрашиваемой страницы
   * @param int $countRecord - количество выводимых записей на одной странице
   * @param int $linkCount - количество выводимых ссылок в пейджере
   * @param bool $viewAll - вывести все страницы
   * @param string $paramName - наименование гет переменной указывающей текущую страницу
   * @param int|null $maxCount - количество записей элементов для построения навигатора
   */
  public function __construct($sql, $numberPage, $countRecord = 20, $linkCount = 6, $viewAll = false, $paramName = "page", $maxCount = null) {
    if(isset($_REQUEST['csvExport']) && $_REQUEST['csvExport'] == 1) {
      $res = DB::query($sql);
      while ($row = DB::fetchAssoc($res)) {
        $data[] = $row;
      }
      CSV::export($data);
      return true;
    }
   
    // Инициализируем переменные класса
    $this->sql = $sql;
    $this->countRecord = $countRecord;
    $this->numberPage = $numberPage;
    $this->viewAll = $viewAll;
    $this->paramName = $paramName;
    $this->maxCount = $maxCount;

    //количество ссылок не может быть меньше двух
    $this->linkCount = $linkCount==1?2:$linkCount;

   //$this->returnData = Storage::get(md5($sql.$numberPage));    
   //if($this->returnData == null) {
      // если не запроcе вывод всего списка записей
      if(!$this->viewAll) {
        //вычисляем данные для пейджера
        $this->calcDataPage();
      }

      // выполняем запрос
      $res = DB::query($this->sql);
     
      // сохраняем все полученные записи
      while($row = DB::fetchAssoc($res)) {    
        $this->returnData[] = $row;
      }
      // $this->maxAcceptedCount = DB::affectedRows();


   //  Storage::save(md5($sql.$numberPage),$this->returnData); 
   //}
  }

  /**
   * Возвращает результат выполнения  SQL запроса.
   * <code>
   *   $navigator = new Navigator($sql, 1, 20);
   *   $res = $navigator->getNumRowsSql();
   *   viewData($res);
   * </code>
   * @return int
   */
  public function getNumRowsSql() {
    return $this->maxAcceptedCount;
  }

  /**
   * Возвращает количество записей.
   * <code>
   *   $navigator = new Navigator($sql, 1, 20);
   *   $res = $navigator->getRowsSql();
   *   viewData($res);
   * </code>
   * @return array - массив полученных записей
   */
  public function getRowsSql() { 
    if(empty($this->returnData)) {
      return array();    
    }
    return $this->returnData;  
  }

  /**
   * Возвращает результат выполнения SQL запроса.
   * <code>
   *   $navigator = new Navigator($sql, 1, 20);
   *   $res = $navigator->getPager();
   *   viewData($res);
   * </code>
   * @param string $type тип навигатора
   * @return string - пейджер в HTML виде
   */
  public function getPager($type = "getQuery") {
    return $this->createNavigator($type);
  }

  /**
   * Вычисляет все параметры для составления пейджера:
   *  - общее колличество записей.
   *  - часть запроса указывающую на нужную страницу.
   *  - максимально доступное количество страниц
   */
  private function calcDataPage() {   
    if($this->maxCount == null) {
      $result = DB::query($this->sql);
      //узнаем общее количество возвращенных записей
      $count = DB::numRows($result);
    } else {
      $count = $this->maxCount;
    }

    $this->maxAcceptedCount = $count;
    MG::set('maxRecordToPager', $count);
    //Вычисляем максимально доступное количество страниц


    // общее количество страниц
	if(empty($this->countRecord)||!is_numeric($this->countRecord)) {$this->countRecord=1;}
    $maxCountRecOnPage = ceil($count / $this->countRecord);

    $this->maxCountRecOnPage = $maxCountRecOnPage;
    // если общее количество страниц меньше чем должно выводиться впейджере
    if($maxCountRecOnPage <= $this->linkCount) {
      $this->linkCount=$maxCountRecOnPage-1;
    }

    // если максимальное количество страниц меньше чем номер запрашиваемой
    if($maxCountRecOnPage <= ($this->numberPage - 1)) {
      
      // если запрашивается  страница пагинации, которая не существует,
      // то в публичной части произойдет редирект на страницу с ошибкой.
      // в админке будет выведена последняя из доступных.
      /*  if(MG::get('controller')!='controllers_ajax') {
        header( "HTTP/1.1 404 Not Found" );
        MG::redirect('/404');
        exit();      
      }*/
      // то запросим у MySql последнюю доступную страницу
      $pos = $maxCountRecOnPage - 1;
      $pos = $pos>0?$pos:0;
      //если номер запрашиваемой страницы, меньше либо равен 0
    } elseif(0 >= ($this->numberPage - 1)) {

      //то запросим у MySql первую доступную страницу
      $pos = 0;

      // если запрашиваемая страница попадает в диапазон существующих
    } else {
      //то запросим у MySql нужную страницу
      $pos = $this->numberPage - 1;
    }

    
    // к запросу дописываем параметр вывода записей с нужной позиции, и их количество
    $this->sql = $this->sql." LIMIT ".$pos * $this->countRecord.", ".$this->countRecord;
  }

  /**
   * Создает навигатор.
   * @param string $type тип навигатора
   * @return string - пейджер в HTML виде
   */
  private function createNavigator($type) {
    // если все записи помещаются на одной странице не формируем навигатор
    if($this->maxAcceptedCount <= $this->countRecord) {
      return false;
    }
    //формирование  навигатора
    if(MG::isAdmin()) { //В административной части
      //если текущая страница, выходит за рамки допустимых, то показываем первую доступную с нужной стороны.
      if($this->numberPage <= 0) {
        $this->numberPage = 1; // если текущая страница меньше первой то показываем всегда перву.
      }
      if($this->numberPage > $this->maxCountRecOnPage) {
        $this->numberPage = $this->maxCountRecOnPage; // если текущая страница больше последней то показываем всегда последнюю.
      }
    } else {//В публичной части
      if(MG::get('controller')=="controllers_catalog" && (intval($this->numberPage) < 0 || intval($this->numberPage) > $this->maxCountRecOnPage)) { //если текущая страница, выходит за рамки допустимых, то показываем страницу 404.
        header( "HTTP/1.1 404 Not Found" );
        MG::redirect('/404');
        exit();
      }else{
        if($this->numberPage == 0) {
          $this->numberPage = 1; // если текущая страница меньше первой то показываем всегда перву.
        }
      }
    }

    $first = '';
    $prev = '';
    $next = '';
    $last = '';

    $firstPieces = array('needed' => false);
    $prevPieces = array('needed' => false);
    $leftpoint = '';
    $firstPagesPieces = array('needed' => false);
    $pagerPieces = array();
    $lastPagesPieces = array('needed' => false);
    $rightpoint ='';
    $nextPieces = array('needed' => false);
    $lastPieces = array('needed' => false);

    //создаем кнопки для навигатора
    if($this->numberPage > 1) { // если не первая страница
      $prev = $this->getLink('linkPage navButton', ($this->numberPage - 1), $type, '&laquo;');
      $prevPieces = $this->linkPieces;
      $prevPieces['needed'] = true;

      $first = $this->getLink('linkPage navButton', (1), $type, '&laquo;&laquo;');
      $firstPieces = $this->linkPieces;
      $firstPieces['needed'] = true;
    }
    if($this->numberPage < $this->maxCountRecOnPage) { // если не последняя страница
      $next = $this->getLink('linkPage navButton', ($this->numberPage + 1), $type, '&raquo;');
      $nextPieces = $this->linkPieces;
      $nextPieces['needed'] = true;

      $last = $this->getLink('linkPage navButton', ($this->maxCountRecOnPage), $type, '&raquo;&raquo;');
      $lastPieces = $this->linkPieces;
      $lastPieces['needed'] = true;
    }

    // если количество доступных страниц меньше требуемого, то вывести лишь возможные
    if($this->linkCount > $this->maxCountRecOnPage) {
      $this->linkCount = $this->maxCountRecOnPage;
    }



    $half = floor($this->linkCount / 2);

    $pager = '';
    if(!MG::isAdmin()) {
      if($this->allPages) {
        $allPages='<div class="allPages">Всего страниц: <span>'.$this->maxCountRecOnPage.'</span></div>';
      };
    }

    // если все записи помещаются на двух страницах выводим только две ссылки
    if($this->linkCount==1) {
       for($i = 1; $i <= 2; $i++) {
      $class = "linkPage";
      if($i == $this->numberPage) {
        $class = "active";
      }
      $pager.=$this->getLink($class, $i, $type);
      $pagerPieces[] = $this->linkPieces;
      // возвращаем полученный список страниц
      }
      if(MG::isAdmin()) {
        $pagination = 'fl-right pagination';
        $navigator = '<div class="mg-pager">'.$allPages.'<ul class="clearfix '.$pagination.'">'.$pager."</ul></div>";

        return $navigator;
      }
      // $navigator = '<div class="mg-pager">'.$allPages.'<ul class="clearfix '.$pagination.'">'.$pager."</ul></div>";
      // return $navigator;
      return MG::layoutManager('layout_pagination', array(
      'totalPages' => $this->maxCountRecOnPage, 
      'pager' => $pagerPieces));
    }


    //средняя часть навигатора , вывод ссылок по половине от общего числа выводимых
    //по обе стороны текущей страницы
    for($i = ($this->numberPage - $half);
        $i <= ($this->numberPage + $half);
        $i++) {

      //всем ссылкам назначается класс 'linkPage'
      $class = "linkPage";

      //если ссылка идет на текущую страницу то ей присваивается особый класс active
      if($i == $this->numberPage) {
        $class = "active";
      }

      $noneftpoint = false;
      $norightpoint = false;
      $lastpages = ''; 
     
      // формирование ссылок на добавочные с конца страницы
      if($i <= 0) {

        $numberPage = (abs($i) + $this->numberPage + $half + 1);
        $lastpages = $this->getLink($class, $numberPage, $type).$lastpages;
        $lastPagesPieces = $this->linkPieces;
        $lastPagesPieces['needed'] = true;

        // если начали добавлять страницы, убираем точки с другого конца
        $leftpoint = "";
        // флаг о том что точки слева убраны
        $noneftpoint = true;
      }

      $firstpages ='';
      // формирование ссылок на добавочные с начала страницы
      if($i > $this->maxCountRecOnPage) {
        $numberPage = (abs($i - $this->maxCountRecOnPage - $this->numberPage + $half));
        $firstpages = $this->getLink($class, $numberPage, $type).$firstpages;
        $firstPagesPieces = $this->linkPieces;
        $firstPagesPieces['needed'] = true;

        $norightpoint = true; //если начали добавлять  страницы, убираем точки с другого конца
        $rightpoint = ""; // флаг о том что точки справа убраны
      }


        if($i > 0 && $i <= $this->maxCountRecOnPage) { //если формируемая ссылка попадает в интервал допустимых страниц
          if(!$noneftpoint && $this->poinBetwenIntervar)
            $leftpoint = "<span class='point'>...</span>"; //добавляем точки слева от списка
          $pager.=$this->getLink($class, $i, $type); //создаем ссылку
          $pagerPieces[] = $this->linkPieces;
          if(!$norightpoint && $this->poinBetwenIntervar)
            $rightpoint = "<span class='point'>...</span>"; //добавляем точки справа от списка
        }

    }

    //склеиваем все сгенерированные части навигатора
    if(MG::isAdmin()) {
      $pagination = 'fl-right pagination';
      $navigator = '<div class="mg-pager">'.$allPages.'<ul class="clearfix '.$pagination.'">'.$first.$prev.$leftpoint.$firstpages.$pager.$lastpages.$rightpoint.$next.$last."</ul></div>";
      // возвращаем полученный список страниц
      return $navigator;
    }

    return MG::layoutManager('layout_pagination', array(
      'totalPages' => $this->maxCountRecOnPage, 
      'first' => $firstPieces,
      'prev' => $prevPieces,
      'leftpoint' => $leftpoint,
      'firstPages' => $firstPagesPieces,
      'pager' => $pagerPieces,
      'lastPages' => $lastPagesPieces,
      'rightpoint' => $rightpoint,
      'next' => $nextPieces,
      'last' => $lastPieces ));
    // return $navigator;
  }

  /**
   * Дает ссылки навигатору.
   * @param string $class класс для подстановки в верстку
   * @param int $numberPage номер страницы
   * @param string $type тип навигатора
   * @param string $ancor якорь ссылки
   * @return string - пейджер в HTML виде
   */
  private function getLink($class, $numberPage, $type = "getQuery", $ancor = null) {
    $href = "href='javascript:void(0);'";
    if($type == "forAjax") {
      $href = "href='javascript:void(0);'";
    }
    if($type == "getQuery") {
      $uri = $_SERVER['REQUEST_URI'];      
      if(MG::get('controller')=="controllers_catalog" && (MG::getSetting('catalogIndex')=='true') &&(URL::isSection(null)||(URL::isSection('index')))) {
        $cutPath = URL::getCutPath();
        $uri = substr_count($uri, 'index') ? str_replace('/index', '/catalog', $uri) : str_replace($cutPath.'/', $cutPath.'/catalog', $uri);
      }

      $url = str_replace(array('[', ']', '(', ')'), array('&#91;', '&#93;', '%28', '%29'), URL::add_get($uri, $this->paramName, $numberPage));      
      $href =  "href='".$url."'";
    }
    $ancor = $ancor ? $ancor : $numberPage;
    if($class == 'active') {
      $liClass = ' class="current"';
    } else {
      $liClass = '';
    }
    $this->linkPieces = array('liClass' => $liClass, 'class' => $class, 'href' => $href, 'ancor' => $ancor);
    return "<li".$liClass."><a class='".$class." page_".$numberPage."' ".$href." >".$ancor."</a></li>";
  }
}