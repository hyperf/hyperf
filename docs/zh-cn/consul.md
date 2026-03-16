# Consul 协程客户端

Hyperf 提供了一个 [Consul](https://www.consul.io/api/index.html) 的协程客户端，由于 Consul 本身的 API 比较简单，也支持 HTTP 的请求方法，故该组件仅对 API 进行了一些封装上的简化，基于 [hyperf/guzzle](https://github.com/hyperf/guzzle) 提供的协程 HTTP 客户端支持。

> `ConsulResponse` 类指的是 `Hyperf\Consul\ConsulResponse` 类

## 安装

```bash
composer require hyperf/consul
```

## 使用

- 获取对应 Consul 客户端，下面以 KV 客户端为例：

```php
use Hyperf\Consul\KV;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Context\ApplicationContext;

$container = ApplicationContext::getContainer();
$clientFactory = $container->get(ClientFactory::class);

$consulServer = 'http://127.0.0.1:8500';
$kv = new KV(function () use ($clientFactory, $consulServer) {
    return $clientFactory->create([
        'base_uri' => $consulServer,
    ]);
});
```

### Consul ACL Token

#### 通过 Header 添加 Token

您可在调用方法时往 Client 传递 Key 为 `X-Consul-Token` 的 Header 来设置，如下所示：

```php
use Hyperf\Consul\KV;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Context\ApplicationContext;

$container = ApplicationContext::getContainer();
$clientFactory = $container->get(ClientFactory::class);

$consulServer = 'http://127.0.0.1:8500';
$kv = new KV(function () use ($clientFactory, $consulServer) {
    return $clientFactory->create([
        'base_uri' => $consulServer,
        'headers' => [
            'X-Consul-Token' => 'your-token'
        ],
    ]);
});
```

#### 通过 Query 添加 Token

您也可在调用方法时往 $options 参数传递 Key 为 `token` 的参数来设置，这样 Token 会跟随 Query 一起传递到 Server，如下所示：

```php
$response = $kv->get($namespace, ['token' => 'your-token'])->json();
```

## KV

由 `Hyperf\Consul\KV` 实现 `Hyperf\Consul\KVInterface` 提供支持。

- get($key, array $options = []): ConsulResponse
- put($key, $value, array $options = []): ConsulResponse
- delete($key, array $options = []): ConsulResponse

## Agent

由 `Hyperf\Consul\Agent` 实现 `Hyperf\Consul\AgentInterface` 提供支持。

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

由 `Hyperf\Consul\Catalog` 实现 `Hyperf\Consul\CatalogInterface` 提供支持。

- register($node): ConsulResponse
- deregister($node): ConsulResponse
- datacenters(): ConsulResponse
- nodes(array $options = []): ConsulResponse
- node($node, array $options = []): ConsulResponse
- services(array $options = []): ConsulResponse
- service($service, array $options = []): ConsulResponse

## Health

由 `Hyperf\Consul\Health` 实现 `Hyperf\Consul\HealthInterface` 提供支持。

- node($node, array $options = []): ConsulResponse
- checks($service, array $options = []): ConsulResponse
- service($service, array $options = []): ConsulResponse
- state($state, array $options = []): ConsulResponse

## Session

由 `Hyperf\Consul\Session` 实现 `Hyperf\Consul\SessionInterface` 提供支持。

- create($body = null, array $options = []): ConsulResponse
- destroy($sessionId, array $options = []): ConsulResponse
- info($sessionId, array $options = []): ConsulResponse
- node($node, array $options = []): ConsulResponse
- all(array $options = []): ConsulResponse
- renew($sessionId, array $options = []): ConsulResponse
