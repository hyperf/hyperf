# 日誌

`hyperf/logger` 元件是基於 [psr/logger](https://github.com/php-fig/log) 實現的，預設使用 [monolog/monolog](https://github.com/Seldaek/monolog) 作為驅動，在 `hyperf-skeleton` 專案內預設提供了一些日誌配置，預設使用 `Monolog\Handler\StreamHandler`, 由於 `Swoole` 已經對 `fopen`, `fwrite` 等函式進行了協程化處理，所以只要不將 `useLocking` 引數設定為 `true`，就是協程安全的。

## 安裝

```shell
composer require hyperf/logger
```

## 配置

在 `hyperf-skeleton` 專案內預設提供了一些日誌配置，預設情況下，日誌的配置檔案為 `config/autoload/logger.php` ，示例如下：

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
        // 第一個引數對應日誌的 name, 第二個引數對應 config/autoload/logger.php 內的 key
        $this->logger = $loggerFactory->get('log', 'default');
    }

    public function method()
    {
        // Do something.
        $this->logger->info("Your log message.");
    }
}
```

## 關於 monolog 的基礎知識

我們結合程式碼來看一些 `monolog` 中所涉及到的基礎概念:

```php
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

// 建立一個 Channel，引數 log 即為 Channel 的名字
$log = new Logger('log');

// 建立兩個 Handler，對應變數 $stream 和 $fire
$stream = new StreamHandler('test.log', Logger::WARNING);
$fire = new FirePHPHandler();

// 定義時間格式為 "Y-m-d H:i:s"
$dateFormat = "Y n j, g:i a";
// 定義日誌格式為 "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
$output = "%datetime%||%channel||%level_name%||%message%||%context%||%extra%\n";
// 根據 時間格式 和 日誌格式，建立一個 Formatter
$formatter = new LineFormatter($output, $dateFormat);

// 將 Formatter 設定到 Handler 裡面
$stream->setFormatter($formatter);

// 將 Handler 推入到 Channel 的 Handler 佇列內
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

- 首先, 例項化一個 `Logger`, 取個名字, 名字對應的就是 `channel`
- 可以為 `Logger` 繫結多個 `Handler`, `Logger` 打日誌, 交由 `Handler` 來處理
- `Handler` 可以指定需要處理哪些 **日誌級別** 的日誌, 比如 `Logger::WARNING`, 只處理日誌級別 `>=Logger::WARNING` 的日誌
- 誰來格式化日誌? `Formatter`, 設定好 Formatter 並繫結到相應的 `Handler` 上
- 日誌包含哪些部分: `"%datetime%||%channel||%level_name%||%message%||%context%||%extra%\n"`
- 區分一下日誌中新增的額外資訊 `context` 和 `extra`: `context` 由使用者打日誌時額外指定, 更加靈活; `extra` 由繫結到 `Logger` 上的 `Processor` 固定新增, 比較適合收集一些 **常見資訊**

## 更多用法

### 封裝 `Log` 類

可能有些時候您更想保持大多數框架使用日誌的習慣，那麼您可以在 `App` 下建立一個 `Log` 類，並透過 `__callStatic` 魔術方法靜態方法呼叫實現對 `Logger` 的取用以及各個等級的日誌記錄，我們透過程式碼來演示一下：

> 切記在使用時，不要讓 $name 跟 請求 掛鉤，比如把 $request_id 當 logger name 來使用，就會導致 Factory 中儲存請求級別的日誌物件，會導致嚴重的記憶體洩漏。

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

預設使用 `Channel` 名為 `app` 來記錄日誌，您也可以透過使用 `Log::get($name)` 方法獲得不同 `Channel` 的 `Logger`, 強大的 `容器(Container)` 幫您解決了這一切

### stdout 日誌

