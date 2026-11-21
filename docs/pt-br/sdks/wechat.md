# EasyWechat

[EasyWeChat](https://www.easywechat.com/) é um SDK WeChat open source (não é o SDK oficial do WeChat).

> Se você está usando o Swoole 4.7.0 ou superior, e tem a opção nativa de curl habilitada, talvez você não precise seguir este documento.

> Como o componente usa `Curl` por padrão, precisamos modificar o `GuzzleClient` correspondente para ser um client de coroutine, ou modificar a constante [SWOOLE_HOOK_FLAGS](/pt-br/coroutine?id=swoole-runtime-hook-level)

## substituindo o `Handler`

O exemplo a seguir usa a conta oficial (public account) como exemplo,

```php
<?php

use Hyperf\Context\ApplicationContext;
use EasyWeChat\Factory;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Hyperf\Guzzle\CoroutineHandler;

$container = ApplicationContext::getContainer();

$app = Factory::officialAccount($config);
$handler = new CoroutineHandler();

// Set HttpClient, some interfaces use http_client directly.
$config = $app['config']->get('http', []);
$config['handler'] = $stack = HandlerStack::create($handler);
$app->rebind('http_client', new Client($config));

// Some interfaces will reset the Handler according to guzzle_handler when requesting data
$app['guzzle_handler'] = $handler;

// If you are using OfficialAccount, you also need to set the following parameters
$app->oauth->setGuzzleOptions([
    'http_errors' => false,
    'handler' => $stack,
]);
```

## Modificando `SWOOLE_HOOK_FLAGS`

Referência [SWOOLE_HOOK_FLAGS](/pt-br/coroutine?id=swoole-runtime-hook-level)

## Como usar o EasyWeChat

O `EasyWeChat` foi projetado para a arquitetura `PHP-FPM`, então é necessário fazer modificações em alguns lugares para que seja usado sob o Hyperf. Vamos usar o callback de pagamento como exemplo para explicar.

1. O `EasyWeChat` já vem com parsing de `XML`, então podemos obter o `XML` original.

```php
$xml = $this->request->getBody()->getContents();
```

2. Coloque os dados XML no `Request` do `EasyWeChat`.

```php
<?php
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;

$get = $this->request->getQueryParams();
$post = $this->request->getParsedBody();
$cookie = $this->request->getCookieParams();
$uploadFiles = $this->request->getUploadedFiles() ?? [];
$server = $this->request->getServerParams();
$xml = $this->request->getBody()->getContents();
$files = [];
/** @var \Hyperf\HttpMessage\Upload\UploadedFile $v */
foreach ($uploadFiles as $k => $v) {
    $files[$k] = $v->toArray();
}
$request = new Request($get, $post, [], $cookie, $files, $server, $xml);
$request->headers = new HeaderBag($this->request->getHeaders());
$app->rebind('request', $request);
// Do something...

```

3. Configuração do Server

Se você precisa usar a funcionalidade de configuração do server da plataforma pública do WeChat, você pode usar o código a seguir.

> O `$response` a seguir é `Symfony\Component\HttpFoundation\Response`, não `Hyperf\HttpMessage\Server\Response`
> Então basta retornar diretamente o conteúdo do `Body` para passar a verificação do WeChat.

```php
$response = $app->server->serve();

return $response->getContent();
```

## Como substituir o cache

O `EasyWeChat` usa `cache em arquivo` por padrão, mas no cenário real, o cache `Redis` é o mais usado. Então isso pode ser substituído pelo componente de cache `hyperf/cache` fornecido pelo `Hyperf`. Se você ainda não instalou esse componente, por favor execute `composer require hyperf/cache` para introduzi-lo. O exemplo de uso é o seguinte:

```php
<?php
use Psr\SimpleCache\CacheInterface;
use Hyperf\Context\ApplicationContext;
use EasyWeChat\Factory;

$app = Factory::miniProgram([]);
$app['cache'] = ApplicationContext::getContainer()->get(CacheInterface::class);
```