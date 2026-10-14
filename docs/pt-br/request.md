# Objeto Request

O `Request (Request)` é implementado completamente com base no padrão [PSR-7](https://www.php-fig.org/psr/psr-7/) e é fornecido por [hyperf/http-message](https://github.com/hyperf/http-message).

> Observe que o `Request (Request)` do padrão [PSR-7](https://www.php-fig.org/psr/psr-7/) é projetado com um `mecanismo de imutabilidade`: todos os métodos que começam com o prefixo `with` retornam um novo objeto e não modificam o valor do objeto original

## Instalação

Este componente é totalmente independente e é adequado para qualquer projeto de framework.

```bash
composer require hyperf/http-message
```

> Se for usado em outros projetos de framework, apenas a API fornecida pelo PSR-7 é suportada. Para detalhes, você pode consultar diretamente as especificações relacionadas do PSR-7. O uso descrito neste documento é limitado ao uso no Hyperf.

## Obter o objeto request

Você pode injetar `Hyperf\HttpServer\Contract\RequestInterface` via container para obter o `Hyperf\HttpServer\Request` correspondente. O objeto realmente injetado é um proxy que implementa um `Request` PSR-7 para cada requisição — o que significa que este objeto só pode ser obtido durante o ciclo de vida de `onRequest`. A seguir está um exemplo de como obter o objeto request:

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

### Injeção de dependência e parâmetros

Se você quiser obter parâmetros de rota via parâmetros do método do controller, você pode listar os parâmetros correspondentes após as dependências, e o framework injetará automaticamente os parâmetros nos argumentos do método. Por exemplo, se sua rota for definida assim:

```php
// Definição de rota usando método de anotações
#[GetMapping(path: "/user/{id:\d+}")]

// Definição de rota usando método de configuração
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET','HEAD'], '/user/{id:\d+}', [\App\Controller\IndexController::class, 'user']);
```

Então você pode obter o parâmetro `id` declarando o parâmetro `$id` no método, como mostrado abaixo:

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

Além de obter parâmetros de rota via injeção de dependência, você também pode obter parâmetros de rota via o método `route` do objeto request, como mostrado abaixo:

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

### Caminho e método da requisição

Além de usar as `APIs` definidas pela interface `Hyperf\HttpServer\Contract\RequestInterface` do padrão [PSR-7](https://www.php-fig.org/psr/psr-7/), o objeto request também fornece uma variedade de métodos para acessar dados da requisição. A seguir estão alguns exemplos de métodos:

#### Obter o caminho da requisição

O método `path()` retorna informações do caminho solicitado. Em outras palavras, se o endereço de destino da requisição for `http://domain.com/foo/bar?baz=1`, então `path()` retornará `foo/bar`:

```php
$uri = $request->path();
```

O método `is(...$patterns)` pode verificar se o caminho da requisição corresponde à regra especificada. Ao usar esse método, você também pode passar o caractere `*` como wildcard:

```php
if ($request->is('user/*')) {
    // ...
}
```

#### Obter a URL solicitada

Você pode usar os métodos `url()` ou `fullUrl()` para obter a `URL` completa da requisição. O método `url()` retorna a `URL` sem os `query parameters`, e o valor de retorno de `fullUrl()` contém os `query parameters`:

```php
// No query parameters
$url = $request->url();

// With query parameters
$url = $request->fullUrl();
```

#### Obter o método da requisição

O método `getMethod()` retorna o método HTTP da requisição. Você também pode usar o método `isMethod(string $method)` para verificar se o método HTTP corresponde às regras especificadas:

```php
$method = $request->getMethod();

if ($request->isMethod('post')) {
    // ...
}
```

### Request PSR-7 e métodos

O componente de mensagens [hyperf/http-message](https://github.com/hyperf/http-message) é uma implementação do padrão [PSR-7](https://www.php-fig.org/psr/psr-7/), e seus métodos de interface podem ser chamados por meio do request injetado.
Se o request for declarado como a interface padrão [PSR-7](https://www.php-fig.org/psr/psr-7/) `Psr\Http\Message\ServerRequestInterface` durante a injeção, o framework converterá automaticamente para o objeto equivalente `Hyperf\HttpServer\Request` que implementa `Hyperf\HttpServer\Contract\RequestInterface`.

> Recomenda-se usar `Hyperf\HttpServer\Contract\RequestInterface` na injeção para obter suporte de auto-completar do IDE para métodos exclusivos.

## Pré-processamento e normalização de entrada

## Obter input

### Obter todo o input

Você pode usar o método `all()` para obter todos os dados de entrada em forma de `array`:

```php
$all = $request->all();
```

### Obter um valor específico de input

Use `input(string $key, $default = null)` e `inputs(array $keys, $default = null): array` para obter `um` ou `vários` valores de input de qualquer forma:

```php
// Returns the input value if it exists or null if it doesn't exist
$name = $request->input('name');

// Return the input value if it exists or the default value of 'Hyperf' if it doesn't exist
$name = $request->input('name','Hyperf');
```

Se os dados enviados contiverem dados em forma de array, você pode usar a sintaxe com ponto para obter um valor “aninhado” do array:

```php
$name = $request->input('products.0.name');

$names = $request->input('products.*.name');
```
### Obter input a partir da query string

Use os métodos `input` ou `inputs` para obter dados de entrada da requisição inteira (incluindo `query parameters`), e o método `query(?string $key = null, $default = null)` para obter input apenas da query string:

```php
// Return the query parameter if it exists, return null if it doesn't exist
$name = $request->query('name');

// Return the query parameter if it exists, return default value of 'Hyperf' if it doesn't exist
$name = $request->query('name','Hyperf');

// If no parameters are passed, all query parameters are returned as an associative array
$name = $request->query();
```

### Obter input `JSON`

Se o formato do `body` da requisição for `JSON`, desde que o header `Content-Type` do request esteja definido corretamente como `application/json`, você pode usar o método `input(string $key , $default = null)` para acessar os dados `JSON` e até usar a sintaxe com ponto para ler arrays em `JSON`:

```php
// Return value or null if it does not exist
$name = $request->input('user.name');

// Return value or default value of 'Hyperf' if it does not exist
$name = $request->input('user.name','Hyperf');

// Return all Json data as an array
$name = $request->all();
```

### Verificar se um valor de input existe

Para determinar se um valor existe na requisição, você pode usar o método `has($keys)`. Se o valor existir, retornará `true`; caso contrário, retornará `false`. O primeiro parâmetro pode ser uma string ou um array com múltiplas strings. No segundo caso, o método retornará `true` apenas se todas as chaves existirem:

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

### Obter Cookies da requisição

Use o método `getCookieParams()` para obter todos os `Cookies` da requisição como um array associativo.

```php
$cookies = $request->getCookieParams();
```

Você pode usar o método `cookie(string $key, $default = null)` para obter o valor do cookie correspondente:

 ```php
// Return value if the cookie exists or return null if it doesn't exist
$name = $request->cookie('name');

// Return value if the cookie exists or return a default value of 'Hyperf' if it doesn't exist
$name = $request->cookie('name','Hyperf');
 ```

## Arquivo

### Obter arquivos enviados

Você pode usar o método `file(string $key, $default): ?Hyperf\HttpMessage\Upload\UploadedFile` para obter o objeto de arquivo enviado a partir da requisição. Se o arquivo enviado existir, este método retorna uma instância da classe `Hyperf\HttpMessage\Upload\UploadedFile`, que herda `SplFileInfo` do `PHP` e também fornece vários métodos para interagir com o arquivo:

```php
// Returns a Hyperf\HttpMessage\Upload\UploadedFile object if the file exists, or null if it does not exist
$file = $request->file('photo');
```

### Verificar se o arquivo existe

Você pode usar o método `hasFile(string $key): bool` para confirmar se há um arquivo na requisição:

```php
if ($request->hasFile('photo')) {
    // ...
}
```

### Verificar se o upload foi bem-sucedido

Além de verificar se o arquivo enviado existe, você também pode verificar se ele é válido via o método `isValid(): bool`:

```php
if ($request->file('photo')->isValid()) {
    // ...
}
```

### Caminho e extensão do arquivo

A classe `UploadedFile` também contém métodos para acessar o caminho completo do arquivo e sua extensão. O método `getExtension()` determinará a extensão do arquivo com base no conteúdo do arquivo. A extensão pode ser diferente da extensão fornecida pelo cliente:

```php
// The path is the temporary path of the uploaded file
$path = $request->file('photo')->getPath();

// Since the tmp_name of the uploaded file by Swoole does not retain the original file name, this method has been rewritten to obtain the suffix of the original file name
$extension = $request->file('photo')->getExtension();
```

### Armazenar arquivos enviados

O arquivo enviado fica armazenado em um local temporário até ser salvo manualmente. Se você não salvar o arquivo, ele será removido do local temporário após a requisição ser concluída. Use `moveTo(string $targetPath): void` para mover arquivos temporários para o local `$targetPath` para armazenamento persistente. Exemplo:

```php
$file = $request->file('photo');
$file->moveTo('/foo/bar.jpg');

// Determine whether the method has moved through the isMoved(): bool method
if ($file->isMoved()) {
    // ...
}
```
