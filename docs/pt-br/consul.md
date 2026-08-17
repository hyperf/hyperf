# Cliente Consul

O Hyperf fornece um client de [Consul](https://www.consul.io/api/index.html) com suporte a corrotinas. Como a API do Consul é relativamente simples e suporta métodos de requisições HTTP, este componente apenas cria algumas abstrações para a API do Consul, junto com o suporte ao client HTTP de corrotina fornecido por [hyperf/guzzle](https://github.com/hyperf/guzzle).

> `ConsulResponse` significa `Hyperf\Consul\ConsulResponse`

## Instalação

```bash
composer require hyperf/consul
```

## KV

A interface `Hyperf\Consul\KVInterface`, implementada por `Hyperf\Consul\KV`.

- get($key, array $options = []): ConsulResponse
- put($key, $value, array $options = []): ConsulResponse
- delete($key, array $options = []): ConsulResponse

## Agent

A interface `Hyperf\Consul\AgentInterface`, implementada por `Hyperf\Consul\Agent`.

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

A interface `Hyperf\Consul\CatalogInterface`, implementada por `Hyperf\Consul\Catalog`.

- register($node): ConsulResponse
- deregister($node): ConsulResponse
- datacenters(): ConsulResponse
- nodes(array $options = []): ConsulResponse
- node($node, array $options = []): ConsulResponse
- services(array $options = []): ConsulResponse
- service($service, array $options = []): ConsulResponse

## Health

A interface `Hyperf\Consul\HealthInterface`, implementada por `Hyperf\Consul\Health`.

- node($node, array $options = []): ConsulResponse
- checks($service, array $options = []): ConsulResponse
- service($service, array $options = []): ConsulResponse
- state($state, array $options = []): ConsulResponse

## Session

A interface `Hyperf\Consul\SessionInterface`, implementada por `Hyperf\Consul\Session`.

- create($body = null, array $options = []): ConsulResponse
- destroy($sessionId, array $options = []): ConsulResponse
- info($sessionId, array $options = []): ConsulResponse
- node($node, array $options = []): ConsulResponse
- all(array $options = []): ConsulResponse
- renew($sessionId, array $options = []): ConsulResponse
