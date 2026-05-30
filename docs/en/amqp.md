# AMQP Component

[hyperf/amqp](https://github.com/hyperf/amqp) is a component that implements the AMQP standard, primarily used for RabbitMQ integration.

## Installation

```bash
composer require hyperf/amqp
```

## Default Configuration

| Configuration | Type | Default Value | Remark |
| :--- | :--- | :--- | :--- |
| host | string | localhost | Host |
| port | int | 5672 | Port |
| user | string | guest | Username |
| password | string | guest | Password |
| vhost | string | / | vhost |
| concurrent.limit | int | 0 | Max number of concurrent consumers |
| pool | object | | Connection pool configuration |
| pool.connections | int | 1 | Number of connections kept within a process |
| params | object | | Basic configuration |

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
            // Try to keep it twice the heartbeat value
            'read_write_timeout' => 6.0,
            'context' => null,
            'keepalive' => false,
            // Try to ensure that the consumption time of each message is less than the heartbeat time
            'heartbeat' => 3,
            'close_on_destruct' => false,
        ],
    ],
    'pool2' => [
        ...
    ]
];
```

You can set different `pool`s in the `__construct` function of `producer` or `consumer`, such as the `default` and `pool2` mentioned above.

## Producing Messages

Use the `gen:producer` command to create a `producer`.

```bash
php bin/hyperf.php gen:amqp-producer DemoProducer
```

In the DemoProducer file, we can modify the fields corresponding to the `#[Producer]` annotation to replace the corresponding `exchange` and `routingKey`.
The `payload` is the data that will be finally delivered to the message queue, so we can rewrite the `__construct` method at will, as long as we finally assign the `payload`.
An example is as follows.

> When using the `#[Producer]` annotation, you need to `use Hyperf\Amqp\Annotation\Producer;` namespace;

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
        // Set different pool
        $this->poolName = 'pool2';

        $user = User::where('id', $id)->first();
        $this->payload = [
            'id' => $id,
            'data' => $user->toArray()
        ];
    }
}
```

Get the `Hyperf\Amqp\Producer` instance through the DI Container to produce messages. The following example directly using `ApplicationContext` to obtain `Hyperf\Amqp\Producer` is actually not reasonable. For the specific use of DI Container, please check the [Dependency Injection](en/di.md) chapter.

```php
<?php
use Hyperf\Amqp\Producer;
use App\Amqp\Producers\DemoProducer;
use Hyperf\Context\ApplicationContext;

$message = new DemoProducer(1);
$producer = ApplicationContext::getContainer()->get(Producer::class);
$result = $producer->produce($message);
```

## Consuming Messages

Use the `gen:amqp-consumer` command to create a `consumer`.

```bash
php bin/hyperf.php gen:amqp-consumer DemoConsumer
```

In the DemoConsumer file, we can modify the fields corresponding to the `#[Consumer]` annotation to replace the corresponding `exchange`, `routingKey`, and `queue`.
The `$data` is the parsed message data.
An example is as follows.

> When using the `#[Consumer]` annotation, you need to `use Hyperf\Amqp\Annotation\Consumer;` namespace;

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

### Prohibit Consumer Process from Automatically Starting

By default, after using the `#[Consumer]` annotation, the framework will automatically create sub-processes to start consumers, and will restart them after the sub-processes exit abnormally.
If you are in the development stage and debugging consumers, it may be inconvenient to debug due to consuming other messages.

In this case, you only need to configure `enable=false` (default is `true` to start with the service) in the `#[Consumer]` annotation, or override the class method `isEnable()` in the corresponding consumer to return `false`.

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

### Set Maximum Consumption Count

You can modify the `maxConsumption` property in the `#[Consumer]` annotation to set the maximum number of messages processed by this consumer. After reaching the specified consumption count, the consumer process will restart.

### Setting Concurrent Consumption

There are three places that affect the consumption rate:

- You can modify the `nums` attribute of the `#[Consumer]` annotation to start multiple consumers
- There is an attribute `$qos` under the `ConsumerMessage` base class. You can control the number of messages pulled from the server each time by overriding the values of `prefetch_size` or `prefetch_count` in `$qos`
- The `concurrent.limit` parameter in the configuration file controls the maximum number of consumption coroutines

### Consumption Result

The framework decides the response behavior of the message based on the result returned by the `consume` method in `Consumer`. There are 4 types of response results: `\Hyperf\Amqp\Result::ACK`, `\Hyperf\Amqp\Result::NACK`, `\Hyperf\Amqp\Result::REQUEUE`, `\Hyperf\Amqp\Result::DROP`. Each return value represents the following behavior:

| Return Value | Behavior |
| :--- | :--- |
| \Hyperf\Amqp\Result::ACK | Confirm that the message was consumed correctly |
| \Hyperf\Amqp\Result::NACK | The message was not consumed correctly, respond using `basic_nack` method |
| \Hyperf\Amqp\Result::REQUEUE | The message was not consumed correctly, respond using `basic_reject` method, and requeue the message |
| \Hyperf\Amqp\Result::DROP | The message was not consumed correctly, respond using `basic_reject` method |

