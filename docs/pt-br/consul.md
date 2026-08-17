# Consul Client

O Hyperf fornece um client [Consul](https://www.consul.io/api/index.html) em coroutine. Como a própria API do Consul é relativamente simples e suporta métodos de requisição HTTP, este componente apenas faz uma abstração da API do Consul, com o suporte de client HTTP em coroutine fornecido pelo [hyperf/guzzle](https://github.com/hyperf/guzzle).

> `ConsulResponse` significa `Hyperf\Consul\ConsulResponse`

## Instalação

```bash
composer require hyperf/consul
```

## KV

A interface `Hyperf\Consul\KVInterface` é implementada por `Hyperf\Consul\KV`.

- get($key, array $options = []): ConsulResponse
- put($key, $value, array $options = []): ConsulResponse
- delete($key, array $options = []): ConsulResponse

## Agent

A interface `Hyperf\Consul\AgentInterface` é implementada por `Hyperf\Consul\Agent`.

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

A interface `Hyperf\Consul\CatalogInterface` é implementada por `Hyperf\Consul\Catalog`.

- register($node): ConsulResponse
- deregister($node): ConsulResponse
- datacenters(): ConsulResponse
- nodes(array $options = []): ConsulResponse
- node($node, array $options = []): ConsulResponse
- services(array $options = []): ConsulResponse
- service($service, array $options = []): ConsulResponse

## Health

A interface `Hyperf\Consul\HealthInterface` é implementada por `Hyperf\Consul\Health`.

- node($node, array $options = []): ConsulResponse
- checks($service, array $options = []): ConsulResponse
- service($service, array $options = []): ConsulResponse
- state($state, array $options = []): ConsulResponse

## Session

A interface `Hyperf\Consul\SessionInterface` é implementada por `Hyperf\Consul\Session`.

- create($body = null, array $options = []): ConsulResponse
- destroy($sessionId, array $options = []): ConsulResponse
- info($sessionId, array $options = []): ConsulResponse
- node($node, array $options = []): ConsulResponse
- all(array $options = []): ConsulResponse
- renew($sessionId, array $options = []): ConsulResponse
