# Paginator

When you need to paginate data, you can easily solve your problem with the [hyperf/paginator](https://github.com/hyperf/paginator) component. You can encapsulate your data queries to make better use of the pagination functionality, and this component can also be used in other frameworks.
Usually, your pagination needs may exist in database queries. The [hyperf/database](https://github.com/hyperf/database) component has been combined with the paginator component, allowing you to easily call the paginator to achieve pagination when performing data queries. For details, please refer to the [Database Model - Paginator](db/paginator.md) chapter.

# Installation

```bash
composer require hyperf/paginator
```

# Basic Usage

Simply have a dataset and pagination requirements to perform pagination processing by instantiating a `Hyperf\Paginator\Paginator` class. The constructor of this class receives `__construct($items, int $perPage, ?int $currentPage = null, array $options = [])` parameters. We only need to pass the dataset in the form of an `Array` or `Hyperf\Collection\Collection` class to the `$items` parameter, and set the data volume per page `$perPage` and the current page number `$currentPage`. The `$options` parameter can define all attributes within the paginator instance in the form of `Key-Value`. For details, refer to the internal attributes of the paginator class.

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

        // Perform data query based on $currentPage and $perPage here; the following uses Collection instead
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

## Get Current Page Number

```php
<?php
$currentPage = $paginator->currentPage();
```

## Get Number of Items on Current Page

```php
<?php
$count = $paginator->count();
```

## Get the Index of the First Item on the Current Page

```php
<?php
$firstItem = $paginator->firstItem();
```

## Get the Index of the Last Item on the Current Page

```php
<?php
$lastItem = $paginator->lastItem();
```

## Check If There Are More Pages

```php
<?php
if ($paginator->hasMorePages()) {
    // ...
}
```

## Get URL for Corresponding Page

```php
<?php
// URL of the next page
$nextPageUrl = $paginator->nextPageUrl();
// URL of the previous page
$previousPageUrl = $paginator->previousPageUrl();
// Get the URL for the specified $page number
$url = $paginator->url($page);
```

## Is It on the First Page

```php
<?php
$onFirstPage = $paginator->onFirstPage();
```

## Are There More Pages

```php
<?php
$hasMorePages = $paginator->hasMorePages();
```

## Number of Items per Page

```php
<?php
$perPage = $paginator->perPage();
```

## Total Data Count

> `Hyperf\Paginator\Paginator` does not have this method; you need to use `Hyperf\Paginator\LengthAwarePaginator`

```php
<?php
$total = $paginator->total();
```
