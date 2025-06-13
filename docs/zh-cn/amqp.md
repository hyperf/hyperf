# AMQP 组件

[hyperf/amqp](https://github.com/hyperf/amqp) 是实现 AMQP 标准的组件，主要适用于对 RabbitMQ 的使用。

## 安装

```bash
composer require hyperf/amqp
```

## 默认配置

|       配置       |  类型  |  默认值   |    备注     |
|:----------------:|:------:|:---------:|:---------:|
|       host       | string | localhost |   Host    |
|       port       |  int   |   5672    |    端口号    |
|       user       | string |   guest   |    用户名    |
|     password     | string |   guest   |    密码     |
|      vhost       | string |     /     |   vhost   |
| concurrent.limit |  int   |     0     | 同时消费的最大数量 |
|       pool       | object |           |   连接池配置   |
| pool.connections |  int   |     1     | 进程内保持的连接数 |
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
            // 尽量保持是 heartbeat 数值的两倍
            'read_write_timeout' => 6.0,
            'context' => null,
            'keepalive' => false,
            // 尽量保证每个消息的消费时间小于心跳时间
            'heartbeat' => 3,
            'close_on_destruct' => false,
        ],
    ],
    'pool2' => [
        ...
    ]
];
```

可在 `producer` 或者 `consumer` 的 `__construct` 函数中，设置不同 `pool`，例如上述的 `default` 和 `pool2`。

## 投递消息

使用 `gen:producer` 命令创建一个 `producer`

```bash
php bin/hyperf.php gen:amqp-producer DemoProducer
```

在 DemoProducer 文件中，我们可以修改 `#[Producer]` 注解对应的字段来替换对应的 `exchange` 和 `routingKey`。
其中 `payload` 就是最终投递到消息队列中的数据，所以我们可以随意改写 `__construct` 方法，只要最后赋值 `payload` 即可。
示例如下。

> 使用 `#[Producer]` 注解时需 `use Hyperf\Amqp\Annotation\Producer;` 命名空间；   

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
        // 设置不同 pool
        $this->poolName = 'pool2';

        $user = User::where('id', $id)->first();
        $this->payload = [
            'id' => $id,
            'data' => $user->toArray()
        ];
    }
}

```

通过 DI Container 获取 `Hyperf\Amqp\Producer` 实例，即可投递消息。以下实例直接使用 `ApplicationContext` 获取 `Hyperf\Amqp\Producer` 其实并不合理，DI Container 具体使用请到 [依赖注入](zh-cn/di.md) 章节中查看。

```php
<?php
use Hyperf\Amqp\Producer;
use App\Amqp\Producers\DemoProducer;
use Hyperf\Context\ApplicationContext;

$message = new DemoProducer(1);
$producer = ApplicationContext::getContainer()->get(Producer::class);
$result = $producer->produce($message);

```

## 消费消息

使用 `gen:amqp-consumer` 命令创建一个 `consumer`。

```bash
php bin/hyperf.php gen:amqp-consumer DemoConsumer
```

在 DemoConsumer 文件中，我们可以修改 `#[Consumer]` 注解对应的字段来替换对应的 `exchange`、`routingKey` 和 `queue`。
其中 `$data` 就是解析后的消息数据。
示例如下。

> 使用 `#[Consumer]` 注解时需 `use Hyperf\Amqp\Annotation\Consumer;` 命名空间；   

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

### 禁止消费进程自启

默认情况下，使用了 `#[Consumer]` 注解后，框架会自动创建子进程启动消费者，并且会在子进程异常退出后，重新拉起。
如果出于开发阶段，进行消费者调试时，可能会因为消费其他消息而导致调试不便。

这种情况，只需要在 `#[Consumer]` 注解中配置 `enable=false` (默认为 `true` 跟随服务启动)或者在对应的消费者中重写类方法 `isEnable()` 返回 `false` 即可

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

### 设置最大消费数

可以修改 `#[Consumer]` 注解中的 `maxConsumption` 属性，设置此消费者最大处理的消息数，达到指定消费数后，消费者进程会重启。

### 设置并发消费
 
影响消费速率的参数有三个地方

- 可以修改 `#[Consumer]` 注解 `nums` 开启多个消费者
- `ConsumerMessage` 基类下有一个属性 `$qos`，可以通过重写`$qos`中的 `prefetch_size` 或者 `prefetch_count` 的值控制每次从服务端拉取的消息数量
- 配置文件中的 `concurrent.limit` 参数，控制消费协程的最大数量


### 消费结果

框架会根据 `Consumer` 内的 `consume` 方法所返回的结果来决定该消息的响应行为，共有 4 中响应结果，分别为 `\Hyperf\Amqp\Result::ACK`、`\Hyperf\Amqp\Result::NACK`、`\Hyperf\Amqp\Result::REQUEUE`、`\Hyperf\Amqp\Result::DROP`，每个返回值分别代表如下行为：

