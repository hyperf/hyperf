#!/usr/bin/env bash

CURRENT_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
TRAVIS_BUILD_DIR="${TRAVIS_BUILD_DIR:-$(dirname $(dirname $CURRENT_DIR))}"

echo -e "Create MySQL database..."
mysql -u root -e "CREATE DATABASE IF NOT EXISTS hyperf charset=utf8mb4 collate=utf8mb4_unicode_ci;"
cat "${TRAVIS_BUILD_DIR}/.travis/hyperf.sql" | mysql -u root hyperf

echo -e "Done\n"

wait