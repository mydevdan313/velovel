<?php

/*
  Plugin Name: Плагин адаптизации структуры характеристик
  Description: Данный плагин позволит владельцам магазинов адаптизировать характеристики под новую структуру, при этом сохранив текущие данные о характеристиках (структура хранения и обработки данных была изменена, для ускорения работы с ними и увеличения возможного функционала)
  Author: Гайдис Михаил
  Version: 1.0.1
 */

new adaptizator;

class adaptizator {

	public function __construct() {

		mgActivateThisPlugin(__FILE__, array(__CLASS__, 'activate')); //Инициализация  метода выполняющегося при активации  
		mgAddAction(__FILE__, array(__CLASS__, 'pageSettingsPlugin')); //Инициализация  метода выполняющегося при нажатии на кнопку настроект плагина  
	  
	}

	public static function activate() {
	  	// создаем таблицы
  		DB::query(
  			'CREATE TABLE IF NOT EXISTS `'.PREFIX.'product_user_property_data` (
  			  `id` int(11) NOT NULL AUTO_INCREMENT,
  			  `prop_id` int(11) NOT NULL,
  			  `prop_data_id` int(11) NOT NULL,
  			  `product_id` int(11) NOT NULL,
  			  `name` text CHARACTER SET utf8 NOT NULL,
  			  `margin` text CHARACTER SET utf8 NOT NULL,
  			  `type_view` text CHARACTER SET utf8 NOT NULL,
  			  `active` tinyint(1) NOT NULL DEFAULT "1",
  			  PRIMARY KEY (`id`)
  			) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8;');
  		// создаем индексы для product_user_property_data
  		DB::createIndexIfNotExist('product_user_property_data', 'id');
  		DB::createIndexIfNotExist('product_user_property_data', 'prop_id');
  		DB::createIndexIfNotExist('product_user_property_data', 'product_id');

  		DB::query(
  			'CREATE TABLE IF NOT EXISTS `'.PREFIX.'property_data` (
  			  `id` int(11) NOT NULL AUTO_INCREMENT,
  			  `prop_id` int(11) NOT NULL,
  			  `name` varchar(1024) CHARACTER SET utf8 NOT NULL,
  			  `margin` text CHARACTER SET utf8 NOT NULL,
  			  `sort` int(11) NOT NULL DEFAULT "1",
  			  `color` varchar(45) NOT NULL,
  			  PRIMARY KEY (`id`)
  			) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;');
  		// создаем индексы для property_data
  		DB::createIndexIfNotExist('property_data', 'id');
  		DB::createIndexIfNotExist('property_data', 'name');
  		DB::createIndexIfNotExist('property_data', 'prop_id');

  		// добавляем столбец в таблицу, если его нет
  		$dbRes = DB::query("SHOW COLUMNS FROM `".PREFIX."product_user_property` LIKE 'id'");
  		if(!$row = DB::fetchArray($dbRes)) {
  		  DB::query("ALTER TABLE `".PREFIX."product_user_property` ADD `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY");
  		}
	}

	public static function pageSettingsPlugin() {
		include('pagepl.php');
	}

