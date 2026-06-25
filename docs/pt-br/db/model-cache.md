# Cache de model

Em cenários de alta frequência, consultamos o banco de dados constantemente. Mesmo com o benefício da chave primária, isso ainda afeta a performance do banco. Com este método de consulta KV, podemos usar facilmente `model cache` para reduzir a pressão no banco de dados. Este módulo implementa cache automático. Ao deletar e modificar o model, o cache é deletado automaticamente. Ao incrementar e decrementar, operamos diretamente no cache para executar os incrementos/decrementos correspondentes.

> O model cache suporta temporariamente armazenamento em `Redis`; outros engines de armazenamento serão adicionados gradualmente.

## Instalação

```bash
composer require hyperf/model-cache
```

## Configurar

O cache de model é configurado em `databases`. Exemplos:

| Configuração       | Tipo   | Padrão                                         | Observações                                                       |
|:-----------------:|:------:|:----------------------------------------------:|:-----------------------------------------------------------------:|
| handler           | string | Hyperf\DbConnection\Cache\Handler\RedisHandler::class | nenhum                                                       |
| cache_key         | string | `mc:%s:m:%s:%s:%s`                             | `mc:prefixo do cache:m:nome da tabela:chave primária:valor da chave primária` |
| prefix            | string | nome da conexão do banco de dados               | prefixo do cache                                                  |
| pool              | string | default                                         | pool do cache                                                     |
| ttl               | int    | 3600                                            | tempo limite                                                      |
| empty_model_ttl   | int    | 60                                              | Timeout quando nenhum dado é consultado                           |
| load_script       | bool   | true                                            | Se deve usar evalSha em vez de eval no Redis                      |
| use_default_value | bool   | false                                           | Se deve usar valores padrão do banco de dados                     |

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

O uso do model cache é bem simples. Você só precisa implementar a interface `Hyperf\ModelCache\CacheableInterface` no Model correspondente. Claro, o framework já fornece a implementação correspondente: você só precisa usar o Trait `Hyperf\ModelCache\Cacheable`.

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
     * A tabela associada ao model.
     *
     * @var string
     */
    protected $table = 'user';

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array
     */
    protected $fillable = ['id', 'name', 'gender', 'created_at', 'updated_at'];

    protected $casts = ['id' => 'integer', 'gender' => 'integer'];
}

// Consulta um cache único
$model = User::findFromCache($id);

// Consulta em lote; retorna Hyperf\Database\Model\Collection
$models = User::findManyFromCache($ids);

```

Os dados correspondentes no Redis são os seguintes, onde `HF-DATA:DEFAULT` existe como um placeholder no `HASH`; *portanto, usuários não devem usar `HF-DATA` como um campo no banco de dados*.

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

Outro ponto é que o mecanismo de atualização de cache implementa, no framework, o listener `Hyperf\ModelCache\Listener\DeleteCacheListener`. Sempre que os dados são modificados, o cache é deletado ativamente.
Se o usuário não quiser que o framework delete o cache, ele pode sobrescrever ativamente o método `deleteCache` e então implementar o monitoramento correspondente por conta própria.

### Editar ou deletar em lote

`Hyperf\ModelCache\Cacheable` assume automaticamente o método `Model::query`. O usuário só precisa deletar dados das seguintes formas para limpar automaticamente o cache correspondente.

```php
<?php
// Deleta dados do usuário no banco e o framework deletará automaticamente o cache correspondente.
User::query(true)->where('gender', '>', 1)->delete();
```

### Usar valor padrão

Quando o model cache é usado em produção, se o cache correspondente já tiver sido criado, mas nesse momento novos campos forem adicionados por mudanças de lógica e seus valores padrão não forem `0`, `null character`, `null` e dados semelhantes, então quando os dados forem consultados, os dados obtidos do cache ficarão inconsistentes com os dados do banco.

Para esse caso, podemos modificar `use_default_value` para `true` e adicionar `Hyperf\DbConnection\Listener\InitTableCollectorListener` na configuração `listener.php` para que a aplicação Hyperf obtenha ativamente as informações de campos do banco ao iniciar. E então, ao obter o cache, comparar e corrigir os dados em cache.

### Controlar tempo de cache nos models

Além do tempo padrão de cache `ttl` configurado em `database.php`, `Hyperf\ModelCache\Cacheable` suporta configurar um tempo de cache mais detalhado para o model:

```php
class User extends Model implements CacheableInterface
{
    use Cacheable;
    
    /**
     * Cache por 10 minutos. Se retornar null, o timeout do arquivo de configuração será usado.
     * @return int|null
     */
    public function getCacheTTL(): ?int
    {
        return 600;
    }
}
```

### EagerLoad

Ao usar relacionamentos de model, conseguimos resolver o problema `N+1` via `load`, mas ainda precisamos consultar o banco uma vez. O model cache reescreve `ModelBuilder` para permitir que usuários obtenham o model correspondente do cache o máximo possível.

> Este recurso não suporta `morphTo` e models relacionais que não tenham apenas consultas `whereIn`.

São fornecidos dois métodos:

1. Configure o EagerLoadListener e use o método `loadCache` diretamente.

Modifique a configuração `listeners.php`:

```php
return [
    Hyperf\ModelCache\Listener\EagerLoadListener::class,
];
```

Carregue o relacionamento correspondente via método `loadCache`.

```php
$books = Book::findManyFromCache([1,2,3]);
$books->loadCache(['user']);

foreach ($books as $book){
    var_dump($book->user);
}
```

2. Usar EagerLoader

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

### Cache adapter

Você pode implementar um cache adapter de acordo com a sua situação e só precisa implementar a interface `Hyperf\ModelCache\Handler\HandlerInterface`.

O framework fornece dois Handlers para escolher:

- Hyperf\ModelCache\Handler\RedisHandler

Usar `HASH` para armazenar o cache pode lidar bem com `Model::increment()`. A desvantagem é que, como o tipo de dados é apenas `String`, ele tem suporte ruim a `null`.

- Hyperf\ModelCache\Handler\RedisStringHandler

Usa `String` para armazenar o cache. Como é dado serializado, ele suporta todos os tipos de dados. A desvantagem é que ele não consegue lidar bem com `Model::increment()`. Quando o model chama operações de incremento, o problema de consistência é resolvido deletando o cache.
