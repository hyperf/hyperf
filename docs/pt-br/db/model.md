# Model

O componente de model é derivado do [Eloquent ORM](https://laravel.com/docs/5.8/eloquent), e todas as operações relacionadas podem ser consultadas na documentação do Eloquent ORM.

## Criando um Model

O Hyperf fornece um comando para criar models, permitindo que você crie convenientemente os models correspondentes com base nas suas tabelas do banco de dados. O comando gera models usando `AST`, o que significa que você pode facilmente resetar o model com um script mesmo depois de adicionar certos métodos.

```
php bin/hyperf.php gen:model table_name
```

Os parâmetros opcionais são os seguintes:

|        Parâmetro   |  Tipo  |          Valor padrão             |                       Observação                       |
| :----------------: | :----: | :-------------------------------: | :-----------------------------------------------------: |
|       --pool       | string |             `default`             |       Pool de conexões, o script vai criar com base na configuração do pool atual        |
|       --path       | string |            `app/Model`            |                     Caminho do model                     |
|   --force-casts    |  bool  |              `false`              |             Se deve forçar o reset do atributo `casts`             |
|      --prefix      | string |             ''              |                      Prefixo da tabela                       |
|   --inheritance    | string |              `Model`              |                       Classe pai                        |
|       --uses       | string | `Hyperf\DbConnection\Model\Model` |              Usado em conjunto com `inheritance`              |
| --refresh-fillable |  bool  |              `false`              |             Se deve atualizar o atributo `fillable`              |
|  --table-mapping   | array  |               `[]`                | Mapeamento de nome de tabela para model, por exemplo, ['users:Account'] |
|  --ignore-tables   | array  |               `[]`                |        Tabelas a serem ignoradas na geração do model, por exemplo, ['users']        |
|  --with-comments   |  bool  |              `false`              |                 Se deve adicionar comentários dos campos                 |
|  --property-case   |  int   |                `0`                |             Tipo do campo 0 snake case 1 camel case     |


Ao usar a opção `--property-case` para converter nomes de campos para camelCase, também é necessário incluir manualmente o trait `Hyperf\Database\Model\Concerns\CamelCase` no seu model.

A configuração correspondente também pode ser definida em `databases.{pool}.commands.gen:model` da seguinte forma:

> Todos os hífens precisam ser convertidos em underscores

```php
<?php

declare(strict_types=1);

use Hyperf\Database\Commands\ModelOption;

return [
    'default' => [
        // Ignore other configurations.
        'commands' => [
            'gen:model' => [
                'path' => 'app/Model',
                'force_casts' => true,
                'inheritance' => 'Model',
                'uses' => '',
                'refresh_fillable' => true,
                'table_mapping' => [],
                'with_comments' => true,
                'property_case' => ModelOption::PROPERTY_SNAKE_CASE,
            ],
        ],
    ],
];
```

O model criado é o seguinte:

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

/**
 * @property $id
 * @property $name
 * @property $gender
 * @property $created_at
 * @property $updated_at
 */
class User extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = ['id', 'name', 'gender', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'gender' => 'integer'];
}
```

## Propriedades do Model

| Parâmetros | Tipo | Valor padrão | Observações |
| :----------: | :----: | :-----: | :------------------: |
| connection | string | default | conexão do banco de dados |
| table | string | Nenhum | Nome da tabela de dados |
| primaryKey | string | id | chave primária do model |
| keyType | string | int | tipo da chave primária |
| fillable | array | [] | Propriedades que permitem atribuição em lote |
| casts | string | Nenhum | Configuração de formatação de dados |
| timestamps | bool | true | Se deve manter timestamps automaticamente |
| incrementing | bool | true | Se a chave primária é auto incremento |

### Nomes de tabela

Se não especificarmos a tabela correspondente ao model, ele vai usar a forma plural do nome da classe em 'snake case' como o nome da tabela. Portanto, neste caso, o Hyperf vai assumir que o model User armazena dados na tabela 'users'. Você pode especificar uma tabela customizada definindo a propriedade table no model:

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    protected ?string $table = 'user';
}
```

### Chave primária

O Hyperf vai assumir que toda tabela de dados possui uma coluna de chave primária chamada id. Você pode definir uma propriedade protected $primaryKey para sobrescrever essa convenção.

