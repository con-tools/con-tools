FROM mysql:latest

ADD database/dumps/dump-1.sql /docker-entrypoint-initdb.d/init.sql
