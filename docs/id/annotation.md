# Annotation

Annotation adalah fitur yang sangat kuat di Hyperf yang dapat digunakan untuk
mengurangi banyak konfigurasi dalam bentuk annotation dan untuk
mengimplementasikan berbagai fitur yang sangat memudahkan.

## Concept

### What is annotation?

Attribute menawarkan kemampuan untuk menambahkan informasi metadata terstruktur yang
dapat dibaca mesin pada deklarasi di dalam kode: class, method, function,
parameter, property, dan class constant dapat menjadi target dari sebuah
attribute. Metadata yang didefinisikan oleh attribute kemudian dapat diperiksa
saat runtime menggunakan Reflection API. Oleh karena itu, attribute dapat
dianggap sebagai bahasa konfigurasi yang disematkan langsung ke dalam kode.

Dengan attribute, implementasi generik dari suatu fitur dan penggunaan
konkretnya dalam aplikasi dapat dipisahkan (decoupled). Dalam beberapa hal, ini
dapat dibandingkan dengan interface dan implementasinya. Namun, jika interface
dan implementasi berkaitan dengan kode, attribute berkaitan dengan memberikan
informasi tambahan (annotating) dan konfigurasi. Interface dapat diimplementasi
oleh class, sedangkan attribute juga dapat dideklarasikan pada method, function,
parameter, property, dan class constant. Dengan demikian, attribute lebih
fleksibel daripada interface.

Contoh sederhana dari penggunaan attribute adalah mengubah interface yang memiliki
method opsional untuk menggunakan attribute. Mari kita asumsikan sebuah interface
ActionHandler yang merepresentasikan suatu operasi dalam aplikasi, di mana
beberapa implementasi dari action handler memerlukan setup dan yang lainnya
tidak. Alih-alih mengharuskan semua class yang mengimplementasikan ActionHandler
untuk mengimplementasikan method setUp(), sebuah attribute dapat digunakan. Salah
satu keuntungan dari pendekatan ini adalah kita dapat menggunakan attribute
tersebut beberapa kali.

### How it works?

Seperti yang telah dikatakan bahwa annotation hanyalah definisi metadata yang
harus bekerja dengan aplikasi agar dapat berfungsi. Di Hyperf, data dalam
annotation dikumpulkan ke dalam class
`Hyperf\Di\Annotation\AnnotationCollector` untuk digunakan oleh aplikasi.
Bergantung pada kebutuhan Anda, Anda juga dapat mengumpulkan data tersebut ke
class kustom Anda sendiri, kemudian membaca dan memanfaatkan metadata annotation
yang terkumpul di tempat di mana annotation itu sendiri diharapkan bekerja untuk
mencapai implementasi fungsional yang diinginkan.

### Ignore some annotations

Dalam beberapa kasus, kita mungkin ingin mengabaikan annotation tertentu.
Sebagai contoh, ketika kita mengakses beberapa tool yang menghasilkan dokumen
secara otomatis, banyak tool menggunakan annotation untuk mendefinisikan konten
struktural yang relevan dari dokumen tersebut. Annotation ini mungkin tidak
sejalan dengan bagaimana Hyperf digunakan, sehingga kita dapat mengatur agar
annotation tersebut diabaikan melalui `config/autoload/annotations.php`.

```php
use JetBrains\PhpStorm\ArrayShape;

return [
    'scan' => [
        // Annotations in the ignore_annotations array will be ignored by the annotation scanner
        'ignore_annotations' => [
            ArrayShape::class,
        ],
    ],
];
```

## Usage of Annotation

Ada tiga tipe penerapan dari annotation, yaitu pada `class`, `method of class`,
dan `property of class`.

### Use class level annotation

Definisi annotation tingkat class berada di blok komentar di atas keyword
`class`. Sebagai contoh, `Controller` dan `AutoController` yang umum digunakan
adalah contoh penggunaan dari class level annotation. Contoh kode berikut adalah
contoh penggunaan class level annotation yang benar, yang menunjukkan bahwa
annotation `ClassAnnotation` diterapkan pada class `Foo`.

```php
<?php
#[ClassAnnotation]
class Foo {}
```

### Use method level annotation

