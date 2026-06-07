# View Engine

> Dimodifikasi dari Laravel Blade template engine, mendukung sintaks dari Blade template engine asli.

```bash
composer require hyperf/view-engine
```

## Membuat Konfigurasi

```bash
php bin/hyperf.php vendor:publish hyperf/view-engine
```

Konfigurasi default adalah sebagai berikut:

> Komponen ini merekomendasikan penggunaan mode rendering SYNC, yang dapat mengurangi overhead komunikasi antar proses.

```php
return [
    'engine' => Hyperf\ViewEngine\HyperfViewEngine::class,
    'mode' => Hyperf\View\Mode::SYNC,
    'config' => [
        'view_path' => BASE_PATH . '/storage/view/',
        'cache_path' => BASE_PATH . '/runtime/view/',
    ],

    # Pendaftaran komponen kustom
    'components' => [
        // 'alert' => \App\View\Components\Alert::class
    ],
    
    # View namespace (terutama digunakan di extension package)
    'namespaces' => [
        // 'admin' => BASE_PATH . '/storage/view/vendor/admin',
    ],
];
```

## Penggunaan

> Tutorial ini banyak meminjam dari [LearnKu](https://learnku.com). Terima kasih banyak kepada LearnKu atas kontribusinya kepada komunitas PHP.

### Pendahuluan

`Blade` adalah template engine yang sederhana namun powerful yang disediakan oleh `Laravel`. Tidak seperti template engine `PHP` populer lainnya, `Blade` tidak membatasi Anda untuk menggunakan kode `PHP` native di view.
Semua file view `Blade` akan dikompilasi menjadi kode `PHP` native dan di-cache. Kecuali jika dimodifikasi, mereka tidak akan dikompilasi ulang, yang berarti `Blade` pada dasarnya tidak menambah beban pada aplikasi Anda.
File view `Blade` menggunakan ekstensi file `.blade.php` dan disimpan di direktori `storage/view` secara default.

### Template Inheritance

#### Mendefinisikan Layout

Pertama, mari kita pelajari layout halaman "master". Karena sebagian besar aplikasi `web` menggunakan layout yang sama pada halaman yang berbeda, akan lebih mudah untuk mendefinisikan satu view layout `Blade`:

```blade
<!-- Disimpan di storage/view/layouts/app.blade.php -->

<html>
    <head>
        <title>Nama Aplikasi - @yield('title')</title>
    </head>
    <body>
        @section('sidebar')
            Ini adalah sidebar master.
        @show

        <div class="container">
            @yield('content')
        </div>
    </body>
</html>
```

Seperti yang Anda lihat, kode ini berisi `HTML` biasa. Tapi perhatikan direktif `@section` dan `@yield`. Sesuai dengan arti `section`, direktif `@section` mendefinisikan konten dari sebuah segmen, sementara direktif `@yield` digunakan untuk menampilkan konten dari segmen tersebut.
Sekarang kita telah mendefinisikan layout untuk aplikasi ini, mari kita definisikan halaman anak yang mewarisi dari layout ini.

#### Layout Inheritance

Saat mendefinisikan view anak, gunakan direktif `@extends` dari `Blade` untuk menentukan view yang akan "diwarisi" oleh view anak. View yang memperluas layout `Blade` dapat menyuntikkan konten ke dalam segmen layout menggunakan direktif `@section`.
Seperti yang ditunjukkan pada contoh sebelumnya, konten dari segmen-segmen ini akan dikontrol untuk ditampilkan oleh direktif `@yield` di layout:

```blade
<!-- Disimpan di storage/view/child.blade.php -->

@extends('layouts.app')

@section('title', 'Judul Halaman')

@section('sidebar')
    @parent

    <p>Ini ditambahkan ke sidebar master.</p>
@endsection

@section('content')
    <p>Ini adalah konten tubuh saya.</p>
@endsection
```

Dalam contoh ini, segmen `sidebar` menggunakan direktif `@parent` untuk menambahkan (bukan menimpa) konten ke `sidebar` layout. Ketika view dirender, direktif `@parent` akan digantikan oleh konten di layout.

> Berbeda dengan contoh sebelumnya, segmen sidebar di sini diakhiri dengan @endsection bukan @show. Direktif @endsection hanya mendefinisikan segmen, sementara @show segera menampilkan segmen ini sambil mendefinisikannya.

Direktif `@yield` juga menerima nilai default sebagai argumen kedua. Jika segmen yang "di-yield" tidak didefinisikan, nilai default akan dirender:

```blade
@yield('content', 'Hyperf')
```

View `Blade` dapat dikembalikan oleh fungsi pembantu `Hyperf\ViewEngine\view`:

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

Anda dapat menempatkan variabel di dalam kurung kurawal untuk menampilkan data di view. Misalnya, dengan route berikut:

```php
use Hyperf\HttpServer\Router\Router;
use function Hyperf\ViewEngine\view;

Router::get('greeting', function () {
    return view('welcome', ['name' => 'Samantha']);
});
```

Anda dapat menampilkan konten dari variabel `name` seperti ini:

```blade
Hello, {{ $name }}.
```

> Pernyataan {{ }} Blade secara otomatis di-escape oleh fungsi htmlspecialchars PHP untuk mencegah serangan XSS.

Anda tidak hanya dapat menampilkan konten variabel yang diteruskan ke view, tetapi juga dapat mengeluarkan hasil dari fungsi `PHP` apa pun. Bahkan, Anda dapat menempatkan kode `PHP` apa pun di dalam pernyataan echo template `Blade`:

```blade
Timestamp UNIX saat ini adalah {{ time() }}.
```

#### Menampilkan Karakter yang Tidak Di-escape

Secara default, pernyataan `Blade {{ }}` secara otomatis di-escape oleh fungsi `htmlspecialchars` `PHP` untuk mencegah serangan `XSS`. Jika Anda tidak ingin data Anda di-escape, Anda dapat menggunakan sintaks berikut:

```blade
Hello, {!! $name !!}.
```

> Berhati-hatilah saat menampilkan data yang disediakan pengguna di aplikasi Anda. Harap gunakan escaping dan sintaks tanda kutip ganda bila memungkinkan untuk mencegah serangan XSS.

#### Rendering JSON

Terkadang, untuk menginisialisasi variabel `JavaScript`, Anda mungkin melewatkan sebuah array ke view dan merendernya sebagai `JSON`. Contoh:

```
<script>
    var app = <?php echo json_encode($array); ?>;
</script>
```

Tentu saja, Anda juga dapat menggunakan direktif `@json` `Blade` daripada memanggil metode `json_encode` secara manual. Argumen untuk direktif `@json` sama dengan fungsi `json_encode` di `PHP`:

```html
<script>
    var app = @json($array);

    var app = @json($array, JSON_PRETTY_PRINT);
</script>
```

> Saat menggunakan direktif @json, Anda sebaiknya hanya merender variabel yang sudah ada sebagai JSON. Template Blade berbasis regex, dan mencoba melewatkan ekspresi kompleks ke direktif @json dapat menyebabkan kesalahan yang tidak terduga.

#### Encoding HTML Entity

Secara default, `Blade` akan melakukan double-encode pada entitas `HTML`. Jika Anda ingin menonaktifkan ini, Anda dapat mendengarkan event `BootApplication` dan memanggil metode `Blade::withoutDoubleEncoding`:

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
    private ContainerInterface $container;

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

#### Blade & JavaScript Frameworks

Karena banyak framework `JavaScript` juga menggunakan "kurung kurawal" untuk mengidentifikasi ekspresi yang akan ditampilkan di browser, Anda dapat menggunakan simbol `@` untuk memberi tahu mesin rendering `Blade` bahwa itu harus tetap tidak berubah. Contoh:

```blade
<h1>Laravel</h1>

Hello, @{{ name }}.
```

Dalam contoh ini, simbol `@` akan dihapus oleh `Blade`; tentu saja, `Blade` tidak akan memodifikasi ekspresi `{{ name }}`, dan template `JavaScript` akan merendernya sebagai gantinya.
Simbol `@` juga digunakan untuk meng-escape direktif `Blade`:

```
{{-- Blade --}}
@@json()

<!-- Output HTML -->
@json()
```

Jika Anda menampilkan sebagian besar variabel `JavaScript` di template Anda, Anda dapat menyematkan `HTML` ke dalam direktif `@verbatim`, sehingga Anda tidak perlu menambahkan simbol `@` sebelum setiap pernyataan echo `Blade`:

```blade
@verbatim
    <div class="container">
        Hello, {{ name }}.
    </div>
@endverbatim
```

### Control Structures

Selain template inheritance dan menampilkan data, `Blade` menyediakan pintasan yang nyaman untuk struktur kontrol `PHP` yang umum, seperti pernyataan kondisional dan loop. Pintasan ini menyediakan cara yang sangat jelas dan ringkas untuk menulis struktur kontrol `PHP` sambil mempertahankan sintaks yang mirip dengan struktur kontrol di `PHP`.

#### If Statements

Anda dapat membuat pernyataan `if` menggunakan direktif `@if`, `@elseif`, `@else`, dan `@endif`. Direktif ini berfungsi persis sama dengan pernyataan `PHP` yang sesuai:

```blade
@if (count($records) === 1)
    Saya punya satu record!
@elseif (count($records) > 1)
    Saya punya banyak record!
@else
    Saya tidak punya record!
@endif
```

Untuk kenyamanan, `Blade` juga menyediakan direktif `@unless`:

```blade
@unless (is_signed_in())
    Anda belum masuk.
@endunless
```

Selain direktif kondisional yang telah dibahas, direktif `@isset` dan `@empty` juga dapat digunakan sebagai pintasan untuk fungsi `PHP` yang sesuai:

```blade
@isset($records)
    // $records terdefinisi dan tidak kosong
@endisset

@empty($records)
    // $records kosong...
@endempty
```

#### Block Directives

Anda dapat menggunakan direktif `@hasSection` untuk memeriksa apakah suatu blok memiliki konten:

```blade
@hasSection('navigation')
    <div class="pull-right">
        @yield('navigation')
    </div>

    <div class="clearfix"></div>
@endif
```

Anda dapat menggunakan direktif `@sectionMissing` untuk memeriksa apakah suatu blok tidak memiliki konten:

```blade
@sectionMissing('navigation')
    <div class="pull-right">
        @include('default-navigation')
    </div>
@endif
```

#### Environment Directives

Anda dapat menggunakan direktif `@production` untuk memeriksa apakah aplikasi berada di lingkungan production:

```blade
@production
    // Konten khusus lingkungan production...
@endproduction
```

Atau, Anda dapat menggunakan direktif `@env` untuk memeriksa apakah aplikasi berjalan di lingkungan yang ditentukan:

```blade
@env('staging')
    // Aplikasi berjalan di lingkungan "staging"...
@endenv

@env(['staging', 'production'])
    // Aplikasi berjalan di lingkungan "staging" atau production...
@endenv
```

#### Switch Statements

Anda dapat membuat pernyataan `Switch` menggunakan direktif `@switch`, `@case`, `@break`, `@default`, dan `@endswitch`:

```blade
@switch($i)
    @case(1)
        Kasus pertama...
        @break

    @case(2)
        Kasus kedua...
        @break

    @default
        Kasus default...
@endswitch
```

#### Loops

Selain pernyataan kondisional, `Blade` menyediakan direktif yang memiliki fungsionalitas yang sama dengan struktur loop `PHP`. Demikian pula, pernyataan ini berfungsi sama dengan sintaks `PHP` yang sesuai:

```blade
@for ($i = 0; $i < 10; $i++)
    Nilai saat ini adalah {{ $i }}
@endfor

@foreach ($users as $user)
    <p>Ini adalah user {{ $user->id }}</p>
@endforeach

@forelse ($users as $user)
    <li>{{ $user->name }}</li>
@empty
    <p>Tidak ada users</p>
@endforelse

@while (true)
    <p>Saya melakukan loop selamanya.</p>
@endwhile
```

> Saat melakukan loop, Anda dapat menggunakan variabel loop untuk mendapatkan informasi berharga tentang loop, misalnya, apakah Anda berada di iterasi pertama dari loop atau iterasi terakhir.

Saat menggunakan loop, Anda dapat menghentikan loop atau melewati iterasi saat ini:

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

Anda dapat mendeklarasikan pernyataan kondisional pada satu baris direktif:

```blade
@foreach ($users as $user)
    @continue($user->type == 1)

    <li>{{ $user->name }}</li>

    @break($user->number == 5)
@endforeach
```

#### Loop Variable

Saat melakukan loop, variabel `$loop` dapat digunakan di dalam loop. Variabel ini menyediakan cara untuk mengakses informasi seperti indeks loop saat ini dan apakah iterasi ini adalah yang pertama atau terakhir:

```blade
@foreach ($users as $user)
    @if ($loop->first)
        Ini adalah iterasi pertama.
    @endif

    @if ($loop->last)
        Ini adalah iterasi terakhir.
    @endif

    <p>Ini adalah user {{ $user->id }}</p>
@endforeach
```

Jika Anda berada dalam nested loop, Anda dapat menggunakan properti `parent` dari variabel `$loop` untuk mengakses parent loop:

```blade
@foreach ($users as $user)
    @foreach ($user->posts as $post)
        @if ($loop->parent->first)
            Ini adalah iterasi pertama dari parent loop.
        @endif
    @endforeach
@endforeach
```

Variabel `$loop` juga berisi berbagai properti yang berguna:

| Properti | Catatan |
|:--:|:--:|
| `$loop->index` | Indeks dari iterasi saat ini (dimulai dari 0). |
| `$loop->iteration` | Jumlah iterasi loop saat ini (dimulai dari 1). |
| `$loop->remaining` | Jumlah iterasi yang tersisa dalam loop. |
| `$loop->count` | Jumlah elemen dalam array yang sedang diiterasi. |
| `$loop->first` | Apakah iterasi saat ini adalah iterasi pertama dari loop. |
| `$loop->last` | Apakah iterasi saat ini adalah iterasi terakhir dari loop. |
| `$loop->even` | Apakah jumlah iterasi loop saat ini genap. |
| `$loop->odd` | Apakah jumlah iterasi loop saat ini ganjil. |
| `$loop->depth` | Tingkat kedalaman nesting dari loop saat ini. |
| `$loop->parent` | Parent loop dalam nested loop. |

#### Comments

`Blade` juga memungkinkan Anda untuk mendefinisikan komentar di view. Namun, tidak seperti komentar `HTML`, komentar `Blade` tidak akan disertakan dalam `HTML` yang dikembalikan oleh aplikasi:

```blade
{{-- Komentar ini tidak akan ada dalam HTML yang dirender --}}
```

#### PHP

Dalam banyak kasus, menyematkan kode `PHP` ke dalam view Anda sangat berguna. Anda dapat mengeksekusi blok kode `PHP` native di template menggunakan direktif `@php` Blade:

```blade
@php
    //
@endphp
```

> Meskipun Blade menyediakan fungsionalitas ini, menggunakannya secara sering dapat mengakibatkan terlalu banyak logika yang disematkan di template Anda.

#### @once Directive

Direktif `@once` memungkinkan Anda untuk mendefinisikan bagian dari template yang akan dihitung hanya sekali dalam setiap siklus rendering.
Direktif ini sangat berguna ketika mendorong potongan kode `JavaScript` tertentu ke head halaman menggunakan `stacks`.
Misalnya, jika Anda ingin merender `component` tertentu dalam sebuah loop, Anda mungkin ingin mendorong kode `JavaScript` ke head hanya ketika component dirender untuk pertama kalinya:

```blade
@once
    @push('scripts')
        <script>
            // Kode JavaScript kustom Anda
        </script>
    @endpush
@endenonce
```

### Components dan Slots

Components dan slots memiliki peran yang mirip dengan Sections dan Layouts. Namun, beberapa orang mungkin merasa components dan slots lebih nyaman digunakan. Hyperf mendukung dua metode untuk menulis components: class-based components dan anonymous components.

Kita dapat mendefinisikan class component dengan membuat kelas yang memperluas kelas `\Hyperf\ViewEngine\Component\Component::class`. Berikut akan menjelaskan cara menggunakan components dengan membuat component `Alert` yang sederhana.

> app/View/Component/Alert.php

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
        return view('components.alert');
    }
}
```

> storage/view/components/alert.blade.php
```html
<div class="alert alert-{{ $type }}">
    {{ $message }}
