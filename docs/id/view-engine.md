# View Engine

> Ditulis ulang berdasarkan laravel blade template engine, mendukung sintaks dari blade template engine asli.

```bash
composer require hyperf/view-engine
```

## Menghasikan Konfigurasi

```bash
php bin/hyperf.php vendor:publish hyperf/view-engine
```

Konfigurasi default adalah sebagai berikut:

> Komponen ini merekomendasikan penggunaan mode rendering SYNC, yang secara
> efektif dapat mengurangi hilangnya komunikasi antar-proses (inter-process
> communication).

```php
return [
    'engine' => Hyperf\ViewEngine\HyperfViewEngine::class,
    'mode' => Hyperf\View\Mode::SYNC,
    'config' => [
        'view_path' => BASE_PATH.'/storage/view/',
        'cache_path' => BASE_PATH.'/runtime/view/',
    ],

    # Custom component registration
    'components' => [
        //'alert' => \App\View\Components\Alert::class
    ],

    # View namespace (mainly used in extension packages)
    'namespaces' => [
        //'admin' => BASE_PATH.'/storage/view/vendor/admin',
    ],
];
```

## Penggunaan

> Tutorial ini sangat banyak meminjam dari [LearnKu](https://learnku.com), dan
> saya sangat berterima kasih kepada LearnKu atas kontribusinya kepada komunitas
> PHP.

### Pendahuluan

`Blade` adalah template engine yang sederhana namun kuat yang disediakan oleh
`Laravel`. Berbeda dengan template engine `PHP` populer lainnya, `Blade` tidak
membatasi Anda untuk menggunakan kode `PHP` asli di dalam view. Semua file view
`Blade` akan dikompilasi menjadi kode `PHP` asli dan di-cache, kecuali jika
diubah, jika tidak maka tidak akan dikompilasi ulang, yang berarti `Blade`
pada dasarnya tidak menambah beban apa pun pada aplikasi Anda.

File view `Blade` menggunakan ekstensi file `.blade.php` dan secara default
disimpan di direktori `storage/view`.

### Template Inheritance

#### Mendefinisikan Layout

Pertama, mari kita pelajari layout halaman "utama". Karena sebagian besar
aplikasi `web` akan menggunakan layout yang sama pada halaman yang berbeda,
sangat mudah untuk mendefinisikan satu view layout `Blade`:

```blade
<!-- Stored in storage/view/layouts/app.blade.php -->

<html>
    <head>
        <title>App Name-@yield('title')</title>
    </head>
    <body>
        @section('sidebar')
            This is the master sidebar.
        @show

        <div class="container">
            @yield('content')
        </div>
    </body>
</html>
```

Seperti yang Anda lihat, program ini berisi `HTML` biasa. Namun harap
perhatikan petunjuk `@section` dan `@yield`. Sama seperti arti dari `section`
(bagian), direktif `@section` mendefinisikan konten dari bagian tersebut,
sedangkan direktif `@yield` digunakan untuk menampilkan konten dari bagian
tersebut.

Sekarang kita telah mendefinisikan layout dari aplikasi ini, selanjutnya mari
kita definisikan subhalaman yang mewarisi layout ini.

#### Pewarisan Layout

Saat mendefinisikan subview, gunakan direktif `@extends` dari `Blade` untuk
menentukan view yang harus "diwarisi" oleh subview tersebut. View yang mewarisi
layout `Blade` dapat menggunakan direktif `@section` untuk menyuntikkan konten
ke dalam bagian layout. Seperti yang ditunjukkan pada contoh sebelumnya, konten
dari fragmen-fragmen ini akan dikontrol dan ditampilkan oleh direktif `@yield`
di dalam layout:

```blade
<!-- Stored in storage/view/child.blade.php -->

@extends('layouts.app')

@section('title','Page Title')

@section('sidebar')
    @parent

    <p>This is appended to the master sidebar.</p>
@endsection

@section('content')
    <p>This is my body content.</p>
@endsection
```

Dalam contoh ini, fragmen `sidebar` menggunakan direktif `@parent` untuk
menambahkan (bukan menimpa) konten ke `sidebar` layout. Saat merender view,
direktif `@parent` akan digantikan oleh konten yang ada di dalam layout.

> Berlawanan dengan contoh sebelumnya, fragmen sidebar di sini diakhiri dengan
> @endsection alih-alih @show. Direktif @endsection hanya mendefinisikan satu
> bagian, sedangkan @show segera menampilkan bagian ini saat mendefinisikannya.

Perintah `@yield` juga menerima nilai default sebagai parameter kedua. Jika
fragmen "yield" tidak ditentukan, nilai default akan dirender:

```blade
@yield('content','Hyperf')
```

View `Blade` dapat dikembalikan oleh helper function `Hyperf\ViewEngine\view`:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use function Hyperf\ViewEngine\view;

#[AutoController(prefix: "view")]
class ViewController extends AbstractController
{
    public function child()
    {
        return (string) view('child');
    }
}

```

### Menampilkan Data

Anda dapat menempatkan variabel dalam tanda kurung kurawal untuk menampilkan data
di dalam view. Sebagai contoh, dengan route berikut:

```php
use Hyperf\HttpServer\Router\Router;
use function Hyperf\ViewEngine\view;

Router::get('greeting', function () {
    return view('welcome', ['name' =>'Samantha']);
});
```

Anda dapat menampilkan konten dari variabel `name` sebagai berikut:

```blade
Hello, {{ $name }}.
```

> Pernyataan {{ }} pada Blade akan secara otomatis di-escape oleh fungsi
> `htmlspecialchars` PHP untuk mencegah serangan XSS.

Tidak hanya dapat menampilkan konten variabel yang dilewatkan ke view, Anda juga
dapat menampilkan hasil dari fungsi `PHP` apa pun. Sebenarnya, Anda dapat
memasukkan kode PHP apa pun di dalam pernyataan echo pada template Blade:

```blade
The current UNIX timestamp is {{ time() }}.
```

#### Menampilkan Karakter Non-Escaped

Secara default, pernyataan `Blade {{ }}` akan secara otomatis di-escape oleh
fungsi `htmlspecialchars` `PHP` untuk mencegah serangan `XSS`. Jika Anda tidak
ingin data Anda di-escape, Anda dapat menggunakan sintaks berikut:

```blade
Hello, {!! $name !!}.
```

> Harap berhati-hati saat menampilkan data yang disediakan pengguna di dalam
> aplikasi. Gunakan escaping dan sintaks tanda kurung kurawal ganda sebanyak
> mungkin untuk mencegah serangan XSS.

#### Merender JSON

Terkadang, untuk menginisialisasi variabel `JavaScript`, Anda mungkin melewatkan
sebuah array ke view dan merendernya sebagai `JSON`. Contoh:

```
<script>
    var app = <?php echo json_encode($array); ?>;
</script>
```

Tentu saja, Anda juga dapat menggunakan perintah `@json` `Blade` alih-alih
memanggil metode `json_encode` secara manual. Parameter dari instruksi `@json`
sama dengan fungsi `json_encode` milik `PHP`:

```html
<script>
    var app = @json($array);

    var app = @json($array, JSON_PRETTY_PRINT);
</script>
```

> Saat menggunakan direktif @json, Anda sebaiknya hanya merender variabel yang
> sudah ada sebagai JSON. Template Blade didasarkan pada regular expression.
> Mencoba melewatkan ekspresi yang kompleks ke direktif @json dapat menyebabkan
> error yang tidak terduga.

#### Pengodean Entitas HTML

Secara default, `Blade` akan melakukan pengodean ganda (double-encode) pada
entitas `HTML`. Jika Anda ingin menonaktifkannya, Anda dapat mendengarkan event
`BootApplication` dan memanggil metode `Blade::withoutDoubleEncoding`:

```php
<?php

declare(strict_types=1);

namespace App\Listener;

use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\ViewEngine\Blade;
use Psr\Container\ContainerInterface;

#[Listener]
class BladeWithoutDoubleEncodingListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function listen(): array
    {
        return [
            BootApplication::class
        ];
    }

    public function process(object $event)
    {
        Blade::withoutDoubleEncoding();
    }
}

