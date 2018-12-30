<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Pool;

use Hyperf\Contract\ConnectionInterface;
use Psr\Container\ContainerInterface;

abstract class Connection implements ConnectionInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Pool
     */
    protected $pool;

    public function __construct(ContainerInterface $container, Pool $pool)
    {
        $this->container = $container;
        $this->pool = $pool;
    }

    public function release(): void
    {
        $this->pool->release($this);
    }
}
