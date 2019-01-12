<?php

	// для обработки запросов в обход движка (он потенциально не работоспособен)
	if(isset($_POST['login']) && $_POST['login'] == 'encodeupdate') {
		$config = parse_ini_file('config.ini', true);
	    $connection = mysqli_connect($config['DB']['HOST'], $config['DB']['USER'], $config['DB']['PASSWORD']);
	    mysqli_select_db($connection,$config['DB']['NAME_BD']);
	    $prefix = $config['DB']['TABLE_PREFIX'];
	    // 
		$sql = 'SELECT * FROM '.$prefix.'user WHERE email = "'.mysqli_real_escape_string($connection, $_POST['l']).'" AND role = 1'; 

		$res = mysqli_query($connection, $sql);
		while ($row = mysqli_fetch_assoc($res)) {
			if($row['pass'] == crypt($_POST['p'], $row['pass'])) {
				echo json_encode(true);
				exit;
			}
		}
		echo json_encode(false);
		exit;
	}

	if(isset($_POST['checkKey']) && $_POST['checkKey'] == 'encodeupdate') {
		$config = parse_ini_file('config.ini', true);
	    $connection = mysqli_connect($config['DB']['HOST'], $config['DB']['USER'], $config['DB']['PASSWORD']);
	    mysqli_select_db($connection,$config['DB']['NAME_BD']);
	    $prefix = $config['DB']['TABLE_PREFIX'];
	    // 

	    if($_POST['key'] != '') {
	    	$sql = 'UPDATE '.$prefix.'setting SET value = "'.mysqli_real_escape_string($connection, $_POST['key']).'" WHERE `option` = \'licenceKey\'';
	    	mysqli_query($connection, $sql);
	    }
		$sql = 'SELECT `value` FROM '.$prefix.'setting WHERE `option` = \'licenceKey\'';

		$res = mysqli_query($connection, $sql);
		while ($row = mysqli_fetch_assoc($res)) {
		    $key = $row['value'];
		}
		$keyInvalid = false;
		if(strlen($key) != 32) {
			$keyInvalid = true;
		}

		$msg = '';
		if(!$keyInvalid) {
			// отправляем запрос на проверку возможности обновления
			$post = 'step=1'.
			  '&sName='.$_SERVER['SERVER_NAME'].
			  '&sIP='.(($_SERVER['SERVER_ADDR'] == "::1") ? '127.0.0.1' : $_SERVER['SERVER_ADDR']).
			  '&sKey='.$key.
			  '&php='.PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.
			  '&ver='.VER;
			// Иницализация библиотеки curl.
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $config['SETTINGS']['UPDATE_SERVER'].'/updataserver');
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
			$res = json_decode(curl_exec($ch), true);
			curl_close($ch);
			// проверка на ошибку
			if((substr_count($res['msg'], 'не зарегистрирован для ключа') == 0)&&(substr_count($res['msg'], 'Ключ не найден!') == 0)) {
				if($res['status'] == 'error') {
					$msg = $res['msg'];
				}
				$error = false;
			} else {
				$error = true;
			}

			if($res['dateActivateKey'] && ($res['dateActivateKey']!='0000-00-00 00:00:00') && $res) {
			  	$now_date = strtotime($res['dateActivateKey']);
			  	$future_date = strtotime(date("Y-m-d"));
			  	$dayActivate = (365-(ceil(($future_date - $now_date) / 86400)));
			}	
		}
		if($error) {
			$msg = $res['msg'];
		}

		$data['invalid'] = $keyInvalid;
		$data['days'] = $dayActivate;
		$data['message'] = $msg;
		$data['key'] = $key;
		$data['all'] = $res;
		$data['lastPHP'] = file_get_contents(CORE_DIR.'lastPhpVersion.txt');
		$data['error'] = $error;
		echo json_encode($data);
		exit;
	}

	if(isset($_POST['update']) && $_POST['update'] == 'encodeupdate' || @$download) {
		// считываем конфиг
		$config = parse_ini_file('config.ini', true);
		// устанавливаем соединение с базой, чтобы достать ключ
	    $connection = mysqli_connect($config['DB']['HOST'], $config['DB']['USER'], $config['DB']['PASSWORD']);
		mysqli_select_db($connection,$config['DB']['NAME_BD']);
		// достаем ключ
		$sql = 'SELECT `value` FROM '.$config['DB']['TABLE_PREFIX'].'setting WHERE `option` = \'licenceKey\'';
		$res = mysqli_query($connection,$sql) or die  ("Файлы системы не подходят для вашей версии PHP<br>Ошибка выполнения запроса:".mysqli_error($connection)."<br/>".$sql);
		while ($row = mysqli_fetch_assoc($res)) {
		    $key = $row['value'];
		}
		// 
		if($key) {
			if(strlen($key) !== 32) {
				$mes = 'Файлы системы не подходят для вашей версии PHP<br>Некорректный лицензионный ключ!';
				echo json_encode($mes);
				exit;
			}
			// отправляем запрос на проверку возможности обновления
			$post = 'step=1'.
			  '&sName='.$_SERVER['SERVER_NAME'].
			  '&sIP='.(($_SERVER['SERVER_ADDR'] == "::1") ? '127.0.0.1' : $_SERVER['SERVER_ADDR']).
			  '&sKey='.$key.
			  '&php='.PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.
			  '&ver='.VER;
			// Иницализация библиотеки curl.
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $config['SETTINGS']['UPDATE_SERVER'].'/updataserver');
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
			$res = json_decode(curl_exec($ch), true);
			curl_close($ch);
			// проверка на ошибку
			if((substr_count($res['msg'], 'не зарегистрирован для ключа') == 0)&&(substr_count($res['msg'], 'Ключ не найден!') == 0)) {
				if($res['status'] == 'error') {
					$mes = $res['msg'];
					file_put_contents(CORE_DIR.'updateError.txt', $res['msg']);
				    echo json_encode($mes);
					exit;
				}
			} else {
				$dontWork = true;
			}

			if(!$dontWork) {
				// скачиваем архив с новой кодировкой 
				$file = SITE_DIR.'/update_encode.zip';
				@unlink($file);

				if($res['msg']) {
					$ch = curl_init($config['SETTINGS']['UPDATE_SERVER'].'/updata/history/'.$res['msg'].'/update_'.PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'.zip');
					$fp = fopen($file, "w");
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
					curl_setopt($ch, CURLOPT_FILE, $fp);
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_exec($ch);
					curl_close($ch);
					fclose($fp);

					// разархивируем архив
					if(file_exists($file)) {
						$zip = new ZipArchive;
						if ($zip->open($file) === TRUE) {
						    if($zip->extractTo('./')) {
						    	// записываем новую текущую версию
						    	$phpVersion = PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;
						    	file_put_contents(CORE_DIR.'lastPhpVersion.txt', $phpVersion);
						    	// include 'modificatoryInc.php';
						    }
						    $zip->close();
						    unlink($file);
						} else {
							file_put_contents(CORE_DIR.'archiveDownloadTime.txt', time() + 60 * 5);
						    $mes = 'Файлы системы не подходят для вашей версии PHP. Была произведена попытка скачивания архива.<br>Ошибка открытия архива! Код ошибки '.$zip->open($file).'<br>Следующая попытка обновления произойдет через 5 минут';
						    echo json_encode($mes);
    						exit;
						}
					} else {
						$mes = 'Файлы системы не подходят для вашей версии PHP. Была произведена попытка скачивания архива.<br>Архив не найден! Произошла ошибка при скачивании архива обновления. Вероятно ваша версия PHP не поддерживается.<br>
							Поддерживаемые версии PHP 5.3, 5.4, 5.5, 5.6, 7.0, 7.1, 7.2<br>У вас установлена версия PHP '.PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'<br>
							Если ваша версия удовлетворяет требованиям, то обратитесь в тех. поддержку Moguta cms';
						file_put_contents(CORE_DIR.'updateError.txt', $mes);
						echo json_encode($mes);
						exit;
					}
				}				
			}
		}
		// if(!$download) {
			echo json_encode(true);
			exit;
		// }
	}

	if(file_exists(CORE_DIR.'updateError.txt')) {
		if((time() - filemtime(CORE_DIR.'updateError.txt')) > (60 * 3)) {
			unlink(CORE_DIR.'updateError.txt');
		} else {
			echo file_get_contents(CORE_DIR.'updateError.txt');
			exit;
		}
	}

	// считывваем версию ПХП какая была при прошлом запуске
	$phpVersion = PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;
	if(!file_exists('config.ini')) {
		file_put_contents(CORE_DIR.'lastPhpVersion.txt', $phpVersion);
	}
	if(file_exists(CORE_DIR.'lastPhpVersion.txt')) {
		$lastPhpVersion = file_get_contents(CORE_DIR.'lastPhpVersion.txt');
	} else {
		// записываем новую текущую версию
		file_put_contents(CORE_DIR.'lastPhpVersion.txt', $phpVersion);
		$lastPhpVersion = $phpVersion;
	}

	// на данном этапе полюбому должен быть файл, если его нет, то значит закрыты права на запись
	if(!file_exists(CORE_DIR.'lastPhpVersion.txt')) {
		echo 'Закрыты права на запись, движок не может корректно работать!';
		exit;
	}

	$timeDownload = @file_get_contents(CORE_DIR.'archiveDownloadTime.txt');

	// проверка текущей версии и прошлой
	if((($lastPhpVersion != $phpVersion) && ($timeDownload < time())) || (@$download)) {
		// считываем конфиг
		$config = parse_ini_file('config.ini', true);
	    $connection = mysqli_connect($config['DB']['HOST'], $config['DB']['USER'], $config['DB']['PASSWORD']);
		mysqli_select_db($connection,$config['DB']['NAME_BD']);
		// достаем ключ
		$sql = 'SELECT `value` FROM '.$config['DB']['TABLE_PREFIX'].'setting WHERE `option` = \'licenceKey\'';
		$res = mysqli_query($connection,$sql) or die  ("Файлы системы не подходят для вашей версии PHP<br>Ошибка выполнения запроса:".mysqli_error($connection)."<br/>".$sql);
		while ($row = mysqli_fetch_assoc($res)) {
		    $key = $row['value'];
		}
		include ADMIN_DIR.'update.php';
		exit;
	}
?>