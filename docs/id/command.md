# Command

Komponen command default pada Hyperf disediakan oleh komponen
[hyperf/command](https://github.com/hyperf/command), dan komponen ini merupakan
abstraksi dari [symfony/console](https://github.com/symfony/console).

# Instalasi

Komponen ini biasanya sudah ada secara default, tetapi jika Anda ingin
menggunakannya pada proyek non-Hyperf, Anda juga dapat menggunakan komponen
[hyperf/command](https://github.com/hyperf/command) dengan command berikut:

```bash
composer require hyperf/command
```

# Daftar Command

Menjalankan `php bin/hyperf.php` secara langsung tanpa argument apa pun akan
menampilkan daftar command.

# Custom Command

## Membuat Command

Jika Anda telah menginstal komponen
[hyperf/devtool](https://github.com/hyperf/devtool), Anda dapat membuat custom
command dengan command `gen:command`:

```bash
php bin/hyperf.php gen:command FooCommand
```
Setelah menjalankan command di atas, class `FooCommand` yang telah
dikonfigurasi akan dibuat di dalam folder `app/Command`.

### Pendefinisian Command

Ada tiga cara untuk mendefinisikan class command. Pertama ditentukan melalui
properti `$name`, kedua ditentukan melalui argument constructor, dan yang
terakhir ditentukan menggunakan annotation. Kami akan mendemonstrasikannya
melalui contoh kode, dengan asumsi kita ingin mendefinisikan command dengan nama
`foo:hello`:

#### Mendefinisikan command melalui properti `$name`:

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
     * The command
     *
     * @var string
     */
    protected ?string $name = 'foo:hello';
}
```

#### Mendefinisikan command melalui constructor:

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

#### Mendefinisikan command melalui annotation:

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

### Mendefinisikan Logika Command

Logika yang dijalankan oleh class command bergantung pada method `handle` di
dalam kode, yang berarti bahwa method `handle` merupakan entry point untuk
command tersebut.

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
     * The command
     *
     * @var string
     */
    protected ?string $name = 'foo:hello';
    
    public function handle()
    {
        // Output Hello Hyperf. in the Console via the built-in method line()
        $this->line('Hello Hyperf.', 'info');
    }
}
```

### Mendefinisikan Argument Command

Saat menulis command, input dari pengguna biasanya dikumpulkan melalui
`parameter` dan `option`, dan `parameter` atau `option` tersebut harus
didefinisikan terlebih dahulu sebelum mengumpulkan input pengguna.

#### Parameter

Misalkan kita ingin mendefinisikan parameter `name`, lalu mengirimkan string
sembarang seperti `Hyperf` ke command dan menjalankan
`php bin/hyperf.php foo:hello Hyperf` untuk menghasilkan output
`Hello Hyperf`. Mari kita tunjukkan melalui kode:

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
     * The command
     *
     * @var string
     */
    protected ?string $name = 'foo:hello';

    public function handle()
    {
        // Get the name argument from $input
        $argument = $this->input->getArgument('name') ?? 'World';
        $this->line('Hello ' . $argument, 'info');
    }
    
    protected function getArguments()
    {
        return [
            ['name', InputArgument::OPTIONAL, 'Here is an explanation of this parameter']
        ];
    }
}
``` 

Jalankan command `php bin/hyperf.php foo:hello Hyperf` dan kita akan melihat
`Hello Hyperf` ditampilkan pada Console.

## Konfigurasi Umum

Kode berikut hanya mengubah bagian isi dari `configure` dan `handle`.

### Mengatur Help

```php
public function configure()
{
    parent::configure();
    $this->setHelp('Hyperf\'s custom command demonstration');
}
```
```bash
$ php bin/hyperf.php demo:command --help
# output
...
Help:
  Hyperf's custom command demonstration

```

### Mengatur Deskripsi

```php
public function configure()
{
    parent::configure();
    $this->setDescription('Hyperf Demo Command');
}
```
```bash
$ php bin/hyperf.php demo:command --help
# output
...
Description:
  Hyperf Demo Command

```

### Mengatur Penggunaan (Usage)

```php
public function configure()
{
    parent::configure();
    $this->addUsage('--name Demo Code');
}
```
```bash
$ php bin/hyperf.php demo:command --help
# output
...
Usage:
  demo:command
  demo:command --name Demo Code
```

### Mengatur Parameter

Parameter mendukung mode-mode berikut.

|          Mode           | Value |                Catatan              |
|:-----------------------:|:--:|:-----------------------------------:|
| InputArgument::REQUIRED | 1  | Parameter wajib diisi, field "default" pada mode ini tidak berlaku. |
| InputArgument::OPTIONAL | 2  |    Parameter bersifat opsional dan sering digunakan dengan nilai default |
| InputArgument::IS_ARRAY | 4  |              Tipe array               |

#### Tipe Opsional (Optional)

```php
public function configure()
{
    parent::configure();
    $this->addArgument('name', InputArgument::OPTIONAL, 'name', 'Hyperf');
}

public function handle()
{
    $this->line($this->input->getArgument('name'));
}
```
```bash
$ php bin/hyperf.php demo:command
# output
...
Hyperf

$ php bin/hyperf.php demo:command Swoole
# output
...
Swoole
```

#### Tipe Array

```php
public function configure()
{
    parent::configure();
    $this->addArgument('name', InputArgument::IS_ARRAY, 'name');
}

public function handle()
{
    var_dump($this->input->getArgument('name'));
}
```
```bash
$ php bin/hyperf.php demo:command Hyperf Swoole
# output
...
array(2) {
  [0]=>
  string(6) "Hyperf"
  [1]=>
  string(6) "Swoole"
}
```

### Mengatur Option

Option mendukung mode-mode berikut.

|            Mode             | Value |     Catatan  |
|:---------------------------:|:--:|:------------:|
|   InputOption::VALUE_NONE   | 1  | Parameter wajib diisi, field "default" pada mode ini tidak berlaku |
| InputOption::VALUE_REQUIRED | 2  |   Option wajib diisi   |
| InputOption::VALUE_OPTIONAL | 4  |   Option bersifat opsional   |
| InputOption::VALUE_IS_ARRAY | 8  |   Option berupa array   |

#### Apakah mengirimkan option atau tidak

```php
public function configure()
{
    parent::configure();
    $this->addOption('opt', 'o', InputOption::VALUE_NONE, 'Whether to optimize');
}

public function handle()
{
    var_dump($this->input->getOption('opt'));
}
```
```bash
$ php bin/hyperf.php demo:command
# output
bool(false)

$ php bin/hyperf.php demo:command -o
# output
bool(true)

$ php bin/hyperf.php demo:command --opt
# output
bool(true)
```

### Option Wajib dan Opsional

`VALUE_OPTIONAL` tidak berbeda dengan `VALUE_REQUIRED` jika digunakan secara
mandiri.

```php
public function configure()
{
    parent::configure();
    $this->addOption('name', 'N', InputOption::VALUE_REQUIRED, 'name', 'Hyperf');
}

public function handle()
{
    var_dump($this->input->getOption('name'));
}
```
```bash
$ php bin/hyperf.php demo:command
# output
string(6) "Hyperf"

$ php bin/hyperf.php demo:command --name Swoole
# output
string(6) "Swoole"
```

### Option Array

`VALUE_IS_ARRAY` dan `VALUE_OPTIONAL`, ketika digunakan bersama-sama, dapat
memberikan efek pengiriman beberapa `Option` sekaligus.

```php
public function configure()
{
    parent::configure();
    $this->addOption('name', 'N', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'name');
}

public function handle()
{
    var_dump($this->input->getOption('name'));
}
```
```bash
$ php bin/hyperf.php demo:command
# output
array(0) {
}

$ php bin/hyperf.php demo:command --name Hyperf --name Swoole
# output
array(2) {
  [0]=>
  string(6) "Hyperf"
  [1]=>
  string(6) "Swoole"
}

```

## Mengonfigurasi Command melalui `$signature`

Selain metode konfigurasi di atas, command line juga mendukung konfigurasi
menggunakan `$signature`.

`$signature` adalah sebuah string, yang dibagi menjadi tiga bagian: `command`,
`argument`, dan `option`, seperti berikut:

```
command:name {argument?* : The argument description.} {--option=* : The option description.}
```

- `?` mewakili `optional`.
- `*` mewakili `array`.
- `?*` mewakili `optional array`.
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

!> Catatan: Secara default, menjalankan command akan memicu event dispatching.
Anda dapat menonaktifkannya dengan menambahkan parameter
`--disable-event-dispatcher`.

## Menjalankan di Command Line

```bash
php bin/hyperf.php foo
```

## Menjalankan Command Lain di dalam Command

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

// You can choose the input/output according to your own needs.
$input = new ArrayInput($params);
$output = new NullOutput();

/** @var \Psr\Container\ContainerInterface $container */
$container = \Hyperf\Context\ApplicationContext::getContainer();

/** @var \Symfony\Component\Console\Application $application */
$application = $container->get(\Hyperf\Contract\ApplicationInterface::class);
$application->setAutoExit(false);

// This method: will not expose exceptions during command execution and will not prevent the program from returning.
$exitCode = $application->run($input, $output);

// Another way: it will expose exceptions and require you to catch and handle runtime exceptions yourself, otherwise it will prevent the program from returning.
$exitCode = $application->find($command)->run($input, $output);
```

## Closure Command

Anda dapat mendefinisikan command dengan cepat di dalam `config\console.php`.

```php
use Hyperf\Command\Console;

Console::command('hello', function () {
    $this->comment('Hello, Hyperf!');
})->describe('This is a demo closure command.');
```

Anda juga dapat mengatur crontab untuk closure command.

```php
use Hyperf\Command\Console;

Console::command('foo', function () {
    $this->comment('Hello, Foo!');
})->describe('This is a demo closure command.')->cron('* * * * *');

Console::command('bar', function () {
    $this->comment('Hello, Bar!');
})->describe('This is another demo closure command.')->cron('* * * * *', callback: fn($cron) => $cron->setSingleton(true));
```

## AsCommand

Anda dapat mengubah sebuah class menjadi command dengan memberikan annotation
`AsCommand` pada class tersebut.

```php
<?php

namespace App\Service;

use Hyperf\Command\Annotation\AsCommand;
use Hyperf\Command\Concerns\InteractsWithIO;

#[AsCommand(signature: 'foo:bar1', handle: 'bar1', description: 'The description of foo:bar1 command.')]
#[AsCommand(signature: 'foo', description: 'The description of foo command.')]
class FooService
{
    use InteractsWithIO;

    #[AsCommand(signature: 'foo:bar {--bar=1 : Bar Value}', description: 'The description of foo:bar command.')]
    public function bar($bar)
    {
        $this->output?->info('Bar Value: ' . $bar);

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
  foo:bar                   The description of foo:bar command.
  foo:bar1                  The description of foo:bar1 command.
```
