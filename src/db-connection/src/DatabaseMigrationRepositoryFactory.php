<?php

namespace Hyperf\DbConnection;


use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Migrations\DatabaseMigrationRepository;
use Psr\Container\ContainerInterface;

class DatabaseMigrationRepositoryFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $reslover = $container->get(ConnectionResolverInterface::class);
        $config = $container->get(ConfigInterface::class);
        $table = $config->get('databases.migrations', 'migrations');
        return make(DatabaseMigrationRepository::class, [
            'resolver' => $reslover,
            'table' => $table
        ]);
    }

}