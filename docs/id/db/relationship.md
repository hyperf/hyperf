# Model Relationships

## Mendefinisikan Relationships

Relationships disajikan sebagai method di class model `Hyperf`. Seperti model itu sendiri, relationships juga bisa dipakai sebagai `query builders` yang powerful dengan method chaining. Misalnya, kita bisa menambahkan constraint ke method chaining dari role relationship:

```php
$user->role()->where('level', 1)->get();
```

### One To One

One-to-one relationship adalah asosiasi yang paling dasar. Misalnya, model `User` mungkin terkait dengan model `Role`. Untuk mendefinisikan relationship ini, kita perlu menulis method `role` di model `User`. Di dalam method `role`, panggil method `hasOne` dan kembalikan hasilnya:

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

Argumen pertama dari method `hasOne` adalah class name dari associated model. Setelah model relationship didefinisikan, kita bisa menggunakan `Hyperf` dynamic attribute untuk mendapatkan related records. Dynamic attributes memungkinkan Anda mengakses relationship methods seolah-olah itu adalah property yang didefinisikan pada model:

```php
$role = User::query()->find(1)->role;
```

### One To Many

Relationship "one-to-many" digunakan untuk mendefinisikan bahwa satu model memiliki sejumlah model terkait lainnya. Misalnya, seorang penulis bisa menulis banyak buku. Seperti semua `Hyperf` relationships lainnya, one-to-many relationship juga didefinisikan dengan menulis method di model `Hyperf`:

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

`Hyperf` otomatis menentukan foreign key attribute untuk model `Book`. Berdasarkan konvensi, `Hyperf` menggunakan bentuk "snake_case" dari nama model pemilik, ditambah suffix `_id`. Jadi dalam contoh di atas, foreign key dari `User` ke `Book` adalah `user_id`.

Setelah relationship didefinisikan, Anda bisa mendapatkan collection of books dengan mengakses property `books` dari model `User`. Ingat, karena Hyperf menyediakan "dynamic attributes", kita bisa mengakses relationship methods seolah-olah itu adalah property dari model:

```php
$books = User::query()->find(1)->books;

foreach ($books as $book) {
    //
}
```

Tentu saja, karena semua relationships juga bisa digunakan sebagai query builders, Anda bisa menggunakan method chaining untuk menambahkan constraint tambahan ke method `books`:

```php
$book = User::query()->find(1)->books()->where('title', 'Mastering Hyperf Framework in One Month')->first();
```

### One To Many (Inverse)

Sekarang kita bisa mendapatkan semua karya dari seorang penulis, mari kita definisikan asosiasi untuk mendapatkan penulis dari buku. Relationship ini adalah kebalikan dari relationship `hasMany` dan perlu didefinisikan menggunakan method `belongsTo` di child model:

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

Setelah relationship ini didefinisikan, kita bisa mengambil model `User` yang terkait dengan mengakses "dynamic attribute" `author` dari model `Book`:

```php
$book = Book::find(1);

echo $book->author->name;
```

### Many To Many

Many-to-many relationships sedikit lebih kompleks daripada relationship `hasOne` dan `hasMany`. Misalnya, seorang user bisa memiliki banyak roles, dan roles ini juga digunakan bersama oleh user lain. Misalnya, banyak user mungkin memiliki role "Administrator". Untuk mendefinisikan relationship ini, diperlukan tiga tabel database: `users`, `roles`, dan `role_user`. Penamaan tabel `role_user` didasarkan pada dua model yang terkait dalam urutan alfabet, dan berisi field `user_id` dan `role_id`.

Many-to-many relationship didefinisikan oleh hasil yang dikembalikan dari pemanggilan method internal `belongsToMany`. Misalnya, kita mendefinisikan method `roles` di model `User`:

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

Setelah relationship didefinisikan, Anda bisa mendapatkan user roles melalui dynamic attribute `roles`:

```php
$user = User::query()->find(1);

foreach ($user->roles as $role) {
    //
}
```

Tentu saja, seperti semua associated models lainnya, Anda bisa menggunakan method `roles` dan menggunakan method chaining untuk menambahkan constraint ke query statement:

```php
$roles = User::find(1)->roles()->orderBy('name')->get();
```

Seperti yang disebutkan sebelumnya, untuk menentukan nama tabel untuk relationship's join table, `Hyperf` akan menggabungkan nama dua model yang terkait dalam urutan alfabet. Tentu saja, Anda juga bisa tidak menggunakan konvensi ini dengan memberikan argumen kedua ke method `belongsToMany`:

