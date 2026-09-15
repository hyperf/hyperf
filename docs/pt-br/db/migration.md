# Migração de banco de dados

A migração de banco de dados pode ser entendida como o versionamento da estrutura do banco de dados, o que pode resolver efetivamente a gestão da estrutura do banco de dados entre os membros da equipe.

# Gerar migrations

Gere um arquivo de migration via `gen:migration`, o comando é seguido por um parâmetro de nome de arquivo, geralmente indicando o que a migration se destina a fazer.

```bash
php bin/hyperf.php gen:migration create_users_table
```

Os arquivos de migration gerados estão localizados na pasta `migrations` no diretório raiz, e cada arquivo de migration inclui um timestamp para que o programa de migração possa determinar a ordem das migrations.

A opção `--table` pode ser usada para especificar o nome da tabela de dados. O nome de tabela especificado será gerado no arquivo de migration por padrão.
A opção `--create` também é usada para especificar o nome da tabela de dados, mas a diferença em relação a `--table` é que essa opção gera um arquivo de migration para criar uma tabela, enquanto `--table` é um arquivo de migration para modificar a tabela.

```bash
php bin/hyperf.php gen:migration create_users_table --table=users
php bin/hyperf.php gen:migration create_users_table --create=users
```

# Estrutura da migration

A classe de migration vai conter `2` métodos por padrão: `up` e `down`.
O método `up` é usado para adicionar uma nova tabela de dados, campo ou índice ao banco de dados, e o método `down` é o inverso do método `up`, ou seja, o oposto da operação em `up`, para que seja executado durante o rollback.

```php
<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('true', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('true');
    }
}
```

# Executar migration

Execute todos os arquivos de migration pendentes executando o comando `migrate`:

```bash
php bin/hyperf.php migrate
```

## Forçar a migração

Algumas operações de migration são destrutivas, o que significa que pode resultar em perda de dados. Para evitar que alguém execute esses comandos em um ambiente de produção, o sistema vai confirmar com você antes desses comandos serem executados, mas se você quiser ignorar essas confirmações, forçando a execução de um comando, você pode usar a flag `--force`:

```bash
php bin/hyperf.php migrate --force
```

## Rollback de migration

Se você quiser reverter a última migration, você pode usar o comando `migrate:rollback` para reverter a última migration. Observe que uma migration pode conter múltiplos arquivos de migration:

```bash
php bin/hyperf.php migrate:rollback
```

Você também pode definir o número de migrations a serem revertidas anexando o parâmetro `step` ao comando `migrate:rollback`. Por exemplo, o seguinte comando vai reverter as últimas 5 migrations:

```bash
php bin/hyperf.php migrate:rollback --step=5
```

Se você quiser reverter todas as migrations, você pode fazer isso com `migrate:reset`:

```bash
php bin/hyperf.php migrate:reset
```

## Rollback & Migrate

O comando `migrate:refresh` não só reverte a migration, mas também executa o comando `migrate`, que reconstrói algumas migrations de forma eficiente:

```bash
php bin/hyperf.php migrate:refresh

// Rebuild database structure and perform data population
php bin/hyperf.php migrate:refresh --seed
```

Especifique o número de rollbacks e reconstruções com o parâmetro `--step`. Por exemplo, o seguinte comando vai reverter e reexecutar as últimas 5 migrations:

```bash
php bin/hyperf.php migrate:refresh --step=5
```

## Reconstruir banco de dados

O banco de dados inteiro pode ser reconstruído de forma eficiente com o comando `migrate:fresh`, que exclui todos os bancos de dados antes de executar o comando `migrate`:

```bash
php bin/hyperf.php migrate:fresh

// Rebuild database structure and perform data population
php bin/hyperf.php migrate:fresh --seed
```

# Schema

No arquivo de migration, a classe `Hyperf\Database\Schema\Schema` é usada principalmente para definir a tabela de dados e gerenciar o processo de migração.

## Criar tabela

Crie uma nova tabela de banco de dados com o método `create`. O método `create` aceita dois parâmetros: o primeiro parâmetro é o nome da tabela de dados, e o segundo parâmetro é um `Closure`, que vai receber um objeto `Hyperf\Database\Schema\Blueprint` para definir a nova tabela de dados:

```php
<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
        });
    }
}
```

Você pode usar os seguintes comandos no gerador de estrutura de banco de dados para definir opções para uma tabela:

