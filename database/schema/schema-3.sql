CREATE TABLE `timeslots` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `event_id` INT UNSIGNED NOT NULL COMMENT '',
  `start_time` DATETIME DEFAULT NULL COMMENT '',
  `duration` INT UNSIGNED NOT NULL COMMENT '',
  `min_attendees` INT UNSIGNED NOT NULL COMMENT '',
  `max_attendees` INT UNSIGNED NOT NULL COMMENT '',
  `node_to_attendees` TEXT COMMENT '',
  PRIMARY KEY (`id`),
  CONSTRAINT `timeslot_hosts_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
) ENGINE=INNODB DEFAULT CHARACTER SET UTF8;

CREATE TABLE `timeslot_hosts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `user_id` INT UNSIGNED NOT NULL COMMENT '',
  `timeslot_id` INT UNSIGNED NOT NULL COMMENT '',
  PRIMARY KEY (`id`),
  CONSTRAINT `timeslot_hosts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `timeslot_hosts_ibfk_2` FOREIGN KEY (`timeslot_id`) REFERENCES `timeslots` (`id`) ON DELETE CASCADE,
) ENGINE=INNODB DEFAULT CHARACTER SET UTF8;

CREATE TABLE `timeslot_locations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `timeslot_id` INT UNSIGNED NOT NULL COMMENT '',
  `location_id` INT UNSIGNED NOT NULL COMMENT '',
  PRIMARY KEY (`id`),
  CONSTRAINT `timeslot_hosts_ibfk_2` FOREIGN KEY (`timeslot_id`) REFERENCES `timeslots` (`id`) ON DELETE CASCADE,
  CONSTRAINT `timeslot_hosts_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE,
) ENGINE=INNODB DEFAULT CHARACTER SET UTF8;

CREATE TABLE `coupons` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `user_id` INT UNSIGNED NOT NULL COMMENT '',
  `coupon_type_id` INT UNSIGNED NOT NULL COMMENT '',
  `sale_id` INT UNSIGNED NOT NULL COMMENT '',
  `amount` DECIMAL(5,2) NOT NULL COMMENT '',
  PRIMARY KEY (`id`),
  CONSTRAINT `timeslot_hosts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `timeslot_hosts_ibfk_2` FOREIGN KEY (`coupon_type_id`) REFERENCES `coupon_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `timeslot_hosts_ibfk_2` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
) ENGINE=INNODB DEFAULT CHARACTER SET UTF8;

CREATE TABLE `locations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `convention_id` INT UNSIGNED NOT NULL COMMENT '',
  `title` VARCHAR(50) NOT NULL COMMENT '',
  `max_attendees` INT UNSIGNED NOT NULL COMMENT '',
  `area` VARCHAR(50) DEFAULT NULL COMMENT '',
  PRIMARY KEY (`id`),
  CONSTRAINT `timeslot_hosts_ibfk_1` FOREIGN KEY (`convention_id`) REFERENCES `conventions` (`id`) ON DELETE CASCADE,
) ENGINE=INNODB DEFAULT CHARACTER SET UTF8;


CREATE TABLE `organizers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `convention_id` INT UNSIGNED NOT NULL COMMENT '',
  `title` VARCHAR(50) NOT NULL COMMENT '',
  PRIMARY KEY (`id`),
  CONSTRAINT `timeslot_hosts_ibfk_1` FOREIGN KEY (`convention_id`) REFERENCES `conventions` (`id`) ON DELETE CASCADE,
) ENGINE=INNODB DEFAULT CHARACTER SET UTF8;

ALTER TABLE `events` ADD COLUMN `logistical_requirements` TEXT DEFAULT NULL COMMENT 'logistical requirements for event' AFTER `notes_to_staff`;

UPDATE `system_settings` SET `value` = '3' WHERE `name` = 'data-version';
