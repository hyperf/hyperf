# AMQP

[hyperf/amqp](https://github.com/hyperf/amqp)

## Instalação

```bash
composer require hyperf/amqp
```

## Configuração Padrão

|   Configuração  |  Tipo  |  Valor padrão   |                        Observação                       |
|:----------------:|:------:|:----------------:|:---------------------------------------------------:|
|       host       | string |     localhost    |                         Host                        |
|       port       |  int   |       5672       |                     Número da porta                     |
|       user       | string |       guest      |                       Nome de usuário                      |
|     password     | string |       guest      |                       Senha                      |
|      vhost       | string |         /        |                         vhost                       |
| concurrent.limit |  int   |         0        |      Quantidade máxima consumida simultaneamente       |
|       pool       | object |                  |   Configuração do pool de conexões                     |
| pool.connections |  int   |         1        | Número de conexões mantidas dentro do processo |
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
            // Try to maintain twice value heartbeat as much as possible
            'read_write_timeout' => 3.0,
            'context' => null,
            'keepalive' => false,
            // Try to ensure that the consumption time of each message is less than the heartbeat time as much as possible
            'heartbeat' => 0,
            'close_on_destruct' => false,
        ],
    ],
    'pool2' => [
        ...
    ]
];
```

## Entregar Mensagem

Use o comando gerador para criar um producer.
```bash
php bin/hyperf.php gen:amqp-producer DemoProducer
```

Podemos modificar a annotation do Producer para substituir o exchange e a routingKey.
Payload são os dados que são finalmente entregues à message queue, então podemos reescrever facilmente o método _construct, apenas garantindo que o payload seja atribuído.

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

Obtenha a instância do Producer através do container, e você poderá entregar a mensagem. Não é razoável, nos exemplos a seguir, usar diretamente o Application Context para obter o Producer. Para o uso específico do container, veja o módulo di.

```php
<?php
use Hyperf\Amqp\Producer;
use App\Amqp\Producers\DemoProducer;
use Hyperf\Context\ApplicationContext;

$message = new DemoProducer(1);
$producer = ApplicationContext::getContainer()->get(Producer::class);
$result = $producer->produce($message);

```

## Consumir Mensagem

Use o comando gerador para criar um consumer.
```bash
php bin/hyperf.php gen:amqp-consumer DemoConsumer
```

Podemos modificar a annotation do Consumer para substituir exchange, routingKey e queue.
E $data são os metadados analisados.

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

O framework cria automaticamente o processo de acordo com as annotations do Consumer, e o processo será reiniciado novamente após uma saída inesperada.

### Definir consumo concorrente

Existem três parâmetros que afetam a taxa de consumo

- você pode modificar o `nums` na annotation `#[Consumer]` para abrir múltiplos consumers
- A classe base `ConsumerMessage` possui uma propriedade `$qos` que controla o número de mensagens obtidas do server de uma só vez ao sobrescrever `prefetch_size` ou `prefetch_count` em `$qos`
- `concurrent.limit` no arquivo de configuração, que controla o número máximo de Coroutines consumidoras

### Resultados do consumo

O framework determinará o comportamento de resposta da mensagem com base no resultado retornado pelo método `consume` no `Consumer`. Existem 4 resultados de resposta, sendo eles `\Hyperf\Amqp\Result::ACK`, `\Hyperf\Amqp\Result::NACK`, `\Hyperf\Amqp\Result::REQUEUE`, `\Hyperf\Amqp\Result::DROP`, e cada valor de retorno representa o seguinte comportamento:

| Retorno                       | Comportamento                                                                 |
|------------------------------|----------------------------------------------------------------------|
| \Hyperf\Amqp\Result::ACK     | Confirma que a mensagem foi consumida corretamente                                               |
| \Hyperf\Amqp\Result::NACK    | A mensagem não foi consumida corretamente; responde com o método `basic_nack`                     |
| \Hyperf\Amqp\Result::REQUEUE | A mensagem não foi consumida corretamente; responde com o método `basic_reject` e recoloca a mensagem na fila |
| \Hyperf\Amqp\Result::DROP    | A mensagem não foi consumida corretamente; responde com o método `basic_reject`                   |

### Personalizar o número de processos consumidores de acordo com o ambiente

Na annotation `#[Consumer]`, você pode definir o número de processos consumidores através do atributo `nums`. Se precisar definir diferentes números de processos consumidores de acordo com diferentes ambientes, você pode sobrescrever o método `getNums`. O exemplo é o seguinte:

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



## Fila com atraso (Delay queue)