</div>
```

#### Mendaftarkan Components Secara Manual

Di `config/autoload/view.php`:

```php
<?php
return [
    // ...
    'components' => [
        'alert' => \App\View\Component\Alert::class,
    ],
];
```

Atau di `ConfigProvider` dari sebuah extension package:

```php
<?php
class ConfigProvider
{
    public function __invoke()
    {
        return [
            // ...konfigurasi lainnya
            'view' => [
                // ...konfigurasi lainnya
                'components' => [
                    'package-alert' => \App\View\Component\Alert::class,
                ],
            ],
        ];
    }
}
```

Setelah mendaftarkan component, Anda dapat menggunakannya melalui alias tag HTML-nya:

```html
<x-alert/>
<x-package-alert/>
```

#### Menampilkan Components

Anda dapat menggunakan tag component Blade di template Blade mana pun untuk menampilkan components. Tag component Blade dimulai dengan `x-`, diikuti oleh nama component.

```html
<x-alert/>
<x-package-alert/>
```

#### Melewatkan Data ke Components

Anda dapat melewatkan data ke component Blade menggunakan atribut HTML. Nilai biasa dapat dilewatkan melalui atribut HTML sederhana, sementara ekspresi PHP dan variabel harus dilewatkan melalui atribut dengan awalan `:`.

```html
<x-alert type="error" :message="$message"/>
```

!> Catatan: Anda dapat mendefinisikan data yang diperlukan oleh component di konstruktor kelas component. Semua properti public dari kelas component akan secara otomatis diteruskan ke view component. Tidak perlu meneruskannya melalui metode `render` kelas component. Saat merender component, Anda bisa mendapatkan konten dari properti public kelas component melalui nama variabel.

#### Component Methods

Selain mengakses properti public dari kelas component, Anda juga dapat mengeksekusi metode public apa pun pada kelas component di view component. Misalnya, sebuah component memiliki metode `isSelected`:

```php
    /**
     * Menentukan apakah opsi yang diberikan saat ini dipilih
     *
     * @param  string  $option
     * @return bool
     */
    public function isSelected($option)
    {
        return $option === $this->selected;
    }
