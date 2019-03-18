# 介绍

[组件库](https://github.com/hyperf-cloud/hyperf)

Hyperf 是基于 `Swoole 4.3+` 的高性能、高灵活性的 PHP 框架，内置协程服务器，性能较传统基于 `PHP-FPM` 的框架有质的提升，提供超高性能的同时，也保持着极其灵活的可扩展性，标准组件均以最新的 `PSR 协议` 标准实现，基于强大的依赖注入组件设计可确保框架内的绝大部分组件或类都是可替换的。
   
框架组件库内置了协程版的 `Laravel Eloquent ORM`、`GRPC 服务端及客户端`、`Zipkin (OpenTracing) 客户端`、`Guzzle HTTP 客户端`、`Elasticsearch 客户端`、`Consul 客户端`、`AMQP 组件`、`Apollo 配置中心`、`基于令牌桶算法的限流器`、`通用连接池` 等组件的提供也省去了自己去实现对应协程版本的麻烦，满足丰富的技术场景和业务场景。

## 快速入门

- 从零开始: [安装](zh/quick_start/install.md) | [快速开始](zh/quick_start/overview.md)
- 入门教程: 
- 进阶教程: 

## 数据库模型

- 快速开始: [介绍](zh/model/intro.md) | [配置](zh/model/config.md)
- 查询构造器: [查询](zh/model/select.md)
- 模型: [模型](zh/model/model.md) | [模型事件](zh/model/event.md) | [实体关系](zh/model/relation.md)

## 框架核心

- 注解: [概念](zh/annotation/intro.md) | [定义及使用](zh/annotation/usage.md)
- 依赖注入: [概念](zh/annotation/intro.md) | [使用](zh/annotation/usage.md) | [配置](zh/annotation/config.md)
- AOP 面向切面编程: [概念](zh/annotation/intro.md) | [快速开始](zh/annotation/usage.md)

## 微服务

- GRPC 服务
- 服务治理: [服务提供](zh/annotation/service-provider.md) | [定义及使用](zh/annotation/usage.md)

## 其他组件

- [缓存](zh/cache.md)
- [AMQP / RabbitMQ](zh/amqp.md)
- [Elasticsearch](zh/elasticsearch.md)
- [Consul](zh/consul.md)

## 组件开发指南

- 定义 Composer 组件
- Hyperf 框架流程介入