A fila com atraso do AMQP não é ordenada de acordo com o tempo de atraso. Portanto, uma vez que você entregue uma tarefa com atraso de 10 segundos e depois entregue uma tarefa com atraso de 5 segundos para essa fila, ela definitivamente estará em primeiro lugar. Depois que a primeira tarefa de 10s for concluída, a segunda tarefa de 5s será consumida.
Portanto, você precisa configurar filas diferentes de acordo com o tempo. Se você quiser uma fila com atraso mais flexível, pode tentar usar a async queue (async-queue) em conjunto com o AMQP.

Além disso, o AMQP precisa baixar o [plugin de delay](https://github.com/rabbitmq/rabbitmq-delayed-message-exchange/releases) e ativá-lo para uso normal

```shell
wget https://github.com/rabbitmq/rabbitmq-delayed-message-exchange/releases/download/3.9.0/rabbitmq_delayed_message_exchange-3.9.0.ez
cp rabbitmq_delayed_message_exchange-3.9.0.ez /opt/rabbitmq/plugins/
rabbitmq-plugins enable rabbitmq_delayed_message_exchange
```

### Producer

Crie um `producer` usando o comando `gen:amqp-producer`. Aqui está um exemplo do tipo `direct`. Para outros tipos, como `fanout` e `topic`, basta alterar o `type` no producer e no consumer.

```bash
php bin/hyperf.php gen:amqp-producer DelayDirectProducer
```

No arquivo DelayDirectProducer, adicione `use ProducerDelayedMessageTrait;`; o exemplo é o seguinte:

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

No arquivo `DelayDirectConsumer`, adicione e importe `use ProducerDelayedMessageTrait, ConsumerDelayedMessageTrait;`; o exemplo é o seguinte:

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

### Mensagem de atraso de produção

> A seguir, uma demonstração de como usá-lo em Command. Consulte o uso real para o uso específico.

Crie um `DelayCommand` usando o comando `gen:command DelayCommand`. Como a seguir:

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
        //1.delayed + direct
        $message = new DelayDirectProducer('delay+direct produceTime:'.(microtime(true)));
        //2.delayed + fanout
        //$message = new DelayFanoutProducer('delay+fanout produceTime:'.(microtime(true)));
        //3.delayed + topic
        //$message = new DelayTopicProducer('delay+topic produceTime:' . (microtime(true)));
        $message->setDelayMs(5000);
        $producer = ApplicationContext::getContainer()->get(Producer::class);
        $producer->produce($message);
    }
}

```
Execute a linha de comando para produzir mensagens
```
php bin/hyperf.php demo:command
```


## Chamada de procedimento remoto RPC

Além dos cenários típicos de message queue, também podemos implementar chamadas de procedimento remoto RPC através do AMQP. Este componente também fornece suporte correspondente para essa implementação.

### Criar consumer

O consumer usado pelo RPC é basicamente igual à implementação do consumer em um cenário típico de message queue. A única diferença é que os dados precisam ser retornados ao producer através da chamada do método `reply`.

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

### Fazendo uma chamada RPC

Também é muito simples iniciar uma chamada de procedimento remoto RPC como um gerador (generator). Basta obter o objeto `Hyperf\Amqp\RpcClient` através do container de injeção de dependência e chamar o método `call` nele. O resultado retornado são os dados de resposta do consumer. Como a seguir:

```php
<?php
use Hyperf\Amqp\Message\DynamicRpcMessage;
use Hyperf\Amqp\RpcClient;
use Hyperf\Context\ApplicationContext;

$rpcClient = ApplicationContext::getContainer()->get(RpcClient::class);
//Set Exchange and RoutingKey consistent with Consumer on DynamicRpcMessage
$result = $rpcClient->call(new DynamicRpcMessage('hyperf', 'hyperf', ['message' => 'Hello Hyperf'])); 

// $result:
// array(1) {
//     ["message"]=>
//     string(18) "Reply:Hello Hyperf"
// }
```

### Abstraindo o RpcMessage

O processo de chamada RPC acima completa diretamente a definição de Exchange e RoutingKey através da classe `Hyperf\Amqp\Message\DynamicRpcMessage`, e transfere os dados da mensagem. No design de projetos de produção, podemos realizar uma camada de abstração sobre o RpcMessage para unificar a definição de Exchange e RoutingKey.

Podemos criar a classe RpcMessage correspondente, como `App\Amqp\FooRpcMessage`, da seguinte forma:

```php
<?php
use Hyperf\Amqp\Message\RpcMessage;

class FooRpcMessage extends RpcMessage
{

    protected $exchange = 'hyperf';

    protected $routingKey = 'hyperf';
    
    public function __construct($data)
    {
        //To pass data
        $this->payload = $data;
    }

}
```

Desta forma, quando fazemos uma chamada RPC, só precisamos passar diretamente a instância de `FooRpcMessage` para o método `call`, sem precisar definir Exchange e RoutingKey a cada chamada.
