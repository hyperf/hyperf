# 依賴注入

## 簡介

Hyperf 默認採用 [hyperf/di](https://github.com/hyperf/di) 作為框架的依賴注入管理容器，儘管從設計上我們允許您更換其它的依賴注入管理容器，但我們強烈不建議您更換該組件。   
[hyperf/di](https://github.com/hyperf/di)
是一個強大的用於管理類的依賴關係並完成自動注入的組件，與傳統依賴注入容器的區別在於更符合長生命週期的應用使用、提供了 [註解及註解注入](zh-hk/annotation.md)
的支持、提供了無比強大的 [AOP 面向切面編程](zh-hk/aop.md) 能力，這些能力及易用性作為 Hyperf 的核心輸出，我們自信的認為該組件是最優秀的。

## 安裝

該組件默認存在 [hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) 項目中並作為主要組件存在，如希望在其它框架內使用該組件可通過下面的命令安裝。

```bash
composer require hyperf/di
```

## 綁定對象關係

### 簡單對象注入

通常來説，類的關係及注入是無需顯性定義的，這一切 Hyperf 都會默默的為您完成，我們通過一些代碼示例來説明一下相關的用法。      
假設我們需要在 `IndexController` 內調用 `UserService` 類的 `getInfoById(int $id)` 方法。

```php
<?php
namespace App\Service;

class UserService
{
    public function getInfoById(int $id)
    {
        // 我們假設存在一個 Info 實體
        return (new Info())->fill($id);    
    }
}
```

#### 通過構造方法注入

```php
<?php
namespace App\Controller;

use App\Service\UserService;

class IndexController
{
    private UserService $userService;
    
    // 通過在構造函數的參數上聲明參數類型完成自動注入
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

> 注意使用構造函數注入時，調用方也就是 `IndexController` 必須是由 DI 創建的對象才能完成自動注入，而 Controller 默認是由 DI 創建的，所以可以直接使用構造函數注入

當您希望定義一個可選的依賴項時，可以通過給參數定義為 `nullable` 或將參數的默認值定義為 `null`，即表示該參數如果在 DI 容器中沒有找到或無法創建對應的對象時，不拋出異常而是直接使用 `null` 來注入。

```php
<?php
namespace App\Controller;

use App\Service\UserService;

class IndexController
{
    private ?UserService $userService;
    
    // 通過設置參數為 nullable，表明該參數為一個可選參數
    public function __construct(?UserService $userService)
    {
        $this->userService = $userService;
    }
    
    public function index()
    {
        $id = 1;
        if ($this->userService instanceof UserService) {
            // 僅值存在時 $userService 可用
            return $this->userService->getInfoById($id);    
        }
        return null;
    }
}
```

#### 通過 `#[Inject]` 註解注入

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

> 通過 `#[Inject]` 註解注入可作用於 DI 創建的（單例）對象，也可作用於通過 `new` 關鍵詞創建的對象；

> 使用 `#[Inject]` 註解時需 `use Hyperf\Di\Annotation\Inject;` 命名空間；

##### Required 參數

`#[Inject]` 註解存在一個 `required` 參數，默認值為 `true`，當將該參數定義為 `false` 時，則表明該成員屬性為一個可選依賴，當對應 `@var` 的對象不存在於 DI
容器或不可創建時，將不會拋出異常而是注入一個 `null`，如下：

```php
<?php
namespace App\Controller;

use App\Service\UserService;
use Hyperf\Di\Annotation\Inject;

class IndexController
{
    /**
     * 通過 `#[Inject]` 註解注入由註解聲明的屬性類型對象
     * 當 UserService 不存在於 DI 容器內或不可創建時，則注入 null
     */
    #[Inject(required: false)]
    private ?UserService $userService;
    
    public function index()
    {
        $id = 1;
        if ($this->userService instanceof UserService) {
            // 僅值存在時 $userService 可用
            return $this->userService->getInfoById($id);    
        }
        return null;
    }
}
```

### 抽象對象注入

基於上面的例子，從合理的角度上來説，Controller 面向的不應該直接是一個 `UserService` 類，可能更多的是一個 `UserServiceInterface`
的接口類，此時我們可以通過 `config/autoload/dependencies.php` 來綁定對象關係達到目的，我們還是通過代碼來解釋一下。

定義一個接口類：

```php
<?php
namespace App\Service;

interface UserServiceInterface
{
    public function getInfoById(int $id);
}
```

`UserService` 實現接口類：

```php
<?php
namespace App\Service;

class UserService implements UserServiceInterface
{
    public function getInfoById(int $id)
    {
        // 我們假設存在一個 Info 實體
        return (new Info())->fill($id);    
    }
}
```

在 `config/autoload/dependencies.php` 內完成關係配置：

```php
<?php
return [
    \App\Service\UserServiceInterface::class => \App\Service\UserService::class
];
```

這樣配置後就可以直接通過 `UserServiceInterface` 來注入 `UserService` 對象了，我們僅通過註解注入的方式來舉例，構造函數注入也是一樣的：

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
        // 直接使用
        return $this->userService->getInfoById($id);    
    }
}
```

### 工廠對象注入

我們假設 `UserService` 的實現會更加複雜一些，在創建 `UserService` 對象時構造函數還需要傳遞進來一些非直接注入型的參數，假設我們需要從配置中取得一個值，然後 `UserService`
需要根據這個值來決定是否開啓緩存模式（順帶一説 Hyperf 提供了更好用的 [模型緩存](zh-hk/db/model-cache.md) 功能）

我們需要創建一個工廠來生成 `UserService` 對象：

```php
<?php 
namespace App\Service;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

class UserServiceFactory
{
    // 實現一個 __invoke() 方法來完成對象的生產，方法參數會自動注入一個當前的容器實例和一個參數數組
    public function __invoke(ContainerInterface $container, array $parameters = [])
    {
        $config = $container->get(ConfigInterface::class);
        // 我們假設對應的配置的 key 為 cache.enable
        $enableCache = $config->get('cache.enable', false);
        return make(UserService::class, compact('enableCache'));
    }
}
```

`UserService` 也可以在構造函數提供一個參數接收對應的值：

```php
<?php
namespace App\Service;

class UserService implements UserServiceInterface
{
    private bool $enableCache;
    
    public function __construct(bool $enableCache)
    {
        // 接收值並儲存於類屬性中
        $this->enableCache = $enableCache;
    }
    
    public function getInfoById(int $id)
    {
        return (new Info())->fill($id);    
    }
}
```

在 `config/autoload/dependencies.php` 調整綁定關係：

```php
<?php
return [
    \App\Service\UserServiceInterface::class => \App\Service\UserServiceFactory::class
];
```

這樣在注入 `UserServiceInterface` 的時候容器就會交由 `UserServiceFactory` 來創建對象了。

> 當然在該場景中可以通過 `#[Value]` 註解來更便捷的注入配置而無需構建工廠類，此僅為舉例

### 懶加載

Hyperf 的長生命週期依賴注入在項目啓動時完成。這意味着長生命週期的類需要注意：

* 構造函數時還不是協程環境，如果注入了可能會觸發協程切換的類，就會導致框架啓動失敗。

* 構造函數中要避免循環依賴（比較典型的例子為 `Listener` 和 `EventDispatcherInterface`），不然也會啓動失敗。

目前解決方案是：只在實例中注入 `Psr\Container\ContainerInterface` ，而其他的組件在非構造函數執行時通過 `container` 獲取。但 PSR-11 中指出:

> 「用户不應該將容器作為參數傳入對象然後在對象中通過容器獲得對象的依賴。這樣是把容器當作服務定位器來使用，而服務定位器是一種反模式」

也就是説這樣的做法雖然有效，但是從設計模式角度來説並不推薦。

另一個方案是使用 PHP 中常用的惰性代理模式，注入一個代理對象，在使用時再實例化目標對象。Hyperf DI 組件設計了懶加載注入功能。

添加 `config/lazy_loader.php` 文件並綁定懶加載關係：

```php
<?php
return [
    /**
     * 格式為：代理類名 => 原類名
     * 代理類此時是不存在的，Hyperf會在runtime文件夾下自動生成該類。
     * 代理類類名和命名空間可以自由定義。
     */
    'App\Service\LazyUserService' => \App\Service\UserServiceInterface::class
];
```

這樣在注入 `App\Service\LazyUserService` 的時候容器就會創建一個 `懶加載代理類` 注入到目標對象中了。

```php
use App\Service\LazyUserService;

class Foo{
    public $service;
    public function __construct(LazyUserService $service){
        $this->service = $service;
    }
}
````

您還可以通過註解 `#[Inject(lazy: true)]` 注入懶加載代理。通過註解實現懶加載不用創建配置文件。

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

注意：當該代理對象執行下列操作時，被代理對象才會從容器中真正實例化。

```php
// 方法調用
$proxy->someMethod();

// 讀取屬性
echo $proxy->someProperty;

// 寫入屬性
$proxy->someProperty = 'foo';

// 檢查屬性是否存在
isset($proxy->someProperty);

// 刪除屬性
unset($proxy->someProperty);
```

### 綁定權重

自 v3.0.17 版本開始，增加了權重功能。可以按照權重，注入權重最大的對象。例如下述兩份 `ConfigProvider` 配置

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

當不使用 `PriorityDefinition` 時，權重為 0。所以被綁定到 `FooInterface` 是 `Foo`。

## 短生命週期對象

通過 `new` 關鍵詞創建的對象毫無疑問的短生命週期的，那麼如果希望創建一個短生命週期的對象但又希望使用 `構造函數依賴自動注入功能`
呢？這時我們可以通過 `make(string $name, array $parameters = [])` 函數來創建 `$name` 對應的的實例，代碼示例如下：

```php
$userService = make(UserService::class, ['enableCache' => true]);
```

> 注意僅 `$name` 對應的對象為短生命週期對象，該對象的所有依賴都是通過 `get()` 方法獲取的，即為長生命週期的對象，可理解為該對象是一個淺拷貝的對象

## 獲取容器對象

有些時候我們可能希望去實現一些更動態的需求時，會希望可以直接獲取到 `容器(Container)` 對象，在絕大部分情況下，框架的入口類（比如命令類、控制器、RPC 服務提供者等）都是由 `容器(Container)`
創建並維護的，也就意味着您所寫的絕大部分業務代碼都是在 `容器(Container)` 的管理作用之下的，也就意味着在絕大部分情況下您都可以通過在 `構造函數(Constructor)` 聲明或通過 `#[Inject]`
註解注入 `Psr\Container\ContainerInterface` 接口類都能夠獲得 `Hyperf\Di\Container` 容器對象，我們通過代碼來演示一下：

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use Psr\Container\ContainerInterface;

class IndexController
{
    private ContainerInterface $container;
    
    // 通過在構造函數的參數上聲明參數類型完成自動注入
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
```   

在某些更極端動態的情況下，或者非 `容器(Container)` 的管理作用之下時，想要獲取到 `容器(Container)`
對象還可以通過 `\Hyperf\Context\ApplicationContext::getContainer()` 方法來獲得 `容器(Container)` 對象。

```php
$container = \Hyperf\Context\ApplicationContext::getContainer();
```

## 掃描適配器

默認使用 `Hyperf\Di\ScanHandler\PcntlScanHandler`.

- Hyperf\Di\ScanHandler\PcntlScanHandler

使用 Pcntl fork 子進程掃描註解，只支持 Linux 環境

- Hyperf\Di\ScanHandler\NullScanHandler

不進行註解掃描操作

- Hyperf\Di\ScanHandler\ProcScanHandler

使用 proc_open 創建子進程掃描註解，支持 Linux 和 Windows(Swow)

### 更換掃描適配器

我們只需要主動修改 `bin/hyperf.php` 文件中 `Hyperf\Di\ClassLoader::init()` 代碼段即可更換適配器。

```php
Hyperf\Di\ClassLoader::init(handler: new Hyperf\Di\ScanHandler\ProcScanHandler());
```

## 注意事項

### 容器僅管理長生命週期的對象

換種方式理解就是容器內管理的對象**都是單例**，這樣的設計對於長生命週期的應用來説會更加的高效，減少了大量無意義的對象創建和銷燬，這樣的設計也就意味着所有需要交由 DI 容器管理的對象**均不能包含** `狀態` 值。   
`狀態` 可直接理解為會隨着請求而變化的值，事實上在 [協程](zh-hk/coroutine.md) 編程中，這些狀態值也是應該存放於 `協程上下文` 中的，即 `Hyperf\Context\Context`。

### #[Inject] 注入覆蓋順序

`#[Inject]` 覆蓋順序為子類覆蓋 `Trait` 覆蓋 父類，即 下述 `Origin` 的 `foo` 變量為本身注入的 `Foo1`。

同理，假如 `Origin` 不存在變量 `$foo` 時，`$foo` 會被第一個 `Trait` 完成注入，注入類 `Foo2`。

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
