-- Schema release 15

CREATE TABLE `pass_requirements` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `convention_id` int(10) unsigned NOT NULL,
  `slug` varchar(45) NOT NULL,
  `title` varchar(45) NOT NULL,
  `start_time` varchar(15) DEFAULT NULL,
  `end_time` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_in_convention` (`convention_id`,`title`),
  UNIQUE KEY `slug_in_convention` (`convention_id`,`slug`),
  CONSTRAINT `pass_requirement_convention_ibfk_1` FOREIGN KEY (`convention_id`) REFERENCES `conventions` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `passes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `convention_id` int(10) unsigned NOT NULL,
  `slug` varchar(45) NOT NULL,
  `title` varchar(45) NOT NULL,
  `public` int(1) unsigned NOT NULL DEFAULT '1', -- "1" == is public
  `status` int(1) unsigned NOT NULL DEFAULT '0', -- "1" == cancelled
  `price` decimal(5,2) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `pass_convention_ibfk_1` FOREIGN KEY (`convention_id`) REFERENCES `conventions` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `pass_requirements_passes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pass_id` int(10) unsigned NOT NULL,
  `pass_requirement_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pass_requirements_ibfk_1` (`pass_requirement_id`),
  KEY `pass_requirements_passes_ibfk_1_idx` (`pass_id`),
  CONSTRAINT `pass_requirements_ibfk_1` FOREIGN KEY (`pass_requirement_id`) REFERENCES `pass_requirements` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `pass_requirements_passes_ibfk_1` FOREIGN KEY (`pass_id`) REFERENCES `passes` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_passes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `pass_id` int(10) unsigned NOT NULL,
  `sale_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(45) NOT NULL,
  `price` decimal(5,2) NOT NULL, -- the price that was paid for that pass
  `status` enum('reserved','processing','authorized','cancelled','refunded') NOT NULL,
  `reserved_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cancel_reason` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_passes_pass_ibfk_1_idx` (`pass_id`),
  KEY `user_passes_user_ibfk_1_idx` (`user_id`),
  CONSTRAINT `user_passes_pass_ibfk_1` FOREIGN KEY (`pass_id`) REFERENCES `passes` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `user_passes_user_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `timeslots` ADD COLUMN `pass_requirement_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `notes_to_attendees`;
ALTER TABLE `timeslots` ADD INDEX `timeslot_pass_requirement_ibfk_2_idx` (`pass_requirement`);
ALTER TABLE `timeslots` ADD CONSTRAINT `timeslot_pass_requirement_ibfk_2` FOREIGN KEY (`pass_requirement`) REFERENCES `pass_requirements` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `coupons` DROP FOREIGN KEY `coupons_ibfk_3`;
ALTER TABLE `coupons`
	CHANGE COLUMN `ticket_id` `object_id` INT(10) UNSIGNED NULL DEFAULT NULL,
	ADD COLUMN `object_type` ENUM('ticket', 'user_pass') NULL DEFAULT NULL AFTER `object_id`,
	DROP INDEX `coupons_ibfk_3` ;

UPDATE `coupons` SET `object_type` = 'ticket' where `object_id` IS NOT NULL AND id > 0;

UPDATE `system_settings` SET `value` = '15' WHERE `name` = 'data-version' and `id` > 0;

-- additional migration to do:
-- 1. add pass requirements for Bigor 17
-- 2. auto set-up existing timeslots with pass requirements by calling /entities/timeslots?update_passes=1
-- 3. add passes for Bigor 17
