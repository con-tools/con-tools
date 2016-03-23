ALTER TABLE `conventions` DROP INDEX `name_UNIQUE`;
ALTER TABLE `conventions` CHANGE COLUMN `name` `title` VARCHAR(255) NOT NULL;
ALTER TABLE `conventions` ADD UNIQUE INDEX `title_UNIQUE` (`title`)  COMMENT '';
ALTER TABLE `conventions` ADD COLUMN `slug` VARCHAR(50) DEFAULT '' COMMENT '' AFTER `id`;
UPDATE `conventions` SET slug = REPLACE(title, ' ', '-') WHERE id > 0;
ALTER TABLE `conventions` CHANGE COLUMN `slug` `slug` VARCHAR(50) NOT NULL COMMENT '';
ALTER TABLE `conventions` ADD COLUMN (
	`series` VARCHAR(50) DEFAULT '' COMMENT '',
	`website` VARCHAR(255) DEFAULT '' COMMENT '',
	`location` TEXT COMMENT '',
	`start_date` DATETIME DEFAULT NULL COMMENT '',
	`end_date` DATETIME DEFAULT NULL COMMENT ''
	);

CREATE TABLE `roles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `key` VARCHAR(20) NOT NULL COMMENT '',
  `title` VARCHAR(20) NOT NULL COMMENT '',
  PRIMARY KEY (`id`)
) ENGINE=INNODB DEFAULT CHARACTER SET UTF8;

CREATE TABLE `events` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `user_id` INT UNSIGNED NOT NULL COMMENT '',
  `staff_contact_id` INT UNSIGNED NOT NULL COMMENT '',
  `convention_id` INT UNSIGNED NOT NULL COMMENT '',
  `title` VARCHAR(255) NOT NULL COMMENT '',
  `teaser` TEXT NOT NULL COMMENT '',
  `description` TEXT NOT NULL COMMENT '',
  `price` DECIMAL(5,2) NOT NULL COMMENT '',
  `requires_registration` BOOLEAN DEFAULT TRUE COMMENT '',
  `duration` INT NOT NULL COMMENT 'in seconds to make epoch calculations easier',
  `min_attendees` INT NOT NULL DEFAULT 0 COMMENT '',
  `max_attendees` INT DEFAULT NULL COMMENT 'null value means "open event"',
  `notes_to_staff` TEXT NOT NULL COMMENT '',
  `notes_to_attendees` TEXT NOT NULL COMMENT '',
  `scheduling_constraints` TEXT NOT NULL COMMENT '',
  PRIMARY KEY (`id`),
  CONSTRAINT `events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `events_ibfk_2` FOREIGN KEY (`staff_contact_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `events_ibfk_3` FOREIGN KEY (`convention_id`) REFERENCES `conventions` (`id`) ON DELETE CASCADE
) ENGINE=INNODB DEFAULT CHARACTER SET UTF8;

CREATE TABLE `event_tag_types` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `convention_id` INT UNSIGNED NOT NULL COMMENT '',
  `title` VARCHAR(50) NOT NULL COMMENT '',
  `requirement` VARCHAR(50) NOT NULL COMMENT '', -- see update on schema-3
  `visible` BOOLEAN NOT NULL DEFAULT TRUE COMMENT '',
  PRIMARY KEY (`id`),
  CONSTRAINT `event_tag_types_ibfk_1` FOREIGN KEY (`convention_id`) REFERENCES `conventions` (`id`) ON DELETE CASCADE
) ENGINE=INNODB DEFAULT CHARACTER SET UTF8;

CREATE TABLE `event_tag_values` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `event_tag_type_id` INT UNSIGNED NOT NULL COMMENT '',
  `title` VARCHAR(50) NOT NULL COMMENT '',
  PRIMARY KEY (`id`),
  CONSTRAINT `event_tag_values_ibfk_1` FOREIGN KEY (`event_tag_type_id`) REFERENCES `event_tag_types` (`id`) ON DELETE CASCADE
) ENGINE=INNODB DEFAULT CHARACTER SET UTF8;

CREATE TABLE `event_tags` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `event_id` INT UNSIGNED NOT NULL COMMENT '',
  `event_tag_value_id` INT UNSIGNED NOT NULL COMMENT '',
  PRIMARY KEY (`id`),
  CONSTRAINT `event_tags_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `event_tags_ibfk_2` FOREIGN KEY (`event_tag_value_id`) REFERENCES `event_tag_values` (`id`) ON DELETE CASCADE
) ENGINE=INNODB DEFAULT CHARACTER SET UTF8;

CREATE TABLE `crm_queues` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `convention_id` INT UNSIGNED NOT NULL COMMENT '',
  `title` VARCHAR(255) NOT NULL COMMENT '',
  PRIMARY KEY (`id`),
  CONSTRAINT `crm_queues_ibfk_1` FOREIGN KEY (`convention_id`) REFERENCES `conventions` (`id`) ON DELETE CASCADE
) ENGINE=INNODB DEFAULT CHARACTER SET UTF8;

