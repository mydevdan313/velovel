<?php

/**
 * Класс Actioner - предназначен для обработки административных действий, 
 * совершаемых из панели управления сайтом, таких как добавление и удалени товаров, 
 * категорий, и др. сущностей.
 * 
 * Методы класса являются контролерами между AJAX запросами и логикой моделей движка, возвращают в конечном результате строку в JSON формате.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */
class Actioner {

	/**
	 * @var string сообщение об успешнон результате выполнения операции. 
	 */
	public $messageSucces;

	/**
	 * @var string сообщение о неудачном результате выполнения операции. 
	 */
	public $messageError;

	/**
	 * @var mixed массив с данными возвращаемый в ответ на AJAX запрос. 
	 */
	public $data = array();

	/**
	 * @var mixed язык локали движка. 
	 */
	public $lang = array();

	/**
	 * @var string префикс таблиц в базе сайта. 
	 */
	public $prefix;

	/**
	 * Конструктор инициализирует поля клааса.
	 * @param bool $lang - массив дополняющий локаль движка. Используется для работы плагинов.
	 */
	public function __construct($lang = false) {
		$langMerge = array();
		if (!empty($lang)) {
			$langMerge = $lang;
		}// если $lang не пустой, значит он передан для работы в наследнике данного класса, например для обработки аяксовых запросов плагина
		include('mg-admin/locales/'.MG::getSetting('languageLocale').'.php');

		$lang = array_merge($lang, $langMerge);

		$this->messageSucces = $lang['ACT_SUCCESS'];
		$this->messageError = $lang['ACT_ERROR'];

		$this->lang = $lang;
		$this->prefix = PREFIX;

		// для удаления лишних пробелов в начале и конце
		foreach ($_POST as $key => $value) {
			// проверка на JSON (сгруппированный пакет данных)
			$tmp = json_decode($value, true);
			if($tmp !== $value && (strpos($value, '[') === 0 || strpos($value, '{') === 0)) {
				$value = json_encode(self::reqursiveTrim($tmp));
			} else {
				$value = self::reqursiveTrim($value);
			}
			// обновление данных 
			$_POST[$key] = $value;
		}
	}

	public function reqursiveTrim($data) {
		if(is_array($data)) {
			foreach ($data as $key => $value) {
				if(is_array($value)) {
					$data[$key] = self::reqursiveTrim($value);
				} else {
					if(is_string($value)) {
						$data[$key] = trim($value);
					}
				}
			}
		} else {
			$data = trim($data);
		}
		return $data;
	}

	/**
	 * Запускает один из методов данного класса.
	 * @param string $action - название метода который нужно вызвать.
	 */
	public function runAction($action) {
		unset($_POST['mguniqueurl']);
		unset($_POST['mguniquetype']);
		//отсекаем все что после  знака ?
		$action = preg_replace("/\?.*/s", "", $action);

		$this->jsonResponse($this->$action());
		exit;
	}

