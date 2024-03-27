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

use Hyperf\Codec\Json;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Engine\Channel;
use Hyperf\LoadBalancer\Node;
use Hyperf\Nacos\Exception\RequestException;
use Hyperf\Nacos\Module;
use Hyperf\Nacos\Protobuf\ListenHandler\NamingPushRequestHandler;
use Hyperf\Nacos\Protobuf\Message\Instance;
use Hyperf\Nacos\Protobuf\Request\InstanceRequest;
use Hyperf\Nacos\Protobuf\Request\NamingRequest;
use Hyperf\Nacos\Protobuf\Request\ServiceQueryRequest;
use Hyperf\Nacos\Protobuf\Request\SubscribeServiceRequest;
use Hyperf\Nacos\Protobuf\Response\NotifySubscriberRequest;
use Hyperf\Nacos\Protobuf\Response\SubscribeServiceResponse;
use Hyperf\ServiceGovernance\DriverInterface;
use Hyperf\ServiceGovernance\Exception\RegisterInstanceException;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;

use function Hyperf\Support\retry;

class NacosGrpcDriver implements DriverInterface
{
    protected Client $client;

    protected LoggerInterface $logger;

    protected ConfigInterface $config;

    protected array $serviceRegistered = [];

    protected array $serviceCreated = [];

    protected array $registerHeartbeat = [];

    private array $metadata = [];

    private Channel $nodeChannel;

    private array $nodes = [];

    private bool $listening = false;

