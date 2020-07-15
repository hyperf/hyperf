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
use Hyperf\Contract\ContainerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\MySqlConnection;
use Hyperf\DbConnection\Collector\TableCollector;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\AfterWorkerStart;
use Hyperf\Process\Event\BeforeProcessHandle;

class InitTableCollectorListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var TableCollector
     */
    protected $collector;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
        ];
    }

    public function process(object $event)
    {
        try {
            $databases = $this->config->get('databases', []);
            $pools = array_keys($databases);
            foreach ($pools as $name) {
                $this->initTableCollector($name);
            }
        } catch (\Throwable $throwable) {
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

        /** @var \Hyperf\Database\Schema\Builder $schemaBuilder */
        $schemaBuilder = $connection->getSchemaBuilder();
        $columns = $schemaBuilder->getColumns();

        foreach ($columns as $column) {
            $this->collector->add($pool, $column);
        }
    }
}
