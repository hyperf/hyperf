[English](./README.md) | 中文

[![Build Status](https://travis-ci.org/hyperf-cloud/hyperf.svg?branch=master)](https://travis-ci.org/hyperf-cloud/hyperf)
<a href="https://packagist.org/packages/hyperf/hyperf"><img src="https://poser.pugx.org/hyperf/hyperf/v/stable.svg" alt="Latest Stable Version"></a>
[![Php Version](https://img.shields.io/badge/php-%3E=7.2-brightgreen.svg?maxAge=2592000)](https://secure.php.net/)
[![Swoole Version](https://img.shields.io/badge/swoole-%3E=4.4-brightgreen.svg?maxAge=2592000)](https://github.com/swoole/swoole-src)
[![Hyperf License](https://img.shields.io/github/license/hyperf-cloud/hyperf.svg?maxAge=2592000)](https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE)

# 介绍

Hyperf 是基于 `Swoole 4.3+` 实现的高性能、高灵活性的 PHP 持久化框架，内置协程服务器及大量常用的组件，性能较传统基于 `PHP-FPM` 的框架有质的提升，提供超高性能的同时，也保持着极其灵活的可扩展性，标准组件均以最新的 [PSR 标准](https://www.php-fig.org/psr) 实现，基于强大的依赖注入设计可确保框架内的绝大部分组件或类都是可替换的。
   
框架组件库除了常见的协程版的 `MySQL 客户端`、`Redis 客户端`，还为您准备了协程版的 `Eloquent ORM`、`JSON RPC 服务端及客户端`、`GRPC 服务端及客户端`、`WebSocket 服务端和客户端`、`Zipkin (OpenTracing) 客户端`、`Guzzle HTTP 客户端`、`Elasticsearch 客户端`、`Consul 客户端`、`ETCD 客户端`、`AMQP 组件`、`基于 Redis 实现的消息队列`、`Apollo 配置中心`、`ETCD 配置中心`、`阿里云 ACM 配置中心`、`基于令牌桶算法的限流器`、`通用连接池`、`熔断器`、`Swagger 文档自动生成`、`Swoole Tracker (Swoole Enterprise)`、`Blade 和 Smarty 视图引擎` 等组件的提供也省去了自己去实现对应协程版本的麻烦。

Hyperf 还提供了 `依赖注入`、`注解`、`AOP 面向切面编程`、`中间件`、`自定义进程`、`事件管理器`、`自动模型缓存`、`Crontab 秒级定时任务` 等非常便捷的功能，满足丰富的技术场景和业务场景，开箱即用。

# 框架初衷

尽管现在基于 PHP 语言开发的框架处于一个百花争鸣的时代，但仍旧没能看到一个优雅的设计与超高性能的共存的完美框架，亦没有看到一个真正为 PHP 微服务铺路的框架，此为 Hyperf 及其团队成员的初衷，我们将持续投入并为此付出努力，也欢迎你加入我们参与开源建设。

# 设计理念

`Hyperspeed + Flexibility = Hyperf`，从名字上我们就将 `超高速` 和 `灵活性` 作为 Hyperf 的基因。
   
- 对于超高速，我们基于 Swoole 协程并在框架设计上进行大量的优化以确保超高性能的输出。   
- 对于灵活性，我们基于 Hyperf 强大的依赖注入组件，组件均基于 [PSR 标准](https://www.php-fig.org/psr) 的契约和由 Hyperf 定义的契约实现，达到框架内的绝大部分的组件或类都是可替换的。   

基于以上的特点，Hyperf 将存在丰富的可能性，如实现 Web 服务，网关服务，分布式中间件，微服务架构，游戏服务器，物联网（IOT）等。

# 运行环境

- Linux, OS X or Cygwin, WSL
- PHP 7.2+
- Swoole 4.4+

# 安全漏洞

如果您发现 Hyperf 中存在安全漏洞，请发送电子邮件至 Hyperf 官方团队，电子邮件地址为 group@hyperf.io ，所有安全漏洞都会被及时的解决。

# 官网及文档

官网 [https://hyperf.io](https://hyperf.io)   
文档 [https://doc.hyperf.io](https://doc.hyperf.io)

# 开源协议

Hyperf 是一个基于 [MIT 协议](https://opensource.org/licenses/MIT) 开源的软件。
