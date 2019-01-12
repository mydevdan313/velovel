<?php

/**
 * Контроллер: Mgadmin
 *
 * Класс Controllers_Mgadmin предназначен для открытия панели администрирования.
 * - Формирует панель управления;
 * - Проверяет наличие обновлений движка на сервере;
 * - Обрабатывает запросы на получение выгрузок каталога.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Controller
 */
class Controllers_Mgadmin extends BaseController {

  function __construct() {
    if(LANG != 'LANG' && LANG != 'default') {
      MG::redirect('/mg-admin');
    }

    if (time() > MG::getOption('imageDropTimer')) {
      MG::dropTrash();
    }

    MG::disableTemplate();
    $model = new Models_Order;
    MG::addInformer(array('count' => $model->getNewOrdersCount(), 'class' => 'message-wrap', 'classIcon' => 'fa-shopping-basket', 'isPlugin' => false, 'section' => 'orders', 'priority' => 80));
    // if ('1' == User::getThis()->role) {
    //   MG::addInformer(array('count' => '', 'class' => 'message-wrap', 'classIcon' => 'statistic-icon', 'isPlugin' => false, 'section' => 'statistics', 'priority' => 10));
    // }
    if (URL::get('csvorderfull')) {
      if(USER::access('order') == 0) exit();
      if($_REQUEST['id']) $id = array($_REQUEST['id']);
      $model = new Models_Order();
      $model->exportToCsvOrder($id, true, true);
    }
    $loginAttempt = (int) MG::getSetting('loginAttempt')?MG::getSetting('loginAttempt'):5;
    unset($_POST['capcha']);
    if (($_SESSION['loginAttempt'] >= 2 )&& ($_SESSION['loginAttempt'] < $loginAttempt)) {
      if (MG::getSetting('useReCaptcha') == 'true' && MG::getSetting('reCaptchaSecret') && MG::getSetting('reCaptchaKey')) {
        if ($_POST['email'] != '' || $_POST['pass'] != '' || $_POST['capcha'] != '') {
          $tmp = ($loginAttempt - $_SESSION['loginAttempt']);
          $msgError = '<span class="msgError">'.MG::restoreMsg('msg__enter_recaptcha_failed',array('#COUNT#' => $tmp)).'</span>';
        }
        $checkCapcha = "<script src='https://www.google.com/recaptcha/api.js'></script>".MG::printReCaptcha();
      }
      else{
        if ($_POST['email'] != '' || $_POST['pass'] != '' || $_POST['capcha'] != '') {
          $tmp = ($loginAttempt - $_SESSION['loginAttempt']);
          $msgError = '<span class="msgError">'.MG::restoreMsg('msg__enter_captcha_failed',array('#COUNT#' => $tmp)).'</span>';
        }
        $checkCapcha = '<div class="checkCapcha">
          <img style="margin-top: 5px; border: 1px solid gray;" src = "'.SITE.'/'.'captcha.html" width="140" height="36">
          <div>Введите текст с картинки:<span class="red-star">*</span> </div>
          <input type="text" name="capcha" class="captcha"></div>';
      }
        
    } elseif (($_SESSION['loginAttempt'] >= $loginAttempt)){  
      $msgError = '<span class="msgError">'.
            'В целях безопасности возможность авторизации '.
            'заблокирована на 15 мин. Разблокировать вход можно по ссылке в письме администратору.</span>';
    }
    $this->data = array(
      'staticMenu' => MG::getSetting('staticMenu'),
      'themeBackground' => MG::getSetting('themeBackground'),
      'themeColor' => MG::getSetting('themeColor'),
      'languageLocale' => MG::getSetting('languageLocale'),
      'informerPanel' => MG::createInformerPanel(),
      'msgError' => $msgError ? $msgError : '',
      'checkCapcha' => $checkCapcha ? $checkCapcha : ''
    );
    if(MG::getSetting('autoGeneration')=='true') {
      $filename = 'sitemap.xml';      
      $create = true;
      if (file_exists($filename)) { 
        $siteMaptime =  filemtime($filename); 
        $days = MG::getSetting('generateEvery') *24*60*60;
        
        if (time() - $siteMaptime >= $days) {
          $create = true;
        } else {
          $create = false;
        }        
      }
      if ($create) {
        Seo::autoGenerateSitemap();
      }
    }    
    $this->pluginsList = PM::getPluginsInfo();
    $this->lang = MG::get('lang');
    if (!$checkLibs = MG::libExists()) {
     // ecли нет класса updata или контрольная сумма файла не совпадает, то 
     // удаление config и флаг в бд о том, что версия нелицензионная
      $fileCont = file_get_contents(URL::getDocumentRoot().'mg-core/lib/updata.php');
      $fileCont = str_replace(array("\r\n", "\r", "\n", "\t", ' ',), '', $fileCont);
      $fileCont = iconv("Windows-1251","UTF-8",$fileCont);
	     
      $includeD = false;
      include 'mg-core/lib/updata.php';

      if (!method_exists('Updata', 'updataSystem')) {
        $hash = md5('randomtrashbefore'.substr(time(), 0, -4).'satatan'.VER.'moartrash');
        $timeLastUpdata = MG::getSetting('timeLastUpdata');
        if($hash != $timeLastUpdata) {
          MG::setOption('timeLastUpdata', $hash);
          $url = 'http://updata.moguta.ru/updataserver';
          $post = 'invalid=1'.
            '&sName='.$_SERVER['SERVER_NAME'];
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
          curl_setopt($ch, CURLOPT_HEADER, false);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_POST, true);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
          curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
          $res = curl_exec($ch);
          curl_close($ch);
          $data = json_decode($res, true);
          if ($data['remove'] == '1') {
            // unlink('config.ini');       
  		      $this->bdInfoUpdateBlock();
            return false;
          }      
        }
      }    
      $this->bdInfoUpdateToTrial();
      
      require_once(URL::getDocumentRoot()."mg-core/lib/updata.php");
    
      $newVer = Updata::checkUpdata(false, true);
      
      $this->newVersion = $newVer['lastVersion'];
      $this->fakeKey = MG::getSetting('trialVersion') ? MG::getSetting('trialVersion') : '' ;
    }
	}
  /**
   * @ignore
   */
	function bdInfoUpdateBlock(){
		$this->fakeKey = 'Движок не функционирует из-за нарушения защитных файлов - публичная часть будет недоступна.' ;
		if (!MG::getSetting('trialVersionStart')) {
		DB::query('INSERT INTO `'.PREFIX.'setting` (`id`, `option`, `value`, `active`, `name`) VALUES (NULL, "trialVersionStart", "true1", "N", "")'); 
		}
		if (!MG::getSetting('trialVersion')) {
		$sql = 'INSERT INTO `'.PREFIX.'setting` (`id`, `option`, `value`, `active`, `name`) '
		. 'VALUES (NULL, "trialVersion","Движок не функционирует из-за нарушения защитных файлов - публичная часть будет недоступна.", "N", "")';
		DB::query($sql); 
		} else {
		DB::query('UPDATE `'.PREFIX.'setting` SET '
		. '`value` = "Движок не функционирует из-за нарушения защитных файлов - публичная часть будет недоступна." WHERE `option`= "trialVersion"'); 
		}
	}
	/**
   * @ignore
   */
	function bdInfoUpdateToTrial(){
	  if (MG::getSetting('trialVersionStart')=='true1') {
        DB::query('DELETE FROM `'.PREFIX.'setting` WHERE `option`= "trialVersionStart"');
        DB::query('DELETE FROM `'.PREFIX.'setting` WHERE `option`= "trialVersion"');
      }
	}
}