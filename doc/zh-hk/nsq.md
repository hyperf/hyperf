# Nsq

Nsq 是一個開源、輕量級、高性能的分佈式消息中間件, 使用 go 語言實現

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

### 創建消費者

```
$ php bin/hyperf.php gen:nsq-consumer DemoConsumer
```

使用 `\Hyperf\Nsq\Annotation\Consumer` 註解可以是設置 `topic / channel / name / nums`, 使用 `$pool` 屬性可以切換不同連接

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

### 投遞消息

使用 `\Hyperf\Nsq\Nsq::publish()` 投遞消息, 同樣可以使用 `$pool` 屬性來切換不同連接

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
        $nsq = make(Nsq::class); // 可以設置 `$pool` 屬性
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

participant "客户端" as client
participant "服務器" as server #orange

activate client
activate server

note right of server: 建立連接
client -> server: socket->connect(ip, port)

...
note right of server: 多次通信 send/recv
client -> server: socket->send()
server-> client: socket->recv()
...

note right of server: 關閉連接
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

participant "客户端" as client
participant "服務器" as server #orange

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
note left of client: 發送一條消息
client -> server: PUB <topic_name>
note left of client: 發送多條消息
client -> server: MPUB
note left of client: 發送一條延時消息
client -> server: DPUB
...

== sub ==
note left of client: client 使用 channel 訂閲 topic
note right of server: SUB 成功後, client 出於 RDY 0 階段
client -> server: SUB <topic_name> <channel_name>
note left of client: 使用 RDY 告訴 server 準備好消費 <count> 條消息
client -> server: RDY <count>
note right of server: server 返回 client <count> 條消息
server -> client: <count> msg
note left of client: 標記消息完成消費(消費成功)
client -> server: FIN <message_id>
note left of client: 消息重新入隊(消費失敗, 重新入隊)
client -> server: REQ <message_id> <timeout>
note left of client: 重置消息超時時間
client -> server: TOUCH <message_id>
...

== heartbeat ==
server -> client: _heartbeat_
note right of server: client 2 次沒有應答 NOP, server 將斷開連接
client -> server: NOP
...

== close ==
note left of client: clean close connection, 表示沒有消息了, 關閉連接
client -> server: CLS
note right of server: server 端成功應答
server -> client: CLOSE_WAIT

deactivate client
deactivate server

@enduml
```