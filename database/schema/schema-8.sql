
CREATE TABLE `organization_members` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `organizer_id` INT UNSIGNED NOT NULL COMMENT '',
  `user_id` INT UNSIGNED NOT NULL COMMENT '',
  `membership` VARCHAR(50) NOT NULL COMMENT 'organization membership code/number/registration etc.',
  PRIMARY KEY (`id`) COMMENT '',
  FOREIGN KEY `fk_organizer_id` (`organizer_id`) REFERENCES `organizers` (`id`) ON DELETE RESTRICT,
  FOREIGN KEY `fk_user_id` (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=MyISAM DEFAULT CHARACTER SET UTF8;

UPDATE `system_settings` SET `value` = '8' WHERE `name` = 'data-version' and `id` > 0;
