# Paginator

Ketika Anda perlu melakukan paginasi data, Anda dapat dengan mudah menyelesaikan masalah Anda dengan komponen [hyperf/paginator](https://github.com/hyperf/paginator). Anda dapat membungkus query data untuk memanfaatkan fungsionalitas paginasi, dan komponen ini juga dapat digunakan di framework lain.
Biasanya, kebutuhan paginasi Anda mungkin ada dalam query database. Komponen [hyperf/database](https://github.com/hyperf/database) telah digabungkan dengan komponen paginator, memungkinkan Anda untuk dengan mudah memanggil paginator guna mencapai paginasi saat melakukan query data. Untuk detailnya, lihat bab [Database Model - Paginator](id/db/paginator.md).

# Instalasi

```bash
composer require hyperf/paginator
```

# Penggunaan Dasar

Cukup sediakan dataset dan tentukan kebutuhan paginasi, lalu proses dengan menginstansiasi kelas `Hyperf\Paginator\Paginator`. Konstruktor kelas ini menerima parameter `__construct($items, int $perPage, ?int $currentPage = null, array $options = [])`. Kita hanya perlu melewatkan dataset dalam bentuk `Array` atau kelas `Hyperf\Collection\Collection` ke parameter `$items`, dan mengatur jumlah data per halaman `$perPage` serta nomor halaman saat ini `$currentPage`. Parameter `$options` dapat mendefinisikan semua atribut dalam instance paginator dalam bentuk `Key-Value`. Untuk detailnya, lihat atribut internal dari kelas paginator.

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

        // Lakukan query data berdasarkan $currentPage dan $perPage di sini; berikut menggunakan Collection sebagai pengganti
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

## Mendapatkan Nomor Halaman Saat Ini

```php
<?php
$currentPage = $paginator->currentPage();
```

## Mendapatkan Jumlah Item di Halaman Saat Ini

```php
<?php
$count = $paginator->count();
```

## Mendapatkan Indeks Item Pertama di Halaman Saat Ini

```php
<?php
$firstItem = $paginator->firstItem();
```

## Mendapatkan Indeks Item Terakhir di Halaman Saat Ini

```php
<?php
$lastItem = $paginator->lastItem();
```

## Memeriksa Apakah Ada Halaman Lain

```php
<?php
if ($paginator->hasMorePages()) {
    // ...
}
```

## Mendapatkan URL untuk Halaman yang Sesuai

```php
<?php
// URL halaman berikutnya
$nextPageUrl = $paginator->nextPageUrl();
// URL halaman sebelumnya
$previousPageUrl = $paginator->previousPageUrl();
// Mendapatkan URL untuk nomor halaman $page yang ditentukan
$url = $paginator->url($page);
```

## Apakah di Halaman Pertama

```php
<?php
$onFirstPage = $paginator->onFirstPage();
```

## Apakah Ada Halaman Lain

```php
<?php
$hasMorePages = $paginator->hasMorePages();
```

## Jumlah Item per Halaman

```php
<?php
$perPage = $paginator->perPage();
```

## Total Jumlah Data

> `Hyperf\Paginator\Paginator` tidak ada metode ini. Anda perlu menggunakan `Hyperf\Paginator\LengthAwarePaginator`

```php
<?php
$total = $paginator->total();
```
