<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\DbConnection\Pool;

use Hyperf\Contract\ConfigInterface;
use Hyperf\DbConnection\Connection;
use Hyperf\Framework\ApplicationContext;
use Hyperf\Pool\Pool;
use Hyperf\Contract\ConnectionInterface;

class DbPool extends Pool
{
    protected $name = 'default';

    protected function createConnection(): ConnectionInterface
    {
        $container = ApplicationContext::getContainer();
        $config = $container->get(ConfigInterface::class);

        $key = sprintf('databases.%s', $this->name);

        return new Connection($container, $config->get($key));
    }
}
