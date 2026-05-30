# Model Association

## Mendefinisikan Asosiasi

Asosiasi disajikan sebagai method dalam class model `Hyperf`. Seperti halnya
model `Hyperf` itu sendiri, asosiasi juga dapat digunakan sebagai `query
builder` yang kuat, menyediakan kemampuan chaining dan query yang andal.
Sebagai contoh, kita dapat menambahkan batasan pada pemanggilan berantai yang
berasosiasi dengan role:

```php
$user->role()->where('level', 1)->get();
```

### One-to-One

One-to-one adalah relasi yang paling dasar. Sebagai contoh, model `User`
mungkin berasosiasi dengan model `Role`. Untuk mendefinisikan asosiasi ini,
kita perlu menulis method `role` di dalam model `User`. Panggil method `hasOne`
di dalam method `role` tersebut dan kembalikan hasilnya:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    public function role()
    {
        return $this->hasOne(Role::class, 'user_id', 'id');
    }
}
```

Parameter pertama dari method `hasOne` adalah nama class dari model yang
diasosiasikan. Setelah asosiasi model didefinisikan, kita dapat menggunakan
`dynamic properties` dari `Hyperf` untuk mendapatkan data terkait. Dynamic
properties memungkinkan Anda mengakses method relasi layaknya property yang
didefinisikan di dalam model:

```php
$role = User::query()->find(1)->role;
```

### One-to-Many

Asosiasi "one-to-many" digunakan untuk mendefinisikan model tunggal yang
terkait dengan sejumlah model lain. Sebagai contoh, seorang penulis (author)
mungkin telah menulis banyak buku (books). Sama seperti relasi `Hyperf`
lainnya, definisi relasi one-to-many dilakukan dengan menulis method di dalam
model `Hyperf`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    public function books()
    {
        return $this->hasMany(Book::class, 'user_id', 'id');
    }
}
```

Ingatlah bahwa `Hyperf` akan secara otomatis menentukan property foreign key
dari model `Book`. Secara konvensi, `Hyperf` akan menggunakan bentuk "snake
case" dari nama model pemilik, ditambah akhiran `_id` sebagai field foreign
key. Oleh karena itu, pada contoh di atas, `Hyperf` akan mengasumsikan bahwa
foreign key yang sesuai untuk `User` pada model `Book` adalah `user_id`.

Setelah relasi didefinisikan, koleksi buku dapat diperoleh dengan mengakses
property `books` pada model `User`. Ingat, karena Hyperf menyediakan "dynamic
properties", kita dapat mengakses method asosiasi layaknya property pada
model:

```php
$books = User::query()->find(1)->books;

foreach ($books as $book) {
    //
}
```

Tentu saja, karena semua asosiasi juga dapat digunakan sebagai query builder,
Anda dapat menggunakan pemanggilan berantai untuk menambahkan batasan
tambahan ke method `books`:

```php
$book = User::query()->find(1)->books()->where('title', 'Mastering the Hyperf framework in one month')->first();
```

### One-to-Many (Sebaliknya)

