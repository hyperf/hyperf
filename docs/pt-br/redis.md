# Redis

## Instalação

```shell
composer require hyperf/redis
```

## Configuração

| Configuração |  Tipo   |   Valor padrão    |   Observação    |
|:------:|:-------:|:-----------:|:---------:|
|  host  | string  | 'localhost' | O host do Redis Server |
|  auth  | string  |     null      |   A senha do Redis Server    |
|  port  | integer |    6379     |   A porta do Redis Server    |
|   db   | integer |      0      |    O DB do Redis Server     |
| cluster.enable | boolean |    false    |          É modo cluster ?          |
|  cluster.name  | string  |    null     |             O nome do cluster             |
| cluster.seeds  |  array  |     []      | Os seeds do cluster, formato: ['host:port'] |
|      pool      | object  |     {}      |           O connection pool           |
|    options     | object  |     {}      |         As opções do Redis Client         |

```php
<?php
return [
    'default' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'auth' => env('REDIS_AUTH', ''),
        'port' => (int) env('REDIS_PORT', 6379),
        'db' => (int) env('REDIS_DB', 0),
        'cluster' => [
            'enable' => (bool) env('REDIS_CLUSTER_ENABLE', false),
            'name' => null,
            'seeds' => [],
        ],
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('REDIS_MAX_IDLE_TIME', 60),
        ],
        'options' => [ // Options of Redis Client, see https://github.com/phpredis/phpredis#setoption
            \Redis::OPT_PREFIX => env('REDIS_PREFIX', ''),
            // or 'prefix' => env('REDIS_PREFIX', ''), v3.0.38 or later
        ],
    ],
];

```

`Publique` o arquivo de configuração completo usando o comando

```shell
php bin/hyperf.php vendor:publish hyperf/redis
```

## Uso

O `hyperf/redis` implementa o proxy da `ext-redis` e o connection pool. Os usuários podem injetar diretamente `\Hyperf\Redis\Redis` através do container de Dependency Injection para usar o client Redis. O que eles realmente obtêm é um proxy do objeto `\Redis`.

```php
<?php

use Hyperf\Context\ApplicationContext;

$container = ApplicationContext::getContainer();

$redis = $container->get(\Hyperf\Redis\Redis::class);
$result = $redis->keys('*');

```

## Configuração de múltiplos recursos

Às vezes, um único recurso `Redis` não é suficiente, e um projeto frequentemente precisa configurar múltiplos recursos. Nesse caso, podemos modificar o arquivo de configuração `redis.php` da seguinte forma:

```php
<?php

return [
    'default' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'auth' => env('REDIS_AUTH', ''),
        'port' => (int) env('REDIS_PORT', 6379),
        'db' => (int) env('REDIS_DB', 0),
        'cluster' => [
            'enable' => (bool) env('REDIS_CLUSTER_ENABLE', false),
            'name' => null,
            'seeds' => [],
        ],
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('REDIS_MAX_IDLE_TIME', 60),
        ],
    ],
    // Add a Redis connection pool named foo
    'foo' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'auth' => env('REDIS_AUTH', ''),
        'port' => (int) env('REDIS_PORT', 6379),
        'db' => 1,
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('REDIS_MAX_IDLE_TIME', 60),
        ],
    ],
];

```

### Uso através de proxy class

Podemos reescrever uma classe `FooRedis` e herdar a classe `Hyperf\Redis\Redis`, e modificar a propriedade `poolName` para o `foo` acima, para completar a troca do connection pool, por exemplo:

```php
<?php

use Hyperf\Redis\Redis;

class FooRedis extends Redis
{
    // The key value of the corresponding Pool
    protected $poolName = 'foo';
}

// Obtain or directly inject the current class through the DI container
$redis = $this->container->get(FooRedis::class);

$result = $redis->keys('*');

```

### Uso através de factory

Quando cada recurso corresponde a um cenário estático, a proxy class é uma boa forma de diferenciar os recursos, mas às vezes a demanda pode ser mais dinâmica. Nesse caso, podemos usar a classe factory `Hyperf\Redis\RedisFactory` para passar dinamicamente o argumento `poolName` e obter o client do connection pool correspondente, sem precisar criar uma proxy class para cada recurso, por exemplo:

