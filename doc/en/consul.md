# Consul Client

Hyperf provides a coroutine [Consul] (https://www.consul.io/api/index.html) client. Since Consul's own API is relatively simple and supports HTTP request methods, this component is only make some abstraction for The Consul API, and the coroutine HTTP client support provided by [hyperf/guzzle] (https://github.com/hyperf-cloud/guzzle).

> `ConsulResponse` means `Hyperf\Consul\ConsulResponse`

## Installation

```bash
composer require hyperf/consul
```

## KV

The `Hyperf\Consul\KVInterface` interface implemented by `Hyperf\Consul\KV`.

- get($key, array $options = []): ConsulResponse
- put($key, $value, array $options = []): ConsulResponse
- delete($key, array $options = []): ConsulResponse

## Agent

The `Hyperf\Consul\AgentInterface` interface implemented by `Hyperf\Consul\Agent`.

- checks(): ConsulResponse
- services(): ConsulResponse
- members(): ConsulResponse
- self(): ConsulResponse
- join($address, array $options = []): ConsulResponse
- forceLeave($node): ConsulResponse
- registerCheck($check): ConsulResponse
- deregisterCheck($checkId): ConsulResponse
- passCheck($checkId, array $options = []): ConsulResponse
- warnCheck($checkId, array $options = []): ConsulResponse
- failCheck($checkId, array $options = []): ConsulResponse
- registerService($service): ConsulResponse
- deregisterService($serviceId): ConsulResponse

## Catalog

The `Hyperf\Consul\CatalogInterface` interface implemented by `Hyperf\Consul\Catalog`.

- register($node): ConsulResponse
- deregister($node): ConsulResponse
- datacenters(): ConsulResponse
- nodes(array $options = []): ConsulResponse
- node($node, array $options = []): ConsulResponse
- services(array $options = []): ConsulResponse
- service($service, array $options = []): ConsulResponse

## Health

The `Hyperf\Consul\HealthInterface` interface implemented by `Hyperf\Consul\Health`.

- node($node, array $options = []): ConsulResponse
- checks($service, array $options = []): ConsulResponse
- service($service, array $options = []): ConsulResponse
- state($state, array $options = []): ConsulResponse

## Session

The `Hyperf\Consul\SessionInterface` interface implemented by `Hyperf\Consul\Session`.

- create($body = null, array $options = []): ConsulResponse
- destroy($sessionId, array $options = []): ConsulResponse
- info($sessionId, array $options = []): ConsulResponse
- node($node, array $options = []): ConsulResponse
- all(array $options = []): ConsulResponse
- renew($sessionId, array $options = []): ConsulResponse