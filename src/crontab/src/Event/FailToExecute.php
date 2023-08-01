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
namespace Hyperf\Crontab\Event;

class_alias(CrontabFailed::class, FailToExecute::class);

if (! class_exists(FailToExecute::class)) {
    /**
     * @deprecated since 3.0, please use Hyperf\Crontab\Event\CrontabFailed instead.
     */
    class FailToExecute extends CrontabFailed
    {
    }
}
