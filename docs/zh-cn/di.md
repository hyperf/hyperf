# 依赖注入

## 简介

Hyperf 默认采用 [hyperf/di](https://github.com/hyperf/di) 作为框架的依赖注入管理容器，尽管从设计上我们允许您更换其它的依赖注入管理容器，但我们强烈不建议您更换该组件。   
[hyperf/di](https://github.com/hyperf/di)
是一个强大的用于管理类的依赖关系并完成自动注入的组件，与传统依赖注入容器的区别在于更符合长生命周期的应用使用、提供了 [注解及注解注入](zh-cn/annotation.md)
的支持、提供了无比强大的 [AOP 面向切面编程](zh-cn/aop.md) 能力，这些能力及易用性作为 Hyperf 的核心输出，我们自信的认为该组件是最优秀的。

## 安装

该组件默认存在 [hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) 项目中并作为主要组件存在，如希望在其它框架内使用该组件可通过下面的命令安装。

```bash
composer require hyperf/di
```

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

class IndexController
{
    private UserService $userService;
    
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

> 注意使用构造函数注入时，调用方也就是 `IndexController` 必须是由 DI 创建的对象才能完成自动注入，而 Controller 默认是由 DI 创建的，所以可以直接使用构造函数注入

当您希望定义一个可选的依赖项时，可以通过给参数定义为 `nullable` 或将参数的默认值定义为 `null`，即表示该参数如果在 DI 容器中没有找到或无法创建对应的对象时，不抛出异常而是直接使用 `null` 来注入。*(该功能仅在
1.1.0 或更高版本可用)*

```php
<?php
namespace App\Controller;

use App\Service\UserService;

class IndexController
{
    private ?UserService $userService;
    
    // 通过设置参数为 nullable，表明该参数为一个可选参数
    public function __construct(?UserService $userService)
    {
        $this->userService = $userService;
    }
    
    public function index()
    {
        $id = 1;
        if ($this->userService instanceof UserService) {
            // 仅值存在时 $userService 可用
            return $this->userService->getInfoById($id);    
        }
        return null;
    }
}
```

#### 通过 `#[Inject]` 注解注入

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
        // 直接使用
        return $this->userService->getInfoById($id);    
    }
}
```

> 通过 `#[Inject]` 注解注入可作用于 DI 创建的（单例）对象，也可作用于通过 `new` 关键词创建的对象；

> 使用 `#[Inject]` 注解时需 `use Hyperf\Di\Annotation\Inject;` 命名空间；

##### Required 参数

`#[Inject]` 注解存在一个 `required` 参数，默认值为 `true`，当将该参数定义为 `false` 时，则表明该成员属性为一个可选依赖，当对应 `@var` 的对象不存在于 DI
容器或不可创建时，将不会抛出异常而是注入一个 `null`，如下：

```php
<?php
namespace App\Controller;

use App\Service\UserService;
use Hyperf\Di\Annotation\Inject;

class IndexController
{
    /**
     * 通过 `#[Inject]` 注解注入由注解声明的属性类型对象
     * 当 UserService 不存在于 DI 容器内或不可创建时，则注入 null
     * 
     * @var UserService
     */
    #[Inject(required: false)]
    private $userService;
    
    public function index()
    {
        $id = 1;
        if ($this->userService instanceof UserService) {
            // 仅值存在时 $userService 可用
            return $this->userService->getInfoById($id);    
        }
        return null;
    }
}
```

### 抽象对象注入

基于上面的例子，从合理的角度上来说，Controller 面向的不应该直接是一个 `UserService` 类，可能更多的是一个 `UserServiceInterface`
的接口类，此时我们可以通过 `config/autoload/dependencies.php` 来绑定对象关系达到目的，我们还是通过代码来解释一下。

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

在 `config/autoload/dependencies.php` 内完成关系配置：

```php
<?php
return [
    \App\Service\UserServiceInterface::class => \App\Service\UserService::class
];
```

这样配置后就可以直接通过 `UserServiceInterface` 来注入 `UserService` 对象了，我们仅通过注解注入的方式来举例，构造函数注入也是一样的：

```php
<?php
namespace App\Controller;