### QOS Configuration

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
        // AMQP does not implement this configuration by default.
        'prefetch_size' => 0,
        // The maximum number of messages that the same consumer can process at the same time.
        'prefetch_count' => 30,
        // Because Hyperf consumes one queue per Channel by default, the effect of setting global to true/false is the same.
        'global' => false,
    ];
    
    public function consumeMessage($data, AMQPMessage $message): Result
    {
        print_r($data);
        return Result::ACK;
    }
}
```

### Customize the Number of Consumer Processes Based on Environment

In the `#[Consumer]` annotation, you can set the number of consumer processes through the `nums` attribute. If you need to set different numbers of consumer processes according to different environments, you can override the `getNums` method to achieve this, as shown below:

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

## Delayed Queue

The delayed queue of AMQP does not sort based on the delay time. Therefore, once you deliver a task with a 10s delay, and then deliver a task with a 5s delay to this queue, the second 5s task will certainly be consumed only after the first 10s task is completed.
Therefore, you need to set different queues according to time. If you want a more flexible delayed queue, you can try using `async-queue` in combination with AMQP.

In addition, AMQP needs to download the [delayed plugin](https://github.com/rabbitmq/rabbitmq-delayed-message-exchange/releases) and activate it to use it normally.

```shell
wget https://github.com/rabbitmq/rabbitmq-delayed-message-exchange/releases/download/3.9.0/rabbitmq_delayed_message_exchange-3.9.0.ez
cp rabbitmq_delayed_message_exchange-3.9.0.ez /opt/rabbitmq/plugins/
rabbitmq-plugins enable rabbitmq_delayed_message_exchange
```

### Producer

Use the `gen:amqp-producer` command to create a `producer`. Here is an example of `direct` type. For other types like `fanout`, `topic`, just change the `type` in the producer and consumer.

```bash
php bin/hyperf.php gen:amqp-producer DelayDirectProducer
```

In the DelayDirectProducer file, add `use ProducerDelayedMessageTrait;`, as shown below:

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

### Consumer

Use the `gen:amqp-consumer` command to create a `consumer`.

```bash
php bin/hyperf.php gen:amqp-consumer DelayDirectConsumer
```

In the `DelayDirectConsumer` file, add the introduction of `use ProducerDelayedMessageTrait, ConsumerDelayedMessageTrait;`, as shown below:

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

### Producing Delayed Messages

> The following is a demonstration of how to use it in Command, please refer to the actual usage for specific usage.

Use the `gen:command DelayCommand` command to create a `DelayCommand`. As follows:

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

Execute the command line to produce messages:

```
php bin/hyperf.php demo:command
```

## RPC Remote Procedure Call

In addition to typical message queue scenarios, we can also implement RPC remote procedure calls through AMQP. This component also provides corresponding support for this implementation.

### Create Consumer

The consumer used by RPC is basically the same as the consumer implementation in typical message queue scenarios. The only difference is that you need to return data to the producer by calling the `reply` method.

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

### Initiate RPC Call

As a producer initiating an RPC remote procedure call, it is also very simple. Just obtain the `Hyperf\Amqp\RpcClient` object through the dependency injection container and call the `call` method. The returned result is the data replied by the consumer, as shown below:

```php
<?php
use Hyperf\Amqp\Message\DynamicRpcMessage;
use Hyperf\Amqp\RpcClient;
use Hyperf\Context\ApplicationContext;

$rpcClient = ApplicationContext::getContainer()->get(RpcClient::class);
// Set the Exchange and RoutingKey consistent with Consumer on DynamicRpcMessage
$result = $rpcClient->call(new DynamicRpcMessage('hyperf', 'hyperf', ['message' => 'Hello Hyperf'])); 

// $result:
// array(1) {
//     ["message"]=>
//     string(18) "Reply:Hello Hyperf"
// }
```

### Abstract RpcMessage

The RPC calling process above directly completes the definition of Exchange and RoutingKey through the `Hyperf\Amqp\Message\DynamicRpcMessage` class and passes message data. In the design of production projects, we can abstract RpcMessage to unify the definition of Exchange and RoutingKey.

We can create the corresponding RpcMessage class such as `App\Amqp\FooRpcMessage` as follows:

```php
<?php
use Hyperf\Amqp\Message\RpcMessage;

class FooRpcMessage extends RpcMessage
{

    protected string $exchange = 'hyperf';

    protected array|string $routingKey = 'hyperf';
    
    public function __construct($data)
    {
        // Data to be passed
        $this->payload = $data;
    }

}
```

In this way, when we perform an RPC call, we only need to pass the `FooRpcMessage` instance directly to the `call` method, without defining Exchange and RoutingKey every time we call it.