```

#### Blade & Framework JavaScript

Karena banyak framework JavaScript juga menggunakan "kurung kurawal" untuk
mengidentifikasi ekspresi yang akan ditampilkan di browser, Anda dapat
menggunakan simbol `@` untuk menunjukkan bahwa rendering engine Blade tidak
perlu memprosesnya. Contoh:

```blade
<h1>Laravel</h1>

Hello, @{{ name }}.
```

Dalam contoh ini, simbol `@` akan dihapus oleh `Blade`; tentu saja, `Blade`
tidak akan mengubah ekspresi `{{ name }}`, melainkan membiarkan template
`JavaScript` untuk merendernya.

Simbol `@` juga digunakan untuk meng-escape instruksi `Blade`:

```
{{-- Blade --}}
@@json()

<!-- HTML output -->
@json()
```

Jika Anda menampilkan sebagian besar variabel `JavaScript` di dalam template,
Anda dapat menyematkan `HTML` di dalam direktif `@verbatim`, sehingga Anda tidak
perlu menambahkan simbol `@` sebelum setiap pernyataan echo `Blade`:

```blade
@verbatim
    <div class="container">
        Hello, {{ name }}.
    </div>
@endverbatim
```

### Kontrol Alur (Process Control)

Selain pewarisan template dan menampilkan data, `Blade` juga menyediakan
shortcut yang nyaman untuk struktur kontrol `PHP` yang umum, seperti pernyataan
kondisional dan perulangan (loop). Shortcut ini memberikan cara penulisan
struktur kontrol `PHP` yang sangat jelas dan ringkas. Pada saat yang sama, ini
juga mempertahankan karakteristik sintaksis yang mirip dengan struktur kontrol
di dalam `PHP`.

#### Pernyataan If

Anda dapat menggunakan direktif `@if`, `@elseif`, `@else`, dan `@endif` untuk
membangun pernyataan `if`. Fungsi dari perintah-perintah ini sama persis
dengan pernyataan `PHP` yang sesuai:

```blade
@if (count($records) === 1)
    I have one record!
