# 协程组件库

所有官方提供的组件库均已进行协程化处理，可安全地在 Hyperf 内或其它协程框架内使用，基于 Hyperf 的开放性和可扩展性，社区可对此开发或适配各种各样的组件，得益于此，Hyperf 将存在着无限的可能性。   
本页将收录各个适配了 Hyperf 的协程组件 和 已经经过验证可安全地用于协程下的常用库，以便您快速的从中选择合适的组件完成您的需求。

## 如何提交我的组件？

如果您开发的协程组件适配了 Hyperf，那么您可以直接对 [hyperf-cloud/hyperf](https://github.com/hyperf-cloud/hyperf) 项目的 `master` 分支发起您的 `Pull Request`，也就是更改当前页`(./doc/zh/awesome-components.md)`。

## 如何适配 Hyperf ?

我们为您提供了一份 [Hyperf 组件开发指南](zh/component-guide/intro.md)，以帮助您开发 Hyperf 组件或适配 Hyperf 框架。

# 组件列表

## 路由

- [nikic/fastroute](https://github.com/nikic/FastRoute) 一个常用的高速路由

## 事件

- [hyperf/event](https://github.com/hyperf-cloud/event) Hyperf 官方提供的基于 PSR-14 的事件管理器

## 日志

- [hyperf/logger](https://github.com/hyperf-cloud/logger) Hyperf 官方提供的基于 PSR-3 的日志管理器，一个基于 monolog 的抽象及封装

## 命令

- [hyperf/command](https://github.com/hyperf-cloud/command) Hyperf 官方提供的基于 [symfony/console](https://github.com/symfony/console) 扩展并支持注解的命令管理组件
- [symfony/console](https://github.com/symfony/console) Symfony 提供的独立命令管理组件

## 数据库

- [hyperf/database](https://github.com/hyperf-cloud/database) Hyperf 官方提供的基于 Eloquent 衍生的数据库 ORM，可复用于其它框架
- [hyperf/model](https://github.com/hyperf-cloud/model) Hyperf 官方提供的基于 [hyperf/database](https://github.com/hyperf-cloud/database) 组件的自动模型缓存组件 

## 依赖注入容器

- [hyperf/di](https://github.com/hyperf-cloud/di) Hyperf 官方提供的支持注解及 AOP 的依赖注入容器

## 服务

- [hyperf/http-server](https://github.com/hyperf-cloud/http-server) Hyperf 官方提供的 HTTP 服务端
- [hyperf/grpc-server](https://github.com/hyperf-cloud/grpc-server) Hyperf 官方提供的 GRPC 服务端
- [hyperf/websocket-server](https://github.com/hyperf-cloud/websocket-server) Hyperf 官方提供的 WebSocket 服务端
- [hyperf/rpc-server](https://github.com/hyperf-cloud/rpc-server) Hyperf 官方提供的通用 RPC 抽象服务端

## 客户端

- [hyperf/consul](https://github.com/hyperf-cloud/consul) Hyperf 官方提供的 Consul 协程客户端
- [hyperf/elasticsearch](https://github.com/hyperf-cloud/elasticsearch) Hyperf 官方提供的 Elasticsearch 协程客户端
- [hyperf/grpc-client](https://github.com/hyperf-cloud/grpc-client) Hyperf 官方提供的 GRPC 协程客户端
- [hyperf/etcd](https://github.com/hyperf-cloud/etcd) Hyperf 官方提供的 ETCD 协程客户端
- [hyperf/rpc-client](https://github.com/hyperf-cloud/rpc-client) Hyperf 官方提供的通用 RPC 抽象协程客户端
- [hyperf/guzzle](https://github.com/hyperf-cloud/guzzle) Hyperf 官方提供的 Guzzle HTTP 协程客户端
- [hyperf/redis](https://github.com/hyperf-cloud/redis) Hyperf 官方提供的 Redis 协程客户端
- [hyperf/websocket-client](https://github.com/hyperf-cloud/websocket-client) Hyperf 官方提供的 WebSocket 协程客户端
- [hyperf/cache](https://github.com/hyperf-cloud/cache) Hyperf 官方提供的基于 PSR-16 的缓存协程客户端，支持注解的使用方式

## 消息队列

- [hyperf/amqp](https://github.com/hyperf-cloud/amqp) Hyperf 官方提供的 AMQP 协程组件
- [hyperf/async-queue](https://github.com/hyperf-cloud/async-queue) Hyperf 官方提供的简单的基于 Redis 的异步队列组件

## 配置中心

- [hyperf/config-apollo](https://github.com/hyperf-cloud/config-apollo) Hyperf 官方提供的 Apollo 配置中心接入组件
- [hyperf/config-aliyun-acm](https://github.com/hyperf-cloud/config-aliyun-acm) Hyperf 官方提供的阿里云 ACM 应用配置服务接入组件
- [hyperf/config-etcd](https://github.com/hyperf-cloud/config-etcd) Hyperf 官方提供的 ETCD 配置中心接入组件

## RPC

- [hyperf/json-rpc](https://github.com/hyperf-cloud/json-rpc) Hyperf 官方提供的 JSON-RPC 协议组件

## 服务治理

- [hyperf/rate-limit](https://github.com/hyperf-cloud/rate-limit) Hyperf 官方提供的基于令牌桶算法的限流组件
- [hyperf/load-balancer](https://github.com/hyperf-cloud/load-balancer) Hyperf 官方提供的负载均衡组件
- [hyperf/service-governance](https://github.com/hyperf-cloud/service-governance) Hyperf 官方提供的服务治理组件
- [hyperf/tracer](https://github.com/hyperf-cloud/tracer) Hyperf 官方提供的 OpenTracing 分布式调用链追踪组件
- [hyperf/circuit-breaker](https://github.com/hyperf-cloud/circuit-breaker) Hyperf 官方提供的服务熔断组件

## 定时任务

- [hyperf/crontab](https://github.com/hyperf-cloud/crontab) Hyperf 官方提供的秒级定时任务组件

## ID 生成器

- [hyperf/snowflake](https://github.com/hyperf-cloud/snowflake) Hyperf 官方提供的 Snowflake ID 生成器组件 (beta)

## 文档生成

- [hyperf/swagger](https://github.com/hyperf-cloud/swagger) Hyperf 官方提供的 Swagger 文档自动生成组件 (beta)

## Graphql

- [hyperf/graphql](https://github.com/hyperf-cloud/graphql) Hyperf 官方提供的 Graphql 服务端组件 (beta)

## 热更新/热重载

- [ha-ni-cc/hyperf-watch](https://github.com/ha-ni-cc/hyperf-watch) 一个基于 fswatch 实现的通用热更新组件
- [mix-php/swoolefor](https://github.com/mix-php/swoolefor) 一个由 Mixphp 实现的通用热更新组件
- [buexplain/go-watch](https://github.com/buexplain/go-watch) 一个基于 Go 语言实现的通用热更新组件
- [remy/nodemon](https://github.com/remy/nodemon) 一个基于 node.js 实现的通用热更新组件

## Swoole

- - [hyperf/swoole-tracker](https://github.com/hyperf-cloud/swoole-tracker) Hyperf 官方提供的对接 Swoole Tracker 的组件，提供阻塞分析、性能分析、内存泄漏分析、运行状态及调用统计等功能
- - [hyperf/task](https://github.com/hyperf-cloud/task) Hyperf 官方提供的 Task 组件，对 Swoole 的 Task 机制进行了封装及抽象，提供便捷的注解用法

> Warning: 请勿于生产环境使用 `热更新/热重载` 功能
