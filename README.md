English | [中文](./README-CN.md)

<p align="center"><a href="https://hyperf.io" target="_blank" rel="noopener noreferrer"><img width="70" src="https://www.hyperf.wiki/logo.png" alt="Hyperf Logo"></a></p>

<p align="center">
  <a href="https://github.com/hyperf/hyperf/releases"><img src="https://poser.pugx.org/hyperf/hyperf/v/stable" alt="Stable Version"></a>
  <a href="https://travis-ci.org/hyperf/hyperf"><img src="https://travis-ci.org/hyperf/hyperf.svg?branch=master" alt="Build Status"></a>
  <a href="https://packagist.org/packages/hyperf/framework"><img src="https://poser.pugx.org/hyperf/framework/downloads" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/hyperf/framework"><img src="https://poser.pugx.org/hyperf/framework/d/monthly" alt="Monthly Downloads"></a>
  <a href="https://www.php.net"><img src="https://img.shields.io/badge/php-%3E=7.2-brightgreen.svg?maxAge=2592000" alt="Php Version"></a>
  <a href="https://github.com/swoole/swoole-src"><img src="https://img.shields.io/badge/swoole-%3E=4.5-brightgreen.svg?maxAge=2592000" alt="Swoole Version"></a>
  <a href="https://github.com/hyperf/hyperf/blob/master/LICENSE"><img src="https://img.shields.io/github/license/hyperf/hyperf.svg?maxAge=2592000" alt="Hyperf License"></a>
</p>

# Introductions

Hyperf is an extremely performant and flexible PHP CLI framework based on `Swoole 4.5+`, powered by the state-of-the-art coroutine server and a large number of battle-tested components. Aside from the decisive benchmark outmatching against PHP-FPM frameworks, Hyperf also distinct itself by its focus on flexibility and composability.  Hyperf ships with an AOP-enabling dependency injector to ensure components and classes are pluggable and meta programmable. All of its core components strictly follow the PSR standards and thus can be used in other frameworks. 

Hyperf's architecture is built upon the combination of `Coroutine`, `Dependency injection`, `Events`, `Annotation`, `AOP (aspect-oriented programming)`. Core components provided by Hyperf can be used out of the box in coroutine context. The set includes but not limited to: `MySQL coroutine client`, `Redis coroutine client`, `WebSocket server and client`, `JSON RPC server and client`, `gRPC server and client`, `Zipkin/Jaeger (OpenTracing) client`, `Guzzle HTTP client`, `Elasticsearch client`, `Consul client`, `ETCD client`, `AMQP component`, `Apollo configuration center`, `Aliyun ACM`, `ETCD configuration center`, `Token bucket algorithm-based limiter`, `Universal connection pool`, `Circuit breaker`, `Swagger`, `Swoole Tracker`, `Snowflake`, `Simply Redis MQ`, `RabbitMQ`, `NSQ`, `Nats`, `Seconds level crontab`, `Custom Processes`, etc. Be assured Hyperf is still a PHP framework. You will also find familiar packages such as `Middleware`, `Event Manager`,  `Coroutine optimized Eloquent ORM` (And Model Cache!), `Translation`, `Validation`, `View engine (Blade/Smarty/Twig/Plates/ThinkTemplate)` and more at your command.

# Origin

Many new PHP frameworks had emerged over the years, yet we were still waiting for one that unites the ultra-performance and elegant design, as well as paving the way for PHP microservice. Hence Hyperf was born to be the pioneer. With this vision in mind, we will continue to invest in it, and you are welcome to join us to participate in open source development.

# Design Goals

`Hyperspeed + Flexibility = Hyperf`. The equation hidden in the name exhibits Hyperf's genetic ambition.  

Hyperspeed: Leveraging Swoole coroutine, Hyperf is capable of handling massive traffic. Hyperf team made many optimizations throughout the framework to eliminate every obstacle between the end-user and the roaring engine.   

Flexibility: Hyperf believes its Dependency Injection component is best in class. With the help of Hyperf DI, components and class are all pluggable and meta programmable. On the other hand, All Hyperf components are mean to be shared interchangeably with the world, as they all follow [PSR](https://www.php-fig.org/psr) or open contracts.

Thanks to these metrits, Hyperf has enabled untapped potential in many areas, such as implementing Web servers, gateway servers, distributed middleware software, microservices architecture, game servers, and Internet of Things (IoT).

# Documentation

[https://hyperf.wiki](https://hyperf.wiki)

# Security Vulnerabilities

If you discover a security vulnerability within Hyperf, please send an e-mail to Hyperf Team via group@hyperf.io. All security vulnerabilities will be promptly addressed.

# Code Contributors

This project exists thanks to all the people who contribute. [[Contribute](https://github.com/hyperf/hyperf/graphs/contributors)].
<a href="https://github.com/hyperf/hyperf/graphs/contributors"><img src="https://opencollective.com/hyperf/contributors.svg?width=890&button=false" /></a>

# Financial Contributors

Become a financial contributor and help us sustain our community. [[Contribute](https://hyperf.wiki/#/en/donate)]

Support this project with your organization or company. Your logo will show up here with a link to your website. [[Contribute](https://hyperf.wiki/#/en/donate)]

## Gold Sponsors

<!--gold start-->
<table>
  <tbody>
    <tr>
      <td align="left" valign="middle">
        <a href="https://guojiang.club/?utm_source=hyperf&utm_campaign=sponsor" target="_blank">
          <img height="110px" src="https://hyperf.wiki/zh-cn/imgs/guojiang-club.jpg">
        </a>
      </td>
    </tr><tr></tr>
  </tbody>
</table>
<!--gold end-->

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

# License

The Hyperf framework is open-source software licensed under the MIT license.