use App\Service\UserServiceInterface;
use Hyperf\Di\Annotation\Inject;

class IndexController
{
    /**
     * @var UserServiceInterface
     */
    #[Inject]
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

我们假设 `UserService` 的实现会更加复杂一些，在创建 `UserService` 对象时构造函数还需要传递进来一些非直接注入型的参数，假设我们需要从配置中取得一个值，然后 `UserService`
需要根据这个值来决定是否开启缓存模式（顺带一说 Hyperf 提供了更好用的 [模型缓存](zh-cn/db/model-cache.md) 功能）

我们需要创建一个工厂来生成 `UserService` 对象：

```php
<?php 
namespace App\Service;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

class UserServiceFactory
{
    // 实现一个 __invoke() 方法来完成对象的生产，方法参数会自动注入一个当前的容器实例和一个参数数组
    public function __invoke(ContainerInterface $container, array $parameters = [])
    {
        $config = $container->get(ConfigInterface::class);
        // 我们假设对应的配置的 key 为 cache.enable
        $enableCache = $config->get('cache.enable', false);
        return make(UserService::class, compact('enableCache'));
    }
}
```

`UserService` 也可以在构造函数提供一个参数接收对应的值：

```php
<?php
namespace App\Service;

class UserService implements UserServiceInterface
{
    private bool $enableCache;
    
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

在 `config/autoload/dependencies.php` 调整绑定关系：

```php
<?php
return [
    \App\Service\UserServiceInterface::class => \App\Service\UserServiceFactory::class
];
```

这样在注入 `UserServiceInterface` 的时候容器就会交由 `UserServiceFactory` 来创建对象了。

> 当然在该场景中可以通过 `#[Value]` 注解来更便捷的注入配置而无需构建工厂类，此仅为举例

### 懒加载

Hyperf 的长生命周期依赖注入在项目启动时完成。这意味着长生命周期的类需要注意：

* 构造函数时还不是协程环境，如果注入了可能会触发协程切换的类，就会导致框架启动失败。

* 构造函数中要避免循环依赖（比较典型的例子为 `Listener` 和 `EventDispatcherInterface`），不然也会启动失败。

目前解决方案是：只在实例中注入 `Psr\Container\ContainerInterface` ，而其他的组件在非构造函数执行时通过 `container` 获取。但 PSR-11 中指出:

> 「用户不应该将容器作为参数传入对象然后在对象中通过容器获得对象的依赖。这样是把容器当作服务定位器来使用，而服务定位器是一种反模式」

也就是说这样的做法虽然有效，但是从设计模式角度来说并不推荐。

另一个方案是使用 PHP 中常用的惰性代理模式，注入一个代理对象，在使用时再实例化目标对象。Hyperf DI 组件设计了懒加载注入功能。

添加 `config/lazy_loader.php` 文件并绑定懒加载关系：

```php
<?php
return [
    /**
     * 格式为：代理类名 => 原类名
     * 代理类此时是不存在的，Hyperf会在runtime文件夹下自动生成该类。
     * 代理类类名和命名空间可以自由定义。
     */
    'App\Service\LazyUserService' => \App\Service\UserServiceInterface::class
];
```

这样在注入 `App\Service\LazyUserService` 的时候容器就会创建一个 `懒加载代理类` 注入到目标对象中了。

```php
use App\Service\LazyUserService;

class Foo{
    public $service;
    public function __construct(LazyUserService $service){
        $this->service = $service;
    }
}
````

您还可以通过注解 `#[Inject(lazy: true)]` 注入懒加载代理。通过注解实现懒加载不用创建配置文件。

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
````

注意：当该代理对象执行下列操作时，被代理对象才会从容器中真正实例化。

```php
// 方法调用
$proxy->someMethod();

// 读取属性
echo $proxy->someProperty;

// 写入属性
$proxy->someProperty = 'foo';

// 检查属性是否存在
isset($proxy->someProperty);

// 删除属性
unset($proxy->someProperty);
```

## 短生命周期对象

通过 `new` 关键词创建的对象毫无疑问的短生命周期的，那么如果希望创建一个短生命周期的对象但又希望使用 `构造函数依赖自动注入功能`
呢？这时我们可以通过 `make(string $name, array $parameters = [])` 函数来创建 `$name` 对应的的实例，代码示例如下：

```php
$userService = make(UserService::class, ['enableCache' => true]);
```

> 注意仅 `$name` 对应的对象为短生命周期对象，该对象的所有依赖都是通过 `get()` 方法获取的，即为长生命周期的对象，可理解为该对象是一个浅拷贝的对象

## 获取容器对象

有些时候我们可能希望去实现一些更动态的需求时，会希望可以直接获取到 `容器(Container)` 对象，在绝大部分情况下，框架的入口类（比如命令类、控制器、RPC 服务提供者等）都是由 `容器(Container)`
创建并维护的，也就意味着您所写的绝大部分业务代码都是在 `容器(Container)` 的管理作用之下的，也就意味着在绝大部分情况下您都可以通过在 `构造函数(Constructor)` 声明或通过 `#[Inject]`
注解注入 `Psr\Container\ContainerInterface` 接口类都能够获得 `Hyperf\Di\Container` 容器对象，我们通过代码来演示一下：

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use Psr\Container\ContainerInterface;

class IndexController
{
    private ContainerInterface $container;
    
