# Response

No Hyperf, você pode obter o objeto proxy de response injetando a interface `Hyperf\HttpServer\Contract\ResponseInterface`; por padrão, o container do DI retornará um objeto `Hyperf\HttpServer\Response`, e você pode chamar diretamente todos os methods de `Psr\Http\Message\ResponseInterface` através deste objeto.

> Observe que o objeto de response padrão da PSR-7 é um objeto imutável. O valor de retorno de todos os methods que começam com `with` é um novo objeto e não modifica o valor do objeto original.

## Retornar JSON

Você pode retornar rapidamente um conteúdo no formato `Json` através do method `json($data)` de `Hyperf\HttpServer\Contract\ResponseInterface`, e o `Content-Type` do objeto response também será definido como `application/json`; `$data` aceita um array ou um objeto que implemente a interface `Hyperf\Contract\Arrayable`.

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

Você pode retornar rapidamente um conteúdo no formato `XML` através do method `xml($data)` de `Hyperf\HttpServer\Contract\ResponseInterface`, e o `Content-Type` do objeto response também será definido como `application/xml`; `$data` aceita um array ou um objeto que implemente a interface `Hyperf\Contract\Xmlable`.

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

## Retornar conteúdo raw

Você pode retornar rapidamente o conteúdo raw através do method `raw($data)` de `Hyperf\HttpServer\Contract\ResponseInterface`, e o `Content-Type` do objeto response também será definido como `plain/text`; `$data` aceita uma string ou um objeto que implemente o method `__toString()`.

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

`Hyperf\HttpServer\Contract\ResponseInterface` fornece o method `redirect(string $toUrl, int $status = 302, string $schema = 'http')` para retornar um objeto `Psr7ResponseInterface` que já configurou o status de redirecionamento.

`redirect`:   

|  Argumento  |  Tipo  | Valor padrão |                                                      Comentário                                                      |
|:------:|:------:|:------:|:--------------------------------------------------------------------------------------------------------------:|
| toUrl  | string |   null   | Se o argumento não começar com `http://` ou `https://`, a URL correspondente será automaticamente montada de acordo com o Host do servidor atual, e o protocolo de montagem de acordo com o argumento `$schema` |
| status |  int   |  302   |                                                   Status code do Response                                                   |
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

`Hyperf\HttpServer\Contract\ResponseInterface` fornece o method `download(string $file, string $name = '')` para retornar um objeto `Psr7ResponseInterface` que já configurou o status de download do arquivo.   
Se a request contiver o header `if-match` ou `if-none-match`, o Hyperf também fará a comparação com o `ETag` de acordo com o padrão do protocolo, e se corresponderem, retornará um response com status code `304`.

`download`:   

| Argumento |  Tipo  | Valor padrão |                                Comentário                                 |
|:----:|:------:|:------:|:-------------------------------------------------------------------:|
| file | string |   null   | Para retornar ao path absoluto do arquivo baixado, use a constante `BASE_PATH` para localizar o diretório raiz do projeto |
| name | string |   null   |         O nome do arquivo do lado do cliente para download; se estiver vazio, será usado o nome original do arquivo baixado          |


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
