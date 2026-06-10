# Kafka

`Kafka` é uma plataforma de processamento de streams open source desenvolvida pela `Apache Software Foundation`, escrita em `Scala` e `Java`. O objetivo deste projeto é fornecer uma plataforma unificada, com alto throughput e baixa latência, para processar dados em tempo real. Sua camada de persistência é essencialmente uma “fila de mensagens publish/subscribe em larga escala baseada na arquitetura de log de transações distribuídas”.

O componente [longlang/phpkafka](https://github.com/swoole/phpkafka) é fornecido por [Longzhiyan](http://longlang.org/) e suporta `PHP-FPM` e `Swoole`. Agradecemos ao `Swoole Team` e ao `ZenTao Team` por suas contribuições para a comunidade.

## Instalação

```bash
composer require hyperf/kafka
```

## Requisitos de versão

- Kafka >= 1.0.0

## Uso

### Configuração

Por padrão, o arquivo de configuração do componente `kafka` fica em `config/autoload/kafka.php`. Se o arquivo não existir, você pode usar o comando `php bin/hyperf.php vendor:publish hyperf/kafka` para publicar o arquivo de configuração correspondente.

O arquivo de configuração padrão é o seguinte:

|         Configuração         |    Tipo    |            Padrão            |                                                                                                Descrição                                                                                                |
|:-----------------------------:| :--------: | :---------------------------: |:---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|
|        connect_timeout        | intï½œfloat |              -1               |                                                          Tempo limite de conexão (unidade: segundo, suporta decimal); se for -1, não há limite                                                           |
|         send_timeout          | intï½œfloat |              -1               |                                                             Tempo limite de envio (unidade: segundo, suporta decimal); se for -1, não há limite                                                              |
|         recv_timeout          | intï½œfloat |              -1               |                                                           Tempo limite de recebimento (unidade: segundo, suporta decimal); se for -1, não há limite                                                           |
|           client_id           |   string   |             null              |                                                                                              Kafka Client ID                                                                                              |
|      max_write_attempts       |    int     |               3               |                                                                                     Número máximo de tentativas de escrita                                                                                      |
|       bootstrap_servers       |   array    |       '127.0.0.1:9092'        |                                       Servidores bootstrap; se este valor for configurado, ele se conectará automaticamente ao servidor e atualizará os brokers automaticamente                                        |
|             acks              |    int     |               0               | O produtor solicita ao líder a confirmação do valor recebido antes que a solicitação de confirmação seja concluída. Valores permitidos: 0 significa sem confirmação, 1 apenas líder, -1 ISR completo |
|          producer_id          |    int     |              -1               |                                                                                                Producer ID                                                                                                |
|        producer_epoch         |    int     |              -1               |                                                                                              Producer Epoch                                                                                               |
|    partition_leader_epoch     |    int     |              -1               |                                                                                          Partition Leader Epoch                                                                                           |
|           interval            | intï½œfloat |               0               |                                        Quantos segundos atrasar a tentativa novamente quando a mensagem não for recebida; o padrão é 0, sem atraso (unidade: segundo, decimal)                                        |
|        session_timeout        | intï½œfloat |              60               |                                Se nenhum sinal de heartbeat for recebido após o timeout, o coordenador considerará o usuário morto. (Unidade: segundos, suporta decimais)                                 |
|       rebalance_timeout       | intï½œfloat |              60               |                                   O tempo máximo que o coordenador espera para cada membro se juntar novamente ao rebalancear o grupo (unidade: segundos, suporta decimais).                                    |
|          replica_id           |    int     |              -1               |                                                                                                Replica ID                                                                                                 |
|            rack_id            |    int     |              -1               |                                                                                                Rack Number                                                                                                |
|          group_retry          |    int     |               5               |                                                          Operação de agrupamento, o número de tentativas automáticas ao corresponder ao código de erro predefinido                                                          |
|       group_retry_sleep       |    int     |               1               |                                                                                 Atraso de tentativa de operação de grupo, unidade: segundo                                                                                 |
|        group_heartbeat        |    int     |               3               |                                                                                  Intervalo de heartbeat do grupo, unidade: segundo                                                                                   |
|         offset_retry          |    int     |               5               |                                                           Operação de offset, o número de tentativas automáticas ao corresponder ao código de erro predefinido                                                           |
|       auto_create_topic       |    bool    |             true              |                                                                                   Se deve criar automaticamente o tópico                                                                                   |
| partition_assignment_strategy |   string   | KafkaStrategy::RANGE_ASSIGNOR |                     Estratégia de alocação de partição de consumidor, opcional: alocação por intervalo (`KafkaStrategy::RANGE_ASSIGNOR`) alocação por polling (`KafkaStrategy::ROUND_ROBIN_ASSIGNOR`))                      |

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

### Criando um consumidor

O comando `gen:kafka-consumer` pode gerar rapidamente um consumidor (Consumer) para consumir mensagens.

```bash
php bin/hyperf.php gen:kafka-consumer KafkaConsumer
```

Você também pode usar a anotação `Hyperf\Kafka\Annotation\Consumer` para declarar uma subclasse da classe abstrata `Hyperf/Kafka/AbstractConsumer` e concluir a definição de um `Consumer`. Tanto a anotação `Hyperf\Kafka\Annotation\Consumer` quanto a classe abstrata contêm os seguintes atributos:

| Configuração |        Tipo        |    Padrão    |                                           Descrição                                            |
| :-----------: | :----------------: | :-----------: | :----------------------------------------------------------------------------------------------: |
|     topic     | string or string[] |      ''       |                                         tópico a monitorar                                         |
|    groupId    |       string       |      ''       |                                     groupId a ser monitorado                                      |
|   memberId    |       string       |      ''       |                                     memberId a ser monitorado                                     |
|  autoCommit   |       string       |      ''       |                                 Se deve realizar o commit automaticamente                                  |
|     name      |       string       | KafkaConsumer |                                         Nome do consumidor                                          |
|     nums      |        int         |       1       |                                   Número de processos de consumidores                                   |
|     pool      |       string       |    default    | A conexão correspondente ao consumidor, correspondendo à chave do arquivo de configuração |

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

Você pode chamar `Hyperf\Kafka\Producer::send(string $topic, ?string $value, ?string $key = null, array $headers = [], ?int $partitionIndex = null)` para enviar mensagens. A seguir, um exemplo de envio de mensagens em um `Controller`:

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

O método `Hyperf\Kafka\Producer::send()` aguardará o ACK. Se você não precisar aguardar o ACK, pode usar o método `Hyperf\Kafka\Producer::sendAsync()` para enviar a mensagem.

### Enviar várias mensagens de uma vez

O método `Hyperf\Kafka\Producer::sendBatch(array $messages)` é usado para enviar mensagens em lote para o `kafka`. A seguir, um exemplo de envio de mensagens em um `Controller`:

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
