<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\ServerRegister;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Server\Server;
use InvalidArgumentException;

class ServerHelper
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function getServers(): array
    {
        $result = [];
        $servers = $this->config->get('server.servers', []);
        foreach ($servers as $server) {
            if (! isset($server['name'], $server['host'], $server['port'])) {
                continue;
            }
            if (! $server['name']) {
                throw new InvalidArgumentException('Invalid server name');
            }
            $host = $server['host'];
            if (in_array($host, ['0.0.0.0', 'localhost'])) {
                $host = $this->getInternalIp();
            }
            if (! filter_var($host, FILTER_VALIDATE_IP)) {
                throw new InvalidArgumentException(sprintf('Invalid host %s', $host));
            }
            $port = $server['port'];
            if (! is_numeric($port) || ($port < 0 || $port > 65535)) {
                throw new InvalidArgumentException(sprintf('Invalid port %s', $port));
            }
            $port = (int) $port;
            $result[$server['name']] = [
                $host,
                $port,
                $server['type'] ?? Server::SERVER_HTTP,
            ];
        }
        return $result;
    }

    public function getInternalIp(): string
    {
        $host = $this->config->get('server_register.host');
        if ($host !== null) {
            if (is_string($host)) {
                return $host;
            }

            if (is_callable($host)) {
                return (string) $host();
            }
        }

        $ips = swoole_get_local_ip();
        if (is_array($ips)) {
            return current($ips);
        }
        $ip = gethostbyname(gethostname());
        if (is_string($ip)) {
            return $ip;
        }
        throw new \RuntimeException('Can not get the internal IP.');
    }
}
