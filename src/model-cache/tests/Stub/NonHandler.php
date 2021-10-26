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
namespace HyperfTest\ModelCache\Stub;

use Hyperf\ModelCache\Config;
use Hyperf\ModelCache\Handler\HandlerInterface;

class NonHandler implements HandlerInterface
{
    public $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function get($key, $default = null)
    {
    }

    public function set($key, $value, $ttl = null)
    {
    }

    public function delete($key)
    {
    }

    public function clear()
    {
    }

    public function getMultiple($keys, $default = null)
    {
    }

    public function setMultiple($values, $ttl = null)
    {
    }

    public function deleteMultiple($keys)
    {
    }

    public function has($key)
    {
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function incr($key, $column, $amount): bool
    {
        return true;
    }
}