```php
// Specify the table storage engine
$table->engine = 'InnoDB';
// Specifies the default character set for data tables
$table->charset = 'utf8';
// Specifies the default collation of the data table
$table->collation = 'utf8_unicode_ci';
// Create a temporary table
$table->temporary();
```

## Renomear tabela

Se você quiser renomear uma tabela de dados, você pode usar o método `rename`:

```php
Schema::rename($from, $to);
```

### Renomear tabela com chave estrangeira

Antes de renomear uma tabela, você deve verificar se todas as restrições de chave estrangeira na tabela têm um nome explícito no arquivo de migration, em vez de deixar o programa de migração definir um nome por convenção, caso contrário, o nome da restrição de chave estrangeira vai se referir ao nome antigo da tabela.

## Excluir tabela

Para excluir uma tabela existente, use os métodos `drop` ou `dropIfExists`:

```php
Schema::drop('users');

Schema::dropIfExists('users');
```

## Verificar se a tabela de dados ou campo existe

Os métodos `hasTable` e `hasColumn` podem ser usados para verificar se uma tabela de dados ou campo existe:

```php
if (Schema::hasTable('users')) {
    //
}

if (Schema::hasColumn('name', 'email')) {
    //
}
```

## Opções de conexão de banco de dados

Se múltiplos bancos de dados são gerenciados ao mesmo tempo, diferentes migrations vão corresponder a diferentes conexões de banco de dados, então podemos definir diferentes conexões de banco de dados no arquivo de migration sobrescrevendo o atributo de classe `$connection` da classe pai:

```php
<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    // This corresponds to the connection key in config/autoload/databases.php
    protected $connection = 'foo';
    
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
        });
    }
}
```

# Campos

## Criar campos

Defina a definição ou alteração a ser realizada pelo arquivo de migration dentro do `Closure` do segundo parâmetro do método `table` ou `create`. Por exemplo, o código a seguir define um campo string de `name`:

```php
<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{   
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('name');
        });
    }
}
```

## Métodos de definição de campos disponíveis

