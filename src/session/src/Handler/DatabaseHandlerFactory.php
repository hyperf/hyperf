<?php

declare(strict_types=1);

namespace Hyperf\Session\Handler;

use Hyperf\Contract\ConfigInterface;
//use Hyperf\DbConnection\Db;
use Psr\Container\ContainerInterface;

class DatabaseHandlerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $connection = $config->get('session.options.connection');
        $table = $config->get('session.options.table');
        $minutes = $config->get('session.options.lifetime', 1200);
        //return new DatabaseHandler(Db::connection($connection), $table, $minutes);
        return new DatabaseHandler($connection, $table, $minutes);
    }
}