    public function __construct(protected ContainerInterface $container)
    {
        $this->client = $container->get(Client::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->config = $container->get(ConfigInterface::class);
        $this->nodeChannel = new Channel(1);
    }

    public function isLongPolling(): bool
    {
        return true;
    }

    public function getNodes(string $uri, string $name, array $metadata): array
    {
        if (! $this->listening) {
            $namespaceId = $this->config->get('services.drivers.nacos.namespace_id');
            $groupName = $this->config->get('services.drivers.nacos.group_name');
            $cluster = $this->config->get('services.drivers.nacos.cluster', 'DEFAULT');

            $client = $this->client->grpc->get($namespaceId, Module::NAMING);
            $client->listenNaming($cluster, $groupName, $name, new NamingPushRequestHandler(function (NotifySubscriberRequest $request) {
                $nodes = [];
                foreach ($request->serviceInfo->hosts as $host) {
                    if ($host->enabled && $host->healthy) {
                        $nodes[] = [
                            'host' => $host->ip,
                            'port' => $host->port,
                            'weight' => $this->getWeight($host->weight),
                            'path_prefix' => $host->metadata['path_prefix'] ?? null,
                        ];
                    }
                }

                $this->nodes = $nodes;
                $chan = $this->nodeChannel;
                $this->nodeChannel = new Channel(1);
                $chan->close();
            }));
        }

        /** @var Node[] $nodes */
        $nodes = $metadata['nodes'] ?? [];
        $isChanged = $this->isChanged($nodes);
        if ($this->nodes && $isChanged) {
            return $this->nodes;
        }

        $this->nodeChannel->pop(60);

        return $this->nodes;
    }

    public function register(string $name, string $host, int $port, array $metadata): void
    {
        $namespaceId = $this->config->get('services.drivers.nacos.namespace_id');
        $groupName = $this->config->get('services.drivers.nacos.group_name');
        $cluster = $this->config->get('services.drivers.nacos.cluster', 'DEFAULT');
        $ephemeral = (bool) $this->config->get('services.drivers.nacos.ephemeral');
        $this->setMetadata($name, $metadata);

        if (! $ephemeral) {
            throw new InvalidArgumentException('nacos grpc driver only support ephemeral.');
        }

        $client = $this->client->grpc->get($namespaceId, Module::NAMING);

        $res = $client->request(new InstanceRequest(
            new NamingRequest($name, $groupName, $namespaceId),
            new Instance($host, $port, 0, true, true, $cluster, $ephemeral, $metadata),
            InstanceRequest::TYPE_REGISTER
        ));

        if (! $res->success) {
            throw new RegisterInstanceException('Register instance failed. The response is ' . $res);
        }

        $client->request(new ServiceQueryRequest(
            new NamingRequest($name, $groupName, $namespaceId),
            $cluster,
            false,
            0
        ));

        $this->serviceRegistered[$name] = true;

        $this->registerHeartbeat($name, $host, $port);
    }

    public function isRegistered(string $name, string $host, int $port, array $metadata): bool
    {
        if (array_key_exists($name, $this->serviceRegistered)) {
            return true;
        }

        $namespaceId = $this->config->get('services.drivers.nacos.namespace_id');
        $groupName = $this->config->get('services.drivers.nacos.group_name');
        $cluster = $this->config->get('services.drivers.nacos.cluster', 'DEFAULT');
        $this->setMetadata($name, $metadata);

        $client = $this->client->grpc->get($namespaceId, Module::NAMING);
        /** @var SubscribeServiceResponse $response */
        $response = $client->request(new SubscribeServiceRequest(
            new NamingRequest(
                $name,
                $groupName,
                $namespaceId,
            ),
        ));

        if ($response->errorCode !== 0) {
            $this->logger->error((string) $response);
            throw new RequestException(sprintf('Failed to get nacos service %s!', $name), $response->errorCode);
        }

        $this->serviceCreated[$name] = true;

        $service = $response->service;
        $instances = $service->hosts;
        foreach ($instances as $instance) {
            if ($instance->ip === $host && $instance->port === $port) {
                $this->serviceRegistered[$name] = true;
                $this->registerHeartbeat($name, $host, $port);
                return true;
            }
        }

        return false;
    }

    /**
     * @param Node[] $nodes
     */
    protected function isChanged(array $nodes): bool
    {
        $now = [];
        foreach ($nodes as $node) {
            $now[] = $node->host . ':' . $node->port . ':' . $node->weight . ':' . $node->pathPrefix;
        }

        $assert = [];
        foreach ($this->nodes as $node) {
            $assert[] = $node['host'] . ':' . $node['port'] . ':' . $node['weight'] . ':' . $node['path_prefix'];
        }

        return Json::encode($now) !== Json::encode($assert);
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
                if (str_contains($body, $message)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function setMetadata(string $name, array $metadata)
    {
        $this->metadata[$name] = $metadata;
    }

    protected function getMetadata(string $name): ?string
    {
        if (empty($this->metadata[$name])) {
            return null;
        }
        unset($this->metadata[$name]['methodName']);
        return Json::encode($this->metadata[$name]);
    }

    protected function registerHeartbeat(string $name, string $host, int $port): void
    {
        $key = $name . $host . $port;
        if (isset($this->registerHeartbeat[$key])) {
            return;
        }
        $this->registerHeartbeat[$key] = true;

        Coroutine::create(function () use ($name, $host, $port) {
            retry(INF, function () use ($name, $host, $port) {
                $lightBeatEnabled = false;

                $namespaceId = $this->config->get('services.drivers.nacos.namespace_id');
                $groupName = $this->config->get('services.drivers.nacos.group_name');
                $cluster = $this->config->get('services.drivers.nacos.cluster', 'DEFAULT');
                $ephemeral = (bool) $this->config->get('services.drivers.nacos.ephemeral');

                while (true) {
                    try {
                        $heartbeat = $this->config->get('services.drivers.nacos.heartbeat', 5);
                        if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield($heartbeat)) {
                            break;
                        }

                        $response = $this->client->instance->beat(
                            $name,
                            [
                                'ip' => $host,
                                'port' => $port,
                                'serviceName' => $groupName . '@@' . $name,
                                'cluster' => $cluster,
                            ],
                            $groupName,
                            $namespaceId,
                            $ephemeral,
                            $lightBeatEnabled
                        );

                        $result = Json::decode((string) $response->getBody());

                        if ($response->getStatusCode() === 200) {
                            $this->logger->debug(sprintf('Instance %s:%d heartbeat successfully, result code:%s', $host, $port, $result['code']));
                        } else {
                            $this->logger->error(sprintf('Instance %s:%d heartbeat failed! %s', $host, $port, (string) $response->getBody()));
                            continue;
                        }

                        $lightBeatEnabled = false;
                        if (isset($result['lightBeatEnabled'])) {
                            $lightBeatEnabled = $result['lightBeatEnabled'];
                        }

                        if ($result['code'] == 20404) {
                            $this->client->instance->register($host, $port, $name, [
                                'groupName' => $this->config->get('services.drivers.nacos.group_name'),
                                'namespaceId' => $this->config->get('services.drivers.nacos.namespace_id'),
                                'metadata' => $this->getMetadata($name),
                            ]);
                        }
                    } catch (Throwable $exception) {
                        $this->logger->error('The nacos heartbeat failed, caused by ' . $exception);
                        throw $exception;
                    }
                }
            });
        });
    }

    private function getWeight($weight): int
    {
        return intval(100 * $weight);
    }
}