| Comando                                    | Descrição
| ------------------------------------------ | ------------------------------------------------------------------------------- |
| $table->bigIncrements('id');	             |  ID auto incremento (chave primária), equivalente a "UNSIGNED BIG INTEGER"               |
| $table->bigInteger('votes');	             |  equivalente a BIGINT                                                           |
| $table->binary('data');	                 |  equivalente a BLOB                                                             |
| $table->boolean('confirmed');	             |  equivalente a BOOLEAN                                                          |
| $table->char('name', 100);	             |  equivalente a CHAR com tamanho                                                 |
| $table->date('created_at');	             |  equivalente a DATE                                                             |
| $table->dateTime('created_at');	         |  equivalente a DATETIME                                                         |
| $table->dateTimeTz('created_at');	         |  equivalente a DATETIME com timezone                                          |
| $table->decimal('amount', 8, 2);	         |  equivalente a DECIMAL com precisão e base                                  |
| $table->double('amount', 8, 2);	         |  equivalente a DOUBLE com precisão e base                                   |
| $table->enum('level', ['easy', 'hard']);	 |  equivalente a ENUM                                                             |
| $table->float('amount', 8, 2);	         |  equivalente a FLOAT com precisão e base                                    |
| $table->geometry('positions');	         |  equivalente a GEOMETRY                                                         |
| $table->geometryCollection('positions');	 |  equivalente a GEOMETRYCOLLECTION                                               |
| $table->increments('id');	                 |  ID auto incremento (chave primária), equivalente a "UNSIGNED INTEGER"                |
| $table->integer('votes');	                 |  equivalente a INTEGER                                                          |
| $table->ipAddress('visitor');	             |  equivalente a endereço IP                                                       |
| $table->json('options');	                 |  equivalente a JSON                                                             |
| $table->jsonb('options');	                 |  equivalente a JSONB                                                            |
| $table->lineString('positions');	         |  equivalente a LINESTRING                                                       |
| $table->longText('description');	         |  equivalente a LONGTEXT                                                         |
| $table->macAddress('device');	             |  equivalente a endereço MAC                                                      |
| $table->mediumIncrements('id');	         |  ID auto incremento (chave primária), equivalente a "UNSIGNED MEDIUM INTEGER"            |
| $table->mediumInteger('votes');	         |  equivalente a MEDIUMINT                                                        |
| $table->mediumText('description');	     |  equivalente a MEDIUMTEXT                                                       |
| $table->morphs('taggable');	             |  equivalente a adicionar taggable_id incremental e taggable_type string          |
| $table->multiLineString('positions');	     |  equivalente a MULTILINESTRING                                                  |
| $table->multiPoint('positions');	         |  equivalente a MULTIPOINT                                                       |
| $table->multiPolygon('positions');	     |  equivalente a MULTIPOLYGON                                                     |
| $table->nullableMorphs('taggable');	     |  equivalente à versão nullable do campo morphs()                                  |
| $table->nullableTimestamps();	             |  equivalente à versão nullable do campo timestamps()                              |
| $table->point('position');	             |  equivalente a POINT                                                            |
| $table->polygon('positions');	             |  equivalente a POLYGON                                                          |
| $table->rememberToken();	                 |  equivalente à versão nullable de VARCHAR (100) do campo remember_token           |
| $table->smallIncrements('id');	         |  ID auto incremento (chave primária), equivalente a "UNSIGNED SMALL INTEGER"             |
| $table->smallInteger('votes');	         |  equivalente a SMALLINT                                                         |
| $table->softDeletes();	                 |  equivalente a adicionar um campo deleted_at nullable para soft delete                  |
| $table->softDeletesTz();	                 |  equivalente a adicionar um campo deleted_at nullable com timezone para soft delete   |
| $table->string('name', 100);	             |  equivalente a VARCHAR com tamanho                                              |
| $table->text('description');	             |  equivalente a TEXT                                                             |
| $table->time('sunrise');	                 |  equivalente a TIME                                                             |
| $table->timeTz('sunrise');	             |  equivalente a TIME com timezone                                           |
| $table->timestamp('added_on');	         |  equivalente a TIMESTAMP                                                        |
| $table->timestampTz('added_on');	         |  equivalente a TIMESTAMP com timezone                                         |
| $table->timestamps();	                     |  equivalente a TIMESTAMP nullable created_at e updated_at                     |
| $table->timestampsTz();	                 |  equivalente a TIMESTAMP nullable com timezone created_at e updated_at       |
| $table->tinyIncrements('id');	             |  equivalente a UNSIGNED TINYINT auto incremento                                  |
| $table->tinyInteger('votes');	             |  equivalente a TINYINT                                                          |
| $table->unsignedBigInteger('votes');	     |  equivalente a UNSIGNED BIGINT                                                  |
| $table->unsignedDecimal('amount', 8, 2);	 |  equivalente a UNSIGNED DECIMAL com precisão e base                         |
| $table->unsignedInteger('votes');	         |  equivalente a UNSIGNED INT                                                     |
| $table->unsignedMediumInteger('votes');	 |  equivalente a UNSIGNED MEDIUMINT                                               |
| $table->unsignedSmallInteger('votes');	 |  equivalente a UNSIGNED SMALLINT                                                |
| $table->unsignedTinyInteger('votes');	     |  equivalente a UNSIGNED TINYINT                                                 |
| $table->uuid('id');	                     |  equivalente a UUID                                                             |
| $table->year('birth_year');	             |  equivalente a YEAR                                                             |
| $table->comment('Table Comment');          |  Define o comentário da tabela, equivalente a COMMENT                                       |

## Modificar campos

### Pré-requisitos

Certifique-se de adicionar a dependência `doctrine/dbal` ao arquivo `composer.json` antes de modificar os campos. A biblioteca Doctrine DBAL é usada para determinar o estado atual de um campo e criar a query SQL necessária para fazer os ajustes especificados nesse campo:

```bash
composer require "doctrine/dbal:^3.0"
```

### Atualizar propriedades de campo

O método `change` pode modificar tipos de campos existentes para novos tipos ou modificar outras propriedades.

```php
<?php

Schema::create('users', function (Blueprint $table) {
    // Modify the length of the field to 50
    $table->string('name', 50)->change();
});
```

Ou modifique o campo para ser `nullable`:

```php
<?php

Schema::table('users', function (Blueprint $table) {
    // Modify the length of the field to 50 and allow null
    $table->string('name', 50)->nullable()->change();
});
```

> Apenas os seguintes tipos de campo podem ser "modificados": bigInteger, binary, boolean, date, dateTime, dateTimeTz, decimal, integer, json, longText, mediumText, smallInteger, string, text, time, unsignedBigInteger, unsignedInteger e unsignedSmallInteger.

### Renomear campo

Campos podem ser renomeados através do método `renameColumn`:

```php
<?php

Schema::table('users', function (Blueprint $table) {
    // Rename field from from to to
    $table->renameColumn('from', 'to')->change();
});
```

