# Pool

## Instalação

```bash
composer require hyperf/pool
```

## Por que o pool é necessário?

Quando o nível de concorrência é muito baixo, a conexão pode ser criada temporariamente. No entanto, quando a vazão do serviço chega a centenas ou milhares, `Connect` e `Close` frequentes podem se tornar um gargalo. Na prática, quando o serviço é iniciado, várias conexões podem ser criadas e armazenadas em uma fila. Quando necessário, uma conexão é retirada da fila e usada; depois, ela é devolvida à fila. A estrutura de dados dessa fila é mantida pelo pool de conexões.

## Uso

Para os componentes fornecidos pelo Hyperf, o pool de conexões já foi adaptado, então seu uso é transparente: o Hyperf completa automaticamente a aquisição e a devolução da conexão.

## Pool de conexão customizado

Para definir um pool de conexões, primeiro você precisa implementar uma subclasse que herde `Hyperf\Pool\Pool` e implemente o método abstrato `createConnection`; deve ser retornado um objeto que implemente a interface `Hyperf\Contract\ConnectionInterface`. Um exemplo:

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

Dessa forma, a conexão pode ser obtida e devolvida chamando os métodos `get(): ConnectionInterface` e `release(ConnectionInterface $connection): void` no objeto `MyConnectionPool` instanciado.

## SimplePool

Uma implementação simples de pool é fornecida pelo Hyperf.

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

O pool tem embutida a interface `LowFrequencyInterface`. Por padrão, ele usa o componente de baixa frequência e decide se deve liberar conexões excedentes no pool com base na frequência de aquisição de conexões.

Se precisarmos substituir o componente de baixa frequência correspondente, podemos trocá-lo diretamente na configuração `dependencies`. Tome o componente de banco de dados como exemplo.

```php
<?php

declare(strict_types=1);

namespace App\Pool;

```php
class Frequency extends \Hyperf\Pool\Frequency
{
    /**
     * O intervalo de tempo da frequência calculada
     * @var int
     */
    protected $time = 10;

    /**
     * Limiar (threshold)
     * @var int
     */
    protected $lowFrequency = 5;

    /**
     * Intervalo de tempo mínimo para disparo contínuo de baixa frequência
     * @var int
     */
    protected $lowFrequencyInterval = 60;
}
```

Modifique o mapeamento da seguinte forma:

```php
<?php
return [
    Hyperf\DbConnection\Frequency::class => App\Pool\Frequency::class,
];
```

### Frequência constante

O Hyperf também fornece outro componente de baixa frequência: `ConstantFrequency`.

Quando esse componente é instanciado, um timer é iniciado e o método `Pool::flushOne(false)` é chamado em intervalos regulares. Esse método retira uma conexão do pool e a destrói caso determine que ela ficou ociosa por mais do que um determinado período.

```php
<?php
return [
    Hyperf\DbConnection\Frequency::class => Hyperf\Pool\ConstantFrequency::class,
];
```
