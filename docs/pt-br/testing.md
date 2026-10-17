# Testes automatizados

Os testes no Hyperf são implementados por padrão usando `phpunit`, mas como o Hyperf é um framework de Coroutine, o script padrão do `phpunit` não funciona muito bem, então fornecemos um script `co-phpunit`. Você pode chamar o script diretamente ou usar o comando composer correspondente. Não existem componentes específicos para testes automatizados, mas haverá implementações correspondentes no [pacote skeleton](https://github.com/hyperf/hyperf-skeleton) fornecido pelo Hyperf.

```
composer require hyperf/testing
```

```json
"scripts": {
    "test": "co-phpunit -c phpunit.xml --colors=always"
},
```

## Bootstrap

O Hyperf fornece por padrão um arquivo `bootstrap.php`, que permite que os usuários escaneiem e carreguem as bibliotecas correspondentes na memória ao executar os testes unitários.

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

Execute os testes unitários

```
composer test
```

## Testes HTTP

Ao desenvolver uma interface, geralmente precisamos de um script de teste automatizado para garantir que a interface fornecida esteja funcionando conforme o esperado. O framework Hyperf fornece a classe `Hyperf\Testing\Client`, que permite simular o processamento de requisições HTTP sem iniciar o servidor HTTP.

```php
<?php
use Hyperf\Testing\Client;

$client = make(Client::class);

$result = $client->get('/');
```

Como o Hyperf oferece suporte à configuração de múltiplas portas, além de testar a interface da porta padrão, como testamos o processamento de requisições de outras portas?

```php
<?php

use Hyperf\Testing\Client;

$client = make(Client::class, ['server' =>'adminHttp']);

$result = $client->json('/user/0',[
    'nickname' =>'Hyperf'
]);

```

Por padrão, o framework usa o `JsonPacker` e fará a análise direta do `request body` como `array`. Se você retornar `string` diretamente, precisará definir o `Packer` correspondente

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

## Depurando código

Depurar código manualmente usando métodos como `dd()` e `var_dump`, e abrir a interface correspondente no navegador, torna-se menos eficiente comparado ao tradicional `php fpm`, porque além das mudanças de código, você também precisa reiniciar o `server` na linha de comando para aplicar essas alterações. Por isso, é mais conveniente fazer esse tipo de depuração usando testes automatizados.

Suponha que implementamos uma função para consultar informações do usuário em `UserDao`
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

Depois escrevemos o teste unitário correspondente

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

Depois executamos nosso teste único

```
composer test - --filter=testUserDaoFirst
```

## Test Doubles

`Gerard Meszaros` definiu esse tipo de teste em `Meszaros2007` com base no conceito de um substituto (stand-in):

Às vezes é difícil testar o `system under test (SUT)` porque ele depende de outros componentes que não podem ser usados no ambiente de teste. Isso pode ocorrer porque esses componentes não estão disponíveis, não retornarão os resultados exigidos pelo teste, ou sua execução terá efeitos colaterais indesejados. Em outros casos, a estratégia de teste requer mais controle ou mais visibilidade sobre o comportamento interno do sistema em teste.

Se você não puder usar (ou optar por não usar) o componente dependente real (DOC) ao escrever um teste, pode usar um test double em seu lugar. O test double não precisa se comportar exatamente da mesma forma que o componente dependente real; ele só precisa fornecer a mesma API que o componente real, para que o sistema em teste pense que está lidando com um componente real!

A seguir são mostrados os test doubles para injetar dependências através do construtor e injetar dependências através da annotation `#[Inject]`.

### Injetar test doubles de dependência através do construtor

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

### Injetar test doubles de dependência através da annotation Inject

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


Execute o seguinte comando:

```shell
phpdbg -dmemory_limit=1024M -qrr ./vendor/bin/co-phpunit -c phpunit.xml --colors=always
```