```

Anda dapat mengeksekusi metode ini dengan memanggil variabel dengan nama yang sama dengan metode:

```html
    <option {{ $isSelected($value) ? 'selected="selected"' : '' }} value="{{ $value }}">
        {{ $label }}
    </option>
```

#### Additional Dependencies

Jika component Anda perlu bergantung pada kelas lain, daftarkan mereka sebelum semua atribut data dari component, dan mereka akan secara otomatis diinjeksi oleh container:

```php
    use App\AlertCreator;
    /**
     * Membuat instance component
     *
     * @param  \App\AlertCreator  $creator
     * @param  string  $type
     * @param  string  $message
     * @return void
     */
    public function __construct(AlertCreator $creator, $type, $message)
    {
        $this->creator = $creator;
        $this->type = $type;
        $this->message = $message;
    }
```

#### Mengelola Attributes

Kita telah mempelajari cara melewatkan atribut data ke components. Namun, terkadang kita mungkin perlu menentukan atribut HTML lainnya (seperti `class`), yang bukan merupakan data yang diperlukan oleh component. Dalam hal ini, kita akan ingin melewatkan atribut-atribut ini ke elemen root dari template component. Misalnya, jika kita ingin merender component `alert`, sebagai berikut:

```html
    <x-alert type="error" :message="$message" class="mt-4"/>
