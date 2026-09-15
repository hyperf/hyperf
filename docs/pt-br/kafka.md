# Kafka

`Kafka` é uma plataforma open source de processamento de streams desenvolvida pela `Apache Software Foundation`, escrita em `Scala` e `Java`. O objetivo deste projeto é fornecer uma plataforma unificada, de alto throughput e baixa latência para processar dados em tempo real. Sua camada de persistência é essencialmente uma "message queue de publish/subscribe em larga escala baseada em uma arquitetura de log de transações distribuídas"

O componente [longlang/phpkafka](https://github.com/swoole/phpkafka) é fornecido pela [Longzhiyan](http://longlang.org/) e suporta `PHP-FPM` e `Swoole`. Agradecemos à `Swoole Team` e à `ZenTao Team` por suas contribuições à comunidade.

## Instalação

```bash
composer require hyperf/kafka
```

## Requisitos de versão

- Kafka >= 1.0.0

## Uso

### Configuração

O arquivo de configuração do componente `kafka` está localizado, por padrão, em `config/autoload/kafka.php`. Se o arquivo não existir, você pode usar o comando `php bin/hyperf.php vendor:publish hyperf/kafka` para publicar o arquivo de configuração correspondente.

O arquivo de configuração padrão é o seguinte:

|         Configuração         |    Tipo    |            Padrão            |                                                                                                Descrição                                                                                                |
|:-----------------------------:| :--------: | :---------------------------: |:---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|
|        connect_timeout        | int｜float |              -1               |                                                          Tempo de timeout de conexão (unidade: segundos, suporta decimal); se for -1, não há limite                                                           |
|         send_timeout          | int｜float |              -1               |                                                             Tempo de timeout de envio (unidade: segundos, suporta decimal); se for -1, não há limite                                                              |
|         recv_timeout          | int｜float |              -1               |                                                           Tempo de timeout de recebimento (unidade: segundos, suporta decimal); se for -1, não há limite                                                           |
|           client_id           |   string   |             null              |                                                                                              Client ID do Kafka                                                                                              |
|      max_write_attempts       |    int     |               3               |                                                                                     Número máximo de tentativas de escrita                                                                                      |
|       bootstrap_servers       |   array    |       '127.0.0.1:9092'        |                                       Bootstrap servers; se este valor for configurado, se conectará automaticamente ao server e atualizará automaticamente os brokers                                        |
|             acks              |    int     |               0               | O producer pede ao leader que confirme o valor que foi recebido antes de a requisição de confirmação ser concluída. Valores permitidos: 0 significa nenhuma confirmação, 1 significa apenas leader, -1 significa ISR completo |
|          producer_id          |    int     |              -1               |                                                                                                Producer ID                                                                                                |
|        producer_epoch         |    int     |              -1               |                                                                                              Producer Epoch                                                                                               |
|    partition_leader_epoch     |    int     |              -1               |                                                                                          Partition Leader Epoch                                                                                           |
|           interval            | int｜float |               0               |                                        Quantos segundos aguardar para tentar novamente quando a mensagem não é recebida; o padrão é 0, sem atraso (unidade: segundos, decimal)                                        |
|        session_timeout        | int｜float |              60               |                                Se nenhum sinal de heartbeat for recebido após o timeout, o coordenador considerará o usuário como inativo. (Unidade: segundos, decimais são suportados)                                 |
|       rebalance_timeout       | int｜float |              60               |                                   O tempo máximo que o coordenador aguarda para cada membro se reincorporar ao grupo durante o rebalanceamento (unidade: segundos, decimais são suportados).                                    |
|          replica_id           |    int     |              -1               |                                                                                                Replica ID                                                                                                 |
|            rack_id            |    int     |              -1               |                                                                                                Número do Rack                                                                                                |
|          group_retry          |    int     |               5               |                                                          Operação de agrupamento; número de retentativas automáticas ao corresponder ao código de erro predefinido                                                          |
|       group_retry_sleep       |    int     |               1               |                                                                                 Atraso de retentativa da operação de grupo, unidade: segundo                                                                                 |
|        group_heartbeat        |    int     |               3               |                                                                                  Intervalo de heartbeat do grupo, unidade: segundo                                                                                   |
|         offset_retry          |    int     |               5               |                                                           Operação de offset; número de retentativas automáticas ao corresponder ao código de erro predefinido                                                           |
|       auto_create_topic       |    bool    |             true              |                                                                                   Se deve criar automaticamente o topic                                                                                   |
| partition_assignment_strategy |   string   | KafkaStrategy::RANGE_ASSIGNOR |                     Estratégia de alocação de partições do consumer; opcional: alocação por intervalo (`KafkaStrategy::RANGE_ASSIGNOR`), alocação por rodízio (`KafkaStrategy::ROUND_ROBIN_ASSIGNOR`))                      |

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

### Criando um consumer

O comando `gen:kafka-consumer` pode gerar rapidamente um consumer (Consumer) para consumir a mensagem.

```bash
php bin/hyperf.php gen:kafka-consumer KafkaConsumer
```

Você também pode usar a annotation `Hyperf\Kafka\Annotation\Consumer` para declarar uma subclasse da classe abstrata `Hyperf/Kafka/AbstractConsumer` para completar a definição de um `Consumer`, onde tanto a annotation `Hyperf\Kafka\Annotation\Consumer` quanto a classe abstrata contêm as seguintes propriedades:

| Configuração |        Tipo        |    Padrão    |                                           Descrição                                            |
| :-----------: | :----------------: | :-----------: | :----------------------------------------------------------------------------------------------: |
|     topic     | string ou string[] |      ''       |                                         topic a ser monitorado                                         |
|    groupId    |       string       |      ''       |                                     groupId a ser monitorado                                      |
|   memberId    |       string       |      ''       |                                     memberId a ser monitorado                                     |
|  autoCommit   |       string       |      ''       |                                 Se deve fazer commit automaticamente                                  |
|     name      |       string       | KafkaConsumer |                                         Nome do Consumer                                          |
|     nums      |        int         |       1       |                                   Número de processos consumidores                                   |
|     pool      |       string       |    default    | A conexão correspondente ao consumer, correspondente à key do arquivo de configuração |

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

### Produzindo uma mensagem

Você pode chamar `Hyperf\Kafka\Producer::send(string $topic, ?string $value, ?string $key = null, array $headers = [], ?int $partitionIndex = null)` para entregar mensagens; a seguir, um exemplo de entrega de mensagem em um `Controller`:

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

O método `Hyperf\Kafka\Producer::send()` aguardará o ACK. Se você não precisar aguardar o ACK, você pode usar o método `Hyperf\Kafka\Producer::sendAsync()` para entregar a mensagem.

### Enviar múltiplas mensagens de uma vez

O método `Hyperf\Kafka\Producer::sendBatch(array $messages)` é usado para entregar mensagens em lote ao `kafka`; a seguir, um exemplo de entrega de mensagem em um `Controller`:

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
