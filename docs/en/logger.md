# Logger

The `hyperf/logger` component is implemented based on [psr/logger](https://github.com/php-fig/log), and [monolog/monolog](https://github.com/Seldaek/monolog) is used by default as a driver. Some log configurations are provided by default in the `hyperf-skeleton` project, and `Monolog\Handler\StreamHandler` is used by default. Since `Swoole` has already coroutineized functions such as `fopen`, `fwrite`, so long as the `useLocking` parameter is not set to `true`, the coroutine is safe.

## Installation

```
composer require hyperf/logger
```

## Configuration

Some log configurations are provided by default in the `hyperf-skeleton` project. By default, the log configuration file is `config/autoload/logger.php`. An example is as follows:

```php
<?php

return [
    'default' => [
        'handler' => [
            'class' => \Monolog\Handler\StreamHandler::class,
            'constructor' => [
                'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
                'level' => \Monolog\Logger::DEBUG,
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

## Instruction for use

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

## Basic knowledge about monolog

Let's take a look at some of the basic concepts involved in monolog with the following code:

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

- Firstly, instantiate a `Logger` and take a name which corresponds to `channel`
- You can bind multiple `Handler` to `Logger`. `Logger` performs log, and hand it over to `Handler` for processing
- `Handler` can specify which **log level** logs need to be processed, such as `Logger::WARNING` or only process logs with log level `>=Logger::WARNING`
- Who will format the log? The `Formatter` will. Just set the Formatter and bind it to the corresponding `Handler`
- What parts of the log included: `"%datetime%||%channel||%level_name%||%message%||%context%||%extra%\n"`
- Distinguish the extra information added in the log `context` and `extra`: The `context` is additionally specified by the user when logging, which is more flexible; And the `extra` is fixedly added by the `Processor` bound to the `Logger`, which is more suitable for collecting some **common information**

## More usage

### Encapsulate the `Log` class

Sometimes, you may wish to keep the habit of logging in most frameworks. Then you can create a `Log` class under `App`, and call the magic static method `__callStatic` to access to `Logger` and each Level of logging. Let’s demonstrate through code:

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

By default, a `Channel` named `app` is used to record logs. You can also use the `Log::get($name)` method to obtain the `Logger` of different `Channels`. The powerful `Container` can help you to solve it all

### stdout log

By default, the log output by the framework components is supported by the implementation class of the interface `Hyperf\Contract\StdoutLoggerInterface`, the `Hyperf\Framework\Logger\StdoutLogger`. This implementation class is just to output the relevant information on the `stdout` through `print_r()`, which is the `terminal` that starts `Hyperf`. In this case, `monolog` is not actually used. What if you want to use `monolog` to be consistent?

Absolutely, it is through the powerful `Container`.

- First, implement a `StdoutLoggerFactory` class. The usage of `Factory` can be explained in more detail in the [Dependency Injection](zh-cn/di.md) chapter.

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

- Declare the dependency, the work of `StdoutLoggerInterface` is done by the class instantiated by the actual dependent `StdoutLoggerFactory`

```php
// config/autoload/dependencies.php
return [
    \Hyperf\Contract\StdoutLoggerInterface::class => \App\StdoutLoggerFactory::class,
];
```

### Output different format logs in different environments

So many uses of the above are only for the `Logger` in the monolog. Let's take a look at `Handler` and `Formatter`.

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
                'level' => \Monolog\Logger::INFO,
            ],
        ],
        'formatter' => $formatter,
    ],
]
```

- A `Handler` named `default` is configured by default, and contains the information of this `Handler` and its `Formatter`
- When obtaining the `Logger`, if the `Handler` is not specified, the bottom layer will automatically bind the `default(Handler)` to the `Logger`
- dev (development) environment: Use `php://stdout` to output logs to `stdout`, and set `allowInlineLineBreaks` in `Formatter`, which is convenient for viewing multi-line logs
- Non-dev environment: The log uses `JsonFormatter`, which will be formatted as `json` and is convenient for delivery to third-party log services

### Rotate log files by date

If you want the log file to be rotated according to the date, you can use the `Monolog\Handler\RotatingFileHandler` provided by `Mongolog`. And the configuration is as follows:

Modify the `config/autoload/logger.php` configuration file, change `Handler` to `Monolog\Handler\RotatingFileHandler::class` and change the `stream` field to `filename`.

```php
<?php

return [
    'default' => [
        'handler' => [
            'class' => Monolog\Handler\RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/hyperf.log',
                'level' => Monolog\Logger::DEBUG,
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

If you want to perform more fine-grained log cutting, you can also extend the `Monolog\Handler\RotatingFileHandler` class and reimplement the `rotate()` method.

### Configure multiple `Handler`

Users can modify `handlers` so that the corresponding log group can supports multiple `handlers`. 
For example, in the following configuration, when a user posts a log higher the level of `INFO`, it will be written in `hyperf.log` and `hyperf-debug.log`.
When a user posts a `DEBUG` log, the log will be written only in `hyperf-debug.log`.

```php
<?php

declare(strict_types=1);

use Monolog\Handler;
use Monolog\Formatter;
use Monolog\Logger;

return [
    'default' => [
        'handlers' => [
            [
                'class' => Handler\StreamHandler::class,
                'constructor' => [
                    'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
                    'level' => Logger::INFO,
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
                    'level' => Logger::DEBUG,
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

The result is as follows

```
==> runtime/logs/hyperf.log <==
[2019-11-08 11:11:35] hyperf.INFO: 5dc4dce791690 [] []

==> runtime/logs/hyperf-debug.log <==
{"message":"5dc4dce791690","context":[],"level":200,"level_name":"INFO","channel":"hyperf","datetime":{"date":"2019-11-08 11:11:35.597153","timezone_type":3,"timezone":"Asia/Shanghai"},"extra":[]}
{"message":"xxxx","context":[],"level":100,"level_name":"DEBUG","channel":"hyperf","datetime":{"date":"2019-11-08 11:11:35.597635","timezone_type":3,"timezone":"Asia/Shanghai"},"extra":[]}
```