| 返回值                       | 行为                                                                 |
|------------------------------|----------------------------------------------------------------------|
| \Hyperf\Amqp\Result::ACK     | 确认消息正确被消费掉了                                               |
| \Hyperf\Amqp\Result::NACK    | 消息没有被正确消费掉，以 `basic_nack` 方法来响应                     |
| \Hyperf\Amqp\Result::REQUEUE | 消息没有被正确消费掉，以 `basic_reject` 方法来响应，并使消息重新入列 |
| \Hyperf\Amqp\Result::DROP    | 消息没有被正确消费掉，以 `basic_reject` 方法来响应                   |

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
        // AMQP 默认并没有实现此配置。
        'prefetch_size' => 0,
        // 同一个消费者，最高同时可以处理的消息数。
        'prefetch_count' => 30,
        // 因为 Hyperf 默认一个 Channel 只消费一个 队列，所以 global 设置为 true/false 效果是一样的。
        'global' => false,
    ];
    
    public function consumeMessage($data, AMQPMessage $message): Result
    {
        print_r($data);
        return Result::ACK;
    }
}
```

### 根据环境自定义消费进程数量

在 `#[Consumer]` 注解中，可以通过 `nums` 属性来设置消费进程数量，如果需要根据不同环境来设置不同的消费进程数量，可以通过重写 `getNums` 方法来实现，示例如下：

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

## 延时队列

AMQP 的延时队列，并不会根据延时时间进行排序，所以，一旦你投递了一个延时 10s 的任务，又往这个队列中投递了一个延时 5s 的任务，那么也一定会在第一个 10s 任务完成后，才会消费第二个 5s 的任务。
所以，需要根据时间设置不同的队列，如果想要更加灵活的延时队列，可以尝试 异步队列(async-queue) 和 AMQP 配合使用。

另外，AMQP 需要下载 [延时插件](https://github.com/rabbitmq/rabbitmq-delayed-message-exchange/releases)，并激活才能正常使用

```shell
wget https://github.com/rabbitmq/rabbitmq-delayed-message-exchange/releases/download/3.9.0/rabbitmq_delayed_message_exchange-3.9.0.ez
cp rabbitmq_delayed_message_exchange-3.9.0.ez /opt/rabbitmq/plugins/
rabbitmq-plugins enable rabbitmq_delayed_message_exchange
```

### 生产者

使用 `gen:amqp-producer` 命令创建一个 `producer`。这里举例 `direct` 类型，其他类型如 `fanout`、`topic`，改生产者和消费者中的 `type` 即可。

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

### 消费者

使用 `gen:amqp-consumer` 命令创建一个 `consumer`。

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

### 生产延时消息

> 以下是在 Command 中演示如何使用，具体用法请以实际为准

使用 `gen:command DelayCommand` 命令创建一个 `DelayCommand`。如下：

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

执行命令行生产消息

```
php bin/hyperf.php demo:command
```

## RPC 远程过程调用

除了典型的消息队列场景，我们还可以通过 AMQP 来实现 RPC 远程过程调用，本组件也为这个实现提供了对应的支持。

### 创建消费者

RPC 使用的消费者，与典型消息队列场景的消费者实现基本无差，唯一的区别是需要通过调用 `reply` 方法返回数据给生产者。

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

### 发起 RPC 调用

作为生成者发起一次 RPC 远程过程调用也非常的简单，只需通过依赖注入容器获得 `Hyperf\Amqp\RpcClient` 对象并调用其中的 `call` 方法即可，返回的结果是消费者 reply 的数据，如下所示：

```php
<?php
use Hyperf\Amqp\Message\DynamicRpcMessage;
use Hyperf\Amqp\RpcClient;
use Hyperf\Context\ApplicationContext;

$rpcClient = ApplicationContext::getContainer()->get(RpcClient::class);
// 在 DynamicRpcMessage 上设置与 Consumer 一致的 Exchange 和 RoutingKey
$result = $rpcClient->call(new DynamicRpcMessage('hyperf', 'hyperf', ['message' => 'Hello Hyperf'])); 

// $result:
// array(1) {
//     ["message"]=>
//     string(18) "Reply:Hello Hyperf"
// }
```

### 抽象 RpcMessage

上面的 RPC 调用过程是直接通过 `Hyperf\Amqp\Message\DynamicRpcMessage` 类来完成 Exchange 和 RoutingKey 的定义，并传递消息数据，在生产项目的设计上，我们可以对 RpcMessage 进行一层抽象，以统一 Exchange 和 RoutingKey 的定义。   

我们可以创建对应的 RpcMessage 类如 `App\Amqp\FooRpcMessage` 如下：

```php
<?php
use Hyperf\Amqp\Message\RpcMessage;

class FooRpcMessage extends RpcMessage
{

    protected string $exchange = 'hyperf';

    protected array|string $routingKey = 'hyperf';
    
    public function __construct($data)
    {
        // 要传递数据
        $this->payload = $data;
    }

}
```

这样我们进行 RPC 调用时，只需直接传递 `FooRpcMessage` 实例到 `call` 方法即可，无需每次调用时都去定义 Exchange 和 RoutingKey。
