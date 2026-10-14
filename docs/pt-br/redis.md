# Redis

## Instalação

```shell
composer require hyperf/redis
```

## Configuração

| Config |  Tipo   |   Valor padrão    |   Comentário    |
|:------:|:-------:|:-----------:|:---------:|
|  host  | string  | 'localhost' | Host do servidor Redis |
|  auth  | string  |     null      |   Senha do servidor Redis    |
|  port  | integer |    6379     |   Porta do servidor Redis    |
|   db   | integer |      0      |    DB do servidor Redis     |
| cluster.enable | boolean |    false    |          É modo cluster?          |
|  cluster.name  | string  |    null     |             Nome do cluster             |
| cluster.seeds  |  array  |     []      | Seeds do cluster, formato: ['host:port'] |
|      pool      | object  |     {}      |           Pool de conexões           |
|    options     | object  |     {}      |         Opções do cliente Redis         |

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

Publique o arquivo completo de configuração usando o comando

```shell
php bin/hyperf.php vendor:publish hyperf/redis
```

## Uso

`hyperf/redis` implementa o proxy do `ext-redis` e o pool de conexões. Os usuários podem injetar diretamente `\Hyperf\Redis\Redis` via container de injeção de dependência para usar o cliente Redis. O que se obtém na prática é um proxy do objeto `\Redis`.

```php
<?php

use Hyperf\Context\ApplicationContext;

$container = ApplicationContext::getContainer();

$redis = $container->get(\Hyperf\Redis\Redis::class);
$result = $redis->keys('*');

```

## Configuração com múltiplos recursos

Às vezes, um único recurso `Redis` não atende às necessidades, e um projeto frequentemente precisa configurar múltiplos recursos. Nesse caso, podemos modificar o arquivo de configuração `redis.php` como a seguir:

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

### Usar via classe proxy

Podemos criar uma classe `FooRedis` herdando `Hyperf\Redis\Redis` e modificar a propriedade `poolName` para o `foo` acima, para concluir a troca do pool de conexões. Exemplo:

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

### Usar via factory

Quando cada recurso corresponde a um cenário estático, a classe proxy é uma boa forma de diferenciar os recursos, mas às vezes a necessidade pode ser mais dinâmica. Nesse caso, podemos usar a factory `Hyperf\Redis\RedisFactory` para passar dinamicamente o argumento `poolName` e obter o cliente do pool correspondente sem criar uma classe proxy para cada recurso. Exemplo:

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

Para habilitar o modo sentinel, você pode modificar o `.env` ou o arquivo de configuração `redis.php` como a seguir

Use `;` para separar múltiplos nós sentinel

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

### Usar `name`

Configure `cluster`, modificando `redis.ini` ou `Dockerfile`, como a seguir:

```shell
    # - configurar PHP
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
// Ignorar outras configurações irrelevantes
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

### Usar seeds

Claro, também é possível usar `seeds` diretamente sem configurar `name`, como a seguir:

```php
<?php
// Ignorar outras configurações irrelevantes
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

## Opções

Os usuários podem modificar `options` para definir opções de configuração do `Redis`.

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
            // ou 'serializer' => \Redis::SERIALIZER_PHP, na versão v3.0.38 ou superior
        ],
    ],
];
```

Por exemplo, definir que o `Redis` nunca terá timeout:

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

> Observe que, em algumas versões da extensão `phpredis`, o tipo de valor de `options` precisa ser `string`.
