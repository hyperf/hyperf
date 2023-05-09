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
namespace Hyperf\Config {
    use Hyperf\Context\ApplicationContext;
    use Hyperf\Contract\ConfigInterface;

    /**
     * @param mixed $default
     */
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

namespace {
    if (! function_exists('config')) {
        /**
         * @deprecated since v3.1, use `Hyperf\Config\config()` instead.
         * @param null|mixed $default
         */
        function config(string $key, $default = null)
        {
            \Hyperf\Config\config($key, $default);
        }
    }
}
