# Transação Distribuída

O [dtm-client](https://github.com/dtm-php/dtm-client) é um componente client de transação distribuída DTM desenvolvido e mantido pela equipe Hyperf. Ele pode realizar o gerenciamento de transações distribuídas com o DTM-Server. É estável e pode ser usado em ambiente de produção.
O [seata/seata-php](https://github.com/seata/seata-php) é um componente client PHP do Seata desenvolvido pela equipe Hyperf e contribuído para a comunidade open source do Seata. Ele pode realizar o gerenciamento de transações distribuídas com o Seata-Server, mas ainda está em iteração de desenvolvimento e ainda não foi usado em ambiente de produção. Esperamos que todos possam participar para acelerar sua incubação.


# Introdução ao DTM-Client

O [dtm/dtm-client](https://packagist.org/packages/dtm/dtm-client) é o client PHP do Gerenciador de Transações Distribuídas [DTM](https://github.com/dtm-labs/dtm). Ele suporta os padrões de transação distribuída TCC, Saga, XA e mensagem em duas fases. No protocolo de comunicação, ele suporta a comunicação com o DTM Server através do protocolo HTTP ou do protocolo gRPC. Além disso, o client pode ser executado com segurança em ambiente PHP-FPM e em ambiente de Coroutine do Swoole, e também facilita ainda mais o suporte para o framework [Hyperf](https://github.com/hyperf/hyperf).

# Sobre o DTM

O DTM é um gerenciador de transações distribuídas open source baseado na linguagem Go, que oferece a poderosa funcionalidade de combinar transações entre linguagens e engines de armazenamento. O DTM resolve com elegância problemas de transação distribuída, como idempotência de interface, compensação nula e suspensão de transação, e também fornece uma solução de transação distribuída fácil de usar, com alto desempenho e fácil de escalar horizontalmente.

## Vantagens

* Fácil de começar
  - Inicia o serviço com configuração zero e fornece uma interface HTTP muito simples e clara, o que reduz bastante a dificuldade de começar com transações distribuídas
* Multiplataforma de linguagens
  - Pode ser usado por empresas com múltiplas stacks de linguagens. É conveniente de usar em várias linguagens como Go, Python, PHP, NodeJs, Ruby, C# etc.
* Simples de usar
  - Os desenvolvedores não precisam mais se preocupar com suspensão de transação, compensação nula, idempotência de interface e outros problemas; a tecnologia de barreira da primeira sub-transação trata disso para você
* Fácil de implantar e expandir
  - Depende apenas de MySQL/Redis, fácil de implantar, fácil de colocar em cluster e fácil de escalar horizontalmente
* Suporte a múltiplos protocolos de transação distribuída
  - TCC, SAGA, XA, mensagem em dois estágios, uma solução única para vários problemas de transação distribuída

## Comparação

Em linguagens não Java, ainda não existe um gerenciador de transações distribuídas maduro além do DTM, então aqui está uma comparação entre o DTM e o Seata, o projeto open source mais maduro em Java:

|                                          Características                                          |                                                DTM                                                |                                              SEATA                                               |                                      Observação                                       |
|:------------------------------------------------------------------------------------------:|:-------------------------------------------------------------------------------------------------:|:------------------------------------------------------------------------------------------------:|:-------------------------------------------------------------------------------:|
|              [Suporte de linguagens](https://dtm.pub/other/opensource.html#lang)               |                     <span style="color:green">Go、C#、Java、Python、PHP...</span>                     |                            <span style="color:orange">Java、Go</span>                             |             O DTM é mais fácil de implementar o client em uma nova linguagem              |
|               [Engine de armazenamento](https://dtm.pub/other/opensource.html#store)                |               <span style="color:green">Suporta Database, Redis, Mongo, etc.</span>               |                            <span style="color:orange">Database</span>                            ||
|            [Tratamento de exceção](https://dtm.pub/other/opensource.html#exception)             |        <span style="color:green"> A barreira de sub-transação é tratada automaticamente </span>        |                           <span style="color:orange">Manualmente</span>                            | O DTM resolve suspensão de transação, compensação nula, idempotência de interface etc. |
|                     [SAGA](https://dtm.pub/other/opensource.html#saga)                     |                           <span style="color:green">Fácil de usar</span>                            |                     <span style="color:orange">Máquina de estados complexa</span>                      ||
|               [Mensagem em duas fases](https://dtm.pub/other/opensource.html#msg)               |                                <span style="color:green">✓</span>                                 |                                 <span style="color:red">✗</span>                                 |                Arquitetura de consistência eventual com mensagem mínima                |
|                      [TCC](https://dtm.pub/other/opensource.html#tcc)                      |                                <span style="color:green">✓</span>                                 |                                <span style="color:green">✓</span>                                ||
|                       [XA](https://dtm.pub/other/opensource.html#xa)                       |                                <span style="color:green">✓</span>                                 |                                <span style="color:green">✓</span>                                ||
|                       [AT](https://dtm.pub/other/opensource.html#at)                       |                     <span style="color:orange">É mais recomendado usar XA</span>                      |                                <span style="color:green">✓</span>                                |                  AT é semelhante ao XA, mas com rollback sujo                   |
| [Serviço único com múltiplas fontes de dados](https://dtm.pub/other/opensource.html#multidb) |                                <span style="color:green">✓</span>                                 |                                 <span style="color:red">✗</span>                                 ||
|           [Protocolo de comunicação](https://dtm.pub/other/opensource.html#protocol)           |                                             HTTP、gRPC                                             |                                            Dubbo etc.                                            |                      O DTM é mais amigável para cloud native                       |
|                   [Github Stargazers](https://dtm.pub/other/opensource.html#star)                    | <img src="https://img.shields.io/github/stars/dtm-labs/dtm.svg?style=social" alt="github stars"/> | <img src="https://img.shields.io/github/stars/seata/seata.svg?style=social" alt="github stars"/> |          O DTM lançou a versão 0.1 em 2021-06-04, se desenvolvendo rapidamente           |

Pelas características da comparação acima, o DTM tem grandes vantagens em muitos aspectos. Se você considerar o suporte a múltiplas linguagens e o suporte a múltiplas engines de armazenamento, então o DTM é, sem dúvida, sua primeira escolha.

# Instalação

É muito conveniente instalar o dtm-client via Composer

```bash
composer require dtm/dtm-client
```

* Não se esqueça de iniciar o DTM Server antes de usá-lo

# Configuração

## Arquivo de configuração

Se você estiver usando o framework Hyperf, após instalar o componente, você pode publicar um arquivo de configuração em `./config/autoload/dtm.php` com o seguinte comando `vendor:publish`

```bash
php bin/hyperf.php vendor:publish dtm/dtm-client
```

Se você estiver usando um framework que não seja Hyperf, copie o arquivo `./vendor/dtm/dtm-client/publish/dtm.php` para o diretório de configuração correspondente.

```php
use DtmClient\Constants\Protocol;
use DtmClient\Constants\DbType;

return [
    // The communication protocol between the client and the DTM Server, supports Protocol::HTTP and Protocol::GRPC
    'protocol' => Protocol::HTTP,
    // DTM Server address
    'server' => '127.0.0.1',
    // DTM Server port
    'port' => [
        'http' => 36789,
        'grpc' => 36790,
    ],
    // Sub-transaction barrier
    'barrier' => [
        // Subtransaction barrier configuration in DB mode 
        'db' => [
            'type' => DbType::MySQL
        ],
        // Subtransaction barrier configuration in Redis mode
        'redis' => [
            // Timeout for subtransaction barrier records
            'expire_seconds' => 7 * 86400,
        ],
        // Classes that apply sub-transaction barriers in non-Hyperf frameworks or without annotation usage
        'apply' => [],
    ],
    // Options of Guzzle client under HTTP protocol
    'guzzle' => [
        'options' => [],
    ],
];
```

## Configurar middleware

Antes de usá-lo, você precisa configurar o middleware `DtmClient\Middleware\DtmMiddleware` como middleware global do server. Este middleware suporta a especificação PSR-15 e é aplicável a todos os frameworks que suportam essa especificação.
Para a configuração de middleware no Hyperf, consulte o capítulo [Documentação do Hyperf - Middleware](https://www.hyperf.wiki/2.2/#/zh-cn/middleware/middleware).

# Uso

O uso do dtm-client é muito simples; fornecemos um projeto de exemplo [dtm-php/dtm-sample](https://github.com/dtm-php/dtm-sample) para ajudá-lo a entender e depurar melhor.
Antes de usar este componente, também é altamente recomendado que você leia a [documentação oficial do DTM](https://dtm.pub/) para um entendimento mais detalhado.

## Padrão TCC

O padrão TCC é uma solução de transação distribuída flexível muito popular. O conceito de TCC é composto pelo acrônimo das três palavras Try-Confirm-Cancel. Foi publicado pela primeira vez em um artigo chamado [Life beyond Distributed Transactions: an Apostate's Opinion](https://www.ics.uci.edu/~cs223/papers/cidr07p15.pdf), por Pat Helland, em 2007.

### Três estágios do TCC

Estágio Try: tenta executar, completa todas as verificações de negócio (consistência), reserva os recursos de negócio necessários (pré-isolamento)
Estágio Confirm: Se todos os branches do Try tiverem sucesso, vai para o estágio Confirm. O Confirm executa de fato o negócio sem qualquer verificação de negócio, e usa apenas os recursos de negócio reservados no estágio Try
Estágio Cancel: Se um dos Try de todos os branches falhar, vai para o estágio Cancel. Libera os recursos de negócio reservados no estágio Try.

Se quisermos realizar um negócio semelhante a uma transferência interbancária, a transferência de saída (TransOut) e a transferência de entrada (TransIn) estão em microsserviços diferentes, e um diagrama de sequência típico de uma transação TCC concluída com sucesso é o seguinte:

<img src="https://en.dtm.pub/assets/tcc_normal.85ceb661.jpg" height=600 />

### Exemplo

O exemplo a seguir mostra como usá-lo no framework Hyperf; outros frameworks são semelhantes

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
                // Create call data for subtransaction A
                $tcc->callBranch(
                    // Arguments for calling the Try method
                    ['amount' => 30],
                    // URL of Try stage
                    $this->serviceUri . '/tcc/transA/try',
                    // URL of Confirm stage
                    $this->serviceUri . '/tcc/transA/confirm',
                    // URL of Cancel stage
                    $this->serviceUri . '/tcc/transA/cancel'
                );
                // Create call data for subtransaction B, and so on
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
        // Get the global transaction ID through TransContext::getGid() and return it to the client
        return TransContext::getGid();
    }
}
```

## Padrão Saga

O padrão Saga é uma das soluções mais conhecidas no campo de transações distribuídas, e também é muito popular em sistemas de grande porte. Ele apareceu pela primeira vez no artigo [SAGAS](https://www.cs.cornell.edu/andru/cs711/2002fa/reading/sagas.pdf), publicado por Hector Garcaa-Molrna & Kenneth Salem, em 1987.

Saga é uma transação de consistência eventual, também é uma transação flexível, também conhecida como transação de longa duração (long-running transaction). Saga é composta por uma série de transações locais. Depois que cada transação local atualiza o banco de dados, ela publica uma mensagem ou um evento para disparar a execução da próxima transação local na transação global Saga. Se uma transação local falhar porque algumas regras de negócio não podem ser satisfeitas, o Saga executa ações de compensação para todas as transações que foram comitadas com sucesso antes da transação que falhou. Portanto, quando o padrão Saga é comparado com o padrão TCC, geralmente se torna mais complicado implementar a lógica de rollback devido à falta de etapas de reserva de recursos.

### Divisão de sub-transações do Saga

Por exemplo, queremos realizar um negócio semelhante a uma transferência interbancária, transferindo 30 dólares da conta A para a conta B. De acordo com o princípio da transação Saga, dividiremos a transação global inteira nos seguintes serviços:
- Serviço de transferência de saída (TransOut), a conta A terá 30 dólares deduzidos
- Serviço de compensação de transferência de saída (TransOutCompensate), faz o rollback da operação de transferência de saída acima, ou seja, aumenta a conta A em 30 dólares
- Serviço de transferência de entrada (TransIn), a conta B terá 30 dólares aumentados
- Serviço de compensação de transferência de entrada (TransInCompensate), faz o rollback da operação de transferência de entrada acima, ou seja, a conta B é reduzida em 30 dólares

A lógica de toda a transação é:

Executa a transferência de saída com sucesso => Executa a transferência de entrada com sucesso => a transação global é concluída

Se ocorrer um erro no meio do caminho, como um erro ao transferir para a conta B, a operação de compensação do branch executado será chamada, ou seja:

Executa a transferência de saída com sucesso => executa a transferência de entrada com falha => executa a compensação de transferência de entrada com sucesso => executa a compensação de transferência de saída com sucesso => rollback da transação global concluído

A seguir está um diagrama de sequência típico de uma transação SAGA concluída com sucesso:

<img src="https://en.dtm.pub/assets/saga_normal.59a75c01.jpg" height=428 />

### Exemplo

O exemplo a seguir mostra como usá-lo no framework Hyperf; outros frameworks são semelhantes

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
        // Init Saga global transaction
        $this->saga->init();
        // Add TransOut sub-transaction
        $this->saga->add(
            $this->serviceUri . '/saga/transOut', 
            $this->serviceUri . '/saga/transOutCompensate', 
            $payload
        );
        // Add TransIn sub-transaction
        $this->saga->add(
            $this->serviceUri . '/saga/transIn', 
            $this->serviceUri . '/saga/transInCompensate', 
            $payload
        );
        // Submit Saga global transaction
        $this->saga->submit();
        // Get the global transaction ID through TransContext::getGid() and return it to the client
        return TransContext::getGid();
    }
}
```
## Padrão XA
XA é uma especificação para transações distribuídas proposta pela organização X/Open. O modelo de Processamento de Transações Distribuídas (DTP) do X/Open prevê três componentes de software:

Um programa de aplicação (AP) define os limites de transação e especifica as ações que compõem uma transação.

Gerenciadores de recursos (RMs, como bancos de dados ou sistemas de acesso a arquivos) fornecem acesso a recursos compartilhados.

Um componente separado chamado gerenciador de transações (TM) atribui identificadores às transações, monitora seu progresso e é responsável pela conclusão da transação e pela recuperação em caso de falha.

A figura a seguir ilustra as interfaces definidas pelo modelo X/Open DTP.

<img src="https://en.dtm.pub/assets/xa-dtp.78622cb4.jpeg" />

XA é dividido em duas fases.

Fase 1 (prepare): Todos os RMs participantes se preparam para executar suas transações e bloqueiam os recursos necessários. Quando cada participante está pronto, ele reporta ao TM.

Fase 2 (commit/rollback): Quando o gerenciador de transações (TM) recebe a confirmação de que todos os participantes (RM) estão prontos, ele envia comandos de commit para todos os participantes. Caso contrário, ele envia comandos de rollback para todos os participantes.

Atualmente, quase todos os bancos de dados populares suportam transações XA, incluindo Mysql, Oracle, SqlServer e Postgres

<img src="https://en.dtm.pub/assets/xa_normal.ebc35054.jpg" height=600 />

### Código de exemplo

O exemplo a seguir é mostrado no framework Hyperf, semelhante aos outros

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
        // Open the Xa, the global thing
        $gid = $this->xa->generateGid();
        $this->xa->globalTransaction($gid, function () use ($payload) {
            // Call the subthings interface
            $respone = $this->xa->callBranch($this->serviceUri . '/xa/api/transIn', $payload);
            // Get subthings return structure in XA http mode
            /* @var ResponseInterface $respone */
            $respone->getBody()->getContents();
            // Call the subthings interface
            $payload = ['amount' => 10];
            $this->xa->callBranch($this->serviceUri . '/xa/api/transOut', $payload);
        });
        // Return the global transaction ID via TransContext:: getGid()
        return TransContext::getGid();
    }

    #[RequestMapping(methods: ["GET", "POST", "PUT"], path: 'api/transIn')]
    public function transIn(RequestInterface $request): array
    {
        $content = $request->post('amount');
        $amount = $content['amount'] ?? 50;
        // The transIn method under the simulated distributed system
        $this->xa->localTransaction(function (DBTransactionInterface $dbTransaction) use ($amount) {
            // Please use the DBTransactionInterface to handle the local Mysql things
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
        // The transOut method under the simulated distributed system
        $this->xa->localTransaction(function (DBTransactionInterface $dbTransaction) use ($amount) {
            // Please use the DBTransactionInterface to handle the local Mysql things
            $dbTransaction->xaExecute('UPDATE `order` set `amount` = `amount` - ? where id = 2', [$amount]);
        });

        return ['status' => 0, 'message' => 'ok'];
    }
}

```
O código acima primeiro registra uma transação global XA, e então chama duas sub-transações TransOut e TransIn. Depois que todas as sub-transações são executadas com sucesso, a transação global XA é comitada para o DTM. O DTM recebe o compromisso da transação global XA, então chama o commit XA de todas as sub-transações e, por fim, altera o status da transação global para bem-sucedida.
