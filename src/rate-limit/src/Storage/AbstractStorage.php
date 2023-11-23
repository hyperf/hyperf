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
namespace Hyperf\RateLimit\Storage;

use bandwidthThrottle\tokenBucket\storage\scope\GlobalScope;
use bandwidthThrottle\tokenBucket\storage\Storage;
use Psr\Container\ContainerInterface;

abstract class AbstractStorage implements Storage, GlobalScope
{
    abstract public function __construct(
        ContainerInterface $container,
        string $key,
        int $timeout,
        array $options = []
    );
}
