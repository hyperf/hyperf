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

namespace Hyperf\Watcher\Driver;

use Hyperf\Coordinator\Timer;
use Hyperf\Watcher\Option;

abstract class AbstractDriver implements DriverInterface
{
    protected Timer $timer;

    protected ?int $timerId = null;

    public function __construct(protected Option $option)
    {
        $this->timer = new Timer();
    }

    public function __destruct()
    {
        $this->stop();
    }

    public function isDarwin(): bool
    {
        return PHP_OS === 'Darwin';
    }

    public function stop()
    {
        if ($this->timerId) {
            $this->timer->clear($this->timerId);
            $this->timerId = null;
        }
    }
}
