ALTER TABLE `events` MODIFY COLUMN `title` TEXT NOT NULL COMMENT '';
ALTER TABLE `events` ADD COLUMN `created_time` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '' AFTER `description`;
ALTER TABLE `events` ADD COLUMN `updated_time` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '' AFTER `created_time`;
ALTER TABLE `events` MODIFY COLUMN `description` TEXT DEFAULT NULL COMMENT 'may be empty if submitting teasers only';

ALTER TABLE `event_tag_values` MODIFY COLUMN `title` VARCHAR(255) NOT NULL COMMENT '' ;


UPDATE `system_settings` SET `value` = '4' WHERE `name` = 'data-version' and `id` > 0;
