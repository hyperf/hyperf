#!/usr/bin/env bash

set -e
set -x

CURRENT_BRANCH="master"

function split()
{
    SHA1=`./bin/splitsh-lite --prefix=$1`
    git push $2 "$SHA1:refs/heads/$CURRENT_BRANCH" -f
}

function remote()
{
    git remote add $1 $2 || true
}

git pull origin $CURRENT_BRANCH

remote config git@github.com:hyperflex-cloud/config.git
remote di git@github.com:hyperflex-cloud/di.git
remote dispatcher git@github.com:hyperflex-cloud/dispatcher.git
remote framework git@github.com:hyperflex-cloud/framework.git
remote grpc-server git@github.com:hyperflex-cloud/grpc-server.git
remote http-server git@github.com:hyperflex-cloud/http-server.git
remote memory git@github.com:hyperflex-cloud/memory.git
remote utils git@github.com:hyperflex-cloud/utils.git

split 'src/config' config
split 'src/di' di
split 'src/dispatcher' dispatcher
split 'src/framework' framework
split 'src/grpc-server' grpc-server
split 'src/http-server' http-server
split 'src/memory' memory
split 'src/utils' utils