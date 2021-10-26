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
use Hyperf\Contract\ConfigInterface;
use Hyperf\Utils\ApplicationContext;

if (! function_exists('config')) {
    function config(string $key, $default = null)
    {
        if (! ApplicationContext::hasContainer()) {
            throw new \RuntimeException('The application context lacks the container.');
        }
        $container = ApplicationContext::getContainer();
        if (! $container->has(ConfigInterface::class)) {
            throw new \RuntimeException('ConfigInterface is missing in container.');
        }
        return $container->get(ConfigInterface::class)->get($key, $default);
    }
}
