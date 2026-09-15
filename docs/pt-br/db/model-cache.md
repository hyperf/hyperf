# Model cache

Em cenários de alta frequência, vamos consultar o banco de dados frequentemente. Embora haja o benefício da chave primária, isso também vai afetar a performance do banco de dados. Com esse método de query kv, podemos facilmente usar o `model cache` para reduzir a pressão no banco de dados. Este módulo implementa cache automático. Ao excluir e modificar o model, o cache é excluído automaticamente. Ao acumular e subtrair, opere diretamente o cache para realizar a acumulação e subtração correspondentes.

> O model cache atualmente suporta armazenamento em `Redis`, outros engines de armazenamento serão adicionados gradualmente.

## Instalação

```bash
composer require hyperf/model-cache
```

## Configurar

O cache de model é configurado em `databases`. Exemplos são os seguintes

|   Configuração   |  Tipo  |                         Padrão                        |                Observações                                           |
|:-----------------:|:------:|:------------------------------------------------------:|:----------------------------------------------------------------:|
| handler           | string | Hyperf\DbConnection\Cache\Handler\RedisHandler::class  |                               nenhuma                               |
| cache_key         | string |                   `mc:%s:m:%s:%s:%s`                   | `mc:prefixo do cache:m:nome da tabela:KEY da chave primária:valor da chave primária` |
| prefix            | string |                   nome da conexão do banco de dados                   |                           prefixo do cache                           |
| pool              | string |                        default                         |                           pool de cache                             |
| ttl               |  int   |                          3600                          |                              timeout                             |
| empty_model_ttl   |  int   |                           60                           |                  Timeout quando nenhum dado é consultado                 |
| load_script       |  bool  |                          true                          |   Se deve usar evalSha em vez de eval no engine Redis  |
| use_default_value |  bool  |                          false                         |              Se deve usar os valores padrão do banco de dados              |

```php
<?php

return [
    'default' => [
        'driver' => env('DB_DRIVER', 'mysql'),
        'host' => env('DB_HOST', 'localhost'),
        'database' => env('DB_DATABASE', 'hyperf'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => env('DB_CHARSET', 'utf8'),
        'collation' => env('DB_COLLATION', 'utf8_unicode_ci'),
        'prefix' => env('DB_PREFIX', ''),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float)env('DB_MAX_IDLE_TIME', 60),
        ],
        'cache' => [
            'handler' => \Hyperf\DbConnection\Cache\Handler\RedisHandler::class,
            'cache_key' => 'mc:%s:m:%s:%s:%s',
            'prefix' => 'default',
            'ttl' => 3600 * 24,
            'empty_model_ttl' => 3600,
            'load_script' => true,
            'use_default_value' => false,
        ]
    ],
];
```

## Uso

O uso do model cache é muito simples. Você só precisa implementar a interface `Hyperf\ModelCache\CacheableInterface` no Model correspondente. Claro, o framework já fornece a implementação correspondente, você só precisa importar a Trait `Hyperf\ModelCache\Cacheable`.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\DbConnection\Model\Model;
use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property $id
 * @property $name
 * @property $gender
 * @property $created_at
 * @property $updated_at
 */
class User extends Model implements CacheableInterface
{
    use Cacheable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'name', 'gender', 'created_at', 'updated_at'];

    protected $casts = ['id' => 'integer', 'gender' => 'integer'];
}

// Query a single cache
$model = User::findFromCache($id);

// Batch query cache, return Hyperf\Database\Model\Collection
$models = User::findManyFromCache($ids);

```

Os dados correspondentes no Redis são os seguintes, onde `HF-DATA:DEFAULT` existe como um placeholder no `HASH`, *portanto os usuários não devem usar `HF-DATA` como um campo do banco de dados*.
```
127.0.0.1:6379> hgetall "mc:default:m:user:id:1"
 1) "id"
 2) "1"
 3) "name"
 4) "Hyperf"
 5) "gender"
 6) "1"
 7) "created_at"
 8) "2018-01-01 00:00:00"
 9) "updated_at"
