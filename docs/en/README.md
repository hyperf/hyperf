# 3.1 Introduction

Hyperf is a high-performance, highly flexible, progressive PHP coroutine framework. It features a built-in coroutine server and a wide array of commonly used components, offering a substantial performance improvement over traditional PHP-FPM-based frameworks. While delivering exceptional performance, it maintains extreme flexibility and extensibility. Standard components are implemented based on [PSR standards](https://www.php-fig.org/psr), and with its powerful Dependency Injection (DI) design, most components or classes are replaceable and reusable.

The framework's component library includes not only standard coroutine-based `MySQL client` and `Redis client`, but also `Eloquent ORM` (coroutine-optimized), `WebSocket server/client`, `JSON RPC server/client`, `gRPC server/client`, `Zipkin/Jaeger (OpenTracing) client`, `Guzzle HTTP client`, `Elasticsearch client`, `Consul client`, `ETCD client`, `AMQP component`, `Apollo configuration center`, `Aliyun ACM application configuration management`, `ETCD configuration center`, `Rate limiter based on token bucket algorithm`, `General connection pool`, `Circuit breaker`, `Swagger document generation`, `View engine`, `Snowflake global ID generator`, etc., saving you the effort of implementing these coroutine-compatible versions yourself.

Hyperf also provides very convenient features such as `PSR-11 compliant dependency injection container`, `Annotations`, `AOP (Aspect-Oriented Programming)`, `PSR-15 compliant middleware`, `Custom processes`, `PSR-14 compliant event manager`, `Redis/RabbitMQ message queue`, `Automatic model caching`, `PSR-16 compliant caching`, `Crontab second-level scheduled tasks`, `Internationalization (i18n)`, `Validation`, etc., satisfying rich technical and business scenarios, and ready to use out-of-the-box.

# Framework Motivation

Although PHP development frameworks are thriving, we have yet to see a perfect framework that combines elegant design with ultra-high performance, nor one that truly paves the way for PHP microservices. This is the motivation behind Hyperf and its team members. We will continue to invest and put effort into this, and we welcome you to join us in participating in open-source development.

# Design Philosophy

`Hyperspeed + Flexibility = Hyperf`. From the name itself, we have set `ultra-high speed` and `flexibility` as Hyperf's genes.

- For ultra-high speed, we base our design on Swoole coroutines and perform extensive optimizations within the framework to ensure ultra-high performance output.
- For flexibility, we base our design on Hyperf's powerful dependency injection component. Components are implemented based on contracts compliant with PSR standards and contracts defined by Hyperf, ensuring that the vast majority of components or classes within the framework are replaceable.

Based on these characteristics, Hyperf has vast possibilities, such as implementing Web services, gateway services, distributed middleware, microservice architecture, game servers, Internet of Things (IoT), etc.

# Production Ready

We have conducted extensive unit tests for components to ensure logical correctness and have maintained high-quality documentation. Before Hyperf was officially opened to the public, it had already undergone rigorous testing in production environments. We only officially opened the project after these trials. To date, a large number of large/medium/small internet companies use Hyperf in their production environments.
