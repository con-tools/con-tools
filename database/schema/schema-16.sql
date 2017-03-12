-- Schema release 16

ALTER TABLE `passes` ADD COLUMN `order` INT NOT NULL DEFAULT 1 AFTER `price`;

UPDATE `system_settings` SET `value` = '16' WHERE `name` = 'data-version' and `id` > 0;
