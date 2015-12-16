ALTER TABLE `conventions` DROP INDEX `name_UNIQUE`;
ALTER TABLE `conventions` CHANGE COLUMN `name` `title` VARCHAR(255) NOT NULL;
ALTER TABLE `conventions` ADD UNIQUE INDEX `title_UNIQUE` (`title`)  COMMENT '';
ALTER TABLE `conventions` ADD COLUMN `slug` VARCHAR(50) DEFAULT '' COMMENT '' AFTER `id`;
UPDATE `conventions` SET slug = REPLACE(title, ' ', '-');
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
  `status` VARCHAR(30) NOT NULL COMMENT '',
  PRIMARY KEY (`id`),
  CONSTRAINT `crm_issues_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `crm_issues_ibfk_2` FOREIGN KEY (`crm_queue_id`) REFERENCES `crm_queue` (`id`) ON DELETE CASCADE,
  CONSTRAINT `crm_issues_ibfk_3` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=INNODB DEFAULT CHARACTER SET UTF8;

CREATE TABLE `crm_messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `crm_issue_id` INT UNSIGNED NOT NULL COMMENT '',
  `sender_id` INT UNSIGNED NOT NULL '',
  `subject` TEXT NOT NULL COMMENT '',
  `text` TEXT NOT NULL COMMENT '',
  `in_reply_to` INT UNSIGNED DEFAULT NULL COMMENT '',
  `smtp_message_id` VARCHAR(255) DEFAULT NULL COMMENT '',
  PRIMARY KEY (`id`),
  CONSTRAINT `crm_messages_ibfk_1` FOREIGN KEY (`crm_issue_id`) REFERENCES `crm_issues` (`id`) ON DELETE CASCADE,
  CONSTRAINT `crm_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `crm_messages_ibfk_3` FOREIGN KEY (`in_reply_to`) REFERENCES `crm_messages` (`id`) ON DELETE CASCADE
) ENGINE=INNODB DEFAULT CHARACTER SET UTF8;

UPDATE `system_settings` SET `value` = '2' WHERE `name` = 'data-version';
