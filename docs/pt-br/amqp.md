# AMQP

[hyperf/amqp](https://github.com/hyperf/amqp)

## Instalação

```bash
composer require hyperf/amqp
```

## Configuração padrão

|   Configuração  |  Tipo  |  Valor padrão   |                        Observação                       |
|:----------------:|:------:|:----------------:|:---------------------------------------------------:|
|       host       | string |     localhost    |                         Host                        |
|       port       |  int   |       5672       |                     Número da porta                     |
|       user       | string |       guest      |                       Nome de usuário                      |
|     password     | string |       guest      |                       Senha                      |
|      vhost       | string |         /        |                         vhost                       |
| concurrent.limit |  int   |         0        |      Quantidade máxima consumida simultaneamente       |
|       pool       | object |                  |   Configuração do pool de conexões                     |
| pool.connections |  int   |         1        | Número de conexões mantidas no processo |
|      params      | object |                  |                   Configurações básicas              |

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
            // Tente manter o heartbeat com o dobro do valor sempre que possível
            'read_write_timeout' => 3.0,
            'context' => null,
            'keepalive' => false,
            // Tente garantir que o tempo de consumo de cada mensagem seja menor que o tempo de heartbeat sempre que possível
            'heartbeat' => 0,
            'close_on_destruct' => false,
        ],
    ],
    'pool2' => [
        ...
    ]
];
```

## Enviar mensagem

Use o comando de generator para criar um producer.
```bash
php bin/hyperf.php gen:amqp-producer DemoProducer
```

Podemos modificar a anotação Producer para substituir exchange e routingKey.
Payload é o dado que é finalmente enviado para a fila de mensagens, então podemos reescrever o método _construct facilmente — apenas garanta que o payload seja atribuído.

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

Obtenha a instância de Producer pelo container para enviar a mensagem. Não é recomendado, nos exemplos abaixo, usar diretamente o Application Context para obter o Producer. Para o uso específico do container, veja o módulo de DI.

```php
<?php
use Hyperf\Amqp\Producer;
use App\Amqp\Producers\DemoProducer;
use Hyperf\Context\ApplicationContext;

$message = new DemoProducer(1);
$producer = ApplicationContext::getContainer()->get(Producer::class);
$result = $producer->produce($message);

```

## Consumir mensagem

Use o comando de generator para criar um consumer.
```bash
php bin/hyperf.php gen:amqp-consumer DemoConsumer
```

Podemos modificar a anotação Consumer para substituir exchange, routingKey e queue.
E `$data` é o metadata parseado.

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

O framework cria automaticamente o processo conforme as anotações Consumer, e o processo será levantado novamente após uma saída inesperada.

### Definir consumo concorrente

Existem três parâmetros que afetam a taxa de consumo

- você pode modificar `nums` na anotação `#[Consumer]` para abrir múltiplos consumers
- a classe base `ConsumerMessage` possui o atributo `$qos` que controla quantas mensagens são puxadas do servidor por vez ao sobrescrever `prefetch_size` ou `prefetch_count` em `$qos`
- `concurrent.limit` no arquivo de configuração, que controla o número máximo de corrotinas de consumer

### Resultados de consumo

O framework determinará o comportamento de resposta da mensagem com base no resultado retornado pelo método `consume` no `Consumer`. Existem 4 resultados de resposta, a saber: `\Hyperf\Amqp\Result::ACK`, `\Hyperf\Amqp\ Result::NACK`, `\Hyperf\Amqp\Result::REQUEUE`, `\Hyperf\Amqp\Result::DROP`. Cada valor de retorno representa o seguinte comportamento:

| Retorno                       | Comportamento                                                                 |
|------------------------------|----------------------------------------------------------------------|
| \Hyperf\Amqp\Result::ACK     | Confirma que a mensagem foi consumida corretamente                                               |
| \Hyperf\Amqp\Result::NACK    | A mensagem não foi consumida corretamente, responde com o método `basic_nack`                     |
| \Hyperf\Amqp\Result::REQUEUE | A mensagem não foi consumida corretamente, responde com o método `basic_reject` e recoloca a mensagem na fila |
| \Hyperf\Amqp\Result::DROP    | A mensagem não foi consumida corretamente, responde com o método `basic_reject`                   |

### Personalizar a quantidade de processos de consumer conforme o ambiente

Na anotação `#[Consumer]`, você pode definir a quantidade de processos de consumer por meio do atributo `nums`. Se você precisar definir quantidades diferentes conforme ambientes diferentes, você pode sobrescrever o método `getNums`. Exemplo:

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



## Fila de atraso (Delay queue)

