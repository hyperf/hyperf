# Modifier

> Dokumen ini banyak meminjam dari [LearnKu](https://learnku.com). Terima kasih banyak kepada LearnKu atas kontribusinya kepada komunitas PHP.

Accessor dan modifier memungkinkan Anda memformat nilai properti model saat
mengambil atau mengatur nilai properti tertentu pada instance model.

## Accessor & Modifier

### Mendefinisikan Accessor

Untuk mendefinisikan accessor, Anda perlu membuat metode `getFooAttribute`
pada model, dan field `Foo` yang akan diakses harus dinamai dengan format
"camelCase". Dalam contoh ini, kita akan mendefinisikan sebuah accessor untuk
properti `first_name`. Accessor ini akan dipanggil secara otomatis saat model
mencoba mengambil properti `first_name`:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Get the user's name.
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

Seperti yang Anda lihat, nilai asli dari field tersebut diteruskan ke dalam
accessor, memungkinkan Anda untuk memprosesnya dan mengembalikan hasilnya.
Untuk mendapatkan nilai yang telah dimodifikasi, Anda dapat mengakses properti
`first_name` pada instance model:

```php
$user = App\User::find(1);

$firstName = $user->first_name;
```

Tentu saja, Anda juga dapat menggunakan nilai properti yang ada dan
menggunakan accessor untuk mengembalikan nilai baru hasil kalkulasi:

```php
namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Get the user's name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
```

### Mendefinisikan Modifier

Untuk mendefinisikan modifier, definisikan metode `setFooAttribute` pada
model. Field `Foo` yang akan diakses dinamai menggunakan format "camelCase".
Mari kita definisikan modifier untuk properti `first_name` lagi. Modifier ini
akan dipanggil secara otomatis saat kita mencoba mengatur nilai dari properti
`first_name` pada skema:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Set the user's name.
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

Modifier menerima nilai dari atribut yang sedang diatur, memungkinkan Anda
untuk mengubah dan menetapkan nilainya ke properti `$attributes` di dalam model.
Sebagai contoh, jika kita mencoba mengatur nilai properti `first_name` menjadi
`Sally`:

```php
$user = App\User::find(1);

$user->first_name = 'Sally';
```

Dalam contoh ini, metode `setFirstNameAttribute` dipanggil dengan nilai
`Sally` sebagai parameternya. Modifier kemudian menerapkan fungsi `strtolower`
dan menyimpan hasil pemrosesan tersebut ke array internal `$attributes`.

## Konverter Tanggal

Secara default, model mengonversi field `created_at` dan `updated_at` menjadi
instance `Carbon`, yang mewarisi kelas bawaan `DateTime` dari PHP dan
menyediakan berbagai metode yang berguna. Anda dapat menambahkan properti
tanggal lainnya dengan mengatur properti `$dates` pada model:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Properties that should be converted to date format.
     *
     * @var array
     */
    protected $dates = [
        'seen_at',
    ];
}

```

> Tip: Anda dapat menonaktifkan timestamp default `created_at` dan
> `updated_at` dengan mengatur nilai publik `$timestamps` pada model menjadi
> `false`.

Saat sebuah field berada dalam format tanggal, Anda dapat mengatur nilainya ke
timestamp `UNIX`, string datetime `(Y-m-d)`, atau instance `DateTime` / `Carbon`.
Nilai tanggal tersebut akan diformat dengan benar dan disimpan ke dalam database
Anda.

Seperti yang disebutkan di atas, saat properti yang diambil tercantum dalam
properti `$dates`, properti tersebut secara otomatis dikonversi menjadi instance
`Carbon`, memungkinkan Anda untuk menggunakan metode `Carbon` apa pun pada
properti tersebut:

```php
$user = App\User::find(1);

return $user->deleted_at->getTimestamp();
```

### Format Waktu

Semua timestamp akan diformat sebagai `Y-m-d H:i:s`. Jika Anda memerlukan
format timestamp kustom, atur properti `$dateFormat` di dalam model. Properti
ini menentukan bagaimana properti tanggal akan disimpan di database, serta
formatnya saat model diserialisasi ke dalam array atau `JSON`:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class Flight extends Model
{
    /**
     * This property should be cast to the native type.
     *
     * @var string
     */
    protected $dateFormat = 'U';
}
```

## Konversi Tipe Atribut

Properti `$casts` pada model menyediakan metode yang memudahkan untuk
mengonversi properti ke tipe data umum. Properti `$casts` harus berupa array
yang kuncinya adalah nama properti yang akan dikonversi, dan nilainya adalah
tipe data tujuan konversi yang Anda inginkan.
Tipe data yang didukung adalah: `integer`, `real`, `float`, `double`,
`decimal:<digits>`, `string`, `boolean`, `object`, `array`, `collection`,
`date`, `datetime`, dan `timestamp`. Saat mengonversi ke tipe `decimal`, Anda
perlu menentukan jumlah digit desimalnya, seperti: `decimal:2`.

