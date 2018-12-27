<?php


namespace Hyperflex\Di\Annotation;


interface MetadataHolderInterface
{

    /**
     * Retrieve the metadata via key.
     */
    public function get(string $key, $default = null);

    /**
     * Set the metadata to holder.
     */
    public function set(string $key, $value): void;

    /**
     * Serialize the all metadata to a string.
     */
    public function serialize(): string;

    /**
     * Deserialize the serialized metadata and set the metadata to holder.
     */
    public function deserialize(string $metadata): bool;

}