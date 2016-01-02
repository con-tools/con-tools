FROM mysql:latest

ADD dumps/dump-2.sql /docker-entrypoint-initdb.d/init.sql
