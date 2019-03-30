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