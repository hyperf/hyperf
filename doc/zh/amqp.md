# AMQP组件

[hyperf/amqp](https://github.com/hyperf/amqp) 是实现 AMQP 标准的组件，主要适用于对 RabbitMQ 的使用。

## 安装

```bash
composer require hyperf/amqp
```

## 默认配置

|       配置       |  类型  |  默认值   |      备注      |
|:----------------:|:------:|:---------:|:--------------:|
|       host       | string | localhost |      Host      |
|       port       |  int   |   5672    |     端口号     |
|       user       | string |   guest   |     用户名     |
|     password     | string |   guest   |      密码      |
|      vhost       | string |     /     |     vhost      |
| concurrent.limit |  int   |     0     | 同时消费的数量 |
|       pool       | object |           |   连接池配置   |
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
            'heartbeat' => 0,
        ],
    ],
    'pool2' => [
        ...
    ]
];
```

可在 `producer` 或者 `consumer` 的 `__construct` 函数中, 设置不同 `pool`.

## 投递消息

使用 `gen:producer` 命令创建一个 `producer`

```bash
php bin/hyperf.php gen:amqp-producer DemoProducer
```

在 DemoProducer 文件中，我们可以修改 `@Producer` 注解对应的字段来替换对应的 `exchange` 和 `routingKey`。
其中 `payload` 就是最终投递到消息队列中的数据，所以我们可以随意改写 `__construct` 方法，只要最后赋值 `payload` 即可。
示例如下。

> 使用 `@Producer` 注解时需 `use Hyperf\Amqp\Annotation\Producer;` 命名空间；   

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

通过 DI Container 获取 `Hyperf\Amqp\Producer` 实例，即可投递消息。以下实例直接使用 `ApplicationContext` 获取 `Hyperf\Amqp\Producer` 其实并不合理，DI Container 具体使用请到 [依赖注入](zh/di.md) 章节中查看。

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

使用 `gen:amqp-consumer` 命令创建一个 `consumer`。

```bash
php bin/hyperf.php gen:amqp-consumer DemoConsumer
```

在 DemoConsumer 文件中，我们可以修改 `@Consumer` 注解对应的字段来替换对应的 `exchange`、`routingKey` 和 `queue`。
其中 `$data` 就是解析后的消息数据。
示例如下。

> 使用 `@Consumer` 注解时需 `use Hyperf\Amqp\Annotation\Consumer;` 命名空间；   

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

框架会根据 `@Consumer` 注解自动创建 `Process 进程`，进程意外退出后会被重新拉起。

### 消费结果

框架会根据 `Consumer` 内的 `consume` 方法所返回的结果来决定该消息的响应行为，共有 4 中响应结果，分别为 `\Hyperf\Amqp\Result::ACK`、`\Hyperf\Amqp\Result::NACK`、`\Hyperf\Amqp\Result::REQUEUE`、`\Hyperf\Amqp\Result::DROP`，每个返回值分别代表如下行为：

| 返回值                       | 行为                                                                 |
|------------------------------|----------------------------------------------------------------------|
| \Hyperf\Amqp\Result::ACK     | 确认消息正确被消费掉了                                               |
| \Hyperf\Amqp\Result::NACK    | 消息没有被正确消费掉，以 `basic_nack` 方法来响应                     |
| \Hyperf\Amqp\Result::REQUEUE | 消息没有被正确消费掉，以 `basic_reject` 方法来响应，并使消息重新入列 |
| \Hyperf\Amqp\Result::DROP    | 消息没有被正确消费掉，以 `basic_reject` 方法来响应                   |