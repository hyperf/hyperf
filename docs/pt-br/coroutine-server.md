# Coroutine Style Server

O Hyperf usa por padrão o [estilo assíncrono do Swoole](https://wiki.swoole.com/#/server/init), que é um modelo multiprocesso, e os processos customizados são executados em processos separados.

> Esse tipo será executado em modo single-process quando usar SWOOLE_BASE e não usar processos customizados. Você pode consultar a documentação oficial do Swoole para mais detalhes.

O Hyperf também fornece um serviço em estilo coroutine, que é um modelo single-process, e todos os processos customizados serão executados em modo coroutine, sem criar processos separados.

Ambos os estilos podem ser escolhidos conforme a necessidade, **mas não é recomendado alternar para um serviço existente sem nenhuma consideração**.

## Configuração

Modifique o arquivo de configuração `autoload/server.php` e defina `type` como `Hyperf\Server\CoroutineServer::class` para iniciar o estilo coroutine.

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

1. Por causa do estilo coroutine e do estilo assíncrono, há diferenças nos callbacks correspondentes, então é preciso usá-los conforme necessário

Por exemplo, o callback `onReceive`: no estilo assíncrono é `Swoole\Server`, e no estilo coroutine é `Swoole\Coroutine\Server\Connection`.

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

2. A coroutine em que o middleware está só termina no `onClose`

Como a instância de banco de dados do `Hyperf` é retornada ao connection pool quando a coroutine é destruída, se `Database` for usado no middleware do `WebSocket`, a conexão no connection pool não será retornada normalmente.
