<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\ModelCache\Listener;

use Hyperf\Event\Annotation\Listener;
use Hyperf\Database\Model\Events\Event;
use Hyperf\Database\Model\Events\Saved;
use Hyperf\Database\Model\Events\Created;
use Hyperf\Database\Model\Events\Deleted;
use Hyperf\Database\Model\Events\Updated;
use Hyperf\Event\Contract\ListenerInterface;

/**
 * @Listener
 */
class DeleteCacheListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            Deleted::class,
            Updated::class,
            Created::class,
            Saved::class,
        ];
    }

    public function process(object $event)
    {
        if ($event instanceof Event) {
            $model = $event->getModel();
            if (method_exists($model, 'deleteCache')) {
                $model->deleteCache();
            }
        }
    }
}
