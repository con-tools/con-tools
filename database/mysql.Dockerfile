FROM mysql:latest

ADD dumps/dump-1.sql /docker-entrypoint-initdb.d/init.sql
