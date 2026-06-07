# Validator

## Kata Pengantar

> [hyperf/validation](https://github.com/hyperf/validation) berasal dari [illuminate/validation](https://github.com/illuminate/validation). Kami telah melakukan beberapa modifikasi namun tetap mempertahankan aturan validasi yang sama. Di sini, kami ingin berterima kasih kepada tim pengembang Laravel karena telah mengimplementasikan komponen validator yang begitu powerful dan mudah digunakan.

## Instalasi

### Memperkenalkan Package Komponen

```bash
composer require hyperf/validation
```

### Menambahkan Middleware

Anda perlu menambahkan konfigurasi global middleware `Hyperf\Validation\Middleware\ValidationMiddleware` ke file konfigurasi `config/autoload/middlewares.php` untuk Server yang menggunakan komponen validator. Berikut adalah contoh menambahkan middleware global yang sesuai ke Server `http`:

```php
<?php
return [
    // String http di bawah ini sesuai dengan nilai atribut name dari setiap server di config/autoload/server.php, artinya konfigurasi middleware yang sesuai hanya berlaku untuk Server tersebut
    'http' => [
        // Konfigurasikan global middleware Anda dalam array, urutannya tergantung pada urutan array ini
        \Hyperf\Validation\Middleware\ValidationMiddleware::class
        // Middleware lainnya disembunyikan di sini
    ],
];
```

> Jika global middleware tidak diatur dengan benar, penggunaan `FormRequest` mungkin tidak valid.

### Menambahkan Exception Handler

Exception handler terutama menangani exception `Hyperf\Validation\ValidationException`. Kami menyediakan `Hyperf\Validation\ValidationExceptionHandler` untuk menanganinya. Anda perlu mengonfigurasi exception handler ini secara manual di file `config/autoload/exceptions.php` project Anda. Tentu saja, Anda juga dapat menyesuaikan exception handler Anda sendiri.

```php
<?php
return [
    'handler' => [
        // Sesuai dengan nama Server Anda saat ini
        'http' => [
            \Hyperf\Validation\ValidationExceptionHandler::class,
        ],
    ],
];
```

### Menerbitkan File Bahasa Validator

Karena adanya fungsionalitas multi-bahasa, komponen ini bergantung pada komponen [hyperf/translation](https://github.com/hyperf/translation). Jika Anda belum menambahkan file konfigurasi untuk komponen Translation, harap jalankan perintah berikut untuk menerbitkan file konfigurasi untuk komponen Translation. Jika Anda sudah menerbitkan atau menambahkannya secara manual, cukup terbitkan file bahasa untuk komponen validator:

Menerbitkan file komponen Translation:

```bash
php bin/hyperf.php vendor:publish hyperf/translation
```

Menerbitkan file komponen validator:

```bash
php bin/hyperf.php vendor:publish hyperf/validation
```

Menjalankan perintah di atas akan menerbitkan file bahasa validator `validation.php` ke direktori file bahasa yang sesuai, di mana `en` merujuk pada file bahasa Inggris dan `zh_CN` merujuk pada file bahasa Mandarin Sederhana. Anda dapat memodifikasi dan menyesuaikan konten file `validation.php` sesuai dengan kebutuhan Anda.

```shell
/storage
    /languages
        /en
            validation.php
        /zh_CN
            validation.php

```

## Penggunaan

### Form Request Validation

Untuk skenario validasi yang kompleks, Anda dapat membuat `FormRequest`. Form Request adalah kelas request kustom yang berisi logika validasi. Anda dapat membuat kelas validasi form bernama `FooRequest` dengan menjalankan perintah berikut:

```bash
php bin/hyperf.php gen:request FooRequest
```

Kelas validasi form akan dibuat di direktori `app\Request`. Jika direktori tersebut tidak ada, maka akan otomatis dibuat saat menjalankan perintah.
Selanjutnya, kita menambahkan beberapa aturan validasi ke metode `rules` dari kelas tersebut:

```php
/**
 * Mendapatkan aturan validasi yang diterapkan pada request
 */
public function rules(): array
{
    return [
        'foo' => 'required|max:255',
        'bar' => 'required',
    ];
}
```

Jadi, bagaimana aturan validasi mulai berlaku? Yang perlu Anda lakukan adalah mendeklarasikan kelas request sebagai parameter di metode controller menggunakan type hinting. Dengan cara ini, request form yang masuk akan divalidasi sebelum metode controller dipanggil, artinya Anda tidak perlu menulis logika validasi apa pun di controller, yang dengan sangat baik memisahkan kedua bagian kode ini:

```php
<?php
namespace App\Controller;

use App\Request\FooRequest;

class IndexController
{
    public function index(FooRequest $request)
    {
        // Request yang masuk lulus validasi...
        
        // Mendapatkan data yang telah divalidasi...
        $validated = $request->validated();
    }
}
```

Jika validasi gagal, validator akan melempar exception `Hyperf\Validation\ValidationException`. Anda dapat menangani exception ini dengan menambahkan kelas penanganan exception kustom. Pada saat yang sama, kami juga menyediakan exception handler `Hyperf\Validation\ValidationExceptionHandler` untuk menangani exception ini, yang juga dapat Anda konfigurasikan secara langsung. Namun, exception handler default mungkin tidak memenuhi kebutuhan Anda, jadi Anda dapat menyesuaikan perilaku setelah kegagalan validasi dengan menyesuaikan exception handler sesuai kebutuhan.

#### Kustom Pesan Error

Anda dapat menyesuaikan pesan error yang digunakan oleh form request dengan menimpa metode `messages`, yang harus mengembalikan array pasangan atribut/aturan dan pesan error yang sesuai:

```php
/**
 * Mendapatkan pesan error kustom untuk aturan validasi yang ditentukan
 */
public function messages(): array
{
    return [
        'foo.required' => 'foo wajib diisi',
        'bar.required'  => 'bar wajib diisi',
    ];
}
```

#### Kustom Atribut Validasi

Jika Anda ingin mengganti bagian `:attribute` dalam pesan validasi dengan nama atribut kustom, Anda dapat menentukan nama kustom dengan menimpa metode `attributes`. Metode ini mengembalikan array pasangan nama atribut dan nama kustom:

```php
/**
 * Mendapatkan atribut kustom untuk error validasi
 */
public function attributes(): array
{
    return [
        'foo' => 'foo dari request',
    ];
}
```

### Membuat Validator Secara Manual

Jika Anda tidak ingin menggunakan fungsi validasi otomatis dari `FormRequest`, Anda bisa mendapatkan kelas factory validator dengan menginjeksi kelas antarmuka `ValidatorFactoryInterface`, dan kemudian secara manual membuat instance validator menggunakan metode `make`:

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
                'foo.required' => 'foo wajib diisi',
                'bar.required' => 'bar wajib diisi',
            ]
        );

        if ($validator->fails()){
            // Menangani exception
            $errorMessage = $validator->errors()->first();  
        }
        // Lakukan sesuatu
    }
}
```

Argumen pertama yang diteruskan ke metode `make` adalah data yang perlu divalidasi, dan argumen kedua adalah aturan validasi untuk data tersebut.

#### Kustom Pesan Error

Jika perlu, Anda juga dapat menggunakan pesan error kustom untuk menggantikan nilai default untuk validasi. Ada beberapa cara untuk menentukan pesan kustom. Pertama, Anda dapat meneruskan pesan kustom sebagai argumen ketiga ke metode `make`:

```php
<?php
$messages = [
    'required' => 'Field :attribute wajib diisi.',
];

