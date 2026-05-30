# Full-Text Search pada Model

## Pendahuluan

> [hyperf/scout](https://github.com/hyperf/scout) diturunkan dari [laravel/scout](https://github.com/laravel/scout), dengan beberapa modifikasi untuk mendukung coroutine namun tetap mempertahankan API yang sama. Terima kasih kepada tim Laravel yang telah mengimplementasikan komponen yang sangat powerful ini. Sebagian dokumentasi ini diadaptasi dari dokumentasi resmi Laravel yang diterjemahkan oleh komunitas Laravel China.

Hyperf/Scout menyediakan solusi sederhana berbasis driver untuk full-text search pada model. Menggunakan model observer, Scout akan secara otomatis menyinkronkan search index Anda dengan record model.

Saat ini, Scout dilengkapi dengan driver Elasticsearch; namun menulis driver kustom sangat mudah, dan Anda bebas mengembangkan Scout dengan implementasi pencarian Anda sendiri.

## Instalasi

### Menginstal Paket Komponen dan Driver Elasticsearch

```bash
composer require hyperf/scout
composer require hyperf/elasticsearch
```

Setelah Scout terinstal, gunakan perintah `vendor:publish` untuk menghasilkan file konfigurasi Scout. Perintah ini akan menghasilkan file konfigurasi `scout.php` di direktori `config` Anda.

```bash
php bin/hyperf.php vendor:publish hyperf/scout
```

Terakhir, tambahkan trait `Hyperf\Scout\Searchable` ke model yang ingin Anda buat searchable. Trait ini akan mendaftarkan model observer untuk menjaga model tetap sinkron dengan semua driver:

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

## Konfigurasi

### File Konfigurasi

Buat file konfigurasi:

```bash
php bin/hyperf.php vendor:publish hyperf/scout
```

Isi file konfigurasi:

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
            // Jika index diset null, setiap model akan memiliki index tersendiri,
            // sebaliknya setiap model akan memiliki type tersendiri
            'index' => null,
            'hosts' => [
                env('ELASTICSEARCH_HOST', 'http://127.0.0.1:9200'),
            ],
        ],
    ],
];
```

### Konfigurasi Index Model

Setiap model disinkronkan dengan "index" pencarian tertentu, yang berisi semua record searchable untuk model tersebut. Dengan kata lain, Anda dapat menganggap setiap "index" seperti tabel MySQL. Secara default, setiap model akan dipersistensikan ke index yang cocok dengan nama "tabel" model (biasanya bentuk jamak dari nama model). Anda juga dapat mengkustomisasi index model dengan meng-override method `searchableAs` pada model:

```php
<?php

namespace App;

use Hyperf\Scout\Searchable;
use Hyperf\Database\Model\Model;

class Post extends Model
{
    use Searchable;

