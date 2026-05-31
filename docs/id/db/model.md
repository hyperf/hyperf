# Model

Model component berasal dari [Eloquent ORM](https://laravel.com/docs/5.8/eloquent). Untuk operasi terkait, silakan lihat dokumentasi Eloquent ORM.

## Membuat Model

Hyperf menyediakan command untuk membuat model, yang memungkinkan Anda dengan mudah membuat model yang sesuai berdasarkan tabel database. Command ini menghasilkan model melalui `AST`, jadi ketika Anda menambahkan beberapa method, Anda juga bisa menggunakan script untuk mereset model dengan mudah.

```
php bin/hyperf.php gen:model table_name
```

Parameter opsionalnya adalah sebagai berikut:

| Parameter | Tipe | Nilai Default | Keterangan |
| :---: | :---: | :---: | :---: |
| --pool | string | `default` | Connection pool, script akan membuat berdasarkan konfigurasi pool saat ini |
| --path | string | `app/Model` | Path model |
| --force-casts | bool | `false` | Apakah memaksa reset parameter `casts` |
| --prefix | string | String Kosong | Table prefix |
| --inheritance | string | `Model` | Parent class |
| --uses | string | `Hyperf\DbConnection\Model\Model` | Digunakan bersama dengan `inheritance` |
| --refresh-fillable | bool | `false` | Apakah me-refresh parameter `fillable` |
| --table-mapping | array | `[]` | Mapping nama tabel ke model, misalnya ['users:Account'] |
| --ignore-tables | array | `[]` | Tabel yang tidak perlu dibuatkan model, misalnya ['users'] |
| --with-comments | bool | `false` | Apakah menambahkan field comments |
| --property-case | int | `0` | Tipe field: 0 snake_case, 1 camelCase |

Saat mengonversi tipe field ke camelCase menggunakan `--property-case`, Anda juga perlu menambahkan `Hyperf\Database\Model\Concerns\CamelCase` secara manual ke model.

Konfigurasi yang sesuai juga bisa dikonfigurasi di `databases.{pool}.commands.gen:model`, sebagai berikut:

> Semua hyphens perlu dikonversi menjadi underscores.

```php
<?php

declare(strict_types=1);

use Hyperf\Database\Commands\ModelOption;

return [
    'default' => [
        // Abaikan konfigurasi lainnya
        'commands' => [
            'gen:model' => [
                'path' => 'app/Model',
                'force_casts' => true,
                'inheritance' => 'Model',
                'uses' => '',
                'refresh_fillable' => true,
                'table_mapping' => [],
                'with_comments' => true,
                'property_case' => ModelOption::PROPERTY_SNAKE_CASE,
            ],
        ],
    ],
];
```

Model yang dibuat adalah sebagai berikut:

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

/**
 * @property $id
 * @property $name
 * @property $gender
 * @property $created_at
 * @property $updated_at
 */
class User extends Model
{
    /**
     * Tabel yang terkait dengan model.
     *
     * @var string
     */
    protected ?string $table = 'user';

    /**
     * Atribut yang bisa diisi secara massal.
     *
     * @var array
     */
    protected array $fillable = ['id', 'name', 'gender', 'created_at', 'updated_at'];

    /**
     * Atribut yang harus di-cast ke tipe native.
     *
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'gender' => 'integer'];
}
```

## Model Member Variables

| Parameter | Tipe | Nilai Default | Keterangan |
| :---: | :---: | :---: | :---: |
| connection | string | default | Koneksi database |
| table | string | Tidak ada | Nama tabel |
| primaryKey | string | id | Model primary key |
| keyType | string | int | Tipe primary key |
| fillable | array | [] | Atribut yang diizinkan untuk mass-assign |
| casts | string | Tidak ada | Konfigurasi formatting data |
| timestamps | bool | true | Apakah otomatis mempertahankan timestamps |
| incrementing | bool | true | Apakah primary key auto-increment |

### Table Name

Jika tidak menentukan tabel yang sesuai dengan model, Hyperf akan menggunakan bentuk plural "snake_case" dari nama class sebagai nama tabel. Jadi, model User akan menyimpan data di tabel `users`. Anda bisa menentukan nama tabel kustom dengan mendefinisikan property `table`:

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    protected ?string $table = 'user';
}
```

### Primary Key

Hyperf mengasumsikan setiap tabel punya kolom primary key bernama `id`. Anda bisa override konvensi ini dengan protected property `$primaryKey`.

Selain itu, Hyperf mengasumsikan primary key adalah auto-increment integer, secara default akan dikonversi ke tipe integer. Jika ingin primary key non-incrementing atau non-numeric, set public property `$incrementing` ke `false`. Jika primary key bukan integer, set protected property `$keyType` ke `string`.

### Timestamps

Secara default, Hyperf mengharapkan `created_at` dan `updated_at` ada di tabel Anda. Jika Anda tidak ingin Hyperf secara otomatis mengelola kedua kolom ini, set property `$timestamps` di model menjadi `false`:

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    public bool $timestamps = false;
}
```

Jika perlu menyesuaikan format timestamp, set property `$dateFormat` di model. Property ini menentukan bagaimana date attributes disimpan di database dan formatnya saat di-serialize ke array atau JSON:

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    protected ?string $dateFormat = 'U';
}
```

Jika tidak ingin menyimpan dalam format `datetime` atau ingin pemrosesan waktu khusus, override method `fromDateTime($value)` di model.

Untuk menyesuaikan nama field timestamps, atur konstanta `CREATED_AT` dan `UPDATED_AT` di model. Set salah satu ke `null` berarti ORM tidak akan memproses field tersebut:

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    const CREATED_AT = 'creation_date';

    const UPDATED_AT = 'last_update';
}
```

### Database Connection

Secara default, model Hyperf menggunakan koneksi `default`. Untuk koneksi yang berbeda, set property `$connection`, pastikan `connection-name` sebagai `key` sudah ada di `databases.php`.

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    protected ?string $connection = 'connection-name';
}
```

### Default Attribute Values

Untuk mendefinisikan nilai default atribut model, gunakan property `$attributes`:

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    protected array $attributes = [
        'delayed' => false,
    ];
}
```

## Model Queries

```php
<?php
use App\Model\User;

/** @var User $user */
$user = User::query()->where('id', 1)->first();
$user->name = 'Hyperf';
$user->save();
```

### Reloading Models

Gunakan method `fresh` dan `refresh` untuk me-reload model. `fresh` mengambil ulang model dari database tanpa mengubah instance yang ada:

```php
<?php
use App\Model\User;

/** @var User $user */
$user = User::query()->find(1);

$freshUser = $user->fresh();
```

Method `refresh` memperbarui instance yang ada dengan data baru dari database, termasuk relationships yang sudah dimuat:

```php
<?php
use App\Model\User;

/** @var User $user */
$user = User::query()->where('name','Hyperf')->first();

$user->name = 'Hyperf2';

$user->refresh();

echo $user->name; // Hyperf
```

### Collections

Method `all` dan `get` mengembalikan instance `Hyperf\Database\Model\Collection`. Class `Collection` menyediakan banyak metode pembantu untuk memproses hasil query:

```php
$users = $users->reject(function ($user) {
    // Kecualikan semua user yang dihapus
    return $user->deleted;
});
```

### Mengambil Satu Model

Selain mengambil semua records dari tabel yang ditentukan, Anda bisa menggunakan method `find` atau `first` untuk mengambil satu record. Method ini mengembalikan instance model tunggal, bukan collection of models:

```php
<?php
use App\Model\User;

$user = User::query()->where('id', 1)->first();

$user = User::query()->find(1);
```

### Mengambil Banyak Model

Tentu saja, method `find` tidak hanya mendukung satu model.

```php
<?php
use App\Model\User;

$users = User::query()->find([1, 2, 3]);
```

### Exception "Not Found"

Terkadang Anda ingin melempar exception saat model tidak ditemukan, ini berguna di controller dan route. Method `findOrFail` dan `firstOrFail` mengambil hasil pertama, dan jika tidak ditemukan akan melempar `Hyperf\Database\Model\ModelNotFoundException`:

```php
<?php
use App\Model\User;

$model = User::findOrFail(1);
$model = User::where('age', '>', 18)->firstOrFail();
```

### Aggregate Functions

Anda juga bisa menggunakan aggregate functions (`count`, `sum`, `max`, dll.) dari query builder. Method ini mengembalikan scalar value, bukan instance model:

```php
<?php
use App\Model\User;

$count = User::query()->where('gender', 1)->count();
```

## Inserting & Updating Models

### Inserting

Untuk menambahkan record baru ke database, buat instance model baru, set atribut untuk instance tersebut, lalu panggil method `save`:

```php
use App\Model\User;

/** @var User $user */
$user = new User();

$user->name = 'Hyperf';

$user->save();
```

Dalam contoh ini, kita menetapkan nilai ke atribut `name` dari instance model `App\Model\User`. Ketika method `save` dipanggil, record baru akan di-insert. Timestamps `created_at` dan `updated_at` akan diatur secara otomatis dan tidak perlu ditetapkan secara manual.

### Updating

Method `save` juga bisa digunakan untuk memperbarui model yang sudah ada di database. Untuk memperbarui model, Anda perlu mengambilnya terlebih dahulu, set atribut yang akan diperbarui, lalu panggil method `save`. Demikian pula, timestamp `updated_at` akan diperbarui secara otomatis, jadi tidak perlu ditetapkan secara manual:

```php
use App\Model\User;

/** @var User $user */
$user = User::query()->find(1);

$user->name = 'Hi Hyperf';

$user->save();
```

### Batch Updates

Anda juga bisa memperbarui beberapa model yang cocok dengan query conditions. Dalam contoh ini, untuk semua user yang `gender`-nya `1`, kita mengubah `gender_show` menjadi "Male":

```php
use App\Model\User;

User::query()->where('gender', 1)->update(['gender_show' => 'Male']);
```

> Batch updates tidak memicu event `saved` dan `updated` karena model tidak diinstansiasi. Juga, `casts` tidak akan dieksekusi, misalnya, jika `json` di database diketik `array` di `casts`, batch update tidak akan otomatis mengonversi `array` ke format string `json`.

### Mass Assignment

Anda juga bisa menggunakan method `create` untuk menyimpan model baru, dan method ini akan mengembalikan instance model. Namun, sebelum menggunakannya, Anda perlu menentukan atribut `fillable` atau `guarded` pada model, karena semua model secara default tidak mengizinkan mass assignment.

Ketika seorang pengguna memberikan parameter yang tidak terduga melalui HTTP request, dan parameter tersebut mengubah field di database yang tidak ingin Anda ubah. Contohnya: pengguna jahat mungkin memberikan parameter `is_admin` melalui HTTP request, dan kemudian meneruskannya ke method `create`, operasi ini bisa memungkinkan pengguna untuk meningkatkan diri mereka menjadi administrator.

Oleh karena itu, sebelum memulai, Anda harus mendefinisikan atribut mana pada model yang bisa di-mass-assign. Anda bisa melakukannya melalui atribut `$fillable` pada model. Contohnya: mengizinkan atribut `name` dari model `User` untuk di-mass-assign:

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    protected array $fillable = ['name'];
}
```

Setelah kita mengatur atribut yang bisa di-mass-assign, kita bisa memasukkan data baru ke database melalui method `create`. Method `create` akan mengembalikan instance model yang disimpan:

```php
use App\Model\User;

$user = User::create(['name' => 'Hyperf']);
```

Jika Anda sudah memiliki instance model, Anda bisa memberikan array ke method `fill` untuk menetapkan nilai:

```php
$user->fill(['name' => 'Hyperf']);
```

### Guarded Attributes

`$fillable` adalah "whitelist" untuk mass assignment. Alternatifnya, gunakan `$guarded` sebagai "blacklist", berisi atribut yang tidak boleh di-mass-assign. Catatan: hanya bisa memakai salah satu, tidak keduanya. Contoh berikut, semua atribut bisa di-mass-assign kecuali `gender_show`:

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    protected $guarded = ['gender_show'];
}
```

### Method Pembuatan Lainnya

`firstOrCreate` / `firstOrNew`

Ada dua method untuk mass assignment: `firstOrCreate` dan `firstOrNew`.

`firstOrCreate` mencari data di database berdasarkan kolom/nilai. Jika tidak ditemukan, ia membuat dan menyimpan record baru dari argumen pertama (dan argumen kedua jika ada).

`firstOrNew` juga mencari record dengan atribut yang diberikan, tapi jika tidak ditemukan, ia mengembalikan instance model baru (belum disimpan ke database). Anda perlu memanggil `save` secara manual:

```php
<?php
use App\Model\User;

// Cari user berdasarkan name, buat jika tidak ada...
$user = User::firstOrCreate(['name' => 'Hyperf']);

// Cari user berdasarkan name, jika tidak ada, buat menggunakan atribut name, gender, dan age...
$user = User::firstOrCreate(
    ['name' => 'Hyperf'],
    ['gender' => 1, 'age' => 20]
);

// Cari user berdasarkan name, buat instance jika tidak ada...
$user = User::firstOrNew(['name' => 'Hyperf']);

// Cari user berdasarkan name, buat instance menggunakan atribut name, gender, dan age jika tidak ada...
$user = User::firstOrNew(
    ['name' => 'Hyperf'],
    ['gender' => 1, 'age' => 20]
);
```

### Menghapus Model

Anda bisa memanggil method `delete` pada instance model untuk menghapus instance tersebut:

```php
use App\Model\User;

$user = User::query()->find(1);

$user->delete();
```

### Menghapus Model via Query

Anda bisa menghapus data model dengan memanggil method `delete` pada query. Dalam contoh ini, kita akan menghapus semua user yang `gender`-nya `1`. Seperti batch updates, batch deletes tidak memicu event model apapun untuk model yang dihapus:

```php
use App\Model\User;

// Catatan: Saat menggunakan method delete, harus didasarkan pada beberapa query conditions untuk menghapus data dengan aman. Jika tidak ada where condition, akan menyebabkan penghapusan seluruh tabel.
User::query()->where('gender', 1)->delete(); 
```

### Menghapus Data Langsung Berdasarkan Primary Key

Dalam contoh di atas, Anda perlu mencari model yang sesuai di database sebelum memanggil `delete`. Sebenarnya, jika Anda mengetahui primary key model, Anda bisa menghapus data model langsung melalui static method `destroy` tanpa mencari di database terlebih dahulu. Method `destroy`, selain menerima satu primary key sebagai argumen, juga menerima beberapa primary key, atau menggunakan array atau collections untuk menampung beberapa primary key:

```php
use App\Model\User;

User::destroy(1);

User::destroy([1,2,3]);
```

### Soft Deletes

Selain menghapus records secara permanen, `Hyperf` juga mendukung "soft delete". Model yang di-soft-delete tidak benar-benar dihapus, atribut `deleted_at` diisi dan disimpan. Jika `deleted_at` tidak null, berarti model sudah di-soft-delete. Untuk mengaktifkannya, gunakan trait `Hyperf\Database\Model\SoftDeletes` di model.

> Trait `SoftDeletes` akan secara otomatis mengonversi atribut `deleted_at` menjadi instance `DateTime / Carbon`.

```php
<?php

namespace App\Model;

use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\SoftDeletes;

class User extends Model
{
    use SoftDeletes;
}
```

Method `restoreOrCreate` mencari data berdasarkan kolom/nilai. Jika ditemukan, model di-restore; jika tidak, record baru dibuat dari atribut argumen pertama dan argumen kedua.

```php
// Cari user berdasarkan name, jika tidak ada, buat menggunakan atribut name, gender, dan age...
$user = User::restoreOrCreate(
    ['name' => 'Hyperf'],
    ['gender' => 1, 'age' => 20]
);
```

## Bit Type

Secara default, dalam proses konversi model database ke SQL di Hyperf, nilai parameter secara seragam dikonversi ke tipe String untuk mengatasi masalah large numbers di `int` dan membuat tipe nilai lebih mudah mencocokkan index. Jika Anda ingin `ORM` mendukung tipe `bit`, Anda hanya perlu menambahkan kode event listener berikut.

```php
<?php

declare(strict_types=1);

namespace App\Listener;

use Hyperf\Database\Connection;
use Hyperf\Database\MySqlBitConnection;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;

#[Listener]
class SupportMySQLBitListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event)
    {
        Connection::resolverFor('mysql', static function ($connection, $database, $prefix, $config) {
            return new MySqlBitConnection($connection, $database, $prefix, $config);
        });
    }
}
```