$validator = $this->validationFactory->make($request->all(), $rules, $messages);
```

Dalam contoh ini, placeholder `:attribute` akan digantikan oleh nama sebenarnya dari field validasi. Selain itu, Anda dapat menggunakan placeholder lain dalam pesan validasi. Contoh:

```php
$messages = [
    'same'    => ':attribute dan :other harus cocok.',
    'size'    => ':attribute harus tepat :size.',
    'between' => 'Nilai :attribute :input tidak antara :min - :max.',
    'in'      => ':attribute harus salah satu dari tipe berikut: :values',
];
```

#### Menentukan Pesan Kustom untuk Atribut Tertentu

Terkadang Anda mungkin hanya ingin menyesuaikan pesan error untuk field tertentu. Gunakan `.` setelah nama atribut untuk menentukan aturan validasi:

```php
$messages = [
    'email.required' => 'Kami perlu mengetahui alamat email Anda!',
];
```

#### Menentukan Pesan Kustom di File PHP

Dalam kebanyakan kasus, Anda mungkin menentukan pesan kustom dalam file daripada meneruskannya langsung ke `Validator`. Untuk melakukannya, tempatkan pesan Anda dalam array `custom` di dalam file bahasa `storage/languages/xx/validation.php`.

#### Menentukan Atribut Kustom di File PHP

Jika Anda ingin mengganti bagian `:attribute` dari pesan validasi dengan nama atribut kustom, Anda dapat menentukan nama kustom dalam array `attributes` dari file bahasa `storage/languages/xx/validation.php`:

```php
'attributes' => [
    'email' => 'alamat email',
],
```

### After Validation Hooks

Validator juga memungkinkan Anda untuk menambahkan fungsi callback yang diizinkan setelah validasi berhasil, sehingga Anda dapat melakukan validasi lebih lanjut, atau bahkan menambahkan lebih banyak pesan error ke koleksi pesan. Cukup gunakan metode `after` pada instance validasi:

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
                'foo.required' => 'foo wajib diisi',
                'bar.required' => 'bar wajib diisi',
            ]
        );

        $validator->after(function ($validator) {
            if ($this->somethingElseIsInvalid()) {
                $validator->errors()->add('field', 'Ada yang salah dengan field ini!');
            }
        });
        
        if ($validator->fails()) {
            //
        }
    }
}
```

## Menangani Pesan Error

Dengan memanggil metode `errors` melalui instance `Validator`, instance `Hyperf\Support\MessageBag` dikembalikan, yang memiliki berbagai metode yang nyaman untuk menangani pesan error.

### Melihat Pesan Error Pertama untuk Field Tertentu

Untuk melihat pesan error pertama untuk field tertentu, Anda dapat menggunakan metode `first`:

```php
$errors = $validator->errors();

echo $errors->first('foo');
```

### Melihat Semua Pesan Error untuk Field Tertentu

