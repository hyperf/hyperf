# 協程

## 概念

Hyperf 是運行於 `Swoole 5` 的協程和 `Swow` 協程之上的，這也是 Hyperf 能提供高性能的其中一個很大的因素。

### PHP-FPM 的運作模式

在聊協程是什麼之前，我們先聊聊傳統 `PHP-FPM` 架構的運作模式，`PHP-FPM` 是一個多進程的 `FastCGI` 管理程序，是絕大多數 `PHP` 應用所使用的運行模式。假設我們使用 `Nginx` 提供 `HTTP` 服務（`Apache` 同理），所有客户端發起的請求最先抵達的都是 `Nginx`，然後 `Nginx` 通過 `FastCGI` 協議將請求轉發給 `PHP-FPM` 處理，`PHP-FPM` 的 `Worker 進程` 會搶佔式的獲得 CGI 請求進行處理，這個處理指的就是，等待 `PHP` 腳本的解析，等待業務處理的結果返回，完成後回收子進程，這整個的過程是阻塞等待的，也就意味着 `PHP-FPM` 的進程數有多少能處理的請求也就是多少，假設 `PHP-FPM` 有 `200` 個 `Worker 進程`，一個請求將耗費 `1` 秒的時間，那麼簡單的來説整個服務器理論上最多可以處理的請求也就是 `200` 個，`QPS` 即為 `200/s`，在高併發的場景下，這樣的性能往往是不夠的，儘管可以利用 `Nginx` 作為負載均衡配合多台 `PHP-FPM` 服務器來提供服務，但由於 `PHP-FPM` 的阻塞等待的工作模型，一個請求會佔用至少一個 `MySQL` 連接，多節點高併發下會產生大量的 `MySQL` 連接，而 `MySQL` 的最大連接數默認值為 `100`，儘管可以修改，但顯而易見該模式沒法很好的應對高併發的場景。

### 異步非阻塞系統

在高併發的場景下，異步非阻塞就顯得優勢明顯了，直觀的優點表現就是 `Worker 進程` 不再同步阻塞的去處理一個請求，而是可以同時處理多個請求，無需 `I/O` 等待，併發能力極強，可以同時發起或維護大量的請求。那麼最直觀的缺點大家可能也都知道，就是永無止境的回調，業務邏輯必須在對應的回調函數內實現，如果業務邏輯存在多次的 `I/O` 請求，則會存在很多層的回調函數，下面示例一段 `Swoole 1.x` 下的異步回調型的偽代碼片段。

```php
$db = new swoole_mysql();
$config = array(
    'host' => '127.0.0.1',
    'port' => 3306,
    'user' => 'test',
    'password' => 'test',
    'database' => 'test',
);

$db->connect($config, function ($db, $r) {
    // 從 users 表中查詢一條數據
    $sql = 'select * from users where id = 1';
    $db->query($sql, function(swoole_mysql $db, $r) {
        if ($r !== false) {
            // 查詢成功後修改一條數據
            $updateSql = 'update users set name="new name" where id = 1';
            $db->query($updateSql, function (swoole_mysql $db, $r) {
                $rows = $db->affected_rows;
                if ($r === true) {
                    return $this->response->end('更新成功');
                }
            });
        }
        $db->close();
    });
});
```

