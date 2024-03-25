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

namespace Hyperf\Redis\Traits;

trait ScanCaller
{
    public function scan(&$cursor, ...$arguments)
    {
        return $this->__call('scan', array_merge([&$cursor], $arguments));
    }

    public function hScan($key, &$cursor, ...$arguments)
    {
        return $this->__call('hScan', array_merge([$key, &$cursor], $arguments));
    }

    public function zScan($key, &$cursor, ...$arguments)
    {
        return $this->__call('zScan', array_merge([$key, &$cursor], $arguments));
    }

    public function sScan($key, &$cursor, ...$arguments)
    {
        return $this->__call('sScan', array_merge([$key, &$cursor], $arguments));
    }
}
