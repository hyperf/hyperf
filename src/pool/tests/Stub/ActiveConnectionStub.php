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

namespace HyperfTest\Pool\Stub;

use Exception;
use Hyperf\Pool\Connection;

class ActiveConnectionStub extends Connection
{
    public $count = 0;

    public function getActiveConnection()
    {
        if ($this->count === 0) {
            ++$this->count;
            throw new Exception();
        }

        return $this;
    }

    public function reconnect(): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }
}
