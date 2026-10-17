# Routing

Por padrão, o Routing usa o pacote [nikic/fast-route](https://github.com/nikic/FastRoute). O componente [hyperf/http-server](https://github.com/hyperf/http-server) é responsável pela conexão com o servidor `Hyperf`, enquanto o Routing de `RPC` é implementado pelo componente [hyperf/rpc-server](https://github.com/hyperf/rpc-server).

## HTTP routing

### Definir routing através de arquivo de configuração

No skeleton [hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton), todas as definições de routing são definidas por padrão no arquivo `config/routes.php`. O `Hyperf` também suporta `annotation routing`, que é o método recomendado, especialmente quando há muitas rotas.

#### Definindo rotas usando closures

Apenas uma URI e uma closure (Closure) são necessárias para construir uma rota básica:

```php
<?php
use Hyperf\HttpServer\Router\Router;

Router::get('/hello-hyperf', function () {
    return 'Hello Hyperf.';
});
```

Agora você pode requisitar `http://host:port/hello-hyperf` através de um navegador ou da linha de comando `cURL` para acessar a rota.

#### Definir routing padrão

O chamado routing padrão se refere ao routing tratado pelos `controllers` e `actions`. Esse método é bastante semelhante à definição por closure, com a diferença evidente de que a lógica de negócio pode ser delegada às respectivas controller classes:

```php
<?php
use Hyperf\HttpServer\Router\Router;

// Any of the following three definitions can achieve the same effect
Router::get('/hello-hyperf', 'App\Controller\IndexController::hello');
Router::get('/hello-hyperf', 'App\Controller\IndexController@hello');
Router::get('/hello-hyperf', [App\Controller\IndexController::class, 'hello']);
```

A rota é definida vinculando o path `/hello-hyperf` ao method `hello` dentro de `App\Controller\IndexController`.

#### Métodos de routing disponíveis

O router fornece múltiplos methods para ajudar você a registrar qualquer routing de requisição HTTP:

```php
use Hyperf\HttpServer\Router\Router;

// Register the route of the HTTP METHOD consistent with the method name
Router::get($uri, $callback);
Router::post($uri, $callback);
Router::put($uri, $callback);
Router::patch($uri, $callback);
Router::delete($uri, $callback);
Router::head($uri, $callback);

// Register the route of any HTTP METHOD
Router::addRoute($httpMethod, $uri, $callback);
```

Às vezes você pode precisar registrar uma rota que corresponda a múltiplos HTTP methods diferentes ao mesmo tempo. Isso pode ser feito usando o method `addRoute`:

```php
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET','POST','PUT','DELETE'], $uri, $callback);
```

#### Como definir grupos de rota

O grupo de rota adiciona o prefixo do grupo a cada URI. A rota real é `group/route`, ou seja, `/user/index`, `/user/store`, `/user/update`, `/user/delete`

```php
Router::addGroup('/user/', function (){
    Router::get('index', 'App\Controller\UserController@index');
    Router::post('store', 'App\Controller\UserController@store');
    Router::get('update', 'App\Controller\UserController@update');
    Router::post('delete', 'App\Controller\UserController@delete');
});
```

### Definir routing através de annotations

O `Hyperf` fornece uma funcionalidade de routing por [annotation](pt-br/annotation.md) muito conveniente. Você pode definir uma rota diretamente definindo as annotations `#[Controller]` ou `#[AutoController]` em qualquer class.

! > As classes de annotation que aparecem abaixo são classes sob o namespace `use Hyperf\HttpServer\Annotation\`, como `Hyperf\HttpServer\Annotation\AutoController`.

#### Parâmetros de annotation

Tanto `#[Controller]` quanto `#[AutoController]` fornecem dois parâmetros, `prefix` e `server`.

`prefix` indica o prefixo de todas as rotas de method dentro do controller; por padrão, a parte após `\Controller\` no namespace da controller class será usada como o prefixo da rota com a nomenclatura SnakeCase, por exemplo, `\App\Controller\Demo\UserController`, o prefixo será `demo/user` por padrão.

Por exemplo, se `App\Controller\Demo\UserController`, o prefixo será `demo/user` por padrão, e se o path de um method na class for `index`, a rota final será `/demo/user/index`.

! > Observe que `prefix` nem sempre é válido; quando o path de um method dentro de uma class começa com `/`, o path é definido a partir do cabeçalho da `URI`, o que significa que o valor do prefix é ignorado.

`server` indica em qual `HTTP Server` a rota é definida. Como o Hyperf suporta múltiplos `HTTP Servers` simultaneamente, esse parâmetro pode ser usado para distinguir em qual `Server` a rota é definida; o padrão é `http`.

|              Controller              |           Annotation            |      Route URI      |
|:------------------------------------:|:-------------------------------:|:-------------------:|
|   App\Controller\MyDataController    |        @AutoController()        |   /my_data/index    |
|   App\Controller\MydataController    |        @AutoController()        |    /mydata/index    |
|   App\Controller\MyDataController    | @AutoController(prefix="/data") |     /data/index     |
| App\Controller\Demo\MyDataController |        @AutoController()        | /demo/my_data/index |
| App\Controller\Demo\MyDataController | @AutoController(prefix="/data") |     /data/index     |



|              Controller              |                                    Annotation                                     |      Route URI      |
|:------------------------------------:|:---------------------------------------------------------------------------------:|:-------------------:|
|   App\Controller\MyDataController    |        @Controller() + @RequestMapping(path: "index", methods: "get,post")        |   /my_data/index    |
| App\Controller\Demo\MyDataController |        @Controller() + @RequestMapping(path: "index", methods: "get,post")        | /demo/my_data/index |
| App\Controller\Demo\MyDataController | @Controller(prefix="/data") + @RequestMapping(path: "index", methods: "get,post") |     /data/index     |
|   App\Controller\MyDataController    |       @Controller() + @RequestMapping(path: "/index", methods: "get,post")        |       /index        |

#### Annotation AutoController

`#[AutoController]` fornece suporte de vinculação de routing para a maioria dos cenários simples de acesso. Ao usar `#[AutoController]`, o `Hyperf` fará automaticamente o parsing de todos os methods `public` da class em que está e fornecerá tanto o método de requisição `GET` quanto `POST`.

> Ao usar a annotation `#[AutoController]`, é necessário o namespace `use Hyperf\HttpServer\Annotation\AutoController;`.

Nomes de controller em Pascal case serão convertidos automaticamente para snake_case. A seguir, um exemplo da correspondência entre o controller, a annotation e a rota resultante:


```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class UserController
{
    // Hyperf will automatically generate a /user/index route for this method, allowing requests via GET or POST
    public function index(RequestInterface $request)
    {
        // Obtain the id parameter from the request
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```

#### Annotation Controller

`#[Controller]` existe para atender requisitos de definição de routing mais detalhados. O uso da annotation `#[Controller]` é utilizado para indicar que a class atual é uma class `controller`, e a annotation `#[RequestMapping]` é necessária para atualizar a definição detalhada do request method e da URI.

Também fornecemos diversas annotations de `mapping` rápidas e convenientes, como `#[GetMapping]`, `#[PostMapping]`, `#[PutMapping]`, `#[PatchMapping]` e `#[DeleteMapping]`, cada uma correspondendo a um request method compatível.

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

#[Controller]
class UserController
{
    // Hyperf will automatically generate a /user/index route for this method, allowing requests via GET or POST
    #[RequestMapping(path: "index", methods: "get,post")]
    public function index(RequestInterface $request)
    {
        // Obtain the id parameter from the request
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```

### Parâmetros de rota

> Os parâmetros de rota fornecidos devem ser consistentes com o nome e tipo da chave do parâmetro do controller, caso contrário o controller não conseguirá receber os parâmetros relacionados

```php
Router::get('/user/{id}', 'App\Controller\UserController::info');
```
Acesse o parâmetro de rota através da injeção de parâmetro no method do controller.

```php
public function info(int $id)
{
    $user = User::find($id);
    return $user->toArray();
}
```

Acesse o parâmetro de rota através do objeto request.

```php
public function index(RequestInterface $request)
{
    // If it exists, it will return, if it does not exist, it will return the default value null
    $id = $request->route('id');
    // If it exists, it returns, if it doesn't exist, it returns the default value 0
    $id = $request->route('id', 0);
}
```

#### Parâmetros obrigatórios

Podemos definir parâmetros de rota obrigatórios usando `{}`. Por exemplo, `/user/{id}` declara que `id` é um parâmetro obrigatório.

#### Parâmetros opcionais

Às vezes você pode querer que um parâmetro de rota seja opcional. Nesse caso, você pode usar `[]` para declarar o parâmetro dentro dos brackets como um parâmetro opcional, como em `/user/[{id}]`.

#### Validar parâmetros

Você também pode usar expressão regular para validar parâmetros. Aqui estão alguns exemplos
```php
use Hyperf\HttpServer\Router\Router;

// Matches /user/42, but not /user/xyz
Router::addRoute('GET', '/user/{id:\d+}', 'handler');

// Matches /user/foobar, but not /user/foo/bar
Router::addRoute('GET', '/user/{name}', 'handler');

// Matches /user/foo/bar as well
Router::addRoute('GET', '/user/{name:.+}', 'handler');

// This route
Router::addRoute('GET', '/user/{id:\d+}[/{name}]', 'handler');
// Is equivalent to these two routes
Router::addRoute('GET', '/user/{id:\d+}', 'handler');
Router::addRoute('GET', '/user/{id:\d+}/{name}', 'handler');

// Multiple nested optional parts are possible as well
Router::addRoute('GET', '/user[/{id:\d+}[/{name}]]', 'handler');

// This route is NOT valid, because optional parts can only occur at the end
Router::addRoute('GET', '/user[/{id:\d+}]/{name}', 'handler');
```

#### Obter informações de routing

Se o componente devtool estiver instalado, você pode usar o comando `php bin/hyperf.php describe:routes` para obter as informações da lista de routing. Você também pode fornecer a option path, o que é conveniente para obter a informação de uma única rota, por exemplo: `php bin/hyperf.php describe:routes --path=/foo/bar`.

## HTTP exceptions

Quando a rota falha ao fazer o match, como `route not found (404)`, `request method not allowed (405)` e outras HTTP exceptions, o Hyperf lançará de forma unificada uma exception que herda a class `Hyperf\HttpMessage\Exception\HttpException`. Você precisa gerenciar essas exceptions através do mecanismo de `ExceptionHandler` e fazer o tratamento de response correspondente. Por padrão, você pode usar diretamente o `Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler` fornecido pelo componente para captura e tratamento de exception. Observe que você precisa configurar esse exception handler no arquivo de configuração `config/autoload/exceptions.php` e garantir que a ordem de encadeamento entre múltiplos exception handlers esteja correta.
Quando você precisar customizar o response para HTTP exceptions como `route not found (404)` e `request method not allowed (405)`, você pode implementar diretamente seu próprio exception handling com base no código do `HttpExceptionHandler` e configurar seu próprio exception handler. Para a lógica e instruções de uso do exception handler, consulte [Exception Handling](pt-br/exception-handler.md).