```

Semua atribut yang bukan milik konstruktor component akan secara otomatis ditambahkan ke "attribute bag" component. Attribute bag ini akan diteruskan ke view component melalui variabel `$attributes`. Dengan mengeluarkan variabel ini, Anda dapat menyajikan semua atribut di component:

```html
    <div {{ $attributes }}>
        <!-- Konten component -->
    </div>
```

#### Mendapatkan Attributes

Anda dapat menggunakan metode `get()` untuk mendapatkan nilai atribut tertentu. Metode ini menerima nama atribut sebagai argumen pertama (argumen kedua adalah nilai default) dan mengembalikan nilainya.

```html
    <div class="{{ $attributes->get("class", "default") }}">
        <!-- Konten component -->
    </div>
```

#### Memeriksa Attributes

Anda dapat menggunakan metode `has()` untuk mendapatkan nilai atribut tertentu. Metode ini menerima nama atribut sebagai argumen dan mengembalikan nilai boolean.

```html
    @if($attributes->has("class"))
        <div class="{{ $attributes->get("class") }}">
            <!-- Konten component -->
        </div>
    @endif
```

#### Menggabungkan Attributes

Terkadang, Anda mungkin perlu menentukan nilai default untuk atribut, atau menggabungkan nilai lain ke dalam atribut tertentu dari component. Untuk melakukannya, Anda dapat menggunakan metode `merge` dari attribute bag:

```html
    <div {{ $attributes->merge(['class' => 'alert alert-'.$type]) }}>
        {{ $message }}
    </div>
