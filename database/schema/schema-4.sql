ALTER TABLE `events` ADD COLUMN `created_time` DATETIME DEFAULT CURRENT_TIMESTAMP AFTER `description`;
ALTER TABLE `events` ADD COLUMN `updated_time` TIMESTAMP AFTER `created_time`;

UPDATE `system_settings` SET `value` = '4' WHERE `name` = 'data-version' and `id` > 0;
