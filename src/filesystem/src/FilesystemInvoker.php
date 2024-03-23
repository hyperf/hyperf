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

namespace Hyperf\Filesystem;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

class FilesystemInvoker
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $name = $config->get('file.default', 'local');
        $factory = $container->get(FilesystemFactory::class);
        return $factory->get($name);
    }
}