@elseif (count($records)> 1)
    I have multiple records!
@else
    I don't have any records!
@endif
```

Untuk kenyamanan, `Blade` juga menyediakan instruksi `@unless`:

```blade
@unless (is_signed_in())
    You are not signed in.
@endunless
```

Selain instruksi kondisional yang telah dibahas, instruksi `@isset` dan
`@empty` juga dapat digunakan sebagai shortcut untuk fungsi `PHP` yang sesuai:

```blade
@isset($records)
    // $records has been defined but not empty
@endisset

@empty($records)
    // $records is empty...
@endempty
```

#### Instruksi Blok

Anda dapat menggunakan perintah `@hasSection` untuk menentukan apakah suatu
blok berisi konten:

```blade
@hasSection('navigation')
    <div class="pull-right">
        @yield('navigation')
    </div>

    <div class="clearfix"></div>
@endif
```

Anda dapat menggunakan perintah `@sectionMissing` untuk menentukan apakah suatu
blok tidak memiliki konten:

```blade
@sectionMissing('navigation')
    <div class="pull-right">
        @include('default-navigation')
    </div>
@endif
```

#### Direktif Lingkungan (Environmental Directives)

Anda dapat menggunakan perintah `@production` untuk menentukan apakah aplikasi
berada di lingkungan produksi:

```blade
@production
    // Production environment specific content...
