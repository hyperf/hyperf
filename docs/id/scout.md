# Model Full-text Search

## Preface

> [hyperf/scout](https://github.com/hyperf/scout) berasal dari [laravel/scout](https://github.com/laravel/scout). Kami melakukan beberapa adaptasi coroutine dengan API yang tetap sama. Terima kasih kepada tim Laravel untuk komponen yang powerful dan mudah digunakan ini. Sebagian dokumentasi dikutip dari dokumentasi resmi Laravel yang diterjemahkan oleh komunitas Laravel Mandarin.

Hyperf/Scout menyediakan solusi berbasis driver yang sederhana untuk pencarian full-text pada model. Menggunakan model observers, Scout secara otomatis menyinkronkan search index Anda dengan model records Anda.

Saat ini, Scout hadir dengan Elasticsearch driver; menulis custom drivers sangat sederhana, dan Anda bebas untuk memperluas Scout dengan implementasi pencarian Anda sendiri.

## Installation

### Mengenalkan Component Package dan Elasticsearch Driver

```bash
composer require hyperf/scout
composer require hyperf/elasticsearch
```

Setelah Scout diinstal, gunakan perintah `vendor:publish` untuk menghasilkan file konfigurasi Scout. Perintah ini akan menghasilkan file konfigurasi `scout.php` di direktori `config` Anda.

```bash
php bin/hyperf.php vendor:publish hyperf/scout
```

Terakhir, tambahkan trait `Hyperf\Scout\Searchable` ke model yang ingin Anda cari. Trait ini mendaftarkan model observer untuk menjaga model dan semua driver tetap sinkron:

```php
<?php

namespace App;

use Hyperf\Database\Model\Model;
use Hyperf\Scout\Searchable;

class Post extends Model
{
    use Searchable;
}
```

## Configuration

### Configuration File

Hasilkan file konfigurasi:

```bash
php bin/hyperf.php vendor:publish hyperf/scout
```

File konfigurasi:

```php
<?php

declare(strict_types=1);

return [
    'default' => env('SCOUT_ENGINE', 'elasticsearch'),
    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],
    'prefix' => env('SCOUT_PREFIX', ''),
    'soft_delete' => false,
    'concurrency' => 100,
    'engine' => [
        'elasticsearch' => [
            'driver' => Hyperf\Scout\Provider\ElasticsearchProvider::class,
            // Jika index diatur ke null, setiap model akan sesuai dengan satu index; jika tidak, setiap model sesuai dengan satu type.
            'index' => null,
            'hosts' => [
                env('ELASTICSEARCH_HOST', 'http://127.0.0.1:9200'),
            ],
        ],
    ],
];
```

### Configuring Model Indexes

Setiap model disinkronkan dengan "search index" yang diberikan, yang berisi semua searchable records untuk model tersebut. Dengan kata lain, Anda dapat menganggap setiap "index" sebagai tabel MySQL. Secara default, setiap model disimpan ke index yang cocok dengan nama "table" model (biasanya bentuk plural dari nama model). Anda juga dapat menyesuaikan index model dengan menimpa method `searchableAs` pada model:

```php
<?php

namespace App;

use Hyperf\Scout\Searchable;
use Hyperf\Database\Model\Model;

class Post extends Model
{
    use Searchable;

    /**
     * Dapatkan nama index untuk model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return 'posts_index';
    }
}
```

### Configuring Searchable Data

Secara default, "index" membaca data dari method `toArray` model untuk penyimpanan. Jika Anda ingin menyesuaikan data yang disinkronkan ke search index, Anda dapat menimpa method `toSearchableArray` pada model:

```php
<?php

namespace App;

use Hyperf\Scout\Searchable;
use Hyperf\Database\Model\Model;

class Post extends Model
{
    use Searchable;

    /**
     * Dapatkan array data yang dapat diindeks untuk model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $array = $this->toArray();

        // Sesuaikan array...

        return $array;
    }
}
```

## Indexing

### Batch Import

Jika Anda ingin menginstal Scout ke proyek yang sudah ada, Anda mungkin sudah memiliki database records yang ingin Anda impor ke search driver Anda. Gunakan perintah `import` yang disediakan oleh Scout untuk mengimpor semua records yang ada ke search index:

```bash
php bin/hyperf.php scout:import "App\Post"
```

### Adding Records

Ketika Anda menambahkan trait `Hyperf\Scout\Searchable` ke model, yang perlu Anda lakukan hanyalah `save` instance model, dan itu akan secara otomatis ditambahkan ke search index Anda. Operasi pembaruan index akan dilakukan di akhir coroutine dan tidak akan memblokir request.

```php
$order = new App\Order;

// ...

$order->save();
```

#### Batch Adding

Jika Anda ingin menambahkan koleksi model ke search index melalui model query builder, Anda juga dapat merantai method `searchable` pada model query builder. `searchable` akan memotong hasil query menjadi beberapa bagian (chunk) dan menambahkan records ke search index Anda.

```php
// Tambahkan menggunakan model query builder...
App\Order::where('price', '>', 100)->searchable();

// Tambahkan menggunakan model relationship...
$user->orders()->searchable();

// Tambahkan menggunakan collection...
$orders->searchable();
```

Method `searchable` dapat dilihat sebagai operasi "update or insert". Dengan kata lain, jika model record sudah ada di index Anda, maka akan diperbarui. Jika belum ada di search index, maka akan ditambahkan ke index.

### Updating Records

Untuk memperbarui model yang dapat dicari, perbarui properti instance model dan `save` model ke database. Scout akan secara otomatis menyinkronkan pembaruan ke search index Anda:

```php
$order = App\Order::find(1);

// Perbarui order...

$order->save();
```

Anda juga dapat menggunakan method `searchable` pada model queries untuk memperbarui koleksi model. Jika model ini tidak ada di index yang Anda ambil, maka akan dibuat:

```php
// Perbarui menggunakan model query...
App\Order::where('price', '>', 100)->searchable();

// Anda juga dapat menggunakan model relationship untuk memperbarui...
$user->orders()->searchable();

// Anda juga dapat menggunakan collection untuk memperbarui...
$orders->searchable();
```

### Removing Records

Cukup gunakan `delete` untuk menghapus model dari database, dan record di index juga akan dihapus. Bentuk penghapusan ini bahkan kompatibel dengan model yang menggunakan soft-delete:

```php
$order = App\Order::find(1);

$order->delete();
```

Jika Anda tidak ingin mengambil model sebelum menghapus record, Anda dapat menggunakan method `unsearchable` pada model query instance atau collection:

```php
// Hapus melalui model query...
App\Order::where('price', '>', 100)->unsearchable();

// Hapus melalui model relationship...
$user->orders()->unsearchable();

// Hapus melalui collection...
$orders->unsearchable();
```

### Pausing Indexing

Anda mungkin perlu melakukan sekumpulan operasi model tanpa menyinkronkan data model ke search index. Pada saat ini, Anda dapat menggunakan method `withoutSyncingToSearch` yang aman untuk coroutine untuk melakukan operasi ini. Method ini menerima callback yang dieksekusi segera. Semua operasi dalam callback ini tidak akan disinkronkan ke index model:

```php
App\Order::withoutSyncingToSearch(function () {
    // Lakukan tindakan model...
});
```

## Searching

Anda dapat menggunakan method `search` untuk mencari model. Method `search` menerima string yang digunakan untuk mencari model. Anda juga perlu merantai method `get` pada search query untuk melakukan query pada model yang cocok dengan statement pencarian yang diberikan:

```php
$orders = App\Order::search('Star Trek')->get();
```

Scout search mengembalikan koleksi model, sehingga Anda dapat langsung mengembalikan hasil dari routes atau controllers, dan mereka akan secara otomatis dikonversi ke format JSON:

```php
Route::get('/search', function () {
    return App\Order::search([])->get();
});
```

Jika Anda ingin mendapatkan hasil mentah sebelum dikembalikan sebagai model, Anda harus menggunakan method `raw`:

```php
$orders = App\Order::search('Star Trek')->raw();
```

Search queries biasanya dijalankan pada index yang ditentukan oleh method [`searchableAs`](#configuring-model-indexes) model. Tentu saja, Anda juga dapat menggunakan method `within` untuk menentukan index kustom yang harus dicari:

```php
$orders = App\Order::search('Star Trek')
    ->within('tv_shows_popularity_desc')
    ->get();
```

### Where Clauses

Scout memungkinkan Anda untuk menambahkan "where" statements sederhana ke search queries. Saat ini, statements ini hanya mendukung pemeriksaan kesamaan numerik dasar dan terutama digunakan untuk query rentang pencarian berdasarkan owner ID. Karena search index bukan database relasional, "where" statements yang lebih lanjut tidak didukung saat ini:

```php
$orders = App\Order::search('Star Trek')->where('user_id', 1)->get();
```

### Pagination

Selain mengambil koleksi model, Anda juga dapat menggunakan method `paginate` untuk melakukan paginasi hasil pencarian. Method ini mengembalikan instance `Paginator` yang mirip dengan [paginasi query model tradisional](/id/db/paginator):

```php
$orders = App\Order::search('Star Trek')->paginate();
```

Anda dapat menentukan berapa banyak model yang akan diambil per halaman dengan memberikan angka sebagai argumen pertama ke method `paginate`:

```php
$orders = App::search('Star Trek')->paginate(15);
```

Setelah mendapatkan hasil pencarian, Anda dapat menggunakan template engine favorit Anda untuk merender pagination links guna menampilkan hasil, seperti halnya paginasi query model tradisional:

```html
<div class="container">
    @foreach ($orders as $order)
        {{ $order->price }}
    @endforeach
</div>

{{ $orders->links() }}
```

## Custom Engines

#### Writing Engines

Jika search engine bawaan Scout tidak memenuhi kebutuhan Anda, Anda dapat menulis custom engine dan mendaftarkannya ke Scout. Engine Anda perlu mewarisi dari kelas abstrak `Hyperf\Scout\Engine\Engine`, yang berisi lima method yang harus diimplementasikan oleh custom engine Anda:

```php
use Hyperf\Scout\Builder;

abstract public function update($models);
abstract public function delete($models);
abstract public function search(Builder $builder);
abstract public function paginate(Builder $builder, $perPage, $page);
abstract public function map($results, $model);
```

Memeriksa method-method ini di class `Hyperf\Scout\Engine\ElasticsearchEngine` akan sangat membantu Anda. Class ini menyediakan titik awal yang baik untuk mempelajari cara mengimplementasikan method-method ini di custom engine.

#### Registering Engines

Setelah Anda menulis custom engine, Anda dapat menentukan engine tersebut di file konfigurasi. Misalnya, jika Anda telah menulis `MySqlSearchEngine`, Anda dapat menulisnya seperti ini di file konfigurasi:

```php
<?php
return [
    'default' => 'mysql',
    'engine' => [
        'mysql' => [
            'driver' => MySqlSearchEngine::class,
        ],
        'elasticsearch' => [
            'driver' => \Hyperf\Scout\Provider\ElasticsearchProvider::class,
        ],
    ],
];
```

## Perbedaan dari laravel/scout

- Hyperf/Scout menggunakan coroutine untuk menyinkronkan search index dan model records secara efisien, tanpa bergantung pada mekanisme queue.
- Hyperf/Scout menyediakan Elasticsearch engine open-source secara default, bukan Algolia yang bersifat closed-source.