CREATE TABLE `crm_issues` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `event_id` INT UNSIGNED NOT NULL COMMENT '',
  `crm_queue_id` INT UNSIGNED NOT NULL COMMENT '',
  `agent_id` INT UNSIGNED NOT NULL COMMENT '',
  `title` TEXT NOT NULL COMMENT '',
  `status` VARCHAR(30) NOT NULL DEFAULT 'unassigned' COMMENT 'One of: unassigned, open, awaiting-approval, ready-for-timeslotting, closed',
  PRIMARY KEY (`id`),
  CONSTRAINT `crm_issues_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `crm_issues_ibfk_2` FOREIGN KEY (`crm_queue_id`) REFERENCES `crm_queues` (`id`) ON DELETE CASCADE,
  CONSTRAINT `crm_issues_ibfk_3` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=INNODB DEFAULT CHARACTER SET UTF8;

CREATE TABLE `crm_messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `crm_issue_id` INT UNSIGNED NOT NULL COMMENT '',
  `sender_id` INT UNSIGNED NOT NULL COMMENT '',
  `subject` TEXT NOT NULL COMMENT '',
  `text` TEXT NOT NULL COMMENT '',
  `in_reply_to` INT UNSIGNED DEFAULT NULL COMMENT '',
  `smtp_message_id` VARCHAR(255) DEFAULT NULL COMMENT '',
  PRIMARY KEY (`id`),
  CONSTRAINT `crm_messages_ibfk_1` FOREIGN KEY (`crm_issue_id`) REFERENCES `crm_issues` (`id`) ON DELETE CASCADE,
  CONSTRAINT `crm_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `crm_messages_ibfk_3` FOREIGN KEY (`in_reply_to`) REFERENCES `crm_messages` (`id`) ON DELETE CASCADE
) ENGINE=INNODB DEFAULT CHARACTER SET UTF8;

CREATE TABLE `coupon_types` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `convention_id` INT UNSIGNED NOT NULL COMMENT '',
  `title` VARCHAR(255) NOT NULL COMMENT '',
  `discount_type` VARCHAR(8) NOT NULL DEFAULT 'fixed' COMMENT 'One of: fixed, percent',
  `amount` DECIMAL(5,2) NOT NULL DEFAULT 0 COMMENT '',
  `category` VARCHAR(255) NOT NULL COMMENT '',
  `multiuse` BOOLEAN NOT NULL DEFAULT FALSE COMMENT '',
  `code` VARCHAR(255) DEFAULT NULL COMMENT 'coupon code to type in or scan',
  PRIMARY KEY (`id`),
  CONSTRAINT `coupon_types_ibfk_1` FOREIGN KEY (`convention_id`) REFERENCES `conventions` (`id`) ON DELETE CASCADE
) ENGINE=INNODB DEFAULT CHARACTER SET UTF8;

CREATE TABLE IF NOT EXISTS `sales` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `user_id` INT UNSIGNED NOT NULL COMMENT '',
  `cashier_id` INT UNSIGNED DEFAULT NULL COMMENT 'If null, this is self-service (i.e. website)',
  `transaction_id` VARCHAR(255) NOT NULL COMMENT 'transaction confirmation ID recived from payment processor',
  `original_sale_id` INT UNSIGNED DEFAULT NULL COMMENT 'for cancellations, the original sale that is cancelled',
  `sale_time` TIMESTAMP,
  `cancellation_notes` TEXT DEFAULT NULL COMMENT 'for cancellation, cashier or user notes',
  PRIMARY KEY (`id`),
  INDEX `sale_cancellation_idx` (`original_sale_id`),
  CONSTRAINT `sale_customer_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sale_cashier_ibfk_1` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sale_cancellation_ibfk_1` FOREIGN KEY (`original_sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=INNODB DEFAULT CHARACTER SET UTF8;

CREATE TABLE IF NOT EXISTS `coupons` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `user_id` INT UNSIGNED NOT NULL COMMENT '',
  `coupon_type_id` INT UNSIGNED NOT NULL COMMENT '',
  `sale_id` INT UNSIGNED NOT NULL COMMENT '',
  `amount` DECIMAL(5,2) NOT NULL COMMENT '',
  PRIMARY KEY (`id`) COMMENT '',
  FOREIGN KEY `fk_user_id` (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY `fk_coupon_type_id` (`coupon_type_id`) REFERENCES `coupon_types` (`id`) ON DELETE CASCADE,
  FOREIGN KEY `fk_sale_id` (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=INNODB CHARACTER SET UTF8;

UPDATE `system_settings` SET `value` = '2' WHERE `name` = 'data-version' and `id` > 0;
