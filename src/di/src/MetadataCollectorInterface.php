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
     * @param mixed $value
     */
    public static function set(string $key, $value): void;

    public static function clear(?string $key = null): void;

    /**
     * Serialize the all metadata to a string.
     */
    public static function serialize(): string;

    /**
     * Deserialize the serialized metadata and set the metadata to holder.
     */
    public static function deserialize(string $metadata): bool;

    /**
     * Return all metadata array.
     */
    public static function list(): array;
}
