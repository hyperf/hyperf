# Validator

## Kata Pengantar

> [hyperf/validation](https://github.com/hyperf/validation) diturunkan dari
> [illuminate/validation](https://github.com/illuminate/validation). Kami telah
> melakukan beberapa modifikasi di dalamnya, tetapi tetap mempertahankan aturan
> validasi yang sama. Terima kasih kepada tim pengembang Laravel karena telah
> mengimplementasikan komponen validator yang sangat kuat dan mudah digunakan.

## Instalasi

### Impor paket komponen

```bash
composer require hyperf/validation
```

### Tambahkan middleware

Anda perlu menambahkan konfigurasi global middleware
`Hyperf\Validation\Middleware\ValidationMiddleware` ke file konfigurasi
`config/autoload/middlewares.php` untuk server yang menggunakan komponen
validator. Berikut adalah contoh global middleware untuk server `http`:

```php
<?php
return [
    // The following http string corresponds to the value corresponding to the name attribute of each server in config/autoload/server.php, which means that the corresponding middleware configuration is only applied to the server
    'http' => [
        // Configure your global middleware in the array, the order is based on the order of the array
        \Hyperf\Validation\Middleware\ValidationMiddleware::class
        // Other middleware goes here
    ],
];
```

> Jika global middleware tidak dikonfigurasi dengan benar, penggunaan
> `FormRequest` mungkin tidak akan berfungsi.

### Tambahkan exception handler

Exception handler ini terutama menangani exception
`Hyperf\Validation\ValidationException`. Kami menyediakan
`Hyperf\Validation\ValidationExceptionHandler` untuk memprosesnya. Anda perlu
mengonfigurasi exception handler ini secara manual ke proyek Anda dengan
menambahkannya ke file `config/autoload/exceptions.php`. Tentu saja, Anda juga
dapat menyesuaikan exception handler Anda sendiri.

```php
<?php
return [
    'handler' => [
        // This corresponds to your current server name
        'http' => [
            \Hyperf\Validation\ValidationExceptionHandler::class,
        ],
    ],
];
```

### Publikasikan file bahasa validator

Karena fitur multi-bahasa, komponen ini bergantung pada komponen
[hyperf/translation](https://github.com/hyperf/translation). Jika Anda belum
menambahkan file konfigurasi dari komponen translation, Anda dapat menjalankan
perintah berikut untuk mempublikasikan file konfigurasi komponen translation.
Jika konfigurasi sudah ada, Anda hanya perlu mempublikasikan file bahasa dari
komponen validator:

Publikasikan file dari komponen translation:

```bash
php bin/hyperf.php vendor:publish hyperf/translation
```

Publikasikan file dari komponen validator:

```bash
php bin/hyperf.php vendor:publish hyperf/validation
```

Menjalankan perintah di atas akan mempublikasikan file bahasa validator
`validation.php` ke direktori file bahasa yang sesuai, di mana `en` merujuk ke
file bahasa Inggris, dan `zh_CN` merujuk ke file bahasa Mandarin Sederhana. Anda
dapat menyesuaikan isi dari file tersebut.

```
/storage
    /languages
        /en
            validation.php
        /zh_CN
            validation.php

```

## Penggunaan

### Validasi Form Request

Untuk skenario validasi yang kompleks, Anda dapat membuat `FormRequest`. Form
request adalah class request kustom yang berisi logika validasi. Anda dapat
membuat class validasi form bernama `FooRequest` dengan menjalankan perintah
berikut:

```bash
php bin/hyperf.php gen:request FooRequest
```

Class validasi form akan dibuat di direktori `app/Request` (atau `app\Request`).
Jika direktori tersebut belum ada, direktori akan dibuat secara otomatis saat
menjalankan perintah.
Selanjutnya, kita tambahkan beberapa aturan validasi ke method `rules` dari
class ini:

```php
/**
 * Get the validation rules applied to the request
 */
public function rules(): array
{
    return [
        'foo' => 'required|max:255',
        'bar' => 'required',
    ];
}
```

Jadi, bagaimana aturan validasi ini dapat berjalan? Yang harus Anda lakukan
adalah mendeklarasikan class request tersebut sebagai parameter melalui type
hint pada method controller. Dengan cara ini, request form yang masuk akan
divalidasi sebelum method controller dipanggil, yang berarti Anda tidak perlu
menulis logika validasi apa pun di dalam controller dan memisahkan kedua bagian
kode tersebut dengan baik:

```php
<?php
namespace App\Controller;

use App\Request\FooRequest;

class IndexController
{
    public function index(FooRequest $request)
    {
        // The incoming request is verified...

        // Get the verified data...
        $validated = $request->validated();
    }
}
```

Jika validasi gagal, validator akan melemparkan exception
`Hyperf\Validation\ValidationException`. Anda dapat menangani exception ini
dengan menambahkan class penanganan exception kustom. Pada saat yang sama, kami
juga menyediakan exception handler `Hyperf\Validation\ValidationExceptionHandler`
untuk menangani exception tersebut. Anda juga dapat langsung mengonfigurasi
exception handler yang kami sediakan untuk menanganinya. Namun, exception handler
bawaan mungkin tidak dapat memenuhi kebutuhan Anda. Anda dapat menyesuaikan
perilaku setelah kegagalan validasi dengan menyesuaikan exception handler
sesuai situasi.

#### Kustomisasi Pesan Error

Anda dapat menyesuaikan pesan error yang digunakan oleh form request dengan
meng-override method `messages`. Method ini harus mengembalikan array dari
pasangan atribut/aturan beserta pesan error yang sesuai:

```php
/**
 * Get the error message of the defined validation rule
 */
public function messages(): array
{
    return [
        'foo.required' => 'foo is required',
        'bar.required' => 'bar is required',
    ];
}
```

#### Kustomisasi Atribut Validasi

Jika Anda ingin mengganti bagian `:attribute` dari pesan validasi dengan nama
atribut kustom, Anda dapat meng-override method `attributes` untuk menentukan
nama kustom. Method ini akan mengembalikan array nama atribut dan pasangan
key-value nama kustom yang sesuai:

```php
/**
 * Get custom attributes for validation errors
 */
public function attributes(): array
{
    return [
        'foo' => 'foo of request',
    ];
}
```

### Membuat Validator Secara Manual

Jika Anda tidak ingin menggunakan fungsi validasi otomatis dari `FormRequest`,
Anda dapat memperoleh class validator factory dengan menginjeksikan interface
`ValidatorFactoryInterface`, kemudian membuat instance validator secara manual
melalui method `make`:

```php
<?php

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

class IndexController
{
    #[Inject]
    protected ValidatorFactoryInterface $validationFactory;

    public function foo(RequestInterface $request)
    {
        $validator = $this->validationFactory->make(
            $request->all(),
            [
                'foo' => 'required',
                'bar' => 'required',
            ],
            [
                'foo.required' => 'foo is required',
                'bar.required' => 'bar is required',
            ]
        );

        if ($validator->fails()){
            // Handle exception
            $errorMessage = $validator->errors()->first();
        }
        // Do something
    }
}
```

Parameter pertama yang diteruskan ke method `make` adalah data yang akan
divalidasi, dan parameter kedua adalah aturan validasi untuk data tersebut.

#### Kustomisasi Pesan Error

Jika perlu, Anda juga dapat menggunakan pesan error kustom alih-alih nilai
bawaan untuk validasi. Ada beberapa cara untuk menentukan informasi kustom.
Pertama, Anda dapat meneruskan informasi kustom sebagai parameter ketiga ke
method `make`:

```php
<?php
$messages = [
    'required' => 'The :attribute field is required.',
];

$validator = $this->validationFactory->make($request->all(), $rules, $messages);
```

Dalam contoh ini, placeholder `:attribute` akan digantikan oleh nama asli
dari field yang divalidasi. Selain itu, Anda juga dapat menggunakan placeholder
lain dalam pesan validasi. Contoh:

```php
$messages = [
    'same' => 'The :attribute and :other must match.',
    'size' => 'The :attribute must be exactly :size.',
    'between' => 'The :attribute value :input is not between :min-:max.',
    'in' => 'The :attribute must be one of the following types: :values',
];
```

#### Menentukan Informasi Kustom untuk Atribut Tertentu

Terkadang Anda mungkin hanya ingin menyesuaikan pesan error untuk field
tertentu. Cukup tambahkan `.` setelah nama field untuk menentukan aturan
validasi dengan pesan kustom:

```php
$messages = [
    'email.required' => 'We need to know your e-mail address!',
];
```

#### Menentukan Informasi Kustom di File PHP

Dalam kebanyakan kasus, Anda mungkin ingin menentukan informasi kustom di
dalam file alih-alih meneruskannya secara langsung ke `Validator`. Untuk
melakukan ini, Anda perlu menempatkan informasi Anda di dalam array `custom`
pada file bahasa `storage/languages/xx/validation.php`.

#### Menentukan Atribut Kustom di File PHP

Jika Anda ingin mengganti bagian `:attribute` dari informasi validasi dengan
nama atribut kustom, Anda dapat menentukan nama kustom di dalam array
`attributes` pada file bahasa `storage/languages/xx/validation.php`:

```php
'attributes' => [
    'email' => 'email address',
],
```

### Hook Pasca-Validasi

Validator juga memungkinkan Anda untuk menambahkan callback function setelah
validasi berhasil, sehingga Anda dapat melakukan langkah validasi berikutnya,
dan bahkan menambahkan lebih banyak pesan error ke dalam koleksi pesan. Untuk
menggunakannya, cukup gunakan method `after` pada instance validator:

```php
<?php

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

class IndexController
{
    #[Inject]
    protected ValidatorFactoryInterface $validationFactory;

    public function foo(RequestInterface $request)
    {
        $validator = $this->validationFactory->make(
            $request->all(),
            [
                'foo' => 'required',
                'bar' => 'required',
            ],
            [
                'foo.required' => 'foo is required',
                'bar.required' => 'bar is required',
            ]
        );

        $validator->after(function ($validator) {
            if ($this->somethingElseIsInvalid()) {
                $validator->errors()->add('field','Something is wrong with this field!');
            }
        });

        if ($validator->fails()) {
            //
        }
    }
}
```

## Menangani Pesan Error

Memanggil method `errors` melalui instance `Validator` akan mengembalikan
instance `Hyperf\Support\MessageBag`, yang memiliki berbagai method praktis
untuk menangani pesan error.

### Melihat Pesan Error Pertama dari Field Tertentu

Untuk melihat pesan error pertama dari field tertentu, Anda dapat menggunakan
method `first`:

```php
$errors = $validator->errors();

echo $errors->first('foo');
```

### Melihat Semua Pesan Error dari Field Tertentu

Jika Anda perlu mendapatkan array berisi semua pesan error untuk field
tertentu, Anda dapat menggunakan method `get`:

```php
foreach ($errors->get('foo') as $message) {
    //
}
```

Jika Anda ingin memvalidasi field array pada form, Anda dapat menggunakan `*`
untuk mendapatkan semua pesan error dari setiap elemen array:

```php
foreach ($errors->get('foo.*') as $message) {
    //
}
```

### Melihat Semua Pesan Error untuk Semua Field

Jika Anda ingin mendapatkan semua pesan error untuk semua field, Anda dapat
menggunakan method `all`:

```php
foreach ($errors->all() as $message) {
    //
}
```

### Menentukan Apakah Field Tertentu Memiliki Pesan Error

Method `has` dapat digunakan untuk menentukan apakah terdapat pesan error pada
field tertentu yang ditentukan:

```php
if ($errors->has('foo')) {
    //
}
```

### Scene (Skenario)

Validator menambahkan fungsi scenario, sehingga kita dapat dengan mudah
memodifikasi aturan validasi sesuai kebutuhan.

> Fitur ini memerlukan versi komponen ini bernilai lebih besar dari atau
> sama dengan 2.2.7.

Buat `SceneRequest` seperti berikut:

```php
<?php
declare(strict_types=1);
namespace App\Request;
use Hyperf\Validation\Request\FormRequest;
class SceneRequest extends FormRequest
{
    protected array $scenes = [
        'foo' => ['username'],
        'bar' => ['username', 'password'],
    ];
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'username' => 'required',
            'gender' => 'required',
        ];
    }
}
```

Ketika kita menggunakannya secara normal, semua aturan validasi akan digunakan,
yaitu `username` and `gender` bersifat wajib.

Kita dapat mengatur scenario sehingga request ini hanya memvalidasi field wajib
`username`.

Jika kita mengonfigurasi `Hyperf\Validation\Middleware\ValidationMiddleware`
dan menginjeksikan `SceneRequest` ke method tersebut, hal itu akan menyebabkan
input divalidasi secara langsung di middleware. Oleh karena itu, kita perlu
mengambil `SceneRequest` dari container di dalam method untuk beralih scenario.

```php
<?php
namespace App\Controller;
use App\Request\DebugRequest;
use App\Request\SceneRequest;
use Hyperf\HttpServer\Annotation\AutoController;
#[AutoController(prefix: 'foo')]
class FooController extends Controller
{
    public function scene()
    {
        $request = $this->container->get(SceneRequest::class);
        $request->scene('foo')->validateResolved();
        return $this->response->success($request->all());
    }
}
```

Namun, kita dapat menggunakan annotation `Scene` untuk beralih scenario.

```php
<?php

namespace App\Controller;

use App\Request\DebugRequest;
use App\Request\SceneRequest;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\Validation\Annotation\Scene;

#[AutoController(prefix: 'foo')]
class FooController extends Controller
{
    #[Scene(scene:'bar1')]
    public function bar1(SceneRequest $request)
    {
        return $this->response->success($request->all());
    }

    #[Scene(scene:'bar2', argument: 'request')] // bind $request
    public function bar2(SceneRequest $request)
    {
        return $this->response->success($request->all());
    }

    #[Scene(scene:'bar3', argument: 'request')] // bind $request
    #[Scene(scene:'bar3', argument: 'req')] // bind $req
    public function bar3(SceneRequest $request, DebugRequest $req)
    {
        return $this->response->success($request->all());
    }

    #[Scene()] // the default scene is method name, The effect is equivalent to #[Scene(scene: 'bar1')]
    public function bar1(SceneRequest $request)
    {
        return $this->response->success($request->all());
    }
}
```

## Aturan Validasi

Berikut adalah daftar aturan validasi beserta fungsinya:

##### accepted

Nilai dari field yang divalidasi harus berupa `yes`, `on`, `1`, atau `true`,
yang berguna saat "menyetujui perjanjian layanan".

##### active_url

Field yang divalidasi harus merupakan URL yang valid berdasarkan function PHP
`dns_get_record`, dengan nilai record berupa `A` atau `AAAA`.

##### after:date

Field yang divalidasi harus bernilai setelah tanggal yang ditentukan, dan
tanggal tersebut akan diproses melalui function PHP `strtotime`:

```php
'start_date' => 'required|date|after:tomorrow'
```

Alih-alih meneruskan string tanggal ke `strtotime`, Anda dapat menentukan
field lain untuk dibandingkan dengan tanggal tersebut:

```php
'finish_date' => 'required|date|after:start_date'
```

##### after_or_equal:date

Field yang divalidasi harus bernilai lebih besar dari atau sama dengan tanggal
yang ditentukan. Untuk informasi lebih lanjut, silakan merujuk pada aturan
`after:date`.

##### alpha

Field yang divalidasi harus berupa huruf (termasuk karakter Mandarin).

##### alpha_dash

Field yang divalidasi dapat berisi huruf (termasuk karakter Mandarin) dan
angka, serta tanda hubung (dash) dan garis bawah (underscore).

##### alpha_num

Field yang divalidasi harus berupa huruf (termasuk karakter Mandarin) atau angka.

##### array

Field yang divalidasi harus berupa array PHP.

##### bail

Jika aturan validasi pertama gagal diverifikasi, hentikan menjalankan aturan
validasi lainnya.

##### before:date

Kebalikan dari `after:date`, field yang divalidasi harus bernilai sebelum
tanggal yang ditentukan, dan tanggal tersebut akan diteruskan ke function PHP
`strtotime`.

##### before_or_equal:date

Field yang divalidasi harus bernilai kurang dari atau sama dengan tanggal yang
ditentukan. Tanggal tersebut akan diteruskan ke function PHP `strtotime`.

##### between:min,max

Memverifikasi bahwa ukuran field berada di antara nilai minimum dan maksimum
yang ditentukan. String, angka, array, dan file semuanya dapat menggunakan
aturan ini seperti aturan `size`:

```php
'name' => 'required|between:1,20'
```

##### boolean

Field yang divalidasi harus dapat dikonversi menjadi nilai boolean dan
menerima input seperti true, false, 1, 0, "1", dan "0".

##### confirmed

Field yang divalidasi harus memiliki field pencocokan `foo_confirmation`.
Sebagai contoh, jika field yang divalidasi adalah `password`, Anda harus
memasukkan field `password_confirmation` yang cocok.

##### date

Field yang divalidasi harus berupa tanggal yang valid berdasarkan function PHP
`strtotime`.

##### date_equals:date

Field yang divalidasi harus sama dengan tanggal yang ditentukan, dan tanggal
tersebut akan diteruskan ke function PHP `strtotime`.

##### date_format:format

Field yang divalidasi harus cocok dengan format yang ditentukan. Anda dapat
menggunakan function PHP `date` atau `date_format` untuk memvalidasi field
tersebut.

##### different:field

Field yang divalidasi harus memiliki nilai yang berbeda dari field yang
ditentukan.

##### digits:value

Field yang divalidasi harus berupa angka dan panjangnya harus bernilai sama
dengan nilai yang ditentukan oleh `value`.

##### digits_between:min,max

Panjang dari field yang divalidasi harus berada di antara nilai minimum dan
maksimum.

##### dimensions

Ukuran gambar yang divalidasi harus memenuhi batasan yang ditentukan oleh
parameter-parameter berikut:

```php
'avatar' => 'dimensions:min_width=100,min_height=200'
```

Batasan yang valid meliputi: `min_width`, `max_width`, `min_height`,
`max_height`, `width`, `height`, `ratio`.

`ratio` membatasi rasio lebar/tinggi, yang dapat dinyatakan dengan ekspresi
`3/2` atau angka desimal `1.5`:

```php
'avatar' => 'dimensions:ratio=3/2'
```

Karena aturan ini membutuhkan banyak parameter, Anda dapat menggunakan method
`Rule::dimensions` untuk menyusun aturan tersebut:

```php
use Hyperf\Validation\Rule;

public function rules(): array
{
    return [
        'avatar' => [
            'required',
            Rule::dimensions()->maxWidth(1000)->maxHeight(500)->ratio(3 / 2),
        ],
    ];
}
```

##### distinct

Ketika memproses array, field yang divalidasi tidak boleh berisi nilai duplikat:

```php
'foo.*.id' => 'distinct'
```

##### email

Field yang divalidasi harus berupa alamat email dengan format yang benar.

##### exists:table,column

Field yang divalidasi harus ada di tabel data yang ditentukan.

Penggunaan dasar:

```php
'state' => 'exists:states'
```

Jika opsi `column` tidak ditentukan, nama field akan digunakan.

Menentukan nama kolom kustom:

```php
'state' => 'exists:states,abbreviation'
```

Terkadang, Anda mungkin perlu menentukan koneksi database yang akan digunakan
untuk query `exists`. Hal ini dapat dicapai dengan menggunakan awalan koneksi
database diikuti titik `.` sebelum nama tabel, atau secara otomatis diselesaikan
dengan menentukan nama class model:

```php
// Metode awalan koneksi database
'email' => 'exists:connection.staff,email'

// Menyelesaikan nama class model secara otomatis
'email' => 'exists:StaffModel::class,email'
```

Jika Anda ingin menyesuaikan query yang dijalankan oleh aturan validasi, Anda
dapat menggunakan class `Rule` untuk menentukan aturan tersebut. Dalam contoh
ini, kita juga menentukan aturan validasi dalam bentuk array, alih-alih
menggunakan karakter `|` untuk membatasinya:

```php
use Hyperf\Validation\Rule;

$validator = $this->validationFactory->make($data, [
    'email' => [
        'required',
        Rule::exists('staff')->where(function ($query) {
            $query->where('account_id', 1);
        }),
    ],
]);
```

##### file

Field yang divalidasi harus berupa file yang berhasil diunggah.

##### filled

Field yang divalidasi tidak boleh kosong jika field tersebut ada.

##### gt:field

Field yang divalidasi harus lebih besar dari field `field` yang diberikan, dan
kedua tipe field tersebut harus sama. Ini berlaku untuk string, angka, array,
dan file, mirip dengan aturan `size`.

##### gte:field

Field yang divalidasi harus lebih besar dari atau sama dengan field `field` yang
diberikan, dan kedua tipe field tersebut harus sama. Ini berlaku untuk string,
angka, array, dan file, mirip dengan aturan `size`.

##### image

File yang divalidasi harus berupa gambar (`jpeg`, `png`, `bmp`, `gif`, atau `svg`).

##### in:foo,bar...

Nilai field yang divalidasi harus berada dalam daftar yang ditentukan. Karena
aturan ini sering kali mengharuskan kita untuk menggabungkan (implode) array, kita
dapat menggunakan `Rule::in` untuk menyusun aturan ini:

```php
use Hyperf\Validation\Rule;

$validator = $this->validationFactory->make($data, [
    'zones' => [
        'required',
        Rule::in(['first-zone','second-zone']),
    ],
]);
```

##### in_array:anotherfield

Field yang divalidasi harus ada di dalam nilai field lain.

##### integer

Field yang divalidasi harus berupa integer.

##### ip

Field yang divalidasi harus berupa alamat IP.

##### ipv4

Field yang divalidasi harus berupa alamat IPv4.

##### ipv6

Field yang divalidasi harus berupa alamat IPv6.

##### json

Field yang divalidasi harus berupa string JSON yang valid.

##### lt:field

Field yang divalidasi harus lebih kecil dari field `field` yang diberikan, dan
kedua tipe field tersebut harus sama. Ini berlaku untuk string, angka, array,
dan file, mirip dengan aturan `size`.

##### lte:field

Field yang divalidasi harus kurang dari atau sama dengan field `field` yang
diberikan, dan kedua tipe field tersebut harus sama. Ini berlaku untuk string,
angka, array, dan file, mirip dengan aturan `size`.

##### max:value

Field yang divalidasi harus kurang dari atau sama dengan nilai maksimum, yang
cara penggunaannya sama dengan aturan `size` untuk field string, angka, array,
dan file.

##### mimetypes:text/plain...

File yang divalidasi harus cocok dengan salah satu tipe file `MIME` yang
ditentukan:

```php
'video' => 'mimetypes:video/avi,video/mpeg,video/quicktime'
```

Untuk menentukan tipe `MIME` dari file yang diunggah, komponen akan membaca isi
file untuk menebak tipe `MIME` tersebut, yang mungkin berbeda dari tipe `MIME`
milik klien.

##### mimes:foo,bar,...

Tipe `MIME` dari file yang divalidasi harus berupa salah satu tipe ekstensi
yang tercantum dalam aturan.
Penggunaan dasar aturan `MIME`:

```php
'photo' => 'mimes:jpeg,bmp,png'
```

Meskipun Anda hanya menentukan ekstensinya, aturan ini sebenarnya memverifikasi
tipe `MIME` file yang didapatkan dari membaca isi file.
Daftar lengkap tipe `MIME` beserta ekstensi yang sesuai dapat ditemukan di sini:
[mime types](http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types)

##### min:value

Kebalikan dari `max:value`, field yang divalidasi harus lebih besar dari atau
sama dengan nilai minimum. Untuk field string, angka, array, dan file, aturan
ini konsisten dengan penggunaan aturan `size`.

##### not_in:foo,bar,...

Nilai field yang divalidasi tidak boleh berada dalam daftar yang ditentukan.
Mirip dengan aturan `in`, kita dapat menggunakan method `Rule::notIn` untuk
menyusun aturan tersebut:

```php
use Hyperf\Validation\Rule;

$validator = $this->validationFactory->make($data, [
    'toppings' => [
        'required',
        Rule::notIn(['sprinkles','cherries']),
    ],
]);
```

##### not_regex:pattern

Field yang divalidasi tidak boleh cocok dengan regular expression yang ditentukan.

Catatan: Saat menggunakan mode `regex/not_regex`, aturan harus ditempatkan
dalam sebuah array alih-alih menggunakan pemisah pipa (pipe), terutama jika
regular expression tersebut mengandung simbol pipa.

##### nullable

Field yang divalidasi boleh bernilai `null`, yang berguna saat memvalidasi
beberapa data primitif yang bisa bernilai `null` seperti integer atau string.

##### numeric

Field yang divalidasi harus berupa angka.

##### present

Field yang divalidasi harus ada dalam data input tetapi boleh kosong.

##### regex:pattern

Field yang divalidasi harus cocok dengan regular expression yang ditentukan.
Bagian dasar dari aturan ini menggunakan function `preg_match` PHP. Oleh karena
itu, pola yang ditentukan harus mengikuti format yang dibutuhkan oleh function
`preg_match` dan berisi pemisah (separator) yang valid. Contoh:

```php
 'email' => 'regex:/^.+@.+$/i'
```

Catatan: Saat menggunakan mode `regex/not_regex`, aturan harus ditempatkan
dalam sebuah array alih-alih menggunakan pemisah pipa, terutama jika regular
expression tersebut mengandung simbol pipa.

##### required

Nilai field yang divalidasi tidak boleh kosong, dan nilai field dianggap kosong
dalam kasus-kasus berikut:
- Nilainya adalah `null`
- Nilainya adalah string kosong
- Nilainya adalah array kosong atau objek `Countable` yang kosong
- Nilainya adalah file yang diunggah tetapi path-nya kosong

##### required_if:anotherfield,value,...

Field yang divalidasi harus ada dan tidak boleh kosong ketika `anotherfield`
bernilai sama dengan nilai `value` yang ditentukan.
Jika Anda ingin menyusun kondisi yang lebih kompleks untuk aturan `required_if`,
Anda dapat menggunakan method `Rule::requiredIf`, yang menerima nilai boolean
atau closure. Ketika meneruskan closure, ia akan mengembalikan `true` atau
`false` untuk menunjukkan apakah field yang divalidasi tersebut bersifat wajib:

```php
use Hyperf\Validation\Rule;

$validator = $this->validationFactory->make($request->all(), [
    'role_id' => Rule::requiredIf($request->user()->is_admin),
]);

$validator = $this->validationFactory->make($request->all(), [
    'role_id' => Rule::requiredIf(function () use ($request) {
        return $request->user()->is_admin;
    }),
]);
```

##### required_unless:anotherfield,value,...

Kecuali field `anotherfield` bernilai sama dengan `value`, field yang
divalidasi tidak boleh kosong.

##### required_with:foo,bar,...

Field yang divalidasi hanya wajib diisi jika salah satu field lain yang
ditentukan ada.

##### required_with_all:foo,bar,...

Field yang divalidasi hanya wajib diisi jika semua field yang ditentukan ada.

##### required_without:foo,bar,...

Field yang divalidasi hanya wajib diisi jika salah satu field yang ditentukan
tidak ada.

##### required_without_all:foo,bar,...

Field yang divalidasi hanya wajib diisi jika semua field yang ditentukan
tidak ada.

##### same:field

Field yang diberikan dan field yang divalidasi harus cocok.

##### size:value

Field yang divalidasi harus memiliki ukuran yang cocok dengan nilai `value`
yang diberikan. Untuk string, `value` adalah jumlah karakter; untuk angka,
`value` adalah nilai integer yang ditentukan; untuk array, `value` adalah
panjang array; untuk file, `value` adalah ukuran file dalam kilobyte (KB).

##### starts_with:foo,bar,...

Field yang divalidasi harus diawali dengan salah satu nilai yang ditentukan.

##### string

Field yang divalidasi harus berupa string. Jika field diperbolehkan kosong,
Anda perlu menetapkan aturan `nullable` pada field tersebut.

##### timezone

Karakter validasi harus berupa pengenal zona waktu (time zone identifier) yang
valid berdasarkan function PHP `timezone_identifiers_list`.

##### unique:table,column,except,idColumn

Field yang divalidasi harus bersifat unik di tabel data yang diberikan. Jika
opsi `column` tidak ditentukan, nama field akan digunakan sebagai `column`
default.

1. Menentukan nama kolom kustom:

```php
'email' => 'unique:users,email_address'
```

2. Koneksi database kustom:
Terkadang, Anda mungkin perlu menyesuaikan koneksi database yang digunakan oleh
validator. Seperti yang terlihat di atas, menetapkan `unique:users` as the
aturan validasi akan menggunakan koneksi database default untuk menanyakan
database. Untuk meng-override koneksi default, gunakan titik `.` setelah nama
tabel data untuk menentukan koneksi, atau secara otomatis diselesaikan dengan
menentukan nama class model:

```php
// Metode awalan koneksi database
'email' => 'unique:connection.users,email_address'

// Menyelesaikan nama class model secara otomatis
'email' => 'unique:UserModel::class,email_address'
```

3. Memaksakan aturan unik yang mengabaikan `ID` tertentu:
Terkadang, Anda mungkin ingin mengabaikan `ID` tertentu selama pemeriksaan
keunikan. Sebagai contoh, bayangkan sebuah antarmuka "perbarui properti" yang
mencakup nama pengguna, alamat email, dan lokasi. Anda ingin memverifikasi
bahwa alamat email tersebut unik. Mengubah field username tidak mengubah field
email. Anda tidak ingin melemparkan error validasi karena pengguna tersebut sudah
memiliki alamat email itu. Anda hanya ingin melemparkan error validasi ketika
email yang diberikan oleh pengguna telah digunakan oleh orang lain.

Untuk memberi tahu validator agar mengabaikan ID pengguna, Anda dapat
menggunakan class `Rule` untuk mendefinisikan aturan ini. Kita juga perlu
menentukan aturan validasi dalam sebuah array alih-alih menggunakan `|`
untuk mendefinisikan aturan tersebut:

```php
use Hyperf\Validation\Rule;

$validator = $this->validationFactory->make($data, [
    'email' => [
        'required',
        Rule::unique('users')->ignore($user->id),
    ],
]);
```

Selain meneruskan nilai primary key dari instance model ke method `ignore`,
Anda juga dapat meneruskan seluruh instance model. Komponen akan secara
otomatis mengurai nilai primary key dari instance model tersebut:

```php
Rule::unique('users')->ignore($user)
```

Jika tabel data Anda menggunakan field primary key selain `id`, Anda dapat
menentukan nama field tersebut saat memanggil method `ignore`:

```php
'email' => Rule::unique('users')->ignore($user->id,'user_id')
```

Secara default, aturan `unique` akan memeriksa keunikan kolom yang cocok dengan
nama atribut yang divalidasi. Namun, Anda dapat menentukan nama kolom yang
berbeda sebagai parameter kedua dari method unique:

```php
Rule::unique('users','email_address')->ignore($user->id),
```

4. Menambahkan klausa `where` tambahan:

Anda juga dapat menentukan batasan query tambahan saat menggunakan method
`where` untuk menyesuaikan query. Sebagai contoh, mari kita tambahkan batasan
yang memverifikasi bahwa `account_id` bernilai 1:

```php
'email' => Rule::unique('users')->where(function ($query) {
    $query->where('account_id', 1);
})
```

##### url

Field yang divalidasi harus berupa URL yang valid.

##### uuid

Field yang divalidasi harus berupa universally unique identifier (UUID)
RFC 4122 (versi 1, 3, 4, atau 5) yang valid.

##### sometimes

Menambahkan aturan kondisional
Verifikasi ketika data ada

Dalam beberapa skenario, Anda mungkin ingin melakukan pemeriksaan validasi
hanya jika field tertentu ada. Untuk menerapkan ini dengan cepat, tambahkan
aturan `sometimes` ke daftar aturan:

```php
$validator = $this->validationFactory->make($data, [
    'email' => 'sometimes|required|email',
]);
```

Dalam contoh di atas, field `email` hanya akan divalidasi jika field tersebut
ada di dalam array `$data`.

Catatan: Jika Anda mencoba memverifikasi field yang selalu ada tetapi mungkin
kosong, silakan merujuk pada pertimbangan field opsional.

Validasi kondisi yang kompleks

Terkadang Anda mungkin ingin menambahkan aturan validasi berdasarkan logika
kondisional yang lebih kompleks. Sebagai contoh, Anda mungkin ingin mewajibkan
field tertentu hanya ketika nilai field lain lebih besar dari 100, atau Anda
mungkin perlu mewajibkan kedua field memiliki nilai tertentu hanya ketika
field yang lain ada. Menambahkan aturan validasi ini tidaklah membingungkan.
Pertama, buat aturan statis yang tidak akan pernah berubah pada instance
`Validator`:

```php
$validator = $this->validationFactory->make($data, [
    'email' => 'required|email',
    'games' => 'required|numeric',
]);
```

Mari kita asumsikan bahwa aplikasi web kita melayani kolektor game. Jika
seorang kolektor game mendaftar di aplikasi kita dan memiliki lebih dari 100
game, kita ingin mereka menjelaskan mengapa mereka memiliki begitu banyak game.
Sebagai contoh, mungkin mereka menjalankan toko game bekas, atau mereka hanya
suka mengoleksi. Untuk menambahkan kondisi ini, kita dapat menggunakan method
`sometimes` pada instance `Validator`:

```php
$v->sometimes('reason','required|max:500', function($input) {
    return $input->games >= 100;
});
```

Parameter pertama yang diteruskan ke method `sometimes` adalah nama field yang
ingin kita validasi secara kondisional, dan parameter kedua adalah aturan yang
ingin kita tambahkan. Jika closure sebagai parameter ketiga mengembalikan
`true`, aturan tersebut akan ditambahkan. Metode ini memudahkan pembangunan
validasi kondisional yang kompleks, dan Anda bahkan dapat menambahkan validasi
kondisional untuk beberapa field sekaligus:

```php
$v->sometimes(['reason','cost'],'required', function($input) {
    return $input->games >= 100;
});
```

Catatan: Parameter `$input` yang diteruskan ke closure adalah instance dari
`Hyperf\Support\Fluent` dan dapat digunakan untuk mengakses input serta file.

### Memvalidasi Input Array

Memverifikasi field input dari array form tidak lagi menjadi hal yang sulit.
Sebagai contoh, jika HTTP request yang masuk berisi field `photos[profile]`,
Anda dapat memverifikasinya seperti ini:

```php
$validator = $this->validationFactory->make($request->all(), [
    'photos.profile' => 'required|image',
]);
```

Kita juga dapat memverifikasi setiap elemen array. Sebagai contoh, untuk
memverifikasi bahwa setiap email dalam input array yang diberikan bersifat unik,
kita dapat melakukannya seperti ini (field array yang dikirimkan ini adalah
array dua dimensi, seperti `person[][email]` atau `person[test][email]`):

```php
$validator = $this->validationFactory->make($request->all(), [
    'person.*.email' => 'email|unique:users',
    'person.*.first_name' => 'required_with:person.*.last_name',
]);
```

Demikian pula, di dalam file bahasa, Anda juga dapat menggunakan karakter `*`
untuk menentukan pesan validasi, sehingga Anda dapat menggunakan satu pesan
validasi untuk mendefinisikan aturan validasi berdasarkan field array:

```php
'custom' => [
    'person.*.email' => [
        'unique' => 'E-mail address of each person must be unique',
    ]
],
```

### Aturan Validasi Kustom

#### Mendaftarkan Aturan Validasi Kustom

Komponen `Validation` menggunakan mekanisme event untuk menerapkan aturan
validasi kustom. Kami telah mendefinisikan event `ValidatorFactoryResolved`.
Yang perlu Anda lakukan adalah mendefinisikan listener untuk
`ValidatorFactoryResolved` and implementasikan registrasi validator di dalam
listener tersebut. Contohnya adalah sebagai berikut:

```php
namespace App\Listener;

use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\Event\ValidatorFactoryResolved;
use Hyperf\Validation\Validator;

#[Listener]
class ValidatorFactoryResolvedListener implements ListenerInterface
{

    public function listen(): array
    {
        return [
            ValidatorFactoryResolved::class,
        ];
    }

    public function process(object $event): void
    {
        /** @var ValidatorFactoryInterface $validatorFactory */
        $validatorFactory = $event->validatorFactory;
        // registered foo validator
        $validatorFactory->extend('foo', function (string $attribute, mixed $value, array $parameters, Validator $validator): bool {
            return $value == 'foo';
        });
        // When creating a custom validation rule, you may sometimes need to define a custom placeholder for error messages. Here is an extension of the :foo placeholder
        $validatorFactory->replacer('foo', function (string $message, string $attribute, string $rule, array $parameters): array|string {
            return str_replace(':foo', $attribute, $message);
        });
    }
}
```

#### Kustomisasi Pesan Error

Anda juga perlu mendefinisikan pesan error untuk aturan kustom. Anda dapat
menggunakan array pesan kustom inline atau menambahkan entri di dalam file
bahasa validasi untuk mencapai fungsionalitas ini. Pesan tersebut harus
ditempatkan di dimensi pertama array, bukan di dalam array custom, karena
array custom hanya digunakan untuk menyimpan informasi error spesifik atribut.
Ambil contoh validator kustom `foo` pada bagian sebelumnya:

Tambahkan konten berikut ke array file `storage/languages/en/validation.php`

```php
    'foo' => 'The :attribute must be foo',
```

Tambahkan konten berikut ke array file `storage/languages/zh_CN/validation.php`

```php
    'foo' => ':attribute must be foo',
```

#### Penggunaan Validator Kustom

```php
<?php

declare(strict_types=1);

namespace App\Request;

use Hyperf\Validation\Request\FormRequest;

class DemoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // use foo validator
            'name' => 'foo'
        ];
    }
}
```
