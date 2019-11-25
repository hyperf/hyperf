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

namespace Hyperf\ServerRegister\Listener;

use Hyperf\Consul\Exception\ServerException;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\MainWorkerStart;
use Hyperf\ServerRegister\Agent\AgentInterface;
use Hyperf\ServerRegister\Agent\ConsulAgent;
use Psr\Container\ContainerInterface;

class RegisterServerListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var AgentInterface
     */
    private $agent;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $registeredServices;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(ConfigInterface::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->agent = $container->get($this->config->get('server_register.agent', ConsulAgent::class));
    }

    public function listen(): array
    {
        return [
            MainWorkerStart::class,
        ];
    }

    public function isEnable(): bool
    {
        return $this->config->get('server_register.enable', false);
    }

    public function process(object $event)
    {
        if (! $this->isEnable()) {
            return;
        }

        $this->registeredServices = [];
        $continue = true;

        $httpServerConfig = $this->getHttpServerConfig();
        $address = $httpServerConfig['host'];
        if (in_array($address, ['0.0.0.0', 'localhost'])) {
            $address = $this->getInternalIp();
        }
        $port = $httpServerConfig['port'];

        $protocol = 'http';
        $serviceName = $this->config->get('consul.service_name');

        while ($continue) {
            try {
                $this->publishToConsul($address, (int) $port, $protocol, $serviceName);
                $continue = false;
            } catch (ServerException $throwable) {
                if (strpos($throwable->getMessage(), 'Connection failed') !== false) {
                    $this->logger->warning('Cannot register service, connection of service center failed, re-register after 10 seconds.');
                    sleep(10);
                } else {
                    throw $throwable;
                }
            }
        }
    }

    public function getHttpServerConfig()
    {
        $serverConfigs = $this->config->get('server.servers');
        $httpServerConfigs = array_filter($serverConfigs, function ($config) {
            return $config['name'] = 'http';
        });
        if (count($httpServerConfigs)) {
            return $httpServerConfigs[0];
        }

        throw new \RuntimeException('Cannot register service, http server not exists.');
    }

    private function getServers(): array
    {
        $result = [];
        $servers = $this->config->get('server.servers', []);
        foreach ($servers as $server) {
            if (! isset($server['name'], $server['host'], $server['port'])) {
                continue;
            }
            if (! $server['name']) {
                throw new \InvalidArgumentException('Invalid server name');
            }
            $host = $server['host'];
            if (in_array($host, ['0.0.0.0', 'localhost'])) {
                $host = $this->getInternalIp();
            }
            if (! filter_var($host, FILTER_VALIDATE_IP)) {
                throw new \InvalidArgumentException(sprintf('Invalid host %s', $host));
            }
            $port = $server['port'];
            if (! is_numeric($port) || ($port < 0 || $port > 65535)) {
                throw new \InvalidArgumentException(sprintf('Invalid port %s', $port));
            }
            $port = (int) $port;
            $result[$server['name']] = [$host, $port];
        }
        return $result;
    }

    private function publishToConsul(string $address, int $port, string $protocol, string $serviceName)
    {
        $this->logger->debug(sprintf('Service %s is registering to the consul.', $serviceName));
        if ($this->isRegistered($serviceName, $address, $port, $protocol)) {
            $this->logger->info(sprintf('Service %s has been already registered to the consul.', $serviceName));
            return;
        }

        $nextId = $this->generateId($this->getLastServiceId($serviceName));

        $requestBody = [
            'Name' => $serviceName,
            'ID' => $nextId,
            'Address' => $address,
            'Port' => $port,
            'Meta' => [
                'Protocol' => $protocol,
            ],
        ];

        $requestBody['Check'] = [
            'DeregisterCriticalServiceAfter' => '90m',
            'HTTP' => "http://{$address}:{$port}/",
            'Interval' => '1s',
        ];

        if ($this->agent->registerService($requestBody)) {
            $this->registeredServices[$serviceName][$protocol][$address][$port] = true;
            $this->logger->info(sprintf('Server %s:%s regist successfully.', $serviceName, $nextId));
        } else {
            $this->logger->warning(sprintf('Server %s regist failed.', $serviceName));
        }
    }

    private function generateId(string $name)
    {
        $exploded = explode('-', $name);
        $length = count($exploded);
        $end = -1;
        if ($length > 1 && is_numeric($exploded[$length - 1])) {
            $end = $exploded[$length - 1];
            unset($exploded[$length - 1]);
        }
        $end = intval($end);
        ++$end;
        $exploded[] = $end;
        return implode('-', $exploded);
    }

    private function getLastServiceId(string $name): string
    {
        $maxId = -1;
        $lastServer = null;
        $servers = $this->agent->services();
        foreach ($servers ?? [] as $server) {
            if ($server->getService() === $name) {
                $exploded = explode('-', (string) $server->getId());
                $length = count($exploded);
                if ($length > 1 && is_numeric($exploded[$length - 1]) && $maxId < $exploded[$length - 1]) {
                    $maxId = $exploded[$length - 1];
                    $lastServer = $server;
                }
            }
        }

        return $lastServer ? $lastServer->getService() : $name;
    }

    private function getInternalIp(): string
    {
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

    private function isRegistered(string $name, string $address, int $port, string $protocol): bool
    {
        if (isset($this->registeredServices[$name][$protocol][$address][$port])) {
            return true;
        }
        $servers = $this->agent->services();
        if ($servers === null) {
            $this->logger->warning(sprintf('Server %s regist failed.', $name));
        }

        $glue = ',';
        $tag = implode($glue, [$name, $address, $port, $protocol]);
        foreach ($servers as $server) {
            $currentTag = implode($glue, [
                $server->getService(),
                $server->getAddress(),
                $server->getPort(),
                $server->getProtocol(),
            ]);

            if ($currentTag === $tag) {
                $this->registeredServices[$name][$protocol][$address][$port] = true;
                return true;
            }
        }

        return false;
    }
}