> 注意 `MySQL` 等異步模塊已在[4.3.0](https://wiki.swoole.com/#/version/bc?id=430)中移除，並轉移到了[swoole_async](https://github.com/swoole/ext-async)。

從上面的代碼片段可以看出，每一個操作幾乎就需要一個回調函數，在複雜的業務場景中回調的層次感和代碼結構絕對會讓你崩潰，其實不難看出這樣的寫法有點類似 `JavaScript` 上的異步方法的寫法，而 `JavaScript` 也為此提供了不少的解決方案（當然方案是源於其它編程語言），如 `Promise`，`yield + generator`, `async/await`，`Promise` 則是對回調的一種封裝方式，而 `yield + generator` 和 `async/await` 則需要在代碼上顯性的增加一些代碼語法標記，這些相對比回調函數來説，不妨都是一些非常不錯的解決方案，但是你需要另花時間來理解它的實現機制和語法。   
`Swoole` 協程也是對異步回調的一種解決方案，在 `PHP` 語言下，`Swoole` 協程與 `yield + generator` 都屬於協程的解決方案，協程的解決方案可以使代碼以近乎於同步代碼的書寫方式來書寫異步代碼，顯性的區別則是 `yield + generator` 的協程機制下，每一處 `I/O` 操作的調用代碼都需要在前面加上 `yield` 語法實現協程切換，每一層調用都需要加上，否則會出現意料之外的錯誤，而 `Swoole` 協程的解決方案對比於此就高明多了，在遇到 `I/O` 時底層自動的進行隱式協程切換，無需添加任何的額外語法，無需在代碼前加上 `yield`，協程切換的過程無聲無息，極大的減輕了維護異步系統的心智負擔。

### 協程是什麼？

我們已經知道了協程可以很好的解決異步非阻塞系統的開發問題，那麼協程本身到底是什麼呢？從定義上來説，**協程是一種輕量級的線程，由用户代碼來調度和管理，而不是由操作系統內核來進行調度，也就是在用户態進行**。可以直接的理解為就是一個非標準的線程實現，但什麼時候切換由用户自己來實現，而不是由操作系統分配 `CPU` 時間決定。具體來説，`Swoole` 的每個 `Worker 進程` 會存在一個協程調度器來調度協程，協程切換的時機就是遇到 `I/O` 操作或代碼顯性切換時，進程內以單線程的形式運行協程，也就意味着一個進程內同一時間只會有一個協程在運行且切換時機明確，也就無需處理像多線程編程下的各種同步鎖的問題。   
單個協程內的代碼運行仍是串行的，放在一個 HTTP 協程服務上來理解就是每一個請求是一個協程，舉個例子，假設為 `請求 A` 創建了 `協程 A`，為 `請求 B` 創建了 `協程 B`，那麼在處理 `協程 A` 的時候代碼跑到了查詢 `MySQL` 的語句上，這個時候 `協程 A` 則會觸發協程切換，`協程 A` 就繼續等待 `I/O` 設備返回結果，那麼此時就會切換到 `協程 B`，開始處理 `協程 B` 的邏輯，當又遇到了一個 `I/O` 操作便又觸發協程切換，再回過來從 `協程 A` 剛才切走的地方繼續執行，如此反覆，遇到 `I/O` 操作就切換到另一個協程去繼續執行而非一直阻塞等待。   
這裏可以發現一個問題就是：**在 `協程 A` 中的 `MySQL` 查詢操作必須得是一個異步非阻塞的操作，否則會由於阻塞導致協程調度器沒法切換到另一個協程繼續執行**，這個也是要在協程編程下需要規避的問題之一。

### 協程與普通線程有哪些區別？

都説協程是一個輕量級的線程，協程和線程都適用於多任務的場景下，從這個角度上來説，協程與線程很相似，都有自己的上下文，可以共享全局變量，但不同之處在於，在同一時間可以有多個線程處於運行狀態，但對於 `Swoole` 協程來説只能有一個，其它的協程都會處於暫停的狀態。此外，普通線程是搶佔式的，哪個線程能得到資源由操作系統決定，而協程是協作式的，執行權由用户態自行分配。

## 協程編程注意事項

### 不能存在阻塞代碼

協程內代碼的阻塞會導致協程調度器無法切換到另一個協程繼續執行代碼，所以我們絕不能在協程內存在阻塞代碼，假設我們啓動了 `4` 個 `Worker` 來處理 `HTTP` 請求（通常啓動的 `Worker` 數量與 `CPU` 核心數一致或 `2` 倍），如果代碼中存在阻塞，暫且理論的認為每個請求都會阻塞 `1` 秒，那麼系統的 `QPS` 也將退化為 `4/s` ，這無疑就是退化成了與 `PHP-FPM` 類似的情況，所以我們絕對不能在協程中存在阻塞代碼。   

那麼到底哪些是阻塞代碼呢？我們可以簡單的認為絕大多數你所熟知的非 `Swoole` 提供的異步函數的 `MySQL`、`Redis`、`Memcache`、`MongoDB`、`HTTP`、`Socket`等客户端，文件操作、`sleep/usleep` 等均為阻塞函數，這幾乎涵蓋了所有日常操作，那麼要如何解決呢？`Swoole` 提供了 `MySQL`、`PostgreSQL`、`Redis`、`HTTP`、`Socket` 的協程客户端可以使用，同時 `Swoole 4.1` 之後提供了一鍵協程化的方法 `\Swoole\Runtime::enableCoroutine()`，只需在使用協程前運行這一行代碼，`Swoole` 會將 所有使用 `php_stream` 進行 `socket` 操作均變成協程調度的異步 `I/O`，可以理解為除了 `curl` 絕大部分原生的操作都可以適用，關於此部分可查閲 [Swoole 文檔](https://wiki.swoole.com/#/runtime) 獲得更具體的信息。  

在 `Hyperf` 中我們已經為您處理好了這一切，您只需關注 `\Swoole\Runtime::enableCoroutine()` 仍無法協程化的阻塞代碼即可。

### 不能通過全局變量儲存狀態

在 `Swoole` 的持久化應用下，一個 `Worker` 內的全局變量是 `Worker` 內共享的，而從協程的介紹我們可以知道同一個 `Worker` 內還會存在多個協程並存在協程切換，也就意味着一個 `Worker` 會在一個時間週期內同時處理多個協程（或直接理解為請求）的代碼，也就意味着如果使用了全局變量來儲存狀態可能會被多個協程所使用，也就是説不同的請求之間可能會混淆數據，這裏的全局變量指的是 `$_GET/$_POST/$_REQUEST/$_SESSION/$_COOKIE/$_SERVER`等`$_`開頭的變量、`global` 變量，以及 `static` 靜態屬性。    
那麼當我們需要使用到這些特性時應該怎麼辦？   

對於全局變量，均是跟隨着一個 `請求(Request)` 而產生的，而 `Hyperf` 的 `請求(Request)/響應(Response)` 是由 [hyperf/http-message](https://github.com/hyperf/http-message) 通過實現 [PSR-7](https://www.php-fig.org/psr/psr-7/) 處理的，故所有的全局變量均可以在 `請求(Request)` 對象中得到相關的值；   

對於 `global` 變量和 `static` 變量，在 `PHP-FPM` 模式下，本質都是存活於一個請求生命週期內的，而在 `Hyperf` 內因為是 `CLI` 應用，會存在 `全局週期` 和 `請求週期(協程週期)` 兩種長生命週期。   
- 全局週期，我們只需要創建一個靜態變量供全局調用即可，靜態變量意味着在服務啓動後，任意協程和代碼邏輯均共享此靜態變量內的數據，也就意味着存放的數據不能是特別服務於某一個請求或某一個協程；
- 協程週期，由於 `Hyperf` 會為每個請求自動創建一個協程來處理，那麼一個協程週期在此也可以理解為一個請求週期，在協程內，所有的狀態數據均應存放於 `Hyperf\Context\Context` 類中，通過該類的 `get`、`set` 來讀取和存儲任意結構的數據，這個 `Context(協程上下文)` 類在執行任意協程時讀取或存儲的數據都是僅限對應的協程的，同時在協程結束時也會自動銷燬相關的上下文數據。

### 最大協程數限制

對 `Swoole Server` 通過 `set` 方法設置 `max_coroutine` 參數，用於配置一個 `Worker` 進程最多可存在的協程數量。因為隨着 `Worker` 進程處理的協程數目的增加，其對應占用的內存也會隨之增加，為了避免超出 `PHP` 的 `memory_limit` 限制，請根據實際業務的壓測結果設置該值，`Swoole` 的默認值為 `100000`（ `Swoole` 版本小於 `v4.4.0-beta` 時默認值為 `3000` ）, 在 `hyperf-skeleton` 項目中默認設置為 `100000`。

## 使用協程

### 創建一個協程

只需通過 `co(callable $callable)` 或 `go(callable $callable)` 函數或 `Hyperf\Coroutine\Coroutine::create(callable $callable)` 即可創建一個協程，協程內可以使用協程相關的方法和客户端。

### 判斷當前是否處於協程環境內

在一些情況下我們希望判斷一些當前是否運行於協程環境內，對於一些兼容協程環境與非協程環境的代碼來説會作為一個判斷的依據，我們可以通過 `Hyperf\Coroutine\Coroutine::inCoroutine(): bool` 方法來得到結果。

### 獲得當前協程的 ID

在一些情況下，我們需要根據 `協程 ID` 去做一些邏輯，比如 `協程上下文` 之類的邏輯，可以通過 `Hyperf\Coroutine\Coroutine::id(): int` 獲得當前的 `協程 ID`，如不處於協程環境下，會返回 `-1`。

### Channel 通道

類似於 `Go` 語言的 `chan`，`Channel` 可為多生產者協程和多消費者協程模式提供支持。底層自動實現了協程的切換和調度。 `Channel` 與 `PHP` 的數組類似，僅佔用內存，沒有其他額外的資源申請，所有操作均為內存操作，無 `I/O` 消耗，使用方法與 `SplQueue` 隊列類似。   
`Channel` 主要用於協程間通訊，當我們希望從一個協程裏返回一些數據到另一個協程時，就可通過 `Channel` 來進行傳遞。   

主要方法：   
- `Channel->push` ：當隊列中有其他協程正在等待 `pop` 數據時，自動按順序喚醒一個消費者協程。當隊列已滿時自動 `yield` 讓出控制權，等待其他協程消費數據
- `Channel->pop` ：當隊列為空時自動 `yield`，等待其他協程生產數據。消費數據後，隊列可寫入新的數據，自動按順序喚醒一個生產者協程。

下面是一個協程間通訊的簡單例子:

```php
<?php
co(function () {
    $channel = new \Swoole\Coroutine\Channel();
    co(function () use ($channel) {
        $channel->push('data');
    });
    $data = $channel->pop();
});
```

### Defer 特性

當我們希望在協程結束時運行一些代碼時，可以通過 `defer(callable $callable)` 函數或 `Hyperf\Coroutine::defer(callable $callable)` 將一段函數以 `棧(stack)` 的形式儲存起來，`棧(stack)` 內的函數會在當前協程結束時以 `先進後出` 的流程逐個執行。

### WaitGroup 特性

`WaitGroup` 是基於 `Channel` 衍生出來的一個特性，如果接觸過 `Go` 語言，我們都會知道 `WaitGroup` 這一特性，在 `Hyperf` 裏，`WaitGroup` 的用途是使得主協程一直阻塞等待直到所有相關的子協程都已經完成了任務後再繼續運行，這裏説到的阻塞等待是僅對於主協程（即當前協程）來説的，並不會阻塞當前進程。      
我們通過一段代碼來演示該特性：   

```php
<?php
$wg = new \Hyperf\Coroutine\WaitGroup();
// 計數器加二
$wg->add(2);
// 創建協程 A
co(function () use ($wg) {
    // some code
    // 計數器減一
    $wg->done();
});
// 創建協程 B
co(function () use ($wg) {
    // some code
    // 計數器減一
    $wg->done();
});
// 等待協程 A 和協程 B 運行完成
$wg->wait();
```

> 注意 `WaitGroup` 本身也需要在協程內才能使用

### Parallel 特性

`Parallel` 特性是 Hyperf 基於 `WaitGroup` 特性抽象出來的一個更便捷的使用方法，我們通過一段代碼來演示一下。

```php
<?php
use Hyperf\Coroutine\Exception\ParallelExecutionException;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Coroutine\Parallel;

$parallel = new Parallel();
$parallel->add(function () {
    sleep(1);
    return Coroutine::id();
});
$parallel->add(function () {
    sleep(1);
    return Coroutine::id();
});

try{
    // $results 結果為 [1, 2]
   $results = $parallel->wait(); 
} catch(ParallelExecutionException $e){
    // $e->getResults() 獲取協程中的返回值。
    // $e->getThrowables() 獲取協程中出現的異常。
}
```
> 注意 `Hyperf\Coroutine\Exception\ParallelExecutionException` 異常僅在 1.1.6 版本和更新的版本下會拋出

通過上面的代碼我們可以看到僅花了 `1` 秒就得到了兩個不同的協程的 `ID`，在調用 `add(callable $callable)` 的時候 `Parallel` 類會為之自動創建一個協程，並加入到 `WaitGroup` 的調度去。    
不僅如此，我們還可以通過 `parallel(array $callables)` 函數進行更進一步的簡化上面的代碼，達到同樣的目的，下面為簡化後的代碼。

```php
<?php
use Hyperf\Coroutine\Coroutine;

// 傳遞的數組參數您也可以帶上 key 便於區分子協程，返回的結果也會根據 key 返回對應的結果
$result = parallel([
    function () {
        sleep(1);
        return Coroutine::id();
    },
    function () {
        sleep(1);
        return Coroutine::id();
    }
]);
```

> 注意 `Parallel` 本身也需要在協程內才能使用

#### 限制 Parallel 最大同時運行的協程數

當我們添加到 `Parallel` 裏的任務有很多時，假設都是一些請求任務，那麼一瞬間發出全部請求很有可能會導致對端服務因為一瞬間接收到了大量的請求而處理不過來，有宕機的風險，所以需要對對端進行適當的保護，但我們又希望可以通過 `Parallel` 機制來加速這些請求的耗時，那麼可以通過在實例化 `Parallel` 對象時傳遞第一個參數，來設置最大運行的協程數，比如我們希望最大設置的協程數為 `5` ，也就意味着 `Parallel` 裏最多隻會有 `5` 個協程在運行，只有當 `5` 個裏有協程完成結束後，後續的協程才會繼續啓動，直至所有協程完成任務，示例代碼如下：

```php
use Hyperf\Coroutine\Exception\ParallelExecutionException;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Coroutine\Parallel;

$parallel = new Parallel(5);
for ($i = 0; $i < 20; $i++) {
    $parallel->add(function () {
        sleep(1);
        return Coroutine::id();
    });
} 

try{
   $results = $parallel->wait(); 
} catch(ParallelExecutionException $e){
    // $e->getResults() 獲取協程中的返回值。
    // $e->getThrowables() 獲取協程中出現的異常。
}
```

### Concurrent 協程運行控制

`Hyperf\Coroutine\Concurrent` 基於 `Swoole\Coroutine\Channel` 實現，用來控制一個代碼塊內同時運行的最大協程數量的特性。

以下樣例，當同時執行 `10` 個子協程時，會在循環中阻塞，但只會阻塞當前協程，直到釋放出一個位置後，循環繼續執行下一個子協程。

```php
<?php

use Hyperf\Coroutine\Concurrent;

$concurrent = new Concurrent(10);

for ($i = 0; $i < 15; ++$i) {
    $concurrent->create(function () {
        // Do something...
    });
}
```

### 協程上下文

由於同一個進程內協程間是內存共享的，但協程的執行/切換是非順序的，也就意味着我們很難掌控當前的協程是哪一個**（事實上可以，但通常沒人這麼幹）**，所以我們需要在發生協程切換時能夠同時切換對應的上下文。
在 `Hyperf` 裏實現協程的上下文管理將非常簡單，基於 `Hyperf\Context\Context` 類的 `set(string $id, $value)`、`get(string $id, $default = null)`、`has(string $id)`、`override(string $id, \Closure $closure)` 靜態方法即可完成上下文數據的管理，通過這些方法設置和獲取的值，都僅限於當前的協程，在協程結束時，對應的上下文也會自動跟隨釋放掉，無需手動管理，無需擔憂內存泄漏的風險。

#### Hyperf\Context\Context::set()

通過調用 `set(string $id, $value)` 方法儲存一個值到當前協程的上下文中，如下：

```php
<?php
use Hyperf\Context\Context;

// 將 bar 字符串以 foo 為 key 儲存到當前協程上下文中
$foo = Context::set('foo', 'bar');
// set 方法會再將 value 作為方法的返回值返回回來，所以 $foo 的值為 bar
```

#### Hyperf\Context\Context::get()

通過調用 `get(string $id, $default = null)` 方法可從當前協程的上下文中取出一個以 `$id` 為 `key` 儲存的值，如不存在則返回 `$default` ，如下：

```php
<?php
use Hyperf\Context\Context;

// 從當前協程上下文中取出 key 為 foo 的值，如不存在則返回 bar 字符串
$foo = Context::get('foo', 'bar');
```

#### Hyperf\Context\Context::has()

通過調用 `has(string $id)` 方法可判斷當前協程的上下文中是否存在以 `$id` 為 `key` 儲存的值，如存在則返回 `true`，不存在則返回 `false`，如下：

```php
<?php
use Hyperf\Context\Context;

// 從當前協程上下文中判斷 key 為 foo 的值是否存在
$foo = Context::has('foo');
```

#### Hyperf\Context\Context::override()

當我們需要做一些複雜的上下文處理，比如先判斷一個 `key` 是否存在，如果存在則取出 `value` 來再對 `value` 進行某些修改，然後再將 `value` 設置回上下文容器中，此時會有比較繁雜的判斷條件，可直接通過調用 `override` 方法來實現這個邏輯，如下：

```php
<?php
use Psr\Http\Message\ServerRequestInterface;
use Hyperf\Context\Context;

// 從協程上下文取出 $request 對象並設置 key 為 foo 的 Header，然後再保存到協程上下文中
$request = Context::override(ServerRequestInterface::class, function (ServerRequestInterface $request) {
    return $request->withAddedHeader('foo', 'bar');
});
```

### Swoole Runtime Hook Level

框架在入口函數中提供了 `SWOOLE_HOOK_FLAGS` 常量，如果您需要修改整個項目的 `Runtime Hook` 等級，比如想要支持 `CURL 協程` 並且 `Swoole` 版本為 `v4.5.4` 之前的版本，可以修改這裏的代碼，如下。

```php
<?php
! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL);
``` 

!> 如果 Swoole 版本 >= `v4.5.4`，不需要做任何修改。
