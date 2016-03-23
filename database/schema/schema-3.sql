CREATE TABLE `timeslots` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `event_id` INT UNSIGNED NOT NULL COMMENT '',
  `start_time` DATETIME DEFAULT NULL COMMENT '',
  `duration` INT UNSIGNED NOT NULL COMMENT '',
  `min_attendees` INT UNSIGNED NOT NULL COMMENT '',
  `max_attendees` INT UNSIGNED NOT NULL COMMENT '',
  `notes_to_attendees` TEXT COMMENT '',
  PRIMARY KEY (`id`),
  CONSTRAINT `timeslot_event_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE RESTRICT
) ENGINE=INNODB DEFAULT CHARACTER SET UTF8;

CREATE TABLE `timeslot_hosts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `user_id` INT UNSIGNED NOT NULL COMMENT '',
  `timeslot_id` INT UNSIGNED NOT NULL COMMENT '',
  PRIMARY KEY (`id`),
  CONSTRAINT `timeslot_host_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `timeslot_instance_ibfk_2` FOREIGN KEY (`timeslot_id`) REFERENCES `timeslots` (`id`) ON DELETE CASCADE
) ENGINE=INNODB DEFAULT CHARACTER SET UTF8;

CREATE TABLE `locations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `convention_id` INT UNSIGNED NOT NULL COMMENT '',
  `title` VARCHAR(50) NOT NULL COMMENT '',
  `slug` VARCHAR(50) NOT NULL COMMENT '',
  `max_attendees` INT UNSIGNED NOT NULL COMMENT '',
  `area` VARCHAR(50) DEFAULT NULL COMMENT 'area specification for areas with subdivisions, such as "main hall" or "blue rooms"',
  PRIMARY KEY (`id`),
  CONSTRAINT `location_convention_ibfk_1` FOREIGN KEY (`convention_id`) REFERENCES `conventions` (`id`) ON DELETE CASCADE,
  UNIQUE INDEX `location_title_uk_1` (`convention_id`, `title`),
  UNIQUE INDEX `location_title_uk_2` (`convention_id`, `slug`) 
) ENGINE=INNODB DEFAULT CHARACTER SET UTF8;

CREATE TABLE `timeslot_locations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `timeslot_id` INT UNSIGNED NOT NULL COMMENT '',
  `location_id` INT UNSIGNED NOT NULL COMMENT '',
  PRIMARY KEY (`id`),
  CONSTRAINT `timeslot_instance_ibfk_1` FOREIGN KEY (`timeslot_id`) REFERENCES `timeslots` (`id`) ON DELETE CASCADE,
  CONSTRAINT `timeslot_location_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE RESTRICT
) ENGINE=INNODB DEFAULT CHARACTER SET UTF8;

CREATE TABLE `organizers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `convention_id` INT UNSIGNED NOT NULL COMMENT '',
  `title` VARCHAR(50) NOT NULL COMMENT '',
  PRIMARY KEY (`id`),
  CONSTRAINT `organizer_convention_ibfk_1` FOREIGN KEY (`convention_id`) REFERENCES `conventions` (`id`) ON DELETE CASCADE
) ENGINE=INNODB DEFAULT CHARACTER SET UTF8;

ALTER TABLE `events` ADD COLUMN `status` INT NOT NULL DEFAULT 0 COMMENT 'see event mode for valid status';
ALTER TABLE `events` ADD COLUMN `logistical_requirements` TEXT DEFAULT NULL COMMENT 'logistical requirements for event' AFTER `notes_to_staff`;
ALTER TABLE `events` ADD COLUMN `custom_data` TEXT DEFAULT NULL COMMENT 'JSON encoded user data' AFTER `scheduling_constraints`;
ALTER TABLE `events` MODIFY COLUMN `staff_contact_id` INT UNSIGNED DEFAULT NULL COMMENT '';
ALTER TABLE `events` MODIFY COLUMN `price` DECIMAL(5,2) DEFAULT NULL COMMENT '';
ALTER TABLE `events` MODIfY COLUMN `teaser` TEXT DEFAULT NULL COMMENT '';
ALTER TABLE `events` MODIfY COLUMN `notes_to_staff` TEXT DEFAULT NULL COMMENT '';
ALTER TABLE `events` MODIfY COLUMN `notes_to_attendees` TEXT DEFAULT NULL COMMENT '';
ALTER TABLE `events` MODIfY COLUMN `scheduling_constraints` TEXT DEFAULT NULL COMMENT '';

ALTER TABLE `event_tag_types` MODIFY COLUMN `requirement` CHAR(1) DEFAULT '+' COMMENT 'Req-spec, can be one of "1", "+" or "*"';
ALTER TABLE `event_tags` DROP FOREIGN KEY `event_tags_ibfk_2`;
ALTER TABLE `event_tags` ADD CONSTRAINT `event_tags_ibfk_2` FOREIGN KEY (`event_tag_value_id`) REFERENCES `event_tag_values` (`id`) ON DELETE RESTRICT;

INSERT INTO `roles` (`key`, `title`) VALUES ('administrator','System Administrator'), ('manager', 'Convention Manager');

CREATE TABLE `managers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `convention_id` INT UNSIGNED NOT NULL COMMENT '',
  `user_id` INT UNSIGNED NOT NULL COMMENT '',
  `role_id` INT UNSIGNED NOT NULL COMMENT '',
  PRIMARY KEY (`id`),
  CONSTRAINT `manager_convention_ibfk_1` FOREIGN KEY (`convention_id`) REFERENCES `conventions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `manager_user_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `manager_role_ibfk_3` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=INNODB DEFAULT CHARACTER SET UTF8;

UPDATE `system_settings` SET `value` = '3' WHERE `name` = 'data-version' and `id` > 0;
