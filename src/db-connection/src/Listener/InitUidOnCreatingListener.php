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

namespace Hyperf\DbConnection\Listener;

use Hyperf\Database\Model\Concerns\HasUlids;
use Hyperf\Database\Model\Concerns\HasUuids;
use Hyperf\Database\Model\Events\Creating;
use Hyperf\Event\Contract\ListenerInterface;

use function Hyperf\Support\class_uses_recursive;

class InitUidOnCreatingListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            Creating::class,
        ];
    }

    public function process(object $event): void
    {
        $model = $event->getModel();
        $class = get_class($model);

        foreach (class_uses_recursive($class) as $trait) {
            if (! in_array($trait, [HasUuids::class, HasUlids::class])) {
                continue;
            }

            foreach ($model->uniqueIds() as $column) {
                if (empty($model->{$column})) {
                    $model->{$column} = $model->newUniqueId();
                }
            }
        }
    }
}
