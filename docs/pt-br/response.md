# Resposta

No Hyperf, você pode obter o objeto proxy de resposta injetando a interface `Hyperf\HttpServer\Contract\ResponseInterface`. Por padrão, o container DI retornará um objeto `Hyperf\HttpServer\Response`, e você pode chamar diretamente todos os métodos de `Psr\Http\Message\ResponseInterface` por meio desse objeto.

> Observe que o objeto de resposta padrão PSR-7 é imutável. O valor retornado por todos os métodos que começam com `with` é um novo objeto e não modifica o valor do objeto original.

## Retornar JSON

Você pode retornar rapidamente conteúdo em formato `Json` pelo método `json($data)` de `Hyperf\HttpServer\Contract\ResponseInterface`. O `Content-Type` da resposta será definido como `application/json`. O `$data` aceita um array ou um objeto que implemente a interface `Hyperf\Contract\Arrayable`.

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

class IndexController
{
    public function json(ResponseInterface $response): Psr7ResponseInterface
    {
        $data = [
            'key' => 'value'
        ];
        return $response->json($data);
    }
}
```

## Retornar XML

Você pode retornar rapidamente conteúdo em formato `XML` pelo método `xml($data)` de `Hyperf\HttpServer\Contract\ResponseInterface`. O `Content-Type` da resposta será definido como `application/xml`. O `$data` aceita um array ou um objeto que implemente a interface `Hyperf\Contract\Xmlable`.

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

class IndexController
{
    public function xml(ResponseInterface $response): Psr7ResponseInterface
    {
        $data = [
            'key' => 'value'
        ];
        return $response->xml($data);
    }
}
```

## Retornar conteúdo bruto

Você pode retornar rapidamente o conteúdo bruto pelo método `raw($data)` de `Hyperf\HttpServer\Contract\ResponseInterface`. O `Content-Type` da resposta será definido como `plain/text`. O `$data` aceita uma string ou um objeto que implemente o método `__toString()`.

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

class IndexController
{
    public function raw(ResponseInterface $response): Psr7ResponseInterface
    {
        return $response->raw('Hello Hyperf.');
    }
}
```

## Retornar view

Consulte [View](pt-br/view.md).

## Redirecionamento

`Hyperf\HttpServer\Contract\ResponseInterface` fornece o método `redirect(string $toUrl, int $status = 302, string $schema = 'http')` para retornar um objeto `Psr7ResponseInterface` que já vem com o status de redirecionamento configurado.

`redirect`:

|  Argumentos  |  Tipo  | Valor padrão |                                                      Comentário                                                      |
|:------:|:------:|:------:|:--------------------------------------------------------------------------------------------------------------:|
| toUrl  | string |   null   | Se o argumento não começar com `http://` ou `https://`, a URL correspondente será montada automaticamente com base no Host do servidor atual, e o protocolo conforme o argumento `$schema` |
| status |  int   |  302   |                                                   Código de status da Response                                                   |
| schema | string |  http  |                 Efetivo quando `$toUrl` não começa com `http://` ou `https://`; apenas `http` ou `https` estão disponíveis                |

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

class IndexController
{
    public function redirect(ResponseInterface $response): Psr7ResponseInterface
    {
        // redirect() method will return an Psr\Http\Message\ResponseInterface object, needs to return the object.
        return $response->redirect('/anotherUrl');
    }
}
```

## Cookie

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;
use Hyperf\HttpMessage\Cookie\Cookie;

class IndexController
{
    public function cookie(ResponseInterface $response): Psr7ResponseInterface
    {
        $cookie = new Cookie('key', 'value');
        return $response->withCookie($cookie)->withContent('Hello Hyperf.');
    }
}
```

## Compressão Gzip

## Chunk

## Download de arquivo

`Hyperf\HttpServer\Contract\ResponseInterface` fornece o método `download(string $file, string $name = '')` para retornar um objeto `Psr7ResponseInterface` que já vem com o estado de download configurado.

Se a requisição contiver os headers `if-match` ou `if-none-match`, o Hyperf também os comparará com o `ETag` conforme o padrão do protocolo e, se houver correspondência, retornará uma resposta com status `304`.

`download`:

| Argumentos |  Tipo  | Valor padrão |                                Comentário                                 |
|:----:|:------:|:------:|:-------------------------------------------------------------------:|
| file | string |   null   | Caminho absoluto do arquivo baixado; use a constante `BASE_PATH` para localizar o diretório raiz do projeto |
| name | string |   null   | Nome do arquivo baixado pelo client; se estiver vazio, será usado o nome original do arquivo baixado |

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

class IndexController
{
    public function index(ResponseInterface $response): Psr7ResponseInterface
    {
        return $response->download(BASE_PATH . '/public/file.csv', 'filename.csv');
    }
}
```
