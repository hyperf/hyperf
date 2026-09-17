# Busca full-text em models

## Prefácio

> [hyperf/scout](https://github.com/hyperf/scout) é derivado de [laravel/scout](https://github.com/laravel/scout). Fizemos algumas adaptações para corrotinas, mas mantivemos a mesma API. Gostaria de agradecer ao time de desenvolvimento do Laravel por implementar um componente tão poderoso e fácil de usar. Este documento foi parcialmente extraído da documentação oficial do Laravel traduzida pela comunidade Laravel China.

O Hyperf/Scout fornece uma solução simples, baseada em drivers, para busca full-text em models. Usando watchers de model, o Scout sincroniza automaticamente seu índice de busca e os registros do model.

Atualmente, o Scout vem com um driver de Elasticsearch. Escrever um driver personalizado é simples, e você é livre para estender o Scout com sua própria implementação de busca.

## Instalação

### Instalar o pacote do componente e o driver de Elasticsearch

```bash
composer require hyperf/scout
composer require hyperf/elasticsearch
```

Após instalar o Scout, use o comando vendor:publish para gerar o arquivo de configuração do Scout. Esse comando gerará um arquivo `scout.php` no seu diretório de configuração.

```bash
php bin/hyperf.php vendor:publish hyperf/scout
```

Por fim, adicione a trait `Hyperf\\Scout\\Searchable` ao model no qual você quer buscar. Essa trait registra um observer do model para manter o model sincronizado com todos os drivers:

```php
<?php

namespace App;

use Hyperf\Database\Model\Model;
use Hyperf\Scout\Searchable;

class Post extends Model
{
    use Searchable;
}
```
## Configuração

### Arquivo de configuração

Gerar arquivo de configuração

```
php bin/hyperf.php vendor:publish hyperf/scout
```

Arquivo de configuração

```php
<?php

declare(strict_types=1);

return [
    'default' => env('SCOUT_ENGINE', 'elasticsearch'),
    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],
    'prefix' => env('SCOUT_PREFIX', ''),
    'soft_delete' => false,
    'concurrency' => 100,
    'engine' => [
        'elasticsearch' => [
            'driver' => Hyperf\Scout\Provider\ElasticsearchProvider::class,
            // If index is set to null, each model corresponds to an index, otherwise each model corresponds to a type
            'index' => null,
            'hosts' => [
                env('ELASTICSEARCH_HOST', 'http://127.0.0.1:9200'),
            ],
        ],
    ],
];

```
### Configurar o índice do model

Cada model é sincronizado com um “índice” de busca específico, que contém todos os registros pesquisáveis desse model. Em outras palavras, você pode pensar em cada “índice” como uma tabela do MySQL. Por padrão, cada model é persistido em um índice que corresponde ao nome da “tabela” do model (geralmente o plural do nome do model). Você também pode personalizar o índice do model sobrescrevendo o método `searchableAs` no model:
```php
    <?php

    namespace App;

    use Hyperf\Scout\Searchable;
    use Hyperf\Database\Model\Model;

    class Post extends Model
    {
        use Searchable;

        /**
         * Obtém o nome do índice para o model.
         *
         * @return string
         */
        public function searchableAs()
        {
            return 'posts_index';
        }
    }
```

<a name="configuring-searchable-data"></a>

### Configurar os dados pesquisáveis

Por padrão, o “índice” lê dados do método `toArray` do model para persistência. Se você quiser personalizar os dados sincronizados com o índice de busca, pode sobrescrever o método `toSearchableArray` no model:
```php
    <?php

    namespace App;

    use Hyperf\Scout\Searchable;
    use Hyperf\Database\Model\Model;

    class Post extends Model
    {
        use Searchable;

        /**
         * Obtém o array de dados indexáveis do model.
         *
         * @return array
         */
        public function toSearchableArray()
        {
            $array = $this->toArray();

            // Personalize o array...

            return $array;
        }
    }
```

<a name="indexing"></a>
## index

<a name="batch-import"></a>
### Importação em lote

Se você quiser instalar o Scout em um projeto existente, provavelmente já tem registros no banco de dados que deseja importar para o mecanismo de busca. Importe todos os registros existentes para o índice de busca usando o comando `import` fornecido pelo Scout:
```bash
    php bin/hyperf.php scout:import "App\Post"
```

<a name="adding-records"></a>
### Adicionar registro

Quando você adiciona a trait `Hyperf\\Scout\\Searchable` a um model, basta dar `save` em uma instância do model, e ela será automaticamente adicionada ao índice de busca. A atualização do índice é feita ao final da corrotina e não bloqueia a requisição.
```php
    $order = new App\Order;

    // ...

    $order->save();
```

#### Adicionar em lote

Se você quiser adicionar uma coleção de models ao índice de busca via query builder, você também pode encadear o método `searchable` no query builder. `searchable` fará chunk do resultado da query e adicionará os registros ao índice de busca.
```php
    // Usar o Model Query Builder para adicionar...
    App\Order::where('price', '>', 100)->searchable();

    // Adicionar registros usando relacionamentos do model...
    $user->orders()->searchable();

    // Adicionar registros usando collections...
    $orders->searchable();
```

O método `searchable` pode ser entendido como uma operação de “upsert”. Ou seja, se o registro do model já existir no índice, ele será atualizado. Se não existir no índice de busca, ele será adicionado ao índice.

<a name="updating-records"></a>
### Atualizar registro

Para atualizar um model pesquisável, basta atualizar as propriedades da instância e dar `save` para persistir no banco de dados. O Scout sincronizará automaticamente as atualizações no índice de busca:
```php
    $order = App\Order::find(1);

    // Atualizar order...

    $order->save();
```

Você também pode usar o método `searchable` em uma query do model para atualizar uma coleção de models. Se o model não existir no índice, ele será criado:
```php
    // Atualizar via query do model...
    App\Order::where('price', '>', 100)->searchable();

    // Você também pode atualizar via relacionamentos do model...
    $user->orders()->searchable();

    // Você também pode atualizar via collection...
    $orders->searchable();
```

<a name="removing-records"></a>
### Excluir registro

Basta excluir o model do banco de dados usando `delete` para remover o registro do índice. Essa forma de exclusão é compatível inclusive com soft delete:
```php
    $order = App\Order::find(1);

    $order->delete();
```

Se você não quiser buscar o model antes de excluir o registro, você pode usar o método `unsearchable` na instância da query do model ou na collection:
```php
    // Excluir via query do model...
    App\Order::where('price', '>', 100)->unsearchable();

    // Excluir via relacionamento do model...
    $user->orders()->unsearchable();

    // Excluir via collection...
    $orders->unsearchable();
```
<a name="pausing-indexing"></a>
### Pausar indexação

Você pode precisar executar um lote de operações no model sem sincronizar os dados do model com o índice de busca. Nesse caso, você pode usar o método coroutine-safe `withoutSyncingToSearch`. Esse método aceita um callback que é executado imediatamente. Todas as operações dentro desse callback não serão sincronizadas com o índice do model:
```php
    App\Order::withoutSyncingToSearch(function () {
        // Executar ações no model...
    });
```
<a name="searching"></a>
## search

Você pode usar o método `search` para buscar models. O método `search` aceita uma string para buscar no model. Você também precisa encadear o método `get` na query de busca para obter os models correspondentes para a expressão de busca:
```php
    $orders = App\Order::search('Star Trek')->get();
```

As buscas do Scout retornam collections de models, então você pode retornar os resultados diretamente de rotas ou controllers, e eles serão automaticamente convertidos para JSON:
```php
    Route::get('/search', function () {
        return App\Order::search([])->get();
    });
```

Se você quiser resultados “raw” antes de serem mapeados para o model, você deve usar o método `raw`:
```php
    $orders = App\Order::search('Star Trek')->raw();
```

As queries de busca geralmente são executadas nos índices definidos pelo método [`searchableAs`](#configuring-model-indexes) do model. É claro que você também pode usar o método `within` para especificar um índice personalizado que deve ser pesquisado:
```php
    $orders = App\Order::search('Star Trek')
        ->within('tv_shows_popularity_desc')
        ->get();
```
<a name="where-clauses"></a>
### Cláusula Where

O Scout permite adicionar cláusulas “where” simples às suas queries de busca. Atualmente, essas cláusulas suportam apenas verificações básicas de igualdade numérica e são usadas principalmente para queries de intervalo baseadas no ID do owner. Como índices de busca não são bancos relacionais, cláusulas “where” mais avançadas não são suportadas no momento:
```php
    $orders = App\Order::search('Star Trek')->where('user_id', 1)->get();
```

<a name="pagination"></a>
### Paginação

Além de obter uma collection de models, você também pode usar o método `paginate` para paginar resultados de busca. Esse método retorna uma instância de `Paginator`, como na [paginação tradicional de query de models](/pt-br/db/paginator):
```php
    $orders = App\Order::search('Star Trek')->paginate();
```

Você pode especificar quantos models obter por página passando o número como primeiro argumento para o método `paginate`:
```php
    $orders = App\Order::search('Star Trek')->paginate(15);
```

Depois de obter os resultados, você pode usar seu template engine favorito para renderizar os links de paginação para exibir os resultados, assim como na paginação tradicional de queries de models:
```php
    <div class="container">
        @foreach ($orders as $order)
            {{ $order->price }}
        @endforeach
    </div>

    {{ $orders->links() }}
```
<a name="custom-engines"></a>
## engine personalizado

#### Implementar engine

Se o engine de busca embutido do Scout não atender às suas necessidades, você pode implementar um engine personalizado e registrá-lo no Scout. Seu engine precisa herdar a classe abstrata `Hyperf\\Scout\\Engine\\Engine`, que contém cinco métodos que seu engine personalizado precisa implementar:
```php
    use Hyperf\Scout\Builder;

    abstract public function update($models);
    abstract public function delete($models);
    abstract public function search(Builder $builder);
    abstract public function paginate(Builder $builder, $perPage, $page);
    abstract public function map($results, $model);
```
É útil ver esses métodos na classe `Hyperf\\Scout\\Engine\\ElasticsearchEngine`. Essa classe é um bom ponto de partida para aprender como implementar esses métodos no seu engine personalizado.

#### Registrar engine

Depois de implementar seu engine personalizado, você pode defini-lo no arquivo de configuração. Por exemplo, se você implementou um `MySqlSearchEngine`, você pode colocar isto no arquivo de configuração:
```php
<?php
return [
    'default' => 'mysql',
    'engine' => [
        'mysql' => [
            'driver' => MySqlSearchEngine::class,
        ],
        'elasticsearch' => [
            'driver' => \Hyperf\Scout\Provider\ElasticsearchProvider::class,
        ],
    ],
];
```

## Diferenças em relação ao laravel/scout

- O Hyperf/Scout usa corrotinas para sincronizar eficientemente índices de busca e registros de model sem depender de mecanismos de fila.
- O Hyperf/Scout fornece o engine open source de Elasticsearch por padrão, em vez do Algolia (closed source).