@endproduction
```

Atau, Anda dapat menggunakan perintah `@env` untuk menentukan apakah aplikasi
sedang berjalan di lingkungan tertentu:

```blade
@env('staging')
    // The application is running in the "staging" environment...
@endenv

@env(['staging','production'])
    // The application is running in a "staging" environment or a production environment...
@endenv
```

#### Pernyataan Switch

Anda dapat menggunakan pernyataan `@switch`, `@case`, `@break`, `@default`,
dan `@endswitch` untuk membangun pernyataan `Switch`:

```blade
@switch($i)
    @case(1)
        First case...
        @break

    @case(2)
        Second case...
        @break

    @default
        Default case...
@endswitch
```

#### Perulangan (Loop)

Selain pernyataan kondisional, `Blade` juga menyediakan instruksi dengan
fungsi yang sama seperti struktur perulangan milik `PHP`. Demikian pula, fungsi
dari pernyataan ini konsisten dengan sintaks `PHP` yang sesuai:

```blade
@for ($i = 0; $i <10; $i++)
    The current value is {{ $i }}
@endfor

@foreach ($users as $user)
    <p>This is user {{ $user->id }}</p>
@endforeach

@forelse ($users as $user)
    <li>{{ $user->name }}</li>
@empty
    <p>No users</p>
@endforelse

@while (true)
    <p>I'm looping forever.</p>
@endwhile
```

> Saat melakukan perulangan, Anda dapat menggunakan variabel loop untuk
> mendapatkan informasi berharga tentang perulangan tersebut, misalnya, apakah
> Anda berada pada iterasi pertama atau iterasi terakhir dari perulangan.

Saat menggunakan perulangan, Anda dapat menghentikan perulangan atau melewati
iterasi saat ini:

```blade
@foreach ($users as $user)
    @if ($user->type == 1)
        @continue
    @endif

    <li>{{ $user->name }}</li>

    @if ($user->number == 5)
        @break
    @endif
@endforeach
```

Anda dapat mendeklarasikan pernyataan kondisional pada satu baris instruksi:

```blade
@foreach ($users as $user)
    @continue($user->type == 1)

    <li>{{ $user->name }}</li>

    @break($user->number == 5)
@endforeach
```

#### Variabel Loop

Saat melakukan perulangan, variabel `$loop` dapat digunakan di dalam perulangan.
Variabel ini menyediakan cara untuk mengakses beberapa informasi seperti indeks
perulangan saat ini dan apakah iterasi ini adalah pertama atau terakhir kali:

```blade
@foreach ($users as $user)
    @if ($loop->first)
        This is the first iteration.
    @endif

    @if ($loop->last)
        This is the last iteration.
    @endif

    <p>This is user {{ $user->id }}</p>
@endforeach
```

Jika Anda berada di dalam nested loop (perulangan bersarang), Anda dapat
mengakses perulangan induk menggunakan properti `parent` dari variabel `$loop`
milik loop tersebut:

```blade
@foreach ($users as $user)
    @foreach ($user->posts as $post)
        @if ($loop->parent->first)
            This is first iteration of the parent loop.
        @endif
    @endforeach
