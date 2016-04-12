
ALTER TABLE `tickets` CHANGE COLUMN `status` `status` ENUM('reserved', 'processing', 'authorized', 'cancelled', 'refunded') NULL COMMENT '';

UPDATE `system_settings` SET `value` = '12' WHERE `name` = 'data-version' and `id` > 0;
