# Injeção de dependência

## Introdução

Por padrão, o Hyperf usa [hyperf/di](https://github.com/hyperf/di) como o container de gerenciamento de injeção de dependências do framework. Embora no design o Hyperf permita substituir o container de injeção de dependências por outros componentes, recomendamos fortemente que você não substitua o [hyperf/di](https://github.com/hyperf/di).

[hyperf/di](https://github.com/hyperf/di) é um componente poderoso usado para gerenciar dependências de classes e executar injeção automática. Comparado com containers tradicionais de injeção de dependências, ele é mais adequado para aplicações de longa duração, fornece suporte a [Anotações e injeção por anotações](pt-br/annotation.md) e capacidades extremamente poderosas de [AOP (programação orientada a aspectos)](pt-br/aop.md). Esses recursos e a facilidade de uso são alguns dos principais diferenciais do Hyperf, e acreditamos firmemente que este componente é o melhor.

## Instalação

Este componente já vem por padrão no [hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) como um componente principal. Se você quiser usar este componente em outros frameworks, você pode instalá-lo com o comando a seguir.

```bash
composer require hyperf/di
```

## Vinculando relações entre objetos

### Injeção simples de objetos

Em geral, não é necessário definir explicitamente o relacionamento e a injeção entre classes: o Hyperf faz isso por você. O exemplo a seguir ilustra o uso.
Suponha que precisamos chamar o método `getInfoById(int $id)` da classe `UserService` dentro do `IndexController`.
```php
<?php
namespace App\Service;

class UserService
{
    public function getInfoById(int $id)
    {
        // Suponha que exista uma entidade Info.
        return (new Info())->fill($id);    
    }
}
```

#### Injeção via construtor

```php
<?php
namespace App\Controller;

use App\Service\UserService;
use Hyperf\HttpServer\Annotation\AutoController;

class IndexController
{
    /**
     * @var UserService
     */
    private $userService;
    
    // A injeção automática é feita declarando o tipo do parâmetro no construtor
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    
    public function index()
    {
        $id = 1;
        // Uso direto
        return $this->userService->getInfoById($id);    
    }
}
```

> Observe que o chamador (isto é, o `IndexController`) precisa ser um objeto criado pelo `DI` para que a injeção automática funcione. Controllers são criados pelo `DI` por padrão, então você pode injetar diretamente no construtor.

Quando você quiser definir uma dependência opcional, pode definir o parâmetro como `nullable` ou definir o valor padrão do parâmetro como `null`. Isso significa que, se o parâmetro não for encontrado no container DI ou o objeto correspondente não puder ser criado, será injetado `null` em vez de lançar uma exceção. *(Este recurso está disponível apenas na versão 1.1.0 ou superior.)*

```php
<?php
namespace App\Controller;

use App\Service\UserService;

class IndexController
{
    /**
     * @var null|UserService
     */
    private $userService;
    
    // Declare an optional parameter by setting it as nullable.
    public function __construct(?UserService $userService)
    {
        $this->userService = $userService;
    }
    
    public function index()
    {
        $id = 1;
        if ($this->userService instanceof UserService) {
            // $userService só está disponível quando não for null
            return $this->userService->getInfoById($id);    
        }
        return null;
    }
}
```

#### Injeção via `#[Inject]`

```php
<?php
namespace App\Controller;

use App\Service\UserService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;

class IndexController
{
    /**
     * Use `#[Inject]` para injetar o objeto do tipo de atributo declarado por `@var` 
     * 
     * @var UserService
     */
    #[Inject]
    private $userService;
    
    public function index()
    {
        $id = 1;
        // Uso direto
        return $this->userService->getInfoById($id);    
    }
}
```

> Observe que o chamador (isto é, o `IndexController`) precisa ser um objeto criado pelo `DI` para realizar a injeção automática. Controllers são criados pelo `DI` por padrão.

> O namespace `use Hyperf\\Di\\Annotation\\Inject;` deve ser usado ao utilizar `#[Inject]`.

##### Parâmetro required

A annotation `#[Inject]` tem um parâmetro `required`, cujo valor padrão é `true`. Quando o parâmetro é definido como `false`, isso indica que esse atributo é uma dependência opcional. Quando o objeto correspondente ao `@var` não existir no DI, será injetado `null` em vez de lançar uma exceção.

```php
<?php
namespace App\Controller;

use App\Service\UserService;
use Hyperf\Di\Annotation\Inject;

class IndexController
{
     /**
      * Injeta o tipo declarado na annotation `@var` através da annotation `#[Inject]`.
      * Se UserService não existir no container DI ou não puder ser criado, será injetado null.
      *
      * @var UserService
      */
    #[Inject(required: false)]
    private $userService;
    
    public function index()
    {
        $id = 1;
        if ($this->userService instanceof UserService) {
            // $userService só está disponível quando não for null
            return $this->userService->getInfoById($id);    
        }
        return null;
    }
}
```

### Injeção de objetos abstratos

Com base no exemplo acima, do ponto de vista de design, o controller não deveria depender diretamente da classe `UserService`, mas sim de uma interface como `UserServiceInterface`. Para isso, podemos usar `config/autoload/dependencies.php` para vincular os relacionamentos entre objetos. Um exemplo demonstra isso.

Defina uma interface:

```php
<?php
namespace App\Service;

