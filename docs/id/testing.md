# Automated Testing

Di Hyperf, pengujian dilakukan melalui `phpunit` secara default, dan mulai dari versi 3.1, framework `pest` yang berbasis phpunit juga didukung [Dokumentasi](https://pestphp.com/docs/installation).


```shell
composer require hyperf/testing --dev
composer require pestphp/pest --dev
```

```json
"scripts": {
    "pest": "pest --colors=always",
    "test": "co-phpunit -c phpunit.xml --colors=always"
},
```

| package         | version |
| --------------- | ------- |
| phpunit/phpunit | ^10.1   |
| pestphp/pest    | ^2.8  |

## Bootstrap

Hyperf menyediakan file `bootstrap.php` default, yang memungkinkan pengguna untuk memindai dan memuat library yang sesuai ke dalam memori saat menjalankan unit test.

```php
<?php

declare(strict_types=1);

error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');

! defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__, 1));
! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL);

// Diaktifkan secara default. Harus dikomentari saat menggunakan fitur pest --parallel atau operasi paralel native lainnya.
Swoole\Runtime::enableCoroutine(true);

require BASE_PATH . '/vendor/autoload.php';

Hyperf\Di\ClassLoader::init();

$container = require BASE_PATH . '/config/container.php';

$container->get(Hyperf\Contract\ApplicationInterface::class);

```

Menjalankan unit test

```
composer test
```

## Catatan

- `hyperf/testing` menyediakan Trait [RunTestsInCoroutine](https://github.com/hyperf/hyperf/blob/master/src/testing/src/Concerns/RunTestsInCoroutine.php). Gunakan kelas ini pada kasus `Test` tertentu untuk mengaktifkan lingkungan coroutine.
- Saat menggunakan fitur `--parallel` di pest, `Swoole\Runtime::enableCoroutine(true)` di `test/bootstrap.php` perlu dikomentari.

## Mocking HTTP Requests

Saat mengembangkan antarmuka, kita biasanya membutuhkan script test otomatis untuk memastikan bahwa antarmuka yang kita sediakan berjalan seperti yang diharapkan. Framework Hyperf menyediakan kelas `Hyperf\Testing\Client`, yang memungkinkan Anda untuk mensimulasikan request layanan HTTP tanpa harus menjalankan Server:

```php
<?php
use Hyperf\Testing\Client;

$client = make(Client::class);

$result = $client->get('/');
```

Karena Hyperf mendukung konfigurasi multi-port, selain memverifikasi antarmuka port default, bagaimana jika kita ingin memverifikasi antarmuka di port lain?

```php
<?php

use Hyperf\Testing\Client;

$client = make(Client::class, ['server' => 'adminHttp']);

$result = $client->json('/user/0',[
    'nickname' => 'Hyperf'
]);

```

Secara default, framework menggunakan `JsonPacker`, yang akan langsung mem-parse `Body` menjadi `array`. Jika Anda mengembalikan `string` secara langsung, Anda perlu mengatur `Packer` yang sesuai.

```php
<?php

use Hyperf\Testing\Client;
use Hyperf\Contract\PackerInterface;

$client = make(Client::class, [
    'packer' => new class() implements PackerInterface {
        public function pack($data): string
        {
            return $data;
        }

        public function unpack(string $data)
        {
            return $data;
        }
    },
]);

$result = $client->json('/user/0', [
    'nickname' => 'Hyperf'
]);
```

### Menggunakan Cookies

```php
<?php

use Hyperf\Testing\Client;
use Hyperf\Codec\Json;

$client = make(Client::class);

$response = $client->sendRequest($client->initRequest('POST', '/request')->withCookieParams([
    'X-CODE' => $id = uniqid(),
]));

$data = Json::decode((string) $response->getBody());
```

## Contoh

Mari kita tulis DEMO kecil untuk mengujinya.

```php
<?php

declare(strict_types=1);

namespace HyperfTest\Cases;

use Hyperf\Testing\Client;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ExampleTest extends TestCase
{
    protected Client $client;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->client = make(Client::class);
    }

    public function testExample()
    {
        $this->assertTrue(true);

        $res = $this->client->get('/');

        $this->assertSame(0, $res['code']);
        $this->assertSame('Hello Hyperf.', $res['data']['message']);
        $this->assertSame('GET', $res['data']['method']);
        $this->assertSame('Hyperf', $res['data']['user']);

        $res = $this->client->get('/', ['user' => 'developer']);

        $this->assertSame(0, $res['code']);
        $this->assertSame('developer', $res['data']['user']);

        $res = $this->client->post('/', [
            'user' => 'developer',
        ]);
        $this->assertSame('Hello Hyperf.', $res['data']['message']);
        $this->assertSame('POST', $res['data']['method']);
        $this->assertSame('developer', $res['data']['user']);

        $res = $this->client->json('/', [
            'user' => 'developer',
        ]);
        $this->assertSame('Hello Hyperf.', $res['data']['message']);
        $this->assertSame('POST', $res['data']['method']);
        $this->assertSame('developer', $res['data']['user']);

        $res = $this->client->file('/', ['name' => 'file', 'file' => BASE_PATH . '/README.md']);

        $this->assertSame('Hello Hyperf.', $res['data']['message']);
        $this->assertSame('POST', $res['data']['method']);
        $this->assertSame('README.md', $res['data']['file']);
    }
}
```

## Debugging Kode

Dalam skenario FPM, kita biasanya memodifikasi kode dan kemudian membuka browser untuk mengakses antarmuka yang sesuai, sehingga kita biasanya membutuhkan dua fungsi, `dd` dan `dump`. Namun Hyperf berjalan dalam mode `CLI`. Bahkan jika kedua fungsi ini disediakan, kita perlu me-restart `Server` di `CLI`, dan kemudian memanggil antarmuka yang sesuai di browser untuk melihat hasilnya. Ini sebenarnya tidak menyederhanakan proses, tetapi membuatnya lebih merepotkan.

Selanjutnya, saya akan memperkenalkan cara melakukan debugging kode dengan cepat dengan bekerja sama dengan `testing` dan menyelesaikan unit testing sebagai bonus.

Misalkan kita mengimplementasikan fungsi untuk mengambil informasi pengguna di `UserDao`:
```php
namespace App\Service\Dao;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\User;

class UserDao extends Dao
{
    /**
     * @param $id
     * @param bool $throw
     * @return
     */
    public function first($id, $throw = true)
    {
        $model = User::query()->find($id);
        if ($throw && empty($model)) {
            throw new BusinessException(ErrorCode::USRE_NOT_EXIST);
        }
        return $model;
    }
}
```

Kemudian kita menulis unit test yang sesuai:

```php
namespace HyperfTest\Cases;

use HyperfTest\HttpTestCase;
use App\Service\Dao\UserDao;
/**
 * @internal
 * @coversNothing
 */
class UserTest extends HttpTestCase
{
    public function testUserDaoFirst()
    {
        $model = \Hyperf\Context\ApplicationContext::getContainer()->get(UserDao::class)->first(1);

        var_dump($model);

        $this->assertSame(1, $model->id);
    }
}
```

Kemudian jalankan unit test kita:

```
composer test -- --filter=testUserDaoFirst
```

## Test Doubles

`Gerard Meszaros` memperkenalkan konsep test doubles di `Meszaros2007`:

Terkadang sulit untuk menguji `System Under Test (SUT)` karena ia bergantung pada komponen lain yang tidak dapat digunakan di lingkungan test. Ini mungkin karena komponen-komponen tersebut tidak tersedia, mereka tidak mengembalikan hasil yang diperlukan untuk test, atau mengeksekusinya memiliki efek samping yang merugikan. Dalam kasus lain, strategi pengujian kita membutuhkan lebih banyak kontrol atau lebih banyak visibilitas ke dalam perilaku internal dari sistem yang diuji.

Jika Anda tidak dapat (atau memilih untuk tidak) menggunakan komponen dependen yang sebenarnya (DOC) saat menulis test, Anda dapat menggunakan test double sebagai pengganti. Test double tidak perlu berperilaku persis seperti komponen dependen yang sebenarnya; ia hanya perlu menyediakan API yang sama dengan komponen asli, sehingga sistem yang diuji akan mengira itu adalah komponen yang sebenarnya!

Berikut menunjukkan test doubles yang diinjeksi melalui konstruktor dan melalui annotation `#[Inject]`.

### Test Double dengan Dependency Diinjeksi melalui Konstruktor

```php
<?php

namespace App\Logic;

use App\Api\DemoApi;

class DemoLogic
{
    private DemoApi $demoApi;

    public function __construct(DemoApi $demoApi)
    {
       $this->demoApi = $demoApi;
    }

    public function test()
    {
        $result = $this->demoApi->test();

        return $result;
    }
}
```

```php
<?php

namespace App\Api;

class DemoApi
{
    public function test()
    {
        return [
            'status' => 1
        ];
    }
}
```

```php
<?php

namespace HyperfTest\Cases;

use App\Api\DemoApi;
use App\Logic\DemoLogic;
use Hyperf\Di\Container;
use HyperfTest\HttpTestCase;
use Mockery;

class DemoLogicTest extends HttpTestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testIndex()
    {
        $res = $this->getContainer()->get(DemoLogic::class)->test();

        $this->assertEquals(1, $res['status']);
    }

    /**
     * @return Container
     */
    protected function getContainer()
    {
        $container = Mockery::mock(Container::class);

        $apiStub = $this->createMock(DemoApi::class);

        $apiStub->method('test')->willReturn([
            'status' => 1,
        ]);

        $container->shouldReceive('get')->with(DemoLogic::class)->andReturn(new DemoLogic($apiStub));

        return $container;
    }
}
```

### Test Double dengan Dependency Diinjeksi melalui Annotation `Inject`

```php
<?php

namespace App\Logic;

use App\Api\DemoApi;
use Hyperf\Di\Annotation\Inject;

class DemoLogic
{
    #[Inject]
    private DemoApi $demoApi;

    public function test()
    {
        $result = $this->demoApi->test();

        return $result;
    }
}
```

```php
<?php

namespace App\Api;

class DemoApi
{
    public function test()
    {
        return [
            'status' => 1
        ];
    }
}
```

```php
<?php

namespace HyperfTest\Cases;

use App\Api\DemoApi;
use App\Logic\DemoLogic;
use Hyperf\Di\Container;
use Hyperf\Context\ApplicationContext;
use HyperfTest\HttpTestCase;
use Mockery;

class DemoLogicTest extends HttpTestCase
{
    /**
     * @after
     */
    public function tearDownAfterMethod()
    {
        Mockery::close();
    }

    public function testIndex()
    {
        $this->getContainer();

        $res = $this->getContainer()->get(DemoLogic::class)->test();

        $this->assertEquals(11, $res['status']);
    }

    /**
     * @return Container
     */
    protected function getContainer()
    {
        $container = ApplicationContext::getContainer();

        $apiStub = $this->createMock(DemoApi::class);

        $apiStub->method('test')->willReturn([
            'status' => 11
        ]);

        $container->define(DemoApi::class, function () use ($apiStub) {
            return $apiStub;
        });
        
        return $container;
    }
}
```

# Unit Test Coverage

## Menggunakan phpdbg untuk Menghasilkan Unit Test Coverage

Ubah file `phpunit.xml` sebagai berikut:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit backupGlobals="false"
         backupStaticAttributes="false"
         bootstrap="./test/bootstrap.php"
         colors="true"
         convertErrorsToExceptions="true"
         convertNoticesToExceptions="true"
         convertWarningsToExceptions="true"
         processIsolation="false"
         stopOnFailure="false">
    <php>
        <!-- Pengaturan PHP.ini atau variabel lingkungan lainnya -->
        <ini name="memory_limit" value="-1" />
    </php>
    <testsuites>
        <testsuite name="Tests">
            // Direktori untuk kasus test yang perlu dijalankan
            <directory suffix="Test.php">./test</directory>
        </testsuite>
    </testsuites>
    <coverage includeUncoveredFiles="true"
              processUncoveredFiles="true"
              pathCoverage="false"
              ignoreDeprecatedCodeUnits="true"
              disableCodeCoverageIgnore="false">
        <include>
            // File yang perlu dihitung unit test coverage-nya
            <directory suffix=".php">./app</directory>
        </include>
        <exclude>
            // File yang akan diabaikan saat menghasilkan unit test coverage
            <directory suffix=".php">./app/excludeFile</directory>
        </exclude>
        <report>
            <html outputDirectory="test/cover/" lowUpperBound="50" highLowerBound="90"/>
        </report>
    </coverage>
    <logging>
        <junit outputFile="test/junit.xml"/>
    </logging>

</phpunit>
```


Jalankan perintah berikut

```shell
phpdbg -dmemory_limit=1024M -qrr ./vendor/bin/co-phpunit -c phpunit.xml --colors=always
```
