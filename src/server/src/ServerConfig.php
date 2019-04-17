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
     * @var int
     */
    protected $mode;

    /**
     * @var array
     */
    protected $servers;

    /**
     * @var array
     */
    protected $processes;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var array
     */
    protected $callbacks;

    /**
     * @var array
     */
    protected $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;

        $servers = $config['servers'] ?? [];
        if (empty($servers)) {
            throw new InvalidArgumentException('Config server.servers not exist.');
        }

        $this->setMode($config['mode'] ?? SWOOLE_BASE)
            ->setServers($config['servers'] ?? [])
            ->setProcesses($config['processes'] ?? [])
            ->setSettings($config['settings'] ?? [])
            ->setCallbacks($config['callbacks'] ?? []);
    }

    /**
     * @return int
     */
    public function getMode(): int
    {
        return $this->mode;
    }

    /**
     * @param int $mode
     * @return ServerConfig
     */
    public function setMode(int $mode): ServerConfig
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * @return array
     */
    public function getServers(): array
    {
        return $this->servers;
    }

    /**
     * @param array $servers
     * @return ServerConfig
     */
    public function setServers(array $servers): ServerConfig
    {
        $this->servers = $servers;
        return $this;
    }

    /**
     * @return array
     */
    public function getProcesses(): array
    {
        return $this->processes;
    }

    /**
     * @param array $processes
     * @return ServerConfig
     */
    public function setProcesses(array $processes): ServerConfig
    {
        $this->processes = $processes;
        return $this;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * @param array $settings
     * @return ServerConfig
     */
    public function setSettings(array $settings): ServerConfig
    {
        $this->settings = $settings;
        return $this;
    }

    /**
     * @return array
     */
    public function getCallbacks(): array
    {
        return $this->callbacks;
    }

    /**
     * @param array $callbacks
     * @return ServerConfig
     */
    public function setCallbacks(array $callbacks): ServerConfig
    {
        $this->callbacks = $callbacks;
        return $this;
    }

    public function toArray(): array
    {
        return [
        ];
    }
}
