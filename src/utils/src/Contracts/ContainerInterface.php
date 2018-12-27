<?php

namespace Hyperflex\Utils\Contracts;


interface ContainerInterface
{

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     * @return mixed Entry.
     */
    public static function get($id);

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     * @return bool
     */
    public static function has($id);

    /**
     * Sets a value and its identifier to the container and returns it.
     *
     * @param string $id Identifier of the entry.
     * @param mixed $value The value that you set into container.
     * @return mixed Returns the value that you set into.
     */
    public static function set($id, $value);

}