	public static function adaptization($param) {
		if(empty($param['stage'])) $param['stage'] = 'propData';
		if(empty($param['row'])) $param['row'] = 0;
		$timeHave = 15;
		$timerSave = microtime(true);

		// считаем количество записей для каждой из таблиц, чтобы в прогрес баре отображать
		$res = DB::query('SELECT count(id) FROM '.PREFIX.'property');
		while($row = DB::fetchAssoc($res)) {
			$param['countProperty'] = $row['count(id)'];
		}
		$res = DB::query('SELECT count(id) FROM '.PREFIX.'product_user_property'); // GROUP BY product_id, property_id
		while($row = DB::fetchAssoc($res)) {
			$param['countPropertyProduct'] = $row['count(id)'];
		}
		// $param['countPropertyProduct'] = DB::numRows($res);

		if($param['stage'] == 'propData') {
			// переносим данные о характеристиках в новую таблицу
			$dbRes = DB::query("SELECT * FROM ".PREFIX."property LIMIT ".DB::quote($param['row'], true).", ".DB::quote($param['countProperty'], true));
			while($row = DB::fetchArray($dbRes)) {
				$data = explode('|', $row['data']);
				foreach ($data as $item) {
					$dataToDb = explode('#', $item);
					if(!empty($dataToDb[0])) {
						// проверка наличия записи
						$checkRow = DB::query('SELECT id FROM '.PREFIX.'property_data WHERE
							prop_id = '.DB::quote($row['id']).' AND name = '.DB::quote($dataToDb[0]).' AND margin = '.DB::quote($dataToDb[1]));
						// если нет записи, то вставляем
						if(!$rowC = DB::fetchArray($checkRow)) {
							$sortRes = DB::query('SELECT MAX(id) FROM '.PREFIX.'property_data');
			  				while($sortRow = DB::fetchAssoc($sortRes)) {
		  						$sort = $sortRow['id'];
		  					}
			  				$sort++;
							DB::query('INSERT INTO '.PREFIX.'property_data (prop_id, name, margin, sort) VALUES
							('.DB::quote($row['id']).', '.DB::quote($dataToDb[0]).', '.DB::quote($dataToDb[1]).', '.DB::quote($sort).')');
						}
					}
				}
				$param['row']++;
				$timeHave -= microtime(true) - $timerSave;
				$timerSave = microtime(true);
				if($timeHave < 0) {
					return $param;
				}
			}
			// переходим на другую таблицу
			$param['stage'] = 'product';
			$param['row'] = 0;
		}

		// переносим данные о товарах в характеристиках в новую таблицу
		while($param['row'] < $param['countPropertyProduct']) {
			$dbRes = DB::query("SELECT * FROM ".PREFIX."product_user_property 
				LIMIT ".DB::quote($param['row'], true).", 100"); // DB::quote($param['countPropertyProduct']
			while($row = DB::fetchArray($dbRes)) {
				// достаем данные о характеристике
				$prop = DB::query("SELECT * FROM ".PREFIX."property WHERE id = ".DB::quote($row['property_id']));
				$propRow = DB::fetchAssoc($prop);
			  	// правим инфу для чекбоксов
			  	if($propRow['type'] == 'assortmentCheckBox') {
			  		$dataValue = explode('|', $propRow['data']);
			  	} else {
			  		$dataValue = explode('|', $row['value']);
			  	}
			  	foreach ($dataValue as $item) {
			  		if(($propRow['type'] != 'assortment')&&($propRow['type'] != 'assortmentCheckBox')) {
			  			$active = 1;
			  			$data[0] = $item;
			  		} else {
			  			$data = explode('#', $item);
			  			if($propRow['type'] == 'assortmentCheckBox') {
			  				if(substr_count('|'.$row['value'].'|', '|'.$data[0].'|') > 0) {
			  					$active = 1;
			  				} else {
			  					$active = 0;
			  				}
			  			} else {
			  				if(substr_count('|'.$row['product_margin'].'|', '|'.$data[0].'|') > 0) {
			  					$active = 1;
			  				} else {
			  					$active = 0;
			  				}
			  			}
			  		}

			  		if(in_array($propRow['type'], array('string', 'textarea'))) {
			  			$active = 1;
			  		}
			  		
			  		if(!empty($data[0])) {
			  			// проверка наличия записи
			  			if(($propRow['type'] == 'assortment')||($propRow['type'] == 'assortmentCheckBox')) {
			  				$checkRow = DB::query('SELECT id FROM '.PREFIX.'product_user_property_data WHERE prop_id = '.DB::quote($row['property_id']).' AND product_id = '.DB::quote($row['product_id']).' AND name = '.DB::quote($data[0]));
			  			} else {
			  				$checkRow = DB::query('SELECT id FROM '.PREFIX.'product_user_property_data WHERE prop_id = '.DB::quote($row['property_id']).' AND product_id = '.DB::quote($row['product_id']));
			  			}
			  			// если нет записи, то вставляем
			  			if(!$rowC = DB::fetchArray($checkRow)) {
			  				$propDataId = 0;
			  				// проверка типа характеристики для небольшого изменения вида записи
			  				if(($propRow['type'] == 'assortment')||($propRow['type'] == 'assortmentCheckBox')) {
			  					$toGetIdPropDataRow = DB::query('SELECT id FROM '.PREFIX.'property_data WHERE 
			  						prop_id = '.DB::quote($row['property_id']).' AND name = '.DB::quote($data[0]));
			  					while($dataId = DB::fetchAssoc($toGetIdPropDataRow)) {
			  						$propDataId = $dataId['id'];
			  					}
			  					// unset($data[0]);
			  				}
			  				DB::query('INSERT INTO '.PREFIX.'product_user_property_data (prop_id, prop_data_id, product_id, name, margin, type_view, active) VALUES
			  					('.DB::quote($row['property_id']).', '.DB::quote($propDataId).', '.DB::quote($row['product_id']).', 
			  					'.DB::quote($data[0]).', '.DB::quote($data[1]).', '.DB::quote($row['type_view']).', '.DB::quote($active).')');
			  			}
			  		}
			  	}

			  	$param['row']++;
			  	$timeHave -= microtime(true) - $timerSave;
			  	$timerSave = microtime(true);
			  	if($timeHave < 0) {
			  		return $param;
			  	}
			}
		}

		MG::setOption('updateDB', 0);

		return $param;
	}

}