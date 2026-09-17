# Controller

Para processar requisições HTTP usando Controller, você precisa vincular rotas e métodos de controller via `Config` ou `Annotation`. Veja o capítulo [Router](pt-br/router.md) para mais detalhes.

Para `Request` e `Response`, o Hyperf fornece `Hyperf\HttpServer\Contract\RequestInterface` e `Hyperf\HttpServer\Contract\ResponseInterface` para obter parâmetros e retornar valores. Veja os capítulos [Request](pt-br/request.md) e [Response](pt-br/response.md) para mais detalhes.

## Criar um Controller

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

class IndexController
{
    // Objetos relacionados serão injetados automaticamente pelo container de injeção de dependência se você obtiver tais objetos definindo RequestInterface e ResponseInterface nos parâmetros.
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        $target = $request->input('target', 'World');
        return 'Hello ' . $target;
    }
}
```

> Assuma que este `Controller` foi definido como rota `/` via `Config`. (Claro, você também pode definir via `Annotation`.)

Chame este endereço via `cURL`, e você verá o conteúdo retornado.

```bash
$ curl http://127.0.0.1:9501/\?target\=Hyperf
Hello Hyperf.
```

## Evite confusão de dados entre corrotinas

Em frameworks tradicionais PHP-FPM, costuma existir um `AbstractController` (ou uma classe abstrata pai com outro nome). Em seguida, outros `Controller` definidos fazem algumas operações de request/response com base no `AbstractController`. Porém, no Hyperf, **NÃO FAÇA ISSO**.

Como a maioria dos objetos, incluindo `Controller`, existe como `Singleton` (o que também favorece a reutilização de objetos) e os dados de requisição ficam armazenados no `Context` da corrotina, **NÃO** armazene nenhum dado de requisição como atributo de classe (incluindo propriedades não estáticas).

Claro, não é impossível armazenar dados de requisição como atributos de classe se você realmente quiser. Repare que os objetos `Request` e `Response` são obtidos via injeção de `Hyperf\HttpServer\Contract\RequestInterface` e `Hyperf\HttpServer\Contract\ResponseInterface`, então o objeto correspondente também é singleton. Como isso é seguro entre corrotinas?

Tomando `RequestInterface` como exemplo: quando o objeto `Hyperf\HttpServer\Request` obtém o `PSR-7 request object` internamente, ele o obtém a partir do `Context`. Então a classe usada é apenas uma classe proxy, e a chamada real é obtida do `Context`.
