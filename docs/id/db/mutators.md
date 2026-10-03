# Mutators

> Dokumentasi ini banyak mengambil referensi dari [LearnKu](https://learnku.com). Terima kasih kepada LearnKu atas kontribusinya ke komunitas PHP.

Accessors dan mutators memungkinkan Anda untuk memformat nilai atribut model ketika Anda mendapatkan atau mengatur atribut pada instance model.

## Accessors & Mutators

### Mendefinisikan Accessor

Untuk mendefinisikan accessor, buat method `getFooAttribute` pada model Anda, di mana `Foo` adalah nama "camel-cased" dari kolom yang ingin Anda akses. Dalam contoh ini, kita akan mendefinisikan accessor untuk atribut `first_name`. Ketika model mencoba mengambil atribut `first_name`, accessor ini akan secara otomatis dipanggil:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Dapatkan nama depan pengguna.
     *
     * @param  string  $value
     * @return string
     */
    public function getFirstNameAttribute($value)
    {
        return ucfirst($value);
    }
}
```

Seperti yang Anda lihat, nilai asli dari kolom diteruskan ke accessor, memungkinkan Anda untuk memprosesnya dan mengembalikan hasilnya. Untuk mengambil nilai yang telah dimodifikasi, Anda dapat mengakses atribut `first_name` pada instance model:

```php
$user = App\User::find(1);

$firstName = $user->first_name;
```

Tentu saja, Anda juga dapat menggunakan nilai atribut yang sudah ada untuk mengembalikan nilai kalkulasi baru melalui accessor:

```php
namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Dapatkan nama lengkap pengguna.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
```

### Mendefinisikan Mutator

Untuk mendefinisikan mutator, definisikan method `setFooAttribute` pada model Anda. Field `Foo` yang akan diakses harus menggunakan penamaan "camel-cased". Mari kita definisikan mutator untuk atribut `first_name`. Ketika kita mencoba mengatur nilai atribut `first_name` pada model, mutator ini akan secara otomatis dipanggil:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Mengatur nama depan pengguna.
     *
     * @param  string  $value
     * @return void
     */
    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = strtolower($value);
    }
}
```

Mutator akan menerima nilai yang sedang diatur pada atribut, memungkinkan Anda untuk memodifikasinya dan mengatur nilai tersebut ke atribut internal `$attributes` model. Misalnya, jika kita mencoba mengatur nilai atribut `first_name` menjadi `Sally`:

```php
$user = App\User::find(1);

$user->first_name = 'Sally';
```

Dalam contoh ini, method `setFirstNameAttribute` dipanggil dengan `Sally` sebagai argumen. Mutator kemudian menerapkan fungsi `strtolower` dan mengatur hasil yang telah diproses ke dalam array internal `$attributes`.

## Date Mutators

Secara default, model akan mengonversi kolom `created_at` dan `updated_at` menjadi instance `Carbon`, yang memperluas class native PHP `DateTime` dan menyediakan berbagai method yang berguna. Anda dapat menambahkan atribut tanggal lainnya dengan mengatur properti `$dates` pada model:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Atribut yang harus dikonversi ke format tanggal.
     *
     * @var array
     */
    protected $dates = [
        'seen_at',
    ];
}
```

> Tip: Anda dapat menonaktifkan timestamp default `created_at` dan `updated_at` dengan mengatur properti public `$timestamps` model menjadi `false`.

Ketika suatu kolom berformat tanggal, Anda dapat mengatur nilainya menjadi timestamp `UNIX`, string date-time `(Y-m-d)`, atau instance `DateTime` / `Carbon`. Nilai tanggal akan diformat dengan benar dan disimpan ke database Anda.

Seperti disebutkan di atas, ketika atribut yang diambil termasuk dalam properti `$dates`, mereka secara otomatis dikonversi menjadi instance `Carbon`, memungkinkan Anda untuk menggunakan method `Carbon` apa pun pada atribut tersebut:

```php
$user = App\User::find(1);

return $user->deleted_at->getTimestamp();
```

### Date Format

Timestamp diformat sebagai `Y-m-d H:i:s`. Jika Anda perlu menyesuaikan format timestamp, Anda dapat mengatur properti `$dateFormat` di model. Properti ini menentukan bagaimana atribut tanggal disimpan di database, serta formatnya ketika model di-serialize menjadi array atau `JSON`:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class Flight extends Model
{
    /**
     * Format penyimpanan kolom tanggal model.
     *
     * @var string
     */
    protected $dateFormat = 'U';
}
```

## Attribute Casting

Properti `$casts` pada model menyediakan method yang mudah untuk me-cast atribut ke tipe data umum. Properti `$casts` harus berupa array di mana key adalah nama atribut yang akan di-cast, dan value adalah tipe data yang Anda inginkan.
Tipe data yang didukung untuk casting adalah: `integer`, `real`, `float`, `double`, `decimal:<digits>`, `string`, `boolean`, `object`, `array`, `collection`, `date`, `datetime`, dan `timestamp`. Saat melakukan casting ke tipe `decimal`, Anda perlu menentukan jumlah digit desimal, misalnya `decimal:2`.