Sebagai contoh, mari kita konversi properti `is_admin` yang disimpan di
database sebagai integer (`0` atau `1`) menjadi nilai boolean:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_admin' => 'boolean',
    ];
}
```

Kini saat Anda mengakses properti `is_admin`, meskipun nilai yang disimpan
di database bertipe integer, nilai kembaliannya akan selalu dikonversi menjadi
tipe boolean:

```php
$user = App\User::find(1);

if ($user->is_admin) {
    //
}
```

### Konversi Tipe Kustom

Model telah memiliki beberapa konversi tipe bawaan yang umum. Namun, pengguna
terkadang perlu mengonversi data ke tipe kustom. Sekarang, kebutuhan ini dapat
dipenuhi dengan mendefinisikan kelas yang mengimplementasikan interface
`CastsAttributes`.

Kelas yang mengimplementasikan interface ini harus mendefinisikan metode `get`
dan `set`. Metode `get` bertanggung jawab untuk mengonversi data mentah yang
diperoleh dari database ke tipe yang sesuai, sedangkan metode `set`
mengonversi data ke tipe database yang sesuai untuk disimpan di database.
Sebagai contoh, mari kita implementasikan kembali konversi tipe `json` bawaan
sebagai konversi tipe kustom:

```php
<?php

namespace App\Casts;

use Hyperf\Contract\CastsAttributes;

class Json implements CastsAttributes
{
    /**
     * Convert the extracted data
     */
    public function get($model, $key, $value, $attributes)
    {
        return json_decode($value, true);
    }

    /**
     * Convert to the value to be stored
     */
    public function set($model, $key, $value, $attributes)
    {
        return json_encode($value);
    }
}
```

Setelah konversi tipe kustom didefinisikan, ia dapat dipasang ke properti
model menggunakan nama kelasnya:

```php
<?php

namespace App;

use App\Casts\Json;
use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Properties that should be typecast
     *
     * @var array
     */
    protected $casts = [
        'options' => Json::class,
    ];
}
```

#### Konversi Tipe Value Object

Tidak hanya dapat mengonversi data ke tipe data asli (native), tetapi Anda juga
dapat mengonversi data ke objek. Kedua konversi tipe kustom ini didefinisikan
dengan cara yang sangat mirip. Namun, metode `set` pada kelas konversi kustom
yang mengonversi data menjadi objek harus mengembalikan array pasangan key-value,
yang digunakan untuk menetapkan nilai asli yang dapat disimpan ke dalam model
yang sesuai.

Sebagai contoh, mari definisikan kelas konversi tipe kustom untuk mengonversi
beberapa nilai properti model menjadi satu value object `Address`, dengan asumsi
bahwa objek `Address` memiliki dua properti publik `lineOne` dan `lineTwo`:

```php
<?php

namespace App\Casts;

use App\Address;
use Hyperf\Contract\CastsAttributes;

class AddressCaster implements CastsAttributes
{
    /**
     * Convert the extracted data
     */
    public function get($model, $key, $value, $attributes): Address
    {
        return new Address(
            $attributes['address_line_one'],
            $attributes['address_line_two']
        );
    }

