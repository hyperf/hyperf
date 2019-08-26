<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Di\Collector;

use Hyperf\Utils\Arr;
use Psr\Container\ContainerInterface;

abstract class MetadataCollector implements MetadataCollectorInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $metadata;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $container->get(MetadataCacheCollector::class)->addCollector(static::class);
    }

    /**
     * Retrieve the metadata via key.
     * @param null|mixed $default
     */
    public function get(string $key, $default = null)
    {
        return Arr::get($this->metadata, $key) ?? $default;
    }

    /**
     * Set the metadata to holder.
     * @param mixed $value
     */
    public function set(string $key, $value): void
    {
        Arr::set($this->metadata, $key, $value);
    }

    /**
     * Determine if the metadata exist.
     * If exist will return true, otherwise return false.
     */
    public function has(string $key): bool
    {
        return Arr::has($this->metadata, $key);
    }

    /**
     * Get all metadata.
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
    }
}