Sebagai contoh, mari kita cast atribut `is_admin`, yang disimpan di database sebagai integer (`0` atau `1`), menjadi boolean:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Atribut yang harus di-cast.
     *
     * @var array
     */
    protected $casts = [
        'is_admin' => 'boolean',
    ];
}
```

Sekarang, ketika Anda mengakses atribut `is_admin`, meskipun nilai yang disimpan di database adalah integer, nilai yang dikembalikan akan selalu di-cast ke tipe boolean:

```php
$user = App\User::find(1);

if ($user->is_admin) {
    //
}
```

### Custom Casting

Model memiliki banyak tipe casting bawaan yang umum. Namun, pengguna terkadang perlu melakukan casting data ke tipe kustom. Sekarang, kebutuhan ini dapat dipenuhi dengan mendefinisikan class yang mengimplementasikan interface `CastsAttributes`.

Class yang mengimplementasikan interface ini harus mendefinisikan method `get` dan `set`. Method `get` bertanggung jawab untuk mengonversi data mentah yang diambil dari database ke tipe yang sesuai, sedangkan method `set` mengonversi data ke tipe database yang sesuai untuk disimpan. Sebagai contoh, mari kita implementasikan ulang casting built-in `json` sebagai custom type casting:

```php
<?php

namespace App\Casts;

use Hyperf\Contract\CastsAttributes;

class Json implements CastsAttributes
{
    /**
     * Transformasi data yang diambil dari database.
     */
    public function get($model, $key, $value, $attributes)
    {
        return json_decode($value, true);
    }

    /**
     * Transformasi nilai untuk disimpan di database.
     */
    public function set($model, $key, $value, $attributes)
    {
        return json_encode($value);
    }
}
```

Setelah mendefinisikan custom type casting, Anda dapat menempelkannya ke atribut model menggunakan nama class-nya:

```php
<?php

namespace App;

use App\Casts\Json;
use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Atribut yang harus di-cast.
     *
     * @var array
     */
    protected $casts = [
        'options' => Json::class,
    ];
}
```

#### Value Object Casting

Anda tidak hanya dapat melakukan casting data ke tipe data native, tetapi juga ke objek. Cara pendefinisian untuk kedua jenis custom casting sangat mirip. Namun, method `set` pada class custom casting yang mengonversi data ke objek perlu mengembalikan array key-value pairs, yang digunakan untuk mengatur nilai mentah yang dapat disimpan ke model yang sesuai.

Sebagai contoh, definisikan class custom casting untuk mengonversi beberapa atribut model menjadi satu objek nilai `Address`. Asumsikan objek `Address` memiliki dua properti public, `lineOne` dan `lineTwo`:

```php
<?php

namespace App\Casts;

use App\Address;
use Hyperf\Contract\CastsAttributes;

class AddressCaster implements CastsAttributes
{
    /**
     * Transformasi data yang diambil dari database.
     */
    public function get($model, $key, $value, $attributes): Address
    {
        return new Address(
            $attributes['address_line_one'],
            $attributes['address_line_two']
        );
    }

    /**
     * Transformasi nilai untuk disimpan di database.
     */
    public function set($model, $key, $value, $attributes)
    {
        return [
            'address_line_one' => $value->lineOne,
            'address_line_two' => $value->lineTwo,
        ];
    }
}
```

Setelah melakukan value object casting, setiap perubahan data pada value object akan secara otomatis disinkronkan kembali ke model sebelum disimpan:

```php
<?php
$user = App\User::find(1);

$user->address->lineOne = 'Updated Address Value';
$user->address->lineTwo = '#10000';

$user->save();

var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Updated Address Value',
//    'address_line_two' => '#10000'
//];
```

**Implementasi di sini berbeda dengan Laravel. Perhatikan jika terjadi penggunaan berikut:**

```php
$user = App\User::find(1);

var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Address Value',
//    'address_line_two' => '#10000'
//];

$user->address->lineOne = 'Updated Address Value';
$user->address->lineTwo = '#20000';

// Setelah memodifikasi field 'address' secara langsung, perubahan belum langsung terlihat di 'attributes', tapi Anda bisa mengakses data yang sudah dimodifikasi melalui $user->address.
var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Address Value',
//    'address_line_two' => '#10000'
//];

// Ketika kita menyimpan atau menghapus data, 'attributes' akan berubah menjadi data yang telah dimodifikasi.
$user->save();
var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Updated Address Value',
//    'address_line_two' => '#20000'
//];
```

Jika Anda memodifikasi `address` tetapi tidak ingin menyimpannya, dan tidak ingin mengambil data `address_line_one` melalui `address->lineOne`, Anda juga dapat menggunakan method berikut:

```php
$user = App\User::find(1);
$user->address->lineOne = 'Updated Address Value';
$user->syncAttributes();
var_dump($user->getAttributes());
```

Tentu saja, jika Anda masih membutuhkan fungsionalitas untuk menyinkronkan modifikasi ke `attributes` setelah memodifikasi `value` yang sesuai, Anda dapat mencoba menggunakan method berikut. Pertama, kita implementasikan `UserInfo` dan mewarisi dari `CastsValue`.

```php
namespace App\Caster;

