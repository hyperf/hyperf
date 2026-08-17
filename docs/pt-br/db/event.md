# Evento

Eventos de model são implementados na interface [psr/event-dispatcher](https://github.com/php-fig/event-dispatcher).

## Listener customizado

Graças ao suporte do componente [hyperf/event](https://github.com/hyperf/event), usuários podem monitorar facilmente os seguintes eventos.
Por exemplo: `QueryExecuted`, `StatementPrepared`, `TransactionBeginning`, `TransactionCommitted`, `TransactionRolledBack`.
A seguir, vamos implementar um listener que registra SQL e falar sobre como usá-lo.
Primeiro, definimos `DbQueryExecutedListener`, implementamos a interface `Hyperf\Event\Contract\ListenerInterface` e definimos a anotação `Hyperf\Event\Annotation\Listener` na classe, para que o Hyperf registre automaticamente o listener no agendador de eventos, sem qualquer configuração manual. O exemplo é:

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

## Eventos de model

Os eventos de model não são consistentes com o `EloquentORM`, que usa `Observer` para escutar eventos de model. O `Hyperf` usa diretamente `hooks` para tratar eventos correspondentes. Se você ainda gosta do estilo de `Observer`, pode implementar um `event listener` por conta própria. Claro, você também pode nos avisar no [issue#2](https://github.com/hyperf/hyperf/issues/2).

### Função hook

| Nome do evento | Disparo (na prática)                               | Bloqueia? | Observação                                                   |
|:--------------:|:---------------------------------------------------:|:---------:|:------------------------------------------------------------:|
| booting        | Antes do model ser carregado pela primeira vez      | não       | Dispara apenas uma vez no ciclo de vida do processo          |
| booted         | Depois do model ser carregado pela primeira vez     | não       | Dispara apenas uma vez no ciclo de vida do processo          |
| retrieved      | Depois de preencher os dados                        | não       | Dispara sempre que o model é consultado do DB ou cache       |
| creating       | Quando os dados são criados                         | sim       |                                                              |
| created        | Depois dos dados serem criados                      | não       |                                                              |
| updating       | Quando os dados são atualizados                     | sim       |                                                              |
| updated        | Depois dos dados serem atualizados                  | não       |                                                              |
| saving         | Quando os dados são criados ou atualizados          | sim       |                                                              |
| saved          | Depois dos dados serem criados ou atualizados       | não       |                                                              |
| restoring      | Quando dados com soft delete são restaurados        | sim       |                                                              |
| restored       | Depois da recuperação de dados com soft delete      | não       |                                                              |
| deleting       | Quando os dados são deletados                       | sim       |                                                              |
| deleted        | Depois da deleção dos dados                         | não       |                                                              |
| forceDeleting  | Quando os dados são deletados à força               | sim       |                                                              |
| forceDeleted   | Depois dos dados serem deletados à força            | não       |                                                              |

O uso de eventos para um model é bem simples: basta adicionar o método correspondente ao model. Por exemplo, quando os dados são salvos abaixo, o evento `saving` é disparado e o campo `created_at` é sobrescrito ativamente.

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
     * A tabela associada ao model.
     *
     * @var string
     */
    protected $table = 'user';

    /**
     * Os atributos que podem ser atribuídos em massa.
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

Quando você precisa monitorar todos os eventos do model, você pode customizar facilmente o `Listener` correspondente, como o listener do model cache abaixo. Quando o model é modificado ou deletado, o cache correspondente também será deletado.

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