Além disso, o Hyperf assume que a chave primária é um valor inteiro auto incremento, o que significa que a chave primária é convertida automaticamente para o tipo int por padrão. Se você quiser usar uma chave primária não incremental ou não numérica, você precisa definir a propriedade public $incrementing para false. Se sua chave primária não for um inteiro, você precisa definir a propriedade protected $keyType no model como string.


### Timestamps

Por padrão, o Hyperf espera que sua tabela tenha as colunas `created_at` e `updated_at`. Se você não quiser que o Hyperf gerencie automaticamente essas duas colunas, defina a propriedade `$timestamps` no seu model como `false`:

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    public bool $timestamps = false;
}
```

Se você precisar customizar o formato do timestamp, defina a propriedade `$dateFormat` no seu model. Essa propriedade determina como o atributo de data é armazenado no banco de dados, e como o model é serializado em formato array ou JSON:

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    protected ?string $dateFormat = 'U';
}
```

Se você precisar de um armazenamento que não mantenha o formato `datetime`, ou quiser fazer processamento adicional no tempo, você pode fazer isso sobrescrevendo o método `fromDateTime($value)` no model.

Se você precisar customizar o nome do campo para armazenar timestamps, você pode definir os valores das constantes `CREATED_AT` e `UPDATED_AT` no model. Se uma delas for `null`, isso indica que você não quer que o ORM processe o campo:

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    const CREATED_AT = 'creation_date';

    const UPDATED_AT = 'last_update';
}
```

### Conectividade com o banco de dados

Por padrão, os models do Hyperf vão usar a conexão de banco de dados padrão `default` configurada pela sua aplicação. Se você quiser especificar uma conexão diferente para o model, defina a propriedade `$connection`: Claro, o `connection-name` como `key` deve existir no arquivo de configuração `databases.php`.

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    protected ?string $connection = 'connection-name';
}
```

### Valor padrão de atributo

Se você quiser definir valores padrão para alguns atributos do model, você pode definir o atributo `$attributes` no model:

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    protected array $attributes = [
        'delayed' => false,
    ];
}
```

## Consulta de model

```php
<?php
use App\Model\User;

/** @var User $user */
$user = User::query()->where('id', 1)->first();
$user->name = 'Hyperf';
$user->save();

```

### Recarregando o model

Você pode recarregar o model usando os métodos `fresh` e `refresh`. O método `fresh` vai buscar o model novamente do banco de dados. As instâncias de model existentes não são afetadas:

```php
<?php
use App\Model\User;

/** @var User $user */
$user = User::query()->find(1);

$freshUser = $user->fresh();
```

O método `refresh` revaloriza um model existente com novos dados do banco de dados. Além disso, os relacionamentos já carregados serão recarregados:

```php
<?php
use App\Model\User;

/** @var User $user */
$user = User::query()->where('name','Hyperf')->first();

$user->name = 'Hyperf2';

$user->refresh();

echo $user->name; // Hyperf
```

### Collection

Para os métodos `all` e `get` no model, você pode consultar múltiplos resultados e retornar uma instância `Hyperf\Database\Model\Collection`. A classe `Collection` fornece muitas funções auxiliares para processar resultados de query:

```php
$users = $users->reject(function ($user) {
    // Exclude all deleted users
    return $user->deleted;
});
```

### Recuperando um único model

Além de recuperar todos os registros de uma tabela de dados especificada, você pode usar os métodos `find` ou `first` para recuperar um único registro. Esses métodos retornam uma única instância de model em vez de uma collection de models:

```php
<?php
use App\Model\User;

$user = User::query()->where('id', 1)->first();

$user = User::query()->find(1);
```

### Recuperando múltiplos models

Claro, o método `find` suporta mais do que apenas um único model.

```php
<?php
use App\Model\User;

$users = User::query()->find([1, 2, 3]);
```

### Exceção "não encontrado"

Às vezes você quer lançar uma exceção quando um model não é encontrado, isso é muito útil em controllers e rotas.
Os métodos `findOrFail` e `firstOrFail` vão buscar o primeiro resultado da query, se não for encontrado, uma exceção `Hyperf\Database\Model\ModelNotFoundException` será lançada:

```php
<?php
use App\Model\User;

$model = User::findOrFail(1);
$model = User::where('age', '>', 18)->firstOrFail();
```

### Função de agregação

Você também pode usar as funções de agregação `count`, `sum`, `max`, entre outras, fornecidas pelo query builder. Esses métodos simplesmente retornam o valor escalar apropriado em vez de uma instância de model:

```php
<?php
use App\Model\User;

