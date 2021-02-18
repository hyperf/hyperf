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
namespace Hyperf\ServiceGovernance\Listener;

use Hyperf\Consul\Exception\ServerException;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\MainWorkerStart;
use Hyperf\Nacos\Api\NacosInstance;
use Hyperf\Nacos\Api\NacosService;
use Hyperf\Nacos\Exception\RuntimeException;
use Hyperf\Nacos\Model\InstanceModel;
use Hyperf\Nacos\Model\ServiceModel;
use Hyperf\Server\Event\MainCoroutineServerStart;
use Hyperf\ServiceGovernance\Register\ConsulAgent;
use Hyperf\ServiceGovernance\ServiceManager;
use Psr\Container\ContainerInterface;

class RegisterServiceListener implements ListenerInterface
{
    /**
     * @var ConsulAgent
     */
    protected $consulAgent;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var array
     */
    protected $defaultLoggerContext
        = [
            'component' => 'service-governance',
        ];

    /**
     * @var array
     */
    protected $registeredServices;

    /**
     * @var string
     */
    protected $group_name;
    /**
     * @var string
     */
    protected $namespace_id;

    /**
     * @var NacosService|mixed
     */
    protected $nacosService;

    /**
     * @var NacosInstance|mixed
     */
    protected $nacosInstance;

