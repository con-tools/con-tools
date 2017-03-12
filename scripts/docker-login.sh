#!/bin/bash

email="admin@con-troll.org"
name="Admin User"
password="123"
endpoint="http://localhost:8080"
mysql_ip="$(docker inspect --format='{{.NetworkSettings.IPAddress}}' controll_mysql_1)"

function try_login() {
    curl -sf "${endpoint}/auth/signin" -H 'Content-Type: application/json' -d "{\"email\":\"${email}\",\"password\":\"${password}\"}" > >(jq -r .token)
}

function create_user() {
    curl -sf "${endpoint}/auth/register" -H 'Content-Type:application/json' -d "{\"email\":\"${email}\",\"password-register\":\"${password}\",\"name\":\"${name}\"}"
}

# try to log in with hard coded values
try_login || ( create_user && try_login ) >/dev/null

mysql -uroot -psecret -h$mysql_ip controll < $(dirname $0)/../database/docker-add-admin-to-convention.sql >/dev/null 2> >(fgrep -v 'Using a password on the command line')
