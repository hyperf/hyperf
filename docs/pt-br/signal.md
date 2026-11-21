# Signal handler

O signal handler escuta o processo `Worker` e o processo `custom`, e se registra automaticamente no signal manager após ser iniciado.

## Instalação

```
composer require hyperf/signal
```

## Publicar configuração

Você pode publicar o arquivo de configuração padrão para seu projeto com o seguinte comando:

```bash
php bin/hyperf.php vendor:publish hyperf/signal
```

## Adicionar handler

A seguir, escutamos o sinal `SIGTERM` do processo `Worker`, e imprimimos o valor do sinal quando ele é recebido.

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

Como o sinal SIGTERM recebido pelo processo Worker é capturado, ele não pode ser encerrado normalmente, então o usuário pode diretamente pressionar `Ctrl + C` para saír, ou modificar a configuração `config/autoload/signal.php` da seguinte forma:

> O WorkerStopHandler não é adequado para o CoroutineServer; implemente-o você mesmo se necessário

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

Depois que o `WorkerStopHandler` é disparado, ele encerrará o processo atual após o tempo configurado em [max_wait_time](https://wiki.swoole.com/#/server/setting?id=max_wait_time).

## Exemplo de configuração de listener para serviços em estilo coroutine

> Os listeners padrão acima são todos adaptados para serviços em estilo assíncrono. Se você precisar usá-los em serviços em estilo coroutine, você pode customizar a configuração da seguinte forma

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
