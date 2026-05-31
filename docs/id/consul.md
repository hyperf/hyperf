# Consul Coroutine Client

Hyperf menyediakan coroutine client untuk [Consul](https://www.consul.io/api/index.html). Karena API Consul sendiri relatif sederhana dan juga mendukung metode permintaan HTTP, komponen ini hanya menyederhanakan enkapsulasi API, berdasarkan dukungan coroutine HTTP client yang disediakan oleh [hyperf/guzzle](https://github.com/hyperf/guzzle).

> Kelas `ConsulResponse` merujuk pada kelas `Hyperf\Consul\ConsulResponse`.

## Instalasi

```bash
composer require hyperf/consul
```

## Penggunaan

- Dapatkan Consul client yang sesuai. Berikut adalah contoh client KV:

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

Anda dapat mengaturnya dengan melewatkan Header dengan Key `X-Consul-Token` ke Client saat memanggil method, sebagai berikut:

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

Anda juga bisa mengaturnya dengan melewatkan parameter `token` ke `$options` saat memanggil method. Dengan begitu, Token akan dikirim ke Server bersama Query:

```php
$response = $kv->get($namespace, ['token' => 'your-token'])->json();
```

## KV

Diimplementasikan oleh `Hyperf\Consul\KV` dan menyediakan dukungan melalui `Hyperf\Consul\KVInterface`.

- get($key, array $options = []): ConsulResponse
- put($key, $value, array $options = []): ConsulResponse
- delete($key, array $options = []): ConsulResponse

## Agent

Diimplementasikan oleh `Hyperf\Consul\Agent` dan menyediakan dukungan melalui `Hyperf\Consul\AgentInterface`.

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

Diimplementasikan oleh `Hyperf\Consul\Catalog` dan menyediakan dukungan melalui `Hyperf\Consul\CatalogInterface`.

- register($node): ConsulResponse
- deregister($node): ConsulResponse
- datacenters(): ConsulResponse
- nodes(array $options = []): ConsulResponse
- node($node, array $options = []): ConsulResponse
- services(array $options = []): ConsulResponse
- service($service, array $options = []): ConsulResponse

## Health

Diimplementasikan oleh `Hyperf\Consul\Health` dan menyediakan dukungan melalui `Hyperf\Consul\HealthInterface`.

- node($node, array $options = []): ConsulResponse
- checks($service, array $options = []): ConsulResponse
- service($service, array $options = []): ConsulResponse
- state($state, array $options = []): ConsulResponse

## Session

Diimplementasikan oleh `Hyperf\Consul\Session` dan menyediakan dukungan melalui `Hyperf\Consul\SessionInterface`.

- create($body = null, array $options = []): ConsulResponse
- destroy($sessionId, array $options = []): ConsulResponse
- info($sessionId, array $options = []): ConsulResponse
- node($node, array $options = []): ConsulResponse
- all(array $options = []): ConsulResponse
- renew($sessionId, array $options = []): ConsulResponse
