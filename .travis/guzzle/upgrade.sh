#!/usr/bin/env sh
if [ ${GUZZLE_7} ]; then
  composer remove overtrue/flysystem-cos --dev
  composer remove endclothing/prometheus_client_php --dev
  cp -rf .travis/guzzle/phpstan.neon phpstan.neon
fi
