
ALTER TABLE `merchandise_skus` ADD COLUMN `code` VARCHAR(45) NOT NULL COMMENT '' AFTER `title`;
ALTER TABLE `merchandise_skus` ADD UNIQUE INDEX `unique-codes` (`convention_id` ASC, `code` ASC)  COMMENT '';

UPDATE `system_settings` SET `value` = '11' WHERE `name` = 'data-version' and `id` > 0;
