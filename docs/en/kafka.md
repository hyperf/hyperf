# Kafka

`Kafka` is an open source stream processing platform developed by `Apache Software Foundation`, written by `Scala` and `Java`. The goal of this project is to provide a unified, high-throughput, low-latency platform for processing real-time data. Its persistence layer is essentially a "large-scale publish/subscribe message queue based on the distributed transaction log architecture"

[longlang/phpkafka](https://github.com/swoole/phpkafka) component is provided by [Longzhiyan](http://longlang.org/) and supports `PHP-FPM` and `Swoole`. Thank you `Swoole Team` and `ZenTao Team` for their contributions to the community.

## Installation

```bash
composer require hyperf/kafka
```

## Version requirements

- Kafka >= 1.0.0

## Usage

### Configuration

The configuration file of the `kafka` component is located in `config/autoload/kafka.php` by default. If the file does not exist, you can use the `php bin/hyperf.php vendor:publish hyperf/kafka` command to publish the corresponding configuration file.

The default configuration file is as follows:

|         Configuration         |    Type    |            Default            |                                                                                                Description                                                                                                |
|:-----------------------------:| :--------: | :---------------------------: |:---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|
|        connect_timeout        | int｜float |              -1               |                                                          Connection timeout time (unit: second, support decimal), if it is-1, there is no limit                                                           |
|         send_timeout          | int｜float |              -1               |                                                             Send timeout time (unit: second, support decimal), if it is-1, there is no limit                                                              |
|         recv_timeout          | int｜float |              -1               |                                                           Receiving timeout time (unit: second, support decimal), if it is-1, there is no limit                                                           |
|           client_id           |   string   |             null              |                                                                                              Kafka Client ID                                                                                              |
|      max_write_attempts       |    int     |               3               |                                                                                     Maximum number of write attempts                                                                                      |
|       bootstrap_servers       |   array    |       '127.0.0.1:9092'        |                                       Bootstrap servers, if this value is configured, it will automatically connect to the server and automatically update brokers                                        |
|             acks              |    int     |               0               | The producer asks the leader to confirm the value that has been received before the confirmation request is completed. Allowed values: 0 means no confirmation, 1 means leader only,-1 means complete ISR |
|          producer_id          |    int     |              -1               |                                                                                                Producer ID                                                                                                |
|        producer_epoch         |    int     |              -1               |                                                                                              Producer Epoch                                                                                               |
|    partition_leader_epoch     |    int     |              -1               |                                                                                          Partition Leader Epoch                                                                                           |
|           interval            | int｜float |               0               |                                        How many seconds to delay trying again when the message is not received, the default is 0, no delay (unit: second, decimal)                                        |
|        session_timeout        | int｜float |              60               |                                If no heartbeat signal is received after the timeout, the coordinator will consider the user dead. (Unit: seconds, decimals are supported)                                 |
|       rebalance_timeout       | int｜float |              60               |                                   The longest time the coordinator waits for each member to rejoin when rebalancing the group (unit: seconds, decimals are supported).                                    |
|          replica_id           |    int     |              -1               |                                                                                                Replica ID                                                                                                 |
|            rack_id            |    int     |              -1               |                                                                                                Rack Number                                                                                                |
|          group_retry          |    int     |               5               |                                                          Grouping operation, the number of automatic retries when matching the preset error code                                                          |
|       group_retry_sleep       |    int     |               1               |                                                                                 Group operation retry delay, unit: second                                                                                 |
|        group_heartbeat        |    int     |               3               |                                                                                  Group heartbeat interval, unit: second                                                                                   |
|         offset_retry          |    int     |               5               |                                                           Offset operation, the number of automatic retries when matching the preset error code                                                           |
|       auto_create_topic       |    bool    |             true              |                                                                                   Whether to automatically create topic                                                                                   |
| partition_assignment_strategy |   string   | KafkaStrategy::RANGE_ASSIGNOR |                     Consumer partition allocation strategy, optional: range allocation (`KafkaStrategy::RANGE_ASSIGNOR`) polling allocation (`KafkaStrategy::ROUND_ROBIN_ASSIGNOR`))                      |