	/**
	 * Добавляет продукт в базу.
	 * @return bool
	 */
	public function addProduct() {
		if(USER::access('product') < 2) {
			$this->messageError = $this->lang['ACCESS_CREATE_PRODUCT'];
			return false;
		}
		$model = new Models_Product;
		$this->data = $model->addProduct($_POST);
		$this->messageSucces = $this->lang['ACT_CREAT_PROD'].' "'.$_POST['name'].'"';
		$this->messageError = $this->lang['ACT_NOT_CREAT_PROD'];
		return true;
	}
	
 
	/**
	 * Клонирует  продукт.
	 * @return bool
	 */
	public function cloneProduct() {
		if(USER::access('product') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$model = new Models_Product;
		$model->clone = true;
		$this->data = $model->cloneProduct($_POST['id']);
		$this->data['image_url'] = mgImageProductPath($this->data['image_url'], $this->data['id']);
		$this->data['sortshow'] = 'true';
		if (MG::getSetting('showCodeInCatalog')=='true') {
			$this->data['codeshow'] = 'true';
		}
		$this->messageSucces = $this->lang['ACT_CLONE_PROD'];
		$this->messageError = $this->lang['ACT_NOT_CLONE_PROD'];
		return true;
	}

	/**
	 * Клонирует  заказ.
	 * @return bool
	 */
	public function cloneOrder() {
		if(USER::access('order') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$model = new Models_Order;
		$this->messageSucces = $this->lang['ACT_CLONE_ORDER'];
		$this->messageError = $this->lang['ACT_NOT_CLONE_ORDER'];
		$this->data = $model->cloneOrder($_POST['id']);
		return $this->data;
	}

	/**
	 * Активирует плагин.
	 * @return bool
	 */
	public function activatePlugin() {
		if(USER::access('plugin') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageSucces = $this->lang['ACT_ACTIVE_PLUG'].' "'.$_POST['pluginTitle'].'"';
		$pluginFolder = $_POST['pluginFolder'];
		$res = DB::query("
			SELECT *
			FROM  `".PREFIX."plugins`
			WHERE folderName = '%s'
			", $pluginFolder);

		if (!DB::numRows($res)) {
			$result = DB::query("
				INSERT INTO `".PREFIX."plugins`
				VALUES ('%s', '1')"
					, $pluginFolder);

			MG::createActivationHook($pluginFolder);
			$this->data['havePage'] = PM::isHookInReg($pluginFolder);
			return true;
		}

		if ($result = DB::query("
			UPDATE `".PREFIX."plugins`
			SET active = '1'
			WHERE `folderName` = '%s'
			", $pluginFolder
			)) {
			MG::createActivationHook($pluginFolder);
			$this->data['havePage'] = PM::isHookInReg($pluginFolder);
			$this->data['newInformer'] = MG::createInformerPanel();
			return true;
		}

		return false;
	}

	/**
	 * Деактивирует плагин.
	 * @return bool
	 */
	public function deactivatePlugin() {
		if(USER::access('plugin') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageSucces = $this->lang['ACT_NOT_ACTIVE_PLUG'].' "'.$_POST['pluginTitle'].'"';
		$pluginFolder = $_POST['pluginFolder'];
		$res = DB::query("
			SELECT *
			FROM  `".PREFIX."plugins`
			WHERE folderName = '%s'
			", $pluginFolder);

		if (DB::numRows($res)) {
			DB::query("
				UPDATE `".PREFIX."plugins`
				SET active = '0'
				WHERE `folderName` = '%s'
			", $pluginFolder
			);

			MG::createDeactivationHook($pluginFolder);
			return true;
		}

		return false;
	}

	/**
	 * Удаляет инсталятор.
	 * @return void
	 */
	public function delInstal() {
		$installDir = SITE_DIR.URL::getCutPath().'/install/';
		$this->removeDir($installDir);
		MG::redirect('');
	}

	/**
	 * Удаляет папку со всем ее содержимым.
	 * @param string $path путь к удаляемой папке.
	 * @return void
	 */
	public function removeDir($path) {
		if (file_exists($path) && is_dir($path)) {
			$dirHandle = opendir($path);

			while (false !== ($file = readdir($dirHandle))) {

				if ($file != '.' && $file != '..') {// Исключаем папки с назварием '.' и '..'
					$tmpPath = $path.'/'.$file;
					chmod($tmpPath, 0777);

					if (is_dir($tmpPath)) {  // Если папка.
						$this->removeDir($tmpPath);
					} else {

						if (file_exists($tmpPath)) {
							// Удаляем файл.
							unlink($tmpPath);
						}
					}
				}
			}
			closedir($dirHandle);

			// Удаляем текущую папку.
			if (file_exists($path)) {
				rmdir($path);
				return true;
			}
		}
	}

	/**
	 * Добавляет картинку для использования в визуальном редакторе.
	 * @return bool
	 */
	public function upload() {
		new Upload(true, $_REQUEST['upload_dir']);
	}

	/**
	 * Добавляет картинку во временную папку для использования в визуальном редакторе.
	 * @return bool
	 */
	public function upload_tmp() {
		new Upload(true, 'prodtmpimg');
	}

	/**
	 * Подключает elfinder.
	 * @return bool
	 */
	public function elfinder() {

		include('mg-core/script/elfinder/php/connector.php');
	}

	
	/**
	 * Обрабатывает запрос на установку плагина.
	 * @return bool
	 */
	public function addNewPlugin() {
		if(USER::access('plugin') < 2) {
			$this->messageError = $this->lang['ACCESS_PLUGIN_INSTALL'];
			return false;
		}
		if (isset($_POST) && 'POST' == $_SERVER['REQUEST_METHOD']) {
			$file_array = $_FILES['addPlugin'];
			$downloadResult = PM::downloadPlugin($file_array);

			if ($downloadResult['data']) {
				$this->messageSucces = $downloadResult['msg'];
				PM::extractPluginZip($downloadResult['data']);
				return true;
			} else {
				$this->messageError = $downloadResult['msg'];
			}
		}
		return false;
	}

	/**
	 * Обрабатывает запрос на установку шаблона.
	 * @return bool
	 */
	public function addNewTemplate() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_TEMPLATE_INSTALL'];
			return false;
		}
		if (isset($_POST) && 'POST' == $_SERVER['REQUEST_METHOD']) {

			if (isset($_FILES['addLanding'])) {
				$file_array = $_FILES['addLanding'];
				$path = 'mg-templates/landings/';
			}
			else{
				$file_array = $_FILES['addTempl'];
				$path = 'mg-templates/';
			}

			//имя шаблона
			$name = $file_array['name'];
			//его размер
			$size = $file_array['size'];      
			//поддерживаемые форматы
			$validFormats = array('zip');

			$lang = MG::get('lang');

			if (strlen($name)) {
				$fullName = explode('.', $name);
				$ext = array_pop($fullName);
				$name = implode('.', $fullName);
				if (in_array($ext, $validFormats)) {
					if ($size < (1024 * 1024 * 10)) {
						$actualName = $name.'.'.$ext;
						$tmp = $file_array['tmp_name'];
						if (move_uploaded_file($tmp, $path.$actualName)) {
							$data = $path.$actualName;
							$msg = $this->lang['TEMPL_UPLOAD'];
						} else {
							$msg = $this->lang['TEMPL_UPLOAD_ERR'];
						}
					} else {
						$msg = $this->lang['TEMPL_UPLOAD_ERR2'];
					}
				} else {
					$msg = $this->lang['TEMPL_UPLOAD_ERR3'];
				}
			} else {
				$msg = $this->lang['TEMPL_UPLOAD_ERR4'];
			}

			if ($data) {
				$this->messageSucces = $msg;

				if (file_exists($data)) {
					$zip = new ZipArchive;
					$res = $zip->open($data, ZIPARCHIVE::CREATE);
					if ($res === TRUE) {
						$zip->extractTo($path);
						$zip->close();
						unlink($data);
						return true;
					}
				}
				$this->messageError = $this->lang['TEMPLATE_UNZIP_FAIL'];
				return false;
			} else {
				$this->messageError = $msg;
			}
		}

		return false;
	}
	
	/*
	 * Проверяет наличие обновлени плагинов
	 * @return bool
	 */
	public function checkPluginsUpdate() {
		if(USER::access('plugin') < 2) {
			$this->messageError = $this->lang['ACCESS_UPDATE_VIEW'];
			return false;
		}
		$this->messageSucces = $this->lang['ACT_PLUGIN_CHECK_UPD_SUCCESS'];
		$this->messageError = $this->lang['ACT_PLUGIN_CHECK_UPD_ERR'];
		
		if(!MG::libExists()) {
			return PM::checkPluginsUpdate();
		} else {
			$this->messageError = $this->lang['ACT_PLUGIN_CURL_NOT_INCLUDE'];
			return false;
		}
	}
	
	/*
	 * Выполняет обновление плагина
	 * @return bool
	 */
	public function updatePlugin() {
		if(USER::access('plugin') < 2) {
			$this->messageError = $this->lang['ACCESS_UPDATE_PLUGIN'];
			return false;
		}
		$this->messageSucces = $this->lang['ACT_PLUGIN_UPD'];
		$this->messageError = $this->lang['ACT_PLUGIN_UPD_ERR'];
		
		if(!MG::libExists()) {
			$update = true;
			$pluginName = $_POST['pluginName'];
			
			$data = PM::getPluginDir($pluginName);
			
			$update = PM::updatePlugin($pluginName, $data['dir'], $data['version']);
			
			if($data['last_version']) {
				$this->data['last_version'] = true;
			}
			
			if(!$update) {
				PM::failtureUpdate($pluginName, $data['version']);
				$failMsg = $this->lang['ACT_PLUGIN_UPD_ERR'];
				if (!is_writable(SITE_DIR.'mg-plugins'.DS.$pluginName)) {
					$failMsg .= ', нет прав на запись в папку плагина '.$pluginName;
				}
				$this->messageError = $failMsg;
				return false;
			}
			
			return true;
		} else {
			$this->messageError = $this->lang['ACT_PLUGIN_CURL_NOT_INCLUDE'];
			return false;
		}
	}

	/**
	 * Обрабатывает запрос на удаление плагина.
	 * @return bool
	 */
	public function deletePlugin() {
		if(USER::access('plugin') < 2) {
			$this->messageError = $this->lang['ACCESS_REMOVE_PLUGIN'];
			return false;
		}
		$this->messageSucces = $this->lang['ACT_PLUGIN_DEL'].$_POST['id'];
		$this->messageError = $this->lang['ACT_PLUGIN_DEL_ERR'];

		// удаление плагина из папки.
		// $documentroot = str_replace('mg-core'.DS.'lib', '', dirname(__FILE__));
		$documentroot = SITE_DIR;
		if (PM::deletePlagin($_POST['id']) && $this->removeDir($documentroot.'mg-plugins'.DS.$_POST['id'])) {
			return true;
		}
		return false;
	}

	/**
	 * Добавляет картинку товара.
	 * @return bool
	 */
	public function addImage() {
		$uploader = new Upload(false);
		//$uploader->deleteImageProduct($_POST['currentImg']);
		$tempData = $uploader->addImage(true);
		$this->data = array('img' => str_replace(array('30_', '70_'), '', $tempData['actualImageName']));
		if ($tempData['status'] == 'success') {
			$this->messageSucces = $tempData['msg'];
			return true;
		} else {
			$this->messageError = $tempData['msg'];
			return false;
		}
	}

	/**
	 * Добавляет картинки товаров.
	 * @return bool
	 */
	public function addImageMultiple() {
		$tmp = $_FILES;
		$_FILES = array();
		$total = 0;
		$succeeded = 0;
		$finalNames = array();
		foreach ($tmp['photoimg_multiple']['name'] as $key => $value) {
			$_FILES['photoimg']['name'] = $tmp['photoimg_multiple']['name'][$key];
			$_FILES['photoimg']['type'] = $tmp['photoimg_multiple']['type'][$key];
			$_FILES['photoimg']['tmp_name'] = $tmp['photoimg_multiple']['tmp_name'][$key];
			$_FILES['photoimg']['error'] = $tmp['photoimg_multiple']['error'][$key];
			$_FILES['photoimg']['size'] = $tmp['photoimg_multiple']['size'][$key];

			$uploader = new Upload(false);
			$tempData = $uploader->addImage(true);
			$total++;
			if ($tempData['actualImageName']) {
				$finalNames[] = str_replace(array('30_', '70_'), '', $tempData['actualImageName']);
				$succeeded++;
			}

		}

		$this->data = $finalNames;

		if ($total == 1 && $succeeded == 1) {
			$this->messageSucces = $tempData['msg'];
			return true;
		}

		if ($total == 1 && $succeeded == 0) {
			$this->messageError = $tempData['msg'];
			return false;
		}

		if ($total > 1 && $succeeded == $total) {
			$this->messageSucces = 'Все изображения загружены';
			return true;
		}

		if ($total > 1 && $succeeded < $total) {
			$this->messageError = 'Загружено '.$succeeded.' из '.$total.' изображений.';
			return false;
		}
		
	}

	/**
	 * Добавляет картинки товаров.
	 * @return string
	 */
	public function addImageUrl() {
		$tmp = str_replace(array('http://', 'https://'), '', $_POST['imgUrl']);
		$tmp = explode('/', $tmp);
		$domain = $tmp[0];
		require_once('mg-core/lib/idna_convert.class.php');
		$idn = new idna_convert(array('idn_version'=>2008));
		$punycode = $domain;
		$newDomain = (stripos($punycode, 'xn--')!==false) ? $idn->decode($punycode) : $idn->encode($punycode);
		if ($domain != $newDomain) {
			$_POST['imgUrl'] = str_replace($domain, $newDomain, $_POST['imgUrl']);
		}
		$headers = get_headers ($_POST['imgUrl'],1);
		if (in_array($headers['Content-Type'], array('image/webp', 'image/svg', 'image/svg+xml', 'image/jpeg', 'image/png', 'image/gif'))) {

			$_FILES = array();
			$_FILES['photoimg']['name'] = time().str_replace('image/', '.', $headers['Content-Type']);
			$_FILES['photoimg']['type'] = $headers['Content-Type'];
			$_FILES['photoimg']['tmp_name'] = $_POST['imgUrl'];
			$_FILES['photoimg']['error'] = 0;
			$_FILES['photoimg']['size'] = $headers['Content-Length'];
			$uploader = new Upload(false);
			if ($_POST['isCatalog'] == 'true') {
				$tempData = $uploader->addImage(true);
			}
			else{
				$tempData = $uploader->addImage(false, false, 'prodtmpimg');
			}
			if ($tempData['status'] == 'success') {
				$this->data = str_replace(array('30_', '70_'), '', $tempData['actualImageName']);
				$this->messageSucces = $tempData['msg'];
				return true;
			} else {
				$this->messageError = $tempData['msg'];
				return false;
			}
		} else {
			$this->messageError = $this->lang['IMAGE_OPEN_ERROR'];
			return false;
		}
	}

	/**
	 * Добавляет картинки товаров.
	 * @return string
	 */
	public function addImageUploader() {
		
		if ($_POST['imgType'] == 'image/jpeg' || $_POST['imgType'] == 'image/png' || $_POST['imgType'] == 'image/gif') {
			$_FILES = array();
			$_FILES['photoimg']['name'] = $_POST['imgName'];
			$_FILES['photoimg']['type'] = $_POST['imgType'];
			$_FILES['photoimg']['tmp_name'] = $_POST['imgUrl'];
			$_FILES['photoimg']['error'] = 0;
			$_FILES['photoimg']['size'] = $_POST['imgSize'];
			$uploader = new Upload(false);
			$tempData = $uploader->addImage(true);
			if ($tempData['status'] == 'success') {
				$this->data = str_replace(array('30_', '70_'), '', $tempData['actualImageName']);
				$this->messageSucces = $tempData['msg'];
				return true;
			} else {
				$this->messageError = $tempData['msg'];
				return false;
			}
		} else {
			$this->messageError = $this->lang['IS_NOT_IMAGE'];
			return false;
		}
	}
	
	 /**
	 * Удаляет картинку товара.
	 * @return bool
	 */
	public function deleteImageProduct() {
		if(USER::access('product') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$uploader = new Upload(false);
		$uploader->deleteImageProduct($_POST['imgFile'], $_POST['id']);
		$this->messageSucces = $this->lang['IMAGE_DELETE_FROM_SERVER'];
		return true; 
	}
	
	/**
	 * Удаляет изображения из временной папки, если товар не был сохранен.
	 */
	public function deleteTmpImages() {
		$arImages = explode('|', trim($_POST['images'], '|'));
		$product = new Models_Product();
		$product->deleteImagesProduct($arImages);
		return false;
	}
	
	/**
	 * Добавляет картинку без водяного знака.
	 * @return bool
	 */
	public function addImageNoWaterMark() {
		$uploader = new Upload(false);
		if (MG::getOption('waterMarkVariants')=='false') {
			$_POST['noWaterMark'] = true;
		}    
		$tempData = $uploader->addImage(true);
		$this->data = array('img' => $tempData['actualImageName']);
		// $documentroot = str_replace('mg-core'.DS.'lib', '', dirname(__FILE__));
		$documentroot = SITE_DIR;
		if ($tempData['status'] == 'success') {
			$this->messageSucces = $tempData['msg'];

			if ($_GET['oldimage'] != 'undefined') {
				if (file_exists($documentroot.'uploads'.DS.$_GET['oldimage'])) {
					// если старая картинка используется только в одном варианте, то она будет удалена         
					$res = DB::query('SELECT image FROM `'.PREFIX.'product_variant` WHERE image = '.DB::quote($_GET['oldimage']));
					if (DB::numRows($res) === 1) {
						unlink($documentroot.'uploads'.DS.$_GET['oldimage']);
					}
				}
			}
			return true;
		} else {
			$this->messageError = $tempData['msg'];
			return false;
		}
	}

	/**
	 * Удаляет категорию.
	 * @return bool
	 */
	public function deleteCategory() {
		if(USER::access('category') < 2) {
			$this->messageError = $this->lang['ACCESS_REMOVE_CATEGORY'];
			return false;
		}
		$this->messageSucces = $this->lang['ACT_DEL_CAT'];
		$this->messageError = $this->lang['ACT_NOT_DEL_CAT'];
		if ($_POST['dropProducts'] == 'true') {
			$cats = MG::get('category')->getCategoryList($_POST['id']);
			$cats[] = $_POST['id'];
			$cats = implode(', ', $cats);
			$model = new Models_Product;
			$res = DB::query('SELECT `id` FROM `'.PREFIX.'product` WHERE `cat_id` IN ('.$cats.')');
			while($row = DB::fetchAssoc($res)) {
				$model->deleteProduct($row['id']);
			}
		}
		
		 
		return MG::get('category')->delCategory($_POST['id']);
	}

	/**
	 * Удаляет страницу.
	 * @return bool
	 */
	public function deletePage() {
		if(USER::access('page') < 2) {
			$this->messageError = $this->lang['ACCESS_REMOVE_PAGE'];
			return false;
		}
		$this->messageSucces = $this->lang['ACT_DEL_PAGE'];
		$this->messageError = $this->lang['ACT_NOT_DEL_PAGE'];
		return MG::get('pages')->delPage($_POST['id']);
	}

	/**
	 * Удаляет пользователя.
	 * @return bool
	 */
	public function deleteUser() {
		if(USER::access('user') < 2) {
			$this->messageError = $this->lang['ACCESS_REMOVE_USER'];
			return false;
		}
		$this->messageSucces = $this->lang['ACT_DEL_USER'];
		$this->messageError = $this->lang['ACT_NOT_DEL_USER'];
		return USER::delete($_POST['id']);
	}

	/**
	 * Удаляет товар.
	 * @return bool
	 */
	public function deleteProduct() {
		if(USER::access('product') < 2) {
			$this->messageError = $this->lang['ACCESS_REMOVE_PRODUCT'];
			return false;
		}
		$this->messageSucces = $this->lang['ACT_DEL_PROD'];
		$this->messageError = $this->lang['ACT_NOT_DEL_PROD'];
		$model = new Models_Product;
		return $model->deleteProduct($_POST['id']);
	}

	/**
	 * Удаляет заказ.
	 * @return bool
	 */
	public function deleteOrder() {
		if(USER::access('order') < 2) {
			$this->messageError = $this->lang['ACCESS_REMOVE_ORDER'];
			return false;
		}
		$this->messageSucces = $this->lang['ACT_DEL_ORDER'];
		$this->messageError = $this->lang['ACT_NOT_DEL_ORDER'];
		$model = new Models_Order;
		$model->refreshCountProducts($_POST['id'], 4);
		$this->data = array('count' => $model->getNewOrdersCount());
		return $model->deleteOrder($_POST['id']);
	}

	/**
	 * Удаляет пользовательскую характеристику товара.
	 * @return bool
	 */
	public function deleteUserProperty() {
		if(USER::access('product') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}

		$res = DB::query('SELECT `plugin` FROM `'.PREFIX.'property` WHERE `id`='.DB::quoteInt($_POST['id']));
		if ($row = DB::fetchArray($res)) {
			$pluginDirectory = PLUGIN_DIR.$row['plugin'].'/index.php';
			if ($row['plugin']&&  file_exists($pluginDirectory)) {
				$this->messageError = $this->lang['ACT_NOT_DEL_PROP_PLUGIN'];
				$result = false;
				return $result;
			}
		}
		$this->messageSucces = $this->lang['ACT_DEL_PROP'];
		$this->messageError = $this->lang['ACT_NOT_DEL_PROP'];
		$result = false;
		if (DB::query('
			DELETE FROM `'.PREFIX.'property`
			WHERE id = '.DB::quote($_POST['id'], true)) &&
			DB::query('
			DELETE FROM `'.PREFIX.'product_user_property_data`
			WHERE prop_id = '.DB::quote($_POST['id'], true)) &&
			DB::query('
			DELETE FROM `'.PREFIX.'category_user_property`
			WHERE property_id = '.DB::quote($_POST['id'], true)) &&
			DB::query('
			DELETE FROM `'.PREFIX.'property_data`
			WHERE prop_id = '.DB::quote($_POST['id'], true))
		) {
			$result = true;
		}
		return $result;
	}

	/**
	 * Удаляет категорию.
	 * @return bool
	 */
	public function editCategory() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageSucces = $this->lang['ACT_EDIT_CAT'].' "'.$_POST['title'].'"';
		$this->messageError = $this->lang['ACT_NOT_EDIT_CAT'];

		$id = $_POST['id'];
		unset($_POST['id']);
		// Если назначаемая категория, является тойже.
		if ($_POST['parent'] == $id) {
			$this->messageError = $this->lang['ACT_ERR_EDIT_CAT'];
			return false;
		}

		$childsCaterory = MG::get('category')->getCategoryList($id);
		// Если есть вложенные, и одна из них назначена родительской.
		if (!empty($childsCaterory)) {
			foreach ($childsCaterory as $cateroryId) {
				if ($_POST['parent'] == $cateroryId) {
					$this->messageError = $this->lang['ACT_ERR_EDIT_CAT'];
					return false;
				}
			}
		}

		if ($_POST['parent'] == $id) {
			$this->messageError = $this->lang['ACT_ERR_EDIT_CAT'];
			return false;
		}
		return MG::get('category')->editCategory($id, $_POST);
	}

	/**
	 * Сохраняет курс валют.
	 * @return bool
	 */
	public function saveCurrency() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		} 
		$this->messageSucces = $this->lang['ACT_SAVE_CURR'];
		$this->messageError = $this->lang['ACT_NOT_SAVE_CURR'];

		$currencyActive =array();
	 
		foreach ($_POST['data'] as $currency) {
			if (!empty($currency['iso'])&&!empty($currency['short'])) {
				$currency['iso'] =  htmlspecialchars($currency['iso']);
				$currency['short'] =  htmlspecialchars($currency['short']);
				$currency['rate'] =  (float)($currency['rate']);
				if ($currency['active'] == 'true') {$currencyActive[] = $currency['iso'];}
				unset($currency['active']);
				$currencyShopRate[$currency['iso']] = $currency['rate'];
				$currencyShopShort[$currency['iso']] = $currency['short'];
			}
		}

		unset($currencyShopRate['']);
		unset($currencyShopShort['']);

		MG::setOption(array('option' => 'currencyRate', 'value' => addslashes(serialize($currencyShopRate))));
		MG::setOption(array('option' => 'currencyShort', 'value' => addslashes(serialize($currencyShopShort))));
		MG::setOption(array('option' => 'currencyActive', 'value' => addslashes(serialize($currencyActive))));
		
		$settings = MG::get('settings');  
		$settings['currencyRate'] = $currencyShopRate;
		$settings['currencyShort'] = $currencyShopShort;
		MG::set('settings', $settings );

		
		$product = new Models_Product();
		$product->updatePriceCourse(MG::getSetting('currencyShopIso'));

		return true;
	}

	/** Применяет скидку/наценку ко всем вложенным подкатегориям.
	 */
	public function applyRateToSubCategory() {
		if(USER::access('category') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		return true;
	}

	/**
	 * Отменяет скидку и наценку для выбраной категории.
	 * @return bool
	 */
	public function clearCategoryRate() {
		if(USER::access('product') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageSucces = $this->lang['ACT_CLEAR_CAT_RATE'];
		MG::get('category')->clearCategoryRate($_POST['id']);
		return true;
	}

	/**
	 * Сохраняет и обновляет параметры товара.
	 * @return bool
	 */
	public function saveProduct() {
		MG::resetAdminCurrency();
		$_POST = json_decode($_POST['data'], true);
		
		$this->messageSucces = $this->lang['ACT_SAVE_PROD'];
		$this->messageError = $this->lang['ACT_NOT_SAVE_PROD'];   
		if(USER::access('product') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		
		if ($_POST['price']) {
			$_POST['price'] = MG::numberDeFormat($_POST['price']);
		}
		if ($_POST['old_price']) {
			$_POST['old_price'] = MG::numberDeFormat($_POST['old_price']);
			$_POST['old_price'] = MG::convertOldPrice($_POST['old_price'], $_POST['currency_iso'], 'set');
		}
		if ($_POST['code']) {
			$_POST['code'] = str_replace(array(',','|'), '', $_POST['code']);
		}

		$model = new Models_Product;
		$itemId = 0;
		//Перед сохранением удалим все помеченные  картинки продукта физически с диска.        
		$_POST = $model->prepareImageName($_POST);

		$tmp  = explode("|", $_POST['image_url']);
		$_POST['image_url'] = $tmp[0];
		
		$images = explode("|", $_POST['image_url']);

		// if(count($_POST['variants']) == 1) {
		//   unset($_POST['variants']);
		// }
		
		foreach($_POST['variants'] as $cell => $variant) {
			$_POST['variants'][$cell]['code'] = str_replace(array(',','|'), '', $variant['code']);
			if ($variant['price']) {
				$_POST['variants'][$cell]['price'] = MG::numberDeFormat($variant['price']);
			}
			if ($variant['old_price']) {
				$variant['old_price'] = MG::numberDeFormat($variant['old_price']);
				$_POST['variants'][$cell]['old_price'] = MG::convertOldPrice($variant['old_price'], $variant['currency_iso'], 'set');
			}
			unset($_POST['variants'][$cell]['undefined']);
			unset($_POST['variants'][$cell]['variant_url']);
			$images[] = $variant['image'];

			$pos = strpos($variant['image'], '_-_time_-_');

			if ($pos) {
				if (MG::getSetting('addDateToImg') == 'true') {
					$tmp1 = explode('_-_time_-_', $variant['image']);
					$tmp2 = strrpos($tmp1[1], '.');
					$tmp1[0] = date("_Y-m-d_H-i-s", substr_replace($tmp1[0], '.', 10, 0));
					$_POST['variants'][$cell]['image'] = substr($tmp1[1], 0, $tmp2).$tmp1[0].substr($tmp1[1], $tmp2);
				}
				else{
					$_POST['variants'][$cell]['image'] = substr($variant['image'], ($pos+10));
				}
			}
		}

		foreach ($_POST['userProperty'] as $key => $value) {
			if($value['type'] == 'textarea') {
				foreach ($value as $key2 => $value2) {
					if($key2 != 'type') {
						$_POST['userProperty'][$key][$key2]['val'] = html_entity_decode(htmlspecialchars_decode($value2['val']));
						if (!empty($_POST['id']) && $_POST['userProperty'][$key][$key2]['val']) {
							$_POST['userProperty'][$key][$key2]['val'] = MG::moveCKimages($_POST['userProperty'][$key][$key2]['val'], 'product', $_POST['id'], 'prop', 'product_user_property_data', 'name', $key2);
						}
					}
				}
			}
		}
		
		if(!is_numeric($_POST['count'])) {
			$_POST['count'] = "-1";
		}

		// исключаем дублированные артикулы в строке связаных товаров
		if (!empty($_POST['related'])) {
			$_POST['related'] = implode(',', array_unique(explode(',', $_POST['related'])));
		}

		$clearImages = array();
		$clearImages2 = array();
		
		foreach ($images as $img) {
			$pos = strpos($img, '_-_time_-_');

			if ($pos) {
				if (MG::getSetting('addDateToImg') == 'true') {
					$tmp1 = explode('_-_time_-_', $img);
					$tmp2 = strrpos($tmp1[1], '.');
					$tmp1[0] = date("_Y-m-d_H-i-s", substr_replace($tmp1[0], '.', 10, 0));
					$clearImages[] = substr($tmp1[1], 0, $tmp2).$tmp1[0].substr($tmp1[1], $tmp2);
				}
				else{
					$clearImages[] = substr($img, ($pos+10));
				}
			}
			else{
				$clearImages[] = $img;
			}
		}

		$tmp = explode("|", $_POST['image_url']);

		foreach ($tmp as $img) {
			$pos = strpos($img, '_-_time_-_');

			if ($pos) {
				if (MG::getSetting('addDateToImg') == 'true') {
					$tmp1 = explode('_-_time_-_', $img);
					$tmp2 = strrpos($tmp1[1], '.');
					$tmp1[0] = date("_Y-m-d_H-i-s", substr_replace($tmp1[0], '.', 10, 0));
					$clearImages2[] = substr($tmp1[1], 0, $tmp2).$tmp1[0].substr($tmp1[1], $tmp2);
				}
				else{
					$clearImages2[] = substr($img, ($pos+10));
				}
			}
			else{
				$clearImages2[] = $img;
			}
		}
		if (!empty($clearImages2)) {
			$_POST['image_url'] = implode('|', $clearImages2);
		}

		//Обновление
		if (!empty($_POST['id'])) {
			$itemId = $_POST['id'];
			$_POST['updateFromModal'] = true; // флаг, чтобы отличить откуда было обновление  товара
			$model->updateProduct($_POST);
			$_POST['image_url'] = $clearImages[0];
			$_POST['currency'] = MG::getSetting('currency');
			$_POST['recommend'] = $_POST['recommend'];
			$tempProd = $model->getProduct($_POST['id'], true, true);     
			$arrVar = $model->getVariants($_POST['id']);
			foreach ($arrVar as $key => $variant) {
				$variant['image'] = basename($variant['image']); 
				$tempProd['variants'][] = $variant;
			}

			// костыль 
			$minVarPrice = array();
			foreach ($arrVar as $key => $value) {
				if(empty($minVarPrice)) {
					$minVarPrice['price'] = $value['price'];
					$minVarPrice['price_course'] = $value['price_course'];
				} else {
					if($minVarPrice['price_course'] > $value['price_course']) {
						$minVarPrice['price'] = $value['price'];
						$minVarPrice['price_course'] = $value['price_course'];
					}
				}
			}
			if($minVarPrice)
				DB::query('UPDATE '.PREFIX.'product SET '.DB::buildPartQuery($minVarPrice).' WHERE id = '.DB::quoteInt($_POST['id']));


			// $tempProd['variants'] = array($arrVar);
			$tempProd['real_price'] = $tempProd['price'];     
			$this->data = $tempProd;
		} else {  // добавление
			unset($_POST['delete_image']);
			$newProd = $model->addProduct($_POST);
			if(empty($_POST['id'])) {
				$_POST['id'] = $newProd['id'];
			}
			$itemId = $newProd['id'];
			$this->data['image_url'] = $clearImages[0];
			$this->data['currency'] = MG::getSetting('currency');
			$this->data['recommend'] = $_POST['recommend'];
			$tempProd = $model->getProduct($newProd['id'], true, true);     
			$arrVar = $model->getVariants($newProd['id']);
			foreach ($arrVar as $key => $variant) {
				$tempProd['variants'][] = $variant;
			}
			// $tempProd['variants'] = array($arrVar);
			$tempProd['real_price'] = $tempProd['price']; 
			$this->data = $tempProd;
		}
		
		if($arImages = explode('|', $_POST['delete_image'])) {
			$model->deleteImagesProduct($arImages, $itemId);
		}

		// сохранение цветов и размеров в виде параметров для характеристики, чтобы работало в фильтре
		// узнаем id характеристики цвета
		$res = DB::query('SELECT prop_id FROM '.PREFIX.'property_data WHERE id = '.DB::quoteInt($_POST['variants'][0]['color']));
		while($row = DB::fetchAssoc($res)) {
			$propIdColor = $row['prop_id'];
		}
		DB::query('DELETE FROM '.PREFIX.'product_user_property_data WHERE prop_id = '.DB::quoteInt($propIdColor).' 
				AND product_id = '.DB::quoteInt($_POST['id']));
		// узнаем id характеристики размера 
		$res = DB::query('SELECT prop_id FROM '.PREFIX.'property_data WHERE id = '.DB::quoteInt($_POST['variants'][0]['size']));
		while($row = DB::fetchAssoc($res)) {
			$propIdSize = $row['prop_id'];
		}
		// чистим базу от предположительно устаревших параметров размера и цвета товаров
		DB::query('DELETE FROM '.PREFIX.'product_user_property_data WHERE prop_id = '.DB::quoteInt($propIdSize).' 
			AND product_id = '.DB::quoteInt($_POST['id']));
		// забиваем новые параметры цвета и размера
		foreach ($_POST['variants'] as $item) {
			DB::query('INSERT INTO '.PREFIX.'product_user_property_data (prop_id, prop_data_id, product_id, active) VALUES 
				('.DB::quoteInt($propIdColor).', '.DB::quoteInt($item['color']).', '.DB::quoteInt($_POST['id']).', 1)');
			DB::query('INSERT INTO '.PREFIX.'product_user_property_data (prop_id, prop_data_id, product_id, active) VALUES 
				('.DB::quoteInt($propIdSize).', '.DB::quoteInt($item['size']).', '.DB::quoteInt($_POST['id']).', 1)');
		}

		$model->movingProductImage($images, $itemId, 'uploads/prodtmpimg');
		$image = (empty($clearImages[0])) ? 0 : $clearImages[0];
		$this->data['image_url'] = mgImageProductPath($image, $itemId);
		
		$this->data['sortshow'] = 'true';
		if (MG::getSetting('showCodeInCatalog') == 'true') {
			$this->data['codeshow'] = 'true';
		}
		if(MG::enabledStorage()) {
			foreach ($this->data['variants'] as $key => $value) {
				$this->data['variants'][$key]['count'] = MG::getProductCountOnStorage($value['count'], $this->data['id'], $value['id'], 'all');
			}
			$this->data['count'] = MG::getProductCountOnStorage($this->data['count'], $this->data['id'], 0, 'all');
		}


		Storage::clear('product-'.$_POST['id'], 'sizeMap-'.$_POST['id'], 'catalog', 'prop');
		return true;
	}

	/**
	 * Обновляет параметры товара (быстрый вариант).
	 * @return bool
	 */
	public function fastSaveProduct() {
		MG::resetAdminCurrency();
		$this->messageSucces = $this->lang['ACT_SAVE_PROD'];
		$this->messageError = $this->lang['ACT_NOT_SAVE_PROD'];
		if(USER::access('product') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$model = new Models_Product;
		$variant = $_POST['variant'];

		if ($_POST['field'] == 'price' && $_POST['value']) {
			$_POST['value'] = MG::numberDeFormat($_POST['value']);
		}
		
		unset($_POST['variant']);

		$arr = array(
			$_POST['field'] => $_POST['value']
		);
		
		if (substr($arr['price'], -2, 1) === ',' || substr($arr['price'], -2, 1) === '.') {
			$arr['price'] .= '0';
		}
		// Обновление.
		if ($variant) {

			if (substr($arr['price'], -3, 1) == ',') {
				$arr['price'] = MG::numberDeFormat($arr['price']);
			}

			$model->fastUpdateProductVariant($_POST['id'], $arr, $_POST['product_id']);
			$arrVar = $model->getVariants($_POST['product_id']);
			foreach ($arrVar as $key => $variant) {
				if ($variant['id'] == $_POST['id']) {
					$this->data = MG::priceCourse($variant['price_course']);
				}
			}

			// костыль 
			$minVarPrice = array();
			foreach ($arrVar as $key => $value) {
				if(empty($minVarPrice)) {
					$minVarPrice['price'] = $value['price'];
					$minVarPrice['price_course'] = $value['price_course'];
				} else {
					if($minVarPrice['price_course'] > $value['price_course']) {
						$minVarPrice['price'] = $value['price'];
						$minVarPrice['price_course'] = $value['price_course'];
					}
				}
			}
			if($minVarPrice)
				DB::query('UPDATE '.PREFIX.'product SET '.DB::buildPartQuery($minVarPrice).' WHERE id = '.DB::quoteInt($_POST['id']));
		} else {
			$model->fastUpdateProduct($_POST['id'], $arr);
			$tempProd = $model->getProduct($_POST['id']);
			$this->data = MG::priceCourse($tempProd['price_course']);
		}

		Storage::clear('product-'.$_POST['id'], 'sizeMap-'.$_POST['id'], 'catalog', 'prop');

		return true;
	}

	/**
	 * Перезаписывает новым значением, любое поле в любой таблице, в зависимости от входящих параметров.
	 */
	public function fastSaveContent() {
		if (!DB::query('
			 UPDATE `'.DB::quote($_POST['table'], true).'`
			 SET `'.DB::quote($_POST['field'], true).'` = '.DB::quote($_POST['content']).'
			 WHERE id = '.DB::quote($_POST['id'], true))) {
			return false;
		}
		return true;
	}

	/**
	 * Устанавливает флаг для вывода продукта в блоке рекомендуемых товаров.
	 * @return bool
	 */
	public function recomendProduct() {
		$this->messageSucces = $this->lang['ACT_PRINT_RECOMEND'];
		$this->messageError = $this->lang['ACT_NOT_PRINT_RECOMEND'];
		if(USER::access('product') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}

		$model = new Models_Product;
		// Обновление.
		if (!empty($_POST['id'])) {
			$model->updateProduct($_POST);
		}

		if ($_POST['recommend']) {
			return true;
		}

		return false;
	}

	/**
	 * Устанавливает флаг активности продукта.
	 * @return bool
	 */
	public function visibleProduct() {
		if(USER::access('page') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT_PRODUCT'];
			return false;
		}
		$this->messageSucces = $this->lang['ACT_V_PROD'];
		$this->messageError = $this->lang['ACT_UNV_PROD'];

		$model = new Models_Product;
		// Обновление.
		if (!empty($_POST['id'])) {
			$model->updateProduct($_POST);
		}

		if ($_POST['activity']) {
			return true;
		}

		return false;
	}

	/**
	 * Устанавливает флаг активности пользовательской характеристики.
	 * @return bool
	 */
	public function visibleProperty() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageSucces = $this->lang['ACT_V_PROP'];
		$this->messageError = $this->lang['ACT_UNV_PROP'];

		// Обновление.
		if (!empty($_POST['id'])) {
			DB::query("
				UPDATE `".PREFIX."property`
				SET `activity`= ".DB::quote($_POST['activity'])." 
				WHERE `id` = ".DB::quote($_POST['id'], true)
			);
		}

		if ($_POST['activity']) {
			return true;
		}

		return false;
	}

	/**
	 * Устанавливает флаг использования в фильтрах указанных характеристик.
	 * @return bool
	 */
	public function filterProperty() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageSucces = $this->lang['PROP_VIEWED_IN_FILTER'];
		$this->messageError = '';

		// Обновление.
		if (!empty($_POST['id'])) {
			DB::query("
				UPDATE `".PREFIX."property`
				SET `filter`= ".DB::quote($_POST['filter'])." 
				WHERE `id` = ".DB::quote($_POST['id'], true)
			);
		}

		if ($_POST['filter']) {
			return true;
		}

		return false;
	}

	/**
	 * Устанавливает флаг для использования характеристики в товарах.
	 * @return bool
	 */
	public function filterVisibleProperty() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageSucces = $this->lang['ACT_FILTER_PROP'];
		$this->messageError = $this->lang['ACT_UNFILTER_PROP'];

		// Обновление.
		if (!empty($_POST['id'])) {
			DB::query("
				UPDATE `".PREFIX."property`
				SET `filter`= ".DB::quote($_POST['filter'])." 
				WHERE `id` = ".DB::quote($_POST['id'], true)
			);
		}

		if ($_POST['filter']) {
			return true;
		}

		return false;
	}

	/**
	 * Устанавливает флаг для вывода продукта в блоке новых товаров.
	 * @return bool
	 */
	public function newProduct() {
		$this->messageSucces = $this->lang['ACT_PRINT_NEW'];
		$this->messageError = $this->lang['ACT_NOT_PRINT_NEW'];
		if(USER::access('product') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$model = new Models_Product;
		// Обновление.
		if (!empty($_POST['id'])) {
			$model->updateProduct($_POST);
		}

		if ($_POST['new']) {
			return true;
		}

		return false;
	}

	/**
	 * Устанавливает флаг для выбранной страницы, чтобы выводить ее в главном меню.
	 * @return bool
	 */
	public function printMainMenu() {
		if(USER::access('page') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageSucces = $this->lang['ADD_IN_MENU'];
		$this->messageError = $this->lang['NOT_ADD_IN_MENU'];


		// Обновление.
		if (!empty($_POST['id'])) {
			MG::get('pages')->updatePage($_POST);
		}

		if ($_POST['print_in_menu']) {
			return true;
		}

		return false;
	}

	/**
	 * Печать заказа.
	 */
	public function printOrder() {
		if(USER::access('order') == 0) {
			$this->messageError = $this->lang['ACCESS_VIEW'];
			return false;
		}
		if (!$_POST['template']) {
			$_POST['template'] = "order";
		}
		$this->messageSucces = $this->lang['ACT_PRINT_ORD'];
		$model = new Models_Order;    
		$this->data = array('html' => $model->printOrder($_POST['id'], true, $_POST['template']));
		return true;
	}

	/**
	 * Получает данные по промокоду.
	 */
	public function getPromoCode() {
		$this->messageSucces = $this->lang['DISCOUNT_APPLY'];
		// Заменить на получение скидки.
		$codes = array();
		// Запрос для проверки , существуют ли промокоды.  
		$result = DB::query('SHOW TABLES');
		while ($row = DB::fetchArray($result)) {
			if (PREFIX.'promo-code' == $row[0]) {
				$res = DB::query('SELECT code, percent FROM `'.PREFIX.'promo-code` WHERE invisible = 1');
				while ($code = DB::fetchAssoc($res)) {
					$codes[$code['code']] = $code['percent'];
				}
			};
		}
		$percent = $codes[$_POST['promocode']] ? $codes[$_POST['promocode']] : 0;
		$this->data = array('percent' => $percent, 'codes' => array('DEFAULT-DISCONT', 'DEFAULT-DISCONT2'));
		return true;
	}
	/**
	 * Получает данные по промокоду.
	 */
	public function getDiscount() {
		// Заменить на получение скидки.
		$percent = 0;
		
		$order = new Models_Order();
		$percent = $order->getOrderDiscount($_POST);
		
		$this->data = $percent;
		return true;
	}

	/**
	 * Получает данные по вводимому email в форме заказа.
	 * @return bool
	 */
	public function getUserEmail() {
		$emails = array('mark-avdeev@mail.ru', 'mark-avdeev2@mail.ru');
		$this->data = $emails;
		return true;
	}

	/**
	 * Сохраняет и обновляет параметры заказа.
	 * @return bool
	 */
	public function saveOrder() {
		if(USER::access('order') < 2) { 
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}

		$_POST['order_content'] = json_decode($_POST['order_content'], true);

		$this->messageSucces = $this->lang['ACT_SAVE_ORD'];
		$this->messageError = $this->lang['ACT_SAVE_ORDER'];

		if ($_POST['address_parts']) {
			$_POST['address_parts'] = json_decode($_POST['address_parts'], true);
			$tmp = array_filter($_POST['address_parts']);
			if (empty($tmp)) {
				$_POST['address_parts'] = '';
			}
			else{
				$_POST['address_parts'] = addslashes(serialize($_POST['address_parts']));
				$_POST['address'] = '';
			}
			
		}

		if (count($_POST['order_content']) != $_POST['orderPositionCount']) {
			$this->messageError = $this->lang['ORDER_TO_BIG'];
			return false;
		}
		MG::resetAdminCurrency();
		unset($_POST['orderPositionCount']);   

		$shopCurr = MG::getSetting('currencyShopIso');
		if ($_POST['currency_iso'] == $shopCurr) {
			$_POST['summ_shop_curr'] = $_POST['summ'];
			$_POST['delivery_shop_curr'] = $_POST['delivery_cost'];
		} else {
			$rates = MG::getSetting('currencyRate');
			$_POST['summ_shop_curr'] = (float)round($_POST['summ']*$rates[$_POST['currency_iso']],2);
			$_POST['delivery_shop_curr'] = (float)round($_POST['delivery_cost']*$rates[$_POST['currency_iso']],2);
		}
		
		// Cобираем воедино все параметры от юр. лица если они были переданы, для записи в информацию о заказе.
		$_POST['yur_info'] = '';
		$informUser = $_POST['inform_user'];
		if ($informUser == 'true') {
			$informUserText = $_POST['inform_user_text'];
		} else {
			$informUserText = '';
		}
		
		unset($_POST['inform_user']);
		unset($_POST['inform_user_text']);
		
		if (!empty($_POST['inn'])) {
			$_POST['yur_info'] = array(
				'email' => htmlspecialchars($_POST['orderEmail']),
				'name' => htmlspecialchars($_POST['orderBuyer']),
				'address' => htmlspecialchars($_POST['orderAddress']),
				'phone' => htmlspecialchars($_POST['orderPhone']),
				'inn' => htmlspecialchars($_POST['inn']),
				'kpp' => htmlspecialchars($_POST['kpp']),
				'nameyur' => htmlspecialchars($_POST['nameyur']),
				'adress' => htmlspecialchars($_POST['adress']),
				'bank' => htmlspecialchars($_POST['bank']),
				'bik' => htmlspecialchars($_POST['bik']),
				'ks' => htmlspecialchars($_POST['ks']),
				'rs' => htmlspecialchars($_POST['rs']),
			);
		}

		$customFields = $_POST['customFields'];
		unset($_POST['customFields']);
		$id = $_POST['id'];

		$model = new Models_Order;

		// Обновление.
		if (!empty($_POST['id'])) {
			unset($_POST['inn']);
			unset($_POST['kpp']);
			unset($_POST['nameyur']);
			unset($_POST['adress']);
			unset($_POST['bank']);
			unset($_POST['bik']);
			unset($_POST['ks']);
			unset($_POST['rs']);
			unset($_POST['ogrn']);

			if (!empty($_POST['yur_info'])) {
				$_POST['yur_info'] = addslashes(serialize($_POST['yur_info']));
			}

			$_POST['delivery_cost'] = MG::numberDeFormat($_POST['delivery_cost']);
			$_POST['order_content'] = addslashes(serialize($_POST['order_content']));
			
			$model->refreshCountAfterEdit($_POST['id'], $_POST['order_content']);

			// возвращаем товары на склад если заказ отменен
			// узнаем статус который был раньше
			$res = DB::query('SELECT status_id FROM '.PREFIX.'order WHERE id = '.DB::quoteInt($_POST['id']));
			$row = DB::fetchAssoc($res);
			$statusOld = $row['status_id'];
			// если был не отмена, а стал отменга, то возвращаем товары
			if(($statusOld != 4) && ($_POST['status_id'] == 4)) {


			}
			// если был отменен, то возвращаем товары в заказ
			if(($statusOld == 4) && ($_POST['status_id'] != 4)) {


			}

			// $model->refreshCountProducts($_POST['id'], $_POST['status_id']); // TODO
			
			$model->updateOrder($_POST, $informUser, $informUserText);
			
			if (in_array($_POST['status_id'], array(2, 5)) && method_exists($model, 'sendLinkForElectro')) {        
				$model->sendLinkForElectro($_POST['id']);
			}
		} else {
			
			if(!USER::getUserInfoByEmail($_POST['user_email'])) {
				$model->passNewUser = MG::genRandomWord(10);
			};	
		
			$newUserData = array(
				'email' => htmlspecialchars($_POST['user_email']),
				'role' => 2,
				'name' => htmlspecialchars($_POST['name_buyer']),
				'pass' => $model->passNewUser,
				'address' => htmlspecialchars($_POST['address']),
				'phone' => htmlspecialchars($_POST['phone']),
				'inn' => htmlspecialchars($_POST['inn']),
				'kpp' => htmlspecialchars($_POST['kpp']),
				'nameyur' => htmlspecialchars($_POST['nameyur']),
				'adress' => htmlspecialchars($_POST['adress']),
				'bank' => htmlspecialchars($_POST['bank']),
				'bik' => htmlspecialchars($_POST['bik']),
				'ks' => htmlspecialchars($_POST['ks']),
				'rs' => htmlspecialchars($_POST['rs']),
			);
			if ($_POST['user_email'] != '') {
				USER::add($newUserData);
			}       

			$orderArray = $model->addOrder($_POST);
			$id = $orderArray['id'];
			$orderNumber = $orderArray['orderNumber'];
			$this->messageSucces = $this->lang['ACT_SAVE_ORD'].' № '.$orderNumber;
			$_POST['id'] = $id;
			$_POST['newId'] = $id;
			$_POST['number'] = $orderNumber;
			$_POST['date'] = MG::dateConvert(date('d.m.Y H:i'));
		}
		

		$_POST['count'] = $model->getNewOrdersCount();
		$_POST['date'] = MG::dateConvert(date('d.m.Y H:i'));
		$this->data = $_POST;
		return true;
	}

	/**
	 * Сохраняет и обновляет параметры категории.
	 * @return bool
	 */
	public function saveCategory() {
		if(USER::access('category') < 2) { 
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		foreach ($_POST['delImgs'] as $value) {
			$value = str_replace(SITE.'/', SITE_DIR, $value);
			if ($_POST['id'] && strpos($value, '/uploads/category/'.$_POST['id'].'/')) {
				@unlink($value);
			}
		}
		unset($_POST['delImgs']);
		$this->messageSucces = $this->lang['ACT_SAVE_CAT'];
		$this->messageError = $this->lang['ACT_NOT_SAVE_CAT'];
		$_POST['image_url'] = $_POST['image_url'] ? str_replace(SITE, '', $_POST['image_url']) : '';
		$_POST['menu_icon'] = $_POST['menu_icon'] ? str_replace(SITE, '', $_POST['menu_icon']) : '';
		$_POST['parent_url'] = MG::get('category')->getParentUrl($_POST['parent']);
		// Обновление.
		if (!empty($_POST['id'])) {
			if (MG::get('category')->updateCategory($_POST)) {
				$this->data = $_POST;
			} else {
				return false;
			}
		} else {  // добавление
			unset($_POST['lang']);
			$this->data = MG::get('category')->addCategory($_POST);
		}
		return true;
	}

	/**
	 * Сохраняет и обновляет параметры страницы.
	 * @return bool
	 */
	public function savePage() {
		$this->messageSucces = $this->lang['ACT_SAVE_PAGE'];
		$this->messageError = $this->lang['ACT_NOT_SAVE_PAGE'];
		if(USER::access('page') < 2) { 
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}

		$_POST['parent_url'] = MG::get('pages')->getParentUrl($_POST['parent']);
		// Обновление.
		if (!empty($_POST['id'])) {
			if (MG::get('pages')->updatePage($_POST)) {
				$this->data = $_POST;
			} else {
				return false;
			}
		} else {  // добавление
			unset($_POST['lang']);
			$this->data = MG::get('pages')->addPage($_POST);
		}
		return true;
	}

	/**
	 * Делает страницу невидимой в меню.
	 * @return bool
	 */
	public function invisiblePage() {
		if(USER::access('page') < 2) { 
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageError = $this->lang['ACT_NOT_SAVE_PAGE'];
		if ($_POST['invisible'] === "1") {
			$this->messageSucces = $this->lang['ACT_UNV_PAGE'];
		} else {
			$this->messageSucces = $this->lang['ACT_V_PAGE'];
		}
		// Обновление.
		if (!empty($_POST['id']) && isset($_POST['invisible'])) {
			MG::get('pages')->updatePage($_POST);
		} else {
			return false;
		}
		return true;
	}

	/**
	 * Делает категорию невидимой в меню.
	 * @return bool
	 */
	public function invisibleCat() {
		if(USER::access('category') < 2) { 
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageError = $this->lang['ACT_NOT_SAVE_CAT'];
		if ($_POST['invisible'] === "1") {
			$this->messageSucces = $this->lang['ACT_UNV_CAT'];
		} else {
			$this->messageSucces = $this->lang['ACT_V_CAT'];
		}
		$array = $_POST;
		// Обновление.
		if (!empty($_POST['id']) && isset($_POST['invisible'])) {
			MG::get('category')->updateCategory($_POST);
			$arrayChildCat = MG::get('category')->getCategoryList($array['id']);
			foreach ($arrayChildCat as $ch_id) {
				$array['id'] = $ch_id;
				MG::get('category')->updateCategory($array);
			}
		} else {
			return false;
		}
		return true;
	}
	 /**
	 * Делает категорию активной/неактивной и товары в ней.
	 * @return bool
	 */
	public function activityCat() {
		if(USER::access('category') < 2) { 
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageError = $this->lang['ACT_NOT_SAVE_CAT'];
		if ($_POST['activity'] === "1") {
			$this->messageSucces = $this->lang['ACT_V_CAT_ACT'];
		} else {
			$this->messageSucces = $this->lang['ACT_UNV_CAT_ACT'];
		}
		// Обновление.
		if (!empty($_POST['id']) && isset($_POST['activity'])) {
			MG::get('category')->updateCategory($_POST);
			DB::query('UPDATE `'.PREFIX.'product` SET `activity`='.DB::quote($_POST['activity']).' WHERE `cat_id`='.DB::quoteInt($_POST['id']));
		} else {
			return false;
		}
		return true;
	}
	
	/**
	 * Устанавливает флаг экпорта категории.
	 * @return bool
	 */
	public function exportCatStatus() {
		if(USER::access('category') < 2) { 
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageError = $this->lang['ACT_NOT_SAVE_CAT'];
		if ($_POST['export'] === "1") {
			$this->messageSucces = $this->lang['ACT_EXPORT_CAT'];
		} else {
			$this->messageSucces = $this->lang['ACT_NOT_EXPORT_CAT'];
		}
		// Обновление.
		if (!empty($_POST['id']) && isset($_POST['export'])) {
			MG::get('category')->updateCategory($_POST);
			$childIds = MG::get('category')->getCategoryList($_POST['id']);
			foreach($childIds as $id) {
				$_POST['id'] = $id;
				MG::get('category')->updateCategory($_POST);
			}
		} else {
			return false;
		}
		return true;
	}

	/**
	 * Делает все страницы видимыми в меню.
	 * @return bool
	 */
	public function refreshVisiblePage() {
		if(USER::access('page') < 2) { 
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		MG::get('pages')->refreshVisiblePage();
		$this->messageSucces = $this->lang['ACT_PINT_IN_MENU'];
		return true;
	}

	/**
	 * Сохраняет и обновляет параметры пользователя.
	 * @return bool
	 */
	public function saveUser() {
		if(USER::access('user') < 2) { 
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageSucces = $this->lang['ACT_SAVE_USER'];
		$this->messageError = $this->lang['ACT_NOT_SAVE_USER'];

		// Обновление.
		if (!empty($_POST['id'])) {

			$res = DB::query('SELECT `id` FROM `'.PREFIX.'user` WHERE `email` = '.db::quote($_POST['email']));
			if ($row = DB::fetchAssoc($res)) {
				if ((int)$_POST['id'] !== (int)$row['id']) {
					$this->messageError = $this->lang['USER_DUPLICATE_EMAIL'];
					return false;
				}
			}

			// если пароль не передан значит не обновляем его
			if (empty($_POST['pass'])) {
				unset($_POST['pass']);
			} else {
				$_POST['pass'] = crypt($_POST['pass']);
			}

			//вычисляем надо ли перезаписать данные текущего пользователя после обновления
			//(только в том случае если из админки меняется запись текущего пользователя)
			$authRewrite = $_POST['id'] != User::getThis()->id ? true : false;

			// если происходит попытка создания нового администратора от лица модератора, то вывести ошибку
			if ($_POST['role'] == '1') {
				if (!USER::AccessOnly('1')) {
					return false;
				}
			}
			if ($_POST['birthday']) {
				$_POST['birthday'] = date('Y-m-d', strtotime($_POST['birthday']));  
			}
			if (User::update($_POST['id'], $_POST, $authRewrite)) {
				$this->data = $_POST;
			} else {
				return false;
			}
		} else {  // добавление	
			if ($_POST['role'] == '1') {
				if (!USER::AccessOnly('1')) {
					return false;
				}
			}

			try {
				$_POST['id'] = User::add($_POST);
			} catch (Exception $exc) {
				$this->messageError = $this->lang['ACT_ERR_SAVE_USER'];
				return false;
			}

			// TODO
			//отправка письма с информацией о регистрации
			$siteName = MG::getSetting('sitename');
			$userEmail = $_POST['email'];
			$message = '
				Здравствуйте!<br>
					Вы получили данное письмо так как на сайте '.$siteName.' зарегистрирован новый пользователь с логином '.$userEmail.'.<br>
					Отвечать на данное сообщение не нужно.';
			$emailData = array(
				'nameFrom' => $siteName,
				'emailFrom' => MG::getSetting('noReplyEmail'),
				'nameTo' => 'Пользователю сайта '.$siteName,
				'emailTo' => $userEmail,
				'subject' => 'Активация пользователя на сайте '.$siteName,
				'body' => $message,
				'html' => true
			);
			Mailer::sendMimeMail($emailData);

			$_POST['date_add'] = date('d.m.Y H:i');
			$this->data = $_POST;
		}
		return true;
	}

	/**
	 * Изменяет настройки.
	 * @return bool
	 */
	public function editSettings() {
		if(USER::access('setting') < 2) { 
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageSucces = $this->lang['ACT_SAVE_SETNG'];
		Storage::clear();
		MG::resetAdminCurrency();

		if (!empty($_POST['options'])) {

			$optionsIntValue = array('categoryImgHeight','categoryImgWidth','heightPreview','widthPreview',
				'heightSmallPreview','widthSmallPreviews','countСatalogProduct','countNewProduct',
				'countRecomProduct','countSaleProduct');
			// если произошла смена валюты магазина, то пересчитываем курсы
			$currencyShopIso = MG::getSetting('currencyShopIso');
			if ($_POST['options']['currencyShopIso'] != MG::getSetting('currencyShopIso')) {
				$currencyRate = MG::getSetting('currencyRate');
				$currencyShort = MG::getSetting('currencyShort');

        $_POST['options']['currency'] = $currencyShort[$_POST['options']['currencyShopIso']];

				$product = new Models_Product();
				$product->updatePriceCourse($_POST['options']['currencyShopIso']);

				//  $currencyRate[$currencyShopIso] = 1/$currencyRate[$_POST['options']['currencyShopIso']];
				$rate = $currencyRate[$_POST['options']['currencyShopIso']];
				$currencyRate[$_POST['options']['currencyShopIso']] = 1;
				foreach ($currencyRate as $iso => $value) {
					if ($iso != $_POST['options']['currencyShopIso']) {
						if (!empty($rate)) {
							$currencyRate[$iso] = $value / $rate;
						}
					}
				}
				unset($currencyRate['']);
				DB::query("UPDATE `".PREFIX."delivery` SET cost = ROUND(cost * ".$currencyRate[$currencyShopIso].", 3) , free = ROUND(free * ".$currencyRate[$currencyShopIso].', 3)');

				
				MG::setOption(array('option' => 'currencyRate', 'value' => addslashes(serialize($currencyRate))));

				// echo $_POST['options']['currencyShopIso'];      
			}


			$errorMemcache = false;
			
			foreach ($_POST['options'] as $option => $value) {

				if($value == 'MEMCACHE') {
					$_POST['host'] = $_POST['options']['cacheHost'];
					$_POST['port'] = $_POST['options']['cachePort'];
					if (!self::testMemcacheConection()) {
						$value = 'DB';
						$errorMemcache = true;
					}
				}
				if ($value == 'favicon.ico') {
					unlink('favicon.ico');
					rename('favicon-temp.ico', 'favicon.ico');
				}
				if($option == 'shopLogo' || $option == 'backgroundSite') {
					$value = str_replace(SITE, '', $value);
				}
				if ($option == 'robots' && !empty($value)) {
					$f = fopen('robots.txt', 'w');
					$result = fwrite($f, $value);
					fclose($f);
					unset($_POST['options']['robots']);
				}
				if ($option == 'smtpPass') {
					$value = CRYPT::mgCrypt($value);
				}
				if (in_array($option, $optionsIntValue)) {
					$value = intval($value);
				}
				if (!DB::query("UPDATE `".PREFIX."setting` SET `value`=".DB::quote($value)." WHERE `option`=".DB::quote($option)."")) {
					return false;
				}
			}
			if ($errorMemcache) {
				return false;
			}
			$this->messageSucces = $this->lang['ACT_SAVE_SETNG'];
			return true;
		}
	}
	

	/**
	 * Метод возвращает соотношение столбцов в импортируемом файле (CSV) с настройками
	 * @return array
	 */
	public function getCsvCompliance() {    
		$importType = (empty($_POST['importType'])) ? 'MogutaCMS' : $_POST['importType'];
		$scheme = (empty($_POST['scheme'])) ? 'default' : $_POST['scheme'];
		
		if($scheme != 'default') {     
			$cmpData = MG::getOption('csvImport-'.$scheme.$importType.'ColComp');      
			$cmpData = unserialize(stripslashes($cmpData));      
		}
		
		if(empty($cmpData)) {
			foreach(Import::$maskArray[$importType] as $id=>$title) {
				$cmpData[$id] = $id;
			}
		}
		
		$notUpdateList = MG::getOption('csvImport-'.$importType.'-notUpdateCol');
		$notUpdateColAr = explode(",", $notUpdateList);
		$notUpdateAr = array();   
		
		foreach(Import::$fields[$importType] as $id=>$title) {
			$notUpdate = 0;

			if(in_array($id, $notUpdateColAr) && $scheme == 'last' && $importType == 'MogutaCMS') {
				$notUpdate = 1;
			}

			$notUpdateAr[$id] = $notUpdate;        
		}    

		// для превью
		if($_SESSION['importType'] != 'excel') {
			$file = new SplFileObject("uploads/importCatalog.csv");
			$file->seek(0);
			$data = array();
			while(!$file->eof()) {    
				if($rowId > 5) break;   
				$rowId++; 
				$data = $file->fgetcsv(";");   
				foreach($data as $k => $v) {
					$data[$k] = str_replace(' ',' ',iconv("WINDOWS-1251", "UTF-8", $v));
				}
				if($rowId == 1) {
					$html = '<thead>';
					foreach ($data as $item) {
						$html .= '<th style="white-space:nowrap;border-right:1px solid;padding-left:5px;background:#e6e6e6;" class="border-color">'.$item.'</th>';
					}
					$html .= '</thead>';
				} else {
					$html .= '<tr>';
					foreach ($data as $item) {
						$html .= '<td style="white-space:nowrap;border-right:1px solid;padding-left:5px;" class="border-color">'.htmlspecialchars($item).'</td>';
					}
					$html .= '</tr>';
				}
			}   
		} else {
			include_once CORE_DIR.'script/excel/PHPExcel/IOFactory.php';
			include_once CORE_DIR.'script/excel/chunkReadFilter.php';  

			$file = "uploads/importCatalog.xlsx";
			
			$chunkFilter = new chunkReadFilter();    
			$chunkFilter->setRows(0, 7);
			$objReader = PHPExcel_IOFactory::createReaderForFile($file);    
			$objReader->setReadFilter($chunkFilter);
			$objReader->setReadDataOnly(true);    
			$objPHPExcel = $objReader->load($file);
			$sheet = $objPHPExcel->getActiveSheet();
			$colNumber = PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn());

			$html = '<thead><tr>';
			for($c = 0; $c < $colNumber; $c++) {
				$html .= '<th style="white-space:nowrap;border-right:1px solid;padding-left:5px;background:#e6e6e6;" class="border-color">'.
					$sheet->getCellByColumnAndRow($c, 1)->getValue().'</th>';
			}
			$html .= '</tr></thead>';
 
			for($r = 2; $r <= 7; $r++) {
				$html .= '<tr>';
				for($c = 0; $c < $colNumber; $c++) {
					$html .= '<td style="white-space:nowrap;border-right:1px solid;padding-left:5px;" class="border-color">'.
						$sheet->getCellByColumnAndRow($c, $r)->getValue().'</td>';
				}
				$html .= '</tr>';
			}

			unset($objReader); 
			unset($objPHPExcel); 
		}

		if(!empty($html)) {
			$html = '<table class="main-table border-color" style="border: 1px solid;border-width:1px 0 1px 1px;">'.$html.'</table>';
		}
		
		$this->data['csvPreview'] = $html;
		$this->data['compliance'] = $cmpData;
		$this->data['notUpdate'] = $notUpdateAr;
		$this->data['maskArray'] = Import::$maskArray[$importType];
		$this->data['fieldsInfo'] = Import::$fieldsInfo[$importType];
		$this->data['requiredFields'] = Import::$requiredFields[$importType];
		$this->data['titleList'] = Import::getTitleList();
		
		return true;
	}
	
	/**
	 * Устанавливает соответсвие столбцов при импорте из CSV
	 * @return bool true
	 */
	public function setCsvCompliance() {
		$importType = (empty($_POST['importType'])) ? 'MogutaCMS' : $_POST['importType'];
		
		if(!empty($_POST['data'])) {      
			$complianceArray = array();
		
			foreach($_POST['data']['compliance'] as $key=>$index) {
				$id = intval(substr($key, 8));
				$complianceArray[$id] = $index;
			}    

			MG::setOption(array('option' => 'csvImport-last'.$importType.'ColComp', 'value' => addslashes(serialize($complianceArray))));
			
			if(!empty($_POST['data']['not_update'])) {
				$notUpdateList = '';
				
				foreach($_POST['data']['not_update'] as $key=>$index) {
					$id = intval(substr($key, 9));
					$notUpdateList .= $id.',';
				} 
				
				$notUpdateList = substr($notUpdateList, 0, -1);
				
				MG::setOption(array('option' => 'csvImport-'.$importType.'-notUpdateCol', 'value' => $notUpdateList));
			}
		} else {
			$cpmArray = array();      
			$colTitles = Import::$maskArray[$importType];
			$titleList = Import::getTitleList();    

			foreach($colTitles as $id=>$title) {
				if($key = array_search($title, $titleList)) {
					$cpmArray[$id] = $key;
				} 
			}               

			MG::setOption(array('option' => 'csvImport-auto'.$importType.'ColComp', 'value' => addslashes(serialize($cpmArray))));
		}
				
		return true;
	}

	/**
	 * Получает параметры редактируемого продукта.
	 */
	public function getProductData() {
		MG::resetAdminCurrency();

		$this->messageError = $this->lang['ACT_NOT_GET_POD'];

		$model = new Models_Product;
		// устанавливаем склад для загрузки количества


		$product = $model->getProduct($_POST['id'], true, true);
		
		$maskField = array('title','meta_title','meta_keywords','meta_desc','image_title','image_alt');
		foreach ($product as $k => $v) {
			 if(in_array($k, $maskField)) {
				$product[$k] = htmlspecialchars_decode($v);  
			 }
		}
		if (!$product['code']) {
			$product['code'] = MG::getSetting('prefixCode').$product['id'];
		}
		
		if (empty($product)) {
			return false;
		}
		$this->data = $product;

		foreach($this->data['images_product'] as $cell => $image) {
			$this->data['images_product'][$cell] = mgImageProductPath($image, $product['id']);
		}
		
		// Получаем весь набор пользовательских характеристик.
		$res = DB::query("SELECT * FROM `".PREFIX."property`");
		while ($userFields = DB::fetchAssoc($res)) {
			$this->data['allProperty'][] = $userFields;
		}

		$variants = $model->getVariants($_POST['id']);
		foreach ($variants as $variant) {
			$variant['image'] = mgImageProductPath($variant['image'], $product['id'], 'small');
			if (!$variant['code']) {
				$variant['code'] =  MG::getSetting('prefixCode').$product['id'].'_'.$variant['id'];
			}
			$this->data['variants'][] = $variant;      
		}

		$stringRelated = ' null';
		$sortRelated = array();
		if (!empty($product['related'])) {
			foreach (explode(',', $product['related']) as $item) {
				$stringRelated .= ','.DB::quote($item);
				if (!empty($item)) {
					$sortRelated[$item] = $item;
				}
			}
			$stringRelated = substr($stringRelated, 1);
		}

		//$productsRelated = $model->getProductByUserFilter(' id IN ('.($product['related']?$product['related']:'0').')');
		$res = DB::query('
			SELECT  CONCAT(c.parent_url,c.url) as category_url,
				p.url as product_url, p.id, p.image_url,p.price_course as price,p.title,p.code
			FROM `'.PREFIX.'product` p
				LEFT JOIN `'.PREFIX.'category` c
				ON c.id = p.cat_id
				LEFT JOIN `'.PREFIX.'product_variant` AS pv
				ON pv.product_id = p.id
			WHERE p.code IN ('.$stringRelated.') OR pv.code IN ('.$stringRelated.')');

		while ($row = DB::fetchAssoc($res)) {
			$img = explode('|', $row['image_url']);
			$row['image_url'] = $img[0];
			$sortRelated[$row['code']] = $row;
		}
		$productsRelated = array();
		//сортируем связанные товары в том порядке, в котором они идут в строке артикулов

		if (!empty($sortRelated)) {
			foreach ($sortRelated as $item) {
				if (is_array($item)) {
					$item['image_url'] = mgImageProductPath($item['image_url'], $item['id'], 'small');
					$productsRelated[] = $item;
				}
			}
		}
		$relatedCat = array();
		if ($product['related_cat']) {
			$res =  DB::query('SELECT `id`, `title`, `url`, `parent_url`, `image_url` FROM `'.PREFIX.'category` WHERE `id` IN ('.DB::quote($product['related_cat'], true).')');
			while ($row = DB::fetchArray($res)) {
				$relatedCat[] = $row;
			}      
		}

		
		$this->data['relatedCat'] = $relatedCat;
		$this->data['relatedArr'] = $productsRelated;
		$_POST['produtcId'] = $_POST['id'];
		$_POST['categoryId'] = $product['cat_id'];
		$tempDataResult = $this->data;
		$this->data = null;
		$this->getProdDataWithCat();
		$tempDataResult['prodData'] = $this->data;
		$tempDataResult['old_price'] = MG::convertOldPrice($tempDataResult['old_price'], $tempDataResult['currency_iso'], 'get');
		if (is_array($tempDataResult['variants'])) {
			foreach ($tempDataResult['variants'] as $key => $value) {
				$tempDataResult['variants'][$key]['old_price'] = MG::convertOldPrice($value['old_price'], $value['currency_iso'], 'get');
			}
		}
		$this->data = $tempDataResult;
		//$this->data['prodData'] = $this->getProdDataWithCat();

		return true;
	}

	/**
	 * Получает параметры для категории продуктов.
	 */
	public function getProdDataWithCat() {
		$this->data['allProperty'] = array();
		$this->data['thisUserFields'] = array();

		// Получаем заданные ранее пользовательские характеристики для редактируемого товара.
		$res = DB::query("
				SELECT pup.prop_id, pup.type_view, prop.*
				FROM `".PREFIX."product_user_property_data` as pup
				LEFT JOIN `".PREFIX."property` as prop ON pup.prop_id = prop.id
				WHERE pup.`product_id` = ".DB::quote($_POST['produtcId']));

		while ($userFields = DB::fetchAssoc($res)) {
			$this->data['thisUserFields'][] = $userFields;
		}

		// // Получаем набор пользовательских характеристик предназначенных для выбраной категории.
		$res = DB::query("
				SELECT *
				FROM `".PREFIX."category_user_property` as сup
				LEFT JOIN `".PREFIX."property` as prop ON сup.property_id = prop.id
				WHERE сup.`category_id` = ".DB::quote($_POST['categoryId']).' ORDER BY sort DESC');
		$alreadyProp = array();
		while ($userFields = DB::fetchAssoc($res)) {
			$this->data['allProperty'][] = $userFields;
			$alreadyProp[$userFields['property_id']] = true;
		}

		// получаем содержимое сложных настроек для пользовательских характеристик
		foreach ($this->data['allProperty'] as &$item) {
			$item['name'] = str_replace(array('prop attr=', '[', '  '), array('', ' [', ' '), $item['name']);
			$data = null;
			// не загружаем данные для размерной сетки (она работает подругому)
			if(($item['type'] != 'color')&&($item['type'] != 'size')) {
				$res = DB::query("SELECT pupd.*, pd.sort FROM ".PREFIX."product_user_property_data AS pupd
					LEFT JOIN ".PREFIX."property_data AS pd ON pd.id = pupd.prop_data_id
					WHERE pupd.`prop_id` = ".DB::quote($item['property_id'])." AND pupd.`product_id` = ".DB::quote($_POST['produtcId']));
				while ($userFieldsData = DB::fetchAssoc($res)) {
					MG::loadLocaleData($userFieldsData['id'], LANG, 'product_user_property_data', $userFieldsData);
					$data[] = $userFieldsData;
				}
			}

			$ar = array();
			foreach ($data as $value) {
				$ar[] = $value['prop_data_id'];
			}
			$ar = implode(',', $ar);
			if(empty($ar)) $ar = '""';

			$res = DB::query("SELECT pd.* FROM ".PREFIX."property_data AS pd
				LEFT JOIN ".PREFIX."property AS p ON p.id = pd.prop_id
				WHERE pd.prop_id = ".DB::quote($item['property_id'])."
				AND pd.id NOT IN (".DB::quoteIN($ar).") AND p.type NOT IN ('string', 'textarea') GROUP BY pd.id ORDER BY pd.sort ASC");
			while ($userFieldsData = DB::fetchAssoc($res)) {
				MG::loadLocaleData($userFieldsData['id'], LANG, 'property_data', $userFieldsData);
				$userFieldsData['prop_data_id'] = $userFieldsData['id'];
				if(($item['type'] != 'color')&&($item['type'] != 'size')) {
					unset($userFieldsData['id']);
				}
				$data[] = $userFieldsData;
			}

			if($data == null) {
				$data = array(array(
					'prop_id' => $item['id'],
					'name' => '',
					'margin' => ''));
			}

			// подгрузка локализаций для сложных характеристик
			if(($item['type'] != 'string')&&($item['type'] != 'textarea')) {
				foreach ($data as &$val) {
					$res = DB::query("SELECT * FROM ".PREFIX."property_data WHERE `id` = ".DB::quote($val['prop_data_id']));
					while ($userFieldsData = DB::fetchAssoc($res)) {
						MG::loadLocaleData($userFieldsData['id'], LANG, 'property_data', $userFieldsData);
						$val['name'] = $userFieldsData['name'];
					}
				}
			}

			$sort = array();
			foreach($data as $key => $arr){
				$sort[$key] = $arr['sort'];
			}
			array_multisort($sort, SORT_NUMERIC, $data);

			$item['data'] = null;
			foreach ($data as $elem) {
				$item['data'][] = $elem;
			}
		}
		
		// Получаем набор пользовательских характеристик.
		// Предназначенных для всех категорий и приплюсовываем его к уже имеющимя характеристикам выбраной категории.
		/* $res = DB::query("SELECT * FROM `".PREFIX."property` WHERE all_category = 1");
			while ($userFields = DB::fetchAssoc($res)) {
			if (empty($alreadyProp[$userFields['id']])) {
			$this->data['allProperty'][] = $userFields;
			$alreadyProp[$userFields['id']];
			}
			} */
		$tempUniqueProp = array();
		foreach ($this->data['allProperty'] as $key => $allProp) {
			if (empty($tempUniqueProp[trim($allProp['name'])])) {
				$tempUniqueProp[trim($allProp['name'])] = $allProp;
			} else {
				$this->data['allProperty'][$key]=array();
			}    
		}
		return true;
	}

	/**
	 * Получает пользовательские поля для добавления нового продукта.
	 */
	public function getUserProperty() {
		if (!empty($_POST['filter'])) {
			$filterAll = explode('&', $_POST['filter']); 
			foreach ($filterAll as $param) {
				$filter = explode('=', $param);
				if (empty($_POST[$filter[0]])) {
					$_POST[str_replace('[]', '', $filter[0])] = $filter[1];
				}
				
			}
		}
		$lang = MG::get('lang');
		$listType = array(
			'null' => 'Не выбрано',
			'string' => $lang['STRING'],
			'select' => $lang['SELECT'],
			'assortment' => $lang['ASSORTMENT'],
			'assortmentCheckBox' => $lang['ASSORTMENTCHECKBOX'],
			'textarea' => $lang['TEXTAREA'],
		);
		$property = array(
			'name' => array(
				'type' => 'text',
				'label' => $lang['STNG_USFLD_NAME'],
				'special' => 'like',
				'value' => !empty($_POST['name']) ? $_POST['name'] : null,
			),
			'type' => array(
				'type' => 'select',
				'option' => $listType,
				'selected' => (!empty($_POST['type'])) ? $_POST['type'] : 'null', // Выбранный пункт (сравнивается по значению)
				'label' => $lang['STNG_USFLD_TYPE']
			),
		);
		if (isset($_POST['applyFilter'])) {
			$property['applyFilter'] = array(
				'type' => 'hidden', //текстовый инпут
				'label' => 'флаг примения фильтров',
				'value' => 1,
			);
		}
		$filter = new Filter($property);
		$arr = array(
			'type' => !empty($_POST['type']) ? $_POST['type'] : null,
			// 'name' => !empty($_POST['name']) ? $_POST['name'] : null,
		);
	 
		$userFilter = $filter->getFilterSql($arr);
		if (empty($userFilter)) {
			$userFilter .= ' 1=1 ';
		}

		$page = !empty($_POST["page"]) ? $_POST["page"] : 0; //если был произведен запрос другой страницы, то присваиваем переменной новый индекс
		$countPrintRowsProperty = MG::getSetting('countPrintRowsProperty') ? MG::getSetting('countPrintRowsProperty') : 20;
		if (intval($_POST['cat_id'])) {
			$sql = "SELECT distinct prop.id, prop.*, cup.category_id FROM `".PREFIX."category_user_property` AS cup
				LEFT JOIN `".PREFIX."property` as prop ON cup.property_id = prop.id
				WHERE cup.category_id = ".DB::quote(intval($_POST['cat_id']))." AND name LIKE '%".$_POST['name']."%' AND ".$userFilter."
				ORDER BY sort DESC";  
		} else {
			$sql = "SELECT * FROM `".PREFIX."property`  WHERE name LIKE '%".$_POST['name']."%' AND ".$userFilter." ORDER BY sort DESC";      
		}

		$navigator = new Navigator($sql, $page, $countPrintRowsProperty); //определяем класс
		$userFields = $navigator->getRowsSql();
		foreach ($userFields as $key => $item) {
			$tmp = explode('[prop attr=', $item['name']);
			$userFields[$key]['mark'] = str_replace(']', '', $tmp[1]);
			$userFields[$key]['name'] = $tmp[0];
		}
		// для переноса на другие страницы
		foreach ($userFields as $key => $val) {
			$sortS[] = $val['sort'];
		}
		$minS = min($sortS);
		$maxS = max($sortS);
		$res = DB::query('SELECT id FROM '.PREFIX.'property WHERE sort < '.DB::quote($minS).' ORDER BY sort DESC LIMIT 1');
		if($row = DB::fetchAssoc($res)) {
			$minId = $row['id'];
		}
		$res = DB::query('SELECT id FROM '.PREFIX.'property WHERE sort > '.DB::quote($maxS).' ORDER BY sort ASC LIMIT 1');
		if($row = DB::fetchAssoc($res)) {
			$maxId = $row['id'];
		}

		$pagination = $navigator->getPager('forAjax');
		$pagination = str_replace("linkPage", "propLinkPage", $pagination);
		$this->data['pageSort']['max'] = $minId;
		$this->data['pageSort']['min'] = $maxId;
		$this->data['displayFilter'] = ($_POST['type'] != "null" && !empty($_POST['type'])) || isset($_POST['applyFilter']); // так проверяем произошол ли запрос по фильтрам или нет
		$this->data['filter'] = $filter->getHtmlFilter();
		$this->data['allProperty'] = $userFields;  
		$this->data['pagination'] = $pagination;  
		return true;
	}

	/**
	 * Получает привязку пользовательского свойства к набору категорий.
	 */
	public function getConnectionCat() {
		$id = $_POST['id'];
		$categoryIds = array();
		// Получчаем список выбраных категорий дл данной характеристики.
		$res = DB::query("
				SELECT category_id
				FROM `".PREFIX."category_user_property` as сup
				WHERE сup.`property_id` = %s", $id);

		while ($row = DB::fetchAssoc($res)) {
			$categoryIds[] = $row['category_id'];
		}

		$this->data['selectedCatIds'] = implode(',', $categoryIds);
		$listCategories = MG::get('category')->getCategoryTitleList(0);
		$arrayCategories = MG::get('category')->getHierarchyCategory(0);
		$html = MG::get('category')->getTitleCategory($arrayCategories, 0);
		$html = '<option value="0">'.$this->lang['ALL'].'</option>'.$html;
		// 
		$this->data['optionHtml'] = $html;

		return true;
	}

	/**
	 * Добавляет новую характеристику.
	 */
	public function addUserProperty() {
		$this->messageSucces = $this->lang['ACT_ADD_POP'];
		$res = DB::query("
			 INSERT INTO `".PREFIX."property`
			 (`name`,`type`,`all_category`,`activity`,`description`,`type_filter`,`1c_id`,`plugin`,`unit`)
			 VALUES ('-',".DB::quote($_POST['type']?$_POST['type']:'none').",'1','1','','checkbox','','', '')"
		);
		if ($id = DB::insertId()) {
			DB::query("
			 UPDATE `".PREFIX."property`
			 SET `sort`=`id` WHERE `id` = ".DB::quote($id)
			);
			$this->data['allProperty'] = array(
				'id' => $id,
				'name' => '-',
				'type' => 'string',
				'activity' => '1',
				'description' => '',
				'unit' => '',
				'type_filter' => 'checkbox',       
				'sort' => $id,
			);
		}
		return true;
	}

	/**
	 * Сохраняет пользовательские настройки для товаров.
	 */
	public function saveUserProperty() {
		if(USER::access('product') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$result = false;
		$this->messageSucces = $this->lang['ACT_EDIT_POP'];
		$id = $_POST['id'];
		$array = $_POST;

		if (!empty($id)) {
			unset($array['id']);
			$res = DB::query('SELECT `plugin`, `type` FROM `'.PREFIX.'property` WHERE `id`='.DB::quote($_POST['id']));
			if ($row = DB::fetchArray($res)) {
				$pluginDirectory = PLUGIN_DIR.$row['plugin'].'/index.php';
				if ($row['plugin'] && file_exists($pluginDirectory)) {
					$this->messageSucces = $this->lang['ACT_EDIT_POP_PLUGIN'];
					$this->data['type'] = $row['type'];
					unset($array['type']);
					$result = true;
				}
			}

			$dataProp = $array['dataProp'];
			unset($array['dataProp']);

			// сохраняем локализацию самой характеристики
			$lang = $array['lang'];
			unset($array['lang']);


			$filterData = array('name');
			foreach ($dataProp as $item) {
				
				if(empty($item['name'])) {
					$name = '';
				} else {
					$name = 'name = '.DB::quote($item['name']).', ';
				}
				DB::query('UPDATE `'.PREFIX.'property_data` SET '.$name.'
					margin = '.DB::quote($item['margin']).', color = '.DB::quote($item['color']).' WHERE `id`='.DB::quote($item['id']));
			}

			if($array['mark'] != '') {
				$array['name'] .= '[prop attr='.$array['mark'].']';
			}
			unset($array['mark']);

			if(!empty($array['name'])) {
				$tmp = explode('[', $array['name']);
				if($tmp[0] != '') {
					DB::query('UPDATE '.PREFIX.'property SET name = '.DB::quote($array['name']).' WHERE id ='.DB::quoteInt($id));
				} else {
					unset($array['name']);
				}
			} else {
				unset($array['name']);
			}

			if(!empty($array['name']) && ($array['name'] != '-')) {
				$res = DB::query('SELECT COUNT(id) AS count FROM '.PREFIX.'property WHERE name = '.DB::quote($array['name']));
				$row = DB::fetchAssoc($res);
				if($row['count'] > 1) {
					$array['name'] .= ' [prop attr='.$id.']';
				}
			}

			// проверка возможности изменения типа характеристики
			$res = DB::query('SELECT type FROM '.PREFIX.'property WHERE id = '.DB::quoteInt($id));
			while($row = DB::fetchAssoc($res)) {
				if($row['type'] != 'none') {
					$array['type'] = $row['type'];
					$this->messageSucces = $this->lang['PROP_SAVE_TYPE_FAIL'];
				}
			} 
	 
			// обновление значений характеристики
			if (DB::query('
				UPDATE `'.PREFIX.'property`
				SET '.DB::buildPartQuery($array).'
				WHERE id ='.DB::quoteInt($id))) {
				$result = true;
			}
		}
		return $result;
	}

	/**
	 * Сохраняет привязку выбранных категорий для характеристики.
	 */
	public function saveUserPropWithCat() {
		if(USER::access('product') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageSucces = $this->lang['ACT_EDIT_POP']; 
		$category = array();
		if (!empty($_POST['category'])) {
			$category = explode("|", $_POST['category']);
		}
		$propId = $_POST['id'];

		$catAlreadyThisProp = array();
		$res = DB::query('
				SELECT `category_id`
				FROM `'.PREFIX.'category_user_property`
				WHERE `property_id` ='.$propId
		);

		while ($row = DB::fetchAssoc($res)) {
			$catAlreadyThisProp[] = $row['category_id'];
		}
	 
		// удалаляем все привязки характеристики к категориям сделанные ранее
		DB::query('
				DELETE FROM `'.PREFIX.'category_user_property`
				WHERE property_id = '.DB::quote($propId));

		$poductIdForCreate = array();
		$propertyDefault = null;
		$catAlreadyThisProp = array_intersect($catAlreadyThisProp, $category);
		$catAlreadyThisProp = array_unique($catAlreadyThisProp);

		if (!empty($category)) {
			foreach ($category as $cat_id) {
				DB::query("
						INSERT IGNORE INTO `".PREFIX."category_user_property`
						VALUES ('%s', '%s')"
					, $cat_id, $propId);

				$propertyDefault = '';
				$res = DB::query('
						 SELECT id
						 FROM `'.PREFIX.'product`
						 WHERE cat_id ='.$cat_id
				);

				while ($row2 = DB::fetchAssoc($res)) {
					$poductIdForCreate[] = $row2['id'];
				}
			}
		}

		$poductIdForCreate = array_unique($poductIdForCreate);

	 /*
		$catAlreadyThisProp = implode(',', $catAlreadyThisProp);
		if (!empty($catAlreadyThisProp)) {
			$where = 'cat_id NOT IN ('.DB::quote($catAlreadyThisProp, true).') and';
		}


		DB::query('
				DELETE pup.* FROM `'.PREFIX.'product_user_property` as pup
				LEFT JOIN `'.PREFIX.'product` as p ON pup.product_id = p.id
				WHERE '.$where.'
					pup.property_id ='.$propId.'
					'
		);
		
	 */
		$allCategory = empty($_POST['category']) ? 1 : 0;

		// Обновлем флаг , использовать во всех категориях.
		DB::query('
				UPDATE `'.PREFIX.'property`
				SET all_category = '.$allCategory.'
				WHERE id = '.DB::quote($propId));

		return true;
	}

	/**
	 * Получает параметры редактируемого пользователя.
	 */
	public function getUserData() {
		$this->messageError = $this->lang['ACT_GET_USER'];
		$response = USER::getUserById($_POST['id']);
		foreach ($response as $k => $v) {
				if($k!='pass') {
					$response->$k = htmlspecialchars_decode($v);  
				}
		}
		$this->data = $response;
		return false;
	}

	/**
	 * Получает параметры категории.
	 */
	public function getCategoryData() {
		$this->messageError = $this->lang['ACT_NOT_GET_CAT'];

		$result = DB::query("
			SELECT * FROM `".PREFIX."category`
			WHERE `id` =".DB::quote($_POST['id'])
		);
		if ($response = DB::fetchAssoc($result)) {
			$maskField = array('title','meta_title','meta_keywords','meta_desc','image_title','image_alt');
			foreach ($response as $k => $v) {
				 if(in_array($k, $maskField)) {
					$response[$k] = htmlspecialchars_decode($v);  
				 }
			}
			$this->data = $response;
			return true;
		} else {
			return false;
		}

		return false;
	}

	/**
	 * Получает параметры редактируемой страницы.
	 */
	public function getPageData() {
		$this->messageError = $this->lang['ACT_SAVE_SETNG'];


		$result = DB::query("
			SELECT * FROM `".PREFIX."page`
			WHERE `id` =".DB::quote($_POST['id'])
		);
		if ($response = DB::fetchAssoc($result)) {
			$maskField = array('title','meta_title','meta_keywords','meta_desc','image_title','image_alt');
			foreach ($response as $k => $v) {
				 if(in_array($k, $maskField)) {
					$response[$k] = htmlspecialchars_decode($v);  
				 }
			}
			MG::loadLocaleData($_POST['id'], LANG, 'page', $response);
			$this->data = $response;
			return true;
		} else {
			return false;
		}

		return false;
	}

	/**
	 * Устанавливает порядок сортировки. Меняет местами две категории.
	 */
	public function changeSortCat() {
		$switchId = $_POST['switchId'];
		$sequence = explode(',', $_POST['sequence']);
		if (!empty($sequence)) {
			foreach ($sequence as $item) {
				MG::get('category')->changeSortCat($switchId, $item);
			}
		} else {
			$this->messageError = $this->lang['ACT_NOT_GET_CAT'];
			return false;
		}

		$this->messageSucces = $this->lang['ACT_SWITH_CAT'];
		return true;
	}

	/**
	 * Устанавливает порядок сортировки. Меняет местами две страницы.
	 */
	public function changeSortPage() {
		$switchId = $_POST['switchId'];
		$sequence = explode(',', $_POST['sequence']);
		if (!empty($sequence)) {
			foreach ($sequence as $item) {
				//MG::get('category')->changeSortCat($switchId, $item);
				MG::get('pages')->changeSortPage($switchId, $item);
			}
		} else {
			$this->messageError = $this->lang['ACT_NOT_GET_PAGE'];
			return false;
		}

		$this->messageSucces = $this->lang['ACT_SWITH_PAGE'];
		return true;
	}

	/**
	 * Устанавливает порядок сортировки. Меняет местами две записи.
	 */
	public function changeSortRow() {   
		$switchId = $_POST['switchId'];
		$tablename = $_POST['tablename'];
		$sequence = explode(',', $_POST['sequence']);
		// if ($tablename =='product' && MG::getSetting('showSortFieldAdmin')=='true') {
		//   $this->messageError = 'Изменить порядок можно только в поле "Порядковый номер"';
		//   return false;
		// }
		if (!empty($sequence)) {
			foreach ($sequence as $item) {
				MG::changeRowsTable($tablename, $switchId, $item);
			}
		} else {
			return false;
		}

		$this->messageSucces = $this->lang['ACT_SWITH'];
		return true;
	}

	/**
	 * Возвращает ответ в формате JSON.
	 * @param bool $flag - если отработаный метод что-то вернул, то ответ считается успешным ждущей его фунции.
	 * @return bool
	 */
	public function jsonResponse($flag) {
		if ($flag === null) {
			return false;
		}
		if ($flag) {
			$this->jsonResponseSucces($this->messageSucces);
		} else {
			$this->jsonResponseError($this->messageError);
		}
	}

	/**
	 * Возвращает положительный ответ с сервера.
	 * @param string $message
	 */
	public function jsonResponseSucces($message) {
		$result = array(
			'data' => $this->data,
			'msg' => $message,
			'status' => 'success');
		echo json_encode($result);
	}

	/**
	 * Возвращает отрицательный ответ с сервера.
	 * @param string $message
	 */
	public function jsonResponseError($message) {
		$result = array(
			'data' => $this->data,
			'msg' => $message,
			'status' => 'error');
		echo json_encode($result);
	}

	/**
	 * Проверяет актуальность текущей версии системы.
	 * @return void возвращает в AJAX сообщение о результате операции.
	 */
	public function checkUpdata() {
		$msg = Updata::checkUpdata();

		if ($this->lang['ACT_THIS_LAST_VER'] == $msg['msg']) {
			$status = 'alert';
		} else {
			$status = 'success';
		}
		$response = array(
			'msg' => $msg['msg'],
			'status' => $status,
		);

		echo json_encode($response);
		exit;
	}

	/**
	 * Обновленяет верcию CMS.
	 *
	 * @return void возвращает в AJAX сообщение о результате операции.
	 */
	public function updata() {
		$version = $_POST['version'];

		if (Updata::updataSystem($version)) {
			$msg = $this->lang['ACT_UPDATE_VER'];
			$status = 'success';
		} else {
			$msg = $this->lang['ACT_ERR_UPDATE_VER'];
			$status = 'error';
		}

		$response = array(
			'msg' => $msg,
			'status' => $status,
		);

		echo json_encode($response);
	}

	/**
	 * Отключает публичную часть сайта. Обычно требуется для внесения изменений администратором.
	 * @return bool
	 */
	public function downTime() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$downtime = MG::getOption('downtime');

		if ('Y' == $downtime) {
			$activ = 'N';
		} else {
			$activ = 'Y';
		}

		$res = DB::query('
			UPDATE `'.PREFIX.'setting`
			SET `value` = "'.$activ.'"
			WHERE `option` = "downtime"
		');

		if ($res) {
			return true;
		};
	}

	/**
	 * Функцию отправляет на сервер обновления информацию о системе и в случае одобрения скачивает архив с обновлением.
	 * @return void возвращает в AJAX сообщение загруженную в систему версию.
	 */
	public function preDownload() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_UPDATE_SYSTEM'];
			return false;
		}
		$this->messageSucces = $this->lang['ACT_UPLOAD_ZIP']." ".$_POST['version'];
		$this->messageError = $this->lang['ACT_NOT_UPLOAD_ZIP'];
		if(!in_array(PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION, array('5.3', '5.4', '5.5', '5.6', '7.0', '7.1', '7.2'))) {
			$this->messageError = $this->lang['PHP_NOT_SUPPORTED'];
		}
		$result = Updata::preDownload($_POST['version']);

		if (!empty($result['status'])) {
			if ($result['status'] == 'error') {
				$this->messageError = $result['msg'];
				return false;
			}
			return true;
		}


		return false;
	}

	/**
	 * Установливает загруженный ранее архив с обновлением.
	 * @return void возвращает в AJAX сообщение о результате операции.
	 */
	public function postDownload() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_UPDATE_SYSTEM'];
			return false;
		}
		$this->messageSucces = $this->lang['ACT_UPDATE_TRUE'].$_POST['version'];
		$this->messageError = $this->lang['ACT_NOT_UPDATE_TRUE'];

		$version = $_POST['version'];

		if (Updata::extractZip('update-m.zip')) {
			$this->messageSucces = $this->lang['ACT_UPDATE_VER'];
			// создание файла индетификации текущей версии кодировки
			file_put_contents(CORE_DIR.'lastPhpVersion.txt', PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION);

			$dir = SITE_DIR.'uploads'.DS.'product'.DS;
			$scan = array_diff(scandir($dir), array('..', '.'));
			foreach ($scan as $value) {
				if(strpos($value, 'p_') === 0 && is_dir($dir.$value)) {
					$renameDir = rename($dir.$value, $dir.substr($value, 2));
					if (!$renameDir) {
						 MG::rMoveDir($dir.$value, $dir.substr($value, 2));
					}
				}
			}

			return true;
		} else {
			$this->messageError = $this->lang['ACT_ERR_UPDATE_VER'];
			return false;
		}
		return false;
	}

	/**
	 * Устанавливает цветовую тему для меню в административном разделе.
	 * @return bool
	 */
	public function setTheme() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		if ($_POST['color']) {
			MG::setOption(array('option' => 'themeColor', 'value' => $_POST['color']));
			MG::setOption(array('option' => 'themeBackground', 'value' => $_POST['background']));
		}
		return true;
	}

	/**
	 * Устанавливает количество отображаемых записей в разделе товаров.
	 * @return bool
	 */
	public function setCountPrintRowsProduct() {

		$count = 20;
		if (!empty($_POST['count'])) {
			$count = (int)$_POST['count'];
		}

		MG::setOption(array('option' => 'countPrintRowsProduct', 'value' => $count));
		return true;
	}

	/**
	 * Устанавливает количество отображаемых записей в разделе страницы.
	 * @return bool
	 */
	public function setCountPrintRowsPage() {

		$count = 20;
		if (is_numeric($_POST['count']) && !empty($_POST['count'])) {
			$count = $_POST['count'];
		}


		MG::setOption(array('option' => 'countPrintRowsPage', 'value' => $count));
		return true;
	}

	/**
	 * Устанавливает количество отображаемых записей в разделе пользователей.
	 * @return bool
	 */
	public function setCountPrintRowsOrder() {

		$count = 20;
		if (is_numeric($_POST['count']) && !empty($_POST['count'])) {
			$count = $_POST['count'];
		}

		MG::setOption(array('option' => 'countPrintRowsOrder', 'value' => $count));
		return true;
	}

	/**
	 * Устанавливает количество отображаемых записей в разделе заказов.
	 * @return bool
	 */
	public function setCountPrintRowsUser() {

		$count = 20;
		if (is_numeric($_POST['count']) && !empty($_POST['count'])) {
			$count = $_POST['count'];
		}

		MG::setOption(array('option' => 'countPrintRowsUser', 'value' => $count));
		return true;
	}
	/**
	 * Устанавливает количество отображаемых записей в разделе характеристик.
	 * @return bool
	 */
	public function countPrintRowsProperty() {
		$count = 20;
		if (is_numeric($_POST['count']) && !empty($_POST['count'])) {
			$count = $_POST['count'];
		}
		MG::setOption(array('option' => 'countPrintRowsProperty', 'value' => $count));
		return true;
	}

	/**
	 * Возвращает список найденых продуктов по ключевому слову.
	 * @return bool
	 */
	public function searchProduct() {
		$this->messageSucces = $this->lang['SEACRH_PRODUCT'];
		$model = new Models_Catalog;

		$_POST['mode']=$_POST['mode']?$_POST['mode']:false;
		$_POST['forcedPage']=$_POST['forcedPage']?$_POST['forcedPage']:false;
		$arr = $model->getListProductByKeyWord($_POST['keyword'], false, false, true, $_POST['mode'], $_POST['forcedPage']);
	
		if (empty($arr)) {  
			$arr['catalogItems'] = array();
		}
		foreach ($arr['catalogItems'] as &$prod) {
			$prod['sortshow'] = 'true';
		}
		if (MG::getSetting('showCodeInCatalog')=='true') {
			foreach ($arr['catalogItems'] as &$prod) {
				$prod['codeshow'] = 'true';
			}      
		}
		$this->data = $arr;
		return true;
	}

	/**
	 * Устанавливает локаль для плагина, используется в JS плагинов.
	 * @return bool
	 */
	public function seLocalesToPlug() {
		$this->data = PM::plugLocales($_POST['pluginName']);
		return true;
	}

	/**
	 * Сохранение способа доставки.
	 */
	public function saveDeliveryMethod() {

		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}

		$weight = '';
		if (is_array($_POST['weight']) && !empty($_POST['weight'])) {
			$weightTmp = array();
			foreach ($_POST['weight'] as $value) {
				$tmp = str_replace(',', '.', str_replace(' ', '', $value['w']));
				$tmp2 = str_replace(',', '.', str_replace(' ', '', $value['p']));
				if ((float)$tmp == 0 && (float)$tmp2 == 0) {continue;}
				$weightTmp[] = array('w'=>(float)$tmp,'p'=>(float)$tmp2);
			}
			if (!empty($weightTmp)) {
				usort($weightTmp, function($a, $b) { 
					if ($a["w"] == $b["w"]) {return 0;}
					return ($a["w"] < $b["w"]) ? -1 : 1;
				});
				$weight = json_encode($weightTmp);
			}
		}

		$this->messageSucces = $this->lang['ACT_SUCCESS'];
		$this->messageError = $this->lang['ACT_ERROR'];
		$status = $_POST['status'];
		$deliveryName = htmlspecialchars($_POST['deliveryName']);
		$deliveryCost = (float)$_POST['deliveryCost'];
		$deliveryId = (int)$_POST['deliveryId'];
		$free = (float)MG::numberDeFormat($_POST['free']);

		$paymentMethod = $_POST['paymentMethod'];
		$paymentArray = json_decode($paymentMethod, true);

		$deliveryDescription = htmlspecialchars($_POST['deliveryDescription']);
		$deliveryActivity = $_POST['deliveryActivity'];
		$deliveryDate = $_POST['deliveryDate'];
		$deliveryYmarket= $_POST['deliveryYmarket'];

		if ($_POST['intervals']) {
			$_POST['intervals'] = '["'.implode('","', array_filter($_POST['intervals'])).'"]';
		}
		
		switch ($status) {
			case 'createDelivery':
				$sql = "
					INSERT INTO `".PREFIX."delivery` (`name`,`cost`, `description`, `activity`,`free`, `date`, `ymarket`, `weight`, `interval`, `address_parts`)
					VALUES (
						".DB::quote($deliveryName).", ".DB::quote($deliveryCost).", ".DB::quote($deliveryDescription).", ".DB::quote($deliveryActivity).", ".DB::quote($free).", ".DB::quote($deliveryDate).", ".DB::quote($deliveryYmarket).", ".DB::quote($weight).", ".DB::quote($_POST['intervals']).", ".DB::quote($_POST['useAddressParts'])."
					);";

				$result = DB::query($sql);

				if ($deliveryId = DB::insertId()) {
					DB::query(" UPDATE `".PREFIX."delivery` SET `sort`=`id` WHERE `id` = ".DB::quote($deliveryId));
					$status = 'success';
					$msg = $this->lang['ACT_SUCCESS'];
				} else {
					$status = 'error';
					$msg = $this->lang['ACT_ERROR'];
				}

				foreach ($paymentArray as $paymentId => $compare) {
					$sql = "
						INSERT INTO `".PREFIX."delivery_payment_compare`
							(`compare`,`payment_id`, `delivery_id`)
						VALUES (
							".DB::quote($compare).", ".DB::quote($paymentId).", ".DB::quote($deliveryId)."
						);
					";
					$result = DB::query($sql);
				}

				break;
			case 'editDelivery':
					$fields = "`name` = ".DB::quote($deliveryName).",
										`description` = ".DB::quote($deliveryDescription).', ';
				$sql = "
					UPDATE `".PREFIX."delivery`
					SET ".$fields."
							`cost` = ".DB::quote($deliveryCost).",
							`activity` = ".DB::quote($deliveryActivity).",
							`free` = ".DB::quote($free).",
							`date` = ".DB::quote($deliveryDate).",
							`ymarket` = ".DB::quote($deliveryYmarket).",
							`weight` = ".DB::quote($weight).",
							`interval` = ".DB::quote($_POST['intervals']).",
							`address_parts` = ".DB::quote($_POST['useAddressParts'])."
					WHERE id = ".DB::quote($deliveryId);
				$result = DB::query($sql);

				foreach ($paymentArray as $paymentId => $compare) {
					$result = DB::query("
						SELECT * 
						FROM `".PREFIX."delivery_payment_compare`         
						WHERE `payment_id` = ".DB::quote($paymentId)."
							AND `delivery_id` = ".DB::quote($deliveryId));
					if (!DB::numRows($object)) {
						$sql = "
								INSERT INTO `".PREFIX."delivery_payment_compare`
									(`compare`,`payment_id`, `delivery_id`)
								VALUES (
									".DB::quote($compare).", ".DB::quote($paymentId).", ".DB::quote($deliveryId)."
								);";
						$result = DB::query($sql);
					} else {
						$sql = "
							UPDATE `".PREFIX."delivery_payment_compare`
							SET `compare` = ".DB::quote($compare)."
							WHERE `payment_id` = ".DB::quote($paymentId)."
								AND `delivery_id` = ".DB::quote($deliveryId);
						$result = DB::query($sql);
					}
				}
			 

				if ($result) {
					$status = 'success';
					$msg = $this->lang['ACT_SUCCESS'];
				} else {
					$status = 'error';
					$msg = $this->lang['ACT_ERROR'];
				}
		}
		 if ($deliveryYmarket == 1) {
					DB::query(" UPDATE `".PREFIX."delivery` SET `ymarket`=0 WHERE `id` != ".DB::quote($deliveryId));
				}
				
		$response = array(
			'data' => array(
				'id' => $deliveryId,
			),
			'status' => $status,
			'msg' => $msg,
		);
		echo json_encode($response);
	}

	/**
	 * Удаляет способ доставки.
	 * @return bool
	 */
	public function deleteDeliveryMethod() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageSucces = $this->lang['ACT_SUCCESS'];
		$this->messageError = $this->lang['ACT_ERROR'];
		$res1 = DB::query('DELETE FROM `'.PREFIX.'delivery` WHERE `id`= '.DB::quote($_POST['id']));
		$res2 = DB::query('DELETE FROM `'.PREFIX.'delivery_payment_compare` WHERE `delivery_id`= '.DB::quote($_POST['id']));

		if ($res1 && $res2) {
			return true;
		}
		return false;
	}

	/**
	 * Сохраняет способ оплаты.
	 */
	public function savePaymentMethod() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}

		$paymentParam = str_replace("'", "\'", $_POST['paymentParam']);
		
		$deliveryMethod = $_POST['deliveryMethod'];
		$deliveryArray = json_decode($deliveryMethod, true);
		$paymentActivity = $_POST['paymentActivity'];
		$paymentId = $_POST['paymentId'];

		if(empty($_POST['paymentId'])) {
			$res = DB::query('SELECT MAX(id) FROM '.PREFIX.'payment');
			$tmpId = DB::fetchAssoc($res);
			$tmpId = $tmpId['MAX(id)'];
			if($tmpId < 1000) {
				$tmpId = 1000;
			} else {
				$tmpId++;
			}
			DB::query("INSERT INTO `".PREFIX."payment`
				SET `paramArray` = ".DB::quote($paymentParamEncoded).",
						`activity` = ".DB::quote($paymentActivity).",
						`rate` = ".DB::quote($_POST['rate'], 1).",
						`permission` = ".DB::quote($_POST['permission']).',
						`id` = '.DB::quoteInt($tmpId).',
						`sort` = '.DB::quoteInt($tmpId));
			$paymentId = DB::insertId();
		}

		if (is_array($deliveryArray)) {
			foreach ($deliveryArray as $deliveryId => $compare) {
				$sql = "
					DELETE FROM `".PREFIX."delivery_payment_compare`
					WHERE `payment_id` = ".DB::quote($paymentId)."
						AND `delivery_id` = ".DB::quote($deliveryId);
				$result = DB::query($sql);
				$sql = "
					INSERT INTO `".PREFIX."delivery_payment_compare`
					(payment_id, delivery_id, compare) VALUES 
					(".DB::quote($paymentId).", ".DB::quote($deliveryId).", ".DB::quote($compare).")";
				$result = DB::query($sql);
			}
		}
		$newparam = array();
		$param = json_decode($paymentParam);
		foreach ($param as $key=>$value) {
			if ($value != '') {
				$value = CRYPT::mgCrypt($value);
			}
			$newparam[$key] = $value;
		}
		$paymentParamEncoded = CRYPT::json_encode_cyr($newparam);

		if($_POST['lang'] != 'default') {
			MG::saveLocaleData($paymentId, $_POST['lang'], 'payment', array('name' => $_POST['name']));
			$name = '';
		} else {
			$name = "`name` = ".DB::quote($_POST['name']).",";
		}
		
		$sql = "
			UPDATE `".PREFIX."payment`
			SET ".$name."     
					`paramArray` = ".DB::quote($paymentParamEncoded).",
					`activity` = ".DB::quote($paymentActivity).",
					`rate` = ".DB::quote($_POST['rate'], 1).",
					`permission` = ".DB::quote($_POST['permission'])."
			WHERE id = ".$paymentId;
		$result = DB::query($sql);

		if ($result) {
			$status = 'success';
			$msg = $this->lang['ACT_SUCCESS'];
		} else {
			$status = 'error';
			$msg = $this->lang['ACT_ERROR'];
		}

		$sql = "
			SELECT *
			FROM `".PREFIX."payment`     
			WHERE id = ".$paymentId;
		$result = DB::query($sql);
		if ($row = DB::fetchAssoc($result)) {
			$newparam = array();
			$param = json_decode($row['paramArray']);
			foreach ($param as $key=>$value) {
				if ($value != '') {
					$value = CRYPT::mgDecrypt($value);
				}
				$newparam[$key] = $value;
				}
			$paymentParam = CRYPT::json_encode_cyr($newparam);
		}

		$response = array(
			'status' => $status,
			'msg' => $msg,
			'data' => array('paymentParam' => $paymentParam)
		);
		echo json_encode($response);
	}
	
	/**
	 * Удаляет способ оплаты (не удаляет стандартные способы оплаты).
	 * @return bool true
	 */
	public function deletePayment() {
		DB::query('DELETE FROM '.PREFIX.'payment WHERE id = '.DB::quoteInt($_POST['id']));
		return true;
	}
	

	/**
	 * Обновляет способов оплаты и доставки при переходе по вкладкам в админке.
	 */
	public function getMethodArray() {
		$mOrder = new Models_Order;
		$deliveryArray = $mOrder->getDeliveryMethod();
		$response['data']['deliveryArray'] = $deliveryArray;

		$paymentArray = array();
		$i = 1;
		while ($payment = $mOrder->getPaymentMethod($i)) {
			$paymentArray[$i] = $payment;
			$i++;
		}
		$response['data']['paymentArray'] = $paymentArray;
		echo json_encode($response);
	}

	/**
	 * Проверяет наличие подключенного модуля xmlwriter и библиотеки libxml.
	 */
	public function existXmlwriter() {
		$this->messageSucces = $this->lang['START_GENERATE_FILE'];
		$this->messageError = $this->lang['XMLWRITER_MISSING'];
		if (LIBXML_VERSION && extension_loaded('xmlwriter')) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Осуществляет импорт данных в таблицы продуктов и категорий.
	 */
	public function importFromCsv() {
		if(USER::access('product') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageSucces = $this->lang['IMPORT_SUCCESS'];
		$this->messageError = $this->lang['ERROR'];
		$importer = new Import();
		$importer->ImportFromCSV();
		return true;
	}

	/**
	 * Получает файл шаблона.
	 */
	public function getTemplateFile() {
		$this->messageError = $this->lang['NOT_FILE_TPL'];
		// доступ к чтению файлов только у админа и модератора
		if (USER::access('setting') >= 1) {

			if ($_POST['type'] == '#ttab6') {
				$pathTemplate  = 'mg-templates'.DS.'landings'.DS.MG::getSetting('landingName');
			}
			else{
				$pathTemplate  = 'mg-templates'.DS.MG::getSetting('templateName');
			}
			if (file_exists($pathTemplate.$_POST['path']) && is_writable($pathTemplate.$_POST['path'])) {
				$this->data['filecontent'] = file_get_contents($pathTemplate.$_POST['path']);
				return true;
			} else {
				$this->data['filecontent'] = "CHMOD = ".substr(sprintf('%o', fileperms($pathTemplate.$_POST['path'])), -4);
				return true;
			}  
		}
		return false;
	}

	/**
	 * Сохраняет файл шаблона.
	 */
	public function saveTemplateFile() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		if (MG::getSetting('templateName') == 'moguta-standard') {
			$this->messageError = $this->lang['REFERENCE_EDIT_FAIL'];
			return false;
		}

		$this->messageSucces = $this->lang['SAVE_FILE_TPL'];
		if ($_POST['type'] == '#ttab6') {
			$pathTemplate  = 'mg-templates'.DS.'landings'.DS.MG::getSetting('landingName');
		}
		else{
			$pathTemplate  = 'mg-templates'.DS.MG::getSetting('templateName');
		}
		if (file_exists($pathTemplate.$_POST['filename']) && is_writable($pathTemplate.$_POST['filename'])) {
			file_put_contents($pathTemplate.$_POST['filename'], $_POST['content']);
		} else {
			return false;
		}
		return true;
	}

	/**
	 * Очищает кеш проверки версий и проверяет наличие новой.
	 */
	public function clearLastUpdate() {
		if (!$checkLibs = MG::libExists()) {
			MG::setOption('timeLastUpdata', '');
			$newVer = Updata::checkUpdata(true);
			Storage::clear('mp-cache');
			MarketplaceMain::update();
			if (!$newVer) {
				$this->messageError = $this->lang['NOT_NEW_VERSION'];
				return false;
			}
			$this->messageSucces = $this->lang['AVAIBLE_NEW_VERSION'].' '.$newVer['lastVersion'];
			return true;
		} else {
			$this->messageError = implode('<br>', $checkLibs);
			return false;
		}
	}

	/**
	 * Получает список продуктов при вводе в поле поиска товара при создании заказа через админку.
	 */
	public function getSearchData() {
		$keyword = URL::getQueryParametr('search');
		$adminOrder = URL::getQueryParametr('adminOrder');
		$searchCats = URL::getQueryParametr('searchCats');
		$useVariants = URL::getQueryParametr('useVariants');

		$adminSearch = false; 
		if ($adminOrder === 'yep') {
			$adminSearch = true; 
		}
		if (!$searchCats && $searchCats !== 0) {
			$searchCats = -1;
		}

		if (!empty($keyword)) {
			$catalog = new Models_Catalog;
			$product = new Models_Product;
			$order = new Models_Order;
			$currencyRate = MG::getSetting('currencyRate');
			$currencyShort = MG::getSetting('currencyShort');
			$currencyShopIso = MG::getSetting('currencyShopIso');
			$items = $catalog->getListProductByKeyWord($keyword, true, false, $adminSearch, false, false, $searchCats);//добавление к заказу из админки товара, который не выводится в каталог.
			
			$blockedProp = $product->noPrintProperty();

			foreach ($items['catalogItems'] as $key => $item) {
				$prop = array();
				$res = DB::query('SELECT * FROM '.PREFIX.'property AS p LEFT JOIN
					'.PREFIX.'category_user_property AS cup ON cup.property_id = p.id
					WHERE cup.category_id = '.DB::quote($item['cat_id']));
				while($row = DB::fetchAssoc($res)) {
					$prop[] = $row;
				}
				
				Property::addDataToProp($prop, $item['id']);

				// MG::loger($prop);

				$items['catalogItems'][$key]['image_url'] = mgImageProductPath($item["image_url"], $item['id'], 'small');

				$propertyFormData = $product->createPropertyForm($param = array(
					'id' => $item['id'],
					'maxCount' => 999,
					'productUserFields' => $prop,//$item['thisUserFields'],
					'action' => "/catalog",
					'method' => "POST",
					'ajax' => true,
					'blockedProp' => $blockedProp,
					'noneAmount' => true,
					'titleBtn' => "<span>".$this->lang['EDIT_ORDER_14']."</span>",
					'blockVariants' => $product->getBlockVariants($item['id'],0,$adminOrder),
					'classForButton' => 'addToCart buy-product buy custom-btn',
					'printCompareButton' => false,
					'currency_iso' => $item['currency_iso'],
					'showCount' => false,
				), $adminOrder);
			 
				$items['catalogItems'][$key]['price'] = $items['catalogItems'][$key]['price']; 
				$items['catalogItems'][$key]['propertyForm'] = $propertyFormData['html'];
				$items['catalogItems'][$key]['notSet'] = $order->notSetGoods($item['id']);
			}
		}
		
		foreach ($items['catalogItems'] as $key => $product) {
			if ($useVariants == 'true' && !empty($product['variants'])) {
				$items['catalogItems'][$key]["price"] = MG::numberFormat($product['variants'][0]["price_course"]);
				$items['catalogItems'][$key]["old_price"] = $product['variants'][0]["old_price"];
				$items['catalogItems'][$key]["count"] = $product['variants'][0]["count"];
				$items['catalogItems'][$key]["code"] = $product['variants'][0]["code"];
				$items['catalogItems'][$key]["weight"] = $product['variants'][0]["weight"];
				$items['catalogItems'][$key]["price_course"] = $product['variants'][0]["price_course"];
			}
		}

		$searchData = array(
			'status' => 'success',
			'item' => array(
				'keyword' => $keyword,
				'count' => $items['numRows'],
				'items' => $items,
			),
			'currency' => MG::getSetting('currency')
		);

		echo json_encode($searchData);
		exit;
	}

	/**
	 * Возвращает случайный продукт из ассортимента.
	 * @return bool
	 */
	public function getRandomProd() {
		$res = DB::query('
			SELECT id 
			FROM `'.PREFIX.'product` 
				WHERE 1=1 
			ORDER BY RAND() LIMIT 1');
		if ($row = DB::fetchAssoc($res)) {
			$product = new Models_Product();
			$prod = $product->getProduct($row['id']);
			$prod['image_url'] = mgImageProductPath($prod['image_url'], $prod['id']);      
		} else {
			return false;
		}
		$this->data['product'] = $prod;
		return true;
	}

	/**
	 * Возвращает список заказов для вывода статистики по заданному периоду.
	 * @return bool
	 */
	public function getOrderPeriodStat() {
		$model = new Models_Order;
		$this->data = $model->getStatisticPeriod($_POST['from_date_stat'], $_POST['to_date_stat']);
		return true;
	}

	/**
	 * Возвращает список заказов для вывода статистики.
	 * @return bool
	 */
	public function getOrderStat() {
		$model = new Models_Order;
		$this->data = $model->getOrderStat();
		return true;
	}

	/**
	 * Выполняет операцию над отмеченными заказами в админке.
	 * @return bool
	 */
	public function operationOrder() {
		if(USER::access('order') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		
		if (empty($_POST['orders_id']) && $_POST['operation'] != 'fulldelete') {
			$this->messageError = $this->lang['CHECK_ORDER'];
			return false;
		}

		$model = new Models_Order;

		switch ($_POST['operation']) {
			case 'delete':
				foreach ($_POST['orders_id'] as $orderId) {
					$model->refreshCountProducts($orderId, 4);
				}
				$result = $model->deleteOrder(true, $_POST['orders_id']);
				break;
			case 'changeStatus':
				foreach ($_POST['orders_id'] as $orderId) {
					$result = $model->updateOrder(array('id' => $orderId, 'status_id' => $_POST['param']));
				}
				break;
			case 'getcsvorder':
				$filename = $model->exportToCsvOrder($_POST['orders_id']); 
				$this->data['filecsv'] = $filename;            
				$this->messageSucces = $this->lang['IMPORT_TO_FILE_SUCCESS'].' '.$filename;
				$result = true;
				break;
			case 'csvorderfull':
				$filename = $model->exportToCsvOrder($_POST['orders_id'], true); 
				$this->data['filecsv'] = $filename;            
				$this->messageSucces = $this->lang['IMPORT_TO_FILE_SUCCESS'].' '.$filename;
				$result = true;
				break;
			case 'fulldelete':
				$res = DB::query("SELECT `id` FROM `".PREFIX."order`");
				while ($row = DB::fetchAssoc($res)) {
					$model->refreshCountProducts($row['id'], 4);
				}
				DB::query("TRUNCATE `".PREFIX."order`");
				$result = true;
				break;
			case 'changeOwner':
				DB::query('UPDATE '.PREFIX.'order SET owner = '.DB::quoteInt($_POST['param']).' WHERE id IN ('.DB::quoteIN($_POST['orders_id']).')');
				$result = true;
				break;
			case 'massPrint':
				$arrayKeys = array_keys($_POST['orders_id']);
				$firstArrayKey = array_shift($arrayKeys);
				$html = '';
				foreach ($_POST['orders_id'] as $key => $orderId) {
					if ($key === $firstArrayKey) {
						$html .= '<span>';
					} else {
						$html .= '<div style="page-break-before: always !important;">';
					}
					$html .= $model->printOrder($orderId, true, $_POST['param']);
					$html .= '</div>';
				}
				$this->data['html'] = $html;
				$result = true;
				break;
		}

		$this->data['count'] = $model->getNewOrdersCount();
		return $result;
	}

	/**
	 * Выполняет операцию над отмеченными характеристиками в админке.
	 * @return bool
	 */
	public function operationProperty() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$operation = $_POST['operation'];
		if (empty($_POST['property_id']) && $operation != 'fulldelete') {
			$this->messageError = $this->lang['CHECK_PROP'];
			return false;
		}
		if ($operation == 'delete') {
			foreach ($_POST['property_id'] as $propertyId) {
				$_POST['id'] = $propertyId;
				$this->deleteUserProperty();
			}
		} elseif (strpos($operation, 'activity') === 0 && !empty($_POST['property_id'])) {
			foreach ($_POST['property_id'] as $propertyId) {
				$_POST['id'] = $propertyId;
				$_POST['activity'] = substr($operation, -1, 1);
				$this->visibleProperty();
			}
		} elseif (strpos($operation, 'filter') === 0 && !empty($_POST['property_id'])) {
			foreach ($_POST['property_id'] as $propertyId) {
				$_POST['id'] = $propertyId;
				$_POST['filter'] = substr($operation, -1, 1);
				$this->filterProperty();
			}
		} elseif (strpos($operation, 'fulldelete') === 0) {
			$res = DB::query("SELECT `id` FROM `".PREFIX."property`");
			while ($row = DB::fetchAssoc($res)) {
				$_POST['id'] = $row['id'];
				$this->deleteUserProperty();
			}
		}
		return true;
	}

	/**
	 * Выполняет операцию над отмеченными товарами в админке.
	 * @return bool
	 */
	public function operationProduct() {
		if(USER::access('product') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$productModel = new Models_Product();
		$operation = $_POST['operation'];
		if (empty($_POST['products_id']) && $operation != 'fulldelete') {
			$this->messageError = $this->lang['CHECK_PRODUCT'];
			return false;
		}
		if ($operation == 'delete') {
			foreach ($_POST['products_id'] as $productId) {
				$productModel->deleteProduct($productId);
			}
		} elseif (strpos($operation, 'activity') === 0 && !empty($_POST['products_id'])) {
			foreach ($_POST['products_id'] as $product) {
				$productModel->updateProduct(array('id' => $product, 'activity' => substr($operation, -1, 1)));
			}
		} elseif (strpos($operation, 'recommend') === 0 && !empty($_POST['products_id'])) {
			foreach ($_POST['products_id'] as $product) {
				$productModel->updateProduct(array('id' => $product, 'recommend' => substr($operation, -1, 1)));
			}
		} elseif (strpos($operation, 'new') === 0 && !empty($_POST['products_id'])) {
			foreach ($_POST['products_id'] as $product) {
				$productModel->updateProduct(array('id' => $product, 'new' => substr($operation, -1, 1)));
			}
		} elseif (strpos($operation, 'clone') === 0 && !empty($_POST['products_id'])) {
			foreach ($_POST['products_id'] as $product) {
				$productModel->clone = true;
				$productModel->cloneProduct($product);
			}
		} elseif (strpos($operation, 'delete') === 0 && !empty($_POST['products_id'])) {
			foreach ($_POST['products_id'] as $product) {
				$productModel->deleteProduct($product);
			}
		} elseif (strpos($operation, 'fulldelete') === 0) {
			$res = DB::query("SELECT `id` FROM `".PREFIX."product`");
			while ($row = DB::fetchAssoc($res)) {
				$productModel->deleteProduct($row['id']);
			}
		} elseif (strpos($operation, 'changecur') === 0 && !empty($_POST['products_id'])) {
			foreach ($_POST['products_id'] as $product) {
				$part = explode('_', $operation);   
				$iso = str_replace($part[0].'_','',$operation);

				$productModel->convertToIso($iso, $_POST['products_id']);
				$this->data['clearfilter'] = true;
				//$result = $model->updateOrder(array('id' => $orderId, 'status_id' => substr($operation, -1, 1)));
			}
		}elseif (strpos($operation, 'getcsv') === 0 && !empty($_POST['products_id'])) {     
				$catalogModel = new Models_Catalog();
				$filename = $catalogModel->exportToCsv($_POST['products_id']); 
				$this->data['filecsv'] = $filename;          
				$this->messageSucces = $this->lang['IMPORT_TO_FILE_SUCCESS'].' '.$filename;
		}elseif (strpos($operation, 'getyml') === 0 && !empty($_POST['products_id'])) { 
				if (LIBXML_VERSION && extension_loaded('xmlwriter')) {
					$ymlLib = new YML();
					$filename = $ymlLib->exportToYml($_POST['products_id']);      
					$this->data['fileyml'] = $filename;
					$this->messageSucces = $this->lang['IMPORT_TO_FILE_SUCCESS'].' '.$filename;
				} else {
					$this->messageError = $this->lang['XMLWRITER_MISSING2'];         
				}       
		}elseif (strpos($operation, 'move_to_category') === 0 && !empty($_POST['products_id'])) {      
			foreach ($_POST['products_id'] as $product) {
				$productModel->updateProduct(array('id' => $product, 'cat_id' => $_POST['data']['category_id']));
			}
		}

		return true;
	}

	/**
	 * Выполняет операцию над отмеченными категориями в админке.
	 * @return bool
	 */
	public function operationCategory() {
		if(USER::access('category') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$operation = $_POST['operation'];

		if (empty($_POST['category_id']) && $operation != 'fulldelete') {
			$this->messageError = $this->lang['CHECK_CATEGORY'];
			return false;
		}
		if ($operation == 'delete') {
			if ($_POST['dropProducts'] == 'true') {
				$model = new Models_Product;
			}
			foreach ($_POST['category_id'] as $catId) {
				if ($_POST['dropProducts'] == 'true') {
					$cats = MG::get('category')->getCategoryList($catId);
					$cats[] = $catId;
					$cats = implode(', ', $cats);
					$res = DB::query('SELECT `id` FROM `'.PREFIX.'product` WHERE `cat_id` IN ('.$cats.')');
					while($row = DB::fetchAssoc($res)) {
						$model->deleteProduct($row['id']);
					}
				}
				MG::get('category')->delCategory($catId);
			}
		} elseif (strpos($operation, 'invisible') === 0 && !empty($_POST['category_id'])) {
			foreach ($_POST['category_id'] as $catId) {
				MG::get('category')->updateCategory(array('id' => $catId, 'invisible' => substr($operation, -1, 1)));
				$arrayChildCat = MG::get('category')->getCategoryList($catId);
				foreach ($arrayChildCat as $ch_id) {
					MG::get('category')->updateCategory(array('id' => $ch_id, 'invisible' => substr($operation, -1, 1)));
				}
			}
		} elseif (strpos($operation, 'fulldelete') === 0) {
			$cats = array();
			$res = DB::query("SELECT `id` FROM `".PREFIX."category`");
			while ($row = DB::fetchAssoc($res)) {
				$cats[] = $row['id'];
				MG::get('category')->delCategory($row['id']);
			}
			if ($_POST['dropProducts'] == 'true') {
				$model = new Models_Product;
				$cats = implode(', ', $cats);
				$res = DB::query('SELECT `id` FROM `'.PREFIX.'product` WHERE `cat_id` IN ('.$cats.')');
				while($row = DB::fetchAssoc($res)) {
					$model->deleteProduct($row['id']);
				}
			}
		} elseif (strpos($operation, 'activity') === 0 && !empty($_POST['category_id'])) {
			$act = substr($operation, -1, 1);
			foreach ($_POST['category_id'] as $catId) {        
				MG::get('category')->updateCategory(array('id' => $catId, 'activity' => $act));
				DB::query('UPDATE `'.PREFIX.'product` SET `activity`='.DB::quote($act).' WHERE `cat_id`='.DB::quoteInt($catId));
			}
		}
		Storage::clear('category');
		return true;
	}
 /**
	 * Выполняет операцию над отмеченными страницами в админке.
	 * @return bool
	 */
	public function operationPage() {
		if(USER::access('page') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$operation = $_POST['operation'];

		if (empty($_POST['page_id']) && $operation != 'fulldelete') {
			$this->messageError = $this->lang['CHECK_PAGE'];
			return false;
		}
		if ($operation == 'delete') {
			foreach ($_POST['page_id'] as $pageId) {
				MG::get('pages')->delPage($pageId);
			}
		} elseif (strpos($operation, 'invisible') === 0 && !empty($_POST['page_id'])) {
			foreach ($_POST['page_id'] as $pageId) {
				MG::get('pages')->updatePage(array('id' => $pageId, 'invisible' => substr($operation, -1, 1)));
			}
		} elseif (strpos($operation, 'fulldelete') === 0) {
			DB::query("TRUNCATE `".PREFIX."page`");
		}
		return true;
	}

	/**
	 * Получает параметры заказа.
	 */
	public function getOrderData() {
		unset($_SESSION['deliveryAdmin']);

		$model = new Models_Order();
		$orderData = $model->getOrder(" id = ".DB::quote(intval($_POST['id'])));
		$orderData = $orderData[$_POST['id']];
		
		if ($orderData['number']=='') {
			$orderData['number'] = $orderData['id'];
			DB::query("UPDATE `".PREFIX."order` SET `number`= ".DB::quote($orderData['number'])." WHERE `id`=".DB::quote($orderData['id'])."");
		} 
			
		$orderData['yur_info'] = unserialize(stripslashes($orderData['yur_info']));
		$orderData['order_content'] = unserialize(stripslashes($orderData['order_content']));
		// Запрос для проверки, существует ли система скидок
		$percent = false;
		$discountSyst = false;
		$res = DB::query('SELECT * FROM `'.PREFIX.'plugins` WHERE `folderName` = "discount-system"');
		$act = DB::fetchArray($res);
		$result = DB::query('SHOW TABLES LIKE "'.PREFIX.'discount-system%"');
		if ((DB::numRows($result) == 2)&&($act['active'])) {        
			$percent = 0; 
			$discountSyst = true;
		}     
		if (!empty($orderData['order_content'])) {
			$product = new Models_Product();

			foreach ($orderData['order_content'] as &$item) {
				foreach ($item as &$v) {
					$v = rawurldecode($v);
				}
			}

			foreach ($orderData['order_content'] as &$items) {
				$res = $product->getProduct($items['id']);
				$items['image_url'] = mgImageProductPath($res['image_url'], $items['id'], 'small');
				$items['property'] = htmlspecialchars_decode(str_replace('&amp;', '&', $items['property']));
				$response['discount'] = $items['discount'];
				$percent = $items['discount'];
				$items['maxCount'] = $res['count'];
				$items['category_unit'] = $res['category_unit'];
				$variants = DB::query("SELECT `id`, `count` FROM `".PREFIX."product_variant`
									WHERE `product_id`=".DB::quote($items['id'])." AND `code`=".DB::quote($items['code']));
				if ($variant = DB::fetchAssoc($variants)) {
					$items['variant'] = $variant['id'];
					$items['maxCount'] = $variant['count'];
				}
				$items['notSet'] = $model->notSetGoods($items['id']);
				$items['price'] = MG::numberDeFormat($items['price']);
			}
		}

		//заменить на получение скидки
		$codes = array();
	
		
		// Запрос для проверки , существуют ли промокоды.  
		$result = DB::query('SHOW TABLES LIKE "'.PREFIX.'promo-code"');
		if (DB::numRows($result)) {
			$res = DB::query('SELECT * FROM `'.PREFIX.'plugins` WHERE `folderName` = "promo-code"');
			$act = DB::fetchArray($res);
			if ($act['active']) {
				$res = DB::query('SELECT code, percent FROM `'.PREFIX.'promo-code` 
					WHERE invisible = 1 
					AND now() >= `from_datetime`
					AND now() <= `to_datetime`');
				while ($code = DB::fetchAssoc($res)) {
					$codes[] = $code['code'];
					if ($code['code'] == $orderData['order_content'][0]['coupon']) {
						$percent = $percent== 0 ? $code['percent'] : $percent;
					}
				}
			};
		}

		$result = DB::query('SHOW TABLES LIKE "'.PREFIX.'oik-discount-coupon"');
		if (DB::numRows($result)) {
			$res = DB::query('SELECT * FROM `'.PREFIX.'plugins` WHERE `folderName` = "oik-discount-coupon"');
			$act = DB::fetchArray($res);
			if ($act['active']) {
				$res = DB::query('SELECT code, value FROM `'.PREFIX.'oik-discount-coupon` 
					WHERE activity = 1 
					AND now() >= `date_active_from`
					AND now() <= `date_active_to`');
				while ($code = DB::fetchAssoc($res)) {
					$codes[] = $code['code'];
					if ($code['code'] == $orderData['order_content'][0]['coupon']) {
						$percent = $percent== 0 ? $code['percent'] : $percent;
					}
				}
			};
		}

		$response['order'] = $orderData;
		$response['order']['discountsSystem'] = $discountSyst;
		$response['order']['discontPercent'] = $percent;
		$response['order']['promoCodes'] = $codes;
		$response['order']['date_delivery'] = $orderData['date_delivery'] ? date('d.m.Y', strtotime($orderData['date_delivery'])) : '';
		$deliveryArray = $model->getDeliveryMethod();
		
		foreach($deliveryArray as $delivery) {
			if(empty($delivery['plugin'])) {
				$delivery['plugin'] = '';
			}
			$response['deliveryArray'][] = $delivery;
		}
				
		$paymentArray = array();
		$i = 1;
		while ($payment = $model->getPaymentMethod($i)) {            
			$payment['name'] .= mgGetPaymentRateTitle($payment['rate']); 
			$paymentArray[$i] = $payment;
			$i++;
		}        
		
		$response['paymentArray'] = $paymentArray;   

		$response['order']['address_imploded'] = '';
		if ($response['order']['address_parts']) {
			$response['order']['address_parts'] = unserialize(stripcslashes($response['order']['address_parts']));
			$response['order']['address_imploded'] = array_filter($response['order']['address_parts']);
			foreach ($response['order']['address_imploded'] as $key => $value) {
				$response['order']['address_imploded'][$key] = htmlspecialchars_decode($value);
			}
			$response['order']['address_imploded'] = implode(', ', $response['order']['address_imploded']);
		}

		$this->data = $response;
		return true;
	}

	/**
	 * Устанавливает флаг редактирования сайта.
	 * @return bool
	 */
	public function setSiteEdit() {
		Storage::clear();
		$_SESSION['user']->enabledSiteEditor = $_POST['enabled'];
		return true;
	}

	/**
	 * Очишает таблицу с кэшем объектов.
	 * @return bool
	 */
	public function clearСache() {
		Storage::clear();
		return true;
	}
	
	
	/**
	 * Удаляет папку с собранными картинками для минифицированного css.
	 * @return bool
	 */
	public function clearImageCssСache() {
		MG::clearMergeStaticFile(PATH_TEMPLATE.'/cache/');
		MG::createImagesForStaticFile(); 
		MG::createFontsForStaticFile();
		return true;
	}

	/**
	 * Возвращает список найденых продуктов по ключевому слову.
	 * @return bool
	 */
	public function uploadCsvToImport() {
		$uploader = new Upload(false);
		$tempData = $uploader->addImportCatalogCSV();

		$this->data = array('img' => $tempData['actualImageName']);

		if ($tempData['status'] == 'success') {
			$this->messageSucces = $this->lang['FILTER_UPLOADED'];
			return true;
		} else {
			$this->messageError = $tempData['msg'];
			return false;
		}
	}
	
	/**
	 * Импортирует структуру категорий из CSV файла.
	 * @return bool
	 */
	public function startImportCategory() {
		if(USER::access('category') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageSucces = $this->lang['PROCESS_START'];
		$this->messageError = $this->lang['IMPORT_START_FAIL'];

		unset($_SESSION['import']);
		
		$import = new Import("Category");
		
		if (empty($_POST['rowId'])) {
			unset($_SESSION['stopProcessImportCsv']);
		}
		
		if ($_POST['delCatalog'] !== null) {
			if ($_POST['delCatalog'] === "true") {
				DB::query('TRUNCATE TABLE `'.PREFIX.'cache`');
				
				if ($_POST['rowId'] == 0) {
					DB::query('TRUNCATE TABLE `'.PREFIX.'category`');
				}
			}
		}
		
		$this->data = $import->startCategoryUpload($_POST['rowId']);

		unset($_SESSION['import']);
		
		if($this->data['status']=='error') {
			$this->messageError = $this->data['msg'].'';
			return false;
		}
		
		return true;
	}

	/**
	 * Импортирует данные из файла importCatalog.csv.
	 * @return bool
	 */
	public function startImport() {
		if(USER::access('product') < 2) {
			$this->messageError = $this->lang['ACCESS_IMPORT'];
			return false;
		}
		$this->messageSucces = $this->lang['PROCESS_START'];
		$this->messageError = $this->lang['IMPORT_START_FAIL'];

		// удаляем временный массив данных (в теории он как то больше отрабатывать должен) // TODO
		unset($_SESSION['import']);

		$import = new Import($_POST['typeCatalog']);
		if(empty($_POST['rowId'])) {
			$import->log('', true);
			$_SESSION['startImportTime'] = microtime(true);
			@unlink('uploads/tmp.csv');
		}
		$_SESSION['iterationImportTime'] = microtime(true);
		$_SESSION['iterationStartRow'] = $_POST['rowId'];

		if (empty($_POST['rowId'])) {
			unset($_SESSION['stopProcessImportCsv']);
		}

		if ($_POST['delCatalog'] !== null) {
			if ($_POST['delCatalog'] === "true") {
				DB::query('TRUNCATE TABLE `'.PREFIX.'cache`');
				if ($_POST['rowId'] == 0) {
					DB::query('TRUNCATE TABLE `'.PREFIX.'product_variant`');
					DB::query('TRUNCATE TABLE `'.PREFIX.'product`');
					// DB::query('TRUNCATE TABLE `'.PREFIX.'product_user_property`');
					DB::query('TRUNCATE TABLE `'.PREFIX.'product_user_property_data`');
					DB::query('TRUNCATE TABLE `'.PREFIX.'property`');
					DB::query('TRUNCATE TABLE `'.PREFIX.'property_data`');
					DB::query('TRUNCATE TABLE `'.PREFIX.'property_group`');
					DB::query('TRUNCATE TABLE `'.PREFIX.'category`');
					DB::query('TRUNCATE TABLE `'.PREFIX.'category_user_property`');

				}
			}
		}

		// удаляем картинки
		if ($_POST['delImages'] !== null) {
			if ($_POST['delImages'] === "true") {
				if ($_POST['rowId'] == 0) {
					MG::rrmdir(SITE_DIR.'uploads/product');
					// MG::rrmdir(SITE_DIR.'uploads/thumbs');
				}
			}
		}

		$this->data = $import->startUpload($_POST['rowId'], $_POST['schemeType'], $_POST['downloadLink'], $_POST['iteration']);
		
		if($this->data['status']=='error') {
			$this->messageError = $this->data['msg'].'';
			return false;
		}
		
		return true;
	}

	/**
	 * Останавливает процесс импорта каталога из файла importCatalog.csv.
	 * @return bool
	 */
	public function canselImport() {
		$this->messageSucces = $this->lang['IMPORT_CANCEL'];
		$this->messageError = $this->lang['IMPORT_CANCEL_FAIL'];

		$import = new Import();
		$import->stopProcess();

		return true;
	}

	/**
	 * Сохраняет реквизиты в настройках заказа.
	 * @return bool
	 */
	public function savePropertyOrder() {
		if(USER::access('order') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageSucces = $this->lang['ACT_SAVE_SETNG'];
		$this->messageError = $this->lang['SAVE_FAIL'];

		$propertyOrder = serialize($_POST);
		$propertyOrder = addslashes($propertyOrder);
		MG::setOption(array('option' => 'propertyOrder', 'value' => $propertyOrder));

		return true;
	}

	/**
	 * Получает данные об ошибке произошедшей в админке и отправляет на support@moguta.ru.
	 * @return bool
	 */
	public function sendBugReport() {
		$this->messageSucces = $this->lang['ADMIN_LOCALE_2'];
		$this->messageError = $this->lang['REPORT_SEND_FAIL'];

		$body .= 'Непредвиденная ошибка на сайте '.$_SERVER['SERVER_NAME'];
		$body .= '<br/><br/><br/><strong>Информация о системе</strong>';
		$body .= '<br/>Версия Moguta.CMS: '.VER;
		$body .= '<br/>Версия php: '.phpversion();
		$body .= '<br/>USER_AGENT: '.$_SERVER['HTTP_USER_AGENT'];
		$body .= '<br/>IP: '.$_SERVER['SERVER_ADDR'];

		$body .= '<br/><strong>Информация о магазине</strong>';
		$product = new Models_Product;
		$body .= '<br/>Количество товаров: '.$product->getProductsCount();
		$body .= '<br/>Количество категорий: '.MG::get('category')->getCategoryCount();
		$body .= '<br/>Шаблон: '.MG::getSetting('templateName');
		$body .= '<br/>E-mail администратора: '.MG::getSetting('adminEmail');

		$body .= '<br/><strong>Баг-репорт</strong>';
		$body .= '<br/>'.$_POST['text'];
		$body .= '<br/><br/><img alt="Embedded Image" src="data:'.$_POST['screen'].'" />';
		Mailer::addHeaders(array("Reply-to" => MG::getSetting('adminEmail')));
		Mailer::sendMimeMail(array(
			'nameFrom' => MG::getSetting('adminEmail'),
			'emailFrom' => MG::getSetting('adminEmail'),
			'nameTo' => "support@moguta.ru",
			'emailTo' => "support@moguta.ru",
			'subject' => "Отчет об ошибке с сайта ".$_SERVER['SERVER_NAME'],
			'body' => $body,
			'html' => true
		));

		return true;
	}

	/**
	 * Устанавливает тестовое соединение с сервером Memcache.
	 * @return bool
	 */
	public function testMemcacheConection() {
		if(class_exists('Memcached')) {
			$memcache_obj = new Memcached(); 
			$memcache_obj->addServer($_POST['host'], $_POST['port']); 
			$ver = $memcache_obj->getVersion();
			if (!empty($ver)) {
				$this->messageSucces = $this->lang['MEMCACHE_CONNECT_SUCCESS'].' '.$ver[$_POST['host'].":".$_POST['port']];
				return true;
			}
			$this->messageError = $this->lang['MEMCACHE_CONNECT_FAIL'].' '.$_POST['host'].":".$_POST['port'];
			return false;
		}
		if (class_exists('Memcache')) {
			$memcacheObj = new Memcache();
			$memcacheObj->connect($_POST['host'], $_POST['port']);
			$ver = $memcacheObj->getVersion();
			if (!empty($ver)) {
				$this->messageSucces = $this->lang['MEMCACHE_CONNECT_SUCCESS'].' '.$ver;
				return true;
			}
			$this->messageError = $this->lang['MEMCACHE_CONNECT_FAIL'].' '.$_POST['host'].":".$_POST['port'];
			return false;
		}
		$this->messageError = $this->lang['MEMCACHE_MISSING'];
		return false;
	}

	/**
	 * Упорядочивает всё дерево категорий по алфавиту.
	 * @return bool
	 */
	public function sortToAlphabet() {
		if(USER::access('category') < 2) {
			$this->messageError = $this->lang['ACCESS_CREATE_PRODUCT'];
			return false;
		}
		MG::get('category')->sortToAlphabet();
		return true;
	}
	
	/**
	 * Выполняет операцию над отмеченными пользователями в админке.
	 * @return bool
	 */
	public function operationUser() {
		if(USER::access('user') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$operation = $_POST['operation'];
		if (empty($_POST['users_id']) && $operation != 'fulldelete') {
			$this->messageError = $this->lang['CHECK_USER'];
			return false;
		}
		$result = false;
		if ($operation == 'delete') {
			foreach ($_POST['users_id'] as $userId) {
				$del = USER::delete($userId);
				if (!$del) {
					$this->messageSucces = $this->lang['USER_DELETED_NOT_ADMIN'];
					$result = true;
				}
			}
		} elseif (strpos($operation, 'getcsvuser') === 0 && !empty($_POST['users_id'])) {     
				$filename = USER::exportToCsvUser($_POST['users_id']); 
				$this->data['filecsv'] = $filename;            
				$this->messageSucces = $this->lang['USER_IMPORT_SUCCESS'].' '.$filename;
				$result = true;
		} elseif (strpos($operation, 'fulldelete') === 0) {     
				DB::query('DELETE FROM `'.PREFIX.'user` WHERE `role` > 1');
				$result = true;
		}
		elseif (strpos($operation, 'changeowner') === 0) {
			DB::query('UPDATE '.PREFIX.'user SET owner = '.DB::quoteInt($_POST['param']).' WHERE id IN ('.DB::quoteIN($_POST['users_id']).')');
			$result = true;
		}
		return $result;
	}
	/**
	 * Получает следующий id для таблицы продуктов.
	 * @return bool
	 */
	public function nextIdProduct() {
		$result['id'] = 0;
		if(USER::access('product') < 2) return false;
		$res = DB::query('SHOW TABLE STATUS WHERE Name =  "'.PREFIX.'product" ');
		if ($row = DB::fetchArray($res)) {
			$result['id'] = $row['Auto_increment'];
		}
		$result['prefix_code'] = 'CN';
		if (MG::getSetting('prefixCode')) {$result['prefix_code'] = MG::getSetting('prefixCode');}
		$this->data = $result;
		return true;
	}
	
	/**
	 * Добавляет новый favicon.
	 * @return bool
	 */
	public function updateFavicon() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$uploader = new Upload(false);   
		$tempData = $uploader->addFavicon();
		$this->data = array('img' => $tempData['actualImageName']);

		if ($tempData['status'] == 'success') {
			$this->messageSucces = $tempData['msg'];
			return true;
		} else {
			$this->messageError = $tempData['msg'];
			return false;
		}
	}

	/**
	 * Изменяет логотип в панели управления.
	 * @return bool
	 */
	public function updateCustomAdmin() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$uploader = new Upload(false);   
		$tempData = $uploader->addImage(false, false);

		$this->data = array('img' => $tempData['actualImageName']);

		if ($tempData['status'] == 'success') {
			$this->messageSucces = $tempData['msg'];
			return true;
		} else {
			$this->messageError = $tempData['msg'];
			return false;
		}
	}
 
	/**
	 * Функция для получения необходимых настроек из js скриптов.
	 * @param $options имя, или массив имен опций.
	 * @return bool
	 */
	public function getSetting() { 
		$setting = $_POST['setting'];
		$this->data = array($setting => MG::getSetting($setting));
		return true;
	}
	
	/**
	 * Сохраняет настройки страницы с применными фильтрами.
	 * @return bool
	 */
	public function saveRewrite() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageSucces = $this->lang['ACT_SUCCESS'];
		$this->messageError = $this->lang['ACT_ERROR'];
		
		if(empty($_POST['url']) || empty($_POST['short_url'])) {
			return false;
		}
				
		$this->data = Urlrewrite::setUrlRewrite($_POST);
		return true;
	}
	
	/**
	 * Возвращает запись о странице с применеными фильтрами.
	 * @return bool
	 */
	public function getRewriteData() {
		$this->messageSucces = $this->lang['ACT_SUCCESS'];
		$this->messageError = $this->lang['ACT_ERROR'];
		
		if(empty($_POST['id'])) {
			$this->messageError = $this->lang['NONE_ID'];
			return false;
		}
		
		$this->data = Urlrewrite::getUrlRewriteData($_POST['id']);
		return true;
	}
	
	/**
	 * Меняет настройки активности страницы с применными фильтрами.
	 * @return bool
	 */
	public function setRewriteActivity() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageSucces = $this->lang['ACT_SUCCESS'];
		$this->messageError = $this->lang['ACT_ERROR'];
		
		if(empty($_POST['id'])) {
			$this->messageError = $this->lang['NONE_ID'];    
			return false;
		}
		
		if(!isset($_POST['activity'])) {
			$this->messageError = $this->lang['NONE_ACTIVE_VALUE']; 
			return false;
		}
		
		if(Urlrewrite::setActivity($_POST['id'], $_POST['activity'])) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Удаляет страницу с примененными фильтрами.
	 * @return bool
	 */
	public function deleteRewrite() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageSucces = $this->lang['ACT_SUCCESS'];
		$this->messageError = $this->lang['ACT_ERROR'];
		
		if(empty($_POST['id'])) {
			$this->messageError = $this->lang['NONE_ID'];    
			return false;
		}
		
		if(Urlrewrite::deleteRewrite($_POST['id'])) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Добавляет новую запись перенаправления.
	 */
	public function addUrlRedirect() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageSucces = $this->lang['STNG_SEO_URL_REDIRECT_ADD_SUCCESS'];
		$res = DB::query("
			INSERT INTO `".PREFIX."url_redirect`
			VALUES ('','','','',1)"
		);
		
		if($id = DB::insertId()) {      
			$this->data = array(
				'id' => $id,
				'url_old' => '',
				'url_new' => '',
				'code' => '',        
			);
		} else {
			$this->messageError = $this->lang['STNG_SEO_URL_REDIRECT_ADD_FAIL'];
			return false;
		}
		
		return true;
	}
	
	/**
	 * Сохраняет запись перенаправления.
	 */
	public function saveUrlRedirect() {    
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageSucces = $this->lang['ACT_EDIT_REDIRECT'];    
		$result = false;    
		$id = $_POST['id'];
		$array = $_POST;
		$array['url_new'] = URL::prepareUrl(htmlspecialchars($array['url_new']), false, false);
		
		if (!empty($id)) {       
			if (DB::query('
				UPDATE `'.PREFIX.'url_redirect`
				SET '.DB::buildPartQuery($array).'
				WHERE id ='.DB::quote($id))) {
				$result = true;
			}      
		}
		return $result;
	}
	/**
	 * Изменяем активность записи перенаправления.
	 * @return bool
	 */
	public function setUrlRedirectActivity() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageSucces = $this->lang['ACT_SUCCESS'];
		$this->messageError = $this->lang['ACT_ERROR'];
		
		if(empty($_POST['id'])) {
			$this->messageError = $this->lang['NONE_ID'];    
			return false;
		}
		
		if(!isset($_POST['activity'])) {
			$this->messageError = $this->lang['NONE_ACTIVE_VALUE']; 
			return false;
		}
		
		if (DB::query('
			UPDATE `'.PREFIX.'url_redirect`
			SET `activity` = '.DB::quote($_POST['activity'], 1).'
			WHERE id ='.DB::quote($_POST['id'], 1))) {
			return true;
		}  
		
		return false;
	}
	
	/**
	 * Меняет настройки активности переадресаций.
	 * @return bool
	 */
	public function deleteUrlRedirect() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageSucces = $this->lang['ACT_SUCCESS'];
		$this->messageError = $this->lang['ACT_ERROR'];
		
		if(empty($_POST['id'])) {
			$this->messageError = $this->lang['NONE_ID'];    
			return false;
		}
		
		if (DB::query('
			DELETE 
			FROM `'.PREFIX.'url_redirect`      
			WHERE id ='.DB::quote($_POST['id'], 1))) {
			return true;
		}  
		
		return false;
	}
	/**
	 * Создает в корневой папке сайта карту в формате XML.
	 */
	public function generateSitemap() {
		$this->messageSucces = $this->lang['SITEMAP_CREATED'];
		$this->messageError = $this->lang['SITEMAP_NOT_CREATED'];  
		$urls = Seo::autoGenerateSitemap();
		if ($urls) {
			$msg = $this->lang['MSG_SITEMAP1']." ".MG::dateConvert(date("d.m.Y"), true).'. '.$this->lang['SITEMAP_COUNT_URL'].' '.$urls;
			$this->data = array('msg' => $msg);
			return true;
		} else {
			return false;
		}
		
	}

	/**
	 * Функция для загрузки архива с изображениями.
	 */
	public function uploadImagesArchive() {      
		if(USER::access('product') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}   
		$tempData = Upload::addImagesArchive();    
		$this->data = array('file' => $tempData['actualImageName']);

		if ($tempData['status'] == 'success') {
			$this->messageSucces = $tempData['msg'];
			return true;
		} else {
			$this->messageError = $tempData['msg'];
			return false;
		}	
	}
	
	/**
	 * Загружает архив изображений для каталога.
	 * @return bool
	 */
	public function selectImagesArchive() {
		$tempData = Upload::addImagesArchive($_POST['data']['filename']);    

		if ($tempData['status'] == 'success') {
			$this->messageSucces = $tempData['msg'];
			return true;
		} else {
			$this->messageError = $tempData['msg'];
			return false;
		}	    
	}
	
	/**
	 * Запускает процесс генерации изображений для товаров.
	 * @return bool
	 */
	public function startGenerationImagePreview() {    
		$upload = new Upload(false);
		
		if($uploadResult = $upload->generatePreviewPhoto()) {
			$this->messageSucces = $uploadResult['messageSucces'];
			$this->data = $uploadResult['data'];
		} else {
			$this->messageError = "Error!";
		}    
		
		return true;
	}

	/**
	 * Создает в mg-pages файл getyml по обращению к которому происходит выгрузка каталога в yml формате.
	 * @return bool
	 */
	public function createYmlLink() {
		$this->messageSucces = $this->lang['YML_LINK_CREATE_SUCCESSFUL'];
		$this->messageError = $this->lang['YML_LINK_CREATE_ERROR'];  
		$name = MG::getSetting('nameOfLinkyml') ? MG::getSetting('nameOfLinkyml') : 'getyml';
		if (!file_exists(PAGE_DIR.$name.'.php')) {
			$code = "<?php \$yml= new YML(); header(\"Content-type: text/xml; \");echo  \$yml->exportToYml(array(),true); ?>";
			$f = fopen(PAGE_DIR.$name.'.php', 'w');
			$result = fwrite($f, $code);
			fclose($f);
			if ($result) {
				$this->data = SITE.'/'.$name;
				return true;
			}
		} else {
			$this->data = SITE.'/'.$name;
			return true;
		}
		return false;
	}

	/**
	 * Сохраняет новое имя для файла с выгрузкой yml.
	 * @return bool
	 */
	public function renameYmlLink() {
		$this->messageSucces = $this->lang['YML_LINK_RENAME_SUCCESSFUL'];
		$this->messageError = $this->lang['YML_LINK_RENAME_ERROR'];  
		$oldname = MG::getSetting('nameOfLinkyml') ? MG::getSetting('nameOfLinkyml') : 'getyml';
		$newname = !empty($_POST['name']) ? $_POST['name']: 'getyml';
		if (preg_match('/[^0-9a-zA-Z]/', $newname)) {
			$this->messageError = $this->lang['YML_LINK_NAME_ERROR'];
			return false;
		}
		if (rename(PAGE_DIR.$oldname.'.php', PAGE_DIR.$newname.'.php')) {
			MG::setOption('nameOfLinkyml', $newname);
			return true;      
		} else {
			return false;
		}
	}

	/**
	 * Получает список адресов покупателей.
	 * @return bool
	 */
	public function getBuyerEmail() {
		$result = array();
		$res = DB::query('SELECT `email` FROM `'.PREFIX.'user` WHERE `email` LIKE "%'.DB::quote($_POST['email'], true).'%"');
		while ($row = DB::fetchArray($res)) {
			$result[] = $row['email'];
		}
		$this->data = $result;
		return true;
	}

	/**
	 * Получает информацию по email покупателя.
	 * @return bool
	 */
	public function getInfoBuyerEmail() {     
		$result = array();
		$res = DB::query('SELECT * FROM `'.PREFIX.'user` WHERE `email` ='.DB::quote($_POST['email']));
		if ($row = DB::fetchArray($res)) {
			$result = $row;
		}
		$this->data = $result;
		return true;
	}

	/**
	 * Тестовая отправка письма администратору.
	 * @return bool
	 */
	public function testEmailSend() {
		$this->messageSucces = $this->lang['SEND_EMAIL_TEST_SUCCESSFUL'].' '.MG::getSetting('adminEmail');
		$this->messageError = $this->lang['SEND_EMAIL_TEST_ERROR']; 
		$result = true;
		$sitename = MG::getSetting('sitename');
		$mails = explode(',', MG::getSetting('adminEmail'));
		if (empty($mails)) {
			$result = false;
		}
		$message = '
				Здравствуйте!<br>
					Вы получили данное письмо при тестировании отправки почты с сайта '.$sitename.'.<br>
						Если вы получили данное письмо, значит почта на сайте настроена корректно.
					Отвечать на данное сообщение не нужно.';
		
		foreach ($mails as $mail) {
			if (preg_match('/^[-._a-zA-Z0-9]+@(?:[a-zA-Z0-9][-a-zA-Z0-9]+\.)+[a-zA-Z]{2,6}$/', $mail)) {
				$res = Mailer::sendMimeMail(array(
					'nameFrom' => $sitename,
					'emailFrom' => MG::getSetting('noReplyEmail'),
					'nameTo' => "Администратору ".$sitename,
					'emailTo' => $mail,
					'subject' => 'Тестирование почты на сайте '.$sitename,
					'body' => $message,
					'html' => true
				));
				if (!$res) {
					$result = false;
				}
			}
			else{
				$result = false;
			}
		}
		return $result;
	}

	/**
	 * Информация о сопутствующих  категориях.
	 * @return bool
	 */
	public function getRelatedCategory() {
		$data = array();
		$cats = implode(',', $_POST['cat']);
		$res =  DB::query('SELECT `id`, `title`, `url`, `parent_url`, `image_url` FROM `'.PREFIX.'category` WHERE `id` IN ('.DB::quote($cats, true).')');
		while ($row = DB::fetchArray($res)) {
			$data[] = $row;
		}
		return $this->data = $data;
	}
	/**
	 * Функция для AJAX запроса генерации SEO тегов по шаблонам, 
	 * при заполнении карточки сущности(товар/категория/страница).
	 * @return bool
	 */
	public function generateSeoFromTmpl() {
		
		return true;
	}
	
	public function getSessionLifeTime() {
		$sessionLifeTime = Storage::getSessionLifeTime();
		
		if (isset($_POST['a']) && $_POST['a'] == 'ping') {
			$sessionExpires = Storage::getSessionExpired($_COOKIE['PHPSESSID']);
			$this->data['sessionLifeTime'] = $sessionExpires + $sessionLifeTime - time();
			$this->data['timeWithoutUser'] = time() - $sessionExpires;
		} else {
			$this->data['sessionLifeTime'] = $sessionLifeTime;
		}
		
		return true;
	}

	public function updateSession() {
		return true;
	}
	
	/**
	 * Возвращает информацию о том, авторизован ли пользователь.
	 * @return bool
	 */
	public function isUserAuth() {
		$this->data = array(
			'auth' => USER::getThis()
		);
		return true;
	}

	/**
	 * Сохранение нового значения характеристики.
	 * @return bool
	 */
	public function saveNewValueProp() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageSucces = $this->lang['CHANGES_ARE_SAVED'];
		$this->messageError = $this->lang['CHANGE_SAVE_FAIL'];
		$result = false;
		$property_id = $_POST['propid']; // id характеристики
		$string = html_entity_decode($_POST['newval']);
		$string = str_replace('$', '\$', $string); // новое значение
		$old = str_replace(array('[', ']', '(', ')', '$'), array('\[', '\]', '\(', '\)', '\$'), $_POST['oldval']); // старое значение
		$sql = " 
				SELECT *
				FROM `" . PREFIX . "product_user_property`
				WHERE `property_id` = " . DB::quote($property_id);
		$res = DB::query($sql);//запрос выбора БД

		while ($row = DB::fetchAssoc($res)) {//пробегаем по каждому значению полей product_margin И value
			$replacedvar = '';
			$replacedvarvalue = '';
			if ($row['product_margin']!= '') {
				 $replacedvar = preg_replace('~(^|\|)(' . $old . ')($|[#|\|])~', '${1}' . $string . '$3', $row['product_margin']);//замена на новую хар-ку
			}
			if ($row['value'] != '') {
				$replacedvarvalue = preg_replace('~(^|\|)(' . $old . ')($|[#|\|])~', '${1}' . $string . '$3', $row['value']);//замена в поле value
			}      
			DB::query("UPDATE `" . PREFIX . "product_user_property` 
				SET `product_margin`= " . DB::quote($replacedvar) . ", `value`= " . DB::quote($replacedvarvalue) . " WHERE `property_id` = " . DB::quote($property_id) . " AND `product_id` = " . DB::quote($row['product_id']) . " ");//запрос замены
		 }

		$res = DB::query('SELECT `data` FROM `' . PREFIX . 'property` WHERE `id`=' . DB::quote($property_id));

		if ($row = DB::fetchArray($res)) {
			$replacedvar = ''; 
			if ($row['data'] != '') {
				$replacedvar = preg_replace('~(^|\|)(' . $old . ')($|[#|\|])~', '${1}' . $string . '$3', $row['data']);//замена на новую хар-ку
				DB::query("UPDATE `" . PREFIX . "property` 
				SET `data`= " . DB::quote($replacedvar) . " WHERE `id` = " . DB::quote($property_id));//запрос замены
			}      
			$result = true;
		}

		$newdata = preg_replace('~(^|\|)(' . $old . ')($|[#|\|])~', '${1}' . $string . '$3', $_POST['olddata']);//замена на новую хар-ку
		$this->data = $newdata;

		return $result;
	}

	/**
	 * Сохраняет порядок строк в таблице с страницами и категориями.
	 * @return bool
	 */
	public function saveSortableTable() {
		$data = $_POST['data'];
		switch ($_POST['type']) {
			case 'page':
				if(USER::access('page') < 2) {
					$this->messageError = $this->lang['ACCESS_EDIT'];
					return false;
				}
				$this->messageSucces = $this->lang['CHANGE_SORT_PAGE'];
				$typeEntity = 'page';
				break;
			case 'category':
				if(USER::access('category') < 2) {
					$this->messageError = $this->lang['ACCESS_EDIT'];
					return false;
				}
				$this->messageSucces = $this->lang['CHANGE_SORT_CAT'];
				$typeEntity = 'category';
				break;
		}

		// составления массива запросов для изменения порядка сортировки
		foreach ($data as $key => $id) {
			$sqlQueryTo[] = 'UPDATE `'.PREFIX.DB::quote($typeEntity,true).'` SET sort = '.DB::quote($key).' WHERE id = '.DB::quote($id);
		}

		foreach ($sqlQueryTo as $sql) {
			DB::query($sql);
		}

		return true;
	}

	/**
	 * Заполняет SEO настройки для товаров, категорий и страниц по шаблону.
	 * @return bool
	 */
	public function setSeoForGroup() {
		if(USER::access('product') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		} 
		$this->messageSucces = $this->lang['SEO_GEN_TMPL_SUCCESS'];
		$this->messageError = $this->lang['SEO_GEN_TMPL_FAIL']; 

		$result = Seo::getMetaByTemplateForAll($_POST['data']);

		if(!$result) {
			$this->messageError = $this->lang['DB_USER_ACCESS_LOW']; 
			return false;
		}

		return true;
	}

	/**
	 * Устанавливает стили панели управления.
	 * @return bool
	 */
	public function saveInterface() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		} 
		MG::setOption('languageLocale', $_POST['languageLocale']);
		MG::setOption('interface', addslashes(serialize($_POST['data'])));
		MG::setOption('themeBackground', $_POST['bg']);

		MG::setOption('customBackground', $_POST['customBG']);
		MG::setOption('bgfullscreen', $_POST['fullscreen']);

		$path = URL::getDocumentRoot().'uploads'.DS.'customAdmin';

		if (is_dir($path)) {
			$handle = opendir($path);
			while(false !== ($file = readdir($handle))) {
				if($file != '.' && $file != '..' && $file != $_POST['customBG'] && $file != $_POST['customLogo'] && !is_dir($file)) { 
					unlink($path.DS.$file);
				}
			}
			closedir($handle); 
		}

		return true;
	}

	/**
	 * Устанавливает стандартные стили панели управления.
	 * @return bool
	 */
	public function defaultInterface() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		} 
		$data['colorMain'] = '#2773eb';
		$data['colorLink'] = '#1585cf';
		$data['colorSave'] = '#4caf50';
		$data['colorBorder'] = '#e6e6e6';
		$data['colorSecondary'] = '#ebebeb';
		MG::setOption('interface', addslashes(serialize($data)));
		return true;
	}

	/**
	 * Сохраняет настройки API.
	 * @return bool
	 */
	public function saveApi() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageSucces = $this->lang['API_SAVED'];
		$this->messageError = $this->lang['ERROR']; 
		MG::setOption('API', addslashes(serialize($_POST['data'])));
		return true;
	}

	/**
	 * Метод генерации токена для API.
	 * @return bool
	 */
	public function createToken() {
		$this->data = md5(microtime(true).SITE);
		return true;
	}


	/**
	 * Изменяет видимость способов доставки и оплаты по клику на лампочку.
	 * @return bool
	 */
	public function changeActivityDP() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageSucces = $this->lang['VIEW_CHANGE'];
		$this->messageError = $this->lang['ERROR']; 

		switch ($_POST['tab']) {
			case 'delivery':
				$tab = 'delivery';
				break;
			case 'payment':
				$tab = 'payment';
				break;
			
			default:
				return false;
		}

		DB::query('UPDATE '.PREFIX.DB::quote($tab,true).' SET activity = '.DB::quote($_POST['status']).' WHERE id = '.DB::quote($_POST['id']));

		return true;
	}

	/**
	 * В категориях подгружает запрашиваемые подкатегории.
	 * @return bool
	 */
	public function showSubCategory() {
		$res = DB::query('SELECT DISTINCT * FROM '.PREFIX.'category WHERE parent = '.DB::quote($_POST['id']).' ORDER BY sort ASC');
		while($row = DB::fetchAssoc($res)) {
			$array[] = $row;
		}
		$this->data = Category::getPages($array, $_POST['level']-1, $_POST['id']);

		return true;
	}

	/**
	 * В страницах подгружает запрашиваемые страницы.
	 * @return bool
	 */
	public function showSubPage() {
		$res = DB::query('SELECT DISTINCT * FROM '.PREFIX.'page WHERE parent = '.DB::quote($_POST['id']).' ORDER BY sort ASC');
		while($row = DB::fetchAssoc($res)) {
			$array[] = $row;
		}
		$this->data = Page::getPages($array, $_POST['level']-1, $_POST['id']);

		return true;
	}

	/**
	 * В категориях подгружает запрашиваемые подкатегории.
	 * @return bool
	 */
	public function showSubCategorySimple() {
		$res = DB::query('SELECT DISTINCT * FROM '.PREFIX.'category WHERE parent = '.DB::quote($_POST['id']).' ORDER BY sort ASC');
		while($row = DB::fetchAssoc($res)) {
			$array[] = $row;
		}
		$this->data = Category::getPagesSimple($array, $_POST['level']-1, $_POST['id']);

		return true;
	}

	//=========================================================
	// ДЛЯ ХАРАКТЕРИСТИК
	//=========================================================

	/**
	 * В разделе настроек характеристик загружает информацию о выбранной характеристики для модального окна.
	 * @return bool
	 */
	public function getProperty() {
		if(USER::access('product') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}

		$res = DB::query('SELECT * FROM '.PREFIX.'property WHERE id = '.DB::quote($_POST['id']));
		while($row = DB::fetchAssoc($res)) {
			$tmp = explode('[prop attr=', $row['name']);
			$row['mark'] = str_replace(']', '', $tmp[1]);
			$row['name'] = $tmp[0];
			MG::loadLocaleData($row['id'], LANG, 'property', $row);
			$row['selectGroup']=Property::getPropertyGroup();
			$property = $row;
		}

		$this->data = $property;

		return true;
	}

	/**
	 * Добавляет поле для характеристики.
	 * @return bool
	 */
	public function addPropertyMargin() {
		if(USER::access('product') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$res = DB::query('SELECT MAX(id) FROM '.PREFIX.'property_data');
		while($row = DB::fetchAssoc($res)) {
			$maxSort = $row['MAX(id)'];
			$maxSort++;
		}
		DB::query('INSERT INTO '.PREFIX.'property_data (prop_id, name, margin, sort) VALUES 
			('.DB::quote($_POST['propId']).', '.DB::quote($_POST['name']).', '.DB::quote($_POST['margin']).', '.DB::quote($maxSort).')');

		return true;
	}

	/**
	 * Загружает дополнительные поля для характеристики.
	 * @return bool
	 */
	public function loadPropertyMargin() {
		$res = DB::query('SELECT * FROM '.PREFIX.'property_data WHERE prop_id = '.DB::quote($_POST['id']).' ORDER BY sort ASC');
		while($row = DB::fetchAssoc($res)) {
			MG::loadLocaleData($row['id'], LANG, 'property_data', $row);
			$propertyData[] = $row;
		}

		$this->data = $propertyData;

		return true;
	}

	/**
	 * Удаляет изображения цвета у характеристики.
	 * @return bool
	 */
	public function deleteImgMargin() {
		if(USER::access('product') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$res = DB::query('SELECT img FROM '.PREFIX.'property_data WHERE id = '.DB::quoteInt($_POST['id']));
		while($row = DB::fetchAssoc($res)) {
			unlink($row['img']);
		}
		DB::query('UPDATE '.PREFIX.'property_data SET img = \'\' WHERE id = '.DB::quoteInt($_POST['id']));
		return true;
	}

	/**
	 * Удаляет характеристику.
	 * @return bool
	 */
	public function deletePropertyMargin() {
		if(USER::access('product') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$res = DB::query('SELECT pd.img, p.type FROM '.PREFIX.'property_data AS pd 
			LEFT JOIN '.PREFIX.'property AS p ON p.id = pd.prop_id 
			WHERE pd.id = '.DB::quoteInt($_POST['id']));
		while ($row = DB::fetchAssoc($res)) {
			@unlink($row['img']);
			if($row['type'] == 'color') {
				DB::query('DELETE FROM '.PREFIX.'product_variant WHERE color = '.DB::quoteInt($_POST['id']));
			}
			if($row['type'] == 'size') {
				DB::query('DELETE FROM '.PREFIX.'product_variant WHERE size = '.DB::quoteInt($_POST['id']));
			}
		}
		DB::query('DELETE FROM '.PREFIX.'property_data WHERE id = '.DB::quoteInt($_POST['id']));
		DB::query('DELETE FROM '.PREFIX.'product_user_property_data WHERE prop_data_id = '.DB::quoteInt($_POST['id']));
		return true;
	}

	/**
	 * Подгружаем размерную сетку для нового загружаемого товара в зависимости от выбранной категории.
	 * @return bool
	 */
	public function loadSizeMapToNewProduct() {
		$this->data = $this->getProdDataWithCat();
		return true;
	}

	/**
	 * Устанавливает изображение для характеристики цвета.
	 * @return bool
	 */
	public function addImageToProp() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_UPLOAD_IMG'];
			return false;
		}

		if($_FILES['propImg']['size'] > 1024 * 512) {
			$this->messageError = $this->lang['UPLOAD_IMG_LIMIT_PROP'];
			return false;
		}

		if(substr_count($_FILES['propImg']['type'], 'image') == '1') {
			$res = DB::query('SELECT img FROM '.PREFIX.'property_data WHERE id = '.DB::quoteInt($_POST['propDataId']));
			while ($row = DB::fetchAssoc($res)) {
				@unlink($row['img']);
			}
			@mkdir('uploads/property-img', 0755);
			$type = explode('.', $_FILES['propImg']['name']);
			$type = end($type);
			$newName = substr(md5(time()), 0, 8);
			move_uploaded_file($_FILES['propImg']['tmp_name'], 'uploads/property-img/'.$newName.'.'.$type);
			DB::query('UPDATE '.PREFIX.'property_data SET img = '.DB::quote('uploads/property-img/'.$newName.'.'.$type).' 
				WHERE id = '.DB::quoteInt($_POST['propDataId']));
		} else {
			$this->messageError = $this->lang['UPLOAD_ONLY_IMG'];
			return false;
		}

		$this->messageSucces = $this->lang['ACT_IMG_UPLOAD'];
		$this->data = 'uploads/property-img/'.$_FILES['propImg']['name'];
		return true;
	}

	//=========================================================
	// ИНТЕГРАЦИИ
	//=========================================================

	/**
	 * Вызов страницы интеграции
	 * @return bool
	 */
	public function getIntegrationPage() {
		$content = '';
		ob_start();
		switch ($_POST['integration']) {
			case 'Avito':
				Avito::createPage();
				break;
			case 'VKUpload':
				VKUpload::createPage();
				break;
			case 'YandexMarket':
				YandexMarket::createPage();
				break;
			case 'GoogleMerchant':
				GoogleMerchant::createPage();
				break;
			case 'MailChimp':
				MailChimp::createPage();
				break;
			case 'RetailCRM':
				RetailCRM::createPage();
				break;
			}
		$content = ob_get_contents();
		ob_end_clean();
		$this->data = $content;
		return true;
	}

	/**
	 * (Интеграция Avito) создание новой выгрузки
	 * @return bool
	 */
	public function newTabAvito() {
		$this->data = Avito::newTab($_POST['name']);
		return true;
	}
	/**
	 * (Интеграция Avito) сохранение настроек выгрузки
	 * @return bool
	 */
	public function saveTabAvito() {
		return Avito::saveTab($_POST['name'], $_POST['data']);
	}
	/**
	 * (Интеграция Avito) удаление существующей выгрузки
	 * @return bool
	 */
	public function deleteTabAvito() {
		return Avito::deleteTab($_POST['name']);
	}
	/**
	 * (Интеграция Avito) загрузка существующей выгрузки
	 * @return bool
	 */
	public function getTabAvito() {
		$this->data = Avito::getTab($_POST['name']);
		return true;
	}
	/**
	 * (Интеграция Avito) построение иерархии категорий Avito
	 * @return bool
	 */
	public function buildSelectsAvito() {
		$this->data = Avito::buildSelects($_POST['id'], $_POST['shopCatId'], $_POST['uploadName']);
		return true;
	}
	/**
	 * (Интеграция Avito) сохранение категории Avito
	 * @return bool
	 */
	public function saveCatAvito() {
		return Avito::saveCat($_POST['shopId'], $_POST['googleId'], $_POST['name'], $_POST['additional']);
	}
	/**
	 * (Интеграция Avito) получение категорий Avito
	 * @return bool
	 */
	public function getCatsAvito() {
		$this->data = Avito::getCats($_POST['name']);
		return true;
	}
	/**
	 * (Интеграция Avito) получение названия категории Avito по ID
	 * @return bool
	 */
	public function getCatNameAvito() {
		$this->data = Avito::getCatName($_POST['id']);
		return true;
	}
	/**
	 * (Интеграция Avito) рекурсивное применение категорий Avito
	 * @return bool
	 */
	public function updateCatsRecursAvito() {
		return Avito::updateCatsRecurs($_POST['shopId'], $_POST['googleId'], $_POST['name']);
	}
	/**
	 * (Интеграция Avito) создание базы категорий Avito
	 * @return bool
	 */
	public function updateDBAvito() {
		return Avito::updateDB();
	}
	/**
	 * (Интеграция Avito) получение списка городов Avito
	 * @return bool
	 */
	public function getCitysAvito() {
		$this->data = Avito::getCitys($_POST['region']);
		return true;
	}
	/**
	 * (Интеграция Avito) получение списка метро и районов Avito
	 * @return bool
	 */
	public function getSubwaysAvito() {
		$this->data = Avito::getSubways($_POST['city']);
		return true;
	}
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/**
	 * (Интеграция VKontakte) сохранение настроек
	 * @return bool
	 */
	public function saveVKUpload() {
		$tmp = array('vkGroupId' => $_POST['vkGroupId'], 'vkAppId' => $_POST['vkAppId'], 'vkApiKey' => $_POST['vkApiKey']);

		MG::setOption(array('option' => 'vkUpload', 'value'  => addslashes(serialize($tmp)), 'active' => 'N'));

		return true;
	}
	/**
	 * (Интеграция VKontakte) коннект и получение категорий ВК
	 * @return bool
	 */
	public function connectVKUpload() {
		$this->data = VKUpload::connect($_POST['token']);

		return true;
	}
	/**
	 * (Интеграция VKontakte) получение ID товаров для выгрузки
	 * @return bool
	 */
	public function getNumVKUpload() {
		$this->data = VKUpload::getNum($_POST['shopCats'], $_POST['inactiveToo'], $_POST['useAdditionalCats']);

		return true;
	}
	/**
	 * (Интеграция VKontakte) выгрузка товаров
	 * @return bool
	 */
	public function uploadVKUpload() {
		$this->data = VKUpload::upload($_POST['access_token'], $_POST['vkCat'], $_POST['vkAlbum'], $_POST['useNull']);

		return true;
	}
	/**
	 * (Интеграция VKontakte) получение ID товаров для удаления
	 * @return bool
	 */
	public function getNumVKUploadDelete() {
		$this->data = VKUpload::getNumDelete($_POST['shopCats'], $_POST['useAdditionalCats']);

		return true;
	}
	/**
	 * (Интеграция VKontakte) удаление товаров
	 * @return bool
	 */
	public function deleteVKUpload() {
		$this->data = VKUpload::delete($_POST['access_token']);

		return true;
	}
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/**
	 * (Интеграция Yandex.Market) создание новой выгрузки
	 * @return bool
	 */
	public function newTabYandexMarket() {
		$this->data = YandexMarket::newTab($_POST['name']);
		return true;
	}
	/**
	 * (Интеграция Yandex.Market) сохранение настроек выгрузки
	 * @return bool
	 */
	public function saveTabYandexMarket() {
		return YandexMarket::saveTab($_POST['name'], $_POST['data']);
	}
	/**
	 * (Интеграция Yandex.Market) удаление существующей выгрузки
	 * @return bool
	 */
	public function deleteTabYandexMarket() {
		return YandexMarket::deleteTab($_POST['name']);
	}
	/**
	 * (Интеграция Yandex.Market) загрузка существующей выгрузки
	 * @return bool
	 */
	public function getTabYandexMarket() {
		$this->data = YandexMarket::getTab($_POST['name']);
		return true;
	}
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/**
	 * (Интеграция Google merchant) создание новой выгрузки
	 * @return bool
	 */
	public function newTabGoogleMerchant() {
		$this->data = GoogleMerchant::newTab($_POST['name']);
		return true;
	}
	/**
	 * (Интеграция Google merchant) сохранение настроек выгрузки
	 * @return bool
	 */
	public function saveTabGoogleMerchant() {
		return GoogleMerchant::saveTab($_POST['name'], $_POST['data']);
	}
	/**
	 * (Интеграция Google merchant) удаление существующей выгрузки
	 * @return bool
	 */
	public function deleteTabGoogleMerchant() {
		return GoogleMerchant::deleteTab($_POST['name']);
	}
	/**
	 * (Интеграция Google merchant) загрузка существующей выгрузки
	 * @return bool
	 */
	public function getTabGoogleMerchant() {
		$this->data = GoogleMerchant::getTab($_POST['name']);
		return true;
	}
	/**
	 * (Интеграция Google merchant) построение иерархии категорий google
	 * @return bool
	 */
	public function buildSelectsGoogleMerchant() {
		$this->data = GoogleMerchant::buildSelects($_POST['id']);
		return true;
	}
	/**
	 * (Интеграция Google merchant) сохранение категории google
	 * @return bool
	 */
	public function saveCatGoogleMerchant() {
		return GoogleMerchant::saveCat($_POST['shopId'], $_POST['googleId'], $_POST['name']);
	}
	/**
	 * (Интеграция Google merchant) получение категорий google
	 * @return bool
	 */
	public function getCatsGoogleMerchant() {
		$this->data = GoogleMerchant::getCats($_POST['name']);
		return true;
	}
	/**
	 * (Интеграция Google merchant) получение названия категории google по ID
	 * @return bool
	 */
	public function getCatNameGoogleMerchant() {
		$this->data = GoogleMerchant::getCatName($_POST['id']);
		return true;
	}
	/**
	 * (Интеграция Google merchant) рекурсивное применение категорий google
	 * @return bool
	 */
	public function updateCatsRecursGoogleMerchant() {
		return GoogleMerchant::updateCatsRecurs($_POST['shopId'], $_POST['googleId'], $_POST['name']);
	}
	/**
	 * (Интеграция Google merchant) удаление повторов категорий google
	 * @return bool
	 */
	public function clearTrashGoogleMerchant() {
		return GoogleMerchant::clearTrash($_POST['name']);
	}
	/**
	 * (Интеграция Google merchant) создание базы категорий google
	 * @return bool
	 */
	public function updateDBGoogleMerchant() {
		return GoogleMerchant::updateDB();
	}
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/**
	 * (Интеграция MailChimp) массовая выгрузка
	 * @return bool
	 */
	public function uploadAllMailChimp() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageSucces = $this->lang['EXPORT_START'];
		$this->messageError = $this->lang['ERROR_CHECH_SETTING'];
		return MailChimp::uploadAll($_POST['API'], $_POST['listId'], $_POST['perm']);
	}
	/**
	 * (Интеграция MailChimp) сохранение настроек
	 * @return bool
	 */
	public function saveMailChimp() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		return MailChimp::saveOptions($_POST['API'], $_POST['listId'], $_POST['perm'], $_POST['uploadNew']);
	}
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/**
	 * (Интеграция RetailCRM) сохранение настроек
	 * @return bool
	 */
	public function saveRetailCRM() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		return RetailCRM::saveOptions($_POST['url'], $_POST['API'], $_POST['site'], $_POST['warehouseCode'], $_POST['paid'], $_POST['notPaid'], $_POST['syncUsers'], $_POST['syncOrders'], $_POST['syncRemains'], $_POST['retailStorage'], $_POST['retailOpFields'], $_POST['retailStatuses'], $_POST['retailDeliverys'], $_POST['retailPayments'], $_POST['retailIndividual'], $_POST['retailLegal'], $_POST['reportSync'], $_POST['useOrderNumber']);
	}
	/**
	 * (Интеграция RetailCRM) стартовая выгрузка
	 * @return bool
	 */
	public function uploadAllRetailCRM() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageSucces = 'Выгрузка завершена успешно';
		$this->messageError = 'Произошла ошибка (подробнее в логе)';
		return RetailCRM::uploadAll($_POST['uploadUsers'], $_POST['uploadOrders']);
	}
	/**
	 * (Интеграция RetailCRM) синхронизация
	 * @return bool
	 */
	public function syncRetailCRM() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageSucces = 'Синхронизация завершена успешно';
		$this->messageError = 'Произошла ошибка синхронизации (подробнее в логе)';
		return RetailCRM::syncAll();
	}
	//=========================================================
	// ИНТЕГРАЦИИ КОНЕЦ
	//=========================================================

	/**
	 * Сохранение столбцов в каталоге.
	 * @return bool
	 */
	public function saveColsCatalog() {
		if(USER::access('product') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$tmp = array('number' => $_POST['catalogNumber'],
							 'category' => $_POST['catalogCategory'],
							 'img' => $_POST['catalogImg'],
							 'price' => $_POST['catalogPrice'],
							 'code' => $_POST['catalogCode'],
							 'order' => $_POST['catalogOrder'],
							 'count' => $_POST['catalogCount']);

		$tmp = addslashes(serialize($tmp));

		MG::setOption(array('option' => 'catalogColumns', 'value'  => $tmp, 'active' => 'Y'));

		return true;
	}

	/**
	 * Сохранение столбцов в заказах.
	 * @return bool
	 */
	public function saveColsOrder() {
		if(USER::access('order') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$tmp = array('id' => $_POST['orderId'],
					 'date' => $_POST['orderDate'],
					 'fio' => $_POST['orderFio'],
					 'email' => $_POST['orderEmail'],
					 'phone' => $_POST['orderPhone'],
					 'yur' => $_POST['orderYur'],
					 'summ' => $_POST['orderSumm'],
					 'deliv' => $_POST['orderDeliv'],
					 'delivDate' => $_POST['orderDelivDate'],
					 'address' => $_POST['orderDelivAddress'],
					 'payment' => $_POST['orderPayment'],
					 'additional' => $_POST['additional'],
					 'commUzer' => $_POST['commUzer'],
					 'commManager' => $_POST['commManager'],
					 'status' => $_POST['orderStatus'],
					 'owner' => $_POST['owner']);

		$tmp = addslashes(serialize($tmp));

		MG::setOption(array('option' => 'orderColumns', 'value'  => $tmp, 'active' => 'Y'));

		return true;
	}

	/**
	 * Сохранение столбцов в пользователях.
	 * @return bool
	 */
	public function saveColsUser() {
		if(USER::access('user') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$tmp = array('email' => $_POST['userEmail'],
					 'phone' => $_POST['userPhone'],
					 'fio' => $_POST['userFio'],
					 'birthday' => $_POST['userBirthday'],
					 'adress' => $_POST['userAdress'],
					 'yur' => $_POST['userYur'],
					 'status' => $_POST['userStatus'],
					 'group' => $_POST['userGroup'],
					 'register' => $_POST['userRegister'],
					 'personal' => $_POST['userPersonal'],
					 'owner' => $_POST['owner']);

		$tmp = addslashes(serialize($tmp));

		MG::setOption(array('option' => 'userColumns', 'value'  => $tmp, 'active' => 'Y'));

		return true;
	}

	/**
	 * Возвращает уведомления движка.
	 * @return bool
	 */
	public function getEngineMessages() {
		$messages = array();
		$res = DB::query("SELECT `id`, `name`, `text`, `group` FROM `".PREFIX."messages`");
		while ($row = DB::fetchAssoc($res)) {
			MG::loadLocaleData($row['id'], $locale, 'messages', $row);
			$messages[$row['group']][$row['name']] = array('id' => $row['id'], 'text' => $row['text'], 'title' => $this->lang[$row['name']], 'tip' => $this->lang['DESC_'.$row['name']]);
		}

		$messages2 = array();

		$messages2['order'] = $messages['order']; 
		$messages2['product'] = $messages['product']; 
		$messages2['register'] = $messages['register']; 
		$messages2['feedback'] = $messages['feedback']; 
		$messages2['status'] = $messages['status']; 

		$this->data = $messages2;
		return true;
	}

	/**
	 * Сброс уведомлений.
	 * @return bool
	 */
	public function resetMsgs() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$messages = array();
		$res = DB::query("SELECT `name`, `text_original` FROM `".PREFIX."messages` where `group` = ".DB::quote($_POST['type']));
		while ($row = DB::fetchAssoc($res)) {
			$messages[$row['name']] = $row['text_original'];
		}

		$this->data = $messages;
		return true;
	}

	/**
	 * Сохраняет уведомления движка.
	 * @return bool
	 */
	public function saveMsgs() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
			foreach ($_POST['fields'] as $value) {
				DB::query("UPDATE `".PREFIX."messages` SET `text`=".DB::quote($value['val'])." WHERE `name`=".DB::quote($value['name']));
			}
		return true;
	}

	
	/**
	 * Получает сокращеную валюту.
	 * @return bool
	 */
	public function getCurrencyShort() {
		$this->data = MG::getSetting('currencyShort');
		return true;
	}
	/**
	 * Установка валюты для администратора.
	 * @return bool
	 */
	public function setAdminCurrency() {
		if (MG::getSetting('printCurrencySelector') == 'true' && $_SESSION['userCurrency'] != $_POST['userCustomCurrency']) {
			$oldCurr = $_SESSION['userCurrency'];
			$_SESSION['userCurrency'] = $_POST['userCustomCurrency'];

			$settings = MG::get('settings');
			$result = DB::query("
				SELECT `option`, `value`
				FROM `".PREFIX."setting` 
				WHERE `option` = 'currencyRate'
				");

			while ($row = DB::fetchAssoc($result)) {
				$settings[$row['option']] = $row['value'];
			}

			$settings['currencyRate'] = unserialize(stripslashes($settings['currencyRate']));

			$settings['currencyShopIso'] = $_SESSION['userCurrency'];

			$rate = $settings['currencyRate'][$settings['currencyShopIso']];

			$settings['currencyRate'][$settings['currencyShopIso']] = 1;

			foreach ($settings['currencyRate'] as $iso => $value) {
				if ($iso != $settings['currencyShopIso']) {
					if (!empty($rate)) {
						$settings['currencyRate'][$iso] = $value / $rate;
					}
				}
			}

			$this->data = array('curr' => $settings['currencyShort'][$_SESSION['userCurrency']], 'multiplier' => $settings['currencyRate'][$oldCurr]);
			$settings['currency'] = $settings['currencyShort'][$settings['currencyShopIso']];
			MG::set('settings', $settings);
		}
		
		return true;
	}
	/**
	 * Сброс валюты для администратора.
	 * @return bool
	 */
	public function resetAdminCurrency() {
			$this->data = array('currency' => MG::getSetting('currency'), 'currencyISO' => MG::getSetting('currencyShopIso'));
		return true;
	}
	

	/**
	 * Возвращает список групп характеристик.
	 * @return bool
	 */
	public function getTablePropertyGroup() {
		$this->data = Property::getPropertyGroup();
	
		return true;
	}
	 
	/**
	 * Добавляет группу характеристик.
	 * @return bool
	 */
	public function addPropertyGroup() {
		if(USER::access('product') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}  
		$this->data = Property::addPropertyGroup($_POST['name']);
		return true;
	}
	 
	/**
	 * Удаляет группу характеристик.
	 * @return bool
	 */
	public function deletePropertyGroup() {
		if(USER::access('product') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}  
		Property::deletePropertyGroup($_POST['id']);
	
		return true;
	}   
	 
	/**
	 * Сохраняет группу характеристик.
	 * @return bool
	 */
	public function savePropertyGroup() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
			foreach ($_POST['fields'] as $value) {
				DB::query("UPDATE `".PREFIX."property_group` SET `name`=".DB::quote($value['val'])." WHERE `id`=".DB::quote($value['id']));
			}
		return true;
	}


	/**
	 * Загружает настройки способа оплаты.
	 * @return bool
	 */
	public function loadPayment() {
		$res = DB::query('SELECT * FROM '.PREFIX.'payment WHERE id = '.DB::quote($_POST['id']));
		while ($row = DB::fetchAssoc($res)) {
			$data = $row;
		}
		MG::loadLocaleData($data['id'], $_POST['lang'], 'payment', $data);
		$this->data = $data;
		return true;
	}

	/**
	 * (Резервное копирование) получение списка таблиц базы данных.
	 * @return bool
	 */
	public function startBackup() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		if (!class_exists('Backup')) {
			include URL::getDocumentRoot().'mg-admin'.DS.'lib'.DS.'backup.php';
		}
		$res = Backup::getDBtables();
		$this->data = $res;
		return true;
	}

	/**
	 * (Резервное копирование) создание заголовков таблиц базы данных.
	 * @return bool
	 */
	public function backupCreateTables() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		if (!class_exists('Backup')) {
			include URL::getDocumentRoot().'mg-admin'.DS.'lib'.DS.'backup.php';
		}
		$res = Backup::createDBtables($_POST['tables']);
		$this->data = $res;
		return true;
	}
	/**
	 * (Резервное копирование) создание дампа базы.
	 * @return bool
	 */
	public function backupTables() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		if (!class_exists('Backup')) {
			include URL::getDocumentRoot().'mg-admin'.DS.'lib'.DS.'backup.php';
		}
		$res = Backup::createDBbackup($_POST['tables'], $_POST['startingLine']);
		$this->data = $res;
		return true;
	}  
	/**
	 * (Резервное копирование) получение списка файлов для резервной копии сайта.
	 * @return bool
	 */
	public function backupGetFileList() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		if (!class_exists('Backup')) {
			include URL::getDocumentRoot().'mg-admin'.DS.'lib'.DS.'backup.php';
		}
		$res = Backup::getFileList();
		$this->data = $res;
		return true;
	}
	/**
	 * (Резервное копирование) архивация файлов.
	 * @return bool
	 */
	public function backupZipFiles() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		if (!class_exists('Backup')) {
			include URL::getDocumentRoot().'mg-admin'.DS.'lib'.DS.'backup.php';
		}
		$res = Backup::zipFiles($_POST['zipName']);
		$this->data = $res;
		return true;
	}
	/**
	 * (Резервное копирование) построение таблицы с существующими архивами.
	 * @return bool
	 */
	public function backupDrawTable() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		if (!class_exists('Backup')) {
			include URL::getDocumentRoot().'mg-admin'.DS.'lib'.DS.'backup.php';
		}
		$res = Backup::drawTable();
		$this->data = $res;
		return true;
	}
	/**
	 * (Резервное копирование) удаление существующего архива.
	 * @return bool
	 */
	public function dropBackup() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		if (!class_exists('Backup')) {
			include URL::getDocumentRoot().'mg-admin'.DS.'lib'.DS.'backup.php';
		}
		$res = Backup::dropBackup($_POST['zip']);
		$this->data = $res;
		return true;
	}
	/**
	 * (Резервное копирование) распаковка - создание списка файлов.
	 * @return bool
	 */
	public function getBackupZipArrays() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		if (!class_exists('Backup')) {
			include URL::getDocumentRoot().'mg-admin'.DS.'lib'.DS.'backup.php';
		}
		$res = Backup::getZipArrays($_POST['zip']);
		$this->data = $res;
		return true;
	}
	/**
	 * (Резервное копирование) распаковка - распаковка архива.
	 * @return bool
	 */
	public function restoreBackupFromZip() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		if (!class_exists('Backup')) {
			include URL::getDocumentRoot().'mg-admin'.DS.'lib'.DS.'backup.php';
		}
		$res = Backup::restoreFromZip($_POST['zip'],$_POST['mode']);
		$this->data = $res;
		return true;
	}
	/**
	 * (Резервное копирование) распаковка - восстановление базы данных.
	 * @return bool
	 */
	public function backupRestoreDB() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		if (!class_exists('Backup')) {
			include URL::getDocumentRoot().'mg-admin'.DS.'lib'.DS.'backup.php';
		}
		$res = Backup::restoreDBbackup($_POST['lineNum']);
		$this->data = $res;
		if ($res['remaining'] === 0 && is_file(URL::getDocumentRoot().'backups'.DS.'mysqldump.sql')) {
			unlink(URL::getDocumentRoot().'backups'.DS.'mysqldump.sql');
		}
		return true;
	}
	/**
	 * (Резервное копирование) проверка файлов в архиве.
	 * @return bool
	 */
	public function BackupCheckZip() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$lang = MG::get('lang');
		if (!class_exists('Backup')) {
			include URL::getDocumentRoot().'mg-admin'.DS.'lib'.DS.'backup.php';
		}

		$res = Backup::checkZip($_POST['zip']);

		if ($res) {
			$this->messageSucces = $lang['BACKUP_ARCHIVE_UPLOAD_CHECKED'];
			return true;
		}
		else{
			$this->messageError = $lang['BACKUP_ARCHIVE_UPLOAD_CHECK_ERROR'];
			return false;
		}
	}
	/**
	 * (Резервное копирование) загрузка нового архива.
	 * @return bool
	 */
	public function addNewBackup() {
		if(USER::access('setting') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$lang = MG::get('lang');

		if (isset($_POST) && 'POST' == $_SERVER['REQUEST_METHOD']) {

			if (isset($_FILES['backupInput'])) {
				$file_array = $_FILES['backupInput'];
				$path = URL::getDocumentRoot().'backups'.DS;
			}

			$name = explode('.', $file_array['name']);
			$ext = array_pop($name);

			if ($ext != 'zip') {
				$this->messageError = $lang['BACKUP_ARCHIVE_UPLOAD_ONLYZIP'];
				return false;
			}

			if (move_uploaded_file($file_array['tmp_name'], $path.$file_array['name'])) {
				$this->data = array('zip' => $path.$file_array['name']);
				return true;
			}
		}
		$this->messageError = $lang['BACKUP_ARCHIVE_UPLOAD_ERROR'];
		return false;
	}
	/**
	 * (Резервное копирование) вычисление размера сайта.
	 * @return bool
	 */
	public function getDumpSize() {
		if (!class_exists('Backup')) {
			include URL::getDocumentRoot().'mg-admin'.DS.'lib'.DS.'backup.php';
		}
		$res = Backup::getDumpSize();
		$this->data = $res;
		return true;
	}
	/**
	 * Копирование отсутствующего блока шаблона из ядра движка в текущий шаблон.
	 * @return bool
	 */
	public function copyTemplateFile() {
		
		$root = URL::getDocumentRoot();
		$template = MG::getOption('templateName');
		$templateDir = $root.'mg-templates'.DS.$template.DS.$_POST['type'].DS;
		$fromDir = $root.'mg-core'.DS.$_POST['type'].DS;

		if (!is_dir($root.'mg-templates'.DS.$template.DS.$_POST['type'])) {
			mkdir($root.'mg-templates'.DS.$template.DS.$_POST['type'],0755);
		}

		if (is_file($fromDir.$_POST['file'])) {
			if (copy($fromDir.$_POST['file'], $templateDir.$_POST['file'])) {
				return true;
			}
		}
		return false;
	}
	/**
	 * Подтверждение прочтения информационного сообщения.
	 * @return bool
	 */
	public function confirmNotification() {
		DB::query('UPDATE `'.PREFIX.'notification` SET `status` = 1 WHERE `id` = '.DB::quoteInt($_POST['id']));
		return true;
	}

	public function calcCountProdCat() {
		// время
		$timeHave = 10;
		$timerSave = microtime(true);
		// достаем категории
		$cats = unserialize(stripcslashes(MG::getOption('catsCacheToCalc')));
		if(!$cats) {
			$res = DB::query('SELECT id FROM '.PREFIX.'category');
			while($row = DB::fetchAssoc($res)) {
				$cats[] = $row['id'];
			}
		}
		// узнаем количество категорий общее
		$res = DB::query('SELECT COUNT(id) FROM '.PREFIX.'category');
		while($row = DB::fetchAssoc($res)) {
			$allCats = $row['COUNT(id)'];
		}
		// считаем товары
		$catalog = new Models_Catalog();
		foreach ($cats as $key => $cat) {
			$category = new Category();
			$catsChild = $category->getCategoryList($cat);
			$catsChild[] = $cat;
			$filter = '((p.cat_id IN ('.DB::quoteIN($catsChild).') or FIND_IN_SET('.DB::quoteInt($cat).',p.`inside_cat`))) AND p.activity = 1';
			$tmp = $catalog->getListByUserFilter(999999, $filter, false, true);
			// проверка количества товаров
			$tmp['catalogItems'] = MG::clearProductBlock($tmp['catalogItems']);
			$count = count($tmp['catalogItems']);
			DB::query('UPDATE '.PREFIX.'category SET countProduct = '.DB::quoteInt($count).' WHERE id = '.DB::quoteInt($cat));
			unset($cats[$key]);
			unset($catsChild);
			// время
			$timeHave -= microtime(true) - $timerSave;
			$timerSave = microtime(true);
			if($timeHave < 0) break;
		}
		MG::setOption('catsCacheToCalc', addslashes(serialize($cats)));
		$percent = ceil(($allCats - count($cats)) / ($allCats / 100));
		if($percent >= 100) {
			DB::query('DELETE FROM '.PREFIX.'setting WHERE `option` = \'catsCacheToCalc\'');
			$lang = MG::get('lang');
			$this->messageSucces = $lang['ACT_SUCCESS'];
		}
		$this->data = $percent;
		return true;
	}

	public function setKey() {
		if(strlen($_POST['key']) !== 32) {
			return false;
		}
		MG::setOption('licenceKey', $_POST['key']);
		$post = 'version='.VER.
			'&sName='.$_SERVER['SERVER_NAME'].
			'&sIP='.(($_SERVER['SERVER_ADDR'] == "::1") ? '127.0.0.1' : $_SERVER['SERVER_ADDR']).
			'&sKey='.$_POST['key'];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://updata.moguta.ru/updataserver');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
		$resp = curl_exec($ch);
		curl_close($ch);

		if (stristr($resp,'error:')!==FALSE){
			$res = explode('error:', $resp);
		} else {
			$res = array($resp, 'false');
		}
		$data = json_decode($res[0], true);

		DB::query("
			UPDATE `".PREFIX."setting`
				SET `value`=".DB::quote($res[0])."
			WHERE `option`='currentVersion'
		");

		if (($res[1]!='false')) {   
			return false;
		} else {
			$download = true;
			include CORE_LIB.'/encodeupdate.php';
			Updata::checkUpdata(true);
			return true;
		}
	}

	public function setDefaultMarginToEmptyMarginProduct() {
		if(USER::access('product') < 2) {
			$this->messageError = $this->lang['ACCESS_EDIT'];
			return false;
		}
		$this->messageSucces = $this->lang['SET_DEFAULT_MARGIN'];
		DB::query('UPDATE '.PREFIX.'product_user_property_data 
			SET margin = (SELECT margin FROM '.PREFIX.'property_data WHERE id = prop_data_id) WHERE prop_data_id != 0 AND margin = \'\'');
		return true;
	}

	public function updateDB() {
		$timeLimit = 5;
		$data = $_POST['data'];
		$time = microtime(true);
		$data['process'] = true;
		$data['step'] = $data['step']?$data['step']:1;
		$data['removeMessage'] = true;
		$data['line'] = 1;

		// =========================================================================
		// переработка простых характеристик под сложные
		if($data['step'] == 1) {
			// предварительный сброс характеристик (делаеться единоразово)
			if(MG::getSetting('updateDB') < 2) {
				DB::query('UPDATE '.PREFIX.'product_user_property_data AS pupd LEFT JOIN '.PREFIX.'property AS p ON p.id = pupd.prop_id SET prop_data_id =0 WHERE p.type = \'string\'');
				MG::setOption('updateDB', 2);
			}
			// переработка простых характеристик под сложные
			$cache = array();
			$countSql = 'SELECT COUNT(pupd.id) AS counta FROM '.PREFIX.'product_user_property_data AS pupd
				LEFT JOIN '.PREFIX.'property AS p ON p.id = pupd.prop_id
				WHERE p.type = \'string\' AND pupd.prop_data_id = 0';
			$res = DB::query($countSql);
			if($row = DB::fetchAssoc($res)) {
				$allCount = $row['counta'];
				if(!$data['allCount']) $data['allCount'] = $allCount;
			}
			if($allCount > 0) {
				$res = DB::query('SELECT MAX(id) FROM '.PREFIX.'property_data');
				if($row = DB::fetchAssoc($res)) {
					$sort = $row['MAX(id)'];
				}

				/*
				
				// адаптизатор на спидах
				$res = DB::query('SELECT DISTINCT pupd.name AS pupd.name, pupd.prop_id FROM '.PREFIX.'product_user_property_data AS pupd
				 LEFT JOIN '.PREFIX.'property AS p ON p.id = pupd.prop_id 
				 WHERE p.type = \'string\' AND pupd.prop_data_id = 0');
				while($row = DB::fetchAssoc($res)) {
					$names[$row['prop_id'].'/'.$row['name']]['name'] = $row['name'];
					$names[$row['prop_id'].'/'.$row['name']]['prop_id'] = $row['prop_id'];
				}

				foreach($names as $value) {
					if(!$cache[$value['name'].'/'.$value['prop_id']]) {
						$resIn = DB::query('SELECT id FROM '.PREFIX.'property_data WHERE name = '.DB::quote($value['name']).'
							AND prop_id = '.DB::quoteInt($value['prop_id']));
						if(!$idC = DB::fetchAssoc($resIn)) {
							DB::query('INSERT INTO '.PREFIX.'property_data (prop_id, name, sort) VALUES 
								('.DB::quoteInt($value['prop_id']).', '.DB::quote($value['name']).', '.DB::quoteInt(++$sort).')');
							$cache[$value['name'].'/'.$value['prop_id']] = DB::insertId();
						} else {
							$cache[$value['name'].'/'.$value['prop_id']] = $idC['id'];
						}
					}

					DB::query('UPDATE '.PREFIX.'product_user_property_data SET active = 1, prop_data_id = '.DB::quoteInt($cache[$value['name'].'/'.$value['prop_id']]).'
						WHERE prop_data_id = 0 AND `name` = '.DB::quote($value['name']).' AND prop_id = '.DB::quoteInt($value['prop_id']));
					// mg::loger($value.'/'.$propId);
					// останавливаемся, если мало времени
					if(microtime(true) - $time > $timeLimit) {
						// mg::loger('exit');
						$res = DB::query($countSql);
						if($row = DB::fetchAssoc($res)) {
							$last = $row['counta'];
						}
						$data['message'] = $this->lang['UPDATE_STRING_PROP_BD'].' ('.number_format($data['allCount'] - $last).' / '.number_format($data['allCount']).')';
						$data['removeMessage'] = false;
						$this->data = $data;
						return true;
					}
				}*/

				// адаптизатор тормоз
				for($count = 0; $count < $allCount; $count+= 49) {
					if(microtime(true) - $time > $timeLimit) {
						$res = DB::query($countSql);
						if($row = DB::fetchAssoc($res)) {
							$last = $row['counta'];
						}
						$data['message'] = $this->lang['UPDATE_STRING_PROP_BD'].' ('.number_format($data['allCount'] - $last).' / '.number_format($data['allCount']).')';
						$data['removeMessage'] = false;
						$this->data = $data;
						return true;
					}
					$res = DB::query('SELECT pupd.* FROM '.PREFIX.'product_user_property_data AS pupd
						LEFT JOIN '.PREFIX.'property AS p ON p.id = pupd.prop_id
						WHERE p.type = \'string\' AND pupd.prop_data_id = 0 ORDER BY pupd.name ASC LIMIT 50');
					while($row = DB::fetchAssoc($res)) {
						if(!$cache[$row['name'].'/'.$row['prop_id']]) {
							$resIn = DB::query('SELECT id FROM '.PREFIX.'property_data WHERE name = '.DB::quote($row['name']).'
								AND prop_id = '.DB::quoteInt($row['prop_id']));
							if(!$idC = DB::fetchAssoc($resIn)) {
								DB::query('INSERT INTO '.PREFIX.'property_data (prop_id, name, sort) VALUES 
									('.DB::quoteInt($row['prop_id']).', '.DB::quote($row['name']).', '.DB::quoteInt(++$sort).')');
								$cache[$row['name'].'/'.$row['prop_id']] = DB::insertId();
							} else {
								$cache[$row['name'].'/'.$row['prop_id']] = $idC['id'];
							}
						}
						$row['prop_data_id'] = $cache[$row['name'].'/'.$row['prop_id']];
						$row['active'] = 1;
						DB::query('DELETE FROM '.PREFIX.'product_user_property_data WHERE id = '.DB::quote($row['id']));
						DB::query('INSERT INTO '.PREFIX.'product_user_property_data SET '.DB::buildPartQuery($row));
					}
				}
				unset($data['allCount']);
				Storage::clear();
			} 
			// для того, чтобы следующие операции уже по новому прогону пошли
			unset($data['allCount']);
			$data['removeMessage'] = true;
			$data['step'] = 2;
			$this->data = $data;
			return true;
		}
		// =========================================================================
		// 
		// =========================================================================
		// восстановление и оптимизация хранения данных характеристик
		if($data['step'] == 2) {
			// активируем все строковые и текстовые характеристики
			$res = DB::query('SELECT id FROM '.PREFIX.'property WHERE type IN (\'string\', \'textarea\')');
			while($row = DB::fetchAssoc($res)) {
				$propIds[] = $row['id'];
			}
			DB::query('UPDATE '.PREFIX.'product_user_property_data SET active = 1 WHERE prop_id IN ('.DB::quoteIN($propIds).')');
			unset($propIds);
			// удаляем все лишние характеристики
			DB::query('DELETE FROM '.PREFIX.'product_user_property_data WHERE active = 0');
			DB::query('DELETE FROM '.PREFIX.'product_user_property_data WHERE prop_id = 0');
			DB::query('DELETE FROM '.PREFIX.'product_user_property_data WHERE product_id = 0');
			DB::query('DELETE FROM '.PREFIX.'property_data WHERE name = \'\'');
			// удаление текстаера из объединений
			$res = DB::query('SELECT id FROM '.PREFIX.'property WHERE type = \'textarea\'');
			while($row = DB::fetchAssoc($res)) {
				$propIds[] = $row['id'];
			}
			DB::query('DELETE FROM '.PREFIX.'property_data WHERE prop_id IN ('.DB::quoteIN($propIds).')');
			unset($propIds);
			// удаление устаревших строковых из объединений
			$res = DB::query('SELECT prop_data_id FROM '.PREFIX.'product_user_property_data AS pupd
				LEFT JOIN '.PREFIX.'property AS p ON p.id = pupd.prop_id WHERE p.type = \'string\' GROUP BY pupd.prop_data_id');
			while($row = DB::fetchAssoc($res)) {
				$propDataIds[] = $row['prop_data_id'];
			}
			$res = DB::query('SELECT id FROM '.PREFIX.'property WHERE type = \'string\'');
			while($row = DB::fetchAssoc($res)) {
				$propIds[] = $row['id'];
			}
			DB::query('DELETE FROM '.PREFIX.'property_data WHERE id NOT IN ('.DB::quoteIN($propDataIds).') AND prop_id IN ('.DB::quoteIN($propIds).')');
			unset($propDataIds);
			unset($propIds);
			// для того, чтобы следующие операции уже по новому прогону пошли
			$data['step'] = 3;
			$this->data = $data;
			return true;
		}
		// =========================================================================
		// 
		// =========================================================================
		// поиск мусорных характеристик (ВСЕГДА ДОЛЖНО БЫТЬ ПОСЛЕДНИМ!)
		if($data['step'] == 3) {
			$res = DB::query('SELECT * FROM '.PREFIX.'category_user_property');
			while($row = DB::fetchAssoc($res)) {
				$catsAndProp[$row['property_id']][] = $row['category_id'];
			}
			$count = 0;
			foreach ($catsAndProp as $prop => $cat) {
				$res = DB::query('SELECT COUNT(pupd.id) AS toDel FROM '.PREFIX.'product_user_property_data AS pupd
					LEFT JOIN '.PREFIX.'product AS p ON p.id = pupd.product_id
					WHERE pupd.prop_id = '.DB::quoteInt($prop).' AND p.cat_id NOT IN ('.DB::quoteIN($cat).')');
				if($row = DB::fetchAssoc($res)) {
					$count += $row['toDel'];
				}
			}
			// отправляем инфу клиенту
			if($count > 0) {
				$data['message'] = 'В базе найдено <b>'.$count.'</b> неиспользуемых значений характеристик! (Отвязанные характеристики)<br> Их удаление ускорит и стабилизирует работу сайта и фильтрации по товарам. <b>Удалить?</b> 
					(<a href="javascript:void(0);" class="link deleteUnBindProp">Удалить</a> / 
					<a href="javascript:void(0);" class="link cancelUnBindProp">Отменить</a>)';
				$data['removeMessage'] = false;
				$data['line'] = 2;
			}
			$data['process'] = false;
			$this->data = $data;
			return true;
		}

		return true;
	}

	public function deleteUnBindProp() {
		$res = DB::query('SELECT * FROM '.PREFIX.'category_user_property');
		while($row = DB::fetchAssoc($res)) {
			$catsAndProp[$row['property_id']][] = $row['category_id'];
		}
		$count = 0;
		foreach ($catsAndProp as $prop => $cat) {
			$ids = NULL;
			$res = DB::query('SELECT pupd.id AS toDel FROM '.PREFIX.'product_user_property_data AS pupd
				LEFT JOIN '.PREFIX.'product AS p ON p.id = pupd.product_id
				WHERE pupd.prop_id = '.DB::quoteInt($prop).' AND p.cat_id NOT IN ('.DB::quoteIN($cat).')');
			while($row = DB::fetchAssoc($res)) {
				$ids[] = $row['toDel'];
			}
			if($ids) {
				DB::query('DELETE FROM '.PREFIX.'product_user_property_data WHERE id IN ('.DB::quoteIN($ids).')');
			}
		}
		return true;
	}

	public function getUserOrderContent() {
		$res = DB::query('SELECT email FROM '.PREFIX.'user WHERE id = '.DB::quoteInt($_POST['id']));
		if($row = DB::fetchAssoc($res)) {
			$userEmail = $row['email'];
		}
		$data = USER::getUserOrderContent($userEmail);
		$tmp = $data['products'];
		unset($data['products']);
		foreach ($tmp as $prod) {
			$prod['price'] = MG::numberFormat(round($prod['price'], 2)).' '.MG::getSetting('currency');
			$data['products'][] = $prod;
		}
		$data['summ'] = MG::numberFormat(round($data['summ']), 2).' '.MG::getSetting('currency');
		$this->data = $data;
		return true;
	}

	public function setMassiveHoly() {
		$time = 7;
		$saveTime = microtime(true);
		$data = $_POST['data'];
		$data['coof'] = str_replace('%', '', $data['coof']);
		if($data['count'] == '') $data['count'] = 1;

		if(empty($data['products'])) {
			$res = DB::query('SELECT id FROM '.PREFIX.'product');
			while($row = DB::fetchAssoc($res)) {
				$tmp[] = $row['id'];
			}
			$data['products'] = serialize($tmp);
			$data['prodCount'] = count($tmp);
			unset($tmp);
		} 

		$data['products'] = unserialize($data['products']);
		foreach ($data['products'] as $key => $productId) {
			if(microtime(true) - $saveTime > $time) {
				$data['percent'] = round(($data['prodCount'] - count($data['products'])) / ($data['prodCount'] / 100), 1);
				$data['products'] = serialize($data['products']);
				$this->data = $data;
				return true;
			}
			$res = DB::query('SELECT id, price_course FROM '.PREFIX.'product_variant WHERE product_id = '.DB::quoteInt($productId));
			$variants = NULL;
			while($row = DB::fetchAssoc($res)) {
				$variants[$row['id']] = $row['price_course'];
			}
			if(!$variants) {
				$price = NULL;
				$res = DB::query('SELECT price_course FROM '.PREFIX.'product WHERE id = '.DB::quoteInt($productId));
				if($row = DB::fetchAssoc($res)) {
					$price = $row['price_course'];
				}
				$price *= 1 - $data['coof'] / 100;
				if($price) {
					$res = DB::query('SELECT id FROM '.PREFIX.'wholesales_sys WHERE product_id = '.DB::quoteInt($productId).' 
						AND variant_id = 0 AND `group` = '.DB::quote($data['group']).' AND count = '.DB::quote($data['count']));
					if($row = DB::fetchAssoc($res)) {
						DB::query('UPDATE '.PREFIX.'wholesales_sys SET price = '.DB::quote($price).' WHERE id = '.DB::quoteInt($row['id']));
					} else {
						DB::query('INSERT INTO '.PREFIX.'wholesales_sys SET product_id = '.DB::quoteInt($productId).', variant_id = 0, price = '.DB::quote($price).',
							count = '.DB::quote($data['count']).', `group` = '.DB::quote($data['group']));
					}
				}
			} else {
				foreach ($variants as $varId => $varPrice) {
					$varPrice *= 1 - $data['coof'] / 100;
					if($varPrice) {
						$res = DB::query('SELECT id FROM '.PREFIX.'wholesales_sys WHERE product_id = '.DB::quoteInt($productId).' 
							AND variant_id = '.DB::quoteInt($varId).' AND `group` = '.DB::quote($data['group']).' AND count = '.DB::quote($data['count']));
						if($row = DB::fetchAssoc($res)) {
							DB::query('UPDATE '.PREFIX.'wholesales_sys SET price = '.DB::quote($varPrice).' WHERE id = '.DB::quoteInt($row['id']));
						} else {
							DB::query('INSERT INTO '.PREFIX.'wholesales_sys SET product_id = '.DB::quoteInt($productId).', variant_id = '.DB::quoteInt($varId).', 
								price = '.DB::quote($varPrice).', count = '.DB::quote($data['count']).', `group` = '.DB::quote($data['group']));
						}
					}
				}
			}
			unset($data['products'][$key]);
		}
		
		$this->messageSucces = $this->lang['WHOLESALES_MASSIVE_DONE'];
		$data['percent'] = 100;
		$data['products'] = serialize($data['products']);
		$this->data = $data;
		return true;
	}
	public function mpInstallPlugin() {
		return MarketplaceMain::installPlugin($_POST['code'], $_POST['trial']);
	}
	public function mpUpdateTemplate() {
		return MarketplaceMain::updateTemplate($_POST['code']);
	}
	public function resetMpCache() {
		Storage::clear('mp-cache');
		return MarketplaceMain::update();
	}
	public function mpGetDescr() {
		$data = MarketplaceMain::mpGetDescr($_POST['code']);
		if ($data) {
			$this->data = $data;
			return true;
		}
		return false;
	}
	public function mpGetResetSelect() {
		$data = MarketplaceMain::getResetSelect();
		if ($data) {
			$this->data = $data;
			return true;
		}
		return false;
	}

	public function enterInUser() {
		$res = DB::query('SELECT * FROM '.PREFIX.'user WHERE id = '.DB::quoteInt($_POST['id']));
		if($row = DB::fetchObject($res)) {
			unset($_SESSION['user']);
			$_SESSION['user'] = new stdClass();
			$_SESSION['user']->role = -1;
			// 
			$_SESSION['user'] = $row;
			$this->data = SITE.'/personal';
			return true;
		}
		return false;
	}
	public function dropTemplate() {
		$this->messageSucces = $this->lang['DROP_TEMPLATE_SUCCESS'];
		$this->messageError = $this->lang['DROP_TEMPLATE_ERROR'];
		if(USER::access('setting') < 2 || $_POST['template'] == 'moguta-standard' || $_POST['template'] == MG::getSetting('templateName')) {return false;}
		$dir = SITE_DIR.'mg-templates'.DS.$_POST['template'];
		MG::rrmdir($dir);
		if (is_dir($dir)) {
			return false;
		}
		return true;
	}
}