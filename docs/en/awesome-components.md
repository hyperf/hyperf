# Coroutine Component Library

All official component libraries have been processed for coroutine support and can be safely used within Hyperf or other coroutine frameworks. Based on the openness and extensibility of Hyperf, the community can develop or adapt a wide variety of components. Thanks to this, Hyperf will have infinite possibilities.
This page will collect various coroutine components adapted for Hyperf and common libraries that have been verified to be safely used in coroutines, so that you can quickly select suitable components to meet your needs from them.

> Component order is sorted by inclusion time.

## How to submit my component?

If the coroutine component you developed is adapted for Hyperf, then you can directly initiate your `Pull Request` to the `master` branch of the [hyperf/hyperf](https://github.com/hyperf/hyperf) project, which is changing the current page (`awesome-components.md`).

## How to adapt to Hyperf?

We provide you with a [Hyperf Component Development Guide](component-guide/intro.md) to help you develop Hyperf components or adapt to the Hyperf framework.

# Component List

## Routing

- [nikic/fastroute](https://github.com/nikic/FastRoute) A commonly used high-speed router.
- [lazychanger/urlrewrite](https://github.com/lazychanger/urlrewrite) A URL rewriting tool based on PSR-7 with the same routing rules as [nikic/fastroute](https://github.com/nikic/FastRoute).

## Events

- [hyperf/event](https://github.com/hyperf/event) Hyperf official PSR-14 based event manager.

## Logging

- [hyperf/logger](https://github.com/hyperf/logger) Hyperf official PSR-3 based log manager, an abstraction and wrapper based on monolog.

## Command

- [hyperf/command](https://github.com/hyperf/command) Hyperf official command management component based on [symfony/console](https://github.com/symfony/console), extended and supporting Annotations.
- [symfony/console](https://github.com/symfony/console) Independent command management component provided by Symfony.

## Database

- [hyperf/database](https://github.com/hyperf/database) Hyperf official database ORM derived from Eloquent, reusable in other frameworks.
- [hyperf/model-cache](https://github.com/hyperf/model-cache) Hyperf official automatic model caching component based on the [hyperf/database](https://github.com/hyperf/database) component.
- [reasno/fastmongo](https://github.com/Reasno/fastmongo) Coroutine-based `MongoDB` client implemented based on `hyperf/gotask`.
- [hyperf-ext/translatable](https://github.com/hyperf-ext/translatable) Provides multi-language capability for models.
- [233cy/hyperf-tenant](https://github.com/233cy/hyperf-tenant) Provides multi-tenant field distinction for models.

## Search Engine

- [hyperf/elasticsearch](https://github.com/hyperf/elasticsearch) Hyperf official Elasticsearch coroutine client.
- [liangguifeng/hyperf-scout-meilisearch](https://github.com/liangguifeng/hyperf-scout-meilisearch) Meilisearch driver adapted to hyperf/scout (refer to laravel/scout).
- [chungou/elasticsearch](https://github.com/kaychem/hyperf-elasticsearch) A simple Elasticsearch builder.

## Dependency Injection Container

- [hyperf/di](https://github.com/hyperf/di) Hyperf official dependency injection container supporting Annotations and AOP.
- [hyperf/pimple](https://github.com/hyperf-cloud/pimple-integration) Lightweight container component conforming to `PSR11` specification implemented based on `pimple/pimple`. It can reduce the cost of using `Hyperf` components in other frameworks.

## Services

- [hyperf/http-server](https://github.com/hyperf/http-server) Hyperf official HTTP server.
- [hyperf/grpc-server](https://github.com/hyperf/grpc-server) Hyperf official GRPC server.
- [hyperf/websocket-server](https://github.com/hyperf/websocket-server) Hyperf official WebSocket server.
- [hyperf/rpc-server](https://github.com/hyperf/rpc-server) Hyperf official universal RPC abstraction server.

## Clients

- [hyperf/consul](https://github.com/hyperf/consul) Hyperf official Consul coroutine client.
- [hyperf/grpc-client](https://github.com/hyperf/grpc-client) Hyperf official gRPC coroutine client.
- [hyperf/etcd](https://github.com/hyperf/etcd) Hyperf official ETCD coroutine client.
- [hyperf/rpc-client](https://github.com/hyperf/rpc-client) Hyperf official universal RPC abstraction coroutine client.
- [hyperf/guzzle](https://github.com/hyperf/guzzle) Hyperf official Guzzle HTTP coroutine client.
- [hyperf/redis](https://github.com/hyperf/redis) Hyperf official Redis coroutine client.
- [hyperf/websocket-client](https://github.com/hyperf/websocket-client) Hyperf official WebSocket coroutine client.
- [hyperf/cache](https://github.com/hyperf/cache) Hyperf official PSR-16 based caching coroutine client, supporting Annotation usage.
- [friendsofhyperf/http-client](https://github.com/friendsofhyperf/http-client) Guzzle HTTP coroutine client based on Hyperf.
- [friendsofhyperf/openai-client](https://github.com/friendsofhyperf/openai-client) OpenAI client based on Hyperf.

## Message Queue

- [hyperf/amqp](https://github.com/hyperf/amqp) Hyperf official AMQP coroutine component.
- [hyperf/async-queue](https://github.com/hyperf/async-queue) Hyperf official simple Redis-based asynchronous queue component.
- [hooklife/hyperf-aliyun-amqp](https://github.com/hooklife/hyperf-aliyun-amqp) Adds support for Alibaba Cloud AMQP to the hyperf/amqp component.

## Configuration Center

- [hyperf/config-apollo](https://github.com/hyperf/config-apollo) Hyperf official Apollo configuration center access component.
- [hyperf/config-aliyun-acm](https://github.com/hyperf/config-aliyun-acm) Hyperf official Alibaba Cloud ACM application configuration service access component.
- [hyperf/config-etcd](https://github.com/hyperf/config-etcd) Hyperf official ETCD configuration center access component.

## RPC

- [hyperf/json-rpc](https://github.com/hyperf/json-rpc) Hyperf official JSON-RPC protocol component.
- [hyperf/rpc-multiplex](https://github.com/hyperf/rpc-multiplex) Hyperf official multiplexing RPC component.
- [hyperf/roc](https://github.com/hyperf/roc) Hyperf official Golang version multiplexing RPC Server component.
- [limingxinleo/roc-skeleton](https://github.com/limingxinleo/roc-skeleton) Golang version multiplexing RPC Server skeleton package.

## Service Governance

- [hyperf/rate-limit](https://github.com/hyperf/rate-limit) Hyperf official rate limiting component based on token bucket algorithm.
- [hyperf/load-balancer](https://github.com/hyperf/load-balancer) Hyperf official load balancing component.
- [hyperf/service-governance](https://github.com/hyperf/service-governance) Hyperf official service governance component.
- [hyperf/tracer](https://github.com/hyperf/tracer) Hyperf official OpenTracing distributed call chain tracing component.
- [hyperf/circuit-breaker](https://github.com/hyperf/circuit-breaker) Hyperf official service circuit breaker component.
- [pudongping/hyperf-throttle-requests](https://github.com/pudongping/hyperf-throttle-requests) Request rate limiter adapted to Hyperf framework. Functionally similar to Laravel framework's throttle middleware.
- [friendsofhyperf/sentry](https://github.com/friendsofhyperf/sentry) [Sentry](https://sentry.io) component adapted to Hyperf framework, used for exception monitoring and performance monitoring.

## Scheduled Tasks

- [hyperf/crontab](https://github.com/hyperf/crontab) Hyperf official second-level scheduled task component.

## ID Generator

- [hyperf/snowflake](https://github.com/hyperf/snowflake) Hyperf official Snowflake ID generator component.
- [tangwei/snowflake](https://github.com/tw2066/snowflake) Based on `hyperf/snowflake` component, enhanced maintenance of `worker machine ID`.

## Document Generation

- [hyperf/swagger](https://github.com/hyperf/swagger) Hyperf official Swagger document automatic generation component (beta).
- [tangwei/swagger](https://github.com/tw2066/api-docs) A swagger document component automatically generated based on PHP type (DTO), supporting startup automatic scanning, automatic routing (UI) generation, and Annotation validation.

## Graphql

- [hyperf/graphql](https://github.com/hyperf/graphql) Hyperf official Graphql server component (beta).

## Hot Update/Hot Reload

- [hyperf/watcher](watcher.md) Official hot update component.
- [ha-ni-cc/hyperf-watch](https://github.com/ha-ni-cc/hyperf-watch) A general hot update component implemented based on Swoole.
- [mix-php/swoolefor](https://github.com/mix-php/swoolefor) A general hot update component implemented by Mixphp.
- [buexplain/go-watch](https://github.com/buexplain/go-watch) A general hot update component implemented based on Go language.
- [remy/nodemon](https://github.com/remy/nodemon) A general hot update component implemented based on node.js.

> Warning: Do not use the `hot update/hot reload` function in a production environment.

## Swoole

- [hyperf/task](https://github.com/hyperf/task) Hyperf official Task component, which encapsulates and abstracts Swoole's Task mechanism and provides convenient Annotation usage.
- [hyperf/gotask](https://github.com/hyperf/gotask) GoTask starts a Go process as a sidecar of the Swoole main process through Swoole process management, uses process communication to post tasks to the sidecar for processing and receives return values. It can be understood as a Go version of Swoole TaskWorker.

## Development and Debugging

- [firstphp/wsdebug](https://github.com/lamplife/wsdebug) Development and debugging component for real-time observation of exceptions and errors via `WebSocket`.
- [qbhy/hyperf-multi-env](https://github.com/qbhy/hyperf-multi-env) Supports multi-env configuration file functionality similar to Laravel, for example, `APP_ENV=testing` can load `.env.testing` configuration to override the default `.env`.
- [qiutuleng/hyperf-dump-server](https://github.com/qiutuleng/hyperf-dump-server) Provides a `dump` function that can print variables or data within the program to another command line window, based on Symfony's `Var-Dump Server` component.
- [leearvin/hyperf-tinker](https://github.com/Arvin-Lee/hyperf-tinker) Provides an interactive Hyperf shell container based on PsySH.
- [friendsofhyperf/telescope](https://github.com/friendsofhyperf/telescope) Debugging tool adapted to Hyperf framework.

## Permission Authentication

- [fx/hyperf-http-auth](https://github.com/nfangxu/hyperf-http-auth) Rewritten based on the auth component in Laravel, adapted to Hyperf framework.
- [96qbhy/hyperf-auth](https://github.com/qbhy/hyperf-auth) Designed referring to Laravel's auth component, supports jwt, session, sso (single point multi-device login) drivers.
- [hyperf-ext/jwt](https://github.com/hyperf-ext/jwt) JWT component, implements full capabilities for JWT authentication.
- [hyperf-ext/auth](https://github.com/hyperf-ext/auth) Ported from `illuminate/auth`, almost fully implements Laravel Auth's functional features.
- [donjan-deng/hyperf-casbin](https://github.com/donjan-deng/hyperf-casbin) Open-source access control framework [Casbin](https://casbin.org/docs/zh-CN/overview) adapted to Hyperf.

## Testing

- [hyperf/testing](https://github.com/hyperf/testing) Hyperf official unit testing component.
- [friendsofhyperf/pest-plugin-hyperf](https://github.com/friendsofhyperf/pest-plugin-hyperf) [Pest](https://pestphp.com/) plugin adapted to Hyperf, providing coroutine environment support for Pest.

## Distributed Lock

- [lysice/hyperf-redis-lock](https://github.com/Lysice/hyperf-redis-lock) Rewritten based on Laravel's lock component, adapted to Hyperf framework.
- [pudongping/hyperf-wise-locksmith](https://github.com/pudongping/hyperf-wise-locksmith) Mutex lock library adapted to Hyperf framework, used to provide orderly execution of PHP code in high-concurrency scenarios. Supports file locks, distributed locks, red locks, coroutine-level mutex locks.

## Distributed Transaction

- [dtm-php/dtm-client](https://github.com/dtm-php/dtm-client) dtm distributed transaction client component supporting Hyperf.

## Annotation Configuration

- [hyperf-helper/dependency](https://github.com/lazychanger/hyperf-helper-dependency) Quickly configure dependencies using Annotations, and support dependency priority.

## DTO

- [fatbit/form-request-param](https://github.com/duncanxia97/hyperf-form-request-param) - Elegant strongly-typed request parameter validation (form validation) and automatic Injection component based on `DTO`.

## Third-party SDK

- [yurunsoft/pay-sdk](https://github.com/Yurunsoft/PaySDK) Alipay/WeChat pay SDK supporting Swoole coroutine.
- [yurunsoft/yurun-oauth-login](https://github.com/Yurunsoft/YurunOAuthLogin) Third-party login authorization SDK supporting Swoole coroutine (QQ, WeChat, Weibo, GitHub, Gitee, etc.).
- [w7corp/wechat](sdks/wechat) EasyWeChat, a popular unofficial WeChat SDK.
- [yansongda/hyperf-pay](https://github.com/yansongda/hyperf-pay) Payment component supporting `Alipay/WeChat`, implemented based on [yansongda/pay](https://github.com/yansongda/pay), adapted to `Hyperf` framework.
- [alapi/hyperf-meilisearch](https://github.com/anhao/hyperf-meilisearch) Meilisearch client provided for Hyperf Scout.
- [vinchan/message-notify](https://github.com/VinchanGit/message-notify) Hyperf exception monitoring alarm notification component (DingTalk group robot, Lark group robot, email, QQ channel robot, Enterprise WeChat group robot).
