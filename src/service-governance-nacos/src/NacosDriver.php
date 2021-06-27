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

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Nacos\Application;
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
     * @var Application
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

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->client = $container->get(Application::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    public function getNodes(string $uri, string $name, array $metadata): array
    {
        $response = $this->client->instance->list($name);
        if ($response->getStatusCode() !== 200) {
            throw new RequestException((string) $response->getBody(), $response->getStatusCode());
        }

        $data = Json::decode((string) $response->getBody());
        $hosts = $data['hosts'] ?? [];
        $nodes = [];
        foreach ($hosts as $node) {
            if (isset($node['ip'], $node['port']) && $node['valid'] ?? false) {
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
                'metadata' => $metadata,
            ]);

            if ($response->getStatusCode() !== 200 || (string) $response->getBody() !== 'ok') {
                throw new RequestException(sprintf('Failed to create nacos service %s!', $name));
            }

            $this->serviceRegistered[$name] = true;
        }

        $response = $this->client->instance->register($host, $port, $name, [
            'metadata' => $metadata,
        ]);

        if ($response->getStatusCode() !== 200 || (string) $response->getBody() !== 'ok') {
            throw new RequestException(sprintf('Failed to create nacos instance %s:%d! for %s', $host, $port, $name));
        }
    }

    public function isRegistered(string $name, string $host, int $port, array $metadata): bool
    {
        if (! array_key_exists($name, $this->serviceRegistered)) {
            $response = $this->client->service->detail($name);
            if ($response->getStatusCode() === 404) {
                return false;
            }

            if ($response->getStatusCode() !== 200) {
                throw new RequestException(sprintf('Failed to get nacos service %s!', $name));
            }

            $this->serviceRegistered[$name] = true;
        }

        $response = $this->client->instance->detail($host, $port, $name);
        if ($response->getStatusCode() === 404) {
            return false;
        }

        if ($response->getStatusCode() !== 200) {
            throw new RequestException(sprintf('Failed to get nacos instance %s:%d for %s!', $host, $port, $name));
        }

        return true;
    }
}
