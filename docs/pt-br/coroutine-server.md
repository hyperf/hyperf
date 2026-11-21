# Servidor no estilo corrotina

O Hyperf usa por padrão o [estilo assíncrono do Swoole](https://wiki.swoole.com/#/server/init), que é um modelo multiprocessos, e processos customizados rodam em processos separados.

> Esse tipo vai rodar em modo single-process ao usar SWOOLE_BASE e não usar processos customizados. Você pode verificar a documentação oficial do Swoole para detalhes.

O Hyperf também fornece um serviço no estilo corrotina, que é um modelo single-process, e todos os processos customizados vão rodar em modo corrotina, sem criar processos separados.

Ambos os estilos podem ser escolhidos conforme a necessidade, **mas não é recomendado trocar um serviço existente sem considerar cuidadosamente**.

## Configuração

Modifique o arquivo de configuração `autoload/server.php` e defina `type` como `Hyperf\Server\CoroutineServer::class` para iniciar no estilo corrotina.

```php
<?php

declare(strict_types=1);

use Hyperf\Server\Event;
use Hyperf\Server\Server;

return [
    'type' => Hyperf\Server\CoroutineServer::class,
    'servers' => [
        [
            'name' => 'http',
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 9501,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_REQUEST => [Hyperf\HttpServer\Server::class, 'onRequest'],
            ],
        ],
    ],
];

```

## WebSocket

1. Por conta das diferenças entre o estilo corrotina e o estilo assíncrono, também há diferenças nos callbacks correspondentes, então isso deve ser usado conforme necessário.

Por exemplo, no callback `onReceive`: no estilo assíncrono é `Swoole\Server`, e no estilo corrotina é `Swoole\Coroutine\Server\Connection`.

```php
<?php

declare(strict_types=1);

namespace Hyperf\Contract;

use Swoole\Coroutine\Server\Connection;
use Swoole\Server as SwooleServer;

interface OnReceiveInterface
{
     /**
      * @param Connection|SwooleServer $server
      */
     public function onReceive($server, int $fd, int $reactorId, string $data): void;
}
```

2. A corrotina onde o middleware está localizado só termina quando ocorrer `onClose`.

Como a instância de banco de dados do `Hyperf` é devolvida ao pool de conexões quando a corrotina é destruída, se `Database` for usado no middleware de `WebSocket`, a conexão no pool não será devolvida normalmente.
