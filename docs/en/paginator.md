# Paginator

When you need to paginate data, you can use the [hyperf/paginator](https://github.com/hyperf/paginator) component to solve your problem conveniently. You can encapsulate your data query little bit to perform better pagination. This component can also works well on other frameworks.

In most cases, paginator is used when query from databases. [hyperf/database](https://github.com/hyperf/database) component has already adapted the paginator component. You can easily use the paginator during data query. More details in [Database - Paginator](en/db/paginator.md) chapter.

# Installation

```bash
composer require hyperf/paginator
```

# Basic Usage

As long as there are data sets and paging requirements, you can instantiate a `Hyperf\Paginator\Paginator` class for paging processing. The constructor of this class receives `__construct($items, int $perPage, ?int $currentPage = null, array $options = [])` parameters. Just pass the data set to the `$items` parameter in the form of `Array (Array)` or `Hyperf\Collection\Colletion` collection class, and set the amount of data per page `$perPage` and the current page number `$currentPage `. The `$options` parameter can define all the attributes of the paginator instance in the form of `Key-Value`, and you can refer to the internal attributes of the paginator class for more details.

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

        // Perform query according to $currentPage and $perPage. The Collection type is used here.
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

# Paginator Methods

## Get current page number

```php
<?php
$currentPage = $paginator->currentPage();
```

## Get item count in the current page

```php
<?php
$count = $paginator->count();
```

## Get the first item in the current page

```php
<?php
$firstItem = $paginator->firstItem();
```

## Get the last item in the current page

```php
<?php
$lastItem = $paginator->lastItem();
```

## Whether there is more page or not

```php
<?php
if ($paginator->hasMorePages()) {
    // ...
}
```

## Get URL of the corresponding page

```php
<?php
// URL of the next page
$nextPageUrl = $paginator->nextPageUrl();
// URL of the previous page
$previousPageUrl = $paginator->previousPageUrl();
// URL of the $page
$url = $paginator->url($page);
```

## On the first page or not

```php
<?php
$onFirstPage = $paginator->onFirstPage();
```

## Get item count per page

```php
<?php
$perPage = $paginator->perPage();
```

## Total count

> No such method in Hyperf\Paginator\Paginator, you need to use Hyperf\Paginator\LengthAwarePaginator

```php
<?php
$total = $paginator->total();
```
