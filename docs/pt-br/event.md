# Eventos

## Prefácio

O modo de eventos precisa ser implementado com base no [PSR-14](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-14-event-dispatcher.md).
O gerenciador de eventos do Hyperf é implementado por padrão pelo [hyperf/event](https://github.com/hyperf/event). Este componente também pode ser usado em outros frameworks ou aplicações, bastando adicioná-lo ao Composer.

```bash
composer require hyperf/event
```

## Conceito

O padrão de eventos é um mecanismo bem testado e confiável. É um mecanismo muito adequado para desacoplamento. Há três papéis:

- `Event` é o objeto de comunicação passado entre o código da aplicação e o `Listener`.
- `Listener` é um listener para escutar a ocorrência de `Event>
- `Event Dispatcher` é o objeto gerente usado para disparar o `Event` e gerenciar o relacionamento entre `Listener` e `Event`.

Vamos explicar com um exemplo fácil de entender. Suponha que tenhamos um método `UserService::register()` para registrar uma conta. Depois que a conta for registrada com sucesso, podemos disparar o evento `UserRegistered` via event dispatcher, que é escutado pelo listener. Quando esse evento ocorre, ao executar algumas operações — como enviar uma mensagem de sucesso de registro — podemos querer fazer mais coisas após o usuário se registrar com sucesso, como enviar um e-mail. Então, podemos escutar o evento `UserRegistered` adicionando outro listener, sem adicionar código que não esteja relacionado ao método `UserService::register()`.

## Uso do gerenciador de eventos

### Definir um evento

Um evento é, na prática, uma classe normal para gerenciar dados de estado. Quando disparado, os dados da aplicação são passados para o evento. O listener então opera sobre a classe do evento. Um evento pode ser escutado por múltiplos listeners.

```php
<?php
namespace App\Event;

class UserRegistered
{
    // Recomenda-se definir isso como uma propriedade public para que o listener possa usá-la diretamente, ou você pode fornecer um Getter para essa propriedade.
    public $user;
    
    public function __construct($user)
    {
        $this->user = $user;    
    }
}
```

### Definir um listener

O listener precisa implementar o método exigido pela interface `Hyperf\Event\Contract\ListenerInterface`. O exemplo é o seguinte:

```php
<?php
namespace App\Listener;

use App\Event\UserRegistered;
use Hyperf\Event\Contract\ListenerInterface;

class UserRegisteredListener implements ListenerInterface
{
    public function listen(): array
    {
        // Retorna um array de eventos que serão escutados por este listener; pode escutar múltiplos eventos ao mesmo tempo
        return [
            UserRegistered::class,
        ];
    }

    /**
     * @param UserRegistered $event
     */
    public function process(object $event): void
    {
        // O código a ser executado pelo listener após o evento ser disparado é escrito aqui, como enviar uma mensagem de sucesso de registro, etc.
        // Acesse diretamente a propriedade user de $event para obter o valor do parâmetro passado quando o evento dispara.
        // $event->user;
    }
}
```

#### Registrando listeners via arquivos de configuração

Depois de definir o listener, precisamos torná-lo descobrível pelo `Dispatcher`. Isso pode ser adicionado no arquivo de configuração `config/autoload/listeners.php` *(se não existir, pode ser criado)*. A ordem de disparo dos listeners segue a ordem de configuração no arquivo:

```php
<?php
return [
    \App\Listener\UserRegisteredListener::class,
];
```

### Registrando listeners com anotação

O Hyperf também fornece uma forma mais fácil de registrar listeners, registrando com a anotação `#[Listener]`. Basta declarar a anotação na classe do listener e o listener será registrado automaticamente no `Hyperf annotation scan domain`. Exemplos:

```php
<?php
namespace App\Listener;

use App\Event\UserRegistered;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

#[Listener]
class UserRegisteredListener implements ListenerInterface
{
    public function listen(): array
    {
        // Retorna um array de eventos que serão escutados por este listener; pode escutar múltiplos eventos ao mesmo tempo
        return [
            UserRegistered::class,
        ];
    }

    /**
     * @param UserRegistered $event
     */
    public function process(object $event): void
    {
        // O código a ser executado pelo listener após o evento ser disparado é escrito aqui, como enviar uma mensagem de sucesso de registro, etc.
        // Acesse diretamente a propriedade user de $event para obter o valor do parâmetro passado quando o evento dispara.
        // $event->user;
    }
}
```

Ao registrar o listener via anotações, podemos definir a ordem do listener atual configurando o atributo `priority`, como `#[Listener(priority: 1)]`. Internamente, isso usa a estrutura `SplPriorityQueue` para armazenar: quanto maior o número de `priority`, maior será a prioridade.

> Para usar a anotação `#[Listener]`, precisa estar no namespace `use Hyperf\Event\Annotation\Listener;`.

### Disparar evento

O evento precisa ser despachado pelo `EventDispatcher` para permitir que o `Listener` escute. Usamos um trecho de código para demonstrar como disparar o evento:

```php
<?php
namespace App\Service;

use Hyperf\Di\Annotation\Inject;
use Psr\EventDispatcher\EventDispatcherInterface;
use App\Event\UserRegistered; 

class UserService
{
    #[Inject]
    private EventDispatcherInterface $eventDispatcher;
    
    public function register()
    {
        // Assumimos que existe uma entidade User
        $user = new User();
        $result = $user->save();
        // Conclui a lógica de registro da conta
        // Este dispatch(object $event) executa os listeners um a um
        $this->eventDispatcher->dispatch(new UserRegistered($user));
        return $result;
    }
}
```

## Eventos do ciclo de vida do Hyperf

![](imgs/hyperf-events.svg)

## Eventos do ciclo de vida do Hyperf Coroutine Style Server

![](https://raw.githubusercontent.com/hyperf/raw-storage/main/hyperf/svg/hyperf-coroutine-events.svg)

## Precauções

### Não injete `EventDispatcherInterface` no `Listener`

Porque `EventDispatcherInterface` depende de `ListenerProviderInterface`, e `ListenerProviderInterface` coletará todos os `Listener` quando for inicializado.

E se `Listener` depender de `EventDispatcherInterface`, isso levará a dependência circular, o que pode causar estouro de memória.

### É melhor injetar apenas `ContainerInterface` no `Listener`.

É melhor injetar apenas `ContainerInterface` no `Listener`, enquanto outros componentes são obtidos via `container` em `process`. Quando o framework inicia, `EventDispatcherInterface` será instanciado. Nesse momento, não é um ambiente de coroutine. Se o `Listener` injetar uma classe que possa disparar uma troca de coroutine, isso fará com que o framework falhe ao iniciar.