10) "2018-01-01 00:00:00"
11) "HF-DATA"
12) "DEFAULT"
```

Outro ponto é que o mecanismo de atualização de cache implementa o listener correspondente `Hyperf\ModelCache\Listener\DeleteCacheListener` no framework. Sempre que os dados são modificados, o cache será excluído ativamente.
Se o usuário não quiser que o framework exclua o cache, ele pode sobrescrever ativamente o método `deleteCache`, e então implementar o monitoramento correspondente por conta própria.

### Editar ou excluir em lote

`Hyperf\ModelCache\Cacheable` vai automaticamente assumir o controle do método `Model::query`. O usuário só precisa excluir os dados das seguintes formas para limpar automaticamente os dados de cache correspondentes.

```php
<?php
// Delete user data from the database and the framework will automatically delete the corresponding cached data.
User::query(true)->where('gender', '>', 1)->delete();
```

### Usar valor padrão

Quando o model cache é usado em ambiente de produção, se os dados de cache correspondentes já foram estabelecidos, mas neste momento novos campos são adicionados devido a mudanças lógicas, e os valores padrão não são `0`, `caractere nulo`, `null` e outros dados desse tipo, quando os dados são consultados, os dados recuperados do cache serão inconsistentes com os dados no banco de dados.

Para essa situação, podemos modificar `use_default_value` para `true` e adicionar `Hyperf\DbConnection\Listener\InitTableCollectorListener` à configuração `listener.php`, para que a aplicação Hyperf possa obter ativamente as informações de campos do banco de dados quando iniciar. E compará-las com os dados em cache ao obtê-los e corrigir os dados em cache.

### Controlar o tempo de cache nos models

Além do tempo de cache padrão `ttl` configurado em `database.php`, `Hyperf\ModelCache\Cacheable` suporta configurar um tempo de cache mais detalhado para o model:

```php
class User extends Model implements CacheableInterface
{
    use Cacheable;
    
    /**
     * Cache for 10 minutes. If null is returned, the timeout set in the configuration file will be used.
     * @return int|null
     */
    public function getCacheTTL(): ?int
    {
        return 600;
    }
}
```

### EagerLoad

Quando usamos relacionamentos de model, podemos resolver o problema `N+1` através de `load`, mas ainda precisamos verificar o banco de dados uma vez. O model cache sobrescreve o `ModelBuilder` para permitir que os usuários obtenham o model correspondente do cache tanto quanto possível.

> Essa funcionalidade não suporta `morphTo` e models relacionais que não têm apenas queries `whereIn`.

Dois métodos são fornecidos abaixo:

1. Configure o EagerLoadListener e use o método `loadCache` diretamente.

Modifique a configuração `listeners.php`

```php
return [
    Hyperf\ModelCache\Listener\EagerLoadListener::class,
];
```

Carregue o relacionamento de model correspondente através do método `loadCache`.

```php
$books = Book::findManyFromCache([1,2,3]);
$books->loadCache(['user']);

foreach ($books as $book){
    var_dump($book->user);
}
```

2. Use o EagerLoader

```php
use Hyperf\ModelCache\EagerLoad\EagerLoader;
use Hyperf\Context\ApplicationContext;

$books = Book::findManyFromCache([1,2,3]);
$loader = ApplicationContext::getContainer()->get(EagerLoader::class);
$loader->load($books, ['user']);

foreach ($books as $book){
    var_dump($book->user);
}
```

### Adaptador de cache

Você pode implementar o adaptador de cache de acordo com sua situação real, e você só precisa implementar a interface `Hyperf\ModelCache\Handler\HandlerInterface`.

O framework fornece dois Handlers para escolher:

- Hyperf\ModelCache\Handler\RedisHandler

Usar `HASH` para armazenar o cache pode tratar efetivamente `Model::increment()`. A desvantagem é que, como o tipo de dado é apenas `String`, tem pouco suporte para `null`.

- Hyperf\ModelCache\Handler\RedisStringHandler

Use `String` para armazenar o cache. Como são dados serializados, suporta todos os tipos de dados. A desvantagem é que não pode tratar efetivamente `Model::increment()`. Quando o model chama a acumulação, o problema de consistência é resolvido excluindo o cache.
