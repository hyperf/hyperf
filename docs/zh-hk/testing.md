# 自動化測試

在 Hyperf 裏測試默認通過 `phpunit` 來實現，但由於 Hyperf 是一個協程框架，所以默認的 `phpunit` 並不能很好的工作，因此我們提供了一個 `co-phpunit` 腳本來進行適配，您可直接調用腳本或者使用對應的 composer 命令來運行。自動化測試沒有特定的組件，但是在 Hyperf 提供的骨架包裏都會有對應實現。

```
composer require hyperf/testing
```

```json
"scripts": {
    "test": "co-phpunit -c phpunit.xml --colors=always"
},
```

## Bootstrap

Hyperf 提供了默認的 `bootstrap.php` 文件，它讓用户在運行單元測試時，掃描並加載對應的庫到內存裏。

```php
<?php

declare(strict_types=1);

error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');

! defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__, 1));
! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL);

Swoole\Runtime::enableCoroutine(true);

require BASE_PATH . '/vendor/autoload.php';

Hyperf\Di\ClassLoader::init();

$container = require BASE_PATH . '/config/container.php';

$container->get(Hyperf\Contract\ApplicationInterface::class);

```

運行單元測試

```
composer test
```

## 模擬 HTTP 請求

在開發接口時，我們通常需要一段自動化測試腳本來保證我們提供的接口按預期在運行，Hyperf 框架下提供了 `Hyperf\Testing\Client` 類，可以讓您在不啓動 Server 的情況下，模擬 HTTP 服務的請求：

```php
<?php
use Hyperf\Testing\Client;

$client = make(Client::class);

$result = $client->get('/');
```

因為 Hyperf 支持多端口配置，除了驗證默認的端口接口外，如果驗證其他端口的接口呢？

```php
<?php

use Hyperf\Testing\Client;

$client = make(Client::class, ['server' => 'adminHttp']);

$result = $client->json('/user/0',[
    'nickname' => 'Hyperf'
]);

```

默認情況下，框架使用 `JsonPacker`，會直接解析 `Body` 為 `array`，如果您直接返回 `string`，則需要設置對應 `Packer`

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

$result = $client->json('/user/0',[
    'nickname' => 'Hyperf'
]);
```

### 使用 Cookies

```php
<?php

use Hyperf\Testing\Client;
use Hyperf\Utils\Codec\Json;

$client = make(Client::class);

$response = $client->sendRequest($client->initRequest('POST', '/request')->withCookieParams([
    'X-CODE' => $id = uniqid(),
]));

$data = Json::decode((string) $response->getBody());
```

## 示例

讓我們寫個小 DEMO 來測試一下。

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

## 調試代碼

在 FPM 場景下，我們通常改完代碼，然後打開瀏覽器訪問對應接口，所以我們通常會需要兩個函數 `dd` 和 `dump`，但 `Hyperf` 跑在 `CLI` 模式下，就算提供了這兩個函數，也需要在 `CLI` 中重啓 `Server`，然後再到瀏覽器中調用對應接口查看結果。這樣其實並沒有簡化流程，反而更麻煩了。

接下來，我來介紹如何通過配合 `testing`，來快速調試代碼，順便完成單元測試。

假設我們在 `UserDao` 中實現了一個查詢用户信息的函數
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

那我們編寫對應的單元測試

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

然後執行我們的單測

```
composer test -- --filter=testUserDaoFirst
```

## 測試替身

`Gerard Meszaros` 在 `Meszaros2007` 中介紹了測試替身的概念：

有時候對 `被測系統(SUT)` 進行測試是很困難的，因為它依賴於其他無法在測試環境中使用的組件。這有可能是因為這些組件不可用，它們不會返回測試所需要的結果，或者執行它們會有不良副作用。在其他情況下，我們的測試策略要求對被測系統的內部行為有更多控制或更多可見性。

如果在編寫測試時無法使用（或選擇不使用）實際的依賴組件(DOC)，可以用測試替身來代替。測試替身不需要和真正的依賴組件有完全一樣的的行為方式；他只需要提供和真正的組件同樣的 API 即可，這樣被測系統就會以為它是真正的組件！

下面展示分別通過構造函數注入依賴、通過 `#[Inject]` 註釋注入依賴的測試替身

### 構造函數注入依賴的測試替身

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

### 通過 Inject 註釋注入依賴的測試替身

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

# 單元測試覆蓋率

## 使用 phpdbg 生成單元測試覆蓋率

修改 `phpunit.xml` 文件內容為如下

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
        <!-- other PHP.ini or environment variables -->
        <ini name="memory_limit" value="-1" />
    </php>
    <testsuites>
        <testsuite name="Tests">
            // 需要執行單測的測試案例目錄
            <directory suffix="Test.php">./test</directory>
        </testsuite>
    </testsuites>
    <coverage includeUncoveredFiles="true"
              processUncoveredFiles="true"
              pathCoverage="false"
              ignoreDeprecatedCodeUnits="true"
              disableCodeCoverageIgnore="false">
        <include>
            // 需要統計單元測試覆蓋率的文件
            <directory suffix=".php">./app</directory>
        </include>
        <exclude>
            // 生產單元測試覆蓋率時，需要忽略的文件
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


執行以下命令

```shell
phpdbg -dmemory_limit=1024M -qrr ./vendor/bin/co-phpunit -c phpunit.xml --colors=always
```
