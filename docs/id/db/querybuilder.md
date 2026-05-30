# Query builder

## Pendahuluan

Query builder database milik Hyperf menyediakan antarmuka yang nyaman untuk
membuat dan menjalankan query database. Ini dapat digunakan untuk melakukan
sebagian besar operasi database dalam aplikasi dan berjalan di semua sistem
database yang didukung.

Query builder Hyperf menggunakan parameter binding PDO untuk melindungi aplikasi
Anda dari serangan SQL injection. Jadi tidak perlu melakukan sanitasi string yang
dilewatkan sebagai binding.

Hanya beberapa tutorial yang umum digunakan yang disediakan di sini, dan tutorial
spesifik dapat dilihat di situs web resmi Laravel.
[Laravel Query Builder](https://laravel.com/docs/5.8/queries)

## Mendapatkan hasil (Get results)

```php
use Hyperf\DbConnection\Db;

$users = Db::select('SELECT * FROM user;');
$users = Db::table('user')->get();
$users = Db::table('user')->select('name', 'gender as user_gender')->get();
```

Metode `Db::select()` mengembalikan array, dan metode `get` mengembalikan
`Hyperf\Collection\Collection`. Elemennya adalah `stdClass`, sehingga data dari
setiap elemen dapat dikembalikan dengan kode berikut:

```php
<?php

foreach ($users as $user) {
    echo $user->name;
}
```

### Mengubah hasil ke format array

Dalam beberapa skenario, Anda mungkin ingin menggunakan struktur `Array` alih-alih
objek `stdClass` pada hasil query. Karena `Eloquent` menghapus konfigurasi
default `FetchMode` yang dikonfigurasi lewat file config, pada titik ini Anda
dapat mengubah konfigurasi tersebut dengan mendengarkan event
`Hyperf\Database\Events\StatementPrepared` melalui listener:

```php
<?php
declare(strict_types=1);

namespace App\Listener;

use Hyperf\Database\Events\StatementPrepared;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use PDO;

#[Listener]
class FetchModeListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            StatementPrepared::class,
        ];
    }

    public function process(object $event)
    {
        if ($event instanceof StatementPrepared) {
            $event->statement->setFetchMode(PDO::FETCH_ASSOC);
        }
    }
}
```

### Mendapatkan nilai dari satu baris

Jika ingin mendapatkan nilai dari satu baris, Anda dapat menggunakan method
`first`.

```php
<?php
use Hyperf\DbConnection\Db;

$row = Db::table('user')->first(); // sql akan otomatis menambahkan limit 1
var_dump($row);
```

### Mendapatkan satu nilai

Jika ingin mendapatkan satu nilai, Anda dapat menggunakan method `value`.

```php
<?php
use Hyperf\DbConnection\Db;

$id = Db::table('user')->value('id');
var_dump($id);
```

### Mendapatkan nilai dari satu kolom

Jika Anda ingin mendapatkan collection yang berisi nilai dari satu kolom saja,
Anda dapat menggunakan metode `pluck`. Pada contoh berikut, kita akan mengambil
kumpulan title pada tabel roles:

```php
<?php
use Hyperf\DbConnection\Db;

$names = Db::table('user')->pluck('name');

foreach ($names as $name) {
    echo $name;
}

```

Anda juga dapat menentukan key kustom untuk field dalam collection yang dikembalikan:

```php
<?php
use Hyperf\DbConnection\Db;

$roles = Db::table('roles')->pluck('title', 'name');

foreach ($roles as $name => $title) {
    echo $title;
}

```

### Hasil ber-chunk (Chunked results)

Jika Anda perlu memproses ribuan data database, Anda dapat mempertimbangkan untuk
menggunakan metode `chunk`. Metode ini mengambil sebagian kecil dari hasil query
pada satu waktu dan meneruskannya ke fungsi `closure` untuk diproses. Metode ini
sangat berguna ketika `Command` menulis ribuan data pemrosesan. Sebagai contoh,
kita dapat memotong seluruh data tabel user menjadi bagian-bagian kecil yang
memproses 100 data sekaligus:

```php
<?php
use Hyperf\DbConnection\Db;

Db::table('user')->orderBy('id')->chunk(100, function ($users) {
    foreach ($users as $user) {
        //
    }
});
```

Anda dapat menghentikan pengambilan hasil chunk dengan mengembalikan `false` di
dalam closure:

```php
use Hyperf\DbConnection\Db;

Db::table('user')->orderBy('id')->chunk(100, function ($users) {

    return false;
});
```

Jika Anda memperbarui record database saat melakukan chunk pada hasil query, hasil
chunk mungkin tidak sesuai dengan yang diharapkan. Oleh karena itu, saat memperbarui
record secara ber-chunk, lebih baik menggunakan metode `chunkById`. Metode ini akan
secara otomatis melakukan paginasi hasil berdasarkan primary key record tersebut:

```php
use Hyperf\DbConnection\Db;

Db::table('user')->where('gender', 1)->chunkById(100, function ($users) {
    foreach ($users as $user) {
        Db::table('user')
            ->where('id', $user->id)
            ->update(['update_time' => time()]);
    }
});
```

> Setiap perubahan pada primary key atau foreign key dapat memengaruhi query blok
> saat memperbarui atau menghapus record di dalam callback blok. Hal ini dapat
> menyebabkan record tidak disertakan dalam hasil chunk.

### Query agregat

Framework ini juga menyediakan metode kelas agregat seperti `count`, `max`,
`min`, `avg`, `sum`.

```php
use Hyperf\DbConnection\Db;

$count = Db::table('user')->count();
```

#### Menentukan apakah record ada

Selain menggunakan metode `count` untuk menentukan apakah hasil dari kondisi
query ada, Anda juga dapat menggunakan metode `exists` dan `doesntExist`:

```php
return Db::table('orders')->where('finalized', 1)->exists();

return Db::table('orders')->where('finalized', 1)->doesntExist();
```

## Query

### Menentukan pernyataan Select

Tentu saja Anda mungkin tidak selalu ingin mengambil semua kolom dari tabel database.
Menggunakan metode select, Anda dapat menyesuaikan pernyataan query select untuk
mengambil field tertentu:

```php
$users = Db::table('user')->select('name', 'email as user_email')->get();
```

Metode `distinct` memaksa query untuk mengembalikan hasil yang unik:

```php
$users = Db::table('user')->distinct()->get();
```

Jika Anda sudah memiliki instance query builder dan ingin menambahkan field ke
query yang sudah ada, Anda dapat menggunakan metode `addSelect`:

```php
$query = Db::table('users')->select('name');

$users = $query->addSelect('age')->get();
```

## Ekspresi mentah (Raw expression)

Terkadang Anda perlu menggunakan ekspresi mentah dalam query, misalnya untuk
mengimplementasikan `COUNT(0) AS count`, yang membutuhkan penggunaan metode `raw`.

```php
use Hyperf\DbConnection\Db;

$res = Db::table('user')->select('gender', Db::raw('COUNT(0) AS `count`'))->groupBy('gender')->get();
```

### Force Index

Masalah slow query database lebih dari 90% disebabkan oleh index yang tidak
tepat. Sebagian query lambat terjadi karena `query optimizer` database server
tidak menggunakan index terbaik. Pada kondisi ini, force index perlu digunakan:

```php
Db::table(Db::raw("{$table} FORCE INDEX({$index})"));
```

### Metode native

Metode berikut dapat digunakan sebagai pengganti `Db::raw` untuk memasukkan
ekspresi mentah ke dalam berbagai bagian query.

Metode `selectRaw` dapat digunakan sebagai pengganti `select(Db::raw(...))`.
Parameter kedua dari metode ini bersifat opsional, dan nilainya berupa array dari
parameter yang di-binding:

```php
$orders = Db::table('order')
    ->selectRaw('price * ? as price_with_tax', [1.0825])
    ->get();
```

Metode `whereRaw` dan `orWhereRaw` menyisipkan `where` native ke dalam query Anda.
Parameter kedua dari kedua metode ini tetap opsional, dan nilainya tetap berupa
array parameter yang di-binding:

```php
$orders = Db::table('order')
    ->whereRaw('price > IF(state = "TX", ?, 100)', [200])
    ->get();
```

Metode `havingRaw` and `orHavingRaw` dapat digunakan untuk menetapkan string mentah
sebagai nilai dari pernyataan `having`:

```php
$orders = Db::table('order')
    ->select('department', Db::raw('SUM(price) as total_sales'))
    ->groupBy('department')
    ->havingRaw('SUM(price) > ?', [2500])
    ->get();
```

Metode `orderByRaw` dapat digunakan untuk menetapkan string mentah sebagai nilai dari
klausa `order by`:

```php
$orders = Db::table('order')
    ->orderByRaw('updated_at - created_at DESC')
    ->get();
```

## Join table

### Klausa Inner Join

Query builder juga dapat menulis metode `join`. Untuk melakukan `"inner join"` dasar,
Anda dapat menggunakan metode `join` pada instance query builder. Argumen pertama yang
dilewatkan ke metode `join` adalah nama tabel yang ingin Anda join, sedangkan argumen
lainnya menggunakan batasan field yang menentukan join tersebut. Anda juga dapat
melakukan join ke beberapa tabel dalam satu query:

```php
$users = Db::table('users')
    ->join('contacts', 'users.id', '=', 'contacts.user_id')
    ->join('orders', 'users.id', '=', 'orders.user_id')
    ->select('users.*', 'contacts.phone', 'orders.price')
    ->get();
```

### Left Join / Right Join

Jika Anda ingin menggunakan `"left join"` atau `"right join"` alih-alih
`"inner join"`, gunakan metode `leftJoin` atau `rightJoin`. Kedua metode ini digunakan
dengan cara yang sama seperti metode `join`:

```php
$users = Db::table('users')
    ->leftJoin('posts', 'users.id', '=', 'posts.user_id')
    ->get();
$users = Db::table('users')
    ->rightJoin('posts', 'users.id', '=', 'posts.user_id')
    ->get();
```

### Pernyataan Cross Join

Gunakan metode `crossJoin` untuk melakukan `"cross join"` dengan nama tabel yang
ingin Anda gabungkan. Cross join menghasilkan produk Cartesian antara tabel pertama
dan tabel yang digabungkan:

```php
$users = Db::table('sizes')
    ->crossJoin('colours')
    ->get();
```

### Pernyataan Join Lanjutan

Anda dapat menentukan pernyataan `join` yang lebih kompleks. Misalnya dengan melewatkan
`closure` sebagai parameter kedua dari metode `join`. `closure` ini menerima objek
`JoinClause`, yang menentukan batasan-batasan dalam pernyataan `join`:

```php
Db::table('users')
    ->join('contacts', function ($join) {
        $join->on('users.id', '=', 'contacts.user_id')->orOn(...);
    })
    ->get();
```

Jika Anda ingin menggunakan pernyataan bergaya `"where"` pada join, Anda dapat
menggunakan metode `where` dan `orWhere` pada join tersebut. Metode ini membandingkan
kolom dengan nilai alih-alih kolom dengan kolom:

```php
Db::table('users')
    ->join('contacts', function ($join) {
        $join->on('users.id', '=', 'contacts.user_id')
                ->where('contacts.user_id', '>', 5);
    })
    ->get();
```

### Query Subjoin

Anda dapat menggunakan metode `joinSub`, `leftJoinSub` dan `rightJoinSub` untuk
menggabungkan query sebagai subquery. Masing-masing metode menerima tiga parameter:
subquery, alias tabel, dan closure yang mendefinisikan field terkait:

```php
$latestPosts = Db::table('posts')
    ->select('user_id', Db::raw('MAX(created_at) as last_post_created_at'))
    ->where('is_published', true)
    ->groupBy('user_id');

$users = Db::table('users')
    ->joinSub($latestPosts, 'latest_posts', function($join) {
        $join->on('users.id', '=', 'latest_posts.user_id');
    })->get();
```

## Combined query (Query Gabungan)

Query builder juga menyediakan jalan pintas untuk "menggabungkan" dua query.
Sebagai contoh, Anda dapat membuat query terlebih dahulu, lalu menggunakan metode
`union` untuk menggabungkannya dengan query kedua:

```php
$first = Db::table('users')->whereNull('first_name');

$users = Db::table('users')
    ->whereNull('last_name')
    ->union($first)
    ->get();
```

## Pernyataan Where

### Pernyataan Where Sederhana

Dalam membuat instance query `where`, Anda dapat menggunakan metode `where`. Cara
paling dasar untuk memanggil `where` adalah dengan melewatkan tiga parameter:
parameter pertama adalah nama kolom, parameter kedua adalah operator apa pun yang
didukung oleh sistem database, dan parameter ketiga adalah nilai yang akan
dibandingkan dengan kolom tersebut.

Sebagai contoh, berikut adalah query untuk memverifikasi bahwa nilai dari field
gender sama dengan 1:

```php
$users = Db::table('user')->where('gender', '=', 1)->get();
```

Untuk kemudahan, jika Anda hanya membandingkan nilai kolom dengan nilai tertentu,
Anda dapat langsung melewatkan nilai tersebut sebagai parameter kedua dari
metode `where`:

```php
$users = Db::table('user')->where('gender', 1)->get();
```

Tentu saja, Anda juga dapat menggunakan operator lain untuk menulis klausa where:

```php
$users = Db::table('users')->where('gender', '>=', 0)->get();

$users = Db::table('users')->where('gender', '<>', 1)->get();

$users = Db::table('users')->where('name', 'like', 'T%')->get();
```

Anda juga dapat melewatkan array kondisi ke fungsi where:

```php
$users = Db::table('user')->where([
    ['status', '=', '1'],
    ['gender', '=', '1'],
])->get();
```

### Pernyataan Or

Anda dapat merantai batasan `where` bersama-sama atau menambahkan klausa `or` ke
dalam query. Metode `orWhere` menerima parameter yang sama dengan metode `where`:

```php
$users = Db::table('user')
    ->where('gender', 1)
    ->orWhere('name', 'John')
    ->get();
```

### Pernyataan Where Lainnya

#### whereBetween

Metode `whereBetween` memverifikasi bahwa nilai field berada di antara dua nilai
yang diberikan:

```php
$users = Db::table('users')->whereBetween('votes', [1, 100])->get();
```

#### whereNotBetween

Metode `whereNotBetween` memverifikasi bahwa nilai field berada di luar dua nilai
yang diberikan:

```php
$users = Db::table('users')->whereNotBetween('votes', [1, 100])->get();
```

#### whereIn / whereNotIn

Metode `whereIn` memvalidasi bahwa nilai field harus ada di dalam array yang
ditentukan:

```php
$users = Db::table('users')->whereIn('id', [1, 2, 3])->get();
```

Metode `whereNotIn` memverifikasi bahwa nilai field tidak boleh ada di dalam array
yang ditentukan:

```php
$users = Db::table('users')->whereNotIn('id', [1, 2, 3])->get();
```

### Pengelompokan parameter

Terkadang Anda perlu membuat klausa `where` yang lebih kompleks, seperti
`"where exists"` atau pengelompokan parameter bersarang. Query builder juga dapat
menangani hal ini. Di bawah ini, mari kita lihat contoh pengelompokan batasan
dalam tanda kurung:

```php
Db::table('users')->where('name', '=', 'John')
    ->where(function ($query) {
        $query->where('votes', '>', 100)
                ->orWhere('title', '=', 'Admin');
    })
    ->get();
```

Seperti yang Anda lihat, sebuah `Closure` ditulis ke metode `where` untuk membuat
query builder guna membatasi pengelompokan. `Closure` menerima instance query yang
dapat Anda gunakan untuk mengatur batasan yang harus disertakan. Contoh di atas
akan menghasilkan SQL berikut:

```sql
select * from users where name = 'John' and (votes > 100 or title = 'Admin')
```

> Anda harus memanggil pengelompokan ini dengan `orWhere` untuk menghindari
> penerapan efek global yang tidak disengaja.

#### Pernyataan Where Exists

Metode `whereExists` memungkinkan Anda untuk menggunakan pernyataan SQL
`where exists`. Metode `whereExists` menerima parameter `Closure` yang menerima
instance query builder untuk mendefinisikan query yang ditempatkan di dalam klausa
`exists`:

```php
Db::table('users')->whereExists(function ($query) {
    $query->select(Db::raw(1))
            ->from('orders')
            ->whereRaw('orders.user_id = users.id');
})
->get();
```

Query di atas akan menghasilkan pernyataan SQL berikut:

```sql
select * from users
where exists (
    select 1 from orders where orders.user_id = users.id
)
```

#### Pernyataan Where JSON

`Hyperf` juga mendukung query field dengan tipe `JSON` (hanya pada database yang
mendukung tipe `JSON`).

```php
$users = Db::table('users')
    ->where('options->language', 'en')
    ->get();

$users = Db::table('users')
    ->where('preferences->dining->meal', 'salad')
    ->get();
```

Anda juga dapat menggunakan `whereJsonContains` untuk melakukan query array `JSON`:

```php
$users = Db::table('users')
    ->whereJsonContains('options->languages', 'en')
    ->get();
```

Anda dapat menggunakan `whereJsonLength` untuk melakukan query panjang array `JSON`:

```php
$users = Db::table('users')
    ->whereJsonLength('options->languages', 0)
    ->get();

$users = Db::table('users')
    ->whereJsonLength('options->languages', '>', 1)
    ->get();
```

## Pengurutan, Pengelompokan, Batas (Limit), & Offset

### orderBy

Metode `orderBy` memungkinkan Anda mengurutkan hasil query berdasarkan field
tertentu. Parameter pertama dari `orderBy` harus berupa field yang ingin diurutkan,
dan parameter kedua mengontrol arah pengurutan, yang dapat berupa `asc` atau `desc`:

```php
$users = Db::table('users')
    ->orderBy('name', 'desc')
    ->get();
```

### latest / oldest

Metode `latest` dan `oldest` memungkinkan Anda mengurutkan hasil berdasarkan tanggal
dengan mudah. Secara default, metode ini menggunakan kolom `created_at` sebagai acuan
pengurutan. Tentu saja, Anda juga dapat melewatkan nama kolom kustom:

```php
$user = Db::table('users')->latest()->first();
```

### inRandomOrder

Metode `inRandomOrder` digunakan untuk mengurutkan hasil secara acak. Misalnya,
Anda dapat menggunakan metode ini untuk menemukan user acak.

```php
$randomUser = Db::table('users')->inRandomOrder()->first();
```

### groupBy / having

Metode `groupBy` dan `having` dapat digunakan untuk mengelompokkan hasil query.
Penggunaan metode `having` sangat mirip dengan metode `where`:

```php
$users = Db::table('users')
    ->groupBy('account_id')
    ->having('account_id', '>', 100)
    ->get();
```

Anda dapat meneruskan beberapa argumen ke metode `groupBy`:

```php
$users = Db::table('users')
    ->groupBy('first_name', 'status')
    ->having('account_id', '>', 100)
    ->get();
```

> Untuk sintaks having yang lebih kompleks, lihat metode `havingRaw`.

### skip / take

Untuk membatasi jumlah hasil yang dikembalikan, atau untuk melewati sejumlah hasil
tertentu, Anda dapat menggunakan metode `skip` dan `take`:

```php
$users = Db::table('users')->skip(10)->take(5)->get();
```

Atau Anda juga dapat menggunakan metode `limit` dan `offset`:

```php
$users = Db::table('users')->offset(10)->limit(5)->get();
```

## Pernyataan Kondisional

Terkadang Anda mungkin ingin mengeksekusi bagian query tertentu hanya jika kondisi
tertentu bernilai true. Misalnya, Anda mungkin hanya ingin menerapkan pernyataan
`where` jika nilai yang diberikan tersedia dalam request. Anda dapat melakukan ini
dengan menggunakan metode `when`:

```php
$role = $request->input('role');

$users = Db::table('users')
    ->when($role, function ($query, $role) {
        return $query->where('role_id', $role);
    })
    ->get();
```

Metode `when` mengeksekusi closure yang diberikan hanya jika parameter pertama
bernilai `true`. Jika parameter pertama bernilai `false`, closure tersebut tidak
akan dieksekusi.

Anda dapat meneruskan closure lain sebagai parameter ketiga dari metode `when`.
Closure tersebut akan dieksekusi jika parameter pertama bernilai `false`. Untuk
mengilustrasikan cara menggunakan fitur ini, mari kita konfigurasikan pengurutan
default dari suatu query:

```php
$sortBy = null;

$users = Db::table('users')
    ->when($sortBy, function ($query, $sortBy) {
        return $query->orderBy($sortBy);
    }, function ($query) {
        return $query->orderBy('name');
    })
    ->get();
```

## Insert (Memasukkan Data)

Query builder juga menyediakan metode `insert` untuk memasukkan data ke dalam
database. Metode `insert` menerima array berisi nama field dan nilainya untuk
proses penyimpanan:

```php
Db::table('users')->insert(
    ['email' => 'john@example.com', 'votes' => 0]
);
```

Anda bahkan dapat melewatkan array multidimensi ke metode `insert` untuk memasukkan
beberapa record sekaligus ke dalam tabel:

```php
Db::table('users')->insert([
    ['email' => 'taylor@example.com', 'votes' => 0],
    ['email' => 'dayle@example.com', 'votes' => 0]
]);
```

### Auto Increment ID

Jika tabel memiliki `ID` auto-incrementing, gunakan metode `insertGetId` untuk
memasukkan data dan mengembalikan nilai `ID` tersebut:

```php
$id = Db::table('users')->insertGetId(
    ['email' => 'john@example.com', 'votes' => 0]
);
```

## Update (Memperbarui Data)

Tentu saja, selain memasukkan record ke database, query builder juga dapat
memperbarui record yang sudah ada melalui metode `update`. Metode `update`, seperti
halnya metode `insert`, menerima array yang berisi field dan nilai yang akan
diperbarui. Anda dapat membatasi query `update` dengan klausa `where`:

```php
Db::table('users')->where('id', 1)->update(['votes' => 1]);
```

### Update atau Insert (Upsert)

Terkadang Anda mungkin ingin memperbarui record yang sudah ada di database, atau
membuat record baru jika data tersebut belum ada. Dalam hal ini, metode
`updateOrInsert` dapat digunakan. Metode `updateOrInsert` menerima dua parameter:
array kondisi untuk mencari record, dan array pasangan key-value yang berisi data
untuk diperbarui.

Metode `updateOrInsert` pertama-tama akan mencoba mencari record database yang cocok
menggunakan key dan value pada parameter pertama. Jika record ditemukan, record
tersebut diperbarui menggunakan nilai pada parameter kedua. Jika tidak ditemukan,
record baru akan dimasukkan yang merupakan gabungan dari kedua array tersebut:

```php
Db::table('users')->updateOrInsert(
    ['email' => 'john@example.com', 'name' => 'John'],
    ['votes' => '2']
);
```

### Memperbarui field JSON

Saat memperbarui field JSON, Anda dapat menggunakan sintaks `->` untuk mengakses
nilai yang sesuai di dalam objek JSON. Fitur ini hanya didukung pada MySQL 5.7+:

```php
Db::table('users')->where('id', 1)->update(['options->enabled' => true]);
```

### Increment dan Decrement Otomatis

Query builder juga menyediakan metode praktis untuk menambah (increment) atau
mengurangi (decrement) nilai dari field tertentu. Metode ini memberikan antarmuka
yang lebih ekspresif dan ringkas daripada menulis pernyataan `update` secara manual.

Kedua metode tersebut menerima setidaknya satu parameter: kolom yang ingin diubah.
Parameter kedua bersifat opsional dan mengontrol jumlah nilai yang akan ditambah
atau dikurangi:

```php
Db::table('users')->increment('votes');

Db::table('users')->increment('votes', 5);

Db::table('users')->decrement('votes');

Db::table('users')->decrement('votes', 5);
```

Anda juga dapat menentukan field tambahan untuk diperbarui selama operasi tersebut:

```php
Db::table('users')->increment('votes', 1, ['name' => 'John']);
```

## Delete (Menghapus Data)

Query builder juga dapat menghapus record dari tabel menggunakan metode `delete`.
Sebelum menggunakan `delete`, Anda dapat menambahkan klausa `where` untuk membatasi
proses penghapusan:

```php
Db::table('users')->delete();

Db::table('users')->where('votes', '>', 100)->delete();
```

Jika Anda perlu mengosongkan seluruh tabel, Anda dapat menggunakan metode `truncate`,
yang akan menghapus semua baris dan mereset nilai auto-incrementing `ID` kembali
ke nol:

```php
Db::table('users')->truncate();
```

## Pessimistic lock (Lock Pesimis)

Query builder juga menyediakan beberapa fungsi untuk membantu Anda menerapkan
`pessimistic locking` pada sintaks `select`. Untuk menerapkan `"shared lock"` pada
query, Anda dapat menggunakan metode `sharedLock`. Shared lock mencegah kolom data
yang dipilih untuk diubah sampai transaksi di-commit:

```php
Db::table('users')->where('votes', '>', 100)->sharedLock()->get();
```

Alternatif lain, Anda dapat menggunakan metode `lockForUpdate`. Gunakan lock
`"update"` untuk mencegah baris dimodifikasi atau dipilih oleh shared lock lainnya:

```php
Db::table('users')->where('votes', '>', 100)->lockForUpdate()->get();
```
