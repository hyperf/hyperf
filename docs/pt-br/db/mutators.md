# Modifier

> Este documento usa muito conteúdo do [LearnKu](https://learnku.com). Muito obrigado ao LearnKu por contribuir com a comunidade PHP.

Accessors e modifiers permitem que você formate os valores de propriedade do model ao obter ou definir determinados valores de propriedade em uma instância de model.

## Accessors & Modifiers

### Definindo um accessor

Para definir um accessor, você precisa criar um método `getFooAttribute` no model, e o campo `Foo` a ser acessado precisa ser nomeado em "camel case". Neste exemplo, vamos definir um accessor para a propriedade `first_name`. Esse accessor é chamado automaticamente quando o model tenta obter a propriedade `first_name`:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Get the user's name.
     *
     * @param  string  $value
     * @return string
     */
    public function getFirstNameAttribute($value)
    {
        return ucfirst($value);
    }
}
```

Como você pode ver, o valor bruto do campo é passado para o accessor, permitindo que você o processe e retorne o resultado. Para obter o valor modificado, você pode acessar a propriedade `first_name` na instância do model:

```php
$user = App\User::find(1);

$firstName = $user->first_name;
```

Claro, você também pode passar um valor de propriedade existente e usar um accessor para retornar um novo valor computado:

```php
namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Get the user's name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
```

### Definindo um modifier

Para definir um modifier, defina o método `setFooAttribute` no model. Os campos `Foo` a serem acessados são nomeados usando "camel case". Vamos definir um modifier para a propriedade `first_name` novamente. Este modifier será chamado automaticamente quando tentarmos definir o valor da propriedade `first_name` no schema:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Set the user's name.
     *
     * @param  string  $value
     * @return void
     */
    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = strtolower($value);
    }
}
```

Modifiers recebem o valor de um atributo que já foi definido, permitindo que você modifique e defina seu valor na propriedade `$attributes` dentro do model. Por exemplo, se tentarmos definir o valor da propriedade `first_name` como `Sally`:

```php
$user = App\User::find(1);

$user->first_name = 'Sally';
```

Neste exemplo, o método `setFirstNameAttribute` é chamado com o valor `Sally` como parâmetro. O modifier então aplica a função `strtolower` e define o resultado do processamento no array interno `$attributes`.

## Conversor de data

Por padrão, o model converte os campos `created_at` e `updated_at` em instâncias `Carbon`, que herdam da classe nativa `DateTime` do `PHP` e fornecem vários métodos úteis. Você pode adicionar outras propriedades de data definindo a propriedade `$dates` do model:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Properties that should be converted to date format.
     *
     * @var array
     */
    protected $dates = [
        'seen_at',
    ];
}

```

> Dica: Você pode desabilitar os timestamps padrão created_at e updated_at definindo o valor público $timestamps do model como false.

Quando um campo está no formato de data, você pode definir o valor como um timestamp `UNIX`, uma string datetime `(Y-m-d)`, ou uma instância `DateTime` / `Carbon`. O valor da data será formatado corretamente e salvo no seu banco de dados:

Como mencionado acima, quando a propriedade obtida está contida na propriedade `$dates`, ela é automaticamente convertida em uma instância `Carbon`, permitindo que você use qualquer método `Carbon` na propriedade:

```php
$user = App\User::find(1);

return $user->deleted_at->getTimestamp();
```

### Formato de hora

Timestamps serão todos formatados como `Y-m-d H:i:s`. Se você precisar de um formato de timestamp customizado, defina a propriedade `$dateFormat` no model. Essa propriedade determina como a propriedade de data será armazenada no banco de dados, e o formato quando o model é serializado em um array ou `JSON`:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class Flight extends Model
{
    /**
     * This property should be cast to the native type.
     *
     * @var string
     */
    protected $dateFormat = 'U';
}
```

## Conversão de tipo de atributo

A propriedade `$casts` no model fornece um método conveniente para converter propriedades em tipos de dados comuns. A propriedade `$casts` deve ser um array cujas chaves são os nomes das propriedades a serem convertidas, e os valores são os tipos de dados que você deseja converter.
Os tipos de dados suportados são: `integer`, `real`, `float`, `double`, `decimal:<digits>`, `string`, `boolean`, `object`, `array`, `collection`, `date`, `datetime` e `timestamp`. Ao converter para o tipo `decimal`, você precisa definir o número de casas decimais, como: `decimal:2`.

