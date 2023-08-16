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
namespace Hyperf\Crontab\Strategy;

use Hyperf\Coroutine\Coroutine;
use Hyperf\Crontab\Crontab;

class CoroutineStrategy extends AbstractStrategy
{
    public function dispatch(Crontab $crontab)
    {
        Coroutine::create(function () use ($crontab) {
            $executor = $this->container->get(Executor::class);
            $executor->execute($crontab);
        });
    }
}
