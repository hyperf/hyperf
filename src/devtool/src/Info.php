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

namespace Hyperf\Devtool;

use Psr\Container\ContainerInterface;
use Hyperf\Devtool\Adapter\AbstractAdapter;

class Info
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function get(string $key): ?AbstractAdapter
    {
        if (! $this->has($key)) {
            return null;
        }
        $class = __NAMESPACE__ . '\\Adapter\\' . ucfirst($key);
        return $this->container->get($class);
    }

    public function has(string $key): bool
    {
        return class_exists(__NAMESPACE__ . '\\Adapter\\' . ucfirst($key));
    }
}
