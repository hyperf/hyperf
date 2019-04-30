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

namespace Hyperf\RpcServer\Listener;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\RpcServer\Event\AfterPathRegister;
use Hyperf\RpcServer\Register\Adapter\ConsulAgent;
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

    public function __construct(ContainerInterface $container, StdoutLoggerInterface $logger)
    {
        $this->consulAgent = $container->get(ConsulAgent::class);
        $this->logger = $logger;
    }

    public function listen(): array
    {
        return [
            AfterPathRegister::class,
        ];
    }

    /**
     * @param AfterPathRegister $event
     */
    public function process(object $event)
    {
        $annotation = $event->annotation;
        switch ($annotation->publishTo) {
            case 'consul':
                $address = '127.0.0.1';
                $port = 9502;
                if ($this->isRegistered($annotation->name, $address, $port, $annotation->protocol)) {
                    $this->logger->debug(sprintf('The %s service has been register to the consul already.', $annotation->name));
                    return;
                }
                $nextId = $this->generateId($this->getLastServiceId($annotation->name));
                $response = $this->consulAgent->registerService([
                    'Name' => $annotation->name,
                    'ID' => $nextId,
                    'Address' => $address,
                    'Port' => $port,
                    'Meta' => [
                        'Protocol' => 'jsonrpc-2.0',
                    ],
                ]);
                if ($response->getStatusCode() === 200) {
                    $this->logger->info(sprintf('Service %s[%s] register to the consul successfully.', $annotation->name, $nextId));
                }
                break;
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
        $services = $this->consulAgent->services()->json();
        $glue = '-';
        $tag = implode($glue, [$name, $address, $port, $protocol]);
        foreach ($services as $serviceId => $service) {
            if (! isset($service['Service'], $service['Address'], $service['Port'], $service['Meta']['Protocol'])) {
                continue;
            }
            $currentTag = implode($glue, [$service['Service'], $service['Address'], $service['Port'], $service['Meta']['Protocol']]);
            if ($currentTag === $tag) {
                return true;
            }
        }
        return false;
    }
}
