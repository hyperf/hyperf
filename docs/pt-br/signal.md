# Manipulador de sinais

O signal handler irá escutar o processo `Worker` e os processos `custom` e se registrar automaticamente no signal manager após iniciar.

## Instalação

```
composer require hyperf/signal
```

## Publicar configuração

Você pode publicar o arquivo de configuração padrão no seu projeto com o comando abaixo:

```bash
php bin/hyperf.php vendor:publish hyperf/signal
```

## Adicionar handler

No exemplo abaixo, escutamos o sinal `SIGTERM` do processo `Worker` e imprimimos o valor do sinal quando ele é recebido.

```php
<?php

declare(strict_types=1);

namespace App\Signal;

use Hyperf\Signal\Annotation\Signal;
use Hyperf\Signal\SignalHandlerInterface;

#[Signal]
class TermSignalHandler implements SignalHandlerInterface
{
    public function listen(): array
    {
        return [
            [SignalHandlerInterface::WORKER, SIGTERM],
        ];
    }

    public function handle(int $signal): void
    {
        var_dump($signal);
    }
}

```

Como o sinal SIGTERM recebido pelo processo Worker é capturado, ele não consegue encerrar normalmente. Então o usuário pode simplesmente usar `Ctrl + C` para sair, ou modificar `config/autoload/signal.php` conforme abaixo:

> WorkerStopHandler não é adequado para CoroutineServer; implemente você mesmo se necessário.

```php
<?php

declare(strict_types=1);

return [
    'handlers' => [
        Hyperf\Signal\Handler\WorkerStopHandler::class => PHP_INT_MIN
    ],
    'timeout' => 5.0,
];
```

Depois que o `WorkerStopHandler` for acionado, ele fechará o processo atual após o tempo configurado em [max_wait_time](https://wiki.swoole.com/#/server/setting?id=max_wait_time).

## Exemplo de configuração de listeners para serviço no estilo corrotina

> Os listeners padrão acima são adaptados a serviços no estilo assíncrono. Se você precisar usá-los em serviços no estilo corrotina, você pode customizar a configuração conforme abaixo.

```php
<?php

declare(strict_types=1);

namespace App\Kernel\Signal;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Process\ProcessManager;
use Hyperf\Server\ServerManager;
use Hyperf\Signal\SignalHandlerInterface;
use Psr\Container\ContainerInterface;

class CoroutineServerStopHandler implements SignalHandlerInterface
{

    protected ContainerInterface $container;

    protected ConfigInterface $config;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(ConfigInterface::class);
    }

    public function listen(): array
    {
        // There is only one Worker process in the coroutine style, so you only need to monitor the WORKER here.
        return [
            [self::WORKER, SIGTERM],
            [self::WORKER, SIGINT],
        ];
    }

    public function handle(int $signal): void
    {
        ProcessManager::setRunning(false);

        foreach (ServerManager::list() as [$type, $server]) {
            // Cyclically close open services
            $server->shutdown();
        }
    }
}

```