> A renomeação de campos do tipo enum atualmente não é suportada.

### Excluir campo

Campos podem ser removidos através do método `dropColumn`:

```php
<?php

Schema::table('users', function (Blueprint $table) {
    // Remove the name field
    $table->dropColumn('name');
    // Delete multiple fields
    $table->dropColumn(['name', 'age']);
});
```

#### Aliases de comando disponíveis

| Comando                      | Descrição                                    |
| ---------------------------- | ---------------------------------------------- |
| $table->dropRememberToken(); |  Remove o campo remember_token.              |
| $table->dropSoftDeletes();   |  Exclui o campo deleted_at.                  |
| $table->dropSoftDeletesTz(); |  Alias para o método dropSoftDeletes().       |
| $table->dropTimestamps();    |  Exclui os campos created_at e updated_at.  |
| $table->dropTimestampsTz();  |  Alias para o método dropTimestamps().        |

## Índice

### Criar índice

### Índice único
Use o método `unique` para criar um índice único:

```php
<?php

// Create index at definition time
$table->string('name')->unique();
// Create indexes after fields are defined
$table->unique('name');
```

#### Índice composto

```php
<?php

// Create a compound index
$table->index(['account_id', 'created_at'], 'index_account_id_and_created_at');
```

#### Definir nome do índice

O migrator gera automaticamente um nome de índice razoável, e cada método de índice aceita um segundo argumento opcional para especificar o nome do índice:

```php
<?php

// Define a unique index name as unique_name
$table->unique('name', 'unique_name');
// Define a composite index named index_account_id_and_created_at
$table->index(['account_id', 'created_at'], '');
```

##### Tipos de índice disponíveis

| Comando                               | Descrição       |
| ------------------------------------- | ----------------- |
| $table->primary('id');                | Adiciona chave primária   |
| $table->primary(['id', 'parent_id']); | Adiciona chave composta |
| $table->unique('email');              | Adiciona índice único  |
| $table->index('state');               | Adiciona índice normal  |
| $table->spatialIndex('location');     | Adiciona índice espacial |

### Renomear índice

Você pode renomear um índice com o método `renameIndex`:

```php
<?php

$table->renameIndex('from', 'to');
```

### Excluir índice

Você pode excluir um índice da seguinte forma. Por padrão, o programa de migração vai concatenar automaticamente o nome do banco de dados, o nome do campo do índice, e o tipo de índice como o nome. Exemplos são os seguintes:

| Comando                                                | Descrição                               |
| ------------------------------------------------------ | ----------------------------------------- |
| $table->dropPrimary('users_id_primary');               | Remove a chave primária da tabela users |
| $table->dropUnique('users_email_unique');              | Remove o índice único da tabela users        |
| $table->dropIndex('geo_state_index');                  | Remove o índice base da tabela geo            |
| $table->dropSpatialIndex('geo_location_spatialindex'); | Remove o índice espacial da tabela geo |

Você também pode passar um array de campos ao método `dropIndex` e o migrator vai gerar um nome de índice com base no nome da tabela, campo e tipo de chave:

```php
<?php

Schema:table('users', function (Blueprint $table) {
    $table->dropIndex(['account_id', 'created_at']);
});
```

### Restrições de chave estrangeira

Também podemos criar restrições de chave estrangeira na camada do banco de dados através dos métodos `foreign`, `references`, `on`. Por exemplo, vamos fazer com que a tabela `posts` defina um campo `user_id` que referencia o campo `id` da tabela `users`:

```php
Schema::table('posts', function (Blueprint $table) {
    $table->unsignedInteger('user_id');

    $table->foreign('user_id')->references('id')->on('users');
});
```

Você também pode especificar a ação desejada para as propriedades `on delete` e `on update`:

```php
$table->foreign('user_id')
      ->references('id')->on('users')
      ->onDelete('cascade');
```

Você pode excluir chaves estrangeiras com o método `dropForeign`. Restrições de chave estrangeira são nomeadas da mesma forma que os índices, seguidas por um sufixo `_foreign`:

```php
$table->dropForeign('posts_user_id_foreign');
```

Ou passe um array de campos e faça o migrator gerar os nomes de acordo com as regras estabelecidas:

```php
$table->dropForeign(['user_id'']);
```

Você pode ativar ou desativar restrições de chave estrangeira usando os seguintes métodos no arquivo de migration:

```php
// Enable foreign key constraints
Schema::enableForeignKeyConstraints();
// Disable foreign key constraints
Schema::disableForeignKeyConstraints();
```