Jika Anda perlu mendapatkan array dari semua pesan error untuk field yang ditentukan, Anda dapat menggunakan metode `get`:

```php
foreach ($errors->get('foo') as $message) {
    //
}
```

Jika Anda memvalidasi field array dari sebuah form, Anda dapat menggunakan `*` untuk mendapatkan semua pesan error untuk setiap elemen array:

```php
foreach ($errors->get('foo.*') as $message) {
    //
}
```

### Melihat Semua Pesan Error untuk Semua Field

Jika Anda ingin mendapatkan semua pesan error untuk semua field, Anda dapat menggunakan metode `all`:

```php
foreach ($errors->all() as $message) {
    //
}
```

### Menentukan Apakah Field Tertentu Memiliki Pesan Error

Metode `has` dapat digunakan untuk menentukan apakah ada pesan error untuk field yang ditentukan:

```php
if ($errors->has('foo')) {
    //
}
```

### Skenario

Validator menambahkan fungsi skenario, yang memungkinkan kita untuk dengan mudah memodifikasi aturan validasi sesuai kebutuhan.

> Fungsionalitas ini membutuhkan versi komponen ini lebih besar atau sama dengan 2.2.7

Buat `SceneRequest` sebagai berikut:

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
        'tar' => ['username' => 'string|required', 'password'],
    ];

    /**
     * Menentukan apakah pengguna diizinkan untuk membuat request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Mendapatkan aturan validasi yang berlaku untuk request.
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

Ketika kita menggunakannya secara normal, semua aturan validasi akan digunakan, artinya `username` dan `gender` keduanya wajib diisi.

Kita dapat mengatur skenario untuk membuat request ini hanya memvalidasi `username` sebagai wajib.

Jika kita telah mengonfigurasi `Hyperf\Validation\Middleware\ValidationMiddleware` dan menginjeksi `SceneRequest` ke dalam metode,
itu akan menyebabkan parameter input divalidasi langsung di middleware, sehingga nilai skenario tidak akan berlaku. Oleh karena itu, kita perlu mendapatkan `SceneRequest` yang sesuai dari container di dalam metode dan melakukan perpindahan skenario.

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

Tentu saja, kita juga dapat beralih skenario melalui annotation `Scene`

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

    #[Scene(scene:'bar2', argument: 'request')] // Terikat ke $request
    public function bar2(SceneRequest $request)
    {
        return $this->response->success($request->all());
    }

    #[Scene(scene:'bar3', argument: 'request')]
    #[Scene(scene:'bar3', argument: 'req')] // Mendukung banyak parameter
    public function bar3(SceneRequest $request, DebugRequest $req)
    {
        return $this->response->success($request->all());
    }

    #[Scene()] // Skenario default adalah nama metode, setara dengan #[Scene(scene: 'bar1')]
    public function bar1(SceneRequest $request)
    {
        return $this->response->success($request->all());
    }
}
```

## Aturan Validasi

Berikut adalah daftar aturan validasi dan fungsinya:

##### accepted

Field yang divalidasi harus `yes`, `on`, `1`, atau `true`, yang berguna saat "menyetujui perjanjian layanan".

##### accepted_if:anotherfield,value,…
Jika field lain yang divalidasi sama dengan nilai yang ditentukan, field yang divalidasi harus `yes`, `on`, `1`, atau `true`, yang berguna untuk memvalidasi penerimaan "Persyaratan Layanan" atau field serupa.

##### declined
Field yang divalidasi harus `no`, `off`, `0`, atau `false`.

##### declined_if:anotherfield,value,…
Jika nilai field validasi lain sama dengan nilai yang ditentukan, nilai field validasi harus `no`, `off`, `0`, atau `false`.

##### active_url

Field yang divalidasi harus berupa nilai yang valid dengan record `A` atau `AAAA` berdasarkan fungsi PHP `dns_get_record`.

##### after:date

Field yang divalidasi harus berupa nilai setelah tanggal yang diberikan. Tanggal akan diproses melalui fungsi PHP strtotime:

```php
'start_date' => 'required|date|after:tomorrow'
```

Anda dapat menentukan field lain untuk dibandingkan dengan tanggal alih-alih melewatkan string tanggal ke strtotime untuk dieksekusi:

```php
'finish_date' => 'required|date|after:start_date'
```

##### after_or_equal:date

Field yang divalidasi harus berupa nilai yang lebih besar dari atau sama dengan tanggal yang diberikan. Untuk informasi lebih lanjut, silakan merujuk ke aturan after:date.

##### alpha

Field yang divalidasi harus berupa huruf (termasuk huruf Cina). Untuk membatasi aturan validasi ini ke karakter dalam rentang ASCII (a-z dan A-Z), Anda dapat memberikan opsi ascii ke aturan validasi:

```php
'username' => 'alpha:ascii',
```

##### alpha_dash

Field yang divalidasi dapat berisi huruf (termasuk huruf Cina) dan angka, serta garis putus-putus dan garis bawah. Untuk membatasi aturan validasi ini ke karakter dalam rentang ASCII (a-z dan A-Z), Anda dapat memberikan opsi ascii ke aturan validasi:

```php
'username' => 'alpha_dash:ascii',
```

##### alpha_num

Field yang divalidasi harus berupa huruf (termasuk huruf Cina) atau angka. Untuk membatasi aturan validasi ini ke karakter dalam rentang ASCII (a-z dan A-Z), Anda dapat memberikan opsi ascii ke aturan validasi:

```php
'username' => 'alpha_num:ascii',
```

#### ascii

Field yang divalidasi harus seluruhnya terdiri dari karakter ASCII 7-bit.

##### array

Field yang divalidasi harus berupa array PHP.

##### required_array_keys:foo,bar,…

Field yang divalidasi harus berupa array dan harus mengandung setidaknya kunci yang ditentukan.

##### bail

Berhenti menjalankan aturan validasi lainnya jika aturan validasi pertama gagal.

##### before:date

Relatif terhadap after:date, field yang divalidasi harus berupa nilai sebelum tanggal yang ditentukan. Tanggal akan diteruskan ke fungsi PHP strtotime.

##### before_or_equal:date

Field yang divalidasi harus kurang dari atau sama dengan tanggal yang diberikan. Tanggal akan diteruskan ke fungsi PHP strtotime.

##### between:min,max

Ukuran field yang divalidasi harus berada di antara nilai minimum dan maksimum yang diberikan. String, angka, array, dan file semuanya dapat menggunakan aturan ini seperti menggunakan aturan size:

'name' => 'required|between:1,20'

##### boolean

Field yang divalidasi harus dapat dikonversi ke nilai boolean, menerima input seperti true, false, 1, 0, "1", dan "0".

##### boolean:strict

Field yang divalidasi harus dapat dikonversi ke nilai boolean, hanya menerima true dan false.

##### confirmed

Field yang divalidasi harus memiliki field yang cocok bernama foo_confirmation. Misalnya, jika field validasi adalah password, Anda harus memasukkan field password_confirmation yang cocok.

##### date

Field yang divalidasi harus berupa tanggal yang valid berdasarkan fungsi PHP strtotime

##### date_equals:date

Field yang divalidasi harus sama dengan tanggal yang diberikan. Tanggal akan diteruskan ke fungsi PHP strtotime.

##### date_format:format

Field yang divalidasi harus cocok dengan format yang ditentukan. Anda dapat menggunakan fungsi PHP date atau date_format untuk memvalidasi field.

##### decimal:min,max

Field yang divalidasi harus bertipe numerik dan harus mengandung jumlah tempat desimal yang ditentukan:

```php
// Harus memiliki tepat dua tempat desimal (misalnya, 9.99)...
'price' => 'decimal:2'

// Harus memiliki 2 hingga 4 tempat desimal...
'price' => 'decimal:2,4'
```

##### lowercase

Field yang divalidasi harus huruf kecil.

##### uppercase

Field yang divalidasi harus huruf besar.

##### mac_address

Field yang divalidasi harus berupa alamat MAC.

##### max_digits:value

Integer yang divalidasi harus memiliki panjang maksimum value.

##### min_digits:value

Integer yang divalidasi harus memiliki setidaknya value digit.

##### exclude

Field yang divalidasi saat ini akan dikecualikan dari metode `validate` dan `validated`.

##### exclude_if:anotherfield,value
Jika `anotherfield` sama dengan `value`, field yang divalidasi saat ini akan dikecualikan dari metode `validate` dan `validated`.

Dalam beberapa skenario kompleks, Anda juga dapat menggunakan metode `Rule::excludeIf`, yang perlu mengembalikan nilai boolean atau fungsi anonim. Jika fungsi anonim dikembalikan, ia harus mengembalikan `true` atau `false` untuk memutuskan apakah field yang divalidasi harus dikecualikan:

```php
use Hyperf\Validation\Rule;

$this->validationFactory->make($request->all(), [
    'role_id' => Rule::excludeIf($request->user()->is_admin),
]);

$this->validationFactory->make($request->all(), [
    'role_id' => Rule::excludeIf(fn () => $request->user()->is_admin),
]);
```

##### prohibited

Field yang memerlukan validasi tidak boleh ada atau harus kosong. Jika memenuhi salah satu kondisi berikut, field dianggap "kosong":

1. Nilai adalah `null`.
2. Nilai adalah string kosong.
3. Nilai adalah array kosong atau objek Countable kosong.
4. Nilai adalah file yang diunggah, tetapi path file kosong.

##### prohibited_if:anotherfield,value,…

Jika field `anotherfield` sama dengan `value` mana pun, field yang memerlukan validasi tidak boleh ada atau harus kosong. Jika memenuhi salah satu kondisi berikut, field dianggap "kosong":

1. Nilai adalah `null`.
2. Nilai adalah string kosong.
3. Nilai adalah array kosong atau objek Countable kosong.
4. Nilai adalah file yang diunggah, tetapi path file kosong.

Jika diperlukan logika prohibited bersyarat yang kompleks, metode `Rule::prohibitedIf` dapat digunakan. Metode ini menerima nilai boolean atau closure. Ketika diberikan closure, closure harus mengembalikan `true` atau `false` untuk menunjukkan apakah field validasi harus dilarang:


```php
use Hyperf\Validation\Rule;

$this->validationFactory->make($request->all(), [
    'role_id' => Rule::prohibitedIf($request->user()->is_admin),
]);

$this->validationFactory->make($request->all(), [
    'role_id' => Rule::prohibitedIf(fn () => $request->user()->is_admin),
]);
```


##### missing

Field yang divalidasi tidak boleh ada dalam data input.

##### missing_if:anotherfield,value,…

Jika field `anotherfield` sama dengan `value` mana pun, maka field yang divalidasi tidak boleh ada.

##### missing_unless:anotherfield,value

Field yang divalidasi tidak boleh ada kecuali field `anotherfield` sama dengan `value` mana pun.

##### missing_with:foo,bar,…

Field yang divalidasi tidak boleh ada jika field lain yang ditentukan ada.

##### missing_with_all:foo,bar,…

Field yang divalidasi tidak boleh ada jika semua field lain yang ditentukan ada.

##### multiple_of:value

Field yang divalidasi harus merupakan kelipatan dari `value`.

##### doesnt_start_with:foo,bar,…

Field yang divalidasi tidak boleh diawali dengan salah satu nilai yang diberikan.

##### doesnt_end_with:foo,bar,…

Field yang divalidasi tidak boleh diakhiri dengan salah satu nilai yang diberikan.

##### different:field

Field yang divalidasi harus berupa nilai yang berbeda dari field yang ditentukan.

##### digits:value

Field yang divalidasi harus berupa angka dan memiliki panjang yang ditentukan oleh `value`.

##### digits_between:min,max

Panjang numerik dari field yang divalidasi harus berada di antara nilai minimum dan maksimum.

##### dimensions

Dimensi dari gambar yang divalidasi harus memenuhi batasan yang ditentukan oleh parameter aturan ini:

```php
'avatar' => 'dimensions:min_width=100,min_height=200'
```

Batasan yang valid meliputi: `min_width`, `max_width`, `min_height`, `max_height`, `width`, `height`, `ratio`.

Batasan `ratio` membatasi rasio lebar/tinggi, yang dapat dinyatakan dengan ekspresi seperti `3/2` atau angka floating-point `1.5`:

```php
'avatar' => 'dimensions:ratio=3/2'
```

Karena aturan ini memerlukan beberapa parameter, Anda dapat menggunakan metode `Rule::dimensions` untuk membangun aturan ini:

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

Saat memproses array, field yang divalidasi tidak boleh mengandung nilai duplikat:

```php
'foo.*.id' => 'distinct'
```

##### email

Field yang divalidasi harus berupa alamat email yang diformat dengan benar.

##### exists:table,column

Field yang divalidasi harus ada di tabel data yang ditentukan.

Penggunaan dasar:

```php
'state' => 'exists:states'
```

Jika opsi `column` tidak ditentukan, nama field akan digunakan.

Tentukan nama kolom kustom:

```php
'state' => 'exists:states,abbreviation'
```

Terkadang, Anda mungkin perlu menentukan koneksi database yang akan digunakan untuk query `exists`, yang dapat dicapai dengan menambahkan awalan nama tabel dengan koneksi database diikuti oleh ".", atau dengan menentukan nama kelas model untuk resolusi otomatis:

```php
// Pendekatan awalan koneksi database
'email' => 'exists:connection.staff,email'

// Pendekatan resolusi otomatis nama kelas model
'email' => 'exists:StaffModel::class,email'
```

Jika Anda ingin menyesuaikan query yang dijalankan oleh aturan validasi, Anda dapat menggunakan kelas `Rule` untuk mendefinisikan aturan. Dalam contoh ini, kami juga menentukan aturan validasi dalam bentuk array alih-alih menggunakan karakter `|` untuk memisahkannya:

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

Field yang divalidasi tidak boleh kosong jika ada.

##### gt:field

Field yang divalidasi harus lebih besar dari field `field` yang diberikan. Kedua field ini harus bertipe sama dan berlaku untuk string, angka, array, dan file, mirip dengan aturan `size`.

##### gte:field

Field yang divalidasi harus lebih besar dari atau sama dengan field `field` yang diberikan. Kedua field ini harus bertipe sama dan berlaku untuk string, angka, array, dan file, mirip dengan aturan `size`.

##### image

File yang divalidasi harus berupa gambar (`jpeg`, `png`, `bmp`, `gif`, atau `svg`).

##### in:foo,bar…

Field yang divalidasi harus berada dalam daftar yang diberikan. Karena aturan ini sering mengharuskan kita untuk `implode` array, kita dapat menggunakan `Rule::in` untuk membangun aturan ini:

```php
use Hyperf\Validation\Rule;

$validator = $this->validationFactory->make($data, [
    'zones' => [
        'required',
        Rule::in(['first-zone', 'second-zone']),
    ],
]);
```

##### in_array:anotherfield

Field yang divalidasi harus ada dalam nilai field lain.

##### integer

Field yang divalidasi harus berupa integer (baik tipe String maupun Integer dapat lolos validasi).

##### integer:strict

Field yang divalidasi harus berupa integer (hanya tipe Integer yang dapat lolos validasi).

##### ip

Field yang divalidasi harus berupa alamat IP.

##### ipv4

Field yang divalidasi harus berupa alamat IPv4.

##### ipv6

Field yang divalidasi harus berupa alamat IPv6.

##### json

Field yang divalidasi harus berupa string JSON yang valid.

##### lt:field

Field yang divalidasi harus lebih kecil dari field `field` yang diberikan. Kedua field ini harus bertipe sama dan berlaku untuk string, angka, array, dan file, mirip dengan aturan `size`.

##### lte:field

Field yang divalidasi harus lebih kecil dari atau sama dengan field `field` yang diberikan. Kedua field ini harus bertipe sama dan berlaku untuk string, angka, array, dan file, mirip dengan aturan `size`.

##### max:value

Field yang divalidasi harus kurang dari atau sama dengan nilai maksimum, dan penggunaannya sama dengan aturan `size` untuk field string, numerik, array, dan file.

##### mimetypes：text/plain…

File yang divalidasi harus cocok dengan salah satu tipe file `MIME` yang diberikan:

```php
'video' => 'mimetypes:video/avi,video/mpeg,video/quicktime'
```

Untuk menentukan tipe `MIME` dari file yang diunggah, komponen akan membaca konten file untuk menebak tipe `MIME`, yang mungkin berbeda dari tipe `MIME` sisi klien.

##### mimes:foo,bar,…

Tipe `MIME` dari file yang divalidasi harus salah satu dari tipe ekstensi yang terdaftar oleh aturan ini.
Penggunaan dasar aturan `MIME`:

```php
'photo' => 'mimes:jpeg,bmp,png'
```

Meskipun Anda hanya menentukan ekstensi, aturan ini sebenarnya memvalidasi tipe `MIME` file yang diperoleh dengan membaca konten file.
Daftar lengkap tipe `MIME` dan ekstensi yang sesuai dapat ditemukan di sini: [mime types](http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types)

##### min:value

Relatif terhadap `max:value`, field yang divalidasi harus lebih besar dari atau sama dengan nilai minimum. Untuk field string, numerik, array, dan file, penggunaannya konsisten dengan aturan `size`.

##### not_in:foo,bar,…

Field yang divalidasi tidak boleh berada dalam daftar yang diberikan. Mirip dengan aturan `in`, kita dapat menggunakan metode `Rule::notIn` untuk membangun aturan:

```php
use Hyperf\Validation\Rule;

$validator = $this->validationFactory->make($data, [
    'toppings' => [
        'required',
        Rule::notIn(['sprinkles', 'cherries']),
    ],
]);
```

##### not_regex:pattern

Field yang divalidasi tidak boleh cocok dengan ekspresi reguler yang diberikan.

Catatan: Saat menggunakan pola `regex/not_regex`, aturan harus ditempatkan dalam array dan tidak dapat menggunakan pemisah pipe, terutama ketika ekspresi reguler mengandung simbol pipe.

##### nullable

Field yang divalidasi dapat berupa `null`, yang berguna saat memvalidasi beberapa data mentah yang dapat berupa `null`, seperti integer atau string.

##### numeric

Field yang divalidasi harus numerik.

##### present

Field yang divalidasi harus muncul dalam data input tetapi dapat kosong.

##### regex:pattern

Field yang divalidasi harus cocok dengan ekspresi reguler yang diberikan.
Aturan ini menggunakan fungsi `PHP` `preg_match` di bawah tenda. Oleh karena itu, pola yang ditentukan harus mengikuti format yang diperlukan oleh fungsi `preg_match` dan mengandung delimiter yang valid. Contoh:

```php
 'email' => 'regex:/^.+@.+$/i'
```

Catatan: Saat menggunakan pola `regex/not_regex`, aturan harus ditempatkan dalam array dan tidak dapat menggunakan pemisah pipe, terutama ketika ekspresi reguler mengandung simbol pipe.

##### required

Field yang divalidasi tidak boleh kosong. Dalam kasus berikut, nilai field kosong:
- Nilai adalah `null`
- Nilai adalah string kosong
- Nilai adalah array kosong atau objek `Countable` kosong
- Nilai adalah file yang diunggah tetapi path kosong

##### required_if:anotherfield,value,…

Field yang divalidasi harus ada dan tidak boleh kosong ketika `anotherfield` sama dengan nilai `value` yang ditentukan.
Jika Anda ingin membangun kondisi yang lebih kompleks untuk aturan `required_if`, Anda dapat menggunakan metode `Rule::requiredIf`, yang menerima nilai boolean atau closure. Saat melewatkan closure, ia mengembalikan `true` atau `false` untuk menunjukkan apakah field yang divalidasi wajib diisi:

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

##### required_unless:anotherfield,value,…

Kecuali field `anotherfield` sama dengan `value`, field yang divalidasi tidak boleh kosong.

##### required_with:foo,bar,…

Field yang divalidasi wajib diisi hanya jika salah satu field lain yang ditentukan ada.

##### required_with_all:foo,bar,…

Field yang divalidasi wajib diisi hanya jika semua field yang ditentukan ada.

##### required_without:foo,bar,…

Field yang divalidasi wajib diisi hanya jika salah satu field yang ditentukan tidak ada.

##### required_without_all:foo,bar,…

Field yang divalidasi wajib diisi hanya jika semua field yang ditentukan tidak ada.

##### same:field

Field yang diberikan dan field yang divalidasi harus cocok.

##### size:value

Field yang divalidasi harus memiliki ukuran yang cocok dengan nilai `value` yang diberikan. Untuk string, `value` adalah jumlah karakter yang sesuai; untuk angka, `value` adalah nilai integer yang diberikan; untuk array, `value` adalah panjang array; untuk file, `value` adalah ukuran file yang sesuai dalam kilobyte (KB).

##### starts_with:foo,bar,...

Field yang divalidasi harus diawali dengan nilai yang diberikan.

##### string

Field yang divalidasi harus berupa string. Jika field diizinkan untuk kosong, Anda perlu menetapkan aturan `nullable` ke field tersebut.

##### timezone

Field yang divalidasi harus berupa pengidentifikasi zona waktu yang valid berdasarkan fungsi `PHP` `timezone_identifiers_list`.

##### unique:table,column,except,idColumn

Field yang divalidasi harus unik di tabel data yang diberikan. Jika opsi `column` tidak ditentukan, nama field akan digunakan sebagai `column` default.

1. Tentukan nama kolom kustom:

```php
'email' => 'unique:users,email_address'
```

2. Koneksi database kustom:
   Terkadang, Anda mungkin perlu menyesuaikan koneksi database yang dihasilkan oleh validator, seperti yang terlihat di atas, mengatur `unique:users` sebagai aturan validasi akan menggunakan koneksi database default untuk melakukan query ke database. Untuk mengganti koneksi default, tentukan koneksi setelah nama tabel dengan ".", atau selesaikan secara otomatis dengan menentukan nama kelas model:

```php
// Pendekatan awalan koneksi database
'email' => 'unique:connection.users,email_address'

// Pendekatan resolusi otomatis nama kelas model
'email' => 'unique:UserModel::class,email_address'
```

3. Memaksa aturan unique untuk mengabaikan `ID` tertentu:
   Terkadang, Anda mungkin ingin mengabaikan `ID` tertentu saat memeriksa keunikan. Misalnya, pertimbangkan antarmuka "Perbarui Atribut" yang menyertakan username, alamat email, dan lokasi. Anda ingin memvalidasi bahwa alamat email tersebut unik. Namun, jika pengguna hanya mengubah field username dan tidak mengubah field email, Anda tidak ingin melempar error validasi karena pengguna sudah memiliki alamat email tersebut. Anda hanya ingin melempar error validasi jika email yang diberikan oleh pengguna telah digunakan oleh orang lain.

   Untuk memberi tahu validator agar mengabaikan `ID` pengguna, Anda dapat menggunakan kelas `Rule` untuk mendefinisikan aturan ini. Kami juga harus menentukan aturan validasi dalam bentuk array, alih-alih menggunakan `|` untuk memisahkan aturan:

```php
use Hyperf\Validation\Rule;

$validator = $this->validationFactory->make($data, [
    'email' => [
        'required',
        Rule::unique('users')->ignore($user->id),
    ],
]);
```

Selain melewatkan nilai primary key instance model ke metode `ignore`, Anda juga dapat melewatkan seluruh instance model. Komponen akan secara otomatis menyelesaikan nilai primary key dari instance model:

```php
Rule::unique('users')->ignore($user)
```

Jika field primary key yang digunakan oleh tabel data Anda bukan `id`, Anda dapat menentukan nama field saat memanggil metode `ignore`:

```php
'email' => Rule::unique('users')->ignore($user->id, 'user_id')
```

Secara default, aturan `unique` memeriksa keunikan di kolom yang cocok dengan nama atribut yang akan divalidasi. Namun, Anda dapat menentukan nama kolom yang berbeda sebagai argumen kedua dari metode `unique`:

```php
Rule::unique('users', 'email_address')->ignore($user->id),
```

4. Menambahkan klausa `where` tambahan:

Saat menggunakan metode `where` untuk menyesuaikan query, Anda juga dapat menentukan batasan query tambahan. Misalnya, kami menambahkan batasan untuk memvalidasi bahwa `account_id` adalah 1:

```php
'email' => Rule::unique('users')->where(function ($query) {
    $query->where('account_id', 1);
})
```

##### url

Field yang divalidasi harus berupa URL yang valid.

##### uuid

Field yang divalidasi harus berupa pengidentifikasi unik global (UUID) RFC 4122 (versi 1, 3, 4, atau 5) yang valid.

##### sometimes

Menambahkan aturan bersyarat
Validasi ketika field ada

Dalam beberapa skenario, Anda mungkin ingin melakukan pemeriksaan validasi hanya jika field tertentu ada. Untuk mengimplementasikannya dengan cepat, tambahkan aturan `sometimes` ke daftar aturan:

```php
$validator = $this->validationFactory->make($data, [
    'email' => 'sometimes|required|email',
]);
```

Dalam contoh di atas, field `email` hanya akan divalidasi jika ada dalam array `$data`.

Catatan: Jika Anda mencoba memvalidasi field yang selalu ada tetapi mungkin kosong, lihat catatan field opsional.

Validasi bersyarat yang kompleks

Terkadang Anda mungkin ingin menambahkan aturan validasi berdasarkan logika bersyarat yang lebih kompleks. Misalnya, Anda mungkin ingin suatu field dijadikan wajib hanya jika nilai field lain lebih besar dari 100, atau Anda mungkin memerlukan kedua field untuk memiliki nilai yang diberikan hanya ketika field lain ada. Menambahkan aturan validasi ini tidak sulit. Pertama, buat aturan statis yang tidak akan pernah berubah di instance `Validator`:

```php
$validator = $this->validationFactory->make($data, [
    'email' => 'required|email',
    'games' => 'required|numeric',
]);
```

Mari kita asumsikan aplikasi Web kita melayani kolektor game. Jika seorang kolektor game mendaftar untuk aplikasi kita dan memiliki lebih dari 100 game, kita ingin mereka menjelaskan mengapa mereka memiliki begitu banyak game, misalnya, mungkin mereka menjalankan toko game bekas, atau mungkin mereka hanya suka mengoleksi. Untuk menambahkan kondisi ini, kita dapat menggunakan metode `sometimes` pada instance `Validator`:

```php
$v->sometimes('reason', 'required|max:500', function($input) {
    return $input->games >= 100;
});
```

Argumen pertama yang diteruskan ke metode `sometimes` adalah field nama yang kita perlukan validasi bersyarat, argumen kedua adalah aturan yang ingin kita tambahkan, dan aturan ditambahkan jika closure yang diteruskan sebagai argumen ketiga mengembalikan `true`. Metode ini membuat pembuatan validasi bersyarat yang kompleks menjadi sederhana, dan Anda bahkan dapat menambahkan validasi bersyarat untuk beberapa field sekaligus:

```php
$v->sometimes(['reason', 'cost'], 'required', function($input) {
    return $input->games >= 100;
});
```

Catatan: Parameter `$input` yang diteruskan ke closure adalah instance dari `Hyperf\Support\Fluent`, yang dapat digunakan untuk mengakses input dan file.

### Memvalidasi Input Array

Memvalidasi field input array form bukan lagi hal yang menyakitkan. Misalnya, jika request HTTP yang masuk berisi field `photos[profile]`, Anda dapat memvalidasinya seperti ini:

```php
$validator = $this->validationFactory->make($request->all(), [
    'photos.profile' => 'required|image',
]);
```

Kami juga dapat memvalidasi setiap elemen dari sebuah array. Misalnya, untuk memvalidasi apakah setiap email dalam input array yang diberikan bersifat unik, Anda dapat melakukan ini (ini untuk kasus di mana field array yang disubmit adalah array dua dimensi, seperti `person[][email]` atau `person[test][email]`):

```php
$validator = $this->validationFactory->make($request->all(), [
    'person.*.email' => 'email|unique:users',
    'person.*.first_name' => 'required_with:person.*.last_name',
]);
```

Demikian pula, Anda juga dapat menggunakan karakter `*` dalam file bahasa untuk menentukan pesan validasi, sehingga memungkinkan Anda menggunakan satu definisi pesan validasi untuk aturan validasi berdasarkan field array:

```php
'custom' => [
    'person.*.email' => [
        'unique' => 'Alamat email setiap orang harus unik',
    ]
],
```

### Aturan Validasi Kustom

#### Mendaftarkan Aturan Validasi Kustom

Komponen `Validation` menggunakan mekanisme event untuk mengimplementasikan aturan validasi kustom. Kami telah mendefinisikan event `ValidatorFactoryResolved`. Yang perlu Anda lakukan adalah mendefinisikan listener untuk `ValidatorFactoryResolved` dan mengimplementasikan pendaftaran validator di listener. Contohnya adalah sebagai berikut:

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
        /**  @var ValidatorFactoryInterface $validatorFactory */
        $validatorFactory = $event->validatorFactory;
        // Mendaftarkan validator foo
        $validatorFactory->extend('foo', function (string $attribute, mixed $value, array $parameters, Validator $validator): bool {
            return $value == 'foo';
        });
        // Saat membuat aturan validasi kustom, Anda mungkin terkadang perlu mendefinisikan placeholder kustom untuk pesan error, di sini placeholder :foo diperluas
        $validatorFactory->replacer('foo', function (string $message, string $attribute, string $rule, array $parameters): array|string {
            return str_replace(':foo', $attribute, $message);
        });
    }
}
```

#### Pesan Error Kustom

Anda juga perlu mendefinisikan pesan error untuk aturan kustom Anda. Anda dapat mencapainya dengan menggunakan array pesan kustom inline atau dengan menambahkan entri di file bahasa validasi. Pesan harus ditempatkan di dimensi pertama array, bukan di dalam array kustom yang hanya digunakan untuk menyimpan pesan error khusus atribut. Mengambil validator kustom `foo` dari bagian sebelumnya sebagai contoh:

Tambahkan konten berikut ke dalam array di `storage/languages/en/validation.php`:

```php
    'foo' => ':attribute harus foo',
```

Tambahkan konten berikut ke dalam array di `storage/languages/zh_CN/validation.php`:

```php    
    'foo' => ':attribute harus foo',
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
     * Menentukan apakah pengguna diizinkan untuk membuat request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Mendapatkan aturan validasi yang berlaku untuk request.
     */
    public function rules(): array
    {
        return [
            // Gunakan validator foo
            'name' => 'foo'
        ];
    }
}
```
