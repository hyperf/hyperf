# 日志

`hyperf/logger` 组件是基于 [psr/logger](https://github.com/php-fig/log) 实现的，默认使用 [monolog/monolog](https://github.com/Seldaek/monolog) 作为驱动，在 `hyperf-skeleton` 项目内默认提供了一些日志配置，默认使用 `Monolog\Handler\StreamHandler`, 由于 `Swoole` 已经对 `fopen`, `fwrite` 等函数进行了协程化处理，所以只要不将 `useLocking` 参数设置为 `true`，就是协程安全的。

## 安装

```shell
composer require hyperf/logger
```

## 配置

在 `hyperf-skeleton` 项目内默认提供了一些日志配置，默认情况下，日志的配置文件为 `config/autoload/logger.php` ，示例如下：

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

## 使用

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
        // 第一个参数对应日志的 name, 第二个参数对应 config/autoload/logger.php 内的 key
        $this->logger = $loggerFactory->get('log', 'default');
    }

    public function method()
    {
        // Do something.
        $this->logger->info("Your log message.");
    }
}
```

## 关于 monolog 的基础知识

我们结合代码来看一些 `monolog` 中所涉及到的基础概念:

```php
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

// 创建一个 Channel，参数 log 即为 Channel 的名字
$log = new Logger('log');

// 创建两个 Handler，对应变量 $stream 和 $fire
$stream = new StreamHandler('test.log', Logger::WARNING);
$fire = new FirePHPHandler();

// 定义时间格式为 "Y-m-d H:i:s"
$dateFormat = "Y n j, g:i a";
// 定义日志格式为 "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
$output = "%datetime%||%channel||%level_name%||%message%||%context%||%extra%\n";
// 根据 时间格式 和 日志格式，创建一个 Formatter
$formatter = new LineFormatter($output, $dateFormat);

// 将 Formatter 设置到 Handler 里面
$stream->setFormatter($formatter);

// 将 Handler 推入到 Channel 的 Handler 队列内
$log->pushHandler($stream);
$log->pushHandler($fire);

// clone new log channel
$log2 = $log->withName('log2');

// add records to the log
$log->warning('Foo');

// add extra data to record
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

- 首先, 实例化一个 `Logger`, 取个名字, 名字对应的就是 `channel`
- 可以为 `Logger` 绑定多个 `Handler`, `Logger` 打日志, 交由 `Handler` 来处理
- `Handler` 可以指定需要处理哪些 **日志级别** 的日志, 比如 `Logger::WARNING`, 只处理日志级别 `>=Logger::WARNING` 的日志
- 谁来格式化日志? `Formatter`, 设置好 Formatter 并绑定到相应的 `Handler` 上
- 日志包含哪些部分: `"%datetime%||%channel||%level_name%||%message%||%context%||%extra%\n"`
- 区分一下日志中添加的额外信息 `context` 和 `extra`: `context` 由用户打日志时额外指定, 更加灵活; `extra` 由绑定到 `Logger` 上的 `Processor` 固定添加, 比较适合收集一些 **常见信息**

## 更多用法

### 封装 `Log` 类

可能有些时候您更想保持大多数框架使用日志的习惯，那么您可以在 `App` 下创建一个 `Log` 类，并通过 `__callStatic` 魔术方法静态方法调用实现对 `Logger` 的取用以及各个等级的日志记录，我们通过代码来演示一下：

> 切记在使用时，不要让 $name 跟 请求 挂钩，比如把 $request_id 当 logger name 来使用，就会导致 Factory 中存储请求级别的日志对象，会导致严重的内存泄漏。

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

默认使用 `Channel` 名为 `app` 来记录日志，您也可以通过使用 `Log::get($name)` 方法获得不同 `Channel` 的 `Logger`, 强大的 `容器(Container)` 帮您解决了这一切

### stdout 日志

