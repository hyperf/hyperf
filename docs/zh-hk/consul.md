# Consul 協程客户端

Hyperf 提供了一個 [Consul](https://www.consul.io/api/index.html) 的協程客户端，由於 Consul 本身的 API 比較簡單，也支持 HTTP 的請求方法，故該組件僅對 API 進行了一些封裝上的簡化，基於 [hyperf/guzzle](https://github.com/hyperf/guzzle) 提供的協程 HTTP 客户端支持。

> `ConsulResponse` 類指的是 `Hyperf\Consul\ConsulResponse` 類

## 安裝

```bash
composer require hyperf/consul
```

## 使用

- 獲取對應 Consul 客户端，下面以 KV 客户端為例：

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

#### 通過 Header 添加 Token

您可在調用方法時往 Client 傳遞 Key 為 `X-Consul-Token` 的 Header 來設置，如下所示：

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

#### 通過 Query 添加 Token

您也可在調用方法時往 $options 參數傳遞 Key 為 `token` 的參數來設置，這樣 Token 會跟隨 Query 一起傳遞到 Server，如下所示：

```php
$response = $kv->get($namespace, ['token' => 'your-token'])->json();
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
