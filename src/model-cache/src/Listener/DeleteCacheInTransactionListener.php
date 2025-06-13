<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\ModelCache\Listener;

use Hyperf\Database\Events\TransactionCommitted;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\ModelCache\InvalidCacheManager;

class DeleteCacheInTransactionListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            TransactionCommitted::class,
        ];
    }

    public function process(object $event): void
    {
        if (! $event instanceof TransactionCommitted) {
            return;
        }

        if ($event->connection->transactionLevel() === 0) {
            InvalidCacheManager::instance()->delete();
        }
    }
}
