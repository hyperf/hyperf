# 分页器

在您需要对数据进行分页处理时，可以借助 [hyperf/paginator](https://github.com/hyperf/paginator) 组件很方便的解决您的问题，您可对您的数据查询进行一定的封装处理，以便更好的使用分页功能，该组件也可用于其它框架上。   
通常情况下，您对分页器的需求可能都是存在于数据库查询上，[hyperf/database](https://github.com/hyperf/database) 数据库组件已经与分页器组件进行了结合，您可以在进行数据查询时很方便的调用分页器来实现分页，具体可查阅 [数据库模型 - 分页](zh-cn/db/paginator.md) 章节。

# 安装

```bash
composer require hyperf/paginator
```

# 基本使用

只需存在数据集和分页需求，便可通过实例化一个 `Hyperf\Paginator\Paginator` 类来进行分页处理，该类的构造函数接收 `__construct($items, int $perPage, ?int $currentPage = null, array $options = [])` 参数，我们只需以 `数组(Array)` 或 `Hyperf\Collection\Colletion` 集合类的形式传递数据集到 `$items` 参数，并设定每页数据量 `$perPage` 和当前页数 `$currentPage` 即可，`$options` 参数则可以通过 `Key-Value` 的形式定义分页器实例内的所有属性，具体可查阅分页器类的内部属性。

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

        // 这里根据 $currentPage 和 $perPage 进行数据查询，以下使用 Collection 代替
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

# 分页器方法

## 获取当前页数

```php
<?php
$currentPage = $paginator->currentPage();
```

## 获取当前页的条数

```php
<?php
$count = $paginator->count();
```

## 获取当前页中第一条数据的编号

```php
<?php
$firstItem = $paginator->firstItem();
```

## 获取当前页中最后一条数据的编号

```php
<?php
$lastItem = $paginator->lastItem();
```

## 获取是否还有更多的分页

```php
<?php
if ($paginator->hasMorePages()) {
    // ...
}
```

## 获取对应分页的 URL

```php
<?php
// 下一页的 URL
$nextPageUrl = $paginator->nextPageUrl();
// 上一页的 URL
$previousPageUrl = $paginator->previousPageUrl();
// 获取指定 $page 页数的 URL
$url = $paginator->url($page);
```

## 是否处于第一页

```php
<?php
$onFirstPage = $paginator->onFirstPage();
```

## 是否有更多分页

```php
<?php
$hasMorePages = $paginator->hasMorePages();
```

## 每页的数据条数

```php
<?php
$perPage = $paginator->perPage();
```

## 数据总数

> Hyperf\Paginator\Paginator 没有这个方法，需要使用 Hyperf\Paginator\LengthAwarePaginator

```php
<?php
$total = $paginator->total();
```
