# Event
Os eventos de model são implementados na interface [psr/event-dispatcher](https://github.com/php-fig/event-dispatcher).

## Listener customizado

Graças ao suporte do componente [hyperf/event](https://github.com/hyperf/event), os usuários podem monitorar facilmente os seguintes eventos.
Por exemplo `QueryExecuted`, `StatementPrepared`, `TransactionBeginning`, `TransactionCommitted`, `TransactionRolledBack`.
A seguir, vamos implementar um listener que registra SQL e falar sobre como usá-lo.
Primeiro, definimos `DbQueryExecutedListener`, implementamos a interface `Hyperf\Event\Contract\ListenerInterface` e definimos a annotation `Hyperf\Event\Annotation\Listener` na classe, para que o Hyperf registre automaticamente o listener no despachante de eventos, sem qualquer configuração manual, o código de exemplo é o seguinte:

```php
<?php

declare(strict_types=1);

namespace App\Listeners;

use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Collection\Arr;
use Hyperf\Stringable\Str;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

#[Listener]
class DbQueryExecutedListener implements ListenerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory->get('sql');
    }

    public function listen(): array
    {
        return [
            QueryExecuted::class,
        ];
    }

    /**
     * @param QueryExecuted $event
     */
    public function process(object $event)
    {
        if ($event instanceof QueryExecuted) {
            $sql = $event->sql;
            if (! Arr::isAssoc($event->bindings)) {
                foreach ($event->bindings as $key => $value) {
                    $sql = Str::replaceFirst('?', "'{$value}'", $sql);
                }
            }

            $this->logger->info(sprintf('[%s] %s', $event->time, $sql));
        }
    }
}

```

## Eventos de Model

Os eventos de model não são consistentes com o `EloquentORM`, que usa `Observer` para monitorar eventos de model. O `Hyperf` usa `hooks` diretamente para tratar os eventos correspondentes. Se você ainda preferir a forma do `Observer`, você pode implementar o `event listener` você mesmo. Claro, você também pode nos avisar em [issue#2](https://github.com/hyperf/hyperf/issues/2).

### Função de hook

|  Nome do evento  |  Momento do disparo                                 | Bloqueia |                           Observação                           |
|:------------:|:-----------------------------------------------:|:----------------:|:----------------------------------------------------------:|
|   booting    |  Antes do model ser carregado pela primeira vez  |        não        |    Disparado apenas uma vez no ciclo de vida do processo    |
|    booted    |  Depois do model ser carregado pela primeira vez   |        não        |    Disparado apenas uma vez no ciclo de vida do processo    |
|  retrieved   |            Após o preenchimento dos dados               |        não        |  Disparado sempre que o model é consultado do DB ou cache  |
|   creating   |           Quando os dados são criados             |        sim       |                                                            |
|   created    |           Após os dados serem criados             |        não        |                                                            |
|   updating   |             Quando os dados são atualizados                |        sim       |                                                            |
|   updated    |               Após a atualização dos dados                 |        não        |                                                            |
|    saving    |       Quando os dados são criados ou atualizados           |        sim       |                                                            |
|    saved     |       Após os dados serem criados ou atualizados          |        não        |                                                            |
|  restoring   |       Quando dados com soft delete são restaurados        |        sim       |                                                            |
|   restored   |       Após a restauração de dados com soft delete          |        não        |                                                            |
|   deleting   |              Quando os dados são excluídos               |        sim       |                                                            |
|   deleted    |              Após a exclusão dos dados                |        não        |                                                            |
|   forceDeleting   |              Quando os dados são forçadamente excluídos         sim    |        sim       |                                                            |
| forceDeleted |       Após os dados serem forçadamente excluídos        |        não        |                                                            |

O uso de eventos para um model é muito simples, basta adicionar o método correspondente ao model. Por exemplo, quando os dados são salvos abaixo, o evento `saving` é disparado, e o campo `created_at` é sobrescrito ativamente.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\Database\Model\Events\Saving;

/**
 * @property $id
 * @property $name
 * @property $gender
 * @property $created_at
 * @property $updated_at
 */
class User extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'name', 'gender', 'created_at', 'updated_at'];

    protected $casts = ['id' => 'integer', 'gender' => 'integer'];

    public function saving(Saving $event)
    {
        $this->setCreatedAt('2019-01-01');
    }
}

```

### Event listener

Quando você precisa monitorar todos os eventos de model, você pode facilmente customizar o `Listener` correspondente, como o listener do model cache abaixo. Quando o model é modificado e excluído, o cache correspondente será excluído.

```php
<?php

declare(strict_types=1);

namespace Hyperf\ModelCache\Listener;

use Hyperf\Database\Model\Events\Deleted;
use Hyperf\Database\Model\Events\Event;
use Hyperf\Database\Model\Events\Saved;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\ModelCache\CacheableInterface;

#[Listener]
class DeleteCacheListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            Deleted::class,
            Saved::class,
        ];
    }

    public function process(object $event)
    {
        if ($event instanceof Event) {
            $model = $event->getModel();
            if ($model instanceof CacheableInterface) {
                $model->deleteCache();
            }
        }
    }
}

```
