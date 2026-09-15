# Logger

O componente `hyperf/logger` é implementado com base em [psr/logger](https://github.com/php-fig/log), e por padrão utiliza o [monolog/monolog](https://github.com/Seldaek/monolog) como driver. Algumas configurações de log já são fornecidas por padrão no projeto `hyperf-skeleton`, e o `Monolog\Handler\StreamHandler` é usado por padrão. Como o `Swoole` já corrotinizou funções como `fopen` e `fwrite`, desde que o parâmetro `useLocking` não seja definido como `true`, a Coroutine é segura.

## Instalação

```shell
composer require hyperf/logger
```

## Configuração

Algumas configurações de log são fornecidas por padrão no projeto `hyperf-skeleton`. Por padrão, o arquivo de configuração de log é `config/autoload/logger.php`. Um exemplo é o seguinte:

```php
<?php

return [
    'default' => [
        'handler' => [
            'class' => \Monolog\Handler\StreamHandler::class,
            'constructor' => [
                'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
                'level' => \Monolog\Level::Debug,
            ],
        ],
        'formatter' => [
            'class' => \Monolog\Formatter\LineFormatter::class,
            'constructor' => [
                'format' => null,
                'dateFormat' => null,
                'allowInlineLineBreaks' => true,
            ]
        ],
    ],
];
```

## Instruções de uso

```php
<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;
use Hyperf\Logger\LoggerFactory;

class DemoService
{
    protected LoggerInterface $logger;

    public function __construct(LoggerFactory $loggerFactory)
    {
        // The first parameter corresponds to the name of the log, and the second parameter corresponds to the key in config/autoload/logger.php
        $this->logger = $loggerFactory->get('log', 'default');
    }

    public function method()
    {
        // Do something.
        $this->logger->info("Your log message.");
    }
}
```

## Conceitos básicos sobre o monolog

Vamos analisar alguns dos conceitos básicos envolvidos no monolog através do código a seguir:

```php
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

// Create a Channel. The parameter log is the name of the Channel
$log = new Logger('log');

// Create two Handlers, corresponding to variables $stream and $fire
$stream = new StreamHandler('test.log', Logger::WARNING);
$fire = new FirePHPHandler();

// Define the time format as "Y-m-d H:i:s"
$dateFormat = "Y n j, g:i a";
// Define the log format as "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
$output = "%datetime%||%channel||%level_name%||%message%||%context%||%extra%\n";
// Create a Formatter based on the time format and log format
$formatter = new LineFormatter($output, $dateFormat);

// Set Formatter to Handler
$stream->setFormatter($formatter);

// Push the Handler into the Handler queue of the Channel
$log->pushHandler($stream);
$log->pushHandler($fire);

// Clone new log channel
$log2 = $log->withName('log2');

// Add records to the log
$log->warning('Foo');

// Add extra data to record
// 1. log context
$log->error('a new user', ['username' => 'daydaygo']);
// 2. processor
$log->pushProcessor(function ($record) {
    $record['extra']['dummy'] = 'hello';
    return $record;
});
$log->pushProcessor(new \Monolog\Processor\MemoryPeakUsageProcessor());
$log->alert('czl');
```

- Primeiro, instancie um `Logger` e forneça um nome que corresponde ao `channel`
- Você pode vincular múltiplos `Handler` a um `Logger`. O `Logger` gera o log e o entrega ao `Handler` para processamento
- O `Handler` pode especificar qual **nível de log** precisa ser processado, como `Logger::WARNING`, ou processar apenas logs com nível `>=Logger::WARNING`
- Quem formata o log? O `Formatter`. Basta configurá-lo e vinculá-lo ao `Handler` correspondente
- Quais partes o log inclui: `"%datetime%||%channel||%level_name%||%message%||%context%||%extra%\n"`
- Diferença entre as informações extras adicionadas ao log `context` e `extra`: o `context` é especificado adicionalmente pelo usuário no momento do log, sendo mais flexível; já o `extra` é adicionado de forma fixa pelo `Processor` vinculado ao `Logger`, sendo mais adequado para coletar algumas **informações comuns**

## Mais usos

### Encapsular a classe `Log`

Às vezes, você pode querer manter o hábito de registrar logs presente na maioria dos frameworks. Nesse caso, você pode criar uma classe `Log` dentro de `App`, e usar o método estático mágico `__callStatic` para acessar o `Logger` e cada nível de log. Vamos demonstrar através de código:

> Lembre-se de não vincular o nome do log à requisição, como usar $request_id como nome de logger, pois isso pode fazer com que objetos de log em nível de requisição sejam armazenados na factory, causando um grave vazamento de memória.

```php
namespace App;

use Hyperf\Logger\Logger;
use Hyperf\Context\ApplicationContext;


class Log
{
    public static function get(string $name = 'app')
    {
        return ApplicationContext::getContainer()->get(\Hyperf\Logger\LoggerFactory::class)->get($name);
    }
}

```

Por padrão, um `Channel` chamado `app` é usado para registrar os logs. Você também pode usar o método `Log::get($name)` para obter o `Logger` de diferentes `Channels`. O poderoso `Container` pode ajudar você a resolver tudo isso.

### Log stdout

Por padrão, o log gerado pelos componentes do framework é suportado pela classe de implementação da interface `Hyperf\Contract\StdoutLoggerInterface`, a `Hyperf\Framework\Logger\StdoutLogger`. Essa classe de implementação apenas emite as informações relevantes no `stdout` através de `print_r()`, que é o `terminal` que inicia o `Hyperf`. Nesse caso, o `monolog` não é realmente utilizado. E se você quiser usar o `monolog` para manter a consistência?

Sem dúvida, é possível através do poderoso `Container`.

- Primeiro, implemente uma classe `StdoutLoggerFactory`. O uso de `Factory` pode ser explicado com mais detalhes no capítulo de [Injeção de dependências](pt-br/di.md).

```php
<?php
declare(strict_types=1);

namespace App;

use Psr\Container\ContainerInterface;

class StdoutLoggerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return Log::get('sys');
    }
}
```

- Declare a dependência: o trabalho de `StdoutLoggerInterface` é feito pela classe instanciada pela `StdoutLoggerFactory`, que é a dependência real:

```php
// config/autoload/dependencies.php
return [
    \Hyperf\Contract\StdoutLoggerInterface::class => \App\StdoutLoggerFactory::class,
];
```

### Gerar logs em formatos diferentes em ambientes diferentes

Todos os usos acima referem-se apenas ao `Logger` do monolog. Vamos observar agora o `Handler` e o `Formatter`.

```php
// config/autoload/logger.php
$appEnv = env('APP_ENV', 'dev');
if ($appEnv == 'dev') {
    $formatter = [
        'class' => \Monolog\Formatter\LineFormatter::class,
        'constructor' => [
            'format' => "||%datetime%||%channel%||%level_name%||%message%||%context%||%extra%\n",
            'allowInlineLineBreaks' => true,
            'includeStacktraces' => true,
        ],
    ];
} else {
    $formatter = [
        'class' => \Monolog\Formatter\JsonFormatter::class,
        'constructor' => [],
    ];
}

return [
    'default' => [
        'handler' => [
            'class' => \Monolog\Handler\StreamHandler::class,
            'constructor' => [
                'stream' => 'php://stdout',
                'level' => \Monolog\Level::Info,
            ],
        ],
        'formatter' => $formatter,
    ],
]
```

- Um `Handler` chamado `default` é configurado por padrão, contendo as informações desse `Handler` e do seu `Formatter`
- Ao obter o `Logger`, se o `Handler` não for especificado, a camada inferior vinculará automaticamente o `default(Handler)` ao `Logger`
- Ambiente dev (desenvolvimento): usa `php://stdout` para enviar os logs ao `stdout`, e define `allowInlineLineBreaks` no `Formatter`, o que facilita a visualização de logs de múltiplas linhas
- Ambiente não-dev: o log usa `JsonFormatter`, que será formatado como `json`, facilitando o envio para serviços de log de terceiros

### Rotacionar arquivos de log por data

Se você quiser que o arquivo de log seja rotacionado de acordo com a data, pode usar o `Monolog\Handler\RotatingFileHandler` fornecido pelo `Monolog`. A configuração é a seguinte:

Modifique o arquivo de configuração `config/autoload/logger.php`, alterando o `Handler` para `Monolog\Handler\RotatingFileHandler::class` e o campo `stream` para `filename`.

```php
<?php

return [
    'default' => [
        'handler' => [
            'class' => Monolog\Handler\RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/hyperf.log',
                'level' => Monolog\Level::Debug,
            ],
        ],
        'formatter' => [
            'class' => Monolog\Formatter\LineFormatter::class,
            'constructor' => [
                'format' => null,
                'dateFormat' => null,
                'allowInlineLineBreaks' => true,
            ],
        ],
    ],
];
```

Se você quiser realizar um particionamento de logs mais refinado, também pode estender a classe `Monolog\Handler\RotatingFileHandler` e reimplementar o método `rotate()`.

### Configurar múltiplos `Handler`

Os usuários podem modificar `handlers` para que o grupo de log correspondente suporte múltiplos `handlers`.
Por exemplo, na configuração a seguir, quando um usuário registra um log com nível superior a `INFO`, ele será escrito em `hyperf.log` e `hyperf-debug.log`.
Quando um usuário registra um log `DEBUG`, o log será escrito apenas em `hyperf-debug.log`.

```php
<?php

declare(strict_types=1);

use Monolog\Handler;
use Monolog\Formatter;
use Monolog\Level;

return [
    'default' => [
        'handlers' => [
            [
                'class' => Handler\StreamHandler::class,
                'constructor' => [
                    'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
                    'level' => Level::Info,
                ],
                'formatter' => [
                    'class' => Formatter\LineFormatter::class,
                    'constructor' => [
                        'format' => null,
                        'dateFormat' => null,
                        'allowInlineLineBreaks' => true,
                    ],
                ],
            ],
            [
                'class' => Handler\StreamHandler::class,
                'constructor' => [
                    'stream' => BASE_PATH . '/runtime/logs/hyperf-debug.log',
                    'level' => Level::Info,
                ],
                'formatter' => [
                    'class' => Formatter\JsonFormatter::class,
                    'constructor' => [
                        'batchMode' => Formatter\JsonFormatter::BATCH_MODE_JSON,
                        'appendNewline' => true,
                    ],
                ],
            ],
        ],
    ],
];

```

Ou

```php

declare(strict_types=1);

use Monolog\Handler;
use Monolog\Formatter;
use Monolog\Level;

return [
    'default' => [
        'handlers' => ['single', 'daily'],
    ],

    'single' => [
        'handler' => [
            'class' => Handler\StreamHandler::class,
            'constructor' => [
                'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
                'level' => Level::Info,
            ],
        ],
        'formatter' => [
            'class' => Formatter\LineFormatter::class,
            'constructor' => [
                'format' => null,
                'dateFormat' => null,
                'allowInlineLineBreaks' => true,
            ],
        ],
    ],

    'daily' => [
        'handler' => [
            'class' => Handler\StreamHandler::class,
            'constructor' => [
                'stream' => BASE_PATH . '/runtime/logs/hyperf-debug.log',
                'level' => Level::Info,
            ],
        ],
        'formatter' => [
            'class' => Formatter\JsonFormatter::class,
            'constructor' => [
                'batchMode' => Formatter\JsonFormatter::BATCH_MODE_JSON,
                'appendNewline' => true,
            ],
        ],
    ],
];

```

O resultado é o seguinte

```shell
==> runtime/logs/hyperf.log <==
[2019-11-08 11:11:35] hyperf.INFO: 5dc4dce791690 [] []

==> runtime/logs/hyperf-debug.log <==
{"message":"5dc4dce791690","context":[],"level":200,"level_name":"INFO","channel":"hyperf","datetime":{"date":"2019-11-08 11:11:35.597153","timezone_type":3,"timezone":"Asia/Shanghai"},"extra":[]}
{"message":"xxxx","context":[],"level":100,"level_name":"DEBUG","channel":"hyperf","datetime":{"date":"2019-11-08 11:11:35.597635","timezone_type":3,"timezone":"Asia/Shanghai"},"extra":[]}
```
