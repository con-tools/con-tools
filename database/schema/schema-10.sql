
CREATE TABLE `merchandise_skus` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  `convention_id` INT UNSIGNED NOT NULL COMMENT '',
  `title` VARCHAR(100) NOT NULL COMMENT 'Description of SKU',
  `created_time` DATETIME NOT NULL COMMENT '',
  `description` TEXT DEFAULT NULL COMMENT 'markdown text',
  `price` DECIMAL(5,2) NOT NULL COMMENT '',
  PRIMARY KEY (`id`) COMMENT '',
  FOREIGN KEY `convention_ibfk_1` (`convention_id`) REFERENCES `conventions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `purchases` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `sku_id` int(10) UNSIGNED NOT NULL,
  `sale_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'sale where this ticket was fulfilled',
  `amount` int(10) UNSIGNED NOT NULL,
  `price` decimal(5,2) NOT NULL COMMENT 'price for the fullfilment, i.e. for amount > 1, for all tickets in this record',
  `status` enum('reserved','processing','authorized','cancelled') DEFAULT NULL,
  `reserved_time` DATETIME NOT NULL,
  `cancel_reason` VARCHAR(45) DEFAULT NULL,
  PRIMARY KEY (`id`) COMMENT '',
  FOREIGN KEY `users_ibfk_1` (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  FOREIGN KEY `sales_ibfk_1` (`sale_id`) REFERENCES `sales` (`id`) ON DELETE RESTRICT,
  FOREIGN KEY `merchandise_ibfk_1` (`sku_id`) REFERENCES `merchandise_skus` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

UPDATE `system_settings` SET `value` = '10' WHERE `name` = 'data-version' and `id` > 0;
