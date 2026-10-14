# Início rápido

## Prefácio

> [hyperf/database](https://github.com/hyperf/database) é derivado de [illuminate/database](https://github.com/illuminate/database). Fizemos algumas modificações, mas a maioria dos métodos permanece a mesma. Agradecemos à equipe de desenvolvimento do Laravel por implementar um componente ORM tão poderoso e fácil de usar.

O componente [hyperf/database](https://github.com/hyperf/database) é baseado nos componentes derivados de [illuminate/database](https://github.com/illuminate/database), com algumas alterações para permitir o uso tanto em frameworks PHP-FPM quanto em frameworks baseados em Swoole. No Hyperf, você precisa usar o componente [hyperf/db-connection](https://github.com/hyperf/db-connection), que implementa um pool de conexões de banco de dados baseado em [hyperf/pool](https://github.com/hyperf/pool). Com ele como ponte, o Hyperf pode integrar conexões e eventos de banco de dados.

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

A configuração padrão é a seguir. Ela suporta configurar múltiplas conexões de banco de dados. A conexão padrão usada quando nenhuma conexão é especificada se chama `default`.

| Nome                 | Tipo   | Valor padrão     | Descrição                                                   |
| :------------------: | :----: | :-------------:  | :---------------------------------------------------------: |
| driver               | string | nenhum           | Tipo de banco de dados                                      |
| host                 | string | nenhum           | Host do banco de dados                                      |
| database             | string | nenhum           | Nome do banco de dados                                      |
| username             | string | nenhum           | Usuário do banco de dados                                   |
| password             | string | null             | Senha do banco de dados                                     |
| charset              | string | utf8             | Charset do banco de dados                                   |
| collation            | string | utf8_unicode_ci  | Collation do banco de dados                                 |
| prefix               | string | ''               | Prefixo de tabelas do banco de dados                         |
| timezone             | string | null             | Timezone do banco de dados                                  |
| pool.min_connections | int    | 1                | Número mínimo de conexões no pool                            |
| pool.max_connections | int    | 10               | Número máximo de conexões no pool                            |
| pool.connect_timeout | float  | 10.0             | Timeout de espera por conexão                                |
| pool.wait_timeout    | float  | 3.0              | Tempo de espera (timeout) em segundos                        |
| pool.heartbeat       | int    | -1               | Heartbeat da conexão (-1 equivale a desabilitado)            |
| pool.max_idle_time   | float  | 60.0             | Tempo máximo ocioso antes de fechar a conexão                |
| options              | array  |                  | Opções de configuração do PDO                                |

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

Às vezes os usuários precisam modificar a configuração padrão do PDO. Por exemplo, se você quiser retornar todos os campos como strings, precisa definir o item de configuração do PDO `ATTR_STRINGIFY_FETCHES` como `true`.

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
            // Configuração padrão do framework
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            // Se você estiver usando um MySQL não-nativo ou um DB fornecido por um cloud vendor, como uma instância de banco/analítica que não suporta o protocolo MySQL prepare, defina como true
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],
];
```

### Separação de leitura e escrita

Às vezes você quer que o `SELECT` use uma conexão de banco e que `INSERT`, `UPDATE` e `DELETE` usem outra. Isso é fácil de implementar no Hyperf, independentemente de você estar usando query nativa, query builder ou model.

Para entender como a separação de leitura/escrita é configurada, primeiro vamos ver um exemplo:

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

Note que no exemplo acima foram adicionadas três chaves no array de configuração: `read`, `write` e `sticky`. As chaves `read` e `write` contêm um array com a chave `host`.

Se você quiser sobrescrever a configuração do array principal, só precisa modificar os arrays `read` e `write`. Então, neste exemplo: 192.168.1.1 será usado como host da conexão de "leitura", e 192.168.1.2 será usado como host da conexão de "escrita". As duas conexões compartilharão várias configurações do array mysql, como credenciais (username/password), prefixo, codificação de caracteres, etc.

`sticky` é um valor opcional que pode ser usado para ler imediatamente os registros que foram gravados no banco durante o ciclo da requisição atual. Se a opção `sticky` estiver habilitada e uma operação de "escrita" tiver sido realizada no ciclo da requisição atual, então qualquer operação de "leitura" usará a conexão de "escrita". Isso garante que os dados gravados no mesmo ciclo possam ser lidos imediatamente, evitando problemas de inconsistência causados por atraso de replicação master-slave. Entretanto, se essa opção deve estar habilitada depende das necessidades da aplicação.

### Configurar múltiplas conexões de banco de dados

Um exemplo de configuração multi-database é:

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

Para usar conexões diferentes, você só precisa especificar `connection` via query builder:

```php
<?php

use Hyperf\DbConnection\Db;
// default
Db::select('SELECT * FROM user;');
Db::connection('default')->select('SELECT * FROM user;');

// test
Db::connection('test')->select('SELECT * FROM user;');
```

Você pode alterar a conexão padrão usada por um model definindo o valor de `$connection` dentro da classe do model:

> Note que a visibilidade da propriedade deve ser `protected`

```php
<?php

