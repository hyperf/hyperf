# Migração de banco de dados

A migração de banco de dados pode ser entendida como o gerenciamento de versões da estrutura do banco de dados, o que pode resolver de forma eficaz a gestão da estrutura do banco entre os membros da equipe.

# Gerar migrações

Gere um arquivo de migração via `gen:migration`. O comando recebe um parâmetro com o nome do arquivo, geralmente indicando o que a migração pretende fazer.

```bash
php bin/hyperf.php gen:migration create_users_table
```

Os arquivos de migração gerados ficam na pasta `migrations` no diretório raiz, e cada arquivo inclui um timestamp para que o sistema de migrações consiga determinar a ordem em que devem ser executadas.

A opção `--table` pode ser usada para especificar o nome da tabela. O nome da tabela especificada será gerado no arquivo de migração por padrão.
A opção `--create` também é usada para especificar o nome da tabela, mas a diferença em relação a `--table` é que ela gera um arquivo de migração para criar uma tabela, enquanto `--table` gera um arquivo para modificar a tabela.

```bash
php bin/hyperf.php gen:migration create_users_table --table=users
php bin/hyperf.php gen:migration create_users_table --create=users
```

# Estrutura da migração

A classe da migração conterá `2` métodos por padrão: `up` e `down`.
O método `up` é usado para adicionar uma nova tabela, campo ou índice no banco de dados, e o método `down` é o inverso do `up`, desfazendo a operação realizada em `up`, de modo que seja executado durante o rollback.

```php
<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Executa as migrações.
     */
    public function up(): void
    {
        Schema::create('true', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
        });
    }

    /**
     * Reverte as migrações.
     */
    public function down(): void
    {
        Schema::dropIfExists('true');
    }
}
```

# Executar migrações

Execute todos os arquivos de migração pendentes executando o comando `migrate`:

```bash
php bin/hyperf.php migrate
```

## Forçar a migração

Algumas operações de migração são destrutivas, o que significa que pode ocorrer perda de dados. Para evitar que alguém execute esses comandos em ambiente de produção, o sistema pedirá confirmação antes de executar, mas se você quiser ignorar essas confirmações e forçar a execução, você pode usar a flag `--force`:

```bash
php bin/hyperf.php migrate --force
```

## Rollback de migrações

Se você quiser fazer rollback da última migração, pode usar o comando `migrate:rollback` para reverter a última migração. Note que uma migração pode conter múltiplos arquivos:

```bash
php bin/hyperf.php migrate:rollback
```

Você também pode definir quantas migrações deseja reverter adicionando o parâmetro `step` ao comando `migrate:rollback`. Por exemplo, o comando abaixo fará rollback das últimas 5 migrações:

```bash
php bin/hyperf.php migrate:rollback --step=5
```

Se você quiser reverter todas as migrações, pode fazer isso com `migrate:reset`:

```bash
php bin/hyperf.php migrate:reset
```

## Rollback e migração

O comando `migrate:refresh` não apenas faz rollback das migrações, como também executa o comando `migrate`, o que reconstrói algumas migrações de forma eficiente:

```bash
php bin/hyperf.php migrate:refresh

// Reconstrói a estrutura do banco de dados e executa o seed de dados
php bin/hyperf.php migrate:refresh --seed
```

Defina a quantidade de rollbacks e reconstruções com o parâmetro `--step`. Por exemplo, o comando abaixo fará rollback e reexecutará as últimas 5 migrações:

```bash
php bin/hyperf.php migrate:refresh --step=5
```

## Reconstruir banco de dados

É possível reconstruir todo o banco de dados de forma eficiente com o comando `migrate:fresh`, que remove todas as tabelas antes de executar o comando `migrate`:

```bash
php bin/hyperf.php migrate:fresh

// Reconstrói a estrutura do banco de dados e executa o seed de dados
php bin/hyperf.php migrate:fresh --seed
```

# Schema

No arquivo de migração, a classe `Hyperf\Database\Schema\Schema` é usada principalmente para definir tabelas e gerenciar o processo de migração.

## Criar tabela

Crie uma nova tabela com o método `create`. O método `create` aceita dois parâmetros: o primeiro é o nome da tabela, e o segundo é um `Closure`, que receberá um objeto `Hyperf\Database\Schema\Blueprint` para definir a nova tabela:

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

Você pode usar os seguintes comandos no gerador de estrutura para definir opções de uma tabela:

