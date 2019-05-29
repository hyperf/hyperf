<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Database\Schema;

use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Utils\ApplicationContext;

class Schema
{
    public static function __callStatic($name, $arguments)
    {
        $container = ApplicationContext::getContainer();
        $resolver = $container->get(ConnectionResolverInterface::class);
        $connection = $resolver->connection();
        return $connection->getSchemaBuilder()->{$name}(...$arguments);
    }

    public function __call($name, $arguments)
    {
        return self::__callStatic($name, $arguments);
    }

    /**
     * Create a connection by ConnectionResolver.
     */
    public function connection(string $name = 'default'): ConnectionInterface
    {
        $container = ApplicationContext::getContainer();
        $resolver = $container->get(ConnectionResolverInterface::class);
        return $resolver->connection($name);
    }
}
