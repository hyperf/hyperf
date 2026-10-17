# Query builder

## Introdução

O query builder de banco de dados do Hyperf fornece uma interface conveniente para criar e executar queries no banco de dados. Ele pode ser usado para realizar a maioria das operações de banco de dados em uma aplicação e funciona em todos os sistemas de banco de dados suportados.

O query builder do Hyperf usa parameter binding do PDO para proteger sua aplicação contra ataques de SQL injection. Portanto, não há necessidade de sanitizar strings passadas como bindings.

Apenas alguns tutoriais de uso comum são fornecidos aqui, e tutoriais específicos podem ser vistos no site oficial do Laravel.
[Laravel Query Builder](https://laravel.com/docs/5.8/queries)

## Obtendo resultados

```php
use Hyperf\DbConnection\Db;

$users = Db::select('SELECT * FROM user;');
$users = Db::table('user')->get();
$users = Db::table('user')->select('name', 'gender as user_gender')->get();
```

O método `Db::select()` retorna um array, e o método `get` retorna `Hyperf\Collection\Collection`. O elemento é `stdClass`, então os dados de cada elemento podem ser retornados através do seguinte código

```php
<?php

foreach ($users as $user) {
    echo $user->name;
}
```

### Convertendo o resultado para formato array

Em alguns cenários, você pode querer usar `Array` em vez da estrutura de objeto `stdClass` no resultado da query, e o `Eloquent` remove o `FetchMode` padrão configurado pela configuração, então neste ponto você pode alterar a configuração ouvindo o evento `Hyperf\Database\Events\StatementPrepared` através de um listener:

```php
<?php
declare(strict_types=1);

namespace App\Listener;

use Hyperf\Database\Events\StatementPrepared;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use PDO;

#[Listener]
class FetchModeListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            StatementPrepared::class,
        ];
    }

    public function process(object $event)
    {
        if ($event instanceof StatementPrepared) {
            $event->statement->setFetchMode(PDO::FETCH_ASSOC);
        }
    }
}
```

### Obtendo o valor de uma coluna

Se você quiser obter uma collection contendo os valores de uma única coluna, você pode usar o método `pluck`. No exemplo a seguir, vamos obter uma collection dos títulos na tabela roles:

```php
<?php
use Hyperf\DbConnection\Db;

$names = Db::table('user')->pluck('name');

foreach ($names as $name) {
    echo $names;
}

```

Você também pode especificar chaves customizadas para os campos na collection retornada:

```php
<?php
use Hyperf\DbConnection\Db;

$roles = Db::table('roles')->pluck('title', 'name');

foreach ($roles as $name => $title) {
    echo $title;
}

```

### Resultados em blocos (chunk)

Se você precisar processar milhares de registros do banco de dados, você pode considerar usar o método `chunk`. Este método pega um pequeno bloco do result set por vez e o passa para a função `closure` para processamento. Este método é muito útil quando um `Command` está processando milhares de dados. Por exemplo, podemos dividir todos os dados da tabela user em pequenos blocos que processam 100 registros por vez:

```php
<?php
use Hyperf\DbConnection\Db;

Db::table('user')->orderBy('id')->chunk(100, function ($users) {
    foreach ($users as $user) {
        //
    }
});
```

Você pode parar de buscar os resultados em blocos retornando `false` dentro do closure:

```php
use Hyperf\DbConnection\Db;

Db::table('user')->orderBy('id')->chunk(100, function ($users) {

    return false;
});
```

Se você estiver atualizando registros do banco de dados enquanto faz o chunking dos resultados, os resultados em blocos podem não ser os mesmos que o esperado. Portanto, ao atualizar registros em blocos, é melhor usar o método chunkById. Este método vai paginar automaticamente os resultados com base na chave primária do registro:

```php
use Hyperf\DbConnection\Db;

Db::table('user')->where('gender', 1)->chunkById(100, function ($users) {
    foreach ($users as $user) {
        Db::table('user')
            ->where('id', $user->id)
            ->update(['update_time' => time()]);
    }
});
```

> Quaisquer mudanças nas chaves primárias ou estrangeiras podem afetar a query em blocos ao atualizar ou excluir registros dentro do callback do bloco. Isso pode fazer com que registros não sejam incluídos no resultado em blocos.

### Query de agregação

O framework também fornece métodos de classe de agregação como `count`, `max`, `min`, `avg`, `sum`.

```php
use Hyperf\DbConnection\Db;

$count = Db::table('user')->count();
```

#### Determinando se o registro existe

Além de usar o método `count` para determinar se o resultado de uma condição de query existe, você também pode usar os métodos `exists` e `doesntExist`:

```php
return Db::table('orders')->where('finalized', 1)->exists();

return Db::table('orders')->where('finalized', 1)->doesntExist();
```

## Consultas

### Especificando uma instrução Select

Claro que você nem sempre vai querer obter todas as colunas de uma tabela do banco de dados. Usando o método select, você pode customizar uma instrução de query select para consultar os campos especificados:

```php
$users = Db::table('user')->select('name', 'email as user_email')->get();
```

O método `distinct` força a query a retornar resultados únicos:

```php
$users = Db::table('user')->distinct()->get();
```

Se você já tem uma instância do query builder e quer adicionar um campo à query existente, você pode usar o método addSelect:

```php
$query = Db::table('users')->select('name');

$users = $query->addSelect('age')->get();
```

## Expressão original

Às vezes você precisa usar expressões raw em uma query, por exemplo, para implementar `COUNT(0) AS count`, o que requer o uso do método `raw`.

```php
use Hyperf\DbConnection\Db;

$res = Db::table('user')->select('gender', Db::raw('COUNT(0) AS `count`'))->groupBy('gender')->get();
```

### Método nativo

Os seguintes métodos podem ser usados no lugar de `Db::raw` para inserir expressões raw em várias partes da query.

O método `selectRaw` pode ser usado no lugar de `select(Db::raw(...))`. O segundo parâmetro deste método é opcional, e o valor é um array de parâmetros vinculados:

```php
$orders = Db::table('order')
    ->selectRaw('price * ? as price_with_tax', [1.0825])
    ->get();
```

Os métodos `whereRaw` e `orWhereRaw` injetam `where` nativo na sua query. O segundo parâmetro destes dois métodos ainda é opcional, e o valor ainda é um array de parâmetros vinculados:

```php
$orders = Db::table('order')
    ->whereRaw('price > IF(state = "TX", ?, 100)', [200])
    ->get();
```

Os métodos `havingRaw` e `orHavingRaw` podem ser usados para definir uma string raw como o valor de uma instrução `having`:

```php
$orders = Db::table('order')
    ->select('department', Db::raw('SUM(price) as total_sales'))
    ->groupBy('department')
    ->havingRaw('SUM(price) > ?', [2500])
    ->get();
```

O método `orderByRaw` pode ser usado para definir uma string raw como o valor da cláusula `order by`:

```php
$orders = Db::table('order')
    ->orderByRaw('updated_at - created_at DESC')
    ->get();
```

## Join de tabelas

### Cláusula Inner Join

O query builder também pode escrever métodos `join`. Para realizar um `"inner join"` básico, você pode usar o método `join` na instância do query builder. O primeiro argumento passado ao método `join` é o nome da tabela que você quer unir, enquanto os outros argumentos usam as restrições de campo que especificam o join. Você também pode unir múltiplas tabelas em uma única query:

```php
$users = Db::table('users')
    ->join('contacts', 'users.id', '=', 'contacts.user_id')
    ->join('orders', 'users.id', '=', 'orders.user_id')
    ->select('users.*', 'contacts.phone', 'orders.price')
    ->get();
```

### Left Join

Se você quiser usar `"left join"` ou `"right join"` em vez de `"inner join"`, use os métodos `leftJoin` ou `rightJoin`. Estes dois métodos são usados da mesma forma que o método `join`:

```php
$users = Db::table('users')
    ->leftJoin('posts', 'users.id', '=', 'posts.user_id')
    ->get();
$users = Db::table('users')
    ->rightJoin('posts', 'users.id', '=', 'posts.user_id')
    ->get();
```

### Instrução Cross Join

Use o método `crossJoin` para fazer um `"cross join"` com o nome da tabela que você quer unir. Um cross join produz um produto cartesiano entre a primeira tabela e as tabelas unidas:

```php
$users = Db::table('sizes')
    ->crossJoin('colours')
    ->get();
```

### Instrução Join Avançada

Você pode especificar instruções `join` mais avançadas. Por exemplo, passando um `closure` como segundo parâmetro do método `join`. Este `closure` recebe um objeto `JoinClause`, especificando as restrições especificadas na instrução `join`:

```php
Db::table('users')
    ->join('contacts', function ($join) {
        $join->on('users.id', '=', 'contacts.user_id')->orOn(...);
    })
    ->get();
```

Se você quiser usar instruções no estilo `"where"` no join, você pode usar os métodos `where` e `orWhere` no join. Estes métodos comparam colunas com valores em vez de colunas com colunas:

```php
Db::table('users')
    ->join('contacts', function ($join) {
        $join->on('users.id', '=', 'contacts.user_id')
                ->where('contacts.user_id', '>', 5);
    })
    ->get();
```

### Query de subjoin

Você pode usar os métodos `joinSub`, `leftJoinSub` e `rightJoinSub` para unir uma query como uma subquery. Cada um destes métodos recebe três parâmetros: uma subquery, um alias de tabela, e um closure que define os campos associados:

```php
$latestPosts = Db::table('posts')
    ->select('user_id', Db::raw('MAX(created_at) as last_post_created_at'))
    ->where('is_published', true)
    ->groupBy('user_id');

$users = Db::table('users')
    ->joinSub($latestPosts, 'latest_posts', function($join) {
        $join->on('users.id', '=', 'latest_posts.user_id');
    })->get();
```

## Query combinada

O query builder também fornece um atalho para "unir" duas queries. Por exemplo, você pode criar uma query primeiro, e então usar o método `union` para unir a segunda query a ela:

```php
$first = Db::table('users')->whereNull('first_name');

$users = Db::table('users')
    ->whereNull('last_name')
    ->union($first)
    ->get();
```

## Instrução Where

### Instrução Where simples

Ao construir uma instância de query `where`, você pode usar o método `where`. A forma mais básica de chamar `where` é passar três parâmetros: o primeiro parâmetro é o nome da coluna, o segundo parâmetro é qualquer operador suportado pelo sistema de banco de dados, e o terceiro parâmetro é o valor a ser comparado com a coluna.

Por exemplo, aqui está uma query para verificar se o valor do campo gender é igual a 1:

```php
$users = Db::table('user')->where('gender', '=', 1)->get();
```

Por conveniência, se você estiver simplesmente comparando o valor da coluna com um valor dado, você pode passar o valor diretamente como o segundo parâmetro do método `where`:

```php
$users = Db::table('user')->where('gender', 1)->get();
```

Claro, você também pode usar outros operadores para escrever cláusulas where:

```php
$users = Db::table('users')->where('gender', '>=', 0)->get();

$users = Db::table('users')->where('gender', '<>', 1)->get();

$users = Db::table('users')->where('name', 'like', 'T%')->get();
```

Você também pode passar um array de condições para a função where:

```php
$users = Db::table('user')->where([
    ['status', '=', '1'],
    ['gender', '=', '1'],
])->get();
```

### Instrução Or

Você pode encadear restrições `where` juntas ou adicionar cláusulas `or` à query. O método `orWhere` aceita os mesmos parâmetros que o método `where`:

```php
$users = Db::table('user')
    ->where('gender', 1)
    ->orWhere('name', 'John')
    ->get();
```

### Outras instruções Where

#### whereBetween

O método `whereBetween` verifica se o valor do campo está entre dois valores dados:

```php
$users = Db::table('users')->whereBetween('votes', [1, 100])->get();
```

#### whereNotBetween

O método `whereNotBetween` verifica se o valor do campo está fora dos dois valores dados:

```php
$users = Db::table('users')->whereNotBetween('votes', [1, 100])->get();
```

#### whereIn / whereNotIn

O método `whereIn` valida que o valor do campo deve existir no array especificado:

```php
$users = Db::table('users')->whereIn('id', [1, 2, 3])->get();
```

O método `whereNotIn` verifica que o valor do campo não deve existir no array especificado:

```php
$users = Db::table('users')->whereNotIn('id', [1, 2, 3])->get();
```

### Agrupamento de parâmetros

Às vezes você precisa criar cláusulas `where` mais avançadas, como `"where exists"` ou agrupamentos de parâmetros aninhados. O query builder também pode lidar com isso. Abaixo, vamos ver um exemplo de agrupamento de restrições entre parênteses:

```php
Db::table('users')->where('name', '=', 'John')
    ->where(function ($query) {
        $query->where('votes', '>', 100)
                ->orWhere('title', '=', 'Admin');
    })
    ->get();
```

Como você pode ver, um `Closure` é escrito no método `where` para construir um query builder que restringe um agrupamento. O `Closure` recebe uma instância de query que você pode usar para definir restrições que devem ser incluídas. O exemplo acima vai gerar o seguinte SQL:

```sql
select * from users where name = 'John' and (votes > 100 or title = 'Admin')
```

> Você deve chamar este agrupamento com orWhere para evitar a aplicação acidental de efeitos globais.

#### Instrução Where Exists

O método `whereExists` permite que você use a instrução `where exists SQL`. O método `whereExists` aceita um parâmetro `Closure`, o closure recebe uma instância do query builder permitindo que você defina queries colocadas na cláusula `exists`:

```php
Db::table('users')->whereExists(function ($query) {
    $query->select(Db::raw(1))
            ->from('orders')
            ->whereRaw('orders.user_id = users.id');
})
->get();
```

A query acima vai produzir a seguinte instrução SQL:

```sql
select * from users
where exists (
    select 1 from orders where orders.user_id = users.id
)
```

#### Instrução Where JSON

O `Hyperf` também suporta consultar campos do tipo `JSON` (apenas em bancos de dados que suportam o tipo `JSON`).

```php
$users = Db::table('users')
    ->where('options->language', 'en')
    ->get();

$users = Db::table('users')
    ->where('preferences->dining->meal', 'salad')
    ->get();
```

Você também pode usar `whereJsonContains` para consultar arrays `JSON`:

```php
$users = Db::table('users')
    ->whereJsonContains('options->languages', 'en')
    ->get();
```

Você pode usar `whereJsonLength` para consultar o tamanho de um array `JSON`:

```php
$users = Db::table('users')
    ->whereJsonLength('options->languages', 0)
    ->get();

$users = Db::table('users')
    ->whereJsonLength('options->languages', '>', 1)
    ->get();
```

## Ordenação, Agrupamento, Limit e Offset

### orderBy

O método `orderBy` permite ordenar o result set por um determinado campo. O primeiro parâmetro de `orderBy` deve ser o campo que você quer ordenar, e o segundo parâmetro controla a direção da ordenação, que pode ser `asc` ou `desc`

```php
$users = Db::table('users')
    ->orderBy('name', 'desc')
    ->get();
```

### latest / oldest

Os métodos `latest` e `oldest` permitem ordenar facilmente por data. Ele usa a coluna `created_at` como ordenação por padrão. Claro, você também pode passar nomes de coluna customizados:

```php
$user = Db::table('users')->latest()->first();
```

### inRandomOrder

O método `inRandomOrder` é usado para ordenar os resultados aleatoriamente. Por exemplo, você pode usar este método para encontrar um usuário aleatório.

```php
$randomUser = Db::table('users')->inRandomOrder()->first();
```

### groupBy / having

Os métodos `groupBy` e `having` podem agrupar resultados. O uso do método `having` é muito similar ao método `where`:

```php
$users = Db::table('users')
    ->groupBy('account_id')
    ->having('account_id', '>', 100)
    ->get();
```

Você pode passar múltiplos argumentos para o método `groupBy`:

```php
$users = Db::table('users')
    ->groupBy('first_name', 'status')
    ->having('account_id', '>', 100)
    ->get();
```

> Para uma sintaxe mais avançada de having, veja o método havingRaw.

### skip / take

Para limitar o número de resultados retornados, ou pular um número específico de resultados, você pode usar os métodos `skip` e `take`:

```php
$users = Db::table('users')->skip(10)->take(5)->get();
```

Ou você também pode usar os métodos limit e offset:

```php
$users = Db::table('users')->offset(10)->limit(5)->get();
```

## Instruções condicionais

Às vezes você pode querer executar uma query apenas se a cláusula se aplicar quando uma determinada condição for verdadeira. Por exemplo, você pode querer aplicar uma instrução `where` apenas se um determinado valor existir na requisição. Você pode fazer isso usando o método `when`:

```php
$role = $request->input('role');

$users = Db::table('users')
    ->when($role, function ($query, $role) {
        return $query->where('role_id', $role);
    })
    ->get();
```

O método `when` executa o closure dado apenas se o primeiro argumento for `true`. Se o primeiro argumento for `false`, então o closure não será executado

Você pode passar outro closure como o terceiro parâmetro do método `when`. O closure será executado se o primeiro argumento for `false`. Para ilustrar como usar essa funcionalidade, vamos configurar a ordenação padrão de uma query:

```php
$sortBy = null;

$users = Db::table('users')
    ->when($sortBy, function ($query, $sortBy) {
        return $query->orderBy($sortBy);
    }, function ($query) {
        return $query->orderBy('name');
    })
    ->get();
```

## Insert

O query builder também fornece o método `insert` para inserir registros no banco de dados. O método `insert` aceita um array de nomes de campos e valores de campos para inserção:

```php
Db::table('users')->insert(
    ['email' => 'john@example.com', 'votes' => 0]
);
```

Você pode até passar um array para o método `insert` para inserir múltiplos registros na tabela

```php
Db::table('users')->insert([
    ['email' => 'taylor@example.com', 'votes' => 0],
    ['email' => 'dayle@example.com', 'votes' => 0]
]);
```

### ID auto incremento

Se a tabela tiver um `ID` auto incremento, use o método `insertGetId` para inserir o registro e retornar o valor do `ID`

```php
$id = Db::table('users')->insertGetId(
    ['email' => 'john@example.com', 'votes' => 0]
);
```

## Update

Claro, além de inserir registros no banco de dados, o query builder também pode atualizar registros existentes através do método `update`. O método `update`, assim como o método `insert`, aceita um array contendo os campos e valores a serem atualizados. Você pode restringir a query de `update` com a cláusula `where`:

```php
Db::table('users')->where('id', 1)->update(['votes' => 1]);
```

### Update ou Insert

Às vezes você pode querer atualizar um registro existente no banco de dados, ou criar um registro correspondente se ele não existir. Nesse caso, o método `updateOrInsert` pode ser usado. O método `updateOrInsert` aceita dois parâmetros: um array de condições para encontrar o registro, e um array de pares chave-valor contendo o registro a ser atualizado.

O método `updateOrInsert` vai primeiro tentar encontrar um registro correspondente no banco de dados usando o par chave e valor do primeiro argumento. Se o registro existir, use o valor do segundo parâmetro para atualizar o registro. Se o registro não for encontrado, um novo registro é inserido, e os dados atualizados são uma coleção dos dois arrays:

```php
Db::table('users')->updateOrInsert(
    ['email' => 'john@example.com', 'name' => 'John'],
    ['votes' => '2']
);
```

### Atualizando campos JSON

Ao atualizar um campo JSON, você pode usar a sintaxe -> para acessar o valor correspondente no objeto JSON, o que é suportado apenas no MySQL 5.7+:

```php
Db::table('users')->where('id', 1)->update(['options->enabled' => true]);
```

### Incremento e decremento automático

O query builder também fornece métodos convenientes para incrementar ou decrementar um determinado campo. Este método fornece uma interface mais expressiva e concisa do que escrever manualmente instruções `update`.

Ambos os métodos recebem pelo menos um parâmetro: a coluna que precisa ser modificada. O segundo parâmetro é opcional e controla a quantidade pela qual a coluna é incrementada ou decrementada:

```php
Db::table('users')->increment('votes');

Db::table('users')->increment('votes', 5);

Db::table('users')->decrement('votes');

Db::table('users')->decrement('votes', 5);
```

Você também pode especificar campos para atualizar durante a operação:

```php
Db::table('users')->increment('votes', 1, ['name' => 'John']);
```

## Delete

O query builder também pode excluir registros de uma tabela usando o método `delete`. Antes de usar `delete`, você pode adicionar uma cláusula `where` para restringir a sintaxe do `delete`:

```php
Db::table('users')->delete();

Db::table('users')->where('votes', '>', 100)->delete();
```

Se você precisar esvaziar a tabela, você pode usar o método `truncate`, que vai excluir todas as linhas e resetar o `ID` auto incremento para zero:

```php
Db::table('users')->truncate();
```

## Lock pessimista

O query builder também contém algumas funções que podem ajudá-lo a implementar `locking pessimista` na sintaxe do `select`. Para implementar um `"shared lock"` em uma query, você pode usar o método `sharedLock`. Locks compartilhados impedem que as colunas de dados selecionadas sejam adulteradas até que a transação seja confirmada

```php
Db::table('users')->where('votes', '>', 100)->sharedLock()->get();
```

Alternativamente, você pode usar o método `lockForUpdate`. Use o lock `"update"` para impedir que linhas sejam modificadas ou selecionadas por outros locks compartilhados:

```php
Db::table('users')->where('votes', '>', 100)->lockForUpdate()->get();
```
