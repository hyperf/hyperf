# Nsq

Nsq 是一個開源、輕量級、高效能的分散式訊息中介軟體, 使用 go 語言實現

## 使用

### 配置

```
return [
    'default' => [
        'host' => '127.0.0.1',
        'port' => 4150,
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => 60.0,
        ],
    ],
];
```

### 建立消費者

```
$ php bin/hyperf.php gen:nsq-consumer DemoConsumer
```

使用 `\Hyperf\Nsq\Annotation\Consumer` 註解可以是設定 `topic / channel / name / nums`, 使用 `$pool` 屬性可以切換不同連線

```php
<?php

declare(strict_types=1);

namespace App\Nsq\Consumer;

use Hyperf\Nsq\AbstractConsumer;
use Hyperf\Nsq\Annotation\Consumer;
use Hyperf\Nsq\Message;
use Hyperf\Nsq\Result;

/**
 * @Consumer(
 *     topic="hyperf", 
 *     channel="hyperf", 
 *     name ="TestNsqConsumer", 
 *     nums=1
 *     )
 */
class TestNsqConsumer extends AbstractConsumer
{
    public function consume(Message $payload): string 
    {
        var_dump($payload->getBody());

        return Result::ACK;
    }
}
```

### 投遞訊息

使用 `\Hyperf\Nsq\Nsq::publish()` 投遞訊息, 同樣可以使用 `$pool` 屬性來切換不同連線

```php
<?php

declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\Nsq\Nsq;

/**
 * @Command
 */
class NsqCommand extends HyperfCommand
{
    protected $name = 'nsq:pub';

    public function handle()
    {
        /** @var Nsq $nsq */
        $nsq = make(Nsq::class); // 可以設定 `$pool` 屬性
        $nsq->publish('hyperf', 'test'. time());

        $this->line('nsq pub success', 'info');
    }
}
```

## Nsq 協議
> https://nsq.io/clients/tcp_protocol_spec.html

- socket 基礎

```plantuml
@startuml

autonumber
hide footbox
title **socket 基礎**

participant "客戶端" as client
participant "伺服器" as server #orange

activate client
activate server

note right of server: 建立連線
client -> server: socket->connect(ip, port)

...
note right of server: 多次通訊 send/recv
client -> server: socket->send()
server-> client: socket->recv()
...

note right of server: 關閉連線
client->server: socket->close()

deactivate client
deactivate server

@enduml
```

- Nsq 協議流程

```plantuml
@startuml

autonumber
hide footbox
title **Nsq 協議**

participant "客戶端" as client
participant "伺服器" as server #orange

activate client
activate server

== connect ==
note left of client: connect 後都為 socket->send/recv
client -> server: socket->connect(ip, host)
note left of client: protocol version
client->server: magic: V2

== auth ==
note left of client: client metadata
client->server: IDENTIFY
note right of server: 如果需要 auth
server->client: auth_required=true
client->server: AUTH
...

== pub ==
note left of client: 傳送一條訊息
client -> server: PUB <topic_name>
note left of client: 傳送多條訊息
client -> server: MPUB
note left of client: 傳送一條延時訊息
client -> server: DPUB
...

== sub ==
note left of client: client 使用 channel 訂閱 topic
note right of server: SUB 成功後, client 出於 RDY 0 階段
client -> server: SUB <topic_name> <channel_name>
note left of client: 使用 RDY 告訴 server 準備好消費 <count> 條訊息
client -> server: RDY <count>
note right of server: server 返回 client <count> 條訊息
server -> client: <count> msg
note left of client: 標記訊息完成消費(消費成功)
client -> server: FIN <message_id>
note left of client: 訊息重新入隊(消費失敗, 重新入隊)
client -> server: REQ <message_id> <timeout>
note left of client: 重置訊息超時時間
client -> server: TOUCH <message_id>
...

== heartbeat ==
server -> client: _heartbeat_
note right of server: client 2 次沒有應答 NOP, server 將斷開連線
client -> server: NOP
...

== close ==
note left of client: clean close connection, 表示沒有訊息了, 關閉連線
client -> server: CLS
note right of server: server 端成功應答
server -> client: CLOSE_WAIT

deactivate client
deactivate server

@enduml
```