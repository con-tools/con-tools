#!/bin/bash
mysql -uroot -p$MYSQL_ROOT_PASSWORD -e 'CREATE DATABASE `controll`;'
# find last dump
dump="$(ls /dumps/dump* | sort | tail -n 1)"
echo "Initializing with database dump $dump"
mysql -uroot -p$MYSQL_ROOT_PASSWORD controll < $dump
# apply any missing schemas
index="$(perl -nle 'm/dump-(\d+)/ and print $1' <<<"$dump")"
while true; do
    index=$(( $index + 1 ))
    if [ -f /schema/schema-$index.sql ]; then
	echo "Updating database with /schema/schema-$index.sql"
	mysql -uroot -p$MYSQL_ROOT_PASSWORD controll < /schema/schema-$index.sql
    else
	break
    fi
done
