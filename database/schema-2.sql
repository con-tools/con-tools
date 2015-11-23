ALTER TABLE `conventions` DROP INDEX `name_UNIQUE`;
ALTER TABLE `conventions` CHANGE COLUMN `name` `title` VARCHAR(255) NOT NULL;
ALTER TABLE `conventions` ADD UNIQUE INDEX `title_UNIQUE` (`title`)  COMMENT '';
ALTER TABLE `conventions` ADD COLUMN `slug` VARCHAR(50) DEFAULT '' COMMENT '' AFTER `id`;
UPDATE `conventions` SET slug = REPLACE(title, ' ', '-');
ALTER TABLE `conventions` CHANGE COLUMN `slug` `slug` VARCHAR(50) NOT NULL COMMENT '';
ALTER TABLE `conventions` ADD COLUMN (
	`series` VARCHAR(50) DEFAULT '' COMMENT '',
	`website` VARCHAR(255) DEFAULT '' COMMENT '',
	`location` TEXT COMMENT '',
	`start_date` DATETIME DEFAULT NULL COMMENT '',
	`end_date` DATETIME DEFAULT NULL COMMENT ''
	);

CREATE TABLE `roles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `key` VARCHAR(20) NOT NULL COMMENT '',
  `title` VARCHAR(20) NOT NULL COMMENT '',
  PRIMARY KEY (`id`)
) ENGINE=INNODB DEFAULT CHARACTER SET UTF8;
	
UPDATE `system_settings` SET `value` = '2' WHERE `name` = 'data-version';
