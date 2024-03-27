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
use Hyperf\Filesystem\Adapter\LocalAdapterFactory;
use Hyperf\Filesystem\Contract\AdapterFactoryInterface;
use Hyperf\Filesystem\Exception\InvalidArgumentException;
use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use Psr\Container\ContainerInterface;

class FilesystemFactory
{
    public function __construct(private ContainerInterface $container, private ConfigInterface $config)
    {
    }

    public function get($adapterName): Filesystem
    {
        $options = $this->config->get('file', [
            'default' => 'local',
            'storage' => [
                'local' => [
                    'driver' => LocalAdapterFactory::class,
                    'root' => BASE_PATH . '/runtime',
                ],
            ],
        ]);
        $adapter = $this->getAdapter($options, $adapterName);
        if (Version::isV2()) {
            return new Filesystem($adapter, $options['storage'][$adapterName] ?? []);
        }

        return new Filesystem($adapter, new Config($options['storage'][$adapterName]));
    }

    public function getAdapter($options, $adapterName)
    {
        if (! $options['storage'] || ! $options['storage'][$adapterName]) {
            throw new InvalidArgumentException("file configurations are missing {$adapterName} options");
        }
        /** @var AdapterFactoryInterface $driver */
        $driver = $this->container->get($options['storage'][$adapterName]['driver']);
        return $driver->make($options['storage'][$adapterName]);
    }
}
