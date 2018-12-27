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

namespace Hyperf\Framework\DependencyInjection;

use function \DI\autowire as autowire;
use function \DI\factory as factory;
use function is_array;
use function is_callable;
use function is_string;

class Definition
{
    /**
     * Adapte more useful difinition syntax.
     */
    public static function reorganizeDefinitions(array $definitions): array
    {
        foreach ($definitions as $identifier => $definition) {
            if (is_string($definition) && class_exists($definition)) {
                if (method_exists($definition, '__invoke')) {
                    $definitions[$identifier] = factory($definition);
                } else {
                    $definitions[$identifier] = autowire($definition);
                }
            } elseif (is_array($definition) && is_callable($definition)) {
                $definitions[$identifier] = factory($definition);
            }
        }
        return $definitions;
    }
}