```

Misalkan kita menggunakan component seperti di bawah ini:

```html
    <x-alert type="error" :message="$message" class="mb-4"/>
```

HTML component yang akhirnya disajikan akan menjadi sebagai berikut:

```html
    <div class="alert alert-error mb-4">
        <!-- Konten dari variabel $message -->
    </div>
```

Secara default, hanya atribut `class` yang akan digabungkan; atribut lainnya akan langsung ditimpa, menghasilkan situasi berikut:

```blade
// Definisi
<div {{ $attributes->merge(['class' => 'alert alert-'.$type, 'other-attr' => 'foo']) }}>{{ $message }}</div>
// Penggunaan
<x-alert type="error" :message="$message" class="mb-4" other-attr="bar"/>
// Render
<div class="alert alert-error mb-4" other-attr="bar"><!-- Konten dari variabel $message --></div>
```

Seperti dalam kasus di atas, jika Anda juga perlu menggabungkan atribut `other-attr`, Anda dapat menggunakan metode berikut dengan menambahkan argumen kedua `true` ke metode `merge()`:

```blade
// Definisi
<div {{ $attributes->merge(['class' => 'alert alert-'.$type, 'other-attr' => 'foo'], true) }}>{{ $message }}</div>
// Penggunaan
<x-alert type="error" :message="$message" class="mb-4" other-attr="bar"/>
// Render
<div class="alert alert-error mb-4" other-attr="foo bar"><!-- Konten dari variabel $message --></div>
```

#### Slots

Biasanya, Anda perlu melewatkan konten tambahan ke component melalui `slots`. Misalkan component `alert` yang kita buat memiliki markup berikut:

```html
    <!-- /storage/view/components/alert.blade.php -->

    <div class="alert alert-danger">
        {{ $slot }}
    </div>
