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

use Closure;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\MainWorkerStart;
use Hyperf\ServerRegister\Agent\AgentInterface;
use Hyperf\ServerRegister\Agent\ConsulAgent;
use Hyperf\ServerRegister\RegistedServer;
use Hyperf\ServerRegister\ServerHelper;
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

        $serverConfigs = $this->config->get('server_register.servers', []);
        $servers = $this->container->get(ServerHelper::class)->getServers();
        $closure = $this->getRegistClosure();
        foreach ($serverConfigs as $serverConfig) {
            $server = $serverConfig['server'] ?? null;
            if ($server && isset($servers[$server])) {
                $name = $serverConfig['name'] ?? $server;
                [$host, $port, $type] = $servers[$server];
                $closure($host, $port, $name, $serverConfig['meta'] + ['protocol' => $type]);
            }
        }
    }

    private function getRegistClosure(): Closure
    {
        return function ($address, $port, $serviceName, $meta) {
            $continue = true;
            while ($continue) {
                try {
                    $this->publishToConsul($address, (int) $port, $serviceName, $meta);
                    $continue = false;
                } catch (\Throwable $throwable) {
                    if (strpos($throwable->getMessage(), 'Connection failed') !== false) {
                        $this->logger->warning('Cannot register server, connection of service center failed, re-register after 10 seconds.');
                        sleep(10);
                    } else {
                        throw $throwable;
                    }
                }
            }
        };
    }

    private function publishToConsul(string $address, int $port, string $serviceName, array $meta)
    {
        $this->logger->debug(sprintf('Server %s is registering.', $serviceName));
        if ($this->isRegistered($serviceName, $address, $port)) {
            $this->logger->info(sprintf('Server %s has been already registered.', $serviceName));
            return;
        }

        $nextId = $this->generateId($this->getLastServiceId($serviceName));

        $server = new RegistedServer($nextId, $serviceName, $address, $port, $meta);

        if ($this->agent->registerService($server)) {
            $this->registeredServices[$serviceName][$address][$port] = true;
            $this->logger->info(sprintf('Server %s: register successfully.', $nextId));
        } else {
            $this->logger->warning(sprintf('Server %s register failed.', $nextId));
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

    private function isRegistered(string $name, string $address, int $port): bool
    {
        if (isset($this->registeredServices[$name][$address][$port])) {
            return true;
        }
        $servers = $this->agent->services();
        if ($servers === null) {
            $this->logger->warning(sprintf('Server %s regist failed.', $name));
        }

        $glue = ',';
        $tag = implode($glue, [$name, $address, $port]);
        foreach ($servers as $server) {
            $currentTag = implode($glue, [
                $server->getService(),
                $server->getAddress(),
                $server->getPort(),
            ]);

            if ($currentTag === $tag) {
                $this->registeredServices[$name][$address][$port] = true;
                return true;
            }
        }

        return false;
    }
}
