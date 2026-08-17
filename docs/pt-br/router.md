# Roteamento

Por padrão, o roteamento usa o pacote [nikic/fast-route](https://github.com/nikic/FastRoute). O componente [hyperf/http-server](https://github.com/hyperf/http-server) é responsável por conectar ao servidor `Hyperf`, enquanto o roteamento `RPC` é implementado pelo componente [hyperf/rpc-server](https://github.com/hyperf/rpc-server).

## Roteamento HTTP

### Definir roteamento via arquivo de configuração

No skeleton [hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton), todas as definições de rotas são definidas por padrão no arquivo `config/routes.php`. O `Hyperf` também suporta `annotation routing`, que é o método recomendado, especialmente quando há muitas rotas.

#### Definindo rotas usando closures

Para construir uma rota básica, basta uma URI e uma closure (Closure):

```php
<?php
use Hyperf\HttpServer\Router\Router;

Router::get('/hello-hyperf', function () {
    return 'Hello Hyperf.';
});
```

Agora você pode requisitar `http://host:port/hello-hyperf` via navegador ou pela linha de comando com `cURL` para acessar a rota.

#### Definir roteamento padrão

O chamado roteamento padrão refere-se ao roteamento manipulado por `controllers` e `actions`. Esse método é bem semelhante à definição por closure, com a diferença óbvia de que a lógica de negócio pode ser delegada às classes de controller:

```php
<?php
use Hyperf\HttpServer\Router\Router;

// Qualquer uma das três definições abaixo pode obter o mesmo efeito
Router::get('/hello-hyperf', 'App\Controller\IndexController::hello');
Router::get('/hello-hyperf', 'App\Controller\IndexController@hello');
Router::get('/hello-hyperf', [App\Controller\IndexController::class, 'hello']);
```

A rota é definida como um vínculo do path `/hello-hyperf` ao método `hello` dentro de `App\Controller\IndexController`.

#### Métodos de roteamento disponíveis

O roteador fornece múltiplos métodos para ajudar você a registrar rotas de requisições HTTP:

```php
use Hyperf\HttpServer\Router\Router;

// Registra a rota do MÉTODO HTTP correspondente ao nome do método
Router::get($uri, $callback);
Router::post($uri, $callback);
Router::put($uri, $callback);
Router::patch($uri, $callback);
Router::delete($uri, $callback);
Router::head($uri, $callback);

// Registra a rota para qualquer MÉTODO HTTP
Router::addRoute($httpMethod, $uri, $callback);
```

Às vezes, você pode precisar registrar uma rota que responda a múltiplos métodos HTTP ao mesmo tempo. Isso pode ser feito usando o método `addRoute`:

```php
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET','POST','PUT','DELETE'], $uri, $callback);
```

#### Como definir grupos de rotas

O grupo de rotas adiciona um prefixo de grupo a cada URI. A rota real é `group/route`, isto é: `/user/index`, `/user/store`, `/user/update`, `/user/delete`.

```php
Router::addGroup('/user/', function (){
    Router::get('index', 'App\Controller\UserController@index');
    Router::post('store', 'App\Controller\UserController@store');
    Router::get('update', 'App\Controller\UserController@update');
    Router::post('delete', 'App\Controller\UserController@delete');
});
```

### Definir roteamento via anotações

O `Hyperf` fornece uma função de roteamento via [anotações](pt-br/annotation.md) muito conveniente. Você pode definir uma rota diretamente ao declarar as anotações `#[Controller]` ou `#[AutoController]` em qualquer classe.

! > As classes de anotações que aparecem abaixo são classes sob o namespace `use Hyperf\HttpServer\Annotation\`, como `Hyperf\HttpServer\Annotation\AutoController`.

#### Parâmetros das anotações

Tanto `#[Controller]` quanto `#[AutoController]` fornecem dois parâmetros: `prefix` e `server`.

`prefix` indica o prefixo de todas as rotas de métodos sob o controller. Por padrão, a parte após \Controller\` no namespace da classe de controller será usada como prefixo da rota, usando a nomenclatura SnakeCase; por exemplo, \App\Controller\Demo\UserController` então o prefix será \demo/user` por padrão.

Por exemplo, para `App\Controller\Demo\UserController`, o prefixo será `demo/user` por padrão e, se o path de um método na classe for `index`, a rota final será `/demo/user/index`.

! > Note que `prefix` nem sempre é válido: quando o path de um método dentro de uma classe começa com `/`, o path é definido a partir do cabeçalho `URI`, o que significa que o valor do prefixo é ignorado.

`server` indica em qual `HTTP Server` a rota é definida. Como o Hyperf suporta múltiplos `HTTP Servers` ao mesmo tempo, este parâmetro pode ser usado para distinguir para qual `Server` a rota é definida; o padrão é `http`.

|              Controller              |           Annotation            |       URI da rota      |
|:------------------------------------:|:-------------------------------:|:----------------------:|
|   App\Controller\MyDataController    |        @AutoController()        |   /my_data/index       |
|   App\Controller\MydataController    |        @AutoController()        |   /mydata/index        |
|   App\Controller\MyDataController    | @AutoController(prefix="/data") |     /data/index        |
| App\Controller\Demo\MyDataController |        @AutoController()        | /demo/my_data/index    |
| App\Controller\Demo\MyDataController | @AutoController(prefix="/data") |     /data/index        |



|              Controller              |                                    Annotation                                     |       URI da rota       |
|:------------------------------------:|:---------------------------------------------------------------------------------:|:-----------------------:|
|   App\Controller\MyDataController    |        @Controller() + @RequestMapping(path: "index", methods: "get,post")        |   /my_data/index        |
| App\Controller\Demo\MyDataController |        @Controller() + @RequestMapping(path: "index", methods: "get,post")        | /demo/my_data/index     |
| App\Controller\Demo\MyDataController | @Controller(prefix="/data") + @RequestMapping(path: "index", methods: "get,post") |     /data/index         |
|   App\Controller\MyDataController    |       @Controller() + @RequestMapping(path: "/index", methods: "get,post")        |       /index            |

#### Anotação AutoController

`#[AutoController]` fornece suporte de binding de rotas para a maioria dos cenários simples de acesso. Ao usar `#[AutoController]`, o `Hyperf` analisará automaticamente todos os métodos `public` da classe e fornecerá os métodos de requisição `GET` e `POST`.

> Ao usar a anotação `#[AutoController]`, é necessário `use Hyperf\HttpServer\Annotation\AutoController;`.

Nomes de controller em PascalCase serão convertidos automaticamente para snake_case. A seguir há um exemplo da correspondência entre controller, anotação e a rota gerada:

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class UserController
{
    // O Hyperf vai gerar automaticamente uma rota /user/index para este método, permitindo requisições via GET ou POST
    public function index(RequestInterface $request)
    {
        // Obtém o parâmetro id da requisição
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```

#### Anotação Controller

`#[Controller]` existe para atender a requisitos mais detalhados de definição de rotas. O uso da anotação `#[Controller]` indica que a classe atual é uma classe `controller`, e a anotação `#[RequestMapping]` é necessária para definir detalhadamente o método de requisição e a URI.

Também fornecemos várias anotações de `mapping` rápidas e convenientes, como `#[GetMapping]`, `#[PostMapping]`, `#[PutMapping]`, `#[PatchMapping]` e `#[DeleteMapping]`, cada uma correspondendo a um método de requisição.

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
    // O Hyperf vai gerar automaticamente uma rota /user/index para este método, permitindo requisições via GET ou POST
    #[RequestMapping(path: "index", methods: "get,post")]
    public function index(RequestInterface $request)
    {
        // Obtém o parâmetro id da requisição
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```

### Parâmetros de rota

> Os parâmetros de rota fornecidos devem ser consistentes com o nome e o tipo da chave do parâmetro do controller; caso contrário, o controller não conseguirá aceitar os parâmetros relevantes.

```php
Router::get('/user/{id}', 'App\Controller\UserController::info');
```

Acesse o parâmetro de rota via injeção no método do controller.

```php
public function info(int $id)
{
    $user = User::find($id);
    return $user->toArray();
}
```

Acesse o parâmetro de rota via objeto request.

```php
public function index(RequestInterface $request)
{
    // Se existir, retorna; se não existir, retorna o valor padrão null
    $id = $request->route('id');
    // Se existir, retorna; se não existir, retorna o valor padrão 0
    $id = $request->route('id', 0);
}
```

#### Parâmetros obrigatórios

Podemos definir parâmetros obrigatórios de rota usando `{}`. Por exemplo, `/user/{id}` declara que `id` é um parâmetro obrigatório.

#### Parâmetros opcionais

Às vezes você pode querer que um parâmetro de rota seja opcional. Neste caso, você pode usar `[]` para declarar o parâmetro dentro dos colchetes como opcional, como em `/user/[{id}]`.

#### Validar parâmetros

Você também pode usar expressões regulares para validar parâmetros. Aqui estão alguns exemplos:

```php
use Hyperf\HttpServer\Router\Router;

// Corresponde a /user/42, mas não a /user/xyz
Router::addRoute('GET', '/user/{id:\d+}', 'handler');

// Corresponde a /user/foobar, mas não a /user/foo/bar
Router::addRoute('GET', '/user/{name}', 'handler');

// Também corresponde a /user/foo/bar
Router::addRoute('GET', '/user/{name:.+}', 'handler');

// Esta rota
Router::addRoute('GET', '/user/{id:\d+}[/{name}]', 'handler');
// É equivalente a estas duas rotas
Router::addRoute('GET', '/user/{id:\d+}', 'handler');
Router::addRoute('GET', '/user/{id:\d+}/{name}', 'handler');

// Também são possíveis múltiplas partes opcionais aninhadas
Router::addRoute('GET', '/user[/{id:\d+}[/{name}]]', 'handler');

// Esta rota NÃO é válida, porque partes opcionais só podem ocorrer no final
Router::addRoute('GET', '/user[/{id:\d+}]/{name}', 'handler');
```

#### Obter informações de roteamento

Se o componente devtool estiver instalado, você pode usar o comando `php bin/hyperf.php describe:routes` para obter as informações da lista de rotas. Você também pode fornecer a opção `path`, o que é conveniente para obter as informações de uma única rota, por exemplo: `php bin/hyperf.php describe:routes --path=/foo/bar`.

## Exceções HTTP

Quando a rota falha ao casar (match), como `route not found (404)`, `request method not allowed (405)` e outras exceções HTTP, o Hyperf lançará uniformemente uma exceção que herda de `Hyperf\HttpMessage\Exception\HttpException`. Você precisa gerenciar essas exceções por meio do mecanismo de `ExceptionHandler` e realizar o processamento de resposta correspondente. Por padrão, você pode usar diretamente o `Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler` fornecido pelo componente para captura e processamento da exceção. Note que você precisa configurar esse exception handler no arquivo de configuração `config/autoload/exceptions.php` e garantir que a sequência (cadeia) entre múltiplos exception handlers esteja correta.
Quando você precisar personalizar a resposta para exceções HTTP como `route not found (404)` e `request method not allowed (405)`, você pode implementar seu próprio tratamento com base no código do `HttpExceptionHandler` e configurar o seu exception handler. Para a lógica e instruções de uso do exception handler, consulte [Exception Handling](pt-br/exception-handler.md).
