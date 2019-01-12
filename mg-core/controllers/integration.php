<?php  
/**
 * Контроллер: Integration
 *
 * Класс Controllers_Integration обрабатывает запрос на открытие результата интеграции
 * @package moguta.cms
 * @subpackage Controller
 */
class Controllers_Integration{
	function __construct() {
		if ($_GET['int'] == 'gm' && array_key_exists('name', $_GET)) {
			header('Content-type: application/xml');
			header("Content-Type: text/xml; charset=utf-8");

			echo GoogleMerchant::constructXML($_GET['name']);
			exit();
		}

		if ($_GET['int'] == 'ym' && array_key_exists('name', $_GET)) {
			header('Content-type: application/xml');
			header("Content-Type: text/xml; charset=utf-8");

			echo YandexMarket::constructYML($_GET['name']);
			exit();
		}

		if ($_GET['int'] == 'avito' && array_key_exists('name', $_GET)) {
			header('Content-type: application/xml');
			header("Content-Type: text/xml; charset=utf-8");

			echo Avito::constructXML($_GET['name']);
			exit();
		}

		if ($_GET['int'] == 'icml') {
			header('Content-type: application/xml');
			header("Content-Type: text/xml; charset=utf-8");

			echo RetailCRM::generateICML();
			exit();
		}

		if (strtolower($_GET['int']) == 'retailsync') {//curl <ссылка> или wget -q -O <ссылка>
			RetailCRM::syncAll();
			exit();
		}
		if (strtolower($_GET['int']) == 'mailchimpupload') {
			$options = unserialize(stripslashes(MG::getSetting('mailChimp')));
			MailChimp::uploadAll($options['api'], $options['listId'], $options['perm']);
			exit();
		}
	}
}

?>