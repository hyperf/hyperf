<?php

declare(strict_types=1);

namespace Hyperf\HttpConsulRegister;

use Hyperf\Consul\Exception\ServerException;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Framework\Event\MainWorkerStart;
use Hyperf\ServiceGovernance\Register\ConsulAgent;
use Psr\Container\ContainerInterface;
use Hyperf\Event\Contract\ListenerInterface;

/**
 * @Listener()
 */
class EnableHttpConsulRegisterListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ConsulAgent
     */
    private $consulAgent;

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
        $this->consulAgent = $container->get(ConsulAgent::class);

        $this->config = $container->get(ConfigInterface::class);

        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    public function listen(): array
    {
        return [
            MainWorkerStart::class,
        ];
    }

    public function isEnable(): bool
    {
        return $this->config->get('consul.http_consul_register', false);
    }

    public function process(object $event)
    {
        if (!$this->isEnable()){
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
        $httpServerConfigs = array_filter($serverConfigs, function($config) {
            return $config['name'] = 'http';
        });
        if (count($httpServerConfigs)){
            return $httpServerConfigs[0];
        }

        throw new \RuntimeException('Cannot register service, http server not exists.');
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

        $response = $this->consulAgent->registerService($requestBody);
        if ($response->getStatusCode() === 200) {
            $this->registeredServices[$serviceName][$protocol][$address][$port] = true;
            $this->logger->info(sprintf('Service %s:%s register to the consul successfully.', $serviceName, $nextId));
        } else {
            $this->logger->warning(sprintf('Service %s register to the consul failed.', $serviceName));
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

    private function getLastServiceId(string $name)
    {
        $maxId = -1;
        $lastService = $name;
        $services = $this->consulAgent->services()->json();
        foreach ($services ?? [] as $id => $service) {
            if (isset($service['Service']) && $service['Service'] === $name) {
                $exploded = explode('-', (string) $id);
                $length = count($exploded);
                if ($length > 1 && is_numeric($exploded[$length - 1]) && $maxId < $exploded[$length - 1]) {
                    $maxId = $exploded[$length - 1];
                    $lastService = $service;
                }
            }
        }
        return $lastService['ID'] ?? $name;
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
        $response = $this->consulAgent->services();
        if ($response->getStatusCode() !== 200) {
            $this->logger->warning(sprintf('Service %s register to the consul failed.', $name));
            return false;
        }
        $services = $response->json();
        $glue = ',';
        $tag = implode($glue, [$name, $address, $port, $protocol]);
        foreach ($services as $serviceId => $service) {
            if (! isset($service['Service'], $service['Address'], $service['Port'], $service['Meta']['Protocol'])) {
                continue;
            }
            $currentTag = implode($glue, [
                $service['Service'],
                $service['Address'],
                $service['Port'],
                $service['Meta']['Protocol'],
            ]);
            if ($currentTag === $tag) {
                $this->registeredServices[$name][$protocol][$address][$port] = true;
                return true;
            }
        }
        return false;
    }
}
