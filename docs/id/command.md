# Command

Fitur command line Hyperf disediakan oleh komponen [hyperf/command](https://github.com/hyperf/command) secara default, dan komponen ini sendiri didasarkan pada abstraksi dari [symfony/console](https://github.com/symfony/console).

# Instalasi

Umumnya, komponen ini sudah ada secara default, tetapi jika Anda ingin menggunakannya untuk project non-Hyperf, Anda juga dapat menambahkan dependency pada komponen [hyperf/command](https://github.com/hyperf/command) dengan perintah berikut:

```bash
composer require hyperf/command
```

# Melihat Daftar Command

Jalankan `php bin/hyperf.php` langsung tanpa argumen apa pun untuk menampilkan daftar command.

# Custom Command

## Membuat Command

Jika Anda telah menginstal komponen [hyperf/devtool](https://github.com/hyperf/devtool), Anda dapat menggunakan perintah `gen:command` untuk membuat custom command:

```bash
php bin/hyperf.php gen:command FooCommand
```
Setelah menjalankan perintah di atas, kelas `FooCommand` yang telah dikonfigurasi akan dibuat di folder `app/Command`.

### Mendefinisikan Command

Ada tiga cara untuk mendefinisikan command yang sesuai dengan kelas command ini. Yang pertama didefinisikan melalui properti `$name`, yang kedua didefinisikan dengan melewatkan parameter ke konstruktor, dan yang terakhir didefinisikan oleh annotation. Kami akan mendemonstrasikannya dengan contoh kode. Misalkan kita ingin mendefinisikan command dari kelas command ini sebagai `foo:hello`:

#### Definisi melalui Properti `$name`:

```php
<?php

declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;

#[Command]
class FooCommand extends HyperfCommand
{
    /**
     * Command yang dijalankan
     */
    protected ?string $name = 'foo:hello';
}
```

#### Definisi melalui Argumen Konstruktor:

```php
<?php

declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;

#[Command]
class FooCommand extends HyperfCommand
{
    public function __construct()
    {
        parent::__construct('foo:hello');
    }
}
```

#### Definisi melalui Annotation:

```php
<?php

declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;

#[Command(name: "foo:hello")]
class FooCommand extends HyperfCommand
{
}
```

### Mendefinisikan Logika Kelas Command

Logika sebenarnya untuk menjalankan kelas command bergantung pada kode di dalam metode `handle`, yang berarti metode `handle` adalah titik masuk untuk command.

```php
<?php

declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;

#[Command]
class FooCommand extends HyperfCommand
{
    /**
     * Command yang dijalankan
     */
    protected ?string $name = 'foo:hello';

    public function handle()
    {
        // Mengeluarkan 'Hello Hyperf.' di Console menggunakan metode line bawaan.
        $this->line('Hello Hyperf.', 'info');
    }
}
```

### Mendefinisikan Parameter Kelas Command

Saat menulis command, input pengguna biasanya dikumpulkan melalui `arguments` dan `options`. Sebelum mengumpulkan input pengguna, Anda harus mendefinisikan `argument` atau `option`.

#### Argument

Misalkan kita ingin mendefinisikan argument `name`, dan kemudian melewatkan string apa pun seperti `Hyperf` ke command dan menjalankan `php bin/hyperf.php foo:hello Hyperf` untuk menghasilkan output `Hello Hyperf`. Mari kita demonstrasikan dengan kode:

```php
<?php

declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Symfony\Component\Console\Input\InputArgument;

#[Command]
class FooCommand extends HyperfCommand
{
    /**
     * Command yang dijalankan
     */
    protected ?string $name = 'foo:hello';

    public function handle()
    {
        // Mendapatkan argument name dari $input
        $argument = $this->input->getArgument('name') ?? 'World';
        $this->line('Hello ' . $argument, 'info');
    }

    protected function getArguments()
    {
        return [
            ['name', InputArgument::OPTIONAL, 'Ini adalah penjelasan dari argument ini']
        ];
    }
}
```

Jalankan `php bin/hyperf.php foo:hello Hyperf`, dan kita akan melihat output `Hello Hyperf`.

## Pengenalan Konfigurasi Command Umum

Kode berikut hanya memodifikasi konten di `configure` dan `handle`.

### Mengatur Help

```php
public function configure()
{
    parent::configure();
    $this->setHelp('Demonstrasi custom command Hyperf');
}

```
```bash
$ php bin/hyperf.php demo:command --help
# Output
...
Help:
  Demonstrasi custom command Hyperf
```


### Mengatur Description

```php
public function configure()
{
    parent::configure();
    $this->setDescription('Hyperf Demo Command');
}
```
```bash
$ php bin/hyperf.php demo:command --help
# Output
...
Description:
  Hyperf Demo Command

```

### Mengatur Usage

```php
public function configure()
{
    parent::configure();
    $this->addUsage('--name demonstration code');
}
```
```bash
$ php bin/hyperf.php demo:command --help
# Output
...
Usage:
  demo:command
  demo:command --name demonstration code
```

### Mengatur Arguments

Arguments mendukung mode berikut.

| Mode | Nilai | Catatan |
|:---:|:---:|:---:|
| InputArgument::REQUIRED | 1 | Argument wajib diisi, field default tidak berlaku dalam mode ini |
| InputArgument::OPTIONAL | 2 | Argument opsional, sering digunakan bersama default |
| InputArgument::IS_ARRAY | 4 | Tipe array |

#### Tipe Opsional

```php
public function configure()
{
    parent::configure();
    $this->addArgument('name', InputArgument::OPTIONAL, 'Nama', 'Hyperf');
}

public function handle()
{
    $this->line($this->input->getArgument('name'));
}
```
```bash
$ php bin/hyperf.php demo:command
# Output
...
Hyperf

$ php bin/hyperf.php demo:command Swoole
# Output
...
Swoole
```

#### Tipe Array

```php
public function configure()
{
    parent::configure();
    $this->addArgument('name', InputArgument::IS_ARRAY, 'Nama');
}

public function handle()
{
    var_dump($this->input->getArgument('name'));
}
```
```bash
$ php bin/hyperf.php demo:command Hyperf Swoole
# Output
...
array(2) {
  [0]=>
  string(6) "Hyperf"
  [1]=>
  string(6) "Swoole"
}
```

### Mengatur Options

Options mendukung mode berikut.

| Mode | Nilai | Catatan |
|:---:|:---:|:---:|
| InputOption::VALUE_NONE | 1 | Apakah nilai opsional dilewatkan, field default tidak berlaku |
| InputOption::VALUE_REQUIRED | 2 | Option wajib diisi |
| InputOption::VALUE_OPTIONAL | 4 | Option opsional |
| InputOption::VALUE_IS_ARRAY | 8 | Array option |

#### Apakah Nilai Opsional Dilewatkan

```php
public function configure()
{
    parent::configure();
    $this->addOption('opt', 'o', InputOption::VALUE_NONE, 'Apakah akan optimasi');
}

public function handle()
{
    var_dump($this->input->getOption('opt'));
}
```
```bash
$ php bin/hyperf.php demo:command
# Output
bool(false)

$ php bin/hyperf.php demo:command -o
# Output
bool(true)

$ php bin/hyperf.php demo:command --opt
# Output
bool(true)
```

### Option Wajib dan Opsional

`VALUE_OPTIONAL` identik dengan `VALUE_REQUIRED` saat digunakan sendiri.

```php
public function configure()
{
    parent::configure();
    $this->addOption('name', 'N', InputOption::VALUE_REQUIRED, 'Nama', 'Hyperf');
}

public function handle()
{
    var_dump($this->input->getOption('name'));
}
```
```bash
$ php bin/hyperf.php demo:command
# Output
string(6) "Hyperf"

$ php bin/hyperf.php demo:command --name Swoole
# Output
string(6) "Swoole"
```

### Array Option

`VALUE_IS_ARRAY` dan `VALUE_OPTIONAL` yang digunakan bersama dapat mencapai efek memasukkan beberapa `Options`.

```php
public function configure()
{
    parent::configure();
    $this->addOption('name', 'N', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Nama');
}

public function handle()
{
    var_dump($this->input->getOption('name'));
}
```
```bash
$ php bin/hyperf.php demo:command
# Output
array(0) {
}

$ php bin/hyperf.php demo:command --name Hyperf --name Swoole
# Output
array(2) {
  [0]=>
  string(6) "Hyperf"
  [1]=>
  string(6) "Swoole"
}
```

## Mengonfigurasi Command Line melalui `$signature`

Selain metode konfigurasi yang disebutkan di atas, command line juga mendukung konfigurasi menggunakan `$signature`.

`$signature` adalah string yang terdiri dari tiga bagian: `command`, `argument`, dan `option`, sebagai berikut:

```
command:name {argument?* : Deskripsi argument.} {--option=* : Deskripsi option.}
```

- `?` mewakili `tidak wajib`.
- `*` mewakili `array`.
- `?*` mewakili `array tidak wajib`.
- `=` mewakili `non-Bool`.

### Contoh

```php
<?php

declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;

#[Command]
class DebugCommand extends HyperfCommand
{
    protected ContainerInterface $container;

    protected ?string $signature = 'test:test {id : user_id} {--name= : user_name}';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct();
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Hyperf Demo Command');
    }

    public function handle()
    {
        var_dump($this->input->getArguments());
        var_dump($this->input->getOptions());
    }
}
```

# Menjalankan Command

!> Catatan: Saat menjalankan command, distribusi event dipicu secara default. Anda dapat menonaktifkannya dengan menambahkan argumen `--disable-event-dispatcher`.

## Menjalankan di Command Line

```bash
php bin/hyperf.php foo
```

## Menjalankan Command Lain di Dalam Command

```php
<?php

declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;

#[Command]
class FooCommand extends HyperfCommand
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('foo');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('foo command');
    }

    public function handle()
    {
        $this->call('bar', [
            '--foo' => 'foo'
        ]);
    }
}
```

## Menjalankan Command di Luar Command

```php
$command = 'foo';

$params = ["command" => $command, "--foo" => "foo", "--bar" => "bar"];

// Anda dapat memilih input/output yang akan digunakan sesuai kebutuhan
$input = new ArrayInput($params);
$output = new NullOutput();

/** @var \Psr\Container\ContainerInterface $container */
$container = \Hyperf\Context\ApplicationContext::getContainer();

/** @var \Symfony\Component\Console\Application $application */
$application = $container->get(\Hyperf\Contract\ApplicationInterface::class);
$application->setAutoExit(false);

// Cara ini: tidak akan mengekspos exception selama eksekusi command, tidak akan mencegah program dari pengembalian
$exitCode = $application->run($input, $output);

// Cara kedua: akan mengekspos exception, Anda perlu menangkap dan menangani exception yang berjalan sendiri, jika tidak maka akan mencegah program dari pengembalian
$exitCode = $application->find($command)->run($input, $output);
```

## Closure Command

Anda dapat dengan cepat mendefinisikan command di `config\console.php`.

```php
use Hyperf\Command\Console;

Console::command('hello', function () {
    $this->comment('Hello, Hyperf!');
})->describe('Ini adalah demo closure command.');
```

Mendefinisikan tugas terjadwal untuk closure command.

```php
use Hyperf\Command\Console;

Console::command('foo', function () {
    $this->comment('Hello, Foo!');
})->describe('Ini adalah demo closure command.')->cron('* * * * *');

Console::command('bar', function () {
    $this->comment('Hello, Bar!');
})->describe('Ini adalah demo closure command lainnya.')->cron('* * * * *', callback: fn($cron) => $cron->setSingleton(true));
```

## AsCommand

Anda dapat mengubah sebuah kelas menjadi command melalui annotation `AsCommand`.

```php
<?php

namespace App\Service;

use Hyperf\Command\Annotation\AsCommand;
use Hyperf\Command\Concerns\InteractsWithIO;

#[AsCommand(signature: 'foo:bar1', handle: 'bar1', description: 'Deskripsi dari perintah foo:bar1.')]
#[AsCommand(signature: 'foo', description: 'Deskripsi dari perintah foo.')]
class FooService
{
    use InteractsWithIO;

    #[AsCommand(signature: 'foo:bar {--bar=1 : Nilai Bar}', description: 'Deskripsi dari perintah foo:bar.')]
    public function bar($bar)
    {
        $this->output?->info('Nilai Bar: ' . $bar);

        return $bar;
    }

    public function bar1()
    {
        $this->output?->info(__METHOD__);
    }

    public function handle()
    {
        $this->output?->info(__METHOD__);
    }
}
```

```shell
$ php bin/hyperf.php

...
foo
  foo:bar                   Deskripsi dari perintah foo:bar.
  foo:bar1                  Deskripsi dari perintah foo:bar1.
```
