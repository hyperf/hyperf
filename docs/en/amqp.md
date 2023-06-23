# AMQP

[https://github.com/hyperf/amqp](https://github.com/hyperf/amqp)

## Default Config
```php
<?php

return [
    'default' => [
        'host' => 'localhost',
        'port' => 5672,
        'user' => 'guest',
        'password' => 'guest',
        'vhost' => '/',
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
            'heartbeat' => 0,
        ],
    ],
];
```

## Deliver Message

Use generator command to create a producer.
```
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
```
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

#[Consumer(exchange: 'hyperf', routingKey: 'hyperf', queue: 'hyperf', nums: 1)]
class DemoConsumer extends ConsumerMessage
{
    public function consume($data): string
    {
        print_r($data);
        return Result::ACK;
    }
}
```

The framework automatically creates the process according to Consumer annotations, and the process will be pulled up again after unexpected exit.
