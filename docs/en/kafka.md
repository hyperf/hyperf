# Kafka

`Kafka` is an open-source stream processing platform developed by the `Apache Software Foundation`, written in `Scala` and `Java`. The goal of the project is to provide a unified, high-throughput, low-latency platform for processing real-time data. Its persistence layer is essentially a "large-scale publish/subscribe message queue based on distributed transaction log architecture".

[longlang/phpkafka](https://github.com/swoole/phpkafka) component is provided by [Longzhiyan](http://longlang.org/), supporting both `PHP-FPM` and `Swoole`. Thanks to the `Swoole team` and the `ZenTao team` for their contributions to the community.

## Installation

```bash
composer require hyperf/kafka
```

## Version Requirements

- Kafka >= 1.0.0

## Usage

### Configuration

The configuration file for the `kafka` component is located at `config/autoload/kafka.php` by default. If this file does not exist, you can publish it using the command `php bin/hyperf.php vendor:publish hyperf/kafka`.

The default configuration file is as follows:

| Configuration | Type | Default Value | Remark |
| :--- | :--- | :--- | :--- |
| connect_timeout | int\|float | -1 | Connection timeout (unit: seconds, decimals supported), -1 means no limit |
| send_timeout | int\|float | -1 | Send timeout (unit: seconds, decimals supported), -1 means no limit |
| recv_timeout | int\|float | -1 | Receive timeout (unit: seconds, decimals supported), -1 means no limit |
| client_id | string | null | Kafka client identifier |
| max_write_attempts | int | 3 | Maximum write attempts |
| bootstrap_servers | array | '127.0.0.1:9092' | Bootstrap servers. If this value is configured, it will automatically connect to this server and update brokers automatically |
| acks | int | 0 | The number of acknowledgments the producer requires the leader to have received before considering a request complete. Allowed values: 0 for no acknowledgment, 1 for leader only, -1 for full ISR |
| producer_id | int | -1 | Producer ID |
| producer_epoch | int | -1 | Producer Epoch |
| partition_leader_epoch | int | -1 | Partition Leader Epoch |
| interval | int\|float | 0 | Delay in seconds to retry when no message is obtained, default is 0 for no delay (unit: seconds, decimals supported) |
| session_timeout | int\|float | 60 | If no heartbeat is received after the timeout, the coordinator will consider the user dead. (unit: seconds, decimals supported) |
| rebalance_timeout | int\|float | 60 | The maximum time the coordinator waits for each member to rejoin when rebalancing the group (unit: seconds, decimals supported) |
| replica_id | int | -1 | Replica ID |
| rack_id | int | -1 | Rack ID |
| group_retry | int | 5 | Automatic retry count for group operations when matching preset error codes |
| group_retry_sleep | int | 1 | Delay for group operation retries, unit: seconds |
| group_heartbeat | int | 3 | Heartbeat interval for groups, unit: seconds |
| offset_retry | int | 5 | Automatic retry count for offset operations when matching preset error codes |
| auto_create_topic | bool | true | Whether to automatically create topic |
| partition_assignment_strategy | string | KafkaStrategy::RANGE_ASSIGNOR | Consumer partition assignment strategy, options: Range assignment (`KafkaStrategy::RANGE_ASSIGNOR`), Round Robin assignment (`KafkaStrategy::ROUND_ROBIN_ASSIGNOR`) |
| sasl | array | [] | SASL authentication information. If empty, no authentication information is sent. phpkafka version must be >= 1.2 |
| ssl | array | [] | SSL connection related information. If empty, SSL is not used. phpkafka version must be >= 1.2 |

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

### Creating Consumers

You can quickly generate a Consumer to consume messages using the `gen:kafka-consumer` command.

```bash
php bin/hyperf.php gen:kafka-consumer KafkaConsumer
```

You can also use the `Hyperf\Kafka\Annotation\Consumer` annotation to declare a subclass of the `Hyperf/Kafka/AbstractConsumer` abstract class to define a `Consumer`. Both the `Hyperf\Kafka\Annotation\Consumer` annotation and the abstract class contain the following properties:

| Configuration | Type | Annotation or Abstract Class Default Value | Remark |
| :--- | :--- | :--- | :--- |
| topic | string or string[] | '' | Topic to listen to |
| groupId | string | '' | GroupId to listen to |
| memberId | string | '' | MemberId to listen to |
| autoCommit | string | '' | Whether auto-commit is needed |
| name | string | KafkaConsumer | Name of the consumer |
| nums | int | 1 | Number of consumer processes |
| pool | string | default | Connection used by the consumer, corresponds to the key in the configuration file |

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

### Producing Messages

You can produce messages to `kafka` by calling the `Hyperf\Kafka\Producer::send(string $topic, ?string $value, ?string $key = null, array $headers = [], ?int $partitionIndex = null)` method. The following is an example of message production in `Controller`:

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

The `Hyperf\Kafka\Producer::send()` method waits for ACK. If you do not need to wait for ACK, you can use the `Hyperf\Kafka\Producer::sendAsync()` method to deliver messages.

### Delivering Multiple Messages at Once

Use the `Hyperf\Kafka\Producer::sendBatch(array $messages)` method to deliver messages to `kafka` in batches. The following is an example of message delivery in `Controller`:

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

### SASL Configuration Instructions

| Parameter Name | Description | Default Value |
| :--- | :--- | :--- |
| type | The class corresponding to SASL authorization. PLAIN is `\longlang\phpkafka\Sasl\PlainSasl::class` | '' |
| username | Username | '' |
| password | Password | '' |

### SSL Configuration Instructions

| Parameter Name | Description | Default Value |
| :--- | :--- | :--- |
| open | Whether to enable SSL transport encryption | `false` |
| compression | Whether to enable compression | `true` |
| certFile | Path to cert certificate | `''` |
| keyFile | Path to private key | `''` |
| passphrase | Cert certificate password | `''` |
| peerName | Server host name. Defaults to the connected host | `''` |
| verifyPeer | Whether to verify the remote certificate | `false` |
| verifyPeerName | Whether to verify the remote server name | `false` |
| verifyDepth | If the certificate chain hierarchy is too deep and exceeds the value set by this option, verification will be terminated. Hierarchy is not verified by default | `0` |
| allowSelfSigned | Whether to allow self-signed certificates | `false` |
| cafile | CA certificate path | `''` |
| capath | CA certificate directory. All pem files in this path will be automatically scanned | `''` |
