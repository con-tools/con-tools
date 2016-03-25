ALTER TABLE `event_tags` ADD UNIQUE INDEX `event_tags_uk_1` (`event_id`,`event_tag_value_id`)  COMMENT '';


UPDATE `system_settings` SET `value` = '5' WHERE `name` = 'data-version' and `id` > 0;
