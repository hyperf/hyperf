# Nsq

Nsq 是一个开源、轻量级、高性能的分布式消息中间件, 使用 go 语言实现

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

### 创建消费者

```
$ php bin/hyperf.php gen:nsq-consumer DemoConsumer
```

使用 `\Hyperf\Nsq\Annotation\Consumer` 注解可以是设置 `topic / channel / name / nums`, 使用 `$pool` 属性可以切换不同连接

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

### 禁止消费进程自启

默认情况下，使用了 `@Consumer` 注解后，框架会自动创建子进程启动消费者，并且会在子进程异常退出后，重新拉起。
如果出于开发阶段，进行消费者调试时，可能会因为消费其他消息而导致调试不便。

这种情况，只需要在对应的消费者中重写类方法 `isEnable()` 返回 `false` 即可。
也可在配置文件 `nsq.php` 中，将 `enable` 选项设置为 `false`。

```php
<?php

declare(strict_types=1);

namespace App\Nsq\Consumer;

use Hyperf\Nsq\AbstractConsumer;
use Hyperf\Nsq\Annotation\Consumer;
use Hyperf\Nsq\Message;
use Hyperf\Nsq\Result;
use Psr\Container\ContainerInterface;

/**
 * @Consumer(
 *     topic="demo_topic", 
 *     channel="demo_channel", 
 *     name ="DemoConsumer", 
 *     nums=1
 * )
 */
class DemoConsumer extends AbstractConsumer
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    public function isEnable(): bool 
    {
        return false;
    }

    public function consume(Message $payload): string
    {
        $body = json_decode($payload->getBody(), true);
        var_dump($body);
        return Result::ACK;
    }
}
```

### 投递消息

使用 `\Hyperf\Nsq\Nsq::publish()` 投递消息, 同样可以使用 `$pool` 属性来切换不同连接

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
        $nsq = make(Nsq::class); // 可以设置 `$pool` 属性
        $nsq->publish('hyperf', 'test'. time());

        $this->line('nsq pub success', 'info');
    }
}
```

## Nsq 协议
> https://nsq.io/clients/tcp_protocol_spec.html

- socket 基础

```plantuml
@startuml

autonumber
hide footbox
title **socket 基础**

participant "客户端" as client
participant "服务器" as server #orange

activate client
activate server

note right of server: 建立连接
client -> server: socket->connect(ip, port)

...
note right of server: 多次通信 send/recv
client -> server: socket->send()
server-> client: socket->recv()
...

note right of server: 关闭连接
client->server: socket->close()

deactivate client
deactivate server

@enduml
```

- Nsq 协议流程

```plantuml
@startuml

autonumber
hide footbox
title **Nsq 协议**

participant "客户端" as client
participant "服务器" as server #orange

activate client
activate server

== connect ==
note left of client: connect 后都为 socket->send/recv
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
note left of client: 发送一条消息
client -> server: PUB <topic_name>
note left of client: 发送多条消息
client -> server: MPUB
note left of client: 发送一条延时消息
client -> server: DPUB
...

== sub ==
note left of client: client 使用 channel 订阅 topic
note right of server: SUB 成功后, client 出于 RDY 0 阶段
client -> server: SUB <topic_name> <channel_name>
note left of client: 使用 RDY 告诉 server 准备好消费 <count> 条消息
client -> server: RDY <count>
note right of server: server 返回 client <count> 条消息
server -> client: <count> msg
note left of client: 标记消息完成消费(消费成功)
client -> server: FIN <message_id>
note left of client: 消息重新入队(消费失败, 重新入队)
client -> server: REQ <message_id> <timeout>
note left of client: 重置消息超时时间
client -> server: TOUCH <message_id>
...

== heartbeat ==
server -> client: _heartbeat_
note right of server: client 2 次没有应答 NOP, server 将断开连接
client -> server: NOP
...

== close ==
note left of client: clean close connection, 表示没有消息了, 关闭连接
client -> server: CLS
note right of server: server 端成功应答
server -> client: CLOSE_WAIT

deactivate client
deactivate server

@enduml
```