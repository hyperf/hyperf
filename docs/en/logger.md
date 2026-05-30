# Logger

The `hyperf/logger` component is implemented based on [psr/logger](https://github.com/php-fig/log) and uses [monolog/monolog](https://github.com/Seldaek/monolog) as the default driver. In a `hyperf-skeleton` project, some logger configurations are provided by default, using `Monolog\Handler\StreamHandler`. Since `Swoole` has already coroutine-enabled functions like `fopen` and `fwrite`, it is coroutine-safe as long as the `useLocking` parameter is not set to `true`.

## Installation

```shell
composer require hyperf/logger
```

## Configuration

In a `hyperf-skeleton` project, some logger configurations are provided by default. By default, the configuration file for the logger is `config/autoload/logger.php`, as shown in the following example:

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

## Usage

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
        // The first argument is the name of the log, the second is the key in config/autoload/logger.php
        $this->logger = $loggerFactory->get('log', 'default');
    }

    public function method()
    {
        // Do something.
        $this->logger->info("Your log message.");
    }
}
```

## Basic Monolog Knowledge

Let's combine the code to look at some basic concepts involved in `monolog`:

```php
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

// Create a Channel, the argument 'log' is the name of the Channel
$log = new Logger('log');

// Create two Handlers, corresponding to variables $stream and $fire
$stream = new StreamHandler('test.log', Logger::WARNING);
$fire = new FirePHPHandler();

// Define date format as "Y-m-d H:i:s"
$dateFormat = "Y n j, g:i a";
// Define log format as "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
$output = "%datetime%||%channel||%level_name%||%message%||%context%||%extra%\n";
// Create a Formatter based on the date format and log format
$formatter = new LineFormatter($output, $dateFormat);

// Set the Formatter to the Handler
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

- First, instantiate a `Logger` and give it a name; the name corresponds to the `channel`.
- You can bind multiple `Handlers` to a `Logger`. When the `Logger` records a log, it delegates the processing to the `Handlers`.
- `Handlers` can specify which **log levels** to handle, for example, `Logger::WARNING` will only handle logs with a level `>=Logger::WARNING`.
- Who formats the log? The `Formatter`. Set up the `Formatter` and bind it to the corresponding `Handler`.
- A log consists of: `"%datetime%||%channel||%level_name%||%message%||%context%||%extra%\n"`
- Distinguish between `context` and `extra` added in the log: `context` is specified extra by the user when logging, which is more flexible; `extra` is fixedly added by the `Processor` bound to the `Logger`, which is more suitable for collecting **common information**.

## Advanced Usage

### Encapsulating the `Log` Class

Sometimes you may want to maintain the habits of logging used in most frameworks. In such cases, you can create a `Log` class under `App` and use the `__callStatic` magic method to implement static calls for accessing the `Logger` and recording logs at various levels. Let's demonstrate this with code:

> Remember, when using it, do not let $name be tied to the request. For example, using $request_id as the logger name would cause the Factory to store request-level logger objects, leading to severe memory leaks.

```php
namespace App;

use Hyperf\Logger\LoggerFactory;
use Hyperf\Context\ApplicationContext;

class Log
{
    public static function get(string $name = 'app')
    {
        return ApplicationContext::getContainer()->get(LoggerFactory::class)->get($name);
    }
}
```

By default, it uses the `Channel` named `app` to record logs. You can also get a `Logger` for a different `Channel` using the `Log::get($name)` method. The powerful `Container` handles all of this for you.

### stdout Logging

The logs output by framework components are supported by the `Hyperf\Framework\Logger\StdoutLogger` implementation class of the `Hyperf\Contract\StdoutLoggerInterface` interface by default. This implementation class only outputs relevant information via `print_r()` to `standard output (stdout)`, i.e., the `Terminal` that starts `Hyperf`, which means `monolog` is not actually used. So how can we handle this if we want to use `monolog` to maintain consistency?

Yes, still via the powerful `Container`.

- First, implement a `StdoutLoggerFactory` class. For more details on the usage of `Factory`, please refer to the [Dependency Injection](di.md) chapter.

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

- Declare the dependency. In places where `StdoutLoggerInterface` is used, it will be completed by the class instantiated by the actually dependent `StdoutLoggerFactory`.

```php
// config/autoload/dependencies.php
return [
    \Hyperf\Contract\StdoutLoggerInterface::class => \App\StdoutLoggerFactory::class,
];
```

### Different Log Formats in Different Environments

The usage mentioned above only revolves around the `Logger` in monolog. Let's take a look at `Handler` and `Formatter`.

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
];
```

- A `Handler` named `default` is configured by default, which includes information about this `Handler` and its `Formatter`.
- When getting a `Logger`, if no `Handler` is specified, the underlying layer will automatically bind the `default` `Handler` to the `Logger`.
- `dev` (development) environment: Logs are output to `standard output (stdout)` using `php://stdout`, and `allowInlineLineBreaks` is set in the `Formatter` for easy viewing of multi-line logs.
- Non-`dev` environment: Logs are formatted as `json` using `JsonFormatter`, which is convenient for submitting to third-party log services.

### Log File Rotation by Date

If you want log files to be rotated by date, you can use the `Monolog\Handler\RotatingFileHandler` already provided by `Monolog`. Configure it as follows:

Modify the `config/autoload/logger.php` configuration file, change the `Handler` to `Monolog\Handler\RotatingFileHandler::class`, and change the `stream` field to `filename`.

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

If you wish to perform finer-grained log splitting, you can also extend the `Monolog\Handler\RotatingFileHandler` class and re-implement the `rotate()` method.

### Configuring Multiple `Handlers`

Users can modify `handlers` to allow the corresponding log group to support multiple `handlers`. For example, in the following configuration, when a user submits a log with a level of `INFO` or higher, the log will be written to both `hyperf.log` and `hyperf-debug.log`.
When a user submits a `DEBUG` level log, it will only be written to `hyperf-debug.log`.

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

Or

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

The result is as follows:

```shell
==> runtime/logs/hyperf.log <==
[2019-11-08 11:11:35] hyperf.INFO: 5dc4dce791690 [] []

==> runtime/logs/hyperf-debug.log <==
{"message":"5dc4dce791690","context":[],"level":200,"level_name":"INFO","channel":"hyperf","datetime":{"date":"2019-11-08 11:11:35.597153","timezone_type":3,"timezone":"Asia/Shanghai"},"extra":[]}
{"message":"xxxx","context":[],"level":100,"level_name":"DEBUG","channel":"hyperf","datetime":{"date":"2019-11-08 11:11:35.597635","timezone_type":3,"timezone":"Asia/Shanghai"},"extra":[]}
```


### Unified Request-Level Logging

Sometimes, we need to associate logs for the same request. Therefore, we can implement a `Processor`.

```php
<?php

declare(strict_types=1);

namespace App\Kernel\Log;

use Hyperf\Context\Context;
use Hyperf\Coroutine\Coroutine;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class AppendRequestIdProcessor implements ProcessorInterface
{
    public const REQUEST_ID = 'log.request.id';

    public function __invoke(array|LogRecord $record)
    {
        $record['extra']['request_id'] = Context::getOrSet(self::REQUEST_ID, uniqid());
        $record['extra']['coroutine_id'] = Coroutine::id();
        return $record;
    }
}

```

Then configure it into our `logger.php` configuration:

```php
<?php

declare(strict_types=1);

use App\Kernel\Log;

return [
    'default' => [
        // Remove other configurations
        'processors' => [
            [
                'class' => Log\AppendRequestIdProcessor::class,
            ],
        ],
    ],
];
```
