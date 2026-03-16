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

namespace Hyperf\Session\Handler;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

class DatabaseHandlerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $connection = $config->get('session.options.connection');
        $table = $config->get('session.options.table');
        $minutes = $config->get('session.options.lifetime', 1200);
        return new DatabaseHandler($connection, $table, $minutes);
    }
}
