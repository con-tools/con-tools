#!/bin/bash

email="admin@con-troll.org"
name="Admin User"
password="123"
endpoint="http://localhost:8080"

function try_login() {
    curl -sf "${endpoint}/auth/signin" -H 'Content-Type: application/json' -d "{\"email\":\"${email}\",\"password\":\"${password}\"}" > >(jq -r .token)
}

function create_user() {
    curl -sf "${endpoint}/auth/register" -H 'Content-Type:application/json' -d "{\"email\":\"${email}\",\"password-register\":\"${password}\",\"name\":\"${name}\"}"
}

# try to log in with hard coded values
try_login || ( create_user && try_login ) >/dev/null

mysql -uroot -psecret -h172.17.0.2 controll < $(dirname $0)/../database/dumps/docker-add-admin-to-convention.sql >/dev/null 2> >(fgrep -v 'Using a password on the command line')
