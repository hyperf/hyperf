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
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnShutdown;
use Hyperf\Nacos\Application;
use Hyperf\Server\Event\CoroutineServerStop;
use Hyperf\ServiceGovernance\IPReaderInterface;
use Psr\Container\ContainerInterface;

class OnShutdownListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var IPReaderInterface
     */
    protected $ipReader;

    /**
     * @var bool
     */
    private $processed = false;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->ipReader = $container->get(IPReaderInterface::class);
    }

    public function listen(): array
    {
        return [
            OnShutdown::class,
            CoroutineServerStop::class,
        ];
    }

    public function process(object $event)
    {
        if ($this->processed) {
            return;
        }
        $this->processed = true;

        $config = $this->container->get(ConfigInterface::class);
        if (! $config->get('nacos.service.enable', true)) {
            return;
        }
        if (! $config->get('nacos.service.instance.auto_removed', false)) {
            return;
        }

        $serviceConfig = $config->get('nacos.service', []);
        $serviceName = $serviceConfig['service_name'];
        $groupName = $serviceConfig['group_name'] ?? null;
        $namespaceId = $serviceConfig['namespace_id'] ?? null;
        $instanceConfig = $serviceConfig['instance'] ?? [];
        $ephemeral = $instanceConfig['ephemeral'] ?? null;
        $cluster = $instanceConfig['cluster'] ?? null;
        $ip = $this->ipReader->read();

        $client = $this->container->get(Application::class);
        $ports = $config->get('server.servers', []);
        foreach ($ports as $portServer) {
            $port = (int) $portServer['port'];
            $response = $client->instance->delete($serviceName, $groupName, $ip, $port, [
                'clusterName' => $cluster,
                'namespaceId' => $namespaceId,
                'ephemeral' => $ephemeral,
            ]);

            if ($response->getStatusCode() === 200) {
                $this->logger->debug(sprintf('Instance %s:%d deleted successfully!', $ip, $port));
            } else {
                $this->logger->error(sprintf('Instance %s:%d deleted failed!', $ip, $port));
            }
        }
    }
}
