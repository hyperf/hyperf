<?php


namespace Hyperflex\Di;


interface MetadataCollectorInterface
{

    /**
     * Retrieve the metadata via key.
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