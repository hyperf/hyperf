# Dependency Injection

## Introduction

Hyperf uses [hyperf/di](https://github.com/hyperf/di) as the dependency injection management container for the framework by default. Although we allow you to replace it with other dependency injection management containers by design, we strongly recommend that you do not replace this component.
[hyperf/di](https://github.com/hyperf/di) is a powerful component for managing class dependencies and completing automatic injection. The difference from traditional dependency injection containers is that it is more suitable for the use of long-lifecycle applications, provides support for [Annotations and Annotation Injection](annotation.md), and provides incomparably powerful [AOP Aspect-Oriented Programming](aop.md) capabilities. As a core output of Hyperf, we are confident that this component is the best.

## Installation

This component exists in the [hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) project by default and exists as a main component. If you wish to use this component in other frameworks, you can install it via the following command.

```bash
composer require hyperf/di
```

## Binding Object Relationships

### Simple Object Injection

Generally speaking, the relationship and injection of classes do not need to be explicitly defined. All of this will be silently completed for you by Hyperf. We use some code examples to illustrate the related usage.
Suppose we need to call the `getInfoById(int $id)` method of the `UserService` class in `IndexController`.

```php
<?php
namespace App\Service;

class UserService
{
    public function getInfoById(int $id)
    {
        // We assume an Info entity exists
        return (new Info())->fill($id);    
    }
}
```

#### Injection via Constructor Method

```php
<?php
namespace App\Controller;

use App\Service\UserService;

class IndexController
{
    private UserService $userService;
    
    // Complete automatic injection by declaring parameter types in the constructor parameters
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    
    public function index()
    {
        $id = 1;
        // Directly use
        return $this->userService->getInfoById($id);    
    }
}
```

> Note that when using constructor injection, the caller, `IndexController`, must be an object created by DI to complete automatic injection, and Controller is created by DI by default, so constructor injection can be used directly.

When you want to define an optional dependency, you can define the parameter as `nullable` or define the default value of the parameter as `null`. This means that if the corresponding object is not found in the DI container or cannot be created, no exception will be thrown, but `null` will be used for injection.

```php
<?php
namespace App\Controller;

use App\Service\UserService;

class IndexController
{
    private ?UserService $userService;
    
    // Indicate that this parameter is an optional parameter by setting it to nullable
    public function __construct(?UserService $userService)
    {
        $this->userService = $userService;
    }
    
    public function index()
    {
        $id = 1;
        if ($this->userService instanceof UserService) {
            // $userService is available only when the value exists
            return $this->userService->getInfoById($id);    
        }
        return null;
    }
}
```

#### Injection via `#[Inject]` Annotation

```php
<?php
namespace App\Controller;

use App\Service\UserService;
use Hyperf\Di\Annotation\Inject;

class IndexController
{

    #[Inject]
    private UserService $userService;
    
    public function index()
    {
        $id = 1;
        // Directly use
        return $this->userService->getInfoById($id);    
    }
}
```

> Injection via `#[Inject]` annotation can act on objects created by DI (singletons), or objects created via the `new` keyword;
>
> When using the `#[Inject]` annotation, you need to `use Hyperf\Di\Annotation\Inject;` namespace;

##### Required Parameter

The `#[Inject]` annotation has a `required` parameter, and the default value is `true`. When this parameter is defined as `false`, it indicates that this member property is an optional dependency. When the object corresponding to `@var` does not exist in the DI container or cannot be created, no exception will be thrown, but `null` will be injected, as follows:

```php
<?php
namespace App\Controller;

use App\Service\UserService;
use Hyperf\Di\Annotation\Inject;

class IndexController
{
    /**
     * Inject the object type of the property declared by the annotation via `#[Inject]` annotation
     * When UserService does not exist in the DI container or cannot be created, null is injected
     */
    #[Inject(required: false)]
    private ?UserService $userService;
    
    public function index()
    {
        $id = 1;
        if ($this->userService instanceof UserService) {
            // $userService is available only when the value exists
            return $this->userService->getInfoById($id);    
        }
        return null;
    }
}
```

### Abstract Object Injection

Based on the example above, from a reasonable perspective, the Controller should not directly face a `UserService` class, but perhaps an interface class `UserServiceInterface`. At this time, we can bind object relationships through `config/autoload/dependencies.php` to achieve the goal. Let's still use code to explain it.

Define an interface class:

```php
<?php
namespace App\Service;

interface UserServiceInterface
{
    public function getInfoById(int $id);
}
```

`UserService` implements the interface class:

```php
<?php
namespace App\Service;

class UserService implements UserServiceInterface
{
    public function getInfoById(int $id)
    {
        // We assume an Info entity exists
        return (new Info())->fill($id);    
    }
}
```

Configure relationships in `config/autoload/dependencies.php`:

```php
<?php
return [
    \App\Service\UserServiceInterface::class => \App\Service\UserService::class
];
```

After such configuration, you can directly inject the `UserService` object via `UserServiceInterface`. We only use annotation injection to give an example, constructor injection is the same:

```php
<?php
namespace App\Controller;

use App\Service\UserServiceInterface;
use Hyperf\Di\Annotation\Inject;

class IndexController
{
    #[Inject]
    private UserServiceInterface $userService;
    
    public function index()
    {
        $id = 1;
        // Directly use
        return $this->userService->getInfoById($id);    
    }
}
```

### Factory Object Injection

Suppose the implementation of `UserService` is more complex. When creating a `UserService` object, some non-directly injectable parameters also need to be passed into the constructor. Suppose we need to get a value from the configuration, and then `UserService` needs to decide whether to enable cache mode based on this value (by the way, Hyperf provides a better [Model Cache](db/model-cache.md) function).

We need to create a factory to generate `UserService` objects:

```php
<?php 
namespace App\Service;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

class UserServiceFactory
{
    // Implement an __invoke() method to complete object production. Method parameters will automatically inject a current container instance and an array of parameters
    public function __invoke(ContainerInterface $container, array $parameters = [])
    {
        $config = $container->get(ConfigInterface::class);
        // We assume the key of the corresponding configuration is cache.enable
        $enableCache = $config->get('cache.enable', false);
        return make(UserService::class, compact('enableCache'));
    }
}
```

`UserService` can also provide a parameter in the constructor to receive the corresponding value:

```php
<?php
namespace App\Service;

class UserService implements UserServiceInterface
{
    private bool $enableCache;
    
    public function __construct(bool $enableCache)
    {
        // Receive the value and store it in the class property
        $this->enableCache = $enableCache;
    }
    
    public function getInfoById(int $id)
    {
        return (new Info())->fill($id);    
    }
}
```

Adjust binding relationships in `config/autoload/dependencies.php`:

```php
<?php
return [
    \App\Service\UserServiceInterface::class => \App\Service\UserServiceFactory::class
];
```

In this way, when `UserServiceInterface` is injected, the container will hand over the creation of the object to `UserServiceFactory`.

> Of course, in this scenario, you can inject configuration more conveniently via `#[Value]` annotation without building a factory class. This is just an example.

### Lazy Loading

Hyperf's long-lifecycle dependency injection is completed when the project starts. This means that long-lifecycle classes need to pay attention to:

* The constructor is not yet in a coroutine environment. If a class that may trigger coroutine switching is injected, it will cause the framework to fail to start.

* Avoid circular dependencies in the constructor (a typical example is `Listener` and `EventDispatcherInterface`), otherwise it will also fail to start.

The current solution is: only inject `Psr\Container\ContainerInterface` in the instance, and other components are obtained via `container` when non-constructor is executed. But PSR-11 points out:

> "Users should not pass the container as an argument to an object in order to obtain the object's dependencies from the container within the object. This uses the container as a service locator, and service locator is an anti-pattern."

That is to say, although this practice is effective, it is not recommended from the perspective of design patterns.

Another solution is to use the lazy proxy pattern commonly used in PHP, inject a proxy object, and then instantiate the target object when it is used. Hyperf DI component designed the lazy loading injection function.

Add `config/lazy_loader.php` file and bind lazy loading relationships:

```php
<?php
return [
    /**
     * Format: Proxy class name => Original class name
     * The proxy class does not exist at this time, Hyperf will automatically generate the class under the runtime folder.
     * The proxy class name and namespace can be freely defined.
     */
    'App\Service\LazyUserService' => \App\Service\UserServiceInterface::class
];
```

In this way, when `App\Service\LazyUserService` is injected, the container will create a `lazy loading proxy class` and inject it into the target object.

```php
use App\Service\LazyUserService;

class Foo{
    public $service;
    public function __construct(LazyUserService $service){
        $this->service = $service;
    }
}
```

You can also inject a lazy loading proxy via the annotation `#[Inject(lazy: true)]`. Implementing lazy loading via annotation does not need to create a configuration file.

```php
use Hyperf\Di\Annotation\Inject;
use App\Service\UserServiceInterface;

class Foo
{
    /**
     * @var UserServiceInterface
     */
    #[Inject(lazy: true)]
    public $service;
}
```

Note: When the proxy object performs the following operations, the proxied object will be truly instantiated from the container.

```php
// Method call
$proxy->someMethod();

// Read property
echo $proxy->someProperty;

// Write property
$proxy->someProperty = 'foo';

// Check if property exists
isset($proxy->someProperty);

// Delete property
unset($proxy->someProperty);
```

### Binding Weight

Since version v3.0.17, the weight function has been added. You can inject the object with the highest weight according to the weight. For example, the following two `ConfigProvider` configurations

```php
<?php
use FooInterface;
use Foo;

return [
    'dependencies' => [
        FooInterface::class => new PriorityDefinition(Foo::class, 1),
    ]
];
```

```php
<?php
use FooInterface;
use Foo2;

return [
    'dependencies' => [
        FooInterface::class => Foo2::class,
    ]
];
```

When `PriorityDefinition` is not used, the weight is 0. So the one bound to `FooInterface` is `Foo`.

## Short Lifecycle Objects

Objects created via the `new` keyword are undoubtedly short-lived. So what if you want to create a short-lived object but want to use the `constructor dependency automatic injection function`? At this time, we can create an instance corresponding to `$name` via the `make(string $name, array $parameters = [])` function. The code example is as follows:

```php
$userService = make(UserService::class, ['enableCache' => true]);
```

> Note that only the object corresponding to `$name` is a short-lived object. All dependencies of this object are obtained via the `get()` method, that is, they are long-lived objects. It can be understood that this object is a shallow copy object.

## Obtaining Container Objects

Sometimes when we want to implement some more dynamic requirements, we want to be able to directly obtain the `Container` object. In most cases, the framework's entry classes (such as command classes, controllers, RPC service providers, etc.) are created and maintained by the `Container`, which means that most of the business code you write is under the management of the `Container`, which means that in most cases, you can obtain the `Hyperf\Di\Container` container object by declaring it in the `Constructor` or injecting the `Psr\Container\ContainerInterface` interface class via `#[Inject]` annotation. We demonstrate this with code:

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use Psr\Container\ContainerInterface;

class IndexController
{
    private ContainerInterface $container;
    
    // Complete automatic injection by declaring parameter types in the constructor parameters
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
```

In some more extreme dynamic situations, or when it is not under the management of the `Container`, to obtain the `Container` object, you can also obtain the `Container` object via the `\Hyperf\Context\ApplicationContext::getContainer()` method.

```php
$container = \Hyperf\Context\ApplicationContext::getContainer();
```

## Scan Adapter

By default, `Hyperf\Di\ScanHandler\PcntlScanHandler` is used.

- Hyperf\Di\ScanHandler\PcntlScanHandler

Use Pcntl to fork child processes to scan annotations, only supported in Linux environments

- Hyperf\Di\ScanHandler\NullScanHandler

Do not perform annotation scanning operations

- Hyperf\Di\ScanHandler\ProcScanHandler

Use proc_open to create child processes to scan annotations, supported in Linux and Windows (Swow)

### Replacing Scan Adapter

We only need to actively modify the `Hyperf\Di\ClassLoader::init()` code snippet in the `bin/hyperf.php` file to replace the adapter.

```php
Hyperf\Di\ClassLoader::init(handler: new Hyperf\Di\ScanHandler\ProcScanHandler());
```

## Precautions

### The Container Only Manages Long-lifecycle Objects

In another way, it means that the objects managed in the container **are all singletons**. This design is more efficient for long-lifecycle applications, reducing a large number of meaningless object creations and destructions. This design also means that all objects that need to be managed by the DI container **cannot contain** `state` values.
`State` can be directly understood as a value that changes with the request. In fact, in [Coroutine](coroutine.md) programming, these state values should also be stored in the `Coroutine Context`, namely `Hyperf\Context\Context`.

### #[Inject] Injection Override Order

The `#[Inject]` override order is: subclass overrides `Trait` overrides parent class. That is, the `foo` variable of `Origin` below is the `Foo1` injected by itself.

Similarly, if the variable `$foo` does not exist in `Origin`, `$foo` will be injected by the first `Trait`, injecting class `Foo2`.

```php
use Hyperf\Di\Annotation\Inject;

class ParentClass
{
    /**
     * @var Foo4 
     */
    #[Inject]
    protected $foo;
}

trait Foo1
{
    /**
     * @var Foo2 
     */
    #[Inject]
    protected $foo;
}

trait Foo2
{
    /**
     * @var Foo3
     */
    #[Inject]
    protected $foo;
}

class Origin extends ParentClass
{
    use Foo1;
    use Foo2;

    /**
     * @var Foo1
     */
    #[Inject]
    protected $foo;
}
```
