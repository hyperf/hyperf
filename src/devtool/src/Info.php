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
namespace Hyperf\Devtool;

use Hyperf\Devtool\Adapter\AbstractAdapter;
use Psr\Container\ContainerInterface;

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
