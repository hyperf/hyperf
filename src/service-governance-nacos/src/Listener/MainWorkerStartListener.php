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

namespace Hyperf\ServiceGovernanceNacos\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\IPReaderInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\MainWorkerStart;
use Hyperf\Nacos\Application;
use Hyperf\Nacos\Exception\RequestException;
use Hyperf\Server\Event\MainCoroutineServerStart;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class MainWorkerStartListener implements ListenerInterface
{
    protected LoggerInterface $logger;

    protected IPReaderInterface $ipReader;

    public function __construct(protected ContainerInterface $container)
    {
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->ipReader = $container->get(IPReaderInterface::class);
    }

    public function listen(): array
    {
        return [
            MainWorkerStart::class,
            MainCoroutineServerStart::class,
        ];
    }

    public function process(object $event): void
    {
        $config = $this->container->get(ConfigInterface::class);

        if (! $config->get('nacos')) {
            return;
        }

        $serviceConfig = $config->get('nacos.service', []);
        if (! $serviceConfig || empty($serviceConfig['enable'])) {
            return;
        }

        $serviceName = $serviceConfig['service_name'];
        $groupName = $serviceConfig['group_name'] ?? null;
        $namespaceId = $serviceConfig['namespace_id'] ?? null;
        $protectThreshold = $serviceConfig['protect_threshold'] ?? null;
        $metadata = $serviceConfig['metadata'] ?? null;
        $selector = $serviceConfig['selector'] ?? null;

        try {
            $client = $this->container->get(Application::class);

            // Register Service to Nacos.
            $response = $client->service->detail($serviceName, $groupName, $namespaceId);
            $optional = [
                'groupName' => $groupName,
                'namespaceId' => $namespaceId,
                'protectThreshold' => $protectThreshold,
                'metadata' => $metadata,
                'selector' => $selector,
            ];

            if ($response->getStatusCode() === 404
                || ($response->getStatusCode() === 500 && strpos((string) $response->getBody(), 'is not found') > 0)) {
                $response = $client->service->create($serviceName, $optional);
                if ($response->getStatusCode() !== 200 || (string) $response->getBody() !== 'ok') {
                    throw new RequestException(sprintf('Failed to create nacos service %s!', $serviceName));
                }
                $this->logger->info(sprintf('Nacos service %s was created successfully!', $serviceName));
            } elseif ($response->getStatusCode() === 200) {
                $response = $client->service->update($serviceName, $optional);
                if ($response->getStatusCode() !== 200 || (string) $response->getBody() !== 'ok') {
                    throw new RequestException(sprintf('Failed to update nacos service %s!', $serviceName));
                }
                $this->logger->info(sprintf('Nacos service %s was updated successfully!', $serviceName));
            } else {
                throw new RequestException((string) $response->getBody(), $response->getStatusCode());
            }

            // Register Instance to Nacos.
            $instanceConfig = $serviceConfig['instance'] ?? [];
            $ephemeral = in_array($instanceConfig['ephemeral'], [true, 'true'], true) ? 'true' : 'false';
            $cluster = $instanceConfig['cluster'] ?? null;
            $weight = $instanceConfig['weight'] ?? null;
            $metadata = $instanceConfig['metadata'] ?? null;

            $ip = $this->ipReader->read();
            $optional = [
                'groupName' => $groupName,
                'namespaceId' => $namespaceId,
                'ephemeral' => $ephemeral,
            ];

            $optionalData = array_merge($optional, [
                'clusterName' => $cluster,
                'weight' => $weight,
                'metadata' => $metadata,
                'enabled' => 'true',
            ]);

            $ports = $config->get('server.servers', []);
            foreach ($ports as $portServer) {
                $port = (int) $portServer['port'];
                $response = $client->instance->detail($ip, $port, $serviceName, array_merge($optional, [
                    'cluster' => $cluster,
                ]));

                if ($response->getStatusCode() === 404
                    || ($response->getStatusCode() === 500 && strpos((string) $response->getBody(), 'no ips found') > 0)) {
                    $response = $client->instance->register($ip, $port, $serviceName, $optionalData);
                    if ($response->getStatusCode() !== 200 || (string) $response->getBody() !== 'ok') {
                        throw new RequestException(sprintf('Failed to create nacos instance %s:%d!', $ip, $port));
                    }
                    $this->logger->info(sprintf('Nacos instance %s:%d was created successfully!', $ip, $port));
                } elseif ($response->getStatusCode() === 200) {
                    $response = $client->instance->update($ip, $port, $serviceName, $optionalData);
                    if ($response->getStatusCode() !== 200 || (string) $response->getBody() !== 'ok') {
                        throw new RequestException(sprintf('Failed to update nacos instance %s:%d!', $ip, $port));
                    }
                    $this->logger->info(sprintf('Nacos instance %s:%d was updated successfully!', $ip, $port));
                }
            }
        } catch (Throwable $exception) {
            $this->logger->critical((string) $exception);
        }
    }
}
