# Consul 協程客户端

Hyperf 提供了一個 [Consul](https://www.consul.io/api/index.html) 的協程客户端，由於 Consul 本身的 API 比較簡單，也支持 HTTP 的請求方法，故該組件僅對 API 進行了一些封裝上的簡化，基於 [hyperf/guzzle](https://github.com/hyperf/guzzle) 提供的協程 HTTP 客户端支持。

> `ConsulResponse` 類指的是 `Hyperf\Consul\ConsulResponse` 類

## 安裝

```bash
composer require hyperf/consul
```

## KV

由 `Hyperf\Consul\KV` 實現 `Hyperf\Consul\KVInterface` 提供支持。

- get($key, array $options = []): ConsulResponse
- put($key, $value, array $options = []): ConsulResponse
- delete($key, array $options = []): ConsulResponse

## Agent

由 `Hyperf\Consul\Agent` 實現 `Hyperf\Consul\AgentInterface` 提供支持。

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

由 `Hyperf\Consul\Catalog` 實現 `Hyperf\Consul\CatalogInterface` 提供支持。

- register($node): ConsulResponse
- deregister($node): ConsulResponse
- datacenters(): ConsulResponse
- nodes(array $options = []): ConsulResponse
- node($node, array $options = []): ConsulResponse
- services(array $options = []): ConsulResponse
- service($service, array $options = []): ConsulResponse

## Health

由 `Hyperf\Consul\Health` 實現 `Hyperf\Consul\HealthInterface` 提供支持。

- node($node, array $options = []): ConsulResponse
- checks($service, array $options = []): ConsulResponse
- service($service, array $options = []): ConsulResponse
- state($state, array $options = []): ConsulResponse

## Session

由 `Hyperf\Consul\Session` 實現 `Hyperf\Consul\SessionInterface` 提供支持。

- create($body = null, array $options = []): ConsulResponse
- destroy($sessionId, array $options = []): ConsulResponse
- info($sessionId, array $options = []): ConsulResponse
- node($node, array $options = []): ConsulResponse
- all(array $options = []): ConsulResponse
- renew($sessionId, array $options = []): ConsulResponse