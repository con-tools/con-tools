#!/bin/bash

email="admin@con-troll.org"
name="Admin User"
password="123"
endpoint="http://localhost:8080"

function try_login() {
    curl -sf "${endpoint}/auth/signin" -H 'Content-Type: application/json' -d "{\"email\":\"${email}\",\"password\":\"${password}\"}" | jq .token
}

function create_user() {
    curl -f "${endpoint}/auth/register" -H 'Content-Type:application/json' -d "{\"email\":\"${email}\",\"password-register\":\"${password}\",\"name\":\"${name}\"}"
}

# try to log in with hard coded values
try_login || ( create_user && try_login )