A fila de atraso do AMQP não é ordenada de acordo com o tempo de atraso. Portanto, se você enviar uma tarefa com atraso de 10 segundos e depois enviar uma tarefa com atraso de 5 segundos para essa fila, a de 10 segundos certamente ficará na frente. Após a tarefa de 10s ser concluída, a tarefa de 5s será consumida.
Portanto, você precisa configurar filas diferentes conforme o tempo. Se quiser uma fila de atraso mais flexível, você pode tentar usar a fila assíncrona (async-queue) em conjunto com AMQP.

Além disso, no AMQP é necessário baixar o [plug-in de delay](https://github.com/rabbitmq/rabbitmq-delayed-message-exchange/releases) e ativá-lo para uso normal

```shell
wget https://github.com/rabbitmq/rabbitmq-delayed-message-exchange/releases/download/3.9.0/rabbitmq_delayed_message_exchange-3.9.0.ez
cp rabbitmq_delayed_message_exchange-3.9.0.ez /opt/rabbitmq/plugins/
rabbitmq-plugins enable rabbitmq_delayed_message_exchange
```

### Producer

Crie um `producer` usando o comando `gen:amqp-producer`. Aqui está um exemplo do tipo `direct`. Para outros tipos como `fanout` e `topic`, basta alterar o `type` no producer e no consumer.

```bash
php bin/hyperf.php gen:amqp-producer DelayDirectProducer
```

No arquivo DelayDirectProducer, adicione `use ProducerDelayedMessageTrait;`. Exemplo:

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

Crie um `consumer` usando o comando `gen:amqp-consumer`.

```bash
php bin/hyperf.php gen:amqp-consumer DelayDirectConsumer
```

No arquivo `DelayDirectConsumer`, adicione e importe `use ProducerDelayedMessageTrait, ConsumerDelayedMessageTrait;`. Exemplo:

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

### Produzir mensagem com atraso

> A seguir está uma demonstração de como usar em um Command. Ajuste conforme o uso real.

Crie um `DelayCommand` usando o comando `gen:command DelayCommand`, como a seguir:

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
        //1.atrasado + direto
        $message = new DelayDirectProducer('delay+direct produceTime:'.(microtime(true)));
        //2.atrasado + fanout
        //$message = new DelayFanoutProducer('delay+fanout produceTime:'.(microtime(true)));
        //3.atrasado + topic
        //$message = new DelayTopicProducer('delay+topic produceTime:' . (microtime(true)));
        $message->setDelayMs(5000);
        $producer = ApplicationContext::getContainer()->get(Producer::class);
        $producer->produce($message);
    }
}

```
Execute no terminal para produzir mensagens
```
php bin/hyperf.php demo:command
```


## RPC (chamada de procedimento remoto)

Além dos cenários típicos de fila de mensagens, também podemos implementar chamadas de procedimento remoto (RPC) via AMQP. Este componente fornece suporte correspondente para essa implementação.

### Criar consumer

O consumer usado por RPC é basicamente o mesmo do cenário típico de fila de mensagens. A única diferença é que os dados precisam ser retornados ao producer chamando o método `reply`.

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

### Fazer uma chamada RPC

Também é bem simples iniciar uma chamada RPC via AMQP. Você só precisa obter o objeto `Hyperf\Amqp\RpcClient` pelo container de injeção de dependência e chamar o método `call`. O resultado retornado é o dado de resposta do consumer. Exemplo:

```php
<?php
use Hyperf\Amqp\Message\DynamicRpcMessage;
use Hyperf\Amqp\RpcClient;
use Hyperf\Context\ApplicationContext;

$rpcClient = ApplicationContext::getContainer()->get(RpcClient::class);
// Defina Exchange e RoutingKey consistentes com o Consumer no DynamicRpcMessage
$result = $rpcClient->call(new DynamicRpcMessage('hyperf', 'hyperf', ['message' => 'Hello Hyperf'])); 

// $result:
// array(1) {
//     ["message"]=>
//     string(18) "Reply:Hello Hyperf"
// }
```

### Abstrair RpcMessage

O processo de chamada RPC acima completa diretamente a definição de Exchange e RoutingKey por meio da classe `Hyperf\Amqp\Message\DynamicRpcMessage` e transmite os dados da mensagem. No desenho de projetos em produção, podemos aplicar uma camada de abstração sobre o RpcMessage para unificar o Exchange e a definição de RoutingKey.

Podemos criar uma classe RpcMessage correspondente, como `App\Amqp\FooRpcMessage`, assim:

```php
<?php
use Hyperf\Amqp\Message\RpcMessage;

class FooRpcMessage extends RpcMessage
{

    protected $exchange = 'hyperf';

    protected $routingKey = 'hyperf';
    
    public function __construct($data)
    {
        // Para passar dados
        $this->payload = $data;
    }

}
```

Dessa forma, ao fazer uma chamada RPC, basta passar diretamente a instância de `FooRpcMessage` para o método `call` sem precisar definir Exchange e RoutingKey a cada chamada.
