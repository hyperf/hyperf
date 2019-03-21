# AMQP组件

[hyperf-cloud/amqp](https://github.com/hyperf-cloud/amqp)

## 默认配置
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

## 投递消息

使用 `generator` 工具新建一个 `producer`
```
php bin/hyperf.php gen:amqp-producer DemoProducer
```

在DemoProducer文件中，我们可以修改Producer注解对应的字段来替换对应的 `exchange` 和 `routingKey`。
其中 `payload` 就是最终投递到消息队列中的数据，所以我们可以随意改写 `__construct` 方法，只要最后赋值 `payload` 即可。
示例如下。

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
        $user = User::where('id', $id)->first();
        $this->payload = [
            'id' => $id,
            'data' => $user->toArray()
        ];
    }
}

```

通过container获取Producer实例，即可投递消息。以下实例直接使用ApplicationContext获取Producer其实并不合理，container具体使用请到di模块中查看。

```php
<?php
use Hyperf\Amqp\Producer;
use App\Amqp\Producers\DemoProducer;
use Hyperf\Utils\ApplicationContext;

$message = new DemoProducer(1);
$producer = ApplicationContext::getContainer()->get(Producer::class);
$result = $producer->produce($message);

```

## 消费消息

使用 `generator` 工具新建一个 `consumer`。
```
php bin/hyperf.php gen:amqp-consumer DemoConsumer
```

在DemoConsumer文件中，我们可以修改Consumer注解对应的字段来替换对应的 `exchange`、`routingKey` 和 `queue`。
其中 `$data` 就是解析后的元数据。
示例如下。

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

框架会根据Consumer注解自动创建Process进程，进程意外退出后会被重新拉起。