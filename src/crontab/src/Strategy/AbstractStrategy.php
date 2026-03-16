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

namespace Hyperf\Crontab\Strategy;

use Psr\Container\ContainerInterface;

abstract class AbstractStrategy implements StrategyInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }
}
