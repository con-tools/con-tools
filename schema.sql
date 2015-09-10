CREATE TABLE IF NOT EXISTS `users` (
  `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `name` VARCHAR(45) NOT NULL COMMENT '',
  `email` VARCHAR(45) NOT NULL COMMENT '',
  `provider` VARCHAR(20) NOT NULL COMMENT '',
  `password` VARCHAR(45) NULL COMMENT '',
  `created_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '',
  `login_time` DATETIME DEFAULT NULL COMMENT '',
  PRIMARY KEY (`user_id`)  COMMENT '',
  UNIQUE INDEX `email_UNIQUE` (`email` ASC)  COMMENT ''
) ENGINE=INNODB CHARACTER SET UTF8;

CREATE TABLE IF NOT EXISTS `tokens` (
  `token_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `user_id` INT UNSIGNED NOT NULL COMMENT '',
  `type` CHAR(10) NOT NULL COMMENT 'valid values: api-login, remember',
  `token` VARCHAR(255) NOT NULL COMMENT 'token data',
  PRIMARY KEY (`token_id`) COMMENT '',
  FOREIGN KEY `fk_user_id` (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=INNODB CHARACTER SET UTF8;

CREATE TABLE IF NOT EXISTS `user_data` (
  `form_id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `user_id` INT UNSIGNED NOT NULL COMMENT '',
  `content_type` VARCHAR(40) NOT NULL COMMENT 'MIME content-type. use application/php for PHP serialized content.',
  `data` MEDIUMBLOB NOT NULL COMMENT 'schemaless data, format depends on content_type',
  PRIMARY KEY (`form_id`) COMMENT '',
  FOREIGN KEY `fk_user_id` (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=INNODB CHARACTER SET UTF8;
