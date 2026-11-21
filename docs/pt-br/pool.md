# Pool

## Instalação

```bash
composer require hyperf/pool
```

## Por que o pool é necessário?

Quando a quantidade de concorrência é muito baixa, a conexão pode ser estabelecida temporariamente. No entanto, quando o throughput do serviço atinge centenas ou milhares de magnitude, `Connect` e `Close` frequentes podem se tornar um gargalo do serviço. Na prática, quando o serviço é iniciado, várias conexões podem ser estabelecidas e armazenadas em uma fila. Quando necessário, uma é retirada da fila e usada, e depois retornada à fila após o uso. A estrutura de dados dessa fila é mantida pelo connection pool.

## Uso

Para os componentes fornecidos pelo Hyperf, o connection pool já foi adaptado. Não há necessidade de perceber isso no uso. O Hyperf completa automaticamente a obtenção e o retorno da conexão.

## Connection pool personalizado

Para definir um connection pool, primeiro você precisa implementar uma subclasse que herda de `Hyperf\Pool\Pool` e implementa o método abstrato `createConnection`, devendo retornar um objeto que implemente a interface `Hyperf\Contract\ConnectionInterface`. Um exemplo é mostrado a seguir:
```php
<?php
namespace App\Pool;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Pool\Pool;

class MyConnectionPool extends Pool
{
    public function createConnection(): ConnectionInterface
    {
        return new MyConnection();
    }
}
``` 
Dessa forma, a conexão pode ser obtida e retornada chamando os métodos `get(): ConnectionInterface` e `release(ConnectionInterface $connection): void` na instância `MyConnectionPool` instanciada.

## SimplePool

Uma implementação de pool simples é fornecida pelo hyperf.

```php
<?php

use Hyperf\Pool\SimplePool\PoolFactory;
use Swoole\Coroutine\Http\Client;

$factory = $container->get(PoolFactory::class);

$pool = $factory->get('your pool name', function () use ($host, $port, $ssl) {
    return new Client($host, $port, $ssl);
}, [
    'max_connections' => 50
]);

$connection = $pool->get();

$client = $connection->getConnection(); // The Client which mentioned above.

// Do something.

$connection->release();

```

## Interface de baixa frequência

O pool possui uma interface `LowFrequencyInterface` embutida. O componente de baixa frequência usado por padrão determina se deve liberar conexões excedentes do pool com base na frequência de obtenção de conexões do pool.

Se precisarmos substituir o componente de baixa frequência correspondente, podemos substituí-lo diretamente na configuração `dependencies`. Tomando o componente de banco de dados como exemplo.

```php
<?php

declare(strict_types=1);

namespace App\Pool;

class Frequency extends \Hyperf\Pool\Frequency
{
    /**
     * The time interval of the calculated frequency
     * @var int
     */
    protected $time = 10;

    /**
     * Threshold
     * @var int
     */
    protected $lowFrequency = 5;

    /**
     * Minimum time interval for continuous low frequency triggering
     * @var int
     */
    protected $lowFrequencyInterval = 60;
}

```

Modifique o mapeamento da seguinte forma

```php
<?php
return [
    Hyperf\DbConnection\Frequency::class => App\Pool\Frequency::class,
];
```

### Frequência constante

O Hyperf também fornece outro componente de baixa frequência, o `ConstantFrequency`.

Quando este componente é instanciado, um timer será iniciado e o método `Pool::flushOne(false)` será chamado em um intervalo regular. Este método retirará uma conexão do pool, e uma conexão será destruída quando o método determinar que ela ficou inativa por mais de um determinado período de tempo.

```php
<?php
return [
    Hyperf\DbConnection\Frequency::class => Hyperf\Pool\ConstantFrequency::class,
];
```
