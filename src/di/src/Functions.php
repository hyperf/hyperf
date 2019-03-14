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

use Hyperf\Utils\ApplicationContext;

if (! function_exists('make')) {
    /**
     * Create a object instance, if the DI container exist in ApplicationContext,
     * then the object will be create by DI container via `make()` method, if not,
     * the object will create by `new` keyword.
     */
    function make(string $name, array $parameters = [])
    {
        if (ApplicationContext::hasContainter()) {
            $container = ApplicationContext::getContainer();
            if (! method_exists($container, 'make')) {
                throw new \RuntimeException(sprintf('Make error, make() method does not exist in %s', get_class($container)));
            }
            return $container->make($name, $parameters);
        }
        $parameters = array_values($parameters);
        return new $name(...$parameters);
    }
}
