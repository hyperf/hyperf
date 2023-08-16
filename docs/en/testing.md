# Automated testing

Testing in Hyperf is implemented by `phpunit` by default, but because Hyperf is a coroutine framework, the default `phpunit` script does not work very well, so we provide a `co-phpunit` script. You can call the script directly or use the corresponding composer command. There are no specific components for automated testing, but there will be corresponding implementations in the [skeleton package](https://github.com/hyperf/hyperf-skeleton) provided by Hyperf.

```
composer require hyperf/testing
```

```json
"scripts": {
    "test": "co-phpunit -c phpunit.xml --colors=always"
},
```

## Bootstrap

Hyperf provides a default `bootstrap.php` file, which allows users to scan and load the corresponding libraries into memory when running unit tests.

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

Run unit tests

```
composer test
```

## HTTP testing

When developing an interface, we usually need an automated test script to ensure that the interface we provide is running as expected. The Hyperf framework provides the `Hyperf\Testing\Client` class, which allows you to simulate HTTP request processing without starting the HTTP server.

```php
<?php
use Hyperf\Testing\Client;

$client = make(Client::class);

$result = $client->get('/');
```

Because Hyperf supports multi-port configuration in addition to testing the default port interface, how do we test other request processing for other ports?

```php
<?php

use Hyperf\Testing\Client;

$client = make(Client::class, ['server' =>'adminHttp']);

$result = $client->json('/user/0',[
    'nickname' =>'Hyperf'
]);

```

By default, the framework uses `JsonPacker` and will directly parse `request body` as `array`. If you return `string` directly, you need to set the corresponding `Packer`

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
    'nickname' =>'Hyperf'
]);
```

## Example

Let's write a small DEMO to test it.

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

    public function __construct($name = null, array $data = [], $dataName ='')
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

        $res = $this->client->get('/', ['user' =>'developer']);

        $this->assertSame(0, $res['code']);
        $this->assertSame('developer', $res['data']['user']);

        $res = $this->client->post('/', [
            'user' =>'developer',
        ]);
        $this->assertSame('Hello Hyperf.', $res['data']['message']);
        $this->assertSame('POST', $res['data']['method']);
        $this->assertSame('developer', $res['data']['user']);

        $res = $this->client->json('/', [
            'user' =>'developer',
        ]);
        $this->assertSame('Hello Hyperf.', $res['data']['message']);
        $this->assertSame('POST', $res['data']['method']);
        $this->assertSame('developer', $res['data']['user']);

        $res = $this->client->file('/', ['name' =>'file','file' => BASE_PATH.'/README.md']);

        $this->assertSame('Hello Hyperf.', $res['data']['message']);
        $this->assertSame('POST', $res['data']['method']);
        $this->assertSame('README.md', $res['data']['file']);
    }
}
```

## Debugging code

Manually debugging code using methods like `dd()` and `var_dump` and opening the corresponding interface in the browser becomes less efficient compared to traditional `php fpm` because in addition to the code changes, you also need to restart the `server` on the command line to apply those changes. Therefore it's more convenient to do this sort of debugging using automated testing.

Suppose we implement a function to query user information in `UserDao`
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

Then we write the corresponding unit test

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

Then perform our single test

```
composer test - --filter=testUserDaoFirst
```

## Test Doubles

`Gerard Meszaros` defined this type of test in `Meszaros2007` based on the concept of a stand-in:

Sometimes it is difficult to test the `system under test (SUT)` because it relies on other components that cannot be used in the test environment. This may be because these components are not available, they will not return the results required by the test, or executing them will have undesirable side effects. In other cases, the testing strategy requires more control or more visibility into the internal behavior of the system under test.

If you cannot use (or choose not to use) the actual dependent component (DOC) when writing a test, you can use a test double instead. The test double does not need to behave in exactly the same way as the real dependent component; it only needs to provide the same API as the real component, so that the system under test will think it is a real component!

The following shows the test doubles of injecting dependencies through the constructor and injecting dependencies through the #[Inject] annotation.

### Inject dependency test doubles through constructor

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

### Inject dependency test doubles through Inject annotations

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

# Unit test coverage

## Use phpdbg to generate unit test coverage

Modify the content of the `phpunit.xml` file as follows:

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
        // Need to generate a file for unit test coverage
        <whitelist processUncoveredFilesFromWhitelist="false">
            <directory suffix=".php">./app</directory>
        </whitelist>
    </filter>

    <logging>
        <log type="coverage-html" target="cover/"/>
    </logging>
</phpunit>

```


Execute the following command:

```shell
phpdbg -dmemory_limit=1024M -qrr ./vendor/bin/co-phpunit -c phpunit.xml --colors=always
```
