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
namespace Hyperf\ServiceGovernanceNacos;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Nacos\Exception\RequestException;
use Hyperf\ServiceGovernance\DriverInterface;
use Hyperf\Utils\Codec\Json;
use Psr\Container\ContainerInterface;

class NacosDriver implements DriverInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $serviceRegistered = [];

    /**
     * @var ConfigInterface
     */
    protected $config;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->client = $container->get(Client::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->config = $container->get(ConfigInterface::class);
    }

    public function getNodes(string $uri, string $name, array $metadata): array
    {
        $response = $this->client->instance->list($name, [
            'groupName' => $this->config->get('services.drivers.nacos.group_name'),
            'namespaceId' => $this->config->get('services.drivers.nacos.namespace_id'),
        ]);
        if ($response->getStatusCode() !== 200) {
            throw new RequestException((string) $response->getBody(), $response->getStatusCode());
        }

        $data = Json::decode((string) $response->getBody());
        $hosts = $data['hosts'] ?? [];
        $nodes = [];
        foreach ($hosts as $node) {
            if (isset($node['ip'], $node['port']) && ($node['healthy'] ?? false)) {
                $nodes[] = [
                    'host' => $node['ip'],
                    'port' => $node['port'],
                    'weight' => $node['weight'] ?? 1,
                ];
            }
        }
        return $nodes;
    }

    public function register(string $name, string $host, int $port, array $metadata): void
    {
        if (! array_key_exists($name, $this->serviceRegistered)) {
            $response = $this->client->service->create($name, [
                'groupName' => $this->config->get('services.drivers.nacos.group_name'),
                'namespaceId' => $this->config->get('services.drivers.nacos.namespace_id'),
                'metadata' => $metadata,
            ]);

            if ($response->getStatusCode() !== 200 || (string) $response->getBody() !== 'ok') {
                throw new RequestException(sprintf('Failed to create nacos service %s!', $name));
            }

            $this->serviceRegistered[$name] = true;
        }

        $response = $this->client->instance->register($host, $port, $name, [
            'metadata' => $metadata,
            'groupName' => $this->config->get('services.drivers.nacos.group_name'),
            'namespaceId' => $this->config->get('services.drivers.nacos.namespace_id'),
        ]);

        if ($response->getStatusCode() !== 200 || (string) $response->getBody() !== 'ok') {
            throw new RequestException(sprintf('Failed to create nacos instance %s:%d! for %s', $host, $port, $name));
        }
    }

    public function isRegistered(string $name, string $host, int $port, array $metadata): bool
    {
        if (! array_key_exists($name, $this->serviceRegistered)) {
            $response = $this->client->service->detail(
                $name,
                $this->config->get('services.drivers.nacos.group_name'),
                $this->config->get('services.drivers.nacos.namespace_id')
            );
            if ($response->getStatusCode() === 404) {
                return false;
            }

            if ($response->getStatusCode() !== 200) {
                throw new RequestException(sprintf('Failed to get nacos service %s!', $name), $response->getStatusCode());
            }

            $this->serviceRegistered[$name] = true;
        }

        $response = $this->client->instance->detail($host, $port, $name, [
            'groupName' => $this->config->get('services.drivers.nacos.group_name'),
            'namespaceId' => $this->config->get('services.drivers.nacos.namespace_id'),
        ]);
        if ($response->getStatusCode() === 404) {
            return false;
        }

        if ($response->getStatusCode() === 500 && strpos((string) $response->getBody(), 'no ips found') > 0) {
            return false;
        }

        if ($response->getStatusCode() !== 200) {
            throw new RequestException(sprintf('Failed to get nacos instance %s:%d for %s!', $host, $port, $name));
        }

        return true;
    }
}
