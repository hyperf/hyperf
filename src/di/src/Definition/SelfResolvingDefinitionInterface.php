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

namespace Hyperf\Di\Definition;

use Psr\Container\ContainerInterface;

interface SelfResolvingDefinitionInterface
{
    /**
     * Resolve the definition and return the resulting value.
     */
    public function resolve(ContainerInterface $container);

    /**
     * Check if a definition can be resolved.
     */
    public function isResolvable(ContainerInterface $container): bool;
}