```

Kita dapat melewatkan konten ke `slots` dengan menyuntikkan konten ke component:

```html
    <x-alert>
        <strong>Whoops!</strong> Sesuatu telah salah!
    </x-alert>
```

Terkadang sebuah component mungkin perlu menempatkan beberapa slot yang berbeda di posisi yang berbeda di dalamnya. Mari kita modifikasi component `alert` untuk memungkinkan penyuntikan `title`.

```html
    <!-- /storage/view/components/alert.blade.php -->

    <span class="alert-title">{{ $title }}</span>

    <div class="alert alert-danger">
        {{ $slot }}
    </div>
```

Anda dapat menggunakan tag `x-slot` untuk mendefinisikan konten untuk slot bernama. Konten lain yang tidak berada dalam tag `x-slot` akan diteruskan ke variabel `$slot` dari component:

```html
    <x-alert>
        <x-slot name="title">
            Server Error
        </x-slot>

        <strong>Whoops!</strong> Sesuatu telah salah!
    </x-alert>
```

#### Inline Components

Untuk component kecil, mengelola baik kelas component maupun template view component bisa merepotkan. Oleh karena itu, Anda dapat mengembalikan konten component dari metode `render`:

```php
    public function render()
    {
        return <<<'blade'
            <div class="alert alert-danger">
                {{ $slot }}
            </div>
        blade;
    }
