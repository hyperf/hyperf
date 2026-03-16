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

namespace Hyperf\ServiceGovernanceConsul;

use Hyperf\Consul\AgentInterface;
use Hyperf\Consul\Health;
use Hyperf\Consul\HealthInterface;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\ServiceGovernance\DriverInterface;
use Hyperf\ServiceGovernance\Exception\ComponentRequiredException;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use function Hyperf\Support\make;

class ConsulDriver implements DriverInterface
{
    protected LoggerInterface $logger;

    protected ConfigInterface $config;

    protected array $registeredServices = [];

    protected ?HealthInterface $health = null;

    public function __construct(protected ContainerInterface $container)
    {
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->config = $container->get(ConfigInterface::class);
    }

    public function isLongPolling(): bool
    {
        return false;
    }

    public function getNodes(string $uri, string $name, array $metadata): array
    {
        $health = $this->createConsulHealth($uri);
        $services = $health->service($name)->json();
        $nodes = [];
        foreach ($services as $node) {
            $passing = true;
            $service = $node['Service'] ?? [];
            $checks = $node['Checks'] ?? [];

            if (isset($service['Meta']['Protocol']) && $metadata['protocol'] !== $service['Meta']['Protocol']) {
                // The node is invalid, if the protocol is not equal with the client's protocol.
                continue;
            }

            foreach ($checks as $check) {
                $status = $check['Status'] ?? false;
                if ($status !== 'passing') {
                    $passing = false;
                }
            }

            if ($passing) {
                $address = $service['Address'] ?? '';
                $port = (int) ($service['Port'] ?? 0);
                // @TODO Get and set the weight property.
                $address && $port && $nodes[] = ['host' => $address, 'port' => $port];
            }
        }
        return $nodes;
    }

    public function register(string $name, string $host, int $port, array $metadata): void
    {
        $nextId = empty($metadata['id']) ? $this->generateId($this->getLastServiceId($name)) : $metadata['id'];
        $protocol = $metadata['protocol'];
        $deregisterCriticalServiceAfter = $this->config->get('services.drivers.consul.check.deregister_critical_service_after') ?? '90m';
        $interval = $this->config->get('services.drivers.consul.check.interval') ?? '1s';
        $requestBody = [
            'Name' => $name,
            'ID' => $nextId,
            'Address' => $host,
            'Port' => $port,
            'Meta' => [
                'Protocol' => $protocol,
            ],
        ];
        if ($protocol === 'jsonrpc-http') {
            $requestBody['Check'] = [
                'DeregisterCriticalServiceAfter' => $deregisterCriticalServiceAfter,
                'HTTP' => "http://{$host}:{$port}/",
                'Interval' => $interval,
            ];
        }
        if (in_array($protocol, ['jsonrpc', 'jsonrpc-tcp-length-check', 'multiplex.default'], true)) {
            $requestBody['Check'] = [
                'DeregisterCriticalServiceAfter' => $deregisterCriticalServiceAfter,
                'TCP' => "{$host}:{$port}",
                'Interval' => $interval,
            ];
        }
        if ($protocol === 'grpc') {
            $requestBody['Check'] = [
                'DeregisterCriticalServiceAfter' => $deregisterCriticalServiceAfter,
                'GRPC' => "{$host}:{$port}",
                'GRPCUseTLS' => false,
                'Interval' => $interval,
            ];
        }
        $response = $this->client()->registerService($requestBody);
        if ($response->getStatusCode() === 200) {
            $this->registeredServices[$name][$protocol][$host][$port] = true;
            $this->logger->info(sprintf('Service %s:%s register to the consul successfully.', $name, $nextId));
        } else {
            $this->logger->warning(sprintf('Service %s register to the consul failed.', $name));
        }
    }

    public function isRegistered(string $name, string $host, int $port, array $metadata): bool
    {
        $protocol = $metadata['protocol'];
        if (isset($this->registeredServices[$name][$protocol][$host][$port])) {
            return true;
        }
        $client = $this->client();
        $response = $client->services();
        if ($response->getStatusCode() !== 200) {
            $this->logger->warning(sprintf('Service %s register to the consul failed.', $name));
            return false;
        }
        $services = $response->json();
        $glue = ',';
        $tag = implode($glue, [$name, $host, $port, $protocol]);
        foreach ($services as $service) {
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
                $this->registeredServices[$name][$protocol][$host][$port] = true;
                return true;
            }
        }
        return false;
    }

    protected function client(): AgentInterface
    {
        return $this->container->get(ConsulAgent::class);
    }

    protected function getLastServiceId(string $name)
    {
        $maxId = -1;
        $lastService = $name;
        $services = $this->client()->services()->json();
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

    protected function createConsulHealth(string $baseUri): HealthInterface
    {
        if ($this->health instanceof HealthInterface) {
            return $this->health;
        }

        if (! class_exists(Health::class)) {
            throw new ComponentRequiredException('Component of \'hyperf/consul\' is required if you want the client fetch the nodes info from consul.');
        }

        $token = $this->config->get('services.drivers.consul.token', '');
        $options = [
            'base_uri' => $baseUri,
        ];

        if (! empty($token)) {
            $options['headers'] = [
                'X-Consul-Token' => $token,
            ];
        }

        return $this->health = make(Health::class, [
            'clientFactory' => function () use ($options) {
                return $this->container->get(ClientFactory::class)->create($options);
            },
        ]);
    }
}
