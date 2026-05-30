# Consul Client

Hyperf menyediakan client [Consul](https://www.consul.io/api/index.html) berbasis
coroutine. Karena API milik Consul sendiri relatif sederhana dan mendukung
metode request HTTP, komponen ini hanya membuat beberapa abstraksi untuk
Consul API, dan didukung oleh coroutine HTTP client yang disediakan oleh
[hyperf/guzzle](https://github.com/hyperf/guzzle).

> `ConsulResponse` berarti `Hyperf\Consul\ConsulResponse`

## Instalasi

```bash
composer require hyperf/consul
```

## Penggunaan

- Mendapatkan Consul client yang sesuai, berikut adalah contoh untuk KV client:

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

#### Menambahkan Token melalui Header

Anda dapat mengatur parameter Header dengan Key `X-Consul-Token` ke Client saat memanggil method, seperti yang ditunjukkan di bawah ini:

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

#### Menambahkan Token melalui Query

Anda juga dapat mengatur parameter `token` pada argument `$options` saat memanggil method, sehingga Token akan diteruskan ke Server bersama dengan Query, seperti yang ditunjukkan di bawah ini:

```php
$response = $kv->get($namespace, ['token' => 'your-token'])->json();
```

## KV

Interface `Hyperf\Consul\KVInterface` diimplementasikan oleh `Hyperf\Consul\KV`.

- get($key, array $options = []): ConsulResponse
- put($key, $value, array $options = []): ConsulResponse
- delete($key, array $options = []): ConsulResponse

## Agent

Interface `Hyperf\Consul\AgentInterface` diimplementasikan oleh `Hyperf\Consul\Agent`.

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

Interface `Hyperf\Consul\CatalogInterface` diimplementasikan oleh `Hyperf\Consul\Catalog`.

- register($node): ConsulResponse
- deregister($node): ConsulResponse
- datacenters(): ConsulResponse
- nodes(array $options = []): ConsulResponse
- node($node, array $options = []): ConsulResponse
- services(array $options = []): ConsulResponse
- service($service, array $options = []): ConsulResponse

## Health

Interface `Hyperf\Consul\HealthInterface` diimplementasikan oleh `Hyperf\Consul\Health`.

- node($node, array $options = []): ConsulResponse
- checks($service, array $options = []): ConsulResponse
- service($service, array $options = []): ConsulResponse
- state($state, array $options = []): ConsulResponse

## Session

Interface `Hyperf\Consul\SessionInterface` diimplementasikan oleh `Hyperf\Consul\Session`.

- create($body = null, array $options = []): ConsulResponse
- destroy($sessionId, array $options = []): ConsulResponse
- info($sessionId, array $options = []): ConsulResponse
- node($node, array $options = []): ConsulResponse
- all(array $options = []): ConsulResponse
- renew($sessionId, array $options = []): ConsulResponse
