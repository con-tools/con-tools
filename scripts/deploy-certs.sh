#!/bin/bash

dir="$(dirname $0)/.."

[ -z "$MYSQL_CLIENT_CERT" ] && exit 0

echo "$MYSQL_CA_CERT" > $dir/application/config/secure/server-ca.pem
echo "$MYSQL_CLIENT_CERT" > $dir/application/config/secure/client-cert.pem
echo "$MYSQL_CLIENT_KEY" > $dir/application/config/secure/client-key.pem
