
ALTER TABLE `timeslots` ADD COLUMN `status` INT(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `event_id`;

UPDATE `system_settings` SET `value` = '14' WHERE `name` = 'data-version' and `id` > 0;
