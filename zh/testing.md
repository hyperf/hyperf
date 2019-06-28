# 自动化测试

在 Hyperf 里测试默认通过 `phpunit` 来实现，但由于 Hyperf 是一个协程框架，所以默认的 `phpunit` 并不能很好的工作，因此我们提供了一个 `co-phpunit` 脚本来进行适配，您可直接调用脚本或者使用对应的 composer 命令来运行。自动化测试没有特定的组件，但是在 Hyperf 提供的骨架包里都会有对应实现。

```
composer require hyperf/testing
```

```json
"scripts": {
    "test": "./test/co-phpunit -c phpunit.xml --colors=always"
},
```

## Bootstrap

Hyperf 提供了默认的 `bootstrap.php` 文件，它让用户在运行单元测试时，扫描并加载对应的库到内存里。

```php
<?php

declare(strict_types=1);

error_reporting(E_ALL);

! defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__, 1));

\Swoole\Runtime::enableCoroutine(true);

require BASE_PATH . '/vendor/autoload.php';

require BASE_PATH . '/config/container.php';

```

> 当用户修改的代码需要重新生成代理类时，需要主动运行一下脚本。因为你单元测试运行时，并不会重置代理类。

```
# 重新生成代理类
php bin/hyperf.php di:init-proxy
# 运行单元测试
composer test
```

## 模拟 HTTP 请求

在开发接口时，我们通常需要一段自动化测试脚本来保证我们提供的接口按预期在运行，Hyperf 框架下提供了 `Hyperf\Testing\Client` 类，可以让您在不启动 Server 的情况下，模拟 HTTP 服务的请求：

```php
<?php
use Hyperf\Testing\Client;

$client = make(Client::class);

$result = $client->get('/');
```

因为 Hyperf 支持多端口配置，除了验证默认的端口接口外，如果验证其他端口的接口呢？

```php
<?php

use Hyperf\Testing\Client;

$client = make(Client::class,['server' => 'adminHttp']);

$result = $client->json('/user/0',[
    'nickname' => 'Hyperf'
]);

```

## 示例

让我们写个小 DEMO 来测试一下。

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

## 调试代码

在FPM场景下，我们通常改完代码，然后打开浏览器访问对应接口，所以我们通常会需要两个函数 `dd` 和 `dump`，但 `Hyperf` 跑在 `CLI` 模式下，就算提供了这两个函数，也需要在 `CLI` 中重启 `Server`，然后再到浏览器中调用对应接口查看结果。这样其实并没有简化流程，反而更麻烦了。

接下来，我来介绍如何通过配合 `testing`，来快速调试代码，顺便完成单元测试。

假设我们在 `UserDao` 中实现了一个查询用户信息的函数
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

那我们编写对应的单元测试

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
        $model = \Hyperf\Utils\ApplicationContext::getContaienr()->get(UserDao::class)->first(1);

        var_dump($model);

        $this->assertSame(1, $model->id);
    }
}
```

然后执行我们的单测

```
composer test -- --filter=testUserDaoFirst
```
