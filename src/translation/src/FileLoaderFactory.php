<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Translation;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Utils\Filesystem\Filesystem;
use Psr\Container\ContainerInterface;

class FileLoaderFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $files = $container->get(Filesystem::class);
        $path = $config->get('translation.path');

        return make(FileLoader::class, compact('files', 'path'));
    }
}
