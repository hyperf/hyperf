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

remote amqp git@github.com:hyperf-cloud/amqp.git
remote async-queue git@github.com:hyperf-cloud/async-queue.git
remote cache git@github.com:hyperf-cloud/cache.git
remote circuit-breaker git@github.com:hyperf-cloud/circuit-breaker.git
remote command git@github.com:hyperf-cloud/command.git
remote config git@github.com:hyperf-cloud/config.git
remote config-aliyun-acm git@github.com:hyperf-cloud/config-aliyun-acm.git
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
remote exception-handler git@github.com:hyperf-cloud/exception-handler.git
remote framework git@github.com:hyperf-cloud/framework.git
remote grpc git@github.com:hyperf-cloud/grpc.git
remote grpc-client git@github.com:hyperf-cloud/grpc-client.git
remote grpc-server git@github.com:hyperf-cloud/grpc-server.git
remote guzzle git@github.com:hyperf-cloud/guzzle.git
remote http-message git@github.com:hyperf-cloud/http-message.git
remote http-server git@github.com:hyperf-cloud/http-server.git
remote json-rpc git@github.com:hyperf-cloud/json-rpc.git
remote load-balancer git@github.com:hyperf-cloud/load-balancer.git
remote logger git@github.com:hyperf-cloud/logger.git
remote memory git@github.com:hyperf-cloud/memory.git
remote model-cache git@github.com:hyperf-cloud/model-cache.git
remote paginator git@github.com:hyperf-cloud/paginator.git
remote pool git@github.com:hyperf-cloud/pool.git
remote process git@github.com:hyperf-cloud/process.git
remote rate-limit git@github.com:hyperf-cloud/rate-limit.git
remote redis git@github.com:hyperf-cloud/redis.git
remote rpc git@github.com:hyperf-cloud/rpc.git
remote rpc-client git@github.com:hyperf-cloud/rpc-client.git
remote rpc-server git@github.com:hyperf-cloud/rpc-server.git
remote server git@github.com:hyperf-cloud/server.git
remote service-governance git@github.com:hyperf-cloud/service-governance.git
remote swagger git@github.com:hyperf-cloud/swagger.git
remote testing git@github.com:hyperf-cloud/testing.git
remote tracer git@github.com:hyperf-cloud/tracer.git
remote utils git@github.com:hyperf-cloud/utils.git

split 'src/amqp' amqp
split 'src/async-queue' async-queue
split 'src/cache' cache
split 'src/circuit-breaker' circuit-breaker
split 'src/command' command
split 'src/config' config
split 'src/config-aliyun-acm' config-aliyun-acm
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
split 'src/exception-handler' exception-handler
split 'src/framework' framework
split 'src/grpc' grpc
split 'src/grpc-client' grpc-client
split 'src/grpc-server' grpc-server
split 'src/guzzle' guzzle
split 'src/http-message' http-message
split 'src/http-server' http-server
split 'src/json-rpc' json-rpc
split 'src/load-balancer' load-balancer
split 'src/logger' logger
split 'src/memory' memory
split 'src/model-cache' model-cache
split 'src/paginator' paginator
split 'src/pool' pool
split 'src/process' process
split 'src/rate-limit' rate-limit
split 'src/redis' redis
split 'src/rpc' rpc
split 'src/rpc-client' rpc-client
split 'src/rpc-server' rpc-server
split 'src/server' server
split 'src/service-governance' service-governance
split 'src/testing' testing
split 'src/tracer' tracer
split 'src/swagger' swagger
split 'src/utils' utils