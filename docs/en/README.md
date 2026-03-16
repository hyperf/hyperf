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

# Production ready

Alongside our well-maintained, multilingual documentation, a large number of unit tests for each component ensure logical correctness. Before `Hyperf` was released to the public (2019-06-20), it had been privately used by some medium and large Internet companies for multiple services, which have been running without incident for years in harsh production environments.