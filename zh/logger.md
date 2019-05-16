# 日志

`hyperf/logger` 组件对 [monolog/monolog](https://github.com/Seldaek/monolog) 进行了封装，默认使用 `Monolog\Handler\StreamHandler`, Swoole 已经对 `fopen`, `fwrite` 等方法进行了协程化，所以只要不将 `useLocking` 设置为true，就不会阻塞协程。

## 安装

```
composer require hyperf/logger
```

## 配置

模型缓存的配置在 `logger` 中。示例如下

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

## 使用

```php
<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Container\ContainerInterface;
use Hyperf\Logger\LoggerFactory;

class DemoService
{
    protected $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->logger =  $container->get(LoggerFactory::class)->get('logname');
    }

    public function method()
    {
        // Do somthing.
        $this->logger->info("Your log message.");
    }
}
```

## 关于 monolog 的基础知识

结合代码来看 monolog 中涉及的基础概念:

```php
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FirePHPHandler;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// create log channel
$log = new Logger('log');

// create log handler
$stream = new StreamHandler('test.log', Logger::WARNING);
$fire = new FirePHPHandler();

// custom log format
// the default date format is "Y-m-d H:i:s"
$dateFormat = "Y n j, g:i a";
// the default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
$output = "%datetime%||%channel||%level_name%||%message%||%context%||%extra%\n";
// finally, create a formatter
$formatter = new LineFormatter($output, $dateFormat);

// set log format
$stream->setFormatter($formatter);

// add handler to log channel
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
- `Handler` 可以指定需要处理那些 **日志级别** 的日志, 比如 `Logger::WARNING`, 只处理日志级别 `>=Logger::WARNING` 的日志
- 谁来格式化日志? `Formatter`, 设置好 Formatter 并绑定到相应的 `Handler` 上
- 日志包含那些部分: `"%datetime%||%channel||%level_name%||%message%||%context%||%extra%\n"`
- 区分一下日志中添加的额外信息 `context` 和 `extra`: `context` 由用户打日志时额外指定, 更加灵活; `extra` 由绑定到 `Logger` 上的 `Processor` 固定添加, 比较适合收集一些 **常见信息**

## hyperf/logger 的高级用法

### 封装 Log 类

```php
namespace App;

use Hyperf\Logger\Logger;
use Hyperf\Utils\ApplicationContext;

/**
 * @method static Logger get($name)
 * @method static log($level, $message, array $context = array())
 * @method static info($message, array $context = array())
 * @method static error($message, array $context = array())
 */
class Log
{
    public static function __callStatic($name, $arguments)
    {
        $container = ApplicationContext::getContainer();
        $log = $container->get(\Hyperf\Logger\LoggerFactory::class);
        if ($name == 'get') {
            return $log->get(...$arguments);
        }
        $log = $log->get('app');
        $log->$name(...$arguments);
    }
}
```

- `__callStatic()` 使用魔术方法实现 **静态方法调用**
- 默认使用 `app` channel(回忆一下上面提到的 monolog 基础概念) 来打日志, 
- 使用 `Log::get()` 就可以切换到不同 channel, 强大的 `container` 解决了这一切

### stdout 日志

默认 `StdoutLoggerInterface` 接口的实现类 `StdoutLogger`, 其实并没有使用 monolog, 如果想要使用 monolog 保持一致呢?

是的, 还是强大的 `container`.

- 首先, 实现一个 `StdoutLoggerFactory`

```php
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

- 申明依赖, 使用 `StdoutLoggerInterface` 的地方, 由实际依赖的 `StdoutLoggerFactory` 实例化的类来完成

```php
// config/dependencies.php
return [
    'dependencies' => [
        \Hyperf\Contract\StdoutLoggerInterface::class => \App\StdoutLoggerFactory::class,
    ],
];
```

### 不同环境下输出不同格式的日志

上面这么多的使用, 都还只在 monolog 中的 `Logger` 这里打转, 这里来看看 `Handler` 和 `Formatter`

```php
// config/autoload/logger.php
$app_env = env('APP_ENV', 'dev');
if ($app_env == 'dev') {
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

- 默认配置了名为 `default` 的 `Handler`, 并包含了此 `Handler` 及其 `Formatter` 的信息
- 获取 `Logger` 时, 如果没有指定 `Handler`, 底层会自动把 `default` 这一 `Handler` 绑定到 `Logger` 上
- dev(开发)环境: 日志使用 `php://stdout` 输出到 stdout, 并且 `Formatter` 中设置 `allowInlineLineBreaks`, 方便查看多行日志
- 非 dev 环境: 日志使用 `JsonFormatter`, 会被格式为 json, 方便投递到第三方日志服务