框架元件所輸出的日誌在預設情況下是由 `Hyperf\Contract\StdoutLoggerInterface` 介面的實現類 `Hyperf\Framework\Logger\StdoutLogger` 提供支援的，該實現類只是為了將相關的資訊透過 `print_r()` 輸出在 `標準輸出(stdout)`，即為啟動 `Hyperf` 的 `終端(Terminal)` 上，也就意味著其實並沒有使用到 `monolog` 的，那麼如果想要使用 `monolog` 來保持一致要怎麼處理呢？

是的, 還是透過強大的 `容器(Container)`.

- 首先, 實現一個 `StdoutLoggerFactory` 類，關於 `Factory` 的用法可在 [依賴注入](zh-tw/di.md) 章節獲得更多詳細的說明。

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

- 宣告依賴, 使用 `StdoutLoggerInterface` 的地方, 由實際依賴的 `StdoutLoggerFactory` 例項化的類來完成

```php
// config/autoload/dependencies.php
return [
    \Hyperf\Contract\StdoutLoggerInterface::class => \App\StdoutLoggerFactory::class,
];
```

### 不同環境下輸出不同格式的日誌

上面這麼多的使用, 都還只在 monolog 中的 `Logger` 這裡打轉, 這裡來看看 `Handler` 和 `Formatter`

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

- 預設配置了名為 `default` 的 `Handler`, 幷包含了此 `Handler` 及其 `Formatter` 的資訊
- 獲取 `Logger` 時, 如果沒有指定 `Handler`, 底層會自動把 `default` 這一 `Handler` 繫結到 `Logger` 上
- dev(開發)環境: 日誌使用 `php://stdout` 輸出到 `標準輸出(stdout)`, 並且 `Formatter` 中設定 `allowInlineLineBreaks`, 方便檢視多行日誌
- 非 dev 環境: 日誌使用 `JsonFormatter`, 會被格式為 `json`, 方便投遞到第三方日誌服務

### 日誌檔案按日期輪轉

如果您希望日誌檔案可以按照日期輪轉，可以透過 `Mongolog` 已經提供了的 `Monolog\Handler\RotatingFileHandler` 來實現，配置如下：

修改 `config/autoload/logger.php` 配置檔案，將 `Handler` 改為 `Monolog\Handler\RotatingFileHandler::class`，並將 `stream` 欄位改為 `filename` 即可。

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

如果您希望再進行更細粒度的日誌切割，也可透過繼承 `Monolog\Handler\RotatingFileHandler` 類並重新實現 `rotate()` 方法實現。

### 配置多個 `Handler`

使用者可以修改 `handlers` 讓對應日誌組支援多個 `handler`。比如以下配置，當用戶投遞一個 `INFO` 級別以上的日誌時，會在 `hyperf.log` 和 `hyperf-debug.log`  寫入日誌。
當用戶投遞一個 `DEBUG` 級別日誌時，只會在 `hyperf-debug.log` 中寫入日誌。

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

結果如下

```shell
==> runtime/logs/hyperf.log <==
[2019-11-08 11:11:35] hyperf.INFO: 5dc4dce791690 [] []

==> runtime/logs/hyperf-debug.log <==
{"message":"5dc4dce791690","context":[],"level":200,"level_name":"INFO","channel":"hyperf","datetime":{"date":"2019-11-08 11:11:35.597153","timezone_type":3,"timezone":"Asia/Shanghai"},"extra":[]}
{"message":"xxxx","context":[],"level":100,"level_name":"DEBUG","channel":"hyperf","datetime":{"date":"2019-11-08 11:11:35.597635","timezone_type":3,"timezone":"Asia/Shanghai"},"extra":[]}
```


### 統一請求級別日誌

有時候，我們需要將同一個請求的日誌關聯起來，所以我們可以實現一個 Processor

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

然後配置到我們的 `logger.php` 配置中

```php
<?php

declare(strict_types=1);

use App\Kernel\Log;

return [
    'default' => [
        // 刪除其他配置
        'processors' => [
            [
                'class' => Log\AppendRequestIdProcessor::class,
            ],
        ],
    ],
];

```
