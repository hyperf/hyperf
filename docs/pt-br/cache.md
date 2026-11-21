# Cache

[hyperf/cache](https://github.com/hyperf/cache) fornece cache via AOP com base na implementação de `Aspect`, e também fornece classes de cache que implementam `Psr\SimpleCache\CacheInterface`.
## Instalação
```
composer require hyperf/cache
```

## Configuração padrão

|  Configuração  |                  Valor padrão                  |         Observação          |
|:------:|:----------------------------------------:|:---------------------:|
| driver |  Hyperf\Cache\Driver\RedisDriver  | Driver de cache, o padrão é Redis |
| packer | Hyperf\Codec\Packer\PhpSerializerPacker |        Empacotador         |
| prefix |                   c:                   |       Prefixo do cache        |
| skip_cache_results |       []                   |       Alguns resultados não são cacheados   |

```php
<?php

return [
    'default' => [
        'driver' => Hyperf\Cache\Driver\RedisDriver::class,
        'packer' => Hyperf\Codec\Packer\PhpSerializerPacker::class,
        'prefix' => 'c:',
        'skip_cache_results' => [],
    ],
];
```

## Uso

### Método Simple Cache

Simple Cache é a especificação [PSR-16](https://www.php-fig.org/psr/psr-16/). Este componente se adapta à especificação. Se você quiser usar a classe de cache `Psr\SimpleCache\CacheInterface `, por exemplo, se quiser reescrever o módulo de cache do `EasyWeChat`, você pode obter `Psr\SimpleCache\CacheInterface` diretamente do container de injeção de dependência, como abaixo:

```php

$cache = $container->get(\Psr\SimpleCache\CacheInterface::class);

```

### Método por anotação

O componente fornece a anotação `Hyperf\Cache\Annotation\Cacheable`, que atua em métodos de classes e permite configurar o prefixo do cache, o tempo de expiração, o listener e o grupo de cache.
Por exemplo, o UserService fornece um método user que consulta as informações de usuário correspondentes ao id. Quando a anotação `Hyperf\Cache\Annotation\Cacheable` é adicionada, o cache correspondente no Redis será gerado automaticamente. A chave é `user:id` e o TTL é de `9000` segundos. Na primeira consulta, será buscado no banco de dados; nas consultas seguintes, será buscado no cache.

```php
<?php

namespace App\Services;

use App\Models\User;
use Hyperf\Cache\Annotation\Cacheable;

class UserService
{
    #[Cacheable(prefix: "user", ttl: 9000, listener: "user-update")]
    public function user($id)
    {
        $user = User::query()->where('id',$id)->first();

        if($user){
            return $user->toArray();
        }

        return null;
    }
}
```

### Limpar o cache gerado por `#[Cacheable]`

Fornecemos duas anotações, `CachePut` e `CacheEvict`, para implementar operações de atualização e limpeza de cache.

Claro, também podemos excluir o cache por meio de eventos. Vamos criar um novo Service para fornecer um método que nos ajude a lidar com o cache.

> Porém, recomendamos que os usuários usem o processamento por anotação em vez de listeners.

```php
<?php

declare(strict_types=1);

namespace App\Service;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Cache\Listener\DeleteListenerEvent;
use Psr\EventDispatcher\EventDispatcherInterface;

class SystemService
{
    #[Inject]
    protected EventDispatcherInterface $dispatcher;

    public function flushCache($userId)
    {
        $this->dispatcher->dispatch(new DeleteListenerEvent('user-update', [$userId]));

        return true;
    }
}
```

Quando personalizamos o `value` de `Cacheable`, como na situação a seguir.

```php
<?php

declare(strict_types=1);

namespace App\Service\Cache;

use Hyperf\Cache\Annotation\Cacheable;

class DemoService
{

    #[Cacheable(prefix: "cache", value: "_#{id}", listener: "user-update")]
    public function getCache(int $id)
    {
        return $id . '_' . uniqid();
    }
}
```

Você precisa ajustar a variável `$arguments` no construtor de `DeleteListenerEvent` de acordo. O código específico é o seguinte.

```php
<?php

declare(strict_types=1);

namespace App\Service;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Cache\Listener\DeleteListenerEvent;
use Psr\EventDispatcher\EventDispatcherInterface;

class SystemService
{
    #[Inject]
    protected EventDispatcherInterface $dispatcher;

    public function flushCache($userId)
    {
        $this->dispatcher->dispatch(new DeleteListenerEvent('user-update', ['id' => $userId]));

        return true;
    }
}
```

## Introdução às anotações

### Cacheable

Por exemplo, na configuração a seguir, o prefixo do cache é `user`, o TTL é `7200`, e o nome do evento de exclusão é `USER_CACHE`. A KEY de cache correspondente é gerada como `c:user:1`.

```php
<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\User;
use Hyperf\Cache\Annotation\Cacheable;

class UserService
{
    #[Cacheable(prefix: "user", ttl: 7200, listener: "USER_CACHE")]
    public function user(int $id): array
    {
        $user = User::query()->find($id);

        return [
            'user' => $user->toArray(),
            'uuid' => $this->unique(),
        ];
    }
}
```

Quando `value` é definido, o framework cacheia o nome da chave `KEY` de acordo com as regras configuradas. No exemplo a seguir, quando `$user->id = 1`, a `KEY` cacheada é `c:userBook:_1`

> Esta configuração também suporta outros tipos de anotações de cache descritos abaixo

```php
<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\User;
use Hyperf\Cache\Annotation\Cacheable;

class UserBookService
{
    #[Cacheable(prefix: "userBook", ttl: 6666, value: "_#{user.id}")]
    public function userBook(User $user): array
    {
        return [
            'book' => $user->book->toArray(),
            'uuid' => $this->unique(),
        ];
    }
}
```

### CacheAhead

Por exemplo, na configuração a seguir, o prefixo do cache é `user`, o TTL é `7200`, a KEY gerada correspondente é `c:user:1`, e o cache é inicializado a cada 10 segundos, do intervalo de 7200 até 600 segundos, até obter sucesso pela primeira vez.

```php
<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\User;
use Hyperf\Cache\Annotation\CacheAhead;

class UserService
{
    #[CacheAhead(prefix: "user", ttl: 7200, aheadSeconds: 600, lockSeconds: 10)]
    public function user(int $id): array
    {
        $user = User::query()->find($id);

        return [
            'user' => $user->toArray(),
            'uuid' => $this->unique(),
        ];
    }
}
```

### CachePut

`CachePut` é diferente de `Cacheable` porque executa o corpo da função sempre que é chamado e depois reescreve o cache. Assim, quando queremos atualizar o cache, podemos chamar os métodos relevantes.

```php
<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\User;
use Hyperf\Cache\Annotation\CachePut;

class UserService
{
    #[CachePut(prefix: "user", ttl: 3601)]
    public function updateUser(int $id)
    {
        $user = User::query()->find($id);
        $user->name = 'HyperfDoc';
        $user->save();

        return [
            'user' => $user->toArray(),
            'uuid' => $this->unique(),
        ];
    }
}
```

### CacheEvict

CacheEvict é mais simples de entender: quando o corpo do método é executado, o cache será limpo ativamente.

```php
<?php

declare(strict_types=1);

namespace App\Service;

use Hyperf\Cache\Annotation\CacheEvict;

class UserBookService
{
    #[CacheEvict(prefix: "userBook", value: "_#{id}")]
    public function updateUserBook(int $id)
    {
        return true;
    }
}
```

## Driver de cache

### Driver Redis

`Hyperf\Cache\Driver\RedisDriver` armazena dados de cache no `Redis`, e os usuários precisam configurar a respectiva `configuração do Redis`. Esse é o modo padrão.

### Driver de memória do processo

Se você precisa cachear dados na memória, pode tentar este driver. A configuração é a seguinte:

```php
<?php

return [
    'memory' => [
        'driver' => Hyperf\Cache\Driver\MemoryDriver::class,
    ],
];
```

### Driver de memória da corrotina

Se você precisa cachear dados no `Context`, pode tentar este driver. Por exemplo, no cenário de aplicação a seguir, `Demo::get` será chamado várias vezes em vários lugares, mas você não quer consultar o `Redis` toda vez.

```php
<?php
use Hyperf\Cache\Annotation\Cacheable;

class Demo
{    
    public function get($userId, $id)
    {
        return $this->getArray($userId)[$id] ?? 0;
    }

    #[Cacheable(prefix: "test", group: "co")]
    public function getArray(int $userId): array
    {
        return $this->redis->hGetAll($userId);
    }
}
```

A configuração correspondente é a seguinte:

```php
<?php

return [
    'co' => [
        'driver' => Hyperf\Cache\Driver\CoroutineMemoryDriver::class,
        'packer' => Hyperf\Codec\Packer\PhpSerializerPacker::class,
    ],
];
```
