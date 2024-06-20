# AMQP 組件

[hyperf/amqp](https://github.com/hyperf/amqp) 是實現 AMQP 標準的組件，主要適用於對 RabbitMQ 的使用。

## 安裝

```bash
composer require hyperf/amqp
```

## 默認配置

|       配置       |  類型  |  默認值   |    備註     |
|:----------------:|:------:|:---------:|:---------:|
|       host       | string | localhost |   Host    |
|       port       |  int   |   5672    |    端口號    |
|       user       | string |   guest   |    用户名    |
|     password     | string |   guest   |    密碼     |
|      vhost       | string |     /     |   vhost   |
| concurrent.limit |  int   |     0     | 同時消費的最大數量 |
|       pool       | object |           |   連接池配置   |
| pool.connections |  int   |     1     | 進程內保持的連接數 |
|      params      | object |           |   基本配置    |

```php
<?php

return [
    'enable' => true,
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
            'connections' => 1,
        ],
        'params' => [
            'insist' => false,
            'login_method' => 'AMQPLAIN',
            'login_response' => null,
            'locale' => 'en_US',
            'connection_timeout' => 3.0,
            // 儘量保持是 heartbeat 數值的兩倍
            'read_write_timeout' => 6.0,
            'context' => null,
            'keepalive' => false,
            // 儘量保證每個消息的消費時間小於心跳時間
            'heartbeat' => 3,
            'close_on_destruct' => false,
        ],
    ],
    'pool2' => [
        ...
    ]
];
```

可在 `producer` 或者 `consumer` 的 `__construct` 函數中，設置不同 `pool`，例如上述的 `default` 和 `pool2`。

## 投遞消息

使用 `gen:producer` 命令創建一個 `producer`

```bash
php bin/hyperf.php gen:amqp-producer DemoProducer
```

在 DemoProducer 文件中，我們可以修改 `#[Producer]` 註解對應的字段來替換對應的 `exchange` 和 `routingKey`。
其中 `payload` 就是最終投遞到消息隊列中的數據，所以我們可以隨意改寫 `__construct` 方法，只要最後賦值 `payload` 即可。
示例如下。

> 使用 `#[Producer]` 註解時需 `use Hyperf\Amqp\Annotation\Producer;` 命名空間；   

```php
<?php

declare(strict_types=1);

namespace App\Amqp\Producers;

use Hyperf\Amqp\Annotation\Producer;
use Hyperf\Amqp\Message\ProducerMessage;
use App\Models\User;

#[Producer(exchange: "hyperf", routingKey: "hyperf")]
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

通過 DI Container 獲取 `Hyperf\Amqp\Producer` 實例，即可投遞消息。以下實例直接使用 `ApplicationContext` 獲取 `Hyperf\Amqp\Producer` 其實並不合理，DI Container 具體使用請到 [依賴注入](zh-hk/di.md) 章節中查看。

```php
<?php
use Hyperf\Amqp\Producer;
use App\Amqp\Producers\DemoProducer;
use Hyperf\Context\ApplicationContext;

$message = new DemoProducer(1);
$producer = ApplicationContext::getContainer()->get(Producer::class);
$result = $producer->produce($message);

```

## 消費消息

使用 `gen:amqp-consumer` 命令創建一個 `consumer`。

```bash
php bin/hyperf.php gen:amqp-consumer DemoConsumer
```

在 DemoConsumer 文件中，我們可以修改 `#[Consumer]` 註解對應的字段來替換對應的 `exchange`、`routingKey` 和 `queue`。
其中 `$data` 就是解析後的消息數據。
示例如下。

> 使用 `#[Consumer]` 註解時需 `use Hyperf\Amqp\Annotation\Consumer;` 命名空間；   

```php
<?php

declare(strict_types=1);

namespace App\Amqp\Consumers;

use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\Amqp\Result;
use PhpAmqpLib\Message\AMQPMessage;

#[Consumer(exchange: "hyperf", routingKey: "hyperf", queue: "hyperf", nums: 1)]
class DemoConsumer extends ConsumerMessage
{
    public function consumeMessage($data, AMQPMessage $message): Result
    {
        print_r($data);
        return Result::ACK;
    }
}
```

### 禁止消費進程自啓

默認情況下，使用了 `#[Consumer]` 註解後，框架會自動創建子進程啓動消費者，並且會在子進程異常退出後，重新拉起。
如果出於開發階段，進行消費者調試時，可能會因為消費其他消息而導致調試不便。

這種情況，只需要在 `#[Consumer]` 註解中配置 `enable=false` (默認為 `true` 跟隨服務啓動)或者在對應的消費者中重寫類方法 `isEnable()` 返回 `false` 即可

```php
<?php

declare(strict_types=1);

namespace App\Amqp\Consumers;

use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\Amqp\Result;
use PhpAmqpLib\Message\AMQPMessage;

#[Consumer(exchange: "hyperf", routingKey: "hyperf", queue: "hyperf", nums: 1, enable: false)]
class DemoConsumer extends ConsumerMessage
{
    public function consumeMessage($data, AMQPMessage $message): Result
    {
        print_r($data);
        return Result::ACK;
    }

    public function isEnable(): bool
    {
        return parent::isEnable();
    }
}
```

### 設置最大消費數

可以修改 `#[Consumer]` 註解中的 `maxConsumption` 屬性，設置此消費者最大處理的消息數，達到指定消費數後，消費者進程會重啓。

### 設置併發消費
 
影響消費速率的參數有三個地方

- 可以修改 `#[Consumer]` 註解 `nums` 開啓多個消費者
- `ConsumerMessage` 基類下有一個屬性 `$qos`，可以通過重寫`$qos`中的 `prefetch_size` 或者 `prefetch_count` 的值控制每次從服務端拉取的消息數量
- 配置文件中的 `concurrent.limit` 參數，控制消費協程的最大數量


### 消費結果

框架會根據 `Consumer` 內的 `consume` 方法所返回的結果來決定該消息的響應行為，共有 4 中響應結果，分別為 `\Hyperf\Amqp\Result::ACK`、`\Hyperf\Amqp\Result::NACK`、`\Hyperf\Amqp\Result::REQUEUE`、`\Hyperf\Amqp\Result::DROP`，每個返回值分別代表如下行為：

| 返回值                       | 行為                                                                 |
|------------------------------|----------------------------------------------------------------------|
| \Hyperf\Amqp\Result::ACK     | 確認消息正確被消費掉了                                               |
| \Hyperf\Amqp\Result::NACK    | 消息沒有被正確消費掉，以 `basic_nack` 方法來響應                     |
| \Hyperf\Amqp\Result::REQUEUE | 消息沒有被正確消費掉，以 `basic_reject` 方法來響應，並使消息重新入列 |
| \Hyperf\Amqp\Result::DROP    | 消息沒有被正確消費掉，以 `basic_reject` 方法來響應                   |

### QOS 配置


```php
<?php

declare(strict_types=1);

namespace App\Amqp\Consumers;

use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\Amqp\Result;
use PhpAmqpLib\Message\AMQPMessage;

#[Consumer(exchange: "hyperf", routingKey: "hyperf", queue: "hyperf", nums: 1)]
class DemoConsumer extends ConsumerMessage
{
    protected ?array $qos = [
        // AMQP 默認並沒有實現此配置。
        'prefetch_size' => 0,
        // 同一個消費者，最高同時可以處理的消息數。
        'prefetch_count' => 30,
        // 因為 Hyperf 默認一個 Channel 只消費一個 隊列，所以 global 設置為 true/false 效果是一樣的。
        'global' => false,
    ];
    
    public function consumeMessage($data, AMQPMessage $message): Result
    {
        print_r($data);
        return Result::ACK;
    }
}
```

### 根據環境自定義消費進程數量

在 `#[Consumer]` 註解中，可以通過 `nums` 屬性來設置消費進程數量，如果需要根據不同環境來設置不同的消費進程數量，可以通過重寫 `getNums` 方法來實現，示例如下：

```php
#[Consumer(
    exchange: 'hyperf',
    routingKey: 'hyperf',
    queue: 'hyperf',
    name: 'hyperf',
    nums: 1
)]
final class DemoConsumer extends ConsumerMessage
{
    public function getNums(): int
    {
        if (is_debug()) {
            return 10;
        }
        return parent::getNums();
    }
}
```

## 延時隊列

AMQP 的延時隊列，並不會根據延時時間進行排序，所以，一旦你投遞了一個延時 10s 的任務，又往這個隊列中投遞了一個延時 5s 的任務，那麼也一定會在第一個 10s 任務完成後，才會消費第二個 5s 的任務。
所以，需要根據時間設置不同的隊列，如果想要更加靈活的延時隊列，可以嘗試 異步隊列(async-queue) 和 AMQP 配合使用。

另外，AMQP 需要下載 [延時插件](https://github.com/rabbitmq/rabbitmq-delayed-message-exchange/releases)，並激活才能正常使用

```shell
wget https://github.com/rabbitmq/rabbitmq-delayed-message-exchange/releases/download/3.9.0/rabbitmq_delayed_message_exchange-3.9.0.ez
cp rabbitmq_delayed_message_exchange-3.9.0.ez /opt/rabbitmq/plugins/
rabbitmq-plugins enable rabbitmq_delayed_message_exchange
```

### 生產者

使用 `gen:amqp-producer` 命令創建一個 `producer`。這裏舉例 `direct` 類型，其他類型如 `fanout`、`topic`，改生產者和消費者中的 `type` 即可。

```bash
php bin/hyperf.php gen:amqp-producer DelayDirectProducer
```

在 DelayDirectProducer 文件中，加入`use ProducerDelayedMessageTrait;`，示例如下：

```php
<?php

namespace App\Amqp\Producer;

use Hyperf\Amqp\Annotation\Producer;
use Hyperf\Amqp\Message\ProducerDelayedMessageTrait;
use Hyperf\Amqp\Message\ProducerMessage;
use Hyperf\Amqp\Message\Type;

#[Producer]
class DelayDirectProducer extends ProducerMessage
{
    use ProducerDelayedMessageTrait;

    protected string $exchange = 'ext.hyperf.delay';

    protected Type|string $type = Type::DIRECT;

    protected array|string $routingKey = '';

    public function __construct($data)
    {
        $this->payload = $data;
    }
}
```

### 消費者

使用 `gen:amqp-consumer` 命令創建一個 `consumer`。

```bash
php bin/hyperf.php gen:amqp-consumer DelayDirectConsumer
```

在 `DelayDirectConsumer` 文件中，增加引入`use ProducerDelayedMessageTrait, ConsumerDelayedMessageTrait;`，示例如下：

```php
<?php

declare(strict_types=1);

namespace App\Amqp\Consumer;

use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Message\ConsumerDelayedMessageTrait;
use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\Amqp\Message\ProducerDelayedMessageTrait;
use Hyperf\Amqp\Message\Type;
use Hyperf\Amqp\Result;
use PhpAmqpLib\Message\AMQPMessage;

#[Consumer(nums: 1)]
class DelayDirectConsumer extends ConsumerMessage
{
    use ProducerDelayedMessageTrait;
    use ConsumerDelayedMessageTrait;

    protected string $exchange = 'ext.hyperf.delay';
    
    protected string $queue = 'queue.hyperf.delay';
    
    protected Type|string $type = Type::DIRECT; //Type::FANOUT;
    
    protected array|string $routingKey = '';

    public function consumeMessage($data, AMQPMessage $message): Result
    {
        var_dump($data, 'delay+direct consumeTime:' . (microtime(true)));
        return Result::ACK;
    }
}

```

### 生產延時消息

> 以下是在 Command 中演示如何使用，具體用法請以實際為準

使用 `gen:command DelayCommand` 命令創建一個 `DelayCommand`。如下：

```php
<?php

declare(strict_types=1);

namespace App\Command;

use App\Amqp\Producer\DelayDirectProducer;
//use App\Amqp\Producer\DelayFanoutProducer;
//use App\Amqp\Producer\DelayTopicProducer;
use Hyperf\Amqp\Producer;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Context\ApplicationContext;
use Psr\Container\ContainerInterface;

#[Command]
class DelayCommand extends HyperfCommand
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('demo:command');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Hyperf Demo Command');
    }

    public function handle()
    {
        //1.delayed + direct
        $message = new DelayDirectProducer('delay+direct produceTime:'.(microtime(true)));
        //2.delayed + fanout
        //$message = new DelayFanoutProducer('delay+fanout produceTime:'.(microtime(true)));
        //3.delayed + topic
        //$message = new DelayTopicProducer('delay+topic produceTime:' . (microtime(true)));
        $message->setDelayMs(5000);
        $producer = ApplicationContext::getContainer()->get(Producer::class);
        $producer->produce($message);
    }
}

```

執行命令行生產消息

```
php bin/hyperf.php demo:command
```

## RPC 遠程過程調用

除了典型的消息隊列場景，我們還可以通過 AMQP 來實現 RPC 遠程過程調用，本組件也為這個實現提供了對應的支持。

### 創建消費者

RPC 使用的消費者，與典型消息隊列場景的消費者實現基本無差，唯一的區別是需要通過調用 `reply` 方法返回數據給生產者。

```php
<?php

declare(strict_types=1);

namespace App\Amqp\Consumer;

use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\Amqp\Result;
use PhpAmqpLib\Message\AMQPMessage;

#[Consumer(exchange: "hyperf", routingKey: "hyperf", queue: "rpc.reply", name: "ReplyConsumer", nums: 1, enable: true)]
class ReplyConsumer extends ConsumerMessage
{
    public function consumeMessage($data, AMQPMessage $message): Result
    {
        $data['message'] .= 'Reply:' . $data['message'];

        $this->reply($data, $message);

        return Result::ACK;
    }
}
```

### 發起 RPC 調用

作為生成者發起一次 RPC 遠程過程調用也非常的簡單，只需通過依賴注入容器獲得 `Hyperf\Amqp\RpcClient` 對象並調用其中的 `call` 方法即可，返回的結果是消費者 reply 的數據，如下所示：

```php
<?php
use Hyperf\Amqp\Message\DynamicRpcMessage;
use Hyperf\Amqp\RpcClient;
use Hyperf\Context\ApplicationContext;

$rpcClient = ApplicationContext::getContainer()->get(RpcClient::class);
// 在 DynamicRpcMessage 上設置與 Consumer 一致的 Exchange 和 RoutingKey
$result = $rpcClient->call(new DynamicRpcMessage('hyperf', 'hyperf', ['message' => 'Hello Hyperf'])); 

// $result:
// array(1) {
//     ["message"]=>
//     string(18) "Reply:Hello Hyperf"
// }
```

### 抽象 RpcMessage

上面的 RPC 調用過程是直接通過 `Hyperf\Amqp\Message\DynamicRpcMessage` 類來完成 Exchange 和 RoutingKey 的定義，並傳遞消息數據，在生產項目的設計上，我們可以對 RpcMessage 進行一層抽象，以統一 Exchange 和 RoutingKey 的定義。   

我們可以創建對應的 RpcMessage 類如 `App\Amqp\FooRpcMessage` 如下：

```php
<?php
use Hyperf\Amqp\Message\RpcMessage;

class FooRpcMessage extends RpcMessage
{

    protected string $exchange = 'hyperf';

    protected array|string $routingKey = 'hyperf';
    
    public function __construct($data)
    {
        // 要傳遞數據
        $this->payload = $data;
    }

}
```

這樣我們進行 RPC 調用時，只需直接傳遞 `FooRpcMessage` 實例到 `call` 方法即可，無需每次調用時都去定義 Exchange 和 RoutingKey。
