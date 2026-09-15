# Cache

O [hyperf/cache](https://github.com/hyperf/cache) fornece cache baseado na implementação de `Aspect`, e também fornece classes de cache que implementam `Psr\SimpleCache\CacheInterface`.
## Instalação
```
composer require hyperf/cache
```

## Configuração padrão

|  Configuração  |                  Valor padrão                  |         Observação          |
|:------:|:----------------------------------------:|:---------------------:|
| driver |  Hyperf\Cache\Driver\RedisDriver  | Cache driver, o padrão é Redis |
| packer | Hyperf\Codec\Packer\PhpSerializerPacker |        Packager         |
| prefix |                   c:                   |       Prefixo do Cache        |
| skip_cache_results |       []                   |       Determinados resultados não são armazenados em cache   |

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

### Simple Cache method

Simple Cache é a especificação [PSR-16](https://www.php-fig.org/psr/psr-16/). Este componente se adapta à especificação. Se você quiser usar a class de Cache `Psr\SimpleCache\CacheInterface`, por exemplo, se você quiser reescrever o módulo de cache do `EasyWeChat`, você pode obter `Psr\SimpleCache\CacheInterface` diretamente do container de Dependency Injection, como mostrado abaixo:

```php

$cache = $container->get(\Psr\SimpleCache\CacheInterface::class);

```

### Método por annotation

O componente fornece a annotation `Hyperf\Cache\Annotation\Cacheable`, que atua sobre methods de class e pode configurar o prefixo de cache correspondente, tempo de expiração, listener e grupo de cache.
Por exemplo, UserService fornece um method user que pode consultar informações do usuário correspondentes ao id. Quando a annotation `Hyperf\Cache\Annotation\Cacheable` é adicionada, o Redis cache correspondente será gerado automaticamente. O valor da chave é `user:id` e o timeout é `9000` segundos. Ao consultar pela primeira vez, será verificado no banco de dados, e nas consultas subsequentes, será verificado no cache.

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

### Limpar o cache gerado pelo `#[Cacheable]`

Fornecemos duas annotations, `CachePut` e `CacheEvict`, para implementar operações de atualização e limpeza de cache.

É claro que também podemos excluir o cache através de events. Vamos criar um novo Service para fornecer um method que nos ajude a tratar o cache.

> No entanto, recomendamos que os usuários usem o tratamento por annotation em vez de listeners.

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

Quando customizamos o `value` de `Cacheable`, como na situação a seguir.

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

Você precisa modificar a variável `$arguments` no constructor de `DeleteListenerEvent` de acordo. O código específico é o seguinte.

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

## Introdução às annotations

### Cacheable

Por exemplo, na configuração a seguir, o prefixo de cache é `user`, o timeout é `7200`, e o nome do event de exclusão é `USER_CACHE`. A `KEY` de cache correspondente gerada é `c:user:1`.

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

Quando `value` é definido, o framework fará o cache do nome de `KEY` de acordo com as regras definidas. No exemplo a seguir, quando `$user->id = 1`, a `KEY` de cache é `c:userBook:_1`

> Essa configuração também suporta outros tipos de annotation de cache descritos abaixo

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

Por exemplo, na configuração a seguir, o prefixo de cache é `user`, o timeout é `7200`, a `KEY` de cache correspondente gerada é `c:user:1`, e o cache é inicializado a cada 10 segundos, de 7200 a 600 segundos, até o primeiro sucesso.

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

`CachePut` é diferente de `Cacheable` porque executa o corpo da função a cada chamada e então reescreve o cache. Então, quando queremos atualizar o cache, podemos chamar os methods relacionados.

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

CacheEvict é mais fácil de entender. Quando o corpo do method é executado, o cache será limpo ativamente.

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

## Cache driver

### Redis driver

`Hyperf\Cache\Driver\RedisDriver` armazenará os dados de cache no `Redis`, e os usuários precisam configurar a `configuration do Redis` correspondente. Esse modo é o modo padrão.

### Process memory driver

Se você precisar armazenar dados em cache na memória, você pode tentar este driver. A configuração é a seguinte:

```php
<?php

return [
    'memory' => [
        'driver' => Hyperf\Cache\Driver\MemoryDriver::class,
    ],
];
```

### Coroutine memory driver

Se você precisar armazenar dados em cache no `Context`, você pode tentar este driver. Por exemplo, no cenário de aplicação a seguir, `Demo::get` será chamado múltiplas vezes em múltiplos lugares, mas você não quer consultar o `Redis` a cada vez.

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
