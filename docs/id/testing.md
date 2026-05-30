# Pengujian Otomatis

Pengujian di Hyperf diimplementasikan menggunakan `phpunit` secara default,
tetapi karena Hyperf adalah sebuah coroutine framework, skrip `phpunit` default
tidak berjalan dengan baik, sehingga kami menyediakan skrip `co-phpunit`. Anda
dapat memanggil skrip tersebut secara langsung atau menggunakan perintah
composer yang sesuai. Tidak ada komponen khusus untuk pengujian otomatis, tetapi
akan ada implementasi yang sesuai dalam [paket
skeleton](https://github.com/hyperf/hyperf-skeleton) yang disediakan oleh
Hyperf.

```
composer require hyperf/testing
```

```json
"scripts": {
    "test": "co-phpunit -c phpunit.xml --colors=always"
},
```

## Bootstrap

Hyperf menyediakan berkas `bootstrap.php` default, yang memungkinkan pengguna
memindai dan memuat library yang sesuai ke dalam memori saat menjalankan unit
test.

```php
<?php

declare(strict_types=1);

error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');

! defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__, 1));
! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL);

Swoole\Runtime::enableCoroutine(true);

require BASE_PATH.'/vendor/autoload.php';

Hyperf\Di\ClassLoader::init();

$container = require BASE_PATH.'/config/container.php';

$container->get(Hyperf\Contract\ApplicationInterface::class);

```

Menjalankan unit test

```
composer test
```

## HTTP Testing

Saat mengembangkan antarmuka (interface/API), kita biasanya memerlukan skrip
pengujian otomatis untuk memastikan bahwa interface yang kita sediakan berjalan
seperti yang diharapkan. Hyperf framework menyediakan kelas
`Hyperf\Testing\Client`, yang memungkinkan Anda mensimulasikan pemrosesan HTTP
request tanpa menjalankan HTTP server.

```php
<?php
use Hyperf\Testing\Client;

$client = make(Client::class);

$result = $client->get('/');
```

Karena Hyperf mendukung konfigurasi multi-port selain menguji interface port
default, bagaimana cara kita menguji pemrosesan request lain untuk port yang
berbeda?

```php
<?php

use Hyperf\Testing\Client;

$client = make(Client::class, ['server' => 'adminHttp']);

$result = $client->json('/user/0', [
    'nickname' => 'Hyperf'
]);

```

Secara default, framework menggunakan `JsonPacker` dan akan langsung mem-parse
`request body` sebagai `array`. Jika Anda mengembalikan `string` secara
langsung, Anda perlu mengatur `Packer` yang sesuai

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

## Contoh

Mari kita tulis sebuah DEMO kecil untuk mengujinya.

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
    /**
     * @var Client
     */
    protected $client;

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

Men-debug kode secara manual menggunakan metode seperti `dd()` dan `var_dump`
serta membuka interface terkait di browser menjadi kurang efisien dibandingkan
dengan `php-fpm` tradisional karena selain perubahan kode, Anda juga perlu
me-restart `server` di command line untuk menerapkan perubahan tersebut. Oleh
karena itu, akan lebih mudah untuk melakukan debugging jenis ini menggunakan
pengujian otomatis.

Misalkan kita mengimplementasikan fungsi untuk menanyakan informasi pengguna di
`UserDao`

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

Kemudian kita menulis unit test yang sesuai

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

Kemudian jalankan pengujian tunggal kita

```
composer test - --filter=testUserDaoFirst
```

## Test Doubles

Gerard Meszaros mendefinisikan jenis pengujian ini dalam Meszaros2007
berdasarkan konsep pengganti (stand-in):

Terkadang sulit untuk menguji `system under test (SUT)` karena bergantung pada
komponen lain yang tidak dapat digunakan di lingkungan pengujian. Hal ini
mungkin disebabkan karena komponen tersebut tidak tersedia, tidak mengembalikan
hasil yang diperlukan oleh pengujian, atau pengeksekusiannya akan menimbulkan
efek samping yang tidak diinginkan. Dalam kasus lain, strategi pengujian
memerlukan kontrol lebih atau visibilitas lebih terhadap perilaku internal dari
system under test.

Jika Anda tidak dapat menggunakan (atau memilih untuk tidak menggunakan)
komponen dependen yang sebenarnya (DOC) saat menulis pengujian, Anda dapat
menggunakan test double sebagai gantinya. Test double tidak harus berperilaku
sama persis dengan komponen dependen yang sebenarnya; ia hanya perlu
menyediakan API yang sama dengan komponen asli, sehingga system under test akan
menganggapnya sebagai komponen asli!

Berikut ini menunjukkan penggunaan test double untuk dependency injection melalui
constructor dan dependency injection melalui anotasi `#[Inject]`.

### Menginjeksi dependency test doubles melalui constructor

```php
<?php

namespace App\Logic;

use App\Api\DemoApi;

class DemoLogic
{
    /**
     * @var DemoApi $demoApi
     */
    private $demoApi;

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
    public function tearDown()
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

### Menginjeksi dependency test doubles melalui anotasi Inject

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
    public function tearDown()
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

# Cakupan Unit Test

## Menggunakan phpdbg untuk menghasilkan cakupan unit test

Modifikasi konten dari file `phpunit.xml` sebagai berikut:

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
    <testsuites>
        <testsuite name="Tests">
            <directory suffix="Test.php">./test</directory>
        </testsuite>
    </testsuites>
    <filter>
        // Perlu menghasilkan file untuk cakupan unit test
        <whitelist processUncoveredFilesFromWhitelist="false">
            <directory suffix=".php">./app</directory>
        </whitelist>
    </filter>

    <logging>
        <log type="coverage-html" target="cover/"/>
    </logging>
</phpunit>
```

Jalankan perintah berikut:

```shell
phpdbg -dmemory_limit=1024M -qrr ./vendor/bin/co-phpunit -c phpunit.xml --colors=always
```
