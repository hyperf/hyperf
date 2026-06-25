# Início rápido

Como exemplo de uso do `Hyperf`, esta página irá `criar um servidor HTTP` para implementar um `Web Service` simples definindo rotas e controllers. O Hyperf pode fazer muito mais, mas recursos como governança de serviços, serviços `gRPC`, programação por anotações, `AOP` e outros serão explicados em capítulos específicos.

## Definindo uma rota

O `Hyperf` usa [nikic/fast-route](https://github.com/nikic/FastRoute) como componente de roteamento padrão, então você pode definir suas rotas facilmente em `config/routes.php`. O `Hyperf` também oferece um recurso de `Annotation Routing` extremamente poderoso e conveniente.

Para mais informações sobre roteamento além dos exemplos abaixo, consulte o capítulo [Router](pt-br/router.md).

### Definir rotas via configuração em arquivo

O arquivo de rotas fica em `config/routes.php` no projeto [hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton). A seguir estão alguns exemplos comuns de uso:

```php
<?php
use Hyperf\HttpServer\Router\Router;

// O exemplo de código aqui fornece três definições diferentes de binding para cada caso. Na prática, você só precisa definir uma delas.

// Define a rota para uma requisição GET, vinculando o endereço '/get' a App\Controller\IndexController::get()
Router::get('/get', 'App\Controller\IndexController::get');
Router::get('/get', 'App\Controller\IndexController@get');
Router::get('/get', [\App\Controller\IndexController::class, 'get']);

// Define a rota para uma requisição POST, vinculando o endereço '/post' a App\Controller\IndexController::post()
Router::post('/post', 'App\Controller\IndexController::post');
Router::post('/post', 'App\Controller\IndexController@post');
Router::post('/post', [\App\Controller\IndexController::class, 'post']);

// Define uma rota que permite requisições GET, POST e HEAD, vinculando o endereço '/multi' a App\Controller\IndexController::multi()
Router::addRoute(['GET', 'POST', 'HEAD'], '/multi', 'App\Controller\IndexController::multi');
Router::addRoute(['GET', 'POST', 'HEAD'], '/multi', 'App\Controller\IndexController@multi');
Router::addRoute(['GET', 'POST', 'HEAD'], '/multi', [\App\Controller\IndexController::class, 'multi']);
```

### Definir rotas via anotações

O `Hyperf` oferece o recurso de [Annotations](pt-br/annotation.md), que torna rápido e fácil definir rotas. O Hyperf fornece as anotações `#[Controller]` e `#[AutoController]` para uso em uma classe `Controller`. Para instruções mais detalhadas, consulte o capítulo [Routing](pt-br/router.md). Aqui vão alguns exemplos rápidos:

### Definir rotas via `#[AutoController]`

A anotação `#[AutoController]` fornece bindings de rotas automáticos para a maioria dos cenários simples. Ao usar `#[AutoController]`, o `Hyperf` irá analisar automaticamente todos os métodos `public` da classe e fornecer requisições `GET` e `POST` para cada um desses métodos.

> As anotações `#[AutoController]` exigem o namespace `use Hyperf\HttpServer\Annotation\AutoController;`

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class IndexController
{
    // O Hyperf vai gerar automaticamente uma rota `/index/index` para este método, permitindo requisições GET ou POST
    public function index(RequestInterface $request)
    {
        // Obtém o parâmetro id da requisição
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```

### Definir rotas via `#[Controller]`

Para definições de rotas mais flexíveis, você pode usar `#[Controller]` em vez de `#[AutoController]`. Usar a anotação `#[Controller]` em uma classe a torna uma `Controller class`, e a anotação `#[RequestMapping]` pode ser usada para definir os métodos e caminhos da requisição.

O `Hyperf` também fornece uma variedade de `Mapping annotations` rápidas e convenientes, como `#[GetMapping]`, `#[PostMapping]`, `#[PutMapping]`, `#[PatchMapping]`, `#[DeleteMapping]`, que podem substituir `#[RequestMapping]` para economizar tempo quando uma rota precisa apenas de um método HTTP.

> As anotações `#[Controller]` exigem o namespace `use Hyperf\HttpServer\Annotation\Controller;`
> As anotações `#[RequestMapping]` exigem o namespace `use Hyperf\HttpServer\Annotation\RequestMapping;` 
> As anotações `#[GetMapping]` exigem o namespace `use Hyperf\HttpServer\Annotation\GetMapping;`  
> As anotações `#[PostMapping]` exigem o namespace `use Hyperf\HttpServer\Annotation\PostMapping;` 
> As anotações `#[PutMapping]` exigem o namespace `use Hyperf\HttpServer\Annotation\PutMapping;`  
> As anotações `#[PatchMapping]` exigem o namespace `use Hyperf\HttpServer\Annotation\PatchMapping;`
> As anotações `#[DeleteMapping]` exigem o namespace `use Hyperf\HttpServer\Annotation\DeleteMapping;`

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
    // O Hyperf vai gerar automaticamente uma rota `/index/index` para este método, permitindo requisições GET ou POST
    #[RequestMapping(path: "index", methods: "get,post")]
    public function index(RequestInterface $request)
    {
        // Obtém o parâmetro id da requisição
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```


## Lidando com requisições HTTP

O `Hyperf` não é opinativo. Não há exigência para que você implemente o processamento de requisições HTTP em um formato específico. Você pode usar o `MVC mode` tradicional ou o `RequestHandler mode` para lidar com requisições. Vamos usar o `MVC mode` como exemplo:

Crie uma pasta `Controller` dentro da pasta `app` e crie um arquivo `IndexController.php`. O método `index` obtém o parâmetro `id` da requisição, converte para o tipo `string` e o retorna para o cliente.

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class IndexController
{
    // O Hyperf vai gerar automaticamente uma rota `/index/index` para este método, permitindo requisições GET ou POST
    public function index(RequestInterface $request)
    {
        // Obtém o parâmetro id da requisição
        $id = $request->input('id', 1);
        // Converte o parâmetro $id para uma string e retorna $id para o cliente com Content-Type:plain/text
        return (string)$id;
    }
}
```

## Injeção automática de dependências

Injeção de dependências é um recurso muito poderoso fornecido pelo `Hyperf` e é a base da flexibilidade do framework.

O `Hyperf` oferece dois métodos de injeção: um via injeção no construtor e outro via injeção por anotação `#[Inject]`. A seguir estão exemplos para ambos.

Suponha que temos uma classe `\App\Service\UserService`. Existe um método `getInfoById(int $id)` na classe que recebe um argumento `id` e retorna uma entidade de usuário. O tipo de retorno e os detalhes internos não são relevantes para esta documentação, então não vamos nos aprofundar nisso; o que queremos é obter o `UserService` na nossa classe e usar seus métodos. A forma normal seria instanciar a classe `UserService` via `new UserService()`, mas no `Hyperf`, usando injeção de dependências, temos uma solução melhor.

### Injeção via construtor

Declare o tipo do parâmetro nos argumentos do construtor, e o `Hyperf` injetará automaticamente o objeto ou valor correspondente.

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
    
    // Declare o tipo do parâmetro nos argumentos do construtor, e o Hyperf injetará automaticamente o objeto ou valor correspondente.
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

### Injeção via anotação `#[Inject]`

Declare o tipo do parâmetro acima da propriedade correspondente via `@var` e use a anotação `#[Inject]`. O `Hyperf` injetará automaticamente o objeto ou valor correspondente.

> As anotações `#[Inject]` exigem o namespace `use Hyperf\Di\Annotation\Inject;`

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
   
No exemplo acima, podemos ver facilmente que `$userService` não é instanciado manualmente; o objeto da classe correspondente à propriedade é injetado automaticamente pelo `Hyperf`.

No entanto, esse caso não mostra o verdadeiro poder da injeção de dependências. Assumimos que `UserService` tem suas próprias dependências, e que essas dependências também têm muitas outras, de modo que qualquer classe que você defina precisaria instanciar muitos objetos manualmente e gerenciar a ordem dos argumentos do construtor de cada classe. No `Hyperf`, não precisamos gerenciar essas dependências manualmente: basta declarar o nome da classe dos argumentos de que precisamos, e o `Hyperf` faz todo o trabalho para nós.

Quando o `UserService` precisa passar por uma mudança interna drástica, como substituir um serviço local por um serviço remoto via RPC, precisamos apenas ajustar a definição da classe em `UserService.php` para substituir o serviço antigo pelo novo serviço RPC em um único arquivo.

## Iniciar o servidor

Como o `Hyperf` tem um servidor de corrotinas embutido, ele será executado como um processo `CLI`. Depois de definir as rotas e escrever a lógica de aplicação, podemos iniciar o servidor entrando no diretório raiz do projeto e executando o comando `php bin/hyperf.php start`.

Quando o `console` indicar que o servidor foi iniciado, você pode acessá-lo via `cURL` ou pelo navegador. Por padrão, a URL dos exemplos de injeção de dependência acima é `http://127.0.0.1:9501/index/info?id=1`.

## Recarregar o código

O `Hyperf` é uma aplicação `CLI` persistente. Depois que o processo inicia, o código `PHP` interpretado permanece inalterado enquanto o processo está em execução; portanto, alterações no código `PHP` após o servidor iniciar não terão efeito. Se você quiser que o servidor recarregue seu código, você precisa encerrar o processo digitando `CTRL + C` no `console` e então executar novamente o comando `php bin/hyperf.php start`.

> Dica: você também pode configurar comandos para gerenciar o servidor na sua IDE e executar rapidamente as operações `Start the Server` ou `Reload the code` diretamente pelos botões `Start/Stop` da IDE.

