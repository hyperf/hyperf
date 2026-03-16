#!/usr/bin/env bash

CURRENT_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
TRAVIS_BUILD_DIR="${TRAVIS_BUILD_DIR:-$(dirname $(dirname $CURRENT_DIR))}"

echo -e "Init PostgreSQL database..."

echo "127.0.0.1:5432:postgres:postgres:postgres" > ~/.pgpass
chmod 600 ~/.pgpass

psql -h 127.0.0.1 -p 5432 -d postgres -U postgres -f .travis/pgsql.sql

echo -e "Done\n"

wait
