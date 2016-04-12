
ALTER TABLE `coupons` ADD COLUMN `created_time` DATETIME NOT NULL COMMENT '';
ALTER TABLE `coupons` ADD COLUMN `reason` VARCHAR(50) NOT NULL COMMENT 'Reason for generating this coupon';

UPDATE `system_settings` SET `value` = '13' WHERE `name` = 'data-version' and `id` > 0;