Como exemplo, vamos converter a propriedade `is_admin` armazenada no banco de dados como um inteiro (`0` ou `1`) para um valor boolean:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_admin' => 'boolean',
    ];
}
```

Agora quando você acessa a propriedade `is_admin`, embora o valor armazenado no banco de dados seja do tipo inteiro, o valor de retorno é sempre convertido para o tipo boolean:

```php
$user = App\User::find(1);

if ($user->is_admin) {
    //
}
```

### Conversão de tipo customizada

Models têm várias conversões de tipo comuns já embutidas. Porém, ocasionalmente os usuários precisam converter dados em tipos customizados. Agora, esse requisito pode ser realizado definindo uma classe que implementa a interface `CastsAttributes`

Classes que implementam essa interface devem definir de antemão os métodos `get` e `set`. O método `get` é responsável por converter os dados brutos obtidos do banco de dados no tipo correspondente, enquanto o método `set` converte os dados no tipo de banco de dados correspondente para armazenamento no banco de dados. Por exemplo, vamos reimplementar a conversão de tipo `json` embutida como uma conversão de tipo customizada:

```php
<?php

namespace App\Casts;

use Hyperf\Contract\CastsAttributes;

class Json implements CastsAttributes
{
    /**
     * Convert the extracted data
     */
    public function get($model, $key, $value, $attributes)
    {
        return json_decode($value, true);
    }

    /**
     * Convert to the value to be stored
     */
    public function set($model, $key, $value, $attributes)
    {
        return json_encode($value);
    }
}
```

Uma vez que uma conversão de tipo customizada é definida, ela pode ser vinculada a uma propriedade do model usando seu nome de classe:

```php
<?php

namespace App;

use App\Casts\Json;
use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Properties that should be typecast
     *
     * @var array
     */
    protected $casts = [
        'options' => Json::class,
    ];
}
```

#### Conversão de tipo de objeto de valor

Você não pode apenas converter dados em tipos de dados nativos, mas também converter dados em objetos. As duas conversões de tipo customizadas são definidas de forma muito similar. Mas o método `set` na classe de conversão customizada que converte os dados em um objeto precisa retornar um array de pares chave-valor, que são usados para definir o valor original e armazenável no model correspondente.

Como exemplo, defina uma classe de conversão de tipo customizada para converter múltiplos valores de propriedade do model em um único objeto de valor `Address`, assumindo que o objeto `Address` tem duas propriedades públicas `lineOne` e `lineTwo`:

```php
<?php

namespace App\Casts;

use App\Address;
use Hyperf\Contract\CastsAttributes;

class AddressCaster implements CastsAttributes
{
    /**
     * Convert the extracted data
     */
    public function get($model, $key, $value, $attributes): Address
    {
        return new Address(
            $attributes['address_line_one'],
            $attributes['address_line_two']
        );
    }

    /**
     * Convert to the value to be stored
     */
    public function set($model, $key, $value, $attributes)
    {
        return [
            'address_line_one' => $value->lineOne,
            'address_line_two' => $value->lineTwo,
        ];
    }
}
```

Após a conversão de tipo do objeto de valor, quaisquer alterações de dados no objeto de valor serão automaticamente sincronizadas de volta ao model antes que o model seja salvo:

```php
<?php
$user = App\User::find(1);

$user->address->lineOne = 'Updated Address Value';
$user->address->lineTwo = '#10000';

$user->save();

var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Updated Address Value',
//    'address_line_two' => '#10000'
//];
```

**A implementação aqui é diferente do Laravel, se o seguinte uso ocorrer, preste atenção especial**

```php
$user = App\User::find(1);

var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Address Value',
//    'address_line_two' => '#10000'
//];

$user->address->lineOne = 'Updated Address Value';
$user->address->lineTwo = '#20000';

// After directly modifying the field of address, it cannot take effect in attributes immediately, but you can get the modified data directly through $user->address.
var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Address Value',
//    'address_line_two' => '#10000'
//];

// When we save the data or delete the data, the attributes will be changed to the modified data.
$user->save();
var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Updated Address Value',
//    'address_line_two' => '#20000'
//];
```

Se após modificar `address`, você não quiser salvá-lo ou obter os dados de `address_line_one` através de `address->lineOne`, você também pode usar o seguinte método

```php
$user = App\User::find(1);
$user->address->lineOne = 'Updated Address Value';
$user->syncAttributes();
var_dump($user->getAttributes());
```

Claro, se você ainda precisar modificar a função de `attributes` de forma sincronizada após modificar o `value` correspondente, você pode tentar o seguinte método. Primeiro, implementamos um `UserInfo` e herdamos `CastsValue`.

```php
namespace App\Caster;

use Hyperf\Database\Model\CastsValue;

