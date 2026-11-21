# Logger

O componente `hyperf/logger` é implementado com base em [psr/logger](https://github.com/php-fig/log), e por padrão usa [monolog/monolog](https://github.com/Seldaek/monolog) como driver. Algumas configurações de log já vêm por padrão no projeto `hyperf-skeleton`, e `Monolog\\Handler\\StreamHandler` é usado por padrão. Como o `Swoole` já “corrotinizou” funções como `fopen` e `fwrite`, desde que o parâmetro `useLocking` não seja definido como `true`, o uso em corrotinas é seguro.

## Instalação

```shell
composer require hyperf/logger
```

## Configuração

Algumas configurações de log já vêm por padrão no projeto `hyperf-skeleton`. Por padrão, o arquivo de configuração de log é `config/autoload/logger.php`. Um exemplo:

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
        // O primeiro parâmetro corresponde ao nome do log, e o segundo parâmetro corresponde à chave em config/autoload/logger.php
        $this->logger = $loggerFactory->get('log', 'default');
    }

    public function method()
    {
        // Faz algo.
        $this->logger->info("Sua mensagem de log.");
    }
}
```

## Conceitos básicos sobre monolog

Vamos ver alguns conceitos básicos envolvidos no monolog com o código a seguir:

```php
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

// Cria um Channel. O parâmetro log é o nome do Channel
$log = new Logger('log');

// Cria dois Handlers, correspondentes às variáveis $stream e $fire
$stream = new StreamHandler('test.log', Logger::WARNING);
$fire = new FirePHPHandler();

// Define o formato de tempo como "Y-m-d H:i:s"
$dateFormat = "Y n j, g:i a";
// Define o formato do log como "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
$output = "%datetime%||%channel||%level_name%||%message%||%context%||%extra%\n";
// Cria um Formatter baseado no formato de tempo e no formato de log
$formatter = new LineFormatter($output, $dateFormat);

// Define o Formatter para o Handler
$stream->setFormatter($formatter);

// Adiciona o Handler na fila de Handlers do Channel
$log->pushHandler($stream);
$log->pushHandler($fire);

// Clona um novo canal de log
$log2 = $log->withName('log2');

// Adiciona registros ao log
$log->warning('Foo');

// Adiciona dados extras ao registro
// 1. contexto do log
$log->error('um novo usuário', ['username' => 'daydaygo']);
// 2. processador
$log->pushProcessor(function ($record) {
    $record['extra']['dummy'] = 'hello';
    return $record;
});
$log->pushProcessor(new \Monolog\Processor\MemoryPeakUsageProcessor());
$log->alert('czl');
```

- Primeiro, instancie um `Logger` e defina um nome que corresponde ao `channel`
- Você pode vincular múltiplos `Handler` ao `Logger`. O `Logger` registra o log e repassa para o `Handler` processar
- O `Handler` pode especificar quais logs de **nível de log** devem ser processados, como `Logger::WARNING`, ou processar apenas logs com nível `>=Logger::WARNING`
- Quem formata o log? O `Formatter`. Basta definir o Formatter e vinculá-lo ao `Handler` correspondente
- Quais partes o log inclui: `"%datetime%||%channel||%level_name%||%message%||%context%||%extra%\\n"`
- Diferencie informações extras adicionadas no log via `context` e `extra`: o `context` é especificado pelo usuário ao logar, sendo mais flexível; e o `extra` é adicionado de forma fixa pelo `Processor` vinculado ao `Logger`, sendo mais adequado para coletar **informações comuns**

## Mais usos

### Encapsular a classe `Log`

Às vezes, você pode querer manter o hábito de logging da maioria dos frameworks. Então você pode criar uma classe `Log` em `App` e chamar o método mágico estático `__callStatic` para acessar o `Logger` e os níveis de logging. Vamos demonstrar com código:

> Lembre-se de não relacionar nome e requisição, como vincular `$request_id` como nome do logger. Isso pode fazer com que objetos de log em nível de requisição sejam armazenados na factory, levando a um grave memory leak.

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

Por padrão, um `Channel` chamado `app` é usado para registrar logs. Você também pode usar o método `Log::get($name)` para obter o `Logger` de diferentes `Channels`. O poderoso `Container` pode te ajudar a resolver tudo isso.

### Log no stdout

Por padrão, a saída de log dos componentes do framework é fornecida pela classe de implementação da interface `Hyperf\\Contract\\StdoutLoggerInterface`: `Hyperf\\Framework\\Logger\\StdoutLogger`. Essa implementação apenas imprime informações no `stdout` via `print_r()`, isto é, no `terminal` que inicia o `Hyperf`. Nesse caso, o `monolog` não é usado de fato. E se você quiser usar `monolog` para manter consistência?

Sim — e isso é feito por meio do poderoso `Container`.

- Primeiro, implemente uma classe `StdoutLoggerFactory`. O uso de `Factory` pode ser explicado em mais detalhes no capítulo [Injeção de dependência](pt-br/di.md).

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

- Declare a dependência: o trabalho de `StdoutLoggerInterface` será feito pela classe instanciada pela dependência real `StdoutLoggerFactory`

```php
// config/autoload/dependencies.php
return [
    \Hyperf\Contract\StdoutLoggerInterface::class => \App\StdoutLoggerFactory::class,
];
```

### Saída de logs em formatos diferentes por ambiente

Os usos acima são apenas para o `Logger` no monolog. Vamos ver `Handler` e `Formatter`.

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

- Um `Handler` chamado `default` é configurado por padrão e contém as informações desse `Handler` e do seu `Formatter`
- Ao obter o `Logger`, se o `Handler` não for especificado, a camada inferior automaticamente vinculará o `default(Handler)` ao `Logger`
- Ambiente dev (desenvolvimento): usa `php://stdout` para enviar logs ao `stdout` e define `allowInlineLineBreaks` no `Formatter`, o que facilita visualizar logs multi-linha
- Ambiente não-dev: o log usa `JsonFormatter`, que formata em `json` e facilita o envio para serviços de logs de terceiros

### Rotacionar arquivos de log por data

Se você quiser que o arquivo de log seja rotacionado conforme a data, você pode usar `Monolog\\Handler\\RotatingFileHandler` fornecido pelo `Monolog`. A configuração é a seguinte:

Modifique o arquivo `config/autoload/logger.php`, troque o `Handler` para `Monolog\\Handler\\RotatingFileHandler::class` e altere o campo `stream` para `filename`.

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

Se você quiser um corte mais fino de logs, você também pode estender a classe `Monolog\\Handler\\RotatingFileHandler` e reimplementar o método `rotate()`.

### Configurar múltiplos `Handler`

Usuários podem modificar `handlers` para que o grupo de log correspondente suporte múltiplos `handlers`.
Por exemplo, na configuração a seguir, quando um usuário registra um log com nível maior ou igual a `INFO`, ele será escrito em `hyperf.log` e `hyperf-debug.log`.
Quando um usuário registra um log `DEBUG`, ele será escrito apenas em `hyperf-debug.log`.

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
