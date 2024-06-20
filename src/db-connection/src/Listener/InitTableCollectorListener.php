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

namespace Hyperf\DbConnection\Listener;

use Hyperf\Command\Event\BeforeHandle;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\MySqlConnection;
use Hyperf\DbConnection\Collector\TableCollector;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\AfterWorkerStart;
use Hyperf\Process\Event\BeforeProcessHandle;
use Hyperf\Server\Event\MainCoroutineServerStart;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class InitTableCollectorListener implements ListenerInterface
{
    protected ConfigInterface $config;

    protected LoggerInterface $logger;

    protected TableCollector $collector;

    public function __construct(protected ContainerInterface $container)
    {
        $this->config = $container->get(ConfigInterface::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->collector = $container->get(TableCollector::class);
    }

    public function listen(): array
    {
        return [
            BeforeHandle::class,
            AfterWorkerStart::class,
            BeforeProcessHandle::class,
            MainCoroutineServerStart::class,
        ];
    }

    public function process(object $event): void
    {
        try {
            $databases = $this->config->get('databases', []);
            $pools = array_keys($databases);
            foreach ($pools as $name) {
                $this->initTableCollector($name);
            }
        } catch (Throwable $throwable) {
            $this->logger->error((string) $throwable);
        }
    }

    public function initTableCollector(string $pool)
    {
        if ($this->collector->has($pool)) {
            return;
        }

        /** @var ConnectionResolverInterface $connectionResolver */
        $connectionResolver = $this->container->get(ConnectionResolverInterface::class);
        /** @var MySqlConnection $connection */
        $connection = $connectionResolver->connection($pool);

        $schemaBuilder = $connection->getSchemaBuilder();
        $columns = $schemaBuilder->getColumns();

        foreach ($columns as $column) {
            $this->collector->add($pool, $column);
        }
    }
}
