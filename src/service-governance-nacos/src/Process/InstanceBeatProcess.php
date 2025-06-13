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

namespace Hyperf\ServiceGovernanceNacos\Process;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\IPReaderInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Nacos\Application;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\ProcessManager;

class InstanceBeatProcess extends AbstractProcess
{
    public string $name = 'nacos-heartbeat';

    public function handle(): void
    {
        $config = $this->container->get(ConfigInterface::class);
        $logger = $this->container->get(StdoutLoggerInterface::class);
        $client = $this->container->get(Application::class);

        $serviceConfig = $config->get('nacos.service', []);
        $serviceName = $serviceConfig['service_name'];
        $namespaceId = $serviceConfig['namespace_id'];
        $groupName = $serviceConfig['group_name'] ?? null;
        $instanceConfig = $serviceConfig['instance'] ?? [];
        $ephemeral = $instanceConfig['ephemeral'] ?? null;
        $cluster = $instanceConfig['cluster'] ?? null;
        $weight = $instanceConfig['weight'] ?? null;
        $ipReader = $this->container->get(IPReaderInterface::class);
        $ip = $ipReader->read();

        while (ProcessManager::isRunning()) {
            $heartbeat = $config->get('nacos.service.instance.heartbeat', 5);
            sleep($heartbeat ?: 5);

            $ports = $config->get('server.servers', []);
            foreach ($ports as $portServer) {
                $port = (int) $portServer['port'];
                $response = $client->instance->beat(
                    $serviceName,
                    [
                        'ip' => $ip,
                        'port' => $port,
                        'serviceName' => $groupName . '@@' . $serviceName,
                        'cluster' => $cluster,
                        'weight' => $weight,
                    ],
                    $groupName,
                    $namespaceId,
                    $ephemeral
                );

                if ($response->getStatusCode() === 200) {
                    $logger->debug(sprintf('Instance %s:%d heartbeat successfully!', $ip, $port));
                } else {
                    $logger->error(sprintf('Instance %s:%d heartbeat failed!', $ip, $port));
                }
            }
        }
    }

    public function isEnable($server): bool
    {
        $config = $this->container->get(ConfigInterface::class);
        return $config->get('nacos.service.enable', true) && $config->get('nacos.service.instance.heartbeat', 0);
    }
}
