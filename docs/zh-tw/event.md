# 事件機制

## 前言

事件模式必須基於 [PSR-14](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-14-event-dispatcher.md) 去實現。   
Hyperf 的事件管理器預設由 [hyperf/event](https://github.com/hyperf/event) 實現，該元件亦可用於其它框架或應用，只需透過 Composer 將該元件引入即可。

```bash
composer require hyperf/event
```

## 概念

事件模式是一種經過了充分測試的可靠機制，是一種非常適用於解耦的機制，分別存在以下 3 種角色：

- `事件(Event)` 是傳遞於應用程式碼與 `監聽器(Listener)` 之間的通訊物件
- `監聽器(Listener)` 是用於監聽 `事件(Event)` 的發生的監聽物件
- `事件排程器(EventDispatcher)` 是用於觸發 `事件(Event)` 和管理 `監聽器(Listener)` 與 `事件(Event)` 之間的關係的管理者物件

用通俗易懂的例子來說明就是，假設我們存在一個 `UserService::register()` 方法用於註冊一個賬號，在賬號註冊成功後我們可以透過事件排程器觸發 `UserRegistered` 事件，由監聽器監聽該事件的發生，在觸發時進行某些操作，比如傳送使用者註冊成功簡訊，在業務發展的同時我們可能會希望在使用者註冊成功之後做更多的事情，比如傳送使用者註冊成功的郵件等等，此時我們就可以透過再增加一個監聽器監聽 `UserRegistered` 事件即可，無需在 `UserService::register()` 方法內部增加與之無關的程式碼。

## 使用事件管理器

> 接下來我們會透過配置和註解兩種方式介紹監聽器，實際使用時，二者只需使用其一即可，如果既有註解又有配置，則會造成監聽器被多次觸發。

### 定義一個事件

一個事件其實就是一個用於管理狀態資料的普通類，觸發時將應用資料傳遞到事件裡，然後監聽器對事件類進行操作，一個事件可被多個監聽器監聽。

```php
<?php
namespace App\Event;

class UserRegistered
{
    // 建議這裡定義成 public 屬性，以便監聽器對該屬性的直接使用，或者你提供該屬性的 Getter
    public $user;
    
    public function __construct($user)
    {
        $this->user = $user;    
    }
}
```

### 定義一個監聽器

監聽器都需要實現一下 `Hyperf\Event\Contract\ListenerInterface` 介面的約束方法，示例如下。

```php
<?php
namespace App\Listener;

use App\Event\UserRegistered;
use Hyperf\Event\Contract\ListenerInterface;

class UserRegisteredListener implements ListenerInterface
{
    public function listen(): array
    {
        // 返回一個該監聽器要監聽的事件陣列，可以同時監聽多個事件
        return [
            UserRegistered::class,
        ];
    }

    /**
     * @param UserRegistered $event
     */
    public function process(object $event): void
    {
        // 事件觸發後該監聽器要執行的程式碼寫在這裡，比如該示例下的傳送使用者註冊成功簡訊等
        // 直接訪問 $event 的 user 屬性獲得事件觸發時傳遞的引數值
        // $event->user;
        
    }
}
```

#### 透過配置檔案註冊監聽器

在定義完監聽器之後，我們需要讓其能被 `事件排程器(Dispatcher)` 發現，可以在 `config/autoload/listeners.php` 配置檔案 *（如不存在可自行建立）* 內新增該監聽器即可，監聽器的觸發順序根據該配置檔案的配置順序:

```php
<?php
return [
    \App\Listener\UserRegisteredListener::class,
];
```

### 透過註解註冊監聽器

Hyperf 還提供了一種更加簡便的監聽器註冊方式，就是透過 `#[Listener]` 註解註冊，只要將該註解定義在監聽器類上，且監聽器類處於 `Hyperf 註解掃描域` 內即可自動完成註冊，程式碼示例如下：

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
        // 返回一個該監聽器要監聽的事件陣列，可以同時監聽多個事件
        return [
            UserRegistered::class,
        ];
    }

    /**
     * @param UserRegistered $event
     */
    public function process(object $event): void
    {
        // 事件觸發後該監聽器要執行的程式碼寫在這裡，比如該示例下的傳送使用者註冊成功簡訊等
        // 直接訪問 $event 的 user 屬性獲得事件觸發時傳遞的引數值
        // $event->user;
    }
}
```

在透過註解註冊監聽器時，我們可以透過設定 `priority` 屬性定義當前監聽器的順序，如 `#[Listener(priority=1)]` ，底層使用 `SplPriorityQueue` 結構儲存，`priority` 數字越大優先順序越高。

> 使用 `#[Listener]` 註解時需 `use Hyperf\Event\Annotation\Listener;` 名稱空間；  

### 觸發事件

事件需要透過 `事件排程器(EventDispatcher)` 排程才能讓 `監聽器(Listener)` 監聽到，我們透過一段程式碼來演示如何觸發事件：

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
        // 我們假設存在 User 這個實體
        $user = new User();
        $result = $user->save();
        // 完成賬號註冊的邏輯
        // 這裡 dispatch(object $event) 會逐個執行監聽該事件的監聽器
        $this->eventDispatcher->dispatch(new UserRegistered($user));
        return $result;
    }
}
```

## Hyperf 生命週期事件

![](imgs/hyperf-events.svg)

## Hyperf 協程風格生命週期事件

![](https://raw.githubusercontent.com/hyperf/raw-storage/main/hyperf/svg/hyperf-coroutine-events.svg)

## 注意事項

### 不要在 `Listener` 中注入 `EventDispatcherInterface`

因為 `EventDispatcherInterface` 依賴於 `ListenerProviderInterface`，而 `ListenerProviderInterface` 初始化的同時，會收集所有的 `Listener`。

而如果 `Listener` 又依賴了 `EventDispatcherInterface`，就會導致循壞依賴，進而導致記憶體溢位。

### 最好只在 `Listener` 中注入 `ContainerInterface`。

最好只在 `Listener` 中注入 `ContainerInterface`，而其他的元件在 `process` 中透過 `container` 獲取。框架啟動開始時，會例項化 `EventDispatcherInterface`，這個時候還不是協程環境，如果 `Listener` 中注入了可能會觸發協程切換的類，就會導致框架啟動失敗。
