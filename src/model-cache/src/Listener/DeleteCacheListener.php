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

use Hyperf\Database\Model\Events\Deleted;
use Hyperf\Database\Model\Events\Event;
use Hyperf\Database\Model\Events\Saved;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\ModelCache\CacheableInterface;
use Hyperf\ModelCache\InvalidCacheManager;

class DeleteCacheListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            Deleted::class,
            Saved::class,
        ];
    }

    public function process(object $event): void
    {
        if (! $event instanceof Event) {
            return;
        }

        $model = $event->getModel();
        if (! $model instanceof CacheableInterface) {
            return;
        }

        if ($model->getConnection()->transactionLevel() > 0) {
            InvalidCacheManager::instance()->push($model);
            return;
        }

        $model->deleteCache();
    }
}
