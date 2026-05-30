# Event

Model event diimplementasikan pada interface
[psr/event-dispatcher](https://github.com/php-fig/event-dispatcher).

## Custom listener

Berkat dukungan komponen [hyperf/event](https://github.com/hyperf/event),
pengguna dapat dengan mudah memantau event berikut:
Misalnya `QueryExecuted`, `StatementPrepared`, `TransactionBeginning`,
`TransactionCommitted`, `TransactionRolledBack`.

Selanjutnya, kita akan mengimplementasikan sebuah listener yang merekam SQL dan
membahas cara menggunakannya.

Pertama, kita mendefinisikan `DbQueryExecutedListener`, mengimplementasikan
interface `Hyperf\Event\Contract\ListenerInterface` dan mendefinisikan anotasi
`Hyperf\Event\Annotation\Listener` pada kelas tersebut, sehingga Hyperf akan
secara otomatis mendaftarkan listener ke event scheduler tanpa konfigurasi
manual apa pun. Contoh kodenya adalah sebagai berikut:

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

## Event Model

Event model tidak sama dengan `EloquentORM`, yang menggunakan `Observer` untuk
mendengarkan event model. `Hyperf` secara langsung menggunakan `hooks` untuk
menangani event yang sesuai. Jika Anda masih menyukai cara `Observer`, Anda
dapat mengimplementasikan `event listener` sendiri. Tentu saja, Anda juga dapat
memberi tahu kami di [issue#2](https://github.com/hyperf/hyperf/issues/2).

### Fungsi Hook

| Nama Event | Waktu Pemicuan | Apakah blocking? | Catatan |
|:------------:|:-----------------------------------------------:|:----------------:|:----------------------------------------------------------:|
| booting | Sebelum model dimuat untuk pertama kalinya | tidak | Hanya dipicu sekali dalam siklus hidup proses |
| booted | Setelah model dimuat untuk pertama kalinya | tidak | Hanya dipicu sekali dalam siklus hidup proses |
| retrieved | Setelah mengisi data | tidak | Dipicu setiap kali model dikueri dari DB atau cache |
| creating | Saat data dibuat | ya | |
| created | Setelah data dibuat | tidak | |
| updating | Saat data diperbarui | ya | |
| updated | Setelah data diperbarui | tidak | |
| saving | Saat data dibuat atau diperbarui | ya | |
| saved | Setelah data dibuat atau diperbarui | tidak | |
| restoring | Saat data soft-deleted dipulihkan | ya | |
| restored | Setelah data soft-deleted dipulihkan | tidak | |
| deleting | Saat data dihapus | ya | |
| deleted | Setelah data dihapus | tidak | |
| forceDeleting | Saat data dihapus secara paksa | ya | |
| forceDeleted | Setelah data dihapus secara paksa | tidak | |

Penggunaan event pada model sangatlah sederhana, cukup tambahkan method yang
sesuai pada model. Sebagai contoh, ketika data disimpan di bawah ini, event
`saving` akan dipicu, dan field `created_at` akan ditulis ulang secara aktif.

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

Ketika Anda perlu memantau semua event model, Anda dapat dengan mudah
menyesuaikan `Listener` yang sesuai, seperti listener cache model di bawah ini.
Ketika model diubah atau dihapus, cache yang sesuai akan dihapus.

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
