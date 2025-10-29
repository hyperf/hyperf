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

namespace HyperfTest\RateLimit\Stub\Storage;

use bandwidthThrottle\tokenBucket\storage\scope\GlobalScope;
use bandwidthThrottle\tokenBucket\storage\Storage;
use Hyperf\RateLimit\Storage\StorageInterface;
use Psr\Container\ContainerInterface;

class EmptyStorage implements StorageInterface, Storage, GlobalScope
{
    public function __construct(ContainerInterface $container, string $key, int $timeout, array $options = [])
    {
    }

    public function getMutex()
    {
        return new class {
            public function check($callback)
            {
                return $this;
            }

            public function then($callback)
            {
                return $this;
            }
        };
    }

    public function isBootstrapped()
    {
    }

    public function bootstrap($microtime)
    {
    }

    public function remove()
    {
    }

    public function setMicrotime($microtime)
    {
    }

    public function letMicrotimeUnchanged()
    {
    }

    public function getMicrotime()
    {
    }
}
