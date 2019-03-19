# 介绍

Hyperf 是基于 `Swoole 4.3+` 实现的高性能、高灵活性的 PHP 持久化框架，内置协程服务器及大量常用的组件，性能较传统基于 `PHP-FPM` 的框架有质的提升，提供超高性能的同时，也保持着极其灵活的可扩展性，标准组件均以最新的 [PSR 标准](https://www.php-fig.org/psr) 实现，基于强大的依赖注入设计可确保框架内的绝大部分组件或类都是可替换的。
   
框架组件库内置了协程版的 `Eloquent ORM`、`GRPC 服务端及客户端`、`Zipkin (OpenTracing) 客户端`、`Guzzle HTTP 客户端`、`Elasticsearch 客户端`、`Consul 客户端`、`ETCD 客户端`、`AMQP 组件`、`Apollo 配置中心`、`基于令牌桶算法的限流器`、`通用连接池` 等组件的提供也省去了自己去实现对应协程版本的麻烦，满足丰富的技术场景和业务场景。

# 框架初衷

尽管现在基于 PHP 语言开发的框架处于一个百花争鸣的时代，但仍旧没能看到一个优雅的设计与超高性能的共存的完美框架，亦没有看到一个真正为 PHP 微服务铺路的框架，此为 Hyperf 及其团队成员的初衷，我们将持续投入并为此付出努力，也欢迎你加入我们参与开源建设。

# 设计理念

`Hyperspeed + Flexibility = Hyperf`   
从名字上我们就将 超高速 和 灵活性 作为 Hyperf 的基因。   
对于超高速，我们基于 Swoole 协程并在框架设计上进行大量的优化以确保超高性能的输出。   
对于灵活性，我们基于 Hyperf 强大的依赖注入组件，组件均基于 PSR标准 的契约和由 Hyperf 定义的契约实现，达到框架内的绝大部分的组件或类都是可替换的。   
基于以上的特点，Hyperf 将存在丰富的可能性，如实现 Web 服务，网关服务，分布式中间件，微服务架构，游戏服务器，物联网（IOT）等。

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
- 配置: [定义及使用](config/usage.md)
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

- [基于 PSR-16 实现的缓存](cache.md)
- [AMQP / RabbitMQ 消息队列](amqp.md)
- [Elasticsearch 客户端](elasticsearch.md)
- [Consul 客户端](consul.md)
- [ETCD 客户端](etcd.md)

## 高级

## 组件开发指南

- 定义 Composer 组件
- Hyperf 框架流程介入