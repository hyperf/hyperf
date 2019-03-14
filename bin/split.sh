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

remote config git@github.com:hyperf-cloud/config.git

remote amqp git@github.com:hyperf-cloud/amqp.git
remote cache git@github.com:hyperf-cloud/cache.git
remote circuit-breaker git@github.com:hyperf-cloud/circuit-breaker.git
remote config git@github.com:hyperf-cloud/config.git
remote config-apollo git@github.com:hyperf-cloud/config-apollo.git
remote constants git@github.com:hyperf-cloud/constants.git
remote consul git@github.com:hyperf-cloud/consul.git
remote contract git@github.com:hyperf-cloud/contract.git
remote database git@github.com:hyperf-cloud/database.git
remote db-connection git@github.com:hyperf-cloud/db-connection.git
remote devtool git@github.com:hyperf-cloud/devtool.git
remote di git@github.com:hyperf-cloud/di.git
remote dispatcher git@github.com:hyperf-cloud/dispatcher.git
remote elasticsearch git@github.com:hyperf-cloud/elasticsearch.git
remote event git@github.com:hyperf-cloud/event.git
remote framework git@github.com:hyperf-cloud/framework.git
remote grpc-client git@github.com:hyperf-cloud/grpc-client.git
remote grpc-server git@github.com:hyperf-cloud/grpc-server.git
remote guzzle git@github.com:hyperf-cloud/guzzle.git
remote http-server git@github.com:hyperf-cloud/http-server.git
remote logger git@github.com:hyperf-cloud/logger.git
remote memory git@github.com:hyperf-cloud/memory.git
remote model-cache git@github.com:hyperf-cloud/model-cache.git
remote paginator git@github.com:hyperf-cloud/paginator.git
remote pool git@github.com:hyperf-cloud/pool.git
remote process git@github.com:hyperf-cloud/process.git
remote queue git@github.com:hyperf-cloud/queue.git
remote rate-limit git@github.com:hyperf-cloud/rate-limit.git
remote redis git@github.com:hyperf-cloud/redis.git
remote tracer git@github.com:hyperf-cloud/tracer.git
remote utils git@github.com:hyperf-cloud/utils.git

split 'src/amqp' amqp
split 'src/cache' cache
split 'src/circuit-breaker' circuit-breaker
split 'src/config' config
split 'src/config-apollo' config-apollo
split 'src/constants' constants
split 'src/consul' consul
split 'src/contract' contract
split 'src/database' database
split 'src/db-connection' db-connection
split 'src/devtool' devtool
split 'src/di' di
split 'src/dispatcher' dispatcher
split 'src/elasticsearch' elasticsearch
split 'src/event' event
split 'src/framework' framework
split 'src/grpc-client' grpc-client
split 'src/grpc-server' grpc-server
split 'src/guzzle' guzzle
split 'src/http-server' http-server
split 'src/logger' logger
split 'src/memory' memory
split 'src/model-cache' model-cache
split 'src/paginator' paginator
split 'src/pool' pool
split 'src/process' process
split 'src/queue' queue
split 'src/rate-limit' rate-limit
split 'src/redis' redis
split 'src/tracer' tracer
split 'src/utils' utils