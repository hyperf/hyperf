# Dependency Injection

## Introduction

Hyperf uses [hyperf/di](https://github.com/hyperf/di) as the framework's dependency injection management container by default. Although in design, we allow you to replace the dependency injection management container with other components, we strongly recommended that don't replace [hyperf/di](https://github.com/hyperf/di).

[hyperf/di](https://github.com/hyperf/di) is a powerful component used to manage dependencies of classes and excute automatic injection. Compared with traditional dependency injection containers, it is more suitable for long-life applications, provides the [Annotation & Annotation Injection](en/annotation.md) support and extremely powerful [AOP Aspect-Oriented Programming](en/aop.md) capabilities. These capabilities and ease of use are the main output of Hyperf, and we firmly believe that this component is the best.

## Installation

This component exists by default in the [hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) and exists as the major component. If you want to use this component in other frameworks, you can install it with the following command.

```bash
composer require hyperf/di
```

## Binding Object Relationship

### Simple Object Injection

Generally, the relationship and injection of the class do not need to be conspicuously defined. Hyperf will do all these for you. The following code demo will illustrate related usage.
Suppose we need to call the `getInfoById(int $id)` method of the `UserService` class in the `IndexController`.
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

#### Inject by Constructor

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

> Note that the caller, that is, the `IndexController` must be an object created by `DI` to perform automatic injection. And controller is created by `DI` by default, so that you can inject directly in constructor.

When you want to define an optional dependency, you can define the parameter as `nullable` or the default value of the parameter as `null`. This means that if the parameter is not found in the DI container or the corresponding object cannot be created, `null` will be injected instead of throwing an exception. *(This function is only available in 1.1.0 or higher version)*

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

#### Inject by `#[Inject]`

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

> Note that the caller, that is, the `IndexController` must be an object created by `DI` to perform automatic injection. Controller is created by `DI` by default.

> The namespace `use Hyperf\Di\Annotation\Inject;` should be used when `#[Inject]` used.

##### Required Parameter

The `#[Inject]` annotation has a `required` parameter, and the default value is `true`. When the parameter is defined as `false`, it indicates that this attribute is an optional dependency. When the object corresponding to `@var` does not exist in DI, a `null` will be injected instead of throwing an exception.

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

### Abstract Object Injection

Based on the above example, from a reasonable point of view, the Controller should not directly work with a `UserService` class, but maybe more of an interface class of `UserServiceInterface`. So, we can use `config/autoload/dependencies. php` to bind the object relationship to achieve the goal. A code demo can explain this.

Define an interface class:

```php
<?php
namespace App\Service;

interface UserServiceInterface
{
    public function getInfoById(int $id);
}
```

`UserService` implements the interface:

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

Configure relations in `config/autoload/dependencies.php`:

```php
<?php
return [
    \App\Service\UserServiceInterface::class => \App\Service\UserService::class
];
```

After this configuration, you can directly inject the `UserService` object through the `UserServiceInterface`. We use annotation injection as an example, and the constructor injection is also the same:

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

### Factory Object Injection
  
Now, let the implementation of `UserService` be more complex, and there are some indirect injected parameters that should be passed into the constructor when a `UserService` instance is created. Imagine that we have to get a value from config, then `UserService` needs to decide whether to enable cache mode based on this value. (By the way, Hyperf provides a better [cache mode](en/db/model-cache.md) function)

We have to create a factory to generate `UserService`  objects:

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

`UserService` may provide an attribute in the constructor to receive the corresponding value:

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

Adjust the binding relationship in `config/autoload/dependencies.php`:

```php
<?php
return [
    \App\Service\UserServiceInterface::class => \App\Service\UserServiceFactory::class
];
```

In this way, when injecting `UserServiceInterface`, the container will hand over the object's creation to `UserServiceFactory`.

> Of course, in this scenario, you can use the `#[Value]` annotation to inject configuration more conveniently rather than building a factory class. This example is just for explaining.

### Lazy Loading

Hyperf's long-lived dependency injection is done when the project starts. This means that long-lived classes need to pay attention to:

* It is not a coroutine environment when the constructor runs. If injection happened, a coroutine switching class may be triggered. It will cause the framework to fail to start.

* Avoid circular dependencies in the constructor (typically, `Listener` and `EventDispatcherInterface`), otherwise the startup will fail.

The current solution is: only inject `Psr\Container\ContainerInterface` into the instance, and other components are obtained through `container` at a time outside the runtime of the constructor. However, as PSR-11 states:

> 「Users should not pass the container as a parameter to the object and then obtain the dependency of that object through the passed container. This uses the container as a service locator, and the service locator is an anti-pattern.」

In other words, although this approach works, it is not recommended from the perspective of design patterns.

Another solution is to use the lazy proxy mode which commonly used in PHP, inject a proxy object, and then instantiate the target object when it is used. 
The Hyperf DI component is designed with lazy loading injection function.

Add the `config/lazy_loader.php` file and bind the lazy loading relationship:

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

In this way, when injecting `App\Service\LazyUserService`, the container will create a `lazy loading proxy class` and inject it into the target object.

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

## Short-lived Objects

Objects created by the `new` are undoubtedly short-lived. If you want to create a short-lived object and want to inject related dependencies through the dependency injection container, you can create `$name` through the `make(string $name, array $parameters = [])` function. The code example is as follows:

```php
$userService = make(UserService::class, ['enableCache' => true]);
```

> Note that only the object corresponding to `$name` is a short-lived object, and all dependencies of this object are obtained through the `get()` method, which means this object is a long-lived object.

## Get the Container Object

Sometimes we wish to achieve some more dynamic requirements, we would like to be able to directly obtain the `Container` object. In most cases, the entry classes of the framework, such as command classes, controllers, RPC service providers, etc., are created and maintained by `Container`, which means that most of your business codes are all under the management of `Container`. This also means that in most cases you can get the `Hyperf\Di\Container` object by declaring in the `Constructor` or by injecting the `Psr\Container\ContainerInterface` interface class through the `#[Inject]` annotation. Here is an example:

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

In some more extreme dynamic situations, or when it is not under the management of `Container`, you can also use `\Hyperf\Context\ApplicationContext::getContainer()` method to obtain the `Container` object.

```php
$container = \Hyperf\Context\ApplicationContext::getContainer();
```

## Cautions

### The container only manages long-lived objects

In other words, the objects managed by container are **all singletons**. This design is more efficient for long-life applications, reducing the meaningless creation and destruction of objects. This also means that all objects that need to be managed by the DI container **can not** contain the `state` value. Which `state` represents some values that will change with the request. In fact, in [coroutine](en/coroutine.md) programming, these state values should also be stored in the `coroutine context`, that is, ` Hyperf\Context\Context`.
