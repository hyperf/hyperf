# Consul Coroutine Client

Hyperf provides a coroutine client for [Consul](https://www.consul.io/api/index.html). Since the Consul API itself is relatively simple and also supports HTTP request methods, this component only simplifies the encapsulation of the API, based on the coroutine HTTP client support provided by [hyperf/guzzle](https://github.com/hyperf/guzzle).

> `ConsulResponse` class refers to the `Hyperf\Consul\ConsulResponse` class.

## Installation

```bash
composer require hyperf/consul
```

## Usage

- Get the corresponding Consul client. Below is an example of the KV client:

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

#### Adding Token via Header

You can set it by passing a Header with the Key `X-Consul-Token` to the Client when calling the method, as follows:

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

#### Adding Token via Query

You can also set it by passing a parameter with Key `token` to the `$options` parameter when calling the method. This way, the Token will be passed to the Server along with the Query, as follows:

```php
$response = $kv->get($namespace, ['token' => 'your-token'])->json();
```

## KV

Implemented by `Hyperf\Consul\KV` and providing support via `Hyperf\Consul\KVInterface`.

- get($key, array $options = []): ConsulResponse
- put($key, $value, array $options = []): ConsulResponse
- delete($key, array $options = []): ConsulResponse

## Agent

Implemented by `Hyperf\Consul\Agent` and providing support via `Hyperf\Consul\AgentInterface`.

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

Implemented by `Hyperf\Consul\Catalog` and providing support via `Hyperf\Consul\CatalogInterface`.

- register($node): ConsulResponse
- deregister($node): ConsulResponse
- datacenters(): ConsulResponse
- nodes(array $options = []): ConsulResponse
- node($node, array $options = []): ConsulResponse
- services(array $options = []): ConsulResponse
- service($service, array $options = []): ConsulResponse

## Health

Implemented by `Hyperf\Consul\Health` and providing support via `Hyperf\Consul\HealthInterface`.

- node($node, array $options = []): ConsulResponse
- checks($service, array $options = []): ConsulResponse
- service($service, array $options = []): ConsulResponse
- state($state, array $options = []): ConsulResponse

## Session

Implemented by `Hyperf\Consul\Session` and providing support via `Hyperf\Consul\SessionInterface`.

- create($body = null, array $options = []): ConsulResponse
- destroy($sessionId, array $options = []): ConsulResponse
- info($sessionId, array $options = []): ConsulResponse
- node($node, array $options = []): ConsulResponse
- all(array $options = []): ConsulResponse
- renew($sessionId, array $options = []): ConsulResponse
