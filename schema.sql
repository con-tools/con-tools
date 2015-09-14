CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `name` VARCHAR(45) NOT NULL COMMENT '',
  `email` VARCHAR(45) NOT NULL COMMENT '',
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
  `descriptor` VARCHAR(255) NOT NULL COMMENT '',
  `content_type` VARCHAR(40) NOT NULL COMMENT 'MIME content-type. use application/php for PHP serialized content.',
  `data` MEDIUMBLOB NOT NULL COMMENT 'schemaless data, format depends on content_type',
  PRIMARY KEY (`id`) COMMENT '',
  FOREIGN KEY `fk_user_id` (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY `fk_convention_id` (`convention_id`) REFERENCES `conventions` (`id`) ON DELETE CASCADE
) ENGINE=INNODB CHARACTER SET UTF8;

insert into conventions (name) values ("ביגור 16") on duplicate key update name = name;
insert into api_keys (convention_id, client_key, client_secret) values (2, "M2UyZjJlNzE2M2RkYmVkZWZiYjkzZDRiZGJmOGVlNzM1YjBlN2ZkNQ", "123456");