    /**
     * Get the index name for the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return 'posts_index';
    }
}
```

### Konfigurasi Data yang Dapat Dicari

Secara default, "index" akan membaca data dari method `toArray` model untuk dipersistensikan. Jika Anda ingin mengkustomisasi data yang disinkronkan ke search index, Anda dapat meng-override method `toSearchableArray` pada model:

```php
<?php

namespace App;

use Hyperf\Scout\Searchable;
use Hyperf\Database\Model\Model;

class Post extends Model
{
    use Searchable;

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $array = $this->toArray();

        // Customize array...

        return $array;
    }
}
```

## Indexing

### Import Massal

Jika Anda ingin menginstal Scout ke proyek yang sudah ada, Anda mungkin sudah memiliki record database yang ingin Anda impor ke search driver. Gunakan perintah `import` yang disediakan Scout untuk mengimpor semua record yang ada ke search index:

```bash
php bin/hyperf.php scout:import "App\Post"
```

### Menambahkan Record

Setelah Anda menambahkan trait `Hyperf\Scout\Searchable` ke model, yang perlu Anda lakukan hanyalah `save` sebuah instance model dan ia akan otomatis ditambahkan ke search index Anda. Operasi update index akan dilakukan di akhir coroutine, tidak akan memblokir request.

```php
$order = new App\Order;

// ...

$order->save();
```

#### Penambahan Massal

Jika Anda ingin menambahkan koleksi model ke search index melalui query builder model, Anda juga dapat merantai method `searchable` pada query builder model. `searchable` akan membagi hasil query builder menjadi beberapa bagian dan menambahkan record ke search index Anda.

```php
// Menambahkan menggunakan query builder model...
App\Order::where('price', '>', 100)->searchable();

// Menambahkan record menggunakan relasi model...
$user->orders()->searchable();

// Menambahkan menggunakan koleksi...
$orders->searchable();
```

Method `searchable` dapat dianggap sebagai operasi "upsert". Dengan kata lain, jika record model sudah ada di index Anda, ia akan diperbarui. Jika tidak ada di search index, ia akan ditambahkan ke index.

### Memperbarui Record

Untuk memperbarui model yang dapat dicari, Anda hanya perlu memperbarui properti instance model dan `save` model ke database. Scout akan secara otomatis menyinkronkan pembaruan ke search index Anda:

```php
$order = App\Order::find(1);

// Update order...

$order->save();
```

Anda juga dapat menggunakan method `searchable` pada query model untuk memperbarui koleksi model. Jika model tidak ada di index yang Anda cari, ia akan dibuat:

```php
// Memperbarui menggunakan query model...
App\Order::where('price', '>', 100)->searchable();

// Anda juga dapat memperbarui menggunakan relasi model...
$user->orders()->searchable();

// Anda juga dapat memperbarui menggunakan koleksi...
$orders->searchable();
```

### Menghapus Record

Cukup gunakan `delete` untuk menghapus model dari database untuk menghapus record dari index. Bentuk penghapusan ini bahkan kompatibel dengan model soft delete:

```php
$order = App\Order::find(1);

$order->delete();
```

Jika Anda tidak ingin mengambil model sebelum menghapus record, Anda dapat menggunakan method `unsearchable` pada instance query model atau koleksi:

```php
// Menghapus melalui query model...
App\Order::where('price', '>', 100)->unsearchable();

// Menghapus melalui relasi model...
$user->orders()->unsearchable();

// Menghapus melalui koleksi...
$orders->unsearchable();
```

### Menjeda Indexing

Terkadang Anda perlu melakukan serangkaian operasi model tanpa menyinkronkan data model ke search index. Anda dapat melakukan ini menggunakan method `withoutSyncingToSearch` yang aman untuk coroutine. Method ini menerima satu callback yang akan segera dieksekusi. Semua operasi model yang terjadi dalam callback tidak akan disinkronkan ke index model:

```php
App\Order::withoutSyncingToSearch(function () {
    // Lakukan operasi model...
});
```

## Pencarian

Anda dapat menggunakan method `search` untuk mencari model. Method `search` menerima string yang digunakan untuk mencari model. Anda juga perlu merantai method `get` pada query pencarian untuk mengambil model yang cocok dengan query pencarian yang diberikan:

```php
$orders = App\Order::search('Star Trek')->get();
```

Pencarian Scout mengembalikan koleksi model Eloquent, sehingga Anda dapat langsung mengembalikan hasil dari route atau controller dan hasilnya akan otomatis dikonversi ke format JSON:

```php
Route::get('/search', function () {
    return App\Order::search([])->get();
});
```

Jika Anda ingin mendapatkan hasil mentah sebelum dikonversi ke model, Anda harus menggunakan method `raw`:

```php
$orders = App\Order::search('Star Trek')->raw();
```

Query pencarian biasanya akan dieksekusi pada index yang ditentukan oleh method [`searchableAs`](#konfigurasi-index-model) model. Tentu saja, Anda juga dapat menggunakan method `within` untuk menentukan index kustom yang harus dicari:

```php
$orders = App\Order::search('Star Trek')
    ->within('tv_shows_popularity_desc')
    ->get();
```

### Klausa Where

Scout memungkinkan Anda menambahkan klausa "where" sederhana ke query pencarian Anda. Saat ini, klausa ini hanya mendukung pemeriksaan kesetaraan numerik dasar, dan terutama berguna untuk membatasi query pencarian berdasarkan ID pemilik. Karena search index bukan database relasional, klausa "where" yang lebih canggih saat ini tidak didukung:

```php
$orders = App\Order::search('Star Trek')->where('user_id', 1)->get();
```

### Paginasi

Selain mengambil koleksi model, Anda dapat melakukan paginasi hasil pencarian menggunakan method `paginate`. Method ini akan mengembalikan instance `Paginator` seperti [paginasi query model tradisional](/id/db/paginator):

```php
$orders = App\Order::search('Star Trek')->paginate();
```

Anda dapat menentukan berapa banyak model yang diambil per halaman dengan meneruskan jumlah sebagai argumen pertama ke method `paginate`:

```php
$orders = App\Order::search('Star Trek')->paginate(15);
```

Setelah Anda mengambil hasil, Anda dapat menampilkan hasil dan merender link paginasi menggunakan template engine favorit Anda, seperti paginasi query model tradisional:

```html
<div class="container">
    @foreach ($orders as $order)
        {{ $order->price }}
    @endforeach
</div>

{{ $orders->links() }}
```

## Engine Kustom

### Menulis Engine

Jika engine pencarian Scout bawaan tidak memenuhi kebutuhan Anda, Anda dapat menulis engine kustom dan mendaftarkannya ke Scout. Engine Anda harus meng-extend abstract class `Hyperf\Scout\Engine\Engine`, yang berisi lima method yang harus diimplementasikan oleh engine kustom Anda:

```php
use Hyperf\Scout\Builder;

abstract public function update($models);
abstract public function delete($models);
abstract public function search(Builder $builder);
abstract public function paginate(Builder $builder, $perPage, $page);
abstract public function map($results, $model);
```

Melihat implementasi method ini di class `Hyperf\Scout\Engine\ElasticsearchEngine` akan sangat membantu. Class ini akan memberi Anda titik awal yang baik untuk mempelajari cara mengimplementasikan method ini di engine kustom Anda.

### Mendaftarkan Engine

Setelah Anda menulis engine kustom, Anda dapat menentukan engine di file konfigurasi. Misalnya, jika Anda telah menulis `MySqlSearchEngine`, Anda dapat menulisnya di file konfigurasi seperti ini:

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

## Perbedaan dengan laravel/scout

- Hyperf/Scout menggunakan coroutine untuk menyinkronkan search index dan record model secara efisien, tanpa bergantung pada mekanisme queue.
- Hyperf/Scout secara default menyediakan engine Elasticsearch open-source, bukan Algolia yang closed-source.
