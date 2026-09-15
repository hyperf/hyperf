# Dependency Injection

## Introdução

O Hyperf usa o [hyperf/di](https://github.com/hyperf/di) por padrão como o container de gerenciamento de Dependency Injection do framework. Embora, em termos de design, permitamos que você substitua o container de gerenciamento de Dependency Injection por outros componentes, recomendamos fortemente que não substitua o [hyperf/di](https://github.com/hyperf/di).

O [hyperf/di](https://github.com/hyperf/di) é um componente poderoso usado para gerenciar dependências das classes e executar injeção automática. Comparado com containers de Dependency Injection tradicionais, ele é mais adequado para aplicações de longa duração, fornecendo suporte a [Annotation & Annotation Injection](pt-br/annotation.md) e capacidades extremamente poderosas de [AOP Aspect-Oriented Programming](pt-br/aop.md). Essas capacidades e a facilidade de uso são o principal diferencial do Hyperf, e acreditamos firmemente que este componente é o melhor.

## Instalação

Este componente já existe por padrão no [hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) e existe como o componente principal. Se você quiser usar este componente em outros frameworks, pode instalá-lo com o comando a seguir.

```bash
composer require hyperf/di
```

## Vinculação de Relacionamento entre Objetos

### Injeção Simples de Objeto

Geralmente, a relação e a injeção da class não precisam ser explicitamente definidas. O Hyperf fará tudo isso para você. A demonstração de código a seguir ilustra o uso relacionado.
Suponha que precisemos chamar o method `getInfoById(int $id)` da class `UserService` dentro do `IndexController`.
```php
<?php
namespace App\Service;

class UserService
{
    public function getInfoById(int $id)
    {
        // Assume that there is an entity of Info.
        return (new Info())->fill($id);    
    }
}
```

#### Injeção pelo Constructor

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
    
    // Automatic injection is completed by declaring the parameter type on the parameters of the constructor
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    
    public function index()
    {
        $id = 1;
        // Use directly
        return $this->userService->getInfoById($id);    
    }
}
```

> Observe que quem chama, ou seja, o `IndexController`, deve ser um objeto criado pelo `DI` para que a injeção automática ocorra. E o controller é criado pelo `DI` por padrão, então você pode injetar diretamente no constructor.

Quando você quiser definir uma dependência opcional, você pode definir o parâmetro como `nullable` ou o valor padrão do parâmetro como `null`. Isso significa que, se o parâmetro não for encontrado no container do DI ou o objeto correspondente não puder ser criado, `null` será injetado em vez de lançar uma exception. *(Esta funcionalidade está disponível apenas na versão 1.1.0 ou superior)*

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
            // $userService is available only in the condition that it is not null
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
     * Use `#[Inject]` to inject the attribute type object declared by `@var` 
     * 
     * @var UserService
     */
    #[Inject]
    private $userService;
    
    public function index()
    {
        $id = 1;
        // Use directly
        return $this->userService->getInfoById($id);    
    }
}
```

> Observe que quem chama, ou seja, o `IndexController`, deve ser um objeto criado pelo `DI` para que a injeção automática ocorra. O controller é criado pelo `DI` por padrão.

> O namespace `use Hyperf\Di\Annotation\Inject;` deve ser usado quando `#[Inject]` for utilizado.

##### Parâmetro obrigatório

A annotation `#[Inject]` possui um parâmetro `required`, cujo valor padrão é `true`. Quando o parâmetro é definido como `false`, indica que essa attribute é uma dependência opcional. Quando o objeto correspondente a `@var` não existir no DI, será injetado um `null` em vez de lançar uma exception.

