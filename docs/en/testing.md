# Automated Testing

In Hyperf, testing is implemented via `phpunit` by default, and starting from 3.1, the `pest` framework based on phpunit is supported [Documentation](https://pestphp.com/docs/installation).


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

Hyperf provides a default `bootstrap.php` file, which allows users to scan and load corresponding libraries into memory when running unit tests.

```php
<?php

declare(strict_types=1);

error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');

! defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__, 1));
! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL);

// Enabled by default. Should be commented out when using pest --parallel feature or other native parallel operations.
Swoole\Runtime::enableCoroutine(true);

require BASE_PATH . '/vendor/autoload.php';

Hyperf\Di\ClassLoader::init();

$container = require BASE_PATH . '/config/container.php';

$container->get(Hyperf\Contract\ApplicationInterface::class);

```

Running unit tests

```
composer test
```

## Notes

- `hyperf/testing` provides the Trait [RunTestsInCoroutine](https://github.com/hyperf/hyperf/blob/master/src/testing/src/Concerns/RunTestsInCoroutine.php). Just use this class in specific `Test` cases to enable the coroutine environment.
- When using the `--parallel` parameter feature in pest, `Swoole\Runtime::enableCoroutine(true)` in `test/bootstrap.php` needs to be commented out.

## Mocking HTTP Requests

When developing interfaces, we usually need automated test scripts to ensure that the interfaces we provide are running as expected. The Hyperf framework provides the `Hyperf\Testing\Client` class, which allows you to simulate HTTP service requests without starting a Server:

```php
<?php
use Hyperf\Testing\Client;

$client = make(Client::class);

$result = $client->get('/');
```

Because Hyperf supports multi-port configuration, in addition to verifying default port interfaces, what if we want to verify interfaces on other ports?

```php
<?php

use Hyperf\Testing\Client;

$client = make(Client::class, ['server' => 'adminHttp']);

$result = $client->json('/user/0',[
    'nickname' => 'Hyperf'
]);

```

By default, the framework uses `JsonPacker`, which will directly parse `Body` into an `array`. If you return a `string` directly, you need to set the corresponding `Packer`.

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

### Using Cookies

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

## Debugging Code

In an FPM scenario, we usually modify code and then open a browser to access the corresponding interface, so we usually need two functions, `dd` and `dump`. But Hyperf runs in `CLI` mode. Even if these two functions are provided, we need to restart the `Server` in the `CLI`, and then call the corresponding interface in the browser to view the results. This actually doesn't simplify the process, but makes it more troublesome.

Next, I will introduce how to quickly debug code by cooperating with `testing` and complete unit testing by the way.

Suppose we implemented a function to query user information in `UserDao`:
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

Then we write the corresponding unit test:

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

Then execute our unit test:

```
composer test -- --filter=testUserDaoFirst
```

## Test Doubles

`Gerard Meszaros` introduced the concept of test doubles in `Meszaros2007`:

Sometimes it is difficult to test the `System Under Test (SUT)` because it relies on other components that cannot be used in the test environment. This may be because these components are unavailable, they do not return the results required for the test, or executing them has adverse side effects. In other cases, our testing strategy requires more control or more visibility into the internal behavior of the system under test.

If you cannot (or choose not to) use the actual dependent component (DOC) while writing a test, you can use a test double as a substitute. A test double does not need to behave exactly like the real dependent component; it only needs to provide the same API as the real component, so that the system under test will think it is the real component!

The following shows test doubles injected via constructor and via `#[Inject]` annotation, respectively.

### Test Double with Dependency Injected via Constructor

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

### Test Double with Dependency Injected via `Inject` Annotation

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

## Use phpdbg to Generate Unit Test Coverage

Modify the `phpunit.xml` file as follows:

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
            // Directory for test cases that need to be executed
            <directory suffix="Test.php">./test</directory>
        </testsuite>
    </testsuites>
    <coverage includeUncoveredFiles="true"
              processUncoveredFiles="true"
              pathCoverage="false"
              ignoreDeprecatedCodeUnits="true"
              disableCodeCoverageIgnore="false">
        <include>
            // Files for which unit test coverage needs to be calculated
            <directory suffix=".php">./app</directory>
        </include>
        <exclude>
            // Files to be ignored when generating unit test coverage
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


Execute the following command

```shell
phpdbg -dmemory_limit=1024M -qrr ./vendor/bin/co-phpunit -c phpunit.xml --colors=always
```
