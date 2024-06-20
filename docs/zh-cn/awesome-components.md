# 协程组件库

所有官方提供的组件库均已进行协程化处理，可安全地在 Hyperf 内或其它协程框架内使用，基于 Hyperf 的开放性和可扩展性，社区可对此开发或适配各种各样的组件，得益于此，Hyperf 将存在着无限的可能性。
本页将收录各个适配了 Hyperf 的协程组件和已经经过验证可安全地用于协程下的常用库，以便您快速的从中选择合适的组件完成您的需求。

> 组件顺序以收录时间排序

## 如何提交我的组件？

如果您开发的协程组件适配了 Hyperf，那么您可以直接对 [hyperf/hyperf](https://github.com/hyperf/hyperf) 项目的 `master` 分支发起您的 `Pull Request`，也就是更改当前页`(zh-cn/awesome-components.md)`。

## 如何适配 Hyperf ?

我们为您提供了一份 [Hyperf 组件开发指南](zh-cn/component-guide/intro.md)，以帮助您开发 Hyperf 组件或适配 Hyperf 框架。

# 组件列表

## 路由

- [nikic/fastroute](https://github.com/nikic/FastRoute) 一个常用的高速路由
- [lazychanger/urlrewrite](https://github.com/lazychanger/urlrewrite) 一个基于 PSR- 7 与 [nikic/fastroute](https://github.com/nikic/FastRoute) 相同路由规则的 URL 重写工具

## 事件

- [hyperf/event](https://github.com/hyperf/event) Hyperf 官方提供的基于 PSR-14 的事件管理器

## 日志

- [hyperf/logger](https://github.com/hyperf/logger) Hyperf 官方提供的基于 PSR-3 的日志管理器，一个基于 monolog 的抽象及封装

## 命令

- [hyperf/command](https://github.com/hyperf/command) Hyperf 官方提供的基于 [symfony/console](https://github.com/symfony/console) 扩展并支持注解的命令管理组件
- [symfony/console](https://github.com/symfony/console) Symfony 提供的独立命令管理组件

## 数据库

- [hyperf/database](https://github.com/hyperf/database) Hyperf 官方提供的基于 Eloquent 衍生的数据库 ORM，可复用于其它框架
- [hyperf/model-cache](https://github.com/hyperf/model-cache) Hyperf 官方提供的基于 [hyperf/database](https://github.com/hyperf/database) 组件的自动模型缓存组件
- [reasno/fastmongo](https://github.com/Reasno/fastmongo) 基于 `hyperf/gotask` 实现的协程化 `MongoDB` 客户端
- [hyperf-ext/translatable](https://github.com/hyperf-ext/translatable) 为模型提供多语言能力
- [233cy/hyperf-tenant](https://github.com/233cy/hyperf-tenant) 为模型提供多租户字段区分

## 依赖注入容器

- [hyperf/di](https://github.com/hyperf/di) Hyperf 官方提供的支持注解及 AOP 的依赖注入容器
- [hyperf/pimple](https://github.com/hyperf-cloud/pimple-integration) 基于 `pimple/pimple` 实现的轻量级符合 `PSR11` 规范的容器组件。可以减少其他框架使用 `Hyperf` 组件时的成本

## 服务

- [hyperf/http-server](https://github.com/hyperf/http-server) Hyperf 官方提供的 HTTP 服务端
- [hyperf/grpc-server](https://github.com/hyperf/grpc-server) Hyperf 官方提供的 GRPC 服务端
- [hyperf/websocket-server](https://github.com/hyperf/websocket-server) Hyperf 官方提供的 WebSocket 服务端
- [hyperf/rpc-server](https://github.com/hyperf/rpc-server) Hyperf 官方提供的通用 RPC 抽象服务端

## 客户端

- [hyperf/consul](https://github.com/hyperf/consul) Hyperf 官方提供的 Consul 协程客户端
- [hyperf/elasticsearch](https://github.com/hyperf/elasticsearch) Hyperf 官方提供的 Elasticsearch 协程客户端
- [hyperf/grpc-client](https://github.com/hyperf/grpc-client) Hyperf 官方提供的 gRPC 协程客户端
- [hyperf/etcd](https://github.com/hyperf/etcd) Hyperf 官方提供的 ETCD 协程客户端
- [hyperf/rpc-client](https://github.com/hyperf/rpc-client) Hyperf 官方提供的通用 RPC 抽象协程客户端
- [hyperf/guzzle](https://github.com/hyperf/guzzle) Hyperf 官方提供的 Guzzle HTTP 协程客户端
- [hyperf/redis](https://github.com/hyperf/redis) Hyperf 官方提供的 Redis 协程客户端
- [hyperf/websocket-client](https://github.com/hyperf/websocket-client) Hyperf 官方提供的 WebSocket 协程客户端
- [hyperf/cache](https://github.com/hyperf/cache) Hyperf 官方提供的基于 PSR-16 的缓存协程客户端，支持注解的使用方式
- [chungou/elasticsearch](https://github.com/kaychem/hyperf-elasticsearch) 一个简单的 Elasticsearch 构造器
- [friendsofhyperf/http-client](https://github.com/friendsofhyperf/http-client) 基于 Hyperf 的 Guzzle HTTP 协程客户端
- [friendsofhyperf/openai-client](https://github.com/friendsofhyperf/openai-client) 基于 Hyperf 的 OpenAI 客户端

## 消息队列

- [hyperf/amqp](https://github.com/hyperf/amqp) Hyperf 官方提供的 AMQP 协程组件
- [hyperf/async-queue](https://github.com/hyperf/async-queue) Hyperf 官方提供的简单的基于 Redis 的异步队列组件
- [hooklife/hyperf-aliyun-amqp](https://github.com/hooklife/hyperf-aliyun-amqp) 使 hyperf/amqp 组件支持阿里云 AMQP

## 配置中心

- [hyperf/config-apollo](https://github.com/hyperf/config-apollo) Hyperf 官方提供的 Apollo 配置中心接入组件
- [hyperf/config-aliyun-acm](https://github.com/hyperf/config-aliyun-acm) Hyperf 官方提供的阿里云 ACM 应用配置服务接入组件
- [hyperf/config-etcd](https://github.com/hyperf/config-etcd) Hyperf 官方提供的 ETCD 配置中心接入组件

## RPC

- [hyperf/json-rpc](https://github.com/hyperf/json-rpc) Hyperf 官方提供的 JSON-RPC 协议组件
- [hyperf/rpc-multiplex](https://github.com/hyperf/rpc-multiplex) Hyperf 官方提供的多路复用 RPC 组件
- [hyperf/roc](https://github.com/hyperf/roc) Hyperf 官方提供的 Golang 版本的多路复用 RPC Server 组件
- [limingxinleo/roc-skeleton](https://github.com/limingxinleo/roc-skeleton) Golang 版本多路复用 RPC Server 骨架包

## 服务治理

- [hyperf/rate-limit](https://github.com/hyperf/rate-limit) Hyperf 官方提供的基于令牌桶算法的限流组件
- [hyperf/load-balancer](https://github.com/hyperf/load-balancer) Hyperf 官方提供的负载均衡组件
- [hyperf/service-governance](https://github.com/hyperf/service-governance) Hyperf 官方提供的服务治理组件
- [hyperf/tracer](https://github.com/hyperf/tracer) Hyperf 官方提供的 OpenTracing 分布式调用链追踪组件
- [hyperf/circuit-breaker](https://github.com/hyperf/circuit-breaker) Hyperf 官方提供的服务熔断组件
- [pudongping/hyperf-throttle-requests](https://github.com/pudongping/hyperf-throttle-requests) 适配 Hyperf 框架的请求频率限流器。功能类似于 Laravel 框架的 throttle 中间件。
- [friendsofhyperf/sentry](https://github.com/friendsofhyperf/sentry) 适配 Hyperf 框架的 [Sentry](https://sentry.io) 组件，用于异常监控与性能监控。

## 定时任务

- [hyperf/crontab](https://github.com/hyperf/crontab) Hyperf 官方提供的秒级定时任务组件

## ID 生成器

- [hyperf/snowflake](https://github.com/hyperf/snowflake) Hyperf 官方提供的 Snowflake ID 生成器组件
- [tangwei/snowflake](https://github.com/tw2066/snowflake) 基于`hyperf/snowflake`组件，增强了`工作机器ID`的维护

## 文档生成

- [hyperf/swagger](https://github.com/hyperf/swagger) Hyperf 官方提供的 Swagger 文档自动生成组件 (beta)
- [tangwei/swagger](https://github.com/tw2066/api-docs) 一个基于 PHP 类型(DTO)自动生成 swagger 文档组件，启动自动扫描、自动生成路由(UI)、注解验证

## Graphql

- [hyperf/graphql](https://github.com/hyperf/graphql) Hyperf 官方提供的 Graphql 服务端组件 (beta)

## 热更新/热重载

- [hyperf/watcher](zh-cn/watcher.md) 官方热更新组件
- [ha-ni-cc/hyperf-watch](https://github.com/ha-ni-cc/hyperf-watch) 一个基于 Swoole 实现的通用热更新组件
- [mix-php/swoolefor](https://github.com/mix-php/swoolefor) 一个由 Mixphp 实现的通用热更新组件
- [buexplain/go-watch](https://github.com/buexplain/go-watch) 一个基于 Go 语言实现的通用热更新组件
- [remy/nodemon](https://github.com/remy/nodemon) 一个基于 node.js 实现的通用热更新组件

> Warning: 请勿于生产环境使用 `热更新/热重载` 功能

## Swoole

- [hyperf/task](https://github.com/hyperf/task) Hyperf 官方提供的 Task 组件，对 Swoole 的 Task 机制进行了封装及抽象，提供便捷的注解用法
- [hyperf/gotask](https://github.com/hyperf/gotask) GoTask 通过 Swoole 进程管理功能启动 Go 进程作为 Swoole 主进程边车(Sidecar)，利用进程通讯将任务投递给边车处理并接收返回值。可以理解为 Go 版的 Swoole TaskWorker

## 开发调试

- [firstphp/wsdebug](https://github.com/lamplife/wsdebug) 通过 `WebSocket` 实时观测异常错误的开发调试组件
- [qbhy/hyperf-multi-env](https://github.com/qbhy/hyperf-multi-env) 支持与 laravel 类似的多 env 配置文件功能，比如通过 `APP_ENV=testing` 可以加载 `.env.testing` 配置覆盖默认的 `.env`
- [qiutuleng/hyperf-dump-server](https://github.com/qiutuleng/hyperf-dump-server) 提供一个 `dump` 函数，可以将程序内的变量或数据打印到另一个命令行窗口中，基于 Symfony 的 `Var-Dump Server` 组件
- [leearvin/hyperf-tinker](https://github.com/Arvin-Lee/hyperf-tinker) 基于 PsySH 提供一个交互式的 Hyperf shell 容器
- [friendsofhyperf/telescope](https://github.com/friendsofhyperf/telescope) 适配 Hyperf 框架的调试工具。

## 权限认证

- [fx/hyperf-http-auth](https://github.com/nfangxu/hyperf-http-auth) 根据 laravel 中的 auth 组件改写的, 适配 hyperf 框架
- [96qbhy/hyperf-auth](https://github.com/qbhy/hyperf-auth) 参考 laravel 的 auth 组件设计，支持 jwt、session、sso(单点多设备登录) 驱动
- [hyperf-ext/jwt](https://github.com/hyperf-ext/jwt) JWT 组件，实现了完整用于 JWT 认证的能力
- [hyperf-ext/auth](https://github.com/hyperf-ext/auth) 移植自 `illuminate/auth`，基本完整的实现了 Laravel Auth 的功能特性
- [donjan-deng/hyperf-casbin](https://github.com/donjan-deng/hyperf-casbin) 适配于 Hyperf 的开源访问控制框架 [Casbin](https://casbin.org/docs/zh-CN/overview)

## 测试

- [hyperf/testing](https://github.com/hyperf/testing) Hyperf 官方提供的单元测试组件
- [friendsofhyperf/pest-plugin-hyperf](https://github.com/friendsofhyperf/pest-plugin-hyperf) 适配于 Hyperf 的 [Pest](https://pestphp.com/) 插件，为 Pest 提供协程环境支持

## 分布式锁

- [lysice/hyperf-redis-lock](https://github.com/Lysice/hyperf-redis-lock) 根据 Laravel 的 lock 组件改写，适配于 Hyperf 框架
- [pudongping/hyperf-wise-locksmith](https://github.com/pudongping/hyperf-wise-locksmith) 适配 hyperf 框架的互斥锁库，用于在高并发场景下提供 PHP 代码的有序执行。支持文件锁、分布式锁、红锁、协程级互斥锁

## 分布式事务

- [dtm-php/dtm-client](https://github.com/dtm-php/dtm-client) 支持 Hyperf 的 dtm 分布式事务客户端组件

## 注解配置

- [hyperf-helper/dependency](https://github.com/lazychanger/hyperf-helper-dependency) 使用注解快速的配置依赖关系，并且支持依赖优先级

## 第三方 SDK

- [yurunsoft/pay-sdk](https://github.com/Yurunsoft/PaySDK) 支持 Swoole 协程的支付宝/微信支付 SDK
- [yurunsoft/yurun-oauth-login](https://github.com/Yurunsoft/YurunOAuthLogin) 支持 Swoole 协程的第三方登录授权 SDK（QQ、微信、微博、GitHub、Gitee 等）
- [w7corp/wechat](zh-cn/sdks/wechat) EasyWeChat，一个流行的非官方微信 SDK
- [yansongda/hyperf-pay](https://github.com/yansongda/hyperf-pay) 支持 `支付宝/微信` 的支付组件，基于 [yansongda/pay](https://github.com/yansongda/pay) 实现，适配于 `Hyperf` 框架
- [alapi/hyperf-meilisearch](https://github.com/anhao/hyperf-meilisearch) 为 Hyperf Scout 提供的 meilisearch 客户端
- [vinchan/message-notify](https://github.com/VinchanGit/message-notify) Hyperf 异常监控报警通知组件(钉钉群机器人、飞书群机器人、邮件、QQ 频道机器人、企业微信群机器人)