```php
// Especifica o engine de armazenamento da tabela
$table->engine = 'InnoDB';
// Especifica o charset padrão das tabelas
$table->charset = 'utf8';
// Especifica a collation padrão da tabela
$table->collation = 'utf8_unicode_ci';
// Cria uma tabela temporária
$table->temporary();
```

## Renomear tabela

Se você quiser renomear uma tabela, pode usar o método `rename`:

```php
Schema::rename($from, $to);
```

### Renomear tabela com chave estrangeira

Antes de renomear uma tabela, verifique se todas as restrições de chave estrangeira na tabela têm um nome explícito no arquivo de migração, em vez de deixar que o sistema de migração atribua um nome por convenção. Caso contrário, o nome da restrição da chave estrangeira continuará referenciando o nome antigo da tabela.

## Remover tabela

Para remover uma tabela existente, use os métodos `drop` ou `dropIfExists`:

```php
Schema::drop('users');

Schema::dropIfExists('users');
```

## Verificar se a tabela ou a coluna existe

Os métodos `hasTable` e `hasColumn` podem ser usados para verificar se uma tabela ou coluna existe:

```php
if (Schema::hasTable('users')) {
    //
}

if (Schema::hasColumn('name', 'email')) {
    //
}
```

## Opções de conexão do banco de dados

Se múltiplos bancos forem gerenciados ao mesmo tempo, diferentes migrações corresponderão a diferentes conexões de banco. Então podemos definir diferentes conexões no arquivo de migração sobrescrevendo o atributo de classe `$connection` da classe pai:

