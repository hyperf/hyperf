# Componente DB minimalista

O [hyperf/database](https://github.com/hyperf/database) tem funcionalidades muito poderosas, mas é inegável que a eficiência é, de fato, um pouco insuficiente. Aqui está um componente `hyperf/db` minimalista.

## Instalação

```bash
composer require hyperf/db
```

## Publicar a configuração do componente

O arquivo de configuração deste componente está localizado em `config/autoload/db.php`. Se o arquivo não existir, você pode publicar o arquivo de configuração para o skeleton com o seguinte comando:

```bash
php bin/hyperf.php vendor:publish hyperf/db
```

## Configuração do componente

A configuração padrão `config/autoload/db.php` é a seguinte, o banco de dados suporta configuração multi-banco de dados, o padrão é `default`.

|  item de configuração  |  tipo  |       padrão      |                          observação                          |
|:--------------------:|:------:|:------------------:|:--------------------------------------------------------:|
|        driver        | string |        nenhum        |                    O engine do banco de dados                   |
|         host         | string |    `localhost`     |                      endereço do banco de dados                    |
|         port         |  int   |        3306        |                      endereço do banco de dados                    |
|       database       | string |        nenhum        |                    Banco de dados padrão                   |
|       username       | string |        nenhum        |                     usuário do banco de dados                    |
|       password       | string |        null        |                     senha do banco de dados                    |
|       charset        | string |        utf8        |                     charset do banco de dados                     |
|      collation       | string |  utf8_unicode_ci   |                    collation do banco de dados                    |
|      fetch_mode      |  int   | `PDO::FETCH_ASSOC` |                 Tipo do result set de query do PDO                |
| pool.min_connections |  int   |         1          | O número mínimo de conexões no pool de conexões |
| pool.max_connections |  int   |         10         | O número máximo de conexões no pool de conexões |
| pool.connect_timeout | float  |        10.0        |                  timeout de espera da conexão                 |
|  pool.wait_timeout   | float  |        3.0         |                      tempo de timeout                       |
|    pool.heartbeat    |  int   |         -1         |                        heartbeat                         |
|  pool.max_idle_time  | float  |        60.0        |                    tempo máximo de ociosidade                     |
|       options        | array  |                    |                      configuração do PDO                       |

## Métodos suportados pelo componente

A interface específica pode ser vista em `Hyperf\DB\ConnectionInterface`.

|    nome do método   |  valor de retorno  |                                       observação                                        |
|:----------------:|:--------------:|:-----------------------------------------------------------------------------------:|
| beginTransaction |     `void`     |                    Abre a transação (Suporta aninhamento de transações)                   |
|      commit      |     `void`     |                   Confirma a transação (Suporta aninhamento de transações                   |
|     rollBack     |     `void`     |                  Reverte a transação (Suporta aninhamento de transações)                 |
|      insert      |     `int`      | Insere dados, retorna o ID da chave primária, chave primária não auto incremento retorna 0 |
|     execute      |     `int`      |                   Executa SQL e retorna o número de linhas afetadas                 |
|      query       |    `array`     |                       Consulta SQL, retorna uma lista de result sets                       |
|      fetch       | `array, object`|                  Consulta SQL e retorna a primeira linha do result set                |
|      connection  |     `self`     |                          Especifica o banco de dados a se conectar                         |

## Uso

### Usando instância DB

```php
<?php

use Hyperf\Context\ApplicationContext;
use Hyperf\DB\DB;

$db = ApplicationContext::getContainer()->get(DB::class);

$res = $db->query('SELECT * FROM `user` WHERE gender = ?;', [1]);

```

### Usando métodos estáticos

```php
<?php

use Hyperf\DB\DB;

$res = DB::query('SELECT * FROM `user` WHERE gender = ?;', [1]);

```

### Métodos customizados usando funções anônimas

> Este método permite que os usuários operem diretamente o `PDO` ou `MySQL` subjacente, então você precisa tratar os problemas de compatibilidade por conta própria

Por exemplo, se quisermos executar certas queries e usar diferentes `fetch mode`, podemos customizar nossos próprios métodos das seguintes formas

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