interface UserServiceInterface
{
    public function getInfoById(int $id);
}
```

`UserService` implementa a interface:

```php
<?php
namespace App\Service;

class UserService implements UserServiceInterface
{
    public function getInfoById(int $id)
    {
        // Assume that there is an entity of Info.
        return (new Info())->fill($id);    
    }
}
```

Configure as relações em `config/autoload/dependencies.php`:

```php
<?php
return [
    \App\Service\UserServiceInterface::class => \App\Service\UserService::class
];
```

Após essa configuração, você pode injetar o objeto `UserService` através de `UserServiceInterface`. Vamos usar injeção por annotation como exemplo, mas injeção via construtor funciona do mesmo jeito:

```php
<?php
namespace App\Controller;

use App\Service\UserServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;

class IndexController
{
    #[Inject]
    private UserServiceInterface $userService;
    
    public function index()
    {
        $id = 1;
        // Uso direto
        return $this->userService->getInfoById($id);    
    }
}
```

### Injeção via factory
  
Agora, vamos supor que a implementação de `UserService` fique mais complexa e que existam parâmetros indiretos que precisam ser passados no construtor quando uma instância de `UserService` é criada. Imagine que precisamos obter um valor da configuração e, com base nisso, `UserService` decide se deve habilitar cache. (Aliás, o Hyperf fornece um recurso melhor de [cache de model](pt-br/db/model-cache.md).)

Precisamos criar uma factory para gerar objetos `UserService`:

```php
<?php 
namespace App\Service;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

class UserServiceFactory
{
    // Implemente um método __invoke() para produzir o objeto; parâmetros serão injetados automaticamente pelo container e pelo array de parâmetros.
    public function __invoke(ContainerInterface $container, array $parameters = [])
    {
        $config = $container->get(ConfigInterface::class);
        // Suponha que a chave de config seja cache.enable
        $enableCache = $config->get('cache.enable', false);
        // O método make(string $name, array $parameters = []) é equivalente a new. Usar make() permite que o AOP intervenha; usar new impede que o AOP intervenha no fluxo normal.
        return make(UserService::class, compact('enableCache'));
    }
}
```

`UserService` pode receber o valor correspondente no construtor:

```php
<?php
namespace App\Service;

class UserService implements UserServiceInterface
{
    
    /**
     * @var bool
     */
    private $enableCache;
    
    public function __construct(bool $enableCache)
    {
        // Recebe o valor e o armazena em um atributo
        $this->enableCache = $enableCache;
    }
    
    public function getInfoById(int $id)
    {
        return (new Info())->fill($id);    
    }
}
```

Ajuste o binding em `config/autoload/dependencies.php`:

```php
<?php
return [
    \App\Service\UserServiceInterface::class => \App\Service\UserServiceFactory::class
];
```

Dessa forma, ao injetar `UserServiceInterface`, o container delegará a criação do objeto para `UserServiceFactory`.

> Claro, neste cenário você pode usar a annotation `#[Value]` para injetar configurações de forma mais conveniente, em vez de criar uma factory. Este exemplo é apenas para fins didáticos.

### Lazy loading (carregamento preguiçoso)

A injeção de dependências de longa duração do Hyperf acontece na inicialização do projeto. Isso significa que classes de longa duração precisam se atentar a:

* O construtor não roda em ambiente de corrotina. Se uma injeção disparar algo que faça troca de corrotina, o framework pode falhar ao iniciar.

* Evite dependências circulares no construtor (tipicamente, entre `Listener` e `EventDispatcherInterface`), caso contrário a inicialização falhará.

