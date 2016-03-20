#!/bin/bash
set -x
mysql -uroot -p$MYSQL_ROOT_PASSWORD -e 'CREATE DATABASE `controll`;'
for file in /docker-entrypoint-initdb-sh.d/*; do
	mysql -uroot -p$MYSQL_ROOT_PASSWORD controll < $file
done
