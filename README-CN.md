[English](./README.md) | 中文

<p align="center"><a href="https://hyperf.io" target="_blank" rel="noopener noreferrer"><img width="70" src="https://cdn.jsdelivr.net/gh/hyperf/hyperf/docs/logo.png" alt="Hyperf Logo"></a></p>

<p align="center">
  <a href="https://github.com/hyperf/hyperf/releases"><img src="https://poser.pugx.org/hyperf/hyperf/v/stable" alt="Stable Version"></a>
  <a href="https://www.php.net"><img src="https://img.shields.io/badge/php-%3E=8.1-brightgreen.svg?maxAge=2592000" alt="Php Version"></a>
  <a href="https://github.com/swoole/swoole-src"><img src="https://img.shields.io/badge/swoole-%3E=5.0-brightgreen.svg?maxAge=2592000" alt="Swoole Version"></a>
  <a href="https://github.com/hyperf/hyperf/blob/master/LICENSE"><img src="https://img.shields.io/github/license/hyperf/hyperf.svg?maxAge=2592000" alt="Hyperf License"></a>
</p>
<p align="center">
  <a href="https://github.com/hyperf/hyperf/actions"><img src="https://github.com/hyperf/hyperf/workflows/PHPUnit%20for%20Hyperf/badge.svg" alt="PHPUnit for Hyperf"></a>
  <a href="https://packagist.org/packages/hyperf/framework"><img src="https://poser.pugx.org/hyperf/framework/downloads" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/hyperf/framework"><img src="https://poser.pugx.org/hyperf/framework/d/monthly" alt="Monthly Downloads"></a>
</p>

# 介绍