```php
<?php
namespace App\Controller;

use App\Service\UserService;
use Hyperf\Di\Annotation\Inject;

class IndexController
{
    /**
     * Inject the attribute type object declared by the `@var` annotation through the `#[Inject]` annotation
     * Null will be injected when UserService does not exist in the DI container or cannot be created
     *
     * @var UserService
     */
    #[Inject(required: false)]
    private $userService;
    
    public function index()
    {
        $id = 1;
        if ($this->userService instanceof UserService) {
            // $userService is available only in the condition that it is not null
            return $this->userService->getInfoById($id);    
        }
        return null;
    }
}
```

### Injeção de Objeto Abstrato

Com base no exemplo acima, sob um ponto de vista razoável, o Controller não deveria trabalhar diretamente com uma class `UserService`, mas talvez mais com uma interface class de `UserServiceInterface`. Então, podemos usar `config/autoload/dependencies.php` para vincular a relação entre objetos e alcançar esse objetivo. Uma demonstração de código pode explicar isso.

Definir uma interface class:

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

Após esta configuração, você pode injetar diretamente o objeto `UserService` através de `UserServiceInterface`. Usamos a injeção por annotation como exemplo, e a injeção via constructor também funciona da mesma forma:

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
        // Use directly
        return $this->userService->getInfoById($id);    
    }
}
```

### Injeção de Objeto via Factory
  
Agora, vamos tornar a implementação de `UserService` mais complexa, com alguns parâmetros injetados indiretamente que devem ser passados para o constructor quando uma instância de `UserService` é criada. Imagine que precisamos obter um valor da config, e então `UserService` precisa decidir se habilita o modo cache com base nesse valor. (A propósito, o Hyperf fornece uma função melhor de [cache mode](pt-br/db/model-cache.md))

Precisamos criar uma factory para gerar objetos `UserService`:

```php
<?php 
namespace App\Service;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

class UserServiceFactory
{
    // Implement an __invoke() method for the production of the object, and parameters will be automatically injected into a current container instance and the parameters array.
    public function __invoke(ContainerInterface $container, array $parameters = [])
    {
        $config = $container->get(ConfigInterface::class);
        // Assume that the key of corresponding config is cache.enable
        $enableCache = $config->get('cache.enable', false);
        // The method make(string $name, array $parameters = []) is equivalent to new. Using make() allows AOP to intervene, however, using new will prevent AOP to intervene into normal processing.
        return make(UserService::class, compact('enableCache'));
    }
}
```

`UserService` pode fornecer uma attribute no constructor para receber o valor correspondente:

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
        // Receiving the value and store it at an attribute
        $this->enableCache = $enableCache;
    }
    
    public function getInfoById(int $id)
    {
        return (new Info())->fill($id);    
    }
}
```

Ajuste a relação de vinculação em `config/autoload/dependencies.php`:

```php
<?php
return [
    \App\Service\UserServiceInterface::class => \App\Service\UserServiceFactory::class
];
```

Dessa forma, ao injetar `UserServiceInterface`, o container delegará a criação do objeto ao `UserServiceFactory`.

> É claro que, nesse cenário, você pode usar a annotation `#[Value]` para injetar a configuration de forma mais conveniente, em vez de construir uma factory class. Este exemplo é apenas para fins de explicação.

### Lazy Loading

A Dependency Injection de longa duração do Hyperf é feita quando o projeto inicia. Isso significa que classes de longa duração precisam se atentar a:

* Não é um ambiente de coroutine quando o constructor é executado. Se uma injeção ocorrer, uma class que aciona a troca de coroutine pode ser disparada, o que fará o framework falhar ao iniciar.

* Evite dependências circulares no constructor (tipicamente, `Listener` e `EventDispatcherInterface`), caso contrário a inicialização falhará.

A solução atual é: injetar apenas `Psr\Container\ContainerInterface` na instância, e obter os outros componentes através do `container` em um momento fora da execução do constructor. Porém, como a PSR-11 afirma:

> 「Usuários não devem passar o container como parâmetro para o objeto e então obter a dependência desse objeto através do container passado. Isso usa o container como um service locator, e o service locator é um anti-pattern.」

