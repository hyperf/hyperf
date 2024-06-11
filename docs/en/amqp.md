# AMQP

[hyperf/amqp](https://github.com/hyperf/amqp)

## Installation

```bash
composer require hyperf/amqp
```

## Default Config

|   Configuration  |  Type  |  Default value   |                        Remark                       |
|:----------------:|:------:|:----------------:|:---------------------------------------------------:|
|       host       | string |     localhost    |                         Host                        |
|       port       |  int   |       5672       |                     Port number                     |
|       user       | string |       guest      |                       Username                      |
|     password     | string |       guest      |                       Password                      |
|      vhost       | string |         /        |                         vhost                       |
| concurrent.limit |  int   |         0        |      Maximum quantity consumed simultaneously       |
|       pool       | object |                  |   Connection pool configuration                     |
| pool.connections |  int   |         1        | Number of connections maintained within the process |
|      params      | object |                  |                   Basic configurations              |

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
            // Try to maintain twice value heartbeat as much as possible
            'read_write_timeout' => 3.0,
            'context' => null,
            'keepalive' => false,
            // Try to ensure that the consumption time of each message is less than the heartbeat time as much as possible
            'heartbeat' => 0,
            'close_on_destruct' => false,
        ],
    ],
    'pool2' => [
        ...
    ]
];
```

## Deliver Message

Use generator command to create a producer.
```bash
php bin/hyperf.php gen:amqp-producer DemoProducer
```

We can modify the Producer annotation to replace exchange and routingKey.
Payload is the data that is finally delivered to the message queue, so we can rewrite the _construct method easily,just make sure payload is assigned.

```php
<?php

declare(strict_types=1);

namespace App\Amqp\Producers;

use Hyperf\Amqp\Annotation\Producer;
use Hyperf\Amqp\Message\ProducerMessage;
use App\Models\User;

#[Producer(exchange: 'hyperf', routingKey: 'hyperf')]
class DemoProducer extends ProducerMessage
{
    public function __construct($id)
    {
        $user = User::where('id', $id)->first();
        $this->payload = [
            'id' => $id,
            'data' => $user->toArray()
        ];
    }
}

```

Get the Producer instance through container, and you can deliver the message. It is not reasonable for the following examples to use Application Context directly to get the Producer. For the specific use of container, see the di module.

```php
<?php
use Hyperf\Amqp\Producer;
use App\Amqp\Producers\DemoProducer;
use Hyperf\Context\ApplicationContext;

$message = new DemoProducer(1);
$producer = ApplicationContext::getContainer()->get(Producer::class);
$result = $producer->produce($message);

```

## Consume Message

Use generator command to create a consumer.
```bash
php bin/hyperf.php gen:amqp-consumer DemoConsumer
```

we can modify the Consumer annotation to replace exchange, routingKey and queue.
And $data is parsed metadata.

```php
<?php

declare(strict_types=1);

namespace App\Amqp\Consumers;

use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\Amqp\Result;
use PhpAmqpLib\Message\AMQPMessage;

#[Consumer(exchange: 'hyperf', routingKey: 'hyperf', queue: 'hyperf', nums: 1)]
class DemoConsumer extends ConsumerMessage
{
    public function consumeMessage($data, AMQPMessage $message): Result
    {
        print_r($data);
        return Result::ACK;
    }
}
```

The framework automatically creates the process according to Consumer annotations, and the process will be pulled up again after unexpected exit.

### Set concurrency consumption

There are three parameters that affect the rate of consumption

- you can modify the `#[Consumer]` annotation `nums` to open multiple consumers
- The `ConsumerMessage` base class has an attribute `$qos` that controls the number of messages pulled from the server at a time by overriding `prefetch_size` or `prefetch_count` in `$qos`
- `concurrent.limit` in the configuration file, which controls the maximum number of consumer coroutines

### Consumption results

The framework will determine the response behavior of the message based on the result returned by the `consume` method in `Consumer`. There are 4 response results, namely `\Hyperf\Amqp\Result::ACK`, `\Hyperf\Amqp\ Result::NACK`, `\Hyperf\Amqp\Result::REQUEUE`, `\Hyperf\Amqp\Result::DROP`, each return value represents the following behavior:

| Return                       | Behavior                                                                 |
|------------------------------|----------------------------------------------------------------------|
| \Hyperf\Amqp\Result::ACK     | Confirm that the message has been consumed correctly                                               |
| \Hyperf\Amqp\Result::NACK    | The message was not consumed correctly, respond with the `basic_nack` method                     |
| \Hyperf\Amqp\Result::REQUEUE | The message was not consumed correctly, respond with the `basic_reject` method and requeue the message |
| \Hyperf\Amqp\Result::DROP    | The message was not consumed correctly, respond with the `basic_reject` method                   |

### Customize the number of consumer processes according to the environment

