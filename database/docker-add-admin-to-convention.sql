select * from managers;

INSERT INTO `managers` (`convention_id`, `user_id`, `role_id`)
SELECT `id`, (SELECT `id` FROM `users` WHERE `email` = 'admin@con-troll.org'), (SELECT `id` FROM `roles` WHERE `key` = 'manager')
FROM `conventions`;
