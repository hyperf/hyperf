# Request object

O `Request object (Request)` é completamente implementado com base no padrão [PSR-7](https://www.php-fig.org/psr/psr-7/) e é implementado pelo [hyperf/http-message](https://github.com/hyperf/http-message).

> Observe que o padrão [PSR-7](https://www.php-fig.org/psr/psr-7/) `Request (Request)` é projetado com o `mecanismo de imutabilidade`; todos os methods que começam com o prefixo `with` retornam um novo objeto e não modificam o valor do objeto original

## Instalação

Este componente é completamente independente e adequado para qualquer projeto de framework.

```bash
composer require hyperf/http-message
```

> Se usado em outros projetos de framework, apenas a API fornecida pela PSR-7 é suportada. Para detalhes, você pode consultar diretamente as especificações relevantes da PSR-7. O uso descrito neste documento é limitado ao uso ao utilizar o Hyperf.

## Obter o objeto de request

Você pode injetar `Hyperf\HttpServer\Contract\RequestInterface` através do container para obter o `Hyperf\HttpServer\Request` correspondente. O objeto realmente injetado é um objeto proxy implementando o `objeto request PSR-7 (Request)` para cada requisição, o que significa que este objeto só pode ser obtido durante o life cycle do `onRequest`. A seguir, um exemplo de como obter o objeto de request:

```php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class IndexController
{
    public function info(RequestInterface $request)
    {
        // ...
    }
}
```

### Dependency injection e parâmetros

Se você quiser obter parâmetros de routing através dos parâmetros do method do controller, você pode listar os parâmetros correspondentes após as dependências, e o framework injetará automaticamente os parâmetros correspondentes nos parâmetros do method. Por exemplo, se sua rota é definida da seguinte forma:

```php
// Route definition using annotation method
#[GetMapping(path: "/user/{id:\d+}")]

// Route definition using configuration method
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET','HEAD'], '/user/{id:\d+}', [\App\Controller\IndexController::class, 'user']);
```

Então você pode obter o parâmetro `query` `id` declarando o parâmetro `$id` no parâmetro do method, como mostrado abaixo:

```php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class IndexController
{
    public function info(RequestInterface $request, int $id)
    {
        // ...
    }
}
```

Além de obter parâmetros de rota através de Dependency Injection, você também pode obter parâmetros de rota através do method `route` do objeto request, como mostrado abaixo:

```php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class IndexController
{
    public function info(RequestInterface $request)
    {
        // Returns the route parameter id if defined or null if the value is missing
        $id = $request->route('id');

        // Returns the route parameter id if defined or 0 if the value is missing
        $id = $request->route('id', 0);
        // ...
    }
}
```

### Path & method da request

Além de usar as `APIs` definidas pelo padrão [PSR-7](https://www.php-fig.org/psr/psr-7/) `Hyperf\HttpServer\Contract\RequestInterface`, o objeto request também fornece diversos methods para acessar os dados da request. Abaixo está uma lista com alguns exemplos de methods:

#### Obter o path da request

O method `path()` retorna a informação de path da requisição. Em outras palavras, se o endereço de destino da requisição de entrada for `http://domain.com/foo/bar?baz=1`, então `path()` retornará `foo/bar`:

```php
$uri = $request->path();
```

O method `is(...$patterns)` pode verificar se o path da requisição de entrada corresponde à regra especificada. Ao usar este method, você também pode passar o caractere `*` como wildcard:

```php
if ($request->is('user/*')) {
    // ...
}
```

#### Obter a URL da requisição

Você pode usar o method `url()` ou `fullUrl()` para obter a `URL` completa da requisição de entrada. O method `url()` retorna a `URL` sem os `query parameters`, e o valor de retorno do method `fullUrl()` contém os `query parameters`:

```php
// No query parameters
$url = $request->url();

// With query parameters
$url = $request->fullUrl();
```

#### Obter o request method

O method `getMethod()` retornará o request method do `HTTP`. Você também pode usar o method `isMethod(string $method)` para verificar se o request method do `HTTP` corresponde à regra especificada:

```php
$method = $request->getMethod();

if ($request->isMethod('post')) {
    // ...
}
```

### Request e method PSR-7

O componente de mensagens [hyperf/http-message](https://github.com/hyperf/http-message) em si é uma implementação dos componentes padrão [PSR-7](https://www.php-fig.org/psr/psr-7/), e os methods da interface podem ser chamados através do objeto request (Request) injetado.
Se a request for declarada como a interface padrão [PSR-7](https://www.php-fig.org/psr/psr-7/) `Psr\Http\Message\ServerRequestInterface` durante a injeção, o framework converterá automaticamente para o objeto equivalente `Hyperf\HttpServer\Request` que implementa `Hyperf\HttpServer\Contract\RequestInterface`.

> É recomendado usar `Hyperf\HttpServer\Contract\RequestInterface` para injeção, para que você possa obter o suporte de autocompletar da IDE para methods exclusivos.

## Pré-processamento & normalização de input

## Obter input

### Obter todo o input

Você pode usar o method `all()` para obter todos os dados de input na forma de um `array`:

```php
$all = $request->all();
```

### Obter o valor de input especificado

Use `input(string $key, $default = null)` e `inputs(array $keys, $default = null): array` para obter `um` ou `múltiplos` valores de input de qualquer forma:

```php
// Returns the input value if it exists or null if it doesn't exist
$name = $request->input('name');

// Return the input value if it exists or the default value of 'Hyperf' if it doesn't exist
$name = $request->input('name','Hyperf');
```

Se os dados do form enviados contiverem dados na forma de um array, você pode usar a dot syntax para obter um valor aninhado a partir do array:

```php
$name = $request->input('products.0.name');

$names = $request->input('products.*.name');
```
### Obter input a partir da query string

Use o method `input` ou `inputs` para obter os dados de input a partir de toda a request (incluindo os `query parameters`), e o method `query(?string $key = null, $default = null)` para obter input apenas a partir da query string:

```php
// Return the query parameter if it exists, return null if it doesn't exist
$name = $request->query('name');

// Return the query parameter if it exists, return default value of 'Hyperf' if it doesn't exist
$name = $request->query('name','Hyperf');

// If no parameters are passed, all query parameters are returned as an associative array
$name = $request->query();
```

### Obter informação de input `JSON`

Se o formato de dados do `body` da request for `JSON`, desde que o valor do header `Content-Type` do `objeto request (Request)` esteja corretamente definido como `application/json`, você pode usar o method `input(string $key, $default = null)` para acessar os dados `JSON`, e você pode até usar a dot syntax para ler o array `JSON`:

```php
// Return value or null if it does not exist
$name = $request->input('user.name');

// Return value or default value of 'Hyperf' if it does not exist
$name = $request->input('user.name','Hyperf');

// Return all Json data as an array
$name = $request->all();
```

### Determinar se o valor de input existe

Para determinar se um valor existe na request, você pode usar o method `has($keys)`. Se o valor existir na request, ele retornará `true`; se não existir, retornará `false`. O primeiro parâmetro pode ser tanto uma string quanto um array contendo múltiplas strings. Neste último caso, o method retornará `true` apenas se todas as chaves existirem:

```php
// Only judge a single value
if ($request->has('name')) {
    // ...
}

// Judge multiple values at the same time
if ($request->has(['name','email'])) {
    // ...
}
```

## Cookies

### Obter Cookies da request

Use o method `getCookieParams()` para obter todos os `Cookies` da request como um array associativo.

```php
$cookies = $request->getCookieParams();
```

Você pode usar o method `cookie(string $key, $default = null)` para obter o valor do cookie correspondente:

 ```php
// Return value if the cookie exists or return null if it doesn't exist
$name = $request->cookie('name');

// Return value if the cookie exists or return a default value of 'Hyperf' if it doesn't exist
$name = $request->cookie('name','Hyperf');
 ```

## File

### Obter arquivos enviados

Você pode usar o method `file(string $key, $default): ?Hyperf\HttpMessage\Upload\UploadedFile` para obter o objeto de arquivo enviado a partir da request. Se o arquivo enviado existir, este method retorna uma instância da class `Hyperf\HttpMessage\Upload\UploadedFile`, que herda a class `SplFileInfo` do `PHP` e também fornece diversos methods para interagir com o arquivo:

```php
// Returns a Hyperf\HttpMessage\Upload\UploadedFile object if the file exists, or null if it does not exist
$file = $request->file('photo');
```

### Verificar se o arquivo existe

Você pode usar o method `hasFile(string $key): bool` para confirmar se há um arquivo na request:

```php
if ($request->hasFile('photo')) {
    // ...
}
```

### Verificar upload com sucesso

Além de verificar se o arquivo enviado existe, você também pode verificar se o arquivo enviado é válido através do method `isValid(): bool`:

```php
if ($request->file('photo')->isValid()) {
    // ...
}
```

### Path do arquivo & extensão

A class `UploadedFile` também contém methods para acessar o path completo do arquivo e sua extensão. O method `getExtension()` determinará a extensão do arquivo com base no conteúdo do arquivo. A extensão pode ser diferente da extensão fornecida pelo cliente:

```php
// The path is the temporary path of the uploaded file
$path = $request->file('photo')->getPath();

// Since the tmp_name of the uploaded file by Swoole does not retain the original file name, this method has been rewritten to obtain the suffix of the original file name
$extension = $request->file('photo')->getExtension();
```

### Armazenar arquivos enviados

O arquivo enviado é armazenado em um local temporário antes de ser armazenado manualmente. Se você não armazenar o arquivo, ele será removido do local temporário após a conclusão da request. Use `moveTo(string $targetPath): void` para mover arquivos temporários para o local de `$targetPath` para armazenamento persistente. O exemplo de código é o seguinte:

```php
$file = $request->file('photo');
$file->moveTo('/foo/bar.jpg');

// Determine whether the method has moved through the isMoved(): bool method
if ($file->isMoved()) {
    // ...
}
```