Definisi annotation tingkat method berada di blok komentar di atas method class.
Sebagai contoh, `RequestMapping` yang umum digunakan adalah contoh penggunaan dari
method level annotation. Contoh kode berikut adalah contoh penggunaan method
level annotation yang benar, yang menunjukkan bahwa annotation `MethodAnnotation`
diterapkan pada method `bar` dari class `Foo`.

```php
<?php
class Foo
{
    #[MethodAnnotation]
    public function bar()
    {
        // some code
    }
}
```

### Use property level of annotation

Definisi annotation tingkat property berada di blok komentar di atas property.
Sebagai contoh, `Value` dan `Inject` yang sering digunakan adalah contoh
penggunaan dari property level annotation. Contoh kode berikut adalah contoh
penggunaan property level annotation yang benar, yang menunjukkan bahwa
annotation `PropertyAnnotation` diterapkan pada property `$bar` dari class
`Foo`.

```php
<?php
class Foo
{
    #[PropertyAnnotation]
    private $bar;
}
```

### The passing of annotation parameter

- Melewatkan parameter tunggal utama: `#[DemoAnnotation('value')]`
- Melewatkan parameter string: `#[DemoAnnotation(key1: 'value1', key2: 'value2')]`
- Melewatkan parameter array: `#[DemoAnnotation(key: ['value1', 'value2'])]`

## Custom Annotation

### Create an Annotation class

Buat class annotation di mana saja, seperti pada contoh kode berikut:

```php
<?php
namespace App\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Bar extends AbstractAnnotation
{
    // some code
}

#[Attribute(Attribute::TARGET_CLASS)]
class Foo extends AbstractAnnotation
{
    // some code
}
```

Perlu dicatat bahwa pada contoh kode di atas, class annotation mewarisi
abstract class `Hyperf\Di\Annotation\AbstractAnnotation`. Ini tidak wajib untuk
class annotation, tetapi untuk class annotation Hyperf, wajib untuk mengimplementasi
interface `Hyperf\Di\Annotation\AnnotationInterface`, sehingga peran dari abstract
class di sini adalah untuk menyediakan definisi minimal. Abstract class tersebut
telah diimplementasikan untuk Anda agar dapat `secara otomatis menetapkan
parameter annotation ke property class`, dan `secara otomatis mengumpulkan data
annotation ke AnnotationCollector`.

### Custom Annotation Collector

Alur eksekusi spesifik dari pengumpulan annotation juga diimplementasikan di
dalam class annotation. Method terkait dibatasi oleh
`Hyperf\Di\Annotation\AnnotationInterface`. Interface tersebut memerlukan
implementasi dari tiga method berikut, dan Anda dapat mengimplementasikan logika
yang sesuai berdasarkan kebutuhan Anda sendiri:

- `public function collectClass(string $className): void;` Method ini akan
  dijalankan ketika annotation didefinisikan pada class.
- `public function collectMethod(string $className, ?string $target): void;`
  Method ini akan dijalankan ketika annotation didefinisikan pada method.
- `public function collectProperty(string $className, ?string $target): void`
  Method ini akan dijalankan ketika annotation didefinisikan pada property.

### Usage of annotation data

Ketika tidak ada method pengumpulan annotation kustom, metadata annotation akan
dikumpulkan di class `Hyperf\Di\Annotation\AnnotationCollector` secara default.
Static method dari class tersebut dapat dengan mudah memperoleh metadata yang
sesuai untuk penilaian logika atau implementasi.

## IDE Plugin of Annotation

Karena `PHP` tidak mendukung `annotation` secara native (secara bawaan), `IDE`
tidak menambahkan dukungan fitur annotation secara default. Namun kita dapat
menambahkan plugin pihak ketiga agar `IDE` mendukung fitur `annotation`.

### PhpStorm

Kita dapat mencari `PHP Annotations` di bagian `Plugins` dan menemukan komponen
yang sesuai [PHP Annotations](https://github.com/Haehnchen/idea-php-annotation-plugin).
Kemudian instal plugin tersebut, restart `PhpStorm`, dan Anda dapat menggunakan
fitur annotation dengan nyaman. Plugin ini terutama menyediakan fitur untuk
menambahkan dukungan lompatan otomatis (jump) dan pengingat kode (code reminder)
untuk class annotation, serta secara otomatis merujuk ke namespace yang sesuai
saat annotation digunakan.