    /**
     * Convert to the value to be stored
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

Setelah konversi tipe value object, perubahan data apa pun pada value object
akan disinkronkan kembali secara otomatis ke model sebelum model disimpan:

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

**Implementasi di sini berbeda dengan Laravel, jika terjadi penggunaan berikut, mohon perhatian khusus**

```php
$user = App\User::find(1);

var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Address Value',
//    'address_line_two' => '#10000'
//];

$user->address->lineOne = 'Updated Address Value';
$user->address->lineTwo = '#20000';

// Setelah memodifikasi field address secara langsung, perubahan tidak akan langsung berpengaruh pada attributes, tetapi Anda dapat mengambil data yang telah dimodifikasi secara langsung melalui $user->address.
var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Address Value',
//    'address_line_two' => '#10000'
//];

// Saat kita menyimpan data atau menghapus data, attributes akan diubah menjadi data yang telah dimodifikasi.
$user->save();
var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Updated Address Value',
//    'address_line_two' => '#20000'
//];
```

Jika setelah mengubah `address`, Anda tidak ingin menyimpannya atau mengambil
data dari `address_line_one` melalui `address->lineOne`, Anda juga dapat
menggunakan metode berikut:

```php
$user = App\User::find(1);
$user->address->lineOne = 'Updated Address Value';
$user->syncAttributes();
var_dump($user->getAttributes());
```

Tentu saja, jika Anda masih perlu memodifikasi fungsi `attributes` secara
sinkron setelah mengubah `value` yang sesuai, Anda dapat mencoba metode berikut.
Pertama, kita mengimplementasikan `UserInfo` dan mewarisi `CastsValue`.

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

Saat kita mengubah UserInfo dengan cara berikut, kita dapat menyinkronkan data
yang diubah ke attributes.

```php
/** @var User $user */
$user = User::query()->find(100);
$user->userInfo->name = 'John1';
var_dump($user->getAttributes()); // ['name' => 'John1']
```

#### Konversi Tipe Inbound

Terkadang, Anda mungkin hanya perlu melakukan typecast pada nilai properti
yang ditulis ke model tanpa melakukan pemrosesan apa pun pada nilai properti
yang diambil dari model. Contoh umum dari konversi tipe inbound adalah "hashing".
Kelas konversi tipe inbound harus mengimplementasikan interface
`CastsInboundAttributes`, dan hanya perlu mengimplementasikan metode `set`.

```php
<?php

namespace App\Casts;

use Hyperf\Contract\CastsInboundAttributes;

class Hash implements CastsInboundAttributes
{
    /**
     * hash algorithm
     *
     * @var string
     */
    protected $algorithm;

    /**
     * Create a new instance of the typecast class
     */
    public function __construct($algorithm = 'md5')
    {
        $this->algorithm = $algorithm;
    }

    /**
     * Convert to the value to be stored
     */
    public function set($model, $key, $value, $attributes)
    {
        return hash($this->algorithm, $value);
    }
}
```

#### Parameter Konversi Tipe

Saat memasang custom cast ke model, Anda dapat menentukan parameter cast yang
diteruskan. Untuk meneruskan parameter konversi tipe, gunakan `:` untuk
memisahkan parameter dari nama kelas, dan gunakan koma untuk memisahkan
beberapa parameter. Parameter-parameter ini akan diteruskan ke konstruktor dari
kelas konversi tipe:

```php
<?php
namespace App;

use App\Casts\Json;
use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Properties that should be typecast
     *
     * @var array
     */
    protected $casts = [
        'secret' => Hash::class.':sha256',
    ];
}
```

### Konversi Array & `JSON`

Konversi tipe `array` sangat berguna saat Anda menyimpan data `JSON` yang
diserialisasi di database. Sebagai contoh: jika database Anda memiliki tipe
field `JSON` atau `TEXT` yang diserialisasi ke `JSON`, dan Anda menambahkan
konversi tipe `array` ke model, field tersebut akan secara otomatis dikonversi
menjadi array `PHP` saat Anda mengaksesnya:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Properties that should be typecast
     *
     * @var array
     */
    protected $casts = [
        'options' => 'array',
    ];
}
```

Setelah konversi didefinisikan, nilai tersebut akan dide-serialisasi secara
otomatis dari tipe `JSON` ke array `PHP` saat Anda mengaksesnya melalui properti
`options`. Saat Anda mengatur nilai properti `options`, array yang diberikan
juga akan diserialisasi secara otomatis ke tipe penyimpanan `JSON`:

```php
$user = App\User::find(1);

$options = $user->options;

$options['key'] = 'value';

$user->options = $options;

$user->save();
```

### Konversi Tipe Tanggal

Saat menggunakan atribut `date` atau `datetime`, Anda dapat menentukan format
tanggalnya. Format ini akan digunakan saat model diserialisasi sebagai array
atau `JSON`:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Properties that should be typecast
     *
     * @var array
     */
    protected $casts = [
         'created_at' => 'datetime:Y-m-d',
    ];
}
```

### Konversi Tipe Saat Query (Query-time)

Ada kalanya Anda perlu melakukan typecast pada properti tertentu selama eksekusi
query, misalnya saat Anda perlu mengambil data dari tabel database. Sebagai
contoh, perhatikan query berikut:

```php
use App\Post;
use App\User;

$users = User::select([
    'users.*',
    'last_posted_at' => Post::selectRaw('MAX(created_at)')
            ->whereColumn('user_id', 'users.id')
])->get();
```

Dalam kumpulan hasil (result set) yang diperoleh dari query ini, atribut
`last_posted_at` akan berupa string. Akan lebih mudah jika kita melakukan
konversi tipe `date` saat mengeksekusi query. Anda dapat melakukannya dengan
menggunakan metode `withCasts`:

```php
$users = User::select([
    'users.*',
    'last_posted_at' => Post::selectRaw('MAX(created_at)')
            ->whereColumn('user_id', 'users.id')
])->withCasts([
    'last_posted_at' => 'date'
])->get();
```
