# EasyWechat

[EasyWeChat](https://www.easywechat.com/) é um SDK open source para WeChat (não é um SDK oficial do WeChat).

> Se você estiver usando o Swoole 4.7.0 ou superior e tiver a opção nativa de curl ativada, talvez você não precise seguir este documento.

> Como o componente usa `Curl` por padrão, precisamos modificar o `GuzzleClient` correspondente para ser um client de coroutine, ou modificar a constante [SWOOLE_HOOK_FLAGS](/pt-br/coroutine?id=swoole-runtime-hook-level)

## Substituir `Handler`

O exemplo a seguir usa conta oficial (public account).

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

// Define o HttpClient; algumas interfaces usam http_client diretamente.
$config = $app['config']->get('http', []);
$config['handler'] = $stack = HandlerStack::create($handler);
$app->rebind('http_client', new Client($config));

// Algumas interfaces redefinem o Handler com base em guzzle_handler ao requisitar dados
$app['guzzle_handler'] = $handler;

// Se você estiver usando OfficialAccount, também precisa definir os seguintes parâmetros
$app->oauth->setGuzzleOptions([
    'http_errors' => false,
    'handler' => $stack,
]);
```

## Modificar `SWOOLE_HOOK_FLAGS`

Referência: [SWOOLE_HOOK_FLAGS](/pt-br/coroutine?id=swoole-runtime-hook-level)

## Como usar o EasyWeChat

`EasyWeChat` foi projetado para a arquitetura `PHP-FPM`, então precisa ser modificado em alguns pontos para ser usado no Hyperf. Vamos usar o callback de pagamento como exemplo para explicar.

1. `EasyWeChat` vem com parsing de `XML`, então conseguimos obter o `XML` original.

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
// Faça algo...

```

3. Configuração do server

Se você precisar usar a função de configuração do server da plataforma pública do WeChat, você pode usar o código a seguir.

> O `$response` a seguir é `Symfony\Component\HttpFoundation\Response`, não `Hyperf\HttpMessage\Server\Response`
> Então basta retornar o conteúdo do `Body` diretamente para passar na verificação do WeChat.

```php
$response = $app->server->serve();

return $response->getContent();
```

## Como substituir o cache

`EasyWeChat` usa `file cache` por padrão, mas no cenário real o cache `Redis` é o mais usado, então isso pode ser substituído pelo componente de cache `hyperf/cache` fornecido pelo `Hyperf`. Se você ainda não tiver instalado este componente, execute `composer require hyperf/cache`. O exemplo de uso é o seguinte:

```php
<?php
use Psr\SimpleCache\CacheInterface;
use Hyperf\Context\ApplicationContext;
use EasyWeChat\Factory;

$app = Factory::miniProgram([]);
$app['cache'] = ApplicationContext::getContainer()->get(CacheInterface::class);
```

