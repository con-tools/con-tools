ALTER TABLE `events` ADD COLUMN `logistical_requirements` TEXT DEFAULT NULL COMMENT 'logistical requirements for event' AFTER `notes_to_staff`;

UPDATE `system_settings` SET `value` = '2' WHERE `name` = 'data-version';
