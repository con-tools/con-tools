
CREATE TABLE IF NOT EXISTS `tickets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `user_id` INT UNSIGNED NOT NULL COMMENT '',
  `timeslot_id` INT UNSIGNED NOT NULL COMMENT '',
  `sale_id` INT UNSIGNED DEFAULT NULL COMMENT 'sale where this ticket was fulfilled',
  `amount` INT UNSIGNED NOT NULL COMMENT '',
  `status` ENUM ('reserved', 'processing', 'authorized', 'cancelled'),
  PRIMARY KEY (`id`)  COMMENT '',
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `timeslots_ibfk_1` FOREIGN KEY (`timeslot_id`) REFERENCES `timeslots` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=INNODB CHARACTER SET UTF8;

ALTER TABLE `conventions` ADD COLUMN `settings` TEXT DEFAULT NULL COMMENT 'json encoded convention specific settings document';
UPDATE `conventions` SET `settings` = '{"payment-processor":{"type":"pelepay","id":"treasurer@roleplay.org.il"}}' WHERE slug = 'ביגור-16' and id > 0;

ALTER TABLE `tickets` ADD COLUMN `price` DECIMAL(5,2) NOT NULL COMMENT 'price for the fullfilment, i.e. for amount > 1, for all tickets in this record' AFTER `amount`;
ALTER TABLE `tickets` ADD COLUMN `reserved_time` DATETIME NOT NULL COMMENT '' AFTER `status`;
ALTER TABLE `tickets` ADD COLUMN `cancel_reason` VARCHAR(45) NULL DEFAULT NULL COMMENT '' AFTER `reserved_time`;
ALTER TABLE `tickets` ADD INDEX `reservation_idx` (`reserved_time` ASC)  COMMENT '';

ALTER TABLE `sales` ADD COLUMN `convention_id` INT UNSIGNED NOT NULL COMMENT '' AFTER `id`;
ALTER TABLE `sales` ADD COLUMN `processor_data` TEXT NULL DEFAULT NULL COMMENT 'processor specific transactio meta data' AFTER `cancellation_notes`;
ALTER TABLE `sales` CHANGE COLUMN `transaction_id` `transaction_id` VARCHAR(255) NULL DEFAULT NULL COMMENT 'transaction confirmation ID recived from payment processor';

ALTER TABLE `sales` ADD CONSTRAINT `conventions_ibfk_1` FOREIGN KEY (`convention_id`) REFERENCES `conventions` (`id`) ON DELETE RESTRICT;

UPDATE `system_settings` SET `value` = '7' WHERE `name` = 'data-version' and `id` > 0;