declare(strict_types=1);
/**
 * Este arquivo faz parte do Hyperf.
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
     * A tabela associada ao model.
     *
     * @var string
     */
    protected $table ='user';

    /**
     * O nome da conexão do model.
     *
     * @var string
     */
    protected $connection ='test';

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array
     */
    protected $fillable = ['id','mobile','realname'];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     *
     * @var array
     */
    protected $casts = ['id' =>'integer'];
}
```

## Executar instruções SQL nativas

Após configurar o banco de dados, você pode usar `Hyperf\DbConnection\Db` para consultar.

### Consultar dados

Isso inclui instruções como `select`, stored procedures e funções que leem dados via SQL.

O método `select` sempre retornará um array, e cada item do array é um objeto `StdClass`.

```php
<?php

use Hyperf\DbConnection\Db;
```php
$users = Db::select('SELECT * FROM `user` WHERE gender = ?',[1]); // retorna um array
```
foreach($users as $user){
    echo $user->name;
}
```

### Modificar dados

Isso inclui instruções como `Insert`, `Update`, `Delete` e stored procedures que modificam dados via SQL.

```php
<?php

use Hyperf\DbConnection\Db;

$inserted = Db::insert('INSERT INTO user (id, name) VALUES (?, ?)', [1,'Hyperf']); // retorna se teve sucesso (bool)

$affected = Db::update('UPDATE user set name =? WHERE id = ?', ['John', 1]); // retorna o número de linhas afetadas (int)

$affected = Db::delete('DELETE FROM user WHERE id = ?', [1]); // retorna o número de linhas afetadas (int)

$result = Db::statement("CALL pro_test(?,'?')", [1,'your words']); // retorna bool; CALL pro_test(?,?) é uma stored procedure, o atributo é MODIFIES SQL DATA (modifica dados SQL)
```

### Gerenciar transações automaticamente

Você pode usar o método `transaction` de `Db` para executar um conjunto de operações como uma transação de banco. Se ocorrer uma exception dentro do closure da transação, ela será revertida (rollback). Se o closure for executado com sucesso, a transação será commitada automaticamente. Isso significa que você não precisa se preocupar com rollback ou commit ao usar o método `transaction`:

```php
<?php
use Hyperf\DbConnection\Db;

Db::transaction(function () {
    Db::table('user')->update(['votes' => 1]);

    Db::table('posts')->delete();
});

```

### Gerenciar transações manualmente

Se você quiser iniciar manualmente uma transação e ter controle total sobre rollback e commit, pode usar os métodos `beginTransaction`, `commit`, `rollBack`:

```php
use Hyperf\DbConnection\Db;

Db::beginTransaction();
try{

    // Faça algo...

    Db::commit();
} catch(\Throwable $ex){
    Db::rollBack();
}
```

## Registrar (logar) consultas SQL brutas

> O método atual só pode ser usado em ambiente de desenvolvimento e deve ser removido antes de subir para produção, caso contrário causará graves vazamentos de memória e problemas de consistência de dados.

Você pode usar o [listener de eventos do database](pt-br/db/event.md) para registrar as consultas SQL:

```php
<?php

use Hyperf\DbConnection\Db;
use Hyperf\Collection\Arr;
use App\Model\Book;

// Habilita a função de log de SQL
// ATENÇÃO: causa vazamento de memória e problemas de consistência de dados no ambiente Swoole CLI; use apenas para desenvolvimento e depuração local!
Db::enableQueryLog();

$book = Book::query()->find(1);

// Imprime a última consulta SQL
var_dump(Arr::last(Db::getQueryLog()));
```

## Lista de drivers

Diferente de [illuminate/database](https://github.com/illuminate/database), o [hyperf/database](https://github.com/hyperf/database) fornece apenas o driver MySQL por padrão e atualmente também oferece [PgSQL](https://github.com/hyperf/database-pgsql), [SQLite](https://github.com/hyperf/database-sqlite) e [SQL Server](https://github.com/hyperf/database-sqlserver-incubator), entre outros drivers.
Se o mysql padrão não atender às necessidades, você pode instalar o driver correspondente por conta própria.

### Driver PgSQL

#### Instalação

Requer `Swoole >= 5.1.0` e que `--enable-swoole-pgsql` esteja habilitado na compilação.

```bash
composer require hyperf/database-pgsql
```

#### Arquivo de configuração

```php
// config/autoload/databases.php
return [
    // Outras configurações
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

Requer `Swoole >= 5.1.0` e que `--enable-swoole-sqlite` esteja habilitado na compilação.

```bash
composer require hyperf/database-sqlite
```

#### Arquivo de configuração

```php
// config/autoload/databases.php
return [
    // Outras configurações
    'sqlite'=>[
        'driver' => env('DB_DRIVER', 'sqlite'),
        'host' => env('DB_HOST', 'localhost'),
        // :memory: Para um banco de dados em memória, você também pode especificar o caminho absoluto para o arquivo.
        'database' => env('DB_DATABASE', ':memory:'),
        // outras configurações do sqlite
    ]
];
```

### Driver SQL Server

#### Instalação

> Em estágio de incubação; no momento não podemos garantir que todas as funcionalidades funcionarão corretamente. Feedbacks são bem-vindos.

Requer `Swoole >= 5.1.0`, depende de pdo_odbc e precisa estar habilitado na compilação via `--with-swoole-odbc`.

```bash
composer require hyperf/database-sqlserver-incubator
```

#### Arquivo de configuração

```php
// config/autoload/databases.php
return [
    // Outras configurações
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
