# Validator

## Prefácio

> [hyperf/validation](https://github.com/hyperf/validation) é derivado de [illuminate/validation](https://github.com/illuminate/validation); fizemos algumas modificações, mas mantivemos as mesmas regras de validação. Agradecemos à equipe de desenvolvimento do Laravel por implementar um componente de validador tão poderoso e fácil de usar.

## Instalação

### Importar o pacote do componente

```bash
composer require hyperf/validation
```

### Adicionar middleware

Você precisa adicionar a configuração do middleware global `Hyperf\Validation\Middleware\ValidationMiddleware` ao arquivo de configuração `config/autoload/middlewares.php` para o server que usa o componente de validador. A seguir estão exemplos de middleware global correspondentes para o server `http`:

```php
<?php
return [
    // The following http string corresponds to the value corresponding to the name attribute of each server in config/autoload/server.php, which means that the corresponding middleware configuration is only applied to the server
    'http' => [
        // Configure your global middleware in the array, the order is based on the order of the array
        \Hyperf\Validation\Middleware\ValidationMiddleware::class
        // Other middleware goes here
    ],
];
```

> Se o middleware global não for configurado corretamente, o uso de `FormRequest` pode não funcionar.

### Adicionar exception handler

O exception handler trata principalmente das exceções `Hyperf\Validation\ValidationException`. Fornecemos um `Hyperf\Validation\ValidationExceptionHandler` para processá-las. Você precisa configurar manualmente esse exception handler no seu projeto adicionando-o ao arquivo `config/autoload/exceptions.php`; claro, você também pode personalizar seu próprio exception handler.

```php
<?php
return [
    'handler' => [
        // This corresponds to your current server name
        'http' => [
            \Hyperf\Validation\ValidationExceptionHandler::class,
        ],
    ],
];
```

### Publicar os arquivos de idioma do validador

Devido à função de múltiplos idiomas, este componente depende do componente [hyperf/translation](https://github.com/hyperf/translation). Se você ainda não adicionou o arquivo de configuração do componente de tradução, pode executar o seguinte comando para publicar o arquivo de configuração do componente de tradução. Se a configuração já existir, você só precisa publicar o arquivo de idioma do componente de validador:

Publicar os arquivos do componente de tradução:

```bash
php bin/hyperf.php vendor:publish hyperf/translation
```

Publicar os arquivos do componente de validador:

```bash
php bin/hyperf.php vendor:publish hyperf/validation
```

Executar o comando acima publicará o arquivo de idioma do validador `validation.php` no diretório de arquivos de idioma correspondente; `en` refere-se ao arquivo de idioma inglês, e `zh_CN` refere-se ao arquivo de idioma chinês simplificado. Você pode personalizar o conteúdo do arquivo.

```
/storage
    /languages
        /en
            validation.php
        /zh_CN
            validation.php

```

## Uso

### Validação de form request

Para cenários de validação complexos, você pode criar um `FormRequest`. O form request é uma classe de request personalizada que contém a lógica de validação. Você pode criar uma classe de validação de formulário chamada FooRequest executando o seguinte comando:

```bash
php bin/hyperf.php gen:request FooRequest
```

A classe de validação de formulário será gerada no diretório `app\Request`. Se o diretório não existir, ele será criado automaticamente ao executar o comando.
A seguir, adicionamos algumas regras de validação ao método `rules` dessa classe:

```php
/**
 * Get the validation rules applied to the request
 */
public function rules(): array
{
    return [
        'foo' => 'required|max:255',
        'bar' => 'required',
    ];
}
```

Então, como a regra de validação entra em vigor? Tudo o que você precisa fazer é declarar a classe de request como um parâmetro através de type hints no método do controller. Assim, o form request recebido será validado antes que o método do controller seja chamado, o que significa que você não precisa escrever nenhuma lógica de validação no controller, desacoplando bem as duas partes do código:

```php
<?php
namespace App\Controller;

use App\Request\FooRequest;

class IndexController
{
    public function index(FooRequest $request)
    {
        // The incoming request is verified...

        // Get the verified data...
        $validated = $request->validated();
    }
}
```

Se a validação falhar, o validador lançará uma exceção `Hyperf\Validation\ValidationException`. Você pode tratar a exceção adicionando uma classe de tratamento de exceção personalizada. Ao mesmo tempo, também fornecemos um exception handler `Hyperf\Validation\ValidationExceptionHandler` para tratar a exceção; você também pode configurar diretamente o exception handler fornecido por nós para tratá-la. No entanto, o exception handler padrão pode não atender às suas necessidades. Você pode personalizar o comportamento após a falha de validação personalizando o exception handler de acordo com a situação.

#### Mensagem de erro personalizada

Você pode personalizar as mensagens de erro usadas pelo form request sobrescrevendo o método `messages`. Este método deve retornar um array de pares atributo/regra e suas mensagens de erro correspondentes:

```php
/**
 * Get the error message of the defined validation rule
 */
public function messages(): array
{
    return [
        'foo.required' => 'foo is required',
        'bar.required' => 'bar is required',
    ];
}
```

#### Atributos de autenticação personalizados

Se você quiser substituir a parte `:attribute` da mensagem de autenticação por um nome de atributo personalizado, você pode sobrescrever o método `attributes` para especificar um nome personalizado. Este método retornará um array de pares chave-valor com nomes de atributos e nomes personalizados correspondentes:

```php
/**
 * Get custom attributes for validation errors
 */
public function attributes(): array
{
    return [
        'foo' => 'foo of request',
    ];
}
```

### Criar um validador manualmente

Se você não quiser usar a função de validação automática do `FormRequest`, você pode obter a classe factory do validador injetando a interface `ValidatorFactoryInterface`, e então criar manualmente uma instância do validador através do método `make`:

```php
<?php

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

class IndexController
{
    #[Inject]
    protected ValidatorFactoryInterface $validationFactory;

    public function foo(RequestInterface $request)
    {
        $validator = $this->validationFactory->make(
            $request->all(),
            [
                'foo' => 'required',
                'bar' => 'required',
            ],
            [
                'foo.required' => 'foo is required',
                'bar.required' => 'bar is required',
            ]
        );

        if ($validator->fails()){
            // Handle exception
            $errorMessage = $validator->errors()->first();
        }
        // Do something
    }
}
```

O primeiro parâmetro passado ao método `make` é o dado a ser validado, e o segundo parâmetro é a regra de validação para o dado.

#### Mensagem de erro personalizada

Se necessário, você também pode usar mensagens de erro personalizadas em vez dos valores padrão para validação. Existem várias formas de especificar informações personalizadas. Primeiro, você pode passar informações personalizadas como o terceiro parâmetro do método `make`:

```php
<?php
$messages = [
    'required' => 'The :attribute field is required.',
];

$validator = $this->validationFactory->make($request->all(), $rules, $messages);
```

Neste exemplo, o placeholder `:attribute` será substituído pelo nome real do campo em validação. Além disso, você também pode usar outros placeholders na mensagem de validação. Ex.:

```php
$messages = [
    'same' => 'The :attribute and :other must match.',
    'size' => 'The :attribute must be exactly :size.',
    'between' => 'The :attribute value :input is not between :min-:max.',
    'in' => 'The :attribute must be one of the following types: :values',
];
```

#### Especificar informações personalizadas para um atributo específico

Às vezes você pode querer personalizar mensagens de erro apenas para campos específicos. Basta adicionar `.` após o nome do campo para especificar as regras de validação com mensagens personalizadas:

```php
$messages = [
    'email.required' => 'We need to know your e-mail address!',
];
```

#### Especificar informações personalizadas no arquivo PHP

Na maioria dos casos, você pode especificar informações personalizadas no arquivo em vez de passá-las diretamente para o `Validator`. Para fazer isso, você precisa colocar suas informações no array `custom` do arquivo de idioma `storage/languages/xx/validation.php`.

#### Especificar atributos personalizados em arquivos PHP

Se você quiser substituir a parte `:attribute` da informação de validação por um nome de atributo personalizado, você pode especificar o nome personalizado no array `attributes` do arquivo de idioma `storage/languages/xx/validation.php`:

```php
'attributes' => [
    'email' => 'email address',
],
```

### Hook pós-validação

O validador também permite que você adicione funções de callback permitidas após a validação ser bem-sucedida, para que você possa realizar a próxima etapa de validação, e até adicionar mais mensagens de erro à coleção de mensagens. Para usá-lo, basta usar o método `after` na instância de validação:

```php
<?php

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

class IndexController
{
    #[Inject]
    protected ValidatorFactoryInterface $validationFactory;

    public function foo(RequestInterface $request)
    {
        $validator = $this->validationFactory->make(
            $request->all(),
            [
                'foo' => 'required',
                'bar' => 'required',
            ],
            [
                'foo.required' => 'foo is required',
                'bar.required' => 'bar is required',
            ]
        );

        $validator->after(function ($validator) {
            if ($this->somethingElseIsInvalid()) {
                $validator->errors()->add('field','Something is wrong with this field!');
            }
        });

        if ($validator->fails()) {
            //
        }
    }
}
```

## Tratamento de mensagens de erro

Chamar o método `errors` através da instância `Validator` retorna uma instância `Hyperf\Support\MessageBag`, que possui vários métodos convenientes para tratar mensagens de erro.

### Ver a primeira mensagem de erro de um campo específico

Para ver a primeira mensagem de erro de um campo específico, você pode usar o método `first`:

```php
$errors = $validator->errors();

echo $errors->first('foo');
```

### Ver todas as mensagens de erro de um campo específico

Se você precisar obter um array com todas as mensagens de erro de um campo especificado, você pode usar o método `get`:

```php
foreach ($errors->get('foo') as $message) {
    //
}
```

Se você quiser validar os campos de array do formulário, você pode usar `*` para obter todas as mensagens de erro de cada elemento do array:

```php
foreach ($errors->get('foo.*') as $message) {
    //
}
```

### Ver todas as mensagens de erro de todos os campos

Se você quiser obter todas as mensagens de erro de todos os campos, você pode usar o método `all`:

```php
foreach ($errors->all() as $message) {
    //
}
```

### Determinar se um campo específico contém uma mensagem de erro

O método `has` pode ser usado para determinar se há uma mensagem de erro no campo especificado:

```php
if ($errors->has('foo')) {
    //
}
```

### Cenário (Scene)

O validador adiciona uma função de cenário, para que possamos facilmente modificar as regras de validação conforme necessário.

> Este recurso requer uma versão deste componente maior ou igual a 2.2.7
Crie um `SceneRequest` conforme a seguir:

```php
<?php
declare(strict_types=1);
namespace App\Request;
use Hyperf\Validation\Request\FormRequest;
class SceneRequest extends FormRequest
{
    protected array $scenes = [
        'foo' => ['username'],
        'bar' => ['username', 'password'],
    ];
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'username' => 'required',
            'gender' => 'required',
        ];
    }
}
```

Quando usamos normalmente, todas as regras de validação são usadas, ou seja, `username` e `gender` são obrigatórios.

Podemos definir o cenário para que esse request valide apenas o campo obrigatório `username`.

Se configurarmos o `Hyperf\Validation\Middleware\ValidationMiddleware` e injetarmos o `SceneRequest` no método,
isso fará com que a entrada seja validada diretamente no middleware,
então precisamos obter o `SceneRequest` do container no método para trocar o cenário.

```php
<?php
namespace App\Controller;
use App\Request\DebugRequest;
use App\Request\SceneRequest;
use Hyperf\HttpServer\Annotation\AutoController;
#[AutoController(prefix: 'foo')]
class FooController extends Controller
{
    public function scene()
    {
        $request = $this->container->get(SceneRequest::class);
        $request->scene('foo')->validateResolved();
        return $this->response->success($request->all());
    }
}
```

Porém, podemos usar a annotation `Scene` para trocá-lo.

```php
<?php

namespace App\Controller;

use App\Request\DebugRequest;
use App\Request\SceneRequest;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\Validation\Annotation\Scene;

#[AutoController(prefix: 'foo')]
class FooController extends Controller
{
    #[Scene(scene:'bar1')]
    public function bar1(SceneRequest $request)
    {
        return $this->response->success($request->all());
    }

    #[Scene(scene:'bar2', argument: 'request')] // bind $request
    public function bar2(SceneRequest $request)
    {
        return $this->response->success($request->all());
    }

    #[Scene(scene:'bar3', argument: 'request')] // bind $request
    #[Scene(scene:'bar3', argument: 'req')] // bind $req
    public function bar3(SceneRequest $request, DebugRequest $req)
    {
        return $this->response->success($request->all());
    }

    #[Scene()] // the default scene is method name, The effect is equivalent to #[Scene(scene: 'bar1')]
    public function bar1(SceneRequest $request)
    {
        return $this->response->success($request->all());
    }
}
```

## Regras de validação

A seguir está uma lista de regras válidas e suas funções:

##### accepted

O valor do campo em validação deve ser `yes`, `on`, `1` ou `true`, o que é útil ao "concordar com os termos de serviço".

##### active_url

O campo em validação deve ser baseado na função `PHP` `dns_get_record`, com o valor registrado por `A` ou `AAAA`.

##### after:date

O campo em validação deve ser um valor posterior à data fornecida, e a data será passada pela função PHP `strtotime`:

```php
'start_date' => 'required|date|after:tomorrow'
```

Em vez de passar uma string de data para `strtotime`, você pode especificar outro campo para comparar com a data:

```php
'finish_date' => 'required|date|after:start_date'
```

##### after_or_equal:date

O campo em validação deve ser um valor maior ou igual à data fornecida. Para mais informações, consulte a regra `after:date`.

##### alpha

O campo em validação deve ser composto por letras (incluindo chinês).

##### alpha_dash

O campo em validação pode conter letras (incluindo chinês) e números, além de traços e underscores.

##### alpha_num

O campo em validação deve ser letras (incluindo chinês) ou números.

##### array

O campo em validação deve ser um array PHP.

##### bail

Se a primeira regra de validação falhar, para a execução das demais regras de validação.

##### before:date

Contrário a `after:date`, o campo em validação deve ser um valor anterior à data especificada, e a data será passada para a função PHP `strtotime`.

##### before_or_equal:date

O campo em validação deve ser menor ou igual à data fornecida. A data será passada para a função `strtotime` do PHP.

##### between:min,max

Valida que o tamanho do campo está entre o valor mínimo e máximo fornecidos. Strings, números, arrays e arquivos podem todos usar essa regra assim como a regra size:

'name' =>'required|between:1,20'

##### boolean

O campo em validação deve poder ser convertido para um valor booleano e aceita entradas como true, false, 1, 0, "1" e "0".

##### confirmed

O campo em validação deve ter um campo correspondente foo_confirmation. Por exemplo, se o campo em validação é password, você deve informar um campo password_confirmation correspondente.

##### date

O campo em validação deve ser uma data válida com base na função PHP `strtotime`

 ##### date_equals:date

 O campo em validação deve ser igual à data fornecida, e a data será passada para a função PHP `strtotime`.

 ##### date_format:format

 O campo em validação deve corresponder ao formato especificado. Você pode usar a função PHP `date` ou `date_format` para validar o campo.

 ##### different:field

 O campo em validação deve ter um valor diferente do campo especificado.

 ##### digits:value

 O campo em validação deve ser numérico e seu tamanho deve ser o valor especificado por value.

 ##### digits_between:min,max

 O tamanho do campo em validação deve estar entre o valor mínimo e máximo.

 ##### dimensions

 O tamanho da imagem validada deve atender às restrições especificadas pelos parâmetros indicados:

 ```php
 'avatar' => 'dimensions:min_width=100,min_height=200'
 ```

 Restrições válidas incluem: `min_width`, `max_width`, `min_height`, `max_height`, `width`, `height`, `ratio`.

 `ratio` restringe a proporção largura/altura, que pode ser expressa pela expressão `3/2` ou pelo número decimal `1.5`:

 ```php
 'avatar' => 'dimensions:ratio=3/2'
 ```

 Como essa regra requer múltiplos parâmetros, você pode usar o método `Rule::dimensions` para construir a regra:

 ```php
 use Hyperf\Validation\Rule;

 public function rules(): array
 {
 return [
            'avatar' => [
               'required',
               Rule::dimensions()->maxWidth(1000)->maxHeight(500)->ratio(3 / 2),
            ],
        ];
 }
 ```
 ##### distinct

 Ao processar arrays, o campo em validação não pode conter valores duplicados:

 ```php
 'foo.*.id' => 'distinct'
 ```

 ##### email

 O campo em validação deve ser um endereço de e-mail formatado corretamente.

 ##### exists:table,column

 O campo em validação deve existir na tabela de dados especificada.

 Uso básico:

 ```php
 'state' => 'exists:states'
```

If the `column` option is not specified, the field name will be used.

Specify a custom column name:

```php
'state' => 'exists:states,abbreviation'
```

Às vezes, você pode precisar especificar a conexão de banco de dados a ser usada para a consulta `exists`. Isso pode ser feito usando `.` antes do nome da tabela para indicar a conexão de banco de dados, ou resolvido automaticamente especificando o nome da classe do model:

```php
// Pre-database connection method
'email' => 'exists:connection.staff,email'

// Automatically resolve model class names
'email' => 'exists:StaffModel::class,email'
```

Se você quiser personalizar a consulta executada pelas regras de validação, você pode usar a classe `Rule` para definir as regras. Neste exemplo, também especificamos as regras de validação na forma de um array, em vez de usar caracteres `|` para qualificá-las:

```php
use Hyperf\Validation\Rule;

$validator = $this->validationFactory->make($data, [
    'email' => [
        'required',
        Rule::exists('staff')->where(function ($query) {
            $query->where('account_id', 1);
        }),
    ],
]);
```

##### file

O campo em validação deve ser um arquivo enviado com sucesso.

##### filled

O campo em validação não pode ser vazio se estiver presente.

##### gt:field

O campo em validação deve ser maior que o campo `field` fornecido, e os dois tipos de campo devem ser iguais; aplicável a strings, números, arrays e arquivos, semelhante à regra `size`

##### gte:field

O campo em validação deve ser maior ou igual ao campo `field` fornecido, e os dois tipos de campo devem ser iguais; aplicável a strings, números, arrays e arquivos, semelhante à regra `size`

##### image

O arquivo em validação deve ser uma imagem (`jpeg`, `png`, `bmp`, `gif` ou `svg`)

##### in:foo,bar...

O valor do campo em validação deve estar na lista fornecida. Como essa regra frequentemente requer que façamos implode do array, podemos usar Rule::in para construir esta regra:

```php
use Hyperf\Validation\Rule;

$validator = $this->validationFactory->make($data, [
    'zones' => [
        'required',
        Rule::in(['first-zone','second-zone']),
    ],
]);
```

##### in_array:anotherfield

O campo em validação deve existir no valor de outro campo.

##### integer

O campo em validação deve ser um número inteiro.

##### ip

O campo em validação deve ser um endereço IP.

##### ipv4

O campo em validação deve ser um endereço IPv4.

##### ipv6

O campo em validação deve ser um endereço IPv6.

##### json

O campo em validação deve ser uma string JSON válida

##### lt:field

O campo em validação deve ser menor que o campo `field` fornecido, e os dois tipos de campo devem ser iguais; aplicável a strings, números, arrays e arquivos, semelhante à regra `size`

##### lte:field

O campo em validação deve ser menor ou igual ao campo `field` fornecido, e os dois tipos de campo devem ser iguais; aplicável a strings, números, arrays e arquivos, semelhante à regra `size`

##### max:value

O campo em validação deve ser menor ou igual ao valor máximo, o que é o mesmo uso da regra `size` para campos de string, numéricos, array e arquivo.

##### mimetypes: text/plain...

O arquivo em validação deve corresponder a um dos tipos de arquivo `MIME` fornecidos:

```php
'video' => 'mimetypes:video/avi,video/mpeg,video/quicktime'
```

Para determinar o tipo `MIME` do arquivo enviado, o componente lerá o conteúdo do arquivo para identificar o tipo `MIME`, que pode ser diferente do tipo `MIME` do cliente.

##### mimes:foo,bar,...

O tipo `MIME` do arquivo em validação deve ser um dos tipos de extensão listados na regra
Uso básico das regras `MIME`:

```php
'photo' => 'mimes:jpeg,bmp,png'
```

Embora você especifique apenas a extensão, essa regra na verdade valida o tipo `MIME` do arquivo obtido lendo seu conteúdo.
A lista completa de tipos `MIME` e suas extensões correspondentes pode ser encontrada aqui: [mime types](http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types)

##### min:value

Em contraste com `max:value`, o campo em validação deve ser maior ou igual ao valor mínimo. Para campos de string, numéricos, array e arquivo, é consistente com o uso da regra `size`.

##### not_in:foo,bar,...

O valor do campo em validação não pode estar na lista fornecida. Semelhante à regra `in`, podemos usar o método `Rule::notIn` para construir a regra:

```php
use Hyperf\Validation\Rule;

$validator = $this->validationFactory->make($data, [
    'toppings' => [
        'required',
        Rule::notIn(['sprinkles','cherries']),
    ],
]);
```

##### not_regex:pattern

O campo em validação não pode corresponder à expressão regular fornecida

Observação: Ao usar o modo `regex/not_regex`, as regras devem ser colocadas em um array em vez de separadas por pipe, especialmente quando a expressão regular contém símbolos de pipe.

##### nullable

O campo em validação pode ser `null`, o que é útil ao validar alguns dados primitivos que podem ser `null`, como inteiros ou strings.

##### numeric

O campo em validação deve ser numérico

##### present

O campo em validação deve aparecer nos dados de entrada, mas pode ser vazio.

##### regex:pattern

O campo em validação deve corresponder à expressão regular fornecida.
A camada inferior desta regra é a função `preg_match` do `PHP`. Portanto, o padrão especificado precisa seguir o formato exigido pela função `preg_match` e conter um delimitador válido. Ex.:

```php
 'email' => 'regex:/^.+@.+$/i'
```

Observação: Ao usar o modo `regex/not_regex`, as regras devem ser colocadas em um array em vez de separadas por pipe, especialmente quando a expressão regular contém símbolos de pipe.

##### required

O valor do campo em validação não pode ser vazio, e o valor do campo é considerado vazio nos seguintes casos:
- O valor é `null`
- O valor é uma string vazia
- O valor é um array vazio ou um objeto `Countable` vazio
- O valor é um arquivo enviado, mas o caminho está vazio

##### required_if:anotherfield,value,…

O campo em validação deve existir quando `anotherfield` é igual ao valor especificado `value`, e não pode ser vazio.
Se você quiser construir condições mais complexas para a regra `required_if`, você pode usar o método `Rule::requiredIf`, que aceita um booleano ou closure. Ao passar um closure, ele retornará `true` ou `false` para indicar se o campo em validação é obrigatório:

```php
use Hyperf\Validation\Rule;

$validator = $this->validationFactory->make($request->all(), [
    'role_id' => Rule::requiredIf($request->user()->is_admin),
]);

$validator = $this->validationFactory->make($request->all(), [
    'role_id' => Rule::requiredIf(function () use ($request) {
        return $request->user()->is_admin;
    }),
]);
```

##### required_unless:anotherfield,value,…

A menos que o campo `anotherfield` seja igual a `value`, o campo em validação não pode ser vazio.

##### required_with:foo,bar,…

O campo em validação só é necessário se qualquer outro campo especificado existir.

##### required_with_all:foo,bar,…

O campo em validação só é necessário se todos os campos especificados existirem.

##### required_without:foo,bar,…

O campo em validação só é necessário se qualquer campo especificado não existir.

##### required_without_all:foo,bar,…

O campo em validação só é necessário se todos os campos especificados não existirem.

##### same:field

O campo fornecido e o campo em validação devem corresponder.

##### size:value

O campo em validação deve ter um tamanho que corresponda ao valor fornecido `value`. Para strings, `value` é o número de caracteres; para números, `value` é um valor inteiro fornecido; para arrays, `value` é o tamanho do array; para arquivos, `value` é o número de kilobytes (KB) do arquivo correspondente.

##### starts_with:foo,bar,...

O campo em validação deve começar com um valor fornecido.

##### string

O campo em validação deve ser uma string. Se o campo puder ser vazio, você precisa atribuir a regra `nullable` ao campo.

##### timezone

O caractere em validação deve ser um identificador de fuso horário válido com base na função `PHP` `timezone_identifiers_list`

##### unique:table,column,except,idColumn

O campo em validação deve ser único em uma tabela de dados fornecida. Se a opção `column` não for especificada, o nome do campo será usado como `column` padrão.

1. Especifique o nome de coluna personalizado:

```php
'email' => 'unique:users,email_address'
```

2. Conexão de banco de dados personalizada:
Às vezes, você pode precisar personalizar a conexão de banco de dados gerada pelo validador. Como você pode ver acima, definir `unique:users` como a regra de validação usará a conexão de banco de dados padrão para consultar o banco de dados. Para sobrescrever a conexão padrão, use "." após o nome da tabela de dados para especificar a conexão, ou resolva automaticamente especificando o nome da classe do model:

```php
// Pre-database connection method
'email' => 'unique:connection.users,email_address'

// Automatically resolve model class names
'email' => 'unique:UserModel::class,email_address'
```

3. Forçar uma regra única que ignora um `ID` fornecido:
Às vezes, você pode querer ignorar um `ID` fornecido durante a verificação de unicidade. Por exemplo, considere uma interface de "atualização de propriedades" que inclui um nome de usuário, endereço de e-mail e localização. Você vai querer validar que o endereço de e-mail é único. Alterar o campo de nome de usuário não altera o campo de e-mail. Você não quer lançar um erro de validação porque o usuário já possui esse endereço de e-mail. Você só quer lançar um erro de validação quando o e-mail fornecido pelo usuário já tiver sido usado por outra pessoa.

Para dizer ao validador para ignorar o ID do usuário, você pode usar a classe Rule para definir essa regra. Também precisamos especificar a regra de validação em um array em vez de usar `|` para definir a regra:

```php
use Hyperf\Validation\Rule;

$validator = $this->validationFactory->make($data, [
    'email' => [
        'required',
        Rule::unique('users')->ignore($user->id),
    ],
]);
```

Além de passar o valor da chave primária da instância do model para o método `ignore`, você também pode passar a instância completa do model. O componente extrairá automaticamente o valor da chave primária da instância do model:

```php
Rule::unique('users')->ignore($user)
```

Se sua tabela de dados usa um campo de chave primária diferente de `id`, você pode especificar o nome do campo ao chamar o método `ignore`:

```php
'email' => Rule::unique('users')->ignore($user->id,'user_id')
```

Por padrão, a regra `unique` verifica a unicidade da coluna que corresponde ao nome do atributo a ser validado. No entanto, você pode especificar nomes de colunas diferentes como o segundo parâmetro do método unique:

```php
Rule::unique('users','email_address')->ignore($user->id),
```

4. Adicionar uma cláusula `where` extra:

Você também pode especificar restrições de consulta adicionais ao usar o método `where` para personalizar a consulta. Por exemplo, vamos adicionar uma restrição que valida que `account_id` é 1:

```php
'email' => Rule::unique('users')->where(function ($query) {
    $query->where('account_id', 1);
})
```

##### url

O campo em validação deve ser uma URL válida.

##### uuid

O campo em validação deve ser um identificador único universal (UUID) válido conforme RFC 4122 (versão 1, 3, 4, ou 5).

##### sometimes

Adicionar regras condicionais
Validar quando existir

Em alguns cenários, você pode querer realizar verificações de validação apenas quando um determinado campo existir. Para implementar isso rapidamente, adicione a regra `sometimes` à lista de regras:

```php
$validator = $this->validationFactory->make($data, [
    'email' => 'sometimes|required|email',
]);
```

No exemplo acima, o campo `email` só será validado se existir no array `$data`.

Observação: Se você tentar validar um campo que sempre existe mas pode ser vazio, consulte as considerações sobre campos opcionais.

Validação condicional complexa

Às vezes você pode querer adicionar regras de validação com base em lógica condicional mais complexa. Por exemplo, você pode querer exigir que um determinado campo seja obrigatório apenas quando o valor de outro campo for maior que 100, ou pode precisar exigir que ambos os campos tenham um valor fornecido apenas quando o outro campo existir. Adicionar essa regra de validação não é uma dor de cabeça. Primeiro, crie uma regra estática que nunca mudará para a instância do `Validator`:

```php
$validator = $this->validationFactory->make($data, [
    'email' => 'required|email',
    'games' => 'required|numeric',
]);
```

Vamos supor que nossa aplicação web atenda a colecionadores de jogos. Se um colecionador de jogos se cadastra em nossa aplicação e possui mais de 100 jogos, queremos que ele explique por que tem tantos jogos. Por exemplo, talvez ele esteja administrando uma loja de jogos usados, ou simplesmente goste de colecionar. Para adicionar essa condição, podemos usar o método `sometimes` na instância `Validator`:

```php
$v->sometimes('reason','required|max:500', function($input) {
    return $input->games >= 100;
});
```

O primeiro parâmetro passado ao método `sometimes` é o campo com nome que precisamos validar condicionalmente, e o segundo parâmetro é a regra que queremos adicionar. Se o closure passado como terceiro parâmetro retornar `true`, a regra é adicionada. Esse método facilita a construção de validações condicionais complexas, e você pode até adicionar validação condicional para múltiplos campos de uma vez:

```php
$v->sometimes(['reason','cost'],'required', function($input) {
    return $input->games >= 100;
});
```

Observação: O parâmetro `$input` passado para o closure é uma instância de `Hyperf\Support\Fluent` e pode ser usado para acessar entradas e arquivos.

### Validar entrada de array

Não é mais uma dor de cabeça validar os campos de array de entrada do formulário. Por exemplo, se a requisição HTTP recebida contém o campo `photos[profile]`, você pode validá-lo assim:

```php
$validator = $this->validationFactory->make($request->all(), [
    'photos.profile' => 'required|image',
]);
```

Também podemos validar cada elemento do array. Por exemplo, para validar que cada e-mail em uma entrada de array fornecida é único, podemos fazer o seguinte (esse tipo de campo de array enviado é um array bidimensional, como `person[][email]` ou `person[test][email]`):

```php
$validator = $this->validationFactory->make($request->all(), [
    'person.*.email' => 'email|unique:users',
    'person.*.first_name' => 'required_with:person.*.last_name',
]);
```

Da mesma forma, no arquivo de idioma, você também pode usar o caractere `*` para especificar a mensagem de validação, para que você possa usar uma única mensagem de validação para definir regras de validação baseadas em campos de array:

```php
'custom' => [
    'person.*.email' => [
        'unique' => 'E-mail address of each person must be unique',
    ]
],
```

### Regras de validação personalizadas

#### Registrar regras de validação personalizadas

O componente `Validation` usa um mecanismo de evento para implementar regras de validação personalizadas. Definimos o evento `ValidatorFactoryResolved`. Tudo o que você precisa fazer é definir um listener para `ValidatorFactoryResolved` e implementar o registro do validador no listener. O exemplo é o seguinte.

```php
namespace App\Listener;

use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\Event\ValidatorFactoryResolved;
use Hyperf\Validation\Validator;

#[Listener]
class ValidatorFactoryResolvedListener implements ListenerInterface
{

    public function listen(): array
    {
        return [
            ValidatorFactoryResolved::class,
        ];
    }

    public function process(object $event): void
    {
        /** @var ValidatorFactoryInterface $validatorFactory */
        $validatorFactory = $event->validatorFactory;
        // registered foo validator
        $validatorFactory->extend('foo', function (string $attribute, mixed $value, array $parameters, Validator $validator): bool {
            return $value == 'foo';
        });
        // When creating a custom validation rule, you may sometimes need to define a custom placeholder for error messages. Here is an extension of the :foo placeholder
        $validatorFactory->replacer('foo', function (string $message, string $attribute, string $rule, array $parameters): array|string {
            return str_replace(':foo', $attribute, $message);
        });
    }
}
```

#### Mensagem de erro personalizada

Você também precisa definir mensagens de erro para regras personalizadas. Você pode usar arrays de mensagens personalizadas inline ou adicionar entradas no arquivo de idioma de validação para conseguir isso. A mensagem deve ser colocada na primeira dimensão do array, não no array custom, que é usado apenas para armazenar as informações de erro específicas do atributo. Tomando o validador personalizado `foo` da seção anterior como exemplo:

`storage/languages/en/validation.php` adicione o seguinte conteúdo ao array do arquivo

```php
    'foo' => 'The :attribute must be foo',
```

`storage/languages/zh_CN/validation.php` adicione o seguinte conteúdo ao array do arquivo

```php
    'foo' => ':attribute must be foo',
```

#### Uso de validador personalizado

```php
<?php

declare(strict_types=1);

namespace App\Request;

use Hyperf\Validation\Request\FormRequest;

class DemoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // use foo validator
            'name' => 'foo'
        ];
    }
}
```