@endforeach
```

Variabel `$loop` juga berisi berbagai atribut yang berguna:

| Properti | Keterangan |
|:--:|:--:|
| `$loop->index` | Indeks iterasi saat ini (dimulai dari 0). |
| `$loop->iteration` | Jumlah iterasi dari perulangan saat ini (dimulai dari 1). |
| `$loop->remaining` | Jumlah iterasi yang tersisa dari perulangan. |
| `$loop->count` | Jumlah elemen dalam array yang sedang diiterasi. |
| `$loop->first` | Apakah iterasi saat ini adalah iterasi pertama dari perulangan. |
| `$loop->last` | Apakah iterasi saat ini adalah iterasi terakhir dari perulangan. |
| `$loop->even` | Apakah jumlah iterasi perulangan saat ini bernilai genap. |
| `$loop->odd` | Apakah jumlah iterasi perulangan saat ini bernilai ganjil. |
| `$loop->depth` | Kedalaman sarang (nesting depth) dari perulangan saat ini. |
| `$loop->parent` | Perulangan induk dalam nested loop. |

#### Komentar

`Blade` juga memungkinkan Anda untuk mendefinisikan komentar di dalam view.
Namun berbeda dengan komentar `HTML`, komentar `Blade` tidak akan disertakan
dalam `HTML` yang dikembalikan oleh aplikasi:

```blade
{{-- This comment will not be present in the rendered HTML --}}
```

#### PHP

Dalam banyak kasus, menyematkan kode `PHP` di dalam view Anda sangatlah berguna.
Anda dapat menggunakan instruksi `@php` dari `Blade` di dalam template untuk
mengeksekusi blok kode `PHP` asli:

```blade
@php
    //
@endphp
```

> Meskipun Blade menyediakan fitur ini, sering menggunakannya dapat menyebabkan
> terlalu banyak logika yang disematkan di dalam template Anda.

#### Direktif @once

Direktif `@once` memungkinkan Anda untuk mendefinisikan bagian dari konten
template, yang hanya akan dihitung sekali dalam setiap siklus rendering.
Instruksi ini sangat berguna dalam konteks penggunaan `stack` untuk mendorong
(push) kode `JavaScript` tertentu ke bagian head halaman.
Sebagai contoh, jika Anda ingin merender `component` tertentu dalam sebuah loop,
Anda mungkin ingin mendorong kode `JavaScript` ke head hanya pada saat pertama
kali komponen dirender:

```blade
@once
    @push('scripts')
        <script>
            // Your custom JavaScript code
        </script>
    @endpush
@endonce
```

### Komponen dan Slot (Components and Slots)

Peran komponen dan slot mirip dengan Section dan Layout. Namun, beberapa
orang mungkin berpikir bahwa komponen dan slot lebih nyaman digunakan. Hyperf
mendukung dua metode penulisan komponen: class-based component dan anonymous
component.

Kita dapat mendefinisikan komponen kelas dengan membuat kelas yang mewarisi
kelas `\Hyperf\ViewEngine\Component\Component::class`. Berikut ini akan
menunjukkan cara menggunakan komponen tersebut dengan membuat komponen `Alert`
sederhana.

> app/View/Compoent/Alert.php

```php
<?php
namespace App\View\Component;
use Hyperf\ViewEngine\Component\Component;
use function Hyperf\ViewEngine\view;
class Alert extends Component
{
    public $type;
    public $message;
    public function __construct($type, $message)
    {
        $this->type = $type;
        $this->message = $message;
    }
    public function render()
    {
        retnurn view('components.alert');
    }
}
```

> storage/view/components/alert.blade.php
```html
<div class="alert alert-{{ $type }}">
    {{ $message }}
