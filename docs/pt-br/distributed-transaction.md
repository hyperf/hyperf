# Transação Distribuída

[dtm-client](https://github.com/dtm-php/dtm-client) é um componente cliente de transação distribuída DTM desenvolvido e mantido pela equipe Hyperf. Pode realizar gestão de transações distribuídas com o DTM-Server. Estável e pode ser usado em ambiente de produção.  
[seata/seata-php](https://github.com/seata/seata-php) é um componente cliente Seata PHP desenvolvido pela equipe Hyperf e contribuído para a comunidade open source Seata. Pode realizar transações distribuídas com gestão do Seata-Server, ainda está em iteração de desenvolvimento e ainda não foi usado em ambiente de produção. Esperamos que todos possam participar para acelerar a incubação.

# Introdução ao DTM-Client

[dtm/dtm-client](https://packagist.org/packages/dtm/dtm-client) é o cliente PHP do Distributed Transaction Manager [DTM](https://github.com/dtm-labs/dtm). Ele já suporta padrões de transação distribuída TCC, Saga, XA e padrão de mensagem em duas fases. No protocolo de comunicação, suporta comunicação com o DTM Server via protocolo HTTP ou protocolo gRPC. Além disso, o cliente pode rodar com segurança em PHP-FPM e ambiente de coroutine Swoole, e também tornou o suporte mais fácil para o framework [Hyperf](https://github.com/hyperf/hyperf).

# Sobre o DTM

DTM é um gerenciador de transações distribuídas open source baseado na linguagem Go, que fornece a poderosa função de combinar transações entre linguagens e motores de armazenamento. O DTM resolve elegantemente problemas de transações distribuídas como idempotência de interface, compensação nula e suspensão de transação, e também fornece soluções de transação distribuída que são fáceis de usar, de alto desempenho e fáceis de escalar horizontalmente.

## Vantagem

- Fácil de começar
  - Inicia o serviço com configuração zero e fornece uma interface HTTP muito simples e clara, o que reduz bastante a dificuldade de começar com transações distribuídas
- Cruzamento de linguagem de programação
  - Pode ser usado por empresas com múltiplas stacks de linguagem. É conveniente usar em várias linguagens como Go, Python, PHP, NodeJs, Ruby, C#, etc.
- Simples de usar
  - Desenvolvedores não precisam mais se preocupar com suspensão de transação, compensação nula, idempotência de interface e outros problemas, e a primeira tecnologia de barreira de subtransação cuida disso para você
- Fácil de implantar e expandir
  - Depende apenas de MySQL/Redis, fácil de implantar, fácil de clusterizar e fácil de escalar horizontalmente
- Suporte a múltiplos protocolos de transação distribuída
  - TCC, SAGA, XA, mensagem em duas fases, solução completa para vários problemas de transação distribuída

## Comparação

Em linguagens não Java, ainda não há um gerenciador de transações distribuídas maduro além do DTM, então aqui está uma comparação entre DTM e Seata, o projeto open source mais maduro em Java:

| Funcionalidades | DTM | SEATA | Memo |
|:---:|:---:|:---:|:---|
| [suporte a linguagens](https://dtm.pub/other/opensource.html#lang) | <span style="color:green">Go, C#, Java, Python, PHP...</span> | <span style="color:orange">Java, Go</span> | DTM é mais fácil de implementar o cliente para uma nova linguagem |
| [Motor de Armazenamento](https://dtm.pub/other/opensource.html#store) | <span style="color:green">Suporta Database, Redis, Mongo, etc.</span> | <span style="color:orange">Database</span> | |
| [Tratamento de Exceção](https://dtm.pub/other/opensource.html#exception) | <span style="color:green">Barreira de subtransação é tratada automaticamente</span> | <span style="color:orange">Manual</span> | DTM resolve suspensão de transação, compensação nula, idempotência de interface, etc. |
| [SAGA](https://dtm.pub/other/opensource.html#saga) | <span style="color:green">Fácil de usar</span> | <span style="color:orange">Máquina de estado complexa</span> | |
| [Mensagem em duas fases](https://dtm.pub/other/opensource.html#msg) | <span style="color:green">✓</span> | <span style="color:red">✗</span> | Arquitetura de Consistência Eventual de Mensagem Mínima |
| [TCC](https://dtm.pub/other/opensource.html#tcc) | <span style="color:green">✓</span> | <span style="color:green">✓</span> | |
| [XA](https://dtm.pub/other/opensource.html#xa) | <span style="color:green">✓</span> | <span style="color:green">✓</span> | |
| [AT](https://dtm.pub/other/opensource.html#at) | <span style="color:orange">XA é mais recomendado</span> | <span style="color:green">✓</span> | AT é semelhante ao XA, mas com rollback sujo |
| [Serviço único com múltiplas fontes de dados](https://dtm.pub/other/opensource.html#multidb) | <span style="color:green">✓</span> | <span style="color:red">✗</span> | |
| [Protocolo de comunicação](https://dtm.pub/other/opensource.html#protocol) | HTTP, gRPC | Dubbo, etc. | DTM é mais amigável para cloud native |
| [Github Stargazers](https://dtm.pub/other/opensource.html#star) | <img src="https://img.shields.io/github/stars/dtm-labs/dtm.svg?style=social" alt="github stars"/> | <img src="https://img.shields.io/github/stars/seata/seata.svg?style=social" alt="github stars"/> | DTM lançou versão 0.1 em 2021-06-04, desenvolvendo rapidamente |

A partir das características da comparação acima, o DTM tem grandes vantagens em muitos aspectos. Se você considera suporte multi-linguagem e suporte a múltiplos motores de armazenamento, então o DTM é sem dúvida sua primeira escolha.

# Instalação

É muito conveniente instalar dtm-client através do Composer

```bash
composer require dtm/dtm-client
```

* Não se esqueça de iniciar o DTM Server antes de usar

# Configuração

## Arquivo de configuração

Se estiver usando o framework Hyperf, após instalar o componente, você pode publicar um arquivo de configuração para `./config/autoload/dtm.php` com o seguinte comando `vendor:publish`

```bash
php bin/hyperf.php vendor:publish dtm/dtm-client
```

Se estiver usando um framework não-Hyperf, copie o arquivo `./vendor/dtm/dtm-client/publish/dtm.php` para o diretório de configuração correspondente.

```php
use DtmClient\Constants\Protocol;
use DtmClient\Constants\DbType;


return [
    // O protocolo de comunicação entre o cliente e o DTM Server, suporta Protocol::HTTP e Protocol::GRPC
    'protocol' => Protocol::HTTP,
    // Endereço do DTM Server
    'server' => '127.0.0.1',
    // Porta do DTM Server
    'port' => [
        'http' => 36789,
        'grpc' => 36790,
    ],
    // Barreira de subtransação
    'barrier' => [
        // Configuração de barreira de subtransação em modo DB 
        'db' => [
            'type' => DbType::MySQL
        ],
        // Configuração de barreira de subtransação em modo Redis
        'redis' => [
            // Tempo de expiração para registros de barreira de subtransação
            'expire_seconds' => 7 * 86400,
        ],
        // Classes que aplicam barreiras de subtransação em frameworks não-Hyperf ou sem uso de anotações
        'apply' => [],
    ],
    // Opções do cliente Guzzle sob protocolo HTTP
    'guzzle' => [
        'options' => [],
    ],
];
```

## Configurar middleware

Antes de usar, você precisa configurar o middleware `DtmClient\Middleware\DtmMiddleware` como middleware global do servidor. Este middleware suporta a especificação PSR-15 e é aplicável a todos os frameworks que suportam esta especificação.
Para configuração de middleware no Hyperf, consulte o capítulo [Documentação Hyperf - Middleware](https://www.hyperf.wiki/2.2/#/zh-cn/middleware/middleware).

# Uso

O uso do dtm-client é muito simples, fornecemos um projeto de exemplo [dtm-php/dtm-sample](https://github.com/dtm-php/dtm-sample) para ajudar você a entender e depurar melhor.
Antes de usar este componente, também é fortemente recomendado que você leia a [Documentação Oficial do DTM](https://dtm.pub/) para uma compreensão mais detalhada.

## Padrão TCC

O padrão TCC é uma solução de transação distribuída flexível muito popular. O conceito de TCC é composto pelas siglas de três palavras Try-Confirm-Cancel. Foi publicado pela primeira vez em um artigo chamado [Life beyond Distributed Transactions: an Apostate's Opinion](https://www.ics.uci.edu/~cs223/papers/cidr07p15.pdf) por Pat Helland em 2007.

### Três estágios do TCC

Fase Try: tenta executar, completa todas as verificações de negócios (consistência), reserva recursos de negócios necessários (pré-isolamento)
Estágio Confirm: Se todas as branches do Try forem bem-sucedidas, vá para o estágio Confirm. Confirm executa realmente o negócio sem nenhuma verificação de negócio e usa apenas os recursos de negócios reservados na fase Try
Estágio Cancel: Se uma das Try de todas as branches falhar, vá para o estágio Cancel. Libera os recursos de negócio reservados na fase Try.

Se quisermos realizar um negócio semelhante a transferência interbancária entre bancos, a transferência de saída (TransOut) e a transferência de entrada (TransIn) estão em microsserviços diferentes, e um diagrama de sequência típico de uma transação TCC concluída com sucesso é o seguinte:

![Diagrama TCC](https://en.dtm.pub/assets/tcc_normal.85ceb661.jpg)

### Exemplo

A seguir mostra como usar no framework Hyperf, outros frameworks são semelhantes

```php
<?php
namespace App\Controller;

use DtmClient\TCC;
use DtmClient\TransContext;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Throwable;

#[Controller(prefix: '/tcc')]
class TccController
{
    protected string $serviceUri = 'http://127.0.0.1:9501';

    #[Inject]
    protected TCC $tcc;

    #[GetMapping(path: 'successCase')]
    public function successCase()
    {
        try {
            
            $this->tcc->globalTransaction(function (TCC $tcc) {
                // Criar dados de chamada para subtransação A
                $tcc->callBranch(
                    // Argumentos para chamar o método Try
                    ['amount' => 30],
                    // URL do estágio Try
                    $this->serviceUri . '/tcc/transA/try',
                    // URL do estágio Confirm
                    $this->serviceUri . '/tcc/transA/confirm',
                    // URL do estágio Cancel
                    $this->serviceUri . '/tcc/transA/cancel'
                );
                // Criar dados de chamada para subtransação B, e assim por diante
                $tcc->callBranch(
                    ['amount' => 30],
                    $this->serviceUri . '/tcc/transB/try',
                    $this->serviceUri . '/tcc/transB/confirm',
                    $this->serviceUri . '/tcc/transB/cancel'
                );
            });
        } catch (Throwable $e) {
            var_dump($e->getMessage(), $e->getTraceAsString());
        }
        // Obter o ID da transação global através de TransContext::getGid() e retornar ao cliente
        return TransContext::getGid();
    }
}
```

## Padrão Saga

O padrão Saga é uma das soluções mais conhecidas no campo de transações distribuídas, e também é muito popular em grandes sistemas. Apareceu primeiro no artigo [SAGAS](https://www.cs.cornell.edu/andru/cs711/2002fa/reading/sagas.pdf) publicado por Hector Garcaa-Molrna & Kenneth Salem em 1987.

Saga é uma transação de consistência eventual, também uma transação flexível, também conhecida como transação de longa duração. Saga é composta por uma série de transações locais. Após cada transação local atualizar o banco de dados, ela publicará uma mensagem ou evento para acionar a execução da próxima transação local na transação global Saga. Se uma transação local falhar porque algumas regras de negócios não podem ser satisfeitas, Saga executa ações compensatórias para todas as transações que foram comprometidas com sucesso antes da transação falhada. Portanto, quando o padrão Saga é comparado com o padrão TCC, frequentemente torna-se mais problemático implementar a lógica de rollback devido à falta de etapas de reserva de recursos.

### Divisão de subtransação do Saga

Por exemplo, queremos realizar um negócio semelhante a transferência interbancária entre bancos, e transferir 30 dólares da conta A para a conta B. De acordo com o princípio da transação Saga, vamos dividir toda a transação global nos seguintes serviços:
- Serviço Transferência de saída (TransOut), a conta A irá deduzir 30 dólares
- Serviço Compensação de transferência de saída (TransOutCompensate), reverter a operação de transferência de saída acima, ou seja, aumentar a conta A em 30 dólares
- Serviço Transferência de entrada (TransIn), a conta B será aumentada em 30 dólares
- Serviço Compensação de transferência de entrada (TransInCompensate), reverter a operação de transferência de entrada acima, ou seja, a conta B é reduzida em 30 dólares

A lógica de toda a transação é:

Executar transferência de saída com sucesso => Executar transferência de entrada com sucesso => a transação global é concluída

Se ocorrer um erro no meio, como erro ao transferir para a conta B, a operação compensatória da branch executada será chamada, ou seja:

Executar transferência de saída com sucesso => executar transferência de entrada com falha => executar compensação de transferência de entrada com sucesso => executar compensação de transferência de saída com sucesso => rollback da transação global completado

A seguir é um diagrama de sequência típico de uma transação SAGA concluída com sucesso:

![Diagrama Saga](https://en.dtm.pub/assets/saga_normal.59a75c01.jpg)

### Exemplo

A seguir mostra como usar no framework Hyperf, outros frameworks são semelhantes

```php
namespace App\Controller;

use DtmClient\Saga;
use DtmClient\TransContext;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;

#[Controller(prefix: '/saga')]
class SagaController
{
    protected string $serviceUri = 'http://127.0.0.1:9501';
    
    #[Inject]
    protected Saga $saga;

    #[GetMapping(path: 'successCase')]
    public function successCase(): string
    {
        $payload = ['amount' => 50];
        // Inicializar transação global Saga
        $this->saga->init();
        // Adicionar subtransação TransOut
        $this->saga->add(
            $this->serviceUri . '/saga/transOut', 
            $this->serviceUri . '/saga/transOutCompensate', 
            $payload
        );
        // Adicionar subtransação TransIn
        $this->saga->add(
            $this->serviceUri . '/saga/transIn', 
            $this->serviceUri . '/saga/transInCompensate', 
            $payload
        );
        // Submeter transação global Saga
        $this->saga->submit();
        // Obter o ID da transação global através de TransContext::getGid() e retornar ao cliente
        return TransContext::getGid();
    }
}
```

## Padrão XA

XA é uma especificação para transações distribuídas proposta pela organização X/Open. O modelo X/Open Distributed Transaction Processing (DTP) prevê três componentes de software:

Um programa de aplicação (AP) define limites de transação e especifica ações que constituem uma transação.

Gerenciadores de recursos (RMs, como bancos de dados ou sistemas de acesso a arquivos) fornecem acesso a recursos compartilhados.

Um componente separado chamado gerenciador de transações (TM) atribui identificadores às transações, monitora seu progresso e assume responsabilidade pela conclusão da transação e para recuperação de falhas.

A figura seguinte ilustra as interfaces definidas pelo modelo X/Open DTP.

![Diagrama XA DTP](https://en.dtm.pub/assets/xa-dtp.78622cb4.jpeg)

XA é dividido em duas fases.

Fase 1 (prepare): Todos os RMs participantes preparam-se para executar suas transações e bloqueiam os recursos necessários. Quando cada participante estiver pronto, ele reporta ao TM.

Fase 2 (commit/rollback): Quando o gerenciador de transações (TM) recebe que todos os participantes (RM) estão prontos, ele envia comandos de commit para todos os participantes. Caso contrário, envia comandos de rollback para todos os participantes.

Atualmente, quase todos os bancos de dados populares suportam transações XA, incluindo Mysql, Oracle, SqlServer e Postgres

![Diagrama XA normal](https://en.dtm.pub/assets/xa_normal.ebc35054.jpg)

### Código de exemplo

A seguir é mostrado no framework Hyperf, semelhante a outros

```php
<?php

namespace App\Controller;

use App\Grpc\GrpcClient;
use DtmClient\DbTransaction\DBTransactionInterface;
use DtmClient\TransContext;
use DtmClient\XA;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;

#[Controller(prefix: '/xa')]
class XAController
{
    private GrpcClient $grpcClient;

    protected string $serviceUri = 'http://127.0.0.1:9502';

    public function __construct(
        private XA $xa,
        protected ConfigInterface $config,
    ) {
        $server = $this->config->get('dtm.server', '127.0.0.1');
        $port = $this->config->get('dtm.port.grpc', 36790);
        $hostname = $server . ':' . $port;
        $this->grpcClient = new GrpcClient($hostname);
    }

    #[GetMapping(path: 'successCase')]
    public function successCase(): string
    {
        $payload = ['amount' => 50];
        // Abrir o Xa, o global thing
        $gid = $this->xa->generateGid();
        $this->xa->globalTransaction($gid, function () use ($payload) {
            // Chamar a interface de subthings
            $respone = $this->xa->callBranch($this->serviceUri . '/xa/api/transIn', $payload);
            // Obter estrutura de retorno de subthings no modo HTTP XA
            /* @var ResponseInterface $respone */
            $respone->getBody()->getContents();
            // Chamar a interface de subthings
            $payload = ['amount' => 10];
            $this->xa->callBranch($this->serviceUri . '/xa/api/transOut', $payload);
        });
        // Retornar o ID da transação global via TransContext::getGid()
        return TransContext::getGid();
    }

    #[RequestMapping(methods: ["GET", "POST", "PUT"], path: 'api/transIn')]
    public function transIn(RequestInterface $request): array
    {
        $content = $request->post('amount');
        $amount = $content['amount'] ?? 50;
        // O método transIn sob o sistema distribuído simulado
        $this->xa->localTransaction(function (DBTransactionInterface $dbTransaction) use ($amount) {
            // Por favor use o DBTransactionInterface para lidar com os things locais do Mysql
            $dbTransaction->xaExecute('UPDATE `order` set `amount` = `amount` + ? where id = 1', [$amount]);
        });

        return ['status' => 0, 'message' => 'ok'];
    }

    /**
     * @param RequestInterface $request
     * @return array
     */
    #[RequestMapping(methods: ["GET", "POST", "PUT"], path: 'api/transOut')]
    public function transOut(RequestInterface $request): array
    {
        $content = $request->post('amount');
        $amount = $content['amount'] ?? 10;
        // O método transOut sob o sistema distribuído simulado
        $this->xa->localTransaction(function (DBTransactionInterface $dbTransaction) use ($amount) {
            // Por favor use o DBTransactionInterface para lidar com os things locais do Mysql
            $dbTransaction->xaExecute('UPDATE `order` set `amount` = `amount` - ? where id = 2', [$amount]);
        });

        return ['status' => 0, 'message' => 'ok'];
    }
}
```

O código acima registra primeiro uma transação XA global, e então chama duas subtransações TransOut e TransIn. Após todas as subtransações serem executadas com sucesso, a transação XA global é comprometida para o DTM. DTM recebe o comprometimento da transação XA global, então chama o commit XA de todas as subtransações, e finalmente muda o status da transação global para succeeded.