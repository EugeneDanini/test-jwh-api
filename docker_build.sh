#!/bin/bash
die () {
    echo >&2 "$@"
    exit 1
}
[ "$#" -eq 1 ] || die "one env argument required"
[ -f .env."$1" ] || die "invalid env argument"

cp .env."$1" .env
sudo docker build -t jwt .
rm .env
