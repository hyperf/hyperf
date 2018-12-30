<?php

namespace Hyperf\Database\Model;

use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\ConnectionInterface;

class Register
{
    /**
     * The connection resolver instance.
     *
     * @var ConnectionResolverInterface
     */
    protected static $resolver;

    /**
     * Resolve a connection instance.
     *
     * @param  string|null $connection
     * @return ConnectionInterface
     */
    public static function resolveConnection($connection = null)
    {
        return static::$resolver->connection($connection);
    }

    /**
     * Get the connection resolver instance.
     *
     * @return ConnectionResolverInterface
     */
    public static function getConnectionResolver()
    {
        return static::$resolver;
    }

    /**
     * Set the connection resolver instance.
     *
     * @param  ConnectionResolverInterface $resolver
     * @return void
     */
    public static function setConnectionResolver(ConnectionResolverInterface $resolver)
    {
        static::$resolver = $resolver;
    }

    /**
     * Unset the connection resolver for models.
     *
     * @return void
     */
    public static function unsetConnectionResolver()
    {
        static::$resolver = null;
    }
}