```php
return $this->belongsToMany(Role::class, 'role_user');
```

Selain menyesuaikan nama join table, Anda juga bisa mendefinisikan key names dari field di tabel tersebut dengan memberikan argumen tambahan ke method `belongsToMany`. Argumen ketiga adalah foreign key name dari model yang mendefinisikan relationship di join table, dan argumen keempat adalah foreign key name dari model lainnya di join table:

```php
return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
```

#### Mengakses Intermediate Table Fields

Seperti yang baru Anda pelajari, many-to-many relationship memerlukan intermediate table sebagai pendukung. `Hyperf` menyediakan beberapa method yang berguna untuk berinteraksi dengan tabel ini. Misalnya, asumsikan objek `User` kita terkait dengan banyak objek `Role`. Setelah mengambil objek terkait ini, Anda bisa mengakses data intermediate table menggunakan atribut `pivot` dari model:

```php
$user = User::find(1);

foreach ($user->roles as $role) {
    echo $role->pivot->created_at;
}
```

Perlu dicatat bahwa setiap objek model `Role` yang kita ambil secara otomatis diberikan atribut `pivot`, yang merepresentasikan model object dari intermediate table dan bisa digunakan seperti model `Hyperf` lainnya.

Secara default, objek `pivot` hanya berisi primary keys dari dua model yang terkait. Jika intermediate table Anda memiliki field tambahan lainnya, Anda harus secara eksplisit menentukannya saat mendefinisikan relationship:

```php
return $this->belongsToMany(Role::class)->withPivot('column1', 'column2');
```

Jika Anda ingin intermediate table secara otomatis mempertahankan timestamps `created_at` dan `updated_at`, cukup lampirkan method `withTimestamps` saat mendefinisikan relationship:

```php
return $this->belongsToMany(Role::class)->withTimestamps();
```

#### Menyesuaikan Nama Atribut `pivot`

Seperti yang telah disebutkan sebelumnya, attributes dari intermediate table bisa diakses menggunakan atribut `pivot`. Namun, Anda bebas menyesuaikan nama atribut ini agar lebih mencerminkan tujuannya di aplikasi.

Misalnya, jika aplikasi Anda mengandung user yang mungkin berlangganan, mungkin ada many-to-many relationship antara user dan podcasts. Jika ini masalahnya, Anda mungkin ingin mengganti nama intermediate table accessor dari `pivot` menjadi `subscription`. Ini bisa dilakukan dengan menggunakan method `as` saat mendefinisikan relationship:

```php
return $this->belongsToMany(Podcast::class)->as('subscription')->withTimestamps();
```

Setelah didefinisikan, Anda bisa mengakses data intermediate table menggunakan nama kustom:

```php
$users = User::with('podcasts')->get();

foreach ($users->flatMap->podcasts as $podcast) {
    echo $podcast->subscription->created_at;
}
```

#### Memfilter Relationships melalui Intermediate Table

Saat mendefinisikan relationships, Anda juga bisa menggunakan method `wherePivot` dan `wherePivotIn` untuk memfilter hasil yang dikembalikan oleh `belongsToMany`:

```php
return $this->belongsToMany('App\Role')->wherePivot('approved', 1);

return $this->belongsToMany('App\Role')->wherePivotIn('priority', [1, 2]);
```

## Eager Loading

Saat mengakses `Hyperf` relationships sebagai property, data relationship di-"lazy load". Ini berarti data relationship tidak benar-benar dimuat sampai pertama kali property tersebut diakses. Namun, `Hyperf` bisa "eager load" child relationships saat melakukan query pada parent model. Eager loading dapat mengurangi masalah N + 1 query. Untuk mengilustrasikan masalah N + 1 query, pertimbangkan kasus di mana model `User` terkait dengan `Role`:

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

Sekarang, mari kita ambil semua user dan role mereka yang sesuai:

```php
$users = User::query()->get();

foreach ($users as $user){
    echo $user->role->name;
}
```

Loop ini akan mengeksekusi satu query untuk mengambil semua user, dan kemudian mengeksekusi query untuk setiap user untuk mengambil role mereka. Jika kita memiliki 10 orang, loop ini akan menjalankan 11 queries: 1 untuk query user, dan 10 query tambahan yang sesuai dengan role mereka.

