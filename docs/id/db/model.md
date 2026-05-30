# Model

Komponen model diturunkan dari [Eloquent ORM](https://laravel.com/docs/5.8/eloquent), dan semua operasi terkait dapat merujuk ke dokumentasi Eloquent ORM.

## Membuat Model

Hyperf menyediakan perintah untuk membuat model, memungkinkan Anda membuat model
yang sesuai dengan mudah berdasarkan tabel database Anda. Perintah ini
menghasilkan model menggunakan `AST`, yang berarti Anda dapat dengan mudah mengatur
ulang (reset) model dengan sebuah skrip bahkan setelah menambahkan metode tertentu.

```
php bin/hyperf.php gen:model table_name
```

Parameter opsional adalah sebagai berikut:

| Parameter | Tipe | Nilai Default | Catatan |
| :----------------: | :----: | :-------------------------------: | :-----------------------------------------------: |
| --pool | string | `default` | Connection pool, skrip akan membuat berdasarkan konfigurasi pool saat ini |
| --path | string | `app/Model` | Path model |
| --force-casts | bool | `false` | Apakah akan mengatur ulang atribut `casts` secara paksa |
| --prefix | string | '' | Prefiks tabel |
| --inheritance | string | `Model` | Class induk |
| --uses | string | `Hyperf\DbConnection\Model\Model` | Digunakan bersama dengan `inheritance` |
| --refresh-fillable | bool | `false` | Apakah akan me-refresh atribut `fillable` |
| --table-mapping | array | `[]` | Pemetaan nama tabel ke model, misal, ['users:Account'] |
| --ignore-tables | array | `[]` | Tabel yang diabaikan untuk pembuatan model, misal, ['users'] |
| --with-comments | bool | `false` | Apakah akan menambahkan komentar field |
| --property-case | int | `0` | Tipe field: 0 snake, 1 hump |


Ketika menggunakan opsi `--property-case` untuk mengubah nama field menjadi
camelCase, Anda juga perlu menyertakan trait
`Hyperf\Database\Model\Concerns\CamelCase` secara manual di dalam model Anda.

Konfigurasi yang sesuai juga dapat diatur di dalam
`databases.{pool}.commands.gen:model` sebagai berikut:

> Semua tanda hubung (dash) harus diubah menjadi garis bawah (underscore)

```php
<?php

declare(strict_types=1);

use Hyperf\Database\Commands\ModelOption;

return [
    'default' => [
        // Ignore other configurations.
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
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = ['id', 'name', 'gender', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'gender' => 'integer'];
}
```

## Variabel Anggota Model

| Parameter | Tipe | Nilai Default | Keterangan |
| :----------: | :----: | :-----: | :------------------: |
| connection | string | default | koneksi database |
| table | string | Tidak ada | Nama tabel data |
| primaryKey | string | id | primary key model |
| keyType | string | int | tipe primary key |
| fillable | array | [] | Properti yang mengizinkan batch assignment |
| casts | string | Tidak ada | Konfigurasi pemformatan data |
| timestamps | bool | true | Apakah otomatis memelihara timestamps |
| incrementing | bool | true | Apakah primary key auto-increment |

### Nama Tabel

Jika kita tidak menentukan tabel yang sesuai untuk model, ia akan menggunakan
bentuk jamak dari nama class dalam format 'snake case' sebagai nama tabel. Oleh
karena itu, dalam kasus ini, Hyperf akan mengasumsikan bahwa model User
menyimpan data dalam tabel 'users'. Anda dapat menentukan tabel kustom dengan
mendefinisikan properti table pada model:

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

Hyperf akan mengasumsikan bahwa setiap tabel data memiliki kolom primary key
bernama id. Anda dapat mendefinisikan properti protected `$primaryKey` untuk
mengesampingkan konvensi tersebut.

Selain itu, Hyperf mengasumsikan bahwa primary key adalah nilai integer yang
auto-increment, yang berarti secara default primary key akan otomatis dikonversi
ke tipe int. Jika Anda ingin menggunakan primary key yang non-incrementing atau
non-numerik, Anda perlu mengatur properti public `$incrementing` menjadi false.
Jika primary key Anda bukan integer, Anda perlu mengatur properti protected
`$keyType` pada model ke string.


### Timestamps

Secara default, Hyperf mengharapkan tabel Anda memiliki kolom `created_at` dan
`updated_at`. Jika Anda tidak ingin Hyperf mengelola kedua kolom ini secara
otomatis, atur properti `$timestamps` di dalam model Anda menjadi `false`:

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

Jika Anda perlu menyesuaikan format timestamp, atur properti `$dateFormat` di
dalam model Anda. Properti ini menentukan bagaimana atribut tanggal disimpan di
dalam database, dan bagaimana model diserialisasikan ke dalam format array atau
JSON:

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

Jika Anda membutuhkan penyimpanan yang tidak ingin mempertahankan format
`datetime`, atau ingin melakukan pemrosesan lebih lanjut pada waktu, Anda dapat
melakukannya dengan meng-override metode `fromDateTime($value)` di dalam model.

Jika Anda perlu menyesuaikan nama field untuk menyimpan timestamp, Anda dapat
mengatur nilai konstanta `CREATED_AT` dan `UPDATED_AT` di dalam model. Jika salah
satunya bernilai `null`, itu menunjukkan bahwa Anda tidak ingin ORM memproses
field tersebut:

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

### Konektivitas Database

Secara default, model Hyperf akan menggunakan koneksi database default
`default` yang dikonfigurasi oleh aplikasi Anda. Jika Anda ingin menentukan
koneksi yang berbeda untuk model tersebut, atur properti `$connection`. Tentu
saja, `connection-name` sebagai `key` harus ada di dalam file konfigurasi
`databases.php`.

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

### Nilai default atribut

Jika Anda ingin mendefinisikan nilai default untuk beberapa atribut model, Anda
dapat mendefinisikan atribut `$attributes` pada model:

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

## Query Model

```php
<?php
use App\Model\User;

/** @var User $user */
$user = User::query()->where('id', 1)->first();
$user->name = 'Hyperf';
$user->save();

```

### Memuat Ulang Model

Anda dapat memuat ulang model menggunakan metode `fresh` dan `refresh`. Metode
`fresh` akan mengambil kembali model dari database. Instans model yang sudah ada
tidak akan terpengaruh:

```php
<?php
use App\Model\User;

/** @var User $user */
$user = User::query()->find(1);

$freshUser = $user->fresh();
```

Metode `refresh` memperbarui instans model yang ada dengan data baru dari
database. Selain itu, relasi yang sudah dimuat akan dimuat ulang:

```php
<?php
use App\Model\User;

/** @var User $user */
$user = User::query()->where('name','Hyperf')->first();

$user->name = 'Hyperf2';

$user->refresh();

echo $user->name; // Hyperf
```

### Collection

Untuk metode `all` dan `get` di dalam model, Anda dapat mengkueri beberapa hasil
dan mengembalikan instans `Hyperf\Database\Model\Collection`. Class `Collection`
menyediakan banyak fungsi pembantu (helper) untuk memproses hasil query:

```php
$users = $users->reject(function ($user) {
    // Exclude all deleted users
    return $user->deleted;
});
```

### Mengambil satu model

Selain mengambil semua record dari tabel data yang ditentukan, Anda dapat
menggunakan metode `find` atau `first` untuk mengambil satu record. Metode-metode
ini mengembalikan satu instans model, bukan koleksi model:

```php
<?php
use App\Model\User;

$user = User::query()->where('id', 1)->first();

$user = User::query()->find(1);
```

### Mengambil beberapa model

Tentu saja metode `find` mendukung lebih dari sekadar satu model.

```php
<?php
use App\Model\User;

$users = User::query()->find([1, 2, 3]);
```

### Pengecualian "Not found" (Tidak Ditemukan)

Terkadang Anda ingin melemparkan exception ketika model tidak ditemukan, hal ini
sangat berguna di dalam controller dan router.

Metode `findOrFail` dan `firstOrFail` akan mengambil hasil pertama dari query,
dan jika tidak ditemukan, exception
`Hyperf\Database\Model\ModelNotFoundException` akan dilemparkan:

```php
<?php
use App\Model\User;

$model = User::findOrFail(1);
$model = User::where('age', '>', 18)->firstOrFail();
```

### Fungsi Agregasi

Anda juga dapat menggunakan `count`, `sum`, `max`, dan fungsi agregasi lainnya
yang disediakan oleh query builder. Metode-metode ini hanya akan mengembalikan
nilai skalar yang sesuai, bukan instans model:

```php
<?php
use App\Model\User;

$count = User::query()->where('gender', 1)->count();
```

## Menyisipkan & memperbarui model

### Menyisipkan (Insert)

Untuk menambahkan record baru ke database, pertama buat instans model baru,
atur properti untuk instans tersebut, lalu panggil metode `save`:

```php
use App\Model\User;

/** @var User $user */
$user = new User();

$user->name = 'Hyperf';

$user->save();
```

Dalam contoh ini, kita menetapkan nilai ke properti `name` dari instans model
`App\Model\User`. Ketika metode `save` dipanggil, record baru akan disisipkan.
Timestamp `created_at` dan `updated_at` akan diatur secara otomatis dan tidak
memerlukan pengisian manual.

### Memperbarui (Update)

Metode `save` juga dapat digunakan untuk memperbarui model yang sudah ada di
dalam database. Untuk memperbarui model, Anda perlu mengambilnya terlebih dahulu,
mengatur properti yang ingin diperbarui, lalu panggil metode `save`. Begitu juga,
timestamp `updated_at` diperbarui secara otomatis, sehingga tidak perlu diisi
secara manual:

```php
use App\Model\User;

/** @var User $user */
$user = User::query()->find(1);

$user->name = 'Hi Hyperf';

$user->save();
```

### Pembaruan massal (Batch update)

Anda juga dapat memperbarui beberapa model yang cocok dengan kriteria query.
Dalam contoh ini, untuk semua pengguna yang memiliki `gender` bernilai `1`, ubah
`gender_show` menjadi male:

```php
use App\Model\User;

User::query()->where('gender', 1)->update(['gender_show' => 'male']);
```

> Selama pembaruan massal (batch update), model yang diperbarui tidak akan memicu event `saved` dan `updated`. Karena selama pembaruan massal, model tidak diinstansiasi. Di saat yang sama, `casts` yang sesuai juga tidak akan dijalankan. Sebagai contoh, untuk format `json` di database, field `casts` di class Model ditandai sebagai `array`. Jika pembaruan massal digunakan, `array` tidak akan otomatis dikonversi saat penyisipan ke dalam format string `json`.

### Pengisian massal (Batch assignment)

Anda juga dapat menyimpan model baru menggunakan metode `create`, yang
mengembalikan instans model. Namun, sebelum menggunakannya, Anda perlu
menentukan atribut `fillable` or `guarded` pada model tersebut, karena secara
default semua model tidak dapat diisi secara massal (batch assignment).

Hal ini untuk mencegah ketika pengguna mengirimkan parameter yang tidak
diharapkan melalui HTTP request, dan parameter tersebut mengubah field di
database yang tidak ingin Anda ubah. Sebagai contoh: pengguna berbahaya mungkin
mengirimkan parameter `is_admin` melalui HTTP request dan kemudian meneruskannya
ke metode `create`. Operasi ini memungkinkan pengguna tersebut untuk menaikkan
level dirinya menjadi administrator.

Oleh karena itu, sebelum memulai, Anda harus menentukan atribut apa saja pada
model yang dapat diisi secara massal. Anda dapat melakukannya melalui atribut
`$fillable` pada model. Sebagai contoh: biarkan atribut `name` dari model `User`
diisi secara massal:

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

Setelah kita mengatur properti yang dapat diisi secara massal, kita dapat
menyisipkan data baru ke database melalui metode `create`. Metode `create` akan
mengembalikan instans model yang disimpan:

```php
use App\Model\User;

$user = User::create(['name' => 'Hyperf']);
```

Jika Anda sudah memiliki instans model, Anda dapat meneruskan sebuah array ke
metode fill untuk mengisi nilainya:

```php
$user->fill(['name' => 'Hyperf']);
```

### Atribut yang dilindungi (Protected attributes)

`$fillable` dapat dianggap sebagai "whitelist" untuk pengisian massal, dan
Anda juga dapat menggunakan atribut `$guarded` untuk mencapai hal ini. Atribut
`$guarded` berisi array yang tidak diperbolehkan untuk pengisian massal. Dengan
kata lain, `$guarded` akan berfungsi lebih seperti "blacklist". Catatan: Anda
hanya dapat menggunakan salah satu dari `$fillable` atau `$guarded`, tidak
keduanya secara bersamaan. Pada contoh berikut, kecuali untuk atribut
`gender_show`, semua atribut lainnya dapat diisi secara massal:

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

### Metode pembuatan lainnya

`firstOrCreate` / `firstOrNew`

Metode `firstOrCreate` akan mencocokkan data di database dengan kolom/nilai yang
diberikan. Jika model yang sesuai tidak dapat ditemukan di database, sebuah
record akan dibuat dari atribut parameter pertama dan juga atribut parameter kedua
kemudian disisipkan ke dalam database.

Metode `firstOrNew`, seperti metode `firstOrCreate`, mencoba mencari record di
database dengan atribut yang diberikan. Perbedaannya adalah jika metode
`firstOrNew` tidak dapat menemukan model yang cocok, metode ini akan
mengembalikan instans model baru. Perhatikan bahwa instans model yang
dikembalikan oleh `firstOrNew` belum disimpan ke database. Anda perlu memanggil
metode `save` secara manual untuk menyimpannya:

```php
<?php
use App\Model\User;

// Find the user by name, create it if it does not exist...
$user = User::firstOrCreate(['name' => 'Hyperf']);

// Find the user by name. If it does not exist, use the name and gender, age attributes to create...
$user = User::firstOrCreate(
    ['name' => 'Hyperf'],
    ['gender' => 1, 'age' => 20]
);

// Find the user by name, create an instance if it does not exist...
$user = User::firstOrNew(['name' => 'Hyperf']);

// Find the user by name. If it does not exist, use the name and gender, age attributes to create an instance...
$user = User::firstOrNew(
    ['name' => 'Hyperf'],
    ['gender' => 1, 'age' => 20]
);
```

### Menghapus model

Metode `delete` dapat dipanggil pada instans model untuk menghapus instans
tersebut:

```php
use App\Model\User;

$user = User::query()->find(1);

$user->delete();
```

### Menghapus model melalui query

Anda dapat menghapus data model dengan memanggil metode `delete` pada query,
dalam contoh ini kita akan menghapus semua pengguna yang memiliki `gender`
bernilai `1`. Seperti halnya pembaruan massal (batch update), penghapusan massal
(batch delete) tidak akan memicu event model apa pun untuk model yang dihapus:

```php
use App\Model\User;

// Note that when using the delete method, certain query conditions must be established to safely delete data. If there is no where condition, the entire data table will be deleted.
User::query()->where('gender', 1)->delete(); 
```

### Menghapus data langsung dengan primary key

Pada contoh di atas, Anda perlu menemukan model yang sesuai di database sebelum
memanggil `delete`. Faktanya, jika Anda mengetahui primary key dari model
tersebut, Anda dapat menghapus data model secara langsung melalui metode statis
`destroy` tanpa harus mencarinya di database terlebih dahulu. Selain menerima
satu primary key sebagai parameter, metode `destroy` juga menerima beberapa
primary key, atau menggunakan array atau collection untuk menyimpan beberapa
primary key:

```php
use App\Model\User;

User::destroy(1);

User::destroy([1,2,3]);
```

### Soft delete

Selain menghapus record database secara nyata, `Hyperf` juga dapat melakukan
"soft delete" pada model. Model yang di-soft delete tidak benar-benar dihapus
dari database. Sebaliknya, atribut `deleted_at` akan diatur pada model dan
nilainya ditulis ke database. Jika nilai `deleted_at` tidak kosong, itu berarti
model telah di-soft delete. Jika Anda ingin mengaktifkan soft delete pada model,
Anda perlu menggunakan trait `Hyperf\Database\Model\SoftDeletes` pada model.

> Trait `SoftDeletes` akan secara otomatis mengubah atribut `deleted_at` menjadi instans `DateTime / Carbon`

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

Metode `restoreOrCreate` akan mencocokkan data di database dengan kolom/nilai yang
diberikan. Jika model yang sesuai ditemukan di database, metode `restore` akan
dijalankan untuk memulihkan model tersebut, jika tidak, record baru akan dibuat
dari atribut parameter pertama dan bahkan atribut parameter kedua lalu disisipkan
ke dalam database.

```php
// Look up users by name, create them with name and gender, age attributes if they don't exist...
$user = User::restoreOrCreate(
    ['name' => 'Hyperf'],
    ['gender' => 1, 'age' => 20]
);
```

## Tipe Bit

Secara default, saat mengonversi model database di Hyperf menjadi SQL, nilai
parameter akan dikonversi secara seragam ke tipe String untuk menyelesaikan
masalah int pada angka besar dan memudahkan pencocokan indeks tipe nilai. Jika
Anda ingin membuat `ORM` mendukung tipe `bit`, cukup tambahkan kode event
listener berikut.

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
