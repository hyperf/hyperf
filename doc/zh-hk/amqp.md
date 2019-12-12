# AMQP 組件

[hyperf/amqp](https://github.com/hyperf/amqp) 是實現 AMQP 標準的組件，主要適用於對 RabbitMQ 的使用。

## 安裝

```bash
composer require hyperf/amqp
```

## 默認配置

|       配置       |  類型  |  默認值   |      備註      |
|:----------------:|:------:|:---------:|:--------------:|
|       host       | string | localhost |      Host      |
|       port       |  int   |   5672    |     端口號     |
|       user       | string |   guest   |     用户名     |
|     password     | string |   guest   |      密碼      |
|      vhost       | string |     /     |     vhost      |
| concurrent.limit |  int   |     0     | 同時消費的數量 |
|       pool       | object |           |   連接池配置   |
|      params      | object |           |    基本配置    |

```php
<?php

return [
    'default' => [
        'host' => 'localhost',
        'port' => 5672,
        'user' => 'guest',
        'password' => 'guest',
        'vhost' => '/',
        'concurrent' => [
            'limit' => 1,
        ],
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
        ],
        'params' => [
            'insist' => false,
            'login_method' => 'AMQPLAIN',
            'login_response' => null,
            'locale' => 'en_US',
            'connection_timeout' => 3.0,
            'read_write_timeout' => 3.0,
            'context' => null,
            'keepalive' => false,
            'heartbeat' => 3,
        ],
    ],
    'pool2' => [
        ...
    ]
];
```

可在 `producer` 或者 `consumer` 的 `__construct` 函數中, 設置不同 `pool`.

## 投遞消息

使用 `gen:producer` 命令創建一個 `producer`

```bash
php bin/hyperf.php gen:amqp-producer DemoProducer
```

在 DemoProducer 文件中，我們可以修改 `@Producer` 註解對應的字段來替換對應的 `exchange` 和 `routingKey`。
其中 `payload` 就是最終投遞到消息隊列中的數據，所以我們可以隨意改寫 `__construct` 方法，只要最後賦值 `payload` 即可。
示例如下。

> 使用 `@Producer` 註解時需 `use Hyperf\Amqp\Annotation\Producer;` 命名空間；   

```php
<?php

declare(strict_types=1);

namespace App\Amqp\Producers;

use Hyperf\Amqp\Annotation\Producer;
use Hyperf\Amqp\Message\ProducerMessage;
use App\Models\User;

/**
 * DemoProducer
 * @Producer(exchange="hyperf", routingKey="hyperf")
 */
class DemoProducer extends ProducerMessage
{
    public function __construct($id)
    {
        // 設置不同 pool
        $this->poolName = 'pool2';

        $user = User::where('id', $id)->first();
        $this->payload = [
            'id' => $id,
            'data' => $user->toArray()
        ];
    }
}

```

通過 DI Container 獲取 `Hyperf\Amqp\Producer` 實例，即可投遞消息。以下實例直接使用 `ApplicationContext` 獲取 `Hyperf\Amqp\Producer` 其實並不合理，DI Container 具體使用請到 [依賴注入](zh/di.md) 章節中查看。

```php
<?php
use Hyperf\Amqp\Producer;
use App\Amqp\Producers\DemoProducer;
use Hyperf\Utils\ApplicationContext;

$message = new DemoProducer(1);
$producer = ApplicationContext::getContainer()->get(Producer::class);
$result = $producer->produce($message);

```

## 消費消息

使用 `gen:amqp-consumer` 命令創建一個 `consumer`。

```bash
php bin/hyperf.php gen:amqp-consumer DemoConsumer
```

在 DemoConsumer 文件中，我們可以修改 `@Consumer` 註解對應的字段來替換對應的 `exchange`、`routingKey` 和 `queue`。
其中 `$data` 就是解析後的消息數據。
示例如下。

> 使用 `@Consumer` 註解時需 `use Hyperf\Amqp\Annotation\Consumer;` 命名空間；   

```php
<?php

declare(strict_types=1);

namespace App\Amqp\Consumers;

use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\Amqp\Result;

/**
 * @Consumer(exchange="hyperf", routingKey="hyperf", queue="hyperf", nums=1)
 */
class DemoConsumer extends ConsumerMessage
{
    public function consume($data): string
    {
        print_r($data);
        return Result::ACK;
    }
}
```

框架會根據 `@Consumer` 註解自動創建 `Process 進程`，進程意外退出後會被重新拉起。

### 消費結果

框架會根據 `Consumer` 內的 `consume` 方法所返回的結果來決定該消息的響應行為，共有 4 中響應結果，分別為 `\Hyperf\Amqp\Result::ACK`、`\Hyperf\Amqp\Result::NACK`、`\Hyperf\Amqp\Result::REQUEUE`、`\Hyperf\Amqp\Result::DROP`，每個返回值分別代表如下行為：

| 返回值                       | 行為                                                                 |
|------------------------------|----------------------------------------------------------------------|
| \Hyperf\Amqp\Result::ACK     | 確認消息正確被消費掉了                                               |
| \Hyperf\Amqp\Result::NACK    | 消息沒有被正確消費掉，以 `basic_nack` 方法來響應                     |
| \Hyperf\Amqp\Result::REQUEUE | 消息沒有被正確消費掉，以 `basic_reject` 方法來響應，並使消息重新入列 |
| \Hyperf\Amqp\Result::DROP    | 消息沒有被正確消費掉，以 `basic_reject` 方法來響應                   |
