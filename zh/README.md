# 介绍

[组件库](https://github.com/hyperf-cloud/hyperf)

Hyperf 是基于 `Swoole 4.3+` 的高性能、高灵活性的 PHP 框架，内置协程服务器，性能较传统基于 `PHP-FPM` 的框架有质的提升，提供超高性能的同时，也保持着极其灵活的可扩展性，标准组件均以最新的 [PSR 标准](https://www.php-fig.org/psr) 实现，基于强大的依赖注入设计可确保框架内的绝大部分组件或类都是可替换的。
   
框架组件库内置了协程版的 `Eloquent ORM`、`GRPC 服务端及客户端`、`Zipkin (OpenTracing) 客户端`、`Guzzle HTTP 客户端`、`Elasticsearch 客户端`、`Consul 客户端`、`AMQP 组件`、`Apollo 配置中心`、`基于令牌桶算法的限流器`、`通用连接池` 等组件的提供也省去了自己去实现对应协程版本的麻烦，满足丰富的技术场景和业务场景。

## 快速入门

- 从零开始: [安装](quick_start/install.md) | [快速开始](quick_start/overview.md)
- 入门教程: 
- 进阶教程: 

## 数据库模型

- 快速开始: [介绍](db/intro.md) | [配置](db/config.md)
- 查询构造器: [查询](db/query.md)
- 模型: [模型](db/db.md) | [模型事件](db/event.md) | [实体关系](db/relation.md)

## 框架核心

- 协程: [概念](coroutine/intro.md) | [协程编程指南](coroutine/guide.md)
- 配置: [介绍](config/intro.md) | [定义及使用](config/usage.md)
- 注解: [概念](annotation/intro.md) | [定义及使用](annotation/usage.md)
- 依赖注入: [概念](di/intro.md) | [使用](di/usage.md) | [配置](di/config.md)
- 事件机制: [概念](event/intro.md) | [快速开始](event/usage.md)
- AOP 面向切面编程: [概念](aop/intro.md) | [快速开始](aop/usage.md)

## 微服务

- 微服务架构: [概念](microservice/intro.md)
- GRPC 服务: [服务端](grpc/server.md) | [客户端](grpc/client.md)
- 服务注册: [概念](service-register/intro.md) | [配置及使用](service-register/usage.md)
- 服务熔断和服务降级: [概念](circuit-breaker/intro.md) | [定义及使用](circuit-breaker/usage.md)
- 服务限流: [概念](rate-limit/intro.md) | [定义及使用](rate-limit/usage.md)
- 配置中心: [概念](config-center/intro.md) | [配置及使用](config-center/usage.md)
- 调用链追踪: [概念](tracer/intro.md) | [定义及使用](tracer/usage.md)

## 其他组件

- [缓存](cache.md)
- [AMQP / RabbitMQ](amqp.md)
- [Elasticsearch 客户端](elasticsearch.md)
- [Consul 客户端](consul.md)

## 组件开发指南

- 定义 Composer 组件
- Hyperf 框架流程介入