$count = User::query()->where('gender', 1)->count();
```

## Inserindo e atualizando model

### Insert

Para adicionar um novo registro ao banco de dados, primeiro crie uma nova instância de model, defina as propriedades para a instância, e então chame o método `save`:

```php
use App\Model\User;

/** @var User $user */
$user = new User();

$user->name = 'Hyperf';

$user->save();
```

Neste exemplo, atribuímos um valor à propriedade `name` da instância do model `App\Model\User`. Quando o método `save` é chamado, um novo registro será inserido. Os timestamps `created_at` e `updated_at` serão definidos automaticamente e não requerem atribuição manual.

### Atualização

O método `save` também pode ser usado para atualizar um model existente no banco de dados. Para atualizar o model, você precisa recuperá-lo primeiro, definir as propriedades a serem atualizadas, e então chamar o método `save`. Da mesma forma, o timestamp `updated_at` é atualizado automaticamente, então não há necessidade de atribuí-lo manualmente:

```php
use App\Model\User;

/** @var User $user */
$user = User::query()->find(1);

$user->name = 'Hi Hyperf';

$user->save();
```

### Atualização em lote

Você também pode atualizar múltiplos models que correspondem aos critérios de query. Neste exemplo, para todos os usuários cujo `gender` é `1`, altera `gender_show` para male:

```php
use App\Model\User;

User::query()->where('gender', 1)->update(['gender_show' => 'male']);
```

> Durante a atualização em lote, o model atualizado não vai disparar os eventos `saved` e `updated`. Isso porque durante a atualização em lote, o model não é instanciado. Ao mesmo tempo, os `casts` correspondentes não serão executados. Por exemplo, no formato `json` no banco de dados, o campo `casts` na classe Model é marcado como `array`. Se a atualização em lote for usada, o `array` não será convertido automaticamente durante a inserção. No formato de string `json`.

### Atribuição em lote

Você também pode salvar um novo model usando o método `create`, que retorna uma instância de model. Porém, antes de usá-lo, você precisa especificar o atributo `fillable` ou `guarded` no model, porque todos os models não podem ser atribuídos em lote por padrão.

Quando o usuário passa um parâmetro inesperado através de uma requisição HTTP, e esse parâmetro altera um campo no banco de dados que você não precisa alterar. Por exemplo: um usuário malicioso pode passar o parâmetro `is_admin` através de uma requisição HTTP e então passá-lo para o método `create`. Essa operação permite que o usuário se promova a administrador.

Portanto, antes de começar, você deve definir quais atributos no model podem ser atribuídos em lote. Você pode fazer isso através do atributo `$fillable` no model. Por exemplo: Permita que o atributo `name` do model `User` seja atribuído em lote:

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    protected array $fillable = ['name'];
}
```

Uma vez que tenhamos definido as propriedades que podem ser atribuídas em lote, podemos inserir novos dados no banco de dados através do método `create`. O método `create` vai retornar a instância de model salva:

```php
use App\Model\User;

$user = User::create(['name' => 'Hyperf']);
```

Se você já tem uma instância de model, você pode passar um array para o método fill para atribuir valores:

```php
$user->fill(['name' => 'Hyperf']);
```

### Atributos protegidos

`$fillable` pode ser considerado uma "whitelist" para atribuição em lote, e você também pode usar o atributo `$guarded` para conseguir isso. O atributo `$guarded` contém os arrays para os quais a atribuição em lote não é permitida. Em outras palavras, `$guarded` vai funcionar mais como uma "blacklist". Observação: Você só pode usar `$fillable` ou `$guarded`, não ambos ao mesmo tempo. No exemplo a seguir, exceto pelo atributo `gender_show`, todos os outros atributos podem ser atribuídos em lote:

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    protected $guarded = ['gender_show'];
}
```

### Outros métodos de criação

`firstOrCreate` / `firstOrNew`

Existem dois métodos que você pode usar para atribuição em lote: `firstOrCreate` e `firstOrNew`.

O método `firstOrCreate` vai comparar os dados no banco de dados com a coluna/valor dado. Se o model correspondente não puder ser encontrado no banco de dados, um registro será criado a partir dos atributos do primeiro parâmetro e até dos atributos do segundo parâmetro e inserido no banco de dados.

O método `firstOrNew`, assim como o método `firstOrCreate`, tenta encontrar um registro no banco de dados pelo atributo dado. A diferença é que se o método `firstOrNew` não conseguir encontrar o model correspondente, ele vai retornar uma nova instância de model. Observe que a instância de model retornada por `firstOrNew` ainda não foi salva no banco de dados. Você precisa chamar manualmente o método `save` para salvá-la:

```php
<?php
use App\Model\User;

