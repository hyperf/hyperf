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
use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Hyperf\Utils\Coroutine;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

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
                'metadata' => $this->formatMetadata($metadata),
            ]);

            if ($response->getStatusCode() !== 200 || (string) $response->getBody() !== 'ok') {
                throw new RequestException(sprintf('Failed to create nacos service %s!', $name));
            }

            $this->serviceRegistered[$name] = true;
        }

        $response = $this->client->instance->register($host, $port, $name, [
            'groupName' => $this->config->get('services.drivers.nacos.group_name'),
            'namespaceId' => $this->config->get('services.drivers.nacos.namespace_id'),
            'metadata' => $this->formatMetadata($metadata),
        ]);

        if ($response->getStatusCode() !== 200 || (string) $response->getBody() !== 'ok') {
            throw new RequestException(sprintf('Failed to create nacos instance %s:%d! for %s', $host, $port, $name));
        }

        $this->registerHeartbeat($name, $host, $port);
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

            if ($response->getStatusCode() === 500 && strpos((string) $response->getBody(), 'is not found') > 0) {
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

        if ($this->isNoIpsFound($response)) {
            return false;
        }

        if ($response->getStatusCode() !== 200) {
            throw new RequestException(sprintf('Failed to get nacos instance %s:%d for %s!', $host, $port, $name));
        }

        $this->registerHeartbeat($name, $host, $port);

        return true;
    }

    protected function isNoIpsFound(ResponseInterface $response): bool
    {
        if ($response->getStatusCode() === 404) {
            return true;
        }

        if ($response->getStatusCode() === 500) {
            $messages = [
                'no ips found',
                'no matched ip',
            ];
            $body = (string) $response->getBody();
            foreach ($messages as $message) {
                if (strpos($body, $message) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function formatMetadata(array $metadata): ?string
    {
        if (empty($metadata)) {
            return null;
        }

        return Json::encode($metadata);
    }

    protected function registerHeartbeat(string $name, string $host, int $port): void
    {
        Coroutine::create(function () use ($name, $host, $port) {
            retry(INF, function () use ($name, $host, $port) {
                while (true) {
                    $heartbeat = $this->config->get('services.drivers.nacos.heartbeat', 5);
                    if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield($heartbeat)) {
                        break;
                    }
                    $groupName = $this->config->get('services.drivers.nacos.group_name');
                    $response = $this->client->instance->beat(
                        $name,
                        [
                            'ip' => $host,
                            'port' => $port,
                            'serviceName' => $groupName . '@@' . $name,
                        ],
                        $groupName,
                        $this->config->get('services.drivers.nacos.namespace_id'),
                    );

                    if ($response->getStatusCode() === 200) {
                        $this->logger->debug(sprintf('Instance %s:%d heartbeat successfully!', $host, $port));
                    } else {
                        $this->logger->error(sprintf('Instance %s:%d heartbeat failed!', $host, $port));
                    }
                }
            });
        });
    }
}
