<?php

if ($mysqlVersion >= 50503) {
  $encoding = 'utf8mb4';
} else {
  $encoding = 'utf8';
}

$damp = array(
  "DROP TABLE IF EXISTS `".$prefix."category`, `".$prefix."category_user_property`, `".$prefix."delivery`, `".$prefix."delivery_payment_compare`, `".$prefix."order`, `".$prefix."order_comments`, `".$prefix."page`, `".$prefix."payment`, `".$prefix."plugins`, `".$prefix."product`, `".$prefix."product_variant`, `".$prefix."property`, `".$prefix."setting`, `".$prefix."user`, `".$prefix."mg-slider`, `".$prefix."site-block-editor`, `".$prefix."product_rating`, `".$prefix."trigger-guarantee`, `".$prefix."trigger-guarantee-elements`, `".$prefix."comments`, `".$prefix."mg-brand`, `".$prefix."GoogleMerchant`, `".$prefix."GoogleMerchantCats`, `".$prefix."YandexMarket`, `".$prefix."sessions`, `".$prefix."cache`, `".$prefix."avito_settings`, `".$prefix."avito_cats`, `".$prefix."avito_locations`, `".$prefix."product_user_property_data`, `".$prefix."property_data`, `".$prefix."locales`, `".$prefix."product_on_storage`, `".$prefix."landings`, `".$prefix."wholesales_sys`, `".$prefix."promo-code`, `".$prefix."property_group`, `".$prefix."product_user_property`, `".$prefix."messages`, `".$prefix."user_group`, `".$prefix."url_redirect`, `".$prefix."url_rewrite`, `".$prefix."write_lock`, `".$prefix."order_opt_fields`, `".$prefix."product_opt_fields`,`".$prefix."order_opt_fields_content`, `".$prefix."user_opt_fields`, `".$prefix."user_opt_fields_content`, `".$prefix."category_opt_fields`, `".$prefix."category_opt_fields_content`, `".$prefix."user_logins`, `".$prefix."letters`",
  "SET names ".$encoding,

  "CREATE TABLE IF NOT EXISTS `".$prefix."user_logins` ( 
      `created_at` BIGINT(20) NULL DEFAULT NULL,
      `user_id` INT(11) NULL DEFAULT NULL,
      `access` TINYTEXT NULL DEFAULT NULL,
      `last_used` INT(11) NULL DEFAULT NULL,
      `fails` TINYTEXT NULL DEFAULT NULL,
      UNIQUE KEY (`created_at`)
    ) ENGINE = MyISAM;",

  "CREATE TABLE IF NOT EXISTS ".$prefix."category_opt_fields (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(249) NOT NULL,
    `sort` int(11),
    PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

  "CREATE TABLE IF NOT EXISTS ".$prefix."category_opt_fields_content (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `field_id` int(11) NOT NULL DEFAULT 0,
    `category_id` int(11) NOT NULL DEFAULT 0,
    `value` TEXT,
    PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

  "CREATE TABLE IF NOT EXISTS ".$prefix."product_opt_fields (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` TEXT,
      `is_price` tinyint(1) DEFAULT 0,
      `sort` int(11),
      `active` tinyint(1) DEFAULT 1,
      PRIMARY KEY (`id`)
      ) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

  "CREATE TABLE IF NOT EXISTS ".$prefix."user_opt_fields (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(249) NOT NULL,
    `type` varchar(249) NOT NULL,
    `vars` TEXT,
    `sort` int(11),
    `placeholder` TEXT,
    `active` tinyint(1) DEFAULT 0,
    PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

  "CREATE TABLE IF NOT EXISTS ".$prefix."user_opt_fields_content (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `field_id` int(11) NOT NULL DEFAULT 0,
    `user_id` int(11) NOT NULL DEFAULT 0,
    `value` TEXT,
    PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

  "CREATE TABLE IF NOT EXISTS ".$prefix."order_opt_fields (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(249) NOT NULL,
    `type` varchar(249) NOT NULL,
    `vars` TEXT,
    `sort` int(11),
    `placeholder` TEXT,
    `droped` int(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

  "CREATE TABLE IF NOT EXISTS ".$prefix."order_opt_fields_content (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `field_id` int(11) NOT NULL DEFAULT 0,
    `order_id` int(11) NOT NULL DEFAULT 0,
    `value` TEXT,
    PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

  "CREATE TABLE IF NOT EXISTS ".$prefix."write_lock (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `table` varchar(249) NOT NULL,
    `entity_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `time_block` int(11) NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

  "CREATE TABLE IF NOT EXISTS `".$prefix."notification` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `message` longtext NOT NULL,
    `status` tinyint(1) NOT NULL DEFAULT '0',
    UNIQUE KEY `id` (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

  "CREATE TABLE IF NOT EXISTS `".$prefix."user_group` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `can_drop` tinyint(1) NOT NULL DEFAULT '1',
    `name` varchar(249) NOT NULL DEFAULT '0',
    `admin_zone` tinyint(1) NOT NULL DEFAULT '0',
    `product` tinyint(1) NOT NULL DEFAULT '0',
    `page` tinyint(1) NOT NULL DEFAULT '0',
    `category` tinyint(1) NOT NULL DEFAULT '0',
    `order` tinyint(1) DEFAULT '0',
    `user` tinyint(1) NOT NULL DEFAULT '0',
    `plugin` tinyint(1) NOT NULL DEFAULT '0',
    `setting` tinyint(1) NOT NULL DEFAULT '0',
    `wholesales` tinyint(1) NOT NULL DEFAULT '0',
    `order_status` TEXT NULL DEFAULT NULL COMMENT 'доступные статусы заказов',
    `ignore_owners` TINYINT NULL DEFAULT '0' COMMENT 'игнорировать ответственных',
    UNIQUE KEY `id` (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

  "INSERT INTO `".$prefix."user_group` (`id`, `can_drop`, `name`, `admin_zone`, `product`, `page`, `category`, `order`, `user`, `plugin`, `setting`, `wholesales`) VALUES
  (-1, 0, 'Гость (Не авторизован)', 0, 0, 0, 0, 0, 0, 0, 0, 0),
  (1, 0, 'Администратор', 1, 2, 2, 2, 2, 2, 2, 2, 1),
  (2, 0, 'Пользователь', 0, 0, 0, 0, 0, 0, 0, 0, 0),
  (3, 0, 'Менеджер', 1, 2, 0, 1, 2, 0, 2, 0, 0),
  (4, 0, 'Модератор', 1, 1, 2, 0, 0, 0, 2, 0, 0);",

  "CREATE TABLE IF NOT EXISTS `".$prefix."messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(249) NOT NULL,
  `text` text NOT NULL,
  `text_original` text NOT NULL,
  `group` varchar(249) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

  "CREATE TABLE IF NOT EXISTS `".$prefix."product_user_property` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `product_id` int(11) NOT NULL,
    `property_id` int(11) NOT NULL,
    `value` text NOT NULL,
    `product_margin` text NOT NULL COMMENT 'наценка продукта',
    `type_view` enum('checkbox','select','radiobutton','') NOT NULL DEFAULT 'select',
    KEY `product_id` (`product_id`),
    KEY `property_id` (`property_id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci COMMENT='Таблица пользовательских свойств продуктов'",

  "CREATE TABLE IF NOT EXISTS `".$prefix."wholesales_sys` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL,
    `variant_id` int(11) NOT NULL,
    `count` int(11) NOT NULL,
    `price` double NOT NULL DEFAULT 0,
    `group` int(11) DEFAULT 1,
    PRIMARY KEY (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

  "CREATE TABLE `".$prefix."product_on_storage` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `storage` varchar(249) NOT NULL,
    `product_id` int(11) NOT NULL,
    `variant_id` int(11) NOT NULL,
    `count` int(11) NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

  "CREATE TABLE IF NOT EXISTS `".$prefix."landings` (
  `id` int(11) NOT NULL,
  `template` varchar(249) CHARACTER SET utf8 DEFAULT NULL,
  `templateColor` varchar(6) CHARACTER SET utf8 DEFAULT NULL,
  `ytp` longtext CHARACTER SET utf8,
  `image` varchar(249) CHARACTER SET utf8 DEFAULT NULL,
  `buySwitch` varchar(6) CHARACTER SET utf8 DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

  "CREATE TABLE IF NOT EXISTS `".$prefix."googlemerchant` (
  `name` varchar(249) NOT NULL,
  `settings` longtext NOT NULL,
  `cats` longtext NOT NULL,
  `edited` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

  "CREATE TABLE IF NOT EXISTS `".$prefix."googlemerchantcats` (
  `id` int(255) NOT NULL,
  `name` varchar(249) NOT NULL,
  `parent_id` int(255) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;", 

"CREATE TABLE IF NOT EXISTS `".$prefix."vk-export` (
  `moguta_id` int(11) NOT NULL,
  `vk_id` varchar(249) NOT NULL,
  `moguta_img` varchar(249) NOT NULL,
  `vk_img` varchar(249) NOT NULL,
  PRIMARY KEY (`moguta_id`)
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

"CREATE TABLE IF NOT EXISTS `".$prefix."avito_settings` (
  `name` varchar(249) NOT NULL,
  `settings` longtext NOT NULL,
  `cats` longtext NOT NULL,
  `additional` longtext NOT NULL,
  `edited` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

"CREATE TABLE IF NOT EXISTS `".$prefix."avito_cats` (
  `id` int(255) NOT NULL,
  `name` varchar(249) NOT NULL,
  `parent_id` int(255) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

"CREATE TABLE IF NOT EXISTS `".$prefix."avito_locations` (
  `id` int(255) NOT NULL,
  `name` varchar(249) NOT NULL,
  `type` int(5) NOT NULL,
  `parent_id` int(255) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

"CREATE TABLE `".$prefix."locales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_ent` int(11) NOT NULL,
  `locale` varchar(249) CHARACTER SET utf8 NOT NULL,
  `table` varchar(249) CHARACTER SET utf8 NOT NULL,
  `field` varchar(249) CHARACTER SET utf8 NOT NULL,
  `text` longtext CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`),
  INDEX (`id`),
  INDEX (`id_ent`),
  INDEX (`locale`),
  INDEX (`table`),
  INDEX (`field`)
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

  "CREATE TABLE IF NOT EXISTS `".$prefix."yandexmarket` (
  `name` varchar(249) NOT NULL,
  `settings` longtext NOT NULL,
  `edited` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

  "CREATE TABLE IF NOT EXISTS `".$prefix."cache` (
  `date_add` int(11) NOT NULL,
  `lifetime` int(11) NOT NULL,
  `name` varchar(249) NOT NULL,
  `value` longtext NOT NULL,
  UNIQUE KEY `name` (`name`),
  INDEX (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

"CREATE TABLE IF NOT EXISTS `".$prefix."category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `left_key` int(11) NOT NULL DEFAULT 1,
  `right_key` int(11) NOT NULL DEFAULT 1,
  `level` int(11) NOT NULL DEFAULT 2,
  `title` varchar(249),
  `menu_title` varchar(249) NOT NULL DEFAULT '',
  `url` varchar(249),
  `parent` int(11) NOT NULL,
  `parent_url` varchar(249) NOT NULL,
  `sort` int(11),
  `html_content` longtext,
  `meta_title` text,
  `meta_keywords` text,
  `meta_desc` text,
  `invisible` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Не выводить в меню',
  `1c_id` varchar(249),
  `image_url` text,
  `menu_icon` text,
  `rate` double NOT NULL DEFAULT '0',
  `export` tinyint(1) NOT NULL DEFAULT '1',
  `seo_content` text,
  `activity` TINYINT(1) NOT NULL DEFAULT '1',
  `unit` varchar(249) NOT NULL DEFAULT 'шт.',
  `seo_alt` text,
  `seo_title` text,
  `menu_seo_alt` text,
  `menu_seo_title` text,
  `countProduct` int(11) NOT NULL DEFAULT '0',
  `weight_unit` VARCHAR(10) NOT NULL DEFAULT 'kg',
  PRIMARY KEY (`id`),
  KEY `1c_id` (`1c_id`),
  KEY `url` (`url`),
  KEY `parent_url` (`parent_url`)
) ENGINE=MyISAM  DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",


"CREATE TABLE IF NOT EXISTS `".$prefix."category_user_property` (
  `category_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

"CREATE TABLE IF NOT EXISTS `".$prefix."product_user_property_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prop_id` int(11) NOT NULL,
  `prop_data_id` int(11) NOT NULL DEFAULT 0,
  `product_id` int(11) NOT NULL,
  `name` text,
  `margin` text,
  `type_view` text CHARACTER SET utf8 NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  INDEX (id),
  INDEX (prop_id),
  INDEX (product_id)
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

"CREATE TABLE IF NOT EXISTS `".$prefix."property_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prop_id` int(11) NOT NULL,
  `name` varchar(249) CHARACTER SET utf8 NOT NULL,
  `margin` text CHARACTER SET utf8 NOT NULL,
  `sort` int(11) NOT NULL DEFAULT 1,
  `color` varchar(45) NOT NULL,
  `img` text NOT NULL,
  PRIMARY KEY (`id`),
  INDEX (id),
  INDEX (name),
  INDEX (prop_id)
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

"CREATE TABLE IF NOT EXISTS `".$prefix."delivery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(249) NOT NULL,
  `cost` double,
  `description` text,
  `activity` int(1) NOT NULL DEFAULT '0',
  `free` double COMMENT 'Бесплатно от',
  `date` int(1),
  `sort` int(11),
  `plugin` varchar(249),
  `weight` longtext,
  `interval` longtext,
  `address_parts` INT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci COMMENT='таблица способов доставки товара';",

"INSERT INTO `".$prefix."delivery` (`id`, `name`, `cost`, `description`, `activity`, `free`, `date`, `sort`) VALUES  
    (1, 'Курьер', 700, 'Курьерская служба', 1, 0, 1, 1),
    (2, 'Почта', 200, 'Почта России', 1, 0, 0, 2),
    (3, 'Без доставки', 0, 'Самовывоз', 1, 0, 0, 3)",

"CREATE TABLE IF NOT EXISTS `".$prefix."delivery_payment_compare` (
  `payment_id` int(10) DEFAULT NULL,
  `delivery_id` int(10) DEFAULT NULL,
  `compare` int(1) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

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
  ('22', 'msg__enter_blocked', 'В целях безопасности возможность авторизации заблокирована на #MINUTES# мин. Отсчет времени от #TIME#.', 'В целях безопасности возможность авторизации заблокирована на #MINUTES# мин. Отсчет времени от #TIME#.', 'register'),
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
  ('53', 'msg__payment_inn', 'Заполните ИНН', 'Заполните ИНН', 'order'),
  ('54', 'msg__payment_required', 'Заполнены не все обязательные поля', 'Заполнены не все обязательные поля', 'order'),
  ('55', 'msg__storage_non_selected', 'Склад не выбран!', 'Склад не выбран!', 'order');
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
  `owner` int(11) NOT NULL DEFAULT 0,
  `updata_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `add_date` timestamp NULL DEFAULT NULL,
  `close_date` timestamp NULL DEFAULT NULL,
  `pay_date` timestamp NULL DEFAULT NULL,
  `user_email` varchar(249) DEFAULT NULL,
  `phone` varchar(249) DEFAULT NULL,
  `address` text,
  `address_parts` text DEFAULT NULL,
  `summ` varchar(249) DEFAULT NULL COMMENT 'Общая сумма товаров в заказе ',
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
  `confirmation` varchar(249) DEFAULT NULL,
  `yur_info` text NOT NULL,
  `name_buyer` text NOT NULL,
  `name_parts` text DEFAULT NULL,
  `date_delivery` text,
  `ip` text NOT NULL,
  `number` varchar(32),
  `hash` VARCHAR(32),
  `1c_last_export` timestamp NULL DEFAULT NULL,  
  `orders_set` INT( 11 ),
  `storage` text NOT NULL,
  `summ_shop_curr` double DEFAULT NULL,
  `delivery_shop_curr` double DEFAULT NULL,
  `currency_iso` varchar(249) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

"CREATE TABLE IF NOT EXISTS `".$prefix."order_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `text` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

"CREATE TABLE IF NOT EXISTS `".$prefix."page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_url` varchar(249) NOT NULL,
  `parent` int(11) NOT NULL,
  `title` varchar(249) NOT NULL,
  `url` varchar(249) COLLATE ".$encoding."_bin NOT NULL,
  `html_content` longtext NOT NULL,
  `meta_title` text,
  `meta_keywords` text,
  `meta_desc` text,
  `sort` int(11),
  `print_in_menu` tinyint(4) NOT NULL DEFAULT '0',
  `invisible` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Не выводить в меню',
  `without_style` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Выводить без стилей шаблона',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

"INSERT INTO `".$prefix."page` (`id`, `parent_url`, `parent`, `title`, `url`, `html_content`, `meta_title`, `meta_keywords`, `meta_desc`, `sort`, `print_in_menu`, `invisible`) VALUES
(1, '', 0, 'Главная', 'index', '<h3 class=\"c-title\">О магазине</h3><div><p>Мы стабильная и надежная компания, с каждым днем наращиваем свой потенциал. Имеем огромный опыт в сфере корпоративных продаж, наши менеджеры готовы предложить Вам высокий уровень сервиса, грамотную консультацию, выгодные условия работы и широкий спектр цветовых решений. В число наших постоянных клиентов входят крупные компании.</p><p>Наши товары производятся только из самых качественных материалов!</p><p>Отдел корпоративных продаж готов предложить Вам персонального менеджера, грамотную консультацию, доставку на следующий день после оплаты, сертификаты на всю продукцию, индивидуальный метод работы.</p><p>Отдельным направлением является работа с частными лицами с оперативной доставкой, низкими ценами и высоким качеством обслуживания.</p><p>Главное для нас — своевременно удовлетворять потребности наших клиентов всеми силами и доступными нам средствами. Работая с нами, Вы гарантированно приобретаете только оригинальный товар подлинного качества.</p><p>Мы работаем по всем видам оплат. Только приобретая товар у официального дилера, Вы застрахованы от подделок. Будем рады нашему долгосрочному сотрудничеству.</p><p>** Информация представленная на сайте является демонстрационной для ознакомления с Moguta.CMS. <a data-cke-saved-href=\"https://moguta.ru/\" href=\"https://moguta.ru/\">Moguta.CMS - простая cms для интернет-магазина.</a></p></div>', 'Главная', 'Главная', '', 5, 0, 1),
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
  `add_security` varchar(249) NOT NULL,
  `permission` VARCHAR(5) NOT NULL DEFAULT 'fiz',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

"INSERT INTO `".$prefix."payment` (`id`, `name`, `activity`, `paramArray`, `urlArray`, `sort`, `add_security`, `permission`) VALUES
(3, 'Наложенный платеж', 1, '{\"Примечание\":\"\"}', '', 3, '".md5($siteName)."', 'fiz'),
(4, 'Наличные (курьеру)', 1, '{\"Примечание\":\"\"}', '', 4, '".md5($siteName)."', 'fiz'),
(7, 'Оплата по реквизитам', 1, '{\"Юридическое лицо\":\"\", \"ИНН\":\"\",\"КПП\":\"\", \"Адрес\":\"\", \"Банк получателя\":\"\", \"БИК\":\"\",\"Расчетный счет\":\"\",\"Кор. счет\":\"\"}', '', 7, '".md5($siteName)."', 'yur')",

"INSERT INTO `".$prefix."payment` (`id`, `name`, `activity`, `paramArray`, `urlArray`, `sort`, `add_security`, `permission`) VALUES
(1, 'WebMoney', 1, '{\"Номер кошелька\":\"\",\"Секретный ключ\":\"\",\"Тестовый режим\":\"".CRYPT::mgCrypt('false')."\",\"Метод шифрования\":\"".CRYPT::mgCrypt('md5')."\"}', '{\"result URL:\":\"/payment?id=1&pay=result\",\"success URL:\":\"/payment?id=1&pay=success\",\"fail URL:\":\"/payment?id=1&pay=fail\"}', 1, '".md5($siteName)."', 'fiz'),
(2, 'Яндекс.Деньги', 1, '{\"Номер счета\":\"\",\"Секретный ключ\":\"\",\"Тестовый режим\":\"".CRYPT::mgCrypt('false')."\",\"Метод шифрования\":\"".CRYPT::mgCrypt('sha1')."\"}', '{\"result URL:\":\"/payment?id=2&pay=result\",\"success URL:\":\"/payment?id=2&pay=success\",\"fail URL:\":\"/payment?id=2&pay=fail\"}', 2, '".md5($siteName)."', 'fiz'),
(5, 'ROBOKASSA', 1, '{\"Логин\":\"\",\"пароль 1\":\"\",\"пароль 2\":\"\",\"Метод шифрования\":\"".CRYPT::mgCrypt('md5')."\",\"Использовать онлайн кассу\":\"ZmFsc2VoWSo2bms3ISEjMnFq\",\"НДС, включенный в цену\":\"MjAlaFkqNm5rNyEhIzJxag==\"}', '{\"result URL:\":\"/payment?id=5&pay=result\",\"success URL:\":\"/payment?id=5&pay=success\",\"fail URL:\":\"/payment?id=5&pay=fail\"}', 5, '".md5($siteName)."', 'fiz'),
(8, 'Интеркасса', 1, '{\"Идентификатор кассы\":\"\",\"Секретный ключ\":\"\",\"Тестовый режим\":\"".CRYPT::mgCrypt('false')."\",\"Метод шифрования\":\"".CRYPT::mgCrypt('md5')."\"}', '{\"result URL:\":\"/payment?id=8&pay=result\",\"success URL:\":\"/payment?id=8&pay=success\",\"fail URL:\":\"/payment?id=8&pay=fail\"}', 8, '".md5($siteName)."', 'fiz'),
(9, 'PayAnyWay', 1, '{\"Номер расширенного счета\":\"\",\"Код проверки целостности данных\":\"\",\"Тестовый режим\":\"".CRYPT::mgCrypt('false')."\",\"Метод шифрования\":\"".CRYPT::mgCrypt('md5')."\"}', '{\"result URL:\":\"/payment?id=9&pay=result\",\"success URL:\":\"/payment?id=9&pay=success\",\"fail URL:\":\"/payment?id=9&pay=fail\"}', 9, '".md5($siteName)."', 'fiz'),
(10, 'PayMaster', 1, '{\"ID магазина\":\"\",\"Секретный ключ\":\"\",\"Метод шифрования\":\"".CRYPT::mgCrypt('md5')."\",\"Использовать онлайн кассу\":\"ZmFsc2VoWSo2bms3ISEjMnFq\",\"НДС, включенный в цену\":\"MjAlaFkqNm5rNyEhIzJxag==\"}', '{\"result URL:\":\"/payment?id=10&pay=result\",\"success URL:\":\"/payment?id=10&pay=success\",\"fail URL:\":\"/payment?id=10&pay=fail\"}', 10, '".md5($siteName)."', 'fiz'),
(11, 'AlfaBank', '1',  '{\"Логин\":\"\",\"Пароль\":\"\",\"Адрес сервера\":\"\",\"Метод шифрования\":\"".CRYPT::mgCrypt('md5')."\"}',  '{\"result URL:\":\"/payment?id=11&pay=result\",\"success URL:\":\"/payment?id=11&pay=success\",\"fail URL:\":\"/payment?id=11&pay=fail\"}' , 11, '".md5($siteName)."', 'fiz'),
(14, 'Яндекс.Касса (HTTP)', 1, '{\"Ссылка для отправки данных\":\"\",\"Идентификатор магазина\":\"\",\"Идентификатор витрины\":\"\",\"shopPassword\":\"\",\"Метод шифрования\":\"".CRYPT::mgCrypt('md5')."\",\"Использовать онлайн кассу\":\"ZmFsc2VoWSo2bms3ISEjMnFq\",\"НДС, включенный в цену\":\"MjAlaFkqNm5rNyEhIzJxag==\"}', '{\"result URL:\":\"/payment?id=14&pay=result\",\"success URL:\":\"/payment?id=14&pay=success\",\"fail URL:\":\"/payment?id=14&pay=fail\"}', 12, '".md5($siteName)."', 'fiz'), 
(15, 'Приват24', 1, '{\"ID мерчанта\":\"\",\"Пароль марчанта\":\"\"}', '', 13, '".md5($siteName)."', 'fiz'),
(16, 'LiqPay', 1, '{\"Публичный ключ\":\"\",\"Приватный ключ\":\"\",\"Тестовый режим\":\"\"}', '', 14, '".md5($siteName)."', 'fiz'),
(17, 'Сбербанк', 1, '{\"API Логин\":\"\",\"Пароль\":\"\",\"Адрес сервера\":\"\",\"Использовать онлайн кассу\":\"ZmFsc2VoWSo2bms3ISEjMnFq\",\"Система налогообложения\":\"MGhZKjZuazchISMycWo=\",\"НДС на товары\":\"M2hZKjZuazchISMycWo=\",\"НДС на доставку\":\"M2hZKjZuazchISMycWo=\"}', '', 15, '".md5($siteName)."', 'fiz'),
(18, 'Тинькофф', 1, '{\"Ключ терминала\":\"\",\"Секретный ключ\":\"\",\"Адрес сервера\":\"\",\"Использовать онлайн кассу\":\"ZmFsc2VoWSo2bms3ISEjMnFq\",\"Система налогообложения\":\"b3NuaFkqNm5rNyEhIzJxag==\",\"НДС на товары\":\"dmF0MjBoWSo2bms3ISEjMnFq\",\"НДС на доставку\":\"dmF0MjBoWSo2bms3ISEjMnFq\",\"Email продавца\":\"\"}', '{\"result URL:\":\"/payment?id=18&pay=result\"}', 16, '".md5($siteName)."', 'fiz'),
(20, 'Comepay: интернет-эквайринг и прием платежей','1','{\"Идентификатор магазина\":\"\",\"Номер магазина\":\"\",\"Пароль магазина\":\"\",\"Callback Password\":\"\",\"Время жизни счета в часах\":\"\",\"Тестовый режим\":\"" . CRYPT::mgCrypt('false') . "\",\"Comepay URL\":\"".CRYPT::mgCrypt('https://actionshop.comepay.ru')."\",\"Comepay test URL\":\"".CRYPT::mgCrypt('https://moneytest.comepay.ru:449') . "\",\"Разрешить печать чеков в ККТ\":\"".CRYPT::mgCrypt('false') . "\",\"НДС на товары\":\"".CRYPT::mgCrypt('3') ."\",\"НДС на доставку\":\"".CRYPT::mgCrypt('3') ."\",\"Признак способа расчёта\":\"".CRYPT::mgCrypt('4') ."\"}', '{\"result URL:\":\"/payment?id=20&pay=result\",\"success URL:\":\"/payment?id=20&pay=success\",\"fail URL:\":\"/payment?id=20&pay=fail\"}', '20', '".md5($siteName)."', 'fiz'),
(21, 'Онлайн оплата (payKeeper)', 1, '{\"Язык страницы оплаты\":\"\",\"ID Магазина\":\"=\",\"Секретный ключ\":\"=\"}', '{\"result URL:\":\"/payment?id=21&pay=result\",\"success URL:\":\"/payment?id=21&pay=success\",\"fail URL:\":\"/payment?id=21&pay=fail\"}', 21, '".md5($siteName)."', 'all'),
(22, 'CloudPayments', 1, '{\"Public ID\":\"\",\"Секретный ключ\":\"\",\"Схема проведения платежа\":\"".CRYPT::mgCrypt('charge')."\",\"Дизайн виджета\":\"".CRYPT::mgCrypt('classic')."\",\"Язык виджета\":\"".CRYPT::mgCrypt('ru-RU')."\",\"Использовать онлайн кассу\":\"\",\"Инн\":\"\",\"Система налогообложения\":\"".CRYPT::mgCrypt('ts_0')."\",\"Ставка НДС\":\"".CRYPT::mgCrypt('vat_20')."\",\"Ставка НДС для доставки\":\"".CRYPT::mgCrypt('vat_20')."\",\"Способ расчета\":\"".CRYPT::mgCrypt('1')."\",\"Предмет расчета\":\"".CRYPT::mgCrypt('1')."\",\"Статус заказа для печати второго чека\":\"".CRYPT::mgCrypt('3')."\"}', '{\"Check URL:\":\"/payment?id=22&pay=result&action=check\",\"Pay URL:\":\"/payment?id=22&pay=result&action=pay\",\"Confirm URL:\":\"/payment?id=22&pay=result&action=confirm\",\"Fail URL:\":\"/payment?id=22&pay=result&action=fail\",\"Refund URL:\":\"/payment?id=22&pay=result&action=refund\",\"Cancel URL:\":\"/payment?id=22&pay=result&action=cancel\"}', 22, '".md5($siteName)."', 'fiz'),
(23, 'Заплатить по частям от Яндекс.Касса', 1, '', '', 23, '".md5($siteName)."', 'fiz'),
(24, 'Яндекс.Касса (API)', 1, '{\"shopid\":\"\",\"api_key\":\"\",\"Использовать онлайн кассу\":\"ZmFsc2VoWSo2bms3ISEjMnFq\",\"НДС, включенный в цену\":\"MjAlaFkqNm5rNyEhIzJxag==\"}', '{\"Check URL:\":\"/payment?id=24\"}', 24, '".md5($siteName)."', 'fiz'),
(25, 'Apple Pay от Яндекс.Касса', 0, '{\"MerchantIdentifier\":\"\",\"MerchantName\":\"\",\"Password\":\"\",\"CertPath\":\"\",\"KeyPath\":\"\"}', '', 25, '".md5($siteName)."', 'fiz'),
(26, 'FREE-KASSA', 1, '{\"Язык страницы оплаты\":\"\", \"ID Магазина\":\"\", \"Секретный ключ1\":\"\", \"Секретный ключ2\":\"\"}', '{\"URL оповещения:\":\"/payment?id=26&pay=result\",\"URL возврата в случае успеха:\":\"/payment?id=26&pay=success\",\"URL возврата в случае неудачи:\":\"/payment?id=26&pay=fail\"}', 26, '".md5($siteName)."', 'fiz'),
(27, 'Мегакасса', 0, '{\"ID магазина\":\"\", \"Секретный ключ\":\"\"}', '{\"result URL:\":\"/payment?id=27&pay=result\",\"success URL:\":\"/payment?id=27&pay=success\",\"fail URL:\":\"/payment?id=27&pay=fail\"}', 27, '".md5($_SERVER['SERVER_NAME'])."', 'fiz'),
(28, 'Qiwi (API)', 1, '{\"Публичный ключ\":\"\", \"Секретный ключ\":\"\"}', '{\"result URL:\":\"/payment?id=28&pay=result\"}', 28, '".md5($_SERVER['SERVER_NAME'])."', 'fiz')",

"INSERT INTO `".$prefix."payment` (`id`, `name`, `activity`, `paramArray`, `urlArray`, `sort`, `add_security`, `permission`) VALUES
(19, 'PayPal', 1, '{\"Токен идентичности\":\"\",\"Email продавца\":\"\",\"Тестовый режим\":\"dHJ1ZWhZKjZuazchISMycWo=\"}', '{\"result URL:\":\"/payment?id=19&pay=result\"}', 19, '".md5($siteName)."', 'fiz')",

"CREATE TABLE IF NOT EXISTS `".$prefix."plugins` (
  `folderName` varchar(249) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `template` TEXT
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",
  
"INSERT INTO `".$prefix."plugins` (`folderName`, `active`) VALUES
('breadcrumbs', 1),
('site-block-editor', 1),
('adaptizator', 1),
('mg-brand', 1),
('comments', 1),
('mg-slider', 1),
('trigger-guarantee', 1),
('in-cart', 1),
('rating', 1)",

"INSERT INTO `".$prefix."plugins` (`folderName`, `active`) VALUES
('daily-product', 1)",

"CREATE TABLE IF NOT EXISTS `".$prefix."product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sort` int(11),
  `cat_id` int(11) NOT NULL,
  `title` varchar(249) NOT NULL,
  `description` longtext ,
  `price` double NOT NULL,
  `url` varchar(249) NOT NULL,
  `image_url` TEXT ,
  `code` varchar(249) NOT NULL,
  `count` int(11) NOT NULL DEFAULT '0',
  `activity` tinyint(1) NOT NULL,
  `meta_title` text,
  `meta_keywords` text,
  `meta_desc` text ,
  `old_price` varchar(249),
  `recommend` tinyint(4) NOT NULL DEFAULT '0',
  `new` tinyint(4) NOT NULL DEFAULT '0',
  `related` text,
  `inside_cat` text ,
  `1c_id` varchar(249) NOT NULL DEFAULT '',
  `weight` double,
  `link_electro` varchar(1024),
  `currency_iso` varchar(249),
  `price_course` double,
  `image_title` text,
  `image_alt` text,
  `yml_sales_notes` text,
  `count_buy` int(11) NOT NULL DEFAULT 0,
  `system_set` INT(11),
  `related_cat` text,
  `short_description` longtext,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `unit` varchar(249) DEFAULT NULL,
  `weight_unit` VARCHAR(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `1c_id` (`1c_id`),
  FULLTEXT KEY `SEARCHPROD` (`title`,`description`,`code`,`meta_title`,`meta_keywords`,`meta_desc`)
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

"CREATE TABLE IF NOT EXISTS `".$prefix."product_variant` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `title_variant` varchar(249) NOT NULL,
  `image` varchar(249),
  `sort` int(11),
  `price` double NOT NULL,
  `old_price` varchar(249) NOT NULL,
  `count` int(11),
  `code` varchar(249),
  `activity` tinyint(1) NOT NULL,
  `weight` double NOT NULL,
  `currency_iso` varchar(249),
  `price_course` double,
  `1c_id` varchar(249),
  `color` varchar(249) NOT NULL,
  `size` varchar(249) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  FULLTEXT KEY `title_variant` (`title_variant`),
  FULLTEXT KEY `code` (`code`)  
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",


"CREATE TABLE IF NOT EXISTS `".$prefix."property` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(249) NOT NULL,
  `type` varchar(249) NOT NULL,
  `default` text,
  `data` text,
  `all_category` tinyint(1),
  `activity` int(1) NOT NULL DEFAULT '0',
  `sort` int(11),
  `filter` tinyint(1) NOT NULL DEFAULT '0',
  `description` TEXT, 
  `type_filter` VARCHAR(32) NULL,
  `1c_id` varchar(249),
  `plugin` varchar(249),
  `unit` VARCHAR(32),
  `group_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

"CREATE TABLE IF NOT EXISTS `".$prefix."property_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(249) NOT NULL,
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

"CREATE TABLE IF NOT EXISTS `".$prefix."setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `option` varchar(249) NOT NULL,
  `value` longtext,
  `active` varchar(1) NOT NULL DEFAULT 'N',
  `name` varchar(249) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE (`option`),
  INDEX (`option`)
) ENGINE=MyISAM  DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

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
('themeBackground', 'bg_7.png', 'N', 'ADMIN_THEM_BG'),
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
('categoryIconHeight', '100', 'Y', ''),
('categoryIconWidth', '100', 'Y', ''),
('waterMark', 'false', 'Y', 'WATERMARK'),
('waterMarkPosition', 'center', 'Y', 'WATERMARKPOSITION'),
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
('cacheMode', 'FILE', 'Y', 'CACHE_MODE'),
('cacheTime', '86400', 'Y', 'CACHE_TIME'),
('cacheHost', '', 'Y', 'CACHE_HOST'),
('cachePort', '', 'Y', 'CACHE_PORT'),
('priceFormat', '1 234,56', 'Y', 'PRICE_FORMAT'),
('horizontMenu', 'false', 'Y', 'HORIZONT_MENU'),
('buttonBuyName', 'Купить', 'Y', 'BUTTON_BUY_NAME'),
('buttonCompareName', 'Сравнить', 'Y', 'BUTTON_COMPARE_NAME'),
('randomProdBlock', 'false', 'Y', 'RANDOM_PROD_BLOCK'),
('buttonMoreName', 'Подробнее', 'Y', 'BUTTON_MORE_NAME'),
('compareCategory', 'true', 'Y', 'COMPARE_CATEGORY'),
('colorScheme', '1', 'Y', 'COLOR_SCHEME'),
('useCaptcha', '', 'Y', 'USE_CAPTCHA'),
('autoRegister', 'true', 'Y', 'AUTO_REGISTER'),
('printFilterResult', 'true', 'Y', 'FILTER_RESULT'),
('dateActivateKey', '0000-00-00 00:00:00', 'N', ''),
('propertyOrder', 'a:16:{s:7:\"nameyur\";s:40:\"ООО \"Интернет-магазин\"\";s:6:\"adress\";s:48:\"г.Москва ул. Тверская, дом 1\";s:3:\"inn\";s:10:\"8805614058\";s:3:\"kpp\";s:9:\"980501000\";s:4:\"ogrn\";s:13:\"7137847078193\";s:4:\"bank\";s:16:\"Сбербанк\";s:3:\"bik\";s:9:\"041012721\";s:2:\"ks\";s:20:\"40702810032030000834\";s:2:\"rs\";s:20:\"30101810600000000957\";s:7:\"general\";s:48:\"Михаил Васильевич Могутов\";s:4:\"sing\";s:0:\"\";s:5:\"stamp\";s:0:\"\";s:3:\"nds\";s:2:\"18\";s:8:\"usedsing\";s:4:\"true\";s:6:\"prefix\";s:3:\"MG_\";s:8:\"currency\";s:34:\"рубль,рубля,рублей\";}', 'N', ''),
('enabledSiteEditor', 'false', 'N', ''),
('lockAuthorization', 'false', 'Y','LOCK_AUTH'),
('orderNumber', 'true','Y', 'ORDER_NUMBER'),
('popupCart', 'true', 'Y', 'POPUP_CART'),
('catalogIndex', 'false', 'Y', 'CATALOG_INDEX'),
('productInSubcat', 'true', 'Y', 'PRODUCT_IN_SUBCAT'),
('copyrightMoguta', 'true', 'Y', 'COPYRIGHT_MOGUTA'),
('picturesCategory', 'true', 'Y', 'PICTURES_CATEGORY'),
('backgroundSite', '', 'Y', 'BACKGROUND_SITE'),
('backgroundColorSite', '', 'Y', 'BACKGROUND_COLOR_SITE'),
('backgroundTextureSite', '', 'Y', 'BACKGROUND_TEXTURE_SITE'),
('backgroundSiteLikeTexture', 'false', 'Y', 'BACKGROUND_SITE_LIKE_TEXTURE'),
('fontSite', '', 'Y', 'FONT_SITE'),
('cacheCssJs', 'false', 'Y', 'CACHE_CSS_JS'),
('categoryImgWidth', 200, 'Y', 'CATEGORY_IMG_WIDTH'),
('categoryImgHeight', 200, 'Y', 'CATEGORY_IMG_HEIGHT'),
('propImgWidth', 50, 'Y', 'PROP_IMG_WIDTH'),
('propImgHeight', 50, 'Y', 'PROP_IMG_HEIGHT'),
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
('weightPropertyName1c', 'Вес', 'Y', 'WEIGHT_NAME_1C'),
('oldPriceName1c', '', 'Y', 'OLD_PRICE_NAME_1C'),
('retailPriceName1c', '', 'Y', 'RETAIL_PRICE_NAME_1C'),
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
('openGraphLogoPath', '', 'Y', 'OPEN_GRAPH_LOGO_PATH'),
('dublinCore', 'true', 'Y', 'DUBLIN_CORE'),
('printSameProdNullRem', 'true', 'Y', 'PRINT_SAME_PROD_NULL_REM'),
('landingName', 'lp-moguta', 'N', ''),
('colorSchemeLanding', 'none', 'N', ''),
('printQuantityInMini', 'false', 'Y', 'SHOW_QUANTITY'),
('printCurrencySelector', 'false', 'Y', 'CURRENCY_SELECTOR'),
('interface', 'a:6:{s:9:\"colorMain\";s:7:\"#2773eb\";s:9:\"colorLink\";s:7:\"#1585cf\";s:9:\"colorSave\";s:7:\"#4caf50\";s:11:\"colorBorder\";s:7:\"#e6e6e6\";s:14:\"colorSecondary\";s:7:\"#ebebeb\";s:8:\"adminBar\";s:9:\"#000000bf\";}', 'Y', ''),
('filterCountProp', '3', 'Y', 'FILTER_COUNT_PROP'),
('filterMode', 'true', 'Y', 'FILTER_MODE'),
('filterCountShow', '5', 'Y', 'FILTER_COUNT_SHOW'),
('filterSubcategory', 'false', 'Y', 'FILTER_SUBCATGORY'),
('printVariantsInMini', 'false', 'Y', 'SHOW_VARIANT_MINI'),
('useReCaptcha', 'false', 'Y', 'USE_RECAPTCHA'),
('invisibleReCaptcha', 'false', 'Y', 'INVISIBLE_RECAPTCHA'),
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
('modParamInVarName', 'true', 'Y', 'MOD_PARAM_IN_VAR_NAME'),
('orderOwners', 'false', 'Y', 'ORDER_OWNERS'),
('ownerRotation', 'false', 'Y', 'OWNER_ROTATION'),
('ownerRotationCurrent', '', 'Y', 'OWNER_ROTATION_CURRENT'),
('ownerList', '', 'Y', 'OWNER_LIST'),
('ownerRemember', 'false', 'Y', 'OWNER_REMBER'),
('ownerRememberPhone', 'false', 'Y', 'OWNER_REMBER_PHONE'),
('ownerRememberEmail', 'false', 'Y', 'OWNER_REMBER_EMAIL'),
('ownerRememberDays', '14', 'Y', 'OWNER_REMBER_DAYS'),
('convertCountToHR', '2:последний товар,5:скоро закончится,15:мало,100:много', 'Y', 'CONVERT_COUNT_TO_HR'),
('blockEntity', 'false', 'Y', 'BLOCK_ENTITY'),
('useFavorites', 'true', 'Y', 'USE_FAVORITES'),
('countPrintRowsBrand', '10', 'Y', ''),
('varHashProduct', 'true', 'Y', 'VAR_HASH_PRODUCT'),
('useSearchEngineInfo', 'false', 'Y', 'USE_SEARCH_ENGINE_INFO'),
('timezone', 'noChange', 'Y', 'TIMEZONE'),
('recalcWholesale', 'true', 'Y', 'RECALC_WHOLESALE'),
('printOneColor', 'false', 'Y', 'PRINT_ONE_COLOR'),
('updateStringProp1C', 'false', 'Y', 'UPDATE_STRING_PROP_1C'),
('weightUnit1C', 'kg', 'Y', 'WEIGHT_UNIT_1C'),
('writeLog1C', 'false', 'Y', 'WRITE_LOG_1C'),
('writeFiles1C', 'false', 'Y', 'WRITE_FILES_1C'),
('writeFullName1C', 'false', 'Y', 'WRITE_FULL_NAME_1C'),
('activityCategory1C', 'true', 'Y', 'ACTIVITY_CATEGORY_1C'),
('notUpdate1C', 'a:11:{s:8:\"1c_title\";s:4:\"true\";s:7:\"1c_code\";s:4:\"true\";s:6:\"1c_url\";s:4:\"true\";s:9:\"1c_weight\";s:4:\"true\";s:8:\"1c_count\";s:4:\"true\";s:14:\"1c_description\";s:4:\"true\";s:12:\"1c_image_url\";s:4:\"true\";s:13:\"1c_meta_title\";s:4:\"true\";s:16:\"1c_meta_keywords\";s:4:\"true\";s:11:\"1c_activity\";s:4:\"true\";s:12:\"1c_old_price\";s:4:\"true\";}', 'y', 'update_1c'),
('listMatch1C', 'a:7:{i:0;s:27:\"не подтвержден\";i:1;s:27:\"ожидает оплаты\";i:2;s:14:\"оплачен\";i:3;s:19:\"в доставке\";i:4;s:14:\"отменен\";i:5;s:16:\"выполнен\";i:6;s:21:\"в обработке\";}', 'Y', 'UPDATE_STATUS_1C'),
('product404', 'false', 'Y', ''),
('product404Sitemap', 'false', 'Y', ''),
('productFilterPriceSliderStep', '10', 'Y', 'PRODUCT_FILTER_PRICE_SLIDER_STEP'),
('catalogColumns', 'a:6:{i:0;s:6:\"number\";i:1;s:8:\"category\";i:2;s:3:\"img\";i:3;s:5:\"title\";i:4;s:5:\"price\";i:5;s:5:\"count\";}', 'Y', ''),
('orderColumns', 'a:9:{i:0;s:2:\"id\";i:1;s:6:\"number\";i:2;s:4:\"date\";i:3;s:3:\"fio\";i:4;s:5:\"email\";i:5;s:4:\"summ\";i:6;s:5:\"deliv\";i:7;s:7:\"payment\";i:8;s:6:\"status\";}', 'Y', ''),
('userColumns', 'a:5:{i:0;s:5:\"email\";i:1;s:6:\"status\";i:2;s:5:\"group\";i:3;s:8:\"register\";i:4;s:8:\"personal\";}', 'Y', ''),
('useNameParts', 'false', 'Y', 'ORDER_NAME_PARTS'),
('searchInDefaultLang', 'true', 'Y', 'SEARCH_IN_DEFAULT_LANG'),
('backupBeforeUpdate', 'true', 'Y', 'BACKUP_BEFORE_UPDATE'),
('rememberLogins', 'true', 'Y', 'REMEMBER_LOGINS'),
('rememberLoginsDays', '180', 'Y', 'REMEMBER_LOGINS_DAYS'),
('loginBlockTime', '15', 'Y', 'LOGIN_BLOCK_TIME'),
('printMultiLangSelector', 'true', 'Y', ''),
('imageResizeRetina', 'false', 'Y', 'IMAGE_RESIZE_RETINA'),
('timeWorkDays', 'Пн-Пт:,Сб-Вс:', 'Y', '')
",

"INSERT IGNORE INTO `".$prefix."setting` (`option`, `value`, `active`, `name`) VALUES
('catalog_meta_title', 'Купить {titeCategory} в Москве', 'N', ''),
('catalog_meta_description', '{cat_desc,160}', 'N', ''),
('catalog_meta_keywords', '{meta_keywords}', 'N', ''),
('product_meta_title', 'Купить {title} в Москве', 'N', ''),
('product_meta_description', 'Акция! {title} за {price} {currency} купить в Москве. {description,100}', 'N', ''),
('product_meta_keywords', '{%title}', 'N', ''),
('page_meta_title', '{title}', 'N', ''),
('page_meta_description', '{html_content,160}', 'N', ''),
('page_meta_keywords', '{meta_keywords}', 'N', ''),
('currencyRate', 'a:6:{s:3:\"RUR\";d:1;s:3:\"UAH\";d:2.03991;s:3:\"USD\";d:56.6278;s:3:\"EUR\";d:70.4959;s:3:\"KZT\";d:0.1757;s:3:\"UZS\";d:0.006926;}', 'Y', 'CUR_SHOP_RATE')
",



'INSERT IGNORE INTO `'.$prefix.'setting` (`option`, `value`, `active`) VALUES
(\'orderFormFields\', \'a:14:{s:5:\"email\";a:4:{s:6:\"active\";s:1:\"1\";s:4:\"sort\";s:1:\"1\";s:8:\"required\";s:1:\"1\";s:13:\"conditionType\";s:6:\"always\";}s:5:\"phone\";a:6:{s:6:\"active\";s:1:\"1\";s:4:\"sort\";s:1:\"2\";s:8:\"required\";s:1:\"1\";s:13:\"conditionType\";s:6:\"always\";s:10:\"conditions\";N;s:4:\"type\";s:5:\"input\";}s:3:\"fio\";a:4:{s:6:\"active\";s:1:\"1\";s:4:\"sort\";s:1:\"3\";s:8:\"required\";s:1:\"0\";s:13:\"conditionType\";s:6:\"always\";}s:7:\"address\";a:6:{s:6:\"active\";s:1:\"1\";s:4:\"sort\";s:1:\"4\";s:8:\"required\";s:1:\"0\";s:13:\"conditionType\";s:5:\"ifAll\";s:10:\"conditions\";a:1:{i:0;a:2:{s:4:\"type\";s:8:\"delivery\";s:5:\"value\";a:3:{i:0;s:1:\"0\";i:1;s:1:\"1\";i:2;s:1:\"2\";}}}s:4:\"type\";s:5:\"input\";}s:4:\"info\";a:5:{s:6:\"active\";s:1:\"1\";s:4:\"sort\";s:1:\"5\";s:8:\"required\";s:1:\"0\";s:13:\"conditionType\";s:6:\"always\";s:10:\"conditions\";N;}s:8:\"customer\";a:4:{s:6:\"active\";s:1:\"1\";s:4:\"sort\";s:1:\"6\";s:8:\"required\";s:1:\"0\";s:13:\"conditionType\";s:6:\"always\";}s:16:\"yur_info_nameyur\";a:5:{s:6:\"active\";s:1:\"1\";s:4:\"sort\";s:1:\"7\";s:8:\"required\";s:1:\"0\";s:13:\"conditionType\";s:5:\"ifAll\";s:10:\"conditions\";a:1:{i:0;a:4:{s:4:\"type\";s:10:\"orderField\";s:9:\"fieldName\";s:8:\"customer\";s:9:\"fieldType\";s:6:\"select\";s:5:\"value\";a:1:{i:0;s:3:\"yur\";}}}}s:15:\"yur_info_adress\";a:5:{s:6:\"active\";s:1:\"1\";s:4:\"sort\";s:1:\"8\";s:8:\"required\";s:1:\"0\";s:13:\"conditionType\";s:5:\"ifAll\";s:10:\"conditions\";a:1:{i:0;a:4:{s:4:\"type\";s:10:\"orderField\";s:9:\"fieldName\";s:8:\"customer\";s:9:\"fieldType\";s:6:\"select\";s:5:\"value\";a:1:{i:0;s:3:\"yur\";}}}}s:12:\"yur_info_inn\";a:5:{s:6:\"active\";s:1:\"1\";s:4:\"sort\";s:1:\"9\";s:8:\"required\";s:1:\"0\";s:13:\"conditionType\";s:5:\"ifAll\";s:10:\"conditions\";a:1:{i:0;a:4:{s:4:\"type\";s:10:\"orderField\";s:9:\"fieldName\";s:8:\"customer\";s:9:\"fieldType\";s:6:\"select\";s:5:\"value\";a:1:{i:0;s:3:\"yur\";}}}}s:12:\"yur_info_kpp\";a:5:{s:6:\"active\";s:1:\"1\";s:4:\"sort\";s:2:\"10\";s:8:\"required\";s:1:\"0\";s:13:\"conditionType\";s:5:\"ifAll\";s:10:\"conditions\";a:1:{i:0;a:4:{s:4:\"type\";s:10:\"orderField\";s:9:\"fieldName\";s:8:\"customer\";s:9:\"fieldType\";s:6:\"select\";s:5:\"value\";a:1:{i:0;s:3:\"yur\";}}}}s:13:\"yur_info_bank\";a:5:{s:6:\"active\";s:1:\"1\";s:4:\"sort\";s:2:\"11\";s:8:\"required\";s:1:\"0\";s:13:\"conditionType\";s:5:\"ifAll\";s:10:\"conditions\";a:1:{i:0;a:4:{s:4:\"type\";s:10:\"orderField\";s:9:\"fieldName\";s:8:\"customer\";s:9:\"fieldType\";s:6:\"select\";s:5:\"value\";a:1:{i:0;s:3:\"yur\";}}}}s:12:\"yur_info_bik\";a:5:{s:6:\"active\";s:1:\"1\";s:4:\"sort\";s:2:\"12\";s:8:\"required\";s:1:\"0\";s:13:\"conditionType\";s:5:\"ifAll\";s:10:\"conditions\";a:1:{i:0;a:4:{s:4:\"type\";s:10:\"orderField\";s:9:\"fieldName\";s:8:\"customer\";s:9:\"fieldType\";s:6:\"select\";s:5:\"value\";a:1:{i:0;s:3:\"yur\";}}}}s:11:\"yur_info_ks\";a:5:{s:6:\"active\";s:1:\"1\";s:4:\"sort\";s:2:\"13\";s:8:\"required\";s:1:\"0\";s:13:\"conditionType\";s:5:\"ifAll\";s:10:\"conditions\";a:1:{i:0;a:4:{s:4:\"type\";s:10:\"orderField\";s:9:\"fieldName\";s:8:\"customer\";s:9:\"fieldType\";s:6:\"select\";s:5:\"value\";a:1:{i:0;s:3:\"yur\";}}}}s:11:\"yur_info_rs\";a:5:{s:6:\"active\";s:1:\"1\";s:4:\"sort\";s:2:\"14\";s:8:\"required\";s:1:\"0\";s:13:\"conditionType\";s:5:\"ifAll\";s:10:\"conditions\";a:1:{i:0;a:4:{s:4:\"type\";s:10:\"orderField\";s:9:\"fieldName\";s:8:\"customer\";s:9:\"fieldType\";s:6:\"select\";s:5:\"value\";a:1:{i:0;s:3:\"yur\";}}}}}\', \'Y\')',

"INSERT INTO `".$prefix."setting` (`option`, `value`) VALUES ('lastModVersion', '9.2.0')",


"CREATE TABLE IF NOT EXISTS `".$prefix."mg-brand` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Порядковый номер записи',
  `data_id` int(11) NOT NULL COMMENT 'Номер в таблице характеристик',
  `brand` text NOT NULL COMMENT 'Бренд',
  `url` text NOT NULL COMMENT 'Логотип',
  `img_alt` text NOT NULL COMMENT 'alt',
  `img_title` text NOT NULL COMMENT 'title',
  `desc` text NOT NULL COMMENT 'Описание',
  `short_url` text NOT NULL COMMENT 'Короткая ссылка',
  `full_url` text NOT NULL COMMENT 'Полная ссылка',
  `add_datetime` DATETIME NOT NULL COMMENT 'Дата добавления',
  `seo_title` text NOT NULL COMMENT '(SEO) Название',
  `seo_keywords` text NOT NULL COMMENT '(SEO) Ключевые слова',
  `seo_desc` text NOT NULL COMMENT '(SEO) Описание',
  `cat_desc_seo` text NOT NULL COMMENT 'Описание для SEO',
  `invisible` int(1) NOT NULL COMMENT 'Видимость',
  `sort` int(11) NOT NULL COMMENT 'Сортировка',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

"CREATE TABLE IF NOT EXISTS `".$prefix."trigger-guarantee` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Порядковый номер',
  `title` text NOT NULL COMMENT 'Загаловок',
  `settings` text NOT NULL COMMENT 'Настройки',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

"CREATE TABLE IF NOT EXISTS `".$prefix."trigger-guarantee-elements` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Порядковый номер',
  `parent` int(11) NOT NULL COMMENT 'id блока',
  `text` text NOT NULL COMMENT 'Текст триггера',
  `icon` text NOT NULL COMMENT 'Иконка или url картинки',
  `sort` int(11) NOT NULL COMMENT 'Сортировка',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

"CREATE TABLE IF NOT EXISTS `".$prefix."comments` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `name` VARCHAR(45) NOT NULL,
  `email` VARCHAR(45) NOT NULL,
  `comment` TEXT NoT NULL,
  `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `uri` varchar(249) NOT NULL,
  `approved` TINYINT NOT NULL DEFAULT 0, 
  `img` text NOT NULL,
  PRIMARY KEY(`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

"CREATE TABLE IF NOT EXISTS `".$prefix."user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner` int(11) NOT NULL DEFAULT 0,
  `email` varchar(249) DEFAULT NULL,
  `pass` varchar(249) DEFAULT NULL,
  `role` int(11) DEFAULT NULL,
  `name` varchar(249) DEFAULT NULL,
  `sname` varchar(249) DEFAULT NULL,
  `pname` varchar(249) DEFAULT NULL,
  `address` text,
  `address_index` TEXT DEFAULT NULL,
  `address_country` TEXT DEFAULT NULL,
  `address_region` TEXT DEFAULT NULL,
  `address_city` TEXT DEFAULT NULL,
  `address_street` TEXT DEFAULT NULL,
  `address_house` TEXT DEFAULT NULL,
  `address_flat` TEXT DEFAULT NULL,
  `phone` varchar(249) DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_add` timestamp NULL DEFAULT NULL,
  `blocked` int(1) NOT NULL DEFAULT '0',
  `restore` varchar(249) DEFAULT NULL,
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
  `op` TEXT COMMENT 'Дополнительные поля',
  `fails` TINYTEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",  
  
//Таблица, для хранения ссылок на страницы выборок фильтров
"CREATE TABLE IF NOT EXISTS `".$prefix."url_rewrite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` TEXT NOT NULL,
  `short_url` varchar(249) NOT NULL,
  `titeCategory` varchar(249) DEFAULT NULL,
  `cat_desc` longtext NOT NULL,
  `meta_title` text NOT NULL,
  `meta_keywords` text NOT NULL,
  `meta_desc` text NOT NULL,
  `activity` tinyint(1) NOT NULL DEFAULT 1,
  `cat_desc_seo` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",
  
//Таблица для записей редиректов
"CREATE TABLE IF NOT EXISTS `".$prefix."url_redirect` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url_old` TEXT NOT NULL,
  `url_new` TEXT NOT NULL,
  `code` int(3) NOT NULL,
  `activity` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",
 
 //Таблица, для хранения сессий
"CREATE TABLE `".$prefix."sessions` ( 
  `session_id` varchar(249) binary NOT NULL default '', 
  `session_expires` int(11) unsigned NOT NULL default '0', 
  `session_data` longtext, 
  PRIMARY KEY  (`session_id`) 
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

// для плагинов по умолячанию
// слайдер

"CREATE TABLE IF NOT EXISTS `".$prefix."mg-slider` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Порядковый номер слайдера',
  `name_slider` text NOT NULL COMMENT 'Название слайдера',
  `slides` text NOT NULL COMMENT 'Содержимое слайдов', 
  `options` text NOT NULL COMMENT 'Настройки сладера',
  `invisible` int(1) NOT NULL DEFAULT '0' COMMENT 'видимость',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

// для редактора блоков
"CREATE TABLE IF NOT EXISTS `".$prefix."site-block-editor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment` text NOT NULL,
  `type` varchar(249) NOT NULL,
  `content` text NOT NULL,
  `width` text NOT NULL, 
  `height` text NOT NULL,      
  `alt` text NOT NULL,
  `title` text NOT NULL,
  `href` text NOT NULL,
  `class` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",

"CREATE TABLE IF NOT EXISTS `".$prefix."product_rating` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Порядковый номер записи',
  `id_product` int(11) NOT NULL COMMENT 'ID товара',
  `rating` double NOT NULL COMMENT 'Оценка',
  `count` int(11) NOT NULL COMMENT 'Количество голосов',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=".$encoding." COLLATE=".$encoding."_general_ci;",
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

// Для шаблонов писем
"CREATE TABLE IF NOT EXISTS `".$prefix."letters` (
  id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  content mediumtext NOT NULL,
  `lang` varchar(50) DEFAULT 'default',
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

"INSERT INTO `".$prefix."letters` (id, name, content) VALUES
    (1, 'email_feedback.php', '<h1 style=\'margin: 0 0 10px 0; font-size: 16px;padding: 0;\'>Сообщение с формы обратной связи!</h1><p style=\'padding: 0;margin: 10px 0;font-size: 12px;\'>Пользователь <strong>{userName}</strong> с почтовым ящиком <strong>{userEmail}</strong> пишет:</p><div style=\'margin: 0;padding: 10px;background: #FFF5B5; font-weight: bold;\'>{message}</div>'),
    (2, 'email_forgot.php', '<h1 style=\"margin: 0 0 10px 0; font-size: 16px;padding: 0;\">Здравствуйте!</h1><p style=\"padding: 0;margin: 10px 0;font-size: 12px;\">Вы зарегистрированы на сайте <strong>{siteName}</strong> с логином <strong>{userEmail}</strong></p><p style=\"padding: 0;margin: 10px 0;font-size: 12px;\">Для восстановления пароля пройдите по ссылке</p><div style=\"margin: 0;padding: 10px;background: #FFF5B5; font-weight: bold; text-align: center;\"><a href=\"{link}\" target=\"blank\"> {link} </a></div><p style=\"padding: 0;margin: 10px 0;font-size: 12px;\">Если Вы не делали запрос на восстановление пароля, то проигнорируйте это письмо.</p><p style=\"padding: 0;margin: 10px 0;font-size: 10px; color: #555; font-weight: bold;\">Отвечать на данное сообщение не нужно.</p>'),
    (3, 'email_order.php', '<table bgcolor=\"#FFFFFF\" cellspacing=\"0\" cellpadding=\"10\" border=\"0\" width=\"675\"><tbody><tr><td valign=\"top\"><h1 style=\"margin: 0 0 10px 0; font-size: 16px;padding: 0;\">Здравствуйте, {fullName}!</h1><div style=\"font-size:12px;line-height:16px;margin:0;\">Ваш заказ <b>№{orderNumber}</b> успешно оформлен.<p class=\"confirm-info\" style=\"font-size:12px;margin:0 0 10px 0\"><br>Перейдите по {confirmLink} для подтверждения заказа и создания личного кабинета<br><br>Следить за статусом заказа вы можете в <a href=\"{personal}\" style=\"color:#1E7EC8;\" target=\"_blank\">личном кабинете</a>.</p><br>Если у Вас возникнут вопросы — их можно задать по почте: <a href=\"mailto:{adminEmail}\" style=\"color:#1E7EC8;\" target=\"_blank\">{adminEmail}</a> или по телефону: <span><span class=\"js-phone-number highlight-phone\">{shopPhone}</span></span></div></td></tr></tbody></table>{tableOrder}'),
    (4, 'email_order_change_status.php', '<p style=\"font-size:12px;line-height:16px;margin:0;\">Здравствуйте, <b>{buyerName}</b>!<br/> Статус Вашего заказа <b>№{orderInfo}</b> был изменен c \"<b>{oldStatus}</b>\" на \"<b>{newStatus}</b>\".<br/>{managerComment}<br/>Следить за состоянием заказа Вы можете в <a href=\"<?php echo SITE.\'/personal\'?>\">личном кабинете</a>.</p>'),
    (5, 'email_order_electro.php', '<p style=\"font-size:12px;line-height:16px;margin:0;\">Ваш заказ <b>№{orderNumber}</b> содержит электронные товары, которые можно скачать по следующей ссылке:<br/> <a href=\"{getElectro}\">{getElectro}</a></p>'),
    (6, 'email_order_new_user.php', '<table bgcolor=\"#FFFFFF\" cellspacing=\"0\" cellpadding=\"10\" border=\"0\" width=\"675\"><tbody><tr><td valign=\"top\"><h1 style=\"margin: 0 0 10px 0; font-size: 16px;padding: 0;\">Здравствуйте, {fullName}!</h1><div style=\"font-size:12px;line-height:16px;margin:0;\"><br>Мы создали для вас <a href=\"<?php echo SITE ?>/personal\" style=\"color:#1E7EC8;\" target=\"_blank\">личный кабинет</a>, чтобы вы могли следить за статусом заказа, а также скачивать оплаченные электронные товары.<br><br><b>Ваш логин:</b> {userEmail}<br><b>Ваш пароль:</b> {pass}</div></td></tr></tbody></table>'),
    (7, 'email_order_paid.php', '<p style=\"font-size:12px;line-height:16px;margin:0;\">Вы получили это письмо, так как произведена оплата заказа №{orderNumber} на сумму {summ}. Оплата произведена при помощи {payment} <br/>Статус заказа сменен на \"{status} \"</p>'),
    (8, 'email_order_status.php', '<p style=\"font-size:12px;line-height:16px;margin:0;\">Здравствуйте,  <b>{buyerName}</b>!<br/> Статус Вашего заказа <b>№{orderInfo}</b> был изменен c \"<b>{oldStatus}</b>\" на \"<b>{newStatus}</b>\".<br/> Следить за состоянием заказа Вы можете в <a href=\"<?php echo SITE.\'/personal\'?>\">личном кабинете</a>.</p>'),
    (9, 'email_registry.php', '<h1 style=\"margin: 0 0 10px 0; font-size: 16px;padding: 0;\">Здравствуйте!</h1><p style=\"padding: 0;margin: 10px 0;font-size: 12px;\">Вы получили данное письмо так как зарегистрировались на сайте <strong>{siteName}</strong> с логином <strong>{userEmail}</strong></p><p style=\"padding: 0;margin: 10px 0;font-size: 12px;\">Для активации пользователя и возможности пользоваться личным кабинетом пройдите по ссылке:</p><div style=\"margin: 0;padding: 10px;background: #FFF5B5; font-weight: bold; text-align: center;\">{link}</div><p style=\"padding: 0;margin: 10px 0;font-size: 10px; color: #555; font-weight: bold;\">Отвечать на данное сообщение не нужно.</p>'),
    (10, 'email_registry_independent.php', '<h1 style=\"margin: 0 0 10px 0; font-size: 16px;padding: 0;\">Здравствуйте!</h1><p style=\"padding: 0;margin: 10px 0;font-size: 12px;\">Вы получили данное письмо так как на сайте <strong>{siteName} </strong> зарегистрирован новый пользователь с логином <strong>{userEmail}</strong></p><p style=\"padding: 0;margin: 10px 0;font-size: 10px; color: #555; font-weight: bold;\">Отвечать на данное сообщение не нужно.</p>'),
    (11, 'email_unclockauth.php', '<h1 style=\"margin: 0 0 10px 0; font-size: 16px;padding: 0;\">Подбор паролей на сайте {siteName} предотвращен!</h1><p style=\"padding: 0;margin: 10px 0;font-size: 12px;\">Система защиты от перебора паролей для авторизации зафиксировала активность. С IP адреса {IP} было введено более 5 неверных паролей. Последний email: <strong>{lastEmail}</strong> Пользователь вновь сможет ввести пароль через 15 минут.</p><p style=\"padding: 0;margin: 10px 0;font-size: 12px;\">Если 5 неправильных попыток авторизации были инициированы администратором,то для снятия блокировки перейдите по ссылке</p><div style=\"margin: 0;padding: 10px;background: #FFF5B5; font-weight: bold; text-align: center;\">{link}</div><p style=\"padding: 0;margin: 10px 0;font-size: 10px; color: #555; font-weight: bold;\">Отвечать на данное сообщение не нужно.</p>')
    ",
);
