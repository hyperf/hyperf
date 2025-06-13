# Kafka

`Kafka` 是由 `Apache 軟件基金會` 開發的一個開源流處理平台，由 `Scala` 和 `Java` 編寫。該項目的目標是為處理實時數據提供一個統一、高吞吐、低延遲的平台。其持久化層本質上是一個 "按照分佈式事務日誌架構的大規模發佈/訂閲消息隊列"

[longlang/phpkafka](https://github.com/swoole/phpkafka) 組件由 [龍之言](http://longlang.org/) 提供，支持 `PHP-FPM` 和 `Swoole`。感謝 `Swoole 團隊` 和 `禪道團隊` 對社區做出的貢獻。

## 安裝

```bash
composer require hyperf/kafka
```

## 版本要求

- Kafka >= 1.0.0

## 使用

### 配置

`kafka` 組件的配置文件默認位於 `config/autoload/kafka.php` 內，如該文件不存在，可通過 `php bin/hyperf.php vendor:publish hyperf/kafka` 命令來將發佈對應的配置文件。

默認配置文件如下：


| 配置                          | 類型       | 默認值                        | 備註                                                                                                                 |
| ----------------------------- | ---------- | ----------------------------- | -------------------------------------------------------------------------------------------------------------------- |
| connect_timeout               | int｜float | -1                            | 連接超時時間（單位：秒，支持小數），為 - 1 則不限制                                                                  |
| send_timeout                  | int｜float | -1                            | 發送超時時間（單位：秒，支持小數），為 - 1 則不限制                                                                  |
| recv_timeout                  | int｜float | -1                            | 接收超時時間（單位：秒，支持小數），為 - 1 則不限制                                                                  |
| client_id                     | stirng     | null                          | Kafka 客户端標識                                                                                                     |
| max_write_attempts            | int        | 3                             | 最大寫入嘗試次數                                                                                                     |
| bootstrap_servers             | array      | '127.0.0.1:9092'              | 引導服務器，如果配置了該值，會自動連接該服務器，並自動更新 brokers                                                   |
| acks                          | int        | 0                             | 生產者要求領導者，在確認請求完成之前已收到的確認數值。允許的值：0 表示無確認，1 表示僅領導者，- 1 表示完整的 ISR。   |
| producer_id                   | int        | -1                            | 生產者 ID                                                                                                            |
| producer_epoch                | int        | -1                            | 生產者 Epoch                                                                                                         |
| partition_leader_epoch        | int        | -1                            | 分區 Leader Epoch                                                                                                    |
| interval                      | int｜float | 0                             | 未獲取消息到消息時，延遲多少秒再次嘗試，默認為 0 則不延遲（單位：秒，支持小數）                                      |
| session_timeout               | int｜float | 60                            | 如果超時後沒有收到心跳信號，則協調器會認為該用户死亡。（單位：秒，支持小數）                                         |
| rebalance_timeout             | int｜float | 60                            | 重新平衡組時，協調器等待每個成員重新加入的最長時間（單位：秒，支持小數）。                                           |
| replica_id                    | int        | -1                            | 副本 ID                                                                                                              |
| rack_id                       | int        | -1                            | 機架編號                                                                                                             |
| group_retry                   | int        | 5                             | 分組操作，匹配預設的錯誤碼時，自動重試次數                                                                           |
| group_retry_sleep             | int        | 1                             | 分組操作重試延遲，單位：秒                                                                                           |
| group_heartbeat               | int        | 3                             | 分組心跳時間間隔，單位：秒                                                                                           |
| offset_retry                  | int        | 5                             | 偏移量操作，匹配預設的錯誤碼時，自動重試次數                                                                         |
| auto_create_topic             | bool       | true                          | 是否需要自動創建 topic                                                                                               |
| partition_assignment_strategy | string     | KafkaStrategy::RANGE_ASSIGNOR | 消費者分區分配策略, 可選：範圍分配(`KafkaStrategy::RANGE_ASSIGNOR`) 輪詢分配(`KafkaStrategy::ROUND_ROBIN_ASSIGNOR`)) |
| sasl                          | array      | []                            | SASL 身份認證信息。為空則不發送身份認證信息 phpkafka 版本需 >= 1.2                                                    |
| ssl                           | array      | []                            | SSL 鏈接相關信息, 為空則不使用 SSL phpkafka 版本需 >= 1.2                                                               |


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

