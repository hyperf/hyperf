English | [中文](./README-CN.md)

[![Build Status](https://travis-ci.org/hyperf-cloud/hyperf.svg?branch=master)](https://travis-ci.org/hyperf-cloud/hyperf)
[![Php Version](https://img.shields.io/badge/php-%3E=7.2-brightgreen.svg?maxAge=2592000)](https://secure.php.net/)
[![Swoole Version](https://img.shields.io/badge/swoole-%3E=4.3.3-brightgreen.svg?maxAge=2592000)](https://github.com/swoole/swoole-src)
[![Hyperf License](https://img.shields.io/github/license/hyperf-cloud/hyperf.svg?maxAge=2592000)](https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE.md)



# Introduction

Hyperf is a high-performance, highly flexible PHP CLI framework based on `Swoole 4.3+`. It has a built-in coroutine server and a large number of commonly used components. The performance is better than the traditional PHP-FPM-based framework, providing ultra-high performance, and also maintains extremely flexible scalability at the same time. Standard components are implemented in the latest PSR standards, and a powerful dependency injection design ensures that most components or classes within the framework are replaceable.

In addition to providing `MySQL coroutine client` and `Redis coroutine client`, these common coroutine client, the hyperf component library also prepares the coroutine version of `Eloquent ORM`, `GRPC server and client`, `Zipkin (OpenTracing) client`, `Guzzle HTTP client`, and `Elasticsearch client`, `Consul client`, `ETCD client`, `AMQP component`, `Apollo configuration center`, `token bucket algorithm-based limiter`, `universal connection pool`, etc. Avoids the trouble of implementing the corresponding coroutine version client by yourself. Also provides convenient functions such as `dependency injection`, `annotation`, `AOP (aspect-oriented programming)`, `middleware`, `custom processes`, `event manager`, `simple Redis message queue`, and `full-featured RabbitMQ message queue` to meet a wide range of technical scenarios and business scenarios.

# Original intention

Although there are many new PHP frameworks have been appeared, but our still does not see a perfect framework for the coexistence of elegant design and ultra-high performance, nor does it see a framework that really paves the way for PHP microservices. For the original intention of Hyperf and its team members, we will continue to invest to it, and you are welcome to join us to participate in open source construction.

# Design concept

`Hyperspeed + Flexibility = Hyperf`, from the framework name we have been used `hyperfspeed (ultra-high performance)` and `flexibility` as the gene of Hyperf.

For ultra-high performance, Hyperf based on the Swoole coroutine, and make a lots of optimization on the framework design to ensure ultra-high performance.   
For flexibility, Hyperf based on the powerful dependency injection component of Hyperf, which is based on [PSR](https://www.php-fig.org/psr) and the contracts defined by Hyperf, so that most of the components or classes within the framework are replaceable and re-useable.   
Based on the above characteristics, Hyperf will have a lots of possibilities, such as implementing Web servers, gateway servers, distributed middleware software, microservices architecture, game servers, and Internet of Things (IoT).

# Documentation

[https://doc.hyperf.io/](https://doc.hyperf.io/)