In the `#[Consumer]` annotation, you can set the number of consumer processes through the `nums` attribute. If you need to set different numbers of consumer processes according to different environments, you can override the `getNums` method. The example is as follows:

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



## Delay queue

AMQP's delay queue is not sorted according to the delay time. Therefore, once you deliver a task with a delay of 10 seconds and then deliver a task with a delay of 5 seconds to this queue, it will definitely be in the first place. After the first 10s task is completed, the second 5s task will be consumed.
Therefore, you need to set up different queues according to time. If you want a more flexible delay queue, you can try using asynchronous queue (async-queue) in conjunction with AMQP.

In addition, AMQP needs to download [delay plug-in](https://github.com/rabbitmq/rabbitmq-delayed-message-exchange/releases) and activate it for normal use

```shell
wget https://github.com/rabbitmq/rabbitmq-delayed-message-exchange/releases/download/3.9.0/rabbitmq_delayed_message_exchange-3.9.0.ez
cp rabbitmq_delayed_message_exchange-3.9.0.ez /opt/rabbitmq/plugins/
rabbitmq-plugins enable rabbitmq_delayed_message_exchange
```

### Producer

Create a `producer` using the `gen:amqp-producer` command. Here is an example of the `direct` type. For other types such as `fanout` and `topic`, just change the `type` in the producer and consumer.

```bash
php bin/hyperf.php gen:amqp-producer DelayDirectProducer
```

In the DelayDirectProducer file, add `use ProducerDelayedMessageTrait;`, the example is as follows:

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

    protected $exchange = 'ext.hyperf.delay';

    protected $type = Type::DIRECT;

    protected $routingKey = '';

    public function __construct($data)
    {
        $this->payload = $data;
    }
}
```
### Consumer

Create a `consumer` using the `gen:amqp-consumer` command.

```bash
php bin/hyperf.php gen:amqp-consumer DelayDirectConsumer
```

In the `DelayDirectConsumer` file, add and introduce `use ProducerDelayedMessageTrait, ConsumerDelayedMessageTrait;`, the example is as follows:

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

    protected $exchange = 'ext.hyperf.delay';
    
    protected $queue = 'queue.hyperf.delay';
    
    protected $type = Type::DIRECT; //Type::FANOUT;
    
    protected $routingKey = '';

    public function consumeMessage($data, AMQPMessage $message): Result
    {
        var_dump($data, 'delay+direct consumeTime:' . (microtime(true)));
        return Result::ACK;
    }
}

```

### Production delay message

> The following is a demonstration of how to use it in Command. Please refer to the actual usage for specific usage.

Create a `DelayCommand` using the `gen:command DelayCommand` command. as follows:

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
Execute command line to produce messages
```
php bin/hyperf.php demo:command
```


## RPC remote procedure call

In addition to typical message queue scenarios, we can also implement RPC remote procedure calls through AMQP. This component also provides corresponding support for this implementation.

### Create consumer

The consumer used by RPC is basically the same as the consumer implementation in a typical message queue scenario. The only difference is that the data needs to be returned to the producer by calling the `reply` method.

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

### Make an RPC call

It is also very simple to initiate an RPC remote procedure call as a generator. You only need to obtain the `Hyperf\Amqp\RpcClient` object through the dependency injection container and call the `call` method in it. The returned result is the consumer reply data. As follows:

```php
<?php
use Hyperf\Amqp\Message\DynamicRpcMessage;
use Hyperf\Amqp\RpcClient;
use Hyperf\Context\ApplicationContext;

$rpcClient = ApplicationContext::getContainer()->get(RpcClient::class);
//Set Exchange and RoutingKey consistent with Consumer on DynamicRpcMessage
$result = $rpcClient->call(new DynamicRpcMessage('hyperf', 'hyperf', ['message' => 'Hello Hyperf'])); 

// $result:
// array(1) {
//     ["message"]=>
//     string(18) "Reply:Hello Hyperf"
// }
```

### Abstract RpcMessage

The above RPC calling process directly completes the definition of Exchange and RoutingKey through the `Hyperf\Amqp\Message\DynamicRpcMessage` class, and transfers message data. In the design of production projects, we can perform a layer of abstraction on RpcMessage to unify Exchange. and the definition of RoutingKey.

We can create the corresponding RpcMessage class such as `App\Amqp\FooRpcMessage` as follows:

```php
<?php
use Hyperf\Amqp\Message\RpcMessage;

class FooRpcMessage extends RpcMessage
{

    protected $exchange = 'hyperf';

    protected $routingKey = 'hyperf';
    
    public function __construct($data)
    {
        //To pass data
        $this->payload = $data;
    }

}
```

In this way, when we make an RPC call, we only need to directly pass the `FooRpcMessage` instance to the `call` method without having to define Exchange and RoutingKey every time it is called.