```

#### Anonymous Components

Mirip dengan inline components, anonymous components menyediakan mekanisme untuk mengelola components melalui satu file. Namun, anonymous components menggunakan satu file view yang tidak terkait dengan sebuah kelas. Untuk mendefinisikan anonymous component, Anda hanya perlu menempatkan template Blade di direktori `/storage/view/components`.
Misalnya, Anda mendefinisikan component di `/storage/view/components/alert.blade.php`:

```html
    <x-alert/>
```

Jika component berada di subdirektori dari direktori `components`, Anda dapat menggunakan karakter `.` untuk menentukan path-nya. Misalnya, component didefinisikan di `/storage/view/components/inputs/button.blade.php`. Anda dapat merendernya seperti ini:

```html
    <x-inputs.button/>
```

#### Data dan Attributes Anonymous Component

Karena anonymous components tidak memiliki kelas terkait, Anda mungkin ingin membedakan data mana yang harus diteruskan ke component sebagai variabel dan atribut mana yang harus disimpan di [attribute bag](#mengelola-attributes).

Anda dapat menggunakan direktif `@props` di tingkat atas template Blade component untuk menentukan atribut mana yang harus diperlakukan sebagai variabel data. Atribut lainnya di component akan disediakan dalam bentuk attribute bag. Jika Anda ingin menentukan nilai default untuk variabel data, Anda dapat menggunakan nama atribut sebagai kunci array dan nilai default sebagai nilai array:

```blade
    <!-- /storage/view/components/alert.blade.php -->

    @props(['type' => 'info', 'message'])

    <div {{ $attributes->merge(['class' => 'alert alert-'.$type]) }}>
        {{ $message }}
    </div>
```

#### Dynamic Components

Terkadang, Anda mungkin perlu merender component tetapi tidak tahu yang mana yang akan dirender sebelum dijalankan. Dalam hal ini, Anda dapat menggunakan component bawaan `dynamic-component` untuk merender component berdasarkan nilai atau variabel:

```html
    <x-dynamic-component :component="$componentName" class="mt-4" />
```

#### Pemuatan Component Otomatis

Secara default, components di bawah `App\View\Component\` dan `components.` akan terdaftar secara otomatis. Anda juga dapat memodifikasi konfigurasi ini melalui file konfigurasi:

> config/autoload/view.php

```php
return [
    // ...
    'autoload' => [
        'classes' => ['App\\Other\\Component\\', 'App\\Another\\Component\\'],
        'components' => ['package::components.', 'components.'],
    ],
];
```

## View Namespace

Dengan mendefinisikan view namespace, akan memudahkan penggunaan file view di extension package Anda. Anda hanya perlu menambahkan satu baris konfigurasi di `ConfigProvider`:

```php
<?php
class ConfigProvider
{
    public function __invoke()
    {
        return [
            // ...konfigurasi lainnya
            'view' => [
                // ...konfigurasi lainnya
                'namespaces' => [
                    'package-name' => __DIR__ . '/../views',
                ],
            ],
        ];
    }
}
```

Setelah menginstal extension package, Anda dapat menimpa file view di extension package dengan mendefinisikan file view dengan path yang sama di direktori `/storage/view/vendor/package-name` project.

## Middleware Opsional

- Hyperf\ViewEngine\Http\Middleware\ShareErrorsFromSession::class

Secara otomatis membagikan `errors` dari `session` ke view, bergantung pada komponen `hyperf/session`

- Hyperf\ViewEngine\Http\Middleware\ValidationExceptionHandle::class

Secara otomatis menangkap exception di `validation` dan menambahkannya ke `session`, bergantung pada komponen `hyperf/session` dan `hyperf/validation`

## Perintah Lainnya

Secara otomatis menginstal konfigurasi yang terkait dengan komponen `view-engine`, `translation`, dan `validation`

```
php bin/hyperf.php view:publish
```
