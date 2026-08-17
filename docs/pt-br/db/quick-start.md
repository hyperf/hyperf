# Início rápido

## Prefácio

> [hyperf/database](https://github.com/hyperf/database) é derivado do [illuminate/database](https://github.com/illuminate/database), fizemos algumas modificações nele, mas a maioria dos métodos permanece igual. Agradecemos à equipe de desenvolvimento do Laravel por implementar um ORM tão poderoso e fácil de usar.

O componente [hyperf/database](https://github.com/hyperf/database) é baseado nos componentes derivados do [illuminate/database](https://github.com/illuminate/database) com algumas mudanças para permitir o uso tanto em frameworks PHP-FPM quanto em frameworks baseados em Swoole. No Hyperf, você precisa usar o componente [hyperf/db-connection](https://github.com/hyperf/db-connection), que implementa um pool de conexões de banco de dados baseado no [hyperf/pool](https://github.com/hyperf/pool). Com ele como ponte, o Hyperf pode integrar conexões de banco de dados e eventos.

## Instalação

### Framework Hyperf

```bash
composer require hyperf/db-connection
```

### Outros frameworks

```bash
composer require hyperf/database
```

## Configuração

A configuração padrão é a seguinte, a configuração suporta múltiplas conexões de banco de dados. A conexão padrão utilizada quando nenhuma conexão é especificada é chamada `default`.

| Nome                 | Tipo   | Valor padrão    | Descrição                                             |
| :------------------: | :----: | :-------------: | :----------------------------------------------------: |
| driver               | string | nenhum          | Tipo do banco de dados                                  |
| host                 | string | nenhum          | Host do banco de dados                                  |
| database             | string | nenhum          | Nome do banco de dados                                  |
| username             | string | nenhum          | Usuário do banco de dados                                |
| password             | string | null            | Senha do banco de dados                                  |
| charset              | string | utf8            | Charset de string do banco de dados                      |
| collation            | string | utf8_unicode_ci | Collation de string do banco de dados                    |
| prefix               | string | ''              | Prefixo das tabelas do banco de dados                     |
| timezone             | string | null            | Timezone do banco de dados                               |
| pool.min_connections | int    | 1               | Número mínimo de conexões no pool de conexões            |
| pool.max_connections | int    | 10              | Número máximo de conexões no pool de conexões            |
| pool.connect_timeout | float  | 10.0            | Timeout de espera da conexão                             |
| pool.wait_timeout    | float  | 3.0             | Tempo de timeout em segundos                             |
| pool.heartbeat       | int    | -1              | Heartbeat da conexão (-1 equivale a desabilitado)        |
| pool.max_idle_time   | float  | 60.0            | Tempo máximo de ociosidade da conexão antes de fechá-la  |
| options              | array  |                 | Opções de configuração do PDO                            |

```php
<?php

return [
    'default' => [
        'driver' => env('DB_DRIVER','mysql'),
        'host' => env('DB_HOST','localhost'),
        'port' => env('DB_PORT', 3306),
        'database' => env('DB_DATABASE','hyperf'),
        'username' => env('DB_USERNAME','root'),
        'password' => env('DB_PASSWORD',''),
        'charset' => env('DB_CHARSET','utf8'),
        'collation' => env('DB_COLLATION','utf8_unicode_ci'),
        'prefix' => env('DB_PREFIX',''),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float)env('DB_MAX_IDLE_TIME', 60),
        ]
    ],
];
```

Às vezes os usuários precisam modificar a configuração padrão do PDO. Por exemplo, se você quiser que todos os campos sejam retornados como strings, você precisa definir o item de configuração do PDO `ATTR_STRINGIFY_FETCHES` como `true`.

```php
<?php

return [
    'default' => [
        'driver' => env('DB_DRIVER','mysql'),
        'host' => env('DB_HOST','localhost'),
        'port' => env('DB_PORT', 3306),
        'database' => env('DB_DATABASE','hyperf'),
        'username' => env('DB_USERNAME','root'),
        'password' => env('DB_PASSWORD',''),
        'charset' => env('DB_CHARSET','utf8'),
        'collation' => env('DB_COLLATION','utf8_unicode_ci'),
        'prefix' => env('DB_PREFIX',''),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('DB_MAX_IDLE_TIME', 60),
        ],
        'options' => [
            // Framework default configuration
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            // If you are using a non-native MySQL or a DB provided by a cloud vendor, such as a database/analytic instance that does not support the MySQL prepare protocol, set this to true
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],
];
```

### Separação de leitura e escrita

Às vezes você quer que a instrução `SELECT` use uma conexão de banco de dados e as instruções `INSERT`, `UPDATE` e `DELETE` usem outra conexão de banco de dados. Isso é fácil de implementar no Hyperf, independentemente de você estar usando uma query nativa, query builder ou model.

Para entender como a separação de leitura e escrita é configurada, vamos primeiro ver um exemplo:

```php
<?php

return [
    'default' => [
        'driver' => env('DB_DRIVER','mysql'),
        'read' => [
            'host' => ['192.168.1.1'],
        ],
        'write' => [
            'host' => ['196.168.1.2'],
        ],
        'sticky' => true,
        'database' => env('DB_DATABASE','hyperf'),
        'username' => env('DB_USERNAME','root'),
        'password' => env('DB_PASSWORD',''),
        'charset' => env('DB_CHARSET','utf8'),
        'collation' => env('DB_COLLATION','utf8_unicode_ci'),
        'prefix' => env('DB_PREFIX',''),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('DB_MAX_IDLE_TIME', 60),
        ],
    ],
];
```

Observe que no exemplo acima, três chaves foram adicionadas ao array de configuração, sendo elas `read`, `write` e `sticky`. As chaves `read` e `write` contêm ambas um array com a chave `host`.

Se você quiser sobrescrever a configuração no array principal, basta modificar os arrays `read` e `write`. Assim, neste exemplo: 192.168.1.1 será usado como host da conexão de "leitura", e 192.168.1.2 será usado como host da conexão de "escrita". As duas conexões compartilharão as diversas configurações do array mysql, como credenciais do banco de dados (usuário/senha), prefixo, codificação de caracteres, etc.

`sticky` é um valor opcional que pode ser usado para ler imediatamente os registros que foram escritos no banco de dados durante o ciclo da requisição atual. Se a opção `sticky` estiver habilitada e uma operação de "escrita" tiver sido executada no ciclo da requisição atual, então qualquer operação de "leitura" usará a conexão de "escrita". Isso garante que os dados escritos no mesmo ciclo de requisição possam ser lidos imediatamente, evitando assim o problema de inconsistência de dados causado pelo delay de replicação master-slave. Porém, se essa opção deve ser habilitada depende das necessidades da aplicação.

### Configurando múltiplas conexões de banco de dados

A configuração multi-banco de dados é a seguinte.

```php
<?php

return [
    'default' => [
        'driver' => env('DB_DRIVER','mysql'),
        'host' => env('DB_HOST','localhost'),
        'database' => env('DB_DATABASE','hyperf'),
        'username' => env('DB_USERNAME','root'),
        'password' => env('DB_PASSWORD',''),
        'charset' => env('DB_CHARSET','utf8'),
        'collation' => env('DB_COLLATION','utf8_unicode_ci'),
        'prefix' => env('DB_PREFIX',''),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('DB_MAX_IDLE_TIME', 60),
        ],
    ],
    'test'=>[
        'driver' => env('DB_DRIVER','mysql'),
        'host' => env('DB_HOST2','localhost'),
        'database' => env('DB_DATABASE','hyperf'),
        'username' => env('DB_USERNAME','root'),
        'password' => env('DB_PASSWORD',''),
        'charset' => env('DB_CHARSET','utf8'),
        'collation' => env('DB_COLLATION','utf8_unicode_ci'),
        'prefix' => env('DB_PREFIX',''),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('DB_MAX_IDLE_TIME', 60),
        ],
    ],
];
```
Para usar conexões diferentes, você só precisa especificar `connection` através do query builder:

```php
<?php

use Hyperf\DbConnection\Db;
// default
Db::select('SELECT * FROM user;');
Db::connection('default')->select('SELECT * FROM user;');

// test
Db::connection('test')->select('SELECT * FROM user;');
```

Você pode alterar a conexão padrão usada por um determinado model definindo o valor de `$connection` dentro da classe do model:

> Observe que a visibilidade da propriedade deve ser definida como `protected`

```php
<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact group@hyperf.io
 * @license https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Model;

/**
 * @property int $id
 * @property string $mobile
 * @property string $realname
 */
class User extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table ='user';

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection ='test';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','mobile','realname'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' =>'integer'];
}
```

## Executando instruções SQL nativas

Após configurar o banco de dados, você pode usar `Hyperf\DbConnection\Db` para consultar.

### Consultando dados

Isso inclui instruções de consulta como `select`, procedures armazenadas e funções que leem dados via SQL.

O método `select` sempre retornará um array, e cada resultado no array é um objeto `StdClass`.

```php
<?php

use Hyperf\DbConnection\Db;

$users = Db::select('SELECT * FROM `user` WHERE gender = ?',[1]); // return array

foreach($users as $user){
    echo $user->name;
}
```

### Modificando dados

Isso inclui instruções de execução como `Insert`, `Update`, `Delete`, e procedures armazenadas que modificam dados via SQL.

```php
<?php

use Hyperf\DbConnection\Db;

$inserted = Db::insert('INSERT INTO user (id, name) VALUES (?, ?)', [1,'Hyperf']); // Returns whether it is successful bool

$affected = Db::update('UPDATE user set name =? WHERE id = ?', ['John', 1]); // Returns the number of affected rows int

$affected = Db::delete('DELETE FROM user WHERE id = ?', [1]); // Returns the number of affected rows int

$result = Db::statement("CALL pro_test(?,'?')", [1,'your words']); // return bool CALL pro_test(?,?) is a stored procedure, the attribute is MODIFIES SQL DATA
```

### Gerenciando transações de banco de dados automaticamente

Você pode usar o método `transaction` do `Db` para executar um conjunto de operações como uma transação de banco de dados. Se ocorrer uma exceção dentro do closure da transação, a transação será revertida (rollback). Se o closure da transação for executado com sucesso, a transação será confirmada (commit) automaticamente. Isso significa que você não precisa se preocupar com rollbacks ou commits ao usar o método `transaction`:

```php
<?php
use Hyperf\DbConnection\Db;

Db::transaction(function () {
    Db::table('user')->update(['votes' => 1]);

    Db::table('posts')->delete();
});

```

### Gerenciando transações de banco de dados manualmente

Se você quiser iniciar uma transação manualmente e ter controle total sobre rollback e commit, você pode usar os métodos `beginTransaction`, `commit`, `rollBack`:

```php
use Hyperf\DbConnection\Db;

Db::beginTransaction();
try{

    // Do something...

    Db::commit();
} catch(\Throwable $ex){
    Db::rollBack();
}
```

## Registrando as queries SQL brutas

> O método atual só pode ser usado em ambiente de desenvolvimento e deve ser removido antes do deploy em produção, caso contrário causará vazamentos de memória sérios e problemas de consistência de dados.

Você pode usar o [listener de eventos do banco de dados](pt-br/db/event) para registrar as queries SQL:

```php
<?php

use Hyperf\DbConnection\Db;
use Hyperf\Collection\Arr;
use App\Model\Book;

// Enable SQL data logging function
// WARNING: causes a memory leak and data consistency problems in the Swoole CLI environment, local development and debugging only!
Db::enableQueryLog();

$book = Book::query()->find(1);

// Print the last SQL query
var_dump(Arr::last(Db::getQueryLog()));
```

## Lista de drivers

Diferente do [illuminate/database](https://github.com/illuminate/database), o [hyperf/database](https://github.com/hyperf/database) fornece apenas o driver MySQL por padrão, e atualmente também fornece os drivers [PgSQL](https://github.com/hyperf/database-pgsql), [SQLite](https://github.com/hyperf/database-sqlite) e [SQL Server](https://github.com/hyperf/database-sqlserver-incubator), entre outros.
Se o mysql padrão não atender às necessidades de uso, você pode instalar o driver correspondente por conta própria.

### Driver PgSql

#### Instalação

Requer `Swoole >= 5.1.0` e a opção `--enable-swoole-pgsql` habilitada na compilação

```bash
composer require hyperf/database-pgsql
```

#### Arquivo de configuração

```php
// config/autoload/databases.php
return [
    // Other configurations
    'pgsql'=> [
        'driver' => env('DB_DRIVER', 'pgsql'),
        'host' => env('DB_HOST', 'localhost'),
        'database' => env('DB_DATABASE', 'hyperf'),
        'port' => env('DB_PORT', 5432),
        'username' => env('DB_USERNAME', 'postgres'),
        'password' => env('DB_PASSWORD'),
        'charset' => env('DB_CHARSET', 'utf8'),
    ]
];
```

### Driver SQLite

#### Instalação

Requer `Swoole >= 5.1.0` e a opção `--enable-swoole-sqlite` habilitada na compilação

```bash
composer require hyperf/database-sqlite
```

#### Arquivo de configuração

```php
// config/autoload/databases.php
return [
    // Other configurations
    'sqlite'=>[
        'driver' => env('DB_DRIVER', 'sqlite'),
        'host' => env('DB_HOST', 'localhost'),
        // :memory: For an in-memory database, you can also specify the absolute path to the file.
        'database' => env('DB_DATABASE', ':memory:'),
        // other sqlite config
    ]
];
```

### Driver SQL Server

#### Instalação

> Em fase de incubação, atualmente não podemos garantir que todas as funcionalidades funcionem corretamente. Feedbacks são bem-vindos.

Requer `Swoole >= 5.1.0` que depende do pdo_odbc, e precisa ser habilitado durante a compilação `--with-swoole-odbc`

```bash
composer require hyperf/database-sqlserver-incubator
```

#### Arquivo de configuração

```php
// config/autoload/databases.php
return [
    // Other configurations
    'sqlserver' => [
        'driver' => env('DB_DRIVER', 'sqlsrv'),
        'host' => env('DB_HOST', 'mssql'),
        'database' => env('DB_DATABASE', 'hyperf'),
        'port' => env('DB_PORT', 1443),
        'username' => env('DB_USERNAME', 'SA'),
        'password' => env('DB_PASSWORD'),
        'odbc_datasource_name' => 'DRIVER={ODBC Driver 18 for SQL Server};SERVER=127.0.0.1,1433;TrustServerCertificate=yes;database=hyperf',
        'odbc'  =>  true,
    ]
];
```
