# Componente DB minimalista

[hyperf/database](https://github.com/hyperf/database) é muito poderoso, mas é inegável que a eficiência pode ser um pouco insuficiente. Aqui está um componente minimalista `hyperf/db`.

## Instalação

```bash
composer require hyperf/db
```

## Publicar configuração do componente

O arquivo de configuração deste componente fica em `config/autoload/db.php`. Se o arquivo não existir, você pode publicar o arquivo de configuração no skeleton com o comando a seguir:

```bash
php bin/hyperf.php vendor:publish hyperf/db
```

## Configuração do componente

A configuração padrão `config/autoload/db.php` é a seguinte. O database suporta configuração de múltiplos bancos de dados; o padrão é `default`.

| Item de configuração   | Tipo   | Padrão             | Observação                                                  |
|:--------------------:|:------:|:------------------:|:-----------------------------------------------------------:|
| driver               | string | nenhum             | O engine do banco de dados                                  |
| host                 | string | `localhost`        | Endereço do banco de dados                                  |
| port                 | int    | 3306               | Porta do banco de dados                                     |
| database             | string | nenhum             | Nome do database padrão                                     |
| username             | string | nenhum             | Usuário do banco de dados                                   |
| password             | string | null               | Senha do banco de dados                                     |
| charset              | string | utf8               | Charset do banco de dados                                   |
| collation            | string | utf8_unicode_ci    | Collation do banco de dados                                 |
| fetch_mode           | int    | `PDO::FETCH_ASSOC` | Tipo do result set das consultas PDO                         |
| pool.min_connections | int    | 1                  | Número mínimo de conexões no pool                            |
| pool.max_connections | int    | 10                 | Número máximo de conexões no pool                            |
| pool.connect_timeout | float  | 10.0               | Timeout de espera de conexão                                 |
| pool.wait_timeout    | float  | 3.0                | Tempo de espera (timeout)                                   |
| pool.heartbeat       | int    | -1                 | Heartbeat                                                   |
| pool.max_idle_time   | float  | 60.0               | Tempo máximo ocioso                                          |
| options              | array  |                    | Configurações do PDO                                        |

## Métodos suportados pelo componente

A interface específica pode ser vista em `Hyperf\DB\ConnectionInterface`.

| Nome do método    | Valor de retorno | Observação                                                                     |
|:----------------:|:---------------:|:------------------------------------------------------------------------------:|
| beginTransaction  | `void`          | Abrir transação (suporta aninhamento de transações)                            |
| commit            | `void`          | Commit da transação (suporta aninhamento de transações)                        |
| rollBack          | `void`          | Rollback da transação (suporta aninhamento de transações)                      |
| insert            | `int`           | Inserir dados; retorna o ID da chave primária; PK não auto-incremental retorna 0 |
| execute           | `int`           | Executar SQL e retornar o número de linhas afetadas                            |
| query             | `array`         | Consultar SQL e retornar uma lista do result set                               |
| fetch             | `array, object` | Consultar SQL e retornar a primeira linha do result set                        |
| connection        | `self`          | Especificar o database a ser conectado                                         |

## Uso

### Usar instância do DB

```php
<?php

use Hyperf\Context\ApplicationContext;
use Hyperf\DB\DB;

$db = ApplicationContext::getContainer()->get(DB::class);

$res = $db->query('SELECT * FROM `user` WHERE gender = ?;', [1]);

```

### Usar métodos estáticos

```php
<?php

use Hyperf\DB\DB;

$res = DB::query('SELECT * FROM `user` WHERE gender = ?;', [1]);

```

### Métodos customizados usando funções anônimas

> Este método permite que usuários operem diretamente o `PDO` ou o `MySQL` subjacente, então você precisa lidar com questões de compatibilidade por conta própria

Por exemplo, se quisermos executar certas consultas e usar diferentes `fetch mode`, podemos customizar nossos métodos das seguintes formas:

```php
<?php
use Hyperf\DB\DB;

$sql = 'SELECT * FROM `user` WHERE id = ?;';
$bindings = [2];
$mode = \PDO::FETCH_OBJ;
$res = DB::run(function (\PDO $pdo) use ($sql, $bindings, $mode) {
    $statement = $pdo->prepare($sql);

    $this->bindValues($statement, $bindings);

    $statement->execute();

    return $statement->fetchAll($mode);
});
```