```php
<?php
use Hyperf\Redis\RedisFactory;
use Hyperf\Context\ApplicationContext;

$container = ApplicationContext::getContainer();

// Obtain or directly inject the RedisFactory class through the DI container
$redis = $container->get(RedisFactory::class)->get('foo');
$result = $redis->keys('*');
```

## Modo Sentinel

Para habilitar o modo sentinel, você pode modificar o `.env` ou o arquivo de configuração `redis.php` da seguinte forma

Use `;` para separar múltiplos nodes sentinel

```env
REDIS_HOST=
REDIS_AUTH="Redis instance password"
REDIS_PORT=
REDIS_DB=
REDIS_SENTINEL_ENABLE=true
REDIS_SENTINEL_PASSWORD="Redis sentinel password"
REDIS_SENTINEL_NODE=192.168.89.129:26381;192.168.89.129:26380;
```

```php
<?php

return [
    'default' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'auth' => env('REDIS_AUTH', null),
        'port' => (int) env('REDIS_PORT', 6379),
        'db' => (int) env('REDIS_DB', 0),
        'timeout' => 30.0,
        'reserved' => null,
        'retry_interval' => 0,
        'sentinel' => [
            'enable' => (bool) env('REDIS_SENTINEL_ENABLE', false),
            'master_name' => env('REDIS_MASTER_NAME', 'mymaster'),
            'nodes' => explode(';', env('REDIS_SENTINEL_NODE', '')),
            'persistent' => false,
            'read_timeout' => 30.0,
            'auth' =>  env('REDIS_SENTINEL_PASSWORD', ''),
        ],
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('REDIS_MAX_IDLE_TIME', 60),
        ],
    ],
];
```

## Modo Cluster

### Usando `name`

Configure `cluster`, modifique `redis.ini`, ou modifique o `Dockerfile`, como a seguir:

```shell
    # - config PHP
    && { \
        echo "upload_max_filesize=100M"; \
        echo "post_max_size=108M"; \
        echo "memory_limit=1024M"; \
        echo "date.timezone=${TIMEZONE}"; \
        echo "redis.clusters.seeds = \"mycluster[]=localhost:7000&mycluster[]=localhost:7001\""; \
        echo "redis.clusters.timeout = \"mycluster=5\""; \
        echo "redis.clusters.read_timeout = \"mycluster=10\""; \
        echo "redis.clusters.auth = \"mycluster=password\"";
    } | tee conf.d/99-overrides.ini \
```

A configuração PHP correspondente é a seguinte

```php
<?php
// Ignore the other irrelevant configurations
return [
    'default' => [
        'cluster' => [
            'enable' => true,
            'name' => 'mycluster',
            'seeds' => [],
        ],
    ],
];
```

### Usando seeds

É claro, também é possível usar `seeds` diretamente sem configurar o `name`, como a seguir:

```php
<?php
// Ignore the other irrelevant configurations
return [
    'default' => [
        'cluster' => [
            'enable' => true,
            'name' => null,
            'seeds' => [
                '192.168.1.110:6379',
                '192.168.1.111:6379',
            ],
        ],
    ],
];
```

## Options

Os usuários podem modificar `options` para definir as opções de configuração do `Redis`.

Por exemplo, alterar a serialização do `Redis` para serialização `PHP`.

```php
<?php

declare(strict_types=1);

return [
    'default' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'auth' => env('REDIS_AUTH', null),
        'port' => (int) env('REDIS_PORT', 6379),
        'db' => (int) env('REDIS_DB', 0),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('REDIS_MAX_IDLE_TIME', 60),
        ],
        'options' => [
            \Redis::OPT_SERIALIZER => \Redis::SERIALIZER_PHP,
            // or 'serializer' => \Redis::SERIALIZER_PHP, v3.0.38 or later
        ],
    ],
];
```

Por exemplo, definir para o `Redis` nunca expirar o timeout:

```php
<?php

declare(strict_types=1);

return [
    'default' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'auth' => env('REDIS_AUTH', null),
        'port' => (int) env('REDIS_PORT', 6379),
        'db' => (int) env('REDIS_DB', 0),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('REDIS_MAX_IDLE_TIME', 60),
        ],
        'options' => [
            \Redis::OPT_READ_TIMEOUT => -1,
            // or 'read_timeout' => -1, v3.0.38 or later
        ],
    ],
];
```

> Observe que, em algumas versões da extensão `phpredis`, o tipo do valor de `options` precisa ser `string`.
