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
namespace Hyperf\Cache;

use Psr\Container\ContainerInterface;

class CacheFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return $container->get(CacheManager::class)->get();
    }
}
