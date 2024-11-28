English | [中文](./README-CN.md)

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

# Introduction

Hyperf is an extremely performant and flexible PHP CLI framework, powered by a state-of-the-art coroutine server and a large number of battle-tested components. Aside from decisively beating PHP-FPM frameworks in benchmarks, Hyperf is unique in its focus on flexibility and composition. Hyperf ships with an AOP-enabling (aspect-oriented programming) dependency injector to ensure components and classes are pluggable and meta-programmable. All of Hyperf's core components strictly follow [PSR](https://www.php-fig.org/psr) standards and can be used in other frameworks.

Hyperf's architecture is built using a combination of `Coroutines`, `Dependency injection`, `Events`, `Annotations`, and `AOP`. In addition to providing `MySQL`, `Redis` and other common coroutine clients, `Hyperf` also provides coroutine compatible versions of `WebSocket server / client`, `JSON RPC server / client`, `gRPC server / client`, `Zipkin/Jaeger (OpenTracing) client`, `Guzzle HTTP client`, `Elasticsearch client`, `Consul client`, `ETCD client`, `AMQP component`, `Apollo configuration center`, `Aliyun ACM`, `ETCD configuration center`, `Token bucket algorithm-based limiter`, `Universal connection pool`, `Circuit breaker`, `Swagger`, `Snowflake`, `Simply Redis MQ`, `RabbitMQ`, `NSQ`, `Nats`, `Seconds level crontab`, `Custom Processes`, etc. Therefore, developers can entirely avoid implementing coroutine compatible versions of these libraries.

Rest assured, Hyperf is still a PHP framework. Hyperf provides all the packages you expect: `Middleware`, `Event Manager`, `Coroutine-optimized Eloquent ORM` (and Model Cache!), `Translation`, `Validation`, `View engine (Blade/Smarty/Twig/Plates/ThinkTemplate)` and more.

# Origin

Although there are many new PHP frameworks, we still haven't found a framework that matches an elegant design with ultra-high performance, nor have we found a framework that paves the way for PHP microservices. With this vision in mind, we will continue to invest in the future of this framework, and you are welcome to join us in contributing to the open-source development of Hyperf.

# Design Goals

`Hyperspeed + Flexibility = Hyperf`. The equation hidden in our name exhibits Hyperf's founding ambition.  

Hyperspeed: Leveraging `Swoole` and `Swow` coroutines, Hyperf is capable of handling massive amounts of traffic. The Hyperf team made many optimizations to the framework to eliminate every bottleneck between the end-user and our blazing engine.   

Flexibility: We believe our Dependency Injection component is best in class. With the help of `Hyperf DI`, components and classes are all pluggable and meta-programmable. Inversely, all Hyperf components are meant to be shared with the world. Our commitment to PSR standards means that you can use Hyperf components in any compatible framework.

Via these traits, Hyperf has discovered the untapped potential in many fields: implementing Web servers, gateway servers, distributed middleware software, microservices architecture, game servers, and Internet-of-Things (IoT).

# Operating environment

- Linux, OS X or Cygwin, WSL, Windows
- PHP 8.1+
- Swoole 5.0+ or Swow 1.4+

# Production ready

Alongside our well-maintained, multilingual documentation, a large number of unit tests for each component ensure logical correctness. Before `Hyperf` was released to the public (2019-06-20), it had been privately used by some medium and large Internet companies for multiple services, which have been running without incident for years in harsh production environments.

# Official website and Documentation

Official website [https://hyperf.io](https://hyperf.io)   
Documentation [https://hyperf.wiki](https://hyperf.wiki)

# Security Vulnerabilities

If you discover a security vulnerability within Hyperf, please send an e-mail to the Hyperf Team via group@hyperf.io. All security vulnerabilities will be promptly addressed.

# Code Contributors

This project exists thanks to all the people who contribute. [[Contribute](https://github.com/hyperf/hyperf/graphs/contributors)].
<a href="https://github.com/hyperf/hyperf/graphs/contributors"><img src="https://opencollective.com/hyperf/contributors.svg?width=890&button=false" /></a>

# Financial Contributors

Become a financial contributor and help us sustain our community. [[Contribute](https://hyperf.wiki/#/en/donate)]

Support this project with your organization or company. Your logo will show up here with a link to your website. [[Contribute](https://hyperf.wiki/#/en/donate)]

# Performance

### Aliyun 8 cores 16G ram

command: `wrk -c 1024 -t 8 http://127.0.0.1:9501/`
```bash
Running 10s test @ http://127.0.0.1:9501/
  8 threads and 1024 connections
  Thread Stats   Avg      Stdev     Max   +/- Stdevs
    Latency    10.08ms    6.82ms  56.66ms   70.19%
    Req/Sec    13.17k     5.94k   33.06k    84.12%
  1049478 requests in 10.10s, 190.16MB read
Requests/sec: 103921.49
Transfer/sec:     18.83MB
```

# The Hyperf Ecosystem

- 🧬 [Nano](https://github.com/hyperf/nano) is a zero-config, no skeleton, minimal Hyperf distribution that allows you to quickly build a Hyperf application with just a single PHP file.
- ⚡️ [GoTask](https://github.com/hyperf/gotask) is a library to spawns a go process as a Swoole sidecar and establishes a bi-directional IPC to offload heavy-duties to Go. Think of it as a Swoole Taskworker in Go.
- 🚀 [Jet](https://github.com/hyperf/jet) is a unification model RPC Client, built-in JSONRPC protocol, available to running in ALL PHP environments, including PHP-FPM and Swoole/Hyperf environments. 
- 🧰 [Box](https://github.com/hyperf/box) is committed to helping improve the programming experience of Hyperf applications, managing the PHP environment and related dependencies, providing the ability to package Hyperf applications as binary programs, and also providing reverse proxy services for managing and deploying Hyperf applications.

# Stargazers over time

[![Stargazers over time](https://starchart.cc/hyperf/hyperf.svg)](https://starchart.cc/hyperf/hyperf.svg)

# License

The Hyperf framework is open-source software licensed under the MIT license.
