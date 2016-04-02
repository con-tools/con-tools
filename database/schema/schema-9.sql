
ALTER TABLE `coupons` DROP FOREIGN KEY `coupons_ibfk_3`;
ALTER TABLE `coupons` CHANGE COLUMN `sale_id` `sale_id` INT(10) UNSIGNED NULL COMMENT '' ;
ALTER TABLE `coupons` ADD CONSTRAINT `coupons_ibfk_3` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE RESTRICT;

UPDATE `system_settings` SET `value` = '9' WHERE `name` = 'data-version' and `id` > 0;