框架组件所输出的日志在默认情况下是由 `Hyperf\Contract\StdoutLoggerInterface` 接口的实现类 `Hyperf\Framework\Logger\StdoutLogger` 提供支持的，该实现类只是为了将相关的信息通过 `print_r()` 输出在 `标准输出(stdout)`，即为启动 `Hyperf` 的 `终端(Terminal)` 上，也就意味着其实并没有使用到 `monolog` 的，那么如果想要使用 `monolog` 来保持一致要怎么处理呢？

是的, 还是通过强大的 `容器(Container)`.

- 首先, 实现一个 `StdoutLoggerFactory` 类，关于 `Factory` 的用法可在 [依赖注入](zh-cn/di.md) 章节获得更多详细的说明。

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

- 声明依赖, 使用 `StdoutLoggerInterface` 的地方, 由实际依赖的 `StdoutLoggerFactory` 实例化的类来完成

```php
// config/autoload/dependencies.php
return [
    \Hyperf\Contract\StdoutLoggerInterface::class => \App\StdoutLoggerFactory::class,
];
```

### 不同环境下输出不同格式的日志

上面这么多的使用, 都还只在 monolog 中的 `Logger` 这里打转, 这里来看看 `Handler` 和 `Formatter`

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

- 默认配置了名为 `default` 的 `Handler`, 并包含了此 `Handler` 及其 `Formatter` 的信息
- 获取 `Logger` 时, 如果没有指定 `Handler`, 底层会自动把 `default` 这一 `Handler` 绑定到 `Logger` 上
- dev(开发)环境: 日志使用 `php://stdout` 输出到 `标准输出(stdout)`, 并且 `Formatter` 中设置 `allowInlineLineBreaks`, 方便查看多行日志
- 非 dev 环境: 日志使用 `JsonFormatter`, 会被格式为 `json`, 方便投递到第三方日志服务

### 日志文件按日期轮转

如果您希望日志文件可以按照日期轮转，可以通过 `Mongolog` 已经提供了的 `Monolog\Handler\RotatingFileHandler` 来实现，配置如下：

修改 `config/autoload/logger.php` 配置文件，将 `Handler` 改为 `Monolog\Handler\RotatingFileHandler::class`，并将 `stream` 字段改为 `filename` 即可。

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

如果您希望再进行更细粒度的日志切割，也可通过继承 `Monolog\Handler\RotatingFileHandler` 类并重新实现 `rotate()` 方法实现。

### 配置多个 `Handler`

用户可以修改 `handlers` 让对应日志组支持多个 `handler`。比如以下配置，当用户投递一个 `INFO` 级别以上的日志时，会在 `hyperf.log` 和 `hyperf-debug.log`  写入日志。
当用户投递一个 `DEBUG` 级别日志时，只会在 `hyperf-debug.log` 中写入日志。

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

或

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

结果如下

```shell
==> runtime/logs/hyperf.log <==
[2019-11-08 11:11:35] hyperf.INFO: 5dc4dce791690 [] []

==> runtime/logs/hyperf-debug.log <==
{"message":"5dc4dce791690","context":[],"level":200,"level_name":"INFO","channel":"hyperf","datetime":{"date":"2019-11-08 11:11:35.597153","timezone_type":3,"timezone":"Asia/Shanghai"},"extra":[]}
{"message":"xxxx","context":[],"level":100,"level_name":"DEBUG","channel":"hyperf","datetime":{"date":"2019-11-08 11:11:35.597635","timezone_type":3,"timezone":"Asia/Shanghai"},"extra":[]}
```


### 统一请求级别日志

有时候，我们需要将同一个请求的日志关联起来，所以我们可以实现一个 Processor

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

然后配置到我们的 `logger.php` 配置中

```php
<?php

declare(strict_types=1);

use App\Kernel\Log;

return [
    'default' => [
        // 删除其他配置
        'processors' => [
            [
                'class' => Log\AppendRequestIdProcessor::class,
            ],
        ],
    ],
];

```
