<?php
/**
 * FildLoaderFactory.php
 *
 * Author: wangyi <chunhei2008@qq.com>
 *
 * Date:   2019-07-25 16:38
 * Copyright: (C) 2014, Guangzhou YIDEJIA Network Technology Co., Ltd.
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
        $files  = $container->get(Filesystem::class);
        $path   = $config->get('translation.lang');

        return make(FileLoader::class, compact('files', 'path'));

    }
}