Sekarang setelah kita bisa mendapatkan semua karya dari seorang penulis, mari
kita definisikan asosiasi untuk mendapatkan penulisnya melalui buku. Asosiasi
ini adalah kebalikan dari asosiasi `hasMany` dan perlu didefinisikan pada child
model menggunakan method `belongsTo`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class Book extends Model
{
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
```

Setelah relasi ini didefinisikan, kita dapat memperoleh model `User` terkait
dengan mengakses "dynamic property" `author` pada model `Book`:

```php
$book = Book::find(1);

echo $book->author->name;
```

### Many-to-Many

Asosiasi many-to-many sedikit lebih rumit daripada asosiasi `hasOne` dan
`hasMany`. Sebagai contoh, seorang user dapat memiliki banyak role, dan
role tersebut juga dapat dimiliki oleh user lain. Contohnya, banyak user
mungkin memiliki role "Administrator". Untuk mendefinisikan asosiasi ini,
diperlukan tiga tabel database: `users`, `roles`, dan `role_user`. Tabel
`role_user` dinamai secara alfabetis berdasarkan kedua model yang
diasosiasikan, dan berisi field `user_id` dan `role_id`.

Asosiasi many-to-many didefinisikan dengan mengembalikan hasil dari method
internal `belongsToMany`. Sebagai contoh, kita definisikan method `roles` pada
model `User`:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}
```

Setelah relasi didefinisikan, Anda dapat mendapatkan role dari user melalui
dynamic property `roles`:

```php
$user = User::query()->find(1);

foreach ($user->roles as $role) {
    //
}
```

Tentu saja, seperti model relasi lainnya, Anda dapat menggunakan method
`roles` untuk menambahkan batasan ke query menggunakan pemanggilan berantai:

```php
$roles = User::find(1)->roles()->orderBy('name')->get();
```

Seperti yang disebutkan sebelumnya, untuk menentukan nama tabel penghubung
(join table) relasi, `Hyperf` akan menggabungkan nama kedua model relasi dalam
urutan alfabetis. Tentu saja, Anda juga dapat mengabaikan konvensi ini dan
meneruskan parameter kedua ke method `belongsToMany`:

```php
return $this->belongsToMany(Role::class, 'role_user');
```

Selain menyesuaikan nama tabel penghubung, Anda juga dapat mendefinisikan nama
kunci (key) dari field di dalam tabel dengan meneruskan parameter tambahan ke
method `belongsToMany`. Parameter ketiga adalah nama foreign key dari model
yang mendefinisikan asosiasi ini di dalam tabel penghubung, dan parameter
keempat adalah nama foreign key dari model lain di dalam tabel penghubung:

```php
return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
```

#### Mendapatkan Field Tabel Perantara (Intermediate Table)

Seperti yang baru saja Anda pelajari, relasi many-to-many memerlukan tabel
perantara sebagai pendukung, dan `Hyperf` menyediakan beberapa method yang
berguna untuk berinteraksi dengan tabel ini. Sebagai contoh, katakanlah objek
`User` kita memiliki beberapa objek `Role` yang berasosiasi dengannya. Setelah
mendapatkan objek asosiasi ini, data dalam tabel perantara dapat diakses
menggunakan atribut `pivot` pada model:

```php
$user = User::find(1);

foreach ($user->roles as $role) {
    echo $role->pivot->created_at;
}
```

Perlu diperhatikan bahwa setiap objek model `Role` yang kita dapatkan secara
otomatis diberikan atribut `pivot`, yang mewakili objek model dari tabel
perantara dan dapat digunakan seperti model `Hyperf` lainnya.

Secara default, objek `pivot` hanya berisi primary key dari kedua model
relasi. Jika Anda memiliki field tambahan di tabel perantara, Anda harus
menentukannya saat mendefinisikan relasi:

```php
return $this->belongsToMany(Role::class)->withPivot('column1', 'column2');
```

Jika Anda ingin tabel perantara secara otomatis mengelola timestamp
`created_at` dan `updated_at`, tambahkan method `withTimestamps` saat
mendefinisikan asosiasi:

```php
return $this->belongsToMany(Role::class)->withTimestamps();
```

#### Menyesuaikan Nama Atribut `pivot`

Seperti yang disebutkan sebelumnya, property dari tabel perantara dapat diakses
menggunakan atribut `pivot`. Namun, Anda bebas menyesuaikan nama atribut ini
agar lebih mencerminkan penggunaannya di aplikasi Anda.

Sebagai contoh, jika aplikasi Anda menyertakan user yang dapat berlangganan
(subscribe), mungkin terdapat relasi many-to-many antara user dan blog. Jika
demikian, Anda mungkin ingin menamai aksesor tabel perantara sebagai
`subscription` alih-alih `pivot`. Hal ini dapat dilakukan menggunakan method
`as` saat mendefinisikan relasi:

```php
return $this->belongsToMany(Podcast::class)->as('subscription')->withTimestamps();
```

Setelah didefinisikan, Anda dapat mengakses data tabel perantara dengan nama kustom:

```php
$users = User::with('podcasts')->get();

foreach ($users->flatMap->podcasts as $podcast) {
    echo $podcast->subscription->created_at;
}
```

#### Memfilter Relasi Berdasarkan Tabel Perantara

Saat mendefinisikan relasi, Anda juga dapat menggunakan method `wherePivot`
dan `wherePivotIn` untuk memfilter hasil yang dikembalikan oleh
`belongsToMany`:

```php
return $this->belongsToMany('App\Role')->wherePivot('approved', 1);

return $this->belongsToMany('App\Role')->wherePivotIn('priority', [1, 2]);
```

## Preloading

Saat mengakses relasi `Hyperf` sebagai atribut, data terkait akan di-load secara
"lazy loaded". Ini berarti data terkait tidak benar-benar dimuat sampai property
tersebut diakses untuk pertama kalinya. Namun, `Hyperf` dapat melakukan
"preload" pada asosiasi anak saat melakukan query pada model induk. Eager
loading dapat meredakan masalah query N+1. Untuk menggambarkan masalah query
N+1, perhatikan model `User` yang berasosiasi dengan `Role`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    public function role()
    {
        return $this->hasOne(Role::class, 'user_id', 'id');
    }
}
```

Sekarang, mari kita dapatkan semua user dan role yang sesuai untuk mereka:

```php
$users = User::query()->get();

