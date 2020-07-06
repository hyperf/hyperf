# Introduction

Hyperf is a high-performance, highly flexible PHP CLI framework based on `Swoole 4.5+`. It has a built-in coroutine server with a large number of commonly used components. It provides ultra-high and better performance than the traditional PHP-FPM-based framework and also maintains extremely flexible scalability at the same time. Standard components are implemented in the latest PSR standards, and a powerful dependency injection design ensures that most components or classes within the framework are replaceable.

In addition to providing `MySQL coroutine client` and `Redis coroutine client`, common coroutine clients, the Hyperf component libraries are also prepared for the coroutine version of `Eloquent ORM`, `GRPC server and client`, `Zipkin (OpenTracing) client`, `Guzzle HTTP client`, `Elasticsearch client`, `Consul client`, `ETCD client`, `AMQP component`, `Apollo configuration center`, `Token bucket algorithm-based limiter`, and `Universal connection pool`, etc. Therefore, the trouble of implementing the corresponding coroutine version client by yourself can be avoided. Hyperf also provides convenient functions such as `Dependency injection`, `Annotation`, `AOP (aspect-oriented programming)`, `Middleware`, `Custom Processes`, `Event Manager`, `Simply Redis message queue`, and `Full-featured RabbitMQ message queue` to meet a wide range of technical and business scenarios.

# Original intention

Although there are many new PHP frameworks have been appeared, but we still has not seen a perfect framework which has the coexistence of elegant design and ultra-high performance, nor would we find a framework that really paves the way for PHP microservices. For the original intention of Hyperf

# Production available

We performed a large number of unit tests on the components to ensure the correctness of the logic, while maintaining high-quality documentation. Before Hyperf officially opened to the public (2019-06-20), it had already launched multiple services in a C-round and a B-round Internet companies, and it has been running perfectly for more than half a year. After the test of the harsh production environment, we officially published this project.
