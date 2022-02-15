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
namespace Hyperf\Database;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\Connectors\ConnectorInterface;
use Hyperf\Utils\Str;
use InvalidArgumentException;

class ConnectionManager
{
    /**
     * @var \Hyperf\Contract\ConfigInterface
     */
    private $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function register(string $name, array $data)
    {
        return $this->config->set('database.' . $name, $data);
    }

    public function registerOrAppend(string $name, array $data)
    {
        $key = 'database.' . $name;
        return $this->config->set($key, array_merge($this->config->get($key, []), $data));
    }

    /**
     * Create a connector instance based on the configuration.
     *
     * @throws \InvalidArgumentException
     * @return ConnectorInterface
     */
    public function getConnector(string $name)
    {
        return $this->getTarget($name, 'connector');
    }

    /**
     * Create a new connection instance.
     *
     * @throws \InvalidArgumentException
     * @return \Hyperf\Database\Connection
     */
    public function getConnection(string $name, array $arguments)
    {
        return $this->getTarget($name, 'connection', $arguments);
    }

    private function getTarget(string $name, string $target, ?array $arguments = [])
    {
        $result = $this->config->get('database.' . Str::lower($name) . '.' . Str::lower($target));
        if (! is_string($result)) {
            throw new InvalidArgumentException("Unsupported driver [{$target}]");
        }
        return new $result(...$arguments);
    }
}
