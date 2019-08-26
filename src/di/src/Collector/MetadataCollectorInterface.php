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

interface MetadataCollectorInterface
{
    /**
     * Retrieve the metadata via key.
     * @param null|mixed $default
     */
    public function get(string $key, $default = null);

    /**
     * Set the metadata to holder.
     * @param mixed $value
     */
    public function set(string $key, $value): void;

    /**
     * Determine if the metadata exist.
     * If exist will return true, otherwise return false.
     */
    public function has(string $key): bool;

    /**
     * Set the metadata.
     */
    public function setMetadata(array $metadata): void;

    /**
     * Get all metadata.
     */
    public function getMetadata(): array;
}
