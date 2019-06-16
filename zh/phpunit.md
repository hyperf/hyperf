# 单元测试

## 安装

```
composer require hyperf/testing
```

## co-phpunit

Hyperf 是协程框架，所以请使用我们重写的 co-phpunit 脚本，或者使用对应的 composer 脚本。

```json
"scripts": {
    "test": "./test/co-phpunit -c phpunit.xml --colors=always"
},
```

## bootstrap

Hyperf 提供了默认的 bootstrap.php 文件，它让用户在运行单元测试时，扫描并加载对应的库到内存里。

> 当用户修改的代码需要重新生成代理类时，需要主动运行一下脚本。因为你单元测试运行时，并不会重置 代理类。

```
# 重新生成代理类
php bin/hyperf.php di:init-proxy
# 运行单元测试
composer test
```

## 接口请求类

Hyperf 框架下提供了 `HyperfTest\Client` 类，可以让用户不启动 Server 的情况下，模拟接口提交。

```php
<?php
use HyperfTest\Client;

$client = make(Client::class);

$result = $client->get('/');
```

因为 Hyperf 支持多端口配置，除了验证默认的端口接口外，如果验证其他端口的接口呢？

```php
<?php

use HyperfTest\Client;

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

use HyperfTest\Client;
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