    // 通过在构造函数的参数上声明参数类型完成自动注入
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
```   

在某些更极端动态的情况下，或者非 `容器(Container)` 的管理作用之下时，想要获取到 `容器(Container)`
对象还可以通过 `\Hyperf\Context\ApplicationContext::getContaienr()` 方法来获得 `容器(Container)` 对象。

```php
$container = \Hyperf\Context\ApplicationContext::getContainer();
```

## 扫描适配器

默认使用 `Hyperf\Di\ScanHandler\PcntlScanHandler`.

- Hyperf\Di\ScanHandler\PcntlScanHandler

使用 Pcntl fork 子进程扫描注解，只支持 Linux 环境

- Hyperf\Di\ScanHandler\NullScanHandler

不进行注解扫描操作

- Hyperf\Di\ScanHandler\ProcScanHandler

使用 proc_open 创建子进程扫描注解，支持 Linux 和 Windows(Swow)

### 更换扫描适配器

我们只需要主动修改 `bin/hyperf.php` 文件中 `Hyperf\Di\ClassLoader::init()` 代码段即可更换适配器。

```php
Hyperf\Di\ClassLoader::init(handler: new Hyperf\Di\ScanHandler\ProcScanHandler());
```

## 注意事项

### 容器仅管理长生命周期的对象

换种方式理解就是容器内管理的对象**都是单例**，这样的设计对于长生命周期的应用来说会更加的高效，减少了大量无意义的对象创建和销毁，这样的设计也就意味着所有需要交由 DI 容器管理的对象**均不能包含** `状态` 值。   
`状态` 可直接理解为会随着请求而变化的值，事实上在 [协程](zh-cn/coroutine.md) 编程中，这些状态值也是应该存放于 `协程上下文` 中的，即 `Hyperf\Context\Context`。

### #[Inject] 注入覆盖顺序

`#[Inject]` 覆盖顺序为子类覆盖 `Trait` 覆盖 父类，即 下述 `Origin` 的 `foo` 变量为本身注入的 `Foo1`。

同理，假如 `Origin` 不存在变量 `$foo` 时，`$foo` 会被第一个 `Trait` 完成注入，注入类 `Foo2`。

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
