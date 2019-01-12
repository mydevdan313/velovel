<?php
	$siteFolder = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
	$site = (strtolower($_SERVER['HTTPS'])=='on'?'https':'http').'://'.$_SERVER['SERVER_NAME'].$siteFolder;
	echo '<input class="site" value="'.$site.'" style="display:none;">';

	define('SITE', $site);
?>
<html class="mg-admin-html auth-page">
<head>
	<title></title>

	<link href="<?php echo $site?>/mg-admin/design/css/vendors.min.css" rel="stylesheet">
	<link href="<?php echo $site?>/mg-admin/design/css/style.css?<?php echo filemtime(ADMIN_DIR.'/design/css/style.css') ?>" rel="stylesheet" type="text/css">
	<!--  -->
	<script src="<?php echo $site?>/mg-core/script/jquery-3.2.1.min.js"></script>

	<style type="text/css">
		.login-div .error, .error-key {
			text-align: center;
		    padding: 10px 0 0;
		    color: red;
		}
		.error-block {
			border: 1px solid red;
			padding: 10px;
			background: #fff3f3;
		}
	</style>
</head>
<body>

	<div class="mg-enter">
		<div class="enter-header">
            <div class="enter-logo"><img src="<?php echo $site ?>/mg-admin/design/images/logo-black.svg"></div>
        </div>
        <!-- логин -->
		<div class="enter-body login-div">
			<div class="text-center">
				<!-- На сервере была изменена версия PHP. Движок не может функцционировать с текущими файлами. -->
				На вашем веб-сервере была изменена версия PHP с <?php echo file_get_contents(CORE_DIR.'lastPhpVersion.txt') ?> на <?php echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION ?><br>Действующие файлы Moguta.CMS предназначены только для версии PHP <?php echo file_get_contents(CORE_DIR.'lastPhpVersion.txt') ?>
				<br><br>
				Чтобы восстановить работу сайта, авторизуйтесь как администратор.
			</div>
			<div class="error" style="display:none;">Неправильный Email или пароль</div>
			<ul class="login-list">
	            <li><input type="text" placeholder="Email" name="email" value="" class="login-input"></li>
	            <li><input type="password" placeholder="Пароль" name="pass" value="" class="pass-input"></li>
	        </ul>
	        <button type="submit" class="enter-button login-btn">Войти</button>
		</div>
		<!-- проверка ключа -->
		<div class="enter-body key-div" style="display:none;">
			<div class="key-info text-center"></div>
			<div class="error-key" style="display:none;">Неправильный Email или пароль</div>
			<input type="text" class="key" placeholder="Лицензионный ключ" style="display:none;">
			<button type="submit" class="enter-button enter-key-btn" style="display:none;">Изменить ключ</button>
			<button type="submit" class="enter-button update-btn" style="display:none;">Обновить сайт</button>
		</div>
		<!-- подтверждение обновления -->
	</div>


	<script type="text/javascript">
		$('body').on('click', '.login-btn', function() {
			login();
		});

		$('[name=email], [name=pass]').keypress(function (e) {
		  	if(e.which == 13) {
		    	login();
		  	}
		});

		$('body').on('click', '.enter-key-btn', function() {
			checkKey($('.key').val());
		});

		$('.key').keypress(function (e) {
		  	if(e.which == 13) {
		    	checkKey($('.key').val());
		  	}
		});

		$('body').on('click', '.update-btn', function() {
			$.ajax({
			    type: 'POST',
			    url: $('.site').val()+'/',
			    dataType: 'json',
			    data: {
			    	update: 'encodeupdate',
			    },
			    success: function (response) {
			        if(response == true) {
			        	location.reload();
			        } else {
			        	$('.error-key').show();
			        	$('.error-key').html(response);
			        }
			    }
			});
		});

		function login() {
			$.ajax({
			    type: 'POST',
			    url: $('.site').val()+'/',
			    dataType: 'json',
			    data: {
			    	login: 'encodeupdate',
			    	l: $('[name=email]').val(),
			    	p: $('[name=pass]').val(),
			    },
			    success: function (response) {
			        if(response) {
			        	$('.login-div').hide();
			        	$('.key-div').show();
			        	checkKey();
			        } else {
			        	$('.login-div .error').show();
			        }
			    }
			});
		}

		function checkKey(key = '') {
			$.ajax({
			    type: 'POST',
			    url: $('.site').val()+'/',
			    dataType: 'json',
			    data: {
			    	checkKey: 'encodeupdate',
			    	key: key,
			    },
			    success: function (response) {
			    	$('.key, .enter-key-btn, .update-btn').show();
			        $('.key').val(response.key);
			        if(response.invalid) {
			        	$('.key-info').html('Данный ключ недействительный. Для восстановления работы сайта введите корректный ключ, либо установите на вашем веб-сервере версию PHP '+response.lastPHP+'<br><br>');
			        	$('.update-btn').hide();
			        } else {
			        	if(response.error) {
			        		$('.key-info').html(response.message+'<br><br>Для восстановления работы сайта введите корректный ключ, либо установите на вашем веб-сервере версию PHP '+response.lastPHP+'<br><br>');
			        		$('.update-btn').hide();
			        	} else {
			        		if(response.days > 0) {
                                $('.key-info').html('Чтобы восстановить работу сайта, необходимо установтить на вашем веб-сервере версию PHP ' + response.lastPHP + '<br>Так же вы можете обновить CMS до актуальной версии.<br><br>');
			        			$('.key, .enter-key-btn').hide();
			        		} else {
                                $('.key-info').html('<b>Чтобы восстановить работу сайта, необходимо установтить на вашем веб-сервере версию PHP ' + response.lastPHP + '</b><br>Так же вы можете обновить CMS до актуальной версии.<br><br><div class="error-block">Срок действия ключа истек, обновление недоступно.</div>');
			        			$('.key, .enter-key-btn, .update-btn').hide();
			        		}
			        		
			        	}
			        }
			    }
			});
		}
	</script>
</body>
</html>

<!--  -->
	
<!--  -->