Hyperf 是一个高性能、高灵活性的渐进式 PHP 协程框架，内置协程服务器及大量常用的组件，性能较传统基于 `PHP-FPM` 的框架有质的提升，提供超高性能的同时，也保持着极其灵活的可扩展性，标准组件均基于 [PSR 标准](https://www.php-fig.org/psr) 实现，基于强大的依赖注入设计，保证了绝大部分组件或类都是 `可替换` 与 `可复用` 的。

框架组件库除了常见的协程版的 `MySQL 客户端`、`Redis 客户端`，还为您准备了协程版的 `Eloquent ORM`、`WebSocket 服务端及客户端`、`JSON RPC 服务端及客户端`、`gRPC 服务端及客户端`、`Zipkin/Jaeger (OpenTracing) 客户端`、`Guzzle HTTP 客户端`、`Elasticsearch 客户端`、`Consul 客户端`、`ETCD 客户端`、`AMQP 组件`、`NSQ 组件`、`Nats 组件`、`Apollo 配置中心`、`阿里云 ACM 应用配置管理`、`ETCD 配置中心`、`基于令牌桶算法的限流器`、`通用连接池`、`熔断器`、`Swagger 文档生成`、`视图引擎`、`Snowflake 全局 ID 生成器` 等组件，省去了自己实现对应协程版本的麻烦。  

Hyperf 还提供了 `基于 PSR-11 的依赖注入容器`、`注解`、`AOP 面向切面编程`、`基于 PSR-15 的中间件`、`自定义进程`、`基于 PSR-14 的事件管理器`、`Redis/RabbitMQ/NSQ/Nats 消息队列`、`自动模型缓存`、`基于 PSR-16 的缓存`、`Crontab 秒级定时任务`、`Translation 国际化`、`Validation 验证器` 等非常便捷的功能，满足丰富的技术场景和业务场景，开箱即用。

# 框架初衷

尽管现在基于 PHP 语言开发的框架处于一个百家争鸣的时代，但仍旧未能看到一个优雅的设计与超高性能的共存的完美框架，亦没有看到一个真正为 PHP 微服务铺路的框架，此为 Hyperf 及其团队成员的初衷，我们将持续投入并为此付出努力，也欢迎你加入我们参与开源建设。

# 设计理念

`Hyperspeed + Flexibility = Hyperf`，从名字上我们就将 `超高速` 和 `灵活性` 作为 Hyperf 的基因。
   
- 对于超高速，我们基于 Swoole 和 Swow 协程并在框架设计上进行大量的优化以确保超高性能的输出。   
- 对于灵活性，我们基于 Hyperf 强大的依赖注入组件，组件均基于 [PSR 标准](https://www.php-fig.org/psr) 的契约和由 Hyperf 定义的契约实现，达到框架内的绝大部分的组件或类都是可替换的。   

基于以上的特点，Hyperf 将存在丰富的可能性，如实现 Web 服务，网关服务，分布式中间件，微服务架构，游戏服务器，物联网（IOT）等。

# 生产可用

我们为组件进行了大量的单元测试以保证逻辑的正确，目前存在 `1120` 个单测共 `3369` 个断言条件，同时维护了高质量的文档，在 Hyperf 正式对外开放(2019年6月20日)之前，便已经过了严酷的生产环境的考验，我们才正式的对外开放该项目，现在已有很多的大型互联网企业将 Hyperf 部署到了自己的生产环境上并稳定运行。   

# 运行环境

- Linux, OS X or Cygwin, WSL, Windows
- PHP 8.1+
- Swoole 5.0+ or Swow 1.4+

# 安全漏洞

如果您发现 Hyperf 中存在安全漏洞，请发送电子邮件至 Hyperf 官方团队，电子邮件地址为 group@hyperf.io ，所有安全漏洞都会被及时的解决。

# 官网及文档

官网 [https://hyperf.io](https://hyperf.io)   
文档 [https://hyperf.wiki](https://hyperf.wiki)

# 代码贡献者

感谢所有参与 Hyperf 开发的代码贡献者。 [[contributors](https://github.com/hyperf/hyperf/graphs/contributors)]
<a href="https://github.com/hyperf/hyperf/graphs/contributors"><img src="https://opencollective.com/hyperf/contributors.svg?width=890&button=false" /></a>

# 资金赞助方

成为我们的资金赞助方，帮助我们维持我们的社区。 [[赞助](https://hyperf.wiki/#/zh-cn/donate)]

以组织/公司的名义赞助 Hyperf 项目的发展，您的 LOGO 和链接可以呈现在下方。 [[赞助](https://hyperf.wiki/#/zh-cn/donate)]

# 性能

### 阿里云 8 核 16G

命令: `wrk -c 1024 -t 8 http://127.0.0.1:9501/`
```bash
Running 10s test @ http://127.0.0.1:9501/
  8 threads and 1024 connections
  Thread Stats   Avg      Stdev     Max   +/- Stdev
    Latency    10.08ms    6.82ms  56.66ms   70.19%
    Req/Sec    13.17k     5.94k   33.06k    84.12%
  1049478 requests in 10.10s, 190.16MB read
Requests/sec: 103921.49
Transfer/sec:     18.83MB
```

# Hyperf 生态

- 🧬 [Nano](https://github.com/hyperf/nano) 是一款零配置、无骨架、极小化的 Hyperf 发行版，通过 Nano 可以让您仅仅通过 1 个 PHP 文件即可快速搭建一个 Hyperf 应用。
- ⚡️ [GoTask](https://github.com/hyperf/gotask) 是一款可以启动 Go 进程作为 Swoole 主进程边车(Sidecar) 的一个库，利用 IPC 进程通讯将任务投递给边车处理并接收返回值，可以理解为 Go 语言版的 Swoole TaskWorker。
- 🚀 [Jet](https://github.com/hyperf/jet) 是一个统一模型的 RPC 客户端，内置 JSONRPC 协议的适配，该组件可适用于所有的 PHP 环境，包括 PHP-FPM 和 Swoole 或 Hyperf。
- 🧰 [Box](https://github.com/hyperf/box) 致力于帮助提升 Hyperf 应用程序的编程体验，用于管理 PHP 环境和相关依赖项，同时提供将 Hyperf 应用程序打包为二进制程序的能力，还提供反向代理服务来管理和部署 Hyperf 应用程序。

# Star 趋势

[![Star 趋势](https://starchart.cc/hyperf/hyperf.svg)](https://starchart.cc/hyperf/hyperf.svg)

# 开源协议

Hyperf 是一个基于 [MIT 协议](https://github.com/hyperf/hyperf/blob/master/LICENSE) 开源的软件。
