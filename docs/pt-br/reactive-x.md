# ReactiveX integration

O componente [hyperf/reactive-x](https://github.com/hyperf/reactive-x) fornece integração com ReactiveX no ambiente Swoole/Hyperf.

## História do ReactiveX

ReactiveX é a abreviação de Reactive Extensions, geralmente abreviado como Rx. Originalmente era uma extensão do LINQ. Foi desenvolvido por uma equipe liderada pelo arquiteto da Microsoft Erik Meijer. Foi open source em novembro de 2012. Rx é um modelo de programação. O objetivo é fornecer uma interface de programação consistente para ajudar os desenvolvedores a lidar com streams de dados assíncronos com mais facilidade. A biblioteca Rx suporta .NET, JavaScript e C++. O Rx tem se tornado cada vez mais popular nos últimos anos, e hoje suporta praticamente todas as linguagens de programação populares. A maioria das bibliotecas da linguagem Rx é mantida pela organização ReactiveX; as mais populares são RxJava/RxJS/Rx.NET, e o site da comunidade é [reactivex.io](http://reactivex.io).

## O que é ReactiveX

A definição da Microsoft é que Rx é uma biblioteca de funções que permite aos desenvolvedores escrever programas assíncronos e baseados em eventos usando sequências observáveis e operadores de consulta no estilo LINQ. Usando o Rx, os desenvolvedores podem usar Observables para representar streams de dados assíncronos, e Operators LINQ para consultar streams de dados assíncronos, além de usar Schedulers para parametrizar o processamento concorrente de streams de dados assíncronos. O Rx pode ser definido da seguinte forma: Rx = Observables + LINQ + Schedulers.

A definição dada pelo [Reactivex.io](http://reactivex.io) é que Rx é uma interface de programação para programação assíncrona usando streams de dados observáveis. O ReactiveX combina a essência do observer pattern, do iterator pattern e da programação funcional.

> As duas seções acima foram extraídas de [RxDocs](https://github.com/mcxiaoke/RxDocs).

## Considere antes de usar

### A favor

- Ao pensar em programação reativa, alguns problemas assíncronos complexos podem ser simplificados.

- Se você já tem experiência com programação reativa em outras linguagens (como RxJS/RxJava), este componente pode ajudá-lo a portar essa experiência para o Hyperf.

- Embora o Swoole recomende escrever programas assíncronos como programas síncronos através de coroutines, o Swoole ainda contém um grande número de eventos, e lidar com eventos é o forte do Rx.

- O Rx também pode desempenhar um papel importante se o seu negócio incluir processamento de streams como WebSocket, gRPC streaming, etc.

### Contra

- A forma de pensar da programação reativa é bem diferente da forma tradicional de pensar orientada a objetos, o que exige adaptação dos desenvolvedores.

- O Rx apenas fornece a forma de pensar, sem mágica adicional. Problemas que podem ser resolvidos pela programação reativa também podem ser resolvidos por meios tradicionais.

- O RxPHP não é o melhor da família Rx.

## Instalação

```bash
composer require hyperf/reactive-x
```

## Pacote

Vamos apresentar alguns encapsulamentos deste componente com exemplos e demonstrar as poderosas capacidades do Rx. Todos os exemplos podem ser encontrados neste componente em `src/Example`.

### Observable::fromEvent

`Observable::fromEvent` converte eventos padrão PSR em sequências observáveis.

O listener de evento para impressão de instruções SQL é fornecido por padrão no pacote skeleton hyperf-skeleton, e a localização padrão é `app/Listener/DbQueryExecutedListener.php`. Vamos fazer algumas otimizações neste listener:

1. Imprimir apenas queries SQL que levam mais de 100ms.

2. Cada conexão pode imprimir no máximo 1 vez por segundo, para evitar que o disco seja sobrecarregado pelo programa com problema.

Sem o ReactiveX, o problema 1 seria fácil de resolver, mas o problema 2 exigiria algum esforço. Com o ReactiveX, esses requisitos podem ser facilmente resolvidos através do seguinte exemplo de código:

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

Transforma o Channel na coroutine do Swoole em uma sequência observável.

O Channel na coroutine do Swoole tem leitura e escrita um-para-um. E se quisermos fazer assinaturas e publicação muitos-para-muitos através de Channels sob o ReactiveX?

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

Cria uma ou mais coroutines e transforma os resultados de execução em uma sequência observável.

Vamos agora fazer duas funções competirem em coroutines concorrentes, e a que terminar de executar primeiro retorna o resultado. O efeito é semelhante ao `Promise.race` em JavaScript.

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

Todas as requisições HTTP são, na verdade, orientadas a eventos. Então o roteamento de requisições HTTP também pode ser assumido pelo ReactiveX.

> Como vamos adicionar uma rota, ela deve ser executada antes do Server iniciar, como no listener de evento `BootApplication`.

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

Depois de assumir a rota, se você precisar controlar a Response retornada, você pode adicionar um terceiro parâmetro ao fromHttpRoute, da mesma forma que uma rota normal, como

```php
$observable = Observable::fromHttpRoute('GET', '/hello-hyperf', 'App\Controller\IndexController::hello');
```

Nesse momento, o `Observable` age como um middleware. Depois de obter a sequência observável do objeto de requisição, ele continuará passando o objeto de requisição para o `Controller` real.

### IpcSubject

A comunicação entre processos do Swoole também é orientada a eventos. Este componente fornece adicionalmente a versão Subject correspondente entre processos, com base nos quatro [Subject](https://mcxiaoke.gitbooks.io/rxdocs/content/Subject.html) fornecidos pelo RxPHP, que podem ser usados para compartilhar informações entre processos.

Por exemplo, precisamos fazer uma sala de chat baseada em WebSocket, com os seguintes requisitos:

1. As mensagens da sala de chat precisam ser compartilhadas entre os `processos Worker`.

2. As últimas 5 mensagens são exibidas quando o usuário faz login pela primeira vez.

Fazemos isso através de uma versão entre processos do `ReplaySubject`.

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

Para facilitar, este componente usa o `IpcSubject` para encapsular um "message bus" `MessageBusInterface`. Basta injetar o `MessageBusInterface` para enviar e receber informações compartilhadas por todos os processos (incluindo processos customizados). Funcionalidades como configuration center podem ser facilmente implementadas através dele.

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

> Como o ReactiveX precisa usar o event loop, observe que a API relacionada ao ReactiveX deve ser chamada após o Swoole Server ser iniciado.

## Referências

* [Documentação Rx em Chinês](https://mcxiaoke.gitbooks.io/rxdocs/content/)
* [Documentação Rx em Inglês](http://reactivex.io/)
* [Repositório RxPHP](https://github.com/ReactiveX/RxPHP)
