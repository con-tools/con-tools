
ALTER TABLE `coupons` DROP FOREIGN KEY `coupons_ibfk_3`;
ALTER TABLE `coupons` CHANGE COLUMN `sale_id` `ticket_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT '' ;
ALTER TABLE `coupons` ADD CONSTRAINT `coupons_ibfk_3` FOREIGN KEY (`ticket_id`)  REFERENCES `tickets` (`id`) ON DELETE RESTRICT;

UPDATE `system_settings` SET `value` = '9' WHERE `name` = 'data-version' and `id` > 0;
