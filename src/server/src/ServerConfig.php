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

namespace Hyperf\Server;

use Hyperf\Contract\Arrayable;
use Hyperf\Server\Exception\InvalidArgumentException;

/**
 * @method ServerConfig setType(string $type)
 * @method ServerConfig setMode(int $mode)
 * @method ServerConfig setServers(array $servers)
 * @method ServerConfig setProcesses(array $processes)
 * @method ServerConfig setSettings(array $settings)
 * @method ServerConfig setCallbacks(array $callbacks)
 * @method string getType()
 * @method int getMode()
 * @method Port[] getServers()
 * @method array getProcesses()
 * @method array getSettings()
 * @method array getCallbacks()
 */
class ServerConfig implements Arrayable
{
    public function __construct(protected array $config = [])
    {
        if (empty($config['servers'] ?? [])) {
            throw new InvalidArgumentException('Config server.servers not exist.');
        }

        $servers = [];
        foreach ($config['servers'] as $name => $item) {
            if (! isset($item['name']) && ! is_numeric($name)) {
                $item['name'] = $name;
            }
            $servers[] = Port::build($item);
        }

        $this->setType($config['type'] ?? Server::class)
            ->setMode($config['mode'] ?? 0)
            ->setServers($servers)
            ->setProcesses($config['processes'] ?? [])
            ->setSettings($config['settings'] ?? [])
            ->setCallbacks($config['callbacks'] ?? []);
    }

    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    public function __get($name)
    {
        if (! $this->isAvailableProperty($name)) {
            throw new \InvalidArgumentException(sprintf('Invalid property %s', $name));
        }
        return $this->config[$name] ?? null;
    }

    public function __call($name, $arguments)
    {
        $prefix = strtolower(substr($name, 0, 3));
        if (in_array($prefix, ['set', 'get'])) {
            $propertyName = strtolower(substr($name, 3));
            if (! $this->isAvailableProperty($propertyName)) {
                throw new \InvalidArgumentException(sprintf('Invalid property %s', $propertyName));
            }
            return $prefix === 'set' ? $this->set($propertyName, ...$arguments) : $this->__get($propertyName);
        }

        throw new \InvalidArgumentException(sprintf('Invalid method %s', $name));
    }

    public function addServer(Port $port): static
    {
        $this->config['servers'][] = $port;
        return $this;
    }

    public function toArray(): array
    {
        return $this->config;
    }

    protected function set($name, $value): self
    {
        if (! $this->isAvailableProperty($name)) {
            throw new \InvalidArgumentException(sprintf('Invalid property %s', $name));
        }
        $this->config[$name] = $value;
        return $this;
    }

    private function isAvailableProperty(string $name)
    {
        return in_array($name, [
            'type', 'mode', 'servers', 'processes', 'settings', 'callbacks',
        ]);
    }
}