Untungnya, kita bisa menggunakan eager loading untuk memampatkan operasi menjadi hanya 2 queries. Saat melakukan query, Anda bisa menggunakan method `with` untuk menentukan relationships yang ingin Anda eager load:

```php
$users = User::query()->with('role')->get();

foreach ($users as $user){
    echo $user->role->name;
}
```

Dalam contoh ini, hanya dua query yang dieksekusi:

```
SELECT * FROM `user`;

SELECT * FROM `role` WHERE id in (1, 2, 3, ...);
```

## Polymorphic Relationships

Polymorphic relationships memungkinkan target model untuk dimiliki oleh lebih dari satu tipe model lainnya menggunakan satu asosiasi.

### One To One (Polymorphic)

#### Table Structure

One-to-one polymorphic relationship mirip dengan simple one-to-one relationship; namun, target model bisa dimiliki oleh lebih dari satu tipe model pada satu asosiasi.
Sebagai contoh, `Book` dan `User` mungkin berbagi relationship dengan model `Image`. Menggunakan one-to-one polymorphic relationship memungkinkan satu daftar gambar digunakan untuk `Book` dan `User`. Mari kita lihat struktur tabelnya:

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

Field `imageable_id` di tabel `image` merepresentasikan makna yang berbeda berdasarkan `imageable_type` yang berbeda. Secara default, `imageable_type` langsung berupa class name dari associated model.

#### Model Example

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

#### Mengambil Relationships

Setelah mendefinisikan model seperti di atas, kita bisa mengambil model yang sesuai melalui model relationships.

Misalnya, kita mengambil gambar dari seorang user.

```php
use App\Model\User;

$user = User::find(1);

$image = $user->image;
```

Atau kita mengambil user atau buku yang sesuai dengan gambar tertentu. `imageable` akan mengambil `User` atau `Book` yang sesuai berdasarkan `imageable_type`.

```php
use App\Model\Image;

$image = Image::find(1);

$imageable = $image->imageable;
```

### One To Many (Polymorphic)

#### Model Example

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

#### Mengambil Relationships

Mengambil semua gambar dari seorang user:

```php
use App\Model\User;

$user = User::query()->find(1);
foreach ($user->images as $image) {
    // ...
}
```

### Custom Polymorphic Mapping

Secara default, framework memerlukan `type` untuk menyimpan class name model yang sesuai, misalnya `imageable_type` yang disebutkan sebelumnya harus berupa `User::class` dan `Book::class` yang sesuai, tetapi jelas, ini sangat tidak nyaman dalam aplikasi praktis. Oleh karena itu, kita bisa menyesuaikan mapping relationship untuk memisahkan database dari struktur internal aplikasi.

```php
use App\Model;
use Hyperf\Database\Model\Relations\Relation;
Relation::morphMap([
    'user' => Model\User::class,
    'book' => Model\Book::class,
]);
```

Karena `Relation::morphMap` akan tetap berada di memory setelah dimodifikasi, kita bisa membuat mapping relationship yang sesuai saat project dimulai. Kita bisa membuat listener berikut:

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

### Nested Eager Loading `morphTo` Relationships

Jika Anda ingin memuat relationship `morphTo`, dan nested relationships dari berbagai entitas yang mungkin dikembalikan oleh relationship tersebut, Anda bisa menggabungkan method `with` dengan method `morphWith` dari relationship `morphTo`.

Misalnya, kita ingin eager load relationship `book.user` dari sebuah gambar.

```php

use App\Model\Book;
use App\Model\Image;
use Hyperf\Database\Model\Relations\MorphTo;

$images = Image::query()->with([
    'imageable' => function (MorphTo $morphTo) {
        $morphWith->morphWith([
            Book::class => ['user'],
        ]);
    },
])->get();
```

SQL query yang sesuai adalah sebagai berikut:

```sql
-- Query semua images
select * from `images`;
-- Query daftar user yang sesuai dengan images
select * from `user` where `user`.`id` in (1, 2);
-- Query daftar buku yang sesuai dengan images
select * from `book` where `book`.`id` in (1, 2, 3);
-- Query daftar user yang sesuai dengan daftar buku
select * from `user` where `user`.`id` in (1, 2);
```

### Polymorphic Relationship Query

Untuk melakukan query keberadaan relationship `MorphTo`, Anda bisa menggunakan method `whereHasMorph` dan method yang sesuai:

Contoh berikut akan melakukan query daftar images di mana buku atau user `ID`-nya adalah 1.

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