foreach ($users as $user){
    echo $user->role->name;
}
```

Loop ini akan menjalankan query untuk mendapatkan semua user, lalu menjalankan
query untuk mendapatkan role bagi setiap user. Jika kita memiliki 10 orang,
loop ini akan menjalankan 11 query: 1 untuk user dan 10 query tambahan untuk
role.

Untungnya, kita dapat meminimalkan operasi tersebut menjadi hanya 2 query saja
menggunakan eager loading. Pada saat melakukan query, Anda dapat menggunakan
method `with` untuk menentukan asosiasi mana yang ingin Anda preload:

```php
$users = User::query()->with('role')->get();

foreach ($users as $user){
    echo $user->role->name;
}
```

Dalam contoh ini, hanya dua query yang dijalankan:

```
SELECT * FROM `user`;

SELECT * FROM `role` WHERE id in (1, 2, 3, ...);
```

## Polymorphic Association

Polymorphic association memungkinkan model target untuk berasosiasi dengan
beberapa model menggunakan bantuan hubungan asosiasi.

### One-to-One (Polymorphic)

#### Struktur Tabel

Asosiasi satu-ke-satu polimorfik (one-to-one polymorphic association) mirip
dengan asosiasi one-to-one biasa, namun model target dapat dimiliki oleh
beberapa model dalam satu asosiasi saja. Sebagai contoh, `Book` dan `User` mungkin
berbagi relasi ke model `Image`. Menggunakan one-to-one polymorphic
association memungkinkan penggunaan daftar gambar yang unik baik untuk `Book`
maupun `User`. Mari kita lihat struktur tabelnya terlebih dahulu:

```
book
  id - integer
  title - string

user 
  id - integer
  name - string

image
  id - integer
  url - string
  imageable_id - integer
  imageable_type - string
```

Field `imageable_id` pada tabel `image` akan memiliki arti berbeda tergantung
pada `imageable_type`. Secara default, `imageable_type` adalah nama class model
terkait secara langsung.

#### Contoh Model

```php
<?php
namespace App\Model;

class Image extends Model
{
    public function imageable()
    {
        return $this->morphTo();
    }
}

class Book extends Model
{
    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}

class User extends Model
{
    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}
```

#### Mendapatkan Asosiasi

Setelah mendefinisikan model seperti di atas, kita dapat memperoleh model yang
sesuai melalui relasi model tersebut.

Sebagai contoh, kita mengambil gambar milik seorang user.

```php
use App\Model\User;

