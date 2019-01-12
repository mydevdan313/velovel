<?php
$curTimestamp = 'CURRENT_TIMESTAMP';

$mysqlVersion = mysqli_get_server_version($connection);
$arVersion = array(
  'main' => round($mysqlVersion/10000),
  'minor' => ($mysqlVersion/100)%10,
  'sub' => $mysqlVersion%100,
);

if ($arVersion['main'] == 5 && $arVersion['minor'] < 6 ||
  $arVersion['main'] == 5 && $arVersion['minor'] == 6 && $arVersion['sub'] < 5) {
  $curTimestamp = '\'0000-00-00 00:00:00\'';
}

$damp = array(
  "DROP TABLE IF EXISTS `".$prefix."category`, `".$prefix."category_user_property`, `".$prefix."delivery`, `".$prefix."delivery_payment_compare`, `".$prefix."order`, `".$prefix."page`, `".$prefix."payment`, `".$prefix."plugins`, `".$prefix."product`, `".$prefix."product_variant`, `".$prefix."property`, `".$prefix."setting`, `".$prefix."user`, `".$prefix."slider-action`, `".$prefix."site-block-editor`, `".$prefix."product_rating`, `".$prefix."trigger-guarantee`, `".$prefix."trigger-guarantee-elements`, `".$prefix."comments`, `".$prefix."brand-logo`, `".$prefix."GoogleMerchant`, `".$prefix."GoogleMerchantCats`, `".$prefix."YandexMarket`, `".$prefix."sessions`, `".$prefix."cache`, `".$prefix."avito_settings`, `".$prefix."avito_cats`, `".$prefix."avito_locations`, `".$prefix."product_user_property_data`, `".$prefix."property_data`, `".$prefix."locales`, `".$prefix."product_on_storage`, `".$prefix."landings`, `".$prefix."wholesales_sys`, `".$prefix."promo-code`, `".$prefix."property_group`, `".$prefix."custom_order_fields`, `".$prefix."product_user_property`, `".$prefix."messages`, `".$prefix."user_group`, `".$prefix."url_redirect`, `".$prefix."url_rewrite`",
  "SET names utf8",

  "CREATE TABLE IF NOT EXISTS `".$prefix."notification` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `message` longtext NOT NULL,
    `status` tinyint(1) NOT NULL DEFAULT '0',
    UNIQUE KEY `id` (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

  "CREATE TABLE IF NOT EXISTS `".$prefix."user_group` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `can_drop` tinyint(1) NOT NULL DEFAULT '1',
    `name` varchar(255) NOT NULL DEFAULT '0',
    `admin_zone` tinyint(1) NOT NULL DEFAULT '0',
    `product` tinyint(1) NOT NULL DEFAULT '0',
    `page` tinyint(1) NOT NULL DEFAULT '0',
    `category` tinyint(1) NOT NULL DEFAULT '0',
    `order` tinyint(1) DEFAULT '0',
    `user` tinyint(1) NOT NULL DEFAULT '0',
    `plugin` tinyint(1) NOT NULL DEFAULT '0',
    `setting` tinyint(1) NOT NULL DEFAULT '0',
    `wholesales` tinyint(1) NOT NULL DEFAULT '0',
    UNIQUE KEY `id` (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

  "INSERT INTO `".$prefix."user_group` (`id`, `can_drop`, `name`, `admin_zone`, `product`, `page`, `category`, `order`, `user`, `plugin`, `setting`, `wholesales`) VALUES
  (-1, 0, 'Гость (Не авторизован)', 0, 0, 0, 0, 0, 0, 0, 0, 0),
  (1, 0, 'Администратор', 1, 2, 2, 2, 2, 2, 2, 2, 1),
  (2, 0, 'Пользователь', 0, 0, 0, 0, 0, 0, 0, 0, 0),
  (3, 0, 'Менеджер', 1, 2, 0, 1, 2, 0, 2, 0, 0),
  (4, 0, 'Модератор', 1, 1, 2, 0, 0, 0, 2, 0, 0);",

  "CREATE TABLE IF NOT EXISTS `".$prefix."messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `text_original` text NOT NULL,
  `group` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

  "CREATE TABLE IF NOT EXISTS `".$prefix."product_user_property` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `product_id` int(11) NOT NULL,
    `property_id` int(11) NOT NULL,
    `value` text NOT NULL,
    `product_margin` text NOT NULL COMMENT 'наценка продукта',
    `type_view` enum('checkbox','select','radiobutton','') NOT NULL DEFAULT 'select',
    KEY `product_id` (`product_id`),
    KEY `property_id` (`property_id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Таблица пользовательских свойств продуктов'",

  "CREATE TABLE IF NOT EXISTS `".$prefix."wholesales_sys` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL,
    `variant_id` int(11) NOT NULL,
    `count` int(11) NOT NULL,
    `price` double NOT NULL DEFAULT 0,
    `group` int(11) DEFAULT 1,
    PRIMARY KEY (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

  "CREATE TABLE `".$prefix."custom_order_fields` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `field` text NOT NULL,
    `id_order` int(11) NOT NULL,
    `value` text NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;",

  "CREATE TABLE `".$prefix."product_on_storage` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `storage` varchar(255) NOT NULL,
    `product_id` int(11) NOT NULL,
    `variant_id` int(11) NOT NULL,
    `count` int(11) NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

  "CREATE TABLE IF NOT EXISTS `".$prefix."landings` (
  `id` int(11) NOT NULL,
  `template` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `templateColor` varchar(6) CHARACTER SET utf8 DEFAULT NULL,
  `ytp` longtext CHARACTER SET utf8,
  `image` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `buySwitch` varchar(6) CHARACTER SET utf8 DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

  "CREATE TABLE IF NOT EXISTS `".$prefix."googlemerchant` (
  `name` varchar(255) NOT NULL,
  `settings` longtext NOT NULL,
  `cats` longtext NOT NULL,
  `edited` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

  "CREATE TABLE IF NOT EXISTS `".$prefix."googlemerchantcats` (
  `id` int(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `parent_id` int(255) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;", 

"CREATE TABLE IF NOT EXISTS `".$prefix."vk-export` (
  `moguta_id` int(11) NOT NULL,
  `vk_id` varchar(255) NOT NULL,
  `moguta_img` varchar(255) NOT NULL,
  `vk_img` varchar(255) NOT NULL,
  PRIMARY KEY (`moguta_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

"CREATE TABLE IF NOT EXISTS `".$prefix."avito_settings` (
  `name` varchar(255) NOT NULL,
  `settings` longtext NOT NULL,
  `cats` longtext NOT NULL,
  `additional` longtext NOT NULL,
  `edited` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

"CREATE TABLE IF NOT EXISTS `".$prefix."avito_cats` (
  `id` int(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `parent_id` int(255) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

"CREATE TABLE IF NOT EXISTS `".$prefix."avito_locations` (
  `id` int(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` int(5) NOT NULL,
  `parent_id` int(255) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

"CREATE TABLE `".$prefix."locales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_ent` int(11) NOT NULL,
  `locale` varchar(255) CHARACTER SET utf8 NOT NULL,
  `table` varchar(255) CHARACTER SET utf8 NOT NULL,
  `field` varchar(255) CHARACTER SET utf8 NOT NULL,
  `text` longtext CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`),
  INDEX (`id`),
  INDEX (`id_ent`),
  INDEX (`locale`),
  INDEX (`table`),
  INDEX (`field`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;",

  "CREATE TABLE IF NOT EXISTS `".$prefix."yandexmarket` (
  `name` varchar(255) NOT NULL,
  `settings` longtext NOT NULL,
  `edited` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

  "CREATE TABLE IF NOT EXISTS `".$prefix."cache` (
  `date_add` int(11) NOT NULL,
  `lifetime` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` longtext NOT NULL,
  UNIQUE KEY `name` (`name`),
  INDEX (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

"CREATE TABLE IF NOT EXISTS `".$prefix."category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `left_key` int(11) NOT NULL DEFAULT 1,
  `right_key` int(11) NOT NULL DEFAULT 1,
  `level` int(11) NOT NULL DEFAULT 2,
  `title` varchar(255),
  `menu_title` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255),
  `parent` int(11) NOT NULL,
  `parent_url` varchar(255) NOT NULL,
  `sort` int(11),
  `html_content` longtext,
  `meta_title` varchar(255),
  `meta_keywords` varchar(512),
  `meta_desc` text,
  `invisible` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Не выводить в меню',
  `1c_id` varchar(255),
  `image_url` text,
  `menu_icon` text,
  `rate` double NOT NULL DEFAULT '0',
  `export` tinyint(1) NOT NULL DEFAULT '1',
  `seo_content` text,
  `activity` TINYINT(1) NOT NULL DEFAULT '1',
  `unit` varchar(255) NOT NULL DEFAULT 'шт.',
  `seo_alt` text,
  `seo_title` text,
  `countProduct` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `1c_id` (`1c_id`),
  KEY `url` (`url`),
  KEY `parent_url` (`parent_url`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1",


"CREATE TABLE IF NOT EXISTS `".$prefix."category_user_property` (
  `category_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8",

'CREATE TABLE IF NOT EXISTS `'.$prefix.'product_user_property_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prop_id` int(11) NOT NULL,
  `prop_data_id` int(11) NOT NULL DEFAULT "0",
  `product_id` int(11) NOT NULL,
  `name` text,
  `margin` text,
  `type_view` text CHARACTER SET utf8 NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT "1",
  PRIMARY KEY (`id`),
  INDEX (id),
  INDEX (prop_id),
  INDEX (product_id)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;',

'CREATE TABLE IF NOT EXISTS `'.$prefix.'property_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prop_id` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `margin` text CHARACTER SET utf8 NOT NULL,
  `sort` int(11) NOT NULL DEFAULT "1",
  `color` varchar(45) NOT NULL,
  `img` text NOT NULL,
  PRIMARY KEY (`id`),
  INDEX (id),
  INDEX (name),
  INDEX (prop_id)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;',

"CREATE TABLE IF NOT EXISTS `".$prefix."delivery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `cost` double,
  `description` text,
  `activity` int(1) NOT NULL DEFAULT '0',
  `free` double COMMENT 'Бесплатно от',
  `date` int(1),
  `sort` int(11),
  `ymarket` int(1),
  `plugin` varchar(255),
  `weight` longtext,
  `interval` longtext,
  `address_parts` INT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='таблица способов доставки товара' AUTO_INCREMENT=4",

"INSERT INTO `".$prefix."delivery` (`id`, `name`, `cost`, `description`, `activity`, `free`, `date`, `sort`, `ymarket`) VALUES  
    (1, 'Курьер', 700, 'Курьерская служба', 1, 0, 1, 1, 1),
    (2, 'Почта', 200, 'Почта России', 1, 0, 0, 2, 0),
    (3, 'Без доставки', 0, 'Самовывоз', 1, 0, 0, 3, 0)",

"CREATE TABLE IF NOT EXISTS `".$prefix."delivery_payment_compare` (
  `payment_id` int(10) DEFAULT NULL,
  `delivery_id` int(10) DEFAULT NULL,
  `compare` int(1) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8",

"INSERT IGNORE INTO `".$prefix."messages` (`id`, `name`, `text`, `text_original`, `group`) VALUES
  ('1', 'msg__order_denied', 'Для просмотра страницы необходимо зайти на сайт под пользователем сделавшим заказ №#NUMBER#.', 'Для просмотра страницы необходимо зайти на сайт под пользователем сделавшим заказ №#NUMBER#.', 'order'),
  ('2', 'msg__no_electro', 'Заказ не содержит электронных товаров или ожидает оплаты!', 'Заказ не содержит электронных товаров или ожидает оплаты!', 'order'),
  ('3', 'msg__electro_download', 'Скачать электронные товары для заказа №#NUMBER#.', 'Скачать электронные товары для заказа №#NUMBER#.', 'order'),
  ('4', 'msg__view_status', 'Посмотреть статус заказа Вы можете в <a href=\"#LINK#\">личном кабинете</a>.', 'Посмотреть статус заказа Вы можете в <a href=\"#LINK#\">личном кабинете</a>.', 'order'),
  ('5', 'msg__order_not_found', 'Некорректная ссылка.<br> Заказ не найден.<br>', 'Некорректная ссылка.<br> Заказ не найден.<br>', 'order'),
  ('6', 'msg__view_order', 'Следить за статусом заказа Вы можете по ссылке<br><a href=\"#LINK#\">#LINK#</a>.', 'Следить за статусом заказа Вы можете по ссылке<br><a href=\"#LINK#\">#LINK#</a>.', 'order'),
  ('7', 'msg__order_confirmed', 'Ваш заказ №#NUMBER# подтвержден и передан на обработку.<br>', 'Ваш заказ №#NUMBER# подтвержден и передан на обработку.<br>', 'order'),
  ('8', 'msg__order_processing', 'Заказ уже подтвержден и находится в работе.<br>', 'Заказ уже подтвержден и находится в работе.<br>', 'order'),
  ('9', 'msg__order_not_confirmed', 'Некорректная ссылка.<br>Заказ не подтвержден.<br>', 'Некорректная ссылка.<br>Заказ не подтвержден.<br>', 'order'),
  ('10', 'msg__email_in_use', 'Пользователь с таким email существует. Пожалуйста, <a href=\"#LINK#\">войдите в систему</a> используя свой электронный адрес и пароль!', 'Пользователь с таким email существует. Пожалуйста, <a href=\"#LINK#\">войдите в систему</a> используя свой электронный адрес и пароль!', 'order'),
  ('11', 'msg__email_incorrect', 'E-mail введен некорректно!', 'E-mail введен некорректно!', 'order'),
  ('12', 'msg__phone_incorrect', 'Введите верный номер телефона!', 'Введите верный номер телефона!', 'order'),
  ('13', 'msg__payment_incorrect', 'Выберите способ оплаты!', 'Выберите способ оплаты!', 'order'),
  ('15', 'msg__product_ended', 'Товара #PRODUCT# уже нет в наличии. Для оформления заказа его необходимо удалить из корзины.', 'Товара #PRODUCT# уже нет в наличии. Для оформления заказа его необходимо удалить из корзины.', 'product'),
  ('16', 'msg__product_ending', 'Товар #PRODUCT# доступен в количестве #COUNT# шт. Для оформления заказа измените количество в корзине.', 'Товар #PRODUCT# доступен в количестве #COUNT# шт. Для оформления заказа измените количество в корзине.', 'product'),
  ('17', 'msg__no_compare', 'Нет товаров для сравнения в этой категории.', 'Нет товаров для сравнения в этой категории.', 'product'),
  ('18', 'msg__product_nonavaiable1', 'Товара временно нет на складе!<br/><a rel=\"nofollow\" href=\"#LINK#\">Сообщить когда будет в наличии.</a>', 'Товара временно нет на складе!<br/><a rel=\"nofollow\" href=\"#LINK#\">Сообщить когда будет в наличии.</a>', 'product'),
  ('19', 'msg__product_nonavaiable2', 'Здравствуйте, меня интересует товар #PRODUCT# с артикулом #CODE#, но его нет в наличии. Сообщите, пожалуйста, о поступлении этого товара на склад. ', 'Здравствуйте, меня интересует товар #PRODUCT# с артикулом #CODE#, но его нет в наличии. Сообщите, пожалуйста, о поступлении этого товара на склад. ', 'product'),
  ('20', 'msg__enter_failed', 'Неправильная пара email-пароль! Авторизоваться не удалось.', 'Неправильная пара email-пароль! Авторизоваться не удалось.', 'register'),
  ('21', 'msg__enter_captcha_failed', 'Неправильно введен код с картинки! Авторизоваться не удалось. Количество оставшихся попыток - #COUNT#.', 'Неправильно введен код с картинки! Авторизоваться не удалось. Количество оставшихся попыток - #COUNT#.', 'register'),
  ('22', 'msg__enter_blocked', 'В целях безопасности возможность авторизации заблокирована на 15 мин. Отсчет времени от #TIME#.', 'В целях безопасности возможность авторизации заблокирована на 15 мин. Отсчет времени от #TIME#.', 'register'),
  ('23', 'msg__enter_field_missing', 'Одно из обязательных полей не заполнено!', 'Одно из обязательных полей не заполнено!', 'register'),
  ('24', 'msg__feedback_sent', 'Ваше сообщение отправлено!', 'Ваше сообщение отправлено!', 'feedback'),
  ('25', 'msg__feedback_wrong_email', 'E-mail не существует!', 'E-mail не существует!', 'feedback'),
  ('26', 'msg__feedback_no_text', 'Введите текст сообщения!', 'Введите текст сообщения!', 'feedback'),
  ('27', 'msg__captcha_incorrect', 'Текст с картинки введен неверно!', 'Текст с картинки введен неверно!', 'feedback'),
  ('28', 'msg__reg_success_email', 'Вы успешно зарегистрировались! Для активации пользователя Вам необходимо перейти по ссылке высланной на Ваш электронный адрес <strong>#EMAIL#</strong>', 'Вы успешно зарегистрировались! Для активации пользователя Вам необходимо перейти по ссылке высланной на Ваш электронный адрес <strong>#EMAIL#</strong>', 'register'),
  ('29', 'msg__reg_success', 'Вы успешно зарегистрировались! <a href=\"#LINK#\">Вход в личный кабинет</a></strong>', 'Вы успешно зарегистрировались! <a href=\"#LINK#\">Вход в личный кабинет</a></strong>', 'register'),
  ('30', 'msg__reg_activated', 'Ваша учетная запись активирована. Теперь Вы можете <a href=\"#LINK#\">войти в личный кабинет</a> используя логин и пароль заданный при регистрации.', 'Ваша учетная запись активирована. Теперь Вы можете <a href=\"#LINK#\">войти в личный кабинет</a> используя логин и пароль заданный при регистрации.', 'register'),
  ('31', 'msg__reg_wrong_link', 'Некорректная ссылка. Повторите активацию!', 'Некорректная ссылка. Повторите активацию!', 'register'),
  ('32', 'msg__reg_link', 'Для активации пользователя Вам необходимо перейти по ссылке высланной на Ваш электронный адрес #EMAIL#', 'Для активации пользователя Вам необходимо перейти по ссылке высланной на Ваш электронный адрес #EMAIL#', 'register'),
  ('33', 'msg__wrong_login', 'К сожалению, такой логин не найден. Если вы уверены, что данный логин существует, свяжитесь, пожалуйста, с нами.', 'К сожалению, такой логин не найден. Если вы уверены, что данный логин существует, свяжитесь, пожалуйста, с нами.', 'register'),
  ('34', 'msg__reg_email_in_use', 'Указанный email уже используется.', 'Указанный email уже используется.', 'register'),
  ('35', 'msg__reg_short_pass', 'Пароль менее 5 символов.', 'Пароль менее 5 символов.', 'register'),
  ('36', 'msg__reg_wrong_pass', 'Введенные пароли не совпадают.', 'Введенные пароли не совпадают.', 'register'),
  ('37', 'msg__reg_wrong_email', 'Неверно заполнено поле email', 'Неверно заполнено поле email', 'register'),
  ('38', 'msg__forgot_restore', 'Инструкция по восстановлению пароля была отправлена на <strong>#EMAIL#</strong>.', 'Инструкция по восстановлению пароля была отправлена на <strong>#EMAIL#</strong>.', 'register'),
  ('39', 'msg__forgot_wrong_link', 'Некорректная ссылка. Повторите заново запрос восстановления пароля.', 'Некорректная ссылка. Повторите заново запрос восстановления пароля.', 'register'),
  ('40', 'msg__forgot_success', 'Пароль изменен! Вы можете войти в личный кабинет по адресу <a href=\"#LINK#\">#LINK#</a>', 'Пароль изменен! Вы можете войти в личный кабинет по адресу <a href=\"#LINK#\">#LINK#</a>', 'register'),
  ('41', 'msg__pers_saved', 'Данные успешно сохранены', 'Данные успешно сохранены', 'register'),
  ('42', 'msg__pers_wrong_pass', 'Неверный пароль', 'Неверный пароль', 'register'),
  ('43', 'msg__pers_pass_changed', 'Пароль изменен', 'Пароль изменен', 'register'),
  ('44', 'msg__recaptcha_incorrect', 'reCAPTCHA не пройдена!', 'reCAPTCHA не пройдена!', 'feedback'),
  ('45', 'msg__enter_recaptcha_failed', 'reCAPTCHA не пройдена! Авторизоваться не удалось. Количество оставшихся попыток - #COUNT#.', 'reCAPTCHA не пройдена! Авторизоваться не удалось. Количество оставшихся попыток - #COUNT#.', 'register'),
  ('46', 'msg__status_not_confirmed', 'не подтвержден', 'не подтвержден', 'status'),
  ('47', 'msg__status_expects_payment', 'ожидает оплаты', 'ожидает оплаты', 'status'),
  ('48', 'msg__status_paid', 'оплачен', 'оплачен', 'status'),
  ('49', 'msg__status_in_delivery', 'в доставке', 'в доставке', 'status'),
  ('50', 'msg__status_canceled', 'отменен', 'отменен', 'status'),
  ('51', 'msg__status_executed', 'выполнен', 'выполнен', 'status'),
  ('52', 'msg__status_processing', 'в обработке', 'в обработке', 'status'),
  ('53', 'msg__payment_inn', 'Заполните ИНН', 'Заполните ИНН', 'order')
  ",


"INSERT INTO `".$prefix."delivery_payment_compare` (`payment_id`, `delivery_id`, `compare`) VALUES
(1, 1, 1),
(5, 1, 1),
(2, 2, 1),
(3, 1, 1),
(1, 2, 1),
(2, 1, 1),
(3, 2, 1),
(4, 2, 1),
(4, 3, 1),
(3, 3, 1),
(2, 3, 1),
(1, 3, 1),
(4, 1, 1),
(5, 2, 1),
(6, 1, 1),
(6, 2, 1),
(6, 3, 1),
(5, 3, 1),
(7, 1, 1),
(7, 2, 1),
(7, 3, 1),
(8, 1, 1),
(8, 2, 1),
(8, 3, 1),
(9, 1, 1),
(9, 2, 1),
(9, 3, 1),
(10, 1, 1),
(10, 2, 1),
(10, 3, 1)",

"CREATE TABLE IF NOT EXISTS `".$prefix."order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `updata_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `add_date` timestamp NOT NULL DEFAULT ".$curTimestamp.",
  `close_date` timestamp,
  `pay_date` timestamp,
  `user_email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text,
  `address_parts` text DEFAULT NULL,
  `summ` varchar(255) DEFAULT NULL COMMENT 'Общая сумма товаров в заказе ',
  `order_content` longtext,
  `delivery_id` int(11) unsigned DEFAULT NULL,
  `delivery_cost` double DEFAULT NULL COMMENT 'Стоимость доставки',
  `delivery_interval` text DEFAULT NULL,
  `delivery_options` text,
  `payment_id` int(11) DEFAULT NULL,
  `paided` int(1) NOT NULL DEFAULT '0',
  `status_id` int(11) DEFAULT NULL,
  `user_comment` text,
  `comment` text,
  `confirmation` varchar(255) DEFAULT NULL,
  `yur_info` text NOT NULL,
  `name_buyer` text NOT NULL,
  `date_delivery` text,
  `ip` text NOT NULL,
  `number` varchar(32),
  `hash` VARCHAR(32),
  `1c_last_export` TIMESTAMP,  
  `orders_set` INT( 11 ),
  `storage` text NOT NULL,
  `summ_shop_curr` double DEFAULT NULL,
  `delivery_shop_curr` double DEFAULT NULL,
  `currency_iso` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1",

"CREATE TABLE IF NOT EXISTS `".$prefix."page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_url` varchar(255) NOT NULL,
  `parent` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `html_content` longtext,
  `meta_title` varchar(255),
  `meta_keywords` varchar(1024),
  `meta_desc` text,
  `sort` int(11),
  `print_in_menu` tinyint(4) NOT NULL DEFAULT '0',
  `invisible` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Не выводить в меню',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8",

"INSERT INTO `".$prefix."page` (`id`, `parent_url`, `parent`, `title`, `url`, `html_content`, `meta_title`, `meta_keywords`, `meta_desc`, `sort`, `print_in_menu`, `invisible`) VALUES
(1, '', 0, 'Главная', 'index', '<h3 style=\"text-align: center;\">Добро пожаловать в наш интернет-магазин!</h3><div><p>Мы стабильная и надежная компания, с каждым днем наращиваем свой потенциал. Имеем огромный опыт в сфере корпоративных продаж, наши менеджеры готовы предложить Вам высокий уровень сервиса, грамотную консультацию, выгодные условия работы и широкий спектр цветовых решений. В число наших постоянных клиентов входят крупные компании.</p><p>Наши товары производятся только из самых качественных материалов!</p><p>Отдел корпоративных продаж готов предложить Вам персонального менеджера, грамотную консультацию, доставку на следующий день после оплаты, сертификаты на всю продукцию, индивидуальный метод работы.</p><p>Отдельным направлением является работа с частными лицами с оперативной доставкой, низкими ценами и высоким качеством обслуживания.</p><p>Главное для нас — своевременно удовлетворять потребности наших клиентов всеми силами и доступными нам средствами. Работая с нами, Вы гарантированно приобретаете только оригинальный товар подлинного качества.</p><p>Мы работаем по всем видам оплат. Только приобретая товар у официального дилера, Вы застрахованы от подделок. Будем рады нашему долгосрочному сотрудничеству.</p><p>** Информация представленная на сайте является демонстрационной для ознакомления с Moguta.CMS. <a data-cke-saved-href=\"https://moguta.ru/\" href=\"https://moguta.ru/\">Moguta.CMS - простая cms для интернет-магазина.</a></p></div>', 'Главная', 'Главная', '', 5, 0, 1),
(2, '', 0, 'Доставка и оплата', 'dostavka', '<div><h1 class=\"new-products-title\">Доставка и оплата</h1><p><strong>Курьером по Москве</strong></p><p>Доставка осуществляется по Москве бесплатно, если сумма заказа составляет свыше 3000 руб.  Стоимость доставки меньше чем на сумму 3000 руб. Составляет 700 руб. Данный способ доставки дает вам возможность получить товар прямо в руки, курьером по Москве. Срок доставки до 24 часов с момента заказа товара в интернет - магазине.</p><p><strong>Доставка по России</strong></p><p>Доставка по России осуществляется с помощью почтово – курьерских служб во все регионы России. Стоимость доставки зависит от региона и параметров товара. Рассчитать стоимость доставки Вы сможете на официальном сайте почтово – курьерской службы Почта-России и т.д. Сроки доставки составляет до 3-х дней с момента заказа товара в интернет – магазине.</p><h2>Способы оплаты:</h2><p><strong>Наличными: </strong>Оплатить заказ товара Вы сможете непосредственно курьеру в руки при получение товара. </p><p><strong>Наложенным платежом:</strong> Оплатить заказ товара Вы сможете наложенным платежом при получение товара на складе. С данным видом оплаты Вы оплачиваете комиссию за пересылку денежных средств. </p><p><strong>Электронными деньгами:</strong> VISA, Master Card, Yandex.Деньги, Webmoney, Qiwi и др.</p></div><div></div><div></div><div></div>', 'Доставка', 'Доставка', 'Доставка осуществляется по Москве бесплатно, если сумма заказа составляет свыше 3000 руб.  Стоимость доставки меньше чем на сумму 3000 руб. Составляет 700 руб.', 2, 1, 0),
(3, '', 0, 'Обратная связь', 'feedback', '<p>Свяжитесь с нами, посредством формы обратной связи представленной ниже. Вы можете задать любой вопрос, и после отправки сообщения наш менеджер свяжется с вами.</p>', 'Обратная связь', 'Обратная связь', 'Свяжитесь с нами, по средствам формы обратной связи представленной ниже. Вы можете задать любой вопрос, и после отправки сообщения наш менеджер свяжется с вами.', 3, 1, 0),
(4, '', 0, 'Контакты', 'contacts', '<h1 class=\"new-products-title\">Контакты</h1><p><strong>Наш адрес </strong>г. Санкт-Петербург Невский проспект, дом 3</p><p><strong>Телефон отдела продаж </strong>8 (555) 555-55-55 </p><p>Пн-Пт 9.00 - 19.00</p><p>Электронный ящик: <span style=\"line-height: 1.6em;\">info@sale.ru</span></p><p><strong>Мы в социальных сетях</strong></p><p></p><p style=\"line-height: 20.7999992370605px;\"><strong>Мы в youtoube</strong></p><p style=\"line-height: 20.7999992370605px;\"></p>', 'Контакты', 'Контакты', 'Мы в социальных сетях  Мы в youtoube ', 4, 1, 0),
(5, '', 0, 'Каталог', 'catalog', 'В каталоге нашего магазина вы найдете не только качественные и полезные вещи, но и абсолютно уникальные новинки из мира цифровой индустрии.', 'Каталог', 'Каталог', '', 1, 1, 0)
",

"CREATE TABLE IF NOT EXISTS `".$prefix."payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(1024) NOT NULL,
  `activity` int(1) NOT NULL DEFAULT '0',
  `paramArray` text DEFAULT NULL,
  `urlArray` varchar(1024) DEFAULT NULL,
  `rate` double NOT NULL DEFAULT '0',
  `sort` int(11),
  `add_security` VARCHAR(255) NOT NULL,
  `permission` VARCHAR(5) NOT NULL DEFAULT 'fiz',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8",

"INSERT INTO `".$prefix."payment` (`id`, `name`, `activity`, `paramArray`, `urlArray`, `sort`, `add_security`, `permission`) VALUES
(3, 'Наложенный платеж', 1, '{\"Примечание\":\"\"}', '', 3, '".md5($siteName)."', 'fiz'),
(4, 'Наличные (курьеру)', 1, '{\"Примечание\":\"\"}', '', 4, '".md5($siteName)."', 'fiz'),
(7, 'Оплата по реквизитам', 1, '{\"Юридическое лицо\":\"\", \"ИНН\":\"\",\"КПП\":\"\", \"Адрес\":\"\", \"Банк получателя\":\"\", \"БИК\":\"\",\"Расчетный счет\":\"\",\"Кор. счет\":\"\"}', '', 7, '".md5($siteName)."', 'yur'),
(12, 'Другой способ оплаты', 1, '{\"Примечание\":\"\"}', '', 16, '".md5($siteName)."', 'fiz'),
(13, 'Другой способ оплаты', 1, '{\"Примечание\":\"\"}', '', 17, '".md5($siteName)."', 'fiz')",



"CREATE TABLE IF NOT EXISTS `".$prefix."plugins` (
  `folderName` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL,
  UNIQUE KEY `name` (`folderName`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8",
  
"INSERT INTO `".$prefix."plugins` (`folderName`, `active`) VALUES
('breadcrumbs', 1),
('site-block-editor', 1),
('adaptizator', 1),
('brand', 1),
('comments', 1),
('slider-action', 1),
('trigger-guarantee', 1),
('rating', 1)",


"CREATE TABLE IF NOT EXISTS `".$prefix."product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sort` int(11),
  `cat_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` longtext ,
  `price` double NOT NULL,
  `url` varchar(255) NOT NULL,
  `image_url` TEXT ,
  `code` varchar(255) NOT NULL,
  `count` int(11) NOT NULL DEFAULT '0',
  `activity` tinyint(1) NOT NULL,
  `meta_title` varchar(255) ,
  `meta_keywords` varchar(1024) ,
  `meta_desc` text ,
  `old_price` varchar(255),
  `recommend` tinyint(4) NOT NULL DEFAULT '0',
  `new` tinyint(4) NOT NULL DEFAULT '0',
  `related` text,
  `inside_cat` text ,
  `1c_id` varchar(255) NOT NULL DEFAULT '',
  `weight` double,
  `link_electro` varchar(1024),
  `currency_iso` varchar(255),
  `price_course` double,
  `image_title` text,
  `image_alt` text,
  `yml_sales_notes` text,
  `count_buy` int(11),
  `system_set` INT(11),
  `related_cat` text,
  `short_description` longtext,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `unit` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `1c_id` (`1c_id`),
  FULLTEXT KEY `SEARCHPROD` (`title`,`description`,`code`,`meta_title`,`meta_keywords`,`meta_desc`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ",

"CREATE TABLE IF NOT EXISTS `".$prefix."product_variant` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `title_variant` varchar(255) NOT NULL,
  `image` varchar(255),
  `sort` int(11),
  `price` double NOT NULL,
  `old_price` varchar(255) NOT NULL,
  `count` int(11),
  `code` varchar(255),
  `activity` tinyint(1) NOT NULL,
  `weight` double NOT NULL,
  `currency_iso` varchar(255),
  `price_course` double,
  `1c_id` VARCHAR(255),
  `color` VARCHAR(255) NOT NULL,
  `size` VARCHAR(255) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  FULLTEXT KEY `title_variant` (`title_variant`),
  FULLTEXT KEY `code` (`code`)  
) ENGINE=MyISAM  DEFAULT CHARSET=utf8",


"CREATE TABLE IF NOT EXISTS `".$prefix."property` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `default` text,
  `data` text,
  `all_category` tinyint(1),
  `activity` int(1) NOT NULL DEFAULT '0',
  `sort` int(11),
  `filter` tinyint(1) NOT NULL DEFAULT '0',
  `description` TEXT, 
  `type_filter` VARCHAR(32) NULL,
  `1c_id` VARCHAR(255),
  `plugin` VARCHAR(255),
  `unit` VARCHAR(32),
  `group_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8",

"CREATE TABLE IF NOT EXISTS `".$prefix."property_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8",

"CREATE TABLE IF NOT EXISTS `".$prefix."setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `option` varchar(255) NOT NULL,
  `value` longtext,
  `active` varchar(1) NOT NULL DEFAULT 'N',
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE (`option`),
  INDEX (`option`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8",

"INSERT IGNORE INTO `".$prefix."setting` (`option`, `value`, `active`, `name`) VALUES
('sitename', 'localhost', 'Y', 'SITE_NAME'),
('adminEmail', '', 'Y', 'EMAIL_ADMIN'),
('templateName', 'moguta', 'Y', 'SITE_TEMPLATE'),
('countСatalogProduct', '6', 'Y', 'CATALOG_COUNT_PAGE'),
('currency', 'руб.', 'Y', 'SETTING_CURRENCY'),
('staticMenu', 'true', 'N', 'SETTING_STATICMENU'),
('orderMessage', 'Оформлен заказ № #ORDER# на сайте #SITE#', 'Y', 'TPL_EMAIL_ORDER'),
('downtime', 'false', 'N', 'DOWNTIME_SITE'),
('currentVersion', '', 'N', 'INFO_CUR_VERSION'),
('timeLastUpdata', '', 'N', 'LASTTIME_UPDATE'),
('title', ' Лучший магазин | Moguta.CMS', 'N', 'SETTING_PAGE_TITLE'),
('countPrintRowsProduct', '20', 'Y', 'ADMIN_COUNT_PROD'),
('languageLocale', 'ru_RU', 'N', 'ADMIN_LANG_LOCALE'),
('countPrintRowsPage', '10', 'Y', 'ADMIN_COUNT_PAGE'),
('themeColor', 'green-theme', 'N', 'ADMIN_THEM_COLOR'),
('themeBackground', 'bg_7', 'N', 'ADMIN_THEM_BG'),
('countPrintRowsOrder', '20', 'N', 'ADMIN_COUNT_ORDER'),
('countPrintRowsUser', '30', 'N', 'ADMIN_COUNT_USER'),
('licenceKey', '', 'N', 'LICENCE_KEY'),
('mainPageIsCatalog', 'true', 'Y', 'SETTING_CAT_ON_INDEX'),
('countNewProduct', '5', 'Y', 'COUNT_NEW_PROD'),
('countRecomProduct', '5', 'Y', 'COUNT_RECOM_PROD'),
('countSaleProduct', '5', 'Y', 'COUNT_SALE_PROD'),
('actionInCatalog', 'true', 'Y', 'VIEW_OR_BUY'),
('printProdNullRem', 'true', 'Y', 'PRINT_PROD_NULL_REM'),
('printRemInfo', 'true', 'Y', 'PRINT_REM_INFO'),
('heightPreview', '348', 'Y', 'PREVIEW_HEIGHT'),
('widthPreview', '540', 'Y', 'PREVIEW_WIDTH'),
('heightSmallPreview', '200', 'Y', 'PREVIEW_HEIGHT_2'),
('widthSmallPreview', '300', 'Y', 'PREVIEW_WIDTH_2'),
('waterMark', 'false', 'Y', 'WATERMARK'),
('widgetCode', '<!-- В это поле необходимо прописать код счетчика посещаемости Вашего сайта. Например, Яндекс.Метрика или Google analytics -->', 'Y', 'WIDGETCODE'),
('noReplyEmail', 'noreply@sitename.ru', 'Y', 'NOREPLY_EMAIL'),
('smtp', 'false', 'Y', 'SMTP'),
('smtpHost', '', 'Y', 'SMTP_HOST'),
('smtpLogin', '', 'Y', 'SMTP_LOGIN'),
('smtpPass', '', 'Y', 'SMTP_PASS'),
('smtpPort', '', 'Y', 'SMTP_PORT'),
('shopPhone', '8 (555) 555-55-55', 'Y', 'SHOP_PHONE'),
('shopAddress', 'г. Москва, ул. Тверская, 1. ', 'Y', 'SHOP_ADDERSS'),
('shopName', 'Интернет-магазин', 'Y', 'SHOP_NAME'),
('shopLogo', '/uploads/logo.svg', 'Y', 'SHOP_LOGO'),
('phoneMask', '+7 (###) ### ##-##,+380 (##) ### ##-##,+375 (##) ### ##-##', 'Y', 'PHONE_MASK'),
('printStrProp', 'false', 'Y', 'PROP_STR_PRINT'),
('noneSupportOldTemplate', 'false', 'Y', 'OLD_TEMPLATE'),
('printCompareButton', 'true', 'Y', 'BUTTON_COMPARE'),
('currencyShopIso', 'RUR', 'Y', 'CUR_SHOP_ISO'),
('cacheObject', 'true', 'Y', 'CACHE_OBJECT'),
('cacheMode', 'DB', 'Y', 'CACHE_MODE'),
('cacheTime', '86400', 'Y', 'CACHE_TIME'),
('cacheHost', '', 'Y', 'CACHE_HOST'),
('cachePort', '', 'Y', 'CACHE_PORT'),
('priceFormat', '1 234,56', 'Y', 'PRICE_FORMAT'),
('horizontMenu', 'false', 'Y', 'HORIZONT_MENU'),
('buttonBuyName', 'Купить', 'Y', 'BUTTON_BUY_NAME'),
('buttonCompareName', 'Сравнить', 'Y', 'BUTTON_COMPARE_NAME'),
('randomProdBlock', 'false', 'Y', 'RANDOM_PROD_BLOCK'),
('timeStartEngine', '".(int)time()."', 'N', 'TIME_START_ENGINE'),
('timeFirstUpdate', '', 'N', 'TIME_START_ENGINE'),
('buttonMoreName', 'Подробнее', 'Y', 'BUTTON_MORE_NAME'),
('compareCategory', 'true', 'Y', 'COMPARE_CATEGORY'),
('colorScheme', '227dc5', 'Y', 'COLOR_SCHEME'),
('useCaptcha', '', 'Y', 'USE_CAPTCHA'),
('autoRegister', 'true', 'Y', 'AUTO_REGISTER'),
('printFilterResult', 'true', 'Y', 'FILTER_RESULT'),
('dateActivateKey ', '0000-00-00 00:00:00', 'N', ''),
('propertyOrder', 'a:16:{s:7:\"nameyur\";s:40:\"ООО \"Интернет-магазин\"\";s:6:\"adress\";s:48:\"г.Москва ул. Тверская, дом 1\";s:3:\"inn\";s:10:\"8805614058\";s:3:\"kpp\";s:9:\"980501000\";s:4:\"ogrn\";s:13:\"7137847078193\";s:4:\"bank\";s:16:\"Сбербанк\";s:3:\"bik\";s:9:\"041012721\";s:2:\"ks\";s:20:\"40702810032030000834\";s:2:\"rs\";s:20:\"30101810600000000957\";s:7:\"general\";s:48:\"Михаил Васильевич Могутов\";s:4:\"sing\";s:0:\"\";s:5:\"stamp\";s:0:\"\";s:3:\"nds\";s:2:\"18\";s:8:\"usedsing\";s:4:\"true\";s:6:\"prefix\";s:3:\"MG_\";s:8:\"currency\";s:34:\"рубль,рубля,рублей\";}', 'N', ''),
('enabledSiteEditor', 'false', 'N', ''),
('lockAuthorization', 'false', 'Y','LOCK_AUTH'),
('orderNumber', 'true','Y', 'ORDER_NUMBER'),
('popupCart', 'true', 'Y', 'POPUP_CART'),
('catalogIndex', 'false', 'Y', 'CATALOG_INDEX'),
('productInSubcat', 'true', 'Y', 'PRODUCT_IN_SUBCAT'),
('copyrightMoguta', 'true', 'Y', 'COPYRIGHT_MOGUTA'),
('picturesCategory', 'true', 'Y', 'PICTURES_CATEGORY'),
('requiredFields', 'true', 'Y', 'REQUIRED_FIELDS'),
('backgroundSite', '', 'Y', 'BACKGROUND_SITE'),
('waterMarkVariants', 'false', 'Y', 'WATERMARK_VARIANTS'),
('cacheCssJs', 'false', 'Y', 'CACHE_CSS_JS'),
('categoryImgWidth', 200, 'Y', 'CATEGORY_IMG_WIDTH'),
('categoryImgHeight', 200, 'Y', 'CATEGORY_IMG_HEIGHT'),
('favicon', 'favicon.ico', 'Y', 'FAVICON'),
('connectZoom', 'true', 'Y', 'CONNECT_ZOOM'),
('filterSort', 'price_course|asc', 'Y', 'FILTER_SORT'),
('shortLink', 'false', 'Y', 'SHORT_LINK'),
('imageResizeType', 'PROPORTIONAL', 'Y', 'IMAGE_RESIZE_TYPE'),
('imageSaveQuality', '75', 'Y', 'IMAGE_SAVE_QUALITY'),
('duplicateDesc', 'false', 'Y', 'DUPLICATE_DESC'),
('excludeUrl', '', 'Y', 'EXCLUDE_SITEMAP'),
('autoGeneration', 'false', 'Y', 'AUTO_GENERATION'),
('generateEvery', '2', 'Y', 'GENERATE_EVERY'),
('consentData', 'true','Y', 'CONSENT_DATA'),
('showCountInCat', 'true','Y', 'SHOW_COUNT_IN_CAT'),
('nameOfLinkyml', 'getyml', 'N', 'NAME_OF_LINKYML'),
('clearCatalog1C', 'false', 'Y', 'CLEAR_1C_CATALOG'),
('fileLimit1C', '10000000', 'Y', 'FILE_LIMIT_1C'),
('notUpdateDescription1C', 'true', 'Y', 'UPDATE_DESCRIPTION_1C'),
('notUpdateImage1C', 'true', 'Y', 'UPDATE_IMAGE_1C'),
('showSortFieldAdmin', 'false', 'Y', 'SHOW_SORT_FIELD_ADMIN'),
('filterSortVariant', 'price_course|asc', 'Y', 'FILTER_SORT_VARIANT'),
('showVariantNull', 'true', 'Y', 'SHOW_VARIANT_NULL'),
('confirmRegistration', 'true', 'Y', 'CONFIRM_REGISTRATION'),
('cachePrefix', '', 'Y', 'CACHE_PREFIX'),
('usePhoneMask', 'true', 'Y', 'USE_PHONE_MASK'),
('smtpSsl', 'false' , 'Y', 'SMTP_SSL'),
('sessionToDB', 'false', 'Y', 'SAVE_SESSION_TO_DB'),
('sessionLifeTime', '1440', 'Y', 'SESSION_LIVE_TIME'),
('sessionAutoUpdate', 'true', 'Y', 'SESSION_AUTO_UPDATE'),
('showCodeInCatalog', 'false', 'Y', 'SHOW_CODE_IN_CATALOG'),
('openGraph', 'true', 'Y', 'OPEN_GRAPH'),
('dublinCore', 'true', 'Y', 'DUBLIN_CORE'),
('printSameProdNullRem', 'true', 'Y', 'PRINT_SAME_PROD_NULL_REM'),
('landingName', 'lp-moguta', 'N', ''),
('colorSchemeLanding', 'none', 'N', ''),
('printQuantityInMini', 'false', 'Y', 'SHOW_QUANTITY'),
('printCurrencySelector', 'false', 'Y', 'CURRENCY_SELECTOR'),
('interface', 'a:5:{s:9:\"colorMain\";s:7:\"#2773eb\";s:9:\"colorLink\";s:7:\"#1585cf\";s:9:\"colorSave\";s:7:\"#4caf50\";s:11:\"colorBorder\";s:7:\"#e6e6e6\";s:14:\"colorSecondary\";s:7:\"#ebebeb\";}', 'Y', ''),
('filterCountProp', '3', 'Y', 'FILTER_COUNT_PROP'),
('filterMode', 'true', 'Y', 'FILTER_MODE'),
('filterSubcategory', 'false', 'Y', 'FILTER_SUBCATGORY'),
('printVariantsInMini', 'false', 'Y', 'SHOW_VARIANT_MINI'),
('useReCaptcha', 'false', 'Y', 'USE_RECAPTCHA'),
('reCaptchaKey', '', 'Y', 'RECAPTCHA_KEY'),
('reCaptchaSecret', '', 'Y', 'RECAPTCHA_SECRET'),
('timeWork', '09:00 - 19:00,10:00 - 17:00', 'Y', 'TIME_WORK'),
('useSeoRewrites', 'false', 'Y', 'SEO_REWRITES'),
('useSeoRedirects', 'false', 'Y', 'SEO_REDIRECTS'),
('showMainImgVar', 'false', 'Y', 'SHOW_MAIN_IMG_VAR'),
('loginAttempt', '5', 'Y', 'LOGIN_ATTEMPT'),
('prefixOrder', 'M-010', 'Y', 'PREFIX_ORDER'),
('captchaOrder', 'false', 'Y', 'CAPTCHA_ORDER'),
('deliveryZero', 'true', 'Y', 'DELIVERY_ZERO'),
('outputMargin', 'true', 'Y', 'OUTPUT_MARGIN'),
('prefixCode', 'CN', 'Y', 'PREFIX_CODE'),
('maxUploadImgWidth', '1500', 'Y', 'MAX_UPLOAD_IMAGE_WIDTH'),
('maxUploadImgHeight', '1500', 'Y', 'MAX_UPLOAD_IMAGE_HEIGHT'),
('searchType', 'like', 'Y', 'SEARCH_TYPE'),
('searchSphinxHost', 'localhost', 'Y', 'SEARCH_SPHINX_HOST'),
('searchSphinxPort', '9312', 'Y', 'SEARCH_SPHINX_PORT'),
('checkAdminIp', 'false', 'Y', 'CHECK_ADMIN_IP'),
('printSeo', 'all', 'Y', 'PRINT_SEO'),
('catalogProp', '0', 'Y', 'CATALOG_PROP'),
('printAgreement', 'true', 'Y', 'PRINT_AGREEMENT'),
('currencyShort', 'a:6:{s:3:\"RUR\";s:7:\"руб.\";s:3:\"UAH\";s:7:\"грн.\";s:3:\"USD\";s:1:\"$\";s:3:\"EUR\";s:3:\"€\";s:3:\"KZT\";s:10:\"тенге\";s:3:\"UZS\";s:6:\"сум\";}', 'Y', 'CUR_SHOP_SHORT'),
('useElectroLink', 'false', 'Y', 'USE_ELECTRO_LINK'),
('currencyActive', 'a:5:{i:0;s:3:\"UAH\";i:1;s:3:\"USD\";i:2;s:3:\"EUR\";i:3;s:3:\"KZT\";i:4;s:3:\"UZS\";}', 'Y', ''),
('closeSite', 'false', 'Y', 'CLOSE_SITE_1C'),
('catalogPreCalcProduct', 'old', 'Y', 'CATALOG_PRE_CALC_PRODUCT'),
('printSpecFilterBlock', 'true', 'Y', 'FILTER_PRINT_SPEC'),
('disabledPropFilter', 'false', 'Y', 'DISABLED_PROP_FILTER'),
('enableDeliveryCur', 'false', 'Y', 'ENABLE_DELIVERY_CUR'),
('addDateToImg', 'true', 'Y', 'ADD_DATE_TO_IMG'),
('variantToSize1c', 'false', 'Y', 'VARIANT_TO_SIZE_1C'),
('sphinxLimit', '20', 'Y', 'SPHINX_LIMIT'),
('filterCatalogMain', 'false', 'Y', 'FILTER_CATALOG_MAIN'),
('importColorSize', 'size', 'Y', 'IMPORT_COLOR_SIZE'),
('sizeName1c', 'Размер', 'Y', 'SIZE_NAME_1C'),
('colorName1c', 'Цвет', 'Y', 'COLOR_NAME_1C'),
('sizeMapMod', 'COLOR', 'Y', 'SIZE_MAP_MOD'),
('modParamInVarName', 'true', 'Y', 'MOD_PARAM_IN_VAR_NAME')
",

"INSERT IGNORE INTO `".$prefix."setting` (`option`, `value`, `active`, `name`) VALUES
('catalog_meta_title', '{titeCategory}', 'N', ''),
('catalog_meta_description', '{cat_desc,160}', 'N', ''),
('catalog_meta_keywords', '{meta_keywords}', 'N', ''),
('product_meta_title', '{title}', 'N', ''),
('product_meta_description', '{title} за {price} {currency} купить. {description,100}', 'N', ''),
('product_meta_keywords', '{meta_keywords}', 'N', ''),
('page_meta_title', '{title}', 'N', ''),
('page_meta_description', '{html_content,160}', 'N', ''),
('page_meta_keywords', '{meta_keywords}', 'N', '')",

"INSERT IGNORE INTO `".$prefix."setting` (`option`, `value`, `active`, `name`) VALUES
('currencyRate', 'a:6:{s:3:\"RUR\";d:1;s:3:\"UAH\";d:1;s:3:\"USD\";d:1;s:3:\"EUR\";d:1;s:3:\"KZT\";d:1;s:3:\"UZS\";d:1;}', 'Y', 'CUR_SHOP_RATE')",

"INSERT INTO `".$prefix."setting` (`option`, `value`) VALUES ('lastModVersion', 'v8.2.2')",

"CREATE TABLE IF NOT EXISTS `".$prefix."brand-logo` (     
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Порядковый номер записи',     
  `brand` text NOT NULL COMMENT 'Бренд',
  `url` text NOT NULL COMMENT 'Логотип',    
  `desc` text NOT NULL COMMENT 'Описание',    
  `sort` int(11) NOT NULL COMMENT 'Порядок',
  `seo_title` text NOT NULL,
  `seo_keywords` text NOT NULL,
  `seo_desc` text NOT NULL,
   PRIMARY KEY (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ",

"CREATE TABLE IF NOT EXISTS `".$prefix."trigger-guarantee` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Порядковый номер',
  `title` text NOT NULL COMMENT 'Загаловок',
  `settings` text NOT NULL COMMENT 'Настройки',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;",

"CREATE TABLE IF NOT EXISTS `".$prefix."trigger-guarantee-elements` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Порядковый номер',
  `parent` int(11) NOT NULL COMMENT 'id блока',
  `text` text NOT NULL COMMENT 'Текст триггера',
  `icon` text NOT NULL COMMENT 'Иконка или url картинки',
  `sort` int(11) NOT NULL COMMENT 'Сортировка',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;",

"CREATE TABLE IF NOT EXISTS `".$prefix."comments` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `name` VARCHAR(45) NOT NULL,
  `email` VARCHAR(45) NOT NULL,
  `comment` TEXT NoT NULL,
  `date` TIMESTAMP NOT NULL,
  `uri` VARCHAR(255) NOT NULL,
  `approved` TINYINT NOT NULL DEFAULT 0, 
  `img` text NOT NULL,
  PRIMARY KEY(`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8",

"CREATE TABLE IF NOT EXISTS `".$prefix."user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL,
  `pass` varchar(255) DEFAULT NULL,
  `role` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `sname` varchar(255) DEFAULT NULL,
  `address` text,
  `address_index` TEXT DEFAULT NULL,
  `address_country` TEXT DEFAULT NULL,
  `address_region` TEXT DEFAULT NULL,
  `address_city` TEXT DEFAULT NULL,
  `address_street` TEXT DEFAULT NULL,
  `address_house` TEXT DEFAULT NULL,
  `address_flat` TEXT DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_add` timestamp NOT NULL DEFAULT ".$curTimestamp.",
  `blocked` int(1) NOT NULL DEFAULT '0',
  `restore` varchar(255) DEFAULT NULL,
  `activity` int(1) DEFAULT '0',
  `inn` text ,
  `kpp` text ,
  `nameyur` text ,
  `adress` text,
  `bank` text,
  `bik` text,
  `ks` text,
  `rs` text,
  `birthday` text,
  `ip` TEXT,
  PRIMARY KEY (`id`),
  KEY `email` (`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1",  
  
//Таблица, для хранения ссылок на страницы выборок фильтров
"CREATE TABLE IF NOT EXISTS `".$prefix."url_rewrite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` TEXT NOT NULL,
  `short_url` varchar(255) NOT NULL,
  `titeCategory` varchar(255) DEFAULT NULL,
  `cat_desc` longtext NOT NULL,
  `meta_title` varchar(255) NOT NULL,
  `meta_keywords` varchar(1024) NOT NULL,
  `meta_desc` text NOT NULL,
  `activity` tinyint(1) NOT NULL DEFAULT 1,
  `cat_desc_seo` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1",
  
//Таблица для записей редиректов
"CREATE TABLE IF NOT EXISTS `".$prefix."url_redirect` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url_old` TEXT NOT NULL,
  `url_new` TEXT NOT NULL,
  `code` int(3) NOT NULL,
  `activity` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1",
 
 //Таблица, для хранения сессий
"CREATE TABLE `".$prefix."sessions` ( 
  `session_id` varchar(255) binary NOT NULL default '', 
  `session_expires` int(11) unsigned NOT NULL default '0', 
  `session_data` longtext, 
  PRIMARY KEY  (`session_id`) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

// для плагинов по умолячанию
// слайдер акций
"CREATE TABLE IF NOT EXISTS `".$prefix."slider-action` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Порядковый номер записи',
  `type` varchar(255) NOT NULL COMMENT 'Тип слайда картинка или HTML',
  `nameaction` text NOT NULL COMMENT 'Название слайда',
  `href` text NOT NULL COMMENT 'ссылка', 
  `value` text NOT NULL COMMENT 'значение',      
  `sort` int(11) NOT NULL COMMENT 'Порядок слайдов',
  `invisible` int(1) NOT NULL COMMENT 'видимость',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;",

// для редактора блоков
"CREATE TABLE IF NOT EXISTS `".$prefix."site-block-editor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment` text NOT NULL,
  `type` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `width` text NOT NULL, 
  `height` text NOT NULL,      
  `alt` text NOT NULL,
  `title` text NOT NULL,
  `href` text NOT NULL,
  `class` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;",

"CREATE TABLE IF NOT EXISTS `".$prefix."product_rating` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Порядковый номер записи',
  `id_product` int(11) NOT NULL COMMENT 'ID товара',
  `rating` double NOT NULL COMMENT 'Оценка',
  `count` int(11) NOT NULL COMMENT 'Количество голосов',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=28",
// для кэша
// 'CREATE INDEX name ON '.$prefix.'cache(name);',
'CREATE INDEX date_add ON '.$prefix.'cache(date_add);',
// для категорий
'CREATE INDEX id ON '.$prefix.'category(id);',
// для связки категорий с характеристиками
'CREATE INDEX category_id ON '.$prefix.'category_user_property(category_id);',
'CREATE INDEX property_id ON '.$prefix.'category_user_property(property_id);',
// для заказов
'CREATE INDEX id ON '.$prefix.'order(id);',
'CREATE INDEX user_email ON '.$prefix.'order(user_email);',
'CREATE INDEX status_id ON '.$prefix.'order(status_id);',
'CREATE INDEX 1c_last_export ON '.$prefix.'order(1c_last_export);',
// для товаров
'CREATE INDEX id ON '.$prefix.'product(id);',
'CREATE INDEX cat_id ON '.$prefix.'product(cat_id);',
'CREATE INDEX url ON '.$prefix.'product(url);',
'CREATE INDEX code ON '.$prefix.'product(code);',
// для характеристик
'CREATE INDEX id ON '.$prefix.'property(id);',
'CREATE INDEX name ON '.$prefix.'property(name);',
'CREATE INDEX 1c_id ON '.$prefix.'property(1c_id);',
// для настроек
// 'CREATE INDEX option ON '.$prefix.'setting(option);',
// для складов
'CREATE INDEX product_id ON '.$prefix.'product_on_storage(product_id);',
'CREATE INDEX variant_id ON '.$prefix.'product_on_storage(variant_id);',
'CREATE INDEX storage ON '.$prefix.'product_on_storage(storage);',
// для скидок
'CREATE INDEX product_id ON '.$prefix.'wholesales_sys(product_id);',
'CREATE INDEX variant_id ON '.$prefix.'wholesales_sys(variant_id);',
'CREATE INDEX count ON '.$prefix.'wholesales_sys(count);',
);
