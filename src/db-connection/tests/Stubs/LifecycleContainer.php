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

namespace HyperfTest\DbConnection\Stubs;

use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Connectors\ConnectionFactory;
use Hyperf\Database\SQLite\Connectors\SQLiteConnector;
use Hyperf\Database\SQLite\Listener\RegisterConnectionListener;
use Hyperf\DbConnection\ConnectionResolver;
use Hyperf\DbConnection\Pool\PoolFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\NullLogger;
use stdClass;

class LifecycleContainer implements ContainerInterface
{
    public array $entries = [];

    public int $opens = 0;

    public string $path;

    public function __construct(bool $releaseAfterUse = true, bool $lazy = true)
    {
        $this->path = tempnam(sys_get_temp_dir(), 'hyperf-lease-');
        ApplicationContext::setContainer($this);
        $this->entries[ConfigInterface::class] = new Config(['databases' => ['default' => [
            'driver' => 'sqlite', 'database' => $this->path, 'prefix' => '',
            'foreign_key_constraints' => true, 'lazy' => $lazy, 'release_after_use' => $releaseAfterUse,
            'pool' => ['min_connections' => 1, 'max_connections' => 10, 'wait_timeout' => 0.05],
        ]]]);
        $this->entries[StdoutLoggerInterface::class] = new class extends NullLogger implements StdoutLoggerInterface {};
        $this->entries['db.connector.sqlite'] = new class($this) extends SQLiteConnector {
            public function __construct(private LifecycleContainer $container)
            {
            }

            public function connect(array $config)
            {
                ++$this->container->opens;
                return parent::connect($config);
            }
        };
        $this->entries[ConnectionFactory::class] = new ConnectionFactory($this);
        $this->entries[PoolFactory::class] = new PoolFactory($this);
        $this->entries[ConnectionResolverInterface::class] = new ConnectionResolver($this);
        (new RegisterConnectionListener($this))->process(new stdClass());
    }

    public function get(string $id): mixed
    {
        return $this->entries[$id];
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->entries);
    }

    public function set(string $id, mixed $value): void
    {
        $this->entries[$id] = $value;
    }
}
