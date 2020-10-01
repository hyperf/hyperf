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
namespace Hyperf\Redis;

trait ScanCaller
{
    public function scan(&$cursor, $pattern = null, $count = 0)
    {
        return $this->__call('scan', [&$cursor, $pattern, $count]);
    }

    public function hScan($key, &$cursor, $pattern = null, $count = 0)
    {
        return $this->__call('hScan', [$key, &$cursor, $pattern, $count]);
    }

    public function zScan($key, &$cursor, $pattern = null, $count = 0)
    {
        return $this->__call('zScan', [$key, &$cursor, $pattern, $count]);
    }

    public function sScan($key, &$cursor, $pattern = null, $count = 0)
    {
        return $this->__call('sScan', [$key, &$cursor, $pattern, $count]);
    }
}