```php
<?php

declare(strict_types=1);

use Hyperf\Kafka\Constants\KafkaStrategy;

return [
    'default' => [
        'connect_timeout' => -1,
        'send_timeout' => -1,
        'recv_timeout' => -1,
        'client_id' => '',
        'max_write_attempts' => 3,
        'bootstrap_servers' => '127.0.0.1:9092',
        'acks' => 0,
        'producer_id' => -1,
        'producer_epoch' => -1,
        'partition_leader_epoch' => -1,
        'interval' => 0,
        'session_timeout' => 60,
        'rebalance_timeout' => 60,
        'replica_id' => -1,
        'rack_id' => '',
        'group_retry' => 5,
        'group_retry_sleep' => 1,
        'group_heartbeat' => 3,
        'offset_retry' => 5,
        'auto_create_topic' => true,
        'partition_assignment_strategy' => KafkaStrategy::RANGE_ASSIGNOR,
        'sasl' => [],
        'ssl' => [],
    ],
];
```

### Creating a consumer

The `gen:kafka-consumer` command can quickly generate a consumer (Consumer) to consume the message.

```bash
php bin/hyperf.php gen:kafka-consumer KafkaConsumer
```

You can also use the `Hyperf\Kafka\Annotation\Consumer` annotation to declare a subclass of the `Hyperf/Kafka/AbstractConsumer` abstract class to complete the definition of a `Consumer`, where `Hyperf\ Both Kafka\Annotation\Consumer` annotations and abstract classes contain the following attributes:

| Configuration |        Type        |    Default    |                                           Description                                            |
| :-----------: | :----------------: | :-----------: | :----------------------------------------------------------------------------------------------: |
|     topic     | string or string[] |      ''       |                                         topic to monitor                                         |
|    groupId    |       string       |      ''       |                                     groupId to be monitored                                      |
|   memberId    |       string       |      ''       |                                     memberId to be monitored                                     |
|  autoCommit   |       string       |      ''       |                                 Whether to commit automatically                                  |
|     name      |       string       | KafkaConsumer |                                         Consumer's name                                          |
|     nums      |        int         |       1       |                                   Number of consumer processes                                   |
|     pool      |       string       |    default    | The connection corresponding to the consumer, corresponding to the key of the configuration file |

```php
<?php

declare(strict_types=1);

namespace App\kafka;

use Hyperf\Kafka\AbstractConsumer;
use Hyperf\Kafka\Annotation\Consumer;
use longlang\phpkafka\Consumer\ConsumeMessage;

#[Consumer(topic: "hyperf", nums: 5, groupId: "hyperf", autoCommit: true)]
class KafkaConsumer extends AbstractConsumer
{
    public function consume(ConsumeMessage $message): string
    {
        var_dump($message->getTopic() . ':' . $message->getKey() . ':' . $message->getValue());
    }
}
```

### Producing a message

You can call `Hyperf\Kafka\Producer::send(string $topic, ?string $value, ?string $key = null, array $headers = [], ?int $partitionIndex = null)` to deliver messages, the following is an example of message delivery in `Controller`:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\Kafka\Producer;

#[AutoController]
class IndexController extends AbstractController
{
    public function index(Producer $producer)
    {
        $producer->send('hyperf', 'value', 'key');
    }
}
```

The `Hyperf\Kafka\Producer::send()` method will wait for ACK. If you do not need to wait for ACK, you can use the `Hyperf\Kafka\Producer::sendAsync()` method to deliver the message.

### Send multiple messages at once

The `Hyperf\Kafka\Producer::sendBatch(array $messages)` method is used to deliver messages in batches to `kafka`, the following is an example of message delivery in `Controller`:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\Kafka\Producer;
use longlang\phpkafka\Producer\ProduceMessage;

#[AutoController]
class IndexController extends AbstractController
{
    public function index(Producer $producer)
    {
        $producer->sendBatch([
            new ProduceMessage('hyperf1', 'hyperf1_value', 'hyperf1_key'),
            new ProduceMessage('hyperf2', 'hyperf2_value', 'hyperf2_key'),
            new ProduceMessage('hyperf3', 'hyperf3_value', 'hyperf3_key'),
        ]);

    }
}
```
