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
namespace Hyperf\Translation;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Support\Filesystem\Filesystem;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;

class FileLoaderFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $files = $container->get(Filesystem::class);
        $path = $config->get('translation.path', BASE_PATH . '/storage/languages');

        return make(FileLoader::class, compact('files', 'path'));
    }
}
