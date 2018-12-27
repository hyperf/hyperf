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

namespace Hyperf\Di\Annotation;

class MetadataHolder implements MetadataHolderInterface
{
    /**
     * @var array
     */
    private $container = [];

    /**
     * Retrieve the metadata via key.
     */
    public function get(string $key, $default = null)
    {
        return $this->container[$key] ?? $default;
    }

    /**
     * Set the metadata to holder.
     */
    public function set(string $key, $value): void
    {
        $this->container[$key] = $value;
    }

    /**
     * Serialize the all metadata to a string.
     */
    public function serialize(): string
    {
        return serialize($this->container);
    }

    /**
     * Deserialize the serialized metadata and set the metadata to holder.
     */
    public function deserialize(string $metadata): bool
    {
        $data = unserialize($metadata);
        $this->container = $data;
        return true;
    }
}
