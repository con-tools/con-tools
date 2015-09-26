DROP TABLE IF EXISTS `api_sessions`;
DROP TABLE IF EXISTS `api_keys`;
DROP TABLE IF EXISTS `user_records`;
DROP TABLE IF EXISTS `conventions`;
DROP TABLE IF EXISTS `tokens`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `api_sessions` (
  `session_id` varchar(24) NOT NULL,
  `last_active` int(10) unsigned NOT NULL,
  `contents` text NOT NULL,
  PRIMARY KEY (`session_id`),
  KEY `last_active` (`last_active`)
) ENGINE=MyISAM DEFAULT CHARACTER SET UTF8;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `name` VARCHAR(45) NOT NULL COMMENT '',
  `email` VARCHAR(45) NOT NULL COMMENT '',
  `phone` VARCHAR(15) DEFAULT NULL COMMENT '',
  `date_of_birth` DATETIME DEFAULT NULL COMMENT '',
  `provider` VARCHAR(20) NOT NULL COMMENT '',
  `password` VARCHAR(255) NULL COMMENT '',
  `created_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '',
  `login_time` DATETIME DEFAULT NULL COMMENT '',
  PRIMARY KEY (`id`)  COMMENT '',
  UNIQUE INDEX `email_UNIQUE` (`email` ASC)  COMMENT ''
) ENGINE=INNODB CHARACTER SET UTF8;

CREATE TABLE IF NOT EXISTS `tokens` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `user_id` INT UNSIGNED NOT NULL COMMENT '',
  `type` CHAR(10) NOT NULL COMMENT 'valid values: api-login, remember',
  `token` VARCHAR(255) NOT NULL COMMENT 'token data',
  `expiry` INT UNSIGNED NOT NULL COMMENT 'how many seconds until this token expires',
  `created_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '',
  `access_time` DATETIME DEFAULT NULL COMMENT '',
  PRIMARY KEY (`id`) COMMENT '',
  UNIQUE INDEX `token_UNIQUE` (`token`)  COMMENT '',
  FOREIGN KEY `fk_user_id` (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=INNODB CHARACTER SET UTF8;

CREATE TABLE IF NOT EXISTS `conventions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `name` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`) COMMENT '',
  UNIQUE INDEX `name_UNIQUE` (`name`)  COMMENT ''
) ENGINE=INNODB CHARACTER SET UTF8;

CREATE TABLE IF NOT EXISTS `api_keys` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `convention_id` INT UNSIGNED NOT NULL COMMENT '',
  `client_key` VARCHAR(255) NOT NULL COMMENT '',
  `client_secret` VARCHAR(255) NOT NULL COMMENT '',
  PRIMARY KEY (`id`) COMMENT '',
  UNIQUE INDEX `client_key_UNIQUE` (`client_key`)  COMMENT '',
  FOREIGN KEY `fk_convention_id` (`convention_id`) REFERENCES `conventions` (`id`) ON DELETE CASCADE
) ENGINE=INNODB CHARACTER SET UTF8;

CREATE TABLE IF NOT EXISTS `user_records` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `user_id` INT UNSIGNED NOT NULL COMMENT '',
  `convention_id` INT UNSIGNED NOT NULL COMMENT '',
  `created_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '',
  `descriptor` VARCHAR(255) NOT NULL COMMENT '',
  `content_type` VARCHAR(40) NOT NULL COMMENT 'MIME content-type. use application/php for PHP serialized content.',
  `data` MEDIUMBLOB NOT NULL COMMENT 'schemaless data, format depends on content_type',
  PRIMARY KEY (`id`) COMMENT '',
  FOREIGN KEY `fk_user_id` (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY `fk_convention_id` (`convention_id`) REFERENCES `conventions` (`id`) ON DELETE CASCADE
) ENGINE=INNODB CHARACTER SET UTF8;

INSERT INTO `users` VALUES (1,'Oded Arbel','oded@geek.co.il',NULL,NULL,'google','ya29.-gFU-NwzgPW_bRzY8mnpOmNmhABPvpTSvMmkIItZtEP9hqp0jMe4BfuDJs0LlCdVHRSdlg','2015-09-14 10:55:35','2015-09-14 05:55:35'),
(2,'יובל - יושי בוטבול','yoshi.yuval@gmail.com',NULL,NULL,'google','ya29.-gGnFJRy0zexsobehkDLgD2G5X5QcYGF71VUscHIzasdyOH3cKWXKC5AfvSIqgbXoOMZ2g','2015-09-16 07:01:54','2015-09-16 02:01:54'),
(3,'הילה גרגורי','hilagrgory@gmail.com',NULL,NULL,'google','ya29.-gHCJZ8A9UWeLnaxdQfs4vqSZYf3b9XSGK069k044s1Ff57osMBeETW7kEilQpaLToTy','2015-09-26 09:26:59','2015-09-26 04:26:59');

INSERT INTO `conventions` VALUES (1,'ביגור 16');

INSERT INTO `api_keys` VALUES (1,1,'M2UyZjJlNzE2M2RkYmVkZWZiYjkzZDRiZGJmOGVlNzM1YjBlN2ZkNQ','123456');