</div>
```

#### Mendaftarkan Komponen secara Manual

Di dalam `config/autoload/view.php`

```php
<?php
return [
    // ...
    'components' => [
        'alert' => \App\View\Component\Alert::class,
    ],
];
```

Or in the `ConfigProvider` in the extension package

```php
<?php
class ConfigProvider
{
    public function __invoke()
    {
        return [
            // ...others config
            'view' => [
                // ...others config
                'components' => [
                    'package-alert' => \App\View\Component\Alert::class,
                ],
            ],
        ];
    }
}
```

Setelah mendaftarkan komponen, Anda akan dapat menggunakannya melalui alias tag
HTML:

```html
<x-alert/>
<x-package-alert/>
```

#### Menampilkan Komponen

Anda dapat menggunakan tag komponen Blade di dalam template Blade apa pun untuk
menampilkan komponen. Tag komponen Blade dimulai dengan `x-`, diikuti oleh nama
komponen.

```html
<x-alert/>
<x-package-alert/>
```

#### Pengiriman Parameter Komponen (Component parameter transfer)

Anda dapat menggunakan atribut HTML untuk meneruskan data ke komponen Blade.
Nilai biasa dapat diteruskan melalui atribut HTML sederhana, sedangkan ekspresi
dan variabel PHP harus diteruskan melalui atribut yang diawali dengan `:`:

```html
<x-alert type="error" :message="$message"/>
```

!> Catatan: Anda dapat mendefinisikan data yang dibutuhkan oleh komponen dalam
konstruktor kelas komponen. Semua properti publik (public properties) di dalam
kelas komponen akan secara otomatis diteruskan ke view komponen. Data tersebut
tidak harus diteruskan melalui metode `render` dari kelas komponen. Saat
merender komponen, Anda dapat memperoleh konten dari properti publik kelas
komponen melalui nama variabelnya.

#### Metode Komponen

Selain mendapatkan properti publik dari kelas komponen, Anda juga dapat
mengeksekusi metode publik apa pun pada kelas komponen di dalam view komponen.
Sebagai contoh, sebuah komponen memiliki metode `isSelected`:

```php
    /**
     * Determine whether the given option is the current option
     *
     * @param string $option
     * @return bool
     */
    public function isSelected($option)
    {
        return $option === $this->selected;
    }
```

Anda dapat mengeksekusi metode tersebut dengan memanggil variabel yang memiliki
nama yang sama dengan metode tersebut:

```html
    <option {{ $isSelected($value)?'selected="selected"':'' }} value="{{ $value }}">
        {{ $label }}
    </option>
```

#### Dependensi Tambahan (Additional dependencies)

Jika komponen Anda perlu bergantung pada kelas lain, Anda harus mencantumkannya
sebelum semua atribut data dari komponen tersebut, dan mereka akan disuntikkan
secara otomatis oleh container:
```php
    use App\AlertCreator;
    /**
     * Create component instance
     *
     * @param \App\AlertCreator $creator
     * @param string $type
     * @param string $message
     * @return void
     */
    public function __construct(AlertCreator $creator, $type, $message)
    {
        $this->creator = $creator;
        $this->type = $type;
        $this->message = $message;
    }
```

#### Mengelola Properti (Manage properties)

Kita telah melihat cara meneruskan atribut data ke komponen. Namun, terkadang
kita mungkin perlu menentukan atribut HTML lain (seperti `class`), yang bukan
merupakan data yang dibutuhkan oleh komponen. Dalam hal ini, kita ingin
meneruskan atribut-atribut ini ke elemen akar (root element) dari template
komponen. Sebagai contoh, kita ingin merender komponen alert sebagai berikut:

```html
    <x-alert type="error" :message="$message" class="mt-4"/>
```

Semua properti yang bukan bagian dari konstruktor komponen akan secara
otomatis ditambahkan ke "property bag" komponen. Bagian properti ini akan
diteruskan ke view komponen melalui variabel `$attributes`. Dengan menampilkan
variabel ini, semua properti dapat dirender di dalam komponen:

```html
    <div {{ $attributes }}>
        <!-- Component content -->
    </div>
```

#### Mendapatkan Atribut

Anda dapat menggunakan metode `get()` untuk mendapatkan nilai atribut tertentu.
Metode ini menerima nama atribut sebagai parameter pertama (parameter kedua
adalah nilai default) dan mengembalikan nilainya.

```html
    <div class="{{ $attributes->get("class", "default") }}">
        <!-- Component content -->
    </div>
```

#### Mendeteksi Atribut

Anda dapat menggunakan metode `has()` untuk memeriksa apakah atribut tertentu
ada. Metode ini menerima nama atribut sebagai parameter dan akan mengembalikan
nilai boolean.

```html
    @if($attributes->has("class"))
        <div class="{{ $attributes->get("class") }}">
            <!-- Component content -->
        </div>
    @endif