```php
<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    // Isso corresponde à chave de conexão em config/autoload/databases.php
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

# Colunas

## Criar colunas

Defina a criação ou alteração a ser realizada pelo arquivo de migração no `Closure` do segundo parâmetro dos métodos `table` ou `create`. Por exemplo, o código abaixo define uma coluna string `name`:

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

## Métodos disponíveis para definição de colunas

| Comando                                    | Descrição
| ------------------------------------------ | ------------------------------------------------------------------------------- |
| $table->bigIncrements('id');	             |  ID auto incremental (chave primária), equivalente a "UNSIGNED BIG INTEGER"     |
| $table->bigInteger('votes');	             |  equivalente a BIGINT                                                           |
| $table->binary('data');	                 |  equivalente a BLOB                                                             |
| $table->boolean('confirmed');	             |  equivalente a BOOLEAN                                                          |
| $table->char('name', 100);	             |  equivalente a CHAR com tamanho                                                 |
| $table->date('created_at');	             |  equivalente a DATE                                                             |
| $table->dateTime('created_at');	         |  equivalente a DATETIME                                                         |
| $table->dateTimeTz('created_at');	         |  equivalente a DATETIME com fuso horário                                        |
| $table->decimal('amount', 8, 2);	         |  equivalente a DECIMAL com precisão e escala                                    |
| $table->double('amount', 8, 2);	         |  equivalente a DOUBLE com precisão e escala                                     |
| $table->enum('level', ['easy', 'hard']);	 |  equivalente a ENUM                                                             |
| $table->float('amount', 8, 2);	         |  equivalente a FLOAT com precisão e escala                                      |
| $table->geometry('positions');	         |  equivalente a GEOMETRY                                                         |
| $table->geometryCollection('positions');	 |  equivalente a GEOMETRYCOLLECTION                                               |
| $table->increments('id');	                 |  ID auto incremental (chave primária), equivalente a "UNSIGNED INTEGER"         |
| $table->integer('votes');	                 |  equivalente a INTEGER                                                          |
| $table->ipAddress('visitor');	             |  equivalente a endereço IP                                                      |
| $table->json('options');	                 |  equivalente a JSON                                                             |
| $table->jsonb('options');	                 |  equivalente a JSONB                                                            |
| $table->lineString('positions');	         |  equivalente a LINESTRING                                                       |
| $table->longText('description');	         |  equivalente a LONGTEXT                                                         |
| $table->macAddress('device');	             |  equivalente a endereço MAC                                                     |
| $table->mediumIncrements('id');	         |  ID auto incremental (chave primária), equivalente a "UNSIGNED MEDIUM INTEGER"  |
| $table->mediumInteger('votes');	         |  equivalente a MEDIUMINT                                                        |
| $table->mediumText('description');	     |  equivalente a MEDIUMTEXT                                                       |
| $table->morphs('taggable');	             |  equivalente a adicionar taggable_id auto incremental e taggable_type string    |
| $table->multiLineString('positions');	     |  equivalente a MULTILINESTRING                                                  |
| $table->multiPoint('positions');	         |  equivalente a MULTIPOINT                                                       |
| $table->multiPolygon('positions');	     |  equivalente a MULTIPOLYGON                                                     |
| $table->nullableMorphs('taggable');	     |  equivalente à versão nullable do campo morphs()                                |
| $table->nullableTimestamps();	             |  equivalente à versão nullable do campo timestamps()                            |
| $table->point('position');	             |  equivalente a POINT                                                            |
| $table->polygon('positions');	             |  equivalente a POLYGON                                                          |
| $table->rememberToken();	                 |  equivalente à versão nullable de VARCHAR (100) do campo remember_token         |
| $table->smallIncrements('id');	         |  ID auto incremental (chave primária), equivalente a "UNSIGNED SMALL INTEGER"   |
| $table->smallInteger('votes');	         |  equivalente a SMALLINT                                                         |
| $table->softDeletes();	                 |  equivalente a adicionar o campo deleted_at nullable para soft delete           |
| $table->softDeletesTz();	                 |  equivalente a adicionar o campo deleted_at nullable para soft delete com fuso  |
| $table->string('name', 100);	             |  equivalente a VARCHAR com tamanho                                              |
| $table->text('description');	             |  equivalente a TEXT                                                             |
| $table->time('sunrise');	                 |  equivalente a TIME                                                             |
| $table->timeTz('sunrise');	             |  equivalente a TIME com fuso horário                                            |
| $table->timestamp('added_on');	         |  equivalente a TIMESTAMP                                                        |
| $table->timestampTz('added_on');	         |  equivalente a TIMESTAMP com fuso horário                                       |
| $table->timestamps();	                     |  equivalente a created_at e updated_at TIMESTAMP nullable                       |
| $table->timestampsTz();	                 |  equivalente a created_at e updated_at TIMESTAMP nullable com fuso horário      |
| $table->tinyIncrements('id');	             |  equivalente a UNSIGNED TINYINT auto incremental                                |
| $table->tinyInteger('votes');	             |  equivalente a TINYINT                                                          |
| $table->unsignedBigInteger('votes');	     |  equivalente a UNSIGNED BIGINT                                                  |
| $table->unsignedDecimal('amount', 8, 2);	 |  equivalente a UNSIGNED DECIMAL com precisão e escala                           |
| $table->unsignedInteger('votes');	         |  equivalente a UNSIGNED INT                                                     |
| $table->unsignedMediumInteger('votes');	 |  equivalente a UNSIGNED MEDIUMINT                                               |
| $table->unsignedSmallInteger('votes');	 |  equivalente a UNSIGNED SMALLINT                                                |
| $table->unsignedTinyInteger('votes');	     |  equivalente a UNSIGNED TINYINT                                                 |
| $table->uuid('id');	                     |  equivalente a UUID                                                             |
| $table->year('birth_year');	             |  equivalente a YEAR                                                             |
| $table->comment('Table Comment');          |  Define comentário da tabela, equivalente a COMMENT                              |

## Modificar colunas

### Pré-requisitos

Certifique-se de adicionar a dependência `doctrine/dbal` ao arquivo `composer.json` antes de modificar colunas. A biblioteca Doctrine DBAL é usada para determinar o estado atual de uma coluna e criar a query SQL necessária para realizar os ajustes especificados nessa coluna:

```bash
composer require "doctrine/dbal:^3.0"
```

### Atualizar propriedades de colunas

O método `change` pode modificar tipos de colunas existentes para novos tipos ou alterar outras propriedades.

```php
<?php

Schema::create('users', function (Blueprint $table) {
    // Modifica o tamanho da coluna para 50
    $table->string('name', 50)->change();
});
```

Ou modificar a coluna para ser `nullable`:

```php
<?php

Schema::table('users', function (Blueprint $table) {
    // Modifica o tamanho da coluna para 50 e permite null
    $table->string('name', 50)->nullable()->change();
});
```

> Apenas os seguintes tipos de coluna podem ser "modificados": bigInteger, binary, boolean, date, dateTime, dateTimeTz, decimal, integer, json, longText, mediumText, smallInteger, string, text, time, unsignedBigInteger, unsignedInteger e unsignedSmallInteger.

### Renomear coluna

Colunas podem ser renomeadas via o método `renameColumn`:

```php
<?php

