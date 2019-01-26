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

namespace Hyperf\Di;

interface MetadataCollectorInterface
{
    /**
     * Retrieve the metadata via key.
     * @param null|mixed $default
     */
    public static function get(string $key, $default = null);

    /**
     * Set the metadata to holder.
     */
    public static function set(string $key, $value): void;

    /**
     * Serialize the all metadata to a string.
     */
    public static function serialize(): string;

    /**
     * Deserialize the serialized metadata and set the metadata to holder.
     */
    public static function deserialize(string $metadata): bool;
}