    public function __construct(ContainerInterface $container)
    {
        $this->consulAgent = $container->get(ConsulAgent::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->serviceManager = $container->get(ServiceManager::class);
        $this->config = $container->get(ConfigInterface::class);
        $this->nacosService = $container->get(NacosService::class);
        $this->nacosInstance = $container->get(NacosInstance::class);
        $this->namespace_id = config('nacos.service.namespace_id', 'public');
        $this->group_name = config('nacos.service.group_name', 'api');
    }

    public function listen(): array
    {
        return [
            MainWorkerStart::class,
            MainCoroutineServerStart::class,
        ];
    }

    public function process(object $event)
    {
        $this->registeredServices = [];
        $continue = true;
        while ($continue) {
            try {
                $services = $this->serviceManager->all();
                $servers = $this->getServers();
                foreach ($services as $serviceName => $serviceProtocols) {
                    foreach ($serviceProtocols as $paths) {
                        foreach ($paths as $path => $service) {
                            if (! isset($service['publishTo'], $service['server'])) {
                                continue;
                            }
                            [$address, $port] = $servers[$service['server']];
                            switch ($service['publishTo']) {
                                case 'consul':
                                    $this->publishToConsul($address, (int) $port, $service, $serviceName, $path);
                                    break;
                            }
                            switch ($service['publishTo']) {
                                case 'nacos':
                                    $this->publishToNacos($address, (int) $port, $service, $serviceName, $path);
                                    break;
                            }
                        }
                    }
                }
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

    protected function publishToConsul(string $address, int $port, array $service, string $serviceName, string $path)
    {
        $this->logger->debug(sprintf('Service %s[%s] is registering to the consul.', $serviceName, $path), $this->defaultLoggerContext);
        if ($this->isRegistered($serviceName, $address, $port, $service['protocol'])) {
            $this->logger->info(sprintf('Service %s[%s] has been already registered to the consul.', $serviceName, $path), $this->defaultLoggerContext);
            return;
        }
        if (isset($service['id']) && $service['id']) {
            $nextId = $service['id'];
        } else {
            $nextId = $this->generateId($this->getLastServiceId($serviceName));
        }
        $requestBody = [
            'Name' => $serviceName,
            'ID' => $nextId,
            'Address' => $address,
            'Port' => $port,
            'Meta' => [
                'Protocol' => $service['protocol'],
            ],
        ];
        if ($service['protocol'] === 'jsonrpc-http') {
            $requestBody['Check'] = [
                'DeregisterCriticalServiceAfter' => '90m',
                'HTTP' => "http://{$address}:{$port}/",
                'Interval' => '1s',
            ];
        }
        if (in_array($service['protocol'], ['jsonrpc', 'jsonrpc-tcp-length-check'], true)) {
            $requestBody['Check'] = [
                'DeregisterCriticalServiceAfter' => '90m',
                'TCP' => "{$address}:{$port}",
                'Interval' => '1s',
            ];
        }
        $response = $this->consulAgent->registerService($requestBody);
        if ($response->getStatusCode() === 200) {
            $this->registeredServices[$serviceName][$service['protocol']][$address][$port] = true;
            $this->logger->info(sprintf('Service %s[%s]:%s register to the consul successfully.', $serviceName, $path, $nextId), $this->defaultLoggerContext);
        } else {
            $this->logger->warning(sprintf('Service %s register to the consul failed.', $serviceName), $this->defaultLoggerContext);
        }
    }

    protected function publishToNacos(string $address, int $port, array $service, string $serviceName, string $path)
    {
        if (isset($service['id']) && $service['id']) {
            $nextId = $service['id'];
        } else {
            $nextId = $this->generateId($this->getLastServiceId($serviceName));
        }
        $serviceModel = new ServiceModel([
            'service_name' => $serviceName,
            'group_name' => $this->group_name,
            'namespace_id' => $this->namespace_id,
            'protect_threshold' => 0.5,
            'metadata' => [
                'Name' => $serviceName,
                'ID' => $nextId,
                'Address' => $address,
                'Port' => $port,
                'Protocol' => $service['protocol'],
                'Agreement' => 'JSON-RPC',
                'AgreementVersion' => '2.0',
                'Method' => $path
            ]
        ]);
        $exist = $this->nacosService->detail($serviceModel);
        /**
         * 已经存在
         */
        if ($exist) {
            $this->logger->info(sprintf('Service %s[%s] has been already registered to the nacos.', $serviceName, $path), $this->defaultLoggerContext);
        }
        /**
         * 创建
         */
        if ($exist && $this->nacosService->create($serviceModel)) {
            $this->logger->info(sprintf('Service %s[%s]:%s register to the nacos successfully.', $serviceName, $path, $nextId), $this->defaultLoggerContext);
        }
        if ((!$exist) && $this->nacosService->update($serviceModel)) {
            $this->logger->info(sprintf('Service %s[%s]:%s update to the nacos successfully.', $serviceName, $path, $nextId), $this->defaultLoggerContext);
        }
        $instance = new InstanceModel([
            'service_name' => $serviceName,
            'group_name' => $this->group_name,
            'cluster' => 'DEFAULT',
            'ephemeral' => true,
            'beat_enable' => true,
            'beat_interval' => 5,
            'namespace_id' => $this->namespace_id,
            'ip' => $address,
            'port' => $port,
            'metadata' => [
                'Name' => $serviceName,
                'ID' => $nextId,
                'Address' => $address,
                'Port' => $port,
                'Protocol' => $service['protocol'],
                'Agreement' => 'JSON-RPC',
                'AgreementVersion' => '2.0',
                'Method' => $path
            ]
        ]);
        if (!$this->nacosInstance->register($instance)) {
            throw new RuntimeException(sprintf('nacos register instance fail: %s', $instance));
        }
    }

    protected function generateId(string $name)
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

    protected function getLastServiceId(string $name)
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

    protected function isRegistered(string $name, string $address, int $port, string $protocol): bool
    {
        if (isset($this->registeredServices[$name][$protocol][$address][$port])) {
            return true;
        }
        $response = $this->consulAgent->services();
        if ($response->getStatusCode() !== 200) {
            $this->logger->warning(sprintf('Service %s register to the consul failed.', $name), $this->defaultLoggerContext);
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

    protected function getServers(): array
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

    protected function getInternalIp(): string
    {
        $ips = swoole_get_local_ip();
        if (is_array($ips) && ! empty($ips)) {
            return current($ips);
        }
        /** @var mixed|string $ip */
        $ip = gethostbyname(gethostname());
        if (is_string($ip)) {
            return $ip;
        }
        throw new \RuntimeException('Can not get the internal IP.');
    }
}
