-- run this after using scripts/docker-login.sh to setup a user
insert into managers (convention_id, user_id, role_id) values (
	(select id from conventions where slug = 'ביגור-16'),
    (select id from users where email = 'admin@con-troll.org'),
    (select id from roles where `key` = 'manager'));