use Hyperf\Database\Model\CastsValue;

/**
 * @property string $name
 * @property int $gender
 */
class UserInfo extends CastsValue
{
}
```

Kemudian implementasikan `UserInfoCaster` yang sesuai:

```php
<?php

declare(strict_types=1);

namespace App\Caster;

use Hyperf\Contract\CastsAttributes;
use Hyperf\Collection\Arr;

class UserInfoCaster implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): UserInfo
    {
        return new UserInfo($model, Arr::only($attributes, ['name', 'gender']));
    }

    public function set($model, string $key, $value, array $attributes)
    {
        return [
            'name' => $value->name,
            'gender' => $value->gender,
        ];
    }
}
```

Ketika kita memodifikasi `UserInfo` dengan cara berikut, kita dapat menyinkronkan data ke `attributes`.

```php
/** @var User $user */
$user = User::query()->find(100);
$user->userInfo->name = 'John1';
var_dump($user->getAttributes()); // ['name' => 'John1']
```

#### Inbound Casting

Terkadang, Anda mungkin hanya perlu melakukan casting pada nilai yang ditulis ke model tanpa memproses nilai yang diambil dari model. Contoh tipikal dari inbound casting adalah "hashing". Class inbound casting perlu mengimplementasikan interface `CastsInboundAttributes`; Anda hanya perlu mengimplementasikan method `set`.

```php
<?php

namespace App\Casts;

use Hyperf\Contract\CastsInboundAttributes;

class Hash implements CastsInboundAttributes
{
    /**
     * Algoritma hashing.
     *
     * @var string
     */
    protected $algorithm;

    /**
     * Buat instance class casting baru.
     */
    public function __construct($algorithm = 'md5')
    {
        $this->algorithm = $algorithm;
    }

    /**
     * Transformasi nilai untuk disimpan di database.
     */
    public function set($model, $key, $value, $attributes)
    {
        return hash($this->algorithm, $value);
    }
}
```

#### Casting Parameters

Ketika menempelkan custom casting ke model, Anda dapat menentukan parameter casting yang masuk. Untuk meneruskan parameter casting, gunakan `:` untuk memisahkan parameter dari nama class, dan gunakan koma untuk memisahkan beberapa parameter. Parameter ini akan diteruskan ke constructor dari class casting:

```php
<?php
namespace App;

use App\Casts\Hash;
use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Atribut yang harus di-cast.
     *
     * @var array
     */
    protected $casts = [
        'secret' => Hash::class.':sha256',
    ];
}
```

### Array & JSON Casting

Tipe cast `array` sangat berguna ketika Anda menyimpan data `JSON` yang telah di-serialize di database Anda. Misalnya: jika database Anda memiliki tipe kolom `JSON` atau `TEXT` yang di-serialize sebagai `JSON`, dan Anda menambahkan tipe cast `array` ke model Anda, maka secara otomatis akan dikonversi menjadi array `PHP` ketika Anda mengaksesnya:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Atribut yang harus di-cast.
     *
     * @var array
     */
    protected $casts = [
        'options' => 'array',
    ];
}
```

Setelah didefinisikan, ketika Anda mengakses atribut `options`, ia akan secara otomatis di-deserialize dari tipe `JSON` menjadi array `PHP`. Ketika Anda mengatur nilai atribut `options`, array yang diberikan juga akan secara otomatis di-serialize ke tipe `JSON` untuk penyimpanan:

```php
$user = App\User::find(1);

$options = $user->options;

$options['key'] = 'value';

$user->options = $options;

$user->save();
```

### Date Casting

Ketika menggunakan atribut `date` atau `datetime`, Anda dapat menentukan format tanggal. Format ini digunakan ketika model di-serialize menjadi array atau `JSON`:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Atribut yang harus di-cast.
     *
     * @var array
     */
    protected $casts = [
         'created_at' => 'datetime:Y-m-d',
    ];
}
```

### Casting During Query

Terkadang Anda perlu melakukan casting pada atribut tertentu selama proses eksekusi query, seperti ketika Anda perlu mengambil data dari tabel database. Sebagai contoh, lihat query berikut:

```php
use App\Post;
use App\User;

$users = User::select([
    'users.*',
    'last_posted_at' => Post::selectRaw('MAX(created_at)')
            ->whereColumn('user_id', 'users.id')
])->get();
```

Dalam kumpulan hasil yang diperoleh dari query ini, atribut `last_posted_at` akan berupa string. Akan lebih nyaman jika kita melakukan casting `date` saat mengeksekusi query. Anda dapat menyelesaikan operasi di atas dengan menggunakan method `withCasts`:

```php
$users = User::select([
    'users.*',
    'last_posted_at' => Post::selectRaw('MAX(created_at)')
            ->whereColumn('user_id', 'users.id')
])->withCasts([
    'last_posted_at' => 'date'
])->get();
```