```

#### Menggabungkan Atribut (Merging attributes)

Pada suatu waktu, Anda mungkin perlu menentukan nilai default dari suatu
atribut, atau menggabungkan nilai lain ke dalam atribut tertentu dari komponen.
Untuk ini, Anda dapat menggunakan metode `merge` dari property bag:

```html
    <div {{ $attributes->merge(['class' =>'alert alert-'.$type]) }}>
        {{ $message }}
    </div>
```

Misalkan kita menggunakan komponen ini seperti yang ditunjukkan di bawah ini:

```html
    <x-alert type="error" :message="$message" class="mb-4"/>
```

HTML komponen yang dirender pada akhirnya akan terlihat seperti ini:

```html
    <div class="alert alert-error mb-4">
        <!-- The content of the $message variable -->
    </div>
```

Secara default, hanya atribut `class` yang akan digabungkan, dan atribut lain
akan langsung ditimpa. Situasi berikut akan terjadi:

```blade
// definition
<div {{ $attributes->merge(['class' =>'alert alert-'.$type,'other-attr' =>'foo']) }}>{{ $message }}</div>
// use
<x-alert type="error" :message="$message" class="mb-4" other-attr="bar"/>
// present
<div class="alert alert-error mb-4" other-attr="bar"><!-- The content of the $message variable --></div>
```

Seperti pada kasus di atas, jika Anda perlu menggabungkan atribut `other-attr`,
Anda dapat menggunakan metode berikut untuk menambahkan parameter kedua `true`
ke metode `merge()`:

```blade
// definition
<div {{ $attributes->merge(['class' =>'alert alert-'.$type,'other-attr' =>'foo'], true) }}>{{ $message }}</ div>
// use
<x-alert type="error" :message="$message" class="mb-4" other-attr="bar"/>
// present
<div class="alert alert-error mb-4" other-attr="foo bar"><!-- The content of the $message variable --></div>
```

#### Slot

Biasanya, Anda perlu meneruskan konten tambahan ke komponen melalui `slots`.
Asumsikan komponen alert yang kita buat memiliki markup berikut:

```html
    <!-- /storage/view/components/alert.blade.php -->

    <div class="alert alert-danger">
        {{ $slot }}
    </div>
```

Kita dapat meneruskan konten ke `slots` dengan menyuntikkan konten ke dalam
komponen:

```html
    <x-alert>
        <strong>Whoops!</strong> Something went wrong!
    </x-alert>
```

Terkadang sebuah komponen mungkin perlu menempatkan beberapa slot yang berbeda
di posisi yang berbeda di dalamnya. Mari kita modifikasi komponen alert untuk
memungkinkan penyuntikan `title`.

```html
    <!-- /storage/view/components/alert.blade.php -->

    <span class="alert-title">{{ $title }}</span>

    <div class="alert alert-danger">
        {{ $slot }}
    </div>
```

Anda dapat menggunakan tag `x-slot` untuk mendefinisikan konten dari slot
bernama. Konten lain yang tidak berada dalam tag `x-slot` akan diteruskan ke
komponen dalam variabel `$slot`:

```html
    <x-alert>
        <x-slot name="title">
            Server Error
        </x-slot>

        <strong>Whoops!</strong> Something went wrong!
    </x-alert>
```

#### Inline Component

Untuk komponen kecil, mengelola kelas komponen dan template view komponen bisa
merepotkan. Oleh karena itu, Anda dapat mengembalikan konten komponen langsung
dari metode `render`:

```php
    public function render()
    {
        return <<<'blade'
            <div class="alert alert-danger">
                {{ $slot }}
            </div>blade;
    }
