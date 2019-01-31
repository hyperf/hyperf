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

namespace Hyperf\Cache\Driver;

use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Packer\PhpSerializer;
use Hyperf\Contract\PackerInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Psr\Container\ContainerInterface;

abstract class Driver implements DriverInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var PackerInterface
     */
    protected $packer;

    public function __construct(ContainerInterface $container, array $config)
    {
        $this->container = $container;
        $this->config = $config;

        $packerClass = $config['packer'] ?? PhpSerializer::class;
        $this->packer = $container->get($packerClass);
    }

    public function getAnnotationValue(string $className, string $method, array $arguments)
    {
        $collector = AnnotationCollector::get($className);
        $config = $collector['_m'][$method][Cacheable::class] ?? [];
        if (empty($config)) {
            $config = $collector['_c'][Cacheable::class] ?? [];
        }

        $key = $config['key'] ?? 'cache:' . md5($className . ':' . $method);
        $key = $this->formatKey($key, $arguments);
        $ttl = $config['ttl'] ?? $this->config['ttl'] ?? 3600;

        return [$key, $ttl];
    }

    protected function formatKey($key, array $arguments)
    {
        $hasObject = false;
        foreach ($arguments as $argument) {
            if (is_object($argument)) {
                $hasObject = true;
                break;
            }
        }

        if ($hasObject) {
            $key .= ':' . md5(serialize($arguments));
        } else {
            $key .= implode(':', $arguments);
        }

        if (strlen($key) > 64) {
            $key = 'cache:' . md5($key);
        }

        return $key;
    }
}