Em outras palavras, embora essa abordagem funcione, ela não é recomendada do ponto de vista dos padrões de design.

Outra solução é usar o modo de lazy proxy, comumente usado em PHP, injetando um objeto proxy e então instanciando o objeto de destino quando ele é usado. 
O componente DI do Hyperf foi projetado com a funcionalidade de injeção via lazy loading.

Adicione o arquivo `config/lazy_loader.php` e vincule a relação de lazy loading:

```php
<?php
return [
    /**
     * Format: proxy class name => original class name
     * The proxy class does not exist at this time, and Hyperf will automatically generate this class in the runtime folder.
     * The proxy class name and namespace can be defined by yourself.
     */
    'App\Service\LazyUserService' => \App\Service\UserServiceInterface::class
];
```

Dessa forma, ao injetar `App\Service\LazyUserService`, o container criará uma `proxy class de lazy loading` e a injetará no objeto de destino.

```php
use App\Service\LazyUserService;

class Foo{
    public $service;
    public function __construct(LazyUserService $service){
        $this->service = $service;
    }
}
````

You can also inject lazy loading proxy through the annotation `#[Inject(lazy: true)]`. Implementing lazy loading through annotations does not need to create configuration files.

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

Note: When the proxy object performs the following operations, the proxy object will be actually instantiated from the container.

```php
// Call methods
$proxy->someMethod();

// Get attributes
echo $proxy->someProperty;

// Set attributes
$proxy->someProperty = 'foo';

// Check if a attribute exists
isset($proxy->someProperty);

// Delete attributes
unset($proxy->someProperty);
```

## Objetos de Curta Duração

Objetos criados pelo `new` são, sem dúvida, de curta duração. Se você quiser criar um objeto de curta duração e injetar dependências relacionadas através do container de Dependency Injection, você pode criar `$name` através da função `make(string $name, array $parameters = [])`. O exemplo de código é o seguinte:

```php
$userService = make(UserService::class, ['enableCache' => true]);
```

> Observe que apenas o objeto correspondente a `$name` é um objeto de curta duração, e todas as dependências desse objeto são obtidas através do method `get()`, o que significa que este objeto é um objeto de longa duração.

## Obter o Objeto do Container

Às vezes queremos alcançar alguns requisitos mais dinâmicos, gostaríamos de conseguir obter diretamente o objeto `Container`. Na maioria dos casos, as classes de entrada do framework, como classes de comando, controllers, provedores de serviço RPC, etc., são criadas e mantidas pelo `Container`, o que significa que a maior parte do seu código de negócio está sob o gerenciamento do `Container`. Isso também significa que, na maioria dos casos, você pode obter o objeto `Hyperf\Di\Container` declarando no `Constructor` ou injetando a interface class `Psr\Container\ContainerInterface` através da annotation `#[Inject]`. Aqui está um exemplo:

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
    
    // Automatic injection is completed by declaring the parameter type on the parameters of the constructor
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
```   

Em algumas situações dinâmicas mais extremas, ou quando não está sob o gerenciamento do `Container`, você também pode usar o method `\Hyperf\Context\ApplicationContext::getContainer()` para obter o objeto `Container`.

```php
$container = \Hyperf\Context\ApplicationContext::getContainer();
```

## Cuidados

### O container gerencia apenas objetos de longa duração

Em outras palavras, os objetos gerenciados pelo container são **todos singletons**. Esse design é mais eficiente para aplicações de longa duração, reduzindo a criação e destruição desnecessária de objetos. Isso também significa que todos os objetos que precisam ser gerenciados pelo container do DI **não podem** conter o valor de `state`. Onde `state` representa alguns valores que mudarão de acordo com a request. Na verdade, na programação com [coroutine](pt-br/coroutine.md), esses valores de estado também devem ser armazenados no `coroutine context`, ou seja, em `Hyperf\Context\Context`.
