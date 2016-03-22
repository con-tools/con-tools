#!/bin/bash
mysql -uroot -p$MYSQL_ROOT_PASSWORD -e 'CREATE DATABASE `controll`;'
for file in /docker-entrypoint-initdb-sh.d/*; do
	echo "Importing $file..."
	mysql -uroot -p$MYSQL_ROOT_PASSWORD controll < $file
	echo "Done"
done