A solução atual é: injetar apenas `Psr\\Container\\ContainerInterface` na instância, e obter os demais componentes via `container` posteriormente, fora do tempo de execução do construtor. Porém, como o PSR-11 diz:

> “Usuários não deveriam passar o container como parâmetro para o objeto e então obter as dependências desse objeto através do container passado. Isso usa o container como um service locator, e service locator é um anti-pattern.”

Em outras palavras, embora esse approach funcione, ele não é recomendado do ponto de vista de padrões de projeto.

Outra solução é usar o padrão de proxy lazy, comum em PHP: injete um objeto proxy e instancie o objeto real apenas quando ele for usado.
O componente Hyperf DI foi projetado com suporte a injeção com lazy loading.

Adicione o arquivo `config/lazy_loader.php` e faça o binding do relacionamento de lazy loading:

```php
<?php
return [
    /**
     * Formato: nome da classe proxy => nome da classe original
     * A classe proxy não existe neste momento; o Hyperf irá gerar automaticamente essa classe na pasta runtime.
     * O nome e o namespace da classe proxy podem ser definidos por você.
     */
    'App\Service\LazyUserService' => \App\Service\UserServiceInterface::class
];
```

Dessa forma, ao injetar `App\\Service\\LazyUserService`, o container criará uma `classe proxy de lazy loading` e a injetará no objeto alvo.

```php
use App\Service\LazyUserService;

class Foo{
    public $service;
    public function __construct(LazyUserService $service){
        $this->service = $service;
    }
}
````

Você também pode injetar um proxy de lazy loading com a annotation `#[Inject(lazy: true)]`. Implementar lazy loading via annotation dispensa a criação de arquivos de configuração.

```php
use Hyperf\Di\Annotation\Inject;
use App\Service\UserServiceInterface;

class Foo{
    /**
     * @var UserServiceInterface
     */
    #[Inject(lazy: true)]
    public $service;
}
````

Nota: quando o objeto proxy executar as operações a seguir, o objeto real será instanciado pelo container.

```php
// Chamar métodos
$proxy->someMethod();

// Ler atributos
echo $proxy->someProperty;

// Definir atributos
$proxy->someProperty = 'foo';

// Verificar se um atributo existe
isset($proxy->someProperty);

// Remover atributos
unset($proxy->someProperty);
```

## Objetos de curta duração

Objetos criados com `new` são, sem dúvida, de curta duração. Se você quer criar um objeto de curta duração e injetar dependências pelo container DI, você pode criar `$name` através da função `make(string $name, array $parameters = [])`. Exemplo:

```php
$userService = make(UserService::class, ['enableCache' => true]);
```

> Observe que apenas o objeto correspondente a `$name` é de curta duração. Todas as dependências desse objeto são obtidas via `get()`, o que significa que essas dependências são objetos de longa duração.

## Obter o objeto Container

Às vezes, queremos atender requisitos mais dinâmicos e obter diretamente o objeto `Container`. Na maioria dos casos, classes de entrada do framework — como commands, controllers, provedores de serviço RPC etc. — são criadas e mantidas pelo `Container`, o que significa que grande parte do seu código de negócio está sob gerenciamento do `Container`. Isso também significa que, na maioria dos casos, você pode obter o `Hyperf\\Di\\Container` declarando no `Constructor` ou injetando a interface `Psr\\Container\\ContainerInterface` via annotation `#[Inject]`. Exemplo:

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use Psr\Container\ContainerInterface;

class IndexController
{
    /**
     * @var ContainerInterface
     */
    private $container;
    
    // A injeção automática é feita declarando o tipo do parâmetro no construtor
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
```   

Em situações mais extremas/dinâmicas, ou quando algo não está sob gerenciamento do `Container`, você também pode usar o método `\\Hyperf\\Context\\ApplicationContext::getContainer()` para obter o `Container`.

```php
$container = \Hyperf\Context\ApplicationContext::getContainer();
```

## Cuidados

### O container só gerencia objetos de longa duração

Em outras palavras, os objetos gerenciados pelo container são **todos singletons**. Esse design é mais eficiente para aplicações de longa duração, reduzindo criação e destruição desnecessárias de objetos. Isso também significa que objetos gerenciados pelo container DI **não podem** conter `state` (estado), isto é, valores que mudam de request para request. Em programação com [corrotinas](pt-br/coroutine.md), esses valores de estado devem ser armazenados no `contexto de corrotina`, isto é, `Hyperf\\Context\\Context`.

