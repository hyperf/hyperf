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
namespace Hyperf\Database\PgSQL\Listener;

use Hyperf\Database\Connection;
use Hyperf\Database\PgSQL\PostgreSqlConnection;
use Hyperf\Database\PgSQL\PostgreSqlSwooleExtConnection;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Psr\Container\ContainerInterface;

class RegisterConnectionListener implements ListenerInterface
{
    /**
     * The IoC container instance.
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Create a new connection factory instance.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    /**
     * register pgsql and pgsql-swoole need Connector and Connection.
     */
    public function process(object $event)
    {
        Connection::resolverFor('pgsql', function ($connection, $database, $prefix, $config) {
            return new PostgreSqlConnection($connection, $database, $prefix, $config);
        });
        Connection::resolverFor('pgsql-swoole', function ($connection, $database, $prefix, $config) {
            return new PostgreSqlSwooleExtConnection($connection, $database, $prefix, $config);
        });
    }
}
