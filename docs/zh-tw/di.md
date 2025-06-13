# 依賴注入

## 簡介

Hyperf 預設採用 [hyperf/di](https://github.com/hyperf/di) 作為框架的依賴注入管理容器，儘管從設計上我們允許您更換其它的依賴注入管理容器，但我們強烈不建議您更換該元件。   
[hyperf/di](https://github.com/hyperf/di)
是一個強大的用於管理類的依賴關係並完成自動注入的元件，與傳統依賴注入容器的區別在於更符合長生命週期的應用使用、提供了 [註解及註解注入](zh-tw/annotation.md)
的支援、提供了無比強大的 [AOP 面向切面程式設計](zh-tw/aop.md) 能力，這些能力及易用性作為 Hyperf 的核心輸出，我們自信的認為該元件是最優秀的。

## 安裝

該元件預設存在 [hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) 專案中並作為主要元件存在，如希望在其它框架內使用該元件可透過下面的命令安裝。

```bash
composer require hyperf/di
```

## 繫結物件關係

### 簡單物件注入

通常來說，類的關係及注入是無需顯性定義的，這一切 Hyperf 都會默默的為您完成，我們透過一些程式碼示例來說明一下相關的用法。      
假設我們需要在 `IndexController` 內呼叫 `UserService` 類的 `getInfoById(int $id)` 方法。

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

#### 透過構造方法注入

```php
<?php
namespace App\Controller;

use App\Service\UserService;

class IndexController
{
    private UserService $userService;
    
    // 透過在建構函式的引數上宣告引數型別完成自動注入
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

> 注意使用建構函式注入時，呼叫方也就是 `IndexController` 必須是由 DI 建立的物件才能完成自動注入，而 Controller 預設是由 DI 建立的，所以可以直接使用建構函式注入

當您希望定義一個可選的依賴項時，可以透過給引數定義為 `nullable` 或將引數的預設值定義為 `null`，即表示該引數如果在 DI 容器中沒有找到或無法建立對應的物件時，不丟擲異常而是直接使用 `null` 來注入。

```php
<?php
namespace App\Controller;

use App\Service\UserService;

class IndexController
{
    private ?UserService $userService;
    
    // 透過設定引數為 nullable，表明該引數為一個可選引數
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

#### 透過 `#[Inject]` 註解注入

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

> 透過 `#[Inject]` 註解注入可作用於 DI 建立的（單例）物件，也可作用於透過 `new` 關鍵詞建立的物件；

> 使用 `#[Inject]` 註解時需 `use Hyperf\Di\Annotation\Inject;` 名稱空間；

##### Required 引數

`#[Inject]` 註解存在一個 `required` 引數，預設值為 `true`，當將該引數定義為 `false` 時，則表明該成員屬性為一個可選依賴，當對應 `@var` 的物件不存在於 DI
容器或不可建立時，將不會丟擲異常而是注入一個 `null`，如下：

```php
<?php
namespace App\Controller;

use App\Service\UserService;
use Hyperf\Di\Annotation\Inject;

class IndexController
{
    /**
     * 透過 `#[Inject]` 註解注入由註解宣告的屬性型別物件
     * 當 UserService 不存在於 DI 容器內或不可建立時，則注入 null
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

### 抽象物件注入

基於上面的例子，從合理的角度上來說，Controller 面向的不應該直接是一個 `UserService` 類，可能更多的是一個 `UserServiceInterface`
的介面類，此時我們可以透過 `config/autoload/dependencies.php` 來繫結物件關係達到目的，我們還是透過程式碼來解釋一下。

定義一個介面類：

```php
<?php
namespace App\Service;

interface UserServiceInterface
{
    public function getInfoById(int $id);
}
```

`UserService` 實現介面類：

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

這樣配置後就可以直接透過 `UserServiceInterface` 來注入 `UserService` 物件了，我們僅透過註解注入的方式來舉例，建構函式注入也是一樣的：

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

### 工廠物件注入

我們假設 `UserService` 的實現會更加複雜一些，在建立 `UserService` 物件時建構函式還需要傳遞進來一些非直接注入型的引數，假設我們需要從配置中取得一個值，然後 `UserService`
需要根據這個值來決定是否開啟快取模式（順帶一說 Hyperf 提供了更好用的 [模型快取](zh-tw/db/model-cache.md) 功能）

我們需要建立一個工廠來生成 `UserService` 物件：

```php
<?php 
namespace App\Service;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

class UserServiceFactory
{
    // 實現一個 __invoke() 方法來完成物件的生產，方法引數會自動注入一個當前的容器例項和一個引數陣列
    public function __invoke(ContainerInterface $container, array $parameters = [])
    {
        $config = $container->get(ConfigInterface::class);
        // 我們假設對應的配置的 key 為 cache.enable
        $enableCache = $config->get('cache.enable', false);
        return make(UserService::class, compact('enableCache'));
    }
}
```

`UserService` 也可以在建構函式提供一個引數接收對應的值：

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

在 `config/autoload/dependencies.php` 調整繫結關係：

```php
<?php
return [
    \App\Service\UserServiceInterface::class => \App\Service\UserServiceFactory::class
];
```

這樣在注入 `UserServiceInterface` 的時候容器就會交由 `UserServiceFactory` 來建立物件了。

> 當然在該場景中可以透過 `#[Value]` 註解來更便捷的注入配置而無需構建工廠類，此僅為舉例

### 懶載入

Hyperf 的長生命週期依賴注入在專案啟動時完成。這意味著長生命週期的類需要注意：

* 建構函式時還不是協程環境，如果注入了可能會觸發協程切換的類，就會導致框架啟動失敗。

* 建構函式中要避免迴圈依賴（比較典型的例子為 `Listener` 和 `EventDispatcherInterface`），不然也會啟動失敗。

目前解決方案是：只在例項中注入 `Psr\Container\ContainerInterface` ，而其他的元件在非建構函式執行時透過 `container` 獲取。但 PSR-11 中指出:

> 「使用者不應該將容器作為引數傳入物件然後在物件中透過容器獲得物件的依賴。這樣是把容器當作服務定位器來使用，而服務定位器是一種反模式」

也就是說這樣的做法雖然有效，但是從設計模式角度來說並不推薦。

另一個方案是使用 PHP 中常用的惰性代理模式，注入一個代理物件，在使用時再例項化目標物件。Hyperf DI 元件設計了懶載入注入功能。

新增 `config/lazy_loader.php` 檔案並繫結懶載入關係：

```php
<?php
return [
    /**
     * 格式為：代理類名 => 原類名
     * 代理類此時是不存在的，Hyperf會在runtime資料夾下自動生成該類。
     * 代理類類名和名稱空間可以自由定義。
     */
    'App\Service\LazyUserService' => \App\Service\UserServiceInterface::class
];
```

這樣在注入 `App\Service\LazyUserService` 的時候容器就會建立一個 `懶載入代理類` 注入到目標物件中了。

```php
use App\Service\LazyUserService;

class Foo{
    public $service;
    public function __construct(LazyUserService $service){
        $this->service = $service;
    }
}
````

您還可以透過註解 `#[Inject(lazy: true)]` 注入懶載入代理。透過註解實現懶載入不用建立配置檔案。

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

注意：當該代理物件執行下列操作時，被代理物件才會從容器中真正例項化。

```php
// 方法呼叫
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

### 繫結權重

自 v3.0.17 版本開始，增加了權重功能。可以按照權重，注入權重最大的物件。例如下述兩份 `ConfigProvider` 配置

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

當不使用 `PriorityDefinition` 時，權重為 0。所以被繫結到 `FooInterface` 是 `Foo`。

## 短生命週期物件

透過 `new` 關鍵詞建立的物件毫無疑問的短生命週期的，那麼如果希望建立一個短生命週期的物件但又希望使用 `建構函式依賴自動注入功能`
呢？這時我們可以透過 `make(string $name, array $parameters = [])` 函式來建立 `$name` 對應的的例項，程式碼示例如下：

```php
$userService = make(UserService::class, ['enableCache' => true]);
```

> 注意僅 `$name` 對應的物件為短生命週期物件，該物件的所有依賴都是透過 `get()` 方法獲取的，即為長生命週期的物件，可理解為該物件是一個淺複製的物件

## 獲取容器物件

有些時候我們可能希望去實現一些更動態的需求時，會希望可以直接獲取到 `容器(Container)` 物件，在絕大部分情況下，框架的入口類（比如命令類、控制器、RPC 服務提供者等）都是由 `容器(Container)`
建立並維護的，也就意味著您所寫的絕大部分業務程式碼都是在 `容器(Container)` 的管理作用之下的，也就意味著在絕大部分情況下您都可以透過在 `建構函式(Constructor)` 宣告或透過 `#[Inject]`
註解注入 `Psr\Container\ContainerInterface` 介面類都能夠獲得 `Hyperf\Di\Container` 容器物件，我們透過程式碼來演示一下：

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use Psr\Container\ContainerInterface;

class IndexController
{
    private ContainerInterface $container;
    
    // 透過在建構函式的引數上宣告引數型別完成自動注入
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
```   

在某些更極端動態的情況下，或者非 `容器(Container)` 的管理作用之下時，想要獲取到 `容器(Container)`
物件還可以透過 `\Hyperf\Context\ApplicationContext::getContainer()` 方法來獲得 `容器(Container)` 物件。

```php
$container = \Hyperf\Context\ApplicationContext::getContainer();
```

## 掃描介面卡

預設使用 `Hyperf\Di\ScanHandler\PcntlScanHandler`.

- Hyperf\Di\ScanHandler\PcntlScanHandler

使用 Pcntl fork 子程序掃描註解，只支援 Linux 環境

- Hyperf\Di\ScanHandler\NullScanHandler

不進行註解掃描操作

- Hyperf\Di\ScanHandler\ProcScanHandler

使用 proc_open 建立子程序掃描註解，支援 Linux 和 Windows(Swow)

### 更換掃描介面卡

我們只需要主動修改 `bin/hyperf.php` 檔案中 `Hyperf\Di\ClassLoader::init()` 程式碼段即可更換介面卡。

```php
Hyperf\Di\ClassLoader::init(handler: new Hyperf\Di\ScanHandler\ProcScanHandler());
```

## 注意事項

### 容器僅管理長生命週期的物件

換種方式理解就是容器內管理的物件**都是單例**，這樣的設計對於長生命週期的應用來說會更加的高效，減少了大量無意義的物件建立和銷燬，這樣的設計也就意味著所有需要交由 DI 容器管理的物件**均不能包含** `狀態` 值。   
`狀態` 可直接理解為會隨著請求而變化的值，事實上在 [協程](zh-tw/coroutine.md) 程式設計中，這些狀態值也是應該存放於 `協程上下文` 中的，即 `Hyperf\Context\Context`。

### #[Inject] 注入覆蓋順序

`#[Inject]` 覆蓋順序為子類覆蓋 `Trait` 覆蓋 父類，即 下述 `Origin` 的 `foo` 變數為本身注入的 `Foo1`。

同理，假如 `Origin` 不存在變數 `$foo` 時，`$foo` 會被第一個 `Trait` 完成注入，注入類 `Foo2`。

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
