# Paginador

Quando você precisa paginar dados, pode usar o componente [hyperf/paginator](https://github.com/hyperf/paginator) para resolver o problema de forma conveniente. Você pode encapsular um pouco sua consulta de dados para obter uma paginação melhor. Este componente também funciona bem em outros frameworks.

Na maioria dos casos, o paginador é usado em consultas de banco de dados. O componente [hyperf/database](https://github.com/hyperf/database) já adaptou o componente de paginador. Você pode usar o paginador facilmente durante consultas. Veja mais detalhes no capítulo [Database - Paginator](pt-br/db/paginator.md).

# Instalação

```bash
composer require hyperf/paginator
```

# Uso básico

Sempre que houver um conjunto de dados e a necessidade de paginação, você pode instanciar a classe `Hyperf\Paginator\Paginator` para processar a paginação. O construtor dessa classe recebe os parâmetros `__construct($items, int $perPage, ?int $currentPage = null, array $options = [])`. Basta passar o conjunto de dados no parâmetro `$items` na forma de `Array (Array)` ou da coleção `Hyperf\Collection\Colletion`, e definir a quantidade de itens por página `$perPage` e o número da página atual `$currentPage`. O parâmetro `$options` pode definir todos os atributos da instância do paginador no formato `Key-Value`; para mais detalhes, você pode consultar os atributos internos da classe.

```php
<?php

namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Paginator\Paginator;
use Hyperf\Collection\Collection;

#[AutoController]
class UserController
{
    public function index(RequestInterface $request)
    {
        $currentPage = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 2);

        // Perform query according to $currentPage and $perPage. The Collection type is used here.
        $collection = new Collection([
            ['id' => 1, 'name' => 'Tom'],
            ['id' => 2, 'name' => 'Sam'],
            ['id' => 3, 'name' => 'Tim'],
            ['id' => 4, 'name' => 'Joe'],
        ]);

        $users = array_values($collection->forPage($currentPage, $perPage)->toArray());

        return new Paginator($users, $perPage, $currentPage);
    }
}
```

# Métodos do paginador

## Obter o número da página atual

```php
<?php
$currentPage = $paginator->currentPage();
```

## Obter a quantidade de itens na página atual

```php
<?php
$count = $paginator->count();
```

## Obter o primeiro item da página atual

```php
<?php
$firstItem = $paginator->firstItem();
```

## Obter o último item da página atual

```php
<?php
$lastItem = $paginator->lastItem();
```

## Verificar se existe uma próxima página

```php
<?php
if ($paginator->hasMorePages()) {
    // ...
}
```

## Obter a URL da página correspondente

```php
```php
// URL da próxima página
$nextPageUrl = $paginator->nextPageUrl();
// URL da página anterior
$previousPageUrl = $paginator->previousPageUrl();
// URL da $page
$url = $paginator->url($page);
```

## Verificar se está na primeira página

```php
<?php
$onFirstPage = $paginator->onFirstPage();
```
ágina

```php
<?php
$onFirstPage = $paginator->onFirstPage();
```
