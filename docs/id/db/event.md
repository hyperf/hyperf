# Events

Model events mengimplementasikan interface [psr/event-dispatcher](https://github.com/php-fig/event-dispatcher) dan didukung bawaan oleh komponen [hyperf/event](https://github.com/hyperf/event).

## Operation Events

ORM akan memicu event berikut selama operasinya. Anda bisa listen event ini sesuai kebutuhan.

| Event | Deskripsi |
| :--------: | :----: |
| Hyperf\Database\Events\QueryExecuted | Setelah query dijalankan |
| Hyperf\Database\Events\StatementPrepared | Setelah statement SQL siap |
| Hyperf\Database\Events\TransactionBeginning | Setelah transaksi dimulai |
| Hyperf\Database\Events\TransactionCommitted | Setelah transaksi di-commit |
| Hyperf\Database\Events\TransactionRolledBack | Setelah transaksi di-rollback |

### SQL Execution Listener

Berdasarkan operation events ORM di atas, mari buat listener untuk mencatat statement SQL. Dengan ini, kita bisa mencatat SQL setiap kali dijalankan.
Pertama, definisikan `DbQueryExecutedListener`, implementasikan `Hyperf\Event\Contract\ListenerInterface`, dan beri annotation `Hyperf\Event\Annotation\Listener` pada class-nya. Hyperf otomatis akan mendaftarkan listener ini ke event dispatcher saat aplikasi jalan, lalu mengeksekusi logikanya ketika event dipicu. Contohnya:

```php
<?php

declare(strict_types=1);

namespace App\Listener;

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
    private LoggerInterface $logger;

    public function __construct(ContainerInterface $container)
    {
        // Output ke log bernama 'sql'. Jika belum ada, Anda perlu menambahkan konfigurasi sendiri.
        // Nama log 'sql' tidak wajib; hanya digunakan di sini untuk membedakan log eksekusi SQL dari log biasa.
        $this->logger = $container->get(LoggerFactory::class)->get('sql');
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

## Model Events

Model events di Hyperf sedikit berbeda dari `Eloquent ORM`. `Eloquent ORM` memakai `Observer` untuk listen pada model events, sementara `Hyperf` menyediakan `hook functions` dan `event listeners`.

### Hook Functions

| Nama Event | Waktu Trigger | Dapat Diblokir | Keterangan |
| :------------: | :----------------: | :--------: | :--------------------------: |
| booting | Sebelum model pertama dimuat | Tidak | Hanya sekali dalam siklus proses |
| booted | Setelah model pertama dimuat | Tidak | Hanya sekali dalam siklus proses |
| retrieved | Setelah data diisi (populate) | Tidak | Setiap kali model di-query dari DB atau cache |
| creating | Saat data sedang dibuat | Ya | |
| created | Setelah data dibuat | Tidak | |
| updating | Saat data sedang diperbarui | Ya | |
| updated | Setelah data diperbarui | Tidak | |
| saving | Saat data sedang dibuat atau diperbarui | Ya | |
| saved | Setelah data dibuat atau diperbarui | Tidak | |
| restoring | Saat data soft-deleted sedang direstore | Ya | |
| restored | Setelah data soft-deleted di-restore | Tidak | |
| deleting | Saat data sedang dihapus | Ya | |
| deleted | Setelah data dihapus | Tidak | |
| forceDeleting | Saat data sedang di-force-delete | Ya | |
| forceDeleted | Setelah data di-force-delete | Tidak | |

Memakai event untuk model tertentu tentunya sangat mudah, tinggal tambahkan method yang sesuai ke model. Misalnya, trigger event `saving` untuk menimpa field `created_at` saat menyimpan data:

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
     * Tabel yang terkait dengan model ini.
     *
     * @var string
     */
    protected $table = 'user';

    /**
     * Atribut yang dapat diisi secara massal.
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

### Event Listener

Kalau perlu mendengarkan semua model events, Anda tinggal mendefinisikan `Listener` yang sesuai. Contoh berikut, model cache listener menghapus cache setelah model dimodifikasi atau dihapus.

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

### Observer

Berterimakasih sekali dengan komponen [hyperf/model-listener](https://github.com/hyperf/blob/master/src/model-listener/), karenanya kita juga bisa memakai `Observer` untuk mendengarkan model events.
Cukup gunakan annotation [ModelListener](https://github.com/hyperf/hyperf/blob/master/src/model-listener/src/Annotation/ModelListener.php) untuk mendefinisikan observer. Contohnya:

```php
<?php
use Hyperf\ModelListener\Annotation\ModelListener;
use App\Model\User;
use Hyperf\Database\Model\Events\Creating;
use Hyperf\Database\Model\Events\Created;

/**
 * Mendefinisikan UserObserver untuk mendengarkan event model User.
 * Anda juga bisa mendengarkan beberapa model dengan mengisi parameter models.
 * Class ini otomatis didaftarkan di container sebagai singleton.
 */
#[ModelListener(models: [ User::class ])]
class UserObserver
{
    public function creating(Creating $event)
    {
        $user = $event->getModel();
        // Dipicu saat membuat user
    }
    
    public function created(Created $event)
    {
        $user = $event->getModel();
        // Di-trigger setelah user dibuat
    }
    
    //... Event lainnya dihilangkan
}
```
