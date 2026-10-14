# Modelo

O componente de model é derivado do [Eloquent ORM](https://laravel.com/docs/5.8/eloquent), e todas as operações relacionadas podem consultar a documentação do Eloquent ORM.

## Criar um Model

O Hyperf fornece um comando para criar models, permitindo criar convenientemente os models correspondentes com base nas tabelas do seu banco de dados. O comando gera models usando `AST`, o que significa que você consegue redefinir o model com um script facilmente mesmo após adicionar alguns métodos.

```
php bin/hyperf.php gen:model table_name
```

Os parâmetros opcionais são:

|        Parâmetro   |  Tipo  |              Valor padrão         |                       Observação                        |
| :----------------: | :----: | :-------------------------------: | :-----------------------------------------------------: |
|       --pool       | string |             `default`             | Pool de conexão; o script criará com base no pool atual |
|       --path       | string |            `app/Model`            | Caminho do model                                        |
|   --force-casts    |  bool  |              `false`              | Se deve redefinir à força o atributo `casts`            |
|      --prefix      | string |             ''                    | Prefixo de tabelas                                      |
|   --inheritance    | string |              `Model`              | Classe pai                                              |
|       --uses       | string | `Hyperf\DbConnection\Model\Model` | Usado em conjunto com `inheritance`                     |
| --refresh-fillable |  bool  |              `false`              | Se deve atualizar o atributo `fillable`                 |
|  --table-mapping   | array  |               `[]`                | Mapeamento de nome de tabela para model, ex.: ['users:Account'] |
|  --ignore-tables   | array  |               `[]`                | Tabelas a ignorar na geração, ex.: ['users']            |
|  --with-comments   |  bool  |              `false`              | Se deve adicionar comentários dos campos                |
|  --property-case   |  int   |                `0`                | Tipo do campo: 0 snake, 1 camel                         |

Ao usar a opção `--property-case` para converter nomes de campos para camelCase, também é necessário incluir manualmente o trait `Hyperf\Database\Model\Concerns\CamelCase` no seu model.

A configuração correspondente também pode ser definida em `databases.{pool}.commands.gen:model`, da seguinte forma:

> Todos os hífens precisam ser convertidos em underscores

```php
<?php

declare(strict_types=1);

use Hyperf\Database\Commands\ModelOption;

return [
    'default' => [
        // Ignore outras configurações.
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

O model criado é:

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
     * A tabela associada ao model.
     *
     * @var string
     */
    protected ?string $table = 'user';

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array
     */
    protected array $fillable = ['id', 'name', 'gender', 'created_at', 'updated_at'];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     *
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'gender' => 'integer'];
}
```

## Variáveis membro do model

| Parâmetros  | Tipo   | Valor padrão | Observações                                 |
| :---------: | :----: | :----------: | :-----------------------------------------: |
| connection  | string | default      | conexão de banco de dados                   |
| table       | string | nenhum        | nome da tabela                              |
| primaryKey  | string | id           | chave primária do model                     |
| keyType     | string | int          | tipo da chave primária                      |
| fillable    | array  | []           | propriedades que permitem atribuição em massa |
| casts       | string | nenhum        | configuração de formatação de dados         |
| timestamps  | bool   | true         | se deve manter timestamps automaticamente   |
| incrementing| bool   | true         | se a chave primária deve ser auto incremental |

### Nome da tabela

Se não especificarmos a tabela correspondente ao model, ele usará a forma plural do nome da classe em 'snake case' como nome da tabela. Portanto, nesse caso, o Hyperf assumirá que o model User armazena dados na tabela 'users'. Você pode especificar uma tabela customizada definindo a propriedade table no model:

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

O Hyperf assume que toda tabela tem uma coluna de chave primária chamada id. Você pode definir uma propriedade protected $primaryKey para sobrescrever essa convenção.

Além disso, o Hyperf assume que a chave primária é um valor inteiro auto incremental, o que significa que a chave primária é convertida automaticamente para int por padrão. Se você quiser usar uma chave primária não incremental ou não numérica, defina a propriedade public $incrementing como false. Se sua chave primária não for um inteiro, você precisa definir a propriedade protected $keyType no model como string.

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

Se você precisar customizar o formato de timestamp, defina a propriedade `$dateFormat` no seu model. Essa propriedade determina como o atributo de data é armazenado no banco de dados, e como o model é serializado em array ou JSON:

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

Se você precisar de um armazenamento que não queira manter o formato `datetime`, ou quiser fazer processamento adicional do tempo, isso pode ser feito sobrescrevendo o método `fromDateTime($value)` no model.

Se você precisar customizar o nome do campo para armazenar timestamps, pode definir os valores das constantes `CREATED_AT` e `UPDATED_AT` no model. Se uma delas for `null`, isso indica que você não quer que o ORM processe esse campo:

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

Por padrão, models do Hyperf usarão a conexão padrão `default` configurada pela sua aplicação. Se você quiser especificar uma conexão diferente para o model, defina a propriedade `$connection`: é claro, o `connection-name` como `key` precisa existir no arquivo de configuração `databases.php`.

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

### Valor padrão de atributos

Se você quiser definir valores padrão para alguns atributos do model, pode definir o atributo `$attributes` no model:

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

## Consultas com model

```php
<?php
use App\Model\User;

/** @var User $user */
$user = User::query()->where('id', 1)->first();
$user->name = 'Hyperf';
$user->save();

```

### Recarregar model

Você pode recarregar o model usando os métodos `fresh` e `refresh`. O método `fresh` buscará o model novamente no banco. Instâncias existentes do model não são afetadas:

```php
<?php
use App\Model\User;

/** @var User $user */
$user = User::query()->find(1);

$freshUser = $user->fresh();
```

O método `refresh` reatribui um model existente com novos dados do banco. Além disso, relacionamentos já carregados serão recarregados:

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

Para os métodos `all` e `get`, você pode consultar múltiplos resultados e retornar uma instância `Hyperf\Database\Model\Collection`. A classe `Collection` fornece várias funções auxiliares para processar resultados:

```php
$users = $users->reject(function ($user) {
    // Exclui todos os usuários deletados
    return $user->deleted;
});
```

### Recuperar um único model

Além de recuperar todos os registros de uma tabela, você pode usar os métodos `find` ou `first` para recuperar um único registro. Esses métodos retornam uma instância única de model, em vez de uma collection de models:

```php
<?php
use App\Model\User;

$user = User::query()->where('id', 1)->first();

$user = User::query()->find(1);
```

### Recuperar múltiplos models

Claro, o método `find` suporta mais de um model.

```php
<?php
use App\Model\User;

$users = User::query()->find([1, 2, 3]);
```

### Exception de "não encontrado"

Às vezes você quer lançar uma exception quando um model não é encontrado; isso é muito útil em controllers e rotas.
Os métodos `findOrFail` e `firstOrFail` recuperarão o primeiro resultado da query e, se não encontrado, uma exception `Hyperf\Database\Model\ModelNotFoundException` será lançada:

```php
<?php
use App\Model\User;

$model = User::findOrFail(1);
$model = User::where('age', '>', 18)->firstOrFail();
```

### Função de agregação

Você também pode usar `count`, `sum`, `max` e outras funções de agregação fornecidas pelo query builder. Esses métodos simplesmente retornam o valor escalar apropriado, em vez de uma instância de model:

```php
<?php
use App\Model\User;

$count = User::query()->where('gender', 1)->count();
```

## Inserir e atualizar model

### Inserir

Para adicionar um novo registro no banco, primeiro crie uma nova instância de model, defina propriedades na instância e então chame o método `save`:

```php
use App\Model\User;

/** @var User $user */
$user = new User();

$user->name = 'Hyperf';

$user->save();
```

Neste exemplo, atribuímos um valor à propriedade `name` da instância `App\Model\User`. Quando o método `save` é chamado, um novo registro será inserido. Os timestamps `created_at` e `updated_at` serão definidos automaticamente, sem necessidade de atribuição manual.

### Atualizar

O método `save` também pode ser usado para atualizar um model existente no banco. Para atualizar o model, você precisa recuperá-lo primeiro, definir as propriedades que devem ser atualizadas e então chamar `save`. Da mesma forma, o timestamp `updated_at` é atualizado automaticamente, então não é necessário atribuí-lo manualmente:

```php
use App\Model\User;

/** @var User $user */
$user = User::query()->find(1);

$user->name = 'Hi Hyperf';

$user->save();
```

### Atualização em lote

Você também pode atualizar múltiplos models que correspondam aos critérios da query. Neste exemplo, para todos os usuários cujo `gender` é `1`, altere `gender_show` para male:

```php
use App\Model\User;

User::query()->where('gender', 1)->update(['gender_show' => 'male']);
```

> Durante atualização em lote, o model atualizado não acionará os eventos `saved` e `updated`, pois o model não é instanciado. Ao mesmo tempo, o `casts` correspondente não será executado. Por exemplo, no formato `json` no banco, o campo `casts` do Model é marcado como `array`. Se for usada atualização em lote, o `array` não será convertido automaticamente durante a inserção para string `json`.

### Atribuição em massa

Você também pode salvar um novo model usando o método `create`, que retorna uma instância do model. Entretanto, antes de usá-lo, você precisa especificar o atributo `fillable` ou `guarded` no model, porque por padrão nenhum model permite atribuição em massa.

Quando o usuário passa um parâmetro inesperado via uma requisição HTTP, e esse parâmetro altera um campo no banco que você não precisava mudar. Por exemplo: um usuário malicioso pode passar o parâmetro `is_admin` via HTTP e então passá-lo para o método `create`. Essa operação permite que o usuário se promova a administrador.

Portanto, antes de começar, você deve definir quais atributos no model podem ser atribuídos em massa. Você pode fazer isso via o atributo `$fillable`. Por exemplo: permitir que o atributo `name` do model `User` seja atribuído em massa:

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

Depois de definir os atributos que podem ser atribuídos em massa, podemos inserir dados no banco via o método `create`. O método `create` retornará a instância do model salva:

```php
use App\Model\User;

$user = User::create(['name' => 'Hyperf']);
```

Se você já tem uma instância de model, pode passar um array para o método fill para atribuir valores:

```php
$user->fill(['name' => 'Hyperf']);
```

### Atributos protegidos

`$fillable` pode ser visto como uma "whitelist" para atribuição em massa, e você também pode usar o atributo `$guarded` para isso. O atributo `$guarded` contém um array de campos para os quais não é permitida atribuição em massa. Em outras palavras, `$guarded` funciona mais como uma "blacklist". Nota: você só pode usar um entre `$fillable` ou `$guarded`, não ambos ao mesmo tempo. No exemplo abaixo, exceto pelo atributo `gender_show`, todos os demais atributos podem ser atribuídos em massa:

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

Existem dois métodos que você pode usar para criação com atribuição em massa: `firstOrCreate` e `firstOrNew`.

O método `firstOrCreate` tentará encontrar um registro no banco com base na coluna/valor fornecidos. Se o model correspondente não puder ser encontrado, um registro será criado a partir dos atributos do primeiro parâmetro e também dos atributos do segundo parâmetro, e então inserido no banco.

O método `firstOrNew`, assim como `firstOrCreate`, tenta encontrar um registro no banco pelos atributos fornecidos. A diferença é que, se não encontrar, ele retornará uma nova instância de model. Note que a instância retornada por `firstOrNew` ainda não foi salva no banco. Você precisa chamar manualmente o método `save` para salvar:

```php
<?php
use App\Model\User;

// Busca o usuário por name; cria se não existir...
$user = User::firstOrCreate(['name' => 'Hyperf']);

// Busca o usuário por name. Se não existir, usa os atributos name, gender e age para criar...
$user = User::firstOrCreate(
    ['name' => 'Hyperf'],
    ['gender' => 1, 'age' => 20]
);

// Busca o usuário por name; cria uma instância se não existir...
$user = User::firstOrNew(['name' => 'Hyperf']);

// Busca o usuário por name. Se não existir, usa os atributos name, gender e age para criar uma instância...
$user = User::firstOrNew(
    ['name' => 'Hyperf'],
    ['gender' => 1, 'age' => 20]
);
```

### Deletar model

O método `delete` pode ser chamado em uma instância do model para deletar a instância:

```php
use App\Model\User;

$user = User::query()->find(1);

$user->delete();
```

### Deletar model por query

Você pode deletar dados do model chamando o método `delete` na query. Neste exemplo, vamos deletar todos os usuários cujo `gender` é `1`. Assim como em atualização em lote, a deleção em lote não dispara eventos do model deletado:

```php
use App\Model\User;

// Note que ao usar delete, certas condições de query devem ser estabelecidas para deletar com segurança. Se não houver where, toda a tabela será deletada.
User::query()->where('gender', 1)->delete(); 
```

### Deletar dados diretamente pela chave primária

No exemplo acima, você precisa encontrar o model correspondente no banco antes de chamar `delete`. Na prática, se você sabe a chave primária do model, pode deletar diretamente via o método estático `destroy` sem precisar buscar no banco. Além de aceitar uma única chave primária como parâmetro, `destroy` também aceita múltiplas chaves primárias, ou um array/collection com múltiplas chaves:

```php
use App\Model\User;

User::destroy(1);

User::destroy([1,2,3]);
```

### Soft delete

Além de deletar registros de fato, o `Hyperf` também pode fazer "soft delete" de models. Um model com soft delete não é realmente removido do banco. Na verdade, o atributo `deleted_at` é definido no model e seu valor é escrito no banco. Se o valor de `deleted_at` não estiver vazio, significa que o model foi soft deleted. Se você quiser habilitar soft delete, precisa usar o trait `Hyperf\Database\Model\SoftDeletes` no model.

> O trait `SoftDeletes` converterá automaticamente o atributo `deleted_at` em uma instância `DateTime / Carbon`

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

O método `restoreOrCreate` tentará encontrar um registro no banco com base na coluna/valor fornecidos. Se o model correspondente for encontrado, executará o método `restore` para restaurar o model. Caso contrário, um registro será criado a partir dos atributos do primeiro parâmetro e também dos atributos do segundo parâmetro, e então inserido no banco.

```php
// Busca usuários por name; cria com os atributos name, gender e age se não existir...
$user = User::restoreOrCreate(
    ['name' => 'Hyperf'],
    ['gender' => 1, 'age' => 20]
);
```

## Tipo bit

Por padrão, ao converter o model do banco no Hyperf para SQL, valores de parâmetros serão convertidos de forma uniforme para String para resolver o problema de int em números grandes e facilitar que tipos de valores correspondam a índices. Se você quiser que o `ORM` suporte o tipo `bit`, basta adicionar o seguinte código de listener:

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
