# 依赖注入

## 简介

Hyperf 默认采用 [hyperf/di](https://github.com/hyperf-cloud/di) 作为框架的依赖注入管理容器，尽管从设计上我们允许您更换其它的依赖注入管理容器，但我们强烈不建议您更换该组件。   
[hyperf/di](https://github.com/hyperf-cloud/di) 是一个强大的用于管理类的依赖关并完成自动注入的组件，与传统依赖注入容器的区别在于更符合长生命周期的应用使用、提供了 [注解及注解注入](zh/annotation.md) 的支持、提供了无比强大的 [AOP 面向切面编程](zh/aop.md) 能力，这些能力及易用性作为 Hyperf 的核心输出，我们自信的认为该组件是最优秀的。

## 绑定对象关系

### 简单对象注入

通常来说，类的关系及注入是无需显性定义的，这一切 Hyperf 都会默默的为您完成，我们通过一些代码示例来说明一下相关的用法。      
假设我们需要在 `IndexController` 内调用 `UserService` 类的 `getInfoById(int $id)` 方法。
```php
<?php
namespace App\Service;

class UserService
{
    public function getInfoById(int $id)
    {
        // 我们假设存在一个 Info 实体
        return (new Info())->fill($id);    
    }
}
```

#### 通过构造方法注入

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
    
    // 通过在构造函数的参数上声明参数类型完成自动注入
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    
    public function index()
    {
        $id = 1;
        // 直接使用
        return $this->userService->getInfoById($id);    
    }
}
```

> 注意调用方也就是 `IndexController` 必须是由 DI 创建的对象才能完成自动注入，Controller 默认是由 DI 创建的

#### 通过 `@Inject` 注解注入

```php
<?php
namespace App\Controller;

use App\Service\UserService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;

class IndexController
{
    /**
     * 通过 `@Inject` 注解注入由 `@var` 注解声明的属性类型对象
     * 
     * @Inject 
     * @var UserService
     */
    private $userService;
    
    public function index()
    {
        $id = 1;
        // 直接使用
        return $this->userService->getInfoById($id);    
    }
}
```

> 注意调用方也就是 `IndexController` 必须是由 DI 创建的对象才能完成自动注入，Controller 默认是由 DI 创建的

### 抽象对象注入

基于上面的例子，从合理的角度上来说，Controller 面向的不应该直接是一个 `UserService` 类，可能更多的是一个 `UserServiceInterface` 的接口类，此时我们可以通过 `config/dependencies.php` 来绑定对象关系达到目的，我们还是通过代码来解释一下。

定义一个接口类：

```php
<?php
namespace App\Service;

interface UserServiceInterface
{
    public function getInfoById(int $id);
}
```

`UserService` 实现接口类：

```php
<?php
namespace App\Service;

class UserService implements UserServiceInterface
{
    public function getInfoById(int $id)
    {
        // 我们假设存在一个 Info 实体
        return (new Info())->fill($id);    
    }
}
```

在 `config/dependencies.php` 内完成关系配置：

```php
<?php
return [
    'dependencies' => [
        \App\Service\UserServiceInterface::class => \App\Service\UserService::class
    ],
];
```

这样配置后就可以直接通过 `UserServiceInterface` 来注入 `UserService` 对象了，我们仅通过注解注入的方式来举例，构造函数注入也是一样的：

```php
<?php
namespace App\Controller;

use App\Service\UserServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;

class IndexController
{
    /**
     * @Inject 
     * @var UserServiceInterface
     */
    private $userService;
    
    public function index()
    {
        $id = 1;
        // 直接使用
        return $this->userService->getInfoById($id);    
    }
}
```

### 工厂对象注入

我们假设 `UserService` 的实现会更加复杂一些，在创建 `UserService` 对象时构造函数还需要传递进来一些非直接注入型的参数，假设我们需要从配置中取得一个值，然后 `UserService` 需要根据这个值来决定是否开启缓存模式（顺带一说 Hyperf 提供了更好用的 [模型缓存](zh/db/model-cache.md) 功能）   

我们需要创建一个工厂来生成 `UserService` 对象：

```php
<?php 
namespace App\Service;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

class UserServiceFactory
{
    // 实现一个 __invoke() 方法来完成对象的生产，方法参数会自动注入一个当前的容器实例
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        // 我们假设对应的配置的 key 为 cache.enable
        $enableCache = $config->get('cache.enable', false);
        // make(string $name, array $parameters = []) 方法等同于 new ，使用 make() 方法是为了允许 AOP 的介入，而直接 new 会导致 AOP 无法正常介入流程
        return make(UserService::class, compact('enableCache'));
    }
}
```

`UserService` 也许在构造函数提供一个参数接收对应的值：

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
        // 接收值并储存于类属性中
        $this->enableCache = $enableCache;
    }
    
    public function getInfoById(int $id)
    {
        return (new Info())->fill($id);    
    }
}
```

在 `config/dependencies.php` 调整绑定关系：

```php
<?php
return [
    'dependencies' => [
        \App\Service\UserServiceInterface::class => \App\Service\UserServiceFactory::class
    ],
];
```

这样在注入 `UserServiceInterface` 的时候容器就会交由 `UserServiceFactory` 来创建对象了。

> 当然在该场景中可以通过 `@Value` 注解来更便捷的注入配置而无需构建工厂类，此仅为举例

## 注意事项

### 容器仅管理长生命周期的对象

换种方式理解就是容器内管理的对象**都是单例**，这样的设计对于长生命周期的应用来说会更加的高效，减少了大量无意义的对象创建和销毁，这样的设计也就意味着所有需要交由 DI 容器管理的对象**均不能包含** `状态` 值。   
`状态` 可直接理解为会随着请求而变化的值，事实上在 [协程](zh/coroutine.md) 编程中，这些状态值也是应该存放于 `协程上下文` 中的，即 `Hyperf\Utils\Context`。

## 短生命周期对象

通过 `new` 关键词创建的对象毫无疑问的短生命周期的，那么如果希望创建一个短生命周期的对象但又希望通过依赖注入容器注入相关的依赖呢？这是我们可以通过 `make(string $name, array $parameters = [])` 函数来创建 `$name` 对应的的实例，代码示例如下：   

```php
$userService = make(UserService::class, ['enableCache' => true]);
```

> 注意仅 `$name` 对应的对象为短生命周期对象，该对象的所有依赖都是通过 `get()` 方法获取的，即为长生命周期的对象