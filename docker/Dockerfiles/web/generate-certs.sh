#!/bin/bash

# $1: Site Url
# $2: Cert file name

if [ $# -ne 2 ]; then
echo "generate-certs() not enough parameters [$1][$2]"
return
fi

mkdir -p /etc/ssl/certs/
key_file=`mktemp`
url=$1
openssl genrsa -des3 -passout pass:1234 -out $key_file 1024
openssl rsa -in $key_file -passin pass:1234 -out /etc/ssl/certs/$1.key
chmod 600 /etc/ssl/certs/$1.key
echo "generate-certs() $1.key generated"

openssl req -new -config /tmp/ssl_config -passin pass:1234 -x509 -nodes -sha1 -days 365 -key $key_file -out /etc/ssl/certs/$1.crt
chmod 644 /etc/ssl/certs/$1.crt
echo "generate-certs() $1.crt generated"

rm -f $key_file
rm -f /tmp/ssl_config