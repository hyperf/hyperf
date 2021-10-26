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
        $table = $config->get('databases.default.migrations', 'migrations');
        return make(DatabaseMigrationRepository::class, [
            'resolver' => $reslover,
            'table' => $table,
        ]);
    }
}
