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

namespace Hyperf\Server;

use Hyperf\Server\Exception\InvalidArgumentException;
use Hyperf\Utils\Contracts\Arrayable;

class ServerConfig implements Arrayable
{
    /**
     * @var array
     */
    protected $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;

        if (empty($config['servers'] ?? [])) {
            throw new InvalidArgumentException('Config server.servers not exist.');
        }

        $servers = [];
        foreach ($config['servers'] as $item) {
            $servers[] = Port::build($item);
        }

        $this->setMode($config['mode'] ?? SWOOLE_BASE)
            ->setServers($servers)
            ->setProcesses($config['processes'] ?? [])
            ->setSettings($config['settings'] ?? [])
            ->setCallbacks($config['callbacks'] ?? []);
    }

    /**
     * @return int
     */
    public function getMode(): int
    {
        return $this->config['mode'] ?? SWOOLE_BASE;
    }

    /**
     * @param int $mode
     * @return ServerConfig
     */
    public function setMode(int $mode): ServerConfig
    {
        $this->config['mode'] = $mode;
        return $this;
    }

    /**
     * @return Port[]
     */
    public function getServers(): array
    {
        return $this->config['servers'] ?? [];
    }

    /**
     * @param Port[] $servers
     * @return ServerConfig
     */
    public function setServers(array $servers): ServerConfig
    {
        $this->config['servers'] = $servers;
        return $this;
    }

    /**
     * @return ServerConfig
     */
    public function addServer(Port $port): ServerConfig
    {
        $this->config['servers'][] = $port;
        return $this;
    }

    /**
     * @return array
     */
    public function getProcesses(): array
    {
        return $this->config['processes'] ?? [];
    }

    /**
     * @param array $processes
     * @return ServerConfig
     */
    public function setProcesses(array $processes): ServerConfig
    {
        $this->config['processes'] = $processes;
        return $this;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->config['settings'] ?? [];
    }

    /**
     * @param array $settings
     * @return ServerConfig
     */
    public function setSettings(array $settings): ServerConfig
    {
        $this->config['settings'] = $settings;
        return $this;
    }

    /**
     * @return array
     */
    public function getCallbacks(): array
    {
        return $this->config['callbacks'] ?? [];
    }

    /**
     * @param array $callbacks
     * @return ServerConfig
     */
    public function setCallbacks(array $callbacks): ServerConfig
    {
        $this->config['callbacks'] = $callbacks;
        return $this;
    }

    public function toArray(): array
    {
        return $this->config;
    }
}
