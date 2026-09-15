# Início Rápido

Como exemplo de como usar o `Hyperf`, esta página vai `criar um HTTP Server` para implementar um `Web Service` simples, definindo rotas e controllers. O Hyperf pode fazer muito mais, mas funcionalidades como governança de serviços, serviços `gRPC`, programação com annotations, `AOP` e outras funcionalidades serão explicadas em capítulos específicos.

## Definindo uma rota

O `Hyperf` usa o [nikic/fast-route](https://github.com/nikic/FastRoute) como componente de roteamento padrão, então você pode definir facilmente suas rotas em `config/routes.php`. O `Hyperf` também fornece uma funcionalidade extremamente poderosa e conveniente de `Roteamento por Annotation`.

Para mais informações sobre roteamento além dos exemplos mostrados abaixo, consulte o capítulo [Router](pt-br/router.md).

### Definir rotas via configuração em arquivo

O arquivo de rotas está localizado em `config/routes.php` no projeto [hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton). Abaixo estão alguns exemplos comuns de uso:

```php
<?php
use Hyperf\HttpServer\Router\Router;

// The code example here provides three different binding definitions for each example. In practice, you only need to define one of them.

// Set the route for a GET request, bind the access address '/get' to App\Controller\IndexController::get()
Router::get('/get', 'App\Controller\IndexController::get');
Router::get('/get', 'App\Controller\IndexController@get');
Router::get('/get', [\App\Controller\IndexController::class, 'get']);

// Set the route for a POST request, bind the access address '/post' to App\Controller\IndexController::post()
Router::post('/post', 'App\Controller\IndexController::post');
Router::post('/post', 'App\Controller\IndexController@post');
Router::post('/post', [\App\Controller\IndexController::class, 'post']);

// Set a route that allows GET, POST, and HEAD requests, bind the access address '/multi' to App\Controller\IndexController::multi()
Router::addRoute(['GET', 'POST', 'HEAD'], '/multi', 'App\Controller\IndexController::multi');
Router::addRoute(['GET', 'POST', 'HEAD'], '/multi', 'App\Controller\IndexController@multi');
Router::addRoute(['GET', 'POST', 'HEAD'], '/multi', [\App\Controller\IndexController::class, 'multi']);
```

### Definir rotas via annotations

O `Hyperf` fornece uma funcionalidade de [Annotations](pt-br/annotation.md) que torna rápido e fácil definir rotas. O Hyperf fornece as annotations `#[Controller]` e `#[AutoController]` para uso em uma classe `Controller`. Para instruções detalhadas, consulte o capítulo [Roteamento](pt-br/router.md). Aqui estão alguns exemplos rápidos:

### Definir rotas via `#[AutoController]`

`#[AutoController]` fornece bindings de roteamento automáticos para a maioria dos cenários simples de roteamento. Ao usar `#[AutoController]`, o `Hyperf` vai analisar automaticamente todos os métodos `public` da classe e fornecer requisições `GET` e `POST` para cada um desses métodos.

> A annotation `#[AutoController]` requer o namespace `use Hyperf\HttpServer\Annotation\AutoController;`

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class IndexController
{
    // Hyperf will automatically generate a `/index/index` route for this method, allowing GET or POST requests
    public function index(RequestInterface $request)
    {
        // Retrieve the id parameter from the request
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```

### Definir rotas via `#[Controller]`

Para definições de roteamento mais flexíveis, `#[Controller]` pode ser usado em vez de `#[AutoController]`. Usar a annotation `#[Controller]` em uma classe a transforma em uma `classe Controller`, e a annotation `#[RequestMapping]` pode ser usada para definir os métodos e caminhos de requisição.

O `Hyperf` também fornece diversas `Mapping annotations` rápidas e convenientes, como `#[GetMapping]`, `#[PostMapping]`, `#[PutMapping]`, `#[PatchMapping]`, `#[DeleteMapping]`, que podem substituir `#[RequestMapping]` para economizar tempo quando uma rota precisa apenas de um único método HTTP.

> A annotation `#[Controller]` requer o namespace `use Hyperf\HttpServer\Annotation\Controller;`
> A annotation `#[RequestMapping]` requer o namespace `use Hyperf\HttpServer\Annotation\RequestMapping;` 
> A annotation `#[GetMapping]` requer o namespace `use Hyperf\HttpServer\Annotation\GetMapping;`  
> A annotation `#[PostMapping]` requer o namespace `use Hyperf\HttpServer\Annotation\PostMapping;` 
> A annotation `#[PutMapping]` requer o namespace `use Hyperf\HttpServer\Annotation\PutMapping;`  
> A annotation `#[PatchMapping]` requer o namespace `use Hyperf\HttpServer\Annotation\PatchMapping;`
> A annotation `#[DeleteMapping]` requer o namespace `use Hyperf\HttpServer\Annotation\DeleteMapping;`

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

#[Controller]
class IndexController
{
    // Hyperf will automatically generate a `/index/index` route for this method, allowing GET or POST requests
    #[RequestMapping(path: "index", methods: "get,post")]
    public function index(RequestInterface $request)
    {
        // Retrieve the id parameter from the request
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```


## Lidando com Requisições HTTP

O `Hyperf` não impõe opiniões. Não há exigência para que você implemente o processamento de requisições HTTP usando algum formato específico. Você pode usar o tradicional `modo MVC` ou o `modo RequestHandler` para lidar com as requisições. Vamos usar o `modo MVC` como exemplo:

Crie uma pasta `Controller` dentro da pasta `app` e crie um arquivo `IndexController.php`. O método `index` obtém o parâmetro `id` da requisição, converte-o para o tipo `string` e o retorna ao cliente.

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class IndexController
{
    // Hyperf will automatically generate a `/index/index` route for this method, allowing GET or POST requests
    public function index(RequestInterface $request)
    {
        // Retrieve the id parameter from the request
        $id = $request->input('id', 1);
        // Transfer $id parameter to a string, and return $id to the client with Content-Type:plain/text
        return (string)$id;
    }
}
```

## Injeção Automática de Dependências

A injeção de dependências é uma funcionalidade muito poderosa fornecida pelo `Hyperf` e é a base para a flexibilidade do framework.

O `Hyperf` fornece dois métodos de injeção, um através de injeção pelo construtor, outro através de injeção pela annotation `#[Inject]`. Abaixo estão exemplos para os dois métodos:

Suponha que temos uma classe `\App\Service\UserService`. Existe um método `getInfoById(int $id)` na classe que recebe um argumento `id` e retorna uma entidade de usuário. O tipo de retorno e os detalhes internos não são relevantes para esta documentação, então não vamos dar muita atenção a eles; o que queremos é obter `UserService` em nossa classe e usar os métodos dessa classe. O método normal é instanciar a classe `UserService` através de `new UserService()`, mas no `Hyperf`, usando injeção de dependências, temos uma solução melhor.

### Injeção via construtor

Declare o tipo do parâmetro nos argumentos do construtor, e o `Hyperf` vai injetar automaticamente o objeto ou valor correspondente.

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;
use App\Service\UserService;

#[AutoController]
class IndexController
{

    private UserService $userService;
    
    // Declare the parameter type within the constructor's arguments, and Hyperf will automatically inject the corresponding object or value.
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    
    // /index/info
    public function info(RequestInterface $request)
    {
        $id = $request->input('id', 1);
        return $this->userService->getInfoById((int)$id);
    }
}
```

### Injeção via annotation `#[Inject]`

Declare o tipo do parâmetro acima da propriedade correspondente da classe via `@var` e use a annotation `#[Inject]`. O `Hyperf` vai injetar automaticamente o objeto ou valor correspondente.

> A annotation `#[Inject]` requer o namespace `use Hyperf\Di\Annotation\Inject;`

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\Di\Annotation\Inject;
use App\Service\UserService;

#[AutoController]
class IndexController
{
    #[Inject]
    private UserService $userService;
    
    // /index/info
    public function info(RequestInterface $request)
    {
        $id = $request->input('id', 1);
        return $this->userService->getInfoById((int)$id);
    }
}
```
   
No exemplo acima, podemos ver facilmente que `$userService` não é instanciado manualmente, mas o objeto da classe correspondente à propriedade é injetado automaticamente pelo `Hyperf`.

No entanto, esse caso não mostra realmente o verdadeiro poder da injeção de dependências. Vamos supor que `UserService` tenha suas próprias dependências, e que essas dependências tenham muitas outras dependências também, de modo que qualquer classe que você definir precise instanciar muitos objetos manualmente e gerenciar a ordem dos argumentos de cada classe. No `Hyperf`, não precisamos gerenciar manualmente essas dependências, basta declarar o nome da classe dos argumentos que precisamos, e o `Hyperf` faz todo o trabalho para nós.

Quando `UserService` precisa passar por uma mudança interna drástica, como substituir um serviço local por um serviço remoto RPC, só precisamos ajustar a definição da classe em `UserService.php` para substituir o serviço antigo pelo novo serviço RPC em um único arquivo.

## Iniciar o servidor

Como o `Hyperf` tem um servidor de coroutines integrado, o `Hyperf` será executado como um processo `CLI`. Após definir nossas rotas e escrever o código de lógica da aplicação, podemos iniciar o servidor entrando no diretório raiz do projeto e executando o comando `php bin/hyperf.php start`.

Quando o `console` mostrar que o servidor foi iniciado, você pode acessar o servidor através do `cURL` ou pelo navegador. Por padrão, a url dos exemplos de injeção de dependências acima é `http://127.0.0.1:9501/index/info?id=1`.

## Recarregar o código

O `Hyperf` é uma aplicação `CLI` persistente. Uma vez que o processo é iniciado, o código `PHP` já interpretado permanecerá inalterado enquanto o processo estiver em execução, então alterações no código `PHP` feitas após o início do servidor não terão efeito. Se você quiser que o servidor recarregue seu código, você precisa encerrar o processo digitando `CTRL + C` no `console` e então executar novamente o comando `php bin/hyperf.php start`.

> Dica: Você também pode configurar os comandos para gerenciar o Server na sua IDE, e assim pode executar rapidamente as operações `Iniciar o Servidor` ou `Recarregar o código` diretamente pelos botões `Start/Stop` da IDE.