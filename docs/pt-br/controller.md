# Controller

Para tratar requisições HTTP usando Controller, você precisa vincular routing e methods do controller através de `Config` ou `Annotation`. Consulte o capítulo [Router](pt-br/router.md) para mais detalhes.

Para `Request` e `Response`, o Hyperf fornece `Hyperf\HttpServer\Contract\RequestInterface` e `Hyperf\HttpServer\Contract\ResponseInterface` para você obter parâmetros e valores de retorno. Consulte os capítulos [Request](pt-br/request.md) e [Response](pt-br/response.md) para mais detalhes.

## Criar um Controller

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

class IndexController
{
    // Related objects will be automatically injected by the dependency injection container if you obtain such objects by defining RequestInterface and ResponseInterface on the parameters.
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        $target = $request->input('target', 'World');
        return 'Hello ' . $target;
    }
}
```

> Assuma que este `Controller` já foi definido como a rota `/` através de `Config`. (É claro que você também pode defini-lo através de `Annotation`)

Chame esse endereço através do `cURL`, e você poderá ver o conteúdo retornado.

```bash
$ curl http://127.0.0.1:9501/\?target\=Hyperf
Hello Hyperf.
```

## Evitar confusão de dados entre coroutines

No framework PHP-FPM tradicional, um `AbstractController` (ou uma abstract parent class com outro nome) seria fornecido. Então, outros `Controller` definidos executariam algumas requests ou responses com base no `AbstractController`. No entanto, no Hyperf, **NÃO FAÇA ISSO**. Como a maioria dos objetos, incluindo `Controller`, existe como `Singleton`, o que também é para melhor reutilização de objetos, e os dados de request são armazenados no `Context` da coroutine, **NÃO** armazene nenhum dado de request como uma attribute da class (incluindo properties não estáticas).

É claro que não é impossível se você realmente quiser armazenar dados de request como attributes da class. Notamos que os objetos `Request` e `Response` são obtidos através da injeção de `Hyperf\HttpServer\Contract\RequestInterface` e `Hyperf\HttpServer\Contract\ResponseInterface` quando tentamos obter `Request` e `Response`, então o objeto correspondente também é um singleton. Como isso é seguro para coroutine aqui? Tomando `RequestInterface` como exemplo, quando o objeto `Hyperf\HttpServer\Request` correspondente obtém o `objeto request PSR-7` internamente, ele é obtido a partir do `Context`. Então a class realmente usada é apenas uma proxy class, e a chamada real é obtida a partir do `Context`.