$user = User::find(1);

$image = $user->image;
```

Atau kita bisa mendapatkan data model yang sesuai dengan gambar tersebut.
`imageable` akan mendapatkan `User` atau `Book` yang sesuai berdasarkan
`imageable_type`.

```php
use App\Model\Image;

$image = Image::find(1);

$imageable = $image->imageable;
```

### One-to-Many (Polymorphic)

#### Contoh Model

```php
<?php
namespace App\Model;

class Image extends Model
{
    public function imageable()
    {
        return $this->morphTo();
    }
}

class Book extends Model
{
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}

class User extends Model
{
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
```

#### Mendapatkan Asosiasi

Mendapatkan semua gambar milik user:

```php
use App\Model\User;

$user = User::query()->find(1);
foreach ($user->images as $image) {
    // ...
}
```

### Pemetaan Polimorfik Kustom (Custom Polymorphic Mapping)

Secara default, framework mengharuskan `type` menyimpan nama class model yang
sesuai. Sebagai contoh, `imageable_type` di atas harus berupa `User::class` dan
`Book::class`, namun dalam aplikasi nyata hal ini sering kali kurang praktis.
Oleh karena itu, kita dapat menyesuaikan relasi pemetaan (mapping) untuk
memisahkan (decouple) database dengan struktur internal aplikasi.

```php
use App\Model;
use Hyperf\Database\Model\Relations\Relation;
Relation::morphMap([
    'user' => Model\User::class,
    'book' => Model\Book::class,
]);
```

Karena `Relation::morphMap` akan menetap di dalam memori setelah dimodifikasi,
kita dapat membuat pemetaan relasi yang sesuai saat project dimulai. Kita dapat
membuat listener berikut:

```php
<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Listener;

use App\Model;
use Hyperf\Database\Model\Relations\Relation;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;

#[Listener]
class MorphMapRelationListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event)
    {
        Relation::morphMap([
            'user' => Model\User::class,
            'book' => Model\Book::class,
        ]);
    }
}
```

### Melakukan Nested Preloading pada Asosiasi `morphTo`

Jika Anda ingin memuat relasi `morphTo`, bersama dengan relasi bersarang
(nested relationship) dari berbagai entitas yang mungkin dikembalikan oleh
relasi tersebut, Anda dapat menggunakan method `with` yang digabungkan dengan
method `morphWith` dari relasi `morphTo`.

Sebagai contoh, kita berencana melakukan preload relasi `book.user` dari `image`:

```php

use App\Model\Book;
use App\Model\Image;
use Hyperf\Database\Model\Relations\MorphTo;

$images = Image::query()->with([
    'imageable' => function (MorphTo $morphTo) {
        $morphTo->morphWith([
            Book::class => ['user'],
        ]);
    },
])->get();
```

Query SQL yang sesuai adalah sebagai berikut:

```sql
// Search all pictures
select * from `images`;
// Query the user list corresponding to the image
select * from `user` where `user`.`id` in (1, 2);
// Query the list of books corresponding to the image
select * from `book` where `book`.`id` in (1, 2, 3);
// Query the user list corresponding to the book list
select * from `user` where `user`.`id` in (1, 2);
```

### Query Relasi Polimorfik

Untuk memeriksa keberadaan asosiasi `MorphTo`, Anda dapat menggunakan method
`whereHasMorph` beserta method padanannya:

Contoh berikut akan melakukan query pada daftar gambar yang memiliki ID user atau
buku bernilai 1.

```php
use App\Model\Book;
use App\Model\Image;
use App\Model\User;
use Hyperf\Database\Model\Builder;

$images = Image::query()->whereHasMorph(
    'imageable',
    [
        User::class,
        Book::class,
    ],
    function (Builder $query) {
        $query->where('imageable_id', 1);
    }
)->get();
```
