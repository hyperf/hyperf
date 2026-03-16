# 分頁器

在您需要對資料進行分頁處理時，可以藉助 [hyperf/paginator](https://github.com/hyperf/paginator) 元件很方便的解決您的問題，您可對您的資料查詢進行一定的封裝處理，以便更好的使用分頁功能，該元件也可用於其它框架上。   
通常情況下，您對分頁器的需求可能都是存在於資料庫查詢上，[hyperf/database](https://github.com/hyperf/database) 資料庫元件已經與分頁器元件進行了結合，您可以在進行資料查詢時很方便的呼叫分頁器來實現分頁，具體可查閱 [資料庫模型 - 分頁](zh-tw/db/paginator.md) 章節。

# 安裝

```bash
composer require hyperf/paginator
```

# 基本使用

只需存在資料集和分頁需求，便可透過例項化一個 `Hyperf\Paginator\Paginator` 類來進行分頁處理，該類的建構函式接收 `__construct($items, int $perPage, ?int $currentPage = null, array $options = [])` 引數，我們只需以 `陣列(Array)` 或 `Hyperf\Collection\Colletion` 集合類的形式傳遞資料集到 `$items` 引數，並設定每頁資料量 `$perPage` 和當前頁數 `$currentPage` 即可，`$options` 引數則可以透過 `Key-Value` 的形式定義分頁器例項內的所有屬性，具體可查閱分頁器類的內部屬性。

```php
<?php

namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Paginator\Paginator;
use Hyperf\Collection\Collection;

#[AutoController]
class UserController
{
    public function index(RequestInterface $request)
    {
        $currentPage = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 2);

        // 這裡根據 $currentPage 和 $perPage 進行資料查詢，以下使用 Collection 代替
        $collection = new Collection([
            ['id' => 1, 'name' => 'Tom'],
            ['id' => 2, 'name' => 'Sam'],
            ['id' => 3, 'name' => 'Tim'],
            ['id' => 4, 'name' => 'Joe'],
        ]);

        $users = array_values($collection->forPage($currentPage, $perPage)->toArray());

        return new Paginator($users, $perPage, $currentPage);
    }
}
```

# 分頁器方法

## 獲取當前頁數

```php
<?php
$currentPage = $paginator->currentPage();
```

## 獲取當前頁的條數

```php
<?php
$count = $paginator->count();
```

## 獲取當前頁中第一條資料的編號

```php
<?php
$firstItem = $paginator->firstItem();
```

## 獲取當前頁中最後一條資料的編號

```php
<?php
$lastItem = $paginator->lastItem();
```

## 獲取是否還有更多的分頁

```php
<?php
if ($paginator->hasMorePages()) {
    // ...
}
```

## 獲取對應分頁的 URL

```php
<?php
// 下一頁的 URL
$nextPageUrl = $paginator->nextPageUrl();
// 上一頁的 URL
$previousPageUrl = $paginator->previousPageUrl();
// 獲取指定 $page 頁數的 URL
$url = $paginator->url($page);
```

## 是否處於第一頁

```php
<?php
$onFirstPage = $paginator->onFirstPage();
```

## 是否有更多分頁

```php
<?php
$hasMorePages = $paginator->hasMorePages();
```

## 每頁的資料條數

```php
<?php
$perPage = $paginator->perPage();
```

## 資料總數

> Hyperf\Paginator\Paginator 沒有這個方法，需要使用 Hyperf\Paginator\LengthAwarePaginator

```php
<?php
$total = $paginator->total();
```
