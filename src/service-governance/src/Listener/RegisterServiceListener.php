<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\ServiceGovernance\Listener;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\MainWorkerStart;
use Hyperf\ServiceGovernance\Register\ConsulAgent;
use Hyperf\ServiceGovernance\ServiceManager;
use Psr\Container\ContainerInterface;

/**
 * @Listener
 */
class RegisterServiceListener implements ListenerInterface
{
    /**
     * @var ConsulAgent
     */
    private $consulAgent;

    /**
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @var ServiceManager
     */
    private $serviceManager;

    /**
     * @var array
     */
    private $defaultLoggerContext
        = [
            'component' => 'service-governance',
        ];

    public function __construct(ContainerInterface $container)
    {
        $this->consulAgent = $container->get(ConsulAgent::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->serviceManager = $container->get(ServiceManager::class);
    }

    public function listen(): array
    {
        return [
            MainWorkerStart::class,
        ];
    }

    /**
     * @param MainWorkerStart $event
     */
    public function process(object $event)
    {
        $services = $this->serviceManager->all();
        foreach ($services as $serviceName => $paths) {
            foreach ($paths as $path => $service) {
                if (! isset($service['publishTo'])) {
                    continue;
                }
                switch ($service['publishTo']) {
                    case 'consul':
                        // @TODO Retrieve the address and port automatically.
                        $address = '127.0.0.1';
                        $port = 9502;
                        $this->logger->debug(sprintf('Service %s[%s] is registering to the consul.', $serviceName, $path), $this->defaultLoggerContext);
                        if ($this->isRegistered($serviceName, $address, $port, $service['protocol'])) {
                            $this->logger->info(sprintf('Service %s[%s] has been already registered to the consul.', $serviceName, $path), $this->defaultLoggerContext);
                            return;
                        }
                        if ($service['ID']) {
                            $nextId = $service['ID'];
                        } else {
                            $nextId = $this->generateId($this->getLastServiceId($serviceName));
                        }
                        $response = $this->consulAgent->registerService([
                            'Name' => $serviceName,
                            'ID' => $nextId,
                            'Address' => $address,
                            'Port' => $port,
                            'Meta' => [
                                'Protocol' => 'jsonrpc',
                            ],
                        ]);
                        if ($response->getStatusCode() === 200) {
                            $this->logger->info(sprintf('Service %s[%s]:%s register to the consul successfully.', $serviceName, $path, $nextId), $this->defaultLoggerContext);
                        } else {
                            $this->logger->warning(sprintf('Service %s register to the consul failed.', $serviceName), $this->defaultLoggerContext);
                        }
                        break;
                }
            }
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

    private function isRegistered(string $name, string $address, int $port, string $protocol): bool
    {
        $response = $this->consulAgent->services();
        if ($response->getStatusCode() !== 200) {
            $this->logger->warning(sprintf('Service %s register to the consul failed.', $name), $this->defaultLoggerContext);
            return false;
        }
        $services = $response->json();
        $glue = '-';
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
                return true;
            }
        }
        return false;
    }
}