### 創建消費者

通過 gen:kafka-consumer 命令可以快速的生成一個 消費者(Consumer) 對消息進行消費。

```bash
php bin/hyperf.php gen:kafka-consumer KafkaConsumer
```

您也可以通過使用 `Hyperf\Kafka\Annotation\Consumer` 註解來對一個 `Hyperf/Kafka/AbstractConsumer` 抽象類的子類進行聲明，來完成一個 `消費者(Consumer)` 的定義，其中 `Hyperf\Kafka\Annotation\Consumer` 註解和抽象類均包含以下屬性：

|    配置    |        類型        | 註解或抽象類默認值 |                 備註                 |
| :--------: | :----------------: | :----------------: | :----------------------------------: |
|   topic    | string or string[] |         ''         |            要監聽的 topic            |
|  groupId   |       string       |         ''         |           要監聽的 groupId           |
|  memberId  |       string       |         ''         |          要監聽的 memberId           |
| autoCommit |       string       |         ''         |           是否需要自動提交           |
|    name    |       string       |   KafkaConsumer    |             消費者的名稱             |
|    nums    |        int         |         1          |            消費者的進程數            |
|    pool    |       string       |      default       | 消費者對應的連接，對應配置文件的 key |


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

### 投遞消息

您可以通過調用 `Hyperf\Kafka\Producer::send(string $topic, ?string $value, ?string $key = null, array $headers = [], ?int $partitionIndex = null)` 方法來向 `kafka` 投遞消息, 下面是在 `Controller` 進行消息投遞的一個示例：

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

`Hyperf\Kafka\Producer::send()` 方法會等待 ACK，如果您不需要等待 ACK，可以使用 `Hyperf\Kafka\Producer::sendAsync()` 方法來投遞消息。

### 一次性投遞多條消息

`Hyperf\Kafka\Producer::sendBatch(array $messages)` 方法來向 `kafka` 批量的投遞消息, 下面是在 `Controller` 進行消息投遞的一個示例：

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

### SASL 配置説明

| 參數名   | 説明                                                                | 默認值 |
| -------- | ------------------------------------------------------------------- | ------ |
| type     | SASL 授權對應的類。PLAIN 為`\longlang\phpkafka\Sasl\PlainSasl::class` | ''     |
| username | 賬號                                                                | ''     |
| password | 密碼                                                                | ''     |

### SSL 配置説明

| 參數名          | 説明                                                                    | 默認值  |
| --------------- | ----------------------------------------------------------------------- | ------- |
| open            | 是否開啓 SSL 傳輸加密                                                     | `false` |
| compression     | 是否開啓壓縮                                                            | `true`  |
| certFile        | cert 證書存放路徑                                                        | `''`    |
| keyFile         | 私鑰存放路徑                                                            | `''`    |
| passphrase      | cert 證書密碼                                                            | `''`    |
| peerName        | 服務器主機名。默認為鏈接的 host                                          | `''`    |
| verifyPeer      | 是否校驗遠端證書                                                        | `false` |
| verifyPeerName  | 是否校驗遠端服務器名稱                                                  | `false` |
| verifyDepth     | 如果證書鏈條層次太深，超過了本選項的設定值，則終止驗證。 默認不校驗層級 | `0`     |
| allowSelfSigned | 是否允許自簽證書                                                        | `false` |
| cafile          | CA 證書路徑                                                              | `''`    |
| capath          | CA 證書目錄。會自動掃描該路徑下所有 pem 文件                               | `''`    |