// Find the user by name, create it if it does not exist...
$user = User::firstOrCreate(['name' => 'Hyperf']);

// Find the user by name. If it does not exist, use the name and gender, age attributes to create...
$user = User::firstOrCreate(
    ['name' => 'Hyperf'],
    ['gender' => 1, 'age' => 20]
);

// Find the user by name, create an instance if it does not exist...
$user = User::firstOrNew(['name' => 'Hyperf']);

// Find the user by name. If it does not exist, use the name and gender, age attributes to create an instance...
$user = User::firstOrNew(
    ['name' => 'Hyperf'],
    ['gender' => 1, 'age' => 20]
);
```

### Excluindo model

O método `delete` pode ser chamado em uma instância de model para excluir a instância:

```php
use App\Model\User;

$user = User::query()->find(1);

$user->delete();
```

### Excluindo model por query

Você pode excluir os dados do model chamando o método `delete` na query, neste exemplo vamos excluir todos os usuários cujo `gender` é `1`. Assim como a atualização em lote, a exclusão em lote não dispara nenhum evento de model para o model excluído:

```php
use App\Model\User;

// Note that when using the delete method, certain query conditions must be established to safely delete data. If there is no where condition, the entire data table will be deleted.
User::query()->where('gender', 1)->delete(); 
```

### Excluindo dados diretamente pela chave primária

No exemplo acima, você precisa encontrar o model correspondente no banco de dados antes de chamar `delete`. Na verdade, se você souber a chave primária do model, você pode excluir os dados do model diretamente através do método estático `destroy` sem precisar buscá-lo primeiro no banco de dados. Além de aceitar uma única chave primária como parâmetro, o método `destroy` também aceita múltiplas chaves primárias, ou usa um array ou collection para armazenar múltiplas chaves primárias:

```php
use App\Model\User;

User::destroy(1);

User::destroy([1,2,3]);
```

### Soft delete

Além de excluir de fato os registros do banco de dados, o `Hyperf` também pode fazer "soft delete" de models. Um model com soft delete não é de fato excluído do banco de dados. Na verdade, o atributo `deleted_at` é definido no model e seu valor é gravado no banco de dados. Se o valor de `deleted_at` for não vazio, isso significa que o model foi excluído por soft delete. Se você quiser habilitar o soft delete do model, você precisa usar o trait `Hyperf\Database\Model\SoftDeletes` no model

> O trait `SoftDeletes` vai converter automaticamente o atributo `deleted_at` em uma instância `DateTime / Carbon`

```php
<?php

namespace App\Model;

use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\SoftDeletes;

class User extends Model
{
    use SoftDeletes;
}
```

O método `restoreOrCreate` vai comparar os dados no banco de dados com a coluna/valor dado. Se o model correspondente for encontrado no banco de dados, execute o método `restore` para restaurar o model, caso contrário um registro será criado a partir dos atributos do primeiro parâmetro e até dos atributos do segundo parâmetro e inserido no banco de dados.

```php
// Look up users by name, create them with name and gender, age attributes if they don't exist...
$user = User::restoreOrCreate(
    ['name' => 'Hyperf'],
    ['gender' => 1, 'age' => 20]
);
```

## Tipo Bit

Por padrão, ao converter o model do banco de dados no Hyperf para SQL, os valores dos parâmetros serão uniformemente convertidos para o tipo String para resolver o problema de int em números grandes e facilitar a correspondência de tipos de valor com índices. Se você quiser que o `ORM` suporte o tipo `bit`, basta adicionar o seguinte código de listener de evento.

```php
<?php

declare(strict_types=1);

namespace App\Listener;

use Hyperf\Database\Connection;
use Hyperf\Database\MySqlBitConnection;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;

#[Listener]
class SupportMySQLBitListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event)
    {
        Connection::resolverFor('mysql', static function ($connection, $database, $prefix, $config) {
            return new MySqlBitConnection($connection, $database, $prefix, $config);
        });
    }
}

```
