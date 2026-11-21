# Testes automatizados

Os testes no Hyperf são implementados por padrão com `phpunit`, mas como o Hyperf é um framework de corrotinas, o script padrão do `phpunit` não funciona tão bem. Por isso, fornecemos um script `co-phpunit`. Você pode chamar o script diretamente ou usar o comando correspondente no composer. Não há componentes específicos para testes automatizados, mas existem implementações correspondentes no [skeleton package](https://github.com/hyperf/hyperf-skeleton) fornecido pelo Hyperf.

```
composer require hyperf/testing
```

```json
"scripts": {
    "test": "co-phpunit -c phpunit.xml --colors=always"
},
```

## Bootstrap

O Hyperf fornece um arquivo `bootstrap.php` padrão, que permite escanear e carregar as bibliotecas correspondentes em memória ao executar testes unitários.

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

Executar testes unitários

```
composer test
```

## Testes HTTP

Ao desenvolver uma interface, normalmente precisamos de um script de teste automatizado para garantir que a interface funcione como esperado. O Hyperf fornece a classe `Hyperf\\Testing\\Client`, que permite simular o processamento de requisições HTTP sem iniciar o servidor HTTP.

```php
<?php
use Hyperf\Testing\Client;

$client = make(Client::class);

$result = $client->get('/');
```

Como o Hyperf suporta configuração multi-porta, além de testar a interface da porta padrão, como testamos o processamento de requisições de outras portas?

```php
<?php

use Hyperf\Testing\Client;

$client = make(Client::class, ['server' =>'adminHttp']);

$result = $client->json('/user/0',[
    'nickname' =>'Hyperf'
]);

```

Por padrão, o framework usa `JsonPacker` e fará parse do `request body` diretamente como `array`. Se você retornar uma `string` diretamente, precisa definir o `Packer` correspondente.

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

## Exemplo

Vamos escrever um pequeno DEMO para testar.

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

## Depuração de código

Depurar manualmente usando métodos como `dd()` e `var_dump` e abrindo a interface correspondente no navegador fica menos eficiente do que no `php fpm` tradicional, porque além de alterar o código você também precisa reiniciar o `server` no terminal para aplicar as mudanças. Por isso, é mais conveniente fazer esse tipo de depuração usando testes automatizados.

Suponha que implementamos uma função para consultar informações de usuário em `UserDao`
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

Então escrevemos o teste unitário correspondente

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

E então executamos apenas este teste

```
composer test - --filter=testUserDaoFirst
```

## Test Doubles

`Gerard Meszaros` definiu esse tipo de teste em `Meszaros2007` com base no conceito de um “stand-in”:

Às vezes é difícil testar o `system under test (SUT)` porque ele depende de outros componentes que não podem ser usados no ambiente de testes. Isso pode acontecer porque esses componentes não estão disponíveis, não retornam os resultados necessários ao teste ou porque executá-los causaria efeitos colaterais indesejáveis. Em outros casos, a estratégia de teste exige mais controle ou mais visibilidade sobre o comportamento interno do sistema sob teste.

Se você não puder usar (ou escolher não usar) o componente dependente real (DOC) ao escrever um teste, você pode usar um test double. O test double não precisa se comportar exatamente como o componente dependente real; ele só precisa fornecer a mesma API do componente real, para que o sistema sob teste pense que ele é um componente real.

A seguir mostramos test doubles usando injeção via construtor e injeção via anotação `#[Inject]`.

### Injetar test doubles via construtor

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

### Injetar test doubles via anotações Inject

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

# Cobertura de testes unitários

## Usar phpdbg para gerar cobertura de testes unitários

Modifique o conteúdo do arquivo `phpunit.xml` da seguinte forma:

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


Execute o comando a seguir:

```shell
phpdbg -dmemory_limit=1024M -qrr ./vendor/bin/co-phpunit -c phpunit.xml --colors=always
```