/**
 * @property string $name
 * @property int $gender
 */
class UserInfo extends CastsValue
{
}
```

Então implemente o `UserInfoCaster` correspondente

```php
<?php

declare(strict_types=1);

namespace App\Caster;

use Hyperf\Contract\CastsAttributes;
use Hyperf\Collection\Arr;

class UserInfoCaster implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): UserInfo
    {
        return new UserInfo($model, Arr::only($attributes, ['name', 'gender']));
    }

    public function set($model, string $key, $value, array $attributes)
    {
        return [
            'name' => $value->name,
            'gender' => $value->gender,
        ];
    }
}

```

Quando modificamos UserInfo da seguinte forma, podemos sincronizar os dados modificados para attributes.

```php
/** @var User $user */
$user = User::query()->find(100);
$user->userInfo->name = 'John1';
var_dump($user->getAttributes()); // ['name' => 'John1']
```

#### Conversão de tipo de entrada

Às vezes, você pode precisar apenas fazer o typecast dos valores de propriedade escritos no model, sem fazer nenhum processamento nos valores de propriedade obtidos do model. Um exemplo típico de conversão de tipo de entrada é o "hashing". Classes de conversão de tipo de entrada precisam implementar a interface `CastsInboundAttributes`, e só precisam implementar o método `set`.

```php
<?php

namespace App\Casts;

use Hyperf\Contract\CastsInboundAttributes;

class Hash implements CastsInboundAttributes
{
    /**
     * hash algorithm
     *
     * @var string
     */
    protected $algorithm;

    /**
     * Create a new instance of the typecast class
     */
    public function __construct($algorithm = 'md5')
    {
        $this->algorithm = $algorithm;
    }

    /**
     * Convert to the value to be stored
     */
    public function set($model, $key, $value, $attributes)
    {
        return hash($this->algorithm, $value);
    }
}
```

#### Parâmetros de conversão de tipo

Ao vincular um cast customizado a um model, você pode especificar o parâmetro de cast a ser passado. Para passar parâmetros de conversão de tipo, use `:` para separar os parâmetros do nome da classe, e use vírgulas para separar múltiplos parâmetros. Esses parâmetros serão passados para o construtor da classe de conversão de tipo:

```php
<?php
namespace App;

use App\Casts\Json;
use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Properties that should be typecast
     *
     * @var array
     */
    protected $casts = [
        'secret' => Hash::class.':sha256',
    ];
}
```

### Conversão Array & `JSON`

Conversões de tipo `array` são muito úteis quando você armazena dados `JSON` serializados no banco de dados. Por exemplo: se seu banco de dados tem um campo do tipo `JSON` ou `TEXT` que é serializado como `JSON`, e você adiciona uma conversão de tipo `array` ao model, ele será automaticamente convertido em um array `PHP` quando você acessá-lo:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Properties that should be typecast
     *
     * @var array
     */
    protected $casts = [
        'options' => 'array',
    ];
}
```

Uma vez que a conversão é definida, ela será automaticamente desserializada do tipo `JSON` para um array `PHP` quando você acessar a propriedade `options`. Quando você define o valor da propriedade `options`, o array fornecido também é automaticamente serializado para armazenamento no tipo `JSON`:

```php
$user = App\User::find(1);

$options = $user->options;

$options['key'] = 'value';

$user->options = $options;

$user->save();
```

### Conversão de tipo de data

Ao usar os atributos `date` ou `datetime`, você pode especificar o formato da data. Esse formato será usado quando os models forem serializados como arrays ou `JSON`:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Properties that should be typecast
     *
     * @var array
     */
    protected $casts = [
         'created_at' => 'datetime:Y-m-d',
    ];
}
```

### Conversão de tipo em tempo de query

Há momentos em que você precisa fazer o typecast de propriedades específicas durante a execução da query, como quando você precisa buscar dados de uma tabela do banco de dados. Como exemplo, considere a seguinte query:

```php
use App\Post;
use App\User;

$users = User::select([
    'users.*',
    'last_posted_at' => Post::selectRaw('MAX(created_at)')
            ->whereColumn('user_id', 'users.id')
])->get();
```

No result set obtido por essa query, a propriedade `last_posted_at` será uma string. Seria mais conveniente se fizéssemos uma conversão de tipo `date` ao executar a query. Você pode fazer isso usando o método `withCasts`:

```php
$users = User::select([
    'users.*',
    'last_posted_at' => Post::selectRaw('MAX(created_at)')
            ->whereColumn('user_id', 'users.id')
])->withCasts([
    'last_posted_at' => 'date'
])->get();
```
