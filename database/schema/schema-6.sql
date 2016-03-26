ALTER TABLE `timeslot_hosts` ADD COLUMN `name` VARCHAR(255) DEFAULT NULL COMMENT 'alternative name for the host to display on the event if the user name is not appropriate';

UPDATE `system_settings` SET `value` = '6' WHERE `name` = 'data-version' and `id` > 0;
