# 事件機制

## 前言

事件模式必須基於 [PSR-14](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-14-event-dispatcher.md) 去實現。   
Hyperf 的事件管理器默認由 [hyperf/event](https://github.com/hyperf/event) 實現，該組件亦可用於其它框架或應用，只需通過 Composer 將該組件引入即可。

```bash
composer require hyperf/event
```

## 概念

事件模式是一種經過了充分測試的可靠機制，是一種非常適用於解耦的機制，分別存在以下 3 種角色：

- `事件(Event)` 是傳遞於應用代碼與 `監聽器(Listener)` 之間的通訊對象
- `監聽器(Listener)` 是用於監聽 `事件(Event)` 的發生的監聽對象
- `事件調度器(EventDispatcher)` 是用於觸發 `事件(Event)` 和管理 `監聽器(Listener)` 與 `事件(Event)` 之間的關係的管理者對象

用通俗易懂的例子來説明就是，假設我們存在一個 `UserService::register()` 方法用於註冊一個賬號，在賬號註冊成功後我們可以通過事件調度器觸發 `UserRegistered` 事件，由監聽器監聽該事件的發生，在觸發時進行某些操作，比如發送用户註冊成功短信，在業務發展的同時我們可能會希望在用户註冊成功之後做更多的事情，比如發送用户註冊成功的郵件等待，此時我們就可以通過再增加一個監聽器監聽 `UserRegistered` 事件即可，無需在 `UserService::register()` 方法內部增加與之無關的代碼。

## 使用事件管理器

### 定義一個事件

一個事件其實就是一個用於管理狀態數據的普通類，觸發時將應用數據傳遞到事件裏，然後監聽器對事件類進行操作，一個事件可被多個監聽器監聽。

```php
<?php
namespace App\Event;

class UserRegistered
{
    // 建議這裏定義成 public 屬性，以便監聽器對該屬性的直接使用，或者你提供該屬性的 Getter
    public $user;
    
    public function __construct($user)
    {
        $this->user = $user;    
    }
}
```

### 定義一個監聽器

監聽器都需要實現一下 `Hyperf\Event\Contract\ListenerInterface` 接口的約束方法，示例如下。

```php
<?php
namespace App\Listener;

use App\Event\UserRegistered;
use Hyperf\Event\Contract\ListenerInterface;

class UserRegisteredListener implements ListenerInterface
{
    public function listen(): array
    {
        // 返回一個該監聽器要監聽的事件數組，可以同時監聽多個事件
        return [
            UserRegistered::class,
        ];
    }

    /**
     * @param UserRegistered $event
     */
    public function process(object $event)
    {
        // 事件觸發後該監聽器要執行的代碼寫在這裏，比如該示例下的發送用户註冊成功短信等
        // 直接訪問 $event 的 user 屬性獲得事件觸發時傳遞的參數值
        // $event->user;
        
    }
}
```

#### 通過配置文件註冊監聽器

在定義完監聽器之後，我們需要讓其能被 `事件調度器(Dispatcher)` 發現，可以在 `config/autoload/listeners.php` 配置文件 *（如不存在可自行創建）* 內添加該監聽器即可，監聽器的觸發順序根據該配置文件的配置順序:

```php
<?php
return [
    \App\Listener\UserRegisteredListener::class,
];
```

### 通過註解註冊監聽器

Hyperf 還提供了一種更加簡便的監聽器註冊方式，就是通過 `@Listener` 註解註冊，只要將該註解定義在監聽器類上，且監聽器類處於 `Hyperf 註解掃描域` 內即可自動完成註冊，代碼示例如下：

```php
<?php
namespace App\Listener;

use App\Event\UserRegistered;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

/**
 * @Listener 
 */
class UserRegisteredListener implements ListenerInterface
{
    public function listen(): array
    {
        // 返回一個該監聽器要監聽的事件數組，可以同時監聽多個事件
        return [
            UserRegistered::class,
        ];
    }

    /**
     * @param UserRegistered $event
     */
    public function process(object $event)
    {
        // 事件觸發後該監聽器要執行的代碼寫在這裏，比如該示例下的發送用户註冊成功短信等
        // 直接訪問 $event 的 user 屬性獲得事件觸發時傳遞的參數值
        // $event->user;
    }
}
```

在通過註解註冊監聽器時，我們可以通過設置 `priority` 屬性定義當前監聽器的順序，如 `@Listener(priority=1)` ，底層使用 `SplPriorityQueue` 結構儲存，`priority` 數字越大優先級越高。

> 使用 `@Listener` 註解時需 `use Hyperf\Event\Annotation\Listener;` 命名空間；  

### 觸發事件

事件需要通過 `事件調度器(EventDispatcher)` 調度才能讓 `監聽器(Listener)` 監聽到，我們通過一段代碼來演示如何觸發事件：

```php
<?php
namespace App\Service;

use Hyperf\Di\Annotation\Inject;
use Psr\EventDispatcher\EventDispatcherInterface;
use App\Event\UserRegistered; 

class UserService
{
    /**
     * @Inject 
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    
    public function register()
    {
        // 我們假設存在 User 這個實體
        $user = new User();
        $result = $user->save();
        // 完成賬號註冊的邏輯
        // 這裏 dispatch(object $event) 會逐個運行監聽該事件的監聽器
        $this->eventDispatcher->dispatch(new UserRegistered($user));
        return $result;
    }
}
```

## 注意事項

### 不要在 `Listener` 中注入 `EventDispatcherInterface`

因為 `EventDispatcherInterface` 依賴於 `ListenerProviderInterface`，而 `ListenerProviderInterface` 初始化的同時，會收集所有的 `Listener`。

而如果 `Listener` 又依賴了 `EventDispatcherInterface`，就會導致循壞依賴，進而導致內存溢出。

### 最好只在 `Listener` 中注入 `ContainerInterface`。

最好只在 `Listener` 中注入 `ContainerInterface`，而其他的組件在 `process` 中通過 `container` 獲取。

框架啟動開始時，會實例化 `EventDispatcherInterface`，如果 `Listener` 中注入了其他組件，可能會導致以下情況。

1. 這個時候還不是協程環境，如果 `Listener` 中注入了可能會觸發協程切換的類，就會導致框架啟動失敗。
2. 運行 `di:init-proxy` 腳本時，因為實例化了 `EventDispatcherInterface`，進而導致所有的 `Listener` 實例化，一旦這個過程生成了代理對象(.proxy.php 擴展名的類)，而腳本內部又有刪除代理類的邏輯，就會導致代理類生成有誤。
3. 條件與上述一致，只不過代理類又配置了別名，會導致生成這個別名對象時，因為判斷代理類不存在，則會重新生成，但又已經生成了 AST 語法樹，並被修改為代理類的 AST 語法樹（AST 註解樹內部節點為引用對象），則會導致代理類生成有誤。

> 上述兩個問題會在後面的版本修復，修改 `di:init-proxy` 腳本不再刪除緩存。

### `BootApplication` 事件儘量避免 IO 操作 

> `1.1.6` 版本及更新的版本已優化此問題

在 `1.1.6` 版本之前，因為 `BootApplication` 是在 `Command` 初始化 和 `Server` 啟動前觸發，所以當前環境一定是非協程環境，一旦使用了協程 `API`，則會導致啟動失敗。

