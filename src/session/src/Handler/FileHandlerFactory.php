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

namespace Hyperf\Session\Handler;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Support\Filesystem\Filesystem;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

class FileHandlerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $path = $config->get('session.options.path');
        $minutes = $config->get('session.options.gc_maxlifetime', 1200);
        if (! $path) {
            throw new InvalidArgumentException('Invalid session path.');
        }
        return new FileHandler($container->get(Filesystem::class), $path, $minutes);
    }
}
