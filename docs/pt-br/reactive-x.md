# Integração com ReactiveX

O componente [hyperf/reactive-x](https://github.com/hyperf/reactive-x) fornece integração com ReactiveX no ambiente Swoole/Hyperf.

## História do ReactiveX

ReactiveX é a abreviação de Reactive Extensions, geralmente abreviado como Rx. Originalmente, era uma extensão do LINQ. Foi desenvolvido por uma equipe liderada pelo arquiteto da Microsoft Erik Meijer e foi open source em novembro de 2012. Rx é um modelo de programação cujo objetivo é fornecer uma interface de programação consistente para ajudar desenvolvedores a lidar mais facilmente com streams de dados assíncronos. A biblioteca Rx suporta .NET, JavaScript e C++. Nos últimos anos, Rx se tornou cada vez mais popular e agora suporta quase todas as linguagens de programação populares. A maioria das bibliotecas Rx por linguagem é mantida pela organização ReactiveX; as mais populares são RxJava/RxJS/Rx.NET, e o site da comunidade é [reactivex.io](http://reactivex.io).

## O que é ReactiveX

A definição da Microsoft é que Rx é uma biblioteca de funções que permite que desenvolvedores escrevam programas assíncronos e orientados a eventos usando sequências observáveis e operadores de consulta no estilo LINQ. Usando Rx, desenvolvedores podem usar Observables para representar streams de dados assíncronos, operadores LINQ para consultar streams assíncronos e Schedulers para parametrizar o processamento concorrente desses streams. Rx pode ser definido assim: Rx = Observables + LINQ + Schedulers.

A definição dada por [Reactivex.io](http://reactivex.io) é que Rx é uma interface de programação para programação assíncrona usando streams de dados observáveis. ReactiveX combina a essência do padrão observer, do padrão iterator e da programação funcional.

> The above two sections are taken from [RxDocs](https://github.com/mcxiaoke/RxDocs).

## Considere antes de usar

### Prós

- Pensar em programação reativa pode simplificar alguns problemas assíncronos complexos.

- Se você já tem experiência com programação reativa em outras linguagens â€‹â€‹(como RxJS/RxJava), este componente pode ajudar a trazer essa experiência para o Hyperf.

- Embora o Swoole recomende escrever programas assíncronos como programas síncronos via corrotinas, o Swoole ainda contém um grande número de eventos — e lidar com eventos é um ponto forte do Rx.

- O Rx também pode desempenhar um papel importante se o seu negócio incluir processamento de streams como WebSocket, streaming de gRPC etc.

### Contras

- A forma de pensar da programação reativa é bem diferente da forma tradicional orientada a objetos, o que exige adaptação por parte dos desenvolvedores.

- Rx apenas fornece uma forma de pensar, sem mágica adicional. Problemas que podem ser resolvidos com programação reativa também podem ser resolvidos por meios tradicionais.

- RxPHP não é o melhor da família Rx.

## Instalação

```bash
composer require hyperf/reactive-x
```

## Pacote

Vamos apresentar algumas encapsulações deste componente com exemplos e demonstrar os recursos poderosos do Rx. Todos os exemplos podem ser encontrados neste componente em `src/Example`.

### Observable::fromEvent

`Observable::fromEvent` converte eventos padrão PSR em sequências observáveis.

O event listener para imprimir statements SQL é fornecido por padrão no pacote skeleton hyperf-skeleton, e a localização padrão é `app/Listener/DbQueryExecutedListener.php`. Vamos fazer algumas otimizações nesse monitor:

1. Imprimir apenas queries SQL que demorem mais de 100ms.

2. Cada conexão pode imprimir no máximo 1 vez por segundo para evitar sobrecarregar o disco com um programa problemático.

Sem ReactiveX, o requisito 1 é tranquilo, mas o requisito 2 exigiria alguma criatividade. Com ReactiveX, esses requisitos podem ser resolvidos facilmente com o código de exemplo a seguir:

```php
<?php

declare(strict_types=1);

namespace Hyperf\ReactiveX\Example;

use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Hyperf\Logger\LoggerFactory;
use Hyperf\ReactiveX\Observable;
use Hyperf\Collection\Arr;
use Hyperf\Stringable\Str;
use Psr\Container\ContainerInterface;

class SqlListener implements ListenerInterface
{
    private $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(LoggerFactory::class)->get('sql');
    }

    public function listen(): array
    {
        return [
            BeforeWorkerStart::class,
        ];
    }

    public function process(object $event)
    {
        Observable::fromEvent(QueryExecuted::class)
            ->filter(
                function ($event) {
                    return $event->time > 100;
                }
            )
            ->groupBy(
                function ($event) {
                    return $event->connectionName;
                }
            )
            ->flatMap(
                function ($group) {
                    return $group->throttle(1000);
                }
            )
            ->map(
                function ($event) {
                    $sql = $event->sql;
                    if (! Arr::isAssoc($event->bindings)) {
                        foreach ($event->bindings as $key => $value) {
                            $sql = Str::replaceFirst('?', "'{$value}'", $sql);
                        }
                    }
                    return [$event->connectionName, $event->time, $sql];
                }
            )->subscribe(
                function ($message) {
                    $this->logger->info(sprintf('slow log: [%s] [%s] %s', ...$message));
                }
            );
    }
}
```

### Observable::fromChannel

Transforma o Channel de corrotina do Swoole em uma sequência observável.

O Channel na corrotina do Swoole é leitura/escrita um-para-um. E se quisermos fazer assinatura e publicação muitos-para-muitos via Channels com ReactiveX?

Veja o exemplo abaixo.

```php
<?php

declare(strict_types=1);

use Hyperf\ReactiveX\Observable;
use Swoole\Coroutine\Channel;

$chan = new Channel(1);
$pub = Observable::fromChannel($chan)->publish();

$pub->subscribe(function ($x) {
    echo 'First Subscription:' . $x . PHP_EOL;
});
$pub->subscribe(function ($x) {
    echo 'Second Subscription:' . $x . PHP_EOL;
});
$pub->connect();

$chan->push('hello');
$chan->push('world');

// First Subscription: hello
// Second Subscription: hello
// First Subscription: world
// Second Subscription: world
```

### Observable::fromCoroutine

Cria uma ou mais corrotinas e transforma os resultados de execução em uma sequência observável.

Agora faremos duas funções competirem em corrotinas concorrentes, e quem terminar primeiro retorna o resultado. O efeito é similar ao `Promise.race` em JavaScript.

```php
<?php

declare(strict_types=1);

use Hyperf\ReactiveX\Observable;
use Swoole\Coroutine\Channel;

$result = new Channel(1);
$o = Observable::fromCoroutine([function () {
    sleep(2);
    return 1;
}, function () {
    sleep(1);
    return 2;
}]);
$o->take(1)->subscribe(
    function ($x) use ($result) {
        $result->push($x);
    }
);
echo $result->pop(); // 2;
```

### Observable::fromHttpRoute

Todas as requisições HTTP são, na prática, orientadas a eventos. Então o roteamento de requisições HTTP também pode ser assumido pelo ReactiveX.

> Como vamos adicionar uma rota, isso deve ser executado antes do Server iniciar, por exemplo no event listener `BootApplication`.

Suponha que temos uma rota de upload com muito tráfego, que precisa ser bufferizada em memória e enviada em lote após dez uploads.

```php
<?php

declare(strict_types=1);

namespace Hyperf\ReactiveX\Example;

use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\ReactiveX\Observable;
use Psr\Http\Message\RequestInterface;

class BatchSaveRoute implements ListenerInterface
{
    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    /**
     * @param QueryExecuted $event
     */
    public function process(object $event)
    {
        Observable::fromHttpRoute(['POST', 'PUT'], '/save')
            ->map(
                function (RequestInterface $request) {
                    return $request->getBody();
                }
            )
            ->bufferWithCount(10)
            ->subscribe(
                function (array $bodies) {
                    echo count($bodies); //10
                }
            );
    }
}
```

Depois de assumir a rota, se você precisar controlar a Response retornada, você pode adicionar um terceiro parâmetro ao fromHttpRoute, que é o mesmo da rota normal, por exemplo:

```php
$observable = Observable::fromHttpRoute('GET', '/hello-hyperf', 'App\Controller\IndexController::hello');
```

Nesse ponto, `Observable` atua como middleware. Depois de obter a sequência observável do objeto de request, ele continuará a repassar o objeto de request para o `Controller` real.

### IpcSubject

A comunicação entre processos do Swoole também é orientada a eventos. Este componente também fornece uma versão de Subject cross-process com base nos quatro [Subject](https://mcxiaoke.gitbooks.io/rxdocs/content/Subject.html) fornecidos pelo RxPHP, que pode ser usada para compartilhar informações entre processos.

Por exemplo, precisamos fazer uma sala de chat baseada em WebSocket, com os seguintes requisitos:

1. As mensagens do chat precisam ser compartilhadas entre `Worker processes`.

2. As últimas 5 mensagens devem ser exibidas quando o usuário entrar pela primeira vez.

Fazemos isso via uma versão cross-process de `ReplaySubject`.

```php
<?php

declare(strict_types=1);

namespace Hyperf\ReactiveX\Example;

use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Hyperf\ReactiveX\Contract\BroadcasterInterface;
use Hyperf\ReactiveX\IpcSubject;
use Rx\Subject\ReplaySubject;
use Swoole\Http\Request;
use Swoole\Server;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;

class WebSocketController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    private IpcSubject $subject;

    private $subscriber = [];

    public function __construct(BroadcasterInterface $broadcaster)
    {
        $relaySubject = make(ReplaySubject::class, ['bufferSize' => 5]);
        // The first parameter is the original RxPHP Subject object.
        // The second parameter is the broadcast mode, the default is the whole process broadcast
        // The third parameter is the channel ID, each channel can only receive messages from the same channel.
        $this->subject = new IpcSubject($relaySubject, $broadcaster, 1);
    }

    public function onMessage(WebSocketServer $server, Frame $frame): void
    {
        $this->subject->onNext($frame->data);
    }

    public function onClose(Server $server, int $fd, int $reactorId): void
    {
        $this->subscriber[$fd]->dispose();
    }

    public function onOpen(WebSocketServer $server, Request $request): void
    {
        $this->subscriber[$request->fd] = $this->subject->subscribe(function ($data) use ($server, $request) {
            $server->push($request->fd, $data);
        });
    }
}

```

Por conveniência, este componente usa `IpcSubject` para encapsular um “message bus” `MessageBusInterface`. Basta injetar `MessageBusInterface` para enviar e receber informações compartilhadas por todos os processos (incluindo processos personalizados). Funcionalidades como central de configuração podem ser implementadas facilmente com isso.

```php
<?php
$bus = make(Hyperf\ReactiveX\MessageBusInterface::class);
// whole process broadcast information
$bus->onNext('Hello Hyperf');
// subscription info
$bus->subscribe(function($message){
    echo $message;
});
```

> Como o ReactiveX precisa usar o event loop, observe que as APIs relacionadas ao ReactiveX devem ser chamadas após o Swoole Server iniciar.

## Referências

* [Documentação Rx em chinês](https://mcxiaoke.gitbooks.io/rxdocs/content/)
* [Documentação Rx em inglês](http://reactivex.io/)
* [RxPHP repository](https://github.com/ReactiveX/RxPHP)