```

#### Anonymous Component

Seperti halnya inline component, anonymous component menyediakan mekanisme untuk
mengelola komponen melalui satu file saja. Namun, anonymous component
menggunakan satu file view tanpa kelas yang terkait dengannya. Untuk
mendefinisikan anonymous component, Anda hanya perlu meletakkan template Blade
di direktori `/storage/view/components`.

Sebagai contoh, jika Anda mendefinisikan komponen di
`/storage/view/components/alert.blade.php`:

```html
    <x-alert/>
```

Jika komponen berada dalam subdirektori dari direktori `components`, Anda dapat
menggunakan karakter `.` untuk menentukan jalurnya. Sebagai contoh, jika
komponen didefinisikan di `/storage/view/components/inputs/button.blade.php`,
Anda dapat merendernya seperti ini:

```html
    <x-inputs.button/>
```

#### Data dan Atribut Anonymous Component

Karena anonymous component tidak memiliki kelas terkait, Anda mungkin ingin
membedakan data mana yang harus diteruskan ke komponen sebagai variabel dan
properti mana yang harus disimpan dalam [property bag](#mengelola-properti).

Anda dapat menggunakan direktif `@props` di tingkat paling atas dari template
Blade komponen untuk menentukan properti mana yang harus digunakan sebagai
variabel data. Semua properti lain dalam komponen akan disediakan dalam bentuk
property bag. Jika Anda ingin menentukan nilai default untuk variabel data
tertentu, Anda dapat menggunakan nama atribut sebagai kunci array dan nilai
default sebagai nilai array:

```blade
    <!-- /storage/view/components/alert.blade.php -->

    @props(['type' =>'info','message'])

    <div {{ $attributes->merge(['class' =>'alert alert-'.$type]) }}>
        {{ $message }}
    </div>
```

#### Komponen Dinamis (Dynamic Components)

Terkadang, Anda mungkin perlu merender komponen, tetapi tidak tahu komponen
mana yang akan dirender sebelum aplikasi dijalankan. Dalam hal ini, Anda dapat
gunakan komponen bawaan `dynamic-component` untuk merender komponen
berdasarkan nilai atau variabel:

```html
    <x-dynamic-component :component="$componentName" class="mt-4" />
```

#### Pemuatan Komponen secara Otomatis (Automatic component loading)

Secara default, komponen di bawah `App\View\Component\` dan `components.`
didaftarkan secara otomatis. Anda juga dapat mengubah konfigurasi ini melalui
file konfigurasi:

> config/autoload/view.php

```php
return [
    // ...
    'autoload' => [
        'classes' => ['App\\Other\\Component\\','App\\Another\\Component\\'],
        'components' => ['package::components.','components.'],
    ],
];
```

## Namespace View (View Namespace)

Dengan mendefinisikan namespace view, Anda dapat dengan mudah menggunakan file
view di dalam extension package Anda. Anda hanya perlu menambahkan baris
konfigurasi di `ConfigProvider`:

```php
<?php
class ConfigProvider
{
    public function __invoke()
    {
        return [
            // ...others config
            'view' => [
                // ...others config
                'namespaces' => [
                    'package-name' => __DIR__.'/../views',
                ],
            ],
        ];
    }
}
```

Setelah menginstal extension package, Anda dapat menimpa (override) view di
dalam extension package tersebut dengan mendefinisikan file view dengan jalur
(path) yang sama di dalam `/storage/view/vendor/package-name` proyek Anda.

## Middleware Opsional

- `Hyperf\ViewEngine\Http\Middleware\ShareErrorsFromSession::class`

Secara otomatis membagikan `errors` di dalam `session` ke view, bergantung pada
komponen `hyperf/session`.

- `Hyperf\ViewEngine\Http\Middleware\ValidationExceptionHandle::class`

Secara otomatis menangkap exception dalam `validation` and menambahkannya ke
`session`, bergantung pada komponen `hyperf/session` dan `hyperf/validation`.

## Perintah Lainnya

Instalasi otomatis konfigurasi terkait komponen `view-engine`, `translation`,
dan `validation`:

```
php bin/hyperf.php view:publish
```
