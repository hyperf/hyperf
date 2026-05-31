# Query Builder

## Pendahuluan

Query builder database Hyperf menyediakan interface yang nyaman untuk membuat dan menjalankan query. Bisa digunakan untuk sebagian besar operasi database dan kompatibel dengan semua sistem database yang didukung.

Query builder Hyperf menggunakan PDO parameter binding untuk melindungi dari SQL injection. Jadi, tidak perlu membersihkan string yang dilewatkan sebagai binding.

Berikut hanya tutorial umum. Untuk tutorial spesifik, kunjungi website resmi Laravel.
[Laravel Query Builder](https://laravel.com/docs/5.8/queries)

## Mengambil Results

```php
use Hyperf\DbConnection\Db;

$users = Db::select('SELECT * FROM user;');
$users = Db::table('user')->get();
$users = Db::table('user')->select('name', 'gender as user_gender')->get();
```

Method `Db::select()` akan mengembalikan array, dan method `get` akan mengembalikan `Hyperf\Collection\Collection`. Elemen-elemennya berupa `stdClass`, jadi Anda bisa mengakses data setiap elemen menggunakan kode berikut:

```php
<?php

foreach ($users as $user) {
    echo $user->name;
}
```

### Mengonversi Results ke Format Array

Di beberapa skenario, Anda mungkin ingin hasil query menggunakan `Array` daripada struktur objek `stdClass`. Karena `Eloquent` telah menghapus kemampuan untuk mengkonfigurasi `FetchMode` default melalui konfigurasi, Anda bisa mendengarkan event `Hyperf\Database\Events\StatementPrepared` untuk mengubah konfigurasi ini:

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

### Mengambil Satu Baris

Jika Anda ingin mendapatkan satu baris, Anda bisa menggunakan method `first`:

```php
<?php
use Hyperf\DbConnection\Db;

$row = Db::table('user')->first(); // SQL akan otomatis menyertakan limit 1
var_dump($row);
```

### Mengambil Satu Nilai

Jika Anda ingin mendapatkan satu nilai, Anda bisa menggunakan method `value`:

```php
<?php
use Hyperf\DbConnection\Db;

$id = Db::table('user')->value('id');
var_dump($id);
```

### Mengambil Satu Kolom Nilai

Jika Anda ingin mendapatkan collection yang berisi nilai dari satu kolom, Anda bisa menggunakan method `pluck`. Pada contoh di bawah, kita akan mendapatkan collection dari title dari tabel roles:

```php
<?php
use Hyperf\DbConnection\Db;

$names = Db::table('user')->pluck('name');

foreach ($names as $name) {
    echo $name;
}
```

Anda juga bisa menentukan custom key untuk collection yang dikembalikan:

```php
<?php
use Hyperf\DbConnection\Db;

$roles = Db::table('roles')->pluck('title', 'name');

foreach ($roles as $name => $title) {
    echo $title;
}
```

### Chunking Results

Jika perlu memproses ribuan records, gunakan method `chunk`. Method ini mengambil potongan kecil result set dan meneruskannya ke `Closure`. Sangat berguna saat menulis `Command` untuk memproses ribuan data. Contoh, kita membagi data tabel user per 100 record:

```php
<?php
use Hyperf\DbConnection\Db;

Db::table('user')->orderBy('id')->chunk(100, function ($users) {
    foreach ($users as $user) {
        //
    }
});
```

Anda bisa menghentikan pengambilan chunked results lebih lanjut dengan mengembalikan `false` di dalam `Closure`:

```php
use Hyperf\DbConnection\Db;

Db::table('user')->orderBy('id')->chunk(100, function ($users) {

    return false;
});
```

Jika Anda mengupdate records saat chunking, hasilnya mungkin tidak konsisten. Untuk update, gunakan `chunkById`. Method ini otomatis melakukan pagination berdasarkan primary key:

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

> Saat mengupdate atau menghapus records di chunk callback, perubahan apapun pada primary key atau foreign key bisa mempengaruhi chunk query. Ini bisa mengakibatkan records tidak termasuk dalam chunked results.

### Aggregations

Framework juga menyediakan method aggregate, seperti `count`, `max`, `min`, `avg`, `sum`.

```php
use Hyperf\DbConnection\Db;

$count = Db::table('user')->count();
```

#### Menentukan Apakah Records Ada

Selain menggunakan method `count` untuk menentukan apakah hasil dari suatu query condition ada, Anda juga bisa menggunakan method `exists` dan `doesntExist`:

```php
return Db::table('orders')->where('finalized', 1)->exists();

return Db::table('orders')->where('finalized', 1)->doesntExist();
```

## Queries

### Menentukan Select Statement

Tentu saja, Anda mungkin tidak selalu ingin mengambil semua kolom dari tabel database. Menggunakan method `select`, Anda bisa menyesuaikan `select` query statement untuk query field tertentu:

```php
$users = Db::table('user')->select('name', 'email as user_email')->get();
```

Method `distinct` memaksa query untuk mengembalikan hasil yang distinct:

```php
$users = Db::table('user')->distinct()->get();
```

Jika Anda sudah memiliki query builder instance dan ingin menambahkan field ke query statement yang ada, Anda bisa menggunakan method `addSelect`:

```php
$query = Db::table('users')->select('name');

$users = $query->addSelect('age')->get();
```

## Raw Expressions

Terkadang Anda perlu raw expressions dalam query, misalnya `COUNT(0) AS count`. Gunakan method `raw` untuk ini.

```php
use Hyperf\DbConnection\Db;

$res = Db::table('user')->select('gender', Db::raw('COUNT(0) AS `count`'))->groupBy('gender')->get();
```

### Forcing Indexes

Lebih dari 90% masalah slow query database disebabkan oleh index yang salah. Beberapa query terjadi karena `query optimizer` server database tidak menggunakan index terbaik. Dalam kasus ini, Anda perlu menggunakan forced index:

```php
Db::table(Db::raw("{$table} FORCE INDEX({$index})"));
```

### Raw Methods

Anda bisa menggunakan method berikut sebagai pengganti `Db::raw` untuk memasukkan raw expressions ke berbagai bagian query.

Method `selectRaw` bisa digunakan sebagai pengganti `select(Db::raw(...))`. Argumen kedua dari method ini bersifat opsional dan berupa array binding parameters:

```php
$orders = Db::table('order')
    ->selectRaw('price * ? as price_with_tax', [1.0825])
    ->get();
```

Method `whereRaw` dan `orWhereRaw` memasukkan raw `where` clauses ke dalam query Anda. Argumen kedua dari kedua method ini juga opsional dan berupa array binding parameters:

```php
$orders = Db::table('order')
    ->whereRaw('price > IF(state = "TX", ?, 100)', [200])
    ->get();
```

Method `havingRaw` dan `orHavingRaw` bisa digunakan untuk mengatur raw strings sebagai nilai dari statement `having`:

```php
$orders = Db::table('order')
    ->select('department', Db::raw('SUM(price) as total_sales'))
    ->groupBy('department')
    ->havingRaw('SUM(price) > ?', [2500])
    ->get();
```

Method `orderByRaw` bisa digunakan untuk mengatur raw strings sebagai nilai dari clause `order by`:

```php
$orders = Db::table('order')
    ->orderByRaw('updated_at - created_at DESC')
    ->get();
```

## Table Joins

### Inner Join Clause

Query builder juga bisa menulis method `join`. Untuk melakukan "inner join" dasar, Anda bisa menggunakan method `join` pada query builder instance. Argumen pertama yang diteruskan ke method `join` adalah nama tabel yang perlu Anda join, sementara argumen lainnya menentukan field constraints untuk join. Anda juga bisa melakukan join beberapa tabel dalam satu query:

```php
$users = Db::table('users')
    ->join('contacts', 'users.id', '=', 'contacts.user_id')
    ->join('orders', 'users.id', '=', 'orders.user_id')
    ->select('users.*', 'contacts.phone', 'orders.price')
    ->get();
```

### Left Join

Jika Anda ingin menggunakan "left join" atau "right join" daripada "inner join", Anda bisa menggunakan method `leftJoin` atau `rightJoin`. Kedua method ini digunakan dengan cara yang sama seperti method `join`:

```php
$users = Db::table('users')
    ->leftJoin('posts', 'users.id', '=', 'posts.user_id')
    ->get();
$users = Db::table('users')
    ->rightJoin('posts', 'users.id', '=', 'posts.user_id')
    ->get();
```

### Cross Join Clause

Gunakan method `crossJoin` dengan nama tabel yang ingin Anda join untuk melakukan "cross join". Cross join menghasilkan Cartesian product antara tabel pertama dan tabel yang di-join:

```php
$users = Db::table('sizes')
    ->crossJoin('colours')
    ->get();
```

### Advanced Join Clauses

Anda bisa menentukan statement `join` yang lebih advanced. Misalnya, berikan `Closure` sebagai argumen kedua ke method `join`. `Closure` ini menerima objek `JoinClause` untuk menentukan constraints dalam statement `join`:

```php
Db::table('users')
    ->join('contacts', function ($join) {
        $join->on('users.id', '=', 'contacts.user_id')->orOn(...);
    })
    ->get();
```

Jika Anda ingin menggunakan clause bergaya "where" pada joins, Anda bisa menggunakan method `where` dan `orWhere` pada join. Method ini membandingkan kolom dan nilai, bukan membandingkan kolom dengan kolom:

```php
Db::table('users')
    ->join('contacts', function ($join) {
        $join->on('users.id', '=', 'contacts.user_id')
                ->where('contacts.user_id', '>', 5);
    })
    ->get();
```

### Subquery Joins

Anda bisa menggunakan method `joinSub`, `leftJoinSub`, dan `rightJoinSub` untuk melakukan join query sebagai subquery. Masing-masing method ini menerima tiga argumen: subquery, alias tabel, dan Closure yang mendefinisikan field yang di-join:

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

## Unions

Query builder juga menyediakan shortcut untuk "union" dua query. Misalnya, Anda bisa membuat query terlebih dahulu dan kemudian menggunakan method `union` untuk menggabungkannya dengan query kedua:

```php
$first = Db::table('users')->whereNull('first_name');

$users = Db::table('users')
    ->whereNull('last_name')
    ->union($first)
    ->get();
```

## Where Clauses

### Simple Where Clauses

Saat membangun query `where`, gunakan method `where`. Cara paling dasar: tiga argumen, nama kolom, operator, dan nilai pembanding.

Contohnya, berikut adalah query untuk memverifikasi bahwa nilai field `gender` sama dengan 1:

```php
$users = Db::table('user')->where('gender', '=', 1)->get();
```

Untuk kemudahan, jika hanya membandingkan kesamaan nilai, Anda bisa memberikan nilai langsung sebagai argumen kedua:

```php
$users = Db::table('user')->where('gender', 1)->get();
```

Tentu saja, Anda juga bisa menggunakan operator lain untuk menulis `where` clauses:

```php
$users = Db::table('users')->where('gender', '>=', 0)->get();

$users = Db::table('users')->where('gender', '<>', 1)->get();

$users = Db::table('users')->where('name', 'like', 'T%')->get();
```

Anda juga bisa memberikan array kondisi ke fungsi `where`:

```php
$users = Db::table('user')->where([
    ['status', '=', '1'],
    ['gender', '=', '1'],
])->get();
```

Anda juga bisa menggunakan Closure untuk membuat array query:

```php
$users = Db::table('user')->where([
    ['status', '=', '1'],
    ['gender', '=', '1'],
    [function ($query) {
        $query->where('type', 3)->orWhere('type', 6);
    }]
])->get();
```

### Or Clauses

Anda bisa merangkai `where` constraints bersama-sama, atau menambahkan `or` clauses ke query Anda. Method `orWhere` menerima argumen yang sama dengan method `where`:

```php
$users = Db::table('user')
    ->where('gender', 1)
    ->orWhere('name', 'John')
    ->get();
```

### Other Where Clauses

#### whereBetween

Method `whereBetween` memverifikasi bahwa nilai suatu field berada di antara dua nilai yang diberikan:

```php
$users = Db::table('users')->whereBetween('votes', [1, 100])->get();
```

#### whereNotBetween

Method `whereNotBetween` memverifikasi bahwa nilai suatu field berada di luar dua nilai yang diberikan:

```php
$users = Db::table('users')->whereNotBetween('votes', [1, 100])->get();
```

#### whereIn / whereNotIn

Method `whereIn` memverifikasi bahwa nilai suatu field ada dalam array yang diberikan:

```php
$users = Db::table('users')->whereIn('id', [1, 2, 3])->get();
```

Method `whereNotIn` memverifikasi bahwa nilai suatu field tidak ada dalam array yang diberikan:

```php
$users = Db::table('users')->whereNotIn('id', [1, 2, 3])->get();
```

### Parameter Grouping

Terkadang Anda perlu `where` clauses yang lebih advanced, seperti "where exists" atau nested groups. Query builder juga bisa menanganinya. Contoh nested constraint grouping:

```php
Db::table('users')->where('name', '=', 'John')
    ->where(function ($query) {
        $query->where('votes', '>', 100)
                ->orWhere('title', '=', 'Admin');
    })
    ->get();
```

Seperti yang Anda lihat, dengan memberikan `Closure` ke dalam method `where`, Anda membangun grouping constraint. `Closure` menerima query builder instance, yang bisa Anda gunakan untuk mengatur constraints yang harus disertakan. Contoh di atas akan menghasilkan SQL berikut:

```sql
select * from users where name = 'John' and (votes > 100 or title = 'Admin')
```

> Anda harus mengelompokkan constraints ini dengan panggilan `orWhere` untuk menghindari perilaku yang tidak diharapkan saat global scopes diterapkan.

#### Where Exists Clauses

Method `whereExists` memungkinkan Anda menulis `where exists SQL` statements. Method `whereExists` menerima `Closure`, yang menerima query builder instance, memungkinkan Anda mendefinisikan query yang akan ditempatkan di dalam clause `exists`:

```php
Db::table('users')->whereExists(function ($query) {
    $query->select(Db::raw(1))
            ->from('orders')
            ->whereRaw('orders.user_id = users.id');
})
->get();
```

Query di atas akan menghasilkan SQL statement berikut:

```sql
select * from users
where exists (
    select 1 from orders where orders.user_id = users.id
)
```

#### JSON Where Clauses

Hyperf juga mendukung query tipe field `JSON` (hanya pada database yang mendukung tipe `JSON`).

```php
$users = Db::table('users')
    ->where('options->language', 'en')
    ->get();

$users = Db::table('users')
    ->where('preferences->dining->meal', 'salad')
    ->get();
```

Anda juga bisa menggunakan `whereJsonContains` untuk query `JSON` arrays:

```php
$users = Db::table('users')
    ->whereJsonContains('options->languages', 'en')
    ->get();
```

Anda bisa menggunakan `whereJsonLength` untuk query panjang dari `JSON` array:

```php
$users = Db::table('users')
    ->whereJsonLength('options->languages', 0)
    ->get();

$users = Db::table('users')
    ->whereJsonLength('options->languages', '>', 1)
    ->get();
```

## Ordering, Grouping, Limit, & Offset

### orderBy

Method `orderBy` memungkinkan Anda mengurutkan result set berdasarkan field tertentu. Argumen pertama `orderBy` harus berupa field yang ingin Anda urutkan, dan argumen kedua mengontrol arah pengurutan, bisa `asc` atau `desc`:

```php
$users = Db::table('users')
    ->orderBy('name', 'desc')
    ->get();
```

### latest / oldest

Method `latest` dan `oldest` memungkinkan Anda dengan mudah mengurutkan results berdasarkan tanggal. Secara default, method ini menggunakan `created_at` sebagai kolom untuk diurutkan. Tentu saja, Anda juga bisa memberikan nama kolom kustom:

```php
$user = Db::table('users')->latest()->first();
```

### inRandomOrder

Method `inRandomOrder` bisa digunakan untuk mengurutkan results secara acak. Misalnya, Anda bisa menggunakan method ini untuk mengambil user secara acak:

```php
$randomUser = Db::table('users')->inRandomOrder()->first();
```

### groupBy / having

Method `groupBy` dan `having` bisa digunakan untuk mengelompokkan results. Penggunaan method `having` sangat mirip dengan method `where`:

```php
$users = Db::table('users')
    ->groupBy('account_id')
    ->having('account_id', '>', 100)
    ->get();
```

Anda bisa memberikan beberapa argumen ke method `groupBy`:

```php
$users = Db::table('users')
    ->groupBy('first_name', 'status')
    ->having('account_id', '>', 100)
    ->get();
```

> Untuk sintaks `having` yang lebih advanced, lihat method `havingRaw`.

### skip / take

Untuk membatasi jumlah hasil yang dikembalikan atau untuk melewati sejumlah hasil, Anda bisa menggunakan method `skip` dan `take`:

```php
$users = Db::table('users')->skip(10)->take(5)->get();
```

Atau, Anda bisa menggunakan method `limit` dan `offset`:

```php
$users = Db::table('users')->offset(10)->limit(5)->get();
```

## Conditional Clauses

Terkadang Anda mungkin ingin clauses hanya diterapkan ketika sesuatu bernilai true. Misalnya, Anda mungkin hanya ingin menerapkan statement `where` jika nilai tertentu ada dalam request. Anda bisa melakukannya dengan menggunakan method `when`:

```php
$role = $request->input('role');

$users = Db::table('users')
    ->when($role, function ($query, $role) {
        return $query->where('role_id', $role);
    })
    ->get();
```

Method `when` hanya mengeksekusi closure yang diberikan ketika argumen pertama bernilai `true`. Jika argumen pertama bernilai `false`, closure tidak akan dieksekusi.

Anda bisa memberikan closure lain sebagai argumen ketiga ke method `when`. Closure ini akan dieksekusi jika argumen pertama bernilai `false`. Untuk mengilustrasikan cara menggunakan fitur ini, mari konfigurasi default sorting untuk query:

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

## Inserts

Query builder juga menyediakan method `insert` untuk memasukkan records ke database. Method `insert` menerima array nama field dan nilai yang akan di-insert:

```php
Db::table('users')->insert(
    ['email' => 'john@example.com', 'votes' => 0]
);
```

Anda bahkan bisa memberikan array of arrays ke method `insert` untuk memasukkan beberapa records ke dalam tabel:

```php
Db::table('users')->insert([
    ['email' => 'taylor@example.com', 'votes' => 0],
    ['email' => 'dayle@example.com', 'votes' => 0]
]);
```

### Auto-incrementing IDs

Jika tabel memiliki `ID` auto-increment, gunakan method `insertGetId` untuk memasukkan record dan mengembalikan nilai `ID`:

```php
$id = Db::table('users')->insertGetId(
    ['email' => 'john@example.com', 'votes' => 0]
);
```

## Updates

Selain memasukkan records ke database, query builder juga bisa memperbarui records yang ada menggunakan method `update`. Method `update`, seperti method `insert`, menerima array kolom dan nilai untuk diperbarui. Anda bisa membatasi `update` queries menggunakan `where` clause:

```php
Db::table('users')->where('id', 1)->update(['votes' => 1]);
```

### Update or Insert

Terkadang Anda mungkin ingin memperbarui record yang ada di database, atau membuatnya jika tidak ada record yang cocok. Dalam kasus ini, Anda bisa menggunakan method `updateOrInsert`. Method `updateOrInsert` menerima dua argumen: array kondisi untuk menemukan record, dan array key-value pairs yang berisi perubahan pada record.

Method `updateOrInsert` pertama-tama akan mencoba mencari database record yang cocok menggunakan key-value pairs dari argumen pertama. Jika record ada, maka akan memperbarui record dengan nilai dari argumen kedua. Jika record tidak ditemukan, record baru akan di-insert dengan data gabungan dari kedua array:

```php
Db::table('users')->updateOrInsert(
    ['email' => 'john@example.com', 'name' => 'John'],
    ['votes' => '2']
);
```

### Memperbarui JSON Fields

Saat memperbarui `JSON` fields, Anda bisa menggunakan sintaks `->` untuk mengakses nilai yang sesuai dalam objek `JSON`. Operasi ini hanya didukung di MySQL 5.7+:

```php
Db::table('users')->where('id', 1)->update(['options->enabled' => true]);
```

### Increments & Decrements

Query builder juga menyediakan method yang nyaman untuk menambah atau mengurangi nilai dari kolom tertentu. Method ini menyediakan interface yang lebih ekspresif dan ringkas daripada menulis `update` statements secara manual.

Kedua method menerima setidaknya satu argumen: kolom yang akan dimodifikasi. Argumen kedua bersifat opsional, digunakan untuk mengontrol jumlah kenaikan atau penurunan kolom:

```php
Db::table('users')->increment('votes');

Db::table('users')->increment('votes', 5);

Db::table('users')->decrement('votes');

Db::table('users')->decrement('votes', 5);
```

Anda juga bisa menentukan kolom tambahan untuk diperbarui selama operasi:

```php
Db::table('users')->increment('votes', 1, ['name' => 'John']);
```

## Deletes

Query builder juga bisa menggunakan method `delete` untuk menghapus records dari tabel. Sebelum menggunakan `delete`, Anda bisa menambahkan `where` clauses untuk membatasi statement `delete`:

```php
Db::table('users')->delete();

Db::table('users')->where('votes', '>', 100)->delete();
```

Jika Anda perlu mengosongkan tabel, Anda bisa menggunakan method `truncate`, yang akan menghapus semua baris dan mereset auto-increment `ID` ke nol:

```php
Db::table('users')->truncate();
```

## Pessimistic Locking

Query builder juga menyertakan beberapa fungsi untuk membantu Anda mencapai "pessimistic locking" pada `select` statements. Jika Anda ingin menempatkan "shared lock" pada query Anda, Anda bisa menggunakan method `sharedLock`. Shared lock mencegah baris data yang dipilih dari modifikasi sampai transaction di-commit:

```php
Db::table('users')->where('votes', '>', 100)->sharedLock()->get();
```

Atau, Anda bisa menggunakan method `lockForUpdate`. Menggunakan "update" lock mencegah baris dari modifikasi atau dipilih dengan shared lock lain:

```php
Db::table('users')->where('votes', '>', 100)->lockForUpdate()->get();
```
