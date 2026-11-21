# Busca full text de model

## Prefácio

> [hyperf/scout](https://github.com/hyperf/scout) é derivado do [laravel/scout](https://github.com/laravel/scout), fizemos algumas adaptações de coroutine nele, mas mantivemos a mesma API. Gostaríamos de agradecer à equipe de desenvolvimento do Laravel por implementar um componente tão poderoso e fácil de usar. Este documento é parcialmente extraído da documentação oficial do Laravel traduzida pela organização da comunidade Laravel China.

O Hyperf/Scout fornece uma solução simples baseada em driver para busca full text de models. Usando observadores de model, o Scout sincroniza automaticamente seu índice de busca e os registros do model.

Atualmente, o Scout vem com um driver Elasticsearch; escrever um driver customizado é simples, e você é livre para estender o Scout com sua própria implementação de busca.

## Instalação

### Introduzir o pacote do componente e o driver Elasticsearch

```bash
composer require hyperf/scout
composer require hyperf/elasticsearch
```

Depois que o Scout for instalado, use o comando vendor:publish para gerar o arquivo de configuração do Scout. Este comando vai gerar um arquivo de configuração scout.php no seu diretório config.

```bash
php bin/hyperf.php vendor:publish hyperf/scout
```

Por fim, adicione a trait Hyperf\Scout\Searchable ao model que você quer buscar. Essa trait registra um observador de model para manter o model sincronizado com todos os drivers:

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
## Configurar

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
### Configurar índice do model

Cada model é sincronizado com um "índice" de busca determinado que contém todos os registros pesquisáveis para aquele model. Em outras palavras, você pode pensar em cada "índice" como uma tabela MySQL. Por padrão, cada model é persistido em um índice que corresponde ao nome da "tabela" do model (geralmente o plural do nome do model). Você também pode customizar o índice do model sobrescrevendo o método `searchableAs` no model:
```php
    <?php

    namespace App;

    use Hyperf\Scout\Searchable;
    use Hyperf\Database\Model\Model;

    class Post extends Model
    {
        use Searchable;

        /**
         * Get the index name for the model.
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

### Configurar dados pesquisáveis

Por padrão, o "índice" vai ler dados do método `toArray` do model para persistência. Se você quiser customizar os dados sincronizados com o índice de busca, você pode sobrescrever o método `toSearchableArray` no model:
```php
    <?php

    namespace App;

    use Hyperf\Scout\Searchable;
    use Hyperf\Database\Model\Model;

    class Post extends Model
    {
        use Searchable;

        /**
         * Get the indexable data array for the model.
         *
         * @return array
         */
        public function toSearchableArray()
        {
            $array = $this->toArray();

            // Customize array...

            return $array;
        }
    }
```

<a name="indexing"></a>
## índice

<a name="batch-import"></a>
### Importação em lote

Se você quiser instalar o Scout em um projeto existente, você provavelmente já tem registros no banco de dados que quer importar para o mecanismo de busca. Importe todos os registros existentes para o índice de busca usando o comando `import` fornecido pelo Scout:
```bash
    php bin/hyperf.php scout:import "App\Post"
```

<a name="adding-records"></a>
### Adicionar registro

Quando você adiciona a Trait `Hyperf\Scout\Searchable` a um model, tudo que você precisa fazer é `save` uma instância de model e ela será adicionada automaticamente ao seu índice de busca. A operação de atualização do índice será feita ao final da coroutine e não vai bloquear a requisição.
```php
    $order = new App\Order;

    // ...

    $order->save();
```

#### Adição em lote

Se você quiser adicionar uma coleção de models ao índice de busca através do query builder do model, você também pode encadear o método `searchable` no query builder do model. `searchable` vai dividir em chunks o resultado da query do construtor e adicionar o registro ao seu índice de busca.
```php
    // Use the Model Query Builder to add...
    App\Order::where('price', '>', 100)->searchable();

    // Adding records using model relationships...
    $user->orders()->searchable();

    // Adding records using collections...
    $orders->searchable();
```

O método `searchable` pode ser pensado como uma operação de "upsert". Em outras palavras, se o registro do model já está no seu índice, ele será atualizado. Se não existir no índice de busca, adicione-o ao índice.

<a name="updating-records"></a>
### Atualizar registro

Para atualizar um model pesquisável, simplesmente atualize as propriedades da instância do model e `save` o model no banco de dados. O Scout vai sincronizar automaticamente as atualizações com seu índice de busca:
```php
    $order = App\Order::find(1);

    // Update order...

    $order->save();
```

Você também pode usar o método `searchable` em uma instrução de query de model para atualizar uma coleção de models. Se o model não existir no índice que você está buscando, ele será criado:
```php
    // Update with model query statement...
    App\Order::where('price', '>', 100)->searchable();

    // You can also use model relational updates...
    $user->orders()->searchable();

    // You can also use collection update...
    $orders->searchable();
```

<a name="removing-records"></a>
### Excluir registro

Simplesmente exclua o model do banco de dados usando `delete` para remover o registro no índice. Essa forma de exclusão é até compatível com o model com soft delete:
```php
    $order = App\Order::find(1);

    $order->delete();
```

Se você não quiser buscar o model antes de excluir o registro, você pode usar o método `unsearchable` na instância de query do model ou coleção:
```php
    // Delete via model query...
    App\Order::where('price', '>', 100)->unsearchable();

    // Delete via model relationship...
    $user->orders()->unsearchable();

    // Delete by Collection...
    $orders->unsearchable();
```
<a name="pausing-indexing"></a>
### Pausar indexação

Você pode precisar realizar um lote de operações de model sem sincronizar os dados do model com o índice de busca. Nesse momento você pode usar o método coroutine-safe `withoutSyncingToSearch` para fazer isso. Este método aceita um callback que é executado imediatamente. Todas as operações neste callback não serão sincronizadas com o índice do model:
```php
    App\Order::withoutSyncingToSearch(function () {
        // Execute model actions...
    });
```
<a name="searching"></a>
## busca

Você pode usar o método `search` para buscar models. O método `search` aceita uma string para buscar o model. Você também precisa encadear o método `get` na query de busca para consultar o model correspondente com uma determinada instrução de busca:
```php
    $orders = App\Order::search('Star Trek')->get();
```

As buscas do Scout retornam coleções de instâncias de model, então você pode retornar resultados diretamente de rotas ou controllers, e eles serão automaticamente convertidos para JSON:
```php
    Route::get('/search', function () {
        return App\Order::search([])->get();
    });
```

Se você quiser resultados raw antes de serem retornados ao model, você deve usar o método `raw`:
```php
    $orders = App\Order::search('Star Trek')->raw();
```

Queries de busca geralmente são executadas nos índices especificados pelo método [`searchableAs`](#configuring-model-indexes) do model. Claro, você também pode usar o método `within` para especificar um índice customizado que deve ser buscado:
```php
    $orders = App\Order::search('Star Trek')
        ->within('tv_shows_popularity_desc')
        ->get();
```
<a name="where-clauses"></a>
### Instrução Where

O Scout permite que você adicione cláusulas "where" simples às suas queries de busca. Atualmente, essas instruções suportam apenas verificações básicas de igualdade numérica, e são usadas principalmente para queries de busca por faixa baseadas no ID do proprietário. Como índices de busca não são bancos de dados relacionais, instruções "where" mais avançadas atualmente não são suportadas:
```php
    $orders = App\Order::search('Star Trek')->where('user_id', 1)->get();
```

<a name="pagination"></a>
### Paginação

Além de recuperar uma coleção de models, você também pode usar o método `paginate` para paginar os resultados de busca. Este método vai retornar uma instância de `Paginator`, assim como na [paginação tradicional de query de model](/pt-br/db/paginator):
```php
    $orders = App\Order::search('Star Trek')->paginate();
```

Você pode especificar quantos models buscar por página passando o número como o primeiro argumento ao método `paginate`:
```php
    $orders = App\Order::search('Star Trek')->paginate(15);
```

Após obter os resultados de busca, você pode usar seu template engine favorito para renderizar os links de paginação para exibir os resultados, assim como na paginação tradicional de query de model:
```php
    <div class="container">
        @foreach ($orders as $order)
            {{ $order->price }}
        @endforeach
    </div>

    {{ $orders->links() }}
```
<a name="custom-engines"></a>
## engine customizado

#### escrever engine

Se o engine de busca embutido do Scout não atender às suas necessidades, você pode escrever um engine customizado e registrá-lo no Scout. Seu engine precisa herdar da classe abstrata `Hyperf\Scout\Engine\Engine`, que contém cinco métodos que seu engine customizado deve implementar:
```php
    use Hyperf\Scout\Builder;

    abstract public function update($models);
    abstract public function delete($models);
    abstract public function search(Builder $builder);
    abstract public function paginate(Builder $builder, $perPage, $page);
    abstract public function map($results, $model);
```
Vai ser útil ver esses métodos na classe `Hyperf\Scout\Engine\ElasticsearchEngine`. Essa classe vai fornecer um bom ponto de partida para você aprender como implementar esses métodos no seu engine customizado.

#### Registrar engine

Depois de escrever seu engine customizado, você pode especificar o engine no arquivo de configuração. Por exemplo, se você escreveu um `MySqlSearchEngine`, você pode escrever isso no arquivo de configuração:
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

- O Hyperf/Scout usa coroutines para sincronizar eficientemente índices de busca e registros de model, sem depender de mecanismos de fila.
- O Hyperf/Scout fornece por padrão o engine Elasticsearch de código aberto em vez do Algolia de código fechado.
