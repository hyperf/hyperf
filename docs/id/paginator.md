# Paginator

Ketika Anda perlu melakukan paginasi data, Anda dapat menggunakan komponen
[hyperf/paginator](https://github.com/hyperf/paginator) untuk menyelesaikan
masalah Anda dengan mudah. Anda dapat melakukan sedikit enkapsulasi kueri data
Anda untuk mendapatkan paginasi yang lebih baik. Komponen ini juga dapat
berfungsi dengan baik pada framework lainnya.

Dalam kebanyakan kasus, paginator digunakan ketika melakukan kueri dari
database. Komponen [hyperf/database](https://github.com/hyperf/database) telah
mengadaptasi komponen paginator ini. Anda dapat dengan mudah menggunakan
paginator selama kueri data. Detail selengkapnya ada pada bab
[Database - Paginator](id/db/paginator.md).

# Instalasi

```bash
composer require hyperf/paginator
```

# Penggunaan Dasar

Selama terdapat set data dan kebutuhan paginasi, Anda dapat menginstansiasi
class `Hyperf\Paginator\Paginator` untuk memproses paginasi. Constructor dari
class ini menerima parameter
`__construct($items, int $perPage, ?int $currentPage = null, array $options = [])`.
Cukup kirimkan set data ke parameter `$items` dalam bentuk `Array (Array)` atau
class collection `Hyperf\Collection\Collection`, lalu atur jumlah data per
halaman `$perPage` serta nomor halaman saat ini `$currentPage`. Parameter
`$options` dapat menentukan semua atribut dari instance paginator dalam bentuk
`Key-Value`, dan Anda dapat merujuk ke atribut internal dari class paginator
untuk detail selengkapnya.

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

# Metode Paginator

## Mendapatkan nomor halaman saat ini

```php
<?php
$currentPage = $paginator->currentPage();
```

## Mendapatkan jumlah item di halaman saat ini

```php
<?php
$count = $paginator->count();
```

## Mendapatkan item pertama di halaman saat ini

```php
<?php
$firstItem = $paginator->firstItem();
```

## Mendapatkan item terakhir di halaman saat ini

```php
<?php
$lastItem = $paginator->lastItem();
```

## Apakah ada halaman berikutnya atau tidak

```php
<?php
if ($paginator->hasMorePages()) {
    // ...
}
```

## Mendapatkan URL dari halaman terkait

```php
<?php
// URL of the next page
$nextPageUrl = $paginator->nextPageUrl();
// URL of the previous page
$previousPageUrl = $paginator->previousPageUrl();
// URL of the $page
$url = $paginator->url($page);
```

## Apakah sedang berada di halaman pertama atau tidak

```php
<?php
$onFirstPage = $paginator->onFirstPage();
```

## Apakah masih ada halaman lain

```php
<?php
$hasMorePages = $paginator->hasMorePages();
```

## Mendapatkan jumlah item per halaman

```php
<?php
$perPage = $paginator->perPage();
```

## Total jumlah data

> Metode ini tidak ada di `Hyperf\Paginator\Paginator`, Anda perlu menggunakan `Hyperf\Paginator\LengthAwarePaginator`

```php
<?php
$total = $paginator->total();
```