Schema::table('users', function (Blueprint $table) {
    // Renomeia a coluna de from para to
    $table->renameColumn('from', 'to')->change();
});
```

> O renomeio de colunas do tipo enum não é suportado atualmente.

### Remover coluna

Colunas podem ser removidas via o método `dropColumn`:

```php
<?php

Schema::table('users', function (Blueprint $table) {
    // Remove a coluna name
    $table->dropColumn('name');
    // Remove múltiplas colunas
    $table->dropColumn(['name', 'age']);
});
```

#### Aliases de comandos disponíveis

| Comando                      | Descrição                                    |
| ---------------------------- | ---------------------------------------------- |
| $table->dropRememberToken(); |  Remove o campo remember_token.               |
| $table->dropSoftDeletes();   |  Remove o campo deleted_at.                   |
| $table->dropSoftDeletesTz(); |  Alias para o método dropSoftDeletes().       |
| $table->dropTimestamps();    |  Remove os campos created_at e updated_at.    |
| $table->dropTimestampsTz();  |  Alias para o método dropTimestamps().        |

## Índices

### Criar índice

### Índice único

Use o método `unique` para criar um índice único:

```php
<?php

// Criar índice no momento da definição
$table->string('name')->unique();
// Criar índices após definir as colunas
$table->unique('name');
```

#### Índice composto

```php
<?php

// Cria um índice composto
$table->index(['account_id', 'created_at'], 'index_account_id_and_created_at');
```

#### Definir o nome do índice

O migrator gera automaticamente um nome de índice razoável, e cada método de índice aceita um segundo argumento opcional para especificar o nome do índice:

```php
<?php

// Define um nome de índice único como unique_name
$table->unique('name', 'unique_name');
// Define um índice composto chamado index_account_id_and_created_at
$table->index(['account_id', 'created_at'], '');
```

##### Tipos de índice disponíveis

| Comando                               | Descrição         |
| ------------------------------------- | ----------------- |
| $table->primary('id');                | Adiciona chave primária  |
| $table->primary(['id', 'parent_id']); | Adiciona chave composta  |
| $table->unique('email');              | Adiciona índice único    |
| $table->index('state');               | Adiciona índice normal   |
| $table->spatialIndex('location');     | Adiciona índice espacial |

### Renomear índice

Você pode renomear um índice com o método `renameIndex`:

```php
<?php

$table->renameIndex('from', 'to');
```

### Excluir índice

Você pode remover um índice da forma a seguir. Por padrão, o migrator concatenará automaticamente o nome do banco, o(s) campo(s) do índice e o tipo do índice como nome. Exemplos:

| Comando                                                | Descrição                                   |
| ------------------------------------------------------ | ------------------------------------------- |
| $table->dropPrimary('users_id_primary');               | Remove a chave primária da tabela users     |
| $table->dropUnique('users_email_unique');              | Remove o índice único da tabela users       |
| $table->dropIndex('geo_state_index');                  | Remove o índice base da tabela geo          |
| $table->dropSpatialIndex('geo_location_spatialindex'); | Remove o índice espacial da tabela geo      |

Você também pode passar um array de campos para o método `dropIndex` e o migrator irá gerar o nome do índice baseado no nome da tabela, nos campos e no tipo da chave:

```php
<?php

Schema:table('users', function (Blueprint $table) {
    $table->dropIndex(['account_id', 'created_at']);
});
```

### Restrições de chave estrangeira

Também podemos criar restrições de chave estrangeira no nível do banco de dados através dos métodos `foreign`, `references`, `on`. Por exemplo, vamos fazer com que a tabela `posts` defina uma coluna `user_id` que referencia a coluna `id` da tabela `users`:

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

Você pode remover chaves estrangeiras com o método `dropForeign`. Restrições de chave estrangeira são nomeadas da mesma forma que índices, com o sufixo `_foreign`:

```php
$table->dropForeign('posts_user_id_foreign');
```

Ou passar um array de campos e deixar que o migrator gere os nomes de acordo com as regras acordadas:

```php
$table->dropForeign(['user_id'']);
```

Você pode ativar ou desativar as restrições de chave estrangeira usando os métodos a seguir no arquivo de migração:

```php
// Ativa restrições de chave estrangeira
Schema::enableForeignKeyConstraints();
// Desativa restrições de chave estrangeira
Schema::disableForeignKeyConstraints();
```
