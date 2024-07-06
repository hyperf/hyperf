# Awesome Components

All officially provided component libraries have been coroutined processed, safe to use within Hyperf or other coroutine frameworks. Based on Hyperf's openness and extensibility, the community could develop or adapt a variety of components to this, benefit from this, Hyperf will have unlimited possibilities.

This page will include a variety of Hyperf-compatible coroutine components and commonly used libraries that have been validated and safely used in coroutine, so you could quickly select the right components to complete your needs.

##  How do I submit my component ?

If the component you developed is adapted to Hyperf, then you can send a `Pull Request` directly on the ` master` branch of the [hyperf/hyperf](https://github.com/hyperf/hyperf) project, which is to change the current page `(en/awesome-components.md)`.

## How to adapt Hyperf ?

We have provided you with a [Hyperf component development guide](en/component-guide/intro) to help you develop Hyperf component or adapt component to Hyperf framework.

# Awesome Components

All officially provided component libraries have been coroutined processed, safe to use within Hyperf or other coroutine frameworks. Based on Hyperf's openness and extensibility, the community could develop or adapt a variety of components to this, benefit from this, Hyperf will have unlimited possibilities.

This page will include a variety of Hyperf-compatible coroutine components and commonly used libraries that have been validated and safely used in coroutine, so you could quickly select the right components to complete your needs.

##  How do I submit my component ?

If the component you developed is adapted to Hyperf, then you can send a `Pull Request` directly on the ` master` branch of the [hyperf/hyperf](https://github.com/hyperf/hyperf) project, which is to change the current page `(en/awesome-components.md)`.

## How to adapt Hyperf ?

We have provided you with a [Hyperf component development guide](en/component-guide/intro) to help you develop Hyperf component or adapt component to Hyperf framework.

# Component list

## Route

- [nikic/fastroute](https://github.com/nikic/FastRoute) a commonly used high speed routing
- [lazychanger/urlrewrite](https://github.com/lazychanger/urlrewrite) A URL rewriting tool based on the same routing rules as [nikic/fastroute](https://github.com/nikic/FastRoute) based on PSR-7

## Event

- [hyperf/event](https://github.com/hyperf/event) PSR-14 based event manager provided by Hyperf officially

## Log

- [hyperf/logger](https://github.com/hyperf/logger) PSR-3 based log manager provided by Hyperf officially

## Command

- [hyperf/command](https://github.com/hyperf/command) Command management component based on [symfony/console](https://github.com/symfony/console) extension and support annotation provided by Hyperf officially
- [symfony/console](https://github.com/symfony/console) Independent command management component provided by Symfony

## Database

- [hyperf/database](https://github.com/hyperf/database) Based on the Eloquent database ORM forked by Hyperf, this component can be reused in other frameworks
- [hyperf/model-cache](https://github.com/hyperf/model-cache) [hyperf/database](https://github.com/hyperf/database) component-based automatic model caching component provided by Hyperf officially

## Dependency injection container

- [hyperf/di](https://github.com/hyperf/di) A dependency injection container provided by Hyperf officially, support annotations and AOP

## Server

- [hyperf/http-server](https://github.com/hyperf/http-server) The HTTP server provided by Hyperf officially
- [hyperf/grpc-server](https://github.com/hyperf/grpc-server) The gRPC server provided by Hyperf officially
- [hyperf/websocket-server](https://github.com/hyperf/websocket-server) The WebSocket server provided by Hyperf officially
- [hyperf/rpc-server](https://github.com/hyperf/rpc-server) The abstract RPC server provided by Hyperf officially

## Client

- [hyperf/consul](https://github.com/hyperf/consul) The Consul coroutine client provided by Hyperf officially
- [hyperf/elasticsearch](https://github.com/hyperf/elasticsearch) The Elasticsearch coroutine client provided by Hyperf officially
- [hyperf/grpc-client](https://github.com/hyperf/grpc-client) The gRPC coroutine client provided by Hyperf officially
- [hyperf/rpc-client](https://github.com/hyperf/rpc-client) The abstract RPC coroutine client provided by Hyperf officially
- [hyperf/guzzle](https://github.com/hyperf/guzzle) The Guzzle HTTP coroutine client provided by Hyperf officially
- [hyperf/redis](https://github.com/hyperf/redis) The Redis coroutine client provided by Hyperf officially
- [hyperf/websocket-client](https://github.com/hyperf/websocket-client) The WebSocket coroutine client provided by Hyperf officially
- [hyperf/cache](https://github.com/hyperf/cache) PSR-16-based cache coroutine client provided by Hyperf officially
- [friendsofhyperf/http-client](https://github.com/friendsofhyperf/http-client) The Guzzle HTTP coroutine client based on Hyperf
- [friendsofhyperf/openai-client](https://github.com/friendsofhyperf/openai-client) The OpenAI coroutine client based on Hyperf

## Testing

- [hyperf/testing](https://github.com/hyperf/testing) Hyperf's official unit testing component
- [friendsofhyperf/pest-plugin-hyperf](https://github.com/friendsofhyperf/pest-plugin-hyperf) [Pest](https://pestphp.com/) plugin designed specifically for Hyperf, providing coroutine environment support for Pest.

## Message queue

- [hyperf/amqp](https://github.com/hyperf/amqp) AMQP coroutine component provided by Hyperf officially
- [hyperf/async-queue](https://github.com/hyperf/async-queue) Redis-based asynchronous queue component provided by Hyperf officially

## Configuration center

- [hyperf/config-apollo](https://github.com/hyperf/config-apollo) Apollo Configuration Center Component provided by Hyperf officially
- [hyperf/config-aliyun-acm](https://github.com/hyperf/config-aliyun-acm) Aliyun ACM Application Configuration Service Component provided by Hyperf officially

## Service governance

- [hyperf/json-rpc](https://github.com/hyperf/json-rpc) JSON-RPC protocol component provided by Hyperf officially
- [hyperf/rate-limit](https://github.com/hyperf/rate-limit) Token bucket algorithm-based rate limiter component provided by Hyperf officially
- [hyperf/load-balancer](https://github.com/hyperf/load-balancer) Load balancer component provided by Hyperf officially
- [hyperf/service-governance](https://github.com/hyperf/service-governance) Service governance component provided by Hyperf officially
- [hyperf/tracer](https://github.com/hyperf/tracer) OpenTracing component provided by Hyperf officially
- [hyperf/circuit-breaker](https://github.com/hyperf/circuit-breaker) service circuit breaker component provided by Hyperf officially
- [friendsofhyperf/sentry](https://github.com/friendsofhyperf/sentry) [Sentry](https://sentry.io) component based on Hyperf

## Annotation Configuration

- [hyperf-helper/dependency](https://github.com/lazychanger/hyperf-helper-dependency) Use annotations to quickly configure dependencies and support dependency priority.

## Development and debugging

- [firstphp/wsdebug](https://github.com/lamplife/wsdebug) Development and debugging component for real-time observation of abnormal errors through `WebSocket`
- [qbhy/hyperf-multi-env](https://github.com/qbhy/hyperf-multi-env) supports multi-env configuration file function similar to laravel, such as `APP_ENV=testing` can load `.env .testing` configuration overrides the default `.env`
- [qiutuleng/hyperf-dump-server](https://github.com/qiutuleng/hyperf-dump-server) provides a `dump` function that can print variables or data in the program to another command line window , based on Symfony's `Var-Dump Server` component
- [learvin/hyperf-tinker](https://github.com/Arvin-Lee/hyperf-tinker) Provides an interactive Hyperf shell container based on PsySH
- [friendsofhyperf/telescope](https://github.com/friendsofhyperf/telescope) Debugging tools adapted to the Hyperf